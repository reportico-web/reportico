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

 * File:        run.php
 *
 * Reportico runner script
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: run.php,v 1.25 2014/05/17 15:12:31 peter Exp $
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
	//$q->allow_debug = true;

    // Specify any URL parameters that should be added into any links generated in Reportico.
    // Useful when embedding in another application or frameworks where requests need to be channelled
    // back though themselves
	//$q->forward_url_get_parameters = "";

    // Reportico Ajax mode. If set to true will run all reportico requests from buttons and links
    // through AJAX, meaning reportico will refresh in its own window and not refresh the whole page
    //$q->reportico_ajax_mode = true;

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
    // report output or report design mode, use respectively ( "ADMIN", "MENU", "PREPARE", "EXECUTE", "MAINTAIN")
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

    // Set source SQL to generate report from, without requirement for report , requires an initial_project to be defined for connection details
    //$q->initial_sql = "SELECT column1 AS columntitle1, column2 AS columntitle2 FROM table";

    // Set Report Title  when running reort from an SQL statement above
    // $q->set_attribute("ReportTitle", "Report Title");

    // Specify access mode to limit what user can do, one of :-
    // FULL - the default, allows user to log in under admin/design mode and design reports
    // ALLPROJECTS - allows entry to admin page to select project  but no ability to logon in admin/designer mode
    // ONEPROJECT - allows entry to a single project and no access to the admin page
    // ONEREPORT - limits user to single report, crtieria entry and report execution ( requires initial project/report )
    // REPORTOUTPUT - executes a report and allows to "Return" button to crtieria entry ( requires initial project/report )
    //$q->access_mode = "<MODE>";

    // Generate report definition from SQL  and set some column / report attributes
    // Also the full report definition can be built up programmatically
    // which requires further doicumentation
    //$q->importSQL("SELECT column1 AS columntitle1, column2 AS columntitle2 FROM table");
    //$q->get_column("column1")->set_attribute("column_display","hide");
    //$q->get_column("column1")->set_attribute("column_title","Custom Title");
    //$q->set_attribute("ReportTitle","New Report Title");


    // Default initial execute mode to single report output if REPORTOUTPUT mode specified
    if ( $q->access_mode == "REPORTOUTPUT" )
        $q->initial_execute_mode = "EXECUTE";


    // Provide an existing connection to Reportico, at the moment to use this there still needs to be project
    // in existence, but the connection specified here will override the 
    // this allows you build create temporary tables and perform other actions prior to reporting
    // $q->external_connection = false;
    // try 
    // {
            // $q->external_connection = new PDO("mysql:host=localhost; dbname=dbname", "username", "password" );
    // }
    // catch ( PDOException $ex )
    // {
            // $q->external_connection = false;
            // // Handle Error
    // }

    // Specify alternate path to projects folder, templates_c folder
    //$q->projects_folder = "projects";
    //$q->admin_projects_folder = "projects";
    //$q->compiled_templates_folder = "templates_c";
    
    // For setting report criteria parameters.. use the criteria name as the key and the criteria value
    // $q->initial_execution_parameters = array();
    // $q->initial_execution_parameters["lookupcriteria"] = "value1,value2";
    // $q->initial_execution_parameters["datecriteria"] = "2014-07-01";
    // $q->initial_execution_parameters["datecriteria2"] = "TODAY";
    // $q->initial_execution_parameters["daterangecriteria1"] = "2014-01-01-2014-02-01";
    // $q->initial_execution_parameters["daterangecriteria2"] = "FIRSTOFMONTH-LASTOFMONTH";

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

    // Set to true if you want to clear the report session whenever you call this script
    // $q->clear_reportico_session = true;

    // Specify an alternative AJAX runner from the stanfdard run.php
    //$q->reportico_ajax_script_url = $_SERVER["SCRIPT_NAME"];


    // If you want to connect to a reporting database whose connection information is available in the calling
    // script, then you should configure your project connection type to "framework" using the configure project link
    //and then you can pass your connection info here
    //define('SW_FRAMEWORK_DB_DRIVER','pdo_mysql');
    //define('SW_FRAMEWORK_DB_USER', '<USER>');
    //define('SW_FRAMEWORK_DB_PASSWORD','PASSWORD');
    //define('SW_FRAMEWORK_DB_HOST', '127.0.0.1'); // Use ip:port to specifiy a non standard port
    //define('SW_FRAMEWORK_DB_DATABASE', '<DATABASENAME>');

    // For passing external user parameters, can be referenced in SQL with {USER_PARAM,parameter_name}
    // and can be referenced in custom SQL with $this->user_parameters
    //$q->user_parameters["your_parameter_name"] = "your parameter value";

    // Jquery already included?
    //$q->jquery_preloaded = false;

    // Bootstrap Features
    // Set bootstrap_styles to false for reportico classic styles, or "3" for bootstrap 3 look and feel and 2 for bootstrap 2
    // If you are embedding reportico and you have already loaded bootstrap then set bootstrap_preloaded equals true so reportico
    // doestnt load it again.
    //$q->bootstrap_styles = "3";
    //$q->bootstrap_preloaded = false;

    // In bootstrap enable pages, the bootstrap modal is by default used for the quick edit buttons
    // but they can be ignored and reportico's own modal invoked by setting this to true
    //$q->force_reportico_mini_maintains = false;

    // Engine to use for charts .. 
    // HTML reports can use javascript charting, PDF reports must use PCHART
    //$q->charting_engine = "PCHART";
    //$q->charting_engine_html = "NVD3";

    // Whether to turn on dynamic grids to provide searchable/sortable reports
    // $q->dynamic_grids = true;
    // $q->dynamic_grids_sortable = true;
    // $q->dynamic_grids_searchable = true;
    // $q->dynamic_grids_paging = false;
    // $q->dynamic_grids_page_size = 10;

    // Show or hide various report elements
    //$q->output_template_parameters["show_hide_navigation_menu"] = "show";
    //$q->output_template_parameters["show_hide_dropdown_menu"] = "show";
    //$q->output_template_parameters["show_hide_report_output_title"] = "show";
    //$q->output_template_parameters["show_hide_prepare_section_boxes"] = "show";
    //$q->output_template_parameters["show_hide_prepare_pdf_button"] = "show";
    //$q->output_template_parameters["show_hide_prepare_html_button"] = "show";
    //$q->output_template_parameters["show_hide_prepare_print_html_button"] = "show";
    //$q->output_template_parameters["show_hide_prepare_csv_button"] = "show";
    //$q->output_template_parameters["show_hide_prepare_page_style"] = "show";

    // Label for criteria section if required
    // $q->criteria_block_label = "Report Criteria:";

    // Static Menu definition
    // ======================
    // identifies the items that will show in the middle of the project menu page.
    // If not set will use the project level menu definitions in project/projectname/menu.php
    // To have no static menu ( for example if you just want to use a drop down then set to empty array )
    // To define a static menu, follow the example here.
    // report can be a valid report file ( without the xml suffix ).
    // If title is left as AUTO then the title will be taken form the report definition
    // Use title of BLANKLINE to separate items and LINE to draw a horizontal line separator

    //$q->static_menu = array (
	    //array ( "report" => "an_xml_reportfile1", "title" => "<AUTO>" ),
	    //array ( "report" => "another_reportfile", "title" => "<AUTO>" ),
	    //array ( "report" => "", "title" => "BLANKLINE" ),
	    //array ( "report" => "anotherfreportfile", "title" => "Custom Title" ),
	    //array ( "report" => "", "title" => "BLANKLINE" ),
	    //array ( "report" => "andanother", "title" => "Another Custom Title" ),
	//);

    // To auto generate a static menu from all the xml report files in the project use
    //$q->static_menu = array ( array ( "report" => ".*\.xml", "title" => "<AUTO>" ) );
    
    // To hide the static report menu
    //$q->static_menu = array ();

    // Required PDF Engine set -- to tcpdf ( default ) or fpdf 
    //$q->pdf_engine = "tcpdf";

    // Dropdown Menu definition
    // ========================
    // Menu items for the drop down menu
    // Enter definition for the the dropdown menu options across the top of the page
    // Each array element represents a dropdown menu across the page and sub array items for each drop down
    // You must specifiy a project folder for each project entry and the reportfile definitions must point to a valid xml report file
    // within the specified project
    //$q->dropdown_menu = array(
    //                array ( 
    //                    "project" => "projectname",
    //                    "title" => "dropdown menu 1 title",
    //                    "items" => array (
    //                        array ( "reportfile" => "report" ),
    //                        array ( "reportfile" => "anotherreport" ),
    //                        )
    //                    ),
    //                array ( 
    //                    "project" => "projectname",
    //                    "title" => "dropdown menu 2 title",
    //                    "items" => array (
    //                        array ( "reportfile" => "report" ),
    //                        array ( "reportfile" => "anotherreport" ),
    //                        )
    //                    ),
    //            );


    // Run the report
	$q->execute();

	//ob_end_flush();

?>
