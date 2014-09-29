<?php
if (!$include_flag) {exit(); }

// Кнопки типов статистики
$type_buttons = array(
	'all_stats' => 'Все',
	'daily_stats' => 'По дням',
	'monthly_stats' => 'По месяцам',
);

echo '<div class="btn-group">';
foreach($type_buttons as $k => $v) {
	echo '<a href="?act=reports&type='.$k.'&subtype='._e($var['subtype']).'&group_by='._e($var['group_by']).'&limited_to='._e($var['limited_to']).'" type="button" class="btn btn-default '.($var['type'] == $k ? 'active' : '').'">'.$v.'</a>';
}
echo '</div>';
