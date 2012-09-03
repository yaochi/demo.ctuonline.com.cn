<?php
/* Function: 社区中官方博客接口
 * Com.:
 * Author: yangyang
 * Date: 2010-10-11
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_blog.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_post.php';
$discuz = & discuz_core::instance();
$discuz->init();
	$officialBlogUid = getOfficialBlogUid();
	//获得分类
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_class')." WHERE uid='$officialBlogUid'");
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}
	$query = DB::query("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.uid='$officialBlogUid' ORDER BY blog.istop DESC ,blog.topdateline DESC,blog.dateline DESC LIMIT 0,10");

	while ($value = DB::fetch($query)) {
		$blog['title']=$value['subject'];
		$blog['descr']=messagecutstr($value['message'],160);
		if($_SERVER['HTTP_HOST']!='demo.ctuonline.com.cn'){
			$siteurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].'/');
		}else{
			$siteurl = htmlspecialchars('http://'.$_SERVER['SERVER_NAME'].'/');
		}
		$blog['url']=$siteurl.'home.php?mod=space&uid='.$officialBlogUid.'&do=blog&id='.$value['blogid'].'&from=space';
		$blog['dateline'] =$value['dateline'];
		$blog['classes'] = $classarr[$value['classid']];
		$blogs[]=$blog;
	}
	echo json_encode($blogs);








?>