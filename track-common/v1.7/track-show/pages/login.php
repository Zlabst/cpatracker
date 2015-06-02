<?php if (!$include_flag){exit();} ?>
<script>
  $(document).ready(function() {
    $('#user-email').focus(); 
  });
</script>

<!-- Start page -->
<!-- ++++++++++++++++++++++++++++++++++++++++ -->		
	
<header class="login-header text-center">
	<a class="logo-brand a-inverse" href="#">
		<span>CPA </span>Tracker
	</a>
	<div class="login-nav pull-right">
		<a class="btn btn-link a-inverse" href="https://www.cpatracker.ru/docs/support"><i class="cpa cpa-bubbles"></i><span>Поддержка</span></a>
	</div>
</header>

<section class="login-container">
	<div class="login-content">
		<form class="form form-horizontal" autocomplete="off" method="post" action="?page=login">
			<input type="hidden" name="page" value="login">
			<input type="hidden" name="act" value="login">
			
			<h2>Войти</h2>
			<?php if(load_plugin('demo') != 'demo') { ?>
			<p>Введите данные, указанные при регистрации. </br> Что делать, если вы <a href="#">забыли пароль</a>?</p>	
			
			<div class="form-group">
				<label class="col-sm-2 control-label" for="user-email">Email</label>
				<div class="col-sm-10">
					<input class="form-control " type="email" name="email" id="user-email" placeholder="Введите Email"  tabindex="1">
				</div>
			</div><!-- form-group-->
			
			<div class="form-group">
				<label class="col-sm-2 control-label" for="user-password">Пароль</label>
				<div class="col-sm-10">
					<input class="form-control " type="password" id="user-password" name="password" placeholder="Введите пароль"  tabindex="2">
				</div>
			</div><!-- form-group-->
			<? } else { ?>
			<p>Демо-версия для ознакомления.</p>
			
			<div class="form-group">
				<label class="col-sm-2 control-label" for="user-email">Email</label>
				<div class="col-sm-10">
					<input class="form-control " type="email" name="email" id="user-email" placeholder="Введите Email" value="demo@cpatracker.ru" tabindex="1">
				</div>
			</div><!-- form-group-->
			
			<div class="form-group <? if(!empty($_REQUEST['error'])) { ?>has-error<? } ?>">
				<label class="col-sm-2 control-label" for="user-password">Пароль</label>
				<div class="col-sm-10">
					<input class="form-control " type="password" id="user-password" name="password" placeholder="Введите пароль" value="demo" tabindex="2">
				</div>
			</div><!-- form-group-->
			
			<? } ?>
			<? if(!empty($_REQUEST['error'])) { ?>
			<div class="alert alert-danger" role="alert">Неверное имя пользователя или пароль.</div>			
			<? } ?>
			<div class="btn-toolbar">
				<div class="btn-group pull-right">
					<button class="btn btn-default btn-long" type="submit" tabindex="4">Войти</button>
				</div><!-- form-group-->
			</div>
		
		</form>
	</div>
</section>

<!-- ++++++++++++++++++++++++++++++++++++++++ -->
<!-- End  page -->

<!--<div class="row">
    <div class="col-sm-6 col-md-4 col-md-offset-4">
        <div class="account-wall">
            <img class="profile-img" src="<?php echo _HTML_TEMPLATE_PATH;?>/img/icons/photo.png">
            <form class="form-signin" action='' method="POST">
              <input type=hidden name='page' value='login'>
              <input type=hidden name='act' value='login'>
    		  <?php if(load_plugin('demo') != 'demo') { ?>
              <input type="text" class="form-control" id="email" name="email" placeholder="E-mail" required autofocus>
              <input type="password" class="form-control" id="password" name="password" placeholder="Пароль" required>
              <?php } else { ?>
              <input type="text" class="form-control" id="email" name="email" value="demo@cpatracker.ru" placeholder="E-mail" required autofocus>
              <input type="password" class="form-control" id="password" value="demo" name="password" placeholder="Пароль" required>
              <?php } ?>
              <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
            </form>
        </div>
    </div>
</div>-->