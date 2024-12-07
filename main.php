<?php
require_once __DIR__ . '/debug.php';
require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/campaign.php';
require_once __DIR__ . '/htmlprocessing.php';
require_once __DIR__ . '/cookies.php';
require_once __DIR__ . '/redirect.php';
require_once __DIR__ . '/abtest.php';
require_once __DIR__ . '/requestfunc.php';

function white(bool $use_js_checks):CloakerAction
{
    global $c; //Campaign
    $ws = $c->white;
    $action = $ws->action;
    $error_codes = $ws->errorCodes;
    $folder_names = $ws->folderNames;
    $curl_urls = $ws->curlUrls;
    $redirect_urls = $ws->redirectUrls;

    //HACK: dirty hack to pass the referer through cookies
    if ($use_js_checks && !empty($_SERVER['HTTP_REFERER'])) {
        set_cookie("referer", $_SERVER['HTTP_REFERER']);
    }

    if ($ws->domainFilterEnabled) { //if we want to use different white pages for different domains
        $curdomain = $_SERVER['HTTP_HOST'];
        if (str_ends_with($curdomain, ':' . $_SERVER['SERVER_PORT'])) {
            $portLength = strlen(':' . $_SERVER['SERVER_PORT']);
            $curdomain = substr($curdomain, 0, -$portLength);
        }
        foreach ($ws->domainSpecific as $wds) {
            if ($wds->name !== $curdomain)
                continue;
            $wtd_arr = explode(":", $wds->action, 2);
            $action = $wtd_arr[0];
            switch ($action) {
                case 'error':
                    $error_codes = [intval($wtd_arr[1])];
                    break;
                case 'folder':
                    $folder_names = [$wtd_arr[1]];
                    break;
                case 'curl':
                    $curl_urls = [$wtd_arr[1]];
                    break;
                case 'redirect':
                    $redirect_urls = [$wtd_arr[1]];
                    break;
            }
            break;
        }
    }

    //if we have Javascript bot tests enabled 
    //then we should use a special white page
    //or add the test code into an existing white page
    if ($use_js_checks) {
        switch ($action) {
            case 'error':
            case 'redirect':
                $page = load_js_testpage();
                break;
            case 'folder':
                $curfolder = select_item($folder_names, $c->saveUserFlow, 'white', true);
                $page = load_white_content($curfolder[0]);
                break;
            case 'curl':
                $cururl = select_item($curl_urls, $c->saveUserFlow, 'white', false);
                $page = load_white_curl($cururl[0]);
                break;
        };
        return new CloakerAction('html',add_js_testcode($page));
    } else {
        switch ($action) {
            case 'error':
                $curcode = select_item($error_codes, $c->saveUserFlow, 'white', true);
                return new CloakerAction('error',$curcode[0]);
                break;
            case 'folder':
                $curfolder = select_item($folder_names, $c->saveUserFlow, 'white', true);
                return new CloakerAction('html', load_white_content($curfolder[0]));
                break;
            case 'curl':
                $cururl = select_item($curl_urls, $c->saveUserFlow, 'white', false);
                return new CloakerAction('html', load_white_curl($cururl[0]));
                break;
            case 'redirect':
                $cururl = select_item($redirect_urls, $c->saveUserFlow, 'white', false);
                return new CloakerAction('redirect',$cururl[0], $ws->redirectType);
                break;
        }
    }
}

function black(array $clickparams):CloakerAction
{
    global $c, $db; //Campaign

    $cursubid = set_subid();
    set_px();

    $landings = [];
    $isfolderland = false;

    $bl = $c->black->land;
    if ($bl->action == 'redirect')
        $landings = $bl->redirectUrls;
    else if ($bl->action == 'folder') {
        $landings = $bl->folderNames;
        $isfolderland = true;
    }

    $bp = $c->black->preland;
    switch ($bp->action) {
        case 'none':
            $res = select_item($landings, $c->saveUserFlow, 'landing', $isfolderland);
            $landing = $res[0];
            $db->add_black_click($cursubid, $clickparams, '', $landing, $c->campaignId);

            switch ($bl->action) {
                case 'folder':
                    return new CloakerAction('html',load_landing($landing));
                case 'redirect':
                    $redirectUrl = insert_subs_into_url($_GET, $landing);
                    return new CloakerAction('redirect',$redirectUrl,$bl->redirectType);
            }
            break;
        case 'folder': //local prelandings
            $prelandings = $bp->folderNames;
            if (empty($prelandings))
                break;
            $res = select_item($prelandings, $c->saveUserFlow, 'prelanding', true);
            $prelanding = $res[0];
            $res = select_item($landings, $c->saveUserFlow, 'landing', $isfolderland);
            $landing = $res[0];
            $t = $res[1];

            $db->add_black_click($cursubid, $clickparams, $prelanding, $landing, $c->campaignId);
            return new CloakerAction('html', load_prelanding($prelanding, $t));
            break;
    }
}

function takeAction(CloakerAction $ca){
    switch ($ca->action){
        case 'html':
            echo $ca->value;
            break;
        case 'redirect':
            redirect($ca->value,$ca->type,true);
            break;
        case 'error':
            http_response_code($ca->type);
            break;
    }
}

class CloakerAction
{
    public string $action;
    public string $value;
    public int $type;
    
    public function __construct(string $action, string $value, int $type=0)
    {
        $this->action = $action;
        $this->value = $value;
        $this->type = $type;
    }
}
