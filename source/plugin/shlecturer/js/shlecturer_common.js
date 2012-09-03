/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_moderate.js 8806 2010-04-23 01:40:18Z monkey $
*/

function confirmdelete() {
	var msg = "确定删除吗?";
	if(confirm(msg)) {
		return true;
	} else {
		return false;
	}
}

function addteachdirectionoption() {
		if(curoptions < 7) {
			$('teachdirection_new').outerHTML = '<p>' + $('teachdirection_hidden').innerHTML + '</p>' + $('teachdirection_new').outerHTML;
			curoptions++;
		}
	}

function delteachdirectionoption(obj) {
	obj.parentNode.parentNode.removeChild(obj.parentNode);
	curoptions--;
}