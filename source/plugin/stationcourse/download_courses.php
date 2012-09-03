<?
include './source/common/xmlexcel/XmlExcel.php'; 
 
require './source/plugin/stationcourse/function/function_stationcourse.php';
$xls=new XmlExcel;   
$xls->setDefaultWidth(80);   
$xls->setDefaultAlign("left");   
$xls->setDefaultHeight(18);   
$fid=$_GET[fid];
$value=getAllCourse_Order($fid);
$arr_bt=array("课程编号","课程名称","课程格式","课件格式","课程来源","学时","推荐度","课程介绍","vd1","vd2","vd3","vd4","vd5","vd6");
//echo count($arr_bt);
for ($i = 0; $i <count($value); $i++) {  
	$vd1=trim(getbyidAllCourse_knowledge(1,$value[$i][id]));
	$vd2=trim(getbyidAllCourse_knowledge(2,$value[$i][id]));
	$vd3=trim(getbyidAllCourse_knowledge(3,$value[$i][id]));
	$vd4=trim(getbyidAllCourse_knowledge(4,$value[$i][id]));
	$vd5=trim(getbyidAllCourse_knowledge(5,$value[$i][id]));
	$vd6=trim(getbyidAllCourse_knowledge(6,$value[$i][id]));
    $arr_nr=array(trim($value[$i][course_id]),trim($value[$i][course_name]),trim($value[$i][course_type]),trim($value[$i][cai_type]),trim($value[$i][cai_sourse]),trim($value[$i][class_hour]),trim($value[$i][recommend]),trim($value[$i][introduction]),$vd1,$vd2,$vd3,$vd4,$vd5,$vd6);		
    $xls->addPageRow($arr_bt,$arr_nr,600000,$xls->uniqueName("sheets"));   

}
$xls->export("course".$fid);  
 
?>

