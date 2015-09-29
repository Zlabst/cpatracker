<?php

class GdeSlon {

    public $network_name = 'GdeSlon';
    private $display_url = 'www.gdeslon.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/gdeslon';
    private $network_description = 'Крупнейшая российская товарная партнерская сеть. Удобные механизмы для создания партнерских магазинов, товарные виджеты для ваших сайтов, купоны и промо-коды. Идеальный выбор для создания собственных сайтов, нацеленных на SEO продвижение или раскрутку в социальных сетях. Привлекайте клиентов и получайте вознаграждение, об остальном позаботится партнерская программа.';

    private $params = array(
        'profit' => 'profit',
        'subid' => 'sub_id',
        't1' => 'action_ip',
        't2' => 'user_agent',
        't5' => 'click_id',
        't7' => 'user_referrer',
        'f1' => 'order_sum',
        'i2' => 'merchant_id',
        'i3' => 'order_id',
        'i4' => 'click_time',
        'i6' => 'action_time',
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
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

