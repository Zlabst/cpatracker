<?php

class SalesDoubler {

    public $network_name = 'SalesDoubler';
    private $display_url = 'www.salesdoubler.com.ua';
    private $registration_url = 'http://www.cpatracker.ru/networks/salesdoubler';
    private $network_description = 'Украинская CPA-сеть с проверенными офферам, которые тщательно отбираются и тестируются представителями сети. Собственная платформа, отзывчивая техническая поддержка, много эксклюзивных рекламодателей с хорошей конверсией. Основные тематики: интернет-магазины, образовательные услуги, потребительские кредиты.';

    private $params = array(
        'subid' => array('url_param'=>'TRANS_ID', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'AFF_REV', 'caption'=>'Сумма продажи'),
        'status' => array('url_param'=>'status', 'caption'=>'Статус'),
        'f1' => array('url_param'=>'SALE_AMOUNT', 'caption'=>'Сумма'),
        't4' => array('url_param'=>'CAMPAIGN', 'caption'=>'Кампания'),
        't7' => array('url_param'=>'SOURCE', 'caption'=>'Источник'),
        't8' => array('url_param'=>'PROMO', 'caption'=>'Промо'),
        't9' => array('url_param'=>'TID1', 'caption'=>'tid1'),
        't10' => array('url_param'=>'TID2', 'caption'=>'tid2'),
        'i3' => array('url_param'=>'CONVERSION_ID', 'caption'=>'ID действия'),
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

        $postback_links[]=array('id'=>'pending',
            'url' => $url . '&status=pending',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - В ожидании»');

        $postback_links[]=array('id'=>'approved',
            'url' => $url . '&status=approved',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - Принято»');

        $postback_links[]=array('id'=>'rejected',
            'url' => $url . '&status=rejected',
            'description'=>'Вставьте эту ссылку в поле «Постбэк - Отклонено»');

        return array(
            'links'=>$postback_links,
            'name' => $this->network_name,
            'display-url' => $this->display_url,
            'registration-url' => $this->registration_url,
            'network-description' => $this->network_description
        );
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $data['txt_param20'] = 'uah';

        if (!isset($data['date_add'])) {
            $data['date_add'] = date('Y-m-d H:i:s');
        }
        unset($data['net']);

        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'rejected':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 'pending':
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
