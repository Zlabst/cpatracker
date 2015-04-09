<?php
if (!$include_flag) {
    exit();
}
?>
<script>
    function add_new_category() {
        var category_name = $('.links_category_add_form input[name=category_name]').val();
        if (category_name=='') {
            $('.links_category_add_form input[name=category_name]').focus();
            return false;
        }

        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: $('#category_add_form').serialize()
        }).done(function( category_id ) 
        {
            //$('#categories_left_menu_list').append('<li><a href="?page=links&category_id='+category_id+'">'+htmlEncode(category_name)+'</a></li>');
            $('.links_category_add_form input[name=category_name]').focus();
            $('#category_add_form')[0].reset();
			
            submenu = $('#submenu_all_offers');
            //add_form = submenu.children().last();
            add_form = submenu.children().splice(submenu.children().length - 2);
            submenu.html(submenu.children().splice(0, submenu.children().length - 2));
            submenu.append('<li><a href="?page=links&category_id='+category_id+'">'+htmlEncode(category_name)+'</a></li>');
            submenu.append(add_form);
            init_add_cat();
        });

        return false;
    }
    function toggle_add_category_form() {
        $('.links_category_add_form').toggle();
        switch ($('.links_category_add_form').css('display')){
            case 'none':
                $('#category_add_form')[0].reset();
                break;

            default: 
                $('.links_category_add_form input[name=category_name]').focus();
                break;
        }
    }

    function htmlEncode(value){
        if (value) {
            return jQuery('<div />').text(value).html();
        } else {
            return '';
        }
    }
	
    $(function() {
        // Отслеживаем состояние меню
        $('#lnk_all_offers').on('click', function (e) {
            show = $('#submenu_all_offers').css('display') == 'none';
            $.cookie('cpa_menu_offers_all', show ? 1 : 0, {expires: 7});
            if(show) {
                window.location.href = $('#submenu_all_offers').children().first().find('a').attr('href');
            }
            return false;
        });
    });
</script>
<?
// Проверка на наличие избранного
$have_favorits = offers_have_status(3);

if (($cat_type == 'favorits' and !$have_favorits)) {
    redirect(_HTML_ROOT_PATH . '/?page=links');
}

/* Проверка на наличие архива (второй раз потому, что первый редирект будет 
 * срабатывать довольно часто, и чтобы не считать впустую offers_have_status) */
$have_archive = offers_have_status(2);

if (($cat_type == 'archive' and !$have_archive)) {
    redirect(_HTML_ROOT_PATH . '/?page=links');
}
?><!-- BEGIN SIDEBAR LEFT -->
<div class="sidebar-left<?php echo $menu_toggle_class; ?>">

    <!-- Button sidebar left toggle -->
    <div class="btn-collapse-sidebar-left icon-dynamic<?php echo $menu_icon_class; ?>"  data-toggle="tooltip" data-placement="bottom" title="Свернуть левое меню"></div>

    <ul class="sidebar-menu"<?php echo $menu_sidebar_style; ?>>
        <li>
            <a class="logo-brand" href="<?php echo _HTML_ROOT_PATH; ?>">
                <span>CPA </span>Tracker
            </a>
        </li>

        <li id="links_menu_favorits"<?php
if ($cat_type == 'favorits') {
    echo ' class="active"';
}
if (!$have_favorits) {
    echo ' style="display: none"';
}
?>>
            <a href="?page=links&type=favorits">Избранное</a>
        </li>
        <li <?php
            if ($cat_type == 'all') {
                echo 'class="active"';
            }
?>>
                <?php
                echo '<a href="#" id="lnk_all_offers">Все офферы</a><ul id="submenu_all_offers" class="submenu" ' . ((($cat_group == 'all' and $_COOKIE['cpa_menu_offers_all'] !== '0') or $_COOKIE['cpa_menu_offers_all'] == '1') ? 'style="display: block;"' : '') . '>';
                foreach ($arr_categories as $cur) {
                    $highlight = ($_REQUEST['category_id'] == $cur['id'] and $cat_type == 'all') ? ' class="active"' : '';
                    echo '<li' . $highlight . '><a href="?page=links&category_id=' . _e($cur['id']) . '">' . _e($cur['category_caption']) . '<span class="span-sidebar">' . _e($arr_categories_count[$cur['id']]) . '</span></a></li>';
                }

                echo '

<form class="links_category_add_form" role="form" method="post" onsubmit="return add_new_category()" id="category_add_form">
<li id="add_cat_form" style="display: list-item;">
<div class="input has-feedback">
<input type="hidden" name="ajax_act" value="add_category">
<input type="hidden" name="csrfkey" value="' . CSRF_KEY . '">
<input class="form-control form-control-alt" type="text" placeholder="Добавить категорию" name="category_name" id="add_cat_name">
<span class="form-control-feedback single-icon">
<i class="icon icon-plus-small "></i>
</span>

</div>
</li></form>';

                echo '<li><a href="#addcategory" id="add_cat_link">Новая категория<span class="span-sidebar"><i class="fa fa-plus-square-o"></i></span></a></li>';

                echo '</ul>';
                ?>
        </li>
        <?php if ($have_archive) { ?>
            <li <?php
        if ($cat_type == 'archive') {
            echo 'class="active"';
        }
            ?>>
                <a href="?page=links&type=archive">Архив</a>
            </li>
        <?php } ?>
    </ul><!--sidebar-menu-->
</div><!-- /.sidebar-left -->
<!-- END SIDEBAR LEFT -->
<?php
echo load_plugin('demo', 'demo_well');
?>
	