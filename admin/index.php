<?php
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
            <button id="columnsSelect" title="Campaigns table view settings" class="btn btn-info"><i
                    class="bi bi-layout-three-columns"></i></button>
            <button id="trafficBack" title="Trafficback settings" class="btn btn-info"><i
                    class="bi bi-exclude"></i></button>
        </div>
        <div id="campaigns"></div>
    </div>
    <?php include __DIR__."/clmnspopup.html" ?>
    <script>
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

        document.getElementById("columnsSelect").onclick = async () => {
            $('#columnModal').modal({
  fadeDuration: 250,
  fadeDelay: 0.80
});
        }

        
        document.addEventListener("DOMContentLoaded", function () {
            let availableClmns = <?= json_encode(AvailableColumns::get_columns_for_type('stats')) ?>;
            let selectedClmns = <?= json_encode($gs['statistics']['table']) ?>;

            let $list = $('#columnsList');
            availableClmns.forEach(column => {
                const isSelected = selectedClmns.some(sc => sc.field === column);
                const $item = $(`
                    <li class="sortable-item">
                        <input type="checkbox" value="${column}" ${isSelected ? 'checked' : ''}>
                        <span>${column}</span>
                    </li>`);
                $list.append($item);
            });
            
            let sortableList = new Sortable(document.getElementById('columnsList'), {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen'
            });
        });
        
        function getSelectedColumns() {
            const selectedColumns = [];
            $('#columnsList .sortable-item').each(function () {
                const $checkbox = $(this).find('input[type="checkbox"]');
                if ($checkbox.is(':checked')) {
                    selectedColumns.push({
                        field: $checkbox.val(),
                        width: -1 // Default width
                    });
                }
            });
            return selectedColumns;
        }

        // Save button handler
        $('#saveColumns').off('click').on('click', async function () {
            const selectedColumns = getSelectedColumns();

            // Save columns configuration
            let res = await fetch("commoneditor.php?action=savecolumns", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'stats',
                    columns: selectedColumns
                })
            });

            let data = await res.json();
            if (!data.error) {
                window.location.reload();
            } else {
                alert('Error saving columns: ' + data.msg);
            }

        });
    </script>
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
            await fetch("commoneditor.php?action=width", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(updatedColumn),
            });
        });
    </script>
</body>

</html>