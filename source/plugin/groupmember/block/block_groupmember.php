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

class block_groupmember{
	
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
  		
    	$newuserlist=$activityuserlist=array();//定义新加入专区用户和活跃用户数组
    	$newuserlist=groupuserlist($fid,'joindateline', $shownum,0);//参数从左到右依次为：专区ID,排序字段，每页大小，起始，
    	//groupuserlist函数在function/function_group.php文件中
    	$activityuserlist=groupuserlist($fid,'lastupdate',$shownum,0);//
    	
    	$onlinememberlist=array();//当前专区，在线的用户列表
    	$onlinememberlist=grouponline($fid,1);//grouponline函数在function_group.php文件中
    	
    	
    	/*
    	 * 拼接返回的html代码
    	 */
    	//$html="<hr class='l'>";
//    	$script1="document.getElementById(\"new\").className=\"a\";
//    			  document.getElementById(\"top\").className=\"\";
//    			  document.getElementById(\"newuserlist\").style.display=\"block\";
//    			  document.getElementById(\"topuserlist\").style.display=\"none\";";
//    	$script2="document.getElementById(\"top\").className=\"a\";
//    			  document.getElementById(\"new\").className=\"\";
//    			  document.getElementById(\"topuserlist\").style.display=\"block\";
//    			  document.getElementById(\"newuserlist\").style.display=\"none\";";
//    	$html.="<div class='bn' style='padding:0px;'>";
//    	//$html.="<h2>专区成员</h2>";
//    	$html.="<ul class='tb cl' style='border-right:1px solid #CCCCCC;'>
//					<li class='a' id='new' onmouseover='$script1' style='width:33.3%;margin:0 0 -1px;text-align:center;'><a href='###' style='-moz-border-radius:0 0 0 0;border-right:medium none'>新加入</a></li>
//					<li id='top' onmouseover='$script2' style='width:33.3%;margin:0 0 -1px;text-align:center;'><a href='###' style='-moz-border-radius:0 0 0 0;border-right:medium none'>活跃会员</a></li>
//					<li style='width:33.3%;margin:0 0 -1px;text-align:center;'><a href='forum.php?mod=group&action=memberlist&fid=$fid'  style='-moz-border-radius:0 0 0 0;border-right:medium none'>全部</a></li>
//				</ul>";
    	
    	
    	/*
    	 * 新加入
    	 */
//    	$html.="<ul class='ml mls cl' id='newuserlist' style='display:block;padding-top:10px;padding-right:0px;'>";
//    	
//    	foreach($newuserlist as $newuser) { 		
//    		$html.="<li>";    		
//    		$title="";
//    		$uid=$newuser['uid'];//用户id
//    		$username=$newuser['username'];//用户登录名    		
//    		$online=0;//是否在线，默认不在线
//    		if(in_array($uid,$onlinememberlist['list'])){
//    			$online=1;
//    		}
//    		
//    		if($newuser['level'] == 1){
//    			$title.="群主";
//    		}else if($newuser['level']==2){
//    			$title.="副群主";
//    		}else if($newuser['level']==3){
//    			$title.="明星会员";
//    		}
//    		if($online){//用户是否在线，在线为1,不在线为0
//    			$title.="在线";
//    		}
//    		$html.="<a href='home.php?mod=space&amp;uid=$uid' title='$title' class='avt'>";
//    		//$html.="<a href='home.php?mod=space&amp;uid=' title='title' class='avt'>";
//    		if($newuser['level'] == 1){
//    			$html.="<em class='gm'></em>";
//    		}else if($newuser['level']==2){
//    			$html.="<em class='gm' style='filter: alpha(opacity=50); opacity: 0.5'></em>";
//    		}else if($newuser['level']==3){
//    			$html.="<em class='gs'></em>";
//    		}
//    		if($online){
//    			$html.="<em class='gol'";
//    			if($newuser['level']<=3){
//    				$html.="style='margin-top:15px;'";
//    			}
//				$html.="></em>";
//    		}
//    		$html.="<img src='uc_server/avatar.php?uid=$uid&size=small'>";
//    		$html.="</a>";    		
//    		//用户登录名
//    		$html.="<p>";
//    		$html.="<a href='home.php?mod=space&uid=$uid'>";
//    		$html.=$username;
//    		$html.="</a>";
//    		$html.="</p>";    		
//    		$html.="</li>";
//    	}
//    	$html.="</ul>";
    	
    	
    	/*
    	 * 活跃会员
    	 */
//    	$html.="<ul class='ml mls cl' id='topuserlist' style='display:none;padding-top:10px;padding-right:0px;'>";
//    	foreach ($activityuserlist as $activityuser) {
//    		$html.="<li>";    		
//    		$title="";
//    		$uid=$activityuser['uid'];//用户id
//    		$username=$activityuser['username'];//用户登录名    
//    		$online=0;//是否在线，默认不在线
//    		if(in_array($uid,$onlinememberlist['list'])){
//    			$online=1;
//    		}		
//    		
//    		if($activityuser['level'] == 1){
//    			$title.="群主";
//    		}else if($activityuser['level']==2){
//    			$title.="副群主";
//    		}else if($activityuser['level']==3){
//    			$title.="明星会员";
//    		}
//    		
//    		if($online){//用户是否在线，在线为1,不在线为0
//    			$title.="在线";
//    		}
//    		$html.="<a href='home.php?mod=space&amp;uid=$uid' title='$title' class='avt'>";
//    		if($activityuser['level'] == 1){
//    			$html.="<em class='gm'></em>";
//    		}else if($activityuser['level']==2){
//    			$html.="<em class='gm' style='filter: alpha(opacity=50); opacity: 0.5'></em>";
//    		}else if($activityuser['level']==3){
//    			$html.="<em class='gs'></em>";
//    		}
//    		if($online){
//    			$html.="<em class='gol'";
//    			if($activityuser['level']<=3){
//    				$html.="style='margin-top:15px;'";
//    			}
//				$html.="></em>";
//    		}
//    		$html.="<img src='uc_server/avatar.php?uid=$uid&size=small'>";//用户头像
//    		$html.="</a>";    		
//    		//用户登录名
//    		$html.="<p>";
//    		$html.="<a href='home.php?mod=space&uid=$uid'>";
//    		$html.=$username;
//    		$html.="</a>";
//    		$html.="</p>";    		
//    		$html.="</li>";
//    	}
//    	$html.="</ul>";  
//
//    	
//    	$html.="</div>";
//    	return array('html' => $html, 'data' =>null);
		
    	require_once libfile("function/avatar");
    			 
        $newuseruids = array();
        foreach($newuserlist as $key=>$item){
        	$item['userImage']=get_user_image($item[uid],'small');            
            $newuserlist[$key]=$item;  
            
            
            
            $newuseruids[] = $item[uid]; 
        	if(in_array($item[uid],$onlinememberlist['list'])){
        		$item['online']=1;
    			$newuserlist[$key]=$item;    			
			}
			
        }
        $activityuseruids = array();
        foreach($activityuserlist as $key=>$item){
        	$item['userImage']=get_user_image($item[uid],'small');            
            $activityuserlist[$key]=$item;  
            
            
            $activityuseruids[] = $item[uid];
        	if(in_array($item[uid],$onlinememberlist['list'])){
        		$item['online']=1;
    			$$activityuserlist[$key]=$item;
			}
        }
        $result=array();
		$result['listdata']=array();
		$result['listdata']['newuserlist']=$newuserlist;
		$result['listdata']['activityuserlist']=$activityuserlist;
		
        $result["newuserrealnames"] = user_get_user_realname($newuseruids);
        $result[activityuserrealnames] = user_get_user_realname($activityuseruids);
		//$result['listdata']['onlinememberlist']=$onlinememberlist;
		return array('data' => $result);
		
    }

}
?>
