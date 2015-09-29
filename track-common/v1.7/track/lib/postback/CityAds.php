<?php

class CityAds {

    public $network_name = 'CityAds';
    private $display_url = 'www.cityads.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/cityads';
    private $network_description = 'Крупнейшая сеть по работе с онлайн-играми с оплатой за регистрацию. Вы также можете получать процент от платежей привлеченных игроков, что позволяет построить стабильный источник дохода даже при ограниченных ресурсах. Вас ждут более 300 предложений в 26 различных категориях, среди которых информационные продукты, купонные сервисы, товары для детей, банковские услуги и онлайн-кинотеатры.';

    private $params = array(
        'subid' => 'subaccount',
        'profit' => 'payout',
        'date_add' => 'conversion_date',
        't1' => 'ip',
        't2' => 'ua',
        't3' => 'target_name',
        't4' => 'offer_name',
        't5' => 'click_id',
        't6' => 'wp_name',
        't7' => 'site',
        't8' => 'action_type',
        't9' => 'country',
        't10' => 'city',
        't11' => 'user_browser',
        't12' => 'user_os',
        't13' => 'user_device',
        't20' => 'payout_currency',
        'i1' => 'target_id',
        'i2' => 'offer_id',
        'i3' => 'cpl_id',
        'i4' => 'click_time',
        'i5' => 'event_time',
        'i6' => 'conversion_time',
        'i7' => 'wp_id',
        'i9' => 'payout_id',
    );

    private $common;
    function __construct() {
        $this->common = new common($this->params);
    }

    function get_network_info()
    {
        $postback_links=array();
        $url = tracklink() . '/p.php?n=' . $this->network_name;

        $url .= '&ak=' . $this->common->get_code();
        $url .= '&status=created';

        $postback_links[]=array('id'=>'main',
            'url'=>$url,
            'description'=>'Для автоматического импорта продаж добавьте ссылку в поле PostBack URL в настройках сети, выберите тип запроса POST и оставьте галочки напротив всех переменных.');

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
        $input_data = $this->common->request($data_all);
        $output_data = array();
        foreach ($input_data as $name => $value) {
            if ($key = array_search($name, $this->params)) {
                $output_data[$key] = $value;
            }
        }
        $output_data['network'] = $this->network_name;
        $output_data['status'] = 1;
        $this->common->process_conversion($output_data);
    }
}
