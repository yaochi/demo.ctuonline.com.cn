<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
    require_once libfile("function/category");
    global $_G;
    
    $pagesize = 10;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page - 1) * $pagesize;
    
    $type = $_GET["type"];
    if($type=="general"){
        $andsql = "AND ffa.type='general'";
    } else if($type=="live"){
        $andsql = "AND ffa.type='live'";
    }
    if($_GET["category_id"]){
        $andsql = "AND category_id=".$_GET["category_id"];
    }
    $orderby = $_GET["orderby"]?$_GET["orderby"]:",fff.dateline";
    if($orderby=="membernum"){
        $orderby = ",fff.membernum";
    } else if($orderby=="average"){
        $orderby = ",ffa.average";
    } elseif ($orderby=="viewnum") {
    	$orderby = ",ffa.viewnum";
    }
    $orderrule = $_GET["orderrule"]?" ".$_GET["orderrule"]:" DESC";
    $orderby .= $orderrule;

    $sql = "SELECT count(1) as c FROM ".DB::table("forum_forum")." ff, "
            .DB::table("forum_forumfield")
            ." fff ,".DB::table("forum_forum_activity")." ffa WHERE ff.fid=fff.fid AND ff.fid=ffa.fid $andsql AND ff.type='activity' AND ff.fup="
            .$_GET["fid"]." ORDER BY fff.displayorder DESC ".$orderby;
    $query = DB::query($sql);
    $getcount = DB::fetch($query);
    
    if($getcount[c]) {
        $url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=activity&plugin_op=groupmenu";
		$multipage = multi($getcount[c], $pagesize, $page, $url);
	}
    $sql = "SELECT ff.*, fff.digest, fff.banner, fff.extra, fff.category_id, fff.founderuid, fff.foundername, fff.membernum, ffa.type as ffatype, ffa.viewnum, ffa.average,ffa.live_id,ffa.teacher_id FROM ".DB::table("forum_forum")." ff, "
            .DB::table("forum_forumfield")
            ." fff ,".DB::table("forum_forum_activity")." ffa WHERE ff.fid=fff.fid AND ff.fid=ffa.fid $andsql AND ff.type='activity' AND ff.fup="
            .$_GET["fid"]." ORDER BY ff.displayorder DESC ".$orderby;;
    $query = DB::query($sql." LIMIT $start, $pagesize");
    $forums = array();
    $cids = array();
    $liveids = array();
    while($data=DB::fetch($query)){
		$relationid[]=$data[fid];
    	$img_activity = null;
    	$teacher_img = null;
        $extra = unserialize($data["extra"]);
        if($extra["startime"]!=0 && $extra["endtime"]!=0){
            $data["str_start_time"] = getdate($extra["startime"]);
            $data["str_end_time"] = getdate($extra["endtime"]);
        }
        $liveid = unserialize($data["live_id"]);
        if(count($liveid)!=0){
            if(empty($liveid[count($liveid)-1])){
                array_pop($liveid);
            }
            $t = implode(",", $liveid);
            if($t){
                $livequery = DB::query("SELECT * FROM ".DB::table("group_live")." WHERE liveid IN(".$t.")");
                while($live=DB::fetch($livequery)){
                    $lives[$data[fid]][] = $live;
                }
            }
        }
        $teacher_ids = unserialize($data["teacher_id"]);
        if(count($liveid)!=0){
            if(empty($liveid[count($liveid)-1])){
                array_pop($liveid);
            }
			foreach($teacher_ids as $i=>$teacherid){
				if($teacherid){
					$teacher_ids[$i]=$teacherid;
				}else{
					$teacher_ids[$i]='0';
				}
			}
            $t = implode(",", $teacher_ids);
            if($t){
                $teacherquery = DB::query("SELECT * FROM " . DB::table("lecturer") . " WHERE id IN(" . $t .") " );
//                $teacher = DB::fetch($teacherquery);
            	while($result = DB::fetch($teacherquery)){
                	$rtnn = check_is_group_teacher($data['fup'], $result[id]);
                	if ($rtnn) {
                		$result[imgurl] = $rtnn[imgurl];
                		if (!$img_activity && $result[imgurl]) {
                			$img_activity = $result[imgurl];
                			break;
                		}
                	}
                }
            }
            if($img_activity){
                $teacher_img = $img_activity;
            }
            if(empty($data["banner"])){
                $data["teacher_img"] = $teacher_img;
            }else{
                $data["teacher_img"] = get_groupimg($data["banner"]);
            }
        }
        $data["realname"] = user_get_user_name($data["founderuid"]);
        $forums[$data[fid]] = $data;
        $cids[] = $data["category_id"];
    }
	if($relationid){
		$query=DB::query("select anonymity,id from ".DB::TABLE("home_feed")." where icon='activitys' and idtype!='feed'  and id in (".implode(',',$relationid).")");
		while($value=DB::fetch($query)){
			if($value[anonymity]>0){
				include_once libfile('function/repeats','plugin/repeats');
				$forums[$value[id]][repeats]=getforuminfo($value[anonymity]);
			}
		}
	}
    $pluginid = $_GET["plugin_name"];
    $is_enable_category = false;
    $other_plugin_info = common_category_is_other($_G["gp_fid"], $pluginid);
    if($other_plugin_info["state"]=='Y' && $other_plugin_info['prefix']=='Y'){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["gp_fid"], $pluginid);
    }
    return array(forums=>$forums, categorys=>$categorys, is_enable_category=>$is_enable_category, lives=>$lives, G=>$_G, multipage=>$multipage,getcount=>$getcount[c]);
}

?>
