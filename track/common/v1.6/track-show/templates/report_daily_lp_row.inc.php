<?php
$r = $var['r'];
global $row_total_data;

//dmp($var['group_by']);
// Первая колонка, название

if ($r['name'] == '{empty}' or trim($r['name']) == '') {
	$name = $group_types[$var['group_by']][1];
} else {
	if($var['group_by'] == 'out_id') {
		//$source_name_full = $source_name;
		$name = current(get_out_description($r['name']));
	} elseif($var['group_by'] == 'referer') {
		$name = str_replace('https://', '', $r['name']);
		$name = str_replace('http://', '', $name);
		if(substr($name, -1) == '/')
			$name = substr($name, 0, strlen($name)-1);
		
		if(substr($key, -1) == '/')
			$key = substr($key, 0, strlen($key)-1);
	} else {
		$name = $r['name'];
	}
}

// Ограничиваем глубину фильтров
if(empty($var['report_params']['filter'][0]) or count($var['report_params']['filter'][0]) < 5) {
	$name = '<a href="'.report_lnk($var['report_params'], array('filter_str' => array_merge($var['report_params']['filter_str'], array($var['report_params']['group_by'] => _e($r['name']))))).'">' . _e($name) . '</a>';
} else {
	$name = _e($name);
}

echo '<tr class="'.$var['class'].'"><td class="name"> ' . $name . "<span style='float:right; margin-left:10px;'><div id='sparkline_{$i}'></div></span></td>";

// Следующие колонки, данные

foreach ($var['arr_dates'] as $cur_date) {
	$clicks_data    = $r[$cur_date]['click'];
	$leads_data     = $r[$cur_date]['lead'];
	$sales_data     = $r[$cur_date]['sale'];
	$saleleads_data = $r[$cur_date]['sale_lead'];
	
	$arr1 = array('click', 'lead', 'sale', 'sale_lead');
	$arr2 = array('cnt', 'cost', 'earnings');
	foreach($arr1 as $k1) {
		foreach($arr2 as $k2) {
			$row_total_data[$k1][$k2] += $r[$cur_date][$k1][$k2];
			$column_total_data[$cur_date][$k1][$k2] += $r[$cur_date][$k1][$k2];
		}
	}
	
	$arr_sparkline[$i][] = $clicks_data['cnt'] + 0;
	
	echo '<td>' . get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data) . '</td>';
}

// Колонка Итого
echo '<td>'.get_clicks_report_element($row_total_data['click'], $row_total_data['lead'], $row_total_data['sale'], $row_total_data['sale_lead']).'</td></tr>';
?>		