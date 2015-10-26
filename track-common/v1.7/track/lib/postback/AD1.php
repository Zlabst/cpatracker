<?php

class AD1 {

    public $network_name = 'AD1';
    private $display_url = 'www.ad1.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/ad1';
    private $network_description = 'Одной из самых привлекательных СРА сетей в рунете. С момента запуска в 2011 году, разработчики активно работают над сетью, добавляют новые инструменты и активно привлекают рекламодателей. Сеть работает на собственной платформе Zotto, выплаты по запросу от 30 рублей. Постоянно проходят конкурсы для вебмастеров с крупными призами.';

    private $params = array(
        'subid' => array('url_param'=>'subid', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'summ_approved', 'caption'=>'Сумма продажи'),
        'date_add' => array('url_param'=>'postback_date', 'caption'=>'Дата продажи'),
        'txt_status' => array('url_param'=>'status', 'caption'=>'Статус'),
        't1' => array('url_param'=>'uip', 'caption'=>'IP'),
        't2' => array('url_param'=>'uagent', 'caption'=>'User-agent'),
        't3' => array('url_param'=>'goal_title', 'caption'=>'Название цели'),
        't4' => array('url_param'=>'offer_name', 'caption'=>'Оффер'),
        'f1' => array('url_param'=>'summ_total', 'caption'=>'Сумма'),
        'i1' => array('url_param'=>'goal_id', 'caption'=>'ID цели'),
        'i2' => array('url_param'=>'offer_id', 'caption'=>'ID оффера'),
        'i3' => array('url_param'=>'order_id', 'caption'=>'ID заказа'),
        'i4' => array('url_param'=>'click_time', 'caption'=>'Время перехода'),
        'i5' => array('url_param'=>'lead_time', 'caption'=>'Время продажи'),
        'i6' => array('url_param'=>'postback_time', 'caption'=>'postback_time'),
        'i7' => array('url_param'=>'rid', 'caption'=>'rid'),
        'd1' => array('url_param'=>'click_date', 'caption'=>'Дата перехода'),
        'd2' => array('url_param'=>'lead_date', 'caption'=>'Дата продажи')
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
            'description'=>'Вставьте эту ссылку в поле PostBack ссылки в настройках Вашего потока в сети AD1 и выберите тип передачи POST:');

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
        unset($data['net']);
        $cnt = count($data);
        $i = 0;

        switch ($data['status'])
        {
            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'declined':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 'waiting':
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

