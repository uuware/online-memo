<div style="text-align:left;display: inline-block;">

<div style="text-align:center;"><h1>Label List</h1></div>

<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act');
$labelid = _getrequest('labelid');
$search = trim(_getpost('search'));

$msg = '';
if($act == 'add' || $act == 'edit' || $act == 'show') {
	include_once('inc/p-labeladd.php');
	if($act != '') {
		return;
	}
}
if($act == 'del') {
	if($labelid == '') {
		$msg = '<font color="red">No id exists.</font>';
	}
	else {
		//remove
		$sql = 'DELETE FROM #__label WHERE labelid = '.$db->Quote($labelid);
		$db->setQuery($sql);
		if(!$db->query()) {
			$msg .= "<font color=red>DB error:".$db->getErrorMsg().", SQL:$sql</font><br>";
		}
	}
}
?>

<div class="filter fheader">
<div class="filter-textp"></div>
<div class="filter-textp"><input name="search" id="search" type="text" value="<?php echo _showhtml($search); ?>" class="txtedit txtsearch"></div>
<div class="btnbase btnwhite btn2" onclick="dosubmit('search', 0)">Search</div>
<div class="btnbase btnwhite btn2" onclick="dosubmit('add', 0)" style="color:red;">Add label</div>
</div>

<script type="text/javascript">
var g_editflag = 0;
function dosubmit(act, id) {
	if(act == 'add') {
		popw_s('?m=l&act=add', 500, 320);
		return;
	}
	if(act == 'del') {
		if(!confirm('Do you really remove it?')) {
			return false;
		}
	}
	byId('act').value = act;
	byId('labelid').value = id;
	var frm = document.forms[0];
	frm.submit();
	return false;
}
</script>
<?php

echo $msg;

	$where = '';
	if(_getsession('isadmin') != '1') {
		$where = ' WHERE labelid='.$db->Quote($labelid).' ';
	}
	$fieldsreal = array('labelname', 'labelcolor', 'labelid');
	if($search != '') {
		$arr = preg_split('/ /', $search);
		foreach($arr as $item) {
			$item = trim($item);
			if($item != '') {
				if($where != '') {
					$where .= ' AND ';
				}
				else {
					$where .= ' WHERE ';
				}
				$whereone = '';
				foreach($fieldsreal as $itemid) {
					if($itemid != '') {
						if($whereone != '') {
							$whereone .= ' OR ';
						}
						$s = str_replace('_', '\_', $item);
						$whereone .= $itemid .' like '.$db->Quote('%'.$s.'%');
					}
				}
				$where .= ' ( '.$whereone.' ) ';
			}
		}
	}


	$sql = 'SELECT * FROM #__label '.$where.' ORDER BY labelname';
	$db->setQuery($sql);
	$cnt = 0;
	$lst = null;
	$param = array();
	$param['pageindex'] = 0 + _getrequest('pg_ind');
	$param['pageitems'] = _getini('pageitems', 30);
	$lst = $db->loadObjectListLimit($param);

	$cntreal = count($lst);


_pg_print($param);
echo '<table class="plist label" border="0" style="max-width:800px;">
<tr>
<th class="name">No.</th>
<th class="name">Label name</th>
<th class="name">Label color</th>
<th class="name">Edit/Remove</th>
</tr>
';

if($lst && count($lst) > 0) {
	$i = 0;
	foreach($lst as $row) {
		$i++;
		$uid = $row->labelid;
		echo '
<tr>
<td class="name center no">'.$row->labelid.'</td>
<td class="name username">'._showhtml($row->labelname).'</td>
<td class="name username" style="background:'._showhtml($row->labelcolor).';">'._showhtml($row->labelcolor).'</td>

<td class="btn" nowrap>
<span class="btnbase btnwhite btn1" onclick="popw_s(\'?m=l&act=edit&labelid='._showhtml($uid).'\', 500, 320);">Edit</span>
<span class="btnbase btnwhite btn1" onclick="dosubmit(\'del\', '._showhtml($uid).');">Remove</span>
</td>
';
	}
}

echo '</table>';
	_pg_print($param);
?>

<input type="hidden" name="labelid" id="labelid" value="">
</div>
