<?php

class Biznip {

    public $network_name = 'Biznip';
    private $display_url = 'www.biznip.com';
    private $registration_url = 'http://www.cpatracker.ru/networks/biznip';
    private $network_description = 'Лучшая в рунете партнерская программа по инфотоварам. Собственные продукты высочайшего качества по работе в интернете, похудению, построению отношений. Привлеченным клиентам постоянно продолжают продавать услуги и товары с помощью почтовых рассылок, партнеры получают комиссию по повторным продажам, что позволяет стабильно зарабатывать даже после остановки трафика.';

    private $params = array(
        'profit' => 'payout',
        'subid' => 'aff_sub',
        'txt_status' => 'status',
        't5' => 'click_id',
        't16' => 'aff_sub2',
        't17' => 'aff_sub3',
        't18' => 'aff_sub4',
        't19' => 'aff_sub5',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'conversion_id',
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
    }

    function get_network_info()
    {
        $postback_links=array();
        $url = tracklink() . '/p.php?n=' . $this->network_name;

        foreach ($this->params as $name => $value)
        {
            $url .= '&' . $name . '={' . $value . '}';
        }

        $url .= '&ak=' . $this->common->get_code();

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Вставьте эту ссылку в поле PostBack ссылки в настройках Biznip:');

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

        unset($data['net']);

        $data['date_add'] = date('Y-m-d H:i:s');

        switch ($data['txt_status']) {
            case 'pending':
                $data['status'] = 3;
                break;
            case 'approved':
                $data['status'] = 1;
                break;
            case 'rejected':
                $data['status'] = 2;
                break;
        }


        $this->common->process_conversion($data);
    }

}

