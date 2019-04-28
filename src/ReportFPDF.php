<?php
/*

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
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */

namespace Reportico\Engine;

// -----------------------------------------------------------------------------
// Class ReportFPDF
// -----------------------------------------------------------------------------
class ReportFPDF extends Report
{
    public $abs_top_margin;
    public $abs_bottom_margin;
    public $abs_left_margin;
    public $abs_right_margin;
    public $orientation;
    public $page_type;
    public $column_order;
    public $fontName;
    public $fontSize;
    public $vsize;
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
    public $calculated_line_height = 0;
    public $max_line_height = 0;

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

    // Drawing mode, in Calculate mode we run through a line of values calculating
    // total width and height and then draw all text elements in Draw mode based
    // on knowing how wide things are
    public $draw_mode = "DRAW";

    // Factor to apply to image pixel size to get them to show at correct size in PDF document
    public $pdfImageDPIScale = 0.72;

    public function __construct()
    {
        $this->column_spacing = 0;
    }

    // For each line reset styles to default values
    public function setDefaultStyles()
    {
        Report::setDefaultStyles();

        // Default column headers to underlined if not specified
        if (!$this->query->output_header_styles) {
            $this->query->output_header_styles["border-style"] = "solid";
            $this->query->output_header_styles["border-width"] = "0 0 1 0";
            $this->query->output_header_styles["border-color"] = array(0, 0, 0);
        }

        if (!$this->query->output_before_form_row_styles) {
            $this->query->output_before_form_row_styles["border-style"] = "solid";
            $this->query->output_before_form_row_styles["border-width"] = "0 0 0 0";
            $this->query->output_before_form_row_styles["border-color"] = array(0, 0, 0);
        }

        if (!$this->query->output_after_form_row_styles) {
            $this->query->output_after_form_row_styles["border-style"] = "solid";
            $this->query->output_after_form_row_styles["border-width"] = "1 0 0 0";
            $this->query->output_after_form_row_styles["border-color"] = array(0, 0, 0);
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

        if (isset($this->query->output_page_styles["background-color"])) {
            unset($this->query->output_page_styles["background-color"]);
        }

    }

    public function start($engine = false)
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

        // Set up default styles
        $this->stylestack = array(
            "border-width" => array(0 => ""),
            "padding" => array(0 => false),
            "border-style" => array(0 => "none"),
            "border-color" => array(0 => "#000000"),
            "font-size" => array(0 => $this->fontSize),
            "color" => array(0 => "#000000"),
            "background-color" => array(0 => array(255, 255, 255)),
            "isfilling" => array(0 => false),
            "padding" => array(0 => 0),
        );

        // If font used is a Unicode Truetype font then
        // use Unicode PDF generator
        $pdf_path = ReporticoUtility::findBestLocationInIncludePath("fpdf");

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

        $this->document->SetAutoPageBreak(false);
        $this->document->SetMargins(0, 0, 0);
        $this->document->SetCreator('Reportico');
        $this->document->SetAuthor('Reportico');
        $this->document->SetTitle($this->reporttitle);

        // Calculate column print and width poistions based on the column start attributes
        $looping = true;

        foreach ($this->query->display_order_set["column"] as $k => $w) {
            $col = ReporticoUtility::getQueryColumn($w->query_name, $this->query->columns);
            $startcol = $col->attributes["ColumnStartPDF"];
            $colwidth = $col->attributes["ColumnWidthPDF"];
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
                    $col = ReporticoUtility::getQueryColumn($w->query_name, $this->query->columns);
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
                            $col->abs_column_start = $this->abs_left_margin;
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
                $topos = $this->abs_right_margin;
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
                        if ($colwidth) {
                            $col1->abs_column_width = $this->absPagingWidth($colwidth);
                            $lastpos = $col1->abs_column_start + $col1->abs_column_width;
                        } else {
                            $col1->abs_column_width = $calccolwidth;
                            $lastpos = $col1->abs_column_start + $calccolwidth;
                        }
                    } else {
                        $colwidth = $col1->attributes["ColumnWidthPDF"];
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

        //if ( $this->line_count < 1 )
        //{
        //// No PDF data found just return
        //return;
        //}

        $this->document->SetDisplayMode("real");
        //$this->document->pdf_close($this->document);
        if ($this->report_file) {
            $this->debug("Saved to $this->report_file");
        } else {
            $this->debug("No pdf file specified !!!");
            //$buf = $this->document->pdf_get_buffer($this->document);
            $buf = $this->document->Output("", "S");
            $len = strlen($buf);

            if (ob_get_length() > 0) {
                ob_clean();
            }

            header("Content-Type: application/pdf");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            $attachfile = "reportico.pdf";
            if ($this->reportfilename) {
                $attachfile = preg_replace("/ /", "_", $this->reportfilename . ".pdf");
            }

            // INLINE output is just returned to browser window it is invoked from
            // with hope that browser uses plugin
            if ($this->query->pdf_delivery_mode == "INLINE") {
                $len = strlen($buf);
                header("Content-Length: $len");
                echo $buf;
                die;
            } else if ($this->query->pdf_delivery_mode == "DOWNLOAD_SAME_WINDOW" && $this->query->reportico_ajax_called) {
                header('Content-Disposition: attachment;filename=' . $attachfile);
                header("Content-Type: application/pdf");
                $buf = base64_encode($buf);
                $len = strlen($buf);
                echo $buf;
                die;
            }
            // DOWNLOAD_NEW_WINDOW new browser window is opened to download file
            else {
                header('Content-Disposition: attachment;filename=' . $attachfile);
                $len = strlen($buf);
                echo $buf;
                die;
            }
            header("Content-Length: $len");

            print($buf);
            die;
        }
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
                        //ReporticoApp::handleError("Unknown Page Sizing Option ".$match[2]);
                        break;

                }
            }
        } else {
            $height = $height_string;
            //ReporticoApp::handleError("Unknown Page Sizing Option $height_string");
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
                        //ReporticoApp::handleError("Unknown age Sizing Option $width_string");
                        //break;

                }
            }
        } else {
            $width = $width_string;
            //ReporticoApp::handleError("Unknown Page Sizing Option $width_string");
        }

        return $width;
    }

    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false) // PDF

    {
        $sessionClass = ReporticoSession();

        if (!$sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

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

            $group_label = ReporticoLang::translate($group_label);

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

    }

    //Cell with horizontal scaling if text is too wide
    public function drawCellContainer($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $valign = "T")
    {
        // Add padding
        $padding = end($this->stylestack["padding"]);
        $toppad = $padding[0];
        $bottompad = $padding[2];

        // Add border and bg color
        $fill = end($this->stylestack["isfilling"]);
        $borderwidth = end($this->stylestack["border-width"]);
        $border = end($this->stylestack["border-style"]);
        if ($border != "none") {
            $border = 1;
        } else {
            $borderwidth = "false";
        }

        // Store current position so we can jump back after cell draw
        $x = $this->document->GetX();
        $y = $this->document->GetY();
        $this->document->MultiCell($w, $this->max_line_height, "", $borderwidth, false, $fill);
        $cell_height = $this->document->GetY() - $y;

        // Jump back
        $this->setPosition($x, $y);
    }

    //Cell with horizontal scaling if text is too wide
    public function drawCell($w, $h = 0, $txt = '', $implied_styles = "PBF", $ln = 0, $align = '', $valign = "T", $link = '')
    {
        // If a cell contains a line break like a "<BR>" then convert it to new line
        $txt = preg_replace("/<BR>/i", "\n", $txt);
        // Calculate cell height as string width divided by width
        $str_width = $this->document->GetStringWidth($txt);
        $numlines = ceil($this->document->GetStringWidth($txt) / ($w - 1));
        $numlines = $this->document->NbLines($w, $txt);
        $cellheight = ceil($numlines * $h);

        if ($this->draw_mode == "CALCULATE") {
            if ($cellheight > $this->calculated_line_height) {
                $this->calculated_line_height = $cellheight;
            }

        }

        // Add padding
        $toppad = 0;
        $bottompad = 0;
        if (strstr($implied_styles, "P")) {
            $padding = end($this->stylestack["padding"]);
            $toppad = $padding[0];
            $bottompad = $padding[2];
        }

        $fill = false;
        if (strstr($implied_styles, "F")) {
            // Add border and bg color
            $fill = end($this->stylestack["isfilling"]);
        }

        $borderwidth = false;
        if (strstr($implied_styles, "B")) {
            $borderwidth = end($this->stylestack["border-width"]);
            $border = end($this->stylestack["border-style"]);
            if ($border != "none") {
                $border = 1;
            } else {
                $borderwidth = "false";
            }

        }

        // Store current position so we can jump back after cell draw
        $y = $this->document->GetY();

        // To cater for multiline values, jump to bottom of line + padding -
        // cell height
        if ($valign == "T") {
            $jumpy = $toppad;
        } else if ($valign == "B") {
            $jumpy = $toppad + $this->calculated_line_height - $cellheight;
        } else if ($valign == "C") {
            $jumpy = (($toppad + $this->calculated_line_height + $bottompad) - $cellheight) / 2;
        }

        if ($this->draw_mode == "CALCULATE") {
            $fill_line_height = $toppad + $this->calculated_line_height + $bottompad;
            if ($this->max_line_height < $fill_line_height) {
                $this->max_line_height = $fill_line_height;
            }

            return;
        }

        if ($toppad) {
            $tmpborder = "";
            if (preg_match("/T/", $borderwidth)) {
                $tmpborder = preg_replace("/B/", "", $borderwidth);
                $borderwidth = preg_replace("/T/", "", $borderwidth);
            }

            $prevx = $this->document->GetX();
            $this->document->MultiCell($w, $toppad, "", $tmpborder, $align, $fill, $link);
            $this->setPosition($prevx, false);
        }

        if ($bottompad) {
            $tmpborder = "";
            if (preg_match("/B/", $borderwidth)) {
                $tmpborder = preg_replace("/T/", "", $borderwidth);
                $borderwidth = preg_replace("/B/", "", $borderwidth);
            }

            $prevx = $this->document->GetX();
            $this->document->MultiCell($w, $h + $bottompad, "", $tmpborder, $align, $fill, $link);
            $this->setPosition($prevx, false);
        }

        $this->setPosition(false, $y + $jumpy);

        // Link in a PDF must include a full URL contain http:// element
        // drilldown link of web url can be relative .. so prepend required elements
        if ($link) {
            if (!preg_match("/^http:\/\//", $link) && !preg_match("/^\//", $link)) {
                $link = "http://" . $_SERVER["HTTP_HOST"] . dirname($this->query->url_path_to_reportico_runner) . "/" . $link;
            }

            if (preg_match("/^\//", $link)) {
                $link = ReporticoApp::getConfig("http_urlhost"). "/" . $link;
            }

        }
        $this->document->MultiCell($w, $h, $txt, $borderwidth, $align, $fill, $link);
        $cell_height = $this->document->GetY() - $y;

        if ($cell_height > $this->current_line_height) {
            $this->current_line_height = $cell_height;
        }

        // Jump back
        $this->setPosition(false, $y);
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
            $this->document->Ln(0);
            $this->setPosition(false, $this->current_line_start_y + $this->current_line_height);
        } else {
            if ($h) {
                $this->document->Ln($h);
            } else {
                $this->document->Ln();
            }

        }
        $this->current_line_start_y = $this->document->GetY();
        $this->current_line_height = 0;
        $this->max_line_height = 0;
        $this->calculated_line_height = 0;
    }

    public function formatPageFooterStart() // PDF

    {
        $this->unapplyStyleTags($this->query->output_page_styles);
    }

    public function formatPageFooterEnd() // PDF

    {
        $this->unapplyStyleTags($this->query->output_reportbody_styles);
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

            $h = $this->document->Image(PDF_HEADER_IMAGE, $x, $y, $w * $this->pdfImageDPIScale);
        }

        //$this->setDefaultStyles();
        $this->applyStyleTags($this->query->output_reportbody_styles);
        return;
    }

    public function formatPageHeaderEnd() // PDF

    {
        $this->endLine();
        $this->applyStyleTags($this->query->output_page_styles);
        //$this->endLine();
    }

    public function beforeFormatCriteriaSelection()
    {
    }

    public function formatCriteriaSelection($label, $value)
    {
        $y = $this->document->GetY();

        $this->yjump = 0;
        // Fetch Group Header Label Start Column + display
        $group_xpos = false;
        if (!$group_xpos) {
            $group_xpos = $this->abs_left_margin;
        }

        $group_xpos = $this->absPagingWidth($group_xpos);

        $this->setPosition($group_xpos, $y);
        $padstring = $label;
        $this->drawCell(120, $this->vsize, "$padstring");

        // Fetch Group Header Label End Column + display
        $group_xpos = false;
        if (!$group_xpos) {
            $group_xpos = $this->absPagingWidth($group_xpos) + 140;
        }

        $group_xpos = $this->absPagingWidth($group_xpos);

        $this->setPosition($group_xpos, $y);
        $padstring = $value;
        $this->drawCell(400, $this->vsize, "$padstring");
        $this->endLine();
        $y = $this->document->GetY();

        if ($this->yjump) {
            $this->setPosition(false, $y + $this->yjump);
        }

        $label = "";
        $value = "";
    }

    public function afterFormatCriteriaSelection()
    {
    }

    public function formatGroupHeaderStart() // PDF

    {
        $sessionClass = ReporticoSession();

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        $this->endLine();

        // Throw new page if current position + number headers + line + headers > than bottom margin
        $ln = 0;
        $totheaderheight = 0;
        $prevheight = $this->calculated_line_height;
        foreach ($this->query->groups as $val) {
            for ($i = 0; $i < count($val->headers); $i++) {
                $col = &$val->headers[$i]["GroupHeaderColumn"];
                $custom = $val->headers[$i]["GroupHeaderCustom"];
                if ($val->headers[$i]["ShowInPDF"] == "yes") {
                    $this->formatGroupHeader($col, $custom, true);
                    $totheaderheight += $this->calculated_line_height;
                }
            }
        }
        $this->calculated_line_height = $totheaderheight;
        $y = $this->document->GetY();
        $this->checkPageOverflow();
        $this->calculated_line_height = $prevheight;
    }

    public function formatGroupHeaderEnd()
    {
        $this->endLine();
    }

    public function formatGroupTrailerStart($first = false) // PDF

    {

        // Tiny padding between group trailers and bofy detail so cell border doesnt overwrite heading underline
        if ($first) {
            $this->endLine(1);
        }

        $this->applyStyleTags($this->query->output_group_trailer_styles);
        return;
    }

    public function formatGroupTrailerEnd($last_trailer = false) // PDF

    {
        $this->unapplyStyleTags($this->query->output_group_trailer_styles);
        return;
    }

    public function formatGroupHeader(&$col, $custom, $calculate_only = false) // PDF format group headers

    {
        $sessionClass = ReporticoSession();

        for ($ctr = 0; $ctr < 2; $ctr++) {
            $this->draw_mode = "CALCULATE";
            if ($ctr == 1 && $calculate_only) {
                $this->draw_mode = "DRAW";
                break;
            }

            if ($ctr == 1) {
                $this->draw_mode = "DRAW";
                $this->checkPageOverflow();
            }

            $y = $this->document->GetY();

            if ($custom) {
                $this->yjump = 2;
                // Fetch Group Header Label Start Column + display
                $group_data_xpos = $col->getAttribute("group_header_data_xpos");
                if (!$group_data_xpos) {
                    $group_data_xpos = $this->abs_left_margin;
                }

                $group_data_xpos = $this->absPagingWidth($group_data_xpos);
                $group_data_width = $this->abs_right_margin - $group_data_xpos;

                $this->unapplyStyleTags($this->query->output_page_styles);

                // Display group header value
                $this->applyStyleTags($this->query->output_group_header_value_styles);
                $this->setPosition($group_data_xpos, $y);

                $tx = $custom;
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
                            if (trim($style[0]) == "width") {
                                $wd = trim($style[1]);
                            } else {
                                $styles[trim($style[0])] = trim($style[1]);
                            }

                        }
                    }
                }

                $tx = $this->reporticoStringToPhp($tx);
                $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
                $tx = preg_replace("/<\/*u>/", "", $tx);

                $this->applyStyleTags($styles);
                if ($this->draw_mode == "DRAW") {
                    $this->drawCellContainer($wd, $this->vsize + 4, $tx, "PBR", 0, $just);
                }
                $link = false;
                $this->drawCell($wd, $this->vsize + 4, $tx, "P", 0, $just, "T", $link);
                $this->unapplyStyleTags($styles);

                $this->unapplyStyleTags($this->query->output_group_header_value_styles);
                $this->endLine();
                $this->drawCell($group_data_width, $this->vsize, ""); // Blank cell to continue page breaking at this size
                $y = $this->document->GetY();

                if ($this->yjump) {
                    $this->setPosition(false, $y + $this->yjump);
                }

                $this->applyStyleTags($this->query->output_page_styles);
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
            $group_label = ReporticoLang::translate($group_label);

            $this->yjump = 2;
            // Fetch Group Header Label Start Column + display
            $group_xpos = $col->getAttribute("group_header_label_xpos");
            $group_data_xpos = $col->getAttribute("group_header_data_xpos");

            if (!$group_xpos) {
                $group_xpos = $this->abs_left_margin;
            }

            if (!$group_data_xpos) {
                $group_data_xpos = $group_xpos + 150;
            }

            $group_xpos = $this->absPagingWidth($group_xpos);
            $group_data_xpos = $this->absPagingWidth($group_data_xpos);
            $group_label_width = $group_data_xpos - 5;
            $group_data_width = $this->abs_right_margin - $group_data_xpos;

            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->unapplyStyleTags($this->query->output_page_styles);
            }

            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->applyStyleTags($this->query->output_group_header_label_styles);
            }

            $this->setPosition($group_xpos, $y);
            $padstring = $group_label;
            $this->drawCell($group_label_width, $this->vsize, "$padstring");
            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->unapplyStyleTags($this->query->output_group_header_label_styles);
            }

            $this->setPosition($group_data_xpos, $y);

            // Display group header value
            $contenttype = $col->deriveAttribute("content_type", $col->query_name);
            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->applyStyleTags($this->query->output_group_header_value_styles);
            }

            $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
            if ($contenttype == "graphic" || preg_match("/imagesql=/", $qn->column_value)) {
                if ($this->draw_mode == "CALCULATE") {
                    continue;
                }

                $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
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
                        //$h = $this->document->ImageHeight($tmpnam.".png", $group_xpos, $y, $width );
                        $h = $this->document->Image($tmpnam . ".png", $group_data_xpos, $y, $width * $this->pdfImageDPIScale) + 2;
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
            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->unapplyStyleTags($this->query->output_group_header_value_styles);
            }

            $this->endLine();
            $this->drawCell($group_data_width, $this->vsize, ""); // Blank cell to continue page breaking at this size
            $y = $this->document->GetY();

            if ($this->yjump) {
                $this->setPosition(false, $y + $this->yjump);
            }

            if ($sessionClass::sessionRequestItem("target_style", "TABLE") != "FORM") {
                $this->applyStyleTags($this->query->output_page_styles);
            }

        }
    }

    public function formatColumnHeader(&$column_item) //PDF column headers

    {
        $sessionClass = ReporticoSession();

        if (!$sessionClass::getReporticoSessionParam("target_show_detail")) {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $k = &$column_item->query_name;
        $padstring = $column_item->deriveAttribute("column_title", $column_item->query_name);
        $padstring = str_replace("_", " ", $padstring);
        $padstring = ucwords(strtolower($padstring));
        $padstring = ReporticoLang::translate($padstring);

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
            $this->drawCell($wd, $this->vsize + 4, $padstring, "PBF", 0, $just, "B");
        }
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
        $this->endLine();

        $tmpnam = tempnam(ReporticoApp::getConfig("tmp_dir"), "gph");
        if ( ReporticoApp::getConfig("graph_engine") == "PCHART") {
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

            $this->document->Image($tmpnam . ".png", $this->abs_left_margin + $xaddon, $y, $width * $this->pdfImageDPIScale, $height * $this->pdfImageDPIScale);
            $y = $this->setPosition(false, $y + $height);
            $this->endLine();
        }
        unlink($tmpnam . ".png");
    }

    public function formatHeaders() // PDF

    {
        $sessionClass = ReporticoSession();

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        // Handle multi line headers by processing all headers
        // in "CALCULATE" mode and then print them on the appropriate line
        $this->draw_mode = "CALCULATE";
        foreach ($this->columns as $w) {
            $this->applyStyleTags($this->query->output_header_styles);
            $this->formatColumnHeader($w);
            $this->unapplyStyleTags($this->query->output_header_styles);
        }
        $this->draw_mode = "DRAW";
        $this->checkPageOverflow();
        foreach ($this->columns as $w) {
            $this->applyStyleTags($this->query->output_header_styles);
            $this->formatColumnHeader($w);
            $this->unapplyStyleTags($this->query->output_header_styles);
        }
        $this->endLine();
        $this->unapplyStyleTags($this->query->output_page_styles);
        $this->drawCell(5, $this->vsize, ""); // Blank cell to continue page breaking at this size
        $this->applyStyleTags($this->query->output_page_styles);

        // Tiny padding between column headers and rows so cell border doesnt overwrite heading underline
        $this->current_line_height = 0;
        $this->max_line_height = 0;
        $this->endLine(1);
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
                    $h = $this->document->Image($tmpnam . ".png", $x, $y, $width * $this->pdfImageDPIScale) + 2;
                    if ($h > $this->yjump) {
                        $this->yjump = $h;
                    }

                    if ($h > $this->max_line_height) {
                        $this->max_line_height = $h;
                    }

                    unlink($tmpnam . ".png");
                }
            }
        } else {
            if (!$wd) {
                $this->document->Write("$padstring");
            } else {
                $this->setPosition($tw, false);

                $this->allcell_styles = array("border-width" => "");
                $this->cell_styles = array("border-width" => "");
                $this->applyStyleTags($this->query->output_allcell_styles, false, false, "ALLCELLS");
                $this->applyStyleTags($column_item->output_cell_styles, false, false, "CELLS");
                $this->applyRowBorderToCell();
                if ($this->draw_mode == "DRAW") {

                    $this->drawCellContainer($wd, $this->vsize + 10, $k, "PBR", 0, $just);
                }
                $link = false;
                if ($column_item->output_hyperlinks) {
                    $link = $column_item->output_hyperlinks["url"];
                }

                $this->drawCell($wd, $this->vsize + 4, $k, "P", 0, $just, "T", $link);
                $this->unapplyStyleTags($column_item->output_cell_styles);
                $this->unapplyStyleTags($this->query->output_allcell_styles);
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
        if (isset($this->row_styles["border-width"]) && $this->row_styles["border-width"]) {
            $cellstyle = $this->cell_styles["border-width"];
            if (!$cellstyle) {
                $cellstyle = $this->allcell_styles["border-width"];
            }
            $rowstyle = $this->row_styles["border-width"];
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
            end($this->stylestack["border-width"]);
            $this->stylestack["border-width"][key($this->stylestack["border-width"])] = $cellstyle;
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

    public function applyStyleTags($styleset, $parent_styleset = false, $grandparent_styleset = false, $apply_type = false)
    {
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
                    if (isset($this->stylestack[$k])) {
                        if ($k == "padding") {
                            $tmp = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
                            $ar = explode(",", preg_replace("/[^0-9]+/", ",", $v));
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
                            $v = preg_replace("/px/", "", $v);
                            $ar = explode(",", preg_replace("/[^0-9]+/", ",", $v));
                            if ($ar) {
                                if (count($ar) == 1 && $ar[0] > 0) {
                                    $tmp = "LBTR";
                                } else if (count($ar) == 2) {
                                    if ($ar[0] > 0) {
                                        $tmp .= "TB";
                                    }

                                    if ($ar[1] > 0) {
                                        $tmp .= "LR";
                                    }

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
                            $v = $tmp;

                            if ($apply_type == "ROW") {
                                $this->row_styles["border-width"] = $v;
                            }

                            if ($apply_type == "ALLCELLS") {
                                $this->allcell_styles["border-width"] = $v;
                            }

                            if ($apply_type == "CELLS") {
                                $this->cell_styles["border-width"] = $v;
                            }

                        }
                        if ($k == "font-size") {
                            $sz = preg_replace("/[^0-9].*/", "", $v);
                            $this->document->SetFontSize($sz);
                            $v = $sz + $this->vspace;
                            $this->vsize = $v;
                        }
                        if ($k == "border-color" || $k == "color" || $k == "background-color") {
                            $v = ReporticoUtility::htmltorgb($v);
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
    }

    public function unapplyStyleTags($styleset, $parent_styleset = false, $grandparent_styleset = false, $type = "")
    {
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
                    }
                }
            }
        }
    }

    public function eachLine($val) // PDF

    {
        $sessionClass = ReporticoSession();


        Report::eachLine($val);

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
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

            $this->applyStyleTags($this->query->output_before_form_row_styles);
            $y = $this->document->GetY();
            $this->setPosition($this->abs_left_margin, $y);
            $this->drawCell(400, $this->vsize, ""); // Blank cell to continue page breaking at this size
            $this->unapplyStyleTags($this->query->output_before_form_row_styles);

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
                $this->applyStyleTags($this->query->output_after_form_row_styles);
                $y = $this->document->GetY();
                $this->setPosition($this->abs_left_margin, $y);
                $this->drawCell($this->abs_right_margin - $this->abs_left_margin, $this->vsize, ""); // Blank cell to continue page breaking at this size
                $this->unapplyStyleTags($this->query->output_after_form_row_styles);
                $this->endLine();

            }

            return;
        }

        $y = $this->document->GetY();
        $this->checkGraphicFit();

        $this->yjump = 0;
        if ($this->body_display == "show" && $sessionClass::getReporticoSessionParam("target_show_detail")) {
            $this->row_styles = array();
            $this->applyStyleTags($this->query->output_row_styles, false, false, "ROW");

            $this->draw_mode = "CALCULATE";
            $this->no_columns_printed = 0;
            $this->no_columns_to_print = 0;
            foreach ($this->columns as $col) {
                $this->formatColumn($col);
            }

            $this->unapplyStyleTags($this->query->output_row_styles);

            $this->draw_mode = "DRAW";

            $this->checkPageOverflow();

            //$this->setPosition($this->abs_left_margin, false);
            //$this->drawCell($this->abs_right_margin - $this->abs_left_margin, $this->calculated_line_height, "xx", 0, 0);
            //$this->disableStyleTag($this->query->output_header_styles, "border-width");
            $this->applyStyleTags($this->query->output_row_styles, false, false, "ROW");

            $this->no_columns_printed = 0;
            foreach ($this->columns as $col) {
                $this->formatColumn($col);
            }

            $this->page_line_count++;
            $this->unapplyStyleTags($this->query->output_row_styles);
            $nextliney = $this->document->GetY() + $this->max_line_height;
            $this->endLine();
            $this->setPosition(false, $nextliney);
        }

        //if ( $this->yjump )
        //$this->setPosition(false, $y + $this->yjump);

        //if ( $y + $this->vsize > $this->abs_bottom_margin )
        //{
        //$this->finishPage();
        //$this->beginPage();
        //}

    }

    public function checkPageOverflow()
    {
        $y = $this->document->GetY();
        //if ( $y + $this->calculated_line_height > $this->abs_bottom_margin )
        if ($y + $this->max_line_height > $this->abs_bottom_margin) {

            // Between page breaks store any current lin eparameters
            $prev_calculated_line_height = $this->calculated_line_height;
            $prev_max_line_height = $this->max_line_height;
            $this->finishPage();
            $this->beginPage();
            $this->beforeGroupHeaders();
            $this->page_line_count++;
            $this->calculated_line_height = $prev_calculated_line_height;
            $this->max_line_height = $prev_max_line_height;
        }
    }

    public function checkGraphicFit()
    {
        $will_fit = true;
        $max_height = $this->vsize;
        foreach ($this->columns as $col) {
            $contenttype = $col->deriveAttribute("content_type", $col->query_name);
            $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
            if ($contenttype == "graphic" || preg_match("/imagesql=/", $qn->column_value)) {
                $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
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
                        $h = $this->document->ImageHeight($tmpnam . ".png", 0, 0, $width);
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

            $this->beforeGroupHeaders();
            $this->page_line_count++;
        }

    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }

    public function setPosition($x, $y)
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

        $this->debug("PDF Begin Page\n");

        $this->page_number++;
        $this->document->AddPage($this->orientations[$this->orientation]);

        $font = $this->document->SetFont($this->fontName);
        $font = $this->document->SetFontSize($this->vsize);
        $this->setPosition($this->abs_left_margin, $this->abs_top_margin);
        $this->current_line_start_y = $this->document->GetY();

        Report::pageHeaders();
        $this->endLine();
        $this->endLine();
    }

    public function finishPage()
    {
        $this->debug("Finish Page");
        $this->pageFooters();
        //$this->document->pdf_end_page($this->document);
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

        // Decode page header
        $tx = $header->text;
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
                    if (trim($style[0]) == "width") {
                        $wd = trim($style[1]);
                    } else {
                        $styles[trim($style[0])] = trim($style[1]);
                    }

                }
            }
        }

        $tx = $this->reporticoStringToPhp($tx);
        $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        $this->applyStyleTags($styles);
        $this->drawCell($wd, $this->vsize, $tx, "PBF", 0, $just);
        $this->unapplyStyleTags($styles);
        $this->endLine();

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

        // Decode page header
        $tx = $footer->text;
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
                    if (trim($style[0]) == "width") {
                        $wd = trim($style[1]);
                    } else {
                        $styles[trim($style[0])] = trim($style[1]);
                    }

                }
            }
        }

        $tx = $this->reporticoStringToPhp($tx);
        $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        $this->applyStyleTags($styles);
        $this->drawCell($wd, $this->vsize, $tx, "PBF", 0, $just);
        $this->unapplyStyleTags($styles);
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
                //$this->document->Line($this->abs_left_margin, $y, $this->abs_page_width - $this->abs_right_margin, $y);
                //$this->setPosition($this->abs_right_margin, $y);
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

}
