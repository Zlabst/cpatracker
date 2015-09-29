<?php

class ActionPay {

    public $network_name = 'ActionPay';
    private $display_url = 'www.actionpay.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/actionpay';
    private $network_description = 'Одна из старейших партнерских сетей рунета. Быстрые выплаты, удобный интерфейс пользователя, отзывчивый саппорт. Основные тематики: магазины одежды, банки и кредиты, инфопродукты, онлайн-игры. Офферы из России, Украины, Казахстана и Молдовы, есть предложения для зарубежного трафика. Прекрасная сеть для долгосрочного сотрудничества.';
    private $params = array(
        'subid' => 'subaccount',
        'profit' => 'payment',
        'i1' => 'aim',
        'i2' => 'offer',
        'i3' => 'apid',
        'i5' => 'time',
        'i7' => 'landing',
        'i8' => 'source',
        't9' => 'uniqueid'
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

        $code = $this->common->get_code();
        $url .= '&ak=' . $code;


        $postback_links[]=array('id'=>'create',
            'url'=>$url . '&status=created',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - Создание»');

        $postback_links[]=array('id'=>'approve',
            'url'=>$url . '&status=approved',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - Принятие»');

        $postback_links[]=array('id'=>'decline',
            'url'=>$url . '&status=declined',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - Отклонение»');

        return array(
            'links'=>$postback_links,
            'name' => $this->network_name,
            'display-url' => $this->display_url,
            'registration-url' => $this->registration_url,
            'network-description' => $this->network_description
        );
    }

    function process_conversion($data_all = array())
    {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        if (!isset($data['date_add'])) {
            $data['date_add'] = date('Y-m-d H:i:s');
        }
        unset($data['net']);

        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'declined':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'created':
                $data['txt_status'] = 'Created';
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
