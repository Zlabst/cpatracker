<?php

class Shakes {

    public $network_name = 'Shakes';
    private $display_url = 'www.shakes.pro';
    private $registration_url = 'http://www.cpatracker.ru/networks/shakes';
    private $network_description = 'Конвертируем ваш трафик в деньги!';

    private $params = array(
        'profit' => 'cost',
        'subid' => 'sub1',
        'date_add' => 'date', // unix
        'txt_status' => 'status',
        't1' => 'ip',
        't5' => 'sub2',
        'i2' => 'offer',
        'i7' => 'landing',
        'i11' => 'layer',
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
        $data['txt_param20'] = 'rub';
        $data['type'] = 'sale';
        unset($data['net']);

        switch ($data['txt_status']) {
            case 'confirm':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'decline':
            case 'reject':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }
        $this->common->process_conversion($data);
    }

}