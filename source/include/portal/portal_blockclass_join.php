<?
global $_G;
foreach ($_G['group_plugins']['group_available'] as $key=>$plugin) {
    $blockfile = DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . 'block/setting.inc.php');
    if (file_exists($blockfile)) {
        @include($blockfile);
    }
}
?>