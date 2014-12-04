<?php
require _TRACK_COMMON_PATH . '/functions.php'; 

$act       = rq('act');
$track_key = rq('key');

$out = array(
	'status' => 1, // Всё хорошо
	'data'   => array(),
);

function api_error($error = '') {
	$out = array(
		'status' => 0,
		'error'  => $error,
	);
	return $out;
}

if($track_key != _SELF_TRACK_KEY) {
	api_error('Invalid track key');
}

$maxsize = 10000; // максимальный размер отдаваемых данных


// Получение данных
if($act == 'data_get') {
	$type = rq('type');
	if(!in_array($type, array('clicks', 'postback'))) {
		$out = api_error('Unknown type');
	} else {
		$path = _TRACK_PATH . '/cache/' . $type;
		$files = dir_files($path, $type);
		$size = 0;
		foreach($files as $f) {
			$size += filesize($path . '/' . $f);
			// Прерываем выполение, если отдаётся больше максимального размера данных
			if(!empty($out['data']) and $size >= $maxsize) break;
			$out['data'][$f] = file_get_contents($path . '/' . $f);
		}
	}

// Данные получены сборщиком, теперь их можно удалять
} elseif($act == 'data_get_confirm') {
	$type  = rq('type');
	$files = rq('files');
	if(!in_array($type, array('clicks', 'postback'))) {
		$out = api_error('Unknown type');
	} else {
		$path = _TRACK_PATH . '/cache/' . $type;
		$files = dir_files($path, $type);
		
		$cnt = 0;
		foreach($files as $f) {
			if(in_array($f, $files)) {
				unlink($path . '/' . $f);
				$cnt++;
			}
		}
		$out['data'] = $cnt;
	}

// Обновление кэша правил
} elseif($act == 'rules_update') {
	$rules_cache = rq('rules');
	$rules_path  = _CACHE_PATH . '/rules';
	
	if (!is_dir($rules_path)) {
		mkdir ($rules_path);
		chmod ($rules_path, 0777);
	}
	
	foreach($rules_cache as $rule_name => $str_rules) {
		$path = $rules_path . '/.' . $rule_name;
		file_put_contents($path, $str_rules, LOCK_EX);
		chmod ($path, 0777);
	}
	
	
	// Удаляем неактуальные кэши
	$files = dir_files($rules_path);
	foreach($files as $f) {
		if(!array_key_exists(substr($f, 1), $rules_cache)) {
			unlink($rules_path . '/' . $f);
		}
	}
	
	$out['data'] = 'success';
	
// Обновление кэша ссылок
} elseif($act == 'links_update') {
	$links_cache = rq('links');
	$outs_path = _CACHE_PATH . '/outs';
	if (!is_dir($outs_path)) {
		mkdir ($outs_path);
		chmod ($outs_path, 0777);
	}
	
	foreach($links_cache as $id => $link) {
		$path = $outs_path . '/.' . $id;
		file_put_contents($path, $link, LOCK_EX);
		chmod ($path, 0777);
	}
	
	
	// Удаляем неактуальные кэши
	$files = dir_files($outs_path);
	foreach($files as $f) {
		if(!array_key_exists(substr($f, 1), $links_cache)) {
			unlink($outs_path . '/' . $f);
		}
	}
	
	$out['data'] = 'success';
}

echo json_encode($out);
?>