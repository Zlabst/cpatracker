<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<style>
.sortdata {
    display: none;
}
</style>
<?php
	$date = rq('date', 4, get_current_day());
	$hour = rq('hour', 2);
	$prev_date=date('Y-m-d', strtotime('-1 days', strtotime($date)));
	$next_date=date('Y-m-d', strtotime('+1 days', strtotime($date)));

	$main_type   = rq('report_type', 0, 'source_name');
	$group_by    = $main_type;
	$limited_to  = '';
	$report_type = 'hourly';
	$from        = $date;
	$to          = $date;
	
	$params = array(
		'type'     => 'basic',
		'part'     => 'hour',
		'filter'   => array(),
		'group_by' => 'source_name',
		'subgroup_by' => 'source_name',
		'conv'     => 'all',
		'mode'     => '',
		'col'      => 'sale_lead',
		'from'     => $date,
		'to'       => $date,
	);
	
	$arr_report_data = get_clicks_report_grouped2($params);
	
	$arr_hourly = array();

	foreach ($arr_report_data['data'] as $row_name => $row_data) {
		foreach ($row_data as $cur_hour => $data) {
			if(!is_numeric($cur_hour)) continue;
			/*
			$clicks_data    = $data['click'];
			$leads_data     = $data['lead'];
			$sales_data     = $data['sale'];
			$saleleads_data = $data['sale_lead'];
			*/
			$arr_hourly[$row_name][$cur_hour] = get_clicks_report_element ($data, false);
		}			
	}
	
	if($_SERVER['REMOTE_ADDR'] == '178.121.223.216') {
		dmp($arr_report_data);
		dmp($arr_hourly);
	}

	echo "<div class='row'>";
	echo "<div class='col-md-12'>";
	echo "<p align=center>";
	if ($date != get_current_day()) {
		echo "<a style='float:right;' href='?date={$next_date}&report_type={$main_type}'>".mysqldate2string($next_date)." &rarr;</a>";
	} else {
		echo "<a style='float:right; visibility:hidden;' href='?date={$next_date}&report_type={$main_type}'>".mysqldate2string($next_date)." &rarr;</a>";
	}
	echo "<b>".mysqldate2string($date)."</b>";
	echo "<a style='float:left;' href='?date={$prev_date}&report_type={$main_type}'>&larr; ".mysqldate2string($prev_date)."</a></p>";


	echo "<table class='table table-striped table-bordered table-condensed'>";
		echo "<tbody>";	
			echo "<tr>";
			echo "<td>";
				echo "<div class='btn-group'>";
				switch ($main_type)
				{
					case 'out_id': 
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Оффер <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=source_name'>Источник</a></li>
							  </ul>";
					break;
					
					default: 
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Источник <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=out_id'>Оффер</a></li>
							  </ul>";
					break;
				}
				 echo "</div>";			
			echo "</td>";			
			for ($i=0;$i<24; $i++)
			{
				echo "<td>".sprintf('%02d', $i)."</td>";
			}
			echo "</tr>";		
			echo "<tr>";
			
			foreach ($arr_hourly as $source_name => $data)
			{
				switch ($main_type)
				{
					case 'out_id': 
						$source_name=get_out_description($source_name);
						if ($source_name=='' || $source_name=='{empty}'){$source_name_show='Без оффера';}
						echo "<td>"._e($source_name[0])."</td>";	
					break;
					
					default: 
						if ($source_name=='' || $source_name=='{empty}') { 
							$source_name = 'Без&nbsp;источника'; $source_name_lnk = ''; 
						} else { 
							$source_name_lnk = $source_name;
							$source_name = empty($source_config[$source_name]['name']) ? $source_name : $source_config[$source_name]['name'];
						}
						echo "<td>"._e($source_name)."</td>";	
					break;
				}
				for ($i=0;$i<24; $i++)
				{
					if ($data[$i]!='')
					{
						echo "<td><a style='text-decoration:none; color:black;' href='?filter_by=hour&source_name="._e($source_name_lnk)."&date=$date&hour=$i'>{$data[$i]}</a></td>";	
					}
					else
					{
						echo "<td></td>";
					}
				}
				echo "</tr>";
			}
		echo "</tbody>";
	echo "</table>";
echo "</div> <!-- ./col-md-12 -->";	
echo "</div> <!-- ./row -->";
// **********************************************
?>

<div class="row" id='report_toolbar'>
	<div class="col-md-12">
		<div class="form-group">

			<div class="btn-group invisible" id='rt_type_section' data-toggle="buttons">
				<label id="rt_clicks_button" class="btn btn-default active" onclick='update_stats("clicks");'><input type="radio" name="option_report_type">Клики</label>
				<label id="rt_conversion_button" class="btn btn-default" onclick='update_stats("conversion");'><input type="radio" name="option_report_type">Конверсия</label>	
				<label id="rt_leadprice_button" class="btn btn-default" onclick='update_stats("lead_price");'><input type="radio" name="option_report_type">Стоимость лида</label>					
				<label id="rt_roi_button" class="btn btn-default" onclick='update_stats("roi");'><input type="radio" name="option_report_type">ROI</label>	
				<label id="rt_epc_button" class="btn btn-default" onclick='update_stats("epc");'><input type="radio" name="option_report_type">EPC</label>	
				<label id="rt_profit_button" class="btn btn-default" onclick='update_stats("profit");'><input type="radio" name="option_report_type">Прибыль</label>
			</div>

			<div class="btn-group invisible" id='rt_sale_section' data-toggle="buttons">
				<!--<label class="btn btn-default active" onclick='update_stats("sale_lead");'><input type="radio" name="option_leads_type">Все действия</label>-->
				<label class="btn btn-default" onclick='update_stats("sale");'><input type="radio" name="option_leads_type">Продажи</label>
				<label class="btn btn-default" onclick='update_stats("lead");'><input type="radio" name="option_leads_type">Лиды</label>	
			</div>

			<div class="btn-group invisible" id='rt_currency_section' data-toggle="buttons">
				<label class="btn btn-default" onclick='update_stats("currency_rub");'><input type="radio" name="option_currency"><i class="fa fa-rub"></i></label>
				<label class="btn btn-default active" onclick='update_stats("currency_usd");'><input type="radio" name="option_currency">$</label>	
			</div>

			<div class="btn-group pull-right">
				<button type="button" class="btn btn-default" title="Параметры отчета" onclick='toggle_report_toolbar()'><i class='fa fa-cog'></i></button>
			</div>		
		</div>
	</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->

<input type='hidden' id='usd_selected' value='1'>
<input type='hidden' id='type_selected' value='clicks'>
<input type='hidden' id='sales_selected' value='1'>


<?php
// ********************************************************

if(!empty($arr_data)) {

	echo "<h4>Лента переходов за ".sdate($date).'<span style="float:right;">'."<a title='Экспорт в Excel' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=excel_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-excel.png'></a></span><span style='float:right; margin-right:16px;'><a title='Экспорт в TSV' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=tsv_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-tsv.png'></a></span>".'<div class="col-xs-4" style="float: right; margin-bottom: 7px;"><form action="" method="get"><input type="hidden" name="filter_by" value="search"/><input type="hidden" name="date" value="'.$date.'"/><input name="search" class="form-control" " type="text" value="'._e($search).'" placeholder="поиск" /></form></div>'."</h4>";

	echo "<table class='table table-striped' id='stats-flow'><thead>
			<tr><th></th><th></th><th>Ссылка</th><th>Источник</th><th>Кампания</th><th colspan=\"6\">Реферер</th><th></th></tr>
		</thead>";
	echo "<tbody>";
	foreach ($arr_data as $row) {
		require _TRACK_SHOW_COMMON_PATH . '/pages/stats-flow-row.php';
	}
	echo "</tbody></table>";
	if($total > 20) {
		echo '<a href="#" onclick="return load_flow(this)" class="center-block text-center">Показать больше</a>';
		
		?>
<script type="text/javascript">
	function load_flow(obj) {
		$.post(
            'index.php?ajax_act=a_load_flow', {
                offset: $('#stats-flow tbody').children().length / 2 ,
                date: '<?php echo _str($date) ?>',
                hour: '<?php echo _str($hour) ?>',
                filter_by: '<?php echo _str($_REQUEST['filter_by']) ?>',
                value: '<?php echo _str($_REQUEST['value']) ?>',
                source_name: '<?php echo _str($_REQUEST['source_name']) ?>'
            }
        ).done(
        	function(data) {
        		if(data == '') {
        			$(obj).hide();
        		} else {
            		$('#stats-flow tbody').children().last().after(data);
            	}
            }
        ); 
		return false;
	}	
</script><?php 
	}
}
?>
<script>show_conv_mode('sale', 0);update_stats2('cnt', false);show_currency('usd');</script>