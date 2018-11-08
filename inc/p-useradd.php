<div style="text-align:left;display: inline-block;">
<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();
$user = _getuser();
//$act = _getrequest('act');
$act2 = _getrequest('act2');
$msg = '';
$userid = '';
$username = '';
$password = '';
$password2 = '';
$realname = '';
$rightsadmin = '';
$rightsbusiness = '';
$rightsviewer = '';
$status = '1';

$disabled = '';
$disabled2 = '';
$title = 'Add a new user';
$btntitle = 'Add';
if($act == 'edit' || $act == 'show') {
	$title = 'Edit a user';
	$btntitle = 'Update';
	$disabled2 = ' disabled="disabled"';
}
if($act2 == 'submit') {
	$userid = _getpost('userid');
	$username = _getpost('username');
	if($username == '') {
		$username = _getpost('username2');
	}
	$password = trim(_getpost('password'));
	$password2 = trim(_getpost('password2'));
	$realname = _getpost('realname');
	$rightsadmin = _getpost('rightsadmin');
	$rightsbusiness = _getpost('rightsbusiness');
	$rightsviewer = _getpost('rightsviewer');

	if($rightsadmin == '') {
		$rightsadmin = 0;
	}
	if($rightsbusiness == '') {
		$rightsbusiness = 0;
	}
	if($rightsviewer == '') {
		$rightsviewer = 0;
	}

	if($userid == '') {
		if($username == '') {
			$msg .= 'Please input username.<br>';
		}
		if($password == '') {
			$msg .= 'Please inpput password.<br>';
		}
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
		if($userid != '') {
			$sql .= ' AND userid <> '.$db->Quote($userid);
		}
		$db->setQuery($sql);
		$ct_one = $db->loadObject();
		if($ct_one) {
			$msg = '<font color="red">The same username existed already.</font>';
		}
	}
	if($msg == '') {
		$object = array(
						'realname' => $realname,
						);
		if(_getsession('isadmin') == '1') {
			//can only be muself!
			if($userid == $user->userid) {
				$rightsadmin = '1';
			}
			$object['rightsadmin'] = $rightsadmin;
			$object['rightsbusiness'] = $rightsbusiness;
			$object['rightsviewer'] = $rightsviewer;
		}
		if($userid != '') {
			//update
			$object['userid'] = $userid;
			if($password != '') {
				$object['password'] = MD5($password);
			}
			if (!$db->updateObject('#__user', $object, array('userid'))) {
				$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
			}
		}
		else {
			//insert
			$object['username'] = $username;
			$object['createddate'] = date('Y-m-d');;
			$object['password'] = MD5($password);
			if (!$db->insertObject('#__user', $object)) {
				$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
			}
		}
	}
	
	if($msg == '') {
		$msg = '<font color="blue">Data is updated.</font>';
echo '
<script type="text/javascript">
if(window.parent.popw_h) {
	window.parent.popw_h();
}
</script>
';
	}
	else {
		$msg = '<font color="red">'.$msg.'</font>';
	}
}

else if($act == 'edit' || $act == 'show') {
	if($act == 'show') {
		$title = 'User details';
		$disabled = ' disabled="disabled"';
	}
	$userid = _getrequest('userid');
	if($userid == '') {
		echo '<font color="red">No id exists.</font><br>';
		$act = '';
	}
	else {
		//check existing
		$db = _getDBO();
		$row = $db->selectObject('#__user', array(), array('userid' => $userid));
		if(!$row) {
			echo '<font color="red">Userid: '.$userid.' does not exist.</font><br>';
			$act = '';
		}
		else {
			$userid = $row->userid;
			$username = $row->username;
			//$password = $row->password;
			$realname = $row->realname;
			$rightsadmin = $row->rightsadmin;
			$rightsbusiness = $row->rightsbusiness;
			$rightsviewer = $row->rightsviewer;
		}
	}
}

if($act != '') {
?>
<input type="hidden" name="userid" id="userid" value="<?php echo _showhtml($userid); ?>">
<input type="hidden" name="username2" id="username2" value="<?php echo _showhtml($username); ?>">
<div class="pc-edit">

<div class="t-line">
<div class="t-title"><?php echo $title; ?></div>
</div>

<div class="t-line">
<span class="t-name">Username</span>
<span><input type="text"<?php echo $disabled; ?><?php echo $disabled2; ?> class="txtedit txtmain" name="username" id="username" maxlength="20" value="<?php echo _showhtml($username); ?>"></span>
</div>

<div class="t-line">
<span class="t-name">Password</span>
<span><input type="password"<?php echo $disabled; ?> class="txtedit txtmain" name="password" id="password" maxlength="20" value="<?php echo _showhtml($password); ?>">
<?php
if($act == 'edit') {
	echo '<br>(No updating when empty)';
}
?>
</span>
</div>

<div class="t-line">
<span class="t-name">Confirm</span>
<span><input type="password"<?php echo $disabled; ?> class="txtedit txtmain" name="password2" id="password2" maxlength="20" value="<?php echo _showhtml($password2); ?>"></span>
</div>

<?php
if(_getsession('isadmin') == '1') {
//cannot change rights!
?>
<div class="t-line">
<span class="t-name">Role</span>
<span style="width:auto;"><label for=rightsviewer>Common</label><input type="checkbox"<?php echo $disabled; ?> value="1" class="t-input" style="width:auto;" name="rightsviewer" id="rightsviewer" <?php echo _iif($rightsviewer == '1', 'checked'); ?>></span>
<span style="width:auto;"><label for=rightsadmin>Administrator</label><input type="checkbox"<?php echo $disabled; ?> value="1" class="t-input" style="width:auto;" name="rightsadmin" id="rightsadmin" <?php echo _iif($rightsadmin == '1', 'checked'); ?>></span>
</div>
<?php
}
?>

<div class="t-line">
<span class="t-name">Real name</span>
<span><input type="text"<?php echo $disabled; ?> class="txtedit txtmain" name="realname" id="realname" maxlength="20" value="<?php echo _showhtml($realname); ?>"></span>
</div>

<div class="filter fheader">
<?php
if($act != 'show') {
	//<div class="filter-btn" onclick="window.history.go(-1);">Back</div>
?>
<div class="btnbase btnwhite btn2" onclick="dosubmit('submit');"><?php echo $btntitle; ?></div>
<div class="btnbase btnwhite btn2" onclick="document.forms[0].reset();">Reset</div>
<?php } ?>
</div>

</div>


<?php echo $msg; ?>
<script type="text/javascript">
	function dosubmit(act) {
		byId('act2').value = act;
		var frm = document.forms[0];
		frm.submit();
		return false;
	}
</script>
<?php
}
?>
<input type="hidden" name="act2" id="act2" value="">
</div>
