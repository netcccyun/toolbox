var ajaxPost = function(url, parameter, callback, dataType) {
	if(!dataType) dataType='json';
	$.ajax({
		type: "POST",
		url: url,
		dataType: dataType,
		json: "callback",
		data: parameter,
		success: function(data) {
			if (callback == null) {
				return;
			} 
			callback(data);
		},
		error: function(error) {
			alert('创建连接失败');
		}
	});
}
var captcha_frame;
var comm_data = {
	cookie:'',
	sid:'',
	vcode:'',
	pt_verifysession:'',
	cap_cd:'',
	captcha: {
		vc:'',
		sess:'',
		cdata:'',
		websig:'',
	},
	sms: {
		issend:false,
		ticket:'',
	}
}

function trim(str){ //去掉头尾空格
	return str.replace(/(^\s*)|(\s*$)/g, "");
}

function invokeSettime(obj){
    var countdown=60;
    settime(obj);
    function settime(obj) {
        if (countdown == 0) {
            $(obj).attr("data-lock", "false");
            $(obj).text("获取验证码");
            countdown = 60;
            return;
        } else {
			$(obj).attr("data-lock", "true");
            $(obj).attr("disabled",true);
            $(obj).text("重发（" + countdown + "）");
            countdown--;
        }
        setTimeout(function() {
                    settime(obj) }
                ,1000)
    }
}

function send_sms_code(){
	var uin=trim($('#uin').val());
	var getvcurl="login.php?do=smscode&r="+Math.random(1);
	var param = {uin: uin, sms_ticket: comm_data.sms.ticket, cookie: comm_data.cookie};
	ajaxPost(getvcurl, param, function(d) {
		if(d.saveOK == 0){
			new invokeSettime("#sendsms");
			alert('发送成功，请注意查收！');
			comm_data.sms.issend = true
		}else{
			alert(d.msg);
		}
	});
}

function login(uin,pwd){
	var isMd5=$("input:radio[name='ismd5']:checked").val() || 0;
	var p=getmd5(uin,pwd,comm_data.vcode,isMd5);
	var loginurl="login.php?do=qqlogin&r="+Math.random(1);
	var param = {uin: uin, pwd: pwd, p: p, vcode: comm_data.vcode, pt_verifysession: comm_data.pt_verifysession, sid: comm_data.sid, cookie: comm_data.cookie};
	if($('.smscode').is(":visible")){
		if(comm_data.sms.issend == true){
			alert('请先发送短信验证码');
			return;
		}
		var sms_code = trim($('#sms_code').val());
		if(sms_code==''){
			alert('短信验证码不能为空！');
			return;
		}
		Object.assign(param, {sms_code: sms_code, sms_ticket: comm_data.sms.ticket});
	}
	$('#load').html('正在登录，请稍等...');
	ajaxPost(loginurl, param, function(d) {
		if(d.saveOK ==0){
			$('#login').hide();
			$('.code').hide();
			$('#submit').hide();
			$('#load').html('<div class="alert alert-success">登录成功！'+decodeURIComponent(d.nick)+'</div><div class="input-group"><span class="input-group-addon">QQ帐号</span><input id="uin" value="'+d.uin+'" class="form-control" /></div><br/><div class="input-group"><span class="input-group-addon">SKEY</span><input id="skey" value="'+d.skey+'" class="form-control"/></div><br/><div class="input-group"><span class="input-group-addon">P_skey</span><input id="pskey" value="'+d.pskey+'" class="form-control"/></div><br/><div class="input-group"><span class="input-group-addon">superkey</span><input id="superkey" value="'+d.superkey+'" class="form-control"/></div><br/><a href="'+d.loginurl+'" target="_blank" rel="noreferrer" class="btn btn-success btn-block">一键登录QQ空间</a><br/><br/><a href="./">返回重新获取</a>');
		}else if(d.saveOK ==4){
			$('#load').html('验证码错误，请重新登录。');
			$('#submit').attr('do','submit');
			$('#code').val("");
			$('.code').hide();
			$('.smscode').hide();
			$('.qqlogin').show();
			$('#login').show();
		}else if(d.saveOK ==3){
			$('#load').html('您输入的帐号或密码不正确，请重新输入密码！');
			$('#submit').attr('do','submit');
			$('#pwd').val('');
			$('.code').hide();
			$('.smscode').hide();
			$('.qqlogin').show();
			$('#login').show();
		}else if(d.saveOK ==10009){
			comm_data.sms.ticket = d.sms_ticket;
			comm_data.sms.issend = false;
			comm_data.cookie = d.cookie;
			$('#load').html(d.msg);
			$('#submit').attr('do','login');
			$('.qqlogin').hide();
			$('.smscode').show();
			$('#login').show();
		}else if(d.saveOK ==10010 || d.saveOK ==10005){
			$('#submit').attr('do','login');
			$('#load').html(d.msg);
		}else if(d.msg =='pwd不能为空'){
			$('#load').html('请输入密码！');
			$('#submit').attr('do','submit');
			$('.code').hide();
			$('.smscode').hide();
			$('.qqlogin').show();
			$('#login').show();
		}else{
			$('#load').html(d.msg);
			$('#submit').attr('do','submit');
		}
	});
	
}
function getvc(uin){
	$('#load').html('获取验证码，请稍等...');
	sess = comm_data.captcha.sess||'0';
	var getvcurl="login.php?do=getvc&r="+Math.random(1);
	var param = {uin: uin, sid: comm_data.sid, sig: comm_data.captcha.vc, sess: sess, websig: comm_data.captcha.websig};
	ajaxPost(getvcurl, param, function(d) {
		if(d.saveOK ==0){
			$('#load').html('请输入验证码');
			comm_data.captcha.vc = d.vc;
			comm_data.captcha.sess = d.sess;
			comm_data.captcha.cdata = d.cdata;
			comm_data.captcha.websig = d.websig;
			$('#codeimg').html('<img onclick="getvc(\''+uin+'\')" src="data:image/png;base64,'+image+'" title="点击刷新">');
			$('#submit').attr('do','code');
			$('#code').val('');
			$('.code').show();
		}else if(d.saveOK ==2){
			comm_data.captcha.vc = d.vc;
			comm_data.captcha.sess = d.sess;
			comm_data.captcha.cdata = d.cdata;
			comm_data.captcha.websig = d.websig;
			dovc(uin,d.ans);
		}else{
			alert(d.msg);
		}
	});

}
function dovc(uin,code){
	$('#load').html('验证验证码，请稍等...');
	var getvcurl="login.php?do=dovc&r="+Math.random(1);
	var param = {uin: uin, ans: code, sid: comm_data.sid, cap_cd: comm_data.cap_cd, sig: comm_data.captcha.vc, sess: comm_data.captcha.sess, websig: comm_data.captcha.websig, cdata: comm_data.captcha.cdata};
	ajaxPost(getvcurl, param, function(d) {
		if(d.rcode == 0){
			var pwd=$('#pwd').val();
			comm_data.vcode = d.randstr;
			comm_data.pt_verifysession = d.sig;
			login(uin,pwd);
		}else if(d.rcode == 50){
			$('#load').html('验证码错误，重新生成验证码，请稍等...');
			getvc(uin);
		}else if(d.rcode == 12){
			comm_data.captcha.sess = d.sess;
			$('#load').html('验证失败，请重试。');
		}else{
			comm_data.captcha.sess = d.sess;
			$('#load').html('验证失败，请重试或使用扫码登录。');
			//getvc(uin);
		}
	});

}
function checkvc(){
	var uin=trim($('#uin').val()),
		pwd=trim($('#pwd').val());
	if(uin==''||pwd=='') {
		$('#load').html('请输入密码！');
		$('.qqlogin').show();
		$('#login').show();
		return false;
	}
	$('#load').html('登录中，请稍候...');
	var getvcurl="login.php?do=checkvc&r="+Math.random(1);
	var param = {uin: uin};
	ajaxPost(getvcurl, param, function(d) {
		if(d.saveOK ==0){
			comm_data.cookie = d.cookie;
			comm_data.sid = d.sid;
			comm_data.vcode = d.vcode;
			comm_data.pt_verifysession = d.pt_verifysession;
			login(uin,pwd);
		}else if(d.saveOK ==1){
			comm_data.cookie = d.cookie;
			comm_data.sid = d.sid;
			comm_data.cap_cd = d.sig;
			//getvc(uin,d.sig,0,d.sid);return;
			var jumpurl = 'cap_frame.php?sid='+d.sid+'&aid=549000912&uin='+uin;
			captcha_frame = layer.open({
				type: 2,
				title: '滑块验证码',
				shade: 0.6,
				area: [$(window).width() > 450 ? '450px' : '100%', $(window).width() > 450 ? '450px' : '380px'],
				content: jumpurl,
				cancel: function(){
					$('#load').hide();
				}
			});
		}else{
			alert(d.msg);
			$('#load').html('');
		}
	});
}
window.onqqlogin = function(d){
	layer.close(captcha_frame);
	comm_data.vcode = d.randstr;
	comm_data.pt_verifysession = d.ticket;
	var uin=trim($('#uin').val()),
		pwd=trim($('#pwd').val());
	login(uin,pwd);
	$('#uin').attr("data-lock", "false");
}
$(document).ready(function(){
	$('#submit').click(function(){
		var self=$(this);
		var uin=trim($('#uin').val()),
			pwd=trim($('#pwd').val());
		if(uin==''||pwd=='') {
			alert("请确保每项不能为空！");
			return false;
		}
		$('#load').show();
		if(self.attr('do') == 'code'){
			var vcode=trim($('#code').val()),
				vc=$('#codeimg').attr('vc');
			dovc(uin,vcode,vc);
		}else if(self.attr('do') == 'login'){
			login(uin,pwd);
		}else{
			if (self.attr("data-lock") === "true") return;
			else self.attr("data-lock", "true");
			checkvc();
			self.attr("data-lock", "false");
		}
	});
});