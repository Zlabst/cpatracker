<?php
if (!$include_flag) {
    exit();
}

$status = rq('status');
$status = ($status == 'all') ? -1 : 0; // все или только непрочитанные
$offset = rq('offset', 2);
$ntf = array();

// Глобобальные системные сообщения. Удаляются только системой
$global_ntf_cnt = count($global_notifications);

// Пользовательские уведомления, могут отмечаться как прочитанные
list($user_ntf_cnt, $user_ntf_unread_cnt, $user_ntf_arr) = user_notifications($status, $offset);

// Добавляем глобальные сообщения к общим счётчикам
if ($global_ntf_cnt > 0) {
    $user_ntf_cnt += $global_ntf_cnt;
    $user_ntf_unread_cnt += $global_ntf_cnt;
}

// Добавляем глобальные уведомления
if ($global_ntf_cnt > 0) {
    $i = 0;
    foreach ($global_notifications as $cur) {
        $i++;
        switch ($cur) {
            case 'CRONTAB_CLICKS_NOT_INSTALLED':
                $process_clicks_path = realpath(_TRACK_SHOW_PATH . '/process_clicks.php');
                $title = 'Статистика переходов не обновляется';
                $description = '<p>Добавьте в cron запуск следующего файла:<br /><code>' . $process_clicks_path . '</code>,<br /> с интервалом в одну минуту.</p><p>Данный скрипт отвечает за импорт данных о переходах в базу данных.</p><p>Строка запуска может выглядеть примерно так:</p><p><code>*/1 * * * * /usr/bin/php5 /var/www/cpatracker.ru/track-show/process_clicks.php &gt;/dev/null</code></p><p>Для редактирования cron файла используйте панель управления сервером или команду "crontab -e" из консоли.</p><p>После первого успешного обновления статистики данное сообщение исчезнет. При возникновении проблем обратитесь в службу технической поддержки вашего хостинга.</p>';
                break;

            case 'CRONTAB_POSTBACK_NOT_INSTALLED':
                $process_postback_path = realpath(_TRACK_SHOW_PATH . '/process_postback.php');
                $title = 'Статистика продаж не обновляется';
                $description = '<p>Добавьте в cron запуск следующего файла:<br /><code>' . $process_postback_path . '</code>,<br /> с интервалом в одну минуту.</p><p>Данный скрипт отвечает за импорт данных о продажах в базу данных.</p><p>Строка запуска может выглядеть примерно так:</p><p><code>*/1 * * * * /usr/bin/php5 /var/www/cpatracker.ru/track-show/process_postback.php &gt;/dev/null</code></p><p>Для редактирования cron файла используйте панель управления сервером или команду "crontab -e" из консоли.</p><p>После первого успешного обновления данных о продажах данное сообщение исчезнет. При возникновении проблем обратитесь в службу технической поддержки вашего хостинга.</p>';
                break;

            case 'API_CONNECT_ERROR':
                $title = 'Произошла ошибка связи во время синхронизации с трекером';
                $description = '<p>Не удалось получить правильый ответ при отправке данных на трекер. Проверьте, пожалуйста, доступность удалённых трекеров и выполните синхронизацию вручную.<br /><a href="?ajax_act=sync_slaves" class="btn btn-default">Выполнить синхронизацию</a>.</p>';
                break;

            default:
                continue;
                break;
        }
        $ntf[] = array(
            'title' => $title,
            'text' => $description,
        );
    }
}
$ntf = array_merge($ntf, $user_ntf_arr);

$assign = array(
    'cnt' => $user_ntf_cnt,
    'cnt_system' => $global_ntf_cnt,
    'cnt_unread' => $user_ntf_unread_cnt,
    'status' => $status,
    'notifications' => $ntf,
);

echo tpx('notifications', $assign);
?>