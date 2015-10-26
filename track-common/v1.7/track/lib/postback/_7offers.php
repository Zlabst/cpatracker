<?php

class _7offers {

    public $network_name = '7offers';
    private $display_url = 'www.7offers.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/7offers';
    private $network_description = 'Крупная сеть партнерских программ с оплатой за целевое действие (Cost Per Action) работает с 2014 года. Офферы с фиксированными выплатами и с процентными отчислениями от стоимости заказа. Сеть принимает мобильный и web-трафик.';
    private $params = array(
        'profit' => array('url_param'=>'goal_value', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'subid1', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'time_action', 'caption'=>'Дата продажи'),
        'status' => array('url_param'=>'action_status', 'caption'=>'Статус'),
        'f1' => array('url_param'=>'action_sum', 'caption'=>'action_sum'),
        't1' => array('url_param'=>'user_ip', 'caption'=>'IP'),
        't2' => array('url_param'=>'user_agent', 'caption'=>'User-agent'),
        't3' => array('url_param'=>'goal_title', 'caption'=>'goal_title'),
        't4' => array('url_param'=>'offer_title', 'caption'=>'offer_title'),
        't7' => array('url_param'=>'source_title', 'caption'=>'source_title'),
        't16' => array('url_param'=>'subid2', 'caption'=>'subid2'),
        't17' => array('url_param'=>'subid3', 'caption'=>'subid3'),
        't18' => array('url_param'=>'subid4', 'caption'=>'subid4'),
        't19' => array('url_param'=>'subid5', 'caption'=>'subid5'),
        't20' => array('url_param'=>'currency', 'caption'=>'Валюта'),
        'i1' => array('url_param'=>'goal_id', 'caption'=>'ID цели'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'action_id', 'caption'=>'ID действия'),
        'i7' => array('url_param'=>'link_id', 'caption'=>'ID ссылки')
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

        $url=tracklink().'/p.php?n='.'_7offers';
        foreach ($this->params as $name => $value)
        {
            $url.='&'.$name.'={{'.$value['url_param'].'}}';
        }
        $url.='&ak='.$this->common->get_code();

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Для автоматического импорта продаж добавьте ссылку в поле PostBack в инструментах 7offers:');

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
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $cnt = count($data);
        $i = 0;

        switch ($data['status'])
        {
            case '1':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case '2':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case '0':
                $data['txt_status'] = 'waiting';
                $data['status'] = 3;
            break;

            default:
                $data['txt_status'] = '';
                $data['status'] = 0;
            break;
        }

        $this->common->process_conversion($data);
    }

}

