<?php
	$keytime=$_GET['keytime'] ? $_GET['keytime'] : 1;
	$fid=$_GET['fid'] ? $_GET['fid'] : -1;
	$url=$_GET['url'] ;
	
	if($keytime!=-1){
		$key=time();
		$key=$key+($keytime*24*60*60);
		$key=base64_encode($key);
		$key=urlencode($key);
		$keyurl="&key={$key}";
	}else{
		$key=base64_encode('group'.$fid);
		$key=urlencode($key);
		$keyurl="&key={$key}";
	}
?>
<root><![CDATA[
<?php
	echo($keyurl);
?>
]]></root>