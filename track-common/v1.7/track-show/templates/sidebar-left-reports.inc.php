<?php
if (!$include_flag)
    exit;

if ($_REQUEST['act'] != 'reports') {
    $reports_lnk = '?act=reports&type=basic';
    $reports_lnk_lp = '?act=reports&type=basic&mode=lp';
} else {
    $params = report_options();
    $reports_lnk = report_lnk($params, array('filter_str' => array(), 'mode' => '', 'type' => 'basic', 'part' => 'all', 'col' => 'act', 'conv' => 'all', 'group_by' => 'out_id'));
    $reports_lnk_lp = report_lnk($params, array('filter_str' => array(), 'mode' => 'lp', 'type' => 'basic', 'part' => 'all', 'col' => 'act', 'conv' => 'all', 'group_by' => 'out_id'));
}
?>
<div class="sidebar-left<?php echo $menu_toggle_class; ?>">
    <!-- Button sidebar left toggle -->
    <div class="btn-collapse-sidebar-left icon-dynamic<?php echo $menu_icon_class; ?>"  data-toggle="tooltip" data-placement="bottom" title="Свернуть левое меню"></div>
    <ul class="sidebar-menu"<?php echo $menu_sidebar_style; ?>>
	<li>
	    <a class="logo-brand" href="<?php echo _HTML_ROOT_PATH; ?>">
		<span>CPA </span>Tracker
	    </a>
	</li>
	<?php
		if($bHideLeftSidebar !== true) {
		$active = ($_REQUEST['type'] == 'basic' and $_REQUEST['mode'] != 'lp' and $_REQUEST['mode'] != 'lp_offers');
	?>
	<li <?php if ($active) { echo 'class="active"'; } ?>><a href="<?php echo $reports_lnk; ?>">Переходы</a>
            <ul id="submenu_all_offers" class="submenu" <?php if($active) { ?>style="display: block;"<?php } ?>>
    		<?php echo type_subpanel2($params, 'basic', ''); ?>
            </ul>
        </li>
	
        <?php
            $active = ($_REQUEST['type'] == 'sales');
            $subtype = rq('subtype');
        ?>
        <li <?php if ($active) { echo 'class="active"'; } ?>><a href="?act=reports&type=sales&subtype=daily">Продажи</a>
           <ul class="submenu" <?php if($active) { ?>style="display: block;"<?php } ?>>
               <li <? if($subtype == 'daily') { echo 'class="active"';} ?>><a href="?act=reports&type=sales&subtype=daily">По дням</a></li>
               <li <? if($subtype == 'monthly') { echo 'class="active"';} ?>><a href="?act=reports&type=sales&subtype=monthly">По месяцам</a></li>
           </ul>
        </li>
        
        <?php
            $active = ($_REQUEST['mode'] == 'lp' or $_REQUEST['mode'] == 'lp_offers');
        ?>
        <li <?php if ($active) { echo 'class="active"'; } ?>><a href="<?php echo $reports_lnk_lp; ?>">Целевые страницы</a>
            <ul class="submenu" <?php if($active) { ?>style="display: block;"<?php } ?>>
    		<?php echo type_subpanel2($params, 'basic', 'lp'); ?>
            </ul>
        </li>
       	<?php } ?>
    </ul>
<?php
echo load_plugin('demo', 'demo_well');
?>
</div>