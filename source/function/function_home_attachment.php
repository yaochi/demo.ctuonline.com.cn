<?php
/* Function: Home_attachment 方法
 * Com.:
 * Author: wuhan
 * Date: 2010-7-20
 */
function dunlink_home($attach, $havethumb = 0, $remote = 0) {
	global $_G;
	$filename = $attach['attachment'];
	$havethumb = $attach['thumb'];
	$remote = $attach['remote'];
	$aid = $attach['aid'];
	if($remote) {
		ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/home/'.$filename);
		$havethumb && ftpcmd('delete', $_G['setting']['ftp']['attachdir'].'/home/'.$filename.'.thumb.jpg');
	} else {
		@unlink($_G['setting']['attachdir'].'/home/'.$filename);
		$havethumb && @unlink($_G['setting']['attachdir'].'/home/'.$filename.'.thumb.jpg');
	}
	@unlink($_G['setting']['attachdir'].'image/'.$aid.'_140_140.jpg');
}

function getattach($id, $idtype, $posttime = 0) {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = array();
	$sqladd = ($id && !empty($idtype))? "1" : "uid='$_G[uid]'";
	$sqladd1 = $posttime > 0 ? "AND dateline>'$posttime'" : '';
	$sqladd2 = "AND id='$id'";
	$sqladd3 = !empty($idtype)? "AND idtype='$idtype'" : '';
	$query = DB::query("SELECT *
		FROM ".DB::table('home_attachment')." WHERE $sqladd $sqladd1 $sqladd2 $sqladd3 ORDER BY dateline");
	$allowext = '';
	
	while($attach = DB::fetch($query)) {
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
//		if($allowext && !in_array($attach['ext'], $allowext)) {
//			continue;
//		}
		
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			if($attach['id']) {
				$attachs['used'][] = $attach;
			} else {
				$attachs['unused'][] = $attach;
			}
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/';
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			if($attach['id']) {
				$imgattachs['used'][] = $attach;
			} else {
				$imgattachs['unused'][] = $attach;
			}
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}

function getattachs($id, $idtype, $posttime = 0, $imgid = 0) {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = array();
	$sqladd1 = $posttime > 0 ? "AND dateline>'$posttime'" : '';
	$sqladd2 = "AND id='$id'";
	$sqladd3 = !empty($idtype)? "AND idtype='$idtype'" : '';
	$query = DB::query("SELECT *
		FROM ".DB::table('home_attachment')." WHERE 1 $sqladd1 $sqladd2 $sqladd3 ORDER BY dateline");
	$allowext = '';
	
	while($attach = DB::fetch($query)) {
		if($attach['aid'] == $imgid){
			continue;
		}
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
//		if($allowext && !in_array($attach['ext'], $allowext)) {
//			continue;
//		}
		$attach['aidencode'] = aidencode($attach['aid']);
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			$attachs[] = $attach;
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/';
			$attach['attachwidth'] = attachwidth($attach['width']);
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			$imgattachs[] = $attach;
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}

function learngetattachs($id=0, $type, $posttime = 0,$imgid = 0) {
	global $_G;
	require_once libfile('function/attachment');
    $addsql1=" where learid=".$id." and type=".$type;
	$addsql2=$posttime > 0 ? "AND dateline>'$posttime'" : '';
	$query = DB::query("select * from pre_learn_attachment $addsql1 $addsql2");
	$allowext = '';
	while($attach = DB::fetch($query)) {
		if($attach['aid'] == $imgid){
			continue;
		}
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
//		if($allowext && !in_array($attach['ext'], $allowext)) {
//			continue;
//		}
		$attach['aidencode'] = aidencode($attach['aid']);
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			$attachs[] = $attach;
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'group/';
			$attach['attachwidth'] = attachwidth($attach['width']);
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			$imgattachs[] = $attach;
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}
function updateattach($postattachcredits, $id, $idtype, $attachnew, $attachdel, $attachupdate = array(), $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];
	$uidadd = " AND uid='$uid'";
	if($attachnew) {
		$newaids = array_keys($attachnew);
		$newattach = $newattachfile = array();
		$query = DB::query("SELECT aid, id, idtype, attachment FROM ".DB::table('home_attachment')." WHERE aid IN (".dimplode($newaids).")$uidadd");
		while($attach = DB::fetch($query)) {
			if(!$attach['id']) {
				$newattach[] = $attach['aid'];
				$newattachfile[$attach['aid']] = $attach['attachment'];
			}
		}

		if($newattach) {
			
			DB::update('home_attachment', array('id'=>$id, 'idtype'=>$idtype), "aid IN (".dimplode($newattach).")");
			
			if($uid == $_G['uid']) {
				//上传附件积分 TODO
				//updatecreditbyaction('postattach', $uid, array(), '', count($newattach));
			}
			ftpupload($newaids, $uid);
		}
	}
	$delaids = array();
	
	if($attachdel){
	$query = DB::query("SELECT aid, attachment, thumb FROM ".DB::table('home_attachment')." WHERE aid IN (".dimplode($attachdel).") AND uid='$uid'");
		while($attach = DB::fetch($query)) {
//			$aids[] = $attach['aid'];
//			if($attachdel && in_array($attach['aid'], $attachdel)) {
				$delaids[] = $attach['aid'];
				dunlink_home($attach);
//			}
//			if($attachupdate && array_key_exists($attach['aid'], $attachupdate) && $attachupdate[$attach['aid']]) {
//				dunlink_home($attach);
//			}
		}
	}

	if($attachupdate) {
		$uaids = dimplode($attachupdate);
		$query = DB::query("SELECT aid, width, filename, filetype, filesize, attachment, isimage, thumb, remote, id, idtype FROM ".DB::table('home_attachment')." WHERE aid IN ($uaids)$uidadd");
		DB::query("DELETE FROM ".DB::table('home_attachment')." WHERE aid IN ($uaids)$uidadd");
		$attachupdate = array_flip($attachupdate);
		while($attach = DB::fetch($query)) {
			$update = $attach;
			$update['dateline'] = TIMESTAMP;
			unset($update['aid']);
			DB::update('home_attachment', $update, "aid='".$attachupdate[$attach['aid']]."'$uidadd");
		}
	}

	if($delaids) {
		DB::query("DELETE FROM ".DB::table('home_attachment')." WHERE aid IN (".dimplode($delaids).")", 'UNBUFFERED');
	}

	$attachcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_attachment')." WHERE id='$id' AND idtype='$idtype'");

	return $attachcount ? $attachcount : 0;
}

function ftpupload($aids, $uid = 0) {
	global $_G;
	$uid = $uid ? $uid : $_G['uid'];

	if(!$aids || !$_G['setting']['ftp']['on']) {
		return;
	}
	$query = DB::query("SELECT aid, thumb, attachment, filename, filesize FROM ".DB::table('home_attachment')." WHERE aid IN (".dimplode($aids).") AND uid='$_G[uid]' AND remote='0'");
	$aids = array();
	while($attach = DB::fetch($query)) {
		$attach['ext'] = fileext($attach['filename']);
		if(((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['filesize'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
			if(ftpcmd('upload', 'home/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'home/'.$attach['attachment'].'.thumb.jpg'))) {
				dunlink_home($attach);
				$aids[] = $attach['aid'];
			}
		}
	}

	if($aids) {
		DB::update('home_attachment', array('remote' => 1), "aid IN (".dimplode($aids).")");
	}
}

function getAttachUrl($attachment, $thumb, $remote){
	global $_G;
	
	$attachurl = ($remote ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/'.$attachment;
	return $attachurl.($thumb ? '.thumb.jpg' : '');
}
?>
