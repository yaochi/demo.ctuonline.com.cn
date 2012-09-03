<?php
/* 2010-10-24 15:49:57
 * 修改 by songsp
 * 将一下方法从数据库取数据改为从memcache中取
 * function getUserGroup($regName)
 */

require_once dirname(__FILE__) . "/common.php";
require_once dirname(__FILE__) . "/insert_user.php";

//memcache
require_once(dirname(dirname(__FILE__)).'/common/memcache_util.php');


class User extends commonClass {

	function __construct() {
		parent :: __construct();
	}

	//__destruct：析构函数，断开连接
	function __destruct() {
		parent :: __destruct();
	}


	/* 查找数据库公用方�?,返回的是array */
	function listpage($sql, $obj) {
		if (empty ($sql)) {
			return -2;
		}
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if ($info == False) {
			return 0;
		} else {
			do {
				$obj[] = $info;
				//每一�?
			} while ($info = mssql_fetch_array($s));

			mssql_free_result($s); //释放结果�?
			return $obj;
		}
	}

	/* ������ݿ⹫�÷���?,���ص��ǵ��м�¼ */
	function loadpage($sql, $obj) {
		if (empty ($sql)) {
			return -2;
		}
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);

		if ($info == False) {
			return "";
		} else {
			$obj = $info;
			mssql_free_result($s); //释放结果�?
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

	/* 6.2 根据用户名查询用户id，返回string类型的id */
	function getUserId($regName) {
		if (empty ($regName)) {
			return -1;
		} else {
			$sql = " select id from LTUSER where regName='$regName'";
			$s = mssql_query($sql);
			$info = mssql_fetch_array($s);
			if ($info == False) {
				return 0;

			} else {
				return $info[id];
				mssql_free_result($s); //释放结果�?
			}
		}
	}

	/* 6.3 根据注册名查找岗位信�? */
	function getUserStation($regName) {
		if (empty ($regName)) {
			return -1;
		} else {
			$sql = " select tuser.azUserId ,station.s_id , station.s_code , station.s_name , station.parent_id , station.remark , station.corp_id
							from ltuser tuser,ltstation station,ltuser_station userStation
							where tuser.regName  = '" . $regName . "' and tuser.azUserId = userStation.user_id and station.s_id = userStation.s_id";
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


	/* 6.4.1 ͨ���û�id �ҵ����ڵ���֯ �������ǹ�˾�����ţ�*/
	function getUserGroupByuserId($userId) {
		if (empty ($userId)) {
			return -1;
		} else {
			$sql = " select ltuser.azUserId, ltgroup.id , ltgroup.regName  , ltgroup.name , ltgroup.parentid ,ltgroup.tIndex , ltgroup.rootID , ltgroup.tcomment
							from ltuser ltuser,ltgroup ltgroup ,ltuser_group userGroup
							where ltuser.id = userGroup.user_id  and ltgroup.id = userGroup.group_id  and ltuser.id='$userId'";
			return $this->loadpage($sql, array ());
		}
	}

	/* 6.5 修改用户密码 */
	function updateUserPassword($userid, $regName, $password) {
		if (empty ($regName) or empty ($userid) or empty ($password)) {
			return -1;
		} else {
			$sql = " select azUserId from LTUSER where regName='$regName'";

			$sql = "update ltuser set password = '" . $password . "' where regName = '" . $regName . "'";

			$s = mssql_query($sql);
			return $s;
		}
	}

	/* 6.7 根据组织id查询出组织下的所有用�?(分页) */
	function getUserByGroupIdAndPage($groupid, $currentPage, $pageSize) {
		if (empty ($currentPage)) {
			$currentPage = 0;
		}
		if(empty ($pageSize)){
			$pageSize = 20;
		}
		$ids = $this->getIdsByParentId($groupid, null, $end);
		$sql = "select id,regname from ltuser where id in (
				           select top ".$pageSize." user_id from ltuser_group where id not in (
						   select top " . $currentPage * $pageSize . "
						    id from ltuser_group where group_id in ( " . $ids . " ) order by id ) and group_id in ( " . $ids . ")  order by id )";
		return $this->listpage($sql, array ());
	}

	/* 6.8 根据组织id查询出组织下的所有用�? (不分�?) */
	function getUserByGroupId($groupid) {
		$sql = "select user1.*,group1.* from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where ";
		$sqlWhere = " user1.azUserId = user_group.User_ID and user_group.group_id=group1.id and user1.userstatus='1' ";
		if (!empty ($groupid)) {
			$sqlWhere .= "and user_group.Group_ID ='" . $groupid . "'";
		}
		return $this->listpage($sql . $sqlWhere, array ());
	}

	/* 6.9 跟组岗位来查找该岗位的用户（分页�? */
	function getUsersByStationIdAndPage($stationId, $currentPage, $pageSize) {
		if (empty ($currentPage) || empty ($pageSize)) {
			$this->getUserByStationIdNoPage($stationId);
		}

		$sql = "select id,regname from ltuser where id in (
				           select top " . $pageSize . " user_id from ltuser_station where id not in (
						   select top " . $currentPage * $pageSize . "
						    id from ltuser_station where s_id = '" . $stationId . "' order by id ) and s_id = '" . $stationId . "'  order by id )";
		return $this->listpage($sql, array ());
	}
	/*   跟组岗位来查找该岗位的用�? 不分�?*/
	function getUserByStationIdNoPage($stationId) {
		$sql = "select ltuser.azUserId, ltuser.regName,ltuser.name from ltuser ltuser,ltuser_station userstation  where ";
		$sqlWhere = "userstation.user_id = ltuser.azUserId and userstation.s_id = '" . $stationId . "'";
		return $this->listpage($sql . $sqlWhere, array ());
	}

	/* 6.10 根据傲姿的用户id，查找综合管理平台所对应的用户id */
	function getUserIdByAZUserId($azUserId) {
		if (empty ($regName)) {
			return -1;
		} else {
			$sql = " select id from ltuser where azUserId = '" . $azUserId . "'";
			$s = mssql_query($sql);
			$info = mssql_fetch_array($s);
			if ($info == False) {
				return 0;
			} else {
				return $info;
				mssql_free_result($s); //释放结果�?
			}
		}
	}

	/* 6.11 根据用户的注册名、姓名�?��?�别查找用户 */
	function getUsersByPrams($id, $regName, $name, $sex, $page) {
		$sql = "select top 20 * from (select top $page *  from ltuser ltuser where userStatus = '1' ";
		if (!empty ($id)) {
			$sql .= " and id='$id' ";
		}
		if (!empty ($regName)) {
			$sql .= " and regName like '%$regName%' ";
		}
		if (!empty ($name)) {
			$sql .= " and name like '%$name%' ";
		}
		if (!empty ($sex)) {
			$sql .= " and sex=$sex ";
		}
		$sql .= " order by id asc) as t1 order by t1.id desc ";
		return $this->listpage($sql, array ());
	}

	function getUserDetail($groupid) {
		if (empty ($groupid)) {
			return -1;
		}
		$sql = "select ltgroup.id,ltgroup.name,ltgroup.parentId,ltgroup.reservedint from ltgroup ltgroup" .
		" where ltgroup.id= '$groupid' ";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if ($info["reservedint"] == '0') {
			return $this->getUserDetail($info[parentId]);
		}
		return $info;
	}

	/* 根据父id查找部门或�?�公司id集合，返回�?�为‘id1’，‘id2�? */
	function getIdsByParentId($parentId, $reservedint, $end) {
		$result = "'" . $parentId . "'";
		$sql = "select id from ltgroup where parentId  = '" . $parentId . "'";
		if (!empty ($reservedint)) {
			$sql .= " and reservedint='$reservedint' ";
		}
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		//		if($info==False){
		//            return 0;
		//   	 	}
		if ($info != False) {
			do {
				if ("-1" != $info["id"]) {
					$id = $info["id"];
					$result .= "," . $this->getIdsByParentId($id, $reservedint, $end);
				}
			} while ($info = mssql_fetch_array($s));
		}
		return $result;
	}

	/**
	 * 创建专家用户
	 *
	 * $userarr 用户信息数组
	 * $creatorRegName 创建者登录名
	 */
		function createExpertUser($userarr, $creatorRegName) {
		global $tname_lt;
		if (empty ($userarr) || empty ($userarr['name'])) {
			return -1;
		} else {
			require_once (dirname(dirname(__FILE__)) . '/common/config_lt_msg.php');
			require_once (dirname(dirname(__FILE__)) . '/common/db/mssql_class_lt.php');
			$mssql4zh = new mssql4lt();
			if(empty($mssql4zh->link)){
				return '--';
			}
            $id = "";
			$regName = "";
			$password = "";
			$creatorId = "";
			$now = number_format(time() * 1000, 0, '', '');
			$userarr['name']=base64_decode($userarr['name']);
            $name= $userarr['name'];
            $userarr['fname']=base64_decode($userarr['fname']);
            $creatorId = $this->getUserId($creatorRegName);



            /*
             * 更新memcached
             * 创建专家用户更新memcached
             * 判断用户是否是专家用户
             */
            global $openMemcache;
		    global $memcached;

		    if($openMemcache==1){
		    	//$key oz_ut_{登录名}
		    	//Value={usertype} + $u0006$ + {fids}
		    	$regName = "ZJ".$creatorId;
		    	$key='oz_ut_'.$regName;
		    	//$memcached->delete($key);
				$memcached->set($key, '0'.'$u0006$'.$userarr['fids'],'32');
		    }

/*	        if(!empty($userarr['name']))
	        	$userarr['name']=mb_convert_encoding($userarr['name'],"GBK","UTF-8");
	        if(!empty($userarr['fname']))
	        	$userarr['fname']=mb_convert_encoding($userarr['fname'],"GBK","UTF-8"); */
			$user = array ('name' => $userarr['name']);
			$userarr['userType'] = "0"; //专家用户
			$userarr['creator'] = $creatorId;
			$userarr['lastModifier'] = $creatorId;
			$userarr['createTime'] = $now;
			$userarr['updateTime'] = $now;
			$usermsg = Syn_InsertUser :: getInstance()->insert_user($user, $userarr,$tname_lt);
            $usermsg['name'] = base64_encode($name);
			return $usermsg;
		}
	}
	/*查询用户管辖的组织机构，根据用户id查询返回相应机构组织的id*/
	function getGroupidByUserid($user_id) {

		$sql_ug = "select group_id from ltuser_group where user_id=" . $user_id;
		$query = mssql_query($sql_ug);
		while ($row = mssql_fetch_array($query)) {
			$group_id = $row['group_id'];
		}
		mssql_free_result($query);
		$sql = "select role_id from ltuser_role where user_id=" . $user_id;
		$query = mssql_query($sql);
		$role_id = 10100;

		while ($row = mssql_fetch_array($query)) {

			if (($row['role_id'] >= 10044) && ($role_id > $row['role_id'])) {
				$role_id = $row['role_id'];
			}

		}
		mssql_free_result($query);
		if ($role_id == 10044)
			$groupid = 9002;

		else
			if ($role_id == 10046) {
				$arr = $this->getGroupID($group_id);
				$groupid = $arr['now'];
			} else
				if ($role_id == 10080) {
					$arr = $this->getGroupID($group_id);
					$groupid = $arr['before'];
				}
        //mssql_close();
		return $groupid;
	}

	function getGroupID($group_id) {
		// $mssql4zh=new mssql4lt();
		$gid_before = $group_id;
		$gid = $group_id;
		$gid_after = $group_id;

		while ($gid_after != 9002) {
			$sql = "select parentid from ltgroup where id=" . $gid_after;
			$query = mssql_query($sql);
			while ($row = mssql_fetch_array($query)) {
				$gid_before = $gid;
				$gid = $gid_after;
				$gid_after = $row['parentid'];

			}
		}
		mssql_free_result($query);
		$arr['now'] = $gid;
		$arr['before'] = $gid_before;
		return $arr;
	}

	/*
	*
	*����û�ע��������û�����ʡ
	*regName �û�ע����
	*String����
	*string[0]:ʡ��˾id
	*string[1]: �û�����ʡ��˾���
	*/
	function getUserprovince($regName){
	   require_once "group.php";
	     //���ע��������û�������Ϣ
		$userGroup=$this->getUsergroup($regName);
		if(is_null($userGroup)){
			return null;
		}else{
			//�û����ڻ��id
			$userGroupId=$userGroup[1];
			//�û����ڵ�ʡ��˾��parentid=9002��Ϊʡ��˾

			$groupids=$this->loadParent($userGroupId);
			//ʡ�����id  15790

			return $groupids;
		}
		return null;
	}

	/* ��ݲ���id���Ҳ��ŵĸ�����,��������Ϊ��id1������id2��
	* Ϊ����ʡ��˾���⿽����4�ĺ��������ݿ��޷��ر�
	*/
	function loadParent($groupId)
	{
		$result = "$groupId";
		$sql = "select id,name,parentID from LTGroup where id=$groupId";
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);



		if($info==False)
		{
			return $result;
		}
		else{
			if("9002"!=$info["parentID"])
			{
				$parentId = $info["parentID"];
				$result = $this->loadParent($parentId);

			}else{
				$result=$info;
			}
		}

		return $result;
	}



	/* 6.1 Ϊ����ƽ̨д�Ľӿڳ��� ֻ�����û����id */
	function getUserByRegName($regName) {
		if (empty ($regName)) {
			return -1;
		} else {
			$sql = " select id,name from  LTUSER 	where regName='$regName'";
			return $this->loadpage($sql, array ());
		}
	}

	/* 6.3 ��ȡ�û�����ʡ����ӿ� */
	function getUserStationForSystem($regName) {
		if (empty ($regName)) {
			return -1;
		} else {
			$sql = " select station.s_id , station.s_name
							from ltuser tuser,ltstation station,ltuser_station userStation
							where tuser.regName  = '" . $regName . "' and tuser.id = userStation.user_id and station.s_id = userStation.s_id";
			return $this->listpage($sql, array ());
		}
	}

	function getUserByGroupIdAndPageAndGroupIds($groupid, $currentPage, $pageSize,$groupIds) {
		if (empty ($currentPage)) {
			$currentPage = 0;
		}
		if(empty ($pageSize)){
			$pageSize = 20;
		}
		$sql = "select id,regname from ltuser where id in (
				           select top ".$pageSize." user_id from ltuser_group where id not in (
						   select top " . $currentPage * $pageSize . "
						    id from ltuser_group where group_id in ( " . $groupIds . " ) order by id ) and group_id in ( " . $groupIds. ")  order by id )";
		return $this->listpage($sql, array ());
	}

	/*
	 * added by fumz，2010-12-3 19:29:51
	 */
	function getUserGroupByUid($uid) {
		if ($uid) {

/*			$sql = " select ltuser.azUserId, ltgroup.id , ltgroup.regName  , ltgroup.name , ltgroup.parentid ,ltgroup.tIndex , ltgroup.rootID , ltgroup.tcomment
							from ltuser ltuser,ltgroup ltgroup ,ltuser_group userGroup
							where ltuser.id = userGroup.user_id  and ltgroup.id = userGroup.group_id  and ltuser.id='$uid'";*/
			$sql="select ltg.group_id as id from ltuser_group ltg,ltuser ltu where ltu.id=ltg.user_id and ltu.id=$uid";
			return $this->loadpage($sql, array ());
		}
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
				//从数据库中查询 ，并放置到memcache中
				$sql_id="select fids from LTUSER where regName='".$regName."' and userType='0'";
				$s = mssql_query($sql_id);
				$row = mssql_fetch_array($s);
				if($row){
					$fids=$row['fids'];
					//$memcached->delete($key);
					$memcached->set($key, '0'.$token.$fids,'32');

					return $fids;
				}

			}



		}else{

			//没有开启memcache    从数据库查询

			$sql_id="select fids from LTUSER where regName='".$regName."' and userType='0'";
			$s = mssql_query($sql_id);
			$row = mssql_fetch_array($s);

			if($row){
				$fids=$row['fids'];
			}
		}
		return $fids;
	}




}
?>

