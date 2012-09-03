<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_memcache.php 7158 2010-03-30 03:40:23Z cnteacher $
 */

class discuz_redis
{
	var $enable;
	var $obj;

	function discuz_redis() {

	}

	function init($config) {
		if($config['on']){
			if(!empty($config['server'])) {
				$this->obj = new Redis;
				if($config['pconnect']) {
					$connect = @$this->obj->pconnect($config['server'], $config['port']);
				} else {
					$connect = @$this->obj->connect($config['server'], $config['port']);
				}
				$this->enable = $connect ? true : false;
			}
		}else{
			$this->enable=false;
		}
	}

	function get($key) {;
		return unserialize($this->obj->get($key));
	}

	function set($key, $value, $ttl = 0) {
		$value=serialize($value);
		if($ttl){
			return $this->obj->setex($key,$ttl,$value);
		}else{
			return $this->obj->set($key,$value);
		}
	}

	function rm($key) {
		return $this->obj->delete($key);
	}

}

?>