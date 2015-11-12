<?php

class CTR {

    public $network_name = 'CTR';
    private $display_url = 'www.ctr.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/ctr';
    private $network_description = 'Позволит вам отслеживать эффективные каналы трафика и увеличивать конверсию и заработок.';


    private $params = array(
        'subid' => array('url_param'=>'sub_id', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'payment', 'caption'=>'Сумма продажи'),
        'date_add' => array('url_param'=>'time', 'caption'=>'Дата продажи'),
        'status' => array('url_param'=>'status', 'caption'=>'ID статуса'),
        'txt_status' => array('url_param'=>'status_name', 'caption'=>'Статус'),
        't1' => array('url_param'=>'ip', 'caption'=>'IP'),
        't4' => array('url_param'=>'utm_campaign', 'caption'=>'utm_campaign'),
        't6' => array('url_param'=>'utm_content', 'caption'=>'utm_content'),
        't7' => array('url_param'=>'utm_source', 'caption'=>'utm_source'),
        't9' => array('url_param'=>'country', 'caption'=>'Страна'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i12' => array('url_param'=>'out_order_id', 'caption'=>'out_order_id'),
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

    function process_conversion($data_all = array()) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);

        switch ($data['status'])
        {
            case '0': case '1':
                $data['txt_status']='waiting';
                $data['status'] = 3;
            break;

            case '3':
                $data['txt_status']='approved';
                $data['status'] = 1;
            break;

            case '4': case '13': case '99': case '88': case '77':
                $data['txt_status']='rejected';
                $data['status'] = 2;
            break;

            default:
                $data['status'] = 0;
            break;
        }

        $data['network'] = $this->network_name;
        $this->common->process_conversion($data);
    }
}