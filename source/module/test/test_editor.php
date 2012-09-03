<?php

if ($_GET["ac"] == save) {
    require_once libfile('function/discuzcode');
    echo discuzcode($_POST["message"]);
} else {
    require_once libfile("function/forum");
    loadforum();
    $_G['fid'] = 43;
    $editorid = "e";
    $editormode = 0;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message");
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
    include template("test/post");
}
?>
