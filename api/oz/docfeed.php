<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-19
 */
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
        $info = $_POST["info"];
		
        $nowtime = $_POST["nowtime"];
        $securecode = $_POST['securecode'];
        $code = md5($orgid.$method.$v.$nowtime);
        $code = $securecode;
        
        if(empty($uid)){
        	$result = array('success' => false, 'message' => base64_encode('用户不正确'));
            echo json_encode($result);
            common_log_create("docfeed-api", serialize($result));
            exit;
        }elseif($code!=$securecode){
            $result = array('success' => false, 'message' => base64_encode('校验码不正确'));
            echo json_encode($result);
            common_log_create("docfeed-api", serialize($result));
            exit;
        }else{
        	$info = str_replace(" ","+", $info);
        	
            $info = json_decode(base64_decode($info),true);
            
            //执行数据库操作
            switch($method){
            	case 'add':
            		require_once libfile("function/feed");
            		
            		$username = DB::result_first("SELECT username FROM ".DB::table('common_member')." WHERE uid='$uid'");
            		feed_add('doc', 'feed_doc_title', array('title' => $info['title']), 'feed_doc_body', array('body' => $info['body']), '', array(), array(),'','','',0,$info['id'], 'docid', $uid, $username);
            	
            		$result = array('success' => true, 'message' => '');
            		break;
            	default:
            		$result = array('success' => false, 'message' => base64_encode('未指定的操作!'));
            }
            echo json_encode($result);
            common_log_create("docfeed-api", serialize($result));
        }
    }
}
?>
