function showstars() {
	var T$ = function(id) { return document.getElementById(id) }
	var T$$ = function(r, t) { return (r || document).getElementsByTagName(t) }
	var Stars = function(cid, rid, hid, config) {
		var lis = T$$(T$(cid), 'li'), curA;
		for (var i = 0, len = lis.length; i < len; i++) {
			lis[i]._val = i;
			lis[i].onclick = function() {
			T$(rid).innerHTML = '<em>' + (T$(hid).value = T$$(this, 'a')[0].getAttribute('star:value')) + '分</em> - ' + config.info[this._val];
			curA = T$$(T$(cid), 'a')[T$(hid).value / config.step - 1];
			};
			lis[i].onmouseout = function() {
				curA && (curA.className += config.curcss);
			}
			lis[i].onmouseover = function() {
				curA && (curA.className = curA.className.replace(config.curcss, ''));
			}
		}
	};
	return {Stars: Stars}
}

for(var j=0;j<4;j++){
	showstars().Stars('wwwzzjs'+j, 'stars2-tips'+j, 'stars2-input'+j, {
		'info' : ['很差', '较差', '还行', '推荐', '力荐'],
		'curcss': ' current-rating',
		'step': 1
		});
}


function class_add(classid) {
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=extraresource&extype=class&inajax=1&classid='+ classid, function(s){
		$("extraclass").innerHTML=$("extraclass").innerHTML+s;
	});
}

function lec_add(lecid) {
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=extraresource&extype=lec&inajax=1&lecid='+ lecid, function(s){
		$("extralec").innerHTML=$("extralec").innerHTML+s;
	});
}

function org_add(orgid) {
	var x = new Ajax();
	x.get('home.php?mod=misc&ac=ajax&op=extraresource&extype=org&inajax=1&orgid='+ orgid, function(s){
		$("extraorg").innerHTML=$("extraorg").innerHTML+s;
	});
}

function showWindow(k, url, mode, cache, cover, menuv) {
	//判断专家cookie
	if(url.indexOf('mod=querystation')==-1&&getcookie('expert_Is') && (url.indexOf('ac=favorite') != -1 || url.indexOf('ac=share') != -1 )) {
		showDialog("非常抱歉，由于您为外部专家，暂无权访问该内容");
		doane();
		return;
	}
	
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	cover = isUndefined(cover) ? 0 : cover;
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
			ajaxget(url, 'fwin_content_' + k, null, '', '', function() {initMenu();show();});
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
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['win'],'drag':typeof drag == null ? '' : drag,'cache':cache, 'cover': cover};
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