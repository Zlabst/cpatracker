<?php
if (!$include_flag) {
    exit();
}
?>
<script>
    function send_support_message()
    {
        if ($('#support_message_text').val()=='')
        {
            $('#support_message_text').focus();
            return false;
        }

        $('#alert-message').hide();
        $.ajax({
            type: "POST",
            url: "index.php",
            data: $('#support_form').serialize()
        })
        .done(function( msg ) 
        {
            var result=msg.toString().split('|');
            if (result[0]=='1')
            {
                switch (result[1]){
                    case '[message_recieved]': 
                        $('#alert-message').html ('Сообщение успешно отправлено');
                        break;

                    default: 
                        $('#alert-message').html (result[1]);
                        break;
                }
				
                $('#support_form')[0].reset();
                $('#alert-message').show();
            }
            else
            {
                $('#alert-message').html(result[1]);
                $('#alert-message').show();
            }
            return false;
        });        
        return false;
    }

    $(function() 
    {
        $("#support_message_text").focus();
    });

</script>

    <!-- Page heading -->
    <div class="page-heading">
        <div class="header-content">
            <h2>Поддержка</h2>
            <p>Быстрая связь с технической поддержкой</p>
        </div><!--Header-content-->

    </div><!--page-heading-->

    <div class="row">
        <div class="col-md-4">
            <form action="" class="form form-validation form-contact" method="post" novalidate="novalidate" id="support_form" onSubmit="return send_support_message();">
                <input type="hidden" name="ajax_act" value="send_support_message">
                <input type="hidden" name="user_email" id="user_email" value="<?php echo $auth_info[1]; ?>">
                <input type="hidden" name="csrfkey" value="<?php echo CSRF_KEY; ?>">

                <textarea class="form-control" rows="10" placeholder="Опишите вашу проблему как можно подробней" id="support_message_text"></textarea>

                <div class="pagination">
                    <button type="submit" class="btn btn-default" href="#" onclick="$('#support_form').submit(); return false;"><i class="cpa cpa-plane"></i><span>Отправить сообщение</span></button>
                </div>
            </form>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <a class="btn btn-link" href="#fakelink"><i class="cpa cpa-folders"></i><span><strong>Документация</strong></span></a>
                <p>Официальный сайт проекта <br /><a href="www.cpatracker.ru"><strong>www.cpatracker.ru</strong></a> </p>
                <p>Контактный e-mail <br /><a href="mailto:support@cpatracker.ru"><strong>support@cpatracker.ru</strong></a> </p>
            </div>
        </div>
    </div>

</div><!-- /.page-content -->
<!-- END PAGE CONTENT -->