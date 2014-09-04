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
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
global $g_session_namespace;
global $g_session_namespace_key;
$g_session_namespace = false;
$g_session_namespace_key = "reportico";

set_up_reportico_session();

if ( $g_session_namespace )
    $g_session_namespace_key = "reportico_".$g_session_namespace;

if ( !function_exists("set_project_environment" ) )
{
/**
 * Function set_project_environment
 *
 * Analyses configuration and current session to identify which project area
 * is to be used. 
 * If a project is specified in the HTTP parameters then that is used, otherwise
 * the current SESSION project is used. If none of these are specified then the default
 * "reports" project is used
 */
function set_project_environment()
{
	global $g_project;
	global $g_menu;
	global $g_menu_title;

	$project = session_request_item("project", "reports");
	$menu = false;
	$menu_title = "Set Menu Title";

	// Now we now the project include the relevant config.php
	$projpath = "projects/".$project;
	$configfile = $projpath."/config.php";
	$menufile = $projpath."/menu.php";

	if ( !is_file($projpath) )
		find_file_to_include($projpath, $projpath);

	if ( !$projpath )
	{
		find_file_to_include("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		$g_menu_title = "";
		$old_error_handler = set_error_handler("ErrorHandler");
		handle_error("Project Directory $project not found. Check INCLUDE_PATH or project name");
		return;
	}
	
	if ( !is_file($configfile) )
		find_file_to_include($configfile, $configfile);
	if ( !is_file($menufile) )
		find_file_to_include($menufile, $menufile);
	
	if ( $configfile )
	{
		include_once($configfile);
		if ( is_file($menufile) )
			include_once($menufile);
		else
			handle_error("Menu Definition file menu.php not found in project $project", E_USER_WARNING);
	}
	else
	{
		find_file_to_include("config.php", $configfile);
		if ( $configfile )
			include_once($configfile);
		$g_project = false;
		$g_menu = false;
		$g_menu_title = "";
		$old_error_handler = set_error_handler("ErrorHandler");
		handle_error("Configuration Definition file config.php not found in project $project", E_USER_ERROR);
	}

	$g_project = $project;
	$g_menu = $menu;
	$g_menu_title = $menu_title;
	return $project;
}
}

set_project_environment();
$datasource = new reportico_datasource();
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
