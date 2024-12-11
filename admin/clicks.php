<?php
require_once __DIR__ . '/passwordcheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';

global $startdate, $enddate;

if (isset($_GET['campId'])) {
    require_once __DIR__ . '/campinit.php';
    global $c;
    $stats = $c->statistics;

} else {
    require_once __DIR__ .'../db/db.php';
    $gs = $db->get_common_settings();
    $stats = $gs['statistics'];
}
?>



<!doctype html>
<html lang="en">
<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div id="clicks"></div>
        <?=show_clicks($startdate, $enddate, $stats);?>
    </div>
</body>
</html>