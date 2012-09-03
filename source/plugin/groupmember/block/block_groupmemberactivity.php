<?php
/*
 * block_groupmemberactivity
 * 新加入专区成员类
 * 单个数据源信息描述
 * @author fumz
 * @since 2010-7-20 8:47:00
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once libfile('function/group');

class block_groupmemberactivity{
	
	function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        return array(
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
  		
  		$shownum=@intval($parameter["items"]);
  		if(!is_integer($shownum)){
  			$shownum=8;
  		}  		
    	$activityuserlist=array();//定义新加入专区用户和活跃用户数组
    	$activityuserlist=groupuserlist($fid,'lastupdate',$shownum,0);//
  		$result=array();
		

		require_once libfile("function/avatar");
        $activityuseruids = array();
        foreach($activityuserlist as $key=>$item){
        	$item['userImage']=get_user_image($item[uid],'small');            
            $activityuserlist[$key]=$item; 
            $activityuseruids[] = $item[uid];
        }
        $result[activityuserrealnames] = user_get_user_realname($activityuseruids);
        $result['listdata']['activityuserlist']=$activityuserlist;
		return array('data' => $result);
		
    }
}
?>
