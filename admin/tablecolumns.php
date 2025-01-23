<?php
require_once __DIR__ . '/../db/db.php';

function show_stats($startDate, $endDate, StatisticsSettings $ss):string
{
    global $db, $campId;

    $tableData ='';
    foreach ($ss->tables as $tSettings) {
        $dataset = $db->get_statistics(
            $tSettings->columns, $tSettings->groupby, $campId,
            $startDate,$endDate, $ss->timezone);
        $dJson = json_encode($dataset);
        $tName = $tSettings->name;
        $tColumns = get_stats_columns(
            $tSettings->columns, null, $tName, $tSettings->groupby);
        $tableData.= <<<EOF
            <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
                <button id="download{$tName}" title="Download table data as CSV" class="btn btn-success">
                    <i class="bi bi-download"></i>
                </button>
            </div>
            <div id="t$tName" style="clear: both;"></div>
            <script>
                let t{$tName}Data = $dJson;
                let t{$tName}Columns = $tColumns;
                let t{$tName}Table = new Tabulator('#t{$tName}', {
                    layout: "fitColumns",
                    columns: t{$tName}Columns,
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
                    data: t{$tName}Data,
                    columnDefaults:{
                        tooltip:true,
                    }
                });

                document.getElementById("download{$tName}").onclick = () => {
                    t{$tName}Table.download("csv", "{$tName}_data.csv");
                };
            </script>
            <br/>
            <br/>
EOF;
    }
    return $tableData;
}

function show_clicks($startDate, $endDate, StatisticsSettings $ss):string
{
    global $db, $campId;

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
    $tColumns = get_clicks_columns($campId, $filter, $ss->timezone);
    $tableData = <<<EOF
        <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
                <button id="download{$tName}" title="Download table as CSV" class="btn btn-success">
                    <i class="bi bi-download"></i> Download CSV
                </button>
            </div>
            <div id="t$tName" style="clear: both;"></div>
            <script>
                let t{$tName}Data = $dJson;
                let t{$tName}Columns = $tColumns;
                let t{$tName}Table = new Tabulator('#t{$tName}', {
                    layout: "fitColumns",
                    columns: t{$tName}Columns,
                    columnCalcs: "both",
                    pagination: "local",
                    paginationSize: 500,
                    paginationSizeSelector: [25, 50, 100, 200, 500, 1000, 2000, 5000],
                    paginationCounter: "rows",
                    height: "100%",
                    data: t{$tName}Data,
                    columnDefaults:{
                        tooltip:true,
                    }
                });

                document.getElementById("download{$tName}").onclick = () => {
                    t{$tName}Table.download("csv", "{$tName}_data.csv");
                };
            </script>
            <br/>
            <br/>
EOF;
    return $tableData;
}

function get_stats_columns(array $columns, ?array $widths=null, ?string $tName=null, ?array $groupby=null): string
{
    $columnSettings = [
        'preland' => [
            "title" => "Preland",
            "field" => "preland",
            "headerFilter" => "input",
        ],
        'land' => [
            "title" => "Land",
            "field" => "land",
            "headerFilter" => "input",
        ],
        'country' => [
            "title" => "Country",
            "field" => "country",
            "headerFilter" => "input",
            "width" => "50",
        ],
        'lang' => [
            "title" => "Lang",
            "field" => "lang",
            "headerFilter" => "input",
            "width" => "50",
        ],
        'isp' => [
            "title" => "ISP",
            "field" => "isp",
            "headerFilter" => "input",
        ],
        'date' => [
            "title" => "Date",
            "field" => "date",
            "sorter" => "date",
            "sorterParams"=>[
                "format"=>"yyyy-MM-dd",
                "alignEmptyValues"=>"top",
            ]
        ],
        'os' => [
            "title" => "OS",
            "field" => "os",
            "headerFilter" => "input",
            "width" => "100",
        ],
        'clicks' => [
            "title" => "Clicks",
            "field" => "clicks",
            "width"=>"90",
            "bottomCalc"=>"sum"
        ],
        'uniques' => [
            "title" => "Uniques",
            "field" => "uniques",
            "width"=>"90",
            "bottomCalc"=>"sum"
        ],
        'uniques_ratio' => [
            "title" => "U/C",
            "field" => "uniques_ratio",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'conversion' => [
            "title" => "CV",
            "field" => "conversion",
            "width" => "60",
            "bottomCalc"=>"sum"
        ],
        'purchase' => [
            "title" => "P",
            "field" => "purchase",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'hold' => [
            "title" => "H",
            "field" => "hold",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'reject' => [
            "title" => "R",
            "field" => "reject",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'trash' => [
            "title" => "T",
            "field" => "trash",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'lpclicks' => [
            "title" => "LPClicks",
            "field" => "lpclicks",
            "width" => "70",
            "bottomCalc"=>"sum"
        ],
        'lpctr' => [
            "title" => "LPCTR",
            "field" => "lpctr",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'cra' => [
            "title" => "CRa",
            "field" => "cra",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'crs' => [
            "title" => "CRs",
            "field" => "crs",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'appt' => [
            "title" => "App(t)",
            "field" => "appt",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'app' => [
            "title" => "App",
            "field" => "app",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "symbol"=> "%",
                "symbolAfter"=> true,
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
        'cpc' => [
            "title" => "CPC",
            "field" => "cpc",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 5,
            ],
            "bottomCalc"=>"avg"
        ],
        'costs' => [
            "title" => "Costs",
            "field" => "costs",
            "width" => "100",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 2,
            ],
            "bottomCalc"=>"sum",
            "bottomCalcParams"=>[
                "precision" => 2,
            ]
        ],
        'epc' => [
            "title" => "EPC",
            "field" => "epc",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 5,
            ],
            "bottomCalc"=>"avg"
        ],
        'epuc' => [
            "title" => "EPuC",
            "field" => "epuc",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 5,
            ],
            "bottomCalc"=>"avg"
        ],
        'revenue' => [
            "title" => "Rev.",
            "field" => "revenue",
            "width" => "100",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 2,
            ],
            "bottomCalc"=>"sum",
            "bottomCalcParams"=>[
                "precision" => 2,
            ]
        ],
        'profit' => [
            "title" => "Profit",
            "field" => "profit",
            "width" => "100",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 2,
            ],
            "bottomCalc"=>"sum",
            "bottomCalcParams"=>[
                "precision" => 2,
            ]
        ],
        'roi' => [
            "title" => "ROI",
            "field" => "roi",
            "width"=>"90",
            "formatter"=> "money",
            "formatterParams"=>[
                "decimal"=> ".",
                "thousand"=> ",",
                "precision"=> 2,
            ],
            "bottomCalc"=>"avg"
        ],
    ];

    $tabulatorColumns = [];
    
    //if we have a groupby parameter, then we should add a group column with a specific name
    if (!is_null($tName) && !is_null($groupby) && count($groupby) > 0)
        $tabulatorColumns[] = ["title" => $tName, "field" => "group"];

    for($i=0; $i<count($columns); $i++)
    {
        $clmn = $columns[$i];
        $width = $widths[$i]??-1;
        if (array_key_exists($clmn, $columnSettings)) {
            $tabulatorColumns[] = $columnSettings[$clmn];
        }
        else{
            $tabulatorColumns[] = ["title"=>$clmn,"field"=>$clmn];
        }
        if ($width===-1) continue;
        $tabulatorColumns[count($tabulatorColumns)-1]["width"] = $width;
    }
    return json_encode($tabulatorColumns);
}

function get_clicks_columns(int $campId, string $filter, $timezone): string
{
    $columns = [];
    switch ($filter) {
        case 'blocked':
            $columns = <<<JSON
            [
                {
                    "title": "IP",
                    "field": "ip",
                    "width": "150",
                    "headerFilter": "input"
                },
                {
                    "title": "Country",
                    "field": "country",
                    "headerFilter": "input",
                    "width": "50"
                },
                {
                    "title": "ISP",
                    "field": "isp",
                    "headerFilter": "input"
                },
                {
                    "title": "Time",
                    "field": "time",
                    "formatter": "datetime",
                    "formatterParams": {
                        "inputFormat": "unix",
                        "outputFormat": "yyyy-MM-dd HH:mm:ss",
                        "timezone": "$timezone"
                    },
                    "sorter": "datetime",
                    "sorterParams": {
                        "format": "unix"
                    }
                },
                {
                    "title": "Reason",
                    "field": "reason",
                    "formatter": "plaintext",
                    "sorter": "string",
                    "width": "80",
                    "headerFilter": "input"
                },
                {
                    "title": "OS",
                    "field": "os",
                    "headerFilter": "input",
                    "width": "100"
                },
                {
                    "title": "UA",
                    "field": "ua",
                    "formatter": "textarea",
                    "headerFilter": "input"
                },
                {
                    "title": "Subs",
                    "field": "params",
                    "headerFilter": "input",
                    "headerFilterFunc": function(headerValue, rowValue, rowData, filterParams){
                        if (rowValue.length===0) return false;
                        return JSON.stringify(rowValue).includes(headerValue);
                    },
                    "headerSort":false,
                    "tooltip": function(e, cell, onRendered){
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    },
                    "formatter": function(cell, formatterParams, onRendered) {
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    }
                },
            ]
JSON;
            break;
        case 'single':
            $columns = <<<JSON
            [
                {
                    "title": "Subid",
                    "field": "subid"
                },
                {
                    "title": "IP",
                    "field": "ip"
                },
                {
                    "title": "Country",
                    "field": "country"
                },
                {
                    "title": "Lang",
                    "field": "lang"
                },
                {
                    "title": "ISP",
                    "field": "isp"
                },
                {
                    "title": "Time",
                    "field": "time",
                    "formatter": "datetime",
                    "formatterParams": {
                        "inputFormat": "unix",
                        "outputFormat": "yyyy-MM-dd HH:mm:ss",
                        "timezone": "$timezone"
                    },
                    "sorter": "datetime",
                    "sorterParams": {
                        "format": "unix"
                    }
                },
                {
                    "title": "OS",
                    "field": "os",
                },
                {
                    "title": "UA",
                    "field": "ua",
                },
                {
                    "title": "Subs",
                    "field": "params",
                    "headerFilter": "input",
                    "headerFilterFunc": function(headerValue, rowValue, rowData, filterParams){
                        if (rowValue.length===0) return false;
                        return JSON.stringify(rowValue).includes(headerValue);
                    },
                    "headerSort":false,
                    "tooltip": function(e, cell, onRendered){
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    },
                    "formatter": function(cell, formatterParams, onRendered) {
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    }
                },
                {
                    "title": "Preland",
                    "field": "preland"
                },
                {
                    "title": "Land",
                    "field": "land"
                }
            ]

JSON;
            break;
        case 'leads':
            $columns = <<<JSON
            [
                {
                    "title": "Subid",
                    "field": "subid",
                    "formatter": "link",
                    "formatterParams": {
                        "urlPrefix": "clicks.php?campId=$campId&filter=single&subid="
                    }
                },
                {
                    "title": "Time",
                    "field": "time",
                    "formatter": "datetime",
                    "formatterParams": {
                        "inputFormat": "unix",
                        "outputFormat": "yyyy-MM-dd HH:mm:ss",
                        "timezone": "$timezone"
                    },
                    "sorter": "datetime",
                    "sorterParams": {
                        "format": "unix"
                    }
                },
                {
                    "title": "Name",
                    "field": "name"
                },
                {
                    "title": "Phone",
                    "field": "phone"
                },
                {
                    "title": "Status",
                    "field": "status"
                },
                {
                    "title": "Preland",
                    "field": "preland"
                },
                {
                    "title": "Land",
                    "field": "land"
                }
            ]
JSON;
            break;
        default:
            $columns = <<<JSON
            [
                {
                    "title": "Subid",
                    "field": "subid",
                    "formatter": "link",
                    "formatterParams": {
                        "urlPrefix": "clicks.php?campId=$campId&filter=single&subid="
                    },
                    "headerSort":false,
                    "width":"100"
                },
                {
                    "title": "IP",
                    "field": "ip",
                    "headerFilter": "input",
                    "width": "120"
                },
                {
                    "title": "Country",
                    "field": "country",
                    "headerFilter": "input",
                    "width": "80"
                },
                {
                    "title": "Lang",
                    "field": "lang",
                    "headerFilter": "input",
                    "width": "50"
                },
                {
                    "title": "ISP",
                    "field": "isp",
                    "headerFilter": "input"
                },
                {
                    "title": "Time",
                    "field": "time",
                    "formatter": "datetime",
                    "formatterParams": {
                        "inputFormat": "unix",
                        "outputFormat": "yyyy-MM-dd HH:mm:ss",
                        "timezone": "$timezone"
                    },
                    "sorter": "datetime",
                    "sorterParams": {
                        "format": "unix"
                    }
                },
                {
                    "title": "OS",
                    "field": "os",
                    "headerFilter": "input",
                    "width": "100"
                },
                {
                    "title": "OSVer",
                    "field": "osver",
                    "headerFilter": "input",
                    "width": "100"
                },
                {
                    "title": "UA",
                    "field": "ua",
                    "headerFilter": "input",
                    "formatter": "textarea",
                    "width":"200"
                },
                {
                    "title": "Subs",
                    "field": "params",
                    "headerFilter": "input",
                    "headerFilterFunc": function(headerValue, rowValue, rowData, filterParams){
                        if (rowValue.length===0) return false;
                        return JSON.stringify(rowValue).includes(headerValue);
                    },
                    "headerSort":false,
                    "tooltip": function(e, cell, onRendered){
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    },
                    "formatter": function(cell, formatterParams, onRendered) {
                        var data = cell.getValue();

                        var keys = Object.keys(data).sort();
                        var formattedData = "";

                        keys.forEach(function(key) {
                            if (data.hasOwnProperty(key)) {
                                formattedData += key + "=" + data[key] + "<br>";
                            }
                        });
                        return formattedData;
                    }
                },
                {
                    "title": "Preland",
                    "field": "preland",
                    "headerFilter": "input",
                    "width":"100"
                },
                {
                    "title": "Land",
                    "field": "land",
                    "headerFilter": "input",
                    "width":"150"
                }
            ]
JSON;
            break;
    }
    return $columns;
}


function get_campaigns_columns(array $clmnWidths): string
{
    $defaultClmns = <<<JSON
    [
        {
            "title": "ID",
            "field": "id",
            "visible": false,
        },
        {
            "title": "Name",
            "formatter": "link",
            "formatterParams": {
                "urlField":"id",
                "urlPrefix":"campsettings.php?campId="
            },
            "field": "name",
            "headerFilter": "input",
            "width": 90,
            "bottomCalc":() => "TOTAL"
        },
        {
            "title": "Actions",
            "formatter": "html",
            "hozAlign": "center",
            "cellClick": campActionsHandler,
            "formatter": function() {
                return `
                    <button class="btn btn-rename" title="Rename"><i class="bi bi-pencil-fill"></i></button>
                    <button class="btn btn-delete" title="Delete"><i class="bi bi-file-x"></i></button>
                    <button class="btn btn-clone" title="Clone"><i class="bi bi-copy"></i></button>
                    <button class="btn btn-stats" title="View stats"><i class="bi bi-bar-chart-fill"></i></button>
                    <button class="btn btn-allowed" title="View allowed clicks"><i class="bi bi-person-circle"></i></button>
                    <button class="btn btn-blocked" title="View blocked clicks"><i class="bi bi-ban"></i></button>`;
            },
            "width":280
        },
JSON;


    $names = array_column($clmnWidths,'field');
    $widths = array_column($clmnWidths,'width');
    $statColumns = get_stats_columns($names,$widths);
    
    $defaultClmns.=substr($statColumns,1);
    return $defaultClmns;
}
