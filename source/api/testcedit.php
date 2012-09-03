
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<?php
include_once('lt_credit/lt_credit_api.php');

$credit_lt = new credit_lt(); 

$uid="1";
$action ="top";
$resourceId="1";

echo "用户获取规则积分：".$credit_lt->createCreditLog($uid,$action,$resourceId);
echo "<br/>";
echo "<br/>";

$fromUid="1";
$toUid="2";
$actionRandom="张大军";
$credit="10";
$experience="10";
echo "用户获取非规则积分：".$credit_lt->createCreditLogRandom($fromUid,$toUid,$actionRandom,$credit,$experience);
echo "<br/>";
echo "<br/>";

echo "用户总积分：".$credit_lt->getTotalCreditByUid($uid);
echo "<br/>";
echo "<br/>";
echo "积分列表：<br/>";
$creditLog=$credit_lt->getCreditLogsByUid($uid);
if(is_array($creditLog)){

	foreach($creditLog as $log){
		foreach($log as $value){
			echo iconv("gb2312","UTF-8", $value)."|";
		}
		echo "<br/>";
	}
}
echo "<br/>";
echo "积分规则：";
$creditRule=$credit_lt->getCreditRuleByAction($action);
if(is_array($creditRule)){

	foreach($creditRule as $rule){
		echo iconv("gb2312","UTF-8", $rule)."|";
	}
}

?>
