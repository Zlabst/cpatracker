<?php

class Wapempire {

    public $network_name = 'Wapempire';
    private $display_url = 'www.wapempire.com';
    private $registration_url = 'http://www.cpatracker.ru/networks/wapempire';
    private $network_description = ' Международная мобильная CPA-сеть с русскоязычными владельцами. Большой выбор мобильных офферов с оплатой по моделям CPI и CPA, включая зарубежный 1Wap-click.';

    private $params = array(
        'profit' => array('url_param'=>'payout', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'aff_sub', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'datetime', 'caption'=>'Дата продажи'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        't7' => array('url_param'=>'source', 'caption'=>'Источник'),
        't12' => array('url_param'=>'device_os', 'caption'=>'ОС'),
        't13' => array('url_param'=>'device_brand', 'caption'=>'Устройство'),
        't14' => array('url_param'=>'affiliate_name', 'caption'=>'Партнер'),
        't15' => array('url_param'=>'file_name', 'caption'=>'Файл'),
        't16' => array('url_param'=>'aff_sub2', 'caption'=>'SubID 2'),
        't17' => array('url_param'=>'aff_sub3', 'caption'=>'SubID 3'),
        't18' => array('url_param'=>'aff_sub4', 'caption'=>'SubID 4'),
        't19' => array('url_param'=>'aff_sub5', 'caption'=>'SubID 5'),
        't20' => array('url_param'=>'currency', 'caption'=>'Валюта'),
        't21' => array('url_param'=>'device_model', 'caption'=>'Модель устройства'),
        't22' => array('url_param'=>'device_os_version', 'caption'=>'Версия ОС'),
        't23' => array('url_param'=>'device_id', 'caption'=>'ID устройства'),
        't24' => array('url_param'=>'android_id', 'caption'=>'Android ID'),
        't25' => array('url_param'=>'mac_address', 'caption'=>'Mac адрес'),
        't26' => array('url_param'=>'open_udid', 'caption'=>'open_udid'),
        't27' => array('url_param'=>'ios_ifa', 'caption'=>'ios_ifa'),
        't28' => array('url_param'=>'ios_ifv', 'caption'=>'ios_ifv'),
        't29' => array('url_param'=>'unid', 'caption'=>'unid'),
        't30' => array('url_param'=>'mobile_ip', 'caption'=>'IP'),
        'i1' => array('url_param'=>'goal_id', 'caption'=>'ID цели'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'transaction_id', 'caption'=>'ID транзакции'),
        'i7' => array('url_param'=>'offer_url_id', 'caption'=>'offer_url_id'),
        'i10' => array('url_param'=>'offer_file_id', 'caption'=>'offer_file_id'),
        'i11' => array('url_param'=>'device_id', 'caption'=>'ID устройства'),
        'i12' => array('url_param'=>'affiliate_id', 'caption'=>'ID партнера'),
        'i13' => array('url_param'=>'affiliate_ref', 'caption'=>'affiliate_ref'),
        'i14' => array('url_param'=>'offer_ref', 'caption'=>'offer_ref'),
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
        $url = tracklink() . '/p.php?n=' . $this->network_name;

        foreach ($this->params as $name => $value) {
            $url .= '&' . $name . '={' . $value['url_param'] . '}';
        }

        $url .= '&ak=' . $this->common->get_code();

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Для автоматического импорта продаж добавьте ссылку в поле PostBack в настройках оффера:');

        return array(
            'links'=>$postback_links,
            'name' => $this->network_name,
            'display-url' => $this->display_url,
            'registration-url' => $this->registration_url,
            'network-description' => $this->network_description
        );
    }

    function process_conversion($data_all)
    {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $data['status'] = 1;
        unset($data['net']);

        $this->common->process_conversion($data);
    }

}