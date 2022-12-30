<?php
/*

 * File:        run.php
 *
 * Reportico ajax runner script
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: run.php,v 1.25 2014/05/17 15:12:31 peter Exp $
 */
if ( file_exists(__DIR__ .'/vendor/autoload.php') )
    require_once(__DIR__ .'/vendor/autoload.php');
else
    require_once(__DIR__ .'/../../../vendor/autoload.php');



// set error reporting level
error_reporting(E_ALL);

// Set the timezone according to system defaults
date_default_timezone_set(@date_default_timezone_get());

// Reserver 100Mb for running
ini_set("memory_limit","100M");

// Allow a good time for long reports to run. Set to 0 to allow unlimited time
ini_set("max_execution_time","90");

// Instantiate Reportico
$q = new Reportico\Engine\Reportico();

// Setup SESSION
Reportico\Engine\ReporticoSession::setUpReporticoSession($q->session_namespace);

// Run the report
$q->execute();

?>
