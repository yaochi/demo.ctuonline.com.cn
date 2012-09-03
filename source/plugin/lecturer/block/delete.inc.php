<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_lecturer{
	function deletedata($ids,$contenttype,$fid){
	 	if($ids){
			foreach($ids as $id){
				$condition = "lecid = ".$id." AND fid=".$fid;
				$table='forum_forum_lecturer';
                $res = DB::delete($table, $condition);
			}
		}
		return $res;
	}
}
?>