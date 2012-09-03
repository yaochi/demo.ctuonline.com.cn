<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_lecturer2 {

    function getsetting() {
		
		global $_G;
	
		$block_param = $_G['tmp_block_param_value'];//放置选择的一些参数值，为解决模块选择的指定数据无法回显
		//print_r($block_param);

        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $fid = $_GET["fid"];

        $check = '';
        
        $one = '<select class="ps" name="parameter[select_lecturer][]" id="select_lecturer">';
        $query = DB::query("SELECT l.*, fl.about fabout, fl.imgurl fimgurl FROM ".DB::table('lecturer')." l, ".DB::table("forum_forum_lecturer")." fl WHERE l.id=fl.lecid AND fl.fid=".$fid);

		if($_GET['select_lecturer']){
				$curr_select_lecturer =$_GET['select_lecturer'];  //当前选择的值

		}else{
			if($block_param['select_lecturer']){
				$curr_select_lecturer = $block_param['select_lecturer'][0];
			}
				
		}

		while($value=DB::fetch($query)) {
			

			if( $curr_select_lecturer == $value['id'] ){
                $check = 'selected="selected"';
            }else{
				 $check = '';
			}
			$one .= '<option value="'.$value['id'].'" '.$check.' >'.$value['name'].'</option>';
		}
        $one .='</select>';
        
        $result[] = array("title"=>"选择讲师", "html"=>$one);
        $result[] = array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'.'<script type="text/javascript">function checkselect() {var select_lecturer = $(\'select_lecturer\');if (select_lecturer) {var slen = select_lecturer.length;if (slen < 1) {showDialog("请至少选择一个讲师");$(\'select_lecturer\').focus();return false;}}}</script>');
        
		return $result;
    }
    
    function getstylesetting($style) {
    	$categorys_setting = array();
        //独立样式
        $categorys_setting["selfstyle"] = array(
            'top_desc_len' => array(
                'title' => '讲师介绍字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '讲师图片宽度',
                'type' => 'text',
                'value' => 120
            ),
            'top_pic_height' => array(
                'title' => '讲师图片高度',
                'type' => 'text',
                'value' => 120
            ),
        );
        
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        if ($parameter['select_lecturer']) {
        		$lecid = $parameter['select_lecturer'][0];
        }
        
        if ($lecid) {
	        $query = DB::query("SELECT l.*, fl.id lid, fl.fid fid, fl.about fabout, fl.imgurl fimgurl FROM ".DB::table('lecturer')." l, ".DB::table("forum_forum_lecturer")." fl WHERE l.id=fl.lecid AND l.id=".$lecid);
	        
	        $lecturers = array();
	    	if ($style[key] == "selfstyle") {
	            //图片尺寸  摘要长度  标题字数
	            while ($lecturer = DB::fetch($query)) {
	                $lecturer["url"] = "forum.php?mod=group&action=plugin&fid={$lecturer[fid]}&plugin_name=lecturer&plugin_op=groupmenu&diy=&lecid={$lecturer['lid']}&lecturer_action=view";
	                if ($lecturer["fabout"]) {
						if(strlen($lecturer["fabout"])>$parameter[top_desc_len]){
	                    	$lecturer["fabout"] = cutstr($lecturer["fabout"], $parameter[top_desc_len]) . "...";
						}
	                }
	                $lecturer["dateline"] = dgmdate($notice["dateline"]);
					$lecturer['contenttype']='lecturer';
	                $lecturers[$lecturer[id]] = $lecturer;
	            }
	        }
        }
        
        $result["parameter"] = $parameter;
        $result["listdata"] = $lecturers;
        
        return array('data' => $result);
    }

}
?>
