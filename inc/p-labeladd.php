<div style="text-align:left;display: inline-block;">
<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();
//$act = _getrequest('act');
$act2 = _getrequest('act2');
$msg = '';
$labelid = '';
$labelname = '';
$labelcolor = '';

$disabled = '';
$disabled2 = '';
$title = 'Add a new label';
$btntitle = 'Add';
if($act == 'edit' || $act == 'show') {
	$title = 'Edit a label';
	$btntitle = 'Update';
	$disabled2 = ' disabled="disabled"';
}
if($act2 == 'submit') {
	$labelid = _getpost('labelid');
	$labelname = _getpost('labelname');
	$labelcolor = _getpost('labelcolor');

	if($labelid == '') {
		if($labelname == '') {
			$msg .= 'Please input label name.<br>';
		}
		if($labelcolor == '') {
			$msg .= 'Please inpput label color.<br>';
		}
	}

	if($msg == '') {
		//check existing
		$sql = 'SELECT * FROM #__label WHERE labelname = '.$db->Quote($labelname);
		if($labelid != '') {
			$sql .= ' AND labelid <> '.$db->Quote($labelid);
		}
		$db->setQuery($sql);
		$ct_one = $db->loadObject();
		if($ct_one) {
			$msg = '<font color="red">The same label name existed already.</font>';
		}
	}
	if($msg == '') {
		$object = array(
			'labelname' => $labelname,
			'labelcolor' => $labelcolor,
		);
		if($labelid != '') {
			//update
			$object['labelid'] = $labelid;
			if (!$db->updateObject('#__label', $object, array('labelid'))) {
				$msg = "<font color=red>DB error:".$db->getErrorMsg()."</font><br>";
			}
		}
		else {
			//insert
			$object['createddate'] = date('Y-m-d');;
			if (!$db->insertObject('#__label', $object)) {
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
		$title = 'label details';
		$disabled = ' disabled="disabled"';
	}
	$labelid = _getrequest('labelid');
	if($labelid == '') {
		echo '<font color="red">No id exists.</font><br>';
		$act = '';
	}
	else {
		//check existing
		$db = _getDBO();
		$row = $db->selectObject('#__label', array(), array('labelid' => $labelid));
		if(!$row) {
			echo '<font color="red">label id: '.$labelid.' does not exist.</font><br>';
			$act = '';
		}
		else {
			$labelid = $row->labelid;
			$labelname = $row->labelname;
			$labelcolor = $row->labelcolor;
		}
	}
}

if($act != '') {
?>
<input type="hidden" name="labelid" id="labelid" value="<?php echo _showhtml($labelid); ?>">
<div class="pc-edit">

<div class="t-line">
<div class="t-title"><?php echo $title; ?></div>
</div>

<div class="t-line">
<span class="t-name">Label name</span>
<span><input type="text"<?php echo $disabled; ?> class="txtedit txtmain" name="labelname" id="labelname" maxlength="20" value="<?php echo _showhtml($labelname); ?>"></span>
</div>

<div class="t-line">
<span class="t-name">Label color</span>
<span><input type="text"<?php echo $disabled; ?> class="txtedit txtmain" name="labelcolor" id="labelcolor" maxlength="7" value="<?php echo _showhtml($labelcolor); ?>" style="width:80px;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('labelcolor')">
</span>
</span>
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
<script type="text/javascript" src="ext/uuhedt/uuhedt.js"></script>
<script type="text/javascript">
function pickColor(id){
  var edt = new UUHEdt('xxx', {_noeditor:true});
  UUHEdtColor.pickColor(edt, id, function(c){
    byId(id).style.backgroundColor = c;
  });
}
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
