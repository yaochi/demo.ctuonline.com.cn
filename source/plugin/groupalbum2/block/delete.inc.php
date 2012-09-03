<?php
class block_delete_groupalbum2 {  
  function deletedata($ids,$contenttype,$fid) {
  foreach($ids as $albumid){
  	$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('no_privilege');
	}
	if($album['uid'] != $_G['uid'] && !checkperm_group('managealbum')) {
		showmessage('no_privilege');
	}

	$albums = getalbums_group($album['uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege');
	}

	 
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			require_once libfile('function/delete');
			deletealbums_group(array($albumid));
			hook_delete_resource($albumid, "ablum");
		} else {
			if($_POST['moveto'] > 0 && $_POST['moveto'] != $albumid && !empty($albums[$_POST['moveto']])) {
				DB::update('group_pic', array('albumid'=>$_POST['moveto']), array('albumid'=>$albumid));
				album_update_pic_group($_POST['moveto']);
			} else {
				DB::update('group_pic', array('albumid'=>0), array('albumid'=>$albumid));
			}
			DB::query("DELETE FROM ".DB::table('group_album')." WHERE albumid='$albumid'");
			hook_delete_resource($albumid, "ablum");
		}
		return "1";
	 
  }
  }
	
}
?>
