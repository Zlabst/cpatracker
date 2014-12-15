<?php
	define('_TRACK_VER',           'v1.6');

	define('_TRACK_PATH',          dirname (__FILE__));
	define('_TRACK_SETTINGS_PATH', _TRACK_PATH . '/cache');
	define('_TRACK_COMMON_PATH',   dirname (__FILE__) . '/../track-common/' . _TRACK_VER . '/track');
	define('_TRACK_STATIC_PATH',   dirname (__FILE__) . '/../track-common/static');

	define('_TRACK_LIB_PATH',      _TRACK_COMMON_PATH . '/lib');
	define('_CACHE_PATH',          _TRACK_PATH . '/cache');
	define('_TRACK_SHOW_COMMON_PATH', dirname (__FILE__) . '/../track-common/' . _TRACK_VER . '/track-show');

	$html_delta_path = explode('/',substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT'])));
	$delta_uri = join('/', array_slice($html_delta_path, 0, count($html_delta_path) - 2));

$s = (empty($_SERVER["HTTPS"]) && empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? '' : ((!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") || $_SERVER['HTTP_X_FORWARDED_PROTO']=='https' ) ? "s" : "";
	$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	$uri_root = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port;
	
	define('_HTML_ROOT_PATH',      $uri_root . $delta_uri);
	define('_HTML_TRACK_PATH',     $uri_root . $delta_uri . '/track');
	
	define('_SELF_TRACK_KEY',      'key123');
?>