<?php
require_once __DIR__.'/../requestfunc.php';
$cssFsPath = __DIR__.'/css';
$cssPath = get_cloaker_path().'css';
?>
    <!-- Google Fonts
		============================================ -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet" />
    <!-- main CSS
		============================================ -->
    <link rel="stylesheet" href="<?=$cssPath?>/main.css?v=<?= filemtime($cssFsPath.'/main.css') ?>" />
    <!-- style CSS
		============================================ -->
    <link rel="stylesheet" href="<?=$cssPath?>/style.css?v=<?= filemtime($cssFsPath.'/style.css') ?>" />
    <!--Bootstrap-->
    <link rel="stylesheet" href="<?=$cssPath?>/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$cssPath?>/bootstrap-icons.min.css">
    <!--QueryBuilder-->
    <link rel="stylesheet" href="<?=$cssPath?>/query-builder.dark.min.css"/>

    <!--Date Picker -->
    <link rel="stylesheet" href="<?=$cssPath?>/flatpickr.min.css">
    <link rel="stylesheet" href="<?=$cssPath?>/dark.css">
    <!--Data tables-->
    <link rel="stylesheet" href="<?=$cssPath?>/tabulator_clo.css?v=<?=filemtime($cssFsPath.'/tabulator_clo.css') ?>" >
    <link rel="stylesheet" href="<?=$cssPath?>/tabulator_midnight.css?v=<?=filemtime($cssFsPath.'/tabulator_midnight.css') ?>" >