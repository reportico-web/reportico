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

 * File:        reportico_report_soap_template.php
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
require_once("reportico_report.php");

class reportico_report_soap_template extends reportico_report
{

	var $soapdata = array();
	var $soapline = array();
	var $soapresult = false;

	function start ()
	{

		// Include NuSoap Web Service PlugIn
		//require_once("nusoap.php");

		reportico_report::start();

		$this->reporttitle = $this->query->derive_attribute("ReportTitle", "Set Report Title");
		$this->debug("SOAP Start **");
	}

	function finish ()
	{
		reportico_report::finish();
		$this->debug("HTML End **");

		if ( $this->line_count < 1 )
		{
			$this->soapresult = new soap_fault('Server',100,"No Data Returned","No Data Returned");
		}
		else
		{
			$this->soapdata = array(
				"ReportTitle" => $this->reporttitle,
				"ReportTime" => date("Y-m-d H:I:s T"),
				$this->soapdata
				);
				
			$this->soapresult = 
				new soapval('reportReturn',
         				'ReportDeliveryType',
         				$this->soapdata,
         				'http://reportico.org/xsd');
		}

	}

	function format_column(& $column_item)
	{
		if ( $this->body_display != "show" )
			return;

		if ( !$this->show_column_header($column_item) )
				return;

		$this->soapline[$column_item->query_name] = $column_item->column_value;
	}

	function each_line($val)
	{
		reportico_report::each_line($val);

		if ( $this->page_line_count == 1 )
		{
			//$this->text .="<tr class='swPrpCritLine'>";
			//foreach ( $this->columns as $col )
				//$this->format_column_header($col);
			//$this->text .="</tr>";
		}

		$this->soapline = array();
		foreach ( $this->query->display_order_set["column"] as $col )
				$this->format_column($col);
		$this->soapdata[] = new soapval('ReportLine', 'ReportLineType', $this->soapline);
	}

	function page_template()
	{
		$this->debug("Page Template");
	}

}

?>
