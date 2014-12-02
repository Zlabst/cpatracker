<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<?php

// Create dates array for reports
$date1      = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
$date2      = date('Y-m-d');
$arr_dates  = getDatesBetween($date1, $date2);

$conv       = rq('conv');
$type       = rq('type', 0, 'daily_stats');
$subtype    = rq('subtype'); // XSS ОПАСНО!!!
//$mode       = rq('mode');
$limited_to = rq('limited_to');
$group_by   = rq('group_by', 0, $subtype);
$part       = rq('part', 0, 'all');

$from       = rq('from', 4, '');
$to         = rq('to', 4, '');


switch ($_REQUEST['type']) {
	case 'basic':
	
	// Параметры отчёта
	$params = report_options();
	
	//$params['where'] = "`is_connected` = '0'"; // только лэндинги
	//$params['mode'] = 'lp';
	
	if($params['mode'] == 'popular') {
		$params['mode'] = 'popular';
		$assign['report_name'] = 'Популярные параметры за ';
		$assign['report_params'] = $params;
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		
		$report = get_clicks_report_grouped2($params);
		
		$assign['click_params'] = $report['click_params'];
		$assign['arr_report_data'] = $report['data'];
		$assign['arr_dates'] = $report['dates'];
		
		// Заголовок отчета
		echo tpx('report_name', $assign);

		// Фильтры конвертации
		//echo tpx('report_conv', $assign);
		
		// Фильтры
		echo tpx('report_groups', $assign);
		
		// Таблица отчета
		echo tpx('report_table', $assign);
		
	} elseif($params['mode'] == 'lp' or $params['mode'] == 'lp_offers') { 
		
		$group_types['out_id'][0] = 'Целевая страница';
		$params['mode'] = 'lp_offers';
		
		$assign = $params;
		$assign['report_params'] = $params;
		
		$assign['report_name'] = 'Целевые страницы за ';
		$report = get_clicks_report_grouped2($params);
		
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		
		$assign['arr_report_data'] = $report['data'];
		$assign['click_params'] = $report['click_params'];
		$assign['arr_dates'] = $report['dates'];
		
		// Заголовок отчета
		echo tpx('report_name', $assign);
		
		// Фильтры
		//echo tpx('report_conv', $assign);
		
		if(!empty($report['data'])) {
			// Фильтры
			echo tpx('report_groups_lp', $assign);
			
			// Таблица отчета
			echo tpx('report_table', $assign);
		}
		/*
		if(!empty($report['data'])) {
		
			
			
			// Таблица отчета
			echo tpx('report_table', $assign);
			
			// Целевые страницы с подчинненными офферами
			if($part == 'all') {
				$params['mode'] = 'lp_offers';
				$assign['report_params'] = $params;
				$report = get_clicks_report_grouped2($params);
				$assign['arr_report_data'] = $report['data'];
				
				if(!empty($report['data'])) {
					echo '<div class="col-sm-9"><h3>Целевые страницы</h3></div>';
					// Таблица отчета
					echo tpx('report_table', $assign);
				}
			} else {
			
			}
			
		}
		*/
		
	} else {
		$report = get_clicks_report_grouped2($params);

		// Собираем переменные в шаблон
		$assign = $params;
		$assign['campaign_params'] = $report['campaign_params'];
		$assign['click_params'] = $report['click_params'];
		$assign['report_params'] = $params;
		$assign['report_name'] = col_name($params, true) . ' за ';
		$assign['timestep'] = ($params['part'] == 'month' ? 'monthly' : 'daily');
		$assign['arr_report_data'] = $report['data'];
		$assign['arr_dates'] = $report['dates'];
		
		//click_params

		// Заголовок отчета
		echo tpx('report_name', $assign);
		
		// Фильтры
		//echo tpx('report_conv', $assign);
		
		// Фильтры
		echo tpx('report_groups', $assign);

		// Таблица отчета
		echo tpx('report_table', $assign);
		
		// Если в Отчете по переходам выбран разрез Источник, то выводим таблицу Целевые страницы, добавляем к ней столбец Целевая страница и делаем источники кликабельными. 
		if(in_array($params['group_by'], array('source_name', 'ads_name', 'campaign_name', 'referer'))) {
		
			$params['where'] = '';
			$params['mode'] = 'lp';
			$assign['report_params'] = $params;
			$report = get_clicks_report_grouped2($params);
			$assign['arr_report_data'] = $report['data'];
			
			if(!empty($report['data'])) {
			
				echo '<div class="col-sm-9"><h3>Целевые страницы</h3></div>';
			
				// Таблица отчета
				echo tpx('report_table', $assign);
			}
		
		}
	}
	
	
	
	//dmp($report['data']);
	
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
	        
	        hashes = window.location.href.split('&');
	        for(var i = 0; i < hashes.length; i++) {
			    hash = hashes[i].split('=');
			    if(hash[0] == 'from') {
			    	hashes[i] = 'from=' + start.format('YYYY-MM-DD');
			    }
			    if(hash[0] == 'to') {
			    	hashes[i] = 'to=' + end.format('YYYY-MM-DD');
			    }
			}
			history.pushState(null, null, hashes.join('&'));
			
	        //console.log($('#range_form').serialize());
	        $('#range_form').submit();
	    }
    );
    
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
	}
    
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
		if(a.indexOf('sortdata') + 1) {
			a = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + a + '</div>')).text().split('|');
			b = $('.' + $('#type_selected').val() + ' .sortdata', $('<div>' + b + '</div>')).text().split('|');
			return srt_data(a, b, 0, 1);
		} else {
			x = cnv2(a);
			y = cnv2(b);
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
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
			return ((x < y) ? 1 : ((x > y) ? -1 : 0));
		}
    };
</script>
<?php 

// Нижние кнопки 
$currency = rq('currency', 0, 'usd');
$col      = rq('col', 0, 'all_actions');

if($params['conv'] == 'lead') {
	$col == 'leads';
};
	
$option_leads_type = array(
	'sale_lead' => 'Все действия',
	'sale' => 'Продажи',
	'lead' => 'Лиды'
);

$option_currency = array(
	'currency_rub' => '<i class="fa fa-rub"></i>',
	'currency_usd' => '$',
);	

// Проверяем на соответствие существующим типам

if(empty($option_leads_type[$col])) 
	$col = 'sale_lead';

if(empty($option_currency['currency_' . $currency])) 
	$currency = 'usd';

echo tpx('report_toolbar');
/*
if(!empty($report['data'])) {
if($type != 'all_stats' and $part != 'all') {
	if($params['mode'] == 'lp_offers') {
		if($conv != 'none') {
			$group_actions = array(
				'sale_lead' => array('cnt', 'repeated', 'lpctr', 'sale_lead', 'conversion_a', 'price', 'profit', 'cpa'),
				'sale' => array('cnt', 'repeated', 'lpctr', 'sale', 'conversion', 'price', 'profit', 'epc', 'roi'),
				'lead' => array('cnt', 'repeated', 'lpctr', 'lead', 'conversion_l', 'price', 'cpl')
			);
			
			$panels = array(
				'sale_lead' => 'Все действия',
				'sale'      => 'Продажи',
				'lead'      => 'Лиды'
			);
		} else {
			$group_actions = array(
				'sale_lead' => array('cnt', 'repeated', 'lpctr', 'price'),
				'sale'      => array('cnt', 'repeated', 'lpctr', 'price'),
				'lead'      => array('cnt', 'repeated', 'lpctr', 'price')
			);
			$panels = array();
		}
		?>
		<div class="row" id='report_toolbar'>
    <div class="col-md-12">
        <div class="form-group">
            	<?php
            		$i = 0;
            		foreach($group_actions as $group => $actions) {
            			echo '<div class="btn-group rt_types rt_type_'.$group.'" data-toggle="buttons" style="'.($i > 0 ? 'display: none' : '').'">';
            			foreach($actions as $action) {
            				echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="update_stats2(\''.$action.'\', '.($report_cols[$action]['money'] == 1 ? 'true' : 'false' ).');"><input type="radio" name="option_report_type">'.$report_cols[$action]['name'].'</label>';
            			$i++;
            			}
            			
            			echo '</div>';
            		}
            		
            		if(!empty($panels)) {
	            		echo '<div class="btn-group" id="rt_sale_section" data-toggle="buttons" >';
	            		
	            		$i = 0;
	            		foreach($panels as $value => $name) {
	            			echo '<label class="btn btn-default '.($i == 0 ? 'active' : '').'" onclick="show_conv_mode(\''.$value.'\')"><input type="radio" name="option_leads_type">' . $name . '</label>';
	            			$i++;
	            		}
	            		echo '</div>';
            		}
            	?>
            <div class="btn-group pull-right" id="rt_currency_section" data-toggle="buttons" style="display: none">
                <label class="btn btn-default" onclick='show_currency("rub");'><input type="radio" name="option_currency">руб.</label>
                <label class="btn btn-default active" onclick='show_currency("usd");'><input type="radio" name="option_currency">$</label>	
            </div>
        </div>
    </div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<script><?php 
		echo "show_conv_mode('" . $col . "', 0);";  // вкладка "Все действия"
		echo "update_stats2('cnt', false);";        // кнопка "Переходы"
		echo "show_currency('" . $currency . "');"; // валюта
		?>
	</script>		
<?php	} else { ?>
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

            <div class="btn-group invisible pull-right" id='rt_currency_section' data-toggle="buttons">
                <label class="btn btn-default" onclick='update_stats("currency_rub");'><input type="radio" name="option_currency">руб.</label>
                <label class="btn btn-default active" onclick='update_stats("currency_usd");'><input type="radio" name="option_currency">$</label>	
            </div>
        </div>
    </div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<?php } } elseif($part == 'all') { 
?>
<div id="report_toolbar" class="row">
	<div class="col-md-12">
		<div class="form-group">
	  		<div id="rt_sale_section" class="btn-group" <?php if($params['mode'] != 'popular' and 0) { ?>data-toggle="buttons"<?php } ?>>
	  			<?php
	  				// Изначально этот фильтр позволял переключать колонки без перезагрузки страницы, потому что они они все уже были на странице.
	  				// Но с появлением режима "Популярные" появляется необходимость перезагружать страницу, а с появлением фильтров конверсии (Все, Только продажи, и.т.д.) эта необходимость переходит на все отчёты
					if($params['mode'] == 'popular' or 1) {
						foreach($option_leads_type as $k => $v) {
							$new_params = array('col' => $k);
							if(in_array($params['conv'], array('sale', 'lead', 'sale_lead'))) {
								$new_params['conv'] = $k;
							}
							
		  					echo '<a class="btn btn-default'.($col == $k ? ' active' : '').'" href="'.report_lnk($params, $new_params).'">' . $v . '</a>';
		  				}
					} else {
		  				foreach($option_leads_type as $k => $v) {
		  					echo '<label class="btn btn-default'.($col == $k ? ' active' : '').'" onclick="update_cols(\''.$k.'\');">
						<input type="radio" name="option_leads_type">
						' . $v . '
					</label>';
		  				}
	  				}
	  			?>
			</div>

			<div id="rt_currency_section" class="btn-group pull-right" data-toggle="buttons">
				<?php
					foreach($option_currency as $k => $v) {
	  					echo '<label class="btn btn-default'.('currency_' . $currency == $k ? ' active' : '').'" onclick="update_cols(\''.$k.'\');">
					<input type="radio" name="option_leads_type">
					' . $v . '
				</label>';
	  				}
				?>
			</div>
			
			
		</div>
	</div>
</div>
<?php }
} // !empty($data) 
*/


if($part == 'all') { ?>
	<script><?php 
		echo "update_cols('" . $col . "', 0);";
		echo "update_cols('currency_" . $currency . "', 1);";
		?>
	</script>
<?php } ?>
<input type='hidden' id='usd_selected' value='1'>
<input type='hidden' id='type_selected' value='cnt'>
<input type='hidden' id='sales_selected' value='1'>