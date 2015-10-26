<?php

class CityAds {

    public $network_name = 'CityAds';
    private $display_url = 'www.cityads.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/cityads';
    private $network_description = 'Крупнейшая сеть по работе с онлайн-играми с оплатой за регистрацию. Вы также можете получать процент от платежей привлеченных игроков, что позволяет построить стабильный источник дохода даже при ограниченных ресурсах. Вас ждут более 300 предложений в 26 различных категориях, среди которых информационные продукты, купонные сервисы, товары для детей, банковские услуги и онлайн-кинотеатры.';

    private $params = array(
        'subid' => array('url_param'=>'subaccount', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'payout', 'caption'=>'Сумма продажи'),
        'date_add' => array('url_param'=>'conversion_date', 'caption'=>'Дата продажи'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't2' => array('url_param'=>'ua', 'caption'=>'User-agent'),
        't3' => array('url_param'=>'target_name', 'caption'=>'target_name'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        't5' => array('url_param'=>'click_id', 'caption'=>'ID перехода'),
        't6' => array('url_param'=>'wp_name', 'caption'=>'wp_name'),
        't7' => array('url_param'=>'site', 'caption'=>'Сайт'),
        't8' => array('url_param'=>'action_type', 'caption'=>'Тип действия'),
        't9' => array('url_param'=>'country', 'caption'=>'Страна'),
        't10' => array('url_param'=>'city', 'caption'=>'Город'),
        't11' => array('url_param'=>'user_browser', 'caption'=>'Браузер'),
        't12' => array('url_param'=>'user_os', 'caption'=>'ОС'),
        't13' => array('url_param'=>'user_device', 'caption'=>'Устройство'),
        't20' => array('url_param'=>'payout_currency', 'caption'=>'Валюта'),
        'i1' => array('url_param'=>'target_id', 'caption'=>'target_id'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'cpl_id', 'caption'=>'cpl_id'),
        'i4' => array('url_param'=>'click_time', 'caption'=>'Дата перехода'),
        'i5' => array('url_param'=>'event_time', 'caption'=>'event_time'),
        'i6' => array('url_param'=>'conversion_time', 'caption'=>'Дата продажи'),
        'i7' => array('url_param'=>'wp_id', 'caption'=>'wp_id'),
        'i9' => array('url_param'=>'payout_id', 'caption'=>'payout_id'),
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
    }

    function get_params_info()
    {
        return $this->params;
    }

    function get_network_info()
    {
        $postback_links=array();
        $url = tracklink() . '/p.php?n=' . $this->network_name;

        $url .= '&ak=' . $this->common->get_code();
        $url .= '&status=created';

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Для автоматического импорта продаж добавьте ссылку в поле PostBack URL в настройках сети, выберите тип запроса POST и оставьте галочки напротив всех переменных.');

        return array(
            'links'=>$postback_links,
            'name' => $this->network_name,
            'display-url' => $this->display_url,
            'registration-url' => $this->registration_url,
            'network-description' => $this->network_description
        );

    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $input_data = $this->common->request($data_all);
        $output_data = array();
        foreach ($input_data as $name => $value) {
            if ($key = array_search($name, $this->params)) {
                $output_data[$key] = $value;
            }
        }
        $output_data['network'] = $this->network_name;
        $output_data['status'] = 1;
        $this->common->process_conversion($output_data);
    }
}
