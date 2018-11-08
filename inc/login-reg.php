
<center>
<br>
<br>
<br>
<br>
<div class="login-title"><?php echo $g_title; ?></div>
<br>

<?php

$db = _getDBO();
//$act = _getrequest('act');
$act2 = _getrequest('act2');
$msg = '';
$username = '';
$password = '';
$password2 = '';
$realname = '';

if($act2 == 'submit') {
	$username = _getpost('username');
	$password = trim(_getpost('password'));
	$password2 = trim(_getpost('password2'));
	$realname = _getpost('realname');

	if($username == '') {
		$msg .= 'Please input username.<br>';
	}
	if($password == '') {
		$msg .= 'Please inpput password.<br>';
	}
	if($password != '' && (strlen($password) < 6 || strlen($password) > 20)) {
		$msg .= 'The length of password must between 6 and 20.<br>';
	}
	if($password != $password2) {
		$msg .= 'The confirm password should be same.<br>';
	}

	if($msg == '') {
		//check existing
		$sql = 'SELECT * FROM #__user WHERE username = '.$db->Quote($username);
		$db->setQuery($sql);
		$ct_one = $db->loadObject();
		if($ct_one) {
			$msg = '<font color="red">The same username existed already.</font>';
		}
	}
	if($msg == '') {
		$object = array('username' => $username,
						'realname' => $realname,
						'rightsadmin' => '0',
						'rightsbusiness' => '0',
						'rightsviewer' => '1',
						);
		//insert
		$object['createddate'] = date('Y-m-d');;
		$object['password'] = MD5($password);
		if (!$db->insertObject('#__user', $object)) {
			$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
		}
	}
	
	if($msg == '') {
		$msg = '<font color="blue">Added successfully.</font>';
	}
	else {
		$msg = '<font color="red">'.$msg.'</font>';
	}
}

?>
<div class="login-error" id="login-error"><?php echo $msg; ?>&nbsp;</div>

<div class="pc-edit">

<div class="t-line">
<div class="t-title">Register a user</div>
</div>

<div class="t-line">
<span class="t-name">Username</span>
<span><input type="text" class="login-input" name="username" id="username" maxlength="20" value="<?php echo _showhtml($username); ?>"></span>
</div>

<div class="t-line">
<span class="t-name">Password</span>
<span><input type="password" class="login-input" name="password" id="password" maxlength="20" value="<?php echo _showhtml($password); ?>">
</span>
</div>

<div class="t-line">
<span class="t-name">Confirm</span>
<span><input type="password" class="login-input" name="password2" id="password2" maxlength="20" value="<?php echo _showhtml($password2); ?>"></span>
</div>

<div class="t-line">
<span class="t-name">Real name</span>
<span><input type="text" class="login-input" name="realname" id="realname" maxlength="100" value="<?php echo _showhtml($realname); ?>"></span>
</div>

<div class="filter fheader">
<br><span class="btnbase btnlogin btnwhite" id="tplogin" style="width:200px;" onclick="dosubmit('submit');">Register</span>
<span class="btnbase btnlogin btnwhite" id="tplogin" onclick="doLogin();">Login</span>
</div>

</div>


<script type="text/javascript">
	function dosubmit(act) {
		byId('act2').value = act;
		var frm = document.forms[0];
		frm.submit();
		return false;
	}
	function doLogin() {
        location.href = '<?php echo _AP_URL_INDEX; ?>';
	}
</script>
<input type="hidden" name="act2" id="act2" value="">

</center>
