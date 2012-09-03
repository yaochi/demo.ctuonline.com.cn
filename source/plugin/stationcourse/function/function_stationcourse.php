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

	function getCoursesPageByStation($fid,$station,$start,$perpage){//根据岗位获得相关的课程列表分页
		$sql="select sc.recommend_degree,c.* from ".DB :: table('station_course')." sc ,".DB :: table('courses')." c where c.course_id= sc.course_id AND sc.station_id=".$station." AND c.fid=".$fid." ORDER BY sc.order ASC LIMIT ".$start.",".$perpage;
		$info = DB::query($sql);
		if($info==False)
			{
				return  0;
			}else{
	        	while ($value = DB::fetch($info)){
	        		if(!$value[update_time]||($value[update_time]<time()-14*24*60*60)){
					$cours=getcourse($value[course_id]);
   					$cours=$cours[result];
   			   		if($cours){
   						$course[average]=$cours[averagescore];
   						$course[upload_time]=$cours[uploadtime]/1000;
   						$course[course_url]=$cours[titlelink];
   						$course[update_time]=time();
   						$where=array("id"=>$value[id]);
   						DB::update('courses',$course,$where);
   						$value[average]=$cours[averagescore];
   						$value[upload_time]=$course[upload_time];
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
		$query = DB::query("DELETE FROM ".DB::table("user_station")." WHERE fid=".$_G[fid]);

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
		require_once(dirname(dirname(__FILE__))."/upload.php");
		$fid=$_G[fid];
		$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$filepath="/data/attachment/plugin_stationcourse/station".$fid.".xls";
		$realpath.=$filepath;
		$data = new Spreadsheet_Excel_Reader(); //实例化
		$data->setOutputEncoding('gbk');      //编码
		$data->read($realpath);

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


	function import_course_1(){

		global $_G;
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		require_once(dirname(dirname(__FILE__))."/upload.php");
		$fid=$_G[fid];
		DB::query("DELETE FROM ".DB::table("courses")." WHERE fid=".$fid);
		$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$filepath="/data/attachment/plugin_stationcourse/course".$fid.".xls";
		$realpath.=$filepath;
		$data = new Spreadsheet_Excel_Reader(); //实例化
		$data->setOutputEncoding('gbk');      //编码
		$data->read($realpath);
		$course=array();
		$course[fid]=$_G[fid];

		//$conn=new com("adodb.connection");      //应用php预定义类创建连接对象
 		//$connstr="Driver={Microsoft Excel Driver (*.xls)};DBQ=".realpath($filepath);//设置驱动，指定Excel文件位置
 		//$conn->open($connstr);				//加载数据库驱动
 		//$sql="select * from [Sheet1$]";	//定义查询语句
 		//$rs=$conn->execute($sql);     //执行查询操作

 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
 		{    //循环输出查询结果，将数据值赋给指定的变量
			$course_id=$data->sheets[0]['cells'][$i][1];
   			$course[course_id]=$course_id;

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


	function import_stacourse_1(){
		global $_G;
	    $fid=$_G[fid];
	    require_once (dirname(dirname(__FILE__)).'/reader.php');
		require_once(dirname(dirname(__FILE__))."/upload.php");
		$sid=$_GET['sid'];
		$sname=$_GET['sname'];
		DB::query("DELETE FROM ".DB::table("station_course")." WHERE station_id=".$sid);
		$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$filepath="/data/attachment/plugin_stationcourse/station_course".$fid.".xls";
		$realpath.=$filepath;

		$data = new Spreadsheet_Excel_Reader(); //实例化
		$data->setOutputEncoding('gbk');      //编码
		$data->read($realpath);

		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++)
 		{    //循环输出查询结果，将数据值赋给指定的变量
			$course[station_id]=$sid;
			$course[station_name]=$sname;
			$course[fid]=$fid;

 			$course_id=$data->sheets[0]['cells'][$i][1];
   			$course_id=mb_convert_encoding($course_id,'UTF-8','GB2312');
 			$course[course_id]=$course_id;

			$course_name=$data->sheets[0]['cells'][$i][2];
   			$course_name=mb_convert_encoding($course_name,'UTF-8','GB2312');
 			$course[course_name]=$course_name;

 			$recommend_degree=$data->sheets[0]['cells'][$i][3];
   			$recommend_degree=mb_convert_encoding($recommend_degree,'UTF-8','GB2312');
 			$course[recommend_degree]=$recommend_degree;

 			//$create_uid=$data->sheets[0]['cells'][$i][3];
 			$course[create_uid]=$_G[uid];

 			//$create_time=$data->sheets[0]['cells'][$i][4];
 			$course[create_time]=time();

 			//$update_uid=$data->sheets[0]['cells'][$i][5];
 			$course[update_uid]=$_G[uid];

 			//$update_time=$data->sheets[0]['cells'][$i][6];
 			$course[update_time]=time();

 			$order=$data->sheets[0]['cells'][$i][5];
 			$course[order]=$order;


			DB::insert('station_course', $course);

 		}

	}
   			//$rs->movenext;

   	function download($file_dir,$file_name){
	$file   =   fopen($file_dir.$file_name, "r ");   //   打开文件
	//   输入文件标签
	Header( "Content-type:   application/octet-stream ");
	Header( "Accept-Ranges:   bytes ");
	Header( "Accept-Length:   ".filesize($file_dir.$file_name));
	Header( "Content-Disposition:   attachment;   filename= ".$file_name);
	//   输出文件内容
	echo   fread($file,filesize($file_dir.$file_name));
	fclose($file);
}


?>