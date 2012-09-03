<?php
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}
require_once libfile('function/discuzcode');
/**
 * 评选
 * @author caimm
 *
 */
class block_selection {

	function getsetting() {
		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

		return array (
			'list_length' => array (
				'title' => '列表显示行数',
				'type' => 'text',
				'value' => 10
			),
			array (
				"html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>'
			)
		);
	}

	function getstylesetting($style) {
		$categorys_setting = array ();
			$categorys_setting["general"] = array (//评选标题
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			)
		);
			$categorys_setting["cate_title_author"] = array (//标题 + 作者
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			),
		);
			$categorys_setting["cate_title_author_desc"] = array (//标题 + 作者 + 摘要
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			),
			'summary_len' => array (
				'title' => '话题摘要字数',
				'type' => 'text',
				'value' => 200
			)
		);
			$categorys_setting["cate_title_author_desc_photo"] = array (//标题 + 作者 + 摘要 + 头像
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			),
			'summary_len' => array (
				'title' => '话题摘要字数',
				'type' => 'text',
				'value' => 200
			)
		);
			$categorys_setting["cate_title_posttime"] = array (//标题 + 创建时间
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			),
		);
			$categorys_setting["cate_title_join"] = array (//标题 + 参加数
	'title_len' => array (
				'title' => '话题标题字数',
				'type' => 'text',
				'value' => 50
			),
		);
		return $categorys_setting[$style];
	}

	function getdata($style, $parameter) {
		if (!$_GET["fid"]) {
			return array (
				'html' => '请在专区内使用该组件',
				'data' => null
			);
		}
		$result = array ();
		$sql = "SELECT s.*,m.realname FROM " . DB :: table("selection") . " s left join ".DB::table("common_member_profile")." m on s.uid = m.uid WHERE fid = " . $_GET["fid"]." order by dateline desc limit ".$parameter["list_length"];
		$query = DB :: query($sql);
		$result = Array ();
		$seletionlist = Array ();

		if ($style[key] == "general") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
				}

		} else if ($style[key] == "cate_title_author") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
				}

		} else if ($style[key] == "cate_title_author_desc") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
					}

		} else if ($style[key] == "cate_title_author_desc_photo") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
				}

		} else if ($style[key] == "cate_title_posttime") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
				}

		} else if ($style[key] == "cate_title_join") {
				while ($seletionlist = DB :: fetch($query)) {
						$seletionlist["url"] = "forum.php?mod=group&action=plugin&fid=".$_GET[fid]."&plugin_name=selection&plugin_op=groupmenu&selectionid=".$seletionlist["selectionid"]."&selection_action=answer&";
						$seletionlist["startdate"] = date("Y-m-d", $seletionlist["selectionstartdate"]);
						$seletionlist["enddate"] = date("Y-m-d", $seletionlist["selectionenddate"]);
						$seletionlist["createtime"] = date("Y-m-d H:i:s", $seletionlist["dateline"]);
						$seletionlist["name"] = cutstr($seletionlist["selectionname"], $parameter["title_len"]);
						$seletionlist["about"] = discuzcode(preg_replace("/img=(\d+),(\d+)/","img=100,100",$seletionlist["selectiondescr"]),-1,0,1,1,1,1,1);
						$seletionlist["about"] = strip_tags($seletionlist["about"],'<img><br>');
						$seletionlist["about"] =cutstr($seletionlist["about"],$parameter["summary_len"]);
						$seletionlist["author"] = $seletionlist["realname"];
						$seletionlist["join"] = $seletionlist["scored"];
						$seletionlists[$seletionlist["selectionid"]]=$seletionlist;
				}

		}
		$result["parameter"] = $parameter;
		$result["listdata"] = $seletionlists;
		return array ('data' => $result);
	}
}
?>
