<?php
// no direct access
defined( '_ST' ) or die( 'Restricted access' );

/**
 * MySQL database driver
 *
 */
class db_mysql extends db_base
{
	/**
	 * The database driver name
	 *
	 * @var string
	 */
	var $name			= 'mysql';

	/**
	 *  The null/zero date string
	 *
	 * @var string
	 */
	var $_nullDate		= '0000-00-00 00:00:00';

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	var $_nameQuote		= '`';

	/**
	* Database object constructor
	*
	* @access	public
	* @param	array	List of options used to configure the connection
	* @since	1.5
	* @see		JDatabase
	*/
	function __construct( $options )
	{
		$host		= array_key_exists('host', $options)	? $options['host']		: 'localhost';
		$user		= array_key_exists('user', $options)	? $options['user']		: '';
		$password	= array_key_exists('password',$options)	? $options['password']	: '';
		$database	= array_key_exists('database',$options)	? $options['database']	: '';
		$prefix		= array_key_exists('prefix', $options)	? $options['prefix']	: 'st0_';
		$select		= array_key_exists('select', $options)	? $options['select']	: true;

		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'mysql_connect' )) {
			$this->_errorNum = DB_ERR_NOTAVAILABLE;
			$this->_errorMsg = 'The MySQL adapter "mysql" is not available.';
			return;
		}

		// connect to the server
		if (!($this->_resource = @mysql_connect( $host, $user, $password, true ))) {
			$this->_errorNum = DB_ERR_NOTCONNECT;
			$this->_errorMsg = 'Could not connect to MySQL';
			return;
		}

		// finalize initialization
		parent::__construct($options);

		// select the database
		if ( $select ) {
			$this->select($database);
		}
	}

	/**
	 * Database object destructor
	 *
	 * @return boolean
	 * @since 1.5
	 */
	function __destruct()
	{
		$return = false;
		if (is_resource($this->_resource)) {
			$return = mysql_close($this->_resource);
			$this->_resource = '';
		}
		return $return;
	}

	/**
	 * Close connection.
	 */
	function close()
	{
		$return = false;
		if (is_resource($this->_resource)) {
			$return = mysql_close($this->_resource);
			$this->_resource = '';
		}
		return $return;
	}

	/**
	 * Test to see if the MySQL connector is available
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function test()
	{
		return (function_exists( 'mysql_connect' ));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @access	public
	 * @return	boolean
	 * @since	1.5
	 */
	function connected()
	{
		if(is_resource($this->_resource)) {
			return mysql_ping($this->_resource);
		}
		return false;
	}

	/**
	 * Select a database for use
	 *
	 * @access	public
	 * @param	string $database
	 * @return	boolean True if the database has been successfully selected
	 * @since	1.5
	 */
	function select($database)
	{
		if ( ! $database )
		{
			return false;
		}

		if ( !mysql_select_db( $database, $this->_resource )) {
			$this->_errorNum = DB_ERR_NOTSELECTDB;
			$this->_errorMsg = 'Could not connect to database';
			return false;
		}

		// if running mysql 5, set sql-mode to mysql40 - thereby circumventing strict mode problems
		if ( strpos( $this->getVersion(), '5' ) === 0 ) {
			$this->setQuery( "SET sql_mode = 'MYSQL40'" );
			$this->query();
		}

		return true;
	}

	/**
	 * Determines UTF support
	 *
	 * @access	public
	 * @return boolean True - UTF is supported
	 */
	function hasUTF()
	{
		$verParts = explode( '.', $this->getVersion() );
		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}

	/**
	 * Custom settings for UTF support
	 *
	 * @access	public
	 */
	function setUTF()
	{
		mysql_query( "SET NAMES 'utf8'", $this->_resource );
	}

	/**
	 * Get a database escaped string
	 *
	 * @param	string	The string to be escaped
	 * @param	boolean	Optional parameter to provide extra escaping
	 * @return	string
	 * @access	public
	 * @abstract
	 */
	function getEscaped( $text, $extra = false )
	{
		//for used by getStTableCreate without connect
		//$result = mysql_real_escape_string( $text, $this->_resource );
		$result = str_replace("'", "''", $text);
		$result = str_replace("\\", "\\\\", $result);
		if ($extra) {
			$result = addcslashes( $result, '%_' );
		}
		return $result;
	}

	/**
	 * Execute the query
	 *
	 * @access	public
	 * @return mixed A database resource if successful, FALSE if not.
	 */
	function query()
	{
		if (!is_resource($this->_resource)) {
			return false;
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->_sql;
		if ($this->_limit > 0 || $this->_offset > 0) {
			$sql .= ' LIMIT '.$this->_offset.', '.$this->_limit;
		}
		if ($this->_debug) {
			$this->_ticker++;
			$this->_log[] = $sql;
		}

		//added for web no coding
		if ($this->_trace) {
			$_SESSION['.trace']['log'][] = 'Query sql start at time:'.StBase::getTimestamp(4, true).', SQL:<span style="color:gray;">'.htmlspecialchars($sql).'</span>';
		}

		$this->_errorNum = 0;
		$this->_errorMsg = '';
		$this->_cursor = mysql_query( $sql, $this->_resource );
		
		//added for web no coding
		if (!$this->_cursor)
		{
			$this->_errorNum = mysql_errno( $this->_resource );
			$this->_errorMsg = mysql_error( $this->_resource )." SQL=$sql";
		}
		if ($this->_trace) {
			if (!$this->_cursor)
			{
				$_SESSION['.trace']['log'][] = 'Query sql end time:'.StBase::getTimestamp(4, true).', <span style="color:red;">Result is error:</span>';
				$_SESSION['.trace']['log'][] = '	errno:'.$this->_errorNum;
				$_SESSION['.trace']['log'][] = '	error:'.$this->_errorMsg;
			}
			else {
				$_SESSION['.trace']['log'][] = 'Query sql end time:'.StBase::getTimestamp(4, true).', Selected rows:'.@mysql_num_rows($this->_cursor).', Affected rows:'.@mysql_affected_rows($this->_resource).'.';
			}
		}

		if (!$this->_cursor)
		{
			return false;
		}
		return $this->_cursor;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return int The number of affected rows in the previous operation
	 * @since 1.0.5
	 */
	function getAffectedRows()
	{
		return mysql_affected_rows( $this->_resource );
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return int The number of rows returned from the most recent query.
	 */
	function getNumRows( $cur=null )
	{
		return mysql_num_rows( $cur ? $cur : $this->_cursor );
	}

	/**
	* SQL Transaction
	* @access private
	*/
	function transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
			case 'start':
				return @mysql_query('START TRANSACTION;', $this->_resource);
			break;

			case 'commit':
				return @mysql_query('COMMIT;', $this->_resource);
			break;

			case 'rollback':
				return @mysql_query('ROLLBACK;', $this->_resource);
			break;
		}

		return true;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return true.
	 */
	function freeResult($cur)
	{
		return mysql_free_result( $cur );
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return current row of the cursor.
	 */
	function fetchObject($cur)
	{
		$ret = null;
		if ($object = mysql_fetch_object( $cur )) {
			$ret = $object;
		}
		return $ret;
	}

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @access	public
	 * @return The value returned in the query or null if the query failed.
	 */
	function loadResult()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row[0];
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	* This global function loads the first row of a query into an object
	*
	* @access	public
	* @return 	object
	*/
	function loadObject( )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($object = mysql_fetch_object( $cur )) {
			$ret = $object;
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	* Load a list of database objects
	*
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*
	* @access	public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadObjectList( $key='' )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object( $cur )) {
			if ($key) {
				$array[$row->$key] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}
	//pageindex for page start index, pageitems for record of per page
	function loadObjectListLimit( &$param, $item_start = -1  )
	{
		if (!($cur = $this->query())) {
			$param['itemcount'] = 0;
			return null;
		}

		$itemcount = $this->getNumRows();
		$pg_cnt = $param['pageitems'];
		$pg_ind = 0;
		if($item_start >= 0) {
		    if(($item_start % $pg_cnt) == 0) {
		        $pg_ind = floor($item_start / $pg_cnt) - 1;
		    }
		    else {
		        $pg_ind = floor($item_start / $pg_cnt);
		    }
		}
		else {
			$pg_ind = $param['pageindex'];
		}
		if($pg_ind < 0) {
		    $pg_ind = 0;
		}
		if($pg_ind * $pg_cnt > $itemcount) {
		    if(($itemcount % $pg_cnt) == 0) {
		        $pg_ind = floor($itemcount / $pg_cnt) - 1;
		    }
		    else {
		        $pg_ind = floor($itemcount / $pg_cnt);
		    }
		}
		$item_start = $pg_ind * $pg_cnt;
		if($item_start >= $itemcount) {
			$item_start = 0;
			$pg_ind = 0;
			$param['pageindex'] = 0; //need also update request[pageindex]!
		}
		$item_end = $item_start + $pg_cnt - 1;
		if($pg_cnt < 0) {
			//then show all
			$item_end = $itemcount - 1;
		}
		if($item_end > $itemcount - 1) {
		    $item_end = $itemcount - 1;
		}
		$param['itemstart'] = $item_start;
		$param['itemend'] = $item_end;
		$param['itemcount'] = $itemcount;

		$array = array();
		if($item_start <= 0 || @mysql_data_seek($cur, $item_start)) {
			while($item_start <= $item_end) {
				$item_start++;
				if ($row = mysql_fetch_object( $cur )) {
					$array[] = $row;
				}
				else {
					break;
				}
			}
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return The first row of the query.
	 */
	function loadRow()
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$ret = null;
		if ($row = mysql_fetch_row( $cur )) {
			$ret = $row;
		}
		mysql_free_result( $cur );
		return $ret;
	}

	/**
	* Load a list of database rows (numeric column indexing)
	*
	* @access public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	* If <var>key</var> is not empty then the returned array is indexed by the value
	* the database key.  Returns <var>null</var> if the query fails.
	*/
	function loadRowList( $key=null )
	{
		if (!($cur = $this->query())) {
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row( $cur )) {
			if ($key !== null) {
				$array[$row[$key]] = $row;
			} else {
				$array[] = $row;
			}
		}
		mysql_free_result( $cur );
		return $array;
	}

	/**
	 * Description
	 *
	 * @access public
	 */
	function insertid()
	{
		//SELECT LAST_INSERT_ID()
		return mysql_insert_id( $this->_resource );
	}

	/**
	 * Description
	 *
	 * @access public
	 */
	function getVersion()
	{
		return mysql_get_server_info( $this->_resource );
	}

	/**
	 * Assumes database collation in use by sampling one text field in one table
	 *
	 * @access	public
	 * @return string Collation in use
	 */
	function getCollation ()
	{
		if ( $this->hasUTF() ) {
			$this->setQuery( 'SHOW FULL COLUMNS FROM #__content' );
			$array = $this->loadObjectList();
			return $array['4']->Collation;
		} else {
			return "N/A (mySQL < 4.1.2)";
		}
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return array A list of all the tables in the database
	 */
	function getTableList()
	{
		$this->setQuery( 'SHOW TABLES' );
		$arr = $this->loadRowList();
		$arr2 = array();
		if($arr) {
			foreach($arr as $tablearr) {
				$arr2[] = $tablearr[0];
			}
		}
		return $arr2;
	}

	/**
	 * Description
	 *
	 * @access	public
	 * @return true if table is exist
	 */
	function existTable($table)
	{
		$table = $this->replacePrefix( $table );
		$cur = @mysql_query( 'SELECT COUNT(*) FROM '.$this->nameQuote($table), $this->_resource );
		if(!$cur) {
			return false;
		}
		mysql_free_result( $cur );
		return true;
	}

	/**
	 * Shows the CREATE TABLE statement that creates the given tables
	 *
	 * @access	public
	 * @param 	array|string 	A table name or a list of table names
	 * @return 	array A list the create SQL for the tables
	 */
	function getTableCreate( $tables )
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval) {
			$this->setQuery( 'SHOW CREATE table ' . $this->nameQuote( $tblval ) );
			$rows = $this->loadRowList();
			foreach ($rows as $row) {
				$result[$tblval] = $row[1];
			}
		}

		return $result;
	}

	/**
	 * get CREATE TABLE text for install
	 *
	 * @access	public
	 * @param 	string 	A table name
	 * @param 	array 	a list of field array
	 * @return 	text for create table
	 */
	function getStTableCreate( $tablename, $fields, $characterutf8 = true )
	{
/*
UNSIGNED?
CREATE TABLE `#__st_lang` (
  `langid` char(5) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  `remark` varchar(100),
  `id` int(11) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`itemid`, `langid`, `propertyid`)
  UNIQUE KEY `st0_section_value_value_aro` (`section_value`(100),`value`(100)),
  KEY `st0_gacl_hidden_aro` (`hidden`)
) TYPE=MyISAM CHARACTER SET `utf8`;

array('fieldid' => 
	array('type' => 'text', 'digit' => 11, 'decimalplace' => 0, 'defaultvalue' => '', 'nullclass' => '1', 'pkclass' => '1', 'incclass' => '1')
);

VCHAR,NUM,CHAR
*/
		$out = "";
		$pk = "";
		for ($loopi = 0; $loopi < 2; $loopi++) {
			foreach ($fields as $field) {
				if($loopi == 0 && $field->pkclass != '1') {
					continue;
				}
				if($loopi == 1 && $field->pkclass == '1') {
					continue;
				}
				if($out != '') {
					$out .= ",\r\n";
				}
				$out .= "  ".$this->nameQuote(strtolower($field->itemid));
				if($field->datatype == 'VCHAR') {
					$out .= " VARCHAR(".$field->digit.")";
				}
				else if($field->datatype == 'CHAR') {
					$out .= " CHAR(".$field->digit.")";
				}
				else if($field->datatype == 'TEXT') {
					$out .= " TEXT";
				}
				else if($field->datatype == 'BLOB') {
					$out .= " BLOB";
				}
				else if($field->datatype == 'DATETIME') {
					$out .= " DATETIME";
				}
				else if($field->datatype == 'NUM') {
					$out .= ' INT';
					if($field->digit != '0') {
						$out .= '('.$field->digit;
						if($field->decimalplace != '0') {
							$out .= ', '.$field->decimalplace;
						}
						$out .= ')';
					}
				}
				else if($field->datatype == 'UNUM') {
					$out .= ' INT';
					if($field->digit != '0') {
						$out .= '('.$field->digit;
						if($field->decimalplace != '0') {
							$out .= ', '.$field->decimalplace;
						}
						$out .= ')';
					}
					$out .= ' UNSIGNED';
				}
				else {
					$out .= " ".strtoupper($field->datatype);
					if($field->digit != '0') {
						$out .= '('.$field->digit;
						if($field->decimalplace != '0') {
							$out .= ', '.$field->decimalplace;
						}
						$out .= ')';
					}
				}
				if($field->pkclass == '1') {
					if($pk != '') {
						$pk .= ", ";
					}
					$pk .= $this->nameQuote(strtolower($field->itemid));
					$out .= " NOT NULL";
				}
				else if($field->nullclass == '0') {
					$out .= " NOT NULL";
				}
				if($field->incclass == '1') {
					$out .= " AUTO_INCREMENT";
				}
				else if($field->defaultvalue != '') {
					$out .= " DEFAULT ".$this->Quote($field->defaultvalue);
				}
				else if($field->defaultblank == '1' && $field->datatype != 'NUM' && $field->datatype != 'UNUM') {
					$out .= " DEFAULT ".$this->Quote('');
				}
			}
		}
		if($pk != '') {
			$out .= ",\r\n";
			$out .= "  PRIMARY KEY ($pk)";
		}
		else {
			$out .= "\r\n";
		}

		$result = "CREATE TABLE ".$this->nameQuote($tablename)." (\r\n" . $out."\r\n";
//for error at 5.5.27, remove next
//		$result .= ') TYPE=MyISAM';
		$result .= ') ';
		//if($characterutf8) {
		//	$result .= ' CHARACTER SET `utf8`';
		//}
		$result .= ";\r\n";
		return $result;
	}

	/**
	 * Retrieves information about the given tables
	 *
	 * @access	public
	 * @param 	array|string 	A table name or a list of table names
	 * @param	boolean			Only return field types, default true
	 * @return	array An array of fields by table
	 */
	function getTableFields( $tables, $typeonly = true )
	{
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval)
		{
			$this->setQuery( 'SHOW FIELDS FROM ' . $tblval );
			$fields = $this->loadObjectList();
			if(!$fields) {
				continue;
			}

			if($typeonly)
			{
				foreach ($fields as $field) {
					$result[$tblval][strtoupper($field->Field)] = strtoupper(preg_replace("/[(0-9)]/",'', $field->Type ));
				}
			}
			else
			{
/*[Field] => lineid
[Type] => int(6)
[Null] => NO
[Key] => PRI or MUL
[Default] => 0
[Extra] => */
				foreach ($fields as $field) {
					$field2 = new stdClass();
					$field2->field = strtoupper($field->Field);
					$p1 = strpos($field->Type, '(');
					$p2 = strpos($field->Type, ')');
					if($p1 !== false && $p2 !== false) {
						$type = substr($field->Type, $p1 + 1, $p2 - $p1 - 1);
						if(strpos($type, ',') !== false) {
							$field2->digit = trim(substr($type, 0, strpos($type, ',')));
							$field2->decimalplace = trim(substr($type, strpos($type, ',') + 1));
						}
						else {
							$field2->digit = trim($type);
							$field2->decimalplace = 0;
						}
					}
					else {
						$field2->digit = 0;
						$field2->decimalplace = 0;
					}
					$type = strtolower(preg_replace("/[(0-9)]/",'', $field->Type ));
					if($type == 'varchar') {
						$field2->type = 'VCHAR';
					}
					else if($type == 'int' || $type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'integer' || $type == 'bigint') {
						$field2->type = 'NUM';
					}
					else if($type == 'bit' || $type == 'bool' || $type == 'boolean') {
						$field2->type = 'NUM';
						$field2->digit = 4;
						$field2->decimalplace = 0;
					}
					else if($type == 'int unsigned') {
						$field2->type = 'UNUM';
					}
					else if($type == 'date' || $type == 'time') {
						$field2->type = 'VCHAR';
						$field2->digit = 10;
						$field2->decimalplace = 0;
					}
					else if($type == 'datetime' || $type == 'timestamp') {
						$field2->type = 'DATETIME';
						$field2->digit = 0;
						$field2->decimalplace = 0;
					}
					else if($type == 'text' || $type == 'mediumtext' || $type == 'longtext' || $type == 'tinytext') {
						$field2->type = 'TEXT';
						$field2->digit = 0;
						$field2->decimalplace = 0;
					}
					else if($type == 'blob' || $type == 'mediumblob' || $type == 'longblob' || $type == 'tinyblob') {
						$field2->type = 'BLOB';
						$field2->digit = 0;
						$field2->decimalplace = 0;
					}
					else {
						$field2->type = strtoupper($type);
					}
					$field2->nullclass = ($field->Null == 'YES' ? '1' : '0');
					$field2->pkclass = ($field->Key == 'PRI' ? '1' : '0');
					//TODO:how to know if is default '' or not default value
					$field2->defaultblank = (isset($field->Default) && $field->Default === '' ? '1' : '0');
					$field2->defaultvalue = $field->Default;
					if(strpos($field->Extra, 'auto_increment') !== false) {
						$field2->incclass = '1';
					}
					else {
						$field2->incclass = '0';
					}
					$field2->extra = $field->Extra;
					$result[$tblval][$field2->field] = $field2;
				}
			}
		}

		return $result;
	}

	function QuoteTrim( $text )
	{
		return 'TRIM(BOTH FROM '.$text.')';
	}
}
?>
