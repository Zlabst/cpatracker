<!-- BEGIN SIDEBAR LEFT -->
<div class="sidebar-left">
    <ul class="sidebar-menu">
        <li>
            <a class="logo-brand" href="/">
                <span>CPA </span>Tracker
            </a>
        </li>
        <li class="install-step passed">
            <div class="circle-icon"><i class="cpa cpa-check-o"></i></div>
            <span>Установка прав доступа к папкам</span>

        </li>
        <li class="install-step active">
            <div class="circle-icon">2</div>
            <span>Подключение баз данных</span>					
        </li>
        <li class="install-step">
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
            <h2>Введите информацию о подключении к базе данных</h2>							
            <p>Если вы в ней не уверены, свяжитесь со службой поддержки вашего хостинга.</p>
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
                    <form class="form-horizontal signin-form" novalidate="novalidate" role="form" id="form_settings" onsubmit="return save_settings();">
                        <input type="hidden" name="ajax_act" value="create_database">

                        <!-- Database name-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="dbname">Имя базы данных</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="dbname" id="dbname">
                            </div>
                        </div>

                        <!-- Database user name-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="login">Имя пользователя</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="login" id="login">
                            </div>
                        </div>

                        <!-- Database password-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="password">Пароль</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="password" id="password">
                            </div>
                        </div>

                        <!-- Database server-->
                        <div class="form-group">
                            <div class="col-md-6">
                                <label class="control-label pull-left" for="dbserver">Сервер базы данных</label>
                            </div>				
                            <div class="col-md-6">
                                <input type="text" placeholder="localhost" class="form-control" name="dbserver" id="dbserver">
                            </div>
                        </div>

                        <!-- Database server-->
                        <div class="form-group" data-popover-content="#server-description" data-toggle="install-popover">
                            <div class="col-md-6">
                                <label class="control-label pull-left">Тип вашего сервера</label>
                            </div>				
                            <div class="col-md-6">
                                <select class="select2" id="server_type" name="server_type" style="width: 100%">
                                    <option selected>Apache</option>
                                    <option>Nginx</option>
                                </select>
                            </div>
                        </div>

                        <!-- Content for Popover  -->
                        <div class="hidden" id="server-description">
                            <div class="popover-body">
                                <p>Определите ваш текущий IP адрес на <b>whatismyip.com</b>, найдите его в таблице:</p>
                                <table class="table server-select">
                                    <thead>
                                        <tr>
                                            <th><b>IP адрес</b></th>
                                            <th><b>Тип сервера</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>178.215.80.37</td>
                                            <td>Apache</td>
                                        </tr>
                                        <tr>
                                            <td>-</td>
                                            <td>Nginx</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </form>
                </div><!-- /.row -->
            </div><!--container-->

        </div>
    </div>

    <!--Pagination-->
    <div class="pagination">
        <div role="toolbar" class="btn-toolbar">
            <div class="btn-group ">
                <a class="btn btn-default" href="#" onclick="save_settings(); return false;"><i class="cpa cpa-check-o"></i><span>Сохранить</span></a>
            </div>
        </div>
        <div role="toolbar" class="btn-toolbar">
            <div class="btn-group">
                <a href="https://www.cpatracker.ru/docs/" class="btn btn-link" target="_blank"><i class="cpa cpa-info"></i><span>Документация</span></a>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
<!-- END PAGE CONTENT -->

<script src="<?php echo _HTML_LIB_PATH; ?>/select2/dist/js/select2.js"></script> 
<script>
    if ($('.select2').length > 0) {
        $('.select2').select2({
            theme: 'classic',
            language: 'ru',
            minimumResultsForSearch: 5,
            matcher: function (params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (data.text.indexOf(params.term) > -1) {
                    var modifiedData = $.extend({}, data, true);
                    modifiedData.text += ' (совпадение)';
                    return modifiedData;
                }
                return null;
            }
        });
    }
    
    $(document).ready(function() {
        $('#dbname').focus();
    });
    
    function save_settings() {
        $('.has-error').removeClass('has-error');
        
        $('#info_message').hide();
		
        if ($('#dbname').val()=='') {
            $('#dbname').focus();
            $('#dbname').parent().parent().addClass('has-error');
            return false;
        }	

        if ($('#login').val()=='') {
            $('#login').focus();
            $('#login').parent().parent().addClass('has-error');
            return false;
        }				

        if ($('#dbserver').val()=='') {
            $('#dbserver').focus();
            $('#dbserver').parent().parent().addClass('has-error');
            return false;
        }
		
        $.ajax({
            type: 'POST',
            url: 'index.php',
            data: $('#form_settings').serialize()
        }).done(function(msg) {
            var result=jQuery.parseJSON(msg);
            if (result[0]) {
                window.location.replace(result[1]);

            } else {
                switch (result[1]) {
                    case 'config_found': 
                        $('#info_message_text').html('Файл с настройками уже найден:<br />'+result[2]+'<br />Перед сохранением новых параметров его необходимо удалить.');
                        $('#info_message').show();
                        break;
					
                    case 'cache_not_writable': 
                        $('#info_message_text').html('Установите права на запись (777) для папки <br />'+result[2]);
                        $('#info_message').show();
                        break;

                    case 'db_error':
                        $('#info_message_text').html('Ошибка базы данных<br />'+result[2]);
                        $('#info_message').show();
                        break;

                    case 'db_not_found': 
                        $('#info_message_text').html('База данных '+result[2]+' не найдена. Вам необходимо ее создать.');
                        $('#info_message').show();
                        break;
                    
                    case 'htaccess_not_found':
                        $('#info_message_text').html('Проверьте наличие файлов .htaccess в каталогах /track и /track-show.');
                        $('#info_message').show();
                        break;
					
                    case 'wurfl_not_writable':
                        $('#info_message_text').html('Файл ' + result[2] + ' не доступен для записи.');
                        $('#info_message').show();
                        break;
                    
                    case 'table_not_create':
                        $('#info_message_text').html('Не удается создать таблицу в базе данных. Проверьте права доступа для пользователя БД.');
                        $('#info_message').show();
                        break;
					
                    case 'schema_not_found': 
                        $('#info_message_text').html('Файл database.php со структурой базы данных не найден.<br />Установите последнюю версию скрипта с официального сайта.');
                        $('#info_message').show();					
                        break;

                    default: 
                        $('#info_message_text').html('Неизвестная ошибка. Напишите на support@cpatracker.ru');
                        $('#info_message').show();					
                        break;					
                }
            }
        });
        return false;
    }
</script>            