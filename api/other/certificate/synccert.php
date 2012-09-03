<?php
/* Function: 社区中证书的接口
 * Com.:
 * Author: yangyang
 * Date: 2011-3-22
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
global $_G;
$Certificate_id=$_G['gp_cid'];
$Certificate_no=$_G['gp_cno'];
$TC_name=$_G['gp_tcname'];
$Regname=$_G['gp_regname'];
$scode=$_G['gp_scode'];
$Certificate_type = empty ($_G['gp_ctype']) ? 0 : intval($_G['gp_ctype']);
$User_id = $_G['gp_uid'];
$operatype=$_G['gp_operatype'];

$returnmes=array();
if(!$Certificate_id){
	$returnmes[success]='1';
	$returnmes[mes]='证书id为空';
	echo(json_encode($returnmes));
}elseif(!$Certificate_no){
	$returnmes[success]='1';
	$returnmes[mes]='证书编号为空';
	echo(json_encode($returnmes));
}elseif(!$Certificate_type){
	$returnmes[success]='1';
	$returnmes[mes]='证书类型为空';
	echo(json_encode($returnmes));
}elseif(!$TC_name){
	$returnmes[success]='1';
	$returnmes[mes]='培训班名称为空';
	echo(json_encode($returnmes));
}elseif(!$Regname){
	$returnmes[success]='1';
	$returnmes[mes]='网大账号为空';
	echo(json_encode($returnmes));
}elseif(!$User_id){
	$returnmes[success]='1';
	$returnmes[mes]='用户账号为空';
	echo(json_encode($returnmes));
}else{
	$code=md5($Certificate_id.$Certificate_no.$Certificate_type.$User_id.$Regname.$TC_name.'esn');
	//if($code==$scode){
	$User_id=intval($User_id);
	if(!$User_id){
		$returnmes[success]='1';
		$returnmes[mes]='用户uid类型错误';
		echo(json_encode($returnmes));
	}else{
			if($operatype=='insert'){
				$certicate[Certificate_id]=$Certificate_id;
			}
			$certicate[Certificate_no]=$Certificate_no;
			$certicate[Certificate_type]=$Certificate_type;
			$certicate[TC_name]=$TC_name;
			$certicate[Regname]=$Regname;
			$certicate[User_id]=$User_id;
			$certicate[Create_date]=time();
			if($operatype=='insert'){
				$res=DB::insert('synchro_cert_info',$certicate,true);
				getCertificate($Certificate_type,$TC_name,$User_id,'insert');				
			}elseif($operatype=='update'){
				$condition='Certificate_id='.$Certificate_id;
				$res=DB::update('synchro_cert_info',$certicate,$condition);
                getCertificate($Certificate_type,$TC_name,$User_id,'update');
			}elseif($operatype=='delete'){
				$condition='Certificate_id='.$Certificate_id;
				$res=DB::delete('synchro_cert_info',$condition);
                getCertificate($Certificate_type,$TC_name,$User_id,'delete');
			}
			if($res){
				$returnmes[success]='2';
				echo(json_encode($returnmes));
			}else{
				$returnmes[success]='1';
				$returnmes[mes]='插入数据错误';
				echo(json_encode($returnmes));
			}
		}
	//}else{
	//	$returnmes[success]='1';
	//	$returnmes[mes]='md5加密错误';
	//	echo(json_encode($returnmes));
	//}
}

function getCertificate($type,$coursename,$uid,$op)
{
	if($type==3) 
        return 1;
    $lecid=getLecturerIdByUid($uid);
    if(!$lecid) 
        return 0;
        
    $traincourse[lecid]=$lecid;
   	$traincourse[belong]=1;
    $traincourse[source]=2;
    $traincourse[isgroup]=0;
    $traincourse[update_time]=time();
    $traincourse[coursename]=$coursename;
    require dirname(dirname(dirname(dirname(__FILE__)))).'/source/plugin/lecturermanage/function/function_lecturermanage.php';
	if($type==1)
    //认证证书 
    	$traincourse[power]=1;

    else if($type==2)
    //授权证书
		$traincourse[power]=2;
 
    else if($type==4)
    //网络内训师证
	   $traincourse[power]=3;
       
    op_course($op,$traincourse,2,$lecid);
	return;	
}

function getLecturerIdByUid($uid)
{
	$id=0;
	$sql="SELECT id FROM `pre_lecturer` where lecid=".$uid.";";
	$info = DB::query($sql);
	$value = DB::fetch($info);

	if($value)
	{
		$id=$value[id];
	}
	return $id;
}

?>