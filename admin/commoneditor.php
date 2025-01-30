<?php
require_once __DIR__ . '/password.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../logging.php';

$passOk = check_password(false);
if (!$passOk)
    return send_common_result("Error: password check not passed!",true);

$action = $_REQUEST['action'];
$table = $_REQUEST['table']??'';
add_log('trace','CommonEditor action: '.$action.', table: '.$table);
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
        $updatedColumn = json_decode($postData, true);
        // Update widths
        foreach ($s['statistics'][$table] as &$column) {
            if ($column['field'] !== $updatedColumn['field'])
                continue;
            $column['width'] = $updatedColumn['width'];
            break;
        }
        break;
    case 'savecolumns':
        $data = json_decode($postData, true);
        if (!isset($data['columns'])) {
            return send_common_result("Error: missing columns data", true);
        }
        
        $s = $db->get_common_settings();
        $newColumns = [];
        
        // Process each column from the new data
        foreach ($data['columns'] as $columnName) {
            $found = false;
            foreach ($s['statistics'][$table] as $existingColumn) {
                if ($existingColumn['field'] === $columnName) {
                    $newColumns[] = $existingColumn;
                    $found = true;
                    break;
                }
            }
            // If column not found, add it with default width
            if (!$found) {
                $newColumns[] = ['field' => $columnName, 'width' => -1];
            }
        }
        $s['statistics'][$table] = $newColumns;
        break;
    case 'trafficback':
        $s['trafficBackUrl'] = $postData;
        break;
    default:
        return send_common_result("Error: wrong action!",true);
}

$res = $db->set_common_settings($s);
if ($res===false)
    return send_common_result("Error saving settings!",true);
return send_common_result("OK");

function send_common_result($msg,$error=false): void
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