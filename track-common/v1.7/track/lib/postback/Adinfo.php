<?php

class Adinfo {

    public $network_name = 'Adinfo';
    private $display_url = 'www.adinfo.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/adinfo';
    private $network_description = 'Надежная партнерская программа с большим количеством эксклюзивных офферов.';

    private $params = array(
        'profit' => 'commission',
        'subid' => 'sud_id',
        'date_add' => 'lead_time',
        'txt_status' => 'status',
        't1' => 'uip',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'group_id',
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
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
            break;

            case 'new': case 'toconfirmed':
                $data['txt_status'] = 'waiting';
                $data['status'] = 3;
            break;

            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
            break;
        }

        $this->common->process_conversion($data);
    }

}