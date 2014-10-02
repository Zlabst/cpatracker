<?php
if (!$include_flag) {exit(); }

$params = $var['report_params'];

// Кнопки типов статистики
$type_buttons = array(
	'all' => 'Все',
	'day' => 'По дням',
	'month' => 'По месяцам',
);

echo '<div class="btn-group">';
foreach($type_buttons as $k => $v) {
	echo '<a href="' . report_lnk($params, array('part' => $k)).'" type="button" class="btn btn-default ' . ($params['part'] == $k ? 'active' : '') . '">' . $v . '</a>';
}
echo '</div>';
