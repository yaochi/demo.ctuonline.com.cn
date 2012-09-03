<?php
global $_G;

if (!$_G['uid']) {
//    $template = "common/showmessage_template";
//    showmessage_template("请首先进行登录", "member.php?mod=logging&action=login", array(), array(), 0, $template);
 /*
             * @function: 记录用户认证前跳转
             * @author：陆健青
             * @data: 2010-12-20
             */
             // $this->var['siteroot'] - 站点根目录
			 //dsetcookie('islogin','true');
			 //dsetcookie('validate_ismanager','false');
			  $str_arr_temp = explode($_G['siteroot'],$_SERVER['REQUEST_URI']);
			 // 获取相对路径, ie: forum.php?mod=group&fid=403
			 $referer = $str_arr_temp[1];
			 $str_arr_temp = explode('.php',$referer);
			 // 获取一级目录名
			 $key = $str_arr_temp[0];
			 // 比对config/config_global.php中配置的CONFIG APP
			 switch($_SERVER['HTTP_HOST']){
			 	case $_G['config']['forward']['www']:
					 if(array_key_exists($key,$_G['config']['app']['domain'])){
						 // 如果APP中有该key，ie: forum、group、home,
						 // 则将相对路径放入cookie[re_url]中缓存；
						 $name = "re_url";
						dsetcookie($name,$referer,time()+5*60);
					  }else{
						  $name = "re_url";
						 dsetcookie($name,'portal.php',time()+5*60);
					  }
					 break;
				case $_G['config']['forward']['home']:	 
					 if(array_key_exists($key,$_G['config']['app']['domain'])){
						 // 如果APP中有该key，ie: forum、group、home,
						 // 则将相对路径放入cookie[re_url]中缓存；
						 $name = "re_url";
						dsetcookie($name,$referer,time()+5*60);
					  }else{
						  $name = "re_url";
						 dsetcookie($name,'home.php',time()+5*60);
					  }
					 break;
				default:
					 if(array_key_exists($key,$_G['config']['app']['domain'])){
						 // 如果APP中有该key，ie: forum、group、home,
						 // 则将相对路径放入cookie[re_url]中缓存；
						 $name = "re_url";
						dsetcookie($name,$referer,time()+5*60);
					  }else{
						  $name = "re_url";
						  if($_G['config']['app']['default']){
						  		dsetcookie($name,$_G['config']['app']['default'].'.php',time()+5*60);
						  }else{
						 		dsetcookie($name,'portal.php',time()+5*60);
						 }
					  }
				}
    dheader("Location: member.php?mod=logging&action=login");
}else{
	$allowmem = memory('check');
	$cache_key = 'viewnotice_'.$_G['uid'] ;// key 和用户有关
	$is_from_db = true;
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			$viewnotice=unserialize($cache);	
			$is_from_db = false;
		}
	}

if($is_from_db){  //从DB中查询
	
		$noticeid=DB::result_first("select notice from ".DB::TABLE("member_notice")." where uid=".$_G['uid']);
		if($noticeid){
			$query=DB::query("select * from ".DB::TABLE("forum_announcement")." where endtime>".$_G['timestamp']." and id not in (".$noticeid.")");
		}else{
			$query=DB::query("select * from ".DB::TABLE("forum_announcement")." where endtime>".$_G['timestamp']);
		}
		while($value=DB::fetch($query)){
			$viewnotice[]=$value;
		}

	
	// 放置到cache中
	if($allowmem){
		memory("set", $cache_key, serialize($viewnotice));
		}		
	}
}

?>
