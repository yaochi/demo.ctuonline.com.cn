<?php
/*
 * create by songsp  2011-3-24 15:49:42
 * 
 * 对memcached-client.php 中的memcached类进行封转
 * 
 * 解决memcached无法使用服务器群的问题。
 * 
 */
require_once  "memcached-client.php";
class my_memcached  extends memcached 
{
	 var $_memcached_get  ;
	 
	
	 function my_memcached ($args=''){
	 	
	 	parent::memcached($args);
	 	
	 	
	 	//将servers值取出
	 	
	 	$servers = $args['servers'];
	 	
	 	if(!$servers) die('serves is null');
	 	
	 	if(is_array($servers) && count($servers) > 1){
	 		
	 		foreach($servers as $index => $server) {
	 			
	 			$args['servers'] = array($server);
	 			
	 			$this->_memcached_get[] = new memcached($args);
	 			
	 		}
	 		
	 	}else{
	 		$this->_memcached_get[] = new memcached($args);
	 	}
	 	
	 	
	 }

	/*
	 * 重写get方法  从过个连接中获取。
	 */
	function get($key){
		
		if(!$key) return ;
		
		foreach($this->_memcached_get as  $index =>$mem){
			
			$cache = $mem->get($key);
			
			if($cache) return $cache;
		}
		
 	}
 	
 	
 	/*
 	 * 重写 add 方法     目前 不做任何事情
 	 */
 	function add ($key, $value, $exp=0){
 		if(1)return false; 
 		
 		return parent::add($key,$value,$exp);
 	}
 	
	/*
 	 * 重写set 方法     目前 不做任何事情
 	 */
	function set ($key, $value, $exp=0)
    {
    	if(1)return false; 
      	return parent::set($key,$value,$exp);
    }
	 
	 
}

/*
$options = array(
    'servers' => array('10.127.1.7:11211','10.127.1.8:11211','127.0.0.1:11211'), //memcached 服务的地址、端口，可用多个数组元素表示多个 memcached 服务,现在部署在10.127.1.7 和10.127.1.8 上面
    'debug' => false,  //是否打开 debug
    'compress_threshold' => 10240,  //超过多少字节的数据时进行压缩
    'persistant' => false  //是否使用持久连接
    );
$m= new my_memcached($options);


$m->add('test','test');
print_r($m->get('oz_u_E00230168'));
echo '<br>';
print_r($m->get('oz_u_E00230171'));
echo '<br>';
print_r($m->get('UezBhH_portal_ads'));
*/


?>
