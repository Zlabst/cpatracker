<?php


class Myragon {
    
    
    public $net = 'Myragon';
    
    private $common;
    
    private $params = array(
        'profit' => 'price',
        'subid' => 'sa',
        'txt_status' => 'event',
        't3' => 'target_name',
        't4' => 'offer_name',
        't20' => 'currency',
        'i1' => 'target_id',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i6' => 'timestamp',
    );
    
    private $reg_url = 'http://www.cpatracker.ru/networks/myragon';
    
    private $net_text = 'Технологичная сеть на собственной платформе с десятками инструментов для вебмастеров. Сеть индивидуально подходит к каждому вебмастеру и с радостью помогает оптимизировать сайт, паблик или блог, а также помогает в проведении эффективных рекламных кампаний. Входит в тройку лидеров российского сегмента по количеству партнеров.';
    
    
    
    function __construct() {
        $this->common = new common($this->params);
    }
    
    
    function get_links() {
        $protocol = isset($_SERVER["HTTPS"]) ? (($_SERVER["HTTPS"]==="on" || $_SERVER["HTTPS"]===1 || $_SERVER["SERVER_PORT"]===$pv_sslport) ? "https://" : "http://") :  (($_SERVER["SERVER_PORT"]===$pv_sslport) ? "https://" : "http://");
        $cur_url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $url = substr($cur_url, 0, strlen($cur_url)-21);
        $url .= '/track/p.php?n='.$this->net;
        foreach ($this->params as $name => $value) {
            $url .= '&'.$name.'=[='.$value.'=]';
        }
        
        $code = $this->common->get_code();
        $url .= '&ak='.$code;
        
        $return = array(
            'id' => 0,
            'url' => $url,
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Myragon.'
        );
        
        return array(
            0 => $return,
            'reg_url' => $this->reg_url,
            'net_text' => $this->net_text
        );
    }
    
    
    function process_conversion($data_all) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $data_all['get'];
        $data['network'] = $this->net;
        unset($data['net']);
        
        switch ($data['txt_status']) {
            case 'New':
                $data['status'] = 3;
                break;
            case 'Done':
                $data['status'] = 1;
                break;
            case 'Canceled':
                $data['status'] = 2;
                break;
            case 'Wait':
                $data['status'] = 4;
                break;
        }
        
        $data['date_add'] = date('Y-m-d H:i:s', $data['timestamp']);
        
        $this->common->process_conversion($data);
    }
    
    
    
}




