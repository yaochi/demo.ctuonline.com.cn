<?php
/*
 * Created on 2012-4-12
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function getAllextra($type){
	global $_G;
	require_once libfile('function/post');
	$sql='';
	if($type=='indexclass'){
		$classquery=DB::query("select * from ".DB::TABLE("extra_class"));
		while($value=DB::fetch($classquery)){
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value['descr'],200);
		$value[sugestdateline]=dgmdate($value['sugestdateline'],'Y-m-d');
		$list[]=$value;
	}
	}if($type=='indexlec'){
		$lecquery=DB::query("select * from ".DB::TABLE("extra_lecture"));
		while($value=DB::fetch($lecquery)){
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value[descr],200);
		$list[]=$value;
	}
	}if($type=='indexorg'){
    $orgquery=DB::query("select * from ".DB::TABLE("extra_org"));
    while($value=DB::fetch($orgquery)){
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value[descr],200);
		$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
		$list[]=$value;
	}
	}

	return $list;
 }
function getclasslec($classid){
	global $_G;
	$query=DB::query("select * from pre_extra_relationship where classid=$classid");
	while($value=DB::fetch($query)){
		if($value[lecid]){
			if(strlen($value[lecstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[lecstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[lecstars]*2);
				$value[lecstars]=$value[lecstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$value[lecid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$leclist[]=$value;
			}
	}
}
      return $leclist;
}
function getorglec($orgid){
		global $_G;
$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$orgid);
while($value=DB::fetch($query)){
		if($value[lecid]){
			if(strlen($value[lecstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[lecstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[lecstars]*2);
				$value[lecstars]=$value[lecstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$value[lecid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$leclist[]=$value;
			}
		}
	}
	return $leclist;
}
?>
