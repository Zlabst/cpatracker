<?php

/*
 * ������ ������ �� ����������
 */
function dir_files($path, $type = '') {
	$files = array();
	if ($handle = opendir($path)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && !is_dir($path . $file)) {
				if($type != '' and (strstr($file, '*') !== false or $file == '.' . $type . '_' . date('Y-m-d-H-i'))) continue;
				$files[] = $file;
			}
		}
	}
	return $files;
}

/*
 * �������� ������� IP
 * ������� ������ ����������: 8.8.8.9 - 8.8.10.255, 212.11.92.*, 212.11.*.*, 212.10.*.100 
 */
 
function check_ip($mask, $ip) {
	// ������� ������� ����� � �������
	$mask = str_replace(' -', '-', $mask);
	$mask = str_replace('- ', '-', $mask);
	
	// �������� ��� ����������� ��������
	$mask = str_replace(';', ' ', $mask);
	$mask = str_replace(',', ' ', $mask);
	$mask = preg_replace("/\s+/", ' ', $mask);
	
	$mask = explode(' ', $mask);
	foreach($mask as $current_mask) {
		// ����� ���� � ���������� IP
		if(strstr($current_mask, '-') !== false) {
			list($ip_start, $ip_end) = explode('-', $current_mask);
			if(ip_in_range($ip, $ip_start, $ip_end)) {
				return true;
			}
		// ��������� IP, �������� � * 
		} else {
			if(ip_in_range($ip, $current_mask)) {
				return true;
			}
		}
	}
	
	return false;
}

/*
 * ����������� ��������� IP � ������ �� 4-� ���������
 */
function ip2arr($ip) {
	if(empty($ip)) return array();
	$ip_arr = explode('.', $ip);
	return count($ip_arr) == 4 ? $ip_arr : array();
}

/*
 * �������� �������������� IP ���������
 * ���� ���� ����� �������� � �������, ���� �������
 */
function ip_in_range($ip, $ip_start, $ip_end = '') {

	// ������� IP
	if(empty($ip_end) and strstr($ip_start, '*') === false) {
		return $ip == $ip_start;
		
	// �������� ��� ����� �� ���������
	} else {
		$ip_arr = ip2arr($ip);
		$ip_start_arr = ip2arr($ip_start);
		
		// ��������
		if(!empty($ip_end)) {
			$ip_end_arr = ip2arr($ip_end);
		
		// ����� �� ���������
		} else {
			for ($i=0; $i<4; $i++) {
				if ($ip_start_arr[$i]=='*') {
					$ip_start_arr[$i]='0';
					$ip_end_arr[$i]='255';
				} else {
					$ip_end_arr[$i]=$ip_start_arr[$i]; 
				}
			}
		}
		
		$ip_num = ip2long($ip);
		return ($ip_num >= ip2long(join('.', $ip_start_arr)) && $ip_num <= ip2long(join('.', $ip_end_arr)));
	}
	return false;
}

/**
 * ��� ������
 */
function track_error($error) {
	if($error == '') return false;
	
	$log_dir = _CACHE_PATH . '/log';
	if(!is_dir($log_dir)) {
		mkdir ($log_dir);
		chmod ($log_dir, 0777);
	}
	
	$path = $log_dir . '/.' . date('Y-m-d') . '.txt';
	$fp = fopen($path, 'a');
	fwrite($fp, date("Y-m-d H:i:s") . ' ' . $error . "\n");
	fclose($fp);
	chmod($path, 0777);
}




/*
 * ��������� ���������� �� POST|GET|REQUEST 
 *
 * @param string $name - ��� ����������
 * @param string $type - p|g|r ������ ��������
 * @param int $num - ��������� ��� ������: 0 - ������, 1 - ����� �����, 2 - ����� �������������, 3 - json, 4 - date YYYY-MM-DD
 * @param mixed $df - �������� �� ���������
 * @return mised
 */
function rq($name, $num = 0, $df = null, $type = 'r') {
	global $_POST, $_GET, $_REQUEST;
	
	if ($type == 'r') {
		$d = &$_REQUEST;
	} elseif ($type == 'p') {
		$d = &$_POST;
	} elseif ($type == 'g') {
		$d = &$_GET;
	}

	if ($num == 0) {
		$def = ($df == null ? '' : $df);
		return array_key_exists($name, $d) ? $d[$name] : $def;
	} elseif($num < 3) {
		$def = ($df == null ? 0 : $df);
		$out = array_key_exists($name, $d) ? intval($d[$name]) : $def;
		return $num == 2 ? abs($out) : $out;
	} elseif($num == 4) {
		$def = ($df === null ? date('Y-m-d') : $df);
		if(array_key_exists($name, $d)) { 
			if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $d[$name])) {
				return $d[$name];
			} elseif(preg_match('/^\d{2}\.\d{4}$/', $d[$name])) {
				$tmp = explode('.', $d[$name]);
				return date('Y-m-d', mktime(0, 0, 0, $tmp[0], 1, $tmp[1]));
			} else {
				return $def;
			}
		} else {
			return $def;
		}
	} else {
		return array_key_exists($name, $d) ? json_decode($d[$name], true) : array();
	}
	return false;
}

function stripslashes2($v) {
	if(is_array($v)) {
		$v = array_map('stripslashes2', $v);
	} else {
		$v = stripslashes($v);
	}
	return $v;
}

/**
 * ���������� ����������
 */	
function dmp(&$v) {
	echo '<pre>'.print_r($v, true).'</pre>';
}


function onlyword($v) {
	return preg_replace("/[^a-zA-Z0-9_-]/u", '', $v);
}
?>