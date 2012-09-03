//*====================================================
//*
//*	 增加右键弹出功能
//*
//*====================================================/
 

TableColor='#0072BC';
function DrawMouseMenu(){
   
	DivH=2;
	oSelection = document.selection;
	var HrStr="<tr><td align=center valign=middle height=2><TABLE border=0 cellpadding=0 cellspacing=0 width=128 height=2><tr><td height=1 bgcolor=buttonshadow><\/td><\/tr><tr><td height=1 bgcolor=buttonhighlight><\/td><\/tr><\/TABLE><\/td><\/tr>";
	var MenuStr1="<tr><td align=center valign=middle height=20><TABLE border=0 cellpadding=0 cellspacing=0 width=132><tr><td valign=middle height=16 class=Mout onMouseOver=this.className='Mover'; onMouseOut=this.className='Mout'; onclick=\"";
	var MenuStr2="<\/td><\/tr><\/TABLE><\/td><\/tr>";

	var XiciMenu=["window.history.back()\">&nbsp;&nbsp;后退","window.history.forward()\">&nbsp;&nbsp;前进"];
	var SysMenu=["document.execCommand('SelectAll')\">&nbsp;&nbsp;全选","MouseMenu.style.visibility='hidden';document.execCommand('SaveAs','true')\">&nbsp;&nbsp;另存为...","location.replace('view-source:'+location.href)\">&nbsp;&nbsp;查看源文件","window.print()\">&nbsp;&nbsp;打印","window.location.reload()\">&nbsp;&nbsp;刷新"];
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

	if(MouseMenu.style.visibility=='visible')MouseMenu.style.visibility='hidden';
	//if(event.srcElement.tagName=="IMG"&&event.srcElement.id!="menugif"||event.srcElement.tagName=="A"||event.srcElement.tagName=="TEXTAREA"||event.srcElement.tagName=="INPUT"||oSelection.type!="None")
	//if(event.srcElement.tagName=="IMG"&&event.srcElement.id!="menugif"||event.srcElement.tagName=="TEXTAREA"||event.srcElement.tagName=="INPUT"||oSelection.type!="None")
		//return true;
	//else{
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
		//目录


			XiciMenu=["_Add("+id+")\">&nbsp;&nbsp;添加","_Modify("+id+")\">&nbsp;&nbsp;修改","\"> <font color=\"#999999\">&nbsp;&nbsp;删除</font>"];

	}else{
			XiciMenu=["_Add("+id+")\">&nbsp;&nbsp;添加","_Modify("+id+")\">&nbsp;&nbsp;修改","_Delete("+id+")\">&nbsp;&nbsp;删除"];	

	}

	var RightMenu=[];
	var SysMenu=["window.location.reload()\">&nbsp;&nbsp;刷新"];
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
	eval(IFrameName + ".window.location.href='"+_addURL+"id="+id+"'");
}
function _Modify(id)
{
	eval(IFrameName + ".window.location.href='"+_modifyURL+"id="+id+"'");
}
function _Delete(id)
{
	if (!confirm("您确认删除吗?"))
		return;
	eval(IFrameName + ".window.location.href='"+_deleteURL+"id="+id+"'");
}
