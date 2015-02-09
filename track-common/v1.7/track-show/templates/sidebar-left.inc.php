<?php 
	if (!$include_flag){exit();} 
?>
	<div class="sidebar-left">
		<!-- Button sidebar left toggle -->
		<div class="btn-collapse-sidebar-left icon-dynamic"  data-toggle="tooltip" data-placement="bottom" title="Свернуть левое меню"></div>
		
		<ul class="sidebar-menu">
			<li>
				<a class="logo-brand" href="/">
					<span>CPA </span>Tracker
				</a>
			</li>
			<?php
				if ($bHideLeftSidebar !== true and is_array($arr_left_menu) and count($arr_left_menu) > 0) {
					foreach ($arr_left_menu as $cur) {
						$class=($cur['is_active']==1)?'active':'';
				?><li class="<?php echo $class;?>"><a href="<?php echo _e($cur['link']);?>"><?php echo _e($cur['caption']);?></a></li><?php
					
					}
				}
			?>
		</ul>
	</div><!-- END SIDEBAR LEFT -->
<?php
	echo load_plugin('demo', 'demo_well');
?>