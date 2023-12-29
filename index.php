<?php
global $cur_config, $os_white, $country_white, $lang_white, $tokens_black,
       $ip_black_filename, $ua_black, $url_should_contain, $ip_black_cidr,
       $block_without_referer, $referer_stopwords, $block_vpnandtor, $isp_black,
       $tds_mode, $use_js_checks;
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/core.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/main.php';

$fs = new FilterSettings(
    $os_white, $country_white, $lang_white, $tokens_black,
    $url_should_contain, $ua_black, $ip_black_filename,
    $ip_black_cidr, $block_without_referer, $referer_stopwords,
    $block_vpnandtor, $isp_black);
$cloaker = new Cloaker($fs);

//если включен full_cloak_on, то шлём всех на white page, полностью набрасываем плащ)
if ($tds_mode === 'full') {
    $db = new Db();
    $db->add_white_click($cloaker->click_params, ['fullcloak'], $cur_config);
    white(false);
} else if ($tds_mode === 'off') {
    black($cloaker->click_params);
} else if ($use_js_checks) {
//если используются js-проверки, то сначала используются они
//проверка же обычная идёт далее в файле js/jsprocessing.php
    white(true);
} else if ($cloaker->is_bad_click()) { //Обнаружили бота или модера
    $db = new Db();
    $db->add_white_click($cloaker->click_params, $cloaker->block_reason, $cur_config);
    white(false);
} else
    black($cloaker->click_params);