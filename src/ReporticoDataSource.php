<?php

/*
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
 * @version $Id: swdb.php,v 1.17 2014/05/17 15:12:31 peter Exp $
 */

namespace Reportico\Engine;

use \PDO;

/**
 * Class ReporticoDataSource
 *
 * Core interface for database retrieval
 */
class ReporticoDataSource extends ReporticoObject
{

    public $type = false;
    public $driver = "mysql";
    public $host_name;
    public $service_name;
    public $user_name = false;
    public $password = "";
    public $database;
    public $server;
    public $protocol;
    public $connection;
    public $connection_string;
    public $connected = false;
    public $ado_connection;

    public $_conn_host_name;
    public $_conn_user_name;
    public $_conn_password;
    public $_conn_driver;
    public $_conn_database;
    public $_conn_server;
    public $_conn_protocol;

    public $external_connection = false;
    public $available_connections = false;

    public $usage = array(
        "description" => "Select data source",
        "methods" => array(
            "datasource" => array(
                "description" => "Select source database",
            ),
            "database" => array(
                "description" => "Connection String",
                "parameters" => array(
                    "connection string" => "PDO connection stringmysql:host=localhost; dbname=mydb",
                ),
            ),
            "user" => array(
                "description" => "Database user",
                "parameters" => array(
                    "username" => "Username for the connection",
                ),
            ),
            "password" => array(
                "description" => "Database pasword",
                "parameters" => array(
                    "password" => "Password for the connection",
                )
            )
        ));

    public function __construct(&$pdo = false, $connections = false)
    {
        $this->_conn_host_name = ReporticoApp::getConfig("db_host");
        $this->_conn_user_name = ReporticoApp::getConfig("db_user");
        $this->_conn_password = ReporticoApp::getConfig("db_password");
        $this->_conn_driver = ReporticoApp::getConfig("db_driver");
        $this->_conn_database = ReporticoApp::getConfig("db_database");
        $this->_conn_server = ReporticoApp::getConfig("db_server");
        $this->_conn_protocol = ReporticoApp::getConfig("db_protocol");

        $this->external_connection = &$pdo;
        $this->available_connections = &$connections;
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        switch ( $method ) {

            case "build":
                $builder = $args[0];
                $builder->store = [];

                $datasource = $builder->engine->datasource = new ReporticoDataSource();
                $builder->engine->datasource->builder = $builder;

                $builder->stepInto("datasource", $datasource, "\Reportico\Engine\ReporticoDataSource");
                return $builder;
                break;

        }
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args)
    {
        $exitLevel = false;
        switch ( $method ) {

            case "usage":
                echo $this->builderUsage("datasource");
                break;

            case "array":
                $this->builder->engine->datasource->driver = "array";
                $this->builder->engine->datasource->database = $args[0];
                //$this->builder->engine->datasource->connect(true);

                $invalid = false;
                if ( !is_array($args[0]) )
                    $invalid = true;
                else
                    $ct = 0;
                    foreach ( $args[0] as $k => $columns ){
                        if (!is_array($columns))
                            $invalid = true;
                        else{
                            if ( $ct == 0 )
                            foreach ($columns as $columnkey => $column) {
                                $this->builder->engine->createQueryColumn($columnkey, "", "", "", "", '####.###', true);
                            }
                        }
                        break;
                    }

                if ($invalid)
                    trigger_error("Array datasource requires array parameter in form [ [ 'col1' => 'val1', 'col2' => 'val2' ], [ 'col1' => 'val3', 'col2' => 'val4'  ], ", E_USER_ERROR);
                break;

            case "database":
                $this->builder->engine->datasource->connection_string = $args[0];
                break;

            case "user":
                $this->builder->engine->datasource->user_name = $args[0];
                break;

            case "password":
                $this->builder->engine->datasource->password = $args[0];
                break;

            case "end":
            default:
                $exitLevel = true;
                break;
        }

        if (!$exitLevel) {
            return $this;
        }

        return false;
    }


    public function setDetails($driver = "mysql", $host_name = "localhost",
        $service_name = "?Unknown?",
        $server = false, $protocol = false) {
        $this->driver = $driver;
        $this->host_name = $host_name;
        $this->service_name = $service_name;
        $this->protocol = $protocol;
        $this->server = $server;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
    }

    // Gets database specific represenation of encoding value based on project encoding
    public function getEncodingForDbDriver($in_db_driver, $in_encoding)
    {
        $out_encoding = $in_encoding;

        if (!$in_encoding || $in_encoding == "None") {
            return false;
        }

        // Get MySQL DB encoding value to use in "SET NAMES" SQL Command
        if ($in_db_driver == "pdo_mysql") {
            switch ($in_encoding) {
                case "LATIN1":$out_encoding = 'latin1';
                    break;
                case "GBK":$out_encoding = 'gbk';
                    break;
                case "GBK2312":$out_encoding = 'gbk2312';
                    break;
                case "UTF8":$out_encoding = 'utf8';
                    break;
                case "LATIN1":$out_encoding = 'latin1';
                    break;
                case "LATIN2":$out_encoding = 'latin2';
                    break;
                case "LATIN5":$out_encoding = 'latin5';
                    break;
                case "LATIN7":$out_encoding = 'latin7';
                    break;
                case "EUC_JP":$out_encoding = 'ujis';
                    break;
                case "EUC_KR":$out_encoding = 'euckr';
                    break;
                case "GBK":$out_encoding = 'gbk';
                    break;
                case "ISO_8859_7":$out_encoding = 'greek';
                    break;
                case "ISO_8859_8":$out_encoding = 'hebrew';
                    break;
                case "WIN1250":$out_encoding = 'cp1250';
                    break;
                case "WIN1251":$out_encoding = 'cp1251';
                    break;
                case "WIN1256":$out_encoding = 'cp1256';
                    break;
                case "WIN1257":$out_encoding = 'cp1257';
                    break;
                case "BIG5":$out_encoding = 'big5';
                    break;
                case "EUCJPMS":$out_encoding = 'eucjpms';
                    break;
                case "BINARY":$out_encoding = 'binary';
                    break;
                case "CP850":$out_encoding = 'cp850';
                    break;
                case "ARMSCII8":$out_encoding = 'armscii8';
                    break;
                case "ASCII":$out_encoding = 'ascii';
                    break;
                case "CP852":$out_encoding = 'cp852';
                    break;
                case "CP866":$out_encoding = 'cp866';
                    break;
                case "DEC8":$out_encoding = 'dec8';
                    break;
                case "GB2312":$out_encoding = 'gb2312';
                    break;
                case "GEOSTD8":$out_encoding = 'geostd8';
                    break;
                case "HP8":$out_encoding = 'hp8';
                    break;
                case "KEYBCS2":$out_encoding = 'keybcs2';
                    break;
                case "KOI8U":$out_encoding = 'koi8u';
                    break;
                case "MACCE":$out_encoding = 'macce';
                    break;
                case "MACROMAN":$out_encoding = 'macroman';
                    break;
                case "SWE7":$out_encoding = 'swe7';
                    break;
                case "TIS620":$out_encoding = 'tis620';
                    break;
                case "UCS2":$out_encoding = 'ucs2';
                    break;
                default:$out_encoding = $in_encoding;
            }
        }

        // Get PostgreSQL DB encoding value to use in "SET NAMES" SQL Command
        if ($in_db_driver == "pdo_pgsql") {
            switch ($in_encoding) {
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
                default:$out_encoding = false;
            }
        }

        if ($in_db_driver == "pdo_mssql") {
            switch ($in_encoding) {
                case "UTF8":$out_encoding = 'UTF-8';
                    break;
                default:$out_encoding = false;
            }
        }
        return $out_encoding;
    }

    public function mapColumnType($driver, $type)
    {
        $ret = $type;
        switch ($driver) {
            case "informix":
                switch ((int) $type) {
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
    public function pdoDriversAsString()
    {

        $drivers = PDO::getAvailableDrivers();
        return (implode($drivers, ","));
    }

    // Checks if request pdo driver exists
    public function pdoDriverExists($in_driver)
    {
        $drivers = PDO::getAvailableDrivers();
        $found = false;
        foreach ($drivers as $v) {
            if ($v == $in_driver) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    public function connect($ignore_config = false)
    {
        $connected = false;

        if ($this->connected) {
            $this->disconnect();
        }

        if ( $this->driver == "array" )
            $ignore_config = true;

        if ($ignore_config) {
            $this->_conn_driver = $this->driver;
            $this->_conn_user_name = $this->user_name;
            $this->_conn_password = $this->password;
            $this->_conn_host_name = $this->host_name;
            $this->_conn_database = $this->database;
            $this->_conn_server = $this->server;
            $this->_conn_protocol = $this->protocol;
        } else if (ReporticoApp::getConfig("db_connect_from_config")) {
            $this->_conn_driver = ReporticoApp::getConfig("db_driver");
            if (!$this->_conn_user_name) {
                $this->_conn_user_name = $this->user_name;
            }

            $this->_conn_password = ReporticoApp::getConfig("db_password");
            if (!$this->_conn_password) {
                $this->_conn_password = $this->password;
            }

            $this->_conn_host_name = ReporticoApp::getConfig("db_host");
            $this->_conn_database = ReporticoApp::getConfig("db_database");
            $this->_conn_server = ReporticoApp::getConfig("db_server");
            $this->_conn_protocol = ReporticoApp::getConfig("db_protocol");
        } else {
            $this->_conn_driver = $this->driver;
            $this->_conn_driver = ReporticoApp::getConfig("db_driver");
            $this->_conn_user_name = $this->user_name;
            $this->_conn_password = $this->password;
            $this->_conn_host_name = $this->host_name;
            $this->_conn_database = ReporticoApp::getConfig("db_database");
            $this->_conn_server = ReporticoApp::getConfig("db_server");
            $this->_conn_protocol = ReporticoApp::getConfig("db_protocol");
        }

        if ($this->_conn_driver == "none") {
            $connected = true;
        }

	// Plugin specified look in config.php for plugin details in "Plugins->Datasources"
	// then instantite the class mentioned and pass in the credentials
        $dbtype = ReporticoApp::getConfig("db_type", false);
        if ($dbtype && $dbtype == "plugin" ) {

            $db_driver = ReporticoApp::getConfig("db_driver", false);
	    include_once(__DIR__."/adodb_elasticsearch.php");

	    $sources = ReporticoApp::get("plugins");
	    if ( !$sources ) {
		    die ("Plugin $db_driver specified but no plugin section exists in the project config.php");
	    }

	    if ( !isset($sources["Datasources"]["$db_driver"]) ) {
		    die ("Plugin $db_driver is not an element of the [\"plugins\"][\"Datasources\"] array in the project config.php");
	    }
	    	
	    $datasource = $sources["Datasources"][$db_driver];
	    if ( !isset($datasource["class"] ) ){
		    die ("Plugin $db_driver in the project config.php does not specify a class");
	    }

	    $x = new \Reportico\Engine\DataSourceElastic($datasource);

	    $class = $sources["Datasources"][$db_driver]["class"];
            $this->ado_connection = new $class($datasource);
	    $connected = $this->ado_connection->Connect();

            return $this->connected;
        }


        if ($this->external_connection) {
            $this->ado_connection = NewADOConnection("pdo");
            $this->ado_connection->_connectionID = $this->external_connection;
            if ($this->ado_connection->_connectionID) {
                switch(ADODB_ASSOC_CASE){
                case 0: $m = PDO::CASE_LOWER; break;
                case 1: $m = PDO::CASE_UPPER; break;
                default:
                case 2: $m = PDO::CASE_NATURAL; break;
                }

                $this->ado_connection->_connectionID->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT );
                //$this->ado_connection->_connectionID->setAttribute(PDO::ATTR_CASE,$m);

                $class = 'ADODB_pdo_'.$this->ado_connection->dsnType;

                //$this->ado_connection->_connectionID->setAttribute(PDO::ATTR_AUTOCOMMIT,true);
                switch($this->ado_connection->dsnType) {
                case 'oci':
                case 'mysql':
                case 'pgsql':
                case 'mssql':
                    include_once(ADODB_DIR.'/drivers/adodb-pdo_'.$this->ado_connection->dsnType.'.inc.php');
                    break;
                }
                //include_once("/var/www/laravel55/vendor/reportico/laravel-experiment/src/Reportico/Laravel/reportico_adodb/drivers/adodb-pdo.inc.php");
                //if (class_exists($class,false))
                    //$this->ado_connection->_driver = new $class();
                //else
                    $this->ado_connection->_driver = new \ADODB_pdo_base();
    
                $this->ado_connection->_driver->_connectionID = $this->ado_connection->_connectionID;
                $this->ado_connection->_UpdatePDO();
                return true;
            }

            $this->ado_connection->_driver->_connectionID = $this->_connectionID;
            $this->ado_connection->_UpdatePDO();




            //$this->ado_connection->ConnectExisting($this->external_connection);
            $this->connected = true;
            return $this->connected;
        }

        $dbtype = ReporticoApp::getConfig("db_type", false);
        if ($dbtype && $dbtype == "existingconnection" && !$this->external_connection) {
            ReporticoApp::handleError("Project defined to use existing connection but none set.");
            return false;
        }

        if ($dbtype && preg_match("/^byname_/", $dbtype)) {
            $connection_name = preg_replace("/byname_/", "", $dbtype);
            if (!isset($this->available_connections[$connection_name])) {
                ReporticoApp::handleError("Connection name \"$connection \" not found in framework connection set");
                return false;
            }
            $useConnection = $this->available_connections[$connection_name];
            switch ($useConnection["driver"]) {
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

            if (isset($useConnection["host"])) {
                $this->_conn_host_name = $useConnection["host"];
            }

            if (isset($useConnection["database"])) {
                $this->_conn_database = $useConnection["database"];
            }

            if (isset($useConnection["username"])) {
                $this->_conn_user_name = $useConnection["username"];
            }

            if (isset($useConnection["password"])) {
                $this->_conn_password = $useConnection["password"];
            }

        }

        $sessionClass = ReporticoSession();
        $this->connection_string = $sessionClass::registerSessionParam("passedDatabaseConnectionString", $this->connection_string);
        if ( $this->connection_string ) {
            $this->user_name = $sessionClass::registerSessionParam("passedDatabaseUserName", $this->user_name);
            $this->password = $sessionClass::registerSessionParam("passedDatabasePassword", $this->password);

            $connected = false;
            if (class_exists('PDO', false)) {
                if (!$this->pdoDriverExists("mysql")) {
                    trigger_error("PDO driver \"mysql\" not found. Available drivers are " . $this->pdoDriversAsString(), E_USER_NOTICE);
                } else {
                    $this->ado_connection = NewADOConnection("pdo");

                    $connected = $this->ado_connection->Connect($this->connection_string, $this->user_name, $this->password);
                }
            } else {
                ReporticoApp::handleError("Attempt to connect to MySQL Database Failed. PDO Support does not seem to be Available");
            }
            $this->connected = $connected;

            return $this->connected;

        }

        switch ($this->_conn_driver) {
            case "none":
                $connected = true;
                break;

            case "array":
                $this->ado_connection = new DataSourceArray();
                $this->ado_connection->Connect($this->_conn_database);
                $connected = true;
                break;

            case "mysql":
                $this->ado_connection = NewADOConnection($this->_conn_driver);
                $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
                $connected = $this->ado_connection->Connect($this->_conn_host_name,
                    $this->_conn_user_name, $this->_conn_password, $this->_conn_database);
                break;

            case "informix":
                $this->ado_connection = NewADOConnection($this->_conn_driver);
                $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
                if (function_exists("ifx_connect")) {
                    $connected = $this->ado_connection->Connect($this->_conn_host_name,
                        $this->_conn_user_name, $this->_conn_password, $this->_conn_database);
                } else {
                    ReporticoApp::handleError("Attempt to connect to Informix Database Failed. Informix PHP Driver is not Available");
                }

                break;

            case "pdo_mssql":
                if (class_exists('PDO', false)) {
                    if ($this->pdoDriverExists("dblib")) {
                        $this->ado_connection = NewADOConnection("pdo");
                        $cnstr =
                        "dblib:" .
                        "host=" . $this->_conn_host_name . "; " .
                        "username=" . $this->_conn_user_name . "; " .
                        "password=" . $this->_conn_password . "; " .
                        "dbname=" . $this->_conn_database;
                        if ($dbenc = $this->getEncodingForDbDriver($this->_conn_driver, ReporticoApp::getConfig("db_encoding"))) {
                            $cnstr .= ";CharacterSet=" . $dbenc;
                        }

                        $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                    } else
                    if ($this->pdoDriverExists("mssql")) {
                        $this->ado_connection = NewADOConnection("pdo");
                        $cnstr =
                        "mssql:" .
                        "host=" . $this->_conn_host_name . "; " .
                        "username=" . $this->_conn_user_name . "; " .
                        "password=" . $this->_conn_password . "; " .
                        "dbname=" . $this->_conn_database;

                        if ($dbenc = $this->getEncodingForDbDriver($this->_conn_driver, ReporticoApp::getConfig("db_encoding"))) {
                            $cnstr .= ";CharacterSet=" . $dbenc;
                        }

                        $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                    } else {
                        trigger_error("PDO driver for mssql not found. Drivers \"dblib\" and \"mssql\" not found. Available drivers are " . $this->pdoDriversAsString(), E_USER_NOTICE);
                    }
                } else {
                    ReporticoApp::handleError("Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "pdo_mssql":
                if (class_exists('PDO', false)) {
                    $this->ado_connection = NewADOConnection("pdo");
                    $cnstr =
                    "dblib:" .
                    "host=" . $this->_conn_host_name . "; " .
                    "username=" . $this->_conn_user_name . "; " .
                    "password=" . $this->_conn_password . "; " .
                    "dbname=" . $this->_conn_database;
                    $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                } else {
                    ReporticoApp::handleError("Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "pdo_sqlsrv":
                if (class_exists('PDO', false)) {

                    $this->ado_connection = NewADOConnection("pdo");
                    if ($this->_conn_protocol) {
                        $cnstr = "sqlsrv:" . "Server=" . $this->_conn_host_name . "," . $this->_conn_protocol . "; " . "Database=" . $this->_conn_database;
                    } else {
                        $cnstr = "sqlsrv:" . "Server=" . $this->_conn_host_name . "; " . "Database=" . $this->_conn_database;
                    }

                    $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                } else {
                    ReporticoApp::handleError("Attempt to connect to MSSQL Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "oci8":
                $this->ado_connection = NewADOConnection($this->_conn_driver);
                $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
                $connected = $this->ado_connection->Connect($this->_conn_host_name,
                    $this->_conn_user_name, $this->_conn_password, $this->_conn_database);
                break;

            case "pdo_oci":
                if (class_exists('PDO', false)) {
                    if (!$this->pdoDriverExists("oci")) {
                        trigger_error("PDO driver \"oci\" not found. Available drivers are " . $this->pdoDriversAsString(), E_USER_NOTICE);
                    } else {
                        $this->ado_connection = NewADOConnection("pdo");
                        if ($this->_conn_protocol) {
                            $cnstr = "oci:" . "dbname=//" . $this->_conn_host_name . ":" . $this->_conn_protocol . "/" . $this->_conn_database;
                        } else {
                            $cnstr = "oci:" . "dbname=//" . $this->_conn_host_name . "/" . $this->_conn_database;
                        }

                        if ($dbenc = $this->getEncodingForDbDriver($this->_conn_driver, ReporticoApp::getConfig("db_encoding"))) {
                            $cnstr .= ";charset=" . $dbenc;
                        }

                        $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                    }
                } else {
                    ReporticoApp::handleError("Attempt to connect to Oracle Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "pdo_pgsql":
                if (class_exists('PDO', false)) {
                    if (!$this->pdoDriverExists("pgsql")) {
                        trigger_error("PDO driver \"pgsql\" not found. Available drivers are " . $this->pdoDriversAsString(), E_USER_NOTICE);
                    } else {
                        $this->ado_connection = NewADOConnection("pdo");
                        $hostarr = explode(":", $this->_conn_host_name);
                        if (count($hostarr) > 1) {
                            $cnstr =
                            "pgsql:" .
                            "host=" . $hostarr[0] . "; " .
                            "port=" . $hostarr[1] . "; " .
                            "user=" . $this->_conn_user_name . "; " .
                            "password=" . $this->_conn_password . "; " .
                            "dbname=" . $this->_conn_database;
                        } else {
                            $cnstr =
                            "pgsql:" .
                            "host=" . $hostarr[0] . "; " .
                            "user=" . $this->_conn_user_name . "; " .
                            "password=" . $this->_conn_password . "; " .
                            "dbname=" . $this->_conn_database;
                        }
                        $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);

                        if ($connected) {
                            if ($dbenc = $this->getEncodingForDbDriver($this->_conn_driver, ReporticoApp::getConfig("db_encoding"))) {
                                $this->ado_connection->Execute("set names '" . $dbenc . "'");
                            }

                        }
                    }
                } else {
                    ReporticoApp::handleError("Attempt to connect to PostgreSQL Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "pdo_mysql":
                if (class_exists('PDO', false)) {
                    if (!$this->pdoDriverExists("mysql")) {
                        trigger_error("PDO driver \"mysql\" not found. Available drivers are " . $this->pdoDriversAsString(), E_USER_NOTICE);
                    } else {
                        $this->ado_connection = NewADOConnection("pdo");

                        // Extract port from host if necessary
                        $hostarr = explode(":", $this->_conn_host_name);
                        if (count($hostarr) > 1) {
                            $cnstr =
                            "mysql:" .
                            "host=" . $hostarr[0] . "; " .
                            "port=" . $hostarr[1] . "; " .
                            //"username=".$this->_conn_user_name."; ".
                            //"password=".$this->_conn_password."; ".
                            "dbname=" . $this->_conn_database;
                        } else {
                            $cnstr =
                            "mysql:" .
                            "host=" . $this->_conn_host_name . "; " .
                                           //"username=".$this->_conn_user_name."; ".
                                           //"password=".$this->_conn_password."; ".
                            "dbname=" . $this->_conn_database;
                        }
                        $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);

                        if ($connected) {
                            if ($dbenc = $this->getEncodingForDbDriver($this->_conn_driver, ReporticoApp::getConfig("db_encoding"))) {
                                $this->ado_connection->Execute("set names '" . $dbenc . "'");
                            }

                        }
                    }
                } else {
                    ReporticoApp::handleError("Attempt to connect to MySQL Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "pdo_sqlite3":
                if (class_exists('PDO', false)) {
                    $this->ado_connection = NewADOConnection("pdo");
                    $cnstr = "sqlite:" . $this->_conn_database;
                    $connected = $this->ado_connection->Connect($cnstr, '', '');
                } else {
                    ReporticoApp::handleError("Attempt to connect to SQLite-3 Database Failed. PDO Driver is not Available");
                }

                break;

            case "sqlite":
                $driver = 'sqlite';
                $database = $this->_conn_host_name . $this->_conn_database;
                $query = 'select * from Chave';
                $db = ADONewConnection($driver);
                if ($db && $db->PConnect($database, "", "", "")) {
                } else {
                    die("* CONNECT TO SQLite-2 FAILED");
                }
                break;

            case "pdo_informix":
                if (class_exists('PDO', false)) {
                    $this->ado_connection = NewADOConnection("pdo");
                    $cnstr =
                    "informix:" .
                    "host=" . $this->_conn_host_name . "; " .
                    "server=" . $this->_conn_server . "; " .
                    "protocol=" . $this->_conn_protocol . "; " .
                    "username=" . $this->_conn_user_name . "; " .
                    "password=" . $this->_conn_password . "; " .
                    "database=" . $this->_conn_database;
                    $connected = $this->ado_connection->Connect($cnstr, $this->_conn_user_name, $this->_conn_password);
                } else {
                    ReporticoApp::handleError("Attempt to connect to Informix Database Failed. PDO Support does not seem to be Available");
                }

                break;

            case "odbc":
                $this->ado_connection = NewADOConnection($this->_conn_driver);
                $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
                $connected = $this->ado_connection->Connect($this->_conn_host_name,
                    $this->_conn_user_name, $this->_conn_password);
                break;

            case "unknown":
                ReporticoApp::handleError("Database driver of unknown specified - please configure your project database connectivity");
                break;

            case "SW_FRAMEWORK_DB_DRIVER":
                ReporticoApp::handleError("You have configured your project to connnect to a database held within a web framework or Content Management System. You need to set constants SW_FRAMEWORK_DB_DRIVER, SW_FRAMEWORK_DB_USER,SW_FRAMEWORK_DB_PASSWORD,SW_FRAMEWORK_DB_HOST,SW_FRAMEWORK_DB_DATABASE from within the calling framework in order to run in this way. You are probably not trying to run from within a framework");
                break;

            default:
                $this->ado_connection = NewADOConnection($this->_conn_driver);
                if ($this->ado_connection) {
                    $this->ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
                    $connected = $this->ado_connection->Connect($this->_conn_host_name,
                        $this->_conn_user_name, $this->_conn_password, $this->_conn_database);
                }
        }

        // Note force connected for SQLite3
        if ($this->_conn_driver == "sqlite") {
            $connected = true;
        } else {
            if ($this->ado_connection && !$connected && $this->_conn_driver != "unknown") {
                ReporticoApp::handleError("Error in Connection to $this->_conn_driver database :" . $this->ado_connection->ErrorMsg());
            }

        }

        $this->connected = $connected;
        return $this->connected;
    }

    public function disconnect()
    {
        if ($this->connected && $this->_conn_driver != "none") {
            $this->ado_connection->Close();
        }

        $this->connected = false;
    }
}
