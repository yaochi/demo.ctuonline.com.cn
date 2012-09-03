<?php
global $memcache_credit; //社区数据库操作类
global $memcacheStatus ; //是否开启memcache
$memcacheStatus = 1; //开启 1开启 0：关闭
global $memc_flag ;//set 时 falg值  32 表示value为字符串
$memc_flag = '32';

if(1==$memcacheStatus){
	extension_loaded('memcache');
	$memcache_credit = new Memcache;
	// 正式平台指向10.127.1.81
	$memcache_credit->pconnect('127.0.0.81', 11211);
	// 如果使用多个memcache，使用一下配置
	//$memcache_credit->pconnect('127.0.0.1', 11211);
	
}
//添加 by wuj 2011.04.08




?>