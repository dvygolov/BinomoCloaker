<?php
require_once __DIR__ . '/passwordcheck.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/dates.php';

$gs = $db->get_common_settings();
$timeRange = Dates::get_time_range($gs['statistics']['timezone']);
$dataset = $db->get_campaigns($timeRange[0],$timeRange[1],
    array_column($gs['statistics']['table'],'field'));
?>
<!doctype html>
<html lang="en">

<?php include "head.php" ?>

<body>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="newCampaign" title="Create new campaign" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> New</button>
            <button id="columnsSelect" title="Campaigns table view settings" class="btn btn-info" rel="modal:open"><i class="bi bi-layout-three-columns"></i></button>
            <button id="trafficBack" title="Set url of trafficback" class="btn btn-info"><i class="bi bi-exclude"></i></button>
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
            fetch("clmneditor.php?action=width", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(updatedColumn),
            });
        });
    </script>
<?php
require_once __DIR__.'/clmns.php';
?>
<div id="columnPopup" class="modal">
    <div class="modal-content">
        <h3 class="text-white">Select and Order Columns</h3>
        <ul id="columnList" class="list-group mb-3"></ul>
        <div class="button-container">
            <button class="btn btn-primary me-2" onclick="applyColumnSettings()">Apply</button>
            <button class="btn btn-secondary" rel="modal:close">Cancel</button>
        </div>
    </div>
</div>

<script>
// Populate column list with checkboxes for each column
function populateColumnList() {
    let columnList = document.getElementById("columnList");
    columnList.innerHTML = ""; // Clear existing items

    let availableColumns = <?= json_encode($availableColumns) ?>;
    let currentColumns = <?= json_encode(array_column($gs['statistics']['table'], 'field')) ?>;

    availableColumns.forEach(column => {
        let li = document.createElement("li");
        
        // Checkbox and label for each column
        li.innerHTML = `
            <input type="checkbox" class="column-checkbox me-2" data-field="${column}" ${currentColumns.includes(column) ? 'checked' : ''}>
            <span>${column.charAt(0).toUpperCase() + column.slice(1)}</span>
        `;

        li.dataset.field = column;
        columnList.appendChild(li);
    });

    // Make list sortable
    new Sortable(columnList, {
        animation: 150
    });
}

// Apply settings and reorder columns in the table
function applyColumnSettings() {
    let selectedColumns = [];
    document.querySelectorAll("#columnList li").forEach(item => {
        let checkbox = item.querySelector(".column-checkbox");
        if (checkbox.checked) {
            selectedColumns.push({
                field: item.dataset.field,
                width: getWidthForField(item.dataset.field)
            });
        }
    });

    // Send selectedColumns to Tabulator or store it as preferred
    updateTableColumns(selectedColumns);

    closePopup();
}

// Get width for each field based on currentColumns setting
function getWidthForField(field) {
    let currentColumns = <?= json_encode($gs['statistics']['table']) ?>;
    let col = currentColumns.find(col => col.field === field);
    return col ? col.width : -1;
}

// Example of updating the Tabulator columns
function updateTableColumns(selectedColumns) {
    // Update Tabulator with selected columns
    let table = Tabulator.findTable("#campaigns")[0];
    table.setColumns(selectedColumns);
}
</script>
</body>

</html>