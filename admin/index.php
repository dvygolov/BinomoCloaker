<?php
require_once __DIR__ . '/passwordcheck.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';

$s = $db->get_global_settings();
$timeRange = get_time_range($s['statistics']['timezone']);
$dataset = $db->get_campaigns($timeRange[0],$timeRange[1],
    array_column($s['statistics']['table'],'field'));
?>
<!doctype html>
<html lang="en">

<?php include "head.php" ?>

<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="newCampaign" title="Create new campaign" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> New</button>
            <button id="columnsSelect" title="Campaigns table view settings" class="btn btn-info"><i class="bi bi-layout-three-columns"></i></button>
            <button id="trafficBack" title="Set url of trafficback" class="btn btn-info"><i class="bi bi-exclude"></i></button>
        </div>
        <div id="campaigns"></div>
    </div>
    <script>
        let tableData = <?= json_encode($dataset) ?>;
        let tableColumns = <?= get_campaigns_columns($campColumnSettings['currentColumns']) ?>;
        let table = new Tabulator('#campaigns', {
            layout: "fitColumns",
            columns: tableColumns,
            pagination: false,
            height: "100%",
            data: tableData,
            columnDefaults:{
                tooltip:true,
            },
            columnResized: function (column) {
                saveColumnWidths();
            },
            columnCalcs:"both"
        });

        table.on("columnResized", function (column) {
            let updatedColumn = { field: column.getField(), width: column.getWidth() };
            fetch("savewidth.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(updatedColumn),
            });
        });
    </script>
<?php include_once __DIR__.'/columnspopup.php';?>
</body>

</html>