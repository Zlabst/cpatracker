<!--<link href="<?php echo _HTML_LIB_PATH; ?>/select2/select2.css" rel="stylesheet"/>-->

<script src="<?php echo _HTML_LIB_PATH; ?>/select2/dist/js/select2.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/mustache/mustache.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/select2/dist/js/i18n/ru.js"></script>
<script src="<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.min.js"></script>
<style>
    .select-link {
        width: 100%;

    }    
    .btn-default.zeroclipboard-is-hover {background-color:#cbe4f5 !important; border-bottom: 1px solid #95b4c9 !important; }
    .btn-default.zeroclipboard-is-active { background-color:#cbe4f5 !important; box-shadow: 0 3px 5px rgba(0, 0, 0, 0.125) inset;}
</style>

<?php

// Проверка на наличие избранного
$sources_favorits = sources_favorits();
$have_favorits = count($sources_favorits) > 0;


$offset = rq('offset', 2);
$source = rq('source');
if (empty($source_config[$source]))
    $source = 'source';

$source_name = (empty($source) or $source == 'source' or empty($source_config[$source])) ? 'Универсальная ссылка' : $source_config[$source]['name'];
?>



<!--
<div class="row">
    <div class="col-sm-9">
        <div class="alert alert-danger" style="display:none;" id="incorrect_name_alert">
            Неверное название ссылки, используйте только латинские буквы, цифры и знаки _ и -.
        </div>
        <form class="form-inline" method="post" onsubmit="return validate_add_rule();" id="form_add_rule" role="form" style="margin-bottom:30px">
            <div class="form-group">
                <label class="sr-only">Название ссылки</label>
                <input type="text" class="form-control" placeholder="Название ссылки" id="rule_name_id" name="rule_name">
            </div>
            &nbsp;→&nbsp;
            <div class="form-group">
                <label class="sr-only">Ссылка</label>
                <input type="hidden" placeholder="Ссылка" name='out_id' class='select-link toSave' data-selected-value='<?php echo $js_last_offer_id; ?>'>
            </div>
            <button type="submit" class="btn btn-default" onclick="">Добавить</button>
            <input type="hidden" name="ajax_act" value="add_rule">
            <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
        </form>         
    </div>
</div>-->



<!-- Page heading -->
<div class="page-heading">
    <p>Ссылка</p>
    <div class="header-content">

        <!--Header-->
        <div class="btn-group header-left">
            <h2><?php echo $source_name; ?><input type="checkbox" value="" class="i-star" source="<?php echo $source; ?>" <?php echo in_array($source, $sources_favorits) ? 'checked' : ''; ?>></h2>
        </div>

        <div role="toolbar" class="btn-toolbar">

            <!--Right buttons-->
            <form class="form-inline" method="post" onsubmit="return validate_add_rule();" id="form_add_rule" role="form">
                <div class="form-inline pull-right">
                    <div class="alert alert-danger" style="display:none;" id="incorrect_name_alert">
                        Неверное название ссылки, используйте только латинские буквы, цифры и знаки _ и -.
                    </div>

                    <div class="btn-group">
                        <input type="text" class="form-control" placeholder="Название ссылки" id="rule_name_id" name="rule_name">
                    </div>
                    <div class="btn-group">
                        <select class="select2 select-link" style="width: 300px" name="out_id" data-selected-value="<?php echo $js_last_offer_id; ?>"></select>
                    </div>

                    <div class="btn-group">
                        <a class="btn btn-default" href="#" onclick="$('#form_add_rule').submit(); return false;"><i class="cpa cpa-plus icon-lg"></i></a>
                    </div>

                </div>
                <input type="hidden" name="ajax_act" value="add_rule">
                <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
            </form>


        </div><!--Toolbar-->


    </div><!--Header-content-->

</div><!--page-heading-->

<script>
    var last_removed = 0;
    window.path_level = <?php echo count(explode('/', tracklink())); ?>;
    
    $(document).ready(function()
    {
        $('input[name=rule_name]').focus();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=get_rules_json&source=<?php echo $source; ?>&offset=<?php echo $offset; ?>'
        }).done(function(msg) {
            var template = $('#rulesTemplate').html();
            var template_data = $.parseJSON(msg);
            
            var html = Mustache.to_html(template, template_data);
            $('#rules_container').html(html);
            
            var answer = eval('(' + msg + ')');

            // Init ZeroClipboard
            $('a[id^="copy-button"]').each(function(i)
            {
                var cur_id = $(this).attr('id');
                var clip = new ZeroClipboard(document.getElementById(cur_id), {
                    moviePath: "<?php echo _HTML_LIB_PATH; ?>/clipboard/ZeroClipboard.swf"
                });

                clip.on('mouseout', function(client, args) {
                    $('.btn-rule-copy').removeClass('zeroclipboard-is-hover');
                });
            });
            
            if(answer.next) {
                $('#next_link').attr('href', answer.next).parent().show();
            } else {
                $('#next_link').parent().hide();
            }
            
            if(answer.prev) {
                $('#prev_link').attr('href', answer.prev).parent().show();
            } else {
                $('#prev_link').parent().hide();
            }
            
            
            
            <!-- =============================================== -->
            <!-- =========== Table collapse  ========== -->
            <!-- =============================================== --> 
            if ($('[data-toggle=collapse-next]').length > 0) {	
                $('body').on('click.collapse-next.data-api', '[data-toggle=collapse-next]', function (e) {
                    var $target = $(this).parent().next().find('.collapse');
                    $target.collapse('toggle');	
                })
            }

            <!-- =============================================== -->
            <!-- =========== Select2  Dropdowns ========== -->
            <!-- =============================================== --> 
            /*
            if ($('.select2').length > 0) {
                $('.select2').select2({
                    theme: 'classic',
                    language: 'ru',
                    minimumResultsForSearch: 5,
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }
                        if (data.text.indexOf(params.term) > -1) {
                            var modifiedData = $.extend({}, data, true);
                            modifiedData.text += ' (совпадение)';
                            return modifiedData;
                        }
                        return null;
                    }
                });
            }
             */
            
            $('.ldelete').on("click", function(e) {
                e.stopImmediatePropagation();
                var id = $(this).closest("tr").attr('id').replace('rule', '');
                delete_rule(id);            
            });
            
            $('.lcopy').on("click", function(e) {
                var id = $(this).closest("tr").attr('id').replace('rule', '');
                copy_link(id);
            });
            
            $('.rname').on("click", function(e) {
                //e.stopPropagation();
                var id = $(this).closest("tr").attr('id').replace('rule', '');
                var rule_name = $('#rule' + id).find('.rule-name-title');
                rename_link(id, rule_name, false);
                return false;
            });
            
            $('.lp_switch').change(function() {
                var direct = $(this).prop("checked") ? 0 : 1;
                var id = $(this).closest('form').find('.panel').first().attr('id');
                $.ajax({
                    type: "POST",
                    url: "index.php",
                    data: 'ajax_act=get_source_link&source=<?php echo $source; ?>&name=' + $('#rule' + id).find('.rule-name-title').text() + '&id=' + id + '&direct=' + direct
                }).done(function(msg) {
                    $('#' + id).closest('form').find('.rule-link-text').val(msg);
                });
               
            });
            
            // Открывает поле редактирования названия ссылки
            function rename_link(id, rule_name, new_link) {
                var stop_flag = false;
                var rule_name_text = $(rule_name).text();
                var rule_old_name = rule_name_text;
                
                if(new_link) {
                    var placeholder = 'Новое имя';
                    var old_name_text = '';
                } else {
                    var placeholder = 'Имя не может быть пустым';
                    var old_name_text = rule_old_name;
                }
                
                $(rule_name).html('<input id="rn' + id + '" class="form-control rulenamein" placeholder="' + placeholder + '" value="' + (old_name_text) + '" >');
                $("#rn" + id).focus(); 
                $("#rn" + id).select();
                $("#rn" + id).click(function(e){
                    e.stopPropagation();
                });
                
                $("#rn" + id).keypress(function(e) {
                    e.stopPropagation();
                    if(e.which == 13) {
                        e.stopImmediatePropagation();
                        e.preventDefault();
                        rule_name_text =  $("#rn" + id).val().replace(/^\s+|\s+$/g, '');
                        console.log(rule_name_text);
                        if(rule_name_text.length){
                            $(rule_name).html($("#rn" + id).val());
                            save_name(id, rule_name_text, rule_old_name, new_link);
                            stop_flag = true;
                        } else {
                            alert("Имя не может быть пустым.");
                            $("#rn"+id).val(rule_old_name);
                            $(rule_name).focus();           
                        }
                    }
                });
                
                $("#rn" + id).focusout(function(e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    rule_name_text = $("#rn"+id).val().replace(/^\s+|\s+$/g, '');
                    if(rule_name_text.length){
                        save_name(id, rule_name_text, rule_old_name, new_link);
                        if(!stop_flag){$(rule_name).html($("#rn"+id).val())}
                    }else{
                        alert("Имя не может быть пустым.");
                        $("#rn" + id).val(rule_old_name);
                        $("#rn" + id).focus();                         
                    }
                });
            }
            
            // Копируем ссылку в интерфейсе
            function copy_link(id) {
                var old_rule = $('#rule' + id);
                var new_rule = old_rule.clone();
                var new_link_id = 0;
                    
                $.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=copy_link&link_id=' + id
                }).done(function(msg) {
                    new_link_id = msg;
                    new_rule.attr('id', 'rule' + new_link_id);
                    
                    // Убираем все лишние элементы управления, они будут доступны после обновления страницы
                    new_rule.find('ul').remove();
                    new_rule.find('a').remove();
                    
                    // Поле ввода имени
                    var rule_name = new_rule.find('.rule-name-title');

                    // Добавляем на экран
                    $('#rules_container').prepend(new_rule);
                    
                    // Открываем поля редактирования имени
                    rename_link(new_link_id, rule_name, true);
                    $('#rn' + new_link_id).val(old_rule.find('.rule-name-title').text());
                    $('#rn' + new_link_id).select();
                });
            }
            
            // Сохранить новое название
            function save_name(id, name, old_name, new_link){
                $.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=update_rule_name&rule_id=' + id + '&rule_name=' + name + '&old_rule_name=' + old_name
                }).done(function(msg) {
                    if(new_link) window.location.reload();                    
                });
            }
            
            // 
            function users_label(obj) {
                obj.parent().find('.users_label').text('Остальные посетители');
            }
            
            function prepareTextInput(row, name, title){ 
                users_label(row);
                row.find('.label-default').text(title);
                row.find('.select-item').attr('placeholder', title);
                row.find('.select-item').attr('itemtype', name);
                row.find('.select-item').focus();
                //row.find('.select-link').select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});
            } 
            
            // Подготовка данных для списка select2
            function sdata(data) {
                return {
                    data: data,
                    theme: 'classic',
                    language: 'ru',
                    minimumResultsForSearch: 5
                };
            }
            
            // Инициализируем список с офферами
            function init_links(rule_table) {
                rule_table.find('select.select-link').select2(sdata(dictionary_links));
                rule_table.find('select.select-link').first().select2('val', rule_table.find('[name = default_out_id]').val());
            }
            
            $('.addcountry').on("click", function(e) {
                e.preventDefault();
                var template = $('#countryTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('select.select-geo_country').select2(sdata(dictionary_countries));
                init_links(rule_table);
            });
            
            $('.addlang').on("click", function(e) {
                e.preventDefault();
                var template = $('#langTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('select.select-lang').select2(sdata(dictionary_langs));
                init_links(rule_table);
            });
            
            $('.addrefer').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'referer', 'Реферер');
                init_links(rule_table);
            });
            
            $('.addcity').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'city', 'Город');               
                init_links(rule_table);
            });
            
            $('.addregion').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'region', 'Регион');               
                init_links(rule_table);
            });
            
            $('.addprovider').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'provider', 'Провайдер');
                init_links(rule_table);
            });
            
            $('.addip').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'ip', 'IP адрес');
                init_links(rule_table);
            });
            
            $('.adddevice').on("click", function(e) {
                e.preventDefault();
                var template = $('#deviceTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('select.select-device').select2(sdata(dictionary_device));
                init_links(rule_table);
            });
            
            $('.addos').on("click", function(e) {
                e.preventDefault();
                var template = $('#osTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                rule_table.find('select.select-os').select2(sdata(dictionary_os));
                init_links(rule_table);
            });
            
            $('.addplatform').on("click", function(e) {
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'platform', 'Платформа');               
                init_links(rule_table);
            });
            
            $('.addbrowser').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'browser', 'Браузер');
                init_links(rule_table);
            });
            
            $('.addagent').on("click", function(e) {
                e.preventDefault();
                var template = $('#referTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                rule_table.prepend(template);
                var row = rule_table.find('div').first();
                prepareTextInput(row, 'agent', 'User-agent');
                init_links(rule_table);
            });
            
            $('.addget').on("click", function(e) {
                e.preventDefault();
                var template = $('#getTemplate').html();
                var rule_table = $(this).parent().parent().parent().parent().parent().parent();
                var rule_id = rule_table.attr('id');
                users_label(rule_table);
                rule_table.prepend(template);
                init_links(rule_table);
            });
            
            // buttons }//  
            $('body').on("change",'.getpreinput',function() { 
                var text = $(this).parent().parent().find('.in1').val()+'='+$(this).parent().parent().find('.in2').val();
                $(this).parent().parent().find('.select-item').val(text);
            });
            
            $('.btnsave').on("click", function(e) {
                e.preventDefault();
                var flag = true;
                var rule_id = $(this).attr('id').replace('btn_save_', '');
                var rule_table = $('#' + rule_id + '');

                if(!flag){ alert("В полях ввода для ссылки GET можно использовать только цифры и буквы латинского алфавита.");  return false;}
                
                $(rule_table).find('.select-link').each(function() {                                      
                    $(this).addClass('toSave');                      
                });
                $(rule_table).find('.select-item').each(function() {                                         
                    $(this).addClass('toSave');                     
                });
                
                if(update_rule(rule_id) && !$(rule_table).find('.fa-check').size()){
                    //$(this).after('<i style="position: relative; right: 20px; top: 9px;" class="fa fa-check pull-right"></i>');
                }
                
                return false;
            });
            
            $('body').on("click", '.btnrmcountry', function(e) {
                e.preventDefault();
                var rule_id = $(this).closest("div.panel").attr('id');
                $(this).parent().parent().parent().remove();
                //update_rule(rule_id);
                
                
                if($('#' + rule_id).children().length < 3) {
                    $('#' + rule_id).find('.users_label').text('Все посетители');
                }
                
                //console.log($(this).closest("div.panel"));
                
                /*
                
                $(this).parent().remove();
                update_rule(rule_id);
                 */ 
            });          
            $(".table-rules th").on("click", function() {
                $(this).closest("table").children("tbody").toggle();
                $(this).closest("table").toggleClass("rule-table-selected");
            });

            
            // Fill values for destination links
            
            var dictionary_links = [];
            dictionary_links.push(<?php echo $js_offers_data; ?>);
            
            var dictionary_sources = <?php echo $js_sources_data; ?>;
  			
            
            // S2ERR
            
            $('select.select-link').each(function()
            {
                /*
                 * theme: 'classic',
                    language: 'ru',
                    minimumResultsForSearch: 5,
                    matcher: function (params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }
                        if (data.text.indexOf(params.term) > -1) {
                            var modifiedData = $.extend({}, data, true);
                            modifiedData.text += ' (совпадение)';
                            return modifiedData;
                        }
                        return null;
                    }
                 **/
                
                $(this).select2({
                    theme: 'classic',
                    language: 'ru',
                    minimumResultsForSearch: 5,
                    data: dictionary_links,
                    placeholder: "Выберите оффер"
                    /*
                    width: 'copy',
                    containerCssClass: 'form-control select2'
                     */
                });
                //$(this).select2({data: {results: dictionary_links}, width: 'copy', containerCssClass: 'form-control select2'});

                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            
            $('button.btn-rule-copy.direct').each(function() {
                $(this).on('click', function(e) {change_source(e, false)});
            });
            
            /*
            $('input.select-sources').each(function()
            {
                $(this).select2({data: {results: dictionary_sources}, width: 'copy', containerCssClass: 'form-control select2'});
                $(this).select2("val", $(this).attr('data-selected-value'));
                $(this).on("select2-selecting", function(e) {change_source(e, true)});
            });
             */
            
            var dictionary_countries = [];
           
            dictionary_countries.push(<?php echo $js_countries_data; ?>); 
            
            
            $('select.select-geo_country').each(function() {
                $(this).select2(sdata(dictionary_countries));
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_langs = [];
            dictionary_langs.push({text: "", children:[{id:"en", text:"Английский, en"},{id:"ru", text:"Русский, ru"},{id:"uk", text:"Украинский, uk"}]});
            dictionary_langs.push(<?php echo $js_langs_data; ?>);
            
            $('select.select-lang').each(function() {
                $(this).select2(sdata(dictionary_langs));
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_os = [];
            dictionary_os.push({text:"", children:[
                    {id:"DEFINED_IOS",     text:"iOS"},
                    {id:"DEFINED_ANDROID", text:"Android"},
                    {id:"DEFINED_WINDOWS", text:"Windows"},
                    {id:"DEFINED_MACOS",   text:"Mac OS"},
                    {id:"DEFINED_LINUX",   text:"Linux"},
                    {id:"DEFINED_MOBILE",  text:"Все мобильные"},
                    {id:"DEFINED_DESKTOP", text:"Все десктопные"}
                ]});
            
            $('select.select-os').each(function() {
                $(this).select2(sdata(dictionary_os));
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            dictionary_device = [];
            dictionary_device.push({text:"", children:[
                    {id:"DEFINED_IPHONE",  text:"Apple iPhone"},
                    {id:"DEFINED_IPAD",    text:"Apple iPad"},
                ]});
            
            $('select.select-device').each(function() {
                $(this).select2(sdata(dictionary_device));
                $(this).select2("val", $(this).attr('data-selected-value'));
            });
            
            $('input.in1').each(function() {
                var text = $(this).parent().parent().find('.select-item').val();
                var arr = text.split('=');
                $(this).val(arr[0]);
            });
            
            $('input.in2').each(function() {
                var text = $(this).parent().parent().find('.select-item').val();
                var arr = text.split('=');
                $(this).val(arr[1]);
            });
        
        
        
<?php
$open_rule = rq('open_rule', 2);
if ($open_rule > 0) {
    ?>

                $('#rule<?php echo $open_rule; ?> td').last().click();

<?php } ?>
        });
    });
    
    function change_source(obj, select2) {
        if(select2) {
            var source = obj.val;
            obj = obj.target;
            var table = $(obj).parent().parent().parent().parent().parent();
            var id = table.find('.btnsave').attr('id').replace('btn_save_', '');
        } else {
            obj = obj.target;
            var table = $(obj).parent().parent().parent().parent();
            var id = table.find('.btnsave').attr('id').replace('btn_save_', '');
            var source = $('#rule-link-select2-' + id).val();
            $('#rule-link-direct-' + id).toggleClass("active");
        }
        var direct = $('#rule-link-direct-' + id).hasClass("active") ? 1 : 0;
        table.find('.rule-link').each(function() {
            lnk = $(this).val();
            parts = lnk.split('/');
        });
		
        $.ajax({
            type: "POST",
            url: "index.php",
            data: 'ajax_act=get_source_link&source=' + source + '&name=' + parts[path_level] + '&id=' + id + '&direct=' + direct
        }).done(function(msg) {
            table.find('.rule-link-text').val(msg);
        });
    }
    
    // Удалить ссылку
    function delete_rule(rule_id) {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=delete_rule&id=' + rule_id
        }).done(function(msg) {
            $('#rule' + rule_id).hide();
            var rule_name = $('#rule'+rule_id).find('.rule-name-title');
            var rule_name_text = $(rule_name).text();
            $('#rule_name').text(rule_name_text);
            $('#restore_alert').show();
            last_removed = rule_id;
        });

        return false;
    }
    
    // Восстановить ссылку
    function restore_rule() {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=restore_rule&id=' + last_removed
        }).done(function(msg) {
            $('#rule' + last_removed).show();
            $('#restore_alert').hide();
            last_removed = 0;
        });

    }
 
    function update_rule(rule_id) {
        var links = [];
        var sources = [];
        var rules_items = '';
        var values = '';
        var error = '';
        var rule_table = $('#' + rule_id);
        var name = $('#rule' + rule_id).find('.rule-name-title').text();
        var i = 0;
        $(rule_table).find('input.in1').each(function() {        
            if (!$(this).val()) {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('input.in2').each(function() {        
            if (!$(this).hasClass('canzero') && !$(this).val()) {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('.select-item.toSave').each(function() {        
            if ($(this).val()) {
                rules_items = rules_items + '&rules_item['+i+"][val]=" + $(this).val();
                rules_items = rules_items + '&rules_item['+i+"][type]=" + $(this).attr('itemtype');
                i++;
            } else {
                error = 'Выберите условие';
            }
        });
        $(rule_table).find('.select-link.toSave').each(function() {
            if ($(this).val()) {
                if (!in_array($(this).val(), links)) {
                    links.push($(this).val());
                }
                values = values + '&rule_value[]=' + $(this).val();
            } else {
                error = 'Выберите оффер';
            }
            
        });
        if (error) {
            alert(error);
            return false;
        } else {
            rules_items = rules_items + '&rules_item['+i+"][val]=default";
            rules_items = rules_items + '&rules_item['+i+"][type]=geo_country" ;
            $.ajax({
                type: 'POST',
                url: 'index.php',
                data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=update_rule&rule_id=' + rule_id + '&rule_name=' + name + rules_items + values
            }).done(function(msg) {
                eval('answer = ' + msg);
                
                if(answer.status) {
                    console.log(answer);
                    console.log(links);
                    
                    if (links.length > 3) {
                        var badge = '<span class="label label-warning">+ ' + (links.length - 3) + '</span>';
                    } else {
                        var badge = '';
                    }
                    $('#rule' + rule_id).find('.rule-offer-names').html(answer.offers_text);
                    $('#rule' + rule_id).find('.rule-destination-title').html(badge);
                } else {
                    alert(answer.error);
                }
            });
        }
        return true;
    }

    // Правильное склонение числительных
    function declination(number, one, two, five) {
        number = Math.abs(number);
        number %= 100;
        if (number >= 5 && number <= 20) {
            return five;
        }
        number %= 10;
        if (number == 1) {
            return one;
        }
        if (number >= 2 && number <= 4) {
            return two;
        }
        return five;
    }
    
    // Проверка на наличие элемента в массиве
    function in_array(needle, haystack, strict) {
        var found = false, key, strict = !!strict;
        for (key in haystack) {
            if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
                found = true;
                break;
            }
        }
        return found;
    }
    
    // Проверка при добавлении
    function validate_add_rule() {
        var nameR = /^[a-z0-9\-\_]+$/i;
        if ($('#rule_name_id').val() == '') {
            return false;
        }
        if (!nameR.test($('input[name=rule_name]', $('#form_add_rule')).val())){
            $('#incorrect_name_alert').show();
            $('input[name=rule_name]', $('#form_add_rule')).focus();
            return false;
        }
        return true;
    }
</script>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Удаление ссылки</h4>
            </div>
            <input type="hidden" id="hideid">
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button"  class="btn btn-danger yeapdel">Удалить</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script id="getTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>GET</span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <div class="btn-group col-6 pull-right">
                <input type="text" class="form-control getpreinput in2 canzero" placeholder="Значение" > 
            </div>
            <div class="btn-group col-6 pull-left">
                <input type="text" class="form-control getpreinput in1" placeholder="Поле" > 
            </div>
            <input type="hidden" class="select-item toSave" itemtype="get" value="{{value}}"> 
        </div>
    </div>
</script>
<script id="referTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a><span class="label-default">Реферер</span></span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <input type="text" class="form-control select-item toSave" placeholder="Реферер" itemtype="referer">
        </div>
    </div>
</script>
<script id="countryTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>Страна</span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" itemtype="geo_country" class="select-geo_country select-item select2 toSave" data-selected-value=""></select>
        </div>
    </div>

</script>
<script id="deviceTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>Устройство</span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" itemtype="device" class="select-device select-item select2 toSave" data-selected-value=""></select>
        </div>
    </div>
</script>
<script id="osTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>ОС</span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" itemtype="os" class="select-os select-item select2 toSave" data-selected-value=""></select>
        </div>
    </div>
</script>
<script id="langTemplate" type="text/template">
    <div class="condition-row">
        <div class="btn-group condition-name">
            <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>Язык</span>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" placeholder="Ссылка" name="out_id[]" class="select-link select2 toSave" data-selected-value=""></select>
        </div>
        <div class="btn-group col-4 pull-right">
            <select style="width: 100%" itemtype="lang" class="select-lang select-item select2 toSave" data-selected-value=""></select>
        </div>
    </div>
</script>
<script id="rulesTemplate" type="text/template">
    {{#rules}}
    <tr id="rule{{id}}">
        <td class="no-padding"><a href="#" class="link-off a-default btn-rule-copy" id="copy-button-{{id}}" data-clipboard-target="rule-link-text-{{id}}"><i class="cpa cpa-folders"></i></a></td>
        <td data-toggle="collapse-next" class="accordion-toggle rule-name-title">{{name}}</td>
        <td data-toggle="collapse-next" class="accordion-toggle rule-offer-names">{{offer_names}}</td>
        <td data-toggle="collapse-next" class="accordion-toggle rule-destination-title">{{#destination_multi}}<span class="label label-warning">{{destination_multi}}</span>{{/destination_multi}}</td>
        <td class="dropdown-cell">
            <div class="dropdown">
                <a aria-expanded="false" role="button" data-toggle="dropdown" class="dropdown-toggle" href="#fakelink">			
                    <i class="cpa cpa-bars"></i>
                </a>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a href="#" class="dropdown-link rname">Переименовать</a>
                    </li>
                    <li>
                        <a href="#" class="dropdown-link lcopy">Дублировать</a>
                    </li>
                    <li class="dropdown-footer text-danger">
                        <a href="#" class="dropdown-link text-danger ldelete">
                            <i class="cpa cpa-trash icon-abs"></i>
                            <span>Удалить</span>
                        </a>
                    </li>
                </ul>
            </div>
        </td>
        <td data-toggle="collapse-next" class="text-center accordion-toggle">
            <a class="a-default"><i class="cpa cpa-angle-down"></i></a>
        </td>
    </tr>

    <!--hidden row-->
    <tr>
        <td colspan="6" class="no-padding">
            <div class="collapse">	
                <div class="condition">
                    <form >					
                        <div class="panel" id="{{id}}">
                            {{#conditions}}
                            <div class="condition-row">
                                <div class="btn-group condition-name">
                                    <span><a class="a-danger btnrmcountry" href="#"><i class="cpa cpa-trash"></i></a>{{type}}</span>
                                </div>
                                <div class="btn-group col-4 pull-right">
                                    <select name="out_id[]" style="width: 100%" class="select-link toSave select2" data-selected-value="{{destination_id}}" ></select>
                                </div>
                                {{#textinput}}
                                {{#getinput}}
                                <div class="btn-group col-4 pull-right">
                                    <div class="btn-group col-6 pull-right">
                                        <input type="text" class="form-control getpreinput in2 canzero" placeholder="Значение" > 
                                    </div>
                                    <div class="btn-group col-6 pull-left">
                                        <input type="text" class="form-control getpreinput in1" placeholder="Поле" > 
                                    </div>
                                    <input type="hidden" class="select-item" itemtype='get' value="{{value}}">                 
                                </div>
                                {{/getinput}}
                                {{^getinput}}
                                <div class="btn-group col-4 pull-right">
                                    <input type="text" class="form-control select-item toSave" placeholder="{{type}}" itemtype='{{select_type}}' value='{{value}}' > 
                                </div>
                                {{/getinput}}
                                {{/textinput}}
                                {{^textinput}}
                                <div class="btn-group col-4 pull-right">
                                    <!--<input type="hidden" placeholder="{{type}}" itemtype='{{select_type}}' class='select-{{select_type}} select-item toSave' data-selected-value='{{value}}'>-->
                                    <select placeholder="{{type}}" itemtype='{{select_type}}' class='select-{{select_type}} select-item toSave' data-selected-value='{{value}}'></select>
                                </div>
                                {{/textinput}}

                            </div><!--condition-row-->
                            {{/conditions}}

                            <div class="condition-row">
                                <div class="btn-group condition-name no-icon">
                                    <span class="users_label">{{other_users}}</span>
                                </div>
                                <div class="btn-group col-4 pull-right">
                                    <select class="select2 select-link toSave" style="width: 100%" name="default_out_id" data-selected-value="{{default_destination_id}}"></select>
                                </div>
                            </div><!--condition-row-->

                            <div class="condition-row">
                                <div class="btn-group pull-right">
                                    <a class="btn btn-default btnsave" href="#" id="btn_save_{{id}}">
                                        <i class="cpa cpa-check-o"></i>
                                        <span>Сохранить</span>
                                    </a>
                                </div>

                                <div class="btn-group no-margin">
                                    <div class="dropdown">						
                                        <a class="btn btn-default dropdown-toggle" href="#fakelink"  data-toggle="dropdown" role="button" aria-expanded="false">
                                            <i class="cpa cpa-plus icon-lg"></i>
                                            <span>Добавить условие</span>
                                            <i class="cpa cpa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-full" role="menu">
                                            <li><a class="dropdown-link addcountry" href="#">Страна</a></li>
                                            <li><a class="dropdown-link addlang" href="#">Язык браузера</a></li>
                                            <li><a class="dropdown-link addrefer" href="#">Реферер</a></li>
                                            <li><a class="dropdown-link addcity" href="#">Город</a></li>
                                            <li><a class="dropdown-link addregion" href="#">Регион</a></li>
                                            <li><a class="dropdown-link addprovider" href="#">Провайдер</a></li>
                                            <li><a class="dropdown-link addip" href="#">IP адрес</a></li>
                                            <li><a class="dropdown-link adddevice" href="#">Устройство</a></li>
                                            <li><a class="dropdown-link addos" href="#">ОС</a></li>
                                            <li><a class="dropdown-link addplatform" href="#">Платформа</a></li>
                                            <li><a class="dropdown-link addbrowser" href="#">Браузер</a></li>
                                            <li><a class="dropdown-link addagent" href="#">User-agent</a></li>
                                            <li><a class="dropdown-link addget" href="#">Параметр в GET-запросе</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div><!--condition-row-->	
                        </div><!--panel-->

                        <div class="panel condition-footer">
                            <div class="btn-group col-10">
                                <input type="text" class="form-control rule-link-text" id="rule-link-text-{{id}}" placeholder="Поле" value="{{url}}">
                            </div>
                            <div class="btn-group pull-right no-margin">
                                <a class="btn btn-default single-icon" href="#" id="copy-button-text-{{id}}" role="button" data-clipboard-target="rule-link-text-{{id}}">
                                    <i class="cpa cpa-folders"></i>
                                </a>
                            </div>
                            <div class="btn-group pull-right no-margin" data-toggle="buttons">
                                <label class="btn btn-toggle active">
                                    <input type="checkbox" autocomplete="off" class="lp_switch" checked> LP <i class="cpa cpa-switch"></i>
                                </label>
                            </div>
                        </div><!--panel-->

                    </form>
                </div><!--condition-->
            </div><!--collapse-->
        </td>
    </tr>
    <!--row-->
    {{/rules}}
</script>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning fade in alert-dismissible" style="display: none;" id="restore_alert" role="alert">
            <button class="close" aria-label="Close" data-dismiss="alert" type="button">
                <span aria-hidden="true">×</span>
            </button>
            <strong>Внимание!</strong> Ссылка <span id="rule_name"></span> была удалена, Вы можете её <strong><u><a href="javascript:void(0);" onClick="restore_rule();">восстановить</a></u></strong>
        </div>
    </div>
</div>

<!-- Table -->

<div class="show-more" style="display: none;">
    <a class="btn btn-link" href="{{prev}}" id="prev_link">
        <i class="cpa cpa-angle-up"></i>
        <span>Показать предыдущие</span>
        <i class="cpa cpa-angle-up"></i>
    </a>
</div>

<div class="link-list-box">
    <table class="table table-striped table-link-list">
        <thead>
            <tr>
                <th></th>
                <th>Название ссылки</th>
                <th>Название оффера</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="rules_container"></tbody>
    </table>
</div>

<div class="show-more" style="display: none;">
    <a class="btn btn-link" href="{{next}}" id="next_link">
        <i class="cpa cpa-angle-down"></i>
        <span>Показать больше</span>
        <i class="cpa cpa-angle-down"></i>
    </a>
</div>

<div class="row">&nbsp;</div>