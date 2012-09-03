<?php
/**
 * esn 积分模块  sns社区平台sqlserver数据库配置文件
 */
$dbhost_mssql_lt	= "127.0.0.1";//主机名  
$dbuser_mssql_lt 	= "sa";			 //用户
$dbpw_mssql_lt		= "sa";			 //密码
$dbname_mssql_lt 	= "ESN";   		 //数据库

//数据库表配置
$tname_lt  = array();
$tname_lt['COMMON_USER'] = 'COMMON_USER'; //用户表
$tname_lt['LTCREDIT_RULE'] = 'LTCREDIT_RULE'; //积分规则表
$tname_lt['LTCREDIT_LOG'] = 'LTCREDIT_LOG'; //积分日志表


define('DB_HOST_MSSQL_LT', $dbhost_mssql_lt);		//主机名  
define('DB_USER_MSSQL_LT', $dbuser_mssql_lt);		//用户
define('DB_PW_MSSQL_LT', $dbpw_mssql_lt ); 			//密码
define('DB_NAME_MSSQL_LT', $dbname_mssql_lt); 		//数据库 
?>