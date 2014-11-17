<?php
	global $params;
	
	// Хлебные крошки
	if(!empty($params['filter_str'])) {
		
		$i = 1;
		echo '<div><ol class="breadcrumb">
			<li><a href="' . report_lnk($params, array('filter' => array())) . '">Все данные</a></li>';

		// Для ссылок преобразуем ID в название	
		foreach($params['filter_str'] as $k => $v) {
			$source_type = param_name($k, $params['filter'][0]['source_name']) . ': ';
			list($v, $type) = explode(':', $v);
			
			$v = current(explode('|', $v));
			
			$source_name = param_val($v, $k, $params['filter'][0]['source_name']);
			
			// Текущая ссылка
			if($i == count($params['filter_str'])) {
				if($params['group_by'] != '') {
					echo '<li class="active">' . $source_type . '<a href="' . report_lnk($params, array('group_by' => '', 'mode' => 'popular', 'subgroup_by' => '' )) . '">' . _e($source_name) . '</a></li>
						<li class="active">' . col_name($params) . '</a></li>'; //$group_types[$params['group_by']][0]
				} else {
					echo '<li class="active">' . $source_type . _e($source_name) . '</li>';
					echo '<li class="active">Популярные</li>';
				}
			} else {
				echo '<li class="active">' . $source_type . '<a href="' . report_lnk($params, array('filter_str' => array_slice($params['filter_str'], 0, $i))) . '">' . _e($source_name) . '</a></li>';
			}
			$i++;
		}
		echo '</ol></div>';
	}
?>	