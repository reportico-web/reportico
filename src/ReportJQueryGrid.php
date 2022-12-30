<?php
/*

 * File:        ReportJQueryGrid.php
 *
 * Base class for all report output formats.
 * Defines base functionality for handling report
 * page headers, footers, group headers, group trailers
 * data lines
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */
namespace Reportico\Engine;

class ReportJQueryGrid extends Report
{
    public $record_template;
    public $gridarr = array();
    public $colnames = array();
    public $colmodel = array();
    public $colmap = array();
    public $colfilters = array();
    public $results = array();
    public $line_ct = 0;
    public $report_name = "";
    public $key_column = false;
    public $dataonly = false;

    public function __construct()
    {
        $this->page_width = 595;
        $this->page_height = 842;
        $this->column_spacing = "2%";
    }

    public function start($engine = false)
    {
        Report::start();
        $title = $this->reporttitle;
        $this->results = array(
        );

        $ct = 0;
        $this->report_name = preg_replace("/\.xml$/", "", $this->query->xmlinput);
        $this->key_column = derive_jquerygrid_rep_params($this->report_name, "primary_key", false);
        $this->colnames[] = "Options";
        $this->colmodel[] = array(
            "name" => "options",
            "index" => "options",
            "jsonmap" => "Options",
            "width" => "80");
        $this->dataonly = ReporticoUtility::getRequestItem("dataonly", false);
    }

    public function finish()
    {
        Report::finish();
        if (ob_get_length() > 0) {
            ob_clean();
        }

        $retarr = array();
        $retarr["JSON"] = "success";
        $retarr["viewname"] = $this->report_name;
        $retarr["colmodel"] = $this->colmodel;
        $retarr["colnames"] = $this->colnames;

        $page = ReporticoUtility::getRequestItem("page", "");
        $numrecords = 1000000;
        if (!$page) {
            $page = 1;
        }

        $rows = ReporticoUtility::getRequestItem("rows", "");
        if (!$rows) {
            $rows = $numrecords;
        }

        $retarr["gridmodel"] = array(
            "total" => (ceil($numrecords / $rows)) . "",
            "page" => $page,
            //"rows" => 15,
            //"sidx" => null,
            //"sord" => "asc",
            //"records" => $this->line_ct * 500,
            "records" => $numrecords . "",
            "rows" => $this->results);

        if ($this->dataonly) {
            $len = strlen(json_encode($retarr["gridmodel"]));
        } else {
            $len = strlen(json_encode($retarr));
        }

        header('Cache-Control: no-cache, must-revalidate');
        //header('Content-Type: application/json');
        header('Content-Type: text/html');

        header("Content-Length: $len");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Disposition: attachment; filename=reportico.json');

        if ($this->dataonly) {
            echo json_encode($retarr["gridmodel"]);
        } else {
            echo json_encode($retarr);
        }

        die;
    }

    public function formatColumn(&$column_item)
    {
        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $k = &$column_item->column_value;
        $padstring = str_pad($k, 20);
    }

    public function eachLine($val)
    {
        Report::eachLine($val);

        //if ( $this->line_ct > 3 ) return;

        // Set the values for the fields in the record

        $this->results[] = array(
            "id" => $this->line_ct,
            "cell" => array(),
        );
        $this->results[count($this->results) - 1]["cell"][] = "options";

        // Excel requires group headers are printed as the first columns in the spreadsheet against
        // the detail.
        foreach ($this->query->groups as $name => $group) {
            if (count($group->headers) > 0) {
                foreach ($group->headers as $gphk => $col) {
                    $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
                    $coltitle = $col->deriveAttribute("column_title", $col->query_name);
                    $coltitle = str_replace("_", " ", $coltitle);
                    $coltitle = ucwords(strtolower($coltitle));
                    $coltitle = ReporticoLang::translate($coltitle);

                    if ($col->query_name == $this->key_column) {
                        $this->results[count($this->results) - 1]["id"][] = $qn->column_value;
                    }

                    $this->results[count($this->results) - 1]["cell"][] = $qn->column_value;

                    if ($this->line_ct == 0) {
                        $this->colmodel[] = array(
                            "name" => $col->query_name,
                            "index" => $col->query_name,
                            "editable" => derive_jquerygrid_col_params($this->report_name, $col->query_name, "editable", false),
                            "edittype" => "text",
                            "sorttype" => "text",
                            "jsonmap" => $col->query_name,
                            "width" => derive_jquerygrid_col_params($this->report_name, $col->query_name, "width", "80"));
                        // Map colname to col
                        $this->colmap[$col->query_name] = count($this->colmodel) - 1;
                        if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "edittype", "")) {
                            $this->colmodel[count($this->colmodel) - 1]["edittype"] = $v;
                        }

                        if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "sorttype", "")) {
                            $this->colmodel[count($this->colmodel) - 1]["sorttype"] = $v;
                        }

                        if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "editoptions", "")) {
                            $this->colmodel[count($this->colmodel) - 1]["editoptions"] = $v;
                        }

                        if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "filtertype", "")) {
                            if ($v == "select") // Generate filter options based on data in columns
                            {
                                $this->colmodel[count($this->colmodel) - 1]["stype"] = "select";
                                if (!$this->colmodel[count($this->colmodel) - 1]["editoptions"]) {
                                    $this->colmodel[count($this->colmodel) - 1]["editoptions"] = array();
                                    $this->colfilters[$col->query_name] = array();
                                    $this->colmodel[count($this->colmodel) - 1]["editoptions"]["value"] = ":All";
                                }
                            }
                        }

                        $this->colnames[] = $coltitle;
                    }

                    if ($this->colmodel[$this->colmap[$col->query_name]]["stype"] == "select") {
                        if (!array_key_exists($qn->column_value, $this->colfilters[$col->query_name])) {
                            //$this->colmodel[$this->colmap[$col->query_name]]["editoptions"][$qn->column_value] = $qn->column_value;
                            $this->colfilters[$col->query_name][$qn->column_value] = "1";
                            $this->colmodel[$this->colmap[$col->query_name]]["editoptions"]["value"] .= ";" . $qn->column_value . ":" . $qn->column_value;
                        }
                    }

                }
            }

        }

        foreach ($this->query->display_order_set["column"] as $col) {
            $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->columns);
            $coltitle = $col->deriveAttribute("column_title", $col->query_name);
            $coltitle = str_replace("_", " ", $coltitle);
            $coltitle = ucwords(strtolower($coltitle));
            $coltitle = ReporticoLang::translate($coltitle);

            if ($col->query_name == $this->key_column) {
                $this->results[count($this->results) - 1]["id"] = $qn->column_value;
            }

            $disp = $col->deriveAttribute("column_display", "show");
            if ($disp == "hide") {
                continue;
            }

            if ($this->line_ct == 0) {
                $this->colmodel[] = array(
                    "name" => $col->query_name,
                    "index" => $col->query_name,
                    "editable" => derive_jquerygrid_col_params($this->report_name, $col->query_name, "editable", false),
                    "edittype" => "text",
                    "sorttype" => "int",
                    "jsonmap" => $col->query_name,
                    "width" => derive_jquerygrid_col_params($this->report_name, $col->query_name, "width", "80"));
                // Map colname to col
                $this->colmap[$col->query_name] = count($this->colmodel) - 1;
                if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "hidden", "")) {
                    $this->colmodel[count($this->colmodel) - 1]["hidden"] = $v;
                }

                if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "edittype", "")) {
                    $this->colmodel[count($this->colmodel) - 1]["edittype"] = $v;
                }

                if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "sorttype", "")) {
                    $this->colmodel[count($this->colmodel) - 1]["sorttype"] = $v;
                }

                if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "filtertype", "")) {
                    if ($v == "select") // Generate filter options based on data in columns
                    {
                        $this->colmodel[count($this->colmodel) - 1]["stype"] = "select";
                        if (!$this->colmodel[count($this->colmodel) - 1]["editoptions"]) {
                            $this->colmodel[count($this->colmodel) - 1]["editoptions"] = array();
                            $this->colfilters[$col->query_name] = array();
                            $this->colmodel[count($this->colmodel) - 1]["editoptions"]["value"] = ":All";
                        }
                    }
                }
                if ($v = derive_jquerygrid_col_params($this->report_name, $col->query_name, "editoptions", "")) {
                    $this->colmodel[count($this->colmodel) - 1]["editoptions"] = $v;
                }

                $this->colnames[] = $coltitle;
            }

            // Add value to filter options if type is select

            if ($this->colmodel[$this->colmap[$col->query_name]]["stype"] == "select") {
                if (!array_key_exists($qn->column_value, $this->colfilters[$col->query_name])) {
                    //$this->colmodel[$this->colmap[$col->query_name]]["editoptions"][$qn->column_value] = $qn->column_value;
                    $this->colfilters[$col->query_name][$qn->column_value] = "1";
                    $this->colmodel[$this->colmap[$col->query_name]]["editoptions"]["value"] .= ";" . $qn->column_value . ":" . $qn->column_value;
                }
            }

            $this->results[count($this->results) - 1]["cell"][] = $qn->column_value;
        }
        // $this->results[] = $cells;
        $this->line_ct++;
    }
}
