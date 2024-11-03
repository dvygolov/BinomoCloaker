<?php
require_once __DIR__ . '/passwordcheck.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/../settings.php';

global $startdate, $enddate;
$fieldsArr = json_decode(file_get_contents(__DIR__ . '/campaigns.json'),true);
$dataset = $db->get_campaigns($startdate->getTimestamp(),$enddate->getTimestamp(),$fieldsArr['columns']);
?>
<!doctype html>
<html lang="en">

<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="newcampaign" title="Create new campaign" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> New</button>
            <button id="" title="Campaigns table view settings" class="btn btn-info"><i class="bi bi-gear-fill"></i></button>
        </div>
        <div id="campaigns"></div>
    </div>
    <script>
        let tableData = <?= json_encode($dataset) ?>;
        let tableColumns = <?= get_campaigns_columns() ?>;
        let table = new Tabulator('#campaigns', {
            layout: "fitColumns",
            columns: tableColumns,
            pagination: false,
            height: "100%",
            data: tableData,
            columnDefaults:{
                tooltip:true,
            }
        });
    </script>
</body>

</html>
