<?php
/*
 * 用户
 */
require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();

$discuz->init();
set_time_limit(0);
$page=$_G[gp_page];
$start=($page-1)*50;
$sql="select uid,email,username,password,repeatsstatus as repeats_status," .
		"adminid,groupid,newpm as new_pm,newprompt as new_prompt,doindex," .
		"indexsetting as index_setting " .
		"from pre_common_member order by uid limit 0,5000";
$info=DB::query($sql);
$con = mysql_connect("localhost:3306", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("eksn_core", $con);
mysql_query("set names utf8");
while($value=DB::fetch($info)){

	$value_0=DB::fetch_first("select regip as reg_ip,lastip as last_ip,lastvisit as last_visit," .
			"lastactivity as last_activity,lastpost as last_post,follow as follows,fans,blogs," .
			"friends from pre_common_member_status where uid=".$value[uid]);

	$value_1=DB::fetch_first("select realname,gender,birthyear,birthmonth,birthday,constellation," .
			"zodiac,telephone,mobile,idcardtype,idcard,address,zipcode,nationality,graduateschool," .
			"company,education,position,bloodtype,qq,msn,bio,interest,spell as namespell " .
			"from pre_common_member_profile where uid=".$value[uid]);

	$value_1[birthday]=$value_1[birthyear]*10000+$value_1[birthmonth]*100+$value_1[birthday];


	$value_2=DB::fetch_first("select followgroups as follow_groupnum " .
			"from pre_common_member_field_home where uid=".$value[uid]);

	$value_3=DB::fetch_first("select groupname as province " .
			"from pre_user_province where uid=".$value[uid]);

	$data[user]=mysql_query("INSERT INTO esn_user(uid, bio, doindex, email, gender, mobile, namespell, password, province, realname, repeats_status, username) VALUES ($value[uid],'$value_1[bio]',$value[doindex],'$value[email]',$value_1[gender],'$value_1[mobile]','$value_1[namespell]','$value[password]','$value_3[province]','$value_1[realname]',$value[repeats_status],'$value[username]')");
	$data[profile]=mysql_query("INSERT INTO esn_user_profile(uid, address, birthday, bloodtype, company, constellation, education, graduate_school, idcard, idcardtype, interest, msn, nationality, position, qq, telephone, zipcode, zodiac) VALUES ($value[uid],'$value_1[address]',$value_1[birthday],'$value_1[bloodtype]','$value_1[company]','$value_1[constellation]','$value_1[education]','$value_1[graduateschool]','$value_1[idcard]','$value_1[idcardtype]','$value_1[interest]','$value_1[msn]','$value_1[nationality]','$value_1[position]','$value_1[qq]','$value_1[telephone]','$value_1[zipcode]','$value_1[zodiac]')");
	$data[status]=mysql_query("INSERT INTO esn_user_status(uid, adminid, follow_groupnum, group_order, groupid, index_setting, last_activity, last_dateline, last_ip, last_post, reg_ip) VALUES ($value[uid],$value[adminid],$value_2[follow_groupnum],'',$value[groupid],$value[index_setting],$value_0[last_activity],$value_0[last_visit],'$value_0[last_ip]',$value_0[last_post],'$value_0[reg_ip]')");
	$data[statistic]=mysql_query("INSERT INTO esn_user_statistic(uid, blogs, fans, follows, friends, new_pm, new_prompt) VALUES ($value[uid],$value_0[blogs],$value_0[fans],$value_0[follows],$value_0[friends],$value[new_pm],$value[new_prompt])");
	$results[]=$data;
}
echo json_encode($results);
