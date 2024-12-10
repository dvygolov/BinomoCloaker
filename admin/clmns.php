<?php
class AvailbleColumns
{
  
  public $blockedColumns = [
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
  
  public $allowedColumns = [
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
  
  public $trafficbackColumns = [
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
  
  public $groupByColumns = [
    "date",
    "preland",
    "land",
    "isp",
    "country",
    "lang",
    "os"
  ];

  public $statsColumns = [
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
}
?>