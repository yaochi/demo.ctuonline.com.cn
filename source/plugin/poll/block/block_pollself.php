<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_pollself{
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
		$query = DB::query("SELECT p.*,t.* FROM ".DB::table("forum_poll")." p LEFT JOIN ".DB::table("forum_thread")." t ON t.tid=p.tid WHERE  t.fid=".$_GET["fid"]." AND t.moderated=0  ORDER BY dateline DESC LIMIT ".$parameter['list_length']);
		$forums = array();
	
		$html = '<div id="portal_block_260_content" class="content"><div class="module cl xld b_poll">';
		while($poll=DB::fetch($query)){
			$html .= '<dl><dt class="xs2" style="padding-left: 0; background: none;">';
			$html .= '<a href="forum.php?mod=viewthread&tid='.$poll['tid'].'" title="'.$poll[subject].'" target="_blank">'.$poll['subject'].'</a></dt>';
			$html .= '<dd><img src="static/image/common/esn/pollbig.png" width="135" height="135" style="display: inline; float: left; margin: 10px 12px 0 -5px; position: relative; "></dd><dd>';
			$html .= '<form method="post" autocomplete="off" action="forum.php?mod=misc&action=votepoll&fid='.$_GET["fid"].'&tid='.$poll['tid'].'"><ul class="side_poll" style="padding-left: 135px;">';
			$pollquery = DB::query("SELECT polloptionid, polloption FROM ".DB::table('forum_polloption')." WHERE tid=".$poll['tid']." ORDER BY displayorder");
			$i=0;
			while($polloption = DB::fetch($pollquery)) {
				$polloption['polloption'] = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
					"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $polloption['polloption']);
				$html .= '<li><label><input type="radio" name="pollanswers[]" id="option_'.$i.'" class="pc" value="'.$polloption['polloptionid'].'">'.$polloption['polloption'].'</label></li>';
				$i=$i+1;
			}
			$html .= '</ul><p class="ptn pns" style="padding-left: 155px;"><button type="submit" name="pollsubmit" id="pollsubmit" value="true" class="pn"><em>投票</em></button></p></form></dd></dl>';
		}
		$html .= '</div></div>';
		
		return array('html' => $html, 'data' =>null);
	}
}
?>
