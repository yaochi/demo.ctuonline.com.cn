<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *
 *
 *      $Id: userapp.php 10075 2011-05-06 09:52:41Z qiaoyongzhi $
 */

define('APPTYPEID', 5);

require_once '../../source/class/class_core.php';
require_once '../../source/function/function_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
$uids=$_GET['uids'];

echo getprovincebyuids($uids);



?>