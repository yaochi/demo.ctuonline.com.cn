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
 
	
class memcacherole{


	
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
 	 		//如果memcache中没有 ，直接返回false
 	 		return false;
 	 	}
 	 	//===end 使用 memcache ========================

 	 	//从数据库中查询
  		$sql = " select id,user_id,role_id from LTUSER_ROLE  where LTUSER_ROLE.user_id=".$userID."  and LTUSER_ROLE.role_id in (10044,10046,10080) ";

  		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名

        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库

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
		mssql_close()or die("无法关闭数据库链接");

  		return $rs;

	 }

}



?>