<?php
if (!$include_flag) {
    exit();
}
?>
<link href="<?php echo _HTML_LIB_PATH; ?>/select2/select2.css" rel="stylesheet"/>


<script src="<?php echo _HTML_LIB_PATH; ?>/select2/select2.js"></script>

<!-- Include Required Prerequisites -->
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

<div class="page-heading">
    <div class="header-content">
        <h2>Добавление затрат</h2>
    </div>
</div>

<script>
    function add_costs()
    {		
        if ($('input[name=date_range]', '#add_costs').val()=='')
        {
            $('input[name=date_range]', '#add_costs').css('background-color', 'lightyellow');	
            return false;
        }

        if ($('input[name=costs_value]', '#add_costs').val()=='' || $('input[name=costs_value]', '#add_costs').val()==0)
        {
            $('input[name=costs_value]', '#add_costs').css('background-color', 'lightyellow');	
            return false;
        }
			
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: $('#add_costs').serialize()
        }).done(function( msg ) 
        {
            $('input[name=costs_value]', '#add_costs').val('');
            $('#status_icon').removeClass('hidden');
            $('#status_icon').fadeIn(100).fadeOut(1200);

            return false;
        });
        return false;
    }

    function change_currency(currency)
    {
        var currency_name=''; var currency_code='';
        switch (currency)
        {		
            case 'rub': 
                currency_name='руб.';
                currency_code='rub';
                break;		
            case 'usd': 
                currency_name='долл.';
                currency_code='usd';
                break;
            case 'uah': 
                currency_name='грн.';
                currency_code='uah';
                break;				
        }
        $('#currency_selected').html(currency_name+'&nbsp;&nbsp;<span class="caret"></span>');
        $('#currency_code').val(currency_code);
        return false;
    }

    $(document).ready(function() 
    {
        // $(".select2").select2();

        $('input[name="date_range"]').daterangepicker(
            {
                "autoApply": true,
                locale: {
                    format: 'DD.MM.YYYY',
                    "firstDay": 1,
                    "daysOfWeek": [
                        "Вс",
                        "Пн",
                        "Вт",
                        "Ср",
                        "Чт",
                        "Пт",
                        "Сб"
                    ],
                    "monthNames": [
                        "Январь",
                        "Февраль",
                        "Март",
                        "Апрель",
                        "Май",
                        "Июнь",
                        "Июль",
                        "Август",
                        "Сентябрь",
                        "Октябрь",
                        "Ноябрь",
                        "Декабрь"
                    ],
                },

            }
        );

/*
        $('#demo').daterangepicker({
            "autoApply": true,
            "ranges": {
                "Сегодня": [
                    "2015-09-21T22:44:24.818Z",
                    "2015-09-21T22:44:24.818Z"
                ],
                "Вчера": [
                    "2015-09-20T22:44:24.819Z",
                    "2015-09-20T22:44:24.819Z"
                ],
                "За последние 7 дней": [
                    "2015-09-15T22:44:24.819Z",
                    "2015-09-21T22:44:24.819Z"
                ],
                "Last 30 Days": [
                    "2015-08-23T22:44:24.819Z",
                    "2015-09-21T22:44:24.819Z"
                ],
                "This Month": [
                    "2015-08-31T21:00:00.000Z",
                    "2015-09-30T20:59:59.999Z"
                ],
                "Last Month": [
                    "2015-07-31T21:00:00.000Z",
                    "2015-08-31T20:59:59.999Z"
                ]
            },
            "locale": {
                "format": "MM/DD/YYYY",
                "separator": " - ",
                "applyLabel": "Apply",
                "cancelLabel": "Cancel",
                "fromLabel": "From",
                "toLabel": "To",
                "customRangeLabel": "Custom",
                "daysOfWeek": [
                    "Su",
                    "Mo",
                    "Tu",
                    "We",
                    "Th",
                    "Fr",
                    "Sa"
                ],
                "monthNames": [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ],
                "firstDay": 1
            },
            "startDate": "09/16/2015",
            "endDate": "09/22/2015"
        }, function(start, end, label) {
            console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
        });
        /*
        $('input[name="date_range"]', $('#add_costs')).focus (function (e) {
            $('input[name="date_range"]', $('#add_costs')).css('background-color', 'white'); 
        });        

        $('input[name="costs_value"]', $('#add_costs')).focus (function (e) {
            $('input[name="costs_value"]', $('#add_costs')).css('background-color', 'white'); 
        });
        */

    });
</script>


<div class="form-group" id="sale_amount">
    <label>Период</label>
    <div class="row">
        <div class="col-xs-6">
            <div class="input-group">
                <input type="text" class="form-control" name="date_range" required="">
            </div><!-- /input-group -->
        </div><!-- /.col-lg-6 -->
    </div><!-- /.row -->
</div>

<form role="form" method="post" id="add_costs" onsubmit="return add_costs();">
    <input type='hidden' name='ajax_act' value='add_costs'>
    <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
    <input type='hidden' id='currency_code' name='currency_code' value='rub'>
    <div class="form-group">
        <label>Период</label>
        <div class="row">
            <div class="input-group col-xs-6">
                <input type="text" name="date_range" class="form-control">
                <span class="input-group-addon"><i class='fa fa-calendar'></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Источник</label>
        <div class="row">
            <div class="col-xs-6">
                <select class="select2" style="width:100%" name="source_name">
                    <?php
                    foreach ($arr_sources as $cur) {
                        echo "<option value='" . _e($cur['source_name']) . "'>" . _e($cur['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>


    <div class="form-group">
        <label>Кампания</label>
        <div class="row">
            <div class="col-xs-6">
                <select class="select2" style='width:100%;' name='campaign_name'>
                    <option value='' selected>Все</option>
                    <?php
                    foreach ($arr_campaigns as $cur) {
                        echo "<option value='" . _e($cur['campaign_name']) . "'>" . _e($cur['campaign_name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Объявление</label>
        <div class="row">
            <div class='col-xs-6'>
                <select class='select2' style='width:100%;' name='ads_name'>
                    <option value='' selected>Все</option>
                    <?php
                    foreach ($arr_ads as $cur) {
                        echo "<option value='" . _e($cur['ads_name']) . "'>" . _e($cur['ads_name']) . "</option>";
                    }
                    ?>			
                </select>
            </div>
        </div>
    </div>		


    <div class="form-group">
        <label>Сумма</label>
        <div class="row">
            <div class="col-xs-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="costs_value" name="costs_value" placeholder="0.00">
                    <div class="input-group-btn">
                        <button type="button" id="currency_selected" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">руб.&nbsp;&nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu pull-right" role="menu">
                            <li><a href="#" onclick="return change_currency('usd');">долл., $</a></li>
                            <li><a href="#" onclick="return change_currency('uah');">грн., ₴</a></li>
                            <li><a href="#" onclick="return change_currency('rub');">руб.</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>		

    <div class="form-group">
        <div class="row">
            <div class="col-xs-6">
                <button type="submit" class="btn btn-default" id="btn_send">Добавить</button>
                <span class='hidden' id='status_icon'><button class='btn btn-link'><i class='fa fa-check'></i></button></span>
            </div>
        </div>
    </div>
</form>