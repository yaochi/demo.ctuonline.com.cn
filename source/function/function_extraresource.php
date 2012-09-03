<?php
function avgextraclassstar($id,$extraclassstar){
	global $_G;
	$extraclassstar['extraid']=$id;
	$extraclassstar['extratype']='class';
	$extraclassstar['uid']=$_G[uid];
	$extraclassstar['dateline']=time();
	$key = ("extraclass_".$_G[uid]."_".$id);
  	if($_COOKIE["extraclass_star_uid"]!=$key){
		setcookie("extraclass_star_uid", $key, time()+86400);
		DB::insert('extrastar',$extraclassstar);
		$query=DB::query("select * from ".DB::TABLE("extrastar")." where extraid=".$id." and extratype='class'");
		while($value=DB::fetch($query)){
			if($value[totalstars]){
				$totalnumber=$totalnumber+1;
				$totalvalue=$value[totalstars]+$totalvalue;
			}
			if($value[starsone]){
				$starsonenumber=$starsonenumber+1;
				$starsonevalue=$value[starsone]+$starsonevalue;
			}
			if($value[starstwo]){
				$starstwonumber=$starstwonumber+1;
				$starstwovalue=$value[starstwo]+$starstwovalue;
			}
			if($value[starsthree]){
				$starsthreenumber=$starsthreenumber+1;
				$starsthreevalue=$value[starsthree]+$starsthreevalue;
			}
		}
		$totalstars=number_format($totalvalue/$totalnumber,1);
		$starsone=number_format($starsonevalue/$starsonenumber,1);
		$starstwo=number_format($starstwovalue/$starstwonumber,1);
		$starsthree=number_format($starsthreevalue/$starsthreenumber,1);
	
		DB::query("update ".DB::table("extra_class")." set totalstars=".$totalstars.",starsone=".$starsone.",starstwo=".$starstwo.",starsthree=".$starsthree." where id=".$id);	
		DB::query("update ".DB::table("extra_resource")." set totalstars=".$totalstars." where type='class' and resourceid=".$id);	
		DB::query("update ".DB::table("extra_relationship")." set classstars=".$totalstars." where classid=".$id);
	}else{
	}
	
}

function avgextralecstar($id,$extralecstar){
	global $_G;
	$extralecstar['extraid']=$id;
	$extralecstar['extratype']='lec';
	$extralecstar['uid']=$_G[uid];
	$extralecstar['dateline']=time();
	$key = ("extralec_".$_G[uid]."_".$id);
  	if($_COOKIE["extralec_star_uid"]!=$key){
		setcookie("extralec_star_uid", $key, time()+86400);
		DB::insert('extrastar',$extralecstar);
		$query=DB::query("select * from ".DB::TABLE("extrastar")." where extraid=".$id." and extratype='lec'");
		while($value=DB::fetch($query)){
			if($value[totalstars]){
				$totalnumber=$totalnumber+1;
				$totalvalue=$value[totalstars]+$totalvalue;
			}
			if($value[starsone]){
				$starsonenumber=$starsonenumber+1;
				$starsonevalue=$value[starsone]+$starsonevalue;
			}
			if($value[starstwo]){
				$starstwonumber=$starstwonumber+1;
				$starstwovalue=$value[starstwo]+$starstwovalue;
			}
			if($value[starsthree]){
				$starsthreenumber=$starsthreenumber+1;
				$starsthreevalue=$value[starsthree]+$starsthreevalue;
			}
		}
		$totalstars=number_format($totalvalue/$totalnumber,1);
		$starsone=number_format($starsonevalue/$starsonenumber,1);
		$starstwo=number_format($starstwovalue/$starstwonumber,1);
		$starsthree=number_format($starsthreevalue/$starsthreenumber,1);
	
		DB::query("update ".DB::table("extra_lecture")." set totalstars=".$totalstars.",starsone=".$starsone.",starstwo=".$starstwo.",starsthree=".$starsthree." where id=".$id);	
		DB::query("update ".DB::table("extra_resource")." set totalstars=".$totalstars." where type='lec' and resourceid=".$id);	
		DB::query("update ".DB::table("extra_relationship")." set lecstars=".$totalstars." where lecid=".$id);
	}else{
	}
	
}

function avgextraorgstar($id,$extraorgstar){
	global $_G;
	$extraorgstar['extraid']=$id;
	$extraorgstar['extratype']='org';
	$extraorgstar['uid']=$_G[uid];
	$extraorgstar['dateline']=time();
	$key = ("extraorg_".$_G[uid]."_".$id);
  	if($_COOKIE["extraorg_star_uid"]!=$key){
		setcookie("extraorg_star_uid", $key, time()+86400);
		DB::insert('extrastar',$extraorgstar);
		$query=DB::query("select * from ".DB::TABLE("extrastar")." where extraid=".$id." and extratype='org'");
		while($value=DB::fetch($query)){
			if($value[totalstars]){
				$totalnumber=$totalnumber+1;
				$totalvalue=$value[totalstars]+$totalvalue;
			}
			if($value[starsone]){
				$starsonenumber=$starsonenumber+1;
				$starsonevalue=$value[starsone]+$starsonevalue;
			}
			if($value[starstwo]){
				$starstwonumber=$starstwonumber+1;
				$starstwovalue=$value[starstwo]+$starstwovalue;
			}
			if($value[starsthree]){
				$starsthreenumber=$starsthreenumber+1;
				$starsthreevalue=$value[starsthree]+$starsthreevalue;
			}
		}
		$totalstars=number_format($totalvalue/$totalnumber,1);
		$starsone=number_format($starsonevalue/$starsonenumber,1);
		$starstwo=number_format($starstwovalue/$starstwonumber,1);
		$starsthree=number_format($starsthreevalue/$starsthreenumber,1);
	
		DB::query("update ".DB::table("extra_org")." set totalstars=".$totalstars.",starsone=".$starsone.",starstwo=".$starstwo.",starsthree=".$starsthree." where id=".$id);	
		DB::query("update ".DB::table("extra_resource")." set totalstars=".$totalstars." where type='org' and resourceid=".$id);	
		DB::query("update ".DB::table("extra_relationship")." set orgstars=".$totalstars." where orgid=".$id);
	}else{
	}
	
}
?>
