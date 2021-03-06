<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_template.php 10798 2010-05-17 02:03:17Z zhangguosheng $
 */

$lang = array
(
	'login_invisible_mode' => '隐身',
	'login_switch_invisible_mode' => '切换在线状态',
	'login_normal_mode' => '在线',
	'anonymous' => '匿名',
	'member' => '会员',
	'finished' => '完成',
	'space_short' => '空间',
	'notice' => '提醒',
	'find' => '查找',
	'choose_please' => '请选择',
	'option_required' => '必选项',
	'more' => '更多',
	'morethread' => '更多话题',
	'pm_center' => '短消息',
	'task_unfinish' => '您有未完成的任务',
	'task' => '任务',
	'edit' => '编辑',
	'delete' => '删除',
	'user_center' => '个人中心',
	'user_index' => '个人空间',
	'forum_manager' => '{$_G[setting][navs][2][navname]}管理',
	'manager' => '管理',
	'administrator' => '管理员',
	'admincp' => '管理中心',
	'faq' => '帮助',
	'search' => '搜索',
	'logout' => '退出',
	'activation' => '激活',
	'login' => '登录',
	'login_permanent' => '记住我的登录状态',
	'contactus' => '联系我们',
	'time_now' => 'GMT{$_G[timenow][offset]}, {$_G[timenow][time]}',
	'credits' => '积分',
	'discuzcode' => 'Discuz 代码',
	'smilies' => '表情',
	'thread_poll' => '投票',
	'thread_trade' => '商品',
	'thread_activity' => '活动',
	'thread_reward' => '提问吧',
	'thread_rewardend' => '已解决',
	'thread_debate' => '辩论',
	'submit' => '提交',
	'ignore_all' => '忽略全部',
	'pass_all' => '全部通过',
	'reward_answer' => '我来回答',
	'activity_already' => '已参加人数',
	'activity_about_member' => '剩余名额',
	'debate_square' => '正方',
	'debate_opponent' => '反方',
	'board_message' => '提示信息',
	'message_forward' => '如果您的浏览器没有自动跳转，请点击此链接',
	'attach_forward' => '如果 {$refreshsecond} 秒后下载仍未开始，请点击此链接',
	'message_go_back' => '[ 点击这里返回上一页 ]',
	'open' => '展开',
	'close' => '关闭',
	'update' => '更新',
	'return' => '返回',
	'confirms' => '确定',
	'cancel' => '取消',
	'setup' => '设置',
	'medals' => '勋章',
	'favorite' => '收藏',
	'additional' => '附加',
	'article' => '文章',
	'image' => '图片',
	'style' => '风格',
	'save' => '保存',
	'unit' => '个',
	'selected' => '已选',
	'unselected' => '未选',
	'remote' => '远程',
	'author' => '作者',
	'batch' => '批量',

	'task_new' => '新任务',
	'task_doing' => '进行中的任务',
	'task_done' => '已完成的任务',
	'task_failed' => '失败的任务',
	'task_reward' => '奖励',
	'task_complete_on' => '完成于',
	'task_lose_on' => '失败于',
	'task_applies' => '人气',
	'task_reapply' => '后可以重新申请',
	'task_reapply_now' => '现在可以重新申请',
	'task_applyagain_now' => '现在可以再次申请',
	'task_applyagain' => ' 后可以再次申请',
	'task_group_nopermission' => '您所在的用户组无法申请此任务',
	'task_applies_full' => '人数已满',
	'task_detail' => '任务详情',
	'task_endtime' => '当前任务下线时间为 $task[endtime]，过期后您将不能申请此任务',
	'task_period_hour' => '每隔 $task[period] 小时允许申请一次',
	'task_period_day' => '每 $task[period] 天允许申请一次',
	'task_period_week' => '每周 $periodweek 允许申请一次',
	'task_period_month' => '每月 $task[period] 日允许申请一次',
	'task_apply_condition' => '申请此任务所需条件',
	'task_general_users' => '普通会员',
	'task_admins' => '管理人员',
	'task_relatedtask' => '必须完成指定任务',
	'task_numlimit' => '人次上限',
	'task_complete_condition' => '完成此任务所需条件',
	'task_applicants' => '已有 $task[applicants] 位会员参与此任务',
	'task_complete' => '已完成',
	'task_complete_time_start' => '从申请任务开始计时，',
	'task_complete_time_limit' => '$taskvars[complete][time][value] 小时内，',
	'task_complete_act_buddy' => '添加 $taskvars[complete][num][value] 个好友。',
	'task_complete_act_favorite' => '收藏 $taskvars[complete][num][value] 个主题。',
	'task_complete_act_magic' => '使用 $taskvars[complete][num][value] 次道具。',
	'task_complete_forumid' => '在版块 <a href="forum.php?mod=forumdisplay&fid=$taskvars[complete][forumid][value]" target="_blank">{$_G[cache][forums][$taskvars[complete][forumid][value]][name]}</a> ',
	'task_complete_act_newthread' => '发新主题 $taskvars[complete][num][value] 次。',
	'task_complete_act_newpost' => '发新主题/回复 $taskvars[complete][num][value] 次。',
	'task_complete_act_newreply_thread' => '回复主题“<a href="forum.php?mod=viewthread&tid=$taskvars[complete][threadid][value]" target="_blank">$subject</a>” $taskvars[complete][num][value] 次。',
	'task_complete_act_newreply_author' => '回复作者“<a href="home.php?mod=space&uid=$taskvars[complete][authorid][value]" target="_blank">$author</a>”的主题 $taskvars[complete][num][value] 次。',
	'task_nonew' => '暂无新任务，周期性任务完成后可以再次申请，敬请关注！',
	'task_nodoing' => '暂无进行中的任务，请到新任务中申请！',

	'faq_search' => '搜索帮助',
	'faq_search_title_and_content' => '搜索帮助标题和内容',
	'faq_search_title' => '搜索帮助标题',
	'faq_search_content' => '搜索帮助内容',
	'faq_search_nomatch' => '对不起，没有找到匹配结果',
	'faq_related' => '相关帮助',

	'focus_hottopics' => '站长推荐',
	'focus_show' => '查看',

	'e_removeformat' => '清除文本格式',
	'e_paste' => '粘贴',
	'e_undo' => '撤销',
	'e_redo' => '重做',
	'e_bold' => '文字加粗',
	'e_italic' => '文字斜体',
	'e_underline' => '文字加下划线',
	'e_hr' => '添加分隔线',
	'e_strike' => '文字加删除线',
	'e_bold_title' => '粗体',
	'e_italic_title' => '斜体',
	'e_underline_title' => '下划线',
	'e_hr_title' => '分隔线',
	'e_strike_title' => '删除线',
	'e_fontname' => '设置字体',
	'e_fontoptions' => '"仿宋_GB2312", "黑体", "楷体_GB2312", "宋体", "新宋体", "微软雅黑", "Trebuchet MS", "Tahoma", "Arial", "Impact", "Verdana", "Times New Roman"',
	'e_fontsize' => '设置文字大小',
	'e_forecolor' => '设置文字颜色',
	'e_paragraph' => '段落排版',
	'e_center' => '居中',
	'e_left' => '居左',
	'e_right' => '居右',
	'e_autotypeset' => '自动排版',
	'e_floatleft' => '左浮动',
	'e_floatright' => '右浮动',
	'e_list' => '添加列表',
	'e_orderedlist' => '排序的列表',
	'e_unorderedlist' => '未排序列表',
	'e_contract' => '收缩编辑框',
	'e_expand' => '扩展编辑框',
	'e_url' => '添加链接',
	'e_unlink' => '移除链接',
	'e_email' => '添加邮箱链接',
	'e_smilies' => '表情',
	'e_smilies_title' => '添加表情',
	'e_image' => '图片',
	'e_image_title' => '添加图片',
	'e_attach' => '附件',
	'e_attach_title' => '添加附件',
	'e_quote' => '引用',
	'e_quote_title' => '添加引用文字',
	'e_code' => '代码',
	'e_code_title' => '添加代码文字',
	'e_table' => '添加表格',
	'e_free' => '添加免费信息',
	'e_hide' => '添加隐藏内容',
	'e_audio' => '音乐',
	'e_audio_title' => '添加音乐',
	'e_video' => '视频',
	'e_video_title' => '添加视频',
	'e_flash' => 'Flash',
	'e_flash_title' => '添加 Flash',
	'e_img_www' => '网络图片',
	'e_img_albumlist' => '相册图片',
	'e_img_width' => '宽(可选)',
	'e_img_height' => '高(可选)',
	'e_img_local' => '上传图片',
	'e_img_album' => '我的相册',
	'e_img_attach' => '图片列表',
	'e_img_inserturl' => '请输入图片地址',
	'e_img_insertphoto' => '点击图片添加到帖子内容中',
	'e_attach_insert' => '点击文件名添加到帖子内容中',
	'e_attach_del'=> '删除',
	'e_attach_url' => '添加附件地址',
	'e_attach_mediacode' => '添加附件媒体播放代码',
	'post_savedata' => '保存数据',
	'post_autosave_restore' => '恢复数据',
	'post_check_length' => '字数检查',
	'post_topicreset' => '清空内容',

	'seccode' => '验证码',
	'seccode_change' => '换一个',
	'secqaa' => '验证问答',
	'seccode_comment' => '请在空白处输入图片中的数字',
	'secqaa_comment' => '您必须正确回答下面的问题才能通过注册:',
	'register_profile_seccode_invalid' => '验证码输入错误，请重新填写。',
	'register_profile_secqaa_invalid' => '验证问答回答错误，请重新填写。',

	'my_space' => '我的翼博',
	'new_message' => '您有新短消息',
	'recent_contacts' => '最近联系人',
	'doing' => '记录',
	'album' => '相册',
	'blog' => '日志',
	'upload' => '上传',
	'share' => '转发',
	'add' => '添加',
	'thread' => '话题',
	'group' => '专区',
	'create' => '创建',
	'create_on' => '创建于',
	'activity' => '活动',
	
	'question' => '问吧',
	'resourceid' => '资源列表',
	'questionary' => '问卷',
	'glive' => '直播',
	
	'launch' => '发起',
	'poll' => '投票',
	'reward' => '提问吧',
	'debate' => '辩论',
	'trade' => '商品',
	'sale' => '出售',
	'friends' => '好友',
	'fans'=>'粉丝',
	'invite' => '邀请',
	'task' => '任务',
	'no_login' => '你没有登录',
	'publish' => '发布',
	'magic' => '道具',
	'favorite' => '收藏',
	'nwkt' => '你我课堂',
	'doc' => '文档',

	'enter_content' => '请输入搜索内容',
	'open_diy' => '打开 DIY 面板',
	'fourm' => '论坛',
	'setup' => '设置',
	'remind' => '提醒',
	'portal' => '门户',
	'portal_manage' => '门户管理',
	'styleswitch' => '风格',

	'report_current_page' => '举报当前页面',

	'userabout_thread' => '帖子',

	'preview_navcur' => '当前',
	'preview_nav' => '导航',
	'preview_msg' => '<a href="javascript:;" class="lit">Crossday Discuz! Board</a> 社区系统（简称 <stong class="xw1 xi1">Discuz!</stong>）是一个采用 PHP 和 MySQL 等其他多种数据库构建的高效建站解决方案。',
	'preview_link' => '链接文字',
	'preview_highlightlink' => '高亮链接',
	'preview_smalltext' => 'SMALL FONT',
	'preview_text' => '普通文本',
	'preview_midtext' => '中等文本',
	'preview_lighttext' => '浅色文字',
	'preview_inputtext' => '我是一个输入框',
	'preview_footertext' => '版权及页脚信息',

	'editor_increase' => '加大编辑框',
	'editor_narrow' => '缩小编辑框',

	'header_savecache' => '暂存',
	'header_savecache_desc' => '暂时保存您DIY的数据，可下次继续',
	'header_perview' => '预览',
	'header_perview_desc' => '预览DIY的效果',
	'header_restore_backup' => '恢复备份',
	'header_restore_backup_desc' => '恢复备份',
	'header_export' => '导出',
	'header_export_desc' => '导出当前页面中所有DIY数据',
	'header_import' => '导入',
	'header_import_desc' => '将DIY数据导入到当前页面',
	'header_update' => '更新',
	'header_update_desc' => '更新当前页面所有模块的数据',
	'header_clearall' => '清空',
	'header_clearall_desc' => '清空页面上所在DIY数据',
	'header_savecachemsg' => '页面已经暂存',
	'header_more_actions' => '更多操作',
	'header_start' => '开始',
	'header_frame' => '框架',
	'header_tabframe' => 'tab框架',
	'header_module' => '模块',
	'header_template' => '模板', //add by songsp
	'header_diy' => '自定义',
	'header_add_frame' => '添加框架',
	'header_add_module' => '添加模块',

	'invite_friend' => '邀请好友',
	'invite_orderby_name' => '按好友用户名查找',
	'invite_orderby_friend' => '按好友分组查找',
	'invite_all_friend' => '全部好友',
	'invite_still_choose' => '还能选择',
	'invite_send' => '发送邀请',

	'report_reason' => '举报理由',
	'report' => '举报',

	'prompt' => '提示',

	'query_user' => '查找用户',
	'query_orderby_name' => '按姓名查找',
	'query_all_user' => '全部用户',
	'query_still_choose' => '还能选择',
	'query_select' => '选择用户',
);

?>