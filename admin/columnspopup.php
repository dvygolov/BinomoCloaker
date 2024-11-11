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
// Open and close popup functions
function openPopup() {
    document.getElementById("columnPopup").style.display = "block";
    populateColumnList();
}

function closePopup() {
    document.getElementById("columnPopup").style.display = "none";
}

// Close popup with Esc key
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closePopup();
    }
});

// Populate column list with checkboxes for each column
function populateColumnList() {
    let columnList = document.getElementById("columnList");
    columnList.innerHTML = ""; // Clear existing items

    let availableColumns = <?= json_encode($campColumnSettings['availableColumns']) ?>;
    let currentColumns = <?= json_encode(array_column($campColumnSettings['currentColumns'], 'field')) ?>;

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
    let currentColumns = <?= json_encode($campColumnSettings['currentColumns']) ?>;
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