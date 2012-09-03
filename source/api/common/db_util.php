<?php
/**
 * 创建的全局数据库类
 */


require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');

//mysql数据操作文件
require_once(dirname(dirname(__FILE__)).'/common/db/mysql_class_lt.php');

//mssqlserver数据库操作文件
//require_once(dirname(dirname(__FILE__)).'/common/db/mssql_class_lt.php');


global $db_mysql; //社区数据库操作类
$db_mysql = new mysql4lt();
//global $db_mssql; //基础平台数据库操作类
//$db_mssql = new mssql4lt();





?>