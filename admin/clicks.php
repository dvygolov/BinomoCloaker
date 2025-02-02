<?php
require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';

global $db;
if (isset($_GET['campId'])) {
    require_once __DIR__ . '/campinit.php';
    global $c, $campId;
    $tz = $c->statistics->timezone;

} else {
    require_once __DIR__ .'/../db/db.php';
    $gs = $db->get_common_settings();
    $tz = $gs['statistics']['timezone'];
    $campId = null;
}

$timeRange = Dates::get_time_range($tz);
$startDate = $timeRange[0];
$endDate = $timeRange[1];

$filter = $_GET['filter'] ?? '';
switch ($filter) {
    case 'trafficback':
        $dataset = $db->get_trafficback_clicks($startDate, $endDate);
        $stats = $gs['statistics']['trafficBack'];
        break;
    case 'leads':
        $dataset = $db->get_leads($startDate, $endDate, $campId);
        $stats = $c->statistics->leads;
        break;
    case 'blocked':
        $dataset = $db->get_white_clicks($startDate, $endDate, $campId);
        $stats = $c->statistics->blocked;
        break;
    case 'single':
        $clickId = $_GET['subid'] ?? '';
        $dataset = $db->get_clicks_by_subid($clickId);
        $stats = $c->statistics->allowed;
        break;
    default:
        $dataset = $db->get_black_clicks($startDate, $endDate, $campId);
        $stats = $c->statistics->allowed;
        break;
}

$dJson = json_encode($dataset);
$tName = empty($filter) ? 'allowed' : $filter;
$tColumns = get_clicks_columns($campId, $tz,$filter, $stats);
?>

<!doctype html>
<html lang="en">
<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="columnsSelect" title="Select and order columns" class="btn btn-info"><i
                    class="bi bi-layout-three-columns"></i></button>
            <button id="downloadCsv" title="Download table as CSV" class="btn btn-success" style="float: right;"><i
                    class="bi bi-download"></i></button>
        </div>
        <div id="t<?=$tName?>" style="clear: both;"></div>
        <script>
            let t<?=$tName?>Data = <?=$dJson?>;
            let t<?=$tName?>Columns = <?=$tColumns?>;
            let t<?=$tName?>Table = new Tabulator('#t<?=$tName?>', {
                layout: "fitColumns",
                columns: t<?=$tName?>Columns,
                columnCalcs: "both",
                pagination: "local",
                paginationSize: 500,
                paginationSizeSelector: [25, 50, 100, 200, 500, 1000, 2000, 5000],
                paginationCounter: "rows",
                height: "100%",
                data: t<?=$tName?>Data,
                columnDefaults:{
                    tooltip:true,
                }
            });

            t<?=$tName?>Table.on("columnResized", async function (column) {
                let updatedColumn = { field: column.getField(), width: column.getWidth() };
                await fetch("clmnseditor.php?action=width&table=<?=$filter?>", {
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
            document.getElementById("downloadCsv").onclick = () => {
                t<?=$tName?>Table.download("csv", "<?=$tName?>_data.csv");
            };

            let availableClmns = <?= json_encode(AvailableColumns::get_columns_for_type($filter)) ?>;
            let selectedClmns = <?= json_encode($stats) ?>;
            addColumnsToList(selectedClmns, availableClmns);
            setSaveButtonHandler("clmnseditor.php?action=savecolumns&table=<?=$filter?>");
        </script>
        <br/>
        <br/>
    </div>
</body>
</html>