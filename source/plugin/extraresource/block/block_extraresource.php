<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_extraresource {
	function getsetting() {
		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        return array(
		array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
	}
	function getstylesetting($style){
        $categorys_setting = array();
        //纯列表
        $categorys_setting["extralist"] = array(
            'titlelength' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        return $categorys_setting[$style];
    }

	function getdata($style, $parameter) {
		if(!$_GET["fid"]){
			return array('html'=>'请在专区内使用该组件', 'data'=>null);
		}
		$query = DB::query("SELECT * FROM ".DB::table("extra_resource")." WHERE released='1' and fid=".$_GET["fid"]." and type='class' ORDER BY totalstars DESC LIMIT ".$parameter['items']);
		while($value=DB::fetch($query)){
			if($value[type]=='class'){
				$value[url]='forum.php?mod=group&action=plugin&fid='.$_GET["fid"].'&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id='.$value[resourceid];
			}elseif($value[type]=='lec'){
				$value[url]='forum.php?mod=group&action=plugin&fid='.$_GET["fid"].'&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewlec&id='.$value[resourceid];
			}elseif($value[type]=='org'){
				$value[url]='forum.php?mod=group&action=plugin&fid='.$_GET["fid"].'&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=vieworg&id='.$value[resourceid];
			}
			if(strlen($value[totalstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
				$value[totalstars]=$value[totalstars].".0";
			}
			$list[]=$value;
		}
		$result["parameter"] = $parameter;
	    $result["listdata"] = $list;
		return array('data' => $result);
	}
}
?>
