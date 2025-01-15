<?php
require_once __DIR__ . '/../logging.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../cookies.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    $result = array('success' => check_password(false));
    echo json_encode($result);
    exit();
}

function check_password($die = true): bool
{
    global $cloSettings;
    $pwd = $cloSettings['adminPassword'];
    $debug = $cloSettings['debug'];
    get_session();

    if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
        if ($debug) header("YWBLoginSession: Already logged in!");
        return true;
    }else{
        if ($debug) {
            if (empty($_SESSION["loggedin"]))
                header("YWBLoginSession: Loggedin is empty!");
            else if ($_SESSION["loggedin"]!==true)
                header("YWBLoginSession: Loggedin is not true!");
        }
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