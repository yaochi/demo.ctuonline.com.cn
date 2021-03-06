/*
	[UCenter Home] (C) 2007-2009 Comsenz Inc.
	$Id: portal_diy.js 11322 2010-05-28 09:23:09Z monkey $
*/
    var drag = new Drag();
	drag.extend({
		'getBlocksTimer' : '',
		'blocks' : [],
		'blockDefaultClass' : [{'key':'选择样式','value':''},{'key':'模块样式1','value':'xbs_1'},{'key':'模块样式2','value':'xbs xbs_2'},{'key':'模块样式3','value':'xbs xbs_3'},{'key':'模块样式4','value':'xbs xbs_4'},{'key':'模块样式5','value':'xbs xbs_5'},{'key':'模块样式6','value':'xbs xbs_6'},{'key':'模块样式7','value':'xbs xbs_7'}],
		'frameDefaultClass' : [{'key':'选择样式','value':''},{'key':'无边框框架','value':'xfs xfs_nbd'},{'key':'框架样式1','value':'xfs xfs_1'},{'key':'框架样式2','value':'xfs xfs_2'},{'key':'框架样式3','value':'xfs xfs_3'},{'key':'框架样式4','value':'xfs xfs_4'},{'key':'框架样式5','value':'xfs xfs_5'}],
		setDefalutMenu : function () {
			this.addMenu('default','标题','drag.openTitleEdit(event)');
			this.addMenu('default','样式','drag.openStyleEdit(event)');
			this.addMenu('default', '删除', 'drag.removeBlock(event)');
			this.addMenu('block', '属性', 'drag.openBlockEdit(event)');
			this.addMenu('block', '更新', 'drag.blockForceUpdate(event)');
		//	this.addMenu('frame', '导出', 'drag.frameExport(event)');
	//		this.addMenu('tab', '导出', 'drag.frameExport(event)');
	//this.addMenu('block', '数据', 'drag.openBlockEdit(event,"data")');

	
		},
		toggleContent : function (e) {
			if ( typeof e !== 'string') {
				e = Util.event(e);
				var id = e.aim.id.replace('cmd_','');
			} else {
				var id = e;
			}
			var obj = this.getObjByName(id);
			if (obj instanceof Block || obj instanceof Tab) {
				Util.toggleEle(id+'_content');
			} else {
				var col = obj.columns;
				for (var i in col) {
					Util.toggleEle($(i));
				}
			}
		},
		openBlockEdit : function (e,tab) {
			e = Util.event(e);
			tab = tab ? tab : 'setting';
			var bid = e.aim.id.replace('cmd_portal_block_','');
			this.removeMenu();
			//url中添加random参数值(随机数)，解决第二次点击模块中属性菜单时不发起get请求。
			showWindow('showblock', 'portal.php?mod=portalcp&ac=block&fid='+getArgs()["fid"]+'&op=edit&bid='+bid+'&tab='+tab+'&tpl='+document.diyform.template.value+'&random='+(Math.round(Math.random()*10000)), 'get', -1);
		},
		getDiyClassName : function (id,index) {
			var obj = this.getObjByName(id);
			var ele = $(id);
			var eleClassName = ele.className.replace(/ {2,}/g,' ');
			var className = '',srcClassName = '';
			if (obj instanceof Block) {
				className = eleClassName.split(this.blockClass+' ');
				srcClassName = this.blockClass;
			} else if(obj instanceof Tab) {
				className = eleClassName.split(this.tabClass+' ');
				srcClassName = this.tabClass;
			} else if(obj instanceof Frame) {
				className = eleClassName.split(this.frameClass+' ');
				srcClassName = this.frameClass;
			}
			if (index != null && index<className.length) {
				className = className[index].replace(/^ | $/g,'');
			} else {
				className.push(srcClassName);
			}
			return className;
		},
		getOption : function (arr,value) {
			var html = '';
			for (var i in arr) {
				if (typeof arr[i] == 'function') continue;
				var selected = arr[i]['value'] == value ? ' selected="selected"' : '';
				html += '<option value="'+arr[i]['value']+'"'+selected+'>'+arr[i]['key']+'</option>';
			}
			return html;
		},
		getRule : function (selector,attr) {
			selector = spaceDiy.checkSelector(selector);
			var value = (!selector || !attr) ? '' : spaceDiy.styleSheet.getRule(selector, attr);
			return value;
		},
		openStyleEdit : function (e) {
			e = Util.event(e);
			var id = e.aim.id.replace('cmd_','');
			var obj = this.getObjByName(id);
			var objType = obj instanceof Block ? 1 : 0;
			var bgcolor = '',bgimage = '',bgrepeat = '',html = '',diyClassName = '',fontcolor = '',fontsize = '',linkcolor = '',linkfontsize = '';
			var bdtstyle = '',bdtwidth = '',bdtcolor = '',bdrstyle = '',bdrwidth = '',bdrcolor = '',bdbstyle = '',bdbwidth = '',bdbcolor = '',bdlstyle = '',bdlwidth = '',bdlcolor = '';
			var margint = '',marginr = '',marginb = '',marginl = '',cmargint = '',cmarginr = '',cmarginb = '',cmarginl ='';
			var selector = '#'+id;
			bgcolor = this.getRule(selector, 'backgroundColor');
			bgimage = this.getRule(selector, 'backgroundImage');
			bgrepeat = this.getRule(selector, 'backgroundRepeat');
			bgimage = bgimage && bgimage != 'none' ? Util.trimUrl(bgimage) : '';

			fontcolor = this.getRule(selector+' .content', 'color');
			fontsize = this.getRule(selector+' .content', 'fontSize').replace('px','');
			var linkSelector = spaceDiy.checkSelector(selector+ ' .content a');
			linkcolor = this.getRule(linkSelector, 'color');
			linkfontsize = this.getRule(linkSelector, 'fontSize').replace('px','');
			fontcolor = Util.formatColor(fontcolor);
			linkcolor = Util.formatColor(linkcolor);

			bdtstyle = this.getRule(selector, 'borderTopStyle');
			bdrstyle = this.getRule(selector, 'borderRightStyle');
			bdbstyle = this.getRule(selector, 'borderBottomStyle');
			bdlstyle = this.getRule(selector, 'borderLeftStyle');

			bdtwidth = this.getRule(selector, 'borderTopWidth');
			bdrwidth = this.getRule(selector, 'borderRightWidth');
			bdbwidth = this.getRule(selector, 'borderBottomWidth');
			bdlwidth = this.getRule(selector, 'borderLeftWidth');

			bdtcolor = this.getRule(selector, 'borderTopColor');
			bdrcolor = this.getRule(selector, 'borderRightColor');
			bdbcolor = this.getRule(selector, 'borderBottomColor');
			bdlcolor = this.getRule(selector, 'borderLeftColor');

			bgcolor = Util.formatColor(bgcolor);
			bdtcolor = Util.formatColor(bdtcolor);
			bdrcolor = Util.formatColor(bdrcolor);
			bdbcolor = Util.formatColor(bdbcolor);
			bdlcolor = Util.formatColor(bdlcolor);

			margint = this.getRule(selector, 'marginTop').replace('px','');
			marginr = this.getRule(selector, 'marginRight').replace('px','');
			marginb = this.getRule(selector, 'marginBottom').replace('px','');
			marginl = this.getRule(selector, 'marginLeft').replace('px','');

			if (objType == 1) {
				selector = selector + ' .content';
				cmargint = this.getRule(selector, 'marginTop').replace('px','');
				cmarginr = this.getRule(selector, 'marginRight').replace('px','');
				cmarginb = this.getRule(selector, 'marginBottom').replace('px','');
				cmarginl = this.getRule(selector, 'marginLeft').replace('px','');
			}

			diyClassName = this.getDiyClassName(id,0);

			var widtharr = [];
			for (var k=0;k<11;k++) {
				var key = k+'px';
				widtharr.push({'key':key,'value':key});
			}

			var bigarr = [];
			for (var k=0;k<31;k++) {
				key = k+'px';
				bigarr.push({'key':key,'value':key});
			}
			var repeatarr = [{'key':'平铺','value':'repeat'},{'key':'不平铺','value':'no-repeat'},{'key':'横向平铺','value':'repeat-x'},{'key':'纵向平铺','value':'repeat-y'}];
			var stylearr = [{'key':'无样式','value':'none'},{'key':'实线','value':'solid'},{'key':'点线','value':'dotted'},{'key':'虚线','value':'dashed'}];
			var table = '<table class="tfm">';
			table += '<tr><th>字体</th><td><input class="ps" id="fontsize" value="'+fontsize+'" size="7" /> px <input type="text" id="fontcolor" value="'+fontcolor+'" size="7" />';
			table += getColorPalette(id+'_fontPalette', 'fontcolor' ,fontcolor)+'</td></tr>';
			table += '<tr><th>链接</th><td><input class="ps" id="linkfontsize" value="'+linkfontsize+'" size="7" /> px <input type="text" id="linkcolor" value="'+linkcolor+'" size="7" />';
			table += getColorPalette(id+'_linkPalette', 'linkcolor' ,linkcolor)+'</td></tr>';

			var ulclass = 'borderul', opchecked = '';
			if (bdtwidth != '' || bdtcolor != '' ) {
				ulclass = 'borderula';
				opchecked = ' checked="checked"';
			}

			table += '<tr><th>边框</th><td><ul id="borderul" class="'+ulclass+'">';
			table += '<li><label>上</label><select class="ps" id="bdtwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdtwidth)+'</select>';
			table += ' <select class="ps" id="bdtstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdtstyle)+'</select>';
			table += ' 颜色<input type="text" id="bdtcolor" value="'+bdtcolor+'" size="7" /><label class="y"><input id="borderop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'borderul\').className = $(\'borderul\').className == \'borderul\' ? \'borderula\' : \'borderul\'"> 分别设置</label>';
			table += getColorPalette(id+'_bdtPalette', 'bdtcolor' ,bdtcolor)+'</li>';

			table += '<li class="bordera"><label>右</label><select class="ps" id="bdrwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdrwidth)+'</select>';
			table += ' <select class="ps" id="bdrstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdrstyle)+'</select>';
			table += ' 颜色<input type="text" id="bdrcolor" value="'+bdrcolor+'" size="7" />';
			table += getColorPalette(id+'_bdrPalette', 'bdrcolor' ,bdrcolor)+'</li>';

			table += '<li class="bordera"><label>下</label><select class="ps" id="bdbwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdbwidth)+'</select>';
			table += ' <select class="ps" id="bdbstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdbstyle)+'</select>';
			table += ' 颜色<input type="text" id="bdbcolor" value="'+bdbcolor+'" size="7" />';
			table += getColorPalette(id+'_bdbPalette', 'bdbcolor' ,bdbcolor)+'</li>';

			table += '<li class="bordera"><label>左</label><select class="ps" id="bdlwidth" ><option value="">大小</option>'+this.getOption(widtharr,bdlwidth)+'</select>';
			table += ' <select class="ps" id="bdlstyle" ><option value="">样式</option>'+this.getOption(stylearr,bdlstyle)+'</select>';
			table += ' 颜色<input type="text" id="bdlcolor" value="'+bdlcolor+'" size="7" />';
			table += getColorPalette(id+'_bdlPalette', 'bdlcolor' ,bdlcolor)+'</li>';
			table += '</ul></td></tr>';

			bigarr = [];
			for (k=-20;k<31;k++) {
				key = k+'px';
				bigarr.push({'key':key,'value':key});
			}

			ulclass = 'borderul', opchecked = '';
			if (margint != '') {
				ulclass = 'borderula';
				opchecked = ' checked="checked"';
			}

			table += '<tr><th>外边距</th><td><div id="margindiv" class="'+ulclass+'"><span><label>上</label><input class="ps" id="margint" value="'+margint+'" size="2"/>px </span>';
			table += '<span class="bordera"><label>右</label><input class="ps" id="marginr" value="'+marginr+'" size="2" />px </span>';
			table += '<span class="bordera"><label>下</label><input class="ps" id="marginb" value="'+marginb+'" size="2" />px </span>';
			table += '<span class="bordera"><label>左</label><input class="ps" id="marginl" value="'+marginl+'" size="2" />px </span>';
			table += '<label class="y"><input id="marginop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'margindiv\').className = $(\'margindiv\').className == \'borderul\' ? \'borderula\' : \'borderul\'"> 分别设置</label><div></td></tr>';

			if (objType == 1) {

				ulclass = 'borderul', opchecked = '';
				if (cmargint != '') {
					ulclass = 'borderula';
					opchecked = ' checked="checked"';
				}

				table += '<tr><th>内边距</th><td><div id="cmargindiv" class="'+ulclass+'"><span><label>上</label><input class="ps" id="cmargint" value="'+cmargint+'" size="2" />px </span>';
				table += '<span class="bordera"><label>右</label><input class="ps" id="cmarginr" value="'+cmarginr+'" size="2" />px </span>';
				table += '<span class="bordera"><label>下</label><input class="ps" id="cmarginb" value="'+cmarginb+'" size="2" />px </span>';
				table += '<span class="bordera"><label>左</label><input class="ps" id="cmarginl" value="'+cmarginl+'" size="2" />px </span>';
				table += '<label class="y"><input id="cmarginop" type="checkbox" value="1" class="pc"'+opchecked+' onclick="$(\'cmargindiv\').className = $(\'cmargindiv\').className == \'borderul\' ? \'borderula\' : \'borderul\'"> 分别设置</label><div></td></tr>';
			}
			table += '<tr><th>背景颜色</th><td><input type="text" id="bgcolor" value="'+bgcolor+'" size="7" />';
			table += getColorPalette(id+'_bgcPalette', 'bgcolor' ,bgcolor)+'</td></tr>';
			table += '<tr><th>背景图片</th><td><input type="text" id="bgimage" value="'+bgimage+'" size="25" /> <select class="ps" id="bgrepeat" >'+this.getOption(repeatarr,bgrepeat)+'</select></td></tr>';
			var classarr = objType == 1 ? this.blockDefaultClass : this.frameDefaultClass;
			table += '<tr><th>指定class</th><td><input type="text" id="diyClassName" value="'+diyClassName+'" size="12" /> <select class="ps" id="bgrepeat" onchange="$(\'diyClassName\').value=this.value;" >'+this.getOption(classarr, diyClassName)+'</select></td></tr>';
			table += '</table>';

			var wname = objType ? '模块' : '框架';
			html = '<div class="c" style="width:450px;position:relative;">'+table+'</div>';
			var h = '<h3 class="flb"><em>编辑'+wname+'样式</em><span><a href="javascript:;" class="flbc" onclick="drag.closeStyleEdit(\''+id+'\');return false;" title="关闭">\n\
				关闭</a></span></h3>';
			var f = '<p class="o pns"><button onclick="drag.saveStyle(\''+id+'\');drag.closeStyleEdit(\''+id+'\');" class="pn pnc" value="true">\n\
				<strong>确定</strong></button><button onclick="drag.closeStyleEdit(\''+id+'\')" class="pn" value="true"><strong>取消</strong></button></p>';
			this.removeMenu(e);
			showWindow('eleStyle',h + html + f, 'html', 0);
		},
		closeStyleEdit : function (id) {
			this.deleteFrame([id+'_bgcPalette',id+'_bdtPalette',id+'_bdrPalette',id+'_bdbPalette',id+'_bdlPalette',id+'_fontPalette',id+'_linkPalette']);
			hideWindow('eleStyle');
		},
		saveStyle : function (id) {
			var className = this.getDiyClassName(id);
			var diyClassName = $('diyClassName').value;
			$(id).className = diyClassName+' '+className[2]+' '+className[1];
			var obj = this.getObjByName(id);
			var objType = obj instanceof Block ? 1 : 0;

			if (objType == 1) this.saveBlockClassName(id,diyClassName);

			var selector = '#'+id;
			var random = Math.random();
			spaceDiy.setStyle(selector, 'background-color', $('bgcolor').value, random);
			var bgimage = $('bgimage').value && $('bgimage') != 'none' ? Util.url($('bgimage').value) : '';
			var bgrepeat = bgimage ? $('bgrepeat').value : '';
			if ($('bgcolor').value != '' && bgimage == '') bgimage = 'none';
			spaceDiy.setStyle(selector, 'background-image', bgimage, random);
			spaceDiy.setStyle(selector, 'background-repeat', bgrepeat, random);
			spaceDiy.setStyle(selector+' .content', 'color', $('fontcolor').value, random);
			spaceDiy.setStyle(selector+' .content', 'font-size', this.formatValue('fontsize'), random);
			spaceDiy.setStyle(spaceDiy.checkSelector(selector+' .content a'), 'color', $('linkcolor').value, random);
			var linkfontsize = parseInt($('linkfontsize').value);
			linkfontsize = isNaN(linkfontsize) ? '' : linkfontsize+'px';
			spaceDiy.setStyle(spaceDiy.checkSelector(selector+' .content a'), 'font-size', this.formatValue('linkfontsize'), random);

			if ($('borderop').checked) {
				var bdtwidth = $('bdtwidth').value,bdrwidth = $('bdrwidth').value,bdbwidth = $('bdbwidth').value,bdlwidth = $('bdlwidth').value;
				var bdtstyle = $('bdtstyle').value,bdrstyle = $('bdrstyle').value,bdbstyle = $('bdbstyle').value,bdlstyle = $('bdlstyle').value;
				var bdtcolor = $('bdtcolor').value,bdrcolor = $('bdrcolor').value,bdbcolor = $('bdbcolor').value,bdlcolor = $('bdlcolor').value;
			} else {
				bdlwidth = bdbwidth = bdrwidth = bdtwidth = $('bdtwidth').value;
				bdlstyle = bdbstyle = bdrstyle = bdtstyle = $('bdtstyle').value;
				bdlcolor = bdbcolor = bdrcolor = bdtcolor = $('bdtcolor').value;
			}
			spaceDiy.setStyle(selector, 'border-top-width', bdtwidth, random);
			spaceDiy.setStyle(selector, 'border-right-width', bdrwidth, random);
			spaceDiy.setStyle(selector, 'border-bottom-width', bdbwidth, random);
			spaceDiy.setStyle(selector, 'border-left-width', bdlwidth, random);

			spaceDiy.setStyle(selector, 'border-top-style', bdtstyle, random);
			spaceDiy.setStyle(selector, 'border-right-style', bdrstyle, random);
			spaceDiy.setStyle(selector, 'border-bottom-style', bdbstyle, random);
			spaceDiy.setStyle(selector, 'border-left-style', bdlstyle, random);

			spaceDiy.setStyle(selector, 'border-top-color', bdtcolor, random);
			spaceDiy.setStyle(selector, 'border-right-color', bdrcolor, random);
			spaceDiy.setStyle(selector, 'border-bottom-color', bdbcolor, random);
			spaceDiy.setStyle(selector, 'border-left-color', bdlcolor, random);

			if ($('marginop').checked) {
				var margint = this.formatValue('margint'),marginr = this.formatValue('marginr'), marginb = this.formatValue('marginb'), marginl = this.formatValue('marginl');
			} else {
				marginl = marginb = marginr = margint = this.formatValue('margint');
			}
			spaceDiy.setStyle(selector, 'margin-top',margint, random);
			spaceDiy.setStyle(selector, 'margin-right', marginr, random);
			spaceDiy.setStyle(selector, 'margin-bottom', marginb, random);
			spaceDiy.setStyle(selector, 'margin-left', marginl, random);

			if (objType == 1) {
				if ($('cmarginop').checked) {
					var cmargint = this.formatValue('cmargint'),cmarginr = this.formatValue('cmarginr'), cmarginb = this.formatValue('cmarginb'), cmarginl = this.formatValue('cmarginl');
				} else {
					cmarginl = cmarginb = cmarginr = cmargint = this.formatValue('cmargint');
				}
				selector = selector + ' .content';
				spaceDiy.setStyle(selector, 'margin-top', cmargint, random);
				spaceDiy.setStyle(selector, 'margin-right', cmarginr, random);
				spaceDiy.setStyle(selector, 'margin-bottom', cmarginb, random);
				spaceDiy.setStyle(selector, 'margin-left', cmarginl, random);
			}

			this.setClose();
		},
		formatValue : function(id) {
			var value = '';
			if ($(id)) {
				value = parseInt($(id).value);
				value = isNaN(value) ? '' : value+'px';
			}
			return value;
		},
		saveBlockClassName : function(id,className){
			if (!$('saveblockclassname')){
				var dom  = document.createElement('div');
				dom.innerHTML = '<form id="saveblockclassname" method="post" action=""><input type="hidden" name="classname" value="" />\n\
					<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="saveclassnamesubmit" value="true"/></form>';
				$('append_parent').appendChild(dom.childNodes[0]);
			}
			$('saveblockclassname').action = 'portal.php?mod=portalcp&fid='+getArgs()["fid"]+'&ac=block&op=saveblockclassname&bid='+id.replace('portal_block_','');
			document.forms.saveblockclassname.classname.value = className;
			ajaxpost('saveblockclassname','ajaxwaitid');
		},
		closeTitleEdit : function (fid) {
			this.deleteFrame(fid+'bgPalette_0');
			for (var i = 0 ; i<=10; i++) {
				this.deleteFrame(fid+'Palette_'+i);
			}
			hideWindow('frameTitle');
		},
		openTitleEdit : function (e, fgid) {
			if (typeof e == 'object') {
				e = Util.event(e);
				var fid = e.aim.id.replace('cmd_','');
			} else {
				fid = e;
			}
			var obj = this.getObjByName(fid); //fid 框架div id
			var titlename = obj instanceof Block ? '模块' : '框架';
			var repeatarr = [{'key':'平铺','value':'repeat'},{'key':'不平铺','value':'no-repeat'},{'key':'横向平铺','value':'repeat-x'},{'key':'纵向平铺','value':'repeat-y'}];

			var len = obj.titles.length;
			var bgimage = obj.titles.style && obj.titles.style['background-image'] ? obj.titles.style['background-image'] : '';
			bgimage = bgimage != 'none' ? Util.trimUrl(bgimage) : '';
			var bgcolor = obj.titles.style && obj.titles.style['background-color'] ? obj.titles.style['background-color'] : '';
			bgcolor = Util.formatColor(bgcolor);
			var bgrepeat = obj.titles.style && obj.titles.style['background-repeat'] ? obj.titles.style['background-repeat'] : '';

			var common = '<table class="tfm">';
			common += '<tr><th>背景图片:</th><td><input type="text" id="titleBgImage" value="'+bgimage+'" /> <select class="ps" id="titleBgRepeat" >'+this.getOption(repeatarr,bgrepeat)+'</select></td></tr>';
			common += '<tr><th>背景颜色:</th><td><input type="text" id="titleBgColor" value="'+bgcolor+'" size="7" />';
			common += getColorPalette(fid+'bgPalette_0', 'titleBgColor' ,bgcolor)+'</td></tr>';
			if (obj instanceof Tab) {
				var switchArr = [{'key':'点击','value':'click'},{'key':'滑过','value':'mouseover'}];
				var switchType = obj.titles['switchType'] ? obj.titles['switchType'][0] : 'click';
				common += '<tr><th>切换类型:</th><td><select class="ps" id="switchType" >'+this.getOption(switchArr,switchType)+'</select></td></tr>';
			}
			common += '</table><hr class="l">';
			var li = '';
			li += '<div id="titleInput_0"><table class="tfm"><tr><th>'+titlename+'标题:</th><td><input type="text" id="titleText_0" value="`title`" /></td></tr>';
			li += '<tr><th>链接:</th><td><input type="text" id="titleLink_0" value="`link`" /></td></tr>';
			li += '<tr><th>图片:</th><td><input type="text" id="titleSrc_0" value="`src`" /></td></tr>';
			li += '<tr><th>位置:</th><td><select id="titleFloat_0" ><option value="" `left`>居左</option><option value="right" `right`>居右</option></select>';
			li += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;偏移量:<input type="text" id="titleMargin_0" value="`margin`" size="7" />px</td></tr>';
			li += '<tr><th>字体:</th><td><select class="ps" id="titleSize_0" ><option value="">大小</option>`size`</select>';
			li += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;颜&nbsp;&nbsp;&nbsp;色:<input type="text" id="titleColor_0" value="`color`" size="7" />';
			li += getColorPalette(fid+'Palette_0', 'titleColor_0' ,'`color`');
			li += '</td></tr><tr><td colspan="2"><hr class="l"></td></tr></table></div>';
			var html = '';
			if (obj.titles['first']) {
				html = this.getTitleHtml(obj, 'first', li);
			}
			for (var i = 0; i < len; i++ ) {
				html += this.getTitleHtml(obj, i, li);
			}
			if (!html) {
				var bigarr = [];
				for (var k=7;k<27;k++) {
					var key = k+'px';
					bigarr.push({'key':key,'value':key});
				}
				var ssize = this.getOption(bigarr,ssize);
				html = li.replace('`size`', ssize).replace(/`\w+`/g, '');
			}

			var c = len + 1;
			html = '<div class="c" style="width:450px;height:400px; overflow:auto;"><div id="addTitleInput" class="pointer" onclick="drag.addTitleInput('+c+');">添加新标题</div><div id="titleEdit">'+html+common+'</div></div>';
			var h = '<h3 class="flb"><em>编辑'+titlename+'标题</em><span><a href="javascript:;" class="flbc" onclick="drag.closeTitleEdit(\''+fid+'\');return false;" title="关闭">\n\
				关闭</a></span></h3>';
			var f = '<p class="o pns"><button onclick="drag.saveTitleEdit(\''+fid+'\','+fgid+');drag.closeTitleEdit(\''+fid+'\');" class="pn pnc" value="true">\n\
				<strong>确定</strong></button><button onclick="drag.closeTitleEdit(\''+fid+'\')" class="pn" value="true"><strong>取消</strong></button></p>';
			this.removeMenu(e);
			showWindow('frameTitle',h + html + f, 'html', 0);
		},
		getTitleHtml : function (obj, i, li) {
			var shtml = '',stitle = '',slink = '',sfloat = '',ssize = '',scolor = '',margin = '',src = '';
			var c = i == 'first' ? '0' : i+1;
			stitle = obj.titles[i]['text'] ? obj.titles[i]['text'] : '';
			slink = obj.titles[i]['href'] ? obj.titles[i]['href'] : '';
			sfloat = obj.titles[i]['float'] ? obj.titles[i]['float'] : '';
			margin = obj.titles[i]['margin'] ? obj.titles[i]['margin'] : '';
			ssize = obj.titles[i]['font-size'] ? obj.titles[i]['font-size']+'px' : '';
			scolor = obj.titles[i]['color'] ? obj.titles[i]['color'] : '';
			src = obj.titles[i]['src'] ? obj.titles[i]['src'] : '';

			var bigarr = [];
			for (var k=7;k<27;k++) {
				var key = k+'px';
				bigarr.push({'key':key,'value':key});
			}
			ssize = this.getOption(bigarr,ssize);

			shtml = li.replace(/_0/g, '_' + c).replace('`title`', stitle).replace('`link`', slink).replace('`size`', ssize).replace('`src`',src);
			var left = sfloat == '' ? 'selected' : '';
			var right = sfloat == 'right' ? 'selected' : '';
			scolor = Util.formatColor(scolor);
			shtml = shtml.replace(/`color`/g, scolor).replace('`left`', left).replace('`right`', right).replace('`margin`', margin);
			return shtml;
		},
		addTitleInput : function (c) {
			if (c  > 10) return false;
			var pre = $('titleInput_'+(c-1));
			var dom = document.createElement('div');
			dom.className = 'tfm';
			var exp = new RegExp('_'+(c-1), 'g');
			dom.id = 'titleInput_'+c;
			dom.innerHTML = pre.innerHTML.replace(exp, '_'+c);
			Util.insertAfter(dom, pre);
			$('addTitleInput').onclick = function () {drag.addTitleInput(c+1)};
		},
		saveTitleEdit : function (fid, fgid) {
			var obj = this.getObjByName(fid);
			var ele  = $(fid);
			var children = ele.childNodes;
			var title = first = '';
			var hastitle = 0;
			var c = 0;
			for (var i in children) {
				if (typeof children[i] == 'object' && Util.hasClass(children[i], this.titleClass)) {
					title = children[i];
					break;
				}
			}
			if (title) {
				var arrDel = [];
				for (var i in title.childNodes) {
					if (typeof title.childNodes[i] == 'object' && Util.hasClass(title.childNodes[i], this.titleTextClass)) {
						first = title.childNodes[i];
						this._createTitleHtml(first, c);
						if (first.innerHTML != '') hastitle = 1;
					} else if (typeof title.childNodes[i] == 'object' && !Util.hasClass(title.childNodes[i], this.moveableObject)) {
						arrDel.push(title.childNodes[i]);
					}
				}
				for (var i = 0; i < arrDel.length; i++) {
					title.removeChild(arrDel[i]);
				}
			} else {
				var className = obj instanceof Tab ? this.tabClass : obj instanceof Frame ? 'frame' : obj instanceof Block ? 'block' : '';
				title = document.createElement('div');
				title.className = className + 'title' + ' '+ this.titleClass;
				ele.insertBefore(title,ele.firstChild);
			}
			if (!first) {
				var first = document.createElement('span');
				first.className = this.titleTextClass;
				this._createTitleHtml(first, c);
				if (first.innerHTML != '') {
					title.insertBefore(first, title.firstChild);
					hastitle = 1;
				}
			}
			while ($('titleText_'+(++c))) {
				var dom = document.createElement('span');
				dom.className = 'subtitle';
				this._createTitleHtml(dom, c);
				if (dom.innerHTML != '') {
					if (dom.innerHTML) Util.insertAfter(dom, first);
					first = dom;
					hastitle = 1;
				}
			}

			var titleBgImage = $('titleBgImage').value;
			titleBgImage = titleBgImage && titleBgImage != 'none' ? Util.url(titleBgImage) : '';
			if ($('titleBgColor').value != '' && titleBgImage == '') titleBgImage = 'none';
			title.style['backgroundImage'] = titleBgImage;
			if (titleBgImage) {
				title.style['backgroundRepeat'] = $('titleBgRepeat').value;
			}
			title.style['backgroundColor'] = $('titleBgColor').value;
			if ($('switchType')) {
				title.switchType = [];
				title.switchType[0] = $('switchType').value ? $('switchType').value : 'click';
				title.setAttribute('switchtype',title.switchType[0]);
			}

			obj.titles = [];
			if (hastitle == 1) {
				this._initTitle(obj,title);
			} else {
				if (!(obj instanceof Tab)) title.parentNode.removeChild(title);
				title = '';
				this.initPosition();
			}
			if (obj instanceof Block) this.saveBlockTitle(fid,title, fgid);
			this.setClose();

		},
		_createTitleHtml : function (ele,tid) {
			var html = '',img = '';
			tid = '_' + tid ;
			var ttext = $('titleText'+tid).value;
			var tlink = $('titleLink'+tid).value;
			var tfloat = $('titleFloat'+tid).value;
			var tmargin_ = tfloat != '' ? tfloat : 'left';
			var tmargin = $('titleMargin'+tid).value;
			var tsize = $('titleSize'+tid).value;
			var tcolor = $('titleColor'+tid).value;
			var src = $('titleSrc'+tid).value;
			var divStyle = 'float:'+tfloat+';margin-'+tmargin_+':'+tmargin+'px;font-size:'+tsize  ;  //可以在这里设置自定义内容
			var aStyle = 'color:'+tcolor+';';
			
			if (src) {
				img = '<img class="vm" src="'+src+'" alt="'+ttext+'" />';
			}
			if (ttext || img) {
				if (tlink) {
					Util.setStyle(ele, divStyle);
					html = '<a href='+tlink+' style="'+aStyle+'">'+img+ttext+'</a>';
				} else {
					Util.setStyle(ele, divStyle+';'+aStyle);
					html = img+ttext;
				}
			}
			ele.innerHTML = html;
			return true;
		},
		saveBlockTitle : function (id,title, fid) {
			if (!$('saveblocktitle')){
				var dom  = document.createElement('div');
				dom.innerHTML = '<form id="saveblocktitle" method="post" action=""><input type="hidden" name="title" value="" />\n\
					<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="savetitlesubmit" value="true"/></form>';
				$('append_parent').appendChild(dom.childNodes[0]);
			}
			$('saveblocktitle').action = 'portal.php?mod=portalcp&ac=block&op=saveblocktitle&fid='+fid+'&bid='+id.replace('portal_block_','');
			var html = !title ? '' : title.outerHTML;
			document.forms.saveblocktitle.title.value = html;
			ajaxpost('saveblocktitle','ajaxwaitid');
		},
		removeBlock : function (e, flag) {
			if ( typeof e !== 'string') {
				e = Util.event(e);
				var id = e.aim.id.replace('cmd_','');
			} else {
				var id = e;
			}
			if ($(id) == null) return false;
			var obj = this.getObjByName(id);
			if (!flag) {
				if (!confirm('您确实要删除吗,删除以后将不可恢复')) return false;
			}
			if (obj instanceof Block) {
				this.delBlock(id);
			} else if (obj instanceof Frame) {
				this.delFrame(obj);
			}
			$(id).parentNode.removeChild($(id));
			this.setClose();
			this.initPosition();
			this.initChkBlock();
		},
		delBlock : function (bid) {
			spaceDiy.removeCssSelector('#'+bid);
			this.stopSlide(bid);
		},
		delFrame : function (frame) {
			spaceDiy.removeCssSelector('#'+frame.name);
			for (var i in frame['columns']) {
				if (frame['columns'][i] instanceof Column) {
					var children = frame['columns'][i]['children'];
					for (var j in children) {
						if (children[j] instanceof Frame) {
							this.delFrame(children[j]);
						} else if (children[j] instanceof Block) {
							this.delBlock(children[j]['name']);
						}
					}
				}
			}
			this.setClose();
		},
		initChkBlock : function (data) {
			if (typeof name == 'undefined' || data == null ) data = this.data;
			if ( data instanceof Frame) {
				this.initChkBlock(data['columns']);
			} else if (data instanceof Block) {
				var el = $('chk'+data.name);
				if (el != null) el.checked = true;
			} else if (typeof data == 'object') {
				for (var i in data) {
					this.initChkBlock(data[i]);
				}
			}
		},

			//blockname 组件编码
		getBlockData : function (blockname, fid) {
			var bid = this.dragObj.id;
			var eleid = bid;
			if (bid.indexOf('portal_block_') != -1) {
				eleid = 0;
			}else {
				bid = 0;
			}
			//alert('portal.php?mod=portalcp&ac=block&fid='+getArgs()["fid"]+'&op=block&classname='+blockname+'&fid='+fid+'&bid='+bid+'&eleid='+eleid+'&tpl='+document.diyform.template.value);
			showWindow('showblock', 'portal.php?mod=portalcp&ac=block&fid='+getArgs()["fid"]+'&op=block&classname='+blockname+'&fid='+fid+'&bid='+bid+'&eleid='+eleid+'&tpl='+document.diyform.template.value,'get',-1);
			drag.initPosition();
			this.fn = '';
			return true;
		},
		stopSlide : function (id) {
			if (typeof slideshow.entities == 'undefined') return false;
			for (var i = 0; i<slideshow.entities.length; i++) {
				if (slideshow.entities[i].blockid == id) {
					clearTimeout(slideshow.entities[i].timer);
					slideshow.entities.splice(i,1);
					break;
				}
			}
		},
		blockForceUpdate : function (e,all) {
			if ( typeof e !== 'string') {
				e = Util.event(e);
				var id = e.aim.id.replace('cmd_','');
			} else {
				var id = e;
			}
			if ($(id) == null) return false;
			var bid = id.replace('portal_block_', '');
			var bcontent = $(id+'_content');
			if (!bcontent) {
				bcontent = document.createElement('div');
				bcontent.id = id+'_content';
				bcontent.className = 'content';
			}
			this.stopSlide(id);

			var height = Util.getFinallyStyle(bcontent, 'height');
			bcontent.style.lineHeight = height == 'auto' ? '' : (height == '0px' ? '20px' : height);
			bcontent.innerHTML = '<center>正在加载内容...</center>';
			var x = new Ajax();
			x.get('portal.php?mod=portalcp&ac=block&op=getblock&fid='+getArgs()["fid"]+'&forceupdate=1&inajax=1&bid='+bid+'&tpl='+document.diyform.template.value, function(s) {
				var obj = document.createElement('div');
				obj.innerHTML = s;
				bcontent.parentNode.removeChild(bcontent);
				$(id).innerHTML = obj.childNodes[0].innerHTML;
				evalscript(s);
				drag.initPosition();
				if (all) {drag.getBlocks();}
			});
		},

			//diy 导出
		frameExport : function (e) {
			var flag = true;
			if (drag.isChange) {
				flag = confirm('您已经做过修改，请保存后再做导出，否则导出的数据将不包括您这次所做的修改。');
			}
			if (!flag) return false;
			if ( typeof e == 'object') {
				e = Util.event(e);
				var frame = e.aim.id.replace('cmd_','');
			} else {
				frame = e == undefined ? '' : e;
			}
			if (!$('frameexport')){
				//alert(1);
				var dom  = document.createElement('div');
				dom.innerHTML = '<form id="frameexport" method="post" action="" target="_blank"><input type="hidden" name="frame" value="" />\n\
					<input type="hidden" name="tpl" value="'+document.diyform.template.value+'" />\n\
					<input type="hidden" name="formhash" value="'+document.diyform.formhash.value+'" /><input type="hidden" name="exportsubmit" value="true"/></form>';
				$('append_parent').appendChild(dom.childNodes[0]);
			}
			$('frameexport').action = 'portal.php?mod=portalcp&ac=diy&op=export&fid='+getArgs()["fid"];
			document.forms.frameexport.frame.value = frame;
			//alert(frame);
			document.forms.frameexport.submit();
		},
		openFrameImport : function () {
			showWindow('showimport','portal.php?mod=portalcp&ac=diy&fid='+getArgs()["fid"]+'&op=import&tpl='+document.diyform.template.value, 'get');
		},
		endBlockForceUpdateBatch : function () {
			if($('allupdate')) {
				$('allupdate').innerHTML = '已更新完成。';
				$('fwin_dialog_submit').style.display = '';
			}
			this.initPosition();
		},
		getBlocks : function () {
			if (this.blocks.length == 0) {
				this.endBlockForceUpdateBatch();
			}
			if (this.blocks.length > 0) {
				var cur = this.blocksLen - this.blocks.length;
				if($('allupdate')) {
					$('allupdate').innerHTML = '共<span style="color:blue">'+this.blocksLen+'</span>个模块,正在更新第<span style="color:red">'+cur+'</span>个,已完成<span style="color:red">'+(parseInt(cur / this.blocksLen * 100)) + '%</span>';
					var bid = 'portal_block_'+this.blocks.pop();
					this.blockForceUpdate(bid,true);
				}
			}
		},
		blockForceUpdateBatch : function (blocks) {
			if (blocks) {
				this.blocks = blocks;
			} else {
				this.initPosition();
				this.blocks = this.allBlocks;
			}
			this.blocksLen = this.blocks.length;
			showDialog('<div id="allupdate" style="width:350px;line-height:28px;">开始更新...</div>','confirm','更新模块数据', '', true, 'drag.endBlockForceUpdateBatch()');
			$('fwin_dialog_submit').style.display = 'none';
			setTimeout(function(){drag.getBlocks()},500);
		},



//=====完全复制上面的代码========================================
		//切换模板
		blockForceUpdateBatch4Template : function (blocks) {
			if (blocks) {
				this.blocks = blocks;
			} else {
				this.initPosition();
				this.blocks = this.allBlocks;
			}
			this.blocksLen = this.blocks.length;
			showDialog('<div id="allupdate" style="width:350px;line-height:28px;">开始切换...</div>','confirm','切换模板', '', true, 'drag.endBlockForceUpdateBatch4Template()');
			$('fwin_dialog_submit').style.display = 'none';
			setTimeout(function(){drag.getBlocks4Template()},500);
		},
		getBlocks4Template : function () {
			if (this.blocks.length == 0) {
				this.endBlockForceUpdateBatch4Template();
			}
			if (this.blocks.length > 0) {
				var cur = this.blocksLen - this.blocks.length;
				if($('allupdate')) {
					$('allupdate').innerHTML = '共<span style="color:blue">'+this.blocksLen+'</span>个模块,正在更新第<span style="color:red">'+cur+'</span>个,已完成<span style="color:red">'+(parseInt(cur / this.blocksLen * 100)) + '%</span>';
					var bid = 'portal_block_'+this.blocks.pop();
					this.blockForceUpdate4Template(bid,true);
				}
			}
		},
		endBlockForceUpdateBatch4Template : function () {
			if($('allupdate')) {
				$('allupdate').innerHTML = '已完成切换。';
				$('fwin_dialog_submit').style.display = '';
			}
			this.initPosition();
		},
		blockForceUpdate4Template : function (e,all) {
			if ( typeof e !== 'string') {
				e = Util.event(e);
				var id = e.aim.id.replace('cmd_','');
			} else {
				var id = e;
			}
			if ($(id) == null) return false;
			var bid = id.replace('portal_block_', '');
			var bcontent = $(id+'_content');
			if (!bcontent) {
				bcontent = document.createElement('div');
				bcontent.id = id+'_content';
				bcontent.className = 'content';
			}
			this.stopSlide(id);

			var height = Util.getFinallyStyle(bcontent, 'height');
			bcontent.style.lineHeight = height == 'auto' ? '' : (height == '0px' ? '20px' : height);
			bcontent.innerHTML = '<center>正在加载内容...</center>';
			var x = new Ajax();
			x.get('portal.php?mod=portalcp&ac=block&op=getblock&fid='+getArgs()["fid"]+'&forceupdate=1&inajax=1&bid='+bid+'&tpl='+document.diyform.template.value, function(s) {
				var obj = document.createElement('div');
				obj.innerHTML = s;
				bcontent.parentNode.removeChild(bcontent);
				$(id).innerHTML = obj.childNodes[0].innerHTML;
				evalscript(s);
				drag.initPosition();
				if (all) {drag.getBlocks4Template();}
			});
		},

		//清除，但不提示
		clearAll_noConfirm : function () {
			for (var i in this.data) {
				for (var j in this.data[i]) {
					if (this.data[i][j].name!=null && this.data[i][j].name.indexOf('_temp')<0) {
						this.delFrame(this.data[i][j]);
						$(this.data[i][j].name).parentNode.removeChild($(this.data[i][j].name));
					}
				}
			}
			this.initPosition();
			this.setClose();
		},

//=====end完全复制上面的代码========================================



		clearAll : function () {
			if (!confirm('您确实要清空页面上所在DIY数据吗,清空以后将不可恢复')) return false;
			for (var i in this.data) {
				for (var j in this.data[i]) {
					if (this.data[i][j].name!=null && this.data[i][j].name.indexOf('_temp')<0) {
						this.delFrame(this.data[i][j]);
						$(this.data[i][j].name).parentNode.removeChild($(this.data[i][j].name));
					}
				}
			}
			this.initPosition();
			this.setClose();
		},
		createObj : function (e,objType,contentType, fid) {
			if (objType == 'block' && !this.checkHasFrame()) {alert("提示：未找到框架，请先添加框架。");spaceDiy.getdiy('frame');return false;}
			e = Util.event(e);
			if(e.which != 1 ) {return false;}
			var html = '',offWidth = 0;
			if (objType == 'frame') {
				html =  this.getFrameHtml(contentType);
				//alert(html);
				offWidth = 600;
			} else if (objType == 'block') {
				html =  this.getBlockHtml(contentType);
				offWidth = 200;
				this.fn = function (e) {drag.getBlockData(contentType, fid);};
			} else if (objType == 'tab') {
				html = this.getTabHtml(contentType);
				offWidth = 300;
			}
			var ele = document.createElement('div');		
			ele.innerHTML = html;
			ele = ele.childNodes[0];
			document.body.appendChild(ele);
			this.dragObj = this.overObj = ele;			
			if (!this.getTmpBoxElement()) return false;			
			var scroll = Util.getScroll();
			this.dragObj.style.position = 'absolute';
			this.dragObj.style.left = e.clientX + scroll.l - 60 + "px";
			this.dragObj.style.top = e.clientY + scroll.t - 10 + "px";
			this.dragObj.style.width = offWidth + 'px';
			this.dragObj.style.cursor = 'move';
			this.dragObj.lastMouseX = e.clientX;
			this.dragObj.lastMouseY = e.clientY;

			
			//this.dragObj.style.height = '200px';//可以设置拖下来高度

						
			Util.insertBefore(this.tmpBoxElement,this.overObj);
			Util.addClass(this.dragObj,this.moving);
			this.dragObj.style.zIndex = 500 ;
			this.scroll = Util.getScroll();
			this.newFlag = true;
			var _method = this;
			document.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
			window.onscroll = function(){Drag.prototype.resetObj.call(_method, e);};
			document.onmousemove = function (e){Drag.prototype.drag.call(_method, e);};
			document.onmouseup = function (e){Drag.prototype.dragEnd.call(_method, e);};		
		},
		getFrameHtml : function (type) {
			var id = 'frame'+Util.getRandom(6);
			var className = [this.frameClass,this.moveableObject].join(' ');
			className = className + ' cl frame-' + type;
			var str = '<div id="'+id+'" class="'+className+'">';
			str += '<div id="'+id+'_title" class="'+this.titleClass+' '+this.frameTitleClass+'"><span class="'+this.titleTextClass+'">'+type+'框架</span></div>';
			var cols = type.split('-');
			var clsl='',clsc='',clsr='';
			clsl = ' frame-'+type+'-l';
			clsc = ' frame-'+type+'-c';
			clsr = ' frame-'+type+'-r';
			var len = cols.length;
			if (len == 1) {
				str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsc+'"></div>';
			} else if (len == 2) {
				str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsl+ '"></div>';
				str += '<div id="'+id+'_center" class="'+this.moveableColumn+clsr+ '"></div>';
			} else if (len == 3) {
				str += '<div id="'+id+'_left" class="'+this.moveableColumn+clsl+'"></div>';
				str += '<div id="'+id+'_center" class="'+this.moveableColumn+clsc+'"></div>';
				str += '<div id="'+id+'_right" class="'+this.moveableColumn+clsr+'"></div>';
			}
			str += '</div>';
			return str;
		},
		getTabHtml : function () {
			var id = 'tab'+Util.getRandom(6);
			var className = [this.tabClass,this.moveableObject].join(' ');
			className = className + ' cl';
			var titleClassName = [this.tabTitleClass, this.titleClass, this.moveableColumn, 'cl'].join(' ');
			var str = '<div id="'+id+'" class="'+className+'">';
			str += '<div id="'+id+'_title" class="'+titleClassName+'"><span class="'+this.titleTextClass+'">tab标签</span></div>';
			str += '<div id="'+id+'_content" class="'+this.tabContentClass+'"></div>';
			str += '</div>';
			return str;
		},
		getBlockHtml : function () {
			var id = 'block'+Util.getRandom(6);
			var str = '<div id="'+id+'" class="block move-span"></div>';
			str += '</div>';
			return str;
		},
		setClose : function () {
			if (!this.isChange) {
				window.onbeforeunload = function() {
					return '您的数据已经修改,退出将无法保存您的修改。';
				};
			}
			spaceDiy.disablePreviewButton();
			Util.show($('savecachemsg'));
			this.isChange = true;
		},
		clearClose : function () {
			this.isChange = false;
			this.isClearClose = true;
			window.onbeforeunload = function () {};
		},

//========================================================
		//add by songsp 2010-11-22 17:23:08  
		//切换模板
		//temp_code 模板编码  fid:专区id  (可以不使用)
		changeTemplate : function (temp_code,fid){
			//if (!confirm('您确实要切换模板吗,切换后之前DIY的内容将被删除')) return false;
			
			/*		
			for (var i in this.data) {
				
				for (var j in this.data[i]) {
					
					if (this.data[i][j].name!=null && this.data[i][j].name.indexOf('_temp')<0) {
						this.delFrame(this.data[i][j]);
						$(this.data[i][j].name).parentNode.removeChild($(this.data[i][j].name));
						//document.getElementById(this.data[i][j].name).parentNode.removeChild(document.getElementById(this.data[i][j].name));
					}
				}
			}
			this.initPosition();
			this.setClose();
			
			*/

//alert(1);
			//
			//$('append_parent').innerHTML = "";
			//return;
			//alert('portal.php?mod=portalcp&ac=diy&fid='+getArgs()["fid"]+'&op=changeTemplate&tpl='+document.diyform.template.value);
			showWindow('showimport','portal.php?mod=portalcp&ac=diy&fid='+getArgs()["fid"]+'&tempcode='+ temp_code +'&op=changeTemplate&tpl='+document.diyform.template.value, 'get');
	
		}

	});
//========================================================

	var spaceDiy = new DIY();
	spaceDiy.extend({
		save : function (optype) {
			optype = typeof optype == 'undefined' ? '' : optype;
			if (optype == 'savecache' && !drag.isChange) {return false;}
			var arr = document.diyform.template.value.split('/');
			if (!optype) {
				if (arr[1].split(':')[0] != 'portal_topic_content') {
					if (document.diyform.template.value.indexOf(':') > -1 && !document.selectsave) {
						var schecked = '',dchecked = '';
						if (document.diyform.savemod.value == '1') {
							dchecked = ' checked';
						} else {
							schecked = ' checked';
						}/*
						showDialog('<form name="selectsave" action="" method="get"><lable><input type="radio" value="0" name="savemod"'+schecked+' />应用于此类全部页面</lable>\n\
						<lable><input type="radio" value="1" name="savemod"'+dchecked+' />只应用于本页面</lable></form>','notice', '', spaceDiy.save);*/
                        showDialog('<form name="selectsave" action="" method="get">确定保存该页面？<input type="hidden" name="savemod" value="1" /></form>','notice', '', spaceDiy.save);
						return false;
					}
					if (document.selectsave) {
						if (document.selectsave.savemod.checked) {
							document.diyform.savemod.value = document.selectsave.savemod.value;
						}
					}
				}
			} else if (optype == 'savecache') {
				if (!drag.isChange) return false;
			} else if (optype =='preview') {
				if (!$('preview_form')) {
					var dom = document.createElement('div');
					var search = '';
					var sarr = location.search.replace('?','').split('&');
					for (var i = 0;i<sarr.length;i++){
						var kv = sarr[i].split('=');
						if (kv.length>1 && kv[0] != 'diy') {
							search += '<input type="hidden" value="'+kv[1]+'" name="'+kv[0]+'" />';
						}
					}
					search +=  '<input type="hidden" value="yes" name="preview" />';
					dom.innerHTML = '<form action="'+location.href+'" target="_bloak" method="get" id="preview_form">'+search+'</form>';
					var form = dom.getElementsByTagName('form');
					$('append_parent').appendChild(form[0]);
				}
				if (drag.isChange) {
					optype = 'savecache';
					document.diyform.rejs.value = '1';
				} else {
					$('preview_form').submit();
					return false;
				}
			}
			document.diyform.action = document.diyform.action.replace(/[&|\?]inajax=1/, '');
			document.diyform.optype.value = optype;
			document.diyform.spacecss.value = spaceDiy.getSpacecssStr();
			document.diyform.style.value = spaceDiy.style;
			document.diyform.layoutdata.value = drag.getPositionStr();
			document.diyform.gobackurl.value = spaceDiy.cancelDiyUrl();
			drag.clearClose();
			if (optype == 'savecache') {
				document.diyform.handlekey.value = 'diyform';
                
				ajaxpost('diyform','ajaxwaitid','ajaxwaitid','onerror');
			} else {
				document.diyform.submit();
			}
		},
		cancelDiyUrl : function () {
			return location.href.replace(/[\?|\&]diy\=yes/g,'').replace(/[\?|\&]preview=yes/,'');
		},
		cancel : function () {

			if (drag.isClearClose) {
				showDialog('<div style="line-height:28px;">是否保留暂存数据？<br />按确定按钮将保留暂存数据，按取消按钮将删除暂存数据。</div>','confirm','保留暂存数据', function(){location.href = spaceDiy.cancelDiyUrl();}, true, function(){window.onunload=function(){spaceDiy.cancelDIY()};location.href = spaceDiy.cancelDiyUrl();});
			} else {
				location.href = this.cancelDiyUrl();
			}

		},
		recover : function() {
			drag.clearClose();
			if (confirm('您确定要恢复到上一版本保存的结果吗？')) {
				document.diyform.recover.value = '1';
				document.diyform.gobackurl.value = location.href.replace(/(\?diy=yes)|(\&diy=yes)/,'').replace(/[\?|\&]preview=yes/,'');
				document.diyform.submit();
			}
		},
		goonDIY : function () {
			if ($('prefile').value == '1') {
				showDialog('<div style="line-height:28px;">是否继续暂存数据的DIY？<br />按确定按钮将打开暂存数据并DIY，按取消按钮将删除暂存数据。</div>','confirm','继续DIY', function(){location.replace(location.href+'&preview=yes');}, true, 'spaceDiy.cancelDIY()');
			} else if (location.search.indexOf('preview=yes') > -1) {
				this.enablePreviewButton();
			} else {
				this.disablePreviewButton();
			}
			setInterval(function(){spaceDiy.save('savecache');},180000);
		},
		enablePreviewButton : function () {
			if ($('preview')){
				$('preview').className = '';
				$('diy_preview').onclick = function () {spaceDiy.save('preview');return false;};
				$('savecachemsg').innerHTML = '页面已经暂存';
				Util.show($('savecachemsg'))
			}
		},
		disablePreviewButton : function () {
			if ($('preview')) {
				$('preview').className = 'unusable';
				$('diy_preview').onclick = function () {return false;};
				$('savecachemsg').innerHTML = '<a href="javascript:;" onclick="spaceDiy.save(\'savecache\');return false;" onfocus="this.blur();">暂存</a>';
				Util.hide($('savecachemsg'))
			}
		},
		cancelDIY : function () {
			this.disablePreviewButton();
			document.diyform.optype.value = 'canceldiy';
			var x = new Ajax();
			x.post($('diyform').action+'&inajax=1','optype=canceldiy&diysubmit=1&template='+document.diyform.template.value+'&savemod='+document.diyform.savemod.value+'&formhash='+document.diyform.formhash.value,function(s){});
		},
		getdiy : function (type, fid) { // DIY 开始、框架、模块选择  。fid：专区id
            if(fid==null){
                fid = getArgs()["fid"];
            }
			if (type) {
				var nav = $('controlnav').children;
				for (var i in nav) {
					if (nav[i].className == 'current') {
						nav[i].className = '';
						var contentid = 'content'+nav[i].id.replace('nav', '');
						if ($(contentid)) $(contentid).style.display = 'none';
					}
				}
				$('nav'+type).className = 'current';
				if (type == 'start' || type == 'frame' || type == 'template') {
					$('content'+type).style.display = 'block';
					return true;
				}
				var para = '&op='+type;
				if (arguments.length > 1) {
					for (var i = 1; i < arguments.length; i++) {
						para += '&' + arguments[i] + '=' + arguments[++i];
					}
				}
				var ajaxtarget = type == 'diy' ? 'diyimages' : '';
				var x = new Ajax();
				x.showId = ajaxtarget;
				//alert('portal.php?mod=portalcp&ac=diy&fid='+getArgs()["fid"]+para+'&inajax=1&ajaxtarget='+ajaxtarget+"&fid="+fid);
				x.get('portal.php?mod=portalcp&ac=diy&fid='+getArgs()["fid"]+para+'&inajax=1&ajaxtarget='+ajaxtarget+"&fid="+fid,function(s, x) {
					if (s) {
						
						if (typeof cpb_frame == 'object' && !BROWSER.ie) {delete cpb_frame;}
						if (!$('content'+type)) {
							var dom = document.createElement('div');
							dom.id = 'content'+type;
							$('controlcontent').appendChild(dom);
						}
						$('content'+type).innerHTML = s;
						$('content'+type).style.display = 'block';
						if (type == 'diy') {
							spaceDiy.setCurrentDiy(spaceDiy.currentDiy);
							if (spaceDiy.styleSheet.rules.length > 0) {
								Util.show('recover_button');
							}
						}

						var evaled = false;
						if(s.indexOf('ajaxerror') != -1) {
							evalscript(s);
							evaled = true;
						}
						if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
							if(x.showId) {
								ajaxupdateevents($(x.showId));
							}
						}
						if(!evaled) evalscript(s);
					}
				});
			}
		}
	});

spaceDiy.goonDIY();
spaceDiy.init();

function succeedhandle_diyform (url, message, values) {
	if (values['rejs']) {
		document.diyform.rejs.value = '';
		parent.$('preview_form').submit();
	}
	spaceDiy.enablePreviewButton();
	return false;
}