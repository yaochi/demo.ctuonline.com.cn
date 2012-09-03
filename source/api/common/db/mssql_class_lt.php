<?php
/*MSSql的操作类*/  
class mssql4lt {
	var $link;
	var $querynum = 0;
	
	
	/**
	 * 定义一些默认的变量  
	 */
	private $dbhost_mssql_lt 	= DB_HOST_MSSQL_LT;			//主机名  
	private $dbuser_mssql_lt 	= DB_USER_MSSQL_LT; 		//用户
	private $dbpw_mssql_lt		= DB_PW_MSSQL_LT; 			//密码  
	private $dbname_mssql_lt 	= DB_NAME_MSSQL_LT; 		//数据库名

	/* 构造函数
	 *
	 * 连接MSSql数据库，
	 * 参数：dbsn->数据库服务器地址，dbun->登陆用户名，dbpw->登陆密码，dbname->数据库名字
	 * */  
	function mssql4lt($dbhost='', $dbuser='', $dbpw='', $dbname='') {
	

		$dbhost==''?$dbhost=$this->dbhost_mssql_lt:$dbhost;
		$dbuser==''?$dbuser=$this->dbuser_mssql_lt:$dbuser;
		$dbpw==''?$dbpw=$this->dbpw_mssql_lt:$dbpw;
		$dbname==''?$dbname=$this->dbname_mssql_lt:$dbname;
		if($this->link = @mssql_connect($dbhost, $dbuser, $dbpw, true)) {
			//$query = $this->Query('SET TEXTSIZE 2147483647');
			if (@mssql_select_db($dbname, $this->link)) {
			} else {
				$this->halt('Can not Select DataBase');
			}
		} else {
			//$this->halt('Can not connect to MSSQL server');
		}
		
		
	}

	/*执行sql语句，返回对应的结果标识*/  
	function Query($sql) {
		if($query = @mssql_query($sql, $this->link)) {
			$this->querynum++;
			return $query;
		} else {
			$this->querynum++;
			//$this->halt('MSSQL Query Error', $sql);
		}
	}

	/*执行Insert Into语句，并返回最后的insert操作所产生的自动增长的id*/  
	function Insert($table, $iarr) {
		$value = $this->InsertSql($iarr);
		$query = $this->Query('INSERT INTO ' . $table . ' ' . $value . '; SELECT SCOPE_IDENTITY() AS [insertid];');
		$record = $this->GetRow($query);
		$this->Clear($query);
		return $record['insertid'];
	}
	
	
	/*执行Update语句，并返回最后的update操作所影响的行数*/  
	function Update($table, $uarr, $condition = '') {
		$value = $this->UpdateSql($uarr);
		if ($condition) {
			$condition = ' WHERE ' . $condition;
		}
		$query = $this->Query('UPDATE ' . $table . ' SET ' . $value . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');
		$record = $this->GetRow($query);
		$this->Clear($query);
		return $record['rowcount'];
	}

	/*执行Delete语句，并返回最后的Delete操作所影响的行数*/  
	function Delete($table, $condition = '') {
		if ($condition) {
			$condition = ' WHERE ' . $condition;
		}
		$query = $this->Query('DELETE ' . $table . $condition . '; SELECT @@ROWCOUNT AS [rowcount];');
		$record = $this->GetRow($query);
		$this->Clear($query);
		return $record['rowcount'];
	}

	/*将字符转为可以安全保存的mssql值，比如a'a转为a''a*/  
	function EnCode($str) {
		return str_replace("'", "''", str_replace('', '', $str));
      }   
     
       /*将可以安全保存的mssql值转为正常的值，比如a''a转为a'a*/  
		function DeCode($str) {
			return str_replace("''", "'", $str);
       }   
    
       /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回([id], [name]) VALUES (1, 'name')*/  
       function InsertSql($iarr) {   
           if (is_array($iarr)) {   
               $fstr = '';   
               $vstr = '';   
               foreach ($iarr as $key => $val) {   
                   $fstr .= '[' . $key . '], ';   
                   $vstr .= "'" . $val . "', ";   
               }   
               if ($fstr) {   
                   $fstr = '(' . substr($fstr, 0, -2) . ')';   
                   $vstr = '(' . substr($vstr, 0, -2) . ')';   
                   return $fstr . ' VALUES ' . $vstr;   
               } else {   
                  return '';   
              }   
           } else {   
               return '';   
          }   
      }   
    
      /*将对应的列和值生成对应的insert语句，如：array('id' => 1, 'name' => 'name')返回[id] = 1, [name] = 'name'*/  
       function UpdateSql($uarr) {   
           if (is_array($uarr)) {   
              $ustr = '';   
              foreach ($uarr as $key => $val) {   
                 $ustr .= '[' . $key . "] = '" . $val . "', ";   
              }   
             if ($ustr) {   
                 return substr($ustr, 0, -2);   
             } else {   
                 return '';   
             }   
         } else {   
             return '';   
         }   
     }   
   
     /*返回对应的查询标识的结果的一行*/  
     function GetRow($query, $result_type = MSSQL_ASSOC) {   
         return mssql_fetch_array($query, $result_type);   
     }   
    
      /*清空查询结果所占用的内存资源*/  
     function Clear($query) {   
         return mssql_free_result($query);   
      }   
    
      /*关闭数据库*/  
      function Close() {   
         return mssql_close($this->link);   
     }   
   
      function halt($message = '', $sql = '') {   
         $message .= '<br />MSSql Error:' . iconv("gb2312","UTF-8", mssql_get_last_message());   
         if ($sql) {   
              $sql = '<br />sql:' . $sql;   
		}
		exit("DataBase Error.<br />Message:$message $sql");
		
//      	echo $message . ' ' . $sql;
//		exit;
	}
}
?>
