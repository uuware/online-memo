
<div style="text-align:center;"><h1>Base Config</h1></div>

<?php
if(_getsession('isadmin', '0') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$backheader = _getini('backheader', '#0000ff', true);
$backmenu = _getini('backmenu', '#dcdddd', true);
$backbody = _getini('backbody', '#ffffff', true);
$pageitems = _getini('pageitems', '30', true);

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act2');
$msg = '';
if($act == 'save') {
	$pageitems = _getpost('pageitems');
	$backheader = _getpost('backheader');
	$backmenu = _getpost('backmenu');
	$backbody = _getpost('backbody');
	if(!_typecheck($pageitems, 'D')) {
		$msg .= '<font color="red">Records count can only be numberical.</font>';
	}
	if($pageitems < 0) {
		$msg .= '<font color="red">Records count needs more than 0.</font>';
	}

	if($msg == '') {
		$newvalues = array(
			'backheader' => $backheader,
			'backmenu' => $backmenu,
			'backbody' => $backbody,
			'pageitems' => $pageitems,
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
Maximal records show in one page</td>
</tr>
<tr>
<td class="name">
Max records: <input type="text" class="txtedit" name="pageitems" id="pageitems" value="<?php echo _showhtml($pageitems); ?>" style="width:40px;">
</td>
</tr>

<tr>
<td class="title"><br></td>
</tr>


<tr>
<td class="title">
Background color</td>
</tr>
<tr>
<td class="name">
of header: <input type="text" class="txtedit" name="backheader" id="backheader" value="<?php echo _showhtml($backheader); ?>" style="width:80px;background-color:<?php echo _showhtml($backheader); ?>;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('backheader')">
</td>
</tr>
<tr>
<td class="name">
of menu: <input type="text" class="txtedit" name="backmenu" id="backmenu" value="<?php echo _showhtml($backmenu); ?>" style="width:80px;background-color:<?php echo _showhtml($backmenu); ?>;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('backmenu')">
</td>
</tr>
<tr>
<td class="name">
of body: <input type="text" class="txtedit" name="backbody" id="backbody" value="<?php echo _showhtml($backbody); ?>" style="width:80px;background-color:<?php echo _showhtml($backbody); ?>;">
<input type="button" value="..." class="btnbase btnwhite btn2" style="padding:4px;" onclick="pickColor('backbody')">
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
