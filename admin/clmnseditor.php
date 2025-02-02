<?php
require_once __DIR__ . '/password.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../logging.php';

$passOk = check_password(false);
if (!$passOk)
    return send_clmnseditor_result("Error: password check not passed!",true);

$action = $_REQUEST['action'];
$table = $_REQUEST['table']??'';
$campId = $_REQUEST['campid']??'';
add_log('trace', "ColumnsEditor action: $action, table: $table, campId: $campId");
switch($table){
    case 'trafficback':
        $table = 'trafficBack';
        break;
    default:
        $table = 'table';
        break;
}

$s = $db->get_common_settings();
$postData = file_get_contents('php://input');

switch ($action) {
    case 'width':
        $uc = json_decode($postData, true);
        update_width($s['statistics'][$table], $uc);
        break;
    case 'savecolumns':
        $data = json_decode($postData, true);
        if (!isset($data['columns'])) {
            return send_clmnseditor_result("Error: missing columns data", true);
        }
        
        $s['statistics'][$table] = 
            get_new_columns($s['statistics'][$table], $data['columns']);
        break;
    case 'trafficback':
        $s['trafficBackUrl'] = $postData;
        break;
    default:
        return send_clmnseditor_result("Error: wrong action!",true);
}

$res = $db->set_common_settings($s);
if ($res===false)
    return send_clmnseditor_result("Error saving settings!",true);
return send_clmnseditor_result("OK");

function send_clmnseditor_result($msg,$error=false): void
{
    $res = ["result" => $msg];
    if ($error){
        $res['error']=true;
    }
    header('Content-type: application/json');
    http_response_code(200);
    $json = json_encode($res);
    echo $json;
}


/**
 * Updates the width of the specified column in the specified table array
 * @param array $table
 * @param array $c
 * @return void
 */
function update_width(array &$table, array $c) {
    foreach ($table as &$tcolumn) {
        if ($tcolumn['field'] !== $c['field'])
            continue;
        $tcolumn['width'] = $c['width'];
        break;
    }
}


function get_new_columns($existingColumns, $newColumnNames): array
{
    $newColumns = [];

    // Process each column from the new data
    foreach ($newColumnNames as $cName) {
        $found = false;
        foreach ($existingColumns as $existingColumn) {
            if ($existingColumn['field'] === $cName) {
                $newColumns[] = $existingColumn;
                $found = true;
                break;
            }
        }
        // If column not found, add it with default width
        if (!$found) {
            $newColumns[] = ['field' => $cName, 'width' => -1];
        }
    }

    return $newColumns;
}