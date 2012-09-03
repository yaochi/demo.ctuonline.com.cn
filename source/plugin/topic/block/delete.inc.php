<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_topic{
	function deletedata($ids,$contenttype,$fid){
	 	if($ids){
			foreach($ids as $id){
				DB::query("DELETE FROM ".DB::table('forum_threadmod')." WHERE tid='$id'");
				DB::query("DELETE FROM ".DB::table('home_comment')." WHERE id='$id' AND idtype='tid'");
				DB::query("DELETE FROM ".DB::table('home_feed')." WHERE id='$id' AND idtype='tid'");
				$condition = "tid = ".$id;
				$table='forum_thread';
                $res = DB::delete($table, $condition);
				hook_delete_resource($id, "topic");
			}
		}
		return $res;
	}
}
?>