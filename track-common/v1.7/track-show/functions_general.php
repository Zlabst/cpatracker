<?php

/**
 * Перехват фатальных ошибок, требует PHP 5.2+
 */
register_shutdown_function("fatal_handler");

function fatal_handler() {
    $errfile = "unknown file";
    $errstr = "shutdown";
    $errno = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== NULL) {
        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];

        $html = format_error($errno, $errstr, $errfile, $errline);

        // Отправляем письмо, если есть соответствующий плагин
        if ($errno == 1) {
            $errors_to_email = load_plugin('errors_to_email');

            if (!empty($errors_to_email)) {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html\r\n";

                mail($errors_to_email, 'CPATracker error', $html, $headers);
            }
        }

        if (_ENABLE_DEBUG && isset($_GET['debug']) && $_GET['debug'] == 1) {
            echo $html;
        } else {
            if ($errno == 1) {
                echo '<div class="alert alert-error">Произошла ошибка. Обратитесь, пожалуйста, в техническую поддержку.</div>';
            }
        }
    }
}

/**
 * Форматирование текста ошибки
 */
function format_error($errno, $errstr, $errfile, $errline) {
    $trace = print_r(debug_backtrace(false), true);
    $request = print_r($_REQUEST, true);

    $content = "<table><thead bgcolor='#c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
    $content .= "<tr valign='top'><td><b>URL</b></td><td><pre>{$_SERVER['REQUEST_URI']}</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>File</b></td><td>$errfile</td></tr>";
    $content .= "<tr valign='top'><td><b>Line</b></td><td>$errline</td></tr>";
    $content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
    $content .= "<tr valign='top'><td><b>Request</b></td><td><pre>$request</pre></td></tr>";
    $content .= '</tbody></table>';

    return $content;
}

/** Колонки, присутствующие в отчётах
 * money - флаг, обозначающий, что нужен значок валюты и возможно конвертация курса
 * каждому ключу массива NAME должна соответствовать функция t_NAME, рассчитывающая значение из начальных данных
 */
$report_cols = array(
    'cnt' => array('name' => 'Переходы', 'money' => 0),
    'cnt_act' => array('name' => 'Переходы', 'money' => 0),
    'cnt_sale' => array('name' => 'Переходы', 'money' => 0),
    'cnt_lead' => array('name' => 'Переходы', 'money' => 0),
    'repeated' => array('name' => 'Повторные', 'money' => 0),
    'lpctr' => array('name' => 'LP CTR', 'money' => 0),
    'sale' => array('name' => 'Продажи', 'money' => 0),
    'lead' => array('name' => 'Лиды', 'money' => 0),
    'act' => array('name' => 'Действия', 'money' => 0),
    'conversion' => array('name' => 'Конверсия', 'money' => 0),
    'conversion_l' => array('name' => 'Конверсия', 'money' => 0),
    'conversion_a' => array('name' => 'Конверсия', 'money' => 0),
    'price' => array('name' => 'Затраты', 'money' => 1),
    'profit' => array('name' => 'Прибыль', 'money' => 1),
    'epc' => array('name' => 'EPC', 'money' => 1),
    'roi' => array('name' => 'ROI', 'money' => 0),
    'cps' => array('name' => 'CPS', 'money' => 1),
    'cpl' => array('name' => 'CPL', 'money' => 1),
    'cpa' => array('name' => 'CPA', 'money' => 1),
);

/*
 * Курсы валют. На будущее, хорошо бы обновлять их откуда-то
 */
$currencies = array(
    'usd' => 1, // рассчёты внутри системы проводятся в долларах
    'rub' => 56, // на 21 января
    'uah' => 15.8
);

$option_currency = array(
    'rub' => '<i class="fa fa-rub"></i>',
    'usd' => '$',
);

// Группы источников
$source_types = array(
    0 => array(
        'name' => 'Контекстная реклама',
        'values' => array('yadirect', 'adwords') //'landing', 
    ),
    1 => array(
        'name' => 'Социальные сети',
        'values' => array('vk', 'facebook', 'targetmail')
    ),
    2 => array(
        'name' => 'Тизерные сети',
        'values' => array('actionteaser', 'adhub', 'adlabs', 'adprofy', 'advertlink', 'bannerbook', 'bodyclick', 'cashprom', 'directadvert', 'globalteaser', 'kadam', 'marketgid', 'mediatarget', 'novostimira', 'privatteaser', 'redclick', 'redtram', 'teasermedia', 'teasernet', 'visitweb', 'yottos')
    ),
    4 => array(
        'name' => 'Мобильные сети',
        'values' => array('adinch', 'admoda', 'adtwirl', 'adultmoda', 'airpush', 'buzzcity', 'decisive', 'go2mobi', 'inmobi', 'jumptap', 'leadbolt', 'mmedia', 'mobfox', 'mobiads', 'octobird', 'startapp', 'tapgage', 'tapit', 'wapstart')
    ),
    3 => array(
        'name' => 'Рекламные сети',
        'values' => array('dntx', 'exoclick', 'leadimpact', 'plugrush', 'popunder', 'sitescout', 'zeropark')
    ),
);

// Predefined sources parameters list
require_once ('source_config.php');

/*
 * Список файлов из директории
 */

function dir_files($path, $type = '') {
    $files = array();
    if ($handle = opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && !is_dir($path . $file)) {
                if ($type != '' and (strstr($file, '*') !== false or $file == '.' . $type . '_' . date('Y-m-d-H-i')))
                    continue;
                $files[] = $file;
            }
        }
    }
    return $files;
}

function _str($str) {
    return mysql_real_escape_string(trim($str));
}

function _e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false);
}

// Смена статуса записи в любой таблице
function change_status($table, $id, $status) {
    $q = "UPDATE `" . $table . "` SET `status` = '" . $status . "' WHERE `id` = '" . $id . "'";
    db_query($q);
}

// Пользовательские уведомления
function user_notifications($status = -1, $offset = 0, $limit = 20) {
    // Общее количество строк
    $q = "SHOW TABLE STATUS LIKE 'tbl_notifications'";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    $total = $r['Rows'];

    // Количество непрочитанных
    $q = "select count(id) as `cnt` 
        from `tbl_notifications`
        where `status` = '0'";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    $total_unread = $r['cnt'];

    $out = array();
    if ($limit > 0) {
        $q = "select * 
            from `tbl_notifications`
            where 1 " . ($status != -1 ? "and `status` = '" . intval($status) . "'" : '' ) . "
            order by `id` desc
            limit $offset, $limit";
        $rs = db_query($q);
        while ($r = mysql_fetch_assoc($rs)) {
            $out[$r['id']] = $r;
        }
    }
    return array($total, $total_unread, $out);
}

function disable_magic_quotes() {
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }
}

function create_crontab_markers() {
    // This files will be removed after first successful crontab run
    $file_name = _CACHE_PATH . '/.crontab_clicks';
    file_put_contents($file_name, 'Please add lines to crontab (see Install section of documentation)');
    chmod($file_name, 0777);

    $file_name = _CACHE_PATH . '/.crontab_postback';
    file_put_contents($file_name, 'Please add lines to crontab (see Install section of documentation)');
    chmod($file_name, 0777);
}

function check_crontab_markers() {
    $result = array('error' => false);
    $crontab_clicks = _CACHE_PATH . '/.crontab_clicks';
    $crontab_postback = _CACHE_PATH . '/.crontab_postback';
    $api_connect_error = _CACHE_PATH . '/.api_connect_error';

    if (is_file($crontab_clicks)) {
        $result['error'] = true;
        $result['crontab_clicks'] = true;
    }

    if (is_file($crontab_postback)) {
        $result['error'] = true;
        $result['crontab_postback'] = true;
    }

    if (is_file($api_connect_error)) {
        $result['error'] = true;
        $result['api_connect'] = true;
    }

    return $result;
}

function check_settings() {
    $settings_file = _TRACK_SETTINGS_PATH . '/settings.php';
    $arr_folders = array('clicks', 'wurfl-persistence', 'wurfl-cache', 'postback');

    if (is_file($settings_file)) {
        $str = file_get_contents($settings_file);
        $str = str_replace('<?php exit(); ?>', '', $str);
        return array(true, unserialize($str), $settings_file);
    }

    if (!is_writable(_CACHE_PATH)) {
        chmod(_CACHE_PATH, 0777);
    }

    if (is_writable(_CACHE_PATH)) {
        // Create required folders
        foreach ($arr_folders as $cur_folder) {
            if (!is_dir(_CACHE_PATH . '/' . $cur_folder)) {
                mkdir(_CACHE_PATH . '/' . $cur_folder);
                chmod(_CACHE_PATH . '/' . $cur_folder, 0777);
            }
        }

        return array(false, 'first_run', $settings_file);
    } else {
        return array(false, 'cache_not_writable', _CACHE_PATH);
    }
}

function change_password($email, $new_password) {
    $sql = "select id, email, password, salt from tbl_users where email='" . mysql_real_escape_string($email) . "'";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);

    if ($row['id'] > 0) {
        $user_password = md5($row['salt'] . $new_password);
        $sql = "update `tbl_users` set `password` = '" . mysql_real_escape_string($user_password) . "' where id = '" . $row['id'] . "'";
        db_query($sql);
        
        $update = array(
            'password' => $user_password,
            'email' => $email,
            'salt' => $row['salt'],
        );
        
        load_plugin('change_billing_password_too', '', $update);

        return true;
    }
}

/**
 * Генерируем хэш для сброса пароля
 * @param string $email
 * @return string 
 */
function reset_password_hash($email = '') {
    $sql = "select id, email, password, salt from tbl_users where 1";

    if ($email != '')
        $sql .= " and email='" . mysql_real_escape_string($email) . "'";

    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);

    return array('hash' => md5($row['salt'] . $row['password']), 'email' => $row['email']);
}

function check_user_credentials($email = '', $password = '') {

    $q = "select id, email, password, salt from tbl_users where 1";

    if ($email != '')
        $q .= " and email='" . mysql_real_escape_string($email) . "'";
    
    $rs = db_query($q) or die(mysql_error());
    
    // Custom authentication plugin, if any.
    $admin_login = (load_plugin('admin_login') == 'admin');

    if ($email == '' and !$admin_login)
        return array(false);

    $row = mysql_fetch_assoc($rs);
    if ($row['id'] > 0) {
        $user_password = md5($row['salt'] . $password);
        if ($user_password == $row['password'] or $admin_login) {
            // Password is correct
            return array(true, $row['email'], $row['password']);
        }
    }
    return array(false);
}

function is_auth() {
    if (isset($_COOKIE['cpatracker_auth_email'])) {
        $user_email = $_COOKIE['cpatracker_auth_email'];
        $user_password = $_COOKIE['cpatracker_auth_password'];

        $sql = "select id, email, password, salt from tbl_users where email='" . mysql_real_escape_string($user_email) . "'";
        $result = db_query($sql);
        $row = mysql_fetch_assoc($result);

        if ($row['id'] > 0) {
            if ($user_password == $row['password']) {
                // Password is correct
                return array(true, $user_email);
            } else {
                // Password is incorrect
                return array(false, 'wrong_password');
            }
        } else {
            $sql = "select count(id) as cnt from tbl_users";
            $result = db_query($sql);
            $row = mysql_fetch_assoc($result);
            if ($row['cnt'] == 0) {
                // No users found
                return array(false, 'register_new');
            } else {
                // User not found
                return array(false, 'user_not_found');
            }
        }
    } else {
        $sql = "select count(id) as cnt from tbl_users";
        $result = db_query($sql);
        $row = mysql_fetch_assoc($result);
        if ($row['cnt'] == 0) {
            // No users found
            return array(false, 'register_new');
        } else {
            return array(false, 'empty_cookie');
        }
    }

    return array(false, 'unknown_error');
}

function register_admin($email, $password) {
    $salt = substr(md5(rand()), 0, 7);
    $salted_password = md5($salt . $password);
    $sql = "insert into tbl_users (email, password, salt) values ('" . mysql_real_escape_string($email) . "', '" . mysql_real_escape_string($salted_password) . "', '" . mysql_real_escape_string($salt) . "')";
    db_query($sql);
    return $salted_password;
}

function full_url() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
    $uri = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    $segments = explode('?', $uri, 2);
    $url = $segments[0];
    $url = str_replace('index.php', '', $url);
    return $url;
}

function get_rules() {
    $arr_rules = array();
    $sql = "select * from tbl_rules where status=0 order by date_add desc, id asc";
    $result = db_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $arr_rules[$row['id']] = $row;
    }
    return $arr_rules;
}

function get_sources() {
    global $source_config;
    $arr_sources = array();
    $sql = "select distinct source_name from tbl_clicks where source_name!='' order by source_name asc";
    $result = db_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $source_name = $row['source_name'];
        $row['name'] = empty($source_config[$source_name]['name']) ? $source_name : $source_config[$source_name]['name'];
        $arr_sources[] = $row;
    }
    return $arr_sources;
}

function get_campaigns() {
    $arr_campaigns = array();
    $sql = "select distinct campaign_name from tbl_clicks where campaign_name!='' order by campaign_name asc";
    $result = db_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $arr_campaigns[] = $row;
    }
    return $arr_campaigns;
}

function get_ads() {
    $arr_ads = array();
    $sql = "select distinct ads_name from tbl_clicks where ads_name!='' order by ads_name asc";
    $result = db_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $arr_ads[] = $row;
    }
    return $arr_ads;
}

function get_last_sales($filter_by = '') {
    $timezone_shift = get_current_timezone_shift();
    $arr_sales = array();
    $filter_by_str = '';
    if ($filter_by != '') {
        $filter_by_str = " and tbl_conversions.subid='" . _str($filter_by) . "' ";
    }

    $sql = "select tbl_conversions.*, CONVERT_TZ(tbl_conversions.date_add, '+00:00', '" . _str($timezone_shift) . "') as date_add, tbl_conversions.id as conversion_id, tbl_clicks.id as click_id, tbl_clicks.country, tbl_clicks.source_name, tbl_clicks.campaign_name, tbl_clicks.ads_name, tbl_clicks.referer, tbl_offers.offer_name from tbl_conversions left join tbl_clicks on tbl_conversions.subid=tbl_clicks.subid left join tbl_offers on tbl_offers.id=tbl_clicks.out_id where 0=0 {$filter_by_str} order by tbl_conversions.date_add desc limit 50";
    $result = db_query($sql) or die(mysql_error());

    while ($row = mysql_fetch_assoc($result)) {
        $add_r = db_query('SELECT * FROM `tbl_postback_params` WHERE `conv_id` = ' . $row['id']);
        while ($add_f = mysql_fetch_assoc($add_r)) {
            $row['add'][] = $add_f;
        }
        $arr_sales[] = $row;
    }
    return $arr_sales;
}

function mysqldate2string($date) {
    $arr_months = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    $date = current(explode(' ', $date));
    $d = explode('-', $date);
    return $d[2] . ' ' . $arr_months[$d[1] - 1] . ' ' . $d[0];
}

function mysqldate2short($str) {
    $dt = explode(' ', $str);
    $dd = explode('-', $dt[0]);
    $tt = explode(':', $dt[1]);
    return "{$dd[2]}.{$dd[1]} {$tt[0]}:{$tt[1]}";
}

// Apply timezone settings and return current day
function get_current_day($offset = '') {
    $timezone_shift = get_current_timezone_shift();
    $dt = strtotime(current(explode(':', $timezone_shift)) . ' hours');
    if ($offset == '') {
        return date('Y-m-d', $dt);
    } else {
        return date('Y-m-d', strtotime($offset, $dt));
    }
}

function get_rule_description($rule_id) {
    $sql = "select link_name from tbl_rules where id='" . mysql_real_escape_string($rule_id) . "'";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);
    return $row['link_name'];
}

function get_out_description($out_id) {
    $sql = "select offer_name, offer_tracking_url from tbl_offers where id='" . mysql_real_escape_string($out_id) . "'";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);
    $result = array($out_id, '');
    if ($out_id > 0) {
        return array($row['offer_name'], $row['offer_tracking_url']);
    } else {
        return array('{empty}', '');
    }
}

/*
  function get_offers_list($skip_networks_offers = true) {
  $arr_offers = array();

  if ($skip_networks_offers) {
  $where = ' and tbl_offers.network_id=0';
  } else {
  $where = '';
  }
  $sql = "select tbl_offers.*, tbl_links_categories_list.category_caption from tbl_offers left join tbl_links_categories on tbl_links_categories.offer_id=tbl_offers.id left join tbl_links_categories_list on tbl_links_categories_list.id=tbl_links_categories.category_id where tbl_offers.status=0 {$where} order by tbl_links_categories_list.category_caption asc, tbl_offers.date_add desc";
  $result = db_query($sql);
  while ($row = mysql_fetch_assoc($result)) {
  $arr_offers[] = $row;
  }
  return $arr_offers;
  } */

function get_class_by_os($platform) {
    switch ($platform) {
        case 'Windows XP': $c = 'b-favicon-os-windowsxp';
            break;
        case 'Windows 7': $c = 'b-favicon-os-windows7';
            break;
        case 'Windows 8': $c = 'b-favicon-os-windows8';
            break;
        case 'Apple': case 'Mac OS X': $c = 'b-favicon-os-apple';
            break;
        case 'Apple iPad': $c = 'b-favicon-os-ipad';
            break;
        case 'BlackBerry': $c = 'b-favicon-os-blackberry';
            break;
        case 'Android': $c = 'b-favicon-os-android';
            break;
        case 'iPhone': $c = 'b-favicon-os-iphone';
            break;
        case 'iPod': $c = 'b-favicon-os-iphone';
            break;
        case 'Linux': case 'FreeBSD': case 'OpenBSD': case 'NetBSD': $c = 'b-favicon-os-linux';
            break;
        default: $c = '';
            break;
    }
    return $c;
}

function get_class_by_platform($platform) {
    switch ($platform) {
        case 'Windows XP': $c = 'b-favicon-os-windowsxp';
            break;
        case 'Windows 7': $c = 'b-favicon-os-windows7';
            break;
        case 'Windows 8': $c = 'b-favicon-os-windows8';
            break;
        case 'Apple': $c = 'b-favicon-os-apple';
            break;
        case 'iPad': $c = 'b-favicon-os-ipad';
            break;
        case 'BlackBerry': $c = 'b-favicon-os-blackberry';
            break;
        case 'Android': $c = 'b-favicon-os-android';
            break;
        case 'iPhone': $c = 'b-favicon-os-iphone';
            break;
        case 'iPod': $c = 'b-favicon-os-iphone';
            break;
        case 'Linux': case 'FreeBSD': case 'OpenBSD': case 'NetBSD': $c = 'b-favicon-os-linux';
            break;
        default: $c = '';
            break;
    }
    return $c;
}

function getDatesBetween($strDateFrom, $strDateTo) {
    $aryRange = array();

    $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
    $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

    if ($iDateTo >= $iDateFrom) {
        array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
        while ($iDateFrom < $iDateTo) {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange, date('Y-m-d', $iDateFrom));
        }
    }
    return $aryRange;
}

function getMonthsBetween($strDateFrom, $strDateTo) {
    $aryRange = array();

    $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
    $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

    if ($iDateTo >= $iDateFrom) {
        $date = explode('-', $strDateFrom);
        $begin_year = $date[0];
        $begin_month = $date[1];
        $date = explode('-', $strDateTo);
        $end_year = $date[0];
        $end_month = $date[1];
        for ($cur_year = $begin_year; $cur_year <= $end_year; $cur_year++) {
            if ($cur_year == $end_year)
                $max_month = $end_month;
            else
                $max_month = 12;
            //
            if ($cur_year == $begin_year)
                $cur_month = $begin_month;
            else
                $cur_month = 1;
            for ($cur_month; $cur_month <= $max_month; $cur_month++) {
                $item = ((strlen($cur_month) < 2) ? '0' . $cur_month : $cur_month) . '.' . $cur_year;
                array_push($aryRange, $item);
            }
        }
    }
    return $aryRange;
}

// Time in relative format
function get_relative_mysql_time($timediff) {
    $arr_td = explode(':', $timediff);
    if ($arr_td[0] > 23) {
        $minutes = $arr_td[0] * 60 + $arr_td[1];
        $d = floor($minutes / 1440) + 0;
        $h = floor(($minutes - $d * 1440) / 60) + 0;
        $td = "{$d}д {$h}ч";
    } else {
        if ($arr_td[0] === '-00') {
            $td = '-' . ($arr_td[0] + 0) . "ч " . ($arr_td[1] + 0) . "м";
        } else {
            if ($arr_td[0] + 0 < 10) {
                $td = ' ' . ($arr_td[0] + 0) . "ч " . ($arr_td[1] + 0) . "м";
            } else {
                $td = ($arr_td[0] + 0) . "ч " . ($arr_td[1] + 0) . "м";
            }
        }
    }
    return $td;
}

function date2mysql($d) {
    $d = explode('.', $d);
    return "{$d[2]}-{$d[1]}-{$d[0]}";
}

function cache_remove_rule($rule_name) {
    if ($rule_name == '') {
        return;
    }

    $rule_hash = md5($rule_name);

    $rules_path = _CACHE_PATH . "/rules";
    $rule_path = "{$rules_path}/.{$rule_hash}";

    unlink($rule_path);
}

/**
 * Полный пересчёт кэша правил: берём данные из базы и отправляем по всем трекерам
 */
function cache_rules_update($link_name = '', $old_name = '') {
    global $_DB_LOGIN, $_DB_PASSWORD, $_DB_NAME, $_DB_HOST, $tracklist;

    // Connect to DB
    mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " . mysql_error());
    mysql_select_db($_DB_NAME);
    db_query('SET NAMES utf8');
    mysql_query("SET @@GLOBAL.sql_mode= ''");
    mysql_query("SET @@SESSION.sql_mode= ''");    

    $rules = array(); // маccив с правилами
    $rules_md5 = array(); // массив правил, подготовленных для кэширования

    $out = array(
        'status' => 1, // Всё хорошо
    );

    // Get cache strings

    $q = "select tbl_rules.id as rule_id, tbl_rules.link_name, tbl_rules_items.id, tbl_rules_items.parent_id, tbl_rules_items.type, tbl_rules_items.value 
        from tbl_rules 
        left join tbl_rules_items on tbl_rules_items.rule_id=tbl_rules.id 
        where tbl_rules.status=0 and tbl_rules_items.status=0 " . (empty($link_name) ? '' : " and `link_name` = '" . $link_name . "'") . "
        order by tbl_rules_items.parent_id, tbl_rules_items.id";
    $rs = db_query($q);
    if (mysql_num_rows($rs) > 0) {
        while ($row = mysql_fetch_assoc($rs)) {
            //$rule_id = $row['rule_id'];
            //$arr_items[$row['id']] = $row;
            $rules[$row['link_name']][$row['id']] = $row;
        }

        // name -> md5
        foreach ($rules as $rule_name => $arr_items) {
            $rules_md5[md5($rule_name)] = $arr_items;
        }

        // Обновляем все правила или одно конкретное с названием $link_name
        $act = empty($link_name) ? 'rules_update' : 'rule_update';

        // У нас единственное правило и оно не найдено, а значит удалено
    } elseif (!empty($link_name)) {
        $act = 'rule_update';
        $rules_md5[md5($link_name)] = '';
    }

    // Это старое имя, его надо удалить (костылик для переименования правила)
    if ($old_name != '') {
        $rules_md5[md5($old_name)] = '';
    }

    // Send to all tracks
    $out = send2trackers($act, $rules_md5);

    return $out;
}

function cache_set_rule($rule_name) {
    global $_DB_LOGIN, $_DB_PASSWORD, $_DB_NAME, $_DB_HOST;

    if ($rule_name == '') {
        return;
    }
    $rule_hash = md5($rule_name);

    $rules_path = _CACHE_PATH . "/rules";
    $rule_path = "{$rules_path}/.{$rule_hash}";

    // Connect to DB
    mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " . mysql_error());
    mysql_select_db($_DB_NAME);
    db_query('SET NAMES utf8');
    mysql_query("SET @@GLOBAL.sql_mode= ''");
    mysql_query("SET @@SESSION.sql_mode= ''");    

    $sql = "select tbl_rules.id as rule_id, tbl_rules_items.id, tbl_rules_items.parent_id, tbl_rules_items.type, tbl_rules_items.value from tbl_rules left join tbl_rules_items on tbl_rules_items.rule_id=tbl_rules.id 
		where tbl_rules.link_name='" . mysql_real_escape_string($rule_name) . "' 
			and tbl_rules.status=0 and tbl_rules_items.status=0 
		order by tbl_rules_items.parent_id, tbl_rules_items.id";
    $result = db_query($sql);

    $arr_items = array();
    $rule_id = 0;
    while ($row = mysql_fetch_assoc($result)) {
        $rule_id = $row['rule_id'];
        $arr_items[$row['id']] = $row;
    }

    if (count($arr_items) == 0) {
        return array();
    }

    $arr_rules = array();
    $i = 1;
    foreach ($arr_items as $row) {
        if ($row['parent_id'] > 0) {
            $arr_rules[$arr_items[$row['parent_id']]['type']][] = array('value' => $arr_items[$row['parent_id']]['value'], 'rule_id' => $rule_id, 'out_id' => $row['value'], 'order' => $i);
            $i++;
        }
    }
    $str_rules = serialize($arr_rules);

    if (!is_dir($rules_path)) {
        mkdir($rules_path);
        chmod($rules_path, 0777);
    }

    if (is_writable($rules_path)) {
        file_put_contents($rule_path, $str_rules);
        chmod($rule_path, 0777);
    }
    return $arr_rules;
}

function offers_have_status($status) {
    $q = "select status 
        from `tbl_offers` 
        where `status` = '" . intval($status) . "' 
        limit 1";
    if ($rs = db_query($q) and mysql_num_rows($rs) > 0) {
        return 1;
    }
    return 0;
}

function sources_favorits() {
    $out = array();
    $q = "select `id` from `tbl_sources`";
    $rs = db_query($q);
    while ($r = mysql_fetch_assoc($rs)) {
        $out[] = $r['id'];
    }
    return $out;
}

function get_links_categories_list() {
    // Get links count for categories
    $sql = "select tbl_links_categories_list.id as `category_id`, count(tbl_offers.id) as cnt 
        from tbl_offers 
        left join tbl_links_categories on tbl_links_categories.offer_id = tbl_offers.id 
        left join tbl_links_categories_list on tbl_links_categories.category_id = tbl_links_categories_list.id 
            and tbl_links_categories_list.status = '0'
        where (tbl_offers.status = 0 or tbl_offers.status = 3) 
            and tbl_offers.network_id = 0 
        group by tbl_links_categories_list.id";
    $result = db_query($sql);
    $arr_categories_count = array();
    while ($row = mysql_fetch_assoc($result)) {
        if (empty($row['category_id'])) {
            $arr_categories_count[0] = $row['cnt'];
        } else {
            $arr_categories_count[$row['category_id']] = $row['cnt'];
        }
    }

    $sql = "SELECT * FROM `tbl_links_categories_list` where status=0 order by category_type, category_caption";
    $result = db_query($sql);
    $arr_data = array();
    while ($row = mysql_fetch_assoc($result)) {
        $arr_data[] = $row;
    }
    return array(
        'categories' => $arr_data,
        'categories_count' => $arr_categories_count
    );
}

function import_sale_info($lead_type, $amount, $subid) {
    $sql = "select id from tbl_conversions where subid='" . _str($subid) . "' and type='" . _str($lead_type) . "'";
    $result = db_query($sql) or die(mysql_error());
    $row = mysql_fetch_assoc($result);

    if ($row['id'] > 0) {
        $id = $row['id'];
        $sql = "update tbl_conversions set profit='" . _str($amount) . "', date_add=NOW() where id='" . _str($id) . "'";
        db_query($sql) or die(mysql_error());
    } else {
        $sql = "insert into tbl_conversions (profit, subid, date_add, type) values ('" . _str($amount) . "', '" . _str($subid) . "', NOW(), '" . _str($lead_type) . "') ON DUPLICATE KEY UPDATE `date_add` = NOW()";
        db_query($sql) or die(mysql_error());
    }

    switch ($lead_type) {
        case 'sale':
            $sql = "update tbl_clicks set conversion_price_main='" . _str($amount) . "', is_sale='1' where subid='" . _str($subid) . "'";
            break;

        case 'lead':
            $sql = "update tbl_clicks set is_lead='1' where subid='" . _str($subid) . "'";
            break;
    }
    db_query($sql) or die(mysql_error());

    return;
}

function import_hasoffers_links($network_id) {
    $sql = "select network_api_url, api_key from tbl_cpa_networks where id='" . mysql_real_escape_string($network_id) . "'";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);
    if ($row['api_key'] == '') {
        return array(false, 'API_KEY_EMPTY');
    }
    $link = str_replace('{API_KEY}', $row['api_key'], $row['network_api_url']);

    $offers_data = file_get_contents($link);

    $arr_offers = array();
    $arr_offers = json_decode($offers_data);
    if (is_array($arr_offers) && count($arr_offers) > 0) {
        return array(false, 'JSON_EMPTY');
    }
    if ($arr_offers->success != 1) {
        return array(false, 'API_RETURNED_FALSE');
    }

    $offers_total = 0;
    $offers_added = 0;
    $offers_empty_id = 0;
    $offers_already_added = 0;

    foreach ($arr_offers->data as $offer_info) {
        foreach ($offer_info as $cur_offer) {
            $offer_data = array();
            $offer_data['network_id'] = $network_id;
            $offer_data['offer_id'] = $cur_offer->id;
            $offer_name = $cur_offer->name;
            if ($offer_name == '') {
                if ($cur_offer->id == '') {
                    $offer_data['offer_name'] = "Без названия";
                } else {
                    $offer_data['offer_name'] = "Оффер #{$cur_offer->id}";
                }
            } else {
                $offer_data['offer_name'] = $offer_name;
            }
            $offer_data['offer_description'] = $cur_offer->description;
            $offer_data['offer_payout_type'] = $cur_offer->payout_type;
            $offer_data['offer_payout'] = $cur_offer->payout;
            $offer_data['offer_payout_currency'] = $cur_offer->currency;
            $offer_data['offer_expiration_date'] = $cur_offer->expiration_date;
            $offer_data['offer_preview_url'] = $cur_offer->preview_url;

            // Append SUBID to tracking url
            $offer_data['offer_tracking_url'] = $cur_offer->tracking_url . '&aff_sub=%SUBID%';
            $arr_offer_comments = array();

            if ($cur_offer->categories != '') {
                $arr_offer_comments[] = "Категория: {$cur_offer->categories}";
            }
            if ($cur_offer->countries != '') {
                $arr_offer_comments[] = "Страны: {$cur_offer->countries}";
            }
            if ($cur_offer->countries_short != '') {
                $arr_offer_comments[] = "Коды стран: {$cur_offer->countries_short}";
            }
            if (count($arr_offer_comments) > 0) {
                $offer_data['offer_comment'] = implode('<br />', $arr_offer_comments);
            } else {
                $offer_data['offer_comment'] = '';
            }

            // Add offer to db
            $result = add_offer($offer_data);
            $offers_total++;
            if ($result[0] == true) {
                $offers_added++;
            } else {
                switch ($result[1]) {
                    case 'EMPTY_ID':
                        $offers_empty_id++;
                        break;

                    case 'ALREADY_ADDED':
                        $offers_already_added++;
                        break;
                }
            }
        }

        $offers_new = $offers_total - $offers_already_added;
    }
    return array(true, "Получено офферов от CPA сети: {$offers_total}, новых: {$offers_new}, добавлено: {$offers_added}, ошибок: {$offers_empty_id}");
}

function add_offer($offer_info) {
    // Check for duplicates if we insert offer for network
    if ($offer_info['network_id'] > 0) {
        if ($offer_info['offer_id'] == '' || $offer_info['offer_id'] == 0) {
            // Empty offer ID for network offer - count as error
            return array(false, 'EMPTY_ID');
        }

        $sql = "select id from tbl_offers where network_id='" . mysql_real_escape_string($offer_info['network_id']) . "' and offer_id='" . mysql_real_escape_string($offer_info['offer_id']) . "'";
        $result = db_query($sql);
        $row = mysql_fetch_assoc($result);
        if ($row['id'] > 0) {
            // Offer was already added
            return array(false, 'ALREADY_ADDED');
        }
    }

    $sql = "insert into tbl_offers(network_id, offer_id, offer_name, offer_description, offer_payout_type, offer_payout, offer_payout_currency, offer_expiration_date, offer_preview_url, offer_tracking_url, offer_comment, date_add) values 
	(
	'" . mysql_real_escape_string($offer_info['network_id']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_id']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_name']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_description']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_payout_type']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_payout']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_payout_currency']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_expiration_date']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_preview_url']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_tracking_url']) . "', 
	'" . mysql_real_escape_string($offer_info['offer_comment']) . "',
	NOW()
	)";
    db_query($sql);
    return array(true);
}

function delete_sale($click_id, $conversion_id, $type) {
    $sql = "delete from tbl_conversions where id='" . _str($conversion_id) . "' and type='" . _str($type) . "'";
    db_query($sql);
    switch ($type) {
        case 'lead':
            $sql = "update tbl_clicks set is_lead='0' where id='" . _str($click_id) . "'";
            db_query($sql);
            break;

        case 'sale':
            $sql = "update tbl_clicks set is_sale='0', conversion_price_main='0' where id='" . _str($click_id) . "'";
            db_query($sql);
            break;

        default:
            $sql = "update tbl_clicks set is_lead='0', is_sale='0', conversion_price_main='0' where id='" . _str($click_id) . "'";
            db_query($sql);
    }
    echo $sql;

    return;
}

function delete_rule($rule_id, $status = 1) {
    // Get rule name
    $q = "select id, link_name from tbl_rules where id='" . _str($rule_id) . "'";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    if ($r['id'] > 0) {
        $q = "update tbl_rules set status='" . $status . "' where id='" . _str($rule_id) . "'";
        db_query($q);

        $q = "update tbl_rules_items set status='" . $status . "' where rule_id='" . _str($rule_id) . "'";
        db_query($q);

        // Обновление кэша
        cache_rules_update($row['link_name']);
    }
    return;
}

function show_country_select($selected = '') {
    $arr_countries = array("AD" => array("AD Andorra Андорра", "Андорра"),
        "AE" => array("AE UAE الإمارات United Arab Emirates ОАЭ", "ОАЭ"),
        "AF" => array("AF افغانستان Afghanistan Афганистан", "Афганистан"),
        "AG" => array("AG Antigua And Barbuda Антигуа и Барбуда", "Антигуа и Барбуда"),
        "AI" => array("AI Anguilla Ангилья", "Ангилья"),
        "AL" => array("AL Albania Албания", "Албания"),
        "AM" => array("AM Հայաստան Armenia Армения", "Армения"),
        "AO" => array("AO Angola Ангола", "Ангола"),
        "AQ" => array("AQ Antarctica Антарктида", "Антарктида"),
        "AR" => array("AR Argentina Аргентина", "Аргентина"),
        "AS" => array("AS American Samoa Американское Самоа", "Американское Самоа"),
        "AT" => array("AT Österreich Osterreich Oesterreich  Austria Австрия", "Австрия"),
        "AU" => array("AU Australia Австралия", "Австралия"),
        "AW" => array("AW Aruba Аруба", "Аруба"),
        "AX" => array("AX Aaland Aland Åland Islands Аландские острова", "Аландские острова"),
        "AZ" => array("AZ Azerbaijan Азербайджан", "Азербайджан"),
        "BA" => array("BA Босна и Херцеговина Bosnia and Herzegovina Босния и Герцеговина", "Босния и Герцеговина"),
        "BB" => array("BB Barbados Барбадос", "Барбадос"),
        "BD" => array("BD বাংলাদেশ Bangladesh Бангладеш", "Бангладеш"),
        "BE" => array("BE België Belgie Belgien Belgique Belgium Бельгия", "Бельгия"),
        "BF" => array("BF Burkina Faso Буркина-Фасо", "Буркина-Фасо"),
        "BG" => array("BG България Bulgaria Болгария", "Болгария"),
        "BH" => array("BH البحرين Bahrain Бахрейн", "Бахрейн"),
        "BI" => array("BI Burundi Бурунди", "Бурунди"),
        "BJ" => array("BJ Benin Бенин", "Бенин"),
        "BL" => array("BL St. Barthelemy Saint Barthélemy Сен-Бартелеми", "Сен-Бартелеми"),
        "BM" => array("BM Bermuda Бермуды", "Бермуды"),
        "BN" => array("BN Brunei Darussalam Бруней", "Бруней"),
        "BO" => array("BO Bolivia Боливия", "Боливия"),
        "BQ" => array("BQ Bonaire, Sint Eustatius and Saba Бонэйр, Синт-Эстатиус и Саба", "Бонэйр, Синт-Эстатиус и Саба"),
        "BR" => array("BR Brasil Brazil Бразилия", "Бразилия"),
        "BS" => array("BS Bahamas Багамы", "Багамы"),
        "BT" => array("BT भूटान Bhutan Бутан", "Бутан"),
        "BV" => array("BV Bouvet Island Остров Буве", "Остров Буве"),
        "BW" => array("BW Botswana Ботсвана", "Ботсвана"),
        "BY" => array("BY Беларусь Belarus Белоруссия", "Белоруссия"),
        "BZ" => array("BZ Belize Белиз", "Белиз"),
        "CA" => array("CA Canada Канада", "Канада"),
        "CC" => array("CC Cocos (Keeling) Islands Кокосовые острова", "Кокосовые острова"),
        "CD" => array("CD Congo-Brazzaville Repubilika ya Kongo Congo, the Democratic Republic of the ДР Конго", "ДР Конго"),
        "CF" => array("CF Central African Republic ЦАР", "ЦАР"),
        "CG" => array("CG Congo Республика Конго", "Республика Конго"),
        "CH" => array("CH Swiss Confederation Schweiz Suisse Svizzera Svizra Switzerland Швейцария", "Швейцария"),
        "CI" => array("CI Cote dIvoire Côte d'Ivoire Кот-д’Ивуар", "Кот-д’Ивуар"),
        "CK" => array("CK Cook Islands Острова Кука", "Острова Кука"),
        "CL" => array("CL Chile Чили", "Чили"),
        "CM" => array("CM Cameroon Камерун", "Камерун"),
        "CN" => array("CN Zhongguo Zhonghua Peoples Republic 中国/中华 China КНР", "КНР"),
        "CO" => array("CO Colombia Колумбия", "Колумбия"),
        "CR" => array("CR Costa Rica Коста-Рика", "Коста-Рика"),
        "CU" => array("CU Cuba Куба", "Куба"),
        "CV" => array("CV Cabo Cape Verde Кабо-Верде", "Кабо-Верде"),
        "CW" => array("CW Curacao Curaçao Кюрасао", "Кюрасао"),
        "CX" => array("CX Christmas Island Остров Рождества", "Остров Рождества"),
        "CY" => array("CY Κύπρος Kýpros Kıbrıs Cyprus Кипр", "Кипр"),
        "CZ" => array("CZ Česká Ceska Czech Republic Чехия", "Чехия"),
        "DE" => array("DE Bundesrepublik Deutschland Germany Германия", "Германия"),
        "DJ" => array("DJ جيبوتي‎ Jabuuti Gabuuti Djibouti Джибути", "Джибути"),
        "DK" => array("DK Danmark Denmark Дания", "Дания"),
        "DM" => array("DM Dominique Dominica Доминика", "Доминика"),
        "DO" => array("DO Dominican Republic Доминиканская Республика", "Доминиканская Республика"),
        "DZ" => array("DZ الجزائر Algeria Алжир", "Алжир"),
        "EC" => array("EC Ecuador Эквадор", "Эквадор"),
        "EE" => array("EE Eesti Estonia Эстония", "Эстония"),
        "EG" => array("EG Egypt Египет", "Египет"),
        "EH" => array("EH لصحراء الغربية Western Sahara Западная Сахара", "Западная Сахара"),
        "ER" => array("ER إرتريا ኤርትራ Eritrea Эритрея", "Эритрея"),
        "ES" => array("ES España Spain Испания", "Испания"),
        "ET" => array("ET ኢትዮጵያ Ethiopia Эфиопия", "Эфиопия"),
        "FI" => array("FI Suomi Finland Финляндия", "Финляндия"),
        "FJ" => array("FJ Viti फ़िजी Fiji Фиджи", "Фиджи"),
        "FK" => array("FK Falkland Islands (Malvinas) Фолклендские острова", "Фолклендские острова"),
        "FM" => array("FM Micronesia, Federated States of Микронезия", "Микронезия"),
        "FO" => array("FO Føroyar Færøerne Faroe Islands Фарерские острова", "Фарерские острова"),
        "FR" => array("FR République française France Франция", "Франция"),
        "GA" => array("GA République Gabonaise Gabon Габон", "Габон"),
        "GB" => array("GB Great Britain England UK Wales Scotland Northern Ireland United Kingdom Великобритания Англия", "Великобритания"),
        "GD" => array("GD Grenada Гренада", "Гренада"),
        "GE" => array("GE საქართველო Georgia Грузия", "Грузия"),
        "GF" => array("GF French Guiana Гвиана", "Гвиана"),
        "GG" => array("GG Guernsey Гернси", "Гернси"),
        "GH" => array("GH Ghana Гана", "Гана"),
        "GI" => array("GI Gibraltar Гибралтар", "Гибралтар"),
        "GL" => array("GL grønland Greenland Гренландия", "Гренландия"),
        "GM" => array("GM Gambia Гамбия", "Гамбия"),
        "GN" => array("GN Guinea Гвинея", "Гвинея"),
        "GP" => array("GP Guadeloupe Гваделупа", "Гваделупа"),
        "GQ" => array("GQ Equatorial Guinea Экваториальная Гвинея", "Экваториальная Гвинея"),
        "GR" => array("GR Ελλάδα Greece Греция", "Греция"),
        "GS" => array("GS South Georgia and the South Sandwich Islands Южная Георгия и Южные Сандвичевы острова", "Южная Георгия и Южные Сандвичевы острова"),
        "GT" => array("GT Guatemala Гватемала", "Гватемала"),
        "GU" => array("GU Guam Гуам", "Гуам"),
        "GW" => array("GW Guinea-Bissau Гвинея-Бисау", "Гвинея-Бисау"),
        "GY" => array("GY Guyana Гайана", "Гайана"),
        "HK" => array("HK 香港 Hong Kong Гонконг", "Гонконг"),
        "HM" => array("HM Heard Island and McDonald Islands Херд и Макдональд", "Херд и Макдональд"),
        "HN" => array("HN Honduras Гондурас", "Гондурас"),
        "HR" => array("HR Hrvatska Croatia Хорватия", "Хорватия"),
        "HT" => array("HT Haiti Гаити", "Гаити"),
        "HU" => array("HU Magyarország Hungary Венгрия", "Венгрия"),
        "ID" => array("ID Indonesia Индонезия", "Индонезия"),
        "IE" => array("IE Éire Ireland Ирландия", "Ирландия"),
        "IL" => array("IL إسرائيل ישראל Israel Израиль", "Израиль"),
        "IM" => array("IM Isle of Man Остров Мэн", "Остров Мэн"),
        "IN" => array("IN भारत गणराज्य Hindustan India Индия", "Индия"),
        "IO" => array("IO British Indian Ocean Territory Британская территория в Индийском океане", "Британская территория в Индийском океане"),
        "IQ" => array("IQ العراق‎ Iraq Ирак", "Ирак"),
        "IR" => array("IR ایران Iran, Islamic Republic of Иран", "Иран"),
        "IS" => array("IS Island Iceland Исландия", "Исландия"),
        "IT" => array("IT Italia Italy Италия", "Италия"),
        "JE" => array("JE Jersey Джерси", "Джерси"),
        "JM" => array("JM Jamaica Ямайка", "Ямайка"),
        "JO" => array("JO الأردن Jordan Иордания", "Иордания"),
        "JP" => array("JP Nippon Nihon 日本 Japan Япония", "Япония"),
        "KE" => array("KE Kenya Кения", "Кения"),
        "KG" => array("KG Кыргызстан Kyrgyzstan Киргизия", "Киргизия"),
        "KH" => array("KH កម្ពុជា Cambodia Камбоджа", "Камбоджа"),
        "KI" => array("KI Kiribati Кирибати", "Кирибати"),
        "KM" => array("KM جزر القمر Comoros Коморы", "Коморы"),
        "KN" => array("KN St. Saint Kitts and Nevis Сент-Китс и Невис", "Сент-Китс и Невис"),
        "KP" => array("KP North Korea Korea, Democratic People's Republic of КНДР", "КНДР"),
        "KR" => array("KR South Korea Korea, Republic of Республика Корея", "Республика Корея"),
        "KW" => array("KW الكويت Kuwait Кувейт", "Кувейт"),
        "KY" => array("KY Cayman Islands Каймановы острова", "Каймановы острова"),
        "RU" => array("RU Rossiya Российская Россия Russian Federation Россия", "Россия"),
        "KZ" => array("KZ Қазақстан Казахстан Kazakhstan Казахстан", "Казахстан"),
        "LA" => array("LA Lao People's Democratic Republic Лаос", "Лаос"),
        "LB" => array("LB لبنان Lebanon Ливан", "Ливан"),
        "LC" => array("LC St. Saint Lucia Сент-Люсия", "Сент-Люсия"),
        "LI" => array("LI Liechtenstein Лихтенштейн", "Лихтенштейн"),
        "LK" => array("LK ශ්‍රී ලංකා இலங்கை Ceylon Sri Lanka Шри-Ланка", "Шри-Ланка"),
        "LR" => array("LR Liberia Либерия", "Либерия"),
        "LS" => array("LS Lesotho Лесото", "Лесото"),
        "LT" => array("LT Lietuva Lithuania Литва", "Литва"),
        "LU" => array("LU Luxembourg Люксембург", "Люксембург"),
        "LV" => array("LV Latvija Latvia Латвия", "Латвия"),
        "LY" => array("LY ليبيا Libyan Arab Jamahiriya Ливия", "Ливия"),
        "MA" => array("MA المغرب Morocco Марокко", "Марокко"),
        "MC" => array("MC Monaco Монако", "Монако"),
        "MD" => array("MD Moldova, Republic of Молдавия", "Молдавия"),
        "ME" => array("ME Montenegro Черногория", "Черногория"),
        "MF" => array("MF St. Saint Martin (French Part) Сен-Мартен", "Сен-Мартен"),
        "MG" => array("MG Madagasikara Madagascar Мадагаскар", "Мадагаскар"),
        "MH" => array("MH Marshall Islands Маршалловы Острова", "Маршалловы Острова"),
        "MK" => array("MK Македонија Macedonia, The Former Yugoslav Republic Of Македония", "Македония"),
        "ML" => array("ML Mali Мали", "Мали"),
        "MM" => array("MM Myanmar Мьянма", "Мьянма"),
        "MN" => array("MN Mongγol ulus Монгол улс Mongolia Монголия", "Монголия"),
        "MO" => array("MO Macao Макао", "Макао"),
        "MP" => array("MP Northern Mariana Islands Северные Марианские острова", "Северные Марианские острова"),
        "MQ" => array("MQ Martinique Мартиника", "Мартиника"),
        "MR" => array("MR الموريتانية Mauritania Мавритания", "Мавритания"),
        "MS" => array("MS Montserrat Монтсеррат", "Монтсеррат"),
        "MT" => array("MT Malta Мальта", "Мальта"),
        "MU" => array("MU Mauritius Маврикий", "Маврикий"),
        "MV" => array("MV Maldives Мальдивы", "Мальдивы"),
        "MW" => array("MW Malawi Малави", "Малави"),
        "MX" => array("MX Mexicanos Mexico Мексика", "Мексика"),
        "MY" => array("MY Malaysia Малайзия", "Малайзия"),
        "MZ" => array("MZ Moçambique Mozambique Мозамбик", "Мозамбик"),
        "NA" => array("NA Namibië Namibia Намибия", "Намибия"),
        "NC" => array("NC New Caledonia Новая Каледония", "Новая Каледония"),
        "NE" => array("NE Nijar Niger Нигер", "Нигер"),
        "NF" => array("NF Norfolk Island Остров Норфолк", "Остров Норфолк"),
        "NG" => array("NG Nijeriya Naíjíríà Nigeria Нигерия", "Нигерия"),
        "NI" => array("NI Nicaragua Никарагуа", "Никарагуа"),
        "NL" => array("NL Holland Nederland Netherlands Нидерланды", "Нидерланды"),
        "NO" => array("NO Norge Noreg Norway Норвегия", "Норвегия"),
        "NP" => array("NP नेपाल Nepal Непал", "Непал"),
        "NR" => array("NR Naoero Nauru Науру", "Науру"),
        "NU" => array("NU Niue Ниуэ", "Ниуэ"),
        "NZ" => array("NZ Aotearoa New Zealand Новая Зеландия", "Новая Зеландия"),
        "OM" => array("OM عمان Oman Оман", "Оман"),
        "PA" => array("PA Panama Панама", "Панама"),
        "PE" => array("PE Peru Перу", "Перу"),
        "PF" => array("PF Polynésie française French Polynesia Французская Полинезия", "Французская Полинезия"),
        "PG" => array("PG Papua New Guinea Папуа — Новая Гвинея", "Папуа — Новая Гвинея"),
        "PH" => array("PH Pilipinas Philippines Филиппины", "Филиппины"),
        "PK" => array("PK پاکستان Pakistan Пакистан", "Пакистан"),
        "PL" => array("PL Polska Poland Польша", "Польша"),
        "PM" => array("PM St. Saint Pierre and Miquelon Сен-Пьер и Микелон", "Сен-Пьер и Микелон"),
        "PN" => array("PN Pitcairn Острова Питкэрн", "Острова Питкэрн"),
        "PR" => array("PR Puerto Rico Пуэрто-Рико", "Пуэрто-Рико"),
        "PS" => array("PS فلسطين Palestinian Territory, Occupied Государство Палестина", "Государство Палестина"),
        "PT" => array("PT Portuguesa Portugal Португалия", "Португалия"),
        "PW" => array("PW Palau Палау", "Палау"),
        "PY" => array("PY Paraguay Парагвай", "Парагвай"),
        "QA" => array("QA قطر Qatar Катар", "Катар"),
        "RE" => array("RE Reunion Réunion Реюньон", "Реюньон"),
        "RO" => array("RO Rumania Roumania România Romania Румыния", "Румыния"),
        "RS" => array("RS Србија Srbija Serbia Сербия", "Сербия"),
        "RW" => array("RW Rwanda Руанда", "Руанда"),
        "SA" => array("SA السعودية Saudi Arabia Саудовская Аравия", "Саудовская Аравия"),
        "SB" => array("SB Solomon Islands Соломоновы Острова", "Соломоновы Острова"),
        "SC" => array("SC Seychelles Сейшельские Острова", "Сейшельские Острова"),
        "SD" => array("SD السودان Sudan Судан", "Судан"),
        "SE" => array("SE Sverige Sweden Швеция", "Швеция"),
        "SG" => array("SG Singapura  சிங்கப்பூர் குடியரசு 新加坡共和国 Singapore Сингапур", "Сингапур"),
        "SH" => array("SH St. Saint Helena Острова Святой Елены, Вознесения и Тристан-да-Кунья", "Острова Святой Елены, Вознесения и Тристан-да-Кунья"),
        "SI" => array("SI Slovenija Slovenia Словения", "Словения"),
        "SJ" => array("SJ Svalbard and Jan Mayen Шпицберген и Ян-Майен", "Шпицберген и Ян-Майен"),
        "SK" => array("SK Slovenská Slovensko Slovakia Словакия", "Словакия"),
        "SL" => array("SL Sierra Leone Сьерра-Леоне", "Сьерра-Леоне"),
        "SM" => array("SM San Marino Сан-Марино", "Сан-Марино"),
        "SN" => array("SN Sénégal Senegal Сенегал", "Сенегал"),
        "SO" => array("SO الصومال Somalia Сомали", "Сомали"),
        "SR" => array("SR शर्नम् Sarnam Sranangron Suriname Суринам", "Суринам"),
        "SS" => array("SS South Sudan Южный Судан", "Южный Судан"),
        "ST" => array("ST Sao Tome and Principe Сан-Томе и Принсипи", "Сан-Томе и Принсипи"),
        "SV" => array("SV El Salvador Сальвадор", "Сальвадор"),
        "SX" => array("SX Sint Maarten (Dutch Part) Синт-Мартен", "Синт-Мартен"),
        "SY" => array("SY Syria سورية Syrian Arab Republic Сирия", "Сирия"),
        "SZ" => array("SZ weSwatini Swatini Ngwane Swaziland Свазиленд", "Свазиленд"),
        "TC" => array("TC Turks and Caicos Islands Тёркс и Кайкос", "Тёркс и Кайкос"),
        "TD" => array("TD تشاد‎ Tchad Chad Чад", "Чад"),
        "TF" => array("TF French Southern Territories Французские Южные и Антарктические Территории", "Французские Южные и Антарктические Территории"),
        "TG" => array("TG Togolese Togo Того", "Того"),
        "TH" => array("TH ประเทศไทย Prathet Thai Thailand Таиланд", "Таиланд"),
        "TJ" => array("TJ Тоҷикистон Toçikiston Tajikistan Таджикистан", "Таджикистан"),
        "TK" => array("TK Tokelau Токелау", "Токелау"),
        "TL" => array("TL Timor-Leste Восточный Тимор", "Восточный Тимор"),
        "TM" => array("TM Türkmenistan Turkmenistan Туркмения", "Туркмения"),
        "TN" => array("TN تونس Tunisia Тунис", "Тунис"),
        "TO" => array("TO Tonga Тонга", "Тонга"),
        "TR" => array("TR Türkiye Turkiye Turkey Турция", "Турция"),
        "TT" => array("TT Trinidad and Tobago Тринидад и Тобаго", "Тринидад и Тобаго"),
        "TV" => array("TV Tuvalu Тувалу", "Тувалу"),
        "TW" => array("TW 台灣 臺灣 Taiwan, Province of China Китайская Республика", "Китайская Республика"),
        "TZ" => array("TZ Tanzania, United Republic of Танзания", "Танзания"),
        "UA" => array("UA Ukrayina Україна Ukraine Украина", "Украина"),
        "UG" => array("UG Uganda Уганда", "Уганда"),
        "UM" => array("UM United States Minor Outlying Islands Внешние малые острова США", "Внешние малые острова (США)"),
        "US" => array("US USA United States of America United States США", "США"),
        "UY" => array("UY Uruguay Уругвай", "Уругвай"),
        "UZ" => array("UZ Ўзбекистон O'zbekstan O‘zbekiston Uzbekistan Узбекистан", "Узбекистан"),
        "VA" => array("VA Holy See (Vatican City State) Ватикан", "Ватикан"),
        "VC" => array("VC St. Saint Vincent and the Grenadines Сент-Винсент и Гренадины", "Сент-Винсент и Гренадины"),
        "VE" => array("VE Venezuela Венесуэла", "Венесуэла"),
        "VG" => array("VG Virgin Islands, British Британские Виргинские острова", "Британские Виргинские острова"),
        "VI" => array("VI Virgin Islands, U.S. Американские Виргинские острова", "Американские Виргинские острова"),
        "VN" => array("VN Việt Nam Vietnam Вьетнам", "Вьетнам"),
        "VU" => array("VU Vanuatu Вануату", "Вануату"),
        "WF" => array("WF Wallis and Futuna Уоллис и Футуна", "Уоллис и Футуна"),
        "WS" => array("WS Samoa Самоа", "Самоа"),
        "YE" => array("YE اليمن Yemen Йемен", "Йемен"),
        "YT" => array("YT Mayotte Майотта", "Майотта"),
        "ZA" => array("ZA RSA Suid-Afrika South Africa ЮАР", "ЮАР"),
        "ZM" => array("ZM Zambia Замбия", "Замбия"),
        "ZW" => array("ZW Zimbabwe Зимбабве", "Зимбабве"));

    $arr_relevancy = array("RU" => '3', "UA" => '3', "BY" => '3', "US" => '2.5', "AM" => '1.4', "AZ" => '1.4', "GE" => '1.4', "KG" => '1.4', "KZ" => '1.4', "TJ" => '1.4', "UZ" => '1.4', "AR" => '1.2', "AT" => '1.2', "AU" => '1.2', "BE" => '1.2', "CA" => '1.2', "CH" => '1.2', "CZ" => '1.2', "DE" => '1.2', "DK" => '1.2', "EE" => '1.2', "ES" => '1.2', "FI" => '1.2', "FR" => '1.2', "GB" => '1.2', "IL" => '1.2', "IE" => '1.2', "IT" => '1.2', "NL" => '1.2', "NO" => '1.2', "NZ" => '1.2', "PL" => '1.2', "PT" => '1.2', "SE" => '1.2', "LT" => '1.2', "LV" => '1.2', "RO" => '1.2', "BR" => '1.1', "HR" => '1.1', "HU" => '1.1', "IN" => '1.1', "MD" => '1.1', "SI" => '1.1', "SK" => '1.1', "TR" => '1.1');

    if ($selected == 'NO_CLASS') {
        $selected = '';
        echo "<select class='new-country-selector' name='rule_country[]' autocorrect='off' autocomplete='off'>";
    } else {
        echo "<select class='country-selector' name='rule_country[]' autocorrect='off' autocomplete='off'>";
    }

    if ($selected == '') {
        $class = 'selected';
    } else {
        $class = '';
    }
    echo "<option {$class} value=''>Выберите страну</option>";
    foreach ($arr_countries as $country_code => $arr) {
        if (isset($arr_relevancy[$country_code])) {
            $booster = " data-relevancy-booster={$arr_relevancy[$country_code]}";
        } else {
            $booster = '';
        }

        if ($selected == $country_code) {
            $class = 'selected';
        } else {
            $class = '';
        }
        echo "<option {$class} {$booster} value='{$country_code}' data-alternative-spellings='{$arr[0]}'>{$arr[1]}</option>";
    }
    echo "</select>";
}

function get_excel_report($date) {
    $timezone_shift = get_current_timezone_shift();
    $sql = "select tbl_offers.offer_name, CONVERT_TZ(tbl_clicks.date_add, '+00:00', '" . _str($timezone_shift) . "') as date_add, tbl_clicks.user_ip, tbl_clicks.user_agent, tbl_clicks.user_os, tbl_clicks.user_platform, tbl_clicks.user_browser, tbl_clicks.country, tbl_clicks.subid, tbl_clicks.source_name, tbl_clicks.campaign_name, tbl_clicks.ads_name, tbl_clicks.referer, tbl_clicks.conversion_price_main from tbl_clicks left join tbl_offers on tbl_offers.id=tbl_clicks.out_id where CONVERT_TZ(tbl_clicks.date_add, '+00:00', '" . _str($timezone_shift) . "') BETWEEN '" . mysql_real_escape_string($date) . " 00:00:00' AND '" . mysql_real_escape_string($date) . " 23:59:59'";

    $result = db_query($sql);
    $arr_data = array();
    while ($row = mysql_fetch_assoc($result)) {
        $arr_data[] = $row;
    }
    return $arr_data;
}

function get_timezone_settings() {
    $sql = "select tbl_timezones.* from tbl_timezones where tbl_timezones.status=0 order by tbl_timezones.id asc";
    $result = db_query($sql);
    $arr_data = array();
    while ($row = mysql_fetch_assoc($result)) {
        $arr_data[] = $row;
    }
    return $arr_data;
}

function get_current_timezone_shift($simple = false) {
    $timezone_shift = '+00:00';
    $sql = "select tbl_timezones.timezone_offset_h from tbl_timezones where tbl_timezones.status=0 and tbl_timezones.is_active=1";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);

    if ($simple) {
        return $row['timezone_offset_h'] * 3600;
    }

    if ($row['timezone_offset_h'] != '') {
        if ($row['timezone_offset_h'] >= 0) {
            $timezone_shift = sprintf("+%02d:00", $row['timezone_offset_h']);
        } else {
            $timezone_shift = sprintf("%03d:00", $row['timezone_offset_h']);
        }
    }
    return $timezone_shift;
}

function change_current_timezone($id) {
    if (($id + 0) > 0) {
        $sql = "update tbl_timezones set is_active=0";
        db_query($sql);

        $sql = "update tbl_timezones set is_active=1 where id='" . mysql_real_escape_string($id) . "'";
        db_query($sql);
    } else {
        return;
    }
}

function add_timezone($name, $offset_h) {
    if (strlen($name) == 0 || strlen($offset_h) == 0) {
        return;
    }
    $sql = "insert into tbl_timezones (timezone_name, timezone_offset_h) values ('" . mysql_real_escape_string($name) . "', '" . mysql_real_escape_string($offset_h) . "')";
    db_query($sql);

    $sql = "select count(id) as cnt from tbl_timezones where status=0";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);
    if ($row['cnt'] == 1) {
        $sql = "update tbl_timezones set is_active=1 where status=0";
        db_query($sql);
    }
}

function update_timezone($name, $offset_h, $id) {
    if (strlen($name) == 0 || strlen($offset_h) == 0 || strlen($id) == 0 || $id <= 0) {
        return;
    }
    $sql = "update tbl_timezones set timezone_name='" . mysql_real_escape_string($name) . "', timezone_offset_h='" . mysql_real_escape_string($offset_h) . "' where id='" . mysql_real_escape_string($id) . "'";
    db_query($sql);
}

function delete_timezone($id) {
    if (strlen($id) == 0 || $id <= 0) {
        return;
    }
    $sql = "select is_active from tbl_timezones where id='" . mysql_real_escape_string($id) . "'";
    $result = db_query($sql);
    $row = mysql_fetch_assoc($result);
    $was_active = ($row['is_active'] == 1);

    $sql = "update tbl_timezones set status=1, is_active=0 where id='" . mysql_real_escape_string($id) . "'";
    db_query($sql);

    if ($was_active) {
        $sql = "select id from tbl_timezones where status=0 order by id asc limit 1";
        $result = db_query($sql);
        $row = mysql_fetch_assoc($result);
        $id = $row['id'];
        if ($id > 0) {
            $sql = "update tbl_timezones set is_active=1 where id='$id'";
            db_query($sql);
        }
    }
}

function timezone_shift_invert($tz)
{
    if (substr($tz, 0, 1)=='+')
    {
        return str_replace('+', '-', $tz);
    }
    else
    {
        return str_replace('-', '+', $tz);
    }
}

function get_rules_offers() {
    $arr_offers = array();

    $sql = "select tbl_offers.*, tbl_links_categories_list.category_caption 
		from tbl_offers 
		left join tbl_links_categories on tbl_links_categories.offer_id = tbl_offers.id 
		left join tbl_links_categories_list on tbl_links_categories_list.id = tbl_links_categories.category_id 
                    and tbl_links_categories_list.status = '0'
		where tbl_offers.status in (0, 3) 
		order by tbl_links_categories_list.category_caption asc, tbl_offers.date_add desc";
    $result = db_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $arr_offers[$row['id']] = $row;
    }
    return $arr_offers;
}

function get_rules_list($arr_offers, $offset = 0, $limit = 50) {
    $arr_rules = array();
    $q = "SELECT tbl_rules.id AS rule_id, tbl_rules.link_name, tbl_rules_items.id AS rule_item_id, tbl_rules_items.parent_id, tbl_rules_items.type, tbl_rules_items.value 
        FROM tbl_rules 
        LEFT JOIN tbl_rules_items ON tbl_rules_items.rule_id = tbl_rules.id 
        WHERE tbl_rules.status = 0 AND tbl_rules_items.status = 0 
        ORDER BY rule_id desc, tbl_rules_items.parent_id ASC, rule_item_id ASC";
    $rs = db_query($q);
    $cur_rule_id = '';
    $i = 0;
    while ($row = mysql_fetch_assoc($rs)) {
        if ($cur_rule_id != $row['rule_id']) {
            $cur_rule_id = $row['rule_id'];
            $arr_rules[$row['rule_id']] = array('id' => $row['rule_id'], 'name' => $row['link_name']);
        }

        if ($row['parent_id'] == 0) {
            $arr_rules[$row['rule_id']]['items'][$row['rule_item_id']]['root'] = $row;
        } else {
            $arr_rules[$row['rule_id']]['items'][$row['parent_id']]['inner'][] = $row;
        }

        switch ($row['type']) {
            case 'redirect':
                $arr_rules[$row['rule_id']]['redirects'][$row['value']] = $arr_offers[$row['value']]['offer_name'];
                break;
        }
    }

    $total = count($arr_rules);
    $arr_rules = array_slice($arr_rules, $offset, $limit);

    return array('rules' => $arr_rules, 'total' => $total);
}

function declination($number, $titles, $show_number = true) {
    $cases = array(2, 0, 1, 1, 1, 2);
    return ($show_number ? $number . " " : '') . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function convert_to_usd($from_currency, $amount) {
    global $currencies;
    switch ($from_currency) {
        case 'rub':
            return $amount / $currencies['rub'];
            break;

        case 'usd':
            return $amount;
            break;

        case 'uah':
            return $amount / $currencies['uah'];
            break;

        default:
            return $amount;
            break;
    }
}

function send_post_request($url, $data) {
    $result = array(false, 'Unknown error');
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_TIMEOUT, 10);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));

    if ($out = curl_exec($c)) {
        $result = array('true', $out);
    } else {
        $result = array('false', curl_error($c));
    }
    curl_close($c);
    /*
      try
      {
      $options = array(
      'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'POST',
      'content' => http_build_query($data),
      ),
      );
      $context = stream_context_create($options);
      $result  = file_get_contents($url, false, $context);
      if ($result===false)
      {
      $result=array('false', "Can't connect to host");
      }
      else
      {
      $result=array('true', $result);
      }
      }
      catch (Exception $e)
      {
      $result=array(false, $e->getMessage());
      }
     */
    return $result;
}

function get_sources_data_js() {
    $i = 1;
    global $source_types, $source_config;
    $arr_data = array();

    foreach ($source_types as $type_val) {
        if ($type_val['name'] == '') {
            $tmp = array(array('id' => 'source', 'text' => 'Основная ссылка'));
        } else {
            $tmp = array();
        }
        foreach ($type_val['values'] as $v) {
            $tmp[] = array('id' => $v, 'text' => $source_config[$v]['name']);
            $i++;
        }
        $arr_data[] = array('text' => $type_val['name'] . ' ', 'children' => $tmp);
    }
    return json_encode($arr_data); //, JSON_UNESCAPED_UNICODE
}

function get_offers_data_js($arr_offers) {
    $arr_data = array();
    $i = 0;
    $cur_category_name = '{n/a}';
    $last_offer_id = current(array_keys($arr_offers));
    foreach ($arr_offers as $cur) {
        if ($cur['category_caption'] != $cur_category_name) {
            if ($cur_category_name != '{n/a}') {
                $i++;
            }
            $arr_data[$i]['optgroup'] = $cur['category_caption'];
            $cur_category_name = $cur['category_caption'];
        }
        $arr_data[$i]['data'][] = array($cur['id'], $cur['offer_name']);
    }

    $str = array();

    foreach ($arr_data as $cur) {
        $tmp = array();
        foreach ($cur['data'] as $cur_item) {
            $tmp[] = array('id' => $cur_item[0], 'text' => $cur_item[1]);
        }
        $str[] = json_encode(array(
            'text' => $cur['optgroup'] . ' ',
            'children' => $tmp
                ));
    }

    return (array(_e($last_offer_id), implode(',', $str)));
}

function get_countries_list_rus() {
    $arr_countries = array("AU" => "Австралия, AU", "AT" => "Австрия, AT", "AZ" => "Азербайджан, AZ", "AL" => "Албания, AL", "DZ" => "Алжир, DZ", "AO" => "Ангола, AO", "AD" => "Андорра, AD", "AG" => "Антигуа и Барбуда, AG", "AR" => "Аргентина, AR", "AM" => "Армения, AM", "AF" => "Афганистан, AF", "BS" => "Багамы, BS", "BD" => "Бангладеш, BD", "BB" => "Барбадос, BB", "BH" => "Бахрейн, BH", "BY" => "Беларусь, BY", "BZ" => "Белиз, BZ", "BE" => "Бельгия, BE", "BJ" => "Бенин, BJ", "BG" => "Болгария, BG", "BO" => "Боливия, BO", "BA" => "Босния, BA", "BW" => "Ботсвана, BW", "BR" => "Бразилия, BR", "BN" => "Бруней Даруссалам, BN", "BF" => "Буркина Фасо, BF", "BI" => "Бурунди, BI", "BT" => "Бутан, BT", "VU" => "Вануату, VU", "VA" => "Ватикан, VA", "GB" => "Великобритания, GB", "HU" => "Венгрия, HU", "VE" => "Венесуэла, VE", "TL" => "Восточный Тимор, TL", "VN" => "Вьетнам, VN", "GA" => "Габон, GA", "HT" => "Гаити, HT", "GY" => "Гайана, GY", "GM" => "Гамбия, GM", "GH" => "Гана, GH", "GT" => "Гватемала, GT", "GN" => "Гвинея, GN", "GW" => "Гвинея-Биссау, GW", "DE" => "Германия, DE", "HN" => "Гондурас, HN", "GD" => "Гренада, GD", "GR" => "Греция, GR", "GE" => "Грузия, GE", "DK" => "Дания, DK", "DJ" => "Джибути, DJ", "DO" => "Доминиканская Республика, DO", "EG" => "Египет, EG", "CD" => "Заир, CD", "ZM" => "Замбия, ZM", "ZW" => "Зимбабве, ZW", "IL" => "Израиль, IL", "IN" => "Индия, IN", "ID" => "Индонезия, ID", "JO" => "Иордания, JO", "IQ" => "Ирак, IQ", "IR" => "Иран, IR", "IE" => "Ирландия, IE", "IS" => "Исландия, IS", "ES" => "Испания, ES", "IT" => "Италия, IT", "YE" => "Йемен, YE", "KZ" => "Казахстан, KZ", "KH" => "Камбоджа, KH", "CM" => "Камерун, CM", "CA" => "Канада, CA", "QA" => "Катар, QA", "KE" => "Кения, KE", "CY" => "Кипр, CY", "KI" => "Кирибати, KI", "CN" => "Китай, CN", "CO" => "Колумбия, CO", "KM" => "Коморские о-ва, KM", "CG" => "Конго, CG", "XK" => "Косово, XK", "CR" => "Коста-Рика, CR", "CI" => "Кот-д'Ивуар, CI", "CU" => "Куба, CU", "KW" => "Кувейт, KW", "KG" => "Кыргызстан, KG", "LA" => "Лаос, LA", "LV" => "Латвия, LV", "LS" => "Лесото, LS", "LR" => "Либерия, LR", "LB" => "Ливан, LB", "LY" => "Ливия, LY", "LT" => "Литва, LT", "LI" => "Лихтенштейн, LI", "LU" => "Люксембург, LU", "MU" => "Маврикий, MU", "MR" => "Мавритания, MR", "MG" => "Мадагаскар, MG", "MK" => "Македония, MK", "MW" => "Малави, MW", "MY" => "Малайзия, MY", "ML" => "Мали, ML", "MV" => "Мальдивские о-ва, MV", "MT" => "Мальта, MT", "MA" => "Марокко, MA", "MX" => "Мексика, MX", "MZ" => "Мозамбик, MZ", "MD" => "Молдова, MD", "MC" => "Монако, MC", "MN" => "Монголия, MN", "MM" => "Мьянма, MM", "NA" => "Намибия, NA", "NR" => "Науру, NR", "NP" => "Непал, NP", "NE" => "Нигерия, NE", "NG" => "Нигерия, NG", "NL" => "Нидерланды, NL", "NI" => "Никарагуа, NI", "NZ" => "Новая Зеландия, NZ", "NO" => "Норвегия, NO", "AE" => "Объединенные Арабские Эмираты, AE", "OM" => "Оман, OM", "DM" => "Остров Доминика, DM", "CV" => "Острова Зеленого Мыса, CV", "PK" => "Пакистан, PK", "PA" => "Панама, PA", "PG" => "Папуа – Новая Гвинея, PG", "PY" => "Парагвай, PY", "PE" => "Перу, PE", "PL" => "Польша, PL", "PT" => "Португалия, PT", "RU" => "Россия, RU", "RW" => "Руанда, RW", "RO" => "Румыния, RO", "SV" => "Сальвадор, SV", "WS" => "Самоа, WS", "SM" => "Сан-Марино, SM", "ST" => "Сан-Томе и Принсипе, ST", "SA" => "Саудовская Аравия, SA", "SZ" => "Свазиленд, SZ", "KP" => "Северная Корея, KP", "SC" => "Сейшельские о-ва, SC", "SN" => "Сенегал, SN", "VC" => "Сент-Винсент и Гренадины, VC", "KN" => "Сент-Киттс и Невис, KN", "LC" => "Сент-Люсия, LC", "RS" => "Сербия, RS", "SG" => "Сингапур, SG", "SY" => "Сирийская Арабская Республика, SY", "SK" => "Словакия, SK", "SI" => "Словения, SI", "SB" => "Соломонские острова, SB", "SO" => "Сомали, SO", "SD" => "Судан, SD", "SR" => "Суринам, SR", "US" => "США, US", "SL" => "Сьерра-Леоне, SL", "TJ" => "Таджикистан, TJ", "TW" => "Тайвань, TW", "TH" => "Тайланд, TH", "TZ" => "Танзания, TZ", "TG" => "Того, TG", "TO" => "Тонга, TO", "TT" => "Тринидад и Тобаго, TT", "TV" => "Тувалу, TV", "TN" => "Тунис, TN", "TM" => "Туркменистан, TM", "TR" => "Турция, TR", "UG" => "Уганда, UG", "UZ" => "Узбекистан, UZ", "UA" => "Украина, UA", "UY" => "Уругвай, UY", "FJ" => "Фиджи, FJ", "PH" => "Филиппины, PH", "FI" => "Финляндия, FI", "FR" => "Франция, FR", "HR" => "Хорватия, HR", "CF" => "ЦАР, CF", "TD" => "Чад, TD", "ME" => "Черногория, ME", "CZ" => "Чешская Республика, CZ", "CL" => "Чили, CL", "CH" => "Швейцария, CH", "SE" => "Швеция, SE", "LK" => "Шри-Ланка, LK", "EC" => "Эквадор, EC", "GQ" => "Экваториальная Гвинея, GQ", "ER" => "Эритрея, ER", "EE" => "Эстония, EE", "ET" => "Эфиопия, ET", "ZA" => "ЮАР, ZA", "KR" => "Южная Корея, KR", "SS" => "Южный Судан, SS", "JM" => "Ямайка, JM", "JP" => "Япония, JP");

    return $arr_countries;
}

function get_lang_list() {
    $arr_langs = array("az" => "Азербайджанский, az", "en" => "Английский, en", "ar" => "Арабский, ar", "be" => "Белорусский, be", "hu" => "Венгерский, hu",
        "vi" => "Вьетнамский, vi", "el" => "Греческий, el", "id" => "Индонезийский, id", "es" => "Испанский, es", "it" => "Итальянский, it", "kk" => "Казахский, kk",
        "zh" => "Китайский, zh", "ko" => "Корейский, ko", "de" => "Немецкий, de", "nl" => "Нидерландский, nl", "pl" => "Польский, pl", "pt" => "Португальский, pt",
        "ps" => "Пушту, ps", "ro" => "Румынский, ro", "ru" => "Русский, ru", "th" => "Тайский, th", "tr" => "Турецкий, tr",
        "uz" => "Узбекский, uz", "uk" => "Украинский, uk", "fr" => "Французский, fr", "hi" => "Хинди, hi", "cs" => "Чешский, cs",
        "ja" => "Японский, ja",);


    return $arr_langs;
}

function get_langs_data_js() {
    $arr_langs = get_lang_list();
    foreach ($arr_langs as $k => $v) {
        $arr_data[] = '{id:"' . $k . '", text:"' . $v . '"}';
    }
    $js_langs_data = '{' . 'text:"", children:[' . implode(',', $arr_data) . ']}';
    return $js_langs_data;
}

function get_countries_data_js() {
    $arr_countries = get_countries_list_rus();
    foreach ($arr_countries as $k => $v) {
        $arr_data[] = '{id:"' . $k . '", text:"' . $v . '"}';
    }
    $js_countries_data = '{' . 'text:"", children:[' . implode(',', $arr_data) . ']}';
    return $js_countries_data;
}

function inputtype($type) {
    switch ($type) {
        case 'referer':
        case 'city':
        case 'region':
        case 'provider':
        case 'ip':
        //case 'os':
        case 'platform':
        case 'browser':
        case 'agent':
            return 1;
            break;

        case 'get':
            return 2;
            break;

        default:
            return null;
            break;
    }
}

/*
 * Получение переменной из POST|GET|REQUEST 
 *
 * @param string $name - имя переменной
 * @param string $type - p|g|r откуда получаем
 * @param int $num - ожидаемый тип данных: 0 - строка, 1 - целое число, 2 - целое положительное, 3 - json, 4 - date YYYY-MM-DD
 * @param mixed $df - значение по умолчанию
 * @return mised
 */

function rq($name, $num = 0, $df = null, $type = 'r') {
    global $_POST, $_GET, $_REQUEST;

    if ($type == 'r') {
        $d = &$_REQUEST;
    } elseif ($type == 'p') {
        $d = &$_POST;
    } elseif ($type == 'g') {
        $d = &$_GET;
    }

    if ($num == 0) { // Значение как есть
        $def = ($df == null ? '' : $df);
        return array_key_exists($name, $d) ? $d[$name] : $def;
    } elseif ($num > 0 and $num < 3) { // Целое число
        $def = ($df == null ? 0 : $df);
        $out = array_key_exists($name, $d) ? intval($d[$name]) : $def;
        return $num == 2 ? abs($out) : $out;
    } elseif ($num == 4) {  // Дата
        $def = ($df === null ? date('Y-m-d') : $df);
        if (array_key_exists($name, $d)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d[$name])) {
                return $d[$name];
            } elseif (preg_match('/^\d{2}\.\d{4}$/', $d[$name])) {
                $tmp = explode('.', $d[$name]);
                return date('Y-m-d', mktime(0, 0, 0, $tmp[0], 1, $tmp[1]));
            } else {
                return $def;
            }
        } else {
            return $def;
        }
    } elseif ($num < 0 and $num > -3) {  // массив через запятую 2,3,4
        $out = array_key_exists($name, $d) ? explode(',', $d[$name]) : array();
        foreach ($out as $k => $v) {
            $out[$k] = ($num == -2) ? abs(intval($v)) : intval($v);
        }
        $out = array_unique($out);
        return $out;
    } else {
        return array_key_exists($name, $d) ? json_decode($d[$name], true) : array();
    }
    return false;
}

/*
 * $a = func()[0] construction
 */

function ap($arr, $n = 0) {
    return $arr[$n];
}

/**
 * Подключение шаблона
 * page - имя шаблона
 * var - массив с переменными
 */
function tpx($page, $var = null) {
    $include_flag = true;
    ob_start();
    require _TRACK_SHOW_COMMON_PATH . '/templates/' . $page . '.inc.php';
    if (isset($vars)) {
        foreach ($vars as $k => $v) {
            if (!isset($var[$k])) {
                echo 'Ошибка в шаблоне <b>' . $page . '</b>, не определена переменная <b>' . $k . '</b>';
            }
        }
    }
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

/**
 * Отладочная информация
 */
function dmp(&$v) {
    echo '<pre>' . print_r($v, true) . '</pre>';
}

/*
  Умное округление в зависимости от величины
 */

function round2($v) {
    $m = $v < 0 ? -1 : 1;
    $v = abs($v);
    if ($v < 1) {
        $size = 4;
    } elseif ($v < 10) {
        $size = 3;
    } else {
        $size = 2;
    }
    return round($v, $size) * $m;
}

function round3($v) {
    if ($v < 0.5) {
        if ($v < 0.0001) {
            return 0;
        } elseif ($v < 0.001) {
            return round($v, 4);
        } elseif ($v < 0.01) {
            return round($v, 3);
        } elseif ($v < 0.1) {
            return round($v, 2);
        } else {
            return round($v, 1);
        }
    } else {
        return round($v);
    }
}

/*
  Это - SubID. Стопудова!
 */

function is_subid($v) {
    return preg_match('/^\d{14}x\d{5}$/', $v);
}

/**
 * 	Формирование запроса на insert
 */
function insertsql($values, $table, $duplicate_update = false, $ignore = false) {
    $values0 = $values;
    foreach ($values as $key => $val) {
        $values[$key] = "'" . _str($val) . "'";
    }
    $sql = "insert " . ($ignore ? 'ignore' : '') . " into `$table` (`" . join("`,`", array_keys($values)) . "`) values (" . join(",", array_values($values)) . ")";

    if ($duplicate_update) {
        $sql .= " ON DUPLICATE KEY UPDATE " . setdefs($values0);
    }
    return $sql;
}

/**
 * 	Формирование запроса на update
 */
function updatesql($values, $table, $idfield = '', $eq = '=') {
    if (empty($values) or !is_array($values))
        return '';

    $sql = "update `$table` set " . setdefs($values, $idfield);
    if ($idfield) {
        $sql .= " where `$idfield` $eq '{$values[$idfield]}'";
    }
    return $sql;
}

/**
 * Сервисная функция для формирование запросов
 */
function setdefs($values, $idfield = '') {
    if (is_array($values)) {
        foreach ($values as $field => $val) {
            $val = mysql_real_escape_string($val);

            if ($field != $idfield)
                $sets[] = "`$field`='$val'";
        }
        return @join(", ", $sets);
    } else {
        return '';
    }
}

/*
 * Милисекунды в формате php5
 */

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/**
 * Небольшая оболочка для выполнения запросов в БД
 */
function db_query($q, $die_on_error = false) {
    global $sql_log, $sql_time;
    //echo $q . '<br>';
    $start = microtime_float();
    $rs = mysql_query($q);
    if (!$rs) {
        $str = $q . "\n" . mysql_error();
        to_log('db_errors', $str);

        if ($die_on_error) {
            die($str);
        }
        return false;
    } else {
        $t = microtime_float() - $start;
        if ($t < 0)
            $t = 0;
        $sql_time += $t;
        $sql_log[] = round($t, 4) . ' ' . $q;
        to_log('db_success', $q . "\n" . round($t, 4));

        return $rs;
    }
}

function to_log($name, $data)
{
    if (defined('_ENABLE_ERROR_LOGS') && _ENABLE_ERROR_LOGS)
    {
        $log_dir = _CACHE_PATH . '/log';
        if (!is_dir($log_dir)) {
            mkdir($log_dir);
            chmod($log_dir, 0777);
        }

        $fp = fopen($log_dir . '/.' . $name . '.log', 'a');
        if (is_array($data))
            $data = print_r($data, true);
        fwrite($fp, date('Y-m-d H:i:s') . "\n" . $data . "\n\n");
        fclose($fp);
    }
}

function delete_offer($ids, $del = 1) {
    if (!is_array($ids))
        $ids = array($ids);
    foreach ($ids as $id) {
        $sql = "update tbl_offers set status='" . $del . "' where id='" . mysql_real_escape_string($id) . "'";
        db_query($sql);
    }
}

function edit_offer($category_id, $link_name, $link_url, $link_id = 0) {
    $link_name = trim(str_replace(array("\r\n", "\r", "\n", "\t"), '', $link_name));
    $link_url = trim(str_replace(array("\r\n", "\r", "\n", "\t"), '', $link_url));

    if (trim($link_url) != '') {
        if (!(strpos($link_url, 'http://') === 0 || strpos($link_url, 'https://') === 0)) {
            $link_url = "http://{$link_url}";
        }

        if ($link_name == '') {
            $link_name = "Оффер #{$link_id}";
        }

        $ins = array(
            'offer_name' => $link_name,
            'offer_tracking_url' => $link_url,
            'date_add' => date('Y-m-d H:i:s'),
        );

        // Add link
        if (empty($link_id)) {
            $q = insertsql($ins, 'tbl_offers');
            db_query($q);
            $link_id = mysql_insert_id();
        } else {
            $ins['id'] = $link_id;
            $q = updatesql($ins, 'tbl_offers', 'id');
            db_query($q);

            $q = "delete from `tbl_links_categories` where `offer_id` = '" . $link_id . "'";
            db_query($q);
        }

        if (!empty($category_id)) {
            // Add link to selected category
            $ins = array(
                'category_id' => $category_id,
                'offer_id' => $link_id
            );
            $q = insertsql($ins, 'tbl_links_categories');
            db_query($q);
        }
    }

    cache_outs_update($link_id);
}

/**
 * Обновление кэша линков в БД
 */
function cache_outs_update($ids = false) {
    global $_DB_LOGIN, $_DB_PASSWORD, $_DB_NAME, $_DB_HOST;

    // Connect to DB
    mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " . mysql_error());
    mysql_select_db($_DB_NAME);
    db_query('SET NAMES utf8');
    mysql_query("SET @@GLOBAL.sql_mode= ''");
    mysql_query("SET @@SESSION.sql_mode= ''");    

    $links = array();

    $q = "select id, offer_tracking_url 
        from tbl_offers
        where (`status` = '0' or `status` = '3')";  // обычные или избранные
    // ids у нас используется только для кэша
    if ($ids !== false) {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $act = 'out_update';
        $q .= " and `id` in (" . join(',', $ids) . ")";

        // Инициализируем пустые ссылки, чтобы отослать их в АПИ с пустым содержимым
        foreach ($ids as $id) {
            $links[$id] = '';
        }
    } else {
        $act = 'outs_update';
    }
    if ($result = db_query($q) and mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $links[$row['id']] = $row['offer_tracking_url'];
        }
    }

    $out = send2trackers($act, $links);
    return $out;
}

/**
 * Отсылка информации на трекер
 */
function api_send($url, $postdata = '') {
    //echo $url;
    //dmp($postdata);
    $c = curl_init();
    //curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-type: multipart/form-data"));
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_TIMEOUT, 10);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($postdata));
    $out = curl_exec($c);

    // Если произошла ошибка - создаём флаг о необходимости синхронизации
    if (empty($out) or curl_error($c) != '') {
        $api_error_marker = _CACHE_PATH . '/.api_connect_error';
        file_put_contents($api_error_marker, '');
    }

    //dmp($out);
    curl_close($c);
    return $out;
}

/**
 * Проверка каталога на существование. Если нет - создаём
 */
function dir_exists($dir) {
    if (!is_dir($dir)) {
        mkdir($dir);
        chmod($dir, 0777);
        return is_dir($dir);
    }
    return true;
}

/**
 * Рассылка информации по трекерам
 */
function send2trackers($name, $data) {
    global $tracklist;
    $out = array(
        'status' => 1,
    );
    $error = array();
    switch ($name) {

        case 'rule_update':  // Обновление ОДНОГО правила
        case 'rules_update': // Обновление ВСЕХ правил
            $rules_cache = array();
            foreach ($data as $rule_name => $arr_items) {
                $i = 1;
                $arr_rules = array();
                foreach ($arr_items as $row) {
                    if ($row['parent_id'] > 0) {
                        $arr_rules[$arr_items[$row['parent_id']]['type']][] = array(
                            'value' => $arr_items[$row['parent_id']]['value'],
                            'rule_id' => $row['rule_id'],
                            'out_id' => $row['value'],
                            'order' => $i
                        );
                        $i++;
                    }
                }
                $str_rules = empty($arr_rules) ? '' : serialize($arr_rules);
                $rules_cache[$rule_name] = $str_rules;
            }

            foreach ($tracklist as $track) {
                $type = substr($track['path'], 0, 4) == 'http' ? 'remote' : 'local';

                // Локальный трекер
                if ($type == 'local') {
                    $rules_path = $track['path'] . '/cache/rules';
                    dir_exists($rules_path);

                    // Записываем новые хеши
                    foreach ($rules_cache as $rule_name => $str_rules) {
                        $path = $rules_path . '/.' . $rule_name;
                        if (file_put_contents($path, $str_rules, LOCK_EX)) {
                            chmod($path, 0777);
                        } else {
                            $error[] = 'Can\'t create file ' . $path;
                        }
                    }

                    // Удаляем кэши, которые есть, но нам их не прислали в обновлениях
                    if ($name == 'rules_update') {
                        $files = dir_files($rules_path);
                        foreach ($files as $f) {
                            if (!array_key_exists(substr($f, 1), $rules_cache)) {
                                unlink($rules_path . '/' . $f);
                            }
                        }
                    }

                    // Удаленный трекер
                } else {
                    $url = $track['path'] . '/api.php?act=ping';
                    $answer_text = api_send($url);
                    $answer = json_decode($answer_text, true);
                    if ($answer['status'] == 1) {

                        // Полное или частичное обновление
                        $url = $track['path'] . '/api.php?act=rules_update' . ($name == 'rules_update' ? '&full=1' : '');

                        $answer_text = api_send($url, array('cache' => $rules_cache, 'key' => $track['key']));
                        $answer = json_decode($answer_text, true);

                        if ($answer['status'] != 1) {
                            if (empty($answer['error'])) {
                                $error[] = $answer['error'];
                            } else {
                                $error[] = 'Unknown error. Answer: ' . $answer_text;
                            }
                        }
                    } else {
                        $str_error = 'Don\'t have access to host ' . $url;

                        if ($answer_text != '') {
                            $str_error .= ' Answer: ' . $answer_text;
                        }

                        $error[] = $str_error;
                    }
                }
            }
            break;

        case 'out_update': // Обновление одной ссылки
        case 'outs_update': // Обновление всех ссылок

            foreach ($tracklist as $track) {
                $type = substr($track['path'], 0, 4) == 'http' ? 'remote' : 'local';
                if ($type == 'local') {
                    $outs_path = $track['path'] . '/cache/outs';
                    dir_exists($outs_path);

                    foreach ($data as $id => $link) {
                        $path = $outs_path . '/.' . $id;
                        if (file_put_contents($path, $link, LOCK_EX)) {
                            chmod($path, 0777);
                        } else {
                            $error[] = 'Can\'t create file ' . $path;
                        }
                    }

                    // Удаляем неактуальные кэши
                    $files = dir_files($outs_path);
                    foreach ($files as $f) {
                        if (!array_key_exists(substr($f, 1), $data)) {
                            unlink($outs_path . '/' . $f);
                        }
                    }
                } else {
                    $url = $track['path'] . '/api.php?act=ping';
                    $answer_text = api_send($url);
                    $answer = json_decode($answer_text, true);
                    if ($answer['status'] == 1) {
                        $url = $track['path'] . '/api.php?act=outs_update' . ($name == 'outs_update' ? '&full=1' : '');
                        $answer_text = api_send($url, array('cache' => $data, 'key' => $track['key']));
                        $answer = json_decode($answer_text, true);

                        if ($answer['status'] != 1) {
                            if (empty($answer['error'])) {
                                $error[] = $answer['error'];
                            } else {
                                $error[] = 'Unknown error';
                            }
                        }
                    } else {
                        $str_error = 'Don\'t have access to host ' . $url . '.';

                        if ($answer_text != '') {
                            $str_error .= ' Answer: ' . $answer_text;
                        }

                        $error[] = $str_error;
                    }
                }
            }

            break;
    }

    if (!empty($error)) {
        $out = array(
            'status' => 0,
            'error' => join("\n", $error)
        );
    }

    return $out;
}

function load_plugin($name, $page = '', $params = null) {
    $html = '';
    $plugin_path = _TRACK_SHOW_COMMON_PATH . '/../../plugins/' . $name . '/index.php';
    if (file_exists($plugin_path)) {
        ob_start();
        require $plugin_path;
        $html = ob_get_contents();
        ob_end_clean();
    }
    return $html;
}

function onlyword($v) {
    return preg_replace("/[^a-zA-Z0-9_-]/u", '', $v);
}

// Convert tracklist
$tracklist = array_merge(
        array(
    array(
        'path' => realpath(_TRACK_MASTER_PATH),
        'key' => '',
    ),
        ), $tracklist
);

/**
 * Ссылка на трекер
 */
function tracklink() {
    global $tracklist;
    if (count($tracklist) > 1) {
        return $tracklist[1]['path'];
    }
    return _HTML_TRACK_PATH;
}

//dmp($tracklist);

function mysql_now() {
    $q = "SELECT NOW() as `now`";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    return $r['now'];
}

function parse_search_refer($refer, $tail = 1) {
    // База данных поисковых систем 
    $search_engines = Array(
        Array("name" => "Картинки.Mail", "pattern" => "go.mail.ru/search_images", "param" => "q="),
        Array("name" => "Mail", "pattern" => "go.mail.ru", "param" => "q="),
        Array("name" => "Google Images", "pattern" => "images.google.", "param" => "q="),
        Array("name" => "Google", "pattern" => "google.", "param" => "q="),
        Array("name" => "Google", "pattern" => "google.", "param" => "as_q="),
        Array("name" => "Live Search", "pattern" => "search.live.com", "param" => "q="),
        Array("name" => "RapidShare Search Engine", "pattern" => "rapidshare-search-engine", "param" => "s="),
        Array("name" => "Rambler", "pattern" => "rambler.ru", "param" => "query="),
        Array("name" => "Rambler", "pattern" => "rambler.ru", "param" => "words="),
        Array("name" => "Yahoo!", "pattern" => "search.yahoo.com", "param" => "p="),
        Array("name" => "Nigma", "pattern" => "nigma.ru/index.php", "param" => "s="),
        Array("name" => "Nigma", "pattern" => "nigma.ru/index.php", "param" => "q="),
        Array("name" => "MSN", "pattern" => "search.msn.com/results", "param" => "q="),
        Array("name" => "Bing", "pattern" => "bing.com/search", "param" => "q="),
        Array("name" => "Ask", "pattern" => "ask.com/web", "param" => "q="),
        Array("name" => "QIP", "pattern" => "search.qip.ru/search", "param" => "query="),
        Array("name" => "RapidAll", "pattern" => "rapidall.com/search.php", "param" => "query="),
        Array("name" => "Яндекс.Картинки", "pattern" => "images.yandex.ru/", "param" => "text="),
        Array("name" => "Яндекс.Mobile", "pattern" => "m.yandex.ru/search", "param" => "query="),
        Array("name" => "Яндекс", "pattern" => "hghltd.yandex.net", "param" => "text="),
        Array("name" => "Яндекс", "pattern" => "yandex.ru", "param" => "text="),
        Array("name" => "Яндекс", "pattern" => "yandex.ua", "param" => "text="),
        Array("name" => "Яндекс", "pattern" => "yandex.kz", "param" => "text="),
        Array("name" => "Яндекс", "pattern" => "yandex.by", "param" => "text="),
        Array("name" => "Avg", "pattern" => "search.avg.com", "param" => "q="),
        Array("name" => "Ukr.net", "pattern" => "search.ukr.net", "param" => "search_query=")
    );

    // Отрезать от ссылки "хвост" 
    $tmp = explode("?", $refer);
    $chk_site = $tmp[0];  // Имя сайта 
    // Разобрать "хвост" на отдельные параметры 
    $params = split("&", implode("&", $tmp));

    $result_engine = "";
    $result_title = $refer;
    $signature_found = false;
    for ($i = 0; $i < count($params); $i++) {
        // Параметр пустой, пропустить 
        if ($params[$i] == "") {
            continue;
        }
        foreach ($search_engines as $engine) {
            // Поиск по всем сигнатурам 
            if (strpos($chk_site, $engine['pattern']) !== false && substr($params[$i], 0, strlen($engine['param'])) == $engine['param']) {
                // Сигнатура найдена 
                $result_title = substr($params[$i], strlen($engine['param']));
                $result_engine = $engine['name'];
                $signature_found = true;
                break;
            }
        }
        // Show first pattern occurence
        if ($signature_found) {
            break;
        }
    }

    if ($result_engine != "") {
        // Привести строку в текстовый вид 
        $str = trim(urldecode($result_title));
        // Если строка в юникоде, то перевести ее в кодировку win1251 
        /*
          if (is_unicode($str))
          {
          // $str=iconv("utf-8","windows-1251",$str);
          }
          else
          {
          $str=iconv('windows-1251','utf-8',$str);
          }
         */
        if ($str != "") {
            // Сформировать строку "Имя поисковой системы: запрос" 
            // $result=$result_engine.": ".$str; 
            $result = $str;
        }
        // Пустой поисковый запрос 
        else {
            $result = "";
        }
    } else {
        $result = "";
    }

    return ($result);
}

/**
 * Получаем данные оферов, все или только количество
 * @param string $cat_type тип категории (архив, избранное или все) 
 * @param int $category_id категория
 * @param int $start смещение
 * @param int $limit максимум строчек в выборке
 * @param int $only_total вернуть только количество, без данных
 * @return array 
 */
function get_offers_list($cat_type = 'all', $category_id = 0, $start = 0, $limit = 1000, $only_total = 0) {
    global $offers_stats_array;
    $offers_stats_array = array();
    $category_name = '{empty}';
    $arr_offers = array();
    $offers_id = array();
    $total = 0;

    if ($category_id > 0) {
        $q = "select id, category_caption, category_name, category_type 
            from tbl_links_categories_list 
            where id='" . intval($category_id) . "'";
        $rs = db_query($q);
        $r = mysql_fetch_assoc($rs);


        if ($r['id'] > 0) {
            $category_name = $r['category_caption'];
        } else {
            return array(
                'error' => 1, // Category id not found
            );
        }

        switch ($r['category_type']) {
            case 'network':
                $page_type = 'network';

                // Get network ID
                $q = "select id 
                    from tbl_cpa_networks 
                    where network_category_name='" . mysql_real_escape_string($row['category_name']) . "'";
                $rs = db_query($q);
                $r = mysql_fetch_assoc($rs);
                $network_id = $r['id'];

                // Только количество
                if ($only_total) {
                    $q = "select count(`status`) as `cnt`
                        from tbl_offers 
                        where network_id='" . mysql_real_escape_string($network_id) . "' 
                            and status in (0,3)";
                    $rs = db_query($q);
                    $r = mysql_fetch_assoc($rs);
                    $total = $r['cnt'];
                } else {
                    // Get list of offers from network
                    $q = "select SQL_CALC_FOUND_ROWS * 
                        from tbl_offers 
                        where network_id='" . mysql_real_escape_string($network_id) . "' 
                            and status in (0,3)
                        order by date_add desc, id asc
                        limit $start, $limit";
                    $rs = db_query($q);
                    $total = total_rows();
                    while ($r = mysql_fetch_assoc($result)) {
                        $arr_offers[] = $r;
                    }
                }

                break;

            default:

                // Только количество
                if ($only_total) {
                    $q = "select count(`status`) as `cnt`
                        from tbl_offers 
                        left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id 
                        where tbl_links_categories.category_id='" . intval($category_id) . "' 
                            and tbl_offers.network_id = '0' 
                            and tbl_offers.status in (0,3)";
                    $rs = db_query($q);
                    $r = mysql_fetch_assoc($rs);
                    $total = $r['cnt'];
                } else {
                    // Get list of offers in category
                    $q = "select SQL_CALC_FOUND_ROWS tbl_offers.*, tbl_links_categories.category_id 
                        from tbl_offers 
                        left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id
                        where tbl_links_categories.category_id='" . intval($category_id) . "' 
                            and tbl_offers.network_id='0' 
                            and tbl_offers.status in (0,3)
                        order by tbl_offers.date_add desc, tbl_offers.id asc
                        limit $start, $limit";
                    $rs = db_query($q);
                    $total = total_rows();
                    while ($r = mysql_fetch_assoc($rs)) {
                        $r['offer_id'] = $r['id'];
                        $r['category_id'] = intval($r['category_id']);
                        $offers_id[] = "'" . mysql_real_escape_string($r['id']) . "'";
                        $arr_offers[] = $r;
                    }
                    $offers_id_str = implode(',', $offers_id);

                    $q = "select out_id, count(id) as cnt 
                        from tbl_clicks 
                        where out_id in ({$offers_id_str}) 
                        group by out_id";
                    $rs = db_query($q);
                    while ($r = mysql_fetch_assoc($rs)) {
                        $offers_stats_array[$r['out_id']] = $r['cnt'];
                    }
                }
                break;
        }
    } else {
        switch ($cat_type) {
            case 'favorits':
                $cond_status = 'tbl_offers.status = 3';
                break;
            case 'archive':
                $cond_status = 'tbl_offers.status = 2';
                break;
            default:
                $cond_status = 'tbl_offers.status IN (0,3) and 
                    (tbl_links_categories.id IS NULL or tbl_links_categories_list.id IS NULL)';
                break;
        }

        // Только количество
        if ($only_total) {
            $q = "select count(tbl_offers.`status`) as `cnt`
                from tbl_offers 
                left join tbl_links_categories on tbl_offers.id = tbl_links_categories.offer_id
                left join tbl_links_categories_list on tbl_links_categories.category_id = tbl_links_categories_list.id 
                    and tbl_links_categories_list.status = '0'
                where tbl_offers.network_id='0' 
                    and " . $cond_status;
            $rs = db_query($q);
            $r = mysql_fetch_assoc($rs);
            $total = $r['cnt'];
        } else {
            // Get list of offers without category
            $q = "select SQL_CALC_FOUND_ROWS tbl_offers.*, tbl_links_categories.category_id  
                from tbl_offers 
                left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id
                left join tbl_links_categories_list on tbl_links_categories.category_id = tbl_links_categories_list.id 
                    and tbl_links_categories_list.status = '0'
                where tbl_offers.network_id='0' 
                    and " . $cond_status . " 
                order by tbl_offers.date_add desc, tbl_offers.id asc
                limit $start, $limit";
            $rs = db_query($q);
            $total = total_rows();
            while ($r = mysql_fetch_assoc($rs)) {
                $r['offer_id'] = $r['id'];
                $r['category_id'] = intval($r['category_id']);
                $offers_id[] = "'" . mysql_real_escape_string($r['id']) . "'";
                $arr_offers[] = $r;
            }
            $offers_id_str = implode(',', $offers_id);
        }
    }

    return array(
        'error' => 0,
        'total' => intval($total),
        'more' => $total > ($offset + $limit) ? 1 : 0,
        'cat_name' => $category_name,
        'data' => $arr_offers,
    );
}

function category_info($category_id) {
    $q = "select * 
        from `tbl_links_categories_list`
        where `id` = '" . intval($category_id) . "'";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    return $r;
}

/**
 * Return total rows for selects with SQL_CALC_FOUND_ROWS option
 * work only for LAST QUERY
 * @return int $total_rows
 */
function total_rows() {
    $rs = mysql_fetch_assoc(db_query('SELECT FOUND_ROWS()'));
    return $rs['FOUND_ROWS()'];
}

/**
 * Всего оферов в выбранной категории
 * @param string тип категории
 * @param int id категории
 */
function offers_total($cat_type, $cat_id = 0) {
    $offers = get_offers_list($cat_type, $cat_id, 0, 1000, 1);
    return $offers;
}

/**
 * Редирект средствами php header 
 * @param string URL 
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Выясняем какие споты входят во временой промежуток
 * @param string $from начало
 * @param string $to конец
 * @param type $timezone_shift смещение временного пояса
 * @return array 
 * 
 * Примеры использования
 * clicks_spot_get() - текущий спот
 * clicks_spot_get('all') - все споты
 * clicks_spot_get('2014-04-12') - все споты, в которых есть записи на весь этот день
 * clicks_spot_get('2014-04-12 10:00:00', '2014-04-13 10:00:00') - споты, в которых есть записи за это время
 */
function clicks_spot_get($from = '', $to = '', $timezone_shift = '+00:00') {
    $out = array();

    if (empty($from) and empty($to)) {
        $where = "`current` = 1";
    } elseif ($from == 'all') {
        $where = " 1";
    } else {
        if (!empty($from) and empty($to)) {
            $to = $from;
        }

        if (strlen($from) == 10) {
            $from .= ' 00:00:00';
        }

        if (strlen($to) == 10) {
            $to .= ' 23:59:59';
        }

        if ($timezone_shift == '+00:00') {
            $where = "(`time_begin` < STR_TO_DATE('" . $to . "', '%Y-%m-%d %H:%i:%s') AND `time_end` > STR_TO_DATE('" . $from . "', '%Y-%m-%d %H:%i:%s'))";
        } else {
            $where = "(CONVERT_TZ(`time_begin`, '+00:00', '" . _str($timezone_shift) . "') < STR_TO_DATE('" . $to . "', '%Y-%m-%d %H:%i:%s') AND CONVERT_TZ(`time_end`, '+00:00', '" . _str($timezone_shift) . "') > STR_TO_DATE('" . $from . "', '%Y-%m-%d %H:%i:%s'))";
        }
    }
    $q = "select `id` from `tbl_clicks_map` where " . $where . " order by `id`";
    if ($rs = db_query($q) and mysql_num_rows($rs) > 0) {
        while ($r = mysql_fetch_assoc($rs)) {
            $out[] = $r['id'];
        }
    }
    return $out;
}

/**
 * Создание нового спота для кликов
 */
function clicks_spot_add() {

    $current_spot_id = clicks_spot_get();
    $q = "select max(`date_add`) as `max_time` from `tbl_clicks_s" . $current_spot_id . "`";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    $max_spot_time = $r['max_time'];

    // Завершаем текущий спот
    $q = "update tbl_clicks_map set `time_end` = '" . $max_spot_time . "', `current` = '0' where `id` = '" . $current_spot_id . "'";

    // Создание нового спота
    $ins = array(
        'time_begin' => '2000-01-01 00:00:00',
        'time_end' => '2020-01-01 00:00:00',
        'current' => '1'
    );
    $q = insertsql($ins, 'tbl_clicks_map');
    db_query($q);

    $new_spot_id = mysql_insert_id();

    $q = "CREATE TABLE IF NOT EXISTS `tbl_clicks_s" . $new_spot_id . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_add` datetime NOT NULL,
  `user_ip` varchar(255) NOT NULL,
  `user_agent` text CHARACTER SET utf8 NOT NULL,
  `user_os` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_os_version` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform_info` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform_info_extra` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_browser` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_browser_version` varchar(255) CHARACTER SET utf8 NOT NULL,
  `is_mobile_device` tinyint(1) NOT NULL,
  `is_phone` tinyint(1) NOT NULL,
  `is_tablet` tinyint(1) NOT NULL,
  `country` varchar(255) NOT NULL,
  `state` varchar(255) CHARACTER SET utf8 NOT NULL,
  `city` varchar(255) CHARACTER SET utf8 NOT NULL,
  `region` varchar(255) CHARACTER SET utf8 NOT NULL,
  `isp` varchar(255) CHARACTER SET utf8 NOT NULL,
  `rule_id` int(11) NOT NULL,
  `out_id` int(11) NOT NULL,
  `subid` varchar(255) CHARACTER SET utf8 NOT NULL,
  `subaccount` varchar(255) CHARACTER SET utf8 NOT NULL,
  `source_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ads_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `referer` text CHARACTER SET utf8 NOT NULL,
  `search_string` text CHARACTER SET utf8 NOT NULL,
  `click_price` decimal(10,4) NOT NULL,
  `conversion_price_main` decimal(10,4) NOT NULL,
  `is_lead` tinyint(1) NOT NULL,
  `is_sale` tinyint(1) NOT NULL,
  `is_parent` tinyint(1) NOT NULL,
  `is_connected` tinyint(1) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `campaign_param1` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param2` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param3` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param4` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param5` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_name1` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value1` text CHARACTER SET utf8 NOT NULL,
  `click_param_name2` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value2` text CHARACTER SET utf8 NOT NULL,
  `click_param_name3` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value3` text CHARACTER SET utf8 NOT NULL,
  `click_param_name4` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value4` text CHARACTER SET utf8 NOT NULL,
  `click_param_name5` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value5` text CHARACTER SET utf8 NOT NULL,
  `click_param_name6` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value6` text CHARACTER SET utf8 NOT NULL,
  `click_param_name7` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value7` text CHARACTER SET utf8 NOT NULL,
  `click_param_name8` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value8` text CHARACTER SET utf8 NOT NULL,
  `click_param_name9` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value9` text CHARACTER SET utf8 NOT NULL,
  `click_param_name10` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value10` text CHARACTER SET utf8 NOT NULL,
  `click_param_name11` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value11` text CHARACTER SET utf8 NOT NULL,
  `click_param_name12` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value12` text CHARACTER SET utf8 NOT NULL,
  `click_param_name13` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value13` text CHARACTER SET utf8 NOT NULL,
  `click_param_name14` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value14` text CHARACTER SET utf8 NOT NULL,
  `click_param_name15` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value15` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subid` (`subid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
    db_query($q);
}

/**
 * По SubID, оказывается, можно вычислить дату!
 * 
 */
function subidtotime($subid) {
    $tmp = current(explode('x', $subid));
    $tmp = substr($tmp, 0, 4) . '-' . substr($tmp, 4, 2) . '-' . substr($tmp, 6, 2) . ' ' . substr($tmp, 8, 2) . ':' . substr($tmp, 10, 2) . ':' . substr($tmp, 12, 2);
    return $tmp;
}

/**
 * Определяем края временного отрезка
 * 
 * @param int $t время
 * @param string $type тип временного отрезка
 * @param string $edge begin или end
 * @return int 
 */
function time_edge($t, $type = 'hour', $edge = 'begin') {
    switch ($type) {
        case 'hour':
            $out = mktime(date('H', $t), 0, 0, date('m', $t), date('d', $t), date('Y', $t));
            if ($edge == 'end')
                $out += (3600 - 1);
            break;
    }
    return $out;
}

function get_cache_timers() {
    $out = array();
    $q = "select * from `tbl_clicks_cache_time` where 1";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    return $r;
}

function set_cache_timer($type, $t) {
    $q = "update `tbl_clicks_cache_time` set `" . $type . "` = '" . $t . "'";
    $rs = db_query($q);
}

/**
 * Случайная строка произвольной длины (пароли)
 * @param int $length
 * @return string 
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $out;
}

function load_networks_list()
{
    require_once _TRACK_LIB_PATH . '/class/common.php';
    require_once _TRACK_LIB_PATH . '/class/custom.php';
    $arr_networks = array();
    $networks = dir(_TRACK_LIB_PATH . '/postback');

    while ($file = $networks->read())
    {
        if ($file != '.' && $file != '..')
        {
            $file = str_replace('.php', '', $file);

            switch ($file)
            {
                case 'GdeSlon':
                    $name = 'Где Слон?';
                break;

                default:
                    $name = $file;
                break;
            }
            $arr_networks[$file] = $name;
        }
    }

    asort($arr_networks);

    $result=array();
    $i = 0;
    $first_letter_old = '';
    foreach ($arr_networks as $network => $name)
    {
        $first_letter = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');

        if ($first_letter_old == $first_letter)
        {
            $result[$i]['class']="is-hidden";
        }
        else
        {
            $result[$i]['class']='';
        }
        $result[$i]['network']=$network;
        $result[$i]['letter']=$first_letter;
        $result[$i]['caption']=$name;
        $first_letter_old = $first_letter;
        $i++;
    }

    return $result;
}
?>