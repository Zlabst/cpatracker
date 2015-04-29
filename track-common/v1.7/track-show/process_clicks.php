<?php
	set_time_limit(0);
	
	error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	
	$process_clicks_marker=_CACHE_PATH.'/.crontab_clicks';
	if(file_exists($process_clicks_marker)) {
		unlink ($process_clicks_marker);
	}

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
	
	if(_CLICKS_SPOT_SIZE > 0) {
		$current_spot_id = current(clicks_spot_get());
	}
	
	// Collector
	
	function api_get_files($url, $n = 0) {
		foreach(array('clicks', 'postback') as $type) {
			$url_params = $url['path'] . '/api.php?act=data_get&type=' . $type. '&key=' . $url['key'];
			$files = json_decode(file_get_contents($url_params), true);
			
			foreach($files['data'] as $f => $data) {
				$path = _CACHE_PATH . '/' . $type . '/' . $f . '_' . $n;
				
				if(!file_exists($path)) {
					$fp = fopen($path, 'w');
					if($fp && fwrite($fp, $data) && fclose($fp)) {
						$url_params = $url['path'] . '/api.php?act=data_get_confirm&type=' . $type. '&key=' . $url['key'] . '&file=' . $f;;
						file_get_contents($url_params);
					}
				}
			}
		}
	}

	foreach($tracklist as $n => $track) {
		if($n == 0) continue; // не трогаем первый трекер, это мастер
		
		// Remote tracker
		if(substr($track['path'], 0, 4) == 'http') {
			
			$files = api_get_files($track, $n);
			
		// Local tracker
		} else {
			foreach(array('clicks', 'postback') as $type) {
				$files = dir_files($track['path'] . '/cache/' . $type, $type);
				foreach($files as $f) {
					rename($track['path'] . '/cache/' . $type . '/' . $f, _CACHE_PATH . '/' . $type . '/' . $f . '_' . $n);
				}
			}
		}
	}
	
	// Process clicks
	
	$arr_files=array();
	$process_at_once=60;
	$iCnt=0;
	
	if ($handle = opendir(_CACHE_PATH . '/clicks/')) {
	    while (false !== ($entry = readdir($handle))) {
	        if ($entry != "." && $entry != ".." && $entry != ".empty") {
		        if (
				        // Check if file starts with dot,  
			        	(strpos($entry, '.')===0) && 
			        	// is not processing now
			        	(strpos($entry, '+')===false) && 
			        	// and was not processed before
			        	(strpos($entry, '*')===false)
		        	)
		        {
		        	// Also check that there were at least 2 minutes from creation date
		        	if ($entry!='.clicks_'.date('Y-m-d-H-i', strtotime('-1 minutes')) &&
		        		$entry!='.clicks_'.date('Y-m-d-H-i')
		        		) {
			        	$arr_files[]=$entry;
			        	/*
		        		if (($iCnt++) > $process_at_once)
		        		{
		        			break;
		        		}*/			        	
		        	}
		        }
	        }
	    }
	    closedir($handle);
	}
	
	if (count ($arr_files)==0){exit();}
	
	asort($arr_files);
	
	$arr_files = array_slice($arr_files, 0, $process_at_once);
	//dmp($arr_files);
	
	//die();

    if (extension_loaded('xmlreader')) 
    {
        // Init WURFL library for mobile device detection
        $wurflDir = _TRACK_LIB_PATH . '/wurfl/WURFL';
        $resourcesDir = _TRACK_LIB_PATH . '/wurfl/resources';	
        require_once $wurflDir.'/Application.php';
        $persistenceDir = _CACHE_COMMON_PATH.'/wurfl-persistence';
        //$persistenceDir = dirname(__FILE__).'/cache/wurfl-persistence';
        $cacheDir = _CACHE_COMMON_PATH.'/wurfl-cache';
        $wurflConfig = new WURFL_Configuration_InMemoryConfig();
        $wurflConfig->wurflFile(_TRACK_STATIC_PATH.'/wurfl/wurfl_1.5.3.xml');
        $wurflConfig->matchMode('accuracy');
        $wurflConfig->allowReload(true);
        $wurflConfig->persistence('file', array('dir' => $persistenceDir));
        $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));
        $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
        $wurflManager = $wurflManagerFactory->create();
    }

	foreach ($arr_files as $cur_file) {
		$name_parts = explode('_', $cur_file);
		if(count($name_parts) > 2 && !empty($tracklist[$name_parts[2]]['timeshift'])) {
			$slave_timeshift = $tracklist[$name_parts[2]]['timeshift'];
		} else {
			$slave_timeshift = 0;
		}
		
		$file_name=_CACHE_PATH."/clicks/{$cur_file}+";
		rename (_CACHE_PATH."/clicks/$cur_file", $file_name);
		$handle = fopen($file_name, "r");
	    while (($buffer = fgets($handle, 4096)) !== false) 
	    {
		    $arr_click=array();
	        $arr_click=explode ("\t", rtrim($buffer, "\n"));
	        save_click_info ($arr_click, $slave_timeshift);
	    }
	    fclose($handle);
		rename ($file_name, _CACHE_PATH."/clicks_processed/{$cur_file}");
	}

	exit();

	function get_hour_by_date($str)
	{
		$a = end(explode (' ', $str));
		return current(explode (':', $a));	
	}

/*
	function _str($str)
	{
		return mysql_real_escape_string ($str);
	}	
*/	
	function get_geodata($ip)
	{
		require_once (_TRACK_LIB_PATH."/maxmind/geoip.inc.php");
		require_once (_TRACK_LIB_PATH."/maxmind/geoipcity.inc.php");
		require_once (_TRACK_LIB_PATH."/maxmind/geoipregionvars.php");
		$gi = geoip_open(_TRACK_STATIC_PATH."/maxmind/MaxmindCity.dat", GEOIP_STANDARD);
		$record = geoip_record_by_addr($gi, $ip);
		geoip_close($gi);

		$giisp = geoip_open(_TRACK_STATIC_PATH."/maxmind-isp/GeoIPISP.dat",GEOIP_STANDARD);
		$isp = geoip_org_by_addr($giisp, $ip);

		$cur_country=$record->country_code;

		// Resolve GeoIP extension conflict
		if (function_exists('geoip_country_code_by_name') && ($cur_country==''))
		{
			$cur_country=geoip_country_code_by_name($ip);
		}

		return array ('country'=>$cur_country, 'state'=>$GEOIP_REGION_NAME[$record->country_code][$record->region], 'city'=>$record->city, 'region'=>$record->region, 'isp'=>$isp);
	}
	
	function save_click_info ($arr_click_info, $timeshift = 0)
	{
		global $current_spot_id;
		// User-agent parser
		require_once (_TRACK_LIB_PATH."/ua-parser/uaparser.php");
		$parser = new UAParser;

		// WURFL mobile database
		global $wurflManager;
		if($timeshift != 0) {
			$click_date = date("Y-m-d H:i:s", strtotime($arr_click_info[0]) + $timeshift);
		} else {
			$click_date = $arr_click_info[0];
		}
		$click_day=current(explode(' ', $click_date));
		$click_hour=get_hour_by_date ($click_date);
		
		$click_ip=$arr_click_info[1];

		// Get geo from IP
		$geo_data=get_geodata($click_ip);
		$click_country=$geo_data['country'];
		$click_state=$geo_data['state'];
		$click_city=$geo_data['city'];
		$click_region=$geo_data['region'];
		$click_isp=$geo_data['isp'];

		// Get info from user agent
		$click_user_agent=$arr_click_info[2];		
		
		// Set empty initial values
		$is_mobile_device=false; $is_tablet=false; $is_phone=false; $brand_name=''; $model_name=''; $model_extra_info=''; 
		$device_os=''; $device_os_version=''; $device_browser=''; $device_browser_version='';

        if (extension_loaded('xmlreader')) 
        {
            $requestingDevice = $wurflManager->getDeviceForUserAgent($click_user_agent);

            $is_wireless = ($requestingDevice->getCapability('is_wireless_device') == 'true');
            $is_tablet = ($requestingDevice->getCapability('is_tablet') == 'true');
            $is_mobile_device = ($is_wireless || $is_tablet);

            // Use WURFL database info for mobile devices only	
            if ($is_mobile_device)
            {	
                    $is_phone = ($requestingDevice->getCapability('can_assign_phone_number') == 'true');

                    $brand_name=$requestingDevice->getCapability('brand_name');
                    $model_name=$requestingDevice->getCapability('model_name');
                    $model_extra_info=$requestingDevice->getCapability('model_extra_info');

                    $device_os = $requestingDevice->getCapability('device_os');
                    $device_os_version = $requestingDevice->getCapability('device_os_version');				

                    $device_browser = $requestingDevice->getCapability('mobile_browser');
                    $device_browser_version = $requestingDevice->getCapability('mobile_browser_version');
            }
            else
            {
                    // Use UAParser to get click info
                    $result = $parser->parse($click_user_agent);

                    $device_browser=$result->ua->family;
                    $device_browser_version=$result->ua->toVersionString;

                    $device_os=$result->os->family;
                    $device_os_version=$result->os->toVersionString;
            }
        }
		
		$click_referer=$arr_click_info[3];
		$click_link_name=$arr_click_info[4];
		$click_link_source=$arr_click_info[5];
		
		// Allow to use - as campaign/ads delimiter
		$link_ads_name = $arr_click_info[6];
		if (strpos($link_ads_name, '-')!==false) {
			$click_link_campaign=current(explode('-', $link_ads_name));
			$click_link_ads=substr($link_ads_name, strpos($link_ads_name, '-')+1);			
		} else {
			$click_link_campaign=$link_ads_name;
			$click_link_ads='';
		}

		$click_subid=$arr_click_info[7];
		$click_subaccount=$arr_click_info[8];
		$click_rule_id=$arr_click_info[9];
		$click_out_id=$arr_click_info[10];
		$click_is_unique=$arr_click_info[11];
		$click_param1=$arr_click_info[12];
		$click_param2=$arr_click_info[13];
		$click_param3=$arr_click_info[14];
		$click_param4=$arr_click_info[15];
		$click_param5=$arr_click_info[16];
		
		// Parse get string
		if(!empty($arr_click_info[17])) {
			parse_str ($arr_click_info[17], $click_get_params);
		} else {
			$click_get_params = array();
		}
		
		$sql_click_params_arr = array();
		
		// Save this source params
		
		global $source_config;
		/*
		to_log('src', $click_link_source);
		to_log('src', $source_config);
		to_log('src', $click_get_params);
		*/
		$i = 1;
		
		// Source config exists
		if(array_key_exists($click_link_source, $source_config) 
			and array_key_exists('params', $source_config[$click_link_source])) {
			
			// Выбираем именованные параметры из того, что пришло
			foreach($source_config[$click_link_source]['params'] as $param_name => $param_info) {
				
				if(empty($param_info['url'])) continue; // "виртуальный" параметр, он определяется не ссылкой, а через другие параметры, см ниже
					
				if(array_key_exists($param_name, $click_get_params)) {
					$param_value = $click_get_params[$param_name];
					if($param_info['url'] == $param_value) $param_value = ''; // Пришло site_id={site_id}, то есть значение пустое
						
					$sql_click_params_arr["click_param_name" . $i] = $param_name;
					$sql_click_params_arr["click_param_value" . $i] = $param_value;
					
					
					// Adwords передает в одном параметре и то что это спецразмещение и то, что это реклама в сайдбаре и сразу же позицию объявления. Поэтому мы разбиваем это значение на два параметра, Размещение (если t - Спецразмещение, если s - Реклама справа, если o или что-то другое - Не определено) и Позиция (где выводим значения как есть, то есть 1t1, 1s2 и т.д.) 
					// Пример "виртуального параметра"
					
					if($click_link_source == 'adwords' and $param_name == 'adposition') {
						$i++;
						$position_type = 0;
						if(strstr($param_value, 's') !== false) {
							$position_type = 's';
						}
						
						if(strstr($param_value, 't') !== false) {
							$position_type = 't';
						}

						$sql_click_params_arr["click_param_name" . $i] = 'position_type';
						$sql_click_params_arr["click_param_value" . $i] = $position_type;
					}
					
					// Поисковые слова Яндекса
					if($click_link_source == 'yadirect' and $param_name == 'ad_id') {
						$i++;
						// 17 - для прямых ссылок, 3 - для обычных
						$referer = empty($arr_click_info[3]) ? $arr_click_info[17] : $arr_click_info[3];
						
						$sql_click_params_arr["click_param_name" . $i] = 'text';
						$sql_click_params_arr["click_param_value" . $i] = parse_search_refer($referer);
					}
					
					unset($click_get_params[$param_name]); // Параметр отработан, убираем его чтобы остались только пользовательские
				}
				$i++;
			}
			
			// Удаляем параметры из чёрной списка (для трекеров, которые шлют нам много лишнего)
			if(isset($source_config[$click_link_source]['rapams_ignore'])) {
				foreach($source_config[$click_link_source]['rapams_ignore'] as $param_name) {
					unset($click_get_params[$param_name]);
				}
			}
			
			// Удаляем дополнительные параметры "прямого перехода"
			$direct_params = array('utm_source', 'rule_name', 'utm_campaign');
			foreach($direct_params as $param_name) {
				unset($click_get_params[$param_name]);
			}
			
			/*
			foreach($click_get_params as $param_name => $param_value) {
				if(!empty($source_config[$click_link_source]['params'][$param_name]['n'])) {
					//$i = $source_config[$click_link_source]['params'][$param_name]['n'] + 5;
					$sql_click_params[]="click_param_name{$i}='"._str($param_name)."', click_param_value{$i}='"._str($param_value)."'";
					
					// Adwords передает в одном параметре и то что это спецразмещение и то, что это реклама в сайдбаре и сразу же позицию объявления. Поэтому мы разбиваем это значение на два параметра, Размещение (если t - Спецразмещение, если s - Реклама справа, если o или что-то другое - Не определено) и Позиция (где выводим значения как есть, то есть 1t1, 1s2 и т.д.) 
					
					if($click_link_source == 'adwords' and $param_name == 'adposition') {
						$position_type = 0;
						if(strstr($param_value, 's') !== false) {
							$position_type = 's';
						}
						
						if(strstr($param_value, 't') !== false) {
							$position_type = 't';
						}
						$sql_click_params[]="click_param_name10='position_type', click_param_value10='"._str($position_type)."'";
					}
					
					unset($click_get_params[$param_name]);
				}
			}
			*/
		}
		
		// Пользовательские параметры
		
		
		$is_connected=false; 
		$connected_subid='';
		foreach ($click_get_params as $param_name => $param_value)
		{
			if ($param_name=='_subid')
			{
				$pattern = '/\d{14}x\d{5}/';
				preg_match_all($pattern, $param_value, $subids);
				foreach($subids[0] as $t_key=>$t_subid)
				{
					if ($t_subid!='')
					{
						$is_connected=true;					
						$connected_subid=$t_subid;
					}
					break;
				}
				continue;
			}

			$sql_click_params_arr["click_param_name" . $i] = $param_name;
			$sql_click_params_arr["click_param_value" . $i] = $param_value;
			
			$i++;

			// Maximum 15 get parameters allowed
			if ($i > 15){break;}
		}

		// Click from landing page
		
		if ($is_connected) {
			if(_CLICKS_SPOT_SIZE > 0) {
				$parent_id = 0;
				
				// Скорее всего, связанный клик у нас будет в текущем споте
				$q = "select id from tbl_clicks_s" . $current_spot_id . " where subid='"._str($connected_subid)."' limit 1";
				if($rs = mysql_query($q) and mysql_num_rows($rs) > 0) {
					$r = mysql_fetch_assoc($rs);
					$parent_id = $r['id'];
					
				// Не нашли в текущем. Может где-то ещё?
				} else {
					$tmp = subidtotime($connected_subid);
					$tmp = clicks_spot_get($tmp, $tmp);
					if(!empty($tmp)) {
						$q = "select id from tbl_clicks_s" . $tmp[0] . " where subid='"._str($connected_subid)."' limit 1";
						if($rs = mysql_query($q) and mysql_num_rows($rs) > 0) {
							$r = mysql_fetch_assoc($rs);
							$parent_id = $r['id'];
						}
					}
				}
				
			} else {
				// Get parent click id
				$sql="select id from tbl_clicks where subid='"._str($connected_subid)."' limit 1";
				$result=mysql_query($sql);
				$row=mysql_fetch_assoc($result);
				if ($row['id']>0) {
					$parent_id=$row['id'];
					$sql="update tbl_clicks set is_parent=1 where id='"._str($parent_id)."'";
					mysql_query($sql);
				} else {
					$parent_id=0;
				}
			}
		}
		
		$ins = array(
			'date_add'   => $click_date,
			'user_ip'    => $click_ip,
			'user_agent' => $click_user_agent,
			'user_os'    => $device_os,
			'user_os_version' => $device_os_version,
			'user_platform' => $brand_name,
			'user_platform_info' => $model_name,
			'user_platform_info_extra' => $model_extra_info,
			'user_browser' => $device_browser,
			'user_browser_version' => $device_browser_version,
			'is_mobile_device' => $is_mobile_device,
			'is_phone'     => $is_phone,
			'is_tablet'    => $is_tablet,
			'country'      => $click_country,
			'state'        => $click_state,
			'city'         => $click_city,
			'region'       => $click_region,
			'isp'          => $click_isp,
			'rule_id'      => $click_rule_id,
			'out_id'       => $click_out_id,
			'subid'        => $click_subid,
			'is_connected' => $is_connected,
			'is_unique'    => $click_is_unique,
			'parent_id'    => $parent_id,
			'subaccount'   => $click_subaccount,
			'source_name'  => $click_link_source,
			'campaign_name' => $click_link_campaign,
			'ads_name'      => $click_link_ads,
			'referer'       => $click_referer,
			'search_string' => '', 
			'campaign_param1' => $click_param1,
			'campaign_param2' => $click_param2,
			'campaign_param3' => $click_param3,
			'campaign_param4' => $click_param4,
		);
		
		if(!empty($sql_click_params_arr)) {
			$ins = array_merge($ins, $sql_click_params_arr);
		}
		
		// Turbo
		if(_CLICKS_SPOT_SIZE > 0) {
			$q = insertsql($ins, 'tbl_clicks_s' . $current_spot_id, false, true);
			//echo $q . '<br />';
			echo '. ';
    		db_query($q);
    		
    		$ins_id = mysql_insert_id();
    		
    		// Если это первая запись - помечаем дату начала спота в карте
    		if($ins_id == (($current_spot_id - 1) * _CLICKS_SPOT_SIZE) + 1) {
    			$upd = array(
    				'id' => $current_spot_id,
    				'time_begin' => $ins['date_add'],
    			);
    			$q = updatesql($upd, 'tbl_clicks_map', 'id');
    			db_query($q);
    		
    		// Если спот подходит к концу
    		} elseif($ins_id >= ($current_spot_id * _CLICKS_SPOT_SIZE)) {
    			
    			$q = "select max(`date_add`) as `max_time` from `tbl_clicks_s" . $current_spot_id . "`";
    			$rs = db_query($q);
    			$r = mysql_fetch_assoc($rs);
    			$max_spot_time = $r['max_time'];
    			
    			// Закрываем текущий спот
    			$upd = array(
    				'id' => $current_spot_id,
    				'time_end' => $max_spot_time,
    				'current' => '0'
    			);
    			$q = updatesql($upd, 'tbl_clicks_map', 'id');
    			db_query($q);
    			
    			// Создание нового спота
			    $ins = array(
			        'time_begin' => '2000-01-01 00:00:00',
			        'time_end' => '2020-01-01 00:00:00',
			        'current' => '1'
			    );
			    $q = insertsql($ins, 'tbl_clicks_map');
			    db_query($q);
			    
			    $current_spot_id = mysql_insert_id();
    			
    			$q="CREATE TABLE IF NOT EXISTS `tbl_clicks_s" . $current_spot_id . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_add` datetime NOT NULL,
  `user_ip` varchar(255) NOT NULL,
  `user_agent` text CHARACTER SET utf8 NOT NULL,
  `user_os` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_os_version` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform_info` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_platform_info_extra` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_browser` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user_browser_version` varchar(255) CHARACTER SET utf8 NOT NULL,
  `is_mobile_device` tinyint(1) NOT NULL,
  `is_phone` tinyint(1) NOT NULL,
  `is_tablet` tinyint(1) NOT NULL,
  `country` varchar(255) NOT NULL,
  `state` varchar(255) CHARACTER SET utf8 NOT NULL,
  `city` varchar(255) CHARACTER SET utf8 NOT NULL,
  `region` varchar(255) CHARACTER SET utf8 NOT NULL,
  `isp` varchar(255) CHARACTER SET utf8 NOT NULL,
  `rule_id` int(11) NOT NULL,
  `out_id` int(11) NOT NULL,
  `subid` varchar(255) CHARACTER SET utf8 NOT NULL,
  `subaccount` varchar(255) CHARACTER SET utf8 NOT NULL,
  `source_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ads_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `referer` text CHARACTER SET utf8 NOT NULL,
  `search_string` text CHARACTER SET utf8 NOT NULL,
  `click_price` decimal(10,4) NOT NULL,
  `conversion_price_main` decimal(10,4) NOT NULL,
  `is_lead` tinyint(1) NOT NULL,
  `is_sale` tinyint(1) NOT NULL,
  `is_parent` tinyint(1) NOT NULL,
  `is_connected` tinyint(1) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `campaign_param1` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param2` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param3` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param4` varchar(255) CHARACTER SET utf8 NOT NULL,
  `campaign_param5` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_name1` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value1` text CHARACTER SET utf8 NOT NULL,
  `click_param_name2` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value2` text CHARACTER SET utf8 NOT NULL,
  `click_param_name3` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value3` text CHARACTER SET utf8 NOT NULL,
  `click_param_name4` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value4` text CHARACTER SET utf8 NOT NULL,
  `click_param_name5` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value5` text CHARACTER SET utf8 NOT NULL,
  `click_param_name6` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value6` text CHARACTER SET utf8 NOT NULL,
  `click_param_name7` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value7` text CHARACTER SET utf8 NOT NULL,
  `click_param_name8` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value8` text CHARACTER SET utf8 NOT NULL,
  `click_param_name9` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value9` text CHARACTER SET utf8 NOT NULL,
  `click_param_name10` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value10` text CHARACTER SET utf8 NOT NULL,
  `click_param_name11` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value11` text CHARACTER SET utf8 NOT NULL,
  `click_param_name12` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value12` text CHARACTER SET utf8 NOT NULL,
  `click_param_name13` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value13` text CHARACTER SET utf8 NOT NULL,
  `click_param_name14` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value14` text CHARACTER SET utf8 NOT NULL,
  `click_param_name15` varchar(255) CHARACTER SET utf8 NOT NULL,
  `click_param_value15` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subid` (`subid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=".(($current_spot_id - 1) * _CLICKS_SPOT_SIZE + 1).";";
    			db_query($q);
    		}
    	
    	// Standart
		} else {
			$q = insertsql($ins, 'tbl_clicks', false, true);
    		db_query($q);
		}
	}
?>