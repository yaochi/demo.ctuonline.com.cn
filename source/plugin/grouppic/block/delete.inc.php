<?php
class block_delete_grouppic {  
  function deletedata($ids,$contenttype,$fid) {
    require_once dirname(dirname(dirname(__FILE__)))."/groupalbum2/function/function_groupalbsum.php";
	DB :: query("SELECT * FROM " . DB :: table('group_pic') . " WHERE picid IN (" . dimplode($ids) . ")");  
	$group_pic = DB :: fetch($query);
	$albumid = $group_pic['albumid'];
 
  	foreach($ids as $id){
		$deleteids[$id] = $id;
		if($albumid > 0) album_update_pic_group($albumid);
				
		$query = DB::query("SELECT * FROM ".DB::table('group_album')." WHERE albumid='$albumid' LIMIT 1");
		$album = DB::fetch($query); 
		 
	}
  	deletepics_group($deleteids); 	
	return "1";
  }
	
}
?>
