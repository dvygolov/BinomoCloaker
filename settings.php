<?php
$cloSettings =
[
//password for the cloaker's admin page
"adminPassword" => "12345qweasd",

//WARNING:if you are using nginx either change your website's config so that it prevents people from
//downloading your database, or just rename the db file so security through obscurity will work! :-D
//TODO: add an ability to quickly switch from SQLite to MySQL
"dbFileName" => "clicks.db",

//default timezone to show statistics for all campaigns on the admin's index.php page
"timezone" => "Europe/Moscow",

//if you want to automatically update MaxMind's geobases then go to maxmind.com, register, get API key
//and put it here
"maxMindKey" => "",

//if you want to use universal thankyou page (UTP) instead of the thankyou pages from your landings, then put the relative path to UTP folder here
"thankyouFolder" => "thankyou",

//If the cloaker won't find any suitable campaign for the incoming traffic - 
//it will be redirected to the traffcback url
"trafficBackUrl" => "https://google.com",

//if true the cloaker will:
//- show any PHP error if any,
//- won't obfuscate any javascript code
//- will add YWB headers to the response, where you'll be able to see, how long does it take to process requests
"debug" => true
];

date_default_timezone_set($cloSettings['timezone']);
$dtz = new DateTimeZone($cloSettings['timezone']);

$startdate = isset($_GET['startdate']) ?
DateTime::createFromFormat('d.m.y', $_GET['startdate'], $dtz) :
new DateTime("now", $dtz);
$enddate = isset($_GET['enddate']) ?
DateTime::createFromFormat('d.m.y', $_GET['enddate'], $dtz) :
new DateTime("now", $dtz);
$startdate->setTime(0, 0, 0);
$enddate->setTime(23, 59, 59);