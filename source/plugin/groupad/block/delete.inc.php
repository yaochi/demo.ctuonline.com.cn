<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_groupad{
	function deletedata($ids,$contenttype,$fid){
	 	if($ids){
			foreach($ids as $id){
				$condition = "id = ".$id;
				$table='groupad';
                $res = DB::delete($table, $condition);
			}
		}
		return $res;
	}
}
?>