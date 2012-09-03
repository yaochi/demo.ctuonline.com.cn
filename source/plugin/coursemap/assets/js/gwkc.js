var panel = ['.post_box_zqlb','.post_box_zzqlb','.post_box_jzgwlb','.post_box_kcmclb'];
var cols = ['one','two','three'];
var anchor = jQuery('#post_course_popup');	//课程面板
var _queue = null;							//显示课程详情延迟队列ID
var _ajax = null;							//获取课程详情API ID
var _seajax = null;							//查询岗位课程API ID
var ex = null;								//前一项高亮的岗位LI

jQuery(panel[0], anchor).on('click', 'a', function(){		//level 0
	getCourseByLevel(jQuery(this).attr('class'), 0);
});
jQuery(panel[1], anchor).on('click', 'a', function(){		//level 1
	getCourseByLevel(jQuery(this).attr('class'), 1);
});
jQuery(panel[2], anchor).on('click','a', function(){		//level 2
	getCourseByLevel(jQuery(this).attr('class'), 2);
});
jQuery(panel[2], anchor).on('mouseenter','li:not([class=selected])', function(){		//level 2
	jQuery(this).addClass('hover');
});
jQuery(panel[2], anchor).on('mouseleave', 'li:not([class=selected])', function(){		//level 2
	jQuery(this).removeClass('hover');
});
jQuery(panel[3], anchor).on('mouseenter', 'li', courseHandler);	//level 3
jQuery(panel[3], anchor).on('click', 'li', function(){			//level 3
	if(_queue) clearTimeout(_queue);
	showCourseDetail(jQuery(this));
});
jQuery(panel[2], anchor).on('click', '.post_btn_jgz', function(){	//岗位加关注
	var stationid = jQuery(this).prev('a').attr('class') || 0;
	var sname = jQuery(this).prev('a').text();
	setStation(stationid, sname);
	getCourseByLevel(stationid, 2);
});

//显示格式化
jQuery(panel[3]+' a', anchor).each(function(){			//课程列表：课程标题格式化
	jQuery(this).text(suitStr(jQuery(this).text(), 34))
});
jQuery(panel[3]+' li:first', anchor).trigger('click');	//课程列表：第一条课程展开

if(mystation != '' && !isNaN(mystation)){
	changeStationMark(true);
}

/* 文档预格式化 结束 */

function getCourseByLevel(pid, l){
	if(_ajax) _ajax.abort();
	_ajax = jQuery.getJSON("api.php?mod=plugin&app=coursemap:api&param="+encodeURIComponent('action=getchild&pid='+pid+'&level='+l), function(data){
		if(data && data.error == null){
			
			
			jQuery(panel[l]+' li.selected',anchor).removeClass('selected');
			jQuery(panel[l]+' a.'+data.use[cols[l]],anchor).parent('li').addClass('selected');
			
			if (l < 1 && data.child){
				showStation(data.child, panel[1], data.use.two);
			}
			if (l < 2 && data.station){
				showStation(data.station, panel[2], data.use.three);
			}
			if (l < 3 && data.course){
				showCourse(data.course, panel[3]);
			}
		}
		
	});
	
}

function showStation(d, p, sel){
	var s = '';
	
	jQuery.each(d, function(k, v){
		s += '<li'+(sel==v.id?' class="selected"':'')+'> '
		   + '<a href="javascript:;" title="'+v.name+'" class="'+v.id+'">'+v.name+'</a> '
		   + (p == panel[2] ? (v.id==mystation?'<span class="post_btn_qxgz">已关注</span>':'<span class="post_btn_jgz" title="关注">关注</span>') : '' ) 
		   + '</li>';
	});
	jQuery(p+' ul', anchor).html(s);
	
	//if jQuery(p, anchor)
}

function showCourse(d, p){
	var s = '';
	
	jQuery.each(d, function(k, v){
		s += '<li><a href="javascript:;" title="'+v.coursename+'" class="'+v.coursecode+'">'+suitStr(v.coursename, 34)+'</a></li>';
	});
	jQuery(p+' ul', anchor).html(s);
	
	jQuery(p+' li:first', anchor).trigger('click');
}

function showCourseDetail(li){
	if(ex && ex == li || li.hasClass('selected')) return false;
	ex = li;
	var extitle = li.text();

	jQuery(panel[3], anchor).off('mouseenter', 'li');
	
	var ul = li.parent('ul');
	var a = li.children('a');
	var panObj = jQuery('div.panel', li);
	
	 
	jQuery('li', ul).children('a:hidden').show().next('div.panel').slideUp(200, function(){
		jQuery(this).parent('li').removeClass('selected');
	});
	li.addClass('selected');
	a.hide();

	if(panObj.length == 0){
		var code = a.attr('class');
		
		panObj = jQuery('<div class="panel" style="display:none;"><img src="'+IMGDIR+'/loading.gif" /></div>');
		li.append(panObj);
		panObj.slideDown(200);
		
		jQuery.getJSON("api.php?mod=plugin&app=coursemap:api&param="+encodeURIComponent('action=getcourseinf&code='+code), function(data){
			var s = '<<strong>' + extitle + '</strong>>, 暂时无法显示。';
			if(data && data.error == null){
				s =     '<div> '
			          + '  <div class="post_kcmcimg"> '
			          + '	<a href="'+ data.titlelink +'" title="'+ data.title +'" target="_blank"><img src="'+data.imglink+'" width="84" height="62" /></a></div> '
			          + '  <div class="post_kcmctext"> '
			          + '    <div class="post_kcmctextbt"> '
			          + '		<a href="'+ data.titlelink +'" title="'+ data.title +'" target="_blank">'+data.title+'</a></div> '
			          + '    <div class="post_kcmctextnr">浏览数:'+data.readnum+' 平均分'+data.averagescore+'</div> '
			          + '  </div> '
			          + '  <div class="clearafter"></div> '
			          + '</div> '
			          + '<div class="post_kcmctextzw">课程介绍：'+suitStr(data.context, 70)+'</div> '     
					  + '';
			}
			panObj.append(s).hide().children('img').remove();
			panObj.slideDown(200, function(){
				jQuery(panel[3], anchor).on('mouseenter', 'li', courseHandler);
			});
		});
	}else{
		panObj.slideDown(200, function(){
			jQuery(panel[3], anchor).on('mouseenter', 'li', courseHandler);
		});
	}
	
}

function courseHandler(){
	var li = jQuery(this);
	if(li.hasClass('selected')) return false;
	
	if(_queue) clearTimeout(_queue);
	_queue = setTimeout(function(){showCourseDetail(li);}, 1000);
}

function setStation(stationid, sname){
	
	jQuery.getJSON("api.php?mod=plugin&app=coursemap:api&param="+encodeURIComponent("action=setstation&stationid="+stationid+"&uid="+UID+"&code="+jQuery.md5('setstation_'+UID)), function(data){
		if(data && data.success == 'Y'){
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>岗位设置成功</span>", 1500);
			mystation = stationid;
			
			var ex = jQuery(panel[2]+' .post_btn_qxgz', anchor).parent('li');
			if(ex.length>0) changeStationMark(false, ex);
			changeStationMark(true);
			
			var caller_info_obj = jQuery('#gwCourse .post_right_title_info span');
			if(caller_info_obj.length>0) caller_info_obj.html('“'+ sname +'”');
		}else{
			showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>岗位设置失败 请稍后再次尝试</span>", 1500);
		}
		
	});
}

function changeStationMark(flag, li){
	var li = li || jQuery(panel[2]+' .'+mystation, anchor).parent('li');
	if(li.length>0){
		if(flag){
			var s = '<span class="post_btn_qxgz" title="已关注">已关注</span>';
			jQuery('.post_btn_jgz', li).remove();
			li.append(s);
		}else{
			var s = '<span class="post_btn_jgz" title="关注">关注</span>';
			jQuery('.post_btn_qxgz', li).remove();
			li.append(s);
		}
	}
}

function searchStationCourse(){
	var pid = seobjHide.val();
	if(!pid || isNaN(pid)) return;
	
	if(_seajax) _seajax.abort();
	_seajax = jQuery.getJSON("api.php?mod=plugin&app=coursemap:api&param="+encodeURIComponent('action=searchstation&pid='+pid), function(data){
		if(data && data.error == null){
			
			if (data.root){
				showStation(data.root, panel[0], data.use.one);
			}
			if (data.child){
				showStation(data.child, panel[1], data.use.two);
			}
			if (data.station){
				showStation(data.station, panel[2], data.use.three);
			}
			if (data.course){
				showCourse(data.course, panel[3]);
			}
		}
		
	});
	
}


/*
** Search autoComplete
*/
var seobj = jQuery('#txtStationName', anchor);
var seobjHide = jQuery('#txtStationId', anchor);
jQuery(seobj).on("focus", stationSearchACRegisterFun);
jQuery(seobj).on("blur", stationSearchACRegisterFun);

function stationSearchACRegisterFun(event) {
	var promp = '岗位名称';
	stationSearchAutoComplete(event, this, jQuery('.acwrapper', anchor), seobjHide, promp, searchStationCourse);
}

function stationSearchAutoComplete(event, srcObj, acObj, hideObj, oPromp, callback) {
	var eventType = event.type;
	var t = jQuery(srcObj);
	var thd = jQuery(hideObj);
	var tParent = t.parent();
	var promptSearchTP = t.data("promptSearchTP");
	var detectValTP = t.data("detectValTP");		//输入检测计时器
	var promptSearchAjax = null;
	var oPromp = oPromp || '';

	var acCleanUp = function() {
		var wrap = acObj;
		try {
			wrap.hide(50);
			wrap.empty();
		} catch(e) {};
			
		clearTimeout(promptSearchTP);
		t.unbind("keydown.popup");
		
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
				jQuery('body').unbind("click.popup",handler);
			}
		};
		jQuery('body').bind("click.popup",handler);
		clearInterval(detectValTP);
		jQuery(srcObj).unbind("keyup.popup");
		if(oPromp && t.val()=='') t.val(oPromp);

	} else if(eventType == "focus") {
		if(oPromp && t.val()==oPromp) t.val('');
		
		var preventDefault = function(event) {
			if(event.keyCode == 13 || event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 38 || event.keyCode == 40) {
				event.preventDefault();
			}
		};

		var completeRest = function(li) {
			var dt = li.data('data');
			t.val(dt.name || '');
			thd.val(dt.id || '');
			//t.data("lastQueryStr", dt.name);
			oldVal = dt.name;
			acCleanUp();
			if (typeof callback == 'function'){ callback(); }
		};

		var acPrompt = function(str) {
			if(str == null || str == "" || str == " ") {
				acCleanUp();
				return;
			}

			clearTimeout(promptSearchTP);
			promptSearchTP = setTimeout(function() {
				
				var searchURL = "api.php?mod=plugin"+"&"+"app=coursemap:api"+"&"+"param="+ encodeURIComponent('action=search&key='+str);
			
				promptSearchAjax = jQuery.ajax({
					url: searchURL,
					dataType: 'json',
					success: function(data){
				  		if(data){
				  			var atList;
			  				if(atList == undefined) {
			  					atList = jQuery("<ul></ul>");
			  				}

			  				jQuery.each(data, function(key, d) {
			  					
			  					var li = jQuery("<li class='item cl'></li>").data("data", {'id':d.id, 'name':d.name});
			  					var sname = d.name.replace(str, "<span>" + str + "</span>");
			  					li.append("<a href='javascript:;'>" + sname + "</a>");
			  					atList.append(li);
			  				});
				  			

				  			if(atList != undefined) {
				  				{
				  					acObj.css({'visibility': 'hidden','display': 'block'});	//取对象的位置
				  					var  acwOffsetParent = acObj.offsetParent()
				  						,tOffsetParent = t.offsetParent()
				  						,tPosition = {
				  							 left: tOffsetParent.offset().left - acwOffsetParent.offset().left
		  									,top: tOffsetParent.offset().top - acwOffsetParent.offset().top
		  								};
				  					acObj.css({'visibility': 'visible','display': 'none'});	//对象设为原来的值
				  					
				  					acObj.css("left",  tPosition.left + t.position().left - 8 + 30);
				  					acObj.css("top",  tPosition.top + t.position().top + 18 + 5);
				  				}
				  				
				  				jQuery("li.item:first", atList).addClass("on");

				  				jQuery("li.item", atList).on("mouseenter.popup", function() {
				  					jQuery("li", atList).removeClass("on");
				  					jQuery(this).addClass("on");
				  				});
				  				jQuery("li.item", atList).on("mouseleave.popup", function() {
				  					jQuery(this).removeClass("on");
				  				});
				  				jQuery("li.item", atList).on("mousedown.popup", function() {
				  					
				  				});
				  				jQuery("li.item", atList).on("click.popup", function() {
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
				  				t.on("keydown.popup", preventDefault);
				  			}
				  		}
				  }
				});
			}, 100);
			
		};

		jQuery(srcObj).on('keypress.popup', function(event){	//文本框禁止输入回车
			if (event.keyCode == '13') {
				event.preventDefault();
			}
		});

		jQuery(srcObj).on("keyup.popup", function(event){
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
		
		var oldVal = t.val();	//记录前一次文本框内的值

		clearInterval(detectValTP);
		detectValTP = setInterval(function(){
			
			var val = jQuery.trim(t.val());

			if (val != oldVal && val != oPromp ) {
				acCleanUp();
				oldVal = val;
				acPrompt(val);
			}
		}, 10);
		t.data("detectValTP", detectValTP);
	}

}

/* ------------------- autoComplete end ------------------- */
