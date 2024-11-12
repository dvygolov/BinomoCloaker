<?php

function get_time_range(string $timezone): array
{
    date_default_timezone_set($timezone);
    $dtz = new DateTimeZone($timezone);
    $startdate = isset($_GET['startdate']) ?
        DateTime::createFromFormat('d.m.y', $_GET['startdate'], $dtz) :
        new DateTime("now", $dtz);
    $enddate = isset($_GET['enddate']) ?
        DateTime::createFromFormat('d.m.y', $_GET['enddate'], $dtz) :
        new DateTime("now", $dtz);
    $startdate->setTime(0, 0, 0);
    $enddate->setTime(23, 59, 59);
    
    return [$startdate->getTimestamp(), $enddate->getTimestamp()];
}
?>