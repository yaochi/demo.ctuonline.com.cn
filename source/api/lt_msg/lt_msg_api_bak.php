<?php

define('LT_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//�����ļ�
require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');

//mysql���ݲ����ļ�
include_once(LT_ROOT.'./db/mysql_class_lt.php');

//mssqlserver���ݿ�����ļ�
include_once(LT_ROOT.'./db/mssql_class_lt.php');

define('RT_SUCCESS',0);	//�����ɹ� 
define('RT_FAIL',1); 	//����ʧ��

set_time_limit(0);//�������������ø�ҳ���ִ��ʱ�䡣������Ϊ 0 ���޶����ʱ��

class msg_lt {


	var $mysql4lt; //����mysql���ݿ���
	
	var $mssql4zh ;//����ƽ̨mssql���ݿ���

	/**
	 * ���캯��  
	 */
	function msg_lt() {
		//$this->mysql4lt = new mysql4lt();
		
	}



	/**
	 * ����"֪ͨ"��"ϵͳ������Ϣ"�Ľӿ�
	 *
	 * ����ֵ����Ϊutf-8����ģ���Ȼ�����������������
	 *
	 * @param msgType
	 * 	������������Ϣ�����͡�
	 *		ö��ֵ��
	 *		"notice"��֪ͨ
	 *		"smsg"��ϵͳ������Ϣ
	 *
	 * @param fromUid
	 *  ֪ͨ����ϵͳ������Ϣ�ķ����ߵ��û�id
	 *
	 * @param fromUname
	 * 	֪ͨ����ϵͳ������Ϣ�ķ����ߵ��û�����
	 *
	 * @param toUid [O]
	 * 	֪ͨ�����ߵ��û�id��
	 *	ֻ��msgTypeΪnoticeʱ��ָ����Ϊsmsgʱ����Ҫ
	 *
	 * @param toUtype [O]
	 * 	�������û����͡�
	 * 	ֻ��msgTypeΪnoticeʱ��ָ����Ϊsmsgʱ����Ҫ
	 * 	����һ��Ⱥ�������֯�����Ա����֪ͨʱ������ָ����ֵ�����ò���ֵΪ��ʱ����ʾΪ���ˡ�
	 *
	 * 	ö��ֵ��
	 *  	"personal"������
	 * 		"org"����֯  ����Ϊorgʱ��toUid��ʾ��֯��id������֪ͨʱ������֯�У�����������֯����ÿ���˷���һ����
	 * 		"mtag"��Ⱥ��
	 *  Ϊ��ʱ��������
	 *
	 * @param message
	 * 	msgTypeΪnoticeʱ����ʾ֪ͨ������
	 * 	msgTypeΪsmsgʱ����¼����ʲô����
	 *  ע������ַ�1000��������Ľ���ȡ
	 *
	 * @param type
	 * 	��msgTypeΪnoticeʱ��
	 * 		��ʾ֪ͨ�����ķ��ࡣ
	 * 		type����ö�ټ�����֪ͨtypeö�� ��
	 * 	��msgTypeΪsmsgʱ��
	 * 		���������õĽӿ�����
	 *
	 *	@param isOfficial [O]
	 *  ��msgTypeΪnoticeʱ������
	 *  	��ʾ֪ͨ�Ƿ��ǹٷ�֪ͨ
	 *  	��ֵΪnullʱĬ��Ϊ"0"
	 *		"1"���ٷ�֪ͨ
	 *		"0"���ǹٷ�֪ͨ
	 *
	 *	@param extra [O]
	 *	��msgTypeΪnoticeʱ������
	 *		��ʾ֪ͨ�еĸ�����Ϣ��json�ṹ�ַ���
	 *		����"{'logo':'/logo.jpg'}"
	 *		Ŀǰֻ�õ� logo ����ֵ����ʾ֪ͨǰ��ͷ�����������ʹ��X��ԭ�еĻ���
	 *
	 *
	 * @param fromAppid
	 * 	��msgTypeΪnoticeʱ��
	 * 		����֪ͨ�����Ǹ�Ӧ�õ�Ӧ��id��
	 * 	��msgTypeΪsmsgʱ��
	 * 		������������Ӧ��id
	 *
	 * @param toAppid [O]
	 * 	��msgTypeΪsmsgʱ����Ҫ��
	 * 		��ʾ���ܶ�����Ӧ��id
	 *
	 * @return int
	 * 	0���ɹ�
	 *  1��ʧ��
	 *
	 */
	function  sendMsg( $msgType , $fromUid , $fromUname, $toUid  ,$toUtype ,$message ,$type , $isOfficial ,$extra , $fromAppid ,$toAppid){

		//global $db;
		global $tname_lt;
		global $dateline_type;
		//global $is_encode;
		
		//$original_message = $message; //δ����֮ǰ��

		$val = 1;

		// 1.��֤����������
		if(!isset($msgType)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵmsgType Ϊ����,û�д�ֵ");
			$val = 0;
		}
		else if(!isset($fromUid)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵfromUid Ϊ����,����Ϊ�������͡�");
			$val = 0;
		}
		else if(!isset($fromUname)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵfromUname Ϊ����,û�д�ֵ");
			$val = 0;
		}
		else if('notice'==$msgType && !isset($toUid) && !is_numeric($toUid)){
			//echo("-- ����ֵ�����Դ���.---- ����msgTypeΪ" . $msgType ."ʱ������ֵtoUid Ϊ����,����δ��������");
			$val = 0;
		}
		else if(!isset($message)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵmessage Ϊ����,û�д�ֵ");
			$val = 0;
		}
		else if(!isset($type)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵtype Ϊ����,û�д�ֵ");
			$val = 0;
		}
		else if(!isset($fromAppid)){
			//echo("-- ����ֵ�����Դ���.---- ����ֵfromAppid Ϊ����,û�д�ֵ");
			$val = 0;
		}
		else if('smsg'==$msgType && !isset($toAppid) ){
			//echo("-- ����ֵ�����Դ���.---- ����msgTypeΪ" . $msgType ."ʱ������ֵtoAppid Ϊ����,����δ��������");
			$val = 0;
		}
		
		if(!$val){
			return RT_FAIL;
		}
		

		$ptype = 0; //֪ͨ������࣬Ĭ��Ϊ0 ����
		//�Ƿ��ǹٷ�֪ͨ
		if(isset($isOfficial) && '1'==$isOfficial){
			$ptype = 5 ;// �ٷ�
		}
		$extra = isset($extra)?$extra:"";
		$extra = addslashes($extra); //ת��
		
		$message = addslashes($message); //ת��

		//��ʼ���������ݿ�
		if(empty($this->mysql4lt)){
			$this->mysql4lt = new mysql4lt();
		}

		// 2.�ֱ���֪ͨ or ϵͳ������
			
		$dateline = time();//ʱ�����java�л�õ�long��ʱ�����˺��룬��������λ��
		
		//echo $dateline_type."-----------------<br>";
		$dateline = $dateline*$dateline_type; 
		switch($msgType){
			case 'notice': //֪ͨ
				
				$noteNum = 0; //����֪ͨ����
				if(empty($toUtype)){
					$toUtype = "personal";//����
				}	
				
				
				/*if($is_encode['uchome_notification']){
					$message = iconv('gbk','UTF-8',$message); 
				}*/
				
				if("personal"==$toUtype){
					//�����˷���֪ͨ
					$notice_inserts = "( $toUid,'$type',1,$fromUid,'$fromUname','$message','$fromAppid',$dateline,$ptype,'$extra')";
					$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra) VALUES ".$notice_inserts;
				
					$this->mysql4lt->query($sql_insert);
					
					//$this->mysql4lt->close(); //���ܹرգ����滹�����ݿ����
					$noteNum = 1;
				}
				else if("org"==$toUtype){
					//����֯����֪ͨ
					//�ӻ���ƽ̨�ϲ�ѯ��֯�е��û�
					//��ʼ������ƽ̨mssql���ݿ�
					if(empty($this->mssql4zh)){
						$this->mssql4zh = new mssql4lt();
					}
					
					$sql_query =  "select DISTINCT  User_ID  from LTUSER_GROUP  where LTUSER_GROUP.Group_ID = '" . $toUid . "' " ;
//$sql_query =  "select top 1  id as User_ID from LTUSER" ;
					
					$query =  $this->mssql4zh->Query($sql_query);
					$notice_inserts = array();
					$batch_num = 10000;//1����ˢ���ύһ��
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
							$notice_inserts[$num] = "( $toUid_tmp,'$type',1,$fromUid,'$fromUname','$message','$fromAppid',$dateline,$ptype,'$extra')";
							$num++;
							$noteNum++;
						}
						
						if($num == $batch_num){
							//���ݿ����
							$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra) VALUES ".implode(',',$notice_inserts);
							$this->mysql4lt->query($sql_insert);
							//���¿�ʼ
							$notice_inserts = array();
							$num = 0;
							
						}
						
					}
					//����δ�ύ��
					if($num >0){
						$sql_insert  = "INSERT INTO ".$tname_lt['uchome_notification']." (uid,type,new,authorid,author,note,fromappid,dateline,ptype,extra) VALUES ".implode(',',$notice_inserts);
						$this->mysql4lt->query($sql_insert);
					}
					
					
					if(!empty($this->mssql4zh)){
						$this->mssql4zh->Close();
					}

				}
	
				//����֪ͨ��־
				/*$lt_notice_log_message = $original_message;
				if($is_encode['lt_notice_log']){
					$lt_notice_log_message = iconv('gbk','UTF-8',$original_message); 
				}*/
				$notice_log_inserts = "( $fromUid,'$fromUname',$toUid,'$toUtype',$noteNum,'$message','$type','$fromAppid',$dateline,$ptype,'$extra')";
				$sql_insert = "INSERT INTO ".$tname_lt['lt_notice_log']." (fromuid,fromuname,touid,toutype,notenum,message,type,fromappid,dateline,ptype,extra) VALUES ".$notice_log_inserts;	
				$this->mysql4lt->query($sql_insert);	
				$this->mysql4lt->close();
				
					
				break;
			case 'smsg':	//ϵͳ���͵���Ϣ
					
				//����һ��ϵͳ���͵���Ϣ���������ݿ�
				/*$lt_system_msg_message = $original_message;
				if($is_encode['lt_system_msg']){
					$lt_system_msg_message = iconv('gbk','UTF-8',$original_message); 
				}*/
				$smsg_inserts = "( $fromUid,'$fromUname','$type','$message','$fromAppid','$toAppid',$dateline)";
				$sql_insert = "INSERT INTO ".$tname_lt['lt_system_msg']." (uid,uname,comm,note,fromappid,toappid,dateline) VALUES ".$smsg_inserts;
					
				$this->mysql4lt->query($sql_insert);
					
				$this->mysql4lt->close();
				
				
				break;
		}



		return RT_SUCCESS;
	}

}


?>