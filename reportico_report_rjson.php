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

 * File:        reportico_report_rjson.php
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
 * @license - http://www.gnu.org/licenses/gpl-2.0.rjson GNU/GPL
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */
require_once("reportico_report.php");

class reportico_report_rjson extends reportico_report
{
	var	$graph_session_placeholder = 0;

    var $jar = array(
        "attributes" => array(
                "hide_title" => false,
                "show_refresh_button" => false,
                "show_prepare_button" => false,
                "show_print_button" => false,

                "show_detail" => true,
                "show_graph" => true,
                "show_group_headers" => true,
                "show_group_trailers" => true,
                "show_criteria" => true,

                "column_header_styles" => false,
            ),
        "title" => "Set Report Title",
        "criteria" => array(),
        "groups" => array(),
        "columns" => array(),
        "pages" => array (
            )
        );
	
	function __construct ()
	{
		return;
	}

	function start ()
	{
		reportico_report::start();

		$this->debug("RJSON Start **");
		$this->page_line_count = 0;
	}

	function finish ()
	{
        reportico_report::finish();

		if ( $this->line_count < 1 )
            $this->setup_report_attributes();

        $str = json_encode($this->jar);
        $len = strlen($str);

        if ( ob_get_length() > 0 )
            ob_end_clean();

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: application/json');

        header("Content-Length: $len");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Disposition: attachment; filename=reportico.json');

        echo $str;
        die;


        $this->page_started = false;
	}

	function begin_page()
	{
		reportico_report::begin_page();
        $this->setup_report_attributes();
	}

    function setup_report_attributes()
    {
		$title = $this->query->derive_attribute("ReportTitle", "Unknown");
		$this->jar["title"] .= sw_translate($title);
        if ( $this->query->output_template_parameters["show_hide_report_output_title"] == "hide" )
            $this->jar["attributes"]["hide_title"] = true;

		$forward = session_request_item('forward_url_get_parameters', '');
		if ( $forward )
			$forward .= "&";

        // In printable rjson mode dont show back box
		if ( !get_request_item("printable_rjson") )
        {
            // Show Go Back Button ( if user is not in "SINGLE REPORT RUN " )
            if ( !$this->query->access_mode || ( $this->query->access_mode != "REPORTOUTPUT" )  )
            {
		     $this->jar["attributes"]["show_prepare_button"] .= $this->query->get_action_url().'?'.$forward.'execute_mode=PREPARE&reportico_session_name='.reportico_session_name();
            }
		    if ( get_reportico_session_param("show_refresh_button") )
		     $this->jar["attributes"]["show_refresh_button"] .= $this->query->get_action_url().'?'.$forward.'refreshReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name();
        }
        else
        {
		    $this->jar["attributes"]["show_print_button"] .= $this->query->get_action_url().'?'.$forward.'printReport=1&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name();
        }

	    $this->jar["attributes"]["column_header_styles"] = $this->query->output_header_styles;
	    $this->jar["attributes"]["column_page_styles"] = $this->query->output_page_styles;
	    $this->jar["attributes"]["page_style"] = session_request_item("target_style", "TABLE" );
        $this->setup_columns();
    }

	function setup_columns()
	{
        foreach ( $this->query->groups as $name => $group)
		{
			if ( count($group->headers) > 0  )
			foreach ($group->headers as $gphk => $col )
			{
				$qn = get_query_column($col->query_name, $this->query->columns ) ;
                $this->jar["groups"][$col->query_name] = array();
                $this->jar["groups"][$col->query_name]["group_header"] = true;
			}
		}

		//foreach ( $this->columns as $col )
		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
            if ( !$this->show_column_header($col) )
                    continue;

            // Create sensible column header label from column name
            $label = column_name_to_label($col->query_name);
            $label = $col->derive_attribute( "column_title",  $label);
            $label = sw_translate($label);

            $this->jar["columns"][$col->query_name] = array();
            $this->jar["columns"][$col->query_name]["label"] = $label;

            $colstyles = array();
            $cw = $col->derive_attribute( "ColumnWidthHTML",  false);
            $just = $col->derive_attribute( "justify", false);
            if ( $cw || $just )
            {
                $this->jar["columns"][$col->query_name]["styles"] = array();
                if ( $cw )
                    $this->jar["columns"][$col->query_name]["styles"]["width"] = $cw;
                if ( $just )
                    $this->jar["columns"][$col->query_name]["styles"]["text-align"] = $just;
            }

 		}
    }

	function format_column(& $column_item)
	{
        if ( $this->body_display != "show" )
            return;

        if ( !$this->show_column_header($column_item) )
        {
                return;
        }

        $colstyles = array();
		$cw = $column_item->derive_attribute( "ColumnWidthHTML",  false);
		$just = $column_item->derive_attribute( "justify", false);
        if ( $cw ) $colstyles["width"] = $cw;
        if ( $just ) $colstyles["text-align"] = $just;
		$style  = $this->get_style_tags($colstyles, $column_item->output_cell_styles, $this->query->output_allcell_styles).'>';

        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["style"] = $style;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["images"] = $column_item->output_images;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["hyperlinks"] = $column_item->output_hyperlinks;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["data"][$column_item->query_name] = $column_item->column_value;
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
				//$this->jar[""] .= "<TR><TD><br></TD></TR>";
				break;

			case "solidline" :
				//$this->jar[""] .= '<TR><TD colspan="10"><hr style="width:100%;" size="2"/></TD>';
				break;

			case "newpage" :
		        //$this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->get_style_tags($this->query->output_page_styles).'>';
                $this->page_started = true;
				break;

			default :
				//$this->jar[""] .= "<TR><TD>Unknown Format $in_value</TD></TR>";
				break;
				
		}	
	}

	function format_headers()
	{
        return;
	}

	function format_group_header_start($throw_page = false)
	{
        //$this->jar["pages"][$this->page_count]["groups"][] = array();
        $this->jar["pages"][$this->page_count]["groups"] = array();
        $this->jar["pages"][$this->page_count]["groups"]["headers"] = array();
        $this->jar["pages"][$this->page_count]["groups"]["trailers"] = array();
        return;
	}

	function format_group_header(&$col) // HTML
	{
		$qn = get_query_column($col->query_name, $this->query->columns ) ;

		// Create sensible group header label from column name
		$tempstring = column_name_to_label($col->query_name);
		$tempstring = $col->derive_attribute( "column_title",  $tempstring);
		$tempstring = sw_translate($col->derive_attribute("column_title",  $tempstring));

        if ( !isset ($this->jar["pages"][$this->page_count]["groups"]["headers"]) )
            $this->jar["pages"][$this->page_count]["groups"]["headers"] = array();
        $hct = count($this->jar["pages"][$this->page_count]["groups"]["headers"]) - 1;
        $this->jar["pages"][$this->page_count]["groups"]["headers"][$hct] = array(
                        "label" => $tempstring,
                        "labelstyle" => $this->query->output_group_header_label_styles,
                        "value" => $qn->column_value,
                        "valuestyle" => $this->query->output_group_header_value_styles
                        );
	}

	function format_group_header_end()
	{
		return;
	}

	function begin_line()
	{
		if ( $this->body_display != "show" )
			return;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["data"] = array();
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["style"] = $this->query->output_row_styles;
	}

	function plot_graph(&$graph, $graph_ct = false)
	{
        return;

        if ( $graph_ct == 0 )
        {
            if ( $this->page_started )
		        $this->jar[""] .= '</TBODY></TABLE>';
            $this->page_started = false;
        }
		$this->graph_session_placeholder++;
		$graph->width_actual = check_for_default("GraphWidth", $graph->width);
		$graph->height_actual = check_for_default("GraphHeight", $graph->height);
		$graph->title_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->title, true);
		$graph->xtitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->xtitle, true);
		$graph->ytitle_actual = reportico_assignment::reportico_meta_sql_criteria($this->query, $graph->ytitle, true);
		$url_string = $graph->generate_url_params("HTML", $this->graph_session_placeholder);
		$this->jar[""] .= '<div class="swRepResultGraph">';
		if ( $url_string )
			$this->jar[""] .= $url_string;
		$this->jar[""] .= '</div>';
	}
	function format_column_trailer(&$trailer_col, &$value_col, $trailer_first=false) // HTML
	{
		$just = $trailer_col->derive_attribute( "justify", false);
        if ( $just && $just != "left" ) 
                $this->query->output_group_trailer_styles["text-align"] = $just;
        else
                $this->query->output_group_trailer_styles["text-align"] = "left";

		if ( !get_reportico_session_param("target_show_group_trailers") )
			return;

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
			    $group_label == "";

            if ( !isset ($this->jar["pages"][$this->page_count]["groups"]["trailers"]) )
                $this->jar["pages"][$this->page_count]["groups"]["trailers"] = array();
            $hct = count($this->jar["pages"][$this->page_count]["groups"]["trailers"]) - 1;
            $this->jar["pages"][$this->page_count]["groups"]["trailers"][$trailer_col->query_name][] = array(
                            "label" => $group_label,
                            "labelstyle" => $this->query->output_group_trailer_styles,
                            "value" => $value_col->column_value,
                            "valuestyle" => $this->query->output_group_trailer_styles
                            );
		}
		//else
			//$this->jar[""] .= "&nbsp;";
	}

	function format_group_trailer_start($first=false)
	{
        return;
	}

	function format_group_trailer_end()
	{
        return;
		$this->jar[""] .= "</TR>";
        if ( $this->page_started )
        {
		    $this->jar[""] .= "</TFOOT></TABLE>";
        }
        $this->page_started = false;
	}


	function end_line()
	{
        return;
		if ( $this->body_display != "show" )
			return;
		$this->jar[""] .= "</TR>";
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

	            // PPP $this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage '.$formpagethrow.'" '.$this->get_style_tags($this->query->output_page_styles).'>';
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
            //$this->jar[""] .= '</TABLE>';
            $this->page_started = false;
            return;
        }

		if ( $this->page_line_count == 1 )
		{
			//$this->jar[""] .="<tr class='swPrpCritLine'>";
			//foreach ( $this->columns as $col )
				//$this->format_column_header($col);
			//$this->jar[""] .="</tr>";
		}

		//foreach ( $this->columns as $col )
		if ( $this->body_display == "show" && get_reportico_session_param("target_show_detail") )
        {
		    $this->begin_line();
            if ( !$this->page_started )
            {
		        //$this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->get_style_tags($this->query->output_page_styles).'>';
                $this->page_started = true;
            }
			foreach ( $this->query->display_order_set["column"] as $col )
				$this->format_column($col);
		    $this->end_line();
        }
	}

	function page_template()
	{
		$this->debug("Page Template");
	}
	function before_format_criteria_selection()
	{
		$this->jar["criteria"][] = array();
	}

	function format_criteria_selection($label, $value)
	{
		$crtieriact = count($this->jar["criteria"]) - 1;
		$this->jar["criteriact"]["label"] = $label;
		$this->jar["criteriact"]["value"] = $value;
	}

	function after_format_criteria_selection()
	{
		//$this->jar[""] .= "</TABLE>";
	}


	function finish_page()
	{
		//$this->debug("HTML Finish Page");
		//pdf_end_page($this->document);
	}

	function publish()
	{
		reportico_report::publish();
		$this->debug("Publish HTML");
	}

	function format_page_header(&$header)
	{
        return;
		$just = strtolower($header->get_attribute("justify"));

		$this->jar[""] .= "<TR>";
		$this->jar[""] .= '<TD colspan="10" justify="'.$just.'">';
		$this->jar[""] .=($header->text);
		$this->jar[""] .= "</TD>";
		$this->jar[""] .= "</TR>";

		return;
	}

	function format_page_footer(&$header)
	{
        return;
		$just = strtolower($header->get_attribute("justify"));

		$this->jar[""] .= "<TR>";
		$this->jar[""] .= '<TD colspan="10" justify="'.$just.'">';
		$this->jar[""] .=($header->text);
		$this->jar[""] .= "</TD>";
		$this->jar[""] .= "</TR>";

		return;
	}


}

?>
