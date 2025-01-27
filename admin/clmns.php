<?php
class AvailableColumns
{
  public static $blockedColumns = [
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
    "params",
    "reason"
  ];

  public static $allowedColumns = [
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
    "params",
    "subid",
    "preland",
    "land",
    "lpclick",
    "status",
    "cost",
    "payout"
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
  public int $width;
  public string $viewModel;

  public function __construct($title, $field, $width, $viewModel) {
    $this->title = $title;
    $this->field = $field;
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
      "width" => $this->width,
    ];
    $tabulatorPart = json_decode("{$this->viewModel}");
    return json_encode(array_merge($commonPart, $tabulatorPart));
  }
}

public static class TableColumns{
  public static array $clmns = [
    "subid"=>new TableColumn("Subid", "subid", 100, <<<JSON
                {
                    "formatter": "link",
                    "formatterParams": {
                        "urlPrefix": "clicks.php?campId=$campId&filter=single&subid="
                    }
                }
JSON)
"),

  ];
}

class StatsTable{
  public array $tableColumns;

}