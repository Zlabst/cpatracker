<?php if (!$include_flag){exit();} ?>
<script src="<?php echo _HTML_TEMPLATE_PATH;?>/js/report_toolbar.js"></script>
<?php
	$date=($_REQUEST['date']?$_REQUEST['date']:get_current_day());
	$prev_date=date('Y-m-d', strtotime('-1 days', strtotime($date)));
	$next_date=date('Y-m-d', strtotime('+1 days', strtotime($date)));

	$main_type=($_REQUEST['report_type'])?$_REQUEST['report_type']:'source_name';
	$group_by=$main_type;
	$limited_to='';
	$report_type='hourly';
	$from=$date;
	$to=$date;
	$arr_report_data=get_clicks_report_grouped($main_type, $group_by, $limited_to, $report_type, $from, $to);
	
	$arr_hourly=array();

	foreach ($arr_report_data as $row_name=>$row_data)
	{
		foreach ($row_data as $cur_hour=>$data)
		{
			$clicks_data=$data['click'];
			$leads_data=$data['lead'];
			$sales_data=$data['sale'];
			$saleleads_data=$data['sale_lead'];

			$arr_hourly[$row_name][$cur_hour]=get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data);
		}			
	}

	echo "<div class='row'>";
	echo "<div class='col-md-12'>";
	echo "<p align=center>";
	if ($date!=get_current_day())
	{
		echo "<a style='float:right;' href='?date={$next_date}&report_type={$main_type}'>".mysqldate2string($next_date)." &rarr;</a>";
	}
	else
	{
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
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Ссылка <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=source_name'>Источник</a></li>
							  </ul>";
					break;
					
					default: 
						echo "<button class='btn btn-link dropdown-toggle' data-toggle='dropdown' style='padding:0; color:black; font-weight: bold;'>Источник <span class='caret'></span></button>
							  <ul class='dropdown-menu'>
							    <li><a href='?date={$date}&report_type=out_id'>Ссылка</a></li>
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
			foreach ($arr_hourly as $source_name=>$data)
			{
				switch ($main_type)
				{
					case 'out_id': 
						$source_name=get_out_description($source_name);
						if ($source_name=='' || $source_name=='{empty}'){$source_name='Без ссылки';}
						echo "<td>"._e($source_name[0])."</td>";	
					break;
					
					default: 
						if ($source_name=='' || $source_name=='{empty}'){$source_name='Без источника';}
						echo "<td>"._e($source_name)."</td>";	
					break;
				}
				for ($i=0;$i<24; $i++)
				{
					if ($data[$i]!='')
					{
						echo "<td><a style='text-decoration:none; color:black;' href='?filter_by=hour&source_name="._e($source_name)."&date=$date&hour=$i'>{$data[$i]}</a></td>";	
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
				<label class="btn btn-default active" onclick='update_stats("sales");'><input type="radio" name="option_leads_type">Продажи</label>
				<label class="btn btn-default" onclick='update_stats("leads");'><input type="radio" name="option_leads_type">Лиды</label>	
			</div>

			<div class="btn-group invisible" id='rt_currency_section' data-toggle="buttons">
				<label class="btn btn-default active" onclick='update_stats("currency_rub");'><input type="radio" name="option_currency">руб.</label>
				<label class="btn btn-default" onclick='update_stats("currency_usd");'><input type="radio" name="option_currency">$</label>	
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

	echo "<h4>Лента переходов за ".sdate($_REQUEST['date'])."<span style='float:right;'><a title='Экспорт в Excel' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=excel_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-excel.png'></a></span><span style='float:right; margin-right:16px;'><a title='Экспорт в TSV' href='?csrfkey="._e(CSRF_KEY)."&ajax_act=tsv_export&date="._e($date)."'><img src='"._HTML_TEMPLATE_PATH."/img/icons/table-tsv.png'></a></span></h4>";
	
	echo "<table class='table table-striped'>";
	echo "<tbody>";
	foreach ($arr_data as $row)
	{
		echo "<tr style='cursor:pointer;' onclick='$(this).next().toggle();'>";
			if ($row['country']==''){$country_title='';$country_icon='question.png';}else{$country_title="{$row['country']}"; 
			$country_icon=strtolower($row['country']).'.png';}
			echo "
			<td nowrap><img title='"._e($country_title)."' src='"._HTML_TEMPLATE_PATH."/img/countries/"._e($country_icon)."'> <i title='"._e($row['user_os'])." "._e($row['user_os_version'])."' class='b-favicon-os "._e(get_class_by_os($row['user_os']))."'></i> 
			<i title='"._e($row['user_platform'].' '.$row['user_platform_info'].' '.$row['user_platform_info_extra'])."' class='b-favicon-os "._e(get_class_by_platform($row['user_platform']))."'></i></td>
			<td nowrap title='"._e($row['dt'])."'>"._e($row['td'])."</td>
			<td><a href='?filter_by=rule_id&value={$row['rule_id']}'>".get_rule_description($row['rule_id'])."</a>&nbsp;&nbsp;&rarr;&nbsp;&nbsp;<a href='?filter_by=out_id&value={$row['out_id']}'>"._e(current(get_out_description($row['out_id'])))."</a></td>
			<td><a href='?filter_by=source_name&value="._e($row['source_name'])."'>"._e($row['source_name'])."</td>
			<td>"._e($row['campaign_name'].' '.$row['ads_name'])."</td>";
			
			$cur_referrer=str_replace (array('http://www.', 'www.'),'',$row['referer']);
			if (strpos($cur_referrer, 'http://')===0){$cur_referrer=substr($cur_referrer, strlen('http://'));}
			
			if (strlen($cur_referrer)>35)
			{
				$cur_referrer=substr($cur_referrer,0, 29).'…';
			}
			$wrapped_referrer=$cur_referrer;

			// Merge cells if we don't have additional params
			if ($row['campaign_param1'].$row['campaign_param2'].$row['campaign_param3'].$row['campaign_param4'].$row['campaign_param5']=='')
			{
				echo "<td colspan=6  title='"._e($row['referer'])."'>"._e($wrapped_referrer)."</td>";			
			}
			else
			{
				echo "<td title='"._e($row['referer'])."'>"._e($wrapped_referrer)."</td>";			
				echo "<td>"._e($row['campaign_param1'])."</td>
				<td>"._e($row['campaign_param2'])."</td>
				<td>"._e($row['campaign_param3'])."</td>
				<td>"._e($row['campaign_param4'])."</td>
				<td>"._e($row['campaign_param5'])."</td>";
			}

			echo "<td>";
				if ($row['conversion_price_main']>0)
				{
					echo "<span class='label label-success' style='font-weight:normal'>"._e($row['conversion_price_main'])."</span>";
				}
			echo "</td>";
		echo "</tr>";	
		echo "<tr style='display:none;'><td colspan=12 style='background:#f9f9f9; padding:0;'>
		<div style='padding:10px 15px 15px 15px; width:100%; line-height:26px;'>
		";

		$arr_locations=array();
		if ($row['country']!=''){$arr_locations[]=$row['country'];}
		if ($row['state']!=''){$arr_locations[]=$row['state'];}
		if ($row['city']!=''){$arr_locations[]=$row['city'];}				
		$str_location=implode (', ', $arr_locations);
		if ($str_location!='')
		{
			echo "<i class='icon-ip'></i> "._e($str_location);
		}
		echo "<span class='badge' style='float:right; font-weight:normal; margin-right:25px;'>"._e($row['user_ip'])."</span><br />";
		if ($row['referer']!='')
		{
			echo _e($row['referer'])."<br />";
		}
		echo _e("{$row['user_os']} {$row['user_os_version']}")."<br />
			"._e("{$row['user_platform']} {$row['user_platform_info']} {$row['user_platform_info_extra']}")."<br />
			"._e("{$row['user_browser']} {$row['user_browser_version']}");

		echo '<p>'._e($row['user_agent']).'</p>';
		echo '<p>'._e($row['subid']).'</p>';
		echo "</div>"; 		
		echo "<div style='width:100%; background:white; height:10px; margin:0; padding:0;'></div>";

		echo "</td></tr>";            
	}
	echo "</tbody></table>";            
?>