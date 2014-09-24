<?php
	if (!$include_flag) {
	    exit();
	}
	$from = get_current_day('-6 days');
    $to = get_current_day();
    $days = getDatesBetween($from, $to);
    
    $q="SELECT t2.offer_name as `name`, t1.*
    	FROM (
    		SELECT `id`, `out_id`, 
    			COUNT(`id`) as `cnt`, 
    			SUM(`is_unique`) as `unique`, 
    			SUM(`click_price`) as `price`, 
    			SUM(`conversion_price_main`) as `income`, 
    			SUM(`is_sale`) as `sale`,
    			SUM(`out`) as `out`
    		FROM (
    			SELECT t1.*, COUNT(t2.id) as `out`
    				FROM `tbl_clicks` t1
    				LEFT JOIN `tbl_clicks` t2 on t2.parent_id = t1.id
    				WHERE t1.`date_add_day` > '" . $from . "'
    					AND t1.`is_connected` = 0
    				GROUP BY `t1`.`id`
    		) t1
    		WHERE 1
    		GROUP BY `out_id`) t1
    	LEFT JOIN `tbl_offers` t2 ON out_id = t2.id
    	LEFT JOIN `tbl_clicks` t3 ON t3.parent_id = t1.id
    	WHERE `t2`.status = '0'";
    $rs = mysql_query($q) or die(mysql_error());
    $data = array();
    while($r = mysql_fetch_assoc($rs)) {
    	$data[] = $r;
    	//echo '<pre>'.print_r($r, true).'</pre>';
    }
    //echo $q;
    //$sales = get_sales($from, $to, $days, $month);
    
    
    echo '<pre>'.print_r($sales, true).'</pre>';
?>
<div class='row'>
        <div class="col-md-4"><h3>Продажи за 7 дней:</h3></div>
</div>
<div class="row">
	<div class="col-md-12">
		<table class="table table-striped table-bordered table-condensed" style="width:600px;">
			<thead>
				<tr>
					<th>Ссылка</th>
					<th>Переходы на LP</th>
					<th>Ушло на офферы</th>
					<th>Продажи</th>
					<th>Конверсия</th>
					<th>Доход</th>
					<th>Затраты</th>
					<th>Прибыль</th>
					<th>ROI</th>
				</tr>
			</thead>
			<tbody>
				<?
					foreach($data as $r) {
						$profit = $r['income'] - $r['price'];
						$roi = round($profit / $r['price'] * 100, 2);
						$conversion = round($r['sale'] / $r['cnt'] * 100, 2);
						$follow = round($r['out'] / $r['cnt'] * 100, 2);
						
						echo '<tr>
							<td nowrap="">'.$r['name'].'</td>
							<td>'.$r['cnt'].'</td>
							<td>'.$r['out'].'</td>
							<td>'.$r['sale'].'</td>
							<td>'.$conversion.'%</td>
							<td>'.$r['income'].'</td>
							<td>'.$r['price'].'</td>
							<td>'.$profit.'</td>
							<td>'.$roi.'%</td>
						</tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>