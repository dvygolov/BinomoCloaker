<?php
require_once __DIR__ . '/../logging.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../cookies.php';

function check_password($die = true): bool
{
    global $cloSettings;
    $pwd = $cloSettings['adminPassword'];
    $debug = $cloSettings['debug'];
    get_session();

    if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
        add_log('trace','Already logged in!');
        return true;
    }else{
        if (empty($_SESSION["loggedin"]))
            add_log('trace','Loggedin is empty!');
        else if ($_SESSION["loggedin"]!==true)
            add_log('trace','Loggedin is not true!');
    }

    if (empty($pwd)){
        add_log('trace','Empty admin password in settings!');
        return true;
    }

    if (!isset($_REQUEST['password'])) {
        $msg = "No password found!";
        add_log("login", $msg, true);
        if ($die){
            die($msg);
        }else{
            return false;
        }
    }
    
    if (empty($_REQUEST['password'])) {
        $msg = "Empty password!";
        add_log("login", $msg, true);
        if ($die){
    die($msg);
        }else{
            return false;
        }
    }

    if ($_REQUEST['password'] !== $pwd) {
        $msg = "Incorrect password!";
        add_log("login", $msg, true);
        if ($die){
            die($msg);
        }else{
            return false;
        }
    }

    add_log("login", "Logged in, setting session.", true);
    $_SESSION['loggedin'] = true;
    session_write_close();
    return true;
}