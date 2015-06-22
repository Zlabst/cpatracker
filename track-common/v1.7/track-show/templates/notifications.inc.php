<?php
if (!$include_flag)
    exit;

if ($var['status'] == -1) {
    $current_ntf_cnt = $var['cnt'];
    $declination = array(' уведомление', ' уведомления', ' уведомлений');
} else {
    $current_ntf_cnt = $var['cnt_unread'];
    $declination = array(' непрочитанное уведомление', ' непрочитанных уведомления', ' непрочитанных уведомлений');
}
?>
<script>
    function mark_as_read(e, id, obj) {
        e.stopPropagation();
        $.ajax({
            type: "get",
            url: "index.php",
            data: { csrfkey:"<?php echo CSRF_KEY ?>", ajax_act: "mark_notify_as_read", id: id }
        })
        .done(function(msg) {
            var responce = eval('(' + msg + ')');
            
            $(obj).hide();
            if(responce.unread_cnt == '0') {
                $('#real_all_button').hide();
            }
            $('#ntf_unread_cnt').text(responce.unread_cnt_all);
            $('#ntf_cnt').text(responce.cnt);
        });        
        return false;
    }
</script>
<div class="page-heading">
    <div class="header-content clearfix">
        <h2 class="pull-left">
            У вас
            <strong><?php echo $current_ntf_cnt; ?></strong>
            <?php echo declination($current_ntf_cnt, $declination, false); ?>
        </h2>
        <?php if ($var['cnt_unread'] - $var['cnt_system'] > 0) { ?>
            <a class="btn btn-default pull-right" href="?page=notifications&act=read_all" id="real_all_button">
                <i class="cpa cpa-check-o"></i>
                <span>Отметить все как прочитанное</span>
            </a>
        <?php } ?>
    </div>
</div>
<div class="link-list-box">
    <table class="table table-striped table-link-list table-notification">
        <tbody>
            <?php foreach ($var['notifications'] as $n) { ?>
                <tr>
                    <td class="accordion-toggle" data-toggle="collapse-next">
                        <strong><?php echo $n['title']; ?></strong>
                    </td>
                    <td class="text-center accordion-toggle" data-toggle="collapse-next">
                        <?php
                        if ($n['id'] > 0) {
                            $next_row_style = 'class="collapse" style="height: 0px;"';
                            if ($n['status'] == 0) {
                                ?>
                                <a class="btn btn-link" href="#" onclick="return mark_as_read(event, <?php echo $n['id']; ?>, this)">
                                    <i class="cpa cpa-check-o"></i>
                                    <span>Отметить как прочитанное</span>
                                </a>
                                <?php
                            }
                        } else {
                            $next_row_style = 'class="collapse in" style="height: auto;"';
                        }
                        ?>
                    </td>
                    <td class="text-center accordion-toggle" data-toggle="collapse-next">
                        <span><?php echo mysqldate2string($n['date']); ?></span>
                    </td>
                    <td class="text-center accordion-toggle" data-toggle="collapse-next">
                        <a class="a-default">
                            <i class="cpa cpa-angle-down"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="no-padding" colspan="4">
                        <div <?php echo $next_row_style; ?>>
                            <div class="notification-body">
                                <div class="panel">
                                    <?php echo $n['text']; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>