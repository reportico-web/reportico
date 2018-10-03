<?php
/*

 * File:        partial.php
 *
 * Reportico runner script
 * !!! Note this script will run reports in FULL design mode
 * !!! This means that users of the reports will be able to 
 * !!! modify reports and save those modifications
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: partial.php,v 1.9 2014/05/17 15:12:31 peter Exp $
 */

	error_reporting(E_ALL);
    date_default_timezone_set(@date_default_timezone_get());

	ini_set("memory_limit","100M");

	//ob_start();
	require_once('reportico.php');
	$q = new Reportico();
	$q->allow_debug = true;
	$q->forward_url_get_parameters = "";
	$q->embedded_report = true;
	$q->execute($q->get_execute_mode(), true);
	//ob_end_flush();
?>
