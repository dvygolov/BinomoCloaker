<?php

require_once __DIR__ . '/../campaign.php';
require_once __DIR__ . '/../debug.php';
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/../macros.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../main.php';
require_once __DIR__ . '/../requestfunc.php';

global $db;
$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp===null){
    $cs = $db->get_common_settings();
    $cp = Cloaker::get_click_params();
    $db->add_trafficback_click($cp);
    if (empty($cs['trafficBackUrl']))
        die("NO CAMPAIGN FOR THIS DOMAIN AND TRAFFICBACK NOT SET!");
    else{
        $mp = new MacrosProcessor(null,$cp);
        $url = urldecode($cs['trafficBackUrl']);
        $url = $mp->replace_url_macros($url);
        header("Access-Control-Expose-Headers: YWBAction", false, 200);
        header("Access-Control-Expose-Headers: YWBLocation", false, 200);
        header("YWBAction: redirect", true, 200);
        header("YWBLocation: " . $url, true, 200);
        return http_response_code(200);
        exit();
    }
}

$c = new Campaign($dbCamp['id'],$dbCamp['settings']);
$cloaker = new Cloaker($c->filters);
$is_bad_click = $cloaker->is_bad_click();

send_access_control_headers();
header("Access-Control-Expose-Headers: YWBAction", false, 200);

if ($is_bad_click) {
    //somehow it passed our javascript tests!
    $db->add_white_click($cloaker->click_params, $cloaker->block_reason, $c->campaignId);
    header("YWBAction: none", true, 200);
    return http_response_code(200);
} else { //common user
    $ca = black($cloaker->click_params);
    if ($ca->action==='html'){
        header("YWBAction: " . $c->black->jsconnectAction, true, 200);
        echo $ca->value;
    }
    else{
        header("YWBAction: redirect", true, 200);
        header("Access-Control-Expose-Headers: YWBLocation", false, 200);
        header("YWBLocation: " . $ca->value, true, 200);
    }

    return http_response_code(200);
}
