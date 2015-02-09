<?php if (!$include_flag){exit();} ?>
<?php
$category_id=$_REQUEST['category_id'];
$category_name='{empty}';

$arr_offers=array();
if ($category_id>0) {
	$offers_stats_array=array();
	$sql="select id, category_caption, category_name, category_type from tbl_links_categories_list where id='".mysql_real_escape_string($category_id)."'";
	$result=mysql_query($sql);
	$row=mysql_fetch_assoc($result);
	
	if ($row['id']>0) {
		$category_name=$row['category_caption'];		
	} else {
		// Category id not found
		header ("Location: ".full_url().'?page=links');
		exit();
	}

	switch ($row['category_type']) {
		case 'network':
			$page_type='network';
			
			// Get network ID
			$sql="select id from tbl_cpa_networks where network_category_name='".mysql_real_escape_string($row['category_name'])."'";
			$result=mysql_query($sql);
			$row=mysql_fetch_assoc($result);
			$network_id=$row['id'];
			
			// Get list of offers from network
			$sql="select * from tbl_offers where network_id='".mysql_real_escape_string($network_id)."' and status=0 order by date_add desc, id asc";
			$result=mysql_query($sql);
			while ($row=mysql_fetch_assoc($result))
			{
				$arr_offers[]=$row;	
			}
		break;
		
		default:
			// Get list of offers in category
			$sql="select tbl_offers.* from tbl_offers left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id where tbl_links_categories.category_id='".mysql_real_escape_string($category_id)."' and tbl_offers.network_id='0' and tbl_offers.status=0 order by tbl_offers.date_add desc, tbl_offers.id asc";
			$result=mysql_query($sql);
			$arr_offers=array();
			$offers_id=array();
			while ($row=mysql_fetch_assoc($result)) {
				$row['offer_id']=$row['id'];
				$offers_id[]="'".mysql_real_escape_string($row['id'])."'";
				$arr_offers[]=$row;	
			}
			$offers_id_str=implode(',', $offers_id);
			
			$sql="select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
			$result=mysql_query($sql);
			$offers_stats_array=array();
			while ($row=mysql_fetch_assoc($result))
			{
				$offers_stats_array[$row['out_id']]=$row['cnt'];
			}
		break;
	}
}
else
{
	// Get list of offers without category
	$sql="select tbl_offers.* from tbl_offers left join tbl_links_categories on tbl_offers.id=tbl_links_categories.offer_id where tbl_links_categories.id IS NULL and tbl_offers.network_id='0' and tbl_offers.status=0 order by tbl_offers.date_add desc, tbl_offers.id asc";
	$result=mysql_query($sql);
	$arr_offers=array();
	$offers_id=array();
	while ($row=mysql_fetch_assoc($result))
	{
		$row['offer_id']=$row['id'];
		$offers_id[]="'".mysql_real_escape_string($row['id'])."'";
		$arr_offers[]=$row;	
	}
	$offers_id_str=implode(',', $offers_id);
	
	$sql="select out_id, count(id) as cnt from tbl_clicks where out_id in ({$offers_id_str}) group by out_id";
	$result=mysql_query($sql);
	$offers_stats_array=array();
	while ($row=mysql_fetch_assoc($result))
	{
		$offers_stats_array[$row['out_id']]=$row['cnt'];
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
		  data: { csrfkey: '<?php echo CSRF_KEY;?>',ajax_act: 'import_hasoffers_offers', id: id}
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
		if ($(offer_url).val()=='')
		{
			$(offer_url).css('background-color','lightyellow');
			$(offer_url).focus();
			return false;	
		}
		return true;
	}	
	function show_category_edit() {
		if ($('.category_edit').css('display')=='none')
		{
			$('.category_edit').show();
			$('.category_edit input[name=category_name]').focus();			
		}
		else{
			$('.category_edit').hide();
		}
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
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=delete_link&id='+(typeof(id) == 'string' ? id : id.join(',')) 
		}).done(function( msg ) {
			if(typeof(id) == 'string') {
            	$('#linkrow-' + id).hide();
           	} else {
           		for(i in id) {
           			$('#linkrow-' + id[i]).hide();
           		}
           	}
            //$('#link_name_alert').text($('#link-name-'+id).text());
            //$('#remove_alert').show();
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
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=move_link_to_category&offer_id='+offer_id+'&category_id='+category_id
		}).done(function( msg ) {
			window.location.reload();
		});
		return false;
	}
        
        
        function restore_link() {
       	var id = last_removed;
		$.ajax({
		  type: 'POST',
		  url: 'index.php',
		  data: 'csrfkey=<?php echo CSRF_KEY;?>&ajax_act=restore_link&id='+id
		}).done(function( msg ) 
		{
//			$(obj).parent().parent().parent().parent().parent().remove();
                        $('#linkrow-' + id).show();
                        $('#remove_alert').hide();
		});
                last_removed = 0;
		return false;
        }
        
        
        $(function() {
        	$('.offer_checkbox').iCheck('uncheck');
			$('.check-all').on('ifChanged', function(e){
				$('.offer_checkbox').iCheck(e.target.checked ? 'check' : 'uncheck');
			});
		});
</script>
<?php
if ($category_name!='{empty}')
{
/*
?>
	<?php
		
	echo "<div class='category_title' onclick='show_category_edit()'><span class='category_name'>"._e($category_name)."</span> <i class='fa fa-edit'></i></div>";
	echo "<div class='category_edit'>";
	?>
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
}
?>
<?php
if ($page_type=='network')
{
	echo "<p align=right><img style='margin-right:15px; display: none;' id='networks_import_ajax' src='img/icons/ajax.gif'><span class='btn' onclick='import_offers_from_network(\""._e($category_id)."\")'>Импорт офферов</span></p>";
	echo "<div class='alert' id='networks_import_status' style='display:none;'>
			<button type='button' class='close' data-dismiss='alert'>&times;</button>
			<strong id='networks_import_status_text'></strong>
		  </div>";
}
else
{
?>

<!-- Page heading -->
		<div class="page-heading">
			<!--<p>Admitad</p>-->
			<div class="header-content">
			
				<!--Header-->
				<div class="btn-group header-left">
					<h2>Все офферы
						<a id="alert-warning-toggle" href=""><i class="icon icon-edit"></i></a>
						<a id="alert-danger-toggle" href=""><i class="icon icon-trash"></i></a>
					</h2>
				</div>
				
				<!--Left buttons-->
				<div role="toolbar" class="btn-toolbar">
					<div class="btn-group dropdown">
						<a class="btn btn-default" href="#" data-toggle="dropdown">
							<i class="icon icon-folder"></i>
							<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-left" role="menu">
							<?php 
								foreach ($arr_categories as $cur) {
		    						if($_REQUEST['category_id'] == $cur['id']) continue;
		    						echo '<li><a class="dropdown-link" href="#" onclick="return move_links_to_category(' . $cur['id'] . ');">' . _e($cur['category_caption']) . '</a></li>';
		    					}
							?>
						</ul>
					</div>
					<div class="btn-group">
						<a class="btn btn-default single-icon" href="fakelink">
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
					<div class="btn-group pull-right">
						<a class="btn btn-default single-icon" href="#" onclick="$('#add_offer_form').toggle(); return false;"><i class="icon icon-plus"></i></a>
					</div>
					
				</div><!--Toolbar-->
			</div><!--Header-content-->
		</div><!--page-heading-->
		
		
<div id="add_offer_form" style="display: none; margin-bottom: 10px;">
	<p><strong>Новый оффер</strong> <a href="#" data-toggle="tooltip" data-placement="bottom" title="" onclick="return false;" style="cursor:default; color:gray;" id="link_add_tooltip" data-original-title="Для использования SubID добавьте [SUBID] в URL" class='no-hover'><i class="fa fa-question-circle"></i></a></p>

	<div class="row">
		<form class="form-inline" role="form" method="post" onSubmit="return check_add_offer();" id="form_add_offer">
			<input type=hidden name="ajax_act" value="add_offer">
	                <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
			<input type=hidden name="category_id" value="<?php echo _e($category_id);?>">

			  <div class="form-group col-xs-3">
				    <input type="text" class="form-control" name='link_name' id="new_link_name" placeholder="Название оффера">
			  </div>

			  <div class="form-group  col-xs-5">
				    <input type="text" class="form-control" name='link_url' placeholder="URL">
			  </div>

		  <button type="submit" class="btn btn-default">Добавить</button>
		</form>
	</div>
</div>

<?php
}

if (count($arr_offers)>0) {
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
					<tr id="linkrow-<?php echo $cur['offer_id'];?>">
						<td>
							<form role="form">
								<div class="checkbox">
								  <label>
									<input type="checkbox" value="" class="i-blue offer_checkbox" id="chk<?php echo $cur['offer_id'];?>">
								  </label>
								</div>
							</form>
						</td>
						<td>
							<form role="form">
								<div class="checkbox">
								  <label>
									<input type="checkbox" value="" class="i-star">
								  </label>
								</div>
							</form>
						</td>
						<td><?php echo _e($cur['offer_name']); ?></td>
						<td></td>
						<td><?php echo $tracking_url; ?></td>
						<td class="dropdown-cell">
							<div class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
									<i class="fa fa-angle-down"></i>
								</a>
								<ul class="dropdown-menu dropdown-menu-right" role="menu">
									<li>
										<a class="dropdown-link" href="#">В архив</a>
									</li>
									<li>
										<a class="dropdown-link" href="#">Изменить</a>
									</li>
									<li>
										<a class="dropdown-link" href="#">Создать правило</a>
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
						<td colspan="3">Итого: <?php echo count($arr_offers);?> из <?php echo count($arr_offers);?> ссылок выводится</td>
						<td colspan="2" class="text-right"><a class="hover-underline" href="fakelink">Показать все</a></td>
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