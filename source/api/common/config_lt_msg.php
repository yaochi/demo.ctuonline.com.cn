<?php
/**
 * esn 消息模块  sns社区平台Mysql数据库配置文件
 */

$dbhost_lt 		= "10.127.1.36"; 	//主机名  
$dbuser_lt 		= "esntest";		//用户
$dbpw_lt 		= "!@#$%^2010";		//密码
$dbname_lt 		= "forum1114";   	//数据库
$dbcharset_lt 	= "utf8";		//字符集

/**
 * esn 消息模块  基础（综合）平台MS SqlServer数据库配置文件
 */
 //10.127.1.32
$dbhost_mssql_lt	= "10.127.1.32";//主机名
$dbuser_mssql_lt 	= "sa";			 //用户
$dbpw_mssql_lt		= "sa";			 //密码
$dbname_mssql_lt 	= "ESN";   		 //数据库

//数据库表配置
global $tname_lt;
$tname_lt  = array();
$tname_lt['uchome_notification'] = 'pre_home_notification'; // discuz X 中通知表
$tname_lt['lt_notice_log'] = 'lt_notice_log'; //通知发送日志表
$tname_lt['lt_system_msg'] = 'lt_system_msg'; //系统类型消息表

$tname_lt['COMMON_USER'] = 'COMMON_USER'; //�û���
$tname_lt['LTCREDIT_RULE'] = 'LTCREDIT_RULE'; //��ֹ����
$tname_lt['LTCREDIT_LOG'] = 'LTCREDIT_LOG'; //�����־��

$tname_lt['LTUSER'] = 'LTUSER'; //用户信息表
$tname_lt['LTUSER_VISIT'] = 'LTUSER_VISIT'; //用户能力系统表



define('DB_HOST_LT', $dbhost_lt);		//主机名  
define('DB_USER_LT', $dbuser_lt);		//用户
define('DB_PW_LT', $dbpw_lt ); 			//密码
define('DB_NAME_LT', $dbname_lt); 		//数据库 
define('DB_CHARSET_LT', $dbcharset_lt);	//字符集

define('DB_HOST_MSSQL_LT', $dbhost_mssql_lt);		//主机名   
define('DB_USER_MSSQL_LT', $dbuser_mssql_lt);		//用户
define('DB_PW_MSSQL_LT', $dbpw_mssql_lt ); 			//密码
define('DB_NAME_MSSQL_LT', $dbname_mssql_lt); 		//数据库 



//消息模块  时间戳级别设置
//1000 毫秒 ；1 秒
global $dateline_type;
$dateline_type = 1;

/*
//各个表字段中文是否需要从GBK->UTF-8
global $is_encode;
$is_encode  = array();
$is_encode['uchome_notification'] = false; //通知表  ，为GBk编码
$is_encode['lt_notice_log'] = true; //通知发送日志表 ，为UTF-8编码
$is_encode['lt_system_msg'] = true; //系统类型消息表 ，为UTF-8编码
*/
?>