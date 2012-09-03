<?php
/* Function: 根据检索参数，删除单张图片
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
$discuz = & discuz_core::instance();

$discuz->init();
$feedid=$_G['gp_feedid'];
$picid=$_G['gp_picid'];
$uid=$_G['gp_uid'];
$code=$_G['gp_code'];

if($picid&&$uid){
	if($code==md5('esn'.$uid.$picid.$feedid)){
		if($feedid){
			$result=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where uid=".$uid." and feedid=".$feedid);
			$delpic=DB::fetch_first("select * from ".DB::TABLE("home_pic")." where picid=".$picid);
			if($result){
				$picidarr=explode(',',$result[target_ids]);
				$number=count($picidarr);
				if($number>5){
					$changepic=DB::fetch_first("select * from ".DB::TABLE("home_pic")." where picid=".$picidarr[5]);
				}
				if(in_array($picid,$picidarr)){
					for($i=0;$i<count($picidarr);$i++){
						if($picidarr[$i]==$picid){
							if(count($picidarr)>5 && $i<5){
								for($j=$i+1;$j<5;$j++){
									$result["image_".$j]=$result["image_".($j+1)];
									$result["image_".$j."_link"]=$result["image_".($j+1)."_link"];
								}
								$result[image_5]=$changepic["filepath"];
								$result[image_5_link]="home.php?mod=space&uid=$result[uid]&do=album&picid=$changepic[picid]";
							}
						}else{
							$newpicidarr[]=$picidarr[$i];
						}
					}
				}
				$result[body_template]=count($newpicidarr)."张图片";
				$result[target_ids]=implode(",",$newpicidarr);
				
				DB::update("home_feed",$result,array('feedid'=>$feedid));
				//删除数据库
				DB::query("delete from ".DB::TABLE("home_pic")." where picid=".$picid);
				//删除物理图片
				if(strpos($delpic['filepath'],'attachment/album/')){
					$filepath=explode('attachment/album/',$delpic['filepath']);
					$filename=explode('.',$filepath[1]);
					@unlink($_G['setting']['attachdir'].'/album/'.$filepath[1]);
					@unlink($_G['setting']['attachdir'].'album/'.$filename[0].'.thumb.jpg');
				}else{
					@unlink($_G['setting']['attachdir'].'/album/'.$delpic['filepath']);
					if($delpic[thumb]) {
						@unlink($_G['setting']['attachdir'].'album/'.$delpic['filepath'].'.thumb.jpg');
					}
				}
				$res['success']='Y';
			}else{
				$res['success']='N';
				$res['message']='您没有权限！';
			}
		}else{
			$delpic=DB::fetch_first("select * from ".DB::TABLE("home_pic")." where picid=".$picid." and uid=".$uid." and feedid=0");
			if($delpic){
				DB::query("delete from ".DB::TABLE("home_pic")." where picid=".$picid);
				@unlink($_G['setting']['attachdir'].'/album/'.$delpic['filepath']);
				if($delpic[thumb]) {
					@unlink($_G['setting']['attachdir'].'album/'.$delpic['filepath'].'.thumb.jpg');
				}
				$res['success']='Y';
			}else{
				$res['success']='N';
				$res['message']='您没有权限！';
			}
			
		}
	}

}

echo json_encode($res);


?>