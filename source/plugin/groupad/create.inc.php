<?php
/*
 * Create by lujianiqng
 * @2010-08-02
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// Load Plugin Module
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

$navs = array(
    array(url=>join_plugin_action2('index', array(select=>"index")), name=>"专区广告")
);

// 该函数与template/create/index.htm呼应
/*
 * @zic 2010-08-05
 * 更新单条广告记录前
 * 查询该条记录原始记录
 */
function index(){
    global $_G;
    // Initialize Data
    $id = $_GET['ad'];
   
    if(!is_null($id)){
        $db = DB::table("groupad");
        $showAttributes = "*";
        $query = "SELECT ".$showAttributes." FROM ".$db." WHERE id=".$id;

        $result = DB::query($query);
        $adEntity = DB::fetch($result);
        $init_date["adEntity"] = $adEntity;
        //return array("is_enable_category"=>$is_enable_category, "categorys"=>$categorys,"adEntity"=>$adEntity);
    }else{
        $init_date["adEntity"] = null;
        //return array("is_enable_category"=>$is_enable_category, "categorys"=>$categorys,"adEntity"=>null);
    }
    
    // 添加分类
    // from xinsen
    require_once libfile("function/category");
    $is_enable_category = false;
    $plugin_name = $_GET["plugin_name"];
    if(common_category_is_enable($_G["fid"], $plugin_name)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $plugin_name);
    }

    $init_date["is_enable_category"] = $is_enable_category;
    $init_date["categorys"] = $categorys;

    // 返回
    return $init_date;
}

/*
 * @zic 2010-08-05  Create
 * @zic 2010-08-13  Add 表单校验
 * 创建广告
 */
function create(){
	global $_G;

        // Initialize
        // 广告记录实体        
        $adEntity = array();

        $adEntity["title"] = $_POST["adnew_title"];
        $adEntity["ad_type"] = $_POST["adnew_type"];
        $adEntity["media_style"] = $_POST["adnew_style"];

        
        $return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=createmenu";
        
        if($adEntity["title"]==""){
            showmessage('未输入广告标题，创建失败', $return_url);
        }
        $adEntity["title"] = addslashes($adEntity["title"]);
       
        switch ($adEntity["media_style"]){
            case "text":
                $adEntity["content"] = $_POST["adnew_text_content"];
                $adEntity["media_url"] = $_POST["adnew_text_link"];
                if($adEntity["content"]==""){
                    showmessage('未输入文字内容，创建失败', $return_url);
                }
                $adEntity["content"] = addslashes($adEntity["content"]);
                
                if($adEntity["media_url"]==""){
                    showmessage('未输入文字链接，创建失败', $return_url);
                }
                $adEntity["media_url"] = urlstr($adEntity["media_url"]);
                break;
                
            case "image":
                $image_id = "TMPadvnewimage";       
                if (!$_POST[$image_id]){
                    $image_id = "adnewimage";
                    // 判断文件上传HTTP是否有错
                    if($_FILES[$image_id]['error']){
                        $msg = "Error: ".$_FILES[$image_id]['error']."<br />";
                        showmessage($msg, $return_url);
                    }

                   // 文件上传处理
                   require_once libfile('class/upload');
                   $upload = new discuz_upload();

                   // 判断文件上传初始化是否成功
		   if(!$upload->init($_FILES[$image_id], 'plugin_groupad',$_G['fid'])){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "init $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }

                   // 判断文件保存是否成功
                   if(!$upload->save()){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "save $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }
                    $target = $upload->attach['target'];
                    $url_arr = explode("./",$target);
                    $url = $url_arr[1].$url_arr[2];
                  
                    $adEntity["media_dir"] = $url;
                    
                    

                }else{
                    $adEntity["media_dir"] = $_POST["TMPadvnewimage"];
                    if($adEntity["media_dir"]==""){
                        showmessage('未上传/输入图片URL，创建失败', $return_url);
                    }                   

                }
                // 通用字段存储
                $adEntity["media_url"] = $_POST["adnew_image_link"];
                 if($adEntity["media_url"]==""){
                        showmessage('未指定图片链接，创建失败', $return_url);
                    }
                // url字符串处理
                $adEntity["media_url"] = urlstr($adEntity["media_url"]);
                $adEntity["content"] = $_POST["adnew_image_alt"];
                // 上传时未输入图片高度，宽度，自动填入图片原始大小
                $imgSize = getimagesize($adEntity["media_dir"]);
                $adEntity["width"] = $imgSize[0];
                $adEntity["height"] = $imgSize[1];
                /*
                if($_POST["adnew_image_width"] == 0 || $_POST["adnew_image_height"] == 0 ){
                        $imgSize = getimagesize($adEntity["media_dir"]);
                        $adEntity["width"] = $imgSize[0];
                        $adEntity["height"] = $imgSize[1];
                }else{
                    $adEntity["width"] = $_POST["adnew_image_width"];
                    $adEntity["height"] = $_POST["adnew_image_height"];
                }
                 *
                 */
                break;
             case "flash":
                 $flash_id = "TMPadvnewflash";
                 if (!$_POST[$flash_id]){
                     $flash_id = "adnewflash";
                    // 判断文件上传HTTP是否有错
                    if($_FILES[$flash_id]['error']){
                        $msg = "Error: ".$_FILES[$flash_id]['error']."<br />";
                        showmessage($msg, $return_url);
                    }
                    
                   // 文件上传处理
                   require_once libfile('class/upload');
                   $upload = new discuz_upload();
                   
                   // 判断文件上传初始化是否成功
		   if(!$upload->init($_FILES[$flash_id], 'plugin_groupad',$_G['fid'])){
                      
                       $errorcode = "file_upload_error_".$upload->error();                      
                       $msg = errorMsg($errorcode);                       
                       showmessage($msg, $return_url);
                   }
                   
                   // 判断文件保存是否成功                   
                   if(!$upload->save()){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "save $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }          
                    $target = $upload->attach['target'];
                    $url_arr = explode("./",$target);
                    $url = $url_arr[1].$url_arr[2];
                    //$url = $upload->type."/".$upload->attach['attachment'];
                    //echo $url;
                    //exit;
                    $adEntity["media_dir"] = $url;
                    
                }else{
                    $adEntity["media_dir"] = $_POST["TMPadvnewflash"];
                    //echo  $_POST["TMPadvnewflash"];
                    //exit;
                     if($adEntity["media_dir"]==""){
                        showmessage('未上传/输入flash，创建失败', $return_url);
                    }
                }
                // 上传时未输入图片高度，宽度，自动填入图片原始大小
                $imgSize = getimagesize($adEntity["media_dir"]);                
                $adEntity["width"] = $imgSize[0];
                $adEntity["height"] = $imgSize[1];

                /*
                 * 屏蔽原处理问题
                $adEntity["width"] = $_POST["adnew_flash_width"];
                $adEntity["height"] = $_POST["adnew_flash_height"];

                if($adEntity["width"]==""){
                    showmessage('未指定flash宽度，创建失败', $return_url);
                }
                if($adEntity["height"]==""){
                    showmessage('未指定flash高度，创建失败', $return_url);
                }
                 *
                 */
                break;
                
             default:
                 showmessage('没有该样式，创建失败', $return_url);
                 break;
             
        }
        
        
	$adEntity["group_id"] = $_G["fid"];
	$adEntity["uid"] = $_G['uid'];
// ---------------------- 广告实体组装完成 --------------------------------
        
        // 将记录插入数据库
        $db = "groupad";
        $insert_id = DB::insert($db, $adEntity, true);

        //$return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=groupmenu&groupad_action=index";
        $return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=groupmenu";
        showmessage('创建成功', $return_url);
}
/*
 * @zic 2010-8-17
 * 广告URL字符串格式化，存入MySQL;
 * @return 字符串
 */
function urlstr($str){
    // 去除首尾空格
    $str = trim($str);
    // 将字符串全部转为小写
    //$str = strtolower($str);

    if($str!="#"){
        // 取出url头部
        $cmphead7 = substr($str,0,7);
        $cmphead8 = substr($str,0,8);
        // 比较
        if($cmphead7 !== "http://" && $cmphead8 !== "https://"){
            $str = "http://".$str;
        }
        $str = addslashes($str);
    }
    return $str;
}

function errorMsg($errorcode){
    $lang = array(
	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。'
    );
    if(array_key_exists($errorcode,$lang)){
        $msg = $lang[$errorcode];
    }else{
        $msg = "上传失败！未知原因，请重新尝试或者联系服务中心。";
    }
    return $msg;
}
/*
 * @zic 2010-08-6
 * 更新单条Text广告记录
 * @zic 2010-08-16
 * 增加表单验证
 */
function update_text(){
        global $_G;

        // Initialize
        // 广告记录实体
        $id = $_POST["ad_id"];
        
        $data = array();
	$data["title"] = $_POST["adnew_title"];
        $data["ad_type"] = $_POST["adnew_type"];
        $data["content"] = $_POST["adnew_text_content"];
        $data["media_url"] = $_POST["adnew_text_link"];

        // 数据校验
        if($data["title"]==""){
            showmessage('未输入广告标题，创建失败', $return_url);
        }elseif($data["content"]==""){
            showmessage('未输入文字内容，创建失败', $return_url);
        }elseif($data["media_url"]==""){
            showmessage('未输入文字链接，创建失败', $return_url);
        }
        $adEntity["title"] = addslashes($adEntity["title"]);
        $adEntity["content"] = addslashes($adEntity["content"]);
        // url字符串处理
        $data["media_url"] = urlstr($data["media_url"]);
        // 拼装SQL
        $table = "groupad";
        $condition = "id = ".$id;
        
        // Updata Database
        $res = DB::update($table, $data, $condition);

        $return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=groupmenu";
        showmessage('更新成功', $return_url);

}

/*
 * @zic 2010-08-6
 * 更新单条Image广告记录
 * @zic 2010-08-17
 * 增加表单验证
 */
function update_image(){
        global $_G;

        // Initialize
        // 广告记录实体
        $id = $_POST["ad_id"];

        $data = array();
	$data["title"] = $_POST["adnew_title"];
        $data["ad_type"] = $_POST["adnew_type"];
        $data["content"] = $_POST["adnew_image_alt"];
        $data["media_url"] = $_POST["adnew_image_link"];
        $data["width"] = $_POST["adnew_image_width"];
        $data["height"] = $_POST["adnew_image_height"];

         // 数据校验
        if($data["title"]==""){
            showmessage('未输入广告标题，创建失败', $return_url);
        }elseif($data["media_url"]==""){
            showmessage('未指定图片链接，创建失败', $return_url);
        }
        // url字符串处理
        $data["media_url"] = urlstr($data["media_url"]);
        
        // 拼装SQL
        $table = "groupad";
        $condition = "id = ".$id;

        // Updata Database
        $res = DB::update($table, $data, $condition);

        $return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=groupmenu";
        showmessage('更新成功', $return_url);

}

/*
 * @zic 2010-08-17
 * 更新单条Flash广告记录
 * 增加表单验证
 */
function update_flash(){
        global $_G;

        // Initialize
        // 广告记录实体
        $id = $_POST["ad_id"];

        $data = array();
	$data["title"] = $_POST["adnew_title"];
        $data["ad_type"] = $_POST["adnew_type"];
        $data["width"] = $_POST["adnew_flash_width"];
        $data["height"] = $_POST["adnew_flash_height"];

         // 数据校验
        if($data["title"]==""){
            showmessage('未输入广告标题，创建失败', $return_url);
        }elseif($data["width"]==""){
            showmessage('未指定flash宽度，创建失败', $return_url);
        }elseif($data["height"]==""){
            showmessage('未指定flash高度，创建失败', $return_url);
        }
        // 拼装SQL
        $table = "groupad";
        $condition = "id = ".$id;

        // Updata Database
        $res = DB::update($table, $data, $condition);

        $return_url = "forum.php?mod=group&action=plugin&fid=".$_G["fid"]."&plugin_name=groupad&plugin_op=groupmenu";
        showmessage('更新成功', $return_url);
}

?>
