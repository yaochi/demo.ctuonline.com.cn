<?php
	global $_G;
	$url="forum.php?mod=group&action=manage&op=manage_shresourcelist&fid=".$_G[fid];

	$upload_path="data/attachment/plugin_shresourcelist";// 上传文件的存储路径
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
     				showmessage("上传的文件内型错误，请重新上传！",$url);

     		//检查文件大小 
   			if($upload_file_size > $file_size_max) 
   					showmessage("上传的文件超过2M，请重新上传！",$url);
   			
          	//检查存储目录，如果存储目录不存在，则创建之 
          	if(!is_dir($upload_path)) 
            		mkdir($upload_path); 
          	
   			//检查读写文件 
  			if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
  					showmessage("文件已上传！",$url);

   			//复制文件到指定目录 
   			$new_file_name=$_G[fid].".xls";//上传过后的保存的名称
   			if(!move_uploaded_file($upload_file,$store_dir.$new_file_name)) 
   					showmessage("复制文件失败！",$url);

   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名) 
   			$info="你上传了文件:".$_FILES['upload']['name'].";文件大小："	.$_FILES['upload']['size']."B。<br/>"	;
   			//文件检查
   			$error=$_FILES['upload']['error']; 
  			switch($error)
  			   { 
    			case 0: 
    				break;
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
?>

