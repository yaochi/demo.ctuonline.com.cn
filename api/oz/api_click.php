<?php
/* Function: 文档表态接口
 * Com.:
 * Author: wuhan
 * Date: 2010-8-10
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$idtype = 'docid';

$maxclicknum = 0;
loadcache('click');

$clicks = empty($_G['cache']['click']['blogid'])?array():$_G['cache']['click']['blogid'];
$_G['cache']['click']['docid'] = $clicks;

$hash = md5("\t");
$query = DB :: query("SELECT * FROM " . DB :: table('doc_click') . " WHERE docid='$id'");
$doc = DB :: fetch($query);
if (empty ($doc)) {
	$doc = array();
	$doc['docid'] = $id;
	foreach ($clicks as $key => $value) {
		$doc["click{$key}"] = 0;
	}
}
	
foreach ($clicks as $key => $value) {
	$value['clicknum'] = $doc["click{$key}"];
	$value['classid'] = mt_rand(1, 4);
	if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
	$clicks[$key] = $value;
}

$clickuserlist = array();
$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,24");
while ($value = DB::fetch($query)) {
	$value['clickname'] = $clicks[$value['clickid']]['name'];
	$clickuserlist[] = $value;
}

include_once template("api/space_click");
?>
