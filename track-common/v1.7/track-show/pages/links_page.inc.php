<?php
if (!$include_flag) {
    exit;
}
$category_id = rq('category_id', 2);
$offset = rq('offset', 2);
$limit = rq('limit', 2, 1000);

// Свежеудаленная категория
$delete_cat = rq('delete_cat', 2);
if(!empty($delete_cat)) {
    $delete_category_info = category_info($delete_cat);
}

/* @var $cat_type string переменная определена при рендеринге меню */
$arr_offers = get_offers_list($cat_type, $category_id, $offset, $limit);

if($arr_offers['error']) {
    redirect(full_url() . '?page=links');
}

if ($arr_offers['cat_name'] == '{empty}') {
    $page_headers[0] = '';
    $page_headers[1] = $cat_types[$cat_type];
} else {
    $page_headers[0] = $cat_types[$cat_type];
    $page_headers[1] = $arr_offers['cat_name'];
}

echo tpx('links');
