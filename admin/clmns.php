<?php
class AvailableColumns
{
  public static $blockedColumns = [
    "time",
    "ip",
    "country",
    "lang",
    "os",
    "osver",
    "device",
    "brand",
    "model",
    "isp",
    "client",
    "clientver",
    "ua",
    "params",
    "reason"
  ];

  public static $allowedColumns = [
    "subid",
    "time",
    "ip",
    "country",
    "lang",
    "os",
    "osver",
    "device",
    "brand",
    "model",
    "isp",
    "client",
    "clientver",
    "ua",
    "params",
    "preland",
    "land",
    "lpclick",
    "status",
    "cost",
    "payout"
  ];

  public static $leadsColumns = [
    "subid",
    "time",
    "ip",
    "country",
    "lang",
    "os",
    "osver",
    "device",
    "brand",
    "model",
    "isp",
    "client",
    "clientver",
    "ua",
    "params",
    "preland",
    "land",
    "lpclick",
    "status",
    "payout",
    "name",
    "phone"
  ];
  public static $trafficbackColumns = [
    "clicks",
    "ip",
    "country",
    "lang",
    "os",
    "osver",
    "device",
    "brand",
    "model",
    "isp",
    "client",
    "clientver",
    "ua",
    "params"
  ];

  public static $groupbyColumns = [
    "date",
    "preland",
    "land",
    "isp",
    "country",
    "lang",
    "os"
  ];

  public static $statsColumns = [
    "clicks",
    "uniques",
    "uniques_ratio",
    "lpclicks",
    "lpctr",
    "cra",
    "crs",
    "epc",
    "uepc",
    "cpc",
    "ucpc",
    "appt",
    "app",
    "conversion",
    "purchase",
    "hold",
    "reject",
    "trash",
    "cpa",
    "ec",
    "revenue",
    "costs",
    "profit",
    "roi"
  ];

  public static function get_columns_for_type($type)
  {
    $clmnsName = $type.'Columns';
    return self::$$clmnsName;
  }
}

class TableColumn{
  public string $title;
  public string $field;
  public string $description;
  public int $width;
  public string $viewModel;

  public function __construct(string $title, string $field, string $description, int $width, ?string $viewModel) {
    $this->title = $title;
    $this->field = $field;
    $this->description = $description;
    $this->width = $width;
    $this->viewModel = $viewModel;
  }

  public function ToDbJson(): string {
    return json_encode([
      "field" => $this->field,
      "width" => $this->width
    ]);
  }
  
  public function ToTabulatorJson(): string {
    $commonPart = [
      "title" => $this->title,
      "field" => $this->field,
      "headerTooltip" => $this->description,
      "width" => $this->width,
    ];
    if (is_null($this->viewModel)) return json_encode($commonPart);
    $tabulatorPart = json_decode("{$this->viewModel}");
    return json_encode(array_merge($commonPart, $tabulatorPart));
  }
}

class TableColumns{
  public static array $clickClmns = [
    "subid"=>new TableColumn("Subid", "subid", "Unique click ID",-1, <<<JSON
                {
                    "formatter": "link",
                    "formatterParams": {
                        "urlPrefix": "clicks.php?campId=$campId&filter=single&subid="
                    }
                }
JSON),
    //TODO: TIMEZONE!!!
    "time"=>new TableColumn("Time", "time", "Click time", -1, <<<JSON
                {
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
                }
JSON),
  ];
  
  public static array $statsClmns = [
        'preland' => [
            "title" => "Preland",
            "headerTooltip" => "Chosen prelanding",
            "field" => "preland",
            "headerFilter" => "input",
        ],
        'land' => [
            "title" => "Land",
            "headerTooltip" => "Chosen landing",
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
            "headerTooltip" => "Browser language",
            "field" => "lang",
            "headerFilter" => "input",
            "width" => "50",
        ],
        'isp' => [
            "title" => "ISP",
            "headerTooltip" => "Internet Service Provider",
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
            "headerTooltip" => "Operating System",
            "field" => "os",
            "headerFilter" => "input",
            "width" => "100",
        ],
        'clicks' => [
            "title" => "Clicks",
            "headerTooltip" => "Number of visitors",
            "field" => "clicks",
            "width"=>"90",
            "bottomCalc"=>"sum"
        ],
        'uniques' => [
            "title" => "Uniques",
            "headerTooltip" => "Number of unique visitors",
            "field" => "uniques",
            "width"=>"90",
            "bottomCalc"=>"sum"
        ],
        'uniques_ratio' => [
            "title" => "U/C",
            "headerTooltip" => "Unique visitors / visitors",
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
            "headerTooltip" => "Conversions",
            "field" => "conversion",
            "width" => "60",
            "bottomCalc"=>"sum"
        ],
        'purchase' => [
            "title" => "P",
            "headerTooltip" => "Purchases",
            "field" => "purchase",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'hold' => [
            "title" => "H",
            "headerTooltip" => "Holds",
            "field" => "hold",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'reject' => [
            "title" => "R",
            "headerTooltip" => "Rejects",
            "field" => "reject",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'trash' => [
            "title" => "T",
            "headerTooltip" => "Trashes",
            "field" => "trash",
            "width" => "50",
            "bottomCalc"=>"sum"
        ],
        'lpclicks' => [
            "title" => "LPClicks",
            "headerTooltip" => "Landing page visitors",
            "field" => "lpclicks",
            "width" => "70",
            "bottomCalc"=>"sum"
        ],
        'lpctr' => [
            "title" => "LPCTR",
            "headerTooltip" => "Landing page visitors percentage",
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
            "headerTooltip" => "Total conversion rate",
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
            "headerTooltip" => "Conversion into Sales rate",
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
            "headerTooltip" => "Approve rate without Trash conversions",
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
            "headerTooltip" => "Approve rate",
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
            "headerTooltip" => "Cost per click",
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
            "headerTooltip" => "Traffic costs",
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
            "headerTooltip" => "Earnings Per Click",
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
            "headerTooltip" => "Earnings Per Unique Click",
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
            "headerTooltip" => "Revenue",
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
            "headerTooltip" => "Profit",
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
            "headerTooltip" => "Return On Investment",
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
}
