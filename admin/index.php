<?php
require_once __DIR__ . '/passwordcheck.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/tablecolumns.php';
require_once __DIR__ . '/../settings.php';

global $startdate, $enddate;
$clmnWidths = 
    json_decode(file_get_contents(__DIR__ . '/campaigns.json'),true)['columns'];
$dataset = $db->get_campaigns(
    $startdate->getTimestamp(),$enddate->getTimestamp(),
    array_column($clmnWidths,'field'));
?>
<!doctype html>
<html lang="en">

<?php include "head.php" ?>

<body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <?php include "header.php" ?>
    <div class="all-content-wrapper">
        <div class="buttons-block">
            <button id="newcampaign" title="Create new campaign" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> New</button>
            <button id="columnsSelect" title="Campaigns table view settings" class="btn btn-info"><i class="bi bi-gear-fill"></i></button>
        </div>
        <div id="campaigns"></div>
    </div>
    <script>
        let tableData = <?= json_encode($dataset) ?>;
        let tableColumns = <?= get_campaigns_columns($clmnWidths) ?>;
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

<!-- Popup Modal -->
<div id="columnPopup" style="display: none;">
    <div class="modal-content">
        <h3>Select and Order Columns</h3>
        <ul id="columnList"></ul>
        <button onclick="applyColumnSettings()">Apply</button>
        <button onclick="closePopup()">Cancel</button>
    </div>
</div>

<style>
    /* Basic popup styles */
    #columnPopup {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        border: 1px solid #ddd;
        padding: 20px;
        z-index: 1000;
        width: 300px;
    }
    .modal-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    #columnList {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    #columnList li {
        display: flex;
        align-items: center;
        padding: 5px;
        border: 1px solid #ddd;
        margin-bottom: 5px;
        cursor: move;
    }
</style>

<script>
// Open and close popup functions
function openPopup() {
    document.getElementById("columnPopup").style.display = "block";
    populateColumnList();
}

function closePopup() {
    document.getElementById("columnPopup").style.display = "none";
}

// Populate column list with checkboxes for each column
function populateColumnList() {
    let columnList = document.getElementById("columnList");
    columnList.innerHTML = ""; // Clear existing items

    // Fetch columns from your campaigns.json structure
    let columnSettings = <?= json_encode($clmnWidths) ?>;
    columnSettings.forEach(col => {
        let li = document.createElement("li");
        li.dataset.field = col.field;
        
        // Checkbox and label for each column
        li.innerHTML = `
            <input type="checkbox" class="column-checkbox" data-field="${col.field}" ${col.visible ? 'checked' : ''}>
            <span>${col.field.charAt(0).toUpperCase() + col.field.slice(1)}</span>
        `;
        
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
                visible: true
            });
        }
    });

    // Send selectedColumns to Tabulator or store it as preferred
    updateTableColumns(selectedColumns);

    closePopup();
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
