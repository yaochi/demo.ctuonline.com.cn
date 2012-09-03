<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('DISCUZ_CORE_FUNCTION', true);

	function getStation($uid,$fid,$type){//获得自己的相关岗位，type=0，当前岗位；type=1，感兴趣的岗位
		$query = DB::query("SELECT * FROM ".DB::table("user_station")." WHERE type=".$type." AND uid=".$uid." AND fid=".$fid);
		$result = array();
    	$result = DB::fetch($query);
    	return $result;
	}
	
	function getCoursesByStation($fid,$station){//根据岗位获得相关的课程列表
		$sql="select c.* from ".DB :: table('station_course')." sc ,".DB :: table('courses')." c where c.course_id= sc.course_id AND sc.station_id=".$station." AND c.fid=".$fid;
		$info = DB::query($sql);
		if($info==False)
			{
				return  0;
			}else{
	        	while ($value = DB::fetch($info)){
				$obj[] = $value;
				}
			return $obj;
			}	
	}
	
	function getcoursecount($station){
		global $_G;
		$sql="select count(*) as count from ".DB :: table('station_course')." where fid=".$_G[fid]." AND station_id=".$station;
		$info = DB::query($sql);
		$value = DB::fetch($info);
		return $value[count];
	}
	
	//根据岗位获得相关的课程列表分页
	function getCoursesPageByStation($fid,$station,$start,$perpage){
		
		$sql="select * from ".DB::table('station_knowedgle')." where station_id=".$station;
		$result=array();
		$result=DB::query($sql);
		$value=DB::fetch($result);
		if(count($value)==0){
			return 0;
		}
		$query="select ck.cid ,c.* ,count(ck.cid) level from ".DB::table('course_knowledge ')." ck left join ".DB::table('courses')." c on  ck.cid = c.id ";
		$wherr_array=array();
		if(!empty($value[vd1])){
			$wherr_array[]=" (type=1 and knowledge_id=".$value[vd1].")";
		}
		if(!empty($value[vd2])){
			$wherr_array[]=" (type=2 and knowledge_id=".$value[vd2].")";
		}
		if(!empty($value[vd3])){
			$wherr_array[]=" (type=3 and knowledge_id=".$value[vd3].")";
		}
		if(!empty($value[vd4])){
	
			$wherr_array[]=" (type=4 and knowledge_id=".$value[vd4].")";
		}
		if(!empty($value[vd5])){
	
			$wherr_array[]=" (type=5 and knowledge_id=".$value[vd5].")"; 
		}
		if(!empty($value[vd6])){
	
			$wherr_array[]=" (type=6 and knowledge_id=".$value[vd6].")";
		}
		if(!$wherr_array){
			return 0;
		}
		$query.="where ".$wherr_array[0];
		for ($i = 1; $i <count($wherr_array); $i++) {
			$query.=' or'.$wherr_array[$i];	
		}
		$query.=' group by ck.cid  order by level desc';
		$info = DB::query($query);
		if($info==False)
			{
				return  0;
			}else{
	        	while ($value = DB::fetch($info)){
	        		if(!$value[course_url]){
					$cours=getcourse($value[course_id]);
   					$cours=$cours[result];
   			   		if($cours){
   						$course[average]=$cours[averagescore];
   						$course[upload_time]=$cours[uploadtime]/1000;
   						$course[course_url]=$cours[titlelink];
   						$where=array("id"=>$value[id]);
   						DB::update('courses',$course,$where);
   						$value[average]=$cours[averagescore];
   						$value[upload_time]=$course[uploadtime];
   						$value[course_url]=$cours[titlelink];
						}
						
					}
				$obj[] = $value;
				}
			return $obj;
			}	
	}
	
	
	function getAllStation(){
		$fid=$_GET['fid'];
		$info = DB::query("SELECT id,name,type,parent_id,parent_name FROM ".DB::table("station")." WHERE fid=".$fid." ORDER BY id ASC");
		if($info==False)
		{
			return  0;
		}else{
	        while ($value = DB::fetch($info)){
			$obj[] = $value;
			}
			return $obj;
		}
	}
	
	function getAllStation_Order($fid){//岗位树Excel内容
		global $_G;
		$sql="SELECT ss.parent_name as first,s.parent_name as second,s.name as third from ".DB::table("station")." s, ".DB::table("station")." ss WHERE  s.parent_id=ss.id AND s.fid=".$_G[fid]." AND s.type=2 ORDER BY ss.id,s.parent_id,s.id ASC";
		$info = DB::query($sql);
		if($info==False)
			{
				return  0;
			}else{
	       		while ($value = DB::fetch($info)){
				$obj[] = $value;
				}
				return $obj;
			}	
	}
	//课程Excel内容
	function getAllCourse_Order($fid){
		global $_G;
		$sql="select * from ".DB::table('courses')." where fid=".$_G[fid];
		$result=DB::query($sql);
		if($result==false){
			return 0;
		}else{
			while ($value=DB::fetch($result)) {
				$obj[] = $value;
			}
			return $obj;
		}
	}	
	
	//根据课程得到知识点
	function getbyidAllCourse_knowledge($type,$courseid){
		global $_G;
		$sql="select c.kowedgle_name as kowedglename ,c_k.type as type from ".DB::table('knowledge')." c , ".DB::table('course_knowledge')." c_k where c.id=c_k.knowledge_id and type=".$type."  and c_k.cid=".$courseid;
		$result = array();
    	$result = DB::query($sql);
	 	$value=DB::fetch($result);
		return $value[kowedglename];	
	}
	function statistics(){
		global $_G;
		$total=array();
		$total[menbernum]=$_G['forum']['membernum'];
		$query = DB::query("SELECT count(*) AS num FROM ".DB::table("user_station")." WHERE type=0 AND station_id!=-1 AND fid=".$_G[fid]);
		$result=DB::fetch($query);
		$total[self]=$result[num];
		$query = DB::query("SELECT count(*) AS num FROM ".DB::table("user_station")." WHERE type=1 AND fid=".$_G[fid]);
		$result=DB::fetch($query);
		$total[interest]=$result[num];
		return $total;
	}
	function forceset(){
		global $_G;
		$query = DB::query("UPDATE ".DB::table("user_station")." set station_id=-1,station_name=''  WHERE type=0 AND fid=".$_G[fid]);
		
	}
	function erase_data(){
		global $_G;
		$query = DB::query("DELETE FROM ".DB::table("user_station")." WHERE fid=".$_G[fid]);
		$query = DB::query("DELETE FROM ".DB::table("station_course")." WHERE fid=".$_G[fid]);
		$query = DB::query("DELETE FROM ".DB::table("station")." WHERE fid=".$_G[fid]);
		$query = DB::query("DELETE FROM ".DB::table("courses")." WHERE fid=".$_G[fid]);
    	return 1;
		
	}
	
	function getSetCase(){//专区所有设置岗位的情况
		global $_G;
		$fid=$_G[fid];
		$getsql1="SELECT u.username AS username,u.station_name AS mystation,s.station_name AS intereststation FROM ";
		$getsql2="SELECT u.username AS username,s.station_name AS mystation,u.station_name AS intereststation FROM ";
		$table=DB :: table('user_station');
		$onsql=" on u.uid=s.uid and u.fid=s.fid and u.type!=s.type where u.fid=".$fid;
		$joinsql=$table." u  left join ".$table." s".$onsql." AND u.type=";
		$sql=$getsql1.$joinsql."0 union ".$getsql2.$joinsql."1";
		$info = DB::query($sql);
		if($info==False)
			{
				return  0;
			}else{
	       		while ($value = DB::fetch($info)){
				$obj[] = $value;
				}
				return $obj;
			}	
		
	}
	
	function getrootstation(){
		global $_G;
		$info = DB::query("SELECT id,name,parent_id,parent_name FROM ".DB::table("station")." WHERE parent_id=-1 AND fid=".$_G[fid]);		
		$value = DB::fetch($info);
		$id=$value[id];
		return $id;
	}
	
		function getByknowidbyknowname($name){
		
		$sql="select id from ".DB::table('knowledge')." where kowedgle_name='".$name."'";
		$info = DB::query($sql);		
		$value = DB::fetch($info);
		$id=$value[id];
		return $id;
	}
	function gestationbyname($name){
		global $_G;
		$info = DB::query("SELECT id,name,parent_id,parent_name FROM ".DB::table("station")." WHERE name='".$name."' AND fid=".$_G[fid]);		
		$value = DB::fetch($info);
		$id=$value[id];
		return $id;
	}
	
	function insertrootstation(){
		global $_G;
		$root[fid]=$_G[fid];
		$root[create_uid]=$_G[uid];
    	$root[create_time]=time();
		$root[update_uid]=$_G[uid];
    	$root[update_time]=time();
		$root[parent_id]=-1;
		$root[type]=-1;
				
    	$rootid=DB::insert('station', $root);
    	return $rootid;
	}
	
	function deleteAllStation(){
		global $_G;
		$query = DB::query("DELETE FROM ".DB::table("user_station")." WHERE fid=".$_G[fid]);
		$query = DB::query("DELETE FROM ".DB::table("station_course")." WHERE fid=".$_G[fid]);
		$query = DB::query("DELETE FROM ".DB::table("station")." WHERE fid=".$_G[fid]);
    	return 1;
	}
	
	function import_station(){//初始化岗位
		global $_G;
		$filepath="data/attachment/plugin_stationcourse/".$_G[fid].".xls";
		deleteAllStation();
		$rootid=getrootstation();
		if(!$rootid){
		insertrootstation();
    	$rootid=getrootstation();
		}
		$station[fid]=$_G[fid];
		$station[create_uid]=$_G[uid];
   		$station[create_time]=time();
		$station[update_uid]=$_G[uid];
    	$station[update_time]=time();
		
		$array=$array2=array();
		$str=$str2="";
		$id=$id2=0;
		$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		$conn->open($connstr);				//加载数据库驱动
 		$sql="select * from [Sheet1$]";	//定义查询语句
 		
 		$rs=$conn->execute($sql);     //执行查询操作
 		while(!$rs->eof)
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
 			//插入第一级族群
 			$station[type]=0;
 			$station[parent_id]=$rootid;
 			$station[parent_name]="";
   			$fields=$rs->fields(first);
   			$name=$fields->value;
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			if (!in_array($name, $array)){
   			$array[]=$name;
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
   			
   			$str=$name;
   			$id=gestationbyname($name);
   			}
   			
  			//插入第二级族群
 			$station[type]=1;
 			$station[parent_id]=$id;
 			$station[parent_name]=$str;
   			$fields=$rs->fields(second);
   			$name=$fields->value;
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			if (!in_array($name, $array2)){
   			$array2[]=$name;
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
  			
  			$str2=$name;
   			$id2=gestationbyname($name);
   			}
   			
 			//插入第三级族群
 			$station[type]=2;
 			$station[parent_id]=$id2;
 			$station[parent_name]=$str2;
   			$fields=$rs->fields(third);
   			$name=$fields->value;
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
   			
 			$rs->movenext;
 		}	
 		
	}
	//清空所属专区的岗位知识点
	function dellAllStation_knoweldge(){
			global $_G;
			$query = DB::query("DELETE FROM ".DB::table("station_knowedgle")." WHERE fid=".$_G[fid]);
    		return 1;
	}
	function import_course(){
		global $_G;
		$filepath="data/attachment/plugin_stationcourse/".$_G[fid].".xls";
		$course=array();
		$course[fid]=$_G[fid];
		$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		$conn->open($connstr);				//加载数据库驱动
 		$sql="select * from [Sheet2$]";	//定义查询语句
 		
 		$rs=$conn->execute($sql);     //执行查询操作
 		while(!$rs->eof)
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
 			$fields=$rs->fields(cai_type);
   			$cai_type=$fields->value;
   			$course[cai_type]=$cai_type;
   			
   			$fields=$rs->fields(class_hour);
   			$class_hour=$fields->value;
   			$course[class_hour]=$class_hour;
   			
   			$fields=$rs->fields(recommend);
   			$recommend=$fields->value;
   			$course[recommend]=$recommend;
 			
 			$fields=$rs->fields(course_name);
   			$course_name=$fields->value;
   			$course_name=mb_convert_encoding($course_name,'UTF-8','GB2312');
 			$course[course_name]=$course_name;
 			
 			$fields=$rs->fields(course_type);
   			$course_type=$fields->value;
   			$course_type=mb_convert_encoding($course_type,'UTF-8','GB2312');
 			$course[course_type]=$course_type;
 			
 			$fields=$rs->fields(cai_resource);
   			$cai_resource=$fields->value;
   			$cai_resource=mb_convert_encoding($cai_resource,'UTF-8','GB2312');
 			$course[cai_sourse]=$cai_resource;
 			
 			$fields=$rs->fields(introduction);
   			$introduction=$fields->value;
   			$introduction=mb_convert_encoding($introduction,'UTF-8','GB2312');
 			$course[introduction]=$introduction;
 			
 			$fields=$rs->fields(course_id);
   			$course_id=$fields->value;
   			$course[course_id]=$course_id;
   			$cours=getcourse($course_id);
   			$cours=$cours[result];
   			if($cours){
   			$course[average]=$cours[averagescore];
   			$course[upload_time]=$cours[uploadtime];
   			$course[course_url]=$cours[titlelink];
   			
   			DB::insert('courses', $course);
   			}
   			$rs->movenext;
 		}
	}
	
	
	function import_stacourse(){
	global $_G;
		$filepath="data/attachment/plugin_stationcourse/".$_G[fid].".xls";
		$course=array();
		$course[fid]=$_G[fid];
		$course[create_uid]=$_G[uid];
    	$course[create_time]=time();
    	$course[update_uid]=$_G[uid];
    	$course[update_time]=time();
		$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		$conn->open($connstr);				//加载数据库驱动
 		$sql="select * from [Sheet3$]";	//定义查询语句
 		
 		$rs=$conn->execute($sql);     //执行查询操作
 		while(!$rs->eof)
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
 			$fields=$rs->fields(station);
   			$station=$fields->value;
   			$station=mb_convert_encoding($station,'UTF-8','GB2312');
   			$id=gestationbyname($station);
   			if($id){
   			$course[station_id]=$id;
			$course[station_name]=$station;
   			
   			$fields=$rs->fields(course_id);
   			$course_id=$fields->value;
   			$course[course_id]=$course_id;
 			
 			$fields=$rs->fields(course_name);
   			$course_name=$fields->value;
   			$course_name=mb_convert_encoding($course_name,'UTF-8','GB2312');
 			$course[course_name]=$course_name;
 	
   			DB::insert('station_course', $course);
   			}
   			$rs->movenext;
 		}
		
	}
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
	function getcourse($id) {
		global $_G;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?q=".$id;
		$str1 = openFileAPI($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			return;
		}
		$filejson=json_decode($str1, true);
		$resource = $filejson['resources'][0];
		return array("result"=>$resource);
	}
	
	function import_station_l(){//初始化岗位
		global $_G;
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		$filepath="data/attachment/plugin_stationcourse/station".$_G[fid].".xls";
		$data = new Spreadsheet_Excel_Reader(); //实例化   
		$data->setOutputEncoding('gbk');      //编码   
		$data->read($filepath);
		
		deleteAllStation();
		$rootid=getrootstation();
		if(!$rootid){
		insertrootstation();
    	$rootid=getrootstation();
		}
		$station[fid]=$_G[fid];
		$station[create_uid]=$_G[uid];
   		$station[create_time]=time();
		$station[update_uid]=$_G[uid];
    	$station[update_time]=time();
		
		$array=$array2=array();
		$str=$str2="";
		$id=$id2=0;
		//$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		//$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		//$conn->open($connstr);				//加载数据库驱动
 		//$sql="select * from [Sheet1$]";	//定义查询语句
 		//$rs=$conn->execute($sql);     //执行查询操作
 		
 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
 			//插入第一级族群
 			$station[type]=0;
 			$station[parent_id]=$rootid;
 			$station[parent_name]="";
   			$name=$data->sheets[0]['cells'][$i][1];
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			if (!in_array($name, $array)){
   			$array[]=$name;
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
   			
   			$str=$name;
   			$id=gestationbyname($name);
   			}
   			
  			//插入第二级族群
 			$station[type]=1;
 			$station[parent_id]=$id;
 			$station[parent_name]=$str;
   			$name=$data->sheets[0]['cells'][$i][2];
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			if (!in_array($name, $array2)){
   			$array2[]=$name;
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
  			
  			$str2=$name;
   			$id2=gestationbyname($name);
   			}
   			
 			//插入第三级族群
 			$station[type]=2;
 			$station[parent_id]=$id2;
 			$station[parent_name]=$str2;
   			$name=$data->sheets[0]['cells'][$i][3];
   			$name=mb_convert_encoding($name,'UTF-8','GB2312');
   			$station[name]=$name;
   			//定义执行insert添加语句，向MySQL数据库中添加数据，完成数据的导出操作
  			DB::insert('station', $station);
   			
 		}	
 		
	}
	//清空课程知识点
	
	function dellAllCourse_knoweldge(){
			global $_G;
			$query = DB::query("DELETE FROM ".DB::table("course_knowledge")." WHERE fid=".$_G[fid]);
    		return 1;
	}
	//根据课程名称的得到课程id
		function gecoursebycourseid($name){
		$sql="SELECT id FROM ".DB::table("courses")." WHERE course_name='".$name."'";
		$info = DB::query($sql);		
		$value = DB::fetch($info);
		$id=$value[id];
		return $id;
	}
	function  import_course_knoweldg(){
		global $_G;
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		$filepath="data/attachment/plugin_stationcourse/course".$_G[fid].".xls";
		$data = new Spreadsheet_Excel_Reader(); //实例化   
		$data->setOutputEncoding('gbk');      //编码   
		$data->read($filepath);
		$course_knowledge=array();
		$course_knowledge[fid]=$_G[fid];
		dellAllCourse_knoweldge();
		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
			
			$course_knowledge_name=$data->sheets[0]['cells'][$i][2];
   			$course_knowledge_name=mb_convert_encoding($course_knowledge_name,'UTF-8','GB2312');
   			$cid=gecoursebycourseid($course_knowledge_name);
 			$course_knowledge[cid]=$cid;
			
			$vd1_name=trim($data->sheets[0]['cells'][$i][9]);//维度1
			$vd1_name=mb_convert_encoding($vd1_name,'UTF-8','GB2312');
			$vd1=getByknowidbyknowname($vd1_name);
 			$course_knowledge[knowledge_id]=$vd1;
   			$course_knowledge[type]="1";
   			if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
   			
			$vd2_name=trim($data->sheets[0]['cells'][$i][10]);//维度2
			$vd2_name=mb_convert_encoding($vd2_name,'UTF-8','GB2312');
			$vd2=getByknowidbyknowname($vd2_name);
 			$course_knowledge[knowledge_id]=$vd2;
            $course_knowledge[type]="2";
            if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
   			
			$vd3_name=trim($data->sheets[0]['cells'][$i][11]);//维度3
			$vd3_name=mb_convert_encoding($vd3_name,'UTF-8','GB2312');
			$vd3=getByknowidbyknowname($vd3_name);
 			$course_knowledge[knowledge_id]=trim($vd3);
   			$course_knowledge[type]="3";
   			if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
   		
			$vd4_name=trim($data->sheets[0]['cells'][$i][12]);//维度4
		 	$vd4_name=mb_convert_encoding($vd4_name,'UTF-8','GB2312');
			$vd4=getByknowidbyknowname($vd4_name);
 			$course_knowledge[knowledge_id]=trim($vd4);
   			$course_knowledge[type]="4";
   			if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
   			
			$vd5_name=trim($data->sheets[0]['cells'][$i][13]);//维度5
			$vd5_name=mb_convert_encoding($vd5_name,'UTF-8','GB2312');
			$vd5=getByknowidbyknowname($vd5_name);
 			$course_knowledge[knowledge_id]=trim($vd5);
   			$course_knowledge[type]="5";
   			if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
   			
			$vd6_name=trim($data->sheets[0]['cells'][$i][14]);//维度6
			$vd6_name=mb_convert_encoding($vd6_name,'UTF-8','GB2312');
			$vd6=getByknowidbyknowname($vd6_name);
 			$course_knowledge[knowledge_id]=trim($vd6);
   			$course_knowledge[type]="6";
   			if($course_knowledge[knowledge_id]) DB::insert('course_knowledge', $course_knowledge);
		}
	}
	//清空所属专区的课程
	function dellAllCourse(){
			global $_G;
			$query = DB::query("DELETE FROM ".DB::table("courses")." WHERE fid=".$_G[fid]);
    		return 1;
	}
	function import_course_1(){
		global $_G;
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		$filepath="data/attachment/plugin_stationcourse/course".$_G[fid].".xls";
		$data = new Spreadsheet_Excel_Reader(); //实例化   
		$data->setOutputEncoding('gbk');      //编码   
		$data->read($filepath);
		$course=array();
		$course[fid]=$_G[fid];
		dellAllCourse();
		//$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		//$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		//$conn->open($connstr);				//加载数据库驱动
 		//$sql="select * from [Sheet2$]";	//定义查询语句
 		//$rs=$conn->execute($sql);     //执行查询操作
 		
 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) 
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
 			$course_name=$data->sheets[0]['cells'][$i][2];
   			$course_name=mb_convert_encoding($course_name,'UTF-8','GB2312');
 			$course[course_name]=$course_name;
 			
 			$course_type=$data->sheets[0]['cells'][$i][3];
   			$course_type=mb_convert_encoding($course_type,'UTF-8','GB2312');
 			$course[course_type]=$course_type;
 			
   			$cai_type=$data->sheets[0]['cells'][$i][4];
   			$course[cai_type]=$cai_type;
   			
   			$cai_resource=$data->sheets[0]['cells'][$i][5];
   			$cai_resource=mb_convert_encoding($cai_resource,'UTF-8','GB2312');
 			$course[cai_sourse]=$cai_resource;
   			
   			$class_hour=$data->sheets[0]['cells'][$i][6];
   			$course[class_hour]=$class_hour;
   			
   			$recommend=$data->sheets[0]['cells'][$i][7];
   			$course[recommend]=$recommend;
 			
   			$introduction=$data->sheets[0]['cells'][$i][8];
   			$introduction=mb_convert_encoding($introduction,'UTF-8','GB2312');
 			$course[introduction]=$introduction;
 			
   			$course_id=$data->sheets[0]['cells'][$i][1];
   			$course[course_id]=$course_id;
   			//$cours=getcourse($course_id);
   			//$cours=$cours[result];
   			//if($cours){
   			//$course[average]=$cours[averagescore];
   			//$course[upload_time]=$cours[uploadtime];
   			//$course[course_url]=$cours[titlelink];
   			
   			DB::insert('courses', $course);
   			//}
 		}
	}
	
	
	function import_station_knowldge(){
		global $_G;
		
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		$filepath="data/attachment/plugin_stationcourse/station_knowledge".$_G[fid].".xls";
		$data = new Spreadsheet_Excel_Reader(); //实例化   
		$data->setOutputEncoding('gbk');      //编码   
		$data->read($filepath);
		dellAllStation_knoweldge();
		$course=array();
		$course[fid]=$_G[fid];
		$course[create_uid]=$_G[uid];
    	$course[create_time]=time();
    	$course[update_uid]=$_G[uid];
    	$course[update_time]=time();
		//$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		//$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		//$conn->open($connstr);				//加载数据库驱动
 		//$sql="select * from [Sheet3$]";	//定义查询语句
 		//$rs=$conn->execute($sql);     //执行查询操作
 		$sheets_numRows=$data->sheets[0]['numRows'];
 		
 		for ($i = 2; $i <= $sheets_numRows; $i++)
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			
   		 	$station_name=$data->sheets[0]['cells'][$i][1];
   			$station_name=mb_convert_encoding($station_name,'UTF-8','GB2312');
   			$station_id=gestationbyname($station_name);
   			if($station_id){
   			$station_knowedgle[station_id]=$station_id;
   			
		    $vd1=trim($data->sheets[0]['cells'][$i][2]);
		    $vd1=mb_convert_encoding($vd1,'UTF-8','GB2312');
   			$station_knowedgle[vd1]=getByknowidbyknowname($vd1);
			
			$vd2=trim($data->sheets[0]['cells'][$i][3]);
   			$vd2=mb_convert_encoding($vd2,'UTF-8','GB2312');
   			$station_knowedgle[vd2]=getByknowidbyknowname($vd2);
 			
   			$vd3=trim($data->sheets[0]['cells'][$i][4]);
   			$vd3=mb_convert_encoding($vd3,'UTF-8','GB2312');
   			$station_knowedgle[vd3]=getByknowidbyknowname($vd3);
 			
   			$vd4=trim($data->sheets[0]['cells'][$i][5]);
   			$vd4=mb_convert_encoding($vd4,'UTF-8','GB2312');
   			$station_knowedgle[vd4]=getByknowidbyknowname($vd4);
 			
   			$vd5=trim($data->sheets[0]['cells'][$i][6]);
   			$vd5=mb_convert_encoding($vd5,'UTF-8','GB2312');
   			$station_knowedgle[vd5]=getByknowidbyknowname($vd5);
 			
   			$vd6=trim($data->sheets[0]['cells'][$i][7]);
   			$vd6=mb_convert_encoding($vd6,'UTF-8','GB2312');
   			$station_knowedgle[vd6]=getByknowidbyknowname($vd6);
 			
   			$station_knowedgle[fid]='507';
   			DB::insert('station_knowedgle', $station_knowedgle);
   			}
 		}
		
	}
	
?>