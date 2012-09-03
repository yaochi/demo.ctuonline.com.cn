/*
**  动态列表组件
*/


// 全局变量
var flag;             // follow all user group myComments atme
var initNum = 15;     // 一页初始有15条动态
var loadNum = 10;     // 然后动态加载10条动态
var currNum = 0 ;     // 当前动态计数
var currPage = 1;	  // 当前页数
var feedSign;         // 是否已被二次加载过
var typeid = '';      // follow 分组id
var typeStr= [];       // 动态类型   img doing vedio music
var startTime='';        // 搜索  开始时间
var endTime='';          // 搜索  结束时间
var fansNum;          // 当前用户粉丝数
var clickSign = false;// 下一次搜索 置标
var clickTimer        // 搜索点击延时
var dlCount = 0;       // 当前页面动态条数
var newFeed = false;    // ajaxPOST新的动态
var currFeedID;        // 当前动态id
var transBolgFlag = false; //评论同时是否转发
var commentType = '';	//我发表的评论 mycommentfeed | 我收到的评论 myreceivecommentfeed
var space_uid = UID;		//取得php页面上的uid
var textarea = function(){	//获取评论框
		if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 8) {
			return 'textarea.growfield';
		}else{
			return 'textarea[name=message]';
		}
	}();

function load_wait(){
	jQuery("#feed").html('<div id="load_wait" style="text-align:center; margin:0px auto;"><br><br><img src="'+IMGDIR+'/loading.gif" /></br></br></div>');
	jQuery(".mn_home .bm").height(jQuery("#wp").outerHeight() + 500);
}
function clear_load_wait(){
	jQuery("#load_wait").remove();
	jQuery(".mn_home .bm").height("auto");
}

function gettime(timestamp){
	var strdate = '';
	var datenow =new Date();
	var s = timestamp*1000;
	var datebefor =  new Date(s);
	var date3=datenow.getTime()-datebefor.getTime()+12000;
	var differyear=Math.floor(date3/(365*24*3600*1000));
	var differDays=Math.floor(date3/(24*3600*1000));
	var differHours=Math.floor(date3/(3600*1000));
	var differMinutes=Math.floor(date3/(60*1000));
	var differSeconds=Math.floor(date3/(1000));
	var year=datebefor.getFullYear();
	var month=datebefor.getMonth()+1;
	var day=datebefor.getDate();
	var day2=new Date(datenow.toDateString());
	var day3=new Date(datebefor.toDateString());
	var hours=datebefor.getHours();
	var minutes=datebefor.getMinutes();
	//differSeconds += 12;
	if( minutes<10){ minutes='0'+minutes;}
	if( hours<10){ hours='0'+hours;}
	if(differSeconds<60){
		strdate +=differSeconds+'秒前';
	}else if(differMinutes<60){
		strdate +=differMinutes+'分钟前';
	}else if((Date.parse(day2)-Date.parse(day3))==0){
		strdate +='今天'+hours+':'+minutes;
	}else if( differyear<1){
		strdate +=month+'月'+day+'日'+hours+':'+minutes;
	}else{
		strdate +='2010年'+month+'月'+day+'日'+hours+':'+minutes;
	}
	return strdate;
}

// pagination
jQuery(window).scroll(function(){
	var winheight = jQuery(window).height();
	var docheight = jQuery(document).height();
	var scrollTop = jQuery(window).scrollTop();
	if(scrollTop >(docheight-winheight-100)){
		if(!feedSign){
			//alert(flag);
			feedSign = true;
			if( flag == 'myComments'){
				loadMyComments(currNum,loadNum);
			}else if(flag == 'myReceiveComments'){
				loadMyReceiveComments(currNum,loadNum);
			}else if(flag == 'follow'){
				load_feed(flag,currNum,loadNum,typeid,typeStr,startTime,endTime);
			}else if( flag == 'user'){
				load_feed(flag,currNum,loadNum,typeid,typeStr,'','');
			}else if( flag == 'all'){
				load_feed(flag,currNum,loadNum,'','','','');
			}else if (flag == 'atme'){
				load_atme(currNum,loadNum);
			}else if (flag == 'group'){
				load_feed(flag,currNum,loadNum,typeid,'',startTime,endTime);
			}else if (flag == 'favorite'){
				load_favorite_feed(UID, currNum, loadNum, typeStr, '');
			}else if (flag == 'links'){
				load_links_feed(tag,UID,currNum,loadNum,'','','');
			}
			currNum += loadNum;
		}
	}
})

function pageReset(){
	//typeStr = [];
	currNum = 0;
	currPage = 1;
	jQuery(".pg").html('');
}

function nextPage(){
	load_wait();
	jQuery(window).scrollTop(0);
	if( flag == 'myComments'){
		loadMyComments(currNum,initNum);
	}else if(flag == 'myReceiveComments'){
		loadMyReceiveComments(currNum,initNum);
	}else if(flag == 'follow'){
		load_feed(flag,currNum,initNum,typeid,typeStr,'','');
	}else if(flag == 'user'){
		load_feed(flag,currNum,initNum,typeid,typeStr,'','');
	}else if( flag == 'all'){
		load_feed(flag,currNum,initNum,'','','','');
	}else if (flag == 'atme'){
		load_atme(currNum,initNum);
	}else if (flag == 'group'){
		load_feed(flag,currNum,initNum,typeid,'','','');
	}else if (flag == 'favorite'){
		load_favorite_feed(UID, currNum, initNum, typeStr, '');
	}else if (flag == 'links'){
		load_links_feed(tag,UID,currNum,initNum,'','','');
	}
	currNum += initNum;
	currPage ++;
	feedSign = false;
	jQuery(".pg").html('');
}

function prePage(){
	load_wait();
	jQuery(window).scrollTop(0);
	currNum = currNum - (initNum + loadNum)*2;
	if(currNum<0) currNum = 0;
	if( flag == 'myComments'){
		loadMyComments(currNum,initNum);
	}else if(flag == 'myReceiveComments'){
		loadMyReceiveComments(currNum,initNum);
	}else if(flag == 'follow'){
		load_feed(flag,currNum,initNum,typeid,typeStr,'','');
	}else if(flag == 'user'){
		load_feed(flag,currNum,initNum,typeid,typeStr,'','');
	}else if( flag == 'all'){
		load_feed(flag,currNum,initNum,'','','','');
	}else if (flag == 'atme'){
		load_atme(currNum,initNum);
	}else if (flag == 'group'){
		load_feed(flag,currNum,initNum,typeid,'','','');
	}else if (flag == 'favorite'){
		load_favorite_feed(UID, currNum, initNum, typeStr, '');
	}else if (flag == 'links'){
		load_links_feed(tag,UID,currNum,initNum,'','','');
	}
	currNum += initNum;
	currPage --;
	feedSign = false;
	jQuery(".pg").html('');
}

function load_pager(){
	jQuery(".pg").html('');
	if( currPage == 1 ){
		if( dlCount >= 25 )
		jQuery(".pg").html('<a class="nxt" href="javascript:nextPage();">下一页</a>');
	}else if( dlCount < 25 ){
		jQuery(".pg").html('<span class="pgb"><a class="pre" href="javascript:prePage();">上一页</a></span>');
	}else{
		jQuery(".pg").html('<span class="pgb"><a class="pre" href="javascript:prePage();">上一页</a></span><a class="nxt" href="javascript:nextPage();">下一页</a>');
	}
}
// pagination:END

function setSearchStr(){
	jQuery("#followGroup a").each(function(){
		if(jQuery(this).attr("class") == "xi2 xw1"){
				typeid = jQuery(this).attr("id");
				var str = 'follow_group_';
				typeid = typeid.substring(str.length,typeid.length);
				//alert(typeid);
		}
	});

	typeStr ='';
	jQuery(".pc").each(function(){
		if(jQuery("#search_do").attr("value") == '1'){
			typeStr += '&idtype[]=blogid';
		}
		if( jQuery(this).attr("value") == '1'){
			typeStr += '&idtype[]=';
			typeStr += jQuery(this).attr("idtype");
		}
	});

	startTime = jQuery("#starttime").attr("value");
	if(startTime!=''){startTime = getTimestamp(startTime,false);}
	endTime = jQuery("#endtime").attr("value");
	endTime = getTimestamp(endTime,true);
}

function getTimestamp(string,end){
	var str = string.split("-");
	if(str[1].charAt(0)=='0'){str[1]=str[1].substring(1,str[1].length); }
	if(str[2].charAt(0)=='0'){str[2]=str[2].substring(1,str[2].length); }
	var time = new Date(str[0],str[1]-1,str[2]);
	time = time.getTime()/1000;
	if(end){time += 86400;}
	return time;
}


function searchTimer(){
	clickTimer = setTimeout(function(){
		clickSign = false;
		flag = 'follow';
		feedSign = false;
		typeStr = [];
		setSearchStr();
		//alert(typeStr);
		pageReset();
		load_feed(flag,currNum,initNum,typeid,typeStr,startTime,endTime);
		currNum += initNum;
	},1500);
}
function clearSearchTimer(){
	clearInterval(clickTimer);
}

jQuery(".pc").each(function(){
	jQuery(this).click(function(){
		if( jQuery(this).attr("value") == '1'){
			jQuery(this).attr("value","0");
		}else{
			jQuery(this).attr("value","1");
		}
		if (!clickSign){
			searchTimer();
			clickSign = true;
		}else {
			clearSearchTimer();
			searchTimer();
		}
		jQuery("#feed").html('<div  id="load_wait" style=" text-align:center; margin:0px auto;"><br><br><img src="'+IMGDIR+'/loading.gif" /></br></br></div>');
	});
});

jQuery("#calendar").on("blur", function(){

})

function changtime(){
	if (!clickSign){
			searchTimer();
			clickSign = true;
	}else {
			clearSearchTimer();
			searchTimer();
	}
	jQuery("#feed").html('<div  id="load_wait" style=" text-align:center; margin:0px auto;"><br><br><img src="'+IMGDIR+'/loading.gif" /></br></br></div>');
}


function dl_count(count){
	dlCount = count ? count+initNum : 0;
}



function setFeedId( val ){
	currFeedID = val;
}

function append_Com(data) {
	var createCom = function(t){
		var anchor = jQuery("#"+data[t+'feedid']);
		var comment = jQuery('.comment',anchor);
		var com = jQuery('.comments',anchor);
		if(com.html()== ''){ comment.prepend('<p class="line"></p>');}

		var btn_comment = jQuery('a.btn_comment',anchor);
		if(btn_comment.length==1){
		var comTimes = btn_comment.html();
		var pos = comTimes.indexOf("(");
		var times = 0;
		if(pos>0){ times = comTimes.substring(pos+1,comTimes.length-1);}
		times = times-2+3;
		btn_comment.html('评论('+times+')');
		}

		var strComment = data.message;

		var timestamp = Date.parse(new Date());
		timestamp = timestamp/1000;
		var strtime = gettime(timestamp);

		var str = anchor.attr("commentdata").split("+");

		var	p = repeatstatus((data.anonymity=='-1')),
			ufid = p.ufid,
			perurl = p.perurl,
			imgurl = p.imgurl,
			realname = p.realname;

		var string='<li><div class="imgCom"><a class="perPanel" href="'+perurl+'" target="_blank"><img width="32px" height="32px" src="'+imgurl+'"></a></div><div class="words"><a class="perPanel" href=\''+perurl+'\' target="_blank"><span class="clmr">'+realname+'</span></a><span>:</span>'+strComment+'<em>&nbsp;('+strtime;
		string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline-block;">&nbsp;|&nbsp;</a><a class="minlk commentReply" href="javascript:;" id="'+realname+'_'+data[t+'feedid']+'_'+data[t+'cid']+'" style="display:inline-block;">回复</a>';
		string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline-block;">&nbsp;|&nbsp;</a><a class="minlk commentDelete" href="javascript:;" id="'+str[0]+'+'+data[t+'cid']+'+'+UID+'+'+str[1]+'+'+str[2]+'" style="display:inline-block;">删除</a>';
		string +=')</em><span class="update_time2" style="display:none;">'+timestamp+'</span></div></li>';
		com.append(string);
	}

	if (data.curcid && data.curcid != '') createCom('cur');
	if (data.precid && data.precid != '') createCom('pre');

}

function succeedhandle_Com(url, msg, values) {
	var anchor = jQuery("#"+currFeedID);
	//alert(anchor.attr("id"));
	//alert(anchor.html());
	var comment = jQuery('.comment',anchor);
	var com = jQuery('.comments',anchor);
	if(com.html()== ''){ comment.prepend('<p class="line"></p>');}

	var btn_comment = jQuery('a.btn_comment',anchor);
	if(btn_comment.length==1){
		var comTimes = btn_comment.html();
		var pos = comTimes.indexOf("(");
		var times = 0;
		if(pos>0){ times = comTimes.substring(pos+1,comTimes.length-1);}
		times = times-2+3;
		btn_comment.html('评论('+times+')');
	}

	var strComment = values.message;
	jQuery(textarea,anchor).attr("value","").css("height","16px").removeAttr("readonly").growfield('restart');
	jQuery('input[name=cid]', anchor).val('');

	//var comment = jQuery('input.btn_comment',anchor);
	//comment.attr("disabled","disabled");
	//comment.css("background-position","0px 0px");
	//comment.css("cursor", "auto");

	//var li_num = jQuery('li',com).length;
	//if(li_num == 2 || li_num == 5){jQuery('li:eq(1)',com).remove();}

	var timestamp = Date.parse(new Date());
	timestamp = timestamp/1000;
	var strtime = gettime(timestamp);

	var str = anchor.attr("commentdata").split("+");

	var ck = jQuery('input[name=anonymity]',anchor).attr('checked');
	var	p = repeatstatus((ck=='checked')),
		ufid = p.ufid,
		perurl = p.perurl,
		imgurl = p.imgurl,
		realname = p.realname,
		auth = p.showauth===true?showMyAuth():'';

	var string='<li><div class="imgCom"><a class="perPanel" href="'+perurl+'" target="_blank"><img width="32px" height="32px" src="'+imgurl+'"></a></div><div class="words"><a class="perPanel" href=\''+perurl+'\' target="_blank" added="added"><span class="clmr">'+realname+'</span></a>'+auth+'<span>:</span>'+strComment+'<em>&nbsp;('+strtime;
	string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline-block;">&nbsp;|&nbsp;</a><a class="minlk commentReply" href="javascript:;" id="'+realname+'_'+currFeedID+'_'+values.cid+'" style="display:inline-block;">回复</a>';
	string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline-block;">&nbsp;|&nbsp;</a><a class="minlk commentDelete" href="javascript:;" id="'+str[0]+'+'+values.cid+'+'+UID+'+'+str[1]+'+'+str[2]+'" style="display:inline-block;">删除</a>';
	string +=')</em><span class="update_time2" style="display:none;">'+timestamp+'</span></div></li>';
	com.append(string);

	load_trans_feed();
	jQuery(".W_checkbox",anchor).attr("checked",false);
	transBolgFlag = false;
}
function errorhandle_Com(msg, values){
	showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>"+msg+"</span>", 1500);
	var f = jQuery('#feedComment_' + currFeedID);
	disComment(f, 'remove');
}
function errorhandle_detailCom(msg, values){
	showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>"+msg+"</span>", 1500);
	var f = jQuery('#detailComment');
	disComment(f, 'remove');
}
function errorhandle_qcpic(msg, values){
	showGrowl("<span class='pc_icn_error z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>"+msg+"</span>", 1500);
	var f = jQuery('#commentPanel');
	disComment(f, 'remove');
}

function setTransBlog(){
	if(transBolgFlag == false){transBolgFlag = true;
	}else{transBolgFlag = false;}
}

function load_trans_feed(){
	if(transBolgFlag){
		load_feed(flag,0,1,'','','','',true);
		currNum++;
	}
}

function feed_msie(target){
	if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
		jQuery(".pic .smallimg:last-child", target).css("padding-right", "0");
		jQuery(".other .buttons a:last-child", target).css({'background':'none', 'padding-right':'0'});
	}
}

function load_feed(type,num,shownum,typeid,idtype,startdatetime,enddatetime,newFeed,func,feedid){

	if(newFeed) {
		if (type=='follow' && REPEATID > 0) { //在‘个人动态’下用马甲添加了一条动态
			showGrowl("<p class='hm'><span class='pc_icn_mark' style='display: inline-block;'></span><span class='xw1 xs2 mlmgroup'>发布成功</span></p>"
					+ "<p class='mtn'>您可在 <strong>[专区动态]</strong> <strong>[最新动态]</strong> 中找到专区马甲发表的动态</p>", 5000);
			return false;
		} else if (type=='group' && REPEATID == 0) { //在‘专区动态’下用真身添加了一条动态
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>发布成功</span>", 3000);
			return false; //避免载入2条相同的数据
		} else if (newFeed == 'transmit'){
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>转发成功</span>", 3000);
		} else {
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>发布成功</span>", 3000);
		}
	}

	//alert("api/blog/api_feed.php?uid=" + $space[uid] +"&"+"type="+type+"&num="+num+"&shownum="+shownum+"&typeid="+typeid+idtype+"&startdatetime="+startdatetime+"&enddatetime="+enddatetime);
	var apiurl = "api/blog/api_feed.php?uid=" + space_uid +"&"+"type="+type+"&num="+num+"&shownum="+shownum+"&typeid="+typeid+"&idtype="+idtype+"&startdatetime="+startdatetime+"&enddatetime="+enddatetime;
	if(feedid){ apiurl += "&feedid="+feedid;}
	jQuery.getJSON(apiurl, function(data){
		if( data != null && data.feed){
			if(type == 'user'){jQuery('#feed').addClass('space');}else{jQuery('#feed').removeClass('space');}
		  	clear_load_wait();
			var string =  get_bodyCont(data.feed, type);
			if( newFeed){
				jQuery("#feed").prepend(string);
			}else{
				jQuery("#feed").append(string);
			}

			feed_msie("#feed");

			update_timestr();
			fetchFeedAuth('#feed');
			jQuery(".commentSay").trigger("myCustomEvent");
			jQuery('textarea[name=message]').trigger("myCustomEvent2");

			if( feedSign == true && !newFeed){dl_count(data.feed.length);load_pager();}
		}else{
			if( feedSign == false){
					jQuery("#load_wait").html('<br><br><p style=" text-align:center; margin:0px auto;"><br><br>非常抱歉,暂无动态！</p><br><br>');
			}
			if( feedSign == true ){dl_count();load_pager();}
		}
		if (func) func();
	});
}

function load_atme(num,shownum){
	jQuery.getJSON("api/blog/api_atme.php?uid=" + space_uid +"&"+"num="+num+"&shownum="+shownum,function(data){
		if( data != null && data.feed){
		  	clear_load_wait();
			var string =  get_bodyCont(data.feed);
			if( newFeed){
				jQuery("#feed").prepend(string);
			}else{
				jQuery("#feed").append(string);
			}
			feed_msie("#feed");

			update_timestr();
			fetchFeedAuth('#feed');
			jQuery(".commentSay").trigger("myCustomEvent");
			jQuery('textarea[name=message]').trigger("myCustomEvent2");
			if( feedSign == true ){dl_count(data.feed.length);load_pager();}
		}else{
			if( feedSign == false){
					jQuery("#load_wait").html('<br><br><p style=" text-align:center; margin:0px auto;"><br><br>非常抱歉,暂无动态！</p><br><br>');
			}
			if( feedSign == true ){dl_count();load_pager();}
		}
	});
}


// 加载标签
function urlCode(string){
	var codestr = string;
	if(string.substring(0,1)=='#'){
		codestr = string.substring(1,string.length-1);
	}
	codestr = encodeURIComponent(codestr);
	return codestr;
}

function trans_links(string){
	if (!string) return '';
	string = string.replace(/\\"/gi, '');
	var len = function(s){
			s = String(s);
			return s.length + (s.match(/[^\x00-\xff]/g) || '').length;
		}
	return string.replace(/#[\w\u4E00-\u9FA5]*?#/g,
		function($1){ 
			if(len($1) <= 22) $1 = '<a href="home.php?mod=space&do=tag&tagname=' + urlCode($1) + '" target="_blank" class="xi2 xw1">'+$1+'</a>';
			return $1;
		});
}
function trans_videoLink(url){
	return (url.toLowerCase().indexOf(".flv</a>")>0)?"":url;
}
function trans_fromLink(index){
	var fromWhere = {
			"0": "<a class='xi2' href='javascript:;'>桌面端</a>",
			"1": "<a class='xi2' href='http://home.myctu.cn/forum.php?mod=group&fid=609' target='_blank'>移动端</a>",
			"2": "<a class='xi2' href='http://know.myctu.cn/WebRoot/index.html' target='_blank'>知识中心</a>",
			"3": "<a class='xi2' href='http://home.myctu.cn/forum.php?mod=group&fid=609' target='_blank'>翼站</a>",

			"": "",
			"undefined": ""
		}
	return fromWhere[index];
}
function trans_link_blank(url){
	if (!url) return '';
	
	return url.replace(/<a(?![^<>]*?target[^<>]*?>).*?>/g, function($1){
			return $1.substring(0, $1.length-1) + ' target="_blank" >';
		});
	
}

function load_links_feed(tag,uid,num,shownum,idtype,starttime,endtime){
	jQuery.getJSON("api/blog/api_tagfeed.php?tag="+encodeURIComponent(tag)+"&"+"uid="+uid+"&num="+num+"&shownum="+shownum+"&idtype="+idtype+"&startdatetime="+starttime+"&enddatetime="+endtime,function(data){
		if( data != null && data.feed){
		  	clear_load_wait();
			var string =  get_bodyCont(data.feed);
			if( newFeed){
				jQuery("#feed").prepend(string);
			}else{
				jQuery("#feed").append(string);
			}
			feed_msie("#feed");

			update_timestr();
			fetchFeedAuth('#feed');
			jQuery(".commentSay").trigger("myCustomEvent");
			jQuery('textarea[name=message]').trigger("myCustomEvent2");
			if( feedSign == true ){dl_count(data.feed.length);load_pager();}
		}else{
				if( feedSign == false){
					jQuery("#load_wait").html('<br><br><p style=" text-align:center; margin:0px auto;"><br><br>非常抱歉,暂无动态！</p><br><br>');
				}
				if( feedSign == true ){dl_count();load_pager();}
		}
	});
}


function get_bodyCont(data, type){

		var string ='';
//		jQuery.each(data, function(key, val) {
			jQuery.each(data, function(key, feed) {
				//string += '<div style="color:red;">'+key+'*******'+feed.feedid+'</div>';
				var icon ='';
				if( feed.icon == "blog" || feed.icon == "doing"){
					icon = "doing";
				}else if(feed.icon =='album'){
					icon = "pic";
				}else if(feed.icon =='share'){
					if (feed.body_data){
						if(feed.body_data.musicvar){
							icon = "music";
						}else if(feed.body_data.flashvar){
							icon = "vedio";
						}else if(feed.idtype=='pic' || (feed.idtype=='feed' && feed.image_1)){
							icon = "pic";
						}else { icon = "share"; }
					}
				}else if(feed.icon =='doc'){
					icon = "doc";
				}else{
					icon = "old";
				}

				var date=gettime(feed.dateline);

				string += '<dl class="list cl" id="'+feed.feedid+'" commentdata="'+feed.icon+'+'+feed.id+'+'+feed.feedid+'" >';

				if (type != 'user'){	//个人空间不显示头像
					if (feed.anonymity > 0) { //如果是马甲，显示马甲信息
						var fid;
						string += '<dd class="m avt" style="width: 56px;"><a class="perPanel" href="forum.php?mod=group&fid='+feed.uid+'" target="_blank"><img src="'+feed.ficon+'" /></a><div class="xi2 xw1 hm xs2" style="margin-top: 2px;"><a class="perPanel" href="forum.php?mod=group&fid='+feed.uid+'" target="_blank">'+feed.username+'</a></div></dd>';
					}
					else if((feed.icon == 'class' || feed.icon == 'doc' || feed.icon == 'case') && feed.idtype != "feed" && feed.uid==0){
						string += '<dd class="m avt" style="width: 56px;"><a class="perPanel" href="http://know.myctu.cn/WebRoot/index.html" target="_blank"><img src="uc_server/avatar.php?uid='+feed.uid+'&size=small" /></a><div class="xi2 xw1 hm xs2" style="margin-top: 2px;"><a class="perPanel" href="http://know.myctu.cn/WebRoot/index.html" target="_blank">知识中心</a></div></dd>';
					}else{
					string += '<dd class="m avt" style="width: 56px;"><a class="perPanel" href="home.php?mod=space&uid='+feed.uid+'" target="_blank"><img src="uc_server/avatar.php?uid='+feed.uid+'&size=small" /></a><div class="xi2 xw1 hm xs2" style="margin-top: 2px;"><a class="perPanel" href="home.php?mod=space&uid='+feed.uid+'" target="_blank" added="ignore">'+feed.username+'</a></div></dd>';
					}
				}
				string +='<dd class="contBox cl">';
				if (type != 'user'){	//个人空间不显示头像
				string +='<div class="triangle"><img src="'+IMGDIR+'/esn/triangle.png"></div>';
				}
				string +='<div class="content1"><b class="white"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b><div class="content2">';
				string +='<div class="content '+icon+'">';

				function bodyContent(){
					var bodystr ='';
					if( feed.icon == "doing"){
						bodystr +='<div class="breif cl">'+feed.title_data.message+'</div>';
					}else if( feed.icon == "blog"){
						if(feed.body_data != null && feed.body_data.summary != null){
							bodystr +='<div class="doctitle">'+trans_link_blank(feed.body_data.subject)+'</div>';
							if(jQuery.trim(feed.body_data.summary) != '') {
								bodystr +='<div class="breif cl">'+trans_links(feed.body_data.summary)+'</div>';
							}
							if( feed.body_data.summary.indexOf("<!--more-->")>1){
								bodystr +='<span value="'+feed.icon+'+'+feed.id+'+'+feed.feedid+'" class="showDetail" ';
								if(feed.idtype =="feed"){
									bodystr +='id="'+feed.id+'+'+feed.oldid+'+'+feed.uid+'"';
								}else{
									bodystr +='id="'+feed.feedid+'+'+feed.id+'+'+feed.uid+'"';
								}
								bodystr +='><a href="javascript:;" class="replaced">查看全文</a></span>';
							}
						}
						if(feed.image_1){
							if(feed.image_1.indexOf("data/kindeditorattached") == -1) {
								bodystr +='<div class="showImage"><img src="'+IMGDIR+'/loading.gif" /><img class="external" onload="externalImgReFit(this);" src="' + feed.image_1 +'"></div>';
							} else {
								var fileSplitterIndex = feed.image_1.lastIndexOf(".");
								bodystr +='<div class="showImage"><img class="thumb" src="'+feed.image_1.substring(0, fileSplitterIndex) + ".thumb"
														    + feed.image_1.substring(fileSplitterIndex, feed.image_1.length) +'"></div>';
							}
						}
					}else if( feed.icon == "share"){
						if( icon == "vedio"){
							bodystr +='<div class="mediatitle">'+trans_links((feed.idtype=="feed"?feed.oldbody_general:feed.body_general))+'</div>';
							bodystr +='<div class="ec"><div class="d url">'+trans_videoLink(feed.body_template)+'</div>';
							bodystr +='<table class="mtm" onclick="javascript:showFlash(\'flash\', \''+feed.body_data.flashvar+'\', this, \''+feed.feedid+'\');" title="点击播放"><tr><td class="vdtn hm" style="background: url('+feed.body_data.imgurl+') no-repeat">';

							var playimg = feed.body_data.imgurl ? 'btn_play.png' : 'btn_play_desc.png';
							if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) {
								bodystr += '<img src="static/image/common/dummy.gif" alt="点击播放" style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'static/image/common/esn/'+playimg+'\', sizingMethod=\'image\')" />';
							} else {
								bodystr += '<img src="static/image/common/esn/'+playimg+'" alt="点击播放" />';
							}

							bodystr += '</td></tr></table></div>';
						}else if( icon == "music"){
							bodystr +='<div class="mediatitle">'+trans_links((feed.idtype=="feed"?feed.oldbody_general:feed.body_general))+'</div><div class="ec"><div class="d url">'+feed.body_template+'</div>';
							bodystr +='<img onclick="javascript:showFlash(\'music\', \''+feed.body_data.musicvar+'\', this, \''+feed.feedid+'\');"  src="static/image/common/esn/click2listen1.gif" alt="点击播放" class="tn btn_player" /></div>';
						}else if( icon == "share"){ 
							bodystr +='<div class="mediatitle">'+trans_links((feed.idtype=="feed"?feed.oldbody_general:feed.body_general))+'</div><div></div>';
							bodystr +='<div class="breif">'+feed.body_template+'</div>';
						}else if( icon == "pic"){
							bodystr +='<div class="mediatitle">'+(feed.idtype=="feed"?feed.oldbody_general:feed.body_general)+'</div><div class="cl"></div><div style="padding:10px 0 0 0;">';
							bodystr +='<a href="'+feed.image_1_link+'"><img src="' + feed.image_1 +'" /></a>';
							bodystr +='</div>';
						}
					}else if( feed.icon == "album" ){
						bodystr +='<span class="imgnum">'+feed.body_template+'</span><div style="clear:both"></div><div class="img_box" commentdata="'+feed.icon+'+'+feed.id+'+'+feed.feedid+'">';
						if (feed.body_template == '0张图片'){
							bodystr += '<s>图片已删除</s>';
						}else{
							var targetIDs = feed.target_ids.split(",");
							jQuery.each(feed.imagearr, function(key, img) {
								var fileSpitterIndex = img.lastIndexOf(".");
								bodystr +='<div id="picid_' + feed.feedid + "_" + targetIDs[key] + '" class="smallimg"><img src="' + img.substring(0, fileSpitterIndex) + '.thumb' +
								          img.substring(fileSpitterIndex, img.length) +'" /></div>';
							})
						}
						//如果是转发内容，取oldbody
						bodystr +='</div><div class="mediatitle">'+trans_links((feed.idtype=="feed"?feed.oldbody_general:feed.body_general))+'</div>';

					}else if( feed.icon == "doc" && feed.idtype == "docid" ){
						bodystr +='<div class="doctitle"><a href="'+ feed.docarr.titlelink +'" target="_blank">'+feed.docarr.title+'</a></div>';
						bodystr +='<div class="cl">';
						bodystr +='<div class="docimage"><img src="'+feed.docarr.imglink+'" height="120px"></div>';
						bodystr +='<div class="breif">'+feed.docarr.context+'</div>';
						bodystr +='</div>';

					}else if( (feed.icon == "class" || feed.icon == "doc" || feed.icon == "case") && feed.idtype == "feed" ){ //精品资源推广：课程、文档、案例
						bodystr +='<div class="doctitle" style="font-size: 14px">'+trans_link_blank(feed.body_data.subject)+'</div>';
						bodystr +='<div class="cl">';
						bodystr +='<div class="docimage"><a href="'+feed.image_1_link+'" target="_blank"><img src="'+feed.image_1+'" height="120px"></a></div>';
						bodystr +='<div class="breif">'+feed.body_data.message+'</div>';
						bodystr +='</div>';

					}else{
						if( icon == 'old'){
							bodystr +='<div class="mediatitle">'+feed.title_template+'</div>';
							if(feed.body_data != null) {
								if(feed.body_data.subject != undefined && feed.body_data.subject != null) {
									bodystr +='<div>'+feed.body_data.subject+'</div>';
								}
								if( feed.body_data.summary != null){
									bodystr +='<div>'+feed.body_data.summary+'</div>';
								}else if(feed.body_data.message != null){
									bodystr +='<div>'+feed.body_data.message+'</div>';
								}

								if(feed.icon =="thread" || feed.icon =="notice"){
									if(feed.body_data.message.indexOf("<!--more-->")>1){
										bodystr +='<span value="'+feed.icon+'+'+feed.id+'+'+feed.feedid+'" class="showDetail" ';
										bodystr +='id="';
										if(feed.icon =="thread"){bodystr +='thread';}
										else{bodystr +='notice';}
										if(feed.idtype =="feed"){
											bodystr +='+'+feed.uid+'+'+feed.oldid+'+'+feed.feedid+'+'+feed.fid+'"';
										}else{
											bodystr +='+'+feed.uid+'+'+feed.id+'+'+feed.feedid+'+'+feed.fid+'"';
										}
										bodystr +='><a href="javascript:;" class="replaced">查看全文</a></span>';
									}
								}
							}
						}
					}
					return bodystr;
				}

				function detail_link(){
					var url = '';
					switch (feed.icon) {
					     case 'thread':			//话题
					         url = 'forum.php?mod=viewthread&fid='+feed.oldfid+'&special=0&plugin_name=topic&plugin_op=groupmenu&tid='+feed.oldid+'&extra=page%3D1';
					         break;
					     case 'album':			//图片
					     	 //url = feed.image_1_link;
					     	 url = 'home.php?mod=space&uid='+feed.olduid+'&do=home&view=me&from=space&feedid='+feed.id;
					         break;
					     case 'blog':			//记录
					         url = 'home.php?mod=space&uid='+feed.olduid+'&do=blog&id='+feed.oldid+'&diymode=1';
					         break;
					     case 'reward':			//提问吧
					         url = 'forum.php?mod=viewthread&tid='+feed.oldid+'&fid='+feed.oldfid+'&plugin_name=qbar&extra=page%3D1';
					         break;
					     //case 'doc':			//文档
					     //    url = (feed.idtype=='docid')?feed.docarr.titlelink:'';
					     //    break;
					     case 'live':			//直播
					         url = 'forum.php?mod=group&action=plugin&fid='+feed.oldfid+'&plugin_name=grouplive&plugin_op=groupmenu&liveid='+feed.oldid+'&op=join&grouplive_action=livecp&';
					         break;
					     case 'resourcelist':	//资源列表
					         url = jQuery(feed.title_data.resourcetitle).attr('href');
					         break;
					     case 'nwkt':			//你我课堂
					         url = 'home.php?mod=space&uid='+feed.olduid+'&do=nwkt&id='+feed.oldid;
					         break;
					     case 'poll':			//投票
					         url = 'forum.php?mod=viewthread&fid='+feed.oldfid+'&special=1&plugin_name=poll&plugin_op=createmenu&tid='+feed.oldid+'&extra=page%3D1';
					         break;
					     case 'notice':			//专区通知
					         url = 'forum.php?mod=group&action=plugin&fid='+feed.oldfid+'&plugin_name=notice&plugin_op=groupmenu&diy=&noticeid='+feed.oldid+'&notice_action=view';
					         break;
					     case 'class':			//精品资源推广：课程
					     case 'doc':			//文档
					     case 'case':			//精品资源推广：案例
					     	 url = jQuery(feed.body_data.subject).attr('href');
					     	 break;
					     default:
					         url = false;
					         break;
					}
					return url;
				}

				if(feed.idtype =="feed" || feed.icon == 'forward'){
					string +='<div class="breif">'+feed.body_general+'</div></div>';
					string +='<div class="transmit"><div class="uptransmit"></div>';
					if((feed.title_data !="" && feed.body_data !="") || feed.image_1 !=""){
						if (!feed.oldid && feed.icon!='forward'){
							string += '<div class="transmitBox doc"><s>原文已删除</s>';
						}else{
							var stroldtime = '';
							if (feed.icon=='class'){ //精品资源推广:课程
								string +='<div class="transmitBox doc"><span class="xi2 xw1 xs2">[课程] </span>';
							}else if (feed.icon=='case'){ //精品资源推广:案例
								string +='<div class="transmitBox doc"><span class="xi2 xw1 xs2">[案例] </span>';
							}else if (feed.icon=='doc'){ //精品资源推广:文档
								string +='<div class="transmitBox doc"><span class="xi2 xw1 xs2">[文档] </span>';
							}else{
								stroldtime = gettime(feed.olddateline);
								string +='<div class="transmitBox '+icon+'"><div class="logo"></div>';
								if (feed.oldanonymity > 0){
									string +='<span class="color_bold"><a class="perPanel" target="_blank" href="forum.php?mod=group&fid='+feed.oldanonymity+'">@'+feed.oldusername+'</a>：</span>';
								}else if (feed.oldanonymity == -1){
									string +='<span class="color_bold"><a class="perPanel" target="_blank" href="home.php?mod=space&uid=-1">@'+feed.oldusername+'</a>：</span>';
								}else{
									if(feed.oldusername!='未知'){
									string +='<span class="color_bold"><a class="perPanel" target="_blank" href="home.php?mod=space&uid='+feed.olduid+'">@'+feed.oldusername+'</a>：</span>';
									}
								}
							}
							string += bodyContent();
							if(feed.oldusername!='未知'){
								string +='<div class="other"><div class="from">来自'+trans_fromLink(feed.oldfromwhere)+'</div><div class="buttons">';

								string +='<a href="home.php?mod=spacecp&ac=forward&feedid='+feed.id+'" onclick="showWinCover(\'transmit\', this.href, \'get\',0);">转发';

								if(feed.oldsharetimes>0){ string +='('+feed.oldsharetimes+')';}
								string +='</a>';

								var dlink = detail_link();
								if (dlink) {
									string +='<a href="'+dlink+'" target="_blank">评论';
									if(feed.oldcommenttimes>0){string +='('+feed.oldcommenttimes+')';}
								}
								string +='</a></div></div>';
							}
						}
					}else{
						string +='<div class="transmitBox">抱歉，原帖已经被删除。';
					}
					string +='</div>';

				}else{
					if( icon != 'old'){
						string += '<div class="logo"></div>';      //其他类型动态没有图标
					}
					string += bodyContent();
				}

				string +='</div><div class="other"><span class="update_time" style="display:none;">'+feed.dateline+'</span><div class="info"><span>'+date+'</span></div><div class="from"><span>来自'+trans_fromLink(feed.fromwhere)+'</span></div>';


				//***************** 开始other中的按钮     修改： 所有类型动态都有按钮 ********************
				//if( icon != 'old'){
					string +='<div class="buttons">';
					if(checkUidInRepeat(feed.uid) && (feed.icon == "doing" || feed.icon == "blog") && feed.idtype !="feed"){
						string +='<a href="javascript:;" class="edit_feed minlk" id="'+feed.uid+'+'+feed.feedid+'+'+feed.id+'">编辑</a>';
					}
					if(checkUidInRepeat(feed.uid) || (feed.uid == -1 && feed.realuid == UID)) {
						string +='<a href="javascript:;" class="delete_feed minlk" id="'+(feed.uid==-1?feed.realuid:feed.uid)+'+'+feed.feedid+'" >删除</a>';
					}

					//私密专区动态不能转发
					if (feed.fid!="" && feed.gviewperm == 1) {
						string +='<a id="transmit" href="home.php?mod=spacecp&ac=forward&feedid='+feed.feedid+'" onclick="showWinCover(this.id, this.href, \'get\',0);">转发';
						if(feed.sharetimes>0){ string +='('+feed.sharetimes+')';}
						string +='</a>';
					}

					if(feed.isfavorite){
						string +='<a href="javascript:;" class="cancel_favorite" id="'+feed.id+'+'+feed.idtype+'+'+UID+'+'+feed.feedid+'">取消收藏</a>';
						string +='<a href="javascript:;" class="favorite" id="'+feed.id+'+'+feed.idtype+'+'+UID+'+'+feed.feedid+'" style=" display:none">收藏</a>';
					}else{
						string +='<a href="javascript:;" class="favorite" id="'+feed.id+'+'+feed.idtype+'+'+UID+'+'+feed.feedid+'">收藏</a>';
						string +='<a href="javascript:;" class="cancel_favorite" id="'+feed.id+'+'+feed.idtype+'+'+UID+'+'+feed.feedid+'" style=" display:none">取消收藏</a>';
					}
					string +='<a href="javascript:;" class="btn_comment">评论';
					if(feed.commenttimes>0){string +='('+feed.commenttimes+')';}
					string +='</a></div>';
				//}
				//***************** 结束other中的按钮 ********************

				string +='</div><div class="comment">';
				if(feed.comment) {
					string += '<p class="line"></p>';
				}
				string += '<ul class="comments">';
				if(feed.comment){
					jQuery.each(feed.comment, function(key, commentLi){
						var commentTime = gettime(commentLi.dateline);
						var uid = commentLi.uid;
						if (commentLi.anonymity > 0){ //专区马甲
							string +='<li><div class="imgCom"><a class="perPanel" href="forum.php?mod=group&fid='+commentLi.authorid+'" target="_blank"><img width="32px" height="32px" src="'+commentLi.ficon+'"/></a></div>';
							string +='<div class="words"><a class="perPanel" href=\'forum.php?mod=group&fid='+commentLi.authorid+'\' target="_blank"><span>'+commentLi.realname+':</span></a>';
						}else{
							string +='<li><div class="imgCom"><a class="perPanel" href="home.php?mod=space&uid='+commentLi.authorid+'" target="_blank"><img width="32px" height="32px" src="uc_server/avatar.php?uid='+commentLi.authorid+'&size=small"/></a></div>';
							string +='<div class="words"><a class="perPanel" href=\'home.php?mod=space&uid='+commentLi.authorid+'\' target="_blank"><span class="clmr">'+commentLi.realname+'</span></a><span>:</span>';
						}
						string +=commentLi.message+'<em>&nbsp;('+commentTime;
						string +='<a class="minlk" style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="minlk commentReply" href="javascript:;" id="'+commentLi.realname+'_'+feed.feedid+'_'+commentLi.cid+'">回复</a>';
						if( checkUidInRepeat(commentLi.authorid) || (commentLi.anonymity == -1 && checkUidInRepeat(commentLi.realuid)) ){
							string +='<a class="minlk" style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="minlk commentDelete" href="javascript:;" id="'+feed.icon+'+'+commentLi.cid+'+'+commentLi.uid+'+'+feed.id+'+'+feed.feedid+'">删除</a>';
						}
						string +=')</em><span class="update_time2" style="display:none;">'+commentLi.dateline+'</span></div></li>';
					});
				}
				string +='</ul>';
				if(feed.comment){
					if(feed.commenttimes>2){ string +='<div class="moreCom" value="'+feed.feedid+'"><span>点击查看&nbsp;&nbsp;更多评论</span></div>';}
				}

				string +='<b class="bgray white"><b class="b4"></b><b class="b3"></b><b class="b2"></b><b class="b1"></b></b>';
				string +='<div class="commentSay"><form action="home.php?mod=spacecp&ac=comment&handlekey=Com" method="post" id="feedComment_'+feed.feedid+'" onsubmit="ajaxpost(\'feedComment_'+feed.feedid+'\', \'return_Com_'+feed.feedid+'\');setFeedId(\''+feed.feedid+'\'); doane(event); disComment(this);"><input type="hidden" value="'+feed.icon+'" name="icon"><input type="hidden" value="'+feed.fid+'" name="fid"><input type="hidden" value="'+feed.id+'" name="iconid"><input type="hidden" value="'+feed.idtype+'" name="iconidtype"><input type="hidden" value="'+feed.feedid+'" name="id"><input type="hidden" value="feed" name="idtype"><input type="hidden" value="true" name="commentsubmit"><input type="hidden" value="true" name="quickcomment"><input type="hidden" name="formhash" value="'+FORMHASH+'" /><input type="hidden" name="cid" /><input type="hidden" name="atjson" /><div style="padding-bottom:5px;">'
				if (feed.fid!="" && feed.gviewperm == 1){
				string +='<input class="W_checkbox mrngroup" type="checkbox" name="forward" onclick="setTransBlog()"><label for="forward">评论同时转发</label> &nbsp; '
				}
				string +='<input class="W_checkbox mrngroup" type="checkbox" name="anonymity" value="-1"><label for="anonymity">匿名评论</label></div><textarea name="message" type="text"></textarea><input class="btn_comment" disabled="disabled" type="submit" value="" style="background-position: 0 0;" /></form><div id="return_Com_'+feed.feedid+'" style="display:none;"></div></div></div>';
				string +='</div><b class="gray"><b class="b4"></b><b class="b3"></b><b class="b2"></b><b class="b1"></b></b></div></dd></dl>';

			});

//		});
		return string;
}


//******************************************************************//
//*************************  事件绑定     *****************************//
//删除自己的评论
jQuery("body").on("click",".commentDelete",function(){
	var anchor = jQuery(this);
	var anchorLi = anchor.parent().parent().parent();
	var commentUL = anchorLi.parent();
	var str = anchor.attr("id").split("+");
	var btn_comment = jQuery('#' + str[4] + ' a.btn_comment');
	jQuery.getJSON("api/blog/api_delcomment.php?icon="+str[0]+"&"+"cid="+str[1]+"&uid="+str[2]+"&id="+str[3]+"&feedid="+str[4]+"&code="+jQuery.md5('esn'+str[0]+str[1]+str[2]+str[3]+str[4]),function(data){
		if(data.success == 'Y'){
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>删除成功</span>", 3000);
			anchorLi.slideUp("fast", function() {
				anchorLi.remove();
				if(commentUL.html() == "") { jQuery(".line", commentUL.parent()).remove(); }

				var comTimes = btn_comment.html();
				var pos = comTimes.indexOf("(");
				var times = 0;
				if(pos>0){ times = comTimes.substring(pos+1,comTimes.length-1);}
				times = times - 1;
				if(times > 0) {
					btn_comment.html('评论('+times+')');
				} else { btn_comment.html('评论'); }
			});
		}else{
			showDialog(data.message,'notice');
		}
	});
})

function commentDelete00(val){
	var anchor = jQuery(val);
	var anchorLi = anchor.parent().parent().parent();
	var str = anchor.attr("id").split("+");
	jQuery.getJSON("api/blog/api_delcomment.php?icon="+str[0]+"&"+"cid="+str[1]+"&uid="+str[2]+"&id="+str[3]+"&feedid="+str[4]+"&code="+jQuery.md5('esn'+str[0]+str[1]+str[2]+str[3]+str[4]),function(data){
		if(data.success == 'Y'){
			showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>删除成功</span>", 3000);
			anchorLi.slideUp("fast", function() {
				anchorLi.remove();
			});
		}else{
			showDialog(data.message,'notice');
		}
	});
}


//回复他人评论
jQuery("body").on("click",".commentReply",function(){
	var stringId = jQuery(this).attr("id").split("_");
	var feedid = stringId[1];
	var replyName = stringId[0];
	var cid = stringId[2];
	var currentFeed = jQuery("#"+feedid);
	jQuery(".commentSay",currentFeed).css("display","block");
	jQuery(textarea,currentFeed).val("回复 @"+replyName+" : ");
	jQuery('input[name=cid]',currentFeed).val(cid);
})

jQuery("body").on("click",".commentReply0",function(){
	var anchor = jQuery(this);
	var stringId = anchor.attr("id").split("+");
	var replyName = stringId[0];
	var cid = stringId[1];
	var comment = anchor.parent().parent().parent().parent();
	jQuery(textarea,comment).val("回复 @"+replyName+" : ");
	jQuery('input[name=cid]',comment).val(cid);
})

function commentReply00(val){
	var anchor = jQuery(val);
	var stringId = anchor.attr("id").split("+");
	var replyName = stringId[0];
	var cid = stringId[1];
	var comment = anchor.parent().parent().parent().parent().parent();
	jQuery(textarea,comment).val("回复 @"+replyName+" : ");
	jQuery('input[name=cid]',comment).val(cid);
}


// 视频 点击播放  按钮变换
jQuery("body").on("mouseenter",".vdtn img",function(){
	var playimg = '';
	if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) {
		playimg = jQuery(this).attr("style");
		jQuery(this).attr("style", playimg.replace(/(.*?)btn_play(.*?)/, '$1btn_play2$2'));
	} else {
		playimg = jQuery(this).attr("src");
		jQuery(this).attr("src", playimg.replace(/(.*?)btn_play(.*?)/, '$1btn_play2$2'));
	}
})
jQuery("body").on("mouseleave",".vdtn img",function(){
	var playimg = '';
	if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) {
		playimg = jQuery(this).attr("style");
		jQuery(this).attr("style", playimg.replace(/(.*?)btn_play2(.*?)/, '$1btn_play$2'));
	} else {
		playimg = jQuery(this).attr("src");
		jQuery(this).attr("src", playimg.replace(/(.*?)btn_play2(.*?)/, '$1btn_play$2'));
	}
})

// 隐藏评论框
jQuery("body").on("myCustomEvent",".commentSay",function() {
  jQuery(this).css("display","none");
});

// 评论宽高度自动调整
jQuery("body").on("myCustomEvent2",'textarea[name=message]',function() {
  jQuery(this).growfield();
});

function succeedhandle_detailCom(url, msg, values){
	var comment = jQuery(".cover_comments");
	var commentwords = values.message;
	jQuery(textarea,comment).attr("value","").removeAttr("readonly").growfield('restart');
	jQuery('input[name=cid]', comment).val('');
	var line = jQuery(".line",comment);

	var timestamp = Date.parse(new Date());
	timestamp = timestamp/1000;
	var strtime = gettime(timestamp);

	var str = comment.attr("commentdata").split("+");

	var ck = jQuery("input[name=anonymity]",comment).attr("checked");
	var	p = repeatstatus((ck=='checked')),
		ufid = p.ufid,
		perurl = p.perurl,
		imgurl = p.imgurl,
		realname = p.realname,
		auth = p.showauth===true?showMyAuth():'';

	var string='<li><div class="imgCom">';
	string +='<a id="'+ufid+'" href="'+perurl+'" target="_blank"><img width="32px" height="32px" src="'+imgurl+'"></a></div>';
	string +='<div class="words"><a id="'+ufid+'" href=\''+perurl+'\' target="_blank" class="perAuth" added="added"><span class="clmr">'+realname+'</span></a>'+auth+'<span>:</span>';
	string +=''+commentwords+'<em style="color:#909090;">&nbsp;('+strtime;
	string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentReply0" href="javascript:;" id="'+realname+'+'+values.cid+'">回复</a>';
	string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" href="javascript:;" id="'+str[0]+'+'+values.cid+'+'+UID+'+'+str[1]+'+'+str[2]+'">删除</a>';
	string +=')</em><span class="update_time2" style="display:none;">'+timestamp+'</span></div></li>';
	line.after(string);
	//alert("ssss");
	
}

// 查看详情
jQuery("body").on("click",".showDetail",function(){
			//为评论框添加Autocomplete事件
			function textAreaAcHandle(event) {
				if(!jQuery("#menu123 .acwrapper").length) {
					jQuery("#menu123").append("<div class='acwrapper'></div>");
				}
				textAreaAutoComplete(event, this, jQuery('#menu123 .acwrapper'));
			}

			var anchor = jQuery(this);
			var string = anchor.attr("id");
			var str = string.split("+");
			var str2 = anchor.attr("value").split("+");
			if(str[0]=="thread"){
				jQuery.getJSON("api/blog/api_showthread.php?uid="+str[1]+"&"+"tid="+str[2]+"&feedid="+str[3]+"&fid="+str[4],function(data){
					var content ='';
					content += '<div class="cover_blog_title">'+data.thread.subject+'</div><div class="cover_blog_content cl">'+trans_links(data.thread.message)+'</div>';
					content += '<ul class="cover_comments" commentdata="'+str2[0]+'+'+str2[1]+'+'+str2[2]+'">';
					content += '<div class="line"></div>';
					if(data.comment){
						jQuery.each(data.comment, function(key,comment) {
							var commentTime = gettime(comment.dateline);
							var uid = comment.uid;
							if (comment.anonymity > 0){ //专区马甲
								content +='<li><div class="imgCom"><a href="forum.php?mod=group&fid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="'+comment.ficon+'"/></a></div>';
								content +='<div class="words"><a href=\'forum.php?mod=group&fid='+comment.authorid+'\' target="_blank"><span>'+comment.author+':</span></a>';
							}else{
								content +='<li><div class="imgCom"><a href="home.php?mod=space&uid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="uc_server/avatar.php?uid='+comment.authorid+'&size=small"/></a></div>';
								content +='<div class="words"><a href=\'home.php?mod=space&uid='+comment.authorid+'\' target="_blank" class="perAuth"><span class="clmr">'+comment.author+'</span></a><span>:</span>';
							}
							content +=comment.message+'<em style="color:#909090;">&nbsp;('+commentTime;
							content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentReply0" href="javascript:;" id="'+comment.realname+'+'+comment.cid+'">回复</a>';
							if( checkUidInRepeat(comment.authorid) || (comment.anonymity == -1 && checkUidInRepeat(comment.realuid)) ){
								content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" href="javascript:;" id="'+str2[0]+'+'+comment.cid+'+'+comment.uid+'+'+str2[1]+'+'+str2[2]+'">删除</a>';
							}
							content +=')</em></div></li>';
						});
					}
					content += '';
					content +='<form action="home.php?mod=spacecp&ac=comment&handlekey=detailCom" method="post"  id="detailComment" onsubmit="ajaxpost(\'detailComment\', \'return_detailCom\');doane(event);disComment(this);" ><div style="padding:10px 0 5px 0;"><input class="W_checkbox mrngroup" type="checkbox" name="forward"><label for="forward">评论同时转发</label> &nbsp; <input class="W_checkbox mrngroup" type="checkbox" name="anonymity" value="-1" ><label for="anonymity">匿名评论</label></div>';
					content +='<input type="hidden" value="'+str[3]+'" name="id"><input type="hidden" value="feed" name="idtype"><input type="hidden" value="'+str[4]+'" name="fid"><input type="hidden" value="'+str[2]+'" name="iconid"><input type="hidden" value="thread" name="icon"><input type="hidden" value="tid" name="iconidtype"><input type="hidden" value="true" name="commentsubmit"><input type="hidden" value="true" name="quickcomment"><input type="hidden" name="formhash" value="'+FORMHASH+'" /><input type="hidden" name="cid" /><input type="hidden" name="atjson" /><div class="mbm"><textarea name="message" type="text" class="growfield_input"></textarea> <input class="btn_comment" type="submit" value="" disabled="disabled" style="background-position: 0 0;" /></div><div id="return_detailCom" style="display:none;"></div></form></ul>';
					showCover(false, content, null, function(){
							fetchFeedAuth('#menu123');
							jQuery('#detailComment textarea[name=message]').on("focus blur", textAreaAcHandle).data('noTag', true);
						});
				});
			}else if(str[0]=="notice"){
				jQuery.getJSON("api/blog/api_notice.php?uid="+str[1]+"&"+"noticeid="+str[2]+"&feedid="+str[3]+"&fid="+str[4],function(data){
					var content ='';
					content += '<div class="cover_blog_title">'+data.notice.title+'</div><div class="cover_blog_content cl">'+trans_links(data.notice.content)+'</div>';
					content += '<ul class="cover_comments" commentdata="'+str2[0]+'+'+str2[1]+'+'+str2[2]+'">';
					content += '<div class="line"></div>';
					if(data.comment){
						jQuery.each(data.comment, function(key,comment) {
							var commentTime = gettime(comment.dateline);
							var uid = comment.uid;
							if (comment.anonymity > 0){ //专区马甲
								content +='<li><div class="imgCom"><a href="forum.php?mod=group&fid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="'+comment.ficon+'"/></a></div>';
								content +='<div class="words"><a href=\'forum.php?mod=group&fid='+comment.authorid+'\' target="_blank"><span>'+comment.realname+':</span></a>';
							}else{
								content +='<li><div class="imgCom"><a href="home.php?mod=space&uid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="uc_server/avatar.php?uid='+comment.authorid+'&size=small"/></a></div>';
								content +='<div class="words"><a href=\'home.php?mod=space&uid='+comment.authorid+'\' target="_blank" class="perAuth"><span class="clmr">'+comment.realname+'</span></a><span>:</span>';
							}
							content +=comment.message+'<em style="color:#909090;">&nbsp;('+commentTime;
							content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentReply0" href="javascript:;" id="'+comment.realname+'+'+comment.cid+'">回复</a>';
							if( checkUidInRepeat(comment.authorid) || (comment.anonymity == -1 && checkUidInRepeat(comment.realuid)) ){
								content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" href="javascript:;" id="'+str2[0]+'+'+comment.cid+'+'+comment.uid+'+'+str2[1]+'+'+str2[2]+'">删除</a>';
							}
							content +=')</em></div></li>';
						});
					}
					content += '';
					content +='<form action="home.php?mod=spacecp&ac=comment&handlekey=detailCom" method="post"  id="detailComment" onsubmit="ajaxpost(\'detailComment\', \'return_detailCom\');doane(event);disComment(this);" ><div style="padding:10px 0 5px 0;"><input class="W_checkbox mrngroup" type="checkbox" name="forward"><label for="forward">评论同时转发</label> &nbsp; <input class="W_checkbox mrngroup" type="checkbox" name="anonymity" value="-1" ><label for="anonymity">匿名评论</label></div>';
					content +='<input type="hidden" value="'+str[3]+'" name="id"><input type="hidden" value="feed" name="idtype"><input type="hidden" value="true" name="commentsubmit"><input type="hidden" value="true" name="quickcomment"><input type="hidden" name="formhash" value="'+FORMHASH+'" /><input type="hidden" name="cid" /><input type="hidden" name="atjson" /><div class="mbm"><textarea name="message" type="text" class="growfield_input"></textarea> <input class="btn_comment" type="submit" value="" disabled="disabled" style="background-position: 0 0;" /></div><div id="return_detailCom" style="display:none;"></div></form></ul>';
					showCover(false, content, null, function(){
							fetchFeedAuth('#menu123');
							jQuery('#detailComment textarea[name=message]').on("focus blur", textAreaAcHandle).data('noTag', true);
						});
				});
			}else{
			   jQuery.getJSON("api/blog/api_showblog.php?feedid="+str[0]+"&"+"blogid="+str[1]+"&uid="+str[2],function(data){
					var content ='';
					content += '<div class="cover_blog_title">'+data.blog.subject+'</div><div class="cover_blog_content cl">'+trans_links(data.blog.message)+'</div>';
					content += '<ul class="cover_comments" commentdata="'+str2[0]+'+'+str2[1]+'+'+str2[2]+'">';
					content += '<div class="line"></div>';
					if(data.comment){
						jQuery.each(data.comment, function(key,comment) {
							var commentTime = gettime(comment.dateline);
							var uid = comment.uid;
							if (comment.anonymity > 0){ //专区马甲
								content +='<li><div class="imgCom"><a href="forum.php?mod=group&fid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="'+comment.ficon+'"/></a></div>';
								content +='<div class="words"><a href=\'forum.php?mod=group&fid='+comment.authorid+'\' target="_blank"><span>'+comment.realname+':</span></a>';
							}else{
								content +='<li><div class="imgCom"><a href="home.php?mod=space&uid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="uc_server/avatar.php?uid='+comment.authorid+'&size=small"/></a></div>';
								content +='<div class="words"><a href=\'home.php?mod=space&uid='+comment.authorid+'\' target="_blank" class="perAuth"><span class="clmr">'+comment.realname+'</span></a><span>:</span>';
							}
							content +=comment.message+'<em style="color:#909090;">&nbsp;('+commentTime;
							content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentReply0" href="javascript:;" id="'+comment.realname+'+'+comment.cid+'">回复</a>';
							if( checkUidInRepeat(comment.authorid) || (comment.anonymity == -1 && checkUidInRepeat(comment.realuid)) ){
								content +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" href="javascript:;" id="'+str2[0]+'+'+comment.cid+'+'+comment.uid+'+'+str2[1]+'+'+str2[2]+'">删除</a>';
							}
							content +=')</em></div></li>';
						});
					}
					content += '';
					content +='<form action="home.php?mod=spacecp&ac=comment&handlekey=detailCom" method="post"  id="detailComment" onsubmit="ajaxpost(\'detailComment\', \'return_detailCom\');doane(event);disComment(this);" ><div style="padding:10px 0 5px 0;"><input class="W_checkbox mrngroup" type="checkbox" name="forward"><label for="forward">评论同时转发</label> &nbsp; <input class="W_checkbox mrngroup" type="checkbox" name="anonymity" value="-1" ><label for="anonymity">匿名评论</label></div>';
					content +='<input type="hidden" value="'+str[0]+'" name="id"><input type="hidden" value="feed" name="idtype"><input type="hidden" value="true" name="commentsubmit"><input type="hidden" value="true" name="quickcomment"><input type="hidden" name="formhash" value="'+FORMHASH+'" /><input type="hidden" name="cid" /><input type="hidden" name="atjson" /><div class="mbm"><textarea name="message" type="text"></textarea> <input class="btn_comment" type="submit" value="" disabled="disabled" style="background-position: 0 0;" /></div><div id="return_detailCom" style="display:none;"></div></form></ul>';
					showCover(false, content, null, function(){
							fetchFeedAuth('#menu123');
							jQuery('#detailComment textarea[name=message]').on("focus blur", textAreaAcHandle).data('noTag', true);
						});
				});
			}


});

//图片全屏查看
jQuery("body").on("click", ".smallimg", function() {
	var str = jQuery(this).parent().attr("commentdata").split("+");
	var clickedPicID = jQuery(this).attr("id").split("_")[2];
	var clickedPicIndex = 0;
	var baseImgWidth, basePos;
	var feedID = jQuery(this).attr("id").split("_")[1];
	var viewLimit = [];
	var commentPanelShown = true;
	var initalCommentPanelWidth;
	showCover(true, "", "", function() {
		var pcobj = jQuery("#menu123");
		pcobj.addClass("picFullView");

		if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) == 6) {
			pcobj.css("position", "absolute");
			pcobj.css("top", jQuery(window).scrollTop());
		} else {
			pcobj.css("position", "fixed");
		}

		pcobj.append("<div id='picDetail'></div>");
		pcobj.append("<div id='picsList'></div>");
		jQuery("#picDetail").append("<div class='pd_container'></div><div id='commentPanel'><div class='commentContainer'  commentdata='"+str[0]+"+"+str[1]+"+"+str[2]+"'></div></div>"
									+ "<a id='imgClose' href='javascript:;' onclick='hideCover();' title='Close'></a>"
									+ "<a id='commentSwitch' class='hide' href='javascript:;'></a>"
									+ "<a id='showOriginalPic' class='show_big' href='javascript:;'></a>");
		initalCommentPanelWidth = jQuery("#commentPanel").width();
		jQuery("#picDetail .pd_container").width(jQuery(document).width() - initalCommentPanelWidth - 50);

		var resetView = function() {
			jQuery("#picDetail").height(jQuery(window).height() - jQuery("#picsList").height());
			jQuery("#picDetail .pd_container").width(jQuery(document).width() - initalCommentPanelWidth - 50);

			var viewLimitVertical  = (jQuery("#picDetail").height() - 120) > 0 ? jQuery("#picDetail").height() - 120 : 0;
			viewLimit = [jQuery(window).width() - jQuery("#commentPanel").width() - 120, viewLimitVertical];

			if(jQuery(".pfv_container img").length > 0) {
				basePos = jQuery(window).width() / 2 - baseImgWidth / 2;
				jQuery(".pfv_container").css("left", basePos - clickedPicIndex * baseImgWidth);
			}

			if(jQuery(".pd_container").length > 0) {
				jQuery("#append_parent .sInput").fadeOut("fast", function() {
					jQuery(this).remove();
				});
				jQuery(".pd_container img").css("width", "");
				jQuery(".pd_container img").css("height", "");
				reFitImg(jQuery(".pd_container img"), viewLimit);
				reCenterContainer(2);
			}
		};

		var reCenterContainer = function(pos) {
			var cw = jQuery(".pd_container").width();
			var ch = jQuery(".pd_container").height();

			jQuery(".pd_container").css("marginLeft", (jQuery(window).width() - jQuery("#commentPanel").width() - cw)/2);
			if(pos == 1) {
				jQuery(".pd_container").css("marginTop", (jQuery("#picDetail").height() - ch)/2);
			} else if(pos == 2) {
				jQuery(".pd_container").css("marginTop", 2*(jQuery("#picDetail").height() - ch)/3);
			}


		};

		var toggleCommentPanel = function () {
			jQuery("#append_parent .sInput").fadeOut("fast", function() {
				jQuery(this).remove();
			});
			if(commentPanelShown) {
				jQuery("#commentPanel").animate({
					marginRight: -jQuery("#commentPanel").width() - 1
				}, 200, 'easeOutExpo', function() {
					commentPanelShown = false;
					jQuery("#commentPanel").width(0);
					jQuery("#commentSwitch").removeClass("hide");
				});
				jQuery(".pd_container").animate({
					marginLeft: parseInt(jQuery(".pd_container").css("marginLeft")) + Math.abs(-jQuery("#commentPanel").width() - 1) / 2
				}, 200, 'easeOutExpo', function() {});
				jQuery("#commentSwitch").animate({
					right: 12
				}, 200, 'easeOutExpo', function() {});
				jQuery("#showOriginalPic").animate({
					right: 89
				}, 200, 'easeOutExpo', function() {});
			} else {
				jQuery("#commentPanel").width(initalCommentPanelWidth);
				jQuery("#commentPanel").animate({
					marginRight: 0
				}, 200, 'easeOutExpo', function() {
					commentPanelShown = true;
					jQuery("#commentSwitch").addClass("hide");
				});
				jQuery(".pd_container").animate({
					marginLeft: parseInt(jQuery(".pd_container").css("marginLeft")) - Math.abs(-jQuery("#commentPanel").width() - 1) / 2
				}, 200, 'easeOutExpo', function() {});
				jQuery("#commentSwitch").animate({
					right: jQuery("#commentPanel").width() + 13
				}, 200, 'easeOutExpo', function() {});
				jQuery("#showOriginalPic").animate({
					right: jQuery("#commentPanel").width() + 90
				}, 200, 'easeOutExpo', function() {});
			}
		};

		resetView();
		jQuery("#commentSwitch").on("click", toggleCommentPanel);
		jQuery("#showOriginalPic").on("click", function(){
			var newwindow = window.open(jQuery('#picDetail .pd_container img').attr('src'),'picwin','');
			if (window.focus) {newwindow.focus()}
			return false;
		});

		jQuery.getJSON("api/blog/api_album.php?feedid=" + feedID, function(data) {
			jQuery("#picsList").append("<div class='pfv_arrow'></div><div class='pfv_container'></div>");
			var picIDArray = [];
			var authorID;
			jQuery.each(data.album, function(key, pic) {
				authorID = pic.uid;
				var fileSplitterIndex = pic.filepath.lastIndexOf(".");
				jQuery(".pfv_container").append("<img src='" + pic.filepath.substring(0, fileSplitterIndex) + ".thumb"
										    + pic.filepath.substring(fileSplitterIndex, pic.filepath.length) + "' width='48' height='48' />");
				picIDArray.push(pic.picid);
				jQuery(".pfv_container img:last").data("picid", pic.picid);
				jQuery(".pfv_container img:last").data("pictitle", pic.title);
				if(pic.picid == clickedPicID) { clickedPicIndex = key; }
			});
			baseImgWidth = jQuery(".pfv_container img:eq(0)").outerWidth();
			basePos = jQuery(window).width() / 2 - baseImgWidth / 2;
			jQuery(".pfv_container").css("width", picIDArray.length * baseImgWidth);
			jQuery(".pfv_container").css("left", basePos - clickedPicIndex * baseImgWidth);
			jQuery(".pfv_container img:eq(" + clickedPicIndex +")").addClass("selected");
			showClickedPic(jQuery(".pfv_container img:eq(" + clickedPicIndex +")").attr("src").replace(".thumb", ""));
 			showClickedPicComment(jQuery(".pfv_container img:eq(" + clickedPicIndex +")").data("picid"));

 			function setImgTitle(source) {
 				jQuery("#append_parent .sInput").remove();
 				var msg = jQuery(".pd_container .picTitle").text();
 				var nowIndexPicID = jQuery(".pfv_container img:eq(" + clickedPicIndex +")").data("picid");
 				var inputstr = "<form id='imgTitleForm_" + nowIndexPicID
 							   + "' action='home.php?mod=spacecp&ac=album&op=editpic&subop=update&albumid=0&handlekey=imgtitle' method='post'>"
 							   + "<div class='quickImgTitleSet'><div class='qts_arrow'></div><div class='qts_content'>"
 							   + "<div id='return_imgTitleForm' style='display:none'></div>"
 							   + "<div class='add_f'><a href='javascript:;'>确定</a></div>"
 							   + "<input type='text' name='imgTitleInput' value='"
 				               + msg + "' /></div></div>"
 				               + "<input type='hidden' name='referer' value=''><input type='hidden' name='formhash' value='"+FORMHASH+"' />"
 				               + "<input type='hidden' name='editpicsubmit' value='true'>"
 				               + "<input type='hidden' name='title[" + nowIndexPicID + "]' value=''></form>";
 				showInput(source.id, inputstr, function() {
 					jQuery("#append_parent .sInput").css("z-index", 602);
 					var selectStr = "#" + source.id + "_pmenu";
 					jQuery(selectStr + " input").blur(
 						function() {
 							if(jQuery(this).val() == "") {
 								jQuery(this).val(msg);
 							}
 					});
 					jQuery(selectStr + " a").click(function() {
 						var inputvalue = jQuery(selectStr + " input").val();
 						jQuery(selectStr + " input[name ^= 'title']").attr("value", inputvalue);
 						ajaxpost("imgTitleForm_" + nowIndexPicID, "return_imgTitleForm");
 					});
 				});
 			}

			function showClickedPic(imgsrc) {
				jQuery(".pd_container").html("<p><img src='"+IMGDIR+"/loading.gif' /></p>");
				jQuery("#append_parent .sInput").remove();
				reCenterContainer(1);
				var detailImg = jQuery("<img />");
				detailImg.on("load", function(){
					jQuery(".pd_container").html((jQuery(this)));
					reFitImg(jQuery(this), viewLimit);
					jQuery(".pd_container").append("<p class='picTitle'>"
												   + jQuery(".pfv_container img:eq(" + clickedPicIndex +")").data("pictitle")
												   + "</p>");
					if(UID == authorID) {
						jQuery(".pd_container").append("<p><a id='imgTitleSetAnchor' href='javascript:;'>编辑标题</a></p>");
					}
					jQuery(".pd_container #imgTitleSetAnchor").click(function() {
						setImgTitle(this);
					});
					reCenterContainer(2);
				});
				detailImg.attr("src", imgsrc);
			}

			function showClickedPicComment(picid) {
				var ccobj = jQuery("#commentPanel .commentContainer");
				ccobj.html("<p class='xs2' style='padding: 0 15px'>评论加载中...</p>");
				jQuery.getJSON("api/blog/api_pic.php?pid=" + picid + "&feedid=" + feedID + "&type=home" , function(data){
					if(data){
						jQuery("#commentPanel").on("click", function(event) {event.stopPropagation();});
						ccobj.html('<div class="content2" style="background:none;border:none;"><div class="comment" style="background:none;">'
									 + '<p class="xs2" style="padding: 0 14px">评论</p></div></div>');
						var string = "<ul class='comments mtm'>";
						if(data.comment != undefined || data.comment !=null) {
							jQuery(".comment p", ccobj).text(jQuery(".comment p", ccobj).text() + " (" + data.comment.length + "条)");
							jQuery.each(data.comment, function(key,comment) {
								var commentTime = gettime(comment.dateline);
								var uid = comment.uid;
								if (comment.anonymity > 0){ //专区马甲
									string +='<li><div class="imgCom"><a id="'+comment.authorid+'" href="forum.php?mod=group&fid='+comment.authorid+'" target="_blank"><img style="border:none;" width="32px" height="32px" src="'+comment.ficon+'"/></a></div>';
									string +='<div class="words"><a id="'+comment.authorid+'" href=\'forum.php?mod=group&fid='+comment.authorid+'\' target="_blank"><span>'+comment.realname+':</span></a>';
								}else{
									string +='<li><div class="imgCom"><a id="'+comment.authorid+'" href="home.php?mod=space&uid='+comment.authorid+'" target="_blank"><img style="border:none;" width="32px" height="32px" src="uc_server/avatar.php?uid='+comment.authorid+'&size=small"/></a></div>';
									string +='<div class="words"><a id="'+comment.authorid+'" href=\'home.php?mod=space&uid='+comment.authorid+'\' target="_blank" class="perAuth"><span class="clmr">'+comment.realname+'</span></a><span>:</span>';
								}
								string +=comment.message+'<em>&nbsp;('+commentTime;
								string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a href="javascript:;" onclick="javascript:commentReply00(this);" id="'+comment.realname+'+'+comment.cid+'">回复</a>';
								if( checkUidInRepeat(comment.authorid) || (comment.anonymity == -1 && checkUidInRepeat(comment.realuid)) ){
									string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" href="javascript:;" id="'+str[0]+'+'+comment.cid+'+'+comment.uid+'+'+str[1]+'+'+str[2]+'" onclick="javascript:commentDelete00(this);">删除</a>';
								}
								string +=')</em></div></li>';
							});
						}
						string += '</ul><div class="commentSay" style="background:none; border-top:1px dotted #333;margin-top:10px;"><form action="home.php?mod=spacecp&ac=comment&handlekey=qcpic" method="post" id="quickcommentform_'+ picid +'" onsubmit="ajaxpost(\'quickcommentform_' + picid +'\', \'return_qcpic_' + picid +'\'); doane(event);disComment(this);"><input type="hidden" value="'+picid+'" name="id"><input type="hidden" value="picid" name="idtype"><input type="hidden" value="true" name="commentsubmit"><input type="hidden" value="true" name="quickcomment"><input type="hidden" name="formhash" value="'+FORMHASH+'" /><input type="hidden" name="cid" /><input type="hidden" name="atjson" /><textarea name="message" style="width: 240px"type="text"></textarea><div class="mtn z"><input class="W_checkbox mrngroup" type="checkbox" name="forward" onclick="setTransBlog();">评论时同时转发<br/><input class="W_checkbox mrngroup" type="checkbox" name="anonymity" value="-1" onclick="setTransBlog();"><label for="anonymity">匿名评论</label></div><input class="btn_comment mtn" disabled="disabled" style="background-position: 0 0;" type="submit" value="" /></form><div id="return_qcpic_'+picid+'" style="display:none;"></div></div>';

						//为评论框添加Autocomplete事件
						function textAreaAcHandle(event) {
							if(!jQuery("#append_parent .acwrapper").length) {
								jQuery("#append_parent").append("<div class='acwrapper' style='z-index:999;'></div>");
							}

							textAreaAutoComplete(event, this, jQuery("#append_parent .acwrapper"));
						}

						jQuery(".comment", ccobj).append(string);
						fetchFeedAuth(jQuery('.comment', ccobj));
						jQuery('#picDetail textarea[name=message]').trigger("myCustomEvent2")
														.on("focus", textAreaAcHandle)
														.on("blur", textAreaAcHandle)
														.data('noTag', true);
					}
				});
			}

			function loadClickedPic(nowIndex) {
				if(nowIndex != clickedPicIndex) {
					var diffNum = clickedPicIndex - nowIndex;
					jQuery(".pfv_container img:eq(" + clickedPicIndex +")").removeClass("selected");
					jQuery(".pfv_container img:eq(" + nowIndex +")").addClass("selected");
					jQuery(".pfv_container").stop();
					jQuery(".pfv_container").css("left", basePos - clickedPicIndex * baseImgWidth);
					clickedPicIndex = nowIndex;
					jQuery(".pfv_container").animate({
						left: parseInt(jQuery(".pfv_container").css("left")) + diffNum * baseImgWidth
					}, 200, 'easeOutQuint', function(){});
					showClickedPic(jQuery(".pfv_container img:eq(" + nowIndex +")").attr("src").replace(".thumb", ""));
					showClickedPicComment(jQuery(".pfv_container img:eq(" + nowIndex +")").data("picid"));
				}
			}

			function keyBoardNav(event) {
				if(event.keyCode == 39 && clickedPicIndex < picIDArray.length - 1) {
					loadClickedPic(clickedPicIndex + 1);
				} else if(event.keyCode == 37 && clickedPicIndex > 0) {
					loadClickedPic(clickedPicIndex - 1);
				}
			}

			var keyBoardNavOff = function() {
				jQuery(document).off("keydown", keyBoardNav);
			};

			var keyBoardNavOn = function() {
				jQuery(document).on("keydown", keyBoardNav);
			};

			jQuery("#append_parent").on("focus", "input, textarea", keyBoardNavOff);
			jQuery("#append_parent").on("blur", "input, textarea", keyBoardNavOn);

			/* 可使用键盘/鼠标导航浏览 */
			jQuery(".pfv_container").on("click", "img", function(event) {
				event.stopPropagation();
				var nowIndex = jQuery.inArray(jQuery(this).data("picid"), picIDArray);
				loadClickedPic(jQuery.inArray(jQuery(this).data("picid"), picIDArray));
			});
			jQuery(document).on("keydown", keyBoardNav);

			jQuery(window).on("resize", resetView);

			/* 用户点击空白处退出全屏状态 a除外*/
			pcobj.click(function() {
				jQuery(document).off("keydown", keyBoardNav);
				jQuery(window).off("resize", resetView);
				jQuery("#append_parent").off("focus", "input, textarea", keyBoardNavOff);
				jQuery("#append_parent").off("blur", "input, textarea", keyBoardNavOn);
				hideCover();
			});
			jQuery(pcobj).on("click","a", function(event) { event.stopPropagation();});
		});
	});
});


//点击音乐按钮更换背景图
jQuery("body").on("click",".btn_player",function(){
			jQuery(this).attr("src","static/image/common/esn/click2listen1.gif");
});

//ajaxpost时 评论按钮变灰
function disComment(target, flag){
	var comment = jQuery("input.btn_comment", target);
	if (flag === 'remove'){
		comment.removeAttr("disabled");
		comment.removeAttr("style");
		comment.css("cursor", "pointer");
		jQuery(textarea, target).removeAttr("readonly");
	}else{
		comment.attr("disabled","disabled");
		comment.css("background-position","0px 0px");
		comment.css("cursor", "auto");
		jQuery(textarea, target).attr("readonly","readonly");
	}

}

//检测评论按钮是否可用
//jQuery("body").on("keyup","textarea[name=message]",function(){
jQuery("body").on("keyup",textarea,function(){
			var target = jQuery(this);
			//var comment =jQuery(".btn_comment",target.parent());
			var comment =jQuery(".btn_comment",target.parents('form'));
			if(target.val() ==""){
				comment.attr("disabled","disabled");
				comment.css("background-position","0px 0px");
				comment.css("cursor", "auto");
			}else{
				comment.removeAttr("disabled");
				comment.removeAttr("style");
				comment.css("cursor", "pointer");
			}
});

function textAreaACFun(event) {
	if(!jQuery("#feed .acwrapper").length) {
		jQuery("#feed").append("<div class='acwrapper'></div>");
	}

	textAreaAutoComplete(event, this, jQuery("#feed .acwrapper"));
}

function addACListener(target) {
		jQuery(target).on("focus", textAreaACFun);
		jQuery(target).on("blur", textAreaACFun);
}

function removeACListener(target) {
		jQuery(target).off("focus", textAreaACFun);
		jQuery(target).off("blur", textAreaACFun);
}

//展开、收起评论
jQuery("body").on("click",".btn_comment",function(){
			var anchor = jQuery(this);
			conanchor = anchor.parent().parent().parent();
			commentanchor = jQuery(".comment",conanchor)
			var comment = jQuery(".commentSay",commentanchor);
			var messageObj = jQuery(textarea, comment);
			messageObj.data('noTag', true);

			if (comment.is(':hidden')){
				comment.slideDown(150, "easeOutQuint", function(){
					messageObj.width(parseInt(messageObj.parent().width()) - parseInt(messageObj.siblings('input.btn_comment').width()) - 22);
				});
				addACListener(jQuery(textarea, comment));
			}else{
				comment.slideUp(150, "easeOutQuint");
				removeACListener(jQuery(textarea, comment));
			}

});

// 编辑动态
jQuery("body").on("click",".edit_feed",function(){
	var anchor = jQuery(this);
	var string = anchor.attr("id");
	var str = string.split("+");
	window.location.href = "home.php?mod=spacecp" + "&" + "ac=blog&blogid=" + str[2] + "&op=edit";
	return false;
});

// 删除动态
jQuery("body").on("click",".delete_feed",function(){
			var anchor = jQuery(this);
			var string = anchor.attr("id");
			var str = string.split("+");
			var func = function(){
				jQuery.getJSON("api/blog/api_delfeed.php?uid="+str[0]+"&"+"feedid="+str[1]+"&code="+jQuery.md5('esn'+str[0]+str[1]),function(data){
					if(data.success == 'Y'){
							showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>删除成功</span>", 3000);
							jQuery("#"+str[1]).slideUp("fast", function() {
								jQuery("#"+str[1]).remove();
								currNum--;
							});
					}else{
						showDialog(data.message,'notice');
					}
				});
			}
			showDialog('<div style="font-size:14px;">确定删除这条动态？</div>', 'confirm','', func);
});

// 图片放大缩小
jQuery("body").on("click",".showImage .thumb, .showImage .detail",function(){
	var imageContainer = jQuery(this).parent();
	var isInTransmit = imageContainer.parent().hasClass("transmit");
	if(imageContainer.data("isProcessing")) return;

	if(imageContainer.data("isDetailed")) {
		imageContainer.data("isProcessing", true);
		var detailImg = jQuery(this);
		var thumbImg = jQuery("img.thumb", imageContainer);
		detailImg.css("position", "absolute");
		detailImg.css("visibility", "hidden");
		thumbImg.css("display", "");
		thumbImg.animate({
			width: imageContainer.data("thumbSize")[0],
			height: imageContainer.data("thumbSize")[1],
			marginLeft: thumbImg.css("margingLeft") ? thumbImg.css("marginLeft") : 0
		}, 200, 'easeOutExpo', function() {
			imageContainer.data("isDetailed", false);
			imageContainer.data("isProcessing", false);
		});
	} else {
		imageContainer.data("isProcessing", true);

		var thumbImg = jQuery(this);
		imageContainer.data("thumbSize", [thumbImg.width(), thumbImg.height()]);

		var showDetailImg = function(thumbImg, detailImg) {
			detailImg.off('load');
			detailImg.addClass("detail");
			imageContainer.append(detailImg);
			var detailImgWidth = isInTransmit ? 472 : 504;
			if(detailImg.width() > detailImgWidth) {
				detailImg.width(detailImgWidth);
				detailImg.height("auto");
			}
			imageContainer.data("detailSize", [detailImg.width(), detailImg.height()]);
			var imgMarginLeft = detailImgWidth - detailImg.width() > 0 ? (detailImgWidth - detailImg.width())/2 : 0;
			thumbImg.animate({
				width: detailImg.width(),
				height: detailImg.height(),
				marginLeft: imgMarginLeft
			}, 200, 'easeOutExpo', function() {
				thumbImg.css("display", "none");
				detailImg.css("position", "static");
				detailImg.css("visibility", "visible");
				detailImg.css("marginLeft", imgMarginLeft);
				imageContainer.data("isDetailed", true);
				imageContainer.data("isProcessing", false);
			});
		};

		if(imageContainer.data("isDetailImgLoaded")) {
			var detailImg = jQuery("img.detail", imageContainer);
			showDetailImg(thumbImg, detailImg);
		} else {
			var loadingGif = jQuery("<img src='"+IMGDIR+"/loading.gif'"
								   + " style='top: " + jQuery(this).height()/2
								   + "px; left: " + (jQuery(this).width()-16)/2 + "px; position: absolute;'"+ "/>");
			imageContainer.prepend(loadingGif);
			var detailImg = jQuery("<img />");
			detailImg.on("load", function(){
				imageContainer.data("isDetailImgLoaded", true);
				loadingGif.remove();
				showDetailImg(thumbImg, detailImg);
			});
			var imgsrc = jQuery(this).hasClass("external") ? jQuery(this).attr("src") : jQuery(this).attr("src").replace(".thumb", "");
			detailImg.attr("src", imgsrc);
		}
	}

});


// 显示更多评论
jQuery("body").on("click",".moreCom",function(){
		var anchor = jQuery(this);
		var feedid = anchor.attr("value");
		var str = jQuery("#"+feedid).attr("commentdata").split("+");
		var string='';

		jQuery.getJSON("api/blog/api_comment.php?feedid="+feedid,function(data){
			jQuery.each(data.comment, function(key,comment) {
				var commentTime = gettime(comment.dateline);
				var uid = comment.uid;
				if (comment.anonymity > 0){ //专区马甲
					string +='<li><div class="imgCom"><a class="perPanel" href="forum.php?mod=group&fid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="'+comment.ficon+'"/></a></div>';
					string +='<div class="words"><a class="perPanel"  href=\'forum.php?mod=group&fid='+comment.authorid+'\' target="_blank"><span>'+comment.realname+':</span></a>';
				}else{
					string +='<li><div class="imgCom"><a class="perPanel" href="home.php?mod=space&uid='+comment.authorid+'" target="_blank"><img width="32px" height="32px" src="uc_server/avatar.php?uid='+comment.authorid+'&size=small"/></a></div>';
					string +='<div class="words"><a class="perPanel"  href=\'home.php?mod=space&uid='+comment.authorid+'\' target="_blank"><span class="clmr">'+comment.realname+'</span></a><span>:</span>';
				}
				string +=comment.message+'<em>&nbsp;('+commentTime;
				string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline;">&nbsp;|&nbsp;</a><a class="minlk commentReply" href="javascript:;" id="'+comment.realname+'_'+feedid+'_'+comment.cid+'" style="display:inline;">回复</a>';
				if( checkUidInRepeat(comment.authorid) || (comment.anonymity == -1 && checkUidInRepeat(comment.realuid)) ){
					string +='<a class="minlk" style="color:#909090;text-decoration:none;display:inline;">&nbsp;|&nbsp;</a><a class="minlk commentDelete" href="javascript:;" id="'+str[0]+'+'+comment.cid+'+'+comment.uid+'+'+str[1]+'+'+feedid+'" style="display:inline;">删除</a>';
				}
				string +=')</em></div></li>';
			});
			jQuery(".comments",anchor.parent()).html(string);
			repaginate(anchor,5);
			
			fetchFeedAuth('#'+feedid+' .comments');
		});
});

// 收藏,取消收藏
jQuery("body").on("click",".favorite",function(){
			var anchor = jQuery(this);
			var string = anchor.attr("id");
			var str = string.split("+");
			jQuery.getJSON("api/blog/api_favorite.php?id="+str[0]+"&"+"idtype="+str[1]+"&uid="+str[2]+"&feedid="+str[3]+"&code="+jQuery.md5('esn'+str[0]+str[1]+str[2]+str[3]),function(data){
				if(data.success == 'Y'){
					showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>收藏成功</span>", 3000);
					anchor.css("display","none");
					jQuery(".cancel_favorite",anchor.parent()).css("display","");
				}else{
					showDialog(data.message,'notice');
				}
			});
});
jQuery("body").on("click",".cancel_favorite",function(){
			var anchor = jQuery(this);
			var string = anchor.attr("id");
			var str = string.split("+");
			jQuery.getJSON("api/blog/api_delfavorite.php?id="+str[0]+"&"+"idtype="+str[1]+"&uid="+str[2]+"&feedid="+str[3]+"&code="+jQuery.md5('esn'+str[0]+str[1]+str[2]+str[3]),function(data){
				if(data.success == 'Y'){
					showGrowl("<span class='pc_icn_mark z' style='margin-top: 3px'></span><span class='xw1 xs2 mlmgroup'>取消收藏成功</span>", 3000);
					anchor.css("display","none");
					jQuery(".favorite",anchor.parent()).css("display","");
				}else{
					showDialog(data.message,'notice');
				}
			});
});

// 显示次要链接
jQuery("body").on("mouseenter", "dl.list", function() {
	if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
		jQuery(".minlk", this).css("display", "inline");
	} else {
		jQuery(".minlk", this).fadeIn(150);
	}
});
jQuery("body").on("mouseleave", "dl.list", function() {
	 if(jQuery.browser.msie && parseInt(jQuery.browser.version, 10) < 9) {
	 	jQuery(".minlk", this).css("display", "none");
	 } else {
	 	jQuery(".minlk", this).hide();
	 }
});


//*****************************************************************//
//*****************************************************************//



function update_timestr(){
	var update_time = setInterval(function(){
	 	jQuery(".update_time").each(function(){
			var time = jQuery(this).html();
			var strtime = gettime(time);
			var infoanchor = jQuery(".info",jQuery(this).parent());
			jQuery("span",infoanchor).html(strtime);
		});
	},60000);

	var update_time2 = setInterval(function(){
	 	jQuery(".update_time2").each(function(){
			var time = jQuery(this).html();
			var strtime = gettime(time);
			var ctanchor = jQuery("em",jQuery(this).parent());
			var optlink = ctanchor.html().substring(ctanchor.html().toLowerCase().indexOf('<a'), ctanchor.html().length-1);
			ctanchor.html('&nbsp;('+strtime+optlink+')');
		});
	},60000);
}


function repaginate(anchor, num){
		var currentPage = 0;
		var numPerPage = num;
		var ul_div = jQuery('ul.comments',anchor.parent());
		ul_div.bind('repaginate', function() {
		      ul_div.find('li').hide()
		        .slice(currentPage * numPerPage,
		          (currentPage + 1) * numPerPage)
		        .show();
		});
		var numRows = ul_div.find('li').length;
		var numPages = Math.ceil(numRows / numPerPage);
		if(numPages>1){
			var pager_div = jQuery('<div class="pager"></div>');
			for (var page = 0; page < numPages; page++) {
			jQuery('<span class="page-number"></span>').text(page + 1)
					.bind('click', {newPage: page}, function(event) {
					  currentPage = event.data['newPage'];
					  ul_div.trigger('repaginate');
					  jQuery(this).addClass('active')
						.siblings().removeClass('active');
					}).appendTo(pager_div).addClass('clickable');
			}
			pager_div.insertAfter(ul_div).find('span.page-number:last').addClass('active');
			currentPage = numPages-1;
		}
		ul_div.trigger('repaginate');
		jQuery(".commentSay",anchor.parent()).css("display","");
		anchor.remove();
}



function comment_li_height( anchor){
		var string = anchor.css("height");
		string = string.substring(0,2);
		if(string<50){
			anchor.css("height","31px");
		}
	}

// 动态外部图片缩略图大小调整
function externalImgReFit(img) {
	var imageContainer = jQuery(img).parent();
	jQuery("img:first", imageContainer).remove();
	var isInTransmit = imageContainer.parent().hasClass("transmit");
	var viewLimit = [isInTransmit ? 472 : 504, 120];
	reFitImg(img, viewLimit);
	jQuery(img).addClass("thumb");
	jQuery(img).css("position", "static");
	jQuery(img).css("visibility", "visible");
}

// 图片全屏查看状态下 成功设置图片标题
function succeedhandle_imgtitle(url, msg, values) {
	var obj = jQuery("#imgTitleSetAnchor_pmenu");
	var titleVal = jQuery("input[name^='title']", obj).val();
	var picIndexID = jQuery("form", obj).attr("id").split("_")[1];
	var picIndex;
	jQuery(".pfv_container img").each(function(i) {
		if(picIndexID == jQuery(this).data("picid")) { picIndex = i; }
	});
	jQuery(".pd_container .picTitle").text(titleVal);
	jQuery(".pfv_container img:eq(" + picIndex +")").data("pictitle", titleVal);
	obj.remove();
}

// 图片全屏查看状态下 成功发表评论
function succeedhandle_qcpic(url, msg, values) {
	var ccobj = jQuery("#commentPanel .commentContainer");
	var msg = values.message || '';
	var strtime = gettime(Date.parse(new Date()) / 1000);

	var str = ccobj.attr("commentdata").split("+");

	var ck = jQuery("input[name=anonymity]", ccobj).attr("checked");
	var	p = repeatstatus((ck=='checked')),
		ufid = p.ufid,
		perurl = p.perurl,
		imgurl = p.imgurl,
		realname = p.realname,
		auth = p.showauth===true?showMyAuth():'';

	var string ='<li><div class="imgCom"><a id="'+ufid+'" href="'+perurl+'" target="_blank"><img style="border:none;" width="32px" height="32px" src="'+imgurl+'"/></a></div><div class="words"><a id="'+ufid+'" href=\''+perurl+'\' target="_blank" class="perAuth" added="added"><span class="clmr">'+realname+'</span></a>'+auth+'<span>:</span>'+msg+'<em>&nbsp;('+strtime;
	string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a onclick="javascript:commentReply00(this);" href="javascript:;" id="'+realname+'+'+values.cid+'">回复</a>';
	string +='<a style="color:#909090;text-decoration:none;">&nbsp;|&nbsp;</a><a class="commentDelete" onclick="javascript:commentDelete00(this);" href="javascript:;" id="'+str[0]+'+'+values.cid+'+'+UID+'+'+str[1]+'+'+str[2]+'" >删除</a>';
	string +=')</em></div></li>';
	jQuery(".comments", ccobj).append(string);
	jQuery(textarea, ccobj).val("").css("height", 16).removeAttr("readonly").growfield('restart');
	jQuery('input[name=cid]', ccobj).val('');
	jQuery(".btn_comment", ccobj).attr("disabled","disabled");
	jQuery(".btn_comment", ccobj).css("background-position","0px 0px");
	jQuery(".btn_comment", ccobj).css("cursor", "auto");
}

var repeatstatus = function(anony){
		var repeat = {ufid:'', perurl:'', imgurl:'', realname:'', showauth:false};

		if (anony === true) { 		//匿名
			repeat.ufid = '-1';
			repeat.perurl = 'home.php?mod=space&uid=-1';
			repeat.imgurl = 'uc_server/avatar.php?uid=-1&size=small';
			repeat.realname = '匿名';

		}else if (REPEATID > 0){	//专区马甲
			var	o = jQuery('#repeat_'+REPEATID).data('repeatInfo');
			repeat.ufid = o.fid;
			repeat.perurl = 'forum.php?mod=group&fid='+o.fid;
			repeat.imgurl = o.avt;
			repeat.realname = o.name;

		}else{						//真实身份
			repeat.ufid = UID;
			repeat.perurl = 'home.php?mod=space&uid='+UID;
			repeat.imgurl = 'uc_server/avatar.php?uid='+UID+'&size=small';
			repeat.realname = REALNAME;
			repeat.showauth = true;
		}
		return repeat;

}

var checkUidInRepeat = function(id){
	var flag = false;
	if (id == UID) flag = true;
	jQuery('a[id^=repeat_]', '#myrepeats_menu').each(function(){
			if (jQuery(this).data('repeatInfo').fid == id) flag = true;
		});
	return flag;
}

jQuery(document).on('click', 'input[name=forward]', function(){	//评论同时转发选中后，可以匿名转发
		var a = jQuery('input[name=anonymity]', jQuery(this).parent()).next();
		if (jQuery(this).attr('checked')=='checked'){
			a.append('<span>+转发</span>');
		}else{
			a.find('span').remove();
		}
	});


jQuery(document).on('click', '#t_all', function(){
	jQuery('#filterType input').each(function(){
		jQuery(this).removeAttr('checked');
	});

	if (!jQuery(this).hasClass('xi2 xw1')) jQuery(this).addClass('xi2 xw1');
	loadfilterType('');
});
jQuery(document).on('click', '#filterType input', function(){
	if (jQuery(this).attr('checked') == 'checked') {
		jQuery('#filterType input:[name=t_all]').removeAttr('checked');
	}

	var idtype = [];
	jQuery('#filterType input').each(function(){
			if (jQuery(this).attr('checked') == 'checked') idtype.push(jQuery(this).attr('idtype'));
		});

		if (idtype.length == 0){
			jQuery('#t_all').addClass('xi2 xw1');
			loadfilterType('');
		}else{
			jQuery('#t_all').removeClass('xi2 xw1');
			loadfilterType(idtype);
		}
});
function disFilterCheck(flag){
	if (flag) {
		jQuery('#filterType input').attr('disabled', 'disabled');
	}else{
		jQuery('#filterType input').removeAttr('disabled');
	}
}
function appendFilter(){
		jQuery('#filterType').append('<a id="t_all" class="xi2 xw1" href="javascript:;">全部</a>'
				+ '<input type="checkbox" name="t_doing" value="blogid" class="pc" idtype="blogid" id="search_do"/><label for="t_doing">记录</label>'
				+ '<input type="checkbox" name="t_share" value="link" class="pc" idtype="link"/><label for="t_share">转发</label>'
				+ '<input type="checkbox" name="t_pic" value="albumid" class="pc" idtype="albumid"/><label for="t_pic">图片</label>'
				+ '<input type="checkbox" name="t_video" value="flash" class="pc" idtype="flash"/><label for="t_video">视频</label>'
				+ '<input type="checkbox" name="t_music" value="music" class="pc" idtype="music"/><label for="t_music">音乐</label>'
				+ '<input type="checkbox" name="t_class" value="class" class="pc"  idtype="class"/><label for="t_class">课程</label>'
				+ '<input type="checkbox" name="t_doc" value="doc" class="pc"  idtype="doc"/><label for="t_doc">文档</label>'
				+ '<input type="checkbox" name="t_case" value="case" class="pc"  idtype="case"/><label for="t_case">案例</label>'
		);
		jQuery('#filterType *').css({
				'vertical-align': 'middle',
				'margin-left': '5px'
			});
			jQuery('#filterType a').css({
					'color': '#444444',
					'border-right': '1px dotted #999',
					'padding-right': '10px'
				});
			jQuery('#filterType input').css({
					'margin-left': '10px'
				});

}