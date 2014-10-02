<?php if (!$include_flag){exit();} ?>

<div class="col-md-3">
	<div class="bs-sidebar hidden-print affix-top">
		<ul class="nav bs-sidenav">
			<li <?php if ($_REQUEST['type']=='basic'){echo 'class="active"';}?>><a href="?act=reports&type=basic">Отчёт по переходам</a></li>      
			<li <?php if ($_REQUEST['type']=='sales'){echo 'class="active"';}?>><a href="?act=reports&type=sales&subtype=daily">Отчет по продажам</a></li>
            <li <?php if ($_REQUEST['type']=='salesreport'){echo 'class="active"';}?>><a href="?act=reports&type=salesreport&subtype=daily">Продажи за период</a></li>
            <li <?php if ($_REQUEST['type']=='targetreport'){echo 'class="active"';}?>><a href="?act=reports&type=targetreport&subtype=daily">Целевые страницы</a></li>
		</ul>
	</div>
</div>