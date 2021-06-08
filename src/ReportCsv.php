<?php
/*

 * File:        ReportCsv.php
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

class ReportCsv extends Report
{
    public $abs_top_margin;
    public $abs_bottom_margin;
    public $abs_left_margin;
    public $abs_right_margin;

    public function __construct()
    {
    }

    public function start($engine = false)
    {
        Report::start();

        $this->debug("Excel Start **");

        $this->page_line_count = 0;
    }

    public function finish()
    {
        Report::finish();

        if ($this->report_file) {
            $this->debug("Saved to $this->report_file");
        } else {
            $this->debug("No csv file specified !!!");
            $buf = "";
            $len = strlen($buf) + 1;

            if ($this->query->pdf_delivery_mode == "DOWNLOAD_SAME_WINDOW" && $this->query->reportico_ajax_called) {
                $this->text = base64_encode($this->text);
            }

            if (ob_get_length() > 0) {
                ob_clean();
            }

            header("Content-type: application/octet-stream");
            $attachfile = "reportico.csv";
            if ($this->reporttitle) {
                $attachfile = preg_replace("/ /", "_", $this->reporttitle . ".csv");
            }

            header('Content-Disposition: attachment;filename=' . $attachfile);

            header("Pragma: no-cache");
            header("Expires: 0");

            $len = strlen($this->text);
            header("Content-Length: $len");
            echo $this->text;
            die;
        }

    }

    public function formatColumnHeader(&$column_item)
    {

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $padstring = $column_item->deriveAttribute("column_title", $column_item->query_name);
        $padstring = str_replace("_", " ", $padstring);
        $padstring = ucwords(strtolower($padstring));
        $padstring = ReporticoLang::translate($padstring);

        $this->text .= '"' . $padstring . '"' . ",";
    }

    public function formatColumn(&$column_item)
    {
        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $padstring = &$column_item->column_value;
        // Dont allow HTML values in CSV output
        if (preg_match("/^<.*>/", $padstring)) {
            $padstring = "";
        }

        // Replace Line Feeds with spaces
        $specchars = array("\r\n", "\n", "\r");
        $output = str_replace($specchars, " ", $padstring);

        // Handle double quotes by changing " to ""
        $output = str_replace("\"", "\"\"", $output);
        if ( is_numeric($output) ){
            $this->text .= $output.",";
        } else {
            $this->text .= "=\"" . $output . "\",";
        }

    }

    public function eachLine($val)
    {
        // Start setting title and headers on first line
        // because we dont want to assume its csv unless we have some
        // output , so we can show an html error otherwise
        if ($this->line_count == 0) {

            $this->debug("Excel Begin Page\n");

            $this->text .= '"' . "$this->reporttitle" . '"';
            $this->text .= "\n";
        }
        Report::eachLine($val);

        // Excel requires group headers are printed as the first columns in the spreadsheet against
        // the detail.
        foreach ($this->query->groups as $name => $group) {
            if (count($group->headers) > 0) {
                foreach ($group->headers as $gphk => $col) {
                    $qn = ReporticoUtility::getQueryColumn($col["GroupHeaderColumn"]->query_name, $this->query->columns);
                    $padstring = $qn->column_value;
                    $this->text .= "\"" . $padstring . "\"";
                    $this->text .= ",";
                }
            }

        }

        //foreach ( $this->columns as $col )
        foreach ($this->query->display_order_set["column"] as $col) {
            $this->formatColumn($col);
        }
        $this->text .= "\n";

    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }

    public function beginPage()
    {
        Report::beginPage();

    }

    public function formatCriteriaSelection($label, $value)
    {
        $this->text .= "\"" . $label . "\"";
        $this->text .= ",";
        $this->text .= "\"" . $value . "\"";
        $this->text .= "\n";
    }

    public function afterFormatCriteriaSelection()
    {
        $this->text .= "\n";
    }

    public function finishPage()
    {
        $this->debug("Excel Finish Page");
        //pdf_end_page($this->document);
        //die;
    }

    public function formatHeaders()
    {
        // Excel requires group headers are printed as the first columns in the spreadsheet against
        // the detail.
        foreach ($this->query->groups as $name => $group) {
            for ($i = 0; $i < count($group->headers); $i++) {
                $col = &$group->headers[$i]["GroupHeaderColumn"];
                $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
                $tempstring = str_replace("_", " ", $col->query_name);
                $tempstring = ucwords(strtolower($tempstring));
                $this->text .= "\"" . ReporticoLang::translate($col->deriveAttribute("column_title", $tempstring)) . "\"";
                $this->text .= ",";
            }
        }

        foreach ($this->query->display_order_set["column"] as $w) {
            $this->formatColumnHeader($w);
        }

        $this->text .= "\n";
    }

    public function formatGroupHeader(&$col, $custom)
    {
        // Excel requires group headers are printed as the first columns in the spreadsheet against
        // the detail.
        return;

        $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
        $padstring = $qn->column_value;
        $tempstring = str_replace("_", " ", $col->query_name);
        $tempstring = ucwords(strtolower($tempstring));
        $this->text .= ReporticoLang::translate($col->deriveAttribute("column_title", $tempstring));
        $this->text .= ": ";
        $this->text .= "$padstring";
        $this->text .= "\n";
    }

    public function beginLine()
    {
        return;
    }

    public function formatColumnTrailer_before_line()
    {
        // Excel requires group headers are printed as the first columns in the spreadsheet against
        // the detail.
        $obj = new \ArrayObject($this->query->groups);
        $it = $obj->getIterator();
        foreach ($it as $name => $group) {
            for ($i = 0; $i < count($group->headers); $i++) {
                $this->text .= ",";
            }
        }
    }

    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false)
    {
        if ($value_col) {
            $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("group_trailer_label");
            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }
            $group_label = ReporticoLang::translate($group_label);
            $padstring = $value_col["GroupTrailerValueColumn"]->old_column_value;
            if ($group_label == "BLANK") {
                $this->text .= "\"$padstring\"";
            } else {
                $this->text .= "\"" . $group_label . ":" . $padstring . "\"";
            }

        }
        $this->text .= ",";
    }

    public function endLine()
    {
        $this->text .= "\n";
    }

    public function publish()
    {
        Report::publish();
        $this->debug("Publish Excel");
    }

}
