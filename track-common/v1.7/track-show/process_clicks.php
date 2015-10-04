<?php
    if(function_exists('set_time_limit')){set_time_limit(0);}

    prepare_debug();

    global $_PROFILING_clicks_processed;
    global $_PROFILING_files_processed;
    prepare_profiling();

    enable_shutdown_tracking();

    set_already_running_flag();

    require _TRACK_SHOW_COMMON_PATH . "/source_config.php";

	hide_crontab_notification();

    connect_to_database();
    update_currency_rates();

	download_clicks();
	get_clicks_to_process();
	mark_all_files_processing();

    load_geoip();
    load_uaparser();
    load_wurfl_manager();

	foreach ($arr_files as $cur_file) 
	{
		$timeshift=get_clicks_timeshift($cur_file);
		$file_name=mark_file_processing_now($cur_file);

		$handle = fopen($file_name, "r");
	    while (($buffer = fgets($handle, 4096)) !== false) 
	    {
		    $arr_click=array();
	        $arr_click=explode ("\t", rtrim($buffer, "\n"));
	        save_click_info ($arr_click, $timeshift);
            if (defined('_ENABLE_PROFILING') && _ENABLE_PROFILING){$_PROFILING_clicks_processed++;}
	    }
	    fclose($handle);

	    mark_file_processed($file_name, $cur_file);
        if (defined('_ENABLE_PROFILING') && _ENABLE_PROFILING){$_PROFILING_files_processed++;}
	}

	unload_geoip();

    define('PROCESS_CLICKS_SUCCESSFUL_TERMINATION', true);
	exit();
?>
<?php

function prepare_debug()
{
    if (defined('_ENABLE_DEBUG') && _ENABLE_DEBUG)
    {
        error_reporting(E_ALL);
        if(function_exists('ini_set'))
        {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', true);
        }
    }
}

function prepare_profiling()
{
    if (defined('_ENABLE_PROFILING') && _ENABLE_PROFILING)
    {
        global $_PROFILING_time_start;
        global $_PROFILING_clicks_processed;
        global $_PROFILING_files_processed;
        global $_PROFILING_arr_steps;

        require_once(_TRACK_SHOW_COMMON_PATH . "/functions_profiling.php");
        $_PROFILING_time_start=microtime(true);
        $_PROFILING_clicks_processed=0;
        $_PROFILING_files_processed=0;
        $_PROFILING_arr_steps=array();
    }
}

function enable_shutdown_tracking()
{
    register_shutdown_function('process_clicks_shutdown');
}

function process_clicks_shutdown()
{
    if (defined('_ENABLE_PROFILING') && _ENABLE_PROFILING)
    {
        global $_PROFILING_time_start;
        global $_PROFILING_clicks_processed;
        global $_PROFILING_files_processed;
        global $_PROFILING_execution_time;
        global $_PROFILING_arr_steps;

        $arr_result=array();
        $_PROFILING_execution_time=microtime(true)-$_PROFILING_time_start;
        $profiling_path=_CACHE_PATH.'/profiling';
        check_create_directory($profiling_path);
        if (defined('PROCESS_CLICKS_SUCCESSFUL_TERMINATION') && PROCESS_CLICKS_SUCCESSFUL_TERMINATION)
        {
            $arr_result['status']='success';
        }
        else
        {
            $arr_result['status']='error';
        }

        if (is_array($_PROFILING_arr_steps) && count($_PROFILING_arr_steps)>0) {
            $arr_result['steps'] = $_PROFILING_arr_steps;
        }
        $arr_result['date_add']=date('Y-m-d H:i:s');
        $arr_result['clicks_processed']=$_PROFILING_clicks_processed;
        $arr_result['files_processed']=$_PROFILING_files_processed;
        $arr_result['execution_time']=$_PROFILING_execution_time;
        $profiling_data=serialize($arr_result);

        file_put_contents($profiling_path.'/.'.date('Y-m-d'), $profiling_data."\n", FILE_APPEND);
    }

    unset_already_running_flag();
}

function check_already_running_flag()
{
    $path=_CACHE_PATH.'/.process_clicks_running';
    if (file_exists($path))
    {

        $last_run_time=file_get_contents($path);
        if (time()-$last_run_time > 60*5)
        {
            // Script is running more than 5 minutes, anything except success
            exit();
        }
        else
        {
            define('PROCESS_CLICKS_SUCCESSFUL_TERMINATION', true);
            exit();
        }
    }
}

function set_already_running_flag()
{
    check_already_running_flag();

    $path=_CACHE_PATH.'/.process_clicks_running';
    file_put_contents($path, time(), LOCK_EX);
    if (!file_exists($path))
    {
        // Were not able to create flag, exit with error
        exit();
    }
}

function unset_already_running_flag()
{
    $path=_CACHE_PATH.'/.process_clicks_running';
    if (file_exists($path))
    {
        unlink($path);
    }
}

function save_click_info ($arr_click_info, $timeshift = 0)
{
    global $link;
	$IN=array();
	$OUT=array();
	$parent_subid='';

	$IN['date_time']=isset($arr_click_info[0])?$arr_click_info[0]:'';
	$IN['ip']=isset($arr_click_info[1])?$arr_click_info[1]:'';
	$IN['user_agent']=isset($arr_click_info[2])?$arr_click_info[2]:'';
	$IN['referer']=isset($arr_click_info[3])?$arr_click_info[3]:'';
	$IN['link_name']=isset($arr_click_info[4])?$arr_click_info[4]:'';
	$IN['source']=isset($arr_click_info[5])?$arr_click_info[5]:'';
	$IN['campaign_ads']=isset($arr_click_info[6])?$arr_click_info[6]:'';
	$IN['subid']=isset($arr_click_info[7])?$arr_click_info[7]:'';
	$IN['subaccount']=isset($arr_click_info[8])?$arr_click_info[8]:'';
	$IN['link_id']=isset($arr_click_info[9])?$arr_click_info[9]:'';
	$IN['offer_id']=isset($arr_click_info[10])?$arr_click_info[10]:'';
	$IN['is_unique']=isset($arr_click_info[11])?$arr_click_info[11]:'';
	$IN['link_param1']=isset($arr_click_info[12])?$arr_click_info[12]:'';
	$IN['link_param2']=isset($arr_click_info[13])?$arr_click_info[13]:'';
	$IN['link_param3']=isset($arr_click_info[14])?$arr_click_info[14]:'';
	$IN['link_param4']=isset($arr_click_info[15])?$arr_click_info[15]:'';
	$IN['link_param5']=isset($arr_click_info[16])?$arr_click_info[16]:'';
	$IN['visit_params']=isset($arr_click_info[17])?$arr_click_info[17]:'';
	

	// 1. Click date
	$OUT['date_time']=($timeshift!=0)?date("Y-m-d H:i:s", strtotime($IN['date_time']) + $timeshift):$IN['date_time'];

	// 2. Click day
	$OUT['day']=current(explode(' ', $IN['date_time']));

	// 3. Click hour
	$OUT['hour']=get_hour_by_date($IN['date_time']);

	// 4. IP address
	$OUT['ip']=$IN['ip'];

	// 5. Country, State, City, Region, ISP
	list ($OUT['country'], $OUT['state'], $OUT['city'], $OUT['region'], $OUT['isp'])=get_country_city_isp($IN['ip']);

	// 6. User-Agent
	$OUT['user_agent']=$IN['user_agent'];

	// 7. OS, OS version, Platform, Platform Info, Platform info extra, Browser, Browser version, Is mobile device, Is phone, Is tablet
	list ($OUT['os'], $OUT['os_version'], $OUT['platform'], $OUT['platform_info'], $OUT['platform_info_extra'],
          $OUT['browser'], $OUT['browser_version'], $OUT['is_mobile_device'], $OUT['is_phone'],
          $OUT['is_tablet'])=get_os_browser_platform($IN['user_agent']);

	// 8. Campaing and ads
	list ($OUT['campaign'], $OUT['ads'])=parse_campaign_ads($IN['campaign_ads']);
	
	// 9. SubID and subaccount
	$OUT['subid']=$IN['subid'];
	$OUT['subaccount']=$IN['subaccount'];

	// 10. Link ID and offer ID
	$OUT['link_id']=$IN['link_id'];
	$OUT['offer_id']=$IN['offer_id'];
	
	// 11. Source
	$OUT['source']=$IN['source'];

	// 12. Is unique click
	$OUT['is_unique']=$IN['is_unique'];

	// 13. Referer
	$OUT['referer']=$IN['referer'];

    // 14. Keyword
    $OUT['keyword']=parse_search_referer($IN['referer']);

	// 14. Links params 1-5
	$OUT['link_param1']=$IN['link_param1'];
	$OUT['link_param2']=$IN['link_param2'];
	$OUT['link_param3']=$IN['link_param3'];
	$OUT['link_param4']=$IN['link_param4'];
	$OUT['link_param5']=$IN['link_param5'];

	// 15. Visit params, parse and check for clicks from landing page
	$OUT['visit_params']=parse_visit_params($IN, $parent_subid);

	// 16. Process clicks from landing page
	$OUT['parent_id']=0;
	$OUT['is_connected']=false;
	if ($parent_subid!='')
	{
        $id=0;
        $sql_prepared="select id from tbl_clicks where subid= ? limit 1";
        $stmt = $link->prepare($sql_prepared);
        $stmt->bind_param('s', $parent_subid);
        $stmt->execute();
        $stmt->bind_result($id);

        while ($stmt->fetch()) {
            ;
        }
        if ($id>0)
        {
            $OUT['is_connected']=true;
            $OUT['parent_id']=$id;
            $sql_prepared="update tbl_clicks set is_parent=1 where id= ?";
            $stmt = $link->prepare($sql_prepared);
            $stmt->bind_param('i', $OUT['parent_id']);
            $result=$stmt->execute();
            if ($result===false)
            {
                // Were not able to run query, exiting with error
                exit();
            }

        }
        $stmt->close();
	}

	// 17. Save current click in DB
    $arr_prepared_visit_params=array();

    for ($i=1; $i<=count($OUT['visit_params'])/2; $i++){
        $arr_prepared_visit_params[]="`click_param_name{$i}`= ?, `click_param_value{$i}`= ?";
    }

    $bind_visit_params=array_values($OUT['visit_params']);

    $sql_prepared_visit_params=implode (',', $arr_prepared_visit_params);
	if (strlen($sql_prepared_visit_params)>0)
	{
        $sql_prepared_visit_params=", {$sql_prepared_visit_params}";
	}

    $sql_prepared="INSERT IGNORE INTO tbl_clicks SET `date_add`= ?, `date_add_day`= ?, `date_add_hour`= ?, `user_ip`= ?, `user_agent`= ?, `user_os`= ?, `user_os_version`= ?, `user_platform`= ?, `user_platform_info`= ?, `user_platform_info_extra`= ?, `user_browser`= ?, `user_browser_version`= ?, `is_mobile_device`= ?, `is_phone`= ?, `is_tablet`= ?, `country`= ?, `state`= ?, `city`= ?, `region`= ?, `isp`= ?, `rule_id`= ?, `out_id`= ?, `subid`= ?, `is_connected`= ?, `is_unique`= ?, `parent_id`= ?, `subaccount`= ?, `source_name`= ?, `campaign_name`= ?, `ads_name`= ?, `referer`= ?, `search_string`= ?, `campaign_param1`= ?, `campaign_param2`= ?, `campaign_param3`= ?, `campaign_param4`= ?, `campaign_param5`= ?{$sql_prepared_visit_params}";

    $arr_bind_values=array($OUT['date_time'], $OUT['day'], $OUT['hour'], $OUT['ip'], $OUT['user_agent'], $OUT['os'], $OUT['os_version'], $OUT['platform'], $OUT['platform_info'], $OUT['platform_info_extra'], $OUT['browser'], $OUT['browser_version'], $OUT['is_mobile_device'], $OUT['is_phone'], $OUT['is_tablet'], $OUT['country'], $OUT['state'], $OUT['city'], $OUT['region'], $OUT['isp'], $OUT['link_id'], $OUT['offer_id'], $OUT['subid'], $OUT['is_connected'], $OUT['is_unique'], $OUT['parent_id'], $OUT['subaccount'], $OUT['source'], $OUT['campaign'], $OUT['ads'], $OUT['referer'], $OUT['keyword'], $OUT['link_param1'], $OUT['link_param2'], $OUT['link_param3'], $OUT['link_param4'], $OUT['link_param5']);

    $arr_bind_values=array_merge(array('ssisssssssssiiissssssssiiisssssssssss'.str_repeat('s', count($bind_visit_params))), $arr_bind_values, $bind_visit_params);

    // Create array of references, so bind can work
    for ($i=0; $i<count($arr_bind_values); $i++)
    {
        $arr_bind_values[$i]=&$arr_bind_values[$i];
    }

    $res    = $link->prepare($sql_prepared);
    $ref    = new ReflectionClass('mysqli_stmt');
    $method = $ref->getMethod("bind_param");
    $method->invokeArgs($res, $arr_bind_values);
    $result=$res->execute();

    if ($result===false)
    {
        // Were not able to run query, exiting with error
        exit();
    }
}


function get_country_city_isp($ip)
{
	global $maxmind_gi;
	global $maxmind_giisp;
    global $GEOIP_REGION_NAME;

    // Don't waste resources on wrong IPs
    if (filter_var($ip, FILTER_VALIDATE_IP)===false)
    {
        return array ('', '', '', '', '');
    }

	$record = geoip_record_by_addr($maxmind_gi, $ip);
    $isp = geoip_org_by_addr($maxmind_giisp, $ip);

	$country=(is_object($record))?$record->country_code:'';

	// Resolve GeoIP extension conflict
	if (function_exists('geoip_country_code_by_name') && ($country==''))
	{
		$country=geoip_country_code_by_name($ip);
	}

    if (is_object($record))
    {
        if (isset ($GEOIP_REGION_NAME[$record->country_code]) && $record->region!='')
        {
            if (isset($GEOIP_REGION_NAME[$record->country_code][$record->region]))
            {
                $state=$GEOIP_REGION_NAME[$record->country_code][$record->region];
            }
            else
            {
                $state='';
            }
        }
        else
        {
            $state='';
        }

        $city=$record->city;
        $region=$record->region;
    }
    else
    {
        $state='';
        $city='';
        $region='';
    }

	return array ($country, $state, $city, $region, $isp);
}

function get_os_browser_platform($user_agent)
{
	global $wurflManager; // WURFL
	global $parser;       // UAParser

	$OS='';
	$OS_VERSION='';
	$PLATFORM='';
	$PLATFORM_INFO='';
	$PLATFORM_INFO_EXTRA='';
	$BROWSER='';
	$BROWSER_VERSION='';
	$IS_MOBILE_DEVICE=false;
	$IS_PHONE=false;
	$IS_TABLET=false;

    if ((defined ('_XMLREADER_INSTALLED') && _XMLREADER_INSTALLED) || (extension_loaded('xmlreader')))
    {
        $requestingDevice = $wurflManager->getDeviceForUserAgent($user_agent);

        $is_wireless = ($requestingDevice->getCapability('is_wireless_device') == 'true');
        $IS_TABLET = ($requestingDevice->getCapability('is_tablet') == 'true');
        $IS_MOBILE_DEVICE = ($is_wireless || $IS_TABLET);

        if ($IS_MOBILE_DEVICE)
        {	
        	// Use WURFL database info for mobile devices only	
            $OS = $requestingDevice->getCapability('device_os');
            $OS_VERSION = $requestingDevice->getCapability('device_os_version');
            $PLATFORM=$requestingDevice->getCapability('brand_name');
            $PLATFORM_INFO=$requestingDevice->getCapability('model_name');
            $PLATFORM_INFO_EXTRA=$requestingDevice->getCapability('model_extra_info');        
            $BROWSER = $requestingDevice->getCapability('mobile_browser');
            $BROWSER_VERSION = $requestingDevice->getCapability('mobile_browser_version');
            $IS_PHONE = ($requestingDevice->getCapability('can_assign_phone_number') == 'true');
        }
        else
        {
            // Use UAParser to get click info
            $result = $parser->parse($user_agent);
            $OS=$result->os->family;
            $OS_VERSION=$result->os->toVersionString;
            $BROWSER=$result->ua->family;
            $BROWSER_VERSION=$result->ua->toVersionString;           
        }
    }

	return array($OS, $OS_VERSION, $PLATFORM, $PLATFORM_INFO, $PLATFORM_INFO_EXTRA, $BROWSER, $BROWSER_VERSION, $IS_MOBILE_DEVICE, $IS_PHONE, $IS_TABLET);
}

function parse_campaign_ads($campaign_ads)
{
	if (strpos($campaign_ads, '-')!==false) 
	{
		return array(current(explode('-', $campaign_ads)), substr($campaign_ads, strpos($campaign_ads, '-')+1));			
	} 
	else 
	{
		return array($campaign_ads, '');
	}
}

function parse_visit_params($IN, &$parent_subid)
{
	global $source_config;
	
	$click_get_params=array();
	$result = array();

	if($IN['visit_params']!='') 
	{
		// Parse to array param_name=>param_value
		parse_str ($IN['visit_params'], $click_get_params);
	}
	
	$i = 1;

	if(array_key_exists($IN['source'], $source_config) && array_key_exists('params', $source_config[$IN['source']]))
	{
		// Parameter is found in $source_config, iterate through available source params
		foreach($source_config[$IN['source']]['params'] as $param_name => $param_info) 
		{
			if(empty($param_info['url'])){continue;} // Got source predefined values, used in reports only

			if(array_key_exists($param_name, $click_get_params)) 
			{
				$param_value = $click_get_params[$param_name];
				if($param_info['url'] == $param_value) {$param_value = '';} // Recieved site_id={site_id}, set empty value

				$result["click_param_name".$i] = $param_name;
				$result["click_param_value".$i] = $param_value;
				
				// Parameters postprocessing
				switch ($IN['source'])
				{
					case 'adwords':
						if($param_name == 'adposition') 
						{
							// Parse adposition, 1t2=>Placement type (premium, right column) and Position (1t1, 1s2) 
							$position_type = 0;
							if(strstr($param_value, 's') !== false) 
							{
								$position_type = 's';
							}
							
							if(strstr($param_value, 't') !== false) 
							{
								$position_type = 't';
							}
							
							$i++;
							$result["click_param_name".$i] = 'position_type';
							$result["click_param_value".$i] = $position_type;
						}
					break;

					case 'yadirect':
						if($param_name == 'ad_id') 
						{
							// Parse search term from referer
							// Direct links: $IN['visit_params'], for tracker links: $IN['referer']
							$referer = ($IN['referer']=='') ? $IN['visit_params'] : $IN['referer'];
							
							$i++;
							$result["click_param_name".$i] = 'text';
							$result["click_param_value".$i] = parse_search_referer($referer);
						}
					break;
				}

				unset($click_get_params[$param_name]); // Remove processed parameter to leave user-defined parameters only
			}
			$i++;
		}
		
		// Remove parameters that are not useful for this source, ex: clid, msid, uuid, state
		if(isset($source_config[$IN['source']]['rapams_ignore'])) 
		{
			foreach($source_config[$IN['source']]['rapams_ignore'] as $param_name) 
			{
				unset($click_get_params[$param_name]);
			}
		}
		
		// Remove tracker parameters used for direct linking
		$direct_params = array('rule_name', 'utm_source', 'utm_campaign');
		foreach($direct_params as $param_name) 
		{
			unset($click_get_params[$param_name]);
		}
	}
	
	// Process user-defined visit parameters 
	foreach ($click_get_params as $param_name => $param_value)
	{
		// Get SubID for connected click
		if ($param_name=='_subid')
		{
			$pattern = '/\d{14}x\d{5}/';
			preg_match_all($pattern, $param_value, $subids);
			foreach($subids[0] as $t_key=>$t_subid)
			{
				if ($t_subid!='')
				{			
					$parent_subid=$t_subid;
				}
				break;
			}
			continue;
		}

		// Save user-defined parameters
		$result["click_param_name".$i] = $param_name;
		$result["click_param_value".$i] = $param_value;
		
		$i++;

		// Maximum 15 get parameters are allowed
		if ($i > 15){break;}
	}

	return $result;
}

function check_create_directory($name)
{
	if (!is_dir($name)) 
	{
		mkdir ($name);
		chmod ($name, 0777);
	}
}

function get_hour_by_date($str)
{
    $t=explode (' ', $str);
	$a = end($t);
	return current(explode (':', $a));	
}

// Get clicks data from remote trackers
function api_get_files($url, $n = 0) 
{
	foreach(array('clicks', 'postback') as $type) 
	{
		$url_params = $url['path'] . '/api.php?act=data_get&type=' . $type. '&key=' . $url['key'];
		$files = json_decode(file_get_contents($url_params), true);
		
		foreach($files['data'] as $f => $data) 
		{
			$path = _CACHE_PATH . '/' . $type . '/' . $f . '_' . $n;
			
			if(!file_exists($path)) 
			{
				$fp = fopen($path, 'w');
				if($fp && fwrite($fp, $data) && fclose($fp)) 
				{
					$url_params = $url['path'] . '/api.php?act=data_get_confirm&type=' . $type. '&key=' . $url['key'] . '&file=' . $f;
					file_get_contents($url_params);
				}
			}
		}
	}
}

function hide_crontab_notification()
{
	// Remove first_run marker
	$process_clicks_marker=_CACHE_PATH.'/.crontab_clicks';
	if(file_exists($process_clicks_marker)) 
	{
		unlink ($process_clicks_marker);
	}	
}	

function connect_to_database()
{
    global $link;
	$settings_file=_TRACK_SETTINGS_PATH.'/settings.php';
	$str=file_get_contents($settings_file);
	$str=str_replace('<?php exit(); ?>', '', $str);
	$arr_settings=unserialize($str);

	$_DB_LOGIN=$arr_settings['login'];
	$_DB_PASSWORD=$arr_settings['password'];
	$_DB_NAME=$arr_settings['dbname'];
	$_DB_HOST=$arr_settings['dbserver'];

    $link = new mysqli($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD, $_DB_NAME);
    if ($link->connect_errno)
    {
        printf("Could not connect: %s\n", $link->connect_error);
        exit();
    }

    $link->set_charset("utf8");
    $link->query("SET sql_mode=''");
}

function download_clicks()
{
	global $tracklist;
	foreach($tracklist as $n => $track) 
	{
		if($n == 0) continue; // First tracker is master, don't touch

		if(substr($track['path'], 0, 4) == 'http') 
		{
			// Remote tracker
			api_get_files($track, $n);
		}
		else
		{
			// Local tracker
			foreach(array('clicks', 'postback') as $type) 
			{
				$files = dir_files($track['path'] . '/cache/' . $type, $type);
				foreach($files as $f) 
				{
					rename($track['path'] . '/cache/' . $type . '/' . $f, _CACHE_PATH . '/' . $type . '/' . $f . '_' . $n);
				}
			}
		}
	}
}	

function get_clicks_to_process()
{
	global $arr_files;
	$iCnt=0;
	if ($handle = opendir(_CACHE_PATH . '/clicks/')) 
	{
	    while (false !== ($entry = readdir($handle))) 
	    {
	        if ($entry != "." && $entry != ".." && $entry != ".empty") 
	        {
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
		        		) 
		        	{
			        	$arr_files[]=$entry;
			        	$iCnt++;
			        	if ($iCnt>=_CLICKS_PROCESS_AT_ONCE){break;}
		        	}
		        }
	        }
	    }
	    closedir($handle);
	}


    if (is_array($arr_files) && count($arr_files)>0)
    {
        // Sort files
        asort($arr_files);
    }
    else
    {
        define('PROCESS_CLICKS_SUCCESSFUL_TERMINATION', true);
        exit();
    }
}

function mark_all_files_processing()
{
	global $arr_files;
	foreach ($arr_files as $cur_file) 
	{
		$file_name=_CACHE_PATH."/clicks/{$cur_file}+";
		rename (_CACHE_PATH."/clicks/$cur_file", $file_name);
	}
}
	
function load_uaparser()
{
	global $parser;
	require_once (_TRACK_LIB_PATH."/ua-parser/uaparser.php");
	$parser = new UAParser;
}

function load_geoip()
{
	global $maxmind_gi;
	global $maxmind_giisp;
    global $GEOIP_REGION_NAME;
	require_once (_TRACK_LIB_PATH."/maxmind/geoip.inc.php");
	require_once (_TRACK_LIB_PATH."/maxmind/geoipcity.inc.php");
	require_once (_TRACK_LIB_PATH."/maxmind/geoipregionvars.php");
	$maxmind_gi = geoip_open(_TRACK_STATIC_PATH."/maxmind/MaxmindCity.dat", GEOIP2_STANDARD);
	$maxmind_giisp = geoip_open(_TRACK_STATIC_PATH."/maxmind-isp/GeoIPISP.dat", GEOIP2_STANDARD);
}

function unload_geoip()
{
	global $maxmind_gi;
	global $maxmind_giisp;	

	geoip_close($maxmind_gi);
	geoip_close($maxmind_giisp);
}

function load_wurfl_manager()
{
	global $wurflManager;
    if ((defined ('_XMLREADER_INSTALLED') && _XMLREADER_INSTALLED) || (extension_loaded('xmlreader')))
    {
        // Init WURFL library for mobile device detection
        $wurflDir = _TRACK_LIB_PATH . '/wurfl/WURFL';
        $resourcesDir = _TRACK_LIB_PATH . '/wurfl/resources';	
        require_once $wurflDir.'/Application.php';
        $persistenceDir = _CACHE_COMMON_PATH.'/wurfl-persistence';
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
}

function get_clicks_timeshift($cur_file)
{
	global $tracklist;
	$name_parts = explode('_', $cur_file);
	if(count($name_parts) > 2 && !empty($tracklist[$name_parts[2]]['timeshift'])) 
	{
		$slave_timeshift = $tracklist[$name_parts[2]]['timeshift'];
	} 
	else 
	{
		$slave_timeshift = 0;
	}
	return $slave_timeshift;
}	

function mark_file_processing_now($cur_file)
{
	$file_name=_CACHE_PATH."/clicks/{$cur_file}++";
	rename (_CACHE_PATH."/clicks/{$cur_file}+", $file_name);
	return $file_name;
}

function mark_file_processed($file_name, $cur_file)
{
	rename ($file_name, _CACHE_PATH."/clicks_processed/{$cur_file}");	    	
}

function parse_search_referer($refer, $tail = 1)
{
    // База данных поисковых систем
    $search_engines = Array(
        Array("name" => "Картинки.Mail", "pattern" => "go.mail.ru/search_images", "param" => "q"),
        Array("name" => "Mail", "pattern" => "go.mail.ru", "param" => "q"),
        Array("name" => "Google Images", "pattern" => "images.google.", "param" => "q"),
        Array("name" => "Google", "pattern" => "google.", "param" => "q"),
        Array("name" => "Google", "pattern" => "google.", "param" => "as_q"),
        Array("name" => "Live Search", "pattern" => "search.live.com", "param" => "q"),
        Array("name" => "RapidShare Search Engine", "pattern" => "rapidshare-search-engine", "param" => "s"),
        Array("name" => "Rambler", "pattern" => "rambler.ru", "param" => "query"),
        Array("name" => "Rambler", "pattern" => "rambler.ru", "param" => "words"),
        Array("name" => "Yahoo!", "pattern" => "search.yahoo.com", "param" => "p"),
        Array("name" => "Nigma", "pattern" => "nigma.ru/index.php", "param" => "s"),
        Array("name" => "Nigma", "pattern" => "nigma.ru/index.php", "param" => "q"),
        Array("name" => "MSN", "pattern" => "search.msn.com/results", "param" => "q"),
        Array("name" => "Bing", "pattern" => "bing.com/search", "param" => "q"),
        Array("name" => "Ask", "pattern" => "ask.com/web", "param" => "q"),
        Array("name" => "QIP", "pattern" => "search.qip.ru/search", "param" => "query"),
        Array("name" => "RapidAll", "pattern" => "rapidall.com/search.php", "param" => "query"),
        Array("name" => "Яндекс.Картинки", "pattern" => "images.yandex.ru/", "param" => "text"),
        Array("name" => "Яндекс.Mobile", "pattern" => "m.yandex.ru/search", "param" => "query"),
        Array("name" => "Яндекс", "pattern" => "hghltd.yandex.net", "param" => "text"),
        Array("name" => "Яндекс", "pattern" => "yandex.ru", "param" => "text"),
        Array("name" => "Яндекс", "pattern" => "yandex.ua", "param" => "text"),
        Array("name" => "Яндекс", "pattern" => "yandex.kz", "param" => "text"),
        Array("name" => "Яндекс", "pattern" => "yandex.by", "param" => "text"),
        Array("name" => "Avg", "pattern" => "search.avg.com", "param" => "q"),
        Array("name" => "Ukr.net", "pattern" => "search.ukr.net", "param" => "search_query")
    );

    // Отрезать от ссылки "хвост"
    $tmp = explode("?", $refer);
    $chk_site = $tmp[0];  // Имя сайта

    $arr_params=array();
    parse_str(parse_url($refer, PHP_URL_QUERY), $arr_params);

    $result_engine = "";
    $result_title = $refer;
    $signature_found = false;

    foreach ($arr_params as $param_name=>$param_value)
    {
        foreach ($search_engines as $engine)
        {
            // Поиск по всем сигнатурам
            if (strpos($chk_site, $engine['pattern']) !== false &&
                $param_name == $engine['param'] && $param_value!='')
            {
                // Сигнатура найдена
                $result_title = $param_value;
                $result_engine = $engine['name'];
                $signature_found = true;
                break;
            }
        }
        // Show first pattern occurence
        if ($signature_found) {
            break;
        }
    }

    if ($result_engine != "") {
        // Привести строку в текстовый вид
        $str = trim(urldecode($result_title));

        if ($str != "") {
            $result = $str;
        }
        // Пустой поисковый запрос
        else {
            $result = "";
        }
    } else {
        $result = "";
    }

    return ($result);
}

function update_currency_rates()
{
    set_error_handler(function($errno, $errstr, $errfile, $errline)
    {
        throw new Exception($errstr, $errno);
    });

    global $link;
    $cur_date_xml=date('d/m/Y');
    $cur_date_sql=date('Y-m-d');
    $sql = "SELECT COALESCE (TIME_FORMAT(TIMEDIFF(NOW(), MAX(date_add)),'%H'), 'empty') as h FROM tbl_currency_rates";
    $arr_currency_rates=array();
    if ($result = $link->query($sql))
    {
        $row = $result->fetch_assoc();

        // Update currency rate every 12 hours
        if (isset($row) && $row['h']=='empty' || $row['h']>12)
        {
            try
            {
                $str=file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$cur_date_xml);
            }
            catch(Exception $e)
            {
                return;
            }

            try
            {
                $arr=simplexml_load_string($str);
            }
            catch(Exception $e)
            {
                return;
            }
            if (!is_object($arr)){return;}

            foreach ($arr->Valute as $cur)
            {
                $value=str_replace(',', '.', $cur->Value)/(int)$cur->Nominal;
                $arr_currency_rates[(string)$cur->CharCode]=$value;
            }
        }
        $result->free();
    }
    else
    {
        return;
    }

    $arr_active_currencies=array();
    $sql = "SELECT id, code from tbl_currency where is_active=1 and code!='XXX'";
    if ($result = $link->query($sql))
    {
        while ( $row = $result->fetch_assoc())
        {
            $arr_active_currencies[$row['code']]=$row['id'];
        }
    }

    // Russian ruble is used as main currency for this account
    $main_currency_id=16;

    foreach ($arr_active_currencies as $code=>$id)
    {
        if (isset($arr_currency_rates[$code]))
        {
            if ($arr_currency_rates[$code]==0){continue;}
            $sql_prepared="INSERT INTO tbl_currency_rates (main_currency_id, currency_id, rate_date, rate_value, date_add, status)
            VALUES (?,?,?,?,NOW(),0) ON DUPLICATE KEY UPDATE rate_date=?, rate_value=?, date_add=NOW(), status=0";

            $stmt = $link->prepare($sql_prepared);
            $stmt->bind_param('iissss', $main_currency_id, $id, $cur_date_sql, $arr_currency_rates[$code],
                                $cur_date_sql, $arr_currency_rates[$code]);

            $stmt->execute();
        }
    }
}