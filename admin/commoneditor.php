<?php
require_once __DIR__ . '/password.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../db/db.php';

$passOk = check_password(false);
if (!$passOk)
    return send_common_result("Error: password check not passed!",true);

$action = $_REQUEST['action'];

switch ($action) {
    case 'width':
        $updatedColumn = json_decode(file_get_contents('php://input'), true);
        $s = $db->get_common_settings();
        // Update widths
        foreach ($s['statistics']['table'] as &$column) {
            if ($column['field'] !== $updatedColumn['field'])
                continue;
            $column['width'] = $updatedColumn['width'];
            break;
        }
        $db->set_common_settings($s);
        break;
    case 'savecolumns':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['columns'])) {
            return send_common_result("Error: missing columns data", true);
        }
        
        $s = $db->get_common_settings();
        $newColumns = [];
        
        // Process each column from the new data
        foreach ($data['columns'] as $columnName) {
            $found = false;
            // Check if column exists in current settings
            if (isset($s['statistics']['table'])) {
                foreach ($s['statistics']['table'] as $existingColumn) {
                    if ($existingColumn['field'] === $columnName) {
                        $newColumns[] = $existingColumn;
                        $found = true;
                        break;
                    }
                }
            }
            // If column not found, add it with default width
            if (!$found) {
                $newColumns[] = ['field' => $columnName, 'width' => -1];
            }
        }
        
        $s['statistics']['table'] = $newColumns;
        
        $db->set_common_settings($s);
        break;
    case 'trafficback':
        $tbUrl = file_get_contents('php://input');
        $s = $db->get_common_settings();
        $s['trafficBackUrl'] = $tbUrl;
        $db->set_common_settings($s);
        break;
    default:
        return send_common_result("Error: wrong action!",true);
}
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