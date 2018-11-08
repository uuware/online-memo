<?php 
//For IT703
//  20180920 Jihao@Invercargill

//system path
define('_AP_BASE_FILE', __FILE__);
//include base
include_once('inc/base.php');

//functions
$act = _getrequest('act');
if($act == 'login') {
	_setsession('islogin', null);
	_setsession('userid', null);
	_setsession('isadmin', null);
	_setsession('rightsbusiness', null);
	_setsession('rightsviewer', null);
	$name = _getpost('username');
	$pwd = _getpost('password');

	$db = _getDBO();
	$row = $db->selectObject('#__user', array(), array('username' => $name));
	if(!$row || $row->password != MD5($pwd)) {
		echo 'Username or password is wrong.';
		return;
	}
	if($row->rightsadmin != '1' && $row->rightsbusiness != '1' && $row->rightsviewer != '1') {
		echo 'this user does not have any rights.';
		return;
	}

	_setsession('islogin', '1');
	_setsession('userid', $row->userid);
	_getuser();

	//insert login stamp
	$object = array(
		'userid' => $row->userid,
		'loginstamp' => date('Y-m-d H:i:s'),
	);
	if (!$db->insertObject('#__userstamp', $object)) {
		$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
	}
	echo 'success';
	return;
}
else if($act == 'loginexit') {
	//logout
	_setsession('userid', null);
	_setsession('isadmin', null);
	_setsession('rightsbusiness', null);
	_setsession('rightsviewer', null);
}
else if($act == 'install') {
	include_once('inc/install.php');
	return;
}

//show main page
include_once('inc/body.php');
?>
