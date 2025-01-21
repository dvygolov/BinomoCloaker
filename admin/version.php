<div id="version">
 Ver. <?= file_get_contents(__DIR__.'/version.txt') ?>
 <br />
 PHP: <?= phpversion() ?>
<?php
if (extension_loaded('sqlite3')) 
    echo "<br />SQLite: ".SQLite3::version()['versionString'];
else
    echo "<br />SQLite: <div style='color:red'>NOT FOUND!</div><br />";

if (extension_loaded('curl')) 
    echo "<br />cURL: ".curl_version()['version'];
else
    echo "<br />cURL: <div style='color:red'>NOT FOUND!</div><br />";
?>
</div>