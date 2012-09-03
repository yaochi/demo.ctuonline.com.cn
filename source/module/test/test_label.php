<?php
require_once libfile("function/label");
//echo "test:credit_create_credit_log". credit_create_credit_log(87896, "top", 10)."<br/>";
//echo "test:credit_get_total_credit_by_uid". credit_get_total_credit_by_uid(87896)."<br/>";
//echo "test:credit_create_credit_log_random". credit_create_credit_log_random(0, 87896, "top", -10)."<br/>";
//echo "test:credit_get_total_credit_by_uid". credit_get_total_credit_by_uid(87896)."<br/>";

//$labels = listalllabels();
//foreach($labels as $fup => $labelarr){
//	echo '<b>'.$fup.'----'.$labelarr[0][name].'</b><br />';
//	foreach($labelarr as $fid => $label)
//	{
//		if($fid!='0'){
//			echo "------".$label[fid]."====".$label[name]."(".$label[groupnum].")<br />";
//		}
//	}
//}

//$labels=listchildlabels("549");
//foreach($labels as $fid => $label)
//{
//	echo "------".$label[fid]."====".$label[name]."(".$label[groupnum].")<br />";
//}

//$label=getlabelgroupnum("582");
//echo "------".$label[fid]."====".$label[name]."(".$label[groupnum].")<br />";

//deletelabelgroup("115","0");


echo getlabelbyname("财务,绩效管理");


?>
