<?php
	global $_G;
	$url="forum.php?mod=group&action=manage&op=manage_shlecturer&fid=".$_G[fid];

	$upload_path="data/attachment/plugin_shlecturer";// 上传文件的存储路径
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
			require_once (dirname(__FILE__).'/reader.php');
			$data = new Spreadsheet_Excel_Reader(); //实例化   
			$data->setOutputEncoding('gbk');      //编码   
			$data->read($upload_file);	
   			$query = DB::query("TRUNCATE TABLE ".DB::table("shlecture"));
			$query = DB::query("TRUNCATE TABLE ".DB::table("shlecture_direct"));
			$query = DB::query("TRUNCATE TABLE ".DB::table("shlecture_stars"));
			$error=array();
			$errorarr[0][sheetname]='内部讲师表'; 
			$errorarr[1][sheetname]='外部讲师表'; 
			$countsheet0=0;
			$countsheet1=0;
			for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
				$countsheet0=$countsheet0+1;
				$errorarr[0][sheeterror]=array();
				if($data->sheets[0]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[0]['cells'][$i][1],'UTF-8','GBK');
				}else{
					$errorarr[0][sheeterror][$i][]='第 '.$i.' 行，姓名不能为空。';
				}
				if($data->sheets[0]['cells'][$i][2]){
					$arr[tempusername]=$data->sheets[0]['cells'][$i][2];
				}else{
					$errorarr[0][sheeterror][$i][]='第 '.$i.' 行，讲师账号必须填写。';
				}
				if($data->sheets[0]['cells'][$i][3]){
					$gender=mb_convert_encoding($data->sheets[0]['cells'][$i][3],'UTF-8','GBK');
					if($gender=='男'){
						$arr[gender]=1;
					}elseif($gender=='女'){
						$arr[gender]=2;
					}elseif($gender=='保密'){
						$arr[gender]=0;
					}else{
						$errorarr[0][sheeterror][$i][]='第 '.$i.' 行，讲师性别有误。';
					}
				}else{
					$arr[gender]=0;
				}
				if($data->sheets[0]['cells'][$i][6]){
					$arr[stars]=floatval($data->sheets[0]['cells'][$i][6]);
					if($arr[stars]>5||!$arr[stars]){
						$errorarr[0][sheeterror][$i][]='第 '.$i.' 行，星级类型有误。';
					}
				}
				if($data->sheets[0]['cells'][$i][9]){
					$rank=mb_convert_encoding($data->sheets[0]['cells'][$i][9],'UTF-8','GBK');
					if($rank=='集团级'){
						$arr[rank]=1;
					}elseif($rank=='公司级'){
						$arr[rank]=2;
					}elseif($rank=='直属单位级'){
						$arr[rank]=3;
					}elseif($rank=='其他'){
						$arr[rank]=4;
					}else{
						$errorarr[0][sheeterror][$i][]='第 '.$i.' 行，讲师级别有误。';
					}
				}else{
					$arr[rank]=2;
				}
				if($errorarr[0][sheeterror][$i]){
					$errorarr[0][sheeterror][$i]=implode('',$errorarr[0][sheeterror][$i]);
					continue;
				}
				$arr[isinnerlec]='1';
				$arr[orgname]=mb_convert_encoding($data->sheets[0]['cells'][$i][4],'UTF-8','GBK');
				$arr[bginfo]=mb_convert_encoding($data->sheets[0]['cells'][$i][5],'UTF-8','GBK');
				$arr[perspecial]=mb_convert_encoding($data->sheets[0]['cells'][$i][7],'UTF-8','GBK');
				$arr[coursexperience]=mb_convert_encoding($data->sheets[0]['cells'][$i][8],'UTF-8','GBK');
				$arr[tel]=mb_convert_encoding($data->sheets[0]['cells'][$i][10],'UTF-8','GBK');
				$arr[email]=mb_convert_encoding($data->sheets[0]['cells'][$i][11],'UTF-8','GBK');
				DB::insert('shlecture',$arr);
				$id=DB::insert_id();
				if($data->sheets[0]['cells'][$i][12]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][12],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'1','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][13]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][13],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'2','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][14]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][14],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'3','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][15]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][15],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'4','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][16]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][16],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'5','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][17]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][17],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'6','".$courses."')");
				}
				if($data->sheets[0]['cells'][$i][18]){
					$courses=mb_convert_encoding($data->sheets[0]['cells'][$i][18],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'7','".$courses."')");
				}
				
			}
			for ($i = 2; $i <= $data->sheets[1]['numRows']; $i++) {
				$countsheet1=$countsheet1+1;
				$errorarr[1][sheeterror]=array();
				if($data->sheets[1]['cells'][$i][1]){
					$arr[name]=mb_convert_encoding($data->sheets[1]['cells'][$i][1],'UTF-8','GBK');
				}else{
					$errorarr[1][sheeterror][$i][]='第 '.$i.' 行，姓名不能为空。';
				}
				if($data->sheets[1]['cells'][$i][3]){
					$gender=mb_convert_encoding($data->sheets[1]['cells'][$i][3],'UTF-8','GBK');
					if($gender=='男'){
						$arr[gender]=1;
					}elseif($gender=='女'){
						$arr[gender]=2;
					}elseif($gender=='保密'){
						$arr[gender]=0;
					}else{
						$errorarr[1][sheeterror][$i][]='第 '.$i.' 行，讲师性别有误。';
					}
				}else{
					$arr[gender]=0;
				}
				if($data->sheets[1]['cells'][$i][6]){
					$arr[stars]=floatval($data->sheets[1]['cells'][$i][6]);
					if($arr[stars]>5||!$arr[stars]){
						$errorarr[1][sheeterror][$i][]='第 '.$i.' 行，星级类型有误。';
					}
				}
				if($data->sheets[1]['cells'][$i][9]){
					$rank=mb_convert_encoding($data->sheets[1]['cells'][$i][9],'UTF-8','GBK');
					if($rank=='集团级'){
						$arr[rank]=1;
					}elseif($rank=='公司级'){
						$arr[rank]=2;
					}elseif($rank=='直属单位级'){
						$arr[rank]=3;
					}elseif($rank=='其他'){
						$arr[rank]=4;
					}else{
						$errorarr[1][sheeterror][$i][]='第 '.$i.' 行，讲师级别有误。';
					}
				}else{
					$arr[rank]=2;
				}
				if($errorarr[1][sheeterror][$i]){
					$errorarr[1][sheeterror][$i]=implode('',$errorarr[1][sheeterror][$i]);
					continue;
				}
				$arr[isinnerlec]='2';
				$arr[orgname]=mb_convert_encoding($data->sheets[1]['cells'][$i][4],'UTF-8','GBK');
				$arr[bginfo]=mb_convert_encoding($data->sheets[1]['cells'][$i][5],'UTF-8','GBK');
				$arr[perspecial]=mb_convert_encoding($data->sheets[1]['cells'][$i][7],'UTF-8','GBK');
				$arr[coursexperience]=mb_convert_encoding($data->sheets[1]['cells'][$i][8],'UTF-8','GBK');
				$arr[tel]=mb_convert_encoding($data->sheets[1]['cells'][$i][10],'UTF-8','GBK');
				$arr[email]=mb_convert_encoding($data->sheets[1]['cells'][$i][11],'UTF-8','GBK');
				
				DB::insert('shlecture',$arr);
				$id=DB::insert_id();
				if($data->sheets[1]['cells'][$i][12]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][12],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'1','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][13]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][13],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'2','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][14]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][14],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'3','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][15]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][15],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'4','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][16]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][16],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'5','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][17]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][17],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'6','".$courses."')");
				}
				if($data->sheets[1]['cells'][$i][18]){
					$courses=mb_convert_encoding($data->sheets[1]['cells'][$i][18],'UTF-8','GBK');
					DB::query("INSERT into ".DB::TABLE("shlecture_direct")."(lecid,teachdirection,courses) values ($id,'7','".$courses."')");
				}
				
			}
			$countsheet0error=count($errorarr[0][sheeterror]);
			$countsheet1error=count($errorarr[1][sheeterror]);
			$countallerror=$countsheet0error+$countsheet1error;
			$countall=$countsheet0+$countsheet1;
				$error[summary]='已处理'.$countall.'条记录，其中导入成功'.($countall-$countallerror).'条，导入失败'.$countallerror.'条';
				$errorarr[0][lisummary]=$countsheet0.'条记录  导入成功'.($countsheet0-$countsheet0error).'条  导入失败'.$countsheet0error.'条';
				$errorarr[1][lisummary]=$countsheet1.'条记录  导入成功'.($countsheet1-$countsheet1error).'条  导入失败'.$countsheet1error.'条';
			
			$error[errorarr]=$errorarr;
			$error[url]=$url;
			if($countsheet0error||$countsheet1error){
				include template('common/importerror');
			}else{
				showmessage('全部数据导入成功!',$url);
			}
			
		} 	 
?>

