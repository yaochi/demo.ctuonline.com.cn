<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_poll{
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
		$query = DB::query("SELECT p.*,t.* FROM ".DB::table("forum_poll")." p LEFT JOIN ".DB::table("forum_thread")." t ON t.tid=p.tid WHERE  t.fid=".$_GET["fid"]." ORDER BY dateline DESC LIMIT ".$parameter['list_length']);
		$forums = array();
		$html = '<table class="tb tb2 nobdb"><tbody>';
		while($poll=DB::fetch($query)){
			$html .= '<tr class="hover">';
			$html .= '<td class="td28"><a href="forum.php?mod=viewthread&tid='.$poll['tid'].'">'.$poll['subject'].'</a></td><td class="td28">';
			$html .= '</td></tr>';
		}
		$html .= '</tbody></table>';
		
		return array('html' => $html, 'data' =>null);
	}
}
?>
