<?php

class Clickdealer {

    public $network_name = 'Clickdealer';
    private $display_url = 'www.clickdealer.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/clickdealer';
    private $network_description = 'Международная партнерская сеть с оплатой за результат. Более 5 тысяч офферов, большой выбор мобильных приложений с оплатой за установку, сервисы знакомств, интернет-магазины, дейтинг и путешествия.';

    private $params = array(
        'profit' => 'price',
        'subid' => 's2',
        'i2' => 'oid',
        'i3' => 'tid',
        'i10' => 'cid',
        'i11' => 'affid',
        't4' => 'campid',
        't6' => 'leadid',
        't16' => 's1',
        't17' => 's3',
        't18' => 's4',
        't19' => 's5',
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
            $url .= '&' . $name . '=#' . $value . '#';
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
        $data['txt_param20'] = 'usd';
        $data['txt_status'] = 'Approved';
        $data['status'] = 1;
        unset($data['net']);

        $this->common->process_conversion($data);
    }

}