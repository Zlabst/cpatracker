<?php if (!$include_flag){exit();} ?>
<script>
	function add_new_category()
	{
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
			
			/*
			li = $('<li>');
			a = $('<a>');
			a.attr('href', '?page=links&category_id=' / )
			*/
			$('#submenu_all_offers').append('<li><a href="?page=links&category_id='+category_id+'">'+htmlEncode(category_name)+'</a></li>');
		});

		return false;
	}
	function toggle_add_category_form()
	{
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
</script>
<? /* ?>
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
	$cat_group = empty($_REQUEST['cat_group']) ? 'all' : $_REQUEST['cat_group'];
?><!-- BEGIN SIDEBAR LEFT -->
	<div class="sidebar-left">
	
		<!-- Button sidebar left toggle -->
		<div class="btn-collapse-sidebar-left icon-dynamic"  data-toggle="tooltip" data-placement="bottom" title="Свернуть левое меню"></div>

		<ul class="sidebar-menu">
			<li>
				<a class="logo-brand" href="/">
					<span>CPA </span>Tracker
				</a>
			</li>
			<li <?php if($cat_group == 'favorits') { echo 'class="active"';} ?> >
				<a href="#fakelink">Избранное</a>
			</li>
			<li <?php if($cat_group == 'archive') { echo 'class="active"';} ?>>
				<a href="#fakelink">Архив</a>
			</li>
			<li <?php if($cat_group == 'all') { echo 'class="active"';} ?>>
				<a href="#">Все офферы</a>
				<?php
					$result = get_links_categories_list();
			    	$arr_categories = array_merge(
			    		array(
			    			array(
			    				'id' => 0,
			    				'category_caption' => 'Без категории'
			    			)
			    		),
			    		$result['categories']
			    	);
			    	//dmp($result['categories']);
			    	$arr_categories_count = $result['categories_count'];
			    	//$arr_categories_count[0] = array_sum($result['categories_count']);
			    	
			    	//dmp($result);
			    	
		    		echo '<ul id="submenu_all_offers" class="submenu" '.($cat_group == 'all' ? 'style="display: block;"' : '').'>';
		    		foreach ($arr_categories as $cur) {
		    			$highlight = $_REQUEST['category_id'] == $cur['id'] ? ' class="highlighted"' : '' ;
		    			echo '<li'.$highlight.'><a href="?page=links&category_id='._e($cur['id']).'">' . _e($cur['category_caption']) . '<span class="span-sidebar">'._e($arr_categories_count[$cur['id']]).'</span></a></li>';
		    		}
		    		echo '</ul>';
			    	
				?>
			</li>
			<li>
				<a href="#" onclick="toggle_add_category_form(); return false;">Добавить категорию</a>
				<form class="form-inline links_category_add_form" role="form" method="post" onsubmit="return add_new_category()" id="category_add_form">
					<input type="hidden" name="ajax_act" value="add_category">
			        <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY;?>">
			        <div class="input has-feedback ">
<input class="form-control form-control-alt" type="text" name="category_name" placeholder="Добавить категорию">
<span class="form-control-feedback single-icon">
					</div>
				</form>
			</li>
		</ul><!--sidebar-menu-->
	</div><!-- /.sidebar-left -->
	<!-- END SIDEBAR LEFT -->
<?php
	echo load_plugin('demo', 'demo_well');
?>
	