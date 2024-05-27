<?php
global $startdate, $enddate, $stats_timezone;
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/initialization.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/../settings.php';

$db = new Db();
$dataset = $db->get_campaigns();
?>
<!doctype html>
<html lang="en">

<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <button id="newcampaign">New campaign</button>
        <div id="campaigns"></div>
    </div>
    <script>
        let tableData = <?= json_encode($dataset) ?>;
        let tableColumns = <?= get_campaigns_columns() ?>;
        let table = new Tabulator('#campaigns', {
            layout: "fitColumns",
            columns: tableColumns,
            pagination: "local",
            paginationSize: 50,
            paginationSizeSelector: [25, 50, 100, 200, 500],
            paginationCounter: "rows",
            height: "100%",
            data: tableData,
            columnDefaults:{
                tooltip:true,
            }
        });
    </script>

    <script>
    </script>
</body>

</html>
