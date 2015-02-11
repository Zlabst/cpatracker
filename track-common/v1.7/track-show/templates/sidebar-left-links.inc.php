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
	    add_form = submenu.children().last();
	    submenu.html(submenu.children().splice(0, submenu.children().length - 1));
	    submenu.append('<li><a href="?page=links&category_id='+category_id+'">'+htmlEncode(category_name)+'</a></li>');
	    submenu.append(add_form);
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
/* ?>
  <div class="col-md-3" id="categories_left_menu">
  <div class="bs-sidebar hidden-print affix-top">
  <ul class="nav bs-sidenav" id='categories_left_menu_list'>
  <?php
  $result=get_links_categories_list();
  $arr_categories=$result['categories'];
  $arr_categories_count=$result['categories_count'];

  if ($_REQUEST['category_id']=='' || $_REQUEST['category_id']==0){$class=" class='active'";}else{$class='';}
  echo "<li {$class}>";
  echo "<a href='?page=links'>Без категории";
  if ($arr_categories_count[0]>0)
  {
  echo "<span class='category_count'>"._e($arr_categories_count[0])."</span>";
  }
  echo "</a>";
  echo "</li>";

  $cur_category_group='{empty}';

  foreach ($arr_categories as $cur)
  {
  if ($cur_category_group!=$cur['category_type'])
  {
  $cur_category_group=$cur['category_type'];
  switch ($cur_category_group)
  {
  case 'network':
  echo "<li class='nav-header'>CPA сети</li>";
  break;

  default:

  break;
  }
  }
  if ($_REQUEST['category_id']==$cur['id']){$class="class='active'";}else{$class="";}
  echo "<li {$class}>";
  echo "<a href='?page=links&category_id="._e($cur['id'])."'>";
  echo _e($cur['category_caption']);
  if ($arr_categories_count[$cur['id']]>0)
  {
  echo "<span class='category_count'>"._e($arr_categories_count[$cur['id']])."</span>";
  }
  echo "</a>";

  echo "</li>";
  }
  ?>
  </ul>
  <div class='links_category_add'>
  <hr />
  <span class='btn-link' onclick="toggle_add_category_form()">+ новая категория</span>
  </div>


  <form class="form-inline links_category_add_form" role="form" method="post" onsubmit='return add_new_category()' id='category_add_form'>
  <input type='hidden' name='ajax_act' value='add_category'>

  <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
  <div class="form-group">
  <input type="text" class="form-control" name="category_name" placeholder="Название категории">
  </div>
  <button type="submit" class="btn btn-default">+</button>
  </form>

  </div>
  <?php
  echo load_plugin('demo', 'demo_well');
  ?>
  </div>

  <? */

$cat_types = array(
    'favorits' => 'Избранное',
    'all' => 'Все офферы',
    'archive' => 'Архив'
);
$cat_type = rq('type');
if (empty($cat_type) or !array_key_exists($cat_type, $cat_types)) {
    $cat_type = 'all';
}

$have_favorits = offers_have_status(3);
$have_archive = offers_have_status(2);
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
	<?php if ($have_favorits) { ?>
    	<li <?php
	if ($cat_type == 'favorits') {
	    echo 'class="active"';
	}
	    ?> >
    	    <a href="?page=links&type=favorits">Избранное</a>
    	</li>
		<?php } ?>
	<li <?php
		if ($cat_type == 'all') {
		    echo 'class="active"';
		}
		?>>
		<?php
		$result = get_links_categories_list();
		$arr_categories = array_merge(
			array(
		    array(
			'id' => 0,
			'category_caption' => 'Без категории'
		    )
			), $result['categories']
		);

		$arr_categories_count = $result['categories_count'];

		echo '<a href="#" id="lnk_all_offers">Все офферы</a><ul id="submenu_all_offers" class="submenu" ' . ((($cat_group == 'all' and $_COOKIE['cpa_menu_offers_all'] !== '0') or $_COOKIE['cpa_menu_offers_all'] == '1') ? 'style="display: block;"' : '') . '>';
		foreach ($arr_categories as $cur) {
		    $highlight = ($_REQUEST['category_id'] == $cur['id'] and $cat_type == 'all') ? ' class="active"' : '';
		    echo '<li' . $highlight . '><a href="?page=links&category_id=' . _e($cur['id']) . '">' . _e($cur['category_caption']) . '<span class="span-sidebar">' . _e($arr_categories_count[$cur['id']]) . '</span></a></li>';
		}

		echo '<li>
<div class="input has-feedback">
<form class="form-inline links_category_add_form" role="form" method="post" onsubmit="return add_new_category()" id="category_add_form">
<input type="hidden" name="ajax_act" value="add_category">
<input type="hidden" name="csrfkey" value="' . CSRF_KEY . '">
<input class="form-control form-control-alt" type="text" placeholder="Добавить категорию" name="category_name" >
<span class="form-control-feedback single-icon">
<i class="icon icon-plus-small "></i>
</span>
</form>
</div>
</li>';

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
	