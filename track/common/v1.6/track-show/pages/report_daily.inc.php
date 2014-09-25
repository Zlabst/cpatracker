<? if (!$include_flag){exit();} ?>
<?php

	$main_type=$subtype;
	$group_by=$subtype;
	$limited_to='';
	$report_type='daily';
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

	$arr_report_data=get_clicks_report_grouped($main_type, $group_by, $limited_to, $report_type, $from, $to);
	
	$arr_dates = strip_empty_dates($arr_dates, $arr_report_data);
	
echo "<div class='row'>";
echo "<div class='col-md-12'>";
echo "<table class='table table-condensed table-striped table-bordered dataTableT' style='margin-bottom:15px !important;'>";
	echo "<thead>";
		echo "<tr>";
		echo "<th>"._e($report_main_column_name)."</th>";		
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
	$arr_sparkline=array();
	$i=0;
	foreach ($arr_report_data as $source_name=>$data)
	{
		$i++;
		echo "<tr>";
		if ($source_name=='{empty}'){$source_name_full="{$empty_name}";}else
		{
			switch ($subtype)
			{
				case 'out_id':
					$source_name_full=current(get_out_description($source_name));
				break;
				
				default:
					$source_name_full=$source_name;
				break;
			}		
		}
		echo "<td nowrap>";
		if ($source_name!='{empty}')
		{
			echo "<a href='?act=reports&type=daily_grouped&subtype={$subtype}&group_by=campaign_name&limited_to="._e($source_name)."'>"._e($source_name_full)."</a>";	
		}
		else
		{
			echo "{$source_name_full}";
		}
		echo "<span style='float:right; margin-left:10px;'><div id='sparkline_{$i}'></div></span>";
		echo "</td>";
		
		$row_total_data=array();
		
		foreach ($arr_dates as $cur_date)
		{
			$clicks_data=$data[$cur_date]['click'];
			$arr_sparkline[$i][]=$clicks_data['cnt']+0;
			
			$leads_data=$data[$cur_date]['lead'];
			$sales_data=$data[$cur_date]['sale'];
			$saleleads_data=$data[$cur_date]['sale_lead'];		

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

			echo '<td>'.get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data).'</td>';
		}
		echo '<td>'.get_clicks_report_element($row_total_data['clicks'], $row_total_data['leads'], $row_total_data['sales'], $row_total_data['saleleads']).'</td>';
		echo "</tr>";
	}
		echo "</tbody><tfoot><tr>";
		echo "<th><strong><i style='display:none;'>&#148257;</i>Итого</strong></th>";
		foreach ($arr_dates as $cur_date)
		{
			echo '<th>'.get_clicks_report_element($column_total_data[$cur_date]['clicks'], $column_total_data[$cur_date]['leads'], $column_total_data[$cur_date]['sales'], $column_total_data[$cur_date]['saleleads']).'</th>';
		}	
		echo '<th>'.get_clicks_report_element($table_total_data['clicks'], $table_total_data['leads'], $table_total_data['sales'], $table_total_data['saleleads']).'</th>';
		echo "</tr></tfoot>";
	echo "";
echo "</table>";
echo "</div>";
echo "</div>";
?>
<script>
$(document).ready(function() {

    $('.dataTableT').dataTable
    ({    	
    	"fnDrawCallback":function(){
	    if ( $('#writerHistory_paginate span span.paginate_button').size()) {
	      	if ($('#writerHistory_paginate')[0])
	      	{
	      		$('#writerHistory_paginate')[0].style.display = "block";
		    }
		    else 
		    {
		    	$('#writerHistory_paginate')[0].style.display = "none";
		   	}
	    }

		},
    	"aoColumns": [
            null,
            <?=str_repeat('{ "asSorting": [ "desc", "asc"], "sType": "click-data" },', count($arr_dates))?>
			{ "asSorting": [ "desc", "asc" ], "sType": "click-data" },            
        ],
		"bPaginate": false,
	    "bLengthChange": false,
	    "bFilter": false,
	    "bSort": true,
	    "bInfo": false,
    "bAutoWidth": false
	})
} );
</script>
<script>
	$(document).ready(function() 
	{
		<?
			foreach ($arr_sparkline as $i=>$val)
			{
		?>
		$("#sparkline_<?=$i?>").sparkline(
			[<?=implode (',', $arr_sparkline[$i]);?>], 
			{
		    	type: 'bar',
			    zeroAxis: false, 
			    barColor:'#AAA', 
			    disableTooltips:true, 
			    width:'40px'
			}
		);
		<?
			}
		?>		
	});
</script>