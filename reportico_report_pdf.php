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

 * File:        reportico_report_pdf.php
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

class reportico_report_pdf extends reportico_report
{
    var $dbg = false;
	var	$abs_top_margin;
	var	$abs_bottom_margin;
	var	$abs_row_left_margin;
	var	$abs_col_left_margin;
	var	$abs_left_margin;
	var	$abs_right_margin;
    var $abs_page_width = 0;
    var $abs_page_height = 0;
    var $abs_print_width =  0;
	var	$orientation;
	var	$page_type;
	var	$column_order;
	var	$fontName;
	var	$fontSize;
	var	$vsize;
	var	$columns_calculated = false;
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
    var $actual_line_height = 0;
    var $calculated_line_height = 0;
    var $max_line_height = 0;
    var $required_line_height = 0;
    var $max_line_border_addition = 0;
    var $max_line_padding_addition = 0;
    var $max_border_top_height = 0;
    var $max_border_bottom_height = 0;

    // Maintains record of where group headers and where they reach
    // so we can place them effectively
    var $group_header_start = 0;
    var $group_header_end = 0;

    // Maintains footers  start point so we dont over flow into them
    var $page_header_end_y = 0;
    var $page_footer_start_y = 0;
    var $last_draw_end_y =  0;

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

    // PDF Driver
    var $pdfDriver = "tcpdf";

    var $inGroupOutput = false;

    // Page and body styles in middle of page should not have thinkgs like 
    // top margin or position applied except on first print in page/body. This is used to 
    // hold modified page styles for mid page use
    var $top_page_page_styles = false;
    var $mid_page_page_styles = false;
    var $bottom_page_page_styles = false;
    var $mid_row_page_styles = false;
    var $top_page_reportbody_styles = false;
    var $mid_page_reportbody_styles = false;
    var $bottom_page_reportbody_styles = false;

    var $debugFp = false;

	function __construct ()
	{
		$this->column_spacing = 0;
	}

    function debug2($txt, $divide = false)
    {
        if ( $divide ) echo "<BR><BR>";
        if ( $this->dbg )
            echo "[".$this->document->GetY().": ".$txt."]";
        if ( $divide ) echo "<BR><BR>";
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
		$this->abs_print_width =  $this->abs_right_margin - $this->abs_left_margin;
		$this->abs_row_left_margin = $this->abs_left_margin;
		$this->abs_col_left_margin = $this->abs_left_margin;
		$this->abs_row_right_margin = $this->abs_right_margin;
		$this->abs_col_right_margin = $this->abs_right_margin;
		$this->abs_row_width = $this->abs_print_width;
		$this->abs_columns_width = $this->abs_print_width;

        // Set up default styles
        $this->stylestack = array(
                "border-width" => array( 0 => false ),
                "border-edges" => array( 0 => "" ),
                "padding" => array( 0 => false ),
                "border-style" => array( 0 => "none" ),
                "border-color" => array( 0 => "#000000" ),
                "font-family" => array( 0 => $this->fontName ),
                "font-size" => array( 0 => $this->fontSize ),
                "font-weight" => array( 0 => false ),
                "font-style" => array( 0 => false ),
                "color" => array( 0 => "#000000" ),
                "background-color" => array( 0 => array ( 255, 255, 255 ) ),
                "isfilling" => array( 0 => false ),
                "padding" => array( 0 => 0 ),
                "margin" => array( 0 => array ( 0, 0, 0, 0) ),
                "margin-left" => array( 0 => 0 ),
                "margin-right" => array( 0 => 0 ),
                "margin-top" => array( 0 => 0 ),
                "margin-bottom" => array( 0 => 0 ),
                "text-align" => array( 0 => false ),
                "position" => array( 0 => "relative" ),
                "height" => array( 0 => false ),
                "width" => array( 0 => false ),
                "background-image" => array( 0 => false ),
                "type" => array( 0 => "BASE" ),
               );


        if ( $this->pdfDriver == "tcpdf" )
        {
            // If font used is a Unicode Truetype font then
            // use Unicode PDF generator
            $pdf_path = find_best_location_in_include_path( "tcpdf" );
            require_once($pdf_path."/tcpdf.php");
            //require_once($pdf_path."/tcpdf.php");
            $this->document = new TCPDF($this->orientations[$this->orientation],'pt',$this->page_type, true, 'UTF-8', false);
            $this->document->setPrintHeader(false);

            //if ( !isset($this->document->CoreFonts[strtolower($this->fontName)]) )
                //if ( !isset ($this->document->fonts[strtolower($this->fontName)] ) )
                    //$this->document->AddFont($this->fontName, '', $this->fontName.'.php');

            // If the font loaded is a TrueTypeUnicode font, then we wnat to 
            // use UniCode PDF generator instead
            // if ( $this->document->FontType == "TrueTypeUnicode" )
            // {
		        // $this->document = new UFPDF($this->orientations[$this->orientation],'pt',$this->page_type);
                // if ( !isset($this->document->CoreFonts[strtolower($this->fontName)]) )
                    // if ( !isset ($this->document->fonts[strtolower($this->fontName)] ) )
                        // $this->document->AddFont($this->fontName, '', $this->fontName.'.php');
            // }
        }
        else
        {
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
        }

		$this->document->SetAutoPageBreak(false);
		$this->document->SetMargins(0,0,0);
		$this->document->SetCreator('Reportico');
		$this->document->SetAuthor('Reportico');
		$this->document->SetTitle($this->reporttitle);

        //$this->calculateColumnMetrics();

    }

    function calculateColumnMetrics()
    {
        // =============================================================
        // Report Body start s a left margin with full width
        $this->query->output_reportbody_styles["style_start"] = $this->abs_left_margin;
        $this->query->output_reportbody_styles["style_width"] = $this->abs_print_width;
        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "margin", "left" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "padding", "left" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "border-width", "left" );
        $this->query->output_reportbody_styles["style_margin_left"] = $this->abs_metric($margin);
        $this->query->output_reportbody_styles["style_padding_left"] = $this->abs_metric($padding);
        $this->query->output_reportbody_styles["style_border_left"] = $this->abs_metric($border);
        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "margin", "right" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "padding", "right" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_reportbody_styles, "border-width", "right" );
        $this->query->output_reportbody_styles["style_margin_right"] = $this->abs_metric($margin);
        $this->query->output_reportbody_styles["style_padding_right"] = $this->abs_metric($padding);
        $this->query->output_reportbody_styles["style_border_right"] = $this->abs_metric($border);
        $this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_margin_left"] );
        $this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_margin_right"] );
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_left"] / 2 );
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_right"] / 2 );

        $this->top_page_reportbody_styles = $this->query->output_reportbody_styles;
        $this->mid_page_reportbody_styles = $this->query->output_reportbody_styles;
        $this->bottom_page_reportbody_styles = $this->query->output_reportbody_styles;

        // Create top, middle, bottom report sections
        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_reportbody_styles, "margin", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "margin", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "margin", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "margin", "top");
        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_reportbody_styles, "border-width", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "border-width", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "border-width", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "border-width", "top");
        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_reportbody_styles, "padding", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "padding", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_reportbody_styles, "padding", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "padding", "top");

        // =============================================================
        // Report Page starts from report body mrgin + padding
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"] + $this->query->output_reportbody_styles["style_border_left"];
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"];
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"] + $this->query->output_reportbody_styles["style_border_left"] / 2;
        $rpadding = $this->query->output_reportbody_styles["style_margin_right"] + $this->query->output_reportbody_styles["style_padding_right"] + $this->query->output_reportbody_styles["style_border_right"];
        $rpadding = $this->query->output_reportbody_styles["style_margin_right"] + $this->query->output_reportbody_styles["style_padding_right"];
        $rpadding = $this->query->output_reportbody_styles["style_margin_right"] + $this->query->output_reportbody_styles["style_padding_right"] + $this->query->output_reportbody_styles["style_border_right"] / 2;
        $this->query->output_page_styles["style_start"] = $this->abs_left_margin + $padding;
        $this->query->output_page_styles["style_start"] = $this->query->output_reportbody_styles["style_start"];


        // .. and has a width of page width -$margin_left
        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "margin", "left" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "padding", "left" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "border-width", "left" );
        $this->query->output_page_styles["style_margin_left"] = $this->abs_metric($margin);
        $this->query->output_page_styles["style_padding_left"] = $this->abs_metric($padding);
        $this->query->output_page_styles["style_border_left"] = $this->abs_metric($border);
        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "margin", "right" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "padding", "right" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_page_styles, "border-width", "right" );
        $this->query->output_page_styles["style_margin_right"] = $this->abs_metric($margin);
        $this->query->output_page_styles["style_padding_right"] = $this->abs_metric($padding);
        $this->query->output_page_styles["style_border_right"] = $this->abs_metric($border);

$this->debugFile("Start {$this->query->output_page_styles["style_start"]}");
        $this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_border_left"] );
$this->debugFile("BODY BORDER LEFT {$this->query->output_page_styles["style_start"]}");
        $this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_padding_left"] );
$this->debugFile("BODY PADDING LEFT {$this->query->output_page_styles["style_start"]}");
        $this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_margin_left"] );
$this->debugFile("BODY MARGIN LEFT {$this->query->output_page_styles["style_start"]}");

        $this->query->output_page_styles["style_width"] = $this->abs_print_width - $this->query->output_page_styles["style_start"];
        $this->query->output_page_styles["style_width"] = $this->abs_print_width - $this->query->output_page_styles["style_start"];
$this->debugFile("Start {$padding} {$rpadding} {$this->query->output_reportbody_styles["style_border_left"]}");
$this->debugFile("Start {$this->query->output_reportbody_styles["style_start"]}");
$this->debugFile("Start {$this->query->output_page_styles["style_start"]}");
$this->debugFile("Width {$this->query->output_reportbody_styles["style_width"]}");
$this->debugFile("Width {$this->output_page_styles["style_width"]}");

        $this->query->output_page_styles["style_width"] = $this->abs_right_margin;
        $this->query->output_page_styles["style_width"] -= $this->query->output_page_styles["style_start"];
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_margin_right"];
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_border_right"];
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_padding_right"];
        $this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_margin_left"] );
        $this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_margin_right"] );

        //$this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_margin_left"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_border_left"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_border_left"] / 2);
        //$this->query->output_page_styles["style_start"] += ( $this->query->output_page_styles["style_border_left"]/ 2 );
        //$this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_border_left"] );
        //$this->query->output_page_styles["style_start"] += ( $this->query->output_reportbody_styles["style_margin_left"] );
        //$this->query->output_page_styles["style_start"] += ( $this->query->output_page_styles["style_margin_left"] );
        //$this->query->output_page_styles["style_start"] += ( $this->query->output_page_styles["style_margin_left"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_right"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_left"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_border_left"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_border_right"] );
        //$this->query->output_page_styles["style_width"] -= ( $this->query->output_page_styles["style_border_left"] );
$this->debugFile("Width {$this->query->output_page_styles["style_width"]}");
$this->debugFile("Page start {$this->query->output_page_styles["style_start"]}");

        // Create Page top bottom and mid styles
        $this->top_page_page_styles = $this->query->output_page_styles;
        $this->mid_page_page_styles = $this->query->output_page_styles;
        $this->bottom_page_page_styles = $this->query->output_page_styles;
        $this->mid_row_page_styles = $this->query->output_page_styles;

        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_page_styles, "margin", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "margin", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "margin", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_page_styles, "margin", "top");
        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_page_styles, "border-width", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "border-width", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "border-width", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_page_styles, "border-width", "top");
        $this->remove_style_tags( "REPDETTOPPAGE", $this->top_page_page_styles, "padding", "bottom");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "padding", "top");
        $this->remove_style_tags( "REPDETMIDPAGE", $this->mid_page_page_styles, "padding", "bottom");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->bottom_page_page_styles, "padding", "top");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->mid_row_page_styles, "padding");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->mid_row_page_styles, "margin");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->mid_row_page_styles, "border-width");
        $this->remove_style_tags( "REPDETBOTPAGE", $this->mid_row_page_styles, "background-color");

        // =============================================================
        // Row styles start from Page start + Page margin + padding with width less that
        $padding = $this->query->output_page_styles["style_margin_left"] + $this->query->output_page_styles["style_padding_left"] + $this->query->output_page_styles["style_border_left"] ;
        $rpadding = $this->query->output_page_styles["style_margin_right"] + $this->query->output_page_styles["style_padding_right"] + $this->query->output_page_styles["style_border_right"];
        //$this->query->output_row_styles["style_start"] = $this->query->output_page_styles["style_start"] + $padding;
        //$this->query->output_row_styles["style_width"] = $this->query->output_page_styles["style_width"] - $padding - $rpadding;

        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "margin", "left" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "padding", "left" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "border-width", "left" );
        $this->query->output_row_styles["style_margin_left"] = $this->abs_metric($margin);
        $this->query->output_row_styles["style_padding_left"] = $this->abs_metric($padding);
        $this->query->output_row_styles["style_border_left"] = $this->abs_metric($border);
        $margin =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "margin", "right" );
        $padding =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "padding", "right" );
        $border =  $this->extract_style_tags ( "EACHLINE", $this->query->output_row_styles, "border-width", "right" );
        $this->query->output_row_styles["style_margin_right"] = $this->abs_metric($margin);
        $this->query->output_row_styles["style_padding_right"] = $this->abs_metric($padding);
        $this->query->output_row_styles["style_border_right"] = $this->abs_metric($border);
        $padding = $this->query->output_row_styles["style_margin_left"] + $this->query->output_row_styles["style_padding_left"] + $this->query->output_row_styles["style_border_left"];
        $this->abs_col_left_margin = $this->query->output_page_styles["style_start"]; // + $this->query->output_page_styles["style_padding_left"];
$this->debugFile("col start {$this->abs_col_left_margin}");
        //$this->abs_col_left_margin += $this->query->output_page_styles["style_start"]; // + $this->query->output_page_styles["style_padding_left"];
        //$this->abs_col_left_margin -= $this->query->output_page_styles["style_border_left"] / 2; // + $this->query->output_page_styles["style_padding_left"];
        $this->abs_col_left_margin += $this->query->output_page_styles["style_margin_left"];
$this->debugFile("col start PAGE MARGIN LEFT  {$this->abs_col_left_margin}");
        $this->abs_col_left_margin += $this->query->output_page_styles["style_border_left"];
$this->debugFile("col start PAGE BORDER LEFT  {$this->abs_col_left_margin}");
        $this->abs_col_left_margin += $this->query->output_page_styles["style_padding_left"];
$this->debugFile("col start PAGE PADDING LEFT  {$this->abs_col_left_margin}");
        //$this->abs_col_left_margin += 1;
        $this->abs_col_left_margin -= $this->query->output_reportbody_styles["style_margin_left"];
        //$this->abs_col_left_margin += 1;
        //$this->abs_col_right_margin -= 
                    //0
                    ////+ $this->query->output_page_styles["style_margin_left"] 
                    //+ $this->query->output_page_styles["style_margin_right"] 
                    //+ $this->query->output_page_styles["style_padding_right"] 
                    ////+ $this->query->output_page_styles["style_padding_left"] 
                    //+ $this->query->output_page_styles["style_border_left"] 
                    //+ $this->query->output_page_styles["style_border_right"];
                    //+ 2;
        //$this->abs_col_right_margin = 
                    //$this->abs_col_left_margin + $this->query->output_page_styles["style_width"] - ( ( $this->query->output_page_styles["style_border_right"] + $this->query->output_page_styles["style_border_left"] ) / 2 );
        $this->abs_col_right_margin = $this->abs_col_left_margin + $this->query->output_page_styles["style_width"];
        $this->abs_col_right_margin = $this->query->output_page_styles["style_start"] + $this->query->output_page_styles["style_width"];
 //- ( ( $this->query->output_page_styles["style_border_right"] + $this->query->output_page_styles["style_border_left"] ) );
        //$this->abs_col_right_margin -= $this->query->output_page_styles["style_margin_left"];
        //$this->abs_col_right_margin -= $this->query->output_page_styles["style_padding_left"];
        $this->abs_col_right_margin -= $this->query->output_page_styles["style_border_right"];
        $this->abs_col_right_margin -= $this->query->output_page_styles["style_padding_right"];
        $this->abs_col_right_margin -= $this->query->output_page_styles["style_margin_right"];
        $this->abs_col_right_margin -= $this->query->output_reportbody_styles["style_margin_right"];
        $this->abs_col_right_margin += $this->query->output_page_styles["style_margin_left"];
        $this->abs_col_right_margin += $this->query->output_page_styles["style_margin_right"];
        //$this->abs_col_right_margin -= $this->query->output_reportbody_styles["style_margin_right"];
        //$this->abs_col_right_margin -= $this->query->output_reportbody_styles["style_border_right"];
        //$this->abs_col_right_margin +=  1;

/*
var_dump($this->query->output_page_styles);
echo $padding;
echo " with ".$this->query->output_page_styles["style_margin_left"]."<BR>";
echo " with ".$this->query->output_page_styles["style_padding_left"]."<BR>";
var_dump($this->query->output_reportbody_styles);
var_dump($this->query->output_page_styles);
var_dump($this->query->output_row_styles);
echo "LR $this->abs_left_margin $this->abs_right_margin  col $this->abs_col_left_margin $this->abs_col_right_margin <BR>";
$x = $this->abs_col_right_margin - $this->abs_col_left_margin;
echo "wid $this->abs_col_right_margin - $this->abs_col_left_margin = $x<BR>";
*/

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
								$col->abs_column_start = $this->abs_col_left_margin;
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
				$topos =  $this->abs_col_right_margin;
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

        $this->debugFile("FINISH");
		//if ( $this->line_count < 1 )
		//{
            //// No PDF data found just return
            //return;
		//}
//var_dump($this->stylestack);
//if ( $this->dbg )
//{
//die; 
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

	function abs_metric($value, $type = "width")
	{
		//if ( preg_match("/(\d*\.*(\d+)(\D*)/", $height_string, $match) )
		if ( preg_match("/(\d+)(\D*)/", $value, $match) )
		{
			$height = $match[1];
			if ( isset( $match[2] ) )
			{
				switch ( $match[2] )
				{
					case "pt":
						$value = $value;
						break;

					case "%":
                        if ( $type == "width" )
						    $value = ( $value * $this->abs_page_width ) / 100;
                        else
						    $value = ( $value * $this->abs_page_height ) / 100;
						break;

					case "mm":
						$value = $value / 0.35277777778;
						break;

					case "cm":
						$value = $value / 0.035277777778;
						break;

					default:
						//handle_error("Unknown Page Sizing Option ".$match[2]);
						break;

				}
			}
		}
		else
		{
			$value = $value;
			//handle_error("Unknown Page Sizing Option $height_string");
		}

		return $value;
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
			$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("group_header_label" );
			if ( !$group_label )
				$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("column_title" );

			if ( !$group_label )
			{
				$group_label = $value_col["GroupTrailerValueColumn"]->query_name;
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
			$padstring = $value_col["GroupTrailerValueColumn"]->old_column_value;
			$just = $this->justifys[$trailer_col->derive_attribute( "justify",  "left")];
			$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("group_trailer_label" );
			if ( !$group_label )
				$group_label = $value_col["GroupTrailerValueColumn"]->get_attribute("column_title" );
            if ( !$group_label )
            {  
                $group_label = $value_col["GroupTrailerValueColumn"]->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }

			if ( $group_label && $group_label != "BLANK" )
				$padstring = $group_label." ".$padstring;

			$this->draw_cell($wd,$this->vsize + 2,"$padstring", "PBF", 0, $just);

			// Fetch Group Header Label Start Column + display
			$group_xpos = $value_col["GroupTrailerValueColumn"]->get_attribute("group_header_label_xpos" );
			if ( !$group_xpos )
				$group_xpos = 0;
			$group_xpos = $this->abs_paging_width($group_xpos);
			$group_xpos = $value_col["GroupTrailerValueColumn"]->abs_column_start;

			$this->set_position($group_xpos, $y);
			$padstring = $group_label;
			$just = $this->justifys[$trailer_col->derive_attribute( "justify",  "left")];
		}

	}

	function format_custom_trailer(&$trailer_col, &$value_col) // PDF
	{
//echo "CUSTOMTRAILER<BR>";
        // If this is the first custom trailer break a little
		if ( !get_reportico_session_param("target_show_group_trailers") )
			return;

        if ( !$value_col["GroupTrailerCustom"] )
            return;

		if ( $value_col["GroupTrailerCustom"] )
		{
//$this->debug2("CUSTOMTRAILER1".$this->group_header_end." ");
            if ( $this->draw_mode != "CALCULATE" )
            {
                if ( !$this->any_custom_trailers || $this->any_custom_trailers == "NONE")
                {
                    //$this->unapply_style_tags( "CUSTRAILNOPAGE", $this->mid_page_page_styles);
		            $this->current_line_height = 0;
		            $this->draw_cell(0, 0, "",0,1);
	                $this->end_line();
                    $this->group_header_start = $this->document->GetY();
                    //$this->apply_style_tags( "CUSTRAILNOPAGE", $this->mid_page_page_styles);
                }
                $this->any_custom_trailers = "PP";
            }
//$this->debug2("CUSTOMTRAILER2".$this->group_header_end." ");
            $this->set_position($this->abs_left_margin, $this->group_header_start);
//$this->debug2("CUSTOMTRAILER3".$this->group_header_end." ");
	        $prevx = $this->document->GetX();
	        $prevy = $this->document->GetY();

            $this->yjump = 2;
            // Fetch Group Header Label Start Column + display
            //$this->unapply_style_tags( "DEFAULT", $this->mid_page_page_styles);
        
            // Display group header value

            $this->set_position($this->abs_left_margin, $this->group_header_start);

            $tx = $value_col["GroupTrailerCustom"];
            $wd = $this->abs_print_width;
            $styles = $this->fetch_cell_styles($tx);
            $just = "L";
	        $this->apply_style_tags( "CUSTOMTRAILER", $styles);
            //if ( $this->draw_mode == "DRAW" )
            //{
			   //$this->draw_cell_container($wd, $this->vsize + 4, $tx,"PBR",0,$just);
            //}
            $link = false;
//$this->debug2("CUSTOMTRAILER2".substr($tx, 0, 10)." ");
			$this->draw_cell($wd, $this->vsize + 0, $tx,"PBF",0,$just, "T", $link);
	        $this->unapply_style_tags( "CUSTOMTRAILER", $styles);

            $this->end_line();
            //$this->draw_cell($group_data_width, $this->vsize, "");    // Blank cell to continue page breaking at this size
            $y = $this->document->GetY();

            // Store where group header reaches so we know where to start printing after row
            if ( $y > $this->group_header_end )
                $this->group_header_end = $y;

            if ( $this->yjump )
                $this->set_position(false, $y + $this->yjump);

            //$this->apply_style_tags( "DEFAULT", $this->mid_page_page_styles);
            $this->set_position($prevx, $prevy);
//$this->debug2("CUSTOMTRAILER 5 ".$this->group_header_end." ");
		}

	}
    //Cell with horizontal scaling if text is too wide
    function draw_cell_container($w,$h=0,$txt='',$border=0,$ln=0,$align='',$valign="T") 
    {
        // Set custom width
        $custom_width = end( $this->stylestack["width"]);
        if ( $custom_width )
            $w = $custom_width;

        // Get margins and position
        $position = end( $this->stylestack["margin-top"]);
        $margin_top = end( $this->stylestack["margin-top"]);
        $margin_left = end( $this->stylestack["margin-left"]);

        // Get Justification
        $justify = end( $this->stylestack["text-align"]);
        if ( $justify )
        {
            switch ( $justify )
            {
                case "centre":
                case "center":
                    $align = "C";
                    break;

                case "justify":
                    $align = "J";
                    break;

                case "right":
                    $align = "R";
                    break;

                case "left":
                    $align = "L";
                    break;
            }
        }

        // Add padding
        $padding = end( $this->stylestack["padding"]);
        $toppad = $padding[0];
        $bottompad = $padding[2];

        // Add border and bg color
        $fill = end( $this->stylestack["isfilling"]);
        $borderwidth = end( $this->stylestack["border-edges"]);
        $border = end( $this->stylestack["border-style"]);
        if ( $border != "none" )
            $border = 1;
        else
            $borderwidth = "false";

        // Store current position so we can jump back after cell draw
		$x = $this->document->GetX();
		$y = $this->document->GetY();

        if ( $margin_top )
		    $this->set_position($false, $y + $margin_top);

        if ( $margin_left )
		    $this->set_position($x + $margin_left, false);

        //$background_image = end( $this->stylestack["background-image"] );
        //if ( $background_image )
            //$h = $this->document->Image($background_image, $this->document->GetX(), $this->document->GetY(), $w *2);

        $this->draw_multicell($w,$this->max_line_height,"",$borderwidth,false,$fill);
        $cell_height = $this->document->GetY() - $y;

        // Jump back
		$this->set_position($x, $y);
    }

    //Cell with horizontal scaling if text is too wide
    function draw_cell($w,$h=0,$txt='',$implied_styles="PBF",$ln=0,$align='',$valign="T", $link='')
    {
if ( preg_match("/^debug/", $txt ) ) $txt .= " ".round($this->document->GetY());
        // Set custom width
        $custom_width = end( $this->stylestack["width"]);
        if ( $custom_width )
        {
            $w = $this->abs_metric($custom_width);
        }

        // Set custom width
        $custom_height = end( $this->stylestack["height"]);
        if ( $custom_height )
        {
            $h = $this->abs_metric($custom_height, "height");
        }

        // Get margins and position
        $position = end( $this->stylestack["position"]);
        $margin_top = $this->abs_metric(end( $this->stylestack["margin-top"]));
        $margin_left = $this->abs_metric(end( $this->stylestack["margin-left"]));
        $margin_right = $this->abs_metric(end( $this->stylestack["margin-right"]));
        $margin_bottom = $this->abs_metric(end( $this->stylestack["margin-bottom"]));

        // If a cell contains a line break like a "<BR>" then convert it to new line
        $txt = preg_replace("/<BR>/i", "\n", $txt);
        // Calculate cell height as string width divided by width

        // Add margin
        $topmargin = 0;
        $bottommargin = 0;
        $leftmargin = 0;
        $rightmargin = 0;

        $margin = end( $this->stylestack["margin"]);
        if ( $margin )
        {
            $topmargin = $this->abs_metric($margin[0]);
            $rightmargin = $this->abs_metric($margin[1]);
            $bottommargin = $this->abs_metric($margin[2]);
            $leftmargin = $this->abs_metric($margin[3]);
        }
$x = print_r($this->stylestack["border-width"], true);
$this->debugFile("oo".$x);
$x = print_r($this->stylestack["border-edges"], true);
$this->debugFile("oo".$x);
$this->debugFile(" L R T B $topmargin $bottommargin $leftmargin $rightmargin");

        // Add padding
        $toppad = 0;
        $bottompad = 0;
        $leftpad = 0;
        $rightpad = 0;
        if ( strstr($implied_styles, "P" ) || $this->pdfDriver == "tcpdf" )
        {
            $padding = end( $this->stylestack["padding"]);
            $toppad = $this->abs_metric($padding[0]);
            $rightpad = $this->abs_metric($padding[1]);
            $bottompad = $this->abs_metric($padding[2]);
            $leftpad = $this->abs_metric($padding[3]);
        }

        if ( $this->pdfDriver == "fpdf" )
            $oldLineWidth = $this->document->LineWidth;
        else
            $oldLineWidth = $this->document->getLineWidth();

        $fill = false;
        if ( strstr($implied_styles, "F" ) || $this->pdfDriver == "tcpdf" )
        {
            // Add border and bg color
            $fill = end( $this->stylestack["isfilling"]);
        }

        // Get Justification
        $justify = end( $this->stylestack["text-align"]);
        if ( $justify )
        {
            switch ( $justify )
            {
                case "centre":
                case "center":
                    $align = "C";
                    break;

                case "justify":
                    $align = "J";
                    break;

                case "right":
                    $align = "R";
                    break;

                case "left":
                    $align = "L";
                    break;
            }
        }
        
        $borderstyle = false;
        $borderedges = false;
        $borderwidth = false;

        $topborderadditiontoheight = 0;
        $botborderadditiontoheight = 0;
        $leftborderadditiontoheight = 0;
        $rightborderadditiontoheight = 0;
        if ( strstr($implied_styles, "B") || $this->pdfDriver == "tcpdf" )
        {
            $borderedges = end( $this->stylestack["border-edges"]);

            $borderwidth = end( $this->stylestack["border-width"]);
            $border = end( $this->stylestack["border-style"]);
            if ( $border != "none" )
                $borderstyle= 1;
            else
                $borderedges = "false";

            if ( $borderwidth )
                $borderwidth = $this->abs_metric($borderwidth);

            // Add extra padding in cell to avoid text conficting with border
            //if ( $borderwidth && preg_match("/T/", $borderedges) ) $toppad += $borderwidth; 
            //if ( $borderwidth && preg_match("/B/", $borderedges) ) $bottompad += $borderwidth; 
            //if ( $borderwidth && preg_match("/L/", $borderedges) ) $leftpad += $borderwidth; 
            //if ( $borderwidth && preg_match("/R/", $borderedges) ) $rightpad += $borderwidth; 

            // Calulate addition cellheight caused by top and bottom borders
            if ( $borderwidth && preg_match("/T/", $borderedges) ) $topborderadditiontoheight = $borderwidth / 2; 
            if ( $borderwidth && preg_match("/B/", $borderedges) ) $botborderadditiontoheight = $borderwidth / 2; 
            if ( $borderwidth && preg_match("/L/", $borderedges) ) $leftborderadditiontoheight = $borderwidth / 2; 
            if ( $borderwidth && preg_match("/R/", $borderedges) ) $rightborderadditiontoheight = $borderwidth / 2; 
            //$topborderadditiontoheight = $borderwidth;
            //$botborderadditiontoheight = $borderwidth;
        }

        // Store current position so we can jump back after cell draw
		$storey = $this->document->GetY();
		$storex = $this->document->GetX();

        // If position is absolute position at page start
        if ( $position == "absolute" )
        {
		    $this->set_position(1, 1);
        }

        // Store current position so we can jump back after cell draw
		$y = $this->document->GetY();
		$x = $this->document->GetX();

        // Cells with borders draw with half border appearing outside the text box so mode text down by half to give it space
        if ( $topborderadditiontoheight )
        {
            $topmargin +=$topborderadditiontoheight;
            //if ( $this->document->GetFontSizePt() == 0 )
                //$toppad +=$topborderadditiontoheight;
        }
    
        // Cells with borders draw with half border appearing outside the text box so mode text down by half to give it space
        if ( $botborderadditiontoheight )
        {
            $bottommargin += $botborderadditiontoheight;
            //if ( $this->document->GetFontSizePt() == 0 )
                $bottompad +=$botborderadditiontoheight;
        }
   $this->debugFile(" MARGNI $borderedges $leftmargin $rightmargin $leftborderadditiontoheight $rightborderadditiontoheight"); 
        if ( $leftborderadditiontoheight )
            $leftmargin += $leftborderadditiontoheight;
        if ( $rightborderadditiontoheight )
            $rightmargin += $rightborderadditiontoheight;

        if ( $leftborderadditiontoheight )
            $w -= $leftborderadditiontoheight;
        if ( $rightborderadditiontoheight )
            $w -= $rightborderadditiontoheight;

   $this->debugFile(" MARGNI $borderedges $leftmargin $rightmargin $leftborderadditiontoheight $rightborderadditiontoheight"); 
        $borderaddition = $botborderadditiontoheight + $topborderadditiontoheight;
        //$paddingaddition = $toppad + $bottompad;

        if ( $this->draw_mode == "CALCULATE" )
        {
                $this->document->startTransaction();
                //$fill_line_height = $margin_top + $toppad + $this->calculated_line_height + $bottompad;
                //if ( $this->max_line_height < $fill_line_height )
                $cellvaluewidth = $w;
                $cellvaluefromy = $this->document->GetY();

                $this->document->SetCellPaddings($leftpad, $toppad, $rightpad, $bottompad);
                $this->document->SetCellMargins($leftmargin, $topmargin, $rightmargin, $bottommargin);

                $cellborder = false;
                if ( $borderedges )
                {
                    $margin_top = end( $this->stylestack["margin-top"]);
                    $border_color = end( $this->stylestack["border-color"] );
                    $border_style = end( $this->stylestack["border-style"] );

                    if ( $border_style == "none" )
                        $cellborder = false;
                    else
                    {
                        if ( $border_style == "solid" )
                            $border_style = 0;
                        else if ( $border_style == "dotted" )
                            $border_style = 1;
                        else if ( $border_style == "dashed" )
                            $border_style = 2;

                        $cellborder = array ( 
                                      //'mode' => "int",
                                      $borderedges => array (
                                      'width' => $borderwidth,
                                      'color' => $border_color,
                                      'dash' => $border_style
                                        ));



                    }
                }
$y1 = $this->document->GetY();
                $ht= $this->draw_multicell($cellvaluewidth,0,"$txt", $cellborder, false, false, false, false);
$y2 = $this->document->GetY() - $y1; $y1 = $this->document->GetY();
//echo "Calulate line height Txt=\"$txt\" fina = $ht  = $y2<BR>";
$tborderwidth = 0;
$bborderwidth = 0;
if ( preg_match("/T/", $borderedges )) $tborderwidth = $borderwidth;
if ( preg_match("/B/", $borderedges )) $bborderwidth = $borderwidth;
$tot =  $topmargin + $bottommargin + $toppad + $bottompad  + $tborderwidth + $bborderwidth;
                $numlines = $this->document->getNumLines($txt, $cellvaluewidth);
$dtxt = " stats Actual height $ht margin=$topmargin + $bottommargin,  pad=$toppad + $bottompad,  border $borderedges $tborderwidth + $bborderwidth = $tot  add $topborderadditiontoheight + $botborderadditiontoheight lines $numlines<BR>";
//echo "$dtxt line hight = 25<BR>";
//echo " to = $tot<BR>";
                $this->document->SetCellPadding(0);
                $this->document->SetCellMargins(0,0);
                if ( $cellvaluewidth < 0 )
                {
                    $cellvaluewidth = 20;
                    $txt = "Padding too large / Width too small - $txt";
                }
                //$cellheight = ( $numlines * $ht ) - (( $numlines  - 1 ) * $this->max_line_border_addition *2);
                $cellheight = $ht;
                //$cellheight = $this->document->GetY() - $cellvaluefromy;
                //$cellheight += $botborderadditiontoheight;
                //$cellheight += $topborderadditiontoheight;
                $requiredheight = $cellheight - $borderaddition;
                if ( $cellheight > $this->max_line_height )
                {
                       $dtxt .= " grown";
                    $this->max_line_height = $cellheight;
                }
                if ( $requiredheight > $this->required_line_height )
                {
                       $dtxt .= " grown";
                    $this->required_line_height = $requiredheight;
                }
$this->debugFile("\nCELL CALCULATE req $this->required_line_height $txt $dtxt ");
                //if ( $borderaddition > $this->max_line_border_addition )
                    //$this->max_line_border_addition = $borderaddition;
                //if ( $paddingaddition > $this->max_line_padding_addition )
                    //$this->max_line_padding_addition = $paddingaddition;

//echo "set $txt to ".$this->max_line_height."<BR>";
                $this->document = $this->document->rollbackTransaction();
                return;
        }

        // To cater for multiline values, jump to bottom of line + padding -
        // cell height
           
        $jumpy = 0;
        if ( $toppad && $this->pdfDriver == "fpdf" )
        {
            if ( $valign == "T" )
                $jumpy = $toppad;
            else if ( $valign == "B" )
                $jumpy = $toppad + $this->calculated_line_height - $cellheight;
            else if ( $valign == "C" )
                $jumpy = ( ( $toppad + $this->calculated_line_height + $bottompad ) - $cellheight ) / 2;
        }


        if ( $margin_top )
		    $this->set_position(false, $y + $margin_top);

        if ( $margin_left )
		    $this->set_position($x + $margin_left, false);

        $prevx = $this->document->GetX();

        // Top Padding
        if ( $toppad && $this->pdfDriver == "fpdf" )
        {
            $tmpborder = $borderedges;
            $tmpborder = preg_replace("/B/", "", $borderedges);

            $pady = $this->document->GetY() - 1;
		    $this->set_position(false, $pady + 2);
            
            $prevx = $this->document->GetX();

            if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
            $this->draw_multicell($w,$toppad,"",$tmpborder,$align,$fill,$link);
            if ( $borderwidth ) $this->document->setLineWidth($oldLineWidth);

		    $this->set_position($prevx, false);
        }


		$this->set_position(false, $margin_top + $y + $jumpy );

        // Link in a PDF must include a full URL contain http:// element
        // drilldown link of web url can be relative .. so prepend required elements
        if ( $link )
        {
            if ( !preg_match("/^http:\/\//", $link) && !preg_match("/^\//", $link ) )
                $link = "http://".$_SERVER["HTTP_HOST"].dirname($this->query->url_path_to_reportico_runner)."/".$link;
            if ( preg_match("/^\//", $link ) )
                $link = SW_HTTP_URLHOST."/".$link;
        }

        // Cell Side Borders
        $tmpborder = $borderedges;
        if ( $toppad ) $tmpborder = preg_replace("/T/", "", $tmpborder);
        if ( $bottompad ) $tmpborder = preg_replace("/B/", "", $tmpborder);
        $cellborder = $tmpborder;
        if ( $leftpad ) $cellborder = preg_replace("/L/", "", $cellborder);
        if ( $rightpad ) $cellborder = preg_replace("/R/", "", $cellborder);


        $storeX = $this->document->GetX();
        if ( $this->pdfDriver == "fpdf" )
            if ( $leftpad )
		        $this->set_position($storeX + $leftpad - 1, false);

        $storeY = $this->document->GetY();

        $cellvaluewidth = $w - $leftpad - $rightpad;
        $cellvaluewidth = $w;
        if ( $cellvaluewidth < 0 )
        {
            $cellvaluewidth = 20;
            $txt = "Padding too large / Width too small - $txt";
        }

        // Cell image
        $background_image = end( $this->stylestack["background-image"] );
        $cell_height = 0;
        $last_draw_end_y = 0;
        if ( $background_image )
        {
            $preimageX = $this->document->GetX();
            $preimageY = $this->document->GetY();

//echo "$background_image, $custom_height $h <BR>";
            $p = $this->draw_image($background_image, $this->document->GetX() + $leftmargin, $this->document->GetY() + $topmargin, $cellvaluewidth, $h);
            
//echo "$last_draw_end_y $storeY $background_image ".$p."<BR>";
            //if ($this->document->GetY() + $p > $this->group_header_end )
                //$this->group_header_end = $this->document->GetY() + $p; 
            $cell_height = $p;
            $last_draw_end_y =  $storeY + $p;
		    $this->set_position($preimageX, $preimageY );
            //$this->last_draw_end_y =  $storeY + $p;
            //return;
        }


        // Cell value
        $cellvaluefromy = $this->document->GetY();
        $actcellvaluewidth = $cellvaluewidth;

        if ( $this->pdfDriver == "fpdf" )
        {
            // with left pad and right pad there can be some odd lines left on side so force a bigger width
            if ( $leftpad ) $actcellvaluewidth += 1;
            if ( $rightpad ) $actcellvaluewidth += 1;

            if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
        }

        if ( $this->pdfDriver == "tcpdf" )
        {
            $this->document->SetCellPaddings($leftpad, $toppad, $rightpad, $bottompad);
$this->debugFile( "set pad $leftpad, $toppad, $rightpad, $bottompad");
$this->debugFile( "set marg $leftmargin, $topmargin, $rightmargin, $bottommargin");
            $this->document->SetCellMargins($leftmargin, $topmargin, $rightmargin, $bottommargin);
if ( preg_match("/^debug/", $txt ) ) $txt .= " ".round($this->document->GetY());
            //if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
            $cellborder = false;
            if ( $borderedges )
            {
                $margin_top = end( $this->stylestack["margin-top"]);
                $border_color = end( $this->stylestack["border-color"] );
                $border_style = end( $this->stylestack["border-style"] );

                if ( $border_style == "none" )
                    $cellborder = false;
                else
                {
                    if ( $border_style == "solid" )
                        $border_style = 0;
                    else if ( $border_style == "dotted" )
                        $border_style = 1;
                    else if ( $border_style == "dashed" )
                        $border_style = 2;

                    $cellborder = array ( 
                                  //'mode' => "int",
                                  $borderedges => array (
                                  'width' => $borderwidth,
                                  'color' => $border_color,
                                  'dash' => $border_style
                                    ));



                }
            }
                                            
        }
if ( preg_match("/^debug/", $txt ) ) $txt .= " $topmargin ".round($this->document->GetY());
    
//var_dump($cellborder);
//$txt .= "$h !";
        //$h = $h - $this->max_line_border_addition;
        //$h = $h - $this->max_line_padding_addition;
        if ( $background_image )
            $ht= $this->draw_multicell($actcellvaluewidth,$h,$txt,$cellborder,$align,false,$link);
        else
        {
//if ( $h == 22.5 ) $h = 0;
//$h = 0;
            $ht= $this->draw_multicell($actcellvaluewidth,$h,$txt,$cellborder,$align,$fill,$link);
$this->debugFile("\nCELL DRAW drawn ( $actcellvaluewidth want = $h Actual height $ht vs $cell_height vs $h $txt  ");
        }

        if ( $borderwidth ) $this->document->setLineWidth($oldLineWidth);
        //$text_cell_height = $this->document->GetY() - $cellvaluefromy;
        $text_cell_height = $ht;
        //$text_cell_height -= $this->max_line_border_addition;
        //$text_cell_height -= $this->max_line_padding_addition;
                    //$this->max_line_padding_addition = $paddingaddition;
//echo "got $actcellvaluewidth x $toppad $bottompad $ht $txt $text_cell_height<BR>";
        if ( $text_cell_height > $cell_height )
            $cell_height = $text_cell_height;

if ( preg_match("/^debug/", $txt ) ) $txt .= " H".round($cell_height);
$txt .= " H".round($cell_height);

        // Left and right padding
        if ( $leftpad && $this->pdfDriver == "fpdf" ) 
        {
		    $this->set_position($storeX, $storeY);

            $tmpborder1 = preg_replace("/R/", "", $tmpborder);
            if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
            $this->draw_multicell($leftpad,$cell_height,"",$tmpborder1,$align,$fill,$link);
            if ( $borderwidth ) $this->document->setLineWidth($oldLineWidth);
        }
        if ( $rightpad && $this->pdfDriver == "fpdf" ) 
        {
		    $this->set_position($storeX + $leftpad + $cellvaluewidth, $storeY);
            $tmpborder1 = preg_replace("/L/", "", $tmpborder);
            if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
            $this->draw_multicell($rightpad,$cell_height,"",$tmpborder1,$align,$fill,$link);
            if ( $borderwidth ) $this->document->setLineWidth($oldLineWidth);
        }
if ( preg_match("/^debug/", $txt ) ) $txt .= " ".round($this->document->GetY());

        // Bottom Padding
        if ( $bottompad && $this->pdfDriver == "fpdf" )
        {
            $pady = $this->document->GetY() - 1;
		    $this->set_position($prevx, $pady);
            $tmpborder = "";
            if ( preg_match("/B/", $borderedges ) )
            {
                $tmpborder = preg_replace("/T/", "", $borderedges);
            }
            
            if ( $borderwidth ) $this->document->setLineWidth($borderwidth);
            $this->draw_multicell($w, $bottompad,"",$tmpborder,$align,$fill,$link);
            if ( $borderwidth ) $this->document->setLineWidth($oldLineWidth);
		    $this->set_position($prevx, false);
        }

        if ( $this->pdfDriver == "tcpdf" )
        {
            $this->document->SetCellPadding(0);
            $this->document->SetCellMargins(0,0);
        }

        // Store reach of cells for headers unless we are in absolute position
        // in which case we allow other stuff to pverwrite it
        if ( $position != "absolute" )
        {
            if ( $this->document->GetY() > $this->group_header_end )
                $this->group_header_end = $this->document->GetY();

            //if ( $topborderadditiontoheight )
                //$cell_height += $topborderadditiontoheight;
            //if ( $botborderadditiontoheight )
                //$cell_height += $botborderadditiontoheight;
            //$cell_height +=  $this->max_line_padding_addition;
            //$cell_height += $botborderadditiontoheight;
            //$cell_height += $toppad;

$this->debugFile("\nCELL DRAW set $cell_height > $this->current_line_height  ");
            if ( $cell_height > $this->current_line_height )
                $this->current_line_height = $cell_height;
if ( preg_match ( "/REPTOPPAGE/", $txt ) )
{
    echo "pad $toppad $bottompad $txt $this->max_line_height / $this->current_line_height is $h <BR>";
//die;
}
    
            $this->last_draw_end_y =  $this->document->GetY();
            if ( $this->last_draw_end_y < $last_draw_end_y )
                $this->last_draw_end_y = $last_draw_end_y;
        }
            

        // Jump back
		$this->set_position(false, $storey);
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
		    $this->document->Ln($this->current_line_height -  $this->max_line_border_addition);
            //$this->set_position(false, $this->current_line_start_y + $this->current_line_height);
        }
        else
        {
            if ( $h !== false ) 
            {
		        $this->document->Ln($h);
            }
            else
            {
		        $this->document->Ln();
            }
        }
		$y = $this->document->GetY();
        $this->set_position(false, $y);
        $this->current_line_start_y = $this->document->GetY();
        $this->current_line_height = 0;
        $this->max_line_height = 0;
        $this->required_line_height = 0;
        $this->max_line_border_addition = 0;
        $this->max_line_padding_addition = 0;
        $this->calculated_line_height = 0;
	}

	function format_page_footer_start() // PDF
	{
	    //$this->unapply_style_tags( "DEFAULT", $this->mid_page_page_styles);
    }

	function format_page_footer_end() // PDF
	{
	    //$this->unapply_style_tags( "DEFAULT", $this->query->output_reportbody_styles);
    }

	function format_page_header_start() // PDF
	{
		$this->reporttitle = $this->query->derive_attribute("ReportTitle", "Set Report Title");
		//$this->reporttitle = preg_replace("/<[^>]*>/", "", $this->reporttitle);
                    
        // Add custom image here
        if ( defined("PDF_HEADER_IMAGE") )
        {
            $x = 500;
            $y = 25;
            $w = 50;
            if ( defined("PDF_HEADER_XPOS") ) $x = PDF_HEADER_XPOS;
            if ( defined("PDF_HEADER_YPOS") ) $y = PDF_HEADER_YPOS;
            if ( defined("PDF_HEADER_WIDTH") ) $w = PDF_HEADER_WIDTH;

            $h = $this->draw_image(PDF_HEADER_IMAGE, $x, $y, $w *  $this->pdfImageDPIScale);
        }

        //$this->set_default_styles();
	    //$this->apply_style_tags( "DEFAULT", $this->query->output_reportbody_styles);
		return;
	}

	function format_page_header_end() // PDF
	{
		$this->end_line();
	    //$this->apply_style_tags( "DEFAULT", $this->mid_page_page_styles);
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
//$this->debug2("GROUP HEADER START $this->inOverflow<BR>");
 //echo "GROUP HEADER START $this->inOverflow<BR>";
        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;
        $this->inGroupOutput = true;

//$this->debug2("GROUP HEADER STARTa $this->inOverflow<BR>");

		$this->group_header_start = $this->document->GetY();
		$this->group_header_end = $this->document->GetY();

//$this->debug2("GROUP HEADER START1 $this->inOverflow<BR>");
		// Throw new page if current position + number headers + line + headers > than bottom margin
		$ln = 0;
        $totheaderheight = 0;
        $prevheight = $this->calculated_line_height;

		foreach ( $this->query->groups as $val )
        {
			for ($i = 0; $i < count($val->headers); $i++ )
			{
				$col =& $val->headers[$i]["GroupHeaderColumn"];
				$custom = $val->headers[$i]["GroupHeaderCustom"];
				$this->format_group_header($col, $custom, true);
                $totheaderheight += $this->calculated_line_height;
			}
        }
        $this->calculated_line_height = $totheaderheight;
		$y = $this->document->GetY();
        $this->check_page_overflow();
        $this->calculated_line_height = $prevheight;
//$this->debug2("group header donw ".$this->document->Gety()." ".$this->group_header_end."<BR>");
	}

	function format_group_header_end()
	{
//$this->debug2("GROUP HEADER END $this->inOverflow<BR>");
        $this->set_position(false, $this->group_header_end);
//$this->debug2("group header end ".$this->document->Gety()."<BR>");
	    //$this->draw_cell($group_data_width, $this->vsize, "$this->current_line_height");    // Blank cell to continue page breaking at this size
		//$this->end_line();
        $this->current_cell_height = 0;
$this->draw_cell(600, $this->vsize, "");    // Blank cell to continue page breaking at this size
		//$this->end_line();
		//$this->end_line();
		//$this->end_line(2);
        $this->inGroupOutput = false;
//$this->draw_cell(600, $this->vsize, "AFTER GROUP HEADER END");
		//$this->end_line();
	}

	function format_group_trailer_start($first=false) // PDF
	{
        $this->any_custom_trailers  = "NONE";
//echo "GROUP TRAILER START DONE $this->inOverflow<BR>";
//$this->debug2("GROUP TRAILER END GO $this->inOverflow<BR>");
        // Tiny padding between group trailers and bofy detail so cell border doesnt overwrite heading underline
        if ( $first )
		    $this->end_line(0);
        $this->apply_style_tags( "GROUPTRAILER", $this->query->output_group_trailer_styles);
//echo "pplay GT<BR>";
//var_dump($this->stylestack["border-edges"]);

		return;
	}

	function format_group_trailer_end($last_trailer = false) // PDF
	{
//echo "GROUP TRAILER END DONE $this->inOverflow<BR>";
//$this->debug2("GROUP TRAILER END DONE $this->inOverflow<BR>");
        $this->unapply_style_tags( "GROUPTRAILER", $this->query->output_group_trailer_styles);
		//$y = $this->document->GetY() -  1;
        //$this->set_position(false, $y);
//$this->debug2("GROUP TRAILER END DONE2 $this->inOverflow<BR>");

		    //$this->end_line(1);
        //$this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);

		return;
	}

	function end_of_page_block() // PDF
	{
		$this->end_line(0);
        $this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);

		return;
	}


	function format_group_custom_trailer_start() // PDF
	{
//$this->debug2("GROUP CUSTOM END GO $this->inOverflow<BR>");

		$this->group_header_start = $this->document->GetY();
		$this->group_header_end = $this->document->GetY();
		return;
	}

	function format_group_custom_trailer_end() // PDF
	{
//$this->debug2("GROUP CUSTOM END DONE $this->inOverflow<BR>");
        $this->set_position(false, $this->group_header_end);
//$this->debug2("GROUP CUSTOM END DONE $this->inOverflow<BR>");
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
                    //if ( trim($style[0] ) == "width" )
                        //$wd = trim($style[1]);
                    //else
                        $styles[trim($style[0])] = trim($style[1]);
                }
            }
        }

        $tx = $this->reportico_string_to_php($tx);
        $tx = reportico_assignment::reportico_meta_sql_criteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
    }

	function format_group_header(&$col, $custom, $calculate_only = false) // PDF format group headers
	{
//$this->debug2("GROUP HEADER GO $this->inOverflow<BR>");
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

            if ( $custom )
            {
		        $prevx = $this->document->GetX();
		        $prevy = $this->document->GetY();
                $this->yjump = 2;
                // Fetch Group Header Label Start Column + display
                //$this->unapply_style_tags( "DEFAULT", $this->mid_page_page_styles);
        
                // Display group header value

                $this->set_position($this->abs_left_margin, $this->group_header_start);

                $wd = $this->abs_print_width;
                $tx = $custom;
                $styles = $this->fetch_cell_styles($tx);
                $tx = $this->reportico_string_to_php($tx);
                $tx = reportico_assignment::reportico_meta_sql_criteria($this->query, $tx);
                $just = "L";
	            $this->apply_style_tags( "GROUPHEADER", $styles);
                //if ( $this->draw_mode == "DRAW" )
                //{
				    //$this->draw_cell_container($wd, $this->vsize + 4, $tx,"PBR",0,$just);
                //}
                $link = false;
//$this->debug2("GROUP HEADER CUSTOM DRAW $tx $this->inOverflow<BR>");
				$this->draw_cell($wd, $this->vsize + 0, $tx,"PBF",0,$just, "T", $link);
	            $this->unapply_style_tags( "GROUPHEADER", $styles);

                $this->end_line();
                //$this->draw_cell($group_data_width, $this->vsize, "");    // Blank cell to continue page breaking at this size
                $y = $this->document->GetY();

                // Store where group header reaches so we know where to start printing after row
                if ( $y > $this->group_header_end )
                    $this->group_header_end = $y;

                if ( $this->yjump )
                    $this->set_position(false, $y + $this->yjump);

                //$this->apply_style_tags( "DEFAULT", $this->mid_page_page_styles);
                $this->set_position($prevx, $prevy);
//echo "format custom header".$this->document->GetY()." ".$this->group_header_end."<BR>";
                continue;
            }

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

            //if ( session_request_item("target_style", "TABLE" ) != "FORM" )
                //$this->unapply_style_tags( "DEFAULT", $this->mid_page_page_styles);

            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->apply_style_tags( "HEADERLABEL", $this->query->output_group_header_label_styles);
		    $this->set_position($group_xpos, $y);
		    $padstring = $group_label;
		    $this->draw_cell( $group_label_width, $this->vsize, "$padstring");
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->unapply_style_tags( "HEADERLABEL", $this->query->output_group_header_label_styles);
		    $this->set_position($group_data_xpos, $y);
    
            // Display group header value
		    $contenttype = $col->derive_attribute( "content_type",  $col->query_name);
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->apply_style_tags( "HEADERVALUE", $this->query->output_group_header_value_styles);

			$qn = get_query_column($col->query_name, $this->query->columns ) ;
		    if ( $contenttype == "graphic"  || preg_match("/imagesql=/", $qn->column_value))
		    {
                if ( $this->draw_mode == "CALCULATE" )
                {
                    if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	                    $this->unapply_style_tags( "HEADERVALUE", $this->query->output_group_header_value_styles);
                    continue;
                }

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
					    $h = $this->draw_image($tmpnam.".png", $group_data_xpos, $y, $width  * $this->pdfImageDPIScale, 0 ) + 2;
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
//$this->debug2("GROUP HEADER NORMAL DRAW $this->inOverflow<BR>");
			    $this->draw_cell($group_data_width, $this->vsize, "$padstring");
		    }
            if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            $this->unapply_style_tags( "HEADERVALUE", $this->query->output_group_header_value_styles);
		    $this->end_line();
		    $this->draw_cell($group_data_width + 200, $this->vsize, "");    // Blank cell to continue page breaking at this size
		    $y = $this->document->GetY();

            // Store where group header reaches so we know where to start printing after row
            if ( $y > $this->group_header_end )
                $this->group_header_end = $y;

		    if ( $this->yjump )
			    $this->set_position(false, $y + $this->yjump);

            //if ( session_request_item("target_style", "TABLE" ) != "FORM" )
	            //$this->apply_style_tags( "DEFAULT", $this->mid_page_page_styles);
        }
//echo "format grup header".$this->document->GetY()." ".$this->group_header_end."<BR>";
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
			$this->draw_cell($wd, $this->vsize + 0, $padstring ,"PBF",0,$just, "B");
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

			$this->draw_image($tmpnam.".png", $this->abs_left_margin + $xaddon, $y, $width * $this->pdfImageDPIScale, $height * $this->pdfImageDPIScale );
			$y = $this->set_position(false, $y + $height);
			$this->end_line();
		}
		unlink($tmpnam.".png");
	}

    function draw_multicell($w, $h, $txt, $border, $align, $fill, $link = false, $keepy = false)
    {
        $oldh = $h;
        $storeY = $this->document->GetY();

        if ( $this->pdfDriver == "tcpdf" )
        {
            if ( $keepy )
                $this->document->Multicell($w,$h, $txt, $border, $align, $fill, 0);
            else
                $this->document->Multicell($w,$h, $txt, $border, $align, $fill,1);
        }
        else
        {
            $this->document->MultiCell($w,$h, $txt, $border, $align, $fill, $link);
        }
        $h = $this->document->GetY() - $storeY;
        return $h;
    }

    function draw_image($file, $x, $y, $w, $h, $hidden = false)
    {
        if ( $this->pdfDriver == "tcpdf" )
        {
                //$y = $this->document->GetY();
		        $h = $this->document->Image($file, $x, $y, $w, $h, '', '', '', false, 300, '', false, false, 0, false, 0, false, $hidden);
                $h = $this->document->getImageRBY() - $y;
                if ( $h < 0 )
                    $h = 0;
        }
        else
        {
            if ( $hidden )
		        $h = $this->document->ImageHeight($file, $x, $y, $w, $h);
            else
		        $h = $this->document->Image($file, $x, $y, $w, $h);
        }
        return $h;
    }

    /*
     * Apply styles to report detail block
     */
	function format_report_detail_start() // PDF
	{
//echo "REPORT DETAIL START<BR>";
		reportico_report::format_report_detail_start();


        // Top wrapper
//var_dump($this->query->output_page_styles);
//var_dump($this->top_page_page_styles);
//var_dump($this->mid_page_page_styles);
//var_dump($this->bottom_page_page_styles);
//die;
    }

    /*
     * Apply styles to report detail block
     */
	function format_report_detail_end()
	{
//echo "REPORT DETAIL END<BR>";
		reportico_report::format_report_detail_end();
//$this->draw_cell(600, $this->vsize, "debugUNAPPLIED Report BLOCK");    // Blank cell to continue page breaking at this size
        //$this->unapply_style_tags( "REPDETMIDPAGE2", $this->mid_page_page_styles);
//$this->draw_cell(600, $this->vsize, "debugUNAPPLIED Report BLOCK");    // Blank cell to continue page breaking at this size
    }


	function format_headers() // PDF
	{
        if ( $this->inOverflow )
            return;

        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RMB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP1", $this->top_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RMB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP1", $this->top_page_page_styles, true);

        if ( session_request_item("target_style", "TABLE" ) == "FORM" )
            return;

        // Start page block
//var_dump($this->top_page_page_styles);
//var_dump($this->stylestack);
//die;
        //$this->draw_mode = "CALCULATE";
        //$this->new_report_page_line_by_style("REPTOPPAGE", $this->query->output_page_styles, true);
        //$this->draw_mode = "DRAW";
        //$this->new_report_page_line_by_style("REPTOPPAGE", $this->query->output_page_styles, true);
echo "HEAD<BR>";
$this->debugFile("\nHEADERS");
/*
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RBB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HBP1", $this->bottom_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RBB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HBP1", $this->bottom_page_page_styles, true);
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RBB2", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HBP2", $this->bottom_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RBB2", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HBP2", $this->bottom_page_page_styles, true);
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RMB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP1", $this->top_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RMB1", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP1", $this->top_page_page_styles, true);
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RMB2", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP2", $this->top_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RMB2", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP2", $this->top_page_page_styles, true);
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RMB3", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP3", $this->query->output_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RMB3", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP3", $this->query->output_page_styles, true);
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("RMB4", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP4", $this->query->output_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("RMB4", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("HMP4", $this->query->output_page_styles, true);
//die;
        //$this->draw_mode = "CALCULATE";
        //$this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);
        //$this->draw_mode = "DRAW";
        //$this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);
        //$this->draw_mode = "CALCULATE";
        //$this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);
        //$this->draw_mode = "DRAW";
        //$this->new_report_page_line_by_style("REPBOTPAGE", $this->bottom_page_page_styles, true);
        //$this->remove_style_tags( "REPDETBOTPAGE", $this->top_page_reportbody_styles, "margin", "top");
//var_dump($this->top_page_page_styles);
//var_dump($this->stylestack);
//die;
*/
        //$this->draw_mode = "CALCULATE";
        //$this->new_report_page_line_by_style("REPMIDPAGE", $this->mid_page_page_styles, true);
        //$this->draw_mode = "DRAW";
        //$this->new_report_page_line_by_style("REPTPOPPAGE", $this->mid_page_page_styles, true);
        ////$this->draw_mode = "CALCULATE";
        ////$this->new_report_page_line_by_style("REPMIDPAGE", $this->mid_page_page_styles, true);
        ////$this->draw_mode = "DRAW";
        //$this->new_report_page_line_by_style("REPMIDPAGE", $this->mid_page_page_styles, true);
        //$this->new_report_page_line_by_style("REPMIDPAGE", $this->mid_page_page_styles, true);
        //$this->new_report_page_line_by_style("REPMIDPAGE", $this->mid_page_page_styles, true);
        //$this->apply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);

        //$this->apply_style_tags( "EACHHEADMID", array("background-color" => "#ffaaff"));
        //$this->draw_mode = "CALCULATE";
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
        //$this->draw_mode = "DRAW";
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
        //$this->end_line(0);
        //$this->draw_mode = "CALCULATE";
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
        //$this->draw_mode = "DRAW";
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
        //$this->unapply_style_tags( "EACHHEADMID", array("background-color" => "#ffaaff"));
        //$this->unapply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);

        // Handle multi line headers by processing all headers 
        // in "CALCULATE" mode and then print them on the appropriate line
        $this->draw_mode = "CALCULATE";
        $this->current_line_height = 0;
        $this->apply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
		foreach ( $this->columns as $w )
		{
            $this->apply_style_tags( "HEADERS", $this->query->output_header_styles);
            $this->format_column_header($w);
            $this->unapply_style_tags( "HEADERS", $this->query->output_header_styles);
       	}
		//$this->draw_cell(500, $this->vsize, "ooklgjlkgjldgjldfjgldfjgldfjgldjglfdjldjfglps");    // Blank cell to continue page breaking at this size
        $this->unapply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);

   		$this->draw_mode = "DRAW";
        $this->check_page_overflow();

        // Page Styles
        $this->new_report_page_line_by_style("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("LINE5PAGE", $this->mid_page_page_styles, false);

        $this->apply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);
		foreach ( $this->columns as $w )
        {
            $this->apply_style_tags( "HEADERS", $this->query->output_header_styles);
            $this->format_column_header($w);
            $this->unapply_style_tags( "HEADERS", $this->query->output_header_styles);
        }

        // Page Styles
        $this->unapply_style_tags( "EACHHEADMID", $this->mid_row_page_styles);

	    $this->unapply_style_tags( "PAGEBODY", $this->query->output_reportbody_styles);
		$this->end_line();
		$this->draw_cell(5, $this->vsize, "");    // Blank cell to continue page breaking at this size
	    $this->apply_style_tags( "PAGEBODY", $this->query->output_reportbody_styles);

        // Tiny padding between column headers and rows so cell border doesnt overwrite heading underline
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


	function format_column(& $column_item) // PDF
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
                    if ( $this->draw_mode == "CALCULATE" )
                    {
					    $h = $this->draw_image($tmpnam.".png", $x, $y, $width * $this->pdfImageDPIScale, 0, true ) + 2;
                    }
                    else
                    {
					    $h = $this->draw_image($tmpnam.".png", $x, $y, $width * $this->pdfImageDPIScale, 0 ) + 2;
                    }
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

                $this->allcell_styles = array("border-edges" => "");
                $this->cell_styles = array("border-edges" => "");
			    $this->apply_style_tags( "COLUMNALL", $this->query->output_allcell_styles, false, false, "ALLCELLS");
			    $this->apply_style_tags( "COLUMNCELL", $column_item->output_cell_styles, false, false, "CELLS");
                $this->apply_row_border_to_cell ();
                if ( $this->draw_mode == "DRAW" )
                {
				    //$this->draw_cell_container($wd, $this->vsize + 4, $k,"PBR",0,$just);
                }
                $link = false;
                if ( $column_item->output_hyperlinks )
                    $link = $column_item->output_hyperlinks["url"];
                //$this->max_line_border_addition = 0;
				$this->draw_cell($wd, $this->required_line_height, "$k","P",0,$just, "T", $link); //PPP
			    $this->unapply_style_tags( "COLUMNCELL", $column_item->output_cell_styles);
			    $this->unapply_style_tags( "COLUMNALL", $this->query->output_allcell_styles);
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
        if (  isset ( $this->row_styles ["border-edges" ] ) && $this->row_styles ["border-edges" ])
        {
            $cellstyle = $this->cell_styles ["border-edges" ];
            if ( !$cellstyle )
            {
                $cellstyle = $this->allcell_styles ["border-edges" ];
            }
            $rowstyle = $this->row_styles ["border-edges" ];
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
            end($this->stylestack["border-edges"]);
            $this->stylestack["border-edges"][key($this->stylestack["border-edges"])] = $cellstyle;
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

    function remove_style_tags ( $type, &$work_styleset, $want, $bit = false)
    {
//echo $type." $want $bit<BR>";
//var_dump($work_styleset);
        if ( $work_styleset && is_array($work_styleset) )
        {
            foreach ( $work_styleset as $k => $v )
            {
                if ( $k != $want )
                    continue;
            
                if ( !$bit )
                {
                    unset ( $work_styleset[$k] );
                    return;
                }
                if ( $k == "margin" || $k == "padding" || $k == "border-width" )
                {
                    $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                    //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                    $ar = explode ( " ", $v);
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
                        { $tmp[0] = "0mm"; $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                            $tmp[3] = $ar[2];
                        }
                        else if ( count($ar) == 4 )
                        {
                            $tmp[0] = $ar[0];
                            $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                            $tmp[3] = $ar[3];
                        }
                     if ( $bit == "top" ) $tmp[0] = "0";
                     if ( $bit == "right" ) $tmp[1] = "0";
                     if ( $bit == "bottom" ) $tmp[2] = "0";
                     if ( $bit == "left" ) $tmp[3] = "0";
                     $work_styleset[$k] = $tmp[0]." ".$tmp[1]." ".$tmp[2]." ".$tmp[3];
                }
                if ( $k == "margin-top" )
                {
                    unset($work_styleset[$k]);
                }
                if ( $k == "position" )
                {
                    unset($work_styleset[$k]);
                }
            }
        }
//echo "REMOVED: $type ";
//var_dump($work_styleset);
    }

    function extract_style_tags ( $type, $styleset, $want, $bit )
    {
        $work_styleset =& $styleset;

        if ( $work_styleset && is_array($work_styleset) )
        {
            foreach ( $work_styleset as $k => $v )
            {
                if ( $k != $want )
                    continue;
            
                if ( isset ( $this->stylestack[$k] ) )
                {
                    if ( $k == "margin" )
                    {
                        $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode ( " ", $v);
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
                        if ( $bit == "top" ) return $tmp[0];
                        if ( $bit == "left" ) return $tmp[3];
                        if ( $bit == "right" ) return $tmp[1];
                        if ( $bit == "bottom" ) return $tmp[2];
                        return $tmp;
                    }

                    if ( $k == "padding" )
                    {
                        $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode ( " ", $v);
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
                        if ( $bit == "top" ) return $tmp[0];
                        if ( $bit == "left" ) return $tmp[3];
                        if ( $bit == "right" ) return $tmp[1];
                        if ( $bit == "bottom" ) return $tmp[2];
                        return $tmp;
                    }

                    if ( $k == "border-width" )
                    {
                        $tmp = "";
                        $v = preg_replace("/px/", "", trim($v));
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode ( " ", $v);
                        $borderwidth = 0;
                        if ( $ar )
                        {
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
                        }
                        if ( $bit == "top" ) return $tmp[0];
                        if ( $bit == "left" ) return $tmp[3];
                        if ( $bit == "right" ) return $tmp[1];
                        if ( $bit == "bottom" ) return $tmp[2];
                        return $tmp;
                    }
                    if ( $k == "font-family" )
                    {
                        $this->document->SetFont($v);
                    }
                    if ( $k == "font-size" )
                    {
                        $sz = preg_replace("/[^0-9].*/", "", $v);
                        $this->document->SetFontSize($sz);
                        $v = $sz + $this->vspace;
                        $this->vsize = $v;
                    }
                    if ( $k == "font-style" )
                    {
                        $currWeight = end( $this->stylestack["font-weight"]);
                        $currFamily = end( $this->stylestack["font-family"]);
                        $pdfStyle = "";
                        switch ( $currWeight )
                        {
                            case "bold": $pdfStyle .= "B"; break;
                            default: $pdfStyle .= "";
                        }
                        switch ( $v )
                        {
                            case "italic": $pdfStyle .= "I"; break;
                            default: $pdfStyle .= "";
                        }
                        $this->document->SetFont($currFamily, $pdfStyle);
                        //$v = $sz + $this->vspace;
                        //$this->vsize = $v;
                    }
                    if ( $k == "font-weight" )
                    {
                        $currStyle = end( $this->stylestack["font-style"]);
                        $currFamily = end( $this->stylestack["font-family"]);
                        $pdfStyle = "";
                            switch ( $v )
                        {
                            case "bold": $pdfStyle .= "B"; break;
                            default: $pdfStyle .= "";
                        }
                        switch ( $currStyle )
                        {
                            case "italic": $pdfStyle .= "I"; break;
                            default: $pdfStyle .= "";
                        }
                        $this->document->SetFont($currFamily, $pdfStyle);
                        //$v = $sz + $this->vspace;
                        //$this->vsize = $v;
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
        //echo "&nbsp;&nbsp;APPLY: $type<BR> ";
        //var_dump($this->stylestack["type"]);
    }

        
    function apply_style_tags ( $type, $styleset, $parent_styleset = false, $grandparent_styleset = false, $apply_type = false, $applyto = false )
    {
        $styleset["type"] = $type;
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
                    // Dont apply anything except the applyto specified
                    if ( $applyto && $ct == 3 && $applyto != $k )
                        continue;
                
                    if ( isset ( $this->stylestack[$k] ) )
                    {
                        if ( $k == "margin" )
                        {
                            $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode ( " ", $v);
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

                        if ( $k == "padding" )
                        {
                            $tmp = array ( 0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode ( " ", $v);
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
//echo "apply border".count($this->stylestack["border-edges"])."<BR>";
                            $tmp = "";
                            $v = preg_replace("/px/", "", trim($v));
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode ( " ", $v);
                            $borderwidth = 0;
                            if ( $ar )
                            {
                                foreach ( $ar as $vv )
                                {
                                    if ( substr($vv, 0, 1) != "0" )
                                    {
                                        $borderwidth = $vv;
                                        break;
                                    }
                                }
                                if ( count($ar) == 1 && $ar[0] > 0 ) 
                                {
                                    $tmp = "LBTR";
                                }
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
                            }
                            $borderedges = $tmp;
                            $v = $borderwidth;
                            if ( $apply_type == "ROW" )
                            {
                                $this->row_styles["border-width"] = $borderwidth;
                                $this->row_styles["border-edges"] = $v;
                            }
                            if ( $apply_type == "ALLCELLS" )
                            {
                                $this->allcell_styles["border-width"] = $borderwidth;
                                $this->allcell_styles["border-edges"] = $v;
                            }
                            if ( $apply_type == "CELLS" )
                            {
                                $this->cell_styles["border-width"] = $borderwidth;
                                $this->cell_styles["border-edges"] = $v;
                            }
$this->debugFile("SET BORDER $apply_type $borderedges $v");
                            array_push ( $this->stylestack["border-edges"], $borderedges);
                        }
                        if ( $k == "font-family" )
                        {
                            $this->document->SetFont($v);
                        }
                        if ( $k == "font-size" )
                        {
                            $sz = preg_replace("/[^0-9].*/", "", $v);
                            $this->document->SetFontSize($sz);
                            $v = $sz + $this->vspace;
                            $this->vsize = $v;
                        }
                        if ( $k == "font-style" )
                        {
                            $currWeight = end( $this->stylestack["font-weight"]);
                            $currFamily = end( $this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ( $currWeight )
                            {
                                case "bold": $pdfStyle .= "B"; break;
                                default: $pdfStyle .= "";
                            }
                            switch ( $v )
                            {
                                case "italic": $pdfStyle .= "I"; break;
                                default: $pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ( $k == "font-weight" )
                        {
                            $currStyle = end( $this->stylestack["font-style"]);
                            $currFamily = end( $this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ( $v )
                            {
                                case "bold": $pdfStyle .= "B"; break;
                                default: $pdfStyle .= "";
                            }
                            switch ( $currStyle )
                            {
                                case "italic": $pdfStyle .= "I"; break;
                                default: $pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
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
        //echo "APPLY: $type ";
        //echo "&nbsp;&nbsp;APPLY: $type ";
        //var_dump($this->stylestack["type"]);
        //var_dump($this->stylestack["background-color"]);
    }

    function unapply_style_tags ( $type1, $styleset, $parent_styleset = false, $grandparent_styleset = false, $type = "", $applyto = false )
    {
if ( !$type1 )
{
echo "<PRE>"; debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); echo "</PRE>";
die;
}
/*
echo $type1;
$val = $this->stylestack["type"][count($this->stylestack["type"]) -1 ];
if ( $val != $type1 )
{
echo "<PRE>"; debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); echo "</PRE>";
echo " UNAPPLY $val = $type1 !!<BR>";
    echo "DIFFERENT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<BR>";
}
//if ( count($this->stylestack["type"]) < 2 )
//{
//echo "<PRE>"; debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS); echo "</PRE>";
//}
if ( count($this->stylestack["type"]) == 3  && $this->stylestack["type"][2] == "REPDETMIDPAGE")
{
echo "YESSSSS<PRE>";
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
echo "</PRE>";
}
*/
        $styleset["type"] = $type;
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
                    // Dont apply anything except the applyto specified
                    if ( $applyto && $ct == 3 && $applyto != $k )
                        continue;
                
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
                        if ( $k == "font-family" )
                        {
                            $this->document->SetFont($value);
                        }
                        if ( $k == "font-style" )
                        {
                            $currWeight = end( $this->stylestack["font-weight"]);
                            $currFamily = end( $this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ( $currWeight )
                            {
                                case "bold": $pdfStyle .= "B"; break;
                                default: $pdfStyle .= "";
                            }
                            switch ( $value )
                            {
                                case "italic": $pdfStyle .= "I"; break;
                                default: $pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ( $k == "font-weight" )
                        {
                            $currStyle = end( $this->stylestack["font-style"]);
                            $currFamily = end( $this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ( $value )
                            {
                                case "bold": $pdfStyle .= "B"; break;
                                default: $pdfStyle .= "";
                            }
                            switch ( $currStyle )
                            {
                                case "italic": $pdfStyle .= "I"; break;
                                default: $pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
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
                        if ( $k == "border-width" )
                        {
//echo "unapply border".count($this->stylestack["border-edges"])."<BR>";
//debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                            $value = array_pop ( $this->stylestack["border-edges"] );
                        }
                    }
                }
            }
        }
        //echo "UNAPPLY: $type1 ";
//if ( preg_match("/MIDPAGE/", $type1) )
        //echo "&nbsp;&nbsp;UNAPPLY: $type1<BR> ";
        //var_dump($this->stylestack["type"]);
        //var_dump($this->stylestack["background-color"]);
//if ( !$this->stylestack["background-color"] )
//{
//echo "NOBACK!!<BR><PRE>";
//debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
//echo "NOBACK!!<BR></PRE>";
//}
    }

    function new_report_page_line($txt = "")
    {
        $this->new_report_page_line_by_style("LINEBODY$txt", $this->mid_page_reportbody_styles, false);
        $this->new_report_page_line_by_style("LINEPAGE$txt", $this->mid_page_page_styles, false);
    }

    function new_report_page_line_by_style($txt = "", &$styles, $blankline = false)
    {
        // Line page wrapper
//echo "NEW LINE $txt <BR>";

        $this->apply_style_tags( "$txt", $styles);
        $tw = $styles["style_start"];
        $wd = $styles["style_width"];
        $this->set_position($tw);
        if ( $blankline )
        {
            $oldSize = $this->document->GetFontSizePt();
            $this->document->SetFontSize(10);
            //$this->draw_cell($wd, 0, "dp $txt $this->max_line_height, $this->max_line_border_addition, $this->max_line_padding_addition = $this->current_line_height");    // Blank cell to continue page breaking at this size
            $this->draw_cell($wd, 0, "");    // Blank cell to continue page breaking at this size
            $this->document->SetFontSize($oldSize);
            if ( $this->draw_mode == "DRAW" )
            {
               $this->end_line(0);
                $this->debugFile("NEW BLANK $txt");
            }
        }
        else
        {
            //$this->draw_cell($wd, $this->max_line_height + $this->max_line_border_addition + $this->max_line_padding_addition, "X$txt np LH.$this->max_line_height, BA $this->max_line_border_addition, MLA $this->max_line_padding_addition");    // Blank cell to continue page breaking at this size
            //$this->draw_cell($wd, $this->max_line_height, "X$txt np LH.$this->max_line_height, BA $this->max_line_border_addition, MLA $this->max_line_padding_addition");    // Blank cell to continue page breaking at this size
            $oldSize = $this->document->GetFontSizePt();
            $this->document->SetFontSize(10);
            $this->draw_cell($wd, $this->max_line_height + $this->max_line_border_addition, $txt ." ".$this->max_line_height);    // Blank cell to continue page breaking at this size
            $this->document->SetFontSize($oldSize);
            $this->debugFile("NEW LINE $txt");
        }

//$this->document->Multicell(80,24, "test $this->draw_mode", false, false, false, 0);
        $this->unapply_style_tags( "$txt", $styles);
    }

	function each_line($val) // PDF
	{
//$this->debug2(".");        
        if ( !$this->columns_calculated )
        {
            // Calulate position and width of column detail taking into account
            // Report Body and Page styles
            $this->calculateColumnMetrics();
            $this->columns_calculated = true;
        }


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

            $this->apply_style_tags( "ROW", $this->query->output_before_form_row_styles);
		    $y = $this->document->GetY();
		    $this->set_position($this->abs_left_margin, $y);
            $this->unapply_style_tags( "ROW", $this->query->output_before_form_row_styles);

		    foreach ( $this->query->groups as $val )
            {
			    for ($i = 0; $i < count($val->headers); $i++ )
			    {
				    $col =& $val->headers[$i]["GroupHeaderColumn"];
				    $this->format_group_header($col, false);
                    $totheaderheight += $this->calculated_line_height;
			    }
            }
            foreach ( $this->query->display_order_set["column"] as $k => $w )
		    {
		        if ( $w->attributes["column_display"] != "show")
					    continue;
                $ct++;

				$this->format_group_header($w, false);
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
                $this->apply_style_tags( "AFTERFORMROW", $this->query->output_after_form_row_styles);
		        $y = $this->document->GetY();
		        $this->set_position($this->abs_left_margin, $y);
		        $this->draw_cell($this->abs_right_margin - $this->abs_left_margin, $this->vsize, "RR");    // Blank cell to continue page breaking at this size
                $this->unapply_style_tags( "AFTERFORMROW", $this->query->output_after_form_row_styles);
                $this->end_line();

            }

            return;
        }

		$y = $this->document->GetY();
		$this->check_graphic_fit();
//$this->draw_cell(50, $this->vsize, "E");    // Blank cell to continue page breaking at this size
		
		$this->yjump = 0;
        $this->debugFile("======================================\nDATA ROW $this->draw_mode\n");
//$this->document->Multicell(80,24, "X $this->draw_mode", false, false, false, 0);
		if ( $this->body_display == "show" && get_reportico_session_param("target_show_detail") )
		{
            $this->row_styles = array();
			$this->apply_style_tags( "ROW2", $this->query->output_row_styles, false, false, "ROW");

            $this->draw_mode = "CALCULATE";
            $this->no_columns_printed = 0;
            $this->no_columns_to_print = 0;
			foreach ( $this->columns as $col )
				$this->format_column($col);
            $this->unapply_style_tags( "ROW2", $this->query->output_row_styles);

            $this->draw_mode = "DRAW";

            $this->check_page_overflow();

            // Line page wrapper
echo "EA MID $this->max_line_height $this->current_line_height<B>";
            $this->new_report_page_line_by_style("LINE5PAGE", $this->mid_page_reportbody_styles, false);
            $this->new_report_page_line_by_style("LINE2PAGE", $this->mid_page_page_styles, false);
//die;

            // Page Styles
            $this->apply_style_tags( "EACHLINEMID", $this->mid_row_page_styles, false, false, "ROW");

			    // Row Styles
                $this->apply_style_tags( "ROW3", $this->query->output_row_styles, false, false, "ROW");
            
                $this->no_columns_printed = 0;
			    foreach ( $this->columns as $col )
			     $this->format_column($col);

			    $this->page_line_count++;

                $this->unapply_style_tags( "ROW3", $this->query->output_row_styles);

            $this->unapply_style_tags( "EACHLINEMID", $this->mid_row_page_styles);

            $nextliney = $this->document->GetY() + $this->max_line_height;
//$this->document->Multicell(160,30, "", false, false, false, 0);
//$this->document->Multicell(160,30, "dp $this->max_line_height, $this->max_line_border_addition, $this->max_line_padding_addition = $this->current_line_height", false, false, false, 0);
			$this->end_line();
            //$this->set_position(false, $nextliney - 1);
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
        if ( $this->inOverflow )
            return;
        $this->inOverflow = true;
//$this->debug2("CHKOVR" . $this->max_line_height . " > ".$this->page_footer_start_y);

        $y = $this->document->GetY();
		//if ( $y + $this->calculated_line_height > $this->abs_bottom_margin )
		if ( $y + $this->max_line_height > $this->page_footer_start_y )
		{
            // Between page breaks store any current lin eparameters
            $prev_calculated_line_height = $this->calculated_line_height;
            $prev_max_line_height = $this->max_line_height;
$this->debug2("--------------------new page", true);
			$this->finish_page();
$this->debug2("--------------------throw page", true);
			$this->begin_page();
			//$this->before_group_headers();
			$this->page_line_count++;
            $this->calculated_line_height = $prev_calculated_line_height;
            $this->max_line_height = $prev_max_line_height;
$this->debug2("--------------------done page", true);
		}
        $this->inOverflow = false;
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
						$h = $this->draw_image($tmpnam.".png", 0, 0, $width, 0, true );
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


    function set_position( $x = false, $y = false )
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

		$this->debug2("<BR>Begin PAGE<BR>");

        $this->page_footer_start_y = $this->abs_bottom_margin;
        $this->page_header_start_y = $this->abs_top_margin;
        $this->page_number++;
		$this->document->AddPage($this->orientations[$this->orientation]);

		$font = $this->document->SetFont($this->fontName, "I", 24);
		$font = $this->document->SetFontSize($this->vsize);

		$this->set_position($this->abs_left_margin, $this->abs_top_margin);
        $this->current_line_start_y = $this->document->GetY();

//$this->draw_cell(600, $this->vsize, "debug BEGIN PAGE $this->vsize");    // Blank cell to continue page breaking at this size

        // Page Headers
		reportico_report::page_headers();
        $prevx = $this->document->GetX();
        $prevy = $this->document->GetY();
		$this->end_line();
		$this->end_line();

        // Page Footers
		$this->page_footers();
		//$this->document->SetAutoPageBreak(true, $this->abs_page_height - $this->abs_bottom_margin );

	    //$this->apply_style_tags( "DEFAULT", $this->mid_page_page_styles);
		$this->set_position($prevx, $this->page_header_end_y  );

		$this->group_header_start = 0;
        $this->group_header_end = 0;

        // Start report body
        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("REPTOPBODY", $this->top_page_reportbody_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("REPTOPBODY", $this->top_page_reportbody_styles, true);

	    $this->apply_style_tags( "PAGEBODY", $this->query->output_reportbody_styles);

	}

	function finish_page()
	{
		$this->debug("Finish Page");

        // if page styles on turn them off
        if ( $this->detail_started )
        {
            $this->format_report_detail_end();
        }

	    //PPP$this->unapply_style_tags( "DEFAULT", $this->mid_page_page_styles);
	    $this->unapply_style_tags( "PAGEBODY", $this->query->output_reportbody_styles);

        $this->draw_mode = "CALCULATE";
        $this->new_report_page_line_by_style("REPTOPPAGE", $this->bottom_page_reportbody_styles, true);
        $this->draw_mode = "DRAW";
        $this->new_report_page_line_by_style("REPTOPPAGE", $this->bottom_page_reportbody_styles, true);

//echo "PAGE END<BR>";
		//$this->page_footers();
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
		
        $tx = $header->text;
//echo "PH $tx ".$this->page_header_end_y."<BR>";
        $styles = $this->fetch_cell_styles($tx);
	    $this->apply_style_tags( "PAGEHEADER", $styles);
		$this->draw_cell($wd, $this->vsize, $tx, "PBF", 0, $just );
	    $this->unapply_style_tags( "PAGEHEADER", $styles);
		$this->end_line();
        $y = $this->last_draw_end_y;
        if ( $y > $this->page_header_end_y )
        {
//echo "inc<BR>";
            $this->page_header_end_y  = $y + 10;
        }
//echo "PE ".$this->page_header_end_y."<BR>";
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

        $this->page_footer_start_y = $y;

        $tx = $footer->text;
        $styles = $this->fetch_cell_styles($tx);
	    $this->apply_style_tags( "PAGEFOOTER", $styles);
		$this->debug2("<BR>DRAW FOOT<BR>");
		$this->draw_cell($wd, $this->vsize, $tx, "PBF", 0, $just);
	    $this->unapply_style_tags( "PAGEFOOTER", $styles);
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

    function debugFile( $txt )
    { 
        if ( !$this->debugFp )
            $this->debugFp = fopen ( "/tmp/fp.out", "w" );

        if ( $txt == "FINISH" )
            fclose($this->debugFp);
        else
            fwrite ( $this->debugFp, "$txt => Max $this->max_line_height Curr $this->current_line_height \n" );

    } 




}

