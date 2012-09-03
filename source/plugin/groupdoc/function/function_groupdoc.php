<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-26
 */
function checkperm_group($permtype){
	global $_G;
	
	if(substr($permtype, 0, 6) == 'manage'){
		return $_G['forum']['ismoderator'];
	}else{
		return true;
	}
}
?>
