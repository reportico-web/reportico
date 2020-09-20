<?php

namespace Reportico\Engine;

/**
 * Class Assignment
 *
 * Identifies instructions for report column output
 * that must be calculated upon report execution.
 */
class Assignment extends ReporticoObject
{
    public $query_name;
    public $expression;
    public $criteria;
    public $else;
    public $raw_expression;
    public $raw_criteria;
    public $styletype;

    public static $sections = array (
        "CELL",
        "ALLCELLS",
        "COLUMNHEADERS",
        "ROW",
        "PAGE",
        "BODY",
        "GROUPHEADERLABEL",
        "GROUPHEADERVALUE",
        "GROUPTRAILER",
        );

    // Indicates an operation which causes an action rather than setting a value
    public $non_assignment_operation = false;

    public $usage = array(
        "description" => "Assign the result of an expression to a report column",
        "methods" => array(
            "expression" => array(
                "description" => "Create an expression to assign to a column",
                "parameters" => array( "column" => "Name of column (existing or new) to assign an expression to")
            ),
            "set" => array(
                "description" => "The expression value to assign to the column. Can include {} notation to include other volumn values.",
                "parameters" => array( "expression" => "Assignment value can include {} botation")
            ),
            "if" => array(
                "description" => "Only apply the expression if the passed condition is true",
                "parameters" => array( "condition" => "If the passed condition (as a PHP expression) is true the expression is set")
            ),
            "else" => array(
                "description" => "Set this expression if the condition is false",
                "parameters" => array( "expression" => "Set to this expression (as a PHP expression) if the condition results is not true")
            ),
            "imageurl" => array( "description" => "Assign to the column the url of an image",
                "parameters" => array(
                    "url" => "The url thats points to an image (the url can contain report column values using the {} notation.",
                    "width" => "(Optional) The width in pixels of the displayed image, default is actual image width",
                    "height" => "(Optional) The height in pixels of the displayed image, default is actual image height",
                )
            ),
            "section" => array( "description" => "Select an area of a report to apply a style to, using the style() option",
                "parameters" => array( "section" => array( "description" => "Element/area of the report to style",
                    "options" => [
                        "CELL" => "Apply the style to the cell of the expression column",
                        "ALLCELLS" => "Apply the style to all cells of the current report row",
                        "COLUMNHEADERS" => "Apply the style to the report column header cells",
                        "ROW" => "Apply the style to the whole report row",
                        "PAGE" => "Apply the style to the current report cell/group section block",
                        "BODY" => "Apply the style to block containing the report detail, group headers and charts",
                        "GROUPHEADERLABEL" => "Aply the style to the group label",
                        "GROUPHEADERVALUE" => "Aply the style to the group value",
                        "GROUPTRAILER" => "Aply the style to the group trailer",
                    ]
                ),
            )),
            "style" => array( "description" => "Set a CSS style of report element specified by the seciton() method or to the cell of the specified expression column",
                "parameters" => array(
                    "CSS style" => "A string containing CSS styles to apply to the column cell or section" ,
                )
            ),

            "drilldownToUrl" => array( "description" => "Generate a report link to drill down to another url script, which could be a reportico script that can accept the passed criteria or another script that does something else",
                "parameters" => array(
                    "drillDownUrl" => "The url to drilldown to" ,
                )
            ),
            "drilldownToReport" => array( "description" => "Generate a report link to drill down to another Reportico report deinfed in a project",
                "parameters" => array(
                    "drillDownProject" => "The name of the project to drilldown to" ,
                    "drillDownReport" => "The name of the report (without the xml extension) to drill down to" ,
                )
            ),
            "where" => array( "description" => "The criteria items from the selected row to pass to a drill down report",
                "parameters" => array(
                    "parameters" => "An array mapping target report criteria items with the columns in the report row pass in. So the keys are target report items and the values are the column names to pass to those criteria items"
                    )
            ),
            "link" => array( "description" => "Assign to a column a url link. The url can contain column values by using the {} notation",
                "parameters" => array(
                    "label" => "The label to display for the url link. This can include the actuial values of columns using the {} notation",
                    "url" => "The URL to go to when clicked. This can pass columns columns through using the {} notation",
                )
            ),
            "skip" => array( "description" => "Do not output the report row. Use in conjunction with the if() method to selectively ignore outputting data"),
            "sum" => array( "description" => "Sets the column to a running total optionally grouped by a column",
                "parameters" => array(
                    "sum column" => "The column to sum on",
                    "group column" => "(Optional) The column to base the group sum on. When this value changes the sum is reset to zero.",
                )
            ),
            "avg" => array( "description" => "Sets the column to a running average optionally grouped by a column",
                "parameters" => array(
                    "average column" => "The column to average on",
                    "group column" => "(Optional) The column to base the group average on. When this value changes the average is reset to zero.",
                )
            ),
            "min" => array( "description" => "Sets the column to a minimum value of a column",
                "parameters" => array(
                    "average column" => "The column to calculate the minimum from on",
                    "group column" => "(Optional) The column to base the group minimum on. When this value changes the minimum is reset",
                )
            ),
            "max" => array( "description" => "Sets the column to a maximum value of a column",
                "parameters" => array(
                    "average column" => "The column to calculate the maximum from on",
                    "group column" => "(Optional) The column to base the group maximum on. When this value changes the maximum is reset",
                )
            ),
            "old" => array( "description" => "Sets the column to the value of source column from the previous report row",
                "parameters" => array(
                    "average column" => "The column to take the previous value from",
                )
            ),
        )
    );

    public function __construct($query_name = false, $expression = "", $criteria = "", $else = "")
    {
        $this->raw_expression = $expression;
        $this->raw_criteria = $criteria;
        $this->query_name = $query_name;
        $this->expression = $this->reporticoStringToPhp($expression);
        $this->criteria = $this->reporticoStringToPhp($criteria);
        $this->else = $this->reporticoStringToPhp($else);
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        switch ( $method ) {

            case "build":
                $styletype = false;
                $builder = $args[0];
                $builder->store = [];

                if (!isset($args[1])) {
                    $colname = ReporticoUtility::getFirstColumn($builder->engine->columns)->query_name;
                } else {
                    if ( $builder->engine->getColumn($args[1])) {
                        $styletype = "CELL";
                        $colname = $args[1];
                    }
                    else {
                        if ( in_array($args[1], Assignment::$sections) ) {
                            $styletype = $args[1];
                            $colname = ReporticoUtility::getFirstColumn($builder->engine->columns)->query_name;
                        } else {
                            $colname = $args[1];
                        }
                        if ( $builder->level == "section" || $builder->level == "element" ) {
                            $styletype = $args[1];
                            $colname = ReporticoUtility::getFirstColumn($builder->engine->columns)->query_name;
                        }
                        else
                            $colname = $args[1];
                    }
                }
                if ( !$builder->engine->getColumn($colname))
                    $builder->engine->createQueryColumn($colname, "", "", "", "", '####.###', false);
                $assignment = $builder->engine->addAssignment($colname, false, "");
                $assignment->builder = $builder;
                $assignment->styletype = $styletype;
                $builder->stepInto("expression", $assignment, "\Reportico\Engine\Assignment");
                return $builder;
                break;

        }
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args)
    {
        $exitLevel = false;
        switch ( $method ) {

            case "usage":
                echo $this->builderUsage("expression");
                break;

            case "imageurl":
            case "url":
                $width = isset($args[1]) ? $args[1] : false;
                $height = isset($args[2]) ? $args[2] : false;
                $this->setExpression("embed_image(\"{$args[0]}\",$width,$height)");
                break;

            case "style":
                $styles = isset($args[0]) ? $args[0] : false;
                $column = false;
                if ( $this->styletype == "CELL" )
                    $column = $this->query_name;
                $this->non_assignment_operation = true;
                if ( $styles)
                    $this->builder->engine->applyStyleset($this->styletype, $styles, $column, false, false, $this);
                break;

            case "drilldownToReport":
                $drilldownToProject = isset($args[0]) ? $args[0] : false;
                $drilldownToReport = isset($args[1]) ? $args[1] : false;
                if ( !$drilldownToProject || !$drilldownToReport ) {
                    trigger_error("Drill down to report specified without specifying project and report as parameters", E_USER_ERROR);
                }
                $this->builder->store["drilldownToProject"] = $args[0];
                $this->builder->store["drilldownToReport"] = $args[1];
                break;

            case "drill":
            case "drilldown":
            case "drilldownToUrl":
                $this->builder->store["drilldownToUrl"] = $args[0];
                break;

            //case "target":
            //case "targetid":
                //break;

            case "where":

                $params = $args[0];
                $drilldownToReport = $this->builder->retrieve("drilldownToReport");
                $drilldownToProject = $this->builder->retrieve("drilldownToProject");
                $drilldownToUrl = $this->builder->retrieve("drilldownToUrl");



                if ( !$drilldownToUrl && !$drilldownToReport) {
                    trigger_error("Drill parameters specified without drilldown defined by drilldownToUrl() or drilldownToReport()", E_USER_ERROR);
                }

                $content = $this->raw_expression;
                if ( !$content )
                    $content = "'Drill'";

                echo $this->builder->engine->initial_project;
                //if ( !preg_match("/^\//", $drilldownTo ) || preg_match("/^http/", $drilldownTo ) ) {
                if ( $drilldownToUrl ) {
                    $url = $drilldownToUrl . "?execute_mode=EXECUTE&project=" . ReporticoApp::getConfig("project");
                    echo $url."<BR>";

                    $midbit = "";

                    foreach ($params as $k => $v) {
                        $midbit .= "&MANUAL_" . $k . "='.{" . $params[$v] . "}.'";
                    }
                
                    if ($midbit) {
                        $url .= $midbit;
                        $this->setExpression("embed_hyperlink($content, '" . $url . "', true, true)");
                    }

                } else {

                    //echo "Report: $drilldownToProject/$drilldownToReport Url: $drilldownToUrl <Br>";

                    $this->builder->engine->setProjectEnvironment($drilldownToProject, $this->builder->engine->projects_folder, $this->builder->engine->admin_projects_folder);

                    $q = new Reportico();
                    $q->projects_folder = $this->builder->engine->projects_folder;
                    $q->reports_path = $q->projects_folder . "/" . ReporticoApp::getConfig("project");
    
                    $reader = new XmlReader($q, $drilldownToReport, false, false, false, true);
                    $reader->xml2query();

                    $this->builder->engine->deriveAjaxOperation();
                    $url = $this->builder->engine->getStartActionUrl() . "?xmlin=" . $drilldownToReport . "&execute_mode=EXECUTE&project=" . $drilldownToProject;
                    $midbit = "";

                    foreach ($q->lookup_queries as $k => $v) {

                        if ( isset($params[$v->query_name]) && $params[$v->query_name]) {

                            if ($v->criteria_type == "DATERANGE") {
                                $midbit .= "&MANUAL_" . $v->query_name . "_FROMDATE='.{" . $params[$v->query_name] . "}.'&" .
                                "MANUAL_" . $v->query_name . "_TODATE='.{" . $params[$v->query_name] . "}.'";
                            } else {
                                $midbit .= "&MANUAL_" . $v->query_name . "='.{" . $params[$v->query_name] . "}.'";
                            }
    
                        }
                    }
                
                    if ($midbit) {
                        $url .= $midbit;
                        $this->setExpression("embed_hyperlink($content, '" . $url . "', true, true)");
                    }
                }

                break;

            case "hyperlink":
            case "link":
                $label = isset($args[0]) ? $args[0] : "link";
                $url = isset($args[1]) ? $args[1] : false;
                $newwindow = isset($args[2]) ? $args[2] : true;
                $independent = isset($args[3]) ? $args[3] : 0;
                $this->setExpression("embed_hyperlink(\"$label\",\"$url\",$newwindow,$independent)");
                break;

            case "expression":
                $this->setExpression($args[0]);
                break;

            case "set":
                $this->setExpression($args[0]);
                break;

            case "if":
                $this->setCriteria($args[0]);
                break;

            case "else":
                $this->setElse($args[0]);
                break;

            case "skip":
            case "skipline":
                $method = "skipline";
                $this->setExpression( "$method()" );
                //$this->builder->engine->getColumn($this->query_name)->setAttribute("column_display", "hide");
                break;

            case "prev": $method = "old";

            case "old":
            case "avg":
            case "sum":
            case "min":
            case "max":
                $agg = isset($args[0]) ? $args[0] : false;
                $on = isset($args[1]) ? $args[1] : false;
                //echo $on ? "\$this->$method({{$agg}},\"{{$on}}\")" : "$method({{$agg}})<BR>";
                $this->setExpression($on ? "$method({{$agg}},{{$on}})" : "$method({{$agg}})");
                break;

            case "end":
            default:
                $exitLevel = true;
                break;
        }

        if (!$exitLevel) {
            return $this;
        }

        return false;
    }



    /**
     * @param $value
     *
     * Apply an expression to the assignment
     */
    public function setExpression($value) {
        $this->raw_expression = $value;
        $this->expression = $this->reporticoStringToPhp($value);
    }

    /**
     * @param $value
     *
     * Apply a test condition to the assignment
     */
    public function setCriteria($value) {
        $this->criteria = $this->reporticoStringToPhp($value);
    }

    /**
     * @param $value
     *
     * Apply an else expression
     */
    public function setElse($value) {
        $this->else = $this->reporticoStringToPhp($value);
    }

    // -----------------------------------------------------------------------------
    // Function : reporticoLookupStringToPhp
    // -----------------------------------------------------------------------------
    public function reporticoLookupStringToPhp($in_string)
    {
        $out_string = preg_replace('/{([^}]*)}/',
            '\"".$this->lookup_queries[\'\1\']->column_value."\"',
            $in_string);

        $cmd = '$out_string = "' . $out_string . '";';
        // echo  "==$cmd===";
        eval($cmd);
        return $out_string;
    }

    // -----------------------------------------------------------------------------
    // Function : reporticoMetaSqlCriteria
    // -----------------------------------------------------------------------------
    public static function reporticoMetaSqlCriteria(&$in_query, $in_string, $prev_col_value = false, $no_warnings = false, $execute_mode = "EXECUTE")
    {
        $sessionClass = ReporticoSession();

        // Replace user parameters with values
        $external_param1 = $sessionClass::getReporticoSessionParam("external_param1");
        $external_param2 = $sessionClass::getReporticoSessionParam("external_param2");
        $external_param3 = $sessionClass::getReporticoSessionParam("external_param3");
        $external_user = $sessionClass::getReporticoSessionParam("external_user");

        if ($external_param1) {
            $in_string = preg_replace("/{EXTERNAL_PARAM1}/", "'" . $external_param1 . "'", $in_string);
        }

        if ($external_param2) {
            $in_string = preg_replace("/{EXTERNAL_PARAM2}/", "'" . $external_param2 . "'", $in_string);
        }

        if ($external_param3) {
            $in_string = preg_replace("/{EXTERNAL_PARAM3}/", "'" . $external_param3 . "'", $in_string);
        }

        if ($external_user) {
            $in_string = preg_replace("/{FRAMEWORK_USER}/", "'" . $external_user . "'", $in_string);
        }

        if ($external_user) {
            $in_string = preg_replace("/{USER}/", "'" . $external_user . "'", $in_string);
        }

        // Support for limesurvey prefix
        if (isset($in_query->user_parameters["lime_"])) {
            $in_string = preg_replace("/{lime_}/", $in_query->user_parameters["lime_"], $in_string);
            $in_string = preg_replace("/{prefix}/", $in_query->user_parameters["lime_"], $in_string);
        }

        // Replace External parameters specified by {USER_PARAM,xxxxx}
        if (preg_match_all("/{USER_PARAM,([^}]*)}/", $in_string, $matches)) {
            foreach ($matches[0] as $k => $v) {
                $param = $matches[1][$k];
                if (isset($in_query->user_parameters[$param])) {
                    $in_string = preg_replace("/{USER_PARAM,$param}/", $in_query->user_parameters[$param], $in_string);
                } else {
                    trigger_error("User parameter $param, specified but not provided to reportico", E_USER_ERROR);
                }
            }
        }

        $looping = true;
        $out_string = $in_string;
        $ct = 0;
        while ($looping) {
            $ct++;
            if ($ct > 100) {
                if (!$no_warnings) {
                    echo "Problem with SQL cannot resolve Criteria Items $crit<br>";
                }

                break;
            }
            $regpat = "/{([^}]*)/";
            if (preg_match($regpat, $out_string, $matches)) {
                $crit = $matches[1];
                $first = substr($crit, 0, 1);
                $critexp = $crit;
                if ($first == "=") {
                    $crit = substr($crit, 1);
                    $critexp = $crit;
                    $clause = "";
                    $label = "";
                    if (array_key_exists($crit, $in_query->lookup_queries)) {
                        $clause = $in_query->lookup_queries[$crit]->criteriaSummaryText($label, $clause);
                    } else if ($cl = ReporticoUtility::getQueryColumn($crit, $in_query->columns)) {
                        if ($prev_col_value) {
                            $clause = $cl->old_column_value;
                        } else {
                            $clause = $cl->column_value;
                        }
                    } else {
                        ReporticoApp::handleError("Un1known Criteria Item $crit in Query $in_string");
                        return $in_string;
                    }
                } else {
                    $eltype = "VALUE";
                    $showquotes = true;
                    $surrounder = false;

                    if (preg_match("/([!])(.*)/", $crit, $critel)) {
                        $surrounder = $critel[1];
                        $crit = $critel[2];
                    }
                    if (preg_match("/(.*),(.*),(.*)/", $crit, $critel)) {
                        $crit = $critel[1];
                        $eltype = $critel[2];
                        if ($critel[3] == "false") {
                            $showquotes = false;
                        }

                    }
                    if (preg_match("/(.*);(.*);(.*)/", $crit, $critel)) {
                        $crit = $critel[1];
                        $eltype = $critel[2];
                        if ($critel[3] == "false") {
                            $showquotes = false;
                        }

                    }
                    if (preg_match("/(.*),(.*)/", $crit, $critel)) {
                        $crit = $critel[1];
                        if ($critel[2] == "false") {
                            $showquotes = false;
                        } else {
                            $eltype = $critel[2];
                        }

                    }
                    if ($surrounder == "!") {
                        $showquotes = false;
                    }

                    if (array_key_exists($crit, $in_query->lookup_queries)) {
                        switch (strtoupper($eltype)) {
                            case "FULL":
                                $clause = $in_query->lookup_queries[$crit]->getCriteriaClause(true, true, true, false, false, $showquotes);
                                break;

                            case "LOWER":
                            case "FROM":
                            case "RANGE1":
                                $clause = $in_query->lookup_queries[$crit]->getCriteriaClause(false, false, false, true, false, $showquotes);
                                break;

                            case "UPPER":
                            case "TO":
                            case "RANGE2":
                                $clause = $in_query->lookup_queries[$crit]->getCriteriaClause(false, false, false, false, true, $showquotes);
                                break;

                            case "VALUE":
                            default:
                                //echo $crit;
                                //echo get_class($in_query);
                                //foreach ($in_query->lookup_queries as $k => $v) {
                                    //echo $k;
                                    //echo get_class($v)."<BR>";
                                //}
                                $clause = $in_query->lookup_queries[$crit]->getCriteriaClause(false, false, true, false, false, $showquotes);
                        }
                        if ($execute_mode == "MAINTAIN" && !$clause) {
                            $clause = "'DUMMY'";
                        }
                    } else if ($cl = ReporticoUtility::getQueryColumn($crit, $in_query->columns)) {
                        if ($prev_col_value) {
                            $clause = $cl->old_column_value;
                        } else {
                            $clause = $cl->column_value;
                        }

                    }
                    //else if ( strtoupper($crit) == "REPORT_TITLE" )
                    //{
                    //$clause = "go";
                    //}
                    else {
                        if ( !$no_warnings )
                            echo "Unknown Criteria Item $crit in Query $in_string";
                        //ReporticoApp::handleError( "Unknown Criteria Item $crit in Query $in_string");
                        return $in_string;
                    }
                }

                if (!$clause) {
                    $out_string = preg_replace("/\[[^[]*\{$critexp\}[^[]*\]/", '', $out_string);
                } else {
                    $out_string = preg_replace("/\{=*$critexp\}/",
                        $clause,
                        $out_string);
                    $out_string = preg_replace("/\[\([^[]*\)\]/", "\1", $out_string);
                }

            } else {
                $looping = false;
            }

        }

        $out_string = preg_replace("/\[\[/", "<STARTBRACKET>", $out_string);
        $out_string = preg_replace("/\]\]/", "<ENDBRACKET>", $out_string);
        $out_string = preg_replace("/\[/", "", $out_string);
        $out_string = preg_replace("/\]/", "", $out_string);
        $out_string = preg_replace("/<STARTBRACKET>/", "[", $out_string);
        $out_string = preg_replace("/<ENDBRACKET>/", "]", $out_string);
        // echo "<br>Meta clause: $out_string<BR>";

        //$out_string = addcslashes($out_string, "\"");
        //$cmd = trim('$out_string = "'.$out_string.'";');
        //echo $out_string;

        //if ( $cmd )
        //eval($cmd);
        return $out_string;
    }

    // -----------------------------------------------------------------------------
    // Function : reporticoStringToPhp
    // -----------------------------------------------------------------------------
    public function reporticoStringToPhp($in_string)
    {

        // first change '(colval)' parameters
        $out_string = $in_string;

        $out_string = preg_replace('/{TARGET_STYLE}/',
            '$this->target_style',
            $out_string);

        $out_string = preg_replace('/{TARGET_FORMAT}/',
            '$this->target_format',
            $out_string);

        $out_string = preg_replace('/old\({([^}]*)},{([^}]*)}\)/',
            '$this->old("\1")',
            $out_string);

        $out_string = preg_replace('/old\({([^}]*)},{([^}]*)}\)/',
            '$this->old("\1")',
            $out_string);

        $out_string = preg_replace('/old\({([^}]*)}\)/',
            '$this->old("\1")',
            $out_string);

        $out_string = preg_replace('/max\({([^}]*)},{([^}]*)}\)/',
            '$this->max("\1","\2")',
            $out_string);

        $out_string = preg_replace('/max\({([^}]*)}\)/',
            '$this->max("\1")',
            $out_string);

        $out_string = preg_replace('/min\({([^}]*)},{([^}]*)}\)/',
            '$this->min("\1","\2")',
            $out_string);

        $out_string = preg_replace('/min\({([^}]*)}\)/',
            '$this->min("\1")',
            $out_string);

        $out_string = preg_replace('/avg\({([^}]*)},{([^}]*)}\)/',
            '$this->avg("\1","\2")',
            $out_string);

        $out_string = preg_replace('/avg\({([^}]*)}\)/',
            '$this->avg("\1")',
            $out_string);

        $out_string = preg_replace('/sum\({([^}]*)},{([^}]*)}\)/',
            '$this->sum("\1","\2")',
            $out_string);

        $out_string = preg_replace('/sum\({([^}]*)}\)/',
            '$this->sum("\1")',
            $out_string);

        $out_string = preg_replace('/imagequery\(/',
            '$this->imagequery(',
            $out_string);

        $out_string = preg_replace('/reset\({([^}]*)}\)/',
            '$this->reset("\1")',
            $out_string);

        $out_string = preg_replace('/changed\({([^}]*)}\)/',
            '$this->changed("\1")',
            $out_string);

        $out_string = preg_replace('/groupsum\({([^}]*)},{([^}]*)},{([^}]*)}\)/',
            '$this->groupsum("\1","\2", "\3")',
            $out_string);

        //$out_string = preg_replace('/count\(\)/',
        //'$this->query_count',
        //$out_string);
        $out_string = preg_replace('/lineno\({([^}]*)}\)/',
            '$this->lineno("\1")',
            $out_string);

        if (preg_match('/skipline\(\)/', $out_string)) {
            $this->non_assignment_operation = true;
            $out_string = preg_replace('/skipline\(\)/',
                '$this->skipline()',
                $out_string);
        }

        // Backward compatibility for previous apply_style
        if (preg_match('/apply_style\(.*\)/', $out_string)) {
            $this->non_assignment_operation = true;
            $out_string = preg_replace('/apply_style\(/',
                'applyStyle("' . $this->query_name . "\",", $out_string);
        }

        if (preg_match('/applyStyle\(.*\)/', $out_string)) {
            $this->non_assignment_operation = true;
            $out_string = preg_replace('/applyStyle\(/',
                '$this->applyStyle("' . $this->query_name . "\",", $out_string);
        }

        if (preg_match('/embed_image\(.*\)/', $out_string)) {
            $this->non_assignment_operation = true;
            $out_string = preg_replace('/embed_image\(/',
                '$this->embedImage("' . $this->query_name . "\",", $out_string);
        }

        if (preg_match('/embed_hyperlink\(.*\)/', $out_string)) {
            $this->non_assignment_operation = true;
            $out_string = preg_replace('/embed_hyperlink\(/',
                '$this->embedHyperlink("' . $this->query_name . "\",", $out_string);
        }

        $out_string = preg_replace('/lineno\(\)/',
            '$this->lineno()',
            $out_string);

        $out_string = preg_replace('/count\({([^}]*)}\)/',
            '$this->lineno("\1")',
            $out_string);

        $out_string = preg_replace('/count\(\)/',
            '$this->lineno()',
            $out_string);

        $out_string = preg_replace('/{([^}]*)}/',
            //'$this->columns[\'\1\']->column_value',
            '$this->getQueryColumnValue(\'\1\', $this->columns)',
            $out_string);

        return $out_string;
    }

}
