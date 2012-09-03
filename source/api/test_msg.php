
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php

$start = time();
//�����ļ�
include_once('lt_msg/lt_msg_api.php');

$msg = new msg_lt();

$msgType='notice';
$fromUid =1;
 $fromUname='2';
  $toUid ='3';
  $toUtype ='personal';
  $message  = iconv('gbk','UTF-8','����һ����Ϣ'); 
  //$message ="123 ���Ĳ���  test";
  $type ='6'; 
  $fromAppid ='LCMS';
  $toAppid='ZBXT';

  $isOfficial ="0";
  $extra =null;
echo $msg->sendMsg( $msgType , $fromUid , $fromUname, $toUid  ,$toUtype ,$message ,$type ,$isOfficial ,$extra , $fromAppid ,$toAppid);
$end = time();
echo "<br>time:".($end-$start);
echo $rs;





?>
