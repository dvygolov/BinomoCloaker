<?php
require_once __DIR__ . '/bases/ipcountry.php';
require_once __DIR__ . '/debug.php';
function add_log($subdir, $msg, $logIp = false)
{
    if ($subdir ==='trace' && !DebugMethods::on())
        return;
    $dir = __DIR__ . "/logs/$subdir";
    if (!file_exists($dir)) 
        mkdir($dir, 0777, true);
    $date = date("d.m.y");
    $fileName = "$dir/$date.log";
    $file = fopen($fileName, 'a+');
    $time = date("Y-m-d H:i:s");
    if ($logIp) {
        $ip = getip();
        $time .= " $ip";
    }
    $msg = "$time $msg\n";
    fwrite($file, $msg);
    fflush($file);
    fclose($file);
}