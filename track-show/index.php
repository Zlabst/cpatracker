<?
	define('_TRACK_SHOW_PATH', dirname (__FILE__)."/../../common/v1.6/track-show");

	// Replace only last occurrence of track-show, to allow track-show in path
	define('_TRACK_PATH', strrev(preg_replace(strrev('/track-show/'), strrev('track'), strrev(dirname (__FILE__)), 1)));	

	define('_CACHE_PATH', _TRACK_PATH.'/cache');

	define('_TRACK_COMMON_PATH', dirname (__FILE__)."/../../common/v1.6/track");
	define('_TRACK_LIB_PATH', _TRACK_COMMON_PATH."/lib");

	define('_HTML_LIB_PATH', "../../common/v1.6/track-show/lib");
	define('_HTML_TEMPLATE_PATH', "../../common/v1.6/track-show/templates");

	// Get full HTML url
    $s = (empty($_SERVER["HTTPS"]) && empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? '' : ($_SERVER["HTTPS"] == "on" || $_SERVER['HTTP_X_FORWARDED_PROTO']=='https' ) ? "s" : "";
	$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	$uri = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	$segments = explode('?', $uri, 2);
	$url = $segments[0];

	define('_HTML_ROOT_PATH', rtrim(str_replace ('index.php', '', $url), '/'));
	
	define('_HTML_TRACK_PATH', strrev(preg_replace(strrev('/track-show/'), strrev('track'), strrev(_HTML_ROOT_PATH), 1)));	

	include _TRACK_SHOW_PATH."/index.php";
?>