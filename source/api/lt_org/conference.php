<?php
/*
 * Created on 2010-6-11
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once(dirname(__FILE__)."/common.php");
 class Conference extends commonClass{
 	
 		 function __construct()
  {            
  	parent::__construct();
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
     parent::__destruct();
	}
	
	/* 根据用户id查找用户的会议列表,不分页 */
 	function getConferenceByUserId($userId){
 		if(empty($userId))
 		{
 			return "传入的参数不正确";
 		}
 		else{
 			$now = time();
 			$sql = "SELECT con.id,con.name,con.descr,con.start_date,con.end_date,conUser.role,conUser.url as url FROM LT_CONFERENCE_USER conUser INNER JOIN LT_CONFERENCE con ON conUser.conferenceEntityId = con.id WHERE (conUser.attendType = '0') AND (conUser.flag = 1) AND (conUser.appUserEntityId = '$userId') AND (con.end_date > $now)";
 			$s = mssql_query($sql);
 			$info = mssql_fetch_array($s);
 			if($info==False)
 			{
 				return "没有查询到数据";
 			}else{
 				do{
 					$obj[] = $info;
 				}while($info = mssql_fetch_array($s));
 				mssql_free_result($s);
 				return $obj;
 			}
 		}
 	}
 }
?>
