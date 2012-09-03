<?php
require_once(dirname(__FILE__)."/common.php");
	class UserMangerInterfaceImpl{
	
	 function __construct()
  {            
   parent::__construct();
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
       parent::__destruct();
	}
	
	
	/* 3.1 判断用户是否是管理员 */	
	function getUserManage($userId,$appId,$appName)
	{
		if((empty($userId)) or (empty($appId)))
		{
			return "传入的参数有误";
		}else{
			//$sql = " select azUserId from LTUSER where regName='$regName.'";
			$sql=" select *  from LTUSER_MANAGER where User_id='$userId' and Able_system='$appId' ";	
			$s = mssql_query($sql);
			$info =mssql_fetch_array($s);
			if($info==False){
            	return false;				
   	 		}
    		else{   
	  			return true;	  			
			}
			
			mssql_free_result($s); 		//释放结果集
		}
	}
	
	/* 3.2 设置用户是否是管理员 */	
	function setUserManage($userId,$appId,$appName)
	{
		
		if((empty($userId)) or (empty($appId)))
		{
			
			return "传入的参数有误";
		}else{
			//$sql = " select azUserId from LTUSER where regName='$regName.'";
			//$sql=" select *  from LTUSER_MANAGER where User_id='$userId' and Able_system='$appId' ";	
			$time_start = $this->getmicrotime();
			echo $userId;
			echo $appId;

			$sql="insert into LTUSER_MANAGER (User_id,Able_system,createTime) values ('$userId' , '$appId','$time_start')";
			$s = mssql_query($sql);			
			mssql_free_result($s); 		//释放结果集
		}
	}
	
	
	/* 3.3 取消用户是否是管理员 */	
	function deleteUserManage($userId,$appId,$appName)
	{
		if((empty($userId)) or (empty($appId)))
		{
			return "传入的参数有误";
		}else{
			//$sql = " select azUserId from LTUSER where regName='$regName.'";
			//$sql=" select *  from LTUSER_MANAGER where User_id='$userId' and Able_system='$appId' ";	
			$time_start = $this->getmicrotime();
			
			$sql=" delete from LTUSER_MANAGER where User_id='$userId' and Able_system='$appId' ";	
			$s = mssql_query($sql);			
			mssql_free_result($s); 		//释放结果集
		}
	}
	
	  
	function getmicrotime()  
	{  
		list($usec, $sec) = explode(" ",microtime());  
		return ((float)$usec + (float)$sec);  
	} 
	
}

?>