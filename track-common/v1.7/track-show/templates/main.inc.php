<?php if (!$include_flag) exit; 

	// Состояние главного меню
	if(empty($_COOKIE['cpa_menu_main'])) {
		$menu_toggle_class = '';
		$menu_sidebar_style = '';
		$menu_icon_class = '';
	} else {
		$menu_toggle_class = ' toggle-left';
		$menu_sidebar_style = ' style="display: none;"';
		$menu_icon_class = ' rotate-180';
	}
?>
<!-- CPA Tracker, http://www.cpatracker.ru -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include (_TRACK_SHOW_COMMON_PATH.'/templates/head.inc.php'); ?>
    </head>

    <body>
        
        <?php include _TRACK_SHOW_COMMON_PATH . "/templates/menu-top.inc.php"; ?>
        <?php
        	$sidebar = _TRACK_SHOW_COMMON_PATH . '/templates/' . (in_array($page_sidebar, $page_sidebar_allowed) ? $page_sidebar : 'sidebar-left.inc.php');
        	include ($sidebar);
            
            //if ($bHideLeftSidebar!==true){$main_container_class='col-sm-9';} else {$main_container_class='col-sm-12';}
        ?>
        <div class="page-content<?php echo $menu_toggle_class;?>">
        	<?php
            	if($_REQUEST['page']!='login') {
                	echo load_plugin('payreminder');
            		echo load_plugin('expiry');
        		}
                if (in_array($page_content, $page_content_allowed)) {
                    include (_TRACK_SHOW_COMMON_PATH.'/pages/'.$page_content);
                }
            ?>
        </div>
        <?php echo tpx('footer'); ?>
    </body>
</html>