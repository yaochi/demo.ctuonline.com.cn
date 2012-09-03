/*----------------------------------------------------------------------------\
|                       Cross Browser Tree Widget 1.17                        |
|-----------------------------------------------------------------------------|
|                          Created by Emil A Eklund                           |
|                  (http://webfx.eae.net/contact.html#emil)                   |
|                      For WebFX (http://webfx.eae.net/)                      |
|-----------------------------------------------------------------------------|
| An object based tree widget,  emulating the one found in microsoft windows, |
| with persistence using cookies. Works in IE 5+, Mozilla and konqueror 3.    |
|-----------------------------------------------------------------------------|
|          Copyright (c) 2000, 2001, 2002, 2003, 2006 Emil A Eklund           |
|-----------------------------------------------------------------------------|
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License.  You may obtain a copy |
| of the License at http://www.apache.org/licenses/LICENSE-2.0                |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| Unless  required  by  applicable law or  agreed  to  in  writing,  software |
| distributed under the License is distributed on an  "AS IS" BASIS,  WITHOUT |
| WARRANTIES OR  CONDITIONS OF ANY KIND,  either express or implied.  See the |
| License  for the  specific language  governing permissions  and limitations |
| under the License.                                                          |
|-----------------------------------------------------------------------------|
| Dependencies: xtree.css (To set up the CSS of the tree classes)             |
|-----------------------------------------------------------------------------|
| 2001-01-10 | Original Version Posted.                                       |
| 2001-03-18 | Added getSelected and get/setBehavior  that can make it behave |
|            | more like windows explorer, check usage for more information.  |
| 2001-09-23 | Version 1.1 - New features included  keyboard  navigation (ie) |
|            | and the ability  to add and  remove nodes dynamically and some |
|            | other small tweaks and fixes.                                  |
| 2002-01-27 | Version 1.11 - Bug fixes and improved mozilla support.         |
| 2002-06-11 | Version 1.12 - Fixed a bug that prevented the indentation line |
|            | from  updating correctly  under some  circumstances.  This bug |
|            | happened when removing the last item in a subtree and items in |
|            | siblings to the remove subtree where not correctly updated.    |
| 2002-06-13 | Fixed a few minor bugs cased by the 1.12 bug-fix.              |
| 2002-08-20 | Added usePersistence flag to allow disable of cookies.         |
| 2002-10-23 | (1.14) Fixed a plus icon issue                                 |
| 2002-10-29 | (1.15) Last changes broke more than they fixed. This version   |
|            | is based on 1.13 and fixes the bugs 1.14 fixed withou breaking |
|            | lots of other things.                                          |
| 2003-02-15 | The  selected node can now be made visible even when  the tree |
|            | control  loses focus.  It uses a new class  declaration in the |
|            | css file '.webfx-tree-item a.selected-inactive', by default it |
|            | puts a light-gray rectangle around the selected node.          |
| 2003-03-16 | Adding target support after lots of lobbying...                |
| 2006-05-26 | Changed license to Apache Software License 2.0.                |
|-----------------------------------------------------------------------------|
| Created 2000-12-11 | All changes are in the log above. | Updated 2006-05-26 |
\----------------------------------------------------------------------------*/

var webFXTreeConfig = {
	rootIcon        : 'images/foldericon.png',
	openRootIcon    : 'images/openfoldericon.png',
	folderIcon      : 'images/foldericon.png',
	openFolderIcon  : 'images/openfoldericon.png',
	fileIcon        : 'images/file.png',
	iIcon           : 'images/I.png',
	lIcon           : 'images/L.png',
	lMinusIcon      : 'images/Lminus.png',
	lPlusIcon       : 'images/Lplus.png',
	tIcon           : 'images/T.png',
	tMinusIcon      : 'images/Tminus.png',
	tPlusIcon       : 'images/Tplus.png',
	blankIcon       : 'images/blank.png',
	defaultText     : 'Tree Item',
	defaultAction   : 'javascript:void(0);',
	defaultBehavior : 'classic',
	usePersistence	: true
};

var webFXTreeHandler = {
	idCounter : 0,
	idPrefix  : "webfx-tree-object-",
	all       : {},
	behavior  : null,
	selected  : null,
	onSelect  : null, /* should be part of tree, not handler */
	getId     : function() { return this.idPrefix + this.idCounter++; },
	toggle    : function (oItem) { this.all[oItem.id.replace('-plus','')].toggle(); },
	select    : function (oItem) { this.all[oItem.id.replace('-icon','')].select(); },
	focus     : function (oItem) { this.all[oItem.id.replace('-anchor','')].focus(); },
	blur      : function (oItem) { this.all[oItem.id.replace('-anchor','')].blur(); },
	keydown   : function (oItem, e) { return this.all[oItem.id].keydown(e.keyCode); },
	cookies   : new WebFXCookie(),
	insertHTMLBeforeEnd	:	function (oElement, sHTML) {
		if (oElement.insertAdjacentHTML != null) {
			oElement.insertAdjacentHTML("BeforeEnd", sHTML)
			return;
		}
		var df;	// DocumentFragment
		var r = oElement.ownerDocument.createRange();
		r.selectNodeContents(oElement);
		r.collapse(false);
		df = r.createContextualFragment(sHTML);
		oElement.appendChild(df);
	}
};

/*
 * WebFXCookie class
 */

function WebFXCookie() {
	if (document.cookie.length) { this.cookies = ' ' + document.cookie; }
}

WebFXCookie.prototype.setCookie = function (key, value) {
	document.cookie = key + "=" + escape(value);
}

WebFXCookie.prototype.getCookie = function (key) {
	if (this.cookies) {
		var start = this.cookies.indexOf(' ' + key + '=');
		if (start == -1) { return null; }
		var end = this.cookies.indexOf(";", start);
		if (end == -1) { end = this.cookies.length; }
		end -= start;
		var cookie = this.cookies.substr(start,end);
		return unescape(cookie.substr(cookie.indexOf('=') + 1, cookie.length - cookie.indexOf('=') + 1));
	}
	else { return null; }
}

/*
 * WebFXTreeAbstractNode class
 * 
 */

function WebFXTreeAbstractNode(id, sText, sAction, parentId, isMarked, isFinished) {
	this.childNodes  = [];
	this.id     = id;
	this.text   = sText || webFXTreeConfig.defaultText;
	this.action = sAction || webFXTreeConfig.defaultAction;
	this._last  = false;
	webFXTreeHandler.all[this.id] = this;
	this.parentId = -1;
	this.isMarked = 0;
	if(parentId)
		this.parentId = parentId;
	if(isMarked)
		this.isMarked = isMarked;
	if(isFinished)
		this.isFinished = isFinished;
}

/*
 * To speed thing up if you're adding multiple nodes at once (after load)
 * use the bNoIdent parameter to prevent automatic re-indentation and call
 * the obj.ident() method manually once all nodes has been added.
 */

WebFXTreeAbstractNode.prototype.add = function (node, bNoIdent) {
	node.parentNode = this;
	this.childNodes[this.childNodes.length] = node;
	var root = this;
	if (this.childNodes.length >= 2) {
		this.childNodes[this.childNodes.length - 2]._last = false;
	}
	while (root.parentNode) { root = root.parentNode; }
	if (root.rendered) {
		if (this.childNodes.length >= 2) {
			document.getElementById(this.childNodes[this.childNodes.length - 2].id + '-plus').src = ((this.childNodes[this.childNodes.length -2].folder)?((this.childNodes[this.childNodes.length -2].open)?webFXTreeConfig.tMinusIcon:webFXTreeConfig.tPlusIcon):webFXTreeConfig.tIcon);
			this.childNodes[this.childNodes.length - 2].plusIcon = webFXTreeConfig.tPlusIcon;
			this.childNodes[this.childNodes.length - 2].minusIcon = webFXTreeConfig.tMinusIcon;
			this.childNodes[this.childNodes.length - 2]._last = false;
		}
		this._last = true;
		var foo = this;
		while (foo.parentNode) {
			for (var i = 0; i < foo.parentNode.childNodes.length; i++) {
				if (foo.id == foo.parentNode.childNodes[i].id) { break; }
			}
			if (i == foo.parentNode.childNodes.length - 1) { foo.parentNode._last = true; }
			else { foo.parentNode._last = false; }
			foo = foo.parentNode;
		}
		webFXTreeHandler.insertHTMLBeforeEnd(document.getElementById(this.id + '-cont'), node.toString());
		if ((!this.folder) && (!this.openIcon)) {
			this.icon = webFXTreeConfig.folderIcon;
			this.openIcon = webFXTreeConfig.openFolderIcon;
		}
		if (!this.folder) { this.folder = true; this.collapse(true); }
		if (!bNoIdent) { this.indent(); }
	}
	return node;
}

WebFXTreeAbstractNode.prototype.toggle = function() {
	if (this.folder) {
		if (this.open) { this.collapse(); }
		else { this.expand(); }
}	}

WebFXTreeAbstractNode.prototype.select = function() {
	document.getElementById(this.id + '-anchor').focus();
}

WebFXTreeAbstractNode.prototype.deSelect = function() {
	document.getElementById(this.id + '-anchor').className = '';
	webFXTreeHandler.selected = null;
}

WebFXTreeAbstractNode.prototype.focus = function() {
	if ((webFXTreeHandler.selected) && (webFXTreeHandler.selected != this)) { webFXTreeHandler.selected.deSelect(); }
	webFXTreeHandler.selected = this;
	if ((this.openIcon) && (webFXTreeHandler.behavior != 'classic')) { document.getElementById(this.id + '-icon').src = this.openIcon; }
	document.getElementById(this.id + '-anchor').className = 'selected';
	document.getElementById(this.id + '-anchor').focus();
	if (webFXTreeHandler.onSelect) { webFXTreeHandler.onSelect(this); }
	if(this.action)
		eval(this.action);
}

WebFXTreeAbstractNode.prototype.blur = function() {
	if ((this.openIcon) && (webFXTreeHandler.behavior != 'classic')) { document.getElementById(this.id + '-icon').src = this.icon; }
	document.getElementById(this.id + '-anchor').className = 'selected-inactive';
}

WebFXTreeAbstractNode.prototype.doExpand = function() {
	if (webFXTreeHandler.behavior == 'classic') { document.getElementById(this.id + '-icon').src = this.openIcon; }
	if (this.childNodes.length) {  document.getElementById(this.id + '-cont').style.display = 'block'; }
	this.open = true;
	if (webFXTreeConfig.usePersistence) {
		webFXTreeHandler.cookies.setCookie(this.id.substr(18,this.id.length - 18), '1');
}	}

WebFXTreeAbstractNode.prototype.doCollapse = function() {
	if (webFXTreeHandler.behavior == 'classic') { document.getElementById(this.id + '-icon').src = this.icon; }
	if (this.childNodes.length) { document.getElementById(this.id + '-cont').style.display = 'none'; }
	this.open = false;
	if (webFXTreeConfig.usePersistence) {
		webFXTreeHandler.cookies.setCookie(this.id.substr(18,this.id.length - 18), '0');
}	}

WebFXTreeAbstractNode.prototype.expandAll = function() {
	this.expandChildren();
	if ((this.folder) && (!this.open)) { this.expand(); }
}

WebFXTreeAbstractNode.prototype.expandChildren = function() {
	for (var i = 0; i < this.childNodes.length; i++) {
		this.childNodes[i].expandAll();
} }

WebFXTreeAbstractNode.prototype.collapseAll = function() {
	this.collapseChildren();
	if ((this.folder) && (this.open)) { this.collapse(true); }
}

WebFXTreeAbstractNode.prototype.collapseChildren = function() {
	for (var i = 0; i < this.childNodes.length; i++) {
		this.childNodes[i].collapseAll();
} }

WebFXTreeAbstractNode.prototype.indent = function(lvl, del, last, level, nodesLeft) {
	/*
	 * Since we only want to modify items one level below ourself,
	 * and since the rightmost indentation position is occupied by
	 * the plus icon we set this to -2
	 */
	if (lvl == null) { lvl = -2; }
	var state = 0;
	for (var i = this.childNodes.length - 1; i >= 0 ; i--) {
		state = this.childNodes[i].indent(lvl + 1, del, last, level);
		if (state) { return; }
	}
	if (del) {
		if ((level >= this._level) && (document.getElementById(this.id + '-plus'))) {
			if (this.folder) {
				document.getElementById(this.id + '-plus').src = (this.open)?webFXTreeConfig.lMinusIcon:webFXTreeConfig.lPlusIcon;
				this.plusIcon = webFXTreeConfig.lPlusIcon;
				this.minusIcon = webFXTreeConfig.lMinusIcon;
			}
			else if (nodesLeft) { document.getElementById(this.id + '-plus').src = webFXTreeConfig.lIcon; }
			return 1;
	}	}
	var foo = document.getElementById(this.id + '-indent-' + lvl);
	if (foo) {
		if ((foo._last) || ((del) && (last))) { foo.src =  webFXTreeConfig.blankIcon; }
		else { foo.src =  webFXTreeConfig.iIcon; }
	}
	return 0;
}

/*
 * WebFXTree class
 */

function WebFXTree(id, sText, sAction, sBehavior, sIcon, sOpenIcon) {
	this.base = WebFXTreeAbstractNode;
	this.base(id, sText, sAction);
	this.icon      = sIcon || webFXTreeConfig.rootIcon;
	this.openIcon  = sOpenIcon || webFXTreeConfig.openRootIcon;
	/* Defaults to open */
	if (webFXTreeConfig.usePersistence) {
		this.open  = (webFXTreeHandler.cookies.getCookie(this.id.substr(18,this.id.length - 18)) == '0')?false:true;
	} else { this.open  = true; }
	this.folder    = true;
	this.rendered  = false;
	this.onSelect  = null;
	if (!webFXTreeHandler.behavior) {  webFXTreeHandler.behavior = sBehavior || webFXTreeConfig.defaultBehavior; }
}

WebFXTree.prototype = new WebFXTreeAbstractNode;

WebFXTree.prototype.setBehavior = function (sBehavior) {
	webFXTreeHandler.behavior =  sBehavior;
};

WebFXTree.prototype.getBehavior = function (sBehavior) {
	return webFXTreeHandler.behavior;
};

WebFXTree.prototype.getSelected = function() {
	if (webFXTreeHandler.selected) { return webFXTreeHandler.selected; }
	else { return null; }
}

WebFXTree.prototype.remove = function() { }

WebFXTree.prototype.expand = function() {
	this.doExpand();
}

WebFXTree.prototype.collapse = function(b) {
	if (!b) { this.focus(); }
	this.doCollapse();
}

WebFXTree.prototype.getFirst = function() {
	return null;
}

WebFXTree.prototype.getLast = function() {
	return null;
}

WebFXTree.prototype.getNextSibling = function() {
	return null;
}

WebFXTree.prototype.getPreviousSibling = function() {
	return null;
}

WebFXTree.prototype.keydown = function(key) {
	if (key == 39) {
		if (!this.open) { this.expand(); }
		else if (this.childNodes.length) { this.childNodes[0].select(); }
		return false;
	}
	if (key == 37) { this.collapse(); return false; }
	if ((key == 40) && (this.open) && (this.childNodes.length)) { this.childNodes[0].select(); return false; }
	return true;
}

WebFXTree.prototype.toString = function() {
	var str = "<div id=\"" + this.id + "\" class=\"webfx-tree-item\">" +
		"<img id=\"" + this.id + "-icon\" class=\"webfx-tree-icon\" src=\"" + ((webFXTreeHandler.behavior == 'classic' && this.open)?this.openIcon:this.icon) + "\" onclick=\"webFXTreeHandler.select(this);\">" +
		"<a id=\"" + this.id + "-anchor\" " +
		(this.target ? " target=\"" + this.target + "\"" : "") +
		">" + this.text + "</a></div>" +
		"<div id=\"" + this.id + "-cont\" class=\"webfx-tree-container\" style=\"display: " + ((this.open)?'block':'none') + ";\">";
	var sb = [];
	for (var i = 0; i < this.childNodes.length; i++) {
		sb[i] = this.childNodes[i].toString(i, this.childNodes.length);
	}
	this.rendered = true;
	return str + sb.join("") + "</div>";
};

/*
 * WebFXTreeItem class
 */

function WebFXTreeItem(id, sText, sAction, parentId, isMarked, eParent, sIcon, sOpenIcon) {
	this.base = WebFXTreeAbstractNode;
	this.base(id, sText, sAction, parentId, isMarked);
	/* Defaults to close */
	if (webFXTreeConfig.usePersistence) {
		this.open = (webFXTreeHandler.cookies.getCookie(this.id.substr(18,this.id.length - 18)) == '1')?true:false;
	} else { this.open = false; }
	if (sIcon) { this.icon = sIcon; }
	if (sOpenIcon) { this.openIcon = sOpenIcon; }
	if (eParent) { eParent.add(this); }
}

WebFXTreeItem.prototype = new WebFXTreeAbstractNode;

WebFXTreeItem.prototype.remove = function() {
	var iconSrc = document.getElementById(this.id + '-plus').src;
	var parentNode = this.parentNode;
	var prevSibling = this.getPreviousSibling(true);
	var nextSibling = this.getNextSibling(true);
	var folder = this.parentNode.folder;
	var last = ((nextSibling) && (nextSibling.parentNode) && (nextSibling.parentNode.id == parentNode.id))?false:true;
	this.getPreviousSibling().focus();
	this._remove();
	if (parentNode.childNodes.length == 0) {
		document.getElementById(parentNode.id + '-cont').style.display = 'none';
		parentNode.doCollapse();
		parentNode.folder = false;
		parentNode.open = false;
	}
	if (!nextSibling || last) { parentNode.indent(null, true, last, this._level, parentNode.childNodes.length); }
	if ((prevSibling == parentNode) && !(parentNode.childNodes.length)) {
		prevSibling.folder = false;
		prevSibling.open = false;
		iconSrc = document.getElementById(prevSibling.id + '-plus').src;
		iconSrc = iconSrc.replace('minus', '').replace('plus', '');
		document.getElementById(prevSibling.id + '-plus').src = iconSrc;
		document.getElementById(prevSibling.id + '-icon').src = webFXTreeConfig.fileIcon;
	}
	if (document.getElementById(prevSibling.id + '-plus')) {
		if (parentNode == prevSibling.parentNode) {
			iconSrc = iconSrc.replace('minus', '').replace('plus', '');
			document.getElementById(prevSibling.id + '-plus').src = iconSrc;
}	}	}

WebFXTreeItem.prototype._remove = function() {
	for (var i = this.childNodes.length - 1; i >= 0; i--) {
		this.childNodes[i]._remove();
 	}
	for (var i = 0; i < this.parentNode.childNodes.length; i++) {
		if (this == this.parentNode.childNodes[i]) {
			for (var j = i; j < this.parentNode.childNodes.length; j++) {
				this.parentNode.childNodes[j] = this.parentNode.childNodes[j+1];
			}
			this.parentNode.childNodes.length -= 1;
			if (i + 1 == this.parentNode.childNodes.length) { this.parentNode._last = true; }
			break;
	}	}
	webFXTreeHandler.all[this.id] = null;
	var tmp = document.getElementById(this.id);
	if (tmp) { tmp.parentNode.removeChild(tmp); }
	tmp = document.getElementById(this.id + '-cont');
	if (tmp) { tmp.parentNode.removeChild(tmp); }
}

WebFXTreeItem.prototype.expand = function() {
	this.doExpand();
	document.getElementById(this.id + '-plus').src = this.minusIcon;
}

WebFXTreeItem.prototype.collapse = function(b) {
	if (!b) { this.focus(); }
	this.doCollapse();
	document.getElementById(this.id + '-plus').src = this.plusIcon;
}

WebFXTreeItem.prototype.getFirst = function() {
	return this.childNodes[0];
}

WebFXTreeItem.prototype.getLast = function() {
	if (this.childNodes[this.childNodes.length - 1].open) { return this.childNodes[this.childNodes.length - 1].getLast(); }
	else { return this.childNodes[this.childNodes.length - 1]; }
}

WebFXTreeItem.prototype.getNextSibling = function() {
	for (var i = 0; i < this.parentNode.childNodes.length; i++) {
		if (this == this.parentNode.childNodes[i]) { break; }
	}
	if (++i == this.parentNode.childNodes.length) { return this.parentNode.getNextSibling(); }
	else { return this.parentNode.childNodes[i]; }
}

WebFXTreeItem.prototype.getPreviousSibling = function(b) {
	for (var i = 0; i < this.parentNode.childNodes.length; i++) {
		if (this == this.parentNode.childNodes[i]) { break; }
	}
	if (i == 0) { return this.parentNode; }
	else {
		if ((this.parentNode.childNodes[--i].open) || (b && this.parentNode.childNodes[i].folder)) { 
			this.parentNode.childNodes[i].expand();
			return this.parentNode.childNodes[i].getLast(); }
		else { return this.parentNode.childNodes[i]; }
} }

WebFXTreeItem.prototype.keydown = function(key) {
	if ((key == 39) && (this.folder)) {
		if (!this.open) { this.expand(); }
		else { this.getFirst().select(); }
		return false;
	}
	else if (key == 37) {
		if (this.open) { this.collapse(); }
		else { this.parentNode.select(); }
		return false;
	}
	else if (key == 40) {
		if (this.open) { this.getFirst().select(); }
		else {
			var sib = this.getNextSibling();
			if (sib) { sib.select(); }
		}
		return false;
	}
	else if (key == 38) { this.getPreviousSibling().select(); return false; }
	return true;
}

WebFXTreeItem.prototype.toString = function (nItem, nItemCount) {
	var foo = this.parentNode;
	var indent = '';
	if (nItem + 1 == nItemCount) { this.parentNode._last = true; }
	var i = 0;
	while (foo.parentNode) {
		foo = foo.parentNode;
		indent = "<img id=\"" + this.id + "-indent-" + i + "\" src=\"" + ((foo._last)?webFXTreeConfig.blankIcon:webFXTreeConfig.iIcon) + "\">" + indent;
		i++;
	}
	this._level = i;
	if (this.childNodes.length) { this.folder = 1; }
	else { this.open = false; }
	if ((this.folder) || (webFXTreeHandler.behavior != 'classic')) {
		if (!this.icon) { this.icon = webFXTreeConfig.folderIcon; }
		if (!this.openIcon) { this.openIcon = webFXTreeConfig.openFolderIcon; }
	}
	else if (!this.icon) { this.icon = webFXTreeConfig.fileIcon; }
	var label = this.text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	var str = "<div id=\"" + this.id + "\" class=\"webfx-tree-item\" onkeydown=\"return webFXTreeHandler.keydown(this, event)\">" +
		indent +
		"<img id=\"" + this.id + "-plus\" src=\"" + ((this.folder)?((this.open)?((this.parentNode._last)?webFXTreeConfig.lMinusIcon:webFXTreeConfig.tMinusIcon):((this.parentNode._last)?webFXTreeConfig.lPlusIcon:webFXTreeConfig.tPlusIcon)):((this.parentNode._last)?webFXTreeConfig.lIcon:webFXTreeConfig.tIcon)) + "\" onclick=\"webFXTreeHandler.toggle(this);\">" +
		"<img id=\"" + this.id + "-icon\" class=\"webfx-tree-icon\" src=\"" + ((webFXTreeHandler.behavior == 'classic' && this.open)?this.openIcon:this.icon) + "\" onclick=\"webFXTreeHandler.select(this);\">" +
		"<img id=\"" + this.id + "-bookmark\" src=\"" + webFXTreeConfig.markIcon + "\" style=\"display:" + (this.isMarked == 1 ? "" : "none") + "\" />" + 
		"<img id=\"" + this.id + "-finishmark\" src=\"" + webFXTreeConfig.finishIcon + "\" style=\"display:" + (this.isFinished == 1 ? "" : "none") + "\" />" + 
		"<a href=\"javascript:\" id=\"" + this.id + "-anchor\" onfocus=\"webFXTreeHandler.focus(this);\" onblur=\"webFXTreeHandler.blur(this);\"" +
		(this.target ? " target=\"" + this.target + "\"" : "") +
		">" + label + "</a></div>" +
		"<div id=\"" + this.id + "-cont\" class=\"webfx-tree-container\" style=\"display: " + ((this.open)?'block':'none') + ";\">";
	var sb = [];
	for (var i = 0; i < this.childNodes.length; i++) {
		sb[i] = this.childNodes[i].toString(i,this.childNodes.length);
	}
	this.plusIcon = ((this.parentNode._last)?webFXTreeConfig.lPlusIcon:webFXTreeConfig.tPlusIcon);
	this.minusIcon = ((this.parentNode._last)?webFXTreeConfig.lMinusIcon:webFXTreeConfig.tMinusIcon);
	return str + sb.join("") + "</div>";
}

/* *************************************************************************** */
webFXTreeConfig.rootIcon		= "../images/jsptree/xp/folder.png";
webFXTreeConfig.openRootIcon	= "../images/jsptree/xp/openfolder.png";
webFXTreeConfig.folderIcon		= "../images/jsptree/xp/folder.png";
webFXTreeConfig.openFolderIcon	= "../images/jsptree/xp/openfolder.png";
webFXTreeConfig.fileIcon		= "../images/jsptree/xp/file.png";
webFXTreeConfig.lMinusIcon		= "../images/jsptree/xp/Lminus.png";
webFXTreeConfig.lPlusIcon		= "../images/jsptree/xp/Lplus.png";
webFXTreeConfig.tMinusIcon		= "../images/jsptree/xp/Tminus.png";
webFXTreeConfig.tPlusIcon		= "../images/jsptree/xp/Tplus.png";
webFXTreeConfig.iIcon			= "../images/jsptree/xp/I.png";
webFXTreeConfig.lIcon			= "../images/jsptree/xp/L.png";
webFXTreeConfig.tIcon			= "../images/jsptree/xp/T.png";
webFXTreeConfig.blankIcon		= "../images/jsptree/blank.png";
webFXTreeConfig.markIcon		= "../images/menu-images/menu_link_ref.gif";
webFXTreeConfig.finishIcon		= "../images/menu-images/menu_link_help.gif";

var tablePrefix = "tb_"; //试题所在表格的id的前缀
var treePrefix = "pt_"; //树id的前缀

/*
function nextNode() {
	var ti = tree.getSelected();
	if(ti) {
		if(ti.folder) {
			ti.expand();
			ti.childNodes[0].focus();
		} else if(ti.getNextSibling() == null) {

		} else if(ti.getNextSibling().folder) {
			ti.getNextSibling().expand();
			ti.getNextSibling().focus();
		} else {
			ti.getNextSibling().focus();
		}
	}
}
*/

function _nextNode() {
	var currentNode = tree.getSelected();
	var nextNode;
	if(currentNode) {
		if(!currentNode.parentNode.open) //如果当前节点的父节点没有展开，就展开父节点
			currentNode.parentNode.expand();
		if(currentNode.folder) {
			currentNode.expand();
			nextNode = currentNode.childNodes[0];
		} else {
			nextNode = currentNode.getNextSibling();
		}
		
		if(nextNode == null) {
			return;
		}
		nextNode.focus();
		//eval(nextNode.action);
	} else {
		_selectRootNode();
	}
	_buttonSwicth(tree.getSelected());
}

function _previousNode() {
	var currentNode = tree.getSelected();
	var previousNode;
	if(currentNode) {
		if(!currentNode.parentNode.open) //如果当前节点的父节点没有展开，就展开父节点
			currentNode.parentNode.expand();
		previousNode = currentNode.getPreviousSibling(true);
		if(previousNode != null) {
			previousNode.focus();
			//eval(previousNode.action);
		}
	} else {
		_selectRootNode();
	}
	_buttonSwicth(tree.getSelected());
}

function _mark(id) {
	var currentNode = tree.getSelected();
	if (currentNode && currentNode.isMarked) {
		currentNode.deleteBookmark();
		document.getElementById(id).value = 0;
	} else if (currentNode) {
		currentNode.addBookmark();
		document.getElementById(id).value = 1;
	}
}

function _finish(id) {
	var currentNode = tree.getSelected();
	if (currentNode && currentNode.isFinished) {
		currentNode.deleteFinishmark();
		document.getElementById(id).value = 0;
	} else if (currentNode) {
		currentNode.addFinishmark();
		document.getElementById(id).value = 1;
	}
}

function _selectRootNode() {
	tree.select();
}

WebFXTreeAbstractNode.prototype.addBookmark = function () {
	return null;
}

WebFXTreeAbstractNode.prototype.deleteBookmark = function () {
	return null;
}

WebFXTreeAbstractNode.prototype.addFinishmark = function () {
	return null;
}

WebFXTreeAbstractNode.prototype.deleteFinishmark = function () {
	return null;
}

WebFXTreeItem.prototype.addBookmark = function () {
	var _id = this.id;
	document.getElementById(_id + "-bookmark").style.display = "";
	this.isMarked = 1;
}

WebFXTreeItem.prototype.deleteBookmark = function () {
	var _id = this.id;
	document.getElementById(_id + "-bookmark").style.display = "none";
	this.isMarked = 0;
}

WebFXTreeItem.prototype.addFinishmark = function () {
	var _id = this.id;
	document.getElementById(_id + "-finishmark").style.display = "";
	this.isFinished = 1;
}

WebFXTreeItem.prototype.deleteFinishmark = function () {
	var _id = this.id;
	document.getElementById(_id + "-finishmark").style.display = "none";
	this.isFinished = 0;
}

function _loadBookmark() {
	for(var i = 0; i < allId.length; i++) {
		if(allId[i] != null) {
			//if(valueHashtable.contains(allId[i] + "_mark"))
				//alert(valueHashtable.items(allId[i] + "_mark"));
			if(valueHashtable.contains(allId[i] + "_mark") && valueHashtable.items(allId[i] + "_mark") == 1) {
				var node = _getNodeById(treePrefix + allId[i]);
				if(node) {
					node.addBookmark();
				} else {
					alert(tid[i] + "不存在！");
				}
				/*
				var bookmark = document.getElementById(treePrefix + allId[i] + "-bookmark");
				if(bookmark)
					bookmark.style.display = "";
				*/
			}
		}
	}
}

function _loadFinishmark() {
	for(var i = 0; i < allId.length; i++) {
		if(allId[i] != null) {
			if(valueHashtable.contains(allId[i] + "_finish") && valueHashtable.items(allId[i] + "_finish") == 1) {
				var node = _getNodeById(treePrefix + allId[i]);
				if(node) {
					node.addFinishmark();
				} else {
					alert(tid[i] + "不存在！");
				}
			}
		}
	}
}


/*
 * 跟据节点的id选择树中的一个节点，如果该节点在目录下，则展开该目录
 */
function _selectNodeById(id) {
	var node;
	var childNodes = _getChildNodes(tree);
	for(var i = 0; i < childNodes.length; i++) {
		if(childNodes[i].id == id) {
			node = childNodes[i];
			_openParentNode(node);
			node.focus();
			break;
		}
	}

	return node;
}

function _openParentNode(currentNode) {
	if(currentNode) {
		var previousNode = _getNodeById(currentNode.parentId);
		if(previousNode) {
			previousNode.expand();
			_openParentNode(previousNode);
		}
	}
}

function _getNodeById(id) {
	var node;
	var childNodes = _getChildNodes(tree);
	for(var i = 0; i < childNodes.length; i++) {
		if(childNodes[i].id == id) {
			node = childNodes[i];
			break;
		}
	}

	return node;
}

function _getChildNodes(currentNode) {
	var childNodes = currentNode.childNodes;
	for(var i = 0; i < childNodes.length; i++) {
		childNodes = childNodes.concat(_getChildNodes(childNodes[i]));
	}

	return childNodes;
}

function _buttonSwicth(currentNode) {
	if(currentNode) {
		if(tree.getSelected()) {
			if(tree.id == currentNode.id  //如果当前节点是根节点
				|| currentNode.getPreviousSibling() == null  //并且有上一个节点
				|| tree.id == currentNode.getPreviousSibling().id) { //并且上一个节点是根节点
				document.form1.previousButton.style.display = "none";
			} else {
				document.form1.previousButton.style.display = "";
			}
			if(currentNode.getNextSibling() || currentNode.folder) {
				document.form1.nextButton.style.display = "";
			} else {
				document.form1.nextButton.style.display = "none";
			}
		}
	}
}
