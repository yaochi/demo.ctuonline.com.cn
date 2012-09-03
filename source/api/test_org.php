
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php

$start = time();

include_once('lt_org/user.php');
//
//$user = new User();
//
//$temp=$user->getUserprovince("E32670100");
//echo  $temp[id];
//echo  $temp[name];
//echo  $temp[parentID];

//include_once('lt_org/userInterface.php');	

//$user = new UserInterFace();

//echo json_encode($user->getUserAndOrgAndStation("E32670100"));
require_once dirname(dirname(__FILE__))."/function/function_org.php";
print_r(getUserGroupByuserId("271859"));
//$end = time();
echo "<br>time:".($end-$start);

?>
