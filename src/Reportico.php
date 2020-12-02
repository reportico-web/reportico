<?php

namespace Reportico\Engine;


// Identify session class functionality appropriate for standalone or in-framework operation, eg use Laravel Session handlind if in Laravel etc
if (!defined("REPORTICO_SESSION_CLASS"))
    define("REPORTICO_SESSION_CLASS", "\Reportico\Engine\ReporticoSession");

function ReporticoSession()
{
    return REPORTICO_SESSION_CLASS;
} 

// Do the same to allow different ways if invoking Builder
if (!defined("REPORTICO_BUILDER_CLASS"))
    define("REPORTICO_BUILDER_CLASS", "\Reportico\Engine\Builder");

function ReporticoBuilder()
{
    return REPORTICO_BUILDER_CLASS;
} 

if ( !function_exists("get_magic_quotes_gpc") ) {
    function get_magic_quotes_gpc() {
        return false;
    } 
}


// Set Session handling based on framework

/*

 * File:        reportico.php
 *
 * This is the core Reportico Reporting Engine. The main
 * reportico class is responsible for coordinating
 * all the other functionality in reading, preparing and
 * executing Reportico reports as well as all the screen
 * handling.
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

/**
 * Class reportico
 *
 * Core functionality that plugs in the database handling,
 * screen handling, XML report definition handling.
 */
class Reportico extends ReporticoObject
{
    public $usage = array(
        "attributes" => array (
            "title" => "ReportTitle",
            "description" => "ReportDescription",
        )
    );

    public $class = "reportico";
    public $prepare_url;
    public $menu_url;
    public $admin_menu_url;
    public $configure_project_url;
    public $delete_project_url;
    public $create_report_url;

    public $version = "7.1.34-beta";
    public $doc_version = "6.0.0";

    public $name;
    public $rowselection = "all";
    public $parent_query = false;
    public $allow_maintain = "FULL";
    public $embedded_report = false;
    public $allow_debug = true;
    public $user_template = false;
    public $xmlin = false;
    public $xmlout = false;
    public $xmloutfile = false;
    public $xmlintext = false;
    public $xmlinput = false;
    public $sqlinput = false;
    public $datasource;
    public $progress_text = "Ready";
    public $progress_status = "Ready"; // One of READY, WORKING, FINISHED, ERROR
    public $query_statement;
    public $maintain_sql = false;
    public $columns = array();
    public $tables = array();
    public $where_text;
    public $group_text;
    public $table_text;
    public $sql_raw = false;
    public $sql_limit_first = false;
    public $sql_skip_offset = false;
    public $display_order_set = array();
    public $order_set = array();
    public $group_set = array();
    public $groups = array();
    public $pageHeaders = array();
    public $pageFooters = array();
    public $query_count = 0;
    public $expand_col = false;
    public $execute_mode;
    public $match_column = "";
    public $lookup_return_col = false;
    public $lookup_queries = array();
    public $source_type = "database";
    public $source_datasource = false;
    public $source_xml = false;
    public $clone_columns = array();
    public $pre_sql = array();
    public $graphs = array();
    public $clearform = false;
    //public $first_criteria_selection = true;
    public $menuitems = array();
    public $dropdown_menu = false;
    public $static_menu = false;
    public $projectitems = array();
    public $target_style = false;
    public $target_format = false;
    public $lineno = 0;
    public $groupvals = array();
    public $email_recipients = false;
    public $drilldown_report = false;
    public $forward_url_get_parameters = "";
    public $forward_url_get_parameters_graph = "";
    public $forward_url_get_parameters_dbimage = "";
    public $reportico_ajax_script_url = false;
    public $reportico_ajax_called = false;
    public $reportico_ajax_mode = true;
    public $reportico_ajax_preloaded = false;
    public $clear_reportico_session = false;

    public $target_show_graph = false;
    public $target_show_detail = false;
    public $target_show_group_headers = false;
    public $target_show_group_trailers = false;
    public $target_showColumnHeaders = false;
    public $target_show_criteria = false;
    public $criteria_block_label = false;

    public $show_form_panel = false;
    public $status_message = "";

    public $framework_parent = false;
    public $framework_type = false;
    public $return_output_to_caller = false;

    public $charting_engine = "PCHART";
    public $charting_engine_html = "NVD3";
    public $pdf_engine = "tcpdf";
    public $pdf_phantomjs_temp_path = false;
    public $pdf_phantomjs_path = "bin/phantomjs";
    public $pdf_delivery_mode = "DOWNLOAD_SAME_WINDOW";
    public $pdf_engine_file = "ReportFPDF";

    public $projects_folder = "projects";
    public $admin_projects_folder = "projects";
    public $compiled_templates_folder = "templates_c";
    public $tmp_folder = "";

    public $widgets = [];

    public $attributes = array(
        "ReportTitle" => "Set Report Title",
        "ReportDescription" => false,
        "PageSize" => ".DEFAULT",
        "PageOrientation" => ".DEFAULT",
        "PageFreezeColumns" => false,
        "TopMargin" => "",
        "BottomMargin" => "",
        "RightMargin" => "",
        "LeftMargin" => "",
        "AutoPaginate" => "",
        "PdfZoomFactor" => "",
        "HtmlZoomFactor" => "",
        "PageTitleDisplay" => "",
        "PageLayout" => "",
        "pdfFont" => "",
        "pdfFontSize" => "",
        "PreExecuteCode" => "NONE",
        "formBetweenRows" => "solidline",
        //"bodyDisplay" => "show",
        //"graphDisplay" => "show",
        "gridDisplay" => ".DEFAULT",
        "gridSortable" => ".DEFAULT",
        "gridSearchable" => ".DEFAULT",
        "gridPageable" => ".DEFAULT",
        "gridPageSize" => ".DEFAULT",
    );

    public $panels = array();
    public $targets = array();
    public $assignment = array();
    public $criteria_links = array();

    // Output control
    public $output_skipline = false;
    public $output_allcell_styles = false;
    public $output_criteria_styles = false;
    public $output_header_styles = false;
    public $output_hyperlinks = false;
    public $output_images = false;
    public $output_row_styles = false;
    public $output_page_styles = false;
    public $output_before_form_row_styles = false;
    public $output_after_form_row_styles = false;
    public $output_group_header_styles = false;
    public $output_group_header_label_styles = false;
    public $output_group_header_value_styles = false;
    public $output_group_trailer_styles = false;
    public $output_reportbody_styles = false;
    public $admin_accessible = true;

    // Template Parameters
    public $output_template_parameters = array(
        "show_hide_navigation_menu" => "show",
        "show_hide_dropdown_menu" => "show",
        "show_hide_report_output_title" => "show",
        "show_hide_prepare_section_boxes" => "hide",
        "show_hide_prepare_pdf_button" => "show",
        "show_hide_prepare_html_button" => "show",
        "show_hide_prepare_print_html_button" => "show",
        "show_hide_prepare_csv_button" => "show",
        "show_hide_prepare_page_style" => "show",
        "show_hide_prepare_reset_buttons" => "hide",
        "show_hide_prepare_go_buttons" => "hide",
    );
    // Template Parameters

    // Charsets for in and output
    public $db_charset = false;
    public $output_charset = false;

    // Currently edited links to other reports
    public $reportlink_report = false;
    public $reportlink_report_item = false;
    public $reportlink_or_import = false;

    // Three parameters which can be set from a calling script
    // which can be incorporated into reportic queries
    // For example a calling framework username can
    // be passed so that data can be returned for that
    // user
    public $external_user = false;
    public $external_param1 = false;
    public $external_param2 = false;
    public $external_param3 = false;

    // Initial settings to set default project, report, execute mode. Set by
    // application frameworks embedding reportico
    public $initial_project = false;
    public $initial_execute_mode = false;
    public $initial_report = false;
    public $initial_project_password = false;
    public $initial_output_format = false;
    public $initial_output_style = false;
    public $initial_show_detail = false;
    public $initial_show_graph = false;
    public $initial_show_group_headers = false;
    public $initial_show_group_trailers = false;
    public $initial_show_column_headers = false;
    public $initial_show_criteria = false;
    public $initial_execution_parameters = false;
    public $authenticator = false;
    public $authenticator_class = "\Reportico\Engine\AuthenticatorStandalone";
    public $initial_role = "guest";
    public $initial_sql = false;

    // Access mode - one of FULL, ALLPROJECTS, ONEPROJECT, REPORTOUTPUT
    public $access_mode = "ALLPROJECTS";

    // Whether to show refresh button on report output
    public $show_refresh_button = false;

    // Whether to show print button on report output
    public $show_print_button = true;

    // Session namespace to use
    public $session_namespace = "reportico";

    // Whether to perform drill downs in their own namespace (normally from embedding in frameworks
    // where reportico namespaces are used within the framework session
    public $drilldown_namespace = false;

    // URL Path to Reportico folder
    public $reportico_url_path = false;

    // Path to Reportico runner for AJAX use or standalone mode
    public $url_path_to_reportico_runner = false;

    // Path to frameworks assets folder
    public $url_path_to_assets = false;

    // Path to templates folder if different
    public $url_path_to_templates = false;

    // Path to public reportico site for help
    public $url_doc_site = "http://www.reportico.org/documentation/";

    // Path to public reportico site
    public $url_site = "http://www.reportico.org/";

    // Path to calling script for form actions
    // In standalone mode will be the reportico runner, otherwise the
    // script in which reportico is embedded
    public $url_path_to_calling_script = false;

    // external user parameters as specified in sql as {USER_PARAM,your_parameter_name}
    // set with $q->user_parameters["your_parameter_name"] = "value";
    public $user_parameters = array();

    // Specify a pdo connection fexternally
    public $external_connection = false;

    public $bootstrap_styles = "3";
    public $jquery_preloaded = false;
    public $bootstrap_preloaded = false;
    public $bootstrap_styling_page = "table table-striped table-condensed";
    public $bootstrap_styling_button_go = "btn btn-sm btn-success";
    public $bootstrap_styling_button_reset = "btn btn-sm btn-default";
    public $bootstrap_styling_button_admin = "btn btn-sm";
    public $bootstrap_styling_button_primary = "btn btn-sm btn-primary";
    public $bootstrap_styling_button_delete = "btn btn-sm btn-danger";
    public $bootstrap_styling_dropdown = "form-control";
    //var $bootstrap_styling_checkbox_button = "btn btn-default btn-xs";
    public $bootstrap_styling_checkbox_button = "checkbox-inline";
    public $bootstrap_styling_checkbox = "checkbox";
    public $bootstrap_styling_toolbar_button = "btn btn-sm";
    public $bootstrap_styling_htabs = "nav nav-justified nav-tabs nav-tabs-justified ";
    public $bootstrap_styling_vtabs = "nav nav-tabs nav-stacked";
    public $bootstrap_styling_design_dropdown = "form-control";
    public $bootstrap_styling_textfield = "form-control";
    public $bootstrap_styling_design_ok = "btn btn-success";
    public $bootstrap_styling_menu_table = "table";
    public $bootstrap_styling_small_button = "btn btn-sm btn-default";

    // Dynamic grids
    public $dynamic_grids = false;
    public $dynamic_grids_sortable = true;
    public $dynamic_grids_searchable = true;
    public $dynamic_grids_paging = false;
    public $dynamic_grids_page_size = 10;

    // Dynamic grids
    public $parent_reportico = false;

    // For laravel ( and other frameworks supporting multiple connections ) specifies
    // an array of available databases to connect ot
    public $available_connections = array();

    // In bootstrap enabled pages, the bootstrap modal is by default used for the quick edit buttons
    // but they can be ignored and reportico's own modal invoked by setting this to true
    public $force_reportico_mini_maintains = false;

    // Array to hold plugins
    public $plugins = array();
    public $csrfToken;
    public $ajaxHandler;

    // Template Engine
    public $theme = "bootstrap4";
    public $templateEngine = false;
    public $templateViewPath = false;
    public $templateCachePath = false;
    public $disableThemeCaching = true;

    // Response code to return back
    public $http_response_code = 200;

    // At any point set to true to returnimmediately back, eg after error
    public $return_to_caller = false;

    public $keep_session_open = false;

    public $initialize_on_execute = true;
    public $report_from_builder = false;
    public $report_from_builder_first_call = false;

    public $template = false;

    // Define asset manager
    public $assetManager = false;
    public $theme_dir = false;

    public $css_framework = false;

    public function __construct()
    {
        ReporticoObject::__construct();

        $this->tmp_folder = __DIR__ . "/../tmp";
        $this->parent_query = &$this;
    }

    // Method to return the framework specific builder method
    public static function ReporticoBuilder()
    {
        return REPORTICO_BUILDER_CLASS;
    } 

    // Dummy functions for yii to work with Reportico
    public function init()
    {
    }

    public function getIsInitialized()
    {
        return true;
    }
    // End Yii functions

    public function &createGraph()
    {
        $engine = $this->charting_engine;

        if (!$this->target_format || $this->target_format == "HTML" || $this->target_format == "HTML2PDF") {
            $engine = $this->charting_engine_html;
        }

        if (ReporticoUtility::getRequestItem("target_format", "HTML") == "PDF") {
            $engine = $this->charting_engine;
        }

        // Cannot use two forms of charting in the same
        if (!class_exists("reportico_graph", false)) {
            switch ($engine) {
                case "NVD3":
                    $graph = new ChartNVD3($this, "internal");
                    break;

                case "FLOT":
                    $graph = new ChartFLOT($this, "internal");
                    break;

                case "JPGRAPH":
                    $graph = new ChrtJpgraph($this, "internal");
                    break;

                case "PCHART":
                default:
                    $graph = new ChartPchart($this, "internal");
                    break;
            }
        }

        $this->graphs[] = &$graph;
        return $graph;
    }

    /*
     ** In AJAX mode, all links, buttons etc will be served by ajax call to
     ** to runner script or specified ajax script, otherwise they will
     ** call the initial calling script
     */
    public function getActionUrl()
    {
        if ($this->reportico_ajax_mode) {
            $calling_script = $this->reportico_ajax_script_url;
            ReporticoApp::set("session_namespace", $this->session_namespace);
        }

        return $calling_script;
    }

    /*
     ** Get a url to the Reportico start script 
     ** which may be required by drilldowns call reportico and initialise it
     */
    public function getStartActionUrl()
    {
        if ($this->reportico_ajax_mode) {
            $calling_script = preg_replace("/run.php/", "start.php", $this->reportico_ajax_script_url);
            ReporticoApp::set("session_namespace", $this->session_namespace);
        }

        return $calling_script;
    }

    public function &getGraphByName($in_query)
    {
        $graphs = array();
        foreach ($this->graphs as $k => $v) {
            if ($v->graph_column == $in_query) {
                $graphs[] = &$this->graphs[$k];
            }
        }
        return $graphs;
    }

    public function queryDisplay()
    {

        foreach ($this->columns as $col) {
            echo $col->query_name;
            echo " " . $col->table_name . "." . $col->column_name;
            echo " " . $col->column_type;
            echo " " . $col->column_length;
            echo " " . $col->in_select;
            echo "<br>\n";
        }

    }

    public function requestDisplay()
    {
        while (list($id, $val) = each($_REQUEST)) {
            echo "<b>$id</b><br>";
            var_dump($val);
            echo "<br>";
        }
    }

    public function setDatasource(&$datasource)
    {
        $this->datasource = &$datasource;
        foreach ($this->columns as $k => $col) {
            $this->columns[$k]->setDatasource($this->datasource);
        }
    }

    public function displayColumns()
    {
        foreach ($this->columns as $k => $col) {
            echo "$k Data: $col->datasource  Name: $col->query_name<br>";
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupReturn
    // -----------------------------------------------------------------------------
    public function setLookupReturn($query_name)
    {

        foreach ($this->columns as $k => $v) {
            $this->columns[$k]->lookup_return_flag = false;
        }
        if ($cl = ReporticoUtility::getQueryColumn($query_name, $this->columns)) {
            $col = &$cl;
            $col->lookup_return_flag = true;
            $this->lookup_return_col = &$col;
        }
    }

    // -----------------------------------------------------------------------------
    // Function : set_column_format
    // -----------------------------------------------------------------------------
    public function setColumnFormat($query_name, $format_type, $format_value)
    {

        $this->checkColumnName("set_column_format", $query_name);
        if ($cl = &ReporticoUtility::getQueryColumn($query_name, $this->columns)) {
            $col = &$cl;
            $col->setFormat($format_type, $format_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : set_group_format
    // -----------------------------------------------------------------------------
    public function setGroupFormat($query_name, $format_type, $format_value)
    {

        $this->checkGroupName("set_group_format", $query_name);
        if (array_key_exists($query_name, $this->group)) {
            $col = &$this->group[$query_name];
            $col->setFormat($format_type, $format_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : add_pre_sql
    // -----------------------------------------------------------------------------
    public function addPreSql($in_sql)
    {
        $this->pre_sql[] = $in_sql;
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupDisplay
    // -----------------------------------------------------------------------------
    public function setLookupDisplay($query_name, $abbrev_name = false)
    {

        if (!$query_name) {
            return;
        }

        if (!$this->checkColumnNameR("setLookupDisplay", $query_name)) {
            ReporticoApp::handleError("Failure in Lookup Display: Unknown Column Name $query_name");
            return;
        }

        if ($cl = ReporticoUtility::getQueryColumn($query_name, $this->columns)) {

            foreach ($this->columns as $k => $v) {
                $this->columns[$k]->lookup_display_flag = false;
                $this->columns[$k]->lookup_abbrev_flag = false;
            }

            $cl->lookup_display_flag = true;

            if ($abbrev_name) {
                $col2 = ReporticoUtility::getQueryColumn($abbrev_name, $this->columns);
                $col2->lookup_abbrev_flag = true;
            } else {
                $cl->lookup_abbrev_flag = true;
            }

        }
    }

    // -----------------------------------------------------------------------------
    // Function : setLookupExpandMatch
    // -----------------------------------------------------------------------------
    public function setLookupExpandMatch($match_column)
    {
        $this->match_column = $match_column;
    }

    // -----------------------------------------------------------------------------
    // Function : check_page_header_name
    // -----------------------------------------------------------------------------
    public function checkPageHeaderName($in_scope, $in_name)
    {
        if (!array_key_exists($in_name, $this->pageHeaders)) {
            ReporticoApp::handleError("$in_scope: Group $in_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_page_footer_name
    // -----------------------------------------------------------------------------
    public function checkPageFooterName($in_scope, $in_name)
    {
        if (!array_key_exists($in_name, $this->pageFooters)) {
            ReporticoApp::handleError("$in_scope: Group $in_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_group_name_r
    // -----------------------------------------------------------------------------
    public function checkGroupNameR($in_scope, $in_column_name)
    {
        if (!($qc = ReporticoUtility::getGroupColumn($in_column_name, $this->groups))) {
            ReporticoApp::handleError("$in_scope: Group $in_column_name unknown");
            return (false);
        } else {
            return true;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_group_name
    // -----------------------------------------------------------------------------
    public function checkGroupName($in_scope, $in_column_name)
    {
        if (!($qc = ReporticoUtility::getGroupColumn($in_column_name, $this->groups))) {
            ReporticoApp::handleError("$in_scope: Group $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_column_name_r
    // -----------------------------------------------------------------------------
    public function checkColumnNameR($in_scope, $in_column_name)
    {
        if (!($cl = ReporticoUtility::getQueryColumn($in_column_name, $this->columns))) {
            ReporticoApp::handleError("$in_scope: Column $in_column_name unknown");
            return false;
        } else {
            return true;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_column_name
    // -----------------------------------------------------------------------------
    public function checkColumnName($in_scope, $in_column_name)
    {
        if (!($cl = ReporticoUtility::getQueryColumn($in_column_name, $this->columns))) {
            ReporticoApp::handleError("$in_scope: Column $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : getCriteriaValue
    // -----------------------------------------------------------------------------
    public function getCriteriaValue($in_criteria_name, $type = "VALUE", $add_delimiters = true)
    {
        if (!array_key_exists($in_criteria_name, $this->lookup_queries)) {
            return false;
        } else {
            return $this->lookup_queries[$in_criteria_name]->getCriteriaClause(false, false, true, false, false, $add_delimiters);
        }

    }

    // -----------------------------------------------------------------------------
    // Function : get_criteria_by_name
    // -----------------------------------------------------------------------------
    public function getCriteriaByName($in_criteria_name)
    {
        if (!array_key_exists($in_criteria_name, $this->lookup_queries)) {
            return false;
        } else {
            return $this->lookup_queries[$in_criteria_name];
        }

    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_name
    // -----------------------------------------------------------------------------
    public function checkCriteriaName($in_scope, $in_column_name)
    {
        if (!array_key_exists($in_column_name, $this->lookup_queries)) {
            ReporticoApp::handleError("$in_scope: Column $in_column_name unknown");
        }
    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_name_r
    // -----------------------------------------------------------------------------
    public function checkCriteriaNameR($in_scope, $in_column_name)
    {
        if (!array_key_exists($in_column_name, $this->lookup_queries)) {
            //ReporticoApp::handleError("$in_scope: Column $in_column_name unknown");
            return false;
        }
        return true;
    }
    // -----------------------------------------------------------------------------
    // Function : set_criteria_link
    // -----------------------------------------------------------------------------
    public function setCriteriaLink($link_from, $link_to, $clause, $link_number = -1)
    {

        if (!$this->checkCriteriaNameR("set_criteria_link", $link_from)) {
            ReporticoApp::handleError("Failure in Criteria Link: Unknown Lookup Name $link_from");
            return;
        }
        if (!$this->checkCriteriaNameR("set_criteria_link", $link_to)) {
            ReporticoApp::handleError("Failure in Criteria Link: Unknown Lookup Name $link_to");
            return;
        }

        //$lf =& $this->columns[$link_from];
        //$lt =& $this->columns[$link_to];

        //$lfq =& $lf->lookup_query;
        //$ltq =& $lt->lookup_query;

        $lfq = &$this->lookup_queries[$link_from]->lookup_query;
        $ltq = &$this->lookup_queries[$link_to]->lookup_query;

        if (!$lfq) {
            ReporticoApp::handleError("set_criteria_link: No Lookup For $link_from");
        }

        $this->lookup_queries[$link_from]->lookup_query->addCriteriaLink($clause, $link_from, $link_to, $ltq, $link_number);
    }

    // -----------------------------------------------------------------------------
    // Function : add_criteria_link
    // -----------------------------------------------------------------------------
    public function addCriteriaLink($clause, $link_from, $link_to, &$query, $link_number = -1)
    {
        if ($link_number != -1) {
            $this->criteria_links[$link_number] =
                array(
                    "clause" => $clause,
                    "link_from" => $link_from,
                    "tag" => $link_to,
                    "query" => &$query,
                );
        } else {
            $this->criteria_links[] =
                array(
                    "clause" => $clause,
                    "link_from" => $link_from,
                    "tag" => $link_to,
                    "query" => &$query,
                );
        }

    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaType
    // -----------------------------------------------------------------------------
    public function setCriteriaType($query_name, $criteria_type)
    {

        $this->checkColumnName("set_criteria_ltype", $query_name);
        if (($cl = &ReporticoUtility::getQueryColumn($query_name, $this->columns))) {
            $cl->setCriteriaType($criteria_type);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaRequired
    // -----------------------------------------------------------------------------
    public function setCriteriaRequired($query_name, $criteria_required)
    {
        //$this->check_column_name("set_criteria_lrequired", $query_name);
        if (($cl = ReporticoUtility::getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaRequired($criteria_required);
        } else {
            echo "fail<BR>";
        }

    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDisplayGroup
    // -----------------------------------------------------------------------------
    public function setCriteriaDisplayGroup($query_name, $criteria_display_group)
    {
        if (($cl = ReporticoUtility::getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaDisplayGroup($criteria_display_group);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHidden
    // -----------------------------------------------------------------------------
    public function setCriteriaHidden($query_name, $criteria_hidden)
    {
        //$this->check_column_name("set_criteria_lhidden", $query_name);
        if (($cl = ReporticoUtility::getQueryColumn($query_name, $this->lookup_queries))) {
            $cl->setCriteriaHidden($criteria_hidden);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHelp
    // -----------------------------------------------------------------------------
    public function setCriteriaHelp($query_name, $criteria_help)
    {
        $this->checkCriteriaName("setCriteriaHelp", $query_name);
        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setCriteriaHelp($criteria_help);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaLinkReport
    // -----------------------------------------------------------------------------
    public function setCriteriaLinkReport($in_query, $in_report, $in_report_item)
    {
        if (array_key_exists($in_query, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$in_query];
            $col->setCriteriaLinkReport($in_report, $in_report_item);
            $col->setDatasource($this->datasource);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaInput
    // -----------------------------------------------------------------------------
    public function setCriteriaInput($in_query, $in_source, $in_display, $in_expand_display = false, $_use = "")
    {
        if (array_key_exists($in_query, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$in_query];
            $col->setCriteriaInput($in_source, $in_display, $in_expand_display, $_use);
            $col->setDatasource($this->datasource);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaLookup
    // -----------------------------------------------------------------------------
    public function &setCriteriaLookup($query_name, &$lookup_query, $in_table = false, $in_column = false)
    {
        if (array_key_exists($query_name, $this->lookup_queries)) {
        } else {
            $this->lookup_queries[$query_name] = new CriteriaColumn(
                $this,
                $query_name,
                $in_table,
                $in_column,
                "CHAR",
                0,
                "###.##",
                0
            );
            $this->setCriteriaAttribute($query_name, "column_title", $query_name);
            $lookup_query->setDatasource($this->datasource);
        }

        $this->parent_query = &$this;
        $this->lookup_queries[$query_name]->setCriteriaLookup($lookup_query);
        //$this->lookup_queries[$query_name]->first_criteria_selection = $this->first_criteria_selection;
        $this->lookup_queries[$query_name]->lookup_query->parent_query = &$this;
        return $this->lookup_queries[$query_name];
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaList
    // -----------------------------------------------------------------------------
    public function setCriteriaList($query_name, $in_list)
    {
        $this->checkCriteriaName("setCriteriaList", $query_name);
        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setCriteriaList($in_list);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDefaults
    // -----------------------------------------------------------------------------
    public function setCriteriaDefaults($query_name, $in_default, $in_delimiter = false)
    {
        if ($in_default || $in_default == "0") {
            $this->checkCriteriaName("setCriteriaDefaults", $query_name);
            if (array_key_exists($query_name, $this->lookup_queries)) {
                $col = &$this->lookup_queries[$query_name];
                $col->setCriteriaDefaults($in_default, $in_delimiter);
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : report_progress
    // -----------------------------------------------------------------------------
    public function reportProgress($in_text, $in_status)
    {
        $sessionClass = ReporticoSession();

        $this->progress_text = $in_text;
        $this->progress_status = $in_status;

        $sessionClass::setReporticoSessionParam("progress_text", $this->progress_text);
        $sessionClass::setReporticoSessionParam("progress_status", $this->progress_status);
    }

    // -----------------------------------------------------------------------------
    // Function : setPageHeaderAttribute
    // -----------------------------------------------------------------------------
    public function setPageHeaderAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (!$query_name) {
            $query_name = count($this->pageHeaders) - 1;
        }

        $this->checkPageHeaderName("setPageHeaderAttribute", $query_name);
        if (array_key_exists($query_name, $this->pageHeaders)) {
            $col = &$this->pageHeaders[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setPageFooterAttribute
    // -----------------------------------------------------------------------------
    public function setPageFooterAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (!$query_name) {
            $query_name = count($this->pageFooters) - 1;
        }

        $this->checkPageFooterName("setPageFooterAttribute", $query_name);
        if (array_key_exists($query_name, $this->pageFooters)) {
            $col = &$this->pageFooters[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaAttribute
    // -----------------------------------------------------------------------------
    public function setCriteriaAttribute($query_name, $attrib_name, $attrib_value)
    {

        if (array_key_exists($query_name, $this->lookup_queries)) {
            $col = &$this->lookup_queries[$query_name];
            $col->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setColumnAttribute
    // -----------------------------------------------------------------------------
    public function setColumnAttribute($query_name, $attrib_name, $attrib_value)
    {

        $this->checkColumnName("setColumnAttribute", $query_name);
        if (($cl = ReporticoUtility::getQueryColumn($query_name, $this->columns))) {
            $cl->setAttribute($attrib_name, $attrib_value);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : addTarget
    // -----------------------------------------------------------------------------
    public function addTarget(&$target)
    {
        $this->targets[] = &$target;
    }

    // -----------------------------------------------------------------------------
    // Function : store_column_results
    // -----------------------------------------------------------------------------
    public function storeColumnResults()
    {
        // Ensure that values returned from database query are placed
        // in the appropriate query column value
        foreach ($this->columns as $k => $col) {
            $this->columns[$k]->old_column_value =
                $this->columns[$k]->column_value;
            $this->columns[$k]->reset_flag = false;
        }
    }

    // -----------------------------------------------------------------------------
    // Function : build_column_results
    // -----------------------------------------------------------------------------
    public function buildColumnResults($result_line)
    {
        // Ensure that values returned from database query are placed
        // in the appropriate query column value
        $ct = 0;
        foreach ($this->columns as $k => $col) {

            if ($col->in_select) {
                $this->debug("selecting $col->query_name in");

                // Oracle returns associated array keys in upper case
                $assoc_key = $col->query_name;

                if (array_key_exists($assoc_key, $result_line)) {
                    $this->debug("exists");
                    $colval = $result_line[$assoc_key];

                    if (is_array($colval)) {
                        continue;
                        //var_dump($colval);
                        //die;
                    }

                    if (is_string($colval)) {
                        $colval = trim($colval);
                    }

                    //$this->debug("$colval");
                } else
                    if (array_key_exists(strtoupper($assoc_key), $result_line)) {
                        $this->debug("exists");
                        $colval = $result_line[strtoupper($assoc_key)];

                        if (is_string($colval)) {
                            $colval = trim($colval);
                        }

                        $this->debug("$colval");
                    } else {
                        $colval = "NULL";
                    }

                $this->columns[$k]->column_value = $colval;
            } else {
                $this->columns[$k]->column_value = $col->query_name;
            }

            $ct++;

        }
    }

    // -----------------------------------------------------------------------------
    // Function : get_execute_mode()
    // -----------------------------------------------------------------------------
    public function getExecuteMode()
    {
        $sessionClass = ReporticoSession();

        // User clicked Report Dropdown + Go Button
        if (array_key_exists('submit_execute_mode', $_REQUEST)) {
            if (array_key_exists('execute_mode', $_REQUEST)) {
                $this->execute_mode = $_REQUEST['execute_mode'];
            }
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_design_mode', $_REQUEST)) {
            $this->execute_mode = "MAINTAIN";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_genws_mode', $_REQUEST)) {
            $this->execute_mode = "SOAPSAVE";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_prepare_mode', $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        // User clicked Design Mode Button
        if (array_key_exists('submit_criteria_mode', $_REQUEST)) {
            $this->execute_mode = "CRITERIA";
        }

        if (array_key_exists('execute_mode', $_REQUEST)) {
            if ($_REQUEST["execute_mode"] == "MAINTAIN" && $this->allow_maintain != "SAFE"
                && $this->allow_maintain != "FULL" && $this->allow_maintain != "DEMO") {
            } else {
                $this->execute_mode = $_REQUEST["execute_mode"];
            }
        }

        if (!$this->execute_mode && array_key_exists('submit', $_REQUEST)) {
            $this->execute_mode = "EXECUTE";
        }

        if (!$this->execute_mode && array_key_exists('submitPrepare', $_REQUEST)) {
            $this->execute_mode = "EXECUTE";
        }

        if (!$this->execute_mode && $sessionClass::issetReporticoSessionParam("execute_mode")) {
            $this->execute_mode = $sessionClass::getReporticoSessionParam("execute_mode");
        }

        // If user has pressed expand then we want to staty in PREPARE mode
        foreach ($_REQUEST as $key => $value) {
            if (preg_match("/^EXPAND_/", $key)) {
                $this->execute_mode = "PREPARE";
                break;
            }
        }

        if (!$this->execute_mode) {
            $this->execute_mode = "MENU";
        }

        if ($this->execute_mode == "MAINTAIN" &&
            $this->allow_maintain != "SAFE" &&
            $this->allow_maintain != "DEMO" &&
            $this->allow_maintain != "FULL" &&
            !$this->parent_query) {
            ReporticoApp::handleError("Report Maintenance Mode Disallowed");
            $this->execute_mode = "PREPARE";
        }
        if (array_key_exists('execute_mode', $_REQUEST)) {
            if ($_REQUEST["execute_mode"] == "MAINTAIN" && $this->allow_maintain != "SAFE"
                && $this->allow_maintain != "DEMO"
                && $this->allow_maintain != "FULL") {
            } else {
                $this->execute_mode = $_REQUEST["execute_mode"];
            }
        }

        if (!$this->execute_mode) {
            $this->execute_mode = "MENU";
        }

        // Override mode if specified from ADMIN page
        if (ReporticoUtility::getRequestItem("jump_to_delete_project", "") && array_key_exists("submit_delete_project", $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        if (ReporticoUtility::getRequestItem("jump_to_configure_project", "") && array_key_exists("submit_configure_project", $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }

        if (ReporticoUtility::getRequestItem("jump_to_menu_project", "") && array_key_exists("submit_menu_project", $_REQUEST)) {
            $this->execute_mode = "MENU";
        }

        if (ReporticoUtility::getRequestItem("jump_to_create_report", "") && array_key_exists("submit_design_project", $_REQUEST)) {
            $this->xmloutfile = "";
            $this->execute_mode = "PREPARE";
        }

        // If Reset pressed force to Prepare mode
        if (array_key_exists("clearform", $_REQUEST)) {
            $this->execute_mode = "PREPARE";
        }
        //echo "<BR>first time clear2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");

        // If logout pressed then force to MENU mode
        if (array_key_exists("logout", $_REQUEST)) {
            $this->execute_mode = "MENU";
        }

        // If initialised from framework then set mode from there
        if ($this->initial_execute_mode && $sessionClass::getReporticoSessionParam("awaiting_initial_defaults")) {
            $this->execute_mode = $this->initial_execute_mode;
        }

        // Maintain execute mode through except for CRITERIA
        if ($this->execute_mode != "CRITERIA") {
            $sessionClass::setReporticoSessionParam("execute_mode", $this->execute_mode);
        }

        return ($this->execute_mode);
    }

    // -----------------------------------------------------------------------------
    // Function : set_request_columns()
    // -----------------------------------------------------------------------------
    public function setRequestColumns()
    {
        $sessionClass = ReporticoSession();

        if (array_key_exists("clearform", $_REQUEST)) {
            $this->clearform = true;
            //$this->first_criteria_selection = true;
        }

        // Store filter group open close state
        if (isset($_REQUEST["closedfilters"]) || isset($_REQUEST["openfilters"])) {
            if (isset($_REQUEST["closedfilters"])) {
                //$sessionClass::setReporticoSessionParam("closedfilters", $_REQUEST["closedfilters"]);
            } else {
                $sessionClass::setReporticoSessionParam("closedfilters", false);
            }

            if (isset($_REQUEST["openfilters"])) {
                $sessionClass::setReporticoSessionParam("openfilters", $_REQUEST["openfilters"]);
            } else {
                $sessionClass::setReporticoSessionParam("openfilters", false);
            }

        }

        // If an initial set of parameter values has been set then parameters are being
        // set probably from a framework. In this case we need clear any MANUAL and HIDDEN requests
        // and set MANUAL ones from the external ones
        if ($this->initial_execution_parameters) {
            foreach ($_REQUEST as $k => $v) {
                if (preg_match("/^MANUAL_/", $k) || preg_match("/^HIDDEN_/", $k)) {
                    unset($_REQUEST[$k]);
                }
            }

        }

        $execute_mode = $this->getExecuteMode();
        /*
        foreach ($this->lookup_queries as $col) {
            // If this is first time into screen and we have defaults then
            // use these instead
            if ($sessionClass::getReporticoSessionParam("firstTimeIn")) {
                $this->lookup_queries[$col->query_name]->column_value =
                    $this->lookup_queries[$col->query_name]->defaults;
                if (is_array($this->lookup_queries[$col->query_name]->column_value)) {
                    $this->lookup_queries[$col->query_name]->column_value =
                        implode(",", $this->lookup_queries[$col->query_name]->column_value);
                }

                // Daterange defaults needs to  eb converted to 2 values
                if ($this->lookup_queries[$col->query_name]->criteria_type == "DATERANGE" && !$this->lookup_queries[$col->query_name]->defaults) {
                    $this->lookup_queries[$col->query_name]->defaults = array();
                    $this->lookup_queries[$col->query_name]->defaults[0] = "TODAY-TODAY";
                    $this->lookup_queries[$col->query_name]->defaults[1] = "TODAY";
                    $this->lookup_queries[$col->query_name]->column_value = "TODAY-TODAY";
                }
                if ($this->lookup_queries[$col->query_name]->criteria_type == "DATE" && !$this->lookup_queries[$col->query_name]->defaults) {
                    $this->lookup_queries[$col->query_name]->defaults = array();
                    $this->lookup_queries[$col->query_name]->defaults[0] = "TODAY";
                    $this->lookup_queries[$col->query_name]->defaults[1] = "TODAY";
                    $this->lookup_queries[$col->query_name]->column_value = "TODAY";
                }
                $this->defaults = $this->lookup_queries[$col->query_name]->defaults;
                if (isset($this->defaults)) {
                    if ($this->lookup_queries[$col->query_name]->criteria_type == "DATERANGE") {
                        if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE",
                            $this->lookup_queries[$col->query_name]->column_value,
                            $this->lookup_queries[$col->query_name]->column_value,
                            $this->lookup_queries[$col->query_name]->column_value2)) {
                            trigger_error("Date default '" . $this->defaults[0] . "' is not a valid3 date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                        }

                    }
                    if ($this->lookup_queries[$col->query_name]->criteria_type == "DATE") {
                        $dummy = "";
                        if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATE", $this->defaults[0], $this->range_start, $dummy)) {
                            if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATE",
                                $this->lookup_queries[$col->query_name]->column_value,
                                $this->lookup_queries[$col->query_name]->column_value,
                                $this->lookup_queries[$col->query_name]->column_value2)) {
                                trigger_error("Date default '" . $this->defaults[0] . "' is not a valid date. Should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                            }
                        }

                    }
                }
            }
        }
        */

        if (array_key_exists("clearform", $_REQUEST)) {
            $sessionClass::setReporticoSessionParam("firstTimeIn", true);
            $sessionClass::setReporticoSessionParam("openfilters", false);
            $sessionClass::setReporticoSessionParam("closedfilters", false);
        }
        //echo "first time cl = ".$sessionClass::getReporticoSessionParam("firstTimeIn");

        // Set up show option check box settings

        // If initial form style specified use it
        if ($this->initial_output_style) {
            $sessionClass::setReporticoSessionParam("target_style", $this->initial_output_style);
        }

        // If default starting "show" setting provided by calling framework then use them
        if ($this->show_print_button) {
            $sessionClass::setReporticoSessionParam("show_print_button", ($this->show_print_button == "show"));
        }

        if ($this->show_refresh_button) {
            $sessionClass::setReporticoSessionParam("show_refresh_button", ($this->show_refresh_button == "show"));
        }

        if ($this->initial_show_detail) {
            $sessionClass::setReporticoSessionParam("target_show_detail", ($this->initial_show_detail == "show"));
        }

        if ($this->initial_show_graph) {
            $sessionClass::setReporticoSessionParam("target_show_graph", ($this->initial_show_graph == "show"));
        }

        if ($this->initial_show_group_headers) {
            $sessionClass::setReporticoSessionParam("target_show_group_headers", ($this->initial_show_group_headers == "show"));
        }

        if ($this->initial_show_group_trailers) {
            $sessionClass::setReporticoSessionParam("target_show_group_trailers", ($this->initial_show_group_trailers == "show"));
        }

        if ($this->initial_show_criteria) {
            $sessionClass::setReporticoSessionParam("target_show_criteria", ($this->initial_show_criteria == "show"));
        }

        $this->target_show_detail = $sessionClass::sessionRequestItem("target_show_detail", true, !$sessionClass::issetReporticoSessionParam("target_show_detail"));
        $this->target_show_graph = $sessionClass::sessionRequestItem("target_show_graph", true, !$sessionClass::issetReporticoSessionParam("target_show_graph"));
        $this->target_show_group_headers = $sessionClass::sessionRequestItem("target_show_group_headers", true, !$sessionClass::issetReporticoSessionParam("target_show_group_headers"));
        $this->target_show_group_trailers = $sessionClass::sessionRequestItem("target_show_group_trailers", true, !$sessionClass::issetReporticoSessionParam("target_show_group_trailers"));
        $this->target_show_criteria = $sessionClass::sessionRequestItem("target_show_criteria", true, !$sessionClass::issetReporticoSessionParam("target_show_criteria"));

        if ($sessionClass::getReporticoSessionParam("firstTimeIn")
            && !$this->initial_show_detail && !$this->initial_show_graph && !$this->initial_show_group_headers
            && !$this->initial_show_group_trailers && !$this->initial_show_column_headers && !$this->initial_show_criteria
        ) {
            // If first time in default output hide/show elements to what is passed in URL params .. if none supplied show all
            if ($this->execute_mode == "EXECUTE") {
                $this->target_show_detail = ReporticoUtility::getRequestItem("target_show_detail", false);
                $this->target_show_graph = ReporticoUtility::getRequestItem("target_show_graph", false);
                $this->target_show_group_headers = ReporticoUtility::getRequestItem("target_show_group_headers", false);
                $this->target_show_group_trailers = ReporticoUtility::getRequestItem("target_show_group_trailers", false);
                $this->target_show_criteria = ReporticoUtility::getRequestItem("target_show_criteria", false);
                if (!$this->target_show_detail && !$this->target_show_graph && !$this->target_show_group_headers
                    && !$this->target_show_group_trailers && !$this->target_showColumnHeaders && !$this->target_show_criteria) {
                    $this->target_show_detail = true;
                    $this->target_show_graph = true;
                    $this->target_show_group_headers = true;
                    $this->target_show_group_trailers = true;
                    $this->target_show_criteria = true;
                }
                $sessionClass::setReporticoSessionParam("target_show_detail", $this->target_show_detail);
                $sessionClass::setReporticoSessionParam("target_show_graph", $this->target_show_graph);
                $sessionClass::setReporticoSessionParam("target_show_group_headers", $this->target_show_group_headers);
                $sessionClass::setReporticoSessionParam("target_show_group_trailers", $this->target_show_group_trailers);
                $sessionClass::setReporticoSessionParam("target_show_criteria", $this->target_show_criteria);
            } else {
                //$this->target_show_detail = true;
                //$this->target_show_graph = true;
                //$this->target_show_group_headers = true;
                //$this->target_show_group_trailers = true;
                //$this->target_showColumnHeaders = true;
                //$this->target_show_criteria = false;
                //$sessionClass::setReporticoSessionParam("target_show_detail",true);
                //$sessionClass::setReporticoSessionParam("target_show_graph",true);
                //$sessionClass::setReporticoSessionParam("target_show_group_headers",true);
                //$sessionClass::setReporticoSessionParam("target_show_group_trailers",true);
                //$sessionClass::setReporticoSessionParam("target_showColumnHeaders",true);
                //$sessionClass::setReporticoSessionParam("target_show_criteria",false);
            }
        } else {
            // If not first time in, then running report would have come from
            // prepare screen which provides details of what report elements to include
            if ($this->execute_mode == "EXECUTE") {
                $runfromcriteriascreen = ReporticoUtility::getRequestItem("user_criteria_entered", false);
                if ($runfromcriteriascreen) {
                    $this->target_show_detail = ReporticoUtility::getRequestItem("target_show_detail", false);
                    $this->target_show_graph = ReporticoUtility::getRequestItem("target_show_graph", false);
                    $this->target_show_group_headers = ReporticoUtility::getRequestItem("target_show_group_headers", false);
                    $this->target_show_group_trailers = ReporticoUtility::getRequestItem("target_show_group_trailers", false);
                    $this->target_show_criteria = ReporticoUtility::getRequestItem("target_show_criteria", false);
                    if (!$this->target_show_detail && !$this->target_show_graph && !$this->target_show_group_headers
                        && !$this->target_show_group_trailers && !$this->target_showColumnHeaders && !$this->target_show_criteria) {
                        $this->target_show_detail = true;
                        $this->target_show_graph = true;
                        $this->target_show_group_headers = true;
                        $this->target_show_group_trailers = true;
                        $this->target_show_criteria = true;
                    }
                    $sessionClass::setReporticoSessionParam("target_show_detail", $this->target_show_detail);
                    $sessionClass::setReporticoSessionParam("target_show_graph", $this->target_show_graph);
                    $sessionClass::setReporticoSessionParam("target_show_group_headers", $this->target_show_group_headers);
                    $sessionClass::setReporticoSessionParam("target_show_group_trailers", $this->target_show_group_trailers);
                    $sessionClass::setReporticoSessionParam("target_show_criteria", $this->target_show_criteria);
                }
            }
        }
        if (isset($_REQUEST["target_show_detail"])) {
            $sessionClass::setReporticoSessionParam("target_show_detail", $_REQUEST["target_show_detail"]);
        }

        if (isset($_REQUEST["target_show_graph"])) {
            $sessionClass::setReporticoSessionParam("target_show_graph", $_REQUEST["target_show_graph"]);
        }

        if (isset($_REQUEST["target_show_group_headers"])) {
            $sessionClass::setReporticoSessionParam("target_show_group_headers", $_REQUEST["target_show_group_headers"]);
        }

        if (isset($_REQUEST["target_show_group_trailers"])) {
            $sessionClass::setReporticoSessionParam("target_show_group_trailers", $_REQUEST["target_show_group_trailers"]);
        }

        if (isset($_REQUEST["target_show_criteria"])) {
            $sessionClass::setReporticoSessionParam("target_show_criteria", $_REQUEST["target_show_criteria"]);
        }

        if (array_key_exists("clearform", $_REQUEST)) {
            return;
        }

        // If external page has supplied an initial output format then use it
        if ($this->initial_output_format) {
            $_REQUEST["target_format"] = $this->initial_output_format;
        }

        // If printable HTML requested force output type to HTML
        if (ReporticoUtility::getRequestItem("printable_html")) {
            $_REQUEST["target_format"] = "HTML";
        }

        // Prompt user for report destination if target not already set - default to HTML if not set
        if (!array_key_exists("target_format", $_REQUEST) && $execute_mode == "EXECUTE") {
            $_REQUEST["target_format"] = "HTML";
        }


        if (array_key_exists("target_format", $_REQUEST) && $execute_mode == "EXECUTE" && count($this->targets) == 0) {
            $tf = $_REQUEST["target_format"];
            if (isset($_GET["target_format"])) {
                $tf = $_GET["target_format"];
            }
            $this->target_format = strtolower($tf);

            if ($this->target_format == "pdf") {
                $this->pdf_engine_file = "Report" . strtoupper($this->pdf_engine) . ".php";
                if ($this->pdf_engine == "chromium") {
                    require_once "ReportChromium.php";
                } else
                if ($this->pdf_engine == "phantomjs") {
                    require_once "ReportPhantomJSPDF.php";
                } else
                    require_once $this->pdf_engine_file;
            } else {
                require_once "Report" . ucwords($this->target_format) . ".php";
            }


            $this->target_format = strtoupper($tf);
            //$this->target_format = "HTML2PDF";
            switch ($tf) {
                case "CSV":
                case "csv":
                case "Microsoft Excel":
                case "EXCEL":
                    $rep = new ReportCsv();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "soap":
                case "SOAP":
                    $rep = new ReportSoapTemplate();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "html":
                case "HTML":
                    $rep = new ReportHtml();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "htmlpdf":
                case "HTML2PDF":
                    $rep = new ReportHtml2pdf();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "htmlgrid":
                case "HTMLGRID":
                    $rep = new ReportHtml_grid_template();
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "pdf":
                case "PDF":
                    if ($this->pdf_engine == "chromium") {
                        $rep = new ReportChromium();
                    } else if ($this->pdf_engine == "phantomjs") {
                        $rep = new ReportPhantomJSPDF();
                    } else if ($this->pdf_engine == "tcpdf") {
                        $rep = new ReportTCPDF();
                    } else {
                        $rep = new ReportFPDF();
                    }

                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "json":
                case "JSON":
                    $rep = new ReportJson();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "jquerygrid":
                case "JQUERYGRID":
                    $rep = new ReportJQueryGrid();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                case "xml":
                case "XML":
                    $rep = new ReportXml();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                //case "array" :
                case "ARRAY":
                    $rep = new ReportArray();
                    $rep->page_length = 80;
                    $this->addTarget($rep);
                    $rep->setQuery($this);
                    break;

                default:
                    // Should not get here
            }
        }

        if (array_key_exists("mailto", $_REQUEST)) {
            $this->email_recipients = $_REQUEST["mailto"];
        }

    }

    // -----------------------------------------------------------------------------
    // Function : build_column_list
    // -----------------------------------------------------------------------------
    public function &buildColumnList()
    {

        $str = "";
        $ct = 0;

        // Build Select Column List
        foreach ($this->columns as $k => $col) {
            if ($col->in_select) {
                if ($ct > 0) {
                    $str .= ",";
                }

                $str .= " ";

                if ($col->table_name) {
                    $str .= $col->table_name . ".";
                }

                $str .= $col->column_name;

                if (($col->query_name)) {
                    $str .= " " . $col->query_name;
                }

                $ct++;
            }
        }
        //die;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_order_list
    // -----------------------------------------------------------------------------
    public function &buildOrderList($in_criteria_name)
    {
        $ct = 0;
        $str = "";

        foreach ($this->order_set as $col) {
            if ($ct > 0) {
                $str .= ",";
            } else {
                $ct++;
            }

            $str .= " ";

            if ($col->table_name) {
                $str .= $col->table_name . ".";
                $str .= $col->column_name . " ";
            } else {
                $str .= $col->query_name . " ";
            }
            $str .= $col->order_type;
        }

        // May need to use users custom sort :-
        if (!$in_criteria_name && $orderby = ReporticoUtility::getRequestItem("sidx", "")) {
            if ($orddir = ReporticoUtility::getRequestItem("sord", "")) {
                $str = $orderby . " " . $orddir;
            } else {
                $str = $orderby;
            }

        }

        if ($str) {
            $str = " \nORDER BY " . $str;
        }

        return $str;
    }

    // -----------------------------------------------------------------------------------
    public function &buildLimitOffset()
    {
        $str = "";

        // Handle any user specified FIRST, SKIP ROWS functions
        // Set in the following order :-
        // User specified a limit and offset parameter else
        $limit = ReporticoUtility::getRequestItem("report_limit", "");
        $offset = ReporticoUtility::getRequestItem("report_offset", "");
        // User specified a page and row parameter  which else
        if (!$limit && !$offset) {
            $page = ReporticoUtility::getRequestItem("page", "");
            $rows = ReporticoUtility::getRequestItem("rows", "");
            if ($page && $page > 0 && $rows) {
                $offset = ($page - 1) * $rows;
                $limit = $rows;
            }
        }

        // report contains a default skip and offset definition
        if (!$limit && !$offset) {
            $limit = $this->sql_limit_first;
            $offset = $this->sql_skip_offset;
        }

        if (!$limit && !$offset) {
            return $str;
        }

        if (!$offset) {
            $offset = "0";
        }

        if ($this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
            // Offset without limit doesnt work in Mysql
            if ($this->datasource->_conn_driver == "pdo_mysql") {
                if (!$limit) {
                    $limit = "18446744073709551615";
                }

            }

            $str .= " LIMIT $limit";

            if ($offset) {
                $str .= " OFFSET $offset";
            }

        } else {
            if ($rows) {
                $str .= " FIRST $limit";
            }

            if ($offset) {
                $str = " SKIP $offset" . $str;
            }

        }

        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_list
    // -----------------------------------------------------------------------------
    public function &buildWhereList($include_lookups = true)
    {
        // Parse the where text to replace withcriteria values specified
        // with {}
        if ($this->where_text != "AND ") {
            $str = " \nWHERE 1 = 1 " . $this->where_text;
        } else {
            $str = " \nWHERE 1 = 1 ";
        }

        $x = array_keys($this->lookup_queries);

        $parsing = true;

        if ($include_lookups) {
            foreach ($this->lookup_queries as $k => $col) {
                if ($col->column_name) {
                    $str .= $col->getCriteriaClause();
                }
            }
        }

        $str .= " " . $this->group_text;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_extra_list
    // -----------------------------------------------------------------------------
    public function &buildWhereExtraList($in_is_expanding = false, $criteria_name)
    {
        $str = "";
        $expval = false;
        if ($in_is_expanding) {
            if (array_key_exists("expand_value", $_REQUEST)) {
                if ($_REQUEST["expand_value"] && $this->match_column) {
                    $expval = $_REQUEST["expand_value"];
                }
            }
            if (array_key_exists("MANUAL_" . $criteria_name, $_REQUEST)) {
                $tmpval = $_REQUEST['MANUAL_' . $criteria_name];
                if (strlen($tmpval) > 1 && substr($tmpval, 0, 1) == "?") {
                    $expval = substr($tmpval, 1);
                }

            }

            if ($expval) {
                $str = ' AND ' . $this->match_column . ' LIKE "%' . $expval . '%"';
            }
        } else if ($expval = ReporticoUtility::getRequestItem("reportico_criteria_match", false)) {
            if ( $this->match_column )
                $str = ' AND ' . $this->match_column . ' LIKE "%' . $expval . '%"';
        }

        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : build_where_criteria_link
    // -----------------------------------------------------------------------------
    public function &buildWhereCriteriaLink($in_is_expanding = false)
    {
        $retval = "";
        foreach ($this->criteria_links as $criteria_link) {
            $clause = $criteria_link["clause"];
            $link = $criteria_link["tag"];
            $query = $criteria_link["query"];

            $params = array();

            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists($link, $_REQUEST)) {
                    $params = $_REQUEST[$link];
                    if (!is_array($params)) {
                        $params = array($params);
                    }

                }
            }

            $hidden_params = array();
            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists("HIDDEN_" . $link, $_REQUEST)) {
                    $hidden_params = $_REQUEST["HIDDEN_" . $link];
                    if (!is_array($hidden_params)) {
                        $hidden_params = array($hidden_params);
                    }

                }
            }

            $manual_params = array();
            if (!array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                if (array_key_exists("MANUAL_" . $link, $_REQUEST)) {
                    $manual_params = explode(',', $_REQUEST["MANUAL_" . $link]);
                    if (!is_array($manual_params)) {
                        $manual_params = array($manual_params);
                    }

                }
            }

            $expanded_params = array();
            if (array_key_exists("EXPANDED_" . $link, $_REQUEST)) {
                $expanded_params = $_REQUEST["EXPANDED_" . $link];
                if (!is_array($expanded_params)) {
                    $expanded_params = array($expanded_params);
                }

            }

            $del = "";
            $cls = "";

            // quotedness for in clause is based on return value column
            if ($query) {
                if ($query->lookup_return_col) {
                    $del = $query->lookup_return_col->getValueDelimiter();
                }
            }

            foreach ($hidden_params as $col) {

                if ($col == "(ALL)") {
                    continue;
                }

                if (!$cls) {
                    $cls = $del . $col . $del;
                } else {
                    $cls .= "," . $del . $col . $del;
                }

            }
            foreach ($expanded_params as $col) {
                if ($col == "(ALL)") {
                    continue;
                }

                if (!$cls) {
                    $cls = $del . $col . $del;
                } else {
                    $cls .= "," . $del . $col . $del;
                }

            }

            if ($cls) {
                $retval = " AND $clause IN ( $cls )";
            }

        }

        return ($retval);

    }

    // -----------------------------------------------------------------------------
    // Function : build_table_list
    // -----------------------------------------------------------------------------
    public function &buildTableList()
    {
        $str = " \nFROM " . $this->table_text;
        return $str;
    }

    // -----------------------------------------------------------------------------
    // Function : buildQuery
    // -----------------------------------------------------------------------------
    public function buildQuery($in_is_expanding = false, $criteria_name = "", $in_design_mode = false, $no_warnings = false)
    {

        if (!$criteria_name) {
            $this->setRequestColumns();
        }

        $execute_mode = $this->getExecuteMode();

        // Use raw user query in >= Version 2.5
        if ($this->sql_raw) {
            // Now if any of the criteria is an SQLCOMMAND then that should be used for the report data so
            // parse the users command and execute it
            if (!$in_is_expanding && !$criteria_name && !$in_design_mode) {
                foreach ($this->lookup_queries as $key => $col) {
                    if ($col->criteria_type == "SQLCOMMAND") {
                        $this->importSQL($col->column_value);
                    }

                }
            }

            $this->query_statement = $this->sql_raw;

            // Build in criteria items
            $critwhere = "";
            if ($execute_mode != "MAINTAIN") {
                foreach ($this->lookup_queries as $k => $col) {
                    if ($col->column_name) {
                        $critwhere .= $col->getCriteriaClause();
                    }
                }
            }

            // Add in any expand criteria
            $critwhere .= $this->buildWhereExtraList($in_is_expanding, $criteria_name);

            // If user has "Main query column" criteria then parse sql to find
            // where to insert them
            if ($critwhere) {
                $p = new SqlParser($this->query_statement);
                if ($p->parse()) {
                    if ($p->haswhere) {
                        $this->query_statement =
                            substr($this->query_statement, 0, $p->whereoffset) .
                            " 1 = 1" .
                            $critwhere .
                            " AND" .
                            substr($this->query_statement, $p->whereoffset);
                    } else {
                        $this->query_statement =
                            substr($this->query_statement, 0, $p->whereoffset) .
                            " WHERE 1 = 1 " .
                            $critwhere .
                            substr($this->query_statement, $p->whereoffset);
                    }
                }
            }

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if ($this->datasource && !$criteria_name && !$in_design_mode) {
                if (isset($this->datasource->_conn_driver) && $this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

        } else {
            // Pre Version 2.5 - parts of SQL specified in XML
            $this->query_statement = "SELECT";

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if ($this->datasource && !$criteria_name && !$in_design_mode) {
                if ($this->datasource->_conn_driver == "pdo_informix" || $this->datasource->_conn_driver == "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

            if ($this->rowselection == "unique") {
                if ($this->datasource && $this->datasource->_conn_driver == "pdo_informix" || $this->datasource->_conn_driver == "informix") {
                    $this->query_statement .= " UNIQUE";
                } else {
                    $this->query_statement .= " DISTINCT";
                }
            }

            $this->query_statement .= $this->buildColumnList();
            $this->query_statement .= $this->buildTableList();

            if ($execute_mode == "MAINTAIN") {
                $this->query_statement .= $this->buildWhereList(false);
            } else {
                $this->query_statement .= $this->buildWhereList(true);
            }

            $this->query_statement .= $this->buildWhereExtraList($in_is_expanding, $criteria_name);
            $this->query_statement .= $this->buildWhereCriteriaLink($in_is_expanding);
            $this->query_statement .= $this->buildOrderList($criteria_name);

            // Dont add limits/offset if crtieria query of entering SQL in design mode
            if (!$criteria_name && !$in_design_mode) {
                if ($this->datasource->_conn_driver != "pdo_informix" && $this->datasource->_conn_driver != "informix") {
                    $this->query_statement .= $this->buildLimitOffset();
                }
            }

        }

        if ($execute_mode != "MAINTAIN") {
            $this->query_statement = Assignment::reporticoMetaSqlCriteria($this->parent_query, $this->query_statement, false, $no_warnings, $execute_mode);
        }
        //echo $this->query_statement."<BR>";

    }

    // -----------------------------------------------------------------------------
    // Function : createPageHeader
    // -----------------------------------------------------------------------------
    public function &createPageHeader(
        $page_header_name = "",
        $line,
        $page_header_text
    )
    {
        if (!$page_header_name) {
            $page_header_name = count($this->pageHeaders);
        }

        $this->pageHeaders[$page_header_name] = new ReporticoPageEnd($line, $page_header_text);
        return $this->pageHeaders[$page_header_name];
    }

    // -----------------------------------------------------------------------------
    // Function : createPageFooter
    // -----------------------------------------------------------------------------
    public function createPageFooter(
        $page_footer_name = "",
        $line,
        $page_footer_text
    )
    {
        if (!$page_footer_name) {
            $page_footer_name = count($this->pageFooters);
        }

        $this->pageFooters[$page_footer_name] = new ReporticoPageEnd($line, $page_footer_text);
        return $this->pageFooters[$page_footer_name];
    }

    // -----------------------------------------------------------------------------
    // Function : createGroup
    // -----------------------------------------------------------------------------
    public function createGroup(
        $query_name = "",
        $in_group = false
    )
    {
        $this->groups[] = new ReporticoGroup($query_name, $this);
        end($this->groups);
        $ky = key($this->groups);
        return ($this->groups[$ky]);
    }

    // -----------------------------------------------------------------------------
    // Function : createGroupTrailer
    // -----------------------------------------------------------------------------
    public function createGroupTrailer($query_name, $trailer_column, $value_column, $trailer_custom = false, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $this->checkGroupName("createGroupTrailer", $query_name);
        //$this->check_column_name("createGroupTrailer", $trailer_column);
        if ( $value_column )
            $this->checkColumnName("createGroupTrailer", $value_column);

        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);
        if ( $value_column )
            $qc = ReporticoUtility::getQueryColumn($value_column, $this->columns);
        else
            $qc = ReporticoUtility::getFirstColumn( $this->columns);
        //$trl = ReporticoUtility::getQueryColumn($trailer_column, $this->columns )) )
        $grp->addTrailer($trailer_column, $qc, $trailer_custom, $show_in_html, $show_in_pdf);
    }

    // -----------------------------------------------------------------------------
    // Function : delete_group_trailer_by_number
    // -----------------------------------------------------------------------------
    public function deleteGroupTrailerByNumber($query_name, $trailer_number)
    {
        $tn = (int)$trailer_number;
        if (!$this->checkGroupNameR("createGroupTrailer", $query_name)) {
            ReporticoApp::handleError("Failure in Group Column Trailer: Unknown Group Name $query_name");
            return;
        }

        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);

        $ct = 0;
        $k = false;
        $updtr = false;
        foreach ($grp->trailers as $k => $v) {
            if ($ct == $tn) {
                array_splice($grp->trailers, $k, 1);
                return;
            }
            $ct++;
        }

    }
    // -----------------------------------------------------------------------------
    // Function : set_group_trailer_by_number
    // -----------------------------------------------------------------------------
    public function setGroupTrailerByNumber($query_name, $trailer_number, $trailer_column, $value_column, $trailer_custom = false, $show_in_html, $show_in_pdf)
    {
        $tn = (int)$trailer_number;
        if (!$this->checkGroupNameR("createGroupTrailer", $query_name)) {
            ReporticoApp::handleError("Failure in Group Column Trailer: Unknown Group Name $query_name");
            return;
        }

        if (!$this->checkColumnNameR("createGroupTrailer", $trailer_column)) {
            ReporticoApp::handleError("Failure in Group Column Trailer: Unknown Column $trailer_column");
            return;
        }

        if (!$this->checkColumnNameR("createGroupTrailer", $value_column)) {
            ReporticoApp::handleError("Failure in Group Column Trailer: Unknown Column $value_column");
            return;
        }

        //$grp =& $this->groups[$query_name] ;
        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);
        $col = ReporticoUtility::getQueryColumn($value_column, $this->columns);

        $trailer = array();
        $trailer["GroupTrailerValueColumn"] = $col;
        $trailer["GroupTrailerDisplayColumn"] = $trailer_column;
        $trailer["GroupTrailerCustom"] = $trailer_custom;
        $trailer["ShowInHTML"] = $show_in_html;
        $trailer["ShowInPDF"] = $show_in_pdf;

        $ct = 0;
        $k = false;
        $updtr = false;
        $looping = true;

        foreach ($grp->trailers as $k => $v) {
            if ($k == $tn) {
                $grp->trailers[$k] = $trailer;
                return;
            }
        }

    }

    // -----------------------------------------------------------------------------
    // Function : createGroupHeader
    // -----------------------------------------------------------------------------
    public function createGroupHeader($query_name, $header_column, $header_custom = false, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $this->checkGroupName("createGroupHeader", $query_name);
        if ( $header_column )
            $this->checkColumnName("createGroupHeader", $header_column);

        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);
        if ( $header_column )
            $col = ReporticoUtility::getQueryColumn($header_column, $this->columns);
        else
            $col = ReporticoUtility::getFirstColumn($this->columns);

        $grp->addHeader($col, $header_custom, $show_in_html, $show_in_pdf);
    }

    // -----------------------------------------------------------------------------
    // Function : set_group_header_by_number
    // -----------------------------------------------------------------------------
    public function setGroupHeaderByNumber($query_name, $header_number, $header_column, $header_custom, $show_in_html = "yes", $show_in_pdf = "yes")
    {
        $hn = (int)$header_number;
        if (!$this->checkGroupNameR("createGroupHeader", $query_name)) {
            ReporticoApp::handleError("Failure in Group Column Header: Unknown Group Name $query_name");
            return;
        }

        if (!$this->checkColumnNameR("createGroupHeader", $header_column)) {
            ReporticoApp::handleError("Failure in Group Column Header: Unknown Column $header_column");
            return;
        }

        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);
        $col = ReporticoUtility::getQueryColumn($header_column, $this->columns);
        $header = array();
        $header["GroupHeaderColumn"] = $col;
        $header["GroupHeaderCustom"] = $header_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
        $grp->headers[$hn] = $header;
        //$this->headers[] = $header;
    }

    // -----------------------------------------------------------------------------
    // Function : deleteGroupHeaderByNumber
    // -----------------------------------------------------------------------------
    public function deleteGroupHeaderByNumber($query_name, $header_number)
    {

        $hn = (int)$header_number;
        if (!$this->checkGroupNameR("delete_group_header", $query_name)) {
            ReporticoApp::handleError("Failure in Group Column Header: Unknown Group Name $query_name");
            return;
        }

        $grp = ReporticoUtility::getGroupColumn($query_name, $this->groups);
        array_splice($grp->headers, $hn, 1);
    }

    // -----------------------------------------------------------------------------
    // Function : createGroup_column
    // -----------------------------------------------------------------------------
    public function createGroupColumn(
        $query_name = "",
        $assoc_column = "",
        $summary_columns = "",
        $header_columns = ""
    )
    {
        $col = &$this->getColumn($query_name);
        $col->assoc_column = $assoc_column;
        $col->header_columns = explode(',', $header_columns);
        $col->summary_columns = explode(',', $summary_columns);

        $this->group_set[] = &$col;
    }

    // -----------------------------------------------------------------------------
    // Function : create_order_column
    // -----------------------------------------------------------------------------
    public function createOrderColumn(
        $query_name = "",
        $order_type = "ASC"
    )
    {
        $col = &$this->getColumn($query_name);

        $order_type = strtoupper($order_type);
        if ($order_type == "UP") {
            $order_type = "ASC";
        }

        if ($order_type == "ASCENDING") {
            $order_type = "ASC";
        }

        if ($order_type == "DOWN") {
            $order_type = "DESC";
        }

        if ($order_type == "DESCENDING") {
            $order_type = "DESC";
        }

        $col->order_type = $order_type;

        $this->order_set[] = &$col;

    }

    // -----------------------------------------------------------------------------
    // Function : remove_group
    // -----------------------------------------------------------------------------
    public function removeGroup(
        $query_name = ""
    )
    {
        if (!($grp = ReporticoUtility::getGroupColumn($query_name, $this->groups))) {
            return;
        }

        $cn = 0;
        $ct = 0;
        foreach ($this->groups as $k => $v) {
            if ($k->group_name == $query_name) {
                $cn = $ct;
                break;
            }

            $ct++;
        }

        // finally remove the column
        array_splice($this->groups, $cn, 1);
    }
    // -----------------------------------------------------------------------------
    // Function : remove_column
    // -----------------------------------------------------------------------------
    public function removeColumn(
        $query_name = ""
    )
    {
        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            return;
        }

        $ct = 0;
        $cn = 0;
        foreach ($this->columns as $k => $v) {
            if ($v->query_name == $query_name) {
                $cn = $ct;
                break;
            }
            $ct++;
        }

        // Remove all order bys to this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->order_set as $k => $v) {
                if ($v->query_name == $query_name) {
                    array_splice($this->order_set, $k, 1);
                    $deleting = true;
                    break;
                }
            }
        }

        // Remove all assignments to this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->assignment as $k => $v) {
                if ($v->query_name == $query_name) {
                    array_splice($this->assignment, $k, 1);
                    $deleting = true;
                    break;
                }
            }
        }

        // Remove all group headers for this column
        $deleting = true;
        while ($deleting) {
            $deleting = false;
            foreach ($this->groups as $k => $v) {
                foreach ($v->headers as $k1 => $v1) {
                    if ($v1->query_name == $query_name) {
                        array_splice($this->groups[$k]->headers, $k1, 1);
                        $deleting = true;
                        break;
                    }
                }

                $cn1 = 0;
                foreach ($v->trailers as $k1 => $v1) {
                    if ($v1["GroupTrailerDisplayColumn"] == $query_name) {
                        array_splice($this->groups[$k]->trailers, $cn1, 1);
                        $deleting = true;
                        break;
                    }

                    foreach ($v->trailers[$k1] as $k2 => $v2) {
                        if ($v2->query_name == $query_name) {
                            array_splice($this->groups[$k]->trailers[$k1], $k2, 1);
                            $deleting = true;
                            break;
                        }
                    }
                    $cn1++;

                    if ($deleting) {
                        break;
                    }

                }

            }
        }

        // finally remove the column
        array_splice($this->columns, $cn, 1);
    }

    // -----------------------------------------------------------------------------
    // Function : createCriteriaColumn
    // -----------------------------------------------------------------------------
    public function createCriteriaColumn(
        $query_name = "",
        $table_name = "table_name",
        $column_name = "column_name",
        $column_type = "string",
        $column_length = 0,
        $column_mask = "MASK",
        $in_select = true
    )
    {
        // Default Query Column Name to Datbase Column Name ( if not set )

        // If the column already exists we are probably importing over the
        // top of an existing query, so just update it
        if ($cl = ReporticoUtility::getQueryColumn($query_name, $this->columns)) {
            $cl->table_name = $table_name;
            $cl->column_name = $column_name;
            $cl->column_type = $column_type;
            $cl->column_length = $column_length;
            $cl->column_mask = $column_mask;
        } else {
            $this->columns[] = new CriteriaColumn
            (
                $this,
                $query_name,
                $table_name,
                $column_name,
                $column_type,
                $column_length,
                $column_mask,
                $in_select
            );
            end($this->columns);
            $ky = key($this->columns);
            $this->display_order_set["itemno"][] = count($this->columns);
            $this->display_order_set["column"][] = &$this->columns[$ky];
        }
    }

    // -----------------------------------------------------------------------------
    // Function : create_query_column
    // -----------------------------------------------------------------------------
    public function createQueryColumn(
        $query_name = "",
        $table_name = "table_name",
        $column_name = "column_name",
        $column_type = "string",
        $column_length = 0,
        $column_mask = "MASK",
        $in_select = true
    )
    {
        // Default Query Column Name to Datbase Column Name ( if not set )
        $this->columns[] = new QueryColumn
        (
            $query_name,
            $table_name,
            $column_name,
            $column_type,
            $column_length,
            $column_mask,
            $in_select
        );
        end($this->columns);
        $ky = key($this->columns);
        $this->display_order_set["itemno"][] = count($this->columns);
        $this->display_order_set["column"][] = &$this->columns[$ky];
    }

    // -----------------------------------------------------------------------------
    // Function : setColumnOrder
    // -----------------------------------------------------------------------------
    public function setColumnOrder(
        $query_name = "",
        $order,
        $insert_before = true
    )
    {
        //echo "=========================================<br>";
        //echo "set order $query_name - $order<br>";
        // Changes the display order of the column
        // by resetting display_order_set
        reset($this->display_order_set);

        $ct = count($this->display_order_set["itemno"]);
        $c = &$this->display_order_set;
        for ($i = 0; $i < $ct; $i++) {
            if ($c["column"][$i]->query_name == $query_name) {
                if ($c["itemno"][$i] < $order) {
                    //echo $c["itemno"][$i]." up1  ".$c["column"][$i]->query_name." $i<br>";
                    $c["itemno"][$i] = $order + 1;
                } else {
                    //echo $c["itemno"][$i]." set  ".$c["column"][$i]->query_name." $i<br>";
                    $c["itemno"][$i] = $order;
                }
            } else
                if (($c["itemno"][$i] >= $order && $insert_before)
                    ||
                    ($c["itemno"][$i] > $order && !$insert_before)) {
                    //echo $c["itemno"][$i]." up5  ".$c["column"][$i]->query_name." $i<br>";
                    $c["itemno"][$i] += 500;
                }
            //else
            //echo $c["itemno"][$i]." leave ".$c["column"][$i]->query_name." $i<br>";
        }

        // Now resort the list
        $n = array_multisort(
            $this->display_order_set["itemno"], SORT_ASC, SORT_NUMERIC,
            $this->display_order_set["column"]
        );

        for ($i = 0; $i < $ct; $i++) {
            $c["itemno"][$i] = $i + 1;
        }
        foreach ($this->display_order_set["itemno"] as $val) {
            $vv = $val - 1;
            //echo " SET $val ",  $this->display_order_set["column"][$vv]->query_name. " - ".$val."/". $this->display_order_set["itemno"][$vv]."<BR>";
            $ct++;
        }

    }

    // Work out whether we are in ajax mode. This is so if either
    // ajax mode has been specified or there is an ajax_script_url specified
    // or reportico has been called by an ajax script using the reportico_ajax_called=1
    // url request parameter
    public function deriveAjaxOperation()
    {
        $sessionClass = ReporticoSession();

        // Fetch URL path to reportico and set URL path to the runner
        $this->reportico_url_path = $sessionClass::registerSessionParam("reportico_url_path",  ReporticoUtility::getReporticoUrlPath());
        if (!$this->url_path_to_reportico_runner) {
            $this->url_path_to_reportico_runner = $this->reportico_url_path . "run.php";
        }

        // If full ajax mode is requested but no ajax url is passed then defalt the ajax url to the default reportico runner
        $sessionClass::registerSessionParam("reportico_ajax_script_url", $this->reportico_ajax_script_url);

        $this->reportico_ajax_script_url = $sessionClass::getReporticoSessionParam("reportico_ajax_script_url");

        if ($this->reportico_ajax_script_url && !$this->reportico_ajax_mode) {
            $this->reportico_ajax_mode = "standalone";
        }

        if (!$this->reportico_ajax_script_url) {
            $this->reportico_ajax_script_url = $this->url_path_to_reportico_runner;
        }

        if ($this->reportico_ajax_called && !$this->reportico_ajax_mode) {
            $this->reportico_ajax_mode = "standalone";
        }

        $this->reportico_ajax_preloaded = ReporticoUtility::getRequestItem("reportico_ajax_called", $this->reportico_ajax_preloaded);
        if ($sessionClass::getReporticoSessionParam("reportico_ajax_called")) {
            $this->reportico_ajax_mode = "standalone";
        }
    }

    // -----------------------------------------------------------------------------
    // Function : initialize_panels
    //
    // Based on whether Reportico is in criteria entry, report run or other mode
    // Flag what browser panels should be displayed
    // -----------------------------------------------------------------------------
    public function initializePanels($mode)
    {
        $sessionClass = ReporticoSession();

        // Initialize authenticator
        if ( !$this->authenticator ) {
            $this->authenticator = Authenticator::initialize($this->authenticator_class, $this);
            Authenticator::login();
        }

        // Initialize templating engine
        $template = new ReporticoTemplateTwig($this->templateViewPath, $this->templateCachePath, $this->getTheme(), $this->disableThemeCaching);
        $template->engine = $this;
        $this->template = &$template;

        $forward_url_params = $sessionClass::sessionRequestItem('forward_url_get_parameters', $this->forward_url_get_parameters);
        $forward_url_params_graph = $sessionClass::sessionRequestItem('forward_url_get_parameters_graph', $this->forward_url_get_parameters_graph);
        $forward_url_params_dbimage = $sessionClass::sessionRequestItem('forward_url_get_parameters_dbimage', $this->forward_url_get_parameters_dbimage);

        //$template->assign('REPORTICO_VERSION', $this->version);
        //$template->assign('REPORTICO_SITE', $this->url_site);

        // Assign user parameters to template
        if ($this->user_parameters && is_array($this->user_parameters)) {
            foreach ($this->user_parameters as $k => $v) {
                $param = preg_replace("/ /", "_", $k);
                $template->assign('USER_' . $param, $v);
            }
        }

        // Date format for ui Datepicker
        $template->assign('PDF_DELIVERY_MODE', $this->pdf_delivery_mode);

        // Set template variables
        $template->assign('SCRIPT_SELF', $this->url_path_to_calling_script);

        $template->assign('REPORTICO_AJAX_MODE', $this->reportico_ajax_mode);
        $template->assign('REPORTICO_AJAX_CALLED', $this->reportico_ajax_called);

        $template->assign('PRINTABLE_HTML', false);
        if (ReporticoUtility::getRequestItem("printable_html")) {
            $template->assign('PRINTABLE_HTML', true);
        }

        $this->url_path_to_assets = $sessionClass::registerSessionParam("url_path_to_assets", $this->url_path_to_assets);
        $this->url_path_to_templates = $sessionClass::registerSessionParam("url_path_to_templates", $this->url_path_to_templates);
        $this->jquery_preloaded = $sessionClass::registerSessionParam("jquery_preloaded", $this->jquery_preloaded);
        $this->bootstrap_preloaded = $sessionClass::registerSessionParam("bootstrap_preloaded", $this->bootstrap_preloaded);
        $this->report_from_builder = $sessionClass::registerSessionParam("report_from_builder", $this->report_from_builder);


        // In frameworks we dont want to load jquery when its intalled once when the module load
        // so flag this unless specified in new_reportico_window
        $template->assign('REPORTICO_STANDALONE_WINDOW', false);
        $template->assign('REPORTICO_AJAX_PRELOADED', $this->reportico_ajax_preloaded);
        if (ReporticoUtility::getRequestItem("new_reportico_window", false)) {
            $this->reportico_ajax_preloaded = false;
            $template->assign('REPORTICO_AJAX_PRELOADED', false);
            $template->assign('REPORTICO_STANDALONE_WINDOW', true);
            $this->jquery_preloaded = false;
            $this->bootstrap_preloaded = false;
        }

        $template->assign('STATUSMSG', '');
        $template->assign('ERRORMSG', false);
        $template->assign('DEMO_MODE', false);

        // Dont allow admin menu buttons to show in demo mode
        if ($this->allow_maintain == "DEMO") {
            $template->assign('DEMO_MODE', true);
        }

        // Use alternative location for js/css/images if specified.
        // Set stylesheet to the reportico bootstrap if bootstrap styles in place
        $this->bootstrap_styles = $sessionClass::registerSessionParam("bootstrap_styles", $this->bootstrap_styles);

        // Force reportico modals or decide based on style?
        $this->force_reportico_mini_maintains = $sessionClass::registerSessionParam("force_reportico_mini_maintains", $this->force_reportico_mini_maintains);

        // If the url to the public theme/template folder is not set, assume we are in standalone Reportico
        // add set it to be in the base folder of reportico
        if (!$this->url_path_to_assets) {
            $this->url_path_to_assets = $this->reportico_url_path . "assets";
        }

        //Define the template dir where we could find specific template css js and template files
        // if not already provided
        if ( !$this->theme_dir ) {
            $theme_dir = __DIR__."/../themes";
            $theme_dir = realpath($theme_dir);
            //if (!$this->url_path_to_templates)
                //$theme_dir = ReporticoUtility::findBestUrlInIncludePath('themes');
            $this->theme_dir = $theme_dir;
        }

        // If the url to the public theme/template folder is not set, assume we are in standalone Reportico
        // add set it to be in the base folder of reportico
        if ( !$this->url_path_to_templates ) {
            $this->url_path_to_templates = $this->reportico_url_path . "themes";
        }

        /*@todo Must be in the theme and not in the code*/
        if (!$this->bootstrap_styles) {
            $csspath = $this->url_path_to_assets . "/css/reportico.css";
            if ($this->url_path_to_assets) {
                $csspath = $this->url_path_to_assets . "/css/reportico.css";
            } else {
                $csspath = $this->reportico_url_path . "/" . ReporticoUtility::findBestUrlInIncludePath("/css/reportico.css");
            }

        } else {
            if ($this->url_path_to_assets) {
                $csspath = $this->url_path_to_assets . "/css/reportico.css";
            } else {
                $csspath = $this->reportico_url_path . "/" . ReporticoUtility::findBestUrlInIncludePath("css/reportico.css");
            }

        }


        // Set charting engine
        $template->assign('REPORTICO_CHARTING_ENGINE', $this->charting_engine_html);

        // Set on/off template elements
        foreach ($this->output_template_parameters as $k => $v) {
            $template->assign(strtoupper($k), $v);
        }
        if ($this->url_path_to_assets) {
            $jspath = $this->url_path_to_assets . "/js";
            $template->assign('JSPATH', $jspath);
        } else {
            $jspath = ReporticoUtility::findBestUrlInIncludePath("js/reportico.js");
            if ($jspath) {
                $jspath = dirname($jspath);
            }

            $template->assign('JSPATH', $this->reportico_url_path . $jspath);
        }

        // Store any menu page URL, in ajax mode links go through the general ajax link, otherwise go through calling script
        $calling_script = $this->getActionUrl();
        if (preg_match("/\?/", $this->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        $this->prepare_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $this->menu_url = $calling_script . "{$url_join_char}execute_mode=MENU&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $this->admin_menu_url = $calling_script . "{$url_join_char}execute_mode=MENU&project=admin&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $this->configure_project_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&xmlin=configureproject.xml&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $this->delete_project_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&xmlin=deleteproject.xml&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $this->create_report_url = $calling_script . "{$url_join_char}execute_mode=PREPARE&xmlin=&reportico_session_name=" . $sessionClass::reporticoSessionName();

        if ($forward_url_params) {
            $this->prepare_url .= "&" . $forward_url_params;
            $this->menu_url .= "&" . $forward_url_params;
            $this->admin_menu_url .= "&" . $forward_url_params;
            $this->configure_project_url .= "&" . $forward_url_params;
            $this->delete_project_url .= "&" . $forward_url_params;
            $this->create_report_url .= "&" . $forward_url_params;
        }

        // Generate dropdown menu strip in menu or prepare mode
        if (ReporticoApp::get("dropdown_menu") && !$this->dropdown_menu) {
            $this->dropdown_menu = ReporticoApp::get("dropdown_menu");
        }

        $template->assign('REPORTICO_BOOTSTRAP_MODAL', true);
        if (!$this->bootstrap_styles || $this->force_reportico_mini_maintains) {
            $template->assign('REPORTICO_BOOTSTRAP_MODAL', false);
        }

        // If no admin password then force user to enter one and  a language
        if (ReporticoApp::getConfig("project") == "admin" && ReporticoApp::getConfig("admin_password") == "PROMPT") {

            Authenticator::flag("show-languages");
            Authenticator::flag("show-set-admin-password");
            Authenticator::flag("show-set-admin-password-error");

        }

        if ( is_object($this->datasource)
            && (
                get_class($this->datasource) == "stdClass"
                ||  $this->datasource->connect()
                || $mode != "MAINTAIN"
            )
        ) {
            // Store connection session details
            if( get_class($this->datasource) != "stdClass" ){
                $sessionClass::setReporticoSessionParam("database", $this->datasource->database);
                $sessionClass::setReporticoSessionParam("hostname", $this->datasource->host_name);
                $sessionClass::setReporticoSessionParam("driver", $this->datasource->driver);
                $sessionClass::setReporticoSessionParam("server", $this->datasource->server);
                $sessionClass::setReporticoSessionParam("protocol", $this->datasource->protocol);
            }
        }

    }

    // -----------------------------------------------------------------------------
    // If initial starting parameters are given (initial project, access_mode then
    // only use them if this is the first use of the session, other wise clear them
    // -----------------------------------------------------------------------------
    public function handleInitialSettings()
    {
        $sessionClass = ReporticoSession();

        if (!$this->framework_parent && !$sessionClass::getReporticoSessionParam("awaiting_initial_defaults")) {
            $this->initial_project = false;
            $this->initial_sql = false;
            $this->initial_execute_mode = false;
            $this->initial_report = false;
            $this->initial_project_password = false;
            $this->initial_output_style = false;
            $this->initial_output_format = false;
            $this->initial_show_detail = false;
            $this->initial_show_graph = false;
            $this->initial_show_group_headers = false;
            $this->initial_show_group_trailers = false;
            $this->initial_show_column_headers = false;
            $this->initial_show_criteria = false;
            $this->initial_execution_parameters = false;
            $this->access_mode = false;
        }
    }

    // -----------------------------------------------------------------------------
    // If initial starting parameters are given (initial project, access_mode then
    // only use them if this is the first use of the session, other wise clear them
    // -----------------------------------------------------------------------------
    public function handledInitialSettings()
    {
        $sessionClass = ReporticoSession();

        if ($sessionClass::getReporticoSessionParam("awaiting_initial_defaults")) {
            $sessionClass::setReporticoSessionParam("awaiting_initial_defaults", false);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : initialize_connection
    // -----------------------------------------------------------------------------
    public function initializeConnection()
    {
        return;
    }

    // -----------------------------------------------------------------------------
    // Function : check_criteria_validity
    // Ensures that Mandatory criteria is met
    // -----------------------------------------------------------------------------
    public function checkCriteriaValidity()
    {
        foreach ($this->lookup_queries as $col) {
            if ($col->required == "yes") {
                //ReporticoApp::handleError( "Mandatory" );
                if (!$this->lookup_queries[$col->query_name]->column_value) {
                    if (true || ReporticoUtility::getRequestItem("new_reportico_window", false)) {
                        $this->http_response_code = 500;
                        $this->return_to_caller = true;
                        ReporticoApp::handleError(ReporticoLang::templateXlate("REQUIRED_CRITERIA") . " - " . ReporticoLang::translate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", "")));
                        //echo '<div class="reportico-error-box">'.ReporticoLang::templateXlate("REQUIRED_CRITERIA")." - ".ReporticoLang::translate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", ""))."</div>";
                        return;
                    } else {
                        ReporticoApp::handleError(ReporticoLang::templateXlate("REQUIRED_CRITERIA") . " - " . ReporticoLang::translate($this->lookup_queries[$col->query_name]->deriveAttribute("column_title", ""))
                            , E_USER_ERROR);
                    }

                }
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : handle_xml_input
    // -----------------------------------------------------------------------------
    public function handleXmlQueryInput($mode = false)
    {
        global $gcalled;
        if ( $gcalled )
            return;
        $gcalled = true;
        $sessionClass = ReporticoSession();

        if ($mode == "MENU" && $sessionClass::issetReporticoSessionParam("xmlin")) //if ( $mode == "MENU" && array_key_exists("xmlin", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $sessionClass::unsetReporticoSessionParam("xmlin");
        }

        if ($mode == "ADMIN" && $sessionClass::issetReporticoSessionParam("xmlin")) //if ( $mode == "ADMIN" && array_key_exists("xmlin", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $sessionClass::unsetReporticoSessionParam("xmlin");
        }

        // See if XML needs to be read in
        $this->xmlinput = false;
        $this->sqlinout = false;

        if ($sessionClass::issetReporticoSessionParam("xmlin")) //if ( array_key_exists("xmlin", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $this->xmlinput = $sessionClass::getReporticoSessionParam("xmlin");
            $sessionClass::setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if ($sessionClass::issetReporticoSessionParam("sqlin")) //if ( array_key_exists("sqlin", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $this->sqlinput = $sessionClass::getReporticoSessionParam("sqlin");
        }

        if (array_key_exists("xmlin", $_REQUEST)) {

            $sessionClass::setReporticoSessionParam("firstTimeIn", true);
            $this->xmlinput = $_REQUEST["xmlin"];

            $sessionClass::unsetReporticoSessionParam("xmlintext");
            $sessionClass::setReporticoSessionParam("xmlin", $this->xmlinput);
            $sessionClass::setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if ($this->initial_report) {
            $this->xmlinput = $this->initial_report;
            $sessionClass::setReporticoSessionParam("xmlin", $this->xmlinput);
            $sessionClass::setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        if ($this->initial_sql) {
            $this->sqlinput = false;
            if (!$sessionClass::getReporticoSessionParam("sqlin")) {
                $sessionClass::setReporticoSessionParam("sqlin", $this->initial_sql);
            }

            $this->sqlinput = $sessionClass::getReporticoSessionParam("sqlin", $this->initial_sql);
            $sessionClass::setReporticoSessionParam("xmlin", false);
            $sessionClass::setReporticoSessionParam("xmlout", false);
        }

        if ($this->user_template == "_DEFAULT") {
            $this->user_template = false;
            $sessionClass::setReporticoSessionParam('reportico_template', $this->user_template);
        } else if (!$this->user_template) {
            $this->user_template = $sessionClass::sessionRequestItem('reportico_template', $this->user_template);
        }
        if (array_key_exists("partial_template", $_REQUEST)) {
            $this->user_template = $_REQUEST["partial_template"];
        }

        // Set template from request if specified
        if ($sessionClass::issetReporticoSessionParam("template")) //if ( array_key_exists("template", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $this->user_template = $sessionClass::getReporticoSessionParam("template");
            $sessionClass::setReporticoSessionParam("template", $this->user_template);
        }
        if (array_key_exists("template", $_REQUEST)) {
            $this->user_template = $_REQUEST["template"];
            $sessionClass::setReporticoSessionParam("template", $this->user_template);
        }

        if ($this->xmlinput && !preg_match("/\.xml$/", $this->xmlinput)) {
            $this->xmlinput .= ".xml";
        }

        // Now work out out file...
        if (!$this->xmloutfile) {
            $this->xmloutfile = $this->xmlinput;
        }

        if ($sessionClass::issetReporticoSessionParam("xmlout")) //if ( array_key_exists("xmlout", $_SESSION[$sessionClass::reporticoNamespace()]) )
        {
            $this->xmloutfile = $sessionClass::getReporticoSessionParam("xmlout");
        }

        if (array_key_exists("xmlout", $_REQUEST) && (array_key_exists("submit_xxx_SAVE", $_REQUEST) || array_key_exists("submit_xxx_PREPARESAVE", $_REQUEST))) {
            $this->xmloutfile = $_REQUEST["xmlout"];
            $sessionClass::setReporticoSessionParam("xmlout", $this->xmloutfile);
        }

        if (!$this->report_from_builder ) {
            $this->xmlintext = false;
        }

        if ( $sessionClass::issetReporticoSessionParam("xmlintext")) {

            if (($this->xmlintext = $sessionClass::getReporticoSessionParam("xmlintext"))) {
                $this->xmlinput = false;
            }
        }

        // Has new report been pressed ? If so clear any existing report
        // definitions
        if (array_key_exists("submit_maintain_NEW", $_REQUEST) ||
            array_key_exists("new_report", $_REQUEST)) {
            $this->xmlinput = false;
            $this->xmlintext = false;
            $this->xmloutfile = false;
            $sessionClass::setReporticoSessionParam("xmlin", $this->xmlinput);
            $sessionClass::setReporticoSessionParam("xmlout", $this->xmlinput);
        }

        // apply default customized reportico actions if not using xml text in session
        $do_defaults = true;

        $this->report_from_builder_first_call = false;
        if ( !$this->report_from_builder_first_call ) {
            if ($this->sqlinput) {
                $this->importSQL($this->sqlinput);
            } else if ($this->xmlinput || $this->xmlintext) {
                if ($this->getExecuteMode() == "MAINTAIN") {
                    $do_defaults = false;
                }
                //else if ( $this->xmlintext )
                //$do_defaults = false;

                $this->xmlin = new XmlReader($this, $this->xmlinput, $this->xmlintext, false, false);
                $this->xmlin->xml2query();
            } else {
                if ($this->getExecuteMode() == "MAINTAIN") {
                    $do_defaults = false;
                }

                $this->xmlin = new XmlReader($this, false, "", false, false);
                $this->xmlin->xml2query();
            }
        }

        // Custom query stuff loaded from project config.php.
        if ($do_defaults) {

            $this->applyOutputOptionsFromConfig();

        }

        if ($this->xmlinput == "deleteproject.xml" ||
            $this->xmlinput == "configureproject.xml" ||
            $this->xmlinput == "createtutorials.xml" ||
            $this->xmlinput == "createproject.xml" ||
            $this->xmlinput == "generate_tutorial.xml") {

            Authenticator::flag("admin-report-selected");
        }
        //Authenticator::show();
    }

    // -----------------------------------------------------------------------------
    // Function : Set page header, footers and other options form config.php
    // -----------------------------------------------------------------------------
    function applyOutputOptionsFromConfig()
    {

        $keyct = 1;
        $output_config = array();

        if ($this->pdf_engine == "tcpdf" && $this->target_format == "PDF")
            $output_config = ReporticoApp::getConfig("output_sections_tcpdf");
        else
            $output_config = ReporticoApp::getConfig("output_sections");

        if ($output_config) {

            foreach ($output_config as $k => $v) {

                if ($k == "page-header-block") {

                    foreach ($v as $header) {

                        $key = "AUTO$keyct";
                        $content = $header["content"];
                        if (isset($header["content"]))
                            $content .= "{STYLE {$header["styles"]}}";
                        $this->createPageHeader($key, 1, $content);
                        $this->setPageHeaderAttribute($key, "ShowInHTML", "yes");
                        $this->setPageHeaderAttribute($key, "ShowInPDF", "yes");
                        $keyct++;

                    }

                }

                if ($k == "page-footer-block") {

                    foreach ($v as $footer) {

                        $key = "AUTO$keyct";
                        $content = $footer["content"];
                        if (isset($footer["content"]))
                            $content .= "{STYLE {$footer["styles"]}}";
                        $this->createPageFooter($key, 1, $content);
                        $this->setPageFooterAttribute($key, "ShowInHTML", "yes");
                        $this->setPageFooterAttribute($key, "ShowInPDF", "yes");
                        $keyct++;

                    }

                }

                if ($k == "styles") {

                    foreach ($v as $type => $styleset) {
                        $this->applyStyleset(strtoupper($type), $styleset["style"]);
                    }

                }
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : get_panel
    // -----------------------------------------------------------------------------
    public function &getPanel($panel = false, $section = "ALL")
    {
        $txt = "";

        switch ($section) {
            case "PRE":
                $txt = $this->panels[$panel]->pre_text;
                break;
            case "POST":
                $txt = $this->panels[$panel]->post_text;
                break;
            default:
                $txt = $this->panels[$panel]->full_text;
                break;
        }
        return $txt;
    }

    // -----------------------------------------------------------------------------
    // Function : execute
    // -----------------------------------------------------------------------------
    public function importSQL($sql)
    {
        $this->setProjectEnvironment($this->initial_project, $this->projects_folder, $this->admin_projects_folder);
        $p = new SqlParser($sql);
        if ($p->parse(false)) {
            $p->importIntoQuery($this);
        }
        $this->initialize_on_execute = false;
    }

    // -----------------------------------------------------------------------------
    // Function : Clears local arrays ready for a new report run
    // -----------------------------------------------------------------------------
    public function initialize()
    {

        if (!$this->initialize_on_execute)
            return;

        $this->panels = array();
        $this->targets = array();
        $this->assignment = array();
        $this->groups = array();
        $this->columns = array();
        $this->criteria_links = array();
        $this->lookup_queries = array();
        $this->tables = array();
        $this->display_order_set = array();
        $this->order_set = array();
        $this->group_set = array();
        $this->pageHeaders = array();
        $this->pageFooters = array();
        $this->lookup_queries = array();
        $this->clone_columns = array();
        $this->pre_sql = array();
        $this->graphs = array();
        $this->menuitems = array();
        $this->projectitems = array();
        $this->groupvals = array();
        $this->panels = array();
        $this->targets = array();
        $this->assignment = array();
        $this->criteria_links = array();
        //$this->user_parameters = array();
        // an array of available databases to connect ot
        $this->available_connections = array();
        $this->plugins = array();

    }

    // -----------------------------------------------------------------------------
    // Function : render
    // -----------------------------------------------------------------------------
    public function render($mode = false, $draw = true)
    {
        $sessionClass = ReporticoSession();

        // If running with just a query ( and no project report ) then we need to generate an XML report equivalent
        // to work with the on the fly query. Therefore force an XML regeneration
        if ( !$sessionClass::issetReporticoSessionParam("xmlintext") && !$this->initial_report ) {
            $xmlout = new XmlWriter($this);
            $xmlout->prepareXmlData();
            $sessionClass::registerSessionParam("reportConfig", htmlspecialchars($xmlout->getXmldata()));
            $this->reportDefinitionLoaded = true;
            $sessionClass::registerSessionParam("xmlintext", $xmlout->getXmldata());
        }

        // Override the passed execute mode if specified in url params
        if ( $mode = ReporticoUtility::getRequestItem("execute_mode", $mode) ) {
            $this->initial_execute_mode = $mode;
        }

        // As this has come from a builder session, if there is no project then the access mode
        // has to be either REPORTOUTPUT for execute or ONEREPORT if its prepare
        if ( !$this->initial_project ) {
            if ( $mode == "EXECUTE" ){
                $this->access_mode = "REPORTOUTPUT";
                $this->initial_role = "report-output";
            } else {
                $this->access_mode = "report";
                $this->initial_role = "report";
            }
        }

        $this->clear_reportico_session = false;
        ReporticoApp::setConfig('allow_output', true);

        $this->execute($mode, $draw);
    }

    // -----------------------------------------------------------------------------
    // Function : execute
    // -----------------------------------------------------------------------------
    public function execute($mode = false, $draw = true)
    {
        $sessionClass = ReporticoSession();
        $this->initialize();


        if (method_exists($sessionClass, "switchToRequestedNamespace"))
            $this->session_namespace = $sessionClass::switchToRequestedNamespace($this->session_namespace);

        if ($this->session_namespace) {
            ReporticoApp::set("session_namespace", $this->session_namespace);
        }

        if (ReporticoApp::get("session_namespace")) {
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
        }

        // If a session namespace doesnt exist create one
        if (!$sessionClass::existsReporticoSession() || isset($_REQUEST['clear_session']) || $this->clear_reportico_session) {
            $namespace = ReporticoApp::get("session_namespace");
            ReporticoApp::set("session_namespace", $namespace);
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
            $sessionClass::initializeReporticoNamespace(ReporticoApp::get("session_namespace_key"));
        }

        // Work out the mode (ADMIN, PREPARE, MENU, EXECUTE, MAINTAIN based on all parameters )
        if (!$mode) {
            $mode = $this->getExecuteMode();
        }

        $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
        set_exception_handler("Reportico\Engine\ReporticoApp::ExceptionHandler");

        // If new session, we need to use initial project, report etc, otherwise ignore them
        $this->handleInitialSettings();

        // load plugins
        $this->loadPlugins();

        // Fetch project config
        $this->admin_projects_folder = $sessionClass::registerSessionParam("admin_projects_folder", $this->admin_projects_folder);
        $this->projects_folder = $sessionClass::registerSessionParam("projects_folder", $this->projects_folder);

        $this->setProjectEnvironment($this->initial_project, $this->projects_folder, $this->admin_projects_folder);

        $this->external_user = $sessionClass::registerSessionParam("external_user", $this->external_user);
        $this->external_param1 = $sessionClass::registerSessionParam("external_param1", $this->external_param1);
        $this->external_param2 = $sessionClass::registerSessionParam("external_param2", $this->external_param2);
        $this->external_param3 = $sessionClass::registerSessionParam("external_param3", $this->external_param3);

        $this->theme = $sessionClass::registerSessionParam("theme", $this->theme);
        $this->disableThemeCaching = $sessionClass::registerSessionParam("disableThemeCaching", $this->disableThemeCaching);

        $this->pdf_engine = $sessionClass::registerSessionParam("pdf_engine", $this->pdf_engine);
        $this->pdf_phantomjs_path = $sessionClass::registerSessionParam("pdf_phantomjs_path", $this->pdf_phantomjs_path);
        $this->pdf_delivery_mode = $sessionClass::registerSessionParam("pdf_delivery_mode", $this->pdf_delivery_mode);
        $this->user_parameters = $sessionClass::registerSessionParam("user_parameters", $this->user_parameters);
        $this->dropdown_menu = $sessionClass::registerSessionParam("dropdown_menu", $this->dropdown_menu);
        $this->static_menu = $sessionClass::registerSessionParam("static_menu", $this->static_menu);
        $this->charting_engine = $sessionClass::registerSessionParam("charting_engine", $this->charting_engine);
        $this->charting_engine_html = $sessionClass::registerSessionParam("charting_engine_html", $this->charting_engine_html);
        $this->output_template_parameters = $sessionClass::registerSessionParam("output_template_parameters", $this->output_template_parameters);

        $this->dynamic_grids = $sessionClass::registerSessionParam("dynamic_grids", $this->dynamic_grids);
        $this->dynamic_grids_sortable = $sessionClass::registerSessionParam("dynamic_grids_sortable", $this->dynamic_grids_sortable);
        $this->dynamic_grids_searchable = $sessionClass::registerSessionParam("dynamic_grids_searchable", $this->dynamic_grids_searchable);
        $this->dynamic_grids_paging = $sessionClass::registerSessionParam("dynamic_grids_paging", $this->dynamic_grids_paging);
        $this->dynamic_grids_page_size = $sessionClass::registerSessionParam("dynamic_grids_page_size", $this->dynamic_grids_page_size);

        // We are in AJAX mode if it is passed throuh
        if (isset($_REQUEST["reportico_ajax_called"])) {
            $this->reportico_ajax_called = $_REQUEST["reportico_ajax_called"];
        }

        //$sessionClass::setReporticoSessionParam("reportico_ajax_called", $_REQUEST["reportico_ajax_called"] );

        // Store whether in framework
        $sessionClass::setReporticoSessionParam("framework_parent", $this->framework_parent);

        /*
        // Set access mode to decide whether to allow user to access Design Mode, Menus, Criteria or just run a single report
        echo "Access mode = $this->access_mode<BR>";
        $this->access_mode = $sessionClass::sessionItem("access_mode", $this->access_mode);
        echo "Access mode = $this->access_mode<BR>";
        if ($this->access_mode == "DEMO") {
            $this->allow_maintain = "DEMO";
        }
        */

        // Convert input and out charsets into their PHP versions
        // for later iconv use
        $this->db_charset = ReporticoLocale::dbCharsetToPhpCharset(ReporticoApp::getConfig("db_encoding", "UTF8"));
        $this->output_charset = ReporticoLocale::outputCharsetToPhpCharset(ReporticoApp::getConfig("output_encoding", "UTF8"));

        ReporticoApp::set("debug_mode", ReporticoUtility::getRequestItem("debug_mode", "0"));
        //ReporticoApp::set("debug_mode", ReporticoUtility::getRequestItem("debug_mode", "0", $this->first_criteria_selection));

        if (!$mode) {
            $mode = $this->getExecuteMode();
        }

        // If the project is the ADMIN project then the Main Menu will be the Admin Page
        if (ReporticoApp::getConfig("project") == "admin" && $mode == "MENU") {
            $mode = "ADMIN";
        }

        // Initialize authenticator
        if ( !$this->authenticator ) {
            $this->authenticator = Authenticator::initialize($this->authenticator_class, $this);
            Authenticator::login();
        }

        // If this is PREPARE mode then we want to identify whether user has entered prepare
        // screen for first time so we know whether to set defaults or not
        switch ($mode) {
            case "PREPARE":
                $this->reportProgress("Ready", "READY");
                //$this->first_criteria_selection = true;
                // Must find ALternative to this for first time in testing!!!
                //if (array_key_exists("target_format", $_REQUEST)) {
                    ////$this->first_criteria_selection = false;
                    //$sessionClass::setReporticoSessionParam("firstTimeIn", false);
                //}

                //echo "frst ".$sessionClass::getReporticoSessionParam("firstTimeIn")."<BR>";

                if (!$sessionClass::issetReporticoSessionParam("firstTimeIn")) {
                    $sessionClass::setReporticoSessionParam("firstTimeIn", true);
                }
                //echo "first time unset = ".$sessionClass::getReporticoSessionParam("firstTimeIn");

                // Default output to HTML in PREPARE mode first time in
                //if ($sessionClass::getReporticoSessionParam("firstTimeIn") && !isset($_REQUEST["target_format"])) {
//
                    //$this->target_format = "HTML";
                    //$sessionClass::setReporticoSessionParam("target_format", "HTML");
                //}

                break;

            case "EXECUTE":

                // If external page has supplied an initial output format then use it
                if ($this->initial_output_format) {
                    $_REQUEST["target_format"] = $this->initial_output_format;
                }

                // If printable HTML requested force output type to HTML
                if (ReporticoUtility::getRequestItem("printable_html")) {
                    $_REQUEST["target_format"] = "HTML";
                }

                // Prompt user for report destination if target not already set - default to HTML if not set
                if (!array_key_exists("target_format", $_REQUEST) && $mode == "EXECUTE") {
                    $_REQUEST["target_format"] = "HTML";
                }

                $this->target_format = strtoupper($_REQUEST["target_format"]);

                //if (array_key_exists("submit", $_REQUEST)) {
                    //$this->first_criteria_selection = false;
                //} else {
                    //$this->first_criteria_selection = true;
                //}

                //echo "first time ex = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                if ($sessionClass::getReporticoSessionParam("awaiting_initial_defaults")) {
                    $sessionClass::setReporticoSessionParam("firstTimeIn", true);
                    //echo "first time exa2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                } else
                    //echo "first time exb2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                    if ($sessionClass::getReporticoSessionParam("firstTimeIn") && ReporticoUtility::getRequestItem("refreshReport", false)) {
                        $sessionClass::setReporticoSessionParam("firstTimeIn", true);
                    } else {
                        $sessionClass::setReporticoSessionParam("firstTimeIn", false);
                    }
                //echo "first time ex2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                break;

            case "MAINTAIN":
                $this->reportProgress("Ready", "READY");
                //$this->first_criteria_selection = true;
                //echo "first time mn1 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                //$sessionClass::setReporticoSessionParam("firstTimeIn", true);
                //echo "first time mn2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                break;

            default:
                //echo "first time def = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                $sessionClass::setReporticoSessionParam("firstTimeIn", true);
                //echo "first time def2 = ".$sessionClass::getReporticoSessionParam("firstTimeIn");
                break;
        }

        // If xml file is used to generate the reportico_query, either by the xmlin session variable
        // or the xmlin request variable then process this before executing
        if ($mode == "MAINTAIN") {
            $_REQUEST['execute_mode'] = "$mode";
            $lastMode = $sessionClass::getReporticoSessionParam("lastMode");
            if ($lastMode == "PREPARE" ) {
                $sessionClass::setReporticoSessionParam('latestRequest', $_REQUEST);
            }
        } else
        if ($mode == "EXECUTE") {
            $_REQUEST['execute_mode'] = "$mode";

            // If executing report then stored the REQUEST parameters unless this
            // is a refresh of the report in which case we want to keep the ones already there
            $runfromcriteriascreen = ReporticoUtility::getRequestItem("user_criteria_entered", false);
            $lastMode = $sessionClass::getReporticoSessionParam("lastMode");
            $refreshmode = ReporticoUtility::getRequestItem("refreshReport", false);

            // HTML2PDF format is called locally and must pick up criteria from prior request
            //if ($this->target_format == "HTML2PDF" && $sessionClass::issetReporticoSessionParam('latestRequest')) {
            if ($this->target_format == "HTML2PDF") {
                if ( $sessionClass::getReporticoSessionParam('latestRequest') ) {
                    $_REQUEST = $sessionClass::getReporticoSessionParam('latestRequest');
                }
                $_REQUEST["target_format"] = $this->target_format;
                $_REQUEST["new_reportico_window"] = 1;
                $_REQUEST["reportico_ajax_called"] = false;
                $this->embedded_report = false;
            } else {
                if (!$runfromcriteriascreen && ($refreshmode || $this->target_format == "HTML2PDF")) {
                    $_REQUEST = $sessionClass::getReporticoSessionParam('latestRequest');
                    $_REQUEST["target_format"] = $this->target_format;
                    $_REQUEST["reportico_ajax_called"] = false;
                }
                // Store current request used for execution for next execution. So we can keep running
                // reports from the criteria screen in PDF, HTML etc repeatedly without losing the orinal //
                // prepare request
                $sessionClass::setReporticoSessionParam('latestRequest', $_REQUEST);
            }
        } else {
            if ($mode != "MODIFY" && $sessionClass::issetReporticoSessionParam('latestRequest')) {
                if ($sessionClass::getReporticoSessionParam('latestRequest')) {

                    $OLD_REQUEST = $_REQUEST;

                    // If a new report is being run dont bother trying to restore previous
                    // run crtieria
                    $lastMode = $sessionClass::getReporticoSessionParam("lastMode");

                    if (!ReporticoUtility::getRequestItem("xmlin") && !ReporticoUtility::getRequestItem("partialMaintain", false)) {
                        $_REQUEST = $sessionClass::getReporticoSessionParam('latestRequest');
                    }

                    $_REQUEST['partial_template'] = "";
                    foreach ($OLD_REQUEST as $k => $v) {
                        if ($k == 'loadTemplate') { $_REQUEST[$k] = $v; }
                        if ($k == 'saveTemplate') { $_REQUEST[$k] = $v; }
                        if ($k == 'templateAction') { $_REQUEST[$k] = $v; }

                        if ($k == 'partial_template') {
                            $_REQUEST[$k] = $v;
                        }

                        if (preg_match("/^EXPAND_/", $k)) {
                            $_REQUEST[$k] = $v;
                        }

                        if ($k == 'reportico_ajax_called') {
                            $_REQUEST[$k] = $v;
                        }
                    }
                    $_REQUEST['execute_mode'] = "$mode";
                    //$_REQUEST['reportico_ajax_called'] = "";
                }
            }
            $sessionClass::setReporticoSessionParam('latestRequest', "");
        }

        // Derive URL call of the calling script so it can be recalled in form actions when not running in AJAX mode
        if (!$this->url_path_to_calling_script) {
            $this->url_path_to_calling_script = $_SERVER["SCRIPT_NAME"];
        }

        // Work out we are in AJAX mode
        $this->deriveAjaxOperation();

        switch ($mode) {
            case "CRITERIA":
                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();
                if (!isset($_REQUEST['reportico_criteria'])) {
                    echo "{ Success: false, Message: \"You must specify a criteria\" }";
                } else if (!$criteria = $this->getCriteriaByName($_REQUEST['reportico_criteria'])) {
                    echo "{ Success: false, Message: \"Criteria {$_REQUEST['reportico_criteria']} unknown in this report\" }";
                } else {
                    echo $criteria->executeCriteriaLookup();
                    echo $criteria->lookup_ajax(false);
                }
                die;

            case "MODIFY":
                require_once "DatabaseEngine.php";
                $this->initializePanels($mode);
                $engine = new DatabaseEngine($this->datasource->ado_connection->_connectionID);
                $status = $engine->performProjectModifications(ReporticoApp::getConfig("project"));
                if ($status["errstat"] != 0) {
                    header("HTTP/1.0 404 Not Found", true);
                }
                echo json_encode($status);
                die;

            case "ADMIN":
                $this->initializePanels($mode);
                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset);
                ReporticoLang::loadModeLanguagePack("admin", $this->output_charset);
                ReporticoLang::localiseTemplateStrings($this->template);
                $this->setRequestColumns();
                $txt = "";
                $this->handleXmlQueryInput($mode);
                $this->loadWidgets("core");

                restore_error_handler();

                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('admin.tpl');

                if ($this->return_output_to_caller) {
                    $txt = $this->template->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                    return $txt;
                } else {
                    $this->template->display($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                }
                break;

            case "MENU":

                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();
                //$this->buildMenu();
                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset);
                ReporticoLang::loadModeLanguagePack("menu", $this->output_charset);
                ReporticoLang::localiseTemplateStrings($this->template);

                $this->loadWidgets("core");

                restore_error_handler();
                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('menu.tpl');

                if ($this->return_output_to_caller) {
                    $txt = $this->template->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                    return $txt;
                } else {
                    $this->template->display($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                }
                break;

            case "PREPARE":

                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);
                $this->handleXmlQueryInput($mode);
                $this->setRequestColumns();

                if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml" || $this->xmlinput == "generate_tutorial.xml") {
                    // If configuring project then use project language strings from admin project
                    // found in projects/admin/lang.php
                    ReporticoLang::loadProjectLanguagePack("admin", $this->output_charset);
                    $this->template->assign('SHOW_MINIMAINTAIN', false);
                    $this->template->assign('IS_ADMIN_SCREEN', true);
                }
                ReporticoLang::loadModeLanguagePack("prepare", $this->output_charset);
                ReporticoLang::localiseTemplateStrings($this->template);

                $this->loadWidgets("core");

                if ($this->xmlinput == "deleteproject.xml" || $this->xmlinput == "configureproject.xml" || $this->xmlinput == "createtutorials.xml" || $this->xmlinput == "createproject.xml" || $this->xmlinput == "generate_tutorial.xml") {
                    $reportfile = "";
                } else {
                    $reportfile = preg_replace("/\.xml/", "", $this->xmloutfile);
                }

                $this->template->assign('XMLFILE', $reportfile);

                $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_prepare.tpl');
                restore_error_handler();

                // Some calling frameworks require output to be returned
                // for rendering inside web pages .. in this case
                // return_output_to_caller will be set to true
                $template = $this->getTemplatePath('prepare.tpl');

                $sessionClass::setReporticoSessionParam("firstTimeIn", false);
                //echo "first time endprep = ".$sessionClass::getReporticoSessionParam("firstTimeIn");

                if ($this->return_output_to_caller) {
                    $txt = $this->template->fetch($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                    return $txt;
                } else {
                    $this->template->display($template);
                    $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                }
                break;

            case "EXECUTE":

                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset);
                $this->initializePanels($mode);

                // Dont interpret XML input if report is not related to a project.
                // In this case the reprt has already been built
                if ( !Authenticator::allowed("non-project-operation"))
                    $this->handleXmlQueryInput($mode);

                ReporticoLang::loadModeLanguagePack("execute", $this->output_charset);
                ReporticoLang::localiseTemplateStrings($this->template);

                $this->loadWidgets("core");

                $this->checkCriteriaValidity();

                ReporticoApp::set("code_area", "Main Query");
                $this->buildQuery(false, "");
                ReporticoApp::set("code_area", false);


                if ( Authenticator::allowed("admin-report-selected")) {
                    // If configuring project then use project language strings from admin project
                    // found in projects/admin/lang.php
                    ReporticoLang::loadProjectLanguagePack("admin", $this->output_charset);
                }

                // For PDF output via phantom report will have already been executed so dont rerun it here
                if ($_REQUEST["target_format"] == "PDF" && ( $this->pdf_engine == "phantomjs" || $this->pdf_engine == "chromium")) {
                    $target = &$this->targets[0];
                    $target->start($this);
                } else {
                    if (!Authenticator::allowed("execute") && !$this->report_from_builder ) {
                        $text = "you are not logged in ";
                    } else
                        if (!$this->return_to_caller) {
                            $text = $this->executeQuery(false);
                        }
                }

                if ( count(ReporticoApp::getSystemErrors()) > 0 ||
                      ReporticoApp::get("debug_mode") ||
                      count(ReporticoApp::getSystemDebug()) > 0 ||
                      !Authenticator::allowed("execute") )
                {

                    // If errors and this is an ajax request return json ajax response for first message
                    $runfromcriteriascreen = ReporticoUtility::getRequestItem("user_criteria_entered", false);

                    header("HTTP/1.0 500 Not Found", true);
                    $this->initializePanels("PREPARE");
                    //$this->setRequestColumns();

                    $title = ReporticoLang::translate($this->deriveAttribute("ReportTitle", "Unknown"));
                    $this->template->assign('TITLE', $title);

                    $this->template->assign('CONTENT', $text);

                    if ( Authenticator::allowed("admin-report-selected")) {
                        // If configuring project then use project language strings from admin project
                        // found in projects/admin/lang.php
                        ReporticoLang::loadProjectLanguagePack("admin", $this->output_charset);
                        $this->template->assign('SHOW_MINIMAINTAIN', false);
                        $this->template->assign('IS_ADMIN_SCREEN', true);
                    }
                    ReporticoLang::loadModeLanguagePack("languages", $this->output_charset, true);
                    ReporticoLang::loadModeLanguagePack("prepare", $this->output_charset);
                    ReporticoLang::localiseTemplateStrings($this->template);
                    $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_execute.tpl');
                    restore_error_handler();


                    // Re-render Status/Error message in case any new errors appeared since last render
                    $this->widgets["status-message-block"] = new \Reportico\Widgets\StatusMessageBlock($this, true);
                    $this->widgetRenders["status-message-block"] = $this->widgets["status-message-block"]->render();
                    $this->template->assign('WIDGETS', $this->widgetRenders);

                    // Some calling frameworks require output to be returned
                    // for rendering inside web pages .. in this case
                    // return_output_to_caller will be set to true
                    $template = $this->getTemplatePath('error.tpl');

                    if (false && $this->return_output_to_caller) {
                        $txt = $this->template->fetch($template);
                        $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                        return $txt;
                    } else {
                        $this->template->display($template);
                        $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                    }
                } else {
                    if ($this->target_format != "HTML" && $this->target_format != "HTML2PDF") {
                        if ($draw) {
                            echo $text;
                        }

                    } else {
                        $title = ReporticoLang::translate($this->deriveAttribute("ReportTitle", "Unknown"));
                        $pagestyle = $this->targets[0]->getStyleTags($this->output_reportbody_styles);

                        $this->template->assign('PAGE_LAYOUT', strtoupper($this->deriveAttribute("PageLayout", "TABLE")));
                        $this->template->assign('REPORT_PAGE_STYLE', $pagestyle);
                        $this->template->assign('TITLE', $title);
                        $this->template->assign('CONTENT', $text);

                        // Pass Print formatting options to Template
                        if ($this->target_format == "HTML2PDF") {
                            $this->template->assign('PRINT_FORMAT', "reportico-print-pdf");

                            if (preg_match("/PDF/i", $this->getAttribute("AutoPaginate")))
                                $this->template->assign('AUTOPAGINATE', "autopaginate");
                            else
                                $this->template->assign('AUTOPAGINATE', "");
                            $this->template->assign('ZOOM_FACTOR', strtolower($this->getAttribute("PdfZoomFactor"), "100%"));
                        } else {
                            $this->template->assign('PRINT_FORMAT', "reportico-print-html");

                            if (preg_match("/HTML/i", $this->getAttribute("AutoPaginate"))) {
                                $this->template->assign('AUTOPAGINATE', "autopaginate");
                            } else
                                $this->template->assign('AUTOPAGINATE', "");
                            $this->template->assign('ZOOM_FACTOR', strtolower($this->getAttribute("HtmlZoomFactor")));
                        }
                        $this->template->assign('PAGE_TITLE_DISPLAY', strtolower($this->getAttribute("PageTitleDisplay")));

                        $this->template->assign('EMBEDDED_REPORT', $this->embedded_report);

                        $this->template->assign('PAGE_SIZE', $this->getAttribute("PageSize"));
                        $this->template->assign('PAGE_ORIENTATION', strtolower($this->getAttribute("PageOrientation", "portrait")));
                        $this->template->assign('PAGE_TOP_MARGIN', strtolower($this->getAttribute("TopMargin", "1cm")));
                        $this->template->assign('PAGE_BOTTOM_MARGIN', strtolower($this->getAttribute("BottomMargin", "1cm")));
                        $this->template->assign('PAGE_LEFT_MARGIN', strtolower($this->getAttribute("LeftMargin", "1cm")));
                        $this->template->assign('PAGE_RIGHT_MARGIN', strtolower($this->getAttribute("RightMargin", "1cm")));
                        //$this->template->assign('PAGE_TOP_MARGIN', "100px");
                        //$this->template->assign('PAGE_BOTTOM_MARGIN', "50px");


                        // When printing in separate html window make sure we dont treat report as embedded
                        if (ReporticoUtility::getRequestItem("new_reportico_window", false)) {
                            $this->template->assign('EMBEDDED_REPORT', false);
                        }

                        if ($this->email_recipients) {

                            $recipients = explode(',', $this->email_recipients);
                            foreach ($recipients as $rec) {
                                ReporticoLang::loadModeLanguagePack("languages", $this->output_charset, true);
                                ReporticoLang::loadModeLanguagePack("execute", $this->output_charset);
                                ReporticoLang::localiseTemplateStrings($this->template);
                                $template = $this->getTemplatePath('execute.tpl');
                                $mailtext = $this->template->fetch($template, null, null, false);
                                //$boundary = '-----=' . md5( uniqid ( rand() ) );
                                //$message = "Content-Type: text/html; name=\"my attachment\"\n";
                                //$message .= "Content-Transfer-Encoding: base64\n";
                                //$message .= "Content-Transfer-Encoding: quoted-printable\n";
                                //$message .= "Content-Disposition: attachment; filename=\"report.html\"\n\n";
                                $content_encode = chunk_split(base64_encode($mailtext));
                                $message = $mailtext . "\n";
                                //$message .= $boundary . "\n";
                                $headers = "From: \"Report Admin\"<me@here.com>\n";
                                $headers .= "MIME-Version: 1.0\n";
                                $headers .= "Content-Transfer-Encoding: base64\n";
                                //$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"";
                                $headers = "Content-Type: text/html\n";
                                mail($rec, "$title", $message, $headers);
                            }
                        } else {
                            ReporticoLang::loadModeLanguagePack("languages", $this->output_charset, true);
                            ReporticoLang::loadModeLanguagePack("execute", $this->output_charset);
                            ReporticoLang::localiseTemplateStrings($this->template);
                            $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_execute.tpl');
                            restore_error_handler();

                            $template = $this->getTemplatePath('execute.tpl');
                            if ($this->return_output_to_caller) {
                                $txt = $this->template->fetch($template);
                                $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                                return $txt;
                            } else {
                                //$txt = $this->template->fetch($template);
                                //file_put_contents("/tmp/fred1", $txt);
                                $this->template->display($template);
                                $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                            }

                        }
                    }
                }
                break;

            case "MAINTAIN":

                if ( !$this->authenticator ) {
                    $this->authenticator = Authenticator::initialize($this->authenticator_class, $this);
                    //Authenticator::login();
                }

                // Avoid url manipulation by only allowing maintain mode in design or demo mode

                $this->handleXmlQueryInput($mode);
                $this->initializePanels($mode);

                ReporticoLang::loadModeLanguagePack("maintain", $this->output_charset);
                ReporticoLang::localiseTemplateStrings($this->template);
                $this->xmlin->handleUserEntry();
                $sessionClass::setReporticoSessionParam("xmlintext", $this->xmlintext);

                $this->loadWidgets("core");

                $text = $this->xmlin->xml2html($this->xmlin->data);
                $this->template->assign('CONTENT', $text);
                $template = $this->getTemplatePath('maintain.tpl');
                $this->template->display($template);
                break;

            case "XMLOUT":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareXmlData();

                if (array_key_exists("xmlout", $_REQUEST)) {
                    $this->xmlout->writeFile($_REQUEST["xmlout"]);
                } else {
                    $this->xmlout->write();
                }

                break;

            case "XMLSHOW":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareXmlData();
                $this->xmlout->write();
                break;

            case "WSDLSHOW":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                $this->xmlout->prepareWsdlData();
                break;

            case "SOAPSAVE":
                $this->handleXmlQueryInput($mode);
                $this->xmlout = new XmlWriter($this);
                //$this->xmlout->generateWebService($this->xmloutfile);
                break;
        }

        $sessionClass::setReporticoSessionParam("lastMode", $mode);

        $this->handledInitialSettings();

        if (!$this->keep_session_open)
            $sessionClass::closeReporticoSession();
        //Authenticator::show("FINAL");
        Authenticator::saveToSession();
    }

    // -----------------------------------------------------------------------------
    // Function : premaintain_query
    // -----------------------------------------------------------------------------
    public function premaintainQuery()
    {
        foreach ($this->pre_sql as $sql) {
            $nsql = Assignment::reporticoLookupStringToPhp($sql);
            $recordSet = false;
            $errorCode = false;
            $errorMessage = false;
            try {
                $recordSet = $conn->Execute($nsql);
            } catch (PDOException $ex) {
                $errorCode = $ex->getCode();
                $errorMessage = $ex->getMessage();
            }
            if (!$recordSet) {
                if ($errorMessage) {
                    ReporticoApp::handleError("Query Failed<BR><BR>" . $nsql . "<br><br>" .
                        $errorMessage);
                } else {
                    ReporticoApp::handleError("Query Failed<BR><BR>" . $nsql . "<br><br>" .
                        "Status " . $conn->ErrorNo() . " - " .
                        $conn->ErrorMsg());
                }

            }
        }

        $this->fetchColumnAttributes();

        // Run query for each target. Currently having more than
        // one target means first target is array which becomes source
        // for second target
        //for ($i = 0; $i < count($this->targets); $i++ )
        for ($i = 0; $i < 1; $i++) {
            $target = &$this->targets[$i];

            $target->setQuery($this);
            $target->setColumns($this->columns);
            $target->start();
        }
    }

    // -----------------------------------------------------------------------------
    // Function : executeQuery
    // -----------------------------------------------------------------------------
    public function executeQuery($in_criteria_name)
    {
        $sessionClass = ReporticoSession();

        $text = "";

        $this->fetchColumnAttributes();

        // Run query for each target. Currently having more than
        // one target means first target is array which becomes source
        // for second target
        //for ($i = 0; $i < count($this->targets); $i++ )
        $_counter = 0;
        //for ($_counter = 0; $_counter < 1; $_counter++) {
        {
            $target = &$this->targets[$_counter];
            $target->setQuery($this);
            $target->setColumns($this->columns);
            $target->start();
            //}

            // Reset all old column values to junk
            foreach ($this->columns as $k => $col) {
                $this->columns[$k]->old_column_value = "";
            }

            /*
            if ($_counter > 0) {
                // Execute query 2
                $this->assignment = array();
                $ds = new ReporticoDataSource();
                $this->setDatasource($ds);

                $ds->setDatabase($this->targets[0]->results);
                $ds->connect();

                foreach ($this->columns as $k => $col) {
                    $this->columns[$k]->in_select = true;
                }
            }
            */

            /* Performing SQL query */
            $ds = &$this->datasource;
            $conn = &$this->datasource->ado_connection;

            $this->debug($this->query_statement);
            //$conn->debug = true;

            foreach ($this->pre_sql as $sql) {
                ReporticoApp::set("code_area", "Custom User SQLs");
                $nsql = Assignment::reporticoMetaSqlCriteria($this, $sql, true);
                ReporticoApp::handleDebug("Pre-SQL" . $nsql, ReporticoApp::DEBUG_LOW);
                $recordSet = false;
                $errorCode = false;
                $errorMessage = false;
                try {
                    $recordSet = $conn->Execute($nsql);
                } catch (PDOException $ex) {
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                }
                if (!$recordSet) {
                    if ($errorMessage) {
                        ReporticoApp::handleError("Pre-Query Failed<BR><BR>" . $nsql . "<br><br>" .
                            $errorMessage);
                    } else {
                        ReporticoApp::handleError("Pre-Query Failed<BR><BR>" . $nsql . "<br><br>" .
                            "Status " . $conn->ErrorNo() . " - " .
                            $conn->ErrorMsg());
                    }

                }
                ReporticoApp::set("code_area", "");
            }
            if (!$in_criteria_name) {
                // Execute Any Pre Execute Code, if not specified then
                // attempt to pick up code automatically from a file "projects/project/report.xml.php"
                $code = $this->getAttribute("PreExecuteCode");
                $source_path = false;
                if (!$code || $code == "NONE" || $code == "XX") {
                    if (preg_match("/.xml$/", $sessionClass::getReporticoSessionParam("xmlin"))) {
                        $source_path = ReporticoUtility::findBestLocationInIncludePath($this->projects_folder . "/" . ReporticoApp::getConfig("project") . "/" . $sessionClass::getReporticoSessionParam("xmlin") . ".php");
                    } else {
                        $source_path = ReporticoUtility::findBestLocationInIncludePath($this->projects_folder . "/" . ReporticoApp::getConfig("project") . "/" . $sessionClass::getReporticoSessionParam("xmlin") . ".xml.php");
                    }
                    if (is_file($source_path)) {
                        $code = file_get_contents($source_path);
                    } else {
                        $code = false;
                    }
                }

                if ($code) {
                    set_include_path(get_include_path().
                        PATH_SEPARATOR.__DIR__."/..".
                        PATH_SEPARATOR.$this->admin_projects_folder."/admin/".
                        PATH_SEPARATOR.$this->projects_folder."/../".
                        PATH_SEPARATOR.$this->projects_folder."/".ReporticoApp::getConfig("project")
                    );
                    //$source_path = ig("project") . "/" . $sessionClass::getReporticoSessionParam("xmlin") . ".xml.php";
                    ReporticoApp::set("code_area", "");
                    //$code = "";
                    $code = "\$lk =& \$this->lookup_queries;" . $code;
                    $code = "\$ds =& \$this->datasource->ado_connection;" . $code;
                    $code = "\$_criteria =& \$this->lookup_queries;" . $code;
                    $code = "\$_pdo =& \$_connection->_connectionID;" . $code;
                    $code = "if ( \$_connection )" . $code;
                    $code = "\$_pdo = false;" . $code;
                    //$code = "set_include_path(get_include_path().'".PATH_SEPARATOR.__DIR__."/..".PATH_SEPARATOR.$this->admin_projects_folder."/admin/'.);" . $code;
                    $code = "\$_connection =& \$this->datasource->ado_connection;" . $code;
                    $code = "namespace Reportico\Engine;" . $code;
                    //echo get_include_path()."<BR>";
                    //echo "<PRE>".htmlspecialchars($code); //die;
                    //$code .= "include '$source_path';";

                    // set to the user defined error handler
                    ReporticoApp::set("eval_code", $code);
                    // If parse error in eval code then use output buffering contents to show user the error
                    $ob_level = ob_get_level();
                    if ($ob_level > 0) {
                        ob_start();
                    }

                    eval($code);
                    $eval_output = ob_get_contents();
                    if ($ob_level > 0) {
                        ob_end_clean();
                    }

                    // Check for parse error
                    if (preg_match("/.*Parse error.*on line <b>(.*)<.b>/", $eval_output, $parseerrors)) {
                        // There is a parse error in the evaluated code .. find the relevant line
                        $errtext = "Parse Error in custom report code: <br><hr>$eval_output<PRE>";
                        foreach (preg_split("/(\r?\n)/", $code) as $lno => $line) {
                            // do stuff with $line
                            if ($lno > $parseerrors[1] - 3 && $lno < $parseerrors[1] + 3) {
                                if ($lno == $parseerrors[1]) {
                                    $errtext .= ">>>  ";
                                } else {
                                    $errtext .= "     ";
                                }

                                $errtext .= $line;
                                $errtext .= "\n";
                            }
                        }
                        $errtext .= "</PRE>";
                        trigger_error($errtext, E_USER_ERROR);

                    } else {
                        echo $eval_output;
                    }
                    ReporticoApp::set("code_area", "");
                    ReporticoApp::set("code_source", "");
                }
            }
            $recordSet = false;

            if ($in_criteria_name) {
                ReporticoApp::set("code_area", "Criteria " . $in_criteria_name);
            } else {
                ReporticoApp::set("code_area", "Main Report Query");
            }

            // User may have flagged returning before SQL performed
            if (ReporticoApp::get("no_sql"))
                return;

            // Prepare for query execution
            $recordSet = false;
            $errorCode = false;
            $errorMessage = false;

            // If the source is an array then dont try to run SQL
            if (is_object($conn) && preg_match("/DataSourceArray/", get_class($conn)) ) {
                $recordSet = $conn;
            } else {
                try {
                    if (!ReporticoApp::get("error_status") && $conn != false) {
                        $recordSet = $conn->Execute($this->query_statement);
                    }
                } catch (PDOException $ex) {
                    $errorCode = $ex->getCode();
                    $errorMessage = $ex->getMessage();
                    ReporticoApp::set("error_status", 1);
                }
            }
            if ($conn && !$recordSet && !ReporticoApp::get("error_status") && $this->execute_mode != "MAINTAIN" ) {
                if ($errorMessage) {
                    ReporticoApp::handleError("Query Failed<BR><BR>" . $this->query_statement . "<br><br>" .
                        $errorMessage);
                } else {
                    ReporticoApp::handleError("Query Failed<BR><BR>" . $this->query_statement . "<br><br>" .
                        "Status " . $conn->ErrorNo() . " - " .
                        $conn->ErrorMsg());
                }

            }

            if ($conn != false) {
                ReporticoApp::handleDebug($this->query_statement, ReporticoApp::DEBUG_LOW);
            }

            // Begin Target Output
            //ReporticoApp::handleError("set");
            if (!$recordSet || ReporticoApp::get("error_status")) {
                //ReporticoApp::handleError("stop");
                return;
            }

            // Main Query Result Fetching
            $this->query_count = 0;
            while (!$recordSet->EOF) {

                $line = $recordSet->FetchRow();
                if ($line == null) {
                    $recordSet->EOF = true;
                    continue;
                }
                $this->query_count++;

                ReporticoApp::set("code_area", "Build Column");
                $this->buildColumnResults($line);

                ReporticoApp::set("code_area", "Assignment");

                if ($_counter < 1) {
                    $target->setDefaultStyles();
                    $this->charsetEncodeDbToOutput();
                    $this->assign();
                }
                ReporticoApp::set("code_source", false);

                // Skip line if required
                if ($this->output_skipline) {
                    $this->query_count--;
                    $this->output_skipline = false;
                    continue;
                }

                ReporticoApp::set("code_area", "Line Output");
                $target->eachLine($line);

                ReporticoApp::set("code_area", "Store Output");
                $this->storeColumnResults();
                if ($recordSet->EOF) {
                    break;
                }

            }
            ReporticoApp::set("code_area", "");

            ReporticoApp::set("no_data", false);

            if ($this->query_count == 0 && !$in_criteria_name && (!$this->access_mode || $this->access_mode != "REPORTOUTPUT")) {
                ReporticoApp::set("no_data", true);
                ReporticoApp::handleError(ReporticoLang::templateXlate("NO_DATA_FOUND"), E_USER_WARNING);
                return;
            }

            // Complete Target Output
            //for ($_counter = 0; $_counter < count($this->targets); $_counter++ )
            //{
            //$target =& $this->targets[$_counter];
            $target->finish();
            $text = $target->getContent();
            /* Free resultset */
            $recordSet->Close();

        }
        return $text;

    }

    // -----------------------------------------------------------------------------
    // Function : get_column
    // -----------------------------------------------------------------------------
    public function &getColumn($query_name)
    {
        $retval = null;
        foreach ($this->columns as $col) {
            if ($col->query_name == $query_name) {
                $retval = &$col;
                break;
            }
        }
        return $retval;
    }

    // -----------------------------------------------------------------------------
    // Function : fetch_column_attributes
    // -----------------------------------------------------------------------------
    public function fetchColumnAttributes()
    {
        if (!is_object($this->datasource))
            return false;

        $conn = $this->datasource->ado_connection;
        //$a = new Reportico($this->datasource);
        //$old_database = $a->database;

        $datadict = false;
        reset($this->columns);
        $lasttab = "";
        while ($d = key($this->columns)) {
            $value = &$this->columns[$d];

            if (array_key_exists($value->query_name, $this->clone_columns)) {
                $value->column_type =
                    $this->clone_columns[$value->query_name][0];
                $value->column_length =
                    $this->clone_columns[$value->query_name][1];

            } else if ($value->table_name) {
                if ($lasttab != $value->table_name) {
                    $datadict = $this->datasource->ado_connection->MetaColumns($value->table_name);
                    if (!$datadict) {
                        // echo "Data Dictionary Attack Failed Table $value->table_name\n";
                        // PREPAREecho "Error ".$this->datasource->ado_connection->ErrorMsg()."<br>";
                        //die;
                    }
                }
                foreach ($datadict as $k => $v) {

                    if (strtoupper(trim($k)) == strtoupper($value->column_name)) {
                        //$coldets = $datadict[strtoupper($value->column_name)];
                        $coldets = $datadict[$k];
                        $value->column_type =
                            ReporticoDataSource::mapColumnType(
                                $this->datasource->driver,
                                $datadict[$k]->type);

                        if (strtoupper($value->column_type) == "INTEGER") {
                            $value->column_length = 0;
                        } else if (strtoupper($value->column_type) == "SMALLINT") {
                            $value->column_length = 0;
                        } else {
                            $value->column_length = (int)$datadict[$k]->max_length;
                        }

                        break;
                    }
                }
            }
            $lasttab = $value->table_name;
            next($this->columns);
        }
    }

    // -----------------------------------------------------------------------------
    // Function : Add_assignment
    // -----------------------------------------------------------------------------
    public function addAssignment
    (
        $query_name,
        $expression,
        $criteria,
        $atstart = false
    )
    {
        //print("Added assign $query_name, $expression, $criteria<BR>");
        if ($atstart) {
            array_unshift($this->assignment, new Assignment
                (
                    $query_name,
                    $expression,
                    $criteria
                )
            );
        } else {
            $this->assignment[] = new Assignment
            (
                $query_name,
                $expression,
                $criteria
            );
        }

        //$x = end($this->assignment);
        //$x->expression = "'gg'";
        return end($this->assignment);

    }

    // -----------------------------------------------------------------------------
    // Function : charset_encode_db_to_output
    // -----------------------------------------------------------------------------
    public function charsetEncodeDbToOutput()
    {
        if ($this->db_charset && $this->output_charset) {
            if ($this->db_charset != $this->output_charset) {
                foreach ($this->columns as $col) {
                    $col->column_value = iconv($this->db_charset, $this->output_charset, $col->column_value);
                }
            }
        }

    }

    // -----------------------------------------------------------------------------
    // Function : assign
    // -----------------------------------------------------------------------------
    public function assign()
    {
        // Clear any styles or instructions left over from previous rows
        foreach ($this->columns as $col) {

            $col->output_cell_styles = false;
            $col->output_images = false;
            $col->output_hyperlinks = false;
        }

        // Perform assignments
        foreach ($this->assignment as $assign) {

            $col = ReporticoUtility::getQueryColumn($assign->query_name, $this->columns);
            if (!$col) {
                continue;
            }
            ReporticoApp::set("code_area", "Assignment");
            ReporticoApp::set("code_source", "<BR>In Assignment if " . $assign->criteria . "<BR>");
            ReporticoApp::set("code_source", "<BR>In Assignment " . $assign->query_name . "=" . $assign->expression);

            if ($this->test($assign->criteria)) {
                if ($assign->non_assignment_operation) {
                    $a = $assign->expression . ';';
                } else {
                    $a = '$col->column_value = ' . $assign->expression . ';';
                }

                if ( $assign->expression ) {
                    try{
                        $r = eval($a);
                    } catch ( \ParseError $ex ) {
                        $error = "Syntax error in expression. Expression must be valid PHP syntax: <PRE>".$a."</PRE> - {$ex->getMessage()}";
                        ReporticoApp::ErrorLogger(E_USER_ERROR, $error);
                    }

                    if (ReporticoApp::get("debug_mode")) {
                        ReporticoApp::handleDebug("Assignment " . $assign->query_name . " = " . $assign->expression .
                            " => " . $col->column_value, ReporticoApp::DEBUG_HIGH);
                    }
                }

            } else {
                if ($assign->else) {

                    if ($assign->non_assignment_operation) {
                        $a = $assign->else . ';';
                    } else {
                        $a = '$col->column_value = ' . $assign->else . ';';
                    }

                    try{
                        $r = eval($a);
                    } catch ( \ParseError $ex ) {
                        $error = "Syntax error in expression else clause. Expression must be valid PHP syntax: <PRE>".$a."</PRE> - {$ex->getMessage()}";
                        ReporticoApp::ErrorLogger(E_USER_ERROR, $error);
                    }

                    if (ReporticoApp::get("debug_mode")) {
                        ReporticoApp::handleDebug("Assignment " . $assign->query_name . " = " . $assign->else .
                            " => " . $col->column_value, ReporticoApp::DEBUG_HIGH);
                    }
                }
            }

        }
    }

    public function getQueryColumnValue($name, &$arr)
    {
        $ret = "NONE";
        foreach ($arr as $val) {
            if ($val->query_name == $name) {
                return $val->column_value;
            }
        }

        // Extract criteria item
        if (substr($name, 0, 1) == "?" || substr($name, 0, 1) == "=") {
            $field = substr($name, 1);
            $bits = explode(",", $field);
            if (isset($this->lookup_queries[$bits[0]])) {
                if (!isset($bits[1])) {
                    $bits[1] = "VALUE";
                }

                $bits[1] = strtoupper($bits[1]);

                if (!isset($bits[2])) {
                    $bits[2] = false;
                }

                if ($bits[1] != "RANGE1" 
                    && $bits[1] != "RANGE2" 
                    && $bits[1] != "FULL" 
                    && $bits[1] != "VALUE"
                    && $bits[1] != "LOWER" 
                    && $bits[1] != "UPPER" 
                    && $bits[1] != "FROM" 
                    && $bits[1] != "TO"
                    ) {
                    $bits[1] = "VALUE";
                }

                $x = $this->lookup_queries[$bits[0]]->getCriteriaValue($bits[1], $bits[2]);
                return $x;
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : test
    // -----------------------------------------------------------------------------
    public function test($criteria)
    {

        $test_result = false;

        if (!$criteria) {
            return (true);
        }

        $test_string = 'if ( ' . $criteria . ' ) $test_result = true;';
        try{
            eval($test_string);
            $r = eval($test_string);
        } catch ( \ParseError $ex ) {
            $error = "Syntax error in expression else clause. Expression must be valid PHP syntax: <PRE>".$test_string."</PRE> - {$ex->getMessage()}";
            ReporticoApp::ErrorLogger(E_USER_ERROR, $error);
        }
        return $test_result;
    }

    // -----------------------------------------------------------------------------
    // Function : changed
    // -----------------------------------------------------------------------------
    public function changed($query_name)
    {

        $result = false;

        if ($query_name == "REPORT_BODY") {
            return false;
        }

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes a changed assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return $result;
        }

        if ($col->column_value
            != $col->old_column_value) {
            $result = true;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : reset
    // -----------------------------------------------------------------------------
    public function reset($query_name)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return 0;
        }
        $col->reset_flag = true;
        $col->column_value = 0;

        return 0;
    }

    // -----------------------------------------------------------------------------
    // Function : groupcount
    // -----------------------------------------------------------------------------
    public function groupcount($groupname, $result_name)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a column ($query_name) that does not exist within the report. Perhaps a group needs to be deleted");
            return 0;
        }
        $res = ReporticoUtility::getQueryColumn($result_name, $this->columns);

        if ($this->changed($groupname)) {
            $this->reset($result_name);
        }

        if ($res->old_column_value && !$res->reset_flag) {
            $result = $res->old_column_value + $col->column_value;
        } else {
            $result = $col->column_value;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : skipline
    // Causes current line output to be skipped/not outputted
    // -----------------------------------------------------------------------------
    public function skipline()
    {
        $this->output_skipline = true;
    }

    // -----------------------------------------------------------------------------
    // Function : embed_image
    // Generates a link object against a column
    // -----------------------------------------------------------------------------
    public function embedImage($column_assignee, $image, $width = false, $height = false)
    {
        ReporticoUtility::getQueryColumn($column_assignee, $this->columns)->output_images =
            array("image" => $image, "width" => $width, "height" => $height);
    }

    // -----------------------------------------------------------------------------
    // Function : create_hyperlink
    // Generates a link object against a column
    // -----------------------------------------------------------------------------
    public function embedHyperlink($column_assignee, $label, $url, $open_in_new = true, $is_drilldown = false)
    {
        ReporticoUtility::getQueryColumn($column_assignee, $this->columns)->output_hyperlinks =
            array("label" => $label, "url" => $url, "open_in_new" => $open_in_new, "is_drilldown" => $is_drilldown);
    }

    // -----------------------------------------------------------------------------
    // Function : applyStyle
    // Sets up style instructions against an output row, cell or page
    // For example allows a cell to appear in a particular color
    // or with specified margins, or allows a row to have a border above etc
    // Styles relate to CSS and are transferred where supported through to PDF
    // -----------------------------------------------------------------------------
    public function applyStyle($column_assignee, $item_type, $style_type, $style_value)
    {
        if ($item_type == "ALLCELLS") {
            $this->output_allcell_styles[$style_type] = $style_value;
        }

        if ($item_type == "CRITERIA") {
            $this->output_criteria_styles[$style_type] = $style_value;
        }

        if ($item_type == "ROW") {
            $this->output_row_styles[$style_type] = $style_value;
        }

        if ($item_type == "CELL") {
            ReporticoUtility::getQueryColumn($column_assignee, $this->columns)->output_cell_styles[$style_type] = $style_value;
        }

        if ($item_type == "PAGE") {
            $this->output_page_styles[$style_type] = $style_value;
        }

        if ($item_type == "BODY") {
            $this->output_reportbody_styles[$style_type] = $style_value;
        }

        if ($item_type == "COLUMNHEADERS") {
            $this->output_header_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADER") {
            $this->output_group_header_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADERLABEL") {
            $this->output_group_header_label_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPHEADERVALUE") {
            $this->output_group_header_value_styles[$style_type] = $style_value;
        }

        if ($item_type == "GROUPTRAILER") {
            $this->output_group_trailer_styles[$style_type] = $style_value;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : lineno
    // -----------------------------------------------------------------------------
    public function lineno($group_name = false)
    {
        $result = 0;
        if ($group_name) {
            if (!array_key_exists($group_name, $this->groupvals)) {
                $this->groupvals[$group_name] =
                    array("lineno" => 0);
            }

            if ($this->changed($group_name)) {
                $this->groupvals[$group_name]["lineno"] = 1;
            } else {
                $this->groupvals[$group_name]["lineno"]++;
            }

            $result = $this->groupvals[$group_name]["lineno"];
        } else {
            $result = $this->query_count;
        }

        return ($result);
    }

    // -----------------------------------------------------------------------------
    // Function : sum
    // -----------------------------------------------------------------------------
    public function sum($query_name, $group_name = false)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an sum assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }
        $result = str_replace(",", "", $col->column_value);

        if (!is_numeric($col->old_column_value))
            $col->old_column_value = 0;
        if ($col->old_column_value && !$col->reset_flag) {
            $result =
                $col->old_column_value +
                str_replace(",", "", $col->column_value);
        }
        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                    array("average" => 0,
                        "sum" => "0",
                        "avgct" => 0,
                        "avgsum" => 0,
                        "min" => 0,
                        "max" => 0);
            }

            if ($this->changed($group_name)) {
                $col->groupvals[$group_name]["sum"] = str_replace(",", "", $col->column_value);
            } else {
                if (is_numeric($col->column_value))
                    $col->groupvals[$group_name]["sum"] += str_replace(",", "", $col->column_value);
            }

            $result = $col->groupvals[$group_name]["sum"];
        } else {
            if ($col->reset_flag || !$col->sum) {
                $col->sum = str_replace(",", "", $col->column_value);
            } else {
                $col->sum += str_replace(",", "", $col->column_value);
            }

            $result = $col->sum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : sum
    // -----------------------------------------------------------------------------
    public function solosum($query_name, $group_name = false)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                    array("average" => 0,
                        "sum" => "0",
                        "avgct" => 0,
                        "avgsum" => 0,
                        "min" => 0,
                        "max" => 0);
            }

            if ($this->changed($group_name)) {
                $col->groupvals[$group_name]["sum"] = $col->column_value;
            } else {
                $col->groupvals[$group_name]["sum"] += $col->column_value;
            }

            $result = $col->groupvals[$group_name]["sum"];
        } else {
            if ($col->reset_flag || !$col->sum) {
                $col->sum = $col->column_value;
            } else {
                $col->sum += $col->column_value;
            }

            $result = $col->sum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : avg
    // -----------------------------------------------------------------------------
    public function avg($query_name, $group_name = false)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                    array("average" => 0,
                        "sum" => "0",
                        "avgct" => 0,
                        "avgsum" => 0,
                        "min" => 0,
                        "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["avgct"] = 1;
                $grpval["average"] = $col->column_value;
                $grpval["avgsum"] = $col->column_value;
            } else {
                $grpval["avgct"]++;
                $grpval["avgsum"] += $col->column_value;
                $grpval["average"] =
                    $grpval["avgsum"] /
                    $grpval["avgct"];
            }
            $result = $grpval["average"];
        } else {
            if ($col->reset_flag || !$col->average) {
                $col->avgct = 1;
                $col->average = $col->column_value;
                $col->avgsum = $col->column_value;
            } else {
                $col->avgct++;
                $col->avgsum += $col->column_value;
                $col->average = $col->avgsum / $col->avgct;
            }
            $result = $col->average;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : max
    // -----------------------------------------------------------------------------
    public function max($query_name, $group_name = false)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                    array("average" => 0,
                        "sum" => "0",
                        "avgct" => 0,
                        "avgsum" => 0,
                        "min" => 0,
                        "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["max"] = $col->column_value;
            } else {
                if ($grpval["max"] < $col->column_value) {
                    $grpval["max"] = $col->column_value;
                }

            }
            $result = $grpval["max"];
        } else {
            if ($col->reset_flag || !$col->maximum) {
                $col->maximum = $col->column_value;
            } else
                if ($col->maximum < $col->column_value) {
                    $col->maximum = $col->column_value;
                }

            $result = $col->maximum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : min
    // -----------------------------------------------------------------------------
    public function min($query_name, $group_name = false)
    {

        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        $result = $col->column_value;

        if ($group_name) {
            if (!array_key_exists($group_name, $col->groupvals)) {
                $col->groupvals[$group_name] =
                    array("average" => 0,
                        "sum" => "0",
                        "avgct" => 0,
                        "avgsum" => 0,
                        "min" => 0,
                        "max" => 0);
            }

            $grpval = &$col->groupvals[$group_name];
            if ($this->changed($group_name)) {
                $grpval["min"] = $col->column_value;
            } else {
                if ($grpval["min"] > $col->column_value) {
                    $grpval["min"] = $col->column_value;
                }

            }
            $result = $grpval["min"];
        } else {
            if ($col->reset_flag || !$col->minimum) {
                $col->minimum = $col->column_value;
            } else
                if ($col->minimum > $col->column_value) {
                    $col->minimum = $col->column_value;
                }

            $result = $col->minimum;
        }

        return $result;
    }

    // -----------------------------------------------------------------------------
    // Function : old
    // -----------------------------------------------------------------------------
    public function old($query_name)
    {
        $col = ReporticoUtility::getQueryColumn($query_name, $this->columns);
        if (!$col) {
            ReporticoApp::handleError("The report includes an assignment involving a group or column ($query_name) that does not exist within the report");
            return 0;
        }

        if (!$col->reset_flag) {
            return $col->old_column_value;
        } else {
            return false;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : imagequery
    // -----------------------------------------------------------------------------
    public function imagequery($imagesql, $width = 200)
    {
        $sessionClass = ReporticoSession();

        $conn = &$this->datasource;

        //$imagesql = str_replace($imagesql, '"', "'");
        $imagesql = preg_replace("/'/", "\"", $imagesql);
        //$params="driver=".$conn->driver."&dbname=".$conn->database."&hostname=".$conn->host_name;
        $params = "dummy=xxx";

        // Link to db image depaends on the framework used. For straight reportico, its a call to the imageget.php
        // file, for Joomla it must go through the Joomla index file
        $imagegetpath = dirname($this->url_path_to_reportico_runner) . "/" . ReporticoUtility::findBestUrlInIncludePath("imageget.php");
        if ($this->framework_parent) {
            $imagegetpath = "";
            if ($this->reportico_ajax_mode != "standalone") {
                $imagegetpath = preg_replace("/ajax/", "dbimage", $this->reportico_ajax_script_url);
            }

        }

        $forward_url_params = $sessionClass::sessionRequestItem('forward_url_get_parameters_dbimage');
        if (!$forward_url_params) {
            $forward_url_params = $sessionClass::sessionRequestItem('forward_url_get_parameters', $this->forward_url_get_parameters);
        }

        if ($forward_url_params) {
            $params .= "&" . $forward_url_params;
        }

        $params .= "&reportico_session_name=" . $sessionClass::reporticoSessionName();

        $result = '<img width="' . $width . '" src=\'' . $imagegetpath . '?' . $params . '&reportico_call_mode=dbimage&imagesql=' . $imagesql . '\'>';

        return $result;
    }

    /**
     * Function generate_dropdown_menu
     *
     * Writes new admin password to the admin config.php
     */
    public function generateDropdownMenu(&$menu)
    {
        foreach ($menu as $k => $v) {
            $project = $v["project"];
            $initproject = $v["project"];
            $projtitle = "<AUTO>";
            if (isset($v["title"])) {
                $projtitle = $v["title"];
            }

            $menu[$k]["title"] = ReporticoLang::translate($projtitle);
            foreach ($v["items"] as $k1 => $menuitem) {
                if (!isset($menuitem["reportname"]) || $menuitem["reportname"] == "<AUTO>") {
                    // Generate Menu from XML files
                    if (is_dir($this->projects_folder)) {
                        $proj_parent = $this->projects_folder;
                    } else {
                        $proj_parent = ReporticoUtility::findBestLocationInIncludePath($this->projects_folder);
                    }

                    $project = $initproject;
                    if (isset($menuitem["project"])) {
                        $project = $menuitem["project"];
                    }

                    $filename = $proj_parent . "/" . $project . "/" . $menuitem["reportfile"];
                    if (!preg_match("/\.xml/", $filename)) {
                        $filename .= ".xml";
                    }

                    if (is_file($filename)) {
                        $query = false;
                        $repxml = new XmlReader($query, $filename, false, "ReportTitle");
                        $menu[$k]["items"][$k1]["reportname"] = ReporticoLang::translate($repxml->search_response);
                        $menu[$k]["items"][$k1]["project"] = $project;
                    }
                }
            }
        }
    }

    public function getBootstrapStyle($type)
    {
        if (!$this->bootstrap_styles) {
            return "";
        }

        $x = $this->{"bootstrap_styling_" . $type};
        if ($x) {
            return $x . " ";
        }
    }

    /**
     * Function setProjectEnvironment
     *
     * Analyses configuration and current session to identify which project area
     * is to be used.
     * If a project is specified in the HTTP parameters then that is used, otherwise
     * the current SESSION
     * "reports" project is used
     */
    public function setProjectEnvironment($initial_project = false, $project_folder = "projects", $admin_project_folder = "projects")
    {
        $sessionClass = ReporticoSession();

        $target_menu = "";
        $project = "";

        $last_project = "";

        if ($sessionClass::issetReporticoSessionParam("project")) {
            if ($sessionClass::getReporticoSessionParam("project")) {
                $last_project = $sessionClass::getReporticoSessionParam("project");
            }
        }

        if (!$project && array_key_exists("submit_delete_project", $_REQUEST)) {
            $project = ReporticoUtility::getRequestItem("jump_to_delete_project", "");
            $_REQUEST["xmlin"] = "deleteproject.xml";
            $sessionClass::setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_configure_project", $_REQUEST)) {
            $project = ReporticoUtility::getRequestItem("jump_to_configure_project", "");
            $_REQUEST["xmlin"] = "configureproject.xml";
            $sessionClass::setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_menu_project", $_REQUEST)) {
            $project = ReporticoUtility::getRequestItem("jump_to_menu_project", "");
            $sessionClass::setReporticoSessionParam("project", $project);
        }

        if (!$project && array_key_exists("submit_design_project", $_REQUEST)) {
            $project = ReporticoUtility::getRequestItem("jump_to_create_report", "");
            $sessionClass::setReporticoSessionParam("project", $project);
        }

        if ($initial_project) {
            $project = $initial_project;
            $sessionClass::setReporticoSessionParam("project", $project);
        }

        if (!$project) {
            $project = $sessionClass::sessionRequestItem("project", "admin");
        }

        if (!$target_menu) {
            $target_menu = $sessionClass::sessionRequestItem("target_menu", "");
        }

        $menu = false;
        $menu_title = "Set Menu Title";

        // Now we now the project include the relevant config.php
        $projpath = $project_folder . "/" . $project;
        $admin_projpath = $admin_project_folder . "/" . $project;

        $configfile = $projpath . "/config.php";
        $configtemplatefile = $admin_projpath . "/adminconfig.template";

        $menufile = $projpath . "/menu.php";
        if ($target_menu != "") {
            $menufile = $projpath . "/menu_" . $target_menu . ".php";
        }

        if (!is_file($projpath)) {
            ReporticoUtility::findFileToInclude($projpath, $projpath);
        }

        $sessionClass::setReporticoSessionParam("project_path", $projpath);
        $this->reports_path = $projpath;

        if (!$projpath) {
            ReporticoUtility::findFileToInclude("config.php", $configfile);
            if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                ReporticoApp::handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
            } else {
                ReporticoApp::set("included_config", $configfile);
                include_once $configfile;
            }
            ReporticoApp::set("projpath", false);
            ReporticoApp::set("project", false);
            ReporticoApp::set("static_menu", false);
            ReporticoApp::set("admin_menu", false);
            ReporticoApp::set('menu_title', '');
            ReporticoApp::set("dropdown_menu", false);
            $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
            ReporticoApp::handleError("Project Directory $project not found. Check INCLUDE_PATH or project name");
            return;
        }

        ReporticoApp::set("projpath", $projpath);
        if (!is_file($configfile)) {
            ReporticoUtility::findFileToInclude($configfile, $configfile);
        }

        if (!is_file($menufile)) {
            ReporticoUtility::findFileToInclude($menufile, $menufile);
        }

        if ($project == "admin" && !is_file($configfile)) {
            ReporticoUtility::findFileToInclude($configtemplatefile, $configfile);
        }

        if ($configfile) {
            if (!is_file($configfile)) {
                ReporticoApp::handleError("Config file $configfile not found in project $project", E_USER_WARNING);
            }

            if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                ReporticoApp::handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
            } else {
                include_once $configfile;
                ReporticoApp::set("included_config", $configfile);
            }

            if (is_file($menufile)) {
                include $menufile;
            }

            //else
            //ReporticoApp::handleError("Menu Definition file $menufile not found in project $project", E_USER_WARNING);

        } else {
            ReporticoUtility::findFileToInclude("config.php", $configfile);
            if ($configfile) {
                if (ReporticoApp::get("included_config") && ReporticoApp::get("included_config") != $configfile) {
                    ReporticoApp::handleError("Cannot load two different instances on a single page from different projects.", E_USER_ERROR);
                } else {
                    include_once $configfile;
                    ReporticoApp::set("included_config", $configfile);
                }
            }

            ReporticoApp::set('project', false);
            ReporticoApp::set("static_menu", false);
            ReporticoApp::set("admin_menu", false);
            ReporticoApp::set("projpath", false);
            ReporticoApp::set('menu_title', '');
            ReporticoApp::set("dropdown_menu", false);
            $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
            ReporticoApp::handleError("Configuration Definition file config.php not found in project $project", E_USER_ERROR);
        }

        // Ensure a Database and Output Character Set Encoding is set
        if (!ReporticoApp::isSetConfig("db_encoding")) {
            ReporticoApp::setConfig("db_encoding", "UTF8");
        }

        if (!ReporticoApp::isSetConfig("output_encoding")) {
            ReporticoApp::setConfig("output_encoding", "UTF8");
        }

        // Ensure a language is set
        if (!ReporticoApp::isSetConfig("language")) {
            ReporticoApp::setConfig("language", "en_gb");
        }

        if (!ReporticoApp::isSetConfig('project')) {
            ReporticoApp::setConfig('project', $project);
        }

        $sessionClass::setReporticoSessionParam("project", $project);

        $language = "en_gb";
        // Default language to first language in avaible_languages
        $langs = ReporticoLang::availableLanguages();
        if (count($langs) > 0) {
            $language = $langs[0]["value"];
        }

        $config_language = ReporticoApp::getConfig("language", false);
        if ($config_language && $config_language != "PROMPT") {
            $language = $sessionClass::sessionRequestItem("reportico_language", $config_language);
        } else {
            $language = $sessionClass::sessionRequestItem("reportico_language", "en_gb");
        }

        // language not found the default to first
        $found = false;
        foreach ($langs as $k => $v) {
            if ($v["value"] == $language) {
                $found = true;
                break;
            }
        }
        if (!$found && count($langs) > 0) {
            $language = $langs[0]["value"];
        }

        if (array_key_exists("submit_language", $_REQUEST)) {
            $language = $_REQUEST["jump_to_language"];
            $sessionClass::setReporticoSessionParam("reportico_language", $language);
        }
        ReporticoApp::setConfig("language", $language);

        if (isset($menu) && !ReporticoApp::get("static_menu")) {
            ReporticoApp::set("static_menu", $menu);
        }

        if (isset($menu) && !ReporticoApp::get("admin_menu")) {
            ReporticoApp::set("admin_menu", $menu);

        }

        if (isset($menu_title) && !ReporticoApp::get("menu_title"))
            ReporticoApp::set('menu_title', $menu_title);

        if (isset($dropdown_menu) && !ReporticoApp::get("dropdown_menu")) {
            ReporticoApp::set("dropdown_menu", $dropdown_menu);
        }

        // Include project specific language translations
        ReporticoLang::loadProjectLanguagePack($project, ReporticoLocale::outputCharsetToPhpCharset(ReporticoApp::getConfig("output_encoding", "UTF8")));

        return $project;
    }
    // -----------------------------------------------------------------------------
    // Function : load_plugins
    //
    // Scan plugins folder for custom plugin functions and load them into plugins array
    // -----------------------------------------------------------------------------
    public function loadPlugins()
    {
        $plugin_dir = ReporticoUtility::findBestLocationInIncludePath("plugins");

        if (is_dir($plugin_dir)) {
            if ($dh = opendir($plugin_dir)) {
                while (($file = readdir($dh)) !== false) {
                    $plugin = $plugin_dir . "/" . $file;
                    if (is_dir($plugin)) {
                        $plugin_file = $plugin . "/global.php";
                        if (is_file($plugin_file)) {
                            require_once $plugin_file;
                        }
                        $plugin_file = $plugin . "/" . strtolower($this->execute_mode) . ".php";
                        if (is_file($plugin_file)) {
                            require_once $plugin_file;
                        }
                    }
                }
            }
        }

        // Call any plugin initialisation
        $this->applyPlugins("initialize", $this);
    }

    // -----------------------------------------------------------------------------
    // Function : load_plugins
    //
    // Scan plugins folder for custom plugin functions and load them into plugins array
    // -----------------------------------------------------------------------------
    public function applyPlugins($section, $params)
    {
        foreach ($this->plugins as $k => $plugin) {
            if (isset($plugin[$section])) {
                if (isset($plugin[$section]["function"])) {
                    return $plugin[$section]["function"]($params);
                } else if (isset($plugin[$section]["file"])) {
                    require_once $plugin["file"];
                }
            }
        }
    }

    /**
     * Set theme to use
     *
     * @param string $value Theme to use
     * */
    public function setTheme($value)
    {
        if (trim($value) != '') {
            $this->theme = $value;
        }
    }

    public function getTheme()
    {
        $sessionClass = ReporticoSession();
        $theme = $sessionClass::sessionRequestItem("theme", $this->theme);

        if ($theme == '') {
            $theme = 'default';
        }

        return $theme;
    }

    /**
     * Get the path to the template
     *
     * @param $template stricng name of template foile (eg. prepare.tpl)
     * @return string Path to the template including template / custom file.
     */
    public function getTemplatePath($template)
    {
        $reportname = preg_replace("/.xml/", "", $this->xmloutfile . '_' . $template);

        // Some calling frameworks require output to be returned
        // for rendering inside web pages .. in this case
        // return_output_to_caller will be set to true

        if (preg_match("/$reportname/", ReporticoUtility::findBestLocationInIncludePath("templates/" . $this->getTheme() . $reportname))) {
            $template = $reportname;
        }

        if (preg_match("/$reportname/", ReporticoUtility::findBestLocationInIncludePath("templates/" . $reportname))) {
            $template = $reportname;
        } else if ($this->user_template) {
            $template = $this->user_template . "_$template";
        }

        return $template;
        //return $this->getTheme() . '/' . $template;
    }

    public function applyStyleset($type, $styles, $column = false, $mode = false, $condition = false, $assignment = false)
    {
        $txt = "";
        $usecolumn = false;
        foreach ($this->columns as $k => $col) {
            if (!$column || $column == $col->query_name) {
                $usecolumn = $col->query_name;
                break;
            }
        }
        if (!$usecolumn) {
            //echo "Apply Styleset Column $column not found<BR>";
            return;
        }

        if ( is_string($styles)) {
            $stylearr = explode(";", $styles);
            $styles = [];
            foreach ($stylearr as $v) {
                $element = explode(":", $v);
                if ($element && isset($element[1])) {
                    $styles[$element[0]] = trim($element[1]);
                }
            }
        }

        foreach ($styles as $element => $style) {
            $txt .= "applyStyle('$type', '$element', '$style');";
        }
        if ($condition && $mode) {
            $condition = "$condition && {TARGET_FORMAT} == '$mode'";
        } else if ($mode) {
            $condition = "{TARGET_FORMAT} == '$mode'";
        }

        if ( !$assignment )
            $this->addAssignment($usecolumn, $txt, $condition, true);
        else
            $assignment->setExpression($txt);

    }

    function session($namespace = "")
    {

        $sessionClass = ReporticoSession();
        if (method_exists($sessionClass, "switchToRequestedNamespace"))
            $this->session_namespace = $sessionClass::switchToRequestedNamespace($this->session_namespace);

        if ($this->session_namespace) {
            ReporticoApp::set("session_namespace", $this->session_namespace);
        }

        if (ReporticoApp::get("session_namespace")) {
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
        }

        ReporticoSession::setUpReporticoSession($this->session_namespace);

        if ( $this->clear_reportico_session) {
            $namespace = ReporticoApp::get("session_namespace");
            ReporticoApp::set("session_namespace", $namespace);
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
            $sessionClass::initializeReporticoNamespace(ReporticoApp::get("session_namespace_key"));
        }

        if ($namespace)
            $this->session_namespace = $namespace;
    }

    /*
     * Prepare asset manager
     */
    function initialiseAssetManager(){

        $this->assetManager = new \Reportico\Engine\AssetManager($this);
        $this->assetManager->initialise();
        $this->assetManager->initialiseTheme();

    }

    /*
     * Initialise controls and browser objects which are relevant to the maind
     */
    function renderWidget($name, $class){

        $themeclass = "\\Reportico\\Themes\\Widgets\\$class";
        //echo " ".$themeclass;
        if ( class_exists($themeclass)) {
            //echo "exists<BR>";
            $class = $themeclass;
        }
        else
            $class = "\Reportico\Widgets\\$class";
        if (isset($this->widgets[$name]))
            $widget = $this->widgets["$name"];
        else {
            $widget = $this->widgets[$name] = new $class($this, true, "$name");
        }

        return $widget->render();
    }

    /*
     * Initialise controls and browser objects which are relevant to the maind
     */
    function loadWidgets($group){

        $this->initialiseAssetManager();

        // Set Grid display options based on report and session defaults
        if ($this->attributes["gridDisplay"] != ".DEFAULT") {
            $this->dynamic_grids = ($this->attributes["gridDisplay"] == "show");
        }

        if ($this->attributes["gridSortable"] != ".DEFAULT") {
            $this->dynamic_grids_sortable = ($this->attributes["gridSortable"] == "yes");
        }

        if ($this->attributes["gridSearchable"] != ".DEFAULT") {
            $this->dynamic_grids_searchable = ($this->attributes["gridSearchable"] == "yes");
        }

        if ($this->attributes["gridPageable"] != ".DEFAULT") {
            $this->dynamic_grids_paging = ($this->attributes["gridPageable"] == "yes");
        }

        if ($this->attributes["gridPageSize"] != ".DEFAULT" && $this->attributes["gridPageSize"]) {
            $this->dynamic_grids_page_size = $this->attributes["gridPageSize"];
        }

        $this->assetManager->setOptions("dynamicgrid",
                [
                "enabled" => $this->dynamic_grids,
                "sortable" => $this->dynamic_grids_sortable,
                "searchable" => $this->dynamic_grids_searchable,
                "paging" => $this->dynamic_grids_paging,
                "pageSize" => $this->dynamic_grids_page_size
                ]);

        $this->assetManager->setOptions("core",
            [
                "csrfToken" => $this->csrfToken,
                "ajaxHandler" => $this->ajaxHandler,
            ]);

        $this->widgets = [];

        // Action externally controlled show/hide of widget elements
        foreach ( $this->output_template_parameters as $k => $v ) {
            if ( $v == "show" ) {
                Authenticator::flag($k);
            }
        }

        //if ( !$this->reportico_ajax_called || ( $this->execute_mode == "PREPARE" ) ) {
            $this->widgets["popup-page-setup"] = new \Reportico\Widgets\PopupEditButton($this, true, "page-setup");
            $this->widgets["output-html-new-window"] = new \Reportico\Widgets\SubmitExecute($this, true, "output-html-new-window");
            $this->widgets["output-html-inline"] = new \Reportico\Widgets\SubmitExecute($this, true, "output-html-inline");
            $this->widgets["output-pdf"] = new \Reportico\Widgets\SubmitExecute($this, true, "output-pdf");
            $this->widgets["output-csv"] = new \Reportico\Widgets\SubmitExecute($this, true, "output-csv");
            $this->widgets["submit-go"] = new \Reportico\Widgets\SubmitExecute($this, true, "submit-go");
            $this->widgets["submit-reset"] = new \Reportico\Widgets\SubmitExecute($this, true, "submit-reset");
            $this->widgets["lookup-search"] = new \Reportico\Widgets\SubmitExecute($this, true, "lookup-search");
            $this->widgets["lookup-clear"] = new \Reportico\Widgets\SubmitExecute($this, true, "lookup-clear");
            $this->widgets["lookup-select-all"] = new \Reportico\Widgets\SubmitExecute($this, true, "lookup-select-all");
            $this->widgets["lookup-ok"] = new \Reportico\Widgets\SubmitExecute($this, true, "lookup-ok");
        //}

        if ( !$this->reportico_ajax_called || ( Authenticator::allowed("design") ) ) {

            if ( !Authenticator::allowed("admin-report-selected")) {
            $this->widgets["save-report"] = new \Reportico\Widgets\SaveReport($this, true);
            $this->widgets["run-report"] = new \Reportico\Widgets\SubmitExecute($this, true, "run-report");
            $this->widgets["delete-report"] = new \Reportico\Widgets\SubmitExecute($this, true, "delete-report");
            $this->widgets["new-report"] = new \Reportico\Widgets\SubmitExecute($this, true, "new-report");

            $this->widgets["popup-edit-sql"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-sql");
            $this->widgets["popup-edit-description"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-description");
            $this->widgets["popup-edit-columns"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-columns");
            $this->widgets["popup-edit-assignments"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-assignments");
            $this->widgets["popup-edit-groups"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-groups");
            $this->widgets["popup-edit-charts"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-charts");
            $this->widgets["popup-edit-criteria"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-criteria");
            $this->widgets["popup-edit-page-headers"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-page-headers");
            $this->widgets["popup-edit-page-footers"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-page-footers");
            $this->widgets["popup-edit-grid"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-grid");
            $this->widgets["popup-edit-title"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-title");
            $this->widgets["popup-edit-code"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-code");
            $this->widgets["popup-edit-pre-sqls"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-pre-sqls");
            $this->widgets["popup-edit-display-order"] = new \Reportico\Widgets\PopupEditButton($this, true, "edit-display-order");
            }
        }

        $this->widgets["navigation-menu"] = new \Reportico\Widgets\NavigationMenu($this, true, "navigation-menu");
        $this->widgets["criteria-form"] = new \Reportico\Widgets\CriteriaForm($this, true, "prepare");
        $this->widgets["form"] = new \Reportico\Widgets\Form($this, true, "basic");
        $this->widgets["design-form"] = new \Reportico\Widgets\Form($this, true, "design");
        //$this->widgets["criteria-lookup"] = new \Reportico\Widgets\CriteriaLookup($this, true);
        $this->widgets["criteria-toggle"] = new \Reportico\Widgets\CriteriaToggle($this, true);
        $this->widgets["powered-by-banner"] = new \Reportico\Widgets\PoweredByBanner($this, true);
        $this->widgets["template"] = new \Reportico\Widgets\Template($this, true);

        $this->widgets["title"] = new \Reportico\Widgets\Title($this, true);
        $this->widgets["description"] = new \Reportico\Widgets\Description($this, true);
        $this->widgets["user-access"] = new \Reportico\Widgets\UserAccess($this, true);
        $this->widgets["admin-page"] = new \Reportico\Widgets\AdminPage($this, true);
        $this->widgets["design-page"] = new \Reportico\Widgets\DesignPage($this, true);
        $this->widgets["menu-page"] = new \Reportico\Widgets\MenuPage($this, true);

        // Handle Widget Submit
        foreach ( $this->widgets as $k => $v ) {
            $this->widgetRenders[$k] = $v->onSubmit();
        }

        $this->widgetRenders = [];
        foreach ( $this->widgets as $k => $v ) {
            $this->widgetRenders[$k] = $v->render();
        }

        // Set up a widget for each criteria
        $criteriaRenders = [];
        foreach ( $this->lookup_queries as $v ) {
            $v->createWidget();
        }
        foreach ( $this->lookup_queries as $v ) {
            $this->widgets["criteria-{$v->query_name}"] = new \Reportico\Widgets\Criteria($this, true, $v);
        }
        foreach ( $this->lookup_queries as $v ) {
            //$v->widget = $this->widgets["criteria-{$v->query_name}"];
            $this->widgets["criteria-{$v->query_name}"]->prehandleUrlParameters();
            $this->widgets["criteria-{$v->query_name}"]->handleUrlParameters();
        }
        foreach ( $this->lookup_queries as $v ) {
            $criteriaRenders[] = $this->widgets["criteria-{$v->query_name}"]->render();
            if ( $this->widgets["criteria-{$v->query_name}"]->engineCriteria->widget == false ) {
                continue;
            }
            $config = $this->widgets["criteria-{$v->query_name}"]->engineCriteria->widget->getRenderConfig();
            if ( $config ) {
                $this->assetManager->manager->appendToCollection($config);
                $this->assetManager->manager->load($config["name"]);
            }
        }

        foreach ( $criteriaRenders as $key => $render ) {
            if ( isset($render["lookup-selection"])) {
                $this->widgetRenders["criteria-lookup"] = $render["lookup-selection"];
                $this->widgetRenders["lookup-search"] = preg_replace("/{LOOKUPITEM}/", $render["lookup-criteria-name"], $this->widgetRenders["lookup-search"]);
                $this->widgetRenders["lookup-select-all"] = preg_replace("/{LOOKUPITEM}/", $render["lookup-criteria-name"], $this->widgetRenders["lookup-select-all"]);
                $this->widgetRenders["lookup-clear"] = preg_replace("/{LOOKUPITEM}/", $render["lookup-criteria-name"], $this->widgetRenders["lookup-clear"]);
                $this->widgetRenders["lookup-ok"] = preg_replace("/{LOOKUPITEM}/", $render["lookup-criteria-name"], $this->widgetRenders["lookup-ok"]);
            }
        }

        $this->widgets["status-message-block"] = new \Reportico\Widgets\StatusMessageBlock($this, true);
        $this->widgetRenders["status-message-block"] = $this->widgets["status-message-block"]->render();
        

        $this->assetManager->reload($group);

        $this->template->assign('ASSETS_CSS', $this->assetManager->css());
        $this->template->assign('ASSETS_JS', $this->assetManager->js());

        $this->template->assign('ASSETS_INIT', $this->assetManager->event("init"));
        $this->template->assign('ASSETS_RUNTIME', $this->assetManager->event("runtime"));

        Authenticator::flag("run-mode-".strtolower($this->execute_mode));

        $this->template->assign('ASSETS_MODALS', $this->assetManager->render("modal"));
        $this->template->assign('CRITERIA_BLOCK', $criteriaRenders);
        $this->template->assign('WIDGETS', $this->widgetRenders);
        $this->template->assign('PERMISSIONS', Authenticator::widgetRenders("permissions"));
        $this->template->assign('FLAGS', Authenticator::widgetRenders("flags"));

        $this->template->assign('REPORTICO_VERSION', $this->version);
        $this->template->assign('REPORTICO_SITE', $this->url_site);
        $this->template->assign('REPORTICO_CSRF_TOKEN', $this->csrfToken);
        $this->template->assign('REPORTICO_AJAX_HANDLER', $this->ajaxHandler);

        $translations = ReporticoApp::get("translations");
        //Authenticator::show("end");

    }
}
// -----------------------------------------------------------------------------
