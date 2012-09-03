/* 
    Document   : base.js
    Author     : Izzln Yin
    Version    : 20110106.1659
*/

var wrapperImgSRC = "assets/image/bg.jpg";
var duration = 150;
var fps = 30;
var tabFrames = {"tabID":[], "tabNum":[]};
var fadeIntervalProcess;

var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','maxthon':'','mozilla':'','webkit':''});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length >>> 0;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}


function initTab(tabID) {
	var tabs = $(tabID + "_title").getElementsByTagName("li");
	
	for(var i = 0; i < tabs.length; i++) {
		var links = tabs[i].getElementsByTagName("a");		
		for(var j = 0; j < links.length; j++) {
			attachEventListener(links[j], "click", switchTab, false);
		}
		
		$(tabs[i].id + "_content").style.display = "none";
	}
	
	addClass(tabs[0], "a");
	$(tabs[0].id + "_content").style.display = "block";

	tabFrames.tabID.push(tabID);
	tabFrames.tabNum.push(tabs.length);	
}

function switchTab(event) {
	var obj = getEventTarget(event);
	var objLiID, objTabID;
	
	if(obj.tagName.toLowerCase() == "a" && obj.parentNode.tagName.toLowerCase() == "li") {
		objLiID = obj.parentNode.id;
		objTabID = obj.parentNode.parentNode.parentNode.id.replace("_title", "");
	}
	
	var tabs = $(objTabID + "_title").getElementsByTagName("li");
	
	for(var i = 0 ; i < tabs.length; i++) {
		removeClass(tabs[i], "a");
		$(tabs[i].id + "_content").style.display = "none";
	}
	
	addClass($(objLiID), "a");
	$(objLiID + "_content").style.display = "block";
	
}

function alternatingRowColor(tables) {
	if(tables) {
		for(var i = 0; i < tables.length; i++) {
			var rows = $(tables[i]).getElementsByTagName("tr");
						
			for(var j = 0; j < rows.length; j++) { j%2 ? "": addClass(rows[j], "alRowColor"); }		
		}
	}
}

function showWindow(id, isIFrame) {
	var fwin_mask = isIFrame ? parent.$("fwin_mask") : $("fwin_mask");
	var fwin_content = isIFrame ? parent.$("fwin_content") : $("fwin_content");
	var fwin_iframe = isIFrame ? parent.$("fwin_iframe") : $("fwin_iframe");
	var wrapper = isIFrame ? parent.$("diy3") : $("diy3");
	
	if(BROWSER.ie && BROWSER.ie < 8) {
		fwin_mask.style.height = wrapper.offsetHeight + "px";
	} else {
		fwin_mask.style.height = wrapper.offsetHeight + 1 + "px";
		fwin_mask.style.marginTop = "-1px";
	}
	fadeIn(fwin_mask, 80);
	fwin_content.style.display = "block";
	
	var idGrp = [id.substring(0, id.indexOf("_")).replace("y", ""), id.substring(id.indexOf("_")+1, id.length)];
	fwin_iframe.src = "/static/page/tyzc/detail/" + idGrp[0] + "/" + idGrp[1] + ".html";
}

function $(id) {
	return !id ? null : document.getElementById(id);
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function addLoadListener(functionRef) {
    if (!attachEventListener(window, "load", functionRef, false)) {
        document.addEventListener("load", functionRef, false);
    }
}

function attachEventListener(target, eventType, functionRef, capture) {
    if (typeof target.addEventListener != "undefined") {
        target.addEventListener(eventType, functionRef, capture);
        return true;
    } else if (typeof target.attachEvent != "undefined") {
        target.attachEvent("on" + eventType, functionRef);
        return true;
    }

    else return false;
}

function stopDefaultAction(event) {
    event.returnValue = false;
    if (typeof event.preventDefault != "undefined") {
        event.preventDefault();
    }
}

function retrieveComputedStyle(element, styleProperty) {
    var computedStyle = null;

    if (typeof element.currentStyle != "undefined") {
        computedStyle = element.currentStyle;
    } else {
        computedStyle = document.defaultView.getComputedStyle(element, null);
    }
    return computedStyle[styleProperty];
}

function getViewportSize() {
    var size = [0, 0];

    if (typeof window.innerWidth != 'undefined') {
        size = [
            window.innerWidth,
            window.innerHeight
        ];
    } else if (typeof document.documentElement != 'undefined'
        && typeof document.documentElement.clientWidth !=
        'undefined' && document.documentElement.clientWidth != 0) {
        size = [
            document.documentElement.clientWidth,
            document.documentElement.clientHeight
        ];
    } else {
        size = [
            document.getElementsByTagName('body')[0].clientWidth,
            document.getElementsByTagName('body')[0].clientHeight
        ];
    }
    return size;
}

function fadeIn(target, opacity) {

    setOpacity(target, 0);
    target.style.display = "block";

    var times = Math.round((duration * fps) / 1000);
    var step = Math.round(100 / times);
    var i = 0;

	clearInterval(fadeIntervalProcess);
    fadeIntervalProcess = setInterval(function() {
        setOpacity(target, i);

        if (i <= opacity) {
            i += step;
        }
      
        else {
            clearInterval(fadeIntervalProcess);
            setOpacity(target, opacity);
        }
    },
    Math.round(1000 / fps));

}

function fadeOut(target, opacity) {
	var opacity = opacity ? opacity : 0;
    var times = Math.round((duration * fps) / 1000);
    var step = Math.round(100 / times);
    var i = 100;
    
	clearInterval(fadeIntervalProcess);
    fadeIntervalProcess = setInterval(function() {
        setOpacity(target, i);

        if (i >= opacity) {
            i -= step;
        }

        else {
            clearInterval(fadeIntervalProcess);
            setOpacity(target, opacity);
            if(opacity == 0) {
            	target.style.display = "none";
            }
        }
    },
    Math.round(1000 / fps));
}

function setOpacity(target, opacityLevel) {
    if (0 <= opacityLevel <= 100) {
    	BROWSER.ie ? target.style.filter = "alpha(opacity=" + opacityLevel +")" : target.style.opacity = opacityLevel / 100;
    }
}

function addClass(target, classValue) {
	var pattern = new RegExp("(^| )" + classValue + "( |$)");
	
	if (!pattern.test(target.className)) {
		if (target.className == "") {
			target.className = classValue;
		} else {
			target.className += " " + classValue;
		} return true;
	}
}

function removeClass(target, classValue) {
	var removedClass = target.className; var pattern = new RegExp("(^| )" + classValue + "( |$)");
	removedClass = removedClass.replace(pattern, "$1");
	removedClass = removedClass.replace(/ $/, "");
	target.className = removedClass;
	return true;
}

function getEventTarget(event)	{
	var targetElement = null;
	
	if (typeof event.target != "undefined")	{
		targetElement = event.target;
	} else {
		targetElement = event.srcElement;
	}
	
	while (targetElement.nodeType == 3 && targetElement.parentNode != null) {
		targetElement = targetElement.parentNode;
	}
	
	return targetElement;
}