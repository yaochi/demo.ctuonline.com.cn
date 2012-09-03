<?php

/**
 * 规则积分调用接口，调用该接口，获得积分
 *
 * @param uid：sso用户id
 * @param action：事件代码，详见附表
 * @param
 *            resourceId：去重信息id，一些信息要防止重复
 *            例如：针对同一日志多次发表评论，只允许获得一次积分，这里的resourceId就是日志ID。
 * @return 返回获得的积分，奖励则为正分，惩罚则为负分
 */
function credit_create_credit_log($uid, $action, $resourceId) {
	global $_G;
	
	/*
	 * 增加积分开关
	 */
	if(!$_G[config][credit][switcher]){
		return false;
	}
	
    if(!$uid || !$resourceId){
        return false;
    }
    
	/*
	 * 如果是专家用户返回false
	 * added by fumz,2010-10-14 13:53:44
	 */
    require_once "function_group.php";
	if(check_is_expertuser($uid)){
		return false;
	}
    $now = credit_get_total_credit_by_uid($uid);
    
    require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    $souce = $credit_lt->createCreditLog($uid, $action, $resourceId);
    
    $notics = array(0, 0, $souce, 0, 0, 0, 0, 0, 0);
    $bases  = array(0, $now, 0, 0, 0, 0);
    $rules = array("");

    dsetcookie('creditnotice', implode('D', $notics).'D'.$uid, 43200);
    dsetcookie('creditbase', '0D'.implode('D', $bases), 43200);
    dsetcookie('creditrule', strip_tags(implode("\t", $rules)), 43200);
    
    return $souce;
}

/**
 * 根据用户id获取用户总积分
 *
 * @param uid：sso用户id
 * @return 总积分
 */
function credit_get_total_credit_by_uid($uid) {
	global $_G;
	if(!$_G[config][credit][switcher]){
		return false;
	}
    if(!$uid){
        return false;
    }
    require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    return $credit_lt->getTotalCreditByUid($uid);
}

/**
 * 非规则积分调用接口 如：悬赏积分
 *
 * @param fromUid：积分送出者id 如果是系统则为0
 * @param toUid：积分接收者id
 * @param action：积分事件，例如：悬赏积分
 * @param credit：积分
 * @param experience：经验值
 * @return
 * @throws Exception
 */
function credit_create_credit_log_random($fromUid,$toUid,$action,$credit) {
	global $_G;
	if(!$_G[config][credit][switcher]){
		return false;
	}
    if(!$fromUid && !$toUid){
        return false;
    }
    require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    return $credit_lt->createCreditLogRandom($fromUid,$toUid,$action,$credit,0);
}


/**
 * 返回积分排行榜信息
 * 
 * @param currentPage：当前页
 * @param pageSize:每页大小
 * @return 积分排行列表
 */
function get_credit_rank($currentPage,$pageSize){
	global $_G;
	if(!$_G[config][credit][switcher]){
		return false;
	}
	require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    return $credit_lt->getCreditRank($currentPage,$pageSize);
}


function credit_get_credit_log($uid){
    global $_G;
	if(!$_G[config][credit][switcher]){
		return false;
	}
    require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    $data = $credit_lt->getCreditLogsByUid($uid);
    $result = array();
    foreach($data as $item){
        if($_G[config][misc][convercode]){
            $item[rulename] = mb_convert_encoding($item[rulename], "UTF-8", "GBK");
        }
        $item[dateLine] = $item[dateLine]/1000;
        $result[] = $item;
    }
    return $result;
}

/**
 * 查询积分规则列表
 *
 * @return 积分规则实体列表
 */
function get_credit_rules(){
	global $_G;
	if(!$_G[config][credit][switcher]){
		return false;
	}
	require_once(dirname(dirname(__FILE__)) . "/api/lt_credit/lt_credit_api.php");
    $credit_lt = new credit_lt();
    return $credit_lt->getCreditRules();
}
?>
