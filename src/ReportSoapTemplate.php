<?php
/*
Reportico - PHP Reporting Tool
Copyright (C) 2010-2014 Peter Deed

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

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
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */
namespace Reportico;

class ReportSoapTemplate extends Report
{

    public $soapdata = array();
    public $soapline = array();
    public $soapresult = false;

    public function start()
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
            //$this->text .="<tr class='swPrpCritLine'>";
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
