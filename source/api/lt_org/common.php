<?php
require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');

 class commonClass{
 
 	private $dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//�����  
	private $dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//�û�
	private $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//����  
	private $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//��ݿ���

	 function __construct()
  {            
     mssql_connect($this->dbhost_mssql_lt, $this->dbuser_mssql_lt,$this->dbpw_mssql_lt)or die("无法连接到数据库");//链接服务器
	 mssql_select_db($this->dbname_mssql_lt) or die("无法打开数据库连接");			//链接数据库
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
      mssql_close()or die("无法关闭数据库链接");	
	}
	
}
?>