<?php

class ActionAds {

    public $network_name = 'ActionAds';
    private $display_url = 'www.actionads.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/actionads';
    private $network_description = 'Дружелюбная партнерская сеть с большим количеством рекламодателей и эксклюзивными офферами. Администрация готова на все ради своих партнёров: сделать выплату раньше, помочь консультацией, проинформировать обо всех изменениях в офферах и ответить на возникшие вопросы. Идеальный выбор для начинающего вебмастера.';
    private $params = array(
        'profit' => array('url_param'=>'payout', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'aff_sub', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'datetime', 'caption'=>'Дата продажи'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        't7' => array('url_param'=>'source', 'caption'=>'Источник'),
        't14' => array('url_param'=>'affiliate_name', 'caption'=>'affiliate_name'),
        't15' => array('url_param'=>'file_name', 'caption'=>'file_name'),
        't16' => array('url_param'=>'aff_sub2', 'caption'=>'aff_sub2'),
        't17' => array('url_param'=>'aff_sub3', 'caption'=>'aff_sub3'),
        't18' => array('url_param'=>'aff_sub4', 'caption'=>'aff_sub4'),
        't19' => array('url_param'=>'aff_sub5', 'caption'=>'aff_sub5'),
        't20' => array('url_param'=>'currency', 'caption'=>'Валюта'),
        'i1' => array('url_param'=>'goal_id', 'caption'=>'ID цели'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'transaction_id', 'caption'=>'ID транзакции'),
        'i7' => array('url_param'=>'offer_url_id', 'caption'=>'offer_url_id'),
        'i10' => array('url_param'=>'offer_file_id', 'caption'=>'offer_file_id'),
        'i11' => array('url_param'=>'device_id', 'caption'=>'ID устройства'),
        'i12' => array('url_param'=>'affiliate_id', 'caption'=>'affiliate_id'),
        'i13' => array('url_param'=>'affiliate_ref', 'caption'=>'affiliate_ref'),
        'i14' => array('url_param'=>'offer_ref', 'caption'=>'offer_ref')
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

        $url=tracklink().'/p.php?n='.$this->network_name;
        foreach ($this->params as $name => $value)
        {
            $url.='&'.$name.'={'.$value['url_param'].'}';
        }
        $url.='&ak='.$this->common->get_code();

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
        $this->common->process_conversion($data);
    }
}