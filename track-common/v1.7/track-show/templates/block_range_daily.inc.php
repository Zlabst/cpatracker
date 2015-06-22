<?php
if (!$include_flag) {exit(); }

// Календарик выбора дней

echo '<div id="per_day_range" class="pull-right" style="margin-left:7px;">
        <span id="cur_day_range"><span class="date">'.date('d.m.Y', strtotime($var['report_params']['from'])).'</span> <span class="amid">—</span> <span class="date">'. date('d.m.Y', strtotime($var['report_params']['to'])).'</span> <i class="cpa cpa-angle-down"></i>
        <input type="hidden" name="from" id="sStart" value="">
        <input type="hidden" name="to" id="sEnd" value="">
    </div>';

?>
<!--<span class="amid">за</span>
<div id="per_day_range" class="pull-right" style="">
<span class="date"><?php echo date('d.m.Y', strtotime($var['report_params']['from'])); ?></span>
<span class="amid">—</span>
<span class="date"><?php echo date('d.m.Y', strtotime($var['report_params']['to'])); ?></span>
<i class="cpa cpa-angle-down"></i>
<input type="hidden" name="from" id="sStart" value="">
<input type="hidden" name="to" id="sEnd" value="">
</div>-->