<?php

// Include files
require_once('swutil.php');

// Set up globals
//$g_menu = false;
//$g_admin_menu = false;
//$g_menu_title = false;
//\Reportico\ReporticoApp::set('menu_title', false);
//$g_dropdown_menu = false;
//$g_translations = false;
//$g_locale = false;
//$g_report_desc = false;

// Defines external plugin parameters
//global $g_external_param1;   // Values passed form calling framworks
//global $g_external_param2;   
//global $g_external_param3;   

// Until next release can only include a config file from a single
// project, so use this variable to ensure only a single config file
// is included
//\Reportico\ReporticoApp::set('included_config', false);

//\Reportico\ReporticoApp::set('no_sql', false);

// Session namespace for allowing multiple reporticos on a single 
// page when called from a framework. In name space in operation the
// session array index to find reportico variables can be found in "reportico"
// otherwise it's reportic_<namespace>
\Reportico\ReporticoApp::setConfig('language', 'en_gb');
\Reportico\ReporticoApp::set('session_namespace', false);
\Reportico\ReporticoApp::set('session_namespace_key', 'reportico');


