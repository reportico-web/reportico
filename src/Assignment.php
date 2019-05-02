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

    public function __construct($query_name, $expression = "", $criteria = "", $else = "")
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
                $assignment = $builder->engine->addAssignment($colname, "''", "");
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

            case "drill":
            case "drilldown":
            case "drilldownTo":
                $this->builder->store["drilldownToReport"] = $args[0];
                break;

            case "target":
            case "targetid":
                break;

            case "where":

                $params = $args[0];
                $drilldownTo = $this->builder->retrieve("drilldownToReport");

                if ( !$drilldownTo ) {
                    trigger_error("Drill parameters specified without drilldown defined", E_USER_ERROR);
                }

                $this->builder->engine->setProjectEnvironment($this->builder->engine->initial_project, $this->builder->engine->projects_folder, $this->builder->engine->admin_projects_folder);

                $q = new Reportico();

                $q->projects_folder = $this->builder->engine->projects_folder;


                $q->reports_path = $q->projects_folder . "/" . ReporticoApp::getConfig("project");


                $reader = new XmlReader($q, $drilldownTo, false);
                $reader->xml2query();

                $content = $this->raw_expression;
                if ( !$content )
                    $content = "Drill";

                $this->builder->engine->deriveAjaxOperation();
                $url = $this->builder->engine->getActionUrl() . "?xmlin=" . $drilldownTo . "&execute_mode=EXECUTE&target_format=HTML&project=" . ReporticoApp::getConfig("project");
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
                    $this->setExpression("embed_hyperlink('" . $content . "', '" . $url . "', true, true)");
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
                $this->builder->engine->getColumn($this->query_name)->setAttribute("column_display", "hide");
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
                $this->setExpression($on ? "$method({{$agg}},\"{{$on}}\")" : "$method({{$agg}})");
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
                    echo "Problem with SQL cannot resolve Criteria Items<br>";
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
                        switch ($eltype) {
                            case "FULL":
                                $clause = $in_query->lookup_queries[$crit]->getCriteriaClause(true, true, true, false, false, $showquotes);
                                break;

                            case "RANGE1":
				$clause = $in_query->lookup_queries[$crit]->getCriteriaClause(false, false, false, true, false, $showquotes);
                                break;

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
