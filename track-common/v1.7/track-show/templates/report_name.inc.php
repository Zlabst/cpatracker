<?php
if (!$include_flag) {
    exit();
}
// Заголовок отчёта с названием, временными рамками и кнопками разбиения по времени
?>

<div class="page-heading">
    <div class="header-content">
        <!--Header-->
        <div class="header-report">
            <div class="dropdown date-select">
                <a href="#fakelink" class="dropdown-toggle" role="button">
                    <h2><?php echo $var['report_name']; ?>
                        <?php echo tpx('block_range_' . $var['timestep'], $var); ?>
                    </h2>
                </a>
    			<?php if($var['report_params']['part'] != 'month') { ?>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li>
                        <a class="dropdown-link" href="<?=report_lnk($var['report_params'], array('from' => date('Y-m-d'), 'to' => date('Y-m-d')))?>" onclick="go(this)">Сегодня</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="<?=report_lnk($var['report_params'], array('from' => date('Y-m-d', time() - 86400), 'to' => date('Y-m-d', time() - 86400)))?>" onclick="go(this)">Вчера</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="<?=report_lnk($var['report_params'], array('from' => date('Y-m-d', time() - (86400 * 7)), 'to' => date('Y-m-d', time() - 86400)))?>" onclick="go(this)">Последняя неделя</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="<?=report_lnk($var['report_params'], array('from' => date('Y-m-d', time() - (86400 * 30)), 'to' => date('Y-m-d', time() - 86400)))?>" onclick="go(this)">Последний месяц</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="<?=report_lnk($var['report_params'], array('from' => date('Y-m-d', time() - (86400 * 90)), 'to' => date('Y-m-d', time() - 86400)))?>" onclick="go(this)">Последний квартал</a>
                    </li>
                    <li class="dropdown-footer">
                        <a title="" data-original-title="" href="#" onclick="return false;" class="dropdown-link" data-popover-content="#date-range" data-toggle="range-popover">Свой интервал<i class="cpa cpa-angle-right pull-right"></i></a>
                    </li>
                </ul>
               	<? } ?>

            </div>
        </div>

        <!--Popover content-->
        <div class="hidden" id="date-range">
            <div class="popover-body">
                <div class="input-daterange input-group datepicker">
                    <input class="form-control" name="start" type="text">
                    <span class="input-group-addon">до</span>
                    <input class="form-control" name="end" type="text">
                </div>
                <a href="#" class="btn btn-default btn-block" onclick="return mod_date(this)"><span>Показать выбранные</span></a>			
            </div>
        </div>
		<script>
			function go(obj) {
				window.location = $(obj).attr('href');
			}
			function format_date(d) {
				 tmp = d.split('.');
				 return tmp[2] + '-' + tmp[1] + '-' + tmp[0];
			}
			function mod_date(obj) {
				href = $(obj).attr('href');
				href = modify_link(href, 'from', format_date($(obj).parent().find('input[name=start]').val()));
				href = modify_link(href, 'to', format_date($(obj).parent().find('input[name=end]').val()));
				$(obj).attr('href', href);
				return true;
			}
		</script>
        <!--Breadcrumbs-->
        <?php echo tpx('report_breadcrumbs'); ?>

        <?php echo $var['toolbar']; ?>

    </div><!--Header-content-->		
</div>

<?php
/*
echo '<form method="post" name="datachangeform" id="range_form">
    <div class="pull-left"><h3>' . $var['report_name'] . '</h3></div>
    ' . tpx('block_range_' . $var['timestep'], $var) . '
  </form>
<div class="row"></div>';

echo tpx('report_breadcrumbs');*/