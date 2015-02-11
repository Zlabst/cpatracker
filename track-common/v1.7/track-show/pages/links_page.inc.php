<?php
if (!$include_flag) {
    exit;
}
$category_id = rq('category_id');
$category_name = '{empty}';

$arr_offers = array();
if ($category_id > 0) {
    $offers_stats_array = array();
    $sql = "select id, category_caption, category_name, category_type from tbl_links_categories_list where id='" . mysql_real_escape_string($category_id) . "'";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);

    if ($row['id'] > 0) {
	$category_name = $row['category_caption'];
    } else {
	// Category id not found
	header("Location: " . full_url() . '?page=links');
	exit();
    }

    switch ($row['category_type']) {
	case 'network':
	    $page_type = 'network';

	    // Get network ID
	    $sql = "select id from tbl_cpa_networks where network_category_name='" . mysql_real_escape_string($row['category_name']) . "'";
	    $result = mysql_query($sql);
	    $row = mysql_fetch_assoc($result);
	    $network_id = $row['id'];

	    // Get list of offers from network
	    $sql = "select * from tbl_offers where network_id='" . mysql_real_escape_string($network_id) . "' and status=0 order by date_add desc, id asc";
	    $result = mysql_query($sql);
	    while ($row = mysql_fetch_assoc($result)) {
		$arr_offers[] = $row;
	    }
	    break;

	default:
	    // Get list of offers in category
	    $sql = "select tbl_offers.* 
		from tbl_offers 
		left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id 
		where tbl_links_categories.category_id='" . mysql_real_escape_string($category_id) . "' and tbl_offers.network_id='0' 
		    and tbl_offers.status IN (0,3)
		order by tbl_offers.date_add desc, tbl_offers.id asc";
	    $result = mysql_query($sql);
	    $arr_offers = array();
	    $offers_id = array();
	    while ($row = mysql_fetch_assoc($result)) {
		$row['offer_id'] = $row['id'];
		$offers_id[] = "'" . mysql_real_escape_string($row['id']) . "'";
		$arr_offers[] = $row;
	    }
	    $offers_id_str = implode(',', $offers_id);

	    $sql = "select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
	    $result = mysql_query($sql);
	    $offers_stats_array = array();
	    while ($row = mysql_fetch_assoc($result)) {
		$offers_stats_array[$row['out_id']] = $row['cnt'];
	    }
	    break;
    }
} else {
    switch ($cat_type) {
	case 'favorits':
	    $cond_status = 'tbl_offers.status = 3';
	    break;
	case 'archive':
	    $cond_status = 'tbl_offers.status = 2';
	    break;
	default:
	    $cond_status = 'tbl_offers.status IN (0,3)';
	    break;
    }

    // Get list of offers without category
    $sql = "select tbl_offers.* 
		from tbl_offers 
		left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id 
		where tbl_links_categories.id IS NULL and tbl_offers.network_id='0' 
			and " . $cond_status . " 
			order by tbl_offers.date_add desc, tbl_offers.id asc";
    $result = mysql_query($sql);
    $arr_offers = array();
    $offers_id = array();
    while ($row = mysql_fetch_assoc($result)) {
	$row['offer_id'] = $row['id'];
	$offers_id[] = "'" . mysql_real_escape_string($row['id']) . "'";
	$arr_offers[] = $row;
    }
    $offers_id_str = implode(',', $offers_id);

    $sql = "select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
    $result = mysql_query($sql);
    $offers_stats_array = array();
    while ($row = mysql_fetch_assoc($result)) {
	$offers_stats_array[$row['out_id']] = $row['cnt'];
    }
}
?>
<script>
    
    var last_removed = 0;
    function import_offers_from_network(id)
    {
	$('#networks_import_ajax').show();
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: { csrfkey: '<?php echo CSRF_KEY; ?>',ajax_act: 'import_hasoffers_offers', id: id}
	}).done(function( msg ) 
	{
	    $('#networks_import_ajax').hide();
	    $('#networks_import_status_text').html(msg);
	    $('#networks_import_status').show();
	});
    }
	
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
    function show_category_edit() {
	if ($('.category_edit').css('display')=='none')	{
	    $('.category_edit').show();
	    $('.category_edit input[name=category_name]').focus();
	    $('.category_title').hide();
	} else {
	    $('.category_edit').hide();
	    $('.category_title').show();
	}
	return false;
    }
	
    function check_category_edit() {
	if ($('.category_edit input[name=is_delete]').val()=='1')
	{
	    return true;
	}

	if ($('.category_edit input[name=category_name]').val()=='')
	{
	    return false;
	}

	return true;
    }
	
    function delete_category() {
	$('#form_category_edit input[name=is_delete]').val('1');
	$('#form_category_edit').submit();
    }
	
    function checked_offers() {
	var checked_offers_arr = [];
	$('.offer_checkbox:checked').each(function() {
	    checked_offers_arr.push($(this).attr('id').replace('chk', ''));
	})
	return checked_offers_arr;
    }
	
    function delete_links() {
	action_arr = checked_offers();
	if(action_arr.length == 0) {
	    alert('Ошибка! Не выбрано ни одного оффера!');
	} else {
	    delete_link(action_arr);
	}
	return false;
    }

    function delete_link(id) {
        last_removed = id;
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=delete_link&id='+(typeof(id) != 'object' ? id : id.join(',')) 
	}).done(function( msg ) {
	    if(typeof(id) != 'object') {
		$('#linkrow-' + id).hide();
	    } else {
		for(i in id) {
		    $('#linkrow-' + id[i]).hide();
		}
	    }
	});

	return false;
    }
	
    function move_links_to_category(category_id) {
	action_arr = checked_offers();
	if(action_arr.length == 0) {
	    alert('Ошибка! Не выбрано ни одного оффера!');
	} else {
	    move_link_to_category(action_arr.join(','), category_id);
	}
	return false;
    }
	
    function move_link_to_category (offer_id, category_id) {
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=move_link_to_category&offer_id='+offer_id+'&category_id='+category_id
	}).done(function(msg) {
	    window.location.reload();
	});
	return false;
    }
	
    function arch_links(arch) {
	action_arr = checked_offers();
	arch_link(action_arr.join(','), arch);
	return false;
    }
    
    function arch_link(id, arch) {
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=arch_link&id=' + id + '&arch=' + (arch ? 1 : 0)
	}).done(function(msg) {
	    window.location.reload();
	});
	return false;
    }
    
    function fave_link(id, fave) {
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=fave_link&id=' + id + '&fave=' + (fave ? 1 : 0)
	}).done(function(msg) {});
	return false;
    }
    
    function restore_link() {
       	var id = last_removed;
	$.ajax({
	    type: 'POST',
	    url: 'index.php',
	    data: 'csrfkey=<?php echo CSRF_KEY; ?>&ajax_act=restore_link&id='+id
	}).done(function(msg) {
            $('#linkrow-' + id).show();
            $('#remove_alert').hide();
	});
        last_removed = 0;
	return false;
    }
    
    var offer_sale_timer = 0;
    
    function start_offer_sale_timer() {
	$('#offer_sale').css('display', $('#add-offer-url').val().indexOf('[SUBID]') >= 0 ? 'inline' : 'none');
	offer_sale_timer = setTimeout('start_offer_sale_timer()', 500);
	console.log('st');
    }
    
    function toggle_offer_add_form() {
	$('#add_offer_form').toggle();
	if($('#add_offer_form').css('display') == 'block') {
	    start_offer_sale_timer();
	} else {
	    $('#add_offer_form').find('form')[0].reset();
	    clearTimeout(offer_sale_timer);
	}
	return false;
    }
    
    $(function() {
	// Убираем отметки (фикс глюка при обновлении)
	$('.offer_checkbox').iCheck('uncheck');
    	
	// Показать кнопки действий, если отмечена хотя бы один оффер
	$('.offer_checkbox').on('ifChanged', function(e) {
	    $('.show-if-offer-checked').toggle($('.offer_checkbox:checked').length > 0)
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
	
	$('#add-offer-form-close').click(function(event) {
	    return toggle_offer_add_form();
	});
	
	$('#add-offer-form-submit').click(function(event) {
	    $('#form_add_offer').submit();
	});
	
	
    });
</script>
<?php
if ($category_name == '{empty}') {
    $page_headers[0] = '';
    $page_headers[1] = $cat_types[$cat_type];
} else {
    $page_headers[0] = $cat_types[$cat_type];
    $page_headers[1] = $category_name;
}

/*
  ?>
  <?php

  echo "<div class='category_title' onclick='show_category_edit()'><span class='category_name'>"._e($category_name)."</span> <i class='fa fa-edit'></i></div>";
  echo "<div class='category_edit'>";
  ?>
  <div class='category_edit'>

 * 
 * 
  <div class="row">
  <form class="form-inline" role="form" method='post' id='form_category_edit' onsubmit='return check_category_edit();'>
  <input type='hidden' name='ajax_act' value='category_edit'>
  <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
  <input type='hidden' name='category_id' value='<?php echo _e($_REQUEST['category_id']);?>'>
  <input type='hidden' name='is_delete' value='0'>

  <div class="form-group col-xs-3">
  <input type="text" class="form-control" name='category_name' placeholder='Название категории' value='<?php echo _e($category_name);?>'>
  </div>

  <button type="submit" class="btn btn-default">Изменить</button>
  <button type="button" class="btn btn-link" onclick='delete_category()'>Удалить</button>
  </form>
  </div>
  <?php

  echo "</div>";
 */
?>
<?php
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
    			<input type="hidden" name="is_delete" value="0">
    			<input type="text" class="form-control" name="category_name" id="category_name" placeholder="Название категории" value="<?php echo _e($category_name); ?>">
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
    		    <i class="icon icon-folder"></i>
    		    <i class="fa fa-angle-down"></i>
    		</a>
    		<ul class="dropdown-menu dropdown-menu-left" role="menu">
			<?php
			foreach ($arr_categories as $cur) {
			    if ($category_id != $cur['id'])
				echo '<li><a class="dropdown-link" href="#" onclick="return move_links_to_category(' . $cur['id'] . ');">' . _e($cur['category_caption']) . '</a></li>';
			}
			?>
    		</ul>
    	    </div>
    	    <div class="btn-group show-if-offer-checked">
    		<a class="btn btn-default single-icon" href="#" onclick="arch_links(<?php echo $cat_type == 'archive' ? 0 : 1; ?>)">
    		    <i class="icon icon-drawer"></i>
    		</a>
    		<a class="btn btn-default single-icon" href="fakelink">
    		    <i class="icon icon-pencil"></i>
    		</a>
    		<a class="btn btn-default single-icon" href="#" onclick="return delete_links();">
    		    <i class="icon icon-trash-gray"></i>
    		</a>
    	    </div>					


    	    <!--Right buttons-->
    	    <!--					<div class="btn-group pull-right">
    							    <a class="btn btn-default" href="fakelink"><i class="fa fa-angle-double-down"></i></a>
    						    </div>-->
		<?php if ($cat_type == 'all') { ?>
		    <div class="btn-group pull-right">
			<a class="btn btn-default single-icon" href="#" onclick="return toggle_offer_add_form()"><i class="icon icon-plus"></i></a>
		    </div>
		<?php } ?>
    	</div><!--Toolbar-->
        </div><!--Header-content-->
    </div><!--page-heading-->


    <div id="add_offer_form" style="display: none; margin-bottom: 10px;">
        <!--<p><strong>Новый оффер</strong> <a href="#" data-toggle="tooltip" data-placement="bottom" title="" onclick="return false;" style="cursor:default; color:gray;" id="link_add_tooltip" data-original-title="Для использования SubID добавьте [SUBID] в URL" class='no-hover'><i class="fa fa-question-circle"></i></a></p>

        <div class="popover-content">
        <form class="form-inline" role="form" method="post" onSubmit="return check_add_offer();" id="form_add_offer">
        <input type=hidden name="ajax_act" value="add_offer">
        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">
        <input type=hidden name="category_id" value="<?php echo _e($category_id); ?>">

        <div class="form-group col-xs-3">
    	<input type="text" class="form-control" name='link_name' id="new_link_name" placeholder="Название оффера">
        </div>

        <div class="form-group  col-xs-5">
    	<input type="text" class="form-control" name='link_url' placeholder="URL">
        </div>

        <button type="submit" class="btn btn-default">Добавить</button>
        </form>
        </div>-->
        <div class="container-fluid">
    	<div class="row">
    	    <form class="form-horizontal offer-form" novalidate="novalidate" role="form" onSubmit="return check_add_offer();" id="form_add_offer">
		<input type="hidden" name="ajax_act" value="add_offer">
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

if (count($arr_offers) > 0) {
    ?>
    <!-- Table -->
    <div class="table-box">

        <table class="table table-th-block table-striped table-hover table-check">
    	<tbody>
		<?php
		foreach ($arr_offers as $cur) {
		    $tracking_url = str_replace(array('http://', 'https://'), '', $cur['offer_tracking_url']);
		    $total_visits = intval($offers_stats_array[$cur['offer_id']]);
		    ?>
		    <!--row-->
		    <tr id="linkrow-<?php echo $cur['offer_id']; ?>">
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
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
				    <i class="fa fa-angle-down"></i>
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
					<a class="dropdown-link" href="#">Изменить</a>
				    </li>
				    <li>
					<a class="dropdown-link" href="#">Создать ссылку</a>
				    </li>
				    <li class="dropdown-footer text-danger">
					<a class="dropdown-link text-danger" href="#" onclick="return delete_link(<?php echo _e($cur['offer_id']); ?>)"><i class="icon icon-abs icon-trash"></i>Удалить</a>
				    </li>
				</ul>
			    </div>
			</td>
		    </tr>
		<?php } ?>
    	</tbody>
    	<tfoot>
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
    		<td colspan="3">Всего: <?php
	    $offers = count($arr_offers);
	    echo $offers . ' ' . numform($offers, array('оффер', 'оффера', 'офферов'));
		?></td>
    		<td colspan="2" class="text-right"><!-- <a class="hover-underline" href="fakelink">Показать все</a>--></td>
    	    </tr>
    	</tfoot>
        </table>
    </div>
<?php } ?>

<div class="pagination">
    <div class="btn-toolbar" role="toolbar">
	<div class="pagination-left">
	    <a class="hover-underline" href="/docs/topic2" target="_blank"><i class="icon icon-info"></i>Как работать с офферами</a>
	</div>
    </div>
</div>