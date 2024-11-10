<?php
require_once __DIR__ . '/macros.php';

function redirect($url, $redirect_type = 302, $rep_macros = false): void
{
    if ($rep_macros) {
        $mp = new MacrosProcessor();
        $url = $mp->replace_url_macros($url);
    }
    header('X-Robots-Tag: noindex, nofollow');
    header('Location: ' . $url, true, $redirect_type);
}

function jsredirect($url): void
{
    echo "<script type='text/javascript'> window.location='$url';</script>";
}

function insert_subs_into_url(array $currentParams, string $redirectUrl)
{
    global $c;
    $preset = ['subid', 'prelanding', 'landing'];
    
    $redirectParsed = parse_url($redirectUrl);
    parse_str($redirectParsed['query'] ?? '', $redirectParams);

    foreach ($c->subIds as $sub) {
        $name = $sub->name;
        $rewrite = $sub->rewrite;

        if (in_array($name, $preset) && !empty(get_cookie($name))) {
            $redirectParams[$rewrite] = get_cookie($name);
        }
        else if (isset($currentParams[$name])) {
            $redirectParams[$rewrite] = $currentParams[$name];
        }
    }

    // Формируем новый query string с обновленными параметрами
    $newQuery = http_build_query($redirectParams);

    // Собираем новый URL для редиректа
    $finalRedirectUrl = $redirectParsed['scheme'] . '://' . $redirectParsed['host'] . $redirectParsed['path'];
    if (!empty($newQuery)) {
        $finalRedirectUrl .= '?' . $newQuery;
    }

    return $finalRedirectUrl;
}
