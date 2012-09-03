<?php
/*
 * 提问吧插件配置文件信息
 * @author fumz
 * @since 2010-7-21 9:00:48
 * @version v1.0
 */
$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));


$blockclass['module']['subs']['qbar'] = array(
	'name' => '提问吧',
	'script' => array(
		//$plugin_id."_qbar" => "专区提问吧组件",
		$plugin_id."_newquestion" =>array(name=>"最新提问",
										  style=> array(array(name=>"提问标题", key=>"general"),array(name=>"提问详细列表", key=>"question_multi"),array(name=>"[标题百搭] (分类) + 标题 + 作者 + 创建时间", key=>"question_multi3"),array(name=>"[标题百搭] (分类) + 标题 + 作者", key=>"question_multi2"),array(name=>"独立样式", key=>"question_self"))),
		$plugin_id."_solvedquestion" =>array(name=>"已解决的提问",
											 style=> array(array(name=>"提问标题", key=>"general"),array(name=>"提问详细列表", key=>"question_multi") ,array(name=>"[标题百搭] (分类) + 标题 + 作者 + 创建时间", key=>"question_multi3"),array(name=>"[标题百搭] (分类) + 标题 + 作者", key=>"question_multi2"),array(name=>"独立样式", key=>"question_self"))),
		$plugin_id."_notsolvedquestion"=>array(name=>"未解决的提问",
										  		style=> array(array(name=>"提问标题", key=>"general"),array(name=>"提问详细列表", key=>"question_multi") ,array(name=>"[标题百搭] (分类) + 标题 + 作者 + 创建时间", key=>"question_multi3"),array(name=>"[标题百搭] (分类) + 标题 + 作者", key=>"question_multi2"),array(name=>"独立样式", key=>"question_self"))),
		),
);

?>
