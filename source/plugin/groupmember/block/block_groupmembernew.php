<?php
/*
 * block_groupmembernew
 * 新加入专区成员类
 * 单个数据源信息描述
 * @author fumz
 * @since 2010-7-20 8:47:00
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_groupmembernew{
	
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
  		
  		$fid=$_GET['fid'];//专区id,diy组件的设置表单action值包含fid的值
  		if(!$fid){
  			return array('html'=>"请在专区内使用该组件$fid", 'data'=>null);
  		}
  		
  		$shownum=@intval($parameter["items"]);
  		if(!is_integer($shownum)){
  			$shownum=8;
  		}
  		
    	$newuserlist=array();//定义新加入专区用户和活跃用户数组
    	$newuserlist=groupuserlist($fid,'joindateline', $shownum,0);//参数从左到右依次为：专区ID,排序字段，每页大小，起始，
		//groupuserlist函数在function/function_group.php文件中
    	
 		require_once libfile("function/avatar");
		$result=array();
		//$result['listdata']['newuserlist']=$newuserlist;
        $newuseruids = array();
        foreach($newuserlist as $key=>$item){
        	$item['userImage']=get_user_image($item[uid],'small');            
            $newuserlist[$key]=$item;  
            $newuseruids[] = $item[uid];
        }
        $result["newuserrealnames"] = user_get_user_realname($newuseruids);
        $result['listdata']['newuserlist']=$newuserlist;
		return array('data' => $result);
		
    }

}
?>
