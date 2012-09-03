<?php
require_once libfile("function/share_log");

$shares = share_log_get_type("thread",100);
foreach($shares as $share)
{
	echo $share[rid]."(".$share[total].")<br />";
}

?>
