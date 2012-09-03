<?php

/**
 * 新建类别
 * $pluginid 组件统一编号 比如 topic  grouppoll qbar groupalbum2 notice activity
 * $name 新建分类名字
 * return 新建分类编号
 */
function common_category_create_category($fid, $pluginid, $name, $displayorder=0){
    $cid = DB::insert("common_category", array(name=>$name, createdate=>time(), displayorder=>$displayorder, pid=>$pluginid, fid=>$fid));
    return $cid;
}

/**
 * 根据分类id获得分类信息
 * @param $id
 * return 返回该分类
 */
function common_category_get_category_by_id($id) {
	
		$query = DB::query("SELECT * FROM ".DB::table("common_category")." WHERE id=".$id);
		$result = DB::fetch($query);
		

	
	return $result;
}

function common_category_create_plugin_category($pid, $data){
    $query = DB::query("SELECT COUNT(1) AS c FROM ".DB::table("common_plugin_category")." WHERE pid='".$pid."' AND fid=".$data[fid]);
    $row = DB::fetch($query);
    if($row && $row["c"]==0){
        $data["pid"] = $pid;
        return DB::insert("common_plugin_category", $data);
    }else{
        DB::update("common_plugin_category", $data, array(pid=>$pid, fid=>$data[fid]));
    }
}
/**
 * 获取某个组件所有分类
 * $plguinid 数组对象，存放分类编号
 * return 返回指定分类的数组，结构为 array(name=>xx, id=>xx)
 */
function common_category_get_category($fid, $plguinid){
	global $G;
	if($_G[common_category_get_category][$fid][$plguinid]){
		return $_G[common_category_get_category][$fid][$plguinid];
	}
		
    $query = DB::query("SELECT * FROM ".DB::table("common_category")." WHERE pid='".$plguinid."' AND fid=".$fid);
    $result = array();
    while($row=DB::fetch($query)){
        $result[$row["id"]] = $row;
    }
    
    $_G[common_category_get_category][$fid][$plguinid]=$result;
    
    return $result;
}

/**
 * 返回组件附加信息(是否开启,是否必须主题分类,是否显示前缀)
 * $fid 专区编号
 * $pluginid 组件编号
 */
function common_category_is_other($fid, $pluginid){
	if($fid&&$pluginid){
	    $query = DB::query("SELECT state,required,prefix FROM ".DB::table("common_plugin_category")." WHERE pid='".$pluginid."' AND fid=".$fid);
	    $row = DB::fetch($query);
	    return $row;		
	}
}

/**
 * 组件是否开启分类
 * $fid 专区编号
 * $pluginid 组件编号
 * return true 开启， false 关闭
 */
function common_category_is_enable($fid, $pluginid){
	if($fid&&$pluginid){
	    $query = DB::query("SELECT state FROM ".DB::table("common_plugin_category")." WHERE pid='".$pluginid."' AND fid=".$fid);
	    $row = DB::fetch($query);
	    if($row && $row["state"]=="Y"){
	        return true;
	    }
	}
    return false;
}

/**
 * 组件是否必须主题分类
 * $fid
 * $pluginid 组件编号
 * return true 开启， false 关闭
 */
function common_category_is_required($fid, $pluginid){
    $query = DB::query("SELECT required FROM ".DB::table("common_plugin_category")." WHERE pid='".$pluginid."' AND fid=".$fid);
    $row = DB::fetch($query);
    if($row && $row["required"]=="Y"){
        return true;
    }
    return false;
}

/**
 * 组件是否显示前缀
 * $fid
 * $pluginid 组件编号
 * return true 开启， false 关闭
 */
function common_category_is_prefix($fid, $pluginid){
    $query = DB::query("SELECT prefix FROM ".DB::table("common_plugin_category")." WHERE pid='".$pluginid."' AND fid=".$fid);
    $row = DB::fetch($query);
    if($row && $row["prefix"]=="Y"){
        return true;
    }
    return false;
}
/**
 * 组件是否显示前缀并且必须主题分类
 * $fid
 * $pluginid 组件编号
 * return true 开启， false 关闭
 */
function common_category_isEnableAndprefix($fid, $pluginid){
	 $query = DB::query("SELECT prefix FROM ".DB::table("common_plugin_category")." WHERE pid='".$pluginid."' AND fid=".$fid);
    $row = DB::fetch($query);
    if($row && $row["prefix"]=="Y" && $row["required"]=="Y"){
        return true;
    }
    return false;
}


/**
 * 根据分类id获得分类信息
 * @param $id
 * return 返回该分类
 */
function common_category_getByid($id) {
	global $G;
	
	if($_G[common_category_getByid][$id]){
		return $_G[common_category_getByid][$id];
	}
	
	$catequery = DB::query("SELECT * FROM ".DB::table("common_category")." WHERE id=".$id);
	$catename=DB::fetch($catequery,0);
	
	return $catename;
}


?>
