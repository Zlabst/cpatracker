<?php
if (!$include_flag) {
    exit();
}

/*
if (defined('_ENABLE_DEBUG') && _ENABLE_DEBUG)
{
    error_reporting(E_ALL);
    if(function_exists('ini_set'))
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', true);
    }
}
*/

echo '<link rel="stylesheet" href="'._HTML_LIB_PATH.'/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/css/bootstrap-datepicker3.min.css" >';
echo '<script src="'._HTML_TEMPLATE_PATH.'/js/report_toolbar.js"></script>';
echo '<script src="'._HTML_LIB_PATH.'/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/js/bootstrap-datepicker.min.js"></script>';
echo '<script src="'._HTML_LIB_PATH.'/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/locales/bootstrap-datepicker.ru.min.js"></script>';

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$IN=array();

// Set default values
$allowed_report_in_params=array(
    'hourly_report'=>array(
        'main_column'=>'source_name' // offer_name
    ),
    'flow_report'=>array(
        'date'=>get_current_day(),
        'filter_by'=>'none', // search, hour
        'filter_value'=>''
    )
);

// Set allowed values from $_REQUEST for each report
foreach ($allowed_report_in_params as $report=>$data)
{
    foreach (array_keys($allowed_report_in_params[$report]) as $cur)
    {
        if (isset($_REQUEST[$cur]) && $_REQUEST[$cur]!='')
        {
            $IN[$report][$cur]=$_REQUEST[$cur];
        }
        else
        {
            // Set default value
            $IN[$report][$cur]=$allowed_report_in_params[$report][$cur];
        }
    }
}

// Get hourly report data
$arr_report_data=prepare_report($IN['hourly_report'] + array('range_type'=>'hourly',
                                                             'date_start'=>$IN['flow_report']['date'],
                                                             'date_end'=>$IN['flow_report']['date']));
// Don't use parameters from hourly report
unset($arr_report_data['report_params']);

// Get clicks flow data
list($more, $arr_flow_data, $s, $s1) = get_visitors_flow_data($IN, 'flow_report');

// Fill variables for stats-flow report
$arr_flow_data['show_more']=$more;
$date_prev = date('Y-m-d', strtotime('-1 days', strtotime($IN['flow_report']['date'])));
$date_next = date('Y-m-d', strtotime('+1 days', strtotime($IN['flow_report']['date'])));
$arr_template_data=array('hide-table-footer'=>true,
    'date_current'=>$IN['flow_report']['date'],
    'date_prev_caption'=>mysqldate2string($date_prev),
    'date_current_caption'=>mysqldate2string($IN['flow_report']['date']),
    'date_next_caption'=>mysqldate2string($date_next),
    'hide_next_day'=>($IN['flow_report']['date']==get_current_day()),
    'date_prev'=>$date_prev,
    'date_next'=>$date_next,
    'csrf'=>CSRF_KEY);

// Render template
echo $mTemplate->render('stats-flow-page', $arr_report_data+$arr_template_data+$arr_flow_data);

// ***************************************

$hour = rq('hour', 2);
?>
<script type="text/javascript">
    function refresh_report(param_name, param_value)
    {
        switch (param_name)
        {
            default:
                var param_found=false;
                $('#report_params input[type="hidden"]').each(function() {
                    if ($(this).attr('name')==param_name)
                    {
                        param_found=true;
                        $(this).val(param_value);
                    }
                });
                if (!param_found)
                {
                    $('#report_params').append('<input type="hidden" name="'+param_name+'" value="'+param_value+'" />');
                }
            break;
        }
        $("#report_params").submit();
        return false;
    }
<?php
/*
?>
    function load_flow(obj)
    {
        $.post(
            'index.php?ajax_act=a_load_flow', {
                start: $('#start').val() ,
                start_s: $('#start_s').val() ,
                date: '<?php echo _str($report_date) ?>',
                hour: '<?php echo _str($hour) ?>',
                filter_by: '<?php echo _str($_REQUEST['filter_by']) ?>',
                filter_value: '<?php echo _str($_REQUEST['filter_value']) ?>'
            }
        ).done(
            function(data) {
                response = eval('(' + data + ')');
                $('#start').val(response.start);
                $('#start_s').val(response.start_s);
                if(!response.more) $(obj).hide();
                $('#stats-flow tbody').first().append(response.data);
            }
        );
        return false;
    }
<?php
*/
?>
</script>