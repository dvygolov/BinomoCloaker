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