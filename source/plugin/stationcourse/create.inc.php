<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

	if(!$_G["gp_select"]){
    	$_G["gp_select"] = "index";
	}

	function index(){
		global $_G;
		return array();
	}

	function setstation(){//type=0,自己的岗位，type=1,感兴趣的岗位
		global $_G;
		$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=stationcourse&plugin_op=groupmenu";
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");

		$mystation=$intereststation=array();

		//type=0,自己的岗位，
		$mystation[type]=0;
		$mystation[station_id]= $_POST["orgname_input_id"];
    	$mystation[station_name]= $_POST["orgname_input"];
		//type=1,感兴趣的岗位
		$intereststation[type]=1;
		$intereststation[station_id] = $_POST["station_input_id"];
   		$intereststation[station_name]= $_POST["station_input"];

    	$mystation[uid]=$intereststation[uid]=$_G[uid];
    	$mystation[username]=$intereststation[username]=user_get_user_name($_G[uid]);
    	$mystation[fid]=$intereststation[fid]=$_G[fid];
    	$mystation[update_uid]=$intereststation[update_uid]=$_G[uid];
    	$mystation[update_time]=$intereststation[update_time]=time();

    	if($mystation[station_id]){
    		$my=getStation($_G[uid],$_G[fid],0);

    		if($my){
    		$where = array('uid' => $_G[uid],'fid' => $_G[fid],'type' => 0);
    		DB::update('user_station', $mystation, $where);
    		}else{
    		DB::insert('user_station', $mystation);
    		}
    	}

		if($intereststation[station_id]){
			$interest=getStation($_G[uid],$_G[fid],1);

			if($interest){
    		$where = array('uid' => $_G[uid],'fid' => $_G[fid],'type' => 1);
    		DB::update('user_station', $intereststation, $where);
    		}else{
    		DB::insert('user_station', $intereststation);
    		}
		}

		showmessage("设置成功",$url);
	}

	function upload(){//上传文档
		require_once (dirname(__FILE__)."/upload.php");
		exit();
	}

	function down(){//下载文档
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$file_dir   =   "static/page/stationcourse/";
		$file_name   =   "SC_TEMPLATE.rar";
		download($file_dir,$file_name);
		exit();
	}

	function generate(){//下载Excel
		require_once (dirname(__FILE__)."/generate.php");
		exit();
	}

	function importstation(){//初始化岗位树
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
		//$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=stationcourse&plugin_op=createmenu&stationcourse_action=importstation";
		//import_station();
		import_station_l();
		showmessage("岗位树导入成功！",$url);

	}

	function importstationknowldge(){//导入课程
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
		//$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=stationcourse&plugin_op=createmenu&stationcourse_action=importcourse";
		//import_station();
		//import_station_knowldge();
		showmessage("岗位知识点导入成功！",$url);
	}

	function import(){//初始化所有数据
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
        require_once ("/create_temp.php");
        erase_data();
        //create_scs();
		/*import_station();
		import_course();
		import_stacourse();*/
        import_station_l();
        import_course_1();
        import_stacourse_1();
		showmessage("初始化成功",$url);

	}

	function importcourse(){//初始化课程
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
    	//$url="forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=stationcourse&plugin_op=createmenu&stationcourse_action=importstacourse";
    	import_course_1();
		showmessage("课程导入成功！",$url);

	}

	function importstacourse(){//初始化岗位课程
		global $_G;
		require_once (dirname(__FILE__)."/function/function_stationcourse.php");
		$url="forum.php?mod=group&action=manage&op=manage_stationcourse&fid=".$_G[fid];
    	//import_stacourse();
    	import_stacourse_1();
		showmessage("岗位课程关联关系导入成功",$url);

	}



?>
