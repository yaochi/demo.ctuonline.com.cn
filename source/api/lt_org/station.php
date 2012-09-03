<?php
/*
 * Created on 2010-5-19
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once(dirname(__FILE__)."/common.php");

 class Station extends commonClass{
 	
 	 function __construct()
  {            
  	parent::__construct();
  }
        
   //__destruct：析构函数，断开连接
 	function __destruct()
  	{
     parent::__destruct();
	}
	
 	/* 查找数据库公用方法,返回的是array */
  	function listpage($sql,$obj)
	{
		if(empty($sql))
		{
			return -2;//接口调用失败
		}		
		$s = mssql_query($sql);
		$info =mssql_fetch_array($s);
		if($info==False){
			mssql_free_result($s); 		//释放结果集
           	return 0;//结果为空
   	 	}
    	else{       
    		do{
				$obj[] = $info;
				//每一行
				
     		 }  while($info=mssql_fetch_array($s));
	  
	  		mssql_free_result($s); 		//释放结果集
	  		return $obj;
		}
	}
 
 	/* 2.1 根据职位编码查找职位信息 */
 	function getStation($code)
 	{
 		if(empty($code))
 		{
 			return -1;
 		}else{
 			$sql = "select s_id ,s_code,s_name,parent_id,Corp_id,remark from ltstation where s_code  = '".$code."'";
			$s = mssql_query($sql);
			$info =mssql_fetch_array($s);
			if($info==False){
            	mssql_free_result($s); 		//释放结果集
            	return 0;
				
   	 		}
    		else{   
	  			return $info;
	  			mysql_free_result($s); 		//释放结果集
			}
 		}
 		
 	}
 	
 	/* 2.2 根据职位编码查找职位id */
 	function getStationId($code)
 	{
 		if(empty($code))
 		{
 			return -1;
 		}else{
 			
 			$sql = "select s_id from ltstation where s_code  = '".$code."'";
			$s = mssql_query($sql);
			$info =mssql_fetch_array($s);
			if($info==False){
              	mssql_free_result($s); 		//释放结果集
            	return 0;
				
   	 		}
    		else{   
	  			return $info[s_id];
	  			mysql_free_result($s); 		//释放结果集
			}
 		}
 	}
 	
 	/* 2.3 根据用户id查找职位信息,优先从cache里面读取 ,王聪*/	
 	function getStationByUser($user_id)
 	{
 		if(empty($user_id))
 		{
 			return -1;//传入参数为空
 		}else{
			//$usersql = " select azUserId from ltuser where id = '".$user_id."'";
			//$users = mssql_query($usersql);
			//$userinfo =mssql_fetch_array($users);
            //mssql_free_result($users);$userinfo['azUserId']
            
 		global $openMemcache;
		global $memcached;
		global $token ;//分隔符		
		//===使用 memcache ========================
 	 	if($openMemcache==1){ //使用memcache 
 	 		//根据用户id，查找到用户的岗位id	
 	 		$key = 'us_'.$user_id;			
			$temp = $memcached->get($key);//
			$tmp_userStation = explode($token, $temp);
			$tem_station=$tmp_userStation[1];
			
			if($tem_station){
				//通过岗位id找到岗位信息
				$temp = $memcached->get("gw_".$tem_station);//
				$temp_stationByuser = explode($token, $temp);
				if($temp_stationByuser){
					$result[] = array(
								0 =>$temp_stationByuser[0] ,"s_id" =>$temp_stationByuser[0] ,
								1 => $temp_stationByuser[2] ,"s_name" => $temp_stationByuser[2] 								
							);
			   		return   $result;
				}
				
			}
 	 	} 	 	
 	 	//===end 使用 memcache ========================	 	
 	 	//echo "from database"; 
 	 	
 			$sql = "select ltstation.s_id s_id ,ltstation.s_name s_name from ltstation ltstation,ltuser_station userstation  where userstation.s_id = ltstation.s_id and userstation.user_id = ".$user_id."";
 			return $this->listpage($sql,array());
 		}
 	}
 	

	/* 2.4 获取所有的岗位 */	
 	function getAllstation($currentPage,$pageSize)
 	{
		if(empty($currentPage) || empty($pageSize))
		{
			$currentPage = 0;
			$pageSize = 20;
		}

 			$sql = " select top ".$pageSize." *  from LTSTATION where s_id not in ( select top ".($currentPage != 0 ? $currentPage - 1 : $currentPage)* $pageSize."  s_id from LTSTATION order by s_id ) order by s_id "  ;

 			$s = mssql_query($sql);
 	 		$info = mssql_fetch_array($s);



			$countSql="select count(*) from LTSTATION ";
            $counts = mssql_query($countSql);
 	 		$countinfo = mssql_fetch_array($counts);

 	 		if($info==False)
 	 		{
 	 			return 0;
 	 		}else{
 	 			do{
 	 				$obj[] = $info;
 	 			}while($info = mssql_fetch_array($s));
                mssql_free_result($s);

				do{
 	 				$countobj = $countinfo;
 	 			}while($countinfo = mssql_fetch_array($counts));

 	 			
				mssql_free_result($counts);
				return array($obj,$countobj);
 	 			
 	 		}
 	}


    /* 2.5 获取岗位下的用户列表 */	
 	function getUserbystationId($statuionId)
 	{
 		if(empty($statuionId))
 		{
 			return -1;
 		}else{
 			//$sql = " select ltuser.*  from ltuser right join LTUSER_STATION on  ltuser.azUserId=LTUSER_STATION.User_id   and LTUSER_STATION.S_id = ".$statuionId."";

			$sql ="select * 

			from ltuser 

			where id in (select ltuser.id

							from   ltuser ,LTUSER_STATION

							where ltuser.azUserId=LTUSER_STATION.User_id   and LTUSER_STATION.S_id = ".$statuionId."
							) ";
 			return $this->listpage($sql,array());
 		}
 	}

	/* 2.6 获取所有的岗位,不分页 */	
 	function getAllstations()
 	{
		 
 		$sql = " select s_id, s_name,parent_id from LTSTATION  order by s_id "  ;

 		$s = mssql_query($sql);
 	 	$info = mssql_fetch_array($s);
		if($info==False)
		{
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
 	}
	
	function getStationsByParentId($parent_id)
 	{
		 
 		$sql = " select s_id, s_name,parent_id from LTSTATION "  ;
		if(!empty($parent_id))
 		{
 			$sql.= " where parent_id = ".$parent_id;
 		}
		$sql.= " order by s_id ";
 		$s = mssql_query($sql);
 	 	$info = mssql_fetch_array($s);
		if($info==False)
		{
			return  0;
		}else{
			do{
				$obj[] = $info;
			}while($info=mssql_fetch_array($s));
			return $obj;
		}
 	}

 }
?>
