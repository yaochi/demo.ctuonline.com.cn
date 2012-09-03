<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_topiclist2 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $return = array(
            'select_type' => array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => array(),
			),
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );

        require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = "topic";
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    } else {
	    	array_shift($settings);
	    }
        $return['select_type']['value'][] = array("0", "全部");
	    foreach ($categorys as $value) {
	    	$return['select_type']['value'][] = array($value['id'], $value['name']);
	    }
        
        return $return;
    }

    function getstylesetting($style) {
        $categorys_setting = array();
        $categorys_setting["pic_wall"] = array(
            'sort_type' => array(
                'title' => '排序方式',
                'type' => 'select',
                'value' => array(array(1, '按时间排序'),array(2, '按人气排序'),array(3, '按支持数排序'),array(4, '按回复数排序')),
            ),
            'top_pic_width' => array(
                'title' => '图片宽度',
                'type' => 'text',
                'value' => 100,
            ),
            'top_pic_height' => array(
                'title' => '图片高度',
                'type' => 'text',
                'value' => 100,
            )
        );
       
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
        global $_G;
        if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        if(!empty($parameter[select_type]) && $parameter[select_type]!=0){
            $where = " AND t.category_id=".$parameter[select_type];
        }
        
        $order_sql='';
        if($parameter[sort_type]==2){
            $order_sql.= ' order by t.views desc ';
        }else if($parameter[sort_type]==3){
            $order_sql.=' order by t.recommend_add desc ';
        }else if($parameter[sort_type]==4){
            $order_sql.=' order by t.replies desc ';
        }else{
            $order_sql.=' order by t.dateline desc ';
        }
        
        $result = array();
        $sql = "SELECT t.*, cmp.realname realname FROM ".DB::table("forum_thread")." t, ".DB::table("common_member_profile")." cmp 
			WHERE t.special=0 AND cmp.uid=t.authorid AND t.fid=$_GET[fid] $where $order_sql LIMIT $parameter[items]";
        $query = DB::query($sql);
        $threads = array();
        $result[keys]="";
        if ($style[key] == "pic_wall") {
            //纯列表 记录标题字数
            while ($thread = DB::fetch($query)) {
                $tid=$thread[tid];
                //获取首页图片 start
                $detail = DB::fetch_first("SELECT * FROM ".DB::table('forum_post')." WHERE tid='$tid' AND first=1");
                $a1=stripos($detail[message],"[attach]",0);
                $a2= stripos($detail[message],"[/attach]",$a1);
                if($a2) {
                    $aid=substr($detail[message],$a1+8,$a2-$a1-8);
                    $img = DB::fetch_first("select * from pre_forum_attachment where aid=".$aid);
                }
                if($img){
                $img_url="data/attachment/forum/".$img[attachment];
                }
                else{
                
                $p1=stripos($detail[message],"[img",0);
                $p2=stripos($detail[message],"]",$p1);
                $p3= stripos($detail[message],"[/img]",$p2);
                if($p3) $img_url=substr($detail[message],$p2+1,$p3-$p2-1);
                else   $img_url=$_G['forum']['icon'];
                }
                unset($img);
                //获取首页图片 end
                
                $thread['imglink']=$img_url;
                $thread["url"] = "forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&tid=" . $thread["tid"];
                $thread["name"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='topic';
				$thread['id']=$thread[tid];
   	            $thread['views']=$thread['views'];
                $thread['recommend']=$thread['recommend_add'];
                $threads[$thread[tid]] = $thread;
                $result[keys].=",".$thread[tid];
            }
        }
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        return array('data' => $result);
    }

}

?>
