<?php
/*
 * Com.:
 * Author: qiaoyz
 * Date: 2012-8-1
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/plugin/coursemap/function/function_coursemap.php';
$discuz = & discuz_core::instance();
$discuz->init();
$action=!empty($_G['gp_action']) ? $_G['gp_action'] : 'index';
if($action){
	$action();
}

/**
 *根据用户uid，序号获取课程及信息
 */
function getcourse(){
	global $_G;
	$num=empty($_G['gp_num'])?0:$_G['gp_num'];//推荐课程序号
	$uid=empty($_G['gp_uid'])?$_G[uid]:$_G['gp_uid'];//获取用户uid
	require_once (dirname(__FILE__)."/function/function_coursemap.php");
	$mystation=getustation($uid);//通过用户uid获取用户关注的岗位
	$course=getCourseBySeq($mystation[res],$num);//根据岗位ID和序号获取推荐课程及详细信息
	$course[setstatus]=$mystation[status];
	$course[sname]=$mystation[sname];
	echo json_encode($course);
}

/**
 *根据岗位id，序号获取课程及信息
 */
function changecourse(){
	global $_G;
	$num=empty($_G['gp_num'])?0:$_G['gp_num'];//推荐课程序号
	$sid=$_G['gp_stationid'];//获取用户岗位id
	require_once (dirname(__FILE__)."/function/function_coursemap.php");
	$course=getCourseBySeq($sid,$num);//根据岗位ID和序号获取推荐课程及详细信息
	echo json_encode($course);
}

/**
 * 根据岗位id和level获取子岗位详细信息
 */
function getchild(){
	global $_G;
	$pid=$_GET['pid'];//获取岗位id
	$level=$_GET['level'];//获取岗位层级
	if($level==0){//当获取的岗位为岗位族群时
		$child=getchildstation($pid);//根据岗位id获取子族群岗位
		$station=getchildstation($child[0][id]);//根据子族群岗位id获取基准岗位
		$course=getrecommends($station[0][id]);//根据基准岗位id获取基准岗位的所有推荐课程
		$use=array("one"=>$pid,"two"=>$child[0][id],"three"=>$station[0][id]);
	}else if($level==1){//当获取到岗位为子族群时
		$child=array();//子族群岗位为空
		$station=getchildstation($pid);//根据子族群岗位id获取基准岗位
		$course=getrecommends($station[0][id]);//根据基准岗位id获取基准岗位的所有推荐课程
		$use=array("one"=>null,"two"=>$pid,"three"=>$station[0][id]);
	}else if($level==2){//当获取到岗位为基准岗位时
		$child=array();//子族群岗位为空
		$station=array();//基准岗位为空
		$course=getrecommends($pid);//根据基准岗位id获取基准岗位的所有推荐课程
		$use=array("one"=>null,"two"=>null,"three"=>$pid);
	}

	$res=array("child"=>$child,"station"=>$station,"course"=>$course,"use"=>$use);
	echo json_encode($res);
}

/**
 * 根据课程编号，获取课程详细信息
 */
function getcourseinf(){
	global $_G;
    $code=$_GET['code'];//获取课程编号
    echo json_encode(getcourseinfo($code));//获取课程详细信息
}


/**
 * 根据uid设置岗位
 */
function setstation(){
	global $_G;
	$code=$_GET['code'];//获取验证码
	$uid=$_GET['uid'];//获取用户uid
	$stationid=$_GET['stationid'];//获取岗位id
	if($code==md5('setstation_'.$uid)){//当验证码匹配
		if((!$uid)||(!$stationid)){//没有获取到uid或者岗位id
			$res['success']="N";
			$res['message']="Lack Uid OR stattionid!";
		}else{//获取到了uid和岗位id
			$re=set_stattion($stationid,$uid);//根据uid设置关注岗位
			if($re){//设置成功
				$res['success']="Y";
				$res['message']="SET SUCCESS!";
			}else{//设置不成功
				$res['success']="N";
				$res['message']="SET FAIL!";
			}
		}
	}else{//验证码不匹配
		$res['success']="N";
		$res['message']="Code Error!";
	}
	echo json_encode($res);
}

/**
 * 根据岗位id获取全部信息
 */
function searchstation(){
	global $_G;
	$pid=$_GET['pid'];
	$path=getstationpath($pid);
	$root=getchildstation(-1);
	$child=getchildstation($path[one]);//根据岗位id获取子族群岗位
	$station=getchildstation($path[two]);//根据子族群岗位id获取基准岗位
	$course=getrecommends($path[three]);//根据基准岗位id获取基准岗位的所有推荐课程
	$res=array("root"=>$root,"child"=>$child,"station"=>$station,"course"=>$course,"use"=>$path);
	echo json_encode($res);
}

/**
 * 根据关键字查询岗位
 */
function search(){
	global $_G;
	$key=$_GET['key'];
	$res=searchd($key);
	echo json_encode($res);
}


//以下方法为暂时没有使用的
 /**
 * 访问url：http://localhost/forum/api.php?mod=plugin&app=coursemap:api&param=action%3Dsearchstation%26pid%3D341
 */
function index(){
	$info=array("uid"=>1,"name"=>"乔永志");
	echo json_encode($info);
}


/**
 *根据岗位id获取子岗位
 */
function getchild_bak(){
	global $_G;
	$info=DB::query("select id,name from ".DB::table("sc_station")." where parent_id=339");
	if($info==false){
		return null;
	}else{
		while($value=DB::fetch($info)){
			$obj[]=$value;
		}
		echo json_encode($obj);
	}

}

/**
 * 根据岗位id获取岗位下推荐的课程，带分页（带课程详细信息），有分页
 */
 function getcourses(){
    global $_G;
    $num=empty($_G['gp_num'])?1:$_G['gp_num'];//页码
    $shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];//每页显示课程数量
    $info1=DB::query("select num from ".DB::table("sc_station")." where id=341");
    $value1=DB::fetch($info1);
    $p=$value1[num];
    $i=ceil($p/$shownum);

   if($i){
   	if(0<$num){
   		if($num<$i+1){
   		$num1=($num-1)*$shownum;
   		}else{
   			$num=rand(1,$i);
   			$num1=($num-1)*$shownum;
   		}
   	}else{
   		$num=rand(1,$i);
   		$num1=($num-1)*$shownum;
   	}
   }

    $info2=DB::query("select * from ".DB::table("sc_relation")." where station_id=341 order by sequence limit $num1,$shownum");
    if($info2==false){
    	return false;
    }else{
    	while($value2=DB::fetch($info2)){
    		$obj[]=$value2;
    	}
    	echo json_encode($obj);
    }

 }

?>