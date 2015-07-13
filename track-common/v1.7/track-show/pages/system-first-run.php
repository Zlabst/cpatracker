<?php

if (!$include_flag)
    exit;

// Настройки БД существуют
if ($settings[0]) {

    // Запрашиваем данные админа
    if (!$auth_info[0] and $auth_info[1] == 'register_new') {
        echo tpx('install_register_admin');
    } 
} else {
    // Настройки надо спросить у юзера и сохранить
    if ($settings[1] == 'cache_not_writable') {

        // Рассказываем, как открыть на запись папку кэша
        echo tpx('install_cache_not_writable');
    } else {

        // Ввод данных для подключения к базе
        echo tpx('install_connect_db');
    }
}
?>