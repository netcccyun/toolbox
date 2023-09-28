var interval1,interval2;
function setCookie(name,value)
{
	var exp = new Date();
	exp.setTime(exp.getTime() + 30*1000);
	document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name)
{
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
	if(arr=document.cookie.match(reg))
		return unescape(arr[2]);
	else
		return null;
}
function delCookie(name)
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null){
      document.cookie= name + "="+cval+";expires="+exp.toGMTString();
    }
}
function getqrpic(force){
	force = force || false;
	cleartime();
	var qrsig = getCookie('qrsig');
	var qrimg = getCookie('qrimg');
	var qrurl = getCookie('qrurl');
	if(qrsig!=null && qrimg!=null && qrurl!=null && force==false){
		$('#qrimg').attr('qrsig',qrsig);
		$('#qrimg').attr('qrurl',qrurl);
		$('#qrimg').html('<img id="qrcodeimg" onclick="getqrpic(true)" src="data:image/png;base64,'+qrimg+'" title="点击刷新">');
		if( /Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/i.test(navigator.userAgent)) {
			$('#mobile').show();
		}
		interval1=setInterval(loginload,1000);
		interval2=setInterval(qrlogin,3000);
	}else{
		var getvcurl='login.php?do=getqrpic&r='+Math.random(1);
		$.get(getvcurl, function(d) {
			if(d.saveOK ==0){
				setCookie('qrsig',d.qrsig);
				setCookie('qrimg',d.data);
				setCookie('qrurl',d.url);
				$('#qrimg').attr('qrsig',d.qrsig);
				$('#qrimg').attr('qrurl',d.url);
				$('#qrimg').html('<img id="qrcodeimg" onclick="getqrpic(true)" src="data:image/png;base64,'+d.data+'" title="点击刷新">');
				if( /Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/i.test(navigator.userAgent)) {
					$('#mobile').show();
				}
				interval1=setInterval(loginload,1000);
				interval2=setInterval(qrlogin,3000);
			}else{
				alert(d.msg);
			}
		}, 'json');
	}
}
function qrlogin(){
	if ($('#login').attr("data-lock") === "true") return;
	var qrsig=$('#qrimg').attr('qrsig');
	var url = 'login.php?do=qrlogin&qrsig='+decodeURIComponent(qrsig)+'&r='+Math.random(1);
	$.get(url, function(d) {
		if(d.saveOK ==0){
			$('#login').html('<div class="alert alert-success">登录成功！'+decodeURIComponent(d.nick)+'</div><div class="input-group"><span class="input-group-addon">QQ帐号</span><input id="uin" value="'+d.uin+'" class="form-control" /></div><br/><div class="input-group"><span class="input-group-addon">SKEY</span><input id="skey" value="'+d.skey+'" class="form-control"/></div><br/><div class="input-group"><span class="input-group-addon">P_skey</span><input id="pskey" value="'+d.pskey+'" class="form-control"/></div><br/><div class="input-group"><span class="input-group-addon">superkey</span><input id="superkey" value="'+d.superkey+'" class="form-control"/></div><br/><a href="./index2.html">返回重新获取</a>');

			$('#qrimg').hide();
			$('#mobile').hide();
			$('#submit').hide();
			$('#login').attr("data-lock", "true");
			cleartime();
		}else if(d.saveOK ==1){
			getqrpic(true);
			$('#loginmsg').html('请重新扫描二维码');
		}else if(d.saveOK ==2){
			$('#loginmsg').html('使用QQ手机版扫描二维码');
		}else if(d.saveOK ==3){
			$('#loginmsg').html('扫描成功，请在手机上确认授权登录');
		}else{
			cleartime();
			$('#loginmsg').html(d.msg);
		}
	}, 'json');
}
function loginload(){
	if ($('#login').attr("data-lock") === "true") return;
	var load=document.getElementById('loginload').innerHTML;
	var len=load.length;
	if(len>2){
		load='.';
	}else{
		load+='.';
	}
	document.getElementById('loginload').innerHTML=load;
}
function cleartime(){
	clearInterval(interval1);
	clearInterval(interval2);
	delCookie('qrsig');
	delCookie('qrimg');
	delCookie('qrurl');
}
function mloginurl(){
	var imagew = $('#qrcodeimg').attr('src');
	imagew = imagew.replace(/data:image\/png;base64,/, "");
	$('#mlogin').html("正在跳转...");
	$.post("qrcode.php?r="+Math.random(1),{image:imagew}, function(arr) {
		if(arr.code==0) {
			$('#loginmsg').html('跳转到QQ登录后请返回此页面');
			window.location.href='mqqapi://forward/url?version=1&src_type=web&url_prefix='+window.btoa(arr.url);
		}else{
			alert(arr.msg);
		}
		$('#mlogin').html("跳转QQ快捷登录");
	}, 'json');
}
function mloginurlnew(){
	var qrurl = $('#qrimg').attr('qrurl');
	$('#loginmsg').html('跳转到QQ登录后请返回此页面');
	var ua = window.navigator.userAgent.toLowerCase();
	var is_ios = ua.indexOf('iphone')>-1 || ua.indexOf('ipad')>-1;
	var schemacallback = '';
	if(is_ios){
		schemacallback = 'weixin://';
	}else if(ua.indexOf('ucbrowser')>-1){
		schemacallback = 'ucweb://';
	}else if(ua.indexOf('meizu')>-1){
		schemacallback = 'mzbrowser://';
	}else if(ua.indexOf('liebaofast')>-1){
		schemacallback = 'lb://';
	}else if(ua.indexOf('baidubrowser')>-1){
		schemacallback = 'bdbrowser://';
	}else if(ua.indexOf('baiduboxapp')>-1){
		schemacallback = 'bdapp://';
	}else if(ua.indexOf('mqqbrowser')>-1){
		schemacallback = 'mqqbrowser://';
	}else if(ua.indexOf('qihoobrowser')>-1){
		schemacallback = 'qihoobrowser://';
	}else if(ua.indexOf('chrome')>-1){
		schemacallback = 'googlechrome://';
	}else if(ua.indexOf('sogoumobilebrowser')>-1){
		schemacallback = 'SogouMSE://';
	}else if(ua.indexOf('xiaomi')>-1){
		schemacallback = 'miuibrowser://';
	}else{
		schemacallback = 'googlechrome://';
	}
	if(is_ios){
		alert('跳转到QQ登录后请手动返回当前浏览器');
		window.location.href='wtloginmqq3://ptlogin/qlogin?qrcode='+encodeURIComponent(qrurl)+'&schemacallback='+encodeURIComponent(schemacallback);
	}else{
		window.location.href='wtloginmqq://ptlogin/qlogin?qrcode='+encodeURIComponent(qrurl)+'&schemacallback='+encodeURIComponent(schemacallback);
	}
}
$(document).ready(function(){
	getqrpic();
});