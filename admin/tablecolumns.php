<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/clmns.php';

function get_stats_columns(array $columns, ?array $widths=null, ?string $tName=null): string
{
    $columnSettings = TableColumns::$statsClmns;
    $tabulatorColumns = [];
    
    //if we have a groupby parameter, then we should add a group column with a specific name
    if (!is_null($tName))
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


function get_clicks_columns(?int $campId, string $timezone,  array $columns): string
{
    $columnSettings = TableColumns::$clickClmns;

    $defaultColumns = 
    [
        "subid"=>[
            "title" => "Subid",
            "field" => "subid",
            "formatter" => "link",
            "formatterParams" => [
                "urlPrefix" => "clicks.php?campId=$campId&filter=single&subid="
            ],
            "headerTooltip" => "Unique click id",
            "headerSort" => false,
        ],
        "time"=>[
            "title" => "Time",
            "field" => "time",
            "formatter" => "datetime",
            "formatterParams" => [
                "inputFormat" => "unix",
                "outputFormat" => "yyyy-MM-dd HH:mm:ss",
                "timezone" => "$timezone"
            ],
            "sorter" => "datetime",
            "sorterParams" => [
                "format" => "unix"
            ]
        ]
    ];

    $tabulatorColumns = [];
    for($i=0; $i<count($columns); $i++)
    {
        $clmn = $columns[$i]['field'];
        $width = $columns[$i]['width']??-1;
        if (array_key_exists($clmn, $columnSettings)) {
            $tabulatorColumns[] = $columnSettings[$clmn];
        }
        else if (array_key_exists($clmn, $defaultColumns)) {
            $tabulatorColumns[] = $defaultColumns[$clmn];
        }
        else{
            $tabulatorColumns[] = ["title"=>$clmn,"field"=>$clmn];
        }
        if ($width===-1) continue;
        $tabulatorColumns[count($tabulatorColumns)-1]["width"] = $width;
    }
    $clmnsJson = json_encode($tabulatorColumns);
    $clmnsJson = str_replace('"FSTART', '', $clmnsJson);
    $clmnsJson = str_replace('FEND"', '', $clmnsJson);
    return $clmnsJson;
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
                    <button class="btn btn-camp btn-rename" title="Rename"><i class="bi bi-pencil-fill"></i></button>
                    <button class="btn btn-camp btn-delete" title="Delete"><i class="bi bi-file-x"></i></button>
                    <button class="btn btn-camp btn-clone" title="Clone"><i class="bi bi-copy"></i></button>
                    <button class="btn btn-camp btn-stats" title="View stats"><i class="bi bi-bar-chart-fill"></i></button>
                    <button class="btn btn-camp btn-allowed" title="View allowed clicks"><i class="bi bi-person-circle"></i></button>
                    <button class="btn btn-camp btn-blocked" title="View blocked clicks"><i class="bi bi-ban"></i></button>
                    <button class="btn btn-camp btn-leads" title="View leads"><i class="bi bi-coin"></i></button>`;
            },
            "width":200
        },
JSON;


    $names = array_column($clmnWidths,'field');
    $widths = array_column($clmnWidths,'width');
    $statColumns = get_stats_columns($names,$widths);
    
    $defaultClmns.=substr($statColumns,1);
    return $defaultClmns;
}
