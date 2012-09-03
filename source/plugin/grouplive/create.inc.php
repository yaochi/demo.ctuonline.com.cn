<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-7-30
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(__FILE__)."/function/function_live.php");

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");

$_GET['plugin_op'] = 'groupmenu';

require_once (dirname(__FILE__)."/livecp.php");
?>
