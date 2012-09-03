<?php
/**
 * 
 * @author fumz
 * 2010-11-26 15:53:20
 */
class block_delete_qbar{
	function deletedata($ids,$contenttype,$fid){
		require_once libfile("function/delete");
		$result=deleteqbar($ids,$fid);
		return result;
	}
}
?>