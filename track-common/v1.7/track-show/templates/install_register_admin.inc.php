<!-- BEGIN SIDEBAR LEFT -->
<div class="sidebar-left">
    <ul class="sidebar-menu">
        <li>
            <a class="logo-brand" href="/track-show/">
                <span>CPA </span>Tracker
            </a>
        </li>
        <li class="install-step passed">
            <div class="circle-icon">1</div>
            <span>Установка прав доступа к папкам</span>

        </li>
        <li class="install-step passed">
            <div class="circle-icon">2</div>
            <span>Подключение баз данных</span>					
        </li>
        <li class="install-step active">
            <div class="circle-icon">3</div>
            <span>Данные администратора</span>					
        </li>
    </ul><!--sidebar-menu-->
</div><!-- /.sidebar-left -->
<!-- END SIDEBAR LEFT -->

<!-- BEGIN PAGE CONTENT -->
<div class="page-content no-top-menu">

    <!-- Page heading -->
    <div class="page-heading">
        <div class="header-content">			
            <h2>Заполните данные администратора</h2>							
            <p>Введите логин и пароль под которыми вы будете входить в систему CPA Tracker.</p>
        </div><!--Header-content-->			
    </div>

    <!-- Docs -->

    <div class="row">
        <div class="col-md-8">

            <!--Alerts-->
            <div class="alert alert-danger fade in alert-dismissible" role="alert" id="info_message" style="display: none;">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <span id="info_message_text"></span></a>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <form class="form-horizontal signin-form" id="register_admin" onSubmit="return check_form();" novalidate="novalidate" role="form">
                        <input type=hidden name="act" value="register_admin"> 
                        <input type=hidden name="page" value="register"> 

                        <!-- Database name-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="email">E-mail</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="email" placeholder="E-mail" class="form-control" name="email" id="email">
                            </div>
                        </div>

                        <!-- Database user name-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="password">Пароль для входа</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="password" placeholder="Введите пароль (минимум 6 символов)" class="form-control" name="password" id="password">
                            </div>
                        </div>

                        <!-- Database password-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="password2">Повторите пароль</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="password" placeholder="Введите пароль повторно" class="form-control" name="password2" id="password2">
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="form-group">
                            <div class="col-md-6">
                                <div class="btn-group ">
                                    <a class="btn btn-default" href="#" onclick="$('#register_admin').submit()"><i class="cpa cpa-check-o"></i><span>Сохранить</span></a>
                                </div>
                            </div>				
                            <div class="col-md-6">
                                <div class="btn-group pull-right">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="1" class="i-blue" id="subscribe" name="subscribe" checked="checked">
                                            Получать информацию об обновлениях трекера на e-mail
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div><!-- /.row -->
            </div><!--container-->

        </div>
    </div>

</div><!-- /.page-content -->
<!-- END PAGE CONTENT -->

<script>
    $(document).ready(function() {
        $('input[name="email"]').focus(); 
    });

    function check_form() {
        $('.has-error').removeClass('has-error');
        $('#info_message').hide();

        if (($('#email').val()!='') && ($('#password').val()!='') && $('#password').val() == $('#password2').val()) {
            return true;
        }

        if ($('#password').val()=='' || $('#password').val().lenght < 6) {
            $('#info_message_text').html('Пароль не может быть короче 6 симолов');
            $('#info_message').show();
            $('#password').parent().parent().addClass('has-error');
            $('#password').focus();
        }
        
        if ($('#password').val() != $('#password2').val()) {
            $('#info_message_text').html('Пароли не совпадают');
            $('#info_message').show();
            $('#password2').parent().parent().addClass('has-error');
            $('#password2').focus();
        }

        if ($('#email').val() == '') {
            $('#info_message_text').html('Е-майл не может быть пустым');
            $('#info_message').show();
            $('#email').parent().parent().addClass('has-error');
            $('#email').focus();
        }

        return false;
    }
</script>