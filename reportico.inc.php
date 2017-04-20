<?php

// Include files
// Include the ADODB Database Abstraction Classes
include_once('reportico_adodb/adodb.inc.php');
require_once('swutil.php');

// Set up globals
$g_project = false;
$g_language = "en_gb";
$g_menu = false;
$g_admin_menu = false;
$g_menu_title = false;
$g_dropdown_menu = false;
$g_translations = false;
$g_locale = false;
$g_report_desc = false;

// Defines external plugin parameters
global $g_no_sql;
global $g_external_param1;   // Values passed form calling framworks
global $g_external_param2;   
global $g_external_param3;   

// Until next release can only include a config file from a single
// project, so use this variable to ensure only a single config file
// is included
global $g_included_config;
$g_included_config = false;

$g_no_sql = false;

// Session namespace for allowing multiple reporticos on a single 
// page when called from a framework. In name space in operation the
// session array index to find reportico variables can be found in "reportico"
// otherwise it's reportic_<namespace>
global $g_session_namespace;
global $g_session_namespace_key;
$g_session_namespace = false;
$g_session_namespace_key = "reportico";


