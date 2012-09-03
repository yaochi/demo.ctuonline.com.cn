<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('DISCUZ_CORE_FUNCTION', true);
	
    function is_szx($str){//判断字符串是否只有字母，数字和-组成
        if(preg_match("/^[0-9a-zA-Z\-]*$/",$str))
            return true;
        else
            return false;
    }
    
    function is_shuzi($str){//判断字符串是否只有字母，数字和-组成
        if(preg_match("/^[0-9]*$/",$str))
            return true;
        else
            return false;
    }
    
    
	function openFile($url) {
			$opts = array (
					'http' => array (
					'method' => 'GET',
					'timeout' => 300000,
					)
				);
			$context = @stream_context_create($opts); 
	
			$result =  file_get_contents($url, false, $context);
			return $result;
	}
	
	function getResourceById($id) {
		global $_G;
		$FILE_SEARCH_PAGE = "http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/search/?q=".$id;
		$str1 = openFile($FILE_SEARCH_PAGE);
		if (empty ($str1)) {
			return;
		}
		$filejson=json_decode($str1, true);
		$resource = $filejson['resources'][0];
		return array("result"=>$resource);
	}
	
	function isexist($resourcecode){
		global $_G;
        //$tm1=time();
		$info = DB::query("SELECT * FROM ".DB::table("shresourcelist")." WHERE fid=".$_G[fid]." AND resourcecode='".$resourcecode."'");
		$value = DB::fetch($info);
		if($value) return true;
        //$_G[time]=time()-$tm1;
		return false;
	}
	
	function getInfor($resourcecode,$isabout=false){
		global $_G;
		$filejson = getResourceById($resourcecode);
    		$resource = $filejson['result'];
	    	if ($resource[type]==1) {
				$typename = "文档";
			} elseif ($resource[type]==2) {
				$typename = "案例";
			} elseif ($resource[type]==4) {
				$typename = "课程";
			}
			$arr=array(
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
	    		"fid"=>$_G['fid'],
	    		"fname"=>$_G['forum']['name'],
	    		"uid"=>$_G['uid'],
    			"uploaddate"=>$resource[uploadtime]/1000,
                "updatetime"=>time(),
    			"readnum"=>$resource[readnum],
    			"sharenum"=>$resource[sharenum],
	    		"favoritenum"=>$resource[favoritenum],
	    		"commentnum"=>$resource[commentnum],
	    		"downloadnum"=>$resource[downloadnum],
	    		"averagescore"=>$resource[averagescore],
    			);
    			if($isabout) $arr['about']=$resource[context];
    		DB::update("shresourcelist",$arr,array("resourcecode"=>$resourcecode));
		return $arr;
	}
	
	function evalue($result,$arr,$isabout=false){
				$result["resourceid"]=$arr["resourceid"];
    			$result["type"]=$arr["type"];
    			$result["typeid"]=$arr["typeid"];
    			$result["kcategoryid"]=$arr["kcategoryid"];
	    		$result["kcategoryname"]=$arr["kcategoryname"];
	    		$result["fcategoryid"]=$arr["fcategoryid"];
	    		$result["fcategoryname"]=$arr["fcategoryname"];
	    		$result["imglink"]=$arr["imglink"];
    			$result["titlelink"]=$arr["titlelink"];
	    		$result["title"]=$arr["title"];
	    		$result["fixobject"]=$arr["fixobject"];
	    		$result["orgname"]=$arr["orgname"];
	    		$result["fid"]=$arr["fid"];
	    		$result["fname"]=$arr["fname"];
	    		$result["uid"]=$arr["uid"];
    			$result["uploaddate"]=$arr["uploaddate"];
    			$result["dateline"]=$arr["dateline"];
    			$result["readnum"]=$arr["readnum"];
    			$result["sharenum"]=$arr["sharenum"];
	    		$result["favoritenum"]=$arr["favoritenum"];
	    		$result["commentnum"]=$arr["commentnum"];
	    		$result["downloadnum"]=$arr["downloadnum"];
	    		$result["averagescore"]=$arr["averagescore"];
	    		if($isabout)
    			$result["about"]=$arr["about"];
	}
	
	function import_resource(){
		global $_G;
		require_once (dirname(dirname(__FILE__)).'/reader.php');
		$filepath="data/attachment/plugin_shresourcelist/".$_G[fid].".xls";
		$data = new Spreadsheet_Excel_Reader(); //实例化 
        //print_r($data->sheets);  
		$data->setOutputEncoding('gbk');      //编码   
		$data->read($filepath);
		$resource=array();
		$resource[fid]=$_G[fid];
 	    $resource[fname]=$_G['forum']['name'];
        $resource[typeid]=4;
 		
        $error=$error1=$error2=array();
        $error1[sheetname]="资源列表";
        $errarr=array();
        $errornum=0;
        $error2["allnum"]=$data->sheets[0]['numRows']-1;
 		for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) 
 		{    //循环输出查询结果，将数据值赋给指定的变量
 			//echo "in";
   	        $resourcecode=$data->sheets[0]['cells'][$i][1];
   			$resource[resourcecode]=$resourcecode;
            if(!$resource[resourcecode]) {
                $errarr[]="第".$i."行,”课程编号“不能为空";
                $errornum++;
                continue;
            }
            if(!is_szx($resource[resourcecode])){
                $errarr[]="第".$i."行,”课程编号“含有非法字符";
                $errornum++;
                continue;
            }
            if(strlen($resource[resourcecode])>30){
                $errarr[]="第".$i."行,”课程编号“长度不能超过30";
                $errornum++;
                continue;
            }
            
 			$type=$data->sheets[0]['cells'][$i][2];
   			$type=mb_convert_encoding($type,'UTF-8','GB2312');
 			$resource[type]=$type;
            /*if(strlen($resource[type])>30){
                $errarr[]="第".$i."行,”资源类型“长度不能超过30";
                $errornum++;
                continue;
            }*/
 			
 			$resourcealiss=$data->sheets[0]['cells'][$i][3];
   			$resourcealiss=mb_convert_encoding($resourcealiss,'UTF-8','GB2312');
 			$resource[resourcealiss]=$resourcealiss;
            /*if(strlen($resource[resourcealiss])>255){
                $errarr[]="第".$i."行,”课程别名“长度不能超过255";
                $errornum++;
                continue;
            }*/
 			
 			$cswareform=$data->sheets[0]['cells'][$i][4];
   			$cswareform=mb_convert_encoding($cswareform,'UTF-8','GB2312');
 			$resource[cswareform]=$cswareform;
            /*if(strlen($resource[$cswareform])>20){
                $errarr[]="第".$i."行,”课程形式“长度不能超过20";
                $errornum++;
                continue;
            }*/
 			
   			$cswaretype=$data->sheets[0]['cells'][$i][5];
            $cswaretype=mb_convert_encoding($cswaretype,'UTF-8','GB2312');
   			$resource[cswaretype]=$cswaretype;
            /*if(strlen($resource[cswaretype])>20){
                $errarr[]="第".$i."行,”课件格式“长度不能超过20";
                $errornum++;
                continue;
            }*/
   			
   			$cswaresource=$data->sheets[0]['cells'][$i][6];
   			$cswaresource=mb_convert_encoding($cswaresource,'UTF-8','GB2312');
 			$resource[cswaresource]=$cswaresource;
            if(strlen($resource[cswaresource])>255){
                $errarr[]="第".$i."行,”课程来源“长度不能超过255";
                $errornum++;
                continue;
            }
   			
   			$classhour=$data->sheets[0]['cells'][$i][7];
   			$resource[classhour]=$classhour;
            if(!is_shuzi($resource[classhour])){
                $errarr[]="第".$i."行,”学时“必须是整数";
                $errornum++;
                continue;
            }
   			
   			$promotiondegree=$data->sheets[0]['cells'][$i][8];
   			$resource[promotiondegree]=$promotiondegree;
            if(!is_shuzi($resource[promotiondegree])||($resource[promotiondegree]>5)){
                $errarr[]="第".$i."行,”推荐度“必须是小于5的正整数";
                $errornum++;
                continue;
            }
 			
   			$about=$data->sheets[0]['cells'][$i][9];
   			$about=mb_convert_encoding($about,'UTF-8','GB2312');
 			$resource[about]=$about;
            /*if(strlen($resource[about])>255){
                $errarr[]="第".$i."行,”课程简介“长度超过255";
                $errornum++;
                continue;
            }*/
 			
   			if(isexist($resourcecode)){
   			$wheresql=array("resourcecode"=>$resourcecode);
   			DB::update('shresourcelist', $resource,$wheresql);
   			} else {
	        $resource[dateline]=time();
   			DB::insert('shresourcelist', $resource);
   			}
 		}
        $rnum=$error2[allnum]-$errornum;
        $summary="已处理 ".$error2[allnum]." 条记录，其中导入成功".$rnum."条，导入失败".$errornum."条";
        $error1[lisummary]=$summary;
        $error1[sheeterror]=$errarr;
        $error2["errornum"]=$errornum;
        $error[arr1]=$error1;
        $error[arr2]=$error2;
        //echo $_G[time];
        return $error;
	}
	
?>