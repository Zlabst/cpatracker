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
	mysql_query("SET @@GLOBAL.sql_mode= ''");
	mysql_query("SET @@SESSION.sql_mode= ''");	
	
	require _TRACK_SHOW_COMMON_PATH . "/functions_general.php";
	require _TRACK_SHOW_COMMON_PATH . "/functions_report.php";
	
	$t1 = microtime_float();
	
	$cache_timers = get_cache_timers();
	
	function make_cache($type, $from, $to, $cache_time, $rewrite = false) {
		
		$hour_key = date('H', $from);
		
		// Кэш дневной ленты переходов
		$params = array(
			'type'     => 'basic',
			'part'     => 'hour',
			'filter'   => array(),
			'group_by' => $type,
			'subgroup_by' => $type,
			'conv'     => 'all',
			'mode'     => '',
			'col'      => 'sale_lead',
			'from'     => date('Y-m-d H:i:s', $from),
			'to'       => date('Y-m-d H:i:s', $to),
			'cache'    => 2
		);
		
		$arr_report_data = get_clicks_report_grouped2($params);
		
		/*
		dmp($params);
		dmp($arr_report_data);
		die();*/
		
		$str = join('',$arr_report_data['click_params']) . join('',$arr_report_data['campaign_params']);
		echo $str . '<br />';
		
		if(empty($arr_report_data['data'])) return false;
		
		foreach($arr_report_data['data'] as $k => $v) {
			$r = $v[$hour_key];
			$ins = array(
				'type'    => $type,
				'id'      => $r['id'],
				'time'    => $cache_time,
				'name'    => $r['name'],
				'price'   => $r['price'],
				'unique'  => $r['unique'],
				'income'  => $r['income'],
				'direct'  => $r['direct'],
				'sale'    => $r['sale'],
				'lead'    => $r['lead'],
				'act'     => $r['act'],
				'out'     => $r['out'],
				'cnt'     => $r['cnt'],
				'sale_lead' => $r['sale_lead'],
				'rebuild' => 0,
				'params'  => bindec($str),
			);
			
			$q = insertsql($ins, 'tbl_clicks_cache_hour', true);
			echo $q . '<br />';
			db_query($q);
		}
		
		if($rewrite) {
			$q = "update `tbl_clicks_cache_hour` set `rebuild` = '0' where `time` = '" . $cache_time . "'";
			db_query($q);
		}
		return true;
	}
	
	/*
	* За какой период нужно собрать данные для кэша?
	*/
	function get_last_cache_time($type = 'hour') {
		global $cache_timers;
		$sec = array(
			'hour' => 3600 - 1,
			'day'  => 86400 - 1,
		);
		
		if($cache_timers[$type] != '0000-00-00 00:00:00') {
			$time_from = $cache_timers[$type];
		}
		
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
	
	//----------------------------------
	
	$main_types = array('out_id', 'source_name', 'campaign_name', 'ads_name', 'referer', 'country', 'state', 'city', 'user_ip', 'isp', 'user_os', 'user_platform', 'user_browser');
	for($i = 1; $i <= 5; $i++) {
		$main_types[] = 'campaign_param' . $i;
	}
	for($i = 1; $i <= 15; $i++) {
		$main_types[] = 'click_param_value' . $i;
	}
	
	// Не надо ли нам обновить какую-то часть кэша?
	$q = "select `time`
		from `tbl_clicks_cache_hour`
		where `rebuild` = '1'
		group by `time`
		order by `time` asc
		limit 1";
	if($rs = db_query($q) and mysql_num_rows($rs) > 0){
		$r = mysql_fetch_assoc($rs);
		
		$time_from = strtotime($r['time']);
		$time_to = $time_from + 3600 - 1;
		
		$cache_time = date('Y-m-d H:i:s', $time_from);
		
		echo 'Выполняем пересчёт кэша за ' . $r['time'] . '<br />';
		
		// УДАЛИТЬ СТАРЫЙ КЭШ
		
		foreach($main_types as $main_type) {
			echo 'Тип <b>' . $main_type . '</b>, кеширование<br />';
			make_cache($main_type, $time_from, $time_to, $cache_time, true);
		}
		exit;
	} else {
		list($time_from, $time_to) = get_last_cache_time();
		$cache_time = date('Y-m-d H:i:s', time_edge($time_from, 'hour', 'begin'));
		
		foreach($main_types as $main_type) {
			echo 'Тип <b>' . $main_type . '</b>, кеширование<br />';
			$result = make_cache($main_type, $time_from, $time_to, $cache_time);
			if(!$result) break;
		}
		set_cache_timer('hour', $cache_time);
	}
	
	
	
	
	$t2 = microtime_float();
	
	echo 'Кэш ленты: ' . $cache_time . ' (' . round($t2 - $t1, 2) .' c.)<br />';
	
	if($_GET['reload']) {
		if($time_to < time()) {
			echo '<script>window.location.reload();</script>';
		} else {
			echo 'Кэш в актуальном состоянии';
		}
	}
?>