<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<?php
// Create dates array for reports
$date1 = date('Y-m-d', strtotime('-6 days', strtotime(date('Y-m-d'))));
$date2 = date('Y-m-d');
$arr_dates = getDatesBetween($date1, $date2);


// Кнопки типов статистики
$type_buttons = array(
	'all_stats' => 'Все посетители',
	'daily_stats' => 'По дням',
	'monthly_stats' => 'По месяцам',
);

$type = rq('type', 0, 'daily_stats');
$subtype = rq('subtype'); // XSS ОПАСНО!!!

// Определяем названия отчётов
switch ($subtype) {
    case 'out_id':
        $report_name = "Переходы по ссылкам";
        $report_main_column_name = "Ссылка";
        $empty_name = "Без ссылки";
        break;

    case 'source_name':
        $report_name = "Переходы по источникам";
        $report_main_column_name = "Источник";
        $empty_name = "Без источника";
        break;
}

// Литералы для группировок
$group_types = array(
	'out_id'          => array('Ссылка', 'Без ссылки'), 
	'campaign_name'   => array('Кампания', 'Не определена'),
	'source_name'     => array('Источник', 'Не определён'),
	'ads_name'        => array('Объявление', 'Без объявления'),
	'referer'         => array('Площадка', 'Не определена'),
	'user_os'         => array('ОС', 'Не определена'),
	'user_platform'   => array('Платформа', 'Не определена'),
	'user_browser'    => array('Браузер', 'Не определен'),
	'country'         => array('Страна', 'Не определена'),
	'state'           => array('Регион', 'Не определен'),
	'city'            => array('Город', 'Не определен'),
	'isp'             => array('Провайдер', 'Не определен'),
	'campaign_param1' => array('Параметр ссылки #1', 'Не определен'),
	'campaign_param2' => array('Параметр ссылки #2', 'Не определен'),
	'campaign_param3' => array('Параметр ссылки #3', 'Не определен'),
	'campaign_param4' => array('Параметр ссылки #4', 'Не определен'),
	'campaign_param5' => array('Параметр ссылки #5', 'Не определен'),
	'click_param_value1'  => array('Параметр перехода #1', 'Не определен'),
	'click_param_value2'  => array('Параметр перехода #2', 'Не определен'),
	'click_param_value3'  => array('Параметр перехода #3', 'Не определен'),
	'click_param_value4'  => array('Параметр перехода #4', 'Не определен'),
	'click_param_value5'  => array('Параметр перехода #5', 'Не определен'),
	'click_param_value6'  => array('Параметр перехода #6', 'Не определен'),
	'click_param_value7'  => array('Параметр перехода #7', 'Не определен'),
	'click_param_value8'  => array('Параметр перехода #8', 'Не определен'),
	'click_param_value9'  => array('Параметр перехода #9', 'Не определен'),
	'click_param_value10' => array('Параметр перехода #10', 'Не определен'),
	'click_param_value11' => array('Параметр перехода #11', 'Не определен'),
	'click_param_value12' => array('Параметр перехода #12', 'Не определен'),
	'click_param_value13' => array('Параметр перехода #13', 'Не определен'),
	'click_param_value14' => array('Параметр перехода #14', 'Не определен'),
	'click_param_value15' => array('Параметр перехода #15', 'Не определен'),
);


// Функция вывода кнопок статистики в интерфейс
function type_subpanel() {
	global $type_buttons, $type;
	$out = '<div class="btn-group">';
    foreach($type_buttons as $k => $v) {
    	$out .= '<a href="?act=reports&type='.$k.'&subtype='.$_GET['subtype'].'" type="button" class="btn btn-default '.($type==$k ? 'active' : '').'">'.$v.'</a>';
    }
    $out .= '</div>';
    return $out;
}

switch ($_REQUEST['type']) {
    case 'daily_stats':

        $from = $_REQUEST['from'];
        $to = $_REQUEST['to'];
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
        
        echo '<form method="post" name="datachangeform" id="range_form">
                <div class="pull-left"><h3>' . $report_name . '</h3></div>
                <div id="per_day_range" class="pull-left" style="">
                    <span id="cur_day_range">'.date('d.m.Y', strtotime($from)).' - '. date('d.m.Y', strtotime($to)).'</span> <b class="caret"></b>
                    <input type="hidden" name="from" id="sStart" value="">
                    <input type="hidden" name="to" id="sEnd" value="">
                </div>
                <div class="pull-right" style="margin-top:18px;">' . type_subpanel() . '</div>
              </form>
        	<div class="row"></div>';

            // Show report data
            include _TRACK_SHOW_PATH."/pages/report_daily.inc.php";

        break;
     case 'monthly_stats':

            $from = $_REQUEST['from'];
            $to = $_REQUEST['to'];
            if ($from == '') {
                if ($to == '') {
                    $from = get_current_day('-6 months');
                    $to = get_current_day();
                } else {
                    $from = date('d.m.Y', strtotime('-6 months', strtotime($to)));
                }
            } else {
                if ($to == '') {
                    $to = date('d.m.Y', strtotime('+6 months', strtotime($from)));
                } else {
                     $from=date ('Y-m-d',  strtotime('13.'.$from));
                     $to=date ('Y-m-d', strtotime('13.'.$to));
                }
            }
            $from=date ('Y-m-01',  strtotime($from));
            $to=date ('Y-m-t',  strtotime($to));
            $fromF = date('m.Y', strtotime($from));
            $toF = date('m.Y', strtotime($to));

            echo '<form method="post"  name="datachangeform">
             		<div class="pull-left"><h3>' . $report_name . '</h3></div>
                    <div style="width: 240px; float: left; margin-top: 18px; margin-left: 5px;">
                        <div class="input-group">                          
                              <div class="input-group-addon "><i class="fa fa-calendar"></i></div>
                      
                              <input style="display: inline; float:left; width: 80px;   border-right: 0;" id="dpMonthsF"   type="text"  data-date="102/2012" data-date-format="mm.yyyy" data-date-viewmode="years" data-date-minviewmode="months"  class="form-control"  name="from" value="' . $fromF . '">
                              <input style="display: inline; float:left; width: 80px;  border-right: 0;  border-top-right-radius: 0; border-bottom-right-radius: 0;" id="dpMonthsT"   type="text"  data-date="102/2012" data-date-format="mm.yyyy" data-date-viewmode="years" data-date-minviewmode="months" type="text" class="form-control"   name="to" value="' . $toF . '">
                              <button type="button" style="width:40px;" class="btn btn-default form-control" onclick="$(\'[name = datachangeform]\').submit();"><i class="glyphicon glyphicon-search"></i></button>  
                        </div>

				 
                    </div>
                    <div class="pull-right" style="margin-top:18px;">' . type_subpanel() . '</div>
                  </form>
                    <div class="row"></div>';

            // Show report data
            include _TRACK_SHOW_PATH."/pages/report_monthly.inc.php";
            break;
    case 'daily_grouped':
        // Show report data
        include _TRACK_SHOW_PATH."/pages/report_daily_grouped.inc.php";
    break;
    
    case 'all_stats':
    	
    	$from = $_REQUEST['from'];
        $to = $_REQUEST['to'];
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
//    $('#putdate_range').daterangepicker({format: 'DD.MM.YYYY', locale: {applyLabel: "Выбрать", cancelLabel: "<i class='fa fa-times' style='color:gray'></i>", fromLabel: "От", toLabel: "До", customRangeLabel: 'Свой интервал', daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
//        }});
    
    
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
<?php if($type != 'targetreport' and $type != 'all_stats') { ?>
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
