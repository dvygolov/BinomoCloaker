<?php
require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';

global $db;
if (isset($_GET['campId'])) {
    require_once __DIR__ . '/campinit.php';
    global $c, $campId;
    $stats = $c->statistics;
    $tz = $stats->timezone;

} else {
    require_once __DIR__ .'/../db/db.php';
    $gs = $db->get_common_settings();
    $stats = $gs['statistics'];
    $tz = $stats['timezone'];
}

$timeRange = Dates::get_time_range($tz);
$startDate = $timeRange[0];
$endDate = $timeRange[1];
?>



<!doctype html>
<html lang="en">
<?php include "head.php" ?>
<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div id="clicks"></div>
        <?php
        $filter = $_GET['filter'] ?? '';
        switch ($filter) {
            case 'traficback':
                $dataset = $db->get_trafficback_clicks($startDate, $endDate);
                break;
            case 'leads':
                $dataset = $db->get_leads($startDate, $endDate, $campId);
                break;
            case 'blocked':
                $dataset = $db->get_white_clicks($startDate, $endDate, $campId);
                break;
            case 'single':
                $clickId = $_GET['subid'] ?? '';
                $dataset = $db->get_clicks_by_subid($clickId);
                break;
            default:
                $dataset = $db->get_black_clicks($startDate, $endDate, $campId);
                break;
        }
        
        $dJson = json_encode($dataset);
        $tName = empty($filter) ? 'allowed' : $filter;
        $tColumns = get_clicks_columns($campId, $filter, $tz);
        ?>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
            <button id="download<?=$tName?>" title="Download table data as CSV" class="btn btn-success">
                <i class="bi bi-download"></i>
            </button>
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

            document.getElementById("download<?=$tName?>").onclick = () => {
                t<?=$tName?>Table.download("csv", "<?=$tName?>_data.csv");
            };
        </script>
        <br/>
        <br/>
    </div>
</body>
</html>