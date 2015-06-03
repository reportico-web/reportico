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

 * File:        reportico_report.php
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
    var $reportfilename;
	var	$body_display = "show";
	var	$graph_display = "show";
	var	$page_started = false;
	var	$text = "";
    var $inOverflow = false;
    var $any_trailers = false;
    var $any_custom_trailers = false;
    var $draw_mode = "DRAW";

    var $detail_started = false;

	var $attributes = array (
		"TopMargin" => "4%",
		"BottomMargin" => "2%",
		"RightMargin" => "5%",
		"LeftMargin" => "5%",
		"BodyStart" => "10%",
		"BodyEnd" => "10%",
		"ReportTitle" => "Set Report Title"
		);

    var $page_styles_started = false;


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

		$out_string = preg_replace('/{page}/i', "$this->page_count", 
			$out_string);

		$out_string = preg_replace('/{#page}/i', "$this->page_count", 
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
		$this->body_display = "show";
		if ( get_request_item("hide_output_text") )
            $this->body_display = false;
		$this->graph_display = "show";
		if ( get_request_item("hide_output_graph") )
            $this->graph_display = false;
		$this->page_line_count = 0;
		$this->line_count = 0;
		$this->page_count = 0;
		$this->debug("Base Start **");
		$this->reporttitle = $this->query->derive_attribute("ReportTitle", "Set Report Title");
		$this->reportfilename = $this->reporttitle;
		$pos = 5;

        // Reorganise group trailers so they are as an array
        foreach ( $this->query->groups as $group )
        {
            $group->organise_trailers_by_display_column();
        }
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

	function format_criteria_selection($label, $value)
	{
		return;
	}

    function format_custom_trailer(&$trailer_col, &$value_col) // PDF
	{
		return;
	}

	function custom_trailer_wrappers()
	{
		return;
	}
    
	function end_of_page_block()
	{
		return;
	}


	function format_criteria_selection_set()
	{
        $is_criteria = false;
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
            {
                $is_criteria = true;
            }
        }

		if ( get_reportico_session_param("target_show_criteria") && $is_criteria )
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
                if (
                    ( $ph->get_attribute("ShowInHTML") == "yes" && get_class($this) == "reportico_report_html" )
                    || ( $ph->get_attribute("ShowInPDF")  == "yes"&& $this->query->target_format == "PDF" )
                    )
                {
				    $this->format_page_header($ph);
                }
		}
		$this->format_page_header_end();
	}

	function page_footers()
	{
		$this->format_page_footer_start();
		foreach($this->query->page_footers as $ph)
		{
                if (
                    ( $ph->get_attribute("ShowInHTML") == "yes" && get_class($this) == "reportico_report_html" )
                    || ( $ph->get_attribute("ShowInPDF")  == "yes"&& $this->query->target_format == "PDF" )
                    )
                {
				    $this->format_page_footer($ph);
                }
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

	function format_report_detail_start()
	{
        $this->detail_started = true;
		return;
	}

	function format_report_detail_end()
	{
        $this->detail_started = false;
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
        return;
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
//echo "each line $this->inOverflow<BR>";
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
        $this->any_trailers = false;
        $this->any_custom_trailers = false;

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

            $rct = 0;

            // Work out which groups have triggered trailer by passing
            // through highest to lowest level .. group changes at level cause change at lower
            // also last line does too!!
            $uppergroupchanged = false;
			reset($this->query->groups);
			do
			{
				$group = current($this->query->groups);
                $group->change_triggered = false;
				if ( $uppergroupchanged || $this->query->changed($group->group_name) || $this->last_line) 
                {
                    $group->change_triggered = true;
                    $uppergroupchanged = true;
			    }
			}
			while( next($this->query->groups) );

			end($this->query->groups);
			do
			{
				$group = current($this->query->groups);
				if ( $group->change_triggered )
				{
                    if ( $rct == 1 )
		                $this->format_report_detail_end();
                    $rct++;
                    $group_changed = true;
					$lev = 0;
					$tolev = 0;

					while ( $lev < $group->max_level )
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
                                $number_group_rows = 0;
                        for ( $passno = 1; $passno <= 2; $passno++ )
                        {
                            if (  $this->query->target_format == "PDF" )
                            {
                                if ( $passno == 1 ) $this->draw_mode = "CALCULATE";
                                if ( $passno == 2 ) 
                                {
                                    $this->draw_mode = "DRAW";
                                    $this->unapply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                    $this->check_page_overflow();
                                    $this->apply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                }
                            }
                            else
                            {
                                if ( $passno == 2 ) break;
                            }
                            // Column Trailers
                            $linedrawn = false;
                            if ( $this->draw_mode == "DRAW" && $number_group_rows == 0 )
                            {
                                $linedrawn = true;
                            }
                            else
                                $number_group_rows = 0;
                            foreach ( $this->query->display_order_set["column"] as $w )
                            {
                                if ( !$this->show_column_header($w) )
                                        continue;

                                if ( array_key_exists($w->query_name, $group->trailers_by_column) )
                                {
                                    $number_group_rows++;
                                    if ( count($group->trailers_by_column[$w->query_name]) >= $lev + 1 && !$group->trailers_by_column[$w->query_name][$lev]["GroupTrailerCustom"] )
                                    {
                                        if ( !$linedrawn )
                                        {
                                            $this->unapply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $this->new_report_page_line("3");
                                            $this->apply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $linedrawn = true;
                                        }
                                        $this->format_column_trailer($w, $group->trailers_by_column[$w->query_name][$lev],$trailer_first);
                                    }
                                    else
                                    {
                                        if ( !$linedrawn )
                                        {
                                           $this->unapply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                           $this->new_report_page_line("3");
                                           $this->apply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                           $linedrawn = true;
                                        }
                                        $this->format_column_trailer($w, $junk,$trailer_first);	
                                    }
                                    $this->any_trailers = true;
                                    
                                    if (  $group->max_level > $tolev )
                                    {
                                        $tolev =  $group->max_level;
                                    }
                                }
                                else
                                {
                                    if ( !$linedrawn )
                                    {
                                        $this->unapply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                        $this->new_report_page_line("3");
                                        $this->apply_style_tags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                        $linedrawn = true;
                                    }
                                    $this->format_column_trailer($w, $junk, $trailer_first);	
                                }
                            } // foreach
                        }
                        if (  get_class($this) != "reportico_report_html" )
						    $this->format_group_trailer_end();
						if ( $trailer_first )
							$trailer_first = false;
						$lev++;
						$this->end_line();
					} // while

				}

			}
			while( prev($this->query->groups) );

            if ( $group_changed && get_class($this) == "reportico_report_html" )
            {
                $this->format_group_trailer_end();
            }

            if ( $group_changed && $this->query->target_format == "PDF" )
            {
                $this->end_of_page_block();
            }


            // Custom trailers
			end($this->query->groups);
			do
			{
				$group = current($this->query->groups);
				if ( $this->query->changed($group->group_name) || $this->last_line) 
				{
                    $this->format_group_custom_trailer_start();
                    // In PDF mode all trailer lines must be passed through twice
                    // to allow calculation of line height. Otherwise
                    // Only one pass through
                    for ( $passno = 1; $passno <= 2; $passno++ )
                    {
                        if ( $this->query->target_format == "PDF" )
                        {
                            if ( $passno == 1 ) $this->draw_mode = "CALCULATE";
                            if ( $passno == 2 ) 
                            {
                                $this->draw_mode = "DRAW";
                                $this->check_page_overflow();
                                $this->custom_trailer_wrappers();
                            }
                        }
                        else
                        {
                            if ( $passno == 2 ) break;
                        }
                        // Column Trailers
                        if ( $this->query->target_format == "PDF" )
                        {
                            foreach ($group->trailers_by_column as $kk => $trailer )
                            {
                                foreach ( $trailer as $kk2 => $colgrp )
                                {
                                    $this->format_custom_trailer($w, $colgrp);
                                }
                            } // foreach
                        }
                    }
                    $this->format_group_custom_trailer_end();
                }
			}
			while( prev($this->query->groups) );

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

	function format_group_custom_trailer_start()
	{
			return;
	}

	function format_group_custom_trailer_end()
	{
			return;
	}

	function format_group_trailer_end($last_trailer = false)
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
        //if ( $this->inOverflow ) 
            //return;

        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

        // Work out which groups have triggered trailer by passing
        // through highest to lowest level .. group changes at level cause change at lower
        // also last line does too!!
        $uppergroupchanged = false;
        reset($this->query->groups);
        if ( $this->query->groups )
        do
        {
            $group = current($this->query->groups);
            $group->change_triggered = false;
            if ( $uppergroupchanged || $this->query->changed($group->group_name) || $this->last_line) 
            {
                $group->change_triggered = true;
                $uppergroupchanged = true;
            }
        }
        while( next($this->query->groups) );

		$changect = 0;
		reset($this->query->groups);
		foreach ( $this->query->groups as $name => $group) 
		{
			if ( count($group->headers) > 0 && ( (  $group->group_name == "REPORT_BODY" && $this->line_count == 0 ) || $group->change_triggered) ) 
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
                {
				    for ($i = 0; $i < count($group->headers); $i++ )
				    {
				        $col =& $group->headers[$i]["GroupHeaderColumn"];
				        $custom = $group->headers[$i]["GroupHeaderCustom"];
				        $this->format_group_header($col, $custom);
				    }
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
		    $this->format_report_detail_start();
            if ( $this->query->target_format == "PDF" )
            {
		        $this->column_header_required = true;
            }
            else
		        $this->format_headers();
            $this->page_styles_started = true;
		}
	}

	function format_group_header(&$col, $custom)
	{
		return;
	}

	function format_headers()
	{
			return;
	}

    function new_report_page_line($txt = "")
    {
        return;
    }



}
?>
