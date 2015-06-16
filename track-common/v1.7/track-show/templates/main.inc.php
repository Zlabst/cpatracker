<?php
if (!$include_flag)
    exit;

// Состояние главного меню
if (empty($_COOKIE['cpa_menu_main'])) {
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

        if ($_REQUEST['page'] != 'login') {
            // Меню сверху
            include _TRACK_SHOW_COMMON_PATH . "/templates/menu-top.inc.php";
            
            // Меню слева
            $sidebar = _TRACK_SHOW_COMMON_PATH . '/templates/' . (in_array($page_sidebar, $page_sidebar_allowed) ? $page_sidebar : 'sidebar-left.inc.php');
            include $sidebar;
            
            echo '<div class="page-content' . $menu_toggle_class.'">';
        }
        ?>
        
            <?php
            if ($_REQUEST['page'] != 'login') {
                echo load_plugin('payreminder');
                echo load_plugin('expiry');
            }
            echo $main_content;
		if ($_REQUEST['page'] != 'login') { ?>
        	</div>
        <? } ?>
        <?php echo tpx('footer'); ?>
        <?php
        	if(!empty($_GET['debug'])) {
        		echo '<pre style="margin-left: 270px;">';
        		foreach($sql_log as $q)	{
        			echo $q.'<br>';
        		}
        		echo 'Total: <b>' .$sql_time. '</b>';
        		echo '</pre>';
        	}
        ?> 
    </body>
    	
</html>