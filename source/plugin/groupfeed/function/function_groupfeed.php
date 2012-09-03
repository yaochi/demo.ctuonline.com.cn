<?php
/* Function: 专区动态
 * Com.:
 * Author: yangyang
 * Date: 2011-5-10
 */

function cutfeed($feed,$titlelength,$bodylength){//对动态进行截取
	
	if($feed[icon]=='poll'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='thread'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='activitys'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='questionary'){
		$replaces=str_replace(strip_tags($feed[title_data][questname]),cutstr(strip_tags($feed[title_data][questname]),$titlelength), $feed[title_data][questname]);
		$feed['title_template'] = str_replace($feed[title_data][questname], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='reward'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
	}elseif($feed[icon]=='resourcelist'){
		$replaces=str_replace(strip_tags($feed[title_data][resourcetitle]),cutstr(strip_tags($feed[title_data][resourcetitle]),$titlelength), $feed[title_data][resourcetitle]);
		$feed['title_template'] = str_replace($feed[title_data][resourcetitle], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='live'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
	}elseif($feed[icon]=='notice'){
		$replaces=str_replace(strip_tags($feed[title_data][noticetitle]),cutstr(strip_tags($feed[title_data][noticetitle]),$titlelength), $feed[title_data][noticetitle]);
		$feed['title_template'] = str_replace($feed[title_data][noticetitle], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='share'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		if(!$replaces){
			$feed['title_template']=$feed['title_template']." <b><a href='".$feed['image_1_link']."'>链接</a></b>";
		}
		$feed['image_1']='';
		$feed['body_template']='';
		$feed['body_general']='';
	}elseif($feed[icon]=='doc'){
		$title_template=explode('上传了[',$feed[body_data][body]);
		$url=substr($title_template[1],9,strcspn($title_template[1],'>')-10);
		$title=substr(strip_tags($title_template[1]),0,strcspn(strip_tags($title_template[1]),']'));
		$title=cutstr($title,$titlelength);
		$feed['title_template'] =$feed['title_template']." <b><a href='".$url."'>$title</a></b>";
		$feed['body_template']='';
	}elseif($feed[icon]=='comment'){
		$replaces=str_replace(strip_tags($feed[title_data][topic]),cutstr(strip_tags($feed[title_data][topic]),$titlelength), $feed[title_data][topic]);
		$feed['title_template'] = str_replace($feed[title_data][topic], $replaces, $feed['title_template']);
	}

	return $feed;
}

function slimcutfeed($feed,$titlelength,$bodylength){//对动态进行截取，无简介
	
	if($feed[icon]=='poll'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		//$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		//$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='thread'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		//$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		//$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='activitys'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		//$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
		//$feed['body_template'] = str_replace($feed[body_data][message], cutstr(strip_tags($feed[body_data][message]),$bodylength), $feed['body_template']);
	}elseif($feed[icon]=='questionary'){
		$replaces=str_replace(strip_tags($feed[title_data][questname]),cutstr(strip_tags($feed[title_data][questname]),$titlelength), $feed[title_data][questname]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] = str_replace($feed[title_data][questname], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='reward'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		//$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
	}elseif($feed[icon]=='resourcelist'){
		$replaces=str_replace(strip_tags($feed[title_data][resourcetitle]),cutstr(strip_tags($feed[title_data][resourcetitle]),$titlelength), $feed[title_data][resourcetitle]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] = str_replace($feed[title_data][resourcetitle], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='live'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] =$feed['title_template']." ".$replaces;
		//$feed['body_template'] = str_replace($feed[body_data][subject], $replaces, $feed['body_template']);
	}elseif($feed[icon]=='notice'){
		$replaces=str_replace(strip_tags($feed[title_data][noticetitle]),cutstr(strip_tags($feed[title_data][noticetitle]),$titlelength), $feed[title_data][noticetitle]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] = str_replace($feed[title_data][noticetitle], $replaces, $feed['title_template']);
	}elseif($feed[icon]=='share'){
		$replaces=str_replace(strip_tags($feed[body_data][subject]),cutstr(strip_tags($feed[body_data][subject]),$titlelength), $feed[body_data][subject]);
		if(!$replaces){
			$feed['title_template']=$feed['title_template']."<a href='".$feed['image_1_link']."'>链接</a>";
		}else{
			$replaces="<b>".$replaces."</b>";
			$feed['title_template'] =$feed['title_template']." ".$replaces;
		}
		$feed['image_1']='';
		//$feed['body_template']='';
		$feed['body_general']='';
	}elseif($feed[icon]=='doc'){
		$title_template=explode('上传了[',$feed[body_data][body]);
		$url=substr($title_template[1],9,strcspn($title_template[1],'>')-10);
		$title=substr(strip_tags($title_template[1]),0,strcspn(strip_tags($title_template[1]),']'));
		$title=cutstr($title,$titlelength);
		$feed['title_template'] =$feed['title_template']." <b><a href='".$url."'>$title</a></b>";
		//$feed['body_template']='';
	}elseif($feed[icon]=='comment'){
		$replaces=str_replace(strip_tags($feed[title_data][topic]),cutstr(strip_tags($feed[title_data][topic]),$titlelength), $feed[title_data][topic]);
		$replaces="<b>".$replaces."</b>";
		$feed['title_template'] = str_replace($feed[title_data][topic], $replaces, $feed['title_template']);
	}
	$feed['body_template']='';
	return $feed;
}


?>
