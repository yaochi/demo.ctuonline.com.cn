<?php
/*
 * Created on 2010-5-19
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 

/* 2010-10-24 15:49:57  
 * 修改 by songsp
 * 将一下方法从数据库取数据改为从memcache中取
 * function getParentGroupById($groupId)
 */


require_once(dirname(__FILE__)."/common.php");

//memcache 
require_once(dirname(dirname(__FILE__)).'/common/memcache_util.php');

 class Group extends commonClass{
 	
 	 function __construct()
  {            
  	parent::__construct();
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
     parent::__destruct();
	}

	
	/* 1.1 根据部门注册名返回部门信息 */
	function getGroup($regName)
	{
		if(empty($regName))
 		{
 			return -1;
 		}else{
 			$sql = "select id,regName,name,parentId,tIndex,rootID,TComment from ltgroup where regName  = '" .$regName."'";
 			mssql_connect("192.168.0.20","sa","sa")or die("无法连接到数据库");	//链接服务器
			mssql_select_db("ESN") or die("无法打开数据库连接");			//链接数据库
			$s = mssql_query($sql);
			$info =mssql_fetch_array($s);
			if($info==False){
            	return 0;
				
   	 		}
    		else{   
	  			return $info;
	  			mysql_free_result($s); 		//释放结果集
	 			mssql_close()or die("无法关闭数据库链接");			//关闭数据库连接
			}
 		}
	}
	
	/* 1.2 根据部门注册名返回部门id */
	function getGroupId($regName)
	{
		if(empty($regName))
 		{
 			return -1;
 		}else{
 			
 			$sql = "select id from ltgroup where regName  = '" .$regName."'";
			$s = mssql_query($sql);
			$info =mssql_fetch_array($s);
			if($info==False){
            	return 0;				
   	 		}
    		else{   
	  			return $info[id];
			}
 		}
	}
	
	/* 1.3 根据父部门，查找相应的子部门 */
	function getGroupIdByParentId($parentId,$end)
	{
		if("0"==$end)
		{
			$ids = $this->getIdsByParentId($parentId,null,$end);
		}else{
			$ids = "'".$parentId."'";
		}
		
		$sql = "select ltgroup.id,ltgroup.name from ltgroup ltgroup where ltgroup.parentId in (".$ids.")";
		$s = mssql_query($sql);
		$info =mssql_fetch_array($s);
		if($info==False){
            return 0;
   	 	}
    	else{       
    		do{
				$obj[] = $info;
				//每一行
     		 }  while($info=mssql_fetch_array($s));
     		 return $obj;
		}
	}
	
	/* 1.4 根据部门id查找部门下员工 */
	function getUserByGroupId($groupId,$end)
	{
		if("0"==$end)
		{
			$ids = $this->getIdsByParentId($groupId,null,$end);
		}else{
			$ids = "'".$groupId."'";
		}
		$sql = "select ltuser.id,ltuser.regName,ltuser.name,ltuser.azUserId from ltuser_group usergroup, ltuser ltuser where usergroup.user_id=ltuser.azUserId and usergroup.group_id in (".$ids.")";
		$s = mssql_query($sql);
		$info =mssql_fetch_array($s);
		if($info==False){
             return 0;
   	 	}
    	else{       
    		do{
				$obj[] = $info;
				//每一行
     		 }  while($info=mssql_fetch_array($s));
     		 return $obj;
		}
	}
	
	
	/* 根据父id查找部门或者公司id集合，返回值为‘id1’，‘id2’ */
	function getIdsByParentId($parentId,$reservedint,$end)
	{
		$result = "'".$parentId."'";
		$sql = "select id from ltgroup where parentId  = '" .$parentId."'";
		if(!empty($reservedint))
		{
			$sql .= " and reservedint='$reservedint' ";
		}
		$s = mssql_query($sql);
		$info =mssql_fetch_array($s);
//		if($info==False){
//            return 0;
//   	 	}
    	if($info!=False){       
    		do{	
	    		if("-1"!=$info["id"])
				{
					$id = $info["id"];
					$result .= ",".$this->getIdsByParentId($id,$reservedint,$end);
				}
     		 }while($info=mssql_fetch_array($s));
		}	
		return $result;
	}
	
	/* 1.5 根据部门id查找其所属的公司 
	 * 
	 * 
	 * 修改 by songsp 2010-10-24 20:33:34 
	 * 添加 memcache 
	 * */
	function getParentGroupById($groupId)
	{
		//memcache
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符

		if(empty($groupId))
		{
			return -1;
		}
//===使用 memcache ========================
 	 	if($openMemcache==1){ //使用memcache
 	 		
 	 		$result = $this->loadParentInMemcache($groupId);
 	 		
 	 		//echo '<br>';
 	 		//print_r($result);
 	 		//echo '<br>';
 	 		if($result ){
 	 			return $result;
 	 		}
 	 	}
	 	//===end 使用 memcache ========================
 	 	//echo "from database";
		$ids = $this->loadParent($groupId);
		$sql = "select ltgroup.id,ltgroup.name,ltgroup.parentID,ltgroup.reservedint from LTGROUP where ltgroup.reservedint='1' and ltgroup.id in ($ids)";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			return 0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
	}
	function loadParentInMemcache($groupId ){
		
		//memcache
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符
		
		
		$key = 'g_'.$groupId;
		
		$tmp = $memcached->get($key); //组织信息 ：组织id ,regname ,name,parentid,rootid,tindex,reservedint
		if(empty($tmp)){
			return;
		}
		$tmp_group = explode($token, $tmp);

		if(!empty($tmp_group)){
			
					
			$rs = array();
			if("-1"!=$tmp_group[3]){
				$parentid = $tmp_group[3];
				// print_r('-------'.$parentid);
				$rs = $this->loadParentInMemcache($parentid);
			}
				
			if('1' == $tmp_group[6]){
				//为公司
				$result[] = array(
								0=>$groupId ,"id"=>$groupId ,
								1 =>$tmp_group[0] ,"name" =>$tmp_group[2] ,
								2 => $tmp_group[1] ,"parentID" => $tmp_group[3] ,
								3 => $tmp_group[2] ,"reservedint" => $tmp_group[6] 		
							);
			}
			//合并数值
			$result = array_merge($rs,$result);
			
			
		}
		return $result;
	
	}

	
	/* 根据部门id查找部门的父部门,返回类型为‘id1’，‘id2’ */
	function loadParent($groupId)
	{
		$result = "'$groupId'";
		$sql = "select parentID from LTGroup where id=$groupId";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			return $result;
		}
		else{
			if("-1"!=$info["parentID"])
			{
				$parentId = $info["parentID"];
				$result .= ",".$this->loadParent($parentId);
			}
		}
		return $result;
	}
	
	/* 1.6 根据公司id查找其下属公司 */
	function getGroupByTopId($topId)
	{
		if(empty($topId))
		{
			return -1;
		}
		
		$ids=$this->getIdsByParentId($topId,"1","0");
		$sql = "select ltgroup.id,ltgroup.name,ltgroup.parentID,ltgroup.reservedint from ltgroup ltgroup where ltgroup.reservedint='1' and  ltgroup.parentId in ($ids)";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
		
	}
	
    /* 1.7 返回所有的组织机构 */
	function getAllGroups()
	{
		 
		$sql = "select ltgroup.id,ltgroup.name,ltgroup.parentID from ltgroup ltgroup where ltgroup.reservedint='1' order by ltgroup.id asc";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
		
	}
	/* 1.8 根据id返回组织机构的信息 ，优先从cache中取*/
	function getGroupById($groupId){
		//memcache
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符		
		//===使用 memcache ========================
 	 	if($openMemcache==1){ //使用memcache 	 		
	 	 	$key = 'g_'.$groupId;			
			$temp = $memcached->get($key); //组织信息 ：组织id ,regname ,name,parentid,rootid,tindex,reservedint
			if($temp){
				$tmp_group = explode($token, $temp);
				$result[] = array(
								0=>$tmp_group[0] ,"id"=>$tmp_group[0] ,
								1 =>$tmp_group[1] ,"regname" =>$tmp_group[1] ,
								2 => $tmp_group[2] ,"name" => $tmp_group[2] ,
								3 => $tmp_group[3] ,"parentid" => $tmp_group[3] ,
								4 => $tmp_group[4] ,"rootid" => $tmp_group[4],
								5 => $tmp_group[5] ,"tindex" => $tmp_group[5], 	
								6 => $tmp_group[6] ,"reservedint" => $tmp_group[6]
							);
 	 			return $result;
 	 		}
 	 	} 	 	
	 	//===end 使用 memcache ========================
	 	
 	 	//echo "from database"; 	 	
		$sql = "select ltgroup.id,ltgroup.regName,ltgroup.name,ltgroup.parentId,ltgroup.tIndex,ltgroup.rootID,ltgroup.TComment 
			from ltgroup ltgroup where id  = $groupId ";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
	}
	/* 1.9 判断用户是否属于某个组织 ，优先从cache中取*/
	function checkUserInGroup($uid,$groupId){
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符		
		//===使用 memcache ========================
 	 	if($openMemcache==1){ //使用memcache 	
 	 		$key = 'ug_'.$uid;			
			$temp = $memcached->get($key);//
			if($temp){
				if($temp==$groupId){
					return true;
				}else{
					return false;
				}
			}
 	 	} 	 	
 	 	//===end 使用 memcache ========================	 	
 	 	//echo "from database"; 
 	 	
		$sql = "select count(*) as num from LTUSER_GROUP where User_ID  = $uid and Group_ID = $groupId";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		
		if($info[num]>0)
		{
			return true;	
		}else{
			return false;	
		}	
	}
	
	/* 1.9 判断用户是否属于某个组织以及下面的组织 */
	function checkUserInGroups($uid,$groupId){
		if($groupId!='9002')
		{
			$ids=$this->getIdsByParentId($groupId,"","0"); 
			$sql = "select count(*) as num from LTUSER_GROUP where User_ID  = $uid and Group_ID in ($ids)";
			$s = mssql_query($sql);
			$info = mssql_fetch_array($s); 
		}else{
			$info[num] = 1;
		}
		
		
		
		if($info[num]>0)
		{
			return 1;	
		}else{
			return 0;	
		}	
	}
 	
 }
?>
