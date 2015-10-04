<?php

class Everyads {

    public $network_name = 'Everyads';
    private $display_url = 'www.everyads.com';
    private $registration_url = 'http://www.cpatracker.ru/networks/everyads';
    private $network_description = 'Рекламная сеть с оплатой за установку. У Вас есть собственное приложение, сайт или сообщество которое посещают с мобильных устройств? С нами вы сможете эффективно монетизировать свои ресурсы. Наши клиенты: eBay, Aviasales, MachineZone, Natural Motion, GetTaxi, Tap4Fun, Kabam, Pacific Interactive, Momondo, Alawar и многие другие';

    private $params = array(
        'profit' => 'payout',
        'subid' => 'aff_sub',
        'date_add' => 'datetime',
        't1' => 'ip',
        't4' => 'offer_name',
        't7' => 'source',
        't14' => 'affiliate_name',
        't15' => 'file_name',
        't16' => 'aff_sub2',
        't17' => 'aff_sub3',
        't18' => 'aff_sub4',
        't19' => 'aff_sub5',
        't20' => 'currency',
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
        $protocol = isset($_SERVER["HTTPS"]) ? (($_SERVER["HTTPS"] === "on" || $_SERVER["HTTPS"] === 1 || $_SERVER["SERVER_PORT"] === $pv_sslport) ? "https://" : "http://") : (($_SERVER["SERVER_PORT"] === $pv_sslport) ? "https://" : "http://");
        $cur_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $url = substr($cur_url, 0, strlen($cur_url) - 21);
        $url .= '/track/p.php?n=' . $this->network_name;
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

    function process_conversion($data_all) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $data['status'] = 1;
        unset($data['net']);


        $this->common->process_conversion($data);
    }

}

