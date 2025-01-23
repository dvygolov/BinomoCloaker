<?php
require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/campinit.php';
require_once __DIR__ . '/dates.php';

global $c;
$timeRange = Dates::get_time_range($c->statistics->timezone);
$statsHtml = show_stats($timeRange[0],$timeRange[1],$c->statistics);
?>
<!doctype html>
<html lang="en">
<?php include "head.php" ?>

<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <?=$statsHtml?>
    </div>
</body>

</html>
