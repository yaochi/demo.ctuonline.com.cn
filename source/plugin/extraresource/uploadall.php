<?php
	global $_G;
	require_once libfile('function/org');
	$url="forum.php?mod=group&action=manage&op=manage_extraresource&fid=".$_G[fid];

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
			$errorarr[0][sheetname]='机构表';
			$errorarr[1][sheetname]='讲师表';
			$errorarr[2][sheetname]='课程表';
			$countsheet0=0;
			$countsheet1=0;
			$countsheet2=0;

			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$countsheet0=$countsheet0+1;
				$arr=array();
				if($data->sheets[0]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[0]['cells'][$i][1],'UTF-8','GBK');
					$res=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where name='".$arr[name]."'");
					if($res){
						$errorarr[0][sheeterrors][$i][]='该机构名已经存在了。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='机构名称不能为空。';
				}
				if($data->sheets[0]['cells'][$i][2]){
					$arr[descr]=mb_convert_encoding($data->sheets[0]['cells'][$i][2],'UTF-8','GBK');
					$arr[sugestdateline]=time();
				}else{
					$errorarr[0][sheeterrors][$i][]='机构简介不能为空。';
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
					$arr[totalstars]=floatval($data->sheets[0]['cells'][$i][4]);
					if($arr[totalstars]>5||!$arr[totalstars]){
						$errorarr[0][sheeterrors][$i][]='总体评分有误。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='总体评分不能为空。';
				}
				if($data->sheets[0]['cells'][$i][5]){
					$arr[starsone]=floatval($data->sheets[0]['cells'][$i][5]);
					if($arr[starsone]>5||!$arr[starsone]){
						$errorarr[0][sheeterrors][$i][]='公司实力有误。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='公司实力不能为空。';
				}
				if($data->sheets[0]['cells'][$i][6]){
					$arr[starstwo]=floatval($data->sheets[0]['cells'][$i][6]);
					if($arr[starstwo]>5||!$arr[starstwo]){
						$errorarr[0][sheeterrors][$i][]='价格水平有误。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='价格水平不能为空。';
				}
				if($data->sheets[0]['cells'][$i][7]){
					$arr[starsthree]=floatval($data->sheets[0]['cells'][$i][7]);
					if($arr[starsthree]>5||!$arr[starsthree]){
						$errorarr[0][sheeterrors][$i][]='服务态度有误。';
					}
				}else{
					$errorarr[0][sheeterrors][$i][]='服务态度不能为空。';
				}
				if($errorarr[0][sheeterrors][$i]){
					$errorarr[0][sheeterror][]='第'.$i.'行  '.implode('',$errorarr[0][sheeterrors][$i]);
					continue;
				}
				DB::insert('extra_org',$arr);

				$orgid = DB::insert_id();
				DB::insert("extra_resource", array("name"=>$arr[name],
				"resourceid"=>$orgid,
				"type"=>'org',
				"totalstars"=>$arr[totalstars],
				"released"=>0,
				"sugestuid"=>$arr[sugestuid],
				"sugestusername"=>$arr[sugestusername],
				"sugestorgid"=>$arr[sugestorgid],
				"sugestorgname"=>$arr[sugestorgname],
				"sugestdateline"=>time(),
				"fid"=>'197'));


				if($arr[starsthree]||$arr[starstwo]||$arr[starsone]||$arr[totalstars]){
					DB::insert('extrastar',array("extraid"=>$orgid,
						"extratype"=>'org',
						"uid"=>$arr[sugestuid],
						"dateline"=>time(),
						"totalstars"=>$arr[totalstars],
						"starsone"=>$arr[starsone],
						"starstwo"=>$arr[starstwo],
						"starsthree"=>$arr[starsthree]));
				}
			}
			for ($i = 2; $i <= $data->sheets[1]['numRows']; $i++) {
				$countsheet1=$countsheet1+1;
				if($data->sheets[1]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[1]['cells'][$i][1],'UTF-8','GBK');
				}else{
					$errorarr[1][sheeterrors][$i][]='讲师姓名不能为空。';
				}
				if($data->sheets[1]['cells'][$i][3]){
					$gender=mb_convert_encoding($data->sheets[1]['cells'][$i][3],'UTF-8','GBK');
					if($gender=='男'){
						$arr[gender]=0;
					}elseif($gender=='女'){
						$arr[gender]=1;
					}else{
						$errorarr[1][sheeterrors][$i][]='讲师性别有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='讲师性别必须填写。';
				}
				if($data->sheets[1]['cells'][$i][4]){
					$relationorgname=mb_convert_encoding($data->sheets[1]['cells'][$i][4],'UTF-8','GBK');
					$orgnamearr=explode(',',$relationorgname);
					$flag=0;
					foreach($orgnamearr as $orgname){
						$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where name='".$orgname."'");
						if(!$org){
							$flag=1;
						}
						$orgarr=array();
						$orgarr[]=$org;
					}
					if($flag=='1'){
						$errorarr[1][sheeterrors][$i][]='填写的机构不存在。';
					}else{
						$arr[relationorgname]=$relationorgname;
					}
				}
				if($data->sheets[1]['cells'][$i][5]){
					$arr[descr]=mb_convert_encoding($data->sheets[1]['cells'][$i][5],'UTF-8','GBK');
				}else{
					$errorarr[1][sheeterrors][$i][]='背景介绍不能为空。';
				}

				if($data->sheets[1]['cells'][$i][8]){
					$rank=mb_convert_encoding($data->sheets[1]['cells'][$i][8],'UTF-8','GBK');
					if($rank=='领导力发展与管理类'){
						$arr[teachdirection]=1;
					}elseif($rank=='营销类'){
						$arr[teachdirection]=2;
					}elseif($rank=='技术类'){
						$arr[teachdirection]=3;
					}else{
						$errorarr[1][sheeterrors][$i][]='授课方向有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课方向必须填写。';
				}
				if($data->sheets[1]['cells'][$i][9]){
					$arr[minfee]=intval($data->sheets[1]['cells'][$i][9]);
					if(!$arr[minfee]){
						$errorarr[1][sheeterrors][$i][]='授课费用下限有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课费用下限必须填写。';
				}
				if($data->sheets[1]['cells'][$i][10]){
					$arr[maxfee]=intval($data->sheets[1]['cells'][$i][10]);
					if(!$arr[maxfee]){
						$errorarr[1][sheeterrors][$i][]='授课费用上限有误。';
					}
					if($arr[maxfee]<$arr[minfee]){
						$errorarr[1][sheeterrors][$i][]='授课费用上限不能小于下限。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课费用上限必须填写。';
				}
				if($data->sheets[1]['cells'][$i][13]){
					$arr[totalstars]=floatval($data->sheets[1]['cells'][$i][13]);
					if($arr[totalstars]>5||!$arr[totalstars]){
						$errorarr[1][sheeterrors][$i][]='总体评分有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='总体评分必须填写。';
				}
				if($data->sheets[1]['cells'][$i][14]){
					$arr[starsone]=floatval($data->sheets[1]['cells'][$i][14]);
					if($arr[starsone]>5||!$arr[starsone]){
						$errorarr[1][sheeterrors][$i][]='授课态度评分有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课态度评分必须填写。';
				}
				if($data->sheets[1]['cells'][$i][15]){
					$arr[starstwo]=floatval($data->sheets[1]['cells'][$i][15]);
					if($arr[starstwo]>5||!$arr[starstwo]){
						$errorarr[1][sheeterrors][$i][]='授课思路评分有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课思路评分必须填写。';
				}
				if($data->sheets[1]['cells'][$i][16]){
					$arr[starsthree]=floatval($data->sheets[1]['cells'][$i][16]);
					if($arr[starsthree]>5||!$arr[starsthree]){
						$errorarr[1][sheeterrors][$i][]='授课技巧评分有误。';
					}
				}else{
					$errorarr[1][sheeterrors][$i][]='授课技巧评分必须填写。';
				}
				if($data->sheets[1]['cells'][$i][17]){
					$uid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$data->sheets[1]['cells'][$i][17]."'");
					if($uid){
						$arr[sugestuid]=$uid;
						$arr[sugestusername]=user_get_user_name($uid);
						$arr[sugestorgid]=get_org_id_by_user($data->sheets[0]['cells'][$i][3]);
						$arr[sugestorgname]=getOrgNameByUser($data->sheets[0]['cells'][$i][3]);
					}else{
						$errorarr[1][sheeterrors][$i][]='填写的推荐人网大账号有错误。';
					}
				}else{
					$arr[sugestuid]='3081504';
					$arr[sugestusername]='贾翠新';
					$arr[sugestorgid]='10642';
					$arr[sugestorgname]='中国电信学院';
				}
				if($errorarr[1][sheeterrors][$i]){
					$errorarr[1][sheeterror][]='第'.$i.'行  '.implode('',$errorarr[1][sheeterrors][$i]);
					continue;
				}
				$arr[isinnerlec]='2';
				$arr[trainingexperince]=mb_convert_encoding($data->sheets[1]['cells'][$i][6],'UTF-8','GBK');
				$arr[trainingtrait]=mb_convert_encoding($data->sheets[1]['cells'][$i][7],'UTF-8','GBK');
				$arr[telephone]=mb_convert_encoding($data->sheets[1]['cells'][$i][11],'UTF-8','GBK');
				$arr[email]=mb_convert_encoding($data->sheets[1]['cells'][$i][12],'UTF-8','GBK');
				$arr[sugestdateline]=time();

				DB::insert('extra_lecture',$arr);
				$lecid=DB::insert_id();

				DB::insert("extra_resource", array("name"=>$arr[name],
				"resourceid"=>$lecid,
				"type"=>'lec',
				"totalstars"=>$arr[totalstars],
				"released"=>0,
				"sugestuid"=>$arr[sugestuid],
				"sugestusername"=>$arr[sugestusername],
				"sugestorgid"=>$arr[sugestorgid],
				"sugestorgname"=>$arr[sugestorgname],
				"sugestdateline"=>time(),
				"fid"=>'197'));

				if($arr[starsthree]||$arr[starstwo]||$arr[starsone]||$arr[totalstars]){
					DB::insert('extrastar',array("extraid"=>$lecid,
						"extratype"=>'lec',
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
							"lecid"=>$lecid,
							"lecname"=>$arr[name],
							"lecstars"=>$arr["totalstars"],
							"dateline"=>time()));
					}
				}
			}
			for ($i = 2; $i <= $data->sheets[2]['numRows']; $i++) {
				$countsheet2=$countsheet2+1;
				$arr=array();
				if($data->sheets[2]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[2]['cells'][$i][1],'UTF-8','GBK');
					$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$arr[name]."'");
					if($res){
						$errorarr[2][sheeterrors][$i][]='该课程已经存在了。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='课程名称不能为空。';
				}
				if($data->sheets[2]['cells'][$i][2]){
					$arr[descr]=mb_convert_encoding($data->sheets[2]['cells'][$i][2],'UTF-8','GBK');
					$arr[sugestdateline]=time();
				}else{
					$errorarr[2][sheeterrors][$i][]='课程简介不能为空。';
				}
				if($data->sheets[2]['cells'][$i][3]){
					$uid=DB::result_first("select uid from ".DB::TABLE("common_member")." where username='".$data->sheets[2]['cells'][$i][3]."'");
					if($uid){
						$arr[sugestuid]=$uid;
						$arr[sugestusername]=user_get_user_name($uid);
						$arr[sugestorgid]=get_org_id_by_user($data->sheets[2]['cells'][$i][3]);
						$arr[sugestorgname]=getOrgNameByUser($data->sheets[2]['cells'][$i][3]);
					}else{
						$errorarr[2][sheeterrors][$i][]='填写的网大账号有错误。';
					}
				}else{
					$arr[sugestuid]='3081504';
					$arr[sugestusername]='贾翠新';
					$arr[sugestorgid]='10642';
					$arr[sugestorgname]='中国电信学院';
				}
				if($data->sheets[2]['cells'][$i][4]){
					$relationlecname=mb_convert_encoding($data->sheets[2]['cells'][$i][4],'UTF-8','GBK');
					$lecnamearr=explode(',',$relationlecname);
					$flag=0;
					foreach($lecnamearr as $lecname){
						$lec=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where name='".$lecname."'");
						if(!$lec){
							$flag=1;
						}
						$lecarr=array();
						$lecarr[]=$lec;
					}
					if($flag=='1'){
						$errorarr[2][sheeterrors][$i][]='填写的讲师不存在。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='请填写培训讲师。';
				}
				if($data->sheets[2]['cells'][$i][5]){
					$relationorgname=mb_convert_encoding($data->sheets[2]['cells'][$i][5],'UTF-8','GBK');
					$orgnamearr=explode(',',$relationorgname);
					$flag=0;
					foreach($orgnamearr as $orgname){
						$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where name='".$orgname."'");
						if(!$org){
							$flag=1;
						}
						$orgarr=array();
						$orgarr[]=$org;
					}
					if($flag=='1'){
						$errorarr[2][sheeterrors][$i][]='填写的机构不存在。';
					}else{
						$arr[relationorgname]=$relationorgname;
					}
				}
				if($data->sheets[2]['cells'][$i][6]){
					$rank=mb_convert_encoding($data->sheets[2]['cells'][$i][6],'UTF-8','GBK');
					if($rank=='管理类'){
						$arr[classification]=1;
					}elseif($rank=='营销类'){
						$arr[classification]=2;
					}elseif($rank=='专业类'){
						$arr[classification]=3;
					}elseif($rank=='通用类'){
						$arr[classification]=4;
					}else{
						$errorarr[2][sheeterrors][$i][]='课程分类有误。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='课程分类必须填写。';
				}
				if($data->sheets[2]['cells'][$i][7]){
					$arr[totalstars]=floatval($data->sheets[2]['cells'][$i][7]);
					if($arr[totalstars]>5||!$arr[totalstars]){
						$errorarr[2][sheeterrors][$i][]='总体评分有误。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='总体评分必须填写。';
				}
				if($data->sheets[2]['cells'][$i][8]){
					$arr[starsone]=floatval($data->sheets[2]['cells'][$i][8]);
					if($arr[starsone]>5||!$arr[starsone]){
						$errorarr[2][sheeterrors][$i][]='前瞻性评分有误。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='前瞻性评分必须填写。';
				}
				if($data->sheets[2]['cells'][$i][9]){
					$arr[starstwo]=floatval($data->sheets[2]['cells'][$i][9]);
					if($arr[starstwo]>5||!$arr[starstwo]){
						$errorarr[2][sheeterrors][$i][]='教学设计评分有误。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='教学设计评分必须填写。';
				}
				if($data->sheets[2]['cells'][$i][10]){
					$arr[starsthree]=floatval($data->sheets[2]['cells'][$i][10]);
					if($arr[starsthree]>5||!$arr[starsthree]){
						$errorarr[2][sheeterrors][$i][]='内容实用评分有误。';
					}
				}else{
					$errorarr[2][sheeterrors][$i][]='内容实用评分必须填写。';
				}

				if($errorarr[2][sheeterrors][$i]){
					$errorarr[2][sheeterror][]='第'.$i.'行  '.implode('',$errorarr[2][sheeterrors][$i]);
					continue;
				}
				DB::insert('extra_class',$arr);

				$classid = DB::insert_id();

				DB::insert("extra_resource", array("name"=>$arr[name],
					"resourceid"=>$classid,
					"type"=>'class',
					"totalstars"=>$arr[totalstars],
					"released"=>0,
					"sugestuid"=>$arr[sugestuid],
					"sugestusername"=>$arr[sugestusername],
					"sugestorgid"=>$arr[sugestorgid],
					"sugestorgname"=>$arr[sugestorgname],
					"sugestdateline"=>time(),
					"fid"=>'197'));
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
			$countsheet1error=count($errorarr[1][sheeterror]);
			$countsheet2error=count($errorarr[2][sheeterror]);
			$countallerror=$countsheet0error+$countsheet1error+$countsheet2error;
			$countall=$countsheet0+$countsheet1+$countsheet2;
				$error[summary]='已处理 '.$countall.' 条记录，其中导入成功 '.($countall-$countallerror).' 条，导入失败 '.$countallerror.' 条';
				$errorarr[0][lisummary]=$countsheet0.' 条记录  导入成功 '.($countsheet0-$countsheet0error).' 条  导入失败 '.$countsheet0error.' 条';
				$errorarr[1][lisummary]=$countsheet1.' 条记录  导入成功 '.($countsheet1-$countsheet1error).' 条  导入失败 '.$countsheet1error.' 条';
				$errorarr[2][lisummary]=$countsheet2.' 条记录  导入成功 '.($countsheet2-$countsheet2error).' 条  导入失败 '.$countsheet2error.' 条';

			$error[errorarr]=$errorarr;
			$error[url]=$url;
			if($countsheet0error||$countsheet1error||$countsheet2error){
				include template('common/importerror');
			}else{
				showmessage('全部数据导入成功!',$url);
			}

		}
?>

