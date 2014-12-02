<?php

include _TRACK_SHOW_PATH . "/functions_general.php"; 

function api_get_files($url) {
	foreach(array('clicks', 'postback') as $type) {
		$url_params = $url['path'] . '/api.php?act=data_get&type=' . $type. '&key=' . $url['key'];
		$files = json_decode(file_get_contents($url_params), true);
		
		foreach($files['data'] as $f => $data) {
			$path = _CACHE_PATH . '/' . $type . '/' . $f;
			
			if(!file_exists($path)) {
				$fp = fopen($path, 'w');
				if($fp && fwrite($fp, $data) && fclose($fp)) {
					$url_params = $url['path'] . '/api.php?act=data_get_confirm&type=' . $type. '&key=' . $url['key'];
					file_get_contents($url_params);
				}
			}
		}
	}
}

foreach($tracklist as $n => $track) {
	// Удаленный трекер
	if(substr($track['path'], 0, 5) == 'http:') {
		
		$files = api_get_files($track);
		
	// Локальный трекер
	} else {
		foreach(array('clicks', 'postback') as $type) {
			$files = dir_files($track['path'] . '/cache/' . $type, $type);
			foreach($files as $f) {
				//echo $f . '<br />';
				rename($track['path'] . '/cache/' . $type . '/' . $f, _CACHE_PATH . '/' . $type . '/' . $f . '_' . $n);
			}
		}
	}
}
?>