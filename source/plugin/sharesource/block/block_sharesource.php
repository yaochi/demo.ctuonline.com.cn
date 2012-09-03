<?php
class block_sharesource{

function getsetting() {

		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        return array(
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'), );
	}

	function getstylesetting($style) {
		return array();
    }

	function getdata($style, $parameter) {
		global $_G;
		require_once (dirname(dirname(__FILE__))."/function/function_sharesource.php");
		$result["list"]=getlist(0,10,0);
		$result["rank"]=getrank(0,5);
      	return array('data' => $result);
	}

}
?>