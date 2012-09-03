<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_groupfeed {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        $settings = array(

			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }

    function getstylesetting($style) {
    	$categorys_setting = array();
    	//标准样式
        $categorys_setting["standard"] = array(
			'content_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
            'summary_len' => array(
                'title' => '简介字数',
                'type' => 'text',
                'default' => 100
            )
        );
		 $categorys_setting["slim1"] = array(
		 	'content_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
            'summary_len' => array(
                'title' => '简介字数',
                'type' => 'text',
                'default' => 100
            )
        );
         $categorys_setting["slim2"] = array(
		 	'content_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 50
                )

        );
        $categorys_setting["slim3"] = array(
		 	'content_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
            'summary_len' => array(
                'title' => '简介字数',
                'type' => 'text',
                'default' => 100
            )
        );
		 $categorys_setting["ticker"] = array(
		 	'content_len' => array(
                     'title' => '标题字数',
                     'type' => 'text',
                     'default' => 50
                ),
            'summary_len' => array(
	            'title' => '简介字数',
	            'type' => 'text',
	            'default' => 100
            ),
			'direction' => array(
				'title' => '滚动方向',
				'type' => 'mradio',
				'value' => array(
					array('0', "上"),
					array('1', "下"),
				),
				'default' => '1'
			)
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
		global $_G;
		require_once (dirname(dirname(__FILE__))."/function/function_groupfeed.php");
		 require_once libfile("function/feed");
		 require_once libfile("function/home");
		 require_once libfile("function/category");
		$_G['home_today'] = $_G['timestamp'] - $_G['timestamp']%(3600*24);
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
    	$fid = $_GET["fid"];
		$page=empty($_G['gp_page'])?1:$_G['gp_page'];
		$start=($page-1)*$parameter['items'];
		
		$allcount=DB::result_first("select count(*) from ".DB::TABLE('home_feed')." WHERE fid=".$fid." or sharetofids like '%,".$fid.",%'");
        $sql = "SELECT * FROM ".DB::table('home_feed')." WHERE fid=".$fid." or sharetofids like '%,".$fid.",%' order by dateline desc LIMIT $start,".$parameter['items'];
        $query = DB::query($sql);
		$hash_datas = array();
		//$more_list = array();
		$uid_feedcount = array();
		$feed_users = $feed_list = $user_list = $filter_list  = $list = $mlist = array();
		$count = $filtercount =0;
		while ($groupfeed = DB::fetch($query)) {
			$groupfeed[username]=user_get_user_name($groupfeed[uid]);
			$groupfeed = mkfeed($groupfeed);
			if($style[key]=='slim2'){
				$groupfeed = slimcutfeed($groupfeed,$parameter["content_len"],$parameter["summary_len"]);
			}else{
				$groupfeed = cutfeed($groupfeed,$parameter["content_len"],$parameter["summary_len"]);
			}
			if(ckicon_uid($groupfeed)) {
				if($groupfeed['dateline']>=$_G['home_today']) {
					$dkey = 'today';
				} elseif ($groupfeed['dateline']>=$_G['home_today']-3600*24) {
					$dkey = 'yesterday';
				} else {
					$dkey = dgmdate($groupfeed['dateline'], 'Y-m-d');
				}


				if(empty($groupfeed['hash_data'])) {
					if(empty($feed_users[$dkey][$groupfeed['uid']])) $feed_users[$dkey][$groupfeed['uid']] = $groupfeed;
					if(empty($uid_feedcount[$dkey][$groupfeed['uid']])) $uid_feedcount[$dkey][$groupfeed['uid']] = 0;

					$uid_feedcount[$dkey][$groupfeed['uid']]++;

					$feed_list[$dkey][$groupfeed['uid']][] = $groupfeed;

				} elseif(empty($hash_datas[$groupfeed['hash_data']])) {
					$hash_datas[$groupfeed['hash_data']] = 1;
					if(empty($feed_users[$dkey][$groupfeed['uid']])) $feed_users[$dkey][$groupfeed['uid']] = $groupfeed;
					if(empty($uid_feedcount[$dkey][$groupfeed['uid']])) $uid_feedcount[$dkey][$groupfeed['uid']] = 0;


					$uid_feedcount[$dkey][$groupfeed['uid']] ++;


					$feed_list[$dkey][$groupfeed['uid']][$groupfeed['hash_data']] = $groupfeed;

				} else {
					$user_list[$groupfeed['hash_data']][] = "<a href=\"home.php?mod=space&uid=$groupfeed[uid]\">$groupfeed[username]</a>";
				}


			} else {
				$filtercount++;
				$filter_list[] = $groupfeed;
			}
			$count++;
		}
		if(!$parameter[blockheight]){
			$parameter[blockheight]=300;
		}

		$parameter['aaid']='ticker'.rand(1,100);
        $result["parameter"] = $parameter;
        $result["feed_users"] = $feed_users;
		$result["feed_list"]=$feed_list;
		$other_info = common_category_is_other($_G["gp_fid"], 'topic');
		if($other_info[state]=='Y'){
			$result["is_enable_category"]=true;
		}else{
			$result["is_enable_category"]=false;
		}
		if($other_info[required]=='Y'){
			$result["required"]=true;
		}else{
			$result["required"]=false;
		}
		$url="forum.php?mod=group&fid=".$_GET["fid"];
		if($allcount>$parameter['items'] && $style[key]=='slim3'){
			$multipage = multi($allcount, $parameter['items'], $page, $url);
		}
		 $result[multi]=$multipage;
		 $categorys = common_category_get_category($_G["gp_fid"],'topic');
		 $result[categorys]=$categorys;
		//$result["more_list"]=$more_list;
       // print_r($result);
        return array('data' => $result);
    }

}
?>
