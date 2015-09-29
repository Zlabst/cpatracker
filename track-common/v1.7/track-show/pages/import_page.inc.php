<?php
if (!$include_flag) {exit();}

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$arr_page_data=array('networks'=>load_networks_list(),
                     'CSRF_KEY'=>CSRF_KEY,
                     '_HTML_LIB_PATH'=>_HTML_LIB_PATH,
                     '_HTML_TEMPLATE_PATH'=>_HTML_TEMPLATE_PATH);
echo $mTemplate->render('import-sales-page', $arr_page_data);
?>
<link href="<?php echo _HTML_LIB_PATH; ?>/select2/select2.css" rel="stylesheet"/>
<style>
    .btn-default.zeroclipboard-is-hover {background-color:#cbe4f5 !important; border-bottom: 1px solid #95b4c9 !important; }
    .btn-default.zeroclipboard-is-active { background-color:#cbe4f5 !important; box-shadow: 0 3px 5px rgba(0, 0, 0, 0.125) inset;}
    .partner-description h4 {margin:0px;}
    .partner-description h4 a{color:#15c; text-decoration:underline; font-weight: normal; font-size:16px; margin-left:20px; display:inline-block; }
</style>

<script src="<?php echo _HTML_LIB_PATH; ?>/mustache/mustache.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/select2/select2.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.min.js"></script>

<script type="text/javascript">
    function check_import()
    {
        if ($('#leadsType').val()=='sale' && ($('#amount_value').val()==0 || $('#amount_value').val()==''))
        {
            return false;
        }

        if ($('#subids').val()=='')
        {
            return false;
        }

        return true;
    }

    function change_currency(currency)
    {
        var currency_name=''; var currency_code='';
        switch (currency)
        {
            case 'rub':
                currency_name='руб.';
                currency_code='rub';
            break;

            case 'usd':
                currency_name='долл.';
                currency_code='usd';
            break;

            case 'uah':
                currency_name='грн.';
                currency_code='uah';
            break;
        }
        $('#currency_selected').html(currency_name+'&nbsp;&nbsp;<span class="caret"></span>');
        $('#currency_code').val(currency_code);
        return false;
    }

    function openURL(url)
    {
        window.open(url);
        return false;
    }
</script>