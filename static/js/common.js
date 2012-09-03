/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: common.js 11553 2010-06-08 03:15:30Z zhangguosheng $
*/

var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','maxthon':'','mozilla':'','webkit':''});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

var CSSLOADED = [];
var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':200,'menu':300,'dialog':400,'prompt':999};
JSMENU['float'] = '';
var AJAX = [];
AJAX['url'] = [];
AJAX['stack'] = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
var CURRENTSTYPE = null;
var discuz_uid = isUndefined(discuz_uid) ? 0 : discuz_uid;
var creditnotice = isUndefined(creditnotice) ? '' : creditnotice;
var cookiedomain = isUndefined(cookiedomain) ? '' : cookiedomain;
var cookiepath = isUndefined(cookiepath) ? '' : cookiepath;

var DISCUZCODE = [];
DISCUZCODE['num'] = '-1';
DISCUZCODE['html'] = [];

var USERABOUT_BOX = true;

/* Added by Izzln */
var fps = 30;

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function trim(str) {
	return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function mb_strlen(str) {
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = !dot ? '...' : '';
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}

if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
			var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

function $(id) {
	return !id ? null : document.getElementById(id);
}

function display(id) {
	$(id).style.display = $(id).style.display == '' ? 'none' : '';
}
function reldisplay(id){
    if($(id)){$(id).style.display='';}
}

function relhidden(id){
    if($(id)){$(id).style.display='none';}
}

function checkall(form, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
			if(e.checked) {
				count++;
			}
		
		}
	}
	return count;
}


function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	expires.setTime(expires.getTime() + seconds * 1000);
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (seconds ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	var cookiepre = '';
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

function thumbImg(obj, method) {
	if(!obj) {
		return;
	}
	obj.onload = null;
	file = obj.src;
	zw = obj.offsetWidth;
	zh = obj.offsetHeight;
	if(zw < 2) {
		if(!obj.id) {
			obj.id = 'img_' + Math.random();
		}
		setTimeout("thumbImg($('" + obj.id + "'), " + method + ")", 100);
		return;
	}
	zr = zw / zh;
	method = !method ? 0 : 1;
	if(method) {
		fixw = obj.getAttribute('_width');
		fixh = obj.getAttribute('_height');
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
		}
		if(zh > fixh) {
			zh = fixh;
			zw = zh * zr;
		}
	} else {
		var imagemaxwidth = isUndefined(imagemaxwidth) ? '600' : imagemaxwidth;
		var widthary = imagemaxwidth.split('%');
		if(widthary.length > 1) {
			fixw = $('wrap').clientWidth - 200;
			if(widthary[0]) {
				fixw = fixw * widthary[0] / 100;
			} else if(widthary[1]) {
				fixw = fixw < widthary[1] ? fixw : widthary[1];
			}
		} else {
			fixw = widthary[0];
		}
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
			obj.style.cursor = 'pointer';
			if(!obj.onclick) {
				obj.onclick = function() {
					zoom(obj, obj.src);
				};
			}
		}
	}
	obj.width = zw;
	obj.height = zh;
}

var zoomclick = 0, zoomstatus = 1;
function zoom(obj, zimg) {
	zimg = !zimg ? obj.src : zimg;
	if(!zoomstatus) {
		window.open(zimg, '', '');
		return;
	}
	if(!obj.id) obj.id = 'img_' + Math.random();
	var menuid = obj.id + '_zmenu';
	var menu = $(menuid);
	var imgid = menuid + '_img';
	var zoomid = menuid + '_zimg';
	var maxh = (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight) - 70;

	if(!menu) {
		menu = document.createElement('div');
		menu.id = menuid;
		var objpos = fetchOffset(obj);
		menu.innerHTML = '<div onclick="$(\'append_parent\').removeChild($(\'' + obj.id + '_zmenu\'))" style="z-index:600;filter:alpha(opacity=70);opacity:0.7;background:#FFF;position:absolute;width:' + obj.clientWidth + 'px;height:' + obj.clientHeight + 'px;left:' + objpos['left'] + 'px;top:' + objpos['top'] + 'px"><table width="100%" height="100%"><tr><td valign="middle" align="center"><img src="' + IMGDIR + '/loading.gif" /></td></tr></table></div>' +
			'<div style="position:absolute;top:-100000px;display:none"><img id="' + imgid + '" src="' + zimg + '"></div>';
		$('append_parent').appendChild(menu);
		$(imgid).onload = function() {
			$(imgid).parentNode.style.display = '';
			var imgw = $(imgid).width;
			var imgh = $(imgid).height;
			var r = imgw / imgh;
			var w = document.body.clientWidth * 0.95;
			w = imgw > w ? w : imgw;
			var h = w / r;
			if(h > maxh) {
				h = maxh;
				w = h * r;
			}
			$('append_parent').removeChild(menu);
			menu = document.createElement('div');
			menu.id = menuid;
			menu.style.overflow = 'visible';
			menu.style.width = (w < 300 ? 300 : w) + 20 + 'px';
			menu.style.height = h + 50 + 'px';
			menu.innerHTML = '<div class="zoominner"><p id="' + menuid + '_ctrl"><span class="y"><a href="' + zimg + '" class="imglink" target="_blank" title="在新窗口打开">在新窗口打开</a><a href="javascipt:;" id="' + menuid + '_adjust" class="imgadjust" title="实际大小">实际大小</a><a href="javascript:;" onclick="hideMenu()" class="imgclose" title="关闭">关闭</a></span>鼠标滚轮缩放图片</p><div align="center" onmousedown="zoomclick=1" onmousemove="zoomclick=2" onmouseup="if(zoomclick==1) hideMenu()"><img id="' + zoomid + '" src="' + zimg + '" width="' + w + '" height="' + h + '" w="' + imgw + '" h="' + imgh + '"></div></div>';
			$('append_parent').appendChild(menu);
			$(menuid + '_adjust').onclick = function(e) {adjust(e, 1)};
			if(BROWSER.ie){
				menu.onmousewheel = adjust;
			} else {
				menu.addEventListener('DOMMouseScroll', adjust, false);
			}
			showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':1,'drag':menuid,'maxh':maxh+70});
		};
	} else {
		showMenu({'menuid':menuid,'duration':3,'pos':'00','cover':1,'drag':menuid,'maxh':menu.clientHeight});
	}
	if(BROWSER.ie) doane(event);
	var adjust = function(e, a) {
		var imgw = $(zoomid).getAttribute('w');
		var imgh = $(zoomid).getAttribute('h');
		var imgwstep = imgw / 10;
		var imghstep = imgh / 10;
		if(!a) {
			if(!e) e = window.event;
			if(e.altKey || e.shiftKey || e.ctrlKey) return;
			if(e.wheelDelta <= 0 || e.detail > 0) {
				if($(zoomid).width - imgwstep <= 200 || $(zoomid).height - imghstep <= 200) {
					doane(e);return;
				}
				$(zoomid).width -= imgwstep;
				$(zoomid).height -= imghstep;
			} else {
				if($(zoomid).width + imgwstep >= imgw) {
					doane(e);return;
				}
				$(zoomid).width += imgwstep;
				$(zoomid).height += imghstep;
			}
		} else {
			$(zoomid).width = imgw;
			$(zoomid).height = imgh;
		}
		menu.style.width = (parseInt($(zoomid).width < 300 ? 300 : parseInt($(zoomid).width)) + 20) + 'px';
		menu.style.height = (parseInt($(zoomid).height) + 50) + 'px';
		setMenuPosition('', menuid, '00');
		doane(e);
	};
}

function loadcss(cssname) {
	if(!CSSLOADED[cssname]) {
		css = document.createElement('link');
		css.type = 'text/css';
		css.rel = 'stylesheet';
		css.href = 'data/cache/style_' + STYLEID + '_' + cssname + '.css?' + VERHASH;
		var headNode = document.getElementsByTagName("head")[0];
		headNode.appendChild(css);
		CSSLOADED[cssname] = 1;
	}
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = $(ctrlid);
	var menuObj = $(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 609 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && $(drag) ? $(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	zindex = cover ? zindex + 200 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if(ctrlObj) {
		if(!ctrlObj.initialized) {
			ctrlObj.initialized = true;
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	var dragMenu = function(menuObj, e, op) {
		e = e ? e : window.event;
		if(op == 1) {
			if(in_array(BROWSER.ie ? e.srcElement.tagName : e.target.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
				return;
			}
			JSMENU['drag'] = [e.clientX, e.clientY];
			JSMENU['drag'][2] = parseInt(menuObj.style.left);
			JSMENU['drag'][3] = parseInt(menuObj.style.top);
			document.onmousemove = function(e) {try{dragMenu(menuObj, e, 2);}catch(err){}};
			document.onmouseup = function(e) {try{dragMenu(menuObj, e, 3);}catch(err){}};
			doane(e);
		}else if(op == 2 && JSMENU['drag'][0]) {
			var menudragnow = [e.clientX, e.clientY];
			menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
			menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
			doane(e);
		}else if(op == 3) {
			JSMENU['drag'] = [];
			document.onmousemove = null;
			document.onmouseup = null;
		}
	};

	if(!menuObj.initialized) {
		menuObj.initialized = true;
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			if(!e || BROWSER.ie) {
				window.event.cancelBubble = true;
				return window.event;
			} else {
				e.stopPropagation();
				return e;
			}
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = document.body.offsetHeight + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=70)';
			coverObj.style.opacity = 0.7;
			$('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = document.body.offsetHeight + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}
	menuObj.style.display = '';
	if(cover) $(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O == 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 10;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 50);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	setMenuPosition(showid, menuid, pos);
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
}

function setMenuPosition(showid, menuid, pos) {
	function checkmenuobj(menuObj) {
		while((menuObj = menuObj.offsetParent) != null) {
			if(menuObj.style.position == 'absolute') {
				return 2;
			}
		}
		return 1;
	}
	var showObj = $(showid);
	var menuObj = menuid ? $(menuid) : $(showid + '_menu');
	if(isUndefined(pos)) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0, ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0, bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = fetchOffset(showObj, BROWSER.ie && BROWSER.ie < 7 ? checkmenuobj(menuObj) : 0);
		sx = sxy['left'];
		sy = sxy['top'];
		sw = showObj.offsetWidth;
		sh = showObj.offsetHeight;
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:
			bpl = sx;
			bpt = sy;
			break;
		case 2:
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			break;
		case 1:
			ml = bpl - mw;
			mt = bpt - mh;
			break;
		case 2:
			ml = bpl;
			mt = bpt - mh;
			break;
		case 3:
			ml = bpl;
			mt = bpt;
			break;
		case 4:
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(in_array(direction, [1, 4]) && ml < 0) {
		ml = bpl;
		if(in_array(basePoint, [1, 4])) ml += sw;
	} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
		ml = bpl - mw;
		if(in_array(basePoint, [2, 3])) ml -= sw;
	}
	if(in_array(direction, [1, 2]) && mt < 0) {
		mt = bpt;
		if(in_array(basePoint, [1, 2])) mt += sh;
	} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
		mt = bpt - mh;
		if(in_array(basePoint, [3, 4])) mt -= sh;
	}
	if(pos == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(pos == '438') {
		ml -= (mcw-sw)/2;			//center the menu;
	}
	if(pos == '439') {
		ml -= (mcw-sw)/2;
		ml -= 3;					//center and specially for hd navigation menu
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	if(ml) menuObj.style.left = ml + 'px';
	if(mt) menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function doane(event) {
	e = event ? event : window.event;
	if(!e) e = getEvent();
	if(e && BROWSER.ie) {
		e.returnValue = false;
		e.cancelBubble = true;
	} else if(e) {
		e.stopPropagation();
		e.preventDefault();
	}
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function _detachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.removeEventListener) {
		obj.removeEventListener(evt, func, false);
	} else if(eventobj.detachEvent) {
		obj.detachEvent('on' + evt, func);
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			if(mode == 2 && obj.style.position == 'absolute') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}

function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = $(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(menuObj.cache) {
				menuObj.style.display = 'none';
				if(menuObj.cover) $(attr + '_cover').style.display = 'none';
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) $(attr + '_cover').parentNode.removeChild($(attr + '_cover'));
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 10;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 50);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}

function showPrompt(ctrlid, evt, msg, timeout) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	var duration = timeout ? 0 : 3;
	if($(menuid)) {
		$(menuid).parentNode.removeChild($(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = ctrlid ? 'prmm up' : 'ntcwin';
	div.style.display = 'none';
	$('append_parent').appendChild(div);
	if(ctrlid) {
		msg = '<div id="' + ctrlid + '_prompt" class="prmc"><ul><li>' + msg + '</li></ul></div>';
	} else {
		msg = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner">' + msg +
			'</td><td class="pc_r">&nbsp;</td></tr></table>';
	}
	div.innerHTML = msg;
	if(ctrlid) {
		if($(ctrlid).evt !== false) {
			var prompting = function() {
				showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210'});
			};
			if(evt == 'click') {
				$(ctrlid).onclick = prompting;
			} else {
				$(ctrlid).onmouseover = prompting;
			}
		}
		showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210','duration':duration,'timeout':timeout,'fade':1,'zindex':JSMENU['zIndex']['prompt']});
		$(ctrlid).unselectable = false;
	}else {
		showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'fade':1,'zindex':JSMENU['zIndex']['prompt']});
	}
}

function showCreditPrompt() {
	var notice = getcookie('creditnotice').split('D');
	var basev = getcookie('creditbase').split('D');
	var creditrule = decodeURI(getcookie('creditrule', 1)).replace(String.fromCharCode(9), ' ');
	if(!discuz_uid || notice.length < 2) {
		setcookie('creditnotice', '', -2592000);
		setcookie('creditrule', '', -2592000);
		return;
	}
	var creditnames = creditnotice.split(',');
	var creditinfo = [];
	var e;
	for(var i in creditnames) {
		e = creditnames[i].split('|');
		creditinfo[e[0]] = [e[1], e[2]];
	}
	creditShow(creditinfo, notice, basev, 0, 1, creditrule);
	showEmpiricalValuePrompt();
}

/*
 * @author fumz
 * @since 2010-11-5 14:37:29
 */
function showEmpiricalValuePrompt(){
	var notice = getcookie('empiricalValueNotice').split('D');
	var basev = getcookie('empiricalValueBase').split('D');
	var creditrule = decodeURI(getcookie('empiricalValueRule', 1)).replace(String.fromCharCode(9), ' ');
	if(!discuz_uid || notice.length < 2) {
		setcookie('empiricalValueNotice', '', -2592000);
		setcookie('empiricalValueRule', '', -2592000);
		return;
	}
	var creditnames = creditnotice.split(',');
	var creditinfo = [];
	var e;
	for(var i in creditnames) {
		e = creditnames[i].split('|');
		creditinfo[e[0]] = [e[1], e[2]];
	}
	empiricalValueShow(creditinfo, notice, basev, 0, 1, creditrule);
}
/*
 * @author fumz
 * @since 2010-11-5 15:00:34 
 * 
 */
function empiricalValueShow(creditinfo, notice, basev, bk, first, creditrule) {
	var s = '', check = 0;
	for(i = 1; i <= 8; i++) {
		v = parseInt(Math.abs(parseInt(notice[i])) / 5) + 1;
		if(notice[i] !== '0' && creditinfo[i]) {
			s += '<span>' + creditinfo[i][0] + '<u>' + basev[i] + '</u>' + (notice[i] != 0 ? (notice[i] > 0 ? '<em>+' : '<em class="desc">') + notice[i] + '</em>' : '') + creditinfo[i][1] + '</span>';
		}
		if(notice[i] > 0) {
			notice[i] = parseInt(notice[i]) - v;
			basev[i] = parseInt(basev[i]) + v;
		} else if(notice[i] < 0) {
			notice[i] = parseInt(notice[i]) + v;
			basev[i] = parseInt(basev[i]) - v;
		}
	}
	for(i = 1; i <= 8; i++) {
		if(notice[i] != 0) {
			check = 1;
		}
	}
	if(!s || first) {
		setcookie('empiricalValueNotice', '', -2592000);
		setcookie('empiricalValueBase', '', -2592000);
		setcookie('empiricalValueRule', '', -2592000);
		if(!s) {
			return;
		}
	}
	if(!$('creditpromptdiv')) {
		showPrompt(null, null, '<div id="creditpromptdiv">' + (creditrule ? '<i>' + creditrule + '</i> ' : '') + s + '</div>', 0);
	} else {
		$('creditpromptdiv').innerHTML = s;
	}
	if(!bk) {
		bk = check ? 0 : 1;
		setTimeout(function () {creditShow(creditinfo, notice, basev, bk, 0, creditrule);}, first ? 2500 : 100);
	} else {
		setTimeout(function () {hideMenu(1, 'prompt');$('append_parent').removeChild($('ntcwin'));}, 1000);
	}
}

function creditShow(creditinfo, notice, basev, bk, first, creditrule) {
	var s = '', check = 0;
	for(i = 1; i <= 8; i++) {
		v = parseInt(Math.abs(parseInt(notice[i])) / 5) + 1;
		if(notice[i] !== '0' && creditinfo[i]) {
			s += '<span>' + creditinfo[i][0] + '<u>' + basev[i] + '</u>' + (notice[i] != 0 ? (notice[i] > 0 ? '<em>+' : '<em class="desc">') + notice[i] + '</em>' : '') + creditinfo[i][1] + '</span>';
		}
		if(notice[i] > 0) {
			notice[i] = parseInt(notice[i]) - v;
			basev[i] = parseInt(basev[i]) + v;
		} else if(notice[i] < 0) {
			notice[i] = parseInt(notice[i]) + v;
			basev[i] = parseInt(basev[i]) - v;
		}
	}
	for(i = 1; i <= 8; i++) {
		if(notice[i] != 0) {
			check = 1;
		}
	}
	if(!s || first) {
		setcookie('creditnotice', '', -2592000);
		setcookie('creditbase', '', -2592000);
		setcookie('creditrule', '', -2592000);
		if(!s) {
			return;
		}
	}
	if(!$('creditpromptdiv')) {
		showPrompt(null, null, '<div id="creditpromptdiv">' + (creditrule ? '<i>' + creditrule + '</i> ' : '') + s + '</div>', 0);
	} else {
		$('creditpromptdiv').innerHTML = s;
	}
	if(!bk) {
		bk = check ? 0 : 1;
		setTimeout(function () {creditShow(creditinfo, notice, basev, bk, 0, creditrule);}, first ? 2500 : 100);
	} else {
		setTimeout(function () {hideMenu(1, 'prompt');$('append_parent').removeChild($('ntcwin'));}, 1000);
	}
}

/* Growl notification
   Added by Izzln
*/
function showGrowl(msg, timeout) {
	var menuid = 'ntcwin';
	var duration = timeout ? 0 : 3;
	if($(menuid)) {
		$(menuid).parentNode.removeChild($(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = 'ntcwin';
	div.style.display = 'none';
	$('append_parent').appendChild(div);

	msg = '<div class="popGrowl">' + msg + '</div>';
	div.innerHTML = msg;

	showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'fade':1,'zindex':JSMENU['zIndex']['prompt']});
}

/* Quick Input 
   Added by Izzln
*/
function showInput(ctrlid, msg, callback) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	if($(menuid)) {
		$(menuid).parentNode.removeChild($(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = 'sInput';
	div.style.display = 'none';
	$('append_parent').appendChild(div);
	msg = '<table cellpadding="0" cellspacing="0" class="fwin" ><tr><td class="t_l2"></td><td class="t_c2"></td><td class="t_r2"></td></tr><tr><td class="m_l2"></td><td class="m_c2">' + msg + '</td><td class="m_r2"></td></tr><tr><td class="b_l2"></td><td class="b_c2"></td><td class="b_r2"></td></tr></table>';
	div.innerHTML = msg;
	
	showMenu({'mtype':'prompt','ctrlid':ctrlid,'menuid':menuid,'duration':3,'pos':'438'});
	$(ctrlid).unselectable = false;
	if(typeof callback == 'function') callback();
	else eval(callback);
}

function showDialog(msg, mode, t, func, cover, funccancel) {
	cover = isUndefined(cover) ? (mode == 'info' ? 0 : 1) : cover;
	mode = in_array(mode, ['confirm', 'notice', 'info']) ? mode : 'alert';
	var menuid = 'fwin_dialog';
	var menuObj = $(menuid);

	if(menuObj) hideMenu('fwin_dialog', 'dialog');
	menuObj = document.createElement('div');
	menuObj.style.display = 'none';
	menuObj.className = 'fwinmask';
	menuObj.id = menuid;
	$('append_parent').appendChild(menuObj);
	var s = '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c"><h3 class="flb"><em>';
	s += t ? t : '提示信息';
	s += '</em><span><a href="javascript:;" id="fwin_dialog_close" class="flbc" onclick="hideMenu(\'' + menuid + '\', \'dialog\')" title="关闭">关闭</a></span></h3>';
	if(mode == 'info') {
		s += msg ? msg : '';
	} else {
		s += '<div class="c' + (mode == 'info' ? '' : ' altw') + '"><div class="' + (mode == 'alert' ? 'alert_error' : 'alert_info') + '"><p>' + msg + '</p></div></div>';
		s += '<p class="o pns"><button id="fwin_dialog_submit" value="true" class="pn pnc"><strong>确定</strong></button>';
		s += mode == 'confirm' ? '<button id="fwin_dialog_cancel" value="true" class="pn" onclick="hideMenu(\'' + menuid + '\', \'dialog\')"><strong>取消</strong></button>' : '';
		s += '</p>';
	}
	s += '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
	menuObj.innerHTML = s;
	if($('fwin_dialog_submit')) $('fwin_dialog_submit').onclick = function() {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if($('fwin_dialog_cancel')) {
		$('fwin_dialog_cancel').onclick = function() {
			if(typeof funccancel == 'function') funccancel();
			else eval(funccancel);
			hideMenu(menuid, 'dialog');
		};
		$('fwin_dialog_close').onclick = $('fwin_dialog_cancel').onclick;
	}
	showMenu({'mtype':'dialog','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'cache':0,'cover':cover});
}

function showWindow(k, url, mode, cache, menuv) {
	//判断专家cookie
	if(url.indexOf('mod=querystation')==-1&&getcookie('expert_Is') && (url.indexOf('ac=favorite') != -1 || url.indexOf('ac=share') != -1 )) {
		showDialog("非常抱歉，由于您为外部专家，暂无权访问该内容");
		doane();
		return;
	}
	
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = $(menuid);
	var drag = null;
	var loadingst = null;

	if(disallowfloat && disallowfloat.indexOf(k) != -1) {
		if(BROWSER.ie) url += (url.indexOf('?') != -1 ?  '&' : '?') + 'referer=' + escape(location.href);
		location.href = url;
		return;
	}

	var fetchContent = function() {
		if(mode == 'get') {
			menuObj.url = url;
			url += (url.search(/\?/) > 0 ? '&' : '?') + 'infloat=yes&handlekey=' + k;
			url += cache == -1 ? '&t='+(+ new Date()) : '';
			var recall = function() {initMenu();show();}
			if(BROWSER.ie && BROWSER.ie == 6) {recall = function() {setTimeout(function(){initMenu();show();},100)}};//IE6下会崩溃
			ajaxget(url, 'fwin_content_' + k, null, '', '', recall);
		} else if(mode == 'post') {
			menuObj.act = $(url).action;
			ajaxpost(url, 'fwin_content_' + k, '', '', '', function() {initMenu();show();});
		}
		loadingst = setTimeout(function() {showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> 请稍候...')}, 500);
	};
	var initMenu = function() {
		clearTimeout(loadingst);
		var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', k);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				drag = objs[i].id;
				fctrlidinit = true;
			}
		}
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['win'],'drag':typeof drag == null ? '' : drag,'cache':cache};
		for(k in menuv) {
			v[k] = menuv[k];
		}
		showMenu(v);
	};

	if(!menuObj) {
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = 'fwinmask';
		menuObj.style.display = 'none';
		$('append_parent').appendChild(menuObj);
		menuObj.innerHTML = '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="t_r"></td></tr><tr><td class="m_l" ondblclick="hideWindow(\'' + k + '\')">&nbsp;&nbsp;</td><td class="m_c" id="fwin_content_' + k + '">'
			+ '</td><td class="m_r" ondblclick="hideWindow(\'' + k + '\')"></td></tr><tr><td class="b_l"></td><td class="b_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="b_r"></td></tr></table>';
		if (mode == 'html') {
			$('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		} else {
			fetchContent();
		}
	} else if((mode == 'get' && url != menuObj.url) || (mode == 'post' && $(url).action != menuObj.act)) {
		fetchContent();
	} else {
		show();
	}
	doane();
}

function showWinCover(k, url, mode, cache, menuv, custom) {
	//判断专家cookie
	if(url.indexOf('mod=querystation')==-1&&getcookie('expert_Is') && (url.indexOf('ac=favorite') != -1 || url.indexOf('ac=share') != -1 )) {
		showDialog("非常抱歉，由于您为外部专家，暂无权访问该内容");
		doane();
		return;
	}
	
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = $(menuid);
	//var drag = null;
	var loadingst = null;

	if(disallowfloat && disallowfloat.indexOf(k) != -1) {
		if(BROWSER.ie) url += (url.indexOf('?') != -1 ?  '&' : '?') + 'referer=' + escape(location.href);
		location.href = url;
		return;
	}

	var fetchContent = function() {
		if(mode == 'get') {
			menuObj.url = url;
			url += (url.search(/\?/) > 0 ? '&' : '?') + 'infloat=yes&handlekey=' + k;
			url += cache == -1 ? '&t='+(+ new Date()) : '';
			var recall = function() {initMenu();showCover();}
			if(BROWSER.ie && BROWSER.ie == 6) {recall = function() {setTimeout(function(){initMenu();showCover();},100)}};//IE6下会崩溃
			ajaxget(url, 'fwin_content_' + k, null, '', '', recall);
		} else if(mode == 'post') {
			menuObj.act = $(url).action;
			ajaxpost(url, 'fwin_content_' + k, '', '', '', function() {initMenu();showCover();});
		}
		loadingst = setTimeout(function() {showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> 请稍候...')}, 500);
	};
	var initMenu = function() {
		clearTimeout(loadingst);
		var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', k);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				//drag = objs[i].id;
				fctrlidinit = true;
			}
		}
	};
	var showCover = function() {
		hideMenu('fwin_dialog', 'dialog');
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'cover':'1','cache':cache};
		for(k in menuv) {
			v[k] = menuv[k];
		}
		showMenu(v);
	};

	if(!menuObj) {
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = 'fwinmask';
		menuObj.style.display = 'none';
		$('append_parent').appendChild(menuObj);
		if(custom){
			menuObj.innerHTML = '<div id="fwin_content_' + k + '"></div>';
		}else{
			menuObj.innerHTML = '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="t_r"></td></tr><tr><td class="m_l" ondblclick="hideWindow(\'' + k + '\')">&nbsp;&nbsp;</td><td class="m_c" id="fwin_content_' + k + '">'
				+ '</td><td class="m_r" ondblclick="hideWindow(\'' + k + '\')"></td></tr><tr><td class="b_l"></td><td class="b_c" ondblclick="hideWindow(\'' + k + '\')"></td><td class="b_r"></td></tr></table>';
		}
		if (mode == 'html') {
			$('fwin_content_' + k).innerHTML = url;
			initMenu();
			showCover();
		} else {
			fetchContent();
		}
	} else if((mode == 'get' && url != menuObj.url) || (mode == 'post' && $(url).action != menuObj.act)) {
		fetchContent();
	} else {
		showCover();
	}
	doane();
}


function hideWindow(k, all) {
	all = isUndefined(all) ? 1 : all;
	hideMenu('fwin_' + k, 'win');
	if(all) {
		hideMenu();
	}
	hideMenu('', 'prompt');
}

function Ajax(recvType, waitId) {

	for(var stackId = 0; stackId < AJAX['stack'].length && AJAX['stack'][stackId] != 0; stackId++);
	AJAX['stack'][stackId] = 1;

	var aj = new Object();

	aj.loading = '请稍候...';
	aj.recvType = recvType ? recvType : 'XML';
	aj.waitId = waitId ? $(waitId) : null;

	aj.resultHandle = null;
	aj.sendString = '';
	aj.targetUrl = '';
	aj.stackId = 0;
	aj.stackId = stackId;

	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};

	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};

	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : $(waitid);
	};

	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};

	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + aj.loading + '</span>';
		}
	};

	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			for(k in AJAX['url']) {
				if(AJAX['url'][k] == aj.targetUrl) {
					AJAX['url'][k] = null;
				}
			}
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				//Reading XML bug of Microsoft IE, solved by sunk
				if(aj.XMLHttpRequest.responseXML.lastChild==null) {
					var xhr = aj.XMLHttpRequest;
					var doc = aj.XMLHttpRequest.responseXML;
					doc = new ActiveXObject("microsoft.xmldom");
					doc.loadXML(xhr.responseText);
					aj.resultHandle(doc.lastChild.firstChild.nodeValue, aj);
				} else {
					if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
						aj.resultHandle('<a href="' + aj.targetUrl + '" target="_blank" style="color:red">内部错误，无法显示此内容</a>' , aj);
					} else {
						aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
					}
				}
			}
			AJAX['stack'][aj.stackId] = 0;
		}
	};

	aj.get = function(targetUrl, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		if(in_array(targetUrl, AJAX['url'])) {
			return false;
		} else {
			AJAX['url'].push(targetUrl);
		}
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		var delay = attackevasive & 1 ? (aj.stackId + 1) * 1001 : 100;
		if(window.XMLHttpRequest) {
			setTimeout(function(){
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);}, delay);
		} else {
			setTimeout(function(){
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();}, delay);
		}
	};
	aj.post = function(targetUrl, sendString, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		if(in_array(targetUrl, AJAX['url'])) {
			return false;
		} else {
			AJAX['url'].push(targetUrl);
		}
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	return aj;
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /.*\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	var evalscripts = [];
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $(id)) {
		$(id).parentNode.removeChild($(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
		} else if(text){
			scriptNode.text = text;
		}
		$('append_parent').appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

function ajaxupdateevents(obj, tagName) {
	tagName = tagName ? tagName : 'A';
	var objs = obj.getElementsByTagName(tagName);
	for(k in objs) {
		var o = objs[k];
		ajaxupdateevent(o);
	}
}

function ajaxupdateevent(o) {
	if(typeof o == 'object' && o.getAttribute) {
		if(o.getAttribute('ajaxtarget')) {
			if(!o.id) o.id = Math.random();
			var ajaxevent = o.getAttribute('ajaxevent') ? o.getAttribute('ajaxevent') : 'click';
			var ajaxurl = o.getAttribute('ajaxurl') ? o.getAttribute('ajaxurl') : o.href;
			_attachEvent(o, ajaxevent, newfunction('ajaxget', ajaxurl, o.getAttribute('ajaxtarget'), o.getAttribute('ajaxwaitid'), o.getAttribute('ajaxloading'), o.getAttribute('ajaxdisplay')));
			if(o.getAttribute('ajaxfunc')) {
				o.getAttribute('ajaxfunc').match(/(\w+)\((.+?)\)/);
				_attachEvent(o, ajaxevent, newfunction(RegExp.$1, RegExp.$2));
			}
		}
	}
}

function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = typeof waitid == 'undefined' || waitid === null ? showid : waitid;
	var x = new Ajax();
	x.setLoading(loading);
	x.setWaitId(waitid);
	x.display = typeof display == 'undefined' || display == null ? '' : display;
	x.showId = $(showid);
	if(x.showId) x.showId.orgdisplay = typeof x.showId.orgdisplay === 'undefined' ? x.showId.style.display : x.showId.orgdisplay;

	if(url.substr(strlen(url) - 1) == '#') {
		url = url.substr(0, strlen(url) - 1);
		x.autogoto = 1;
	}

	var url = url + '&inajax=1&ajaxtarget=' + showid;
	if(BROWSER.ie && BROWSER.ie < 9) {
		url += '&rdm='+Math.random();
	}
	x.get(url, function(s, x) {
		var evaled = false;
		if(s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			if(x.showId) {
				x.showId.style.display = x.showId.orgdisplay;
				x.showId.style.display = x.display;
				x.showId.orgdisplay = x.showId.style.display;
				ajaxinnerhtml(x.showId, s);
				ajaxupdateevents(x.showId);
				if(x.autogoto) scroll(0, x.showId.offsetTop);
			}
		}

		ajaxerror = null;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
	});
}

function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = $(ajaxframeid);
	var formtarget = $(formid).target;
	
	var handleResult = function() {
		var s = '';
		var evaled = false;

		showloading('none');
		var doc_err = true;
		try {
			if(BROWSER.ie && BROWSER.ie < 9) {
				var xmldoc = $(ajaxframeid).contentWindow.document.XMLDocument;
				if (xmldoc){
					s = xmldoc.text;
					doc_err = false;
				}
			} else {
				var doc = $(ajaxframeid).contentWindow.document.documentElement.firstChild;
				if(doc && doc.nodeType == '4'){
					if(BROWSER.safari > 0) {
						s = doc.wholeText;
					} else {
						s = doc.nodeValue;
					}
					doc_err = false;
				}
			}
			if(doc_err === true){
				if(typeof showid == 'string' && showid != ''){
//					var errmsg = '';
//					if(BROWSER.ie && BROWSER.ie < 9) {
//						errmsg = $(ajaxframeid).contentWindow.document.body.innerHTML;
//					}else{
//						errmsg = $(ajaxframeid).contentWindow.document.documentElement.innerHTML;
//					}
//					console.log(errmsg);
//					errmsg = errmsg.toLowerCase();
//					var err = errmsg.substring(errmsg.indexOf('<b>')+3, errmsg.indexOf('</b>'))+errmsg.substring(errmsg.indexOf('</b>')+4, errmsg.indexOf('<br'));
					var err = '内部错误，无法显示此内容';
					var fn = showid.split('_');
					s = "<script>errorhandle_"+fn[1]+"('"+err+"');</script>";
				}else{
					s = "<script>alert('内部错误，无法显示此内容');</script>";
				}
			}
		} catch(e) {
            alert(e.message);
			s = '内部错误，无法显示此内容';
		}

		if(typeof s == 'string' && s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			$(showid).className = showidclass;
		}
		if(submitbtn) {
			submitbtn.disabled = false;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml($(showid), s);
		}
		ajaxerror = null;
		if($(formid)) $(formid).target = formtarget;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		$('append_parent').removeChild(ajaxframe);
	};
	if(!ajaxframe) {
		if (BROWSER.ie && BROWSER.ie < 9) {
			ajaxframe = document.createElement('<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '"></iframe>');
		} else {
			ajaxframe = document.createElement('iframe');
			ajaxframe.name = ajaxframeid;
			ajaxframe.id = ajaxframeid;
		}
		ajaxframe.style.display = 'none';
		ajaxframe.loading = 1;
		$('append_parent').appendChild(ajaxframe);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	showloading();
	$(formid).target = ajaxframeid;
	$(formid).action += '&inajax=1';
	if(BROWSER.ie && BROWSER.ie < 9) {
		$(formid).action += '&rdm='+Math.random();
	}
	$(formid).submit();
	if(submitbtn) {
		submitbtn.disabled = true;
	}
	doane();
	return false;
}

function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall) {
	var ctrlid = ctrlObj.id;
	if(!ctrlid) {
		ctrlid = ctrlObj.id = 'ajaxid_' + Math.random();
	}
	var menuid = ctrlid + '_menu';
	var menu = $(menuid);
	if(isUndefined(timeout)) timeout = 3000;
	if(isUndefined(cache)) cache = 1;
	if(isUndefined(pos)) pos = '43';
	if(isUndefined(duration)) duration = timeout > 0 ? 0 : 3;
	var func = function() {
		showMenu({'ctrlid':ctrlid,'duration':duration,'timeout':timeout,'pos':pos,'cache':cache,'layer':2});
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
	};

	if(menu) {
		if(menu.style.display == '') {
			hideMenu(menuid);
		} else {
			func();
		}
	} else {
		menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = 'p_pop';
		menu.innerHTML = '<div class="p_opt" id="' + menuid + '_content"></div>';
		$('append_parent').appendChild(menu);
		var url = (!isUndefined(ctrlObj.href) ? ctrlObj.href : ctrlObj.attributes['href'].value) + '&ajaxmenu=1';
		ajaxget(url, menuid + '_content', 'ajaxwaitid', '', '', func);
	}
	doane();
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function showloading(display, waiting) {
	var display = display ? display : 'block';
	var waiting = waiting ? waiting : '请稍候...';
	$('ajaxwaitid').innerHTML = waiting;
	$('ajaxwaitid').style.display = display;
}

function ajaxinnerhtml(showid, s) {
	if(showid.tagName != 'TBODY') {
		showid.innerHTML = s;
	} else {
		while(showid.firstChild) {
			showid.firstChild.parentNode.removeChild(showid.firstChild);
		}
		var div1 = document.createElement('DIV');
		div1.id = showid.id+'_div';
		div1.innerHTML = '<table><tbody id="'+showid.id+'_tbody">'+s+'</tbody></table>';
		$('append_parent').appendChild(div1);
		var trs = div1.getElementsByTagName('TR');
		var l = trs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(trs[0]);
		}
		var inputs = div1.getElementsByTagName('INPUT');
		var l = inputs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(inputs[0]);
		}
		div1.parentNode.removeChild(div1);
	}
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}

function AC_DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision) {
	var versionStr = -1;
	if(navigator.plugins != null && navigator.plugins.length > 0 && (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"])) {
		var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
		var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
		var descArray = flashDescription.split(" ");
		var tempArrayMajor = descArray[2].split(".");
		var versionMajor = tempArrayMajor[0];
		var versionMinor = tempArrayMajor[1];
		var versionRevision = descArray[3];
		if(versionRevision == "") {
			versionRevision = descArray[4];
		}
		if(versionRevision[0] == "d") {
			versionRevision = versionRevision.substring(1);
		} else if(versionRevision[0] == "r") {
			versionRevision = versionRevision.substring(1);
			if(versionRevision.indexOf("d") > 0) {
				versionRevision = versionRevision.substring(0, versionRevision.indexOf("d"));
			}
		}
		versionStr = versionMajor + "." + versionMinor + "." + versionRevision;
	} else if(BROWSER.ie && !BROWSER.opera) {
		try {
			var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
			versionStr = axo.GetVariable("$version");
		} catch (e) {}
	}
	if(versionStr == -1 ) {
		return false;
	} else if(versionStr != 0) {
		if(BROWSER.ie && !BROWSER.opera) {
			tempArray = versionStr.split(" ");
			tempString = tempArray[1];
			versionArray = tempString.split(",");
		} else {
			versionArray = versionStr.split(".");
		}
		var versionMajor = versionArray[0];
		var versionMinor = versionArray[1];
		var versionRevision = versionArray[2];
		return versionMajor > parseFloat(reqMajorVer) || (versionMajor == parseFloat(reqMajorVer)) && (versionMinor > parseFloat(reqMinorVer) || versionMinor == parseFloat(reqMinorVer) && versionRevision >= parseFloat(reqRevision));
	}
}

function AC_FL_RunContent() {
	var str = '';
	if(AC_DetectFlashVer(9,0,124)) {
		var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
		if(BROWSER.ie && !BROWSER.opera) {
			str += '<object ';
			for (var i in ret.objAttrs) {
				str += i + '="' + ret.objAttrs[i] + '" ';
			}
			str += '>';
			for (var i in ret.params) {
				str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
			}
			str += '</object>';
		} else {
			str += '<embed ';
			for (var i in ret.embedAttrs) {
				str += i + '="' + ret.embedAttrs[i] + '" ';
			}
			str += '></embed>';
		}
	} else {
		str = '此内容需要 Adobe Flash Player 9.0.124 或更高版本<br /><a href="http://www.adobe.com/go/getflashplayer/" target="_blank"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="下载 Flash Player" /></a>';
	}
	return str;
}

function simulateSelect(selectId, widthvalue) {
	var selectObj = $(selectId);
	if(!selectObj) return;
	var widthvalue = widthvalue ? widthvalue : 70;
	var defaultopt = selectObj.options[0] ? selectObj.options[0].innerHTML : '';
	var defaultv = '';
	var menuObj = document.createElement('div');
	var ul = document.createElement('ul');
	var handleKeyDown = function(e) {
		e = BROWSER.ie ? event : e;
		if(e.keyCode == 40 || e.keyCode == 38) doane(e);
	};
	var selectwidth = (selectObj.getAttribute('width', i) ? selectObj.getAttribute('width', i) : widthvalue) + 'px';

	for(var i = 0; i < selectObj.options.length; i++) {
		var li = document.createElement('li');
		li.innerHTML = selectObj.options[i].innerHTML;
		li.k_id = i;
		li.k_value = selectObj.options[i].value;
		if(selectObj.options[i].selected) {
			defaultopt = selectObj.options[i].innerHTML;
			defaultv = selectObj.options[i].value;
			li.className = 'current';
			selectObj.setAttribute('selecti', i);
		}
		li.onclick = function() {
			if($(selectId + '_ctrl').innerHTML != this.innerHTML) {
				var lis = menuObj.getElementsByTagName('li');
				lis[$(selectId).getAttribute('selecti')].className = '';
				this.className = 'current';
				$(selectId + '_ctrl').innerHTML = this.innerHTML;
				$(selectId).setAttribute('selecti', this.k_id);
				$(selectId).options.length = 0;
				$(selectId).options[0] = new Option('', this.k_value);
				eval(selectObj.getAttribute('change'));
			}
			hideMenu(menuObj.id);
			return false;
		};
		ul.appendChild(li);
	}

	selectObj.options.length = 0;
	selectObj.options[0]= new Option('', defaultv);
	selectObj.style.display = 'none';
	selectObj.outerHTML += '<a href="javascript:;" hidefocus="true" id="' + selectId + '_ctrl" style="width:' + selectwidth + '" tabindex="1">' + defaultopt + '</a>';

	menuObj.id = selectId + '_ctrl_menu';
	menuObj.className = 'sltm';
	menuObj.style.display = 'none';
	menuObj.style.width = selectwidth;
	menuObj.appendChild(ul);
	$('append_parent').appendChild(menuObj);

	$(selectId + '_ctrl').onclick = function(e) {
		$(selectId + '_ctrl_menu').style.width = selectwidth;
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var offset = fetchOffset($(selectId + '_ctrl'));
		var maxh = (document.documentElement.clientHeight - offset['top'] + scrollTop) - 10;
		showMenu({'ctrlid':(selectId == 'loginfield' ? 'account' : selectId + '_ctrl'),'menuid':selectId + '_ctrl_menu','evt':'click','pos':'13','maxh':maxh});
		doane(e);
	};
	$(selectId + '_ctrl').onfocus = menuObj.onfocus = function() {
		_attachEvent(document.body, 'keydown', handleKeyDown);
	};
	$(selectId + '_ctrl').onblur = menuObj.onblur = function() {
		_detachEvent(document.body, 'keydown', handleKeyDown);
	};
	$(selectId + '_ctrl').onkeyup = function(e) {
		e = e ? e : window.event;
		value = e.keyCode;
		if(value == 40 || value == 38) {
			if(menuObj.style.display == 'none') {
				$(selectId + '_ctrl').onclick();
			} else {
				lis = menuObj.getElementsByTagName('li');
				selecti = selectObj.getAttribute('selecti');
				lis[selecti].className = '';
				if(value == 40) {
					selecti = parseInt(selecti) + 1;
				} else if(value == 38) {
					selecti = parseInt(selecti) - 1;
				}
				if(selecti < 0) {
					selecti = lis.length - 1
				} else if(selecti > lis.length - 1) {
					selecti = 0;
				}
				lis[selecti].className = 'current';
				selectObj.setAttribute('selecti', selecti);
				lis[selecti].parentNode.scrollTop = lis[selecti].offsetTop;
			}
		} else if(value == 13) {
			var lis = menuObj.getElementsByTagName('li');
			lis[selectObj.getAttribute('selecti')].onclick();
		} else if(value == 27) {
			hideMenu(menuObj.id);
		}
	};
}

function detectCapsLock(e, obj) {
	var valueCapsLock = e.keyCode ? e.keyCode : e.which;
	var valueShift = e.shiftKey ? e.shiftKey : (valueCapsLock == 16 ? true : false);
	this.clearDetect = function () {
		obj.className = 'txt';
	};

	obj.className = (valueCapsLock >= 65 && valueCapsLock <= 90 && !valueShift || valueCapsLock >= 97 && valueCapsLock <= 122 && valueShift) ? 'clck txt' : 'txt';

	if(BROWSER.ie) {
		event.srcElement.onblur = this.clearDetect;
	} else {
		e.target.onblur = this.clearDetect;
	}
}

function switchTab(prefix, current, total) {
	for(i = 1; i <= total;i++) {
		$(prefix + '_' + i).className = '';
		$(prefix + '_c_' + i).style.display = 'none';
	}
	$(prefix + '_' + current).className = 'a';
	$(prefix + '_c_' + current).style.display = '';
}

function showselect(obj, inpid, t, rettype) {
	if(!obj.id) {
		var t = !t ? 0 : t;
		var rettype = !rettype ? 0 : rettype;
		obj.id = 'calendarexp_' + Math.random();
		div = document.createElement('div');
		div.id = obj.id + '_menu';
		div.style.display = 'none';
		div.className = 'p_pop';
		$('append_parent').appendChild(div);
		s = '';
		if(!t) {
			s += showselect_row(inpid, '一天', 1, 0, rettype);
			s += showselect_row(inpid, '一周', 7, 0, rettype);
			s += showselect_row(inpid, '一个月', 30, 0, rettype);
			s += showselect_row(inpid, '三个月', 90, 0, rettype);
			s += showselect_row(inpid, '自定义', -2);
		} else {
			if($(t)) {
				var lis = $(t).getElementsByTagName('LI');
				for(i = 0;i < lis.length;i++) {
					s += '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = this.innerHTML">' + lis[i].innerHTML + '</a>';
				}
				s += showselect_row(inpid, '自定义', -1);
			} else {
				s += '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'0\'">永久</a>';
				s += showselect_row(inpid, '7 天', 7, 1, rettype);
				s += showselect_row(inpid, '14 天', 14, 1, rettype);
				s += showselect_row(inpid, '一个月', 30, 1, rettype);
				s += showselect_row(inpid, '三个月', 90, 1, rettype);
				s += showselect_row(inpid, '半年', 182, 1, rettype);
				s += showselect_row(inpid, '一年', 365, 1, rettype);
				s += showselect_row(inpid, '自定义', -1);
			}
		}
		$(div.id).innerHTML = s;
	}
	showMenu({'ctrlid':obj.id,'evt':'click'});
	if(BROWSER.ie && BROWSER.ie < 7) {
		doane(event);
	}
}

function showselect_row(inpid, s, v, notime, rettype) {
	if(v >= 0) {
		if(!rettype) {
			var notime = !notime ? 0 : 1;
			t = today.getTime();
			t += 86400000 * v;
			d = new Date();
			d.setTime(t);
			return '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'' + d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + (!notime ? ' ' + d.getHours() + ':' + d.getMinutes() : '') + '\'">' + s + '</a>';
		} else {
			return '<a href="javascript:;" onclick="$(\'' + inpid + '\').value = \'' + v + '\'">' + s + '</a>';
		}
	} else if(v == -1) {
		return '<a href="javascript:;" onclick="$(\'' + inpid + '\').focus()">' + s + '</a>';
	} else if(v == -2) {
		return '<a href="javascript:;" onclick="$(\'' + inpid + '\').onclick()">' + s + '</a>';
	}
}

function showColorBox(ctrlid, layer, k) {
	if(!$(ctrlid + '_menu')) {
		var menu = document.createElement('div');
		menu.id = ctrlid + '_menu';
		menu.className = 'p_pop colorbox';
		menu.unselectable = true;
		menu.style.display = 'none';
		var coloroptions = ['Black', 'Sienna', 'DarkOliveGreen', 'DarkGreen', 'DarkSlateBlue', 'Navy', 'Indigo', 'DarkSlateGray', 'DarkRed', 'DarkOrange', 'Olive', 'Green', 'Teal', 'Blue', 'SlateGray', 'DimGray', 'Red', 'SandyBrown', 'YellowGreen', 'SeaGreen', 'MediumTurquoise', 'RoyalBlue', 'Purple', 'Gray', 'Magenta', 'Orange', 'Yellow', 'Lime', 'Cyan', 'DeepSkyBlue', 'DarkOrchid', 'Silver', 'Pink', 'Wheat', 'LemonChiffon', 'PaleGreen', 'PaleTurquoise', 'LightBlue', 'Plum', 'White'];
		var colortexts = ['黑色', '赭色', '暗橄榄绿色', '暗绿色', '暗灰蓝色', '海军色', '靛青色', '墨绿色', '暗红色', '暗桔黄色', '橄榄色', '绿色', '水鸭色', '蓝色', '灰石色', '暗灰色', '红色', '沙褐色', '黄绿色', '海绿色', '间绿宝石', '皇家蓝', '紫色', '灰色', '红紫色', '橙色', '黄色', '酸橙色', '青色', '深天蓝色', '暗紫色', '银色', '粉色', '浅黄色', '柠檬绸色', '苍绿色', '苍宝石绿', '亮蓝色', '洋李色', '白色'];
		var str = '';
		for(var i = 0; i < 40; i++) {
			str += '<input type="button" style="background-color: ' + coloroptions[i] + '"' + (typeof setEditorTip == 'function' ? ' onmouseover="setEditorTip(\'' + colortexts[i] + '\')" onmouseout="setEditorTip(\'\')"' : '') + ' onclick="'
			+ (typeof wysiwyg == 'undefined' ? 'seditor_insertunit(\'' + k + '\', \'[color=' + coloroptions[i] + ']\', \'[/color]\')' : (ctrlid == editorid + '_tbl_param_4' ? '$(\'' + ctrlid + '\').value=\'' + coloroptions[i] + '\';hideMenu(2)' : 'discuzcode(\'forecolor\', \'' + coloroptions[i] + '\')'))
			+ '" title="' + colortexts[i] + '" />' + (i < 39 && (i + 1) % 8 == 0 ? '<br />' : '');
		}
		menu.innerHTML = str;
		$('append_parent').appendChild(menu);
	}
	showMenu({'ctrlid':ctrlid,'evt':'click','layer':layer});
}



function smilies_show(id, smcols, seditorkey) {
	if(seditorkey && !$(seditorkey + 'sml_menu')) {
		var div = document.createElement("div");
		div.id = seditorkey + 'sml_menu';
		div.style.display = 'none';
		div.className = 'sllt';
		$('append_parent').appendChild(div);
		var div = document.createElement("div");
		div.id = id;
		div.style.overflow = 'hidden';
		$(seditorkey + 'sml_menu').appendChild(div);
	}
	if(typeof smilies_type == 'undefined') {
		var scriptNode = document.createElement("script");
		scriptNode.type = "text/javascript";
		scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
		scriptNode.src = 'data/cache/common_smilies_var.js?' + VERHASH;
		$('append_parent').appendChild(scriptNode);
		if(BROWSER.ie) {
			scriptNode.onreadystatechange = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		} else {
			scriptNode.onload = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		}
	} else {
		smilies_onload(id, smcols, seditorkey);
	}
}

function smilies_onload(id, smcols, seditorkey) {
	seditorkey = !seditorkey ? '' : seditorkey;
	smile = getcookie('smile').split('D');
	if(typeof smilies_type == 'object') {
		if(smile[0] && smilies_array[smile[0]]) {
			CURRENTSTYPE = smile[0];
		} else {
			for(i in smilies_array) {
				CURRENTSTYPE = i;break;
			}
		}
		smiliestype = '<div class="slg"><ul>';
		for(i in smilies_type) {
			if(smilies_type[i][0]) {
				smiliestype += '<li><a href="javascript:;" hidefocus="true" ' + (CURRENTSTYPE == i ? 'class="current"' : '') + ' id="'+seditorkey+'stype_'+i+'" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', '+i+', 1, \'' + seditorkey + '\');if(CURRENTSTYPE) {$(\''+seditorkey+'stype_\'+CURRENTSTYPE).className=\'\';}this.className=\'current\';CURRENTSTYPE='+i+';doane(event);">'+smilies_type[i][0]+'</a></li>';
			}
		}
		smiliestype += '</ul></div>';
		$(id).innerHTML = smiliestype + '<div id="' + id + '_data"></div><div class="sllt_p" id="' + id + '_page"></div>';
		smilies_switch(id, smcols, CURRENTSTYPE, smile[1], seditorkey);
		smilies_fastdata = '';
		if($('fastsmilies') && smilies_fast) {
			var j = 0;
			for(i = 0;i < smilies_fast.length; i++) {
				if(j == 0) {
					smilies_fastdata += '<tr>';
				}
				j = ++j > 3 ? 0 : j;
				s = smilies_array[smilies_fast[i][0]][smilies_fast[i][1]][smilies_fast[i][2]];
				smilieimg = STATICURL + 'image/smiley/' + smilies_type[smilies_fast[i][0]][1] + '/' + s[2];
				img[k] = new Image();
				img[k].src = smilieimg;
				smilies_fastdata += s ? '<td onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
					'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
			}
			$('fastsmilies').innerHTML = '<table cellspacing="0" cellpadding="0"><tr>' + smilies_fastdata + '</tr></table>' +
				'<p class="ptw"><a href="javascript:;" onclick="showMenu({\'ctrlid\':\'' + seditorkey + 'sml\',\'evt\':\'click\',\'layer\':2});doane(event)">显示所有表情</a></p>';
		}
	}
}

function smilies_switch(id, smcols, type, page, seditorkey) {
	page = page ? page : 1;
	if(!smilies_array[type] || !smilies_array[type][page]) return;
	setcookie('smile', type + 'D' + page, 31536000);
	smiliesdata = '<table id="' + id + '_table" cellpadding="0" cellspacing="0"><tr>';
	j = k = 0;
	img = [];
	for(i in smilies_array[type][page]) {
		if(j >= smcols) {
			smiliesdata += '<tr>';
			j = 0;
		}
		s = smilies_array[type][page][i];
		smilieimg = STATICURL + 'image/smiley/' + smilies_type[type][1] + '/' + s[2];
		img[k] = new Image();
		img[k].src = smilieimg;
		smiliesdata += s && s[0] ? '<td onmouseover="smilies_preview(\'' + seditorkey + '\', \'' + id + '\', this, ' + s[5] + ')" onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
			'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
		j++;k++;
	}
	smiliesdata += '</table>';
	smiliespage = '';
	if(smilies_array[type].length > 2) {
		prevpage = ((prevpage = parseInt(page) - 1) < 1) ? smilies_array[type].length - 1 : prevpage;
		nextpage = ((nextpage = parseInt(page) + 1) == smilies_array[type].length) ? 1 : nextpage;
		smiliespage = '<div class="z"><a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + prevpage + ', \'' + seditorkey + '\');doane(event);">上页</a>' +
			'<a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + nextpage + ', \'' + seditorkey + '\');doane(event);">下页</a></div>' +
			page + '/' + (smilies_array[type].length - 1);
	}
	$(id + '_data').innerHTML = smiliesdata;
	$(id + '_page').innerHTML = smiliespage;
}

function smilies_preview(seditorkey, id, obj, w) {
	var menu = $('smilies_preview');
	if(!menu) {
		menu = document.createElement('div');
		menu.id = 'smilies_preview';
		menu.className = 'sl_pv';
		menu.style.display = 'none';
		$('append_parent').appendChild(menu);
	}
	menu.innerHTML = '<img width="' + w + '" src="' + obj.childNodes[0].src + '" />';
	mpos = fetchOffset($(id + '_data'));
	spos = fetchOffset(obj);
	pos = spos['left'] >= mpos['left'] + $(id + '_data').offsetWidth / 2 ? '13' : '24';
	showMenu({'ctrlid':obj.id,'showid':id + '_data','menuid':menu.id,'pos':pos,'layer':3});
}

//function saveUserdata(name, data) {
//	if(BROWSER.ie){
//		with(document.documentElement) {
//			setAttribute("value", data);
//			save('Discuz_' + name);
//		}
//	} else if(window.sessionStorage){
//		sessionStorage.setItem('Discuz_' + name, data);
//		}
//}
//
//function loadUserdata(name) {
//	if(BROWSER.ie){
//		with(document.documentElement) {
//					load('Discuz_' + name);
//			return getAttribute("value");
//			}
//	} else if(window.sessionStorage){
//		return sessionStorage.getItem('Discuz_' + name);
//		}
//}

function seditor_ctlent(event, script) {
	if(event.ctrlKey && event.keyCode == 13 || event.altKey && event.keyCode == 83) {
		eval(script);
	}
}

function seditor_insertunit(key, text, textend, moveend) {
	$(key + 'message').focus();
	textend = isUndefined(textend) ? '' : textend;
	moveend = isUndefined(textend) ? 0 : moveend;
	startlen = strlen(text);
	endlen = strlen(textend);
	if(!isUndefined($(key + 'message').selectionStart)) {
		var opn = $(key + 'message').selectionStart + 0;
		if(textend != '') {
			text = text + $(key + 'message').value.substring($(key + 'message').selectionStart, $(key + 'message').selectionEnd) + textend;
		}
		$(key + 'message').value = $(key + 'message').value.substr(0, $(key + 'message').selectionStart) + text + $(key + 'message').value.substr($(key + 'message').selectionEnd);
		if(!moveend) {
			$(key + 'message').selectionStart = opn + strlen(text) - endlen;
			$(key + 'message').selectionEnd = opn + strlen(text) - endlen;
		}
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(textend != '') {
			text = text + sel.text + textend;
		}
		sel.text = text.replace(/\r?\n/g, '\r\n');
		if(!moveend) {
			sel.moveStart('character', -endlen);
			sel.moveEnd('character', -endlen);
		}
		sel.select();
	} else {
		$(key + 'message').value += text;
	}
	hideMenu(2);
	if(BROWSER.ie) {
		doane();
	}
}

function parseurl(str, mode, parsecode) {
	if(isUndefined(parsecode)) parsecode = true;
	if(parsecode) str= str.replace(/\s*\[code\]([\s\S]+?)\[\/code\]\s*/ig, function($1, $2) {return codetag($2);});
	str = str.replace(/([^>=\]"'\/]|^)((((https?|ftp):\/\/)|www\.)([\w\-]+\.)*[\w\-\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!]*)+\.(jpg|gif|png|bmp))/ig, mode == 'html' ? '$1<img src="$2" border="0">' : '$1[img]$2[/img]');
	str = str.replace(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig, mode == 'html' ? '$1<a href="mailto:$2">$2</a>' : '$1[email]$2[/email]');
	if(parsecode) {
		for(var i = 0; i <= DISCUZCODE['num']; i++) {
			str = str.replace("[\tDISCUZ_CODE_" + i + "\t]", DISCUZCODE['html'][i]);
		}
	}
	return str;
}

function codetag(text) {
	DISCUZCODE['num']++;
	if(typeof wysiwyg != 'undefined' && wysiwyg) text = text.replace(/<br[^\>]*>/ig, '\n').replace(/<(\/|)[A-Za-z].*?>/ig, '');
	DISCUZCODE['html'][DISCUZCODE['num']] = '[code]' + text + '[/code]';
	return '[\tDISCUZ_CODE_' + DISCUZCODE['num'] + '\t]';
}

if(typeof IN_ADMINCP == 'undefined') {
	if(creditnotice != '' && getcookie('creditnotice')) {
		_attachEvent(window, 'load', showCreditPrompt, document);
	}
}

if(BROWSER.ie) {
	document.documentElement.addBehavior("#default#userdata");
}

function initTab(frameId, type) {
	if (typeof document['diyform'] == 'object' || $(frameId).className.indexOf('tab') < 0) return false;
	type = type || 'click';
	var tabs = $(frameId+'_title').childNodes;
	var arrTab = [];
	for(var i in tabs) {
		if (tabs[i]['nodeType'] == 1 && tabs[i]['className'].indexOf('move-span') > -1) {
			arrTab.push(tabs[i]);
		}
	}
	var counter = 0;
	var tab = document.createElement('ul');
	tab.className = 'tb cl';
	var len = arrTab.length;
	for(var i = 0;i < len; i++) {
		var tabId = arrTab[i].id;
		if (hasClass(arrTab[i],'frame') || hasClass(arrTab[i],'tab')) {
			var arrColumn = [];
			for (var j in arrTab[i].childNodes) {
				if (typeof arrTab[i].childNodes[j] == 'object' && !hasClass(arrTab[i].childNodes[j],'title')) arrColumn.push(arrTab[i].childNodes[j]);
			}
			var frameContent = document.createElement('div');
			frameContent.id = tabId+'_content';
			frameContent.className = hasClass(arrTab[i],'frame') ? 'content cl '+arrTab[i].className.substr(arrTab[i].className.lastIndexOf(' ')+1) : 'content cl';
			var colLen = arrColumn.length;
			for (var k = 0; k < colLen; k++) {
				frameContent.appendChild(arrColumn[k]);
			}
		} else {
			var frameContent = $(tabId+'_content');
			frameContent = frameContent || document.createElement('div');
		}
		frameContent.style.display = counter ? 'none' : '';
		$(frameId+'_content').appendChild(frameContent);


		var li = document.createElement('li');
		li.id = tabId;
		li.className = counter ? '' : 'a';
		li.innerHTML = arrTab[i]['innerText'] ? arrTab[i]['innerText'] : arrTab[i]['textContent'];
		var a = arrTab[i].getElementsByTagName('a');
		var href = a && a[0] ? a[0].href : 'javascript:;';
        var onclick='';
        if(a && a[0] && a[0].href==''){
            onclick = type == 'click' ? ' onclick="return false;"' : '';
        }
		li.innerHTML = '<a href="'+href+'"'+onclick+' onfocus="this.blur();">'+li.innerHTML+'</a>';
		li['on'+type] = function(e){switchTabUl(e);};
		tab.appendChild(li);
		$(frameId+'_title').removeChild(arrTab[i]);
		counter++;
	}
	$(frameId+'_title').appendChild(tab);
}
function switchTabUl (e) {
	e = e || window.event;
	var aim = e.target || e.srcElement;
	var tabId = aim.id;
	var parent = aim.parentNode;
	while(parent['nodeName'] != 'UL' && parent['nodeName'] != 'BODY') {
		tabId = parent.id
		parent = parent.parentNode;
	}
	if(parent['nodeName'] == 'BODY') return false;
	var tabs = parent.childNodes;
	var len2 = tabs.length;
	for(var j = 0; j < len2; j++) {
		tabs[j].className = (tabs[j].id == tabId) ? 'a' : '';
		var content = $(tabs[j].id+'_content');
		if (content) content.style.display = tabs[j].id == tabId ? 'block' : 'none';
	}
}

function ctrlEnter(event, btnId, onlyEnter) {
	if(isUndefined(onlyEnter)) onlyEnter = 0;
	if((event.ctrlKey || onlyEnter) && event.keyCode == 13) {
		$(btnId).click();
		return false;
	}
	return true;
}

function hasClass(elem, className) {
	return elem.className && (" " + elem.className + " ").indexOf(" " + className + " ") != -1;
}


function runslideshow() {
	var slideshows = [];
	var elements = document.getElementsByTagName('ul');
	for(var i=0,L=elements.length; i<L; i++) {
		if(hasClass(elements[i], 'slideshow')) {
			slideshows.push(elements[i]);
		}
	}
	var elements = document.getElementsByTagName('div');
	for(var i=0,L=elements.length; i<L; i++) {
		if(hasClass(elements[i], 'slideshow')) {
			slideshows.push(elements[i]);
		}
	}
	for(var i=0,L=slideshows.length; i<L; i++) {
		new slideshow(slideshows[i]);
	}
}
/* Modified by Izzln */
/* Add IE6 transparent img function */
function slideshow(el) {
	var obj = this;
	var parent = el;
	while ((parent = parent.parentNode) && parent != document.body) {
		if (hasClass(parent,'content')) {
			this.blockid = parent.id.replace(/_content/g,'');
			break;
		}
	}
	this.blockid = this.blockid ? this.blockid : el.parentNode.parentNode.id;
	if(typeof slideshow.entities == 'undefined') {
		slideshow.entities = [];
	}
	for(var i=0,L=slideshow.entities.length; i<L;i++) {
		if(slideshow.entities[i].blockid == this.blockid) {
			return ;
		}
	}
	this.id = slideshow.entities.length;
	slideshow.entities[this.id] = this;
	this.container = el;
	this.elements = [];
	this.imgs = [];
	this.imgLoad = [];
	this.imgLoaded = 0;
	this.index = this.length = 0;
	var nodes = el.childNodes;
	for(var i=0, L=nodes.length; i<L; i++) {
		if (nodes[i].nodeType == 1) {
			this.elements[this.length] = nodes[i];
			this.length += 1;
		}
	}
	for(var i=0, L=this.elements.length; i<L; i++) {
		this.elements[i].style.display = "none";
	}
	this.container.parentNode.style.position = 'relative';
	this.slidebar = document.createElement('div');
	this.slidebar.className = 'slidebar';
	this.slidebar.style.display = 'none';
	var html = '<ul>';
	for(var i=0; i<this.length; i++) {
		html += '<li onmouseover="slideshow.entities[' + this.id + '].xactive(' + i + '); return false;">' + (i + 1).toString() + '</li>';
	}
	html += '</ul>';
	this.slidebar.innerHTML = html;
	this.container.parentNode.appendChild(this.slidebar);
	this.controls = this.slidebar.getElementsByTagName('li');

	this.active = function(index) {
		this.elements[this.index].style.display = "none";
		this.elements[index].style.display = "block";
		this.controls[this.index].className = '';
		this.controls[index].className = 'on';
		this.index = index;
	};
	this.xactive = function(index) {
		clearTimeout(this.timer);
		this.active(index);
		var ss = this;
		this.timer = setTimeout(function(){
			ss.run();
		}, 8000);
	};
	this.run = function() {
		var index = this.index + 1 < this.length ? this.index + 1 : 0;
		this.active(index);
		var ss = this;
		this.timer = setTimeout(function(){
			ss.run();
		}, 2500);
	};

	var imgs = el.getElementsByTagName('img');
	for(var i = 0; i < imgs.length; i++) {
		this.imgs.push(imgs[i]);
	}
	for(i=0, L=this.imgs.length; i<L; i++) {
		if(BROWSER.ie && BROWSER.ie < 7 && this.imgs[i].src.indexOf(".png") != -1) {
			var imgobj = el.getElementsByTagName("dd")[i].getElementsByTagName("img")[0];
			var imgdiv = document.createElement("div");
			imgdiv.style.width = imgobj.style.width;
			imgdiv.style.height = imgobj.style.height;
			imgdiv.style.cursor = "pointer";
			imgdiv.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + this.imgs[i].src + "', sizingMethod='scale')";
			el.getElementsByTagName("dd")[i].getElementsByTagName("a")[0].removeChild(imgobj);
			el.getElementsByTagName("dd")[i].getElementsByTagName("a")[0].appendChild(imgdiv);
		}
		this.imgLoad.push(new Image());
		this.imgLoad[i].src = this.imgs[i].src;
		this.imgLoad[i].onerror = function (){obj.imgLoaded ++;};
	}

	this.getSize = function (img) {
		if (!img) return false;
		this.width = img.width ? parseInt(img.width) : 0;
		this.height = img.height ? parseInt(img.height) : 0;
		var ele = img.parentNode;
		while ((!this.width || !this.height) && !hasClass(ele,'slideshow') && ele != document.body) {
			this.width = ele.style.width ? parseInt(ele.style.width) : 0;
			this.height = ele.style.height ? parseInt(ele.style.height) : 0;
			ele = ele.parentNode;
		}
		return true;
	};

	this.getSize(imgs[0]);
	
	this.checkLoad = function () {
		var obj = this;
		for(i = 0;i < this.imgs.length;i++) {
			if(this.imgLoad[i].complete && !this.imgLoad[i].status) {
				this.imgLoaded++;
				this.imgLoad[i].status = 1;
			}
		}
		if(this.imgLoaded < this.imgs.length) {
			if (!$(this.blockid+'_percent')) {
				var dom = document.createElement('div');
				dom.id = this.blockid+"_percent";
				dom.style.width = this.width ? this.width+'px' : '150px';
				dom.style.height = this.height ? this.height+'px' : '150px';
				dom.style.lineHeight = this.height ? this.height+'px' : '150px';
				dom.style.backgroundColor = '#ccc';
				dom.style.textAlign = 'center';
				dom.style.top = '0';
				dom.style.left = '0';
				el.parentNode.appendChild(dom);
			}
			el.parentNode.style.position = 'relative';
			$(this.blockid+'_percent').innerHTML = (parseInt(this.imgLoaded / this.imgs.length * 100)) + '%';
			setTimeout(function () {obj.checkLoad();}, 100)
		} else {
			if ($(this.blockid+'_percent')) el.parentNode.removeChild($(this.blockid+'_percent'));
			this.container.style.display = 'block';
			this.slidebar.style.display = '';
			this.index = this.length -1;
			this.run();
		}
	};
	this.checkLoad();
}

/* Added by Izzln */
/* Function called to provide all scroll styles */
/* scrollType:
      marquee, ticker

   assets:
   	  direction: 0: up; 1: down; 2: left; 3: right;
   	  moduleHeight: num
   	  displayTicksNum: num
*/
function listScroll(target, scrollType, assets) {
	if(target != null) {
		if(scrollType == "marquee") {
			new marqueeScroll(target, assets);
		} else if(scrollType == "ticker") {
			new tickerScroll(target, assets);
		}
	}
}

/* Added by Izzln */
/* Please don't call it directly */
/* Only accept direction 0 or 1 */
function tickerScroll(target, assets) {
	var mode = 0;
	isUndefined(assets.moduleHeight) ? '' : mode += 1;
	isUndefined(assets.displayTicksNum) ? '' : mode += 2;
					
	var direction = !isNaN(parseInt(assets.direction)) &&  assets.direction < 2 ? assets.direction : 1;
	var displayTicksNum = !isNaN(parseInt(assets.displayTicksNum)) ? assets.displayTicksNum : 3;
	var moduleHeight = !isNaN(parseInt(assets.moduleHeight)) ? assets.moduleHeight : 200;
	//alert("direction: " + direction + ", displayTicksNum: " + displayTicksNum + ", moduleHeight: " + moduleHeight);				
	var ticks = target.getElementsByTagName("li");
	addClass(target, "ticks");
	var whitegrDiv = document.createElement("div");
	whitegrDiv.className = "whitegr";
	target.appendChild(whitegrDiv);
	var runSign = true;
	
	attachEventListener(target, "mouseover", function() {runSign = false;}, false);
	attachEventListener(target, "mouseout", function() {runSign = true;}, false);
	
	//rebuild the list
	var tickULs = target.getElementsByTagName("ul");
	var newUL = document.createElement("ul");
	newUL.className = tickULs[0].className;
	tickULs[0].parentNode.appendChild(newUL);
	
	if(direction) {
		var tempTickerHeight = 0;
		for(var i = 0; i < ticks.length; i++) {
			if(tempTickerHeight < moduleHeight) {
				//alert("i: " + i + ";ticks[" + (ticks.length - i - 1) + "].offsetHeight: " + ticks[ticks.length - i - 1].offsetHeight);
				tempTickerHeight += ticks[ticks.length - i - 1].offsetHeight;
			} else {
				var tempPointer = ticks.length - i;
				break;
			}
		}
		for(var i = 0; i < ticks.length; i++) {
			newUL.appendChild(ticks[0]);
		}
		for(var i = 0; i < tempPointer; i++) {
			newUL.appendChild(ticks[0]);
		}				
	} else {
		for(var i = 0; i < ticks.length; i++) {
			newUL.appendChild(ticks[ticks.length - i - 1]);
		}
	}
	for(var i = 0; i < tickULs.length - 1; i++) {
		tickULs[i].style.display = "none";
	}
	
	var tickerHeight = 0;
	var tickerElements = [];
	for(var i = 0; i < target.childNodes.length; i++) {
		if(target.childNodes[i].nodeType != 3) { tickerElements.push(target.childNodes[i]); }
	}
	for(var i = 0; i < tickerElements.length; i++) {
			tickerHeight += tickerElements[i].offsetHeight;
	}
	
	var tickFadeIn = function(tick, duration) {
		var times = Math.round((duration * fps) / 1000);
	    var step = Math.round(100 / times);
	    var i = 0;
	
	    var intervalProcess = setInterval(function() {
	        setOpacity(tick, i, true);
	
	        if (i <= 100) {
	            i += step;
	        }
	
	        else {
	            clearInterval(intervalProcess);
	            setOpacity(tick, 100, true);
	        }
	    },
	    Math.round(1000 / fps));				
	};
	
	var tickFadeOut = function(tick, duration) {
		var times = Math.round((duration * fps) / 1000);
	    var step = Math.round(100 / times);
	    var i = 100;
	
	    var intervalProcess = setInterval(function() {
	        setOpacity(tick, i, true);
	
	        if (i >= 0) {
	            i -= step;
	        }
	
	        else {
	            clearInterval(intervalProcess);
	            setOpacity(tick, 0, true);
	            tickShorten(ticks[0], 1500);
	        }
	    },
	    Math.round(1000 / fps));				
	};
	
	var tickEnlarge = function(tick, duration) {
		var times = Math.round(duration * fps / 1000);
		
		var tickPaddingTop = parseInt(retrieveComputedStyle(tick, "paddingTop"));
		var tickPaddingBottom = parseInt(retrieveComputedStyle(tick, "paddingBottom"));
		
		var tickHeight = tick.offsetHeight;
		var step = Math.ceil(tickHeight / times);
		var posi = 0;
		//alert("tickoffsetHeight: " + tick.offsetHeight + "; tickHeight: " + tickHeight);
		
		tick.style.minHeight = 0;
		tick.style.height = 0;
		tick.style.paddingTop = 0;
		tick.style.paddingBottom = 0;
		tick.parentNode.insertBefore(tick, ticks[0]);
		
		var intervalProcess = setInterval(function() {
			if(runSign) {
				if(posi < tickHeight) {
				posi += step;
				tick.style.height = posi + "px";
				} else {
					clearInterval(intervalProcess);
					tick.style.height = tickHeight + "px";
					tick.style.minHeight = "20px";
					tick.style.height = "auto";
					tick.style.paddingTop = tickPaddingTop + "px";
					tick.style.paddingBottom = tickPaddingBottom + "px";
					tickFadeIn(tick, 500);	
				}
			}
		}, Math.round(duration / times));
	}
	
	var tickShorten = function(tick, duration) {
		var times = Math.round(duration * fps / 1000);
		
		var tickPaddingTop = parseInt(retrieveComputedStyle(tick, "paddingTop"));
		var tickPaddingBottom = parseInt(retrieveComputedStyle(tick, "paddingBottom"));
		
		var tickHeight = tick.offsetHeight;
		var step = Math.ceil(tickHeight / times);
		var posi = tickHeight;
		//alert("tickoffsetHeight: " + tick.offsetHeight + "; tickHeight: " + tickHeight);
		
		tick.style.minHeight = 0;
		tick.style.height = tickHeight + "px";
		tick.style.paddingTop = 0;
		tick.style.paddingBottom = 0;
		
		var intervalProcess = setInterval(function() {
			if(runSign) {
				if(posi > 0) {
				posi -= step;
				posi >= 0 ? "" : posi = 0;
				tick.style.height = posi + "px";
				} else {
					clearInterval(intervalProcess);
					tick.style.height = 0;
					tick.parentNode.appendChild(tick);
					tick.style.minHeight = "20px";
					tick.style.height = "auto";
					tick.style.paddingTop = tickPaddingTop + "px";
					tick.style.paddingBottom = tickPaddingBottom + "px";
					setOpacity(tick, 100, true);
				}
			}
		}, Math.round(duration / times));
	};
	
	var insertTick = function() {
		if(runSign) {
			setOpacity(ticks[ticks.length - 1], 0, true);
			tickEnlarge(ticks[ticks.length - 1], 1500);		
		}
	};
	
	var removeTick = function() {
		if(runSign) {
			tickFadeOut(ticks[0], 500);		
		}
	};
	
	
	//tickerScroll Core
	if(mode > 1) {
	
	} else if(mode = 1) {
		if(tickerHeight > moduleHeight) {
			if(direction) {
				insertTick();
				setInterval(insertTick, 5000);
			} else {							
				removeTick();
				setInterval(removeTick, 5000);						
			}
		}else {
			tickerElements[0].style.marginTop = (moduleHeight - tickerHeight) / 2 + "px";
		}
	}
}


/* Added by Izzln */
function runMarqueeScroll() {
	var marquees = [];
	var objects = document.getElementsByTagName("div");
	for(var i = 0; i < objects.length; i++) {
		if(hasClass(objects[i], 'scrollMarquee')) {
			marquees.push(objects[i]);
		}
	}	

	for(var i = 0; i < marquees.length; i++) {
		new marqueeScroll(marquees[i]);
	}
}

/* Added by Izzln */
function marqueeScroll(target) {
	var list = target.getElementsByTagName("li");
	var listPoint = 0;
	var marginLeftEaseIntervalProcess;
	var speed = 40;
	var scrolling = false;
	
	list[0].parentNode.style.height = list[0].offsetHeight + "px";
	for(var i = 0; i < list.length; i++) {
		list[i].style.display = "none";
	}
	
	var marginLeftEase = function(target, destination, duration, callback) {
		var duration = duration ? duration : 500;
		var originalML = parseInt(retrieveComputedStyle(target, "marginLeft"));
		var lOr = destination > originalML ? true : false;
		var runSign = true;
	
		var times = Math.round(duration * fps / 1000);
		var step = Math.ceil(Math.abs(destination - originalML) / times);
		var posi = originalML;
		
		attachEventListener(target, "mouseover", function() {runSign = false;}, false);
		attachEventListener(target, "mouseout", function() {runSign = true;}, false);
		
		clearInterval(marginLeftEaseIntervalProcess);
		marginLeftEaseIntervalProcess = setInterval(function() {
			if(runSign) { 
				if(times && Math.abs(parseInt(retrieveComputedStyle(target, "marginLeft")) - originalML) < Math.abs(destination)) {
					if(lOr) {
						posi += step;
						target.style.marginLeft = posi + "px";
					} else {
						posi -= step;
						target.style.marginLeft = posi + "px";
					}
					times--;
				} else {
					clearInterval(marginLeftEaseIntervalProcess);
					target.style.marginLeft = destination + "px";
					eval(callback);
				}
			}
		}, Math.round(duration / times));
	};
	
	var mscrollingOver = function() {
		list[listPoint].style.display = "none";
		list[listPoint].style.marginLeft = "0px";
        scrolling = false;
        listPoint++;
		if(listPoint == list.length) { listPoint = 0; }
	};
	
	var mscrolling = function() {
		scrolling = true;
		fadeIn(list[listPoint], 200, 1);
		var scrollW = list[listPoint].clientWidth;
		//alert(scrollW + "," + list[listPoint].parentNode.offsetWidth);
		if(scrollW > list[listPoint].parentNode.offsetWidth) {
			setTimeout(function() {
				marginLeftEase(list[listPoint], -scrollW, scrollW * speed, "mscrollingOver()");
			}, 2000);
		} else {
			setTimeout(function() {
				mscrollingOver();
			}, 4000);
		}
	};
	
	setInterval(function(){
		if(!scrolling) {
			mscrolling();
		}
	}, 1000);
}


function updatestring(str1, str2, clear) {
	str2 = '_' + str2 + '_';
	return clear ? str1.replace(str2, '') : (str1.indexOf(str2) == -1 ? str1 + str2 : str1);
}

function parsepmcode(theform) {
	theform.message.value = parseurl(theform.message.value);
}

function toggle_collapse(objname, noimg, complex, lang) {
	var obj = $(objname);
	if(obj) {
		obj.style.display = obj.style.display == '' ? 'none' : '';
		var collapsed = getcookie('collapse');
		collapsed = updatestring(collapsed, objname, !obj.style.display);
		setcookie('collapse', collapsed, (collapsed ? 2592000 : -2592000));
	}
	if(!noimg) {
		var img = $(objname + '_img');
		if(img.tagName != 'IMG') {
			if(img.className.indexOf('_yes') == -1) {
				img.className = img.className.replace(/_no/, '_yes');
				if(lang) {
					img.innerHTML = lang[0];
				}
			} else {
				img.className = img.className.replace(/_yes/, '_no');
				if(lang) {
					img.innerHTML = lang[1];
				}
			}
		} else {
			img.src = img.src.indexOf('_yes.gif') == -1 ? img.src.replace(/_no\.gif/, '_yes\.gif') : img.src.replace(/_yes\.gif/, '_no\.gif');
		}
		img.blur();
	}
	if(complex) {
		var objc = $(objname + '_c');
		if(objc) {
			objc.className = objc.className == 'umh' ? 'umh umn' : 'umh';
		}
	}

}
function openDiy(){
	window.location.href = ((window.location.href + '').replace(/[\?\&]diy=yes/g, '').split('#')[0] + ( window.location.search && window.location.search.indexOf('?diy=yes') < 0 ? '&diy=yes' : '?diy=yes'));
}

function getClipboardData() {
	window.document.clipboardswf.SetVariable('str', clipboardswfdata);
}

function setCopy(text, msg){
	if(BROWSER.ie) {
		clipboardData.setData('Text', text);
		if(msg) {
			showDialog(msg, 'notice');
		}
	} else {
		var msg = '<div class="c"><div style="width: 200px; text-align: center; text-decoration:underline;">点此复制到剪贴板</div>' +
		AC_FL_RunContent('id', 'clipboardswf', 'name', 'clipboardswf', 'devicefont', 'false', 'width', '200', 'height', '40', 'src', STATICURL + 'image/common/clipboard.swf', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent', 'style' , 'margin-top:-20px') + '</div>';
		showDialog(msg, 'info');
		text = text.replace(/[\xA0]/g, ' ');
		clipboardswfdata = text;
	}
}

function showdistrict(container, elems, totallevel, changelevel) {
	var getdid = function(elem) {
		var op = elem.options[elem.selectedIndex];
		return op['did'] || op.getAttribute('did') || '0';
	};
	var pid = changelevel >= 1 && elems[0] && $(elems[0]) ? getdid($(elems[0])) : 0;
	var cid = changelevel >= 2 && elems[1] && $(elems[1]) ? getdid($(elems[1])) : 0;
	var did = changelevel >= 3 && elems[2] && $(elems[2]) ? getdid($(elems[2])) : 0;
	var coid = changelevel >= 4 && elems[3] && $(elems[3]) ? getdid($(elems[3])) : 0;
	var url = "home.php?mod=misc&ac=ajax&op=district&container="+container
		+"&province="+elems[0]+"&city="+elems[1]+"&district="+elems[2]+"&community="+elems[3]
		+"&pid="+pid + "&cid="+cid+"&did="+did+"&coid="+coid+'&level='+totallevel+'&handlekey='+container+'&inajax=1';
	ajaxget(url, container, '');
}

function setDoodle(fid, oid, url, tid, from) {
	if(tid == null) {
		hideWindow(fid);
	} else {
		$(tid).style.display = '';
		$(fid).style.display = 'none';
	}
	var doodleText = '[img]'+url+'[/img]';
	if($(oid) != null) {
		if(from == "editor") {
			insertImage(url);
		} else if(from == "fastpost") {
			seditor_insertunit('fastpost', doodleText);
		} else if(from == "forumeditor") {
			if(wysiwyg) {
				insertText('<img src="' + url + '" border="0" alt="" />', false);
			} else {
				insertText(doodleText, strlen(doodleText), 0);
			}
		} else {
			insertContent(oid, doodleText);
		}
	}
}

function searchFocus(obj) {
	if(obj.value == '请输入搜索内容') {
		obj.value = '';
	}
	$('sc').className = 'y f';
}

function searchBlur(obj) {
	if(obj.value == '' ) {
		obj.value = '请输入搜索内容';
		$('sc').className = 'y';
	}
}

function inituserabout() {
	if(!$('pp')) {
		return;
	}
	var sideArea = $('pp').parentNode;
	if(sideArea.className == 'sd pph') {
		$('ppc_menu').className = 'p_pop blk';
		$('ppc').onmouseover = function() {getAppIcon();showMenu('ppc');}
	} else {
		getAppIcon();
		$('ppc_menu').className = 'bbs';
		$('ppc_menu').style.display = 'block';
	}
}
function getAppIcon() {
	var appIcons = $('ppc_menu').getElementsByTagName('img');
	for(i=0;i<appIcons.length;i++) {
		if(appIcons[i].className=='appicn'){
			appIcons[i].src = 'http://appicon.manyou.com/icons/' + appIcons[i].name;
		}
	}
}

function showUser() {
	$('newpmbox_menu').style.display = USERABOUT_BOX ? '' : 'none';
	if(USERABOUT_BOX) {
		var x = new Ajax();
		x.get('home.php?mod=spacecp&ac=pm&op=getpmuser&inajax=1', function(s){
			USERABOUT_FS.addDataSource(eval('('+s+')'), 1);
		});
	}
	USERABOUT_BOX = !USERABOUT_BOX;
}

function setstyle(styleid) {
	setcookie('styleid', styleid, 86400 * 7);
	location.href = location.href;
}

function showPreview(val, id) {
	var showObj = $(id);
	if(showObj) {
		showObj.innerHTML = val.replace(/\n/ig, "<bupdateseccoder />");
	}
}

var secST = new Array();
function updatesecqaa(idhash) {
	if($('secqaa_' + idhash)) {
		$('secqaaverify_' + idhash).value = '';
		if(secST['qaa_' + idhash]) {
			clearTimeout(secST['qaa_' + idhash]);
		}
		$('checksecqaaverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
		ajaxget('misc.php?mod=secqaa&action=update&idhash=' + idhash, 'secqaa_' + idhash, null, '', '', function() {
			secST['qaa_' + idhash] = setTimeout(function() {$('secqaa_' + idhash).innerHTML = '<span style="cursor:pointer" class="xi2" onclick="updatesecqaa(\''+idhash+'\')">刷新验证问答</span>';}, 180000);
		});
	}
}

function updateseccode(idhash, play) {
	if(isUndefined(play)) {
		if($('seccode_' + idhash)) {
			$('seccodeverify_' + idhash).value = '';
			if(secST['code_' + idhash]) {
				clearTimeout(secST['code_' + idhash]);
			}
			$('checkseccodeverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
			ajaxget('misc.php?mod=seccode&action=update&idhash=' + idhash, 'seccode_' + idhash, null, '', '', function() {
				secST['code_' + idhash] = setTimeout(function() {$('seccode_' + idhash).innerHTML = '<span style="cursor:pointer" class="xi2" onclick="updateseccode(\''+idhash+'\')">刷新验证码</span>';}, 180000);
			});
		}
	} else {
		eval('window.document.seccodeplayer_' + idhash + '.SetVariable("isPlay", "1")');
	}
}

function checksec(type, idhash, showmsg, recall) {
	var showmsg = !showmsg ? 0 : showmsg;
	var secverify = $('sec' + type + 'verify_' + idhash).value;
	if(!secverify) {
		return;
	}
	var x = new Ajax('XML', 'checksec' + type + 'verify_' + idhash);
	x.loading = '';
	$('checksec' + type + 'verify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/loading.gif" width="16" height="16" class="vm" />';
	x.get('misc.php?mod=sec' + type + '&action=check&inajax=1&&idhash=' + idhash + '&secverify=' + (BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(secverify) : secverify), function(s){
		var obj = $('checksec' + type + 'verify_' + idhash);
		obj.style.display = '';
		if(s.substr(0, 7) == 'succeed') {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_right.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				recall(1);
			}
		} else {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_error.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				if(type == 'code') {
					showDialog('验证码错误，请重新填写');
				} else if(type == 'qaa') {
					showDialog('验证问答错误，请重新填写');
				}
				recall(0);
			}
		}
	});
}

function createPalette(colorid,id) {
	var iframe = "<iframe name=\"c"+colorid+"_frame\" src=\"\" frameborder=\"0\" width=\"166\" height=\"186\" scrolling=\"no\"></iframe>";
	if (!$("c"+colorid+"_menu")) {
		var dom = document.createElement('span');
		dom.id = "c"+colorid+"_menu";
		dom.style.display = 'none';
		dom.innerHTML = iframe;
		$('append_parent').appendChild(dom);
	}
	window.frames["c"+colorid+"_frame"].location.href = STATICURL+"image/admincp/getcolor.htm?c"+colorid+"|"+id;
	showMenu({'ctrlid':'c'+colorid});
}

function initscmenu() {
	if(BROWSER.ie){
        var tmp = $('sctype_menu');
        if(tmp){
            var a = tmp.getElementsByTagName('label');
            for(var i=0; i<a.length; i++){
                a[i].onmouseover = function(){
                    this.className = 'sca';
                };
                a[i].onmouseout = function(){
                    this.className = '';
                };
            }
        }
	}
}

function orgtreeselect(selectid, name,channel){
	channel = isUndefined(channel) ? 'orgname' : channel;
    $("curorgname").innerHTML = name;
    $(channel+"_input").value = name;
    $(channel+"_input_id").value = selectid;
}

function submiselectorg(frm){
    hideWindow('orgtree');
}

function getArgs( ) {
     var args = new Object( );
     var query = location.search.substring(1);      // Get query string
     var pairs = query.split("&");                  // Break at ampersand
     for(var i = 0; i < pairs.length; i++) {
         var pos = pairs[i].indexOf('=');           // Look for "name=value"
         if (pos == -1) continue;                   // If not found, skip
         var argname = pairs[i].substring(0,pos); // Extract the name
         var value = pairs[i].substring(pos+1);     // Extract the value
         value = decodeURIComponent(value);         // Decode it, if needed
         args[argname] = value;                     // Store as a property
     }
     return args;                                   // Return the object
}

/* Added by Izzln */
function getJSONP(url, callbackName) {
	var script = document.createElement("script");
	script.src = url + "&random=" + Math.random() + "&callback=" + callbackName;
	script.type = "text/javascript";
	script.charset = "utf-8";
	var head = document.getElementsByTagName("head")[0];
	head.appendChild(script);
    
    script.onload = script.onreadystatechange = function() {
    	if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
			setTimeout(function(){
				head.removeChild(script);
			}, 50);
		}
    }
}

/* Added by Izzln */
function scrollads(target, speed) {
    var objlist = document.getElementById(target);
    var objs = objlist.getElementsByTagName("li");
    var objLength = 0;
    var step = 0;
    var size = parseInt(objlist.parentNode.offsetWidth);
    var runSign = true;

    if(speed == "fast") {
        step = 60;
    } else if (speed == "slow") {
        step = 30;
    }
    
    objlist.style.marginLeft = size + "px";
    for (var i = 0; i < objs.length; i++) {
        objLength += objs[i].offsetWidth + parseInt(retrieveComputedStyle(objs[i], "marginLeft")) + parseInt(retrieveComputedStyle(objs[i], "marginRight"));
    }
    objlist.style.width = objLength + "px";
    attachEventListener(objlist, "mouseover", function() {runSign = false;}, false);
    attachEventListener(objlist, "mouseout", function() {runSign = true;}, false);

    scrollOnce();

    function scrollOnce() {
        var s = objLength + size;
        var ml = size;

	    var intervalProcess = setInterval(function() {
				if(runSign) {
		            if (s >= 0) {
		                s -= step / fps;
		                ml -= step / fps;
		                objlist.style.marginLeft = ml + "px";
		            }
		                        
		            else {
		                clearInterval(intervalProcess);
		                objlist.style.marginLeft = size + "px";
		                scrollOnce();
		            }
		         }
	        },
	        Math.round(1000 / fps));       
    }
}
				
/* Added by Izzln */
function doubandiym(dbId) {	
	var dbImg = $(dbId + "Content").getElementsByTagName("img");
	var dbMenu = $(dbId + "Menu").getElementsByTagName("li");
	var dbMenuHeight;
	var selPosi = [0, 0];
	
	dbMenuHeight = Math.round(dbImg[0].height / dbMenu.length);		
	var ratio = dbImg[0].width / dbImg[0].height;

	for(var i = 0; i < dbMenu.length; i++) {
		dbMenu[i].id = dbId + "Menu_" + i;
		dbMenu[i].style.height = dbMenuHeight - 2 * parseInt(retrieveComputedStyle(dbMenu[i], "paddingTop")) + "px";

		attachEventListener(dbMenu[i], "mouseover", function(event) {
			var target;
			getEventTarget(event).id.indexOf("Menu") > -1 ? target = getEventTarget(event) : target = getEventTarget(event).parentNode;
	
			selPosi.reverse();
			selPosi[1] = target.id.replace(dbId+"Menu_", "");
			if(selPosi[1] != selPosi[0]) {
				addClass(dbMenu[selPosi[1]], "on");
				dbImg[selPosi[1]].parentNode.style.display = "block";
				removeClass(dbMenu[selPosi[0]], "on");
				dbImg[selPosi[0]].parentNode.style.display = "none";
			}
		}, false);
	}

	for(var i = 0; i < dbImg.length; i++) {
		var tempHeight = $(dbId + "Menu").offsetHeight;
		
		dbImg[i].height = tempHeight;
		dbImg[i].parentNode.className.indexOf("dbShadow") > -1 ? "" : dbImg[i].width = tempHeight * ratio;
	}
}

/* Added by Izzln */
function initProIndicator(target) {
	for(var i = 0; i < target.childNodes.length; i++) {
		if(target.childNodes[i].id == "allLength") { var allLength = target.childNodes[i]; }
		if(target.childNodes[i].id == "proceedLength") { var proceedLength = target.childNodes[i]; }
		if(target.childNodes[i].id == "nodes") { var nodeContainer = target.childNodes[i]; }
	}
	var nodes = nodeContainer.getElementsByTagName("div");
	if(BROWSER.ie) {
		nodes[nodes.length - 1].style.marginRight = 0;
	}
	nodes[0].className = "actived";
	
	var firstPadding = nodes[0].offsetWidth / 2;
	var marginRight = parseInt(retrieveComputedStyle(nodes[0], "marginRight"));
	var length = 0;
	for(var i = 0; i < nodes.length; i++) {
		length += nodes[i].offsetWidth;
	}
	length += marginRight * (nodes.length - 1);
	target.style.width = length + "px";
	target.style.height = nodes[0].offsetHeight + "px";
	length = length - nodes[0].offsetWidth / 2 - nodes[nodes.length - 1].offsetWidth / 2;
	allLength.style.width = length + "px";
	allLength.style.left = firstPadding + "px";
	proceedLength.style.left = firstPadding + "px";
}

/* Added by Izzln */
function proIndicatorAction(target, backOrForth) {
	for(var i = 0; i < target.childNodes.length; i++) {
		if(target.childNodes[i].id == "proceedLength") { var proceedLength = target.childNodes[i]; }
		if(target.childNodes[i].id == "nodes") { var nodeContainer = target.childNodes[i]; }
	}
	var nodes = nodeContainer.getElementsByTagName("div");
	
	var pointer = 0;
	for(var i = 0; i < nodes.length; i++) {
		if(nodes[i].className.indexOf("actived") != -1) { pointer++; }
	}
	
	if((!backOrForth && pointer > 1) || (backOrForth && pointer < nodes.length)) {
		if(backOrForth) {
			nodes[pointer].className = "actived"	
		} else {
			nodes[pointer - 1].className = "";
			pointer -= 2;
		}
		
		var length = 0;
		for(var i = 0; i <= pointer; i++) {
			length += nodes[i].offsetWidth;
		}
		length += pointer * parseInt(retrieveComputedStyle(nodes[0], "marginRight"));
		length = length - nodes[0].offsetWidth / 2 - nodes[pointer].offsetWidth / 2;
		proceedLength.style.width = length + "px";
	}
}

/* Added by Izzln */
function switchWizard(target, slideNo) {
	var nodes = target.childNodes;
	var slides = [];
	for(var i = 0; i < nodes.length; i++) {
		if(nodes[i].nodeType != "3" && nodes[i].id.indexOf("step") != -1) {
			slides.push(nodes[i]);
		}	
	}
	
	for(var i = 0; i < slides.length;  i++) {
		addClass(slides[i], "off");
	}
	removeClass(slides[slideNo - 1], "off");
}

/* Tab式按钮UI控制 Added by Izzln */
function tabButtonUC(tbgroup, defaultNum) {
	var defaultNum = !isNaN(parseInt(defaultNum)) ? defaultNum : 0;
	var anchors = tbgroup.getElementsByTagName("a");
	if(defaultNum > anchors.length) {
		defaultNum = anchors.length;
	}
	
	for(var i = 0; i < anchors.length; i++) {
		attachEventListener(anchors[i], "click", function(event){
			for(var j = 0;  j < anchors.length; j++) {
				removeClass(anchors[j], "act");
			}
			addClass(getEventTarget(event).parentNode, "act");
		}, false);
	}
	addClass(anchors[defaultNum], "act");
}

/* 用于通知知识中心同步注销 Added by Izzln*/
function exitSync() {
	var exitSync = document.createElement("script");
	exitSync.src = "http://know.myctu.cn/WebRoot/pages/logout.jsp";
	exitSync.type = "text/javascript";
	var head = document.getElementsByTagName("head")[0];
    head.appendChild(exitSync);
	setTimeout(function(){
		window.location.href = "member.php?mod=logging&action=logout&formhash=" + VERHASH;
	}, 200);
}
            
/* Added by Izzln */
function addLoadListener(functionRef) {
    if (!attachEventListener(window, "load", functionRef, false)) {
        document.addEventListener("load", functionRef, false);
    }
}

/* Added by Izzln */
function attachEventListener(target, eventType, functionRef, capture) {
    if (typeof target.addEventListener != "undefined") {
        target.addEventListener(eventType, functionRef, capture);
        return true;
    } else if (typeof target.attachEvent != "undefined") {
        target.attachEvent("on" + eventType, functionRef);
        return true;
    }
    else return false;
}

/* Added by Izzln */
function detachEventListener(target, eventType, functionRef, capture) {
	if (typeof target.removeEventListener != "undefined") {
		target.removeEventListener(eventType, functionRef, capture);
	} else if (typeof target.detachEvent != "undefined") {
		target.detachEvent("on" + eventType, functionRef); }
	else {
		target["on" + eventType] = null;
	}
}

/* Added by Izzln */
function getEventTarget(event)	{
	var targetElement = null;
	
	if (typeof event.target != "undefined")	{
		targetElement = event.target;
	} else {
		targetElement = event.srcElement;
	}
	
	while (targetElement.nodeType == 3 && targetElement.parentNode != null) {
		targetElement = targetElement.parentNode;
	}
	
	return targetElement;
}

/* Added by Izzln */
function retrieveComputedStyle(element, styleProperty) {
    var computedStyle = null;

    if (typeof element.currentStyle != "undefined") {
        computedStyle = element.currentStyle;
    } else {
        computedStyle = document.defaultView.getComputedStyle(element, null);
    }
    return computedStyle[styleProperty];
}

/* Added by Izzln */
function getViewportSize() {
    var size = [0, 0];

    if (typeof window.innerWidth != 'undefined') {
        size = [
        window.innerWidth,
        window.innerHeight
        ];
    } else if (typeof document.documentElement != 'undefined'
        && typeof document.documentElement.clientWidth !=
        'undefined' && document.documentElement.clientWidth != 0) {
        size = [
        document.documentElement.clientWidth,
        document.documentElement.clientHeight
        ];
    } else {
        size = [
        document.getElementsByTagName('body')[0].clientWidth,
        document.getElementsByTagName('body')[0].clientHeight
        ];
    }
    return size;
}

/* Added by Izzln */
function getLeft(e){
		var offset=e.offsetLeft;
		if(e.offsetParent!=null) offset+=getLeft(e.offsetParent);
		return offset;
}

/* Added by Izzln */
function getLastChild(target) {
	return target.lastChild.nodeType == "3" ? target.lastChild.previousSibling : target.lastChild;
}

/* Added by Izzln */
function setOpacity(target, opacityLevel, isWord) {
    if (0 <= opacityLevel <= 100) {
    	target.style.zoom = 1;
        target.style.opacity = opacityLevel / 100;
        if(isWord) { target.style.filter = "alpha(opacity=" + opacityLevel + ")" };
    }
}


/* Added by Izzln */
function fadeIn(target, duration, isWord) {
    setOpacity(target, 0, isWord);
    target.style.display = "block";

    var times = Math.round((duration * fps) / 1000);
    var step = Math.round(100 / times);
    var i = 0;

    var intervalProcess = setInterval(function() {
        setOpacity(target, i, isWord);

        if (i <= 100) {
            i += step;
        }

        else {
            clearInterval(intervalProcess);
            setOpacity(target, 100, isWord);
        }
    },
    Math.round(1000 / fps));

}


/* Added by Izzln */
function fadeOut(target, duration, isWord) {
    var times = Math.round((duration * fps) / 1000);
    var step = Math.round(100 / times);
    var i = 100;

    var intervalProcess = setInterval(function() {
        setOpacity(target, i, isWord);

        if (i >= 0) {
            i -= step;
        }

        else {
            clearInterval(intervalProcess);
            setOpacity(target, 0, isWord);
            target.style.display = "none";
        }
    },
    Math.round(1000 / fps));
}

/* Added by Izzln */
function addClass(target, classValue) {
    var pattern = new RegExp("(^| )" + classValue + "( |$)");
   
    if (!pattern.test(target.className)) {
        if (target.className == "") {
            target.className = classValue;
        } else {
            target.className += " " + classValue;
        }
    }
    
    return true;

}

/* Added by Izzln */
function removeClass(target, classValue) {
	var removedClass = target.className;
	var pattern = new RegExp("(^| )" + classValue + "( |$)");
	removedClass = removedClass.replace(pattern, "$1");
    removedClass = removedClass.replace(/ $/, "");
    target.className = removedClass;
    return true;
}

/* Added by Izzln */
function moveStart(event){
	if (typeof event == "undefined") {
		event = window.event;
	}
	
	if (typeof event.pageX == "undefined") {
		event.pageX = event.clientX + getScrollingPosition()[0];
	}
	
	var target = getEventTarget(event);
	
	while (target.nodeName.toLowerCase() != "div") {
		target = target.parentNode;
	}
	if(target.className.indexOf("timeBoard") > -1) {
		target = target.parentNode.parentNode;
	}
	if(target.className.indexOf("timeSlice") > -1) {
		target = target.parentNode;
	}

	document.currentTarget = target;
	
	var currentLeft = parseInt(target.style.marginLeft);
	
	if (isNaN(currentLeft)) {
		currentLeft = "0";
	}
	
	if (typeof target.originLeft == "undefined") {
		target.originLeft = currentLeft;
	}
	
	target.clickOriginX = event.pageX;
	target.differenceX = currentLeft - event.pageX;

	attachEventListener(document, "mousemove", mousemoveCheckThreshold, false);
	attachEventListener(document, "mouseup", mouseupCancelThreshold, false);
	
	doane(event);
	
	return false;
}

/* Added by Izzln */
function mousemoveCheckThreshold(event) {
	if (typeof event == "undefined") {
		event = window.event;
	}

	if (typeof event.pageX == "undefined") {
		event.pageX = event.clientX + getScrollingPosition()[0];
	}
	
	var target = document.currentTarget;
	if (Math.abs(target.clickOriginX - event.pageX) > 3) {
		detachEventListener(document, "mousemove", mousemoveCheckThreshold, false);
		detachEventListener(document, "mouseup", mouseupCancelThreshold, false);
		attachEventListener(document, "mousemove", moving, false);
		attachEventListener(document, "mouseup", moveStop, false);
		attachEventListener(document, "click", clickDragNDrop, false);
	}

	doane(event);
	return false;
}

/* Added by Izzln */
function mouseupCancelThreshold() {
	detachEventListener(document, "mousemove", mousemoveCheckThreshold, false);
	detachEventListener(document, "mouseup", mouseupCancelThreshold, false);
	return false;
}

/* Added by Izzln */
function clickDragNDrop(event) {
	if (typeof event == "undefined") {
		event = window.event;
	}
	detachEventListener(document, "click", clickDragNDrop, false);
	doane(event);
	return true;
}

/* Added by Izzln */
function moving(event) {
	if (typeof event == "undefined") {
		event = window.event;
	}
	if (typeof event.pageX == "undefined") {
		event.pageX = event.clientX + getScrollingPosition()[0];
	}
	
	var target = document.currentTarget;
	var tempLeft = event.pageX + target.differenceX;
	if(tempLeft > 0) {
		target.style.marginLeft = -1 + "px";
		$("leftHandle").style.display = "none";
	}else if(tempLeft < -($("timeSlices").offsetWidth - $("timeSlicesWrapper").offsetWidth)) {
		target.style.marginLeft = -($("timeSlices").offsetWidth - $("timeSlicesWrapper").offsetWidth) + "px";
		$("rightHandle").style.display = "none";
	}else {
		target.style.marginLeft = tempLeft + "px";
		$("leftHandle").style.display = "block";
		$("rightHandle").style.display = "block";
	}
	 

	doane(event);
	return true;
}

/* Added by Izzln */
function moveStop(event) {
	if (typeof event == "undefined") {
		event = window.event;
	}

	if (typeof event.pageX == "undefined") {
		event.pageX = event.clientX + getScrollingPosition()[0];
	}
	detachEventListener(document, "mousemove", moving, false);
	detachEventListener(document, "mouseup", moveStop, false);
	return true;
}

/* Added by Izzln */	
function getScrollingPosition() {
	var position = [0, 0];
	
	if (typeof window.pageYOffset != 'undefined') {
	    position = [
	        window.pageXOffset,
	        window.pageYOffset
	    ];
		}else if (typeof document.documentElement.scrollTop != 'undefined' && document.documentElement.scrollTop > 0) {
	    position = [
	        document.documentElement.scrollLeft,
	        document.documentElement.scrollTop
	    ];
	}else if (typeof document.body.scrollTop != 'undefined') {
	    position = [
	        document.body.scrollLeft,
	        document.body.scrollTop
	    ];
	}
	return position;
}

function changesort(afrm){
    var nav_order = $("nav_order");
    var nav_order_rule = $("nav_order_rule");
    if(!nav_order || !nav_order_rule){
        return false;
    }
    if(afrm.className.indexOf('pressed')>0){
        afrm.className = "iconbtn y";
        nav_order.style.display = 'none';
        nav_order_rule.style.display = 'none';
    }else{
        afrm.className = "iconbtn y pressed";
        nav_order.style.display = '';
        nav_order_rule.style.display = '';
    }
}

function changecategory(afrm){
    var nav_category = $("nav_category");
    if(!nav_category){
        return false;
    }
    if(afrm.className.indexOf('pressed')>0){
        afrm.className = "iconbtn y";
        nav_category.style.display = 'none';
    }else{
        afrm.className = "iconbtn y pressed";
        nav_category.style.display = '';
    }
}

function selectStation(select, name){
    result = $("station_input_id").value;
    names = $("station_input").value;
    if(select.checked){
        var t = result.split(",");
        var f = true;
        for(var i=0; i<=t.length; i++){
            if(t[i]==select.value){
                f = false;
                break;
            }
        }
        if(f){
            result += select.value+",";
            names += name+",";
        }
    }else{
        result = result.replace(select.value+",", "");
        names = names.replace(name+",", "");
    }
    $("station_input_id").value = result;
    $("station_input").value = names;
}

function submitStation(){
    hideWindow('qrystationwin');
}

function submitselectkcategory(){
    hideWindow('win_kcategory');
}

function selectkcategory(id, name){
    $("curcategroyname").innerHTML = name;
    kc = $("kcategoryid");
    kcn = $("kcategoryname");
    if(kc){
        kc.value = id;
    }
    if(kcn){
        kcn.value = name;
    }
}

function submitselectkorg(){
    hideWindow('win_korg');
}

function selectkorg(id, name){
    $("curorgname").innerHTML = name;
    kc = $("orgname_input_id");
    kcn = $("orgname_input");
    if(kc){
        kc.value = id;
    }
    if(kcn){
        kcn.value = name;
    }
}

//限制输入，中文英文数字和长度：onkeyup=function(){limitDo.call(this,16)}
var len=function(s){ //获取字符串的字节长度
    s=String(s);
    return s.length+(s.match(/[^\x00-\xff]/g) ||"").length; //加上匹配到的全角字符长度
},
limitDo=function(limit){
	this.value=this.value.replace(/[^\w\u4E00-\u9FA5]/g, ''); //限制中文英文数字
    var val=this.value;
    if(len(val)>limit) {
        //val=val.substr(0,limit);
        while(len(val=val.substr(0,val.length-1))>limit);
        this.value=val;
    }
};

/**
 * 将json字符串转换为string : Added by Betty
 */
var obj2str = function(o) {
                if (o == undefined) {
                    return "";
                }
                var r = [];
                if (typeof o == "string") return "\"" + o.replace(/([\"\\])/g, "\\$1").replace(/(\n)/g, "\\n").replace(/(\r)/g, "\\r").replace(/(\t)/g, "\\t") + "\"";
                if (typeof o == "object") {
                    if (!o.sort) {
                        for (var i in o)
                            r.push("\"" + i + "\":" + obj2str(o[i]));
                        if (!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
                            r.push("toString:" + o.toString.toString());
                        }
                        r = "{" + r.join() + "}"
                    } else {
                        for (var i = 0; i < o.length; i++)
                            r.push(obj2str(o[i]))
                        r = "[" + r.join() + "]";
                    }
                    return r;
                }
                return o.toString().replace(/\"\:/g, '":""');
            }
            
function HTMLEncode(input) { 
	var converter = document.createElement("DIV");
		converter .appendChild ( document.createTextNode(input) );
	var output = converter.innerHTML; 
		converter = null; 
 return output; 
} 
function HTMLDecode(input) { 
	var converter = document.createElement("DIV"); 
		converter .appendChild ( document.createTextNode(input) );  
	var output = converter.innerText; 
		converter = null; 
 return output; 
}

/** 
 * js截取字符串，中英文都能用 
 * @param str：需要截取的字符串 
 * @param len: 需要截取的长度 
 */  
function suitStr(str, len){  
	if(!str) return '';
	if(!len) len = 0;
	
	var _length = 0;  
	var _len = 0;  
	var _cut = new String();  
	_len = str.length;  
	
	for(var i = 0; i < _len; i++){  
        a = str.charAt(i);  
        _length++;  
        if(escape(a).length > 4) {  //中文字符的长度经编码之后大于4  
        	_length++;  
        }  
        _cut = _cut.concat(a);  
        if(_length >= len){  
        	_cut = _cut.concat(" ...");  
        	return _cut;  
        }  
    }  
      
    if(_length < len){  
    	return  str;  
    }  
}

/*
 *	SSO登录后，弹出新窗口显示宣传页
 *	有效期为2012.6.12一天
 */
//var _LOGIN = getcookie('usrname');
//var _HASOPEN = getcookie('hasctuopen');
//if (_LOGIN != '' && _HASOPEN != 'has'){
//	var newwin = window.open('http://home.myctu.cn/data/attachment/online/20120608/index.html','ctuonline');
//	newwin.focus();
//	setcookie('hasctuopen','has');
//}