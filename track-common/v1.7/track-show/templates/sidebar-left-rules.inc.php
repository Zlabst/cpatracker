<?php
if (!$include_flag) {
    exit();
}
?>
<script>
    var crtf_key = '<?php echo CSRF_KEY; ?>';
	
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
        
        // Отметка правила как избранного
        function fave_source(id, fave) {
            $.ajax({
                type: 'POST',
                url: 'index.php',
                data: 'csrfkey='+crtf_key+'&ajax_act=fave_source&id=' + id + '&fave=' + (fave ? 1 : 0)
            }).done(function(msg) {
                response = eval('(' + msg + ')');
                $('#rules_menu_favorits').toggle(response.have_favorits == 1);
                if(fave) {
                    lnk = $('#li_' + id).clone().attr('id', 'li_fave_' + id)
                    lnk.find('a').attr('href', lnk.find('a').attr('href') + '&fav=1');
                    lnk.appendTo('#rules_favorits');
                } else {
                    $('#li_fave_' + id).remove();
                }
	            
            });
            return false;
        }
	    
        // Звёзды избранного
        $('.i-star').on('ifChanged', function(e) {
            id = $(e.target).attr('id').replace('fav', '');
            fave_source(id, e.target.checked)
        });
    });
    
</script>
<?
// Проверка на наличие избранного
$sources_favorits = sources_favorits();
$have_favorits = count($sources_favorits) > 0;

$source = rq('source'); // выбран источник
$select_favorits = rq('fav', 2);    // выбрана категория Избранное

if (($cat_type == 'favorits' and !$have_favorits)) {
    redirect(_HTML_ROOT_PATH . '/?page=rules');
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



        <li id="rules_menu_favorits"<?php
if ($cat_type == 'favorits') {
    echo ' class="active"';
}
if (!$have_favorits) {
    echo ' style="display: none"';
}
?>>
            <a href="?page=links&type=favorits">Избранное</a>
            <ul class="submenu" id="rules_favorits" <?php echo $select_favorits ? 'style="display: block"' : '' ?>>
                <?
                foreach ($sources_favorits as $stv) {
                    echo '<li class="checkable '.($source == $stv ? 'active' : '').'" id="li_fave_' . $stv . '">
						<a href="?page=rules&source=' . $stv . '&fav=1">' . $source_config[$stv]['name'] . '</a>
					</li>';
                }
                ?>    		
            </ul>
        </li>

        <li class="">
            <a href="?page=rules">Универсальная ссылка</a>
        </li>
        <?php
        //echo $select_favorits ? 1 : 0;
        //dmp($source_types);
        foreach ($source_types as $st) {
            echo '<li><a href="#fakelink">' . $st['name'] . '</a>';

            echo '<ul class="submenu " ' . ((empty($select_favorits) and in_array($source, $st['values'])) ? 'style="display: block"' : '') . '>';
            foreach ($st['values'] as $stv) {
                echo '<li class="checkable '.($source == $stv ? 'active' : '').'" id="li_' . $stv . '">
						<div class="checkbox">
							<input type="checkbox" value="" class="i-star" id="fav' . $stv . '" ' . (in_array($stv, $sources_favorits) ? 'checked' : '') . '>
						</div>
						<a href="?page=rules&source=' . $stv . '">' . $source_config[$stv]['name'] . '</a>
					</li>';
            }
            echo '</ul>';

            echo '</li>';
        }
        ?>    
    </ul><!--sidebar-menu-->
</div><!-- /.sidebar-left -->
<!-- END SIDEBAR LEFT -->
<?php
echo load_plugin('demo', 'demo_well');