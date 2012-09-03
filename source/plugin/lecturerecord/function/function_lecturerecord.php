<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('DISCUZ_CORE_FUNCTION', true);

/**
 * 计算积分
 * type-授课类型,rule-积分参数数组
 */
 function calculate_credit($type,$rule){
	$value=$per=$many=0;
	if($type==1) {	//授课积分
		$per=intval($rule[degree]>4) ? 75 : 0 ;
		$many=$rule[num]*$rule[coefficient]*$rule[degree]/5;
	}
	else if($type==2) {	//课程开发积分
		$per=500;
		$many=$rule[coefficient]*$rule[degree];
	}
	else if($type==3) {	//课题研究积分
		$per=50;
		$many=$rule[num];
	}
	else {
		$per=50;
		$many=$rule[num];
	}

	$value=round($many*$per);
	return $value;
}

/**
 * 检查记录是否存在,不存在则新增记录
 * 创建失败返回false，否则返回true
 */
function check_exist($lecid,$year){
	if(!$lecid)
		return false;
	$value = DB::fetch(DB::query("SELECT count(*) as count FROM `pre_lecturerecord_credit` where lecid=".$lecid." AND year=".$year));
	$value1 = DB::fetch(DB::query("SELECT count(*) as count FROM `pre_lecturerecord_credit` where lecid=".$lecid." AND year=0"));
	$username='未知';
	if($value[count]<1||$value1[count]<1)	$username=getLecturerName($lecid);
	if($value1[count]<1)
		DB::query("INSERT INTO `pre_lecturerecord_credit` VALUES (".$lecid.",'".$username."',0,0,0);");
	if($value[count]<1)
		DB::query("INSERT INTO `pre_lecturerecord_credit` VALUES (".$lecid.",'".$username."',".$year.",0,0);");
	return true;
}

/**
 * 获取授课记录内容
 */
 function get_lecrecord($id){
	$sql="select * from pre_lecture_record where id=".$id;
 	$value=DB::fetch(DB::query($sql));
	return $value;
 }

/**
 * 基础操作新增积分
 */
function insert_credit($year,$lecid,$credit){
	if(check_exist($lecid,$year)){
		DB::query("update `pre_lecturerecord_credit` set credit=credit+".$credit.",num=num+1 where lecid=".$lecid." AND year=".$year);
		DB::query("update `pre_lecturerecord_credit` set credit=credit+".$credit.",num=num+1 where lecid=".$lecid." AND year=0");
	}
}

/**
 * 基础操作删除积分
 */
function delete_credit($year,$lecid,$credit){
	DB::query("update `pre_lecturerecord_credit` set credit=credit-".$credit.",num=num-1 where lecid=".$lecid." AND year=".$year);
	DB::query("update `pre_lecturerecord_credit` set credit=credit-".$credit.",num=num-1 where lecid=".$lecid." AND year=0");
}

/**
 * 新增授课记录添加积分
 * inf-资源信息；rule-积分规则相关信息
 */
 function add_lecrecord_credit($inf,$rule){
	$inf[type] = intval($inf[type]) ? $inf[type] : 1;
	$inf[year] = intval($inf[year]) ? $inf[year] : date("Y",time());
	if(!$inf[lecid])	return;
	$credit=calculate_credit($inf[type],$rule);
	insert_credit($inf[year],$inf[lecid],$credit);
}

/**
 * 修改授课记录积分变动
 * inf-资源信息；rule-积分规则相关信息
 */
 function up_lecrecord_credit($inf,$rule){
 	del_lecrecord_credit($inf[recordid]);
 	$inf[type] = intval($inf[type]) ? $inf[type] : 1;
	$inf[year] = intval($inf[year]) ? $inf[year] : date("Y",time());
	if(!$inf[lecid])	return;
	$credit=calculate_credit($inf[type],$rule);
	insert_credit($inf[year],$inf[lecid],$credit);
}

/**
 * 删除授课记录减积分
 * inf-资源信息；rule-积分规则相关信息
 */
function del_lecrecord_credit($recordid){
	$lecrecord=get_lecrecord($recordid);
	$inf[type] = $lecrecord[type];
	$inf[year] = date("Y",$lecrecord[starttime]);
	$inf[lecid]= $lecrecord[teacher_id];
	if($inf[type]==1){	//授课积分
		$rule[num]=$lecrecord[class_time];
		$rule[coefficient]=$lecrecord[class_level];
		$rule[degree]=$lecrecord[class_result];
	}
	else if($inf[type]==2) {	//课程开发积分
		$rule[coefficient]=$lecrecord[class_level];
		$rule[degree]=$lecrecord[class_result];
	}
	else if($inf[type]==3) {	//课题研究积分
		$rule[num]=$lecrecord[join_num];
	}
	else {
		$rule[num]=$lecrecord[tu_num];
	}
	$credit=calculate_credit($inf[type],$rule);
	delete_credit($inf[year],$inf[lecid],$credit);
}

/**
 * 获取某一年授课积分排行前几名
 * @param $year
 * @param $num
 */
//SELECT lc.lecid,l.name,l.orgname,lc.credit FROM `pre_lecturerecord_credit` lc,`pre_lecturer` l where lc.year=2011 AND lc.lecid=l.id order by lc.credit desc limit 0,50;
function getTop($year,$num=50)
{
	require_once (dirname(dirname(dirname(dirname(__FILE__))))."/function/function_space.php");
	$sql="SELECT lc.lecid,l.name,l.tempusername,l.orgname,l.orgname_all,lc.credit FROM `pre_lecturerecord_credit` lc,`pre_lecturer` l where lc.year=".$year." AND lc.lecid=l.id order by lc.credit desc limit 0,".$num.";";
	$info = DB::query($sql);
	$i=1;
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {
	        	$value[posit]=$i++;

	        	$province=substr($value[orgname_all],19);
	        	$index=strpos($province,"-");
	        	$province=substr($province,0,$index);

	        	if(!$province&&$value[tempusername])
	        	{
	        		$progroup=getprogroup($value['tempusername'],true);
	        		$province=$progroup[groupname];
	        	}

	        	if($province) $value[orgname]=$province;
				$obj[] = $value;
			}
			return $obj;
		}
}

//获取讲师名称
function getLecturerName($lecid){
	$name="";
	$sql="SELECT name FROM `pre_lecturer` where id=".$lecid.";";
	$info = DB::query($sql);
	$value = DB::fetch($info);
	if(!$value){
		$name="未知";
	}
	else{
	    $name=$value[name];
	}
	return $name;
}

	//获取集团课程
	function getGroupCourse()
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

?>