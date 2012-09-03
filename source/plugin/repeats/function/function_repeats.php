<?
/*
* 马甲函数
*/
//创建马甲时调用
function createrepeats($name,$fid,$uid,$switch){
	global $_G;
	if($name && $fid && $uid && $switch){
	
		$query=DB::query("select * from ".DB::TABLE("forum_repeats")." where fid=".$fid." or name='".$name."'");
		$flag=0;
		//判断专区是否有马甲，以及马甲是否被使用
		while($value=DB::fetch($query)){
			if($value['fid']==$fid){
				$flag=1;
				$result=$value;
			}	
			if($value['name']==$name && $value['fid']!=$fid ){
				$flag=2;
			}
		}
		//如果专区有马甲，则查出马甲可以的使用账号，如果没有则创建，并把管理员加进去
		if($flag==1){
			if(!$result['switch']){
				startrepeats($result[id]);
			}
		}elseif($flag==2){
			DB::query("insert into ".DB::TABLE("forum_repeats")." (fid,name,dateline,switch) values ('".$fid."','".$name.'_'.$fid."','".time()."'),'1'");
			$result[id]=DB::insert_id();
			$result[fid]=$fid;
			$result[name]=$name.'_'.$fid;
			$realname=user_get_user_name($uid);
			DB::query("insert into ".DB::TABLE("repeats_relation")." (repeatsid,fid,uid,realname,dateline) values ('".$result[id]."','".$fid."','".$uid."','".$realname."','".time()."')");
		}else{
			DB::query("insert into ".DB::TABLE("forum_repeats")." (fid,name,dateline,switch) values ('".$fid."','".$name."','".time()."','1')");
			$result[id]=DB::insert_id();
			$result[fid]=$fid;
			$result[name]=$name;
			$realname=user_get_user_name($uid);
			DB::query("insert into ".DB::TABLE("repeats_relation")." (repeatsid,fid,uid,realname,dateline) values ('".$result[id]."','".$fid."','".$uid."','".$realname."','".time()."')");
		}
	}
	return $result;
}

//修改马甲信息
function modifyrepeats($id,$name){
	global $_G;
	if($id && $name){
		$repeats=DB::fetch_first("select * from ".DB::TABLE("forum_repeats")." where name='".$name."'");
		
		$result['success']=1;
		if($repeats){
			if($repeats[id]!=$id){
				$result['success']=0;
				$result['message']='该马甲名已被使用';
			}
		}else{
			DB::query("update ".DB::TABLE("forum_repeats")." set name='".$name."',updateline='".time()."' where id=".$id);
		}
	}
	return $result;
}
//通过id关闭马甲
function closerepeats($id){
	DB::query("update ".DB::TABLE("forum_repeats")." set switch='0',updateline='".time()."' where id=".$id);
}

//通过专区id关闭马甲
function closerepeatsbyfid($fid){
	DB::query("update ".DB::TABLE("forum_repeats")." set switch='0',updateline='".time()."' where fid=".$fid);
}

//开启马甲
function startrepeats($id){
	DB::query("update ".DB::TABLE("forum_repeats")." set switch='1',updateline='".time()."' where id=".$id);
}

//删除马甲
function deleterepeats($id){
	if($id){
		DB::query("delete from ".DB::TABLE("forum_repeats")." where id=".$id);
	}
}

//查看专区马甲(通过马甲id)
function viewrepeats($id){
	if($id){
		$result=DB::fetch_first("select * from ".DB::TABLE("forum_repeats")." where id='".$id."'");
	}
	return $result;
}

//查看专区马甲（通过专区id)
function viewrepeatsbyfid($fid){
	if($fid){
		$result=DB::fetch_first("select * from ".DB::TABLE("forum_repeats")." where fid='".$fid."'");
	}
	return $result;
}

//查看所有马甲信息
function viewallrepeats(){
	$query=DB::query("select * from ".DB::TABLE("forum_repeats"));
	while($value=DB::fetch($query)){
		$list[]=$value;
	}
	return $list;
}

//查找马甲
function searchrepeats($name){
	if($name){
		$query=DB::query("select * from ".DB::TABLE("forum_repeats")." where name like '%".$name."%'");
		while($value=DB::fetch($query)){
			$list[]=$value;

		}
	}
	return $list;
}

//根据用户id返回用户可用的马甲列表
function viewrepeatsbyuid($uid){
	if($uid){
		$query=DB::query("select * from ".DB::TABLE("forum_repeats")." fr,".DB::TABLE("repeats_relation")." rr where fr.id=rr.repeatsid and fr.switch='1' and rr.uid=".$uid);
		while($value=DB::fetch($query)){
			$list[]=$value;
		}
	}
	return $list;
}

//专区马甲的授权（通过id）
function azarepeats($id,$uidarr=array()){
	if($id && $uidarr){
		$query=DB::query("select * from ".DB::TABLE("repeats_relation")." where repeatsid=".$id." and uid in (".implode(',',$uidarr).")");
		while($value=DB::fetch($query)){
			$newuidarr[]=$value['uid'];
			$fid=$value['fid'];
		}
		if(!$fid){
			$repeats=viewrepeats($id);
			$fid=$repeats[fid];
		}
		for($i=0;$i<count($uidarr);$i++){
			if(in_array($uidarr[$i],$newuidarr)){
			}else{
				DB::query("insert into ".DB::TABLE("repeats_relation")." (repeatsid,fid,uid,realname,dateline) values ('".$id."','".$fid."','".$uidarr[$i]."','".user_get_user_name($uidarr[$i])."','".time()."')");
			}
		}
	}
}

//专区马甲的授权（通过专区id）
function azarepeatsbyfid($fid,$uidarr=array()){
	if($fid && $uidarr){
		$repeats=viewrepeatsbyfid($fid);
		$query=DB::query("select * from ".DB::TABLE("repeats_relation")." where repeatsid=".$repeats[id]." and uid in (".implode(',',$uidarr).")");
		while($value=DB::fetch($query)){
			$newuidarr[]=$value['uid'];
		}
		for($i=0;$i<count($uidarr);$i++){
			if(in_array($uidarr[$i],$newuidarr)){
			}else{
				DB::query("insert into ".DB::TABLE("repeats_relation")." (repeatsid,fid,uid,realname,dateline) values ('".$repeats[id]."','".$fid."','".$uidarr[$i]."','".user_get_user_name($uidarr[$i])."','".time()."')");
			}
		}
	}
}

//将取消对用户的授权 
function delazarepeats($idarr=array()){
	if(count($idarr)){
		DB::query("delete from ".DB::TABLE("repeats_relation")." where id in (".implode(',',$idarr).")");
	}
	
}

//将取消对用户的授权 
function delazarepeatsbyuid($idarr=array(),$fid){
	if(count($idarr)){
		DB::query("delete from ".DB::TABLE("repeats_relation")." where uid in (".implode(',',$idarr).") and fid='$fid'");
	}
	
}
//根据马甲id查找授权用户
function viewmemberbyrepeatsid($repeatsid,$start,$perpage){
	if($repeatsid){
		$query=DB::query("select * from ".DB::TABLE("repeats_relation")." where repeatsid=".$repeatsid." order by dateline asc limit $start,$perpage");
		while($value=DB::fetch($query)){
			$list[]=$value;
		}
	}
	return $list;
}

//根据马甲id获取授权用户数
function getrepeatscount($repeatsid){
	if($repeatsid){
		$count=DB::result_first("select count(*) from ".DB::TABLE("repeats_relation")." where repeatsid=".$repeatsid);
	}
	return $count;
}

//更加马甲id获取马甲和专区信息
function getforuminfo($repeatsid){
	if($repeatsid){
		$result=DB::fetch_first("select fr.*,ff.icon from ".DB::TABLE("forum_repeats")." fr,".DB::TABLE("forum_forumfield")." ff where fr.fid=ff.fid and fr.id=".$repeatsid);
	}
	return $result;
}

?>
