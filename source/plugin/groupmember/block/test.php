<?php
//if (!defined('IN_DISCUZ')) {
//    exit('Access Denied');
//}
//require_once libfile("function/group");
$newuserlist=$activityuserlist=array();
$newuserlist=groupuserlist(37,'joindateline', 8,0);
foreach($newuserlist as $user){
	echo($user['uid']);
}
?>