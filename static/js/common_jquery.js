/* Global Initialization */
jQuery(document).ready(function() {
	jQuery.ajaxSetup({ cache:false });	//防止jQuery JSON ajax被IE缓存
	newUIInit();
	showPerPanel();
	checkMyRepeatExistence(UID);
	checkMymsgPrompt(UID, '', window.location.href);
	checkMyAuth();
});

function newUIInit() {
	jQuery(".pss li").hover(
		function() {
			if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) { jQuery(this).toggleClass("hover"); }
			jQuery(this).parent().attr("class", "op" + jQuery(this).attr("optval"));
		}
	);
	jQuery(".mn_home .bm").height(jQuery("#wp").outerHeight() + 500);
}

/* new selection */
function setPssValue(anchor, inputName) {
	if(jQuery(anchor).hasClass("on")) {
		var selectedLiNum = jQuery("ul", anchor).attr("class").replace("op", "");
		if(jQuery("span", anchor).text() != jQuery("li[optval='" + selectedLiNum + "']", anchor).text()) {
			if(inputName != "") {
				jQuery("input[name=" + inputName + "]").val(selectedLiNum);
			}
			jQuery("span", anchor).text(jQuery("li[optval='" + selectedLiNum + "']", anchor).text());
			jQuery("span", anchor).addClass("xw1");
		}
		jQuery(anchor).removeClass("on");
	} else {
		jQuery(anchor).addClass("on");
	}
}

function resetPssValue(anchor, inputName) {
	jQuery("ul", anchor).attr("class", "op0");
	if(inputName != "") {
		jQuery("input[name=" + inputName + "]").val(0);
	}
	jQuery("span", anchor).text(jQuery("li[optval=0]", anchor).text());
	//jQuery("span", anchor).addClass("xg0");
	jQuery("span", anchor).removeClass("xw1");
	jQuery(anchor).removeClass("on");
}

/* new pop menu */
function showPpmuMenu(source, event, referObj, animationIsNeeded) {
	if(event.stopPropagation) { event.stopPropagation(); }
	event.cancelBubble = true;

	var target = "#" + source.id + "_menu";
	if(jQuery(target + ":visible").length != 0) {
		hidePpmuMenu(target);
	} else {
		var basePos = referObj == undefined ? [0, 0] : [jQuery(referObj).offset().left, jQuery(referObj).offset().top];
		jQuery(target).css("position", "absolute");
		var targetDim = [jQuery(target).width(), jQuery(target).height()];
		jQuery(target).css("top", jQuery(source).offset().top + jQuery(source).height() - basePos[1] + 10);
		if(animationIsNeeded != undefined && animationIsNeeded) {
			jQuery(target).css("left", jQuery(source).offset().left - basePos[0]);
			jQuery(target).width(0);
			jQuery(target).height(0);
			jQuery(target).css("display", "block");
			jQuery(target).animate({
				width: targetDim[0],
				height: targetDim[1],
				left: jQuery(source).offset().left - basePos[0] -targetDim[0]/2 + jQuery(source).width()/2
			}, 150, 'easeOutCubic', function() {});
		} else {
			jQuery(target).css("left", jQuery(source).offset().left - basePos[0] -targetDim[0]/2 + jQuery(source).width()/2);
			jQuery(target).css("display", "block");
		}
		jQuery(target).css("z-index", "301");
		jQuery(target).addClass("shownMenu");
		jQuery(document).on("click", hidePpmuMenu);
	}
}

function hidePpmuMenu(target) {
	if(target && typeof target == 'string') {
		jQuery(target).fadeOut(150);
	} else {
		jQuery(".ppmu [class*='shownMenu']").each(function() {
			jQuery(this).removeClass("shownMenu");
			jQuery(this).fadeOut(150);
		});
		jQuery(document).off("click", hidePpmuMenu);
	}
}

function getMD5(paraArray) {
	if(paraArray != null && paraArray != "") {
		var str = "esn";
		for(var i = 0; i < paraArray.length; i++) {
			str += paraArray[i];
		}
		return "code=" + jQuery.md5(str);
	}
}



function reFitImg(img, viewLimit) {
	img instanceof jQuery ? "" : img = jQuery(img);

	if(viewLimit) {
		var iw = img.width();
		var ih = img.height();
		var maxWidth = viewLimit[0];
		var maxHeight = viewLimit[1];

		if(iw < maxWidth && ih < maxHeight) return false;

		if(iw / ih > maxWidth / maxHeight) {
			img.width(maxWidth);
			img.height("auto");
		} else {
			img.height(maxHeight);
			img.width("auto");
		}
		return true;

	} else return false;
}

var st;
function showCover(coverOnly, msg, effect, callback) {
	if (jQuery.browser.mozilla){ st = document.documentElement.scrollTop;}
	jQuery("html").css("overflow","hidden");

	var coverObj = document.createElement('div');
	coverObj.id = '_cover';
	coverObj.style.position = 'absolute';
	coverObj.style.zIndex = 600;
	coverObj.style.left = coverObj.style.top = '0px';
	coverObj.style.width = '100%';
	coverObj.style.height = document.body.offsetHeight > window.screen.height ? document.body.offsetHeight+'px' :  window.screen.height+'px'; //document.body.offsetHeight + 'px';
	coverObj.style.backgroundColor = '#000';
	coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=90)';
	coverObj.style.opacity = 0.9;
	coverObj.onclick = hideCover;
	jQuery('#append_parent').append(coverObj);

	var menuObj = document.createElement('div');
	menuObj.id = 'menu123';
	menuObj.style.position = 'absolute';
	menuObj.style.zIndex = 601;
	//menuObj.style.width = '800px';
	//menuObj.style.overflow = 'auto';

	var s = null;
	if(!coverOnly) {
		s = '<table cellpadding="0" cellspacing="0" class="fwin" ><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c"><div class="coverCont"><div id="coverContInner">';
		s += '<div class="coverClose"><span onclick="hideCover();" class="flbc"></span></div>';
		s += msg;
		s += '</div></div></td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
	} else {
		s = msg;
	}
	menuObj.innerHTML = s;

	if(typeof effect == 'string') {
		if(effect == "slideUp") {
			jQuery('#append_parent').append(menuObj);
			jQuery(menuObj).slideUp("fast");
		} else {
			jQuery('#append_parent').append(menuObj);
		}
	} else jQuery('#append_parent').append(menuObj);


	function position(anchor){
		var height = jQuery(window).height();
		var width = jQuery(window).width();
		var scrollTop = jQuery(window).scrollTop();
		var scrollLeft = jQuery(window).scrollLeft();
		var dheight;
		//if(anchor.clientWidth>width){
			//anchor.style.width = width-50 + 'px';
		//}
		if(anchor.clientHeight > height-50){
			dheight = height-50;
		}else{
			dheight = anchor.clientHeight;
		}
		anchor.style.left=(width-anchor.clientWidth)/2+scrollLeft+'px';
		anchor.style.top=(height-dheight)/2+scrollTop+"px";
		//if(anchor.clientHeight>height){
			jQuery(".coverCont").css("height", (dheight-36)+'px'); //36 = padding + margin
		//}
		//图片加载延迟，高度不能正确计算
		var contimg = jQuery('img', anchor);
		if(contimg.length > 0){
			//var imgLoad = false;
			contimg.on('load', function(){
				//if(imgLoad === false){ //成功加载第一张图片的时候，重新计算对话框高度
					var ih = jQuery('.coverCont #coverContInner').height();
					if( ih > height - 50 - 36){
						dheight = height - 50;
					}else{
						dheight = ih + 36;
					}
					anchor.style.top=(height-dheight)/2+scrollTop+"px";
					jQuery(".coverCont").css("height", (dheight-36)+'px');
					imgLoad = true;
				//}
			});
		}
	}
	if(!coverOnly) {
		position(menuObj);
		
		jQuery(window).resize(function() {
			var height = jQuery(window).height();
			var width = jQuery(window).width();
			var scrollTop = jQuery(window).scrollTop();
			var scrollLeft = jQuery(window).scrollLeft();
			var anchorwidth = 800;    //jQuery("#menu123").css("width")
			var anchorheight = jQuery("#menu123").css("height");
			//anchorwidth = anchorwidth.substring(0,anchorwidth.length-2);
			anchorheight = anchorheight.substring(0,anchorheight.length-2);
			var dheight;

			if(anchorheight>height-50){
				dheight = height - 50;
			}else{ dheight = anchorheight;}
			jQuery("#menu123").css("left",(width-anchorwidth)/2+scrollLeft+'px')
			jQuery("#menu123").css("top",(height-dheight)/2+scrollTop+'px')
			//if(anchorheight>height){
				jQuery(".coverCont").css("height",(dheight-36)+'px');
			//}
		});
	}

	if(typeof callback == 'function') callback();
	else eval(callback);
}

function hideCover(){
	jQuery("#append_parent").empty();
	jQuery("html").removeAttr("style");
	if (st) {
		document.documentElement.scrollTop = st;
	}
	jQuery(window).unbind('resize');
}

function ffPanel(mode, uid) {
	var ffobj = jQuery("#ffp");
	ffobj.empty();

	if(mode == "filter") {
		var ffpfv = jQuery('<div id="ffpfv" class="ffpops xs2 mtn" style="border-bottom: 1px solid #d6d6d6"></div>');
		var ffpma = jQuery('<div id="ffpma" class="ffpops"></div>');
		var ffpmenu = jQuery('<div id="ffpmenu" class="ppmu"></div>');
		var addFeedView_menu = jQuery('<ul class="p_pop h_pop" id="addFeedView_menu" style="display: none"></ul>');
		var addFeedViewBtn = jQuery('<a id="addFeedView" href="javascript:;" class="more"></a>');
		var addGroup_menu = jQuery('<ul class="p_pop h_pop" id="addGroup_menu" style="display: none"></ul>');
		var fvnum;

		ffobj.append(ffpfv);
		ffobj.append(ffpma);
		ffobj.append('<div class="nvShadow"></div>');
		ffobj.append(ffpmenu);
		ffpmenu.append(addFeedView_menu);
		ffpmenu.append(addGroup_menu);

		ffpfv.on("addFeedView", "ul", function() {
			var fv = jQuery("ul li:last a", ffpfv);

			/* 文字大于一定宽度 使用中间省略号截字法 */
			if(fv.width() > 70) {
				var fvLength = fv.text().length;
				var fvLengthIndex = fvLength / 2;

				while(fv.width() > 70) {
					fv.text(fv.text().substring(0, fvLengthIndex) + "..." + fv.text().substring(fvLength - fvLengthIndex, fvLength));
					fvLengthIndex--;
					fvLength = fv.text().length;
				}
				fv.data("abbr", fv.text());
			}

			var ffpWidth = ffpfv.width();
			var existWidth = 0;
			jQuery("li", ffpfv).each(function() {
				existWidth += jQuery(this).outerWidth();
			});
			var addFeedViewBtnWidth = 14;
			var fvindex = jQuery("li:last", ffpfv);
			if(existWidth > ffpWidth - addFeedViewBtnWidth) {
				jQuery("a", fvindex).text(jQuery("a", fvindex).attr("title"));
				jQuery(addFeedView_menu).append(fvindex);
			}
		});


		ffobj.resetFfpfv = function() {
			ffpfv.empty();
			addFeedView_menu.empty();
			ffpfv.append('<ul></ul>');
			jQuery('ul', ffpfv).append('<li><a href="javascript:loadfollow();">个人动态</a></li><li><a href="javascript:loadgroup();">专区动态</a></li><li><a href="javascript:loadall();">最新动态</a></li>');


			if(!(typeof fvArray == "undefined" || fvArray == null || fvArray == "" || fvArray == 0)) {
				var fvReArray = fvArray.split(",");
				jQuery.each(fvReArray, function(i){
					var fvstr = fvReArray[i].split("_");
					var fv = jQuery("<a href='javascript:;' onclick='loadffpscFeeds(this);' title='" + fvstr[0] + "'>" + fvstr[0] + "</a>");
					fv.data("type", fvstr[1]);
					fv.data("id", fvstr[2]);
					jQuery("ul", ffpfv).append("<li></li>");
					jQuery("ul li:last", ffpfv).append(fv);
					jQuery("ul", ffpfv).trigger("addFeedView");
				});
			};

			fvnum = jQuery('li', ffpfv).length;
			addFeedViewBtn.click(function(event) {
				showPpmuMenu(this, event, ffobj);
			});
			jQuery('ul', ffpfv).append('<li class="moreLi"></li>');
			jQuery('li:eq(' + fvnum + ')', ffpfv).append(addFeedViewBtn);
			addFeedView_menu.append('<li><a href="javascript:showAddViewPanel(' + uid + ');" hidefocus="true">管理视图</a></li>');
			if(jQuery("li", addFeedView_menu).length > 1) {
				jQuery("li:last", addFeedView_menu).css("borderTop", "1px solid #eaeaea");
			}

			if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
				ffPanelFix_msie(ffpfv);
			}
		};

		ffobj.resetFfpma = function() {
			ffpma.empty();
			addGroup_menu.empty();
		};

		ffobj.getFfpfv = function() {
			return ffpfv;
		}

		ffobj.getFfpma = function() {
			return ffpma;
		}

		ffobj.getAddFeedView_menu = function() {
			return addFeedView_menu;
		}

		ffobj.getAddGroup_menu = function() {
			return addGroup_menu;
		}

		ffobj.resetFfpfv();


	} else if(mode == "title"){
		ffobj.append('<div class="ffpops xs2 mtn pbm"></div>');
		ffobj.append('<div class="nvShadow"></div>');

		ffobj.append = function(str) {
			jQuery(".ffpops", ffobj).append(str);
		};
	}



	return ffobj;
}

function ffPanelFix_msie(target) {
	jQuery("li", target).removeAttr("style");
	jQuery("li:first-child, li:last-child", target).css("background", "none");
}

function showAddViewPanel(uid) {
	showCover(true, "", "", function() {
		jQuery("#append_parent #_cover").css("opacity", 0.7);
		var pcobj = jQuery("#menu123");
		pcobj.append('<table cellpadding="0" cellspacing="0" class="fwin" >'
					 + '<tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr>'
					 + '<tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c"><h3 class="flb" id="fctrl_ar_doc">'
					 + '<em>管理您的动态视图</em><span><a href="javascript:;" class="flbc" title="关闭">关闭</a></span></h3>'
					 + '<div id="addViewPanel" class="flb clpt" style="width: 400px"></div>'
					 + '<p class="o pns"><button type="submit" name="btnsubmit" value="true" class="pn pnc"><strong>确定</strong></button></p>'
					 + '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>');
		var cobj = jQuery("#addViewPanel", pcobj);


		var followstr, groupstr = null;
		var selectedArray = fvArray;
		cobj.on("click", "li", function() {
			if(jQuery("input", this).attr("checked")) {
				jQuery("input", this).attr("checked", false);
			} else jQuery("input", this).attr("checked", true);
		});
		cobj.on("click", "li input", function(event) {
			event.stopPropagation();
		});
		jQuery("button[name='btnsubmit']", pcobj).click(function() {
			sendResult();
		});

		jQuery.getJSON("api/blog/api_followgroup.php?uid="+uid,function(data){
			followstr = '<h2 class="xs2 mtm">关注人分组</h2><ul id="followList" class="optionlist">';
			if(data){
				jQuery.each(data, function(key, value) {
					jQuery.each(value, function(key, followname) {
						followstr += '<li id="follow_follow_'+key+'"><input class="mrngroup" type="checkbox" name="follow_follow_checkbox_'+key+'" >'+followname+'</li>';
					});
				});
				followstr += '</ul>';
				if(followstr && groupstr) {
					cobj.css("background", "none");
					cobj.append(followstr);
					cobj.append(groupstr);
					resetView();
					setInitalSelection();
				}
			}
		});

		jQuery.getJSON("api/blog/api_usergroup.php?uid="+uid,function(data){
			groupstr = '<h2 class="xs2 mtm">已加入的专区</h2><ul id="groupList" class="optionlist">';
			if(data){
				jQuery.each(data.grouplist, function(key, value) {
						groupstr += '<li id="follow_group_'+value.fid+'"><input class="mrngroup" type="checkbox" name="follow_group_checkbox_'+value.fid+'" >'+value.name+'</li>';
				});
				groupstr += '</ul>';
				if(followstr && groupstr) {
					cobj.css("background", "none");
					cobj.append(followstr);
					cobj.append(groupstr);
					resetView();
					setInitalSelection();
				}
			}
		});

		var reCenterContainer = function() {
			var cw = pcobj.width();
			var ch = pcobj.height();
			pcobj.css("left", (jQuery(window).width() - cw)/2);
			pcobj.css("top", jQuery(window).scrollTop() + (jQuery(window).height() - ch)/2);

		};

		var resetView = function() {
			jQuery("#followList li").filter(
				function(index) {
					return index % 3 == 2;
				}
			).css("paddingRight", 0);
			jQuery("#groupList li", cobj).css("width", 185);


			var ulHeight = 0;
			jQuery("ul", cobj).each(function() {
				ulHeight += jQuery(this).outerHeight();
			});
			cobj.height(cobj.height() + ulHeight + 40);
			reCenterContainer();
		};

		var setInitalSelection = function() {
			if(!(typeof selectedArray == "undefined" || selectedArray == null || selectedArray == "" || selectedArray == 0)) {
				var fvReArray = selectedArray.split(",");
				jQuery.each(fvReArray, function(i){
					var fvstr = fvReArray[i].split("_");
					if(fvstr[1] == "follow") {
						jQuery("#followList input").each(function() {
							if(jQuery(this).parent().attr("id").split("_")[2] == fvstr[2]){
								jQuery(this).attr("checked", true);
							}
						});
					} else if(fvstr[1] == "group") {
						jQuery("#groupList input").each(function() {
							if(jQuery(this).parent().attr("id").split("_")[2] == fvstr[2]){
								jQuery(this).attr("checked", true);
							}
						});
					}
				});
			};
		};

		var sendResult = function() {
			selectedArray = [];

			jQuery("ul input", cobj).each(function() {
				if(jQuery(this).attr("checked")) {
					selectedArray.push(jQuery(this).parent().text() + "_" + jQuery(this).parent().attr("id").split("_")[1] + "_" + jQuery(this).parent().attr("id").split("_")[2]);
				}
			});

			jQuery.getJSON("api/blog/api_indexsetting.php?uid=" + uid + "&indexsetting=" + encodeURIComponent(selectedArray.toString()), function(data) {
				if(data) {
					if(data.success == "Y") {
						showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>视图设置成功</span>", 3000);
					} else {
						showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>视图设置失败 请稍后再次尝试</span>", 3000);
					}
				}
			});

			fvArray = selectedArray.toString();
			hideCover();
			loadfollow();

		};

		reCenterContainer();

		jQuery(window).on("resize", reCenterContainer);

		jQuery(".flbc", pcobj).click(function() {
			hideCover();

		});
	});
}


function switchFilter(isExtended, thstr){
	var sptd = jQuery(".ffpops table tr:eq(0) td:first");
	if(isExtended) {
		jQuery("#filterSwitch").text("高级筛选");
		jQuery("#filterSwitch").attr("href", "javascript:switchFilter(false,'" + thstr + "');");

		var filterClose = function() {
			sptd.css("paddingLeft", 35);
			sptd.prev().remove();
			sptd.animate({
				paddingLeft: 0
			}, 200, function() {});
		};
		if(jQuery(".ffpops table tr").length > 1) {
			jQuery(".ffpops table tr:gt(0)").slideUp("fast", function() {
				filterClose();
			});
		} else {
			filterClose();
		}

	} else {
		jQuery("#filterSwitch").text("简单筛选");
		jQuery("#filterSwitch").attr("href", "javascript:switchFilter(true,'" + thstr + "');");

		sptd.animate({
			paddingLeft: 35
		}, 100, function() {
			sptd.parent().prepend("<th>" + thstr + "</th>");
			sptd.css("paddingLeft", 0);
			jQuery(".ffpops table tr:eq(1)").slideDown("fast");
			jQuery(".ffpops table tr:eq(2)").slideDown("fast", function() {
				var today = new Date();
				if(!calendarJSLoaded) {
					jQuery.getScript('static/js/home_calendar.js', function() {
						calendarJSLoaded = true;
						jQuery("#starttime, #endtime").click(function(event) {
							showcalendar(event, this, false, "", today,true);
						});
						jQuery("#endtime").val(today.getFullYear() + "-" + zerofill(today.getMonth() + 1) + "-" + zerofill(today.getDate()));
					});
				}
			});
		});
	}
}

function checkMyRepeatExistence(uid) {
	//通过是否存在个人空间检测用户是否为外部专家 外部专家应无个人空间 外部专家无马甲功能
	if(jQuery("#myspace").length != 0) {
		jQuery.getJSON("api/blog/api_myrepeats.php?uid=" + uid, function(data){
			if (data != null && data.list != null) {
				var realname = jQuery('#um #loginuser').html();
				if (jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 8) {
					jQuery("#myspace").prev().before('<a id="myrepeats" href="javascript:;" onclick="showPpmuMenu(this, event, \'#um\', true);" class="icn_repeat"></a>');
				} else {
					jQuery("#myspace").prev().before('<a id="myrepeats" href="javascript:;" onclick="showPpmuMenu(this, event, null, true);" class="icn_repeat"></a>');
				}

				var myrepeats_menu = jQuery('<ul class="p_pop h_pop" id="myrepeats_menu" style="display: none"></ul>');
				var selfRepeat = jQuery('<li><a id="repeat_0" href="javascript:;" onclick="changeMyRepeat(0)" title="' + realname  + '">'
								 + '<img src="uc_server/avatar.php?uid=' + UID + '&size=small" width="32" height="32" />'
							     + realname + '</a></li>');
					selfRepeat.children('a').data("repeatInfo", {id: 0, name: realname, fid: UID, avt: 'uc_server/avatar.php?uid=' + UID + '&size=small'});
				myrepeats_menu.append(selfRepeat);
				jQuery.each(data.list, function(key, repeatInfo){
					var repeat = jQuery('<a id="repeat_' + repeatInfo.repeatsid + '" href="javascript:;" onclick="changeMyRepeat(' + repeatInfo.repeatsid + ')" title="' + repeatInfo.name +'">'
										 + '<img src="' + repeatInfo.icon + '" width="32" height="32" />'
										 + repeatInfo.name + '</a>');
					repeat.data("repeatInfo", {id: repeatInfo.repeatsid, name: repeatInfo.name, fid: repeatInfo.fid, avt: repeatInfo.icon});
					myrepeats_menu.append(repeat);
					repeat.wrap('<li></li>');
				});
				jQuery("#ummenu").append(myrepeats_menu);
				if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) {
					jQuery("#myrepeats_menu li").hover(
						function() {
							jQuery(this).toggleClass("hover");
					});
				}
				markMyRepeat(false);
			}
		});
	}
}

function changeMyRepeat(repeatsid) {
	if(repeatsid != REPEATID) {
		jQuery.getJSON("api/blog/api_changerepeats.php?uid="+UID+"&"+"repeatsid="+repeatsid+"&"+getMD5([UID, repeatsid]), function(data){
			if (data.success == 'Y') {
				REPEATID = repeatsid;
				jQuery('#myrepeats_menu li').removeClass("selected");

				if(jQuery(".mi").length != 0) {
					jQuery(".mi").animate({
						height: 0,
						opacity: 0
					}, 400, 'easeOutQuint', function() {
						jQuery("#forumDesc, #selfIntro").remove();
						jQuery(".mi").remove();
						markMyRepeat(true);
					});
				} else {
					markMyRepeat(false);
				}
			}
		});
	}
}

function markMyRepeat(animationIsNeeded) {
	var o = jQuery('#repeat_'+(REPEATID>0?REPEATID:0));
	o.parent().addClass("selected");		//在选中的身份后面打勾
	jQuery('#um #loginuser').html(o.data('repeatInfo').name);
	jQuery('#um #loginuser').attr('href', (REPEATID>0?'forum.php?mod=group&fid=':'home.php?mod=space&uid=')+o.data('repeatInfo').fid);
	jQuery('#um #loginuser').toggleClass('xcorange',(REPEATID>0));
	jQuery('#um #myrepeats').toggleClass('on',(REPEATID>0));
	if(jQuery(".pp").length != 0 && jQuery(".pp").data("doHome")) {
		if(REPEATID > 0) {
			showRepeatMi(REPEATID, animationIsNeeded);
		} else {
			if(jQuery(".mi").length == 0) {
				 showSelfMi(animationIsNeeded);
			}
		}
	}
}

function showRepeatMi(repeatsid, animationIsNeeded) {
	var miDIV = jQuery('<div class="mi"></div>');
	var miTable = jQuery('<table cellpadding="0" cellspacing="0"></table>');
	var miTableTR = jQuery('<tr></tr>');
	var miTableTH = jQuery('<th></th>');
	var miTableTD = jQuery('<td></td>');
	miTableTR.append(miTableTH);
	miTableTR.append(miTableTD);
	miTable.append(miTableTR);
	miDIV.append(miTable);
	jQuery(".pp:first").prepend(miDIV);

	var avt = jQuery('<a href="forum.php?mod=group&fid=' + jQuery('#repeat_' + repeatsid).data("repeatInfo").fid + '" class="avt">'
			  	   + '<img src="' + jQuery('#repeat_' + repeatsid).data("repeatInfo").avt + '"></a>');
	var name = jQuery('<h3 class="xs2 xcorange"><a href="forum.php?mod=group&fid=' + jQuery('#repeat_' + repeatsid).data("repeatInfo").fid + '">'
					+ jQuery('#repeat_' + repeatsid).data("repeatInfo").name + '</a></h3>');
	var prompt = jQuery('<p class="mtn xg1 cl">(专区马甲)</p>');
	var miFlnums = jQuery('<ul id="flnums">'
				        + '<li><a href="javascript:;"><span class="flnum">---</span><span>成员</span></a></li>'
						+ '<li><a href="javascript:;"><span class="flnum">---</span><span>已发表</span></a></li>'
						+ '<li><a href="javascript:;"><span class="flnum">---</span><span>访问</span></a></li></ul>');
	var forumDesc = jQuery('<div class="cblk" id="forumDesc" style="display: none">'
						 //+ '<a href="forum.php?mod=group&action=manage&fid=' + jQuery('#repeat_' + repeatsid).data("repeatInfo").fid + '#groupnav" class="y minlk xg1">修改</a>'
						 + '<h2>专区简介</h2><p></p></div>');

	if(jQuery.browser.msie) {
		jQuery("li:first", miFlnums).css({paddingLeft: 0});
		jQuery("li:last", miFlnums).css({background: "none"});
	}

	if(animationIsNeeded) {
		avt.css("position", "absolute");
		avt.css("visibility", "hidden");
		miTableTH.append(avt);
		var tmpWidth = avt.width();
		var tmpHeight = avt.height();
		avt.width(0);
		avt.height(0);
		avt.css("position", "static");
		avt.css("visibility", "visible");
		avt.css("marginLeft", tmpWidth/2);
		avt.animate({
			width: tmpWidth,
			height: tmpHeight,
			marginLeft: 0
		}, 500, 'easeOutBack', function() {
			miFlnums.css("display", "none");
			miDIV.append(miFlnums);
			miFlnums.show("fast");
		});
		name.css("display", "none");
		prompt.css("display", "none");
		miTableTD.append(name);
		miTableTD.append(prompt);
		name.show("fast");
		prompt.show("fast");
	} else {
		miTableTH.append(avt);
		miTableTD.append(name);
		miTableTD.append(prompt);
		miDIV.append(miFlnums);
	}


	jQuery.getJSON("api/blog/api_forumcart.php?uid=" + UID + "&fid=" + jQuery('#repeat_' + repeatsid).data("repeatInfo").fid, function(data) {
		if (data != null && data.forum != null) {
			var flnumArray = [data.forum.membernum, data.forum.blogs, data.forum.viewnum];
			jQuery(".flnum", miFlnums).each(function(index) {
				jQuery(this).text(flnumArray[index]);
			});
			if(data.forum.description != null && data.forum.description != "") {
				jQuery('p', forumDesc).html(data.forum.description);
				forumDesc.insertAfter(miDIV);
				forumDesc.show("fast");
			}
		}
	});

}

/* notification : BEGIN */
function clkMymsgMenu(type) {
	if (typeof type != 'string'){ return false;	}
	switch(type){
		case 'comment':
			window.location.href='home.php?mod=space&do=mycomment&fchannel=receive';
			break;
		case 'at':
			window.location.href = 'home.php?mod=space&do=atme';
			break;
		case 'official':
			window.location.href='home.php?mod=space&do=home&view=notice&type=gfnotice';
			break;
		case 'follow':
			window.location.href='home.php?mod=space&do=follow&op=fans';
			break;
		case 'sayhi':
			window.location.href='home.php?mod=spacecp&ac=poke';
			break;
		default:
			window.location.href='home.php';
			break;
	}
}
function checkMymsgPrompt(uid, type, url) {
	if (jQuery('#myprompt').length == 0) {return;}
	
	var type = type || '';
	var param = {};
	if (typeof url == 'string') {
		var paraString;
		if(url.indexOf("?") != -1) {
			paraString = url.substring(url.indexOf("?")+1, url.length).split("&");
		}
		if(paraString != null) {
			jQuery.each(paraString, function(key, value) {
				var tmpStr = value.split("=");
				param[tmpStr[0]] = tmpStr[1];
			});
		}
	}
	if(param['do'] != ''){
		if(param['do'] == 'mycomment' && param['fchannel'] == 'receive'){
			type = 'comment';
		}else if(param['do'] == 'atme'){
			type = 'at';
		}else if(param['do'] == 'follow'){
			type = 'follow';
		}else if(param['do'] == 'home' && param['view'] == 'notice'){
			type = 'official';
		}
	}else if(param['ac'] == 'poke'){
			type = 'sayhi';
		}
	
	var myprompt_menu = jQuery('#ummenu #myprompt_menu');
	if (myprompt_menu.length > 0){
		myprompt_menu.empty();
	}else{
		myprompt_menu = jQuery('<ul class="n_pop" id="myprompt_menu" style="display:none;"></ul>');
		jQuery("#ummenu").append(myprompt_menu);
	}
	 
	//data fetch
	jQuery.getJSON("api/blog/api_noti_new.php?&uid="+uid+"&type="+type+"&random="+Math.random(), function(data) {
		if(data != null) {
			if(data.official!=0){
				 myprompt_menu.append('<li class="newMsg" id="gfnotice">'+data.official+'条新的通知公告<a href="#a" onclick="clkMymsgMenu(\'official\')" class="xi2 xw1 mlngroup">查看通知</a></li>');
			}
			if(data.follow!=0){
				  myprompt_menu.append('<li class="newMsg" id="followid">您有'+data.follow+'个新的粉丝<a href="#a" onclick="clkMymsgMenu(\'follow\')" class="xi2 xw1 mlngroup">查看粉丝</a></li>');
			}
			if(data.comment!=0){
			     myprompt_menu.append('<li class="newMsg" id="comment">您有'+data.comment+'个新的评论<a href="#a" onclick="clkMymsgMenu(\'comment\')" class="xi2 xw1 mlngroup" id="checkcomment">查看评论</a></li>');
			}
			if(data.atme!=0){
			     myprompt_menu.append('<li class="newMsg" id="atnum">您有'+data.atme+'个@到我<a href="#a" onclick="clkMymsgMenu(\'at\')" class="xi2 xw1 mlngroup" id="atme">查看@到我</a></li>');
			}
			if (data.sayhi!=0){
			    myprompt_menu.append('<li class="newMsg" id="sayhiid">您有'+data.sayhi+'个打招呼<a href="#a" onclick="clkMymsgMenu(\'sayhi\')" class="xi2 xw1 mlngroup">查看打招呼</a></li>');
			}
		}
		
		if(jQuery("li.newMsg", myprompt_menu).length != 0) {
			myprompt_menu.css("position", "absolute");
			var targetDim = [myprompt_menu.width(), myprompt_menu.height()];
			var pipeMargin = parseInt(jQuery(".pipe").css("marginLeft"));			
			if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 8) {
				myprompt_menu.css("top", jQuery("#myprompt").position().top + jQuery("#myprompt").height());
				myprompt_menu.css("left", jQuery("#myprompt").position().left + jQuery("#myprompt").width() - targetDim[0] + pipeMargin);
				myprompt_menu.css("width", targetDim[0]);
			}else{
				myprompt_menu.css("top", jQuery("#myprompt").offset().top + jQuery("#myprompt").height());
				myprompt_menu.css("left", jQuery("#myprompt").offset().left + jQuery("#myprompt").width() - targetDim[0] + pipeMargin);
			}
			myprompt_menu.css("display", "block");
			myprompt_menu.css("z-index", "310");
			myprompt_menu.data("oriHeight", myprompt_menu.height());
	
			jQuery(window).scroll(function(){
				if(!myprompt_menu.data("fixedSign")) { var1 = myprompt_menu.offset().top; }
				var2 = jQuery(window).scrollTop();
				var _IE7 = (jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 7)? true : false;
				var _IE6 = (jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6)? true : false;

				if(var2 >= var1) {
					if(!myprompt_menu.data("fixedSign")) {
						if(!_IE6) { 
							myprompt_menu.addClass("fixed");
							myprompt_menu.data("fixedSign", true);
						 }
						if(_IE7) { myprompt_menu.css("left", jQuery("#myprompt").offset().left + jQuery("#myprompt").width() - targetDim[0] + pipeMargin); }
						hideMymsgMenu();
					}
					if(_IE6) { myprompt_menu.css("top", var2-jQuery("#myprompt").offset().top); }
				} else {
					if(myprompt_menu.data("fixedSign")) {
						if(!_IE6) { 
							myprompt_menu.removeClass("fixed");
							myprompt_menu.data("fixedSign", false);
						}
						if(_IE7) { myprompt_menu.css("left", jQuery("#myprompt").position().left + jQuery("#myprompt").width() - targetDim[0] + pipeMargin); }
					}
					if(_IE6) { myprompt_menu.css("top", jQuery("#myprompt").position().top + jQuery("#myprompt").height()); }
				}
			});
		}
  });
}
function showMymsgMenu(event) {
	if(event.stopPropagation) { event.stopPropagation(); }
	event.cancelBubble = true;
	var myprompt_menu = jQuery("#myprompt_menu");
	var msgMenuList = ['<a href="#a" onclick="clkMymsgMenu(\'comment\')">查看评论</a>','<a href="#a" onclick="clkMymsgMenu(\'at\')">查看@我</a>','<a href="#a" onclick="clkMymsgMenu(\'official\')">通知通告</a>','<a href="#a" onclick="clkMymsgMenu(\'follow\')">新粉丝</a>','<a href="#a" onclick="clkMymsgMenu(\'sayhi\')">打招呼</a>'];
	if(jQuery("#myprompt_menu li.nlink:visible").length != 0) {
		hideMymsgMenu();
	} else {
		//通知container中有新提醒
		if(jQuery("li.newMsg", myprompt_menu).length != 0) {
			myprompt_menu.append('<li class="clp" style="position:absolute; visibility:hidden;"><hr class="l" style="margin: 6px 0; background: #afbfca"></li>');
			jQuery.each(msgMenuList, function(key, value) {
				myprompt_menu.append('<li class="nlink" style="position:absolute; visibility:hidden;">' + value + '</li>');
			});

			var extendHeight = 0;
			jQuery("li.nlink", myprompt_menu).each(function() {
				extendHeight += jQuery(this).outerHeight();
			});
			extendHeight += jQuery("li.clp", myprompt_menu).outerHeight();
			extendHeight +=  myprompt_menu.data("oriHeight");
			myprompt_menu.animate({
				height: extendHeight
			}, 200, 'swing', function() {
				jQuery("li.nlink", myprompt_menu).css("position", "static");
				jQuery("li.clp", myprompt_menu).css("position", "static");
				jQuery("li.nlink", myprompt_menu).css("visibility", "visible");
				jQuery("li.clp", myprompt_menu).css("visibility", "visible");
			});
		} else {
			//通知container为空
			if(jQuery("li", myprompt_menu).length == 0) {
				jQuery.each(msgMenuList, function(key, value) {
					myprompt_menu.append('<li class="nlink">' + value + '</li>');
				});
			}
			myprompt_menu.css("position", "absolute");
			var targetDim = [myprompt_menu.width(), myprompt_menu.height()];
			if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 8) {
				myprompt_menu.css("top", jQuery("#myprompt").position().top + jQuery("#myprompt").height());
				myprompt_menu.css("left", jQuery("#myprompt").position().left + jQuery("#myprompt").width()/2 - targetDim[0]/2);
				myprompt_menu.css("width", targetDim[0]);
			}else{
				myprompt_menu.css("top", jQuery("#myprompt").offset().top + jQuery("#myprompt").height());
				myprompt_menu.css("left", jQuery("#myprompt").offset().left - targetDim[0]/2 + jQuery("#myprompt").width()/2);
			}
			myprompt_menu.css("display", "block");
			myprompt_menu.css("z-index", "310");
		}
		jQuery(document).on("click", hideMymsgMenu);
	}
}
function hideMymsgMenu() {
	if(jQuery("#myprompt_menu li.newMsg").length != 0) {
		jQuery("#myprompt_menu").stop();
		jQuery("#myprompt_menu li.clp").remove();
		jQuery("#myprompt_menu li.nlink").remove();
		jQuery("#myprompt_menu").animate({
			height: jQuery("#myprompt_menu").data("oriHeight")
		}, 200, 'swing', function() {});
	} else {
		jQuery("#myprompt_menu").fadeOut(150);
		jQuery(document).off("click", hideMymsgMenu);
	}
}
/* notification : END */

/* 帐号认证 */
var MYAUTH = -1;			//我的帐户认证信息
function checkMyAuth(){
	jQuery.getJSON("api.php?mod=plugin"+"&"+"app=userauth:api"+"&"+"param=" + encodeURIComponent('uid='+UID) + "&random="+Math.random(), function(data) {
		if(data && data.error == '0') {
			MYAUTH = data.data[UID].isauth;
		}
	});
}
function showMyAuth(){	//记得在a.perPanel||a.perAuth标签上加上added="added",避免fetchFeedAuth()显示2次
	if(MYAUTH == 1){
		return getProAuth();
	}
	return '';
}
function getProAuth(){
	return '<a href="home.php?mod=pro" target="_blank"><b class="auth_approve" alt="专家认证" title="专家认证" ></b></a>';
}
function fetchUAuth(uid, callback){
	var auth = ['',getProAuth()];
	var i = 0;
	if(_authmap[uid] && _authmap[uid] != 'auth'){
		i = _authmap[uid];
		if(callback) callback(auth[i]);
	}else{
		jQuery.getJSON("api.php?mod=plugin"+"&"+"app=userauth:api"+"&"+"param=" + encodeURIComponent('uid='+uid) + "&random="+Math.random(), function(data) {
			if(data && data.error == '0') {
				i = _authmap[uid] = data.data[uid].isauth;
				if(callback) callback(auth[i]);
			}
		});
	}
}
var _authmap = {};		//认证信息集合{uid:isauth}
function fetchFeedAuth(owner){
	var owner = (owner)?jQuery(owner):jQuery('#feed');
	var _authuid = [];
	jQuery("a.perPanel, a.perAuth", owner).not('[added]').each(function() {
		var anchor = jQuery(this);
		if (anchor.find('img').length == 0){
			var uid = null;
			var url = anchor.attr('href') || '';
			if (url.indexOf("?") != -1) {
				url = url.substring(url.indexOf("?") + 1, url.length).split("&");
			}
			if (url != null) {
				jQuery.each(url, function(key, value){
					var tmpStr = value.split("=");
					if (tmpStr[0] == 'uid' && tmpStr[1] > 0 ) {
						uid = tmpStr[1];
						if (!_authmap[uid]){
							_authuid.push(parseInt(uid), 10);
							_authmap[uid] = 'auth';
						}
						anchor.attr('uid', uid);
					}
				});
			}
		}
	});
	
	if(_authuid.length > 0){
		var uids = _authuid.join(',');
		jQuery.getJSON("api.php?mod=plugin"+"&"+"app=userauth:api"+"&"+"param=" + encodeURIComponent("uid="+uids), function(data) {
			if(data && data.error == '0') {
		  		jQuery.each(data.data, function(key, val){
		  			_authmap[key] = val.isauth;
		  		});
		  	}
	  		jQuery("a.perPanel, a.perAuth", owner).not('[added]').each(function() {
	  			var anchor = jQuery(this);
	  			var uid = anchor.attr('uid');
	  			if(_authmap[uid] == 1){
	  				anchor.attr('added','added');
					anchor.after(getProAuth());
				}
			});
		});
	}
}


var perPaneltimer;      //鼠标离开链接1s内面板依然存在，用于将鼠标从链接移至面板内
var showPanelTimer;     //鼠标悬停于链接上一段时间，信息面板才出现
var uFlag = null;       //个人信息面板，判断是否是同一个人
var fFlag = null;       //个人信息面板，判断是否是同一专区
var cFlag = null;       //个人信息面板，判断是否从专区和个人的转换

// 个人信息面板
function showPerPanel(){
	jQuery("body").on("mouseenter",".W_layer",function(){
		clearInterval(perPaneltimer);
		jQuery(this).css("display","");
	});
	jQuery("body").on("mouseleave",".W_layer",function(){
		jQuery(this).css("display","none");
	});

	jQuery("body").on("mouseenter",".usercarta",function(){
		jQuery("span",jQuery(this)).css("color","#0093C3");
	});
	jQuery("body").on("mouseleave",".usercarta",function(){
		jQuery("span",jQuery(this)).css("color","#999999");
	});

	jQuery("body").on("mouseenter",".perPanel",function(){
				clearInterval(perPaneltimer);
				var anchor = jQuery(this);
				showPanelTimer = setTimeout(function(){
					createPerPanel(anchor,UID);
				},500);
	});
	jQuery("body").on("mouseleave",".perPanel",function(){
				clearInterval(showPanelTimer);
				perPaneltimer = setTimeout(function(){
						jQuery(".W_layer").css("display","none");
				},500);
	});
}

function quitForum(uid,fid,jointype,source){
	jQuery.getJSON("api/blog/api_outforum.php?uid=" + uid + "&" + "fid=" + fid + "&" + getMD5([uid, fid]), function(data){
		if (data != null) {
			if(data.success == 'Y'){
				if(jointype == '0'||jointype == '1'){
					jQuery(source).parent().after('<div class="add_f"><a href="javascript:;" onclick="joinForum('+uid+','+fid+','+jointype+',this);"><span>+</span>加入专区</a></div>');
				}
				jQuery(source).parent().remove();
				showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>退出成功</span>", 3000);
			}else{
				showDialog(data.message,'notice');
			}
		}
	})
}

function joinForum(uid,fid,jointype,source){
	jQuery.getJSON("api/blog/api_joinforum.php?uid=" + uid + "&" + "fid=" + fid + "&" + getMD5([uid, fid]), function(data){
		if (data != null) {
			if(data.success == 'Y'){
				if(jointype == '0'){
					jQuery(source).parent().after('<div class="cancel_f"><span>已加入</span>&nbsp;|&nbsp;<a href="javascript:;" onclick="quitForum('+uid+','+fid+','+jointype+',this);">退出专区</a></div>');
					showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>加入成功</span>", 3000);
				}else if(jointype == '1'){
					jQuery(source).parent().after('<div class="xi2 xw1"><span>审核中...</span></div>');
					showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>等待审核</span>", 3000);
				}
				jQuery(source).parent().remove();

			}else{
				showDialog(data.message,'notice');
			}
		}
	})
}

function createPerPanel(val,currentUid){
			var anchor = val;
			var uid = null;
			var fid = null;
			var url = anchor.attr("href");
			var regname;
			var ncobj;

			if (url.indexOf("?") != -1) {
				url = url.substring(url.indexOf("?") + 1, url.length).split("&");
			}
			if (url != null) {
				jQuery.each(url, function(key, value){
					var tmpStr = value.split("=");
					if (tmpStr[0] == 'uid') {
						uid = tmpStr[1];
					}
					if (tmpStr[0] == 'fid') {
						fid = tmpStr[1];
					}
				});
			}

			//alert(fid+"**"+uid);

			if (uFlag == null && fFlag == null) {
				jQuery("#append_parent").before('<div class="W_layer"><div class="W_box" style="position: absolute;bottom:0px;"></div></div>');
			}

			if (fid != null && uid == null) {
				if (fFlag == null || fFlag != fid || cFlag == "uFlag") {
					jQuery(".W_box").html('<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l2"></td><td class="t_c2"></td><td class="t_r2"></td></tr><tr><td class="m_l2"></td><td class="m_c2"><div class="name_card"><p class="mtm mbm hm xg1"><img src="static/image/common/loading.gif" class="mrngroup"/>加载中...</p></div></td><td class="m_r2"></td></tr><tr><td class="b_l2"></td><td class="b_c2"></td><td class="b_r2"></td></tr></table>');
					ncobj = jQuery(".W_layer .name_card");
					jQuery.getJSON("api/blog/api_forumcart.php?uid=" + currentUid + "&" + "fid=" + fid, function(data){
						if (data != null) {
							ncobj.empty();
							var str = '<div class="name cl"><div class="avt pic"><a href="forum.php?mod=group&fid=' + fid + '" target="_blank"><img src="'+data.forum.icon+'" /></a></div>' +
							'<div><div class="pname"><a href="forum.php?mod=group&fid=' +
							fid +
							'" target="_blank">' +
							data.forum.name +
							'</a><span class="pcredit"></span></div><div><span class="progroup"></span></div>' +
							'<div class="usercart"><a class="usercarta" href="forum.php?mod=group&fid='+fid+'">成员<span>' +
							data.forum.membernum +
							'</span></a><a class="usercarta" href="forum.php?mod=group&fid='+fid+'">已发表<span>' +
							data.forum.blogs +
							'</span></a><a class="usercarta" href="forum.php?mod=group&fid='+fid+'">访问<span>' +
							data.forum.viewnum +
							'</span></a></div></div></div>';

							if (data.forum.description != null && data.forum.description != "") {
								str += '<div class="info">简介：' + data.forum.description + '</div>';
							}
							else {
								str += '<div class="info">简介：暂无</div>';
							}

							str += '<div class="links">';

							if (data.forum.ismember == '1') {
								str += '<div class="floatRight"><div class="cancel_f"><span>已加入</span>&nbsp;|&nbsp;<a href="javascript:;" onclick="quitForum(' + currentUid + ',' + fid + ',' + data.forum.jointype + ',this);">退出专区</a></div></div></div>';
							}
							else
								if (data.forum.ismember == '3') {
									str += '<div class="floatRight"><div class="cancel_f"><span>已加入</span></div></div>';
								}
								else
									if (data.forum.ismember == '2') {
										str += '<div class="floatRight"><div class="xi2 xw1"><span>审核中...</span></div>';
									}
									else
										if (data.forum.ismember == '0') {
											if (data.forum.jointype == '0' || data.forum.jointype == '2') {
												str += '<div class="floatRight"><div class="add_f"><a href="javascript:;" onclick="joinForum(' + currentUid + ',' + fid + ',' + data.forum.jointype + ',this);"><span>+</span>加入专区</a></div></div></div>';
											}
										}
							ncobj.html(str);
						}
					})
				}
				fFlag = fid;
				cFlag = "fFlag";
			}
			if (uid != null && fid == null) {
				if (uid == -1) {
					jQuery(".W_box").html('<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l2"></td><td class="t_c2"></td><td class="t_r2"></td></tr><tr><td class="m_l2"></td><td class="m_c2"><div class="name_card"><p class="mtm mbm hm xg1"><img src="static/image/common/loading.gif" class="mrngroup"/>加载中...</p></div></td><td class="m_r2"></td></tr><tr><td class="b_l2"></td><td class="b_c2"></td><td class="b_r2"></td></tr></table>');
					jQuery(".W_layer .name_card").html('<div class="name cl"><div class="avt pic"><img src="uc_server/avatar.php?uid=-1&size=small"></div><div class="pname"><a>匿名</a></div></div>');
					jQuery(".W_layer .name_card").append('');
				} else if (uFlag == null || uFlag != uid || cFlag == "fFlag") {
					jQuery(".W_box").html('<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l2"></td><td class="t_c2"></td><td class="t_r2"></td></tr><tr><td class="m_l2"></td><td class="m_c2"><div class="name_card"><p class="mtm mbm hm xg1"><img src="static/image/common/loading.gif" class="mrngroup"/>加载中...</p></div></td><td class="m_r2"></td></tr><tr><td class="b_l2"></td><td class="b_c2"></td><td class="b_r2"></td></tr></table>');

					ncobj = jQuery(".W_layer .name_card");
					var dataNeeded = {
						"bio": false,
						"usercart": false,
						"usercredit": false,
						"relationship": false,
						"followgroup": false,
						"auth":false
					};

					var completeCard = function(){
						ncobj.empty();
						var progroupstr = ncobj.data("progroup") == undefined ? 
						'<div><span class="progroup"><img src="static/image/common/loading.gif" /></span></div>' : 
						'<div><span class="progroup">[' + ncobj.data("progroup") + ']</span></div>';
						
						ncobj.html('<div class="name cl"><div class="avt pic"><a href="home.php?mod=space&uid=' + uid + '" target="_blank">' +
						'<img src="uc_server/avatar.php?uid=' +
						uid +
						'&size=small" /></a></div>' +
						'<div><div class="pname"><a href="home.php?mod=space&uid=' +
						uid +
						'" target="_blank">' +
						ncobj.data("realname") +
						'</a>' +
						ncobj.data("auth") +
						'</div>' + progroupstr + 
						'<div class="usercart"><a class="usercarta" href="home.php?mod=space&uid=' +
						uid +
						'&do=follow&view=me&from=space&diymode=1">关注<span>' +
						ncobj.data("usercart")[0] +
						'</span></a>' +
						'</span>' +
						'<a class="usercarta" href="home.php?mod=space&uid=' +
						uid +
						'&do=follow&op=fans&diymode=1">粉丝<span>' +
						ncobj.data("usercart")[1] +
						'</span></a><a class="usercarta" href="home.php?mod=space&uid=' +
						uid +
						'&do=follow&followtype=friend&diymode=1">好友<span>' +
						ncobj.data("usercart")[2] +
						'</span></a>' +
						'<a class="usercarta" href="home.php?mod=space&uid=' +
						uid +
						'">已发表<span>' +
						ncobj.data("usercart")[3] +
						'</span></a></div></div></div>');

						if (ncobj.data("infostr") != null && ncobj.data("infostr") != "") {
							ncobj.append('<div class="info">简介：' + ncobj.data("infostr") + '</div>');
						}
						else {
							ncobj.append('<div class="info">简介：无</div>');
						}

						if (currentUid != uid) {
							ncobj.append('<div class="links"><div class="floatLeft">' +
							'<span><a onclick="showWindow(\'showMsgBox\', this.href, \'get\', 0)" href="home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' +
							uid +
							'&touid=' +
							uid +
							'&pmid=0&daterange=2">私信</a>&nbsp;|&nbsp;</span>'     //+ '<a href="home.php?mod=space&uid='+uid+'&do=follow">设置分组</a>&nbsp;|&nbsp;'
							+
							'<span id="setgroup">' +
							ncobj.data("grouplink") +
							'</span>' +
							'<span><a href="home.php?mod=space&uid=' +
							uid +
							'">访问空间</a></span></div>' +
							'<div class="floatRight">' +
							ncobj.data("relationship") +
							'</div></div>' +
							(REPEATID>0?'<div class="repeat xg1">提示：将使用您的真实身份对 ' + ncobj.data("realname") + ' 进行关注</div>':'') +
							'<div class="groups"><p>关注人分组</p>' +
							ncobj.data("followgroup") +
							'</div>');
						}
					};

					jQuery(ncobj).on("perPanelDataLoaded", function(){
						if (dataNeeded.bio && dataNeeded.usercart && dataNeeded.usercredit && dataNeeded.auth) {
							if ((currentUid != uid) && dataNeeded.relationship && dataNeeded.followgroup) {
								completeCard();
							}
							else {
								completeCard();
							}
						}
					});

					//alert("api/blog/api_bio.php?uid=" + uid + "&" + getMD5([uid]));
					jQuery.getJSON("api/blog/api_bio.php?uid=" + uid + "&" + getMD5([uid])+"&random="+Math.random(), function(data){
						ncobj.data("infostr", data.info);
						dataNeeded.bio = true;

						jQuery(ncobj).trigger("perPanelDataLoaded");
					});

					//alert("api/blog/api_usercart.php?uid=" + uid + "&" + getMD5([uid]));
					jQuery.getJSON("api/blog/api_usercart.php?uid=" + uid + "&" + getMD5([uid])+"&random="+Math.random(), function(data){
						var follows, fans, blogs, friends;
						if (data.follows != null) {
							follows = data.follows;
						}else {
							follows = 0;
						}
						if (data.friends != null) {
							friends = data.friends;
						}
						else {
							friends = 0;
						}
						if (data.fans != null) {
							fans = data.fans;
						}
						else {
							fans = 0;
						}
						if (data.blogs != null) {
							blogs = data.blogs;
						}
						else {
							blogs = 0;
						}

						ncobj.data("usercart", [follows, fans, friends, blogs]);
						ncobj.data("realname", data.realname);
						regname = data.username;
						dataNeeded.usercart = true;

						jQuery(ncobj).trigger("perPanelDataLoaded");

						jQuery.ajax({
							url: "api/sso/getuserprogroup.php?pro=group" + "&" + "regname=" + regname,
							dataType: 'json',
							success: function(data){
								var groupname = data&&data.groupname ? data.groupname : "中国电信";
								ncobj.data("progroup", groupname);
								jQuery(".progroup", ncobj).html("[" + groupname + "]");
							//jQuery(ncobj).trigger("perPanelDataLoaded");
							}
						});
					});

					//alert("api/blog/api_usercredit.php?uid=" + uid);
					jQuery.getJSON("api/blog/api_usercredit.php?uid=" + uid, function(data){
						ncobj.data("usercredit", data.credit);
						dataNeeded.usercredit = true;

						jQuery(ncobj).trigger("perPanelDataLoaded");
					});

					//alert("api/blog/api_relationship.php?fromuid=currentUid"+"&"+"touid="+uid);
					jQuery.getJSON("api/blog/api_relationship.php?fromuid=" + currentUid + "&" + "touid=" + uid, function(data){
						if (data.followed == 3) {
							ncobj.data("relationship", getFollowBtn('friend', currentUid, uid, displayFollowgroup));
							ncobj.data("grouplink", getFGroupLink());
						}
						else
							if (data.followed == 1) {
								ncobj.data("relationship", getFollowBtn('unfollow', currentUid, uid, displayFollowgroup));
								ncobj.data("grouplink", getFGroupLink());
							}
							else {
								ncobj.data("relationship", getFollowBtn('follow', currentUid, uid, displayFollowgroup));
								ncobj.data("grouplink", "");
							}
						dataNeeded.relationship = true;
						jQuery(ncobj).trigger("perPanelDataLoaded");
					});

					//alert("api/blog/api_followgroup.php?uid=" + currentUid);
					jQuery.getJSON("api/blog/api_followgroup.php?uid=" + currentUid, function(data){
						jQuery.getJSON("api/blog/api_fusergroup.php?uid=" + currentUid + "&fuid=" + uid, function(selgroup){
							var sel = (selgroup && selgroup.group) ? selgroup.group : null;
							ncobj.data("followgroup", getFollowgroup(data.groups, sel, uid));

							dataNeeded.followgroup = true;
							jQuery(ncobj).trigger("perPanelDataLoaded");
						});
					});
					
					//帐号认证
					jQuery.getJSON("api.php?mod=plugin"+"&"+"app=userauth:api"+"&"+"param=" + encodeURIComponent("uid="+uid) + "&random="+Math.random(), function(data) {
						ncobj.data('auth', '');
						if(data && data.error == '0') {
							var userauth = data.data;
							if(userauth[uid].isauth == 1){
								ncobj.data('auth', getProAuth());
							}
						}
						dataNeeded.auth = true;
						jQuery(ncobj).trigger("perPanelDataLoaded");
					});
					
				}
				showFollowgroup(false);
				uFlag = uid;
				cFlag = "uFlag";
			}

			var leftPos,topPos;
			var mheight = anchor.offset().top;
			var mleft = anchor.offset().left;
			var offsetHeight = jQuery(window).scrollTop();
			var offsetWidth = jQuery(window).width();
			//alert(mheight+'****'+mleft+'****'+offsetHeight+'****'+offsetWidth);
			if( mleft > offsetWidth *3/5){ leftPos = mleft - 330;}else{leftPos = mleft+20;}
			if( mheight - offsetHeight < 200){
				jQuery(".W_box").css("bottom","").css("top","0px");
				topPos = mheight+10;
			}else{
				jQuery(".W_box").css("bottom","0px").css("top","");
				topPos = mheight;
			}
			jQuery(".W_layer").css("top",topPos);
			jQuery(".W_layer").css("left",leftPos);
			jQuery(".W_layer").css("display","block");
}

//名片PerPanel中，关注人分组
jQuery(document).on("click",".groups ul li input",function(e){
		var anchor = jQuery(this);
		var fuid = anchor.attr("fuid");
		var gid = anchor.val();
		var uid = space_uid;

		if(anchor.attr("checked")){
			jQuery.getJSON("api/blog/api_group_update.php?fuid="+fuid+"&gid="+gid+"&uid="+uid+"&action=add&code="+jQuery.md5('esn'+'add'+fuid+gid+uid),function(data){
				if(data.success == 'Y'){
					//jQuery("input",anchor).attr("checked",true);
					//update();
				}else{
					showDialog('删除失败，请重试，或稍等片刻');
				}
			 });
		}else{
			 jQuery.getJSON("api/blog/api_group_update.php?fuid="+fuid+"&gid="+gid+"&uid="+uid+"&action=delete&code="+jQuery.md5('esn'+'delete'+fuid+gid+uid),function(data){
				if(data.success == 'Y'){
					//jQuery("input",anchor).attr("checked",false);
					//update();
				}else{
					showDialog(data.message);
				}
			 });
		}
   });


function getFollowBtn(type, fromuid, touid, func, params) {
	if(type == "follow") {
		return "<div class='add_f'><a href='javascript:;' onclick='follow(" + fromuid + ", " + touid + ", this, " + func + ", \"" + params + "\")'><span>+</span>加关注</a></div>";
	} else if(type == "unfollow") {
		return "<div class='cancel_f'><span>已关注</span>&nbsp;|&nbsp;<a href='javascript:;' onclick='unfollow(" + fromuid + ", " + touid + ", this, " + func + ", \"" + params + "\")'>取消</a></div>";
	} else if(type == "friend") {
		return "<div class='cancel_f fri'><span>好友</span>&nbsp;|&nbsp;<a href='javascript:;' onclick='unfollow(" + fromuid + ", " + touid + ", this, " + func + ", \"" + params + "\")'>取消</a></div>";
	}
}

function follow(fromuid, touid, source, func, params) {
	var oriStatus;
	jQuery.getJSON("api/blog/api_relationship.php?fromuid=" + fromuid + "&" + "touid=" + touid, function(data){
		oriStatus = data.followed;

		jQuery.getJSON("api/blog/api_follow.php?fromuid=" + fromuid + "&" + "touid=" + touid + "&" + getMD5([fromuid, touid]),function(data){
			if(data.success =='Y'){
				if (jQuery(source).parent().hasClass('add_f')){
					jQuery(source).parent().after(getFollowBtn("unfollow", fromuid, touid, func, params));
					jQuery(source).parent().remove();
				}else{
					jQuery(source).html('已关注');
				}
				if (func){params?func(params):func(true);}
				if(REPEATID == 0) {
					try {
						jQuery("#flnums .flnum:first").text(parseInt(jQuery("#flnums .flnum:first").text())+1);
						if(oriStatus == 2){
							jQuery("#flnums .flnum:eq(2)").text(parseInt(jQuery("#flnums .flnum:eq(2)").text())+1);
						}
					} catch(e) {};
				}
				showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>关注成功</span>", 3000);
			}else{
				showDialog(data.message,'notice');
			}
		});
	});
}

function unfollow(fromuid, touid, source, func, params) {
	var oriStatus;
	jQuery.getJSON("api/blog/api_relationship.php?fromuid=" + fromuid + "&" + "touid=" + touid, function(data){
		oriStatus = data.followed;

		jQuery.getJSON("api/blog/api_delfollow.php?fromuid=" + fromuid + "&" + "touid=" + touid + "&" + getMD5([fromuid, touid]),function(data){
			if(data.success =='Y'){
				jQuery(source).parent().after(getFollowBtn("follow", fromuid, touid, func, params));
				jQuery(source).parent().remove();
				showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>取消关注成功</span>", 3000);
				if (func) func(false);
				if(REPEATID == 0) {
					try {
						jQuery("#flnums .flnum:first").text(parseInt(jQuery("#flnums .flnum:first").text())-1);
						if(oriStatus == 3){
							jQuery("#flnums .flnum:eq(2)").text(parseInt(jQuery("#flnums .flnum:eq(2)").text())-1);
						}
					} catch(e) {};
				}
			}else{
				showDialog(data.message,'notice');
			}
		});
	});
}

function showFollowgroup(isShow) {
	var div = jQuery("div.name_card .groups");
	if (isShow == null) isShow = (div.is(":visible"))?false:true;
	if (isShow) {
		jQuery("#setgroup a").text("隐藏分组");
		div.slideDown("fast");
	}else{
		jQuery("#setgroup a").text("设置分组");
		div.hide();
	}
}
function getFollowgroup(groups, selgroup, fuid) {
	var content,check;
		content = '<ul class="cl"> ';
		jQuery.each(groups, function(gid, groupname) {
			check = '';
			if (selgroup)
				jQuery.each(selgroup, function(key, g){
					if (gid == key) {check = 'checked="checked"';}
				});
			content += '<li><input type="checkbox" value="'+ gid +'" fuid="'+ fuid +'" '+ check +' ><label>'+ groupname +'</label></li> '
		});
		content += '</ul>';
	return content;
}

function getFGroupLink() {
	return '<a href="javascript:showFollowgroup();">设置分组</a>&nbsp;|&nbsp;';
}

function showFGroupLink(isShow) {
	var container = jQuery("#setgroup");
	(isShow && container.html() == "" )?container.html(getFGroupLink()):container.toggle(isShow);
}


/* 在getFollowBtn中调用，显示或者隐藏关注人链接和分组*/
function displayFollowgroup(flag) {
	showFGroupLink(flag);
	showFollowgroup(flag);
	if (!flag) {
		jQuery("div.name_card .groups ul li input").removeAttr("checked");
	}
}


/* ------------------- autoComplete begin ------------------- */
/*
  promptStatus:
     0: standby, default
     1: active

  promptType:
     0: at, default
     1: tag

  使用promptpos, promptStatus, promptSearchTP, evtCertainTP, evtOverRide, evtOverRideInfo, lastQueryStr, sugClickedOverRide变量
  存于使用autocomplete对象的data中

*/
function textAreaAutoComplete(event, srcObj, acObj) {
	var eventType = event.type;
	var t = jQuery(srcObj);
	var tParent = t.parent();
	var tForm = t.parents('form');
	var acParent = jQuery('#append_parent');		//默认存放.acwrapper的容器
	var acObj = acObj;								//未传值 结果为undefined
	var promptSearchTP = t.data("promptSearchTP");
	var detectValTP = t.data("detectValTP");		//输入检测计时器
	var atjsonArr = t.data("atjsonArr") || [];		//textarea的所有@
	var noTag = t.data("noTag") || false;			//(转发、评论)不响应#tag

	var acCleanUp = function() {
		var wrap = (acObj == undefined) ? jQuery(".acwrapper", acParent) : acObj;
		try {
			wrap.hide(50);
			wrap.empty();
		} catch(e) {};
		clearTimeout(promptSearchTP);
		t.data("promptStatus", 0);
		t.data("promptType", 0);
		t.data("evtOverRide", false);
		t.unbind("keydown.ac");
	};

	if(eventType == "blur") {
		var handler = function(event){
			var wrap = (acObj == undefined) ? jQuery(".acwrapper", acParent) : acObj;
			try {
				var ex = event.pageX
				   ,ey  = event.pageY
				   ,x1  = wrap.offset().left
				   ,x2  = (wrap.offset().left+wrap.width())
				   ,y1  = wrap.offset().top
				   ,y2  = (wrap.offset().top+wrap.height());
				if (wrap.is(':visible') &&(ex < x1 || ex > x2 || ey < y1 || ey > y2) ) {
					acCleanUp();
				}
			} catch(e) {};
			if (wrap.is(':empty')){
				jQuery('body').unbind("click.ac",handler);
			}
		};
		jQuery('body').bind("click.ac",handler);
		clearInterval(detectValTP);
		jQuery(srcObj).unbind("keyup.ac");

	} else if(eventType == "focus") {
		//alert(event.type);
		//alert(srcObj.tagName);
		var atPattern = /@[\u4e00-\u9fa5\w\-]+/g;
		var atPattern2 = /@[\u4e00-\u9fa5\w\-]+/g;
		var tagPattern = /#[\u4e00-\u9fa5\w]{0,20}#/g;				// /#.*?#/g;
		var promptStatus = t.data("promptStatus") == null ? 0 : parseInt(t.data("promptStatus"));
		var promptType = parseInt(t.data("promptType"));
		var promptpos = parseInt(t.data("promptpos"));
		var evtCertainTP = t.data("evtCertainTP");
		var evtOverRide = t.data("evtOverRide");
		var evtOverRideInfo = t.data("evtOverRideInfo");
		var sugClickedOverRide = t.data("sugClickedOverRide");
		var currPos;
		var blurCleanUpTP;
		var oldVal = t.val();			//记录前一次从0到光标处的值
		var oldText = t.val();			//记录前一次文本框内的所有值
		var atarrPos = -1;				//当前光标@xx 在数组中的index值

		//if(eventType != "blur") { currPos = t.getCursorPos(); }
		//alert(promptType);

		var preventDefault = function(event) {
			if(event.keyCode == 13 || event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 38 || event.keyCode == 40) {
				event.preventDefault();
			}
		};

		var acConfirmHide = function(){
			jQuery('#acconfirm', acObj||jQuery(".acwrapper", acParent)).remove();
			jQuery('#accover', acObj||jQuery(".acwrapper", acParent)).remove();
		};

		var completeRest = function(li) {
			//promptType ? readyStr += "# " : readyStr += " ";
			acConfirmHide();
			var acConfirm = function(msg, acConfirmOk){
				jQuery(li).append('<div id="acconfirm"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td align=center>'
									+msg+'<span class="btok" title="确定"></span><span class="btcl" title="取消"></span>'
									+'</td></tr></table></div><div id="accover"></div>');
				jQuery('#acconfirm .btok', li).click(function(event){event.stopPropagation();acConfirmOk();});
				jQuery('#acconfirm .btcl', li).click(function(event){event.stopPropagation();acConfirmHide();});
			};

			var dt = li.data('data');
			var rest = function(){
				var readyStr = (dt.note) ? dt.note : dt.name;
				var oriMsgObjVal = t.val();
				promptType ? readyStr += "# " : readyStr += " ";

				if(evtOverRideInfo != undefined && (currPos > evtOverRideInfo[0] && currPos <= evtOverRideInfo[1])) {
					t.val(oriMsgObjVal.substring(0, promptpos) + readyStr + oriMsgObjVal.substring(evtOverRideInfo[1], oriMsgObjVal.length));
				} else {
					t.val(oriMsgObjVal.substring(0, promptpos) + readyStr + oriMsgObjVal.substring(currPos, oriMsgObjVal.length));
				}
				t.setCursor(srcObj, promptpos + readyStr.length);
				if (!promptType) {
					atjsonArr[atarrPos-1] = {id: dt.id+'', name: dt.name+'', type: dt.type+'', note: dt.note+''};
					jQuery('input[name=atjson]',tForm).val(obj2str(atjsonArr));
					t.data("atjsonArr",atjsonArr);
				}
				acCleanUp();
			};

			if (promptType) {
				rest();
			}else if (dt.type == 'user' && li.data('followed') == '0'){
				acConfirm('是否[关注]并@'+ dt.name +' ？', function(){
					jQuery.getJSON("api/blog/api_follow.php?fromuid=" + UID + "&" + "touid=" + dt.id + "&" + getMD5([UID, dt.id]),function(data){
						if(data.success =='Y'){
							rest();
						}else{
							showDialog(data.message,'notice');
						}
					});
				});
			} else if (dt.type == 'group' && li.data('joined') == '0'){
				acConfirm('是否加入['+ dt.name +']专区,并@'+ dt.name +' ？', function(){
					jQuery.getJSON("api/blog/api_joinforum.php?uid=" + UID + "&" + "fid=" + dt.id + "&" + getMD5([UID, dt.id]), function(data){
						if(data.success =='Y'){
							rest();
						}else{
							showDialog(data.message,'notice');
						}
					});
				});
			} else {
				rest();
			}


		};

		var acPrompt = function(str) {
			if(str != null && str != "" && str != " ") {

				clearTimeout(promptSearchTP);
				promptSearchTP = setTimeout(function() {
				if(parseInt(promptType)) {
					var searchURL = "api/blog/api_tag.php?" + "&" + "name="+ encodeURIComponent(str);
				} else {
					var searchURL = "api/blog/api_newsearch.php?uid=" + UID + "&" + "name="+ encodeURIComponent(str);
				}
				jQuery.ajax({
				  url: searchURL,
				  dataType: 'json',
				  success: function(data) {
				  		if(data != null && data.keyword == t.data("lastQueryStr")) {
				  			var atList;
				  			if(promptType && data.tag != undefined) {
				  				if(atList == undefined) {
				  					atList = jQuery("<ul></ul>");
				  				}

				  				jQuery.each(data.tag, function(key, tag) {
				  					if(key < 5) {
				  						var li = jQuery("<li class='item cl'></li>").data("data", {name:tag.name});
				  						tag.name = tag.name.replace(str, "<span>" + str + "</span>");
				  						li.append("<a href='javascript:;'>" + tag.name + "</a>");
				  						atList.append(li);
				  					}
				  				});
				  			} else {
				  				if(data.member != undefined) {
				  					if(atList == undefined) {
				  						atList = jQuery("<ul></ul>");
				  					}

				  					var followed = '1', count = 0;
				  					jQuery.each(data.member, function(key, member) {
				  						if(count == 0) {
				  							atList.append('<li class="title">[个人]</li>');
				  							count = 1;
				  						}
				  						if(member.followed != followed) {
				  							atList.append('<li class="break">没有关注过的用户,会先关注再@提到该用户</li>');
				  							followed = '0';
				  						}

			  							var uname = member.name.replace(str, "<span>" + str + "</span>");
			  							if (member.note && member.note!='') uname += '(' + member.note.replace(str, "<span>" + str + "</span>") + ')';
			  							var li = jQuery("<li class='item cl'></li>").data("followed", member.followed).data("data",{id:member.uid,name:member.name,type:member.type,note:member.note});
			  							li.append("<img src='" + member.icon + "' /><a href='javascript:;'>" + uname + "<br /><span class='memo'>" + member.userprovince + "</span></a>");
			  							atList.append(li);
				  					});
				  				}

				  				if(data.group != undefined) {
				  					if(atList == undefined) {
				  						atList = jQuery("<ul></ul>");
				  					}

				  					var joined = '1', count = 0;
				  					jQuery.each(data.group , function(key, group) {
				  						if(count == 0) {
				  							atList.append('<li class="title">[专区]</li>');
				  							count = 1;
				  						}
				  						if(group.joined != joined) {
				  							atList.append('<li class="break">没有加入过的专区,会先加入再@提到该专区</li>');
				  							joined = '0';
				  						}

			  							var gname = group.name.replace(str, "<span>" + str + "</span>");
			  							var li = jQuery("<li class='item cl'></li>").data("joined", group.joined).data("data",{id:group.fid,name:group.name,type:group.type});
			  							li.append("<img src='" + group.icon + "' /><a href='javascript:;' style='line-height:36px;'>" + gname + "</a>");
			  							atList.append(li);
				  					});
				  				}
				  			}

				  			if(atList != undefined) {
				  				if(acObj == undefined) {
				  					if(!jQuery(".acwrapper", acParent).length) {
				  						acParent.append("<div class='acwrapper' style='z-index:999;'></div>");
				  					}
				  					var wrap = jQuery(".acwrapper", acParent);
				  					//tParent.css("position", "relative");
				  					wrap.css("left", t.offset().left + t.getCursorOffset(t, promptpos).left - 8);
				  					wrap.css("top", t.offset().top + t.getCursorOffset(t, promptpos).top + 18);
				  				} else {
				  					acObj.css({'visibility': 'hidden','display': 'block'});	//取对象的位置
				  					var tCursor = t.getCursorOffset(t, promptpos)
				  						,acwOffsetParent = acObj.offsetParent()
				  						,tOffsetParent = t.offsetParent()
				  						,tPosition = {left: tOffsetParent.offset().left - acwOffsetParent.offset().left + t.position().left
				  									  ,top: tOffsetParent.offset().top - acwOffsetParent.offset().top + t.position().top
				  									 }
				  						;
				  					acObj.css({'visibility': 'visible','display': 'none'});	//对象设为原来的值
				  						
				  					//while (tOffsetParent[0] != acwOffsetParent[0]) {
				  					//	tPosition.left += tOffsetParent.position().left;
				  					//	tPosition.top += tOffsetParent.position().top;
				  					//	tOffsetParent = tOffsetParent.offsetParent();
				  					//}
				  					
				  					acObj.css("left", tPosition.left + tCursor.left - 8);
				  					acObj.css("top", tPosition.top + tCursor.top + 18);
				  				}
				  				jQuery("li.item:first", atList).addClass("on");

				  				jQuery("li.item", atList).on("mouseenter.ac", function() {
				  					jQuery("li", atList).removeClass("on");
				  					jQuery(this).addClass("on");
				  				});
				  				jQuery("li.item", atList).on("mouseleave.ac", function() {
				  					jQuery(this).removeClass("on");
				  				});
				  				jQuery("li.item", atList).on("mousedown.ac", function() {
				  					t.data("sugClickedOverRide", true);
				  				});
				  				jQuery("li.item", atList).on("click.ac", function() {
				  					clearTimeout(blurCleanUpTP);
				  					//var readyStr = jQuery(this).text();
				  					completeRest(jQuery(this));
				  					//acCleanUp();
				  				});
				  				acObj.html(atList);

				  				acObj.css("width", "auto");
				  				acObj.css("overflow", "visible");
				  				acObj.show(50, function(){
				  					if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 7){
					  					atList.css('width', atList.width());
					  				}else if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6){
					  					atList.css('width', '240px');
					  				}
				  				});



				  				t.on("keydown.ac", preventDefault);
				  			}
				  		}
				  }
				});
				t.data("lastQueryStr", str);
				}, 500);
			}
		};

		//tForm.submit(function(){
		//	acCleanUp();
		//	jQuery(this).unbind('submit');
		//});

		jQuery(srcObj).on('keypress.ac', function(event){	//文本框禁止输入回车
			if (event.keyCode == '13') {
				event.preventDefault();
			}
		});

		jQuery(srcObj).on("keyup.ac", function(event){
			var keycode = event.keyCode;
			if (keycode == 27){ //esc
				acCleanUp();
				return false;
			}

			//acObj = acObj == undefined ? jQuery(".acwrapper", tParent) : acObj;
			var atList = jQuery("ul", acObj);
			if(acObj && atList.length && acObj.is(":visible")) {
				var sugIndex = jQuery("li.item", atList).index(jQuery("li.item.on", atList));
				switch (keycode) {
					case 13: //enter
							if(sugIndex != -1) {
								//var readyStr = jQuery("li:eq(" + sugIndex + ")", atList).text();
								completeRest(jQuery("li.item:eq(" + sugIndex + ")", atList));
								//acCleanUp();
							}
						break;
					case 33: //page up
						if(atList.length) {
							jQuery("li", atList).removeClass("on");
							jQuery("li.item:first", atList).addClass("on");
						}
						break;
					case 34: //page down
						if(atList.length) {
							jQuery("li", atList).removeClass("on");
							jQuery("li.item:last", atList).addClass("on");
						}
						break;
					case 38: //up arrow
						if(atList.length) {
							if(sugIndex == -1) {
								jQuery("li.item:last", atList).addClass("on");
							} else {
								if(sugIndex == 0) {
									sugIndex = jQuery("li.item", atList).length - 1;
								} else sugIndex--;
								jQuery("li", atList).removeClass("on");
								jQuery("li.item:eq(" + sugIndex + ")", atList).addClass("on");
							}
						}
						break;
					case 40: //down arrow
						if(atList.length) {
							if(sugIndex == -1) {
								jQuery("li.item:first", atList).addClass("on");
							} else {
								if(sugIndex == jQuery("li.item", atList).length - 1) {
									sugIndex = 0;
								} else sugIndex++;
								jQuery("li", atList).removeClass("on");
								jQuery("li.item:eq(" + sugIndex + ")", atList).addClass("on");
							}
						}
						break;
				}
			}
		});

		clearInterval(detectValTP);
		detectValTP = setInterval(function(){
			currPos = t.getCursorPos();
			var allText = t.val();
			var val = allText.substring(0, currPos);

			if (val != oldVal || oldText != allText) {
				oldVal = val;
				oldText = allText;
				t.data("promptStatus", 0);
				t.data("promptType", 0);
				t.data("evtOverRide", false);
				clearTimeout(promptSearchTP);
				//sugClickedOverRide = t.data("sugClickedOverRide");

				//判断有多少有效@
				var allVal = allText.match(atPattern2);
				var currVal = val.match(atPattern2);
				var atCount = (allVal)? allVal.length : 0;
					atarrPos = (currVal)? currVal.length : 0;

				if (atCount == 0) {
					atarrPos = 0;
					atjsonArr = [];
				}else if (atCount < atjsonArr.length) {
					for (var i=(atjsonArr.length-atCount); i>0; i--)
					atjsonArr.splice(atarrPos+i-1,1);
				}else if (atCount > atjsonArr.length) {
					if (currPos == 0){	//文本框初始状态
						for (var i=(atCount-atjsonArr.length); i>0; i--)
						atjsonArr.splice(atCount-i,0,{id: '', name: allVal[atCount-i].substring(1), type: '', note: ''});
					}else{
						for (var i=(atCount-atjsonArr.length); i>0; i--)
						atjsonArr.splice(atarrPos-i,0,{id: '', name: allVal[atarrPos-i].substring(1), type: '', note: ''});
					}
				}else if (atCount == atjsonArr.length) {
					if (atarrPos==0) atarrPos = 1;
					var atname = allVal[atarrPos-1].substring(1);
					if (atjsonArr[atarrPos-1].name != atname && atjsonArr[atarrPos-1].note != atname)
						atjsonArr[atarrPos-1] = {id: '', name: atname, type: '', note: ''};
				}
				jQuery('input[name=atjson]',tForm).val(obj2str(atjsonArr));
				t.data("atjsonArr",atjsonArr);

				//在@和#范围内
				var patval = allText;
				var arr;
				var posiInfo = [];

				while ((arr = atPattern.exec(patval)) != null) {
					posiInfo.push(arr.index + "-" + atPattern.lastIndex + "-0");
				}
				if (!noTag){
					while ((arr = tagPattern.exec(patval)) != null) {
						posiInfo.push(arr.index + "-" + tagPattern.lastIndex + "-1");
					}
				}
				jQuery.each(posiInfo, function(key, value) {
					var tempArr = value.split("-");
					if( (currPos > parseInt(tempArr[0]) && currPos <= parseInt(tempArr[1]) && 0 == parseInt(tempArr[2]))
						|| (currPos > parseInt(tempArr[0]) && currPos < parseInt(tempArr[1]) && 1 == parseInt(tempArr[2]))	) {
						t.data("promptStatus", 1);
						t.data("promptType", tempArr[2]);
						t.data("evtOverRide", true);
						t.data("evtOverRideInfo", [tempArr[0], tempArr[1]]);
						t.data("promptpos", ++tempArr[0]);
					}
				});

				//触发@或者#
				if (!t.data("evtOverRide")) {
					acCleanUp();
					clearTimeout(promptSearchTP);
					var keyword = allText.substring(currPos-1, currPos);
					if(keyword == "◎" || keyword == "@") {
						t.data("promptStatus", 1);
						t.data("promptType", 0);
						t.data("promptpos", currPos);
						var oriMsgObjVal = allText;
						t.val(oriMsgObjVal.substring(0, currPos-1) + "@" + oriMsgObjVal.substring(currPos, oriMsgObjVal.length));
						t.setCursor(srcObj, currPos);
					} 
					if (!noTag){
						if(keyword == "＃" || keyword == "#") {
							t.data("promptStatus", 1);
							t.data("promptType", 1);
							t.data("promptpos", currPos);
							var oriMsgObjVal = allText;
							t.val(oriMsgObjVal.substring(0, currPos-1) + "#" + oriMsgObjVal.substring(currPos, oriMsgObjVal.length));
							t.setCursor(srcObj, currPos);
						} else {
							var lastpos = val.lastIndexOf("#");
							if (lastpos != -1 && currPos - lastpos < 20 && /^[\u4e00-\u9fa5\w]/.exec(val.substring(lastpos+1))) {
								t.data("promptStatus", 1);
								t.data("promptType", 1);
								t.data("promptpos", lastpos+1);
								t.data("evtOverRideInfo", lastpos, currPos);
							}
						}
					}
				}
				if (t.data("promptStatus")){	//在提示状态
					promptStatus = parseInt(t.data("promptStatus"));
					promptType = parseInt(t.data("promptType"));
					promptpos = parseInt(t.data("promptpos"));
					evtOverRide = t.data("evtOverRide");
					evtOverRideInfo = t.data("evtOverRideInfo");

					var str = allText.substring(promptpos, currPos);
					acPrompt(str);
				} else {
					//acCleanUp();
					//clearTimeout(promptSearchTP);
				}

			}
		}, 10);
		t.data("detectValTP", detectValTP);
	}
}

function inputAutoComplete(event, srcObj, acObj) {
	var eventType = event.type;
	var t = jQuery(srcObj);
	var tParent = t.parent();
	var tForm = t.parents('form');
	var acParent = jQuery('#append_parent');		//默认存放.acwrapper的容器
	var acObj = acObj;								//未传值 结果为undefined
	var promptSearchTP = t.data("promptSearchTP");
	var detectValTP = t.data("detectValTP");		//输入检测计时器
	var atData = t.data("atData") || [];		    //@input所有username相关数据
	var promptSearchAjax = null;

	var acCleanUp = function() {
		var wrap = (acObj == undefined) ? jQuery(".acwrapper", acParent) : acObj;
		try {
			wrap.hide(50);
			wrap.empty();
		} catch(e) {};
			
		clearTimeout(promptSearchTP);
		t.data("evtOverRide", false);
		t.unbind("keydown.ac");
		
		if(promptSearchAjax) promptSearchAjax.abort();
	};

	if(eventType == "blur") {
		var handler = function(event){
			var wrap = (acObj == undefined) ? jQuery(".acwrapper", acParent) : acObj;
			try {
				var ex = event.pageX
				   ,ey  = event.pageY
				   ,x1  = wrap.offset().left
				   ,x2  = (wrap.offset().left+wrap.width())
				   ,y1  = wrap.offset().top
				   ,y2  = (wrap.offset().top+wrap.height());
				if (wrap.is(':visible') &&(ex < x1 || ex > x2 || ey < y1 || ey > y2) ) {
					acCleanUp();
				}
			} catch(e) {};
			if (wrap.is(':empty')){
				jQuery('body').unbind("click.ac",handler);
			}
		};
		jQuery('body').bind("click.ac",handler);
		clearInterval(detectValTP);
		jQuery(srcObj).unbind("keyup.ac");

	} else if(eventType == "focus") {
		var promptType = (t.attr('name')=='taginput'?1:0); //0:@username, 1:#tag#
		var promptpos = parseInt(t.data("promptpos"));
		var evtOverRide = t.data("evtOverRide");
		var evtOverRideInfo = t.data("evtOverRideInfo");
		var currPos;
		var oldVal = t.val();			//记录前一次文本框内的所有值
		var oldStr = t.val();			//记录前一次从0到光标处的值
		var atIndex = -1;				//当前光标在@username数组中的index值
		
		var preventDefault = function(event) {
			if(event.keyCode == 13 || event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 38 || event.keyCode == 40) {
				event.preventDefault();
			}
		};

		var acConfirmHide = function(){
			jQuery('#acconfirm', acObj||jQuery(".acwrapper", acParent)).remove();
			jQuery('#accover', acObj||jQuery(".acwrapper", acParent)).remove();
		};

		var completeRest = function(li) {
			acConfirmHide();
			var acConfirm = function(msg, acConfirmOk){
				jQuery(li).append('<div id="acconfirm"><table border=0 cellpadding=0 cellspacing=0 width=100% height=100%><tr><td align=center>'
									+msg+'<span class="btok" title="确定"></span><span class="btcl" title="取消"></span>'
									+'</td></tr></table></div><div id="accover"></div>');
				jQuery('#acconfirm .btok', li).click(function(event){event.stopPropagation();acConfirmOk();});
				jQuery('#acconfirm .btcl', li).click(function(event){event.stopPropagation();acConfirmHide();});
			};

			var dt = li.data('data');
			var rest = function(){
				var readyStr = (dt.note) ? dt.note : dt.name;
				var oriMsgObjVal = t.val();
				readyStr += " ";

				if(evtOverRideInfo != undefined && (currPos > evtOverRideInfo[0] && currPos <= evtOverRideInfo[1])) {
					t.val(oriMsgObjVal.substring(0, promptpos) + readyStr + oriMsgObjVal.substring(evtOverRideInfo[1], oriMsgObjVal.length));
				} else {
					t.val(oriMsgObjVal.substring(0, promptpos) + readyStr + oriMsgObjVal.substring(currPos, oriMsgObjVal.length));
				}
				t.setCursor(srcObj, promptpos + readyStr.length);
				if (promptType === 0) {
					atData[atIndex] = {id: dt.id+'', name: dt.name+'', type: dt.type+'', note: dt.note+''};
					jQuery('input[name=atjson]',tForm).val(obj2str(atData));
					t.data("atData",atData);
				}
				acCleanUp();
			};

			if (promptType === 1) {
				rest();
			}else if (dt.type == 'user' && li.data('followed') == '0'){
				acConfirm('是否[关注]并@'+ dt.name +' ？', function(){
					jQuery.getJSON("api/blog/api_follow.php?fromuid=" + UID + "&" + "touid=" + dt.id + "&" + getMD5([UID, dt.id]),function(data){
						if(data.success =='Y'){
							rest();
						}else{
							showDialog(data.message,'notice');
						}
					});
				});
			} else if (dt.type == 'group' && li.data('joined') == '0'){
				acConfirm('是否加入['+ dt.name +']专区,并@'+ dt.name +' ？', function(){
					jQuery.getJSON("api/blog/api_joinforum.php?uid=" + UID + "&" + "fid=" + dt.id + "&" + getMD5([UID, dt.id]), function(data){
						if(data.success =='Y'){
							rest();
						}else{
							showDialog(data.message,'notice');
						}
					});
				});
			} else {
				rest();
			}


		};

		var acPrompt = function(str) {
			if(str == null || str == "" || str == " ") {
				acCleanUp();
				return;
			}

				clearTimeout(promptSearchTP);
				promptSearchTP = setTimeout(function() {
				if(promptType === 1) {
					var searchURL = "api/blog/api_tag.php?" + "&" + "name="+ encodeURIComponent(str);
				} else {
					var searchURL = "api/blog/api_newsearch.php?uid=" + UID + "&" + "name="+ encodeURIComponent(str);
				}
				promptSearchAjax = jQuery.ajax({
				  url: searchURL,
				  dataType: 'json',
				  success: function(data) {
				  		if(data != null && data.keyword == t.data("lastQueryStr")) {
				  			var atList;
				  			if(promptType === 1 && data.tag != undefined) {
				  				if(atList == undefined) {
				  					atList = jQuery("<ul></ul>");
				  				}

				  				jQuery.each(data.tag, function(key, tag) {
				  					if(key < 5) {
				  						var li = jQuery("<li class='item cl'></li>").data("data", {name:tag.name});
				  						tag.name = tag.name.replace(str, "<span>" + str + "</span>");
				  						li.append("<a href='javascript:;'>" + tag.name + "</a>");
				  						atList.append(li);
				  					}
				  				});
				  			} else {
				  				if(data.member != undefined) {
				  					if(atList == undefined) {
				  						atList = jQuery("<ul></ul>");
				  					}

				  					var followed = '1', count = 0;
				  					jQuery.each(data.member, function(key, member) {
				  						if(count == 0) {
				  							atList.append('<li class="title">[个人]</li>');
				  							count = 1;
				  						}
				  						if(member.followed != followed) {
				  							atList.append('<li class="break">没有关注过的用户,会先关注再@提到该用户</li>');
				  							followed = '0';
				  						}

			  							var uname = member.name.replace(str, "<span>" + str + "</span>");
			  							if (member.note && member.note!='') uname += '(' + member.note.replace(str, "<span>" + str + "</span>") + ')';
			  							var li = jQuery("<li class='item cl'></li>").data("followed", member.followed).data("data",{id:member.uid,name:member.name,type:member.type,note:member.note});
			  							li.append("<img src='" + member.icon + "' /><a href='javascript:;'>" + uname + "<br /><span class='memo'>" + member.userprovince + "</span></a>");
			  							atList.append(li);
				  					});
				  				}

				  				if(data.group != undefined) {
				  					if(atList == undefined) {
				  						atList = jQuery("<ul></ul>");
				  					}

				  					var joined = '1', count = 0;
				  					jQuery.each(data.group , function(key, group) {
				  						if(count == 0) {
				  							atList.append('<li class="title">[专区]</li>');
				  							count = 1;
				  						}
				  						if(group.joined != joined) {
				  							atList.append('<li class="break">没有加入过的专区,会先加入再@提到该专区</li>');
				  							joined = '0';
				  						}

			  							var gname = group.name.replace(str, "<span>" + str + "</span>");
			  							var li = jQuery("<li class='item cl'></li>").data("joined", group.joined).data("data",{id:group.fid,name:group.name,type:group.type});
			  							li.append("<img src='" + group.icon + "' /><a href='javascript:;' style='line-height:36px;'>" + gname + "</a>");
			  							atList.append(li);
				  					});
				  				}
				  			}

				  			if(atList != undefined) {
				  				if(acObj == undefined) {
				  					if(!jQuery(".acwrapper", acParent).length) {
				  						acParent.append("<div class='acwrapper' style='z-index:999;'></div>");
				  					}
				  					var wrap = jQuery(".acwrapper", acParent);
				  					//tParent.css("position", "relative");
				  					wrap.css("left", t.offset().left + t.getCursorOffset(t, promptpos).left - 8);
				  					wrap.css("top", t.offset().top + t.getCursorOffset(t, promptpos).top + 18);
				  				} else {
				  					acObj.css({'visibility': 'hidden','display': 'block'});	//取对象的位置
				  					var tCursor = t.getCursorOffset(t, promptpos)
				  						,acwOffsetParent = acObj.offsetParent()
				  						,tOffsetParent = t.offsetParent()
				  						,tPosition = {left: tOffsetParent.offset().left - acwOffsetParent.offset().left + t.position().left
				  									  ,top: tOffsetParent.offset().top - acwOffsetParent.offset().top + t.position().top
				  									 }
				  						;
				  					acObj.css({'visibility': 'visible','display': 'none'});	//对象设为原来的值
				  						
				  					acObj.css("left", tPosition.left + tCursor.left - 8 + 35);
				  					acObj.css("top", tPosition.top + tCursor.top + 18 + 10);
				  				}
				  				jQuery("li.item:first", atList).addClass("on");

				  				jQuery("li.item", atList).on("mouseenter.ac", function() {
				  					jQuery("li", atList).removeClass("on");
				  					jQuery(this).addClass("on");
				  				});
				  				jQuery("li.item", atList).on("mouseleave.ac", function() {
				  					jQuery(this).removeClass("on");
				  				});
				  				jQuery("li.item", atList).on("mousedown.ac", function() {
				  					
				  				});
				  				jQuery("li.item", atList).on("click.ac", function() {
				  					completeRest(jQuery(this));
				  				});
				  				acObj.html(atList);

				  				acObj.css("width", "auto");
				  				acObj.css("overflow", "visible");
				  				acObj.show(50, function(){
				  					if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 7){
					  					atList.css('width', atList.width());
					  				}else if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6){
					  					atList.css('width', '240px');
					  				}
				  				});



				  				t.on("keydown.ac", preventDefault);
				  			}
				  		}
				  }
				});
				t.data("lastQueryStr", str);
				}, 100);
			
		};

		jQuery(srcObj).on('keypress.ac', function(event){	//文本框禁止输入回车
			if (event.keyCode == '13') {
				event.preventDefault();
			}
		});

		jQuery(srcObj).on("keyup.ac", function(event){
			var keycode = event.keyCode;
			if (keycode == 27){ //esc
				acCleanUp();
				return false;
			}
			
			var atList = jQuery("ul", acObj);
			if(acObj && atList.length && acObj.is(":visible")) {
				var sugIndex = jQuery("li.item", atList).index(jQuery("li.item.on", atList));
				switch (keycode) {
					case 13: //enter
							if(sugIndex != -1) {
								completeRest(jQuery("li.item:eq(" + sugIndex + ")", atList));
							}
						break;
					case 33: //page up
						if(atList.length) {
							jQuery("li", atList).removeClass("on");
							jQuery("li.item:first", atList).addClass("on");
						}
						break;
					case 34: //page down
						if(atList.length) {
							jQuery("li", atList).removeClass("on");
							jQuery("li.item:last", atList).addClass("on");
						}
						break;
					case 38: //up arrow
						if(atList.length) {
							if(sugIndex == -1) {
								jQuery("li.item:last", atList).addClass("on");
							} else {
								if(sugIndex == 0) {
									sugIndex = jQuery("li.item", atList).length - 1;
								} else sugIndex--;
								jQuery("li", atList).removeClass("on");
								jQuery("li.item:eq(" + sugIndex + ")", atList).addClass("on");
							}
						}
						break;
					case 40: //down arrow
						if(atList.length) {
							if(sugIndex == -1) {
								jQuery("li.item:first", atList).addClass("on");
							} else {
								if(sugIndex == jQuery("li.item", atList).length - 1) {
									sugIndex = 0;
								} else sugIndex++;
								jQuery("li", atList).removeClass("on");
								jQuery("li.item:eq(" + sugIndex + ")", atList).addClass("on");
							}
						}
						break;
				}
			}
		});
		
		var update_atData = function(s){
			if((s+'').length == 0) return;

			var s = trim(s).replace(/\s+/g, ' ')
			   ,arr = s.split(/\s/g)
			   ,atCount = arr.length
			   ,sub_arr = trim(s.substring(0, currPos)).split(/\s/g);

			atIndex = sub_arr.length - 1;

			if (atCount == 0) {
				atIndex = -1;
				atData = [];
			}else if (atCount < atData.length) {
				for(var i=(atData.length-atCount); i>0; i--){ atData.splice(i-atIndex-1, 1); }
			}else if (atCount > atData.length) {
				for(var i=(atCount-atData.length); i>0; i--){ atData.splice(atIndex-i+1, 0, {id: '', name: arr[atIndex-i+1], type: '', note: ''}); }
			}
			for(var i=0; i<arr.length; i++ ){
				if(arr[i]!=atData[i].name && arr[i]!=atData[i].note){
					atData.splice(i, 1, {id: '', name: arr[i], type: '', note: ''});
				}
			}

			jQuery('input[name=atjson]',tForm).val(obj2str(atData));
			t.data("atData",atData);
		}

		clearInterval(detectValTP);
		detectValTP = setInterval(function(){
			currPos = t.getCursorPos();
			
			var val = t.val();
			var str = val.substring(0, currPos);
			var promptStatus = false;

			if ((str != oldStr || val != oldVal)) {
				acCleanUp();

				oldVal = val;
				oldStr = str;

				var keyword = val.substring(currPos-1, currPos);
				if (keyword == '　' || keyword == ',' || keyword == '，'){ //以半角空格为分隔符，可接受‘全角空格’‘半角空格’‘全角逗号’‘半角逗号’
					var oriMsgObjVal = val;
					var msgVal = oriMsgObjVal.substring(0, currPos-1) + " " + oriMsgObjVal.substring(currPos, oriMsgObjVal.length);
					//t.val(msgVal.replace('　', ' ').replace(/\s+/gi, ' '));
					t.setCursor(srcObj, currPos);
					oldVal = val;
					keyword = ' ';
				}

				//在@和#范围内
				var index = 0, lastIndex = 0;
				var posiInfo = [];
				var t_val = val.replace(/(\s+)$/g, '');
				
				while (t_val.indexOf(' ', index) < t_val.length && t_val.indexOf(' ', index) > -1) {
				    lastIndex = t_val.indexOf(' ', index);
				    posiInfo.push([index, lastIndex]);
				    index = lastIndex + 1;
				}
				if(index < t_val.length) posiInfo.push([index, t_val.length]);
				
				jQuery.each(posiInfo, function(key, value) {
					var tempArr = value;
					if( currPos > parseInt(tempArr[0]) && currPos <= parseInt(tempArr[1]) ) {
						t.data("evtOverRide", true);
						t.data("evtOverRideInfo", tempArr);
						t.data("promptpos", tempArr[0]);
						promptStatus = true;
					}
				});
				
				//更新@username的json数据
				if(promptType === 0) update_atData(t_val, posiInfo);

				//新的@或者#
				if (!t.data("evtOverRide")) {
					if (keyword != ' ' && keyword.length != 0){
						t.data("promptpos", index);
						promptStatus = true;
					}
				}
			
				if (promptStatus) {
					promptpos = parseInt(t.data("promptpos"));
					evtOverRide = t.data("evtOverRide");
					evtOverRideInfo = t.data("evtOverRideInfo");
					var promptstr = val.substring(promptpos, currPos);
					acPrompt(promptstr);
				}
			}
		}, 10);
		t.data("detectValTP", detectValTP);
	}

}

/* ------------------- autoComplete end ------------------- */


/* jQuery Plugins */
/* 获取某字符在textarea或input中的坐标 */
/*
   返回：top, left
*/
(function($){
	$.fn.extend({
		getCursorPos : function() {
		    var pos = 0;
			var elem = this[0];

			if($.browser.msie && parseInt($.browser.version, 10) < 9){
				if(elem.type == 'text'){
					elem.focus();
					var sel = null;
					sel = document.selection.createRange ();
					sel.moveStart ('character', -elem.value.length);
					pos = sel.text.length;
				}else{
			        //elem.focus();
			        var range = null;
			        range = document.selection.createRange();
			        var tmpRange = range.duplicate();
			        tmpRange.moveToElementText(elem);
			        tmpRange.setEndPoint("EndToEnd", range);
			        elem.selectionStart = tmpRange.text.length - range.text.length;
			        elem.selectionEnd = elem.selectionStart + range.text.length;
			        pos = elem.selectionStart;
			    }
			} else{
				pos = elem.selectionStart;
			    //if( elem.selectionStart || elem.selectionStart == '0' ) {
			      //  pos = elem.selectionStart;
			    //}
			}
			return pos;
		},

		getCursorOffset : function(textElem, flagCharAt) {
		    var font = "'Hiragino Sans GB', 'Hiragino Kaku Gothic Pro', 'Microsoft YaHei'";

		    var isCss1 = false;

		    if ($.browser.msie && $.browser.version < 8) {
		        isCss1 = true
		    }

		    function format(h) {
		        var a = /<|>|\'|\"|&|\\|\r\n|\n| /gi;
		        var hash = {
		            "<": "&lt;",
		            ">": "&gt;",
		            '"': "&quot;",
		            "\\": "&#92;",
		            "&": "&amp;",
		            "'": "&#039;",
		            "\r": "",
		            "\n": "<br>",
		            " ": !isCss1 ? "<span style='white-space:pre-wrap;'> </span>" : "<pre style='overflow:hidden;display:inline;word-wrap:break-word;'> </pre>"
		        };

		        return h.replace(a, function (m) {
		            return hash[m]
		        });
		    }

		    //生成一个透明镜像
		    function mirror($element) {
		        this.ele = $element;

		        this.init();
		    }
		    mirror.prototype = {
		        //panel
		        $p : null,

		        //要测试的对象的位置
		        $f : null,

		        css : ["overflowY", "height", "width", "paddingTop", "paddingLeft", "paddingRight", "paddingBottom", "marginTop", "marginLeft", "marginRight", "marginBottom"
		            ,'fontFamily', 'borderStyle', 'borderWidth', 'wordWrap', 'fontSize', 'overflowX'],

		        init : function() {
		            var $p = this.$p = $('<div></div>');

		            var css = {opacity: 0, position: 'absolute', left: 0, top:0, zIndex: -20000},
		                $ele = this.ele;

		            css = $.extend({
		                fontFamily: font,
		                borderStyle: "solid",
		                borderWidth: "0px",
		                wordWrap: "break-word",
		                fontSize: "14px",
		                lineHeight: "21px",
		                overflowX: "hidden"
		            }, css);

		            $.each(this.css, function(i, p){
		                css[p] = $ele.css(p);
		            });

		            $p.css(css);
		            $('body').append($p);
		        },

		        setContent : function(front, flag, end) {
		            var $p = this.$p, $flag;
		            $p.html('<span>' + format(front) + '</span>');
		            this.$f = $flag = $('<span>' + format(flag) + "i" + '</span>');
		            $p.append($flag);
		            $p.append('<span>' + format(end) + '</span>');
		        },

		        getPos : function() {
		            return this.$f.position();
		        }
		    }

		    return function() {
		        var $textElem = $(textElem);
		        if (!$textElem.data('mirror')) {
		            $textElem.data('mirror', new mirror($textElem));
		        }

		        var $mirror = $textElem.data('mirror');

		        if (!$mirror) {
		            return {};
		        }

		        var text = $textElem.val(),
		        frontContent = text.substring(0, flagCharAt),
		        flag = text.charAt(flagCharAt),
		        lastContent = text.substring(flagCharAt+1);
		        $mirror.setContent(frontContent, flag, lastContent);

		        return $mirror.getPos();
		    }();
		},

		setCursor : function(elem, pos, coverLen){
		    pos = pos == null ? elem.value.length : pos;
		    coverLen = coverLen == null ? 0 : coverLen;
		    elem.focus();
		    if(elem.createTextRange){
		        var range = elem.createTextRange();
		        range.move("character", pos);
		        range.moveEnd("character", coverLen);
		        range.select();
		    }else {
		        elem.setSelectionRange(pos, pos + coverLen);
		    }
		}
	});
})(jQuery);

