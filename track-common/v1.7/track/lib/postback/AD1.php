<?php

class AD1 {

    public $network_name = 'AD1';
    private $display_url = 'www.ad1.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/ad1';
    private $network_description = 'Одной из самых привлекательных СРА сетей в рунете. С момента запуска в 2011 году, разработчики активно работают над сетью, добавляют новые инструменты и активно привлекают рекламодателей. Сеть работает на собственной платформе Zotto, выплаты по запросу от 30 рублей. Постоянно проходят конкурсы для вебмастеров с крупными призами.';

    private $params = array(
        'subid' => 'subid',
        'profit' => 'summ_approved',
        'date_add' => 'postback_date',
        'txt_status' => 'status',
        't1' => 'uip',
        't2' => 'uagent',
        't3' => 'goal_title',
        't4' => 'offer_name',
        'f1' => 'summ_total',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'click_time',
        'i5' => 'lead_time',
        'i6' => 'postback_time',
        'i7' => 'rid',
        'd1' => 'click_date',
        'd2' => 'lead_date'
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
            'description'=>'Вставьте эту ссылку в поле PostBack ссылки в настройках Вашего потока в сети AD1 и выберите тип передачи POST:');

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
        unset($data['net']);
        $cnt = count($data);
        $i = 0;

        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'declined':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'waiting':
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

