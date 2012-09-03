<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_feed.php 10123 2010-05-07 02:27:04Z liguode $
 */

$lang = array
(

	'feed_blog_password' => '{actor} 发表了新加密日志 {subject}',
	'feed_blog_title' => '{actor} 发表了新记录',
	'feed_blog_body' => '<b>{subject}</b>{summary}',
	'feed_album_title' => '{actor} 更新了相册',
	'feed_album_body' => '<b>{album}</b><br />共 {picnum} 张图片',
	'feed_pic_title' => '{actor} 上传了新图片',
	'feed_pic_body' => '{title}',

	//专区
	'feed_group_join' => '{actor} 加入了 {groupname} 专区',

	'feed_poll' => '{actor} 发起了新投票',

	'feed_comment_space' => '{actor} 在 {touser} 的留言板留了言',
	'feed_comment_image' => '{actor} 评论了 {touser} 的图片',
	'feed_comment_blog' => '{actor} 评论了 {touser} 的日志 {blog}',
	'feed_comment_poll' => '{actor} 评论了 {touser} 的投票 {poll}',
	'feed_comment_topic' => '{actor} 评论了 {touser} 的话题 {topic}',
	'feed_comment_event' => '{actor} 在 {touser} 组织的活动 {event} 中留言了',
	'feed_comment_share' => '{actor} 对 {touser} 分享的 {share} 发表了评论',

	'feed_showcredit' => '{actor} 赠送给 {fusername} 竞价积分 {credit} 个，帮助好友提升在<a href="home.php?mod=space&do=top" target="_blank">竞价排行榜</a>中的名次',
	'feed_showcredit_self' => '{actor} 增加竞价积分 {credit} 个，提升自己在<a href="home.php?mod=space&do=top" target="_blank">竞价排行榜</a>中的名次',
	'feed_doing_title' => '{actor}：{message}',
	'feed_friend_title' => '{actor} 和 {touser} 成为了好友',



	'feed_click_blog' => '{actor} 送了一个“{click}”给 {touser} 的日志 {subject}',
	'feed_click_thread' => '{actor} 送了一个“{click}”给 {touser} 的话题 {subject}',
	'feed_click_pic' => '{actor} 送了一个“{click}”给 {touser} 的图片',
	'feed_click_article' => '{actor} 送了一个“{click}”给 {touser} 的文章 {subject}',


	'feed_task' => '{actor} 完成了有奖任务 {task}',
	'feed_task_credit' => '{actor} 完成了有奖任务 {task}，领取了 {credit} 个奖励积分',

	'feed_profile_update_base' => '{actor} 更新了自己的基本资料',
	'feed_profile_update_contact' => '{actor} 更新了自己的联系方式',
	'feed_profile_update_edu' => '{actor} 更新了自己的教育情况',
	'feed_profile_update_work' => '{actor} 更新了自己的工作信息',
	'feed_profile_update_info' => '{actor} 更新了自己的兴趣爱好等个人信息',
	'feed_add_attachsize' => '{actor} 用 {credit} 个积分兑换了 {size} 附件空间，可以上传更多的图片啦(<a href="home.php?mod=spacecp&ac=credit&op=addsize">我也来兑换</a>)',

	'feed_invite' => '{actor} 发起邀请，和 {username} 成为了好友',

	'feed_notice' => '{username} 发出了一份通知公告 {noticetitle}',

	'feed_selection' => '{username} 新建了一个评选 {questname}',

    'feed_share' => '{username} 分享了一个评选 {title}',

	'feed_resourcelist' => '{username} 新建了一个资源 {resourcetitle}',
	//资源列表（上海专区专用）
	'feed_shresourcelist'=>'{username} 新建了一个资源 {resourcetitle}',


	'feed_questionary' => '{username} 发表一个问卷 {questname}',
	'feed_questionary_answer'=>'{username}回答了问卷{questname}',
	'feed_lecturerecord' => '{actor} 为“{lecname}”老师新增了一条授课记录',

	'magicuse_thunder_announce_title' => '<strong>{username} 发出了“雷鸣之声”</strong>',
	'magicuse_thunder_announce_body' => '大家好，我上线啦<br /><a href="home.php?mod=space&uid={uid}" target="_blank">欢迎来我家串个门</a>',


	'feed_thread_title' =>			'{actor} 发表了新话题',
	'feed_thread_message' =>		'<b>{subject}</b><br />{message}',

	'feed_reply_title' =>			'{actor} 回复了 {author} 的话题 {subject}',
	'feed_reply_title_anonymous' =>		'{actor} 回复了话题 {subject}',
	'feed_reply_message' =>			'',

	'feed_thread_poll_title' =>		'{actor} 发起了新投票',
	'feed_thread_poll_message' =>		'<b>{subject}</b><br />{message}',

	'feed_thread_votepoll_title' =>		'{actor} 参与了关于 {subject} 的投票',
	'feed_reply_votepoll' =>		'{actor} 回复了投票 {subject}',
	'feed_thread_votepoll_message' =>	'',

	'feed_thread_goods_title' =>		'{actor} 出售了一个新商品',
	'feed_thread_goods_message_1' =>	'<b>{itemname}</b><br />售价 {itemprice} 元 附加 {itemcredit}{creditunit}',
	'feed_thread_goods_message_2' =>	'<b>{itemname}</b><br />售价 {itemprice} 元',
	'feed_thread_goods_message_3' =>	'<b>{itemname}</b><br />售价 {itemcredit}{creditunit}',

	'feed_thread_reward_title' =>		'{actor} 发起了新提问',
	'feed_thread_reward_message' =>		'<b>{subject}</b><br />提问悬赏 {rewardprice} 经验值',

	'feed_reply_reward_title' =>		'{actor} 回复了关于 {subject} 的提问',
	'feed_reply_reward_message' =>		'',

	'feed_thread_activity_title' =>		'{actor} 发起了新活动',
	'feed_thread_activity_message' =>	'<b>{subject}</b><br />{message}',

	'feed_reply_activity_title' =>		'{actor} 报名参加了 {subject} 的活动',
	'feed_reply_activity_message' =>	'',

	'feed_thread_debate_title' =>		'{actor} 发起了新辩论',
	'feed_thread_debate_message' =>		'<b>{subject}</b><br />正方：{affirmpoint}<br />反方：{negapoint}<br />{message}',

	'feed_thread_debatevote_title_1' =>	'{actor} 以正方身份参与了关于 {subject} 的辩论',
	'feed_thread_debatevote_title_2' =>	'{actor} 以反方身份参与了关于 {subject} 的辩论',
	'feed_thread_debatevote_title_3' =>	'{actor} 以中立身份参与了关于 {subject} 的辩论',
	'feed_thread_debatevote_message_1' =>	'',
	'feed_thread_debatevote_message_2' =>	'',
	'feed_thread_debatevote_message_3' =>	'',

	//你我课堂
	'feed_nwkt_title' => '{actor} 发表了新你我课堂',
	'feed_nwkt_body' => '<b>{subject}</b><br />{summary}',

	//直播
	'feed_live_title' => '{actor} 创建了新直播',
	'feed_live_body' => '<b>{subject}</b>',
	'feed_live_play_title' => '{actor}参加了直播',
	'feed_live_play_body' => '<b>{subject}</b>',
	'feed_live_replay_title' => '{actor}参加了点播',
	'feed_live_replay_body' => '<b>{subject}</b>',

	'feed_stick_grouplive_title' => '{actor} 的直播被置顶',
	'feed_stick_grouplive_body' => '<b>{title}</b>',
	'feed_stick_no_grouplive_title' => '{actor} 的直播被撤销置顶',
	'feed_stick_no_grouplive_body' => '<b>{title}</b>',
	'feed_digest_grouplive_title' => '{actor} 的直播被加精',
	'feed_digest_grouplive_body' => '<b>{title}</b>',
	'feed_digest_no_grouplive_title' => '{actor} 的直播被撤销加精',
	'feed_digest_no_grouplive_body' => '<b>{title}</b>',

	'feed_comment_notice' => '{actor} 评论了 {touser} 的通知公告 {notice}',

	'feed_click_notice' => '{actor} 送了一个“{click}”给 {touser} 的通知公告{subject}',

	'feed_comment_nwkt' => '{actor} 评论了 {touser} 的你我课堂 {nwkt}',

	'feed_click_nwkt' => '{actor} 送了一个“{click}”给 {touser} 的你我课堂 {subject}',

	'feed_comment_doc' => '{actor} 评论了 {touser} 的文档 {doc}',
	'feed_click_doc' => '{actor} 送了一个“{click}”给 {touser} 的文档 {subject}',
	'feed_comment_case' => '{actor} 评论了 {touser} 的案例 {doc}',
	'feed_comment_class' => '{actor} 评论了 {touser} 的课程 {doc}',

	//文档
	'feed_stick_doc_title' => '{actor} 的文档被置顶',
	'feed_stick_doc_body' => '<b>{title}</b>',
	'feed_stick_no_doc_title' => '{actor} 的文档被撤销置顶',
	'feed_stick_no_doc_body' => '<b>{title}</b>',
	'feed_digest_doc_title' => '{actor} 的文档被加精',
	'feed_digest_doc_body' => '<b>{title}</b>',
	'feed_digest_no_doc_title' => '{actor} 的文档被撤销加精',
	'feed_digest_no_doc_body' => '<b>{title}</b>',
	'feed_doc_title' => '{actor} {title}',
	'feed_doc_body' => '<b> {body} </b>',

	//相册
	'feed_view_pic_title' => '{actor} 查看了图片',
	'feed_view_pic_body' => '{title}',


	//feed
	'feed_feed_title' => '{actor}：{message}',

	//外部培训资源
	'feed_extra_class' => '{fname} 外部培训资源平台 新增了推荐课程 {class}',
	'feed_extra_lec'=>'{fname} 外部培训资源平台 新增了推荐讲师 {lecture}',
	'feed_extra_org' => '{fname} 外部培训资源平台 新增了推荐培训机构 {org}',


);

?>