<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

$lang = array
(
	'feed_at'=>'{author}在动态中@(提到)你了，<a href="javascript:;" onclick="top.OpenApp(\'{from_id}\',\'{url}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">快去看看吧</a>',
	'feed_at_wx'=>'{author}在动态中@(提到)你:{message}',
	'feed_at_redirecturl'=>'{url}',
	'feed_at_title'=>'动态@我提醒',
	
	'feed_reply'=>'{author}在动态中回复了你，<a href="javascript:;" onclick="top.OpenApp(\'{from_id}\',\'{url}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">快去看看吧</a>',
	'feed_reply_title'=>'动态回复我提醒',
	'feed_reply_wx'=>'{author}在动态中回复了你:{message}',
	'feed_reply_redirecturl'=>'{url}',
	
	'profile_moderate'=>'有新的待处理{title}，<a href="javascript:;" onclick="top.OpenApp(\'{from_id}\',\'{url}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">现在处理</a>',
	'profile_moderate_wx'=>'有新的待处理{title}',
	'profile_moderate_redirecturl'=>'{url}',
	'profile_moderate_title'=>'{title} 审核提醒',
	
	'user_profile_moderate_pass'=>'{title} 通过了，<a href="javascript:;" onclick="top.OpenWindow(\'profile\',\'{url}\',\'{title}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">现在去看看</a>',
	'user_profile_moderate_pass_wx'=>'{title} 通过了',
	'user_profile_moderate_pass_redirecturl'=>'{url}',
	'user_profile_moderate_pass_title'=>'{title} 提醒',

	'user_profile_moderate_refusal'=>'{title} 被拒绝：{profile} {reason} <a href="javascript:;" onclick="top.OpenWindow(\'profile\',\'{url}\',\'{title}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">现在去看看</a>',
	'user_profile_moderate_refusal_wx'=>'{title} 被拒绝：{profile} {reason}',
	'user_profile_moderate_refusal_redirecturl'=>'{url}',
	'user_profile_moderate_refusal_title'=>'{title} 提醒',
	
	'user_profile_pass_refusal'=>'您通过审核的{title}已被拒绝！ <a href="javascript:;" onclick="top.OpenWindow(\'profile\',\'{url}\',\'{title}\');_notice.setIsread(jQuery(this).parent().parent().parent().attr(\'nid\'));">现在去看看</a>',
	'user_profile_pass_refusal_wx'=>'您通过审核的{title}已被拒绝！',
	'user_profile_pass_refusal_redirecturl'=>'{url}',
	'user_profile_pass_refusal_title'=>'{title} 提醒',
);

?>