<?php
if (!$include_flag) {
    exit();
}?>Всего: <?php echo $var['total'] . ' ' . numform($var['total'], array('оффер', 'оффера', 'офферов'));?>