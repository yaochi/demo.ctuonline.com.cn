<?php
/*
 * block_groupmemberonline
 * 在线成员
 * 单个数据源信息描述
 * @author caimm
 * @since 2012-1-11
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once libfile('function/group');

class block_groupmemberonline{

	function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        return array(
        		'shownum' => array ('title' => '显示数量','type' => 'text','value' => 12),
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'),
                'fid'=>array("type"=>'<input type="hidden" name="fid" value="37"/>')//专区id
        );
	}
	function getstylesetting($style) {
        $categorys_setting = array();
        return $categorys_setting[$style];
    }

  	function getdata($style, $parameter) {
  		global $_G;
  		$fid=$_GET['fid'];//专区id,diy组件的设置表单action值包含fid的值
  		if(!$fid){
  			return array('html'=>"请在专区内使用该组件$fid", 'data'=>null);
  		}

    	$onlineuserlist=array();//定义在线成员
  		$result=array();
  		$sql = "select distinct s.uid from ".DB::table("common_session")." s,".DB::table("forum_groupuser")." g where s.uid=g.uid and g.fid=".$fid." order by s.lastactivity desc limit 0, ".$parameter["shownum"];
  		$info = DB::query($sql);
  		require_once libfile("function/avatar");
  		while($onlineuserlist=DB::fetch($info)){
			$onlineuserlist['userImage']=get_user_image($onlineuserlist[uid],'small');
			$onlineuserlists[]=$onlineuserlist;
  		}

        $result['listdata']['onlineuserlist']=$onlineuserlists;
		return array('data' => $result);

    }
}
?>