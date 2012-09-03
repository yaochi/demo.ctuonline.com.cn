var addCourseID = 0;
var isNetWorkLectStateChanged = false;
var currentSelLv;

function corpLectLevelUC(event) {
	var target = getEventTarget(event);
	var lecType = [0, 0];
	lecType[0] = $("lectype").value.split(",")[0];
	var isNetworkLect = $("lectype").value.indexOf("4") != -1 ? true : false;
		
	if(target.id == "other") {
		lecType[1] = 5;
		if(isNetworkLect) {
			isNetworkLect = false;
			isNetWorkLectStateChanged = true;	
		}
	} else if(target.id == "network") {
		isNetworkLect = !isNetworkLect;
		isNetWorkLectStateChanged = true;
		lecType[1] = lecType[0];
	} else if(target.id.indexOf("clv") != -1) {
		lecType[1] = target.id.replace("clv", "");
	}
	
	if(lecType[1] > lecType[0] || (isNetWorkLectStateChanged && !isNetworkLect)) {
		if(adjustCourse("test", lecType, isNetworkLect)) {
			showDialog("本降级操作将会导致该讲师培训课程等级的变化 是否继续?", "confirm", "", function(){
				adjustCorpLevel(target, isNetworkLect);
				adjustCourse("downgrade", lecType, isNetworkLect);
			});
		} else {
			adjustCorpLevel(target, isNetworkLect);
			adjustCourse("downgrade", lecType, isNetworkLect);
		}
	} else {
		adjustCorpLevel(target, isNetworkLect);
		adjustCourse("upgrade", lecType, isNetworkLect);
	}
}

function adjustCorpLevel(target, isNetworkLect) {
	if(target.id == "other") {
		for(var i = 0; i < lectLevelAnchors.length; i++) {
			removeClass(lectLevelAnchors[i], "act");
		}
		addClass(target, "act");
		lectType = 5;
		isNetworkLect = false;
	} else {
		removeClass($("other"), "act");
		
		if(target.id == "network") {
			if(isNetworkLect) {
				addClass( target, "act");
			} else {
				removeClass(target, "act");
				var tmSign;
				for(var i = 0; i < lectLevelAnchors.length; i++) {
					if(lectLevelAnchors[i].className.indexOf("act") != -1) {
						tmSign = true;
					}
				}
				tmSign ? "" : addClass($("other"), "act");
			}
		}
		
		if(target.id.indexOf("clv") != -1) {
			for(var i = 0; i < lectLevelAnchors.length; i++) {
				if(lectLevelAnchors[i].className.indexOf("clv") != -1) {
					removeClass(lectLevelAnchors[i], "act");				
				}
			}
			lectType = target.className.replace("clv", "");
			addClass(target, "act");
		}
	}
	
	if(lectType == 5) {
		$("lectype").value = isNetworkLect ? 4 : 5;
	} else {
		$("lectype").value = isNetworkLect ? lectType + ",4" : lectType;
	}
}

function adjustCourse(mode, lecType, isNetworkLect) {
	courses = $("deliCourses").getElementsByTagName("tr");
	var courseIntels = {"type":[], "id":[], "cq":[]};
	var pattern = /^add/;
	var tmpCQ, tmpSelection;
	var isDuplicated = false;
	
	for(var i = 0; i < courses.length - 1; i++) {
		tmpCQ = pattern.test(courses[i].id) ? $("addcourseQuali_" + courses[i].id.split("_")[1]).value : $("courseQuali_" + courses[i].id.split("_")[1]).value;
		if(tmpCQ != 5 && tmpCQ != 4) {
			pattern.test(courses[i].id) ? courseIntels.type.push(1) : courseIntels.type.push(0);
			courseIntels.id.push(courses[i].id.split("_")[1]);
			courseIntels.cq.push(tmpCQ);
		}
	}
	
	if(mode == "test") {
		var k = 5;
		if(lecType[0] == 1) {
			k = 0;
		} else if(lecType[0] == 2) {
			k = 1;
		} else if(lecType[0] == 3) {
			k = 2;
		}
		for(var i = 0; i < courseIntels.id.length; i++) {
			if(k == courseIntels.cq[i] || (isNetWorkLectStateChanged && !isNetworkLect && courseIntels.cq[i] == 3)) {
				return 1;
			}
		}
		return 0;
	} else if(mode == "upgrade") {
		for(var i = 0; i < courseIntels.id.length; i++) {
			if(lecType[1] == 1) {
				courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]).style.display = "none" : $("courseQuali_" + courseIntels.id[i]).style.display = "none";
			} else {
				tmpSelection = courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]) : $("courseQuali_" + courseIntels.id[i]);
				if (lecType[1] == 2) {
					for(var j = 0; j < tmpSelection.options.length; j++) {
						if(tmpSelection.options[j].value == 1) {
							isDuplicated = true;
						}
					}
					!isDuplicated ? tmpSelection.options.add(new Option("认证", 1)) : isDuplicated = false;
				} else if(lecType[1] == 2 || lecType[1] == 3) {
					for(var j = 0; j < tmpSelection.options.length; j++) {
						if(tmpSelection.options[j].value == 2) {
							isDuplicated = true;
						}
					}
					!isDuplicated ? tmpSelection.options.add(new Option("授权", 2)) : isDuplicated = false;
				}
				if(isNetworkLect) {
					for(var j = 0; j < tmpSelection.options.length; j++) {
						if(tmpSelection.options[j].value == 3) {
							isDuplicated = true;
						}
					}
					!isDuplicated ? tmpSelection.options.add(new Option("网络", 3)) : isDuplicated = false;
				}
			}
		}
	} else if(mode == "downgrade") {
		var k = parseInt(lecType[0]);
		//alert(lecType);

		while(k != parseInt(lecType[1])) {
			if(k == 1) {
				for(var i = 0; i < courseIntels.id.length; i++) {
					if(courseIntels.cq[i] == 0) {
						tmpSelection = courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]) : $("courseQuali_" + courseIntels.id[i]);
						tmpSelection.style.display = "block";
						while(tmpSelection.options.length) {
		               		tmpSelection.options.remove(0);
		           		}
						var newOption1 = new Option("授权", 2);
						var newOption2 = new Option("认证", 1);
						tmpSelection.options.add(newOption1);
						tmpSelection.options.add(newOption2);
						if(isNetworkLect) {
							var newOption3 = new Option("网络", 3);
							tmpSelection.options.add(newOption3);
						}
						tmpSelection.options[0].selected = true;
						if(!courseIntels.type[i]) {
							$("dataSource_" + courseIntels.id[i]).innerHTML = "手工";
							$("delicn_" + courseIntels.id[i]).style.display = "block";
							$("updateTime_" + courseIntels.id[i]).innerHTML = "";
							setModifiedCourseValue(courseIntels.id[i]);
						}
					}
				}
				k += 1;
				//alert("k=1:" + k);
			} else if(k == 2) {
				for(var i = 0; i < courseIntels.id.length; i++) {
					if(0 < courseIntels.cq[i] < 4) {
						tmpSelection = courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]) : $("courseQuali_" + courseIntels.id[i]);
						for(var j = 0; j < tmpSelection.options.length; j++) {
							if(courseIntels.cq[i] == 1 && tmpSelection.options[j].value == 2) {
									tmpSelection.options[j].selected = true;
							}
							if(tmpSelection.options[j].value == 1) {
								tmpSelection.options.remove(j);
							}
						}
						if(!courseIntels.type[i]) {
							$("dataSource_" + courseIntels.id[i]).innerHTML = "手工";
							$("delicn_" + courseIntels.id[i]).style.display = "block";
							$("updateTime_" + courseIntels.id[i]).innerHTML = "";
							setModifiedCourseValue(courseIntels.id[i]);
						}
					}
				}
				k += 1;
				//alert("k=2:" + k);
			} else if(k == 3) {
				for(var i = 0; i < courseIntels.id.length; i++) {
					if(courseIntels.cq[i] == 2) {
						courseIntels.type[i] ? removeClass($("addCourse_" + courseIntels.id[i]), "collCourse") : removeClass($("course_" + courseIntels.id[i]), "collCourse");
						tmpSelection = courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]) : $("courseQuali_" + courseIntels.id[i]);
						tmpSelection.style.display = "none";
						while(tmpSelection.options.length) {
		               		tmpSelection.options.remove(0);
		           		}
						var newOption = new Option("无", 5);
						tmpSelection.options.add(newOption);
						tmpSelection.options[0].selected = true;
						courseIntels.type[i] ? "" : setModifiedCourseValue(courseIntels.id[i]);
					}
				}
				k += 2;
				//alert("k=3:" + k);
			}
		}
		if(isNetWorkLectStateChanged && !isNetworkLect) {
			for(var i = 0; i < courseIntels.id.length; i++) {
				tmpSelection = courseIntels.type[i] ? $("addcourseQuali_" + courseIntels.id[i]) : $("courseQuali_" + courseIntels.id[i]);
				
				if(courseIntels.cq[i] == 3) {
					courseIntels.type[i] ? removeClass($("addCourse_" + courseIntels.id[i]), "collCourse") : removeClass($("course_" + courseIntels.id[i]), "collCourse");
					tmpSelection.style.display = "none";
					while(tmpSelection.options.length) {
	               		tmpSelection.options.remove(0);
	           		}
					var newOption = new Option("无", 5);
					newOption.selected = true;
					tmpSelection.options.add(newOption);
					courseIntels.type[i] ? "" : setModifiedCourseValue(courseIntels.id[i]);
				} else {
					for(var j = 0; j < tmpSelection.options.length; j++) {
						if(tmpSelection.options[j].value == 3) {
							tmpSelection.options.remove(j);
						}
					}
				}
			}
		}
	}
	isNetWorkLectStateChanged = false;
}

function proLectLevelsUC(event) {
	var target = getEventTarget(event);
	currentSelLv = target.id.replace("prolv", "");

	if(target.selectedIndex != -1) {
		$("delPlv" + currentSelLv).style.display != "inline-block" ? $("delPlv" + currentSelLv).style.display = "inline-block" : "";
		$("renPlv" + currentSelLv).style.display != "inline-block" ? $("renPlv" + currentSelLv).style.display = "inline-block" : "";
		var selname = $("prolv" + currentSelLv).value;
		$("lectype").value = selname;
		
		if(currentSelLv < proLectLevels.length) {
			currentSelLv++;
			$("addPlv" + currentSelLv).style.display != "inline-block" ? $("addPlv" + currentSelLv).style.display = "inline-block" : "";
			addClass($("prolv" + currentSelLv), "loading");
			$("prolv" + currentSelLv).name = selname;
			for(var i = currentSelLv; i <= proLectLevels.length; i++) {
				while($("prolv" + i).options.length) {
               		$("prolv" + i).options.remove(0);
           		}
           		$("delPlv" + i).style.display = "none";
				$("renPlv" + i).style.display = "none";
			}
			for(var i = parseInt(currentSelLv)+1; i <= proLectLevels.length; i++) {
				$("addPlv" + i).style.display = "none";
			}
			
			var callurl = "forum.php?mod=group" + "&" + "action=plugin&fid=197&plugin_name=lecturermanage&plugin_op=createmenu&lecturermanage_action=sublevel&par=" + selname;
			getJSONP(callurl, "proLectLevelsUpdateUC");
		}
	}
}

function proLectLevelsUpdateUC(data) {
	if(data.s != null) {
		for(var i = 0; i < data.s.length; i++) {
			var tmpOption = new Option(data.s[i].levelname, data.s[i].id);
			$("prolv" + currentSelLv).options.add(tmpOption);
		}	
	}
	removeClass($("prolv" + currentSelLv), "loading");
}

function proLectLevelsAddUC(event) {
	var target = getEventTarget(event);
	currentSelLv = target.id.replace("addPlv", "");
	showWindow('proLectLv', 'forum.php?mod=group&action=plugin&fid=197&plugin_name=lecturermanage&plugin_op=createmenu&lecturermanage_action=operate&oper=addlevel');
}

function proLectLevelsDelUC(event) {
	var target = getEventTarget(event);
	currentSelLv = target.id.replace("delPlv", "");
	var lvID = $("prolv" + currentSelLv).value;
	
	showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> 请稍候...');
	var callurl = "forum.php?mod=group" + "&" + "action=plugin&fid=197&plugin_name=lecturermanage&plugin_op=createmenu&lecturermanage_action=del_level&levelid=" + lvID;
	getJSONP(callurl, "proLevelDel");
}

function proLevelDel(data) {
	if(data.s) {
		hideMenu('fwin_dialog', 'dialog');
		for(var i = parseInt(currentSelLv)+1; i <= proLectLevels.length; i++) {
			while($("prolv" + i).options.length) {
            	$("prolv" + i).options.remove(0);
          	}
			$("addPlv" + i).style.display = "none";
		}
		for(var i = currentSelLv; i <= proLectLevels.length; i++) {
			$("delPlv" + i).style.display = "none";
			$("renPlv" + i).style.display = "none";
		}
	
		$("prolv" + currentSelLv).options.remove($("prolv" + currentSelLv).selectedIndex);
	} else {
		showDialog("由于此级别下存在讲师，故无法被删除", "notice");
	}
}

function proLectLevelsRenUC(event) {
	var target = getEventTarget(event);
	currentSelLv = target.id.replace("renPlv", "");
	$("prolv" + currentSelLv).disabled = true;
	if($("levelname") != null) {
		$("levelname").value = $("prolv" + currentSelLv).options[$("prolv" + currentSelLv).selectedIndex].text;
	}
	showWindow('proLectLv', 'forum.php?mod=group&action=plugin&fid=197&plugin_name=lecturermanage&plugin_op=createmenu&lecturermanage_action=operate&oper=changelevel');
}

function addCourse(courseTable, isinnerlec) {
	var adminType = $("admintype").value;
	var isinnerlec = isinnerlec == 1 ? true : false;
	var lecType;
	var isNetworkLect;
	if(adminType == 1 && isinnerlec) {
		lecType = $("lectype").value.split(",")[0];
		isNetworkLect = $("lectype").value.indexOf("4") != -1 ? true : false;
	}
	var courseName = "";
	var isInCorpCourseList = false;
	if(adminType == 1 && isinnerlec && lecType != 5) {
		isInCorpCourseList = $("courseTypeA").checked;
		if(isInCorpCourseList) {
			courseName = $("corpCourseList").value;
		} else {
			courseName = $("otherCourseText").value.replace(/(^\s*)|(\s*$)/g, "");
		}
		$("otherCourseText").value = "";
		$('otherCourseLabel').style.display = '';
	}
	
	var newCourse = courseTable.tBodies[0].insertRow(courseTable.tBodies[0].rows.length - 1);
	newCourse.id = "addCourse_" + addCourseID;
	if(adminType == 1 && isinnerlec && lecType != 5 ) { addClass(newCourse, "collCourse"); }
	
	newTD(newCourse, "icn").innerHTML = newStdIcn();
	newTD(newCourse, "courseName").innerHTML = newCourseNameField(courseName, addCourseID, isInCorpCourseList);
	newTD(newCourse, "courseQuali").innerHTML = newCourseQualiSect(adminType, isinnerlec, lecType, addCourseID, isNetworkLect);
	if(adminType > 0 ) {
		newTD(newCourse, "dataSource", addCourseID).innerHTML = "手工";
	} else {
		newTD(newCourse, "dataSource", addCourseID);
	}
	newTD(newCourse, "updateTime", addCourseID);
	newTD(newCourse, "delicnCol").innerHTML = newDelIcn(courseTable, addCourseID);
	
	$("addedcourses").value == "" ? $("addedcourses").value = addCourseID : $("addedcourses").value += "," + addCourseID;
	addCourseID++;
}

function delCourse(courseTable, courseID) {
	var courseinfo = courseID.split("_");
	var para;
	if(courseinfo[0] == "addCourse") {
		para = getAddCourseValue().split(",");
		for(var i = 0; i < para.length; i++) {
			if(courseinfo[1] == para[i]) {
				para.splice(i, 1);
			}
		}
		$("addedcourses").value = para.toString();
	} else {
		para = getModifiedCourseValue().split(",");
		for(var i = 0; i < para.length; i++) {
			if(courseinfo[1] == para[i]) {
				para.splice(i, 1);
			}
		}
		$("modifiedcourses").value = para.toString();
		$("deletedcourses").value == "" ? $("deletedcourses").value = courseinfo[1] : $("deletedcourses").value += "," + courseinfo[1];
	}
	
	fadeOut($(courseID), 200, 1);
	setTimeout("$('" + courseID + "').parentNode.removeChild($('" + courseID + "'))", 200);
}

function modiCourse(event) {
	var target = getEventTarget(event);
	var courseinfo = target.id.split("_");
	var adminType = $("admintype").value;
	if(adminType == 1) {
		if(target.nodeName.toLowerCase() == "select") {
			$("dataSource_" + courseinfo[1]).innerHTML = "手工";
			$("delicn_" + courseinfo[1]).style.display = "block";
		}
		$("updateTime_" + courseinfo[1]).innerHTML = "";
	}
	setModifiedCourseValue(courseinfo[1]);
}

function newTD(row, className, id) {
	var newtd = row.insertCell(-1);
	if(!isUndefined(className)) {
		newtd.className = className;
	}
	if(!isUndefined(id)) {
		newtd.id = "addcourse" + className + "_" + id;
	}
	return newtd;
}

function newStdIcn() {
	return "<img src='source/plugin/lecturermanage/assets/image/stdicn.gif' width='16' height='16' title='集团标准课程' />";
}

function newCourseNameField(value, id, isInCorpCourseList) {
	var tmpInput = "<input id='addcourseName_" + id + "' " + "name='addcourseName_" + id + "' " + "value='" + value + "' class='px' style='height: 16px; width: 200px; font-size: 12px;' ";
	return isInCorpCourseList ? tmpInput +  "type='hidden' />" + value : tmpInput + "type='text' />";
}

function newCourseQualiSect(adminType, isinnerlec, lecType, id, isNetworkLect) {
	if(adminType == 1 && isinnerlec && lecType != 5) {
		var selection = "<select name='addcourseQuali_" + id + "' " + "id='addcourseQuali_" + id + "' class='ps'";
		if(lecType == 1) {
			selection += " style='display: none;'><option value='0' selected='selected'>集团级</option>";
		} else if(lecType <= 3) {
			selection += "><option value='2' selected='selected'>授权</option>";	
		}
		if(lecType == 2) {
			selection += "><option value='1'>认证</option>"
		}
		if(isNetworkLect) {
			selection += "><option value='3'>网络</option>"
		}
		selection += "</select>";
	} else if(adminType == 2 && isinnerlec) {
		var selection = "<select name='addcourseQuali_" + id + "' " + "id='addcourseQuali_" + id + "' class='ps'><option value='4' selected='selected'>省级</option></select>";
	} else {
		var selection = "<select name='addcourseQuali_" + id + "' " + "id='addcourseQuali_" + id + "' class='ps' style='display: none;'><option value='5' selected='selected'>无</option></select>";
	}
	
	return selection;
}

function newDelIcn(courseTable, id) {
	return "<a href=\"javascript:delCourse($('" + courseTable.id + "'), 'addCourse_"+ id +"');\" id='addDelicn_" + id + "' class='delicn y' title='删除此课程'></a>";
}

function getAddCourseValue() {
	return $("addedcourses").value;
}

function getDeletedCourseValue() {
	return $("deletedcourses").value;	
}

function getModifiedCourseValue() {
	return $("modifiedcourses").value;	
}

function setModifiedCourseValue(id) {
	var para = getModifiedCourseValue().split(",");
	var isDuplicated = false;
	for(var i = 0; i < para.length; i++) {
		if(id == para[i]) {
			isDuplicated = true;
		}
	}
	if(!isDuplicated) {
		$("modifiedcourses").value == "" ? $("modifiedcourses").value = id : $("modifiedcourses").value += "," + id;
	}
}

function clearname() {
	$('firstman_names').value='';
	$('firstman_names_uids').value='';
}

function confirmdelete() {
	var msg = "确定删除吗?";
	if(confirm(msg)) {
		return true;
	} else {
		return false;
	}
}

function trimWS(input) {
	input.value = input.value.replace(/(^\s*)|(\s*$)/g, "");
}