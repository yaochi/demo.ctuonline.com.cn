<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_notice{
	function deletedata($ids,$contenttype,$fid){
	 	if($ids){
			foreach($ids as $id){
				$condition = "id = ".$id;
				$table='notice';
                $res = DB::delete($table, $condition);
				hook_delete_resource($id,"notice");
			}
		}
		return $res;
	}
}
?>