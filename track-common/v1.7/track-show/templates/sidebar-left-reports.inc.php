<?php if (!$include_flag) exit; 
	
	if($_REQUEST['act'] != 'reports') {
		$reports_lnk = '?act=reports&type=basic';
		$reports_lnk_lp = '?act=reports&type=basic&mode=lp';
	} else {
		$params = report_options();
		$reports_lnk = report_lnk($params, array('filter_str' => array(), 'mode' => '', 'type' => 'basic', 'part' => 'all', 'col' => 'act', 'conv' => 'all', 'group_by' => 'out_id'));
		$reports_lnk_lp = report_lnk($params, array('filter_str' => array(), 'mode' => 'lp', 'type' => 'basic', 'part' => 'all', 'col' => 'act', 'conv' => 'all', 'group_by' => 'out_id'));
	}
?>
<div class="sidebar-left<?php echo $menu_toggle_class;?>">
	<!-- Button sidebar left toggle -->
	<div class="btn-collapse-sidebar-left icon-dynamic<?php echo $menu_icon_class;?>"  data-toggle="tooltip" data-placement="bottom" title="Свернуть левое меню"></div>
	<ul class="sidebar-menu"<?php echo $menu_sidebar_style;?>>
		<li>
			<a class="logo-brand" href="<?php echo _HTML_ROOT_PATH; ?>">
				<span>CPA </span>Tracker
			</a>
		</li>
		<li <?php if ($_REQUEST['type']=='basic' and $_REQUEST['mode']!='lp' and $_REQUEST['mode']!='lp_offers'){echo 'class="active"';}?>><a href="<?php echo $reports_lnk; ?>">Отчёт по переходам</a></li>      
			<li <?php if ($_REQUEST['type']=='sales'){echo 'class="active"';}?>><a href="?act=reports&type=sales&subtype=daily">Отчёт по продажам</a></li>
            <li <?php if ($_REQUEST['mode']=='lp' or $_REQUEST['mode']=='lp_offers') {echo 'class="active"';}?>><a href="<?php echo $reports_lnk_lp; ?>">Отчёт по целевым страницам</a></li>
	</ul>
	<?php 
		echo load_plugin('demo', 'demo_well');
	?>
</div>
<!--
<div class="col-md-3">
	<div class="bs-sidebar hidden-print affix-top">
		<ul class="nav bs-sidenav">
			<li <?php if ($_REQUEST['type']=='basic' and $_REQUEST['mode']!='lp' and $_REQUEST['mode']!='lp_offers'){echo 'class="active"';}?>><a href="<?php echo $reports_lnk; ?>">Отчёт по переходам</a></li>      
			<li <?php if ($_REQUEST['type']=='sales'){echo 'class="active"';}?>><a href="?act=reports&type=sales&subtype=daily">Отчёт по продажам</a></li>
            <li <?php if ($_REQUEST['mode']=='lp' or $_REQUEST['mode']=='lp_offers') {echo 'class="active"';}?>><a href="<?php echo $reports_lnk_lp; ?>">Отчёт по целевым страницам</a></li>
		</ul>
	</div>
	<?php 
		echo load_plugin('demo', 'demo_well');
	?>
</div>-->