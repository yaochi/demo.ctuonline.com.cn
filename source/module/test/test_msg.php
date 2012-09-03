<?php
require_once libfile("function/core");

echo notification_add(0, 'system', 'user_usergroup', array(
						'usergroup' => '<a href="home.php?mod=spacecp&ac=credit&op=usergroup">哈哈</a>',
					), 1);

DB::query("SELECT * FROM ".DB::table("forum_forum"));


require_once libfile("function/org");
var_dump(getOrgNameByUser("test"));
?>
