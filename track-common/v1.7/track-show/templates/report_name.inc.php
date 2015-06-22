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
                        <!--<span class="amid">за</span>
                        <span class="date">19.01.2015</span>
                        <span class="amid">—</span>
                        <span class="date">19.01.2015</span>
                        <i class="cpa cpa-angle-down"></i>-->
                    </h2>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li>
                        <a class="dropdown-link" href="#">Сегодня</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="#">Вчера</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="#">Последняя неделя</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="#">Последний месяц</a>
                    </li>
                    <li>
                        <a class="dropdown-link" href="#">Последний квартал</a>
                    </li>
                    <li class="dropdown-footer">
                        <a title="" data-original-title="" href="#fakelink" class="dropdown-link" data-popover-content="#date-range" data-toggle="range-popover">Свой интервал<i class="cpa cpa-angle-right pull-right"></i></a>
                    </li>
                </ul>

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
                <a href="#fakelink" class="btn btn-default btn-block"><span>Показать выбранные</span></a>			
            </div>
        </div>

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