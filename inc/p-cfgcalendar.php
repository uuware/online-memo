
<div style="text-align:center;"><h1>Calendar Config</h1></div>

<?php
if(_getsession('isadmin', '0') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$weekstart = _getini('weekstart', '0', true);
$corCalSun = _getini('corCalSun', '#ff0000', true);
$corCalSat = _getini('corCalSat', '#0000ff', true);

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act2');
$msg = '';
if($act == 'save') {
	$weekstart = _getpost('weekstart');
	$corCalSun = _getpost('corCalSun');
	$corCalSat = _getpost('corCalSat');
	if(!_typecheck($weekstart, 'D')) {
		$msg .= '<font color="red">Week start can only be numberical.</font>';
	}
	if($weekstart < 0 || $weekstart > 6) {
		$msg .= '<font color="red">Week start needs between 0 and 6.</font>';
	}

	if($msg == '') {
		$newvalues = array(
			'weekstart' => $weekstart,
			'corCalSun' => $corCalSun,
			'corCalSat' => $corCalSat,
		);
		if(!_saveIni($newvalues)) {
			$msg .= '<font color="red">Failed to save data.</font>';
		}
	}
}
?>
<?php echo $msg; ?>
<table class="plist cfg" border="0">

<tr>
<td class="title">
Week start</td>
</tr>
<tr>
<td class="name">
Week start from: <input type="text" class="txtedit" name="weekstart" id="weekstart" value="<?php echo _showhtml($weekstart); ?>" style="width:40px;">(from 0:Sunday to 6:Saturday)
</td>
</tr>

<tr>
<td class="title"><br></td>
</tr>


<tr>
<td class="title">
Color for weekday</td>
</tr>
<tr>
<td class="name">
Sunday Color: <input type="text" class="txtedit" name="corCalSun" id="corCalSun" value="<?php echo _showhtml($corCalSun); ?>" style="width:80px;background-color:<?php echo _showhtml($corCalSun); ?>;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('corCalSun')">
</td>
</tr>
<tr>
<td class="name">
Saturday: <input type="text" class="txtedit" name="corCalSat" id="corCalSat" value="<?php echo _showhtml($corCalSat); ?>" style="width:80px;background-color:<?php echo _showhtml($corCalSat); ?>;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('corCalSat')">
</td>
</tr>


<tr>
<td class="title" colspan="2">
<span class="btnbase btnwhite btn2" onclick="dosubmit('save');">Save</span>
</td>
</tr>


</table>
<script type="text/javascript" src="ext/uuhedt/uuhedt.js"></script>
<input type="hidden" name="act2" id="act2" value="">
<script type="text/javascript">
function pickColor(id){
  var edt = new UUHEdt('xxx', {_noeditor:true});
  UUHEdtColor.pickColor(edt, id, function(c){
    byId(id).style.backgroundColor = c;
  });
}
var g_editflag = 0;
function dosubmit(act) {
	byId('act2').value = act;
	var frm = document.forms[0];
	frm.submit();
	return false;
}
</script>
