<?php
/*
 * Created on 2012-8-2
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 /**
  *计算UTF-8编码的字符串长度
  */
function mystrlen_utf8($string = null) {
	preg_match_all("/./us", $string, $match);// 将字符串分解为单元
	return count($match[0]);// 返回单元个数
}

/**
 * 插入岗位
 */
 function insertstation($station){
	global $_G;
	$sequence=1;
	$info=DB::query("select * from ".DB::table("sc_station")." where level=".$station[level]." and name='".$station[name]."'");
	$value=DB::fetch($info);
	if($value){
		if($station[level]==2) $sequence=$value[num]+1;
		$sid=$value[id];
		if(!$value[status]){
			$numsql="";
			if($station[level]==2) $numsql=",num=num+1";
			DB::query("update ".DB::table("sc_station")." set status=1".$numsql." where name='".$station[name]."'");
		}else{
			if($station[level]==2)
				DB::query("update ".DB::table("sc_station")." set num=num+1 where name='".$station[name]."'");
		}
	}else{
		$station[pname]=spell($station[name]);
		$sid=DB::insert('sc_station',$station,true);
	}
	return array("sid"=>$sid,"sequence"=>$sequence);
}

/**
 * 检查导入文件的数据格式是否正确
 */
 function check(){
	global $_G;
    require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/common/phpexcel/reader.php');
    	$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$filepath="/data/attachment/plugin_coursemap/coursemap.xls";
		$realpath.=$filepath;
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('gbk');
		$data->read($realpath);
		$root=mb_convert_encoding($data->sheets[0]['cells'][1][1],'UTF-8','GB2312');
        	if($root!='岗位族群'){
        		$mes[]=array("row"=>1,"col"=>1,"mes"=>"应该是“岗位族群”");
        	}
		$child=mb_convert_encoding($data->sheets[0]['cells'][1][2],'UTF-8','GB2312');
        	if($child!='子族群'){
        		$mes[]=array("row"=>1,"col"=>2,"mes"=>"应该是“子族群”");
        }
		$station=mb_convert_encoding($data->sheets[0]['cells'][1][3],'UTF-8','GB2312');
        	if($station!='基准岗位'){
        		$mes[]=array("row"=>1,"col"=>3,"mes"=>"应该是“基准岗位”");
        	}
		$code=mb_convert_encoding($data->sheets[0]['cells'][1][4],'UTF-8','GB2312');
        	if($code!='课程编号'){
        		$mes[]=array("row"=>1,"col"=>4,"mes"=>"应该是“课程编号”");
        	}
		$name=mb_convert_encoding($data->sheets[0]['cells'][1][5],'UTF-8','GB2312');
		if($name!='课程名称'){
        		$mes[]=array("row"=>1,"col"=>5,"mes"=>"应该是“课程名称”");
        	}
		for ($i = 2; $i < $data->sheets[0]['numRows']; $i++) {
			$one=$data->sheets[0]['cells'][$i][1];
			$one=mb_convert_encoding($one,'UTF-8','GB2312');
           if($one){
				if(mystrlen_utf8($one)>15){
            	$mes[]=array("row"=>$i,"col"=>1,"mes"=>"岗位族群长度不应该超过10");
			}
            }else{
            	$mes[]=array("row"=>$i,"col"=>1,"mes"=>"岗位族群名称为空");
            }
			$two=$data->sheets[0]['cells'][$i][2];
			$two=mb_convert_encoding($two,'UTF-8','GB2312');
    		if($two){
				if(mystrlen_utf8($two)>15){
            	$mes[]=array("row"=>$i,"col"=>2,"mes"=>"子族群长度不应该超过10");
			}
            }else{
            	$mes[]=array("row"=>$i,"col"=>2,"mes"=>"子族群名称为空");
            }
			$three=$data->sheets[0]['cells'][$i][3];
			$three=mb_convert_encoding($three,'UTF-8','GB2312');
			if($three){
				if(mystrlen_utf8($three)>15){
            	$mes[]=array("row"=>$i,"col"=>3,"mes"=>"岗位名称长度不应该超过10");
			}
            }else{
            	$mes[]=array("row"=>$i,"col"=>3,"mes"=>"岗位名称为空");
            }
			$four=$data->sheets[0]['cells'][$i][4];
			$four=mb_convert_encoding($four,'UTF-8','GB2312');
			$str1="/^[A-Za-z0-9\-]{1,25}+$/";
			if($four){
				if(preg_match($str1,$four)==false){
					$mes[]=array("row"=>$i,"col"=>4,"mes"=>"课程编号不正确");
				}
			}else{
				$mes[]=array("row"=>$i,"col"=>4,"mes"=>"课程编号为空");
			}
			$five=$data->sheets[0]['cells'][$i][5];
			$five=mb_convert_encoding($five,'UTF-8','GB2312');
			if($five){
				if(mystrlen_utf8($five)>100){
            	$mes[]=array("row"=>$i,"col"=>5,"mes"=>"课程名称长度不应该超过100");
			}
            }else{
            	$mes[]=array("row"=>$i,"col"=>5,"mes"=>"课程名称为空");
            }
 		}

		if($mes){
			return array("suc"=>0,"mes"=>$mes);
		}else{
 		    return array("suc"=>1,"mes"=>"上传文件没有问题");
		}
 }

/**
 * 初始化岗位课程数据
 */
 function init_coursemap(){
 	global $_G;
    require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/common/phpexcel/reader.php');
    	$info=DB::query("UPDATE ".DB::table("sc_station")." SET status=0,num=0");
    	$info=DB::query("delete from ".DB::table("sc_relation"));
    	$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		$filepath="/data/attachment/plugin_coursemap/coursemap.xls";
		$realpath.=$filepath;
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('gbk');
		$data->read($realpath);
		$relation_arr=array();
		for ($i = 2; $i < $data->sheets[0]['numRows']; $i++) {
			$name=$data->sheets[0]['cells'][$i][1];
			$station[name]=mb_convert_encoding($name,'UTF-8','GB2312');
			$station[level]=0;
			$station[parent_id]=-1;
			$station[status]=1;
			$station[num]=0;
			$ids[0]=insertstation($station);

			$name=$data->sheets[0]['cells'][$i][2];
			$station[name]=mb_convert_encoding($name,'UTF-8','GB2312');
			$station[level]=1;
			$station[parent_id]=$ids[0][sid];
			$station[status]=1;
			$station[num]=0;
			$ids[1]=insertstation($station);

			$name=$data->sheets[0]['cells'][$i][3];
			$station[name]=mb_convert_encoding($name,'UTF-8','GB2312');
			$stationname=$station[name];
			$station[level]=2;
			$station[parent_id]=$ids[1][sid];
			$station[status]=1;
			$station[num]=1;
			$ids[2]=insertstation($station);

			$relation[station_id]=$ids[2][sid];
			$relation[coursecode]=$data->sheets[0]['cells'][$i][4];
			$coursename=$data->sheets[0]['cells'][$i][5];
			$relation[coursename]=mb_convert_encoding($coursename,'UTF-8','GB2312');
			$relation[sequence]=$ids[2][sequence];
			$key=$relation[station_id]."_".$relation[coursecode];
			if(!$relation_arr[$key]){
				DB::insert('sc_relation',$relation);
				$relation_arr[$key]=1;
			}

 		}
 		DB::query("update pre_sc_ustation set status=-1 where station_id in(select id from pre_sc_station where status=0)");
 		DB::query("delete from pre_sc_station where status=0");
 }

/**
 *获取课程详细信息
 */
 function getcourseinfo($code) {
		global $_G;
		if($_G[config]['memory']['redis']['on']){
			$redis = new Redis();
			$resource=$redis->hset("coursemap",$code);
			if($resource)
				return $resource;
		}
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?q=".$code;
		$str1 = openFileAPIcompany($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			return;
		}
		$filejson=json_decode($str1, true);
		$resource = $filejson['resources'][0];
		if($_G[config]['memory']['redis']['on']&&$resource){
			$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
			$redis->hset("coursemap", $code, $resource);
		}
		return $resource;
}

/**
 * 获取用户的关注岗位
 */
 function getustation($uid){
 	//根据uid获取到关注岗位id
	$set=DB::fetch(DB::query("select us.station_id,us.status,s.name from pre_sc_ustation us,pre_sc_station s where s.id=us.station_id AND us.uid=".$uid));
	//如果没有关注岗位则设置为岗位名为产品管理的岗位
	$status=$set[status];
	$sname=$set[name];
	if($set[status]!=1){
		$res=DB::fetch(DB::query("select id from pre_sc_station where name='产品管理'"));
		$res=$res[id];
		$sname="产品管理";
	}
	else	$res=$set[station_id];
	if(empty($status)) $status=0;
	return array("set"=>$set[station_id],"res"=>$res,"status"=>$status,"sname"=>$sname);
 }

 /**
  * 根据岗位ID和序号获取推荐课程及详细信息
  */
  function getCourseBySeq($stationid,$seq){
	//获取岗位下推荐课程数
	$num=getcoursenum($stationid);
	//当未获取到序号时，随机一个序号
	if(!$seq)	$seq=rand(1,$num);
	else	$seq=$seq%$num;
    //获取推荐课程的课程序号
	$code=DB::fetch(DB::query("select coursecode from ".DB::table("sc_relation")." where sequence=$seq and station_id=".$stationid));
	//获取课程详细信息
	$course=getcourseinfo($code[coursecode]);
	//将课程序号加入详细信息里返回
	$course[sequence]=$seq;
	return $course;
  }

/**
 * 获取岗位的完整目录
 */
function getstationpath($station){
	$info=DB::query("select t.parent_id as one,t.id as two,s.id as three from pre_sc_station s,pre_sc_station t where s.id=".$station." AND t.id=s.parent_id");
	$value=DB::fetch($info);
	return $value;
 }

/**
 * 获取子岗位
 */
 function getchildstation($station){
	$info=DB::query("select id,name,level from pre_sc_station where parent_id=".$station);
	while($value=DB::fetch($info)){
		$obj[]=$value;
	}
	return $obj;
 }

 /**
  * 获取岗位下推荐课程数
  */
  function getcoursenum($stationid){
	$value=DB::fetch(DB::query("select num from ".DB::table("sc_station")." where id=".$stationid));
	return $value[num];
  }

/**
 * 获取岗位的所有推荐课程
 */
 function getrecommends($station){
	$info=DB::query("select coursecode,coursename from pre_sc_relation where station_id=".$station);
	while($value=DB::fetch($info)){
		$obj[]=$value;
	}
	return $obj;

 }

 /**
 * 获取岗位的所有推荐课程,根据名称
 */
 function getrecommendbyname($name){
	$info=DB::query("select s.id,s.name,r.coursecode,r.coursename from pre_sc_relation r,pre_sc_station s where r.station_id=s.id AND s.name like '%".$name."%' order by s.id");
	while($value=DB::fetch($info)){
		$obj[]=$value;
	}
	return $obj;

 }

/**
 * 根据uid设置关注岗位
 */
 function set_stattion($stationid,$uid){
 	global $_G;
 	if(!$stationid) return false;//未获取到关注岗位id
 	if(!$uid) return false;//未获取到uid
    //查询该用户是否已经设置了关注岗位
	$value=DB::fetch(DB::query("select station_id from ".DB::table("sc_ustation")." where uid=".$uid));
	//如果设置了根据获取到的岗位id进行修改关注岗位
	if($value)
		DB::query("update ".DB::table("sc_ustation")." set status=1,station_id=".$stationid." where uid=".$uid);
	//如果未设置根据获取到的岗位id进行设置关注岗位
	else
		DB::query("insert into pre_sc_ustation(uid,regname,station_id,status) values (".$uid.",'".$realname=user_get_user_name($uid)."',".$stationid.",1)");

	return true;
 }

/**
 *根据条件在sphinx下查询
 */
 function getsphinxdata($str){
 	global $_G;
	require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/api/sphinx/sphinxapi.php';
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "coursemap";
	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	$sc->SetArrayResult ( true );
	$result = $sc->Query ($str, $index );
	return $result[matches];
}

/**
 *根据条件查询符合条件的结果
 */
 function searchd($str){
 	global $_G;
 	if($_G[config]['sphinx']['used']){//sphinx开启
		$result=getsphinxdata($str);//查询符合条件的记录
		for($i=0; $i<count($result); $i++){
			$idarr[]=$result[$i]['id'];
		}
		if(count($result)==0){
			$in=-1;
		}else{
			$in="";
		}
		$query=DB::query("select id,name from ".DB::table("sc_station")." where id in(".$in.implode(',',$idarr).") limit 0,20");
		while($value=DB::fetch($query)){//获取岗位具体内容
			$obj[]=$value;
		}
		return $obj;
 	}else{//sphinx关闭，直接查询数据库
		$query=DB::query("select id,name from ".DB::table("sc_station")." where status=1 AND level=2 AND (name like '%".$str."%' or pname like '%".$str."%') limit 0,20");
		while($value=DB::fetch($query)){
			$obj[]=$value;
		}
		return $obj;
 	}
 }

 /**
  *获取管理员更新后台数据的最新记录
  */
  function getrecord(){
  	$inf=array("status"=>0,"mes"=>"暂时没有更新记录！");
	$record=DB::fetch(DB::query("select * from pre_sc_record order by id desc limit 0,1"));
	if($record){
		$inf[status]=$record[status];
		$inf[mes]=dgmdate($record[dateline])." ".$record[realname]."更新".(($record[status]==0)?"失败":"成功");
	}
	return $inf;
  }

  function updaterecord($record){
	DB::insert('sc_record',$record);
  }

 /**
  * 将汉字转换为拼音
  */
  function spell($str){
  	require_once dirname(dirname(__FILE__)).'/tool/spell/ChineseSpellUtils.php';
	$ChineseSpell=new ChineseSpell();
	$str=iconv('utf-8','gb2312',$str);
	$pinyin=$ChineseSpell->getFullSpell($str,'');
	unset($ChineseSpell);
	return $pinyin;
  }


 /**
  * 上传文件
  */
  function uploadxls(){
	global $_G;
	$upload_path="data/attachment/plugin_coursemap";
	$file_size_max=1024*1024*2; //2M限制文件上传最大容量(bytes)
	$store_dir=$upload_path."/"; // 上传文件的储存位置
   	$accept_overwrite=1;  //是否允许覆盖相同文件  1：允许 0:不允许
	$upload_file=$_FILES['mapxls']['tmp_name'];  //文件被上传后在服务端储存的临时文件名
	$upload_file_name=$_FILES['mapxls']['name']; //文件名
	$upload_file_size=$_FILES['mapxls']['size'];  //文件大小

	//print_r($_FILES['upload']);//此句可以输出上传文件的全部信息
    if($upload_file){
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if($ext != "xls")
   				return array("suc"=>false,"mes"=>"上传的文件类型型错误，请重新上传");

     		//检查文件大小
   			if($upload_file_size > $file_size_max)
   				return array("suc"=>false,"mes"=>"上传的文件超过2M，请重新上传");

          	//检查存储目录，如果存储目录不存在，则创建之
          	if(!is_dir($upload_path))
            		mkdir($upload_path);

   			/*检查读写文件
  			if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
  					showmessage("文件已上传！",$url);
  			*/

   			//复制文件到指定目录
   			$new_file_name="coursemap.xls";//上传过后的保存的名称

   			if(!move_uploaded_file($upload_file,$store_dir.$new_file_name))
   				return array("suc"=>false,"mes"=>"复制文件失败");
   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名)
   			$file_size_kb=$upload_file_size/1024;
   			//文件检查
   			$error=$_FILES['mapxls']['error'];
  			switch($error)
  			{
    			case 0:
    				return array("suc"=>true,"mes"=>"文件上传成功");
    			case 1:
    				return array("suc"=>false,"mes"=>"上传的文件超过了系统配置中upload_max_filesize选项限制的值");
    			case 2:
    				return array("suc"=>false,"mes"=>"上传文件的大小超过了 表单中 MAX_FILE_SIZE 选项指定的值");
          		case 3:
          			return array("suc"=>false,"mes"=>"文件只有部分被上传");
    			case 4:
    				return array("suc"=>false,"mes"=>"没有文件被上传");
  			}
		}else{
			return array("suc"=>false,"mes"=>"请选择文件上传");
		}
		return array("suc"=>false,"mes"=>"文件上传失败");;
  }

/**
 * 下载文件模板
 */
  function download(){
	ob_end_clean();
	$file_dir='data/attachment/plugin_coursemap/';
	$file_name='coursemap_model.xls';
	$file=fopen($file_dir.$file_name,"r");   //   打开文件
	//   输入文件标签
	Header("Content-type:application/octet-stream");
	Header("Accept-Ranges:bytes");
	Header("Accept-Length:".filesize($file_dir.$file_name));
	Header("Content-Disposition:attachment;filename=".$file_name);
	//   输出文件内容
	echo fread($file,filesize($file_dir.$file_name));
	fclose($file);
	exit;
}

?>
