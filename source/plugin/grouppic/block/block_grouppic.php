<?php
/* Function: 图片列表
 * Com.:
 * Author: SK
 * Date: 2010-9-26
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_grouppic {

	function getsetting() {	
		
		global $_G;
		$block_param = $_G['tmp_block_param_value'];//放置选择的一些参数值，为解决模块选择的指定数据无法回显

		//print_r($block_param);

		
		//当前选择的相册
		//curr_select_albums
		if ($_GET['select_album']) {
			$select_album = $_GET['select_album'];
			$select_album = substr($select_album, 0, strlen($select_album)-1);
			$curr_select_albums = explode(',', $select_album);
		}else{
			if($block_param['select_album']){
				$curr_select_albums = $block_param['select_album'];
			}
		}

		//curr_picids 当前选择的图片数组
		$curr_picnum = 0; //选中的图片数
		if($block_param['picids']){
			$str_picids = $block_param['picids']; //[picids] => 45,46 
			$curr_picids = explode(',', $str_picids);
			
			if($curr_picids){
				$curr_picnum = sizeof($curr_picids);
			}
		}





		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
		$myscript = "'".$_GET['script']."'";
		$myname = "'grouppic'";
		$fid = $_GET["fid"];

//		$one = '<select class="ps" multiple="multiple" name="parameter[select_album][]" id="select_album" onchange="block_get_setting_pic_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
		$one = '<select class="ps" multiple="multiple" name="parameter[select_album][]" id="select_album" onchange="change_select_album()">';
		$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE fid=".$fid);
		
		/*
		if ($_GET['select_album']) {
			$select_album = $_GET['select_album'];
			$select_album = substr($select_album, 0, strlen($select_album)-1);
			$albums = explode(',', $select_album);
		} */

		$check = '';
		while($value=DB::fetch($query)) {
			foreach ($curr_select_albums as $albumid) {
				if ($albumid==$value['albumid']) {
					$check = 'selected="selected"';
					break;
				} else {
					$check = '';
				}
			}
			$one .= '<option value="'.$value['albumid'].'" '.$check.' >'.$value['albumname'].'</option>';
		}
		$one .='</select>';
		$one .='<script type="text/javascript">var num = '.$curr_picnum.'; function change_select_album() {var tab = document.getElementById("select_album");var t;var vv="";for(var i=0; i<tab.length; i++) {if (tab[i].selected) {vv += tab[i].value + ",";}}'."block_get_setting_pic_array($myname, $myscript, $_GET[fid], vv);".'num = 0;var intervalProcess = setInterval(function(){try{if($(\'loadedSign\')) {setUlistWidth();clearTimeout(intervalProcess);}} catch(err){}}, 1000);}</script>';
		
		$two = '<div id="picdiym" class="reHeight"><ul>';
		//if ($_GET['select_album']) {
		if ($curr_select_albums) {
			//$select_album = $_GET['select_album'];
			//$select_album = substr($select_album, 0, strlen($select_album)-1);
			
			$select_album = dimplode($curr_select_albums);
			$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE albumid in (".$select_album.")");

			while ($value=DB::fetch($query)) {
                //Added by Zic, Izzln
				$Imgsrc = "data/attachment/plugin_groupalbum2/".$value["filepath"].".thumb.jpg";
                $ImgsrcThumb = "data/attachment/plugin_groupalbum2/".$value["filepath"].".thumb_thumb.jpg";
                if(!file_exists($ImgsrcThumb)){
                    require_once libfile("function/image");
                    $ImgsrcThumb = makeImage($Imgsrc,50);
                }

				//设置选中 add by songsp 2010-12-8 18:45
				if($curr_picids && in_array($value[picid],$curr_picids )){
					$pic_classname=' class="selected" ';
				}else{
					$pic_classname='';
				}


				$two .= '<li id="pic_id_'.$value[picid].'" name="pic_id_'.$value[picid].'" onclick="changeselectpic('.$value[picid].')"'.$pic_classname.'><div style="background-image: url(\''.$ImgsrcThumb.'\');"><span class="replaced">已选择</span></div></li>';
			}
			$two .= '<span id="loadedSign"></span>';	//Add a loaded sign for pic list width adjusting;
		}
		$two .= '</ul></div><p class="xg1">已选择&nbsp;<span id="picnum" class="xi2">'.$curr_picnum.'</span>&nbsp;张</p>';
		$two .= '<script type="text/javascript">function setUlistWidth() {var ulist = $(\'picdiym\').getElementsByTagName(\'li\'); var ul = $(\'picdiym\').getElementsByTagName(\'ul\')[0]; var ulistLength = Math.ceil(ulist.length/2) * ulist[0].offsetWidth; if(ulistLength > ul.offsetWidth) {ul.style.width = ulistLength + \'px\';} ul.removeChild($(\'loadedSign\'))}';
		$two .= 'function addpic(va) {var vstr = $(\'picids\').value;var vlist = Array();vlist = vstr.split(\',\');if(vstr != \'\') {vlist[vlist.length] = va;$(\'picids\').value = vlist.join(\',\');} else {$(\'picids\').value = va;}}';
		$two .= 'function removepic(va) {var vlist = $(\'picids\').value;vlist = vlist.split(\',\');if(vlist && vlist.length>0) {for(var i=0; i<vlist.length; i++) {if(vlist[i]==va) {vlist.splice(i,1);}}} else {}$(\'picids\').value = vlist.join(\',\');}';
		//$two .= 'function changepiclist(value) {var vlist = $(\'picids\').value;if(!vlist && vlist.length>0) {vlist[vlist.length+1] = value;} else {var vlist = new Array();vlist[0] = value;}$(\'picids\').value = vlist;}';
		$two .= 'function changeselectpic(pic){var pp=$(\'pic_id_\'+pic); if(pp.className.indexOf(\'selected\') == -1){addpic(pic);pp.className=\'selected\';$(\'picnum\').innerHTML=++num;}else{removepic(pic);pp.className=\'\';$(\'picnum\').innerHTML=--num;}}';
		$two .= '</script>';
		$two .= '<input type="hidden" id="picids" name="parameter[picids]" value="'.$str_picids.'">';

		$result[] = array("title"=>"选择相册", "html"=>$one);
		$result[] = array("title"=>"选择图片", "html"=>$two);
		$result[] = array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>');

		return $result;
	}

	function getstylesetting($style) {
		$categorys_setting = array();
		$categorys_setting["pic_title"] =
		$categorys_setting["selfstyle"] = array(
            'title_len' => array(
                'title' => '图片描述字数',
                'type' => 'text',
                'value' => 50
			),
            'top_pic_width' => array(
                'title' => '图片宽度',
                'type' => 'text',
                'value' => 100
			),
            'top_pic_height' => array(
                'title' => '图片高度',
                'type' => 'text',
                'value' => 100
			),
		);
		$categorys_setting["selfstyle2"] = array(
            'top_pic_width' => array(
                'title' => '图片宽度',
                'type' => 'text',
                'value' => 100
			),
            'top_pic_height' => array(
                'title' => '图片高度',
                'type' => 'text',
                'value' => 100
			),
		);
		return $categorys_setting[$style];
	}

	function getdata($style, $parameter) {
		
		$picids = $parameter["picids"];
    	if ($picids) {
    		$wheresql = " WHERE pic.picid in (".$picids.")";
    		$orderby = " ORDER BY find_in_set(pic.picid,'".$picids."')";
    	} else {
    		$wheresql = " WHERE 1=2";
    		$orderby = "";
    	}
		
		$sql = "SELECT pic.*, album.fid fid FROM ".DB::table('group_pic')." pic, ".DB::table('group_album')." album ".$wheresql." AND album.albumid=pic.albumid ".$orderby;
        $query = DB::query($sql);
        $pics = array();
		if ($style[key] == "selfstyle") {
            while ($pic = DB::fetch($query)) {
                $pic['url'] = 'forum.php?mod=group&action=plugin&fid='.$pic[fid].'&plugin_name=groupalbum2&plugin_op=groupmenu&picid='.$pic[picid].'&groupalbum2_action=index';
                $pic['imglink'] = 'data/attachment/plugin_groupalbum2/'.$pic['filepath'];
				$pic['id'] = $pic['picid'];
				$pic['contenttype'] = 'grouppic';
                $pics[] = $pic;
            }
		} elseif ($style[key] == "selfstyle2") {
			while ($pic = DB::fetch($query)) {
                $pic['url'] = 'forum.php?mod=group&action=plugin&fid='.$pic[fid].'&plugin_name=groupalbum2&plugin_op=groupmenu&picid='.$pic[picid].'&groupalbum2_action=index';
                $pic['imglink'] = 'data/attachment/plugin_groupalbum2/'.$pic['filepath'];
				$pic['id'] = $pic['picid'];
				$pic['contenttype'] = 'grouppic';
                $pics[] = $pic;
            }
		} elseif ($style[key] == "pic_title") {
			while ($pic = DB::fetch($query)) {
                $pic['url'] = 'forum.php?mod=group&action=plugin&fid='.$pic[fid].'&plugin_name=groupalbum2&plugin_op=groupmenu&picid='.$pic[picid].'&groupalbum2_action=index';
                $pic['imglink'] = 'data/attachment/plugin_groupalbum2/'.$pic['filepath'];
				$pic['id'] = $pic['picid'];
				$pic['contenttype'] = 'grouppic';
                $pics[] = $pic;
            }
		}
		$result["parameter"] = $parameter;
		$result["listdata"] = $pics;

		return array('data' => $result);
	}
}
?>
