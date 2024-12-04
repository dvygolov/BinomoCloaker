<?php
require_once __DIR__ . '/cookies.php';
require_once __DIR__ . '/db/db.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/bases/ipcountry.php';

class MacrosProcessor
{
    private string $subid;
    public function __construct($subid = null)
    {
        $this->subid = $subid ?? get_cookie('subid');
    }

    public function replace_html_macros($html): string
    {
        $ip = getip();
        $html = preg_replace_callback('/\{city,([^\}]+)\}/', function ($m) use ($ip) {
            return getcity($ip, $m[1]);
        }, $html);

        $html = preg_replace('/\{subid\}/', $this->subid, $html);

        $px = get_cookie('px');
        $html = preg_replace('/\{px\}/', $px, $html);
        return $html;
    }

    public function replace_url_macros($url): string
    {
        $url_components = parse_url($url);
        parse_str($url_components['query'] ?? '', $query_array);

        // Iterate over the $sub_ids and replace the keys
        foreach ($query_array as $qk => $qv) {
            if (empty($qv))
                continue;
            if ($qv[0] !== '{' || $qv[strlen($qv) - 1] !== '}')
                continue; //we need only macroses

            $macro = substr($qv, 1, strlen($qv) - 2);
            $macroValue = $this->get_macro_value($macro);
            if ($macroValue === false)
                continue; //HINT: should we log $url?
            $query_array[$qk] = $macroValue;
        }

        // Build the new query string
        $new_query = http_build_query($query_array);

        // Rebuild the URL
        $new_url = $url_components['scheme'] . '://' . $url_components['host'];
        if (isset($url_components['path'])) {
            $new_url .= $url_components['path'];
        }
        if ($new_query) {
            $new_url .= '?' . $new_query;
        }

        return $new_url;
    }

    private function get_macro_value($macro,$is_s2s = false): string|bool
    {
        global $db;
        if ($macro === 'subid') {
            $cookie = get_cookie($macro);
            if (empty($cookie)) {
                add_log("macros", "Couldn't get subid macros value from cookie.");
                return false;
            }
            return $cookie;
        }

        $clickParams = ['ip', 'country', 'lang', 'os', 'osver', 'client', 'clientver', 'device', 'brand', 'model', 'isp', 'ua', 'preland', 'land', 'status'];
        if (in_array($macro, $clickParams)) {
            if (empty($this->subid)) {
                add_log("macros", "Couldn't get macros $macro value from DB. Subid not set!");
                return false;
            } else {
                $clicks = $db->get_clicks_by_subid($this->subid, true);
                return $clicks[0][$macro];
            }
        }

        //we need to find click parameter with this name, we can do that only if we know subid
        if (str_starts_with($macro, "c.")) {
            if (empty($this->subid)) {
                add_log("macros", "Couldn't get macros $macro value from DB. Subid not set!");
                return false;
            } else {
                $clicks = $db->get_clicks_by_subid($this->subid, true);
                if (count($clicks[0]['params']) == 0) {
                    add_log(
                    "macros",
                    "Couldn't find click macro $macro value. Subid:{$this->subid}, Params are EMPTY!"
                    );
                    return false;
                }
                $p = $clicks[0]['params'];
                $cmacro = substr($macro, 2);
                if (array_key_exists($cmacro, $p)) {
                    return $p[$cmacro];
                } else {
                    add_log(
                    "macros",
                    "Couldn't find click macro $macro value. Subid:{$this->subid}, Params:" . json_encode($p)
                    );
                    return false;
                }
            }
        }
        if ($macro === 'domain') {
            return $_SERVER['HTTP_HOST'];
        }

        if ($macro === 'time') {
            return time();
        }
        if (str_starts_with($macro, "hash:")) {
            $toHash = substr($macro, 5);
            $toHashValue = $this->get_macro_value($toHash);
            if ($toHashValue === false) {
                add_log("macros", "Couldn't find  macro $toHash value to hash. Subid:{$this->subid}");
                return false;
            }
            $hashed = crypt($toHashValue);
            add_log("macros", "Hashing $toHashValue to $hashed");
            return $hashed;
        }
        if (str_starts_with($macro, "random:")) {
            $range = explode('-', substr($macro, 7));
            $selected = rand($range[0], $range[1]);
            add_log("macros", "Got random $selected from range $range");
            return $selected;
        }

        //some kind of strange macros, we need to log this situation
        add_log("macros", "Couldn't find macros: $macro. Subid:{$this->subid}");
        return false;
    }
}