<?php
require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';


if (isset($_GET['campId'])) {
    require_once __DIR__ . '/campinit.php';
    global $c;
    $stats = $c->statistics;
    $tz = $stats->timezone;

} else {
    require_once __DIR__ .'../db/db.php';
    $gs = $db->get_common_settings();
    $stats = $gs['statistics'];
    $tz = $stats['timezone'];
}

$timeRange = Dates::get_time_range($tz);
?>



<!doctype html>
<html lang="en">
<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div id="clicks"></div>
        <?=show_clicks($timeRange[0], $timeRange[1], $stats);?>
    </div>
</body>
</html>