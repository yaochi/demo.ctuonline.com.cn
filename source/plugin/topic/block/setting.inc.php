<?php
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['module']['subs']['topic_list'] = array(
					"name" => "话题",
					"script" => array(
						$plugin_id."_topiclist"=>array(name => "最新话题",
                        style=> array(
                            array(name=>"话题标题", key=>"general"), array(name=>"焦点模式", key=>"focus"), array(name=>"热议", key=>"hot"),
                            array(name=>"突出内容", key=>"out"), array(name=>"(分类) + 标题 + 作者", key=>"cate_title_author"),
                            array(name=>"主题 + 作者/时间 + 回复 + 最后发表", key=>"title_author_reply_lastpost"),
                            array(name=>"[标题百搭] (分类) + 标题 + 作者 + 摘要", key=>"cate_title_author_desc"),
                            array(name=>"[标题百搭] (分类) + 标题 + 作者 + 摘要 + 头像", key=>"cate_title_author_desc_photo"),
                            array(name=>"[标题百搭] (分类) + 标题 + 发帖时间",key=>"cate_title_posttime"),
                            array(name=>"[标题百搭] (分类) + 标题 + 最后回复时间",key=>"cate_title_lastpost"),
                            array(name=>"[标题百搭] (分类) + 标题 + 查看数", key=>"cate_title_viewnum"),
                            array(name=>"[标题百搭] (分类) + 标题 + 回复数", key=>"cate_title_replynum"),
                            )),
                            $plugin_id."_topiclist2"=>array(name => "相册话题",
                        style=> array(
                            array(name=>"相册墙", key=>"pic_wall"),
                            )),
					)
);
?>
