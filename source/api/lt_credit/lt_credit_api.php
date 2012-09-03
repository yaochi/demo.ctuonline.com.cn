<?php

define('LT_CREDIT_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//配置文件
require_once(dirname(dirname(__FILE__)).'/common/config_lt_msg.php');

//mssqlserver数据库操作文件
require_once(dirname(dirname(__FILE__)).'/common/db/mssql_class_lt.php');


//积分memcache
require_once(dirname(dirname(__FILE__)).'/common/memcache_credit_util.php');



define('RT_SUCCESS',0);	//操作成功
define('RT_FAIL',1); 	//操作失败

set_time_limit(0);//本函数用来配置该页最久执行时间。若配置为0则不限定最久时间

class credit_lt {

	private $mssql4zh ;//mssql数据库链接类

	/**
	 * 构造函数
	 */
	function credit_lt() {

	}


	/***
	 * 获得积分日志规则 (新增)
	 * 2011.04.11 by wuj
	 */
	private function getLogByUidAndRuleFromMemOrDB($uid,$creditRule){

		global $memcache_credit;
		global $memcacheStatus; //是否启用缓存
		global $memc_flag; //分隔符
		global $tname_lt;

		if(null == $creditRule){
			throw new Exceptoin("获取日志规则出错了，creditRule不可为空！");
		}
		$reluid = $creditRule['id'];
		$creditRule= array();
		$key = 'cl_'.$uid.'_'.$reluid;
		if(1==$memcacheStatus){//开启memcache

			$cache = $memcache_credit->get($key);
			if(!empty($cache)){
				//cache中存在
				$creditLog = json_decode($cache,true);
				return $creditLog;
			}
		}

		//没有开启memcache 或者memcache中没有
		//从数据库中查询
		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$sql =	"select id,uid,ruleid,action,total,cyclenum,credit,experience,starttime,dateline,rewardtype,appids,userids,infoids from ".$tname_lt['LTCREDIT_LOG']." where ruleid='".$reluid."' ".
				"and uid='" . $uid . "' and normal_rule = 1";

		$query=$this->mssql4zh->Query($sql);
		$creditLog= $this->mssql4zh->GetRow($query);

		$this->mssql4zh->Close();
		$this->mssql4zh=null;

		//放置到memcache中
		if(1==$memcacheStatus){//开启memcache
			if(!empty($creditLog)){
				$memcache_credit->set($key,json_encode($creditLog),$memc_flag);
			}
		}
		return $creditLog;

	}
	/***
	 * 修改用户总积分(新增)
	 * 2011.04.11 by wuj
	 */
	private function updateCreditToMemOrDB($credit,$uid){
		global $memcache_credit;
		global $memcacheStatus; //是否启用缓存
		global $memc_flag; //分隔符
		global $tname_lt;
		if(null == $credit){
			throw new Exceptoin("修改用户总积分出错了，credit不可为空！");
		}
		if(null == $uid){
			throw new Exceptoin("修改用户总积分出错了，uid不可为空！");
		}


		//修改 by songsp 2011-4-11 21:52:45
		//获得用户总积分和总经验值
		$totals = $this->getTotalCreditAndExperienceByUidFromMemOrDB($uid);


		$key = 'uc_'.$uid;
		if(1==$memcacheStatus){//开启memcache
			 //$total = $this->getTotalCreditByUid($uid);
			 //$obj = $memcache_credit->get($key);
			// $obj2 =  json_decode($obj,true);
			// $arr = (array)$obj2;
			 $arr = $totals;
		     $arr["c"] = ((int)$totals['c'] + $credit);
			 $memcache_credit->set($key, json_encode($arr),$memc_flag);
			 return;
		}
		//没有开启memcache 或者memcache中没有
		//从数据库中查询
		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$update_user_total_credit = "update lt_user_credit_total set credit=credit+(" . $credit . ") where uid=" . $uid ;
		$this->mssql4zh->Query($update_user_total_credit);
		$this->mssql4zh->Close();
		$this->mssql4zh=null;
	}
	/***
	 * 修改用户积分日志(新增)
	 * 2011.04.11 by wuj
	 */
	private function updateCreditLogToMemOrDB($creditLog,$credit){
		global $memcache_credit;
		global $memcacheStatus; //是否启用缓存
		global $memc_flag; //分隔符
		global $tname_lt;

		if(null == $creditLog){
			throw new Exceptoin("获取日志规则出错了，creditLog不可为空！");
		}
		$key = 'cl_'.$creditLog['uid'].'_'.$creditLog['ruleid'];
		if(1==$memcacheStatus){//开启memcache
			$memcache_credit->set($key, json_encode($creditLog),$memc_flag);

			//记录用户更新的规则
			$key = 'a_'.$creditLog['uid'];
			$string = $memcache_credit->get($key);
 			if(!empty($string)){
 				$find = ",".$creditLog['ruleid'].",";
 				$str = ','.$string;
				if(strpos($str,$find) === false){
					$string = $string.$creditLog['ruleid'].",";
					$memcache_credit->set($key,$string,$memc_flag);
				}

			}else{
				$string = $creditLog['ruleid'].",";
				$memcache_credit->set($key,$string,$memc_flag);
			}

			return;
		}

		//没有开启memcache 或者memcache中没有
		//从数据库中查询
		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}

		$update_credit_log = "update " . $tname_lt['LTCREDIT_LOG'] . " set ";
		$update_credit_log .= (" total=" . $creditLog['total'] . ", ");
		$update_credit_log .= (" cycleNum=" . $creditLog['cyclenum'] . ", ");
		$update_credit_log .= (" credit=" . $creditLog['credit'] . ", ");
		$update_credit_log .= (" experience=" . $creditLog['experience'] . ", ");
		$update_credit_log .= (" startTime=" . $creditLog['starttime'] . ", ");
		$update_credit_log .= (" dateLine=" . $creditLog['dateline'] . ", ");
		$update_credit_log .= (" appids='" . $creditLog['appids'] . "', ");
		$update_credit_log .= (" userids='" . $creditLog['userids'] . "', ");
		$update_credit_log .= (" infoids='" . $creditLog['infoids'] . "' ");
		$update_credit_log .= (" where uid='" . $creditLog['uid'] . "' and ruleid='" . $creditLog['ruleid'] . "' ");
		$this->mssql4zh->Query($update_credit_log);
		$this->mssql4zh->Close();
		$this->mssql4zh=null;
	}
	/**
	 * 规则积分调用接口，调用该接口，获得积分
	 * 修改于2011.04.11 by wuj
	 * @param uid：sso用户id
	 * @param action：时间代码，详见附表
	 * @param
	 *            resourceId：去重信息id，一些信息要防止重复
	 *            例如：针对同一日志多次发表评论，只允许获得一次积分，这里的resourceId就是日志ID。
	 * @return 返回获得的积分，奖励则为正分，惩罚则为负分
	 */
	public function createCreditLog($uid, $action, $resourceId) {
		global $memcache_credit;
		global $memcacheStatus; //是否启用缓存
		global $memc_flag; //分隔符
		global $tname_lt;

		$credit = 0;
		if (0 == $uid) {
			throw new Exception("规则积分调用接口出错了，原因是未传入uid！");
		}
		if ("" == $action) {
			throw new Exception("规则积分调用接口出错了，原因是未传入action！");
		}
		$creditRule = $this->getCreditRuleByAction($action);

		if(null == $creditRule){
			throw new Exceptoin("积分调用接口失败，原因是传入的事件代码错误！");
		}
		$creditLog = $this->getLogByUidAndRuleFromMemOrDB($uid,$creditRule);
			//不存在积分日志
			if (empty ($creditLog)) {
				$values = array (
					"uid" => $uid,
					"ruleid" => $creditRule['id'],
					"action" => $creditRule['action'],
					"total" => 1,
					"cycleNum" => 1
				);
				//奖励
				if ("1" == $creditRule['rewardtype']) {
					$values["credit"] = $creditRule['credit'];
					$values["experience"] = $creditRule['experience'];
					$credit = $creditRule['credit'];
				}
				//惩罚
				else {
					$values["credit"] = $creditRule['credit'] * (-1);
					$values["experience"] = $creditRule['experience'] * (-1);
					$credit = $creditRule['credit'] * (-1);
				}
				$values["startTime"] = number_format(time() * 1000, 0, '', '');
				$values["dateLine"] = number_format(time() * 1000, 0, '', '');
				$values["rewardType"] = $creditRule['rewardtype'];
				$values["appids"] = "";
				$values["userids"] = "";
				$values["infoids"] = "";
				//add by songsp 2011-4-11 22:02:12
				$values['normal_rule']=1;
				$values['rulename'] = $creditRule['rulename'];
				//去重:第一次一定不会重复，调用该方法的目的是为了添加重复的id
				$this->getCheckCheating(&$values, &$creditRule, $resourceId);
				if($memcacheStatus == 1){   //把积分日志更新到memcache
					$arrLog = array(
							'id'=>	'-1',
							'uid'=>	$values['uid'],
							'rulename'=> $values['rulename'],
							'ruleid'=>	$values['ruleid'],
							'action'=>	$values['action'],
							'total'=>	$values['total'],
							'cyclenum'=>	$values['cycleNum'],
							'credit'=>	$values['credit'],
							'experience'=>	$values['experience'],
							'starttime'=>	$values['startTime'],
							'dateline'=>	$values['dateLine'],
							'rewardtype'=>	$values['rewardType'],
							'appids'=>	$values['appids'],
							'userids'=>	$values['userids'],
							'infoids'=>	$values['infoids']
							);
					$key = 'cl_'.$uid.'_'.$values['ruleid'];
					$memcache_credit->set($key, json_encode($arrLog),$memc_flag);
					
					//修改by songsp  2011-4-14 16:56:59
					//记录用户更新的规则
					$key = 'a_'.$values['uid'];
					$string = $memcache_credit->get($key);
		 			if(!empty($string)){
		 				$find = ",".$values['ruleid'].",";
		 				$str = ','.$string;
						if(strpos($str,$find) === false){
							$string = $string.$values['ruleid'].",";
							$memcache_credit->set($key,$string,$memc_flag);
						}
		
					}else{
						$string = $values['ruleid'].",";
						$memcache_credit->set($key,$string,$memc_flag);
					}
					
					
				}else{
					//保存积分日志
					if (empty ($this->mssql4zh)) {
						$this->mssql4zh = new mssql4lt();
					}
					if(empty($this->mssql4zh->link)){
						return '--';
					}
					$this->mssql4zh->Insert($tname_lt['LTCREDIT_LOG'], $values);
					$this->mssql4zh->Close();
					$this->mssql4zh = null;
				}
			} else {
				//根据周期规则，判断是否应该获得积分：true-获得，false-不获得
				$flag = $this->checkCycle(& $creditLog, &$creditRule, $resourceId);
				if ($flag) {
					//奖励
					if ("1" == $creditRule['rewardtype'])
						$credit = $creditRule['credit'];
					//惩罚
					else
						$credit = $creditRule['credit'] * (-1);
					//更新积分日志
					$this->updateCreditLogToMemOrDB($creditLog,$credit);
				}
			}

		if ($credit != 0) {
			//更改用户总积分
			$this->updateCreditToMemOrDB($credit,$uid);
		}

		return $credit;
	}




	/**
	 * 根据用户id获取用户总积分
	 *
	 *  修改 by songsp  2011-4-11 13:30:31    添加 memcache
	 *
	 * @param uid：sso用户id
	 * @return 总积分
	 */
	public function getTotalCreditByUid($uid){

		if(!isset($uid)){
			throw new Exceptoin("获取用户的积分出错了，uid不可为空！");
		}

		$credit=0;
		$totals = $this->getTotalCreditAndExperienceByUidFromMemOrDB($uid);

		if(!empty($totals)){
			$credit = $totals['c'];
		}

		return $credit;
	}

	/**
	 * 根据用户id查询用户所有积分日志
	 *
	 * @param uid：sso用户id
	 * @return 积分日志列表
	 */
	public function getCreditLogsByUid($uid){
		global $tname_lt;

		if(!isset($uid)){
			throw new Exceptoin("获取积分日志出错了，uid不可为空！");
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$creditLog= array();
		$num = 0;

		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$sql = " select * from ".$tname_lt['LTCREDIT_LOG']."  where uid='".$uid."' order by dateLine desc ";
		$query=$this->mssql4zh->Query($sql);
		while($row= mssql_fetch_array($query)){
			$creditLog[$num++] = $row;
			//$creditLog[$num-1]['rulename']=iconv("gb2312","UTF-8", $creditLog[$num-1]['rulename']);
		}

		$this->mssql4zh->Close();
		$this->mssql4zh=null;

		return $creditLog;
	}

	/**
	 * 根据事件代码查询积分规则  数值key值全部小写
	 *
	 * 修改 by songsp  2011-4-11 10:48:42    添加memcache
	 *
	 * @param action：事件代码，详见附件
	 * @return 积分规则实体
	 */
	public function getCreditRuleByAction($action){

		global $tname_lt;
		global $memc_flag ;
		global $memcacheStatus;
		global $memcache_credit;


		if(!isset($action)){
			throw new Exceptoin("获取积分规则出错了，action不可为空！");
		}

		$creditRule= array();


		$key = 'cr_'.$action ;
		if(1==$memcacheStatus){//开启memcache

			$cache = $memcache_credit->get($key);
			if(!empty($cache)){
				//cache中存在
				$creditRule = json_decode($cache,true);

				return $creditRule;
			}
		}



		//没有开启memcache 或者memcache中没有

		//从数据库中查询



		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$sql = " select id, rulename, action ,cycletype,cycletime,rewardnum,rewardtype,norepeat,credit,experience from ".$tname_lt['LTCREDIT_RULE']."  where action='".$action."'";
		$query=$this->mssql4zh->Query($sql);
		$creditRule= $this->mssql4zh->GetRow($query);

		$this->mssql4zh->Close();
		$this->mssql4zh=null;




		//放置到memcache中
		if(1==$memcacheStatus){//开启memcache

			$memcache_credit->set($key,json_encode($creditRule),$memc_flag);

		}

		return $creditRule;
	}

	/**
	 * 查询积分规则列表
	 *
	 * @return 积分规则实体列表
	 */
	public function getCreditRules(){

		global $tname_lt;

		$creditRules= array();
		$num = 0;

		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$sql = " select * from ".$tname_lt['LTCREDIT_RULE'];
		$query=$this->mssql4zh->Query($sql);
		while($row= mssql_fetch_array($query)){
			$creditRules[$num++] = $row;
			//$creditRules[$num-1]['ruleName']=iconv("gb2312","UTF-8", $creditRules[$num-1]['ruleName']);
		}

		$this->mssql4zh->Close();
		$this->mssql4zh=null;

		return $creditRules;
	}

	/**
	 * 判断是否去重，返回true表示重复，需要去重，返回false表示不存在重复或者无须去重
	 *
	 * @param $creditLog：积分日志
	 * @param $creditLog：积分规则
	 * @param $creditLog：去重id
	 * @return true or false
	 */
	private function getCheckCheating($creditLog,$creditRule,$resourceId){
		$result=false;
     	//无需做去重校验
		if("0"==$creditRule["norepeat"])
			return $result;
		if("0"!=$creditRule["norepeat"] && empty($resourceId)){
			throw new Exception("积分接口调用出错了，需要去掉重复的id为空！");
		}

		//0：不去重 1：针对信息去重 2：针对用户去重 3：针对应用去重
		switch($creditRule["norepeat"]){
			case "0":
			{
				$result=false;
			}
				break;
			case "1":
			{
				if(!empty($creditLog["infoids"])){
					if(strstr($creditLog["infoids"],$resourceId.",")){
						$result=true;
					}

				}
				if(!$result)
					$creditLog["infoids"].=($resourceId.",");
			}
				break;
			case "2":
			{
				if(!empty($creditLog["userids"])){
					if(strstr($creditLog["userids"],$resourceId.","))
						$result=true;
				}
				if(!$result)
					$creditLog["userids"].=($resourceId.",");
			}
				break;
			case "3":
			{
				if(!empty($creditLog["appids"])){
					if(strstr($creditLog["appids"],$resourceId.","))
						$result=true;
				}
				if(!$result)
					$creditLog["appids"].=($resourceId.",");
			}
				break;
		}

		return $result;
	}

	/**
	 * 通过周期判断是否添加积分
	 *
	 * @param $creditLog：积分日志
	 * @param $creditLog：积分规则
	 * @param $creditLog：去重id
	 * @return true or false
	 */
	private function checkCycle($creditLog,$creditRule,$resourceId){
		$result=false;
		$insideCycle=false;

		//奖励
		if("1"==$creditRule['rewardtype']){
			$curTime=number_format(time()*1000,0,'','');//当前时间
			$startTime=$creditLog['starttime'];//本周内第一次奖励时间
			$lastTime=$creditLog['dateline'];//最后一次奖励时间
			//奖励周期： 0：一次性 1：每天 2：整点 3：间隔分钟 4：不限周期
			switch($creditRule["cycletype"]){
				case "0"://一次性
				{
					$insideCycle=false;
					$result=false;
				}
					break;
				case "1"://每天
				{
					//同一天
					if($startTime + 24 * 60 * 60 * 1000 > $curTime){
						$insideCycle=true;
					}
					//不是同一天
					else{
						$insideCycle=false;
					}
				}
					break;
				case "2"://整点
				{
					//判断是否在一个周期内
					if($startTime + $creditRule['cycletime'] * 60 * 60 * 1000 > $curTime){
						$insideCycle=true;
					}
					//不在同一个周期内
					else{
						$insideCycle=false;
					}
				}
					break;
				case "3"://间隔分钟
				{
					//判断是否在一个周期内
					if($startTime + $creditRule['cycletime'] * 60 * 1000 > $curTime){
						$insideCycle=true;
					}
					//不在同一个周期内
					else{
						$insideCycle=false;
					}
				}
					break;
				case "4"://不限周期
				{
					$insideCycle=true;
				}
					break;
			}
			//如果在周期内

			if($insideCycle){
				//去重
				if($this->getCheckCheating(&$creditLog,&$creditRule,$resourceId)){
					$result=false;
				}else{
					//周期内奖励次数未满，可以再奖励，若最多奖励次数为0，则表示无限次奖励
					if($creditRule['rewardnum']==0 || $creditLog['cyclenum']<$creditRule['rewardnum']){
						$creditLog['total']=$creditLog['total']+1;
						$creditLog['cyclenum']=$creditLog['cyclenum']+1;
						$creditLog['credit']=$creditRule['credit'];
						$creditLog['experience']=$creditRule['experience'];
						$creditLog['dateline']=number_format(time()*1000,0,'','');

						$result=true;
					}else{
						$result=false;
					}
				}
			}
			//若不在周期内
			if(!$insideCycle && "0"!=$creditRule["cycletype"]){
				//周期内清零
				$this->updateClearCheckCheating(&$creditLog,&$creditRule,$resourceId);

				$creditLog['total']=$creditLog['total']+1;
				$creditLog['cyclenum']=1;
				$creditLog['credit']=$creditRule['credit'];
				$creditLog['experience']=$creditRule['experience'];
				$creditLog['dateline']=number_format(time()*1000,0,'','');
				$creditLog['starttime']=number_format(time()*1000,0,'','');

				$result=true;

				//如果不是同一天，则一定不会重复，调用该方法的目的是添加去重id
				$this->getCheckCheating(&$creditLog,&$creditRule,$resourceId);
			}
		}
		//惩罚
		else{
			$creditLog['total']=$creditLog['total']+1;
			$creditLog['cyclenum']=$creditLog['cyclenum']+1;
			$creditLog['credit']=$creditRule['credit']*(-1);
			$creditLog['experience']=$creditRule['experience']*(-1);
			$creditLog['dateline']=number_format(time()*1000,0,'','');

			$result=true;
		}

		return $result;
	}

	/**
	 * 周期初始化
	 */
	private function updateClearCheckCheating($creditLog,$creditRule,$resourceId){
		//无需去重
		if("0"==$creditRule['norepeat'])
			return;

		// 去重奖励:'0'无,'1'针对信息去掉重复,'2'针对用户去掉重复,'3'针对应用去重
		switch($creditRule["norepeat"]){
			case "1":
					$creditLog["infoids"]="";
				break;
			case "2":
					$creditLog["userids"]="";
				break;
			case "3":
					$creditLog["appids"]="";
				break;
		}

		$creditLog['cyclenum']=0;// 周期内次数清零
	}

	/**
	 * 非规则积分调用接口 如：悬赏积分
	 *
	 * 修改 by songsp  2011-4-11 12:14:17  添加 memcache
	 *
	 * @param fromUid：积分送出者id
	 * @param toUid：积分接收者id
	 * @param action：积分事件，例如：悬赏积分
	 * @param credit：积分
	 * @param experience：经验值
	 * @return
	 * @throws Exception
	 */
	public function createCreditLogRandom($fromUid,$toUid,$action,$credit,$experience){
		global $tname_lt;
		global $memc_flag ;
		global $memcacheStatus;
		global $memcache_credit;

		$now=number_format(time()*1000,0,'','');

		if(0==$toUid){
			throw new Exception("非规则积分接口调用出错了，原因是未传入toUid！");
		}
		if(""==$action){
			throw new Exception("非规则积分接口调用出错了，原因是未传入action！");
		}else{
			$action=iconv("UTF-8","gb2312", $action);
		}

		//print_r($action);

		//开启memcache
		if(1==$memcacheStatus){
			$key = 'randomcredit';

			$cache = $memcache_credit->get($key);
			if(!empty($cache)){
				$randlogs = json_decode($cache,true);
			}else{
				$randlogs = array();
			}

			$randlogs[] = array(
				"fromuid"=>$fromUid,
				"touid"=>$toUid,
				"action"=>$action,
				"credit"=>$credit,
				"experience"=>$experience,
				"dateline"=>$now

			);

			$memcache_credit->set($key,json_encode($randlogs),$memc_flag);



			//更新用户总积分
			if($credit!=0){

				$total_credit = 0;
				$total_experience = 0;
				$totals = $this->getTotalCreditAndExperienceByUidFromMemOrDB($toUid); //获得用户总积分和总经验值

				if($totals ){

					$total_credit = (int)$totals['c'];
					$total_experience = (int)$totals['e'];

				}
				$total_credit = $total_credit + $credit;
				$total_experience = $total_experience + $experience;

				//更新用户总积分信息
				$jsonVO = array(
					"c"=>$total_credit,
					"e"=>$total_experience
				);
				//uc_{uid}
				$key = 'uc_'.$toUid ;
				$memcache_credit->set($key,json_encode($jsonVO),$memc_flag);

				if(0!=$fromUid){

					$total_credit = 0;
					$total_experience = 0;
					$totals = $this->getTotalCreditAndExperienceByUidFromMemOrDB($fromUid); //获得用户总积分和总经验值

					if($totals ){

						$total_credit = (int)$totals['c'];
						$total_experience = (int)$totals['e'];

					}
					$total_credit = $total_credit - $credit;
					$total_experience = $total_experience - $experience;

					//更新用户总积分信息
					$jsonVO = array(
						"c"=>$total_credit,
						"e"=>$total_experience
					);
					//uc_{uid}
					$key = 'uc_'.$fromUid ;
					$memcache_credit->set($key,json_encode($jsonVO),$memc_flag);

				}





			}

			return ;
		}




		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		//保存接受者积分日志
		$toValues=array("uid"=>$toUid,"ruleid"=>'0',"action"=>$action,"rulename"=>$action,"total"=>1,"cycleNum"=>1,"credit"=>$credit,"experience"=>$experience,"startTime"=>$now,"dateLine"=>$now,"rewardType"=>"1","normal_rule"=>0);
		$this->mssql4zh->Insert($tname_lt['LTCREDIT_LOG'],$toValues);
		if($credit!=0){
			//更改用户总积分
			$update_to_user_total_credit="update lt_user_credit_total set credit=credit+".$credit." where uid='".$toUid."'";
			$this->mssql4zh->Query($update_to_user_total_credit);
		}

		if(0!=$fromUid){
			//保存发送者积分日志
			$fromValues=array("uid"=>$fromUid,"ruleid"=>0,"action"=>$action,"rulename"=>$action,"total"=>1,"cycleNum"=>1,"credit"=>$credit*(-1),"experience"=>$experience*(-1),"startTime"=>$now,"dateLine"=>$now,"rewardType"=>"0","normal_rule"=>0);
			$this->mssql4zh->Insert($tname_lt['LTCREDIT_LOG'],$fromValues);
			if($credit!=0){
				//更改用户总积分
				$update_from_user_total_credit="update lt_user_credit_total set credit=credit-".$credit." where uid='".$fromUid."'";
				$this->mssql4zh->Query($update_from_user_total_credit);
			}
		}

		$this->mssql4zh->Close();
		$this->mssql4zh=null;
		return 	$credit;
	}

	/**
	 * 返回积分排行榜信息
	 *
	 * @param currentPage：当前页
	 * @param pageSize:每页大小
	 * @return 积分排行列表
	 */
	public function getCreditRank($currentPage,$pageSize){
		global $tname_lt;

		if((empty($currentPage) && 0!=$currentPage) || (empty($pageSize) && 0!=$pageSize)){
			$currentPage = 0;
			$pageSize = 20;
		}

		$creditRank= array();
		$num = 0;

		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}

		$sql = "select top ".$pageSize." user1.id , user1.name, user1.login_name,tc1.credit from ".$tname_lt['COMMON_USER']." user1  ".
		"left join  lt_user_credit_total tc1  on user1.id=tc1.uid where user1.status='1'  and user1.id not in (".
		" select top ".($currentPage != 0 ? $currentPage - 1 : $currentPage)* $pageSize." user2.id  from ".$tname_lt['COMMON_USER']." user2 ".
		"left join lt_user_credit_total tc2  on user2.id=tc2.uid where user2.status='1' order by  tc2.credit desc ".
		") order by  tc1.credit desc ";


		//print_r($sql);
		$query=$this->mssql4zh->Query($sql);
		while($row= mssql_fetch_array($query)){
			$creditRank[$num]['id'] = $row['id'];
			$creditRank[$num]['name'] = $row['name'];
			$creditRank[$num]['loginName'] = $row['login_name'];
			$creditRank[$num]['credit'] = $row['credit'];
			$num++;
		}

		$this->mssql4zh->Close();
		$this->mssql4zh=null;

		return $creditRank;
	}


	// 从memcache 或 数据库中获取用户总积分和总经验值 返回数组   c:总积分  e：经验值
	private   function getTotalCreditAndExperienceByUidFromMemOrDB($uid){
		global $tname_lt;
		global $memc_flag ;
		global $memcacheStatus;
		global $memcache_credit;

		if(!isset($uid)){
			throw new Exceptoin("getTotalCreditAndExperienceByUidFromMemOrDB出错了，uid不可为空！");
		}

		$key = 'uc_'.$uid;

		if(1==$memcacheStatus){



			$cache = $memcache_credit->get($key);

			if(!empty($cache)){
				return json_decode($cache,true);

			}

		}

		//memcache中没有

		$credit=0;
		$experience=0;
		//初始化mssql数据库链接
		if(empty($this->mssql4zh)){
			$this->mssql4zh=new mssql4lt();
		}
		if(empty($this->mssql4zh->link)){
			return '--';
		}
		$sql = " select credit , experience from lt_user_credit_total  where uid=".$uid;
		$query=$this->mssql4zh->Query($sql);
		if($row=$this->mssql4zh->GetRow($query)){
			$credit = $row['credit'];
			$experience = $row['experience'];
		}else{
			//数据库中没有
			$sql = "insert into lt_user_credit_total(uid ,credit,experience) values (".$uid.",0,0)";

			$this->mssql4zh->Query($sql);

		}
		$this->mssql4zh->Close();
		$this->mssql4zh=null;

		$rs = array(
			'c'=>$credit,
			'e'=>$experience
		);
		//放到memcache中
		if(1==$memcacheStatus){
			$memcache_credit->set($key,json_encode($rs),$memc_flag);

		}

		return $rs;

	}

}


?>