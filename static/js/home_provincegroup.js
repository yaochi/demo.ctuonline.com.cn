/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: home_provincegroup.js 7643 2011-05-10 10:54:32Z qiaoyongzhi $
*/
function sendrequest(regname,did)
{
  var url="api/sso/getuserprogroup.php?pro=group"+"&"+"regname="+regname;
  xmlHttp=GetXmlHttpObject();
  if (xmlHttp==null)
  {
    //alert ("Browser does not support HTTP Request");
    return;
  }
    xmlHttp.onreadystatechange=function(){getinfor(did);}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function getinfor(did)
{
if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
 {
	 var content=xmlHttp.responseText;
	 if(content && content != 'null') {
 		var json = eval("(" + content + ")");
 		var group = json&&json.groupname ? json.groupname : "中国电信";
 		document.getElementById(did).innerHTML="["+group+"]";
 	}
 }
}


function GetXmlHttpObject()
{
	var request = false;
	if(window.XMLHttpRequest) {
		request = new XMLHttpRequest();
		if(request.overrideMimeType) {
			request.overrideMimeType('text/xml');
		}
	} else if(window.ActiveXObject) {
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for(var i=0; i<versions.length; i++) {
			try {
				//alert(versions[i]);
				request = new ActiveXObject(versions[i]);
				if(request) {
					return request;
				}
			} catch(e) {}
		}
	}
	return request;
}

