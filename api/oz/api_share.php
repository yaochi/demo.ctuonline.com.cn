<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-10
 */

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

include_once libfile("function/doc");
include_once libfile("function/spacecp");

$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$doc = getFile($id);
if($doc && !empty($doc['id'])){
	$doc['uid'] = $doc['userid'];
}else{
	showmessage('view_to_info_did_not_exist');	
}

$_GET['subject'] = base64_encode($doc['title']);
$_GET['subjectlink'] = base64_encode($doc['titlelink']);
$_GET['authorid'] = $doc['uid'];
$_GET['author'] = base64_encode($doc['username']);
$_GET['message'] = base64_encode($doc['context']);
$_GET['image'] = base64_encode($doc['imglink']);

if($doc['type']){
	if($doc['type']==1){
		$_GET['type'] = 'doc';
	}elseif($doc['type']==4){
		$_GET['type'] = 'class';
	}elseif($doc['type']==2){
		$_GET['type'] = 'case';
	}else{
		$_GET['type'] = 'doc';
	}
}else{
	$_GET['type'] = 'doc';
}
$_GET['fid'] = empty($doc['zoneid']) ? 0 : intval($doc['zoneid']);

require_once dirname(dirname(dirname(__FILE__))).'/source/include/spacecp/spacecp_share.php';

?>
