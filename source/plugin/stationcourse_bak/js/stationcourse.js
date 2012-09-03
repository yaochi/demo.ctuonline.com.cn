/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_moderate.js 8806 2010-04-23 01:40:18Z monkey $
*/



function change(id){
	var status=document.getElementById(id).style.display;
	if(status=='none')
	document.getElementById(id).style.display = "";
	if(!status) document.getElementById(id).style.display = "none";
}

function changetoedit(one,two){
	document.getElementById(one).style.display = "none";
	document.getElementById(two).style.display = "";
}

function selectstation(selectid, name,type){
    $("curorgname").innerHTML = name;
    if(type==0){
    $("orgname_input").value = name;
    $("orgname_input_id").value = selectid;
    }
    else if(type==1){
	$("station_input").value = name;
	$("station_input_id").value = selectid;
	}
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

