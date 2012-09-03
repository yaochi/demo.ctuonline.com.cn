<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 组织
 * 排行元素：
 * @author SK
 *
 */
class block_ranking12 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		$settings = array(
			'select_ordertype' => array(
				'title' => '选择排序元素',
				'type' => 'select',
				'value' => array(),
			),
			'select_orderby' => array(
				'title' => '选择排序类型',
				'type' => 'select',
				'value' => array(),
			),
			'list_length' => array(
                'title' => '列表显示条数',
                'type' => 'text',
                'default' => 10
            ),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		
		$settings['select_ordertype']['value'][] = array('1', '参与人数');
		$settings['select_ordertype']['value'][] = array('2', '活跃度');
		$settings['select_orderby']['value'][] = array('1', '由高到低');
		$settings['select_orderby']['value'][] = array('2', '由低到高');
		
		return $settings;
    }

    function getdata($style, $parameter) {
    	$groupid = $_GET["fid"];
		$ordertype = $parameter['select_ordertype'];
		$ordertypesql = ""; 
		$ordertypename = "";
	    if($ordertype==1) {
	    	$ordertypename = "参与人数";
	    	$ordertypesql = "ORDER BY tt.membernum ".($parameter['select_orderby']==1 ? "DESC" : "");
	    } elseif ($ordertype==2) {
	    	$ordertypename = "活跃度";
	    	$ordertypesql = "ORDER BY tt.membernum ".($parameter['select_orderby']==1 ? "DESC" : "");
	    }
        $query = DB::query("SELECT t.fid, t.name, tt.membernum FROM ".DB::table('forum_forum')." t, ".DB::table('forum_forumfield')." tt WHERE t.fid=tt.fid AND t.type='sub' ".$ordertypesql." LIMIT ".intval($parameter["list_length"]));
        $html = "<table>";
        $html .="<tr><th>专区</th><td>".$ordertypename."</td><tr>";
        while($data = DB::fetch($query)) {
            $url = "forum.php?mod=group&fid=".$data['fid'];
//            显示标题和内容
            $html .= sprintf('<tr><th><a href="%s" title="%s">%s</a></th><td>%s</td></tr>', $url, $data["name"], cutstr($data["name"], 8), $data["membernum"]);
        }
        $html .= "</table>";
        return array('html' => $html, 'data' =>null);
    }

}
?>
