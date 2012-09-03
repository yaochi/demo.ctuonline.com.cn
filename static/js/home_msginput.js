var baseActAddress = "home.php?mod=spacecp";
var atstr = jQuery("input[name=atinput]").val();
var tagstr = jQuery("input[name=taginput]").val();
var msgobj = jQuery("textarea[name=msginput]");
var anonymitySign = jQuery("input[name=anonymity]");
var loadingGif = "static/image/common/loading.gif";
var msgOriHeight = msgobj.height();
var blogEditor, swfu;

var mifmData = jQuery.parseJSON('{' + 
				'"doing": {"actAddress": "&ac=blog&handlekey=mifm",' + 
						  '"msgstr": "工作学习中有什么收获和体会？生活中有什么新鲜事？写在这里与大家分享…",' + 
						  '"msgHeight": 100, "privacy": 1},' +
				'"doingAdv": {"actAddress": "&ac=blog&handlekey=mifm",' + 
						  '"msgstr": "工作学习中有什么收获和体会？生活中有什么新鲜事？写在这里与大家分享…",' + 
						  '"titlestr": "记录标题（可选）",' + 
						  '"msgHeight": 300, "privacy": 1},' +
				'"share": {"actAddress": "&ac=share&type=link&view=me&from=&fchannel=xmenus&handlekey=mifm",' + 
						  '"msgstr": "分享说明（可选）",' + 
						  '"linkstr": "输入或粘贴一个网址",' + 
						  '"msgHeight": 56, "privacy": 1},' +
				'"pic": {"actAddress": "&ac=album&op=upload&handlekey=mifm",' + 
						  '"msgstr": "图片说明（可选）",' + 
						  '"imagestr": "点击以添加图片",' + 
						  '"msgHeight": 56, "privacy": 1},' +
				'"video": {"actAddress": "&ac=share&type=link&view=me&from=&fchannel=xmenus&handlekey=mifm",' + 
						  '"msgstr": "视频说明（可选）",' + 
						  '"linkstr": "外部视频所在网页的网址 现支持Youtube/优酷/酷6/偶偶视频/56/新浪视频/搜狐视频",' + 
						  '"videostr1": "输入或粘贴外部视频网址",' + 
						  '"videostr2": "上传本地视频",' + 
						  '"videostr3": "点击以添加视频 文件后缀名须为flv 容量不超过50 MB",' + 
						  '"msgHeight": 56, "privacy": 1},' +
			    '"music": {"actAddress": "&ac=share&type=link&view=me&from=&fchannel=xmenus&handlekey=mifm",' + 
			    		  '"msgstr": "音乐说明（可选）",' + 
			    		  '"linkstr": "输入或粘贴音乐文件的链接 文件后缀名须为mp3或wma",' + 
			    		  '"msgHeight": 56, "privacy": 1}' +
			  	'}');
				
function getMifmDataValue(key1, key2) {
	if(key1 && key2) {
		var val;
		jQuery.each(mifmData[key1], function(key, value) {
			val = value;
			return key != key2;
		});
		return val;
	}
	return null;
}

function getInputStr(name) {
	if(name == "atstr" || name == "tagstr") {
		return eval(name);
	} else {
		if(mifmLastOpen) {
			return getMifmDataValue(mifmLastOpen, name);
		}
		return null;
	}
}

/* 发布框open核心函数 */
/* 首先调用上一次类型的清理函数 待清理函数确认完毕后继续
   尔后为mifm添加class, form增加action, 恢复@/#提示, msginput文字内容保存/恢复, 动画
   其余事务均由类型各自完成
*/
function mifm_coreOpen(target, oDTD) {
	var animateOff = false;
	
	var stdProcedure = function(oDTD) {
		jQuery(".mifm").addClass("mi_" + target);
		jQuery("#addPostForm").attr("action", baseActAddress + mifmData[target].actAddress);
		mifmData[target].privacy ? jQuery("#mifm_navi .pss").fadeIn("fast") : jQuery("#mifm_navi .pss").fadeOut("fast");
		
		if(!animateOff) {
			msgobj.animate({
				height: mifmData[target].msgHeight
			}, 800, 'easeOutExpo', function(){});
			jQuery("#mifm_close").css("display", "block");
		}
		msgobj.css("cursor", "text");
		msgobj.off("focus", quickOpen);
			
		mifmLastOpen = target;
		oDTD.resolve();
	};
	
	if(mifmLastOpen) {
		msgobj.stop();
		var cDTD = new jQuery.Deferred();
		eval("mifm_" + mifmLastOpen + "Close(0, cDTD)");
		jQuery.when(cDTD.promise()).done(function() {
				jQuery(".mifm").removeClass("mi_" + mifmLastOpen);
				(msgobj.val() == mifmData[mifmLastOpen].msgstr || msgobj.val() == "") ? msgobj.val(mifmData[target].msgstr) : animateOff = true;
				stdProcedure(oDTD);
			}
		);
	} else {
		//jQuery("input[name=atinput]").val(atstr);
		//jQuery("input[name=taginput]").val(tagstr);
		//addInputListener(jQuery("input[name=atinput]"), "autocomplete2");
		//addInputListener(jQuery("input[name=taginput]"), "autocomplete2");
		//if(msgstrPending) {
		//	msgstrPending = false;
		//	msgobj.removeClass("xg0");
		//	msgobj.val(msgstrPendingParam.msgstr);
		//	showLimitedGroupNotice(msgstrPendingParam.fid);
		//} else{
			msgobj.val(mifmData[target].msgstr);
			checkGroupLink();
		//}
		
		//jQuery("#atcontainer").slideDown("fast");
		//jQuery("#tagcontainer").slideDown("fast");
		jQuery("#addsubmit_btn").slideDown("fast");
		
		stdProcedure(oDTD);
	}
}

function mifm_coreClose(){
	jQuery(".mifm").removeClass("mi_" + mifmLastOpen);
	jQuery("#addPostForm").removeAttr("action");
	
	msgobj.stop();
	msgobj.val(mifmData["doing"].msgstr);
	//jQuery("input[name=atinput]").val(atstr);
	//jQuery("input[name=taginput]").val(tagstr);
	//jQuery("#atcontainer").slideUp("fast");
	//jQuery("#tagcontainer").slideUp("fast");
	jQuery("#addsubmit_btn").slideUp("fast");
	jQuery("#addsubmit_btn").removeClass("on");
	jQuery("#addsubmit_btn").attr("disabled", "disabled");
	jQuery("#mifm_navi .pss").fadeOut("fast");
	anonymitySign.val(0);
	resetPssValue(jQuery("#mifm_navi .pss a"), 'privacyValue');
	
	jQuery("#mifm_notice").hide();  //隐藏封闭专区提醒信息
	
	jQuery("#mifm_close").css("display", "none");
	
	msgobj.animate({
		height: msgOriHeight
	}, 800, 'easeOutExpo', function(){});
	msgobj.on("focus", quickOpen);
	msgobj.css("cursor", "pointer");
	msgobj.blur();
	
	jQuery("textarea[name=msginput]").each(
		function() {
			if(!jQuery(this).hasClass("xg0")) {
				jQuery(this).addClass("xg0");
			}
		}
	);

	mifmLastOpen = null;
}

function mifmSubmit() {
	jQuery(".mifm_mask").css("display", "block");
	jQuery(".mifm_mask").css("height", jQuery(".mifm").outerHeight());
	jQuery(".mifm_mask").css("lineHeight", jQuery(".mifm").outerHeight() + "px");
	jQuery(".mifm_mask").animate({
		opacity: 0.95
	}, 500, function(){
		//发布提交前格式化内容
		var ta = jQuery('.mifm textarea[name=msginput]');			//获取发布文本框
		
		var atinput = jQuery(".mifm input[name=atinput]");			//获取@文本框
		if(atinput.val() == atstr) { 
			atinput.val(""); 
		}else if (ta.data('open') == 'doingAdv'){
			atinput.val(trim(atinput.val().replace(/\s+/g, ' '))); 	//合并连续多个空格
		}
		
		var taginput = jQuery(".mifm input[name=taginput]");		//获取#文本框
		if(taginput.val() == tagstr) { 
			taginput.val(""); 
		}else if (ta.data('open') == 'doingAdv'){
			taginput.val(trim(taginput.val().replace(/\s+/g, ' '))); //合并连续多个空格
		}
		
		if (ta.data('open') != 'doingAdv') {ta.val(HTMLEncode(ta.val()));} //普通发布框 输入html输出text
		else {ta.val(ta.val().replace(/<\/(p|h1|h2|h3|h4|h5|h6|pre|li|blockquote)>/g, function($1){return '&nbsp;'+$1;}))} //高级编辑器 在</p>前插入&nbsp;
		
		ajaxpost("addPostForm", 'return_mifm');
	});
	
}

function mifm_doingOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("doing", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		jQuery("#editor_switch").show(150);
		addInputListener(msgobj, "must");
		addInputListener(msgobj, "autocomplete");
		inputMustListener();	//检测文本框已存在内容是否可发布
		msgobj.growfield({
			min: mifmData["doing"].msgHeight
		});
		msgobj.focus();
	});
}

function mifm_doingClose(isFullClose, cDTD){
	var run = function() {
		jQuery("#editor_switch").hide(150);
		removeInputListener(msgobj, "must");
		removeInputListener(msgobj, "autocomplete");
		published = false;
		if(isFullClose){
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
	}
	
	if(!published && isFullClose && msgobj.val() != mifmData["doing"].msgstr && msgobj.val().length > 10) {
		showDialog("确定放弃您所填写内容?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_doingValidate() {
	var msgval = jQuery.trim(msgobj.val());
	if(msgval == "" || msgval == mifmData["doing"].msgstr) {
		return false;
	} else return true;
}

function mifm_doingAdvOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("doingAdv", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		var str1 = "javascript:mifmOpen('doing')";
		var str2 = "javascript:mifmOpen('doingAdv')";
		jQuery("#editor_switch").text("简单功能");
		jQuery("#editor_switch").attr("href", str1);
		jQuery("#editor_switch").show(150);
		jQuery("#ar_doing").attr("href", str2);
		jQuery("#titleinput").val(mifmData["doingAdv"].titlestr);
		jQuery("#atcontainer").slideDown("fast");
		jQuery("#tagcontainer").slideDown("fast");
		addInputListener(jQuery("input[name=atinput]"), "autocomplete2");
		addInputListener(jQuery("input[name=taginput]"), "autocomplete2");
		
		msgobj.val() == mifmData["doingAdv"].msgstr ? msgobj.val("") : "";
		
		var loadingGifStr = "url(" + loadingGif +") no-repeat center";
		msgobj.css("background", loadingGifStr);
		var editorInit = function() {
			KindEditor.basePath = 'static/js/kindeditor/';
			blogEditor = KindEditor.create(msgobj, 
			{
				resizeType: 0,
				items : [
						'source', '|', 'undo', 'redo', '|',   'cut', 'copy', 'paste',
						'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
						'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
						'superscript', 'clearhtml', 'quickformat', 'selectall', 'fullscreen', '/',
						'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
						'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image','media',
						'insertfile', 'table', 'hr', 'pagebreak',
						'link', 'unlink'
						],
				width: "100%", 
				height: mifmData["doingAdv"].msgHeight,
				afterChange: inputMustListener
			});
			
			/* 借用监听器使高级编辑器创建后focus & validate */
			var editorIntervalProcess = setInterval(function() {
				try{
				    blogEditor.focus();
				    inputMustListener();
				    msgobj.css("background", "none");
				    clearInterval(editorIntervalProcess);
				}catch(e) {};
			}, 500);
		};
		if(scriptLoaded.kindeditor) {
			editorInit();
		} else {
			scriptLoaded.kindeditor = true;
			jQuery.getScript('static/js/kindeditor/kindeditor.js', editorInit);
		}
	});
}

function mifm_doingAdvClose(isFullClose, cDTD){
	var run = function() {
		var str1 = "javascript:mifmOpen('doing')";
		var str2 = "javascript:mifmOpen('doingAdv')";
		jQuery("#editor_switch").hide(150);
		jQuery("#editor_switch").text("高级功能");
		jQuery("#editor_switch").attr("href", str2);
		jQuery("#ar_doing").attr("href", str1);
		msgobj.css("background", "none");
		published = false;
		if (blogEditor) {
			blogEditor.html("");
			msgobj.addClass("xg0");
			blogEditor.remove();
			blogEditor = null;
		}
		if(!jQuery("#titleinput").hasClass("xg0")) {
			jQuery("#titleinput").addClass("xg0");
		}
		if(isFullClose) {
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
			
		jQuery("#titleinput").val('');
		jQuery("input[name=atinput], input[name=taginput]").each(
			function() {
				if(!jQuery(this).hasClass("xg0")) {
					jQuery(this).addClass("xg0");
				}
			}
		);
		jQuery("#atcontainer").slideUp("fast");
		jQuery("#tagcontainer").slideUp("fast");
		jQuery("input[name=atinput]").val(atstr);
		jQuery("input[name=taginput]").val(tagstr);
		removeInputListener(jQuery("input[name=atinput]"), "autocomplete2");
		removeInputListener(jQuery("input[name=taginput]"), "autocomplete2");
		jQuery("#addPostForm input[name=atjson]").val('');
	}
	
	if(!published && !blogEditor.isEmpty()) {
		showDialog("确定放弃您所填写内容?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_doingAdvValidate() {
	try{
	    if(blogEditor.html() == "") {
	    	return false;
	    } else return true;
	}catch(e) {};
}

function mifm_picOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("pic", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		jQuery("#imageinput").append("<div id='divFileProgressContainer'></div><span id='spanButtonPlaceholder'></span><div id='thumbnails'></div>");
		jQuery("#imageinput > p").text("初始化...");
		jQuery("#addPostForm").append("<input type='hidden' name='picpath' value=''>" + 
											  "<input type='hidden' name='pictitle' value=''>" +
											  "<input type='hidden' name='picname' value=''>" +
											  "<input type='hidden' name='pictype' value=''>" +
											  "<input type='hidden' name='picsize' value=''>");
		inputMustListener();
		addInputListener(msgobj, "autocomplete");
		msgobj.growfield({
			min: mifmData["pic"].msgHeight 
		});

		var swfLoaded = function() {
			jQuery("#imageinput > p").text(mifmData["pic"].imagestr);
		}
		
		var swfuploadInit = function() {
			if(scriptLoaded.swfupload && scriptLoaded.swfhandlers) {
				swfu = new SWFUpload({
					// Backend Settings
					upload_url: "static/js/swfupload/img_upload.php",
					post_params: {"PHPSESSID": getcookie('PHPSESSID')},
		
					// File Upload Settings
					file_size_limit : "5 MB",	// 2MB
					file_types : "*.jpg;*.png;*.gif",
					file_types_description : "Images",
					file_upload_limit : "24",
		
					// Event Handler Settings - these functions as defined in Handlers.js
					file_queued_handler: img_fileQueued,
					file_queue_error_handler : img_fileQueueError,
					file_dialog_complete_handler : img_fileDialogComplete,
					upload_progress_handler : img_uploadProgress,
					upload_error_handler : img_uploadError,
					upload_success_handler : img_uploadSuccess,
					upload_complete_handler : img_uploadComplete,
					swfupload_loaded_handler: swfLoaded,
		
					// Button Settings
					button_placeholder_id : "spanButtonPlaceholder",
					button_width: 586,
					button_height: 36,
					button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
					button_cursor: SWFUpload.CURSOR.HAND,
					
					// Flash Settings
					flash_url : "static/js/swfupload/swfupload.swf",
		
					custom_settings : {
						upload_target : "divFileProgressContainer"
					},
					
					// Debug Settings
					debug: false
				});
			}
		};
		
		if(scriptLoaded.swfupload && scriptLoaded.swfhandlers) {
			swfuploadInit();
		} else {
			jQuery.getScript('static/js/swfupload/swfupload.js', function(){
				scriptLoaded.swfupload = true;
				swfuploadInit();
			});
			jQuery.getScript('static/js/swfupload/handlers.js', function(){
				scriptLoaded.swfhandlers = true;
				swfuploadInit();
			});
		}
	});
}

function mifm_picClose(isFullClose, cDTD){
	var run = function() {
		jQuery("#imageinput").empty();
		jQuery("#imageinput").append("<p class='xg0'></p>");
		jQuery("#addPostForm input[name ^= 'pic']").remove();
		removeInputListener(msgobj, "autocomplete");
		if (swfu) {
			swfu.destroy();
			swfu = null;
		}
		published = false;
		if(isFullClose) {
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
	}
	
	if(!published && mifm_picValidate()) {
		showDialog("确定放弃您所上传的图片?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_picValidate() {
	if(jQuery("#thumbnails > div").length > 0) {
		return true;
	} else return false;
}

function mifm_shareOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("share", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		jQuery("#linkinput").val(mifmData["share"].linkstr);
		addInputListener(msgobj, "must");
		addInputListener(jQuery("#linkinput"), "must");
		addInputListener(msgobj, "autocomplete");
		inputMustListener();
		msgobj.growfield({
			min: mifmData["share"].msgHeight
		});
	});	
}

function mifm_shareClose(isFullClose, cDTD){
	var run = function() {
		removeInputListener(msgobj, "must");
		removeInputListener(jQuery("#linkinput"), "must");
		removeInputListener(msgobj, "autocomplete");
		if(!jQuery("#linkinput").hasClass("xg0")) {
			jQuery("#linkinput").addClass("xg0");
		}
		published = false;
		if(isFullClose){
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
	}
	
	if(!published && isFullClose && msgobj.val() != mifmData["share"].msgstr && msgobj.val().length > 10) {
		showDialog("确定放弃您所填写内容?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_shareValidate() {
	if(jQuery("#linkinput").val() == "" || jQuery("#linkinput").val() == mifmData["share"].linkstr) {
		return false;
	} else return true;
}

function mifm_videoOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("video", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		var viobj = jQuery("#videoinput");
		var viobjMinor = jQuery("#videoinputminor");
		
		viobj.append("<a href='javascript:;' id='scsellocal' class='scsel' style='position: absolute; right:0; '>" + mifmData["video"].videostr2 + "</a>"
					 + "<a href='javascript:;' id='scseloutside' class='scsel'>" + mifmData["video"].videostr1 + "</a>"
					 + "<div class='scsel_arrow'></div>");
									 	 
		jQuery(".scsel", viobj).click(function() {
			var selIndi = function(obj) {
				jQuery(".scsel", viobj).removeClass("on");
				obj.addClass("on");
				jQuery(".scsel_arrow", viobj).css("marginLeft", obj.position().left + obj.width() / 2 - jQuery(".scsel_arrow", viobj).width() / 2);
				jQuery(".scsel_arrow", viobj).css("display", "block");
			};
			
			if(jQuery(this).attr("id") == "scseloutside") {
				var switch2Outside = function() {
					selIndi(jQuery("#videoinput #scseloutside"));
										
					viobjMinor.empty();
					viobjMinor.append("<p class='xg0'></p>");
					viobjMinor.removeClass("off");
					viobjMinor.css("display", "none");
					
					if (swfu) {
						swfu.destroy();
						swfu = null;
					}
					
					addInputListener(jQuery("#linkinput"), "must");
					jQuery("#linkinput").val(mifmData["video"].linkstr);
					jQuery("#linkinput").css("display", "block");
					jQuery("#linkinput").focus();
				};
				
				if(viobjMinor.css("display") != "none") {
					if(mifm_videoValidate()) {
						showDialog("确定放弃您所上传的视频?", "confirm", "", function(){ switch2Outside(); });
					} else switch2Outside();
				} else {
					selIndi(jQuery(this));
					addInputListener(jQuery("#linkinput"), "must");
					jQuery("#linkinput").slideDown("fast");
					jQuery("#linkinput").focus();
				}
			} else if (jQuery(this).attr("id") == "scsellocal") {
				selIndi(jQuery(this));
				removeInputListener(jQuery("#linkinput"), "must");
				jQuery("#linkinput").css("display") != "none" ? viobjMinor.css("display", "block") : viobjMinor.slideDown("fast");
				jQuery("#linkinput").css("display", "none");
				
				viobjMinor.append("<div id='divFileProgressContainer'></div><span id='spanButtonPlaceholder'></span><div id='thumbnails'></div>");
				jQuery("p", viobjMinor).text("初始化...");
				
				if(scriptLoaded.swfupload && scriptLoaded.swfhandlers) {
					swfuploadInit();
				} else {
					jQuery.getScript('static/js/swfupload/swfupload.js', function(){
						scriptLoaded.swfupload = true;
						swfuploadInit();
					});
					jQuery.getScript('static/js/swfupload/handlers.js', function(){
						scriptLoaded.swfhandlers = true;
						swfuploadInit();
					});
				}			
			}
		});
		
		var swfLoaded = function() {
			jQuery("p", viobjMinor).text(mifmData["video"].videostr3);			
		}
		
		var swfuploadInit = function() {
			if(scriptLoaded.swfupload && scriptLoaded.swfhandlers) {
				swfu = new SWFUpload({
					// Backend Settings
					upload_url: VIDEOUPLOADURL,
					post_params: {"PHPSESSID": getcookie('PHPSESSID'), "uid": UID},
		
					// File Upload Settings
					file_size_limit : "50 MB",	// 2MB
					file_types : "*.flv",
					file_types_description : "media",
					file_upload_limit : "1",
		
					// Event Handler Settings - these functions as defined in Handlers.js
					file_queued_handler: video_fileQueued,
					file_queue_error_handler : video_fileQueueError,
					file_dialog_complete_handler : video_fileDialogComplete,
					upload_progress_handler : video_uploadProgress,
					upload_error_handler : video_uploadError,
					upload_success_handler : video_uploadSuccess,
					upload_complete_handler : video_uploadComplete,
					swfupload_loaded_handler: swfLoaded,
		
					// Button Settings
					button_placeholder_id : "spanButtonPlaceholder",
					button_width: 586,
					button_height: 36,
					button_action : SWFUpload.BUTTON_ACTION.SELECT_FILE,
					button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
					button_cursor: SWFUpload.CURSOR.HAND,
					
					// Flash Settings
					flash_url : "static/js/swfupload/swfupload.swf",
		
					custom_settings : {
						upload_target : "divFileProgressContainer"
					},
					
					// Debug Settings
					debug: false
				});
			}
		};
				
		jQuery("#linkinput").val(mifmData["video"].linkstr);
		addInputListener(msgobj, "autocomplete");
		inputMustListener();
		msgobj.growfield({
			min: mifmData["video"].msgHeight
		});
	});
}

function mifm_videoClose(isFullClose, cDTD){
	var run = function() {
		jQuery("#videoinput").empty();
		jQuery("#videoinputminor").empty();
		jQuery("#videoinputminor").append("<p class='xg0'></p>");
		jQuery("#videoinputminor").removeClass("off");
		
		jQuery("#linkinput").attr("style", "");
		jQuery("#videoinputminor").attr("style", "");
		
		removeInputListener(jQuery("#linkinput"), "must");
		removeInputListener(msgobj, "autocomplete");
		if(!jQuery("#linkinput").hasClass("xg0")) {
			jQuery("#linkinput").addClass("xg0");
		}
		if (swfu) {
			swfu.destroy();
			swfu = null;
		}
		published = false;
		if(isFullClose){
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
	}
	
	if(!published && jQuery("#videoinput .scsel.on").attr("id") == "scsellocal" && mifm_videoValidate()) {
		showDialog("确定放弃您所上传的视频?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_videoValidate() {
	var nowObjID = jQuery("#videoinput .scsel.on").attr("id");
	if(nowObjID == "scseloutside") {
		if(jQuery("#linkinput").val() == "" || jQuery("#linkinput").val() == mifmData["video"].linkstr) {
			return false;
		} else return true;
	} else if(nowObjID == "scsellocal") {
		if(jQuery("#thumbnails").children().length > 0) {
			return true;
		} else return false;
	}
}

function mifm_musicOpen() {
	var oDTD = new jQuery.Deferred();
	mifm_coreOpen("music", oDTD);
	jQuery.when(oDTD.promise()).done(function() {
		jQuery("#linkinput").val(mifmData["music"].linkstr);
		addInputListener(jQuery("#linkinput"), "must");
		addInputListener(msgobj, "autocomplete");
		inputMustListener();
		msgobj.growfield({
			min: mifmData["music"].msgHeight
		});
	});
}

function mifm_musicClose(isFullClose, cDTD){
	var run = function() {
		removeInputListener(jQuery("#linkinput"), "must");
		removeInputListener(msgobj, "autocomplete");
		if(!jQuery("#linkinput").hasClass("xg0")) {
			jQuery("#linkinput").addClass("xg0");
		}
		published = false;
		if(isFullClose){
			msgobj.growfield('destroy');
			mifm_coreClose();
		} else cDTD.resolve();
	}
	
	if(!published && isFullClose && msgobj.val() != mifmData["music"].msgstr && msgobj.val().length > 10) {
		showDialog("确定放弃您所填写内容?", "confirm", "", function(){ run() });
	} else run();
}

function mifm_musicValidate() {
	if(jQuery("#linkinput").val() == "" || jQuery("#linkinput").val() == mifmData["music"].linkstr) {
		return false;
	} else return true;
}

/* 发布框focus/blur时 文字提示处理函数 */
jQuery(".mifm [name$='input']").focus(
	function(){
		var targetName = jQuery(this).attr("name") == undefined ? jQuery(this).attr("id") : jQuery(this).attr("name");
		var eventname = targetName.replace("input", "");
		if(jQuery(this).val() == getInputStr(eventname + "str")) {
			jQuery(this).removeClass("xg0");
			jQuery(this).val("");
		}
	}
).blur(
	function(){
		var targetName = jQuery(this).attr("name") == undefined ? jQuery(this).attr("id") : jQuery(this).attr("name");
		var eventname = targetName.replace("input", "");
		if(jQuery(this).val() == "") {
			jQuery(this).addClass("xg0");
			jQuery(this).val(getInputStr(eventname + "str"));
		}
	}
);

function textAreaACRegisterFun(event) {
	textAreaAutoComplete(event, this, jQuery('#ct>div.acwrapper'));
}

function inputACRegisterFun(event) {
	inputAutoComplete(event, this, jQuery('.mifm .acwrapper'));
}

function addInputListener(target, type) {
	var func;
	if(type == "must") {
		func = "inputMustListener";
		if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
			jQuery(target).on("propertychange", eval(func));
		} else {
			jQuery(target).on("input", eval(func));
		}
	} else if (type == "autocomplete") {
		if (!jQuery('#ct>div.acwrapper').length) jQuery('#ct').prepend("<div class='acwrapper'></div>");
		//jQuery(target).on("keyup", textAreaACRegisterFun);
		//jQuery(target).on("click", textAreaACRegisterFun);
		jQuery(target).on("focus", textAreaACRegisterFun);
		jQuery(target).on("blur", textAreaACRegisterFun);
		//if(jQuery.browser.opera) {
		//	jQuery(target).on("input", textAreaACRegisterFun);
		//}
	} else if (type == "autocomplete2") {
		/*jQuery(target).on("keyup", inputACRegisterFun);
		jQuery(target).on("click", inputACRegisterFun);
		jQuery(target).on("focus", inputACRegisterFun);
		jQuery(target).on("blur", inputACRegisterFun);
		if(jQuery.browser.opera) {
			jQuery(target).on("input", inputACRegisterFun);
		}*/
		jQuery(target).on("focus", inputACRegisterFun);
		jQuery(target).on("blur", inputACRegisterFun);
	}
}

function removeInputListener(target, type) {
	var func;
	if(type == "must") {
		func = "inputMustListener";
		if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
			jQuery(target).off("propertychange", eval(func));
		} else {
			jQuery(target).off("input", eval(func));
		}
	} else if (type == "autocomplete") {
		jQuery('#ct>div.acwrapper').remove();
		//jQuery(target).off("keyup", textAreaACRegisterFun);
		//jQuery(target).off("click", textAreaACRegisterFun);
		jQuery(target).off("focus", textAreaACRegisterFun);
		jQuery(target).off("blur", textAreaACRegisterFun);
		//if(jQuery.browser.opera) {
		//	jQuery(target).off("input", textAreaACRegisterFun);
		//}
	} else if (type == "autocomplete2") {
		/*jQuery(target).off("keyup", inputACRegisterFun);
		jQuery(target).off("click", inputACRegisterFun);
		jQuery(target).off("focus", inputACRegisterFun);
		jQuery(target).off("blur", inputACRegisterFun);
		if(jQuery.browser.opera) {
			jQuery(target).off("input", inputACRegisterFun);
		}*/
		jQuery(target).off("focus", inputACRegisterFun);
		jQuery(target).off("blur", inputACRegisterFun);
	}
}

function inputMustListener() {
	var mustFlag = eval("mifm_" + mifmLastOpen + "Validate()");
	
	if(mustFlag) {
		jQuery("#addsubmit_btn").addClass("on");
		jQuery("#addsubmit_btn").removeAttr("disabled");
	} else {
		jQuery("#addsubmit_btn").removeClass("on");
		jQuery("#addsubmit_btn").attr("disabled", "disabled");
	}
}