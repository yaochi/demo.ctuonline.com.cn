<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_logging.php 11748 2010-06-12 05:40:23Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('NOROBOT', TRUE);

if(!in_array($_G['gp_action'], array('login', 'logout', 'seccode'))) {
	showmessage('undefined_action', NULL);
}

//默认为home.php
$url = "home.php";

//Add by lujianqing 2011/04/06
//根据sso回传到redirect参数，跳转到不同页面；
if(!is_null($_G['gp_redirect'])){
	$url=$_G['gp_redirect'].".php";	
}

$ctl_obj = new logging_ctl();
$method = 'on_'.$_G['gp_action'];
$ctl_obj->$method($url);

class  logging_ctl{

	var $var = null;

	function logging_ctl() {
		require_once libfile('function/misc');
		require_once libfile('function/login');
		loaducenter();
	}
	
	// CAS Logging Code
        // Add by Lujianqing
        // ---2010.7.25----------------
	function on_login($url) {
            
         global $_G;  
       
        //---CAS Client----------------
        // initialize phpCAS
        //require_once(dirname(dirname(dirname(__FILE__))) . "/api/cas/CAS.php");
        require_once DISCUZ_ROOT."/source/api/cas/CAS.php";
        // CAS认证function更换为可配置；
        // 配置文件在Config_global.php下[CONFIG SSO]
        phpCAS::client($_G['config']['sso']['server_version'],$_G['config']['sso']['hostname'],$_G['config']['sso']['server_port'],$_G['config']['sso']['server_uri']);
        //phpCAS::client(CAS_VERSION_2_0,'sso.myctu.cn',8443,'cas');
        // no SSL validation for the CAS server
        phpCAS::setNoCasServerValidation();
        //echo phpCAS::getVersion();       
        if(!phpCAS::isAuthenticated()){     
             phpCAS::forceAuthentication();
        }
        $casusername = phpCAS::getUser();
		// 将用户登录帐号写入cookie中，以便由上海电信
		// 2011-04-03
		setcookie("usrname",$casusername);
        //----CAS End-----------------------
        
        // 取出用户验证时输入url的地址
        if(isset($_G['cookie']['re_url'])){
            $url = $_G['cookie']['re_url'];
        }
        dsetcookie('re_url');
        // ------ 用户初始化 --------------------------------------------------------
        global $_G;
        $_G['uid'] = $_G['member']['uid'] = 0;
	$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
               
        require_once libfile("function/login");
        $result = casuserlogin($casusername);

        // CAS登录成功
        // ---2010.7.25----------------              
       if($result['status'] > 0) {
            setloginstatus($result['member'], $_G['gp_cookietime'] ? 2592000 : 0);
            DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='$_G[uid]'");
            $ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

            include_once libfile('function/stat');
            updatestat('login');
            updatecreditbyaction('daylogin', $_G['uid']);
            checkusergroup($_G['uid']);
            
           /*
            if($invite['id']) {
                    DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$username), array('id'=>$invite['id']));
                    updatestat('invite');
            }

            if($invite['uid']) {
                    require_once libfile('function/friend');
                    friend_make($invite['uid'], $invite['username'], false);
                    dsetcookie('invite_auth', '', -86400 * 365);
                    if($invite['appid']) {
                            updatestat('appinvite');
                    }
            }
            * 
            */
            // --获取用户真实姓名---------------------2010-08-28------
            // 写入$_G['cookie']['realusername']
            $realusername = trim($result['member_profile']['realname']);
            $name = "realusername";
            dsetcookie($name,$realusername);
            //--END--------------------------------------------------

            // --每天登录调用积分接口------------------2010-08-26------
            require_once libfile("function/credit");
            $uid = $_G["uid"];
            $action = "daylogin";
            $resourceId = $uid;
            //用户积分接口
            $credit = credit_create_credit_log($uid, $action, $resourceId);
            

            //--END--------------------------------------------------
            require_once(dirname(dirname(dirname(__FILE__)))."/function/function_org.php");
            // 缓存该用户所属公司和所属组织信息
            // 根据用户名获取该用户组织信息
            $org_id = get_org_id_by_user($casusername);
            $name = "org_id";
            // 缓存用户部门信息
            dsetcookie($name,$org_id);
            // 如果该用户有所在部门信息
            // 查询该用户公司组织信息
            if($org_id){
                $company_id = getParentGroupById($org_id);
                if(is_array($company_id)){
                    // 当前用户组织信息层数
                    $name = "company_num";
                    $value = count($company_id);
                    dsetcookie($name,$value);
                    // 当前用户各层组织信息
                    foreach($company_id as $key => $value){
                        switch ($key){
                            case 1:
                                $name = "group";
                                dsetcookie($name,$value);
                                break;
                            case 2:
                                $name = "province";
                                dsetcookie($name,$value);	
								//取得省公司的名称，王聪 修改ESNRFT-210  start
								//$provinceArray=getOrgById($value);		
								//dsetcookie("provinceName",$provinceArray[0]['name']);
								//取得省公司的名称，王聪 修改ESNRFT-210  end
                                break;
                            case 3:
                                $name = "municipality";
                                dsetcookie($name,$value);
                                break;
                        }

                    }
                }
            }
            include_once DISCUZ_ROOT.'./source/api/lt_org/memcacherole.php';
            $trole=new memcacherole();
            $ismanager=$trole->checkUserIsManager($_G[uid]);
            dsetcookie('validate_ismanager',$ismanager);


            // 加载调用接口文件
            // require_once(dirname(dirname(dirname(__FILE__)))."/function/function_org.php");
            // checkisExpertUser($casusername)
           
            require_once(dirname(dirname(dirname(__FILE__)))."/function/function_group.php");
            // 判断是否为专家用户
            //echo $_G['member']['uid'];
            //exit;
            
            if(check_is_expertuser($_G['member']['uid'])){   
                // 专家用户第一次登陆时，激活
                firstlogin_expertuser($_G['member']['uid']);
                // 查询该专家用户所属专区
                $expert_details = group_get_by_user($_G['member']['uid']);
                // 由于目前1个专家用户只能加入1个专区，所以只取出第一个专区进行跳转
                // fid - 专区id，name - 专区名
                $fid = $expert_details[0]['fid'];
                $fid_name = $expert_details[0]['name'];
                if(is_null($fid)){
                    // 引导外部专家用户退出
                    $url = $_G['siteurl']."member.php%3Fmod%3Dlogging%26action%3Dlogin";
                    showmessage('该登录帐号已被停用，请联系专区管理员……',$url);
                }
                $url = "forum.php?mod=group&fid=$fid";
                // 缓存在$_G
                $expertuser = array(
                    'Is' => true,
                    'fid' => $fid,
                    'fid_name' => $fid_name,
                    'url' => $url
                );

                foreach($expertuser as $key => $value){
                    $name = "expert_$key";
                    dsetcookie($name,$value);
                }
            }


            //$param = array('username' =>  $_G['cookie']['realusername'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['member']['uid']);
            //showmessage('login_succeed_cas', $url, $param);
            // 2011-04-02 Mod lujianqing
			// 页面不经过跳转
			header("Location: ".$url); 
             /*
            if(!empty($_G['inajax'])) {
                    $_G['setting']['msgforward'] = unserialize($_G['setting']['msgforward']);
                    $mrefreshtime = intval($_G['setting']['msgforward']['refreshtime']) * 1000;
                    loadcache('usergroups');
                    $usergroups = addslashes($_G['cache']['usergroups'][$_G['groupid']]['grouptitle']);
                    $message = 1;
                    include template('member/login');
            } else {
                    $param = array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['member']['uid']);
                    if($_G['groupid'] == 8) {
                            showmessage('login_succeed_inactive_member', 'home.php?mod=space&do=home', $param);
                    } else {
                            showmessage('login_succeed', $invite?'home.php?mod=space&do=home':dreferer(), $param);
                    }
            }
             *
             */
             
      }/*elseif($result['status'] == -1) {
                $auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
                $location = 'member.php?mod=register&action=activation&auth='.rawurlencode($auth);
                if($_G['inajax']) {
                        $message = 2;
                        include template('member/login');
                } else {
                        showmessage('login_activation', $location);
                }
       }*/else {              
                //$service["service"] = "https://sso.myctu.cn:8443/cas/login?service=http%3A%2F%2F218.83.175.179%2Fforum%2Fmember.php%3Fmod%3Dlogging%26action%3Dlogin";
                $service["service"] = "https://sso.myctu.cn:8443/cas/login?service=".$_G['siteurl']."member.php%3Fmod%3Dlogging%26action%3Dlogin";
                phpCAS::logout($service);

                 /*
                $password = preg_replace("/^(.{".round(strlen($_G['gp_password']) / 4)."})(.+?)(.{".round(strlen($_G['gp_password']) / 6)."})$/s", "\\1***\\3", $_G['gp_password']);
                $errorlog = dhtmlspecialchars(
                        TIMESTAMP."\t".
                        ($result['ucresult']['username'] ? $result['ucresult']['username'] : dstripslashes($_G['gp_username']))."\t".
                        $password."\t".
                        "Ques #".intval($_G['gp_questionid'])."\t".
                        $_G['clientip']);
                writelog('illegallog', $errorlog);
                loginfailed($_G['member_loginperm']);
                $fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_G['gp_questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
                showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm']));
                */
        }

}
	
    // Modify
    // --2010.7.27 annotated by lujianqing
    function on_logout() {
            global $_G;


            $ucsynlogout = $_G['setting']['allowsynlogin'] ? uc_user_synlogout() : '';
            
            /*
             // 防止重复退出
            if($_G['gp_formhash'] != $_G['formhash']) {
                 showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
            }            
           */
            // 社区退出,清除cookie
            foreach(array('sid', 'auth', 'visitedfid', 'onlinedetail', 'loginuser', 'activationauth', 'disableprompt', 'indextype') as $k) {
		dsetcookie($k);
            }

            $_G['uid'] = $_G['adminid'] = $_G['member']['credits'] = 0;
            $_G['username'] = $_G['member']['password'] = '';

            // 清除外部专家cookie
            // Add by lujianqing 20101008
            foreach(array('expert_Is', 'expert_fid', 'expert_fid_name', 'expert_url', 'loginuser') as $k) {
                    dsetcookie($k);
            }
             // 清除三级管理cookie
            foreach(array('validate_checked', 'validate_ismanager', 'validate_istop') as $k) {
                    dsetcookie($k);
            }

            // 清除登录时记录的URL
            dsetcookie('re_url');
            /* 暂时没法清除
            foreach($subgrouparray as $key=>$value){
						$name='orgidarray_'.$key;
						dsetcookie($name,$value[0]);

					}
            */
            $_G['groupid'] = $_G['member']['groupid'] = 7;
            $_G['uid'] = $_G['member']['uid'] = 0;
            $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
            $_G['setting']['styleid'] = $_G['setting']['styleid'];


            require_once DISCUZ_ROOT."/source/api/cas/CAS.php";
            phpCAS::client($_G['config']['sso']['server_version'],$_G['config']['sso']['hostname'],$_G['config']['sso']['server_port'],$_G['config']['sso']['server_uri']);
            $service["service"] = "https://sso.myctu.cn:8443/cas/login?service=".$_G['siteurl']."member.php%3Fmod%3Dlogging%26action%3Dlogin";
            phpCAS::logout($service);
        
        // -- END -------------------------------------------------------------------------------------------------
	//showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
		
	}        

        function clearcookies() {
	global $_G;
	foreach(array('sid', 'auth', 'visitedfid', 'onlinedetail', 'loginuser', 'activationauth', 'disableprompt', 'indextype') as $k) {
		dsetcookie($k);
	}
	$_G['uid'] = $_G['adminid'] = $_G['member']['credits'] = 0;
	$_G['username'] = $_G['member']['password'] = '';
        
        // 清除外部专家cookie
        // Add by lujianqing 20101008
        foreach(array('expert_Is', 'expert_fid', 'expert_fid_name', 'expert_url', 'loginuser') as $k) {
		dsetcookie($k);
	}
}
}

class discuz_logging_ctl {

	
	var $var = null;

	function logging_ctl() {
		require_once libfile('function/misc');
		require_once libfile('function/login');
		loaducenter();
		
	}

	function on_login() {
	
		global $_G;
		if($_G['uid']) {
			$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
			$param = array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['member']['uid']);
			showmessage('login_succeed', dreferer(), $param, array('showdialog' => 1, 'locationtime' => 1));
		}

		require_once libfile('function/login');

		if(!($_G['member_loginperm'] = logincheck())) {
			showmessage('login_strike');
		}

		$seccodecheck = $_G['setting']['seccodestatus'] & 2;
		$invite = getinvite();

		if(!submitcheck('loginsubmit', 1, $seccodecheck)) {

			$_G['referer'] = dreferer();

			$thetimenow = '(GMT '.($_G['setting']['timeoffset'] > 0 ? '+' : '').$_G['setting']['timeoffset'].') '.
				dgmdate(TIMESTAMP, 'u').

			$cookietimecheck = !empty($_G['cookie']['cookietime']) ? 'checked="checked"' : '';

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode{0} * 1000000;
			}

			$username = !empty($_G['cookie']['loginuser']) ? htmlspecialchars($_G['cookie']['loginuser']) : '';
	
			include template('member/login');

		} else {
		
			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			$result = userlogin($_G['gp_username'], $_G['gp_password'], $_G['gp_questionid'], $_G['gp_answer'], $_G['setting']['autoidselect'] ? 'auto' : $_G['gp_loginfield']);

			if($result['status'] > 0) {
				setloginstatus($result['member'], $_G['gp_cookietime'] ? 2592000 : 0);
				DB::query("UPDATE ".DB::table('common_member_status')." SET lastip='".$_G['clientip']."', lastvisit='".time()."' WHERE uid='$_G[uid]'");
				$ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

				include_once libfile('function/stat');
				updatestat('login');
				updatecreditbyaction('daylogin', $_G['uid']);
				checkusergroup($_G['uid']);
				if($invite['id']) {
					DB::update("common_invite", array('fuid'=>$uid, 'fusername'=>$username), array('id'=>$invite['id']));
					updatestat('invite');
				}
				if($invite['uid']) {
					require_once libfile('function/friend');
					friend_make($invite['uid'], $invite['username'], false);
					dsetcookie('invite_auth', '', -86400 * 365);
					if($invite['appid']) {
						updatestat('appinvite');
					}
				}

				if(!empty($_G['inajax'])) {
					$_G['setting']['msgforward'] = unserialize($_G['setting']['msgforward']);
					$mrefreshtime = intval($_G['setting']['msgforward']['refreshtime']) * 1000;
					loadcache('usergroups');
					$usergroups = addslashes($_G['cache']['usergroups'][$_G['groupid']]['grouptitle']);
					$message = 1;
					include template('member/login');
				} else {
					$param = array('username' => $_G['member']['username'], 'ucsynlogin' => $ucsynlogin, 'uid' => $_G['member']['uid']);
					if($_G['groupid'] == 8) {
						showmessage('login_succeed_inactive_member', 'home.php?mod=space&do=home', $param);
					} else {
						showmessage('login_succeed', $invite?'home.php?mod=space&do=home':dreferer(), $param);
					}
				}
			} elseif($result['status'] == -1) {
				$auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
				$location = 'member.php?mod=register&action=activation&auth='.rawurlencode($auth);
				if($_G['inajax']) {
					$message = 2;
					include template('member/login');
				} else {
					showmessage('login_activation', $location);
				}
			} else {
				$password = preg_replace("/^(.{".round(strlen($_G['gp_password']) / 4)."})(.+?)(.{".round(strlen($_G['gp_password']) / 6)."})$/s", "\\1***\\3", $_G['gp_password']);
				$errorlog = dhtmlspecialchars(
					TIMESTAMP."\t".
					($result['ucresult']['username'] ? $result['ucresult']['username'] : dstripslashes($_G['gp_username']))."\t".
					$password."\t".
					"Ques #".intval($_G['gp_questionid'])."\t".
					$_G['clientip']);
				writelog('illegallog', $errorlog);
				loginfailed($_G['member_loginperm']);
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_G['gp_questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm']));
			}

		}

	}
	
	function on_logout() {
		global $_G;

		$ucsynlogout = $_G['setting']['allowsynlogin'] ? uc_user_synlogout() : '';

		if($_G['gp_formhash'] != $_G['formhash']) {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
		}

		clearcookies();
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		$_G['setting']['styleid'] = $_G['setting']['styleid'];

		showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout));
	}
}

function clearcookies() {
	global $_G;
	foreach(array('sid', 'auth', 'visitedfid', 'onlinedetail', 'loginuser', 'activationauth', 'disableprompt', 'indextype') as $k) {
		dsetcookie($k);
	}
	$_G['uid'] = $_G['adminid'] = $_G['member']['credits'] = 0;
	$_G['username'] = $_G['member']['password'] = '';
}

?>