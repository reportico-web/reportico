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
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;
use \Reportico\Engine\ReporticoUtility;

class DateRangePicker extends Widget
{
    public $rawvalue = false;
    public $value = false;
    public $range_start_raw = false;
    public $range_end_raw = false;
    public $range_start = false;
    public $range_end = false;
    public $range_raw = false;
    public $derived = false;
    public $range_name = false;

    public $options = [
           "Today" => [
               "widgetEvaluate" => "[moment(), moment()]",
               "phpEvaluate" => [ "today", "today" ],
            ],
            "Yesterday" => [
                "widgetEvaluate" => "[ moment().subtract(1, 'days'), moment().subtract(1, 'days')]",
                "phpEvaluate" => [ "yesterday", "yesterday" ]
            ],
           "Last 7 Days" => [
                "widgetEvaluate" => "[moment().subtract(6, 'days'), moment()]",
                "phpEvaluate" => [ "-6 day", "today" ]
            ],
           "Last 30 Days" => [
                "widgetEvaluate" => "[moment().subtract(29, 'days'), moment()]",
                "phpEvaluate" => [ "-30 day", "today" ]
            ],
           "This Month" => [
                "widgetEvaluate" => "[moment().startOf('month'), moment().endOf('month')]",
                "phpEvaluate" => [ "first day of this month", "last day of this month" ]
            ],
           "Last Month" => [
                "widgetEvaluate" => "[moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]",
                "phpEvaluate" => [ "first day of last month", "last day of last month" ]
            ],
    ];

    public function __construct($engine)
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $format = strtoupper(ReporticoApp::getConfig("prep_dateformat") ?: "");
        $format = preg_replace("/Y/i", "YYYY", $format);
        $format = preg_replace("/M/i", "MM", $format);
        $format = preg_replace("/D/i", "DD", $format);

        $rangeFunctions = "";
        foreach ($this->options as $koption => $option ) {
            if ( $rangeFunctions )
                $rangeFunctions .= ",\n";
             $rangeFunctions .= "'$koption': {$option["widgetEvaluate"]}";
        }

        return
            [
                'name' => 'daterangepicker',
                'type' => 'criteria-selection',
                'title' => 'Date Range Picker',
                'renderType' => 'TEXTFIELD',
                'sourceType' => 'DATERANGE',
                'order' => 200,
                'files' => [
                    'css' => [
                        "{$this->engine->url_path_to_assets}/node_modules/bootstrap-daterangepicker/css/daterangepicker.css",
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/node_modules/bootstrap-daterangepicker/js/moment.min.js",
                        "{$this->engine->url_path_to_assets}/node_modules/bootstrap-daterangepicker/js/daterangepicker.js",
                        //ReporticoLocale::getDatepickerLanguage(ReporticoApp::getConfig("language")). '.min.js'
                    ],
                    'events' => [
                        'init' => [
                            '
reportico_jquery(\'.reportico-daterange-field\').daterangepicker({
    timePicker: false,
                   ranges: {
                   '.$rangeFunctions.'
        } ,
    //startDate: moment().startOf(\'hour\'),
    //endDate: moment().startOf(\'hour\').add(32, \'hour\'),
    locale: {
            format: \''.$format.'\'
    }},
    function(start, end, label) {
    console.log("A new date range selection was made: " + start.format(\'YYYY-MM-DD\') + \' to \' + end.format(\'YYYY-MM-DD\'));
    
                    id = this.element.prop("id").replace(/reportico-daterange-field_/, "");
                    
                    name = this.chosenLabel;
                    if ( typeof this.ranges[ name ] === "undefined" ) {
                        name = "Custom Range";
                    }
                    
                    reportico_jquery("#reportico-daterange-label-" + id ).prop("value", name);
                    reportico_jquery("#reportico-daterange-preset-" + id ).prop("value", name);
                    
                    xx = this.element.prop("value", "sss");
                    //if ( id.match(/_FROMDATE/) )
                    //{
                        //todate = id.replace(/_FROMDATE/, "_TODATE");
                        //reportico_jquery("#" + todate).prop("value", this.value);
                    //}
  }
  );
'
                    ]
                    ]
                ],
            ];
    }

    // -----------------------------------------------------------------------------
    // Function : collateRequestDate
    // -----------------------------------------------------------------------------
    public function collateRequestDate($in_query_name, $in_tag, $in_default, $in_format)
    {
        $retval = $in_default;
        if (array_key_exists($in_query_name . "_" . $in_tag . "_DAY", $_REQUEST)) {
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


    public function getCriteriaClause($lhs = true, $operand = true, $rhs = true, $rhs1 = false, $rhs2 = false, $add_del = true)
    {

        $cls = "";

        $criteria = $this->criteria;
        $this->deriveValue();

        if ($criteria->column_value_derived) 
            $range_name = $criteria->column_value_derived;
        else
            $range_name = $criteria->column_value;

        if ($range_name) {

            // If daterange value here is a range in a single value then its been
            // run directly from command line and needs splitting up using "-"

            /*
            if ( isset($this->options[$range_name])) {
                $dateRange = $this->options[$range_name]["phpEvaluate"];
                $val1 = (new \DateTime($dateRange[0]))->format("Y-m-d");
                $val2 = (new \DateTime($dateRange[1]))->format("Y-m-d");
            } else {
                echo $criteria->column_value." xxxx<BR>";
                $val1 = ReporticoLocale::parseDate($criteria->column_value, false, ReporticoApp::getConfig("prep_dateformat"));
                $val2 = ReporticoLocale::parseDate($criteria->column_value2, false, ReporticoApp::getConfig("prep_dateformat"));
            }
            */

            //echo "GCL:".$this->range_start."-".$this->range_end."<BR>";
            $val1 = ReporticoLocale::convertYMDtoLocal($this->range_start, ReporticoApp::getConfig("prep_dateformat"), ReporticoApp::getConfig("db_dateformat"));
            $val2 = ReporticoLocale::convertYMDtoLocal($this->range_end, ReporticoApp::getConfig("prep_dateformat"), ReporticoApp::getConfig("db_dateformat"));
            //echo "GCL:".$val1."-".$val2."<BR>";

            if ($lhs) {
                if ($criteria->table_name && $criteria->column_name) {
                    $cls .= " AND " . $criteria->table_name . "." . $criteria->column_name;
                } else
                    if ($criteria->column_name) {
                        $cls .= " AND " . $criteria->column_name;
                    }

            }

            $del = "";
            if ($add_del) {
                $del = $criteria->getValueDelimiter();
            }

            if ($rhs) {
                $cls .= "BETWEEN ";
                $cls .= $del . $val1 . $del;
                $cls .= " AND ";
                $cls .= $del . $val2 . $del;
            }
            if ($rhs1) {
                $cls = $del . $val1 . $del;
            }
            if ($rhs2) {
                $cls = $del . $val2 . $del;
            }
        }

        //echo "CLAUSE: ".$cls."<BR>";

        return $cls;

    }

    public function deriveValue()
    {

        $sessionClass = \Reportico\Engine\ReporticoSession();
        $criteriaName = "XXXXXXXX";
        if ( $this->criteria ) {
            $this->value = $this->criteria->column_value;
            $criteriaName = $this->criteria->query_name;
        }

        $this->range_start = $this->range_end = "";
        $this->range_name = "";
        $this->range_start_raw = $this->range_end_raw = "";

        //echo "try ".$this->criteria->column_value."<BR>";
        //echo "derived ".$this->criteria->column_value_derived."<BR>";
        if (!array_key_exists("clearform", $_REQUEST) && array_key_exists("MANUAL_" . $criteriaName, $_REQUEST)) {

            $this->range_name = $_REQUEST["MANUAL_".$criteriaName];
            if ( isset($this->options[$this->range_name])) {
                $this->range_raw = $this->range_name;
                $dateRange = $this->options[$this->range_name]["phpEvaluate"];
                $this->range_start = (new \DateTime($dateRange[0]))->format("Y-m-d");
                $this->range_end = (new \DateTime($dateRange[1]))->format("Y-m-d");
            } else {
                $this->range_raw = $this->range_name;
                //echo "<PRE>"; var_dump($_REQUEST); echo "</PRE>";
                if (isset($_REQUEST["MANUAL_derived_".$criteriaName])){
                    //echo "try ".$this->criteria->column_value."<BR>";
                    //echo $this->criteria->column_value = $_REQUEST["MANUAL_derived_".$criteriaName];
                    //echo "use der {$this->criteria->column_value}<BR>";
                    if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE", $_REQUEST["MANUAL_derived_".$criteriaName], $this->range_start, $this->range_end)) {
                        //echo "Error!";
                    }
                }
                else if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE", $_REQUEST["MANUAL_".$criteriaName], $this->range_start, $this->range_end)) {
                    //echo "Error!";
                }
                $this->range_start_raw = $this->range_start;
                $this->range_end_raw = $this->range_end;
                //echo "DRANGE:".$this->range_start."-".$this->range_end."<BR>";
            }

        } else if (!array_key_exists("clearform", $_REQUEST) && array_key_exists("MANUAL_" . $criteriaName . "_FROMDATE", $_REQUEST)) {
            $this->range_start_raw = $this->range_start = $_REQUEST["MANUAL_" . $criteriaName . "_FROMDATE"];
            $this->range_start = $this->collateRequestDate($criteriaName, "FROMDATE", $this->range_start, ReporticoApp::getConfig("prep_dateformat"));
            //echo $this->range_start;
        } else
            if (!array_key_exists("clearform", $_REQUEST) && array_key_exists("HIDDEN_" . $criteriaName . "_FROMDATE", $_REQUEST)) {
                $this->range_start_raw = $this->range_start = $_REQUEST["HIDDEN_" . $criteriaName . "_FROMDATE"];
                $this->range_start = $this->collateRequestDate($criteriaName, "FROMDATE", $this->range_start, ReporticoApp::getConfig("prep_dateformat"));
            } else {
                // User reset form or first time in, set defaults and clear existing form info
                if (count($this->criteria->defaults) == 0) {
                    $this->criteria->defaults[0] = "TODAY-TODAY";
                }

                if ($this->criteria->defaults[0]) {
                    $this->range_name = $this->criteria->defaults[0];
                    if ( isset($this->options[$this->range_name])) {
                        $this->range_raw = $this->range_name;
                        $dateRange = $this->options[$this->range_name]["phpEvaluate"];
                        $this->range_start = (new \DateTime($dateRange[0]))->format("Y-m-d");
                        $this->range_end = (new \DateTime($dateRange[1]))->format("Y-m-d");
                    } else {
                        if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE", $this->criteria->defaults[0], $this->range_start, $this->range_end)) {
                            trigger_error("Date default '" . $this->criteria->defaults[0] . "' is not a valid date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                        }
                    }

                    unset($_REQUEST["MANUAL_" . $criteriaName . "_FROMDATE"]);
                    unset($_REQUEST["MANUAL_" . $criteriaName . "_TODATE"]);
                    unset($_REQUEST["HIDDEN_" . $criteriaName . "_FROMDATE"]);
                    unset($_REQUEST["HIDDEN_" . $criteriaName . "_TODATE"]);
                }
            }

        if (!$this->range_start) {
            $this->range_end_raw = $this->range_end = "TODAY";
        }

        //echo "NOW: $this->range_start - $this->range_end<BR>";
        $this->range_start = ReporticoLocale::parseDate($this->range_start, false, ReporticoApp::getConfig("prep_dateformat"));
        $this->range_end = ReporticoLocale::parseDate($this->range_end, false, ReporticoApp::getConfig("prep_dateformat"));

        if (array_key_exists("MANUAL_" . $criteriaName . "_TODATE", $_REQUEST)) {
            $this->range_end_raw = $this->range_end = $_REQUEST["MANUAL_" . $criteriaName . "_TODATE"];
            //echo $this->range_end;
            $this->range_end = $this->collateRequestDate($criteriaName, "TODATE", $this->range_end, ReporticoApp::getConfig("prep_dateformat"));
            //echo $this->range_end;
        } else if (array_key_exists("HIDDEN_" . $criteriaName . "_TODATE", $_REQUEST)) {
            $this->range_end_raw = $this->range_end = $_REQUEST["HIDDEN_" . $criteriaName . "_TODATE"];
            $this->range_end = $this->collateRequestDate($criteriaName, "TODATE", $this->range_end, ReporticoApp::getConfig("prep_dateformat"));
        }

        if (!$this->range_end) {
            $this->range_end_raw = $this->range_end = "TODAY";
        }

        $this->range_end = ReporticoLocale::parseDate($this->range_end, false, ReporticoApp::getConfig("prep_dateformat"));

        if ( !$this->range_raw && $this->range_start_raw && $this->range_end_raw ) {
            $this->range_raw = $this->range_start_raw ."-". $this->range_end_raw;
            //echo "got $this->range_raw";

        }
        //echo "NOW: $this->range_start - $this->range_end<BR>";
        //echo "RAW: $this->range_raw <BR><BR>";

        return ;

    }

    public function render()
    {

        $text = "";

        $name = "XXXXXXXX";
        if ( $this->criteria ) {
            $this->value = $this->criteria->column_value;
            $name = $this->criteria->query_name;
        }

        $this->deriveValue();

        $format = strtoupper(ReporticoApp::getConfig("prep_dateformat"));
        $format = preg_replace("/Y/i", "YYYY", $format);
        $format = preg_replace("/M/i", "MM", $format);
        $format = preg_replace("/D/i", "DD", $format);

        //$text .= '<input  type="hidden" name="HIDDEN_' . $name . '_FROMDATE"';
        //$text .= '<input  type="hidden" name="HIDDEN_' . $name . '_TODATE"';
        //$text .= ' size="' . ($this->criteria->column_length) . '"';
        //$text .= ' maxlength="' . $this->criteria->column_length . '"';
        //$text .= ' value="' . $this->value . '">';

        $this->value = $this->range_start." - ".$this->range_end;

        if ( $this->criteria ) {
            $text .= '<div style="position: relative">';
            $text .= '<input  type="text" class="form-control reportico-daterange-field" id="reportico-daterange-field_'.$name.'" name="MANUAL_derived_'.$name.'" value="' . $this->value . '">';
            if ( !$this->range_raw )
                $text .= '<input  type="text" class="badge badge-info p-0" readonly="readonly" style="display: inline;border:none; position: absolute; right:4px; top:4px" id="reportico-daterange-label-'.$name.'" name="MANUAL_label_'.$name.'" value="' . "Custom Range" . '">';
            else
                $text .= '<input  type="text" class="badge badge-info p-0" readonly="readonly" style="display: inline;border:none; position: absolute; right:4px; top:4px" id="reportico-daterange-label-'.$name.'" name="MANUAL_label_'.$name.'" value="' . $this->range_raw . '">';

            $text .= '</div>';
            $text .= '<input  type="text" readonly="readonly" style="display:none" id="reportico-daterange-preset-'.$name.'" name="MANUAL_'.$name.'" value="' . $this->range_raw . '">';
        }


        return $text;

    }
}
// -----------------------------------------------------------------------------
