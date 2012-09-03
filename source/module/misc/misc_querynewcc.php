<?php
global $_G;
/*echo dirname(dirname(dirname(__FILE__)));*/
require_once dirname(dirname(dirname(__FILE__)))."/function/function_resource.php";

$type=$_GET['type']?$_GET['type']:'doc';
if($type!='doc'&&$type!='course'){
	$type='doc';
}
$count=$_GET['count']?$_GET['count']:'5';
if(is_numeric($count)){
	$count=intval($count);
}else{
	$count=5;
}
$result=array();
if($type=="course"){
	$result=topnewCourses($count);
}else{
	$result=topnewDocs($count);
}
$result=$result['result'];
//print_r($result);
$resultstr='';

if(!empty($result)){
	$resultstr.="<style>body, ul { margin:0; padding: 0; font: normal normal normal 12px/1.5 'DejaVu Sans', 'Lucida Grande', Tahoma, 'Hiragino Sans GB', STHeiti, SimSun, sans-serif; } .xl a { text-decoration: none; font-size: 12px; color: #333 } .xl li { margin: 2px 0; padding-left: 10px; background: transparent url(static/image/common/dot.gif) no-repeat 0 8px; height: 1.5em; overflow: hidden;}</style>";
	$resultstr.="<div class='xl xl1'>";
	$resultstr.="<ul>";
	foreach($result as $key=>$value){
		$resultstr.="<li>";
		$resultstr.="<a href='$value[titlelink]' target='_blank'>$value[title]</a>";
		$resultstr.="</li>";
	}
	$resultstr.="</ul>";
	$resultstr.="</div>";
	
}

echo $resultstr;
?>