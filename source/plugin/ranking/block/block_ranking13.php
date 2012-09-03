<?php

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
/**
 * 成员
 * 排行元素：
 * @author SK
 *
 */
class block_ranking13 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+参与人数
        $categorys_setting["title_joinnum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
 
    	return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	$fid = $_GET["fid"];
    	
    	$sql = "SELECT selectionid, selectionname, scored FROM ".DB::table("selection")." WHERE fid=$fid and moderated>=0";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$selections = array();
    	
    	if ($style[key] == "title_joinnum") {
    		$orderby = " ORDER BY scored DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($selection = DB::fetch($query)) {
    			$selection["url"] = "forum.php?mod=group&action=plugin&fid=$fid&plugin_name=selection&plugin_op=groupmenu&selectionid=".$selection[selectionid]."&selection_action=answer&";
				$selection["id"] = $selection[selectionid];
    			$selection["joiner"] = $selection[scored];
                $selection["title"] = cutstr($selection["selectionname"], $parameter["title_len"]);
				$selection['contenttype']='selection';
                $selections[] = $selection;
            }
    	} 
        $result["parameter"] = $parameter;
        $result["listdata"] = $selections;
        
        return array('data' => $result);
    }

}
?>
