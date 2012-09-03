var bind = function(obj,func){
	return function(){
		func.apply(obj,arguments);
	};
};
var get=function(className, tag ,root) {
        if(arguments.length==1){
        	root = (arguments[0]) ? (typeof arguments[0]=="string")?document.getElementById(arguments[0]):arguments[0] : null || document;
        	return root;
        }
        root = (root) ? (typeof root=="string")?document.getElementById(root):root : null || document; 
        if (!root) {return [];}
        var nodes = [],elements = root.getElementsByTagName(tag);
        for (var i = 0, len = elements.length; i < len; ++i) {
        	if(elements[i].className==className||className=="*"){
                nodes[nodes.length] = elements[i];
            }
        }
        return nodes;
}
var addEventHandler=function(obj, type, func) {
		if(!obj){return;}
		var doOn=function(o){
			if(o.addEventListener){o.addEventListener(type, func, false);}
			else if(o.attachEvent){o.attachEvent("on" + type, func);}
			else{o["on" + type] = func;}
		}
		var IsArray=function(v){ 
			try{ 
				var a = v[0]; 
				return typeof(a) != "undefined"; 
			}catch(e){ 
				return false; 
			} 
		}
		if(obj.tagName!='SELECT'&&IsArray(obj)){
			for(var i=0,oLen=obj.length;i<oLen;i++){
				doOn(obj[i],type.func);
			}
		}else{
			doOn(obj);
		}
};
var batch=function(str){
		var trim=function(html){
			return (html=='')?'':html.replace(/(^\s*)|(\s*$)/g, "");
		}
		return (trim(str)=='')?'':trim(str.replace(/<.*?>/g,""));
};
var Lang={'zh_CN':{
			'id':'ID：',
			'tip':'请输入好友的姓名(支持拼音首字母输入)',
			'del':'移除',
			'empty':'姓名不在好友列表哦，请重新输入',
			'select':'请选择好友！'
		},
		'en_US':{}
};
var show=function(id){
	this.fribox= get('fribox','div',id)[0];
	this.input = get('*','input',id)[0];
	this.select = get('selbtn','a',id)[0];
	this.listbox=get('allfriend','div',id)[0];
	this.friendList=get('friendList','ul',id)[0];
	this.tip=null;
	this.friendData=null;
	this.index=0;
	this.autobox=null;
	this.keyword=null;
	this.flag=undefined;
	this.selAll=get('sl','a',id)[0];
	this.showgroup=get('group');
	this.submit=get('btn_qd');
	this.getEvent=function(e){
		var event=e||window.event;
		if(event){return event.srcElement||event.target;}
	};
};
show.prototype = {
	create:function(tag,cName,pNode){
		pNode==(pNode)?pNode:document.body;
		var oo=document.createElement(tag);
			if(cName){oo.className=cName;}
			pNode.appendChild(oo);
		return oo;
		
	},
	setFocus:function(e){
		var target=this.getEvent(e);//獲取鼠標當前點擊對象
		if(target.tagName=='HTML'&&this.autobox!=null){//如果點擊頁面和不在自動提示下拉層上，則隱藏下拉層
			return this.autobox.style.display='none';
		}
		while(target&&target.tagName!="BODY"){
			if(target==this.fribox||target==this.autobox){//向上遍歷，如果點擊位於好友選擇框或者自動提示層上，則出發onfocus事件
				return this.input.focus();
			}
			target=target.parentNode;
		}
	},
	showDefault:function(e){
    	this.select.className='selbtn';
    	this.input.value='';
    	this.listbox.style.display='none';
    	if(this.autobox!=null){this.autobox.style.display='none';}
    	if(this.tip==null){//如果提示層不存在，則自動創建并顯示
    		this.tip=new this.create('div','default',this.fribox);
    		this.tip.innerHTML=Lang.zh_CN.tip;
			this.tip.style.backgroundColor='#eee';
    	}
    	this.tip.style.top=this.input.offsetTop+this.input.offsetHeight+6+'px';
    	this.tip.style.left=this.input.offsetLeft-3+'px';
    	this.fribox.parentNode.style.borderLeft=this.fribox.parentNode.style.borderTop='1px #000000 solid';
	    this.tip.style.display='';
	},
	hideDefault:function(e){
    	if(this.tip!=null){//如果提示層存在，則隱藏
    		this.fribox.parentNode.style.borderLeft=this.fribox.parentNode.style.borderTop='1px #ffffff solid';
	    	this.tip.style.display='none';
    	}
	},
	FullData:function(jsonStr){
		var data=eval(jsonStr);
		this.friendData=data;
	},
	getData:function(oo){
		var _data=this.friendData,group='';
		if(oo!=undefined){//如果經過好友選擇框觸發，則加入此條件
				group=oo.options[oo.selectedIndex].value;
				this.friendList.innerHTML='';
				var selAll=get('selall');
				//selAll.style.display=(group=='')?'none':'inline';
		}
		if(_data.length>0){
			var oFrag=document.createDocumentFragment();//創建文檔碎片對象，在所有文檔遍歷完成之後一次性插入到頁面顯示，避免多次頁面操作產生頁面閃爍
			var slist=get('fri','div',this.fribox),flag;
			this.friendList.innerHTML='';
			for(var i=0,dLen=_data.length;i<dLen;i++){
				if(group!=''&&_data[i].type!=group){continue;}
				flag=this.strip(get('fri','div',this.fribox),_data[i].real_name[0]);
				var oNod=document.createTextNode(_data[i].real_name[0]);
				var isChk=(flag==false)?' checked':'';
				var oInput=(document.all)?document.createElement('<input type="checkbox"'+isChk+'/>'):document.createElement('input');
					oInput.type='checkbox';
					oInput.setAttribute('title',Lang.zh_CN.id+_data[i].uid);
				var oLi=document.createElement('li');
					oLi.setAttribute('title',Lang.zh_CN.id+_data[i].uid);
					oLi.appendChild(oInput);
					oLi.appendChild(oNod);
					oFrag.appendChild(oLi);
					if(flag==false){oInput.checked=true;}
			}
			this.friendList.appendChild(oFrag);
		}
	},
	getGroup:function(e){
		var target=this.getEvent(e);
		this.flag=false;
		this.getData(target);
	},
	showfriendbox:function(e){
    	var target=this.getEvent(e);
    	target.blur();
    	this.getData();//讀取并創建好友列表
    	this.select.className=(this.select.className=='on')?'selbtn':'on';
    	this.listbox.style.top=this.fribox.clientHeight+6+'px';
    	if(this.autobox!=null){this.autobox.style.display='none';}
    	this.listbox.style.display=(this.listbox.style.display=='block')?'none':'block';
	},
	
	//添加已选择好友项
	insertDIV:function(nod,cNod,cId){
		//单击x删除好友项
		var delObj=function(e){
				var evt=e||window.event;
		    	var target=evt.srcElement||evt.target;
				target.parentNode.parentNode.removeChild(target.parentNode);
				that.output();
		}
		//img对象存在删除按钮
		var img=document.createElement('img');
			img.src='static/image/home/del.gif';
			img.setAttribute('alt',Lang.zh_CN.del,true);
			addEventHandler(img,'click',bind(this,delObj));//創建刪除按鈕并綁定事件
		//input对象存放好友id
		var input=document.createElement('input');
			input.type='hidden';
			input.id='fid';
			input.name='fid';
			input.value=cId;
		//div对象存在好友名称和id，以及删除按钮
		var odiv=document.createElement('div');
			odiv.className='fri';
			odiv.innerHTML=cNod+'  ';
			odiv.appendChild(img);
			odiv.appendChild(input);
		//容器内添加div对象
		nod.appendChild(odiv);
		img=odiv=null;
	},
	
	strip:function(from,to){
		for(var j=0,slen=from.length;j<slen;j++){
			var oInner=batch(from[j].innerHTML);
			if(oInner==to){
				return flag=false;
			}
		}
		return true;
	},
	doSubmit:function(e){
    	var flist=this.friendList.getElementsByTagName('input'),slist=get('fri','div',this.fribox),selList=[],friList=[]; 	
    	if(!flist||flist.length==0){alert(Lang.zh_CN.select);return;}
		friList.length=0;
		var size=0;
    	for(var i=0,flen=flist.length;i<flen;i++){
    		if(flist[i].checked==true){    		
    			friList[size]=new Array(2);
    			friList[size][0]=flist[i].title.substring(flist[i].title.indexOf("：")+1);
    			friList[size][1]=batch(flist[i].parentNode.innerHTML);
    			size++;
    			//friList.push(batch(flist[i].parentNode.innerHTML));
    		}
    	}
		var oFrag=document.createDocumentFragment(),flag;
		if(slist.length>0){
			for(var i=0,olen=friList.length;i<olen;i++){
				flag=this.strip(get('fri','div',this.fribox),friList[i][1]);
				if(flag==true){this.insertDIV(oFrag,friList[i][1],friList[i][0]);}
			}
		}else{
			for(var i=0,olen=friList.length;i<olen;i++){
				this.insertDIV(oFrag,friList[i][1],friList[i][0]);
			}
		}
		this.fribox.insertBefore(oFrag,this.input);
		this.listbox.style.display='none';
		this.output();
	},
	over:function(e){
    	this.submit.className='pn pnc';
	},
	out:function(e){
    	this.submit.className='pn pnc';
	},
	autoComplete:function(e){
    	if(!this.autobox){
    		this.autobox=new this.create('div','autobox',this.fribox);
    	}
		var target=this.getEvent(e);
		if (target&&this.autobox!=null){this.keyDown(target,e);}
    	this.autobox.style.top=this.input.offsetTop+this.input.offsetHeight+6+'px';
    	this.autobox.style.left=this.input.offsetLeft-3+'px';
    	this.fribox.parentNode.style.borderLeft=this.fribox.parentNode.style.borderTop='1px #000000 solid';
	    this.tip.style.display='none';
	},
	run:function(path){
		var allfriList=get('*','div',this.autobox);
		if(allfriList.length==0){return;}
		if(path=='down'){
			if(this.index<=0){
				this.index=0;
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="#F2F6FB";}
			}else if(this.index>=allfriList.length){
				if(allfriList[this.index-1]){allfriList[this.index-1].style.backgroundColor="";}
				this.index=0;
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="#F2F6FB";}
			}else{
				if(allfriList[this.index-1]){allfriList[this.index-1].style.backgroundColor="";}
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="#F2F6FB";}
			}
			this.index++;
		}else{
			this.index--;
			if(this.index<=0){
				this.index=0;
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="";}
				this.index=allfriList.length;
				if(allfriList[this.index-1]){allfriList[this.index-1].style.backgroundColor="#F2F6FB";}
			}else if(this.index>=allfriList.length-1){
				this.index=allfriList.length-1;
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="";}
				if(allfriList[this.index-1]){allfriList[this.index-1].style.backgroundColor="#F2F6FB";}
			}else{
				if(allfriList[this.index]){allfriList[this.index].style.backgroundColor="";}
				if(allfriList[this.index-1]){allfriList[this.index-1].style.backgroundColor="#F2F6FB";}
			}
		}
	},
	keyDown:function(iobj,e){
		if (document.all){
			var keycode=event.keyCode;
		}else{
			var keycode=e.which;
		}
		var target=this.getEvent(e),searchtxt=target.value.replace(/\s/ig,''),innerdivhtml="",alldiv=isyouselect=0,sdiv=this.autobox,nowsel=true;
		switch(keycode){
		case 40:/*鍵盤下按鍵*/
			this.run('down');
			break;
		case 38:/*鍵盤上按鍵*/
			this.run('up');
			break;
		default:
			var selFri=get('fri','div',this.fribox);
			if(keycode==8){
				if(this.keyword==null&&selFri[selFri.length-1]){
						this.fribox.removeChild(selFri[selFri.length-1]);
						this.input.focus();
						this.output();
						return false;
				}
			}
			if (searchtxt==""){
				sdiv.innerHTML=Lang.zh_CN.tip;
				sdiv.style.display="block";
				this.keyword=null;
				this.output();
				return false;
			}
			nowsel=true;
			var _data=this.friendData;
			for (i=0;i<_data.length;i++){
				if (_data[i].real_name[0].substr(0,searchtxt.length).toLowerCase()==searchtxt||_data[i].real_name[1].substr(0,searchtxt.length).toLowerCase()==searchtxt||_data[i].real_name[2].substr(0,searchtxt.length).toLowerCase()==searchtxt){
					alldiv++;
					innerdivhtml=innerdivhtml+"<div>"+_data[i].real_name[0]+"</div>";
					if ((searchtxt==_data[i].real_name[0]||searchtxt==_data[i].real_name[1]||searchtxt==_data[i].real_name[2]) && isyouselect==0){
							this.index++;
							isyouselect=1;
					}
				}
			}
			if (alldiv!=0 && innerdivhtml!=""){
				sdiv.innerHTML=innerdivhtml;
				var autoList=get('*','div',this.autobox),_strip=this.strip;
				var _getEvent=this.getEvent,_input=this.input,_autobox=this.autobox,_fribox=this.fribox,_insert=this.insertDIV,_output=this.output;
				var mouseover=function(e){
					var tar=_getEvent(e);
					tar.style.backgroundColor="#F2F6FB";
				}
				var mouseout=function(e){
					var tar=_getEvent(e);
					tar.style.backgroundColor=""; 
				}
				var  doSelect=function(e,theObj){
					var tar=(theObj)?theObj:_getEvent(e),oInner=batch(tar.innerHTML),oInnerId=tar.title.substring(tar.title.indexOf("：")+1);
					var slist=get('fri','div',_fribox),oFrag=document.createDocumentFragment();
					var flag=_strip(slist,oInner);
					if(flag==true){
						_insert(oFrag,oInner,oInnerId);
						_fribox.insertBefore(oFrag,_input);
					}
					_input.value='';
					sdiv.style.display='none';
					_output();
				}
				if(keycode==13){
					this.index--;
					this.index=(this.index<0)?0:this.index;
					var allfriList=get('*','div',this.autobox);
					if(allfriList.length==1){this.index=0;}
					doSelect(e,allfriList[this.index]);
					this.keyword=null;
					this.index=0;
					this.output();
					return;
				}
				addEventHandler(autoList,'mouseover',mouseover);
				addEventHandler(autoList,'mouseout',mouseout);
				addEventHandler(autoList,'click',doSelect);
				sdiv.style.backgroundColor='#fff';
			}
			else{
				  if (searchtxt){
					sdiv.innerHTML=Lang.zh_CN.empty;
					sdiv.style.backgroundColor='#eee';
				  }
				this.index=0;
			}
			sdiv.style.display="block";
			this.keyword=this.input.value;
			return false;
		}
	},
	output:function(){
		//var outdata=(get('output'))?get('output'):null; //用戶名子列表輸出對象
		//if(outdata==null){return;}
		var valList=[],slist=get('fri','div',this.fribox);
		for(var i=0,len=slist.length;i<len;i++){
			var inputs=slist[i].childNodes;
			for(var iLoop = 0; iLoop < inputs.length; iLoop ++) {
				 var input = inputs[iLoop];
			  	 if ("INPUT" == input.nodeName){   
  					valList.push(input.value);	
  				 }
			}
		}
		valList=valList.join(',');
		//(outdata.tagName!='INPUT')?outdata.innerHTML=valList:outdata.value=valList;
		document.getElementById('selectIds').value=valList;	
		//alert(document.getElementById('selectIds').value);
	},
	selectAll:function(e){
		if(!this.flag){this.flag=true;}
		else{
			this.flag=(this.flag==true)?false:true;
		}
		var boxAll=this.friendList.getElementsByTagName('input');
		for(var i=0,olen=boxAll.length;i<olen;i++){
			boxAll[i].checked=this.flag;
		}
		return this;
	},
	init:function(e){
		that=this;
		addEventHandler(document,'click',bind(this,this.setFocus));
		addEventHandler(this.input,'focus',bind(this,this.showDefault));
		addEventHandler(this.input,'blur',bind(this,this.hideDefault));
		addEventHandler(this.input,'keyup',bind(this,this.autoComplete));
		addEventHandler(this.select,'click',bind(this,this.showfriendbox));
		addEventHandler(this.submit,'click',bind(this,this.doSubmit));
		addEventHandler(this.submit,'mouseover',bind(this,this.over));
		addEventHandler(this.submit,'mouseout',bind(this,this.out));
		addEventHandler(this.showgroup,'change',bind(this,this.getGroup));
		addEventHandler(this.selAll,'click',bind(this,this.selectAll));
	}
};