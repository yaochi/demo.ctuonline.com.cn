<?php

require_once(dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");
// 活动导入广告
function index() {
    global $_G;
    // 活动id
    $activityid =  $_G["fid"];
    
    $table = DB::table("forum_forum");
    // 根据活动的id取出专区的id
    $query = "SELECT fup FROM $table WHERE fid= $activityid" ;
    $query = DB::query($query);
    $result = DB::fetch($query);
    if ($result) {
        // 专区id
        $groupid = $result[fup];

        $db = DB::table("groupad");
        $showAttributes = "*";

        /*
         * 判断是否添加筛选条件
         * 支持复合筛选
         * 用户自定义筛选 + 是否显示
         * @zic 2010-08 06
         */
        $is_display = 1;        
        $addfilter = "";
        if(!is_null($is_display)){
            $addfilter = $addfilter." AND is_display = ".$is_display;
        }
        
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
        $query = "SELECT ".$showAttributes." FROM ".$db." WHERE group_id=".$groupid.$addfilter." ORDER BY is_display DESC, display_order DESC, update_time DESC LIMIT ".$start.",".$perpage;
        $result = DB::query($query);
        // 获取结果数
        $num = DB::num_rows($result);
        // 将结果取出放在一个array中
        $adEntitys = array();
        for ($i=0; $i<$num; $i++){
            $adEntitys[$i] = DB::fetch($result);
            // 删除"标题字"数超过$content_len的字，并以...代替
            // 2010-8-15
            $adEntitys[$i]["title"] = cut_str(10, $adEntitys[$i]["title"]);
            $adEntitys[$i]["content"] = cut_str(10, $adEntitys[$i]["content"]);
            $adEntitys[$i]["media_url"] = cut_str(20, $adEntitys[$i]["media_url"]);
        }
        $init_date["adEntitys"] = $adEntitys;
        
        return $init_date;
    }
    return array();
}
// 删除过多字数
function cut_str($len, $str){

    $content = utf8Substr($str,0,$len);
    if($content!=$str){
        $content = "$content...";
    }
    return $content;
}

// 获取$groupid专区中创建的AD记录数
// 2010-8-16
function count_ad($db, $groupid, $addsql){
     $query = "SELECT count(*) FROM ".$db." WHERE group_id=".$groupid.$addsql;
     return DB::result_first($query, 0);
}

//utf-8 字符串截取函数
function utf8Substr($str, $from, $len){
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);

}

function create() {
    global $_G;
    // 活动id
    $fid = $_G["fid"];
    // 广告id
    $ids = $_POST[ids];

    // SQL
    $db =  DB::table('groupad');
    $sql = "SELECT t.* FROM $db t WHERE id IN(" . implode(",", $ids) . ")";
    $query = DB::query($sql);
    $tids = array();
    $count = 0;
    while ($row = DB::fetch($query)) {
        $count++;
        $sql = "INSERT INTO " . DB::table('groupad') . " (title, media_dir, media_url, media_style, content, width, height, uid, group_id)
		VALUES ( '$row[title]', '$row[media_dir]', '$row[media_url]', '$row[media_style]','$row[content]','$row[width]','$row[height]','$row[height]','$fid')";
        DB::query($sql);
        //$tid = DB::insert_id();
        //$tids[] = $row[tid];
       // $tidmap[$row[tid]] = $tid;
    }

    $url = "forum.php?mod=activity&action=plugin&fid=$fid&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu";
    DB::query("UPDATE " . DB::table("forum_forum") . " SET threads=$count WHERE fid=$fid");
    showmessage("导入完毕", $url);
}

?>
