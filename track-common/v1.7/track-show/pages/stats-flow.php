<?php
if (!$include_flag) {
    exit();
}
?>
<link rel="stylesheet" href="<?php echo _HTML_LIB_PATH; ?>/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/css/bootstrap-datepicker3.min.css" >
<script src="<?php echo _HTML_TEMPLATE_PATH; ?>/js/report_toolbar.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/bootstrap/plugins/bootstrap-datepicker-1.4.0-dist/locales/bootstrap-datepicker.ru.min.js"></script>
<style>
    .sortdata {
        display: none;
    }
</style>
<?php
include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

// Default stats-flow values
$report_date=($_REQUEST['date'])?$_REQUEST['date']:get_current_day();
$arr_default_values=array('range_type'=>'hourly', 'main_column'=>'source_name', 'date_start'=>$report_date, 'date_end'=>$report_date);
$arr_report_data=prepare_report($arr_default_values);
$arr_report_data['hide-table-footer']=true;
echo $mTemplate->render('report-table-transitions', $arr_report_data);



$date = rq('date', 4, get_current_day());
$hour = rq('hour', 2);
$prev_date = date('Y-m-d', strtotime('-1 days', strtotime($date)));
$next_date = date('Y-m-d', strtotime('+1 days', strtotime($date)));

// Кнопки панели управления
$group_actions = array(
    'act' => array('cnt_act')
);

$main_type = rq('report_type', 0, 'source_name');
$limited_to = '';

$params = array(
    'type' => 'basic',
    'part' => 'hour',
    'filter' => array(),
    'group_by' => $main_type,
    'subgroup_by' => $main_type,
    'conv' => 'all',
    'mode' => '',
    'col' => 'sale_lead',
    'from' => $date,
    'to' => $date,
    'cache' => ((_CLICKS_SPOT_SIZE > 0 and empty($_GET['nocache'])) ? 1 : 0)
);

$arr_report_data = get_clicks_report_grouped2($params);


$arr_hourly = array();

foreach ($arr_report_data['data'] as $row_name => $row_data)
{
    foreach ($row_data as $cur_hour => $data)
    {
        $arr_hourly[$row_name][$cur_hour] = get_clicks_report_element2($data, true, false, $group_actions);
    }
}

?>
<!-- Page heading -->
<div class="page-heading">
    <div class="header-content">

        <!--Header-->
        <div class="row">
            <div class="col-md-4">
                <?php
                if (1) {
                    echo '<a class="btn btn-link no-side-padding " href="?date=' . $prev_date . '&report_type=' . $main_type . '"><i class="cpa cpa-angle-left"></i><span>' . mysqldate2string($prev_date) . '</span></a>';
                }
                ?>
            </div>

            <div class="col-md-4 text-center">
                <a class="current-date" href="#" id="datepicker-single" onclick="return false;">
                    <h2><span><?php echo mysqldate2string($date); ?></span></h2>
                </a>

            </div>
            <div class="col-md-4">
                <?php
                if ($date != get_current_day()) {
                    echo '<a class="btn btn-link no-side-padding pull-right" href="?date=' . $next_date . '&report_type=' . $main_type . '"><span>' . mysqldate2string($next_date) . '</span><i class="cpa cpa-angle-right"></i></a>';
                }
                ?>
            </div>
        </div>
    </div><!--Header-content-->		
</div><!--page-heading-->

<!-- Table -->
<div class="table-report-box">
    <table class="table table-striped table-transitions">
        <thead>
            <tr>
                <th class="selected">Источник</th>
                <?php
                for ($i = 0; $i < 24; $i++) {
                    echo '<th>' . sprintf('%02d', $i) . '</td>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            echo "<tr>";

            foreach ($arr_hourly as $source_name => $data)
            {
                $source_name_link=($source_name=='')?'{empty}':$source_name;
                echo '<td><a href="?filter_by=source_name&value=' . $source_name_link . '&date=' . $date . '">' . _e(param_val($source_name, $main_type)) . '</a></td>';

                if ($main_type == 'source_name')
                {
                    $source_name_lnk = param_key($source_name, 'source_name');
                }
                else
                {
                    $source_name_lnk = '';
                }

                for ($i = 0; $i < 24; $i++)
                {
                    $i2 = sprintf('%02d', $i);
                    if ($data[$i2] != '')
                    {
                        echo "<td><a style='text-decoration:none; color:black;' href='?filter_by=hour&source_name=" . _e($source_name_lnk) . "&date=$date&hour=$i'>{$data[$i2]}</a></td>";
                    }
                    else
                    {
                        echo "<td></td>";
                    }
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div><!-- /table-report-box -->
<?php
$panels = array(
    'act' => 'Все действия',
    'sale' => 'Продажи',
    'lead' => 'Лиды'
);
?>

<!--Bottom Buttons-->
<div class="pagination">
    <div role="toolbar" class="btn-toolbar pull-right"></div>
</div>


<!--Search-->
<form class="search" action="" method="get" id="flow_search_from">
    <div class="input-group">
        <div class="form-group has-feedback ">
            <input type="hidden" name="filter_by" value="search"/>
            <input type="hidden" name="date" value="<?php echo $date; ?>"/>
            <input type="text" class="form-control" name="search" value="<?php echo _e($search); ?>">
            <a class="form-control-feedback" href="#" onclick="$('#flow_search_from').submit(); return false;" type="submit">
                <i class="cpa cpa-magnifier "></i>
            </a>
        </div>
        <div class="input-group-btn"><a class="btn btn-default" href="?csrfkey=<?php echo _e(CSRF_KEY) . "&ajax_act=excel_export&date=" . _e($date); ?>"><i class="cpa cpa-xlsx"></i></a></div>
    </div>
</form>

<input type="hidden" id="usd_selected" value="1">
<input type="hidden" id="type_selected" value="clicks">
<input type="hidden" id="sales_selected" value="0">

<?php
if (!empty($arr_data)) {
    ?>

    <!-- Table -->
    <div class="link-list-box">
        <table class="table table-striped table-link-list-alt" id="stats-flow">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Ссылка</th>
                    <th>Источник</th>
                    <th>Кампания</th>
                    <th></th>
                    <th>Параметры</th>
                </tr>
            </thead>

            <tbody>
                <?php
                echo tpx('stats-flow-rows', array('data' => $arr_data));
                ?>
            </tbody>
        </table>
    </div>

    <?php
    if ($more) {
        echo '<div class="show-more">
			<a class="btn btn-link" href="#" onclick="return load_flow(this)">
				<i class="cpa cpa-angle-down"></i>
				<span>Показать больше</span>
				<i class="cpa cpa-angle-down"></i>
			</a>
                        <input type="hidden" id="start" value="20">
                        <input type="hidden" id="start_s" value="0">
		</div>';
        ?>
        <script type="text/javascript">
            function load_flow(obj) {
                $.post(
                'index.php?ajax_act=a_load_flow', {
                    start: $('#start').val() ,
                    start_s: $('#start_s').val() ,
                    date: '<?php echo _str($date) ?>',
                    hour: '<?php echo _str($hour) ?>',
                    filter_by: '<?php echo _str($_REQUEST['filter_by']) ?>',
                    value: '<?php echo _str($_REQUEST['value']) ?>',
                    source_name: '<?php echo _str($_REQUEST['source_name']) ?>'
                }
            ).done(
                function(data) {
                    response = eval('(' + data + ')');
                    $('#start').val(response.start);
                    $('#start_s').val(response.start_s);
                    if(!response.more) $(obj).hide();
                    $('#stats-flow tbody').first().append(response.data);
                }
            ); 
                return false;
            }	
        </script><?php
    }
}
?>
<script>
    // show_conv_mode('act', 0);
    update_stats2('cnt_act', false); 
    show_currency('usd');
</script>