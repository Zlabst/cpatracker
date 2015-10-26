<?php

class GdeSlon {

    public $network_name = 'GdeSlon';
    private $display_url = 'www.gdeslon.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/gdeslon';
    private $network_description = 'Крупнейшая российская товарная партнерская сеть. Удобные механизмы для создания партнерских магазинов, товарные виджеты для ваших сайтов, купоны и промо-коды. Идеальный выбор для создания собственных сайтов, нацеленных на SEO продвижение или раскрутку в социальных сетях. Привлекайте клиентов и получайте вознаграждение, об остальном позаботится партнерская программа.';

    private $params = array(
        'profit' => array('url_param'=>'profit', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'sub_id', 'caption'=>'SubID'),
        't1' => array('url_param'=>'action_ip', 'caption'=>'IP'),
        't2' => array('url_param'=>'user_agent', 'caption'=>'User-agent'),
        't5' => array('url_param'=>'click_id', 'caption'=>'ID перехода'),
        't7' => array('url_param'=>'user_referrer', 'caption'=>'Реферер'),
        'f1' => array('url_param'=>'order_sum', 'caption'=>'Сумма'),
        'i2' => array('url_param'=>'merchant_id', 'caption'=>'ID продавца'),
        'i3' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i4' => array('url_param'=>'click_time', 'caption'=>'Дата перехода'),
        'i6' => array('url_param'=>'action_time', 'caption'=>'Дата продажи'),
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
    }

    function get_params_info(){
        return $this->params;
    }

    function get_network_info()
    {
        $postback_links=array();
        $url = tracklink() . '/p.php?n=' . $this->network_name;
        $url .= '&ak=' . $this->common->get_code();

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Для автоматического импорта продаж добавьте ссылку в поле PostBack в настройках Gdeslon и выберите метод GET:');

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
        $input_data = $this->common->request($data_all);
        $output_data = array();
        foreach ($input_data as $name => $value) {
            if ($key = array_search($name, $this->params)) {
                $output_data[$key] = $value;
            }
        }
        $output_data['network'] = $this->network_name;
        $output_data['status'] = 1;
        $output_data['date_add'] = date('Y-m-d H:i:s', $output_data['action_time']);
        $this->common->process_conversion($output_data);
    }

}

