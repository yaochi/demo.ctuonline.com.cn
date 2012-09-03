<?php
class block_delete_grouplive {  

  function deletedata($ids,$contenttype,$fid) {
  require_once (dirname(dirname(__FILE__))."/function/function_live.php");
 	 foreach($ids as $id){
 		 deletelives(array($id));
	 }
	
	return "1";
  }
	
}
?>
