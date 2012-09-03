<?php
	/* Note: This thumbnail creation script requires the GD PHP Extension.  
		If GD is not installed correctly PHP does not render this page correctly
		and SWFUpload will get "stuck" never calling uploadSuccess or uploadError
	 */

	// Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
	
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();

	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}

	session_start();
	if(file_exists($_POST["Filedata_path"])){
	
		$file1 = $_POST["Filedata_path"];
		//$content = file_get_contents($file1);
		// Check the upload
		/*if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "invalid upload";
			exit(0);
		}*/
		//文件处理
		$file_id = md5($_POST["Filename"] + rand()*100000);
		$save_path=dirname(dirname(dirname(dirname(__FILE__)))).'/data/attachment/flv/';
		//$file_name = $_POST["Filename"];

		/*$temp_arr = explode(".", $_POST["Filename"]);
		$file_ext = array_pop($temp_arr);
		$file_ext = trim($file_ext);
		$file_ext = strtolower($file_ext);*/

		$file_ext=validatefile($file1);
		$new_file_name=$file_id.'.'.$file_ext;
		$file_path = $save_path . $new_file_name;
		//$tmp_name=$file1;
		$file_url='/data/attachment/flv/'.$new_file_name;
		
		$uid=$_POST["uid"];
		$mediaarr=array('uid'=>$uid,"file_name"=>$file_name,"dateline"=>time(),"file_url"=>$file_url);
		DB::insert("home_media",$mediaarr);

		if (!isset($_SESSION["file_info"])) {
			$_SESSION["file_info"] = array();
		}

		rename($file1,$file_path);
		//写入
		/*$fp = fopen($file_path, 'w');
		fwrite($fp, $content);
		fclose($fp);*/
		
		//删除原来的
		//unlink($file1);
		
		echo $_G['config']['media']['url'].$file_url;
		//echo $file_path;	// Return the file id to the script
	}else{
		echo "error";
	}
	
?>