if(!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '');
  };
}
function trim(str) {
	if(str === undefined || str === null) return '';
	return str.replace(/^\s+|\s+$/g, '');
}
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function byId(id, doc) {
  doc = doc || window.document;
  if (doc.getElementById) {
    return doc.getElementById(id);
  } else if (doc.all) {
    return doc.all.item(id);
  }
  return null;
}

/**
 * Function for Prev/Next of pages
 */
function doPageIndex(ind) {
  var frm = document.forms[0];
  byId('pg_ind').value = ind;
  for(var i = 0; i < frm.elements.length; i++) {
    if (frm.elements[i].type == 'submit') {
      frm.elements[i].click();
      return false;
    }
  }
  frm.submit();
  return false;
}

var g_cookie_pre = '_yh';

/**
 * retrieves a named value from cookie
 *
 * @param   string  name    name of the value to retrieve
 * @return  string  value   value for the given name from cookie
 */
function readcookie(name) {
  name = g_cookie_pre + name;
  var c = document.cookie;
  var p0 = c.indexOf(name + "=");
  if (p0 != -1) {
    var p1 = c.indexOf(";", p0);
    if (p1 == -1) p1 = c.length;
    return unescape(c.substring(p0 + name.length + 1, p1));
  }
  return null;
}
function clearcookie(name, path, domain, secure) {
  name = g_cookie_pre + name;
  document.cookie = name + "=;expires=Fri, 02-Jan-1970 00:00:00 GMT" +
    ( (path)    ? ";path=" + path : "") +
    ( (domain)  ? ";domain=" + domain : "") +
    ( (secure)  ? ";secure" : "");
}

/**
 * stores a named value into cookie
 *
 * @param   string  name    name of value
 * @param   string  value   value to be stored
 * @param   number  expiredays expire days
 * @param   string  path
 * @param   string  domain
 * @param   boolean secure
 */
function setcookie(name, value, expiredays, path, domain, secure) {
  name = g_cookie_pre + name;
  var expires;
  if(typeof expiredays == "number") {
    expires = new Date((new Date()).getTime() + expiredays * 24 * 3600000);
  }
  else {
    expires = new Date((new Date()).getTime() + 365 * 24 * 3600000);
  }
  document.cookie = name + "=" + escape(value) +
    ( (expires) ? ";expires=" + expires.toGMTString() : "") +
    ( (path)    ? ";path=" + path : "") +
    ( (domain)  ? ";domain=" + domain : "") +
    ( (secure)  ? ";secure" : "");
}

function closeWindow() {
  try {
    self.close();
  }catch(e){}
  try {
    window.close();
  }catch(e){}
  try {
    window.open('about:blank', '_self').close();
  }catch(e){}
}

//图片预览
function img_change(obj, imgid, allowext, msg) {
	if(!allowext || allowext == '') {
		allowext = '.png;.jpg;';
	}
	var fn = obj.value;
	var ext = fn.toLowerCase().substring(fn.length - 4);
	if(allowext.indexOf(ext+';') < 0) {
		if(msg) alert(msg);
		else alert("Upload "+allowext+" file only.");
		return false;
	}
	var objUrl = getObjectURL(obj.files[0]);
	if (objUrl) {
		byId(imgid).src = objUrl;
	}
}

//建立一個可存取到該file的url
function getObjectURL(file) {
	var url = null ; 
	if (window.createObjectURL!=undefined) { // basic
		url = window.createObjectURL(file) ;
	} else if (window.URL!=undefined) { // mozilla(firefox)
		url = window.URL.createObjectURL(file) ;
	} else if (window.webkitURL!=undefined) { // webkit or chrome
		url = window.webkitURL.createObjectURL(file) ;
	}
	return url ;
}


// Get XML document
function getXMLDoc(str) {
  var i = document.implementation;
  if (!i || !i.createDocument) {
    // Try IE objects
    var xdoc = null;
    try {
      xdoc = new ActiveXObject('MSXML2.DOMDocument');
    } catch (ex) {
      try {
        xdoc = new ActiveXObject('Microsoft.XmlDom');
      } catch (ex) {}
    }
    if(xdoc) xdoc.loadXML(str);
    return xdoc;
  }
  //firefox
  var domparser = new DOMParser();
  try {
    var xdoc = domparser.parseFromString(str, 'text/xml');
    return xdoc;
  } catch (ex) {}
  return null;
}

// Returns XmlHttpRequest or null
function getXMLHttp() {
  if(window.XMLHttpRequest) return new XMLHttpRequest();
  if(window.ActiveXObject) {
    if(window._XmlHttpActiveX) return new ActiveXObject(window._XmlHttpActiveX);
    var o = ["Msxml2.XMLHTTP.3.0", "Msxml3.XMLHTTP", "Msxml2.XMLHTTP", "Microsoft.XMLHTTP"];
    for(var i=0;i<o.length;i++) {
      try {
         var Req = new ActiveXObject(o[i]);
         window._XmlHttpActiveX = o[i];
         return Req;
      }
      catch(e){}
    }
  }
  return null;
}

//while xml:var items = xmlDoc.childNodes[1];items.childNodes.length;
//get string from xml:var s = objxml.xml || new XMLSerializer().serializeToString(objxml);
//noEncodeURI for php use of file_get_contents("php://input");
function postAjax(callback_fun, url, body, noEncodeURI, noPost, oxmlhttp) {
  var xmlhttp = oxmlhttp;
  if(!xmlhttp) {
    xmlhttp = getXMLHttp();
  }
  if(!noPost && !body) {
    xmlhttp.open('GET', encodeURI(url), true);
  }
  else {
    xmlhttp.open('POST', encodeURI(url), true);
  }
  xmlhttp.setRequestHeader('Content-Type' , 'application/x-www-form-urlencoded');

  xmlhttp.setRequestHeader('Pragma', 'no-cache');
  xmlhttp.setRequestHeader('Cache-Control', 'no-cache');
  xmlhttp.setRequestHeader('If-Modified-Since', 'Thu, 01 Jun 1970 00:00:00 GMT');

  if(callback_fun) {
    xmlhttp.onreadystatechange = function()
    {
      if (xmlhttp.readyState == 4) {
        if (xmlhttp.status == 200) {
          var s = xmlhttp.responseText;
          //s = s.replace(/<\?[^?]+\?>|<!DOCTYPE[^>]+>/g, '');
          //s = s.replace(/ ?\/>/g, ' />');
          //var objxml = getXMLDoc(s); //will error:要素が見つかりません。
          var objxml = xmlhttp.responseXML;
          if(!objxml) {
            objxml = getXMLDoc(s);
          }
          callback_fun(s, objxml);
        }
        else if (xmlhttp.status == 404) {
          callback_fun(url + '\n404 Not Found', null);
        }
        xmlhttp = null;
      }
      return false;
    };
  }
  if(typeof body === 'object') {
    var b1 = '';
    for(key in body) {
        if(body[key] !== undefined) {
          b1 += key + '=' + body[key]+'&';
        }
    }
    body = b1;
  }
  if(!body) body = null;
  //encodeURIComponent
  if(!noEncodeURI && body) body = encodeURI(body);
  xmlhttp.send(body);
  return true;
}

