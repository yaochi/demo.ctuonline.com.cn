<?php
/*
 * 新提问，纯标题显示
 * @author fumz
 * @since 2010-7-20 8:47:00
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once libfile('function/group');

class block_newquestion{
	function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        return array(
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'), );
	}
	
	function getstylesetting($style) {
        $categorys_setting = array();
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '提问标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["question_multi"] = array(
            'title_len' => array(
                'title' => '提问标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["question_multi3"] = array(
            'title_len' => array(
                'title' => '提问标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["question_multi2"] = array(
            'title_len' => array(
                'title' => '提问标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        $categorys_setting["question_self"] = array(
            'title_len' => array(
                'title' => '提问标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        return $categorys_setting[$style];
    }        
  	function getdata($style, $parameter) {
  		global $_G;
  		$questionlist=array();
  		$fid=$_G['fid'];
  		$shownum=is_int($parameter["items"])?$parameter["items"]:'10';
  		$query=DB::query("SELECT ft.*, cmp.realname realname FROM ".DB::table('forum_thread')." ft, ".DB::table("common_member_profile")." cmp WHERE ft.authorid=cmp.uid AND ft.fid='$fid' AND ft.special=3 AND ft.displayorder!='-1'  ORDER BY ft.dateline desc limit $shownum");
  		
  		require_once libfile("function/category");
  		
		//$qis_enable_category = common_category_is_enable($fid, 'qbar');//fumz,提问吧分类是否开启
		//$qisprefix=common_category_is_prefix($fid, 'qbar');//fumz,提问是否显示分类
		//将上面的两步合并为一步
		$isEnableAndprefix=common_category_isEnableAndprefix($fid, 'qbar');
		while($question=DB::fetch($query)){
			$question['lastpostername'] = user_get_user_name_by_username($question['lastposter']);
			$question['url']="forum.php?mod=viewthread&plugin_name=qbar&tid=".$question['tid'];
			$question['homeurl']="home.php?mod=space&uid=".$question['authorid'];
			if ($style[key] == "general"&&$parameter["title_len"]) {
				$question['name']=cutstr($question['subject'], $parameter["title_len"]);
			}elseif($style[key] == "question_multi"&&$parameter["title_len"]){
				$question['subject']=cutstr($question['subject'], $parameter["title_len"]);
				$question['name']=$question['subject'];
			}elseif($style[key] == "question_self"&&$parameter["title_len"]){
				$question['subject']=cutstr($question['subject'], $parameter["title_len"]);
				$question['name']=$question['subject'];
			}else{
				$question['name']=$question['subject'];
			}
			//if($qis_enable_category&&$qisprefix){
			if($isEnableAndprefix){
				if($question['category_id']!=0){
//					$catesql="SELECT name from pre_common_category WHERE id=".$question['category_id'];
//					$catequery=DB::query($catesql);
//					$catename=DB::fetch($catequery,0);
					$catename=common_category_getByid($question['category_id']);
					
					if(empty($catename)){
						$question['category_name']="未分类";
					}else{
						$question['category_name']=$catename['name'];
					}							
				}else{
					$question['category_name']="未分类";
				}		
			}				
			$question['date']=dgmdate($question['dateline']);
			$question['dateline']=dgmdate($question['dateline']);
			$question['lastpost']=dgmdate($question['lastpost']);
			//for diy delete
			$question['id']=$question['tid'];
			$question['contenttype']='qbar';
			$questionlist[$question['tid']]=$question;
			
		}
		$result=array();
		$result["parameter"] = $parameter;
		$result["listdata"] = $questionlist;
        return array('data' => $result);
		
    }

}
?>
