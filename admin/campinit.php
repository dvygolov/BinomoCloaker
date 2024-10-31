<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../campaign.php';

$campId = $_REQUEST['campId']??-1;
if ($campId===-1) die("Campaign Id not found in URL!");
$s = $db->get_campaign_settings($campId);
$c = new Campaign($campId, $s);
?>