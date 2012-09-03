<?php
define('IN_ADMINCP', TRUE);

$op = $_G[gp_op]?$_G[gp_op]:"index";
$_G[gp_op] = $op;

require DISCUZ_ROOT.'./source/include/manage/'.$mod.'/manage_op_'.$op.'.php';
?>
