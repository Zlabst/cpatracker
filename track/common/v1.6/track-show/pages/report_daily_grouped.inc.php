<?php if (!$include_flag){exit();} ?>
<?php
    $from=$_REQUEST['from'];
	$to=$_REQUEST['to'];

	// Set default range values for this report
	if ($from=='')
	{
		if ($to=='')
		{
			$from=get_current_day('-6 days');
			$to=get_current_day();
		}
		else
		{
			$from=date ('Y-m-d', strtotime('-6 days', strtotime($to)));
		}
	}
	else
	{
		if ($to=='')
		{
			$to=date ('Y-m-d', strtotime('+6 days', strtotime($from)));
		}
		else
		{
			// Will use existing values
		}
	}

	$arr_dates=getDatesBetween($from, $to);

	$main_type=$_REQUEST['subtype'];
	$group_by=$_REQUEST['group_by'];
	$limited_to=$_REQUEST['limited_to'];
	$source_name = $_REQUEST['limited_to'];
	$report_type='daily';
	$arr_report_data=get_clicks_report_grouped($main_type, $group_by, $limited_to, $report_type,$from,$to);

	switch ($main_type)
	{
		case 'out_id': 
			$report_name="Отчет по ссылке";
			$report_main_column_name="Ссылка";
			$empty_name="Без ссылки";
		break;	
	
		case 'source_name': 
			$report_name="Отчет по источнику";
			$report_main_column_name="Источник";		
			$empty_name="Без источника";		
		break;
	}

	
		switch ($main_type)
			{
				case 'out_id': 
					$source_name=current(get_out_description($source_name));
				break;	
			
				default: 
					
				break;
			}
	
		if ($source_name=='{empty}'){$source_name='Не определен';}
                                
        $fromF=date ('d.m.Y', strtotime($from));
        $toF=date ('d.m.Y', strtotime($to));
        $value_date_range = "$fromF - $toF";
        
        echo '<form method="post"  name="datachangeform" id="range_form">
                <div id="per_day_range" class="pull-right" style="margin-top:0px; margin-bottom:10px;">
                    <span class="glyphicon glyphicon-calendar"></span>
                    <span id="cur_day_range">'.date('d.m.Y', strtotime($from)).' - '. date('d.m.Y', strtotime($to)).'</span> <b class="caret"></b>
                    <input type="hidden" name="from" id="sStart" value="">
                    <input type="hidden" name="to" id="sEnd" value="">
                </div>
                
                <div><h5>'._e($report_name)." "._e($source_name).'</h5></div>
              </form>';
        
        foreach ($arr_report_data as $source_name=>$data)
	{
		?>
<div class='row report_grouped_menu'>
<div class='col-md-12'>

	<div class="btn-group">
		<?php if ($group_by=='campaign_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>" >Кампания</a>

		<?php if ($group_by=='ads_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=ads_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Объявление</a>

		<?php if ($group_by=='referer'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=referer&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Площадка</a>

		<?php if ($group_by=='out_id'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=daily_grouped&subtype=<?php echo $main_type;?>&group_by=out_id&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Ссылка</a>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Гео
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=country&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Страна</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=city&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Город</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=region&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Регион</a></li>			
			<li class="divider"></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=isp&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Провайдер</a></li>			
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Устройство
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=user_os&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">ОС</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=user_platform&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Платформа</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=user_browser&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Браузер</a></li>
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Другие параметры
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_param1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #1</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_param2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #2</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_param3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #3</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_param4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #4</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=campaign_param5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #5</a></li>
			<li class="divider"></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #1</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #2</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #3</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #4</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #5</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value6&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #6</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value7&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #7</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value8&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #8</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value9&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #9</a></li>
			<li><a href="?act=reports&type=daily_grouped&subtype=<?php echo _e($main_type);?>&group_by=click_param_value10&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #10</a></li>
			</ul>
		</div>
	</div>

</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->

<script>
$(document).ready(function() {
    $('.dataTableT').dataTable
    ({    	
    	"aoColumns": [
            null,
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc"], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
            { "asSorting": [ "desc", "asc" ], "sType": "click-data" },
			{ "asSorting": [ "desc", "asc" ], "sType": "click-data" },            
        ],
		"bPaginate": <?php echo (count($arr_report_data) > 10) ? 'true' : 'false'; ?>,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
        "bAutoWidth": false
	})
} );
</script>

<div class="row">&nbsp;</div>
<?php
		echo "<div class='row'>";
		echo "<div class='col-md-12'>";
		echo "<table class='table table-condensed table-striped table-bordered dataTableT'>";	
		echo "<thead>";
			echo "<tr>";
				echo "<th>"._e($group_types[$group_by][0])."</th>";		
				foreach ($arr_dates as $cur_date)
				{
					$d=date('d.m', strtotime($cur_date));
					echo "<th>"._e($d)."</th>";
				}
			echo "<th>Итого</th>";				
			echo "</tr>";		
		echo "</thead>";
		echo "<tbody>";
		$table_total_data=array();
		$column_total_data=array();		
		foreach ($data as $key=>$inner_data)
		{
			if ($key=='{empty}'){$key='Не определен';}		
			echo "<tr>";
			switch ($group_by){
				case 'out_id': 
					$key=current(get_out_description($key));
				break;
				
				default: 
				break;
			}
				echo "<td>"._e($key)."</td>";
				$row_total_data=array();

				foreach ($arr_dates as $cur_date)
				{
					$clicks_data=$inner_data[$cur_date]['click'];
					$leads_data=$inner_data[$cur_date]['lead'];
					$sales_data=$inner_data[$cur_date]['sale'];
					$saleleads_data=$inner_data[$cur_date]['sale_lead'];

					$row_total_data['clicks']['cnt']+=$clicks_data['cnt'];
					$row_total_data['clicks']['cost']+=$clicks_data['cost'];
					$row_total_data['clicks']['earnings']+=$clicks_data['earnings'];
					$row_total_data['leads']['cnt']+=$leads_data['cnt'];
					$row_total_data['leads']['cost']+=$leads_data['cost'];
					$row_total_data['leads']['earnings']+=$leads_data['earnings'];
					$row_total_data['sales']['cnt']+=$sales_data['cnt'];
					$row_total_data['sales']['cost']+=$sales_data['cost'];
					$row_total_data['sales']['earnings']+=$sales_data['earnings'];
					$row_total_data['saleleads']['cnt']+=$saleleads_data['cnt'];
					$row_total_data['saleleads']['cost']+=$saleleads_data['cost'];
					$row_total_data['saleleads']['earnings']+=$saleleads_data['earnings'];		

					$column_total_data[$cur_date]['clicks']['cnt']+=$clicks_data['cnt'];
					$column_total_data[$cur_date]['clicks']['cost']+=$clicks_data['cost'];
					$column_total_data[$cur_date]['clicks']['earnings']+=$clicks_data['earnings'];
					$column_total_data[$cur_date]['leads']['cnt']+=$leads_data['cnt'];
					$column_total_data[$cur_date]['leads']['cost']+=$leads_data['cost'];
					$column_total_data[$cur_date]['leads']['earnings']+=$leads_data['earnings'];
					$column_total_data[$cur_date]['sales']['cnt']+=$sales_data['cnt'];
					$column_total_data[$cur_date]['sales']['cost']+=$sales_data['cost'];
					$column_total_data[$cur_date]['sales']['earnings']+=$sales_data['earnings'];
					$column_total_data[$cur_date]['saleleads']['cnt']+=$saleleads_data['cnt'];
					$column_total_data[$cur_date]['saleleads']['cost']+=$saleleads_data['cost'];
					$column_total_data[$cur_date]['saleleads']['earnings']+=$saleleads_data['earnings'];
					
					$table_total_data['clicks']['cnt']+=$clicks_data['cnt'];
					$table_total_data['clicks']['cost']+=$clicks_data['cost'];
					$table_total_data['clicks']['earnings']+=$clicks_data['earnings'];

					$table_total_data['leads']['cnt']+=$leads_data['cnt'];
					$table_total_data['leads']['cost']+=$leads_data['cost'];
					$table_total_data['leads']['earnings']+=$leads_data['earnings'];

					$table_total_data['sales']['cnt']+=$sales_data['cnt'];
					$table_total_data['sales']['cost']+=$sales_data['cost'];
					$table_total_data['sales']['earnings']+=$sales_data['earnings'];

					$table_total_data['saleleads']['cnt']+=$saleleads_data['cnt'];
					$table_total_data['saleleads']['cost']+=$saleleads_data['cost'];
					$table_total_data['saleleads']['earnings']+=$saleleads_data['earnings'];					
					echo '<td>'.get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data);
					if ($inner_data[$cur_date]['click']['is_parent_cnt']>0 && $inner_data[$cur_date]['click']['cnt']>0)
					{
						$t=round(($inner_data[$cur_date]['click']['is_parent_cnt']/$inner_data[$cur_date]['click']['cnt'])*100);
						echo "<span class='label label-info' style='padding:1px 3px; position:relative; top:-1px; margin-left:3px; font-weight:normal;'>";
							echo $t.'%';
						echo '</span>';
					}
					echo '</td>';						
				}
			echo '<td>';
				echo get_clicks_report_element($row_total_data['clicks'], $row_total_data['leads'], $row_total_data['sales'], $row_total_data['saleleads']);
			echo '</td>';	
			echo "</tr>";
		}		
		echo "</tbody>";
		echo "</table>";
		echo "</div>";
		echo "</div>";		
	}
?>