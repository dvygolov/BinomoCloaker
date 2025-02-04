<?php
$cloSettings =
[
//password for the cloaker's admin page
"adminPassword" => "12345qweasd",

//if you add a domain here, then only users from this domain will be able to access the admin page
//all others will get a 404
"adminDomain" => "",

//WARNING:if you are using nginx either change your website's config so that it prevents people from
//downloading your database, or just rename the db file so security through obscurity will work! :-D
//TODO: add an ability to quickly switch from SQLite to MySQL
"dbConnection" => "clicks.db",

//if you want to automatically update MaxMind's geobases 
//then go to maxmind.com, register, get API key and put it here
"maxMindKey" => "",

//if you want to use universal thankyou page (UTP) instead of the thankyou pages from your landings, then put the relative path to UTP folder here
"thankyouFolder" => "thankyou",

//if true the cloaker will:
//- show PHP errors if any,
//- won't obfuscate any javascript code
//- will add YWB headers to the response, where you'll be able to see, how long does it take to process requests
"debug" => true,

//TODO: implement!
//if true the cloaker will automatically check for new versions and update itself from GitHub
"autoupdate" => true
];
