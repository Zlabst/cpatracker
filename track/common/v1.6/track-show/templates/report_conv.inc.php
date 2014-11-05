<?php
if (!$include_flag) {exit(); }

$params = $var['report_params'];

// Кнопки типов статистики
$type_buttons = array(
	'all'  => array('Все переходы', ''),
	'sale' => array('Только продажи', 'col_s'),
	'lead' => array('Только лиды', 'col_l'),
	'sale_lead' => array('Только действия', 'col_a'),
	'none' => array('Без конверсий', ''),
);

echo '<div class="row"><div class="btn-group pull-right">';
foreach($type_buttons as $k => $v) {
	echo '<a href="' . report_lnk($params, array('conv' => $k)).'" type="button" class="btn btn-default' . ($params['conv'] == $k ? ' active' : '') . ($v[1] != '' ? ' ' . $v[1] : '') . '">' . $v[0] . '</a>';
}
echo '</div></div>';
