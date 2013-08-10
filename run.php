<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2013 Peter Deed

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

 * File:        run.php
 *
 * Reportico runner script
 * !!! Note this script will run reports in FULL design mode
 * !!! This means that users of the reports will be able to 
 * !!! modify reports and save those modifications
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2013 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: run.php,v 1.14 2013/08/08 18:20:39 peter Exp $
 */

    // set error reporting level
	error_reporting(E_ALL);

    // Set the timezone according to system defaults
    date_default_timezone_set(@date_default_timezone_get());

    // Reserver 100Mb for running
	ini_set("memory_limit","100M");

    // Allow a good time for long reports to run. Set to 0 to allow unlimited time
	ini_set("max_execution_time","90");

    // Include Reportico - for embedding reportico in a script running from outside the Reportico directory, 
    // just include the full path to the file reportico.php
	//require_once('<FULL_PATH_TO_REPORTICO>/reportico.php');
	require_once('reportico.php');

    // Only turn on output buffering if necessary, normally leave this uncommented
	//ob_start();

	$q = new reportico();

    // In design mode, allow sql debugging
	$q->allow_debug = true;

    // Specify any URL parameters that should be added into any links generated in Reportico.
    // Useful when embedding in another application or frameworks where requests need to be channelled
    // back though themselves
	//$q->forward_url_get_parameters = "";

    // Reportico Ajax mode. If set to true will run all reportico requests from buttons and links
    // through AJAX, meaning reportico will refresh in its own window and not refresh the whole page
    //$q->reportico_ajax_mode = false;

    /*
    ** Initial execution states .. allows you to start user and limit user to specfic
    ** report menus, reports or report output
    ** The default behaviour is to show the Administration page on initial startup
    */

    // Start user in specific project
    //$q->initial_project = "<project>";          

    // If starting user in specific project then project passweord is required if one exists
    // and you dont want user to have to type it in
    //$q->initial_project_password = "<project password>";

    // Specify a report to start user in specify the xml report file in the specified project folder
    //$q->initial_report = "reportfile.xml";

    // Specify whether user is started in administration page, project menu, report criteria entry, 
    // report output or report design mode, use respectively ( "ADMIN", "u$q->get_execute_mode(), true);ENU", "PREPARE", "EXECUTE", "MAINTAIN")
    // default is "ADMIN"
    //$q->initial_execute_mode = "<MODE>";

    // When only executing a report, indicates what format it should be showed in .. HTML(the default), PDF or CSV
    //$q->initial_output_format = "HTML";

    // When initial mode is report criteria entry or execution, these set the flags for whether report detail, group hears, columns headers
    // etc are to be show. For example you might only want to run a report and show the graphs, by default all show except criteria
    //$q->initial_show_detail = "show";
    //$q->initial_show_graph = "show";
    //$q->initial_show_group_headers = "show";
    //$q->initial_show_group_trailers = "show";
    //$q->initial_show_column_headers = "show";
    //$q->initial_show_criteria = "show";

    // Set default output style - TABLE = one row per record, FORM = one page per record
    //$q->initial_output_style = "TABLE";

    // Specify access mode to limit what user can do, one of :-
    // FULL - the default, allows user to log in under admin/design mode and design reports
    // ALLPROJECTS - allows entry to admin page to select project  but no ability to logon in admin/designer mode
    // ONEPROJECT - allows entry to a single project and no access to the admin page
    // ONEREPORT - limits user to single report, crtieria entry and report execution ( requires initial project/report )
    // REPORTOUTPUT - executes a report and allows to "Return" button to crtieria entry ( requires initial project/report )
    //$q->access_mode = "<MODE>";

    // Default initial execute mode to single report output if REPORTOUTPUT mode specified
    if ( $q->access_mode == "REPORTOUTPUT" )
        $q->initial_execute_mode = "EXECUTE";

    // The session namespace to use. Only relevant when showing more than one report in a single page. Specify a name
    // to store all session variables for this instance and then when running another report instance later in the script 
    // use another name
    //$q->session_namespace = "namespace";

    // Current user - when embedding reportico, you may wish to run queries by user. In this case
    // set the current user here. Then you can use the construct {FRAMEWORK_USER} within your queries
	//$q->external_user = "<CURRENT USER>";

    // Indicates whether report output should include a refresh button
    //$q->show_refresh_button = false;

    // Set to true if you are embedding in another report
    //$q->embedded_report = false;


    // If you want to connect to a reporting database whose connection information is available in the calling
    // script, then you should configure your project connection type to "framework" and then you can pass your
    // connection info here
    //define('SW_FRAMEWORK_DB_DRIVER','pdo_mysql');
    //define('SW_FRAMEWORK_DB_USER', '<USER>');
    //define('SW_FRAMEWORK_DB_PASSWORD','PASSWORD');
    //define('SW_FRAMEWORK_DB_HOST', '127.0.0.1'); // Use ip:port to specifiy a non standard port
    //define('SW_FRAMEWORK_DB_DATABASE', '<DATABASENAME>');

    // For passing external user parameters, can be referenced in SQL with {USER_PARAM,parameter_name}
    // and can be referenced in custom SQL with $this->user_parameters
    //$q->user_parameters["your_parameter_name"] = "your parameter value";

    // Run the report
	$q->execute();

	//ob_end_flush();
?>
