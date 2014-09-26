<? 
    if (!$include_flag){exit();} 

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
    <?
    if ($bHideTopMenu!==true)
    {
    ?>    
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li <? if ($_REQUEST['page']==''){echo 'class="active"';}?>><a href="?act=">Отчеты</a></li>
            <li <? if ($_REQUEST['page']=='links'){echo 'class="active"';}?>><a href="?page=links">Ссылки</a></li>
            <li <? if ($_REQUEST['page']=='rules'){echo 'class="active"';}?>><a href="?page=rules">Правила</a></li>
            <li <? if (in_array($_REQUEST['page'], array('import', 'costs', 'postback'))){echo 'class="active"';}?>><a href="?page=import">Инструменты</a></li>
          </ul>



          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class='fa fa-clock-o color-white'></i>&nbsp;<?php echo _e($arr_timezone_selected_name);?> <b class="caret"></b></a>
              <ul class="dropdown-menu">
               <?
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

    <?
    }
    ?>

  </div>
</div>