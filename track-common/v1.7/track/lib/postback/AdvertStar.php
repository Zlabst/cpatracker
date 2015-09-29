<?php

class AdvertStar {

    public $network_name = 'AdvertStar';
    private $display_url = 'www.advertstar.net';
    private $registration_url = 'http://www.cpatracker.ru/networks/advertstar';
    private $network_description = 'Персональное обслуживание крупных партнеров, эксклюзивные условия крупным адвертам с качественным трафиком. Основные тематики сети: браузерные игры, сайты знакомств, интернет-магазины, образовательные офферы. Вас ждут уникальные офферы, собственная партнерская платформа и выплаты до 4 раз в месяц.';
    private $params = array(
        'subid' => 'SUB_ID',
        'profit' => 'REV',
        'date_add' => 'START_TIME',
        't6' => 'END_TIME',
        't1' => 'IP',
        't9' => 'GEO',
        'i2' => 'AID',
        'i1' => 'AIM',
        't5' => 'CLICK_ID',
        't6' => 'LEAD_ID',
        't7' => 'SITE_ID',
        'i3' => 'ORDER_ID',
        't16' => 'SUB_ID2',
        't17' => 'SUB_ID3',
        't18' => 'SUB_ID4',
        't19' => 'SUB_ID5',
        't21' => 'ORDER_COMMENT',
        'txt_status' => 'STATUS',
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

    function process_conversion($data_all) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $data['type'] = 'sale';

        unset($data['net']);
        switch ($data['txt_status']) {
            case 1:
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
                break;
            case 2:
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
                break;
            case 0:
                $data['txt_status'] = 'waiting';
                $data['status'] = 3;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }

        $this->common->process_conversion($data);
    }

}

