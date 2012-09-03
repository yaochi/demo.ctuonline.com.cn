//导入相应的js文件
//alert("df");
document.writeln('<link rel=stylesheet href="../css/xtree.css" type="text/css">');
document.writeln('<script type="text/javascript" src="../js/xtree/xtree.js"></script>');
document.writeln('<script type="text/javascript" src="../js/xtree/xmlextras.js"></script>');
document.writeln('<script type="text/javascript" src="../js/xtree/xloadtree.js"></script>');
document.writeln('<script type="text/javascript" src="../js/xtree/poslib.js"></script>');

//初始化层

function createResourceDiv() {
	document.writeln("<div id=\"_resourceLayer\" style=\"position: absolute; width:150; top: 0px; left: 0px;padding: 5px; visibility: hidden; background-color: #FFFFFF\">");
	document.writeln("<TABLE border=0 bgcolor='#cccccc' cellspacing='0' cellpadding='0' width='100%'>");
	document.writeln("<tr align=right bgcolor='#cccccc'>");
	document.writeln("<td style='padding-right: 0px; padding-left: 0px;' width=%100 height=10>&nbsp;</td>");
	document.writeln("<td style='padding-right: 0px; padding-left: 0px;' bgcolor='#cccccc' width=10 height=8 style='BACKGROUND-COLOR: lightsteelblue; BORDER-BOTTOM: #000000 1px solid; BORDER-LEFT: #FFFFFF 1px solid; BORDER-RIGHT: #000000 1px solid; BORDER-TOP: #ffffff 1px solid; COLOR: #666699;' align='left'>");
	document.writeln("<a onclick='_ShowSelectResource()'  style='cursor:hand;font-size:7px'><font style='font-family:Webdings' >r</font></a></td></tr>");
	document.writeln("</TABLE>");
	document.writeln("<TABLE border=0 bgcolor='#cccccc' cellspacing='0' cellpadding='0'>");
	document.writeln("<script type=\"text/javascript\">");
	document.writeln("/// XP Look");
	document.writeln("webFXTreeConfig.rootIcon		= \"../images/jsptree/xp/folder.png\";");
	document.writeln("webFXTreeConfig.openRootIcon	= \"../images/jsptree/xp/openfolder.png\";");
	document.writeln("webFXTreeConfig.folderIcon	= \"../images/jsptree/xp/folder.png\";");
	document.writeln("webFXTreeConfig.openFolderIcon= \"../images/jsptree/xp/openfolder.png\";");
	document.writeln("webFXTreeConfig.fileIcon		= \"../images/jsptree/xp/file.png\";");
	document.writeln("webFXTreeConfig.lMinusIcon	= \"../images/jsptree/xp/Lminus.png\";");
	document.writeln("webFXTreeConfig.lPlusIcon		= \"../images/jsptree/xp/Lplus.png\";");
	document.writeln("webFXTreeConfig.tMinusIcon	= \"../images/jsptree/xp/Tminus.png\";");
	document.writeln("webFXTreeConfig.tPlusIcon		= \"../images/jsptree/xp/Tplus.png\";");
	document.writeln("webFXTreeConfig.iIcon			= \"../images/jsptree/xp/I.png\";");
	document.writeln("webFXTreeConfig.lIcon			= \"../images/jsptree/xp/L.png\";");
	document.writeln("webFXTreeConfig.tIcon			= \"../images/jsptree/xp/T.png\";");
	document.writeln("webFXTreeConfig.blankIcon		= \"../images/jsptree/blank.png\";");
	document.writeln("");
	
	document.writeln("var treeResource = new WebFXLoadTree(\"导航资源\", \"../command/ResourceManager?flag=selectResource\",\"javascript:_SetSelectedResource('10000','导航资源');\");");
	document.writeln("document.write(treeResource);");
	document.writeln("</script>");
	document.writeln("</TABLE>");
	document.writeln("<table align='center' class='table-content'><tr><td  class='td-button-c'>");
	document.writeln("");
	document.writeln("</td></tr></table>");
	document.writeln("</div>");
}

var id_field = "";
var name_field = "";
var isSubmit = false;

function _ShowSelectResource(idfield,namefiled) {
	isSubmit = false;
	id_field = idfield;
	name_field = namefiled;
	if (_resourceLayer.style.visibility == "hidden") {
	  document.all._resourceLayer.style.left = event.clientX+document.body.scrollLeft;
	  document.all._resourceLayer.style.top = event.clientY+document.body.scrollTop
	  document.all._resourceLayer.style.visibility = "visible";
	}else{
	  	document.all._resourceLayer.style.visibility = "hidden";
	}	
}
function _SetSelectedResource(id,name) {
  	eval("document.all."+id_field+".value = id");	
  	eval("document.all."+name_field+".value = name");
	_ShowSelectResource();
}
function  _jsStationConfirm(){
  	document.all._resourceLayer.style.visibility = "hidden";
	if(isSubmit){
	}
}




