<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_image.php 10983 2010-05-19 04:48:12Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function makethumb($srcfile) {
	global $_G;

	if (!file_exists($srcfile)) {
		return '';
	}
	$dstfile = $srcfile.'.thumb.jpg';

	$tow = intval($_G['setting']['thumbwidth']);
	$toh = intval($_G['setting']['thumbheight']);
	if($tow < 60) $tow = 60;
	if($toh < 60) $toh = 60;

	$make_max = 0;
	$maxtow = intval($_G['setting']['maxthumbwidth']);
	$maxtoh = intval($_G['setting']['maxthumbheight']);
	if($maxtow >= 300 && $maxtoh >= 300) {
		$make_max = 1;
	}

	$im = '';
	if($data = getimagesize($srcfile)) {
		if($data[2] == 1) {
			$make_max = 0;
			if(function_exists("imagecreatefromgif")) {
				$im = imagecreatefromgif($srcfile);
			}
		} elseif($data[2] == 2) {
			if(function_exists("imagecreatefromjpeg")) {
				$im = imagecreatefromjpeg($srcfile);
			}
		} elseif($data[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$im = imagecreatefrompng($srcfile);
			}
		}
	}
	if(!$im) return '';

	$srcw = imagesx($im);
	$srch = imagesy($im);

	$towh = $tow/$toh;
	$srcwh = $srcw/$srch;
	if($towh <= $srcwh){
		$ftow = $tow;
		$ftoh = $ftow*($srch/$srcw);

		$fmaxtow = $maxtow;
		$fmaxtoh = $fmaxtow*($srch/$srcw);
	} else {
		$ftoh = $toh;
		$ftow = $ftoh*($srcw/$srch);

		$fmaxtoh = $maxtoh;
		$fmaxtow = $fmaxtoh*($srcw/$srch);
	}
	if($srcw <= $maxtow && $srch <= $maxtoh) {
		$make_max = 0;
	}

	if($srcw > $tow || $srch > $toh) {
		if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$ni = imagecreatetruecolor($ftow, $ftoh)) {
			imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
			if($make_max && @$maxni = imagecreatetruecolor($fmaxtow, $fmaxtoh)) {
				imagecopyresampled($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && @$ni = imagecreate($ftow, $ftoh)) {
			imagecopyresized($ni, $im, 0, 0, 0, 0, $ftow, $ftoh, $srcw, $srch);
			if($make_max && @$maxni = imagecreate($fmaxtow, $fmaxtoh)) {
				imagecopyresized($maxni, $im, 0, 0, 0, 0, $fmaxtow, $fmaxtoh, $srcw, $srch);
			}
		} else {
			return '';
		}
		if(function_exists('imagejpeg')) {
			imagejpeg($ni, $dstfile);
			if($make_max) {
				imagejpeg($maxni, $srcfile);
			}
		} elseif(function_exists('imagepng')) {
			imagepng($ni, $dstfile);
			if($make_max) {
				imagepng($maxni, $srcfile);
			}
		}
		imagedestroy($ni);
		if($make_max) {
			imagedestroy($maxni);
		}
	}
	imagedestroy($im);

	if(!file_exists($dstfile)) {
		return '';
	} else {
		return $dstfile;
	}
}

function makewatermark($src_file) {
	global $_G;

	$watermarkfile = empty($_G['setting']['watermarkfile'])?DISCUZ_ROOT.'./image/watermark.png':$_G['setting']['watermarkfile'];
	if(!file_exists($watermarkfile) || !$water_info = getimagesize($watermarkfile)) {
		return '';
	}
	$water_w = $water_info[0];
	$water_h = $water_info[1];
	$water_im = '';
	switch($water_info[2]) {
		case 1:@$water_im = imagecreatefromgif($watermarkfile);break;
		case 2:@$water_im = imagecreatefromjpeg($watermarkfile);break;
		case 3:@$water_im = imagecreatefrompng($watermarkfile);break;
		default:break;
	}
	if(empty($water_im)) {
		return '';
	}

	if(!file_exists($srcfile) || !$src_info = getimagesize($srcfile)) {
		return '';
	}
	$src_w = $src_info[0];
	$src_h = $src_info[1];
	$src_im = '';
	switch($src_info[2]) {
		case 1:
			$fp = fopen($srcfile, 'rb');
			$filecontent = fread($fp, filesize($srcfile));
			fclose($fp);
			if(strpos($filecontent, 'NETSCAPE2.0') === FALSE) {
				@$src_im = imagecreatefromgif($srcfile);
			}
			break;
		case 2:@$src_im = imagecreatefromjpeg($srcfile);break;
		case 3:@$src_im = imagecreatefrompng($srcfile);break;
		default:break;
	}
	if(empty($src_im)) {
		return '';
	}

	if(($src_w < $water_w + 150) || ($src_h < $water_h + 150)) {
		return '';
	}

	switch($_G['setting']['watermarkpos']) {
		case 1:
		$posx = 0;
		$posy = 0;
		break;
		case 2:
		$posx = $src_w - $water_w;
		$posy = 0;
		break;
		case 3:
		$posx = 0;
		$posy = $src_h - $water_h;
		break;
		case 4:
		$posx = $src_w - $water_w;
		$posy = $src_h - $water_h;
		break;
		default:
		$posx = mt_rand(0, ($src_w - $water_w));
		$posy = mt_rand(0, ($src_h - $water_h));
		break;
	}

	@imagealphablending($src_im, true);
	@imagecopy($src_im, $water_im, $posx, $posy, 0, 0, $water_w, $water_h);
	switch($src_info[2]) {
		case 1:@imagegif($src_im, $srcfile);break;
		case 2:@imagejpeg($src_im, $srcfile);break;
		case 3:@imagepng($src_im, $srcfile);break;
		default:return '';
	}
	@imagedestroy($water_im);
	@imagedestroy($src_im);
}

// 裁剪图片大小
// Add lujianqing 2010-10-7
function  makeImage($src_file,$size=0){
    if(is_int($size) && $size <= 0){
        echo "您输入的size不是大于零的正整数";
        return '';
    }
    // 如果文件不存在或者裁剪为0*0，直接返回
    if (!file_exists($src_file)) {
        echo "原始图片不存在";
        echo $src_file;
	return ;
    }   
    // --输入参数检查End-------------------------------------------------------    
   
    // 获取图像类型
    //$type=exif_imagetype($src_file);
    $image_info = getimagesize($src_file);
    $type = $image_info[2];
    // 支持类型GIF-1, JPEG-2, PNG-3,
    $support_type=array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG );
    if(!in_array($type, $support_type, true)) {
        echo "图片格式不支持! 该方法仅支持 gif, jpg , or png";
        return;
    }
   
    //Load image
    switch($type) {
    case IMAGETYPE_JPEG :
        $src_img=imagecreatefromjpeg($src_file);
        break;
    case IMAGETYPE_PNG :
        $src_img=imagecreatefrompng($src_file);
        break;
    case IMAGETYPE_GIF :
        $src_img=imagecreatefromgif($src_file);
        break;
    default:
        echo "Load image error!";
        return;
    }
     /*
    // 获取图片大小
    $ori_width = imagesx($src_img);
    $ori_height = imagesy($src_img);
    */
    $ori_width = $image_info[0];
    $ori_height = $image_info[1];
    // 定义一个新图像
    $new_img= imagecreatetruecolor($size,$size);
    
    // 根据长、宽较短的一边获取拉伸比例
    // 获取新(临时)长\宽尺寸
    if($ori_width <= $ori_height){
        $ratio = $ori_width/$size;
        if($ratio>1){
            $ratio = 1/$ratio;
        }
        $new_width = $size;
        $new_height = intval($ori_height*$ratio);
        $src_x = 0;
        $src_y = intval(($new_height-$size)/2);
        $new_img_temp=imagecreatetruecolor($new_width,$new_height);
    }else{
       $ratio = $ori_height/$size;
       if($ratio>1){
            $ratio = 1/$ratio;
        }
       $new_width = intval($ori_width*$ratio);
       $new_height = $size;
       $src_x = intval(($new_width-$size)/2);
       $src_y = 0;
       $new_img_temp=imagecreatetruecolor($new_width,$new_height);
    }
    // 复制一份缩小后的图像
    imagecopyresampled($new_img_temp,$src_img,0,0,0,0,$new_width,$new_height,$ori_width,$ori_height);
    // 居中裁剪
    imagecopy($new_img,$new_img_temp,0,0,$src_x,$src_y,$new_width,$new_height);
    
    // ---以下为保存图像---------------------------------------------------------   
    // 新存储对象路径
    $str_temp = explode(".",$src_file);   
    $num = count($str_temp)-1;
    // 获取后缀 .jpg .png .gif
    $suffix = strrchr($src_file,".$str_temp[$num]");
    // 获取去掉后缀的原始图片存储路径
    $str_temp = explode($suffix,$src_file);    
    // 获取新图片存储路径
    $dst_file = $str_temp[0].'_thumb'.$suffix;

    // 存储图片
    switch($type) {
        case IMAGETYPE_JPEG :
            
            imagejpeg($new_img, $dst_file,100); // 存储图像
            break;
        case IMAGETYPE_PNG :
           
            imagepng($new_img,$dst_file,0);
            break;
        case IMAGETYPE_GIF :
            
            imagegif($new_img,$dst_file);
            break;
        default:
            echo "This type is not supported";
            break;
    }
    
    return $dst_file;
    
}


?>