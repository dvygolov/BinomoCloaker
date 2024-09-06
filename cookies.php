<?php
function set_cookie($name, $value, $path = '/'): void
{
    $expires = time() + 60 * 60 * 24 * 5; //время, на которое ставятся куки, по умолчанию - 5 дней
    header("Set-Cookie: {$name}={$value}; Expires={$expires}; Path={$path}; SameSite=None; Secure", false);
    get_session();
    $_SESSION[$name] = $value;
    session_write_close();
}

function get_cookie($name): string
{
    get_session(true);
    return $_COOKIE[$name] ?? $_SESSION[$name] ?? '';
}

function set_subid(): string
{
    //giving each user a unique ID - subid and saving it to cookies
    //or getting it from cookies if exists
    $cursubid = get_cookie('subid');
    if (empty($cursubid))
        $cursubid = uniqid();
    set_cookie('subid', $cursubid, '/');
    return $cursubid;
}

function set_px(): void
{
    $curpx = $_GET['px'] ?? '';
    if (empty($curpx)) return;
    set_cookie('px', $curpx, '/');
}

//проверяем, если у пользователя установлена куки, что он уже конвертился, а также имя и телефон, то сверяем время
//если прошло менее суток, то хуй ему, а не лид, обнуляем время
function has_conversion_cookies($name, $phone): bool
{
    $cname = get_cookie('name');
    $cphone = get_cookie('phone');
    $ctime = get_cookie('ctime');

    if (empty($ctime) || empty($name) || empty($phone)) {
        return false;
    }

    if ($cname !== $name || $cphone !== $phone) {
        return false;
    }

    $currentTimestamp = (new DateTime())->getTimestamp();
    $secondsDiff = $currentTimestamp - $ctime;

    if ($secondsDiff < 24 * 60 * 60) {
        set_cookie('ctime', $currentTimestamp);
        return true;
    }

    return false;
}

function get_session($readOnly = false)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if ($readOnly)
            session_start(['read_and_close' => true]);
        else {
            ini_set("session.cookie_secure", 1);
            session_start();
        }
    }
}