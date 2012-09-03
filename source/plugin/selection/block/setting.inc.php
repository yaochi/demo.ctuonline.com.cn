<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs'][$plugin_id] = array (
	'name' => "评选",
	'script' => array (
		$plugin_id."_selection"=>array(name => "最新评选",
                        style=> array(array(name=>"评选标题", key=>"general"),
						array(name=>"标题 + 作者", key=>"cate_title_author"),
						array(name=>"标题 + 作者 + 摘要", key=>"cate_title_author_desc"),
						array(name=>"标题 + 作者 + 摘要 + 头像", key=>"cate_title_author_desc_photo"),
						array(name=>"标题 + 创建时间",key=>"cate_title_posttime"),
						array(name=>"标题 + 参加数", key=>"cate_title_join"))),
	),
);
?>