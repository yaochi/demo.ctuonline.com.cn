<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-7-30
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(__FILE__)."/function/function_groupalbum.php");

// 首先判断该用户是否为该专区的用户
$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");
// Update $_GET['plugin_op'] 参数
$_GET['plugin_op'] = 'groupmenu';

require_once (dirname(__FILE__)."/upload.php");
?>
