/* 
** Added by Betty 
*/

/* 定位选项卡下的小箭头 */
function posArrow(blockNum){
	var nodes = $("stacoursepanel").childNodes;
	var blocks = [];
	for(var i = 0; i < nodes.length; i++) {
		if(nodes[i].nodeType != "3" && nodes[i].id.indexOf("stationBlock") != -1) {
			blocks.push(nodes[i]);
		}
	}
	
	var length = 0;
	var realNode = $("stationBlock" + blockNum);
	for(var j = 0; j <= blockNum; j++) {
		length += blocks[j].offsetWidth;
	}
	$("stationArrow").style.left = length - realNode.offsetWidth / 2 - $("stationArrow").offsetWidth / 2 + "px";

}

function searchFocusL(obj) {
if(obj.value == '请输入关键词') {
obj.value = '';
}

}
function searchBlurL(obj) {
if(obj.value == '' ) {
obj.value = '请输入关键词';

}
} 