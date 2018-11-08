<div style="text-align:left;display: inline-block;">

<div style="text-align:center;"><h1>User List</h1></div>

<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();
$user = _getuser();
$userid_ad = $user->userid;

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act');
$userid = _getrequest('userid');
$search = trim(_getpost('search'));

$msg = '';
if($act == 'add' || $act == 'edit' || $act == 'show') {
	include_once('inc/p-useradd.php');
	if($act != '') {
		return;
	}
}
if($act == 'del') {
	if($userid == '') {
		$msg = '<font color="red">No id exists.</font>';
	}
	else if($userid == $userid_ad) {
		$msg = '<font color="red">You can not remove yourself.</font>';
	}
	else {
		//remove
		$sql = 'DELETE FROM #__user WHERE userid = '.$db->Quote($userid);
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
<div class="btnbase btnwhite btn2" onclick="dosubmit('add', 0)" style="color:red;">Add User</div>
</div>

<script type="text/javascript">
var g_editflag = 0;
function dosubmit(act, id) {
	if(act == 'add') {
		popw_s('?m=u&act=add', 500, 300);
		return;
	}
	if(act == 'del') {
		if(!confirm('Do you really remove it?')) {
			return false;
		}
	}
	byId('act').value = act;
	byId('userid').value = id;
	var frm = document.forms[0];
	frm.submit();
	return false;
}
</script>
<?php

echo $msg;

	$where = '';
	if(_getsession('isadmin') != '1') {
		$where = ' WHERE userid='.$db->Quote($user->userid).' ';
	}
	$fieldsreal = array('username', 'realname', 'userid');
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


	$sql = 'SELECT * FROM #__user '.$where.' ORDER BY username';
	$db->setQuery($sql);
	$cnt = 0;
	$lst = null;
	$param = array();
	$param['pageindex'] = 0 + _getrequest('pg_ind');
	$param['pageitems'] = _getini('pageitems', 30);
	$lst = $db->loadObjectListLimit($param);

	$cntreal = count($lst);


_pg_print($param);
echo '<table class="plist user" border="0" style="max-width:800px;">
<tr>
<th class="name">No.</th>
<th class="name">Username</th>
<th class="name">Real name</th>
<th class="name">Common</th>
<th class="name">Administrator</th>
<th class="name">Edit/Remove</th>
</tr>
';

if($lst && count($lst) > 0) {
	$i = 0;
	foreach($lst as $row) {
		$i++;
		$uid = $row->userid;
		$chk1_0 = $row->rightsadmin;
		$chk2_0 = $row->rightsbusiness;
		$chk3_0 = $row->rightsviewer;
		echo '
<tr>
<td class="name center no">'.$i.'</td>
<td class="name username">'._showhtml($row->username).'</td>
<td class="name realname">'._showhtml($row->realname).'</td>
<td class="name center"><input type="checkbox"'._iif($row->rightsviewer==1, ' checked').' disabled="disabled"></td>
<td class="name center"><input type="checkbox"'._iif($row->rightsadmin==1, ' checked').' disabled="disabled"></td>

<td class="btn" nowrap>
<span class="btnbase btnwhite btn1" onclick="popw_s(\'?m=u&act=edit&userid='._showhtml($uid).'\', 500, 300);">Edit</span>
<span class="btnbase btnwhite btn1" onclick="dosubmit(\'del\', '._showhtml($uid).');">Remove</span>
</td>
';
	}
}

echo '</table>';
	_pg_print($param);
?>

<input type="hidden" name="userid" id="userid" value="">
</div>
