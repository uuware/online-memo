<div style="text-align:left;display: inline-block;">

<div style="text-align:center;"><h1>Memo List</h1></div>

<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

$db = _getDBO();
$user = _getuser();
$userid = $user->userid;

$m = _getrequest('m');
$m2 = _getrequest('m2');

$act = _getrequest('act');
$taskid = _getrequest('taskid');
$search = trim(_getpost('search'));
$sfrom = trim(_getpost('sfrom'));
$sto = trim(_getpost('sto'));
$cid = trim(_getpost('cid'));

$msg = '';
if($act == 'del') {
	if($taskid == '') {
		$msg = '<font color="red">No id exists.</font>';
	}
	else {
		//remove
		$sql = 'DELETE FROM #__task WHERE taskid = '.$db->Quote($taskid);
		$db->setQuery($sql);
		if(!$db->query()) {
			$msg .= "<font color=red>DB error:".$db->getErrorMsg().", SQL:$sql</font><br>";
		}
		else {
			$msg = '<font color="blue">Data is removed.</font>';
		}
	}
}

if($act != 'prn') {
	$csel = '<option value=""> - </option>';
	$sql = 'SELECT * FROM #__label ORDER BY labelname';
	$db->setQuery($sql);
	$ent = $db->loadObjectList();
	if($ent) {
		foreach ($ent as $row)
		{
			$sel = ($cid == $row->labelname) ? ' selected' : '';
			$clientt = $row->labelname.' ('.$row->labelcolor.')';
			$csel .= '<option value="'.$row->labelid.'"'.$sel.'>'.$clientt.'</option>';
		}
	}
?>

<div class="filter fheader">
<div class="filter-textp"></div>
<div class="filter-textp">
From: <input name="sfrom" id="sfrom" type="text" value="<?php echo _showhtml($sfrom); ?>" class="txtedit txtdate tcal">
To: <input name="sto" id="sto" type="text" value="<?php echo _showhtml($sto); ?>" class="txtedit txtdate tcal">
Label: <select name="cid" id="cid"><?php echo $csel; ?></select>
Key: <input name="search" id="search" type="text" value="<?php echo _showhtml($search); ?>" class="txtedit txtsearch" style="width:180px;"></div>
<br><div class="btnbase btnwhite btn2" onclick="dosubmit('search', 0)">Search</div>
<div class="btnbase btnwhite btn2" onclick="dosubmit('add', 0)" style="color:red;">Add Memo</div>
<div class="btnbase btnwhite btn2" onclick="dosubmit('prn', 0)">Print</div>
</div>

<script type="text/javascript">
var g_editflag = 0;
function dosubmit(act, id) {
	if(act == 'add') {
		popw_s('?m=t&act=add&ymd=', 500, 380);
		return;
	}
	if(act == 'del') {
		if(!confirm('Do you really remove it?')) {
			return false;
		}
	}
	byId('act').value = act;
	byId('taskid').value = id;
	var frm = document.forms[0];
	if(act == 'prn')
	{
		byId('m').value = 't';
		frm.target = '_blank';
	}
	frm.submit();

	byId('m').value = '';
	frm.target = '';
	byId('act').value = 'search';
	return false;
}
</script>
<?php
};

echo $msg;

	$where = ' WHERE t.userid='.$db->Quote($userid);
	if($cid != '') {
		$where .= ' AND t.labelarr='.$db->Quote($cid);
	}
	if($sfrom != '') {
		$sfrom2 = str_replace('-', '', $sfrom);
		$where .= ' AND t.duedate>'.$db->Quote($sfrom2);
	}
	if($sto != '') {
		$sto2 = str_replace('-', '', $sto);
		$where .= ' AND t.duedate<'.$db->Quote($sto2);
	}
	//$fieldsreal = array('t.todo', 't.duedate', 'c.title', 'c.lastname', 'c.firstname', 't.status');
	$fieldsreal = array('t.todo', 't.duedate', 't.labelarr', 't.status');
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

	//$sql = 'SELECT t.taskid, t.todo, t.duedate AS duedate, t.clientid, t.status, c.title, c.lastname, c.firstname FROM #__task t left join t_client c on t.clientid=c.clientid '.$where.' ORDER BY duedate';
	$sql = 'SELECT t.taskid, t.todo, t.duedate AS duedate, t.labelarr, t.status FROM #__task t '.$where.' ORDER BY duedate';
	$db->setQuery($sql);
	$cnt = 0;
	$lst = null;
	$param = array();
	$param['pageindex'] = 0 + _getrequest('pg_ind');
	$param['pageitems'] = _getini('pageitems', 30);

	if($act == 'prn') {
		$param['pageitems'] = 9999;
	}

	$lst = $db->loadObjectListLimit($param);

	$cntreal = count($lst);

if($act != 'prn') {
	_pg_print($param);
}
echo '<table class="plist task" border="0" style="max-width:800px;">
<tr>
<th class="name">No.</th>
<th class="todo">Memo</th>
<th class="due">Due date</th>
<th class="statue">Status</th>
<th class="labelarr">Label</th>
';
if($act != 'prn') {
	echo '<th class="name">Edit/Remove</th>';
}
echo '</tr>';

if($lst && count($lst) > 0) {

	$csel = '';
	$sql = 'SELECT * FROM #__label ORDER BY labelname';
	$db->setQuery($sql);
	$labelent = $db->loadObjectList('labelid');

	foreach($lst as $row) {
		$uid = $row->taskid;
		$todo = $row->todo;
		$status = $row->status;
		$status_s = '';
		/*
		$client_s = trim($row->title). ' ';
		if(trim($row->lastname) != '') {
			$client_s .= trim($row->lastname). ' ';
		}
		if(trim($row->lastname) != '') {
			$client_s .= trim($row->lastname). ' ';
		}
		*/
		if($status == '2') {
			$status_s = 'Pending';
		}
		else if($status == '3') {
			$status_s = 'Completed';
		}
		else {
			$status_s = 'Processing';
		}
		$duedate = $row->duedate;
		if(strlen($duedate) == 8) {
			$duedate = substr($duedate, 0, 4).'-'.substr($duedate, 4, 2).'-'.substr($duedate, 6, 2);
		}
		$cname = (isset($labelent[$row->labelarr]) ? $labelent[$row->labelarr]->labelname : '');
		echo '
<tr>
<td class="name center no">'.$uid.'</td>
<td class="name username" style="word-break: break-all;">'._showhtml($row->todo).'</td>
<td class="name center">'._showhtml($duedate).'</td>
<td class="name center">'._showhtml($status_s).'</td>
<td class="name center">'._showhtml($cname).'</td>
';

		if($act != 'prn') {
	echo '<td class="btn">
<span class="btnbase btnwhite btn1" onclick="popw_s(\'?m=t&act=edit&taskid='._showhtml($uid).'\', 500, 380);">Edit</span>
<span class="btnbase btnwhite btn1" onclick="dosubmit(\'del\', '._showhtml($uid).');">Remove</span>
</td>
';
		}
	}
}

echo '</table>';
	if($act != 'prn') {
		_pg_print($param);
	}
?>

<input type="hidden" name="taskid" id="taskid" value="">
<br>

<input type="hidden" name="act2" id="act2" value="">
</div>
