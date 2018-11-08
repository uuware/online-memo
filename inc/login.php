
<center>
<br>
<br>
<br>
<br>
<div class="login-pc">
<div class="login-title"><?php echo $g_title; ?></div>
<img src="images/memo-logo2.png" height=150px />
<br>
<div class="login-error" id="login-error">&nbsp;</div>
<br>

<div class="login-pc2">
<span class="login-title2">Username: </span><input type="text" maxlength="20" id="username" class="login-input" _tips=1 value="input username" /><br>
<span class="login-title2">Password: </span><input type="password" maxlength="20" id="password" class="login-input" value="" /><br>
<span class="login-title2">&nbsp;</span><span class="login-right"><input type="checkbox" id="saveme" /><label for="saveme">Remember password</label></span><br>
</div>


<br><span class="btnbase btnlogin btnwhite" id="tplogin" style="width:200px;" onclick="doLogin();">Login</span>
<span class="btnbase btnlogin btnwhite" id="tplogin" onclick="doReg();">Register</span>

<div class="login-cr login-maxw">
<br>
Jihao, Zhu(2017010338)</div>
<div class="login-cr">IT817 Assignment 3</div>
</div>
</center>

<script type="text/javascript">
<!--
	var g_login = false;
	function doLogin() {
		var name = byId('username').value;
		var pwd = byId('password').value;
		if(name == '') {
	    	byId('login-error').innerHTML = 'Please input username.';
		}
		else if(pwd == '') {
	    	byId('login-error').innerHTML = 'Please input password';
		}
		else {
	    	byId('login-error').innerHTML = '&nbsp;';
			postAjax(login_callback, '<?php echo _AP_URL_INDEX; ?>?act=login', '&username='+name+'&password='+pwd);
		}
		if(byId('saveme').checked) {
			setcookie('username', name, 30, '/');
			setcookie('userpwd', pwd, 30, '/');
		}
		else {
			clearcookie('username', '/');
			clearcookie('userpwd', '/');
		}
		return false;
	}
	function login_callback(txt, obj) {
		if(txt == 'success') {
	        location.href = '<?php echo _AP_URL_INDEX; ?>';
		}
		else {
	    	byId('login-error').innerHTML = txt;
		}
		g_login = false;
	}

	function doReg() {
        location.href = '<?php echo _AP_URL_INDEX; ?>?m=r';
	}

	window.onload = function()
	{
		var allInput = document.getElementsByTagName("input");
		for(var i=0; i<allInput.length; i++){
			if(allInput[i].getAttribute('_tips') == 1 && allInput[i].value != '') {
				allInput[i]._tips = 1;
				allInput[i]._value = allInput[i].value;
			}
			allInput[i].onfocus = function() {
				if(this._tips == 1) {
					this._tips = 0;
					this.style.color = '#000';
					this.value = '';
				}
			};
			allInput[i].onblur = function() {
				if(this.value=='' && this._value) {
					this.value = this._value;
					this.style.color = '#ccc';
					this._tips = 1;
				}
			};
		}

		var name = readcookie('username');
		if(name && name != '') {
			byId('username').value = name;
			byId('saveme').checked = true;
			byId('username')._tips = 0;
			byId('username').style.color = '#000';
		}
		var pwd = readcookie('userpwd');
		if(pwd && pwd != '') {
			byId('password').value = pwd;
			byId('saveme').checked = true;
			byId('password')._tips = 0;
			byId('password').style.color = '#000';
		}
	}
//-->
</script>
