<?php

require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/macros.php';
require_once __DIR__ . '/requestfunc.php';
require_once __DIR__ . '/campaign.php';
global $db;

$curLink = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$subid = $_REQUEST['subid'] ?? '';
if ($subid === '') {
    http_response_code(500);
    echo "No subid found! Url: $curLink";
    return;
}
$status = $_REQUEST['status'] ?? '';
if ($status === '') {
    http_response_code(500);
    echo "No status found! Url: $curLink";
    return;
}
$payout = $_REQUEST['payout'] ?? '';
if ($payout === '') {
    http_response_code(500);
    echo "No payout found! Url: $curLink";
    return;
}
$click = $db->get_clicks_by_subid($subid,true);
if (empty($click)){
    http_response_code(500);
    echo "No click for subid $subid found! Url: $curLink";
    return;
}
$cs = $db->get_campaign_settings($click[0]['campaign_id']);
$c = new Campaign($click[0]['campaign_id'],$cs);

$inner_status = '';
switch (strtolower($status)) {
    case strtolower($c->postback->leadStatusName):
        $inner_status = 'Lead';
        break;
    case strtolower($c->postback->purchaseStatusName):
        $inner_status = 'Purchase';
        break;
    case strtolower($c->postback->rejectStatusName):
        $inner_status = 'Reject';
        break;
    case strtolower($c->postback->trashStatusName):
        $inner_status = 'Trash';
        break;
}

if ($inner_status === '') {
    http_response_code(500);
    echo "Status $status is unknown! Url: $curLink";
    return;
}

if ($subid === '' || $status === '')
    $msg = "Error! No subid or status! {$curLink}";
else
    $msg = "$subid, $status, $payout";
add_log("postback", $msg);

$updated = $db->update_status($subid, $inner_status, $payout);

if ($updated) {
    process_s2s_posbacks($c->postback->s2sPostbacks, $inner_status, $subid);
    http_response_code(200);
    echo "Postback for subid $subid with status $status and payout $payout accepted.";
} else {
    http_response_code(404);
    echo "Postback for subid $subid with status $status and payout $payout NOT accepted! Subid NOT FOUND.";
}

function process_s2s_posbacks(array $s2s_postbacks, string $inner_status, string $subid): void
{
    $mp = new MacrosProcessor($subid);
    foreach ($s2s_postbacks as $s2s) {
        if (!in_array($inner_status, $s2s->events)) continue;
        if (empty($s2s->url)) continue;
        $final_url = str_replace('{status}', $inner_status, $s2s->url);
        $final_url = $mp->replace_url_macros($final_url);
        $s2s_res = '';
        switch ($s2s->method) {
            case 'GET':
                $s2s_res = get($final_url);
                break;
            case 'POST':
                $urlParts = explode('?', $final_url);
                if (count($urlParts) == 1)
                    $params = array();
                else
                    parse_str($urlParts[1], $params);
                $s2s_res = post($urlParts[0], $params);
                break;
        }
        add_log("postback", "{$s2s->method}, $final_url, $inner_status, {$s2s_res['info']['http_code']}");
    }
}