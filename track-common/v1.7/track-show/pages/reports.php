<?php
if (!$include_flag) {exit();}

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$arr_currencies_list=get_active_currencies();
$selected_currency=current($arr_currencies_list);

$arr_report_data=prepare_report('main-report', $_REQUEST+array('report_params'=>array('act'=>'reports')));

foreach ($arr_report_data['report_params'] as $cur)
{
    if ($cur['name']=='currency_id')
    {
        $selected_currency=$arr_currencies_list[$cur['value']];
        break;
    }
}

echo $mTemplate->render('report-page', $arr_report_data+array(
        'currencies'=>array_values($arr_currencies_list),
        'selected_currency_symbol'=>$selected_currency['symbol'])
);

?>
<script>
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
            $('#report_params input[name="report_period"]').remove();
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

<?php
// ************************* REMOVE BELOW ***********************************
// Create dates array for reports
$date1 = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
$date2 = date('Y-m-d');
$arr_dates = getDatesBetween($date1, $date2);

$conv = rq('conv');
$type = rq('type', 0, 'daily_stats');
$subtype = rq('subtype'); // XSS ОПАСНО!!!

$limited_to = rq('limited_to');
$group_by = rq('group_by', 0, $subtype);
$part = rq('part', 0, 'all');

$from = rq('from', 4, '');
$to = rq('to', 4, '');


// Нижние кнопки 
$currency = rq('currency', 0, 'usd');
$col = rq('col', 0, 'act');

if ($params['conv'] == 'lead') {
    $col == 'leads';
};

$option_leads_type = array(
    'act' => 'Все действия',
    'sale' => 'Продажи',
    'lead' => 'Лиды'
);

// Проверяем на соответствие существующим типам

if (empty($option_leads_type[$col]))
    $col = 'act';

if (empty($option_currency[$currency]))
    $currency = 'usd';

if ($part == 'all') {
    ?><style><?php
    switch ($col) {
        case 'act':
            echo '.col_s:not(.col_a) {display: none;} .col_l:not(.col_a) {display: none;} ';
            break;
        case 'sale':
            echo '.col_a:not(.col_s) {display: none;} .col_l:not(.col_s) {display: none;}';
            break;
        case 'lead':
            echo '.col_a:not(.col_l) {display: none;} .col_s:not(.col_l) {display: none;}';
            break;
    }
    ?></style>
<?php
}

// ---------------------------------------
// [!] [!] [!!!]
switch ($_REQUEST['type'].'x')
{
    case 'basic':
        $params = report_options();

        if ($params['mode'] == 'popular')
        {
            $params['mode'] = 'popular';
            $assign['report_name'] = 'Популярные параметры <span class="amid">за</span> ';
            $assign['report_params'] = $params;
            $assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');

            $report = get_clicks_report_grouped2($params);

            $assign['click_params'] = $report['click_params'];
            $assign['arr_report_data'] = $report['data'];
            $assign['arr_dates'] = $report['dates'];
            $assign['toolbar'] = tpx('report_groups', $assign);

            // Заголовок отчета
            echo tpx('report_name', $assign);

            // Таблица отчета
            echo tpx('report_table', $assign);
        } elseif ($params['mode'] == 'lp' or $params['mode'] == 'lp_offers')
        {
            $group_types['out_id'][0] = 'Целевая страница';
            $params['mode'] = 'lp_offers';

            $assign = $params;
            $assign['report_params'] = $params;

            $assign['report_name'] = 'Целевые страницы <span class="amid">за</span> ';
            $report = get_clicks_report_grouped2($params);

            $assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');

            $assign['arr_report_data'] = $report['data'];
            $assign['click_params'] = $report['click_params'];
            $assign['arr_dates'] = $report['dates'];
            
            // Фильтры
            if (!empty($report['data'])) {
                $assign['toolbar'] = tpx('report_groups_lp', $assign);
            }
            
            // Заголовок отчета
            echo tpx('report_name', $assign);

            if (!empty($report['data'])) {
                // Таблица отчета
                echo tpx('report_table', $assign);
            }
        }
        else
        {
            $report = get_clicks_report_grouped2($params);

            // Собираем переменные в шаблон
            $assign = $params;
            $assign['campaign_params'] = $report['campaign_params'];
            $assign['click_params'] = $report['click_params'];
            $assign['report_params'] = $params;
            $assign['report_name'] = col_name($params, true) . ' <span class="amid">за</span> ';
            $assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
            $assign['arr_report_data'] = $report['data'];
            $assign['arr_dates'] = $report['dates'];
            $assign['toolbar'] = tpx('report_groups', $assign);

            // Заголовок отчета
            echo tpx('report_name', $assign);

            // Таблица отчета
            echo tpx('report_table', $assign);

            // Если в Отчете по переходам выбран разрез Источник, то выводим таблицу Целевые страницы, добавляем к ней столбец Целевая страница и делаем источники кликабельными. 
            //if(in_array($params['group_by'], array('source_name', 'ads_name', 'campaign_name', 'referer'))) {

            $params['where'] = '';
            $params['mode'] = 'lp';
            $assign['report_params'] = $params;
            $report_lp = get_clicks_report_grouped2($params);
            $assign['arr_report_data'] = $report_lp['data'];

            if (!empty($report_lp['data']))
            {
                echo '<div class="page-heading"><div class="header-content"><div class="header-report"><a ><h2>Целевые страницы</h2></a></div></div></div>';

                // Таблица отчета
                echo tpx('report_table', $assign);
            }

            // Возвращаем режим на место, иначе кнопки внизу будут вести на этот тип отчёта
            $params['mode'] = $assign['report_params']['mode'] = '';
        }

        break;

    case 'all_stats':
        if ($from == '') {
            if ($to == '') {
                $from = get_current_day('-6 days');
                $to = get_current_day();
            } else {
                $from = date('d.m.Y', strtotime('-6 days', strtotime($to)));
            }
        } else {
            if ($to == '') {
                $to = date('d.m.Y', strtotime('+6 days', strtotime($from)));
            } else {
                // Will use existing values
            }
        }

        $fromF = date('d.m.Y', strtotime($from));
        $toF = date('d.m.Y', strtotime($to));
        $value_date_range = "$fromF - $toF";

        /*
        echo '<form method="post"  name="datachangeform" id="range_form">
                <div class="pull-left"><h3>' . $report_name . '</h3></div>
                <div id="per_day_range" class="pull-left" style="">
                    <span id="cur_day_range"><span class="date">' . date('d.m.Y', strtotime($from)) . '</span> <span class="amid">—</span> <span class="date">' . date('d.m.Y', strtotime($to)) . '</span> <i class="cpa cpa-angle-down"></i></span>
                    <input type="hidden" name="from" id="sStart" value="">
                    <input type="hidden" name="to" id="sEnd" value="">
                </div>
                <div class="pull-right" style="margin-top:18px;"><div class="btn-group">' . join('', type_subpanel()) . '</div></div>
              </form>';
*/
        include _TRACK_SHOW_COMMON_PATH . "/pages/report_all.inc.php";
        break;

    case 'targetreport':

        $from = empty($_REQUEST['from']) ? date('Y-m-d', time() - 3600 * 24 * 6) : date('Y-m-d', strtotime($_REQUEST['from']));
        $to = empty($_REQUEST['to']) ? date('Y-m-d') : date('Y-m-d', strtotime($_REQUEST['to']));

        include _TRACK_SHOW_COMMON_PATH . "/pages/targetreport.php";
        break;
}

?>


<link href="<?php echo _HTML_LIB_PATH; ?>/datatables/css/jquery.dataTables.css" rel="stylesheet">
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

<script>
    $('#dpMonthsF').datepicker();
    $('#dpMonthsT').datepicker();
    
<?php
$from = empty($_POST['from']) ? date('d.m.Y', time() - 3600 * 24 * 6) : date('d.m.Y', strtotime($_POST['from']));
$to = empty($_POST['to']) ? date('d.m.Y') : date('d.m.Y', strtotime($_POST['to']));
?>

        // Многомерная сортировка
        srt_data = function(a, b, i, asc) {
            asc_work = (i == 3 || i == 0) ? 1 : asc; // порядок лэндинга и оффера одинаковый всегда
            maxlen = a.length;
            x = parseFloat(a[i]);
            y = parseFloat(b[i]);
            if(x < y) {
                return asc_work * -1;
            } else if(x > y) {
                return asc_work * 1;
            } else {
                if(i < maxlen - 1) {
                    i++;
                    return srt_data(a, b, i, asc);
                } else {
                    return 0;
                }
            }
        };
    
        function cnv2(m) {
            n = $('.cnt', $('<div>' + m + '</div>')).text();
            if(n != '') {
                n = n.split(':');
                if(n.length == 2) {
                    n0 = n[0]; n1 = n[1];
                } else if(n.length == 1) {
                    n0 = 0; n1 = n[0];
                } else {
                    n0 = 0; n1 = 0;
                }
            } else {
                n0 = 0; n1 = 0;
            }
            return [parseFloat(n0), parseFloat(n1)];
        }
    
        jQuery.fn.dataTableExt.oSort['click-data-asc'] = function(a, b) {
            if(a.indexOf('sortdata') + 1) {
                a = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + a + '</div>')).text().split('|');
                b = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + b + '</div>')).text().split('|');
                return srt_data(a, b, 0, 1);
            } else {
                x = cnv2(a);
                y = cnv2(b);
                return ((x[0] < y[0]) ? -1 : ((x[0] > y[0]) ? 1 : ((x[1] < y[1]) ? -1 : ((x[1] > y[1]) ? 1 : 0))   ));
            }
        };

        jQuery.fn.dataTableExt.oSort['click-data-desc'] = function(a, b) {
            if(a.indexOf('sortdata') + 1) {
                a = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + a + '</div>')).text().split('|');
                b = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + b + '</div>')).text().split('|');
                return srt_data(a, b, 0, -1);
            } else { 
                x = cnv2(a);
                y = cnv2(b);
                return ((x[0] < y[0]) ? 1 : ((x[0] > y[0]) ? -1 : ((x[1] < y[1]) ? 1 : ((x[1] > y[1]) ? -1 : 0))  ));
            }
        };
</script>
<input type="hidden" id="usd_selected" value="1">
<input type="hidden" id="type_selected" value="cnt">
<input type="hidden" id="sales_selected" value="1">