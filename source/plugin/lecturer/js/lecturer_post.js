/*
	[Discuz!] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum_moderate.js 8806 2010-04-23 01:40:18Z monkey $
 */

function validate(theform) {
	var boxes = document.getElementsByName("lecturercheckbox[]");
	var check = "";
	var checked = false;
	for ( var i = 0; i < boxes.length; i++) {
		if (boxes[i].checked == true) {
			checked = true;
			break;
		}
	}
	if (!checked) {
		alert("请至少选择一个讲师");
		return false;
	}
}
