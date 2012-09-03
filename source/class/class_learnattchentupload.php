<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_forumupload.php 11442 2010-06-02 08:50:30Z monkey $
 */
class learnattachment_upload {

	var $uid;
	var $aid;
	var $simple;
	var $statusid;
	var $attach;

    function learnattachment_upload(){
	    global $_G;
		$this->uid = intval($_G['uid']);
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$this->uid);
		$dataline=intval($_G['gp_posttime']);
		if(!$_FILES['Filedata']['error']) {
			$this->aid = 0;
			$this->simple = !empty($_G['gp_simple']) ? $_G['gp_simple'] : 0;
           $this->learid=!empty($_G['gp_learid'])? $_G['gp_learid'] : 0;
			$_G['groupid'] = intval(DB::result_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='".$this->uid."'"));
			loadcache('usergroup_'.$_G['groupid']);
			$_G['group'] = $_G['cache']['usergroup_'.$_G['groupid']];

			require_once libfile('class/upload');

			$upload = new discuz_upload();
			$upload->init($_FILES['Filedata'], 'home');
			$this->attach = &$upload->attach;

			if($upload->error()) {
				$this->uploadmsg(2);
			}
			//使用专区的附件设置
//			$allowupload = !$_G['group']['maxattachnum'] || $_G['group']['maxattachnum'] && $_G['group']['maxattachnum'] > DB::result_first("SELECT count(*) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND dateline>'$_G[timestamp]'-86400");
//			$allowupload = true;
//			if(!$allowupload) {
//				$this->uploadmsg(9);
//			}
			//使用专区的附件设置
//			if($_G['group']['attachextensions'] && (!preg_match("/(^|\s|,)".preg_quote($upload->attach['ext'], '/')."($|\s|,)/i", $_G['group']['attachextensions']) || !$upload->attach['ext'])) {
//				$this->uploadmsg(1);
//			}

			if(empty($upload->attach['size'])) {
				$this->uploadmsg(2);
			}

			//附件大小10MB
			if($upload->attach['size'] > 10485760){
				$this->uploadmsg(3);
			}

//			使用专区的附件设置
//			if($_G['group']['maxattachsize'] && $upload->attach['size'] > $_G['group']['maxattachsize']) {
//				$this->uploadmsg(3);
//			}


			//使用专区的附件设置
//			if($type = DB::fetch_first("SELECT maxsize FROM ".DB::table('forum_attachtype')." WHERE extension='".addslashes($upload->attach['ext'])."'")) {
//				if($type['maxsize'] == 0) {
//					$this->uploadmsg(4);
//				} elseif($upload->attach['size'] > $type['maxsize']) {
//					$this->uploadmsg(5);
//				}
//			}
			//使用专区的附件设置
//			if($upload->attach['size'] && $_G['group']['maxsizeperday']) {
//				$todaysize = intval(DB::result_first("SELECT SUM(filesize) FROM ".DB::table('forum_attachment')." WHERE uid='$_G[uid]' AND dateline>'$_G[timestamp]'-86400"));
//				$todaysize += $upload->attach['size'];
//				if($todaysize >= $_G['group']['maxsizeperday']) {
//					$this->uploadmsg(6);
//				}
//			}
			$upload->save();
			if($upload->error() == -103) {
				$this->uploadmsg(8);
			} elseif($upload->error()) {
				$this->uploadmsg(9);
			}
			$thumb = $remote = $width = 0;
			if($upload->attach['isimage']) {
				require_once libfile('class/image');
				$image = new image;
				$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], $_G['setting']['thumbsource']) ? 1 : 0;
				if(!$_G['setting']['thumbsource']) {
					$width = $image->imginfo['width'];
				} else {
					$imginfo = @getimagesize($upload->attach['target']);
					$width = $imginfo[0];
				}
			}
			if($_G['gp_type'] != 'image' && $upload->attach['isimage']) {
				$upload->attach['isimage'] = -1;
			}

			$idtype = empty($_GET['idtype'])? '' : $_GET['idtype'];
			$this->type=!empty($_G['gp_learntype'])? $_G['gp_learntype']: 0;
			$is=changefilename($this->learid,$this->type);
             if($is > 0){
              DB::query("delete from ".DB::table('learn_attachment')." where learid='".$this->learid."' and type='".$this->type."'");
             }
			DB::query("INSERT INTO ".DB::table('learn_attachment')." (width, dateline, filename, filetype, filesize, attachment, downloads, isimage, thumb, remote, uid,type,learid)
                                           VALUES ('$width','$_G[timestamp]','".$upload->attach['name']."','".$upload->attach['type']."','".$upload->attach['size']."','".$upload->attach['attachment']."','0','".$upload->attach['isimage']."','$thumb', '$remote','".$this->uid."','".$this->type."','".$this->learid."')");
			$this->aid = DB::insert_id();
			$this->uploadmsg(0);
		}
    }
	function uploadmsg($statusid) {
		global $_G;
		if($this->simple == 1) {
			echo 'DISCUZUPLOAD|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'];
		} elseif($this->simple == 2) {
			echo 'DISCUZUPLOAD|'.($_G['gp_type'] == 'image' ? '1' : '0').'|'.$statusid.'|'.$this->aid.'|'.$this->attach['isimage'].'|'.$this->attach['attachment'].'|'.$this->attach['name'];
		} else {
			echo $this->aid;
		}
		exit;
	}
}
function changefilename($learid,$type){
	global $_G;
	$sql="select count(*) as num from pre_learn_attachment where learid='".$learid."' and type=".$type;
	$info=DB::query($sql);
	$value =DB::fetch($info);
	return $value[num];
}
?>