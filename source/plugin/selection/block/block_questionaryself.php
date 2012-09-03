<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_questionaryself {
	function getsetting() {
		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        return array('list_length' => array(
                'title' => '列表显示行数',
                'type' => 'text',
                'default' => 10
                ),
                array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
        );
	}
	function getdata($style, $parameter) {
		if(!$_GET["fid"]){
			return array('html'=>'请在专区内使用该组件', 'data'=>null);
		}
		$query = DB::query("SELECT * FROM ".DB::table("questionary")." WHERE fid=".$_GET["fid"]." ORDER BY dateline DESC LIMIT ".$parameter['list_length']);
		$forums = array();
		$html = '<div id="portal_block_264_content" class="content">';
		while($questionary=DB::fetch($query)){
			$html .= '<div class="module cl xld mbn mtn" style="width: 200px; height: 200px; background: url(static/image/common/esn/questionarybig.png) no-repeat 0 0"><dl class="mtn mbn">';
			$html .= '<dt style="text-align: center; padding: 60px 20px 0px 15px; overflow: hidden; line-height: 22px; height: 66px; color:#7b4a22;" class="xs2"><a href="forum.php?mod=group&action=plugin&fid='.$_GET['fid'].'&plugin_name=questionary&plugin_op=groupmenu&questid='.$questionary['questid'].'&questionary_action=answer" title="'.$questionary[questname].'" target="_blank">'.$questionary['questname'].'</a></dt>';
			$html .= '<p class="ptm pns" style="text-align:center;"><button type="button" onclick="javascript:window.open(\'forum.php?mod=group&action=plugin&fid='.$_GET['fid'].'&plugin_name=questionary&plugin_op=groupmenu&questid='.$questionary['questid'].'&questionary_action=answer\');"  class="pn"><em>参与</em></button></p></dl></div>';
		}
		$html .= '</div>';
		
		return array('html' => $html, 'data' =>null);
	}
}
?>
