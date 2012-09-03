<?php
require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$mod = getgpc('mod');


$discuz->init();

require DISCUZ_ROOT.'./source/module/ajax/ajax_'.$mod.'.php';
?>
