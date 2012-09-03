<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$ac = $_G["gp_ac"];


$pagesize = 10;
$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$start = ($page - 1) * $pagesize;

if ($ac == "recommend") {
    $title = "推荐专区";
    $recommendgroup = array();
    $query = DB::query("SELECT count(1) as c
		FROM " . DB::table('forum_forum') . " f
		LEFT JOIN " . DB::table("forum_forumfield") . " ff ON ff.fid=f.fid
		WHERE f.displayorder=1");
    $count = DB::fetch($query);
    if ($count && $count[c]) {
        $query = DB::query("SELECT f.fid, f.name, ff.icon, ff.membernum, ff.threads, ff.description, ff.group_type,  f.displayorder,ff.jointype
		FROM " . DB::table('forum_forum') . " f
		LEFT JOIN " . DB::table("forum_forumfield") . " ff ON ff.fid=f.fid
		WHERE f.displayorder=1 ORDER BY ff.dateline DESC LIMIT 16");
        while ($group = DB::fetch($query)) {
            $group['icon'] = get_groupimg($group['icon'], 'icon');
            $group['name'] = cutstr($group["name"], 26);
            if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
                $group['type_icn_id'] = 'brzone_s';
            } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
                $group['type_icn_id'] = 'bizone_s';
            }
            $grouplist[] = $group;
        }
        $url = "group.php?mod=more&ac=recommend";
        $multipage = multi($count[c], $pagesize, $page, $url);
    }
} else if ($ac == "hot") {
    $title = "活跃专区";
    $newactivity = array();
    $query = DB::query("SELECT count(1) as c
		FROM " . DB::table('forum_forum') . " f
		LEFT JOIN " . DB::table("forum_forumfield") . " ff ON ff.fid=f.fid
		WHERE f.type='sub'");
    $count = DB::fetch($query);
    if ($count && $count[c]) {
        $query = DB::query("SELECT f.fid, f.name, ff.icon, ff.membernum, ff.threads, ff.description,  ff.group_type, f.displayorder,ff.jointype
		FROM " . DB::table('forum_forum') . " f
		LEFT JOIN " . DB::table("forum_forumfield") . " ff ON ff.fid=f.fid
		WHERE f.type='sub' ORDER BY ff.hot DESC LIMIT $start, $pagesize");
        while ($group = DB::fetch($query)) {
            $group['icon'] = get_groupimg($group['icon'], 'icon');
            $group['name'] = cutstr($group["name"], 26);
            if ($group["group_type"] >= 1 && $group["group_type"] < 20) {
                $group['type_icn_id'] = 'brzone_s';
            } elseif ($group["group_type"] >= 20 && $group["group_type"] < 60) {
                $group['type_icn_id'] = 'bizone_s';
            }
            $grouplist[] = $group;
        }
        $url = "group.php?mod=more&ac=hot";
        $multipage = multi($count[c], $pagesize, $page, $url);
    }
}
include template('group/more');
?>
