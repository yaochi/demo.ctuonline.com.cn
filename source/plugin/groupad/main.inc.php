<?php
/*
 * Create by lujianiqng
 * @2010-08-02
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
    global $_G;
    // Initialize Data
    // 获取当前专区id
    $groupid = $_GET["fid"];
    $db = DB::table("groupad");
    $showAttributes = "id, display_order, is_display, title, ad_type, media_style";

    /*
     * 判断是否添加筛选条件
     * 不支持复合筛选
     * @zic 2010-08-06
     */
    $is_display = $_GET["is_display"];
    $ad_type = $_GET["ad_type"];
    $addfilter = "";
    if(!is_null($is_display)){
        $addfilter = $addfilter." AND is_display = ".$is_display;
    }elseif(!is_null($ad_type)){
        $addfilter = $addfilter." AND ad_type = ".$ad_type;
    }
    $init_date["is_display"] = $is_display;
    $init_date["ad_type"] = $ad_type;

    /*
     * 判断添加排序条件
     * 不支持符合排序
     * @zic 2010-08-17
     */
    $display_order =$_GET["display_order"];
    $update_time =$_GET["update_time"];
    $addOrder = "";
    // 前端判断用
    // 页面上单独与字符串比较有问题，可能与字符串输出有关
    if(is_null($display_order) && is_null($update_time)){
        $update_time = "desc";
        $addOrder = " update_time $update_time";
        $display_order = "";
    }elseif(!is_null($display_order)){
        $addOrder = " display_order $display_order";
        $update_time = "";
    }else{
        $addOrder = " update_time $update_time";
        $display_order = "";
    }
    $init_date["display_order"] = $display_order;
    $init_date["update_time"] = $update_time;

    /*
     * 分页显示记录
     * @zic 2010-08-06
     */
    // 每页显示记录条数
    $perpage = 20;
    // 当前页
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $perpage;
    // 获取所有记录条数
    $getcount = count_ad($db, $groupid, $addfilter);
    // 点击数字的链接地址
    $url = "forum.php?mod=group&action=plugin&fid=".$groupid."&plugin_name=groupad&plugin_op=groupmenu";
    if($getcount) {
	$multipage = multi($getcount, $perpage, $page, $url);
    }
    $init_date["multipage"] = $multipage;

    // 组装Sql查询语句
    //$query = "SELECT ".$showAttributes." FROM ".$db." WHERE group_id=".$groupid." ORDER BY update_time DESC";
    $query = "SELECT $showAttributes FROM $db WHERE group_id= $groupid $addfilter ORDER BY is_display DESC, $addOrder LIMIT $start,$perpage";
    //echo $query;
    //exit;
    $result = DB::query($query);
    // 获取结果数
    $num = DB::num_rows($result);
    // 将结果取出放在一个array中
    $adEntitys = array();
    for ($i=0; $i<$num; $i++){
        $adEntitys[$i] = DB::fetch($result);
        // 删除标题字数超过$content_len的字，并以...代替
        // 2010-8-15
        $content_len = 10;
        $content = $adEntitys[$i]["title"];
        $content = utf8Substr($content,0,$content_len);
        if($content!=$adEntitys[$i]["title"]){
            $content = "$content...";
        }
        $adEntitys[$i]["title"] = $content;
    }
    $init_date["adEntitys"] = $adEntitys;

    // 添加分类
    // from xinsen
    require_once libfile("function/category");
    $is_enable_category = false;
    $plugin_name = $_GET["plugin_name"];
    if(common_category_is_enable($_G["fid"], $plugin_name)){
        $is_enable_category = true;
        // 根据组件名和专区id，获取该专区组件的所有分类;
        $categorys = common_category_get_category($_G["fid"], $plugin_name);
    }

    $init_date["is_enable_category"] = $is_enable_category;
    $init_date["categorys"] = $categorys;
	$init_date["getcount"] = $getcount;
    //print_r($init_date);
    return $init_date;
}

// 获取$groupid专区中创建的AD记录数
function count_ad($db, $groupid, $addsql){
     $query = "SELECT count(*) FROM ".$db." WHERE group_id=".$groupid.$addsql;
     return DB::result_first($query, 0);
}

function list_update(){
    
    // Initialize Data
    // 取出数组
    $id = $_POST["ad_id"];
    $display_order = $_POST["display_order"];
    $is_display = $_POST["is_display"];
    // 二维数组$is_delete[$ids[x]][0]
    $is_delete = $_POST["is_delete"];
           

    // 判断是否有删除记录
    $del_num = count($is_delete);

    $table = "groupad";
    // 列出当前所有记录
    $num = count($id);
    
    if (!$del_num){        
        for ($i=0; $i<$num; $i++){            
            $data = array("display_order"=>$display_order[$id[$i]][0], "is_display"=>$is_display[$id[$i]][0]);
            $condition = "id = ".$id[$i];
            /* echo Test
            echo $id[$i]."<br />";
            echo $display_order[$i]."<br />";
            echo $is_display[$i]."<br />";
            test();
            */             
            $res = DB::update($table, $data, $condition);
            //echo $res."<br />";
        }
        showmessage('更新成功' , join_plugin_action('index'));
    }else{
        
        for ($i=0; $i<$num; $i++){            
            // 当二维数组$is_delete[][]有值，则删除该条记录
            if ($is_delete[$id[$i]][0]){
                $condition = "id = ".$id[$i];
                $res = DB::delete($table, $condition);
                //echo $res."<br />";
            }
        }
        showmessage('删除成功' , join_plugin_action('index'));
       
    }
   
}

// 点击广告时，给响应的积分和经验值
function push_cre_exp(){
    global $_G;
    require_once libfile("function/credit");
    require_once libfile("function/group");
    // --点击发送积分---------------------------------------------------
    // 用户id
    $uid = $_G["uid"];
    $groupid = $_G["fid"];
     // 事件代码
    $action = "viewadvertise";
    //  资源id(如果为null的话，$credit=null)
    $resourceId = $_GET["id"];
    $credit = credit_create_credit_log($uid, $action, $resourceId);
    //echo "credit = $credit";
    
    // --点击发送积分-------------------END-----------------------------
    // --点击发送经验值-------------------------------------------------
    // 点击广告动作唯一标识
    $op_setting = "click_ad";
    $exp = group_add_empirical_by_setting($uid, $groupid, $op_setting, $resourceId);

    $url = base64_decode($_GET['murl']);
    // 页面重定向

    if($url == "#"){
	   $url = $_SERVER['HTTP_REFERER'];
    }
	header("location: $url");
}

//utf-8 字符串截取函数
function utf8Substr($str, $from, $len){
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);

}
?>
