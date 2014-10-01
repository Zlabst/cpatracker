<?php
if (!$include_flag){exit();}
// Меню фильтров для отчёта

extract($var);

?><div class='row report_grouped_menu'>
<div class='col-md-12'>
	<div class="btn-group">
		<?php if(!empty($limited_to)) { ?>
		<?php if($subtype == 'out_id') { 
			if ($group_by=='source_name'){$class="active";}else{$class='';}
			?>
			<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=source_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>" >Источник</a>
		<? } ?>
		<?php if($subtype == 'source_name') {
			if ($group_by=='out_id'){$class="active";}else{$class='';}
			?>
			<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=out_id&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>" >Оффер</a>
		<? } } ?>
		<?php if ($group_by=='campaign_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>" >Кампания</a>

		<?php if ($group_by=='ads_name'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=ads_name&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Объявление</a>

		<?php if ($group_by=='referer'){$class="active";}else{$class='';} ?>
		<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=referer&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Площадка</a>

		<?php if ($group_by=='out_id'){$class="active";}else{$class='';} ?>
		<!--<a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo $subtype;?>&group_by=out_id&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>" class="btn btn-default <?php echo $class;?>">Ссылка</a>-->

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Гео
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=country&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Страна</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=city&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Город</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=region&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Регион</a></li>			
			<li class="divider"></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=isp&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Провайдер</a></li>			
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Устройство
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=user_os&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">ОС</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=user_platform&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Платформа</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=user_browser&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Браузер</a></li>
			</ul>
		</div>

		<div class="btn-group">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			Другие параметры
			<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_param1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #1</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_param2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #2</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_param3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #3</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_param4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #4</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=campaign_param5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр ссылки #5</a></li>
			<li class="divider"></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value1&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #1</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value2&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #2</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value3&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #3</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value4&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #4</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value5&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #5</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value6&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #6</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value7&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #7</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value8&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #8</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value9&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #9</a></li>
			<li><a href="?act=reports&type=<?php echo _e($type);?>&subtype=<?php echo _e($subtype);?>&group_by=click_param_value10&limited_to=<?php echo _e($limited_to);?>&from=<?php echo $from?>&to=<?php echo $to?>">Параметр перехода #10</a></li>
			</ul>
		</div>
	</div>

</div> <!-- ./col-md-12 -->
</div> <!-- ./row -->
<div class="row">&nbsp;</div>