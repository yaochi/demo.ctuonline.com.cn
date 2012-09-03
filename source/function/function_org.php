<?php

function getOrgNameByUser($regname){
	global  $_G;
//	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
//	$usermgr = new User();
//	$org = $usermgr->getUserGroup($regname);

	require_once dirname(dirname(__FILE__))."/api/lt_org/memcacheUser.php";
	$usermgr = new memcacheUser();
	$org = $usermgr->getUserGroup($regname);
	
	if($org<=0){
		return false;
	}
	$orgname = $org[name];
	if($_G[config][misc][convercode]){
		$orgname = mb_convert_encoding($orgname, "UTF-8", "GBK");
	}
	return $orgname;
}

function get_org_by_user($regname){
	global  $_G;
//	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
//	$usermgr = new User();
//	$org = $usermgr->getUserGroup($regname);

	require_once dirname(dirname(__FILE__))."/api/lt_org/memcacheUser.php";
	$usermgr = new memcacheUser();
	$org = $usermgr->getUserGroup($regname);
	
	if($_G[config][misc][convercode]){
		$org[name] = mb_convert_encoding($org[name], "UTF-8", "GBK");
	}
	return $org;
}

function get_org_id_by_user($regname){
	global  $_G;
//	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
//	$usermgr = new User();
//	$org = $usermgr->getUserGroup($regname);

	require_once dirname(dirname(__FILE__))."/api/lt_org/memcacheUser.php";
	$usermgr = new memcacheUser();
	$org = $usermgr->getUserGroup($regname);
	if($org==-1){
		return false;
	}
	return $org[id];
}

/*
 *  用户所在省公司id
 */
function getUserGroupByuserId($userId){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
	$usermgr = new User();
	$org = $usermgr->getUserGroupByuserId($userId);
	$groupId=$org['id'];	
	unset ($usermgr);
	//print_r($groupId);
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$data = $groupMgr->getParentGroupById($groupId);
    if($data==0){
         return false;
     }else{
     foreach($data as $item){
         if($_G[config][misc][convercode]){
             $item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
          }
         $result[] = $item;
       }
     }
     $value = count($result);
     unset ($groupMgr);
     /**
      * 修正所在省公司，有些人是直接在集团下面的
      * 王聪
      */
     if($value>=3){
     	return $result[2]['name'];
     }else if($value>=2){
     	return $result[1]['name'];
     }else{
     	return false;
     }
     
	
}



function getSubOrg($parentId){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$data = $groupMgr->getGroupIdByParentId($parentId, "0");
	if($data==0){
         return false;
     }else{
     foreach($data as $item){
		if($_G[config][misc][convercode]){
			$item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
		}
		$result[] = $item[id];
	}
     }
	
	return $result;
}

/*
 * 查找某个公司下面的所有公司的id   ’1‘，’2‘
 * 王聪
 */
function getSubCor($parentId){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$data = $groupMgr->getIdsByParentId($parentId,"1","0");
	unset($groupMgr);
	return $data;
}


// 根据用户所属组织信息获取其公司信息
// Add by lujianqing 20101007
function getParentGroupById($groupId){
	global  $_G;
//	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
//	$groupMgr = new Group();
//	$data = $groupMgr->getParentGroupById($groupId);

	require_once dirname(dirname(__FILE__))."/api/lt_org/memcachegroup.php";
	$groupMgr = new memcachegroup();
	$org = $groupMgr->getParentGroupById($regname);
	
        if($data==0){
            return false;
        }else{
            foreach($data as $item){
                    if($_G[config][misc][convercode]){
                            $item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
                    }
                    $result[] = $item["id"];
            }
        }
	return $result;
}
function get_sub_org_ids($parent_id){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$data = $groupMgr->getGroupIdByParentId($parent_id, "0");
	foreach($data as $item){
		$result[] = $item[id];
	}
	return $result;
}
function getUserByOrg($orgid){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$result = array();
	$data = $groupMgr->getUserByGroupId($orgid, "0");
	if($data<=0){
		return false;
	}
	foreach($data as $item){
		if($_G[config][misc][convercode]){
			$item["regName"] = mb_convert_encoding($item["regName"], "UTF-8", "GBK");
			$item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
			$item["azUserId"] = mb_convert_encoding($item["azUserId"], "UTF-8", "GBK");
		}
		$result[] = $item;
	}
	return $result;
}

function getOrgById($orgid){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/group.php";
	$groupMgr = new Group();
	$data = $groupMgr->getGroupById($orgid);
	$result = array();
	foreach($data as $item){
		if($_G[config][misc][convercode]){
			$item["regName"] = mb_convert_encoding($item["regName"], "UTF-8", "GBK");
			$item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
			$item["tIndex"] = mb_convert_encoding($item["tIndex"], "UTF-8", "GBK");
			$item["TComment"] = mb_convert_encoding($item["TComment"], "UTF-8", "GBK");
		}
		$result[] = $item;
	}
	return $result;
}

function org_get_all_station($pageno){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/station.php";
	$stationMgr = new Station();
	$data = $stationMgr->GetAllstation($pageno, 10);
	$result = array();
	$result[count] = $data[1][0];
	foreach($data[0] as $item){
		if($_G[config][misc][convercode]){
			$item["s_name"] = mb_convert_encoding($item["s_name"], "UTF-8", "GBK");
		}
		$result[data][] = $item;
	}
	return $result;
}
function org_get_stattion($uid){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/station.php";
	$stationMgr = new Station();
	$data = $stationMgr->getStationByUser($uid);
	if($data<=0){
		return false;
	}
	foreach($data as $item){
		if($_G[config][misc][convercode]){
			$item[s_name] = mb_convert_encoding($item[s_name], "UTF-8", "GBK");
		}
		$return[] = $item;
	}
	return $return;
}

function getStationByUser($userid){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/station.php";
	 
	$stationManager = new Station();

	$data = $stationManager->getStationByUser($userid);
	if($data<=0){
		return false;
	}
	if($_G[config][misc][convercode]){
		foreach($data as $item){
			$item[s_name] = mb_convert_encoding($item[s_name], "UTF-8", "GBK");
			$return[] = $item;
		}
	}
	return $return;
}

function org_station_by_user($stationid){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/station.php";
	$stationMgr = new Station();
	$data = $stationMgr->getUserbystationId($stationid);
	foreach($data as $item){
		if($_G[config][misc][convercode]){
			$item[name] = mb_convert_encoding($item[name], "UTF-8", "GBK");
		}
		$return[] = $item;
	}
	return $return;
}

function create_expert_user($userarr,$creatorRegName){
	global  $_G;
	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
	$usermgr = new User();
	$result = $usermgr->createExpertUser($userarr,$creatorRegName);
	if($result<=0){		
		return false;
	} else {
		$userinfo = array();
        $userinfo['username'] = $result['regName'];
        $userinfo['realname'] = base64_decode($result['name']);
        $userinfo['password'] = $userinfo['username'];
        $userinfo['activelink'] = $result['furl'];
        $expertuid = $result['uid'];
		//测试
//		$userinfo['username'] = 'ZJ60000001';
//		$userinfo['realname'] = 'ZJ60000001';
//		$userinfo['password'] = $userinfo['username'];
//		$userinfo['activelink'] = 'test';
//		$expertuid = '60000001';
        $userinfo['email'] = $userinfo['username'].'@'.$userinfo['username'].'.com';
        $result = create_expert_user_step2($expertuid, $userinfo);
	}
	return $result;
}

function create_expert_user_step2($expertuid, $userinfo) {
	include_once dirname(dirname(dirname(__FILE__))).'/api/sso/expertuser.php';
	$newuser = new ExpertUser();
	$result = $newuser->create($expertuid, $userinfo);
	return array('result' => $result, 'expertuid' => $expertuid, 'username' => $userinfo['username'], 'activelink' => $userinfo['activelink']);
}

/*
 * 	修改 by songsp 2010-12-18 将处理放到/api/lt_org/user.php 中
 *  判断是否是专家用户
 *  是返回true,否者返回false
 */
function checkisExpertUser($regName){
	/*
	$mssql4zh=new mssql4lt();
	$sql_id="select fids from LTUSER where regName='".$regName."' and userType='0'";
	$query=$mssql4zh->Query($sql_id);
        
        // 只取一条记录
	$row=$mssql4zh->GetRow($query);
	$fids=$row['fids'];
    //echo $fids;
    return $fids;
    */
	
	//修改 by songsp  2010-12-18 改为memcache方式
//	require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
//	$usermgr = new User();
//	$fids = $usermgr->checkisExpertUser($regName);
	
	require_once dirname(dirname(__FILE__))."/api/lt_org/memcacheUser.php";
	$usermgr = new memcacheUser();
	$fids = $usermgr->checkisExpertUser($regName);

	
	return $fids;
}

function getUserGroupByUid($uid){
	if($uid){
		global  $_G;
		require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
		$usermgr = new User();
		$org = $usermgr->getUserGroupByUid($uid);
		
		if($org==-1){
			return false;
		}
	
		return $org[id];		
	}

}

?>