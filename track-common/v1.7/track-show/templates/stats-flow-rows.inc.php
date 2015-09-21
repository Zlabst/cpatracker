<?php
global $source_config;

foreach ($var['data'] as $row)
{

    // Флаг страны
    if ($row['country'] == '')
    {
        $country_title = '';
        $country_icon = 'question.png';
    }
    else
    {
        $country_title = "{$row['country']}";
        $country_icon = strtolower($row['country']) . '.png';
    }
    
    // OS icon
    switch(strtolower($row['user_os']))
    {
        case 'android': $icon_os = 'android'; break;
        case 'linux': $icon_os = 'linux'; break;
        case 'ios': $icon_os = 'apple-ios'; break;
        default: $icon_os = ''; break;
    }

    if ($row['is_phone']==1){
        $icon_tablet='phone';
    }
    elseif ($row['is_tablet']==1){
        $icon_tablet='tablet';
    }
    else
    {
        $icon_tablet='';
    }

    // Источник
    $source_name = empty($source_config[$row['source_name']]['name']) ? $row['source_name'] : $source_config[$row['source_name']]['name'];
    if ($row['source_name'] == 'source')
        $source_name = '&mdash;';

    $rule_decs = get_rule_description($row['rule_id']);

    // Ссылка
    $date_url = (isset($_REQUEST['date']) and preg_match('/^\d{4}-\d{2}-\d{2}$/', $_REQUEST['date'])) ? '&date=' . $_REQUEST['date'] : '';
    if ($row['out_id'] > 0) {
        $out_text = "<a href='?filter_by=out_id&value={$row['out_id']}{$date_url}'>" . _e(current(get_out_description($row['out_id']))) . "</a>";
    } else {
        $out_text = "Не определён";
    }

    // Адрес
    $arr_locations = array();
    if ($row['country'] != '') {
        $arr_locations[] = $row['country'];
    }
    if ($row['state'] != '') {
        $arr_locations[] = $row['state'];
    }
    if ($row['city'] != '') {
        $arr_locations[] = $row['city'];
    }
    $str_location = implode(', ', $arr_locations);

    // Параметры ссылки
    $click_params = params_list($row, 'click_param_value', $row['source_name']);
    if (!empty($click_params)) {
        $str_click_params = join('; ', $click_params);
    }

    // Параметры перехода
    $campaign_params = params_list($row, 'campaign_param');
    if (!empty($campaign_params)) {
        $str_campaign_params = join('; ', $campaign_params);
    }

    // Реферер
    if ($row['source_name'] == 'yadirect' and !empty($row['click_param_value8']))
    {
        $cur_referrer = $row['click_param_value8'];
        if (mb_strlen($cur_referrer, 'UTF-8') > 40) {
            $wrapped_referrer = mb_substr($cur_referrer, 0, 38, 'UTF-8') . '…';
        } else {
            $wrapped_referrer = $cur_referrer;
        }
        $wrapped_referrer = '<span style="color: darkmagenta">' . _e($wrapped_referrer) . '</span>';
    }
    else
    {
        if ($row['search_string']!='')
        {
            $cur_referrer=$row['search_string'];
            if (mb_strlen($cur_referrer, 'UTF-8') > 35) {
                $wrapped_referrer = mb_substr($cur_referrer, 0, 29, 'UTF-8') . '…';
            } else {
                $wrapped_referrer = $cur_referrer;
            }
            $wrapped_referrer = '<span style="color: darkmagenta">' . _e($wrapped_referrer) . '</span>';
        }
        else
        {
            $cur_referrer = str_replace(array('http://www.', 'www.'), '', $row['referer']);
            if (strpos($cur_referrer, 'http://') === 0) {
                $cur_referrer = substr($cur_referrer, strlen('http://'));
            }
            if (mb_strlen($cur_referrer, 'UTF-8') > 35) {
                $wrapped_referrer = mb_substr($cur_referrer, 0, 29, 'UTF-8') . '…';
            } else {
                $wrapped_referrer = $cur_referrer;
            }
            $wrapped_referrer = _e($wrapped_referrer);
        }
    }
    ?>

    <!--row-->
    <tr>
        <td class="accordion-toggle" data-toggle="collapse-next">
            <img src="<?php echo _HTML_TEMPLATE_PATH . "/img/countries/" . _e($country_icon) ?>" class="flag flag-us" alt="us" />
        </td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php if(!empty($icon_os)) { ?><i class="cpa cpa-<?php echo $icon_os;?>"></i><? } ?></td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php if(!empty($icon_tablet)) { ?><i class="cpa cpa-<?php echo $icon_tablet;?>"></i><? } ?></td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php echo _e($row['td']); ?></td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php echo $out_text; ?></td>
        <td class="accordion-toggle inactive" data-toggle="collapse-next"><?php echo $source_name; ?></td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php echo _e($row['campaign_name'] . ' ' . $row['ads_name']); ?></td>
        <td class="accordion-toggle" data-toggle="collapse-next"><?php echo $wrapped_referrer;?></td>
        <td class="text-right accordion-toggle" data-toggle="collapse-next">
            <a class="a-default"><i class="cpa cpa-angle-down"></i></a>
        </td>
    </tr>
    <!--hidden row-->
    <tr >
        <td colspan="9" class="no-padding">
            <div class="collapse">	
                <div class="inner">

                    <table class="table table-link-description">					                
                        <thead>
                            <tr>
                                <th>Местоположение</th>
                                <th>ОС</th>
                                <th>SubID</th>
                            </tr>
                        </thead>

                        <tbody>				
                            <!--row-->
                            <tr>
                                <td><?php echo $str_location; ?></td>
                                <td><?php echo $row['user_os']; ?> </td>
                                <td><?php echo $row['subid'] ?></td>
                            </tr>
                        </tbody>					                
                    </table>


                    <table class="table table-link-description">
                        <thead>
                        <tr>
                            <th>Реферер</th>
                            <th>Браузер</th>
                        </tr>
                        </thead>

                        <tbody>
                        <!--row-->
                        <tr>
                            <td><?php echo $wrapped_referrer; ?></td>
                            <td><?php echo $row['user_agent'] ?></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php if (strlen($str_click_params.$str_campaign_params)>0)
                    {
                    ?>
                        <table class="table table-link-description">
                            <thead>
                            <tr>
                                <th>Параметры ссылки</th>
                                <th>Параметры перехода</th>
                            </tr>
                            </thead>

                            <tbody>
                            <!--row-->
                            <tr>
                                <td><?php echo _e($str_click_params); ?></td>
                                <td><?php echo _e($str_campaign_params); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    <?php
                    }
                    ?>
                </div><!--inner-->
            </div><!--collapse-->
        </td>
    </tr>

    <?
}
?>