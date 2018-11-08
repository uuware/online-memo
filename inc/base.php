<?php
//管理系统
//  20150615 aboy@tokyo

!defined('DS') && define('DS', DIRECTORY_SEPARATOR);
define('_ST', 1);
define('_ST_DIR_ROOT', dirname(_AP_BASE_FILE).DS);
define('_ST_CODE_BASE', dirname(__FILE__).DS);
$_troot = realpath($_SERVER['DOCUMENT_ROOT']);
$_tdir = substr(_ST_DIR_ROOT, strlen($_troot));
define('_AP_URL_ROOT', str_replace('\\', '/', $_tdir));
define('_AP_URL_IMG', _AP_URL_ROOT.'images/');
define('_AP_URL_INDEX', _AP_URL_ROOT . basename($_SERVER['PHP_SELF']));

define('_AP_UPLOAD_IMG', _ST_DIR_ROOT.'images_up'.DS);
define('_AP_UPLOAD_IMG_URL', _AP_URL_ROOT.'images_up/');

define('_SESSION_PRE', '_it817');

define('_RIGHTS_FLAG_NO', 0);
define('_RIGHTS_FLAG_NG', 1);
define('_RIGHTS_FLAG_ASK', 8);
define('_RIGHTS_FLAG_OK', 9);

$g_title = 'Online Memo System v1.0';
function _iif($condition, $str1, $str2 = '')
{
	if($condition) {
		return $str1;
	}
	return $str2;
}

function _getcat()
{
	return '<option value=""> - </option><option value="work">Work</option><option value="home">Home</option><option value="study">Study</option>';
}

function _getuser()
{
	//for test
	if(false) {
		_setsession('isadmin', '1');
		_setsession('rightsbusiness', '1');
		_setsession('rightsviewer', '1');
		$ent = new stdClass();
		$ent->userid = '1';
		return $ent;
	}

	static $ent = false;
	if($ent) {
		return $ent;
	}

	$userid = _getsession('userid', '0');
	if($userid == '0') {
		echo '<font color="red">Failed to get user information, please login again.</font><br>';
		_destroySession();
		die();
	}

	$db = _getDBO();
	//$sql = 'SELECT * FROM #__user WHERE userid = '.$db->Quote($userid);
	//$db->setQuery($sql);
	//$ent = $db->loadObject();
	$ent = $db->selectObject('#__user', array(), array('userid' => $userid));
	if(!$ent) {
		echo '<font color="red">User: '.$userid.' does not exist, please login again.</font><br>';
		_destroySession();
		die();
	}

	if($ent->rightsadmin == '1') {
		_setsession('isadmin', '1');
	}
	if($ent->rightsbusiness == '1') {
		_setsession('rightsbusiness', '1');
	}
	if($ent->rightsviewer == '1') {
		_setsession('rightsviewer', '1');
	}
	return $ent;
}

function _getclientname($cid)
{
	$db = _getDBO();
	$sql = 'SELECT title,firstname,lastname FROM #__client WHERE clientid = '.$db->Quote($cid);
	$db->setQuery($sql);
	$ent = $db->loadObject();
	if($ent) {
		return $ent->title.' '.$ent->firstname.' '.$ent->lastname;
	}
	return false;
}
function _getusername($uid)
{
	$db = _getDBO();
	$sql = 'SELECT username FROM #__user WHERE userid = '.$db->Quote($uid);
	$db->setQuery($sql);
	$ent = $db->loadObject();
	if($ent) {
		return $ent->username;
	}
	return false;
}

function _getdbdate($date, $char = '-')
{
	$arr = array();
	//mssql:09 21 2015 12:00AM, 09 1 2016 12:00AM
	if(substr($date, -2) == 'AM' || substr($date, -2) == 'PM') {
		$arr2 = explode(' ', $date);
		$arr[0] = $arr2[2];
		$arr[1] = $arr2[0];
		$arr[2] = $arr2[1];
	}
	else {
		$date = str_replace(array('/', '.'), array('-', '-'), $date);
		if(strpos($date, '-') !== false) {
			$arr = explode('-', $date);
			if(count($arr) != 3) {
				return false;
			}
		}
		else if(strlen($date) == 8) {
			$arr[0] = substr($date, 0, 4);
			$arr[1] = substr($date, 4, 2);
			$arr[2] = substr($date, 6, 2);
		}
		else {
			return false;
		}
	}
	if($char == 'zh') {
		return sprintf("%04d年%02d月%02d日", $arr[0], $arr[1], $arr[2]);
	}
	return sprintf("%04d%s%02d%s%02d", $arr[0], $char, $arr[1], $char, $arr[2]);
}

function _post($curlPost, $url){
	$header = array(
		"Content-Type: application/x-www-form-urlencoded",
		"Content-Length: ".strlen($curlPost)
	);
	$context = array(
		"http" => array(
			"method"  => "POST",
			"header"  => implode("\r\n", $header),
			"content" => $curlPost
		)
	);

	$return_str = file_get_contents($url, false, stream_context_create($context));
	return $return_str;


	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_NOBODY, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	$return_str = curl_exec($curl);
	curl_close($curl);
	return $return_str;
}

function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}

function _pg_print($param) {
	$max = floor($param['itemcount'] / $param['pageitems']);
	if(($param['itemcount'] % $param['pageitems']) != 0) {
	    $max++;
	}
	$ind = $param['pageindex'];
	if($max > 0) {
		$prev = _getini('pg_prev', 'Prev');
		$next = _getini('pg_next', 'Next');
		$goto = _getini('pg_goto', 'Go to page ');
		echo '<div id="pg_panel" class="pg_link_panel">';
		if($ind > 0) {
			echo '<a id="pg_prev" class="pg_link_a" onclick="return doPageIndex('.($ind-1).');" href="javascript:void(0)" title="'.$goto.' '.($ind).'"><img src="'._AP_URL_ROOT.'images/icon_left.gif" width="12" height="16" border="0" align="top" /></a>';
		}
		else {
			echo '<span id="pg_prev" class="pg_link_a"><img src="'._AP_URL_ROOT.'images/icon_left_d.gif" width="12" height="16" border="0" align="top" /></span>';
		}

		for($i = 0; $i < $max; $i++) {
			if($i < 3 || $i >= $max-3 || ($i > $ind-4 && $i < $ind+4)) {
				if($i == $ind) {
					echo '<span class="pg_link_cur"><b>'.($i+1).'</b></span>';
				}
				else {
					echo '<a class="pg_link_a" onclick="return doPageIndex('.$i.');" href="javascript:void(0)">'.($i+1).'</a>';
				}
			}
			else {
				if($i == $ind-4 || $i == $ind+4) {
					echo '...';
				}
			}
			//if(($i+1)%25 == 0) {
			//	echo '<br>';
			//}
		}

		if($ind < $max - 1) {
			echo '<a id="pg_next" class="pg_link_a" onclick="return doPageIndex('.($ind+1).');" href="javascript:void(0)" title="'.$goto.' '.($ind+2).'"><img src="'._AP_URL_ROOT.'images/icon_right.gif" width="12" height="16" border="0" align="top" /></a>';
		}
		else {
			echo '<span id="pg_next" class="pg_link_a"><img src="'._AP_URL_ROOT.'images/icon_right_d.gif" width="12" height="16" border="0" align="top" /></span>';
		}
		echo '</div>';
	}
}

//读取配置文件，$def为不存在时候的默认值。当$forblank=true的时候，不存在或为空，都返回$def
function _getini($name, $def = '', $forblank = false)
{
	return StBase::getIni($name, $def, $forblank, false);
}
function _saveIni(&$newvalues)
{
	$inifile = _ST_CODE_BASE.'config.php';
	return StBase::saveIni($inifile, $newvalues);
}

//读取request值，$def为不存在时候的默认值
function _getrequest($name, $def = '')
{
	if(isset($_REQUEST[$name])) {
		return $_REQUEST[$name];
	}
	if(isset($_REQUEST[strtolower($name)])) {
		return $_REQUEST[strtolower($name)];
	}
	return _getpost($name, $def);
}
//设置request值
function _setrequest($name, $value)
{
	$_REQUEST[$name] = $value;
}

//读取post值，$def为不存在时候的默认值
function _getpost($name, $def = '')
{
	if(isset($_POST[$name])) {
		return $_POST[$name];
	}
	if(isset($_POST[strtolower($name)])) {
		return $_POST[strtolower($name)];
	}
	return $def;
}
//设置post值
function _setpost($name, $value)
{
	$_POST[$name] = $value;
}

//读取session值，$def为不存在时候的默认值
function _getsession($name, $def = '')
{
	$namespace = '__'._SESSION_PRE;
	$name = strtoupper($name);
	if(!isset($_SESSION['ready'])) {
		@session_start();
		$_SESSION['ready'] = true;
	}

	static $chktimeout;
	if(isset($_SESSION[$namespace][$name])) {
		return $_SESSION[$namespace][$name];
	}
	return $def;
}
//设置session值，$value=null时删除改值
function _setsession($name, $value)
{
	$namespace = '__'._SESSION_PRE;
	$name = strtoupper($name);
	if(!isset($_SESSION['ready'])) {
		@session_start();
		$_SESSION['ready'] = true;
	}
	if (null === $value) {
		unset($_SESSION[$namespace][$name]);
	}
	else {
		$_SESSION[$namespace][$name] = $value;
	}
}
//释放session
function _destroySession()
{
	$namespace = '__'._SESSION_PRE;
	if(!isset($_SESSION ['ready'])) {
		@session_start();
		$_SESSION ['ready'] = true;
	}
	if(isset($_SESSION['ready'])) {
		unset($_SESSION[$namespace]);
		if(count($_SESSION) == 1) {
			//no others of 'ready'
			unset($_SESSION);
			@session_destroy();
		}
	}
}

//取得访问db对象。$newinstance=true时，创建一个新的对象返回。一般$newinstance=false
function &_getDBO($newinstance = false)
{
	return StBase::getDBO($newinstance);
}

//创建目录。$createindex=true时创建index.html，$createhtaccess=true时创建访问限制.htaccess文件。$iferrordie=true时，不能创建则停止。
function _createDir($dir, $createindex = true, $createhtaccess = false, $iferrordie = false)
{
	return StBase::createDir($dir, $createindex, $createhtaccess, $iferrordie);
}

//处理上传文件，移动到指定的目录
//$fsize=1M
function _uploadfile($fid, $desd, &$desf_inout, $fsize = 1048576, $ftype = '.png;.gif;.jpg;')
{
	$ret = '';
	if(isset($_FILES[$fid]) && $_FILES[$fid]['name'] != "") {
		$f = strtolower($_FILES[$fid]['name']);
		if($_FILES[$fid]['error'] > 0 || $_FILES[$fid]['tmp_name'] == '') {
			$ret .= "<font color=red>上传文件错误: " . $_FILES[$fid]['error'] . "</font><br />";
		}
		else if(strpos($ftype, substr($f, -4).';') === false) {
			$ret .= "<font color=red>只能上传[{$ftype}]文件: " . $f . "</font><br />";
		}
		else if($_FILES[$fid]['size'] > $fsize) {
			$ret .= "<font color=red>文件体积太大（{$_FILES[$file]['size']}），需要<{$fsize}</font><br />";
		}
		else {
			_createDir($desd, true, false, true);
			if($desf_inout == '') {
				$desf_inout = _safefilename($f);
			}
			else if(strpos($desf_inout, '.') === false) {
				$desf_inout .= substr($f, -4);
			}
			$ff = $desd.$desf_inout;
			if(is_file($ff)) {
				unlink($ff);
			}
			if(is_file($ff)) {
				$ret .= "<font color=red>删除旧的文件错误: " . $ff . "</font><br />";
			}
			else {
				$f = $_FILES[$fid]['tmp_name'];
				if(!move_uploaded_file($f, $ff)) {
					copy($f, $ff);
					unlink($f);
				}
				if(!is_file($ff)) {
					$ret .= "<font color=red>上传文件错误: " . $ff . "</font><br />";
				}
			}
		}
	}
	if($ret == '') {
		return true;
	}
	return $ret;
}

//为了安全，对文件名特别处理
function _safefilename($filename) {
	$filename = str_replace(array(':', ';', '"', '\'', '\\', '/', '*', '..', '?', '|'), '_', $filename);
	while(strpos($filename, '..') !== false) {
		$filename = str_replace('..', '_', $filename);
	}
	return trim($filename);
}
function _pathencode($str) {
	return strtr(base64_encode($str), '/', '-');
}
function _pathdecode($str) {
	return base64_decode(strtr($str, '-', '/'));
}
function _fmtFileSize($fileSize) {
	$size = sprintf(" %u ", 0 + $fileSize);
	$sizename = array("Bytes" , "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
	$sizei = floor(log($fileSize, 1024));
	return round($size/pow(1024, $sizei), 3).$sizename[$sizei];
}

/*
//输入数据检查
$ty:
	D:digit, A:alpha
if no $ext then can use:
1.ctype_alnum(string $text) : check for alphabetic or numeric
2.ctype_alpha(string $text)：check for alphabetic character(s),\n,\r,\t
3.ctype_cntrl(string $text)：check for control character(s)
4.ctype_digit(strint $text)：check for numeric character(s)
5.ctype_graph(string $text)：Check for any printable character(s) except space
6.ctype_lower()：check for lowercase character(s)
7.ctype_upper()：check for uppercase character(s)
8.ctype_space： check for whitespace character(s)
9.ctype_xdigit： check for character(s) representing a hexadecimal digit
*/
function _typecheck($s, $ty, $ext = '') {
	$arr = str_split($s);
	$len = count($arr);
	if($ty == "D") {
		for($i = 0; $i < $len; $i++) {
			$c = $arr[$i];
			if(!ctype_digit($c) && (!$ext || !strpos($ext, $c))) {
				return false;
			}
		}
	}
	else if($ty == "A") {
		for($i = 0; $i < $len; $i++) {
			$c = $arr[$i];
			if(!ctype_alpha($c) && (!$ext || !strpos($ext, $c))) {
				return false;
			}
		}
	}
	else if($ty == "AD" || $ty == "DA") {
		for($i = 0; $i < $len; $i++) {
			$c = $arr[$i];
			if((!ctype_digit($c) && !ctype_alpha($c)) && (!$ext || !strpos($ext, $c))) {
				return false;
			}
		}
	}
	return true;
}

//显示db的数据
function _showhtml(&$str, $isBr = false, $isJsText = false)
{
	if($isJsText) {
		return str_replace(array("'", '\r\n', '\n', '\r'), array("\\'", '\\n', '\\n', '\\n'), htmlspecialchars($str, ENT_COMPAT));
	}
	if($isBr) {
		return nl2br(htmlspecialchars($str, ENT_QUOTES));
	}
	return htmlspecialchars($str, ENT_QUOTES);
}

function _tocombo($items, $defvalue, $addid = true, $v_as_k = false)
{
	$ret = '';
	foreach($items as $k => $v) {
	 	if($v_as_k) {
	 		$k = $v;
	 	}
		$sel = ($k == $defvalue) ? ' selected="selected"' : '';
		$ret .= "<option value=\"{$k}\"{$sel}>{$v}".(($addid&&$k!=="")?"[{$k}]":"")."</option>\r\n";
	}
	return $ret;
}

/**
 * 判断是手机访问pc访问
 */
function check_wap() {
    if (isset($_SERVER['HTTP_VIA'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_X_NOKIA_CONNECTION_MODE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_X_UP_CALLING_LINE_ID'])) {
        return true;
    }
    if (strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML") > 0) {
        // Check whether the browser/gateway says it accepts WML.  
        $br = "WML";
    } else {
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '';
        if (empty($browser)) {
            return true;
        }

        $mobile_os_list = array('Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 
        	'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
        $mobile_token_list = array('Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', 
        	'320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 
        	'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod');
        $found_mobile = (check_wap_substrs($mobile_os_list, $browser) || check_wap_substrs($mobile_token_list, $browser));
        if ($found_mobile) {
            $br = "WML";
        } else {
            $br = "WWW";
        }
    }
    if ($br == "WML") {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断手机访问， pc访问
 */
function check_wap_substrs($list, $str) {
    $flag = false;
    for ($i = 0; $i < count($list); $i++) {
        if (strpos($str, $list[$i]) > 0) {
            $flag = true;
            break;
        }
    }
    return $flag;
}


class StBase
{
	function &getDBO($newinstance = false)
	{
		static $instance;
		if(!isset($instance) || $newinstance)
		{
			$dbType = StBase::getIni('dbType');
			$dbHost = StBase::getIni('dbHost');
			$dbPort = StBase::getIni('dbPort');
			$dbUser = StBase::getIni('dbUser');
			$dbName = StBase::getIni('dbName');
			$dbPass = StBase::getIni('dbPass');
			$prefix = StBase::getIni('dbPrefix');
			$dbCharset = StBase::getIni('dbCharset');

			$dbOption = array('host' => $dbHost, 'user' => $dbUser
							, 'password' => $dbPass, 'database' => $dbName
							, 'prefix' => $prefix, 'driver' => $dbType
							, 'port' => $dbPort, 'dbcharset' => $dbCharset);
			//special for sqlite
			if($dbType == 'sqlite' || $dbType == 'pdosqlite') {
				if(strpos($dbName, "/") === false && strpos($dbName, "\\") === false) {
					$dbName = _ST_CODE_BASE . $dbName;
				}
			}
			if($newinstance) {
				$dbOption['new'] = true;
			}

			$db = '';
			if(substr($dbType, 0, 3) == 'pdo') {
				if(!class_exists('db_pdo'))
				{
					include_once(_ST_CODE_BASE . 'db_pdo.php');
				}
				$db = &db_pdo::getInstance($dbOption);
			}
			else {
				if(!class_exists('db_base'))
				{
					include_once(_ST_CODE_BASE . 'db_base.php');
				}
				$db = &db_base::getInstance($dbOption);
			}
			if(is_string($db)) {
				//even error, not stop! but only show error messsage once.
				static $errdb;
				if(!isset($errdb)) {
					if(substr($dbType, 0, 3) == 'pdo') {
						$errdb = new db_pdo($dbOption);
					}
					else {
						$errdb = new db_base($dbOption);
					}
					echo "<span style=\"color:red;position:absolute;top:0;left:0;z-index:99999;\">Type:$dbType, Host:$dbHost.[$db]</span>";
					//die($db);
				}
				return $errdb;
			}
			if($newinstance) {
				return $db;
			}
			$instance = $db;
		}

		return $instance;
	}
	function getIni($name, $def = '', $forblank = false, $updatevalue = false)
	{
		$name = strtoupper($name);
		static $instance;
		if (!isset($instance)) {
			$file = _ST_CODE_BASE.'config.php';
			if(is_file($file)) {
				$G_ST_INI_TEMPVAL = false;
				include_once($file);
				if($G_ST_INI_TEMPVAL) {
					$instance = $G_ST_INI_TEMPVAL;
				}
			}
		}
		if($updatevalue === true) {
			$instance[$name] = $def;
		}
		if(isset($instance[$name])) {
			if($forblank !== false && $instance[$name] == '') {
				if($forblank === true) {
					return $def;
				}
				return $forblank;
			}
			return $instance[$name];
		}
		return $def;
	}

	function createDir($dir, $createindex = true, $createhtaccess = true, $iferrordie = false) {
		if($dir == '') {
			return;
		}
		if(substr($dir, -1) != DS) {
			$dir .= DS;
		}
		if(!is_dir($dir)) {
			mkdir($dir, 0777);
			if($createindex) {
				file_put_contents($dir.'index.html', '<html><body bgcolor="#FFFFFF"></body></html>');
			}
			if($createhtaccess) {
				file_put_contents($dir.'.htaccess', "<Files *>\r\n   Order Allow,Deny\r\n    Deny from All\r\n</Files>\r\n");
			}
		}
		if($iferrordie && !is_dir($dir)) {
			die('Create folder['.$dir.'] error, Stopped!');
		}
	}

	function saveIni($inifile, &$newvalues)
	{
		$comment = '';
		$G_ST_INI_TEMPVAL = false;
		if(file_exists($inifile)) {
			$comment0 = file_get_contents($inifile);
			include($inifile);
			$pos1 = strpos($comment0, 'if(!isset($G_ST_INI_TEMPVAL) || $G_ST_INI_TEMPVAL !== false)');
			if($pos1 !== false) {
				$comment0 = substr($comment0, 0, $pos1);
				$pos1 = strpos($comment0, '/*');
				$pos2 = strpos($comment0, '*/');
				if($pos1 !== false && $pos2 !== false) {
					$comment = substr($comment0, $pos1, $pos2 - $pos1 + 2)."\r\n";
				}
			}
		}

		$out = '<?php
'.$comment.'if(!isset($G_ST_INI_TEMPVAL) || $G_ST_INI_TEMPVAL !== false) {
	die( \'Restricted access\' );
}
$G_ST_INI_TEMPVAL = array(
';

		$savevalues = new stdClass();
		foreach($newvalues as $key => $value) {
			$key = str_replace("'", '', strtoupper($key));
			if($key != '') {
				$savevalues->$key = 1;
				$value = str_replace("'", '&#039;', $value);
				$out .= "	'{$key}' => '$value',\r\n";
			}
		}

		if($G_ST_INI_TEMPVAL) {
			foreach($G_ST_INI_TEMPVAL as $key => $value) {
				if(!isset($savevalues->$key)) {
					$savevalues->$key = 1;
					$key = str_replace("'", '', $key);
					$value = str_replace("'", '&#039;', $value);
					$out .= "	'".strtoupper($key)."' => '$value',\r\n";
				}
			}
		}
		$out .= ");\r\n";

		$ret = file_put_contents($inifile, $out);
		if($ret === false) {
			return false;
		}
		return true;
	}
}
