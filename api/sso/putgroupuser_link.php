<?php
	$orgId = $_REQUEST["orgId"];
	$fid = $_REQUEST["fid"];
	fopen("http://localhost:9000/servlet/putGroupUserServlet?fid=".$fid."&orgId=".$orgId,"r");
?>
