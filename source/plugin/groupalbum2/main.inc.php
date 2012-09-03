<?php
/* Function: 专区相册，用于分发请求，根据请求执行不同的代码
 * 1	获取标识 $_G["gp_plugin_name"]."_action"；注意默认首页必须是index
 * 2	判断标识是否合法；
 * 3	执行标识的对应的代码；
 * 4	自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加；
 * 例：
 * 假设 $_G["gp_plugin_name"] = groupalbum2:main
 * 1	获取 groupalbum2:main_action = index;
 * 2	判断 groupalbum2:main_action = index 是否在数组 array('index', 'albumcp','upload') 中
 * 3	运行 index.php
 * 4	调用 template/index.htm
 * 问题: 
 * 1	由于目前采用修改home_album表, 增加一个fid字段, 所以用户在个人中心还会看到他在专区中上传的图片，
 * 		收藏，分享需要修改，主要是最后的显示页面是要专区中。
 * 2	如果创建新表, 用户在个人中心是看不到专区中上传的, 但是对于表态, 评价, 收藏, 分享这些都需要修改, 
 * 		需要增加新的分类, 专区相册, 因为用的是新表, 原来的是针对Home_album表，就无法用了。
 * 3	页面要需要修改, 目前样式有问题
 * 4	权限, 经验值还需要增加.
 * Com.:
 * Author: wuhan
 * Date: 2010-7-12
 * 
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
//获取标识, 如: groupalbum2:main_action=?
$join_plugin_action = $_REQUEST[$_G["gp_plugin_name"]."_action"];

//判断标识是否合法
if(empty($join_plugin_action) || !in_array($join_plugin_action, array('index', 'albumcp','upload','swfupload'))){
	$join_plugin_action = 'index';
}

require_once (dirname(__FILE__)."/function/function_groupalbum.php");

$_G['ismember'] = DB::result_first("SELECT 1 FROM ".DB::table('forum_groupuser')." WHERE fid='$_G[fid]' AND uid='$_G[uid]'");

//执行标识的对应的代码
//会自动调用template下面语标识同名的模板文件，不需要写template()，但可以根据实际情况添加
require_once (dirname(__FILE__)."/$join_plugin_action.php");
?>
