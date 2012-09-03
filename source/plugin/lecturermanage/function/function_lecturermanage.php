<?php
	if(!defined('IN_DISCUZ'))
	{
		exit('Access Denied');
	}
	define('DISCUZ_CORE_FUNCTION', true);

	function openFileAPI($url) {
			$opts = array (
					'http' => array (
					'method' => 'GET',
					'timeout' => 300000,
					)
				);
			$context = @stream_context_create($opts);

			$result =  file_get_contents($url, false, $context);
			return $result;
	}

	function getInfo($temp,$type=1)//调用基础管理平台接口，获取信息
	{
		global $_G;
		$FILE_SEARCH_PAGE = "http://".$_G[config][expert][activeurl];
		if($type==1)//省公司信息
		{
			$regname=$temp;
			$FILE_SEARCH_PAGE.="/api/user/getuserprovinceorg.do?regname=".$regname;
		}
		else if($type==2)//组织完整路径
		{
			$groupid=$temp;
			$FILE_SEARCH_PAGE.="/api/org/listfullorg.do?groupid=".$groupid;
		}
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		$filejson=json_decode($str1, true);
		return $filejson;

	}

	function getAllOrgName($groupid)//获取组织完整路径
	{
		$arr=getInfo($groupid,2);
		$len=count($arr);
		if($len<=0) return '';
		$str=$arr[0][groupname];
		for($i=1;$i<$len;$i++)
		{
			$str.="-".$arr[$i][groupname];
		}
		return $str;
	}

	function getOrg($regname) {//获取省信息
	global $_G;
	$cache_key = "user_isprovince_group_".$regname;//用户所在部门信息中的省信息
	$cache = memory("get", $cache_key);

	if(!empty($cache))
    {
		return unserialize($cache);
	}

	$FILE_SEARCH_PAGE = "http://".$_G[config][expert][activeurl]."/api/user/getuserprovinceorg.do?regname=".$regname;
	$str1 = openFileAPI($FILE_SEARCH_PAGE);
	$filejson=json_decode($str1, true);
	if(!empty($filejson))
    {
		memory("set", $cache_key, serialize($filejson), 24*60*60);
	}
	return $filejson;
	}

	//获取省信息，集团的返回的是一个数组，键is表示是否属于省，键org表示省机构的信息
	function getProvinceOrg($regname)
	{
		$org=getOrg($regname);
		if(!$org)
        {
            $group[is]=false;
        }
		else if($org[groupid]==9002){$group[is]=false;}
		else{
			$group[is]=true;
			$group[org]=$org;
		}

		//$group=array("is"=>false);
		//$group=array("is"=>true,"org"=>array("groupid"=>34543,"groupname"=>"中国电信股份有限公司广东分公司"));
		return $group;
	}

	//获取省的一级等级
	function getProvinceLevelF($province){
		$parentid=searchProvinceLevel($province);
		$sql="SELECT p.id,p.levelname,p.parent_id FROM pre_province_level p,pre_province_level l where  l.parent_id=0  AND p.parent_id=l.id AND p.province_id=".$province[groupid];
		$info = DB::query($sql);
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$obj[] = $value;
			}
			return $obj;
		}
	}

	//查找省等级，不存在则创建，返回省级父节点ID
	function searchProvinceLevel($province)
	{
		global $_G;

		$sql="SELECT * FROM pre_province_level where province_id=".$province[groupid]." AND parent_id=0";
		$info = DB::query($sql);
		$value = DB::fetch($info);
		if(!$value)
		{
			$level0[fid]=$level[fid]=$_G[fid];
			$level0[update_user]=$level[update_user]=$_G[uid];
    		$level0[levelname]=$province[groupname];
			$level0[province_id]=$level[province_id]=$province[groupid];
			$level0[parent_id]=0;
			$level0[isprovince]=$level[isprovince]=1;//是省等级

    		$parentid=DB::insert('province_level', $level0);

    		$level[levelname]="省级内训师";
    		$level[parent_id]=$parentid;
    		DB::insert('province_level', $level);
    		DB::query("UPDATE pre_province_level set child_num=child_num+1 where id=".$parentid);
    		return $parentid;

		}
		else
		{
			return $value[id];
		}
	}

	//获取省级的路径和各点同级的所有级别
	function getleclevels($levelid){
		$arr=array();
		$info = DB::query("SELECT p.id as pid,l.id as lid,l.parent_id as tid FROM pre_province_level p,pre_province_level l where  l.id=p.parent_id  AND p.id=".$levelid);
		$value = DB::fetch($info);
		if($value[tid]==0)
			$arr[level][one]=$value[pid];
		else
		{
			$info = DB::query("SELECT p.id as pid,l.id as lid,l.parent_id as tid FROM pre_province_level p,pre_province_level l where  l.id=p.parent_id  AND p.id=".$value[tid]);
			$val = DB::fetch($info);
			if(!$val)
			{
				$arr[level][one]=$value[lid];
				$arr[level][two]=$value[pid];
			}
			else
			{
				$arr[level][one]=$value[tid];
				$arr[level][two]=$value[lid];
				$arr[level][three]=$value[pid];
			}
		}
		if($arr[level][one])
			$arr[levels1]=getSameLevels($arr[level][one]);
		if($arr[level][two])
			$arr[levels2]=getSameLevels($arr[level][two]);
		if($arr[level][three])
			$arr[levels3]=getSameLevels($arr[level][three]);

		return $arr;
	}

	//获取处于同一级别的所有级别
	function getSameLevels($levelid)
	{
		$info = DB::query("SELECT l.*  FROM pre_province_level p,pre_province_level l where  l.parent_id=p.parent_id  AND p.id=".$levelid);
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$obj[] = $value;
			}
			return $obj;
		}
	}

	function getSubLevel($parentid)
	{
		$sql="SELECT id,levelname,parent_id,child_num FROM pre_province_level where parent_id=".$parentid;
		$info = DB::query($sql);
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$obj[] = $value;
			}
			return $obj;
		}
	}

	//删除等级
	function delLevel($levelid)
	{
		$info = DB::query("SELECT num,parent_id FROM pre_province_level where id=".$levelid);
		$value = DB::fetch($info);
		if(!$value)
			return 0;
		if($value[num]==0)
		{
			$arr=getSubLevel($levelid);
			if($arr)
			{
				$info = DB::query("SELECT parent_id FROM pre_province_level where id=".$value[parent_id]);
				$val = DB::fetch($info);
				if($val[parent_id]!=0)
					DB::query("DELETE FROM pre_province_level where parent_id=".$levelid);
				else
				{
					$len=count($arr);
					for($i=0;$i<$len;$i++)
					{
						DB::query("DELETE FROM pre_province_level where parent_id=".$arr[$i][id]);
						DB::query("DELETE FROM pre_province_level where id=".$arr[$i][id]);
					}
				}
			}

			DB::query("DELETE FROM pre_province_level where id=".$levelid);
			DB::query("UPDATE pre_province_level set child_num=child_num-1 where id=".$value[parent_id]);
			return 1;
		}
		else
		    return 0;
	}

	function updateLevelNum($levelid,$num){

	}

	function addLevel($parentid,$levelname)
	{
		global $_G;
		if(!$levelname)
			return 0;
		$info = DB::query("SELECT count(*) as count,province_id FROM pre_province_level where parent_id=".$parentid." AND levelname='".$levelname."'");
		$value = DB::fetch($info);
		if($value[count]==0)
		{
			$info = DB::query("SELECT * FROM pre_province_level where id=".$parentid);
			$value = DB::fetch($info);
			$level[fid]=$_G[fid];
			$level[update_user]=$_G[uid];
    		$level[levelname]=$levelname;
			$level[province_id]=$value[province_id];
			$level[parent_id]=$parentid;
			$level[isprovince]=1;//是省等级


    		DB::insert('province_level', $level);
    		$id=DB::insert_id();
    		DB::query("UPDATE pre_province_level set child_num=child_num+1 where id=".$parentid);
    		return $id;
		}
		else
		    return 0;
	}

	function changeLevel($id,$levelname)
	{
		global $_G;
		if(!$levelname)
			return 0;
		$info = DB::query("SELECT count(*) as count FROM pre_province_level where id=".$id);
		$value = DB::fetch($info);
		if($value[count]!=0)
		{
    		DB::query("UPDATE pre_province_level set levelname='".$levelname."' where id=".$id);
    		return 1;
		}
		else
		    return 0;
	}

	function getsubs($parentid,$arr)
	{
		$sql="SELECT id,levelname,parent_id FROM pre_province_level where parent_id=".$parentid;
		$info = DB::query($sql);

		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$arr[] = $value[id];
			}
			return $arr;
		}
	}

	//获取集团课程
	function getGroupCourses()
	{
		$sql="select * from ".DB :: table('group_course')." where type=1";
		$info = DB::query($sql);
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$obj[] = $value;
			}
			return $obj;
		}
	}

	//判断外部讲师是否存在
	function lecturerIsIn($name,$org)
	{
		$sql="SELECT * FROM `pre_lecturer` where isinnerlec=2 AND name='".$name."' AND orgname='".$org."'";
		$info = DB::query($sql);
		$value = DB::fetch($info);
		if(!$value)
			return  0;
		else
			return 1;
	}

	function changeRankName($rank,$province_rank)
	{
		$str_name="";
		if($rank)
			$str_name.=changeGroupRankname($rank);
		if($rank&&$province_rank)
			$str_name.=",";
		if($province_rank)
			$str_name.=changeProvinceRankname($province_rank);
		return $str_name;
	}

	//外部讲师
	function getOutterRankname($rank)
	{
			if($rank==1)
				$str="荣誉教授";
			else if($rank==2)
				$str="客座教授";
			else if($rank==3)
				$str="专家教授";
			else
				$str="讲师";
		return $str;
	}

	//内部讲师
	function changeGroupRankname($rank)
	{
		$str_name="";
		$levels = explode(",",$rank);
		if(count($levels)==1)
		{
			if($levels[0]==1)
				$str_name.="集团级内训师";
			else if($levels[0]==2)
				$str_name.="集团课程认证讲师";
			else if($levels[0]==3)
				$str_name.="集团课程授权讲师";
			else if($levels[0]==4)
				$str_name.="网络内训师";
			else if($levels[0]==5)
				$str_name.="其他";
		}
		else if(count($levels)==2)
		{
			if($levels[0]==1)
				$str_name.="集团级内训师";
			else if($levels[0]==2)
				$str_name.="集团课程认证讲师";
			else if($levels[0]==3)
				$str_name.="集团课程授权讲师";
			if($levels[1]==4)
				$str_name.=",网络内训师";
			else if($levels[1]==5)
				$str_name.=",其他";
		}
		return $str_name;
	}

	function changeProvinceRankname($rank)
	{
		$str_name="";
		if($rank)
		{
			$sql="SELECT p.levelname as levelname,l.levelname as province FROM `pre_province_level` p, `pre_province_level` l where p.id=".$rank." AND l.province_id=p.province_id AND l.parent_id=0;";
			$info = DB::query($sql);
			$value = DB::fetch($info);
			$str_name.=$value[levelname]."(".$value[province].")";
		}
		return $str_name;
	}

	function provinceRankname($rank)
	{
		$str_name=array();
		if($rank)
		{
			$sql="SELECT p.levelname as levelname,l.levelname as province FROM `pre_province_level` p, `pre_province_level` l where p.id=".$rank." AND l.province_id=p.province_id AND l.parent_id=0;";
			$info = DB::query($sql);
			$value = DB::fetch($info);
			$str_name=$value;
		}
		return $str_name;
	}

	function getProvinceRank($rank)
	{
		$arr_rank=array();
		if($rank)
		{
			$sql="SELECT p.id,p.levelname as levelname,l.levelname as province FROM `pre_province_level` p, `pre_province_level` l where p.id=".$rank." AND l.province_id=p.province_id AND l.parent_id=0;";
			$info = DB::query($sql);
			$arr_rank = DB::fetch($info);
		}
		return $arr_rank;
	}

	//内部讲师,集团
	function getGroupRank($rank)
	{
		$arr_rank=$arr_rank_one=$arr_rank_two=array();
		$levels = explode(",",$rank);
		if(count($levels)==1)
		{
			if($levels[0]==1)
				$name="集团级内训师";
			else if($levels[0]==2)
				$name="集团课程认证讲师";
			else if($levels[0]==3)
				$name="集团课程授权讲师";
			else if($levels[0]==4)
				$name="网络内训师";
			else if($levels[0]==5)
				$name="其他";
			if($levels[0]<=3)
			{
				$arr_rank_one[id]=$levels[0];
				$arr_rank_one[name]=$name;
			}
			else
			{
				$arr_rank_two[id]=$levels[0];
				$arr_rank_two[name]=$name;
			}
		}
		else if(count($levels)==2)
		{
			if($levels[0]==1)
				$name="集团级内训师";
			else if($levels[0]==2)
				$name="集团课程认证讲师";
			else if($levels[0]==3)
				$name="集团课程授权讲师";

			$arr_rank_one[id]=$levels[0];
			$arr_rank_one[name]=$name;

			if($levels[1]==4)
				$name=",网络内训师";
			else if($levels[1]==5)
				$name=",其他";

			$arr_rank_two[id]=$levels[1];
			$arr_rank_two[name]=$name;
		}
		$arr_rank[one]=$arr_rank_one;
		$arr_rank[two]=$arr_rank_two;
		return $arr_rank;
	}

	//内部讲师
	function getRank($rank,$province_rank,$isinnerlec)
	{
		if($isinnerlec==1)
		{
			$arr_rank=array();
			$levels = explode(",",$rank);
			if(count($levels)==1)
			{
				if($levels[0]<=3)
					$one=$levels[0];
				else
					$two=$levels[0];
			}
			else if(count($levels)==2)
			{
				$one=$levels[0];
				$two=$levels[1];
			}
			$arr_rank[one]=$one;
			$arr_rank[two]=$two;
			$arr_rank[gname]=changeGroupRankname($rank);

			if($province_rank)
			{
				$arr_rank[three]=$province_rank;
				$arr=provinceRankname($province_rank);
				$arr_rank[pname]=$arr[levelname];
				$arr_rank[province]=$arr[province];
			}
		}
		else
		{
			$arr_rank[one]=$rank;
			$arr_rank[oname]=getOutterRankname($rank);
		}
		return $arr_rank;
	}

	function getRole_SZ()
	{
		global $_G;
		$levels=array();
		if($_G['forum']['ismoderator'])
		{
			if($_G[fid]==197)
			{
    			$rolearr=getProvinceOrg($_G[username]);

    			if(!$rolearr[is])
    				$type=1;
    			else
    			{
    				$type=2;
    				$levels=getProvinceLevelF($rolearr[org]);
    				$sql="SELECT id,levelname,parent_id FROM pre_province_level where province_id=".$rolearr[org][groupid]." AND parent_id=0";
					$info = DB::query($sql);
    				$value = DB::fetch($info);

    			}

    		}
		}
    	else
    		$type=3;
    	$arr_role=array("type"=>$type,"levels"=>$levels,"plevel"=>$value[id]);
    	return $arr_role;
	}

	function array_multi_unique($ar)
	{
   		$ar = array_map('serialize', $ar);
  		$ar = array_unique($ar);
  		return array_map('unserialize', $ar);
	}

	//培训课程操作
	function op_course($op,$arr,$type,$lecid)
	{
		if(!$arr[coursename]) return;
		if($op=='insert')
		{
			$arr[source]=$type;
			if($arr[power]<=3) $arr[isgroup]=isGroupCourse($arr[coursename]);
			else $arr[isgroup]=0;
			$course=getCourse($arr[coursename],$arr[power],$lecid);
			if($arr[power]<=2)
			{
				if($course)
				{
					if($arr[power]<=$course[power])
					{
						$condition=array("id"=>$course[id]);
						DB::update('train_course', $arr, $condition);
					}
					else
					{
						$arr[power]=$course[power];
						DB::insert('train_course', $arr);
					}
				}
				else
				{
					DB::insert('train_course', $arr);
				}
			}
			else
			{
				if($course)
				{
					$condition=array("id"=>$course[id]);
					DB::update('train_course', $arr, $condition);
				}
				else
					DB::insert('train_course', $arr);
			}

		}
		else if($op=='update')
		{
			$arr[source]=$type;
			if($arr[power]<=3) $arr[isgroup]=isGroupCourse($arr[coursename]);
			else $arr[isgroup]=0;
            $course=getCourse($arr[coursename],$arr[power],$lecid);
            if($type==1)
            {
                if($course[id]==$arr[id]||!$course)
                {
                    $condition=array("id"=>$arr[id]);
                    DB::update('train_course', $arr, $condition);
                }
                else
                    DB::query("delete FROM `pre_train_course` where id=".$arr[id]);
                }
            else
            {
                if($arr[power]<=$course[power])
                    DB::query("UPDATE `pre_train_course` SET power=".$arr[power]." where coursename='".$arr[coursename]."' AND lecid=".$arr[lecid]);
            }

		}
		else if($op=='delete')
		{
            if($type==2)
            {
		        $info = DB::query("SELECT * FROM `pre_train_course` where coursename='".$arr[coursename]."' AND lecid=".$arr[lecid]);
		        $value = DB::fetch($info);
                if($value[power]==1)
                    DB::query("UPDATE `pre_train_course` SET power=2 where coursename='".$arr[coursename]."' AND lecid=".$arr[lecid]);
                else
                    DB::query("delete FROM `pre_train_course` where coursename='".$arr[coursename]."' AND lecid=".$arr[lecid]);
            }
            else
            {
                if($arr)
				    DB::query("delete FROM `pre_train_course` where id in(".$arr.");");
            }
		}
	}

	function getCourse($name,$power,$lecid)
	{
		if($power<=2)
		{
			$sql="SELECT * FROM `pre_train_course` where coursename='".$name."' AND lecid=".$lecid." AND power<=2;";
		}
		else
		{
			$sql="SELECT * FROM `pre_train_course` where coursename='".$name."' AND lecid=".$lecid." AND power=".$power;
		}
		$info = DB::query($sql);
		$value = DB::fetch($info);
		return $value;
	}

	function isGroupCourse($name)
	{//1-集团课程，2-伪集团课程
		$info = DB::query("select * from ".DB :: table('group_course')." where type=1 AND coursename='".$name."'");
		$value = DB::fetch($info);
		if(!$value)
		{
			$arr[coursename]=$name;
			$arr[type]=2;
			DB::insert('group_course', $arr);
			return 2;
		}
		return $value[type];
	}

	//获取讲师的培训课程
	function getTrainCourseByLecid($lecid)
	{
		$arr_train=array();
		$sql="SELECT * FROM `pre_train_course` where lecid=".$lecid." AND power<=3 order by power asc";
		$info = DB::query($sql);

		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$arr[] = $value;
			}
			$arr_train[group]=$arr;
		}

		$sql="SELECT * FROM `pre_train_course` where lecid=".$lecid." AND power=4 order by power asc";
		$info = DB::query($sql);

		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$arr1[] = $value;
			}
			$arr_train[province]=$arr1;
		}

		$sql="SELECT * FROM `pre_train_course` where lecid=".$lecid." AND power=5 order by power asc";
		$info = DB::query($sql);

		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
				$arr2[] = $value;
			}
			$arr_train[other]=$arr2;
		}
		return $arr_train;
	}

	//获取年授课记录
	function getlecturerecords($lecid,$year)
	{
		$sql="SELECT * FROM `pre_lecturerecord_credit` where lecid=".$lecid." AND year=".$year.";";
		$info = DB::query($sql);
		$value = DB::fetch($info);
		if($value)
		{
			return  $value[num];
		}
		else
		{
			return 0;
		}


	}

	//降级，内部讲师
	function degrade($level){
		$level=getGroupRank($level);
		$level1=$level[one][id];
		$level2=$level[two][id];
		if($level1==1){}
		else if($level1==2)
		{
			DB::query("UPDATE `pre_train_course` SET power=5 where power=0;");
		}
		else if($level1==3)
		{
			DB::query("UPDATE `pre_train_course` SET power=5 where power=0;");
			DB::query("UPDATE `pre_train_course` SET power=2 where power=1;");
		}
		if(!$level2||$level2!=4)
		{
			DB::query("UPDATE `pre_train_course` SET power=5 where power=3;");
		}
		if((!$level1&&$level2==4)||($level2==5))
		{
			DB::query("UPDATE `pre_train_course` SET power=5 where power<=2;");
		}
	}

	//获取讲师ID
	 function getLecturer_xf($regname,$type=1)
    {
        $query = DB::query("SELECT * FROM `pre_lecturer` where tempusername='".$regname."';");
       	$info = DB::fetch($query);
        if(!$info[id]) return 0;
        if($info[tel]!=''&&$info[email]!=''&&$type) return -1;
        return $info[id];
    }

    function getGender($regname)
    {
    	require_once dirname(dirname(dirname(dirname(__FILE__))))."/api/lt_org/memcacheUser.php";
		$usermgr = new memcacheUser();
		$user = $usermgr->getUser($regname);
		return $user[sex];
    }

	function  changeToInternal($uid){//讲师级别升级，升为集团级内训师
	}

	//讲师降级
	function downgrade($uid)
	{

	}

	//创建省级别
	function addProvinceLevel($parent_id)
	{

	}
	//编辑省级别
	function editProvinceLevel($parent_id)
	{

	}

	//删除省级别
	function deleteProvinceLevel($org_id)
	{

	}

	//增加培训课程
	function addTrainCourse($uid,$type)
	{

	}

	//编辑培训课程
	function editTrainCourse($uid,$type)
	{

	}

	//删除培训课程
	function deleteTrainCourse($uid,$type,$tcid)
	{

	}

?>





