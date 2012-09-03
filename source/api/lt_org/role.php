<?php
/*
 * Created on 2010-5-20
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


/* 2010-10-24 15:49:57
 * 修改 by songsp
 * 将一下方法从数据库取数据改为从memcache中取
 * function checkUserIsManager($userID)
 */

require_once(dirname(__FILE__)."/common.php");

//memcache
require_once(dirname(dirname(__FILE__)).'/common/memcache_util.php');


 class TRole extends commonClass{

 	 function __construct()
  {
  	parent::__construct();
  }

   //__destruct：析构函数，断开连接
 	/* function __destruct()
  	{
     parent::__destruct();
	}*/

 	/* 5.1 根据角色id查找相应的用户（不分页） */
 	 function getUserByRoleId($roleId){
 	 	if(empty($roleId))
 	 	{
 	 		return "传入的参数不正确";
 	 	}
 	 	else
 	 	{
 	 		$sql = "select select ltuser.azUserId ,ltuser.regName,ltuser.password,ltuser.name,ltuser.sex,ltuser.email,ltuser.folk,ltuser.learn,ltuser.businessCategory,ltuser.business,ltuser.status,ltuser.techGrade,ltuser.techCategory,ltuser.photo,ltuser.birthday,ltuser.certificateType,ltuser.certificateCode,ltuser.academy,ltuser.major,ltuser.degree,ltuser.phone,ltuser.mobile,ltuser.laborType from LTUSER ltuser, LTUSER_ROLE userrole WHERE ltuser.azUserId = userrole.user_id  and userrole.role='".$roleId."' ";
 	 		$s = mssql_query($sql);
 	 		$info = mssql_fetch_array($s);
 	 		if($info==False)
 	 		{
 	 			return "没有查找到数?";
 	 		}else{
 	 			do{
 	 				$obj[] = $info;
 	 			}while($info = mssql_fetch_array($s));
 	 			mssql_free_result($s);
 	 			return $obj;
 	 		}
 	 	}
 	 }

 	 /* 5.2 根据用户id查找其所在的角色 */
 	 function getRoleByUserId($userId)
 	 {
 	 	if(empty($userId))
 	 	{
 	 		return "传入的参数不正确";
 	 	}else{
 	 		$sql = "select distinct ltrole.id,ltrole.regname,ltrole.name,ltrole.opera_type from LTROLE ltrole ,LTUSER_ROLE userrole where ltrole.id=userrole.role_id and userrole.user_id ='".$userId."'";
 	 		$s = mssql_query($sql);
 	 		//$info = mssql_fetch_array($s);
 	 		//alter by qiaoyz,2011-3-22
 	 		while($temp = mssql_fetch_array($s)) $info[] = $temp;
 	 		if($info==False)
 	 		{
 	 			return "没有查找到数?";
 	 		}else{

 	 			mssql_free_result($s);
 	 			return $info;
 	 		}
 	 	}
 	 }

 	/* 5.3 根据角色id查找角色信息 */
 	 function getRole($roleId)
 	 {
 	 	if(empty($roleId))
 	 	{
 	 		return "传入的参数不正确";
 	 	}else{
 	 		$sql = "select ltrole.id,ltrole.regname,ltrole.name,ltrole.opera_type from LTROLE ltrole where ltrole.id='".$roleId."'";
 	 		$s = mssql_query($sql);
 	 		$info = mssql_fetch_array($s);
 	 		if($info==False)
 	 		{
 	 			return "没有查找到数?";
 	 		}else{
 	 			mssql_free_result($s);
 	 			return $info;
 	 		}
 	 	}
 	 }

	  /* 5.4 查找?个用户是不是三级管理权限 如果是三级管理员 返回true ，否则返回false  这里的管理员是指省，集团，市管理?
	   *
	   *
	   * 修改 by songsp  添加memcache优化
	   * */
	 function checkUserIsManager($userID){
	 	global $openMemcache;
		global $memcached;
		global $token ;//分隔符

	 	if(empty($userID))
 	 	{
 	 		return false;
 	 	}
 	 	//===使用 memcache ========================
 	 	if($openMemcache==1){ //使用memcache
 	 		//key  用户角色  ur_用户id_角色id
 			//角色只为3个 .roleid 分别为10044,10046,10080
 	 		$key = 'ur_'.$userID.'_10044';

 	 		$tmp_1 = $memcached->get($key);
 	 		if('1'==$tmp_1){
 	 			return true;
 	 		}
 	 		$key = 'ur_'.$userID.'_10046';
 	 		$tmp_2 = $memcached->get($key);
 	 		if('1'==$tmp_2){
 	 			return true;
 	 		}
 	 		$key = 'ur_'.$userID.'_10080';
 	 		$tmp_3 = $memcached->get($key);
 	 		if('1'==$tmp_3){
 	 			return true;
 	 		}
 	 		//如果memcache没有，直接返回
 	 		return false;

 	 	}
 	 	//===end 使用 memcache ========================

 	 	//从数据库中查询
  		$sql = " select id,user_id,role_id from LTUSER_ROLE  where LTUSER_ROLE.user_id=".$userID."  and LTUSER_ROLE.role_id in (10044,10046,10080) ";
  		$s = mssql_query($sql);


  		$rs = false;
  		while (($row = mssql_fetch_array($s)))
	    {
	    	$rs = true;

	    	if(1 !=$openMemcache){ //未使用memcache
	    		break;
	    	}else{

	    		//使用memcache  做更新
	    		$role_id = $row['role_id'];
  				$key = 'ur_'.$userID.'_'.$role_id;
 	 			$tmp = $memcached->get($key);
 	 			if(empty($tmp)){
 	 				$memcached->set($key, '1','32');
 	 			}
	    		//echo $role_id."<br>";
	    	}
	    }

  		//echo '<br>from database';
		mssql_free_result($s);
		//mssql_close();

  		return $rs;

	 }

 }
?>
