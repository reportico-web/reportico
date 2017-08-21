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

 * File:        Report_pdf.php
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

class ReportTCPDF extends Report
{
    public $dbg = false;
    public $abs_top_margin;
    public $abs_bottom_margin;
    public $abs_row_left_margin;
    public $abs_col_left_margin;
    public $abs_left_margin;
    public $abs_right_margin;
    public $abs_page_width = 0;
    public $abs_page_height = 0;
    public $abs_print_width = 0;
    public $orientation;
    public $page_type;
    public $column_order;
    public $fontName;
    public $fontSize;
    public $vsize;
    public $column_header_required = false;
    public $columns_calculated = false;
    public $fillmode = false;
    public $justifys = array(
        "right" => "R",
        "centre" => "C",
        "center" => "C",
        "left" => "L",
    );
    public $orientations = array(
        "Portrait" => "P",
        "Landscape" => "L",
    );
    public $page_types = array(
        "B5" => array("height" => 709, "width" => 501),
        "A6" => array("height" => 421, "width" => 297),
        "A5" => array("height" => 595, "width" => 421),
        "A4" => array("height" => 842, "width" => 595),
        "A3" => array("height" => 1190, "width" => 842),
        "A2" => array("height" => 1684, "width" => 1190),
        "A1" => array("height" => 2380, "width" => 1684),
        "A0" => array("height" => 3368, "width" => 2380),
        "US-Letter" => array("height" => 792, "width" => 612),
        "US-Legal" => array("height" => 1008, "width" => 612),
        "US-Ledger" => array("height" => 792, "width" => 1224),
    );
    public $yjump = 0;
    public $vspace = 0;

    // Maintains record of how high a line is so the next line will be at the right
    // place
    public $current_line_start_y = 0;
    public $current_line_height = 0;
    public $actual_line_height = 0;
    public $calculated_line_height = 0;
    public $max_line_height = 0;
    public $required_line_height = 0;
    public $max_line_border_addition = 0;
    public $max_line_padding_addition = 0;
    public $max_border_top_height = 0;
    public $max_border_bottom_height = 0;
    public $last_cell_width = 0;
    public $last_cell_xpos = 0;

    // Maintains record of where group headers and where they reach
    // so we can place them effectively
    public $group_header_start = 0;
    public $group_header_end = 0;

    // Maintains footers  start point so we dont over flow into them
    public $page_header_end_y = 0;
    public $page_footer_start_y = 0;
    public $last_draw_end_y = 0;

    // Array of styles. Each style type is a stack that maintains the current
    // Text colour background colour etc
    public $stylestack;

    // Keeps track of how many cells in current line printed/to print
    public $no_columns_printed = 0;
    public $no_columns_to_print = 0;

    // Holds selected styles for rows/cells/allcells so they can merged
    public $row_styles = array();
    public $allcell_styles = array();
    public $cell_styles = array();
    public $criteria_styles = array();

    // Drawing mode, in Calculate mode we run through a line of values calculating
    // total width and height and then draw all text elements in Draw mode based
    // on knowing how wide things are
    public $draw_mode = "DRAW";

    // Factor to apply to image pixel size to get them to show at correct size in PDF document
    public $pdfImageDPIScale = 0.72;

    // PDF Driver
    public $pdfDriver = "tcpdf";

    public $inGroupOutput = false;

    // Page and body styles in middle of page should not have thinkgs like
    // top margin or position applied except on first print in page/body. This is used to
    // hold modified page styles for mid page use
    public $all_page_page_styles = false;
    public $top_page_page_styles = false;
    public $mid_page_page_styles = false;
    public $bottom_page_page_styles = false;
    public $mid_row_page_styles = false;
    public $mid_cell_criteria_styles = false;
    public $top_page_criteria_styles = false;
    public $mid_page_criteria_styles = false;
    public $bottom_page_criteria_styles = false;
    public $mid_cell_reportbody_styles = false;
    public $top_page_reportbody_styles = false;
    public $mid_page_reportbody_styles = false;
    public $bottom_page_reportbody_styles = false;
    public $all_page_row_styles = false;
    public $mid_cell_row_styles = false;
    public $all_page_criteria_styles = false;

    public $debugFp = false;
    public $page_detail_started = false;
    public $page_broken_mid_page = false;
    public $ignore_height_checking = false;

    public $group_headers_custom_drawn = 0;
    public $group_headers_drawn = 0;

    public $page_footer_wrapper_offset = 0;

    public $cell_row_top_addition = 0;
    public $cell_row_bottom_addition = 0;

    public function __construct()
    {
        $this->column_spacing = 0;
    }

    public function debug2($txt, $divide = false)
    {
        if ($divide) {
            echo "<BR><BR>";
        }

        if ($this->dbg) {
            echo "[" . $this->document->GetY() . ": " . $txt . "]";
        }

        $txt = "[" . $this->document->GetY() . ": " . $txt . "]" . $txt;
        if ($divide) {
            echo "<BR><BR>";
        }

        $this->drawMulticell(300, 30, "$txt", false, false, false, false, true);
        echo $txt;
    }

    // For each line reset styles to default values
    public function setDefaultStyles()
    {
        Report::setDefaultStyles();

        // Default column headers to underlined if not specified
        if (!$this->query->output_header_styles) {
            $this->query->output_header_styles["requires-before"] = "0";
            $this->query->output_header_styles["border-style"] = "solid";
            $this->query->output_header_styles["border-width"] = "0 0 1 0";
            $this->query->output_header_styles["border-color"] = array(0, 0, 0);
        }

        if (!$this->query->output_before_form_row_styles) {
            $this->query->output_before_form_row_styles["border-style"] = "solid";
            $this->query->output_before_form_row_styles["border-width"] = "0 0 0 0";
            $this->query->output_before_form_row_styles["border-color"] = array(0, 0, 0);
        }

        if (!$this->query->output_criteria_styles) {
            $this->query->output_criteria_styles["border-style"] = "solid";
            $this->query->output_criteria_styles["background-color"] = "#aaaaaa";
            $this->query->output_criteria_styles["border-width"] = "1px 1px 1px 1px";
            $this->query->output_criteria_styles["border-color"] = array(0, 0, 0);
            $this->query->output_criteria_styles["margin"] = "0px 5px 10px 5px";
            $this->query->output_criteria_styles["padding"] = "0px 5px 0px 5px";
        }

        if (!$this->query->output_after_form_row_styles) {
            $this->query->output_after_form_row_styles["border-style"] = "solid";
            $this->query->output_after_form_row_styles["border-width"] = "1 0 0 0";
            $this->query->output_after_form_row_styles["border-color"] = array(0, 0, 0);
        }

        if (!$this->query->output_group_header_styles) {
            $this->query->output_group_header_styles["requires-before"] = "0";
        }

        if (!$this->query->output_group_trailer_styles) {
            $this->query->output_group_trailer_styles["border-style"] = "solid";
            $this->query->output_group_trailer_styles["border-width"] = "1 0 1 0";
            $this->query->output_group_trailer_styles["border-color"] = array(0, 0, 0);
        }

        // Turn off page header and body background as its too complicated for now
        if (isset($this->query->output_reportbody_styles["background-color"])) {
            unset($this->query->output_reportbody_styles["background-color"]);
        }

        if (isset($this->all_page_page_styles["background-color"])) {
            unset($this->all_page_page_styles["background-color"]);
        }

    }

    public function start()
    {
        Report::start();
        $this->debug("PDF Start **");

        // Set default page size, margins, fonts etc
        $this->page_line_count = 0;
        $this->fontName = $this->query->getAttribute("pdfFont");
        $this->fontSize = $this->query->getAttribute("pdfFontSize");
        $this->vsize = $this->fontSize + $this->vspace;
        $this->orientation = $this->query->getAttribute("PageOrientation");
        $this->page_type = $this->query->getAttribute("PageSize");

        if ($this->orientation == "Portrait") {
            $this->abs_page_width = $this->page_types[$this->page_type]["width"];
            $this->abs_page_height = $this->page_types[$this->page_type]["height"];
        } else {
            $this->abs_page_width = $this->page_types[$this->page_type]["height"];
            $this->abs_page_height = $this->page_types[$this->page_type]["width"];
        }
        $this->abs_top_margin = $this->absPagingHeight($this->query->getAttribute("TopMargin"));
        $this->abs_bottom_margin = $this->abs_page_height -
        $this->absPagingHeight($this->query->getAttribute("BottomMargin"));
        $this->abs_right_margin = $this->abs_page_width -
        $this->absPagingWidth($this->query->getAttribute("RightMargin"));
        $this->abs_left_margin = $this->absPagingWidth($this->query->getAttribute("LeftMargin"));
        $this->abs_print_width = $this->abs_right_margin - $this->abs_left_margin;
        $this->abs_row_left_margin = $this->abs_left_margin;
        $this->abs_col_left_margin = $this->abs_left_margin;
        $this->abs_row_right_margin = $this->abs_right_margin;
        $this->abs_col_right_margin = $this->abs_right_margin;
        $this->abs_row_width = $this->abs_print_width;
        $this->abs_columns_width = $this->abs_print_width;

        // Set up default styles
        $this->stylestack = array(
            "border-width" => array(0 => false),
            "border-edges" => array(0 => ""),
            "padding" => array(0 => false),
            "border-style" => array(0 => "none"),
            "border-color" => array(0 => "#000000"),
            "font-family" => array(0 => $this->fontName),
            "font-size" => array(0 => $this->fontSize),
            "font-weight" => array(0 => false),
            "font-style" => array(0 => false),
            "color" => array(0 => "#000000"),
            "background-color" => array(0 => array(255, 255, 255)),
            "isfilling" => array(0 => false),
            "padding" => array(0 => 0),
            "margin" => array(0 => array(0, 0, 0, 0)),
            "margin-left" => array(0 => 0),
            "margin-right" => array(0 => 0),
            "margin-top" => array(0 => 0),
            "margin-bottom" => array(0 => 0),
            "text-align" => array(0 => false),
            "position" => array(0 => "relative"),
            "height" => array(0 => false),
            "width" => array(0 => false),
            "background-image" => array(0 => false),
            "type" => array(0 => "BASE"),
            "display" => array(0 => "inline"),
        );

        if ($this->pdfDriver == "tcpdf") {
            // If font used is a Unicode Truetype font then
            // use Unicode PDF generator
            $pdf_path = findBestLocationInIncludePath("tcpdf");
            $this->document = new \TCPDF($this->orientations[$this->orientation], 'pt', $this->page_type, true, 'UTF-8', false);
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
        } else {
            $pdf_path = findBestLocationInIncludePath("fpdf");
            require_once $pdf_path . "/fpdf.php";
            require_once $pdf_path . "/ufpdf.php";

            $this->document = new \FPDF($this->orientations[$this->orientation], 'pt', $this->page_type);

            if (!isset($this->document->CoreFonts[strtolower($this->fontName)])) {
                if (!isset($this->document->fonts[strtolower($this->fontName)])) {
                    $this->document->AddFont($this->fontName, '', $this->fontName . '.php');
                }
            }

            // If the font loaded is a TrueTypeUnicode font, then we wnat to
            // use UniCode PDF generator instead
            if ($this->document->FontType == "TrueTypeUnicode") {
                $this->document = new UFPDF($this->orientations[$this->orientation], 'pt', $this->page_type);
                if (!isset($this->document->CoreFonts[strtolower($this->fontName)])) {
                    if (!isset($this->document->fonts[strtolower($this->fontName)])) {
                        $this->document->AddFont($this->fontName, '', $this->fontName . '.php');
                    }
                }

            }
        }

        $this->document->SetAutoPageBreak(false);
        $this->document->SetMargins(0, 0, 0);
        $this->document->SetCreator('Reportico');
        $this->document->SetAuthor('Reportico');
        $this->document->SetTitle($this->reporttitle);

        //$this->calculateColumnMetrics();

    }

    public function calculateColumnMetrics()
    {
        // =============================================================
        // Report Body start s a left margin with full width
        $this->query->output_reportbody_styles["style_start"] = $this->abs_left_margin;
        $this->query->output_reportbody_styles["style_width"] = $this->abs_print_width;
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "margin", "left");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "padding", "left");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "border-width", "left");
        $this->query->output_reportbody_styles["style_margin_left"] = $this->absMetric($margin);
        $this->query->output_reportbody_styles["style_padding_left"] = $this->absMetric($padding);
        $this->query->output_reportbody_styles["style_border_left"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "margin", "right");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "padding", "right");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "border-width", "right");
        $this->query->output_reportbody_styles["style_margin_right"] = $this->absMetric($margin);
        $this->query->output_reportbody_styles["style_padding_right"] = $this->absMetric($padding);
        $this->query->output_reportbody_styles["style_border_right"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "margin", "bottom");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "padding", "bottom");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "border-width", "bottom");
        $this->query->output_reportbody_styles["style_margin_bottom"] = $this->absMetric($margin);
        $this->query->output_reportbody_styles["style_padding_bottom"] = $this->absMetric($padding);
        $this->query->output_reportbody_styles["style_border_bottom"] = $this->absMetric($border);
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_margin_left"] );
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_margin_right"] );
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_left"] / 2 );
        //$this->query->output_reportbody_styles["style_width"] -= ( $this->query->output_reportbody_styles["style_border_right"] / 2 );
        $width = $this->extractStyleTags("EACHLINE", $this->query->output_reportbody_styles, "width");
        $this->query->output_reportbody_styles["style_forced_width"] = $this->absMetric($width);
        if ($this->query->output_reportbody_styles["style_forced_width"]) {
            $this->query->output_reportbody_styles["style_width"] = $this->query->output_reportbody_styles["style_forced_width"];
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->bottom_page_reportbody_styles, "width");
        }

        $this->top_page_reportbody_styles = $this->query->output_reportbody_styles;
        $this->mid_page_reportbody_styles = $this->query->output_reportbody_styles;
        $this->mid_cell_reportbody_styles = $this->query->output_reportbody_styles;
        $this->bottom_page_reportbody_styles = $this->query->output_reportbody_styles;

        // Create top, middle, bottom report sections
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_reportbody_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "margin", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "margin", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_reportbody_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "border-width", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "border-width", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_reportbody_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "padding", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_reportbody_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_reportbody_styles, "background-image");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_reportbody_styles, "height");
        //$this->removeStyleTags( "REPDETTOPPAGE", $this->mid_page_reportbody_styles, "width");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_reportbody_styles, "padding", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "margin");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "border-width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "padding");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "background-color");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "height");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "background-image");

        // =============================================================
        // Report Page starts from report body mrgin + padding
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"] + $this->query->output_reportbody_styles["style_border_left"];
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"];
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"] + $this->query->output_reportbody_styles["style_border_left"] / 2;
        $padding = 0;

        //$this->query->output_page_styles["style_start"] = $this->abs_left_margin + $padding;
        $this->query->output_page_styles["style_start"] = $this->query->output_reportbody_styles["style_start"];

        // .. and has a width of page width -$margin_left
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "margin", "left");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "padding", "left");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "border-width", "left");
        $this->query->output_page_styles["style_margin_left"] = $this->absMetric($margin);
        $this->query->output_page_styles["style_padding_left"] = $this->absMetric($padding);
        $this->query->output_page_styles["style_border_left"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "margin", "right");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "padding", "right");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "border-width", "right");
        $this->query->output_page_styles["style_margin_right"] = $this->absMetric($margin);
        $this->query->output_page_styles["style_padding_right"] = $this->absMetric($padding);
        $this->query->output_page_styles["style_border_right"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "margin", "bottom");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "padding", "bottom");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "border-width", "bottom");
        $this->query->output_page_styles["style_margin_bottom"] = $this->absMetric($margin);
        $this->query->output_page_styles["style_padding_bottom"] = $this->absMetric($padding);
        $this->query->output_page_styles["style_border_bottom"] = $this->absMetric($border);

        $this->query->output_page_styles["style_start"] += ($this->query->output_reportbody_styles["style_border_left"]);
        $this->query->output_page_styles["style_start"] += ($this->query->output_reportbody_styles["style_padding_left"]);
        $this->query->output_page_styles["style_start"] += ($this->query->output_reportbody_styles["style_margin_left"]);

        $this->query->output_page_styles["style_width"] = $this->abs_right_margin;
        if ($this->query->output_reportbody_styles["style_forced_width"]) {
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_reportbody_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->bottom_page_reportbody_styles, "width");
            //$this->removeStyleTags( "REPDETTOPPAGE", $this->output_reportbody_styles, "width");
            $this->query->output_page_styles["style_width"] = $this->query->output_reportbody_styles["style_forced_width"];
            $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_border_left"];
            $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_padding_left"];
            $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_margin_left"];
        } else {
            $this->query->output_page_styles["style_width"] -= $this->query->output_page_styles["style_start"];
        }
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_margin_right"];
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_border_right"];
        $this->query->output_page_styles["style_width"] -= $this->query->output_reportbody_styles["style_padding_right"];

        // Look for specific page width
        $width = $this->extractStyleTags("EACHLINE", $this->query->output_page_styles, "width");
        $this->query->output_page_styles["style_forced_width"] = $this->absMetric($width);

        if ($this->query->output_page_styles["style_forced_width"]) {
            $this->query->output_page_styles["style_width"] = $this->query->output_page_styles["style_forced_width"];
            $this->removeStyleTags("REPDETTOPPAGE", $this->query->output_page_styles, "width");
        }

        // Create Page top bottom and mid styles
        $this->all_page_page_styles = $this->query->output_page_styles;
        $this->top_page_page_styles = $this->query->output_page_styles;
        $this->mid_page_page_styles = $this->query->output_page_styles;
        $this->bottom_page_page_styles = $this->query->output_page_styles;
        $this->mid_row_page_styles = $this->query->output_page_styles;

        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_page_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "margin", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_page_styles, "margin", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_page_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "border-width", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_page_styles, "border-width", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_page_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "padding", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_page_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_page_styles, "padding", "top");
        $this->removeStyleTags("REPDETBOTPAGE", $this->mid_row_page_styles, "padding");
        $this->removeStyleTags("REPDETBOTPAGE", $this->mid_row_page_styles, "margin");
        $this->removeStyleTags("REPDETBOTPAGE", $this->mid_row_page_styles, "border-width");
        $this->removeStyleTags("REPDETBOTPAGE", $this->mid_row_page_styles, "background-color");

        // =============================================================
        // Row styles start from Page start + Page margin + padding with width less that
        $padding = $this->query->output_page_styles["style_margin_left"] + $this->query->output_page_styles["style_padding_left"] + $this->query->output_page_styles["style_border_left"];
        $rpadding = $this->query->output_page_styles["style_margin_right"] + $this->query->output_page_styles["style_padding_right"] + $this->query->output_page_styles["style_border_right"];

        $this->query->output_row_styles["style_start"] = $this->query->output_page_styles["style_start"] + $padding;
        $this->query->output_row_styles["style_width"] = $this->query->output_page_styles["style_width"] - $padding - $rpadding;
        $width = $this->extractStyleTags("EACHLINE", $this->query->output_row_styles, "width");
        $this->query->output_row_styles["style_forced_width"] = $this->absMetric($width);
        if ($this->query->output_row_styles["style_forced_width"]) {
            $this->query->all_page_row_styles["style_width"] = $this->query->output_row_styles["style_forced_width"];
            $this->removeStyleTags("REPDETTOPPAGE", $this->output_row_styles, "width");
        }
        $this->all_page_row_styles = $this->query->output_row_styles;
        $this->mid_cell_row_styles = $this->query->output_row_styles;

        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "margin");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "border-width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "padding");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "background-color");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "height");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_row_styles, "background-image");

        $margin = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "margin", "left");
        $padding = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "padding", "left");
        $border = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "border-width", "left");
        $this->all_page_row_styles["style_margin_left"] = $this->absMetric($margin);
        $this->all_page_row_styles["style_padding_left"] = $this->absMetric($padding);
        $this->all_page_row_styles["style_border_left"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "margin", "right");
        $padding = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "padding", "right");
        $border = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "border-width", "right");
        $this->all_page_row_styles["style_margin_right"] = $this->absMetric($margin);
        $this->all_page_row_styles["style_padding_right"] = $this->absMetric($padding);
        $this->all_page_row_styles["style_border_right"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "margin", "top");
        $padding = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "padding", "top");
        $border = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "border-width", "top");
        $this->all_page_row_styles["style_margin_top"] = $this->absMetric($margin);
        $this->all_page_row_styles["style_padding_top"] = $this->absMetric($padding);
        $this->all_page_row_styles["style_border_top"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "margin", "bottom");
        $padding = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "padding", "bottom");
        $border = $this->extractStyleTags("EACHLINE", $this->all_page_row_styles, "border-width", "bottom");
        $this->all_page_row_styles["style_margin_bottom"] = $this->absMetric($margin);
        $this->all_page_row_styles["style_padding_bottom"] = $this->absMetric($padding);
        $this->all_page_row_styles["style_border_bottom"] = $this->absMetric($border);
        $padding = $this->all_page_row_styles["style_margin_left"] + $this->all_page_row_styles["style_padding_left"] + $this->all_page_row_styles["style_border_left"];

        // =============================================================
        // Criteria styles start from Page start + Page margin + padding with width less that
        $padding = $this->query->output_reportbody_styles["style_margin_left"] + $this->query->output_reportbody_styles["style_padding_left"] + $this->query->output_reportbody_styles["style_border_left"];
        $rpadding = $this->query->output_reportbody_styles["style_margin_right"] + $this->query->output_reportbody_styles["style_padding_right"] + $this->query->output_reportbody_styles["style_border_right"];

        $this->query->output_criteria_styles["style_start"] = $this->query->output_page_styles["style_start"] + $padding;
        $this->query->output_criteria_styles["style_width"] = $this->query->output_page_styles["style_width"] - $padding - $rpadding;
        $width = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "width");
        $this->query->output_criteria_styles["style_forced_width"] = $this->absMetric($width);
        if ($this->query->output_criteria_styles["style_forced_width"]) {
            $this->query->all_page_criteria_styles["style_width"] = $this->query->output_criteria_styles["style_forced_width"];
            $this->removeStyleTags("REPDETTOPPAGE", $this->output_criteria_styles, "width");
        }
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "margin", "left");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "padding", "left");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "border-width", "left");
        $this->query->output_criteria_styles["style_margin_left"] = $this->absMetric($margin);
        $this->query->output_criteria_styles["style_padding_left"] = $this->absMetric($padding);
        $this->query->output_criteria_styles["style_border_left"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "margin", "right");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "padding", "right");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "border-width", "right");
        $this->query->output_criteria_styles["style_margin_right"] = $this->absMetric($margin);
        $this->query->output_criteria_styles["style_padding_right"] = $this->absMetric($padding);
        $this->query->output_criteria_styles["style_border_right"] = $this->absMetric($border);
        $margin = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "margin", "top");
        $padding = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "padding", "top");
        $border = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "border-width", "top");
        $this->query->output_criteria_styles["style_margin_top"] = $this->absMetric($margin);
        $this->query->output_criteria_styles["style_padding_top"] = $this->absMetric($padding);
        $this->query->output_criteria_styles["style_border_top"] = $this->absMetric($border);
        $padding = $this->query->output_criteria_styles["style_margin_left"] + $this->query->output_criteria_styles["style_padding_left"] + $this->query->output_criteria_styles["style_border_left"];

        $this->all_page_criteria_styles = $this->query->output_criteria_styles;

        $width = $this->extractStyleTags("EACHLINE", $this->query->output_criteria_styles, "width");
        $this->query->output_criteria_styles["style_forced_width"] = $this->absMetric($width);
        if ($this->query->output_criteria_styles["style_forced_width"]) {
            $this->query->output_criteria_styles["style_width"] = $this->query->output_criteria_styles["style_forced_width"];
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_criteria_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_criteria_styles, "width");
            $this->removeStyleTags("REPDETTOPPAGE", $this->bottom_page_criteria_styles, "width");
        }

        $this->top_page_criteria_styles = $this->query->output_criteria_styles;
        $this->mid_page_criteria_styles = $this->query->output_criteria_styles;
        $this->mid_cell_criteria_styles = $this->query->output_criteria_styles;
        $this->bottom_page_criteria_styles = $this->query->output_criteria_styles;

        // Create top, middle, bottom report sections
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_criteria_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "margin", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "margin", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_criteria_styles, "margin", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_criteria_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "border-width", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "border-width", "bottom");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_criteria_styles, "border-width", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->top_page_criteria_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "padding", "top");
        $this->removeStyleTags("REPDETMIDPAGE", $this->mid_page_criteria_styles, "padding", "bottom");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_criteria_styles, "background-image");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_page_criteria_styles, "height");
        //$this->removeStyleTags( "REPDETTOPPAGE", $this->mid_page_criteria_styles, "width");
        $this->removeStyleTags("REPDETBOTPAGE", $this->bottom_page_criteria_styles, "padding", "top");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "margin");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "border-width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "padding");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "background-color");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "width");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "height");
        $this->removeStyleTags("REPDETTOPPAGE", $this->mid_cell_criteria_styles, "background-image");

        // =============================================================
        // Set column detail start and  width
        $this->abs_col_left_margin = $this->query->output_row_styles["style_start"]; // + $this->query->output_page_styles["style_padding_left"];
        $this->abs_col_left_margin += $this->all_page_row_styles["style_margin_left"];
        $this->abs_col_left_margin += $this->all_page_row_styles["style_border_left"];
        $this->abs_col_left_margin += $this->all_page_row_styles["style_padding_left"];
//
        $this->abs_col_right_margin = $this->all_page_row_styles["style_start"] + $this->all_page_row_styles["style_width"];

        $this->abs_col_right_margin -= $this->all_page_row_styles["style_border_right"];
        $this->abs_col_right_margin -= $this->all_page_row_styles["style_padding_right"];
        $this->abs_col_right_margin -= $this->all_page_row_styles["style_margin_right"];

        $this->page_footer_wrapper_offset = $this->query->output_page_styles["style_border_right"] +
        $this->query->output_page_styles["style_margin_right"] +
        $this->query->output_page_styles["style_padding_right"] +
        $this->query->output_reportbody_styles["style_padding_right"] +
        $this->query->output_reportbody_styles["style_margin_right"] +
        $this->query->output_reportbody_styles["style_border_right"];

        // Calculate column print and width poistions based on the column start attributes
        $looping = true;

        foreach ($this->query->display_order_set["column"] as $k => $w) {
            $col = getQueryColumn($w->query_name, $this->query->columns);
            $startcol = $col->attributes["ColumnStartPDF"];
            $colwidth = $col->attributes["ColumnWidthPDF"];
            if ($col->output_cell_styles && isset($col->output_cell_styles["width"])) {
                $colwidth = $col->output_cell_styles["width"];
            }

            if ($startcol) {
                $col->abs_column_start = $this->absPagingWidth($startcol);
            } else {
                $col->abs_column_start = 0;
            }

            if ($colwidth) {
                $col->abs_column_width = $this->absPagingWidth($colwidth);
            } else {
                $col->abs_column_width = 0;
            }

        }

        while ($looping) {
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

            foreach ($this->query->display_order_set["column"] as $k => $w) {
                if ($w->attributes["column_display"] != "show") {
                    continue;
                }

                {
                    $col = getQueryColumn($w->query_name, $this->query->columns);
                    $startcol = $col->abs_column_start;
                    $colwidth = $col->abs_column_width;
                    if ($startcol) {
                        if ( /*$fromkey &&*/$frompos && $gapct) {
                            $topos = $col->abs_column_start;
                            break;
                        } else {
                            $fromkey = $k;
                            $tokey = $k;
                            $frompos = $col->abs_column_start;
                            if ($colwidth) {
                                $coltaken += $colwidth;
                                $coltaken = 0;
                                $colswithwidth = 1;
                                $colstocalc = 1;
                            } else {
                                $colstocalc++;
                                $gapct++;
                            }
                        }
                        $lastct = 0;
                    } else {
                        if (!$frompos) {
                            $col->abs_column_start = $this->abs_col_left_margin;
                            $frompos = $col->abs_column_start;
                            $fromkey = $k;
                        }
                        if ($colwidth) {
                            $coltaken += $colwidth;
                            $colswithwidth++;
                        }
                        $colstocalc++;
                        $tokey = $k;
                        $gapct++;
                        $looping = true;
                    }
                }

            }

            if (!$gapct) {
                break;
            }

            // We have two known positions find total free space between
            $calctoend = false;
            if (!$topos) {
                $calctoend = true;
                $topos = $this->abs_col_right_margin;
            }

            $totwidth = $topos - $frompos;
            if ($coltaken > $totwidth) {
                $coltaken = $totwidth;
            }

            $colno = 0;
            $calccolwidth = ($totwidth - $coltaken) / (($colstocalc - $colswithwidth));
            $lastpos = $this->abs_left_margin;
            for ($ct = $fromkey; $ct <= $tokey; $ct++) {
                $col1 = &$this->query->display_order_set["column"][$ct];
                if ($col1->attributes["column_display"] == "show") {
                    $abspos = $col1->abs_column_start;

                    if (!$abspos) {
                        $col1->abs_column_start = $lastpos;
                        $colwidth = $col1->attributes["ColumnWidthPDF"];
                        if ($col1->output_cell_styles && isset($col1->output_cell_styles["width"])) {
                            $colwidth = $col1->output_cell_styles["width"];
                        }

                        if ($colwidth) {
                            $col1->abs_column_width = $this->absPagingWidth($colwidth);
                            $lastpos = $col1->abs_column_start + $col1->abs_column_width;
                        } else {
                            $col1->abs_column_width = $calccolwidth;
                            $lastpos = $col1->abs_column_start + $calccolwidth;
                        }
                    } else {
                        $colwidth = $col1->attributes["ColumnWidthPDF"];
                        if ($col1->output_cell_styles && isset($col1->output_cell_styles["width"])) {
                            $colwidth = $col1->output_cell_styles["width"];
                        }

                        if ($colwidth) {
                            $col1->abs_column_width = $this->absPagingWidth($colwidth);
                            $lastpos = $col1->abs_column_start + $col1->abs_column_width;
                        } else {
                            $col1->abs_column_width = $calccolwidth;
                            $lastpos = $col1->abs_column_start + $calccolwidth;
                        }
                    }
                }
            }

        }
    }

    public function finish()
    {
        Report::finish();
        $this->debug("Finish");

        $this->document->SetDisplayMode("real");
        //$this->document->pdf_close($this->document);
        if ($this->report_file) {
            $this->debug("Saved to $this->report_file");
        } else {
            $this->debug("No pdf file specified !!!");
            //$buf = $this->document->pdf_get_buffer($this->document);
            //$buf = $this->document->Output("", "S");
            //$buf = base64_decode(buf);
            //$len = strlen($buf);
            //header("Content-Type: application/pdf");
            //header("Content-Length: $len");
            //header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

            if (ob_get_length() > 0) {
                ob_clean();
            }

            // Use TCPDF's mechanisms for delivering via attachment or inline
            $attachfile = "reportico.pdf";
            if ($this->reportfilename) {
                $attachfile = preg_replace("/ /", "_", $this->reportfilename . ".pdf");
            }

            header('Content-Disposition: attachment;filename=' . $attachfile);

            // INLINE output is just returned to browser window it is invoked from
            // with hope that browser uses plugin
            if ($this->query->pdf_delivery_mode == "INLINE") {
                $this->document->Output($attachfile, "I");
                die;
            }
            // DOWNLOAD_SAME_WINDOW output is ajaxed back to current browser window and then downloaded
            else if ($this->query->pdf_delivery_mode == "DOWNLOAD_SAME_WINDOW" && $this->query->reportico_ajax_called) {
                header('Content-Disposition: attachment;filename=' . $attachfile);
                header("Content-Type: application/pdf");
                $buf = base64_encode($this->document->Output($attachfile, "S"));
                $len = strlen($buf);
                echo $buf;
                die;
            }
            // DOWNLOAD_NEW_WINDOW new browser window is opened to download file
            else {
                $buf = $this->document->Output($attachfile, "D");
                die;
            }

            print($buf);
            die;
        }
    }

    public function absMetric($value, $type = "width")
    {
        //if ( preg_match("/(\d*\.*(\d+)(\D*)/", $height_string, $match) )
        if (preg_match("/(\d+)(\D*)/", $value, $match)) {
            $number = $match[1];
            if (isset($match[2])) {
                switch ($match[2]) {
                    case "px":
                        $value = $number;
                        break;

                    case "pt":
                        $value = $value;
                        break;

                    case "%":
                        if ($type == "width") {
                            $value = ($value * $this->abs_page_width) / 100;
                        } else {
                            $value = ($value * $this->abs_page_height) / 100;
                        }

                        break;

                    case "mm":
                        $value = $value / 0.35277777778;
                        break;

                    case "cm":
                        $value = $value / 0.035277777778;
                        break;

                    default:
                        //handleError("Unknown Page Sizing Option ".$match[2]);
                        break;

                }
            }
        } else {
            $value = $value;
            //handleError("Unknown Page Sizing Option $height_string");
        }

        return $value;
    }

    public function absPagingHeight($height_string)
    {
        //if ( preg_match("/(\d*\.*(\d+)(\D*)/", $height_string, $match) )
        if (preg_match("/(\d+)(\D*)/", $height_string, $match)) {
            $height = $match[1];
            if (isset($match[2])) {
                switch ($match[2]) {
                    case "pt":
                        $height = $height;
                        break;

                    case "%":
                        $height = ($height * $this->abs_page_height) / 100;
                        break;

                    case "mm":
                        $height = $height / 0.35277777778;
                        break;

                    case "cm":
                        $height = $height / 0.035277777778;
                        break;

                    default:
                        //handleError("Unknown Page Sizing Option ".$match[2]);
                        break;

                }
            }
        } else {
            $height = $height_string;
            //handleError("Unknown Page Sizing Option $height_string");
        }

        return $height;
    }

    public function absPagingWidth($width_string)
    {
        if (preg_match("/(\d+)(\D*)/", $width_string, $match)) {
            $width = $match[1];
            if (isset($match[2])) {
                switch ($match[2]) {
                    case "pt":
                        $width = $width;
                        break;

                    case "%":
                        $width = ($width * $this->abs_page_width) / 100;
                        break;

                    case "mm":
                        $width = $width / 0.35277777778;
                        break;

                    case "cm":
                        $width = $width / 0.035277777778;
                        break;

                        //default:
                        //handleError("Unknown age Sizing Option $width_string");
                        //break;

                }
            }
        } else {
            $width = $width_string;
            //handleError("Unknown Page Sizing Option $width_string");
        }

        return $width;
    }

    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false) // PDF

    {
        if (!getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        if ($value_col) {

            $y = $this->document->GetY();

            // Fetch Group Header Label
            $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("group_header_label");
            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }

            $group_label = swTranslate($group_label);

            // Fetch Group Header Label End Column + display
            $group_xpos = $trailer_col->abs_column_start;

            $wd = $trailer_col->abs_column_width;
            if ($wd - $this->column_spacing > 0) {
                $wd = $wd - $this->column_spacing;
            }

            $this->setPosition($group_xpos, $y);
            $padstring = $value_col["GroupTrailerValueColumn"]->old_column_value;
            $just = $this->justifys[$trailer_col->deriveAttribute("justify", "left")];
            $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("group_trailer_label");
            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }

            if ($group_label && $group_label != "BLANK") {
                $padstring = $group_label . " " . $padstring;
            }

            $this->drawCell($wd, $this->vsize + 2, "$padstring", "PBF", 0, $just);

            // Fetch Group Header Label Start Column + display
            $group_xpos = $value_col["GroupTrailerValueColumn"]->getAttribute("group_header_label_xpos");
            if (!$group_xpos) {
                $group_xpos = 0;
            }

            $group_xpos = $this->absPagingWidth($group_xpos);
            $group_xpos = $value_col["GroupTrailerValueColumn"]->abs_column_start;

            $this->setPosition($group_xpos, $y);
            $padstring = $group_label;
            $just = $this->justifys[$trailer_col->deriveAttribute("justify", "left")];
        }
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

    }

    public function customTrailerWrappers()
    {
        $hty = $this->group_header_end - $this->group_header_start;
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
    }

    public function formatCustomTrailer(&$trailer_col, &$value_col) // PDF

    {
        // If this is the first custom trailer break a little
        if (!getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        if (!$value_col["GroupTrailerCustom"]) {
            return;
        }

        if ($value_col["GroupTrailerCustom"]) {

            if ($this->draw_mode != "CALCULATE") {
                if (!$this->any_custom_trailers || $this->any_custom_trailers == "NONE") {
                    //$this->unapplyStyleTags( "CUSTRAILNOPAGE", $this->mid_page_page_styles);
                    $this->current_line_height = 0;
                    //$this->drawCell(0, 0, "",0,1);
                    //$this->endLine();
                    $this->group_header_start = $this->document->GetY();
                    //$this->applyStyleTags( "CUSTRAILNOPAGE", $this->mid_page_page_styles);
                }
                $this->any_custom_trailers = "PP";
            }
//$this->debug2("CUSTOMTRAILER2".$this->group_header_end." ");
            $this->setPosition($this->abs_left_margin, $this->group_header_start);
//$this->debug2("CUSTOMTRAILER3".$this->group_header_end." ");
            $prevx = $this->document->GetX();
            $prevy = $this->document->GetY();

            $this->yjump = 2;
            // Fetch Group Header Label Start Column + display
            //$this->unapplyStyleTags( "DEFAULT", $this->mid_page_page_styles);

            // Display group header value

            $this->setPosition($this->abs_left_margin, $this->group_header_start);

            $tx = $value_col["GroupTrailerCustom"];
            $wd = $this->abs_print_width;
            $styles = $this->fetchCellStyles($tx);
            $just = "L";
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
            $this->applyStyleTags("CUSTOMTRAILER", $styles);
            //if ( $this->draw_mode == "DRAW" )
            //{
            //$this->drawCellContainer($wd, $this->vsize + 4, $tx,"PBR",0,$just);
            //}

            $pmargin = $this->extractStyleTags("EACHLINE", $this->all_page_page_styles, "margin", "left");
            $rgmargin = $this->extractStyleTags("EACHLINE", $styles, "margin", "right");
            $x = $this->all_page_page_styles["style_start"];
            $wd = $this->all_page_page_styles["style_width"] + $pmargin - $rgmargin;
            $this->setPosition($x, $this->group_header_start);

            $link = false;

            $this->drawCell($wd, $this->vsize + 0, $tx, "PBF", 0, $just, "T", $link);
            $this->unapplyStyleTags("CUSTOMTRAILER", $styles);
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            $this->endLine();
            //$this->drawCell($group_data_width, $this->vsize, "");    // Blank cell to continue page breaking at this size
            $y = $this->document->GetY();

            // Store where group header reaches so we know where to start printing after row
            if ($y > $this->group_header_end) {
                $this->group_header_end = $y;
            }

            if ($this->yjump) {
                $this->setPosition(false, $y + $this->yjump);
            }

            //$this->applyStyleTags( "DEFAULT", $this->mid_page_page_styles);
            $this->setPosition($prevx, $prevy);
        }

    }
    //Cell with horizontal scaling if text is too wide
    public function drawCellContainer($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $valign = "T")
    {
        // Set custom width
        $custom_width = end($this->stylestack["width"]);
        if ($custom_width) {
            $w = $custom_width;
        }

        // Get margins and position
        $position = end($this->stylestack["margin-top"]);
        $margin_top = end($this->stylestack["margin-top"]);
        $margin_left = end($this->stylestack["margin-left"]);

        // Get Justification
        $justify = end($this->stylestack["text-align"]);
        if ($justify) {
            switch ($justify) {
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
        $padding = end($this->stylestack["padding"]);
        $toppad = $padding[0];
        $bottompad = $padding[2];

        // Add border and bg color
        $fill = end($this->stylestack["isfilling"]);
        $borderwidth = end($this->stylestack["border-edges"]);
        $border = end($this->stylestack["border-style"]);
        if ($border != "none") {
            $border = 1;
        } else {
            $borderwidth = "false";
        }

        // Store current position so we can jump back after cell draw
        $x = $this->document->GetX();
        $y = $this->document->GetY();

        if ($margin_top) {
            $this->setPosition($false, $y + $margin_top);
        }

        if ($margin_left) {
            $this->setPosition($x + $margin_left, false);
        }

        //$background_image = end( $this->stylestack["background-image"] );
        //if ( $background_image )
        //$h = $this->document->Image($background_image, $this->document->GetX(), $this->document->GetY(), $w *2);

        $this->drawMulticell($w, $this->max_line_height, "", $borderwidth, false, $fill);
        $cell_height = $this->document->GetY() - $y;

        // Jump back
        $this->setPosition($x, $y);
    }

    //Cell with horizontal scaling if text is too wide
    public function drawCell($w, $h = 0, $txt = '', $implied_styles = "PBF", $ln = 0, $align = '', $valign = "T", $link = '')
    {
        if (end($this->stylestack["display"]) == "none") {
            return;
        }

        // Set custom width
        $custom_width = end($this->stylestack["width"]);
        if ($custom_width) {
            $w = $this->absMetric($custom_width);
        }

        // Set custom width
        $custom_height = end($this->stylestack["height"]);
        if ($custom_height) {
            $h = $this->absMetric($custom_height, "height");
        }

        // Get margins and position
        $position = end($this->stylestack["position"]);
        $margin_top = $this->absMetric(end($this->stylestack["margin-top"]));
        $margin_left = $this->absMetric(end($this->stylestack["margin-left"]));
        $margin_right = $this->absMetric(end($this->stylestack["margin-right"]));
        $margin_bottom = $this->absMetric(end($this->stylestack["margin-bottom"]));

        // If a cell contains a line break like a "<BR>" then convert it to new line
        $txt = preg_replace("/<BR>/i", "\n", $txt);
        // Calculate cell height as string width divided by width

        // Add margin
        $topmargin = 0;
        $bottommargin = 0;
        $leftmargin = 0;
        $rightmargin = 0;

        $margin = end($this->stylestack["margin"]);
        if ($margin) {
            $topmargin = $this->absMetric($margin[0]);
            $rightmargin = $this->absMetric($margin[1]);
            $bottommargin = $this->absMetric($margin[2]);
            $leftmargin = $this->absMetric($margin[3]);
        }

        // Add padding
        $toppad = 0;
        $bottompad = 0;
        $leftpad = 0;
        $rightpad = 0;
        if (strstr($implied_styles, "P") || $this->pdfDriver == "tcpdf") {
            $padding = end($this->stylestack["padding"]);
            $toppad = $this->absMetric($padding[0]);
            $rightpad = $this->absMetric($padding[1]);
            $bottompad = $this->absMetric($padding[2]);
            $leftpad = $this->absMetric($padding[3]);
        }

        if ($this->pdfDriver == "fpdf") {
            $oldLineWidth = $this->document->LineWidth;
        } else {
            $oldLineWidth = $this->document->getLineWidth();
        }

        $fill = false;
        if (strstr($implied_styles, "F") || $this->pdfDriver == "tcpdf") {
            // Add border and bg color
            $fill = end($this->stylestack["isfilling"]);
        }

        // Get Justification
        $justify = end($this->stylestack["text-align"]);
        if ($justify) {
            switch ($justify) {
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
        if (strstr($implied_styles, "B") || $this->pdfDriver == "tcpdf") {
            $borderedges = end($this->stylestack["border-edges"]);

            $borderwidth = end($this->stylestack["border-width"]);
            $border = end($this->stylestack["border-style"]);
            if ($border != "none") {
                $borderstyle = 1;
            } else {
                $borderedges = "false";
            }

            if ($borderwidth) {
                $borderwidth = $this->absMetric($borderwidth);
            }

            // Add extra padding in cell to avoid text conficting with border
            //if ( $borderwidth && preg_match("/T/", $borderedges) ) $toppad += $borderwidth;
            //if ( $borderwidth && preg_match("/B/", $borderedges) ) $bottompad += $borderwidth;
            //if ( $borderwidth && preg_match("/L/", $borderedges) ) $leftpad += $borderwidth;
            //if ( $borderwidth && preg_match("/R/", $borderedges) ) $rightpad += $borderwidth;

            // Calulate addition cellheight caused by top and bottom borders
            if ($borderwidth && preg_match("/T/", $borderedges)) {
                $topborderadditiontoheight = $borderwidth / 2;
            }

            if ($borderwidth && preg_match("/B/", $borderedges)) {
                $botborderadditiontoheight = $borderwidth / 2;
            }

            if ($borderwidth && preg_match("/L/", $borderedges)) {
                $leftborderadditiontoheight = $borderwidth / 2;
            }

            if ($borderwidth && preg_match("/R/", $borderedges)) {
                $rightborderadditiontoheight = $borderwidth / 2;
            }

            //$topborderadditiontoheight = $borderwidth;
            //$botborderadditiontoheight = $borderwidth;
        }

        // Store current position so we can jump back after cell draw
        $storey = $this->document->GetY();
        $storex = $this->document->GetX();

        // If position is absolute position at page start
        if ($position == "absolute") {
            $this->setPosition(1, 1);
        }

        // Store current position so we can jump back after cell draw
        $y = $this->document->GetY();
        $x = $this->document->GetX();

        // Cells with borders draw with half border appearing outside the text box so mode text down by half to give it space
        if ($topborderadditiontoheight) {
            $topmargin += $topborderadditiontoheight;
            //if ( $this->document->GetFontSizePt() == 0 )
            //$toppad +=$topborderadditiontoheight;
        }

        // Cells with borders draw with half border appearing outside the text box so mode text down by half to give it space
        if ($botborderadditiontoheight) {
            $bottommargin += $botborderadditiontoheight;
            //if ( $this->document->GetFontSizePt() == 0 )
            $bottompad += $botborderadditiontoheight;
        }

        if ($leftborderadditiontoheight) {
            $leftmargin += $leftborderadditiontoheight;
        }

        if ($rightborderadditiontoheight) {
            $rightmargin += $rightborderadditiontoheight;
        }

        //if ( $leftborderadditiontoheight )
        //$w -= $leftborderadditiontoheight;
        //if ( $rightborderadditiontoheight )
        //$w -= $rightborderadditiontoheight;

        // TCPDF ignores a right margin if a width is specified.. we always specify a width, so
        // to use a right margin reduce the width by the right margin instead also widht
        // must be reduced by left margin because TCPDF does not reduce with by the margin
        if ($rightmargin) {
            $w -= $rightmargin;
        }

        if ($leftmargin) {
            $w -= $leftmargin;
        }

        // Add to top/bottom margin space require by surrounding row border
        $topmargin += $this->cell_row_top_addition;
        $bottommargin += $this->cell_row_bottom_addition;

        $borderaddition = $botborderadditiontoheight + $topborderadditiontoheight;
        //$paddingaddition = $toppad + $bottompad;
        if ($this->draw_mode == "CALCULATE") {
            $this->document->startTransaction();
            //$fill_line_height = $margin_top + $toppad + $this->calculated_line_height + $bottompad;
            //if ( $this->max_line_height < $fill_line_height )
            $cellvaluewidth = $w;
            $cellvaluefromy = $this->document->GetY();

            $this->document->SetCellPaddings($leftpad, $toppad, $rightpad, $bottompad);
            $this->document->SetCellMargins($leftmargin, $topmargin, $rightmargin, $bottommargin);

            $cellborder = false;
            if ($borderedges) {
                $margin_top = end($this->stylestack["margin-top"]);
                $border_color = end($this->stylestack["border-color"]);
                $border_style = end($this->stylestack["border-style"]);

                if ($border_style == "none") {
                    $cellborder = false;
                } else {
                    if ($border_style == "solid") {
                        $border_style = 0;
                    } else if ($border_style == "dotted") {
                        $border_style = 1;
                    } else if ($border_style == "dashed") {
                        $border_style = 2;
                    }

                    $cellborder = array(
                        //'mode' => "int",
                        $borderedges => array(
                            'width' => $borderwidth,
                            'color' => $border_color,
                            'dash' => $border_style,
                        ));

                }
            }
            $ht = $this->drawMulticell($cellvaluewidth, 0, "$txt", $cellborder, false, false, false, false);

            $this->document->SetCellPadding(0);
            $this->document->SetCellMargins(0, 0);
            if ($cellvaluewidth < 0) {
                $cellvaluewidth = 20;
                $txt = "Padding too large / Width too small - $txt";
            }

            $cellheight = $ht;
            if ($custom_height) {
                $cellheight = $h;
            }

            $requiredheight = $cellheight - $borderaddition;
            $cellheight += $topmargin;
            $cellheight += $bottommargin;
            if ($cellheight > $this->max_line_height) {
                $this->max_line_height = $cellheight;
            }
            if ($requiredheight > $this->required_line_height) {
                $this->required_line_height = $requiredheight;
            }

            $this->document = $this->document->rollbackTransaction();
            return;
        }

        // To cater for multiline values, jump to bottom of line + padding -
        // cell height

        $jumpy = 0;
        if ($toppad && $this->pdfDriver == "fpdf") {
            if ($valign == "T") {
                $jumpy = $toppad;
            } else if ($valign == "B") {
                $jumpy = $toppad + $this->calculated_line_height - $cellheight;
            } else if ($valign == "C") {
                $jumpy = (($toppad + $this->calculated_line_height + $bottompad) - $cellheight) / 2;
            }

        }

        if ($margin_top) {
            $this->setPosition(false, $y + $margin_top);
        }

        if ($margin_left) {
            $this->setPosition($x + $margin_left, false);
        }

        $prevx = $this->document->GetX();

        // Top Padding
        if ($toppad && $this->pdfDriver == "fpdf") {
            $tmpborder = $borderedges;
            $tmpborder = preg_replace("/B/", "", $borderedges);

            $pady = $this->document->GetY() - 1;
            $this->setPosition(false, $pady + 2);

            $prevx = $this->document->GetX();

            if ($borderwidth) {
                $this->document->setLineWidth($borderwidth);
            }

            $this->drawMulticell($w, $toppad, "", $tmpborder, $align, $fill, $link);
            if ($borderwidth) {
                $this->document->setLineWidth($oldLineWidth);
            }

            $this->setPosition($prevx, false);
        }

        $this->setPosition(false, $margin_top + $y + $jumpy);

        // Link in a PDF must include a full URL contain http:// element
        // drilldown link of web url can be relative .. so prepend required elements
        if ($link) {
            if (!preg_match("/^http:\/\//", $link) && !preg_match("/^\//", $link)) {
                $link = "http://" . $_SERVER["HTTP_HOST"] . dirname($this->query->url_path_to_reportico_runner) . "/" . $link;
            }

            if (preg_match("/^\//", $link)) {
                $link = ReporticoApp::getConfig("http_urlhost") . "/" . $link;
            }

        }

        // Cell Side Borders
        $tmpborder = $borderedges;
        if ($toppad) {
            $tmpborder = preg_replace("/T/", "", $tmpborder);
        }

        if ($bottompad) {
            $tmpborder = preg_replace("/B/", "", $tmpborder);
        }

        $cellborder = $tmpborder;
        if ($leftpad) {
            $cellborder = preg_replace("/L/", "", $cellborder);
        }

        if ($rightpad) {
            $cellborder = preg_replace("/R/", "", $cellborder);
        }

        $storeX = $this->document->GetX();
        if ($this->pdfDriver == "fpdf") {
            if ($leftpad) {
                $this->setPosition($storeX + $leftpad - 1, false);
            }
        }

        $storeY = $this->document->GetY();

        $cellvaluewidth = $w - $leftpad - $rightpad;
        $cellvaluewidth = $w;
        if ($cellvaluewidth < 0) {
            $cellvaluewidth = 20;
            $txt = "Padding too large / Width too small - $txt";
        }

        // Cell image
        $background_image = end($this->stylestack["background-image"]);
        $cell_height = 0;
        $last_draw_end_y = 0;
        if ($background_image) {
            $preimageX = $this->document->GetX();
            $preimageY = $this->document->GetY();

//$this->debugFile("$this->draw_mode $margin_top t $topmargin txt <$txt> align $align image cur $cellvaluewidth / $h vs $custom_height/$custom_width $p\n");
            // If text prvoded then background image covers text else set to custom height/width
            //$cellvaluewidth = "200";
            //if ( $txt )
            $p = $this->drawImage($background_image, $this->document->GetX() + $leftmargin, $this->document->GetY() + $topmargin, $cellvaluewidth, $h, false, $align);
            //else
            //$p = $this->drawImage($background_image, $this->document->GetX() + $leftmargin, $this->document->GetY() + $topmargin, $custom_width, $custom_height, false, $align);
            $p += $topmargin;

//echo "$last_draw_end_y $storeY $background_image ".$p."<BR>";
            //if ($this->document->GetY() + $p > $this->group_header_end )
            //$this->group_header_end = $this->document->GetY() + $p;
            $cell_height = $p;
            $last_draw_end_y = $storeY + $p;
            $this->setPosition($preimageX, $preimageY);
            //$this->last_draw_end_y =  $storeY + $p;
            //return;
        }

        // Cell value
        $cellvaluefromy = $this->document->GetY();
        $actcellvaluewidth = $cellvaluewidth;

        if ($this->pdfDriver == "fpdf") {
            // with left pad and right pad there can be some odd lines left on side so force a bigger width
            if ($leftpad) {
                $actcellvaluewidth += 1;
            }

            if ($rightpad) {
                $actcellvaluewidth += 1;
            }

            if ($borderwidth) {
                $this->document->setLineWidth($borderwidth);
            }

        }

        if ($this->pdfDriver == "tcpdf") {
            $this->document->SetCellPaddings($leftpad, $toppad, $rightpad, $bottompad);

            $this->document->SetCellMargins($leftmargin, $topmargin, $rightmargin, $bottommargin);

            $cellborder = false;
            if ($borderedges) {
                $margin_top = end($this->stylestack["margin-top"]);
                $border_color = end($this->stylestack["border-color"]);
                $border_style = end($this->stylestack["border-style"]);

                if ($border_style == "none") {
                    $cellborder = false;
                } else {
                    if ($border_style == "solid") {
                        $border_style = 0;
                    } else if ($border_style == "dotted") {
                        $border_style = 1;
                    } else if ($border_style == "dashed") {
                        $border_style = 2;
                    }

                    $cellborder = array(
                        //'mode' => "int",
                        $borderedges => array(
                            'width' => $borderwidth,
                            'color' => $border_color,
                            'dash' => $border_style,
                        ));

                }
            }

        }

        $this->last_cell_xpos = $this->document->GetX();
        $this->last_cell_width = $actcellvaluewidth;

        if ($background_image) {
            $ht = $this->drawMulticell($actcellvaluewidth, $h, $txt, $cellborder, $align, false, $link);
        } else {
            $ht = $this->drawMulticell($actcellvaluewidth, $h, $txt, $cellborder, $align, $fill, $link);
        }

        if ($borderwidth) {
            $this->document->setLineWidth($oldLineWidth);
        }

        $text_cell_height = $ht;

        if ($text_cell_height > $cell_height) {
            $cell_height = $text_cell_height;
        }

        // Store reach of cells for headers unless we are in absolute position
        // in which case we allow other stuff to pverwrite it
        if ($position != "absolute") {
            if ($this->document->GetY() > $this->group_header_end) {
                $this->group_header_end = $this->document->GetY();
            }

            if ($cell_height > $this->current_line_height && !$this->ignore_height_checking) {
                $this->current_line_height = $cell_height;
            }

            $this->last_draw_end_y = $this->document->GetY();
            if ($this->last_draw_end_y < $last_draw_end_y) {
                $this->last_draw_end_y = $last_draw_end_y;
            }

        }

        // Jump back
        $this->setPosition(false, $storey);
    }

    // New Line ensure next line appears under the highest cell on the current so use
    // record of current line height
    public function endLine($h = false)
    {
        // Dont draw line ends in draw mode
        if ($this->draw_mode == "CALCULATE") {
            return;
        }

        if ($this->current_line_height) {
            $this->document->Ln($this->current_line_height - $this->max_line_border_addition);
            //$this->setPosition(false, $this->current_line_start_y + $this->current_line_height);
        } else {
            if ($h !== false) {
                $this->document->Ln($h);
            } else {
                $this->document->Ln();
            }
        }
        $y = $this->document->GetY();
        $this->setPosition(false, $y);
        $this->current_line_start_y = $this->document->GetY();
        $this->current_line_height = 0;
        $this->max_line_height = 0;
        $this->required_line_height = 0;
        $this->max_line_border_addition = 0;
        $this->max_line_padding_addition = 0;
        $this->calculated_line_height = 0;
    }

    public function formatPageFooterStart() // PDF

    {
        //$this->unapplyStyleTags( "DEFAULT", $this->mid_page_page_styles);
    }

    public function formatPageFooterEnd() // PDF

    {
        //$this->unapplyStyleTags( "DEFAULT", $this->query->output_reportbody_styles);
    }

    public function formatPageHeaderStart() // PDF

    {
        $this->reporttitle = $this->query->deriveAttribute("ReportTitle", "Set Report Title");
        //$this->reporttitle = preg_replace("/<[^>]*>/", "", $this->reporttitle);

        // Add custom image here
        if (defined("PDF_HEADER_IMAGE")) {
            $x = 500;
            $y = 25;
            $w = 50;
            if (defined("PDF_HEADER_XPOS")) {
                $x = PDF_HEADER_XPOS;
            }

            if (defined("PDF_HEADER_YPOS")) {
                $y = PDF_HEADER_YPOS;
            }

            if (defined("PDF_HEADER_WIDTH")) {
                $w = PDF_HEADER_WIDTH;
            }

            $h = $this->drawImage(PDF_HEADER_IMAGE, $x, $y, $w * $this->pdfImageDPIScale);
        }

        return;
    }

    public function formatPageHeaderEnd() // PDF

    {
        $this->endLine();
    }

    public function beforeFormatCriteriaSelection()
    {
        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("REPTOPBODY", $this->top_page_criteria_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("REPTOPBODY", $this->top_page_criteria_styles, true);

        // If set draw a Criteria label :-
        if ($this->query->criteria_block_label) {
            $label = $this->query->criteria_block_label;
            $this->draw_mode = "CALCULATE";
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);

            $group_xpos =
            $this->all_page_criteria_styles["style_start"] +
            $this->all_page_criteria_styles["style_margin_left"] +
            $this->all_page_criteria_styles["style_padding_left"] +
            $this->all_page_criteria_styles["style_border_left"];
            $this->setPosition($group_xpos, $y);
            $padstring = $label;
            $this->drawCell(120, $this->vsize, "$padstring");

            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            // -----------------------------------------------------
            $this->draw_mode = "DRAW";
            $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
            $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_criteria_styles, false);

            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);

            $this->yjump = 0;
            // Fetch Group Header Label Start Column + display
            $group_xpos =
            $this->all_page_criteria_styles["style_start"] +
            $this->all_page_criteria_styles["style_margin_left"] +
            $this->all_page_criteria_styles["style_padding_left"] +
            $this->all_page_criteria_styles["style_border_left"];
            $this->setPosition($group_xpos, $y);
            $padstring = $label;
            $this->drawCell(120, $this->vsize, "$padstring");
            $this->endLine();
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
            $y = $this->document->GetY();

            if ($this->yjump) {
                $this->setPosition(false, $y + $this->yjump);
            }

        }
    }

    public function formatCriteriaSelection($label, $value)
    {
        $y = $this->document->GetY();
        $criteria_label_start =
        $this->all_page_criteria_styles["style_start"] +
        $this->all_page_criteria_styles["style_margin_left"] +
        $this->all_page_criteria_styles["style_padding_left"] +
        $this->all_page_criteria_styles["style_border_left"];
        $criteria_label_width = 120;
        $criteria_value_width = $this->all_page_criteria_styles["style_width"] -
        $criteria_label_width -
        $this->all_page_criteria_styles["style_margin_left"] -
        $this->all_page_criteria_styles["style_padding_left"] -
        $this->all_page_criteria_styles["style_border_left"] -
        $this->all_page_criteria_styles["style_margin_right"] -
        $this->all_page_criteria_styles["style_padding_right"] -
        $this->all_page_criteria_styles["style_border_right"];
        $criteria_value_start = $criteria_label_start + $criteria_label_width;
        if ($criteria_value_width < 120) {
            $criteria_value_width = 120;
        }

        // -----------------------------------------------------
        $this->draw_mode = "CALCULATE";

        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);
        $group_xpos = false;
        if (!$group_xpos) {
            $group_xpos = $this->abs_left_margin;
        }

        $group_xpos = $this->absPagingWidth($group_xpos);

        // Draw label
        $this->setPosition($criteria_label_start, $y);
        $this->drawCell($criteria_label_width, $this->vsize, "$label");

        // Set position inside group box and draw value
        $this->setPosition($criteria_value_start, $y);
        $this->drawCell($criteria_value_width, $this->vsize, "$value");
        $this->endLine();
        $y = $this->document->GetY();

        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

        // -----------------------------------------------------
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_criteria_styles, false);
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);

        $this->yjump = 0;
        // Fetch Group Header Label Start Column + display
        $group_xpos =
        $this->all_page_criteria_styles["style_start"] +
        $this->all_page_criteria_styles["style_margin_left"] +
        $this->all_page_criteria_styles["style_padding_left"] +
        $this->all_page_criteria_styles["style_border_left"];

        // Draw label
        $this->setPosition($criteria_label_start, $y);
        $this->drawCell($criteria_label_width, $this->vsize, "$label");

        // Set position inside group box and draw value
        $this->setPosition($criteria_value_start, $y);
        $this->drawCell($criteria_value_width, $this->vsize, "$value");

        $this->endLine();
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_criteria_styles);
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $y = $this->document->GetY();

        if ($this->yjump) {
            $this->setPosition(false, $y + $this->yjump);
        }

        $label = "";
        $value = "";
    }

    public function afterFormatCriteriaSelection()
    {

        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("ENDPAGE", $this->bottom_page_criteria_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("ENDPAGE", $this->bottom_page_criteria_styles, true);
    }

    public function formatGroupHeaderStart() // PDF

    {
        if (sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        $this->inGroupOutput = true;

        $this->checkLineRequirement($this->query->output_group_header_styles);

        $this->group_header_start = $this->document->GetY();
        $this->group_header_end = $this->document->GetY();

        // Throw new page if current position + number headers + line + headers > than bottom margin
        $ln = 0;
        $totheaderheight = 0;
        $prevheight = $this->calculated_line_height;

        foreach ($this->query->groups as $val) {
            for ($i = 0; $i < count($val->headers); $i++) {
                $col = &$val->headers[$i]["GroupHeaderColumn"];
                $custom = $val->headers[$i]["GroupHeaderCustom"];
                if ($val->headers[$i]["ShowInPDF"]) {
                    $this->formatGroupHeader($col, $custom, true);
                    $totheaderheight += $this->calculated_line_height;
                }
            }
        }
        $this->group_headers_custom_drawn = 0;
        $this->group_headers_drawn = 0;

        $this->calculated_line_height = $totheaderheight;
        $y = $this->document->GetY();
        $this->checkPageOverflow();
        $this->calculated_line_height = $prevheight;
    }

    public function formatGroupHeaderEnd()
    {
        $this->setPosition(false, $this->group_header_end);
        $this->current_cell_height = 0;
        $this->inGroupOutput = false;
    }

    public function formatGroupTrailerStart($first = false) // PDF

    {
        $this->any_custom_trailers = "NONE";

        // Tiny padding between group trailers and bofy detail so cell border doesnt overwrite heading underline
        if ($first) {
            $this->endLine(0);
        }

        $this->applyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);

        return;
    }

    public function formatGroupTrailerEnd($last_trailer = false) // PDF

    {
        $this->unapplyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);

        return;
    }

    public function endOfPageBlock() // PDF

    {
        $this->endLine(0);
        $this->checkForDetailPageEnd();

        return;
    }

    public function formatGroupCustomTrailerStart() // PDF

    {
        $this->group_header_start = $this->document->GetY();
        $this->group_header_end = $this->document->GetY();
        return;
    }

    public function formatGroupCustomTrailerEnd() // PDF

    {
        $this->setPosition(false, $this->group_header_end);
        return;
    }

    public function fetchCellStyles(&$tx)
    {
        $styles = false;
        $matches = array();
        if (preg_match("/{STYLE[ ,]*([^}].*)}/", $tx, $matches)) {
            if (isset($matches[1])) {
                $stylearr = explode(";", $matches[1]);
                $tx = preg_replace("/{STYLE[ ,]*[^}].*}/", "", $tx);
                foreach ($stylearr as $v) {
                    if (!$v) {
                        continue;
                    }

                    $style = explode(":", $v);
                    if (count($style) >= 2)
                    //if ( trim($style[0] ) == "width" )
                    //$wd = trim($style[1]);
                    //else
                    {
                        $styles[trim($style[0])] = trim($style[1]);
                    }

                }
            }
        }

        $tx = $this->reporticoStringToPhp($tx);
        $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
    }

    public function formatGroupHeader(&$col, $custom, $calculate_only = false) // PDF format group headers

    {
        $this->checkLineRequirement($this->query->output_group_header_label_styles);
        for ($ctr = 0; $ctr < 2; $ctr++) {
            $this->draw_mode = "CALCULATE";
            if ($ctr == 1 && $calculate_only) {
                $this->draw_mode = "DRAW";
                break;
            }
            if ($ctr == 1) {
                $this->draw_mode = "DRAW";
                $this->checkPageOverflow();
                if (!$custom || $this->group_headers_custom_drawn == 0) {
                    $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
                }

                $this->group_headers_custom_drawn++;
            }

            $y = $this->document->GetY();

            if ($custom) {
                $prevx = $this->document->GetX();
                $prevy = $this->document->GetY();
                $this->yjump = 2;

                $wd = $this->abs_print_width;
                $tx = $custom;
                $styles = $this->fetchCellStyles($tx);
                $tx = $this->reporticoStringToPhp($tx);
                $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
                $just = "L";

                $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
                $this->applyStyleTags("GROUPHEADER", $styles);
                $link = false;

                $pmargin = $this->extractStyleTags("EACHLINE", $this->all_page_page_styles, "margin", "left");
                $rgmargin = $this->extractStyleTags("EACHLINE", $styles, "margin", "right");
                $x = $this->all_page_page_styles["style_start"];
                $wd = $this->all_page_page_styles["style_width"];
                $this->setPosition($x, $this->group_header_start);
                $this->drawCell($wd, $this->vsize + 0, $tx, "PBF", 0, $just, "T", $link);
                $this->unapplyStyleTags("GROUPHEADER", $styles);
                $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
                $this->endLine();

                //$this->drawCell($group_data_width, $this->vsize, "");    // Blank cell to continue page breaking at this size
                $y = $this->document->GetY();

                // Store where group header reaches so we know where to start printing after row
                if ($y > $this->group_header_end) {
                    $this->group_header_end = $y;
                }

                if ($this->yjump) {
                    $this->setPosition(false, $y + $this->yjump);
                }

                //$this->applyStyleTags( "DEFAULT", $this->mid_page_page_styles);
                $this->setPosition($prevx, $prevy);
                continue;
            }

            $group_label = $col->getAttribute("group_header_label");
            if (!$group_label) {
                $group_label = $col->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $col->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }
            $group_label = swTranslate($group_label);

            $this->yjump = 2;
            // Fetch Group Header Label Start Column + display
            $margin = $this->extractStyleTags("EACHLINE", $this->query->output_group_header_label_styles, "margin", "right");
            $pmargin = $this->extractStyleTags("EACHLINE", $this->all_page_page_styles, "margin", "left");
            $rpmargin = $this->extractStyleTags("EACHLINE", $this->all_page_page_styles, "margin", "right");

            // Default group header label to 150px unless one is specifed
            $labelwidth = 150;
            if (isset($this->query->output_group_header_label_styles["width"])) {
                $labelwidth = $this->absMetric($this->query->output_group_header_label_styles["width"]);
            }

            $group_xpos = $this->all_page_page_styles["style_start"];
            $group_xpos += $pmargin;
            $group_label_width = $labelwidth;
            $group_data_xpos = $group_xpos + $labelwidth + $margin;
            $group_data_width = $this->all_page_page_styles["style_width"] - $labelwidth;
            $group_data_end = $this->all_page_page_styles["style_start"] + $this->all_page_page_styles["style_width"];

            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            if (sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->applyStyleTags("GROUPHEADERLABEL", $this->query->output_group_header_label_styles);
            }

            $this->setPosition($group_xpos, $y);
            $padstring = $group_label;
            $this->drawCell($group_label_width, $this->vsize, "$padstring");

            if (sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->unapplyStyleTags("GROUPHEADERLABEL", $this->query->output_group_header_label_styles);
            }

            // Display group header value
            $contenttype = $col->deriveAttribute("content_type", $col->query_name);
            if (sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->applyStyleTags("GROUPHEADERVALUE", $this->query->output_group_header_value_styles);
            }

            $qn = getQueryColumn($col->query_name, $this->query->columns);
            if ($contenttype == "graphic" || preg_match("/imagesql=/", $qn->column_value)) {
                if ($this->draw_mode == "CALCULATE") {
                    if (sessionRequestItem("target_style", "TABLE") != "FORM") {
                        $this->unapplyStyleTags("GROUPHEADERVALUE", $this->query->output_group_header_value_styles);
                    }

                    $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
                    continue;
                }

                $qn = getQueryColumn($col->query_name, $this->query->columns);
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

                if ($str) {
                    $tmpnam = tempnam(ReporticoApp::getConfig("tmp_dir"), "dbi");
                    unlink($tmpnam);
                    $width = $qn->abs_column_width;
                    $height = 20;
                    $im = imagecreatefromstring($str);

                    if (imagepng($im, $tmpnam . ".png")) {
                        $x = $qn->abs_column_start;
                        $y = $this->document->GetY();
                        $this->setPosition($group_data_xpos);
                        $h = $this->drawImage($tmpnam . ".png", $group_data_xpos, $y, $width * $this->pdfImageDPIScale, 0) + 2;
                        if ($h > $this->max_line_height) {
                            $this->max_line_height = $h;
                        }

                        $this->yjump = $h;
                        unlink($tmpnam . ".png");
                    }
                }
            } else {
                $this->setPosition($group_data_xpos, $y);
                $padstring = $qn->column_value;
                $this->drawCell($group_data_width, $this->vsize, "$padstring");
            }
            if (sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->unapplyStyleTags("GROUPHEADERVALUE", $this->query->output_group_header_value_styles);
            }

            $this->endLine();
            //$this->drawCell($group_data_width + 200, $this->vsize, "");    // Blank cell to continue page breaking at this size
            $y = $this->document->GetY();
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            // Store where group header reaches so we know where to start printing after row
            if ($y > $this->group_header_end) {
                $this->group_header_end = $y;
            }

            //if ( $this->yjump )
            //$this->setPosition(false, $y + $this->yjump);

        }
    }

    public function formatColumnHeader(&$column_item) //PDF column headers

    {
        if (!getReporticoSessionParam("target_show_detail")) {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $k = &$column_item->query_name;
        $padstring = $column_item->deriveAttribute("column_title", $column_item->query_name);
        $padstring = str_replace("_", " ", $padstring);
        $padstring = ucwords(strtolower($padstring));
        $padstring = swTranslate($padstring);

        $just = $this->justifys[$column_item->deriveAttribute("justify", "left")];

        $contenttype = $column_item->deriveAttribute(
            "content_type", $column_item->query_name);

        $tw = $column_item->abs_column_start;
        $x = $this->document->GetX();
        $y = $this->document->GetY();
        $this->setPosition($tw, $y);

        $wd = $column_item->abs_column_width;
        if ($wd - $this->column_spacing > 0) {
            $wd = $wd - $this->column_spacing;
        }

        if (!$wd) {
            $this->document->Write("$padstring");
        } else {
            $this->drawCell($wd, $this->vsize + 0, $padstring, "PBF", 0, $just, "B");
        }
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
        //$this->endLine();

        $tmpnam = tempnam(ReporticoApp::getConfig("tmp_dir"), "gph");

        if (ReporticoApp::getConfig("graph_engine") == "PCHART") {
            unlink($tmpnam);
            $img = $graph->generateGraphImage($tmpnam . ".png");
        } else /* If jpgraph */
        {
            $handle = $graph->generateGraphImage();
            unlink($tmpnam);
            $img = imagepng($handle, $tmpnam . ".png");
        }
        if ($img);
        {
            $x = $this->document->GetX();
            $y = $this->document->GetY();
            $this->setPosition($this->abs_left_margin, false);

            $width = $graph->width_pdf_actual;
            $height = $graph->height_pdf_actual;

            //if ( $width > ($this->abs_right_margin - $this->abs_left_margin) )
            //{
            //$height = $height * (  ($this->abs_right_margin - $this->abs_left_margin) / $width );
            ////$width = ($this->abs_right_margin - $this->abs_left_margin);
            //}
            $xaddon = ($this->abs_right_margin - $this->abs_left_margin - ($width * $this->pdfImageDPIScale)) / 2;
            if ($y + $height >= $this->abs_bottom_margin) {
                $this->finishPage();
                $this->beginPage();
                $x = $this->document->GetX();
                $y = $this->document->GetY();
            }

            $this->drawImage($tmpnam . ".png", $this->abs_left_margin + $xaddon, $y, $width * $this->pdfImageDPIScale, $height * $this->pdfImageDPIScale);
            $this->max_line_height = $height * $this->pdfImageDPIScale;
            $this->max_line_border_addition = 0;
            $this->newReportPageLineByStyle("RMB1", $this->mid_page_reportbody_styles, false);
            $y = $this->setPosition(false, $y + ($height * $this->pdfImageDPIScale));
            $this->max_line_height = 0;
        }
        unlink($tmpnam . ".png");
    }

    public function drawMulticell($w, $h, $txt, $border, $align, $fill, $link = false, $keepy = false)
    {
        $oldh = $h;

        $storeY = $this->document->GetY();

        if ($this->pdfDriver == "tcpdf") {
            if ($link) {
                $this->document->Write($h, "$txt", $link);
            } else
            if ($keepy) {
                $this->document->Multicell($w, $h, $txt, $border, $align, $fill, 0);
            } else {
                $this->document->Multicell($w, $h, $txt, $border, $align, $fill, 1);
            }

        } else {
            $this->document->MultiCell($w, $h, $txt, $border, $align, $fill, $link);
        }
        $h = $this->document->GetY() - $storeY;
        return $h;
    }

    public function drawImage($file, $x, $y, $w, $h, $hidden = false, $halign = "")
    {
        if ($this->pdfDriver == "tcpdf") {
            $imagehalign = "L";
            $imagevalign = "T";
            if ($halign) {
                if ($halign == "left") {
                    $imagehalign = "L";
                } else if ($halign == "right") {
                    $imagehalign = "R";
                }
            } else if ($halign == "center") {
                $imagehalign = "C";
            } else {
                $imagehalign = $halign;
            }

            $align = $imagehalign . $imagevalign;
            //$y = $this->document->GetY();
            //$y = $this->document->GetY();
            //$this->debugFile("DRAW $file image $x/$y $w / $h vs ".$this->document->getImageRBY() ." align : $halign $align ");
            //$h = $this->document->Image($file, $x, $y, $w, $h, '', '', '', false, 300, $imagehalign, false, false, 0, false, 0, "", $hidden);
            $h = $this->document->Image($file, $x, $y, $w, $h, '', '', '', false, 300, "", false, false, 0, $align, $hidden);
            //$this->debugFile("DRAW $file image $h vs ".$this->document->getImageRBY() ."");
            $h = $this->document->getImageRBY() - $y;
            //$this->debugFile("New eight $h");
            if ($h < 0) {
                $h = 0;
            }

        } else {
            if ($hidden) {
                $h = $this->document->ImageHeight($file, $x, $y, $w, $h);
            } else {
                $h = $this->document->Image($file, $x, $y, $w, $h);
            }

        }
        return $h;
    }

    /*
     * Apply styles to report detail block
     */
    public function formatReportDetailStart() // PDF

    {
        Report::formatReportDetailStart();
    }

    /*
     * Apply styles to report detail block
     */
    public function formatReportDetailEnd()
    {
        Report::formatReportDetailEnd();
    }

    /*
     * Checks if page changed and we need a new start of page block
     */
    public function checkForDetailPageStart() // PDF

    {
        //if ( $this->inOverflow )
        //return;

        if ($this->page_detail_started) {
            return;
        }

        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("RMB1", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("HMP1", $this->top_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("RMB1", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("HMP1", $this->top_page_page_styles, true);
        $this->page_detail_started = true;
    }

    /*
     * Checks if page changed and we need a new start of page block
     */
    public function checkForDetailPageEnd() // PDF

    {
        //if ( $this->inOverflow )
        //return;

        if (!$this->page_detail_started) {
            return;
        }

        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("RMB1", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("HMP1", $this->bottom_page_page_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("RMB1", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("HMP1", $this->bottom_page_page_styles, true);
        $this->page_detail_started = false;
    }

    public function formatHeaders($force = false) // PDF

    {
        if ($this->inOverflow && !$force) {
            return;
        }

        $this->checkLineRequirement($this->query->output_header_styles);

        $this->checkForDetailPageStart();

        if (sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        // Handle multi line headers by processing all headers
        // in "CALCULATE" mode and then print them on the appropriate line
        $this->draw_mode = "CALCULATE";
        $this->current_line_height = 0;
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $this->applyStyleTags("EACHHEADMID", $this->mid_row_page_styles);
        foreach ($this->columns as $w) {
            $this->applyStyleTags("HEADERS", $this->query->output_header_styles);
            $this->formatColumnHeader($w);
            $this->unapplyStyleTags("HEADERS", $this->query->output_header_styles);
        }
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_row_page_styles);
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

        $this->draw_mode = "DRAW";
        $this->checkPageOverflow();

        // Page Styles
        $this->newReportPageLineByStyle("REPTOPPAGE", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("LINE5PAGE", $this->mid_page_page_styles, false);

        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $this->applyStyleTags("EACHHEADMID", $this->mid_row_page_styles);
        foreach ($this->columns as $w) {
            $this->applyStyleTags("HEADERS", $this->query->output_header_styles);
            $this->formatColumnHeader($w);
            $this->unapplyStyleTags("HEADERS", $this->query->output_header_styles);
        }

        // Page Styles
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_row_page_styles);
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

        $this->endLine();
        $this->drawCell(5, $this->vsize, ""); // Blank cell to continue page breaking at this size

    }

    public function showXY($txt = "")
    {
        $x = $this->document->GetX();
        $y = $this->document->GetY();
        $this->setPosition(2, false);
        $txt .= " ($x, $y)";
        $this->document->Cell(140, 20, $txt);
        $this->setPosition($x, $y);
    }

    public function formatColumn(&$column_item) // PDF

    {
//if ( $this->line_count < 3 ) $this->debugFile("==========> FC $this->draw_mode $h =  $this->required_line_height");
        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        // Keep track of how many columns in current row to print if calculating
        // or already printed if drawing so we can calculate when to draw
        // borders around a cell
        if ($this->draw_mode == "CALCULATE") {
            $this->no_columns_to_print++;
            $this->no_columns_printed++;
        } else {
            $this->no_columns_printed++;
        }

        $k = &$column_item->column_value;
        $tw = $column_item->abs_column_start;
        $wd = $column_item->abs_column_width;

        if ($wd - $this->column_spacing > 0) {
            $wd = $wd - $this->column_spacing;
        }

        $just = $this->justifys[$column_item->deriveAttribute("justify", "left")];
        $contenttype = $column_item->deriveAttribute(
            "content_type", $column_item->query_name);

        if ($column_item->output_images) {
            $str = file_get_contents($column_item->output_images["image"]);
            if ($str) {
                $tmpnam = tempnam(SW_TMP_DIR, "dbi");
                unlink($tmpnam);
                $width = $column_item->abs_column_width;
                $height = 20;
                $im = imagecreatefromstring($str);

                if (imagepng($im, $tmpnam . ".png")) {
                    $x = $column_item->abs_column_start;
                    $y = $this->document->GetY();
                    $this->setPosition($x, false);
                    if ($this->draw_mode == "CALCULATE") {
                        $h = $this->drawImage($tmpnam . ".png", $x, $y, $width * $this->pdfImageDPIScale, 0, true) + 2;
                        if ($h > $this->max_line_height) {
                            $this->max_line_height = $h;
                        }

                        if ($h > $this->required_line_height) {
                            $this->required_line_height = $h;
                        }

                    } else {
                        $h = $this->drawImage($tmpnam . ".png", $x, $y, $width * $this->pdfImageDPIScale, 0) + 2;
                        if ($h > $this->current_line_height && !$this->ignore_height_checking) {
                            $this->current_line_height = $h;
                        }

                    }
                    if ($h > $this->yjump) {
                        $this->yjump = $h;
                    }

                    unlink($tmpnam . ".png");
                }
            }
        } else
        if ($contenttype == "graphic" || preg_match("/imagesql=/", $column_item->column_value)) {
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

            if ($str) {
                $tmpnam = tempnam(ReporticoApp::getConfig("tmp_dir"), "dbi");
                unlink($tmpnam);
                $width = $column_item->abs_column_width;
                $height = 20;
                $im = imagecreatefromstring($str);

                if (imagepng($im, $tmpnam . ".png")) {
                    $x = $column_item->abs_column_start;
                    $y = $this->document->GetY();
                    $this->setPosition($x, false);
                    if ($this->draw_mode == "CALCULATE") {
                        $h = $this->drawImage($tmpnam . ".png", $x, $y, $width * $this->pdfImageDPIScale, 0, true) + 2;
                        if ($h > $this->max_line_height) {
                            $this->max_line_height = $h;
                        }

                        if ($h > $this->required_line_height) {
                            $this->required_line_height = $h;
                        }

                    } else {
                        $h = $this->drawImage($tmpnam . ".png", $x, $y, $width * $this->pdfImageDPIScale, 0) + 2;
                        if ($h > $this->current_line_height && !$this->ignore_height_checking) {
                            $this->current_line_height = $h;
                        }

                    }
                    if ($h > $this->yjump) {
                        $this->yjump = $h;
                    }

                    unlink($tmpnam . ".png");
                }
            }
        } else {

            if (!$wd) {
                $this->document->Write("$padstring");
            } else {
                $this->setPosition($tw, false);

                $this->allcell_styles = array("border-edges" => "");
                $this->cell_styles = array("border-edges" => "");
                $this->applyStyleTags("COLUMNALL", $this->query->output_allcell_styles, false, false, "ALLCELLS");
                $this->applyStyleTags("COLUMNCELL", $column_item->output_cell_styles, false, false, "CELLS");
                //$this->applyRowBorderToCell ();
                if ($this->draw_mode == "DRAW") {
                    //$this->drawCellContainer($wd, $this->vsize + 4, $k,"PBR",0,$just);
                }
                $link = false;
                if ($column_item->output_hyperlinks) {
                    $link = $column_item->output_hyperlinks["url"];
                }

                //$this->max_line_border_addition = 0;

                $this->drawCell($wd, $this->required_line_height, "$k", "P", 0, $just, "T", $link); //PPP
                $this->unapplyStyleTags("COLUMNCELL", $column_item->output_cell_styles);
                $this->unapplyStyleTags("COLUMNALL", $this->query->output_allcell_styles);
                $tw = $this->abs_page_width - $this->abs_right_margin;
            }
        }
    }

    // If first cell in row then, if row has border then ensure it
    // is drawn with top, bottow, left border, last cell has right, top, bottom
    // middle cells have top and bottom
    // first column of a row has parent row with border
    public function applyRowBorderToCell()
    {
        if (isset($this->row_styles["border-edges"]) && $this->row_styles["border-edges"]) {
            $cellstyle = $this->cell_styles["border-edges"];
            if (!$cellstyle) {
                $cellstyle = $this->allcell_styles["border-edges"];
            }
            $rowstyle = $this->row_styles["border-edges"];
            if (!strstr($cellstyle, "T") && strstr($rowstyle, "T")) {
                $cellstyle .= "T";
            }

            if (!strstr($cellstyle, "B") && strstr($rowstyle, "B")) {
                $cellstyle .= "B";
            }

            if ($this->no_columns_printed == 1) {
                if (!strstr($cellstyle, "L") && strstr($rowstyle, "L")) {
                    $cellstyle .= "L";
                }

            }
            if ($this->no_columns_printed == $this->no_columns_to_print) {
                if (!strstr($cellstyle, "R") && strstr($rowstyle, "R")) {
                    $cellstyle .= "R";
                }

            }
            end($this->stylestack["border-edges"]);
            $this->stylestack["border-edges"][key($this->stylestack["border-edges"])] = $cellstyle;
        }
    }

    // Removes a tag element from the style stack
    public function setStyleTag($styleset, $tag, $value)
    {
        $styleset[$tag] = $value;
    }

    // Removes a tag element from the style stack
    public function disableStyleTag($styleset, $tag)
    {
        foreach ($styleset as $k => $v) {
            if (isset($this->stylestack[$k]) && $k == $tag) {
                unset($this->stylestack[$k]);
            }
        }
    }

    public function removeStyleTags($type, &$work_styleset, $want, $bit = false)
    {
        if ($work_styleset && is_array($work_styleset)) {
            foreach ($work_styleset as $k => $v) {
                if ($k != $want) {
                    continue;
                }

                if (!$bit) {
                    unset($work_styleset[$k]);
                    return;
                }
                if ($k == "margin" || $k == "padding" || $k == "border-width") {
                    $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                    //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                    $ar = explode(" ", $v);
                    if ($ar) {
                        if (count($ar) == 1 && $ar[0] > 0) {
                            $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                        } else if (count($ar) == 2) {
                            $tmp[0] = $tmp[2] = $ar[1];
                            $tmp[1] = $tmp[3] = $ar[2];
                        }
                    } else if (count($ar) == 3) {
                        $tmp[0] = "0mm";
                        $tmp[1] = $ar[1];
                        $tmp[2] = $ar[2];
                        $tmp[3] = $ar[2];
                    } else if (count($ar) == 4) {
                        $tmp[0] = $ar[0];
                        $tmp[1] = $ar[1];
                        $tmp[2] = $ar[2];
                        $tmp[3] = $ar[3];
                    }
                    if ($bit == "top") {
                        $tmp[0] = "0";
                    }

                    if ($bit == "right") {
                        $tmp[1] = "0";
                    }

                    if ($bit == "bottom") {
                        $tmp[2] = "0";
                    }

                    if ($bit == "left") {
                        $tmp[3] = "0";
                    }

                    $work_styleset[$k] = $tmp[0] . " " . $tmp[1] . " " . $tmp[2] . " " . $tmp[3];
                }
                if ($k == "margin-top") {
                    unset($work_styleset[$k]);
                }
                if ($k == "position") {
                    unset($work_styleset[$k]);
                }
            }
        }
    }

    public function extractStyleTags($type, $styleset, $want, $bit = false)
    {
        $work_styleset = &$styleset;

        if ($work_styleset && is_array($work_styleset)) {
            foreach ($work_styleset as $k => $v) {
                if ($k != $want) {
                    continue;
                }

                //if ( $k == "requires-before" || isset ( $this->stylestack[$k] ) )
                {
                    if ($k == "margin") {
                        $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode(" ", $v);
                        if ($ar) {
                            if (count($ar) == 1 && $ar[0] > 0) {
                                $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                            } else if (count($ar) == 2) {
                                $tmp[0] = $tmp[2] = $ar[1];
                                $tmp[1] = $tmp[3] = $ar[2];
                            }
                        } else if (count($ar) == 3) {
                            $tmp[0] = $ar[0];
                            $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                        } else if (count($ar) == 4) {
                            $tmp[0] = $ar[0];
                            $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                            $tmp[3] = $ar[3];
                        }
                        if ($bit == "top") {
                            return $tmp[0];
                        }

                        if ($bit == "left") {
                            return $tmp[3];
                        }

                        if ($bit == "right") {
                            return $tmp[1];
                        }

                        if ($bit == "bottom") {
                            return $tmp[2];
                        }

                        return $tmp;
                    }

                    if ($k == "padding") {
                        $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode(" ", $v);
                        if ($ar) {
                            if (count($ar) == 1 && $ar[0] > 0) {
                                $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                            } else if (count($ar) == 2) {
                                $tmp[0] = $tmp[2] = $ar[1];
                                $tmp[1] = $tmp[3] = $ar[2];
                            }
                        } else if (count($ar) == 3) {
                            $tmp[0] = $ar[0];
                            $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                        } else if (count($ar) == 4) {
                            $tmp[0] = $ar[0];
                            $tmp[1] = $ar[1];
                            $tmp[2] = $ar[2];
                            $tmp[3] = $ar[3];
                        }
                        if ($bit == "top") {
                            return $tmp[0];
                        }

                        if ($bit == "left") {
                            return $tmp[3];
                        }

                        if ($bit == "right") {
                            return $tmp[1];
                        }

                        if ($bit == "bottom") {
                            return $tmp[2];
                        }

                        return $tmp;
                    }

                    if ($k == "border-width") {
                        $tmp = "";
                        $v = preg_replace("/px/", "", trim($v));
                        //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                        $ar = explode(" ", $v);
                        $borderwidth = 0;
                        if ($ar) {
                            if (count($ar) == 1 && $ar[0] > 0) {
                                $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                            } else if (count($ar) == 2) {
                                $tmp[0] = $tmp[2] = $ar[1];
                                $tmp[1] = $tmp[3] = $ar[2];
                            } else if (count($ar) == 3) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                            } else if (count($ar) == 4) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                                $tmp[3] = $ar[3];
                            }
                        }
                        if ($bit == "top") {
                            return $tmp[0];
                        }

                        if ($bit == "left") {
                            return $tmp[3];
                        }

                        if ($bit == "right") {
                            return $tmp[1];
                        }

                        if ($bit == "bottom") {
                            return $tmp[2];
                        }

                        return $tmp;
                    }

                    if ($k == "width") {
                        $tmp = preg_replace("/px/", "", trim($v));
                        return $tmp;
                    }
                    return $v;
                }
            }
        }
        //echo "&nbsp;&nbsp;APPLY: $type<BR> ";
        //var_dump($this->stylestack["type"]);
    }

    public function applyStyleTags($type, $styleset, $parent_styleset = false, $grandparent_styleset = false, $apply_type = false, $applyto = false)
    {
        $styleset["type"] = $type;
        for ($ct = 1; $ct < 4; $ct++) {
            $work_styleset = false;
            if ($ct == 1) {
                $work_styleset = &$grandparent_styleset;
            }

            if ($ct == 2) {
                $work_styleset = &$parent_styleset;
            }

            if ($ct == 3) {
                $work_styleset = &$styleset;
            }

            if (!$work_styleset) {
                continue;
            }

            if ($work_styleset && is_array($work_styleset)) {
                foreach ($work_styleset as $k => $v) {
                    // Dont apply anything except the applyto specified
                    if ($applyto && $ct == 3 && $applyto != $k) {
                        continue;
                    }

                    if (isset($this->stylestack[$k])) {
                        if ($k == "margin") {
                            $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode(" ", $v);
                            if ($ar) {
                                if (count($ar) == 1 && $ar[0] > 0) {
                                    $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                                } else if (count($ar) == 2) {
                                    $tmp[0] = $tmp[2] = $ar[1];
                                    $tmp[1] = $tmp[3] = $ar[2];
                                }
                            } else if (count($ar) == 3) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                            } else if (count($ar) == 4) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                                $tmp[3] = $ar[3];
                            }
                            $v = $tmp;
                        }

                        if ($k == "padding") {
                            $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode(" ", $v);
                            if ($ar) {
                                if (count($ar) == 1 && $ar[0] > 0) {
                                    $tmp[0] = $tmp[1] = $tmp[2] = $tmp[3] = $ar[0];
                                } else if (count($ar) == 2) {
                                    $tmp[0] = $tmp[2] = $ar[1];
                                    $tmp[1] = $tmp[3] = $ar[2];
                                }
                            } else if (count($ar) == 3) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                            } else if (count($ar) == 4) {
                                $tmp[0] = $ar[0];
                                $tmp[1] = $ar[1];
                                $tmp[2] = $ar[2];
                                $tmp[3] = $ar[3];
                            }
                            $v = $tmp;
                        }

                        if ($k == "border-width") {
                            $tmp = "";
                            $v = preg_replace("/px/", "", trim($v));
                            //$ar = explode ( ",", preg_replace("/[^0-9]+/", ",", $v));
                            $ar = explode(" ", $v);
                            $borderwidth = 0;
                            if ($ar) {
                                foreach ($ar as $vv) {
                                    if (substr($vv, 0, 1) != "0") {
                                        $borderwidth = $vv;
                                        break;
                                    }
                                }
                                if (count($ar) == 1 && $ar[0] > 0) {
                                    $tmp = "LBTR";
                                } else if (count($ar) == 2) {
                                    if ($ar[0] > 0) {
                                        $tmp .= "TB";
                                    }

                                    if ($ar[1] > 0) {
                                        $tmp .= "LR";
                                    }

                                } else if (count($ar) == 3) {
                                    if ($ar[0] > 0) {
                                        $tmp .= "T";
                                    }

                                    if ($ar[1] > 0) {
                                        $tmp .= "R";
                                    }

                                    if ($ar[2] > 0) {
                                        $tmp .= "B";
                                    }

                                } else if (count($ar) == 4) {
                                    if ($ar[0] > 0) {
                                        $tmp .= "T";
                                    }

                                    if ($ar[1] > 0) {
                                        $tmp .= "R";
                                    }

                                    if ($ar[2] > 0) {
                                        $tmp .= "B";
                                    }

                                    if ($ar[3] > 0) {
                                        $tmp .= "L";
                                    }

                                }
                            }
                            $borderedges = $tmp;
                            $v = $borderwidth;
                            if ($apply_type == "ROW") {
                                $this->row_styles["border-width"] = $borderwidth;
                                $this->row_styles["border-edges"] = $v;
                            }
                            if ($apply_type == "ALLCELLS") {
                                $this->allcell_styles["border-width"] = $borderwidth;
                                $this->allcell_styles["border-edges"] = $v;
                            }
                            if ($apply_type == "CELLS") {
                                $this->cell_styles["border-width"] = $borderwidth;
                                $this->cell_styles["border-edges"] = $v;
                            }
                            array_push($this->stylestack["border-edges"], $borderedges);
                        }
                        if ($k == "font-family") {
                            $this->document->SetFont($v);
                        }
                        if ($k == "font-size") {
                            $sz = preg_replace("/[^0-9].*/", "", $v);
                            $this->document->SetFontSize($sz);
                            $v = $sz + $this->vspace;
                            $this->vsize = $v;
                        }
                        if ($k == "font-style") {
                            $currWeight = end($this->stylestack["font-weight"]);
                            $currFamily = end($this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ($currWeight) {
                                case "bold":$pdfStyle .= "B";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            switch ($v) {
                                case "italic":$pdfStyle .= "I";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ($k == "font-weight") {
                            $currStyle = end($this->stylestack["font-style"]);
                            $currFamily = end($this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ($v) {
                                case "bold":$pdfStyle .= "B";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            switch ($currStyle) {
                                case "italic":$pdfStyle .= "I";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ($k == "border-color" || $k == "color" || $k == "background-color") {
                            $v = htmltorgb($v);
                            if ($k == "border-color") {
                                $this->document->SetDrawColor($v[0], $v[1], $v[2]);
                            }

                            if ($k == "color") {
                                $this->document->SetTextColor($v[0], $v[1], $v[2]);
                            }
                            if ($k == "background-color") {
                                $this->document->SetFillColor($v[0], $v[1], $v[2]);
                                array_push($this->stylestack["isfilling"], 1);
                            }
                        }

                        array_push($this->stylestack[$k], $v);
                    }
                }
            }
        }
        //echo "APPLY: $type ";
        //echo "&nbsp;&nbsp;APPLY: $type ";
        //var_dump($this->stylestack["type"]);
        //var_dump($this->stylestack["background-color"]);
    }

    public function unapplyStyleTags($type1, $styleset, $parent_styleset = false, $grandparent_styleset = false, $type = "", $applyto = false)
    {
        $styleset["type"] = $type;
        for ($ct = 1; $ct < 4; $ct++) {
            $work_styleset = false;
            if ($ct == 1) {
                $work_styleset = &$grandparent_styleset;
            }

            if ($ct == 2) {
                $work_styleset = &$parent_styleset;
            }

            if ($ct == 3) {
                $work_styleset = &$styleset;
            }

            if (!$work_styleset) {
                continue;
            }

            if ($work_styleset && is_array($work_styleset)) {
                foreach ($work_styleset as $k => $v) {
                    // Dont apply anything except the applyto specified
                    if ($applyto && $ct == 3 && $applyto != $k) {
                        continue;
                    }

                    if (isset($this->stylestack[$k])) {
                        $value = array_pop($this->stylestack[$k]);
                        $value = end($this->stylestack[$k]);
                        if ($k == "font-size") {
                            $this->vsize = $value;
                        }
                        if ($k == "color") {
                            $this->document->SetTextColor($value[0], $value[1], $value[2]);
                        }
                        if ($k == "border-color") {
                            $this->document->SetDrawColor($value[0], $value[1], $value[2]);
                        }
                        if ($k == "font-family") {
                            $this->document->SetFont($value);
                        }
                        if ($k == "font-style") {
                            $currWeight = end($this->stylestack["font-weight"]);
                            $currFamily = end($this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ($currWeight) {
                                case "bold":$pdfStyle .= "B";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            switch ($value) {
                                case "italic":$pdfStyle .= "I";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ($k == "font-weight") {
                            $currStyle = end($this->stylestack["font-style"]);
                            $currFamily = end($this->stylestack["font-family"]);
                            $pdfStyle = "";
                            switch ($value) {
                                case "bold":$pdfStyle .= "B";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            switch ($currStyle) {
                                case "italic":$pdfStyle .= "I";
                                    break;
                                default:$pdfStyle .= "";
                            }
                            $this->document->SetFont($currFamily, $pdfStyle);
                            //$v = $sz + $this->vspace;
                            //$this->vsize = $v;
                        }
                        if ($k == "font-size") {
                            $sz = preg_replace("/[^0-9].*/", "", $value);
                            $this->document->SetFontSize($sz);
                            $value = $sz + $this->vspace;
                            $this->vsize = $value;
                        }
                        if ($k == "background-color") {
                            $this->document->SetFillColor($value[0], $value[1], $value[2]);
                            $value = array_pop($this->stylestack["isfilling"]);
                        }
                        if ($k == "border-width") {
                            $value = array_pop($this->stylestack["border-edges"]);
                        }
                    }
                }
            }
        }
    }

    public function newReportPageLine($txt = "")
    {
        $this->newReportPageLineByStyle("LINEBODY$txt", $this->mid_page_reportbody_styles, false);
        $this->newReportPageLineByStyle("LINEPAGE$txt", $this->mid_page_page_styles, false);
    }

    public function newReportPageLineByStyle($txt = "", &$styles, $blankline = false)
    {
        // Line page wrapper
        $this->applyStyleTags("$txt", $styles);
        $tw = $styles["style_start"];
        $wd = $styles["style_width"];
        $this->setPosition($tw);
        if ($blankline) {
            $oldSize = $this->document->GetFontSizePt();
            // Blank cell to continue page breaking at this size
            $this->drawCell($wd, 0, ""); // Blank cell to continue page breaking at this size
            $this->document->SetFontSize($oldSize);
            if ($this->draw_mode == "DRAW") {
                $this->endLine(0);
                $this->setPosition($tw);
                $this->setPosition($tw, $this->document->GetY() - 0.2);
            }
        } else {
            $oldSize = $this->document->GetFontSizePt();
            $this->document->SetFontSize(0);
            $this->ignore_height_checking = true;
            $this->drawCell($wd, $this->max_line_height + $this->max_line_border_addition + 0.5, ""); // Blank cell to continue page breaking at this size
            $this->ignore_height_checking = false;
            $this->document->SetFontSize($oldSize);
        }

        $this->unapplyStyleTags("$txt", $styles);
    }

    public function eachLine($val) // PDF

    {

        if (!$this->columns_calculated) {
            // Calulate position and width of column detail taking into account
            // Report Body and Page styles
            $this->calculateColumnMetrics();
            $this->columns_calculated = true;
        }

        Report::eachLine($val);
        if (sessionRequestItem("target_style", "TABLE") == "FORM") {
            $this->endLine();

            // Throw new page if set to throw between rows
            $formpagethrow = $this->query->getAttribute("formBetweenRows");
            if ($this->line_count > 1 && $formpagethrow == "newpage") {
                $this->finishPage();
                $this->beginPage();
            }

            // Throw new page if current position + number headers + line + headers > than bottom margin
            $ln = 0;
            $totheaderheight = 0;
            $prevheight = $this->calculated_line_height;

            $this->applyStyleTags("ROW", $this->query->output_before_form_row_styles);
            $y = $this->document->GetY();
            $this->setPosition($this->abs_left_margin, $y);
            $this->unapplyStyleTags("ROW", $this->query->output_before_form_row_styles);

            foreach ($this->query->groups as $val) {
                for ($i = 0; $i < count($val->headers); $i++) {
                    $col = &$val->headers[$i]["GroupHeaderColumn"];
                    $this->formatGroupHeader($col, false);
                    $totheaderheight += $this->calculated_line_height;
                }
            }
            foreach ($this->query->display_order_set["column"] as $k => $w) {
                if ($w->attributes["column_display"] != "show") {
                    continue;
                }

                $ct++;

                $this->formatGroupHeader($w, false);
                $totheaderheight += $this->calculated_line_height;
            }
            $this->calculated_line_height = $totheaderheight;
            $y = $this->document->GetY();
            $this->checkPageOverflow();
            $this->calculated_line_height = $prevheight;

            // Between form solid line or blank line
            if ($formpagethrow == "blankline") {
                $this->endLine();
                $this->endLine();
            } else {
                $this->endLine();
                $this->applyStyleTags("AFTERFORMROW", $this->query->output_after_form_row_styles);
                $y = $this->document->GetY();
                $this->setPosition($this->abs_left_margin, $y);
                $this->drawCell($this->abs_right_margin - $this->abs_left_margin, $this->vsize, "RR"); // Blank cell to continue page breaking at this size
                $this->unapplyStyleTags("AFTERFORMROW", $this->query->output_after_form_row_styles);
                $this->endLine();

            }

            return;
        }

        $y = $this->document->GetY();
        $this->checkGraphicFit();

        $this->yjump = 0;
        if ($this->body_display == "show" && getReporticoSessionParam("target_show_detail")) {
            $this->row_styles = array();
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles, false, false, "ROW");
            $this->applyStyleTags("EACHLINEMID", $this->mid_row_page_styles, false, false, "ROW");
            $this->applyStyleTags("ROW2", $this->mid_cell_row_styles, false, false, "ROW");

            $this->draw_mode = "CALCULATE";
            $this->no_columns_printed = 0;
            $this->no_columns_to_print = 0;
            $this->cell_row_top_addition = $this->all_page_row_styles["style_border_top"];
            $this->cell_row_bottom_addition = $this->all_page_row_styles["style_border_bottom"];
            foreach ($this->columns as $col) {
                $this->formatColumn($col);
            }

            $this->cell_row_top_addition = 0;
            $this->cell_row_bottom_addition = 0;
            $this->unapplyStyleTags("ROW2", $this->mid_cell_row_styles);
            $this->unapplyStyleTags("EACHLINEMID", $this->mid_row_page_styles);
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            $this->draw_mode = "DRAW";

            $this->checkPageOverflow();

            $prev_calculated_line_height = $this->calculated_line_height;
            $prev_current_line_height = $this->current_line_height;
            $prev_max_line_height = $this->max_line_height;
            $prev_required_line_height = $this->required_line_height;

            if ($this->column_header_required) {
                $this->formatHeaders();
                $this->column_header_required = false;
            }

            $this->current_line_height = $prev_current_line_height;
            $this->calculated_line_height = $prev_calculated_line_height;
            $this->max_line_height = $prev_max_line_height;
            $this->required_line_height = $prev_required_line_height;

            // Line page wrapper
            $this->newReportPageLineByStyle("LINE5PAGE", $this->mid_page_reportbody_styles, false);
            $this->newReportPageLineByStyle("LINE2PAGE", $this->mid_page_page_styles, false);
            $this->newReportPageLineByStyle("LINE2PAGE", $this->all_page_row_styles, false);

            // Page Styles
            $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles, false, false, "ROW");
            $this->applyStyleTags("EACHLINEMID", $this->mid_row_page_styles, false, false, "ROW");
            $this->applyStyleTags("ROW2", $this->mid_cell_row_styles, false, false, "ROW");

            $this->cell_row_top_addition = $this->all_page_row_styles["style_border_top"];
            $this->cell_row_bottom_addition = $this->all_page_row_styles["style_border_bottom"];

            $this->no_columns_printed = 0;
            foreach ($this->columns as $col) {
                $this->formatColumn($col);
            }

            $this->cell_row_top_addition = 0;
            $this->cell_row_bottom_addition = 0;

            $this->page_line_count++;

            $this->unapplyStyleTags("ROW2", $this->mid_cell_row_styles);
            $this->unapplyStyleTags("EACHLINEMID", $this->mid_row_page_styles);
            $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

            $nextliney = $this->document->GetY() + $this->max_line_height;
            $this->endLine();
        }
    }

    public function checkLineRequirement(&$styleset)
    {
        if (!isset($styleset["requires-before"])) {
            return;
        }

        $requires = $this->absMetric($this->extractStyleTags("EACHLINE", $styleset, "requires-before"));
        if (!$requires) {
            return;
        }

        if ($this->document->GetY() + $requires > $this->abs_bottom_margin) {
            $this->finishPage();
            $this->beginPage();

            //$this->beforeGroupHeaders();
            $this->page_line_count++;
        }

    }

    public function checkPageOverflow()
    {
        if ($this->inOverflow) {
            return;
        }

        $this->inOverflow = true;

        $y = $this->document->GetY();
        //if ( $y + $this->calculated_line_height > $this->abs_bottom_margin )
        if ($y + $this->max_line_height > $this->page_footer_start_y) {
            // Between page breaks store any current lin eparameters
            $prev_calculated_line_height = $this->calculated_line_height;
            $prev_current_line_height = $this->current_line_height;
            $prev_max_line_height = $this->max_line_height;

            $this->finishPage();
            $this->beginPage();
            if ($this->page_broken_mid_page) {
                $this->column_header_required = true;
            }

            //$this->beforeGroupHeaders();
            $this->page_line_count++;
            $this->calculated_line_height = $prev_calculated_line_height;
            $this->max_line_height = $prev_max_line_height;
        }
        $this->inOverflow = false;
    }

    public function checkGraphicFit()
    {
        $will_fit = true;
        $max_height = $this->vsize;
        foreach ($this->columns as $col) {
            $contenttype = $col->deriveAttribute("content_type", $col->query_name);
            $qn = getQueryColumn($col->query_name, $this->query->columns);
            if ($contenttype == "graphic" || preg_match("/imagesql=/", $qn->column_value)) {
                $qn = getQueryColumn($col->query_name, $this->query->columns);
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

                if ($str) {
                    //$im = convert_image_string_to_image($str, "png");
                    $tmpnam = tempnam(ReporticoApp::getConfig("tmp_dir"), "dbi");
                    unlink($tmpnam);
                    $width = $qn->abs_column_width;
                    $height = 20;
                    $im = imagecreatefromstring($str);

                    if (imagepng($im, $tmpnam . ".png")) {
                        $h = $this->drawImage($tmpnam . ".png", 0, 0, $width, 0, true);
                        unlink($tmpnam . ".png");
                        if ($max_height < $h) {
                            $max_height = $h;
                        }

                    }
                }
            }
        }

        $y = $this->document->GetY();

        if ($y + $max_height/*+ 10*/ > $this->abs_bottom_margin) {
            $this->finishPage();
            $this->beginPage();

            //$this->beforeGroupHeaders();
            $this->page_line_count++;
        }

    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }

    public function setPosition($x = false, $y = false)
    {
        if ($this->draw_mode == "CALCULATE") {
            return;
        }

        if ($x && $y) {
            $this->document->SetXY($x, $y);
            $this->current_line_start_y = ($y);
        } else
        if ($y) {
            $this->document->SetXY($this->document->GetX(), $y);
            $this->current_line_start_y = ($y);
        }
        if ($x) {
            $this->document->SetX($x, $this->document->GetY());
        }
    }

    public function beginPage()
    {
        Report::beginPage();

        $this->page_footer_start_y = $this->abs_bottom_margin;
        $this->page_header_start_y = $this->abs_top_margin;
        $this->page_number++;

        $this->document->AddPage($this->orientations[$this->orientation]);

        $font = $this->document->SetFont($this->fontName);
        $font = $this->document->SetFontSize($this->vsize);

        $this->setPosition($this->abs_left_margin, $this->abs_top_margin);
        $this->current_line_start_y = $this->document->GetY();

        // Page Headers
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        Report::pageHeaders();
        $prevx = $this->document->GetX();
        $prevy = $this->document->GetY();
        $this->endLine();
        $this->endLine();
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);

        // Page Footers
        $this->applyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        $this->pageFooters();
        $this->unapplyStyleTags("EACHHEADMID", $this->mid_cell_reportbody_styles);
        //$this->document->SetAutoPageBreak(true, $this->abs_page_height - $this->abs_bottom_margin );

        //$this->applyStyleTags( "DEFAULT", $this->mid_page_page_styles);
        $this->setPosition($prevx, $this->page_header_end_y);

        $this->group_header_start = 0;
        $this->group_header_end = 0;

        // Start report body
        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("REPTOPBODY", $this->top_page_reportbody_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("REPTOPBODY", $this->top_page_reportbody_styles, true);

        //$this->applyStyleTags( "PAGEBODY", $this->query->output_reportbody_styles);
        $this->page_detail_started = false;

        if ($this->page_broken_mid_page) {
            $this->checkForDetailPageStart();
        }

        if ($this->page_broken_mid_page) {
            $this->column_header_required = true;
        }

    }

    public function finishPage()
    {
        $this->debug("Finish Page");

        $this->current_line_height = 0;
        $this->max_line_height = 0;

        $this->page_broken_mid_page = $this->page_detail_started;

        $this->checkForDetailPageEnd();

        // if page styles on turn them off
        if ($this->detail_started) {
            $this->formatReportDetailEnd();
        }

        $this->draw_mode = "CALCULATE";
        $this->newReportPageLineByStyle("ENDPAGE", $this->bottom_page_reportbody_styles, true);
        $this->draw_mode = "DRAW";
        $this->newReportPageLineByStyle("ENDPAGE", $this->bottom_page_reportbody_styles, true);
    }

    public function publish()
    {
        Report::publish();
        $this->debug("Publish PDF");
    }

    public function formatPageHeader(&$header)
    {
        $startcol = $header->getAttribute("ColumnStartPDF");
        $tw = $this->absPagingWidth($startcol);
        if (!$tw) {
            $tw = $this->abs_left_margin;
        }

        $inhtml = $header->getAttribute("ShowInHTML");
        $inpdf = $header->getAttribute("ShowInPDF");

        $wd = $header->getAttribute("ColumnWidthPDF");
        if (!$wd) {
            if ($this->abs_right_margin > $tw) {
                $wd = $this->abs_right_margin - $tw;
            } else {
                $wd = "100%";
            }
        }

        $wd = $this->absPagingWidth($wd);

        $just = $this->justifys[$header->deriveAttribute("justify", "left")];

        $y = $this->abs_top_margin + ($this->vsize * ($header->line - 1));
        $this->setPosition($tw, $y);

        $tx = $header->text;
        $styles = $this->fetchCellStyles($tx);
        $this->applyStyleTags("PAGEHEADER", $styles);
        $this->drawCell($wd, $this->vsize, $tx, "PBF", 0, $just);
        $this->unapplyStyleTags("PAGEHEADER", $styles);
        $this->endLine();
        $y = $this->last_draw_end_y;
        if ($y > $this->page_header_end_y) {
            $this->page_header_end_y = $y + 10;
        }
        return;
    }

    public function formatPageFooter(&$footer)
    {
        $startcol = $footer->getAttribute("ColumnStartPDF");
        $tw = $this->absPagingWidth($startcol);
        if (!$tw) {
            $tw = $this->abs_left_margin;
        }

        $wd = $footer->getAttribute("ColumnWidthPDF");
        if (!$wd) {
            if ($this->abs_right_margin > $tw) {
                $wd = $this->abs_right_margin - $tw;
            } else {
                $wd = "100%";
            }
        }

        $wd = $this->absPagingWidth($wd);

        $just = $this->justifys[$footer->deriveAttribute("justify", "left")];

        $y = $this->abs_bottom_margin - ($this->vsize * $footer->line);
        $this->setPosition($tw, $y);

        $this->page_footer_start_y = $y;

        // Set page to throw taking account of page and body end heights
        $this->page_footer_start_y -= $this->page_footer_wrapper_offset - 2;

        $tx = $footer->text;
        $styles = $this->fetchCellStyles($tx);
        $this->applyStyleTags("PAGEFOOTER", $styles);
        $this->drawCell($wd, $this->vsize, $tx, "PBF", 0, $just);
        $this->unapplyStyleTags("PAGEFOOTER", $styles);
        $this->endLine();

        return;
    }

    public function formatFormat($in_value, $format)
    {
        switch ($in_value) {
            case "blankline":
                //$this->endLine();
                break;

            case "solidline":
                $y = $this->document->GetY();
                $this->endLine();
                break;

            case "newpage":
                $this->finishPage();
                $this->beginPage();
                break;

            default:
                $this->endLine();
                break;

        }
    }

    public function debugFile($txt)
    {
        if (!$this->debugFp) {
            $this->debugFp = fopen("/tmp/debug.out", "w");
        }

        if ($txt == "FINISH") {
            fclose($this->debugFp);
        } else {
            fwrite($this->debugFp, "$txt\n");
        }

        //fwrite ( $this->debugFp, "$txt => Max $this->max_line_height Curr $this->current_line_height \n" );

    }

}
