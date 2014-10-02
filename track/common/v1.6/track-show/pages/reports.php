<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<?php

// Create dates array for reports
$date1      = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
$date2      = date('Y-m-d');
$arr_dates  = getDatesBetween($date1, $date2);

$type       = rq('type', 0, 'daily_stats');
$subtype    = rq('subtype'); // XSS ОПАСНО!!!
$limited_to = rq('limited_to');
$group_by   = rq('group_by', 0, $subtype);
$part       = rq('part', 0, 'all');

$from       = rq('from', 4, '');
$to         = rq('to', 4, '');

// Определяем названия отчётов
switch ($subtype) {
    case 'out_id':
        $report_name = "Переходы по офферам";
    	$parent_link = "Все офферы";
        break;

    case 'source_name':
        $report_name = "Переходы по источникам";
    	$parent_link = "Все источники";
        break;
}

switch ($_REQUEST['type']) {
	case 'basic':
	
	// Параметры отчёта
	$params = report_options();
	
	// Хлебные крошки
	if(!empty($params['filter'])) {
		
		//$breadcrumbs = array(report_lnk($params, array('filter' => array())) => 'Все данные');
		$i = 1;
		echo '<div><ol class="breadcrumb">
			<li><a href="' . report_lnk($params, array('filter' => array())) . '">Все данные</a></li>';
		// Для ссылок преобразуем ID в название	
		foreach($params['filter'] as $k => $v) {
			if($k == 'out_id') {
				$source_name = current(get_out_description($v));
			} else {
				$source_name = $v;
			}
			// Текущая ссылка
			if($i == count($params['filter'])) {
				echo '<li class="active">' . _e($source_name) . '</li>';
			} else {
				echo '<li class="active"><a href="' . report_lnk($params, array('filter' => array_slice($params['filter'], 0, $i))) . '">' . _e($source_name) . '</a></li>';
			}
			//echo '<li><a href="?act=reports&type=all_stats&subtype='._e($subtype).'">'.$parent_link.'</a></li><li class="active">'._e($source_name).'</li>';
			$i++;
		}
		echo '</ol></div>';
	}
	
	// Даты отчёта
	if($params['part'] == 'month') {
		$arr_dates = getMonthsBetween($params['from'], $params['to']);
	} elseif($params['part'] == 'day') {
		$arr_dates = getDatesBetween($params['from'], $params['to']);
	}
	
	$params['where'] = "`is_connected` = '0'"; // только лэндинги
	$arr_report_data = get_clicks_report_grouped2($params);

	// Оставляем даты, за которые есть данные
	$arr_dates = strip_empty_dates($arr_dates, $arr_report_data);

	//dmp($arr_report_data);

	// Собираем переменные в шаблон
	$assign = $params;
	$assign['report_params'] = $params;
	$assign['report_name'] = 'Отчёт по ' . $group_types[$params['group_by']][2] . ' за ';
	$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
	$assign['arr_report_data'] = $arr_report_data;
	$assign['arr_dates'] = $arr_dates;

	// Заголовок отчета
	echo tpx('report_name', $assign);

	// Фильтры
	echo tpx('report_groups', $assign);

	// Таблица отчета
	if($params['part'] == 'all') {
		echo tpx('report_click_all', $assign);
	} else {
		echo tpx('report_daily', $assign);
	}
	break;

    case 'daily_grouped':
        // Show report data
        include _TRACK_SHOW_PATH."/pages/report_daily_grouped.inc.php";
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
        
        echo '<form method="post"  name="datachangeform" id="range_form">
                <div class="pull-left"><h3>' . $report_name . '</h3></div>
                <div id="per_day_range" class="pull-left" style="">
                    <span id="cur_day_range">'.date('d.m.Y', strtotime($from)).' - '. date('d.m.Y', strtotime($to)).'</span> <b class="caret"></b>
                    <input type="hidden" name="from" id="sStart" value="">
                    <input type="hidden" name="to" id="sEnd" value="">
                </div>
                <div class="pull-right" style="margin-top:18px;">' . type_subpanel() . '</div>
              </form>';
    	
    	include _TRACK_SHOW_PATH."/pages/report_all.inc.php";
    break;
    
    case 'targetreport':
    	
    	$from = empty($_REQUEST['from']) ? date('Y-m-d', time() - 3600*24*6) : date('Y-m-d', strtotime($_REQUEST['from']));
    	$to =   empty($_REQUEST['to']) ? date('Y-m-d') :  date('Y-m-d', strtotime($_REQUEST['to']));
    	
    	include _TRACK_SHOW_PATH."/pages/targetreport.php";
   	break;
}
?>

<link href="<?php echo _HTML_LIB_PATH;?>/datatables/css/jquery.dataTables.css" rel="stylesheet">
<link href="<?php echo _HTML_LIB_PATH;?>/datatables/css/dt_bootstrap.css" rel="stylesheet">
<script src="<?php echo _HTML_LIB_PATH;?>/datatables/js/jquery.dataTables.min.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/datatables/js/dt_bootstrap.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/sparkline/jquery.sparkline.min.js"></script>
<link href="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH;?>/daterangepicker/daterangepicker.js"></script>
<link href="<?php echo _HTML_LIB_PATH;?>/datepicker/css/datepicker.css" rel="stylesheet"/>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH;?>/datepicker/js/bootstrap-datepicker.js"></script>

<script>
    $('#dpMonthsF').datepicker();
    $('#dpMonthsT').datepicker();
    
    <?php
    	$from = empty($_POST['from']) ? date('d.m.Y', time() - 3600*24*6) : date('d.m.Y', strtotime($_POST['from']));
    	$to = empty($_POST['to']) ? date('d.m.Y') :  date('d.m.Y', strtotime($_POST['to']));
    ?>
    
    $('#per_day_range').daterangepicker(
        {
            startDate: '<?php echo _e($from)?>',
            endDate: '<?php echo _e($to)?>',
            format: 'DD.MM.YYYY',
            locale: {
                applyLabel: "Выбрать",
                cancelLabel: "<i class='fa fa-times' style='color:gray'></i>",
                fromLabel: "От",
                toLabel: "До",
                customRangeLabel: 'Свой интервал',
                daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
            },
            ranges: {
                'Сегодня': [moment(), moment()],
                'Вчера': [moment().subtract('days', 1), moment().subtract('days', 1)],
                'Последние 7 дней': [moment().subtract('days', 6), moment()],
                'Последние 30 дней': [moment().subtract('days', 29), moment()],
                'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                'Прошлый месяц': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                
            }
        },
	    function(start, end) {
	        $('#cur_day_range').text(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
	        $('#sStart').val(start.format('YYYY-MM-DD'));
	        $('#sEnd').val(end.format('YYYY-MM-DD'));
	        $('#range_form').submit();
	    }
    );
    function cnv2(m) {
    	n = $('.clicks', $('<div>' + m + '</div>')).text();
        if(n != '') {
        	n = n.split(':')
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
		x = cnv2(a);
    	y = cnv2(b);
        return ((x[0] < y[0]) ? -1 : ((x[0] > y[0]) ? 1 : ((x[1] < y[1]) ? -1 : ((x[1] > y[1]) ? 1 : 0))));
    };
    jQuery.fn.dataTableExt.oSort['click-data-desc'] = function(a, b) {
    	x = cnv2(a);
    	y = cnv2(b);
        return ((x[0] < y[0]) ? 1 : ((x[0] > y[0]) ? -1 : ((x[1] < y[1]) ? 1 : ((x[1] > y[1]) ? -1 : 0))));
    };
</script>
<?php if($type != 'targetreport' and $type != 'all_stats' and $part != 'all') { ?>
<div class="row" id='report_toolbar'>
    <div class="col-md-12">
        <div class="form-group">
            <div class="btn-group" id='rt_type_section' data-toggle="buttons">
                <label id="rt_clicks_button" class="btn btn-default active" onclick='update_stats("clicks");'><input type="radio" name="option_report_type">Клики</label>
                <label id="rt_conversion_button" class="btn btn-default" onclick='update_stats("conversion");'><input type="radio" name="option_report_type">Конверсия</label>	
                <label id="rt_leadprice_button" class="btn btn-default" onclick='update_stats("lead_price");'><input type="radio" name="option_report_type">Стоимость лида</label>					
                <label id="rt_roi_button" class="btn btn-default" onclick='update_stats("roi");'><input type="radio" name="option_report_type">ROI</label>	
                <label id="rt_epc_button" class="btn btn-default" onclick='update_stats("epc");'><input type="radio" name="option_report_type">EPC</label>	
                <label id="rt_profit_button" class="btn btn-default" onclick='update_stats("profit");'><input type="radio" name="option_report_type">Прибыль</label>
            </div>

            <div class="btn-group" id='rt_sale_section' data-toggle="buttons">
                <label class="btn btn-default active" onclick='update_stats("sales");'><input type="radio" name="option_leads_type">Продажи</label>
                <label class="btn btn-default" onclick='update_stats("leads");'><input type="radio" name="option_leads_type">Лиды</label>	
            </div>

            <div class="btn-group invisible" id='rt_currency_section' data-toggle="buttons">
                <label class="btn btn-default" onclick='update_stats("currency_rub");'><input type="radio" name="option_currency">руб.</label>
                <label class="btn btn-default active" onclick='update_stats("currency_usd");'><input type="radio" name="option_currency">$</label>	
            </div>
        </div>
    </div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<?php } ?>
<input type='hidden' id='usd_selected' value='1'>
<input type='hidden' id='type_selected' value='clicks'>
<input type='hidden' id='sales_selected' value='1'>