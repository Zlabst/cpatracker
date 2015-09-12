<?php

$source_config = array(
    'source' => array(
        'name' => 'Целевая страница',
        'params' => array(
            'source' => array('name' => 'Источник', 'url' => '{utm_source}'),
            'keyword' => array('name' => 'Ключевая фраза', 'url' => '{utm_term}'),
            'campaign_id' => array('name' => 'ID объявления', 'url' => '{utm_campaign}'),
        )
    ),
    'yottos' => array(
        'name' => 'Yottos',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{source}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{content}'),
        )
    ),
    'privatteaser' => array(
        'name' => 'Privatteaser',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{REF}'),
        )
    ),
    'novostimira' => array(
        'name' => 'Novostimira',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{nm_inf_g}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{nm_g}'),
        )
    ),
    'globalteaser' => array(
        'name' => 'GlobalTeaser',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{sid}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{tid}'),
        )
    ),
    'adprofy' => array(
        'name' => 'Adprofy',
        'params' => array(
            'site_id' => array('name' => 'ID площадки', 'url' => '[ab]'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '[at]'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '[ac]'),
        )
    ),
    'redclick' => array(
        'name' => 'Redclick',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => 'source_id'),
            'ad_id' => array('name' => 'ID объявления', 'url' => 'tizer_id'),
        )
    ),
    'redtram' => array(
        'name' => 'Redtram',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{SITE_NAME}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{GOOD_ID}'),
        )
    ),
    'actionteaser' => array(
        'name' => 'Actionteaser',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[SID]'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '[ID]'),
        )
    ),
    'adhub' => array(
        'name' => 'Adhub',
        'params' => array(
            'site_id' => array('name' => 'ID площадки', 'url' => '{site_id}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{ad_id}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{camp_id}'),
        )
    ),
    'bannerbook' => array(
        'name' => 'Bannerbook',
        'params' => array(
            'ad_id' => array('name' => 'ID объявления', 'url' => '{TEASER_ID}'),
            'site_id' => array('name' => 'ID площадки', 'url' => '{SITE_ID}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{CAMP_ID} '),
            'place_id' => array('name' => 'ID размещения', 'url' => '{PLACE_ID}'),
        )
    ),
    'advertlink' => array(
        'name' => 'Advertlink',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[SID]'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '[CID]'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '[TID]'),
        )
    ),
    'topmmorpg' => array(
        'name' => 'Topmmorpg',
    ),
    'zeropark' => array(
        'name' => 'Zeropark',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{target}'),
            'keyword' => array('name' => 'Ключевая фраза', 'url' => '{keyword}'),
            'match' => array('name' => 'Исходная фраза', 'url' => '{match}'),
        )
    ),
    'wapstart' => array(
        'name' => 'Wapstart',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '%SITE_ID%'),
        )
    ),
    'tapgage' => array(
        'name' => 'Tapgage',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => 'TAPGAGE_VAR_SITEID'),
        )
    ),
    'sitescout' => array(
        'name' => 'SiteScout',
        'params' => array(
            'ad_id' => array('name' => 'ID объявления', 'url' => '{adId}'),
            'place_id' => array('name' => 'ID площадки', 'url' => '{siteId}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{campaignId}'),
            'net_id' => array('name' => 'ID сети', 'url' => '{networkId}'),
            'domain' => array('name' => 'Домен SiteScout', 'url' => '{domain}'),
            'place' => array('name' => 'Площадка SiteScout', 'url' => '{pageUrl}'),
            'aud_id' => array('name' => 'ID аудитории', 'url' => '{demographicIds}'),
            'type_id' => array('name' => 'ID категории', 'url' => '{contextualIds}'),
            'operator' => array('name' => 'Оператор', 'url' => '{carrier}'),
            'device' => array('name' => 'Устройство', 'url' => '{device}'),
            'app_id' => array('name' => 'ID приложения', 'url' => '{appId}'),
        )
    ),
    'plugrush' => array(
        'name' => 'Plugrush',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{$id}'),
            'type_id' => array('name' => 'ID категории', 'url' => '{$category}'),
            'domain' => array('name' => 'Площадка Plugrush', 'url' => '{$domain}'),
            'place_type' => array('name' => 'Тип площадки', 'url' => '{$trafficsource}'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '{$ad_id}'),
        )
    ),
    'mobfox' => array(
        'name' => 'Mobfox',
        'params' => array(
            'ad_id' => array('name' => 'ID объявления', 'url' => 'MFOXADID'),
            'place_id' => array('name' => 'ID площадки', 'url' => 'MFOXPUBID'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => 'MFOXCAID'),
        )
    ),
    'mmedia' => array(
        'name' => 'mMedia',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[:_jv_apid:]'),
        )
    ),
    'jumptap' => array(
        'name' => 'Jumptap',
        'params' => array(
            'ad_id' => array('name' => 'Объявление', 'url' => 'JT_ADBUNDLE'),
            'device' => array('name' => 'Устройство', 'url' => 'JT_HANDSET'),
            'publisher' => array('name' => 'Вебмастер', 'url' => 'JT_PUBLISHER'),
            'place_id' => array('name' => 'Площадка', 'url' => 'JT_SITE'),
            'operator' => array('name' => 'Оператор', 'url' => 'JT_OPERATOR'),
            'keyword' => array('name' => 'Ключевая фраза', 'url' => 'JT_KEYWORD'),
        )
    ),
    'adtwirl' => array(
        'name' => 'Adtwirl',
    ),
    'leadbolt' => array(
        'name' => 'Leadbolt',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[PUB_ID]'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '[AD_GROUP_ID]'),
            'ad_id' => array('name' => 'ID объявления', 'url' => '[AD_ID]'),
        )
    ),
    'octobird' => array(
        'name' => 'Octobird',
        'params' => array(
            'site_id' => array('name' => 'ID площадки', 'url' => '{OB_SITE_ID} '),
        )
    ),
    'exoclick' => array(
        'name' => 'ExoClick',
        'params' => array(
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{campaign_id}'),
            'variation_id' => array('name' => 'ID объявления', 'url' => '{variation_id}'),
            'src_hostname' => array('name' => 'Площадка Exoclick', 'url' => '{src_hostname}'),
            'site_id' => array('name' => 'ID площадки', 'url' => '{site_id}'),
            'zone_id' => array('name' => 'ID размещения', 'url' => '{zone_id}'),
            'category_id' => array('name' => 'ID категории', 'url' => '{category_id}'),
        )
    ),
    'decisive' => array(
        'name' => 'Decisive',
        'params' => array(
            'ad_id' => array('name' => 'ID объявления', 'url' => '{{{ad_id}}}'),
            'creative_id' => array('name' => 'ID баннера', 'url' => '{{{creative_id}}}'),
            'ad_name' => array('name' => 'Кампания', 'url' => '{{{ad_name}}}'),
            'carrier' => array('name' => 'Оператор', 'url' => '{{{carrier}}}'),
            'os' => array('name' => 'ОС', 'url' => '{{{os}}}'),
            'os_version' => array('name' => 'Версия ОС', 'url' => '{{{os_version}}}'),
            'device' => array('name' => 'Устройство', 'url' => '{{{device}}}'),
            'media' => array('name' => 'Тип размещения', 'url' => '{{{media}}}'),
            'country' => array('name' => 'Страна', 'url' => '{{{country}}}'),
            'app' => array('name' => 'Приложение', 'url' => '{{{app}}}'),
            'site' => array('name' => 'Площадка Decisive', 'url' => '{{{site}}}'),
            'category' => array('name' => 'Категория площадки', 'url' => '{{{category}}}'),
            'subcategory' => array('name' => 'Подкатегория площадки', 'url' => '{{{subcategory}}}'),
        )
    ),
    'adinch' => array(
        'name' => 'Adinch',
        'params' => array(
            'appid' => array('name' => 'ID площадки', 'url' => '{APP_ID}'),
        )
    ),
    'buzzcity' => array(
        'name' => 'Buzzcity',
        'params' => array(
            'pubid' => array('name' => 'ID площадки', 'url' => '{pubid}'),
        )
    ),
    'inmobi' => array(
        'name' => 'Inmobi',
        'params' => array(
            'place' => array('name' => 'ID площадки', 'url' => '__si__cb'),
        )
    ),
    'admoda' => array(
        'name' => 'Admoda',
        'params' => array(
            'zoneid' => array('name' => 'ID площадки', 'url' => '%zoneid%'),
            'campaignid' => array('name' => 'ID кампании', 'url' => '%campaignid%'),
        )
    ),
    'adultmoda' => array(
        'name' => 'Adultmoda',
        'params' => array(
            'zoneid' => array('name' => 'ID площадки', 'url' => '%zoneid%'),
            'pubid' => array('name' => 'ID вебмастера', 'url' => '%pubid%'),
            'adid' => array('name' => 'ID объявления', 'url' => '%adid%'),
            'campaignid' => array('name' => 'ID кампании', 'url' => '%campaignid%'),
        )
    ),
    'leadimpact' => array(
        'name' => 'Leadimpact',
        'params' => array(
            'keyword' => array('name' => 'Ключевая фраза', 'url' => '%KEYWORD%'),
        )
    ),
    'dntx' => array(
        'name' => 'DNTX',
        'params' => array(
            'sourceid' => array('name' => 'ID площадки', 'url' => '[sourceid]'),
            'match' => array('name' => 'Ключевая фраза', 'url' => '[match]'),
        )
    ),
    'startapp' => array(
        'name' => 'StartApp',
        'params' => array(
            'aid' => array('name' => 'ID приложения', 'url' => 'app_id'),
            'creativeid' => array('name' => 'ID объявления', 'url' => 'creative_name'),
            'cid' => array('name' => 'ID кампании', 'url' => 'campaign_id'),
        )
    ),
    'go2mobi' => array(
        'name' => 'Go2mobi',
        'params' => array(
            'campaign' => array('name' => 'ID кампании', 'url' => '{campaign}'),
            'pln' => array('name' => 'Площадка Go2mobi', 'url' => '{pln}'),
            'plid' => array('name' => 'ID площадки', 'url' => '{plid}'),
            'crid' => array('name' => 'ID объявления', 'url' => '{crid}'),
            'isp' => array('name' => 'Оператор', 'url' => '{isp}'),
            'device_vendor' => array('name' => 'Производитель', 'url' => '{device_vendor}'),
            'device_model' => array('name' => 'Устройство', 'url' => '{device_model}'),
            'os' => array('name' => 'ОС', 'url' => '{os}'),
            'os_verion' => array('name' => 'Версия ОС', 'url' => '{os_verion}'),
        )
    ),
    'tapit' => array(
        'name' => 'Tapit',
        'params' => array(
            'site' => array('name' => 'ID площадки', 'url' => '[site]'),
            'channel' => array('name' => 'ID категории', 'url' => '[channel]'),
            'carrier' => array('name' => 'Оператор', 'url' => '[carrier]'),
            'platform' => array('name' => 'Платформа', 'url' => '[platform]'),
            'version' => array('name' => 'Версия ОС', 'url' => '[version]'),
            'device' => array('name' => 'Устройство', 'url' => '[phone_brand]'),
            'model' => array('name' => 'Модель', 'url' => '[phone_model]'),
            'creativeid' => array('name' => 'ID объявления', 'url' => '[creative]'),
            'environment' => array('name' => 'Тип площадки', 'url' => '[environment]'),
        )
    ),
    'airpush' => array(
        'name' => 'Airpush',
        'params' => array(
            'carrier' => array('name' => 'Оператор', 'url' => '%carrier%'),
            'device' => array('name' => 'Устройство', 'url' => '%device%'),
            'manufacturer' => array('name' => 'Производитель', 'url' => '%manufacturer%'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '%campaignid%'),
            'creativeid' => array('name' => 'ID объявления', 'url' => '%creativeid%'),
            'app_id' => array('name' => 'ID приложения', 'url' => '%dapp%'),
            'pubid' => array('name' => 'ID площадки', 'url' => '%pubid%'),
            'framework' => array('name' => 'Версия ОС', 'url' => '%framework%'),
        )
    ),
    'mobiads' => array(
        'name' => 'Mobiads',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[place_id]'),
        )
    ),
    'adlabs' => array(
        'name' => 'Adlabs',
        'params' => array(
            'adv_id' => array('name' => 'ID тизера', 'url' => '%tizer_id%'),
            'place_id' => array('name' => 'ID площадки', 'url' => '%source_id%'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '%campaign_id%'),
        )
    ),
    'bodyclick' => array(
        'name' => 'Bodyclick',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[SID]'),
            'adv_id' => array('name' => 'ID объявления', 'url' => '[ID]'),
            'keywords' => array('name' => 'Ключевая фраза', 'url' => '[Q]'),
            'title' => array('name' => 'Заголовок', 'url' => '[TITLE]'),
            'img' => array('name' => 'Изображение', 'url' => '[IMG]'),
        )
    ),
    'cashprom' => array(
        'name' => 'Cashprom',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{%CASHPROM_PLACE_ID%}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{%CASHPROM_CAMPAIGN_ID%}'),
            'adv_id' => array('name' => 'ID объявления', 'url' => '{%CASHPROM_ADV_ID%}'),
        )
    ),
    'directadvert' => array(
        'name' => 'DirectAdvert',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '%SITE_ID%'),
            'adv_id' => array('name' => 'ID объявления', 'url' => '%AD_ID%'),
        )
    ),
    'kadam' => array(
        'name' => 'Kadam',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[SID]'),
            'adv_id' => array('name' => 'ID тизера', 'url' => '[ID]'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '[CID]'),
        )
    ),
    'marketgid' => array(
        'name' => 'Marketgid',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{widget_id}'),
            'adv_id' => array('name' => 'ID тизера', 'url' => '{teaser_id}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{campaign_id}'),
            'category_id' => array('name' => 'ID категории', 'url' => '{category_id} '),
        )
    ),
    'mediatarget' => array(
        'name' => 'Mediatarget',
        'params' => array(
            'utm_campaign' => array('name' => 'ID кампании', 'url' => '[SITE_ID]'),
            'utm_place' => array('name' => 'Площадка MediaTarget', 'url' => '[TEASER_ID]'),
            'utm_term' => array('name' => 'Ключевая фраза', 'url' => '[IMAGE]'),
        )
    ),
    'teasermedia' => array(
        'name' => 'Teasermedia',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '{{domain}}'),
            'adv_id' => array('name' => 'ID объявления', 'url' => '{{tid}}'),
        )
    ),
    'teasernet' => array(
        'name' => 'Teasernet',
        'params' => array(
            'place_id' => array('name' => 'ID площадки', 'url' => '[SITE_ID]'),
            'adv_id' => array('name' => 'ID тизера', 'url' => '[TEASER_ID]'),
            'image' => array('name' => 'Изображение', 'url' => '[IMAGE]'),
            'title' => array('name' => 'Заголовок', 'url' => '[TITLE]'),
        )
    ),
    'visitweb' => array(
        'name' => 'Visitweb',
        'params' => array(
            'adv_id' => array('name' => 'ID объявления', 'url' => '{AD}'),
            'place_id' => array('name' => 'Площадка Visitweb', 'url' => '{USITE}'),
            'referer' => array('name' => 'Реферер Vistweb', 'url' => '{REF}'),
        )
    ),
    'vk' => array(
        'name' => 'ВКонтакте',
        'params' => array(
            'adv_id' => array('name' => 'ID объявления', 'url' => '{ad_id}'),
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{campaign_id}'),
        )
    ),
    'facebook' => array(
        'name' => 'Facebook',
    ),
    'targetmail' => array(
        'name' => 'Target@Mail.ru',
        'params' => array(
            'campaign_id' => array('name' => 'ID кампании', 'url' => '{{campaign_id}}'),
            'adv_id' => array('name' => 'ID баннера', 'url' => '{{banner_id}}'),
            'gender' => array('name' => 'Пол', 'url' => '{{gender}}'),
            'age' => array('name' => 'Возраст', 'url' => '{{age}}'),
        )
    ),
    'adwords' => array(
        'name' => 'Google Adwords',
        'params' => array(
            'adv_id' => array('name' => 'ID объявления', 'url' => '{creative}'),
            'keyword' => array('name' => 'Ключевая фраза', 'url' => '{keyword}'),
            'place_id' => array('name' => 'Площадка Adwords', 'url' => '{placement}'),
            'adposition' => array('name' => 'Позиция', 'url' => '{adposition}'),
            'position_type' => array(
                'n' => 5,
                'name' => 'Размещение',
                'list' => array(
                    '0' => 'Не определено',
                    's' => 'Реклама справа',
                    't' => 'Спецразмещение',
                )
            ),
        )
    ),
    'yadirect' => array(
        'name' => 'Яндекс.Директ',
        'rapams_ignore' => array('etext', 'uuid', 'state', 'data', 'b64e', 'sign', 'keyno', 'l10n', 'cts', 'ref', 'mc', 'csg', 'clid', 'lr', 'redircnt', 'msid'),
        'params' => array(
            'source_type' => array(
                'name' => 'Тип площадки',
                'url' => '{source_type}',
                'list' => array(
                    'search' => 'Поиск',
                    'context' => 'РСЯ',
                ),
            ),
            'source' => array(
                'name' => 'Площадка РСЯ',
                'url' => '{source}',
                'list' => array(
                    'none' => 'Не определена'
                )
            ),
            'position_type' => array(
                'name' => 'Размещение',
                'url' => '{position_type}',
                'list' => array(
                    'premium' => 'Cпецразмещение',
                    'other' => 'Блок внизу',
                    'none' => 'Не определено'
                )
            ),
            'position' => array(
                'name' => 'Позиция',
                'url' => '{position}',
                'list' => array(
                    '0' => 'Не определено',
                )
            ),
            'keyword' => array('n' => 5, 'name' => 'Ключевая фраза', 'url' => '{keyword}'),
            'campaign_id' => array('n' => 6, 'name' => 'ID кампании', 'url' => '{campaign_id}'),
            'ad_id' => array('n' => 7, 'name' => 'ID объявления', 'url' => '{ad_id}'),
            'text' => array('n' => 8, 'name' => 'Полная ключевая фраза'),
        ),
    ),
    'popunder' => array(
        'name' => 'Popunder.ru',
        'params' => array(
            'account' => array('name' => 'ID вебмастера', 'url' => '{wm_account_id}'),
            'place_id' => array('name' => 'ID площадки', 'url' => '{wm_site_id}'),
            'domain' => array('name' => 'Площадка Popunder', 'url' => '{wm_domain}'),
            'adv_id' => array('name' => 'ID баннера', 'url' => '{banner_id}'),
            'keyword' => array('name' => 'Ключевая фраза', 'url' => '{kwlist}'),
            'topic_id' => array(
                'name' => 'ID категории',
                'url' => '{topic_id}',
                'list' => array(
                    '0' => 'Не определена',
                    '5' => 'Авто',
                    '6' => 'Дом',
                    '7' => 'Заработок',
                    '8' => 'Коммуникации',
                    '9' => 'Личное',
                    '10' => 'Недвижимость',
                    '11' => 'Общество',
                    '12' => 'Путешествия',
                    '13' => 'Развлечения',
                    '14' => 'Реклама',
                    '15' => 'Строительство',
                    '16' => 'Учёба',
                    '17' => 'Финансы',
                    '18' => 'Шопинг',
                    '19' => 'Эротика',
                    '20' => 'Разное'
                )
            )
        )
    )
);