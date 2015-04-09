<?php
    if (!$include_flag) exit;
	$arr_timezone_settings=get_timezone_settings();
	if (count ($arr_timezone_settings) == 0) {
        $arr_timezone_selected_name='Сервер';
    } else {
        foreach ($arr_timezone_settings as $cur) {
            if ($cur['is_active']==1) {
                $arr_timezone_selected_name=$cur['timezone_name'];
                break;           
            }
        }
    }
?>
<!-- BEGIN TOP NAV -->
<script>
    function change_current_timezone(id)
    {
        $.ajax({
          type: "get",
          url: "index.php",
          data: { csrfkey:"<?php echo CSRF_KEY?>", ajax_act: "change_current_timezone", id: id }
        })
          .done(function( msg ) 
          {
            location.reload(true); 
          });        
        return false;
    }
</script>
	<div class="top-navbar<?php echo $menu_toggle_class;?>">
		<div class="top-nav-content">
			<div class="container-fluid">
			
				<!-- Nav main menu  -->
				<ul class="nav navbar-nav navbar-left main-menu">
					<li <?php if ($_REQUEST['type']=='' and $_REQUEST['page']==''){echo 'class="active"';}?>><a href="?act=">Лента</a></li>
            		<li <?php if ($_REQUEST['act'] =='reports'){echo 'class="active"';}?>><a href="?act=reports&type=basic">Отчеты</a></li>
            		<li <?php if ($_REQUEST['page']=='links'){echo 'class="active"';}?>><a href="?page=links&type=favorits">Офферы</a></li>
            		<li <?php if ($_REQUEST['page']=='rules'){echo 'class="active"';}?>><a href="?page=rules">Ссылки</a></li>
            		<li <?php if (in_array($_REQUEST['page'], array('import', 'costs', 'postback'))){echo 'class="active"';}?>><a href="?page=import">Инструменты</a></li>
				</ul>

				<!-- New offer form-->
				<div id="popover-content" class="hide" >
					<div class="container-fluid">
						<div class="row">
							<form class="form-horizontal offer-form" novalidate="novalidate" role="form">
								
								<!-- Offer name-->
								<div class="form-group">
									<div class="col-sm-2">
										<label class="control-label pull-left" for="offer-name">Название</label>
									</div>				
									<div class="col-sm-10">
										<input type="text" placeholder="Введите название оффера" class="form-control" name="offer-name" id="offer-name">
									</div>
								</div>
								
								<!-- Offer URL-->
								<div class="form-group">
									<div class="col-sm-2">
										<label class="control-label pull-left" for="offer-url">URL</label>
									</div>				
									<div class="col-sm-10">
										<input type="text" placeholder="Введите URL" class="form-control" name="offer-url" id="offer-url">
										<span class="help-block small pull-left">Для использования SubId добавьте [SUBID] в URL</span>
										<span class="help-block small pull-right"><i class="icon icon-one"></i>Учет продаж включен</span>
									</div>
								</div>
								
								<!-- Offer description-->
								<div class="form-group">
									<div class="col-sm-2">
										<label class="control-label pull-left" for="offer-description">Описание</label>
									</div>				
									<div class="col-sm-10">
										<textarea class="form-control" rows="3"  id="offer-description" name="offer-description" placeholder="Описание оффера"></textarea>
									</div>
								</div>
								
								<!-- Offer Tags-->
								<div class="form-group">
									<div class="col-sm-2">
										<label class="control-label pull-left" for="offer-description">Общий тег</label>
									</div>				
									<div class="col-sm-10">
										<select class="selectpicker2 show-tick input-group-btn"  data-style="btn-info">
									        <option>Lorem.</option>
											<option>Lorem ipsum.</option>
											<option>Lorem ipsum dolor.</option>
											<option>Lorem ipsum dolor sit amet.</option>
										</select>
									</div>
								</div>
								
								<!--Buttons-->
								<div class="form-group">
									<div class="col-sm-offset-2 col-sm-10">
										<div class="btn-toolbar">
											<div class="pull-left btn-group ">
												<a href="#fakelink" class="btn btn-default"><i class="icon icon-plus"></i>Добавить новый оффер</a>
											</div>
											<div class="pull-left btn-group ">
												<a id="offer-form-close" href="#fakelink" class="btn btn-cancel"><i class="icon icon-cancel"></i>Отмена</a>
											</div>
											<div class="pull-right btn-group">
												 <a href="#fakelink" class="btn btn-default">Добавить группу</a>
											</div>
										</div>
									</div>
								</div>
																
							</form>
						</div><!-- /.row -->
					</div><!--container-->
				</div><!--popover-content-->
				
				<!-- Nav dropdown -->
				<ul class="nav navbar-nav navbar-right navbar-link pull-right">
					<?php if(1) { // Сделать проверку на многопользовательность ?>
					<li class="text-info">
						<a class="dropdown-link" href="?page=logout">Выход</a>
					</li>
					<?php } else { ?>
					<li class="dropdown">
						<a href="#fakelink"  class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
							<?php echo $auth_info[1]?>
							<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-right" role="menu">
							<!--<li>
								<a class="dropdown-link" href="#">lov@gmail.com</a>
							</li>-->
							<li class="dropdown-footer text-info">
								<a class="dropdown-link" href="?page=logout"><i class="icon icon-abs icon-logout"></i>Выход</a>
							</li>
						</ul>
					</li>
					<?php } ?>
				</ul>
				
				<!-- Nav dropdown -->
				<ul class="nav navbar-nav navbar-left navbar-link with-value pull-right">
					<li class="dropdown">
						<a href="#fakelink"  class="dropdown-toggle dropdown-link" data-toggle="dropdown" role="button" aria-expanded="false">
							<i class="icon icon-abs icon-clock"></i>
							<?php echo $arr_timezone_selected_name; ?>
							<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu pull-right" role="menu" style="min-width: 250px;">
							<?php
								foreach ($arr_timezone_settings as $cur) {
			                        if ($cur['is_active'] != 1) {
			                        	// Дописываем +
			                        	$timezone_offset = $cur['timezone_offset_h'];
			                        	if(substr($timezone_offset, 0, 1) != '-') $timezone_offset = '+' . $timezone_offset;
			                        	
			                        	$timezone_offset .= ':00';
			                        	
			                        	echo '<li><a class="dropdown-link" href="#" onclick="return change_current_timezone('.$cur['id'].')">'._e($cur['timezone_name']).'<span class="value pull-right">'.$timezone_offset.'</span></a></li>';
			                        }
			                    }
							?>
							<li class="dropdown-footer text-info">
								<a class="dropdown-link"  href="?page=settings&type=timezone"><i class="icon icon-abs icon-cog"></i>Настроить часовой пояс</a>
							</li>
						</ul>
					</li>
				</ul>
			
			</div><!--container-fluid-->
		</div><!-- /.top-nav-content -->
	</div><!-- /.top-navbar -->
	<!-- END TOP NAV -->
<?php
    if (!$include_flag){exit();} 
	/*
    $arr_timezone_settings=get_timezone_settings();
    if (count ($arr_timezone_settings)==0)
    {
        $arr_timezone_selected_name='Сервер';
    }
    else
    {
        foreach ($arr_timezone_settings as $cur)
        {
            if ($cur['is_active']==1)
            {
                $arr_timezone_selected_name=$cur['timezone_name'];
                break;           
            }
        }
    }
?>
<script>
    function change_current_timezone(id)
    {
        $.ajax({
          type: "get",
          url: "index.php",
          data: { csrfkey:"<?php echo CSRF_KEY?>", ajax_act: "change_current_timezone", id: id }
        })
          .done(function( msg ) 
          {
            location.reload(true); 
          });        
        return false;
    }
</script>
<!-- Static navbar -->
<div class="navbar navbar-static-top navbar-inverse">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="?act=">CPA Tracker</a>
    </div>
    <?php
    if ($bHideTopMenu!==true)
    {
    ?>    
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li <?php if ($_REQUEST['type']=='' and $_REQUEST['page']==''){echo 'class="active"';}?>><a href="?act=">Лента</a></li>
            <li <?php if ($_REQUEST['act'] =='reports'){echo 'class="active"';}?>><a href="?act=reports&type=basic">Отчеты</a></li>
            <li <?php if ($_REQUEST['page']=='links'){echo 'class="active"';}?>><a href="?page=links">Офферы</a></li>
            <li <?php if ($_REQUEST['page']=='rules'){echo 'class="active"';}?>><a href="?page=rules">Ссылки</a></li>
            <li <?php if (in_array($_REQUEST['page'], array('import', 'costs', 'postback'))){echo 'class="active"';}?>><a href="?page=import">Инструменты</a></li>
            <?php echo load_plugin('demo', 'demo_warn'); ?>
          </ul>

          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class='fa fa-clock-o color-white'></i>&nbsp;<?php echo _e($arr_timezone_selected_name);?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
               <?php
                    foreach ($arr_timezone_settings as $cur)
                    {
                        if ($cur['is_active']!=1)
                        {
                            echo "<li role='presentation'><a role='menuitem' tabindex='-1' href='#' onclick='return change_current_timezone({$cur['id']})'>"._e($cur['timezone_name'])."</a></li>";                        
                        }
                    }
                    if (count($arr_timezone_settings)>1)
                    {
                        echo "<li role='presentation' class='divider'></li>";
                    }
                ?>  
                <li><a href="?page=settings&type=timezone"><i class='fa fa-cog'></i>&nbsp;Настроить часовой пояс</a></li>
              </ul>
            </li>            
            <li><a href="?page=logout">Выход</a></li>
          </ul>

            <?php
              $notifications_count=count($global_notifications);
              if ($notifications_count>0)
              {
                echo "<ul class='nav navbar-nav'>";
                echo "<li><a href='?page=notifications'><span class='label label-danger'><i class='fa fa-info-circle'></i> ".declination($notifications_count, array(' сообщение', ' сообщения', ' сообщений'))."</span></a></li>";
                echo "</ul>";
              }
            ?>


        </div><!--/.nav-collapse -->

    <?php
    }
    ?>

  </div>
</div><? */ 
	
?>