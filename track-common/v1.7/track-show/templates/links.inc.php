<?php
if (!$include_flag) {
    exit();
}

global $page_headers, $page_type, $category_id, $arr_categories, $arr_offers, $cat_type,
 $delete_cat, $delete_category_info;
?><script>
    var last_removed = 0;     // id последнего удалённого офера
    var offer_sale_timer = 0; // таймер проверки на [SUBID] в поле ссылки нового офера
    var cat_id = <?php echo intval($category_id); ?>;
    var cat_type = '<?php echo _e($cat_type) ?>';
    var crtf_key = '<?php echo CSRF_KEY; ?>';
    
    function import_offers_from_network(id) {
        $('#networks_import_ajax').show();
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: { csrfkey: crtf_key, ajax_act: 'import_hasoffers_offers', id: id}
        }).done(function( msg ) {
            $('#networks_import_ajax').hide();
            $('#networks_import_status_text').html(msg);
            $('#networks_import_status').show();
        });
        return false;
    }
    
    // Правильно ли заполнена форма оффера?
    function check_add_offer() {	
        var offer_url=$('input[name="link_url"]', $('#form_add_offer'));
        $(offer_url).css('background-color', 'white');		
        if ($(offer_url).val()=='') {
            $(offer_url).css('background-color','lightyellow');
            $(offer_url).focus();
            return false;	
        }
        return true;
    }	
    
    // Форма редактирования категории
    function show_category_edit() {
        if ($('.category_edit').css('display')=='none')	{
            $('.category_edit').show();
            $('.category_edit input[name=category_name]').focus().select();
            //$('#category_title').select();
            $('.category_title').hide();
        } else {
            $('.category_edit').hide();
            $('.category_title').show();
        }
        return false;
    }
	
    // Правильно ли отредактирована категория?
    function check_category_edit() {
        if ($('.category_edit input[name=is_delete]').val()=='1') {
            return true;
        }

        if ($('.category_edit input[name=category_name]').val()=='') {
            return false;
        }
        return true;
    }
	
    // Удаление категории
    function delete_category() {
        $('#form_category_edit input[name=is_delete]').val('1');
        $('#form_category_edit').submit();
        return false;
    }
    
    // Отмеченные галочками оферы (вернёт массив)
    function checked_offers() {
        var checked_offers_arr = [];
        $('.offer_checkbox:checked').each(function() {
            checked_offers_arr.push($(this).attr('id').replace('chk', ''));
        });
        return checked_offers_arr;
    }
    
    // Удаление отмеченных галками оферов
    function delete_links() {
        action_arr = checked_offers();
        if(action_arr.length > 0) {
            delete_link(action_arr);
            $('.show-if-offer-checked').hide(); // теперь ничего не отмечено, скрываем кнопки
        }
        return false;
    }

    // Удаление конкретного офера
    function delete_link(id) {
        last_removed = id;
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey='+crtf_key+'&ajax_act=delete_link&cat_type='+cat_type+'&cat_id='+cat_id+'&id='+(typeof(id) != 'object' ? id : id.join(',')) 
        }).done(function(msg) {
            if(typeof(id) == 'object') {
                for(i in id) {
                    $('#linkrow-' + id[i]).hide();
                }
                if(id.length > 1) {
                    $('#remove_alert .alert-text').html(id.length + ' оффер' + numform(id.length, ['', 'а', 'ов']) +  ' были удален' + numform(id.length, ['', 'ы', 'ы']) + ', вы можете их');
                } else {
                    $('#remove_alert .alert-text').html('Оффер &laquo;' + offer_name(id[0]) + '&raquo; был удален, вы можете его');
                }
            } else {
                $('#linkrow-' + id).hide();
                $('#remove_alert .alert-text').html('Оффер &laquo;' + offer_name(id) + '&raquo; был удален, вы можете его');
            }
            response = eval('(' + msg + ')');
            $('#offers_footer_total').html(response.total_html);
            $('#offers_footer_all').toggle(response.more);
            $('#offers_table').toggle(response.total > 0);
            $('#remove_alert').show();
            //console.log(response.more);
        });
        return false;
    }
    
    // Восстановление удаленных оферов
    function restore_link() {
       	var id = last_removed;
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey='+crtf_key+'&ajax_act=restore_link&cat_type='+cat_type+'&cat_id='+cat_id+'&id='+(typeof(id) != 'object' ? id : id.join(',')) 
        }).done(function(msg) {
            if(typeof(id) == 'object') {
                for(i in id) {
                    $('#linkrow-' + id[i]).show();
                }
            } else {
                $('#linkrow-' + id).show();
            }
            response = eval('(' + msg + ')');
            $('#offers_footer_total').html(response.total_html);
            $('#offers_footer_all').toggle(response.more);
            $('#offers_table').toggle(response.total > 0);
            $('#remove_alert').hide();
        });
        last_removed = 0;
        return false;
    }
    
    // Перенос оферов в категорию
    function move_links_to_category(category_id) {
        action_arr = checked_offers();
        if(action_arr.length > 0) {
            move_link_to_category(action_arr.join(','), category_id);
        }
        return false;
    }
 
    // Перенос конкретного офера в категорию
    function move_link_to_category (offer_id, category_id) {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey='+crtf_key+'&ajax_act=move_link_to_category&offer_id='+offer_id+'&category_id='+category_id
        }).done(function(msg) {
            window.location.reload();
        });
        return false;
    }
    
    // Перенос отмеченных оферов в архив
    function arch_links(arch) {
        action_arr = checked_offers();
        arch_link(action_arr.join(','), arch);
        return false;
    }
    
    // Перенос одного офера в архив
    function arch_link(id, arch) {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey='+crtf_key+'&ajax_act=arch_link&id=' + id + '&arch=' + (arch ? 1 : 0)
        }).done(function(msg) {
            window.location.reload();
        });
        return false;
    }
    
    // Отметка офера как избранного
    function fave_link(id, fave) {
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: 'csrfkey='+crtf_key+'&ajax_act=fave_link&id=' + id + '&fave=' + (fave ? 1 : 0)
        }).done(function(msg) {
            response = eval('(' + msg + ')');
            $('#links_menu_favorits').toggle(response.have_favorits == 1);
        });
        return false;
    }
    
    // Запуск таймера проверки на [SUBID] в форме добавления офера
    function start_offer_sale_timer() {
    	
        if($('#add-offer-url').val().toUpperCase().indexOf('[SUBID]') >= 0) {
            if($('#add-offer-url').val().indexOf('[SUBID]') < 0) {
                $('#add-offer-url').val($('#add-offer-url').val().replace(/\[subid\]/i, '[SUBID]'));
            }
            $('#offer_sale').css('display', 'inline');
        } else {
            $('#offer_sale').css('display', 'none');
        }
    	
    	
        //$('#offer_sale').css('display', $('#add-offer-url').val().indexOf('[SUBID]') >= 0 ? 'inline' : 'none');
        offer_sale_timer = setTimeout('start_offer_sale_timer()', 500);
    }
    
    function offer_name(offer_id) {
        return $($('#linkrow-' + offer_id).find('td')[2]).text();
    }
    
    // Показ/сокрытие формы редактирования офера
    function toggle_offer_edit_form(offer_id) {
       
        $('#add-offer-name').val($($('#linkrow-' + offer_id).find('td')[2]).text());
        $('#add-offer-url').val($($('#linkrow-' + offer_id).find('td')[4]).text());
        $('#add-offer-id').val(offer_id);
        $('#add-offer-form-submit').html('<i class="icon icon-pencil"></i> Редактировать оффер');
       
        if($('#add_offer_form').css('display') != 'block') {
            $('#add_offer_form').show();
            start_offer_sale_timer();
        }
        return false;
    }
    
    // Показ/сокрытие формы добавления офера
    function toggle_offer_add_form(e) {
        e.stopPropagation();
        $('#add_offer_form').toggle();
        if($('#add_offer_form').css('display') == 'block') {
            $('#add-offer-name').val('');
            $('#add-offer-url').val('');
            $('#add-offer-id').val('0');
            $('#add-offer-name').focus();
            $('#add-offer-form-submit').html('<i class="icon icon-plus"></i> Добавить новый оффер');
            start_offer_sale_timer();
            $('#add_offer_form').click(function(e){
                e.stopPropagation();
            });
            $('body').click(function(e) {
                toggle_offer_add_form(e);
            });
        } else {
            $('#add_offer_form').find('form')[0].reset();
            clearTimeout(offer_sale_timer);
            
            $('body').unbind();
        }
        return false;
    }
    
    function init_add_cat() {
    	
        // Ссылка добавления категории
        $('#add_cat_link').click(function(event) {
            $('#add_cat_link').hide();
            $('#add_cat_form').show();
            $('#add_cat_name').focus();
            return false;
        });
        
        // esc для создания новой категории
        $('#add_cat_name').bind("keydown", function(event){
            if(event.which == 27) {
                $('#add_cat_link').show();
                $('#add_cat_form').hide();
                $('#add_cat_name').val('');
            }
        });
        
        $('#add_cat_link').show();
        $('#add_cat_form').hide();
    }
    
    function numform(n, expr) {
        i = n % 100;
        if (i >= 5 && i <= 20) {
            return expr[2];
        } else {
            i %= 10;
            if (i == 1) {
                res = expr[0];
            } else { 
                if (i >= 2 && i <= 4) {
                    res = expr[1];
                } else {
                    res = expr[2];
                }
            }
        }
        return res;
    }
	    
    function objk2arr(obj) {
        arr = [];
        for(i in obj) {
            arr.push(i);
        }
        return arr;
    }
    
    // Проверяем, нужно ли показывать "Всего офферов""
    function chk_tbl_footer() {
        $('#offers_footer').toggle($('#offers_table tbody').children().length > 1);
    }
    
    $(function() {
        $('#form_add_offer').bind("keydown", function(event){
            if(event.which == 27) {
                toggle_offer_add_form(event);
            }
            if(event.which == 13) {
                $('#form_add_offer').submit();
            }
        });
        
        // Убираем отметки (фикс глюка при обновлении страницы)
        $('.offer_checkbox').iCheck('uncheck');
    	
        // Редактирование офера из верхнего меню
        $('.sel-link-edit').click(function(event) {
            toggle_offer_edit_form(checked_offers()[0]);
            return false;
        });
    	
        // Показать кнопки действий, если отмечен хотя бы один оффер
        $('.offer_checkbox').on('ifChanged', function(e) {
            $('.show-if-offer-checked').toggle($('.offer_checkbox:checked').length > 0);
            
            // Редактирование оффера
            $('.sel-link-edit').toggle($('.offer_checkbox:checked').length == 1);
            
            // Выбираем уникальные категории
            var sel_categories = {};
            $('.offer_checkbox:checked').each(function(){
                id = $(this).attr('id').replace('chk', '');
                cat = $('#linkrow-' + id).attr('category');
                sel_categories[cat] = 1; 
            });
            sel_categories = objk2arr(sel_categories);
            $('.move2cat').show();
            if(sel_categories.length == 1) {
                $('.move2cat_' + sel_categories[0]).hide();
            }
        });
    	
        // Действие для галки "Отметить все"
        $('.check-all').on('ifChanged', function(e) {
            $('.offer_checkbox').iCheck(e.target.checked ? 'check' : 'uncheck');
        });
	
        // Звёзды избранного
        $('.i-star').on('ifChanged', function(e) {
            id = $(e.target).attr('id').replace('fav', '');
            fave_link(id, e.target.checked)
        });
	
        // esc для редактирования названия категории
        $('#category_name').bind("keydown", function(event){
            if(event.which == 27) {
                show_category_edit();
            }
        });
	
        // Редактирование категории
        $('#alert-warning-toggle').click(function(event) {
            show_category_edit();
            return false;
        });
	
        // Удаление категории
        $('#alert-danger-toggle').click(function(event) {
            delete_category();
            return false;
        });
	
        // Отменить добавление офера
        $('#add-offer-form-close').click(function(event) {
            return toggle_offer_add_form(event);
        });
	
        // Добавить новый офер
        $('#add-offer-form-submit').click(function(event) {
            $('#form_add_offer').submit();
        });
        
        init_add_cat(); 
        chk_tbl_footer(); // Проверка, нужно ли показывать футер и "Всего офферов" иже с ним
    });
</script>
<div class="alert alert-warning fade in alert-dismissible" role="alert" id="remove_alert">
    <button class="close" aria-label="Close" data-dismiss="alert" type="button">
        <span aria-hidden="true">×</span>
    </button>
    <span class="alert-text">Один или несколько объектов были удалены, Вы можете их</span> 
    <a class="alert-link" href="#" onClick="return restore_link();"><strong><u>восстановить</u></strong></a>
</div>
<?php
// Была удалена категория
if (!empty($delete_category_info['id'])) {
    ?><div class="alert alert-warning fade in alert-dismissible" role="alert">
        <button class="close" aria-label="Close" data-dismiss="alert" type="button">
            <span aria-hidden="true">×</span>
        </button>
        <strong>Внимание!</strong> 
        Категория  &laquo;<strong><?php echo $delete_category_info['category_caption'] ?></strong>&raquo; была удалена, Вы можете её 
        <a class="alert-link" href="?page=links&ajax_act=category_edit&is_delete=0&category_id=<?php echo $delete_category_info['id']; ?>&csrfkey=<?php echo CSRF_KEY; ?>"><strong><u>восстановить</u></strong></a>
    </div>
    <?php
}

if ($page_type == 'network') {
    echo "<p align=right><img style='margin-right:15px; display: none;' id='networks_import_ajax' src='img/icons/ajax.gif'><span class='btn' onclick='import_offers_from_network(\"" . _e($category_id) . "\")'>Импорт офферов</span></p>";
    echo "<div class='alert' id='networks_import_status' style='display:none;'>
			<button type='button' class='close' data-dismiss='alert'>&times;</button>
			<strong id='networks_import_status_text'></strong>
		  </div>";
} else {
    ?>

    <!-- Page heading -->
    <div class="page-heading">
        <p><?php echo $page_headers[0]; ?></p>
        <div class="header-content">

            <!--Header-->
            <div class="btn-group header-left">
                <h2>
                    <div class="category_edit">
                        <form class="form-inline" role="form" method="post" id="form_category_edit" onsubmit="return check_category_edit();">
                            <input type="hidden" name="ajax_act" value="category_edit">
                            <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
                            <input type="hidden" name="category_id" value="<?php echo _e($_REQUEST["category_id"]); ?>">
                            <input type="hidden" name="is_delete" value="-1">
                            <input type="text" class="form-control" name="category_name" id="category_name" placeholder="Название категории" value="<?php echo _e($page_headers[1]); ?>">
                        </form>
                    </div>
                    <div class="category_title"><?php
    echo $page_headers[1];
    if ($page_headers[0] != '') {
        ?>
                            <a id="alert-warning-toggle" href="#"><i class="icon icon-edit"></i></a>
                            <a id="alert-danger-toggle" href="#"><i class="icon icon-trash"></i></a>
                        <?php } ?></div>

                </h2>
            </div>

            <!--Left buttons-->
            <div role="toolbar" class="btn-toolbar">
                <div class="btn-group dropdown show-if-offer-checked">
                    <a class="btn btn-default" href="#" data-toggle="dropdown">
                        <i class="icon icon-folder" data-placement="top" data-toggle="tooltip" data-original-title="переместить в категорию"></i>
                        <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <?php
                        foreach ($arr_categories as $cur) {
                            // Отвечать за сок
                            //if ($category_id != $cur['id'] or ($cur['id'] == 0 and $cat_type == 'favorits'))

                            echo '<li class="move2cat move2cat_' . $cur['id'] . '"><a class="dropdown-link" href="#" onclick="return move_links_to_category(' . $cur['id'] . ');">' . _e($cur['category_caption']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="btn-group show-if-offer-checked">
                    <a class="btn btn-default single-icon" href="#" onclick="return arch_links(<?php echo $cat_type == 'archive' ? 0 : 1; ?>)" data-placement="top" data-toggle="tooltip" data-original-title="<?php echo $cat_type == 'archive' ? 'достать из архива' : 'переместить в архив'; ?>">
                        <i class="icon icon-drawer"></i>
                    </a>
                    <a class="btn btn-default single-icon sel-link-edit" href="fakelink" data-placement="top" data-toggle="tooltip" data-original-title="редактировать">
                        <i class="icon icon-pencil"></i>
                    </a>
                    <a class="btn btn-default single-icon" href="#" onclick="return delete_links();" data-placement="top" data-toggle="tooltip" data-original-title="удалить">
                        <i class="icon icon-trash-gray"></i>
                    </a>
                </div>


                <!--Right buttons-->
                <!--					<div class="btn-group pull-right">
                                                                <a class="btn btn-default" href="fakelink"><i class="fa fa-angle-double-down"></i></a>
                                                        </div>-->
                <?php if ($cat_type == 'all' or $cat_type == 'favorits') { ?>
                    <div class="btn-group pull-right">
                        <a class="btn btn-default single-icon" href="#" onclick="return toggle_offer_add_form(event)"><i class="icon icon-plus"></i></a>
                    </div>
                <?php } ?>
            </div><!--Toolbar-->
        </div><!--Header-content-->
    </div><!--page-heading-->


    <div id="add_offer_form" style="display: none; margin-bottom: 10px;">
        <div class="container-fluid">
            <div class="row">
                <form class="form-horizontal offer-form" novalidate="novalidate" role="form" onSubmit="return check_add_offer();" id="form_add_offer">
                    <input type="hidden" name="ajax_act" value="add_offer">
                    <input type="hidden" name="link_id" id="add-offer-id" value="0">
                    <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
                    <input type="hidden" name="category_id" value="<?php echo _e($category_id); ?>">

                    <!-- Offer name-->
                    <div class="form-group">
                        <div class="col-sm-2">
                            <label class="control-label pull-left" for="add-offer-name">Название</label>
                        </div>				
                        <div class="col-sm-10">
                            <input type="text" placeholder="Введите название оффера" class="form-control" name="link_name" id="add-offer-name">
                        </div>
                    </div>

                    <!-- Offer URL-->
                    <div class="form-group">
                        <div class="col-sm-2">
                            <label class="control-label pull-left" for="add_offer-url">URL</label>
                        </div>				
                        <div class="col-sm-10">
                            <input type="text" placeholder="Введите URL" class="form-control" name="link_url" id="add-offer-url">
                            <span class="help-block small pull-left">Для использования SubId добавьте [SUBID] в URL</span>
                            <span class="help-block small pull-right" id="offer_sale"><i class="icon icon-one"></i>Учет продаж включен</span>
                        </div>
                    </div>

                    <!--Buttons-->
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="btn-toolbar">
                                <div class="pull-left btn-group ">
                                    <a id="add-offer-form-submit" href="#" class="btn btn-default"><i class="icon icon-plus"></i>Добавить новый оффер</a>
                                </div>
                                <div class="pull-left btn-group ">
                                    <a id="add-offer-form-close" href="#" class="btn btn-cancel"><i class="icon icon-cancel"></i>Отмена</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div><!-- /.row -->
        </div><!--container-->
    </div>

    <?php
}

if (count($arr_offers['data']) > 0) {
    ?>
    <!-- Table -->
    <div class="table-box" id="offers_table">

        <table class="table table-th-block table-striped table-hover table-check">
            <tbody>
                <?php
                foreach ($arr_offers['data'] as $cur) {
                    //dmp($cur);

                    $tracking_url = str_replace(array('http://', 'https://'), '', $cur['offer_tracking_url']);
                    $total_visits = intval($offers_stats_array[$cur['offer_id']]);
                    ?>
                    <!--row-->
                    <tr id="linkrow-<?php echo $cur['offer_id']; ?>" category="<?php echo $cur['category_id'] ?>">
                        <td>
                            <form role="form">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="" class="i-blue offer_checkbox" id="chk<?php echo $cur['offer_id']; ?>">
                                    </label>
                                </div>
                            </form>
                        </td>
                        <td>
                            <form role="form">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" value="" class="i-star" id="fav<?php echo $cur['offer_id']; ?>" <?php
            if ($cur['status'] == 3) {
                echo 'checked="checked"';
            }
                    ?>>
                                    </label>
                                </div>
                            </form>
                        </td>
                        <td><?php echo _e($cur['offer_name']); ?></td>
                        <td><?php
                                if (strstr($tracking_url, '[SUBID]') !== false) {
                                    echo '<i class="icon icon-one"></i>';
                                }
                    ?></td>
                        <td><?php echo $tracking_url; ?></td>
                        <td class="dropdown-cell">
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle a-default" data-toggle="dropdown" role="button" aria-expanded="false">
                                    <i class="cpa cpa-bars"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                        <?php if ($cat_type == 'archive') { ?>
                                            <a class="dropdown-link" href="#" onclick="return arch_link(<?php echo $cur['offer_id']; ?>, 0)">Из  архива</a>
                                        <?php } else { ?>
                                            <a class="dropdown-link" href="#" onclick="return arch_link(<?php echo $cur['offer_id']; ?>, 1)">В архив</a>
                                        <?php } ?>
                                    </li>
                                    <li>
                                        <a class="dropdown-link" href="#" onclick="return toggle_offer_edit_form(<?php echo $cur['offer_id']; ?>)">Изменить</a>
                                    </li>
                                    <li class="dropdown-footer text-danger">
                                        <a class="dropdown-link text-danger" href="#" onclick="return delete_link(<?php echo _e($cur['offer_id']); ?>)"><i class="icon icon-abs icon-trash"></i>Удалить</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php }
                ?>
            </tbody>
            <tfoot id="offers_footer">
                <tr>
                    <td>
                        <form role="form">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="" class="i-blue check-all" name="check-all">
                                </label>
                            </div>
                        </form>
                    </td>
                    <td colspan="3" id="offers_footer_total"><?php echo tpx('links_table_total', $arr_offers); ?></td>
                    <td colspan="2" class="text-right"><a class="hover-underline" href="#" id="offers_footer_all" <?php if ($arr_offers['more'] == 0) echo 'style="display: none;"' ?>>Показать все</a></td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php } ?>

<?php
if (empty($arr_offers) || count($arr_offers['data'])==0)
{
?>
    <div class="row" style="margin-top:30px;">
        <div class="col-sm-12">
            <p style="font-size:16px;">Вы можете отслеживать эффективность собственных сайтов, целевых страниц, групп ВКонтакте, мобильных приложений, страниц-прокладок, офферов в CPA сетях и любых других интернет-страниц, на которые можно перейти из браузера. Все эти объекты в системе мы называем &laquo;офферы&raquo;, от английского слова offer (предложение).</p>
            <p style="font-size:16px;">
                Начните работу с CPA Tracker с <span style="color:#15c; cursor:pointer; text-decoration:underline;" onclick="return toggle_offer_add_form(event);">добавления</span> вашего первого оффера.
            </p>
        </div>
    </div>
<?php
}

if(count($arr_offers['data'])==1)
{
    $sql="select id from tbl_rules where status=0 limit 1";
    $result=mysql_query($sql);
    $row=mysql_fetch_assoc($result);

    if ($row['id']>0){;}else
    {
        ?>
        <div class="row" style="margin-top:30px;">
            <div class="col-sm-12">
                <p style="font-size:16px;">Вы добавили оффер в систему. Теперь нужно создать ссылку, с помощью которой вы будете отслеживать переходы.<br />Перейдите в раздел &laquo;<a style="color:#15c; cursor:pointer; text-decoration:underline;" href="<?php echo _HTML_ROOT_PATH; ?>/?page=rules">Ссылки</a>&raquo;, введите название ссылки и нажмите на кнопку &laquo;Добавить&raquo;.</p>
                <p style="font-size:16px;">В названии ссылки можно использовать английские буквы, цифры, дефис и знак подчеркивания «_». Это название будет являться частью вашей ссылки для отслеживания, примерно так: http://www.jmp1.ru/12345/<span style="color:red;">link1</span>/campaign-ads</p>
                <p style="font-size:16px;">После добавления выберите в левом меню раздела &laquo;Ссылки&raquo; рекламую систему или &laquo;Универсальную ссылку&raquo;, если вы будете размещать ссылку на сайтах или группах в социальных сетях, скопируйте ссылку в буфер обмена и используйте ее для перенаправления посетителей.</p>
            </div>
        </div>
    <?php
    }

}
?>
