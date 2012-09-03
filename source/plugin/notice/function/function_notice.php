<?php


function get_notice_types($groupid) {
	global $_G;

	$noticetypes = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('notice_type') . " WHERE group_id='$groupid' ORDER BY id DESC");
	while ($noticetype = DB :: fetch($query)) {
		$noticetypes[] = $noticetype;
	}
	return $noticetypes;
}

?>
