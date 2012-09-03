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
            common_log_create("empirical-api", serialize($result));
            exit;
        }elseif($code!=$securecode){
            $result = array('success' => false, 'message' => base64_encode('校验码不正确'));
            echo json_encode($result);
            common_log_create("empirical-api", serialize($result));
            exit;
        }else{
        	$info = str_replace(" ","+", $info);
        	
            $info = json_decode(base64_decode($info),true);
            
            //执行数据库操作
            switch($method){
            	case 'add':
            		if(empty($info['op_setting']) || !in_array($info['op_setting'], array('doc_upload','doc_read','doc_dowland','doc_dowland_add','doc_delete','resource_doc_dowland','resource_read','resource_class_unicast'))){
            			$result = array('success' => false, 'message' => base64_encode('未指定的经验值操作'));
            			break;
            		}
            	
            		//经验值
					require_once libfile('function/group');
					group_add_empirical_by_setting($uid, $info['zoneid'], $info['op_setting']);
            	
            		$result = array('success' => true, 'message' => '');
            		break;
            	default:
            		$result = array('success' => false, 'message' => base64_encode('未指定的操作!'));
            }
            echo json_encode($result);
            common_log_create("empirical-api", serialize($result));
        }
    }
}
?>
