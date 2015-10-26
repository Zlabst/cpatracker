<?php

class HotPartner {

    public $network_name = 'HotPartner';
    private $display_url = 'www.hotpartner.biz';
    private $registration_url = 'http://www.cpatracker.ru/networks/hotpartner';
    private $network_description = 'CPA сеть работает с 2010 года на рынках России, Беларуси и Казахстана. Владеет собственным круглосуточным call-центром, платят вебмастерам по запросу 5 дней в неделю. Сеть специализируется на wow-товарах.';

    private $params = array(
        'profit' => array('url_param'=>'payout', 'caption'=>'Сумма продажи'),
        'subid' => array('url_param'=>'pl_name', 'caption'=>'SubID'),
        'date_add' => array('url_param'=>'time', 'caption'=>'Дата продажи'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        't7' => array('url_param'=>'referer', 'caption'=>'Реферер'),
        'i7' => array('url_param'=>'shop_id', 'caption'=>'ID продавца'),
        'i10' => array('url_param'=>'teaser_id', 'caption'=>'ID тизера'),
        'i11' => array('url_param'=>'partner_id', 'caption'=>'ID партнера'),
        'i12' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i14' => array('url_param'=>'pl_id', 'caption'=>'pl_id'),
        't15' => array('url_param'=>'gate', 'caption'=>'gate'),
        't16' => array('url_param'=>'shop_name', 'caption'=>'Продавец')
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
            case 'confirmed': case 'payed':
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