/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_moderate.js 8806 2010-04-23 01:40:18Z monkey $
*/

function block_get_setting_array(classname, script, fid, param) {
	var x = new Ajax();
	
	x.get('portal.php?mod=portalcp&ac=block&op=setting&classname='+classname+'&script='+script+'&inajax=1'+"&fid="+fid+"&param="+param, function(s){
		ajaxinnerhtml($('tbody_setting'), s);
	});
}

function modaction(action, pid, extra) {
	if(!action) {
		return;
	}
	var extra = !extra ? '' : '&' + extra;
	if(!pid && in_array(action, ['delpost', 'banpost'])) {
		var checked = 0;
		var pid = '';
		for(var i = 0; i < $('modactions').elements.length; i++) {
			if($('modactions').elements[i].name.match('topiclist')) {
				checked = 1;
				break;
			}
		}
	} else {
		var checked = 1;
	}
	if(!checked) {
		alert('请选择需要操作的讲师');
	} else {
		$('modactions').action = 'forum.php?mod=topicadmin&action='+ action +'&fid=' + fid + '&tid=' + tid + '&infloat=yes&nopost=yes' + (!pid ? '' : '&topiclist[]=' + pid) + extra + '&r' + Math.random();
		showWindow('mods', 'modactions', 'post');
		if(BROWSER.ie) {
			doane(event);
		}
		hideMenu();
	}
}

function modlecturers(optgroup, operation) {
	var operation = !operation ? '' : operation;
	$('modactions').action = 'forum.php?mod=topicadmin&action=moderate&fid=' + fid + '&moderate[]=' + tid + '&infloat=yes&nopost=yes' + (optgroup != 3 && optgroup != 2 ? '&from=' + tid : '');
	$('modactions').optgroup.value = optgroup;
	$('modactions').operation.value = operation;
	hideWindow('mods');
	showWindow('mods', 'modactions', 'post', 0);
	if(BROWSER.ie) {
		doane(event);
	}
}

function pidchecked(obj) {
	if(obj.checked) {
		if(BROWSER.ie && !BROWSER.opera) {
			var inp = document.createElement('<input name="topiclist[]" />');
		} else {
			var inp = document.createElement('input');
			inp.name = 'topiclist[]';
		}
		inp.id = 'topiclist_' + obj.value;
		inp.value = obj.value;
		inp.style.display = 'none';
		$('modactions').appendChild(inp);
	} else {
		$('modactions').removeChild($('topiclist_' + obj.value));
	}
}

var modclickcount = 0;
function modclick(obj, pid) {
	if(obj.checked) {
		modclickcount++;
	} else {
		modclickcount--;
	}
	$('mdct').innerHTML = modclickcount;
	if(modclickcount > 0) {
		var offset = fetchOffset(obj);
		$('mdly').style.top = offset['top'] - 65 + 'px';
		$('mdly').style.left = offset['left'] - 215 + 'px';
		$('mdly').style.display = '';
	} else {
		$('mdly').style.display = 'none';
	}
}

function resetmodcount() {
	modclickcount = 0;
	$('mdly').style.display = 'none';
}

function tmodclick(obj) {
	if(obj.checked) {
		modclickcount++;
	} else {
		modclickcount--;
	}
	$('mdct').innerHTML = modclickcount;
	if(modclickcount > 0) {
		var top_offset = obj.offsetTop;
		while((obj = obj.offsetParent).id != 'lecturerlist') {
			top_offset += obj.offsetTop;
		}
		$('mdly').style.top = top_offset - 7 + 'px';
		$('mdly').style.display = '';
	} else {
		$('mdly').style.display = 'none';
	}
}

function tmodlecturers(optgroup, operation) {
	var checked = 0;
	var operation = !operation ? '' : operation;
	for(var i = 0; i < $('moderate').elements.length; i++) {
		if($('moderate').elements[i].name.match('moderate') && $('moderate').elements[i].checked) {
			checked = 1;
			break;
		}
	}
	if(!checked) {
		alert('请选择需要操作的讲师');
	} else {
		$('moderate').optgroup.value = optgroup;
		$('moderate').operation.value = operation;
		showlecturerWindow('mods', 'moderate', 'post');
	}
}

function showlecturerWindow(k, url, mode, cache, menuv) {
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

loadcss('forum_moderator');