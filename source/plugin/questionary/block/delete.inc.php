<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_questionary{
	function deletedata($ids,$contenttype,$fid){
	 	if($ids){
			foreach($ids as $id){
				DB :: query("DELETE FROM " . DB :: table('questionary_question') . " WHERE questid='$id'");
				DB :: query("DELETE FROM " . DB :: table('questionary_questionoption') . " WHERE questid='$id'");
				DB :: query("DELETE FROM " . DB :: table('questionary_questionchoicers') . " WHERE questid='$id'");
				$condition = "questid = ".$id;
				$table='questionary';
                $res = DB::delete($table, $condition);
				hook_delete_resource($id,'question');
			}
		}
		return $res;
	}
}
?>