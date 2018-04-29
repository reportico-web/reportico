<?php
/*
 * File:        imageget.php
 *
 * Script to take location of an image within the database
 * and output it to the browser. Used by the Reportico engine
 * as a URL that can be embeeded in report browser output.
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: imageget.php,v 1.13 2014/05/17 15:12:31 peter Exp $
 */


include_once('reportico_adodb/adodb.inc.php');
include_once ("reportico.php");
include_once ("swutil.php");
include_once ("swdb.php");

error_reporting(E_ALL);


// Session namespace for allowing multiple reporticos on a single 
// page when called from a framework. In name space in operation the
// session array index to find reportico variables can be found in "reportico"
// otherwise it's reportic_<namespace>
ReporticoApp::set("session_namespace",  false);
ReporticoApp::set("session_namespace_key",  "reportico");



setUpReporticoSession();

if ( ReporticoApp::get("session_namespace") )
    ReporticoApp::set("session_namespace_key",  "reportico_".ReporticoApp::get("session_namespace") );



if ( !function_exists("setProjectEnvironment" ) )
{
/**
 * Function setProjectEnvironment
 *
 * Analyses configuration and current session to identify which project area
 * is to be used. 
 * If a project is specified in the HTTP parameters then that is used, otherwise
 * the current SESSION project is used. If none of these are specified then the default
 * "reports" project is used
 */
function setProjectEnvironment()
{
	global $g_project;
	global $g_menu;
	//global $g_menu_title;

	$project = sessionRequestItem("project", "reports");
	$menu = false;
	$menu_title = "Set Menu Title";

	// Now we now the project include the relevant config.php
	$projpath = "projects/".$project;
	$configfile = $projpath."/config.php";
	$menufile = $projpath."/menu.php";

	if ( !is_file($projpath) )
		findFileToInclude($projpath, $projpath);

	if ( !$projpath )
	{
		findFileToInclude("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		//$g_menu_title = "";
		ReporticoApp::set('menu_title','');
		$old_error_handler = set_error_handler("ErrorHandler");
		handleError("Project Directory $project not found. Check INCLUDE_PATH or project name");
		return;
	}
	
	if ( !is_file($configfile) )
		findFileToInclude($configfile, $configfile);
	if ( !is_file($menufile) )
		findFileToInclude($menufile, $menufile);
	
	if ( $configfile )
	{
		include_once($configfile);
		if ( is_file($menufile) )
			include_once($menufile);
		else
			handleError("Menu Definition file menu.php not found in project $project", E_USER_WARNING);
	}
	else
	{
		findFileToInclude("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		//$g_menu_title = "";
		ReporticoApp::set('menu_title','');
		$old_error_handler = set_error_handler("ErrorHandler");
		handleError("Configuration Definition file config.php not found in project $project", E_USER_ERROR);
	}

	$g_project = $project;
	$g_menu = $menu;
	//$g_menu_title = $menu_title;
	ReporticoApp::set('menu_title',$$menu_title);
	return $project;
}
}

setProjectEnvironment();
$datasource = new ReporticoDatasource();
$datasource->connect();

$imagesql = $_REQUEST["imagesql"];
if ( !preg_match("/^select/i", $imagesql ) )
    return false;

$rs = $datasource->ado_connection->Execute($imagesql) 
    or die("Query failed : " . $ado_connection->ErrorMsg());
$line = $rs->FetchRow();

//header('Content-Type: image/gif');
foreach ( $line as $col )
{
    $data = $col;
    break;
}
echo $data;


?>
