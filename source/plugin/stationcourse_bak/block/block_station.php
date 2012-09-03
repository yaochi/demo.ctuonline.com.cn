<?php
class block_station{
	
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
		require_once (dirname(dirname(__FILE__))."/function/function_stationcourse.php");
		$my_station=getStation($_G[uid],$_G[fid],0);//获得当前的岗位信息

		$result=array();
		$result["parameter"] = $parameter;
		$result["username"] =user_get_user_name($_G[uid]);
		$result["station"]=$my_station[station_name];
		$result["status"]=1;
		if(!$my_station) $result["status"]=0;
		//$result["listdata"] = $_G["group_plugins"]["group_available"]["groupmenu"];
        return array('data' => $result);
	}

}
?>