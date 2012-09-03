<?php
/* 2010-10-24 15:49:57  
 * 修改 by songsp
 * 将一下方法从数据库取数据改为从memcache中取
 * function getUserGroup($regName) 
 */

require_once dirname(__FILE__) . "/common.php";
//memcache 
require_once(dirname(dirname(__FILE__)).'/common/memcache_util.php');
require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');
 
	
class memcachegroup{
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
		
		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名
		
        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
			    
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			mssql_close()or die("无法关闭数据库链接");	
			return 0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			mssql_close()or die("无法关闭数据库链接");	
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
		
		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名
		
        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
	    
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			mssql_close()or die("无法关闭数据库链接");	
			return $result;
		}
		else{
			if("-1"!=$info["parentID"])
			{
				$parentId = $info["parentID"];
				$result .= ",".$this->loadParent($parentId);
			}
		}
		
		mssql_close()or die("无法关闭数据库链接");	
		return $result;
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
		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名
		
        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
	    
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if($info==False)
		{
			mssql_close()or die("无法关闭数据库链接");	
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			mssql_close()or die("无法关闭数据库链接");	
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
		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名
		
        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
	    
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		
		if($info[num]>0)
		{
			mssql_close()or die("无法关闭数据库链接");	
			return true;	
		}else{
			mssql_close()or die("无法关闭数据库链接");	
			return false;	
		}	
	}
	
	
}
	
	
	
?>