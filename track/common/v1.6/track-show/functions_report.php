<?php
	function get_visitors_flow_data($filter='', $offset = 0, $limit = 20, $date = 0)
	{			
		if(empty($date) or !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			//echo '123';
			$timezone_shift_simple = get_current_timezone_shift(true);
			$date = date('Y-m-d', time() + $timezone_shift_simple);
		}
		
		$timezone_shift = get_current_timezone_shift();
		//echo '*' . $timezone_shift . '*';
		$filter_str='';
		if ($filter!='')
		{
			switch ($filter['filter_by'])
			{
				case 'hour': 
					$filter_str .= " and source_name='".mysql_real_escape_string($filter['source_name'])."' AND CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') BETWEEN '"._str($filter['date'])." "._str($filter['hour']).":00:00' AND '"._str($filter['date'])." "._str($filter['hour']).":59:59' ";				
				break;
				
				// поиск по названию кампании, объявления, рефереру, SubID, источнику, IP адресу 
				case 'search':
					if(is_subid($filter['filter_value'])) {
						$filter_str .= " and `subid` LIKE '" . mysql_real_escape_string($filter['filter_value']) . "'";
						$date = false; // ищем за всё время
					} else {
						$filter_str .= " and (
							`user_ip` LIKE '". mysql_real_escape_string($filter['filter_value']) ."' OR
							`campaign_name` LIKE '%". mysql_real_escape_string($filter['filter_value']) ."%' OR
							`source_name` LIKE '%". mysql_real_escape_string($filter['filter_value']) ."%' OR
							`referer` LIKE '%". mysql_real_escape_string($filter['filter_value']) ."%'
						)";
					}
				break;
				
				default:
					$filter_str .= " and ".mysql_real_escape_string ($filter['filter_by'])."='".mysql_real_escape_string ($filter['filter_value'])."'";
				break;
			}
		}
		
		$sql="select SQL_CALC_FOUND_ROWS *, date_format(CONVERT_TZ(tbl_clicks.date_add, '+00:00', '"._str($timezone_shift)."'), '%d.%m.%Y %H:%i') as dt, timediff(NOW(), tbl_clicks.date_add) as td from tbl_clicks 
		where 1
		{$filter_str}
		".($date ? "and date_format(CONVERT_TZ(tbl_clicks.date_add, '+00:00', '"._str($timezone_shift)."'), '%Y-%m-%d %H:%i:%s') between '".$date." 00:00:00' and '".$date." 23:59:59'" : '' )."
		order by date_add desc limit $offset, $limit";

		$result=mysql_query($sql);
		$arr_data=array();
		
		$q="SELECT FOUND_ROWS() as `cnt`";
		$total = ap(mysql_fetch_assoc(mysql_query($q)), 'cnt');
		
		while ($row=mysql_fetch_assoc($result))
		{
			$row['td']=get_relative_mysql_time($row['td']);				
			$arr_data[]=$row;
		}
		//dmp($arr_data);

		return array($total, $arr_data);
	}
	
	function sdate($d, $today = true) {
		$d = strtotime($d);
		if((empty($d) and $today) or date('Y-m-d') == date('Y-m-d', $d)) {
			return 'сегодня';
		} elseif(date('Y-m-d') == date('Y-m-d', $d + 86400)) {
			return 'вчера';
		} else {
			$months = array(
				'01' =>	"января",
				'02' =>	"февраля",
				'03' =>	"марта",
				'04' =>	"апреля",
				'05' =>	"мая",
				'06' =>	"июня",
				'07' =>	"июля",
				'08' =>	"августа",
				'09' =>	"сентября",
				'10' =>	"октября",
				'11' =>	"ноября",
				'12' =>	"декабря",
			);
			return date('j', $d) . ' ' . $months[date('m', $d)] . ' ' . date('Y', $d);
		}
	}
	
	function get_clicks_rows($params, $start = 0, $limit = 0, $campaign_params, $click_params) {
		
		// Смещение часового пояса
		$timezone_shift = get_current_timezone_shift();
		
		// Применяем фильтры
		if(!empty($params['filter'][0]) or !is_array($params['filter'][0])) {
			$tmp = array();
			foreach($params['filter'][0] as $k => $v) {
				if($k == 'referer') {
					if($v == '{empty}') {
						$tmp[] = "`".$k."` = ''";
					} else {
						$tmp[] = "`".$k."` LIKE '%".mysql_real_escape_string($v)."%'";
					}
				} elseif($k == 'ads_name') {
					list($campaign_name, $ads_name) = explode('-', $v);
					$tmp[] = "`campaign_name` = '".mysql_real_escape_string($campaign_name)."'";
					$tmp[] = "`ads_name` = '".mysql_real_escape_string($ads_name)."'";
				} else {
					if($v == '{empty}') { $v = '';}
					$tmp[] = "`".$k."` = '".mysql_real_escape_string($v)."'";
				}
			}
			$where = ' and ('.join(' and ', $tmp).')';
		} else {
			$where = '';
		}
		
		// Дополнительные поля для режима популярных параметров
		if($params['mode'] == 'popular' or 1) {
			$select = ', out_id, source_name, ads_name, referer, user_os, user_platform, user_browser, country, state, city, isp, campaign_param1, campaign_param2, campaign_param3, campaign_param4, campaign_param5 ';
			for($i = 1; $i <= 15; $i++) {
				$select .= ', click_param_value' . $i . ' ';
			}
		} else {
			$select = '';
		}
		
		// Выбираем все переходы за период
		$q="SELECT SQL_CALC_FOUND_ROWS ".(empty($params['group_by']) ? '' : " " . mysql_real_escape_string($params['group_by']) . " as `name`, "). 
			(($params['group_by'] == $params['subgroup_by']) ? '' : " " . mysql_real_escape_string($params['subgroup_by']) . ", ") .
			"
			1 as `cnt`,
			t1.id,
			t1.source_name,
			UNIX_TIMESTAMP(t1.date_add) as `time_add`,
			t1.out_id,
			t1.parent_id,
			t1.campaign_name,
			t1.click_price,
			t1.is_unique,
			t1.conversion_price_main,
			t1.is_sale,
			t1.is_lead,
			t1.is_parent,
			t1.is_connected ".$select."
			FROM `tbl_clicks` t1
			WHERE CONVERT_TZ(t1.`date_add_day`, '+00:00', '"._str($timezone_shift)."') BETWEEN '" . $params['from'] . "' AND '" . $params['to'] . "'" . $where . (empty($params['where']) ? '' : " and " . $params['where'] ). "
			ORDER BY t1.id ASC
			LIMIT $start, $limit";
		
		//echo $q . '<br />';
		$rs = db_query($q);
		
		$q="SELECT FOUND_ROWS() as `cnt`";
		$total = ap(mysql_fetch_assoc(mysql_query($q)), 'cnt');
		
		while($r = mysql_fetch_assoc($rs)) {
			$rows[$r['id']] = $r;
			
			// Определяем наличие пользовательских параметров
			for($i = 1; $i <= 5; $i++) {
				if($r['campaign_param' . $i] != '') {
					$campaign_params[$i] = 1;
				}
			}
			
			for($i = 1; $i <= 15; $i++) {
				if($r['click_param_value' . $i] != '') {
					$click_params[$i] = 1;
				}
			}
		}
		
		return array($total, $rows, $campaign_params, $click_params);
	}
	
	/**
	 * Подготовка данных для отчётов:
	 * subtype - колонка, по которой группируем данные (то же, что и group_by, если не задан limited_to)
	 * limited_to - фильтр по subtype
	 * group_by - группировка второго уровня, если задан limited_to
	 * type - hourly, daily, monthly с каким шагом собираем статистику
	 * from, to - временные рамки, за которые нужна статистика, обязательно в формате Y-m-d H:i:s
	 * where - дополнительные условия выборки кликов
	 * mode - режим выборки и группировки: offers, landings, lp_offers
	 */
	
	function get_clicks_report_grouped2 ($params) {
		global $group_types;
		
		// Флаги существующих параметров
		$campaign_params = array(
			1 => 0, 0, 0, 0, 0
		);
		
		$click_params = array(
			1 => 0, 0, 0, 0, 0, 
			0, 0, 0, 0, 0,
			0, 0, 0, 0, 0
		);
		
		// По временным промежуткам
		$date_formats = array(
			'hour' => 'Y-m-d H',
			'day'  => 'Y-m-d',
			'month'=> 'm.Y'
		);
		
		$groups = array(
			'00' => 'click',
			'01' => 'lead',
			'10' => 'sale',
			'11' => 'sale_lead'
		);
		
		// Смещение часового пояса
		$timezone_shift = get_current_timezone_shift();
		
		$rows = array(); // все клики за период
		$data = array(); // сгруппированные данные
		$arr_dates = array(); // даты для отчёта
		
		if($params['part'] == 'month') {
			$arr_dates = getMonthsBetween($params['from'], $params['to']);
		} elseif($params['part'] == 'day') {
			$arr_dates = getDatesBetween($params['from'], $params['to']);
		}
		
		global $pop_sort_by, $pop_sort_order;
		$pop_sort_by = 'cnt';
		$pop_sort_order = 1;
		
		// Режим показов конвертаций, все, только действия, только продажи, только лиды, без конвертаций.
		// В отчете "популярных параметров" этот фильтр работает ТОЛЬКО как параметр сортировки, в других режимах как условие для WHERE
		
		if($params['conv'] != 'all') {
			if($params['mode'] == 'popular') {
				if($params['conv'] == 'sale') {
					$pop_sort_by = 'sale';
				} elseif($params['conv'] == 'lead') {
					$pop_sort_by = 'lead';
				} elseif($params['conv'] == 'sale_lead') {
					$pop_sort_by = 'sale_lead';
				} elseif($params['conv'] == 'none') {
					$pop_sort_by = $params['col'];
					$pop_sort_order = -1;
				}
			} else {
				/*
				if($params['conv'] == 'sale') {
					$params['where'] = '`is_sale` = 1';
				} elseif($params['conv'] == 'lead') {
					//$params['where'] = '`is_lead` = 1';
				} elseif($params['conv'] == 'sale_lead') {
					//$params['where'] = '(`is_sale` = 1 or `is_lead` = 1)';
				} elseif($params['conv'] == 'none') {
					//$params['where'] = ''; 
					//$params['where'] = '`is_sale` = 0 and `is_lead` = 0';
				}
				*/
			}
		}
		
		$parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)
		
		$limit = 5000;
		$total = 30000;
		
		for($start = 0; $limit + $start <= $total; $start += $limit) {
			$rows = array();
			
			// Получаем порцию данных
			list($total, $rows, $campaign_params, $click_params) = get_clicks_rows($params, $start, $limit, $campaign_params, $click_params);
			
			// Режим обработки для Landing Page
			// группируем всю информацию с подчинённых переходов на родительские
			if($params['mode'] == 'lp' or $params['mode'] == '') {
				foreach($rows as $k => $r) {
					if($r['parent_id'] > 0) { // ссылка на оффер
						if(parent_row($r['parent_id'], 'id') == 0) {
							unset($rows[$k]); // не найден лэндинг, удаляем переход
							continue;
						}
						// не будем считать более одного исходящего с лэндинга
						$out_calc = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
						$parent_clicks[$r['parent_id']] = 1;
						
						// исходящие
						$rows[$r['parent_id']]['out'] +=  $out_calc;
					}
				}
			}
			
			if($params['mode'] == 'lp_offers') {
				
				foreach($rows as $k => $r) {
					if($r['parent_id'] > 0) { // ссылка на оффер
						// Несём продажи наверх, к лэндингу
						$rows[$r['parent_id']]['is_sale'] += $r['is_sale'];
						$rows[$r['parent_id']]['is_lead'] += $r['is_lead'];
						$rows[$r['parent_id']]['conversion_price_main'] += $r['conversion_price_main'];
						
						// А расходы вниз, к офферу
						$rows[$k]['click_price'] += $rows[$r['parent_id']]['click_price'];
						
						// Считаем исходящие для лэндингов
						$out_calc = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
						$parent_clicks[$r['parent_id']] = 1;
						
						$rows[$r['parent_id']]['out'] +=  $out_calc;
					}
				}
				
			}
			
			//dmp($params);
			
			// Фильтры показа
			if(!empty($params['filter'][1])) {
				$parent_clicks2 = array(); // $parent_clicks у нас для исходящих, а тут костыль (
				$rows_new = array(); // сюда будем складывать новые строчки, вместо unset существующих
				foreach($rows as $k => $v) {
					
					if($v['parent_id'] > 0) {
						if(empty($parent_clicks2[$v['parent_id']])) {
							$parent_clicks2[$v['parent_id']] = 1;
						} else {
							continue;
						}
					}
					
					//dmp($params['filter'][1]);
					
					$viz_filter = 1;
					
					foreach($params['filter'][1] as $name => $value) {
						list($cur_val, $parent_val) = explode('|', $value);
						
						if(
							$params['subgroup_by'] == 'out_id' and (
							
							($parent_val == 0 and ($v[$name] == $cur_val or parent_row($v['parent_id'], $name) == $cur_val))
						or ($parent_val > 0 and ($v['parent_id'] > 0 and parent_row($v['parent_id'], $name) == $parent_val) and $v[$name] == $cur_val))
						or ($v[$name] == $cur_val and (empty($parent_val) or $v[$params['group_by']] == $parent_val))
						
						) {
							/*
							$lp_offers_valid[$cur_val] = 1;
							
							// Сбрасываем parent_id, чтобы оффер у нас был как бы "самостоятельный", без лэндинга. Иначе придётся дорабатывать шаблон отчёта
							if($parent_val > 0) {
								$v['parent_id'] = 0;
							}
							$rows_new[$k] = $v;
							*/
							//dmp($v);
						} else {
							$viz_filter = 0;
							break;
						}
					}
					
					if($viz_filter) {
						//echo '1';
						$lp_offers_valid[$cur_val] = 1;
							
						// Сбрасываем parent_id, чтобы оффер у нас был как бы "самостоятельный", без лэндинга. Иначе придётся дорабатывать шаблон отчёта
						if($parent_val > 0) {
							$v['parent_id'] = 0;
						}
						$rows_new[$k] = $v;
					}
				}
				
				//dmp($rows_new);
				
				$rows = $rows_new;

				unset($rows_new); // Прибираемся
				unset($parent_clicks2);
			}
			
			//dmp($rows);
			
			// Режим популярных значений
			// Вынесен в отдельное условие из-за особой обработки по дням и месяцам
			if($params['mode'] == 'popular') {
				
				$data2 = array();
				
				foreach($rows as $r) {
					foreach($group_types as $k => $v) {
						$name = param_val($r, $k);

						$data[$k][$name]['cnt']    += $r['cnt'];
						$data[$k][$name]['price']  += $r['click_price'];
						$data[$k][$name]['unique'] += $r['is_unique'];
						$data[$k][$name]['income'] += $r['conversion_price_main'];
						$data[$k][$name]['sale']   += $r['is_sale'];
						$data[$k][$name]['lead']   += $r['is_lead'];
						$data[$k][$name]['out']    += $r['out'];
						
						// Продажи + Лиды = Действия.
						$sl = $r['is_sale'] + $r['is_lead'];
						if($sl > 2) $sl = 2; // Не более двух на переход
						
						$data[$k][$name]['sale_lead'] += $sl;
						
						// Если это не общий режим - добавляем информацию о датах
						if($params['part'] != 'all') {
							
							//$k1 = (trim($r['name']) == '' ? '{empty}' : $r['name']);
							$k2 = date($date_formats[$params['part']], $r['time_add']);
							$k3 = $groups[$r['is_sale'].$r['is_lead']];
							
							$data2[$k][$name][$k2][$k3]['cnt'] += 1;
							$data2[$k][$name][$k2][$k3]['cost'] += $r['clicks_price'];
							$data2[$k][$name][$k2][$k3]['earnings'] += $r['conversions_sum'];
							$data2[$k][$name][$k2][$k3]['is_parent_cnt'] += $r['is_parent'];
						}
					}
				}
			
			// Режим показа группировки офферов и лэндингов
			// Тоже вынесен в отдельное условие из-за особой обработки по дням и месяцам
			} elseif($params['mode'] == 'lp_offers') {
				
				$parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)
				
				// Вся статистика, без разбиения по времени
				foreach($rows as $r) {
					
					$k    = param_key($r, $params['group_by']);
					$name = param_val($r, $params['group_by']);
					
					if(!isset($data[$k])) {
						$data[$k] = array(
							'id'     => $k,
							'name'   => $name,
							'price'  => 0,
							'unique' => 0,
							'income' => 0,
							'sale'   => 0,
							'lead'   => 0,
							'out'    => 0,
							'cnt'    => 0,
							'sale_lead' => 0,
						);
					}
					
					// Продажи + Лиды = Действия. 
					$r['sale_lead'] = $r['is_sale'] + $r['is_lead'];
					if($r['sale_lead'] > 2) $r['sale_lead'] = 2; // Не более одного на переход

					// Подчиненные связи будут формироваться не по parent_id перехода,
					// а через другие параметры этого перехода (например через источники, с которых пришли)
					// Лэндинг 1
					// ├ Источник 1
					// └ Источник 2
					
					if($params['subgroup_by'] != $params['group_by']) {
						
						if($r['parent_id'] == 0) {
							$k1        = param_key($r, $params['subgroup_by']);
							$r['name'] = param_val($r, $params['subgroup_by']);
							
							// Общая часть статистики
							stat_inc($data[$k]['sub'][$k1], $r, $k1, $r['name']);
							
							// Выдаём офферу разрешение на показ (тут ведь у нас лэндинги, просто так не покажем)
							$lp_offers_valid[$k] = 1;
							
							// Информация о датах 
							if($params['part'] != 'all') {
								$timekey = date($date_formats[$params['part']], $r['time_add']);

								stat_inc($data[$k]['sub'][$k1][$timekey], $r, $k1, $r['name']);
							}
						} else {
							continue;
						}
					}
					
					// Подчиненные связи будут формироваться по parent_id перехода
					// Лэндинг 1
					// ├ Оффер 1
					// └ Оффер 2
					
					if($r['parent_id'] > 0) {
						// Будем считать исходящий только если у этого родителя его ещё нет
						$r['cnt'] = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
						$parent_clicks[$r['parent_id']] = 1;
						
						$parent_row = parent_row($r['parent_id']);
						$k0         = param_key($parent_row, $params['group_by']);
						
						$k1   = param_key($r, $params['subgroup_by']);
						$name = param_val($r, $params['subgroup_by']);
						
						stat_inc($data[$k0]['sub'][$k1], $r, $k1, $name);
						
						// Отмечаем исходящий для лэндинга
						if($r['cnt']) {
							$data[$k0]['out'] += 1;
						}
						
						$data[$k]['order'] = 1;
						
						// Выдаём офферу разрешение на показ
						$lp_offers_valid[$k0] = 1;
						$lp_offers_valid[$k1] = 1;
						
						// Запрошена информация по дням
						if($params['part'] != 'all') {
							
							$k2 = date($date_formats[$params['part']], $r['time_add']);
							
							$id   = param_key($r, $params['subgroup_by']);
							$name = param_val($r, $params['subgroup_by']);
							
							stat_inc($data[$k0]['sub'][$k1][$k2], $r, $id, $name);
						}
						
					// Обычный инкремент статистики
					} else {
						stat_inc($data[$k], $r, $k, $name);

						// Информация о датах
						if($params['part'] != 'all') {
							$timekey = date($date_formats[$params['part']], $r['time_add']);
							stat_inc($data[$k][$timekey], $r, $k, $name);
						}
					}
				}
				
				//dmp($data);
				/*************/
				
			} else {
				// Данные выбраны, начинаем группировку
				// Статистика за весь период
				if($params['part'] == 'all') {
					
					$parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)
					
					// Вся статистика, без разбиения по времени
					foreach($rows as $r) {
						$k    = param_key($r, $params['group_by']);
						$name = param_val($r, $params['group_by']);
						
						// Продажи + Лиды = Действия. 
						$r['sale_lead'] = $r['is_sale'] + $r['is_lead'];
						if($r['sale_lead'] > 2) $r['sale_lead'] = 2; // Не более одного на переход
						
						stat_inc($data[$k], $r, $k, $name);
					}

				// Статистика по дням
				} else {

					foreach($rows as $r) {
						$k1 = (trim($r['name']) == '' ? '{empty}' : $r['name']);
						$k2 = date($date_formats[$params['part']], $r['time_add']);
						$k3 = $groups[$r['is_sale'].$r['is_lead']];
						
						// Обрезаем реферер до домена
						if($params['group_by'] == 'referer') {
							$url = parse_url($k1);
							$k1 = $r['name'] = $url['host'];
						}
						
						
						$data[$k1]['sale'] += $r['is_sale'];
						$data[$k1]['lead'] += $r['is_lead'];
						$data[$k1]['sale_lead'] += $r['is_sale'];
						$data[$k1]['sale_lead'] += $r['is_lead'];
						
						$data[$k1][$k2][$k3]['cnt'] += 1;
						$data[$k1][$k2][$k3]['cost'] += $r['clicks_price'];
						$data[$k1][$k2][$k3]['earnings'] += $r['conversions_sum'];
						$data[$k1][$k2][$k3]['is_parent_cnt'] += $r['is_parent'];
					}
				}
			} // Стандартный режим
		} // Цикличный сбор данных из БД
		
		// ----------------------------------------
		// Постобработка, когда ВСЕ данные получены
		// ----------------------------------------
		
		//if($params['part'] == 'all') {
		if($params['mode'] == 'popular') {
			
			if($params['group_by'] != '') {
				
				foreach($data as $k => $v) {
					if($k != $params['group_by']) {
						unset($data[$k]);
					} else {
						$total = sum_arr($v, 'cnt');
						foreach($data[$k] as $k1 => $v1) {
							$data[$k][$k1]['total'] = $total;
						}
						
					}
				}
			} else {
				foreach($data as $k => $v) {
					uasort($v, 'params_order');

					$data[$k] = current($v);
					
					// Для этого режима нам нужны ТОЛЬКО нулевые конвертации
					if($params['conv'] == 'none' and $data[$k][$params['col']] != 0) {
						unset($data[$k]);
						continue;
					}
					
					$data[$k]['total'] = sum_arr($v, 'cnt');
					$data[$k]['name'] = $k;
					$data[$k]['popular'] = current(array_keys($v));
				}
			}
			
			// Убираем из популярных "не определено", отфильрованные значения и если 100%
			
			foreach($data as $k => $r) {
				if($r['popular'] == $group_types[$r['name']][1]
					or !empty($params['filter'][0][$r['name']])
					or ($r['cnt'] == $r['total'] or round($r['cnt'] / $r['total'] * 100) == 100)
					) {
					unset($data[$k]);
				}
			}
			
			if($params['part'] != 'all') {
				$data3 = array();
				foreach($data as $k => $v) {
					
					//$name = $group_types[$v['name']][1];
					$name = $v['name'];
					
					$data3[$name] = $data2[$k][$v['popular']];
					$data3[$name]['popular'] = $v['popular'];
				}
				unset($data2);
				$data = $data3;
			}
		} else {
	
			// Убираем строчки с конверсиями
			if($params['conv'] == 'none') {
				foreach($data as $k => $v) {
					if($v['sale_lead'] > 0) unset($data[$k]);
				}
			} elseif($params['conv'] == 'sale_lead') {
				foreach($data as $k => $v) {
					if($v['sale_lead'] == 0) unset($data[$k]);
				}
			} elseif($params['conv'] == 'sale') {
				foreach($data as $k => $v) {
					if($v['sale'] == 0) unset($data[$k]);
				}
			} elseif($params['conv'] == 'lead') {
				foreach($data as $k => $v) {
					if($v['lead'] == 0) unset($data[$k]);
				}
			}
		}
		//}
		
		if($part != 'all') {
			// Оставляем только те даты, за которые есть данные
			$arr_dates = strip_empty_dates($arr_dates, $data);
		}
		
		// Особая сортировка для режима lp_offers, офферы с прямыми переходами в конце
		if($params['mode'] == 'lp_offers') { //and $params['part'] == 'all'
			uasort($data, 'lp_order');
			
			//dmp($data);
			$lp_offers_valid = array_keys($lp_offers_valid);
			$ln = 0; // номер лэндинга - условное значение, необходимое для группировки при сортировке таблицы с подчиненными офферами. У лэндинга и его офферов должен быть один номер, уникальный для этой группы
			foreach($data as $k => $v) {
				if(!in_array($k, $lp_offers_valid) or $v['cnt'] == 0) {
					unset($data[$k]);
				} else {
					$data[$k]['ln'] = $ln;
					if(!empty($data[$k]['sub'])) {
						foreach($data[$k]['sub'] as $k0 => $v0) {
							$data[$k]['sub'][$k0]['ln'] = $ln;
						}
					}
					$ln++;
				}
			}
		}
		
		// Удаляем страницы, у которых нет исходящих (Это не Лэндинги)
		if(($params['mode'] == 'lp' and $params['part'] == 'all') and empty($parent_val) ) {
			foreach($data as $k => $v) {
				if(empty($v['out'])) {
					unset($data[$k]);
				}
			}
		}
		
		// cсылка "Другие", для Площадки, параметров ссылки и перехода 
		// если не выбран какой-то определенный лэндинг.
		//
		if(!isset($params['filter'][1]['out_id']) and
			$params['mode'] == 'lp_offers' and
			($params['subgroup_by'] == 'referer'
			or strstr($params['subgroup_by'], 'click_param_value') !== false)
		) {
			$max_sub = 50; // После скольки объектов начинаем сворачивать
			foreach($data as $k => &$v) {
				if(isset($v['sub']) and count($v['sub']) > $max_sub) {
					uasort($v['sub'], 'sub_order');
					
					$sub = array_slice($v['sub'], $max_sub);
					$v['sub'] = array_slice($v['sub'], 0, $max_sub);
					
					$other = array(); // Сюда мы соберём всю статистику "других"
					foreach($sub as $sub_row) {
						stat_inc($other, $sub_row, -1, 'Другие');
					}
					$v['sub'][-1] = $other;
					
					//dmp($other);
				}
			}
		}
		
		//dmp($params);
		
		return array(
			'data' => $data, 
			'dates' => $arr_dates, 
			'click_params' => $click_params,
			'campaign_params' => $campaign_params
		);
	} 
	
	// Сортировка по кликам 
	function sub_order($a, $b) {
		if($a['cnt'] == $b['cnt']) {
			return 0;
		}
		return ($a['cnt'] < $b['cnt']) ? 1 : -1;
	}
	
	function get_clicks_report_grouped ($main_column, $group_by, $limited_to='', $report_type='daily', $from='', $to='') {
        $timezone_shift=get_current_timezone_shift();

        switch ($report_type)
        {
            case 'hourly':
                $time_column_alias='date_add_hour';
                $time_column="HOUR(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."')) as date_add_hour";
                $group_time_column="HOUR(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."'))";
                $order_time_column="date_add_hour";
                if ($from=='')
                {
                    if ($to=='')
                    {
                        $time_filter="1=1";
                    }
                    else
                    {
                        $time_filter="CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') <= '"._str($to)." 23:59:59'";
                    }
                }
                else
                {
                    if ($to=='')
                    {
                        $time_filter="CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') >= '"._str($from)." 00:00:00'";
                    }
                    else
                    {
                        $time_filter="CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') BETWEEN '"._str($from)." 00:00:00' AND '"._str($to)." 23:59:59'";
                    }
                }
            break;

            case 'daily':
                $time_column_alias="date_add_day";
                $time_column="DATE(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."')) as date_add_day";
                $group_time_column="DATE(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."'))";
                $order_time_column="date_add_day";

                $time_filter="`date_add_day` >= DATE_SUB( DATE(CONVERT_TZ(NOW(), '+00:00', '"._str($timezone_shift)."')) , INTERVAL 7 DAY)";

                if ($from=='')
                {
                    if ($to=='')
                    {
                        $from=get_current_day('-6 days');
                        $to=get_current_day();
                    }
                    else
                    {
                        $from=date ('Y-m-d', strtotime('-6 days', strtotime($to)));
                    }
                }
                else
                {
                    if ($to=='')
                    {
                        $to=date ('Y-m-d', strtotime('+6 days', strtotime($from)));
                    }
                    else
                    {
                        // Will use existing values
                    }
                }

                $time_filter="CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') BETWEEN '"._str($from)." 00:00:00' AND '"._str($to)." 23:59:59'";    
            break;

            case 'monthly':
                $time_column_alias="date_add_day";
                $time_column="DATE(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."')) as date_add_day";
                $group_time_column="DATE(CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."'))";
                $order_time_column="date_add_day";

                $time_filter="`date_add_day` >= DATE_SUB( DATE(CONVERT_TZ(NOW(), '+00:00', '"._str($timezone_shift)."')) , INTERVAL 7 DAY)";

                if ($from=='')
                {
                    if ($to=='')
                    {
                        $from=get_current_day('-6 months');
                        $to=get_current_day();
                    }
                    else
                    {
                        $from=date ('Y-m-d', strtotime('-6 months', strtotime($to)));
                    }
                }
                else
                {
                    if ($to=='')
                    {
                        $to=date ('Y-m-d', strtotime('+6 months', strtotime($from)));
                    }
                    else
                    {
                        $from=date ('Y-m-d',  strtotime('13.'.$from));
                        $to=date ('Y-m-d', strtotime('13.'.$to));
                    }
                }
                   $from=date ('Y-m-01',  strtotime($from));
                   $to=date ('Y-m-t',  strtotime($to));
                $time_filter="CONVERT_TZ(date_add, '+00:00', '"._str($timezone_shift)."') BETWEEN '"._str($from)." 00:00:00' AND '"._str($to)." 23:59:59'";    
            break;

            default: 
                $time_column_alias="date_add_day";
                $time_column="date_add_day";
                $group_time_column="date_add_day";
                $order_time_column="date_add_day";
                $time_filter="`date_add_day` >= DATE_SUB( CURDATE() , INTERVAL 7 DAY)";
            break;
        }

        if ($limited_to!=''){$limited_to=" and `"._str($main_column)."`='"._str($limited_to)."'";}
    
        if ($main_column==$group_by)
        {
            $sql="SELECT 
                    `"._str($main_column)."`, 
                    {$time_column}, 
                    SUM(`click_price`) as clicks_price, 
                    SUM(`conversion_price_main`) as conversions_sum, 
                    SUM(`is_parent`) as parent_count, 
                    `is_sale`, 
                    `is_lead`, 
                    COUNT(`id`) AS cnt
                FROM 
                    `tbl_clicks`
                WHERE 
                    {$time_filter}
                    {$limited_to}
                GROUP BY 
                    `"._str($main_column)."`, 
                    `is_sale`, 
                    `is_lead`,
                    {$group_time_column}
                ORDER BY 
                    `"._str($main_column)."`, 
                    {$order_time_column} ASC
                    "; 
        }
        else
        {
            switch ($group_by)
            {
                case 'user_platform': 
                    $sql="SELECT 
                            `"._str($main_column)."`, 
                            CONCAT(`user_platform`, ' ', `user_platform_info`) as user_platform, 
                            {$time_column}, 
                            SUM(`click_price`) as clicks_price, 
                            SUM(`conversion_price_main`) as conversions_sum, 
                            SUM(`is_parent`) as parent_count, 
                            `is_sale`, 
                            `is_lead`, 
                            COUNT(`id`) AS cnt
                        FROM 
                            `tbl_clicks`
                        WHERE 
                            {$time_filter}
                            {$limited_to}
                        GROUP BY 
                            `"._str($main_column)."`, 
                            `user_platform`,
                            `user_platform_info`,
                            `is_sale`, 
                            `is_lead`,
                            {$group_time_column}
                        ORDER BY 
                            `"._str($main_column)."`, 
                            `"._str($group_by)."`,
                            {$order_time_column} ASC
                            ";                
                break;

                case 'referer':
                    $sql="SELECT 
                        `"._str($main_column)."`, 
                        LEFT(referer, IF(LOCATE('/', referer, 8) = 0, LENGTH(referer), LOCATE('/', referer, 8))) as `referer`,
                        {$time_column}, 
                        SUM(`click_price`) as clicks_price, 
                        SUM(`conversion_price_main`) as conversions_sum, 
                        SUM(`is_parent`) as parent_count, 
                        `is_sale`, 
                        `is_lead`, 
                        COUNT(`id`) AS cnt
                    FROM 
                        `tbl_clicks`
                    WHERE 
                        {$time_filter}
                        {$limited_to}
                    GROUP BY 
                        `"._str($main_column)."`, 
                        LEFT(referer, IF(LOCATE('/', referer, 8) = 0, LENGTH(referer), LOCATE('/', referer, 8))), 
                        `is_sale`, 
                        `is_lead`,
                        {$group_time_column}
                    ORDER BY 
                        `"._str($main_column)."`, 
                        LEFT(referer, IF(LOCATE('/', referer, 8) = 0, LENGTH(referer), LOCATE('/', referer, 8))),
                        {$order_time_column} ASC
                        ";
                break;

                default: 
                    $sql="SELECT 
                            `"._str($main_column)."`, 
                            `"._str($group_by)."`, 
                            {$time_column}, 
                            SUM(`click_price`) as clicks_price, 
                            SUM(`conversion_price_main`) as conversions_sum, 
                            SUM(`is_parent`) as parent_count, 
                            `is_sale`, 
                            `is_lead`, 
                            COUNT(`id`) AS cnt
                        FROM 
                            `tbl_clicks`
                        WHERE 
                            {$time_filter}
                            {$limited_to}
                        GROUP BY 
                            `"._str($main_column)."`, 
                            `"._str($group_by)."`, 
                            `is_sale`, 
                            `is_lead`,
                            {$group_time_column}
                        ORDER BY 
                            `"._str($main_column)."`, 
                            `"._str($group_by)."`,
                            {$order_time_column} ASC
                            ";
                break;            
            }
        }

        $result=mysql_query($sql);
        while ($row=mysql_fetch_assoc($result))
        {
            if ($row[$main_column]==''){$row[$main_column]='{empty}';}
            $group_by_value=$row[$group_by];
            if ($group_by_value==''){$group_by_value='{empty}';}

            switch ($row['is_sale'].$row['is_lead'])
            {
                case '00':
                    $click_type='click';
                break;

                case '01':
                    $click_type='lead';
                break;

                case '10':
                    $click_type='sale';
                break;

                case '11':
                    $click_type='sale_lead';
                break;
            }

            if ($main_column==$group_by)
            {
                if($report_type == 'monthly') {
                    $arr_report_data[$row[$main_column]][date('m.Y', strtotime($row[$time_column_alias]))][$click_type]=array('cnt'=>$row['cnt'], 'cost'=>$row['clicks_price'], 'earnings'=>$row['conversions_sum'], 'is_parent_cnt'=>$row['parent_count']);
                } else {
                    $arr_report_data[$row[$main_column]][$row[$time_column_alias]][$click_type]=array('cnt'=>$row['cnt'], 'cost'=>$row['clicks_price'], 'earnings'=>$row['conversions_sum'], 'is_parent_cnt'=>$row['parent_count']);
                }
            }
            else
            {
                if($report_type == 'monthly') {
                    $arr_report_data[$row[$main_column]][$group_by_value][date('m.Y', strtotime($row[$time_column_alias]))][$click_type]=array('cnt'=>$row['cnt'], 'cost'=>$row['clicks_price'], 'earnings'=>$row['conversions_sum'], 'is_parent_cnt'=>$row['parent_count']);
                } else { 
                    $arr_report_data[$row[$main_column]][$group_by_value][$row[$time_column_alias]][$click_type]=array('cnt'=>$row['cnt'], 'cost'=>$row['clicks_price'], 'earnings'=>$row['conversions_sum'], 'is_parent_cnt'=>$row['parent_count']);
                }
            }
        }

        return $arr_report_data;
    }

	
	// Суммирует значения из двухмерного массива
	function sum_arr($arr, $param = 'cnt') {
		$summ = 0;
		foreach($arr as $v) {
			$summ += $v[$param];
		}
		return $summ;
	}
	
	// Сортировка лэндингов
	function lp_order($a, $b) {
		if($a['order'] == $b['order']) {
			return 0;
		}
		return ($a['order'] < $b['order']) ? -1 : 1;
	}
	
	// Сортировка по конверсии
	function params_order($a, $b) {
		global $pop_sort_by, $pop_sort_order;
		
		$k1 = $a[$pop_sort_by];
		$k2 = $b[$pop_sort_by];
		if($k1 == $k2) {
			// Вторичная сортировка по переходам
			if($pop_sort_by != 'cnt') {
				$k1 = $a['cnt'];
				$k2 = $b['cnt'];
				if($k1 == $k2) {
					return 0;
				}
				return ($k1 < $k2) ? 1 : -1;
			} else {
				return 0;
			}
		}
		return ($k1 < $k2) ? $pop_sort_order * 1 : $pop_sort_order * -1;
	}
	
	/* v2 */
	function get_clicks_report_element2 ($data) { 
		global $report_cols, $currencies;
		$out = array();
		foreach($report_cols as $col => $options) {
			$func = 't_' . $col;
			//$out[] = '<span class="timetab sdata ' . $col . '">' . $func($data) . '</span>';
			$out[] = '<span class="timetab sdata ' . $col . '">' . sortdata($col, $data) . '</span>';
			/*
			if($options['money']) {
				// Для денежных колонок добавляем все возможные значения
				foreach($currencies as $currency => $rate) {
					$out[] = '<span class="sdata ' . $col . '">'.$func($data).'</span>';
				}
			} else {
				
			}
			*/
		}
		return join('', $out);
		/*
		$clicks_data    = $data['click'];
		$leads_data     = $data['lead'];
		$sales_data     = $data['sale'];
		$saleleads_data = $data['sale_lead'];
		
		// Выходные данные
		$out = array();
		
		if ((isset($clicks_data)) || (isset($leads_data)) || (isset($sales_data)) || isset($saleleads_data))
		{
			$clicks_count = array_sum (array($clicks_data['cnt'], $leads_data['cnt'], $sales_data['cnt'], $saleleads_data['cnt']));
			$leads_count  = array_sum (array($leads_data['cnt'], $saleleads_data['cnt']));
			$sales_count  = array_sum (array($sales_data['cnt'], $saleleads_data['cnt']));

			$clicks_cost  = array_sum (array($clicks_data['cost'], $leads_data['cost'], $sales_data['cost'], $saleleads_data['cost']));			
			
			$sales_amount = array_sum (array($sales_data['earnings'], $saleleads_data['earnings']));
			$sales_amount_rub = $sales_amount * 30;
			
			$profit_amount = $sales_amount-$clicks_cost;
			$profit_amount_rub = $profit_amount*30;

			if ($sales_count>0) {
				$conversion=round2($sales_count/$clicks_count*100).'%';
				$epc=$sales_amount/$clicks_count;
				$epc_rub=$epc*30;
			}
			else
			{
				$conversion="0%";
			}

			if ($leads_count>0)
			{
				$conversion_leads='<b>'.round2($leads_count/$clicks_count).'%</b>';
				$leads_clicks="<b>{$clicks_count}:{$leads_count}</b>";
				$lead_price=$clicks_cost/$leads_count;
				$lead_price_rub=($clicks_cost/$leads_count)*30;
			}
			else
			{
				$leads_clicks="{$clicks_count}:{$leads_count}";
				$conversion_leads="0%";
				$lead_price='';
				$lead_price_rub='';
			}

			// Round and format values
			$sales_amount=round($sales_amount, 2);
			$sales_amount_rub=round($sales_amount_rub, 2);
			$profit_amount=round($profit_amount, 2);
			$profit_amount_rub=round($profit_amount_rub, 2);
			
			if ($profit_amount==0)
			{
				$profit_amount="<span style='color:lightgray; font-weight:normal;'>$0</span>";
				$profit_amount_rub="<span style='color:lightgray; font-weight:normal;'>0р.</span>";
			}
			else
			{
				if ($profit_amount<0)
				{
					$profit_amount='<span style="color:red;">-$'.abs($profit_amount)."</span>";
					$profit_amount_rub="<span style='color:red;'>{$profit_amount_rub} р.</span>";						
				}
				else
				{
					$profit_amount='$'.$profit_amount;
					$profit_amount_rub=$profit_amount_rub.' р.';
				}
			}
			
			if (is_numeric ($lead_price)) {$lead_price='$'.round($lead_price, 2);}
			if (is_numeric ($lead_price_rub)) {$lead_price_rub=round($lead_price_rub, 2).'р.';}
			
			if ($epc>=0.01){$epc=round($epc, 2);}else{$epc=round($epc, 3);}
			if ($epc_rub>=0.01){$epc_rub=round($epc_rub, 2);}else{$epc_rub=round($epc_rub, 3);}


			if ($clicks_cost>0)
			{
				$roi=round(($sales_amount-$clicks_cost)/$clicks_cost*100).'%';
				if ($roi<=0){$roi="<span style='color:red;'>{$roi}</span>";}
			}
			else
			{
				$roi='';
			}

			if ($sales_count>0)
			{
				return "<span class='sdata leads leads_clicks'>{$leads_clicks}</span>
						<span class='sdata leads leads_conversion'>{$conversion_leads}</span> 
						<span class='sdata leads leads_price usd'>{$lead_price}</span>
						<span class='sdata leads leads_price rub'>{$lead_price_rub}</span>
						<b><span class='sdata clicks'>{$clicks_count}:{$sales_count}</span><span class='sdata conversion'>{$conversion}</span><span class='sdata sales usd'>{$profit_amount}</span><span class='sdata sales rub'>{$profit_amount_rub}</span><span class='sdata epc usd'>\${$epc}</span><span class='sdata epc rub'>{$epc_rub} р.</span><span class='sdata roi'>{$roi}</span></b>";				
			}
			else
			{
				return "<span class='sdata leads leads_clicks'>{$leads_clicks}</span>
						<span class='sdata leads leads_conversion'>{$conversion_leads}</span> 
						<span class='sdata leads leads_price'>{$lead_price}</span>
						<span class='sdata clicks'>{$clicks_count}</span><span class='sdata conversion'>{$conversion}</span><span class='sdata roi' style='color:lightgray;'>-</span>
						<span style='color:lightgray;' class='sdata epc usd'>$0</span><span style='color:lightgray;' class='sdata epc rub'>0 р.</span>
						<span class='sdata sales usd' style='font-weight:bold;'>{$profit_amount}</span><span class='sdata sales rub' style='font-weight:bold;'>{$profit_amount_rub}</span>";
			}
		}
		else
		{
			return '';
		}
		*/
	}
	/* /v2 */
	
	function get_clicks_report_element ($clicks_data, $leads_data, $sales_data, $saleleads_data)
	{ 
		if ((isset($clicks_data)) || (isset($leads_data)) || (isset($sales_data)) || isset($saleleads_data))
		{
			$clicks_count = array_sum (array($clicks_data['cnt'], $leads_data['cnt'], $sales_data['cnt'], $saleleads_data['cnt']));
			$leads_count  = array_sum (array($leads_data['cnt'], $saleleads_data['cnt']));
			$sales_count  = array_sum (array($sales_data['cnt'], $saleleads_data['cnt']));

			$clicks_cost  = array_sum (array($clicks_data['cost'], $leads_data['cost'], $sales_data['cost'], $saleleads_data['cost']));			
			
			$sales_amount = array_sum (array($sales_data['earnings'], $saleleads_data['earnings']));
			$sales_amount_rub = $sales_amount * 30;
			
			$profit_amount = $sales_amount-$clicks_cost;
			$profit_amount_rub = $profit_amount*30;

			if ($sales_count>0) {
				$conversion=round2($sales_count/$clicks_count*100).'%';
				$epc=$sales_amount/$clicks_count;
				$epc_rub=$epc*30;
			}
			else
			{
				$conversion="0%";
			}

			if ($leads_count>0)
			{
				$conversion_leads='<b>'.round2($leads_count/$clicks_count).'%</b>';
				$leads_clicks="<b>{$clicks_count}:{$leads_count}</b>";
				$lead_price=$clicks_cost/$leads_count;
				$lead_price_rub=($clicks_cost/$leads_count)*30;
			}
			else
			{
				$leads_clicks="{$clicks_count}:{$leads_count}";
				$conversion_leads="0%";
				$lead_price='';
				$lead_price_rub='';
			}

			// Round and format values
			$sales_amount=round($sales_amount, 2);
			$sales_amount_rub=round($sales_amount_rub, 2);
			$profit_amount=round($profit_amount, 2);
			$profit_amount_rub=round($profit_amount_rub, 2);
			
			if ($profit_amount==0)
			{
				$profit_amount="<span style='color:lightgray; font-weight:normal;'>$0</span>";
				$profit_amount_rub="<span style='color:lightgray; font-weight:normal;'>0р.</span>";
			}
			else
			{
				if ($profit_amount<0)
				{
					$profit_amount='<span style="color:red;">-$'.abs($profit_amount)."</span>";
					$profit_amount_rub="<span style='color:red;'>{$profit_amount_rub} р.</span>";						
				}
				else
				{
					$profit_amount='$'.$profit_amount;
					$profit_amount_rub=$profit_amount_rub.' р.';
				}
			}
			
			if (is_numeric ($lead_price)) {$lead_price='$'.round($lead_price, 2);}
			if (is_numeric ($lead_price_rub)) {$lead_price_rub=round($lead_price_rub, 2).'р.';}
			
			if ($epc>=0.01){$epc=round($epc, 2);}else{$epc=round($epc, 3);}
			if ($epc_rub>=0.01){$epc_rub=round($epc_rub, 2);}else{$epc_rub=round($epc_rub, 3);}


			if ($clicks_cost>0)
			{
				$roi=round(($sales_amount-$clicks_cost)/$clicks_cost*100).'%';
				if ($roi<=0){$roi="<span style='color:red;'>{$roi}</span>";}
			}
			else
			{
				$roi='';
			}

			if ($sales_count>0)
			{
				return "<span class='sdata leads leads_clicks'>{$leads_clicks}</span>
						<span class='sdata leads leads_conversion'>{$conversion_leads}</span> 
						<span class='sdata leads leads_price usd'>{$lead_price}</span>
						<span class='sdata leads leads_price rub'>{$lead_price_rub}</span>
						<b><span class='sdata clicks'>{$clicks_count}:{$sales_count}</span><span class='sdata conversion'>{$conversion}</span><span class='sdata sales usd'>{$profit_amount}</span><span class='sdata sales rub'>{$profit_amount_rub}</span><span class='sdata epc usd'>\${$epc}</span><span class='sdata epc rub'>{$epc_rub} р.</span><span class='sdata roi'>{$roi}</span></b>";				
			}
			else
			{
				return "<span class='sdata leads leads_clicks'>{$leads_clicks}</span>
						<span class='sdata leads leads_conversion'>{$conversion_leads}</span> 
						<span class='sdata leads leads_price'>{$lead_price}</span>
						<span class='sdata clicks'>{$clicks_count}</span><span class='sdata conversion'>{$conversion}</span><span class='sdata roi' style='color:lightgray;'>-</span>
						<span style='color:lightgray;' class='sdata epc usd'>$0</span><span style='color:lightgray;' class='sdata epc rub'>0 р.</span>
						<span class='sdata sales usd' style='font-weight:bold;'>{$profit_amount}</span><span class='sdata sales rub' style='font-weight:bold;'>{$profit_amount_rub}</span>";
			}
		}
		else
		{
			return '';
		}
	}
        
        
        function get_sales($from, $to, $days, $month) {
            $timezone_shift = get_current_timezone_shift();
            $sql = 'SELECT *, `cnv`.`date_add` as `date` FROM `tbl_conversions` `cnv` LEFT JOIN `tbl_clicks` `clc` ON `cnv`.`subid` = `clc`.`subid`  WHERE `cnv`.`status` = 0 AND CONVERT_TZ(`cnv`.`date_add`, "+00:00", "'._str($timezone_shift).'") BETWEEN "'._str($from).' 00:00:00" AND "'._str($to).' 23:59:59" ORDER BY `cnv`.`date_add` ASC';
            
            $r = mysql_query($sql);
            
            if (mysql_num_rows($r) == 0) {
                return false;
            }
            
            $data = array();
            $return = array();
            
            while ($f = mysql_fetch_assoc($r)) {
                $data[] = $f;
            }
            
            foreach ($data as $row) {
                if ($row['source_name'] == '') {
                    $row['source_name'] = '_';
                }
                foreach ($days as $day) {
                    $d = (!$month)?date('d.m', strtotime($day)):$day;
                    if ($d == date((!$month)?'d.m':'m.Y', strtotime($row['date']))) {
                        $return[$row['source_name']][$d]++;
                    }
                }
            }
            
            return $return;
        }
        
        /*
         * Убираем даты, за которые нет данных
         */
        function strip_empty_dates($arr_dates, $arr_report_data, $mode = 'date') {
			$dates = array();
			$begin = false;
			
			if($mode == 'group') {
				$arr_report_data = current($arr_report_data);
			}
			
			foreach ($arr_report_data as $source_name => $data) {
				foreach($data as $k => $v) {
					if($mode == 'month') $k = date('m.Y', strtotime($k));
					$dates[$k] = 1;
				}
			}
			
			foreach($arr_dates as $k => $v) {
				if(!isset($dates[$v]) and !$begin) unset($arr_dates[$k]);
				else $begin  = true;
			}
			return $arr_dates;
		}
		
		/*
		 * Готовит к выводу параметры перехода
		 */
		function params_list($row, $name, $source_name = '') {
			global $source_config;
			
			// Если есть фильтр по источнику - считаем именованные параметры
			if(!empty($source_config[$source_name]['params'])) {
				$named_params = $source_config[$source_name]['params'];
				$named_params_cnt = count($named_params);
				$named_params_keys = array_keys($named_params);
			} else {
				$named_params_cnt = 0;
			}
			
			$out = array();
			for($i = 1; $i <= 15; $i++) {
				if(empty($row[$name.$i])) continue;
				
				list($param_name, $param_val) = click_param($i, $row[$name.$i], $source_name);
				/*
				if($i <= $named_params_cnt) {
					$param_name = $named_params[$named_params_keys[$i]]['name'];
				} else {
					$param_name = $i - $named_params_cnt;
				}
				*/
				$out[] = $param_name.': '.$param_val;
			}
			/*
			$i = 1;
			
			while(isset($row[$name.$i])) {
				if($row[$name.$i] != '') {
					$out[] = $i.': '.$row[$name.$i] . '<br />';
				}
				$i++;
			}*/
			return $out;
		}
		
		/*
		* Функция вывода кнопок статистики в интерфейс
		*/
		function type_subpanel() {
			global $type;

			// Кнопки типов статистики
			$type_buttons = array(
				'all_stats' => 'Все',
				'daily_stats' => 'По дням',
				'monthly_stats' => 'По месяцам',
			);
			
			$out = '<div class="btn-group">';
		    foreach($type_buttons as $k => $v) {
		    	$out .= '<a href="?act=reports&type='.$k.'&subtype='.$_GET['subtype'].'" type="button" class="btn btn-default '.($type==$k ? 'active' : '').'">'.$v.'</a>';
		    }
		    $out .= '</div>';
		    return $out;
		}
		
		// Литералы для группировок
		$group_types = array(
			'out_id'          => array('Оффер', 'Без оффера', 'Офферы'), 
			'source_name'     => array('Источник', 'Не определён', 'Источники'),
			'campaign_name'   => array('Кампания', 'Не определена', 'Кампании'),
			'ads_name'        => array('Объявление', 'Не определено', 'Объявления'),
			'referer'         => array('Площадка', 'Не определена', 'Площадки'),
			'user_os'         => array('ОС', 'Не определена', 'ОС'),
			'user_platform'   => array('Платформа', 'Не определена', 'Платформы'),
			'user_browser'    => array('Браузер', 'Не определен', 'Браузеры'),
			'country'         => array('Страна', 'Не определена', 'Страны'),
			'state'           => array('Регион', 'Не определен', 'Регионы'),
			'city'            => array('Город', 'Не определен', 'Города'),
			'isp'             => array('Провайдер', 'Не определен', 'Провайдеры'),
			'campaign_param1' => array('Параметр ссылки #1', 'Не определен', 'Параметр ссылки #1'),
			'campaign_param2' => array('Параметр ссылки #2', 'Не определен', 'Параметр ссылки #2'),
			'campaign_param3' => array('Параметр ссылки #3', 'Не определен', 'Параметр ссылки #3'),
			'campaign_param4' => array('Параметр ссылки #4', 'Не определен', 'Параметр ссылки #4'),
			'campaign_param5' => array('Параметр ссылки #5', 'Не определен', 'Параметр ссылки #5'),
			
			'click_param_value1'  => array('Параметр перехода #1', 'Не определен', 'Параметр перехода #1'),
			'click_param_value2'  => array('Параметр перехода #2', 'Не определен', 'Параметр перехода #2'),
			'click_param_value3'  => array('Параметр перехода #3', 'Не определен', 'Параметр перехода #3'),
			'click_param_value4'  => array('Параметр перехода #4', 'Не определен', 'Параметр перехода #4'),
			'click_param_value5'  => array('Параметр перехода #5', 'Не определен', 'параметр перехода #5'),
			'click_param_value6'  => array('Параметр перехода #6', 'Не определен', 'Параметр перехода #6'),
			'click_param_value7'  => array('Параметр перехода #7', 'Не определен', 'Параметр перехода #7'),
			'click_param_value8'  => array('Параметр перехода #8', 'Не определен', 'Параметр перехода #8'),
			'click_param_value9'  => array('Параметр перехода #9', 'Не определен', 'Параметр перехода #9'),
			'click_param_value10' => array('Параметр перехода #10', 'Не определен', 'Параметр перехода #10'),
			'click_param_value11' => array('Параметр перехода #11', 'Не определен', 'Параметр перехода #11'),
			'click_param_value12' => array('Параметр перехода #12', 'Не определен', 'Параметр перехода #12'),
			'click_param_value13' => array('Параметр перехода #13', 'Не определен', 'Параметр перехода #13'),
			'click_param_value14' => array('Параметр перехода #14', 'Не определен', 'Параметр перехода #14'),
			'click_param_value15' => array('Параметр перехода #15', 'Не определен', 'Параметр перехода #15'),/*
			'cp1'  => array('Параметр перехода #1', 'Не определен', 'параметру #1'),
			'cp2'  => array('Параметр перехода #2', 'Не определен', 'параметру #2'),
			'cp3'  => array('Параметр перехода #3', 'Не определен', 'параметру #3'),
			'cp4'  => array('Параметр перехода #4', 'Не определен', 'параметру #4'),
			'cp5'  => array('Параметр перехода #5', 'Не определен', 'параметру #5'),
			'cp6'  => array('Параметр перехода #6', 'Не определен', 'параметру #6'),
			'cp7'  => array('Параметр перехода #7', 'Не определен', 'параметру #7'),
			'cp8'  => array('Параметр перехода #8', 'Не определен', 'параметру #8'),
			'cp9'  => array('Параметр перехода #9', 'Не определен', 'параметру #9'),
			'cp10' => array('Параметр перехода #10', 'Не определен', 'параметру #10'),
			'cp11' => array('Параметр перехода #11', 'Не определен', 'параметру #11'),
			'cp12' => array('Параметр перехода #12', 'Не определен', 'параметру #12'),
			'cp13' => array('Параметр перехода #13', 'Не определен', 'параметру #13'),
			'cp14' => array('Параметр перехода #14', 'Не определен', 'параметру #14'),
			'cp15' => array('Параметр перехода #15', 'Не определен', 'параметру #15'),*/
		);
		
		/*
		 * Ссылка согласно параметрам отчёта
		 */
		
		function report_lnk($params, $set = false) {
			if($set and is_array($set)) {
				foreach($set as $k => $v) {
					if($k == 'filter') {
						$k = 'filter_str';
					}
					$params[$k] = $v;
				}
			}
			
			
			$tmp = array();
			
			foreach($params['filter_str'] as $k => $v) {
				$tmp[] = $k . ':' . $v;
			}
			$vars = array(
				'act' => 'reports',
				'filter' => join(';', $tmp),
				'type' => $params['type'],
				'part' => $params['part'],
				'group_by' => $params['group_by'],
				'subgroup_by' => $params['subgroup_by'],
				'conv' => $params['conv'],
				'mode' => $params['mode'],
				'col'  => $params['col'],
				'from' => $params['from'],
				'to' => $params['to'],
			);
			return '?' . http_build_query($vars);
		}
		
		/*
		 * Формируем параметры отчёта из REQUEST-переменных
		 */
		function report_options() {
			global $group_types;
			// Дешифруем фильтры
			$tmp_filters = rq('filter');
			$filter = array(0 => array(), 1 => array());
			$filter_str = array();
			
			if(!empty($tmp_filters)) {
				$tmp_filters = explode(';', $tmp_filters);
				foreach($tmp_filters as $tmp_filter) {
					list($k, $v, $type) = explode(':', $tmp_filter);
					$type = intval($type);
					if(array_key_exists($k, $group_types)) {
						$filter[$type][$k] = $v;
						$filter_str[$k] = $v . ':' . $type;
					}
				}
			}
			
			$part = rq('part', 0, 'day');
			
			// Устанавливаем даты по умолчанию
			switch($part) {
				case 'month':
	        		$from  = date ('Y-m-01', strtotime(get_current_day('-6 months')));
	    			$to    = date ('Y-m-t',  strtotime(get_current_day()));
				break;
				default:
					$from = get_current_day('-6 days');
	        		$to   = get_current_day();
				break;
			}
			
			$group_by = rq('group_by', 0, 'out_id');
			$subgroup_by = rq('subgroup_by', 0, $group_by);
			$conv = rq('conv', 0, 'all');
			$mode = rq('mode', 0, '');
			$col  = rq('col', 0, 'sale_lead');
			
			// Если эта группировка уже затронута фильтром - выбираем следующую по приоритету
			// Примечание: в отчёте по целевым можно не выбирать
			if($mode != 'lp') {
				$i = 0;
				$group_types_keys = array_keys($group_types);
				while(!empty($filter) and array_key_exists($group_by, $filter)) {
					$group_by = $group_types_keys[$i];
					$i++;
				}
			}
			/*
			for($i = 0; empty($filter) or array_key_exists($group_by, $filter); $i++) {
				$group_by = $group_types_keys[$i];
			}*/
			
			// Готовим параметры для отдачи
			$v = array(
				'type' => rq('type', 0, 'basic'),
				'part' => rq('part', 0, 'all'),
				'filter' => $filter,
				'filter_str' => $filter_str,
				'group_by' => $group_by,
				'subgroup_by' => $subgroup_by,
				'conv' => $conv,
				'mode' => $mode,
				'col'  => $col,
				'from' => rq('from', 4, $from),
				'to'   => rq('to', 4, $to)
			);
			return $v;
		}
		
		// Набор функций для вычисления и форматирование показателей в отчётах
		function t_price($r, $wrap = true) {
			$r['price'] = round($r['price'], 2);
			return currencies_span($r['price'], $wrap);
		}
		
		function t_lpctr($r, $wrap = true) {
			if(!empty($r['sub'])) {
				$out = round($r['out'] / $r['cnt'] * 100, 1);
				return $wrap ? $out . '%' : $out;
			} else {
				return '';
			}
		}
		
		function t_income($r, $wrap = true) {
			return currencies_span($r['income'], $wrap);
		}

		function t_epc($r, $wrap = true) {
			return currencies_span(round2($r['income'] / $r['cnt']), $wrap);
		}

		function t_profit($r, $wrap = true) {
			return currencies_span(round2($r['income'] - $r['price']), $wrap);
		}

		function t_roi($r, $wrap = true) {
			$out = round(($r['income'] - $r['price']) / $r['price'] * 100, 1);
			return $wrap ? $out . '%' : $out;
		}

		function t_conversion($r, $wrap = true) {
			if($r['sale'] == 0) return $wrap ? '' : 0;
			$out = round2($r['sale'] / $r['cnt'] * 100);
			return $wrap ? $out . '%' : $out;
		}

		function t_conversion_l($r, $wrap = true) {
			if($r['lead'] == 0) return $wrap ? '' : 0;
			$out = round2($r['lead'] / $r['cnt'] * 100);
			return $wrap ? $out . '%' : $out;
		}
		
		function t_conversion_a($r, $wrap = true) {
			if($r['sale_lead'] == 0) return $wrap ? '' : 0;
			$out = round2($r['sale_lead'] / $r['cnt'] * 100);
			return $wrap ? $out . '%' : $out;
		}
							
		function t_follow($r, $wrap = true) {
			$out = round($r['out'] / $r['cnt'] * 100, 1);
			return $wrap ? $out . '%' : $out;
		}

		function t_cps($r, $wrap = true) {
			return currencies_span(round2($r['price'] / $r['sale']), $wrap);
		}
		
		function t_cpa($r, $wrap = true) {
			return currencies_span(round2($r['price'] / $r['sale_lead']), $wrap);
		}
		
		function t_cpl($r, $wrap = true) {
			return currencies_span(round2($r['price'] / $r['lead']), $wrap);
		}

		function t_repeated($r, $wrap = true) {
			
			$repeated = $r['cnt'] - $r['unique'];
			//if($repeated < 0 or $repeated == 0) return $wrap ? '' : 0;
			if($repeated < 0) $repeated = 0;
			$repeated = round($repeated / $r['cnt']  * 100, 1);
			return $wrap ? $repeated . '%' : $repeated;
		}
		
		function t_cnt($r, $wrap = true) {
			return $r['cnt'];
		}
		
		function t_sale($r, $wrap = true) {
			if($r['sale'] == 0) return $wrap ? '' : 0;
			return $r['sale'];
		}
		
		function t_lead($r, $wrap = true) {
			if($r['lead'] == 0) return $wrap ? '' : 0;
			return $r['lead'];
		}
		
		function t_sale_lead($r, $wrap = true) {
			if($r['sale_lead'] == 0) return $wrap ? '' : 0;
			return $r['sale_lead'];
		}

		function currencies_span($v, $wrap = true) {
			if(!$wrap) return $v;
			$rub_rate = 30;
			$style = '';
			if(empty($v)) {
				$style = 'style="color:lightgray;font-weight:normal;"';
			} elseif($v < 0) {
				$style = 'style="color:red;"';
			} 
			return '<b><span class="sdata usd" '.$style.'>'.($v < 0 ? '-' : '').'$'.abs($v).'</span><span class="sdata rub" '.$style.'>'.round($v*$rub_rate).'р.</span></b>';
		}
		
		function click_param($n, $val, $source_name) {
			global $source_config;
			if(!empty($source_config[$source_name]['params'])) {
				$named_params = $source_config[$source_name]['params'];
				$named_params_cnt = count($named_params);
				$named_params_keys = array_keys($named_params);
			} else {
				$named_params_cnt = 0;
			}
			
			if($n <= $named_params_cnt) {
				$param_name = $named_params[$named_params_keys[$n - 1]]['name'];
				if(!empty($named_params[$named_params_keys[$n - 1]]['list']) and
					!empty($named_params[$named_params_keys[$n - 1]]['list'][$val])) {
						$val = $named_params[$named_params_keys[$n - 1]]['list'][$val];
					} 
			} else {
				$param_name = '#'.($n - $named_params_cnt);
			}
			return array($param_name, $val);
		}
		
		
		// Значение поля для рассчётов, например площадка
		// http://site.ru/topic1/page1.html станет site.ru
		
		function param_key($row, $type) {
			
			if(trim($row[$type]) != '') {
				// Обрезаем реферер до домена
				if($type == 'referer') {
					$url = parse_url($row[$type]);
					$out = $url['host'];
				
				// Для объявления добавляем кампанию
				} elseif($type == 'ads_name') {
					if($row[$type] != '') {
						$out = ($row['campaign_name'] . '-' . $row[$type]);
					} else {
						$out = '{empty}';
					}
				} else {
					$out = $row[$type];
				}
			} else {
				$out = '{empty}';
			}
			return $out;
		}
		
		// Вливаем информацию о переходе в массив статистики 
		
		function stat_inc(&$arr, $r, $id, $name) {
			if(!isset($arr)) {
				$arr = array(
					'id'     => $id,
					'name'   => $name,
					'price'  => 0,
					'unique' => 0,
					'income' => 0,
					'sale'   => 0,
					'lead'   => 0,
					'out'    => 0,
					'cnt'    => 0,
					'sale_lead' => 0,
				);
			}
			$arr['id']        =  $id;
			$arr['name']      =  $name;
			$arr['sale']      += $r['is_sale'];
			$arr['lead']      += $r['is_lead'];
			$arr['cnt']       += $r['cnt'];
			$arr['price']     += $r['click_price'];
			$arr['unique']    += $r['is_unique'];
			$arr['income']    += $r['conversion_price_main'];
			$arr['out']       += $r['is_connected'];
			$arr['sale_lead'] += $r['sale_lead'];
		}
		
		// Значение поля для отображения пользователю, например
		// out_id "10" становится названием ссылки "Ссылка 1",
		// а источник popunder станет Popunder.ru
		// нам нужно обрабатывать рефереров, имена объявлений, специальные параметры
		
		function param_val($row, $type, $source_name = '') {
			global $group_types, $source_config;
			static $links = array();
			
			
			$name = '';
			if(is_array($row)) {
				$v = $row[$type];
				$source_name = $row['source_name'];
			} else {
				$v = $row;
			}
			
			if($type == 'referer') {

				$name = parse_url($v);
				$name = $name['host'];
				
			} elseif($type == 'source_name') {
				$name = empty($source_config[$v]['name']) ? $v : $source_config[$v]['name'];
				
			} elseif($type == 'ads_name') {
				if($v != '') {
					$name = is_array($row) ? ($row['campaign_name'] . '-' . $row['ads_name']) : $row;
				}
				//$name = $v;
				//dmp($v);
				//dmp($row);
			} elseif($type == 'out_id') {
				if(isset($links[$v])) {
					$name = $links[$v];
				} else {
					$name = current(get_out_description($v));
					$links[$v] = $name;
				}
			} else {
				// Специальные поля, определённые для источника в виде списка
				if(!empty($source_config[$source_name]['params']) 
					and strstr($type, 'click_param_value') !== false) {
					$n = intval(str_replace('click_param_value', '', $type));
					$i = 1;
					foreach($source_config[$source_name]['params'] as $param) {
						if($i == $n and !empty($param['list'][$v])) {
							$name = str_replace(' ', '&nbsp;', $param['list'][$v]);
							return $name;
						}
						$i++;
					}
					$name = $v;
				} else {
					$name = $v;
				}
			}
			
			if(trim($name) == '' or $name == '{empty}') $name = $group_types[$type][1];
			return $name;
		}
		
		/*
		 * Название параметра (если пользовательский (click_param_value1-15) - зависит от источника)
		 */
		function param_name($type, $source = '', $only_name = false) {
			global $source_config, $group_types;
			
			$n = intval(str_replace('click_param_value', '', $type));
			
			// Если есть фильтр по источнику - считаем именованные параметры
			if(!empty($source) and !empty($source_config[$source]['params'])) {
				$named_params_cnt = count($source_config[$source]['params']);
			} else {
				$named_params_cnt = 0;
			}
			
			if(strstr($type, 'click_param_value') !== false and $named_params_cnt > 0) {
				$i = 1;
				foreach($source_config[$source]['params'] as $v) {
					if($i == $n) {
						$name = str_replace(' ', '&nbsp;', $v['name']);
						if($only_name) {
							return $name;
						}
						return $name;
					}
					$i++;
				}
			}
			
			if($only_name) {
				if(strstr($type, 'click_param_value') !== false) {
					return 'Параметр #' . ($n - $named_params_cnt);
				} else {
					return $group_types[$type][2];
				}
			}
			
			$name = $group_types[$type][0];
			$name = str_replace('Параметр перехода', 'ПП', $name);
			$name = str_replace('Параметр ссылки', 'ПС', $name);
			$name = str_replace('#' . $n, '#' . ($n - $named_params_cnt), $name);
			return $name;
		}
		
		/**
		 * Название ведущей колонки в отчёте (для специальных настроек источников)
		 */
		function col_name($params, $only_name = false) {
			return param_name($params['group_by'], $params['filter'][0]['source_name'], $only_name);
		}
		
		/*
		 * фрагмент данных для сортировки подчинённых офферов (режим lp_offers)
		 * Вид: order|val|ln|offer|val_offer
		 * order - 1 для офферов с прямыми переходами и 0 для всех остальных, офферы всегда внизу
		 * val   - значение ячейки лэндинга (у оффера - значение родительской ячейки)
		 * ln    - номер группы, одинаковый у лэндинга и всех его подчиненённых офферов
		 * offer - флаг оффера, 0 для лэндинга, 1 для оффера, сортируется всегда так чтобы лэндинг был вверху
		 * val_offer - значение ячейки оффера (для лэндинга пустое)
		 */
		function sortdata($col_name, $data) {
			//dmp($data);
			//print_r($data);
			//static $l; // счётчик лэндингов
			$r = $data['r'];
			$parent = $data['parent'];
			$func = 't_' . $col_name;
			$tmp = array(
				intval($data['r']['order'])
				//empty($data['r']['sub']) ? 0 : 1 // есть ли подчинённые
			);
			
			$val0 = intval($func($r, false));
			$val = $func($r);
			
			if($col_name == 'cnt' and $r['sale_lead'] > 0 and $data['part'] != 'all') {
				$val = $val . ':' . $r['sale_lead'];
				$val0 += ($r['sale_lead'] * 10000000);
			}
			
			if(!empty($parent)) {
				//dmp($parent);
				if($col_name == 'cnt' and $data['part'] != 'all') {
					// В дневном режиме особый режим переноса родительских переходов
					$tmp[] = intval($func($parent, false) + ($parent['sale_lead'] * 10000000) ); // значение лэндинга
				} else {
					$tmp[] = intval($func($parent, false)); // значение лэндинга
				}
				$tmp[] = $data['r']['ln']; // номер лэндинга
				$tmp[] = 1; // это оффер
				$tmp[] = $val0;
			} else {
				//$l[$col_name]++;
				$tmp[] = $val0;
				$tmp[] = $data['r']['ln']; // номер лэндинга
				$tmp[] = 0; // это лэндинг
			}
			
			return '<span class="sortdata">'.join('|', $tmp).'|</span>' . $val;
		}
		
		/*
		 * Мы загружаем данные частями и иногда получается так, что родительский клик мы загрузили, а подчиненный - нет, или наоборот. Лезем прямо в базу и проверяем наличие клика
		 */
		function parent_row($id, $name = '') {
			global $rows;
			if(empty($id)) return 0;
			
			if(!isset($rows[$id])) {
				$q="select * from `tbl_clicks` where `id` = '".intval($id)."' limit 1";
				//echo $q. '<br >';
				if($rs = db_query($q) and mysql_num_rows($rs) > 0) {
					$row = mysql_fetch_assoc($rs);
				} else {
					return 0;
				}
			} else {
				$row = $rows[$id];
			}
			return empty($name) ?  $row : $row[$name];
		}
		
		/*
		 * Получаем самую первую ссылку из правила
		 */
		function get_first_rule_link($rule_id) {
			$q="select `offer_tracking_url` 
				from `tbl_rules_items`
				left join `tbl_offers` on value = tbl_offers.id
				where `rule_id` = '".intval($rule_id)."'
					and `type` = 'redirect'
				order by `tbl_rules_items`.`id`
				limit 1";
			$rs = db_query($q);
			$r = mysql_fetch_assoc($rs);
			return $r['offer_tracking_url'];
		}
?>