<?php
require_once __DIR__ . '/bases/ipcountry.php';

function get_cloaker_path(bool $withPrefix = true, bool $withSlashEnd = true): string
{
    $domain = $_SERVER['HTTP_HOST'];
    if ($withPrefix) {
        $prefix = is_https() ? 'https://' : 'http://';
        $fullpath = $prefix . $domain . '/';
    } else
        $fullpath = $domain . '/';
    $script_path = array_values(array_filter(explode("/", $_SERVER['SCRIPT_NAME']), 'strlen'));
    array_pop($script_path);

    if (count($script_path) > 0) {
        if ($script_path[count($script_path) - 1] === 'js') //Dirty hack for js-connections
            array_pop($script_path);
        if (count($script_path) > 0)
            $fullpath .= implode('/', $script_path);
    }

    if ($withSlashEnd && !str_ends_with($fullpath, '/')) {
        $fullpath .= '/';
    } elseif (!$withSlashEnd && str_ends_with($fullpath, '/')) {
        $fullpath = substr($fullpath, 0, -1);
    }

    return $fullpath;
}

function is_https(): bool
{
    if (str_contains($_SERVER['HTTP_HOST'], '127.0.0.1'))
        return true; //for debug

    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $isSecure = true;
    } elseif (
    !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
    !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
    ) {
        $isSecure = true;
    } elseif ($_SERVER['SERVER_PORT'] == 443) {
        $isSecure = true;
    }
    return $isSecure;
}

function send_access_control_headers()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $parsed_url = parse_url($_SERVER['HTTP_REFERER']);
        $origin = $parsed_url['scheme'] . '://' . $parsed_url['host'];
        if (!empty($parsed_url['port']))
            $origin .= ':' . $parsed_url['port'];
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    header('Access-Control-Allow-Credentials: true');
}

function get_abs_from_rel($url)
{
    $fullpath = get_cloaker_path();
    $fullpath .= $url;
    if (!str_ends_with($url, '.php'))
        $fullpath = $fullpath . '/';
    return $fullpath;
}

function get_request_headers($ispost = false): array
{
    $ip = getip();
    $headers = array(
    'X-YWBCLO-UIP: ' . $ip,
    'X-FORWARDED-FOR: ' . $ip,
    'CF-CONNECTING-IP: ' . $ip,
    'FORWARDED-FOR: ' . $ip,
    'X-COMING-FROM: ' . $ip,
    'COMING-FROM: ' . $ip,
    'FORWARDED-FOR-IP: ' . $ip,
    'CLIENT-IP: ' . $ip,
    'X-REAL-IP: ' . $ip,
    'REMOTE-ADDR: ' . $ip
    );
    if ($ispost)
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
    return $headers;
}

function get($url): array
{
    $curl = curl_init();
    $optArray = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => get_request_headers(false),
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_REFERER => $_SERVER['REQUEST_URI'],
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'
    );
    curl_setopt_array($curl, $optArray);
    $content = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return ["html" => $content, "info" => $info, "error" => $error];
}

function post($url, $postfields): array
{
    $curl = curl_init();
    curl_setopt_array(
    $curl,
    array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_VERBOSE => true,
    CURLOPT_POSTFIELDS => $postfields,
    CURLOPT_REFERER => $_SERVER['REQUEST_URI'],
    CURLOPT_HTTPHEADER => get_request_headers(true),
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36'
    )
    );

    $content = curl_exec($curl);
    $info = curl_getinfo($curl);
    $error = curl_error($curl);
    curl_close($curl);
    return ["html" => $content, "info" => $info, "error" => $error];
}

