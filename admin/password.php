<?php
require_once __DIR__ . '/../logging.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../cookies.php';

function check_password($die = true): bool
{
    global $cloSettings;
    $pwd = $cloSettings['adminPassword'];
    get_session();
    $debug = $cloSettings['debug'];

    if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
        if ($debug) header("YWBLogin: Already logged in!");
        return true;
    }

    if (empty($pwd)){
        if ($debug) header("YWBLogin: Empty admin password in settings!");
        return true;
    }

    if (!isset($_REQUEST['password'])) {
        $msg = "No password found!";
        if ($debug) header("YWBLogin: No password in request!");
        add_log("login", $msg, true);
        if ($die){
            die($msg);
        }else{
            return false;
        }
    }
    
    if (empty($_REQUEST['password'])) {
        $msg = "Empty password!";
        if ($debug) header("YWBLogin: Empty password in request!");
        add_log("login", $msg, true);
        if ($die){
    die($msg);
        }else{
            return false;
        }
    }

    if ($_REQUEST['password'] !== $pwd) {
        $msg = "Incorrect password!";
        if ($debug) header("YWBLogin: Incorrect password!");
        add_log("login", $msg, true);
        if ($die){
            die($msg);
        }else{
            return false;
        }
    }

    if ($debug) header("YWBLogin: Logged in, setting session.");
    $_SESSION['loggedin'] = true;
    session_write_close();
    return true;
}