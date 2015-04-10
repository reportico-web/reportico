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

 * File:        reportico_report_html.php
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

class reportico_report_html extends reportico_report
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
		        $this->text .= '<div class="reporticoJSONExecute"><a class="swJSONExecute1 testy" href="'.$this->query->get_action_url().'?'.$forward.'refreshReport=1&target_format=JSON&execute_mode=EXECUTE&reportico_session_name='.reportico_session_name().'" title="'.template_xlate("GO_REFRESH").'">&nbsp;</a></div>';
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
            $url .= '&clear_session=1&reportico_session_name=NS_drilldown'.reportico_namespace();
        }


        if ( $hyperlinks["label"] == "<SELF>" )
            $txt = '<a href="'.$url.'"'.$open.'>'.$padstring.'</a>';
        else
            $txt = '<a href="'.$url.'"'.$open.'>'.$hyperlinks["label"].'</a>';
        return $txt;
    }
    
    function extract_styles_and_text_from_string ( &$text, &$styles, &$attributes, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";
        $style_arr = $this->fetch_cell_styles($text);

        $widthset = false;

        if ( $grandparent_styleset && is_array($grandparent_styleset) )
            foreach ( $grandparent_styleset as $k => $v )
            {
                if ( $k == "width" ) $widthset = true;
                $styles .= "$k:$v;";
            }

        if ( $parent_styleset && is_array($parent_styleset) )
            foreach ( $parent_styleset as $k => $v )
            {
                if ( $k == "width" ) $widthset = true;
                $styles .= "$k:$v;";
            }

        if ( isset ( $attributes["justify"] ) )
        {
            if ( $attributes["justify"] == "center" )
                $styles .= "text-align: center;"; 
            if ( $attributes["justify"] == "right" )
                $styles .= "text-align: right;"; 
        }

        if ( isset ( $attributes["ColumnWidthPDF"] ) && $attributes["ColumnWidthPDF"] )
        {
            if ( is_numeric ($attributes["ColumnWidthPDF"]) )
                $styles .= "width: ".$attributes["ColumnWidthPDF"]."px;"; 
            else
                $styles .= "width: ".$attributes["ColumnWidthPDF"].";"; 
        }

        if ( isset ( $attributes["ColumnStartPDF"] ) && $attributes["ColumnStartPDF"] )
        {
            if ( is_numeric ($attributes["ColumnStartPDF"]) )
                $styles .= "margin-left: ".$attributes["ColumnStartPDF"]."px;"; 
            else
                $styles .= "margin-left: ".$attributes["ColumnStartPDF"]."24;"; 
        }

        if ( $style_arr )
            foreach ( $style_arr as $k => $v )
            {
                if ( $k == "width" ) $widthset = true;
                if ( $k == "background-image" )
                    $styles .= "background: url('$v') no-repeat;";
                else
                    $styles .= "$k:$v;";
            }

        // If no width specified default to 100%
        //if ( !$widthset )
            //$styles .= "width:100%;";

        return;
    }

    function fetch_cell_styles(&$tx)
    {
        $styles = false;
        $matches = array();
        if (preg_match("/{STYLE[ ,]*([^}].*)}/", $tx, $matches))
        {
            if ( isset($matches[1]))
            {
                $stylearr = explode(";",$matches[1]);
                $tx = preg_replace("/{STYLE[ ,]*[^}].*}/", "", $tx);
                foreach ($stylearr as $v )
                {
                    if ( !$v )
                        continue;
                    $style = explode(":", $v);
                    if ( count($style) >= 2 )
                    {
                        $name = trim($style[0]);
                        $value = trim($style[1]);
//echo "$name = $value, ";
                        if ( is_numeric($value) )
                        {
                            if ( $name == "width" ) $value .= "px";
                        }
                        $styles[$name] = $value;
                    }
                }
            }
        }
//echo "<BR>";

        $tx = $this->reportico_string_to_php($tx);
        $tx = reportico_assignment::reportico_meta_sql_criteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
    }


    function get_style_tags ( $styleset, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";

        if ( $grandparent_styleset && is_array($grandparent_styleset) )
            foreach ( $grandparent_styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v !important;";
            }

        if ( $parent_styleset && is_array($parent_styleset) )
            foreach ( $parent_styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v !important;";
            }

        if ( $styleset && is_array($styleset) )
            foreach ( $styleset as $k => $v )
            {
                if ( !$outtext )
                    $outtext = "style=\"";
                $outtext .= "$k:$v !important;";
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

	function format_group_header(&$col, $custom) // HTML
	{
        if ( $custom)
        {
            return;
        }

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
            if ( $value_col["GroupTrailerValueColumn"]->output_images )
                        $padstring = $this->format_images ($value_col["GroupTrailerValueColumn"]->output_images);

			if ( $group_label == "BLANK" )
				$this->text .= $padstring;
			else
				$this->text .= $group_label." ".$padstring;
		}
		else
        {
			$this->text .= "&nbsp;";
        }
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

	function format_group_trailer_end($last_trailer = false)
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
                    $col =& $val->headers[$i]["GroupHeaderColumn"];
                    $custom = $val->headers[$i]["GroupHeaderCustom"];
                    $this->format_group_header($col, $custom, true);
			    }
            }

            foreach ( $this->query->display_order_set["column"] as $k => $w )
		    {
		        if ( $w->attributes["column_display"] != "show")
					    continue;
					$this->format_group_header($w, false, false);
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

        // Page Headers
		reportico_report::page_headers();

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
	    $this->text .= '<TH>';
		$this->text .= '<TABLE class="swRepCriteria"'. $this->get_style_tags($this->query->output_criteria_styles).'>';
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

	function format_page_header_start()
	{
        $this->text .= "<div class=\"swPageHeaderBlock\">";
    }

	function format_page_header_end()
	{
        $this->text .= "</div>";
    }

	function format_page_header(&$header)
	{
        $styles = "";
        $text = $header->text;

        $this->extract_styles_and_text_from_string ( $text, $styles, $header->attributes, $parent_styleset = false, $grandparent_styleset = false);
		$just = strtolower($header->get_attribute("justify"));

//echo "Value $text<BR>";
//var_dump($header->attributes);
//echo "Styles = $styles <BR>";
        $img = "";
        if ( $styles )
        {
            $matches = array();
            if ( preg_match ( "/background: url\('(.*)'\).*;/", $styles, $matches ) )
            {
                $styles = preg_replace("/background: url\('(.*)'\).*;/", "", $styles);
                if ( count($matches) > 1 )
                {
                    $img = "<img src='".$matches[1]."'/>";
                }
            }
		    $this->text .= "<DIV class=\"swPageHeader\" style=\"$styles\">";
        }
        else
		    $this->text .= "<DIV class=\"swPageHeader\" >";
		$this->text .= "$img$text";
		$this->text .= "</DIV>";
		//$this->text .= "<TR>";
		//$this->text .= '<TD colspan="10" justify="'.$just.'">';
		//$this->text .=($header->text);
		//$this->text .= "</TD>";
		//$this->text .= "</TR>";

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

?>
