<?php
include_once __DIR__.'/../../requestfunc.php';
class LibreTranslate
{
    private $translateAddress = 'https://libretranslate.de/translate';
    private $languages = ["en", "ar", "zh", "nl", "fi", "fr", "de", "hi", "hu", "id", "ga", "it", "ja", "ko", "pl", "pt", "ru", "es", "sv", "tr", "uk", "vi"];

    public function checkLanguages($src, $target)
    {
        return (in_array($src, $this->languages) && in_array($target, $this->languages));
    }

    public function translate($text, $sourceLang, $targetLang)
    {
        $params = array("q" => $text, "source" => $sourceLang, "target" => $targetLang, "format" => "text");
        $res = post($this->translateAddress, $params);
        if ($res['info']['http_code']!==200){
            add_log("thankyou",
                "Can't translate text '$text' from $sourceLang to $targetLang using Libretranslate. Error {$res['error']}");
            return 'error';
        }

        $json = json_decode($res['content']);
        if (isset($json->error)) //this language is not supported so we show an english version
            return 'error';
        else
            return $json->translatedText;
    }
}
