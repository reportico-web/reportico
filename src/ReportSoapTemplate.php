<?php
/*

 * File:        ReportSoapTemplate.php
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

class ReportSoapTemplate extends Report
{

    public $soapdata = array();
    public $soapline = array();
    public $soapresult = false;

    public function start($engine = false)
    {

        // Include NuSoap Web Service PlugIn
        //require_once("nusoap.php");

        Report::start();

        $this->reporttitle = $this->query->deriveAttribute("ReportTitle", "Set Report Title");
        $this->debug("SOAP Start **");
    }

    public function finish()
    {
        Report::finish();
        $this->debug("HTML End **");

        if ($this->line_count < 1) {
            $this->soapresult = new soap_fault('Server', 100, "No Data Returned", "No Data Returned");
        } else {
            $this->soapdata = array(
                "ReportTitle" => $this->reporttitle,
                "ReportTime" => date("Y-m-d H:I:s T"),
                $this->soapdata,
            );

            $this->soapresult =
            new soapval('reportReturn',
                'ReportDeliveryType',
                $this->soapdata,
                'http://reportico.org/xsd');
        }

    }

    public function formatColumn(&$column_item)
    {
        if ($this->body_display != "show") {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $this->soapline[$column_item->query_name] = $column_item->column_value;
    }

    public function eachLine($val)
    {
        Report::eachLine($val);

        if ($this->page_line_count == 1) {
            //$this->text .="<tr class='reportico-prepare-crit-line'>";
            //foreach ( $this->columns as $col )
            //$this->formatColumnHeader($col);
            //$this->text .="</tr>";
        }

        $this->soapline = array();
        foreach ($this->query->display_order_set["column"] as $col) {
            $this->formatColumn($col);
        }

        $this->soapdata[] = new soapval('ReportLine', 'ReportLineType', $this->soapline);
    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }

}
