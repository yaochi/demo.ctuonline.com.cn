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

class memcacheUser{

	/* */
	function loadpage($sql, $obj) {
		$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
		$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名

        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库

		if (empty ($sql)) {
			mssql_close()or die("无法关闭数据库链接");
			return -2;
		}
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);

		if ($info == False) {
			mssql_close()or die("无法关闭数据库链接");
			return "";
		} else {
			$obj = $info;
			mssql_free_result($s); //释放结果？
			mssql_close()or die("无法关闭数据库链接");
			return $obj;
		}
	}

	/* 6.1 根据注册名查询用户信�? ，返回array */
	function getUser($regName) {
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符
		if (empty ($regName)) {
			return -1;
		} else {

			//修改 by songsp 2011-3-22 15:01:21  从memcache中获取
			if($openMemcache==1){
				$key  =  'oz_u_'.$regName;
				$cache = $memcached->get($key);
				if(!empty($cache)){

					//解析
					$tmp = explode($token, $cache);
					$arr =  array(
						0=>$tmp[0] ,"azUserId"=>$tmp[0] ,
						1 =>$tmp[1] ,"regName" =>$tmp[1] ,
						2 => $tmp[2] ,"password" => $tmp[2] ,
						3 => $tmp[3] ,"name" => $tmp[3] ,
						4 => $tmp[4] ,"sex" => $tmp[4] ,
						5 => $tmp[5] ,"email" => $tmp[5] ,
						6 => $tmp[6] ,"folk" => $tmp[6] ,
						7=> $tmp[7] ,"learn"=> $tmp[7] ,
						8=> $tmp[8] ,"businessCategory"=> $tmp[8] ,
						9=> $tmp[9] ,"business"=> $tmp[9] ,
						10=> $tmp[10] ,"status"=> $tmp[10] ,
						11=> $tmp[11] ,"techGrade"=> $tmp[11] ,
						12=> $tmp[12] ,"techCategory"=> $tmp[12] ,
						13=> $tmp[13] ,"photo"=> $tmp[13] ,
						14=> $tmp[14] ,"birthday"=> $tmp[14] ,
						15=> $tmp[15] ,"certificateType"=> $tmp[15] ,
						16=> $tmp[16] ,"certificateCode"=> $tmp[16] ,
						17=> $tmp[17] ,"academy"=> $tmp[17] ,
						18=> $tmp[18] ,"major"=> $tmp[18] ,
						19=> $tmp[19] ,"degree"=> $tmp[19] ,
						20=> $tmp[20] ,"phone"=> $tmp[20] ,
						21=> $tmp[21] ,"mobile"=> $tmp[21] ,
						22=> $tmp[22] ,"laborType"=> $tmp[22] ,
						23=> $tmp[0] ,"azUserId"=> $tmp[0] ,
						);

					return $arr;
				}


			}


			$sql = " select azUserId,regName,password,name,sex,email,folk,learn,
								businessCategory,business,status,techGrade,techCategory,
								photo,birthday,certificateType,certificateCode,academy,major,
								degree,phone,mobile,laborType,azUserId from LTUSER
								where regName='$regName'";
			return $this->loadpage($sql, array ());
		}
	}


	/* 6.4 根据注册名查询用户部门信息*/
	function getUserGroup($regName) {
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符
		if (empty ($regName)) {
			return -1;
		}


		if($openMemcache==1){

			//echo $memcached->_active.'uuuu';

	 			//以下为从memcache中取
				//先从 用户名->用户id
				$tmp_uid = $memcached->get('u_'.$regName);
				if(!empty($tmp_uid)){

					//从用户id->组织id
					$tmp_gid = $memcached->get('ug_'.$tmp_uid);

					if(!empty($tmp_gid)){
						//从组织id->组织信息
						$tmp_group = $memcached->get('g_'.$tmp_gid); //组织信息 ：组织id ,regname ,name,parentid,rootid,tindex,reservedint

						if(!empty($tmp_group)){

							//解析
							$tmp = explode($token, $tmp_group);
							$arr =  array(
								0=>$tmp_uid ,"azUserId"=>$tmp_uid ,
								1 =>$tmp[0] ,"id" =>$tmp[0] ,
								2 => $tmp[1] ,"regName" => $tmp[1] ,
								3 => $tmp[2] ,"name" => $tmp[2] ,
								4 => $tmp[3] ,"parentid" => $tmp[3] ,
								5 => $tmp[5] ,"tIndex" => $tmp[5] ,
								6 => $tmp[4] ,"rootID" => $tmp[4] ,
								7=> $tmp[6] ,"reservedint"=> $tmp[6] ,

							);


							//print_r($arr);echo "<br>";

							return $arr;
						}
					}

				//end 从memcache中取
				}
			}

			//以下为从数据库中查询==================================
			$sql = " select ltuser.azUserId, ltgroup.id , ltgroup.regName  , ltgroup.name , ltgroup.parentid ,ltgroup.tIndex , ltgroup.rootID , ltgroup.reservedint
								from ltuser ltuser,ltgroup ltgroup ,ltuser_group userGroup
								where ltuser.azUserId = userGroup.user_id  and ltgroup.id = userGroup.group_id  and ltuser.regName='$regName'";
			$rs = $this->loadpage($sql, array ());

			//print_r ($rs);echo '<br>';
			//echo "from database <br>";
			if(!is_null($rs)){
				if($openMemcache==1){
					//更新cache
					//1.用户名id -->用户id

					if(empty($tmp_uid)){
						$key = 'u_'.$regName;
						//$memcached->delete($key);
						$memcached->set($key, $rs[azUserId],'32');
					}
					//2.用户id ->组织id
					if(empty($tmp_gid)){
						$key = 'ug_'.$rs[azUserId];
						//$memcached->delete($key);
						$memcached->set($key, $rs[id],'32');
					}
					//3.组织id->组织信息
					if(empty($tmp_gid)){
						$key = 'g_'.$rs[id];
						//内容顺序//id ,regname ,name,parentid,rootid,tindex,reservedint
						$content =  $rs[id].$token.$rs[regName].$token.$rs[name].$token.$rs[parentid].$token.$rs[rootID].$token.$rs[tIndex].$token.$rs[reservedint];
						//$memcached->delete($key);
						$memcached->set($key, $content,'32');
					}
				}

			}

			return $rs;
			// end ===================================



	}

	/*
	 * 	add by songsp  2010-12-18  memcache 方式
	 *  判断是否是专家用户  如果是返回专家所在专区ids
	 *
	 */
	function checkisExpertUser($regName){
		global $openMemcache;
		global $memcached;
		global $token ;//分隔符
		if($openMemcache==1){
			$key = 'oz_ut_'.$regName;
			$tmp_val = $memcached->get($key);
			if(!empty($tmp_val)){
				//解析
				$val = explode($token, $tmp_val);
				if($val){
					if('0'==$val[0]){
						return $val[1];
					}
				}
			}else{
				$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
				$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
		        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
		        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名

		        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
			    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库

				//从数据库中查询 ，并放置到memcache中
				$sql_id="select fids from LTUSER where regName='".$regName."' and userType='0'";
				$s = mssql_query($sql_id);
				$row = mssql_fetch_array($s);

				mssql_close()or die("无法关闭数据库链接");

				if($row){
					$fids=$row['fids'];
					//$memcached->delete($key);
					$memcached->set($key, '0'.$token.$fids,'32');
					return $fids;
				}

			}



		}else{
			$dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//数据库ip
			$dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//数据库用户名
	        $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//数据库密码
	        $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名

	        mssql_connect($dbhost_mssql_lt, $dbuser_mssql_lt,$dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
		    mssql_select_db($dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
			//没有开启memcache    从数据库查询

			$sql_id="select fids from LTUSER where regName='".$regName."' and userType='0'";
			$s = mssql_query($sql_id);
			$row = mssql_fetch_array($s);
			mssql_close()or die("无法关闭数据库链接");
			if($row){
				$fids=$row['fids'];
			}
		}
		return $fids;
	}

	}

?>