<?php 
/*
 * @author fumz
 * @since 2010-9-26 15:30:26
 * @三级管理权限判断
 */
global $_G;

include_once DISCUZ_ROOT.'./source/api/lt_org/role.php';
include_once DISCUZ_ROOT.'./source/api/lt_org/group.php';
include_once DISCUZ_ROOT.'./source/api/lt_org/user.php';
include_once DISCUZ_ROOT.'./source/function/function_org.php';
/*
 * 三级管理权限数据保存结构
 * $_G['validate']['checked']是否验证过用户的三级管理权限
 * $_G['validate']['ismanager']是否是三级管理员
 * $_G['validate']['org_id']用户所管理的最顶层组织机构id
 * $_G['validate']['istop']用户管理机构id是否是集团
 * $_G['validate']['orgidarray']管理的组织机构id数组
 * 
 */

/*if(empty($_G['validate'])){
	$_G['validate']=array();
}*/
/*if($_G['cookie']['validate']['checked']){//验证过用户是否是三级管理员
	echo "重新进入管理后台";
	if(!$_G['cookie']['validate']['ismanager']){//不是三级管理员
		showmessage('对不起，你不是管理员', 'portal.php');
	}else{
		$_G['validate']=$_G['cookie']['validate'];		
	}	
}else{//未验证过用户是否是三级管理员
	echo "首次进入管理后台";
	$validate=array();
	$validate['checked']=true;
	$trole=new TRole();
	$validate['ismanager']=$trole->checkUserIsManager($_G[uid]);//$_G['validate']['ismanager']=$trole->checkUserIsManager($_G[uid]);
	unset($trole);
	if($validate['ismanager']){//是否是三级管理员		
		$user=new User();
		$org_id=$user->getGroupidByUserid($_G['uid']);
		unset($user);
		
		if($org_id){		
			if($org_id!='9001'&&$org_id!='9001'&&$org_id!=9001&&$org_id!=9002){//如果不是集团级
				$validate['istop']=false;
				$group=new Group();
				$subgrouparray=$group->getGroupByTopId($org_id);
				foreach($subgrouparray as $key=>$value){
					//$_G['validate']['orgidarray'][$key]=$value[0];
					$validate['orgidarray'][$key]=$value[0];
				}
				if(empty($validate['orgidarray'])){
					//$_G['validate']['orgidarray']=array($org_id);
					$validate['orgidarray']=array($org_id);
				}
			}else{
				//$_G['validate']['istop']=true;
				//$_G['validate']['orgidarray']=array($org_id);
				$validate['istop']=true;
				$validate['orgidarray']=array($org_id);
			}
		}
		dsetcookie('validate',$validate);	
		$G['validate']=$validate;
	}else{
		showmessage('对不起，你不是管理员', 'portal.php');
	}
	
}*/
if(empty($_G['validate'])){
	$_G['validate']=array();
}

if($_G['uid'] && ($_G['adminid'] == 1 || $_G['member']['allowadmincp'])){
	$_G['validate']['istop']=true;
	dsetcookie('validate_istop',true);
}else{
	if($_G['cookie']['validate_checked']){//验证过用户是否是三级管理员
		if(!$_G['cookie']['validate_ismanager']){//不是三级管理员
			showmessage('对不起，你不是管理员',$_SERVER['HTTP_REFERER']);
		}else{
			/*
			 * 从cookie中取出用户所管理的公司id等信息
			 */	
			$_G['validate']['checked']=$_G['cookie']['validate_checked'];
			$_G['validate']['ismanager']=$_G['cookie']['validate_ismanager'];
			$_G['validate']['org_id']=$_G['cookie']['orgidarray000000'];
			$_G['validate']['istop']=$_G['cookie']['validate_istop'];
			$_G['validate']['orgidarray']=array();
			foreach($_G['cookie'] as $key=>$value){
				if(strpos("ab".$key,'orgidarray')){
					$_G['validate']['orgidarray'][$key]=$value;
				}
			}
		}	
	}else{//未验证过用户是否是三级管理员
		dsetcookie('validate_checked',true);
		$_G['validate']['checked']=true;
		
		$trole=new TRole();
		$ismanager=$trole->checkUserIsManager($_G[uid]);
		dsetcookie('validate_ismanager',$ismanager);
		$_G['validate']['ismanager']=$ismanager;
		unset($trole);
		
		if($ismanager){//是否是三级管理员		
			$user=new User();
			$org_id=$user->getGroupidByUserid($_G['uid']);
			unset($user);
			
			if($org_id){		
				dsetcookie('orgidarray000000',$org_id);//added by fumz,2010年10月12日13:22:07
				if($org_id!='9001'&&$org_id!='9002'&&$org_id!=9001&&$org_id!=9002){//如果不是集团级
					dsetcookie('validate_istop',false);
					$_G['validate']['istop']=false;
					$group=new Group();
					$subgrouparray=$group->getGroupByTopId($org_id);
					if(!empty($subgrouparray)){
						$_G['validate']['orgidarray']=array();
						foreach($subgrouparray as $key=>$value){
							$name='orgidarray_'.$key;
							dsetcookie($name,$value[0]);    
							$_G['validate']['orgidarray'][$key]=$value[0];
						}
						dsetcookie('orgidarray',$org_id);
						$_G['validate']['orgidarray'][$org_id]=$org_id;
					}else{
						$_G['validate']['orgidarray']=array($org_id);
						dsetcookie('orgidarray',$org_id);
					}
				}else{
					$_G['validate']['istop']=true;
					$_G['validate']['orgidarray']=array($org_id);
					dsetcookie('validate_istop',true);
					dsetcookie('orgidarray',$org_id);
				}
			}else{
				showmessage('对不起，你不是管理员', $_SERVER['HTTP_REFERER']);
			}
		}else{
			showmessage('对不起，你不是管理员', $_SERVER['HTTP_REFERER']);
		}
		
	}
}
?>