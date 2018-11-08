<div style="text-align:left;display: inline-block;">

<?php
if(_getsession('isadmin', '0') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act');
$sid = _getrequest('sid');
$search = trim(_getpost('search'));

$msg = '';
if($act == 'del') {
	if($sid == '') {
		$msg = '<font color="red">No id exists.</font>';
	}
	else {
		//remove
		$sql = 'DELETE FROM #__userstamp WHERE stampid = '.$db->Quote($sid);
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
<div class="btnbase btnwhite btn3" onclick="dosubmit('search', 0)">Search</div>
</div>

<script type="text/javascript">
var g_editflag = 0;
function dosubmit(act, id) {
	if(act == 'del') {
		if(!confirm('Do you really remove it?')) {
			return false;
		}
	}
	byId('act').value = act;
	byId('sid').value = id;
	var frm = document.forms[0];
	frm.submit();
	return false;
}
</script>
<?php

echo $msg;

	$where = 'WHERE s.userid=u.userid ';
	$fieldsreal = array('u.username', 'u.realname', 's.loginstamp');
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


	$sql = 'SELECT s.stampid, s.loginstamp, s.userid, u.username AS username, u.realname FROM #__userstamp s, #__user u '.$where.' ORDER BY username, loginstamp';
	$db->setQuery($sql);
	$cnt = 0;
	$lst = null;
	$param = array();
	$param['pageindex'] = 0 + _getrequest('pg_ind');
	$param['pageitems'] = _getini('pageitems', 30);
	$lst = $db->loadObjectListLimit($param);

	$cntreal = count($lst);


_pg_print($param);
echo '<table class="plist stamp" border="0" style="max-width:800px;">
<tr>
<th class="name">Id</th>
<th class="name">Username</th>
<th class="name">Real name</th>
<th class="name">Login Stamp</th>
<th class="name">Remove</th>
</tr>
';

if($lst && count($lst) > 0) {
	$i = 0;
	foreach($lst as $row) {
		$i++;
		$id = $row->stampid;
		echo '
<tr>
<td class="name center no">'.$row->stampid.'</td>
<td class="name username">'._showhtml($row->username).'</td>
<td class="name realname">'._showhtml($row->realname).'</td>
<td class="name stamp">'._showhtml($row->loginstamp).'</td>

<td class="btn">
<span class="btnbase btnwhite btn1" onclick="dosubmit(\'del\', '._showhtml($id).');">Remove</span>
</td>
';
	}
}

echo '</table>';
	_pg_print($param);
?>

<input type="hidden" name="sid" id="sid" value="">
</div>
