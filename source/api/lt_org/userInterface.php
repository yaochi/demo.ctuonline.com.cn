

<?php
	 
	class UserInterFace   {
//		* 为第三方能力系统做的接口
	
	   function getUserAndOrgAndStation($regName){
	   
	      include_once('user.php');
		   //用户信息
		  $user=new User();
		  $userDetailtemp=$user->getUserByRegName($regName);
		  if($userDetailtemp){
			  $userDetail['id']=$userDetailtemp['id'];
			  $userDetail['name']=$userDetailtemp['name'];
		  }
		  $ret['0']=$userDetail;
		  //岗位信息
		  $userStationtemp=$user->getUserStationForSystem($regName);
			if($userStationtemp){
			  foreach ($userStationtemp as $key=>$value){		  
				$listvalue['s_id']=$value['s_id'];
				$listvalue['s_name']=$value['s_name'];
				$ret['1'][]=$listvalue;
			  }
		  }else{
		  	$ret['1']=null;
		  }
			
		   //省份信息
		  $provincetemp=$user->getUserprovince($regName);
		  if($provincetemp){
		  	$privoince['p_id']=$provincetemp['id'];
		  	$privoince['p_name']=$provincetemp['name'];
		  }
		  $ret['2']=$privoince;
		   unset($user);
		   return $ret;
	   }
	
	}
	
?>