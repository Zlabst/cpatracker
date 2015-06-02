<?php

require _TRACK_COMMON_PATH . '/functions.php';

$act = rq('act');
$track_key = rq('key');

if (get_magic_quotes_gpc()) {
    $_REQUEST = stripslashes2($_REQUEST);
}
/*
  if($act != 'ping') {
  dmp($_REQUEST);
  } */
  
 

$out = array(
    'status' => 1, // Всё хорошо
    'data' => array(),
);

function api_error($error = '') {
    $out = array(
        'status' => 0,
        'error' => $error,
    );
    return $out;
}

// Проверяем ключ, если только это не ping
if ($track_key != _SELF_TRACK_KEY and $act != 'ping') {
    $out = api_error('Invalid track key');
    echo json_encode($out);
    exit;
}

$maxsize = 10000; // максимальный размер отдаваемых данных
// Получение данных
if ($act == 'data_get') {
    $type = rq('type');
    if (!in_array($type, array('clicks', 'postback'))) {
        $out = api_error('Unknown type');
    } else {
        $path = _TRACK_PATH . '/cache/' . $type;
        $files = dir_files($path, $type);
        $size = 0;
        foreach ($files as $f) {
            $size += filesize($path . '/' . $f);
            // Прерываем выполение, если отдаётся больше максимального размера данных
            if (!empty($out['data']) and $size >= $maxsize)
                break;
            $out['data'][$f] = iconv('cp1251', 'utf8', file_get_contents($path . '/' . $f));
        }
    }

// Данные получены сборщиком, теперь их можно удалять
} elseif ($act == 'data_get_confirm') {
    $type = rq('type');
    $confirm_files = explode(',', rq('file'));
    if (!in_array($type, array('clicks', 'postback'))) {
        $out = api_error('Unknown type');
    } else {
        $path = _TRACK_PATH . '/cache/' . $type;
        $files = dir_files($path, $type);

        $cnt = 0;
        foreach ($files as $f) {
            if (in_array($f, $confirm_files)) {
                // unlink($path . '/' . $f);
                rename($path . '/' . $f, $path . '/' . $f . '*');
                $cnt++;
            }
        }
        $out['data'] = $cnt;
    }

// Обновление кэша
} elseif ($act == 'outs_update' or $act == 'rules_update') {
	//dmp($_REQUEST);
    $cache = rq('cache');
    $full = rq('full', 2); // признак получения полныго списка кэша. Чего в нём нет - быть не должно и на диске
    $errors = array();
    
    $type = str_replace('_update', '', $act);
    $cache_path = _CACHE_PATH . '/' . $type;

    $masks = array(
        'outs' => '/^\d+$/',
        'rules' => '/^[0-9a-f]{32}$/'
    );

    

    if (!is_dir($cache_path)) {
        mkdir($cache_path);
        chmod($cache_path, 0777);
    }
    /*
    dmp($act);
    dmp($full);
    dmp($cache);
	*/
    foreach ($cache as $id => $content) {
        if (!preg_match($masks[$type], $id)) {
            $errors[] = 'Неверное имя кэша ' . $id;
            break;
        }

        $path = $cache_path . '/.' . $id;
        if ($content == '') {
            unlink($path);
            if (file_exists($path)) {
                $errors[] = 'Ошибка удаления файла ' . $path;
            }
        } else {
            if (file_put_contents($path, $content, LOCK_EX)) {
                chmod($path, 0777);
            } else {
                $errors[] = 'Ошибка записи в файл ' . $path;
            }
        }
    }

    // Удаляем неактуальные кэши, если это был полный список
    if ($full) {
        $files = dir_files($cache_path);
        foreach ($files as $f) {
            if (!array_key_exists(substr($f, 1), $cache)) {
                unlink($cache_path . '/' . $f);
            }
        }
    }

    if (!empty($errors)) {
        $out = api_error(join("\n", $errors));
    } else {
        $out['data'] = 'success';
    }
} elseif ($act == 'get_status') {
    $out['data'] = array(
        'time' => date('Y-m-d H:i:s'),
        'unix_time' => time(),
        'timezone' => date_default_timezone_get(),
    );
} elseif ($act != 'ping') {
    $errors[] = 'Неизвестный метод ' . $act;
    $out = api_error(join("\n", $errors));
}


echo json_encode($out);
?>