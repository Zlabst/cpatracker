<?php
if (!$include_flag) {exit();}

echo '<script src="'._HTML_LIB_PATH.'/chartjs/Chart.js"></script>';
echo '<script src="'._HTML_LIB_PATH.'/mustache/mustache.js"></script>';

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$arr_currencies_list=get_active_currencies();
$selected_currency=current($arr_currencies_list);

$arr_report_data=prepare_report('main-report', $_REQUEST+array(
        'report_params'=>array('act'=>'reports'),
        'type'=>'sales',
        'main_column'=>'source_name'
    )
);

list($more, $arr_flow_data, $s, $s1) = get_sales_flow_data($_REQUEST, 'flow_report');
$arr_report_data['flow_rows']=$arr_flow_data;
$arr_report_data['show_more']=$more;

// Fill sales table header names
$arr_report_data['flow-table-header']['values']=array(
    'short_date'=>array('caption'=>'Дата'),
    'offer_name'=>array('caption'=>'Оффер'),
    'network'=>array('caption'=>'Сеть'),
    'source_name'=>array('caption'=>'Источник'),
    'campaign_name'=>array('caption'=>'Кампания'),
    'placement'=>array('caption'=>'Площадка'),
    'profit'=>array('caption'=>'Сумма'),
    'status'=>array('caption'=>'Статус')
);

// ********************************************************************************


// Prepare data for sales chart
$i=0;
$arr_sales_chart_data=array();
foreach ($arr_report_data['table-columns'] as $cur){
    if ($i++==0){continue;}
    $arr_sales_chart_data['captions'][]="'".$cur['caption']."'";
}
$i=0; $total_sales=0;
foreach ($arr_report_data['table-total']['values'] as $cur)
{
    if ($i++==0){continue;}
    $total_sales+=$cur['value'];
    $arr_sales_chart_data['values'][]="'".$cur['value']."'";
}

// Chart data
if ($total_sales>0)
{
    $arr_sales_chart_data['data']['spacing']=round(100/count($arr_sales_chart_data['values']))+1;
    $arr_sales_chart_data['data']['captions']=implode(',',$arr_sales_chart_data['captions']);
    $arr_sales_chart_data['data']['values']=implode(',',$arr_sales_chart_data['values']);

    // Header toolbar
    $arr_report_data['header-toolbar']['buttons'][]=array(
        'active'=>'active',
        'action'=>"show_chart('bar')",
        'name'=>'',
        'checked'=>'checked',
        'button-class'=>'fa fa-bar-chart');

    $arr_report_data['header-toolbar']['buttons'][]=array(
        'active'=>'',
        'action'=>"show_chart('line')",
        'name'=>'',
        'checked'=>'',
        'button-class'=>'fa fa-area-chart');

    $arr_report_data['header-toolbar']['buttons'][]=array(
        'active'=>'',
        'action'=>"show_chart('hide')",
        'name'=>'',
        'checked'=>'',
        'button-class'=>'fa fa-eye-slash');
}

// Report caption
$arr_report_data['report_caption']='Продажи';

foreach ($arr_report_data['report_params'] as $cur)
{
    if ($cur['name']=='currency_id')
    {
        $selected_currency=$arr_currencies_list[$cur['value']];
        break;
    }
}

// Return to numeric keys
$arr_report_data['flow-table-header']['values']=array_values($arr_report_data['flow-table-header']['values']);


echo $mTemplate->render('sales-page', $arr_report_data+array(
        'currencies'=>array_values($arr_currencies_list),
        'selected_currency_symbol'=>$selected_currency['symbol'],
        'chart'=>$arr_sales_chart_data)
);

?>
<script>
    function filter_sales(subid)
    {
        var current_report_params={};
        if (subid!='')
        {
            current_report_params['filter_by']='subid';
            current_report_params['filter_value']=subid;
        }
        else
        {
            $("#report_params").find('input[type="hidden"]').each(function()
            {
                current_report_params[this.name] = this.value;
            })
            current_report_params['offset']=-100;
            current_report_params['limit']=20;
        }

        $.post(
            'index.php?ajax_act=a_load_sales_flow',
            $.param(current_report_params)
        ).done
        (
            function(response)
            {
                var data=jQuery.parseJSON(response);
                $.get('<?php echo _HTML_TEMPLATE_PATH.'/views/sales-flow-rows.mustache';?>', function(template)
                {
                    var d=[];
                    d['flow_rows']=data['data'];
                    var rendered = Mustache.render(template, d);
                    $('#sales-flow tbody').children().remove();
                    $('#sales-flow tbody').append(rendered);
                    if (subid=='')
                    {
                        $('.show-more').show();
                    }
                    else
                    {
                        $('.show-more').hide();
                    }

                    return false;
                });
            }
        );

        return false;
    }
    function show_chart(action)
    {
        switch (action)
        {
            case 'bar':
                $('#salesLineChart').hide();
                $('#salesBarChart').show();
                if (typeof myBarChart != 'undefined'){
                    myBarChart.destroy();
                    myBarChart = new Chart(objChartBar).Bar(chartDataBar, chartOptionsBar);
                }
                else
                {
                    myBarChart = new Chart(objChartBar).Bar(chartDataBar, chartOptionsBar);
                }
            break;

            case 'line':
                $('#salesBarChart').hide();
                $('#salesLineChart').css("width", '100%');
                $('#salesLineChart').css("height", '230px');
                $('#salesLineChart').css("display", 'block');
                if (typeof myLineChart != 'undefined'){
                    myLineChart.destroy();
                    myLineChart = new Chart(objChartLine).Line(chartDataLine, chartOptionsLine);
                }
                else
                {
                    myLineChart = new Chart(objChartLine).Line(chartDataLine, chartOptionsLine);
                }
            break;

            case 'hide':
                $('#salesBarChart').hide();
                $('#salesLineChart').hide();
            break;
        }
    }

    function refresh_report(param_name, param_value)
    {
        if (param_name=='custom_range')
        {
            var obj=$(param_value).parent();
            var date_start=$('input[name="start"]', $(obj)).val();
            date_start=date_start.split('.');
            date_start=date_start[2] + '-' + date_start[1] + '-' + date_start[0];

            var date_end=$('input[name="end"]', $(obj)).val();
            date_end=date_end.split('.');
            date_end=date_end[2] + '-' + date_end[1] + '-' + date_end[0];

            if ($('#report_params input[name="date_start"]').length)
            {
                $('#report_params input[name="date_start"]').val(date_start);
            }
            else
            {
                $('#report_params').append('<input type="hidden" name="date_start" value="'+date_start+'" />');
            }

            if ($('#report_params input[name="date_end"]').length)
            {
                $('#report_params input[name="date_end"]').val(date_end);
            }
            else
            {
                $('#report_params').append('<input type="hidden" name="date_end" value="'+date_end+'" />');
            }

            // Remove report_period field, dates are already set
            if ($('#report_params input[name="report_period"]').length)
            {
                $('#report_params input[name="report_period"]').val('custom');
            }
            else
            {
                $('#report_params').append('<input type="hidden" name="report_period" value="custom" />');
            }

            $("#report_params").submit();

            return false;
        }

        var arr_param_names=param_name.toString().split ('||');
        var arr_param_values=param_value.toString().split ('||');
        var names=[]; var values=[];

        arr_param_names.forEach(function(item, i)
        {
            names[i]=item.toString().split ('|');
        });
        values=arr_param_values;

        var param_value_index=0;

        for (var i = 0; i < names.length; i++)
        {
            param_name=names[i];
            param_value=values[i];

            switch (param_name[0])
            {
                case 'filter': case 'force_filter':
                if (param_name[0]=='force_filter')
                {
                    // Remove previous filters
                    $('#report_params [name="filter_by[]"]').remove();
                    $('#report_params [name="filter_value[]"]').remove();
                }

                for (var k=1; k<param_name.length; k++)
                {
                    $('#report_params').append(
                        $('<input/>')
                            .attr('type', 'hidden')
                            .attr('name', 'filter_by[]')
                            .val(param_name[k])
                    );

                    $('#report_params').append(
                        $('<input/>')
                            .attr('type', 'hidden')
                            .attr('name', 'filter_value[]')
                            .val(values[param_value_index])
                    );
                    param_value_index++;
                }
                break;

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

                    // Remove date_start and date_end fields, we have custom period
                    if (param_name=='report_period')
                    {
                        $('#report_params input[name="date_start"]').remove();
                        $('#report_params input[name="date_end"]').remove();
                    }
                    param_value_index++;
                    break;
            }
        }

        $("#report_params").submit();
        return false;
    }

    function load_flow(obj)
    {
        $.post(
            'index.php?ajax_act=a_load_sales_flow',
            $("#report_params").serialize()
        ).done
        (
            function(response)
            {
                var data=jQuery.parseJSON(response);
                $.get('<?php echo _HTML_TEMPLATE_PATH.'/views/sales-flow-rows.mustache';?>', function(template)
                {
                    var d=[];
                    d['flow_rows']=data['data'];
                    var rendered = Mustache.render(template, d);
                    $('#sales-flow tbody').first().append(rendered);
                });

                if ($('#report_params input[name="offset"]').length)
                {
                    $('#report_params input[name="offset"]').val(data['offset']);
                }
                else
                {
                    $('#report_params').append('<input type="hidden" name="'+'offset'+'" value="'+data['offset']+'" />');
                }
                if(!data['more']){$(obj).hide();}
            }
        );
        return false;
    }

    function delete_sale(obj, conversion_id)
    {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_sale&conversion_id=' + conversion_id
        }).done(function(msg)
        {
            $("#sales_"+conversion_id+"_main").remove();
            $("#sales_"+conversion_id+"_hidden").remove();
        });

        return false;
    }
</script>

<script>
    $(document).ready(function()
    {
        result='';
        tmp = [];
        location.search.substr(1).split("&").forEach(function (item)
        {
            tmp = item.split("=");
            if (tmp[0] === 'filter_actions') result = decodeURIComponent(tmp[1]);
        });
        switch (result)
        {
            case 'sales':
                show_columns('report-main-report', '.c-sale', '.c-action, .c-lead', '.c-cell');
                break;

            case 'leads':
                show_columns('report-main-report', '.c-lead', '.c-action, .c-sale', '.c-cell');
                break;

            default:
                show_columns('report-main-report', '.c-action', '.c-sale, .c-lead', '.c-cell');
            break;
        }
    });

    function show_columns(id, show_class, hide_class, visibility_class)
    {
        if (typeof visibility_class !== 'undefined')
        {
            $(visibility_class, $('#'+id)).css('visibility', "");
        }

        $(hide_class, $('#'+id)).hide();
        $(show_class, $('#'+id)).show();
    }
</script>


<link href="<?php echo _HTML_LIB_PATH; ?>/datatables/css/dt_bootstrap.css" rel="stylesheet">
<script src="<?php echo _HTML_LIB_PATH; ?>/datatables/js/jquery.dataTables.min.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/datatables/js/dt_bootstrap.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/sparkline/jquery.sparkline.min.js"></script>
<link href="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/daterangepicker.js"></script>
<link href="<?php echo _HTML_LIB_PATH; ?>/datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet"/>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH; ?>/datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH; ?>/datepicker/locales/bootstrap-datepicker.ru.min.js"></script>
<?php
// [!] REMOVE BELOW ================================================================

?>

<link href="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker.js"></script>