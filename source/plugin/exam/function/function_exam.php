<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/**
 *插入有奖问卷
 *exam信息
 */
 function create($exam){
	DB::insert('exam',$exam);
	$id=DB::insert_id();
	return $id;
 }

/**
 *更新有奖问卷
 *exam信息
 *id-有奖问卷ID
 */
 function update($exam,$id){
 	$where=array("id"=>$id);
	DB::update('exam',$exam,$where);
 }

/**
 *更新有奖问卷答对数
 *id-有奖问卷ID
 */
 function updaterightnum($id,$status){
 	DB::query("update pre_exam set rightnum=rightnum+".$status.",addnum=addnum+1 where id=".$id);
 }

/**
 *获取有奖问卷列表
 */
 function getlist($per){
 	$query=DB::query("select * from pre_exam order by dateline desc limit 0,".$per);
 	if ($query == false) {
		return null;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
 }

 /**
  * 获取最新有奖问卷
  */
  function getmaxid(){
	$value=DB :: fetch(DB::query("select max(id) as maxid from pre_exam"));
	return $value[maxid];
  }

  /**
   * 获取有奖问卷基础信息
   * ID-有奖问卷ID
   */
   function getexam($id){
	$value=DB :: fetch(DB::query("select * from pre_exam where id=".$id));
	return $value;
   }

 /**
  * 删除试题表中有关的记录
  * id-有奖问答ID
  */
  function delete_question($id){
	DB::query("DELETE FROM pre_exam_question  WHERE eid=".$id);
  }

 /**
  * 更新试题表中有关的记录
  * id-有奖问答ID
  */
  function update_question_rightnum($id,$tids){
	DB::query("update pre_exam_question set rightnum=rightnum+1 where eid=".$id." and tid in (".$tids.")");
  }

 /**
  * 获取试题表中有关的记录
  * id-有奖问答ID
  */
  function search_question($id){
	$query=DB::query("SELECT * FROM pre_exam_question  WHERE eid=".$id);
	if ($query == false) {
		return null;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
  }

  /**
   * 插入学员答题记录
   */
   function record_answer($answer){
	DB::insert('exam_answer',$answer);
   }

/**
 *获取答题列表
 */
 function getanswerlist($eid,$per){
 	$query=DB::query("select * from pre_exam_answer where eid=".$eid." order by rightnum desc,dateline limit 0,".$per);
 	if ($query == false) {
		return null;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
 }

  /**
   * 获取我的答题
   * eid-有奖问卷ID,uid-用户uid
   */
   function getmyanswer($eid,$uid,$mem){
		$key="exam_".$eid."_".$uid;
		if($mem==1){
			require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/api/common/memcache_util.php');
			$cache = memory("get", $key);
			if(!empty($cache)){
				return unserialize($cache);
			}
		}
		$my[is]=false;
		$value=DB :: fetch(DB::query("select * from pre_exam_answer where eid=".$eid." AND uid=".$uid));
		$arr= explode(",",$value[answers]);
		for($i=0;$i<count($arr);$i++){
			$new[$i+1]=$arr[$i];
		}
		$my[info]=$value;
		$my[answ]=$new;
		if($value!=null){
			$my[is]=true;
		}
		if(($mem==1)&&$my[is]){//将信息放入memcache中
			memory("set", $key, serialize($my),3600);
   		}
	return $my;
   }

 /**
  *解析选项
  */
  function analy($option){
	$arr1= explode(",,",$option);
	foreach($arr1 as $str){
		$arr[]=array("k"=>substr($str,0,1),"v"=>substr($str,2));
	}
	return $arr;
  }

 /**
  *试卷转换
  */
  function change($id){
	$question=search_question($id);
	if($question==null)	$questions=$answers=null;
    foreach($question as $obj){
		$ques[tid]=$obj[tid];
		$ques[title]=$obj[title];
		$ques[type]=$obj[type];
		$ques[options]=analy($obj[option]);
		$ques[rightnum]=$obj[rightnum];
		$questions[]=$ques;
		$answers[$obj[tid]]=$obj[answer];
    }
    return array("questions"=>$questions,"answers"=>$answers);
  }

  /**
   * 获取试题信息，带memcache
   *id-有奖问卷ID，mem-是否启用memcache,0:否;1:是.
   */
   function getexaminfo($id,$mem){
   		$key="exam_".$id;
   		if($mem==1){//从memcache中获取信息
   		require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/api/common/memcache_util.php');
			$cache = memory("get", $key);
			if(!empty($cache)){
				return unserialize($cache);
			}

   		}
   		$exam=change($id);
   		$exam[base]=getexam($id);

   		if($mem==1){//将信息放入memcache中
			memory("set", $key, serialize($exam), 86400);
   		}
   		return $exam;
   }

 /**
  * 导入试题和答案
  */
  function import($id){
	global $_G;
	delete_question($id);//删除上次保存的试题
	require_once (dirname(dirname(dirname(dirname(__FILE__)))).'/common/phpexcel/reader.php');
	$realpath=dirname(dirname(dirname(dirname(dirname(__FILE__)))));
	$filepath="/data/attachment/plugin_exam/".$id.".xls";
	$realpath.=$filepath;
	$data = new Spreadsheet_Excel_Reader(); //实例化
	$data->setOutputEncoding('gbk');      //编码
	$data->read($realpath);

	$len=$data->sheets[0]['numRows'];
 	for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++)
 	{    //循环输出查询结果，将数据值赋给指定的变量
 		$question=array();
		$question[eid]=$id;
		$question[tid]=$i-2;

		$type_name=$data->sheets[0]['cells'][$i][1];
		$type_name=mb_convert_encoding($type_name,'UTF-8','GB2312');
		if($type_name=='单项选择题') $question[type]=1;
		else $question[type]=2;

		$title=$data->sheets[0]['cells'][$i][2];
		$title=mb_convert_encoding($title,'UTF-8','GB2312');
		$question[title]=$title;

		$option=$data->sheets[0]['cells'][$i][3];
		$option=mb_convert_encoding($option,'UTF-8','GB2312');
		$question[option]=$option;

		$answer=$data->sheets[0]['cells'][$i][4];
		$question[answer]=$answer;

		DB::insert('exam_question',$question);
		unset($question);
 	}
	unset($data);
	return $len-2;
  }

/**
 *调用接口
 */
//function openFileAPIcompany($url) {
//	$opts = array (
//		'http' => array (
//			'method' => 'GET',
//			'timeout' => 300000,
//		)
//	);
//	$context = @ stream_context_create($opts);
//	$result = file_get_contents($url, false, $context);
//	return $result;
//}

/**
 * 调接口根据regname获取用户所在省公司
 */
function getprovince($regname){
	global $_G;
	$FILE_SEARCH_PAGE = "http://" . $_G[config][expert][activeurl];
	$FILE_SEARCH_PAGE .= "/api/user/getuserprovinceorg.do?regname=" . $regname;
	$str1 = openFileAPIcompany($FILE_SEARCH_PAGE);
	$filejson = json_decode($str1, true);
	if($filejson==null)	return "";
	if(strlen($filejson[groupname])>=18){
		if(substr($filejson[groupname],0,12)=='中国电信')
			return substr($filejson[groupname],12);
		return substr($filejson[groupname],0,18);
	}
	return $filejson[groupname];
}


/**
 * 学习积分操作
 *用户信息 uid-用户ID,regname-用户网大帐号,realname-用户真实姓名
 *type 1-学习激励 2-意见箱 3-学习力评估 4-有奖问卷
 *mode 1-新建 2-审核通过 3-证明奖励 4-奖励 5-其他
 *credit 积分>0
 *company 所在公司
 *optionid 如学习激励ID
 *积分表pre_learn_credit,积分记录表pre_learncredit_record
 *调用接口根据regname获取所在省公司getprovince($regname)
 *根据uid获取realname-user_get_user_name
 */
function op_learncredit($uid,$regname,$realname,$type,$mode,$optionid,$credit,$company){
	$info=DB :: fetch(DB :: query("select count(*) as count from pre_learn_credit where username='".$regname."'"));
	if($info[count]==0){
		//查找用户所在省公司
		$province=getprovince($regname);
		if($province!="") $company=$province;

		//查找用户真实姓名
		if($mode==3){
			$realname=user_get_user_name($uid);
		}
		$learn_credit=array('uid' => $uid,'username' => $regname,'realname' => $realname,'totalcredit' => $credit,'exchangecredit' => $credit,'company' => $company,'dateline'=>time());
		DB :: insert("learn_credit", $learn_credit);
	} else {
		$up_sql="update pre_learn_credit set totalcredit=totalcredit+".$credit.",exchangecredit=exchangecredit+".$credit.",dateline=".time()." where username='".$regname."'";
		DB :: query($up_sql);
	}
	//更新积分记录
	$learncredit_record=array('uid' => $uid,'username' => $regname,'type' => $type,'mode' => $mode,'objectid' => $optionid,'credit' => $credit,'dateline'=>time());
	DB :: insert("learncredit_record", $learncredit_record);
}

 /**
  * 上传文件
  * id-有奖问答ID
  */
  function upload($id){
	global $_G;
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=createmenu";
	$upload_path="data/attachment/plugin_exam";
	$file_size_max=1024*1024*1; //1M限制文件上传最大容量(bytes)
	$store_dir=$upload_path."/"; // 上传文件的储存位置
   	$accept_overwrite=1;  //是否允许覆盖相同文件  1：允许 0:不允许

	$upload_file=$_FILES['exam']['tmp_name'];  //文件被上传后在服务端储存的临时文件名
	$upload_file_name=$_FILES['exam']['name']; //文件名
	$upload_file_size=$_FILES['exam']['size'];  //文件大小

	//print_r($_FILES['upload']);//此句可以输出上传文件的全部信息
    if($upload_file)
		{
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if($ext != "xls")
     				showmessage("上传的文件类型型错误，请重新上传！",$url);

     		//检查文件大小
   			if($upload_file_size > $file_size_max)
   					showmessage("上传的文件超过1M，请重新上传！",$url);

          	//检查存储目录，如果存储目录不存在，则创建之
          	if(!is_dir($upload_path))
            		mkdir($upload_path);

   			/*检查读写文件
  			if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
  					showmessage("文件已上传！",$url);
  			*/

   			//复制文件到指定目录
   			$new_file_name=$id.".xls";//上传过后的保存的名称

   			if(!move_uploaded_file($upload_file,$store_dir.$new_file_name))
   				showmessage("复制文件失败！",$url);
   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名)
   			$file_size_kb=$upload_file_size/1024;
   			$info="你上传了文件:".$_FILES['exam']['name'].";文件大小："	.$file_size_kb."KB。<br/>";
   			//文件检查
   			$error=$_FILES['exam']['error'];
  			switch($error)
  			{
    			case 0:
    				return $upload_file_name;
    			case 1:
    				showmessage("上传的文件超过了系统配置中upload_max_filesize选项限制的值。",$url);
    			case 2:
    				showmessage("上传文件的大小超过了 表单中 MAX_FILE_SIZE 选项指定的值。",$url);
          		case 3:
          			showmessage("文件只有部分被上传！",$url);
    			case 4:
    				showmessage("没有文件被上传！",$url);
  			}
		}
		return 0;
  }

/**
 *下载试题模板
 */
 function download($file){
	$file   =   fopen($file, "r ");   //   打开文件
	//   输入文件标签
	Header( "Content-type:   application/octet-stream ");
	Header( "Accept-Ranges:   bytes ");
	Header( "Accept-Length:   ".filesize($file));
	Header( "Content-Disposition:   attachment;   filename=有奖问卷模板.xls");
	//   输出文件内容
	echo   fread($file,filesize($file));
	fclose($file);
}
?>
