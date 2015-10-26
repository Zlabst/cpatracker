<?php

class KMA {

    public $network_name = 'KMA';
    private $display_url = 'www.kma.biz';
    private $registration_url = 'http://www.cpatracker.ru/networks/kma';
    private $network_description = 'Конвертируем ваш трафик в деньги!';

    private $params = array(
        'profit' => array('url_param'=>'sum', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'data1', 'caption'=>'SubID'),
        't4' => array('url_param'=>'campaignid', 'caption'=>'ID кампании'),
        'i3' => array('url_param'=>'orderid', 'caption'=>'ID заказа'),
        't6' => array('url_param'=>'chan', 'caption'=>'Канал'),
        't5' => array('url_param'=>'data2', 'caption'=>'data2'),
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
        $data['status'] = 1;
        $data['txt_param20'] = 'rub';
        unset($data['net']);
        $this->common->process_conversion($data);
    }

}