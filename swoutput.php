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

 * File:        swoutput.php
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

class reportico_report extends reportico_object
{
	var	$query_set = array();
	var	$document;
	var	$report_file = "";
	var	$page_width;
	var	$page_height;
	var	$page_length = 65;
	var	$page_count = 0;
	var	$page_line_count = 0;
	var	$line_count = 0;
	var	$page_number;
	var	$columns;
	var	$last_line = false;
	var	$query;
	var	$reporttitle;
	var	$reportfilename;
	var	$body_display = "show";
	var	$graph_display = "show";
	var	$page_started = false;
	var	$text = "";

	var $attributes = array (
		"TopMargin" => "4%",
		"BottomMargin" => "2%",
		"RightMargin" => "5%",
		"LeftMargin" => "5%",
		"BodyStart" => "10%",
		"BodyEnd" => "10%",
		"ReportTitle" => "Set Report Title"
		);


	function __construct()
	{
		reportico_object::reportico_object();

		$this->formats = array(
		"body_style" => "blankline",
		"after_header" => "blankline",
		"before_trailer" => "blankline",
		"after_trailer" => "blankline"
			);
	}

	function reportico_string_to_php($in_string)
	{
		// first change '(colval)' parameters
		$out_string = $in_string;

		if ( preg_match_all( "/{([^}]*)/", $out_string, $matches ) )
		{
			foreach ( $matches[1] as $match )
			{
				$first = substr($match, 0, 1);
				if ( $first == "=" )
				{
					$crit = substr ( $match, 1 );
					$out_string = preg_replace("/\{$match\}/", 
							$this->query->lookup_queries[$crit]->
										get_criteria_clause(false,false,true),
										$out_string);
				}
				if ( preg_match("/^session_/", $match ) )
				{
					$crit = substr ( $match, 8 );
					$out_string = preg_replace("/\{$match\}/", 
							get_reportico_session_param($crit), $out_string);
				}
			}
			
		}


		if ( preg_match("/date\((.*)\)/", $out_string, $match) )	
		{
			$dt = preg_replace("/[\"']/", "", date($match[1]));
			$out_string = preg_replace("/date\(.*\)/i", "$dt", $out_string);
		}

		$out_string = preg_replace('/date("\(.*\)")/', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/pageno\(\)/', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/page\(\)/', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/{page}/', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/{#page}/', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/report_*title\(\)/i', $this->reporttitle, 
			$out_string);

		$out_string = preg_replace('/{report_*title}/i', $this->reporttitle, 
			$out_string);

		$out_string = preg_replace('/{title}/', $this->reporttitle, 
			$out_string);

		return($out_string);
	}

	function set_query (&$query)
	{
		$this->query =& $query;
		$this->columns =& $query->columns;
	}

	function set_columns (&$columns)
	{
		$this->columns =& $columns;
	}

	function start ()
	{
		$this->body_display = $this->query->derive_attribute( "bodyDisplay",  "show" );
		if ( get_request_item("hide_output_text") )
            $this->body_display = false;
		$this->graph_display = $this->query->derive_attribute( "graphDisplay",  "show" );
		if ( get_request_item("hide_output_graph") )
            $this->graph_display = false;
		$this->page_line_count = 0;
		$this->line_count = 0;
		$this->page_count = 0;
		$this->debug("Base Start **");
		$this->reporttitle = $this->query->derive_attribute("ReportTitle", "Set Report Title");
		$this->reportfilename = $this->reporttitle;
		$pos = 5;
	}


	function finish ()
	{
		$this->last_line = true;
		$this->debug("Base finish");
		if ( get_reportico_session_param("target_show_group_trailers") )
		    $this->after_group_trailers();
		if ( $this->page_count > 0 )
			$this->finish_page();


	}

	function begin_page()
	{
		$this->debug("Base New Page");
		$this->page_count ++;
		$this->page_line_count = 0;

	}

    // For each line reset styles to default values
    function set_default_styles()
    {
        $this->query->output_allcell_styles = false;
        $this->query->output_row_styles = false;
        $this->query->output_before_form_row_styles = false;
        $this->query->output_after_form_row_styles = false;
        $this->query->output_page_styles = false;
        $this->query->output_header_styles = false;
        $this->query->output_reportbody_styles = false;
        $this->query->output_group_header_label_styles = false;
        $this->query->output_group_header_value_styles = false;
        $this->query->output_group_trailer_styles = false;
        $this->query->output_hyperlinks = false;
        $this->query->output_images = false;
    }

	function before_format_criteria_selection()
	{
	}

	function format_criteria_selection_set()
	{
		if ( get_reportico_session_param("target_show_criteria") )
		{
			$this->before_format_criteria_selection();
			foreach ( $this->query->lookup_queries as $name => $crit)
			{
				$label = "";
				$value = "";

                if ( isset($crit->criteria_summary) && $crit->criteria_summary )
                {
					$label = $crit->derive_attribute("column_title", $crit->query_name);
                    $value = $crit->criteria_summary;
                }
                else
                {
				if ( get_request_item($name."_FROMDATE_DAY", "" ) )
				{
					$label = $crit->derive_attribute("column_title", $crit->query_name);
					$label = sw_translate($label);
					$mth = get_request_item($name."_FROMDATE_MONTH","") + 1;
					$value = get_request_item($name."_FROMDATE_DAY","")."/".
					$mth."/".
					get_request_item($name."_FROMDATE_YEAR","");
					if ( get_request_item($name."_TODATE_DAY", "" ) )
					{
						$mth = get_request_item($name."_TODATE_MONTH","") + 1;
						$value .= "-";
						$value .= get_request_item($name."_TODATE_DAY","")."/".
						$mth."/".
						get_request_item($name."_TODATE_YEAR","");
					}
				}
				else if ( get_request_item("MANUAL_".$name."_FROMDATE", "" ) )
				{
					$label = $crit->derive_attribute("column_title", $crit->query_name);
					$label = sw_translate($label);
					$value = get_request_item("MANUAL_".$name."_FROMDATE","");
					if ( get_request_item("MANUAL_".$name."_TODATE", "" ) )
					{
						$value .= "-";
						$value .= get_request_item("MANUAL_".$name."_TODATE");
					}
		
				}
				else if ( get_request_item("HIDDEN_".$name."_FROMDATE", "" ) )
				{
					$label = $crit->derive_attribute("column_title", $crit->query_name);
					$label = sw_translate($label);
					$value = get_request_item("HIDDEN_".$name."_FROMDATE","");
					if ( get_request_item("HIDDEN_".$name."_TODATE", "" ) )
					{
						$value .= "-";
						$value .= get_request_item("HIDDEN_".$name."_TODATE");
					}
		
				}
				else if ( get_request_item("EXPANDED_".$name, "" ) )
				{
					$label = $crit->derive_attribute("column_title", $crit->query_name);
					$label = sw_translate($label);
					$value .= implode(get_request_item("EXPANDED_".$name, ""),",");
				}
				else if ( get_request_item("MANUAL_".$name, "" ) )
				{
					$label = $crit->derive_attribute("column_title", $crit->query_name);
					$label = sw_translate($label);
					$value .= get_request_item("MANUAL_".$name, "");
		
				}
			}
			if ( $label || $value )
				$this->format_criteria_selection($label, $value);
            }
			$this->after_format_criteria_selection();
		}
	}

	function after_format_criteria_selection()
	{
	}

	function page_headers()
	{
		$this->format_page_header_start();
		foreach($this->query->page_headers as $ph)
		{
				$this->format_page_header($ph);
		}
		$this->format_page_header_end();
	}

	function page_footers()
	{
		$this->format_page_footer_start();
		foreach($this->query->page_footers as $ph)
		{
				$this->format_page_footer($ph);
		}
		$this->format_page_footer_end();
	}

	function finish_page()
	{
		$this->debug("Base Finish Page");
	}

	function new_line()
	{
		$this->debug(" Base New Page");
	}

	function format_format($column_item, $format)
	{
		return;
	}

	function format_page_header(&$header)
	{
		return;
	}

	function format_page_footer(&$header)
	{
		return;
	}

	function format_page_header_start()
	{
		return;
	}

	function format_page_header_end()
	{
		return;
	}

	function format_page_footer_start()
	{
		return;
	}

	function format_page_footer_end()
	{
		return;
	}


	function format_column(& $column_item)
	{
		$this->debug(" Base Format Column");
	}

	function new_column_header()
	{
		$this->debug("Base New Page");
	}

	function new_column()
	{
		$this->debug("New Column");
	}

	function show_column_header(& $column_item)
	{
		$this->debug("Show Column Header");

		if ( !is_object($column_item) )
			return(false);

		$disp = $column_item->derive_attribute(
			"column_display",  "show" );

		if ( $disp == "hide" )
			return false;

		return true;
	}


	function publish()
	{
		$this->debug("Base Publish");
	}

	function begin_line()
	{
		return;
	}

	function end_line()
	{
		return;
	}

	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first = false)
	{
	}
	
	function format_column_trailer_before_line()
	{
	}

	function check_graphic_fit()
	{
		return true;
	}

	function each_line($val)
	{
		if ( $this->page_count == 0 )
		{
			$this->begin_page();

			// Print Criteria Items at top of report
			$this->format_criteria_selection_set();
			//$this->page_headers();
		}
		$this->debug("Base Each Line");


		$this->debug("Base Each Line");

		if ( get_reportico_session_param("target_show_group_trailers") )
		    $this->after_group_trailers();
		    $this->before_group_headers();

		$this->page_line_count++;
		$this->line_count++;

		// Add relevant values to any graphs
		foreach ( $this->query->graphs as $k => $v )
		{
			$gr =& $this->query->graphs[$k];
			if ( !$gr ) continue;
			foreach ( $gr->plot as $k1 => $v1 )
			{
				$pl =& $gr->plot[$k1];
				$col = get_query_column($pl["name"], $this->query->columns ) ;
                $gr->add_plot_value($pl["name"], $k1, $col->column_value);
			}
			if ( $gr->xlabel_column )
			{
				$col1 = get_query_column($gr->xlabel_column, $this->query->columns ) ;
    			$gr->add_xlabel( $col1->column_value);
			}
		}


		$this->debug("Line: ".$this->page_line_count."/".$this->line_count);
	}

	function after_group_trailers()
	{
        // Dont apply trailers in FORM style
        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

		$trailer_first = true;
        $group_changed = false;
		if ( $this->line_count <= 0 )
		{
			// No group trailers as it's the first page
		}
		else
		{
			//Plot After Group Trailers
			if ( count($this->query->groups) == 0 )
				return;

			end($this->query->groups);
			do
			{
				$group = current($this->query->groups);
				if ( $this->query->changed($group->group_name) || $this->last_line) 
				{
                    $group_changed = true;
					$lev = 0;
					$tolev = 0;
					while ( $lev <= $tolev )
					{
						if ( $lev == 0 )
							$this->apply_format($group, "before_trailer");

						$this->format_group_trailer_start($trailer_first);
						$this->format_column_trailer_before_line();

						$junk = 0;
						$wc = count($this->columns);
                    
                        // In PDF mode all trailer lines must be passed through twice
                        // to allow calculation of line height. Otherwise
                        // Only one pass through
                        for ( $passno = 1; $passno <= 2; $passno++ )
                        {
                            if (  get_class($this) == "reportico_report_pdf" )
                            {
                                if ( $passno == 1 ) $this->draw_mode = "CALCULATE";
                                if ( $passno == 2 ) 
                                {
                                    $this->draw_mode = "DRAW";
                                    $this->unapply_style_tags($this->query->output_group_trailer_styles);
                                    $this->check_page_overflow();
                                    $this->apply_style_tags($this->query->output_group_trailer_styles);
                                }
                            }
                            else
                            {
                                if ( $passno == 2 ) break;
                            }
                            foreach ( $this->query->display_order_set["column"] as $w )
                            {
                                if ( !$this->show_column_header($w) )
                                        continue;

                                if ( array_key_exists($w->query_name, $group->trailers) )
                                {
                                    if ( count($group->trailers[$w->query_name]) >= $lev + 1 )
                                    {
                                        $colgrp =& $group->trailers[$w->query_name][$lev];
                                        $this->format_column_trailer($w, $colgrp,$trailer_first);
                                    }
                                    else
                                        $this->format_column_trailer($w, $junk,$trailer_first);	
                                    
                                    if (  $group->max_level > $tolev )
                                    {
                                        $tolev =  $group->max_level;
                                    }

                                }
                                else
                                {
                                    $this->format_column_trailer($w, $junk, $trailer_first);	
                                }
                            } // foreach
                        }
                        if (  get_class($this) != "reportico_report_html_template" )
						    $this->format_group_trailer_end();
						if ( $trailer_first )
							$trailer_first = false;
						$lev++;
						$this->end_line();
					} // while
				}

			}
			while( prev($this->query->groups) );

            if ( $group_changed && get_class($this) == "reportico_report_html_template" )
            {
                $this->format_group_trailer_end();
            }

			// Plot After Group Graphs
            $graph_ct = 0;
			end($this->query->groups);
			do
			{
				$group = current($this->query->groups);

				if ( $this->query->changed($group->group_name) || $this->last_line) 
				{
					if ( !function_exists( "imagecreatefromstring" ) )
						trigger_error("Function imagecreatefromstring does not exist - ensure PHP is installed with GD option" );
					if ( function_exists( "imagecreatefromstring" ) &&
				       			$this->graph_display && 
							//get_checkbox_value("target_show_graph"))
	                        get_reportico_session_param("target_show_graph") )
					if ( $graphs =& $this->query->get_graph_by_name($group->group_name) )
					{
                        foreach ( $graphs as $graph )
                        {
		                    $graph->width_pdf_actual = check_for_default("GraphWidthPDF", $graph->width_pdf);
		                    $graph->height_pdf_actual = check_for_default("GraphHeightPDF", $graph->height_pdf);
		                    $graph->title_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->title, true);
		                    $graph->xtitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->xtitle, true);
		                    $graph->ytitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->ytitle, true);
						    if ( $url_string = $graph->generate_url_params($this->query->target_format) )
						    {
								    $this->plot_graph($graph, $graph_ct);
                                    $graph_ct++;
						    }
                        }
					}
				}
			}
			while( prev($this->query->groups) );
		}
	}

	function plot_graph(&$graph, $graph_ct = false)
	{
	}

	function apply_format($item, $format)
	{
		$formatval = $item->get_format($format);
		$this->format_format($formatval, $format);
	}

	function format_group_trailer_start($first = false)
	{
			return;
	}

	function format_group_trailer_end()
	{
			return;
	}

	function format_group_header_start()
	{
			
			return;
	}

	function format_group_header_end()
	{
			return;
	}

	function before_group_headers()
	{
        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

		$changect = 0;
		reset($this->query->groups);
		foreach ( $this->query->groups as $name => $group) 
		{
			if ( count($group->headers) > 0 && ( (  $group->group_name == "REPORT_BODY" && $this->line_count == 0 ) || $this->query->changed($group->group_name) )) 
			{
				if ( $changect == 0 && $this->page_line_count > 0)
				{
					$changect++;
					$this->apply_format($group, "before_header");
					$this->format_group_header_start($group->get_format("before_header") == "newpage");
				}
				else if ( $changect == 0 || 1)
				{
					$this->format_group_header_start($this->page_line_count > 0 && $group->get_format("before_header") == "newpage");
				}

 
	            if ( get_reportico_session_param("target_show_group_headers") )
				    for ($i = 0; $i < count($group->headers); $i++ )
				    {
					    $col =& $group->headers[$i];
					    $this->format_group_header($col);
				    }
				if ( $graphs =& $this->query->get_graph_by_name($group->group_name) )
				{
                    foreach ( $graphs as $graph )
                    {
				        $graph->clear_data();
				    }
                }

				$this->format_group_header_end();
				$this->apply_format($group, "after_header");
			}
            else if ( (  $group->group_name == "REPORT_BODY" && $this->line_count == 0 ) || $this->query->changed($group->group_name) )
            {
				    if ( $graphs =& $this->query->get_graph_by_name($group->group_name) )
				    {
                        foreach ( $graphs as $graph )
                        {
				            $graph->clear_data();
				    }
                }
            }
		}
		
        // Show column headers for HTML/CSV on group change, or on first line of report, or on new page
		if ( ( !$this->page_started && ( $this->query->target_format == "HTML" || $this->query->target_format == "HTMLPRINT" ) ) || 
                ( $this->query->target_format != "CSV" && $changect > 0 ) || 
                $this->page_line_count == 0 )
		{	
		    $this->format_headers();
		}
	}

	function format_group_header(&$col)
	{
		return;
	}

	function format_headers()
	{
			return;
	}



}

/**
 * Class reportico_report_array
 *
 * Allows a reportico data query to send its output to an
 * array. generally used internally for storing data
 * from user criteria selection lists.
 */
class reportico_report_array extends reportico_report
{
	var	$record_template;
	var	$column_spacing;
	var	$results = array();
	
	function __construct ()
	{
		$this->page_width = 595;
		$this->page_height = 842;
		$this->column_spacing = "2%";
	}

	function start ()
	{

		reportico_report::start();

		$results=array();

		$ct=0;
	}

	function finish ()
	{
		reportico_report::finish();

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

		// Set the values for the fields in the record
		$record = array();

		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$qn = get_query_column($col->query_name, $this->columns ) ;
			$this->results[$qn->query_name][] = $qn->column_value;
			$ct = count($this->results[$qn->query_name]);
       	}
		
	}

}


// -----------------------------------------------------------------------------
// Class reportico_report_pdf
// -----------------------------------------------------------------------------
class reportico_report_pdf extends reportico_report
{
	var	$abs_top_margin;
	var	$abs_bottom_margin;
	var	$abs_left_margin;
	var	$abs_right_margin;
	var	$orientation;
	var	$page_type;
	var	$column_order;
	var	$fontName;
	var	$fontSize;
	var	$vsize;
	var	$fillmode = false;
	var	$justifys = array (
		"right" => "R",
		"centre" => "C",
		"center" => "C",
		"left" => "L"
		);
	var	$orientations = array (
		"Portrait" => "P",
		"Landscape" => "L"
		);
	var	$page_types = array (
		"B5" => array ("height" => 709, "width" => 501 ),
		"A6" => array ("height" => 421, "width" => 297 ),
		"A5" => array ("height" => 595, "width" => 421 ),
		"A4" => array ("height" => 842, "width" => 595 ),
		"A3" => array ("height" => 1190, "width" => 842 ),
		"A2" => array ("height" => 1684, "width" => 1190 ),
		"A1" => array ("height" => 2380, "width" => 1684 ),
		"A0" => array ("height" => 3368, "width" => 2380 ),
		"US-Letter" => array ("height" => 792, "width" => 612 ),
		"US-Legal" => array ("height" => 1008, "width" => 612 ),
		"US-Ledger" => array ("height" => 792, "width" => 1224 ),
		);
	var	$yjump = 0;
	var	$vspace = 0;

    // Maintains record of how high a line is so the next line will be at the right
    // place
    var $current_line_start_y = 0;
    var $current_line_height = 0;
    var $calculated_line_height = 0;
    var $max_line_height = 0;

    // Array of styles. Each style type is a stack that maintains the current
    // Text colour background colour etc
    var $stylestack;

    // Keeps track of how many cells in current line printed/to print
    var $no_columns_printed = 0;
    var $no_columns_to_print = 0;

    // Holds selected styles for rows/cells/allcells so they can merged 
    var $row_styles = array();
    var $allcell_styles = array();
    var $cell_styles = array();

    // Drawing mode, in Calculate mode we run through a line of values calculating
    // total width and height and then draw all text elements in Draw mode based
    // on knowing how wide things are
    var $draw_mode = "DRAW";
	
    // Factor to apply to image pixel size to get them to show at correct size in PDF document
	var	$pdfImageDPIScale = 0.72;

	function __construct ()
	{
		$this->column_spacing = 0;
	}

    // For each line reset styles to default values
    function set_default_styles()
    {
		reportico_report::set_default_styles();

		// Default column headers to underlined if not specified
        if ( !$this->query->output_header_styles )
		{
        	$this->query->output_header_styles["border-style"] = "solid";
        	$this->query->output_header_styles["border-width"] = "0 0 1 0";
        	$this->query->output_header_styles["border-color"] = array(0, 0, 0);
		}

        if ( !$this->query->output_before_form_row_styles )
		{
        	$this->query->output_before_form_row_styles["border-style"] = "solid";
        	$this->query->output_before_form_row_styles["border-width"] = "0 0 0 0";
        	$this->query->output_before_form_row_styles["border-color"] = array(0, 0, 0);
		}

        if ( !$this->query->output_after_form_row_styles )
		{
        	$this->query->output_after_form_row_styles["border-style"] = "solid";
        	$this->query->output_after_form_row_styles["border-width"] = "1 0 0 0";
        	$this->query->output_after_form_row_styles["border-color"] = array(0, 0, 0);
		}

        if ( !$this->query->output_group_trailer_styles )
		{
        	$this->query->output_group_trailer_styles["border-style"] = "solid";
        	$this->query->output_group_trailer_styles["border-width"] = "1 0 1 0";
        	$this->query->output_group_trailer_styles["border-color"] = array(0, 0, 0);
		}

		// Turn off page header and body background as its too complicated for now
        if ( isset($this->query->output_reportbody_styles["background-color"]) )
            unset($this->query->output_reportbody_styles["background-color"]);
        if ( isset($this->query->output_page_styles["background-color"]) )
            unset($this->query->output_page_styles["background-color"]);
    }

	function start ()
	{
		reportico_report::start();
		$this->debug("PDF Start **");


        // Set default page size, margins, fonts etc
		$this->page_line_count = 0;
		$this->fontName = $this->query->get_attribute("pdfFont");
		$this->fontSize = $this->query->get_attribute("pdfFontSize");
		$this->vsize = $this->fontSize + $this->vspace;
		$this->orientation = $this->query->get_attribute("PageOrientation");
		$this->page_type = $this->query->get_attribute("PageSize");
		if ( $this->orientation == "Portrait" )
		{
			$this->abs_page_width = $this->page_types[$this->page_type]["width"];
			$this->abs_page_height = $this->page_types[$this->page_type]["height"];
		}
		else
		{
			$this->abs_page_width = $this->page_types[$this->page_type]["height"];
			$this->abs_page_height = $this->page_types[$this->page_type]["width"];
		}
		$this->abs_top_margin = $this->abs_paging_height($this->query->get_attribute("TopMargin"));
		$this->abs_bottom_margin = $this->abs_page_height - 
						$this->abs_paging_height($this->query->get_attribute("BottomMargin"));
		$this->abs_right_margin = $this->abs_page_width - 
						$this->abs_paging_width($this->query->get_attribute("RightMargin"));
		$this->abs_left_margin = $this->abs_paging_width($this->query->get_attribute("LeftMargin"));

        // Set up default styles
        $this->stylestack = array(
                "border-width" => array( 0 => "" ),
                "padding" => array( 0 => false ),
                "border-style" => array( 0 => "none" ),
                "border-color" => array( 0 => "#000000" ),
                "font-size" => array( 0 => $this->fontSize ),
                "color" => array( 0 => "#000000" ),
                "background-color" => array( 0 => array ( 255, 255, 255 ) ),
                "isfilling" => array( 0 => false ),
                "padding" => array( 0 => 0 ),
               );

        // If font used is a Unicode Truetype font then
        // use Unicode PDF generator
        $pdf_path = find_best_location_in_include_path( "fpdf" );
        require_once($pdf_path."/fpdf.php");
        require_once($pdf_path."/ufpdf.php");

        $this->document = new FPDF($this->orientations[$this->orientation],'pt',$this->page_type);

        if ( !isset($this->document->CoreFonts[strtolower($this->fontName)]) )
            if ( !isset ($this->document->fonts[strtolower($this->fontName)] ) )
                $this->document->AddFont($this->fontName, '', $this->fontName.'.php');

        // If the font loaded is a TrueTypeUnicode font, then we wnat to 
        // use UniCode PDF generator instead
        if ( $this->document->FontType == "TrueTypeUnicode" )
        {
		    $this->document = new UFPDF($this->orientations[$this->orientation],'pt',$this->page_type);
            if ( !isset($this->document->CoreFonts[strtolower($this->fontName)]) )
                if ( !isset ($this->document->fonts[strtolower($this->fontName)] ) )
                    $this->document->AddFont($this->fontName, '', $this->fontName.'.php');
        }

		$this->document->SetAutoPageBreak(false);
		$this->document->SetMargins(0,0,0);
		$this->document->SetCreator('Reportico');
		$this->document->SetAuthor('Reportico');
		$this->document->SetTitle($this->reporttitle);

		// Calculate column print and width poistions based on the column start attributes
		$looping = true;

		foreach ( $this->query->display_order_set["column"] as $k => $w )
		{
			$col = get_query_column($w->query_name, $this->query->columns ) ;
			$startcol =  $col->attributes["ColumnStartPDF"];
			$colwidth =  $col->attributes["ColumnWidthPDF"];
			if ( $startcol )
				$col->abs_column_start = $this->abs_paging_width($startcol);
			else
				$col->abs_column_start = 0;
			if ( $colwidth )
				$col->abs_column_width = $this->abs_paging_width($colwidth);
			else
				$col->abs_column_width = 0;
		}

		while ( $looping )
		{
			$fromkey = 0;
			$nextkey = 0;
			$frompos = 0;
			$nextpos = 0;
			$topos = 0;
			$lastwidth = 0;
			$looping = false;
			$gapct = 0;
			$k = 0;
			$colct = count($this->query->display_order_set["column"]);
			$coltaken = 0;
			$colstocalc = 0;
			$colswithwidth = 0;

			foreach ( $this->query->display_order_set["column"] as $k => $w )
			{
				if ( $w->attributes["column_display"] != "show")
					continue;

				{
						$col = get_query_column($w->query_name, $this->query->columns ) ;
						$startcol =  $col->abs_column_start;
						$colwidth =  $col->abs_column_width;
						if ( $startcol )
						{
							if ( /*$fromkey &&*/ $frompos && $gapct )
							{
								$topos = $col->abs_column_start;
								break;
							}
							else
							{
								$fromkey = $k;
								$tokey = $k;
								$frompos = $col->abs_column_start;
								if ( $colwidth )
								{
									$coltaken += $colwidth;
									$coltaken = 0;
									$colswithwidth=1;
									$colstocalc=1;
								}
								else
								{
									$colstocalc++;
									$gapct++;
								}
							}
							$lastct = 0;
						}
						else
						{
							if ( !$frompos )
							{
								$col->abs_column_start = $this->abs_left_margin;
								$frompos = $col->abs_column_start;
								$fromkey = $k;
							}
							if ( $colwidth )
							{
								$coltaken += $colwidth;
								$colswithwidth++;
							}
							$colstocalc++;
							$tokey =$k;
							$gapct++;
							$looping = true;
						}
				}

			}

			if ( !$gapct )
				break;

			// We have two known positions find total free space between
			$calctoend = false;
			if ( !$topos )
			{
				$calctoend = true;
				$topos =  $this->abs_right_margin;
			}

			$totwidth = $topos - $frompos;
			if ( $coltaken > $totwidth )
				$coltaken = $totwidth;

			$colno = 0;
			$calccolwidth = ( $totwidth - $coltaken ) / (( $colstocalc - $colswithwidth ) );
			$lastpos = $this->abs_left_margin;
			for ( $ct = $fromkey; $ct <= $tokey; $ct++ )
			{
				$col1 =& $this->query->display_order_set["column"][$ct];
				if ( $col1->attributes["column_display"] == "show")
				{
					$abspos = $col1->abs_column_start;

					if ( !$abspos )
					{
						$col1->abs_column_start = $lastpos;
						$colwidth =  $col1->attributes["ColumnWidthPDF"];
						if ( $colwidth )
						{
							$col1->abs_column_width = $this->abs_paging_width($colwidth);
							$lastpos = $col1->abs_column_start + $col1->abs_column_width;
						}
						else
						{
							$col1->abs_column_width = $calccolwidth;
							$lastpos = $col1->abs_column_start + $calccolwidth;
						}
					}
					else
					{
						$colwidth =  $col1->attributes["ColumnWidthPDF"];
						if ( $colwidth )
						{
							$col1->abs_column_width = $this->abs_paging_width($colwidth);
							$lastpos = $col1->abs_column_start + $col1->abs_column_width;
						}
						else
						{
							$col1->abs_column_width = $calccolwidth;
							$lastpos = $col1->abs_column_start + $calccolwidth;
						}
					}
				}
			}

		}
	}


	function finish ()
	{
		reportico_report::finish();
		$this->debug("Finish");

		//if ( $this->line_count < 1 )
		//{
            //// No PDF data found just return
            //return;
		//}

		$this->document->SetDisplayMode("real");
		//$this->document->pdf_close($this->document);
		if ( $this->report_file )
		{
			$this->debug("Saved to $this->report_file");
		}
		else
		{
			$this->debug("No pdf file specified !!!");
			//$buf = $this->document->pdf_get_buffer($this->document);
			$buf = $this->document->Output("", "S");
			$len = strlen($buf);

			if ( ob_get_length() > 0 )
				ob_clean();	

			header("Content-Type: application/pdf");
			header("Content-Length: $len");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            $attachfile = "reportico.pdf";
            if ( $this->reportfilename )
                $attachfile = preg_replace("/ /", "_", $this->reportfilename.".pdf");
			header('Content-Disposition: attachment;filename='.$attachfile);


			print($buf);
			die;
		}
	}

	function abs_paging_height($height_string)
	{
		//if ( preg_match("/(\d*\.*(\d+)(\D*)/", $height_string, $match) )
		if ( preg_match("/(\d+)(\D*)/", $height_string, $match) )
		{
			$height = $match[1];
			if ( isset( $match[2] ) )
			{
				switch ( $match[2] )
				{
					case "pt":
						$height = $height;
						break;

					case "%":
						$height = ( $height * $this->abs_page_height ) / 100;
						break;

					case "mm":
						$height = $height / 0.35277777778;
						break;

					case "cm":
						$height = $height / 0.035277777778;
						break;

					default:
						//handle_error("Unknown Page Sizing Option ".$match[2]);
						break;

				}
			}
		}
		else
		{
			$height = $height_string;
			//handle_error("Unknown Page Sizing Option $height_string");
		}

		return $height;
	}

	function abs_paging_width($width_string)
	{
		if ( preg_match("/(\d+)(\D*)/", $width_string, $match) )
		{
			$width = $match[1];
			if ( isset( $match[2] ) )
			{
				switch ( $match[2] )
				{
					case "pt":
						$width = $width;
						break;

					case "%":
						$width = ( $width * $this->abs_page_width ) / 100;
						break;

					case "mm":
						$width = $width / 0.35277777778;
						break;

					case "cm":
						$width = $width / 0.035277777778;
						break;

					//default:
						//handle_error("Unknown age Sizing Option $width_string");
						//break;

				}
			}
		}
		else
		{
			$width = $width_string;
			//handle_error("Unknown Page Sizing Option $width_string");
		}

		return $width;
	}

	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first = false) // PDF
	{
		if ( !get_reportico_session_param("target_show_group_trailers") )
			return;

		if ( $value_col )
		{

			$y = $this->document->GetY();

			// Fetch Group Header Label
			$group_label = $value_col->get_attribute("group_header_label" );
			if ( !$group_label )
				$group_label = $value_col->get_attribute("column_title" );

			if ( !$group_label )
			{
				$group_label = $value_col->query_name;
				$group_label = str_replace("_", " ", $group_label);
				$group_label = ucwords(strtolower($group_label));
			}

			$group_label = sw_translate($group_label);

			// Fetch Group Header Label End Column + display
			$group_xpos = $trailer_col->abs_column_start;

			$wd = $trailer_col->abs_column_width;
			if ( $wd - $this->column_spacing > 0 )
				$wd = $wd - $this->column_spacing;

			$this->set_position($group_xpos, $y);
			$padstring = $value_col->old_column_value;
			$just = $this->justifys[$trailer_col->derive_attribute( "justify",  "left")];
			$group_label = $value_col->get_attribute("group_trailer_label" );
			if ( !$group_label )
				$group_label = $value_col->get_attribute("column_title" );
            if ( !$group_label )
            {  
                $group_label = $value_col->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }

			if ( $group_label && $group_label != "BLANK" )
				$padstring = $group_label." ".$padstring;

			$this->draw_cell($wd,$this->vsize + 2,"$padstring", "PBF", 0, $just);

			// Fetch Group Header Label Start Column + display
			$group_xpos = $value_col->get_attribute("group_header_label_xpos" );
			if ( !$group_xpos )
				$group_xpos = 0;
			$group_xpos = $this->abs_paging_width($group_xpos);
			$group_xpos = $value_col->abs_column_start;

			$this->set_position($group_xpos, $y);
			$padstring = $group_label;
			$just = $this->justifys[$trailer_col->derive_attribute( "justify",  "left")];
		}

	}

    //Cell with horizontal scaling if text is too wide
    function draw_cell_container($w,$h=0,$txt='',$border=0,$ln=0,$align='',$valign="T")
    {
        // Add padding
        $padding = end( $this->stylestack["padding"]);
        $toppad = $padding[0];
        $bottompad = $padding[2];

        // Add border and bg color
        $fill = end( $this->stylestack["isfilling"]);
        $borderwidth = end( $this->stylestack["border-width"]);
        $border = end( $this->stylestack["border-style"]);
        if ( $border != "none" )
            $border = 1;
        else
            $borderwidth = "false";

        // Store current position so we can jump back after cell draw
		$x = $this->document->GetX();
		$y = $this->document->GetY();
        $this->document->MultiCell($w,$this->max_line_height,"",$borderwidth,false,$fill);
        $cell_height = $this->document->GetY() - $y;

        // Jump back
		$this->set_position($x, $y);
    }

    //Cell with horizontal scaling if text is too wide
    function draw_cell($w,$h=0,$txt='',$implied_styles="PBF",$ln=0,$align='',$valign="T", $link='')
    {
        // If a cell contains a line break like a "<BR>" then convert it to new line
        $txt = preg_replace("/<BR>/i", "\n", $txt);
        // Calculate cell height as string width divided by width
        $str_width=$this->document->GetStringWidth($txt);
        $numlines = ceil( $this->document->GetStringWidth($txt) / ($w - 1) );
        $numlines = $this->document->NbLines($w, $txt);
        $cellheight = ceil ( $numlines * $h );

        if ( $this->draw_mode == "CALCULATE" )
        {
            if ( $cellheight > $this->calculated_line_height )
                $this->calculated_line_height = $cellheight;
        }

        // Add padding
        $toppad = 0;
        $bottompad = 0;
        if ( strstr($implied_styles, "P" ))
        {
            $padding = end( $this->stylestack["padding"]);
            $toppad = $padding[0];
            $bottompad = $padding[2];
        }

        $fill = false;
        if ( strstr($implied_styles, "F" ))
        {
            // Add border and bg color
            $fill = end( $this->stylestack["isfilling"]);
        }

        
        $borderwidth = false;
        if ( strstr($implied_styles, "B" ))
        {
            $borderwidth = end( $this->stylestack["border-width"]);
            $border = end( $this->stylestack["border-style"]);
            if ( $border != "none" )
                $border = 1;
            else
                $borderwidth = "false";
        }

        // Store current position so we can jump back after cell draw
		$y = $this->document->GetY();

        // To cater for multiline values, jump to bottom of line + padding -
        // cell height
        if ( $valign == "T" )
            $jumpy = $toppad;
        else if ( $valign == "B" )
            $jumpy = $toppad + $this->calculated_line_height - $cellheight;
        else if ( $valign == "C" )
            $jumpy = ( ( $toppad + $this->calculated_line_height + $bottompad ) - $cellheight ) / 2;

        if ( $this->draw_mode == "CALCULATE" )
        {
                $fill_line_height = $toppad + $this->calculated_line_height + $bottompad;
                if ( $this->max_line_height < $fill_line_height )
                    $this->max_line_height = $fill_line_height;
                return;
        }

        if ( $toppad )
        {
            $tmpborder = "";
            if ( preg_match("/T/", $borderwidth ) )
            {
                $tmpborder = "T";
                $borderwidth = preg_replace("/T/", "", $borderwidth);
            }
            
            $prevx = $this->document->GetX();
            $this->document->MultiCell($w,$toppad,"",$tmpborder,$align,$fill,$link);
		    $this->set_position($prevx, false);
        }

        if ( $bottompad )
        {
            $tmpborder = "";
            if ( preg_match("/B/", $borderwidth ) )
            {
                $tmpborder = "B";
                $borderwidth = preg_replace("/B/", "", $borderwidth);
            }
            
            $prevx = $this->document->GetX();
            $this->document->MultiCell($w,$bottompad,"",$tmpborder,$align,$fill,$link);
		    $this->set_position($prevx, false);
        }


		$this->set_position(false, $y + $jumpy );

        // Link in a PDF must include a full URL contain http:// element
        // drilldown link of web url can be relative .. so prepend required elements
        if ( $link )
        {
            if ( !preg_match("/^http:\/\//", $link) && !preg_match("/^\//", $link ) )
                $link = "http://".$_SERVER["HTTP_HOST"].dirname($this->query->url_path_to_reportico_runner)."/".$link;
            if ( preg_match("/^\//", $link ) )
                $link = SW_HTTP_URLHOST."/".$link;
        }
        $this->document->MultiCell($w,$h,$txt,$borderwidth,$align,$fill,$link);
        $cell_height = $this->document->GetY() - $y;

        if ( $cell_height > $this->current_line_height )
            $this->current_line_height = $cell_height;

        // Jump back
		$this->set_position(false, $y);
    }

    // New Line ensure next line appears under the highest cell on the current so use
    // record of current line height
	function end_line($h = false)
	{
        // Dont draw line ends in draw mode
        if ( $this->draw_mode == "CALCULATE" )
            return;

        if ( $this->current_line_height )
        {
		    $this->document->Ln(0);
            $this->set_position(false, $this->current_line_start_y + $this->current_line_height);
        }
        else
        {
            if ( $h )
            {
		        $this->document->Ln($h);
            }
            else
		        $this->document->Ln();
        }
        $this->current_line_start_y = $this->document->GetY();
        $this->current_line_height = 0;
        $this->max_line_height = 0;
        $this->calculated_line_height = 0;
	}

	function format_page_footer_start() // PDF
	{
	    $this->unapply_style_tags($this->query->output_page_styles);
    }

	function format_page_footer_end() // PDF
	{
	    $this->unapply_style_tags($this->query->output_reportbody_styles);
    }

	function format_page_header_start() // PDF
	{
		$this->reporttitle = $this->query->derive_attribute("ReportTitle", "Set Report Title");
                    
        // Add custom image here
        if ( defined("PDF_HEADER_IMAGE") )
        {
            $x = 500;
            $y = 25;
            $w = 50;
            if ( defined("PDF_HEADER_XPOS") ) $x = PDF_HEADER_XPOS;
            if ( defined("PDF_HEADER_YPOS") ) $y = PDF_HEADER_YPOS;
            if ( defined("PDF_HEADER_WIDTH") ) $w = PDF_HEADER_WIDTH;

            $h = $this->document->Image(PDF_HEADER_IMAGE, $x, $y, $w *  $this->pdfImageDPIScale);
        }

        //$this->set_default_styles();
	    $this->apply_style_tags($this->query->output_reportbody_styles);
		return;
	}

	function format_page_header_end() // PDF
	{
		$this->end_line();
	    $this->apply_style_tags($this->query->output_page_styles);
		//$this->end_line();
	}

	function before_format_criteria_selection()
	{
	}

	function format_criteria_selection($label, $value)
	{
		$y = $this->document->GetY();

		$this->yjump = 0;
		// Fetch Group Header Label Start Column + display
		$group_xpos = false;
		if ( !$group_xpos )
			$group_xpos = $this->abs_left_margin;
		$group_xpos = $this->abs_paging_width($group_xpos);

		$this->set_position($group_xpos, $y);
		$padstring = $label;
		$this->draw_cell( 120, $this->vsize, "$padstring");

		// Fetch Group Header Label End Column + display
		$group_xpos = false;
		if ( !$group_xpos )
			$group_xpos = $this->abs_paging_width($group_xpos) + 140;
		$group_xpos = $this->abs_paging_width($group_xpos);

		$this->set_position($group_xpos, $y);
		$padstring = $value;
		$this->draw_cell(400, $this->vsize, "$padstring");
		$this->end_line();
		$y = $this->document->GetY();

		if ( $this->yjump )
			$this->set_position(false, $y + $this->yjump);

		$label = "";
		$value = "";
	}

	function after_format_criteria_selection()
	{
	}

	function format_group_header_start() // PDF
	{
        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

		$this->end_line();

		// Throw new page if current position + number headers + line + headers > than bottom margin
		$ln = 0;
        $totheaderheight = 0;
        $prevheight = $this->calculated_line_height;
		foreach ( $this->query->groups as $val )
        {
			for ($i = 0; $i < count($val->headers); $i++ )
			{
				$col =& $val->headers[$i];
				$this->format_group_header($col, true);
                $totheaderheight += $this->calculated_line_height;
			}
        }
        $this->calculated_line_height = $totheaderheight;
		$y = $this->document->GetY();
        $this->check_page_overflow();
        $this->calculated_line_height = $prevheight;
	}

	function format_group_header_end()
	{
		$this->end_line();
	}

	function format_group_trailer_start($first=false) // PDF
	{

        // Tiny padding between group trailers and bofy detail so cell border doesnt overwrite heading underline
        if ( $first )
		    $this->end_line(1);
        $this->apply_style_tags($this->query->output_group_trailer_styles);
		return;
	}

	function format_group_trailer_end() // PDF
	{
        $this->unapply_style_tags($this->query->output_group_trailer_styles);
		return;
	}

	function format_group_header(&$col, $calculate_only = false) // PDF format group headers
	{
        for ( $ctr = 0; $ctr < 2; $ctr++ )
        {

            $this->draw_mode = "CALCULATE";
            if ( $ctr == 1 && $calculate_only )
            {
                $this->draw_mode = "DRAW";
                break;
            }

            if ( $ctr == 1 )
            {
                $this->draw_mode = "DRAW";
                $this->check_page_overflow();
            }

		    $y = $this->document->GetY();
		    $group_label = $col->get_attribute("group_header_label" );
		    if ( !$group_label )
			    $group_label = $col->get_attribute("column_title" );
		    if ( !$group_label )
		    {
			    $group_label = $col->query_name;
			    $group_label = str_replace("_", " ", $group_label);
			    $group_label = ucwords(strtolower($group_label));
		    }
		    $group_label = sw_translate($group_label);

		    $this->yjump = 2;
		    // Fetch Group Header Label Start Column + display
		    $group_xpos = $col->get_attribute("group_header_label_xpos" );
		    $group_data_xpos = $col->get_attribute("group_header_data_xpos" );

		    if ( !$group_xpos )
			    $group_xpos = $this->abs_left_margin;
		    if ( !$group_data_xpos )
			    $group_data_xpos = $group_xpos + 150;

		    $group_xpos = $this->abs_paging_width($group_xpos);
		    $group_data_xpos = $this->abs_paging_width($group_data_xpos);
		    $group_label_width = $group_data_xpos - 5;
		    $group_data_width = $this->abs_right_margin - $group_data_xpos;

            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
                $this->unapply_style_tags($this->query->output_page_styles);

            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->apply_style_tags($this->query->output_group_header_label_styles);
		    $this->set_position($group_xpos, $y);
		    $padstring = $group_label;
		    $this->draw_cell( $group_label_width, $this->vsize, "$padstring");
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->unapply_style_tags($this->query->output_group_header_label_styles);
		    $this->set_position($group_data_xpos, $y);
    
            // Display group header value
		    $contenttype = $col->derive_attribute( "content_type",  $col->query_name);
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->apply_style_tags($this->query->output_group_header_value_styles);

			$qn = get_query_column($col->query_name, $this->query->columns ) ;
		    if ( $contenttype == "graphic"  || preg_match("/imagesql=/", $qn->column_value))
		    {
                if ( $this->draw_mode == "CALCULATE" )
                    continue;

			    $qn = get_query_column($col->query_name, $this->query->columns ) ;
			    $sql = @preg_replace("/.*imagesql=/", "", $qn->column_value);
			    $sql = @preg_replace("/'>$/", "", $sql);
			    $str = 
			    &get_db_image_string(
				    $this->query->datasource->driver, 
				    $this->query->datasource->database, 
				    $this->query->datasource->host_name, 
				    $sql,
				    $this->query->datasource->ado_connection
			    );

			    if ( $str )
			    {
				    $tmpnam = tempnam(SW_TMP_DIR, "dbi");
                    unlink ($tmpnam);
				    $width = $qn->abs_column_width;
				    $height = 20;
			    	$im = imagecreatefromstring($str);

				    if ( imagepng($im, $tmpnam.".png" ) )
				    {
					    $x = $qn->abs_column_start;
					    $y = $this->document->GetY();
					    $this->set_position($group_data_xpos);
					    //$h = $this->document->ImageHeight($tmpnam.".png", $group_xpos, $y, $width );
					    $h = $this->document->Image($tmpnam.".png", $group_data_xpos, $y, $width  * $this->pdfImageDPIScale ) + 2;
                        if ( $h > $this->max_line_height )
                            $this->max_line_height = $h;
					    $this->yjump =$h;
					    unlink($tmpnam.".png");
				    }
			    }
		    }
		    else
		    {
			    $this->set_position($group_data_xpos, $y);
			    $padstring = $qn->column_value;
			    $this->draw_cell($group_data_width, $this->vsize, "$padstring");
		    }
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->unapply_style_tags($this->query->output_group_header_value_styles);
		    $this->end_line();
		    $this->draw_cell($group_data_width, $this->vsize, "");    // Blank cell to continue page breaking at this size
		    $y = $this->document->GetY();

		    if ( $this->yjump )
			    $this->set_position(false, $y + $this->yjump);

            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->apply_style_tags($this->query->output_page_styles);
        }
	}


	function format_column_header(& $column_item)   //PDF column headers
	{
		if ( !get_reportico_session_param("target_show_column_headers") )
			return;

		if ( !$this->show_column_header($column_item) )
				return;

		$k =& $column_item->query_name;
		$padstring = $column_item->derive_attribute( "column_title",  $column_item->query_name);
		$padstring = str_replace("_", " ", $padstring);
		$padstring = ucwords(strtolower($padstring));
		$padstring = sw_translate($padstring);

		$just = $this->justifys[$column_item->derive_attribute( "justify",  "left")];

		$contenttype = $column_item->derive_attribute(
			"content_type",  $column_item->query_name);

		$tw = $column_item->abs_column_start;
		$x = $this->document->GetX();
		$y = $this->document->GetY();
		$this->set_position($tw, $y);

		$wd = $column_item->abs_column_width;
		if ( $wd - $this->column_spacing > 0 )
			$wd = $wd - $this->column_spacing;
		if ( !$wd )
		{
			$this->document->Write( "$padstring");
		}
		else
		{
			$this->draw_cell($wd, $this->vsize + 4, $padstring ,"PBF",0,$just, "B");
		}
	}

	function plot_graph(&$graph, $graph_ct = false)
	{
		$this->end_line();
		
		$tmpnam = tempnam(SW_TMP_DIR, "gph");
        if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE == "PCHART" )
        {
		    unlink($tmpnam);
		    $img = $graph->generate_graph_image($tmpnam.".png");
        }
        else /* If jpgraph */
        {
		    $handle = $graph->generate_graph_image();
		    unlink($tmpnam);
		    $img = imagepng($handle, $tmpnam.".png" );
		}
		if ( $img );
		{
			$x = $this->document->GetX();
			$y = $this->document->GetY();
			$this->set_position($this->abs_left_margin, false);

			$width = $graph->width_pdf_actual;
			$height = $graph->height_pdf_actual;

			//if ( $width > ($this->abs_right_margin - $this->abs_left_margin) )
			//{
				//$height = $height * (  ($this->abs_right_margin - $this->abs_left_margin) / $width );
				////$width = ($this->abs_right_margin - $this->abs_left_margin);
			//}
			$xaddon = ( $this->abs_right_margin - $this->abs_left_margin - ($width * $this->pdfImageDPIScale) ) / 2 ;
			if ( $y + $height >= $this->abs_bottom_margin )
			{
				$this->finish_page();
				$this->begin_page();
				$x = $this->document->GetX();
				$y = $this->document->GetY();
			}

			$this->document->Image($tmpnam.".png", $this->abs_left_margin + $xaddon, $y, $width * $this->pdfImageDPIScale, $height * $this->pdfImageDPIScale );
			$y = $this->set_position(false, $y + $height);
			$this->end_line();
		}
		unlink($tmpnam.".png");
	}

	function format_headers() // PDF
	{
        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

        // Handle multi line headers by processing all headers 
        // in "CALCULATE" mode and then print them on the appropriate line
        $this->draw_mode = "CALCULATE";
		foreach ( $this->columns as $w )
		{
            $this->apply_style_tags($this->query->output_header_styles);
            $this->format_column_header($w);
            $this->unapply_style_tags($this->query->output_header_styles);
       	}
   		$this->draw_mode = "DRAW";
        $this->check_page_overflow();
		foreach ( $this->columns as $w )
        {
            $this->apply_style_tags($this->query->output_header_styles);
            $this->format_column_header($w);
            $this->unapply_style_tags($this->query->output_header_styles);
        }
		$this->end_line();
        $this->unapply_style_tags($this->query->output_page_styles);
		$this->draw_cell(5, $this->vsize, "");    // Blank cell to continue page breaking at this size
        $this->apply_style_tags($this->query->output_page_styles);

        // Tiny padding between column headers and rows so cell border doesnt overwrite heading underline
        $this->current_line_height = 0;
        $this->max_line_height = 0;
		$this->end_line(1);
	}

    function showXY($txt = "")
    {
        $x = $this->document->GetX();
        $y = $this->document->GetY(); 
        $this->set_position (2, false);
        $txt .= " ($x, $y)";
        $this->document->Cell(140, 20, $txt);
        $this->set_position ($x, $y);
    }


	function format_column(& $column_item)
	{
		if ( !$this->show_column_header($column_item) )
				return;
        // Keep track of how many columns in current row to print if calculating
        // or already printed if drawing so we can calculate when to draw
        // borders around a cell
        if ( $this->draw_mode == "CALCULATE" )
        {
            $this->no_columns_to_print++;
            $this->no_columns_printed++;
        }
        else
            $this->no_columns_printed++;


		$k =& $column_item->column_value;
		$tw = $column_item->abs_column_start;
		$wd = $column_item->abs_column_width;

		if ( $wd - $this->column_spacing > 0 )
			$wd = $wd - $this->column_spacing;
		$just = $this->justifys[$column_item->derive_attribute( "justify",  "left")];
		$contenttype = $column_item->derive_attribute(
			"content_type",  $column_item->query_name);

	    if ( $contenttype == "graphic"  || preg_match("/imagesql=/", $column_item->column_value))
	    {
			$sql = @preg_replace("/.*imagesql=/", "", $column_item->column_value);
			$sql = @preg_replace("/'>$/", "", $sql);
			$str = 
			&get_db_image_string(
				$this->query->datasource->driver, 
				$this->query->datasource->database, 
				$this->query->datasource->host_name, 
				$sql,
				$this->query->datasource->ado_connection
			);

			if ( $str )
			{
				$tmpnam = tempnam(SW_TMP_DIR, "dbi");
                unlink ($tmpnam);
				$width = $column_item->abs_column_width;
				$height = 20;
				$im = imagecreatefromstring($str);

				if ( imagepng($im, $tmpnam.".png" ) )
				{
					$x = $column_item->abs_column_start;
					$y = $this->document->GetY();
					$this->set_position($x, false);
					$h = $this->document->Image($tmpnam.".png", $x, $y, $width * $this->pdfImageDPIScale ) + 2;
					if ( $h > $this->yjump )
						$this->yjump =$h;
                    if ( $h > $this->max_line_height )
                        $this->max_line_height = $h;

					unlink($tmpnam.".png");
				}
			}
		}
		else
		{
			if ( !$wd )
				$this->document->Write( "$padstring");
			else
			{
				$this->set_position($tw, false);

                $this->allcell_styles = array("border-width" => "");
                $this->cell_styles = array("border-width" => "");
			    $this->apply_style_tags($this->query->output_allcell_styles, false, false, "ALLCELLS");
			    $this->apply_style_tags($column_item->output_cell_styles, false, false, "CELLS");
                $this->apply_row_border_to_cell ();
                if ( $this->draw_mode == "DRAW" )
                {
                        
				    $this->draw_cell_container($wd, $this->vsize + 4, $k,"PBR",0,$just);
                }
                $link = false;
                if ( $column_item->output_hyperlinks )
                    $link = $column_item->output_hyperlinks["url"];
				$this->draw_cell($wd, $this->vsize + 4, $k,"P",0,$just, "T", $link);
			    $this->unapply_style_tags($column_item->output_cell_styles);
			    $this->unapply_style_tags($this->query->output_allcell_styles);
				$tw = $this->abs_page_width - $this->abs_right_margin;
			}
		}
	}

    // If first cell in row then, if row has border then ensure it 
    // is drawn with top, bottow, left border, last cell has right, top, bottom
    // middle cells have top and bottom
    // first column of a row has parent row with border
    function apply_row_border_to_cell ()
    {
        if (  isset ( $this->row_styles ["border-width" ] ) && $this->row_styles ["border-width" ])
        {
            $cellstyle = $this->cell_styles ["border-width" ];
            if ( !$cellstyle )
            {
                $cellstyle = $this->allcell_styles ["border-width" ];
            }
            $rowstyle = $this->row_styles ["border-width" ];
            if ( !strstr ( $cellstyle, "T" ) && strstr ( $rowstyle, "T" ) ) $cellstyle .= "T";
            if ( !strstr ( $cellstyle, "B" ) && strstr ( $rowstyle, "B" ) ) $cellstyle .= "B";
            if ( $this->no_columns_printed == 1 )
            {   
                if ( !strstr ( $cellstyle, "L" ) && strstr ( $rowstyle, "L" ) ) $cellstyle .= "L";
            }
            if ( $this->no_columns_printed == $this->no_columns_to_print )
            {   
                if ( !strstr ( $cellstyle, "R" ) && strstr ( $rowstyle, "R" ) ) $cellstyle .= "R";
            }
            end($this->stylestack["border-width"]);
            $this->stylestack["border-width"][key($this->stylestack["border-width"])] = $cellstyle;
        }
    }

    // Removes a tag element from the style stack
    function set_style_tag ( $styleset, $tag, $value )
    {
        $styleset[$tag]  = $value;
    }
        
    // Removes a tag element from the style stack
    function disable_style_tag ( $styleset, $tag )
    {
        foreach ( $styleset as $k => $v )
        {
            if ( isset ( $this->stylestack[$k] ) && $k == $tag )
            {
                unset($this->stylestack[$k]);
            }
        }
    }
        
    function apply_style_tags ( $styleset, $parent_styleset = false, $grandparent_styleset = false, $apply_type = false )
    {
        for ( $ct = 1; $ct < 4; $ct++ )
        {
            $work_styleset = false;
            if ( $ct == 1 ) $work_styleset =& $grandparent_styleset;
            if ( $ct == 2 ) $work_styleset =& $parent_styleset;
            if ( $ct == 3 ) $work_styleset =& $styleset;
            
            if ( !$work_styleset )
                continue;

            if ( $work_styleset && is_array($work_styleset) )
            {
                foreach ( $work_styleset as $k => $v )
                {
                    if ( isset ( $this->stylestack[$k] ) )
                    {
                        if ( $k == "padding" )
                        {
                            $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            $ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            if ( $ar )
                                if ( count($ar) == 1 && $ar[0] > 0 ) 
                                {
                                    $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                                }
                                else if ( count($ar) == 2 )
                                {
                                    $tmp[0] = $tmp[2] = $ar[1];
                                    $tmp[1] = $tmp[3] = $ar[2];
                                }
                                else if ( count($ar) == 3 )
                                {
                                    $tmp[0] = $ar[0];
                                    $tmp[1] = $ar[1];
                                    $tmp[2] = $ar[2];
                                }
                                else if ( count($ar) == 4 )
                                {
                                    $tmp[0] = $ar[0];
                                    $tmp[1] = $ar[1];
                                    $tmp[2] = $ar[2];
                                    $tmp[3] = $ar[3];
                                }
                            $v = $tmp;
                        }

                        if ( $k == "border-width" )
                        {
                            $tmp = "";
                            $v = preg_replace("/px/", "", $v);
                            $ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            if ( $ar )
                                if ( count($ar) == 1 && $ar[0] > 0 ) 
                                    $tmp = "LBTR";
                                else if ( count($ar) == 2 )
                                {
                                    if ( $ar[0] > 0 ) $tmp .= "TB";
                                    if ( $ar[1] > 0 ) $tmp .= "LR";
                                }
                                else if ( count($ar) == 3 )
                                {
                                    if ( $ar[0] > 0 ) $tmp .= "T";
                                    if ( $ar[1] > 0 ) $tmp .= "R";
                                    if ( $ar[2] > 0 ) $tmp .= "B";
                                }
                                else if ( count($ar) == 4 )
                                {
                                    if ( $ar[0] > 0 ) $tmp .= "T";
                                    if ( $ar[1] > 0 ) $tmp .= "R";
                                    if ( $ar[2] > 0 ) $tmp .= "B";
                                    if ( $ar[3] > 0 ) $tmp .= "L";
                                }
                            $v = $tmp;

                            if ( $apply_type == "ROW" )
                                $this->row_styles["border-width"] = $v;
                            if ( $apply_type == "ALLCELLS" )
                                $this->allcell_styles["border-width"] = $v;
                            if ( $apply_type == "CELLS" )
                                $this->cell_styles["border-width"] = $v;
                        }
                        if ( $k == "font-size" )
                        {
                            $sz = preg_replace("/[^0-9].*/", "", $v);
                            $this->document->SetFontSize($sz);
                            $v = $sz + $this->vspace;
                            $this->vsize = $v;
                        }
                        if ( $k == "border-color" || $k == "color" || $k == "background-color" )
                        {
                            $v = htmltorgb($v);
                            if ( $k == "border-color" )
                                $this->document->SetDrawColor($v[0], $v[1], $v[2]);
                            if ( $k == "color" )
                            {
                                $this->document->SetTextColor($v[0], $v[1], $v[2]);
                            }
                            if ( $k == "background-color" )
                            {
                                $this->document->SetFillColor($v[0], $v[1], $v[2]);
                                array_push ( $this->stylestack["isfilling"], 1);
                            }
                        }

                        array_push ( $this->stylestack[$k], $v);
                    }
                }
            }
        }
    }

    function unapply_style_tags ( $styleset, $parent_styleset = false, $grandparent_styleset = false, $type = "" )
    {
        for ( $ct = 1; $ct < 4; $ct++ )
        {
            $work_styleset = false;
            if ( $ct == 1 ) $work_styleset =& $grandparent_styleset;
            if ( $ct == 2 ) $work_styleset =& $parent_styleset;
            if ( $ct == 3 ) $work_styleset =& $styleset;
            
            if ( !$work_styleset )
                continue;

            if ( $work_styleset && is_array($work_styleset) )
            {
                foreach ( $work_styleset as $k => $v )
                {
                    if ( isset ( $this->stylestack[$k] ) )
                    {
                        $value = array_pop ( $this->stylestack[$k] );
                        $value = end ( $this->stylestack[$k] );
                        if ( $k == "font-size" )
                        {
                            $this->vsize = $value;
                        }
                        if ( $k == "color" )
                        {
                            $this->document->SetTextColor($value[0], $value[1], $value[2]);
                        }
                        if ( $k == "border-color" )
                        {
                            $this->document->SetDrawColor($value[0], $value[1], $value[2]);
                        }
                        if ( $k == "font-size" )
                        {
                            $sz = preg_replace("/[^0-9].*/", "", $value);
                            $this->document->SetFontSize($sz);
                            $value = $sz + $this->vspace;
                            $this->vsize = $value;
                        }
                        if ( $k == "background-color" )
                        {
                            $this->document->SetFillColor($value[0], $value[1], $value[2]);
                            $value = array_pop ( $this->stylestack["isfilling"] );
                        }
                    }
                }
            }
        }
    }

	function each_line($val) // PDF
	{
        
		reportico_report::each_line($val);

        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
        {
		    $this->end_line();

            // Throw new page if set to throw between rows
	        $formpagethrow = $this->query->get_attribute("formBetweenRows");
		    if ( $this->line_count > 1 && $formpagethrow == "newpage" )
            {
	            $this->finish_page();
	            $this->begin_page();
            }

		    // Throw new page if current position + number headers + line + headers > than bottom margin
		    $ln = 0;
            $totheaderheight = 0;
            $prevheight = $this->calculated_line_height;

            $this->apply_style_tags($this->query->output_before_form_row_styles);
		    $y = $this->document->GetY();
		    $this->set_position($this->abs_left_margin, $y);
		    $this->draw_cell(400, $this->vsize, "");    // Blank cell to continue page breaking at this size
            $this->unapply_style_tags($this->query->output_before_form_row_styles);

		    foreach ( $this->query->groups as $val )
            {
			    for ($i = 0; $i < count($val->headers); $i++ )
			    {
				    $col =& $val->headers[$i];
				    $this->format_group_header($col);
                    $totheaderheight += $this->calculated_line_height;
			    }
            }
            foreach ( $this->query->display_order_set["column"] as $k => $w )
		    {
		        if ( $w->attributes["column_display"] != "show")
					    continue;
                $ct++;

				$this->format_group_header($w);
                $totheaderheight += $this->calculated_line_height;
            }
            $this->calculated_line_height = $totheaderheight;
		    $y = $this->document->GetY();
            $this->check_page_overflow();
            $this->calculated_line_height = $prevheight;

            // Between form solid line or blank line
            if ( $formpagethrow == "blankline" )
            {
                $this->end_line();
                $this->end_line();
            }
            else
            {
                $this->end_line();
                $this->apply_style_tags($this->query->output_after_form_row_styles);
		        $y = $this->document->GetY();
		        $this->set_position($this->abs_left_margin, $y);
		        $this->draw_cell($this->abs_right_margin - $this->abs_left_margin, $this->vsize, "");    // Blank cell to continue page breaking at this size
                $this->unapply_style_tags($this->query->output_after_form_row_styles);
                $this->end_line();

            }

            return;
        }


		$y = $this->document->GetY();
		$this->check_graphic_fit();
		
		$this->yjump = 0;
		if ( $this->body_display == "show" && get_reportico_session_param("target_show_detail") )
		{
            $this->row_styles = array();
			$this->apply_style_tags($this->query->output_row_styles, false, false, "ROW");

            $this->draw_mode = "CALCULATE";
            $this->no_columns_printed = 0;
            $this->no_columns_to_print = 0;
			foreach ( $this->columns as $col )
				$this->format_column($col);
            $this->unapply_style_tags($this->query->output_row_styles);

            $this->draw_mode = "DRAW";

            $this->check_page_overflow();

			//$this->set_position($this->abs_left_margin, false);
			//$this->draw_cell($this->abs_right_margin - $this->abs_left_margin, $this->calculated_line_height, "xx", 0, 0);
			//$this->disable_style_tag($this->query->output_header_styles, "border-width");
            $this->apply_style_tags($this->query->output_row_styles, false, false, "ROW");
            
            $this->no_columns_printed = 0;
			foreach ( $this->columns as $col )
            {
				$this->format_column($col);
            }

			$this->page_line_count++;
            $this->unapply_style_tags($this->query->output_row_styles);
            $nextliney = $this->document->GetY() + $this->max_line_height;
			$this->end_line();
            $this->set_position(false, $nextliney);
		}

		//if ( $this->yjump )
			//$this->set_position(false, $y + $this->yjump);

		//if ( $y + $this->vsize > $this->abs_bottom_margin )
		//{
			//$this->finish_page();
			//$this->begin_page();
		//}


	}

	function check_page_overflow()
    {
        $y = $this->document->GetY();
		//if ( $y + $this->calculated_line_height > $this->abs_bottom_margin )
		if ( $y + $this->max_line_height > $this->abs_bottom_margin )
		{
            
            // Between page breaks store any current lin eparameters
            $prev_calculated_line_height = $this->calculated_line_height;
            $prev_max_line_height = $this->max_line_height;
			$this->finish_page();
			$this->begin_page();
			$this->before_group_headers();
			$this->page_line_count++;
            $this->calculated_line_height = $prev_calculated_line_height;
            $this->max_line_height = $prev_max_line_height;
		}
    }

	function check_graphic_fit()
	{
		$will_fit = true;
		$max_height = $this->vsize;
		foreach ( $this->columns as $col )
		{
			$contenttype = $col->derive_attribute( "content_type",  $col->query_name);
			$qn = get_query_column($col->query_name, $this->query->columns ) ;
		    if ( $contenttype == "graphic"  || preg_match("/imagesql=/", $qn->column_value))
			{
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
				$sql = @preg_replace("/.*imagesql=/", "", $qn->column_value);
				$sql = @preg_replace("/'>$/", "", $sql);
				$str = 
					&get_db_image_string(
					$this->query->datasource->driver, 
					$this->query->datasource->database, 
					$this->query->datasource->host_name, 
					$sql,
					$this->query->datasource->ado_connection
				);

				if ( $str )
				{
					//$im = convert_image_string_to_image($str, "png");
					$tmpnam = tempnam(SW_TMP_DIR, "dbi");
                    unlink ($tmpnam);
					$width = $qn->abs_column_width;
					$height = 20;
					$im = imagecreatefromstring($str);

					if ( imagepng($im, $tmpnam.".png" ) )
					{
						$h = $this->document->ImageHeight($tmpnam.".png", 0, 0, $width );
						unlink($tmpnam.".png");
						if ( $max_height < $h )
							$max_height = $h;
					}
				}
			}
		}

		$y = $this->document->GetY();

		if ( $y + $max_height /*+ 10*/ > $this->abs_bottom_margin )
		{
			$this->finish_page();
			$this->begin_page();

			$this->before_group_headers();
			$this->page_line_count++;
		}

	}

	function page_template()
	{
		$this->debug("Page Template");
	}


    function set_position( $x, $y )
    {
        if ( $this->draw_mode == "CALCULATE" )
            return;

        if ( $x && $y )
        {
            $this->document->SetXY($x, $y);
            $this->current_line_start_y = ($y);
        }
        else
        if ( $y )
        {
            $this->document->SetXY($this->document->GetX(), $y);
            $this->current_line_start_y = ($y);
        }
        if ( $x )
        {
            $this->document->SetX($x, $this->document->GetY());
        }
    }

	function begin_page()
	{
		reportico_report::begin_page();

		$this->debug("PDF Begin Page\n");

        $this->page_number++;
		$this->document->AddPage($this->orientations[$this->orientation]);

		$font = $this->document->SetFont($this->fontName);
		$font = $this->document->SetFontSize($this->vsize);
		$this->set_position($this->abs_left_margin, $this->abs_top_margin);
        $this->current_line_start_y = $this->document->GetY();

		reportico_report::page_headers();
		$this->end_line();
		$this->end_line();
	}

	function finish_page()
	{
		$this->debug("Finish Page");
		$this->page_footers();
		//$this->document->pdf_end_page($this->document);
	}

	function publish()
	{
		reportico_report::publish();
		$this->debug("Publish PDF");
	}

	function format_page_header(&$header)
	{
		$startcol = $header->get_attribute("ColumnStartPDF");
		$tw = $this->abs_paging_width($startcol);
		if ( !$tw )
		    $tw = $this->abs_left_margin;

		$wd = $header->get_attribute("ColumnWidthPDF");
		if ( !$wd )
			if ( $this->abs_right_margin > $tw )
				$wd = $this->abs_right_margin - $tw;
			else
				$wd = "100%";
		$wd = $this->abs_paging_width($wd);

		$just = $this->justifys[$header->derive_attribute( "justify",  "left")];

		$y = $this->abs_top_margin + ( $this->vsize * ( $header->line - 1 ) );
		$this->set_position($tw,$y);
		
		$tx = $this->reportico_string_to_php(reportico_assignment::reportico_meta_sql_criteria($this->query, $header->text));
		$this->draw_cell($wd, $this->vsize, $tx, "PBF", 0, $just );
		$this->end_line();

		return;
	}

	function format_page_footer(&$footer)
	{
		$startcol = $footer->get_attribute("ColumnStartPDF");
		$tw = $this->abs_paging_width($startcol);
		if ( !$tw )
			$tw = $this->abs_left_margin;

		$wd = $footer->get_attribute("ColumnWidthPDF");
		if ( !$wd )
			if ( $this->abs_right_margin > $tw )
				$wd = $this->abs_right_margin - $tw;
			else
				$wd = "100%";
		$wd = $this->abs_paging_width($wd);

		$just = $this->justifys[$footer->derive_attribute( "justify",  "left")];

		$y = $this->abs_bottom_margin - ( $this->vsize * $footer->line );
		$this->set_position($tw, $y);
		//$tx = $this->reportico_string_to_php($footer->text);
		$tx = $this->reportico_string_to_php(reportico_assignment::reportico_meta_sql_criteria($this->query, $footer->text));
		$this->draw_cell($wd, $this->vsize, $tx, "PBF", 0, $just);
		$this->end_line();

		return;
	}

	function format_format($in_value, $format)
	{
		switch($in_value)
		{
			case "blankline" :
				//$this->end_line();
				break;

			case "solidline" :
				$y = $this->document->GetY();
				//$this->document->Line($this->abs_left_margin, $y, $this->abs_page_width - $this->abs_right_margin, $y);
				//$this->set_position($this->abs_right_margin, $y);
				$this->end_line();
				break;

			case "newpage" :
				$this->finish_page();
				$this->begin_page();
				break;


			default :
				$this->end_line();
				break;
				
		}	
	}



}

// -----------------------------------------------------------------------------
// Class reportico_report_soap_template
// -----------------------------------------------------------------------------
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


// -----------------------------------------------------------------------------
// Class reportico_report_html_template
// -----------------------------------------------------------------------------
class reportico_report_html_template extends reportico_report
{
	var	$abs_top_margin;
	var	$abs_bottom_margin;
	var	$abs_left_margin;
	var	$abs_right_margin;
	var	$graph_session_placeholder = 0;
	
	function __construct ()
	{
		return;
	}

	function start ()
	{
		reportico_report::start();

		$this->debug("HTML Start **");

		//pdf_set_info($this->document,'Creator', 'God');
		//pdf_set_info($this->document,'Author', 'Peter');
		//pdf_set_info($this->document,'Title', 'The Title');

		$this->page_line_count = 0;
		$this->abs_top_margin = $this->abs_paging_height($this->get_attribute("TopMargin"));
		$this->abs_bottom_margin = $this->abs_paging_height($this->get_attribute("BottomMargin"));
		$this->abs_right_margin = $this->abs_paging_height($this->get_attribute("RightMargin"));
		$this->abs_left_margin = $this->abs_paging_height($this->get_attribute("LeftMargin"));
	}

	function finish ()
	{
		reportico_report::finish();
		$this->debug("HTML End **");

		if ( $this->line_count < 1 )
		{
			$title = $this->query->derive_attribute("ReportTitle", "Unknown");
			$this->text .= '<H1 class="swRepTitle">'.sw_translate($title).'</H1>';
			$forward = session_request_item('forward_url_get_parameters', '');
			if ( $forward )
				$forward .= "&";

            // In printable html mode dont show back box
		    if ( !get_request_item("printable_html") )
            {
                // Show Go Back Button ( if user is not in "SINGLE REPORT RUN " )
                if ( !$this->query->access_mode || ( $this->query->access_mode != "REPORTOUTPUT" )  )
                {
			        $this->text .= '<div class="swRepBackBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'execute_mode=PREPARE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_BACK").'">&nbsp;</a></div>';
                }
		        if ( get_reportico_session_param("show_refresh_button") )
			        $this->text .= '<div class="swRepRefreshBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'refreshReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_REFRESH").'">&nbsp;</a></div>';
            }
            else
            {
		        $this->text .= '<div class="swRepPrintBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'printReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_PRINT").'">'.template_xlate("GO_PRINT").'</a></div>';
            }

			$this->text .= '<div class="swRepNoRows">'.template_xlate("NO_DATA_FOUND").'</div>';
		}

		if ( $this->report_file )
		{
			$this->debug("Saved to $this->report_file");
		}
		else
		{
			$this->debug("No html file specified !!!");
			$buf = "";
			$len = strlen($buf) + 1;
	
			print($buf);
		}

        if ( $this->page_started )
		    $this->text .= "</TBODY></TABLE>";
        $this->page_started = false;
	}

	function abs_paging_height($height_string)
	{
		$height = (int)$height_string;
		if ( strstr($height_string, "%" ) )
		{
			$height = (int)
				( $this->page_height * $height_string ) / 100;
		}

		return $height;
	}

	function abs_paging_width($width_string)
	{
		$width = (int)$width_string;
		if ( strstr($width_string, "%" ) )
		{
			$width = (int)
				( $this->page_width * $width_string ) / 100;
		}

		return $width;
	}

	function format_column_header(& $column_item)	//HTML
	{

		if ( $this->body_display != "show" )
			return;

		if ( !get_reportico_session_param("target_show_column_headers") )
			return;

		if ( !$this->show_column_header($column_item) )
				return;

		// Create sensible column header label from column name
		$padstring = column_name_to_label($column_item->query_name);
		$padstring = $column_item->derive_attribute( "column_title",  $padstring);
		$padstring = sw_translate($padstring);

        $colstyles = array();
		$cw = $column_item->derive_attribute( "ColumnWidthHTML",  false);
		$just = $column_item->derive_attribute( "justify", false);
        if ( $cw ) $colstyles["width"] = $cw;
        if ( $just ) $colstyles["text-align"] = $just;

	    $this->text .= '<TH '.$this->get_style_tags($colstyles, $this->query->output_header_styles).'>';
        $this->text .= $padstring;
        $this->text .= "</TH>";
	}

	function format_column(& $column_item)
	{
		if ( $this->body_display != "show" )
			return;

		if ( !$this->show_column_header($column_item) )
        {
				return;
        }

		$padstring =& $column_item->column_value;

        $colstyles = array();
		$cw = $column_item->derive_attribute( "ColumnWidthHTML",  false);
		$just = $column_item->derive_attribute( "justify", false);
        if ( $cw ) $colstyles["width"] = $cw;
        if ( $just ) $colstyles["text-align"] = $just;

		$this->text .= '<TD '.$this->get_style_tags($colstyles, $column_item->output_cell_styles, $this->query->output_allcell_styles).'>';
        if ( $column_item->output_images )
           $padstring = $this->format_images ($column_item->output_images);

        if ( $column_item->output_hyperlinks )
            $this->text .= $this->format_hyperlinks ($column_item->output_hyperlinks, $padstring);
        else
            $this->text .= $padstring;
        $this->text .= "</TD>";
	}

    function format_images ( $image )
    {
        $txt = '<img src="'.$image["image"].'" alt="" ';
        if ( isset($image["height"]) && $image["height"] ) $txt .= ' height="'.$image["height"].'"';
        if ( isset($image["width"]) && $image["width"] ) $txt .= ' width="'.$image["width"].'"'; 
        $txt .= " /> ";
        return $txt;
    }
    
    function format_hyperlinks ( $hyperlinks, $padstring )
    {
        $open = "";
        if ( $hyperlinks["open_in_new"] )
            $open = " target=\"_blank\"";

        // Work out the drilldown url ...
        $url = $hyperlinks["url"];

        // Add any application specific url params
        if ( $hyperlinks["is_drilldown"] )
        {
            if ( session_request_item('forward_url_get_parameters', '') )
                $url .= "&".session_request_item('forward_url_get_parameters', '');

            // Add drilldown namespace normally specified in frameworks
            $url .= '&clear_session=1&reportico_session_name=NS_drilldown';
        }


        if ( $hyperlinks["label"] == "<SELF>" )
            $txt = '<a href="'.$url.'"'.$open.'>'.$padstring.'</a>';
        else
            $txt = '<a href="'.$url.'"'.$open.'>'.$hyperlinks["label"].'</a>';
        return $txt;
    }
    
    function get_style_tags ( $styleset, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";

        if ( $grandparent_styleset && is_array($grandparent_styleset) )
            foreach ( $grandparent_styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v;";
            }

        if ( $parent_styleset && is_array($parent_styleset) )
            foreach ( $parent_styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v;";
            }

        if ( $styleset && is_array($styleset) )
            foreach ( $styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v;";
            }

        if ( $outtext )
            $outtext .= "\"";
        return $outtext;
    }

	function format_format($in_value, $format)
	{
		switch($in_value)
		{
			case "blankline" :
				//$this->text .= "<TR><TD><br></TD></TR>";
				break;

			case "solidline" :
				$this->text .= '<TR><TD colspan="10"><hr style="width:100%;" size="2"/></TD>';
				break;

			case "newpage" :
		        //$this->text .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->get_style_tags($this->query->output_page_styles).'>';
                $this->page_started = true;
				break;

			default :
				$this->text .= "<TR><TD>Unknown Format $in_value</TD></TR>";
				break;
				
		}	
	}

	function format_headers()
	{
        if ( !$this->page_started )
        {
		    $this->text .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->get_style_tags($this->query->output_page_styles).'>';
            $this->page_started = true;
        }

        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

		if ( $this->body_display != "show" )
			return;
		$this->text .="<thead><tr class='swRepColHdrRow'>";
		foreach ( $this->query->display_order_set["column"] as $w )
			$this->format_column_header($w);
		$this->text .="</tr></thead><tbody>";
	}

	function format_group_header_start($throw_page = false)
	{
        // Ensure group box spans to end of table
        $spanct = 0;
		foreach ( $this->columns as $col )
		    if ( $this->show_column_header($col) )
				$spanct++;

		//$this->text .= "<TR class=swRepDatRow>";
		//$this->text .= "<TD class=swRepDatVal colspan=\"".$spanct."\">";
        if ( $throw_page )
		    $this->text .= '<TABLE class="swRepGrpHdrBox swNewPage" cellspacing="0">';
        else
		    $this->text .= '<TABLE class="swRepGrpHdrBox" cellspacing="0">';
	}

	function format_group_header(&$col) // HTML
	{
		$this->text .= '<TR class="swRepGrpHdrRow">';
		$this->text .= '<TD class="swRepGrpHdrLbl" '.$this->get_style_tags($this->query->output_group_header_label_styles).'>';
		$qn = get_query_column($col->query_name, $this->query->columns ) ;

		$padstring = $qn->column_value;

		// Create sensible group header label from column name
		$tempstring = column_name_to_label($col->query_name);
		$tempstring = $col->derive_attribute( "column_title",  $tempstring);
		$tempstring = sw_translate($col->derive_attribute("column_title",  $tempstring));

		$this->text .= sw_translate($col->derive_attribute("group_header_label",  $tempstring));
		$this->text .= "</TD>";
		$this->text .= '<TD class="swRepGrpHdrDat" '.$this->get_style_tags($this->query->output_group_header_value_styles).'>';
		$this->text .= "$padstring";
		$this->text .= "</TD>";
		$this->text .= "</TR>";
	}

	function format_group_header_end()
	{
		$this->text .= "</TABLE>";
        $this->page_started = false;
	}



	function begin_line()
	{
		if ( $this->body_display != "show" )
			return;
		$this->text .= '<TR class="swRepResultLine" '.$this->get_style_tags($this->query->output_row_styles).'>';
	}

	function plot_graph(&$graph, $graph_ct = false)
	{
        if ( $graph_ct == 0 )
        {
            if ( $this->page_started )
		        $this->text .= '</TBODY></TABLE>';
            $this->page_started = false;
        }
		$this->graph_session_placeholder++;
		$graph->width_actual = check_for_default("GraphWidth", $graph->width);
		$graph->height_actual = check_for_default("GraphHeight", $graph->height);
		$graph->title_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->title, true);
		$graph->xtitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->xtitle, true);
		$graph->ytitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->ytitle, true);
		$url_string = $graph->generate_url_params("HTML", $this->graph_session_placeholder);
		$this->text .= '<div class="swRepResultGraph">';
		if ( $url_string )
			$this->text .= $url_string;
		$this->text .= '</div>';
	}
	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first=false) // HTML
	{
		if ( !get_reportico_session_param("target_show_group_trailers") )
			return;

		$just = $trailer_col->derive_attribute( "justify", false);
        if ( $just && $just != "left" ) 
                $this->query->output_group_trailer_styles["text-align"] = $just;
        else
                $this->query->output_group_trailer_styles["text-align"] = "left";

		if ( $value_col )
		    if ( $trailer_first )
			    $this->text .= '<TD class="swRepGrpTlrDat1st" '.$this->get_style_tags($this->query->output_group_trailer_styles).'>';
		    else
			    $this->text .= '<TD class="swRepGrpTlrDat" '.$this->get_style_tags($this->query->output_group_trailer_styles).'>';
        else
		    if ( $trailer_first )
			    $this->text .= '<TD class="swRepGrpTlrDat1st">';
		    else
			    $this->text .= '<TD class="swRepGrpTlrDat">';
		if ( $value_col )
		{
			$group_label = $value_col->get_attribute("group_trailer_label" );
			if ( !$group_label )
				$group_label = $value_col->get_attribute("column_title" );
			if ( !$group_label )
			{
				$group_label = $value_col->query_name;
				$group_label = str_replace("_", " ", $group_label);
				$group_label = ucwords(strtolower($group_label));
			}
			$group_label = sw_translate($group_label);
			$padstring = $value_col->old_column_value;
            if ( $value_col->output_images )
                        $padstring = $this->format_images ($value_col->output_images);

			if ( $group_label == "BLANK" )
				$this->text .= $padstring;
			else
				$this->text .= $group_label." ".$padstring;
		}
		else
			$this->text .= "&nbsp;";
		$this->text .= "</TD>";
	}

	function format_group_trailer_start($first=false)
	{
		if ( $first )
        {
            $this->text .= "</TBODY><TFOOT>";
			$this->text .= '<TR class="swRepGrpTlrRow1st">';
        }
		else
			$this->text .= '<TR class="swRepGrpTlrRow">';
	}

	function format_group_trailer_end()
	{

		$this->text .= "</TR>";
        if ( $this->page_started )
        {
		    $this->text .= "</TFOOT></TABLE>";
        }
        $this->page_started = false;
	}


	function end_line()
	{
		if ( $this->body_display != "show" )
			return;
		$this->text .= "</TR>";
	}

	function each_line($val) // HTML
	{

		reportico_report::each_line($val);

        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
        {
            if ( !$this->page_started )
            {
		        $formpagethrow = $this->query->get_attribute("formBetweenRows");
                switch ( $formpagethrow )
                {
                    case "newpage":
		                if ( $this->page_line_count > 0 ) 
                            $formpagethrow = "swRepPageFormLine swNewPage";
                        else
                            $formpagethrow = "swRepPageFormLine";
                        break;
                    case "blankline":
                        $formpagethrow = "swRepPageFormBlank";
                        break;
                    case "solidline":
                        $formpagethrow = "swRepPageFormLine";
                        break;
                }

	            $this->text .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage '.$formpagethrow.'" '.$this->get_style_tags($this->query->output_page_styles).'>';
                $this->page_started = true;
            }
		    foreach ( $this->query->groups as $val )
            {
			    for ($i = 0; $i < count($val->headers); $i++ )
			    {
				    $col =& $val->headers[$i];
				    $this->format_group_header($col);
			    }
            }
            foreach ( $this->query->display_order_set["column"] as $k => $w )
		    {
		        if ( $w->attributes["column_display"] != "show")
					    continue;
					$this->format_group_header($w);
            }
		    $this->page_line_count++;
		    $this->line_count++;
            $this->text .= '</TABLE>';
            $this->page_started = false;
            return;
        }

		if ( $this->page_line_count == 1 )
		{
			//$this->text .="<tr class='swPrpCritLine'>";
			//foreach ( $this->columns as $col )
				//$this->format_column_header($col);
			//$this->text .="</tr>";
		}

		//foreach ( $this->columns as $col )
		if ( $this->body_display == "show" && get_reportico_session_param("target_show_detail") )
        {
		    $this->begin_line();
            if ( !$this->page_started )
            {
		        $this->text .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->get_style_tags($this->query->output_page_styles).'>';
                $this->page_started = true;
            }
			foreach ( $this->query->display_order_set["column"] as $col )
				$this->format_column($col);
		    $this->end_line();
        }

		//if ( $y < $this->abs_bottom_margin )
		//{
			//$this->finish_page();
			//$this->begin_page();
		//}


	}

	function page_template()
	{
		$this->debug("Page Template");
	}

	function begin_page()
	{
		reportico_report::begin_page();

		$this->debug("HTML Begin Page\n");


		$title = $this->query->derive_attribute("ReportTitle", "Unknown");
        if ( $this->query->output_template_parameters["show_hide_report_output_title"] != "hide" )
		    $this->text .= '<H1 class="swRepTitle">'.sw_translate($title).'</H1>';
		$forward = session_request_item('forward_url_get_parameters', '');
		if ( $forward )
			$forward .= "&";

	    if ( !get_request_item("printable_html") )
        {
            if ( !$this->query->access_mode || ( $this->query->access_mode != "REPORTOUTPUT" )  )
            {
			    $this->text .= '<div class="swRepBackBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'execute_mode=PREPARE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_BACK").'">&nbsp;</a></div>';
            }
	        if ( get_reportico_session_param("show_refresh_button") )
		        $this->text .= '<div class="swRepRefreshBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'refreshReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_REFRESH").'">&nbsp;</a></div>';
        }
        else
        {
	        $this->text .= '<div class="swRepPrintBox"><a class="swLinkMenu" href="'.$this->query->get_action_url().'?'.$forward.'printReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_PRINT").'">'.'&nbsp;'.'</a></div>';
        }

	}

	function before_format_criteria_selection()
	{
		$this->text .= '<TABLE class="swRepCriteria">';
	}

	function format_criteria_selection($label, $value)
	{
		$this->text .= '<TR class="swRepGrpHdrRow">';
		$this->text .= '<TD class="swRepGrpHdrLbl">';
		$this->text .= $label;
		$this->text .= "</TD>";
		$this->text .= '<TD class="swRepGrpHdrDat">';
		$this->text .= $value;
		$this->text .= "</TD>";
		$this->text .= "</TR>";

	}

	function after_format_criteria_selection()
	{
		$this->text .= "</TABLE>";
	}


	function finish_page()
	{
		$this->debug("HTML Finish Page");
		//pdf_end_page($this->document);
	}

	function publish()
	{
		reportico_report::publish();
		$this->debug("Publish HTML");
	}

	function format_page_header(&$header)
	{
		$just = strtolower($header->get_attribute("justify"));

		$this->text .= "<TR>";
		$this->text .= '<TD colspan="10" justify="'.$just.'">';
		$this->text .=($header->text);
		$this->text .= "</TD>";
		$this->text .= "</TR>";

		return;
	}

	function format_page_footer(&$header)
	{
		$just = strtolower($header->get_attribute("justify"));

		$this->text .= "<TR>";
		$this->text .= '<TD colspan="10" justify="'.$just.'">';
		$this->text .=($header->text);
		$this->text .= "</TD>";
		$this->text .= "</TR>";

		return;
	}


}

// -----------------------------------------------------------------------------
// Class reportico_report_csv
// -----------------------------------------------------------------------------
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

		// Start the web page
		if ( ob_get_length() > 0 )
		    ob_clean();	
		header("Content-type: application/octet-stream");

        $attachfile = "reportico.csv";
        if ( $this->reporttitle )
            $attachfile = preg_replace("/ /", "_", $this->reporttitle.".csv");
		header('Content-Disposition: attachment; filename='.$attachfile);
		header("Pragma: no-cache");
		header("Expires: 0");

		$this->debug("Excel Begin Page\n");

		echo '"'."$this->reporttitle".'"';
		echo "\n";
	}

	function finish ()
	{
		reportico_report::finish();
		//if ( $this->line_count < 1 )
		//{
            //// No CSV data found just return
            //return;
		//}

		if ( $this->report_file )
		{
			$this->debug("Saved to $this->report_file");
		}
		else
		{
			$this->debug("No csv file specified !!!");
			$buf = "";
			$len = strlen($buf) + 1;
	
			print($buf);
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

		echo '"'.$padstring.'"'.",";
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
        echo "\"".$output."\",";

	}

	function each_line($val)
	{
		reportico_report::each_line($val);

		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
                foreach ( $this->query->groups as $name => $group)
		{
			if ( count($group->headers) > 0  )
			foreach ($group->headers as $gphk => $col )
			{
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
				$padstring = $qn->column_value;
				echo "\"".$padstring."\"";
				echo ",";
			}
		}
				

		//foreach ( $this->columns as $col )
		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$this->format_column($col);
       		}
		echo "\n";

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
		echo "\"".$label."\"";
		echo ",";
		echo "\"".$value."\"";
		echo "\n";
	}

	function after_format_criteria_selection()
	{
		echo "\n";
	}

	function finish_page()
	{
		$this->debug("Excel Finish Page");
		//pdf_end_page($this->document);
		die;
	}

	function format_headers()
	{
		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
        foreach ( $this->query->groups as $name => $group)
		{
			for ($i = 0; $i < count($group->headers); $i++ )
			{
				$col =& $group->headers[$i];
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
				$tempstring = str_replace("_", " ", $col->query_name);
				$tempstring = ucwords(strtolower($tempstring));
				echo "\"".sw_translate($col->derive_attribute("column_title",  $tempstring))."\"";
				echo ",";
			}
		}
				
		foreach ( $this->query->display_order_set["column"] as $w )
			$this->format_column_header($w);
		echo "\n";
	}

	function format_group_header(&$col) // CSV
	{
		// Excel requires group headers are printed as the first columns in the spreadsheet against
		// the detail. 
		return;

		$qn = get_query_column($col->query_name, $this->query->columns ) ;
		$padstring = $qn->column_value;
		$tempstring = str_replace("_", " ", $col->query_name);
		$tempstring = ucwords(strtolower($tempstring));
		echo sw_translate($col->derive_attribute("column_title",  $tempstring));
		echo ": ";
		echo "$padstring";
		echo "\n";
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
				echo ",";
			}
		}
	}

	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first = false)
	{
		if ( $value_col )
		{
			$group_label = $value_col->get_attribute("group_trailer_label" );
			if ( !$group_label )
				$group_label = $value_col->get_attribute("column_title" );
			if ( !$group_label )
			{
				$group_label = $value_col->query_name;
				$group_label = str_replace("_", " ", $group_label);
				$group_label = ucwords(strtolower($group_label));
			}
			$group_label = sw_translate($group_label);
			$padstring = $value_col->old_column_value;
			echo $group_label.":".$padstring;
		}
		echo ",";
	}

	function end_line()
	{
		echo "\n";
	}


	function publish()
	{
		reportico_report::publish();
		$this->debug("Publish Excel");
	}


}

/**
 * Class reportico_report_json
 *
 * Reports out in json format
 */
class reportico_report_json extends reportico_report
{
	var	$record_template;
	var	$results = array();
	var	$line_ct = 0;
	
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
			"title" => $title,
			"displaylike" => array(),
			"data" => array()
			);

		$ct=0;
	}

	function finish ()
	{
		reportico_report::finish();
		$len = strlen(json_encode($this->results));

		if ( ob_get_length() > 0 )
            ob_end_clean();

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: application/json');

		header("Content-Length: $len");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Disposition: attachment; filename=reportico.json');

		echo json_encode($this->results);
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

		// Set the values for the fields in the record
		$this->results["data"][$this->line_ct] = array();

        if ( $this->line_ct == 0 )
        {
                $qn = get_query_column("golap", $this->columns ) ;
                if ( $qn )
                        {
                    $arr = explode ( ",", $qn->column_value );
                    foreach ( $arr as $k => $v )
                    {
                        $arr1 = explode ( "=", $v );
                        $this->results["displaylike"][$arr1[0]] = $arr1[1];
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

		    $disp = $col->derive_attribute( "column_display",  "show" );
		    if ( $disp == "hide" ) continue;

			$this->results["data"][$this->line_ct][$coltitle] = $qn->column_value;
   		}
		$this->line_ct++;
		
	}
}

/**
 * Class reportico_report_xml
 *
 * Reports out in xml format
 */
class reportico_report_xml extends reportico_report
{
	var	$record_template;
	var	$results = array();
	var	$line_ct = 0;
	
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
			"title" => $title,
			"timestamp" => date("Y-m-d\TH:i:s\Z", time()),
			"displaylike" => array(),
			"data" => array()
			);

		$ct=0;
	}

	function finish ()
	{
		reportico_report::finish();
		$xmlroot =  preg_replace("/\.xml$/", "", $this->query->xmloutfile);
		$xml =  $this->arrayToXML($this->results,  new SimpleXMLElement('<'.$xmlroot.'/>'))->asXml();
        	$len = strlen($xml);

		if ( ob_get_length() > 0 )
            ob_end_clean();

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: text/xml');

		header("Content-Length: $len");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Disposition: inline; filename=reportico.xml');
		
		echo $xml;

		die;
	}

	function arrayToXML1($root_element_name,$ar)
	{
    		$xml = new SimpleXMLElement("<?xml version=\"1.0\"?><".$root_element_name."></".$root_element_name.">");
    		$f = create_function('$f,$c,$a','
            		foreach($a as $k=>$v) {
                		if(is_array($v)) {
                    		$ch=$c->addChild($k);
                    		$f($f,$ch,$v);
                		} else {
                    		$c->addChild($k,$v);
                		}
            		}');
    		$f($f,$xml,$ar);
    		return $xml->asXML();
	}
	function arrayToXml(array $arr, SimpleXMLElement $xml)
	{
    		foreach ($arr as $k => $v) {
				if ( is_array ($v) && count($v) == 0 )
					continue;
				if ( preg_match("/^dataline_/", $k ) ) $k = "dataline";
        		    is_array($v) ? $this->arrayToXml($v, $xml->addChild($k)) : $xml->addChild($k, $v);
    		}
    		return $xml;
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

		// Set the values for the fields in the record
		$linekey = "dataline_".($this->line_ct + 1);
		$this->results["data"][$linekey] = array();

       	if ( $this->line_ct == 0 )
       	{
               $qn = get_query_column("golap", $this->columns ) ;
               if ( $qn )
                       {
                   $arr = explode ( ",", $qn->column_value );
                   foreach ( $arr as $k => $v )
                   {
                       $arr1 = explode ( "=", $v );
                       $this->results["displaylike"][$arr1[0]] = $arr1[1];
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

		    $disp = $col->derive_attribute( "column_display",  "show" );
		    if ( $disp == "hide" ) continue;

			$this->results["data"][$linekey][preg_replace("/ /", "", $coltitle)] = $qn->column_value;

       		}
		$this->line_ct++;
		
	}

}

/**
 * Class reportico_report_jquerybuilder
 *
 * Reports out in jquerybuilder format
 */
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

function derive_jquerygrid_rep_params ($report_name, $parameter, $default)
{
    $param = "SW_JQDEF_".$report_name."_".$parameter;

    if ( defined($param) )
    {
        return constant($param);
    }
    else
        return $default;
}
function derive_jquerygrid_col_params ($report_name, $column_name, $parameter, $default)
{
    $param = "SW_JQDEF_".$report_name."_".$column_name."_".$parameter;

    if ( defined($param) )
    {
        return constant($param);
    }
    else
        return $default;
}

?>
