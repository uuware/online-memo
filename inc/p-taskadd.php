<div style="text-align:left;display: inline-block;">
<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();
$user = _getuser();
$userid = $user->userid;

//$act = _getrequest('act');
$act2 = _getrequest('act2');
$msg = '';
$todo = '';
$duedate = '';
$labelarr = '';
$status = '1';
if($act == 'add') {
	$duedate2 = _getrequest('ymd');
	if(strlen($duedate2) == 8) {
		$duedate = substr($duedate2, 0, 4).'-'.substr($duedate2, 4, 2).'-'.substr($duedate2, 6, 2);
	}
}

$disabled = '';
$title = 'Add a new memo';
$btntitle = 'Add';
if($act == 'edit' || $act == 'show') {
	$title = 'Edit a memo';
	$btntitle = 'Update';
}
if($act2 == 'submit') {
	$taskid = _getpost('taskid');
	$todo = _getpost('todo');
	$duedate = trim(_getpost('duedate'));
	$labelarr = trim(_getpost('labelarr'));
	$status = _getpost('status');

	if($status == '') {
		$status = '1';
	}
	if($todo == '') {
		$msg .= 'Please input memo.<br>';
	}
	if($duedate == '') {
		$msg .= 'Please inpput duedate.<br>';
	}

	if($msg == '') {
		$duedate2 = str_replace('-', '', $duedate);
		$object = array('todo' => $todo,
						'duedate' => $duedate2,
						'labelarr' => $labelarr,
						'userid' => $userid,
						'status' => $status,
						);
		if($taskid != '') {
			//update
			$object['taskid'] = $taskid;
			if (!$db->updateObject('#__task', $object, array('taskid'))) {
				$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
			}
		}
		else {
			//insert
			$object['createddate'] = date('Y-m-d');
			if (!$db->insertObject('#__task', $object)) {
				$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
			}
			else {
				$todo = '';
				$duedate = '';
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
		$title = 'Memo details';
		$disabled = ' disabled="disabled"';
	}
	$taskid = _getrequest('taskid');
	if($taskid == '') {
		echo '<font color="red">No id exists.</font><br>';
		$act = '';
	}
	else {
		//check existing
		$db = _getDBO();
		$row = $db->selectObject('#__task', array(), array('taskid' => $taskid));
		if(!$row) {
			echo '<font color="red">Taskid: '.$taskid.' does not exist.</font><br>';
			$act = '';
		}
		else {
			$taskid = $row->taskid;
			$todo = $row->todo;
			$duedate2 = $row->duedate;
			if(strlen($duedate2) == 8) {
				$duedate = substr($duedate2, 0, 4).'-'.substr($duedate2, 4, 2).'-'.substr($duedate2, 6, 2);
			}
			$labelarr = $row->labelarr;
			//$labelarr
			/*if($clientid != '' && $clientid != '0') {
				$row = $db->selectObject('#__client', array(), array('clientid' => $clientid));
				if($row) {
					$client = $row->title.' '.$row->lastname.' '.$row->firstname;
				}
			}
			*/
			$status = $row->status;
		}
	}
}

if($act != '') {
?>
<input type="hidden" name="taskid" id="taskid" value="<?php echo _showhtml($taskid); ?>">
<div class="pc-edit">

<div class="t-line">
<div class="t-title"><?php echo $title; ?></div>
</div>

<div class="t-line">
<span class="t-name">Memo Title</span>
<span><input type="text" class="txtedit txtmain" name="username" id="username" maxlength="20" value="<?php echo _showhtml($todo); ?>"></span>
</div>

<div class="t-line">
<span class="t-name">Memo</span>
<span><textarea type="text"<?php echo $disabled; ?> class="txtedit txtmain" name="todo" id="todo" style="height:110px;">
<?php echo _showhtml($todo); ?></textarea>
</span>
</div>

<div class="t-line">
<span class="t-name">Due Date</span>
<span><input type="text"<?php echo $disabled; ?> class="txtedit txtdate tcal" name="duedate" id="duedate" maxlength="20" value="<?php echo _showhtml($duedate); ?>">
</span>
</div>

<div class="t-line">
<span class="t-name">Label</span>
<?php
/*
<span><input type="text"<?php echo $disabled; ?> class="txtedit txtmain" name="labelarr" id="labelarr" maxlength="50" value="<?php echo _showhtml($labelarr); ?>">
*/
	$csel = '';
	$sql = 'SELECT * FROM #__label ORDER BY labelname';
	$db->setQuery($sql);
	$ent = $db->loadObjectList();
	if($ent) {
		foreach ($ent as $row)
		{
			$sel = ($labelarr == $row->labelid) ? ' selected' : '';
			$clientt = $row->labelname.' ('.$row->labelcolor.')';
			$csel .= '<option value="'.$row->labelid.'"'.$sel.'>'.$clientt.'</option>';
		}
	}

	echo '<select '.$disabled.'name="labelarr" id="labelarr">'.$csel.'</select>';
?>
</div>

<div class="t-line">
<span class="t-name">Status</span>
<span style="width:auto;"><input name="status" id="status1"<?php echo _iif($status == '1', 'checked'); ?> type="radio" value="1"><label for="status1">Processing</label></span>
<span style="width:auto;"><input name="status" id="status2"<?php echo _iif($status == '2', 'checked'); ?> type="radio" value="2"><label for="status2">Pending</label></span>
<span style="width:auto;"><input name="status" id="status3"<?php echo _iif($status == '3', 'checked'); ?> type="radio" value="3"><label for="status3">Completed</label></span>
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
