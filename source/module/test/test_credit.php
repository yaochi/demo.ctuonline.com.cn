<?php
require_once libfile("function/credit");
echo "test:credit_create_credit_log". credit_create_credit_log(87896, "top", 1000)."<br/>";
//echo "test:credit_get_total_credit_by_uid". credit_get_total_credit_by_uid(87896)."<br/>";
//echo "test:credit_create_credit_log_random". credit_create_credit_log_random(0, 87896, "top", -10)."<br/>";
//echo "test:credit_get_total_credit_by_uid". credit_get_total_credit_by_uid(87896)."<br/>";
//
//$creditRank=get_credit_rank(0,2);
//if(!$creditRank){
//	echo "为空";
//}else{
//	foreach( $creditRank as $value )
//	{
//	 echo $value['name']."=".$value['credit'];
//	 echo "<br/>";
//	}
//}
//
//
//echo "积分规则列表：<br/>";
//$creditRules=get_credit_rules();
//if(!$creditRules){
//	echo "为空";
//}else{
//	foreach( $creditRules as $value )
//	{
//	 echo $value['ruleName']."=".$value['action'];
//	 echo "<br/>";
//	}
//}

$credit=credit_get_credit_log(1);
foreach( $credit as $value )
{
 echo $value['rulename']."=".$value['action'];
 echo "<br/>";
}


?>
