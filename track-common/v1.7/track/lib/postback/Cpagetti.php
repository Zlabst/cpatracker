<?php

class Cpagetti {

    public $network_name = 'Cpagetti';
    private $display_url = 'www.cpagetti.com';
    private $registration_url = 'https://www.cpatracker.ru/networks/cpagetti';
    private $network_description = 'Товарная партнерская сеть с оплатой за подтвержденную заявку, актуальные предложения по самым популярным тематикам на широкую аудиторию. Всегда адекватная техническая поддержка, быстрый обзвон рекламодателями и конкурентные выплаты.';

    private $params = array(
        'date' => 'time',
        't1' => 'ip',
        'profit' => 'money',
        'txt_status' => 'status', // wait, accept, decline, invalid
        'i2' => 'offer',
        'i3' => 'conversion_id',
        'i7' => 'landing',
        'i11' => 'layer',
        't5' => 'sub2',
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
        $data['txt_param20'] = 'rub';
        $data['type'] = 'sale';
        unset($data['net']);

        switch ($data['txt_status'])
        {
            case 'accept':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
            break;

            case 'decline': case 'invalid':
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