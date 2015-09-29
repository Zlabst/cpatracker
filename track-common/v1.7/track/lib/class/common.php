<?php

class common {

    private $params = array();

    function __construct($params = array()) {
        $this->params = $params;
    }

    function set_params($params) {
        if (is_array($params)) {
            $this->params = $params;
        }
    }

    function process_conversion($data)
    {
        $cnt = count($this->params);
        $i = 0;
        $is_lead = (isset($data['is_lead'])) ? 1 : 0;
        $is_sale = (isset($data['is_sale'])) ? 1 : 0;
        unset($data['is_lead']);
        unset($data['is_sale']);

        switch (strtoupper($data['txt_param20']))
        {
            case 'UAH':
                $data['profit'] = convert_to_usd('uah', $data['profit']);
            break;

            case 'USD':
                $data['profit'] = convert_to_usd('usd', $data['profit']);
            break;

            default:
                $data['profit'] = convert_to_usd('rub', $data['profit']);
            break;
        }

        // Специальная обработка "статусного постбэка" от сети CTR.
        // В этом случае приходит только статус, связанный с остальными данными через order_id (i3)
        // и нужно поменять статус соостветствующей конвертации.

        $ctr_order = false; // флаг, о том, что некоторые операции (замену логов) выполнять не нужно,
                            // так как это не полный запрос, а только статус
        if ($data['network'] == 'CTR' and !empty($data['status'])) {
            $q = 'SELECT * FROM `tbl_conversions` WHERE (`i3` = "' . mysql_real_escape_string($data['i3']) . '" AND `network` = "CTR") LIMIT 1';
            if ($rs = db_query($q) and mysql_num_rows($rs) > 0) {
                $r = mysql_fetch_assoc($rs);
                $data['subid'] = $r['subid'];
            }
            dmp($data);
            foreach ($data as $k => $v) {
                if (!in_array($k, array('network', 'i3', 'status', 'txt_status', 'ak', 'date_add', 'subid'))) {
                    unset($data[$k]);
                }
            }
            $ctr_order = true;
        }


        if (isset($data['subid']) && $data['subid'] != '')
        {
            $table = 'tbl_clicks';

            $subid = $data['subid']; // мы скоро обнулим массив data, а subid нам ещё понадобится
            $status = $data['status'];

            // Проверяем есть ли клик с этим SibID
            $q = 'SELECT `id`, `is_sale`, `is_lead` FROM `' . $table . '` WHERE `subid` = "' . mysql_real_escape_string($subid) . '"';
            $r = mysql_query($q) or die($q . '<br />' . mysql_error());

            if (mysql_num_rows($r) > 0) {
                $f = mysql_fetch_assoc($r);
                $click_id = $f['id'];
                if ($data['profit'] > 0) {
                    $is_lead = $f['is_lead'] > 0 ? 1 : 0;
                    $is_sale = 1;
                } else {
                    $is_lead = 1;
                    $is_sale = $f['is_sale'] > 0 ? 1 : 0;
                }

                $q = 'UPDATE `' . $table . '` SET `is_sale` = ' . $is_sale . ', `is_lead` = ' . intval($is_lead) . ', `conversion_price_main` = "' . mysql_real_escape_string($data['profit']) . '" WHERE `id` = ' . $click_id;
                mysql_query($q) or die(mysql_error());

                // Обновление кэша
                if (_CLICKS_SPOT_SIZE > 0) {
                    $time_from = time_edge(strtotime($tmp), 'hour', 'begin');
                    $q = "UPDATE `tbl_clicks_cache_hour` SET `rebuild` = '1' WHERE `time` = '" . date('Y-m-d H:i:s', $time_from) . "'";
                    mysql_query($q);
                }
            }

            // ----------------------------
            // Готовим данные для конверсии
            // ----------------------------

            $upd = array(); // Инициализируем массив для запроса на обновление
            // Дополнительные поля, которых нет в $params, но которые нам нужны в БД
            $additional_fields = array('date_add', 'txt_status', 'status', 'network', 'type');



            foreach ($data as $name => $value) {
                if (array_key_exists($name, $this->params) or in_array($name, $additional_fields)) {
                    $upd[$name] = $value;
                    unset($data[$name]);
                }
            }

            //if (empty($upd['date_add'])) {
            $upd['date_add'] = mysql_now(); // date('Y-m-d H:i:s');
            //}
            // Проверяем, есть ли уже конверсия с таким SubID
            $q = 'SELECT * FROM `tbl_conversions` WHERE `subid` = "' . mysql_real_escape_string($subid) . '" LIMIT 1';

            $r = db_query($q) or die(mysql_error());

            if (mysql_num_rows($r) > 0) {
                $f = mysql_fetch_assoc($r);

                $upd['id'] = $conv_id = $f['id'];

                $q = updatesql($upd, 'tbl_conversions', 'id');
                db_query($q);

                // Чистим логи
                if (!$ctr_order) {
                    db_query('DELETE FROM `tbl_postback_params` WHERE `conv_id` = ' . $f['id']) or die(mysql_error());
                }
            } else {
                $q = insertsql($upd, 'tbl_conversions');
                db_query($q);

                $conv_id = mysql_insert_id();
            }

            // Нужно ли нам отменить продажу?
            if ($status == 2) {
                delete_sale($click_id, $conv_id, 'sale');
                //return false;
            }

            // Пишем postback логи
            foreach ($data as $name => $value) {
                if (strpos($name, 'pbsave_') !== false) {
                    $name = str_replace('pbsave_', '', $name);
                    $ins = array(
                        'conv_id' => $conv_id,
                        'name' => $name,
                        'value' => value,
                    );
                    $q = insertsql($ins, 'tbl_postback_params');
                    db_query($q);
                }
            }
        }
    }

    function get_code() {
        if (is_file(_ROOT_PATH . '/cache/.postback.key')) {
            $key = file_get_contents(_ROOT_PATH . '/cache/.postback.key');
            return $key;
        } else {
            $key = substr(md5(__FILE__), 3, 10);
            file_put_contents(_ROOT_PATH . '/cache/.postback.key', $key);
            return $key;
        }
    }

    function get_pixelcode() {
        if (is_file(_ROOT_PATH . '/cache/.pixel.key')) {
            $key = file_get_contents(_ROOT_PATH . '/cache/.pixel.key');
            return $key;
        } else {
            $key = substr(md5(__FILE__ . 'TraCKKERPIxxel'), 3, 10);
            file_put_contents(_ROOT_PATH . '/cache/.pixel.key', $key);
            return $key;
        }
    }

    function log($net, $post, $get) {
        if (!isset($get['apikey']) || ($this->get_code() != $get['apikey'])) {
            return;
        }

        if (!is_dir(_ROOT_PATH . '/cache/pblogs/')) {
            mkdir(_ROOT_PATH . '/cache/pblogs');
        }

        $log = fopen(_ROOT_PATH . '/cache/pblogs/.' . $net . date('Y-m-d') . '.txt', 'a+');

        if ($log) {
            fwrite($log, '[' . date('Y-m-d H:i:s') . '] [POST] ' . var_export($post));
            fwrite($log, '[' . date('Y-m-d H:i:s') . '] [GET] ' . var_export($get));
            fclose($log);
        }
    }

    /*
     * Если данные есть и в GET и в POST - формируем общий массив по следующему правилу:
      1. Сохраняем данные из GET
      2. Заменяем/добавляем данные из POST, если это не приведет к удалению ключей
     */

    function request($data) {
        $out = array();

        if (!empty($data['get'])) {
            $out = $data['get'];
        }

        if (!empty($data['post'])) {
            foreach ($data['post'] as $k => $v) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

}