<?php
/* Function: 热门标签
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$number=empty($_G[gp_number])?10:$_G[gp_number];

$query=DB::query("SELECT count(tagname) as a ,tagname,tagid FROM ".DB::TABLE("home_tagrelation")." where tagname!='' and contentid!='0' group by tagname order by a desc limit 0,$number");
while($value=DB::fetch($query)){
	$tags[]=$value;
}

$res['hottags']=$tags;
echo json_encode($res);
?>