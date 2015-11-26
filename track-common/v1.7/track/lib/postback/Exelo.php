<?php

class Exelo {

    public $network_name = 'Exelo';
    private $display_url = 'www.exelo.ru';
    private $registration_url = 'http://www.cpatracker.ru/networks/exelo';
    private $network_description = 'Надежная партнерская сеть с большим количеством товарных офферов, мгновенной технической поддержкой и постоянным мониторингом доступности сайтов рекламодателей. Очень качественный личный кабинет, понятные настройки для всех офферов, парковка доменов и выплаты в течение суток для всех вебмастеров.';
    private $params = array(
        'subid' => array('url_param'=>'sub_1', 'caption'=>'SubID'),
        'profit' => array('url_param'=>'action_value', 'caption'=>'Сумма продажи'),
        't1' => array('url_param'=>'program_id', 'caption'=>'ID оффера'),
        't2' => array('url_param'=>'program_title', 'caption'=>'Название оффера')
    );
    private $common;

    function get_params_info(){
        return $this->params;
    }

    function __construct() {
        $this->common = new common($this->params);
    }

    function get_network_info()
    {
        $postback_links=array();

        $url = tracklink() . '/p.php?n=' . $this->network_name;
        foreach ($this->params as $name => $value) {
            $url .= '&' . $name . '=[[' . $value['url_param'] . ']]';
        }

        $code = $this->common->get_code();
        $url .= '&ak=' . $code;

        $postback_links[]=array('id'=>'create',
            'url'=>$url . '&status=wait',
            'description'=>'Вставьте эту ссылку в поле «URL фиксации нового действия» в разделе «Инструменты, Conversion postback»');

        $postback_links[]=array('id'=>'approve',
            'url'=>$url . '&status=approved',
            'description'=>'Вставьте эту ссылку в поле «URL для подтверждения действия»');

        $postback_links[]=array('id'=>'decline',
            'url'=>$url . '&status=rejected',
            'description'=>'Вставьте эту ссылку в поле «URL для отклонения действия»');

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
        if (!isset($data['date_add'])) {
            $data['date_add'] = date('Y-m-d H:i:s');
        }
        unset($data['net']);

        switch ($data['status'])
        {
            case 'approve':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
            break;

            case 'decline':
                $data['txt_status'] = 'rejected';
                $data['status'] = 2;
            break;

            case 'wait':
                $data['txt_status'] = '';
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
