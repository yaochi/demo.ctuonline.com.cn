<?php
class block_delete_selection {  
  function deletedata($ids,$contenttype,$fid) {
	foreach($ids as $id){
		DB :: query("update " . DB :: table('selection') . " set moderated = -1 WHERE selectionid='$id'");
	}
	return "1";
  }
	
}
?>
