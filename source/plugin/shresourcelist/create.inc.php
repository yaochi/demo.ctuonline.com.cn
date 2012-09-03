<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function download(){//下载文档
		require_once (dirname(__FILE__)."/download.php");
		exit();
}
	
function import(){//导入资源
		global $_G;
        set_time_limit(0);
		require_once (dirname(__FILE__)."/function/function_shresourcelist.php");
 		require_once (dirname(__FILE__)."/upload.php");
        $error=$errorarr=array();
        $errorarr=import_resource(); 
        $sucnum=$errorarr[arr2][allnum]-$errorarr[arr2][errornum];
        $error['summary']="已处理 ".$errorarr[arr2][allnum]." 条记录，其中导入成功".$sucnum."条，导入失败".$errorarr[arr2][errornum]."条";
        $error['errorarr'][0]=$errorarr["arr1"];
        $url="forum.php?mod=group&action=manage&op=manage_shresourcelist&fid=".$_G[fid];
        $error['url']=$url;
        if($errorarr[arr2][errornum]<=0){
		showmessage("资源导入成功",$url);
        } else {
		include template('common/importerror');
        exit();
        }
}


function edit() {
	global  $_G;
	$resourceid = $_GET["resourceid"];
	if (!$resourceid) {
		showmessage('参数不合法', 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=shresourcelist&plugin_op=groupmenu');
	}
	
	$query = DB::query("SELECT * FROM ".DB::table("shresourcelist")." WHERE id=".$resourceid);
    $resource = DB::fetch($query);
    
    if (!$resource) {
    	showmessage('该资源不存在', 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=shresourcelist&plugin_op=groupmenu');
    }
    
	//分类
    require_once libfile("function/category");
    $info = common_category_is_other($_G["gp_fid"], 'shresourcelist');
    $is_enable_category = false;
    if($info && $info["state"]=='Y'){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["gp_fid"], 'shresourcelist');
    }
    
    return array("resource"=>$resource, "_G"=>$_G,"is_enable_category"=>$is_enable_category,"categorys"=>$categorys);
}

function update() {
	global  $_G;
	$resourceid = $_POST["resourceid"];
	if (!$resourceid) {
		showmessage('参数不合法', 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=shresourcelist&plugin_op=groupmenu');
	}
	
//	获取图片url
    $img = null;
    if ($_POST["aid"]) {
    	$query = DB::query("SELECT * FROM ".DB::table("home_attachment")." WHERE aid=".$_POST["aid"]);
    	$img = DB::fetch($query);
    	$imgurl = "data/attachment/home/".$img["attachment"];
    	$imgurlsql = ", imglink='".$imgurl."'";
    }
	$fcategoryid=$_POST["category"];
	if(empty($fcategoryid)) $fcategoryid=0;
	DB::query("UPDATE ".DB::table("shresourcelist")." SET fixobject='".$_POST["fixobject"]."', resourcealiss='".$_POST["resourcealiss"]."', about='".$_POST["about"]."' ".$imgurlsql." ,fcategoryid=$fcategoryid WHERE id=".$resourceid);
    showmessage('更新成功', 'forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=shresourcelist&plugin_op=groupmenu');
}

function index(){
    global  $_G;
    $fup = $_GET["fid"];
    
    if (!$_POST["type"] && !$_GET["type"]) {
    	return array("_G"=>$_G);
    } else {
    	//	分页
	    $perpage = 20;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page - 1) * $perpage;
    	if ($_POST["type"]) {
	    	$filejson = getresourcelist($page, $perpage, $_POST["type"], $_POST["title"], $_POST["keyword"], $_POST["kcategoryid"], $_POST["includesubkcategory"], $_POST["orgname_input_id"], $_POST["includesubcompany"], strtotime($_POST["uploadtimefrom"]), strtotime($_POST["uploadtimeto"]));
	    	$type = $_POST["type"];
	    	$title = $_POST["title"];
	    	$keyword = $_POST["keyword"];
	    	$kcategoryid = $_POST["kcategoryid"];
	    	$kcategoryname = $_POST["kcategoryname"];
	    	$includesubkcategory = $_POST["includesubkcategory"];
	    	$orgname_input = $_POST["orgname_input"];
	    	$includesubcompany = $_POST["includesubcompany"];
	    	$uploadtimefrom = $_POST["uploadtimefrom"];
	    	$uploadtimeto = $_POST["uploadtimeto"];
    	} elseif ($_GET["type"]) {
			$filejson = getresourcelist($page, $perpage, $_GET["type"], $_GET["q"], $_GET["keyword"], $_GET["kcategory"], $_GET["includesubkcategory"], $_GET["uploadCompany"], $_GET["includesubcompany"], strtotime($_GET["uploadtimefrom"]), strtotime($_GET["uploadtimeto"]));
			$type = $_GET["type"];
	    	$title = $_GET["q"];
	    	$keyword = $_GET["keyword"];
	    	$kcategoryid = $_GET["kcategory"];
	    	$kcategoryname = $_GET["kcategoryname"];
	    	$includesubkcategory = $_GET["includesubkcategory"];
	    	$orgname_input_id = $_GET["uploadCompany"];
	    	$includesubcompany = $_GET["includesubcompany"];
	    	$uploadtimefrom = $_GET["uploadtimefrom"];
	    	$uploadtimeto = $_GET["uploadtimeto"];
    	}
		$list = $resourses = array();
		if($filejson){
			$count = $filejson['result']['totalAmount'];
			$list = $filejson['result']['resources'];
		}
		if (!$list) {
			return array("_G"=>$_G);
		}
		
	    $getcount = $count;
	    $pageparam = $filejson['pageparam'];
		$url = "forum.php?mod=group&action=plugin&fid=$fup&plugin_name=shresourcelist&plugin_op=createmenu&diy=&shresourcelist_action=index".$pageparam;
		
		if($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}
		
	    //	分类
		require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = 'shresourcelist';
	    $groupid = $_G["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    }
	    
    	return array("multipage"=>$multipage, "_G"=>$_G, "resources"=>$list, "total"=>$count, "is_enable_category"=>$is_enable_category, "categorys"=>$categorys, "type"=>$type, "title"=>$title, "keyword"=>$keyword, "kcategoryid"=>$kcategoryid, "kcategoryname"=>$kcategoryname, "includesubkcategory"=>$includesubkcategory, "orgname_input_id"=>$orgname_input, "includesubcompany"=>$includesubcompany, "uploadtimefrom"=>$uploadtimefrom, "uploadtimeto"=>$uploadtimeto);
    }
}

function save() {
	global $_G;
	$fup = $_GET["fid"];
    if(!$fup){
        showmessage('参数不合法', join_plugin_action("index"));
    }
    
    if ($_POST["resourcecheckbox"]) {
    	
    	$category = $_POST["category"];
//    	if ($category) {
//	    	$categorys[] = explode('|',$category);
//	    	$categoryid = $categorys[0];
//	    	$categoryname = $categorys[1];
//    	}
    	foreach ($_POST["resourcecheckbox"] as $resourceid) {
    		$filejson = getresource($resourceid);
    		$resource = $filejson['result'];

	    	if ($resource[type]==1) {
				$typename = "文档";
			} elseif ($resource[type]==2) {
				$typename = "案例";
			} elseif ($resource[type]==4) {
				$typename = "课程";
			}
    		
    		DB::insert("shresourcelist", array(
    			"resourceid"=>$resource[id],
    			"type"=>$typename,
    			"typeid"=>$resource[type],
    			"kcategoryid"=>$resource[kcategory],
	    		"kcategoryname"=>$resource[kcategoryname],
	    		"fcategoryid"=>$category,
	    		"fcategoryname"=>$categoryname,
	    		"imglink"=>$resource[imglink],
    			"titlelink"=>$resource[titlelink],
	    		"title"=>$resource[title],
	    		"fixobject"=>$resource[fixobject],
	    		"orgname"=>$resource[uploadCompany],
	    		"about"=>$resource[context],
	    		"fid"=>$_G['fid'],
	    		"fname"=>$_G['forum']['name'],
	    		"uid"=>$_G['uid'],
    			"uploaddate"=>$resource[uploadtime]/1000,
    			"dateline"=>time(),
    			"readnum"=>$resource[readnum],
    			"sharenum"=>$resource[sharenum],
	    		"favoritenum"=>$resource[favoritenum],
	    		"commentnum"=>$resource[commentnum],
	    		"downloadnum"=>$resource[downloadnum],
	    		"averagescore"=>$resource[averagescore],
                "resourcecode"=>$resource[code],
                "resourcealiss"=>$resource[title]
    			));
    	//add by qiaoyz,2011-3-24, EKSN-215 向专区资源列表中加入了一些资源，但不会在专区首页DIY的专区动态模块中显示出来		
        //发送动态
	    $title=$resource[title];
	    $titlelink=$resource[titlelink];
		require_once libfile('function/feed');
				$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.user_get_user_name_by_username($_G['username']).'</a>', 'resourcetitle' => '<a href='.$titlelink.'>'.$title.'</a>');
		feed_add('shresourcelist', 'feed_shresourcelist', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], user_get_user_name_by_username($_G['username']),$_G['fid']);
    	}
    }
			
	showmessage('创建成功', join_plugin_action('index'));
    
}

function getresource($id) {
	global $_G;
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=property&id=".$id;
    $str1 = openFileAPI($FILE_SEARCH_PAGE);
	if (empty ($str1)) {
		return;
	}
	$filejson=json_decode($str1, true);
	$resource = $filejson['list'][0];
	return array("result"=>$resource);
}


function getresourcelist($currentpage, $pagesize, $type, $title, $keyword, $kcategory, $includesubkcategory, $uploadcompany, $includesubcompany, $uploadtimefrom, $uploadtimeto) {
	global $_G;
	$resourses = array ();
	$count = 0;
	
	$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=search&";
	$params = array();
	if(!empty($currentpage)){
		$params['currentpage'] = $currentpage;
	} else {
		$params['currentpage'] = 1;
	}
	if(!empty($pagesize)){
		$params['pagesize'] = $pagesize;
	} else {
		$params['pagesize'] = 20;
	}
	if(!empty($type)){
		if ($type==1) {
			$typename = "文档";
		} elseif ($type==2) {
			$typename = "案例";
		} elseif ($type==4) {
			$typename = "课程";
		}
//		$params['type'] = base64_encode($typename);
		$params['type'] = $type;
	}
	if(!empty($title)){
		$params['q'] = $title;
	}
	if(!empty($keyword)){
		$params['keyword'] = base64_encode($keyword);
	}
	if(!empty($kcategory)){
		$params['kcategory'] = $kcategory;
	}
	if(!empty($includesubkcategory)){
		$params['includesubkcategory'] = true;
	}
	if(!empty($uploadcompany)){
		$params['uploadCompany'] = $uploadcompany;
	}
	if(!empty($includesubcompany)){
		$params['includesubcompany'] = false;
	} else {
		$params['includesubcompany'] = true;
	}
	if(!empty($uploadtimefrom)){
		$params['uploadtimefrom'] = $uploadtimefrom*1000;
	}
	if(!empty($uploadtimeto)){
		$params['uploadtimeto'] = $uploadtimeto*1000;
	}
	
	$url = 	$FILE_SEARCH_PAGE;
	$pageparam = "&";
	foreach($params as $key => $value){
		$url .= $key ."=".$value."&";
		$pageparam .= $key ."=".$value."&";
	}
	
	$str1 = openFileAPI($url);
	if (empty ($str1)) {
		return;
	}
	return array("result"=>json_decode($str1, true), "pageparam"=>$pageparam);
}

function openFileAPI($url) {
	$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 30,
		)
	);
	$context = @stream_context_create($opts); 
	
	$result =  file_get_contents($url, false, $context);
	return $result;
}

function import_data(){
    global $_G;
    set_time_limit(0);
    $type=empty($_GET['type'])?'caseanddoc':$_GET['type'];
    require_once (dirname(__FILE__)."/function/function_shresourcelist.php");
    require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
    if($type=='caseanddoc')
    {
    $filepath="case20110714.xls";
    $data = new Spreadsheet_Excel_Reader(); 
    $data->setOutputEncoding('gbk');    
    $data->read($filepath);
    $len_case=$data->sheets[0]['numRows'];
    $filepath="docu20110714.xls";
    $data = new Spreadsheet_Excel_Reader(); 
    $data->setOutputEncoding('gbk');    
    $data->read($filepath);
    $len_docu=$data->sheets[0]['numRows'];
    $len_arr['docu']=$len_docu-1;
    $len_arr['case']=$len_case-1;
    $soure="caseanddoc";
    $len_arr['per']=100;
	}
	else
	{
	$filepath="course.xls";
    $data = new Spreadsheet_Excel_Reader(); 
    $data->setOutputEncoding('gbk');    
    $data->read($filepath);
    $len_course=$data->sheets[0]['numRows'];
    $len_arr['course']=$len_course-1;
    $soure="course";
	}
    return array("len"=>$len_arr,"soure"=>$soure);
    
}

function import_dc(){
    global $_G;
    set_time_limit(0);
    require_once (dirname(__FILE__)."/function/function_shresourcelist.php");
    require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
    $ac=$_G['gp_ac'];
    $len=$_G['gp_len'];
    $start=($_G['gp_page']-1)*$_G['gp_per'];
    if($start<$len-$_G['gp_per']) $len=$start+$_G['gp_per'];
    if($ac=='case') $filepath="case20110714.xls";
    else $filepath="docu20110714.xls";
    $data = new Spreadsheet_Excel_Reader(); 
    $data->setOutputEncoding('gbk');    
    $data->read($filepath);
    //echo $start."--".($len+1);
   	for ($i = $start+2; $i < $len+2; $i++)
    {   
        $code=$data->sheets[0]['cells'][$i][1];
        if(!$code) continue;
        if(isexist($code)) continue;
        //echo $code."<br/>";
        //插入数据
        $filejson = getResourceById($code);
    		$resource = $filejson['result'];
			if(!$resource[code]) continue;
	    	if ($resource[type]==1) {
				$typename = "文档";
			} elseif ($resource[type]==2) {
				$typename = "案例";
			} elseif ($resource[type]==4) {
				$typename = "课程";
			}
    		
    		DB::insert("shresourcelist", array(
    			"resourceid"=>$resource[id],
    			"type"=>$typename,
    			"typeid"=>$resource[type],
    			"kcategoryid"=>$resource[kcategory],
	    		"kcategoryname"=>$resource[kcategoryname],
	    		"fcategoryid"=>0,
	    		"fcategoryname"=>'全部',
	    		"imglink"=>$resource[imglink],
    			"titlelink"=>$resource[titlelink],
	    		"title"=>$resource[title],
	    		"fixobject"=>$resource[fixobject],
	    		"orgname"=>$resource[uploadCompany],
	    		"about"=>$resource[context],
	    		"fid"=>$_G['fid'],
	    		"fname"=>$_G['forum']['name'],
	    		"uid"=>$_G['uid'],
    			"uploaddate"=>$resource[uploadtime]/1000,
    			"dateline"=>time(),
    			"readnum"=>$resource[readnum],
    			"sharenum"=>$resource[sharenum],
	    		"favoritenum"=>$resource[favoritenum],
	    		"commentnum"=>$resource[commentnum],
	    		"downloadnum"=>$resource[downloadnum],
	    		"averagescore"=>$resource[averagescore],
                "resourcecode"=>$resource[code],
                "resourcealiss"=>$resource[title]
    			));
        
    }
    $arraydata=array("s"=>true,"ac"=>$ac,"page"=>$_G['gp_page']);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();	
}

function import_kc(){
    global $_G;
    set_time_limit(0);
    require_once (dirname(__FILE__)."/function/function_shresourcelist.php");
    require_once (dirname(dirname(dirname(__FILE__))).'/common/phpexcel/reader.php');
    $start=$_G['gp_start'];
    $end=$_G['gp_end'];
   	$filepath="course.xls";
    $data = new Spreadsheet_Excel_Reader(); 
    $data->setOutputEncoding('gbk');    
    $data->read($filepath);

   	for ($i = $start+2; $i < $end+2; $i++)
    {   
        $code=$data->sheets[0]['cells'][$i][1];
        if(!$code) continue;
        if(isexist($code)) continue;
        //echo $code."<br/>";
        //插入数据
        $filejson = getResourceById($code);
    		$resource = $filejson['result'];
			if(!$resource[code]) continue;
	    	if ($resource[type]==1) {
				$typename = "文档";
			} elseif ($resource[type]==2) {
				$typename = "案例";
			} elseif ($resource[type]==4) {
				$typename = "课程";
			}
			
 			
 			$resourcealiss=$data->sheets[0]['cells'][$i][2];
   			$resourcealiss=mb_convert_encoding($resourcealiss,'UTF-8','GB2312');
 			$resource[resourcealiss]=$resourcealiss;
 			
 			$classhour=$data->sheets[0]['cells'][$i][8];
 			$resource[classhour]=ceil($classhour);
 			
 			/*$cswareform=$data->sheets[0]['cells'][$i][4];
   			$cswareform=mb_convert_encoding($cswareform,'UTF-8','GB2312');
 			$resource[cswareform]=$cswareform;
           
 			
   			$cswaretype=$data->sheets[0]['cells'][$i][5];
            $cswaretype=mb_convert_encoding($cswaretype,'UTF-8','GB2312');
   			$resource[cswaretype]=$cswaretype;
            
   			
   			$cswaresource=$data->sheets[0]['cells'][$i][6];
   			$cswaresource=mb_convert_encoding($cswaresource,'UTF-8','GB2312');
 			$resource[cswaresource]=$cswaresource;
            
   			
   			$classhour=$data->sheets[0]['cells'][$i][7];
   			$resource[classhour]=$classhour;
            
   			
   			$promotiondegree=$data->sheets[0]['cells'][$i][8];
   			$resource[promotiondegree]=$promotiondegree;
            
 			
   			$about=$data->sheets[0]['cells'][$i][9];
   			$about=mb_convert_encoding($about,'UTF-8','GB2312');
 			$resource[about]=$about;*/
    		
    		DB::insert("shresourcelist", array(
    			"resourceid"=>$resource[id],
    			"type"=>$typename,
    			"typeid"=>$resource[type],
    			"kcategoryid"=>$resource[kcategory],
	    		"kcategoryname"=>$resource[kcategoryname],
	    		"fcategoryid"=>0,
	    		"fcategoryname"=>'全部',
	    		"imglink"=>$resource[imglink],
    			"titlelink"=>$resource[titlelink],
	    		"title"=>$resource[title],
	    		"fixobject"=>$resource[fixobject],
	    		"orgname"=>$resource[uploadCompany],
	    		"about"=>$resource[context],
	    		"fid"=>$_G['fid'],
	    		"fname"=>$_G['forum']['name'],
	    		"uid"=>$_G['uid'],
    			"uploaddate"=>$resource[uploadtime]/1000,
    			"dateline"=>time(),
    			"updatetime"=>time(),
    			"readnum"=>$resource[readnum],
    			"sharenum"=>$resource[sharenum],
	    		"favoritenum"=>$resource[favoritenum],
	    		"commentnum"=>$resource[commentnum],
	    		"downloadnum"=>$resource[downloadnum],
	    		"averagescore"=>$resource[averagescore],
                "resourcecode"=>$resource[code],
    		  	"cswaresource"=>$resource[uploadCompany],
    			"classhour"=>$resource[classhour],
                "resourcealiss"=>$resource[resourcealiss]
    			));
    			unset($resource);
        
    }
    $arraydata=array("s"=>true,"start"=>$start,"end"=>$end);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();	
}


?>
