<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_questionary {
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
		$html = '<table class="tb tb2 nobdb"><tbody>';
		while($questionary=DB::fetch($query)){
			$html .= '<tr class="hover">';
			$html .= '<td class="td28"><a href="forum.php?mod=group&action=plugin&fid='.$_GET['fid'].'&plugin_name=questionary&plugin_op=groupmenu&questid='.$questionary['questid'].'&questionary_action=answer">'.$questionary['questname'].'</a></td><td class="td28">';
			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';

		return array('html' => $html, 'data' =>null);
	}
}
?>
