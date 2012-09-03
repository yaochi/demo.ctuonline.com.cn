<?php


require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';

$discuz = &discuz_core::instance();
$discuz->init();
$method=strtolower($_SERVER["REQUEST_METHOD"]);
$result=0;

if($method=='post'){
	$from=$_REQUEST['from'];
	if($from=='oz'){
		$ids=$_POST['ids'];		
		$id_arr=array();
		$djson='';
		if($ids){
			$djson=json_decode($ids);
		}	
		if(!empty($djson)){
			foreach($djson as $key=>$value){
				$id_arr[]=$value;
			}
			delete_group_doc($id_arr);
			delete_resourcelist($id_arr);
		}	
		$result=1;		
	}
}
function delete_group_doc($ids){
	if(!empty($ids)){
		DB::query("DELETE FROM pre_group_doc WHERE docid IN(".dimplode($ids).");");
	}
}

function delete_resourcelist($ids){
	if(!empty($ids)){
		DB::query("DELETE FROM pre_resourcelist WHERE resourceid IN(".dimplode($ids).");");//数据同步					
	}
}
/*$s=array('first'=>2956789,'second'=>3254691,'third'=>1956273,'fourth'=>2956788);
print_r($s);
$js=json_encode($s);
print_r($js);*/
echo $result;
exit;
?>