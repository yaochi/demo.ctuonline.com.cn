<?php
	require './source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	$selectionid = $_GET['selectionid'];
	$query = DB :: query("SELECT * FROM pre_selection  WHERE selectionid=$selectionid");
	$selection = DB :: fetch($query);
	$num = DB :: result_first
("SELECT sum(scored) FROM pre_selection_option WHERE selectionid=$selectionid");
	$query = DB :: query("SELECT * FROM pre_selection_option WHERE selectionid=$selectionid order by scored desc");
	$selection['selectionname'] = iconv( "UTF-8", "gb2312",$selection['selectionname']);
	$outputdata = $selection['selectionname']."\t\n";
	$outputdata .= "���β���ͶƱ������"."\t".""."\t".$selection['scored']."\t".""."\t"."\t\n";
	$outputdata .= "����"."\t"."ѡ����"."\t"."Ʊ��"."\t"."�ٷֱ�"."\t"."\t\n";
	$i = 1;
	while($value = DB :: fetch($query)){
		$optionlist[]=$value;
		$value['optionname'] = iconv( "UTF-8", "gb2312",$value['optionname']);
		if($selection['scored']==0){
			$outputdata .= $i."\t".$value['optionname']."\t".$value['scored']."\t"."0"."\t"."\t\n";
		}else{
			$rent = round($value['scored']*100/$num,2);
			$outputdata .= $i."\t".$value['optionname']."\t".$value['scored']."\t".$rent."\t"."\t\n";
		}
		
		$i = $i+1;
	}
	
	header("Content-Type: application/vnd.ms-execl");
	header("Content-Disposition: attachment; filename=��ѡͳ�ƽ��.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	//$outputdata = iconv( "UTF-8", "gb2312",$outputdata);
	//showmessage($outputdata);
	echo($outputdata);
	exit();
?>
