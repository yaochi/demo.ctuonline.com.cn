//*====================================================
//*
//*	 �����Ҽ���������
//*
//*====================================================/
TableColor='#0072BC';
function DrawMouseMenu(){
	DivH=2;
	oSelection = document.selection;
	var HrStr="<tr><td align=center valign=middle height=2><TABLE border=0 cellpadding=0 cellspacing=0 width=128 height=2><tr><td height=1 bgcolor=buttonshadow><\/td><\/tr><tr><td height=1 bgcolor=buttonhighlight><\/td><\/tr><\/TABLE><\/td><\/tr>";
	var MenuStr1="<tr><td align=center valign=middle height=20><TABLE border=0 cellpadding=0 cellspacing=0 width=132><tr><td valign=middle height=16 class=Mout onMouseOver=this.className='Mover'; onMouseOut=this.className='Mout'; onclick=\"";
	var MenuStr2="<\/td><\/tr><\/TABLE><\/td><\/tr>";

	var XiciMenu=["window.history.back()\">&nbsp;&nbsp;����","window.history.forward()\">&nbsp;&nbsp;ǰ��"];
	var SysMenu=["document.execCommand('SelectAll')\">&nbsp;&nbsp;ȫѡ","MouseMenu.style.visibility='hidden';document.execCommand('SaveAs','true')\">&nbsp;&nbsp;���Ϊ...","location.replace('view-source:'+location.href)\">&nbsp;&nbsp;�鿴Դ�ļ�","window.print()\">&nbsp;&nbsp;��ӡ","window.location.reload()\">&nbsp;&nbsp;ˢ��"];
	var MenuStr="";
	for(i=0;i<XiciMenu.length;i++){
		MenuStr+=MenuStr1+XiciMenu[i]+MenuStr2;
		DivH+=20;
	}
	MenuStr+=HrStr;
	for(i=0;i<arguments.length;i++){
		MenuStr+=MenuStr1+arguments[i]+MenuStr2;
		DivH+=20;
	}
	if(arguments.length>0){
		MenuStr+=HrStr;
		DivH+=2;
	}
	for(i=0;i<SysMenu.length;i++){
		MenuStr+=MenuStr1+SysMenu[i]+MenuStr2;
		DivH+=20;
	}
	var DivStr1="<DIV id=MouseMenu class=div1 style=\"position:absolute; left:0px; top:0px; width=100;height="+0+"; z-index:1; visibility:hidden;\"><TABLE border=0 cellpadding=0 cellspacing=0 class=div2><tr><td bgcolor="+TableColor+" width=10 valign=bottom align=center  bgcolor=buttonface>"+
		"<img id=menugif src='#' width=18 height=160><\/td><td bgcolor=buttonface><TABLE border=0 cellpadding=0 cellspacing=0>";
		//"<\/td><td bgcolor=buttonface><TABLE border=0 cellpadding=0 cellspacing=0>";
	var DivStr2="<\/TABLE><\/td><\/tr><\/TABLE><\/DIV>";
	document.write(DivStr1+MenuStr+DivStr2);
	//document.body.oncontextmenu=new Function("return ShowMouseMenu();");
	document.body.onclick=new Function("MouseMenu.style.visibility='hidden';");
	document.body.onscroll=new Function("MouseMenu.style.visibility='hidden';");
	document.body.onselectstart=new Function("MouseMenu.style.visibility='hidden';");
	window.onresizestart=new Function("MouseMenu.style.visibility='hidden';");
}

function ShowMouseMenu(){
   // alert(this.id);
	if (event.srcElement.tagName=='A')
	{
		//alert(webFXTreeHandler.selected.action);
		var tmp = webFXTreeHandler.selected.action;
		//------------------------------
		var t1= tmp.substring(0,tmp.lastIndexOf("'"));
		var t2 = t1.substring(t1.lastIndexOf("'") + 1);
		//------------------------------
		eval("rightButton("+t2+")");
	}
	if(MouseMenu.style.visibility=='visible') MouseMenu.style.visibility='hidden';
		if(event.clientX+150 > document.body.clientWidth && event.clientX - 150 > 0)
			MouseMenu.style.left=event.clientX+document.body.scrollLeft-150;
		else
			MouseMenu.style.left=event.clientX+document.body.scrollLeft;
		if(event.clientY+DivH > document.body.clientHeight && event.clientY - DivH >0)
			MouseMenu.style.top=event.clientY+document.body.scrollTop-DivH;
		else
			MouseMenu.style.top=event.clientY+document.body.scrollTop;

	MouseMenu.style.visibility='visible';

	return false;
}
 function rightButton(id,isleaf) {   
	MouseMenu.innerHTML = "";
	DivH=2;
	var HrStr="<tr><td align=center valign=middle height=2><TABLE border=0 cellpadding=0 cellspacing=0 width=128 height=2><tr><td height=1 bgcolor=buttonshadow><\/td><\/tr><tr><td height=1 bgcolor=buttonhighlight><\/td><\/tr><\/TABLE><\/td><\/tr>";
	var MenuStr1="<tr><td align=center valign=middle height=20><TABLE border=0 cellpadding=0 cellspacing=0 width=128><tr><td valign=middle height=16 width=100% class=Mout onMouseOver=this.className='Mover'; onMouseOut=this.className='Mout'; onclick=\"";
	var MenuStr2="<\/td><\/tr><\/TABLE><\/td><\/tr>";
	var XiciMenu = "";
	if (!isleaf) {
		//Ŀ¼
			XiciMenu=["_Add("+id+")\">&nbsp;&nbsp;���","_Modify("+id+")\">&nbsp;&nbsp;�޸�","\"> <font color=\"#999999\">&nbsp;&nbsp;ɾ��</font>"];
	}else if(id == 1000000){
			XiciMenu=["_Add("+id+")\">&nbsp;&nbsp;���","_Modify("+id+")\">&nbsp;&nbsp;�޸�","\"> <font color=\"#999999\">&nbsp;&nbsp;ɾ��</font>"];
	}else{
			XiciMenu=["_Add("+id+")\">&nbsp;&nbsp;���","_Modify("+id+")\">&nbsp;&nbsp;�޸�","_Delete("+id+")\">&nbsp;&nbsp;ɾ��"];	
	}
	var RightMenu=[];
	var SysMenu=["window.location.reload()\">&nbsp;&nbsp;ˢ��"];
	var MenuStr="";
	for(i=0;i<XiciMenu.length;i++){
		MenuStr+=MenuStr1+XiciMenu[i]+MenuStr2;
		DivH+=20;
	}
	for(i=0;i<RightMenu.length;i++){
		MenuStr+=MenuStr1+RightMenu[i]+MenuStr2;
		DivH+=20;
	}
	MenuStr+=HrStr;
	for(i=0;i<SysMenu.length;i++){
		MenuStr+=MenuStr1+SysMenu[i]+MenuStr2;
		DivH+=20;
	}
	var DivStr1="<TABLE border=0 cellpadding=0 cellspacing=0 class=div2><tr><td bgcolor="+TableColor+" width=10 valign=bottom align=center  bgcolor=buttonface><img id=menugif src='#' width=18 height=100><\/td><td bgcolor=buttonface><TABLE border=0 cellpadding=0 cellspacing=0>";
	var DivStr2="<\/TABLE><\/td><\/tr><\/TABLE>";
	MouseMenu.innerHTML = unescape(DivStr1+MenuStr+DivStr2);
}
function _Add(id)
{
	openwindowtocenter('../command/InnerTeacherControl?flag=innerteacherTypeAdd&parentid='+id+'&isgroup=1','CreatInfo',500,300);
}
function _Modify(id)
{
	openwindowtocenter('../command/InnerTeacherControl?flag=innerteacherTypeMod&selId='+id+'&isgroup=1','openCategry',500,300);
}
function openWindowbyDel( url, winName, width, height) 
{
	xposition=0; yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 ))	{
	  xposition = (window.screen.availWidth - width) / 2;
	}
	yposition = 0;
	theproperty= "width=" + width + "," 
	+ "height=" + height + "," 
	+ "location=0," 
	+ "menubar=0,"
	+ "resizable=1,"
	+ "scrollbars=yes,"
	+ "status=0," 
	+ "titlebar=0,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "screenx=" + xposition + "," 
	+ "screeny=" + yposition + "," 
	+ "left=2000," 
	+ "top=" + yposition; 
	window.open( url,winName,theproperty );
}

function _Delete(id)
{
	MouseMenu.style.visibility='hidden';
	if (confirm("ɾ�������ɾ������������н�ʦ��ȷ����")) {
		openWindowbyDel('../command/InnerTeacherControl?flag=innerteacherTypeDel&id='+id+'&type=U','DeleteCategry',10,10);
		//delNode();
	}
}
