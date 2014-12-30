<?php
if (!$include_flag){exit();}
// Таблица отчёта

global $group_types;

global $table_n;

if(!isset($table_n)) {
	$table_n = 0;
} else {
	$table_n++;
}

echo "<div class='row'>";
echo "<div class='col-md-12'>";
echo "<table class='table table-condensed table-striped table-bordered dataTableT dataTableT".$table_n."' style='margin-bottom:15px !important;'>";
	
	// Заголовок 
	
	//dmp($var);
	
	echo "<thead>";
		echo "<tr>";
		
		if($var['report_params']['mode'] == 'popular') {
			echo "<th>Популярные</th><th>Значение</th>";
		} else {
			echo "<th>" . _e(col_name($var)) . "</th>";
		}
		
		foreach ($var['arr_dates'] as $cur_date) {
			$d = $var['timestep'] == 'monthly' ? $cur_date : date('d.m', strtotime($cur_date));
			echo "<th>"._e($d)."</th>";
		}
		echo "<th>Итого</th>";
		echo "</tr>";
	echo "</thead>";
	echo "<tbody>";
	
	$table_total_data  = array(); // суммирование всего
	$column_total_data = array(); // суммирование колонок
	$arr_sparkline     = array();
	$i = 0;
	
	//dmp($var['arr_report_data']);
	
	foreach ($var['arr_report_data'] as $source_name => $data) {

		$row_total_data = array(); // суммирование по строкам
		$i++;
		
		//dmp($data);
		
		// Первая колонка, название
		
		$source_name_full = param_val($source_name, $var['group_by'], $var['filter'][0]['source_name']);
		
		/*
		if ($source_name == '{empty}' or trim($source_name) == '') {
			$source_name_full = $group_types[$var['group_by']][1]; 
		} else {
			if($var['group_by'] == 'out_id') {
				//$source_name_full = $source_name;
				$source_name_full = current(get_out_description($source_name));
			} elseif($var['group_by'] == 'referer') {
				$source_name_full = str_replace('https://', '', $source_name);
				$source_name_full = str_replace('http://', '', $source_name_full);
				if(substr($source_name_full, -1) == '/')
					$source_name_full = substr($source_name_full, 0, strlen($source_name_full)-1);
				
				if(substr($key, -1) == '/')
					$key = substr($key, 0, strlen($key)-1);
			} else {
				$source_name_full = $source_name;
			}
		}
		*/
		
		
		
		if($var['report_params']['mode'] == 'popular') {
			
			$name = str_replace('Параметр перехода', 'ПП', $group_types[$source_name_full][0]);
			$name = str_replace('Параметр перехода', 'ПС', $name);
			
			$source_name_full = '<b><a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array('group_by' => _e($source_name))))).'">' . $name . '</a></b>';
			
			$data['popular'] = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($source_name => _e($data['popular']))))).'">' . _e(param_val($data['popular'], $source_name)) . '</a>';
			
			echo "<tr><td><table class=\"sparktable\"><tr><td>" . $source_name_full . "</td><td><span style='float:right; margin-left:10px;'><div id='sparkline_{$i}'></div></span></td></tr></table></td><td>".$data['popular']."</td>";
		} else {
			// Ограничиваем глубину фильтров
			if(empty($var['report_params']['filter'][0]) or count($var['report_params']['filter'][0]) < 5) {
				$source_name_full = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params']['group_by'] => _e($source_name))))).'">' . _e($source_name_full) . '</a>';
			} else {
				$source_name_full = _e($source_name_full);
			}
			
			echo "<tr><td><table class=\"sparktable\"><tr><td>" . $source_name_full . "</td><td><span style='float:right; margin-left:10px;'><div id='sparkline_{$i}'></div></span></td></tr></table></td>";
		}
		
		
		// Следующие колонки, данные
		
		foreach ($var['arr_dates'] as $cur_date) {
			$clicks_data    = $data[$cur_date]['click'];
			$leads_data     = $data[$cur_date]['lead'];
			$sales_data     = $data[$cur_date]['sale'];
			$saleleads_data = $data[$cur_date]['sale_lead'];
			
			$arr1 = array('click', 'lead', 'sale', 'sale_lead');
			$arr2 = array('cnt', 'cost', 'earnings');
			foreach($arr1 as $k1) {
				foreach($arr2 as $k2) {
					$table_total_data[$k1][$k2] += $data[$cur_date][$k1][$k2];
					$row_total_data[$k1][$k2] += $data[$cur_date][$k1][$k2];
					$column_total_data[$cur_date][$k1][$k2] += $data[$cur_date][$k1][$k2];
				}
			}
			
			$arr_sparkline[$i][] = $clicks_data['cnt'] + 0;
			
			echo '<td>' . get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data) . '</td>';
		}
		
		// Колонка Итого
		echo '<td>'.get_clicks_report_element($row_total_data['click'], $row_total_data['lead'], $row_total_data['sale'], $row_total_data['sale_lead']).'</td></tr>';
	}
	echo "</tbody>";
	
	// Итоговая строка
	
	if($var['report_params']['mode'] != 'popular') {
		echo "<tfoot><tr><th ".($var['report_params']['mode'] == 'popular' ? ' colspan="2"' : '') ."><strong><i style='display:none;'>&#148257;</i>Итого</strong></th>";
		foreach ($var['arr_dates'] as $cur_date) {
				echo '<th>' . get_clicks_report_element($column_total_data[$cur_date]['click'], $column_total_data[$cur_date]['lead'], $column_total_data[$cur_date]['sale'], $column_total_data[$cur_date]['sale_lead']) . '</th>';
		}	
		echo '<th>' . get_clicks_report_element($table_total_data['click'], $table_total_data['lead'], $table_total_data['sale'], $table_total_data['sale_lead']) . '</th>';
		echo "</tr></tfoot>";
	}
	
	echo "</table></div></div>";
	
	// Скрипты, отвечающие за сортировку и sparklines
?>
<script>
$(document).ready(function() {

    $('.dataTableT<?php echo $table_n; ?>').dataTable
    ({    	
    	"fnDrawCallback":function(){
	    if ( $('#writerHistory_paginate span span.paginate_button').size()) {
	      	if ($('#writerHistory_paginate')[0]) {
	      		$('#writerHistory_paginate')[0].style.display = "block";
		    } else {
		    	$('#writerHistory_paginate')[0].style.display = "none";
		   	}
	    }

		},
    	"aoColumns": [
            null,
            <?php if($var['report_params']['mode'] == 'popular') { ?>null,<?php } ?>
            <?php echo str_repeat('{ "asSorting": [ "desc", "asc"], "sType": "click-data" },', count($var['arr_dates']))?>
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
<script>
	$(document).ready(function() 
	{
		<?php
			foreach ($arr_sparkline as $i=>$val) {
		?>
		$("#sparkline_<?php echo $i?>").sparkline(
			[<?php echo implode (',', $arr_sparkline[$i]);?>], 
			{
		    	type: 'bar',
			    zeroAxis: false, 
			    barColor:'#AAA', 
			    disableTooltips:true, 
			    width:'40px'
			}
		);
		<?php
			}
		?>		
	});
</script>