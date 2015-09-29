<?php

class Advertise {

    public $network_name = 'Advertise';
    private $display_url = 'www.advertise.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/advertise';
    private $network_description = 'Партнерская сеть с повышенными ставками на все офферы, ежедневные выплаты без комиссии и эксклюзивные промо-материалы. Среди рекламодателей: онлайн-игры, сайты знакомств, wow-товары и финансовые сервисы.';

    private $params = array(
        'subid' => 'subid',
        'profit' => 'amount',
        'date_add' => 'action_time',
        'txt_status' => 'status',
        'type' => 'action_type',
        'f1' => 'order_sum',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'click_time',
        'i5' => 'source_id',
        'i6' => 'conversion_time',
        'i7' => 'action_id',
        'i8' => 'stats_action_id',
        't1' => 'action_ip',
        't2' => 'user_agent',
        't4' => 'offer_name',
        't7' => 'source_name',
        't8' => 'user_referer',
        't9' => 'country',
        't10' => 'city',
        't16' => 'subid1',
        't17' => 'subid2',
        't18' => 'subid3',
        't19' => 'subid4',
        't21' => 'keyword',
        't22' => 'action_name',
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

        if (empty($data['type'])) {
            $data['type'] = 'sale';
        }

        unset($data['net']);
        switch ($data['txt_status']) {
            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
                break;
            case 'rejected':
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
                break;
            case 'processing':
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