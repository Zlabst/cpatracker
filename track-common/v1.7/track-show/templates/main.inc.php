<?php
if (!$include_flag)
    exit;

// Состояние главного меню
if (empty($_COOKIE['cpa_menu_main']) or $page_content == 'stats-flow.php') {
    $menu_toggle_class = '';
    $menu_sidebar_style = '';
    $menu_icon_class = '';
} else {
    $menu_toggle_class = ' toggle-left';
    $menu_sidebar_style = ' style="display: none;"';
    $menu_icon_class = ' rotate-180';
}

if($page_content == 'stats-flow.php') {
	$menu_toggle_class = ' no-sidebar';
	$bHideLeftSidebar = true;
}

$is_authorized = (!in_array($_REQUEST['page'], $open_pages) and $settings[0] and $auth_info[1] != 'register_new');
?>
<!-- CPA Tracker, http://www.cpatracker.ru -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include (_TRACK_SHOW_COMMON_PATH . '/templates/head.inc.php'); ?>
    </head>
    <body>
        <?php
        // Переносим расчётную часть выше, чтобы на момент показа меню у нас были все данные
        if (in_array($page_content, $page_content_allowed)) {
            ob_start();
            include (_TRACK_SHOW_COMMON_PATH . '/pages/' . $page_content);
            $main_content = ob_get_contents();
            ob_end_clean();
        } else {
            $main_content = '';
        }

        if ($is_authorized)
        {
            // Меню сверху
            include _TRACK_SHOW_COMMON_PATH . "/templates/menu-top.inc.php";
            
            // Меню слева
            $sidebar = _TRACK_SHOW_COMMON_PATH . '/templates/' . (in_array($page_sidebar, $page_sidebar_allowed) ? $page_sidebar : 'sidebar-left.inc.php');
            include $sidebar;
            
            echo '<div class="page-content' . $menu_toggle_class.'">';
        }
        ?>
        
        <?php
            if ($is_authorized)
            {
                echo load_plugin('payreminder');
                echo load_plugin('expiry');
            }
            echo $main_content;
		    if ($is_authorized) {
        	   echo '</div>';
            } 
        ?>
        <?php echo tpx('footer'); ?>
    </body>
</html>
