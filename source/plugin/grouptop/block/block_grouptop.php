<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once libfile('function/discuzcode');
class block_grouptop {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        $settings = array(
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }

    function getstylesetting($style) {
    	$categorys_setting = array();
    	//标准样式
        $categorys_setting["standard"] = array(
			'title_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 30
                ),
            'message_len' => array(
                'title' => '简介字数',
                'type' => 'text',
                'default' => 70
            )
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
		global $_G;
		$fid=$_G[fid];
		$toplist=array();
		//通知
		$query=DB::query("select id,title,content,create_time,uid,username from ".DB::table("notice")." where group_id=".$fid." and displayorder in(1,2,3) order by displayorder desc,create_time desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid=".$value[id]."&notice_action=view&";
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=discuzcode($value['content'],-1,0,1,1,1,1,1);
			$value['message']=strip_tags($value['message']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$toplist['data']=$value;
			$toplist['time']=$value["create_time"];
			$toplist['tag']=1;
			$toplists[]=$toplist;
		}
		//活动
		$query = DB::query("select ff.fid,ff.name,fff.description,fff.founderuid,fff.foundername,fff.dateline from ".DB::table("forum_forum")." ff,".DB::table("forum_forumfield")." fff where ff.fup =".$fid." and ff.fid=fff.fid and ff.displayorder in(1,2,3) order by ff.displayorder desc,fff.dateline desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=activity&fup=".$fid."&fid=".$value[fid];
			$value['uid']=$value['founderuid'];
			$value['title']=$value['name'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['description']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='创建了一个活动';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//提问吧
		$query = DB::query("select t.tid,t.fid,t.author,t.authorid,t.subject,t.dateline,p.message from ".DB::table("forum_thread")." t,".DB::table("forum_post")." p where t.tid=p.tid and t.fid=".$fid." and t.special=3 and p.first=1 and t.displayorder in(1,2,3) order by t.displayorder desc,t.dateline desc");
		while($value=DB::fetch($query)){
			$value[url]="forum.php?mod=viewthread&tid=".$value[tid]."&fid=".$fid."&plugin_name=qbar&extra=page%3D1";
			$value['uid']=$value['authorid'];
			$value['title']=$value['subject'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['message']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='提出了一个问题';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//投票
		$query = DB::query("select t.tid,t.fid,t.author,t.authorid,t.subject,t.dateline,p.message from ".DB::table("forum_thread")." t,".DB::table("forum_post")." p where t.tid=p.tid and t.fid=".$fid." and t.special=1 and p.first=1 and t.displayorder in(1,2,3) order by t.displayorder desc,t.dateline desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=viewthread&fid=".$fid."&special=1&plugin_name=poll&plugin_op=createmenu&tid=".$value[tid]."&extra=page%3D1";
			$value['uid']=$value['authorid'];
			$value['title']=$value['subject'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['message']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='发起一个投票';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//问卷
		$query = DB::query("select questid,questname,questdescr,uid,username,dateline from ".DB::table("questionary")." where fid=".$fid." and displayorder=1 order by dateline desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=questionary&plugin_op=groupmenu&questid=".$value[questid]."&questionary_action=answer&";
			$value['title']=$value['questname'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['questdescr']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='发布了一个问卷';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//资源
		$query = DB::query("select resourceid,title,type,typeid,imglink,titlelink,about,uid,dateline from ".DB::table("resourcelist")." where fid=".$fid." and displayorder=1 order by dateline");
		while($value=DB::fetch($query)){
			$value['url']=$value['titlelink'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['about']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='新建了一个'.$value['type'];
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//话题
		$query = DB::query("select t.tid,t.fid,t.author,t.authorid,t.subject,t.dateline,p.message from ".DB::table("forum_thread")." t,".DB::table("forum_post")." p where t.tid=p.tid and t.fid=".$fid." and t.special=0 and p.first=1 and t.displayorder in(1,2,3) order by t.displayorder desc,t.dateline desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=viewthread&fid=".$fid."&special=0&plugin_name=topic&plugin_op=groupmenu&tid=".$value[tid]."&extra=page%3D1";
			$value['uid']=$value['authorid'];
			$value['title']=$value['subject'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['message']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='发布了一个话题';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//评选
		$query = DB::query("select selectionid,selectionname,selectiondescr,uid,username,dateline from ".DB::table("selection")." where fid=".$fid." and displayorder=1 order by displayorder desc,dateline desc");
		while($value=DB::fetch($query)){
			$value['url']="forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$value[selectionid]."&selection_action=answer&";
			$value['title']=$value['selectionname'];
			$value['title']=cutstr($value['title'],$parameter['title_len']);
			$value['message']=strip_tags($value['selectiondescr']);
			$value['message']=cutstr($value['message'],$parameter['message_len']);
			$value['lang']='新建了一个评选';
			$value['realname']=user_get_user_name($value["uid"]);
			$toplist['data']=$value;
			$toplist['time']=$value["dateline"];
			$toplist['tag']=0;
			$toplists[]=$toplist;
		}
		//排序
		foreach($toplists as $key => $row){
			$tag[$key] = $row['tag'];
			$time[$key] = $row['time'];
			$data[$key] = $row['data'];
		}
		array_multisort($tag, SORT_DESC, $time, SORT_DESC, $toplists);
		$result['listdata']=$toplists;
        return array('data' => $result);
    }

}
?>