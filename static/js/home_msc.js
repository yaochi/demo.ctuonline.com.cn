/* 搜索用户/专区 */
/* Author: Izzln
   根据用户屏幕高度计算每页条数
   Prefetch策略：
      基本信息 x 6;
      所在机构 x 6;
      follow关系 x 4;
*/
   
var mscobj = jQuery(".msc");
var mscOriHeight = mscobj.height();
var mscInput = jQuery("input[name=hsinput]", mscobj);
var mscInputVal;
var mscResult = jQuery("#hsResults", mscobj);
var mssOriStr = mscInput.val();
var mscResultIsAnimating, isLoadingSCResults = false;
var srecordHeight = 72;
var params = { "showNumStep": 0, "memberCount": 0, "memberPrefetchNum": 0, "groupCount": 0, "groupPrefetchNum": 0, "memberPageIndex": 0, "groupPageIndex": 0 };
var pfParams = { "basic": 6, "province": 6, "rel": 4 };
/* MoG[0]: 接口返回用户/专区是否存在结果 0: 仅存在用户结果或存在用户+专区结果；1: 仅存在专区结果
   MoG[1]: 用户手工指定查看用户/专区结果 0: 用户未设置或用户指定查看用户结果；1: 用户指定查看专区结果
   
   两者或运算后结果：0: 显示用户结果；1: 显示专区结果
*/
var MoG = [0, 0];

var mscInputCtrl = function(event) {
	mscInputVal = jQuery.trim(mscInput.val());

	if(!isLoadingSCResults && mscInput.val() != "") {
		jQuery(".mscl", mscobj).css("display", "block");
	} else {
		jQuery(".mscl", mscobj).css("display", "none");
		clrResult();
	}

	if(event.keyCode == 13 && mscInputVal != "") {
		clrResult();
		jQuery(".mscl", mscobj).css("display", "none");
		jQuery(".mscloading", mscobj).css("display", "block");
		mscInput.attr("disabled", true);
		
		jQuery.each(params, function(i) {
			params[i] = 0;
		});
		params.showNumStep = Math.floor((jQuery(window).height() - 100) / srecordHeight);
		if(params.showNumStep > 12) {
			params.showNumStep = 12;		//防止列表超过素材长度
		}
		isLoadingSCResults = true;
		showSCResults(0);
	}
};

function showSCResults(pageIndex) {
	if(isLoadingSCResults) {
		var scDTD = new jQuery.Deferred();
		fetchSCResults(mscInputVal, 0, params.showNumStep * pfParams.basic, scDTD);
		jQuery.when(scDTD.promise()).done(function() {
			var extendHSRsult = function() {
				mscobj.parent().addClass("mscresult");
				mscResultIsAnimating = true;
				mscobj.animate({
					//高度由原本搜索框高度＋搜索结果高度＋loading图片与所在省份文字高度修正值相加而得
					height: mscobj.height() + jQuery("#hsResults", mscobj).outerHeight() + jQuery("#memberList dl:visible", mscResult).length * 7
				}, 300, 'swing', function() {
					mscResultIsAnimating = false;
					mscResult.css("position", "static");
					mscResult.css("visibility", "visible");
				});
				
				mscInput.attr("disabled", false);
			};
		
			if(params.memberCount == 0 && params.groupCount == 0) {
				mscResult.html("<p class='xg1 hm mlmgroup mrmgroup mtn'>非常抱歉&nbsp;没有找到包含<span class='xw1'>\"" + mscInputVal +"\"</span>名称的用户或专区</p>");
				extendHSRsult();
			} else {			
				if(MoG[0]) {
					mscResult.append('<div class="resTitle"><span class="xw1">专区(' + (params.groupCount > 200 ? '200+' : params.groupCount) + ')</span></div>');
					mscResult.append('<div id="groupList"></div>');
					jQuery("#groupListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
						jQuery("#groupList", mscResult).append(jQuery(this));
					});
					jQuery("#groupList dl:last", mscResult).removeClass("bbda");
					if(jQuery("#groupListBk dl", mscResult).length > 0) {
						jQuery("#groupList", mscResult).append('<p id="groupLICtl">'
															   + '<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y">下一页&gt;</a>'
															   + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'group\');" class="z">查看全部</a></p>'
															   + '<span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>');
					}
					extendHSRsult();
				} else {
					if(params.memberCount > 0 && params.groupCount > 0) {
						mscResult.append('<div class="resTitle"><span><a id="memberTitle" href="javascript:switchSCPanel(\'member\');" class="xi2 xw1">用户(' + (params.memberCount > 200 ? '200+' : params.memberCount) + ')</a></span><span><a id="groupTitle" href="javascript:switchSCPanel(\'group\');">专区(' + (params.groupCount > 200 ? '200+' : params.groupCount) + ')</a></span>');
					} else if(params.memberCount > 0 ) {
						mscResult.append('<div class="resTitle"><span class="xw1">用户(' + (params.memberCount > 200 ? '200+' : params.memberCount) + ')</span></div>');
					}
					
					var scrRelDTD = new jQuery.Deferred();
					fetchSCRRelationship(jQuery("#memberListBk dl:lt(" + params.showNumStep * pfParams.rel + ")", mscResult), scrRelDTD);
					fetchSCRProvince(jQuery("#memberListBk dl:lt(" + params.showNumStep * pfParams.province + ")", mscResult));
					jQuery.when(scrRelDTD.promise()).done(function() {
						mscResult.append('<div id="memberList"></div>');
						jQuery("#memberListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
							jQuery("#memberList", mscResult).append(jQuery(this));
						});
						fetchFeedAuth('#memberList'); //获取账户认证信息
						jQuery("#memberList dl:last", mscResult).removeClass("bbda");
						if(jQuery("#memberListBk dl", mscResult).length > 0) {
							jQuery("#memberList", mscResult).append('<p id="memberLICtl">'
																   + '<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y">下一页&gt;</a>'
																   + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'member\');" class="z">查看全部</a></p>'
																   + '<span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>');
						}
						extendHSRsult();
					});
					
					if(params.groupCount > 0) {
						mscResult.append('<div id="groupList" style="display: none;"></div>');
						jQuery("#groupListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
							jQuery("#groupList", mscResult).append(jQuery(this));
						});
						jQuery("#groupList dl:last", mscResult).removeClass("bbda");
						if(jQuery("#groupListBk dl", mscResult).length > 0) {
							jQuery("#groupList", mscResult).append('<p id="groupLICtl">'
																   + '<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y">下一页&gt;</a>'
																   + '<span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>'
																   + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'group\');" class="z">查看全部</a></p>');
						}
					}
				}
			}
		}).fail(function() {
			showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>无法完成查询操作 请稍后再次尝试</span>", 3000);
		});	
	} else {
		if(MoG[0] || MoG[1]) {
			jQuery("#groupList dl", mscResult).css("display", "none");
			jQuery("#groupLICtl", mscResult).addClass("hm");
			jQuery("#groupLICtl", mscResult).html('<img src="static/image/common/loading.gif" />');

			if(pageIndex > params.groupPageIndex) {
				if(params.groupCount - params.groupPrefetchNum > 0 && jQuery("#groupListBk dl", mscResult).length == 0) {
					needReRunFlag = true;
				}
				
				var stdProcedure = function() {
					jQuery("#groupListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
						jQuery("#groupList", mscResult).append(jQuery(this));
					});
					jQuery("#groupList dl:last", mscResult).removeClass("bbda");
					
					jQuery("#groupLICtl", mscResult).html('<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y mlmgroup" '
										 + (params.groupCount - (pageIndex + 1) * params.showNumStep > 0 ? '' : 'style="visibility: hidden;"')
									     + '>下一页&gt;</a><a href="javascript:showSCResults(' + (pageIndex - 1) + ')" class="y">&lt;上一页</a>'
					                     + '<span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>'
					                     + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'group\');" class="z">查看全部</a>');
					jQuery("#groupLICtl", mscResult).removeClass("hm");
					jQuery("#groupList", mscResult).append(jQuery("#groupLICtl", mscResult));
										
					adjustHSResults();
					params.groupPageIndex = pageIndex;
					
					//Prefetch
					if((params.groupPrefetchNum - pageIndex * params.showNumStep < params.showNumStep * pfParams.basic) && (pageIndex % pfParams.basic == Math.floor(pfParams.basic / 2)) && (params.groupCount - params.groupPrefetchNum > 0)) {
						var sssDTD = new jQuery.Deferred();
						fetchSCResults(mscInputVal, params.groupPrefetchNum, params.showNumStep * pfParams.basic, sssDTD);
						jQuery.when(sssDTD.promise()).done(function() {
							if(needReRunFlag) {
								stdProcedure();
							}
							//alert("params.groupPrefetchNum:" + params.groupPrefetchNum);
							
						});
					}					
				};
								
				stdProcedure();
			} else {
				var k = [];
				jQuery("#groupList dl:gt(" + (params.groupPageIndex * params.showNumStep - 1) + ")", mscResult).each(function() {
					k.push(jQuery(this).attr("id"));
				});
				k.reverse();
				jQuery.each(k, function(key, value) {
					jQuery("#groupListBk", mscResult).prepend(jQuery("#" + value));
					jQuery("#" + value).css("display", "block");
				});
				
				jQuery("#groupList dl", mscResult).filter(function(i) {
					return i >= (params.groupPageIndex - 1) * params.showNumStep && i < params.groupPageIndex * params.showNumStep;
				}).css("display", "block");
				
				jQuery("#groupLICtl", mscResult).html('<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y mlmgroup">下一页&gt;</a>'
									 +'<a href="javascript:showSCResults(' + (pageIndex - 1) + ')" class="y mlmgroup" '
									 + (pageIndex == 0 ? 'style="visibility: hidden;"' : '')
									 + '>&lt;上一页</a><span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>'
				                     + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'group\');" class="z">查看全部</a>');
				jQuery("#groupLICtl", mscResult).removeClass("hm");
				jQuery("#groupList", mscResult).append(jQuery("#groupLICtl", mscResult));
				
				adjustHSResults();
				params.groupPageIndex = pageIndex;
			}
		} else {
			jQuery("#memberList dl", mscResult).css("display", "none");
			jQuery("#memberLICtl", mscResult).addClass("hm");
			jQuery("#memberLICtl", mscResult).html('<img src="static/image/common/loading.gif" />');

			if(pageIndex > params.memberPageIndex) {
				/* 考虑到网络中断可能会导致prefetch失效 故在本页结果放置前再次进行检查补漏 */ 
				var needFetchSCRRelationship = [];
				var needFetchSCRProvince = [];
				var needReRunFlag = false;
				
				if(params.memberCount - params.memberPrefetchNum > 0 && jQuery("#memberListBk dl", mscResult).length == 0) {
					needReRunFlag = true;
				}
				
				var stdProcedure = function() {
					jQuery("#memberListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
						jQuery("#memberList", mscResult).append(jQuery(this));
					});
					fetchFeedAuth('#memberList'); //获取账户认证信息
					jQuery("#memberList dl:last", mscResult).removeClass("bbda");
					
					jQuery("#memberLICtl", mscResult).html('<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y mlmgroup"'
										 + (params.memberCount - (pageIndex + 1) * params.showNumStep > 0 ? '' : 'style="visibility: hidden;"')
									     + '>下一页&gt;</a><a href="javascript:showSCResults(' + (pageIndex - 1) + ')" class="y">&lt;上一页</a>'
									     + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'member\');" class="z">查看全部</a>'
					                     + '<span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>');
					jQuery("#memberLICtl", mscResult).removeClass("hm");
					jQuery("#memberList", mscResult).append(jQuery("#memberLICtl", mscResult));
										
					adjustHSResults();
					params.memberPageIndex = pageIndex;
					
					//Prefetch
					if((params.memberPrefetchNum - pageIndex * params.showNumStep < params.showNumStep * pfParams.basic) && (pageIndex % pfParams.basic == Math.floor(pfParams.basic / 2)) && (params.memberCount - params.memberPrefetchNum > 0)) {
						var sssDTD = new jQuery.Deferred();
						fetchSCResults(mscInputVal, params.memberPrefetchNum, params.showNumStep * pfParams.basic, sssDTD);
						jQuery.when(sssDTD.promise()).done(function() {
							if(needReRunFlag) {
								stdProcedure();
							}
							//alert("params.memberPrefetchNum:" + params.memberPrefetchNum);
							if(pageIndex % pfParams.province == Math.floor(pfParams.province / 2)) {
								//alert("fetching pro");
								fetchSCRProvince(jQuery("#memberListBk dl:lt(" + params.showNumStep * pfParams.province + ")", mscResult));
							}
						});
					}
					
					if(pageIndex % pfParams.rel == Math.floor(pfParams.rel / 2)) {
						//alert("fetching rel:" + params.showNumStep * pfParams.rel);
						fetchSCRRelationship(jQuery("#memberListBk dl:lt(" + params.showNumStep * pfParams.rel + ")", mscResult));
					}
					
				};
				
				jQuery("#memberListBk dl:lt(" + params.showNumStep + ")", mscResult).each(function() {
					if(!jQuery(this).data("ready")) {
						needFetchSCRRelationship.push(jQuery(this));
					}
					if(jQuery(".progroup", this).text() == "") {
						needFetchSCRProvince.push(jQuery(this));
					}
				});
				
				if(needFetchSCRProvince.length > 0) {
					var scrProDTD = new jQuery.Deferred();
					fetchSCRProvince(jQuery(needFetchSCRProvince), scrProDTD);
					jQuery.when(scrProDTD.promise()).done(function() {
						adjustHSResults();
					});	
				}
				
				if(needFetchSCRRelationship.length > 0) {
					var scrRelDTD = new jQuery.Deferred();
					fetchSCRRelationship(jQuery(needFetchSCRRelationship), scrRelDTD);
					jQuery.when(scrRelDTD.promise()).done(function() {
						stdProcedure();
					});	
				} else {
					stdProcedure();
				}
			} else {
				var k = [];
				jQuery("#memberList dl:gt(" + (params.memberPageIndex * params.showNumStep - 1) + ")", mscResult).each(function() {
					k.push(jQuery(this).attr("id"));
				});
				k.reverse();
				jQuery.each(k, function(key, value) {
					jQuery("#memberListBk", mscResult).prepend(jQuery("#" + value));
					jQuery("#" + value).css("display", "block");
				});
				
				jQuery("#memberList dl", mscResult).filter(function(i) {
					return i >= (params.memberPageIndex - 1) * params.showNumStep && i < params.memberPageIndex * params.showNumStep;
				}).css("display", "block");
				
				jQuery("#memberLICtl", mscResult).html('<a href="javascript:showSCResults(' + (pageIndex + 1) + ')" class="y mlmgroup">下一页&gt;</a>'
									 +'<a href="javascript:showSCResults(' + (pageIndex - 1) + ')" class="y mlmgroup" '
									 + (pageIndex == 0 ? 'style="visibility: hidden;"' : '')
									 + '>&lt;上一页</a><span style="margin-left: 60px">第' + (pageIndex + 1) + '页</span>'
									 + '<a href="javascript:openASCRPage(\'' + mscInputVal + '\',\'member\');" class="z">查看全部</a>');
				jQuery("#memberLICtl", mscResult).removeClass("hm");
				jQuery("#memberList", mscResult).append(jQuery("#memberLICtl", mscResult));
				
				adjustHSResults();
				params.memberPageIndex = pageIndex;
			}
		}
	}
}


function fetchSCResults(str, shownNum, showNumStep, dtd) {
	//alert("api/blog/api_searchall.php?name=" + encodeURIComponent(str) + "&num=" + shownNum + "&shownum=" + showNumStep);
	
	jQuery.when(jQuery.getJSON("api/blog/api_searchmember.php?name=" + encodeURIComponent(str) + "&num=" + shownNum + "&shownum=" + showNumStep),
				jQuery.getJSON("api/blog/api_searchforum.php?name=" + encodeURIComponent(str) + "&num=" + shownNum + "&shownum=" + showNumStep))
	  .then(function(d1,d2){
		var data = {};
	  	data.member = d1[0].member;
	  	data.membercount = d1[0].membercount;
	  	data.forum = d2[0].forum;
	  	data.forumcount = d2[0].forumcount;
	  	
		if(data) {
			jQuery(".mscl", mscobj).css("display", "block");
			jQuery(".mscloading", mscobj).css("display", "none");
			isLoadingSCResults = false;
			
			params.memberCount = data.membercount;
			params.groupCount = data.forumcount;
			
			if(data.member != null || data.forum != null) {
				if(data.membercount > 0 && data.forumcount > 0) {
					MoG[0] = 0;
				} else if(data.membercount > 0){
					MoG[0] = 0;
				} else if(data.forumcount > 0) {
					MoG[0] = 1;
				}
				
				if(data.membercount > 0) {
					if(jQuery("#memberListBk", mscResult).length == 0) {
						mscResult.append('<div id="memberListBk" style="display: none"></div>');
					}
					
					var mscMemberListBK = jQuery("#memberListBk", mscResult);
					var thisCount = 0;
					jQuery.each(data.member, function(key, member) {
						if(!jQuery("#uid" + member.uid, mscResult).length) {
							var srecord = jQuery('<dl id=uid' + member.uid + ' class="bbda"></dl>');
							srecord.data("ready", false);
							var srecord_usrimg = jQuery('<dd class="m avt"><a href="home.php?mod=space&uid=' + member.uid 
														+ '" title="' + member.realname + '" target="_blank">'
														+ '<img src="uc_server/avatar.php?uid=' + member.uid + '&size=small" /></a></dd>');
							var srecord_usrname = jQuery('<dt><div class="relbtn y"></div><span class="xi2 xs2"><a href="home.php?mod=space&uid='
													     + member.uid + '" title="' + member.realname + '" target="_blank" class="perAuth">' + member.realname
													     + '</a></span></dt>');
							var srecord_usrorg = jQuery('<dd class="xg1 progroup"><img src="static/image/common/loading.gif" /></dd>');
							var srecord_usrcart = jQuery('<dd>关注<span class="xg1 mrngroup">' + member.follow + '</span>'
														 + '粉丝<span class="xg1 mrngroup">' + member.fans + '</span>好友<span class="xg1 mrngroup">' 
														 + member.friends + '</span>已发表<span class="xg1 mrngroup">' + member.blogs + '</span></dd>');
							mscMemberListBK.append(srecord);
							srecord.append(srecord_usrimg);
							srecord.append(srecord_usrname);
							srecord.append(srecord_usrorg);
							srecord.append(srecord_usrcart);
							thisCount++;
						}
					});
					params.memberPrefetchNum += thisCount;
					//fetchFeedAuth(mscMemberListBK);
				}
				
				if(data.forumcount > 0) {
					if(jQuery("#groupListBk", mscResult).length == 0) {
						mscResult.append('<div id="groupListBk" style="display: none"></div>');
					}
					
					var mscGroupListBK = jQuery("#groupListBk", mscResult);
					var thisCount = 0;
					jQuery.each(data.forum, function(key, forum) {
						if(!jQuery("#fid" + forum.fid, mscResult).length) {
							var srecord = jQuery('<dl id=fid' + forum.fid + ' class="bbda"></dl>');
							srecord.data("ready", true);
							
							var iconstr = forum.icon == "" ? "static/image/images/def_group.png" : "data/attachment/group/" + forum.icon;
							var srecord_forumimg = jQuery('<dd class="m avt"><a href="forum.php?mod=group&fid=' + forum.fid
												        + '" title="' + forum.name + '" target="_blank">'
														+ '<img src="' + iconstr + '" /></a></dd>');
							var srecord_forumname = jQuery('<dt><span class="xi2 xs2"><a href="forum.php?mod=group&fid=' + forum.fid
													     + '" title="' + forum.name + '" target="_blank">' + forum.name + '</a></span></dt>');
							var srecord_forumcart = jQuery('<dd>' + forum.membernum + '名成员</dd>');
							
							var descstr;
							if(forum.description.length > 16) {
								descstr = forum.description.substring(0, 16) + "...";
							} else {
								descstr = forum.description;
							}
							var srecord_forumdisc = jQuery('<dd class="xg1 oneLine">' + descstr + '</dd>');
							mscGroupListBK.append(srecord);
							srecord.append(srecord_forumimg);
							srecord.append(srecord_forumname);
							srecord.append(srecord_forumcart);
							srecord.append(srecord_forumdisc);
							thisCount++;
						}
					});
					params.groupPrefetchNum += thisCount;
				}
			}
			
			if(dtd != undefined) { dtd.resolve(); }
			
		} else {
			if(dtd != undefined) { dtd.reject(); }
		}
	});
}

function fetchSCRRelationship(obj, dtd){
	var taskCount = obj.length;
	
	obj.each(function() {
		var srecordID = jQuery(this).attr("id").replace("uid", "");
		jQuery.getJSON("api/blog/api_relationship.php?fromuid=" + UID + "&" + "touid=" + srecordID, function(data){
			if(UID != srecordID) {
				if(data.followed == 3){
					jQuery("#uid" + srecordID + " .relbtn", mscResult).html(getFollowBtn('friend', UID, srecordID));
				 }else if(data.followed == 1) {
				 	jQuery("#uid" + srecordID + " .relbtn", mscResult).html(getFollowBtn('unfollow', UID, srecordID));
				} else {
					jQuery("#uid" + srecordID + " .relbtn", mscResult).html(getFollowBtn('follow', UID, srecordID));
				}
			} else {
				jQuery("#uid" + srecordID + " .avt", mscResult).append('<div class="selfTag">自己</div>');
			}
			
			jQuery("#uid" + srecordID).data("ready", true);
			taskCount--;
			if(taskCount == 0 && dtd != undefined) {
				dtd.resolve();
			}
		});
	});
}

function fetchSCRProvince(obj, dtd) {
	var taskCount = obj.length;
	
	obj.each(function() {
		var srecordID = jQuery(this).attr("id").replace("uid", "");
		jQuery.ajax({
		  url: "api/sso/getuserprogroup.php?pro=group" + "&" + "uid="+ srecordID,
		  dataType: 'json',
		  success: function(data) {
		  		var prostr = data&&data.groupname ? data.groupname : "中国电信";
		  		jQuery("#uid" + srecordID + " .progroup", mscResult).html("[" + prostr + "]");
		  		taskCount--;
		  		if(taskCount == 0 && dtd != undefined) {
		  			dtd.resolve();
		  		} 		
		  }
		});
	});
}

function switchSCPanel(panelType) {
	jQuery(".resTitle a", mscResult).removeClass("xi2 xw1");
	jQuery("#" + panelType + "Title").addClass("xi2 xw1");
	
	jQuery('div[id$="List"]', mscResult).css("display", "none");
	jQuery("#" + panelType + "List").css("display", "block");
	
	if(panelType == "group") {
		MoG[1] = 1;
	} else if (panelType == "member") {
		MoG[1] = 0;
	}
	
	adjustHSResults();
}

function adjustHSResults() {
	mscResultIsAnimating = true;
	mscobj.animate({
		height: mscOriHeight + jQuery("#hsResults", mscobj).outerHeight()
	}, 300, 'swing', function() {
		mscResultIsAnimating = false;
	});
}

function openASCRPage(str, type) {
	var oristr = jQuery("#sc #srchtxt").val();
	
	jQuery("#sc #srchtxt").val(str);
	jQuery("#sc #mod_" + type).attr("checked", true);
	jQuery("#sc #searchsubmit").click();
	
	jQuery("#sc #srchtxt").val(oristr);
	jQuery("#sc #mod_blog").attr("checked", true);
}

var msearchClear = function() {
	mscInput.addClass("xg0");
	mscInput.val(mssOriStr);
	jQuery(".mscl", mscobj).css("display", "none");
	clrResult();	
};

var clrResult = function() {
	if(!mscResultIsAnimating && mscResult.css("display") != "none") {
		mscResult.css("position", "absolute");
		mscResult.css("visibility", "hidden");
		mscResult.empty();
		
		mscResultIsAnimating = true;
		mscobj.animate({
			height: mscOriHeight - 10
		}, 300, 'swing', function() {
			mscResultIsAnimating = false;
			mscobj.height(mscOriHeight);
			mscobj.parent().removeClass("mscresult");
		});
	} else return
};

mscInput.focus(function() {
	if(jQuery(this).val() == mssOriStr) {
		jQuery(this).removeClass("xg0");
		jQuery(this).val("");
	}
	jQuery(this).on("keyup", mscInputCtrl);
}).blur(function() {
	if(jQuery(this).val() == "") {
		jQuery(this).addClass("xg0");
		jQuery(this).val(mssOriStr);
	}
	jQuery(this).off("keyup", mscInputCtrl);
});

jQuery(".mscl", mscobj).click(msearchClear);