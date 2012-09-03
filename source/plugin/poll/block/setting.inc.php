<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

$blockclass['module']['subs'][$plugin_id] = array (
	'name' => "投票",
	'script' => array (
		$plugin_id."_polltitle"=>array(name => "最新投票",
                        style=> array(array(name=>"投票标题", key=>"general"),array(name=>"独立样式", key=>"selfstyle"),
						array(name=>"[标题百搭] (分类) + 标题 + 作者", key=>"cate_title_author"),
                            array(name=>"[标题百搭] (分类) + 标题 + 作者 + 摘要", key=>"cate_title_author_desc"),
                            array(name=>"[标题百搭] (分类) + 标题 + 作者 + 摘要 + 头像", key=>"cate_title_author_desc_photo"),
                            array(name=>"[标题百搭] (分类) + 标题 + 创建时间",key=>"cate_title_posttime"),
                            array(name=>"[标题百搭] (分类) + 标题 + 最后回复时间",key=>"cate_title_lastpost"),
                            array(name=>"[标题百搭] (分类) + 标题 + 查看数", key=>"cate_title_viewnum"),
                            array(name=>"[标题百搭] (分类) + 标题 + 回复数", key=>"cate_title_replynum")
							)),
	),
);
?>
