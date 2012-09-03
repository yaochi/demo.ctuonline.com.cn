/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admincp.js 11592 2010-06-09 00:13:59Z monkey $
*/

function redirect(url) {
	window.location.replace(url);
}

function scrollTopBody() {
	return Math.max(document.documentElement.scrollTop, document.body.scrollTop);
}

function checkAll(type, form, value, checkall, changestyle) {
	var checkall = checkall ? checkall : 'chkall';
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(type == 'option' && e.type == 'radio' && e.value == value && e.disabled != true) {
			e.checked = true;
		} else if(type == 'value' && e.type == 'checkbox' && e.getAttribute('chkvalue') == value) {
			e.checked = form.elements[checkall].checked;
			if(changestyle) {
				multiupdate(e);
			}
		} else if(type == 'prefix' && e.name && e.name != checkall && (!value || (value && e.name.match(value)))) {
			e.checked = form.elements[checkall].checked;
			if(changestyle && e.parentNode && e.parentNode.tagName.toLowerCase() == 'li') {
				e.parentNode.className = e.checked ? 'checked' : '';
			}
		}
	}
}

function altStyle(obj) {
	function altStyleClear(obj) {
		var input, lis, i;
		lis = obj.parentNode.getElementsByTagName('li');
		for(i=0; i < lis.length; i++){
			lis[i].className = '';
		}
	}

	var input, lis, i, cc, o;
	cc = 0;
	lis = obj.getElementsByTagName('li');
	for(i=0; i < lis.length; i++){
		lis[i].onclick = function(e) {
			o = BROWSER.ie ? event.srcElement.tagName : e.target.tagName;
			altKey = BROWSER.ie ? window.event.altKey : e.altKey;
			if(cc) {
				return;
			}
			cc = 1;
			input = this.getElementsByTagName('input')[0];
			if(input.getAttribute('type') == 'checkbox' || input.getAttribute('type') == 'radio') {
				if(input.getAttribute('type') == 'radio') {
					altStyleClear(this);
				}

				if(BROWSER.ie || o != 'INPUT' && input.onclick) {
					input.click();
				}
				if(this.className != 'checked') {
					this.className = 'checked';
					input.checked = true;
				} else {
					this.className = '';
					input.checked = false;
				}
				if(altKey && input.name.match(/^multinew\[\d+\]/)) {
					miid = input.id.split('|');
					mi = 0;
					while($(miid[0] + '|' + mi)) {
						$(miid[0] + '|' + mi).checked = input.checked;
						if(input.getAttribute('type') == 'radio') {
							altStyleClear($(miid[0] + '|' + mi).parentNode);
						}
						$(miid[0] + '|' + mi).parentNode.className = input.checked ? 'checked' : '';
						mi++;
					}
				}
			}
		};
		lis[i].onmouseup = function(e) {
			cc = 0;
		}
	}
}

var addrowdirect = 0;
function addrow(obj, type) {
	var table = obj.parentNode.parentNode.parentNode.parentNode;
	if(!addrowdirect) {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex);
	} else {
		var row = table.insertRow(obj.parentNode.parentNode.parentNode.rowIndex + 1);
	}
	var typedata = rowtypedata[type];
	for(var i = 0; i <= typedata.length - 1; i++) {
		var cell = row.insertCell(i);
		cell.colSpan = typedata[i][0];
		var tmp = typedata[i][1];
		if(typedata[i][2]) {
			cell.className = typedata[i][2];
		}
		tmp = tmp.replace(/\{(\d+)\}/g, function($1, $2) {return addrow.arguments[parseInt($2) + 1];});
		cell.innerHTML = tmp;
	}
	addrowdirect = 0;
}

function dropmenu(obj){
	showMenu({'ctrlid':obj.id, 'menuid':obj.id + 'child', 'evt':'mouseover'});
	$(obj.id + 'child').style.top = (parseInt($(obj.id + 'child').style.top) - Math.max(document.body.scrollTop, document.documentElement.scrollTop)) + 'px';
	if(BROWSER.ie > 6 || !BROWSER.ie) {
		$(obj.id + 'child').style.left = (parseInt($(obj.id + 'child').style.left) - Math.max(document.body.scrollLeft, document.documentElement.scrollLeft)) + 'px';
	}
}

function textareasize(obj, op) {
	if(!op) {
		if(obj.scrollHeight > 70) {
			obj.style.height = (obj.scrollHeight < 300 ? obj.scrollHeight : 300) + 'px';
			if(obj.style.position == 'absolute') {
				obj.parentNode.style.height = obj.style.height;
			}
		}
	} else {
		if(obj.style.position == 'absolute') {
			obj.style.position = '';
			obj.style.width = '';
			obj.parentNode.style.height = '';
		} else {
			obj.parentNode.style.height = obj.parentNode.offsetHeight + 'px';
			obj.style.width = BROWSER.ie > 6 || !BROWSER.ie ? '90%' : '600px';
			obj.style.position = 'absolute';
		}
	}
}

function showanchor(obj) {
	var navs = $('submenu').getElementsByTagName('li');
	for(var i = 0; i < navs.length; i++) {
		if(navs[i].id.substr(0, 4) == 'nav_' && navs[i].id != obj.id) {
			if($(navs[i].id.substr(4))) {
				navs[i].className = '';
				$(navs[i].id.substr(4)).style.display = 'none';
				if($(navs[i].id.substr(4) + '_tips')) $(navs[i].id.substr(4) + '_tips').style.display = 'none';
			}
		}
	}
	obj.className = 'current';
	currentAnchor = obj.id.substr(4);
	$(currentAnchor).style.display = '';
	if($(currentAnchor + '_tips')) $(currentAnchor + '_tips').style.display = '';
	if($(currentAnchor + 'form')) {
		$(currentAnchor + 'form').anchor.value = currentAnchor;
	} else if($('cpform')) {
		$('cpform').anchor.value = currentAnchor;
	}
}

function updatecolorpreview(obj) {
	$(obj).style.background = $(obj + '_v').value;
}

function entersubmit(e, name) {
	var e = e ? e : event;
	if(e.keyCode != 13) {
		return;
	}
	var tag = BROWSER.ie ? e.srcElement.tagName : e.target.tagName;
	if(tag != 'TEXTAREA') {
		doane(e);
		if($('submit_' + name).offsetWidth) {
			$('formscrolltop').value = document.documentElement.scrollTop;
			$('submit_' + name).click();
		}
	}
}

function parsetag(tag) {
	var str = document.body.innerHTML.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3) {
		if(tag && $3.indexOf(tag) != -1) {
			$3 = $3.replace(tag, '<h_>');
		}
		return $2 + $3;
		});
	document.body.innerHTML = str.replace(/<h_>/ig, function($1, $2) {
		return '<font class="highlight">' + tag + '</font>';
		});
}

function sdisplay(id, obj) {
	obj.innerHTML = $(id).style.display == 'none' ? '<img src="static/image/admincp/desc.gif" style="vertical-align:middle" />' : '<img src="static/image/admincp/add.gif" style="vertical-align:middle" />'
	display(id);
}

if(ISFRAME) {
	try {
		_attachEvent(document.documentElement, 'keydown', parent.resetEscAndF5);
	} catch(e) {}
}

var multiids = new Array();
function multiupdate(obj) {
	v = obj.value;
	if(obj.checked) {
		multiids[v] = v;
	} else {
		multiids[v] = null;
	}
}

function getmultiids() {
	var ids = '', comma = '';
	for(i in multiids) {
		if(multiids[i] != null) {
			ids += comma + multiids[i];
			comma = ',';
		}
	}
	return ids;
}

function orgtreechange(frm){
    if(frm.value==1){
        $("selectorgtree").style.display='';
        $("station_input_tr").style.display="none"
    }else if(frm.value==20){
        $("selectorgtree").style.display='none';
        $("station_input_tr").style.display="";
    }else{
        hideWindow('orgtree');
        $("selectorgtree").style.display='none';
        $("orgname_input_id").value = "";
    }
}

function displayrow(f, c1, c2){
	var o = f;
	var rows = [];
	while (o.tagName.toUpperCase() != 'TR') o = o.parentNode;
	if (o.className == c1){
		if (f.className == 'addtr') {
			f.className = 'minustr';
			while (o.nextSibling && (inarray(c2, o.nextSibling.className) != -1 || o.nextSibling.nodeName == '#text')){
				o = o.nextSibling;
				if(o.className == c2[0]) {
					o.style.display = '';
					rows.push(o);
				}
			}
		}else{
			f.className = 'addtr';
			while (o.nextSibling && (inarray(c2, o.nextSibling.className) != -1 || o.nextSibling.nodeName == '#text')){
				o = o.nextSibling;
				if (o.nodeName.toUpperCase() == 'TR'){
					o.style.display = 'none';
					walkTheDom(o, function(dEl){
						if(dEl.className == 'minustr') dEl.className = 'addtr';
					});
				}
			}
		}
	}
	return rows;
}

function inarray(a, v) {
   var i;
   for (i = 0; i < a.length; i++) {
     if (v === a[i]) {
       return i;
     }
   }
   return -1;
} 
 
function walkTheDom(node, func) {
	func(node);
	node = node.firstChild;
	while(node){
		walkTheDom(node, func);
		node = node.nextSibling;
	};
}

function getData(dataSource, callback){
	var XMLHttpRequestObject=false;
	if (window.XMLHttpRequest) {
		XMLHttpRequestObject = new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	if (XMLHttpRequestObject) {
		XMLHttpRequestObject.open("GET", dataSource);
		XMLHttpRequestObject.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		XMLHttpRequestObject.onreadystatechange = function() {
			if (XMLHttpRequestObject.readyState==4 && XMLHttpRequestObject.status==200) {
				callback(XMLHttpRequestObject.responseText);
			}
		}
		XMLHttpRequestObject.send(null);
	}
}