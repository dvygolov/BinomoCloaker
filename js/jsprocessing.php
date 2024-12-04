<?php

require_once __DIR__ . '/../campaign.php';
require_once __DIR__ . '/../debug.php';
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/../macros.php';
require_once __DIR__ . '/../settings.php';
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../main.php';
require_once __DIR__ . '/../requestfunc.php';

global $db;
$dbCamp = $db->get_campaign_by_currentpath();
if ($dbCamp===null){
    $cs = $db->get_common_settings();
    $c = new Cloaker();
    $db->add_trafficback_click($c->click_params);
    if (empty($cs['trafficBackUrl']))
        die("NO CAMPAIGN FOR THIS DOMAIN AND TRAFFICBACK NOT SET!");
    else{
        $mp = new MacrosProcessor();
        $url = urldecode($cs['trafficBackUrl']);
        $url = $mp->replace_url_macros($url);
        header("Access-Control-Expose-Headers: YWBAction", false, 200);
        header("Access-Control-Expose-Headers: YWBLocation", false, 200);
        header("YWBAction: redirect", true, 200);
        header("YWBLocation: " . $url, true, 200);
        return http_response_code(200);
        exit();
    }
}

$c = new Campaign($dbCamp['id'],$dbCamp['settings']);
$cloaker = new Cloaker($c->filters);
//Проверяем зашедшего пользователя
$is_bad_click = $cloaker->is_bad_click();

//Добавляем, по какому из js-событий пользователь прошёл сюда
if (isset($_GET['reason']))
    $cloaker->block_reason[] = $_GET['reason'];

send_access_control_headers();

if ($is_bad_click) {
    //это бот, который прошёл javascript-проверку
    $db->add_white_click($cloaker->click_params, $cloaker->block_reason, $c->campaignId);
    header("Access-Control-Expose-Headers: YWBAction", false, 200);
    header("YWBAction: none", true, 200);
    return http_response_code(200);
} else { //Обычный юзверь
    if ($c->black->jsconnectAction === 'redirect') { //если в настройках JS-подключения у нас редирект
        $url = rtrim(get_cloaker_path(), '/');
        $from = rtrim(strtok($_GET['uri'], '?'), '/');
        //если у нас одинаковый адрес, откуда мы вызываем скрипт и наш собственный
        //значит у нас просто включена JS-проверка и нам не нужно опять редиректить
        if ($url !== $from) {
            header("Access-Control-Expose-Headers: YWBAction", false, 200);
            header("Access-Control-Expose-Headers: YWBLocation", false, 200);
            header("YWBAction: redirect", true, 200);
            header("YWBLocation: " . $url, true, 200);
            return http_response_code(200);
        }
    }
    //если в настройках JS-подключения у нас подмена или iframe
    header("Access-Control-Expose-Headers: YWBAction", false, 200);
    header("YWBAction: " . $c->black->jsconnectAction, true, 200);
    black($cloaker->click_params);

    if (!headers_sent()) {
        //если в настройках кло для блэка стоит редирект, то для js xhr запроса надо его модифицировать
        $all_headers = implode(',', headers_list());
        if (str_contains($all_headers, "Location")) {
            header_remove("Location"); //удаляем редирект
            $matches = [];
            preg_match("/Location: ([^ ]+)/", $all_headers, $matches);
            $redirect_url = $matches[1];
            header("Access-Control-Expose-Headers: YWBLocation", false, 200);
            header("YWBAction: redirect", true, 200);
            header("YWBLocation: " . $redirect_url, true, 200);
            return http_response_code(200);
        }
    }
}
