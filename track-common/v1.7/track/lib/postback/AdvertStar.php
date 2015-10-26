<?php

class AdvertStar {

    public $network_name = 'AdvertStar';
    private $display_url = 'www.advertstar.net';
    private $registration_url = 'http://www.cpatracker.ru/networks/advertstar';
    private $network_description = 'Персональное обслуживание крупных партнеров, эксклюзивные условия крупным адвертам с качественным трафиком. Основные тематики сети: браузерные игры, сайты знакомств, интернет-магазины, образовательные офферы. Вас ждут уникальные офферы, собственная партнерская платформа и выплаты до 4 раз в месяц.';
    private $params = array(
        'subid' => array('url_param'=>'SUB_ID', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'REV', 'caption'=>'Сумма продажи'),
        'date_add' => array('url_param'=>'START_TIME', 'caption'=>'Дата продажи'),
        't1' => array('url_param'=>'IP', 'caption'=>'IP'),
        't9' => array('url_param'=>'GEO', 'caption'=>'Гео'),
        'i2' => array('url_param'=>'AID', 'caption'=>'aid'),
        'i1' => array('url_param'=>'AIM', 'caption'=>'aim'),
        't5' => array('url_param'=>'CLICK_ID', 'caption'=>'ID перехода'),
        't6' => array('url_param'=>'LEAD_ID', 'caption'=>'ID действия'),
        't7' => array('url_param'=>'SITE_ID', 'caption'=>'ID сайта'),
        'i3' => array('url_param'=>'ORDER_ID', 'caption'=>'ID заказа'),
        't16' => array('url_param'=>'SUB_ID2', 'caption'=>'sub_id2'),
        't17' => array('url_param'=>'SUB_ID3', 'caption'=>'sub_id3'),
        't18' => array('url_param'=>'SUB_ID4', 'caption'=>'sub_id4'),
        't19' => array('url_param'=>'SUB_ID5', 'caption'=>'sub_id5'),
        't21' => array('url_param'=>'ORDER_COMMENT', 'caption'=>'Комментарий'),
        'txt_status' => array('url_param'=>'STATUS', 'caption'=>'Статус'),
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

    function process_conversion($data_all) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $data['type'] = 'sale';

        unset($data['net']);
        switch ($data['txt_status'])
        {
            case 1:
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 2:
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 0:
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

