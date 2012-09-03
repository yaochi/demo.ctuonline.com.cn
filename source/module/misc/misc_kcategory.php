<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
function getjs($parent, $items){
    foreach($items as $k=>$children){
       $t .= 'd.add(' . $children->id . ', '.$parent.', "' . $children->name . '", "javascript:selectkcategory(\''.$children->id.'\', \''.$children->name.'\')");';
       if(!empty($children->children)){
           $t = $t . getjs($children->id, $children->children);
       }
    }
    return $t;
}
require_once libfile("function/misc");
$json = misc_get_url("http://".$_G['config']['misc']['resourcehost']."/WebRoot/api/api.api?m=categories");
$json = json_decode($json);
$t .= '<link href="static/js/dtree/dtree.css" rel="stylesheet" type="text/css" /><script src="static/js/dtree/dtree.js" type="text/javascript"></script>';
$t .= '<script type="text/javascript">d = new dTree("d");d.add(0, -1, "知识分类", "#");';
foreach($json as $key=>$item){
    $t .= 'd.add(' . $item->id . ', 0, "' . $item->name . '", "javascript:selectkcategory(\''.$item->id.'\', \''.$item->name.'\')");';
    $t .= getjs($item->id, $item->children);
}
$t .= 'document.getElementById("kcategory").innerHTML=d.toString();';
$t .= '</script>';

include template('common/kcategory');
?>
