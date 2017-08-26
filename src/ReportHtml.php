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

 * File:        ReportHtml.php
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

class ReportHtml extends Report
{
    public $abs_top_margin;
    public $abs_bottom_margin;
    public $abs_left_margin;
    public $abs_right_margin;
    public $header_count = 0;
    public $footer_count = 0;
    public $graph_sessionPlaceholder = 0;
    public $tbody_started = false;
    public $tfoot_started = false;

    public function __construct()
    {
        return;
    }

    public function start()
    {
        Report::start();

        $this->debug("HTML Start **");

        //pdf_set_info($this->document,'Creator', 'God');
        //pdf_set_info($this->document,'Author', 'Peter');
        //pdf_set_info($this->document,'Title', 'The Title');

        $this->page_line_count = 0;
        $this->abs_top_margin = $this->absPagingHeight($this->getAttribute("TopMargin"));
        $this->abs_bottom_margin = $this->absPagingHeight($this->getAttribute("BottomMargin"));
        $this->abs_right_margin = $this->absPagingHeight($this->getAttribute("RightMargin"));
        $this->abs_left_margin = $this->absPagingHeight($this->getAttribute("LeftMargin"));
    }

    public function finish()
    {
        Report::finish();
        $this->debug("HTML End **");

        if (preg_match("/\?/", $this->query->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        if ($this->line_count < 1) {
            $title = $this->query->deriveAttribute("ReportTitle", "Unknown");
            $this->text .= '<H1 class="swRepTitle">' . ReporticoLang::translate($title) . '</H1>';
            $forward = ReporticoSession::sessionRequestItem('forward_url_get_parameters', '');
            if ($forward) {
                $forward .= "&";
            }

            $this->text .= '<div class="swRepButtons">';
            // In printable html mode dont show back box
            if (!ReporticoUtility::getRequestItem("printable_html")) {
                // Show Go Back Button ( if user is not in "SINGLE REPORT RUN " )
                if (!$this->query->access_mode || ($this->query->access_mode != "REPORTOUTPUT")) {
                    $this->text .= '<div class="swRepBackBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'execute_mode=PREPARE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_BACK") . '">&nbsp;</a></div>';
                }
                if (ReporticoSession::getReporticoSessionParam("show_refresh_button")) {
                    $this->text .= '<div class="swRepRefreshBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'refreshReport=1&execute_mode=EXECUTE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_REFRESH") . '">&nbsp;</a></div>';
                }

                $this->text .= '<div class="reporticoJSONExecute"><a class="swJSONExecute1 testy" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'refreshReport=1&target_format=JSON&execute_mode=EXECUTE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_REFRESH") . '">&nbsp;</a></div>';
            } else {
                $this->text .= '<div class="swRepPrintBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'printReport=1&execute_mode=EXECUTE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_PRINT") . '">' . ReporticoLang::templateXlate("GO_PRINT") . '</a></div>';
            }
            $this->text .= '</div>';

            $this->text .= '<div class="swRepNoRows">' . ReporticoLang::templateXlate("NO_DATA_FOUND") . '</div>';
        }

        if ($this->report_file) {
            $this->debug("Saved to $this->report_file");
        } else {
            $this->debug("No html file specified !!!");
            $buf = "";
            $len = strlen($buf) + 1;

            print($buf);
        }

        if ($this->page_started) {
            if ($this->tbody_started) {
                $this->text .= '</TBODY>';
                $this->tbody_started = false;
            }
            $this->text .= "</TABLE>";
        }

        //$this->text .= "</footer>";
        $this->text .= "</div>";
        $this->footer_count++;
        $this->text .= "<footer class=\"swPageFooterBlock swPageFooterBlock{$this->footer_count}\">";
        $this->text .= "Page Footer";
        $this->text .= "</footer>";
        $this->page_started = false;
    }

    public function absPagingHeight($height_string)
    {
        $height = (int) $height_string;
        if (strstr($height_string, "%")) {
            $height = (int)
            ($this->page_height * $height_string) / 100;
        }

        return $height;
    }

    public function absPagingWidth($width_string)
    {
        $width = (int) $width_string;
        if (strstr($width_string, "%")) {
            $width = (int)
            ($this->page_width * $width_string) / 100;
        }

        return $width;
    }

    public function formatColumnHeader(&$column_item) //HTML

    {

        if ($this->body_display != "show") {
            return;
        }

        if (!ReporticoSession::getReporticoSessionParam("target_show_detail")) {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        // Create sensible column header label from column name
        $padstring = ReporticoUtility::columnNameToLabel($column_item->query_name);
        $padstring = $column_item->deriveAttribute("column_title", $padstring);
        $padstring = ReporticoLang::translate($padstring);

        $colstyles = array();
        $cw = $column_item->deriveAttribute("ColumnWidthHTML", false);
        $just = $column_item->deriveAttribute("justify", false);
        if ($cw) {
            $colstyles["width"] = $cw;
        }

        if ($just) {
            $colstyles["text-align"] = $just;
        }

        $this->text .= '<TH ' . $this->getStyleTags($colstyles, $this->query->output_header_styles) . '>';
        $this->text .= $padstring;
        $this->text .= "</TH>";
    }

    public function formatColumn(&$column_item)
    {
        if ($this->body_display != "show") {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $padstring = &$column_item->column_value;

        $colstyles = array();
        $cw = $column_item->deriveAttribute("ColumnWidthHTML", false);
        $just = $column_item->deriveAttribute("justify", false);
        if ($cw) {
            $colstyles["width"] = $cw;
        }

        if ($just) {
            $colstyles["text-align"] = $just;
        }

        $this->text .= '<TD ' . $this->getStyleTags($colstyles, $column_item->output_cell_styles, $this->query->output_allcell_styles) . '>';
        if ($column_item->output_images) {
            $padstring = $this->formatImages($column_item->output_images);
        }

        if ($column_item->output_hyperlinks) {
            $this->text .= $this->formatHyperlinks($column_item->output_hyperlinks, $padstring);
        } else {
            $this->text .= $padstring;
        }

        $this->text .= "</TD>";
    }

    public function formatImages($image)
    {
        $txt = '<img src="' . $image["image"] . '" alt="" ';
        if (isset($image["height"]) && $image["height"]) {
            $txt .= ' height="' . $image["height"] . '"';
        }

        if (isset($image["width"]) && $image["width"]) {
            $txt .= ' width="' . $image["width"] . '"';
        }

        $txt .= " /> ";
        return $txt;
    }

    public function formatHyperlinks($hyperlinks, $padstring)
    {
        $open = "";
        if ($hyperlinks["open_in_new"]) {
            $open = " target=\"_blank\"";
        }

        // Work out the drilldown url ...
        $url = $hyperlinks["url"];

        // Add any application specific url params
        if ($hyperlinks["is_drilldown"]) {
            if (ReporticoSession::sessionRequestItem('forward_url_get_parameters', '')) {
                $url .= "&" . ReporticoSession::sessionRequestItem('forward_url_get_parameters', '');
            }

            // Add drilldown namespace normally specified in frameworks
            $url .= '&clear_session=1&reportico_session_name=NS_drilldown' . ReporticoSession::reporticoNamespace();
        }

        if ($hyperlinks["label"] == "<SELF>") {
            $txt = '<a href="' . $url . '"' . $open . '>' . $padstring . '</a>';
        } else {
            $txt = '<a href="' . $url . '"' . $open . '>' . $hyperlinks["label"] . '</a>';
        }

        return $txt;
    }

    public function extractStylesAndTextFromString(&$text, &$styles, &$attributes, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";
        $style_arr = $this->fetchCellStyles($text);

        $widthset = false;

        if ($grandparent_styleset && is_array($grandparent_styleset)) {
            foreach ($grandparent_styleset as $k => $v) {
                if ($k == "width") {
                    $widthset = true;
                }

                $styles .= "$k:$v;";
            }
        }

        if ($parent_styleset && is_array($parent_styleset)) {
            foreach ($parent_styleset as $k => $v) {
                if ($k == "width") {
                    $widthset = true;
                }

                $styles .= "$k:$v;";
            }
        }

        if (isset($attributes["justify"])) {
            if ($attributes["justify"] == "center") {
                $styles .= "text-align: center;";
            }

            if ($attributes["justify"] == "right") {
                $styles .= "text-align: right;";
            }

        }

        if (isset($attributes["ColumnWidthPDF"]) && $attributes["ColumnWidthPDF"]) {
            if (is_numeric($attributes["ColumnWidthPDF"])) {
                $styles .= "width: " . $attributes["ColumnWidthPDF"] . "px;";
            } else {
                $styles .= "width: " . $attributes["ColumnWidthPDF"] . ";";
            }

        }

        if (isset($attributes["ColumnStartPDF"]) && $attributes["ColumnStartPDF"]) {
            if (is_numeric($attributes["ColumnStartPDF"])) {
                $styles .= "margin-left: " . $attributes["ColumnStartPDF"] . "px;";
            } else {
                $styles .= "margin-left: " . $attributes["ColumnStartPDF"] . "24;";
            }

        }

        if ($style_arr) {
            foreach ($style_arr as $k => $v) {
                if ($k == "width") {
                    $widthset = true;
                }

                if ($k == "background-image") {
                    $styles .= "background: url('$v') no-repeat;";
                } else {
                    $styles .= "$k:$v;";
                }

            }
        }

        // If no width specified default to 100%
        //if ( !$widthset )
        //$styles .= "width:100%;";

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
                    if (count($style) >= 2) {
                        $name = trim($style[0]);
                        $value = trim($style[1]);
//echo "$name = $value, ";
                        if (is_numeric($value)) {
                            if ($name == "width") {
                                $value .= "px";
                            }

                        }
                        $styles[$name] = $value;
                    }
                }
            }
        }
//echo "<BR>";

        $tx = $this->reporticoStringToPhp($tx);
        $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
    }

    public function getStyleTags($styleset, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";

        if ($grandparent_styleset && is_array($grandparent_styleset)) {
            foreach ($grandparent_styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v !important;";
            }
        }

        if ($parent_styleset && is_array($parent_styleset)) {
            foreach ($parent_styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v !important;";
            }
        }

        if ($styleset && is_array($styleset)) {
            foreach ($styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v !important;";
            }
        }

        if ($outtext) {
            $outtext .= "\"";
        }

        return $outtext;
    }

    public function formatFormat($in_value, $format)
    {
        switch ($in_value) {
            case "blankline":
                //$this->text .= "<TR><TD><br></TD></TR>";
                break;

            case "solidline":
                $this->text .= '<TR><TD colspan="10"><hr style="width:100%;" size="2"/></TD>';
                break;

            case "newpage":
                //$this->text .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'swRepPage" '.$this->getStyleTags($this->query->output_page_styles).'>';
                $this->page_started = true;
                break;

            default:
                $this->text .= "<TR><TD>Unknown Format $in_value</TD></TR>";
                break;

        }
    }

    public function formatHeaders()
    {
        if (!$this->page_started) {
            $this->text .= '<TABLE class="' . $this->query->getBootstrapStyle("page") . 'swRepPage" ' . $this->getStyleTags($this->query->output_page_styles) . '>';
            $this->page_started = true;
        }

        if (ReporticoSession::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        if ($this->body_display != "show") {
            return;
        }

        $this->text .= "<thead><tr class='swRepColHdrRow'>";
        foreach ($this->query->display_order_set["column"] as $w) {
            $this->formatColumnHeader($w);
        }

        $this->text .= "</tr></thead>";

        if ($this->body_display == "show" && ReporticoSession::getReporticoSessionParam("target_show_detail")) {
            $this->text .= "<tbody>";
            $this->tbody_started = true;
        }
    }

    public function formatGroupHeaderStart($throw_page = false)
    {
        // Ensure group box spans to end of table
        $spanct = 0;
        foreach ($this->columns as $col) {
            if ($this->showColumnHeader($col)) {
                $spanct++;
            }
        }

        //$this->text .= "<TR class=swRepDatRow>";
        //$this->text .= "<TD class=swRepDatVal colspan=\"".$spanct."\">";
        if ($throw_page || $this->page_started) {
            $title = $this->query->deriveAttribute("ReportTitle", "Unknown");
            $this->pageHeaders();
            if ($this->query->output_template_parameters["show_hide_report_output_title"] != "hide") {
                $this->text .= '<H1 class="swRepTitle">' . ReporticoLang::translate($title) . '</H1>';
            }

            $this->text .= '<TABLE class="swRepGrpHdrBox swNewPage" >';
        } else {
            $this->text .= '<TABLE class="swRepGrpHdrBox" >';
        }

    }

    public function formatGroupHeader(&$col, $custom) // HTML

    {
        if (!$col) {
            return;
        }

        $this->text .= '<TR class="swRepGrpHdrRow">';
        $this->text .= '<TD class="swRepGrpHdrLbl" ' . $this->getStyleTags($this->query->output_group_header_label_styles) . '>';
        $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);

        $padstring = $qn->column_value;

        // Create sensible group header label from column name
        $tempstring = ReporticoUtility::columnNameToLabel($col->query_name);
        $tempstring = $col->deriveAttribute("column_title", $tempstring);
        $tempstring = ReporticoLang::translate($col->deriveAttribute("column_title", $tempstring));

        $this->text .= ReporticoLang::translate($col->deriveAttribute("group_header_label", $tempstring));
        $this->text .= "</TD>";
        $this->text .= '<TD class="swRepGrpHdrDat" ' . $this->getStyleTags($this->query->output_group_header_value_styles) . '>';
        $this->text .= "$padstring";
        $this->text .= "</TD>";
        $this->text .= "</TR>";
    }

    public function formatGroupHeaderEnd()
    {
        $this->text .= "</TABLE>";
        $this->page_started = false;
    }

    public function beginLine()
    {
        if ($this->body_display != "show") {
            return;
        }

        $this->text .= '<TR class="swRepResultLine" ' . $this->getStyleTags($this->query->output_row_styles) . '>';
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
        if ($graph_ct == 0) {
            if ($this->page_started) {
                if ($this->tbody_started) {
                    $this->text .= '</TBODY>';
                    $this->tbody_started = false;
                }
                $this->text .= '</TABLE>';
            }
            $this->page_started = false;
        }
        $this->graph_sessionPlaceholder++;
        $graph->width_actual = ReporticoApp::getDefaultConfig("GraphWidth", $graph->width);
        $graph->height_actual = ReporticoApp::getDefaultConfig("GraphHeight", $graph->height);
        $graph->title_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->title, true);
        $graph->xtitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->xtitle, true);
        $graph->ytitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->ytitle, true);
        $url_string = $graph->generateUrlParams("HTML", $this->graph_sessionPlaceholder);
        $this->text .= '<div class="swRepResultGraph">';
        if ($url_string) {
            $this->text .= $url_string;
        }

        $this->text .= '</div>';
    }
    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false) // HTML

    {
        if (!ReporticoSession::getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        $just = $trailer_col->deriveAttribute("justify", false);
        if ($just && $just != "left") {
            $this->query->output_group_trailer_styles["text-align"] = $just;
        } else {
            $this->query->output_group_trailer_styles["text-align"] = "left";
        }

        if ($value_col) {
            if ($trailer_first) {
                $this->text .= '<TD class="swRepGrpTlrDat1st" ' . $this->getStyleTags($this->query->output_group_trailer_styles) . '>';
            } else {
                $this->text .= '<TD class="swRepGrpTlrDat" ' . $this->getStyleTags($this->query->output_group_trailer_styles) . '>';
            }
        } else
        if ($trailer_first) {
            $this->text .= '<TD class="swRepGrpTlrDat1st">';
        } else {
            $this->text .= '<TD class="swRepGrpTlrDat">';
        }

        if ($value_col) {
            $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("group_trailer_label");
            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $value_col["GroupTrailerValueColumn"]->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }
            $group_label = ReporticoLang::translate($group_label);
            $padstring = $value_col["GroupTrailerValueColumn"]->old_column_value;
            if ($value_col["GroupTrailerValueColumn"]->output_images) {
                $padstring = $this->formatImages($value_col["GroupTrailerValueColumn"]->output_images);
            }

            if ($group_label == "BLANK") {
                $this->text .= $padstring;
            } else {
                $this->text .= $group_label . " " . $padstring;
            }

        } else {
            $this->text .= "&nbsp;";
        }
        $this->text .= "</TD>";
    }

    public function formatGroupTrailerStart($first = false)
    {
        if ($first) {
            if ($this->tbody_started) {
                $this->text .= '</TBODY>';
                $this->tbody_started = false;
            }
            $this->text .= "<TFOOT>";
            $this->tfoot_started = true;
            $this->text .= '<TR class="swRepGrpTlrRow1st">';
        } else {
            $this->text .= '<TR class="swRepGrpTlrRow">';
        }

    }

    public function formatGroupTrailerEnd($last_trailer = false)
    {

        //$this->text .= "</TR>";
        if ($this->page_started) {
            if ($this->tfoot_started) {
                $this->text .= "</TFOOT>";
                $this->tfoot_started = false;
            }
            $this->text .= "</TABLE>";
        }
        $this->page_started = false;
    }

    public function endLine()
    {
        if ($this->body_display != "show") {
            return;
        }

        $this->text .= "</TR>";
    }

    public function eachLine($val) // HTML

    {

        Report::eachLine($val);

        if (ReporticoSession::sessionRequestItem("target_style", "TABLE") == "FORM") {
            if (!$this->page_started) {
                $formpagethrow = $this->query->getAttribute("formBetweenRows");
                switch ($formpagethrow) {
                    case "newpage":
                        if ($this->page_line_count > 0) {
                            $formpagethrow = "swRepPageFormLine swNewPage";
                        } else {
                            $formpagethrow = "swRepPageFormLine";
                        }

                        break;
                    case "blankline":
                        $formpagethrow = "swRepPageFormBlank";
                        break;
                    case "solidline":
                        $formpagethrow = "swRepPageFormLine";
                        break;
                }

                $this->text .= '<TABLE class="' . $this->query->getBootstrapStyle("page") . 'swRepPage ' . $formpagethrow . '" ' . $this->getStyleTags($this->query->output_page_styles) . '>';
                $this->page_started = true;
            }
            foreach ($this->query->groups as $val) {
                for ($i = 0; $i < count($val->headers); $i++) {
                    $col = &$val->headers[$i];
                    $col = &$val->headers[$i]["GroupHeaderColumn"];
                    $custom = $val->headers[$i]["GroupHeaderCustom"];
                    if ($val->headers[$i]["ShowInHTML"] == "yes") {
                        $this->formatGroupHeader($col, $custom, true);
                    }
                }
            }

            foreach ($this->query->display_order_set["column"] as $k => $w) {
                if ($w->attributes["column_display"] != "show") {
                    continue;
                }

                $this->formatGroupHeader($w, false, false);
            }
            $this->page_line_count++;
            $this->line_count++;
            $this->text .= '</TABLE>';
            $this->page_started = false;
            return;
        }

        if ($this->page_line_count == 1) {
            //$this->text .="<tr class='swPrpCritLine'>";
            //foreach ( $this->columns as $col )
            //$this->formatColumnHeader($col);
            //$this->text .="</tr>";
        }

        //foreach ( $this->columns as $col )
        if ($this->body_display == "show" && ReporticoSession::getReporticoSessionParam("target_show_detail")) {
            $this->beginLine();
            if (!$this->page_started) {
                $this->text .= '<TABLE class="' . $this->query->getBootstrapStyle("page") . 'swRepPage" ' . $this->getStyleTags($this->query->output_page_styles) . '>';
                $this->page_started = true;
            }
            foreach ($this->query->display_order_set["column"] as $col) {
                $this->formatColumn($col);
            }

            $this->endLine();
        }

        //if ( $y < $this->abs_bottom_margin )
        //{
        //$this->finishPage();
        //$this->beginPage();
        //}

    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }

    public function beginPage()
    {
        Report::beginPage();

        $this->throw_page = true;
        //$this->page_started = true;
        $this->debug("HTML Begin Page\n");

        $forward = ReporticoSession::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $forward .= "&";
        }

        if (preg_match("/\?/", $this->query->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        if (!ReporticoUtility::getRequestItem("printable_html")) {
            if (!$this->query->access_mode || ($this->query->access_mode != "REPORTOUTPUT")) {
                $this->text .= '<div class="swRepButtons">';
                $this->text .= '<div class="swRepBackBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'execute_mode=PREPARE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_BACK") . '">&nbsp;</a></div>';
            }
            if (ReporticoSession::getReporticoSessionParam("show_refresh_button")) {
                $this->text .= '<div class="swRepRefreshBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'refreshReport=1&execute_mode=EXECUTE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_REFRESH") . '">&nbsp;</a></div>';
            }

            $this->text .= '</div>';

        } else {
            //$this->text .= '<div class="prepareAjaxExecuteIgnore swPDFBox1"><a class="swLinkMenu5 swPDFBox" target="_blank" href="'.$this->query->getActionUrl().'?'.$forward.'refreshReport=1&target_format=PDF&execute_mode=EXECUTE&reportico_session_name='.ReporticoSession::reporticoSessionName().'" title="Print PDF">&nbsp;</a></div>';
            $this->text .= '<div class="swRepButtons">';
            $this->text .= '<div class="swRepPrintBox"><a class="swLinkMenu" href="' . $this->query->getActionUrl() . $url_join_char . $forward . 'printReport=1&execute_mode=EXECUTE&reportico_session_name=' . ReporticoSession::reporticoSessionName() . '" title="' . ReporticoLang::templateXlate("GO_PRINT") . '">' . '&nbsp;' . '</a></div>';
            $this->text .= '</div>';
        }

        // Page Headers
        Report::pageHeaders();

        $title = $this->query->deriveAttribute("ReportTitle", "Unknown");
        if ($this->query->output_template_parameters["show_hide_report_output_title"] != "hide") {
            $this->text .= '<H1 class="swRepTitle">' . ReporticoLang::translate($title) . '</H1>';
        }

    }

    public function beforeFormatCriteriaSelection()
    {
        $this->text .= '<TH>';
        $this->text .= '<TABLE class="swRepCriteria"' . $this->getStyleTags($this->query->output_criteria_styles) . '>';
    }

    public function formatCriteriaSelection($label, $value)
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

    public function afterFormatCriteriaSelection()
    {
        $this->text .= "</TABLE>";
    }

    public function finishPage()
    {
        $this->debug("HTML Finish Page");
        //pdf_end_page($this->document);
    }

    public function publish()
    {
        Report::publish();
        $this->debug("Publish HTML");
    }

    public function formatPageHeaderStart()
    {
        if ($this->line_count > 0) {
            $this->text .= "</div>";
            $this->footer_count++;
            $this->text .= "<footer class=\"swPageFooterBlock swLastPageFooterBlock swPageFooterBlock{$this->footer_count}\">";
            $this->text .= "Page Footer";
            $this->text .= "</footer>";
            $this->text .= "<div class=\"swPageBlock\" >";
            $this->header_count++;
            $this->text .= "<div class=\"swPageHeaderBlock swNewPageHeaderBlock swPageHeaderBlock{$this->header_count}\" >";
        } else {
            $this->text .= "<div class=\"swPageBlock\" >";
            $this->header_count++;
            $this->text .= "<div class=\"swPageHeaderBlock swPageHeaderBlock{$this->header_count}\" >";
        }
    }

    public function formatPageHeaderEnd()
    {
        $this->text .= "</div>";
    }

    public function formatPageHeader(&$header)
    {
        $styles = "";
        $text = $header->text;

        $this->extractStylesAndTextFromString($text, $styles, $header->attributes, $parent_styleset = false, $grandparent_styleset = false);
        $just = strtolower($header->getAttribute("justify"));

//echo "Value $text<BR>";
        //var_dump($header->attributes);
        //echo "Styles = $styles <BR>";
        $img = "";
        if ($styles) {
            $matches = array();
            if (preg_match("/background: url\('(.*)'\).*;/", $styles, $matches)) {
                $styles = preg_replace("/background: url\('(.*)'\).*;/", "", $styles);
                if (count($matches) > 1) {
                    $img = "<img src='" . $matches[1] . "'/>";
                }
            }
            $this->text .= "<DIV class=\"swPageHeader\" style=\"$styles\">";
        } else {
            $this->text .= "<DIV class=\"swPageHeader\" >";
        }

        $this->text .= "$img$text";
        $this->text .= "</DIV>";
        //$this->text .= "<TR>";
        //$this->text .= '<TD colspan="10" justify="'.$just.'">';
        //$this->text .=($header->text);
        //$this->text .= "</TD>";
        //$this->text .= "</TR>";

        return;
    }

    public function formatPageFooter(&$header)
    {
        $just = strtolower($header->getAttribute("justify"));

        $this->text .= "<TR>";
        $this->text .= '<TD colspan="10" justify="' . $just . '">';
        $this->text .= ($header->text);
        $this->text .= "</TD>";
        $this->text .= "</TR>";

        return;
    }

}
