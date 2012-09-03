<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_quickquery.php 9284 2010-04-28 01:52:59Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$simplequeries = array(
	array('comment' => '快速开启论坛版块功能', 'sql' => ''),
	array('comment' => '开启 所有版块 主题回收站', 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'1\''),
	array('comment' => '开启 所有版块 Discuz! 代码”', 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'1\''),
	array('comment' => '开启 所有版块 [IMG] 代码”', 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'1\''),
	array('comment' => '开启 所有版块 Smilies 代码', 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'1\''),
	array('comment' => '开启 所有版块 内容干扰码', 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'1\''),
	array('comment' => '开启 所有版块 允许匿名发贴”', 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'1\''),

	array('comment' => '快速关闭论坛版块功能', 'sql' => ''),
	array('comment' => '关闭 所有版块 主题回收站', 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'0\''),
	array('comment' => '关闭 所有版块 HTML 代码', 'sql' => 'UPDATE {tablepre}forum_forum SET allowhtml=\'0\''),
	array('comment' => '关闭 所有版块 Discuz! 代码', 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'0\''),
	array('comment' => '关闭 所有版块 [IMG] 代码', 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'0\''),
	array('comment' => '关闭 所有版块 Smilies 代码', 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'0\''),
	array('comment' => '关闭 所有版块 内容干扰码', 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'0\''),
	array('comment' => '关闭 所有版块 允许匿名发贴', 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'0\''),

	array('comment' => '会员操作相关', 'sql' => ''),
	array('comment' => '清空 所有会员 积分交易记录', 'sql' => 'TRUNCATE {tablepre}common_credit_log;'),
);

?>