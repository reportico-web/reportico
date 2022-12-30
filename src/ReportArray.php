<?php
/*

 * File:        ReportArray.php
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

class ReportArray extends Report
{
    public $record_template;
    public $column_spacing;
    public $results = array();

    public function __construct()
    {
        $this->page_width = 595;
        $this->page_height = 842;
        $this->column_spacing = "2%";
    }

    public function start($engine = false)
    {

        Report::start();

        $results = array();

        $ct = 0;
    }

    public function finish()
    {
        Report::finish();

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

        // Set the values for the fields in the record
        $record = array();

        foreach ($this->query->display_order_set["column"] as $col) {
            $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->columns);
            $this->results[$qn->query_name][] = $qn->column_value;
            $ct = count($this->results[$qn->query_name]);
        }

    }

}
