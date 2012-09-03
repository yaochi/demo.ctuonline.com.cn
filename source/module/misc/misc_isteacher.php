<?php
$id = $_G["gp_id"];
if($id){
    $count = DB::result(DB::query("SELECT COUNT(1) AS c FROM ".DB::table("lecturer")." WHERE lecid=".$id));
    if($count==0){
        echo "<xml>ok</xml>";
        exit;
    }
}
echo "<xml>error</xml>";
?>
