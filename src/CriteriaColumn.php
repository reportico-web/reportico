<?php

namespace Reportico\Engine;

/**
 * Class CriteriaColumn
 *
 * Identifies a criteria item. Holds all the necessary information
 * to allow users to input criteria values including criteria presentation
 * information. Holds database query parameters to criteria selection
 * lists can be generated from the database when the criteria type is LOOKUP
 */

class CriteriaColumn extends QueryColumn
{
    public $defaults = array();
    public $defaults_raw = "";
    public $value;
    public $range_start;
    public $range_end;
    public $criteria_type;
    public $_use;
    public $criteria_display;
    public $display_group;
    public $criteria_help;
    public $expand_display;
    public $required;
    public $hidden;
    public $order_type;
    public $list_values = array();
    //public $first_criteria_selection = true;
    public $parent_reportico = false;
    public $criteria_summary;
    public $widget;
    public $widgetExpand;
    public $column_value = false;
    public $column_value2 = false;
    public $column_value_derived = false;

    // For criteria that is linked to in another report
    // Specifies both the report to link to and the criteria item
    // a blank criteria item means all criterias are pulled in
    public $link_to_report = false;
    public $link_to_report_item = false;

    public $criteria_types = array(
        "FROMDATE",
        "TODATE",
        "FROMTIME",
        "TOTIME",
        "ANY",
        "NOINPUT",
        "ANYCHAR",
        "TEXTFIELD",
        "SQLCOMMAND",
        "ANYINT",
        "LOOKUP",
        "DATERANGE",
        "DATE",
        "SWITCH",
    );

    public function __construct
    (
        $parent_reportico,
        $query_name,
        $table_name,
        $column_name,
        $column_type,
        $column_length,
        $column_mask,
        $in_select
    ) {
        $this->parent_reportico = $parent_reportico;

        QueryColumn::__construct(
            $query_name,
            $table_name,
            $column_name,
            $column_type,
            $column_length,
            $column_mask,
            $in_select);
    }

    public function setLookup($table, $return_columns, $display_columns)
    {
    }

    // -----------------------------------------------------------------------------
    // Function : executeCriteriaLookup
    // -----------------------------------------------------------------------------
    public function executeCriteriaLookup($in_is_expanding = false, $no_warnings = false)
    {
        $rep = new ReportArray();

        $this->lookup_query->rowselection = true;
        $this->lookup_query->setDatasource($this->datasource);
        $this->lookup_query->targets = array();
        $this->lookup_query->addTarget($rep);
        $this->lookup_query->buildQuery($in_is_expanding, $this->query_name, false, $no_warnings);
        $this->lookup_query->executeQuery($this->query_name);
    }

    // -----------------------------------------------------------------------------
    // -----------------------------------------------------------------------------
    public function criteriaSummaryText(&$label, &$value)
    {
        $label = "";
        $value = "";
        $name = $this->query_name;

        if (isset($this->criteria_summary) && $this->criteria_summary) {
            $label = $this->deriveAttribute("column_title", $this->query_name);
            $value = $this->criteria_summary;
        } else {
            if (ReporticoUtility::getRequestItem($this->query_name . "_FROMDATE_DAY", "")) {
                $label = $this->deriveAttribute("column_title", $this->query_name);
                $label = ReporticoLang::translate($label);
                $mth = ReporticoUtility::getRequestItem($name . "_FROMDATE_MONTH", "") + 1;
                $value = ReporticoUtility::getRequestItem($name . "_FROMDATE_DAY", "") . "/" .
                $mth . "/" .
                ReporticoUtility::getRequestItem($name . "_FROMDATE_YEAR", "");
                if (ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "")) {
                    $mth = ReporticoUtility::getRequestItem($name . "_TODATE_MONTH", "") + 1;
                    $value .= "-";
                    $value .= ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "") . "/" .
                    $mth . "/" .
                    ReporticoUtility::getRequestItem($name . "_TODATE_YEAR", "");
                }
            } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "")) {
                $label = $this->deriveAttribute("column_title", $this->query_name);
                $label = ReporticoLang::translate($label);
                $value = ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "");
                if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE", "")) {
                    $value .= "-";
                    $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE");
                }

            } else if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "")) {
                $label = $this->deriveAttribute("column_title", $this->query_name);
                $label = ReporticoLang::translate($label);
                $value = ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "");
                if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE", "")) {
                    $value .= "-";
                    $value .= ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE");
                }

            } else if (ReporticoUtility::getRequestItem("EXPANDED_" . $name, "")) {
                $label = $this->deriveAttribute("column_title", $this->query_name);
                $label = ReporticoLang::translate($label);
                $value .= implode(ReporticoUtility::getRequestItem("EXPANDED_" . $name, ""), ",");
            } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name, "")) {
                $label = $this->deriveAttribute("column_title", $this->query_name);
                $label = ReporticoLang::translate($label);
                $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name, "");
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : criteriaSummaryDisplay
    //
    // For a given criteria item that has been checked to identify the values
    // that would be passed to the main query, this returns the summary of user
    // selected values for displaying in the criteria summary at top of report
    // -----------------------------------------------------------------------------
    public function criteriaSummaryDisplay()
    {
        $text = "";

        $type = $this->criteria_display;

        $value_string = "";

        $params = array();
        $manual_params = array();
        $hidden_params = array();
        $expanded_params = array();
        $manual_override = false;

        if (ReporticoUtility::getRequestItem("MANUAL_" . $this->query_name . "_FROMDATE", "")) {
            $this->criteria_summary = ReporticoUtility::getRequestItem("MANUAL_" . $this->query_name . "_FROMDATE", "");
            if (ReporticoUtility::getRequestItem("MANUAL_" . $this->query_name . "_TODATE", "")) {
                $this->criteria_summary .= "-";
                $this->criteria_summary .= ReporticoUtility::getRequestItem("MANUAL_" . $this->query_name . "_TODATE");
            }
            return;
        }

        if (ReporticoUtility::getRequestItem("HIDDEN_" . $this->query_name . "_FROMDATE", "")) {
            $this->criteria_summary = ReporticoUtility::getRequestItem("HIDDEN_" . $this->query_name . "_FROMDATE", "");
            if (ReporticoUtility::getRequestItem("HIDDEN_" . $this->query_name . "_TODATE", "")) {
                $this->criteria_summary .= "-";
                $this->criteria_summary .= ReporticoUtility::getRequestItem("HIDDEN_" . $this->query_name . "_TODATE");
            }
            return;
        }

        if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
            if (array_key_exists($this->query_name, $_REQUEST)) {
                $params = $_REQUEST[$this->query_name];
                if (!is_array($params)) {
                    $params = array($params);
                }

            }
        }

        $hidden_params = array();
        if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
            if (array_key_exists("HIDDEN_" . $this->query_name, $_REQUEST)) {
                $hidden_params = $_REQUEST["HIDDEN_" . $this->query_name];
                if (!is_array($hidden_params)) {
                    $hidden_params = array($hidden_params);
                }

            }
        }

        $manual_params = array();
        if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
            if (array_key_exists("MANUAL_" . $this->query_name, $_REQUEST)) {
                if ( is_array( $_REQUEST["MANUAL_" . $this->query_name] ) ) {
                    $manual_params = $_REQUEST["MANUAL_" . $this->query_name];
                    $param_string = implode(",",$manual_params);
                }
                else {
                    $manual_params = explode(',', $_REQUEST["MANUAL_" . $this->query_name]);
                    $param_string = $_REQUEST["MANUAL_". $this->query_name];
                }

                if ($manual_params) {
                    $hidden_params = $manual_params;
                    $manual_override = true;
                    $value_string = $param_string;
                }
            }
        }

        $expanded_params = array();
        if (array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
            $expanded_params = $_REQUEST["EXPANDED_" . $this->query_name];
            if (!is_array($expanded_params)) {
                $expanded_params = array($expanded_params);
            }

        }

        if ($this->criteria_type == "LIST") {
            $checkedct = 0;
            $res = &$this->list_values;
            $text = "";
            if (!$res) {
                $text = "";
            } else {
                reset($res);
                $k = key($res);
                for ($i = 0; $i < count($res); $i++) {
                    $line = &$res[$i];
                    $lab = $res[$i]["label"];
                    $ret = $res[$i]["value"];
                    $checked = false;

                    if (in_array($ret, $params)) {
                        $checked = true;
                    }

                    if (in_array($ret, $hidden_params)) {
                        $checked = true;
                    }

                    if (in_array($ret, $expanded_params)) {
                        $checked = true;
                    }

                    if ($checked) {
                        if ($checkedct++) {
                            $text .= ",";
                        }

                        $text .= $lab;
                    }
                }
                $this->criteria_summary = $text;
                return;
            }
        }

        $txt = "";
        if ( !isset($this->lookup_query->targets[0]) )
            return;
        $res = &$this->lookup_query->targets[0]->results;
        if (!$res) {
            $res = array();
            $k = 0;
        } else {
            reset($res);
            $k = key($res);
            $checkedct = 0;
            for ($i = 0; $i < count($res[$k]); $i++) {
                $line = &$res[$i];
                foreach ($this->lookup_query->columns as $ky => $col) {
                    if ($col->lookup_display_flag) {
                        $lab = $res[$col->query_name][$i];
                    }
                    if ($col->lookup_return_flag) {
                        $ret = $res[$col->query_name][$i];
                    }

                    if ($col->lookup_abbrev_flag) {
                        $abb = $res[$col->query_name][$i];
                    }

                }
                $checked = false;

                if (in_array($ret, $params)) {
                    $checked = true;
                }

                if (in_array($ret, $hidden_params) && !$manual_override) {
                    $checked = true;
                }

                if (in_array($ret, $expanded_params)) {
                    $checked = true;
                }

                if (in_array($abb, $hidden_params) && $manual_override) {
                    $checked = true;
                }

                if ($checked) {
                    if ($checkedct++) {
                        $text .= ",";
                    }

                    $text .= $lab;
                }
            }
        }

        if (array_key_exists("EXPAND_" . $this->query_name, $_REQUEST) ||
            array_key_exists("EXPANDCLEAR_" . $this->query_name, $_REQUEST) ||
            array_key_exists("EXPANDSELECTALL_" . $this->query_name, $_REQUEST) ||
            array_key_exists("EXPANDSEARCH_" . $this->query_name, $_REQUEST) ||
            $this->criteria_display == "NOINPUT") {

            $tag = $value_string;
            if (strlen($tag) > 40) {
                $tag = substr($tag, 0, 40) . "...";
            }

            if (!$tag) {
                $tag = "ANY";
            }

            $text .= $tag;
        } else if ($this->criteria_display == "ANYCHAR" || $this->criteria_display == "TEXTFIELD") {
            $txt = $value_string;
        }

        $this->criteria_summary = $text;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaList
    //
    // Generates a criteria list item by taking a string of list labels and values
    // separated by commas and each item separated by =
    // -----------------------------------------------------------------------------
    public function setCriteriaList($in_list)
    {
        // Replace external parameters specified by {USER_PARAM,xxxxx}
        // With external values
        $matches = [];
        if ($this->parent_reportico->execute_mode != "MAINTAIN"  && preg_match_all("/{USER_PARAM,([^}]*)}/", $in_list, $matches)) {
            foreach ($matches[0] as $k => $v) {
                $param = $matches[1][$k];
                if (isset($this->parent_reportico->user_parameters[$param]["function"]) ) {
                    $function = $this->parent_reportico->user_parameters[$param]["function"];
                    if ( !isset($this->parent_reportico->user_functions[$function] )) {
                        trigger_error("User function $function required but not defined in user_functions array", E_USER_ERROR);
                        return;
                    }
                    $result = $this->parent_reportico->user_functions[$function]();
                    $in_list = implode(',', array_map(
                        function ($v, $k) { return sprintf("%s=%s", $k, $v); },
                        $result,
                        array_keys($result)
                    ));
                } else 
                if (isset($this->parent_reportico->user_parameters[$param]["values"]) &&
                    is_array($this->parent_reportico->user_parameters[$param]["values"])
                ) {
                    $in_list = implode(',', array_map(
                        function ($v, $k) { return sprintf("%s=%s", $k, $v); },
                        $this->parent_reportico->user_parameters[$param]["values"],
                        array_keys($this->parent_reportico->user_parameters[$param]["values"])
                    ));
                } else {
                    trigger_error("User parameter $param, specified but not provided to reportico in user_parameters array", E_USER_ERROR);
                }
            }
        }

        if ($in_list) {
            $choices = array();

            $alias = false;
            if ( $aliases = ReporticoApp::get("criteria_list_aliases")) {

                $test = preg_replace("/[{}]/", "", $in_list);

                if ( isset($aliases[$test])) {
                    $alias = $aliases[$test];
                }
            }

            if ( $alias ) {
                    if ( is_array ($alias) ){
                        foreach ( $alias as $item ){
                            if ( is_string($item ))
                                $choices[] = $item;
                        }
                    }
            }
            else if ($in_list == "{connections}" && $this->parent_reportico->framework_parent == "october" ) {
                $choices[] = "Existing October Connection=existingconnection";
                if (isset($this->parent_reportico) && $this->parent_reportico->available_connections) {
                    foreach ($this->parent_reportico->available_connections as $k => $v) {
                        $choices[] = "Database '$k'=byname_$k";
                    }

                }

                $this->criteria_list = $in_list;
            } else
            if ($in_list == "{connections}" && $this->parent_reportico->framework_parent == "laravel" ) {
                $choices[] = "Existing Laravel Connection=existingconnection";
                if (isset($this->parent_reportico) && $this->parent_reportico->available_connections) {
                    foreach ($this->parent_reportico->available_connections as $k => $v) {
                        $choices[] = "Database '$k'=byname_$k";
                    }

                }

                $this->criteria_list = $in_list;
            } else
            if ($in_list == "{connections}") {
                if ( !isset($this->available_connections) ) {
                    $this->available_connections = array(
                        "pdo_mysql" => "MySQL",
                        "pdo_pgsql" => "PostgreSQL with PDO",
                        "oci8" => "Oracle without PDO (Beta)",
                        "pdo_oci" => "Oracle with PDO (Beta)",
                        "pdo_mssql" => "Mssql (with DBLIB/MSSQL PDO)",
                        "pdo_sqlsrv" => "Mssql (with SQLSRV PDO)",
                        "pdo_sqlite3" => "SQLite3",
                        "framework" => "Framework(e.g. Joomla)",
                       );
                }

                // For Yii, Laravel etc show framework option as first option relating to the framework name
                if ( $this->parent_reportico->framework_parent ) {
                    $ftype = ucwords($this->parent_reportico->framework_parent);
                    $choices[] = "My $ftype Connection=framework";
                    unset ( $this->available_connections["framework"] );
                    $this->setCriteriaDefaults("framework");
                }

                foreach ($this->available_connections as $k => $v) {
                    $choices[] = $v . "=" . $k;
                }
                $this->criteria_list = $in_list;
            } else
            if ($in_list == "{languages}") {
                $langs = ReporticoLang::availableLanguages();
                foreach ($langs as $k => $v) {
                    $choices[] = ReporticoLang::templateXlate($v["value"]) . "=" . $v["value"];
                }
                $this->criteria_list = $in_list;
            } else {
                $this->criteria_list = $in_list;
                if (!is_array($in_list)) {
                    $choices = explode(',', $in_list);
                }

            }
            $this->criteria_list = $in_list;


            $this->list_values = [];
            foreach ($choices as $items) {
                $itemval = explode('=', $items);
                if (count($itemval) > 1) {
                    $this->list_values[] = array("label" => $itemval[0],
                        "value" => $itemval[1]);
                }
            }
        }
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDefaults
    // -----------------------------------------------------------------------------
    public function setCriteriaDefaults($in_default, $in_delimiter = false)
    {
        if (!$in_delimiter) {
            $in_delimiter = ",";
        }
        $this->defaults_raw = $in_default;
        $this->defaults = preg_split("/" . $in_delimiter . "/", $this->deriveMetaValue($in_default));
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaLookup
    // -----------------------------------------------------------------------------
    public function setCriteriaLookup(&$lookup_query)
    {
        $this->lookup_query = $lookup_query;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaRequired
    // -----------------------------------------------------------------------------
    public function setCriteriaRequired($criteria_required)
    {
        $this->required = $criteria_required;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaDisplayGroup
    // -----------------------------------------------------------------------------
    public function setCriteriaDisplayGroup($criteria_display_group)
    {
        $this->display_group = $criteria_display_group;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHidden
    // -----------------------------------------------------------------------------
    public function setCriteriaHidden($criteria_hidden)
    {
        $this->hidden = $criteria_hidden;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaType
    // -----------------------------------------------------------------------------
    public function setCriteriaType($criteria_type)
    {
        $this->criteria_type = $criteria_type;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaHelp
    // -----------------------------------------------------------------------------
    public function setCriteriaHelp($criteria_help)
    {
        $this->criteria_help = $criteria_help;
    }

    // -----------------------------------------------------------------------------
    // Function : set_criteria_link
    // -----------------------------------------------------------------------------
    public function setCriteriaLinkReport($in_report, $in_report_item)
    {
        $this->link_to_report = $in_report;
        $this->link_to_report_item = $in_report_item;
    }

    // -----------------------------------------------------------------------------
    // Function : setCriteriaInput
    // -----------------------------------------------------------------------------
    public function setCriteriaInput($in_source, $in_display, $in_expand_display = false, $use = "")
    {
        $this->criteria_type = $in_source;
        $this->criteria_display = $in_display;
        $this->expand_display = $in_expand_display;
        $this->_use = $use;
    }

    // -----------------------------------------------------------------------------
    // Function : collateRequestDate
    // -----------------------------------------------------------------------------
    public function collateRequestDate($in_query_name, $in_tag, $in_default, $in_format)
    {
        $retval = $in_default;
        if (array_key_exists($this->query_name . "_" . $in_tag . "_DAY", $_REQUEST)) {
            if (!class_exists("DateTime", false)) {
                ReporticoApp::handleError("This version of PHP does not have the DateTime class. Must be PHP >= 5.3 to use date criteria");
                return $retval;
            }
            $dy = $_REQUEST[$this->query_name . "_" . $in_tag . "_DAY"];
            $mn = $_REQUEST[$this->query_name . "_" . $in_tag . "_MONTH"] + 1;
            $yr = $_REQUEST[$this->query_name . "_" . $in_tag . "_YEAR"];
            $retval = sprintf("%02d-%02d-%04d", $dy, $mn, $yr);

            $datetime = DateTime::createFromFormat("d-m-Y", $retval);
            $in_format = ReporticoLocale::getLocaleDateFormat($in_format);
            $retval = $datetime->format($in_format);
        }
        return ($retval);
    }


    // -----------------------------------------------------------------------------
    // Function : lookup_ajax
    // -----------------------------------------------------------------------------
    public function &lookup_ajax($in_is_expanding)
    {

        $sessionClass = ReporticoSession();

        $text = "";
        if ($in_is_expanding) {
            $tag_pref = "EXPANDED_";
            $type = $this->expand_display;
        } else {
            $tag_pref = "";
            $type = $this->criteria_display;
        }

        $value_string = "";

        $params = array();
        $manual_params = array();
        $hidden_params = array();
        $expanded_params = array();
        $manual_override = false;

        if (!array_key_exists("clearform", $_REQUEST)) {
            if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
                if (array_key_exists($this->query_name, $_REQUEST)) {
                    $params = $_REQUEST[$this->query_name];
                    if (!is_array($params)) {
                        $params = array($params);
                    }

                }
            }

            $hidden_params = array();
            if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
                if (array_key_exists("HIDDEN_" . $this->query_name, $_REQUEST)) {
                    $hidden_params = $_REQUEST["HIDDEN_" . $this->query_name];
                    if (!is_array($hidden_params)) {
                        $hidden_params = array($hidden_params);
                    }

                }
            }

            $manual_params = array();
            if (!array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
                if (array_key_exists("MANUAL_" . $this->query_name, $_REQUEST)) {

                    if ( is_array($_REQUEST["MANUAL_". $this->query_name]))
                        $manual_params = $_REQUEST["MANUAL_" . $this->query_name];
                    else
                        $manual_params = explode(',', $_REQUEST["MANUAL_" . $this->query_name]);
                    if ($manual_params) {
                        $hidden_params = $manual_params;
                        $manual_override = true;
                    }
                }
            }

            // If this is first time into screen and we have defaults then
            // use these instead
            if (!$hidden_params && $sessionClass::getReporticoSessionParam("firstTimeIn")) {
                $hidden_params = $this->defaults;
                $manual_params = $this->defaults;
            }

            $expanded_params = array();
            if (array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST)) {
                $expanded_params = $_REQUEST["EXPANDED_" . $this->query_name];
                if (!is_array($expanded_params)) {
                    $expanded_params = array($expanded_params);
                }

            }
        } else {
            $hidden_params = $this->defaults;
            $manual_params = $this->defaults;
            $params = $this->defaults;
        }

        $text .= '{"items": [';

        if ( !isset($this->lookup_query->targets[0]) )
            return;
        $res = &$this->lookup_query->targets[0]->results;
        if ($res) {

            reset($res);
            $k = key($res);
            for ($i = 0; $i < count($res[$k]); $i++) {
                $line = &$res[$i];
                foreach ($this->lookup_query->columns as $ky => $col) {

                    if ($col->lookup_display_flag) {
                        $lab = $res[$col->query_name][$i];
                    }
                    if ($col->lookup_return_flag) {
                        $ret = $res[$col->query_name][$i];
                    }

                    if ($col->lookup_abbrev_flag) {
                        $abb = $res[$col->query_name][$i];
                    }

                }

                if ($i > 0) {
                    $text .= ",";
                }

                $text .= "{\"id\":\"$ret\", \"text\":\"$lab\"}";

            }
        }

        $text .= ']}';

        return $text;
    }

    // -----------------------------------------------------------------------------
    // Function : getCriteriaValue
    // -----------------------------------------------------------------------------
    public function getCriteriaValue($in_type, $use_del = true)
    {

        $cls = "";

        switch (strtoupper($in_type)) {
            case "LOWER":
            case "FROM":
            case "RANGE1":
                $cls = $this->getCriteriaClause(false, false, false, true, false, $use_del);
                break;

            case "UPPER":
            case "TO":
            case "RANGE2":
                $cls = $this->getCriteriaClause(false, false, false, false, true, $use_del);
                break;
            case "FULL":
                $cls = $this->getCriteriaClause(true, true, true, false, false, $use_del);
                break;

            case "VALUE":
                $cls = $this->getCriteriaClause(false, false, true, false, false, $use_del);
                break;

            default:
                ReporticoApp::handleError("Unknown Criteria clause type $in_type for criteria " . $this->query_name);
                break;
        }
        return $cls;
    }

    // -----------------------------------------------------------------------------
    // Function : getCriteriaClause
    // -----------------------------------------------------------------------------
    public function getCriteriaClause($lhs = true, $operand = true, $rhs = true, $rhs1 = false, $rhs2 = false, $add_del = true)
    {
        $cls = "";

        if ($this->_use == "SHOW/HIDE-and-GROUPBY") {
            $add_del = false;
        }

        if ($this->column_value == "(ALL)") {
            return $cls;
        }

        if ($this->column_value == "(NOTFOUND)") {
            $cls = " AND 1 = 0";
            return $cls;
        }

        if (!$this->column_value && !$this->column_value_derived) {
            return ($cls);
        }

        $del = '';

        switch ($this->criteria_type) {

            case "ANY":
            case "ANYCHAR":
            case "TEXTFIELD":
                if ($add_del) {
                    $del = $this->getValueDelimiter();
                }

                $extract = explode(',', $this->column_value);
                if (is_array($extract)) {
                    $ct = 0;
                    foreach ($extract as $col) {
                        if (is_string($col)) {
                            $col = trim($col);
                        }

                        if (!$col) {
                            continue;
                        }

                        if ($col == "(ALL)") {
                            continue;
                        }

                        if ($ct == 0) {
                            if ($lhs) {
                                //$cls .= " XX".$this->table_name.".".$this->column_name;
                                $cls .= " AND " . $this->column_name;
                            }
                            if ($rhs) {
                                if ($operand) {
                                    $cls .= " IN (";
                                }

                                $cls .= $del . $col . $del;
                            }
                        } else
                        if ($rhs) {
                            $cls .= "," . $del . $col . $del;
                        }

                        $ct++;
                    }

                    if ($ct > 0 && $rhs) {
                        if ($operand) {
                            $cls .= " )";
                        }
                    }

                } else {
                    if ($lhs) {
                        if ($this->table_name && $this->column_name) {
                            $cls .= " AND " . $this->table_name . "." . $this->column_name;
                        } else
                        if ($this->column_name) {
                            $cls .= " AND " . $this->column_name;
                        }

                    }
                    if ($rhs) {
                        if ($operand) {
                            $cls .= " =" . $del . $this->column_value . $del;
                        } else {
                            $cls .= $del . $this->column_value . $del;
                        }
                    }

                }
                break;

            case "LIST":
                if ($add_del) {
                    $del = $this->getValueDelimiter();
                }

                if (!is_array($this->column_value)) {
                    $this->column_value = explode(',', $this->column_value);
                }

                if (is_array($this->column_value)) {
                    $ct = 0;
                    foreach ($this->column_value as $col) {
                        if (is_string($col)) {
                            $col = trim($col);
                        }

                        if ($col == "(ALL)") {
                            continue;
                        }

                        if ($ct == 0) {
                            if ($lhs) {
                                if ($this->table_name && $this->column_name) {
                                    $cls .= " AND " . $this->table_name . "." . $this->column_name;
                                } else
                                if ($this->column_name) {
                                    $cls .= " AND " . $this->column_name;
                                }

                            }
                            if ($rhs) {
                                if ($operand) {
                                    $cls .= " IN (";
                                }

                                $cls .= $del . $col . $del;
                            }
                        } else
                        if ($rhs) {
                            $cls .= "," . $del . $col . $del;
                        }

                        $ct++;
                    }

                    if ($ct > 0) {
                        if ($operand) {
                            $cls .= " )";
                        }
                    }

                } else {
                    if ($lhs) {
                        if ($this->table_name && $this->column_name) {
                            $cls .= " AND " . $this->table_name . "." . $this->column_name;
                        } else
                        if ($this->column_name) {
                            $cls .= " AND " . $this->column_name;
                        }

                    }
                    if ($rhs) {
                        if ($operand) {
                            $cls .= " =" . $del . $this->column_value . $del;
                        } else {
                            $cls .= $del . $this->column_value . $del;
                        }

                    }
                }
                break;

            case "DATE":
                $cls = $this->widget->getCriteriaClause($lhs, $operand, $rhs, $rhs1, $rhs2, $add_del);
                break;

            case "DATERANGE":
                $cls = $this->widget->getCriteriaClause($lhs, $operand, $rhs, $rhs1, $rhs2, $add_del);
                break;

            case "LOOKUP":
                if ($add_del) {
                    $del = $this->getValueDelimiter();
                }

                if (!is_array($this->column_value)) {
                    $this->column_value = explode(',', $this->column_value);
                }

                if (is_array($this->column_value)) {
                    $ct = 0;
                    foreach ($this->column_value as $col) {
                        if (is_string($col)) {
                            $col = trim($col);
                        }

                        if ($col == "(ALL)") {
                            continue;
                        }

                        if ($ct == 0) {
                            if ($lhs) {
                                if ($this->table_name && $this->column_name) {
                                    $cls .= " AND " . $this->table_name . "." . $this->column_name;
                                } else
                                if ($this->column_name) {
                                    $cls .= " AND " . $this->column_name;
                                }

                            }
                            if ($rhs) {
                                if ($operand) {
                                    $cls .= " IN (";
                                }

                                $cls .= $del . $col . $del;
                            }
                        } else
                        if ($rhs) {
                            $cls .= "," . $del . $col . $del;
                        }

                        $ct++;
                    }

                    if ($ct > 0) {
                        if ($operand) {
                            $cls .= " )";
                        }
                    }

                } else {
                    if ($lhs) {
                        if ($this->table_name && $this->column_name) {
                            $cls .= " AND " . $this->table_name . "." . $this->column_name;
                        } else
                        if ($this->column_name) {
                            $cls .= " AND " . $this->column_name;
                        }

                    }
                    if ($rhs) {
                        if ($operand) {
                            $cls .= " =" . $del . $this->column_value . $del;
                        } else {
                            $cls .= $del . $this->column_value . $del;
                        }

                    }
                }
                break;

            default:
                $cls = $this->widget->getCriteriaClause($lhs, $operand, $rhs, $rhs1, $rhs2, $add_del);
                break;
        }

        return ($cls);
    }

    public function createWidget($expanding = false)
    {
        $text = "";

        $type = $this->criteria_type;

        switch ($type) {
            case "LIST":
                $this->widget = \Reportico\Widgets\CriteriaList::createCriteriaList($this->parent_reportico, $this, $expanding );
                $this->widget->criteria = $this;
                break;

            case "LOOKUP":
                $this->widget = \Reportico\Widgets\CriteriaLookup::createCriteriaLookup($this->parent_reportico, $this, $expanding );
                $this->widget->criteria = $this;
                break;

            case "DATE":
                //$text .= $this->date_display();
                $this->widget = new \Reportico\Widgets\DatePicker(false);
                $this->widget->criteria = $this;
                break;

            case "DATERANGE":
                //$text .= $this->daterange_display();
                $this->widget = new \Reportico\Widgets\DateRangePicker(false);
                $this->widget->criteria = $this;
                break;

            case "ANYCHAR":
            case "TEXTFIELD":
                $this->widget = new \Reportico\Widgets\TextField($this->parent_reportico);
                $this->widget->criteria = $this;

                //$tag = "";
                //$tag .= '<input  type="text" class="' . $this->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" name="MANUAL_' . $this->query_name . '"';
                //$tag .= ' size="50%"';
                //$tag .= ' value="' . $this->column_value . '">';
                //$text .= $tag;

                break;

            case "SQLCOMMAND":
                $tag = "";
                $tag .= '<br><textarea  cols="70" rows="20" class="' . $this->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" type="text" name="MANUAL_' . $this->query_name . '">';
                $tag .= $this->column_value;
                $tag .= "</textarea>";
                $text .= $tag;
                break;

            default:
                if ( $type ) {
                    $class = "\\Reportico\\Widgets\\$type";
                    $this->widget = new $class(false);
                    $this->widget->criteria = $this;
                }
                break;
        }

        return $text;
    }
    public function renderSelection($expanding = false)
    {
        $text = "";

        $type = $this->criteria_type;

        switch ($type) {
            case "LIST":
                $text .= (\Reportico\Widgets\CriteriaList::createCriteriaList($this->parent_reportico, $this, $expanding ))->render();
                break;

            case "LOOKUP":
                //var_dump($_REQUEST);
                //ReporticoUtility::trace("RENDER $type $this->criteria_display");
                $thereishidden = false;
                if(array_key_exists("HIDDEN_" . $this->query_name, $_REQUEST)){
                    $thereishidden = true;
                    $hidden = $_REQUEST["HIDDEN_".$this->query_name];
                    if ( is_array($hidden) && count($hidden) == 1 && isset($hidden[0])){
                        if ( $hidden[0] == "(ALL)")
                            $thereishidden = false;
                    }
                }

                if (
                    ($this->criteria_display !== "TEXTFIELD" && $this->criteria_display !== "ANYCHAR" && $this->criteria_display != "NOINPUT")
                    ||
                    (
                        array_key_exists("EXPANDED_" . $this->query_name, $_REQUEST) ||
                        array_key_exists("EXPAND_" . $this->query_name, $_REQUEST) ||
                        $thereishidden ||
                        $this->column_value
                    )
                ) {

                    // Dont bother running select for criteria lookup if criteria item is a dynamic
                    $this->executeCriteriaLookup();
                }
                //$text .= $this->lookup_display(false);
                $text .= (\Reportico\Widgets\CriteriaLookup::createCriteriaLookup($this->parent_reportico, $this, $expanding ))->render();
                break;

            case "DATE":
                $this->widget = new \Reportico\Widgets\DatePicker(false);
                $this->widget->criteria = $this;
                $text .= $this->widget->render();
                break;

            case "DATERANGE":
                $this->widget = new \Reportico\Widgets\DateRangePicker(false);
                $this->widget->criteria = $this;
                $text .= $this->widget->render();
                break;

            case "ANYCHAR":
            case "TEXTFIELD":
                $this->widget = new \Reportico\Widgets\TextField($this->parent_reportico);
                $this->widget->criteria = $this;
                $text = $this->widget->render();

                //$tag = "";
                //$tag .= '<input  type="text" class="' . $this->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" name="MANUAL_' . $this->query_name . '"';
                //$tag .= ' size="50%"';
                //$tag .= ' value="' . $this->column_value . '">';
                //$text .= $tag;

                break;

            case "SQLCOMMAND":
                $tag = "";
                $tag .= '<br><textarea  cols="70" rows="20" class="' . $this->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" type="text" name="MANUAL_' . $this->query_name . '">';
                $tag .= $this->column_value;
                $tag .= "</textarea>";
                $text .= $tag;
                break;

            default:
                if ( $type ) {
                    $class = "\\Reportico\\Widgets\\$type";
                    $this->widget = new $class(false);
                    $this->widget->criteria = $this;
                    $text .= $this->widget->render();
                }
                break;
        }

        return $text;
    }
}
