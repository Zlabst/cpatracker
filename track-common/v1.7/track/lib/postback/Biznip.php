<?php

class Biznip {

    public $network_name = 'Biznip';
    private $display_url = 'www.biznip.com';
    private $registration_url = 'http://www.cpatracker.ru/networks/biznip';
    private $network_description = 'Лучшая в рунете партнерская программа по инфотоварам. Собственные продукты высочайшего качества по работе в интернете, похудению, построению отношений. Привлеченным клиентам постоянно продолжают продавать услуги и товары с помощью почтовых рассылок, партнеры получают комиссию по повторным продажам, что позволяет стабильно зарабатывать даже после остановки трафика.';

    private $params = array(
        'profit' => array('url_param'=>'payout', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'aff_sub', 'caption'=>'SubID'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        't5' => array('url_param'=>'click_id', 'caption'=>'ID перехода'),
        't16' => array('url_param'=>'aff_sub2', 'caption'=>'aff_sub2'),
        't17' => array('url_param'=>'aff_sub3', 'caption'=>'aff_sub3'),
        't18' => array('url_param'=>'aff_sub4', 'caption'=>'aff_sub4'),
        't19' => array('url_param'=>'aff_sub5', 'caption'=>'aff_sub5'),
        'i1' => array('url_param'=>'goal_id', 'caption'=>'ID цели'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'conversion_id', 'caption'=>'ID продажи'),
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

        foreach ($this->params as $name => $value)
        {
            $url .= '&' . $name . '={' . $value['url_param'] . '}';
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

        switch ($data['txt_status'])
        {
            case 'pending':
                $data['txt_status'] = 'waiting';
                $data['status'] = 3;
            break;

            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'rejected':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            default:
                $data['txt_status'] = '';
                $data['status'] = 0;
            break;
        }

        $this->common->process_conversion($data);
    }

}

