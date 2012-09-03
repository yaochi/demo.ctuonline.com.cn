<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_questionarytitle {
	
	function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $settings = array(
				'ad_class'=> array(
					'title' => '选择分类',
					'type' => 'select',
					'value' => array()
                ),
				'title' => array(
                    'title' => '指定标题',
                    'type' => 'text'
                ),
				'content_len' => array(
                     'title' => '问卷标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
		
		require_once libfile("function/category");
        $is_enable_category = false;
        $fid = $_GET["fid"];
        if(common_category_is_enable($fid, $plugin_id)){
            $is_enable_category = true;
            $categorys = common_category_get_category($fid, $plugin_id);
        }

        foreach ($categorys as $value){
                $settings['ad_class']['value'][] = array($value['id'], $value['name']);
        }
         $settings['ad_class']['value'][] = array(0, "默认分类");
         $settings['ad_class']['value'][] = array(-1, "所有");
		
		return $settings;
    }
	 function getstylesetting($style) {
		 $categorys_setting = array();
        $categorys_setting["cate_title_author_desc"] = array(
            'summary_len' => array(
                'title' => '问卷摘要字数',
                'type' => 'text',
                'default' => 50
            )
        );
		 $categorys_setting["cate_title_author_desc_photo"] = array(
            'summary_len' => array(
                'title' => '问卷摘要字数',
                'type' => 'text',
                'default' => 50
            )
        );
        return $categorys_setting[$style];
    }
	 function getdata($style, $parameter) {
	  $plugin_id = array_pop(explode("\\", dirname(dirname(__FILE__))));
		if(!$_GET["fid"]){
			return array('html'=>'请在专区内使用该组件', 'data'=>null);
		}
		$classid=$parameter['ad_class'];
		$title = $parameter["title"];
		
		$classid = intval($classid);
        $adclass_sql = "";
        if($classid!=-1){
            $adclass_sql = " AND classid = '$classid' ";
        }
		 require_once libfile("function/category");
         $categorys = common_category_get_category($_GET["fid"],$plugin_id);
        $result = array();
		$questionarys=array();
        $query = DB::query("SELECT * FROM ".DB::table("questionary")." WHERE fid=".$_GET["fid"].$adclass_sql." AND questname LIKE '%$title%' ORDER BY dateline DESC LIMIT ".$parameter['items']);
        while ($questionary = DB::fetch($query)) {
            $questionary["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET['fid']."&plugin_name=questionary&plugin_op=groupmenu&questid=".$questionary['questid']."&questionary_action=answer";
			$questionary["name"]=cutstr($questionary['questname'], $parameter["content_len"]);
			 if($questionary[classid]<=0 || !$categorys[$questionary[classid]]){
                    $questionary["category"] = "未分类";
                }else{
                    $questionary["category"] = $categorys[$questionary[classid]]['name'];
                }
			if($style[key] == "cate_title_author_desc" || $style[key] == "cate_title_author_desc_photo"){
			require_once libfile('function/post');
				$questionary[questdescr]=messagecutstr($questionary[questdescr], $parameter["summary_len"]);
			}
			$questionary['id']=$questionary['questid'];
			$questionary['contenttype']='questionary';
			$questionary["dateline"] = dgmdate($questionary[dateline]);
			$questionary['username'] = user_get_user_name_by_username($questionary['username']);
            $questionarys[] = $questionary;
        }
         $result["parameter"] = $parameter;  //update by qiaoyongzhi,2011-2-25 EKSN143 模块高度
         $result["listdata"] = $questionarys;
        return array('data' => $result);
    }


	//function getsetting() {
//		$plugin_id = array_pop(explode("\\", dirname(dirname(__FILE__))));
//        
//        return array('list_length' => array(
//                'title' => '列表显示行数',
//                'type' => 'text',
//                'default' => 10
//                ),
//                array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
//        );
//	}
//	function getdata($style, $parameter) {
//		if(!$_GET["fid"]){
//			return array('html'=>'请在专区内使用该组件', 'data'=>null);
//		}
//		$query = DB::query("SELECT * FROM ".DB::table("questionary")." WHERE fid=".$_GET["fid"]." ORDER BY dateline DESC LIMIT ".$parameter['list_length']);
//		$forums = array();
//		$html = '<div id="portal_block_263_content" class="content"><div class="module cl xl xl1"><ul>';
//		while($questionary=DB::fetch($query)){
//			$html .= '<li><a href="forum.php?mod=group&action=plugin&fid='.$_GET['fid'].'&plugin_name=questionary&plugin_op=groupmenu&questid='.$questionary['questid'].'&questionary_action=answer" title="'.$questionary[questname].'" target="_blank">'.$questionary['questname'].'</a></li>';
//		}
//		$html .= '</ul></div></div>';
//		
//		return array('html' => $html, 'data' =>null);
//	}
}
?>
