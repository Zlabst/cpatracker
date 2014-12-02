<?php
	// ������, ����������� ������ � �������� �� ������ ��� �������������� �� ������� ��������
	
	// ���� ������ ����� �������� ������ � ������ ������
	header('Access-Control-Allow-Origin: *');
	
	ob_start();
	
	$settings_file= _TRACK_SETTINGS_PATH.'/settings.php';
	require _TRACK_SHOW_PATH . "/functions_general.php";
 
	$str = file_get_contents($settings_file);
	$str = str_replace('<?php exit(); ?>', '', $str);
	$arr_settings = unserialize($str);

	$_DB_LOGIN    = $arr_settings['login'];
	$_DB_PASSWORD = $arr_settings['password'];
	$_DB_NAME     = $arr_settings['dbname'];
	$_DB_HOST     = $arr_settings['dbserver'];
	$_SERVER_TYPE = $arr_settings['server_type'];
	if ($_SERVER_TYPE==''){exit();}

	if (!function_exists('remove_tab'))
	{
		function remove_tab($str){
			return str_replace ("\t", ' ', $str);
		}
	}
	
	if (!function_exists('add_parent_subid')) {
		function add_parent_subid($domain, $subid) {
			$unique = 0;
			if(array_key_exists('cpa_parents', $_COOKIE)) {
				$parents = json_decode($_COOKIE['cpa_parents'], true);
			} else {
				$parents = array();
			}
			$parents[$domain] = $subid;
			
			// Parent click
			$cookie_time = $_SERVER['REQUEST_TIME'] + 3600;
			
			// Unique user
			$cookie_name = 'cpa_was_here_' . str_replace('.', '_', $domain);
			if(empty($_COOKIE[$cookie_name])) {
				$cookie_time = $_SERVER['REQUEST_TIME'] + (60 * 60 * 24 * 31);
				setcookie($cookie_name, 1, $cookie_time, "/", $_SERVER['HTTP_HOST']);
				$unique = 1;
			}

			setcookie("cpa_parents", json_encode($parents), $cookie_time, "/", $_SERVER['HTTP_HOST']);
			return $unique;
		}
	}

	$requestingDevice   = null;
         
    require_once (_TRACK_LIB_PATH."/ua-parser/uaparser.php");
	if (extension_loaded('xmlreader')) {
            // Init WURFL library for mobile device detection
            $wurflDir = _TRACK_LIB_PATH.'/wurfl/WURFL';
            $resourcesDir = _TRACK_LIB_PATH.'/wurfl/resources';	
            require_once $wurflDir.'/Application.php';
            $persistenceDir = _CACHE_PATH.'/wurfl-persistence';
            $cacheDir = _CACHE_PATH.'/wurfl-cache';	
            $wurflConfig = new WURFL_Configuration_InMemoryConfig();
            $wurflConfig->wurflFile(_TRACK_STATIC_PATH.'/wurfl/wurfl.zip');
            $wurflConfig->matchMode('accuracy');
            $wurflConfig->allowReload(true);
            $wurflConfig->persistence('file', array('dir' => $persistenceDir));
            $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));
            $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
            $wurflManager = $wurflManagerFactory->create();
            $requestingDevice = $wurflManager->getDeviceForUserAgent($_SERVER['HTTP_USER_AGENT']); 
        }

	if (!function_exists('get_geodata'))
	{
		function get_geodata($ip)
		{
			require_once (_TRACK_LIB_PATH."/maxmind/geoip.inc.php");
			require_once (_TRACK_LIB_PATH."/maxmind/geoipcity.inc.php");
			require_once (_TRACK_LIB_PATH."/maxmind/geoipregionvars.php");
			$gi = geoip_open(_TRACK_STATIC_PATH."/maxmind/MaxmindCity.dat", GEOIP_STANDARD);
			$record = geoip_record_by_addr($gi, $ip);
			$isp = geoip_org_by_addr($gi, $ip);
			geoip_close($gi);

			$cur_country=$record->country_code;

			// Resolve GeoIP extension conflict
			if (function_exists('geoip_country_code_by_name') && ($cur_country==''))
			{
				$cur_country=geoip_country_code_by_name($ip);
			}
			
			return array ('country'=>$cur_country, 'state'=>$GEOIP_REGION_NAME[$record->country_code][$record->region], 'city'=>$record->city, 'region'=>$record->region,'isp'=>$isp);
		}
	}

	// Remove trailing slash
	$track_request = rtrim($_REQUEST['track_request'], '/');
	$track_request = explode ('/', $track_request);

	$str=''; // ��� ������ �� ������� � ���

	// Date
	$str.=date("Y-m-d H:i:s")."\t";

	switch ($_SERVER_TYPE) {
		case 'apache':
			$ip=$_SERVER['REMOTE_ADDR'];
		break;

		case 'nginx':
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		break;
	}

	// Check if we have several ip addresses
	if (strpos($ip, ',') !== false) {
		$arr_ips=explode(',', $ip);
		if (trim($arr_ips[0]) != '127.0.0.1') {
			$ip = trim($arr_ips[0]);
		} else {
			$ip = trim($arr_ips[1]);
		}
	}
	
	$str .= remove_tab($ip)."\t";
	
	// Country and city
	$geo_data=get_geodata($ip);
	$cur_country=$geo_data['country'];
	$cur_state=$geo_data['state'];
	$cur_city=$geo_data['city'];
    $isp=$geo_data['isp'];

	// User language
    $user_lang =  substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                
	// User-agent
	$str .= remove_tab($_SERVER['HTTP_USER_AGENT'])."\t";
	
	// 3 Referer
	$str .= remove_tab($_GET['referrer'])."\t";
	
	// 4 Link name
	$link_name = 'landing';
	$str .= $link_name."\t";

	// 5 Link source
	$link_source = (empty($_GET['utm_source']) or empty($source_config[$_GET['utm_source']])) ? 'landing' : $_GET['utm_source'];
	$str .= $link_source."\t";

	// 6 Link ads name

	$link_ads_name = empty($_GET['utm_campaign']) ? 'landing' : $_GET['utm_campaign'];
	$str .= $link_ads_name."\t";

	// Subid
	$subid=date("YmdHis") . 'x' . sprintf ("%05d", rand(0,99999));
	$str .= $subid."\t";

	// Subaccount
	$str .= $subid."\t";
	
	$out_id  = 0;
	$rule_id = 0;
	
	
	$redirect_link = str_ireplace('[SUBID]', $subid, $_GET['redirect_link']);

	// Add rule id
	$str.=$rule_id."\t";

	// Add out id
	$str.=$out_id."\t";
	
	// Other link params
	// Limit number of params to 5
	$track_request=array_slice($track_request, 3, 5);

	// Extend array to 5 params exactly
	$arr_link_params=array();
	for ($i=0; $i<5; $i++) {
		if (isset($track_request[$i])) {
			$arr_link_params[]=$track_request[$i];
		} else {
			$arr_link_params[]='';
		}
	}

	$link_params=implode ("\t", $arr_link_params);

	// Additional GET params
	$request_params=$_GET;
	$get_request=array();
	foreach ($request_params as $key => $value) {
		if ($key=='track_request'){continue;}
        if (strtoupper(substr($key, 0, 3)) == 'IN_') {
            $var = substr($key, 3);
            $redirect_link = str_ireplace('['.$var.']', $value, $redirect_link);
        }
		$get_request[]="{$key}={$value}";
	}
    
    // Write cookie with parent SubID
	$url = parse_url($redirect_link);
	$is_unique = add_parent_subid($url['host'], $subid);
        
    // Cleaning not used []-params
    $redirect_link = preg_replace('/(\[[a-z\_0-9]+\])/i', '', $redirect_link);
	
	// Add unique user
	$str .= $is_unique."\t";
	
	// Possibly last value, don't add \t to the end
	$str .= $link_params;
	
	// Last value, don't add \t
	$request_string=implode ('&', $get_request);
	if (strlen($request_string)>0)
	{
		$str.="\t".$request_string;
	}

	$str.="\n";

	// Save click information in file	
	file_put_contents(_CACHE_PATH.'/clicks/'.'.clicks_'.date('Y-m-d-H-i'), $str, FILE_APPEND | LOCK_EX);
	
	echo $subid;
	exit();
?>