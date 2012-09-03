<?php
	/* Note: This thumbnail creation script requires the GD PHP Extension.  
		If GD is not installed correctly PHP does not render this page correctly
		and SWFUpload will get "stuck" never calling uploadSuccess or uploadError
	 */

	// Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
	
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_core.php';
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}

	session_start();

	// Check the upload
	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "invalid upload";
		exit(0);
	}
	
	//文件处理
	$file_id = md5($_FILES["Filedata"]["tmp_name"] + rand()*100000);
	
	$subdir1 = date('Ym');
	$subdir2 = date('d');
	$save_path=dirname(dirname(dirname(dirname(__FILE__)))).'/data/attachment/album/'.$subdir1.'/';
	if (!file_exists($save_path)) {
			mkdir($save_path);
		}
	$save_path=$save_path.$subdir2.'/';
	if (!file_exists($save_path)) {
			mkdir($save_path);
		}
	$subdir=$subdir1.'/'.$subdir2.'/';
	$save_url='data/attachment/album/'.$subdir;
	$file_name = $_FILES['Filedata']['name'];
	/*$temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);*/
	$file_ext=validatefile($_FILES["Filedata"]["tmp_name"]);
	$new_file_name=$file_id.'.'.$file_ext;
	$file_path = $save_path . $new_file_name;
	$tmp_name=$_FILES["Filedata"]["tmp_name"];
	
	if (!file_exists($save_path)) {
			mkdir($save_path);
		}

	

	// Get the image and create a thumbnail
	if($file_ext=='jpg'){
		$img = @imagecreatefromjpeg($_FILES["Filedata"]["tmp_name"]);
	}elseif($file_ext=='png'){
		$img = @imagecreatefrompng($_FILES["Filedata"]["tmp_name"]);
	}elseif($file_ext=='gif'){
		$img = @imagecreatefromgif($_FILES["Filedata"]["tmp_name"]);
	}else{
	}
	if (!$img) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "could not create image handle";
		exit(0);
	}

	//uploadfile
	move_uploaded_file($tmp_name, $file_path);

	$width = imageSX($img);
	$height = imageSY($img);
	$size=92;
	//zip save
	$new_img_save= imagecreatetruecolor($size,$size);
	if($width <= $height){
        $ratio = $width/$size;
        if($ratio>1){
            $ratio = 1/$ratio;
        }
        $new_width_save = $size;
        $new_height_save = intval($height*$ratio);
        $src_x = 0;
        $src_y = intval(($new_height_save-$size)/2);
        $new_img_temp=imagecreatetruecolor($new_width_save,$new_height_save);
    }else{
       $ratio = $height/$size;
       if($ratio>1){
            $ratio = 1/$ratio;
        }
       $new_width_save = intval($width*$ratio);
       $new_height_save = $size;
       $src_x = intval(($new_width_save-$size)/2);
       $src_y = 0;
       $new_img_temp=imagecreatetruecolor($new_width_save,$new_height_save);
    }
	
	 // 复制一份缩小后的图像
    imagecopyresampled($new_img_temp,$img,0,0,0,0,$new_width_save,$new_height_save,$width,$height);
    // 居中裁剪
    imagecopy($new_img_save,$new_img_temp,0,0,$src_x,$src_y,$new_width_save,$new_height_save);

	$new_file_name_save=$file_id.'.thumb.'.$file_ext;
	$file_path_save = $save_path . $new_file_name_save;

	//保存图片 
	if($file_ext=='jpg'){
		imagejpeg($new_img_save,$file_path_save,100);
	}elseif($file_ext=='png'){
		imagepng($new_img_save ,$file_path_save);
	}elseif($file_ext=='gif'){
		imagegif($new_img_save ,$file_path_save);
	}else{
	}
	//存入数据库
	/*$setarr = array(
		'albumid' => 0,
		'uid' => $_POST['uid'],
		'username' =>$_POST['username'],
		'dateline' => time(),
		'filename' =>$file_name,
		'title' => $file_name,
		'type' => $file_ext,
		'size' => $_FILES['Filedata']['size'],
		'filepath' => $save_url,
		'thumb' => 1
	);
	DB::insert('home_pic',$setarr);
	*/
	/*if (!$width || !$height) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "Invalid width or height";
		exit(0);
	}

	// Build the thumbnail
	$target_width = 100;
	$target_height = 100;
	$target_ratio = $target_width / $target_height;

	$img_ratio = $width / $height;

	if ($target_ratio > $img_ratio) {
		$new_height = $target_height;
		$new_width = $img_ratio * $target_height;
	} else {
		$new_height = $target_width / $img_ratio;
		$new_width = $target_width;
	}

	if ($new_height > $target_height) {
		$new_height = $target_height;
	}
	if ($new_width > $target_width) {
		$new_height = $target_width;
	}

	$new_img = ImageCreateTrueColor(100, 100);
	if (!@imagefilledrectangle($new_img, 0, 0, $target_width-1, $target_height-1, 0)) {	// Fill the image black
		header("HTTP/1.1 500 Internal Server Error");
		echo "Could not fill new image";
		exit(0);
	}

	if (!@imagecopyresampled($new_img, $img, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height)) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "Could not resize image";
		exit(0);
	}

	if (!isset($_SESSION["file_info"])) {
		$_SESSION["file_info"] = array();
	}

	// Use a output buffering to load the image into a variable
	ob_start();
	imagejpeg($new_img);
	$imagevariable = ob_get_contents();
	ob_end_clean();

	

	
	$file_id = "FILEID:".$file_id;
	$_SESSION["file_info"][$file_id] = $imagevariable;*/
	$res['file_name']=$new_file_name;
	$res['file_path']=$save_url;
	$res['file_type']=$file_ext;
	$res['file_size']=$_FILES["Filedata"]['size'];

	echo json_encode($res);	// Return the file id to the script
	
?>