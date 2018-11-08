<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("You don not have access rights!");
}

//http://www.timeanddate.com/calendar/custommenu.html

$db = _getDBO();
$act = _getrequest('act');
$msg = '';
if($act == 'del') {
	$taskid = _getrequest('taskid');
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
	echo $msg;
}


$title = 'My memo';
$p_yy = _getrequest('yy');
$mline = 0 + _getrequest('ml');
$p_mm = _getrequest('mm');
$p_prn = _getrequest('prn');
if($p_yy == '') {
	$p_yy = date('Y');
}
if($p_mm == '') {
	$p_mm = date('m');
}
$p_mm = 0 + $p_mm;
if($p_mm < 1) {
	$p_mm = 12;
	$p_yy--;
}
if($p_mm > 12) {
	$p_mm = 1;
	$p_yy++;
}
if($mline < 1) {
	$mline = 1;
}
$today = strtotime(date('Y-m-d'));

$weekname = 'Su,Mo,Tu,We,Th,Fr,Sa,';
$arrweekname = preg_split('/,/', $weekname.$weekname);
$baseurl = _AP_URL_INDEX . '?';
$weekfrom = 0 + _getini('weekStart', '0');
$weektitle = '';
for($j=$weekfrom; $j<$weekfrom+7; $j++) {
	$sty = '';
	if($j == 0 || $j == 7) {
		$sty = ' style="color:'._getini('corCalSun', '#ff0000').';"';
	}
	else if($j == 6 || $j == 13) {
		$sty = ' style="color:'._getini('corCalSat', '#0000ff').';"';
	}
	$weektitle .= '<td'.$sty.'>'.$arrweekname[$j].'</td>';
}

$td_h = '40px;';
if($mline <= 1) {
	$mline = 1;
	$td_h = '90px;';
}
else if($mline != 3 && $mline != 4 && $mline != 6) {
	$mline = 3;
}
echo '<style>
.xcal tr{height:'.$td_h.'; font-size:20pt; }
.xcal td{width:14%;}
.tdadd{position:relative;}
.ieditp{width:100px;text-overflow:ellipsis;white-space:nowrap;}
.iedit{cursor:pointer;}
</style>';
echo '<table align="center" border="0" cellpadding="4" cellspacing="0" width="100%">';
echo '<tr><td colspan="'.($mline*2).'" align="center"><div><h1>'.$title.'['.$p_yy.']';
if($p_prn != '1') {
	if($mline <= 1) {
		echo '<a href="javascript:void(0)" onclick="selchg(this, false, '.($p_mm-1).');"><img align="bottom" src="'._AP_URL_IMG.'icon_left4.gif" title="'.($p_mm-1).'" border="0" /></a>';
		echo '<a href="javascript:void(0)" onclick="selchg(this, false, '.($p_mm+1).');"><img align="bottom" src="'._AP_URL_IMG.'icon_right4.gif" title="'.($p_mm+1).'" border="0" /></a>';
	}
	else {
		echo '<a href="javascript:void(0)" onclick="selchg(this, '.($p_yy-1).', false);"><img align="bottom" src="'._AP_URL_IMG.'icon_left4.gif" title="'.($p_yy-1).'" border="0" /></a>';
		echo '<a href="javascript:void(0)" onclick="selchg(this, '.($p_yy+1).', false);"><img align="bottom" src="'._AP_URL_IMG.'icon_right4.gif" title="'.($p_yy+1).'" border="0" /></a>';
	}
	echo '<select id="selml" onchange="selchg(this, false, false);" class="txtedit" style="FONT-SIZE:8pt;">';
	echo '<option value=""></option><option value="1">one month</option><option value="3">3/Line</option><option value="4">4/Line</option>';
	echo '</select>';
	echo '<span><a href="javascript:void(0)" title="Print" onclick="window.open(\'?'.'m='._getrequest('m').'&m2='._getrequest('m2')
		.'&yy='.$p_yy.'&ml='.$mline.'&prn=1\',\'win2\',\'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,fullscreen=yes,directories=no,location=no\'); return false;" rel="nofollow">
<img border=0 src="'._AP_URL_IMG.'icon_print.gif" alt="Print" /></a></span>';
}
echo '</h1></div></td></tr>'."\r\n";
$monthname = 'January,February,March,April,May,June,July,August,September,October,November,December,';
$arrmonthname = preg_split('/,/', $monthname);

$sfrom = '';
$sto = '';
if($mline <= 1) {
	$sfrom = $p_yy.substr('0'.$p_mm, -2).'00';
	$sto = $p_yy.substr('0'.$p_mm, -2).'99';
}
else {
	$sfrom = $p_yy.'0000';
	$sto = $p_yy.'9999';
}

$monthdata = array();
$monthdata2 = array();

$user = _getuser();
$userid = $user->userid;
//$sql = "SELECT t.taskid, t.todo, t.duedate AS duedate, t.clientid, t.status, c.title, c.lastname, c.firstname FROM #__task t left join t_client c on t.clientid=c.clientid WHERE t.userid='$userid' AND t.duedate>'$sfrom' AND t.duedate<'$sto' ORDER BY duedate";
$sql = "SELECT t.taskid, t.todo, t.duedate AS duedate, t.labelarr, t.status FROM #__task t WHERE t.userid='$userid' AND t.duedate>'$sfrom' AND t.duedate<'$sto' ORDER BY duedate";
$db->setQuery($sql);
$lst = $db->loadObjectList();
if($lst && count($lst) > 0) {
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
		}*/
		if($status == '2') {
			$status_s = 'Processing';
		}
		else if($status == '3') {
			$status_s = 'Pending';
		}
		else {
			$status_s = 'Processing';
		}
		
		$txt0 = $todo.', '.$status_s.', '.$row->labelarr;
		$txt = '<div class="ieditp"><img class="iedit" onclick="medt(\''.$uid.'\')" width="16px" src="'._AP_URL_IMG.'icon_edit.gif" /> <img class="iedit" onclick="mdel(\''.$uid.'\')" width="16px" src="'._AP_URL_IMG.'icon_del3.gif" />'._showhtml($txt0).'</div>';
		if(!isset($monthdata[$row->duedate])) {
			$monthdata[$row->duedate] = '';
			$monthdata2[$row->duedate] = '';
		}
		$monthdata[$row->duedate] .= $txt;
		$monthdata2[$row->duedate] .= _showhtml(str_replace(array("\r", "\n"), array('', ''), $txt0))."\r\n";
	}
}


for($ii = 0; $ii < 12/$mline; $ii++) {
	echo "<tr>\r\n";
	for($jj = 1; $jj <= $mline; $jj++) {
		$m = $ii * $mline + $jj;
		if($mline <= 1) {
			$m = $p_mm;
		}
		echo '<th align="center">'.$arrmonthname[$m-1].'</th><td></td>';
		if($mline <= 1) {
			break;
		}
	}
	echo "</tr>\r\n<tr>\r\n";
	for($jj = 1; $jj <= $mline; $jj++) {
		$m = $ii * $mline + $jj;
		if($mline <= 1) {
			$m = $p_mm;
		}
		echo '<td valign="top">';
		$out = '';

		$date01 = strtotime("$p_yy-$m-01");
		$dateweek = date('w', $date01);
		if($dateweek < $weekfrom) {
			$dateweek += 7;
		}
		$datefrom = strtotime("$p_yy-$m-01 -" . ($dateweek - $weekfrom + 1) . " days");

		$out .= '<table class="xcal" border="1" align="center" cellpadding="1" cellspacing="2" style="width:100%;font-size:10pt;text-align:center;border-top:3px double #ccc;border-color:#00ffff;">';
		$out .= '<tr style="font-weight:bold;height:20px;font-size:12pt;">'.$weektitle.'</tr>';

		$bkcolor = '';
		for($i=0; $i<6; $i++) {
			$out2 = "<tr>\r\n";
			for($j=0; $j<7; $j++) {
				$datefrom = strtotime(date("Y-m-d", $datefrom) . " +1 day");
				$w = date('w', $datefrom);
				$m2 = date('m', $datefrom);
				if($i > 3 && $j == 0 && ($m2 > $m || date('Y', $datefrom) > $p_yy)) {
					$out2 = '';
					//break;
				}
				$sty = 'overflow:hidden;';
				$title = '';
				$color = '';
				$bkcolor2 = '';
				$ymd = date('Ymd', $datefrom);
				$stxt = '';
				if(isset($monthdata[$ymd]) && $m2 == $m) {
					$title = ' title="'.$monthdata2[$ymd].'"';
					$bkcolor2 = '#b0fcb4';
					if($mline <= 1) {
						$stxt = $monthdata[$ymd];
						//if(strlen($stxt) > 10) {
						//	$stxt = substr($monthdata[$ymd], 0, 10).'...';
						//}
					}
					$stxt = '<br><img style="position:absolute;right:0px;top:0px;" width="20px" src="'._AP_URL_IMG.'notice256.jpg" />'.$stxt;
				}
				if($color == '') {
					if($w == 0) {
						$color = 'color:'._getini('corCalSun', '#ff0000').';';
					}
					else if($w == 6) {
						$color = 'color:'._getini('corCalSat', '#0000ff').';';
					}
				}
				else {
					$color = 'color:'.$color.';';
				}
				if($bkcolor2 == '') {
					$bkcolor2 = $bkcolor;
				}
				if($bkcolor2 != '') {
					$bkcolor2 = 'background-color:'.$bkcolor2.';';
				}
				$sty .= $color;
				$sty .= $bkcolor2;
				if($datefrom == $today && $m2 == $m && $p_prn != '1') {
					$sty .= 'background:'.$bkcolor2.' url('._AP_URL_IMG.'today.gif) center no-repeat;';
				}
				$d = date('j', $datefrom);
				if($m2 != $m) {
					if($mline <= 1) {
						$d = '<span style="color:#bbb;">'.$d.'</span>';
					}
					else {
						$d = '<br>';
					}
					$out2 .= '<td style="'.$sty.'" '.$title.'>'.$d.$stxt.'</td>';
				}
				else {
					if($p_prn != '1') {
						$out2 .= '<td class="tdadd" onmouseover="min(this, \''.$ymd.'\');" onmouseout="mout(this);" style="'.$sty.'" '.$title.'>'.$d.$stxt.'</td>';
					}
					else {
						$out2 .= '<td class="tdadd" style="'.$sty.'" '.$title.'>'.$d.$stxt.'</td>';
					}
				}
			}
			if($out2 != '') {
				$out2 .= "</tr>\r\n";
			}
			$out .= $out2;

			if($mline <= 1) {
				if($m2 != $m) {
					break;
				}
			}
		}

		$out .= '</table>';
		$out .= '<div style="height:15px;"></div>';


		echo $out;
		echo '</td><td style="width:15px;"><div style="width:5px;"></div></td>';
		if($mline <= 1) {
			break;
		}
	}
	echo "</tr>\r\n<tr>\r\n";
	for($jj = 1; $jj <= $mline; $jj++) {
		echo '<th></th><td style="width:15px;"></td>';
	}
	echo "</tr>\r\n";
	if($mline <= 1) {
		break;
	}
}
echo '</table>';
echo '<div id="tadd" style="display:none;position:absolute;left:2px;top:2px;"><img class="iedit" onclick="madd()" width="24px" src="'._AP_URL_IMG.'taskadd.gif" /></div>';
?>
<!-- FTabMain START -->
<div id="ftab_edit0" title="Edit a appointment" style="display:none;">
<div title="" id="p-uedit">
</div>
</div>
<!-- FTabMain END -->
<script>
var g_add_ymd = '';
function min(elem, ymd) {
	var o = byId('tadd');
	g_add_ymd = ymd;
	if(o.style.display == '') {
		return;
	}
	o.style.display = '';
	elem.appendChild(o);
}

function mout(elem) {
	var o = byId('tadd');
	o.style.display = 'none';
}
function madd() {
	popw_s('?m=t&act=add&ymd='+g_add_ymd, 500, 380, call_refresh);
}
function selchg(elem, yy, mm) {
<?php
echo '	var ml = elem.value ? elem.value : \''.$mline.'\';
	var yy0 = yy ? yy : \''.$p_yy.'\';
	var mm0 = mm!==false ? mm : \''.$p_mm.'\';
	document.location.href = \'?m='._getrequest('m').'&m2='._getrequest('m2').'&yy=\'+yy0+\'&mm=\'+mm0+\'&ml=\'+ml;
'; ?>
}
function medt(id) {
	popw_s('?m=t&act=edit&taskid='+id, 500, 380, call_refresh);
	return false;
}
function mdel(id) {
	window.event.preventDefault();
	if(!confirm('Do you really remove it?')) {
		return false;
	}
<?php
echo '	document.location.href = \'?m='._getrequest('m').'&m2='._getrequest('m2').'&yy='.$p_yy.'&mm='.$p_mm.'&ml='.$mline.'&act=del&taskid=\'+id
'; ?>
	return false;
}
function call_refresh() {
<?php
echo '	document.location.href = \'?m='._getrequest('m').'&m2='._getrequest('m2').'&yy='.$p_yy.'&mm='.$p_mm.'&ml='.$mline.'\'
'; ?>
}
</script>
