<?php
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/htmlprocessing.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/redirect.php';
require_once __DIR__ . '/abtest.php';
require_once __DIR__ . '/cookies.php';
require_once __DIR__ . '/campaign.php';

global $db;
$campId = $_GET['campId']??'';
if (empty($campId))
    die('NO CAMPAIGN ID FOUND!');

$settings = $db->get_campaign_settings($campId);
$c = new Campaign($campId, $settings);
//adding the fact that user reached landing to the database
$subid = get_cookie('subid');
$db->add_lpctr($subid);

$l = $_GET['l'] ?? -1;
$ls = $c->black->land;

switch ($ls->action) {
    case 'folder':
        $landing = select_item_by_index($ls->folderNames, $l, true);
        echo load_landing($landing);
        break;
    case 'redirect':
        $redirectUrl = select_item_by_index($ls->redirectUrls, $l, false);
        $fullUrl = insert_subs_into_url($_GET, $redirectUrl);
        redirect($fullUrl, $ls->redirectType, true);
        break;
}