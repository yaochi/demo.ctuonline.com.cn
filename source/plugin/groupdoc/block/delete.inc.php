<?php
class block_delete_groupdoc {  

  function deletedata($ids,$contenttype,$fid) {
  	require_once (dirname(dirname(__FILE__))."/function/function_doc.php"); 
	global $_G;
  	deleteFiles($_G['uid'],$ids);
	return "1";
  }
	
}
?>
