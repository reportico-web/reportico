<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2014 Peter Deed

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        swdb.php
 *
 * Contains interface to data retrieval functionality
 * that is responsible for fetching data from databases
 * during report execution
 * 
 * Your database must be supported by the ADODB database
 * abstraction classes provided along with Reportico. Currently
 * the only databases to be tested are MySQL and Informix
 * 
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swdb.php,v 1.17 2014/05/17 15:12:31 peter Exp $
 */


// Include the ADODB Database Abstraction Classes
include_once('reportico_adodb/adodb.inc.php');

/**
 * Class reportico_datasource
 *
 * Core interface for database retrieval
 */
class reportico_datasource extends reportico_object
{

    var	$driver = "mysql";
    var	$host_name;
	var	$service_name;
	var	$user_name = false;
	var	$password = "";
	var	$database;
	var	$server;
	var	$protocol;
	var	$connection;
	var	$connected = false;
	var	$ado_connection;

	var $_conn_host_name = SW_DB_HOST;
	var $_conn_user_name = SW_DB_USER;
	var $_conn_password = SW_DB_PASSWORD;
	var $_conn_driver = SW_DB_DRIVER;
	var $_conn_database = SW_DB_DATABASE;
	var $_conn_server = SW_DB_SERVER;
	var $_conn_protocol = SW_DB_PROTOCOL;

    var $external_connection = false;
    var $available_connections = false;
	
	function __construct(&$pdo = false, $connections = false)
	{
        $this->external_connection = &$pdo;
        $this->available_connections = &$connections;
	}

	function set_details($driver = "mysql", $host_name = "localhost", 
						$service_name = "?Unknown?",
						$server = false, $protocol = false )
	{
		$this->driver = $driver;
		$this->host_name = $host_name;
		$this->service_name = $service_name;
		$this->protocol = $protocol;
		$this->server = $server;
	}

	function set_database($database)
	{
		$this->database = $database;
	}

    // Gets database specific represenation of encoding value based on project encoding
    function get_encoding_for_db_driver($in_db_driver, $in_encoding)
    {
        $out_encoding = $in_encoding;

        if ( !$in_encoding || $in_encoding == "None" )
            return false;

        // Get MySQL DB encoding value to use in "SET NAMES" SQL Command
        if ( $in_db_driver == "pdo_mysql" )
        {
            switch ( $in_encoding )
            {   
                case "LATIN1" : $out_encoding = 'latin1'; break;
                case "GBK" : $out_encoding = 'gbk'; break;
                case "GBK2312" : $out_encoding = 'gbk2312'; break;
                case "UTF8" : $out_encoding = 'utf8'; break;
                case "LATIN1" : $out_encoding = 'latin1'; break;
                case "LATIN2" : $out_encoding = 'latin2'; break;
                case "LATIN5" : $out_encoding = 'latin5'; break;
                case "LATIN7" : $out_encoding = 'latin7'; break;
                case "EUC_JP" : $out_encoding = 'ujis'; break;
                case "EUC_KR" : $out_encoding = 'euckr'; break;
                case "GBK" : $out_encoding = 'gbk'; break;
                case "ISO_8859_7" : $out_encoding = 'greek'; break;
                case "ISO_8859_8" : $out_encoding = 'hebrew'; break;
                case "WIN1250" : $out_encoding = 'cp1250'; break;
                case "WIN1251" : $out_encoding = 'cp1251'; break;
                case "WIN1256" : $out_encoding = 'cp1256'; break;
                case "WIN1257" : $out_encoding = 'cp1257'; break;
                case "BIG5" : $out_encoding = 'big5'; break;
                case "EUCJPMS" : $out_encoding = 'eucjpms'; break;
                case "BINARY" : $out_encoding = 'binary'; break;
                case "CP850" : $out_encoding = 'cp850'; break;
                case "ARMSCII8" : $out_encoding = 'armscii8'; break;
                case "ASCII" : $out_encoding = 'ascii'; break;
                case "CP852" : $out_encoding = 'cp852'; break;
                case "CP866" : $out_encoding = 'cp866'; break;
                case "DEC8" : $out_encoding = 'dec8'; break;
                case "GB2312" : $out_encoding = 'gb2312'; break;
                case "GEOSTD8" : $out_encoding = 'geostd8'; break;
                case "HP8" : $out_encoding = 'hp8'; break;
                case "KEYBCS2" : $out_encoding = 'keybcs2'; break;
                case "KOI8U" : $out_encoding = 'koi8u'; break;
                case "MACCE" : $out_encoding = 'macce'; break;
                case "MACROMAN" : $out_encoding = 'macroman'; break;
                case "SWE7" : $out_encoding = 'swe7'; break;
                case "TIS620" : $out_encoding = 'tis620'; break;
                case "UCS2" : $out_encoding = 'ucs2'; break;
                default:  $out_encoding = $in_encoding;
            }
        }

	// Get PostgreSQL DB encoding value to use in "SET NAMES" SQL Command
        if ( $in_db_driver == "pdo_pgsql" )
        {
            switch ( $in_encoding )
            {   
                case "UTF8":
                case "LATIN1":
                case "LATIN2":
                case "LATIN3":
                case "LATIN4":
                case "LATIN5":
                case "LATIN6":
                case "LATIN7":
                case "LATIN8":
                case "LATIN9":
                case "LATIN10":
                case "EUC_CN":
                case "EUC_JP":
                case "EUC_KR":
                case "EUC_TW":
                case "GB18030":
                case "GBK":
                case "ISO_8859_5":
                case "ISO_8859_6":
                case "ISO_8859_7":
                case "ISO_8859_8":
                case "JOHAB":
                case "KOI8":
                case "MULE_INTERNAL":
                case "SJIS":
                case "SQL_ASCII":
                case "UHC":
                case "WIN866":
                case "WIN874":
                case "WIN1250":
                case "WIN1251":
                case "WIN1252":
                case "WIN1253":
                case "WIN1254":
                case "WIN1255":
                case "WIN1256":
                case "WIN1257":
                case "WIN1258":
                case "BIG5":
                	$out_encoding = $in_encoding;
                default:  $out_encoding = false;
            }
        }

        if ( $in_db_driver == "pdo_mssql" )
        {
            switch ( $in_encoding )
            {   
                case "UTF8" : $out_encoding = 'UTF-8'; break;
                default:  $out_encoding = false;
            }
        }
        return $out_encoding;
    }


	function map_column_type($driver, $type)
	{
		$ret = $type;
		switch ( $driver )
		{
			case "informix":
				switch ( (int)$type )
				{
					case 2:
					case 258:
					case 262:
						$ret = "integer";
						break;

					case 1:
						$ret = "interval hour to second";
						break;

					case 10:
						$ret = "datetime year to second";
						break;

					case 14:
						$ret = "interval hour to second";
						break;

					case 5:
						$ret = "decimal(16)";
						break;

					case 256:
					case 0:
						$ret = "char";
						break;

					case 1:
					case 257:
						$ret = "smallint";
						break;

					default:
						break;
				}
				break;

			default:
				$retype = $type;
				break;
		}
		return $ret;
	}

    // Returns string of PDO drivers
    function pdo_drivers_as_string ()
    {
       
        $drivers = PDO::getAvailableDrivers();
        return ( implode($drivers, ",") );
    }

    // Checks if request pdo driver exists
    function pdo_driver_exists ( $in_driver )
    {
        $drivers = PDO::getAvailableDrivers();
        $found = false;
        foreach ( $drivers as $v )
        {
            if ( $v == $in_driver )
            {
                $found = true;
                break;
            }
        }
        return $found;
    }

	function connect($ignore_config = false)
	{
		$connected = false;

		if ( $this->connected ) 
		{
			$this->disconnect();
		}

		if ( $ignore_config )
		{
			$this->_conn_driver = $this->driver;
			$this->_conn_user_name = $this->user_name;
			$this->_conn_password = $this->password;
			$this->_conn_host_name = $this->host_name;
			$this->_conn_database = $this->database;
			$this->_conn_server = $this->server;
			$this->_conn_protocol = $this->protocol;
		}
		else if ( SW_DB_CONNECT_FROM_CONFIG )
		{
			$this->_conn_driver = SW_DB_DRIVER;
			if ( !$this->_conn_user_name ) 
				$this->_conn_user_name = $this->user_name;
			$this->_conn_password = SW_DB_PASSWORD;
			if ( !$this->_conn_password ) 
				$this->_conn_password = $this->password;
			$this->_conn_host_name = SW_DB_HOST;
			$this->_conn_database = SW_DB_DATABASE;
			$this->_conn_server = SW_DB_SERVER;
			$this->_conn_protocol = SW_DB_PROTOCOL;
		}
		else
		{
			$this->_conn_driver = $this->driver;
			$this->_conn_driver = SW_DB_DRIVER;
			$this->_conn_user_name = $this->user_name;
			$this->_conn_password = $this->password;
			$this->_conn_host_name = $this->host_name;
			$this->_conn_database = SW_DB_DATABASE;
			$this->_conn_server = SW_DB_SERVER;
			$this->_conn_protocol = SW_DB_PROTOCOL;
		}

		if ( $this->_conn_driver == "none" )
		{
			$connected = true;
		}


        if ( $this->external_connection )
        {
            $this->ado_connection = NewADOConnection("pdo");
			$this->ado_connection->ConnectExisting($this->external_connection);
		    $this->connected = true;
		    return $this->connected;
        }

        if ( defined ("SW_DB_TYPE") && SW_DB_TYPE == "existingconnection" && !$this->external_connection )
        {
				handle_error( "Project defined to use existing connection but none set.");
                return false;
        }

        if ( defined ("SW_DB_TYPE") && preg_match ("/^byname_/", SW_DB_TYPE) )
        {
            $connection_name = preg_replace("/byname_/", "", SW_DB_TYPE);
            if ( !isset($this->available_connections[$connection_name] ))
            {
				handle_error( "Connection name \"$connection \" not found in framework connection set");
                return false;
            }
            $useConnection = $this->available_connections[$connection_name];
            switch($useConnection["driver"]) 
            {
                case "pgsql":
                    $this->driver = "pdo_pgsql";
                    break;
                case "sqlsrv":
                    $this->_conn_driver = "pdo_sqlsrv";
                    break;
                case "mysql":
                    $this->_conn_driver = "pdo_mysql";
                    break;
                case "sqlite":
                    $this->_conn_driver = "pdo_sqlite3";
                    break;
                default: 
                    $this->_conn_driver = "unknown";
            }

            // Extract Yii database elements from connection string 
            $this->_conn_host_name = "unknown";
            $this->_conn_database = "unknown";
            $this->_conn_user_name = "unknown";
            $this->_conn_password = "unknown";

            if ( isset ( $useConnection["host"] ) ) $this->_conn_host_name = $useConnection["host"];
            if ( isset ( $useConnection["database"] ) ) $this->_conn_database = $useConnection["database"];
            if ( isset ( $useConnection["username"] ) ) $this->_conn_user_name = $useConnection["username"];
            if ( isset ( $useConnection["password"] ) ) $this->_conn_password = $useConnection["password"];
        }

		switch ( $this->_conn_driver )
		{
			case "none":
				$connected = true;
				break;

			case "array":
				$this->ado_connection = new reportico_db_array();
				$this->ado_connection->Connect($this->_conn_database);
				$connected = true;
				break;

			case "mysql":
				$this->ado_connection = NewADOConnection($this->_conn_driver);
				$this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
				$connected = $this->ado_connection->Connect($this->_conn_host_name,
					$this->_conn_user_name,$this->_conn_password,$this->_conn_database);
				break;

			case "informix":
				$this->ado_connection = NewADOConnection($this->_conn_driver);
				$this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
				if ( function_exists("ifx_connect") )
					$connected = $this->ado_connection->Connect($this->_conn_host_name,
						$this->_conn_user_name,$this->_conn_password,$this->_conn_database);
				else
					handle_error( "Attempt to connect to Informix Database Failed. Informix PHP Driver is not Available");
				break;

			case "pdo_mssql":
				if ( class_exists('PDO', false) )
				{
                    if ( $this->pdo_driver_exists( "dblib" ) )
                    {
					    $this->ado_connection = NewADOConnection("pdo");
					    $cnstr =
						    "dblib:".
						    "host=".$this->_conn_host_name."; ".
						    "username=".$this->_conn_user_name."; ".
						    "password=".$this->_conn_password."; ".
						    "dbname=".$this->_conn_database;
                        if ( $dbenc = $this->get_encoding_for_db_driver (  $this->_conn_driver, SW_DB_ENCODING ) )
                                $cnstr .= ";CharacterSet=".$dbenc;

					    $connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
                    }
                    else
                    if ( $this->pdo_driver_exists( "mssql" ) )
                    {
					    $this->ado_connection = NewADOConnection("pdo");
					    $cnstr =
						    "mssql:".
						    "host=".$this->_conn_host_name."; ".
						    "username=".$this->_conn_user_name."; ".
						    "password=".$this->_conn_password."; ".
						    "dbname=".$this->_conn_database;

                        if ( $dbenc = $this->get_encoding_for_db_driver (  $this->_conn_driver, SW_DB_ENCODING ) )
                                $cnstr .= ";CharacterSet=".$dbenc;

					    $connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
                    }
                    else
                    {   
                        trigger_error("PDO driver for mssql not found. Drivers \"dblib\" and \"mssql\" not found. Available drivers are ".$this->pdo_drivers_as_string(), E_USER_NOTICE);
                    }
				}
				else
					handle_error( "Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
				break;

			case "pdo_mssql":
				if ( class_exists('PDO', false) )
				{
					$this->ado_connection = NewADOConnection("pdo");
					$cnstr =
						"dblib:".
						"host=".$this->_conn_host_name."; ".
						"username=".$this->_conn_user_name."; ".
						"password=".$this->_conn_password."; ".
						"dbname=".$this->_conn_database;
					$connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
				}
				else
					handle_error( "Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
				break;

			case "pdo_sqlsrv":
				if ( class_exists('PDO', false) )
				{

					$this->ado_connection = NewADOConnection("pdo");
                    if ( $this->_conn_protocol )
						$cnstr = "sqlsrv:".  "Server=".$this->_conn_host_name.",".$this->_conn_protocol."; ".  "Database=".$this->_conn_database;
					else
						$cnstr = "sqlsrv:".  "Server=".$this->_conn_host_name."; ".  "Database=".$this->_conn_database;
					$connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
				}
				else
					handle_error( "Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
				break;

			case "oci8":
				$this->ado_connection = NewADOConnection($this->_conn_driver);
				$this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
				$connected = $this->ado_connection->Connect($this->_conn_host_name,
					$this->_conn_user_name,$this->_conn_password,$this->_conn_database);
				break;

			case "pdo_oci":
				if ( class_exists('PDO', false) )
				{
                    if ( !$this->pdo_driver_exists( "oci" ) )
                    {   
                        trigger_error("PDO driver \"oci\" not found. Available drivers are ".$this->pdo_drivers_as_string(), E_USER_NOTICE);
                    }
                    else
                    {
					    $this->ado_connection = NewADOConnection("pdo");
                        if ( $this->_conn_protocol )
					        $cnstr = "oci:".  "dbname=//".$this->_conn_host_name.":".$this->_conn_protocol."/".$this->_conn_database;
                        else
					        $cnstr = "oci:".  "dbname=//".$this->_conn_host_name."/".$this->_conn_database;

                        if ( $dbenc = $this->get_encoding_for_db_driver (  $this->_conn_driver, SW_DB_ENCODING ) )
                                $cnstr .= ";charset=".$dbenc;

					    $connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
                    }
				}
				else
					handle_error( "Attempt to connect to Oracle Database Failed. PDO Support does not seem to be Available");
				break;

			case "pdo_pgsql":
				if ( class_exists('PDO', false) )
				{
                    if ( !$this->pdo_driver_exists( "pgsql" ) )
                    {   
                        trigger_error("PDO driver \"pgsql\" not found. Available drivers are ".$this->pdo_drivers_as_string(), E_USER_NOTICE);
                    }
                    else
                    {
					    $this->ado_connection = NewADOConnection("pdo");
                        $hostarr = explode(":", $this->_conn_host_name);
                        if ( count($hostarr) > 1 )
                        {
					        $cnstr =
						    "pgsql:".
						    "host=".$hostarr[0]."; ".
						    "port=".$hostarr[1]."; ".
						    "user=".$this->_conn_user_name."; ".
						    "password=".$this->_conn_password."; ".
						    "dbname=".$this->_conn_database;
                        }
                        else
                        {
					        $cnstr =
						    "pgsql:".
						    "host=".$hostarr[0]."; ".
						    "user=".$this->_conn_user_name."; ".
						    "password=".$this->_conn_password."; ".
						    "dbname=".$this->_conn_database;
                        }
					    $connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);

                        if ( $connected )
                        {
                            if ( $dbenc = $this->get_encoding_for_db_driver (  $this->_conn_driver, SW_DB_ENCODING ) )
                                $this->ado_connection->Execute("set names '".$dbenc."'");
                        }
                    }
				}
				else
					handle_error( "Attempt to connect to PostgreSQL Database Failed. PDO Support does not seem to be Available");
				break;

			case "pdo_mysql":
				if ( class_exists('PDO', false) )
				{
                    if ( !$this->pdo_driver_exists( "mysql" ) )
                    {   
                        trigger_error("PDO driver \"mysql\" not found. Available drivers are ".$this->pdo_drivers_as_string(), E_USER_NOTICE);
                    }
                    else
                    {
					    $this->ado_connection = NewADOConnection("pdo");

                        // Extract port from host if necessary
                        $hostarr = explode(":", $this->_conn_host_name);
                        if ( count($hostarr) > 1 )
                        {
					        $cnstr =
						        "mysql:".
						        "host=".$hostarr[0]."; ".
						        "port=".$hostarr[1]."; ".
						        //"username=".$this->_conn_user_name."; ".
						        //"password=".$this->_conn_password."; ".
						        "dbname=".$this->_conn_database;
                        }
                        else
                        {
					        $cnstr =
						        "mysql:".
						        "host=".$this->_conn_host_name."; ".
						        //"username=".$this->_conn_user_name."; ".
						        //"password=".$this->_conn_password."; ".
						        "dbname=".$this->_conn_database;
                        }
					    $connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
    
                        if ( $connected )
                        {
                                if ( $dbenc = $this->get_encoding_for_db_driver (  $this->_conn_driver, SW_DB_ENCODING ) )
                                    $this->ado_connection->Execute("set names '".$dbenc."'");
                        }
                    }
				}
				else
					handle_error( "Attempt to connect to MySQL Database Failed. PDO Support does not seem to be Available");
				break;

			case "pdo_sqlite3":
				if ( class_exists('PDO', false) )
				{
					$this->ado_connection = NewADOConnection("pdo");
					$cnstr = "sqlite:" . $this->_conn_database;
					$connected = $this->ado_connection->Connect($cnstr,'','');
				}
				else
					handle_error( "Attempt to connect to SQLite-3 Database Failed. PDO Driver is not Available");
				break;

			case "sqlite":
				$driver =   'sqlite' ;
				$database = $this->_conn_host_name . $this->_conn_database;
				$query =    'select * from Chave' ;
				$db = ADONewConnection($driver);
				if ($db && $db->PConnect($database, "", "", ""))
				{
				}
				else
				{
					die( "* CONNECT TO SQLite-2 FAILED" ) ;
				}
				break;

			case "pdo_informix":
				if ( class_exists('PDO', false) )
				{
					$this->ado_connection = NewADOConnection("pdo");
					$cnstr =
						"informix:".
						"host=".$this->_conn_host_name."; ".
						"server=".$this->_conn_server."; ".
						"protocol=".$this->_conn_protocol."; ".
						"username=".$this->_conn_user_name."; ".
						"password=".$this->_conn_password."; ".
						"database=".$this->_conn_database;
					$connected = $this->ado_connection->Connect($cnstr,$this->_conn_user_name,$this->_conn_password);
				}
				else
					handle_error( "Attempt to connect to Informix Database Failed. PDO Support does not seem to be Available");
				break;

			case "odbc":
				$this->ado_connection = NewADOConnection($this->_conn_driver);
				$this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
				$connected = $this->ado_connection->Connect($this->_conn_host_name,
						$this->_conn_user_name, $this->_conn_password);
				break;

			case "unknown":
				handle_error( "Database driver of unknown specified - please configure your project database connectivity");
				break;

			case "SW_FRAMEWORK_DB_DRIVER":
				handle_error( "You have configured your project to connnect to a database held within a web framework or Content Management System. You need to set constants SW_FRAMEWORK_DB_DRIVER, SW_FRAMEWORK_DB_USER,SW_FRAMEWORK_DB_PASSWORD,SW_FRAMEWORK_DB_HOST,SW_FRAMEWORK_DB_DATABASE from within the calling framework in order to run in this way. You are probably not trying to run from within a framework");
				break;

			default:
				$this->ado_connection = NewADOConnection($this->_conn_driver);
                if ( $this->ado_connection )
                {
				    $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
				    $connected = $this->ado_connection->Connect($this->_conn_host_name,
					    $this->_conn_user_name,$this->_conn_password,$this->_conn_database);
                }
		}

		// Note force connected for SQLite3
		if ( $this->_conn_driver == "sqlite" )
		{
			$connected = true ;
		}
		else
		{
            if ( $this->ado_connection && !$connected && $this->_conn_driver != "unknown" )
				handle_error( "Error in Connection to $this->_conn_driver database :".$this->ado_connection->ErrorMsg());
		}

		$this->connected = $connected;
		return $this->connected;
	}

	function disconnect()
	{
		if ( $this->connected && $this->_conn_driver != "none" ) 
			$this->ado_connection->Close();
		$this->connected = false;
	}
}

/**
 * Class reportico_db_array
 *
 * Allows an array of data to appear like a database table by
 * implementing the necessary functions for connecting, disconnecting
 * and fetching. This means the Reportico engine will not care if data comes
 * from a database or an array
 */
class reportico_db_array
{
	var $array_set;
	var $EOF = false;
	var $ct = 0;
	var $numrows = 0;

	function __construct()
	{
	}

	function Connect(&$in_array)
	{
		$this->array_set =& $in_array;
		reset($this->array_set);
		$k = key($this->array_set);
		$this->numrows = count($this->array_set[$k]);
	}

	function FetchRow()
	{
		$rs = array();

		reset($this->array_set);
		while ( $d =& key($this->array_set) )
		{
			$rs[$d] = $this->array_set[$d][$this->ct];
			next($this->array_set);
		}
		$this->ct++;

		if ( $this->ct == $this->numrows )
		{
			$this->EOF = true;
		}

		return($rs);
	}

	function & ErrorMsg()
	{
		return "Array dummy Message";
	}

	function Close()
	{
		return ;
	}

	function & Execute($in_query)
	{
		return($this);
	}


}

?>
