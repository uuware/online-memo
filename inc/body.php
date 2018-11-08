<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $g_title; ?></title>
<link rel="stylesheet" href="ext/common.css?v=20181022" type="text/css">
<script type="text/javascript" src="ext/common.js?v=20181022"></script>
<link rel="stylesheet" type="text/css" href="ext/calendar/tcal.css?v=20181022" />
<script type="text/javascript" src="ext/calendar/tcal.js?v=20181022"></script>

<link rel="stylesheet" href="ext/ftab/ftab.css">
<link rel="stylesheet" href="ext/ftab/ftab_green.css">
<script type="text/javascript" src="ext/ftab/ftab.js"></script>
<script type="text/javascript">
<?php
echo "var AP_URL_ROOT = '"._AP_URL_ROOT."';
var AP_URL_INDEX = '"._AP_URL_INDEX."';
var AP_URL_IMAGE = '"._AP_URL_IMG."';
";
?>
var g_first = true;
function s_menu(obj) {
	var om = byId('mark_menu_p');
	var ob = byId('mark_body_p');
	if(om.style.display == 'none') {
		om.style.display = '';
	}
	else {
		om.style.display = 'none';
	}
	window.event.preventDefault();

	if(g_first) {
		g_first = false;
		document.addEventListener("click", function(e){
		    e = e || window.event;
		    var tg = e.srcElement || e.target;
		    if(tg.getAttribute('name') == 'mx') return;
			var om = byId('mark_menu_p');
			if(om.style.display != 'none') {
				om.style.display = 'none';
			}
		});
	}
}
</script>
<style>
<?php
$backheader = _getini('backheader', '#0000ff', true);
$backmenu = _getini('backmenu', '#dcdddd', true);
$backbody = _getini('backbody', '#ffffff', true);
echo '
.site-title {background-color: '.$backheader.';}
.navbar-nav-p {background-color: '.$backmenu.';}
body, form {background-color: '.$backbody.';}
';
?>
</style>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">

<?php
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//show login page
	if(_getrequest('m') == 'r') {
		include_once('inc/login-reg.php');
		echo '</form></body></html>';
		return;
	}
	include_once('inc/login.php');
	echo '</form></body></html>';
	return;
}
if(_getsession('isadmin') != '1' && _getsession('rightsbusiness') != '1' && _getsession('rightsviewer') != '1') {
	//judge access rights
	die("No access rights!");
}




//special process
if(_getrequest('prn') == '1') {
	include_once('inc/p-taskcalendar.php');
	echo '</form></body></html>';
	return;
}
$mlink = _getrequest('m');
$mlink2 = _getrequest('m2');
if($mlink == 'u') {
	if(_getrequest('act') == 'edit' || _getrequest('act') == 'add') {
		include_once('inc/p-useradd.php');
		echo '</form></body></html>';
		return;
	}
}
else if($mlink == 'l') {
    if(_getrequest('act') == 'edit' || _getrequest('act') == 'add') {
        include_once('inc/p-labeladd.php');
        echo '</form></body></html>';
        return;
    }
}
else if($mlink == 't') {
	if(_getrequest('act') == 'edit' || _getrequest('act') == 'add') {
		include_once('inc/p-taskadd.php');
		echo '</form></body></html>';
		return;
	}

	else if(_getrequest('act') == 'prn') {
		include_once('inc/p-tasklist.php');
		echo '</form></body></html>';
		return;
	}
}
?>
<!--body begin-->
<center><div class="bodywrapper">
<div class="bodywrapper_s" id="bodywrapper_s">

<!--header begin-->
<span name="mx" class="mark_menu" onclick="s_menu(this)" style="float:left;">
<div name="mx"></div>
<div name="mx"></div>
<div name="mx"></div>
</span>
<div class="site-title"><?php echo $g_title; ?>  <a class="header-link" href="?act=loginexit">[Logout]</a></div>
<div id="mark_menu_p" class="navbar-nav-p" style="display:none;position:absolute;left:0px;top:0px;z-index:1;width:250px;height:100%;">
<div onclick="s_menu()" style="width:100%; height:40px;padding-left:10px;"><img src="images/arrow-left.png" height="40px" /></div>
<ul class="navbar-nav" id="mark_menusub">
<li class="nav-item">Memo Management</li>
<li class="nav-item"><a class="nav-link" href="?m=task&m2=list">Memo List</a></li>
<li class="nav-item"><a class="nav-link" href="?m=task&m2=calendar">Calendar</a></li>
<li class="nav-item">Label Management</li>
<li class="nav-item"><a class="nav-link" href="?m=label&m2=list">Label List</a></li>
<li class="nav-item">User Management</li>
<li class="nav-item"><a class="nav-link" href="?m=user&m2=list">User List</a></li>
<li class="nav-item"><a class="nav-link" href="?m=user&m2=stamp">Login stamp</a></li>
<li class="nav-item">Config</li>
<li class="nav-item"><a class="nav-link" href="?m=cfg&m2=base">Base Config</a></li>
<li class="nav-item"><a class="nav-link" href="?m=cfg&m2=calendar">Calendar Config</a></li>
</ul>
</div>

<input type="hidden" name="m" id="m" value="<?php echo $mlink; ?>">
<input type="hidden" name="act" id="act" value="<?php echo _getrequest('act'); ?>">
<input type="hidden" name="pg_ind" id="pg_ind" value="<?php echo _getrequest('pg_ind'); ?>">
<!--header end-->

<center>
<div class="bodywrapper_s2" id="bodywrapper_s2">
<?php
	if($mlink == '') {
		$mlink = 'task';
	}
	if($mlink2 == '') {
		$mlink2 = 'calendar';
	}
	include_once('inc/p-'.$mlink.$mlink2.'.php');
?>
</div>
</center>

</div>
</div></center>

<div id="ftab_ed" title="" style="display:none;">
<div title="" style="border:8px solid orange;" id="ftab_ed_c"></div>
</div>
<script>
var ftab_edit = null;
var fun_call = null;
function popw_s(src, ww, hh, fun) {
	fun_call = fun;
	byId('ftab_ed_c').innerHTML = '<iframe src="'+src+'" width="100%" height="100%" frameborder="no"></iframe>';
	ftab_edit = FTab('ftab_ed',false,false,ww,hh,'modal:1;title:0;status:0;tab:0;cookie:0;center:1;keepcenter:1;scroll:0;');
	ftab_edit.show();
}
function popw_h() {
	if(ftab_edit) ftab_edit.hide();
	if(fun_call) fun_call();
}
</script>

</form>
</body>
</html>
