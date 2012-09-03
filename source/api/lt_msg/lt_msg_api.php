<?php

define('LT_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//配置文件
require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');

//社区提供的通知屏蔽盒通知提醒api
require_once(dirname(dirname(dirname(__FILE__))).'/function/function_spacecp.php');


//mysql数据操作文件
//require_once(dirname(dirname(__FILE__)).'/common/db/mysql_class_lt.php');

//mssqlserver数据库操作文件
require_once(dirname(dirname(__FILE__)).'/common/db/mssql_class_lt.php');


require_once(dirname(dirname(__FILE__)).'/common/db_util.php');

define('RT_SUCCESS',0);	//操作成功 
define('RT_FAIL',1); 	//操作失败

set_time_limit(0);//本函数用来配置该页最久执行时间。若配置为 0 则不限定最久时间

class msg_lt {


	var $mysql4lt; //社区mysql数据库类
	
	var $mssql4zh ;//基础平台mssql数据库类

	/**
	 * 构造函数  
	 */
	function msg_lt() {
		//$this->mysql4lt = new mysql4lt();
	}



	/**
	 * 生成"通知"和"系统类型消息"的接口
	 *
	 * 参数值必须为utf-8编码的，不然会出现中文乱码问题
	 *
	 * @param msgType
	 * 	代表所发送信息的类型。
	 *		枚举值：
	 *		"notice"：通知
	 *		"smsg"：系统类型消息
	 *
	 * @param fromUid
	 *  通知或者系统类型消息的发送者的用户id
	 *
	 * @param fromUname  [O]
	 * 	通知或者系统类型消息的发送者的用户姓名
	 *
	 * @param toUid [O]
	 * 	通知接受者的用户id。
	 *	只有msgType为notice时才指定，为smsg时不需要
	 *
	 * @param toUtype [O]
	 * 	接受者用户类型。
	 * 	只有msgType为notice时才指定，为smsg时不需要
	 * 	当向一个群组或者组织里的人员发送通知时，可以指定该值。当该参数值为空时，表示为个人。
	 *
	 * 	枚举值：
	 *  	"personal"：个人
	 * 		"org"：组织  。当为org时，toUid表示组织的id，发送通知时将给组织中（不包括子组织）的每个人发送一条。
	 * 		"mtag"：群组
	 *  为空时代表个人
	 *
	 * @param message
	 * 	msgType为notice时，表示通知的内容
	 * 	msgType为smsg时，记录做了什么事情
	 *  注：最大字符1000个，多余的将截取
	 *
	 * @param type
	 * 	当msgType为notice时：
	 * 		表示通知本身的分类。
	 * 		type分类枚举见后面通知type枚举 。
	 * 	当msgType为smsg时：
	 * 		代表所调用的接口名称
	 *
	 *	@param isOfficial [O]
	 *  当msgType为notice时有意义
	 *  	表示通知是否是官方通知
	 *  	传值为null时默认为"0"
	 *		"1"：官方通知
	 *		"0"：非官方通知
	 *
	 *	@param extra [O]
	 *	当msgType为notice时有意义
	 *		表示通知中的附件信息。json结构字符串
	 *		例："{'logo':'/logo.jpg'}"
	 *		目前只用到 logo 参数值，表示通知前的头像，如果不设置使用X里原有的机制
	 *
	 *
	 * @param fromAppid
	 * 	当msgType为notice时：
	 * 		代表通知来自那个应用的应用id。
	 * 	当msgType为smsg时：
	 * 		代表发起动作的应用id
	 *
	 * @param toAppid [O]
	 * 	当msgType为smsg时才需要。
	 * 		表示接受动作的应用id
	 *
	 * @return int
	 * 	0：成功
	 *  1：失败
	 *
	 */
	function  sendMsg( $msgType , $fromUid , $fromUname, $toUid  ,$toUtype ,$message ,$type , $isOfficial ,$extra , $fromAppid ,$toAppid,$from_id=0){

		//global $db;
		global $tname_lt;
		global $dateline_type;
		//global $is_encode;
		
		global $db_mysql;
		
		//$original_message = $message; //未编码之前的

		$val = 1;

		// 1.验证参数完整性
		if(!isset($msgType)){
			//echo("-- 参数值完整性错误.---- 参数值msgType 为必填,没有传值");
			$val = 0;
		}
		else if(!isset($fromUid)){
			//echo("-- 参数值完整性错误.---- 参数值fromUid 为必填,并且为数字类型。");
			$val = 0;
		}
		/*else if(!isset($fromUname)){
			//echo("-- 参数值完整性错误.---- 参数值fromUname 为必填,没有传值");
			$val = 0;
		}*/
		else if('notice'==$msgType && !isset($toUid) && !is_numeric($toUid)){
			//echo("-- 参数值完整性错误.---- 参数msgType为" . $msgType ."时，参数值toUid 为必填,并且未数字类型");
			$val = 0;
		}
		else if(!isset($message)){
			//echo("-- 参数值完整性错误.---- 参数值message 为必填,没有传值");
			$val = 0;
		}
		else if(!isset($type)){
			//echo("-- 参数值完整性错误.---- 参数值type 为必填,没有传值");
			$val = 0;
		}
		else if(!isset($fromAppid)){
			//echo("-- 参数值完整性错误.---- 参数值fromAppid 为必填,没有传值");
			$val = 0;
		}
		else if('smsg'==$msgType && !isset($toAppid) ){
			//echo("-- 参数值完整性错误.---- 参数msgType为" . $msgType ."时，参数值toAppid 为必填,并且未数字类型");
			$val = 0;
		}
		
		if(!$val){
			return RT_FAIL;
		}
		
		
		
//判断通知屏蔽设置
		if('notice' == $msgType){
			
			if($this->checkNoticeIgnore($type)){
				return RT_FAIL;
			}
		}		

		$ptype = 0; //通知整体分类，默认为0 社区
		//是否是官方通知
		if(isset($isOfficial) && '1'==$isOfficial){
			$ptype = 5 ;// 官方
		}
		
		$extra = isset($extra)&&'null'!=$extra? $extra:"";
		$extra = addslashes($extra); //转义
		
		$message = addslashes($message); //转义

		//初始化社区数据库
		if(empty($this->mysql4lt)){
			$this->mysql4lt = $db_mysql;
		}

		// 2.分别处理通知 or 系统类型消
			
		$dateline = time();//时间戳比java中获得的long型时间少了毫秒，及少了三位数
		
		//echo $dateline_type."-----------------<br>";
		$dateline = $dateline*$dateline_type; 
		switch($msgType){
			case 'notice': //通知
				
				$noteNum = 0; //发送通知个数
				if(empty($toUtype)){
					$toUtype = "personal";//个人
				}	
				
				
				/*if($is_encode['uchome_notification']){
					$message = iconv('gbk','UTF-8',$message); 
				}*/
				
				if("personal"==$toUtype){
					//给个人发送通知

										
					//判断接受者是否屏蔽了发送者该类型的通知
					/*
					if($this->lt_spacecp_is_disable($toUid,$type,$fromUid)){
						return RT_FAIL;
					}
					*/
					
					
					$notice_inserts = "( $toUid,'$type',1,$fromUid,'$fromUname','$message','$fromAppid',$dateline,$ptype,'$extra','$from_id')";
					$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra,from_id) VALUES ".$notice_inserts;
				
					$this->mysql4lt->query($sql_insert);
					
					$noteNum = 1;
					
					
					//通知提醒
					$this->lt_spacecp_notice_inc($toUid);
					
					
				}
				else if("org"==$toUtype){
					//给组织发送通知  不做通知屏蔽判断
					//从基础平台上查询组织中的用户
					//初始化基础平台mssql数据库
					if(empty($this->mssql4zh)){
						$this->mssql4zh = new mssql4lt();
					}
					if(empty($this->mssql4zh->link)){
						return '--';
					}
					$sql_query =  "select DISTINCT  User_ID  from LTUSER_GROUP  where LTUSER_GROUP.Group_ID = '" . $toUid . "' " ;
//$sql_query =  "select top 1  id as User_ID from LTUSER" ;
					
					$query =  $this->mssql4zh->Query($sql_query);
					$notice_inserts = array();
					$batch_num = 10000;//1万条刷新提交一次
					$num = 0;
					while($row = $this->mssql4zh->GetRow($query)){
						//echo "<br>------" .$row['User_ID'];
						$toUid_tmp = $row['User_ID'];
						/*if(!empty($toUid_tmp)){
							$notice_inserts = "( $toUid_tmp,'$type',1,$fromUid,'$fromUname','$message','$fromAppid',$dateline)";
							$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline) VALUES ".$notice_inserts;
							$this->mysql4lt->query($sql_insert);
							$noteNum++;
						}*/
						if(!empty($toUid_tmp)){
							$notice_inserts[$num] = "( $toUid_tmp,'$type',1,$fromUid,'$fromUname','$message','$fromAppid',$dateline,$ptype,'$extra','$from_id')";
							$num++;
							$noteNum++;
						}
						
						if($num == $batch_num){
							//数据库操作
							$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra,from_id) VALUES ".implode(',',$notice_inserts);
							$this->mysql4lt->query($sql_insert);
							//重新开始
							$notice_inserts = array();
							$num = 0;
							
						}
						
						
						//通知提醒
						$this->lt_spacecp_notice_inc($toUid_tmp);
						
					}
					//处理未提交的
					if($num >0){
						$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra,from_id) VALUES ".implode(',',$notice_inserts);
						$this->mysql4lt->query($sql_insert);
					}
					
					
					if(!empty($this->mssql4zh)){
						$this->mssql4zh->Close();
					}

				}
	
				//保存通知日志
				/*$lt_notice_log_message = $original_message;
				if($is_encode['lt_notice_log']){
					$lt_notice_log_message = iconv('gbk','UTF-8',$original_message); 
				}*/
				$notice_log_inserts = "( $fromUid,'$fromUname',$toUid,'$toUtype',$noteNum,'$message','$type','$fromAppid',$dateline,$ptype,'$extra')";
				//$sql_insert = "INSERT INTO ".$tname_lt['lt_notice_log']." (fromuid,fromuname,touid,toutype,notenum,message,type,fromappid,dateline,ptype,extra) VALUES ".$notice_log_inserts;	
				//$this->mysql4lt->query($sql_insert);	
				//$this->mysql4lt->close();
				
					
				break;
			case 'smsg':	//系统类型的消息
					
				//新增一条系统类型的消息到社区数据库
				/*$lt_system_msg_message = $original_message;
				if($is_encode['lt_system_msg']){
					$lt_system_msg_message = iconv('gbk','UTF-8',$original_message); 
				}*/
				$smsg_inserts = "( $fromUid,'$fromUname','$type','$message','$fromAppid','$toAppid',$dateline)";
				//$sql_insert = "INSERT INTO ".$tname_lt['lt_system_msg']." (uid,uname,comm,note,fromappid,toappid,dateline) VALUES ".$smsg_inserts;
					
				//$this->mysql4lt->query($sql_insert);
					
				//$this->mysql4lt->close();
				
				
				break;
		}



		return RT_SUCCESS;
	}
	
	
	
	//-- 通知提醒  提醒数+1
	function lt_spacecp_notice_inc($uid){
		//调用社区提供的接口
		return spacecp_notice_inc($uid);
	}
	
	/**
	 * 判断用户是否屏蔽了消息
	 * return true 屏蔽了消息
	 * return false 没有屏蔽消息
	 */
	function lt_spacecp_is_disable($uid, $type, $authorid){
		//调用社区提供的接口
	   	return spacecp_is_disable($uid, $type, $authorid);
	}
	
	/**
	 * 判断通知是否设置为屏蔽 true 屏蔽  false 未屏蔽
	 * @param $type
	 */
	function checkNoticeIgnore($type){
		global $tname_lt;
		global $db_mysql;
		
		$rs = false;
		//初始化社区数据库
		if(empty($this->mysql4lt)){
			$this->mysql4lt = $db_mysql;
		}
		
		$sql_query =  "select status  from lt_notice_ignore where type = '" . $type . "' " ;
		
		$query =  $this->mysql4lt->query($sql_query);
		
		if($row = $this->mysql4lt->fetch_array($query)){
			$status = $row['status'];
			$rs = ($status == 1);
		}
	   	return $rs;
		
	}


}


?>