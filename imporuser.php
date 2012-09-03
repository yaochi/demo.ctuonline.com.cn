<?php
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$table = "lt_user_log7";

$sql = "select count(1) as c from $table";
$total = DB::result_first($sql);

$start = 0;
$pagesize = 100;
if($total){
	require "./api/sso/user.php";
	$user = new User();
	$bk = false; 
	echo "Start";
	while($start<=$total){
		$sql = "select * from $table LIMIT $start, $pagesize";
		$query = DB::query($sql);
		echo $start;
		while($row=DB::fetch($query)){
			switch($row['certificatetype']){
				case -1: $row['certificatetype'] = '未定义';break;
				case 0: $row['certificatetype'] = '身份证';break;
				case 1: $row['certificatetype'] = '户口本';break;
				case 2: $row['certificatetype'] = '军人证';break;
			}
			$row['username'] = $row['regName'];
			$row['realname'] = $row['name'];
			$row['gender'] = $row['sex'];
			
			$result = array();
			switch($row['OperaType']){
				case "0":
				$result = $user->create($row['userId'], $row);
				break;
				case "1":
				$result = $user->update($row['userId'], $row);
				break;
				case "2":
				$result = $user->remove($row['userId']);
				break;
			}
//			if(!$result['success']){
//				print_r($row);
//				print_r($result);
//				$bk = true;
//				break;
//			}
		}
		$start += $pagesize;
		if($bk) break;
	}
	echo "END";
}

mysql_close($link);
?>