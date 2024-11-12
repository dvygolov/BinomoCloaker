<?php
require_once __DIR__.'/obfuscator.php';
require_once __DIR__.'/../settings.php';
require_once __DIR__.'/../requestfunc.php';
require_once __DIR__.'/../debug.php';
require_once __DIR__.'/../db/db.php';
require_once __DIR__.'/../campaign.php';

header('Content-Type: text/javascript');
global $db;
$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp===null)
    die("NO CAMPAIGN FOR THIS DOMAIN!");
//TODO: add traffickback campaign
$c = new Campaign($dbCamp['id'],$dbCamp['settings']);


$jsCode = file_get_contents(__DIR__.'/detect.js');
if (DebugMethods::on())
    $jsCode = str_replace('{DEBUG}', 'true', $jsCode);
else
    $jsCode = str_replace('{DEBUG}', 'false', $jsCode);

$jsChecks = $c->white->jsChecks;
$jsCode = str_replace('{DOMAIN}', get_cloaker_path(), $jsCode);
$js_checks_str=	implode('", "', $jsChecks->events);
$jsCode = str_replace('{JSCHECKS}', $js_checks_str, $jsCode);

$jsCode = str_replace('{JSTIMEOUT}', $jsChecks->timeout, $jsCode);
$jsCode = str_replace('{JSTZMIN}', $jsChecks->tzMin, $jsCode);
$jsCode = str_replace('{JSTZMAX}', $jsChecks->tzMax, $jsCode);

$detector= file_get_contents(__DIR__.'/detector.js');
$fullCode = $detector."\n".$jsCode;
if (DebugMethods::on()){
    echo $fullCode;
} else {
    $hunter = new HunterObfuscator($fullCode);
    echo $hunter->Obfuscate();
}
