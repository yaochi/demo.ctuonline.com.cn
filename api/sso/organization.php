<?php
$method = strtolower($_SERVER["REQUEST_METHOD"]);

if($method=="post"){
    $my_api_version = $_POST["v"];
    if($my_api_version == 1){
        require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
        $discuz = & discuz_core::instance();
        $discuz->init();

        require_once libfile("function/log");
        $orgid = $_POST["orgid"];
        $method = $_POST["method"];
        $my_api_version = $_POST["v"];
        $userinfo = $_POST["orgrinfo"];
        $nowtime = $_POST["nowtime"];
        $securecode = $_POST["securecode"];
        $action = "org-api";
        $code = md5($orgid.$method.$my_api_version.$nowtime);
        if($code!=$securecode){
            $result["success"] = false;
            $result["message"] = base64_encode("校验码不正确");
            echo json_encode($result);
            common_log_create($action, serialize($result));
            exit;
        }else{
            if(!isset($orgid)){
                $result["success"] = false;
                $result["message"] = base64_encode("组织机构编号不能为空");
                echo json_encode($result);
                common_log_create($action, serialize($result));
                exit;
            }
            //删除组织机构缓存
            DB::query("DELETE FROM ".DB::table("common_syscache")." WHERE cname='orgtree'");
            $result["success"] = true;
            $result["message"] = base64_encode("更新成功");
            echo json_encode($result);
        }
    }
}
?>
