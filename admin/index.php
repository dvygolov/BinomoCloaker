<?php
//we always need a slash at the end of the url, otherwise links will not work properly
$url = $_SERVER['REQUEST_URI'];
if ($url==='/admin'){
    header("Location: " . $url . "/");
    exit();
}

require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/clmns.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';

$gs = $db->get_common_settings();
$timeRange = Dates::get_time_range($gs['statistics']['timezone']);
$dataset = $db->get_campaigns(
    $timeRange[0],
    $timeRange[1],
    array_column($gs['statistics']['table'], 'field')
);
?>
<!doctype html>
<html lang="en">
<?php include __DIR__."/head.php" ?>
<body>
    <?php include __DIR__."/header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="newCampaign" title="Create new campaign" class="btn btn-primary"><i
                    class="bi bi-plus-circle-fill"></i> New</button>
            <button id="columnsSelect" title="Select and order columns" class="btn btn-info"><i
                    class="bi bi-layout-three-columns"></i></button>
            <button id="trafficBack" title="Trafficback url" class="btn btn-info"><i
                    class="bi bi-exclude"></i></button>
            <button id="trafficBackStats" title="Show trafficback statistics" class="btn btn-info"><i
                    class="bi bi-graph-up"></i></button>
            <button id="downloadCsv" title="Download table as CSV" class="btn btn-success" style="float: right;"><i
                    class="bi bi-download"></i></button>
        </div>
        <div id="campaigns"></div>
    </div>
    <script>
        let tableData = <?= json_encode($dataset) ?>;
        let tableColumns = <?= get_campaigns_columns($gs['statistics']['table']) ?>;
        let table = new Tabulator('#campaigns', {
            layout: "fitColumns",
            columns: tableColumns,
            pagination: false,
            height: "100%",
            data: tableData,
            columnDefaults: {
                tooltip: true,
            },
            columnCalcs: "both"
        });

        table.on("columnResized", async function (column) {
            let updatedColumn = { field: column.getField(), width: column.getWidth() };
            await fetch("commoneditor.php?action=width&table=campaigns", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(updatedColumn),
            });
        });
    </script>
    <?php include __DIR__."/clmnspopup.html" ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("newCampaign").onclick = async () => {
                let campName = prompt("Enter new campaign name:");
                if (campName)
                    await campEditor('add', null, campName);
            };

            document.getElementById("trafficBack").onclick = async () => {
                let tbUrl = prompt("Enter trafficback url:", "<?= $gs['trafficBackUrl'] ?>");
                if (tbUrl === null) return;
                let res = await fetch("commoneditor.php?action=trafficback", {
                    method: "POST",
                    body: tbUrl,
                });
                if (!res['error']) {
                    alert('TrafficBack url saved!');
                    window.location.reload();
                }
                else
                    alert('Error saving trafficback url:' + res['msg']);
            };

            document.getElementById("trafficBackStats").onclick = () => {
                window.location.href = "clicks.php?filter=trafficback";
            };

            document.getElementById("downloadCsv").onclick = () => {
                table.download("csv", "campaigns_data.csv");
            };
        
            let availableClmns = <?= json_encode(AvailableColumns::get_columns_for_type('stats')) ?>;
            let selectedClmns = <?= json_encode($gs['statistics']['table']) ?>;
            addColumnsToList(selectedClmns, availableClmns);
            setSaveButtonHandler("commoneditor.php?action=savecolumns&table=campaigns");
        });
    </script>
</body>

</html>