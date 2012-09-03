<?php
	 
	class User   {

	 function __construct()
  {            
     mssql_connect("192.168.0.20","sa","sa")or die("无法连接到数据库");	//链接服务器
	 mssql_select_db("ESN") or die("无法打开数据库连接");			//链接数据库
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
      mssql_close()or die("无法关闭数据库链接");	
	}
	
	/* 查找数据库公用方法,返回的是array */
  	function listpage($sql,$obj)
	{
		if(empty($sql))
		{
			return;
		}		
		$s = mssql_query($sql);
		$info =mssql_fetch_array($s);
		if($info==False){
            return  "没有查找到数据";
   	 	}
    	else{       
    		do{
				$obj[] = $info;
				//每一行
     		 }  while($info=mssql_fetch_array($s));
	  
	  	mssql_free_result($s); 		//释放结果集
		}	
		return $obj;
	}
	
	/* 6.1 根据注册名查询用户信息 ，返回array */
	function getUser($regName)
		{
			if(empty($regName))
			{
				return array(1=>"传入的参数有误");
			}
			else{
				$sql = " select azUserId,regName,password,name,sex,email,folk,learn,
					businessCategory,business,status,techGrade,techCategory,
					photo,birthday,certificateType,certificateCode,academy,major,
					degree,phone,mobile,laborType,azUserId from LTUSER 
					where regName='$regName'";
					echo $sql;
				return $this->listpage($sql,array());
			}
		}
	
 
	
	/* 6.3 根据注册名查找岗位信息 */
	function getUserStation($regName)
	{
		if(empty($regName))
		{
			return array(1=>"传入的参数有误");
		}
		else{
			$sql = " select tuser.azUserId ,station.s_id , station.s_code , station.s_name , station.parent_id , station.remark , station.corp_id 
				from ltuser tuser,ltstation station,ltuser_station userStation 
				where tuser.regName  = '" .$regName. "' and tuser.azUserId = userStation.user_id and station.s_id = userStation.s_id";
			echo $sql;
			return $this->listpage($sql,array());
		}
	}
	
	/* 6.4 根据注册名查询用户部门信息 */
	function getUserGroup($regName)
	{
		if(empty($regName))
		{
			return array(1=>"传入的参数有误");
		}
		else{
			$sql = " select tuser.azUserId, ltgroup.id , ltgroup.regName  , ltgroup.name , ltgroup.parentid ,ltgroup.tIndex , ltgroup.rootID , ltgroup.tcomment 
				from ltuser tuser,ltgroup ltgroup ,ltuser_group userGroup  
				where tuser.azUserId = userGroup.user_id  and ltgroup.id = userGroup.group_id  and ltuser.regName='$regName'";
			return $this->listpage($sql,array());
		}
	}
	
 
	
	/* 6.7 根据组织id查询出组织下的所有用户(分页) */
	function getUserByGroupIdAndPage($groupid,$currentPage,$pageSize)
	{
		if(empty($currentPage) || empty($pageSize))
		{
			$currentPage = 0;
			$pageSize = 20;
		}
		$primaryKey = "user_group.id";
		$sql = "select top ".$pageSize." user1.*,group1.* from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where ";
		$sqlWhere = " user1.azUserId = user_group.User_ID and user_group.group_id=group1.id and user1.userstatus='1' ";
		if(!empty($groupid))
		{
			$sqlWhere .= "and user_group.Group_ID ='".$groupid."'";
		}
		$sql .= $sqlWhere." and ".$primaryKey ." not in ( select top ".($currentPage != 0 ? $currentPage - 1 : $currentPage)* $pageSize." ".$primaryKey." from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where  ".$sqlWhere.")";
		echo $sql;
		return $this->listpage($sql,array());
	}
	
	/* 6.8 根据组织id查询出组织下的所有用户 (不分页) */
	function getUserByGroupId($groupid)
	{
		$sql = "select user1.*,group1.* from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where ";
		$sqlWhere = " user1.azUserId = user_group.User_ID and user_group.group_id=group1.id and user1.userstatus='1' ";
		if(!empty($groupid))
		{
			$sqlWhere .= "and user_group.Group_ID ='".$groupid."'";
		}
		return $this->listpage($sql.$sqlWhere,array());
	}
	
	/* 6.9 跟组岗位来查找该岗位的用户（分页） */
	function getUsersByStationIdAndPage($stationId,$currentPage,$pageSize)
	{
		if(empty($currentPage) || empty($pageSize))
		{
			$this->getUserByStationIdNoPage($stationId);
		}
		$sql = "select top ".$pageSize." ltuser.azUserId, ltuser.regName,ltuser.name from ltuser ltuser,ltuser_station userstation  where ";
		$sqlWhere = "userstation.user_id = ltuser.azUserId and userstation.s_id = '".$stationId."'";
		
		$sql .= $sqlWhere." and userstation.id not in ( select top ".($currentPage != 0 ? $currentPage - 1 : $currentPage)* $pageSize." userstation.id from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where  ".$sqlWhere.")";
		echo $sql;
		return $this->listpage($sql,array());		
	}
	/*   跟组岗位来查找该岗位的用户 不分页*/
	function getUserByStationIdNoPage($stationId)
	{
		$sql = "select ltuser.azUserId, ltuser.regName,ltuser.name from ltuser ltuser,ltuser_station userstation  where ";
		$sqlWhere = "userstation.user_id = ltuser.azUserId and userstation.s_id = '".$stationId."'";
		echo $sql.$sqlWhere;
		return $this->listpage($sql.$sqlWhere,array());	
	}
	
 
	
	/* 6.11 根据用户的注册名、姓名、性别查找用户 */
	function getUsersByPrams($id,$regName,$name,$sex,$page)
	{
		$sql = "select top 20 * from (select top $page *  from ltuser ltuser where userStatus = '1' ";
		if(!empty($id))
		{
			$sql .= " and id='$id' ";
		}
		if(!empty($regName))
		{
			$sql .= " and regName like '%$regName%' ";
		}
		if(!empty($name))
		{
			$sql .= " and name like '%$name%' ";
		}
		if(!empty($sex))
		{
			$sql .= " and sex=$sex ";
		}
		$sql .= " order by id asc) as t1 order by t1.id desc ";
		return $this->listpage($sql,array());	
	}
	
}
?>

