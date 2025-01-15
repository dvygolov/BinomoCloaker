<?php
require_once __DIR__ . '/../debug.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/password.php';
require_once __DIR__ . '/../redirect.php';
global $cloSettings;
$admDomain = $cloSettings['adminDomain'];
if (isset($admDomain) && !empty($admDomain)) {
    $currentDomain = $_SERVER['SERVER_NAME'] ?? '';
    if ($currentDomain !== $admDomain) {
        if ($cloSettings['debug'] === true) {
            echo "Admin Domain ".$admDomain." is set, but your domain is $currentDomain. You are not allowed to access this page!";
        } else {
            http_response_code(404);
        }
        die();
    }
}
if (!check_password(false)) {
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $redirectUrl = '/' . $currentDir . '/login.php';
    redirect($redirectUrl);
    exit;
}