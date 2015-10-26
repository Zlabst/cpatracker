<?php

class Advertise {

    public $network_name = 'Advertise';
    private $display_url = 'www.advertise.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/advertise';
    private $network_description = 'Партнерская сеть с повышенными ставками на все офферы, ежедневные выплаты без комиссии и эксклюзивные промо-материалы. Среди рекламодателей: онлайн-игры, сайты знакомств, wow-товары и финансовые сервисы.';

    private $params = array(
        'subid' => array('url_param'=>'subid', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'amount', 'caption'=>'Сумма продажи'),
        'date_add' => array('url_param'=>'action_time', 'caption'=>'Дата продажи'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        'type' => array('url_param'=>'action_type', 'caption'=>'Тип действия'),
        'f1' => array('url_param'=>'order_sum', 'caption'=>'Сумма продажи'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i4' => array('url_param'=>'click_time', 'caption'=>'Дата перехода'),
        'i5' => array('url_param'=>'source_id', 'caption'=>'ID источника'),
        'i6' => array('url_param'=>'conversion_time', 'caption'=>'Дата продажи'),
        'i7' => array('url_param'=>'action_id', 'caption'=>'ID действия'),
        'i8' => array('url_param'=>'stats_action_id', 'caption'=>'stats_action_id'),
        't1' => array('url_param'=>'action_ip', 'caption'=>'IP'),
        't2' => array('url_param'=>'user_agent', 'caption'=>'User-agent'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        't7' => array('url_param'=>'source_name', 'caption'=>'Источник'),
        't8' => array('url_param'=>'user_referer', 'caption'=>'Реферер'),
        't9' => array('url_param'=>'country', 'caption'=>'Страна'),
        't10' => array('url_param'=>'city', 'caption'=>'Город'),
        't16' => array('url_param'=>'subid1', 'caption'=>'subid1'),
        't17' => array('url_param'=>'subid2', 'caption'=>'subid2'),
        't18' => array('url_param'=>'subid3', 'caption'=>'subid3'),
        't19' => array('url_param'=>'subid4', 'caption'=>'subid4'),
        't21' => array('url_param'=>'keyword', 'caption'=>'Ключевое слово'),
        't22' => array('url_param'=>'action_name', 'caption'=>'Действие')
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

    function process_conversion($data_all)
    {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;

        if (empty($data['type'])) {
            $data['type'] = 'sale';
        }

        unset($data['net']);
        switch ($data['txt_status'])
        {
            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'rejected':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 'processing':
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