<?php
if (!$include_flag) {exit();}

include _TRACK_SHOW_COMMON_PATH.'/lib/mustache/Autoloader.php';
Mustache_Autoloader::register(_TRACK_SHOW_COMMON_PATH.'/lib/mustache');

$mTemplate = new Mustache_Engine(array(
    'loader' => new Mustache_Loader_FilesystemLoader(_TRACK_SHOW_COMMON_PATH . '/templates/views'),
));

$arr_currencies_list=get_active_currencies();
$selected_currency=current($arr_currencies_list);

$arr_report_data=prepare_report('main-report', $_REQUEST+array('report_params'=>array('act'=>'reports')));

foreach ($arr_report_data['report_params'] as $cur)
{
    if ($cur['name']=='currency_id')
    {
        $selected_currency=$arr_currencies_list[$cur['value']];
        break;
    }
}

echo $mTemplate->render('report-page', $arr_report_data+array(
        'currencies'=>array_values($arr_currencies_list),
        'selected_currency_symbol'=>$selected_currency['symbol'])
);

?>
<script>
    function refresh_report(param_name, param_value)
    {
        if (param_name=='custom_range')
        {
            var obj=$(param_value).parent();
            var date_start=$('input[name="start"]', $(obj)).val();
            date_start=date_start.split('.');
            date_start=date_start[2] + '-' + date_start[1] + '-' + date_start[0];

            var date_end=$('input[name="end"]', $(obj)).val();
            date_end=date_end.split('.');
            date_end=date_end[2] + '-' + date_end[1] + '-' + date_end[0];

            if ($('#report_params input[name="date_start"]').length)
            {
                $('#report_params input[name="date_start"]').val(date_start);
            }
            else
            {
                $('#report_params').append('<input type="hidden" name="date_start" value="'+date_start+'" />');
            }

            if ($('#report_params input[name="date_end"]').length)
            {
                $('#report_params input[name="date_end"]').val(date_end);
            }
            else
            {
                $('#report_params').append('<input type="hidden" name="date_end" value="'+date_end+'" />');
            }

            // Remove report_period field, dates are already set
            $('#report_params input[name="report_period"]').remove();
            $("#report_params").submit();

            return false;
        }

        var arr_param_names=param_name.toString().split ('||');
        var arr_param_values=param_value.toString().split ('||');
        var names=[]; var values=[];

        arr_param_names.forEach(function(item, i)
        {
            names[i]=item.toString().split ('|');
        });
        values=arr_param_values;

        var param_value_index=0;

        for (var i = 0; i < names.length; i++)
        {
            param_name=names[i];
            param_value=values[i];

            switch (param_name[0])
            {
                case 'filter': case 'force_filter':
                    if (param_name[0]=='force_filter')
                    {
                        // Remove previous filters
                        $('#report_params [name="filter_by[]"]').remove();
                        $('#report_params [name="filter_value[]"]').remove();
                    }

                    for (var k=1; k<param_name.length; k++)
                    {
                        $('#report_params').append(
                            $('<input/>')
                                .attr('type', 'hidden')
                                .attr('name', 'filter_by[]')
                                .val(param_name[k])
                        );

                        $('#report_params').append(
                            $('<input/>')
                                .attr('type', 'hidden')
                                .attr('name', 'filter_value[]')
                                .val(values[param_value_index])
                        );
                        param_value_index++;
                    }
                break;

                default:
                    var param_found=false;
                    $('#report_params input[type="hidden"]').each(function() {
                        if ($(this).attr('name')==param_name)
                        {
                            param_found=true;
                            $(this).val(param_value);
                        }
                    });
                    if (!param_found)
                    {
                        $('#report_params').append('<input type="hidden" name="'+param_name+'" value="'+param_value+'" />');
                    }

                    // Remove date_start and date_end fields, we have custom period
                    if (param_name=='report_period')
                    {
                        $('#report_params input[name="date_start"]').remove();
                        $('#report_params input[name="date_end"]').remove();
                    }
                    param_value_index++;
                break;
            }
        }

        $("#report_params").submit();
        return false;
    }

    function export_as_xls()
    {
        _url = location.href;
        _url += (_url.split('?')[1] ? '&':'?') + 'export=1';
        window.location.href = _url;
    }

</script>

<script>
$(document).ready(function()
{
    $('.editable').editable
    (
        function(value, settings)
        {
            var filter_by=$("#main-report_IN input[name='filter_by']").val();
            var filter_value=$("#main-report_IN input[name='filter_value']").val();
            var date_start=$("#main-report_IN input[name='date_start']").val();
            var date_end=$("#main-report_IN input[name='date_end']").val();
            var currency_id=$("#main-report_IN input[name='currency_id']").val();

            var main_column=$("#main-report_IN input[name='main_column']").val();
            var row_value=$(this).data('row_value');
            var clicks_count=$(this).data('clicks_count');

            if (value=='')
            {
                value=0;
            }


            $.ajax({
                type: 'POST',
                url: 'index.php',
                data: 'csrfkey=<?php echo CSRF_KEY;?>'+
                '&ajax_act=add_costs'+
                '&value='+row_value+
                '&cost='+value+
                '&clicks_count='+clicks_count+
                '&'+$("#main-report_IN").serialize()
            }).done(function( msg )
            {
                console.log (msg);
            });

            return(value);
        },
        {
            data: function(value, settings)
            {
                var regex = /[+-]?\d+(\.\d+)?/g;
                return value.match(regex).map(function(v) { return parseFloat(v); });
            },
            onblur: 'submit',
            cssclass : 'editable-input'
        }
    );

    result='';
    tmp = [];
    location.search.substr(1).split("&").forEach(function (item)
    {
        tmp = item.split("=");
        if (tmp[0] === 'filter_actions') result = decodeURIComponent(tmp[1]);
    });
    switch (result)
    {
        case 'sales':
            show_columns('report-main-report', '.c-sale', '.c-action, .c-lead', '.c-cell');
        break;

        case 'leads':
            show_columns('report-main-report', '.c-lead', '.c-action, .c-sale', '.c-cell');
        break;

        default:
            show_columns('report-main-report', '.c-action', '.c-sale, .c-lead', '.c-cell');
        break;
    }
});

function show_columns(id, show_class, hide_class, visibility_class)
{
    if (typeof visibility_class !== 'undefined')
    {
        $(visibility_class, $('#'+id)).css('visibility', "");
    }

    $(hide_class, $('#'+id)).hide();
    $(show_class, $('#'+id)).show();
}
</script>


<style>
.editable {
    cursor: pointer;
    display:block;
    border-bottom:1px solid transparent;
}
.editable:hover {
    border-bottom:1px dashed lightgray;
}
.editable-input{
    width:1px;
    display:inline-block;
    outline:0;
}
input:focus
{
    outline:0;
}
</style>

<link href="<?php echo _HTML_LIB_PATH; ?>/datatables/css/jquery.dataTables.css" rel="stylesheet">
<link href="<?php echo _HTML_LIB_PATH; ?>/datatables/css/dt_bootstrap.css" rel="stylesheet">
<script src="<?php echo _HTML_LIB_PATH; ?>/datatables/js/jquery.dataTables.min.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/datatables/js/dt_bootstrap.js" charset="utf-8" type="text/javascript"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/sparkline/jquery.sparkline.min.js"></script>
<link href="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/moment.min.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/daterangepicker/daterangepicker.js"></script>
<link href="<?php echo _HTML_LIB_PATH; ?>/datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet"/>
<script src="<?php echo _HTML_LIB_PATH; ?>/jeditable/jquery.jeditable.min.js"></script>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH; ?>/datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo _HTML_LIB_PATH; ?>/datepicker/locales/bootstrap-datepicker.ru.min.js"></script>