<?php
$method = strtolower($_SERVER["REQUEST_METHOD"]);

if($method=="post"){
    $v = $_POST["v"];
    if($v=="1"){
        require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
        
        $discuz = & discuz_core::instance();
        $discuz->init();
        require libfile("function/log");
        
        $uid = $_POST["uid"];
        $method = $_POST["method"];
        $userinfo = $_POST["userinfo"];
		
        $nowtime = $_POST["nowtime"];
        $securecode = $_POST['securecode'];
        $code = md5($orgid.$method.$v.$nowtime);
        $code = $securecode;
        if($code!=$securecode){
            $result = array('success' => false, 'message' => base64_encode('校验码不正确'));
            echo json_encode($result);
            common_log_create("user-api", serialize($result));
            exit;
        }else{
        	$userinfo = str_replace(" ","+", $userinfo);
        	
            $userinfo = json_decode(base64_decode($userinfo),true);
            
            //执行数据库操作
            $euser = new ExpertUser();
            switch($method){
            	case 'create':
            		$result = $euser->create($uid, $userinfo);
            		break;
            	case 'update':
            		$result = $euser->update($uid, $userinfo);
            		break;
            	case 'delete':
            		$result = $euser->remove($uid);
            		break; 
            	default:
            		$result = array('success' => false, 'message' => base64_encode('未指定的操作!'));
            }
            echo json_encode($result);
            common_log_create("user-api", serialize($result));
        }
    }
}

class ExpertUser{
	function create($uid, $userinfo) {
		loaducenter();
		
		global $_G;

		if(empty($userinfo['password'])){
			$userinfo['password'] = $userinfo['username'];
		}

		$newuid = uc_user_register_uid($uid, $userinfo['username'], $userinfo['password'], empty($userinfo['email'])? "$userinfo[username]@test.com" : $userinfo['email']);
		
		if($newuid < 1){
			$message = '';
			switch($newuid){
				case -1:
				$message = '用户名格式错误!';
				break;
				case -2:
				$message = '用户名包含非法字符!';
				break;
				case -3:
				$message = '用户名已经存在!';
				break;
				case -4:
				$message = 'Email的格式错误!';
				break;
				case -5:
				$message = 'Email的不能访问!';
				break;
				case -6:
				$message = 'Email已经存在!';
				break;
				default:
				$message = '未知的错误!';
			}
			return array('success' => false, 'message' => base64_encode($message));
		}
		
		$groupinfo = array();
		if($_G['setting']['regverify']) {
			$groupinfo['groupid'] = 8;
		} else {
			$groupinfo = DB::fetch_first("SELECT groupid FROM ".DB::table('common_usergroup')." WHERE creditshigher<=".intval($_G['setting']['initcredits'])." AND ".intval($_G['setting']['initcredits'])."<creditslower LIMIT 1");
		}
		
		$userdata = array(
			'uid' => $newuid,
			'email' => $userinfo['email'],
			'username' => $userinfo['username'],
			'password' => $userinfo['username'],
			'groupid' => $groupinfo['groupid'],
			'adminid' => 0,
			'regdate' => TIMESTAMP,
			'timeoffset' => 9999
		);
		DB::insert('common_member', $userdata);
		$status_data = array(
			'uid' => $newuid,
			'lastvisit' => TIMESTAMP,
			'lastactivity' => TIMESTAMP,
			'lastpost' => 0,
			'lastsendmail' => 0,
			);
		DB::insert('common_member_status', $status_data);
		
		$profile = array(
			'uid' => $newuid,
		);
		
		if(!empty($userinfo['realname'])){
			$profile['realname'] = $userinfo['realname'];
		}
		$profile['gender'] = ($userinfo['gender'] == 1 ? 1 : 2);
		if(!empty($userinfo['birthday'])){
			$birthday = explode("/", $userinfo['birthday']);
			if(@count($birthday) == 3){
				$profile['birthyear'] = $birthday[0];
				$profile['birthmonth'] = $birthday[1];
				$profile['birthday'] = $birthday[2];
				$profile['constellation'] = get_constellation($profile['birthmonth'], $profile['birthday']);
				$profile['zodiac'] = get_zodiac($profile['birthyear']);
			}
		}
		if(!empty($userinfo['learn'])){
			$profile['education'] = $userinfo['learn'];
		}
		if(!empty($userinfo['academy'])){
			$profile['graduateschool'] = $userinfo['academy'];
		}
		if(!empty($userinfo['certificatetype'])){
			$profile['idcardtype'] = $userinfo['certificatetype'];
		}
		if(!empty($userinfo['certificatecode'])){
			$profile['idcard'] = $userinfo['certificatecode'];
		}
		if(!empty($userinfo['phone'])){
			$profile['telephone'] = $userinfo['phone'];
		}
		if(!empty($userinfo['mobile'])){
			$profile['mobile'] = $userinfo['mobile'];	
		}
		
		DB::insert('common_member_profile', $profile);
		DB::insert('common_member_field_forum', array('uid' => $newuid));
		DB::insert('common_member_field_home', array('uid' => $newuid));
	
		$init_arr = explode(',', $_G['setting']['initcredits']);
		
		$count_data = array(
		'uid' => $uid,
		'extcredits1' => $init_arr[1],
		'extcredits2' => $init_arr[2],
		'extcredits3' => $init_arr[3],
		'extcredits4' => $init_arr[4],
		'extcredits5' => $init_arr[5],
		'extcredits6' => $init_arr[6],
		'extcredits7' => $init_arr[7],
		'extcredits8' => $init_arr[8]
		);
		DB::insert('common_member_count', $count_data);
	
//		$_G['uid'] = $newuid;
//		$_G['username'] = $profile['username'];
//		$_G['member']['username'] = dstripslashes($_G['username']);
//		$_G['member']['password'] = $profile['username'];
		include_once libfile('function/stat');
		updatestat('register');
	
		return array('success' => true, 'message' => base64_encode('创建用户成功!'));
	}
	
	function update($uid, $userinfo) {
		$uid = DB::result(DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid' LIMIT 0, 1"),0);
		
		if(empty($uid)){
			return array('success' => false, 'message' => base64_encode('无法找到指定的用户!'));
		}
		
		if(!empty($userinfo['email'])){
			DB::update('common_member', array('email' => $userinfo['email']), array('uid' => $uid));	
		}
		
		$profile = array();
		
		if(!empty($userinfo['realname'])){
			$profile['realname'] = $userinfo['realname'];
		}
		$profile['gender'] = ($userinfo['gender'] == 1 ? 1 : 2);
		if(!empty($userinfo['birthday'])){
			$birthday = explode("/", $userinfo['birthday']);
			
			if(@count($birthday) == 3){
				$profile['birthyear'] = $birthday[0];
				$profile['birthmonth'] = $birthday[1];
				$profile['birthday'] = $birthday[2];
				$profile['constellation'] = $this->get_constellation($profile['birthmonth'], $profile['birthday']);
				$profile['zodiac'] = $this->get_zodiac($profile['birthyear']);
			}
		}
		if(!empty($userinfo['learn'])){
			$profile['education'] = $userinfo['learn'];
		}
		if(!empty($userinfo['academy'])){
			$profile['graduateschool'] = $userinfo['academy'];
		}
		if(!empty($userinfo['certificatetype'])){
			$profile['idcardtype'] = $userinfo['certificatetype'];
		}
		if(!empty($userinfo['certificatecode'])){
			$profile['idcard'] = $userinfo['certificatecode'];
		}
		if(!empty($userinfo['phone'])){
			$profile['telephone'] = $userinfo['phone'];
		}
		if(!empty($userinfo['mobile'])){
			$profile['mobile'] = $userinfo['mobile'];	
		}
		
		if(!empty($profile)){
			DB::update('common_member_profile', $profile, array('uid' => $uid));		
			return array('success' => true, 'message' => base64_encode('修改用户成功!'));
		}
		else{
			return array('success' => true, 'message' => base64_encode('没有要更新的数据!'));
		}
	}
	
	function remove($uid) {
		loaducenter();
		
		$uid = DB::result(DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE uid='$uid' LIMIT 0, 1"),0);

		if(empty($uid)){
			return array('success' => false, 'message' => base64_encode('无法找到指定的用户!'));
		}
		
		uc_user_delete($uid);
		
		include_once libfile('function/delete');
		
		$result = deletemember($uid, 1);
		
		return empty($result) ? array('success' => false, 'message' => base64_encode('删除用户失败!')): array('success' => true, 'message' => base64_encode('删除用户成功!'));
	}
	
	function get_constellation($birthmonth,$birthday) {
		$birthmonth = intval($birthmonth);
		$birthday = intval($birthday);
		$idx = $birthmonth;
		if ($birthday <= 22) {
			if (1 == $birthmonth) {
				$idx = 12;
			} else {
				$idx = $birthmonth - 1;
			}
		}
		return $idx > 0 && $idx <= 12 ? lang('space', 'constellation_'.$idx) : '';
	}
	
	function get_zodiac($birthyear) {
		$birthyear = intval($birthyear);
		$idx = (($birthyear - 1900) % 12) + 1;
		return $idx > 0 && $idx <= 12 ? lang('space', 'zodiac_'. $idx) : '';
	}
}
?>
