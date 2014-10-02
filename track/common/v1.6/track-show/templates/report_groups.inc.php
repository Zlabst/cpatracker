<?php
if (!$include_flag){exit();}
// Меню фильтров для отчёта

extract($var);

global $params;

// Параметры отчёта нужны для формирования ссылок
$params = $var['report_params'];

// Формируем ссылку на группировку
function glink($v, $li = false) {
	global $group_types, $params;
	
	// Если параметр уже есть в фильтре - не показываем этот тип группировки
	if(array_key_exists($v, $params['filter'])) return ''; 
	
	if($li) {
		$class = '';
	} else {
		$class = ' class="btn btn-default'.($v == $params['group_by'] ? ' active' : '').'"';
	}
	$out = '<a href="' . report_lnk($params, array('group_by' => $v)) . '"' . $class . '>' . $group_types[$v][0] . '</a>';
	if($li) $out = '<li>' . $out . '</li>';
	return $out;
}
	


?><div class='row report_grouped_menu'>
<div class='col-md-12'>
	<div class="btn-group">
		<?php
			echo 
				glink('out_id') .
				glink('source_name') .
				glink('campaign_name') .
				glink('ads_name') .
				glink('referer') ;
		?>

		<?php if ($group_by=='out_id'){$class="active";}else{$class='';} ?>
		<!--<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo $subtype;?>&group_by=out_id&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Ссылка</a>-->

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Гео
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<?php 
				echo 
				glink('country', true) .
				glink('state', true) .
				glink('city', true) ;
			echo '<li class="divider"></li>';
			echo glink('isp', true); ?>
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Устройство
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<?php 
				echo 
				glink('user_os', true) .  
				glink('user_platform', true) .
				glink('user_browser', true);
			?>
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Другие параметры
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<?php
				echo 
				glink('campaign_param1', true) .
				glink('campaign_param2', true) .
				glink('campaign_param3', true) .
				glink('campaign_param4', true) .
				glink('campaign_param5', true);
				
				echo '<li class="divider"></li>';
				
				echo 
				glink('click_param_value1', true) .
				glink('click_param_value2', true) .
				glink('click_param_value3', true) .
				glink('click_param_value4', true) .
				glink('click_param_value5', true) .
				glink('click_param_value6', true) .
				glink('click_param_value7', true) .
				glink('click_param_value8', true) .
				glink('click_param_value9', true) .
				glink('click_param_value10', true) .
				glink('click_param_value11', true) .
				glink('click_param_value12', true) .
				glink('click_param_value13', true) .
				glink('click_param_value14', true) .
				glink('click_param_value15', true);
			?>
			</ul>
		</div>
	</div>

</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<div class="row">&nbsp;</div>