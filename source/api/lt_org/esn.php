
<?php
class ESN {

	/* 查找数据库公用方法 */
	function listpage($sql, $obj) {
		if (empty ($sql)) {
			return;
		}
		mssql_connect("192.168.0.20", "sa", "sa");
		mssql_select_db("ESN");

		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if ($info == False) {
			return "没有查找到数据";
		} else {
			do {
				$obj[] = $info;
				//每一行
			} while ($info = mssql_fetch_array($s));

			mssql_close();
		}

		return $obj;
	}

	/* 查找部门列表 */
	function listOrganization($groupId) {
		$result = "";
		mssql_connect("192.168.0.20", "sa", "sa");
		mssql_select_db("ESN");
		$sql = "select parentID from LTGroup where id = " . $groupId;
		$s = mssql_query($sql);
		$info = mssql_fetch_array($s);
		if ($info == False) {
			return $result;
		} else {
			do {
				$obj[] = $info;

				if ($info[parentID] == -1) {
					return $result;
				} else {
					$result .= $this->listOrganization($info[parentID]);
				}
			} while ($info = mssql_fetch_array($s));

			mssql_close();
		}
		return $result;

	}

	/* 查找职位列表 */
	function listStation($name, $code, $currentPage, $pageSize) {
		if (empty ($currentPage) or $currentPage == 0) {
			$currentPage = 1;
		}
		if (empty ($pageSize) or $pageSize == 0) {
			$pageSize = 20;
		}
		$sql = "select top " . $pageSize . " s_id,s_code,s_name,parent_id from LTStation where ";
		$sqlWhere = "1=1 ";
		if (!empty ($name)) {
			$sqlWhere .= "and name like '%" . $name . "%'";
		}
		if (!empty ($code)) {
			$sqlWhere .= "and code ='" . $code . "'";
		}
		$sql .= $sqlWhere . " and s_id not in (select top " . ($currentPage != 0 ? $currentPage -1 : $currentPage) * $pageSize . " s_id from LTStation where  " . $sqlWhere;
		if (!empty ($order)) {
			$sql .= " order by " . $order . " ) order by " . $order;
		} else {
			$sql .= " ) ";
		}
		return $this->listpage($sql, array ());
	}

	/* 查找某个实体 */
	function loadDetail($obj, $id) {
		$sql = "select * from " . $obj . " where id='" . $id . "'";
		$this->listpage($sql, $obj);
	}

	/* 根据部门查找用户,根据用户查找部门 */
	function listUserInGroup($groupid, $user_id, $currentPage, $pageSize) {
		$primaryKey = "user_group.id";
		$sql = "select top " . $pageSize . "user1.*,group1.* from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where ";
		$sqlWhere = " user1.id = user_group.User_ID and user_group.group_id=group1.id ";
		if (!empty ($groupid)) {
			$sqlWhere .= "and user_group.Group_ID ='" . $groupid . "'";
		}
		if (!empty ($user_id)) {
			$sqlWhere .= " and user_group.user_ID='" . $user_id . "'";
		}
		$sql .= $sqlWhere . " and " . $primaryKey . " not in ( select top " . ($currentPage != 0 ? $currentPage -1 : $currentPage) * $pageSize . " " . $primaryKey . " from LTUSER user1 , LTUSER_GROUP user_group,LTGroup group1 where  " . $sqlWhere;
		if (!empty ($order)) {
			$sql .= " order by " . $order . " ) order by " . $order;
		} else {
			$sql .= " ) ";
		}
		$this->listpage($sql, "tuser");

	}
	/*
	 * 根据部门查找该部门下的所有用户
	 *
	 * */
	function getuserOrg($ip, $dbuser, $dbpasswd, $uid,$str) {
		mssql_connect($ip, $dbuser, $dbpasswd);
		mssql_select_db("ESN");
		$sql = "select  distinct user_id from LTUSER_GROUP where group_id in (select group_id from LTUSER_GROUP where user_id=$uid) and user_id not in($str) and user_id!=$uid";
		$r = mssql_query($sql);
		$info = mssql_fetch_array($r);
		if($info){
			while($info = mssql_fetch_array($r)){
                  $value[uid]=$info[user_id];
                  $userdata[]=$value;
			}
			return $userdata;
		}
		else {
		$sql = "select distinct user_id from LTUSER_GROUP where group_id in (select parentid from LTGROUP where id in(select group_id from LTUSER_GROUP where user_id=$uid)) and user_id not in($str) and user_id!=$uid";
		$r = mssql_query($sql);
		$info = mssql_fetch_array($r);
		if($info){
			while($info = mssql_fetch_array($r)){
                  $value[uid]=$info[user_id];
                  $userdata[]=$value;
			}
			return $userdata;
		}
		}
		}
	}
?>

