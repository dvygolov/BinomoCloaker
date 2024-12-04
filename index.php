<?php
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/campaign.php';
require_once __DIR__ . '/core.php';
require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/main.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/redirect.php';

global $db;
$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp===null){
    $c = new Cloaker();
    $db->add_trafficback_click($c->click_params);
    $cs = $db->get_common_settings();
    if (empty($cs['trafficBackUrl']))
        die("NO CAMPAIGN FOR THIS DOMAIN AND TRAFFICBACK NOT SET!");
    else{
        redirect($cs['trafficBackUrl'],302,true);
        exit();
    }
}

$c = new Campaign($dbCamp['id'],$dbCamp['settings']);
$cloaker = new Cloaker($c->filters);

if ($c->white->jsChecks->enabled) {
    white(true);
} else if ($cloaker->is_bad_click()) { 
    $db->add_white_click($cloaker->click_params, $cloaker->block_reason, $c->campaignId);
    white(false);
} else
    black($cloaker->click_params);