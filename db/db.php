<?php
require_once __DIR__ . "/../cookies.php";
require_once __DIR__ . "/../logging.php";
require_once __DIR__ . "/../settings.php";
require_once __DIR__ . "/../requestfunc.php";

class Db
{
    private $dbPath;

    public function __construct()
    {
        global $cloSettings;
        $this->dbPath = __DIR__ . '/' . $cloSettings['dbConnection'];
        if (!file_exists($this->dbPath)) {
            $this->create_new_db();
        }
    }

    public function get_trafficback_clicks($startdate, $enddate): array
    {
        $query = "SELECT * FROM trafficback WHERE time BETWEEN :startDate AND :endDate ORDER BY time DESC";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':startDate', $startdate, SQLITE3_INTEGER);
            $stmt->bindValue(':endDate', $enddate, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $trafficbackClicks = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['params'])) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params'] === null && json_last_error() !== JSON_ERROR_NONE) {
                        add_log("errors", "Failed to parse trafficback params JSON for row " . $row['id'] . ": " . json_last_error_msg());
                        $row['params'] = [];
                    }
                }
                $trafficbackClicks[] = $row;
            }

            add_log("trace", "Retrieved " . count($trafficbackClicks) . " trafficback clicks from $startdate to $enddate");
            return $trafficbackClicks;
        } catch (Exception $e) {
            add_log("errors", "Failed to get trafficback clicks: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }


    public function get_white_clicks($startdate, $enddate, $campId): array
    {
        // Prepare SQL query to select blocked clicks within the date range
        $query = "SELECT * FROM blocked WHERE time BETWEEN :startDate AND :endDate AND campaign_id = :campid ORDER BY time DESC";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters to the prepared statement
            $stmt->bindValue(':startDate', $startdate, SQLITE3_INTEGER);
            $stmt->bindValue(':endDate', $enddate, SQLITE3_INTEGER);
            $stmt->bindValue(':campid', $campId, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Initialize an array to hold the results
            $blockedClicks = [];
            // Fetch each row and add it to the array
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['params'])) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params'] === null && json_last_error() !== JSON_ERROR_NONE) {
                        add_log("errors", "Failed to parse blocked click params JSON for row " . $row['id'] . ": " . json_last_error_msg());
                        $row['params'] = [];
                    }
                }
                $blockedClicks[] = $row;
            }

            add_log("trace", "Retrieved " . count($blockedClicks) . " blocked clicks for campaign $campId from $startdate to $enddate");
            // Return the array of blocked clicks
            return $blockedClicks;
        } catch (Exception $e) {
            add_log("errors", "Failed to get blocked clicks for campaign $campId: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_black_clicks($startdate, $enddate, $campId): array
    {
        // Prepare SQL query to select blocked clicks within the date range
        $query = "SELECT * FROM clicks WHERE time BETWEEN :startDate AND :endDate AND campaign_id = :campid ORDER BY time DESC";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters to the prepared statement
            $stmt->bindValue(':startDate', $startdate, SQLITE3_INTEGER);
            $stmt->bindValue(':endDate', $enddate, SQLITE3_INTEGER);
            $stmt->bindValue(':campid', $campId, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Initialize an array to hold the results
            $clicks = [];
            // Fetch each row and add it to the array
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['params'])) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params'] === null && json_last_error() !== JSON_ERROR_NONE) {
                        add_log("errors", "Failed to parse click params JSON for row " . $row['id'] . ": " . json_last_error_msg());
                        $row['params'] = [];
                    }
                }
                $clicks[] = $row;
            }

            add_log("trace", "Retrieved " . count($clicks) . " clicks for campaign $campId from $startdate to $enddate");
            return $clicks;
        } catch (Exception $e) {
            add_log("errors", "Get Black Clicks: $startdate $enddate $campId " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_clicks_by_subid($subid, bool $firstOnly = false): array
    {
        if (empty($subid)) {
            add_log("trace", "Skipping clicks retrieval - empty subid provided");
            return [];
        }

        $query = "SELECT * FROM clicks WHERE subid = :subid ORDER BY time DESC";
        if ($firstOnly) {
            $query .= " LIMIT 1";
        }

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':subid', $subid, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $clicks = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['params'])) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params'] === null && json_last_error() !== JSON_ERROR_NONE) {
                        add_log("errors", "Failed to parse click params JSON for row " . $row['id'] . ": " . json_last_error_msg());
                        $row['params'] = [];
                    }
                }
                $clicks[] = $row;
            }

            $limit_msg = $firstOnly ? " (limited to 1)" : "";
            add_log("trace", "Retrieved " . count($clicks) . " clicks for subid $subid" . $limit_msg);
            return $clicks;
        } catch (Exception $e) {
            add_log("errors", "Failed to get clicks for subid $subid: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_leads($startdate, $enddate, $campId): array
    {
        // Prepare SQL query to select leads within the date range and configuration
        $query = "SELECT * FROM clicks WHERE time BETWEEN :startDate AND :endDate AND campaign_id = :campid AND status IS NOT NULL ORDER BY time DESC";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters to the prepared statement
            $stmt->bindValue(':startDate', $startdate, SQLITE3_INTEGER);
            $stmt->bindValue(':endDate', $enddate, SQLITE3_INTEGER);
            $stmt->bindValue(':campid', $campId, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Initialize an array to hold the results
            $leads = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['params'])) {
                    $row['params'] = json_decode($row['params'], true);
                    if ($row['params'] === null && json_last_error() !== JSON_ERROR_NONE) {
                        add_log("errors", "Failed to parse lead params JSON for row " . $row['id'] . ": " . json_last_error_msg());
                        $row['params'] = [];
                    }
                }
                $leads[] = $row;
            }

            add_log("trace", "Retrieved " . count($leads) . " leads for campaign $campId from $startdate to $enddate");
            return $leads;
        } catch (Exception $e) {
            add_log("errors", "Failed to get leads for campaign $campId: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    private function get_stats_select_parts(array $selectedFields): array
    {
        $selectParts = [];
        // Process selected fields
        foreach ($selectedFields as $field) {
            switch ($field) {
                case 'clicks':
                    $selectParts[] = "COUNT(c.id) AS clicks";
                    break;
                case 'uniques':
                    $selectParts[] = "COUNT(DISTINCT subid) AS uniques";
                    break;
                case 'uniques_ratio':
                    $selectParts[] = "(COUNT(DISTINCT subid)*1.0/COUNT(*) * 100.0) AS uniques_ratio";
                    break;
                case 'conversion':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN subid END) AS conversion";
                    break;
                case 'purchase':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN subid END) AS purchase";
                    break;
                case 'hold':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN status = 'Lead' THEN subid END) AS hold";
                    break;
                case 'reject':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN status = 'Reject' THEN subid END) AS reject";
                    break;
                case 'trash':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN status = 'Trash' THEN subid END) AS trash";
                    break;
                case 'lpclicks':
                    $selectParts[] = "COUNT(DISTINCT CASE WHEN lpclick = 1 THEN c.id END) AS lpclicks";
                    break;
                case 'lpctr':
                    $selectParts[] = "(COUNT(DISTINCT CASE WHEN lpclick = 1 THEN c.id END) * 100.0 / COUNT(*)) AS lpctr";
                    break;
                case 'cra':
                    $selectParts[] = "(COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN c.id END) * 100.0 / COUNT(*)) AS cra";
                    break;
                case 'crs':
                    $selectParts[] = "(COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN c.id END) * 100.0 / COUNT(*)) AS crs";
                    break;
                case 'appt':
                    $selectParts[] = "CASE
                            WHEN COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN c.id END) = 0
                                 OR (COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN c.id END) - COUNT(DISTINCT CASE WHEN status = 'Trash' THEN c.id END)) = 0
                            THEN 0
                            ELSE (COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN c.id END) * 100.0 / (COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN c.id END) - COUNT(DISTINCT CASE WHEN status = 'Trash' THEN c.id END)))
                       END AS appt";
                    break;
                case 'app':
                    $selectParts[] = "CASE
                            WHEN COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN c.id END) = 0
                                 OR COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN c.id END) = 0
                            THEN 0
                            ELSE (COUNT(DISTINCT CASE WHEN status = 'Purchase' THEN c.id END) * 100.0 / COUNT(DISTINCT CASE WHEN status IS NOT NULL THEN c.id END))
                       END AS app";
                    break;
                case 'cpc':
                    $selectParts[] = "(SUM(cost) * 1.0 / COUNT(c.id)) AS cpc";
                    break;
                case 'costs':
                    $selectParts[] = "SUM(cost) AS costs";
                    break;
                case 'epc':
                    $selectParts[] = "(SUM(payout) * 1.0 / COUNT(c.id)) AS epc";
                    break;
                case 'epuc':
                    $selectParts[] = "(SUM(payout) * 1.0 / COUNT(DISTINCT(subid))) AS epuc";
                    break;
                case 'revenue':
                    $selectParts[] = "SUM(payout) AS revenue";
                    break;
                case 'profit':
                    $selectParts[] = "(SUM(payout) - SUM(cost)) as profit";
                    break;
                case 'roi':
                    $selectParts[] = "((SUM(payout) - SUM(cost))*1.0 / SUM(cost) * 100.0) as roi";
                    break;
            }
        }
        return $selectParts;
    }

    public function get_statistics(
    $selectedFields,
    $groupByFields,
    $campId,
    $startDate,
    $endDate,
    $timezone
    ) {
        $baseQuery =
        "SELECT %s FROM clicks c WHERE campaign_id = :campid AND time BETWEEN :startDate AND :endDate";
        $selectParts = [];
        $groupByParts = [];
        $orderByParts = [];

        $selectParts = $this->get_stats_select_parts($selectedFields);

        // Process group by fields
        foreach ($groupByFields as $field) {
            if ($field === 'date') {

                $dateTime = new DateTime('now', new DateTimeZone($timezone));
                // Get the offset in seconds from UTC
                $offsetInSeconds = $dateTime->getOffset();
                // Convert this offset to an SQLite compatible format (HH:MM)
                $hours = floor($offsetInSeconds / 3600);
                $minutes = floor(($offsetInSeconds % 3600) / 60);
                $offsetFormatted = sprintf('%+03d:%02d', $hours, $minutes);

                $selectParts[] =
                "strftime('%Y-%m-%d', datetime(time, 'unixepoch', '{$offsetFormatted}')) AS date";
                $groupByParts[] = "date";
                $orderByParts[] = "date";
            } elseif (in_array($field, ['preland', 'land', 'isp', 'country', 'lang', 'os'])) {
                $selectParts[] = $field;
                $groupByParts[] = $field;
                $orderByParts[] = $field;
            } else {
                // JSON fields
                $jsonExtract = "COALESCE(json_extract(params, '$." . $field . "'), 'unknown') AS " . $field;
                $selectParts[] = $jsonExtract;
                $groupByParts[] = $field;
                $orderByParts[] = $field;
            }
        }

        // Construct the SQL query
        $selectClause = implode(', ', $selectParts);
        $groupByClause = !empty($groupByParts) ? "GROUP BY " . implode(', ', $groupByParts) : '';
        $orderByClause = !empty($orderByParts) ? "ORDER BY " . implode(', ', $orderByParts) : '';
        $sqlQuery = sprintf($baseQuery, $selectClause) . " " . $groupByClause . " " . $orderByClause;

        $db = $this->open_db(true);
        // Prepare and execute the query
        $stmt = $db->prepare($sqlQuery);
        if ($stmt === false) {
            // Prepare failed, get and display the error message
            $errorMessage = $db->lastErrorMsg();
            add_log("errors", "Error preparing statistics statement: $errorMessage");
            $db->close();
            return [];
        }
        $stmt->bindValue(':campid', $campId, SQLITE3_INTEGER);
        $stmt->bindValue(':startDate', $startDate, SQLITE3_INTEGER);
        $stmt->bindValue(':endDate', $endDate, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result === false) {
            // Prepare failed, get and display the error message
            $errorMessage = $db->lastErrorMsg();
            add_log("errors", "Error executing statistics statement: $errorMessage");
            $db->close();
            return [];
        }

        $treeData = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->add_row($treeData, $row, $selectedFields, $groupByFields);
        }

        //$this->countTotals($treeData, $newGroupIndex, $selectedFields, $groupByFields);
        $db->close();
        return $treeData;
    }
    //TODO: totals correct counting
    private function add_row(&$treeData, $row, $columns, $groupBy)
    {
        $children = &$treeData;
        $i = 0;
        while ($i < count($groupBy)) {
            $curGroup = $groupBy[$i];
            $lastChild = count($children) === 0 ? null : $children[count($children) - 1];
            //if row will be the first child or
            //if new row has group value that differs from the last child's group value
            if ($lastChild === null || $lastChild['group'] !== $row[$curGroup]) {

                if (count($children) !== 0) {
                    //count totals for previous levels
                    $j = $i;
                    $totChildren = &$children;
                    $totParents = [];
                    while ($j < count($groupBy) - 1) {
                        $parent = &$totChildren[count($totChildren) - 1];
                        $totChildren = &$parent['_children'];
                        $totParents[] = &$parent;
                        $j++;
                    }
                    while ($j > $i) {
                        $parent = array_pop($totParents);
                        $totals = $this->count_totals($totChildren, $columns);
                        $parent = array_merge($parent, $totals);
                        $j--;
                    }
                }

                $children[] = ['group' => $row[$curGroup], '_children' => []];
                $lastChild = &$children[count($children) - 1];

                unset($row[$curGroup]); //current group became a new level, we should remove it from row

                //if we are at the last group by level - we need to add all the row data here
                if ($i === count($groupBy) - 1) {
                    unset($lastChild['_children']); //child-free node! it will have 'group' only
                    $lastChild = array_merge($lastChild, $row);
                }
            }
            $children = &$lastChild['_children'];
            $i++;
        }
    }
    private function count_totals(array $children, array $columns): array
    {
        // If we have only one child row
        if (count($children) === 1) {
            // Filter the array to include only keys present in the $columns array
            $filtered = array_intersect_key($children[0], array_flip($columns));
            return $filtered;
        }

        //if we have many children - sum their values
        $sumArray = [];
        foreach ($children as $child) {
            // Iterate over each key-value pair in the current array
            foreach ($child as $key => $value) {
                if (
                in_array($key, [
                '_children',
                'group',
                'uniques_ratio',
                'lpctr',
                'cra',
                'crs',
                'appt',
                'app',
                'cpc',
                'epc',
                'epuc'
                ])
                )
                    continue;
                if (!isset($sumArray[$key])) {
                    $sumArray[$key] = 0;
                }
                $sumArray[$key] += $value;
            }
        }

        if (in_array('uniques_ratio', $columns))
            $sumArray['uniques_ratio'] =
            $sumArray['clicks'] === 0 ? 0 : $sumArray['uniques'] * 1.0 / $sumArray['clicks'] * 100;
        if (in_array('lpctr', $columns))
            $sumArray['lpctr'] =
            $sumArray['clicks'] === 0 ? 0 : $sumArray['lpclicks'] * 1.0 / $sumArray['clicks'] * 100.0;
        if (in_array('cra', $columns))
            $sumArray['cra'] =
            $sumArray['clicks'] === 0 ? 0 : $sumArray['conversion'] * 1.0 / $sumArray['clicks'] * 100.0;
        if (in_array('crs', $columns))
            $sumArray['crs'] =
            $sumArray['clicks'] === 0 ? 0 : $sumArray['purchase'] * 1.0 / $sumArray['clicks'] * 100.0;
        if (in_array('appt', $columns))
            $sumArray['appt'] = $sumArray['conversion'] - $sumArray['trash'] === 0 ? 0 :
            $sumArray['purchase'] * 1.0 / ($sumArray['conversion'] - $sumArray['trash']) * 100.0;
        if (in_array('app', $columns))
            $sumArray['app'] =
            $sumArray['conversion'] === 0 ? 0 : $sumArray['purchase'] * 1.0 / $sumArray['conversion'] * 100.0;

        if (in_array('cpc', $columns))
            $sumArray['cpc'] = $sumArray['clicks'] === 0 ? 0 : $sumArray['costs'] * 1.0 / $sumArray['clicks'];
        if (in_array('epc', $columns))
            $sumArray['epc'] = $sumArray['clicks'] === 0 ? 0 : $sumArray['revenue'] * 1.0 / $sumArray['clicks'];
        if (in_array('epuc', $columns))
            $sumArray['epuc'] = $sumArray['uniques'] === 0 ? 0 : $sumArray['revenue'] * 1.0 / $sumArray['uniques'] * 100;

        return $sumArray;
    }

    public function add_trafficback_click($data): bool
    {
        // Prepare click data
        $click = $this->prepare_click_data($data);

        // Prepare SQL insert statement
        $query = "INSERT INTO trafficback (time, ip, country, lang, os, osver, brand, model, isp, client, clientver, ua, params) VALUES (:time, :ip, :country, :lang, :os, :osver, :brand, :model, :isp, :client, :clientver, :ua, :params)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters
            foreach ($click as $key => $value) {
                if (!isset($value)) {
                    add_log("warning", "Null value found for field '$key' in trafficback click data");
                    $value = '';
                }
                $stmt->bindValue(':' . $key, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            add_log("trace", "Successfully added trafficback click for IP: " . ($click['ip'] ?? 'unknown'));
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to add trafficback click: " . $e->getMessage() . ", Data: " . json_encode($click));
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function add_white_click($data, $reason, $campId): bool
    {
        // Prepare click data
        $click = $this->prepare_click_data($data, $campId);
        $click['reason'] = $reason;

        // Prepare SQL insert statement
        $query = "INSERT INTO blocked (campaign_id, time, ip, country, lang, os, osver, brand, model, isp, client, clientver, ua, reason, params) VALUES (:campaign_id, :time, :ip, :country, :lang, :os, :osver, :brand, :model, :isp, :client, :clientver, :ua, :reason, :params)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters
            foreach ($click as $key => $value) {
                if (!isset($value)) {
                    add_log("warning", "Null value found for field '$key' in white click data");
                    $value = '';
                }
                $stmt->bindValue(':' . $key, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            add_log("trace", "Successfully added white click for IP: " . ($click['ip'] ?? 'unknown') . ", Campaign: $campId, Reason: $reason");
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to add white click: " . $e->getMessage() . ", Data: " . json_encode($click));
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function add_black_click($subid, $data, $preland, $land, $campId): bool
    {
        // Prepare click data with the provided data and configuration
        $click = $this->prepare_click_data($data, $campId);
        $click['subid'] = $subid;
        $click['preland'] = empty($preland) ? 'unknown' : $preland;
        $click['land'] = empty($land) ? 'unknown' : $land;

        // Prepare the SQL INSERT statement for the 'clicks' table
        $query = "INSERT INTO clicks (campaign_id, time, ip, country, lang, os, osver, client, clientver, device, brand, model, isp, ua, subid, preland, land, params, cost, lpclick, status) VALUES (:campaign_id, :time, :ip, :country, :lang, :os, :osver, :client, :clientver, :device, :brand, :model, :isp, :ua, :subid, :preland, :land, :params, :cpc, 0, NULL)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Bind parameters from the click array
            foreach ($click as $key => $value) {
                if (!isset($value)) {
                    add_log("warning", "Null value found for field '$key' in black click data");
                    $value = '';
                }
                $stmt->bindValue(':' . $key, $value);
            }

            // Manually bind the lpclick and status parameters
            $stmt->bindValue(':lpclick', 0, SQLITE3_INTEGER);
            $stmt->bindValue(':status', null, SQLITE3_NULL);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            add_log("trace", "Successfully added black click for IP: " . ($click['ip'] ?? 'unknown') . ", Campaign: $campId, SubID: $subid");
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to add black click: " . $e->getMessage() . ", Data: " . json_encode($click));
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function add_lead($subid, $name, $phone, $status = 'Lead'): bool
    {
        if (empty($subid)) {
            add_log("warning", "Skipping lead addition - empty subid provided");
            return false;
        }

        $updateQuery = "UPDATE clicks SET status = :status, name = :name, phone = :phone WHERE id = (SELECT id FROM clicks WHERE subid = :subid ORDER BY time DESC LIMIT 1)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($updateQuery);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Sanitize and validate input
            $name = trim($name);
            $phone = trim($phone);
            if (empty($name) || empty($phone)) {
                throw new Exception("Name or phone number cannot be empty");
            }

            // Bind parameters with proper type checking
            $stmt->bindValue(':subid', $subid, SQLITE3_TEXT);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Check if any rows were affected
            if ($db->changes() === 0) {
                add_log("warning", "No click found for subid: $subid when adding lead");
                return false;
            }

            add_log("trace", "Successfully added lead for subid: $subid, status: $status");
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to add lead: " . $e->getMessage() . ", Data: subid=$subid, name=$name, phone=$phone, status=$status");
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function update_status($subid, $status, $payout): bool
    {
        if (empty($subid)) {
            add_log("warning", "Skipping status update - empty subid provided");
            return false;
        }

        if (!$this->subid_exists($subid)) {
            add_log("warning", "Skipping status update - subid not found: $subid");
            return false;
        }

        $updateQuery = "UPDATE clicks SET status = :status, payout = :payout WHERE id = (SELECT id FROM clicks WHERE subid = :subid ORDER BY time DESC LIMIT 1)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($updateQuery);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Validate and bind parameters
            if (!is_numeric($payout)) {
                throw new Exception("Invalid payout value: $payout");
            }

            $stmt->bindValue(':subid', $subid, SQLITE3_TEXT);
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':payout', floatval($payout), SQLITE3_FLOAT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Check if any rows were affected
            if ($db->changes() === 0) {
                add_log("warning", "No click found for subid: $subid when updating status");
                return false;
            }

            add_log("trace", "Successfully updated status for subid: $subid, new status: $status, payout: $payout");
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to update status: " . $e->getMessage() . ", Data: subid=$subid, status=$status, payout=$payout");
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function add_lpctr($subid): bool
    {
        if (empty($subid)) {
            add_log("warning", "Skipping lpctr update - empty subid provided");
            return false;
        }

        if (!$this->subid_exists($subid)) {
            add_log("warning", "Skipping lpctr update - subid not found: $subid");
            return false;
        }

        $updateQuery = "UPDATE clicks SET lpclick = 1 WHERE id = (SELECT id FROM clicks WHERE subid = :subid ORDER BY time DESC LIMIT 1)";

        $db = null;
        try {
            $db = $this->open_db();
            $stmt = $db->prepare($updateQuery);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':subid', $subid, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Check if any rows were affected
            if ($db->changes() === 0) {
                add_log("warning", "No click found for subid: $subid when updating lpctr");
                return false;
            }

            add_log("trace", "Successfully updated lpctr for subid: $subid");
            return true;
        } catch (Exception $e) {
            add_log("errors", "Failed to update lpctr: " . $e->getMessage() . ", subid: $subid");
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    private function subid_exists($subid): bool
    {
        if (empty($subid)) {
            add_log("warning", "Empty subid provided for existence check");
            return false;
        }

        $db = null;
        try {
            $db = $this->open_db(true); // Read-only connection since we're just checking
            $stmt = $db->prepare('SELECT COUNT(*) AS count FROM clicks WHERE subid = :subid');

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':subid', $subid, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($row === false) {
                throw new Exception("Failed to fetch result row");
            }

            $exists = ($row['count'] > 0);
            add_log("trace", "Subid existence check - subid: $subid, exists: " . ($exists ? "yes" : "no"));
            return $exists;
        } catch (Exception $e) {
            add_log("errors", "Failed to check subid existence: " . $e->getMessage() . ", subid: $subid");
            return false; // Safer to return false than die() on error
        } finally {
            if (isset($db)) $db->close();
        }
    }

    private function prepare_click_data($data, $campId=null): array
    {
        $data["time"] = (new DateTime())->getTimestamp();
        if (!is_null($campId)) $data["campaign_id"] = $campId;

        $query = [];
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $query);
        }

        if (array_key_exists("cpc", $query)) {
            $data["cpc"] = $query["cpc"];
            unset($query["cpc"]);
        }

        $data["params"] = json_encode($query);
        return $data;
    }

    public function add_campaign($name): bool|int
    {
        $query = "INSERT INTO campaigns (name, settings) VALUES (:name, :settings)";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            $settingsJson = file_get_contents(__DIR__ . '/default.json');

            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':settings', $settingsJson, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) 
                throw new Exception($db->lastErrorMsg());
            
            $db->exec('COMMIT');
            $newCampaignId = $db->lastInsertRowID();
            add_log("trace", "Added new campaign $name: " . $newCampaignId);
            return $newCampaignId;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't add campaign $name: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function clone_campaign($id): bool|int
    {
        // SQL query to clone campaign using a single command
        $query = "INSERT INTO campaigns (name, settings)
                  SELECT name || ' (Clone)', settings FROM campaigns WHERE id = :id";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            // Bind the original campaign ID to the query
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            $newCampaignId = $db->lastInsertRowID();
            add_log("trace", "Cloned campaign $id: new id = $newCampaignId");
            return $newCampaignId;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't clone campaign $id: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_campaign_settings($id):array
    {
        $query = "SELECT settings FROM campaigns WHERE id = :id";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $arr = $result->fetchArray(SQLITE3_ASSOC);
            $settings = json_decode($arr['settings'], true);

            return $settings;
        } catch (Exception $e) {
            add_log("errors", "Couldn't fetch campaign $id: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_campaign_by_currentpath(): array|bool
    {
        $domain = get_cloaker_path(false, false);
        $query = "SELECT * FROM campaigns";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);
            
            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (empty($row['settings']))
                    continue;
                $settings = json_decode($row['settings'], true);
                if (!isset($settings['domains']))
                    continue;
                if (!$this->match_domain($settings['domains'], $domain))
                    continue;

                $row['settings'] = json_decode($row['settings'], true);
                add_log("trace", "Found matching campaign for domain $domain: " . $row['id']);
                return $row;
            }

            add_log("trace", "No matching campaign found for domain $domain");
            return false;
        } catch (Exception $e) {
            add_log("errors", "Couldn't fetch campaigns for domain $domain: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    private function match_domain($domains, $domainToMatch): bool
    {
        foreach ($domains as $domain) {
            if ($domain === $domainToMatch) {
                return true;
            } elseif (strpos($domain, '*') !== false) {
                // Convert wildcard domain to a regex pattern
                $pattern = str_replace('.', '\.', $domain);
                $pattern = str_replace('*', '.*', $pattern);
                if (preg_match('/^' . $pattern . '$/', $domainToMatch)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function rename_campaign($id, $name): bool
    {
        $query = "UPDATE campaigns SET name = :name WHERE id = :id";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            add_log("trace", "Renamed campaign $id to: $name");
            return true;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't rename campaign $id to $name: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function save_campaign_settings(int $id, array $settings): bool
    {
        $query = "UPDATE campaigns SET settings = :settings WHERE id = :id";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $settingsJson = json_encode($settings);

            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':settings', $settingsJson, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            add_log("trace", "Saved settings for campaign $id");
            return true;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't save campaign's $id settings: " . $e->getMessage() . ", $settingsJson");
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }


    public function delete_campaign($id): bool
    {
        $query = "DELETE FROM campaigns WHERE id = :id";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            add_log("trace", "Deleted campaign $id");
            return true;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't delete campaign $id: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_campaigns($startDate, $endDate, array $selectFields): array
    {
        $query = "
        SELECT cmp.id, cmp.name, %s
        FROM campaigns cmp
        LEFT JOIN clicks c ON c.campaign_id=cmp.id AND c.time BETWEEN :startDate AND :endDate
        GROUP BY cmp.id";

        $selectClause = implode(',', $this->get_stats_select_parts($selectFields));
        $query = sprintf($query, $selectClause);

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':startDate', $startDate, SQLITE3_INTEGER);
            $stmt->bindValue(':endDate', $endDate, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $campaigns = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if (!empty($row['settings'])) {
                    $row['settings'] = json_decode($row['settings'], true);
                }
                $campaigns[] = $row;
            }

            add_log("trace", "Fetched " . count($campaigns) . " campaigns from $startDate to $endDate");
            return $campaigns;
        } catch (Exception $e) {
            add_log("errors", "Couldn't fetch campaigns: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function get_common_settings(): array
    {
        $query = "SELECT settings FROM common";

        $db = null;
        try {
            $db = $this->open_db(true);
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $row = $result->fetchArray(SQLITE3_ASSOC);
            if ($row === false) {
                throw new Exception("No common settings found");
            }

            $settings = json_decode($row['settings'], true);
            if ($settings === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Failed to parse common settings JSON: " . json_last_error_msg());
            }

            add_log("trace", "Successfully retrieved common settings");
            return $settings;
        } catch (Exception $e) {
            add_log("errors", "Couldn't get common settings: " . $e->getMessage());
            return [];
        } finally {
            if (isset($db)) $db->close();
        }
    }

    public function set_common_settings(array $s): bool
    {
        $query = "UPDATE common SET settings=:settings";

        $db = null;
        try {
            $db = $this->open_db();
            $db->exec('BEGIN IMMEDIATE');
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $settingsJson = json_encode($s);
            if ($settingsJson === false) {
                throw new Exception("Failed to encode settings to JSON: " . json_last_error_msg());
            }

            $stmt->bindValue(':settings', $settingsJson, SQLITE3_TEXT);

            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            add_log("trace", "Successfully updated common settings");
            return true;
        } catch (Exception $e) {
            if (isset($db)) $db->exec('ROLLBACK');
            add_log("errors", "Couldn't update common settings: " . $e->getMessage());
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }

    private function open_db(bool $readOnly = false): SQLite3
    {
        $db = new SQLite3($this->dbPath, $readOnly ? SQLITE3_OPEN_READONLY : SQLITE3_OPEN_READWRITE);
        $db->busyTimeout(5000);
        return $db;
    }

    private function create_new_db(): bool
    {
        $db = null;
        try {
            // Read SQL schema and initial settings
            $createTableSQL = @file_get_contents(__DIR__ . "/db.sql");
            if ($createTableSQL === false) {
                throw new Exception("Failed to read database schema file");
            }

            $settingsJson = @file_get_contents(__DIR__ . '/common.json');
            if ($settingsJson === false) {
                throw new Exception("Failed to read common settings file");
            }

            // Initialize database
            $db = new SQLite3($this->dbPath, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
            $db->busyTimeout(5000);
            
            $db->exec('BEGIN IMMEDIATE');

            // Create tables
            $result = $db->exec($createTableSQL);
            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            // Insert initial settings
            $query = "INSERT INTO common (settings) VALUES (:settings)";
            $stmt = $db->prepare($query);

            if ($stmt === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $stmt->bindValue(':settings', $settingsJson, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result === false) {
                throw new Exception($db->lastErrorMsg());
            }

            $db->exec('COMMIT');
            add_log("trace", "Successfully initialized database with schema and common settings");
            return true;
        } catch (Exception $e) {
            if (isset($db)) {
                $db->exec('ROLLBACK');
                add_log("errors", "Failed to initialize database: " . $e->getMessage());
            } else {
                die("Critical error initializing database: " . $e->getMessage());
            }
            return false;
        } finally {
            if (isset($db)) $db->close();
        }
    }
}

$db = new Db();