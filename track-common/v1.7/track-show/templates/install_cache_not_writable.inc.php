<!-- BEGIN SIDEBAR LEFT -->
<div class="sidebar-left">
    <ul class="sidebar-menu">
        <li>
            <a class="logo-brand" href="/track-show/">
                <span>CPA </span>Tracker
            </a>
        </li>
        <li class="install-step active">
            <div class="circle-icon">1</div>
            <span>Установка прав доступа к папкам</span>

        </li>
        <li class="install-step">
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
            <h2>Установка прав доступа к папкам</h2>							
        </div><!--Header-content-->			
    </div>

    <!-- Docs -->
    <ol class="help">
        <li>
            <p>Перед продолжением установки вам необходимо изменить права доступа на папку:</p>
            <p >
                <span class="alert-small"><b><?php echo realpath(_CACHE_PATH); ?></b></span>
            </p>
            <p>Для этого вы можете воспользоваться бесплатным FTP клиентом Filezilla <a href="http://filezilla.ru/get/" target="_blank"><b><i class="cpa cpa-download"></i> Скачать</b></a></p>
        </li>
        <li>
            <p>Откройте папку с установленным трекером, внутри папки <b>track</b> найдите папку <b>cache</b> и нажмите на ней правой кнопкой мыши.<br /> Выберите пункт <b>«Права доступа к файлу»</b></p>
            <img src="<?php echo _HTML_TEMPLATE_PATH; ?>/img/help/filezilla_1.png" alt="screen" />
        </li>
        <li>
            <p>Установите значения как показано на рисунке. Нажмите <b>ОК</b> и обновите эту страницу.</p>
            <img src="<?php echo _HTML_TEMPLATE_PATH; ?>/img/help/filezilla_2.png" alt="screen" />
        </li>
    </ol>

    <!--Pagination-->
    <div class="pagination">
        <div role="toolbar" class="btn-toolbar">
            <div class="btn-group ">
                <a class="btn btn-default" href="#" onclick="window.location.reload(); return false;"><i class="cpa cpa-refresh"></i><span>Обновить страницу</span></a>
            </div>
            <div class="btn-group">
                <a class="btn btn-link" href="mailto:support@cpatracker.ru"><i class="cpa cpa-warning"></i><span>Если вы по прежнему видите эту страницу — напишите на <b>support@cpatracker.ru</b></span></a>
            </div>
        </div>
    </div>
</div><!-- /.page-content -->
<!-- END PAGE CONTENT -->