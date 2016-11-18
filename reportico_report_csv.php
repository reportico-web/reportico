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

 * File:        reportico_report_csv.php
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

class reportico_report_csv extends reportico_report
{
	var	$abs_top_margin;
	var	$abs_bottom_margin;
	var	$abs_left_margin;
	var	$abs_right_margin;
	
	function __construct ()
	{
	}

	function start ()
	{
		reportico_report::start();

		$this->debug("Excel Start **");

		$this->page_line_count = 0;
	}

	function finish ()
	{
		reportico_report::finish();

		if ( $this->report_file )
		{
			$this->debug("Saved to $this->report_file");
		}
		else
		{
			$this->debug("No csv file specified !!!");
			$buf = "";
			$len = strlen($buf) + 1;

            if ( $this->query->pdf_delivery_mode == "DOWNLOAD_SAME_WINDOW" && $this->query->reportico_ajax_called )
            {   
                $this->text = base64_encode($this->text);
            }

		    if ( ob_get_length() > 0 )
		        ob_clean();	

		    header("Content-type: application/octet-stream");
            $attachfile = "reportico.csv";
            if ( $this->reporttitle )
                $attachfile = preg_replace("/ /", "_", $this->reporttitle.".csv");
		    header('Content-Disposition: attachment;filename='.$attachfile);

		    header("Pragma: no-cache");
		    header("Expires: 0");

            $len = strlen($this->text);
            header("Content-Length: $len");
            echo $this->text;
            die;
		}

	}

	function format_column_header(& $column_item)
	{

		if ( !$this->show_column_header($column_item) )
				return;

		$padstring = $column_item->derive_attribute( "column_title",  $column_item->query_name);
		$padstring = str_replace("_", " ", $padstring);
		$padstring = ucwords(strtolower($padstring));
		$padstring = sw_translate($padstring);

		$this->text .= '"'.$padstring.'"'.",";
	}

	function format_column(& $column_item)
	{
		if ( !$this->show_column_header($column_item) )
				return;

		$padstring =& $column_item->column_value;
		// Dont allow HTML values in CSV output
		if ( preg_match ( "/^<.*>/", $padstring ) )
			$padstring = "";

        // Replace Line Feeds with spaces
        $specchars = array("\r\n", "\n", "\r");
        $output = str_replace($specchars, " ", $padstring);

        // Handle double quotes by changing " to ""
        $output = str_replace("\"", "\"\"", $output);
        $this->text .= "\"".$output."\",";

	}

	function each_line($val)
	{
		reportico_report::each_line($val);

        // Start setting title and headers on first line
        // because we dont want to assume its csv unless we have some
        // output , so we can show an html error otherwise
        if ( $this->line_count == 1 )
        {

		    $this->debug("Excel Begin Page\n");
    
		    $this->text .= '"'."$this->reporttitle".'"';
		    $this->text .= "\n";
        }

		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
                foreach ( $this->query->groups as $name => $group)
		{
			if ( count($group->headers) > 0  )
			foreach ($group->headers as $gphk => $col )
			{
				$qn = get_query_column($col["GroupHeaderColumn"]->query_name, $this->query->columns ) ;
				$padstring = $qn->column_value;
				$this->text .= "\"".$padstring."\"";
				$this->text .= ",";
			}
		}
				

		//foreach ( $this->columns as $col )
		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$this->format_column($col);
       		}
		$this->text .= "\n";

	}

	function page_template()
	{
		$this->debug("Page Template");
	}

	function begin_page()
	{
		reportico_report::begin_page();

	}

	function format_criteria_selection($label, $value)
	{
		$this->text .= "\"".$label."\"";
		$this->text .= ",";
		$this->text .= "\"".$value."\"";
		$this->text .= "\n";
	}

	function after_format_criteria_selection()
	{
		$this->text .= "\n";
	}

	function finish_page()
	{
		$this->debug("Excel Finish Page");
		//pdf_end_page($this->document);
		//die;
	}

	function format_headers()
	{
		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
        foreach ( $this->query->groups as $name => $group)
		{
			for ($i = 0; $i < count($group->headers); $i++ )
			{
				$col =& $group->headers[$i]["GroupHeaderColumn"];
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
				$tempstring = str_replace("_", " ", $col->query_name);
				$tempstring = ucwords(strtolower($tempstring));
				$this->text .= "\"".sw_translate($col->derive_attribute("column_title",  $tempstring))."\"";
				$this->text .= ",";
			}
		}
				
		foreach ( $this->query->display_order_set["column"] as $w )
			$this->format_column_header($w);
		$this->text .= "\n";
	}

	function format_group_header(&$col, $custom)
	{
		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
		return;

		$qn = get_query_column($col->query_name, $this->query->columns ) ;
		$padstring = $qn->column_value;
		$tempstring = str_replace("_", " ", $col->query_name);
		$tempstring = ucwords(strtolower($tempstring));
		$this->text .= sw_translate($col->derive_attribute("column_title",  $tempstring));
		$this->text .= ": ";
		$this->text .= "$padstring";
		$this->text .= "\n";
	}


	function begin_line()
	{
		return;
	}

	function format_column_trailer_before_line()
	{
		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
		$obj = new ArrayObject( $this->query->groups );
		$it = $obj->getIterator();
        foreach ( $it as $name => $group)
		{
			for ($i = 0; $i < count($group->headers); $i++ )
			{
				$this->text .= ",";
			}
		}
	}

	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first = false)
	{
		if ( $value_col )
		{
			$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("group_trailer_label" );
			if ( !$group_label )
				$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("column_title" );
			if ( !$group_label )
			{
				$group_label = $value_col["GroupTrailerValueColumn"]->query_name;
				$group_label = str_replace("_", " ", $group_label);
				$group_label = ucwords(strtolower($group_label));
			}
			$group_label = sw_translate($group_label);
			$padstring = $value_col["GroupTrailerValueColumn"]->old_column_value;
            if ( $group_label == "BLANK" )
			    $this->text .= "\"$padstring\"";
            else
			    $this->text .= "\"".$group_label.":".$padstring."\"";
		}
		$this->text .= ",";
	}

	function end_line()
	{
		$this->text .= "\n";
	}


	function publish()
	{
		reportico_report::publish();
		$this->debug("Publish Excel");
	}


}

?>
