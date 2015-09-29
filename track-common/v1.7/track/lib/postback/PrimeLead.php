<?php

class PrimeLead {

    public $network_name = 'PrimeLead';
    private $display_url = 'www.primelead.com.ua';
    private $registration_url = 'http://www.cpatracker.ru/networks/primelead';
    private $network_description = 'Украинская партнерская сеть. Большой выбор предложений для украинского трафика, крупнейшие рекламодатели, среди которых курсы Ешко, сайт Rabota.ua и офферы от Альфа-Банка. Сеть также предлагает вебмастерам сотрудничество по привлечению покупателей в онлайн-магазины, работает по популярному направлению пластиковых окон и SEO-продвижения. Основной таргетинг: Украина, большинство офферов с оплатой за регистрацию или заявку. Есть XML-выгрузки для создания собственных партнерских магазинов.';

    private $params = array(
        'profit' => 'payout',
        'subid' => 'aff_sub',
        'date_add' => 'datetime',
        't1' => 'ip',
        't4' => 'offer_name',
        't7' => 'source',
        't12' => 'device_os',
        't13' => 'device_brand',
        't14' => 'affiliate_name',
        't15' => 'file_name',
        't16' => 'aff_sub2',
        't17' => 'aff_sub3',
        't18' => 'aff_sub4',
        't19' => 'aff_sub5',
        't20' => 'currency',
        't21' => 'device_model',
        't22' => 'device_os_version',
        't23' => 'device_id',
        't24' => 'android_id',
        't25' => 'mac_address',
        't26' => 'open_udid',
        't27' => 'ios_ifa',
        't28' => 'ios_ifv',
        't29' => 'unid',
        't30' => 'mobile_ip',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'transaction_id',
        'i7' => 'offer_url_id',
        'i10' => 'offer_file_id',
        'i11' => 'device_id',
        'i12' => 'affiliate_id',
        'i13' => 'affiliate_ref',
        'i14' => 'offer_ref',
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
    }

    function get_network_info()
    {
        $postback_links=array();
        $url = tracklink() . '/p.php?n=' . $this->network_name;

        foreach ($this->params as $name => $value) {
            $url .= '&' . $name . '={' . $value . '}';
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

