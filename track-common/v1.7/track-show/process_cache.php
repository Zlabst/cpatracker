<?php
	set_time_limit(0);
	
	$settings_file=_TRACK_SETTINGS_PATH.'/settings.php'; 
	
	$str=file_get_contents($settings_file);
	$str=str_replace('<?php exit(); ?>', '', $str);
	$arr_settings=unserialize($str);
	
	if (isset($_GET['debug'])) {
		ini_set('display_errors', 1);
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
	}
	
	$_DB_LOGIN=$arr_settings['login'];
	$_DB_PASSWORD=$arr_settings['password'];
	$_DB_NAME=$arr_settings['dbname'];
	$_DB_HOST=$arr_settings['dbserver'];
 	
 	// Connect to DB
	
	mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " .mysql_error());
	mysql_select_db($_DB_NAME);
	mysql_query('SET NAMES utf8');
	
	require _TRACK_SHOW_COMMON_PATH . "/functions_general.php";
	require _TRACK_SHOW_COMMON_PATH . "/functions_report.php";
	
	$t1 = microtime_float();
	
	function time_edge($t, $type = 'hour', $edge = 'begin') {
		switch($type) {
			case 'hour':
				$out = mktime(date('H', $t), 0, 0, date('m', $t), date('d', $t), date('Y', $t));
				if($edge == 'end') $out += (3600 - 1);
				break;
		}
		return $out;
	}
	
	function get_cache_timers() {
		$out = array();
		$q = "select * from `tbl_clicks_cache_time` where 1";
		$rs = db_query($q);
		$r = mysql_fetch_assoc($rs);
		return $r;
	}
	
	function set_cache_timer($type, $t) {
		$q = "update `tbl_clicks_cache_time` set `" . $type . "` = '" . $t ."'";
		$rs = db_query($q);
	}
	
	
	$cache_timers = get_cache_timers();
	
	/*
	* За какой период нужно собрать данные для кэша?
	*/
	function get_last_cache_time($type = 'hour') {
		global $cache_timers;
		$sec = array(
			'hour' => 3600 - 1,
		);
		
		//dmp($cache_timers);
		
		if($cache_timers[$type] != '0000-00-00 00:00:00') {
			$time_from = $cache_timers[$type];
		}
		
		/*
		$q="select max(`time`) as `last_time`
			from `tbl_clicks_cache_" . $type . "`
			where 1";
		if($rs = db_query($q) and mysql_num_rows($rs) > 0){
			$r = mysql_fetch_assoc($rs);
			$time_from = $r['last_time'];
		}*/
		
		// Кэша нет вообще никакого, а данные хоть есть?
		if(empty($time_from)) {
			/*
			$spot_ids = clicks_spot_get();
			$spot_id = $spot_ids[0];
			*/
			$spot_id = 1;
			
			$q="select min(`date_add`) as `first_time`
				from `tbl_clicks_s" . $spot_id . "`
				where 1";
			if($rs = db_query($q) and mysql_num_rows($rs) > 0){
				$r = mysql_fetch_assoc($rs);
				$time_from = strtotime($r['first_time']);
				$now = time();
				
				if(($time_from + $sec[$type]) > $now) {
					// нельзя складывать в кэш, время ещё не подошло
					return false;
				}
			} else {
				
				
				
				return false;
			}
		} else {
			// Помним, что кэш накрывает последний час/день/месяц
			$time_from = strtotime($time_from) + $sec[$type] + 1;
		}
		
		$time_to = time_edge($time_from, $type, 'end');
		
		return array($time_from, $time_to);
	}
	
	
	
	list($time_from, $time_to) = get_last_cache_time();
	$hour_key = date('H', $time_from);
	$cache_time = date('Y-m-d H:i:s', time_edge($time_from, 'hour', 'begin'));
	
	$main_types = array('out_id', 'source_name');
	
	foreach($main_types as $main_type) {
		// Кэш дневной ленты переходов
		$params = array(
			'type'     => 'basic',
			'part'     => 'hour',
			'filter'   => array(),
			'group_by' => $main_type,
			'subgroup_by' => $main_type,
			'conv'     => 'all',
			'mode'     => '',
			'col'      => 'sale_lead',
			'from'     => date('Y-m-d H:i:s', $time_from),
			'to'       => date('Y-m-d H:i:s', $time_to),
			'cache'    => 0
		);
		
		//dmp($params);
		
		$arr_report_data = get_clicks_report_grouped2($params);
		
		foreach($arr_report_data['data'] as $k => $v) {
			$r = $v[$hour_key];
			$ins = array(
				'type'   => $main_type,
				'id'     => $r['id'],
				'time'   => $cache_time,
				'name'   => $r['name'],
				'price'  => $r['price'],
				'unique' => $r['unique'],
				'income' => $r['income'],
				'direct' => $r['direct'],
				'sale'   => $r['sale'],
				'lead'   => $r['lead'],
				'act'    => $r['act'],
				'out'    => $r['out'],
				'cnt'    => $r['cnt'],
				'sale_lead' => $r['sale_lead'],
			);
			
			$q=insertsql($ins, 'tbl_clicks_cache_hour');
			//echo $q . '<br />';
			db_query($q);
		}
	}
	set_cache_timer('hour', $cache_time);
	
	$t2 = microtime_float();
	
	echo 'Кэш ленты: ' . $cache_time . ' (' . round($t2 - $t1, 2) .' c.)<br />';
?>