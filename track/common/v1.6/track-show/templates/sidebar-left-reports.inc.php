<? if (!$include_flag){exit();} ?>

<div class="col-md-3">
	<div class="bs-sidebar hidden-print affix-top">
		<ul class="nav bs-sidenav">
			<li <? if ($_REQUEST['type']==''){echo 'class="active"';}?>><a href="?act=">Лента переходов</a></li>
			<li <? if ($_REQUEST['subtype']=='source_name'){echo 'class="active"';}?>><a href="?act=reports&type=all_stats&subtype=source_name">Переходы по источникам</a></li>
			<li <? if ($_REQUEST['subtype']=='out_id'){echo 'class="active"';}?>><a href="?act=reports&type=all_stats&subtype=out_id">Переходы по ссылкам</a></li>      
			<li <? if ($_REQUEST['type']=='sales'){echo 'class="active"';}?>><a href="?act=reports&type=sales&subtype=daily">Отчет по продажам</a></li>
            <li <? if ($_REQUEST['type']=='salesreport'){echo 'class="active"';}?>><a href="?act=reports&type=salesreport&subtype=daily">Продажи за период</a></li>
            <li <? if ($_REQUEST['type']=='targetreport'){echo 'class="active"';}?>><a href="?act=reports&type=targetreport&subtype=daily">Целевые страницы</a></li>
		</ul>
	</div>
</div>