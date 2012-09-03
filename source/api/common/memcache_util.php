<?php


//  包含 memcached 类文件
//require_once(dirname(dirname(__FILE__)).'/memcached-client/memcached-client.php');


global $memcached; //社区数据库操作类
global $token; //
$token = '$u0006$';
//添加 by songsp 2010-10-24 15:55:34
global $openMemcache ; //是否开启memcache
$openMemcache = 1; //开启 1开启 0：关闭




if(1==$openMemcache){
	extension_loaded('memcache');
	$memcached = new Memcache;
	// 正式平台指向10.127.1.7:11211 10.127.1.8:11211
	$memcached->pconnect('127.0.0.1', 11211);
	// 如果使用多个memcache，再调用一次配置
	//$memcached->pconnect('10.127.1.8', 11211);

}
?>
