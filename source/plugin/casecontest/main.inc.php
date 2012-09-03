<?php
/*
 * Created on 2012-8-22
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

/**
 *参数：class-分类编号；sort-排序规则
 *返回课程列表
 */
function index(){
	global $_G;

	include template("casecontest:index");
	dexit();
}

?>
