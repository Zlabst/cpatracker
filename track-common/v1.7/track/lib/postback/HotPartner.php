<?php

class HotPartner {

    public $network_name = 'HotPartner';
    private $display_url = 'www.hotpartner.biz';
    private $registration_url = 'http://www.cpatracker.ru/networks/hotpartner';
    private $network_description = 'CPA сеть работает с 2010 года на рынках России, Беларуси и Казахстана. Владеет собственным круглосуточным call-центром, платят вебмастерам по запросу 5 дней в неделю. Сеть специализируется на wow-товарах.';

    private $params = array(
        'profit' => 'payout',
        'subid' => 'pl_name',
        'date_add' => 'time',
        'txt_status' => 'status',
        't1' => 'ip',
        't4' => 'offer_name',
        't7' => 'referer',
        'i7' => 'shop_id',
        'i10' => 'teaser_id',
        'i11' => 'partner_id',
        'i12' => 'order_id',
        'i13' => 'partner_id',
        'i14' => 'pl_id',
        't15' => 'gate',
        't16' => 'shop_name',
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
            case 'confirmed': case 'payed':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
            break;

            case 'cancel':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
            break;

            case 'new': case 'toconfirmed':
                $data['txt_status'] = 'Waiting';
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