<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 * Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

class Widget
{
    // Define asset manager
    public $config = false;
    protected $engine = false;
    protected $manager = false;
    public $criteria = false;
    public $name = "core";
    public $added = false;

    protected $options = [];

    public function __construct($engine, $load = true)
    {
        $this->engine = $engine;
        if ( $engine ) {
            $this->manager = $engine->manager;
            $this->config = $this->getConfig();
            $this->name = $this->config["name"];
            //var_dump($this->config);
            if ( $load && $this->config ) {
                $this->manager->manager->appendToCollection($this->config);
                $this->added = true;
            }
        }
    }

    function setOptions($options){

        $this->options = array_merge( $this->options, $options );
    }

    public function loadPartial($name)
    {
        $partialName = strtolower(get_class($this));
        $fileName = __DIR__."/$partialName/$name.htm";
        if (file_exists($fileName)) {
            return file_get_contents($fileName);
        }
        return false;
    }

    public function onSubmit()
    {
        return false;
    }

    public function prehandleUrlParameters()
    {
        $col = $this->engineCriteria;
        $engine = $this->engine;
        //echo "HUP1 ".get_class($col)."/"." $col->query_name $col->column_value<BR>";

        $crit_value = null;
        $crit_name = $col->query_name;

        if (array_key_exists($crit_name, $_REQUEST)) {
            // Since using Select2, we find unselected list boxes still send an empty array with a single character which we dont want to include
            // as a criteria selection
            if (!(is_array($_REQUEST[$crit_name]) && $_REQUEST[$col->query_name][0] == "")) {
                $crit_value = $_REQUEST[$crit_name];
            }

        }

        return;

        if (array_key_exists("HIDDEN_" . $crit_name, $_REQUEST)) {
            $crit_value = $_REQUEST["HIDDEN_" . $crit_name];
        }

        // applying multi-column values
        if (array_key_exists("HIDDEN_" . $crit_name . "_FROMDATE", $_REQUEST)) {
            $crit_value_1 = $_REQUEST["HIDDEN_" . $crit_name . "_FROMDATE"];
            $engine->lookup_queries[$crit_name]->column_value1 = $crit_value_1;
        }

        if (array_key_exists("HIDDEN_" . $crit_name . "_TODATE", $_REQUEST)) {
            $crit_value_2 = $_REQUEST["HIDDEN_" . $crit_name . "_TODATE"];
            $engine->lookup_queries[$crit_name]->column_value2 = $crit_value_2;
        }
        // end applying multi-column values

        if (array_key_exists("EXPANDED_" . $crit_name, $_REQUEST)) {
            $crit_value = $_REQUEST["EXPANDED_" . $crit_name];
        }

        // in case of single column value, we apply it now
        if (!is_null($crit_value)) {
            $engine->lookup_queries[$crit_name]->column_value = $crit_value;

            // for groupby criteria, we need to show and hide columns accordingly
            if ($crit_name == 'showfields' || $crit_name == 'groupby') {
                foreach ($engine->columns as $q_col) {
                    //show the column if it matches a groupby value
                    if (in_array($q_col->column_name, $crit_value)) {
                        $q_col->attributes['column_display'] = "show";
                    }
                    // if it doesn't match, hide it if this is the first
                    // groupby column we are going through; otherwise
                    // leave it as it is
                    elseif (!isset($not_first_pass)) {
                        $q_col->attributes['column_display'] = "hide";
                    }
                }
                $not_first_pass = true;
            }
        }
    }

    public function handleUrlParameters() {


        $col = $this->engineCriteria;
        $engine = $this->engine;
        $execute_mode = $this->engine->execute_mode;
        $identified_criteria = false;

        //echo "Handle URL $col->query_name<BR>";

        // If an initial set of parameter values has been set then parameters are being
        // set probably from a framework. Use these for setting criteria
        if ($engine->initial_execution_parameters) {
            if (isset($engine->initial_execution_parameters[$col->query_name])) {
                $val1 = false;
                $val2 = false;
                $criteriaval = $engine->initial_execution_parameters[$col->query_name];
                if ($col->criteria_type == "DATERANGE") {
                    if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE",
                        $criteriaval,
                        $val1,
                        $val2)) {
                        trigger_error("Date default '" . $criteriaval . "' 1is not a valid date4 range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                    } else {
                        $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"] = $val1;
                        $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"] = $val2;
                        if ($sessionClass::getReporticoSessionParam('latestRequest')) {
                            $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name . "_FROMDATE", $val1, $sessionClass::reporticoNamespace(), "latestRequest");
                            $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name . "_TODATE", $val2, $sessionClass::reporticoNamespace(), "latestRequest");
                        }
                    }
                } else if ($col->criteria_type == "DATE") {
                    if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATE",
                        $criteriaval,
                        $val1,
                        $val2)) {
                        trigger_error("Date default '" . $criteriaval . "' is not a valid date. Should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                    } else {
                        $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"] = $val1;
                        $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"] = $val1;
                        $_REQUEST["MANUAL_" . $col->query_name] = $val1;
                        if ($sessionClass::getReporticoSessionParam('latestRequest')) {
                            $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name . "_FROMDATE", $val1, $sessionClass::reporticoNamespace(), "latestRequest");
                            $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name . "_TODATE", $val1, $sessionClass::reporticoNamespace(), "latestRequest");
                            $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name, $val1, $sessionClass::reporticoNamespace(), "latestRequest");
                        }
                    }
                } else {
                    $_REQUEST["MANUAL_" . $col->query_name] = $criteriaval;
                    if ($sessionClass::getReporticoSessionParam('latestRequest')) {
                        $sessionClass::setReporticoSessionParam("MANUAL_" . $col->query_name, $val1, $sessionClass::reporticoNamespace(), "latestRequest");
                    }
                }
            }
        }

        // Fetch the criteria value summary if required for displaying
        // the criteria entry summary at top of report
        if ($execute_mode && $execute_mode != "MAINTAIN" && $engine->target_show_criteria &&
            ((array_key_exists($col->query_name, $_REQUEST) && !(is_array($_REQUEST[$col->query_name]) && $_REQUEST[$col->query_name][0] == ""))
                || array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)
                || array_key_exists("HIDDEN_" . $col->query_name, $_REQUEST)
            )) {
            $lq = &$engine->lookup_queries[$col->query_name];
            if ($lq->criteria_type == "LOOKUP") {
                $lq->executeCriteriaLookup();
            }

            $lq->criteriaSummaryDisplay();
            $identified_criteria = true;
        }

        if (array_key_exists($col->query_name, $_REQUEST)) {
            // Since using Select2, we find unselected list boxes still send an empty array with a single character which we dont want to include
            // as a criteria selection
            if (!(is_array($_REQUEST[$col->query_name]) && $_REQUEST[$col->query_name][0] == "")) {
                $engine->lookup_queries[$col->query_name]->column_value =
                    $_REQUEST[$col->query_name];
            }

        }

        if (array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)) {

            if (array_key_exists("MANUAL_derived_" . $col->query_name, $_REQUEST)) {
                $engine->lookup_queries[$col->query_name]->column_value_derived =
                    $_REQUEST["MANUAL_derived_" . $col->query_name];
            }

            $engine->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["MANUAL_" . $col->query_name];

            //if ( isset($col->widget->options) ) {
                //echo "cv ".get_class($this)." ".$engine->lookup_queries[$col->query_name]->column_value;
                //echo "cv ".get_class($col->widget)." ".$engine->lookup_queries[$col->query_name]->column_value;
            //}


            $lq = &$engine->lookup_queries[$col->query_name];
            if ($lq->criteria_type == "LOOKUP" && $_REQUEST["MANUAL_" . $col->query_name]) {
                if (array_key_exists("MANUAL_" . $col->query_name, $_REQUEST)) {
                    foreach ($lq->lookup_query->columns as $k => $col1) {
                        if ($col1->lookup_display_flag) {
                            $lab = &$lq->lookup_query->columns[$k];
                        }

                        if ($col1->lookup_return_flag) {
                            $ret = &$lq->lookup_query->columns[$k];
                        }

                        if ($col1->lookup_abbrev_flag) {
                            $abb = &$lq->lookup_query->columns[$k];
                        }

                    }
                }

                if ($abb && $ret && $abb->query_name != $ret->query_name) {
                    if (!$identified_criteria) {
                       $lq->executeCriteriaLookup();
                    }

                    $res = &$lq->lookup_query->targets[0]->results;
                    $choices = $lq->column_value;
                    if (!is_array($choices)) {
                        $choices = explode(',', $choices);
                    }

                    $lq->column_value;
                    $choices = array_unique($choices);
                    $target_choices = array();
                    foreach ($choices as $k => $v) {
                        if (isset($res[$abb->query_name])) {
                            foreach ($res[$abb->query_name] as $k1 => $v1) {
                                if ($v1 == $v) {
                                    //echo "$v1 / $v<br>";
                                    $target_choices[] = $res[$ret->query_name][$k1];
                                    //echo "$k -> ".$choices[$k]."<BR>";
                                }
                            }
                        }

                    }
                    $choices = $target_choices;
                    $lq->column_value = implode(",", $choices);

                    if (!$choices) {
                        // Need to set the column value to a arbitrary value when no data found
                        // matching users MANUAL entry .. if left blank then would not bother
                        // creating where clause entry
                        $lq->column_value = "(NOTFOUND)";
                    }
                    $_REQUEST["HIDDEN_" . $col->query_name] = $choices;
                } else {
                    if (!is_array($_REQUEST["MANUAL_" . $col->query_name])) {
                        $_REQUEST["HIDDEN_" . $col->query_name] = explode(",", $_REQUEST["MANUAL_" . $col->query_name]);
                    } else {
                        $_REQUEST["HIDDEN_" . $col->query_name] = $_REQUEST["MANUAL_" . $col->query_name];
                    }

                }
            }
        }

        /*
        if (array_key_exists($col->query_name . "_FROMDATE_DAY", $_REQUEST)) {
            $engine->lookup_queries[$col->query_name]->column_value =
                $engine->lookup_queries[$col->query_name]->collateRequestDate(
                    $col->query_name, "FROMDATE",
                    $engine->lookup_queries[$col->query_name]->column_value,
                    ReporticoApp::getConfig("prep_dateformat"));
        }

        if (array_key_exists($col->query_name . "_TODATE_DAY", $_REQUEST)) {
            $engine->lookup_queries[$col->query_name]->column_value2 =
                $engine->lookup_queries[$col->query_name]->collateRequestDate(
                    $col->query_name, "TODATE",
                    $engine->lookup_queries[$col->query_name]->column_value2,
                    ReporticoApp::getConfig("prep_dateformat"));
        }
        */

        if (array_key_exists("MANUAL_" . $col->query_name . "_FROMDATE", $_REQUEST)) {
            $engine->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["MANUAL_" . $col->query_name . "_FROMDATE"];

        }

        if (array_key_exists("MANUAL_" . $col->query_name . "_TODATE", $_REQUEST)) {
            $engine->lookup_queries[$col->query_name]->column_value2 =
                $_REQUEST["MANUAL_" . $col->query_name . "_TODATE"];
        }

        if (array_key_exists("EXPANDED_" . $col->query_name, $_REQUEST)) {
            $engine->lookup_queries[$col->query_name]->column_value =
                $_REQUEST["EXPANDED_" . $col->query_name];
        }


    }


    //public abstract function getConfig();

}
// -----------------------------------------------------------------------------


