<?php

/**
 *      [Discuz!] (C)2010-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_forumupload.php 11442 2010-06-02 08:50:30Z monkey $
 *      Modify by lujianqing 2010-08-03
 */

class attachment_upload {

	var $uid;
	var $aid;
	var $simple;
	var $statusid;
	var $attach;

	function attachment_upload() {
		global $_G;
                
		$this->uid = intval($_G['uid']);
	            
		if(!$_FILES['adnewimage']['error']) {
			$this->aid = 0;
			$this->simple = !empty($_G['gp_simple']) ? $_G['gp_simple'] : 0;

			$_G['groupid'] = intval(DB::result_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='".$this->uid."'"));
			loadcache('usergroup_'.$_G['groupid']);
			$_G['group'] = $_G['cache']['usergroup_'.$_G['groupid']];

			require_once libfile('class/upload');

			$upload = new discuz_upload();
                        // $_FILES[input file name]
                        // Modify Root
                       
			$upload->init($_FILES['adnewimage'], 'plugin_groupad');
			$this->attach = &$upload->attach;
                        
			if($upload->error()) {
				$this->uploadmsg(2);
			}
			
			if(empty($upload->attach['size'])) {
				$this->uploadmsg(2);
			}
			
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
                        /* insert table $upload->attach['attachment']
			DB::query("INSERT INTO ".DB::table('home_attachment')." (dateline, readperm, price, filename, filetype, filesize, attachment, downloads, isimage, uid, thumb, remote, width)
				VALUES ('$_G[timestamp]', '0', '0', '".$upload->attach['name']."', '".$upload->attach['type']."', '".$upload->attach['size']."', '".$upload->attach['attachment']."', '0', '".$upload->attach['isimage']."', '".$this->uid."', '$thumb', '$remote', '$width')");
			$this->aid = DB::insert_id();
			$this->uploadmsg(0);
                         *
                         */
                        
                        
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

?>