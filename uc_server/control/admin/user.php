<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 888 2008-12-16 02:07:15Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

define('UC_USER_CHECK_USERNAME_FAILED', -1);
define('UC_USER_USERNAME_BADWORD', -2);
define('UC_USER_USERNAME_EXISTS', -3);
define('UC_USER_EMAIL_FORMAT_ILLEGAL', -4);
define('UC_USER_EMAIL_ACCESS_ILLEGAL', -5);
define('UC_USER_EMAIL_EXISTS', -6);

define('UC_LOGIN_SUCCEED', 0);
define('UC_LOGIN_ERROR_FOUNDER_PW', -1);
define('UC_LOGIN_ERROR_ADMIN_PW', -2);
define('UC_LOGIN_ERROR_ADMIN_NOT_EXISTS', -3);
define('UC_LOGIN_ERROR_SECCODE', -4);
define('UC_LOGIN_ERROR_FAILEDLOGIN', -5);

class control extends adminbase {

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		if(getgpc('a') != 'login' && getgpc('a') != 'logout') {
			if(!$this->user['isfounder'] && !$this->user['allowadminuser']) {
				$this->message('no_permission_for_this_module');
			}
		}
		$this->load('user');
	}

	function onlogin() {
		$authkey = md5(UC_KEY.$_SERVER['HTTP_USER_AGENT'].$this->onlineip);

		$this->load('user');
		$username = getgpc('username', 'P');
		$password = getgpc('password', 'P');
		$iframe	  = getgpc('iframe');

		$isfounder = intval(getgpc('isfounder', 'P'));
		/*
		echo $sid = $this->sid_encode('admin');
		echo $this->sid_decode($sid);
		*/
		$rand = rand(100000, 999999);
		$seccodeinit = rawurlencode($this->authcode($rand, 'ENCODE', $authkey, 180));
		$errorcode = 0;
		if($this->submitcheck()) {
			$failedlogin = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."failedlogins WHERE ip='$this->onlineip'");
			if($failedlogin['count'] > 4) {
				if($this->time - $failedlogin['lastupdate'] < 15 * 60) {
					$errorcode = UC_LOGIN_ERROR_FAILEDLOGIN;
				} else {
					$expiration = $this->time - 15 * 60;
					$this->db->query("DELETE FROM ".UC_DBTABLEPRE."failedlogins WHERE lastupdate<'$expiration'");
				}
			} else {

				$seccodehidden = urldecode(getgpc('seccodehidden', 'P'));
				$seccode = strtoupper(getgpc('seccode', 'P'));
				$seccodehidden = $this->authcode($seccodehidden, 'DECODE', $authkey);
				require UC_ROOT.'./lib/seccode.class.php';
				seccode::seccodeconvert($seccodehidden);
				if(empty($seccodehidden) || $seccodehidden != $seccode) {
					$errorcode = UC_LOGIN_ERROR_SECCODE;
				} else {
					$errorcode = UC_LOGIN_SUCCEED;
					$this->user['username'] = $username;
					if($isfounder == 1) {
						$this->user['username'] = 'UCenterAdministrator';
						$md5password =  md5(md5($password).UC_FOUNDERSALT);
						if($md5password == UC_FOUNDERPW) {
							$username = $this->user['username'];
							$this->view->sid = $this->sid_encode($this->user['username']);
						} else {
							$errorcode = UC_LOGIN_ERROR_FOUNDER_PW;
						}
					} else {
						$admin = $this->db->fetch_first("SELECT a.uid,m.username,m.salt,m.password FROM ".UC_DBTABLEPRE."admins a LEFT JOIN ".UC_DBTABLEPRE."members m USING(uid) WHERE a.username='$username'");
						if(!empty($admin)) {
							$md5password =  md5(md5($password).$admin['salt']);
							if($admin['password'] == $md5password) {
								$this->view->sid = $this->sid_encode($admin['username']);
							} else {
								$errorcode = UC_LOGIN_ERROR_ADMIN_PW;
							}
						} else {
							$errorcode = UC_LOGIN_ERROR_ADMIN_NOT_EXISTS;
						}
					}

					if($errorcode == 0) {
						$this->setcookie('sid', $this->view->sid, 86400);
						$pwlen = strlen($password);
						$this->user['admin'] = 1;
						$this->writelog('login', 'succeed');
						if($iframe) {
							header('location: admin.php?m=frame&a=main&iframe=1'.($this->cookie_status ? '' : '&sid='.$this->view->sid));
							exit;
						} else {
							header('location: admin.php'.($this->cookie_status ? '' : '?sid='.$this->view->sid));
							exit;
						}
					} else {
						$this->writelog('login', 'error: user='.$this->user['username'].'; password='.($pwlen > 2 ? preg_replace("/^(.{".round($pwlen / 4)."})(.+?)(.{".round($pwlen / 6)."})$/s", "\\1***\\3", $password) : $password));
						if(empty($failedlogin)) {
							$expiration = $this->time - 15 * 60;
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."failedlogins WHERE lastupdate<'$expiration'");
							$this->db->query("INSERT INTO ".UC_DBTABLEPRE."failedlogins SET ip='$this->onlineip', count=1, lastupdate='$this->time'");
						} else {
							$this->db->query("UPDATE ".UC_DBTABLEPRE."failedlogins SET count=count+1,lastupdate='$this->time' WHERE ip='$this->onlineip'");
						}
					}
				}
			}
		}
		$username = htmlspecialchars($username);
		$password = htmlspecialchars($password);
		$this->view->assign('seccodeinit', $seccodeinit);
		$this->view->assign('username', $username);
		$this->view->assign('password', $password);
		$this->view->assign('isfounder', $isfounder);
		$this->view->assign('errorcode', $errorcode);
		$this->view->assign('iframe', $iframe);
		$this->view->display('admin_login');
	}

	function onlogout() {
		$this->writelog('logout');
		$this->setcookie('sid', '');
		header('location: admin.php');
	}

	function onadd() {
		if(!$this->submitcheck('submit')) {
			exit;
		}
		$username = getgpc('addname', 'P');
		$password =  getgpc('addpassword', 'P');
		$email = getgpc('addemail', 'P');

		if(($status = $this->_check_username($username)) < 0) {
			if($status == UC_USER_CHECK_USERNAME_FAILED) {
				$this->message('user_add_username_ignore', 'BACK');
			} elseif($status == UC_USER_USERNAME_BADWORD) {
				$this->message('user_add_username_badwords', 'BACK');
			} elseif($status == UC_USER_USERNAME_EXISTS) {
				$this->message('user_add_username_exists', 'BACK');
			}
		}
		if(($status = $this->_check_email($email)) < 0) {
			if($status == UC_USER_EMAIL_FORMAT_ILLEGAL) {
				$this->message('user_add_email_formatinvalid', 'BACK');
			} elseif($status == UC_USER_EMAIL_ACCESS_ILLEGAL) {
				$this->message('user_add_email_ignore', 'BACK');
			} elseif($status == UC_USER_EMAIL_EXISTS) {
				$this->message('user_add_email_exists', 'BACK');
			}
		}
		$uid = $_ENV['user']->add_user($username, $password, $email);
		$this->message('user_add_succeed', 'admin.php?m=user&a=ls');
	}

	function onls() {

		include_once UC_ROOT.'view/default/admin.lang.php';

		$status = 0;
		if(!empty($_POST['addname']) && $this->submitcheck()) {
			$this->check_priv();
			$username = getgpc('addname', 'P');
			$password =  getgpc('addpassword', 'P');
			$email = getgpc('addemail', 'P');

			if(($status = $this->_check_username($username)) >= 0) {
				if(($status = $this->_check_email($email)) >= 0) {
					$_ENV['user']->add_user($username, $password, $email);
					$status = 1;
					$this->writelog('user_add', "username=$username");
				}
			}
		}
		$this->view->assign('status', $status);

		if(!empty($_POST['delete'])) {
			$_ENV['user']->delete_user($_POST['delete']);
			$status = 2;
			$this->writelog('user_delete', "uid=".implode(',', $_POST['delete']));
		}
		$srchname = getgpc('srchname', 'R');
		$srchregdatestart = getgpc('srchregdatestart', 'R');
		$srchregdateend = getgpc('srchregdateend', 'R');
		$srchuid = intval(getgpc('srchuid', 'R'));
		$srchregip = trim(getgpc('srchregip', 'R'));
		$srchemail = trim(getgpc('srchemail', 'R'));

		$sqladd = '';
		if($srchname) {
			$sqladd .= " AND username LIKE '$srchname%'";
			$this->view->assign('srchname', $srchname);
		}
		if($srchuid) {
			$sqladd .= " AND uid='$srchuid'";
			$this->view->assign('srchuid', $srchuid);
		}
		if($srchemail) {
			$sqladd .= " AND email='$srchemail'";
			$this->view->assign('srchemail', $srchemail);
		}
		if($srchregdatestart) {
			$sqladd .= " AND regdate>'".strtotime($srchregdatestart)."'";
			$this->view->assign('srchregdatestart', $srchregdatestart);
		}
		if($srchregdateend) {
			$sqladd .= " AND regdate<'".strtotime($srchregdateend)."'";
			$this->view->assign('srchregdateend', $srchregdateend);
		}
		if($srchregip) {
			$sqladd .= " AND regip='$srchregip'";
			$this->view->assign('srchregip', $srchregip);
		}
		$sqladd = $sqladd ? " WHERE 1 $sqladd" : '';

		$num = $_ENV['user']->get_total_num($sqladd);
		$userlist = $_ENV['user']->get_list($_GET['page'], UC_PPP, $num, $sqladd);
		foreach($userlist as $key => $user) {
			$user['smallavatar'] = '<img src="avatar.php?uid='.$user['uid'].'&size=small">';
			$userlist[$key] = $user;
		}
		$multipage = $this->page($num, UC_PPP, $_GET['page'], 'admin.php?m=user&a=ls&srchname='.$srchname.'&srchregdate='.$srchregdate);

		$this->_format_userlist($userlist);
		$this->view->assign('userlist', $userlist);
		//$this->view->assign('apps', $this->cache['apps']);
		$adduser = getgpc('adduser');
		$a = getgpc('a');
		$this->view->assign('multipage', $multipage);
		$this->view->assign('adduser', $adduser);
		$this->view->assign('a', $a);
		$this->view->display('admin_user');

	}

	function onedit() {
		$uid = getgpc('uid');
		$status = 0;
		if(!$this->user['isfounder']) {
			$isprotected = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."protectedmembers WHERE uid = '$uid'");
			if($isprotected) {
				$this->message('user_edit_noperm');
			}
		}

		if($this->submitcheck()) {
			$username = getgpc('username', 'P');
			$newusername = getgpc('newusername', 'P');
			$password = getgpc('password', 'P');
			$email = getgpc('email', 'P');
			$delavatar = getgpc('delavatar', 'P');
			$rmrecques = getgpc('rmrecques', 'P');
			$sqladd = '';
			if($username != $newusername) {
				if($_ENV['user']->get_user_by_username($newusername)) {
					$this->message('admin_user_exists');
				}
				$sqladd .= "username='$newusername', ";
				$this->load('note');
				$_ENV['note']->add('renameuser', 'uid='.$uid.'&oldusername='.urlencode($username).'&newusername='.urlencode($newusername));
			}
			if($password) {
				$salt = substr(uniqid(rand()), 0, 6);
				$orgpassword = $password;
				$password = md5(md5($password).$salt);
				$sqladd .= "password='$password', salt='$salt', ";
				$this->load('note');
				$_ENV['note']->add('updatepw', 'username='.urlencode($username).'&password=');
			}
			if($rmrecques) {
				$sqladd .= "secques='', ";
			}
			if(!empty($delavatar)) {
				$_ENV['user']->delete_useravatar($uid);
			}

			$this->db->query("UPDATE ".UC_DBTABLEPRE."members SET $sqladd email='$email' WHERE uid='$uid'");
			$status = $this->db->errno() ? -1 : 1;
		}		
		$user = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."members WHERE uid='$uid'");
		$user['bigavatar'] = '<img src="avatar.php?uid='.$uid.'&size=big">';
		$user['bigavatarreal'] = '<img src="avatar.php?uid='.$uid.'&size=big&type=real">';
		$this->view->assign('uid', $uid);
		$this->view->assign('user', $user);
		$this->view->assign('status', $status);
		$this->view->display('admin_user');
	}


	function _check_username($username) {
		$username = addslashes(trim(stripslashes($username)));
		if(!$_ENV['user']->check_username($username)) {
			return UC_USER_CHECK_USERNAME_FAILED;
/*		} elseif($username != $_ENV['user']->replace_badwords($username)) {
			return UC_USER_USERNAME_BADWORD;*/
		} elseif($_ENV['user']->check_usernameexists($username)) {
			return UC_USER_USERNAME_EXISTS;
		}
		return 1;
	}

	function _check_email($email) {
		if(!$_ENV['user']->check_emailformat($email)) {
			return UC_USER_EMAIL_FORMAT_ILLEGAL;
		} elseif(!$_ENV['user']->check_emailaccess($email)) {
			return UC_USER_EMAIL_ACCESS_ILLEGAL;
		} elseif($this->settings['doublee'] && $_ENV['user']->check_emailexists($email)) {
			return UC_USER_EMAIL_EXISTS;
		} else {
			return 1;
		}
	}

	function _format_userlist(&$userlist) {
		if(is_array($userlist)) {
			foreach($userlist AS $key => $user) {
				$userlist[$key]['regdate'] = $this->date($user['regdate']);
			}
		}
	}

}



?>