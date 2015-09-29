<?php

class Himba {

    public $network_name = 'Himba';
    private $display_url = 'www.himba.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/himba';
    private $network_description = 'Партнерская сеть, фокусирующаяся на банковских услугах, страховании, кредитовании и образовательных офферах. Чаще всего рекламодатели платят за заполнение анкет, выдачу кредитных карт, оформление страховок или заявок на получение образовательных услуг. Подавляющее большинство трафика принимается со всей территории РФ, но есть офферы, которые принимают посетителей из Москвы и области или отдельных регионов.';

    private $params = array(
        'profit' => 'amount',
        'subid' => 'sub_id',
        'status' => 'status',
        't7' => 'source',
        't16' => 'sub_id2',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'adv_sub',
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
        unset($data['net']);
        $data['date_add'] = date('Y-m-d H:i:s');

        $this->common->process_conversion($data);
    }

}

