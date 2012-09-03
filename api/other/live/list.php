<?php
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	global $_G;
	$action=$_G['gp_ac']?$_G['gp_ac']:"getlist";
	$action();

	function getlist(){
		global $_G;
		$page=$_G['gp_page']?$_G['gp_page']:1;
		$per=$_G['gp_per']?$_G['gp_per']:10;
		$fid=$_G['gp_fid'];
		$name=$_G['gp_name'];
		$where=" ";
		if($fid) $where.= " AND fid=".$fid;
		if($name) $where.= " AND subject like '%".$name."%'";
		$sql_count="select count(*) as num from pre_group_live where 1=1 ".$where;
		$count=DB::fetch(DB::query($sql_count));

		$query=DB::query("select liveid,newliveid,subject,endtime-starttime as times,fid,secondman_ids,url from pre_group_live where 1=1 ".$where." order by starttime desc limit ".($page-1)*$per.",".$per);
		while($value=DB::fetch($query))
		{
			$val[name]=$value[subject];
			$val[times]=($value[times]>0&&$value[times]<36000)?$value[times]:0;
			$val[fid]=$value[fid];
			$val[lecturer]=$value[secondman_ids]? getNames($value[secondman_ids]) :"未知";
			$val[url]=$value[url]?$value[url]:"http://broadcast.myctu.cn/live/beforelive.do?action=start&liveid=".$value[newliveid]."&fid=".$value[fid];
			$obj[]=$val;
		}
		$data=array("count"=>$count[num],"list"=>$obj);
		$jsondata = json_encode ($data);
		echo $jsondata;
	}

	function getNames($ids){
		global $_G;
		$arr= explode(",",$ids);
		$names="1,";
		foreach($arr as $id){
			$name=user_get_user_name($id);
			$names.=$name;
		}
		return substr($names,2);
	}

?>