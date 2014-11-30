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

 * File:        reportico_report_jquerygrid.php
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

class reportico_report_jquerygrid extends reportico_report
{
	var	$record_template;
	var	$gridarr = array();
	var	$colnames = array();
	var	$colmodel = array();
	var	$colmap = array();
	var	$colfilters = array();
	var	$results = array();
	var	$line_ct = 0;
	var	$report_name = "";
	var	$key_column = false;
	var	$dataonly = false;
	
	function __construct ()
	{
		$this->page_width = 595;
		$this->page_height = 842;
		$this->column_spacing = "2%";
	}

	function start ()
	{
		reportico_report::start();
		$title = $this->reporttitle;
		$this->results=array(
			);

		$ct=0;
		$this->report_name =  preg_replace("/\.xml$/", "", $this->query->xmlinput);
	    $this->key_column = derive_jquerygrid_rep_params ( $this->report_name, "primary_key", false );
	    $this->colnames[] = "Options";
	    $this->colmodel[] = array(
						    "name" => "options",
						    "index" => "options",
						    "jsonmap" => "Options",
							"width" => "80" );
        $this->dataonly = get_request_item("dataonly", false);
	}

	function finish ()
	{
		reportico_report::finish();
		if ( ob_get_length() > 0 )
			ob_clean();	

		$retarr = array ();
		$retarr["JSON"] = "success";
		$retarr["viewname"] = $this->report_name;
		$retarr["colmodel"] = $this->colmodel;
		$retarr["colnames"] = $this->colnames;

        $page = get_request_item("page", "");
        $numrecords = 1000000;
        if ( !$page ) 
            $page = 1;
        $rows = get_request_item("rows", "");
        if ( !$rows ) 
            $rows = $numrecords;
            

		$retarr["gridmodel"] = array (
			"total" => (ceil($numrecords / $rows))."",
			"page" => $page,
			//"rows" => 15,
			//"sidx" => null,
			//"sord" => "asc",
			//"records" => $this->line_ct * 500,
			"records" => $numrecords."",
			"rows" => $this->results );
		
        if ( $this->dataonly )
		    $len = strlen(json_encode($retarr["gridmodel"]));
        else
		    $len = strlen(json_encode($retarr));
		header('Cache-Control: no-cache, must-revalidate');
		//header('Content-Type: application/json');
		header('Content-Type: text/html');

		header("Content-Length: $len");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Disposition: attachment; filename=reportico.json');


        if ( $this->dataonly )
		    echo json_encode($retarr["gridmodel"]);
        else
		    echo json_encode($retarr);
		die;
	}

	function format_column(& $column_item)
	{
		if ( !$this->show_column_header($column_item) )
				return;

		$k =& $column_item->column_value;
		$padstring = str_pad($k,20);
	}

	function each_line($val)
	{
		reportico_report::each_line($val);

		//if ( $this->line_ct > 3 ) return;

		// Set the values for the fields in the record

        $this->results[] = array (
                    "id" => $this->line_ct,
                    "cell" => array()
                    );
        $this->results[count($this->results)-1]["cell"][] = "options";

		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
        foreach ( $this->query->groups as $name => $group)
		{
			if ( count($group->headers) > 0  )
			foreach ($group->headers as $gphk => $col )
			{
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
			    $coltitle = $col->derive_attribute( "column_title",  $col->query_name);
			    $coltitle = str_replace("_", " ", $coltitle);
			    $coltitle = ucwords(strtolower($coltitle));
			    $coltitle = sw_translate($coltitle);

                if ( $col->query_name == $this->key_column )
		            $this->results[count($this->results)-1]["id"][] = $qn->column_value;

		        $this->results[count($this->results)-1]["cell"][] = $qn->column_value;

			    if ( $this->line_ct == 0 )
			    {
				    $this->colmodel[] = array(
						    "name" => $col->query_name,
						    "index" => $col->query_name,
						    "editable" => derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "editable", false ),
						    "edittype" => "text",
						    "sorttype" => "text",
						    "jsonmap" => $col->query_name,
							"width" => derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "width", "80" ));
                    // Map colname to col
                    $this->colmap[$col->query_name] = count($this->colmodel) - 1;
					if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "edittype", "" ) )
						$this->colmodel[count($this->colmodel)-1]["edittype"] = $v;
					if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "sorttype", "" ) )
						$this->colmodel[count($this->colmodel)-1]["sorttype"] = $v;
					if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "editoptions", "" ) )
						$this->colmodel[count($this->colmodel)-1]["editoptions"] = $v;

				    if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "filtertype", "" ) )
                    {
                        if ( $v == "select" ) // Generate filter options based on data in columns
                        {
					        $this->colmodel[count($this->colmodel)-1]["stype"] = "select";
                            if ( ! $this->colmodel[count($this->colmodel)-1]["editoptions"]  )
                            {
                                $this->colmodel[count($this->colmodel)-1]["editoptions"] = array();
                                $this->colfilters[$col->query_name] = array();
                                $this->colmodel[count($this->colmodel)-1]["editoptions"]["value"] = ":All";
                            }
                        }
                    }

				    $this->colnames[] = $coltitle;
			    }

                if ( $this->colmodel[$this->colmap[$col->query_name]]["stype"] == "select" )
                {
                    if ( !array_key_exists( $qn->column_value, $this->colfilters[$col->query_name] ) )
                    {
                        //$this->colmodel[$this->colmap[$col->query_name]]["editoptions"][$qn->column_value] = $qn->column_value;
                        $this->colfilters[$col->query_name][$qn->column_value] = "1";
                        $this->colmodel[$this->colmap[$col->query_name]]["editoptions"]["value"] .= ";".$qn->column_value.":".$qn->column_value;
                    }
                }

						
			}
		}
				
		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$qn = get_query_column($col->query_name, $this->columns ) ;
			$coltitle = $col->derive_attribute( "column_title",  $col->query_name);
			$coltitle = str_replace("_", " ", $coltitle);
			$coltitle = ucwords(strtolower($coltitle));
			$coltitle = sw_translate($coltitle);

            if ( $col->query_name == $this->key_column )
            {
		        $this->results[count($this->results)-1]["id"] = $qn->column_value;
            }

		    $disp = $col->derive_attribute( "column_display",  "show" );
		    if ( $disp == "hide" ) continue;

			if ( $this->line_ct == 0 )
			{
				$this->colmodel[] = array(
						"name" => $col->query_name,
						"index" => $col->query_name,
						"editable" => derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "editable", false ),
						"edittype" => "text",
						"sorttype" => "int",
						"jsonmap" => $col->query_name,
						"width" => derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "width", "80" ));
                // Map colname to col
                $this->colmap[$col->query_name] = count($this->colmodel) - 1;
				if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "hidden", "" ) )
					$this->colmodel[count($this->colmodel)-1]["hidden"] = $v;
				if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "edittype", "" ) )
					$this->colmodel[count($this->colmodel)-1]["edittype"] = $v;
				if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "sorttype", "" ) )
					$this->colmodel[count($this->colmodel)-1]["sorttype"] = $v;
				if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "filtertype", "" ) )
                {
                    if ( $v == "select" ) // Generate filter options based on data in columns
                    {
					    $this->colmodel[count($this->colmodel)-1]["stype"] = "select";
                        if ( ! $this->colmodel[count($this->colmodel)-1]["editoptions"]  )
                        {
                           $this->colmodel[count($this->colmodel)-1]["editoptions"] = array();
                           $this->colfilters[$col->query_name] = array();
                           $this->colmodel[count($this->colmodel)-1]["editoptions"]["value"] = ":All";
                        }
                    }
                }
				if ( $v = derive_jquerygrid_col_params ( $this->report_name, $col->query_name, "editoptions", "" ) )
					$this->colmodel[count($this->colmodel)-1]["editoptions"] = $v;
				$this->colnames[] = $coltitle;
			}
						
			
            // Add value to filter options if type is select

            if ( $this->colmodel[$this->colmap[$col->query_name]]["stype"] == "select" )
            {
                if ( !array_key_exists( $qn->column_value, $this->colfilters[$col->query_name] ) )
                {
                    //$this->colmodel[$this->colmap[$col->query_name]]["editoptions"][$qn->column_value] = $qn->column_value;
                    $this->colfilters[$col->query_name][$qn->column_value] = "1";
                    $this->colmodel[$this->colmap[$col->query_name]]["editoptions"]["value"] .= ";".$qn->column_value.":".$qn->column_value;
                }
            }

		    $this->results[count($this->results)-1]["cell"][] = $qn->column_value;
   		}
		// $this->results[] = $cells;
		$this->line_ct++;
	}
}

?>
