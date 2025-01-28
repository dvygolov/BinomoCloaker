<?php
require_once __DIR__ . '/securitycheck.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/campinit.php';
require_once __DIR__ . '/dates.php';

global $c, $db;
$timeRange = Dates::get_time_range($c->statistics->timezone);
?>
<!doctype html>
<html lang="en">
<?php include "head.php" ?>

<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
    <?php
    $tableData ='';
    $ss = $c->statistics;
    foreach ($ss->tables as $tSettings) {
        $dataset = $db->get_statistics(
            $tSettings->columns, $tSettings->groupby, $campId,
            $timeRange[0],$timeRange[1], $ss->timezone);
        $dJson = json_encode($dataset);
        $tName = $tSettings->name;
        $tColumns = get_stats_columns($tSettings->columns, null, $tName, $tSettings->groupby);
    ?>
            <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
                <button id="download<?=$tName?>" title="Download table data as CSV" class="btn btn-success">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div id="t<?=$tName?>" style="clear: both;"></div>
            <script>
                let t<?=$tName?>Data = $dJson;
                let t<?=$tName?>Columns = $tColumns;
                let t<?=$tName?>Table = new Tabulator('#t<?=$tName?>', {
                    layout: "fitColumns",
                    columns: t<?=$tName?>Columns,
                    columnCalcs: "both",
                    pagination: "local",
                    paginationSize: 500,
                    paginationSizeSelector: [25, 50, 100, 200, 500, 1000, 2000, 5000],
                    paginationCounter: "rows",
                    dataTree: true,
                    dataTreeBranchElement:false,
                    dataTreeStartExpanded:false,
                    dataTreeChildIndent: 35,
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
    <?php } ?>
    </div>
</body>

</html>
