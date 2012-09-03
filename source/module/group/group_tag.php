<html>
<head>
<style>
.partition { line-height:21px; font-weight:700; color:#09C; text-align:left; }
.tb { width:600px; margin-top: 10px; padding-left: 2px; line-height: 33px; border-bottom: 1px solid {MENUBORDER}; }
.tb2 .partition{ padding:5px; background:url(static/image/admincp/bg_repx_hc.gif) repeat-x 0 -40px; }
.tb2 th{ background:#CfCfCf}
</style>
</head>
</body>

<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_index.php 9843 2010-05-05 05:38:57Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$oparray = array('list');
$operation = !in_array(getgpc('operation'), $oparray) ? 'list' : getgpc('operation');

if($operation == 'list'){

	$sql = "select tag.fid,tag.name,tag.fup,lg.id from ".DB::table("forum_forum")." tag LEFT JOIN ".DB::table("forum_labelgroup")." lg ON tag.fid = lg.labelid WHERE tag.type='label' group by tag.fid ";
	$query = DB::query($sql);
	$labels =$toplabels = array();
	while($value=DB::fetch($query)){
		{
			if($value[fup]=='0'){
				$toplabels[$value[fid]] = $value;
			}else{
				$labels[$value[fup]][] = $value;
			}
		}
	}
	
	$tags = getgpc('tagids');
	$tagids = split(",",$tags);
	$index = 1;
	showformtitle();
	foreach($toplabels as $id => $tlabel){				
		showlabel($tlabel,'');
		$index = 1;
		foreach($labels as $lid => $label){					
			if($lid==$id){
				foreach($label as $subid => $sublabel){			
					if($index%4==1)echo '<tr>';
					showlabel($sublabel,'label',$tagids);						
					if($index%4==0)echo '</tr>' ;
					$index ++ ;
				}
			}
		}
	}
	showsubfooter();
	showformfooter();
}


function showformtitle($name){
	$return = '<form method="post" autocomplete="off" name="tag" id="tag" class="s_clear" action="javascript:onsubmit();"><table class="tb tb2"><tbody><tr><th class="partition" colspan="4">'.lang('group/template','label_list').'</th></tr></tbody>';
	echo $return;
}
function showsubtitle(){
	$return = '<tr><th colspan="4" align="left"><br /><input type="checkbox" onClick="javascript:selectAll()" id="checkall" name="checkall" />'.lang('group/template','checkall').'</th></tr>';
	echo $return;
}

function showsubfooter(){
	//<td><input type="checkbox" onClick="javascript:selectAll()" id="checkall" name="checkall" />'.lang('group/template','checkall').'</td>
	$return = '<tr><td align="left" colspan="3"><input type="submit" value="'.lang('group/template','confirms').'" /></td></tr>';
	echo $return;
}

function showformfooter(){
	$return = '</table></form>';
	echo $return;
}

function showlabel(&$label,$type,$tagids){
	
		if($type== 'label'){
			if($label[name]=='业务' || $label[name]=='机构'){
				$return = '<td><input type="checkbox" disabled="disabled" id="tagid[]" name="tagid[]" value="'.$label[fid].','.$label[name].'"  />&nbsp;'.$label[name] .'</td>';
			}else{
				$return = '<td><input type="checkbox" id="tagid[]" name="tagid[]" value="'.$label[fid].','.$label[name].'"  />&nbsp;'.$label[name] .'</td>';
			}
			if(in_array($label[fid],$tagids)){
				if($label[name]=='业务' || $label[name]=='机构'){
					$return = '<td><input type="checkbox" checked="checked" disabled="disabled"  id="tagid[]" name="tagid[]" value="'.$label[fid].','.$label[name].'"  />&nbsp;'.$label[name] .'</td>';
				}else{
					$return = '<td><input type="checkbox" checked="checked" id="tagid[]" name="tagid[]" value="'.$label[fid].','.$label[name].'"  />&nbsp;'.$label[name] .'</td>';
				}
			}
		}else if($type== ''){
			$return = '<tr><th align="left" colspan="4">'.$label[name].'</th></tr>';
		}
	
	echo $return;
}



?>
<script>
var data = new Array();
var tag  = {count:"2"};
function onsubmit()
{
	var ids = document.getElementsByName("tagid[]");
	var index =0;
	for( var i=0;i<ids.length;i++ )
	{
		if( ids[i].checked )
		{
			data[index] = ids[i].value ;
			index++;
		}
	}

	 if( opener.select_tag_data(data) )
	 {
		window.close();
	 }
}

function selectAll()
{
	var checkall = document.getElementById("checkall");
	var ids = document.getElementsByName("tagid[]");
	for( var i=0;i<ids.length;i++ )
	{
		if(checkall.checked)
		{
			ids[i].checked = "checked";
		}else{
			ids[i].checked = "";
		}
	}
}

</script>
</body></html>