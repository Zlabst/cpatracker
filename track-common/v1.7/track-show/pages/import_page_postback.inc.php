<?php
require_once _TRACK_LIB_PATH . '/class/common.php';
require_once _TRACK_LIB_PATH . '/class/custom.php';
$available_nets = array();
$networks = dir(_TRACK_LIB_PATH . '/postback');

while ($file = $networks->read()) {
    if ($file != '.' && $file != '..') {
        $file = str_replace('.php', '', $file);
        $name = $file;
        if ($file == 'GdeSlon')
            $name = 'Где Слон?';
        $available_nets[$file] = $name;
    }
}

asort($available_nets);
$custom = new custom();
?>
<link href="<?php echo _HTML_LIB_PATH; ?>/select2/select2.css" rel="stylesheet"/>
<style>
    .btn-default.zeroclipboard-is-hover {background-color:#cbe4f5 !important; border-bottom: 1px solid #95b4c9 !important; }
    .btn-default.zeroclipboard-is-active { background-color:#cbe4f5 !important; box-shadow: 0 3px 5px rgba(0, 0, 0, 0.125) inset;}
</style>

<script src="<?php echo _HTML_LIB_PATH; ?>/mustache/mustache.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/select2/select2.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.min.js"></script>

<script type="text/javascript">
    var links;
    var base_custom = "<?php echo $custom->get_links(); ?>";
    $(document).ready(function()
    {
        // init ZeroClipboard
        
        var clip = new ZeroClipboard(document.getElementById("copy-button"), {
            moviePath: "<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.swf"
        });

        $('.net-btn').click(function() {
            var btn = this;
            $('.link-columns a').removeClass('active');
            $(btn).addClass('active');
            $('#search-row').hide();
            $('#master-row').show();
            $('#master-form').hide();
            $.post(
            'index.php?ajax_act=postback_info',
            {
                net: $(this).attr('net'),
                csrfkey: '<?php echo CSRF_KEY ?>'
            },
            function(data) {
                if (data.status == 'OK') {

                    links = data.links;

                    $('.net-name').text($(btn).attr('net'));
                    $('#netlink_name').text($(btn).attr('net'));
                    $('#netlink_text').html(data.net_text);
                    $('#netlink_href').attr('href', data.reg_url);
                    var template = $('#linkTemplate').html();
                    var template_data = data;
                    if(!template_data.title) template_data.title = 'Создание';

                    var html = Mustache.to_html(template, template_data);

                    $('#links').html(html);
                    
                    // Переинициализируем tooltips
                    
                    if ($('[data-toggle="tooltip"]').length > 0) {
                        $('[data-toggle="tooltip"]').tooltip({
                            container: 'body',
                            delay: { "show": 500, "hide": 100 }
                        });
                    }
                    

                    $('a.clpbrd-copy').each(function(i) {
                        var cur_id = $(this).attr('id');
                        var clip = new ZeroClipboard(document.getElementById(cur_id), {
                            moviePath: "<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.swf"
                        });

                        clip.on('mouseout', function(client, args) {
                            $('.btn-rule-copy').removeClass('zeroclipboard-is-hover');
                        });
                    });

                    $('#result-row').show();
                } 
            }, // function(data)
            'json'
        );
        return false;
        });


        $('#is_lead').change(function() {
            show_urls($('#is_lead').is(':checked'), $('#is_sale').is(':checked'));
        });
        $('#is_sale').change(function() {
            show_urls($('#is_lead').is(':checked'), $('#is_sale').is(':checked'));
        });

        $('#custom-master-start').click(function() {
            $('.link-columns a').removeClass('active');
            $('#search-row').hide();
            $('#net-row2').hide();
            $('#result-row').hide();
            $('#master-form').show();
            return false;
        });
        
        var checkbox_change = function() {
            var cur_url = base_custom;

            $('#master-form input[type=checkbox]').each(function(i) {
                if ($(this).is(':checked')) {
                    cur_url = cur_url + '&' + $(this).attr('id') + '=' + $('#' + $(this).attr('id') + '_val').val();
                }
                $('#custom-link-val').val(cur_url);
            });

        };

        $('#master-form input[type=checkbox]').change(checkbox_change);

        $('#master-form input[type=text]').change(function() {
            var cur_url = base_custom;
            $('#master-form input[type=checkbox]').each(function(i) {
                if ($(this).is(':checked')) {
                    cur_url = cur_url + '&' + $(this).attr('id') + '=' + $('#' + $(this).attr('id') + '_val').val();
                }
                $('#custom-link-val').val(cur_url);
            });

        });
		
		
        checkbox_change();
    });

    function show_urls(is_lead, is_sale) {
        $.each(links, function(i, item) {
            var url = item.url;
            if (is_lead) {
                url = url + '&is_lead=1';
            }

            if (is_sale) {
                url = url + '&is_sale=1';
            }
            $('#net-link-' + item.id).val(url);
        })
    }
</script>

<script id="linkTemplate" type="text/template">
    {{#links}}
    <form class="form-horizontal" data-toggle="tooltip" data-placement="top" title="{{{description}}}">
        <div class="form-group">
               <label class="control-label col-sm-3" for="linkCreate">{{{title}}}</label>
               <div class="col-sm-9">
                   <div class="input-group">
                       <span class="input-group-btn"> <a class="btn btn-default btn-input clpbrd-copy" data-clipboard-target="net-link-{{id}}" role="button" id="copy-button-{{id}}"><i class="icon icon-folders" id="copy_link_{{id}}"></i></a></span>
                   <input type="text" class="form-control" name="linkCreate" id="net-link-{{id}}" value="{{url}}">
               </div>
           </div>
        </div>
    </form>
    {{/links}}
</script>

<!-- Page heading -->
<div class="page-heading">
    <p>Интеграция с CPA сетями</p>
    <div class="header-content">
        <h2>Настройка Postback</h2>
    </div><!--Header-content-->			
</div>

<!-- Network selection -->
<div class="panel panel-default panel-tools">
    <div class="panel-body">
        <div class="row">

            <div class="col-md-5">
                <p>Выберите партнерскую сеть для которой вы хотели бы настроить Postback</p>
                <p>Если сеть отсутствует в списке - используйте <span>«Универсальную ссылку»</span></p>
                <a class="btn btn-default" href="#" id="custom-master-start">Универсальная ссылка</a>				
            </div><!--col-->

            <div class="col-md-6 col-md-offset-1">
                <ul class="link-columns">
                    <?php
                    $i = 0;
                    $first_letter = $first_letter_old = '';
                    foreach ($available_nets as $net => $name) {
                        $first_letter = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
                        echo '<li>
                                    <span ' . ($first_letter_old == $first_letter ? 'class="is-hidden"' : '') . ' >' . $first_letter . '</span>
                                    <a class="btn btn-link-alt net-btn" href="#" net="' . $net . '">' . $name . '</a>
					</li>';
                        $first_letter_old = $first_letter;
                    }
                    ?>
                </ul>
            </div><!--col-->

        </div><!--row-->

    </div><!--panel-body-->
</div><!--panel-->

<!-- *************************************************** -->


<div class="panel panel-default panel-partner" id="result-row" style="display:none;">
    <div class="panel-body">
        <div class="row">

            <div class="col-md-5">
                <div class="partner-description">
                    <h4>Партнерская сеть <span class="net-name"></span></h4>
                    <!--<a  href="#fakelink">
                            <img src="assets/images/partner-logo.png" alt="logo" />
                    </a>-->				

                    <p id="netlink_text"> </p>
                    <div class="btn-group">
                        <a href="#fakelink">Читать далее<i class="fa fa-angle-down"></i></a>
                        <a href="#fakelink" id="netlink_href" target="_blank">Регистрация в сети</a>
                    </div>
                </div>
            </div><!--col-->

            <div class="col-md-6 col-md-offset-1">
                <div class="partner-links">
                    <h4>Postback <span>ссылка для сети <span class="net-name"></span></span></h4>
                    <div id="links"></div>
                </div><!--partner-links-->
            </div><!--col-->

        </div><!--row-->			
    </div><!--panel-body-->




    <!---
    Postback ссылка для сети <b><span class="net-name"></span></b>:<br><br>
<div class="col-md-12">
    Postback ссылка для сети <b><span class="net-name"></span></b>:<br><br>

    <div id="links"></div>
    <div class="panel panel-primary" style="margin-top: 30px;">
        <div class="panel-heading">
            <h3 class="panel-title">Партнерская сеть <span class="net-name"></span></h3>
        </div>
        <div class="panel-body">
            <span id="netlink_text"></span>
            <div>
                <a class="btn btn-primary pull-right" id="netlink_href" href="" target="_blank" style="padding: 5px 10px; margin-top: 15px;">Зарегистрироваться в <span id="netlink_name"></span> →</a>
            </div>
        </div>
    </div>
</div>-->

</div>

<div class="row" id="master-form" style="display:none;">
    <div class="col-md-12">
        <div class="input-group">
            <span class="input-group-btn">
                <button id="copy-button" class="btn btn-default clpbrd-copy" id="custom-link" data-clipboard-target='custom-link-val' title="Скопировать в буфер" type="button"><i class='fa fa-copy' id='clipboard_copy_icon'></i></button>
            </span>
            <input type="text" style="width:100%;" class="form-control" id="custom-link-val" value="<?php echo $custom->get_links(); ?>" ><br>
        </div><br>
        Выберите какие параметры отслеживать (помимо параметров из таблицы трекер хранит все параметры начинающиеся с префикса pbsave_):<br>

        <table class="table table-hover table-striped">
            <tr>
                <td><input type="checkbox" id="profit" checked="checked"></td>
                <td>Сумма конверсии:</td>
                <td><input type="text" id="profit_val" value="{profit}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param20"></td>
                <td>Валюта:</td>
                <td><input type="text" id="txt_param20_val" value="{currency}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="subid" checked="checked"></td>
                <td>SubID:</td>
                <td><input type="text" id="subid_val" value="{subid}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="status"></td>
                <td>Статус:</td>
                <td><input type="text" id="status_val" value="{status}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="date_add"></td>
                <td>Дата:</td>
                <td><input type="text" id="date_add_val" value="{date}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param1"></td>
                <td>IP:</td>
                <td><input type="text" id="txt_param1_val" value="{ip}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param2"></td>
                <td>User Agent:</td>
                <td><input type="text" id="txt_param2_val" value="{uagent}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param4"></td>
                <td>Название оффера:</td>
                <td><input type="text" id="txt_param4_val" value="{offer_name}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="txt_param7"></td>
                <td>Источник:</td>
                <td><input type="text" id="txt_param7_val" value="{source}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param1"></td>
                <td>ID цели:</td>
                <td><input type="text" id="int_param1_val" value="{goal_id}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param2"></td>
                <td>ID оффера:</td>
                <td><input type="text" id="int_param2_val" value="{offer_id}"></td>
            </tr>
            <tr>
                <td><input type="checkbox" id="int_param3"></td>
                <td>ID заказа:</td>
                <td><input type="text" id="int_param3_val" value="{order_id}"></td>
            </tr>
        </table>
    </div>    
</div>