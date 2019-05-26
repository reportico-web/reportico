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

class TimeRangePicker extends Widget
{
    public $rawvalue = false;
    public $value = false;
    public $range_start_raw = false;
    public $range_end_raw = false;
    public $range_start = false;
    public $range_end = false;
    public $range_raw = false;
    public $derived = false;

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
                "phpEvaluate" => [ "-7 day", "today" ]
            ],
           "Last 30 Days" => [
                "widgetEvaluate" => "[moment().subtract(29, 'days'), moment()]",
                "phpEvaluate" => [ "-30 day", "today" ]
            ],
           "This Month" => [
                "widgetEvaluate" => "[moment().startOf('month'), moment().endOf('month')]",
                "phpEvaluate" => [ "first day of this month", "last day of last month" ]
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

        $format = strtoupper(ReporticoApp::getConfig("prep_timeformat"));
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
                'name' => 'timerangepicker',
                'type' => 'criteria-selection',
                'title' => 'Time Range Picker',
                'renderType' => 'time-range',
                'sourceType' => 'TimeRangePicker',
                'order' => 200,
                'files' => [
                    'css' => [
                        //"{$this->engine->url_path_to_assets}/node_modules/clockpicker/css/bootstrap-clockpicker.css",
                        "{$this->engine->url_path_to_assets}/node_modules/timepicker/css/jquery.timepicker.css",
                    ],
                    'js' => [
                        //"{$this->engine->url_path_to_assets}/node_modules/clockpicker/js/jquery-clockpicker.js",
                        "{$this->engine->url_path_to_assets}/node_modules/timepicker/js/jquery.timepicker.js",
                    ],
                    'events' => [
                        'init' => [
                            '
//reportico_jquery(".reportico-timerange-field").clockpicker({
                //template: false,
                //showInputs: true,
                //minuteStep: 30,
                //donetext: "Done"
  //}
  //);
reportico_jquery(".reportico-timerange-field").timepicker({
                template: false,
                "timeFormat": "H:i:s",
                showInputs: true,
                minuteStep: 60,
                donetext: "Done"
  });
  reportico_jquery(".reportico-timerange-field").on("changeTime", function() {
  
    var source = reportico_jquery(this).prop("id");
    var fromid = "#"+source;
    var toid = "#"+source;
    var final = "";
    var fromval = "";
    var toval = "";
    if ( source.match(/reportico-timerange-from/ )) {
        toid = "#"+source.replace(/reportico-timerange-from/, "reportico-timerange-to");
        fromval = reportico_jquery(fromid).val();
        toval = reportico_jquery(toid).val();
        if ( toval < fromval ) {
            toval = fromval;
            reportico_jquery(toid).val(toval);
        }
        final = "#"+source.replace(/reportico-timerange-from/, "reportico-timerange-final");
    }
    if ( source.match(/reportico-timerange-to/ )) {
        fromid = "#"+source.replace(/reportico-timerange-to/, "reportico-timerange-from");
        fromval = reportico_jquery(fromid).val();
        toval = reportico_jquery(toid).val();
        if ( toval < fromval ) {
            fromval = toval;
            reportico_jquery(fromid).val(fromval);
        }
        final = "#"+source.replace(/reportico-timerange-to/, "reportico-timerange-final");
    }
    
    finalText = fromval+"-"+toval;
    console.log(fromid);
    console.log(toid);
    reportico_jquery(final).val(finalText);
    
  });
'
                    ]
                    ]
                ],
            ];
    }

    // -----------------------------------------------------------------------------
    // Function : collateRequestTime
    // -----------------------------------------------------------------------------
    public function collateRequestTime($in_query_name, $in_tag, $in_default, $in_format)
    {
        $retval = $in_default;
        if (array_key_exists($in_query_name . "_" . $in_tag . "_DAY", $_REQUEST)) {
            if (!class_exists("TimeTime", false)) {
                ReporticoApp::handleError("This version of PHP does not have the TimeTime class. Must be PHP >= 5.3 to use time criteria");
                return $retval;
            }
            $dy = $_REQUEST[$this->query_name . "_" . $in_tag . "_DAY"];
            $mn = $_REQUEST[$this->query_name . "_" . $in_tag . "_MONTH"] + 1;
            $yr = $_REQUEST[$this->query_name . "_" . $in_tag . "_YEAR"];
            $retval = sprintf("%02d-%02d-%04d", $dy, $mn, $yr);

            $timetime = TimeTime::createFromFormat("d-m-Y", $retval);
            $in_format = ReporticoLocale::getLocaleTimeFormat($in_format);
            $retval = $timetime->format($in_format);
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

            $bits = explode("-",$range_name);
            $val1 = $bits[0];
            $val2 = $bits[1];
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
        if ( preg_match("/-/", $this->criteria->column_value)){
            $bits = explode("-", $this->criteria->column_value);
            $this->range_start = (new \DateTime($bits[0]))->format("H:i:s");
            $this->range_end = (new \DateTime($bits[1]))->format("H:i:s");
        }


        if ( !$this->range_start )
            $this->range_start = "00:00:00";
        if ( !$this->range_end )
            $this->range_end = "23:59:59";

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

        $this->value = $this->range_start."-".$this->range_end;

        if ( $this->criteria ) {
            $text .= '<div style="position: relative">';
            $text .= '<input  type="text" class="reportico-timerange-field" id="reportico-timerange-from_'.$name.'" name="IGNORE_'.$name.'" value="' . $this->range_start . '">';
            $text .= ' - ';
            $text .= '<input  type="text" class="reportico-timerange-field" id="reportico-timerange-to_'.$name.'" name="IGNORE_'.$name.'" value="' . $this->range_end . '">';
            $text .= '</div>';
            $text .= '<input  type="text" readonly="readonly" style="display:none" id="reportico-timerange-final_'.$name.'" name="MANUAL_'.$name.'" value="' . $this->value . '">';
        }


        return $text;

    }
}
// -----------------------------------------------------------------------------
