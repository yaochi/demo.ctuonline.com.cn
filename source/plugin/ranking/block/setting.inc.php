<?php

$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
$blockclass['noticeinfo']["subs"]["ranking"] = array(
        "name" => "排行榜" . $plugin_id,
        "script" => array(
			$plugin_id . "_ranking1" => array(name => "话题",
	            style=>array(
	            	array(name=>"标题+点击次数",key=>"title_viewnum"),
	            	array(name=>"标题+评论次数",key=>"title_commentnum"),
	            )
	        ),
			$plugin_id . "_ranking2" => array(name => "相册",
	            style=>array(
	            	array(name=>"标题+图片数",key=>"title_picnum"),
	            )
	        ),
			$plugin_id . "_ranking3" => array(name => "提问吧",
	            style=>array(
	            	array(name=>"标题+点击次数",key=>"title_viewnum"),
	            	array(name=>"标题+评论次数",key=>"title_commentnum"),
	            )
	        ),
			$plugin_id . "_ranking4" => array(name => "问卷",
	            style=>array(
	            	array(name=>"标题+参与人数",key=>"title_joinnum"),
	            )
	        ),
			$plugin_id . "_ranking5" => array(name => "投票",
	            style=>array(
	            	array(name=>"标题+参与人数",key=>"title_joinnum"),
	            	array(name=>"标题+点击人数",key=>"title_viewnum"),
	            	array(name=>"标题+评论次数",key=>"title_commentnum"),
	            )
	        ),
			$plugin_id . "_ranking6" => array(name => "直播",
	            style=>array(
	            	array(name=>"标题+查看数",key=>"title_viewnum"),
	            	array(name=>"标题+点播数",key=>"title_playnum"),
	            )
	        ),
			$plugin_id . "_ranking7" => array(name => "资源列表",
	            style=>array(
	            	array(name=>"标题+平均分",key=>"title_averagescorenum"),
	            	array(name=>"标题+查看次数",key=>"title_viewnum"),
	            	array(name=>"标题+评论次数",key=>"title_commentnum"),
	            	array(name=>"标题+转发次数",key=>"title_sharenum"),
	            	array(name=>"标题+收藏次数",key=>"title_favoritenum"),
	            )
	        ),
			$plugin_id . "_ranking8" => array(name => "社区文档",
	            style=>array(
	            	array(name=>"标题+平均分",key=>"title_averagescorenum"),
	            	array(name=>"标题+查看次数",key=>"title_viewnum"),
	            	array(name=>"标题+评论次数",key=>"title_commentnum"),
	            	array(name=>"标题+转发次数",key=>"title_sharenum"),
	            	array(name=>"标题+收藏次数",key=>"title_favoritenum"),
	            )
	        ),
			$plugin_id . "_ranking9" => array(name => "专区",
	            style=>array(
	            	array(name=>"标题+参与人数",key=>"title_joinnum"),
	            	array(name=>"标题+活跃度",key=>"title_hotnum"),
	            )
	        ),
			$plugin_id . "_ranking10" => array(name => "活动",
	            style=>array(
	            	array(name=>"标题+参与人数",key=>"title_joinnum"),
	            	array(name=>"标题+点击人数",key=>"title_viewnum"),
	            	array(name=>"标题+平均分",key=>"title_averagescorenum"),
	            )
	        ),
			$plugin_id . "_ranking11" => array(name => "成员",
	            style=>array(
	            	array(name=>"标题+经验值",key=>"title_empiricalnum"),
	            	array(name=>"标题+积分",key=>"title_creditnum"),
	            )
	        ),
			
			$plugin_id . "_ranking13" => array(name => "评选",
	            style=>array(
	            	array(name=>"标题+参与人数",key=>"title_joinnum"),
	 
	            )
	        ),
		),
);
?>
