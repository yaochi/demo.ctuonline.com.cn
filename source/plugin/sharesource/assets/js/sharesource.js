function validate(){
	var arr=new Array("contactor","contactway","province","dept","coursename","trainobj","lecturer","lecturerinfo","start","advantage","defect");
	var mes=new Array("分享人信息","分享人联系方式","省份","本地网","课程名称","培训对象","讲师姓名","讲师简介","开始日期","课程优势","课程局限");
	for(var em in arr){
		if(!checkById_focus(arr[em],1)){
		alert(mes[em]+"不能为空");
		return false;
		}
	}
	if(!checkcategory()) {
	alert("没有选择课程分类");
	return false;
	}
	if(!checktime()) {
	alert("课程时长未填写或错误");
	return false;
	}
	if(!checkdegree()) {
	alert("满意度评估未填写或错误");
	return false;
	}
	if(!checkexp()) {
	alert("体会和建议未填写或长度小于5");
	return false;
	}

	return true;
}

function checkcategory(){//检查课程分类
	var value=document.getElementById("category").value;
	if(value==0){
		document.getElementById("category").focus();
		return false;
	}
	return true;
}

function checktime(){//检查课程时长
	var value=document.getElementById("time").value;
	if(value<=0||value>1000){
		document.getElementById("time").focus();
		return false;
	}
	return true;
}

function checkdegree(){//检查满意度评估
	var value=document.getElementById("degree").value;
	if(!value||value<0||value>5){
		document.getElementById("degree").focus();
		return false;
	}
	return true;
}

function checkexp(){//检查体会和建议
	var value=document.getElementById("exp").value;
	if(trim(value).length<5){
		document.getElementById("exp").focus();
		return false;
	}
	return true;
}

function checkById_focus(id,type){//type:1-文本，2-下拉列表
	if(!checkById(id,type)){
		document.getElementById(id).focus();
		return false;
	}
	else return true;
}

function checkById(id,type){//type:1-文本，2-下拉列表
	var bool;
	if(type==1){
		bool=checkText(id);
	}
	return bool;
}

function checkText(id){//根据id判断文本框是否为空
	var val=document.getElementById(id).value;
	if(val) return true;
	else return false;
}