<?php

class Shakes {

    public $network_name = 'Shakes';
    private $display_url = 'www.shakes.pro';
    private $registration_url = 'http://www.cpatracker.ru/networks/shakes';
    private $network_description = 'Конвертируем ваш трафик в деньги!';

    private $params = array(
        'profit' => array('url_param'=>'cost', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'sub1', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'date', 'caption'=>'Дата продажи'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't5' => array('url_param'=>'sub2', 'caption'=>'SubID 2'),
        'i2' => array('url_param'=>'offer', 'caption'=>'Оффер'),
        'i7' => array('url_param'=>'landing', 'caption'=>'Сайт'),
        'i11' => array('url_param'=>'layer', 'caption'=>'layer')
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
        $data['txt_param20'] = 'rub';
        $data['type'] = 'sale';
        unset($data['net']);

        switch ($data['txt_status'])
        {
            case 'confirm':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'decline':
            case 'reject':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            default:
                $data['txt_status'] = '';
                $data['status'] = 0;
            break;
        }
        $this->common->process_conversion($data);
    }
}