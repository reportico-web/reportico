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

use Reportico\Engine\Reportico;
use Reportico\Engine\ReporticoUtility;

class DynamicGrid extends Widget
{
    public $rawvalue = false;

    public $enabled = false;
    public $sortable = false;
    public $searchable = false;
    public $pagesize = false;

    public function __construct($engine, $load = true)
    {
        $this->options = [
            "enabled" => false,
            "sortable" => false,
            "searchable" => false,
            "paging" => false,
            "pageSize" => 10
        ];

        parent::__construct($engine, $load);
    }

    public function getConfig() {

        $enabled = $this->options["enabled"] ? "true" : "false";
        $sortable = $this->options["sortable"] ? "true" : "false";
        $searchable = $this->options["searchable"] ? "true" : "false";
        $paging = $this->options["paging"] ? "true" : "false";
        $pagesize = $this->options["pageSize"];

        $css = [ "{$this->engine->url_path_to_assets}/node_modules/datatables/css/jquery.dataTables.min.css", ];
        $js = [ "{$this->engine->url_path_to_assets}/node_modules/datatables/js/jquery.dataTables.min.js" ];
        //$css = [ ];
        //$js = [ ];
        if ( $this->engine->reportico_ajax_preloaded ) {
            $css = [];
            $js = [];
        }

        return
            [
                'name' => 'dynamicgrid',
                'group' => 'core',
                'order' => 200,
                'files' => [
                    'css' => $css,
                    'js' => $js,
                    'events' => [
                        'runtime' =>
                            [
                                "
//alert('set runtime $enabled'); 
reportico_dynamic_grids = $enabled;
reportico_dynamic_grids_sortable = $sortable;
reportico_dynamic_grids_searchable = $searchable;
reportico_dynamic_grids_paging = $paging;
reportico_dynamic_grids_page_size = $pagesize;
"
                            ],
                        'init' =>
                            [
"
//alert('set init $enabled'); 
reportico_dynamic_grids = $enabled;
reportico_dynamic_grids_sortable = $sortable;
reportico_dynamic_grids_searchable = $searchable;
reportico_dynamic_grids_paging = $paging;
reportico_dynamic_grids_page_size = $pagesize;
        if ( reportico_dynamic_grids )
        {
            reportico_jquery('.reportico-page').each(function(){
                reportico_jquery(this).dataTable(
                {
                'retrieve' : true,
                'searching' : reportico_dynamic_grids_searchable,
                'ordering' : reportico_dynamic_grids_sortable,
                'paging' : reportico_dynamic_grids_paging,
                'iDisplayLength': reportico_dynamic_grids_page_size
                }
                );
        });
        }
"
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


    public function deriveValue()
    {
        echo "PPP IGNORE!!!!";
        return;
        $criteriaName = "XXXXXXXX";
        if ( $this->criteria ) {
            $this->value = $this->criteria->column_value;
            $criteriaName = $this->criteria->query_name;
        }

        $this->range_start = $this->range_end = "";

        if (!array_key_exists("clearform", $_REQUEST) && array_key_exists("MANUAL_" . $criteriaName . "_FROMDATE", $_REQUEST)) {

            $this->range_start = $_REQUEST["MANUAL_" . $criteriaName . "_FROMDATE"];
            $this->range_start = $this->collateRequestDate($criteriaName, "FROMDATE", $this->range_start, ReporticoApp::getConfig("prep_dateformat"));
        } else
            if (!array_key_exists("clearform", $_REQUEST) && array_key_exists("HIDDEN_" . $criteriaName . "_FROMDATE", $_REQUEST)) {
                $this->range_start = $_REQUEST["HIDDEN_" . $criteriaName . "_FROMDATE"];
                $this->range_start = $this->collateRequestDate($criteriaName, "FROMDATE", $this->range_start, ReporticoApp::getConfig("prep_dateformat"));
            } else {
                // User reset form or first time in, set defaults and clear existing form info
                if (count($this->criteria->defaults) == 0) {
                    $this->criteria->defaults[0] = "TODAY-TODAY";
                }

                if ($this->criteria->defaults[0]) {
                    if (!ReporticoLocale::convertDateRangeDefaultsToDates("DATERANGE", $this->criteria->defaults[0], $this->range_start, $this->range_end)) {
                        trigger_error("Date default '" . $this->criteria->defaults[0] . "' is not a valid date range. Should be 2 values separated by '-'. Each one should be in date format (e.g. yyyy-mm-dd, dd/mm/yyyy) or a date type (TODAY, TOMMORROW etc", E_USER_ERROR);
                    }

                    unset($_REQUEST["MANUAL_" . $criteriaName . "_FROMDATE"]);
                    unset($_REQUEST["MANUAL_" . $criteriaName . "_TODATE"]);
                    unset($_REQUEST["HIDDEN_" . $criteriaName . "_FROMDATE"]);
                    unset($_REQUEST["HIDDEN_" . $criteriaName . "_TODATE"]);
                }
            }

        if (!$this->range_start) {
            $this->range_end = "TODAY";
        }

        $this->range_start = ReporticoLocale::parseDate($this->range_start, false, ReporticoApp::getConfig("prep_dateformat"));
        $this->range_end = ReporticoLocale::parseDate($this->range_end, false, ReporticoApp::getConfig("prep_dateformat"));

        if (array_key_exists("MANUAL_" . $criteriaName . "_TODATE", $_REQUEST)) {
            $this->range_end = $_REQUEST["MANUAL_" . $criteriaName . "_TODATE"];
            $this->range_end = $this->collateRequestDate($criteriaName, "TODATE", $this->range_end, ReporticoApp::getConfig("prep_dateformat"));
        } else if (array_key_exists("HIDDEN_" . $criteriaName . "_TODATE", $_REQUEST)) {
            $this->range_end = $_REQUEST["HIDDEN_" . $criteriaName . "_TODATE"];
            $this->range_end = $this->collateRequestDate($criteriaName, "TODATE", $this->range_end, ReporticoApp::getConfig("prep_dateformat"));
        }

        //if (array_key_exists("MANUAL_" . $criteriaName, $_REQUEST)) {
            //$x = $this->collateRequestDate($criteriaName, "TODATE", $this->range_end, ReporticoApp::getConfig("prep_dateformat"));
        //}

        if (!$this->range_end) {
            $this->range_end = "TODAY";
        }

        //$this->range_end = ReporticoLocale::parseDate($this->range_end, false, ReporticoApp::getConfig("prep_dateformat"));

        return ;

    }

}
// -----------------------------------------------------------------------------
