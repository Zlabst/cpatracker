<?php

class CTR {

    public $network_name = 'CTR';
    private $display_url = 'www.ctr.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/ctr';
    private $network_description = 'Позволит вам отслеживать эффективные каналы трафика и увеличивать конверсию и заработок.';

    private $params = array(
        'subid' => 'sub_id',
        'profit' => 'payment',
        'date_add' => 'time',
        'status' => 'status',
        'txt_status' => 'status_name',
        't1' => 'ip',
        't4' => 'utm_campaign',
        't6' => 'utm_content',
        't7' => 'utm_source',
        't9' => 'country',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i12' => 'out_order_id',
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

    function process_conversion($data_all = array()) {
        $this->common->log($this->network_name, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->network_name;
        $this->common->process_conversion($data);
    }

}