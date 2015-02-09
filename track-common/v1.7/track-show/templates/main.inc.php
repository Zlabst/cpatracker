<?php if (!$include_flag){exit();} ?>
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
        <div class="page-content">
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