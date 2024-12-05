<?php
require_once __DIR__ . '/../debug.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/../db/db.php';

global $db;

$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp === null)
    die("NO CAMPAIGN FOR THIS DOMAIN!");
$reason = isset($_GET['reason']) ? $_GET['reason'] : 'js_checks';
$db->add_white_click(Cloaker::get_click_params(), $reason, $dbCamp['id']);
if (DebugMethods::on()) {
    echo $added ? "OK" : "Error";
}
exit();
