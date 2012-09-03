<?php
function get_join_plugins(){
    return array(
            array("name"=>"专区",
                  "option"=>array(array("value"=>"groupmenu", "h"=>"1000", "name"=>"专区-Tab菜单", "id"=>"groupmenu"),
                      array("value"=>"createmenu", "h"=>"0000", "name"=>"新建", "id"=>"createmenu"),
                      array("value"=>"importmenu", "h"=>"0000", "name"=>"活动导入", "id"=>"importmenu"),
                      array("value"=>"viewmenu", "h"=>"0000", "name"=>"查看", "id"=>"viewmenu"),
                      )
                ),
            array("name"=>"执行",
                  "option"=>array(array("value"=>"nosomething", "h"=>"0000", "name"=>"仅仅执行", "id"=>"nosomething"),)
                ),
			array("name"=>"后台",
                  "option"=>array(array("value"=>"3", "h"=>"1001", "name"=>"插件管理", "id"=>"plugin"),)
                ),
			array("name"=>"appapi",
                  "option"=>array(array("value"=>"appapi", "h"=>"0000", "name"=>"api", "id"=>"appapi"),)
                ),	
            );
}

function get_join_add_plugin_point_option($module){
    $_JOIN_PLUGINS = get_join_plugins();
    if(is_array($_JOIN_PLUGINS)){
        foreach($_JOIN_PLUGINS as $item){
            $result .=  '<optgroup label="'.$item["name"].'">';
            foreach($item["option"] as $option){
                $result .=  '<option h="'.$option["h"].'" e="class" value="'.$option["value"].'"'.($module['type'] == $option["value"] ? ' selected="selected"' : '').'>'.$option["name"].'</option>';
            }
            $result .= '</optgroup>';
        }
    }
    return $result;
}

function get_join_add_plugin_adminid_option($module){
    global $_G;
    foreach ($_G["common"]["group_type"] as $key => $value) {
        $result .=  '<optgroup label="'.$value["name"].'">';
        if ($value["subs"]) {
            foreach($value["subs"] as $k=>$v){
                $result .= '<option value="'.$k.'" '.($k==$module["adminid"]?"selected=\"selected\"":"").'>'.$v["name"].'</option>';
            }
        }else{
            $result .= '<option value="'.$key.'" '.($key==$module["adminid"]?"selected=\"selected\"":"").'>'.$value["name"].'</option>';
        }
        $result .= '</optgroup>';
    }
    return $result;
}

function get_join_add_plugin_name($plugin, $module){
    return array("name"=>$module["type"],
            "data"=>array(
                $plugin['identifier'].':'.$module['name']=>array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory'])
             )
        );
}

function join_plugin_template($file, $action){
    global $_G;
    list($templateid, $file) = explode(':', $file);
    $action = $action?$action:"index";
    $file = empty($clonefile) || STYLEID != $_G['cache']['style_default']['styleid'] ? $file : $file.'_'.$clonefile;

    //载入第二种路径下的文件模板
    $pluginop = $_G["gp_plugin_op"];
    $filename = $_G["group_plugins"]["group_available"][$pluginop][$templateid]["name"];
    $tpldir = './source/plugin/'.$templateid.'/template/'.$filename;
    if(!file_exists($tpldir)){
        $tpldir = './source/plugin/'.$templateid.'/template';
    }
    $tplfile = $tpldir.'/'.$action.'.htm';
    $cachefile = './data/template/'.(defined('STYLEID') ? STYLEID.'_' : '_').$templateid.":".$pluginop.'_'.str_replace('/', '_', $action).'.tpl.php';
    checktplrefresh($tplfile, $tplfile, @filemtime($cachefile), $templateid.":".$pluginop, $cachefile, $tpldir, $file);
    return $cachefile;
}

function join_plugin_block_is_display($bid){
    global $_G;
    $block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
    $param = unserialize($block["param"]);

    //add by songsp 2010-11-28 16:16
	if($param["plugin_id"] && in_array($param["plugin_id"], array('pluginmenu', 'foruminfo','forumremark','grouptop'))){
        return true;
    }


    if($param["plugin_id"] && !$_G['group_plugins']['group_available'][$param["plugin_id"]]){
        return false;
    }
    return true;
}

function join_load_plugin_block($script){
    global $_G;
    list($plugin, $script) = explode("_", $script);
    if($plugin && $script){
    	//修改  by songsp 2010-11-28
    	if($plugin && in_array($plugin, array('pluginmenu', 'foruminfo','forumremark','grouptop'))){
			$obj = "block_".$script;
            $blockclass = "block/block_".$script;
            require_once(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin. '/' . $blockclass . '.php'));
            return new $obj();

    	}
    	// end by songsp

        $plugin = $_G['group_plugins']['group_available'][$plugin];
        if($plugin){
            $obj = "block_".$script;
            $blockclass = "block/block_".$script;
            require_once(DISCUZ_ROOT . ($modfile = './source/plugin/' . $plugin['directory'] . $blockclass . '.php'));
            return new $obj();
        }
    }
}

function join_plugin_action($action, $other){
    $url = $_SERVER['SCRIPT_NAME']."?";
    $action_name = $_GET["plugin_name"]."_action";
    foreach($_GET as $key => $value){
        $url .= $action_name!=$key?"${key}=${value}&":"";
    }
    foreach($other as $key => $value){
        $url .= "${key}=${value}&";
    }
    $url .= $action_name."=${action}&";
    return $url;
}

function join_plugin_action2($action, $other){
	$url = $_SERVER['SCRIPT_NAME']."?";
    $action_name = $_GET["plugin_name"]."_action";

    $need_get = array('mod','action','fid','plugin_name','plugin_op');

    foreach($_GET as $key => $value){
    	if(in_array($key, $need_get))
        $url .= $action_name!=$key?"${key}=${value}&":"";
    }
    foreach($other as $key => $value){
        $url .= "${key}=${value}&";
    }
    $url .= $action_name."=${action}&";
    return $url;
}
?>
