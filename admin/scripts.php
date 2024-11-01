<?php
require_once __DIR__.'/../requestfunc.php';
$jsFsPath = __DIR__.'/js';
$jsPath = get_cloaker_path().'js';
?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="<?=$jsPath?>/bootstrap.bundle.min.js"></script>
<script src="<?=$jsPath?>/query-builder.standalone.min.js"></script>
<script src="<?=$jsPath?>/flatpickr.js"></script>
<script src="<?=$jsPath?>/luxon.min.js"></script>
<script src="<?=$jsPath?>/tabulator.js?v=<?= filemtime($jsFsPath.'/tabulator.js') ?>"></script>
<script src="<?=$jsPath?>/campeditor.js?v=<?= filemtime($jsFsPath.'/campeditor.js') ?>"></script>
