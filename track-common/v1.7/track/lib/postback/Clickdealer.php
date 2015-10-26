<?php

class Clickdealer {

    public $network_name = 'Clickdealer';
    private $display_url = 'www.clickdealer.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/clickdealer';
    private $network_description = 'Международная партнерская сеть с оплатой за результат. Более 5 тысяч офферов, большой выбор мобильных приложений с оплатой за установку, сервисы знакомств, интернет-магазины, дейтинг и путешествия.';

    private $params = array(
        'profit' => array('url_param'=>'price', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'s2', 'caption'=>'SubID'),
        'i2' => array('url_param'=>'oid', 'caption'=>'oid'),
        'i3' => array('url_param'=>'tid', 'caption'=>'tid'),
        'i10' => array('url_param'=>'cid', 'caption'=>'cid'),
        'i11' => array('url_param'=>'affid', 'caption'=>'affid'),
        't4' => array('url_param'=>'campid', 'caption'=>'ID кампании'),
        't6' => array('url_param'=>'leadid', 'caption'=>'ID действия'),
        't16' => array('url_param'=>'s1', 'caption'=>'s1'),
        't17' => array('url_param'=>'s3', 'caption'=>'s3'),
        't18' => array('url_param'=>'s4', 'caption'=>'s4'),
        't19' => array('url_param'=>'s5', 'caption'=>'s5'),
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
            $url .= '&' . $name . '=#' . $value['url_param'] . '#';
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
        $data['txt_status'] = 'approved';
        $data['status'] = 1;
        unset($data['net']);

        $this->common->process_conversion($data);
    }

}