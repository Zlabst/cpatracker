<?php

class Adinfo {

    public $network_name = 'Adinfo';
    private $display_url = 'www.adinfo.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/adinfo';
    private $network_description = 'Надежная партнерская программа с большим количеством эксклюзивных офферов.';

    private $params = array(
        'profit' => array('url_param'=>'commission', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'sud_id', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'lead_time', 'caption'=>'Дата продажи'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        't1' => array('url_param'=>'uip', 'caption'=>'IP'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i4' => array('url_param'=>'group_id', 'caption'=>'ID группы')
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
        $data['type'] = 'sale';
        $data['txt_param20'] = 'rub';
        unset($data['net']);

        switch ($data['txt_status'])
        {
            case 'confirmed':
            case 'payed':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'cancel':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 'new': case 'toconfirmed':
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