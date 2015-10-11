<?php
if (!$include_flag) {exit();}

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$arr_currencies_list=get_active_currencies();
$arr_currencies_list=array_values($arr_currencies_list);
$selected_currency=$arr_currencies_list[0];

$arr_page_data=array('networks'=>load_networks_list(),
                     'currencies'=>$arr_currencies_list,
                     'selected_currency_id'=>$selected_currency['id'],
                     'selected_currency_symbol'=>$selected_currency['symbol'],
                     'CSRF_KEY'=>CSRF_KEY,
                     '_HTML_LIB_PATH'=>_HTML_LIB_PATH,
                     '_HTML_TEMPLATE_PATH'=>_HTML_TEMPLATE_PATH);

echo $mTemplate->render('import-sales-page', $arr_page_data);
