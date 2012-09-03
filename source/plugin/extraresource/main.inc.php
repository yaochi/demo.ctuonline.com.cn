<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");


function index(){//课程列表
	global $_G;
	require_once libfile('function/post');
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}
	//end

	$addsql = "";
	$addurl = "";

	$role=$_GET['role']?$_GET['role']:'desc';
	if($role=='desc'){
		$sql=" sugestdateline desc";
		$addurl="&role=desc";
	}
	if($role=='asc'){
		$sql=" sugestdateline asc";
		$addurl="&role=asc";
	}
	if($role=='sugestdateline'){
		$sql=" sugestdateline desc";
		$addurl="&role=sugestdateline";
	}
	if($role=='totalstars'){
		$sql=" totalstars desc";
		$addurl="&role=totalstars";
	}if($role=='classname'){
		$sql=" name DESC";
		$addurl="&role=classname";
	}if ($role=='ification') {
		$sql=" classification DESC";
		$addurl="&role=ification";
	}
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	if($_G['forum']['ismoderator']){
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_class"));
		$classquery=DB::query("select * from ".DB::TABLE("extra_class")." order by $sql LIMIT $start,$perpage");
	}else{
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where released='1' or sugestuid='".$_G[uid]."'");
		$classquery=DB::query("select * from ".DB::TABLE("extra_class")." where released='1' or sugestuid='".$_G[uid]."' order by $sql LIMIT $start,$perpage");
	}
	while($value=DB::fetch($classquery)){
		$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=1 and uid='".$_G[uid]."'");
		$comaryarr=DB::fetch($comaryqury);
		$value[status]=$comaryarr[status];
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value[descr],200);
		$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
		$classlist[]=$value;
	}
	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&order=".$addurl;
	if($count){
		$multipage = multi($count, $perpage, $page, $url);
	}
	return array("multipage"=>$multipage,"classlist"=>$classlist,"role"=>$role);

}

function indexorg(){//机构列表
	global $_G;
	require_once libfile('function/post');
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}
	//end
	$addsql = "";
	$addurl = "";

	$order=$_GET['order']?$_GET['order']:'sugestdateline';
	$role=$_GET['role']?$_GET['role']:'desc';
	if($role=='desc'){
		$addsql=" sugestdateline desc";
		$addurl="&role=desc";
	}
	if($role=='asc'){
		$addsql=" sugestdateline asc";
		$addurl="&role=asc";
	}
	if($role=='sugestdateline'){
		$addsql=" sugestdateline desc";
		$addurl="&role=sugestdateline";
	}
	if($role=='totalstars'){
		$addsql=" totalstars desc";
		$addurl="&role=totalstars";
	}if($role=='orgname'){
		$addsql=" name DESC";
		$addurl="&role=orgname";
	}if ($role=='ification') {
		$addsql=" sugestorgname DESC";
		$addurl="&role=ification";
	}

	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;
	if($_G['forum']['ismoderator']){
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_org"));
		$orgquery=DB::query("select * from ".DB::TABLE("extra_org")." order by $addsql LIMIT $start,$perpage");
	}else{
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where released='1' or sugestuid='".$_G[uid]."'");
		$orgquery=DB::query("select * from ".DB::TABLE("extra_org")." where released='1' or sugestuid='".$_G[uid]."' order by $addsql LIMIT $start,$perpage");
	}
	while($value=DB::fetch($orgquery)){
		$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=3 and uid='".$_G[uid]."'");
		$comaryarr=DB::fetch($comaryqury);
		$value[status]=$comaryarr[status];
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value[descr],200);
		$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
		$orglist[]=$value;

	}
	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=indexorg&order=".$addurl;
	if($count){
		$multipage = multi($count, $perpage, $page, $url);
	}
	return array("multipage"=>$multipage,"orglist"=>$orglist,"role"=>$role,"order"=>$order);

}

function indexlec(){//讲师列表
	global $_G;
	require_once libfile('function/post');
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}
	//end
	$addsql = "";
	$addurl = "";

	$role=$_GET['role']?$_GET['role']:'desc';
	if($role=='desc'){
		$sql=" sugestdateline desc";
		$addurl="&role=desc";
	}
	if($role=='asc'){
		$sql=" sugestdateline asc";
		$addurl="&role=asc";
	}
	if($role=='sugestdateline'){
		$sql=" sugestdateline desc";
		$addurl="&role=sugestdateline";
	}
	if($role=='totalstars'){
		$sql=" totalstars desc";
		$addurl="&role=totalstars";
	}if($role=='lec'){
		$sql=" name DESC";
		$addurl="&role=lec";
	}if ($role=='ification') {
		$sql=" teachdirection DESC";
		$addurl="&role=ification";
	}
	//	分页
    $perpage = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $perpage;

	if($_G['forum']['ismoderator']){
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture"));
		$lecquery=DB::query("select * from ".DB::TABLE("extra_lecture")." order by  $sql LIMIT $start,$perpage");
	}else{
		$count=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture")." where released='1' or sugestuid='".$_G[uid]."'");
		$lecquery=DB::query("select * from ".DB::TABLE("extra_lecture")." where released='1' or sugestuid='".$_G[uid]."' order by $sql LIMIT $start,$perpage");
	}

	while($value=DB::fetch($lecquery)){
		$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=2 and uid='".$_G[uid]."'");
		$comaryarr=DB::fetch($comaryqury);
		$value[status]=$comaryarr[status];
		if(strlen($value[totalstars])>1){
			$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
		}else{
			$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
			$value[totalstars]=$value[totalstars].'.0';
		}
		$value[descr]=messagecutstr($value[descr],200);
		$leclist[]=$value;
	}

	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=indexlec".$addurl;

	if($count){
		$multipage = multi($count, $perpage, $page, $url);
	}
	return array("multipage"=>$multipage,"leclist"=>$leclist,"role"=>$role);
}

function search(){
		global $_G;

		$groupsearch=$_G[gp_groupsearch];
		if($groupsearch){
			$namesql=" and name like '%".$groupsearch."%'";
		}else{
			showmessage('请输入要查找外部资源的名称',join_plugin_action("index"));
		}
		if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
			$releasesql=" and (released=1 or sugestuid='".$_G[uid]."' ) ";
		}
		$classcount=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where 1=1 ".$releasesql.$namesql);
		if($classcount){
			$classquery=DB::query("select * from ".DB::TABLE("extra_class")." where 1=1 ".$releasesql.$namesql);
			while($value=DB::fetch($classquery)){
			$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=1 and uid='".$_G[uid]."'");
		    $comaryarr=DB::fetch($comaryqury);
		    $value[status]=$comaryarr[status];
				if(strlen($value[totalstars])>1){
					$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
				}else{
					$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
				}
				$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
				$classlist[]=$value;
			}
		}
		$orgcount=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where 1=1 ".$releasesql.$namesql);
		if($orgcount){
			$orgquery=DB::query("select * from ".DB::TABLE("extra_org")." where 1=1 ".$releasesql.$namesql);
			while($value=DB::fetch($orgquery)){
				$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=3 and uid='".$_G[uid]."'");
		        $comaryarr=DB::fetch($comaryqury);
		       $value[status]=$comaryarr[status];
				if(strlen($value[totalstars])>1){
					$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
				}else{
					$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
				}
				$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
				$orglist[]=$value;
			}
		}
		$leccount=DB::result_first("select count(*) from ".DB::TABLE("extra_lecture")." where 1=1 ".$releasesql.$namesql);
		if($leccount){
			$lecquery=DB::query("select * from ".DB::TABLE("extra_lecture")." where 1=1 ".$releasesql.$namesql);
			while($value=DB::fetch($lecquery)){
				$comaryqury=DB::query("select * from ".DB::table('extra_compare')." where extra_id=".$value[id]." and type=2 and uid='".$_G[uid]."'");
		        $comaryarr=DB::fetch($comaryqury);
		       $value[status]=$comaryarr[status];
				if(strlen($value[totalstars])>1){
					$value[stars]="xiaoxx xiaoxx".(substr($value[totalstars],0,1)*2+1);
				}else{
					$value[stars]="xiaoxx xiaoxx".floor($value[totalstars]*2);
				}
				$value[sugestdateline]=dgmdate($value[sugestdateline],'Y-m-d');
				$leclist[]=$value;
			}
		}

		return array("classlist"=>$classlist,"leclist"=>$leclist,"orglist"=>$orglist,'groupsearch'=>$groupsearch);


}


function viewclass(){//课程详情
	global $_G;
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='1'||$_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='2'){
		$cannotsuguest=1;
	}
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;
	}
	//end

	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$id);
	$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$id);
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
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	$class[sugestdateline]=dgmdate($class[sugestdateline]);
	$class[viewtimes]=$class[viewtimes]+1;
	if(strlen($class[totalstars])>1){
		$class[stars]="xiaoxx xiaoxx".(substr($class[totalstars],0,1)*2+1);
	}else{
		$class[stars]="xiaoxx xiaoxx".floor($class[totalstars]*2);
		$class[totalstars]=$class[totalstars].'.0';
	}
	if(strlen($class[starsone])>1){
		$class[stars1]="xiaoxx xiaoxx".(substr($class[starsone],0,1)*2+1);
	}else{
		$class[stars1]="xiaoxx xiaoxx".floor($class[starsone]*2);
		$class[starsone]=$class[starsone].'.0';
	}
	if(strlen($class[starstwo])>1){
		$class[stars2]="xiaoxx xiaoxx".(substr($class[starstwo],0,1)*2+1);
	}else{
		$class[stars2]="xiaoxx xiaoxx".floor($class[starstwo]*2);
		$class[starstwo]=$class[starstwo].'.0';
	}
	if(strlen($class[starsthree])>1){
		$class[stars3]="xiaoxx xiaoxx".(substr($class[starsthree],0,1)*2+1);
	}else{
		$class[stars3]="xiaoxx xiaoxx".floor($class[starsthree]*2);
		$class[starsthree]=$class[starsthree].'.0';
	}
	$comment_refer= join_plugin_action('viewclass',array('id'=>$id));
	//评论
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;

	require_once libfile('function/home');
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$comment_list = array ();
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extraclassid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extraclassid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$comment_list[] = $value;
		}
		$comment_replynum = $count;
	} else {
		$comment_replynum = 0;
	}
	$comment_needreplynum = true;
	if($count){
		$multi = multi($count, $perpage, $page, $comment_refer);
	}
	$key = ("extraclass_".$_G[uid]."_".$id);

  	if($_COOKIE["extraclass_star_uid"]!=$key){
  	}else{
		$showstars=1;
	}

	DB::query("update ".DB::TABLE("extra_class")." set viewtimes=".$class[viewtimes]." where id=".$id);
	 //获得专区信息
	$groupnav = get_groupnav($_G['forum']);

	include template("extraresource:viewclass");
	dexit();
}

function editclass(){//课程详情
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_G["gp_id"];
    if(!$id){
        showmessage('参数不合法',join_plugin_action('viewclass',array('id'=>$id)));
    }
	if(submitcheck('editsubmit')){
		$name=$_POST[classname];
		$descr=$_POST[classdescr];
		$modifyarr=array();
		$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id=".$id;
		if($name){
			$res=DB::result_first("select count(*) from ".DB::TABLE("extra_class")." where name='".$name."' and id!=".$id);
			if($res){
				showmessage('该课程已存在，请重新填写！请返回');
			}
			$modifyarr[name]=$name;
		}
		if($descr){
			$modifyarr[descr]=$descr;
		}
		//关联机构
		$orgids=$_POST[orgids];
		$orgnames=$_POST[orgnames];

		foreach($orgids as $key=>$value){
			if(in_array($value,$orgidarr)){
			}else{
				$orgidarr[]=$value;
				$orgnamearr[]=$orgnames[$key];
			}
		}
		//关联讲师
		$lecids=$_POST[lecids];
		if(!$lecids){
			showmessage('请选择讲师！请返回');
		}

		foreach($lecids as $key=>$value){
			if(in_array($value,$lecidarr)){
			}else{
				$lecidarr[]=$value;
			}
		}
		if($_FILES['classinfo']['name']){
			$upload_path="data/attachment/plugin_extraresource";// 上传文件的存储路径
			$file_size_max=1024*1024*10; //10M限制文件上传最大容量(bytes)
			$store_dir=$upload_path."/"; // 上传文件的储存位置
			$accept_overwrite=0;  //是否允许覆盖相同文件  1：允许 0:不允许

			//POST中name= "upload";
			$upload_file=$_FILES['classinfo']['tmp_name'];  //文件被上传后在服务端储存的临时文件名
			$upload_file_name=str_replace(" ","",$_FILES['classinfo']['name']); //文件名

			$upload_file_size=$_FILES['upload']['size'];  //文件大小
			$extarr=array(doc,docx,ppt,pptx,pdf,rtf);
			 if($upload_file)
				{
					//检查文件内型
					preg_match('|\.(\w+)$|', $upload_file_name, $ext);
					$ext = strtolower($ext[1]);
					if(in_array($ext,$extarr)){
					}else{
						showmessage("上传的文件类型错误，请重新上传！",$url);
					}

					//检查文件大小
					if($upload_file_size > $file_size_max)
							showmessage("上传的文件超过10M，请重新上传！",$url);

					//检查存储目录，如果存储目录不存在，则创建之
					if(!is_dir($upload_path))
							mkdir($upload_path);

					//检查读写文件
					if(file_exists($store_dir.$upload_file_name) && $accept_overwrite)
							showmessage("文件已上传！",$url);

					//复制文件到指定目录
					//$new_file_name=$_G[fid].".xls";//上传过后的保存的名称
					if(!move_uploaded_file($upload_file,$store_dir.$upload_file_name))
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
				$uploadfile=$store_dir.$upload_file_name;
		}
		if($uploadfile){
			$modifyarr[uploadfile]=$uploadfile;
			$modifyarr[uploadfilename]=$upload_file_name;
		}
		if($_POST[classification]){
			$modifyarr[classification]=$_POST[classification];
		}

		DB::update('extra_class',$modifyarr,"id=".$id);
		DB::query("update ".DB::table('extra_resource')." set name='".$name."' where type='class' and resourceid=".$id);
		DB::query("delete from ".DB::table('extra_relationship')." WHERE classid=".$id);
		if($lecidarr){
			foreach($lecidarr as $lecid){
				$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);
				DB::insert("extra_relationship",array("classid"=>$id,
				"classname"=>$name,
				"classstars"=>intval($_POST["classtotalstars"]),
				"lecid"=>$lecid,
				"lecname"=>$lecture[name],
				"lecorg"=>$lecture[relationorgname],
				"lecstars"=>$lecture[totalstars],
				"dateline"=>time()));
			}
		}

		if($orgidarr){
			foreach($orgidarr as $oid){
				$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$oid);
				DB::insert("extra_relationship",array("orgid"=>$oid,
				"orgname"=>$org[name],
				"orglog"=>$org[uploadfile],
				"orgstars"=>$org["totalstars"],
				"classid"=>$id,
				"classname"=>$name,
				"classstars"=>intval($_POST["classtotalstars"]),
				"dateline"=>time()));
			}
		}

		showmessage("编辑成功！", join_plugin_action('viewclass',array('id'=>$id)));
	}else{
		$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$id);
		$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$id);
		while($value=DB::fetch($query)){
			if($value[lecid]){
				$leclist[]=$value;
			}
			if($value[orgid]){
				$orglist[]=$value;
			}
		}

			 //获得专区信息
		$groupnav = get_groupnav($_G['forum']);
		include template("extraresource:editclass");
		dexit();
	}
}

function vieworg(){//机构详情
	global $_G;

	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='1'||$_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='2'){
		$cannotsuguest=1;
	}
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}
	//end
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexorg"));
    }
	$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$id);
	$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$id);
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
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
	}
	if(strlen($org[totalstars])>1){
		$org[stars]="xiaoxx xiaoxx".(substr($org[totalstars],0,1)*2+1);
	}else{
		$org[stars]="xiaoxx xiaoxx".floor($org[totalstars]*2);
		$org[totalstars]=$org[totalstars].'.0';
	}
	if(strlen($org[starsone])>1){
		$org[stars1]="xiaoxx xiaoxx".(substr($org[starsone],0,1)*2+1);
	}else{
		$org[stars1]="xiaoxx xiaoxx".floor($org[starsone]*2);
		$org[starsone]=$org[starsone].'.0';
	}
	if(strlen($org[starstwo])>1){
		$org[stars2]="xiaoxx xiaoxx".(substr($org[starstwo],0,1)*2+1);
	}else{
		$org[stars2]="xiaoxx xiaoxx".floor($org[starstwo]*2);
		$org[starstwo]=$org[starstwo].'.0';
	}
	if(strlen($org[starsthree])>1){
		$org[stars3]="xiaoxx xiaoxx".(substr($org[starsthree],0,1)*2+1);
	}else{
		$org[stars3]="xiaoxx xiaoxx".floor($org[starsthree]*2);
		$org[starsthree]=$org[starsthree].'.0';
	}

	$comment_refer= join_plugin_action('vieworg',array('id'=>$id));
	//评论
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;

	require_once libfile('function/home');
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$comment_list = array ();
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extraorgid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extraorgid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$comment_list[] = $value;
		}
		$comment_replynum = $count;
	} else {
		$comment_replynum = 0;
	}
	$comment_needreplynum = true;
	if($count){
		$multi = multi($count, $perpage, $page, $comment_refer);
	}
	$key = ("extraorg_".$_G[uid]."_".$id);

  	if($_COOKIE["extraorg_star_uid"]!=$key){
  	}else{
		$showstars=1;
	}
	//获得专区信息
	$groupnav = get_groupnav($_G['forum']);
	include template("extraresource:vieworg");
	dexit();
}

function editorg(){//编辑机构
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_G["gp_id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	if(submitcheck('editsubmit')){
		$name=$_POST[orgname];
		$descr=$_POST[orgdescr];
		$modifyarr=array();
		if($name){
			$res=DB::result_first("select count(*) from ".DB::TABLE("extra_org")." where name='".$name."' and id!=".$id);
			if($res){
				showmessage('该机构已存在，请重新填写！', join_plugin_action("indexorg"));
			}
			$modifyarr[name]=$name;
		}
		if($descr){
			$modifyarr[descr]=$descr;
		}

		//获取图片url
		$img = null;
		if ($_POST["aid"]) {
			$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
			$img = DB::fetch($query);
		}
		if($img["attachment"]){
			$uploadfile = "data/attachment/home/".$img["attachment"];
			$modifyarr[uploadfile]=$uploadfile;
		}else{
			$uploadfile=$_POST["uploadfile"];
		}
		DB::update('extra_org',$modifyarr,"id=".$id);
		DB::query("update ".DB::table('extra_resource')." set name='".$name."' where type='org' and resourceid=".$id);
		DB::query("delete from ".DB::table('extra_relationship')." WHERE orgid=".$id);

		$lecids=$_POST[lecids];

		foreach($lecids as $key=>$value){
			if(in_array($value,$lecidarr)){
			}else{
				$lecidarr[]=$value;
			}
		}
		//关联课程
		$classids=$_POST[classids];

		foreach($classids as $key=>$value){
			if(in_array($value,$classidarr)){
			}else{
				$classidarr[]=$value;
			}
		}


		if($lecidarr){
			foreach($lecidarr as $lecid){
				$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecid);

				DB::insert("extra_relationship",array("orgid"=>$id,
				"orgname"=>$name,
				"orglog"=>$uploadfile,
				"orgstars"=>intval($_POST["orgtotalstars"]),
				"lecid"=>$lecid,
				"lecname"=>$lecture[name],
				"lecorg"=>$lecture[relationorgname].",".$name,
				"lecstars"=>$lecture[totalstars],
				"dateline"=>time()));
				$query=DB::query("select orgname from ".DB::table("extra_relationship")." where lecid=".$lecid);
				$relationorg=null;
				while($value=DB::fetch($query)){
					if($value[orgname]){
						$relationorg[]=$value[orgname];
					}
				}
				$relationorgname=implode(',',$relationorg);
				DB::query("update ".DB::table("extra_lecture")." set relationorgname='".$relationorgname."' where id=".$lecid);
				DB::query("update ".DB::table("extra_relationship")." set lecorg='".$relationorgname."' where lecid=".$lecid);

			}
		}
		if($classidarr){
			foreach($classidarr as $claid){
				$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
				DB::insert("extra_relationship",array("orgid"=>$id,
				"orgname"=>$name,
				"orglog"=>$uploadfile,
				"orgstars"=>intval($_POST["orgtotalstars"]),
				"classid"=>$claid,
				"classname"=>$class[name],
				"classstars"=>$class[totalstars],
				"dateline"=>time()));
				$query=DB::query("select orgname from ".DB::table("extra_relationship")." where classid=".$claid);
				$relationorg=null;
				while($value=DB::fetch($query)){
					if($value[orgname]){
						$relationorg[]=$value[orgname];
					}
				}
				$relationorgname=implode(',',$relationorg);
				DB::query("update ".DB::table("extra_class")." set relationorgname='".relationorgname."' where id=".$claid);
			}
		}

		showmessage("编辑成功！", join_plugin_action('vieworg',array('id'=>$id)));
	}else{
		$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$id);
		$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$id);
		while($value=DB::fetch($query)){
			if($value[lecid]){
				$leclist[]=$value;
			}
			if($value[classid]){
				$classlist[]=$value;
			}
		}
		//获得专区信息
		$groupnav = get_groupnav($_G['forum']);
		include template("extraresource:editorg");
		dexit();
	}



}



function viewlec(){//讲师详情
	global $_G;
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='1'||$_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]=='2'){
		$cannotsuguest=1;
	}
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		$_G['forum']['ismoderator']=0;

	}
	//end
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }
	$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$id);
	$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$id);
	while($value=DB::fetch($query)){
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	if(strlen($lecture[totalstars])>1){
		$lecture[stars]="xiaoxx xiaoxx".(substr($lecture[totalstars],0,1)*2+1);
	}else{
		$lecture[stars]="xiaoxx xiaoxx".floor($lecture[totalstars]*2);
		$lecture[totalstars]=$lecture[totalstars].'.0';
	}
	if(strlen($lecture[starsone])>1){
		$lecture[stars1]="xiaoxx xiaoxx".(substr($lecture[starsone],0,1)*2+1);
	}else{
		$lecture[stars1]="xiaoxx xiaoxx".floor($lecture[starsone]*2);
		$lecture[starsone]=$lecture[starsone].'.0';
	}
	if(strlen($lecture[starstwo])>1){
		$lecture[stars2]="xiaoxx xiaoxx".(substr($lecture[starstwo],0,1)*2+1);
	}else{
		$lecture[stars2]="xiaoxx xiaoxx".floor($lecture[starstwo]*2);
		$lecture[starstwo]=$lecture[starstwo].'.0';
	}
	if(strlen($lecture[starsthree])>1){
		$lecture[stars3]="xiaoxx xiaoxx".(substr($lecture[starsthree],0,1)*2+1);
	}else{
		$lecture[stars3]="xiaoxx xiaoxx".floor($lecture[starsthree]*2);
		$lecture[starsthree]=$lecture[starsthree].'.0';
	}

	$comment_refer= join_plugin_action('viewlec',array('id'=>$id));
	//评论
	$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;

	require_once libfile('function/home');
	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
	$csql = $cid ? "cid='$cid' AND" : '';
	$comment_list = array ();
	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extralecid'"), 0);
	if ($count) {
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$id' AND idtype='extralecid' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$comment_list[] = $value;
		}
		$comment_replynum = $count;
	} else {
		$comment_replynum = 0;
	}
	$comment_needreplynum = true;
	if($count){
		$multi = multi($count, $perpage, $page, $comment_refer);
	}
	$key = ("extralec_".$_G[uid]."_".$id);

  	if($_COOKIE["extralec_star_uid"]!=$key){
  	}else{
		$showstars=1;
	}
	//获得专区信息
	$groupnav = get_groupnav($_G['forum']);
	include template("extraresource:viewlec");
	dexit();
}

function editlec(){//编辑讲师
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_G["gp_id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }
	if(submitcheck('editsubmit')){
		$name=$_POST[name];
		$descr=$_POST[lecdescr];
		if($descr){
			$modifyarr[descr]=$descr;
		}
		//关联机构
		$orgids=$_POST[orgids];
		$orgnames=$_POST[orgnames];

		foreach($orgids as $key=>$value){
			if(in_array($value,$orgidarr)){
			}else{
				$orgidarr[]=$value;
				$orgnamearr[]=$orgnames[$key];
			}
		}
		$modify[relationorgname]=implode(',',$orgnamearr);

			//关联课程
		$classids=$_POST[classids];
		if(!$classids){
			showmessage('请选择课程！', join_plugin_action('viewlec',array('id'=>$id)));
		}
		foreach($classids as $key=>$value){
			if(in_array($value,$classidarr)){
			}else{
				$classidarr[]=$value;
			}
		}
		//获取图片url
		$img = null;
		if ($_POST["aid"]) {
			$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
			$img = DB::fetch($query);
		}
		if($img["attachment"]){
			$uploadfile = "data/attachment/home/".$img["attachment"];
			$modifyarr[uploadfile]=$uploadfile;
		}else{
			$uploadfile=$_POST["uploadfile"];
		}
		if($_POST["trainingexperince"]){
			$modifyarr[trainingexperince]=$_POST["trainingexperince"];
		}
		if($_POST["trainingtrait"]){
			$modifyarr[trainingtrait]=$_POST["trainingtrait"];
		}
		if($_POST["teachdirection"]){
			$modifyarr[teachdirection]=$_POST["teachdirection"];
		}
		if($_POST["telephone"]){
			$modifyarr[telephone]=$_POST["telephone"];
		}
		if($_POST["email"]){
			$modifyarr[email]=$_POST["email"];
		}
		if($_POST["gender"]){
			$modifyarr[gender]=$_POST["gender"];
		}
		if($_POST["minfee"]){
			$modifyarr[minfee]=intval($_POST["minfee"]);
		}
		if($_POST["maxfee"]){
			$modifyarr[maxfee]=intval($_POST["maxfee"]);
		}
		DB::update('extra_lecture',$modifyarr,"id=".$id);
		DB::query("update ".DB::table('extra_resource')." set name='".$name."' where type='lec' and resourceid=".$id);
		DB::query("delete from ".DB::table('extra_relationship')." WHERE lecid=".$id);

		if($orgidarr){
			foreach($orgidarr as $orgid){
				$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orgid);
				DB::insert("extra_relationship",array("orgid"=>$orgid,
				"orgname"=>$org[name],
				"orglog"=>$org[uploadfile],
				"orgstars"=>$org["totalstars"],
				"lecid"=>$id,
				"lecname"=>$name,
				"lecorg"=>implode(',',$orgnamearr),
				"lecstars"=>intval($_POST["lectotalstars"]),
				"dateline"=>time()));
			}
		}
		if($classidarr){
			foreach($classidarr as $claid){
				$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$claid);
				DB::insert("extra_relationship",array("lecid"=>$id,
				"lecname"=>$name,
				"lecorg"=>implode(',',$orgnamearr),
				"lecstars"=>intval($_POST["lectotalstars"]),
				"classid"=>$claid,
				"classname"=>$class[name],
				"classstars"=>$class[totalstars],
				"dateline"=>time()));
			}
		}

		showmessage("编辑成功！", join_plugin_action('viewlec',array('id'=>$id)));
	}else{
		$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$id);
		$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$id);
		while($value=DB::fetch($query)){
			if($value[orgid]){
				$orglist[]=$value;
			}
			if($value[classid]){
				$classlist[]=$value;
			}
		}
		//获得专区信息
		$groupnav = get_groupnav($_G['forum']);
		include template("extraresource:editlec");
		dexit();
	}
}

function releaseclass(){//发布课程
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	DB::query("update ".DB::table("extra_class")." set released=1, releaseddateline=".time()."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=1 where type='class' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id=".$id ;
	//通知动态
	$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$id);
	notification_add($class[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的课程进行了发布，赶快去查看一下了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$class[name].'</a>'), 1);

	require_once libfile('function/feed');
	$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'class' => '<a href="'.$url.'">'.$class[name].'</a>');
	feed_add('extraresource', 'feed_extra_class', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);
	showmessage("发布成功",$url);

}

function unreleaseclass(){//取消发布课程
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	DB::query("update ".DB::table("extra_class")." set released=0, releaseddateline=".time()."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=0 where type='class' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewclass&id=".$id ;

	showmessage("取消发布成功",$url);

}

function releaseorg(){//发布机构
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexorg"));
    }
	DB::query("update ".DB::table("extra_org")." set released=1, releaseddateline=".time()."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=1 where type='org' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=vieworg&id=".$id ;
	//通知动态
	$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$id);
	notification_add($org[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的机构进行了发布，赶快去查看一下了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$org[name].'</a>'), 1);

	require_once libfile('function/feed');
	$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'org' => '<a href="'.$url.'">'.$org[name].'</a>');
	feed_add('extraresource', 'feed_extra_org', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);


	showmessage("发布成功",$url);

}

function unreleaseorg(){//取消发布机构
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexorg"));
    }
	DB::query("update ".DB::table("extra_org")." set released=0, releaseddateline=".time()."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=0 where type='org' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=vieworg&id=".$id ;

	showmessage("取消发布成功",$url);

}

function releaselec(){//发布讲师
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }
	$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$id);
	if($lecture[oldlecid]){
	}else{
		if($lecture[isinnerlec]=='1'){
			DB::insert("lecturer", array("lecid"=>$lecture[innerlecid],
			"name"=>$lecture[name],
			"gender"=>intval($lecture[gender]+1),
			"rank"=>'5',
			"orgname"=>$lecture["relationorgname"],
			"tel"=>$lecture["telephone"],
			"email"=>$lecture["email"],
			"isinnerlec"=>$lecture["isinnerlec"],
			"teachdirection"=>$lecture["teachdirection"],
			"imgurl"=>$lecture["uploadfile"],
			"fid"=>'197',
			"fname"=>'培训师家园',
			"uid"=>$lecture["sugestuid"],
			"dateline"=>$lecture["sugestdateline"],
			"bginfo"=>$lecture["descr"],
			"trainingexperience"=>$lecture["trainingexperince"],
			"trainingtrait"=>$lecture["trainingtrait"]));
		}
		$oldlecid=DB::insert_id();
	}
	if($oldlecid){
		$lecsql=",oldlecid='".$oldlecid."'";
	}
	DB::query("update ".DB::table("extra_lecture")." set released=1, releaseddateline=".time().$lecsql."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=1 where type='lec' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewlec&id=".$id ;
	//通知动态

	notification_add($lecture[sugestuid], '外部培训资源', '[外部培训资源]管理员已对你在{groupname}推荐的“{extratitle}”的讲师进行了发布，赶快去查看一下了！', array( 'groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => '<a href="'.$url.'">'.$lecture[name].'</a>'), 1);

	require_once libfile('function/feed');
	$tite_data = array('fname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'lecture' => '<a href="'.$url.'">'.$lecture[name].'</a>');
	feed_add('extraresource', 'feed_extra_lec', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $_G['username'],$_G['fid']);

	showmessage("发布成功",$url);

}

function unreleaselec(){//取消发布讲师
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }
	DB::query("update ".DB::table("extra_lecture")." set released=0, releaseddateline=".time()."  WHERE id=".$id);
	DB::query("update ".DB::table('extra_resource')." set released=0 where type='lec' and resourceid=".$id);
	$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=extraresource&plugin_op=groupmenu&extraresource_action=viewlec&id=".$id ;

	showmessage("取消发布成功",$url);

}

function deleteclass(){//删除课程
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("index"));
    }
	$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$id);
	DB::query("delete from ".DB::table('extra_class')." WHERE id=".$id);
	DB::query("delete from ".DB::table('extra_resource')." WHERE type='class' and resourceid =".$id);
	DB::query("delete from ".DB::table('extra_relationship')." WHERE classid=".$id);
	DB::query("delete from ".DB::table('extra_compare')." where extra_id=".$id." and type=1");

	notification_add($class[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的课程，被管理员删除了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $class[name]), 1);

	showmessage("删除成功！",join_plugin_action("index"));

}

function deletelec(){//删除讲师
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexlec"));
    }
	$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$id);
	DB::query("delete from ".DB::table('extra_lecture')." WHERE id=".$id);
	DB::query("delete from ".DB::table('extra_resource')." WHERE type='lec' and resourceid =".$id);
	DB::query("delete from ".DB::table('extra_relationship')." WHERE lecid=".$id);
    DB::query("delete from ".DB::table('extra_compare')." where extra_id=".$id." and type=2");
	notification_add($lecture[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的讲师，被管理员删除了！',  array('groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $lecture[name]), 1);

	showmessage("删除成功！",join_plugin_action("indexlec"));

}

function deleteorg(){//删除机构
	global $_G;
	//权限控制start
	if($_G[myself][forum_groupuser][$_G['fid']."_".$_G['uid']][level]!='1'){
		showmessage('你没有权限！请返回');
	}
	$id = $_GET["id"];
    if(!$id){
        showmessage('参数不合法', join_plugin_action("indexorg"));
    }
	$org=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$id);
	DB::query("delete from ".DB::table('extra_org')." WHERE id =".$id);
	DB::query("delete from ".DB::table('extra_resource')." WHERE type='org' and resourceid =".$id);
	deleteorgrelation($id);
   DB::query("delete from ".DB::table('extra_compare')." where extra_id=".$id." and type=3");
	notification_add($org[sugestuid], '外部培训资源', '[外部培训资源]你在{groupname}发布的“{extratitle}”的机构，被管理员删除了！', array( 'groupname' => '<a href="forum.php?mod=group&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a>', 'extratitle' => $org[name]), 1);

	showmessage("删除成功！",join_plugin_action("index"));

}

function deleteorgrelation($ids){//删除机构时删除关联内容
	$query=DB::query("select * from ".DB::table("extra_relationship")." where orgid IN ($ids)");
	while($value=DB::fetch($query)){
		if($value[lecid]){
			$orgname='';
			$newname=array();
			$orgname=explode(',',$value[lecorg]);
			foreach($orgname as $name){
				if($name==$value[orgname]){
				}else{
					$newname[]=$name;
				}
			}
			$lecorg=implode(',',$newname);;
			DB::query("update ".DB::table("extra_lecture")." set relationorgname='".$lecorg."' where id=".$value[lecid]);
			DB::query("update ".DB::table("extra_relationship")." set lecorg='".$lecorg."' where lecid=".$value[lecid]);
		}
		if($value[classid]){
			$orgname='';
			$newname=array();
			$class=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			$orgname=explode(',',$class[relationorgname]);
			foreach($orgname as $name){
				if($name==$value[orgname]){
				}else{
					$newname[]=$name;
				}
			}
			$relationorgname=implode(',',$newname);
			DB::query("update ".DB::table("extra_class")." set relationorgname='".$relationorgname."' where id=".$value[classid]);
		}
	}
	DB::query("delete from ".DB::table('extra_relationship')." WHERE orgid IN ($ids)");

}

function errorMsg($errorcode){
    $lang = array(
	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。'
    );
    if(array_key_exists($errorcode,$lang)){
        $msg = $lang[$errorcode];
    }else{
        $msg = "上传失败！未知原因，请重新尝试或者联系服务中心。";
    }
    return $msg;
}
function extracompareclass(){
	global $_G;
	$id=$_GET[id];
	$type=$_GET['type'];
	if($type=='addcompare'){
		$count = DB :: result(DB::query("select count(*) as count from ".DB::table('extra_compare')." where extra_id=".$id." and type=1 and uid='".$_G[uid]."'"));
	if($count){
     DB::query("update ".DB::table('extra_compare')." set status=1 where extra_id=".$id." and type=1 and uid='".$_G[uid]."'");
     $info=1;
	}else{
	DB::insert("extra_compare",array(
	"uid"=>$_G[uid],
	"extra_id"=>$id,
	"type"=>"1",
	"status"=>"1"
	));
	 $info=1;
    }
	}if($type=='delcompare'){
    DB::query("update ".DB::table('extra_compare')." set status=0 where extra_id=".$id." and type=1  and uid='".$_G[uid]."'");
    $info=0;
	}
    $arraydata=array("is"=>$info,"id"=>$id);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}
function extracompare(){
	global $_G;
	$id=$_GET[id];
	$type=$_GET['type'];
	if($type=='addcompare'){
		$count = DB :: result(DB::query("select count(*) as count from ".DB::table('extra_compare')." where extra_id=".$id." and type=3 and uid='".$_G[uid]."'"));
	if($count){
     DB::query("update ".DB::table('extra_compare')." set status=1 where extra_id=".$id." and type=3 and uid='".$_G[uid]."'");
     $info=1;
	}else{
	DB::insert("extra_compare",array(
	"uid"=>$_G[uid],
	"extra_id"=>$id,
	"type"=>"3",
	"status"=>"1"
	));
	 $info=1;
    }
	}if($type=='delcompare'){
    DB::query("update ".DB::table('extra_compare')." set status=0 where extra_id=".$id." and type=3 and uid='".$_G[uid]."'");
    $info=0;
	}
    $arraydata=array("is"=>$info,"id"=>$id);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}
function viewcompareclass(){
	global $_G;
	$query=DB::query("select * from ".DB::table('extra_compare')." where uid=".$_G[uid]." and status=1 and type=1 ");
	if($query==false){
		return false;
	}else{
		while ($info=DB::fetch($query)) {
			$orginfo=DB::query("select name from pre_extra_class where id =".$info[extra_id]);
			$orgvalue=DB::fetch($orginfo);
			$value[extra_id]=$info[extra_id];
			$value[type]=$info[type];
			$value[name]=$orgvalue[name];
			$comparelist[]=$value;
}
}
if(!empty($comparelist)){
	$classlef_id =$comparelist[0][extra_id];
	$classrig_id=$comparelist[0][extra_id];

	$classalign_id=$comparelist[0][extra_id];

    $classleflist=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$classlef_id);
	$classlefqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$classlef_id);

	$classriglist=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$classrig_id);
	$classrigqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$classrig_id);

	$classalignlist=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$classalign_id);
	$classalignqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$classalign_id);
	while($value=DB::fetch($classlefqury)){
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
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	$classleflist[sugestdateline]=dgmdate($classleflist[sugestdateline]);
	$classleflist[viewtimes]=$classleflist[viewtimes]+1;
	if(strlen($classleflist[totalstars])>1){
		$classleflist[stars]="xiaoxx xiaoxx".(substr($classleflist[totalstars],0,1)*2+1);
	}else{
		$classleflist[stars]="xiaoxx xiaoxx".floor($classleflist[totalstars]*2);
		$classleflist[totalstars]=$classleflist[totalstars].'.0';
	}
	if(strlen($classleflist[starsone])>1){
		$classleflist[stars1]="xiaoxx xiaoxx".(substr($classleflist[starsone],0,1)*2+1);
	}else{
		$classleflist[stars1]="xiaoxx xiaoxx".floor($classleflist[starsone]*2);
		$classleflist[starsone]=$classleflist[starsone].'.0';
	}
	if(strlen($classleflist[starstwo])>1){
		$classleflist[stars2]="xiaoxx xiaoxx".(substr($classleflist[starstwo],0,1)*2+1);
	}else{
		$classleflist[stars2]="xiaoxx xiaoxx".floor($classleflist[starstwo]*2);
		$classleflist[starstwo]=$classleflist[starstwo].'.0';
	}
	if(strlen($classleflist[starsthree])>1){
		$classleflist[stars3]="xiaoxx xiaoxx".(substr($classleflist[starsthree],0,1)*2+1);
	}else{
		$classleflist[stars3]="xiaoxx xiaoxx".floor($classleflist[starsthree]*2);
		$classleflist[starsthree]=$classleflist[starsthree].'.0';
	}

	while($value=DB::fetch($classrigqury)){
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
				$classleclist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classrigorglist[]=$value;
			}
		}
	}
	$classriglist[sugestdateline]=dgmdate($classriglist[sugestdateline]);
	$classriglist[viewtimes]=$classriglist[viewtimes]+1;
	if(strlen($classriglist[totalstars])>1){
		$classriglist[stars]="xiaoxx xiaoxx".(substr($classriglist[totalstars],0,1)*2+1);
	}else{
		$classriglist[stars]="xiaoxx xiaoxx".floor($classriglist[totalstars]*2);
		$classriglist[totalstars]=$classriglist[totalstars].'.0';
	}
	if(strlen($classriglist[starsone])>1){
		$classriglist[stars1]="xiaoxx xiaoxx".(substr($classriglist[starsone],0,1)*2+1);
	}else{
		$classriglist[stars1]="xiaoxx xiaoxx".floor($classriglist[starsone]*2);
		$classriglist[starsone]=$classriglist[starsone].'.0';
	}
	if(strlen($classriglist[starstwo])>1){
		$classriglist[stars2]="xiaoxx xiaoxx".(substr($classriglist[starstwo],0,1)*2+1);
	}else{
		$classriglist[stars2]="xiaoxx xiaoxx".floor($classriglist[starstwo]*2);
		$classriglist[starstwo]=$classriglist[starstwo].'.0';
	}
	if(strlen($classriglist[starsthree])>1){
		$classriglist[stars3]="xiaoxx xiaoxx".(substr($classriglist[starsthree],0,1)*2+1);
	}else{
		$classriglist[stars3]="xiaoxx xiaoxx".floor($classriglist[starsthree]*2);
		$classriglist[starsthree]=$classriglist[starsthree].'.0';
	}
	while($value=DB::fetch($classalignqury)){
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
				$lecalignlist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$alignorglist[]=$value;
			}
		}
	}
	$classalignlist[sugestdateline]=dgmdate($classalignlist[sugestdateline]);
	$classalignlist[viewtimes]=$classalignlist[viewtimes]+1;
	if(strlen($classalignlist[totalstars])>1){
		$classalignlist[stars]="xiaoxx xiaoxx".(substr($classalignlist[totalstars],0,1)*2+1);
	}else{
		$classalignlist[stars]="xiaoxx xiaoxx".floor($classalignlist[totalstars]*2);
		$classalignlist[totalstars]=$classalignlist[totalstars].'.0';
	}
	if(strlen($classalignlist[starsone])>1){
		$classalignlist[stars1]="xiaoxx xiaoxx".(substr($classalignlist[starsone],0,1)*2+1);
	}else{
		$classalignlist[stars1]="xiaoxx xiaoxx".floor($classalignlist[starsone]*2);
		$classalignlist[starsone]=$classalignlist[starsone].'.0';
	}
	if(strlen($classalignlist[starstwo])>1){
		$classalignlist[stars2]="xiaoxx xiaoxx".(substr($classalignlist[starstwo],0,1)*2+1);
	}else{
		$classalignlist[stars2]="xiaoxx xiaoxx".floor($classalignlist[starstwo]*2);
		$classalignlist[starstwo]=$classalignlist[starstwo].'.0';
	}
	if(strlen($classalignlist[starsthree])>1){
		$classalignlist[stars3]="xiaoxx xiaoxx".(substr($classalignlist[starsthree],0,1)*2+1);
	}else{
		$classalignlist[stars3]="xiaoxx xiaoxx".floor($classalignlist[starsthree]*2);
		$classalignlist[starsthree]=$classalignlist[starsthree].'.0';
	}
}


	return array('classleflist'=>$classleflist,"compare"=>$comparelist,'leclist'=>$leclist,'orglist'=>$orglist,'classlef_id'=>$classlef_id
	,'classriglist'=>$classriglist,'classleclist'=>$classleclist,'classrigorglist'=>$classrigorglist,'classrig_id'=>$classrig_id,"classalignlist"=>$classalignlist
	,"alignorglist"=>$alignorglist,"lecalignlist"=>$lecalignlist,"classalign_id"=>$classalign_id);
}
function viewcompare(){
	global $_G;
	$query=DB::query("select * from ".DB::table('extra_compare')." where uid=".$_G[uid]." and status=1 and type=3 and uid='".$_G[uid]."'");
	if($query==false){
		return false;
	}else{
		while ($info=DB::fetch($query)) {
			$orginfo=DB::query("select name from pre_extra_org where id =".$info[extra_id]);
			$orgvalue=DB::fetch($orginfo);
			$value[extra_id]=$info[extra_id];
			$value[type]=$info[type];
			$value[name]=$orgvalue[name];
			$comparelist[]=$value;
		}
	}
	if(!empty($comparelist)){
	$orglef_id =$comparelist[0][extra_id];
	$orgrig_id=$comparelist[0][extra_id];
	$orgalign_id=$comparelist[0][extra_id];
    $orgleflist=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orglef_id);
	$orglefqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$orglef_id);

	$orgriglist=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orgrig_id);
	$orgrigqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$orgrig_id);

	$orgalignlist=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$orgalign_id);
	$orgalignqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$orgalign_id);
	while($value=DB::fetch($orglefqury)){
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
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
	}
if(strlen($orgleflist[totalstars])>1){
		$orgleflist[stars]="xiaoxx xiaoxx".(substr($orgleflist[totalstars],0,1)*2+1);
	}else{
		$orgleflist[stars]="xiaoxx xiaoxx".floor($orgleflist[totalstars]*2);
		$orgleflist[totalstars]=$orgleflist[totalstars].'.0';
	}
	if(strlen($orgleflist[starsone])>1){
		$orgleflist[stars1]="xiaoxx xiaoxx".(substr($orgleflist[starsone],0,1)*2+1);
	}else{
		$orgleflist[stars1]="xiaoxx xiaoxx".floor($orgleflist[starsone]*2);
		$orgleflist[starsone]=$orgleflist[starsone].'.0';
	}
	if(strlen($orgleflist[starstwo])>1){
		$orgleflist[stars2]="xiaoxx xiaoxx".(substr($orgleflist[starstwo],0,1)*2+1);
	}else{
		$orgleflist[stars2]="xiaoxx xiaoxx".floor($orgleflist[starstwo]*2);
		$orgleflist[starstwo]=$orgleflist[starstwo].'.0';
	}
	if(strlen($orgleflist[starsthree])>1){
		$orgleflist[stars3]="xiaoxx xiaoxx".(substr($orgleflist[starsthree],0,1)*2+1);
	}else{
		$orgleflist[stars3]="xiaoxx xiaoxx".floor($orgleflist[starsthree]*2);
		$orgleflist[starsthree]=$orgleflist[starsthree].'.0';
	}
while($rigvalue=DB::fetch($orgrigqury)){
		if($rigvalue[lecid]){
			if(strlen($rigvalue[lecstars])>1){
				$rigvalue[stars]="xiaoxx xiaoxx".(substr($rigvalue[lecstars],0,1)*2+1);
			}else{
				$rigvalue[stars]="xiaoxx xiaoxx".floor($rigvalue[lecstars]*2);
				$rigvalue[lecstars]=$rigvalue[lecstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$rigvalue[lecid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$rigvalue[released]=$res[released];
				$lecriglist[]=$rigvalue;
			}
		}
		if($rigvalue[classid]){
			if(strlen($rigvalue[classstars])>1){
				$rigvalue[stars]="xiaoxx xiaoxx".(substr($rigvalue[classstars],0,1)*2+1);
			}else{
				$rigvalue[stars]="xiaoxx xiaoxx".floor($rigvalue[classstars]*2);
				$rigvalue[classstars]=$rigvalue[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$rigvalue[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$rigvalue[released]=$res[released];
				$classriglist[]=$rigvalue;
			}
		}
	}
if(strlen($orgriglist[totalstars])>1){
		$orgriglist[stars]="xiaoxx xiaoxx".(substr($orgriglist[totalstars],0,1)*2+1);
	}else{
		$orgriglist[stars]="xiaoxx xiaoxx".floor($orgriglist[totalstars]*2);
		$orgriglist[totalstars]=$orgriglist[totalstars].'.0';
	}
	if(strlen($orgriglist[starsone])>1){
		$orgriglist[stars1]="xiaoxx xiaoxx".(substr($orgriglist[starsone],0,1)*2+1);
	}else{
		$orgriglist[stars1]="xiaoxx xiaoxx".floor($orgriglist[starsone]*2);
		$orgriglist[starsone]=$orgriglist[starsone].'.0';
	}
	if(strlen($orgriglist[starstwo])>1){
		$orgriglist[stars2]="xiaoxx xiaoxx".(substr($orgriglist[starstwo],0,1)*2+1);
	}else{
		$orgriglist[stars2]="xiaoxx xiaoxx".floor($orgriglist[starstwo]*2);
		$orgriglist[starstwo]=$orgriglist[starstwo].'.0';
	}
	if(strlen($orgriglist[starsthree])>1){
		$orgriglist[stars3]="xiaoxx xiaoxx".(substr($orgriglist[starsthree],0,1)*2+1);
	}else{
		$orgriglist[stars3]="xiaoxx xiaoxx".floor($orgriglist[starsthree]*2);
		$orgriglist[starsthree]=$orgriglist[starsthree].'.0';
	}
	while($value=DB::fetch($orgalignqury)){
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
				$lecalignlist[]=$value;
			}
		}
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classalignlist[]=$value;
			}
		}
	}
if(strlen($orgalignlist[totalstars])>1){
		$orgalignlist[stars]="xiaoxx xiaoxx".(substr($orgalignlist[totalstars],0,1)*2+1);
	}else{
		$orgalignlist[stars]="xiaoxx xiaoxx".floor($orgalignlist[totalstars]*2);
		$orgalignlist[totalstars]=$orgalignlist[totalstars].'.0';
	}
	if(strlen($orgalignlist[starsone])>1){
		$orgalignlist[stars1]="xiaoxx xiaoxx".(substr($orgalignlist[starsone],0,1)*2+1);
	}else{
		$orgalignlist[stars1]="xiaoxx xiaoxx".floor($orgalignlist[starsone]*2);
		$orgalignlist[starsone]=$orgalignlist[starsone].'.0';
	}
	if(strlen($orgalignlist[starstwo])>1){
		$orgalignlist[stars2]="xiaoxx xiaoxx".(substr($orgalignlist[starstwo],0,1)*2+1);
	}else{
		$orgalignlist[stars2]="xiaoxx xiaoxx".floor($orgalignlist[starstwo]*2);
		$orgalignlist[starstwo]=$orgalignlist[starstwo].'.0';
	}
	if(strlen($orgalignlist[starsthree])>1){
		$orgalignlist[stars3]="xiaoxx xiaoxx".(substr($orgalignlist[starsthree],0,1)*2+1);
	}else{
		$orgalignlist[stars3]="xiaoxx xiaoxx".floor($orgalignlist[starsthree]*2);
		$orgalignlist[starsthree]=$orgalignlist[starsthree].'.0';
	}
	}
	return array("compare"=>$comparelist,"orgleflist"=>$orgleflist,"orglef_id"=>$orglef_id,"orgrig_id"=>$orgrig_id,"leclist"=>$leclist,"classlist"=>$classlist,"orgriglist"=>$orgriglist
	,"classriglist"=>$classriglist,"lecriglist"=>$lecriglist,"orgalignlist"=>$orgalignlist,"classalignlist"=>$classalignlist,"lecalignlist"=>$lecalignlist);


}

function extracomparelec(){
global $_G;
	$id=$_GET[id];
	$type=$_GET['type'];
	if($type=='addcompare'){
		$count = DB :: result(DB::query("select count(*) as count from ".DB::table('extra_compare')." where extra_id=".$id." and type=2 and uid='".$_G[uid]."'"));
	if($count){
     DB::query("update ".DB::table('extra_compare')." set status=1 where extra_id=".$id." and type=2 and uid='".$_G[uid]."'");
     $info=1;
	}else{
	DB::insert("extra_compare",array(
	"uid"=>$_G[uid],
	"extra_id"=>$id,
	"type"=>"2",
	"status"=>"1"
	));
	 $info=1;
    }
	}if($type=='delcompare'){
    DB::query("update ".DB::table('extra_compare')." set status=0 where extra_id=".$id." and type=2 and uid='".$_G[uid]."'");
    $info=0;
	}
     $arraydata=array("is"=>$info,"id"=>$id);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}
function viewcomparelec(){
	global $_G;
	$query=DB::query("select * from ".DB::table('extra_compare')." where uid=".$_G[uid]." and status=1 and type=2");
	if($query==false){
		return false;
	}else{
		while ($info=DB::fetch($query)) {
			$orginfo=DB::query("select name from pre_extra_lecture where id =".$info[extra_id]);
			$orgvalue=DB::fetch($orginfo);
			$value[extra_id]=$info[extra_id];
			$value[type]=$info[type];
			$value[name]=$orgvalue[name];
			$comparelist[]=$value;
		}
	}
	if(!empty($comparelist)){
    $leclef_id=$comparelist[0][extra_id];
	$lecrig_id=$comparelist[0][extra_id];
	$lecalign_id=$comparelist[0][extra_id];
	$lecture=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$leclef_id);
	$querylef=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$leclef_id);

	$lectureriglist=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecrig_id);
	$queryrig=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$lecrig_id);

    $lecturealignlist=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lecalign_id);
	$queryalign=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$lecalign_id);

	while($value=DB::fetch($querylef)){
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	if(strlen($lecture[totalstars])>1){
		$lecture[stars]="xiaoxx xiaoxx".(substr($lecture[totalstars],0,1)*2+1);
	}else{
		$lecture[stars]="xiaoxx xiaoxx".floor($lecture[totalstars]*2);
		$lecture[totalstars]=$lecture[totalstars].'.0';
	}
	if(strlen($lecture[starsone])>1){
		$lecture[stars1]="xiaoxx xiaoxx".(substr($lecture[starsone],0,1)*2+1);
	}else{
		$lecture[stars1]="xiaoxx xiaoxx".floor($lecture[starsone]*2);
		$lecture[starsone]=$lecture[starsone].'.0';
	}
	if(strlen($lecture[starstwo])>1){
		$lecture[stars2]="xiaoxx xiaoxx".(substr($lecture[starstwo],0,1)*2+1);
	}else{
		$lecture[stars2]="xiaoxx xiaoxx".floor($lecture[starstwo]*2);
		$lecture[starstwo]=$lecture[starstwo].'.0';
	}
	if(strlen($lecture[starsthree])>1){
		$lecture[stars3]="xiaoxx xiaoxx".(substr($lecture[starsthree],0,1)*2+1);
	}else{
		$lecture[stars3]="xiaoxx xiaoxx".floor($lecture[starsthree]*2);
		$lecture[starsthree]=$lecture[starsthree].'.0';
	}


	while($value=DB::fetch($queryrig)){
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classriglist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orgriglist[]=$value;
			}
		}
	}
	if(strlen($lectureriglist[totalstars])>1){
		$lectureriglist[stars]="xiaoxx xiaoxx".(substr($lectureriglist[totalstars],0,1)*2+1);
	}else{
		$lectureriglist[stars]="xiaoxx xiaoxx".floor($lectureriglist[totalstars]*2);
		$lectureriglist[totalstars]=$lectureriglist[totalstars].'.0';
	}
	if(strlen($lectureriglist[starsone])>1){
		$lectureriglist[stars1]="xiaoxx xiaoxx".(substr($lectureriglist[starsone],0,1)*2+1);
	}else{
		$lectureriglist[stars1]="xiaoxx xiaoxx".floor($lectureriglist[starsone]*2);
		$lectureriglist[starsone]=$lectureriglist[starsone].'.0';
	}
	if(strlen($lectureriglist[starstwo])>1){
		$lectureriglist[stars2]="xiaoxx xiaoxx".(substr($lectureriglist[starstwo],0,1)*2+1);
	}else{
		$lectureriglist[stars2]="xiaoxx xiaoxx".floor($lectureriglist[starstwo]*2);
		$lectureriglist[starstwo]=$lectureriglist[starstwo].'.0';
	}
	if(strlen($lectureriglist[starsthree])>1){
		$lectureriglist[stars3]="xiaoxx xiaoxx".(substr($lectureriglist[starsthree],0,1)*2+1);
	}else{
		$lectureriglist[stars3]="xiaoxx xiaoxx".floor($lectureriglist[starsthree]*2);
		$lectureriglist[starsthree]=$lectureriglist[starsthree].'.0';
	}
	while($value=DB::fetch($queryalign)){
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classalignlist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orgalignlist[]=$value;
			}
		}
	}
	if(strlen($lecturealignlist[totalstars])>1){
		$lecturealignlist[stars]="xiaoxx xiaoxx".(substr($lecturealignlist[totalstars],0,1)*2+1);
	}else{
		$lecturealignlist[stars]="xiaoxx xiaoxx".floor($lecturealignlist[totalstars]*2);
		$lecturealignlist[totalstars]=$lecturealignlist[totalstars].'.0';
	}
	if(strlen($lecturealignlist[starsone])>1){
		$lecturealignlist[stars1]="xiaoxx xiaoxx".(substr($lecturealignlist[starsone],0,1)*2+1);
	}else{
		$lecturealignlist[stars1]="xiaoxx xiaoxx".floor($lecturealignlist[starsone]*2);
		$lecturealignlist[starsone]=$lecturealignlist[starsone].'.0';
	}
	if(strlen($lecturealignlist[starstwo])>1){
		$lecturealignlist[stars2]="xiaoxx xiaoxx".(substr($lecturealignlist[starstwo],0,1)*2+1);
	}else{
		$lecturealignlist[stars2]="xiaoxx xiaoxx".floor($lecturealignlist[starstwo]*2);
		$lecturealignlist[starstwo]=$lecturealignlist[starstwo].'.0';
	}
	if(strlen($lecturealignlist[starsthree])>1){
		$lecturealignlist[stars3]="xiaoxx xiaoxx".(substr($lecturealignlist[starsthree],0,1)*2+1);
	}else{
		$lecturealignlist[stars3]="xiaoxx xiaoxx".floor($lecturealignlist[starsthree]*2);
		$lecturealignlist[starsthree]=$lecturealignlist[starsthree].'.0';
	}
	}

	return array("compare"=>$comparelist,'orglist'=>$orglist,'classlist'=>$classlist,'lecture'=>$lecture,'leclef_id'=>$leclef_id,'classriglist'=>$classriglist,'orgriglist'=>$orgriglist,
                'lectureriglist'=>$lectureriglist,'lecrig_id'=>$lecrig_id,"classalignlist"=>$classalignlist,"lecturealignlist"=>$lecturealignlist
                ,"orgalignlist"=>$orgalignlist,'lecalign_id'=>$lecalign_id);
}
function compareorg(){
	global $_G;
	$org_id=$_GET[orgid];
    $orglist=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$org_id);
	$orgqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where orgid=".$org_id);
	while($value=DB::fetch($orgqury)){
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
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
	}
if(strlen($orglist[totalstars])>1){
		$orglist[stars]="xiaoxx xiaoxx".(substr($orglist[totalstars],0,1)*2+1);
	}else{
		$orglist[stars]="xiaoxx xiaoxx".floor($orglist[totalstars]*2);
		$orglist[totalstars]=$orglist[totalstars].'.0';
	}
	if(strlen($orglist[starsone])>1){
		$orglist[stars1]="xiaoxx xiaoxx".(substr($orglist[starsone],0,1)*2+1);
	}else{
		$orglist[stars1]="xiaoxx xiaoxx".floor($orglist[starsone]*2);
		$orglist[starsone]=$orglist[starsone].'.0';
	}
	if(strlen($orglist[starstwo])>1){
		$orglist[stars2]="xiaoxx xiaoxx".(substr($orglist[starstwo],0,1)*2+1);
	}else{
		$orglist[stars2]="xiaoxx xiaoxx".floor($orglist[starstwo]*2);
		$orglist[starstwo]=$orglist[starstwo].'.0';
	}
	if(strlen($orglist[starsthree])>1){
		$orglist[stars3]="xiaoxx xiaoxx".(substr($orglist[starsthree],0,1)*2+1);
	}else{
		$orglist[stars3]="xiaoxx xiaoxx".floor($orglist[starsthree]*2);
		$orglist[starsthree]=$orglist[starsthree].'.0';
	}
	 $arraydata=array("is"=>$orglist,"class"=>$classlist,'lec'=>$leclist);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}
function compareclass(){
	global $_G;
	$class_id =  $_GET[classid];
	$classlist=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$class_id);
	$classqury=DB::query("select * from ".DB::TABLE("extra_relationship")." where classid=".$class_id);
	while($value=DB::fetch($classqury)){
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
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	$classlist[sugestdateline]=dgmdate($classlist[sugestdateline]);
	$classlist[viewtimes]=$classlist[viewtimes]+1;
	if(strlen($classlist[totalstars])>1){
		$classlist[stars]="xiaoxx xiaoxx".(substr($classlist[totalstars],0,1)*2+1);
	}else{
		$classlist[stars]="xiaoxx xiaoxx".floor($classlist[totalstars]*2);
		$classlist[totalstars]=$classlist[totalstars].'.0';
	}
	if(strlen($classlist[starsone])>1){
		$classlist[stars1]="xiaoxx xiaoxx".(substr($classlist[starsone],0,1)*2+1);
	}else{
		$classlist[stars1]="xiaoxx xiaoxx".floor($classlist[starsone]*2);
		$classlist[starsone]=$classlist[starsone].'.0';
	}
	if(strlen($classlist[starstwo])>1){
		$classlist[stars2]="xiaoxx xiaoxx".(substr($classlist[starstwo],0,1)*2+1);
	}else{
		$classlist[stars2]="xiaoxx xiaoxx".floor($classlist[starstwo]*2);
		$classlist[starstwo]=$classlist[starstwo].'.0';
	}
	if(strlen($classlist[starsthree])>1){
		$classlist[stars3]="xiaoxx xiaoxx".(substr($classlist[starsthree],0,1)*2+1);
	}else{
		$classlist[stars3]="xiaoxx xiaoxx".floor($classlist[starsthree]*2);
		$classlist[starsthree]=$classlist[starsthree].'.0';
	}
$arraydata=array("class"=>$classlist,"lec"=>$leclist,'org'=>$orglist,'class_id'=>$class_id);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}
function comparelec(){
	global $_G;
	$lec_id= $_GET[lecid];
	$lecturlist=DB::fetch_first("select * from ".DB::TABLE("extra_lecture")." where id=".$lec_id);
	$query=DB::query("select * from ".DB::TABLE("extra_relationship")." where lecid=".$lec_id);

	while($value=DB::fetch($query)){
		if($value[classid]){
			if(strlen($value[classstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[classstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[classstars]*2);
				$value[classstars]=$value[classstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_class")." where id=".$value[classid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$classlist[]=$value;
			}
		}
		if($value[orgid]){
			if(strlen($value[orgstars])>1){
				$value[stars]="xiaoxx xiaoxx".(substr($value[orgstars],0,1)*2+1);
			}else{
				$value[stars]="xiaoxx xiaoxx".floor($value[orgstars]*2);
				$value[orgstars]=$value[orgstars].'.0';
			}
			$res=DB::fetch_first("select * from ".DB::TABLE("extra_org")." where id=".$value[orgid]);
			if($res[released]||$res[sugestuid]==$_G[uid]){
				$value[released]=$res[released];
				$orglist[]=$value;
			}
		}
	}
	if(strlen($lecturlist[totalstars])>1){
		$lecturlist[stars]="xiaoxx xiaoxx".(substr($lecturlist[totalstars],0,1)*2+1);
	}else{
		$lecturlist[stars]="xiaoxx xiaoxx".floor($lecturlist[totalstars]*2);
		$lecturlist[totalstars]=$lecturlist[totalstars].'.0';
	}
	if(strlen($lecturlist[starsone])>1){
		$lecturlist[stars1]="xiaoxx xiaoxx".(substr($lecturlist[starsone],0,1)*2+1);
	}else{
		$lecturlist[stars1]="xiaoxx xiaoxx".floor($lecturlist[starsone]*2);
		$lecturlist[starsone]=$lecturlist[starsone].'.0';
	}
	if(strlen($lecturlist[starstwo])>1){
		$lecturlist[stars2]="xiaoxx xiaoxx".(substr($lecturlist[starstwo],0,1)*2+1);
	}else{
		$lecturlist[stars2]="xiaoxx xiaoxx".floor($lecturlist[starstwo]*2);
		$lecturlist[starstwo]=$lecturlist[starstwo].'.0';
	}
	if(strlen($lecturlist[starsthree])>1){
		$lecturlist[stars3]="xiaoxx xiaoxx".(substr($lecturlist[starsthree],0,1)*2+1);
	}else{
		$lecturlist[stars3]="xiaoxx xiaoxx".floor($lecturlist[starsthree]*2);
		$lecturlist[starsthree]=$lecturlist[starsthree].'.0';
	}
	$arraydata=array("lec"=>$lecturlist,"class"=>$classlist,'org'=>$orglist,'lec_id'=>$lec_id);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
}

function classdescview(){
	global $_G;
	$classid=$_GET[id];
	$sql="select descr from pre_extra_class where id=".$classid;
	$descrvalue=DB::fetch(DB::query($sql));
    return array("descrvalue"=>$descrvalue);
}
function lecdescrview(){
	global $_G;
	$lecid=$_GET[id];
	$sql="select descr from pre_extra_lecture where id=".$lecid;
	$descrvalue=DB::fetch(DB::query($sql));
    return array("descrvalue"=>$descrvalue);
}
function orgdescrview(){
	global $_G;
	$orgid=$_GET[id];
	$sql="select descr from pre_extra_org where id=".$orgid;
	$descrvalue=DB::fetch(DB::query($sql));
    return array("descrvalue"=>$descrvalue);
}
?>
