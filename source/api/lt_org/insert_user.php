<?php
class Syn_InsertUser
{
 static private $_instance=NULL;
 static public function getInstance(){    
        if(self::$_instance == NULL){    
             self::$_instance = new Syn_InsertUser();    
         }    
        return self::$_instance;    
     }    
        
    private function __construct(){    
     }    
        
    private function __clone(){    
     }        
        
    static function insert_user($user,$userarr,$tname_lt){ 
     global $_G;
     $mssql4zh=new mssql4lt(); 
	 if(empty($mssql4zh->link)){
		return '--';
	}
     $sql_id="select max(id) as max_id from common_user";
			$query=$mssql4zh->Query($sql_id);
               while($row=$mssql4zh->GetRow($query)){
				$id = number_format($row['max_id'],0,'','')+1;
			}
     $regName = "ZJ".$id;//ע���� 
     $user=array('id'=>$id,'login_name'=>$regName);
     $userarr['id'] = $id;
     $userarr['azUserId'] = $id;
     $userarr['regName'] = $regName;
     $userVisit=array('User_id'=>$id,'Able_system'=>'ESNSQ');
     $mssql4zh->Insert($tname_lt['LTUSER_VISIT'],$userVisit);
	 $sql_cu="INSERT INTO COMMON_USER (id,login_name) VALUES (".$user['id'].",'".$user['login_name']."');";
	 $sql_lu="INSERT INTO LTUSER (name,fids,fname,furl,userType,creator,lastModifier,createTime,updateTime,id,azUserId,regName)";
	 $name=$userarr['name'];
	 $fids=$userarr['fids'];
	 $fname=$userarr['fname'];
	 $furl=$userarr['furl'];
	 $userType=$userarr['userType'];
	 $creator=$userarr['creator'];
	 $lastModifier=$userarr['lastModifier'];
	 $createTime=$userarr['createTime'];
	 $updateTime=$userarr['updateTime'];
	 $id=$userarr['id'];
	 $azUserId=$userarr['azUserId'];
	 $regName=$userarr['regName'];
	 $sql_lu.=" VALUES('$name','$fids','$fname','$furl','$userType',$creator,$lastModifier,$createTime,$updateTime,$id,'$azUserId','$regName');";
	 //exit($sql_lu);
	 //try{
	 	$mssql4zh->Query($sql_cu);
     	$mssql4zh->Query($sql_lu); 	 	
/*	 }catch(Exception $e){
	 	showmessage("创建专家用户失败",$furl."&action=manage&op=manageuser");
	 }*/
   
     $usermgr['regName']=$regName;
     $usermgr['furl']="http://".$_G[config][expert][activeurl]."/usermanage/invite.do?regName=".urlencode(base64_encode($regName))."&uid=".urlencode(base64_encode($id));
     $usermgr['uid']=$id;    
     return $usermgr; 
     }    
}    
?>
 































