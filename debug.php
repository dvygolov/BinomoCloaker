<?php
require_once __DIR__.'/settings.php';
class DebugMethods
{
    static array $start_times;

    public static function on(): bool
    {
        global $cloSettings;
        return $cloSettings['debug'];
    }
    public static function start($header_name): void
    {
        if (!self::on()) return;
        self::$start_times[$header_name] = microtime(true);
    }

    public static function stop($header_name): void
    {
        if (!self::on()) return;
        $time_elapsed_secs = microtime(true) - self::$start_times[$header_name];
        unset(self::$start_times[$header_name]);
        header($header_name.": " . $time_elapsed_secs);
    }

    public static function display_errors(): void
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    public static function check_php():void
    {
        $ver=phpversion();
        if (version_compare($ver, '8.2.0', '<'))
            die("PHP version should be 8.2 or higher! Change your PHP version and return.");
    }
    
    public static function check_sqlite():void
    {
        if (!extension_loaded('sqlite3')) 
            die("SQLite extension NOT FOUND! Use another hosting or enable SQLite.");
    }
    
    public static function check_dirs():void
    {
        if (!is_writable(__DIR__)) 
            die("PHP doesn't have write access for the current directory. Change access rights!");
    }
}
if (DebugMethods::on()){
    DebugMethods::display_errors();
}

DebugMethods::check_php();
DebugMethods::check_sqlite();
DebugMethods::check_dirs();
