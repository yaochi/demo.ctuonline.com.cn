<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_activity{
	function deletedata($ids,$contenttype,$fid){
	 	require_once libfile('function/group');
		$result=activity_delete_by_useradmin($ids,"true");
		return result;
	}
}
?>