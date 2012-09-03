<?php

$method = strtolower($_SERVER["REQUEST_METHOD"]);
            
if ($method == "post") {
    $my_api_version = $_POST["v"];
    if ($my_api_version == 1) {
        require_once dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';
        $discuz = & discuz_core::instance();
        $discuz->init();

        require_once libfile("function/log");
        require_once libfile("function/group");
        require_once libfile("function/org");
        
        $uid = $_POST["uid"];
        $my_api_version = $_POST["v"];
        $oldorgid = $_POST["oldorgid"];
        $neworgid = $_POST["neworgid"];
        $nowtime = $_POST["nowtime"];
        $code = md5($uid.$oldorgid.$neworgid.$my_api_version.$nowtime);
        $securecode = $_POST["securecode"];
        $action = "org-user-api";
        $result = array();
        if ($code != $securecode) {
            $result["success"] = false;
            $result["message"] = base64_encode("校验码不正确");
            echo json_encode($result);
            common_log_create($action, serialize($result));
            exit;
        } else {
            if($oldorgid==0 && isset($neworgid) && isset($uid)){
            	$query = DB::query("SELECT uid, username FROM " . DB::table("common_member") . " WHERE uid=" . $uid . " LIMIT 1");
                $user = DB::fetch($query);
                if(!$user){
                    $result["success"] = false;
                    $result["message"] = base64_encode("用户信息不存在于社区数据库中");
                    echo json_encode($result);
                    common_log_create($action, serialize($result));
                    exit;
                }
                
            	//新用户加入一个机构，那么应该加用户到机构专区里面去，王聪注释，不仅仅要加入到自己的结构，还有所有的上级机构的专区
            	$company_id = getParentGroupById($neworgid);
            	if(is_array($company_id)){
            		 $name = "company_num";
                     $value = count($company_id);
            	     foreach($company_id as $key => $value){
                        switch ($key){
                            case 1:
                            	$neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                                
                                break;
                            case 2:
                                $neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);	
								break;
                            case 3:
                                $neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                                break;
                        }

                    }                    
            	}

               // $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
               // $newgroup = DB::fetch($query);
                
               // if(!$newgroup){
               //    $result["success"] = false;
               //    $result["message"] = base64_encode("组织机构不存在");
               //     echo json_encode($result);
               //     common_log_create($action, serialize($result));
               //     exit;
               // }
               // add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                
                $result["success"] = true;
                $result["message"] = base64_encode("新添加用户到专区");
                echo json_encode($result);
                common_log_create($action, serialize($result));
                exit;
            }

            if($neworgid==0 && isset($oldorgid) && isset($uid)){
            	 //用户从一个机构移出，需要把用户的相关结构的用户移出，王聪注释
                $query = DB::query("SELECT uid, username FROM " . DB::table("common_member") . " WHERE uid=" . $uid . " LIMIT 1");
                $user = DB::fetch($query);
               if(!$user){
                    $result["success"] = false;
                    $result["message"] = base64_encode("用户信息不存在于社区数据库中");
                    echo json_encode($result);
                    common_log_create($action, serialize($result));
                    exit;
                }
                
                
               $company_id = getParentGroupById($oldorgid);
            	if(is_array($company_id)){
            		 $name = "company_num";
                     $value = count($company_id);
            	     foreach($company_id as $key => $value){
                        switch ($key){
                            case 1:
                            	$oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                            case 2:
                                $oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                            case 3:
                                $oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                        }

                    }                    
            	}
                
               // $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
               // $oldgroup = DB::fetch($query);
                
                //if(!$oldgroup){
                //    $result["success"] = false;
                //    $result["message"] = base64_encode("组织机构不存在");
               //     echo json_encode($result);
               //     common_log_create($action, serialize($result));
               //     exit;
               // }
               // remove_user_from_group($user["uid"], $oldgroup["fid"]);
                $result["success"] = true;
                $result["message"] = base64_encode("从专区移除用户");
                echo json_encode($result);
                common_log_create($action, serialize($result));
                exit;
            }

            if(isset($neworgid)&& isset($oldorgid) && isset($uid)){
            	//用户从一个机构变化到另外的一个机构，需要删除用户原来在的机构专区，加入到新的机构专区当中，王聪注释
                $query = DB::query("SELECT uid, username FROM " . DB::table("common_member") . " WHERE uid=" . $uid . " LIMIT 1");
                $user = DB::fetch($query);
                if(!$user){
                    $result["success"] = false;
                    $result["message"] = base64_encode("用户信息不存在于社区数据库中");
                    echo json_encode($result);
                    common_log_create($action, serialize($result));
                    exit;
                }
                
                $company_id = getParentGroupById($oldorgid);
                //先移出用户
            	if(is_array($company_id)){
            		 $name = "company_num";
                     $value = count($company_id);
            	     foreach($company_id as $key => $value){
                        switch ($key){
                            case 1:
                            	$oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                            case 2:
                                $oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                            case 3:
                                $oldorgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
                                $oldgroup = DB::fetch($query);
                
				                if(!$oldgroup){
				                    break;
				                }
               					remove_user_from_group($user["uid"], $oldgroup["fid"]);
                                break;
                        }

                    }                    
            	}
                
            	
            	//把用户加到新的机构专区
                if(is_array($company_id)){
            		 $name = "company_num";
                     $value = count($company_id);
            	     foreach($company_id as $key => $value){
                        switch ($key){
                            case 1:
                            	$neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                                
                                break;
                            case 2:
                                $neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);	
								break;
                            case 3:
                                $neworgid=$value;
                                $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
                                $newgroup = DB::fetch($query);
                
				                if(!$newgroup){
				                    break;
				                }
               					add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                                break;
                        }

                    }                    
            	  }
            	
               // $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $oldorgid . " LIMIT 1");
               // $oldgroup = DB::fetch($query);
               // if(!$oldgroup){
                //    $result["success"] = false;
                //    $result["message"] = base64_encode("用户之前所在的组织机构不存在");
                //    echo json_encode($result);
                //    common_log_create($action, serialize($result));
                //    exit;
               // }
                
                
               // $query = DB::query("SELECT * FROM " . DB::table("forum_forum") . " WHERE org_id=" . $neworgid . " LIMIT 1");
               // $newgroup = DB::fetch($query);
                //if(!$newgroup){
                //    $result["success"] = false;
                //    $result["message"] = base64_encode("用户要加入的组织机构不存在");
                //    echo json_encode($result);
                //    common_log_create($action, serialize($result));
                //    exit;
               // }

                //开始加入
                //remove_user_from_group($user["uid"], $oldgroup["fid"]);
                //add_user_to_group($user["uid"], $user["username"], $newgroup["fid"]);
                
                $result["success"] = true;
                $result["message"] = base64_encode("修改成功");
                echo json_encode($result);
                common_log_create($action, serialize($result));
            }
        }
    }
}

function add_user_to_group($uid, $username, $fid){
    $query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("forum_groupuser")." WHERE fid=".$fid." AND uid=".$uid);
    $count = DB::fetch($query);
    if($count && $count["c"]!=0){
        return false;
    }
    
    $modmember = 4;
    DB::query("INSERT INTO " . DB::table('forum_groupuser') . " (fid, uid, username, level, joindateline, lastupdate)
           VALUES ('$fid', '$uid', '$username', '$modmember', '" . TIMESTAMP . "', '" . TIMESTAMP . "')", 'UNBUFFERED');
    update_usergroups($uid);
    DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+1 WHERE fid='$fid'");
    updateactivity($fid, 0);
    include_once libfile('function/stat');
    updatestat('groupjoin');
    delgroupcache($fid, array('activityuser', 'newuserlist'));
    return true;
}

function remove_user_from_group($uid, $fid){
    DB::query("DELETE FROM " . DB::table('forum_groupuser') . " WHERE fid='$fid' AND uid='$uid'");
    DB::query("UPDATE " . DB::table('forum_forumfield') . " SET membernum=membernum+'-1' WHERE fid='$fid'");
    update_usergroups($uid);
    delgroupcache($fid, array('activityuser', 'newuserlist'));
}
?>
