<?php
	global $_G;
	require_once libfile('function/org');
	$url="forum.php?mod=group&action=manage&op=manage_extraclass&fid=".$_G[fid];

	$upload_path="data/attachment/plugin_extraresource";// 上传文件的存储路径
	$file_size_max=1024*1024*2; //1M限制文件上传最大容量(bytes)
	$store_dir=$upload_path."/"; // 上传文件的储存位置 
   	$accept_overwrite=0;  //是否允许覆盖相同文件  1：允许 0:不允许
   	
	//POST中name= "upload";
	$upload_file=$_FILES['upload']['tmp_name'];  //文件被上传后在服务端储存的临时文件名 
	$upload_file_name=$_FILES['upload']['name']; //文件名 
	/*if($upload_file_name!=$_G[fid].".xls"){
		showmessage("上传的文件名不符合规定，请重新上传文件".$_G[fid].".xls"."！",$url);
	}*/
	$upload_file_size=$_FILES['upload']['size'];  //文件大小 
	//print_r($_FILES['upload']);//此句可以输出上传文件的全部信息 
    if($upload_file) 
		{ 
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if($ext != "xls") 
     				showmessage("上传的文件类型错误，请重新上传！",$url);

     		//检查文件大小 
   			if($upload_file_size > $file_size_max) 
   					showmessage("上传的文件超过2M，请重新上传！",$url);
			require_once (dirname(dirname(__FILE__)).'/shlecturer/reader.php');
			$data = new Spreadsheet_Excel_Reader(); //实例化   
			$data->setOutputEncoding('gbk');      //编码   
			$data->read($upload_file);	
 
			$error=array();
			$errorarr[0][sheetname]='外部课程表'; 
			$countsheet0=0;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$countsheet0=$countsheet0+1;
				$arr=array();
				if($data->sheets[0]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[0]['cells'][$i][1],'UTF-8','GBK');
					$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$arr[name]."'");
					if($res){
						$errorarr[0][sheeterrors][$i][]='该课程已经存在了。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='课程名称不能为空。';
				}
				if($data->sheets[0]['cells'][$i][2]){
					$arr[descr]=mb_convert_encoding($data->sheets[0]['cells'][$i][2],'UTF-8','GBK');
					$arr[sugestdateline]=time();
				}else{
					$errorarr[0][sheeterrors][$i][]='课程简介不能为空。';
				}
				if($data->sheets[0]['cells'][$i][3]){
					$uid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$data->sheets[0]['cells'][$i][3]."'");
					if($uid){
						$arr[sugestuid]=$uid;
						$arr[sugestusername]=user_get_user_name($uid);
						$arr[sugestorgid]=get_org_id_by_user($data->sheets[0]['cells'][$i][3]);
						$arr[sugestorgname]=getOrgNameByUser($data->sheets[0]['cells'][$i][3]);
					}else{
						$errorarr[0][sheeterrors][$i][]='填写的网大账号有错误。';
					}
				}else{
					$arr[sugestuid]='3081504';
					$arr[sugestusername]='贾翠新';
					$arr[sugestorgid]='10642';
					$arr[sugestorgname]='中国电信学院';
				}
				if($data->sheets[0]['cells'][$i][4]){
					$relationlecname=mb_convert_encoding($data->sheets[0]['cells'][$i][4],'UTF-8','GBK');
					$lecnamearr=explode(',',$relationlecname);
					$flag=0;
					foreach($lecnamearr as $lecname){
						$lec=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where name='".$lecname."' and released=1");
						if(!$lec){
							$flag=1;
						}
						$lecarr[]=$lec;
					}
					if($flag=='1'){
						$errorarr[0][sheeterrors][$i][]='填写的讲师不存在。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='请填写培训讲师。';
				}
				if($data->sheets[0]['cells'][$i][5]){
					$relationorgname=mb_convert_encoding($data->sheets[0]['cells'][$i][5],'UTF-8','GBK');
					$orgnamearr=explode(',',$relationorgname);
					$flag=0;
					foreach($orgnamearr as $orgname){
						$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where name='".$orgname."' and released=1");
						if(!$org){
							$flag=1;
						}
						$orgarr[]=$org;
					}
					if($flag=='1'){
						$errorarr[0][sheeterrors][$i][]='填写的机构不存在。';
					}else{
						$arr[relationorgname]=$relationorgname;
					}
				}
				if($data->sheets[0]['cells'][$i][6]){
					$arr[totalstars]=floatval($data->sheets[0]['cells'][$i][6]);
					if($arr[totalstars]>5||!$arr[totalstars]){
						$errorarr[0][sheeterrors][$i][]='总体评分有误。';
					}
				}
				if($data->sheets[0]['cells'][$i][7]){
					$arr[starsone]=floatval($data->sheets[0]['cells'][$i][7]);
					if($arr[starsone]>5||!$arr[starsone]){
						$errorarr[0][sheeterrors][$i][]='内容实用评分有误。';
					}
				}
				if($data->sheets[0]['cells'][$i][8]){
					$arr[starstwo]=floatval($data->sheets[0]['cells'][$i][8]);
					if($arr[starstwo]>5||!$arr[starstwo]){
						$errorarr[0][sheeterrors][$i][]='教学设计评分有误。';
					}
				}
				if($data->sheets[0]['cells'][$i][9]){
					$arr[starsthree]=floatval($data->sheets[0]['cells'][$i][9]);
					if($arr[starsthree]>5||!$arr[starsthree]){
						$errorarr[0][sheeterrors][$i][]='内容容量评分有误。';
					}
				}
				if($errorarr[0][sheeterrors][$i]){
					$errorarr[0][sheeterror][]='第'.$i.'行  '.implode('',$errorarr[0][sheeterrors][$i]);
					continue;
				}
				DB::insert('extra_class',$arr);
				
				$classid = DB::insert_id();
				if($arr[starsthree]||$arr[starstwo]||$arr[starsone]||$arr[totalstars]){
					DB::insert('extrastar',array("extraid"=>$classid,
						"extratype"=>'class',
						"uid"=>$arr[sugestuid],
						"dateline"=>time(),
						"totalstars"=>$arr[totalstars],
						"starsone"=>$arr[starsone],
						"starstwo"=>$arr[starstwo],
						"starsthree"=>$arr[starsthree]));
				}
				if($orgarr){
					foreach($orgarr as $org){
						DB::insert("extra_relationship",array("orgid"=>$org[id],
							"orgname"=>$org[name],
							"orglog"=>$org[uploadfile],
							"orgstars"=>$org["totalstars"],
							"classid"=>$classid,
							"classname"=>$arr[name],
							"classstars"=>$arr["totalstars"],
							"dateline"=>time()));
					}
				}
				if($lecarr){
					foreach($lecarr as $lec){
						DB::insert("extra_relationship",array("classid"=>$classid,
							"classname"=>$arr[name],
							"classstars"=>$arr["totalstars"],
							"lecid"=>$lec[id],
							"lecname"=>$lec[name],
							"lecorg"=>$lec[relationorgname],
							"lecstars"=>$lec["totalstars"],
							"dateline"=>time()));
					}
				}
			}
			$countsheet0error=count($errorarr[0][sheeterror]);
			$countallerror=$countsheet0error;
			$countall=$countsheet0;
				$error[summary]='已处理 '.$countall.' 条记录，其中导入成功 '.($countall-$countallerror).' 条，导入失败 '.$countallerror.' 条';
				$errorarr[0][lisummary]=$countsheet0.' 条记录  导入成功 '.($countsheet0-$countsheet0error).' 条  导入失败 '.$countsheet0error.' 条';
			
			$error[errorarr]=$errorarr;
			$error[url]=$url;
			if($countsheet0error){
				include template('common/importerror');
			}else{
				showmessage('全部数据导入成功!',$url);
			}
			
		} 	 
?>

