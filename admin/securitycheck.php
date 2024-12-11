<?php
require_once __DIR__ . '/../debug.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/password.php';
require_once __DIR__ . '/../redirect.php';
global $cloSettings;
if (isset($cloSettings['adminDomain']) && !empty($cloSettings['adminDomain'])) {
    $currentDomain = $_SERVER['SERVER_NAME'] ?? '';
    if ($currentDomain !== $cloSettings['adminDomain']) {
        http_response_code(404);
        die();
    }
}
if (!check_password(false)) {
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $redirectUrl = '/' . $currentDir . '/login.php';
    redirect($redirectUrl);
    exit;
}