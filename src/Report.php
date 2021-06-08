<?php
/*

 * File:        Report.php
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

class Report extends ReporticoObject
{
    public $query_set = array();
    public $document;
    public $report_file = "";
    public $page_width;
    public $page_height;
    public $page_length = 65;
    public $page_count = 0;
    public $page_line_count = 0;
    public $line_count = 0;
    public $page_number;
    public $columns;
    public $last_line = false;
    public $query;
    public $reporttitle;
    public $reportfilename;
    public $body_display = "show";
    public $graph_display = "show";
    public $page_started = false;
    public $text = "";
    public $inOverflow = false;
    public $any_trailers = false;
    public $any_custom_trailers = false;
    public $draw_mode = "DRAW";

    public $detail_started = false;

    public $attributes = array(
        "TopMargin" => "4%",
        "BottomMargin" => "2%",
        "RightMargin" => "5%",
        "LeftMargin" => "5%",
        "BodyStart" => "10%",
        "BodyEnd" => "10%",
        "ReportTitle" => "Set Report Title",
    );

    public $page_styles_started = false;

    public function __construct()
    {
        ReporticoObject::reportico_object();

        $this->formats = array(
            "body_style" => "blankline",
            "after_header" => "blankline",
            "before_trailer" => "blankline",
            "after_trailer" => "blankline",
        );
    }

    public static function reporticoStringToPhpStandalone($in_string,$parent)
    {
        $sessionClass = ReporticoSession();

        // first change '(colval)' parameters
        $out_string = $in_string;

        if (preg_match_all("/{([^}]*)/", $out_string, $matches)) {
            foreach ($matches[1] as $match) {
                $first = substr($match, 0, 1);
                if ($first == "=") {
                    $crit = substr($match, 1);
                    $label = "";
                    $value = "";
                    $parent->query->lookup_queries[$crit]->criteriaSummaryText($label, $value);
                    $out_string = preg_replace("/\{$match\}/",
                        $value,
                        $out_string);
                }
                if (preg_match("/^session_/", $match)) {
                    $crit = substr($match, 8);
                    $out_string = preg_replace("/\{$match\}/",
                        $sessionClass::getReporticoSessionParam($crit), $out_string);
                }
            }
        }

        if (preg_match("/date\((.*)\)/", $out_string, $match)) {
            $dt = preg_replace("/[\"']/", "", date($match[1]));
            $out_string = preg_replace("/date\(.*\)/i", "$dt", $out_string);
        }

        $out_string = preg_replace('/date("\(.*\)")/', "$parent->page_count",
            $out_string);

        $out_string = preg_replace('/pageno\(\)/', "$parent->page_count",
            $out_string);

        $out_string = preg_replace('/page\(\)/', "$parent->page_count",
            $out_string);

        $out_string = preg_replace('/{page}/i', "$parent->page_count",
            $out_string);

        $out_string = preg_replace('/{#page}/i', "$parent->page_count",
            $out_string);

        $out_string = preg_replace('/report_*title\(\)/i', $parent->deriveAttribute("ReportTitle"),
            $out_string);

        $out_string = preg_replace('/{report_*title}/i', $parent->deriveAttribute("ReportTitle"),
            $out_string);

        $out_string = preg_replace('/{title}/', $parent->reporttitle,
            $out_string);

        return ($out_string);
    }
    public function reporticoStringToPhp($in_string)
    {
        $sessionClass = ReporticoSession();


        // first change '(colval)' parameters
        $out_string = $in_string;

        if (preg_match_all("/{([^}]*)/", $out_string, $matches)) {
            foreach ($matches[1] as $match) {
                $first = substr($match, 0, 1);
                if ($first == "=") {
                    $crit = substr($match, 1);
                    $label = "";
                    $value = "";
                    $this->query->lookup_queries[$crit]->criteriaSummaryText($label, $value);
                    $out_string = preg_replace("/\{$match\}/",
                        $value,
                        $out_string);
                }
                if (preg_match("/^session_/", $match)) {
                    $crit = substr($match, 8);
                    $out_string = preg_replace("/\{$match\}/",
                        $sessionClass::getReporticoSessionParam($crit), $out_string);
                }
            }
        }

        if (preg_match("/date\((.*)\)/", $out_string, $match)) {
            $dt = preg_replace("/[\"']/", "", date($match[1]));
            $out_string = preg_replace("/date\(.*\)/i", "$dt", $out_string);
        }

        $out_string = preg_replace('/date("\(.*\)")/', "$this->page_count",
            $out_string);

        $out_string = preg_replace('/pageno\(\)/', "$this->page_count",
            $out_string);

        $out_string = preg_replace('/page\(\)/', "$this->page_count",
            $out_string);

        if ( preg_match("/TCPDF/", get_class($this))) {
            $out_string = preg_replace('/{page}/i', "$this->page_count", $out_string);
            $out_string = preg_replace('/{pagetotal}/i', "$this->page_count", $out_string);
        } else {
            $out_string = preg_replace('/{page}/i', "<span class='reportico-page-number'>$this->page_count</span>",
                $out_string);

            $out_string = preg_replace('/{pagetotal}/i', "<span class='reportico-page-count'>$this->page_count</span>",
                $out_string);
        }

        $out_string = preg_replace('/{#page}/i', "$this->page_count",
            $out_string);

        $out_string = preg_replace('/report_*title\(\)/i', $this->reporttitle,
            $out_string);

        $out_string = preg_replace('/{report_*title}/i', $this->reporttitle,
            $out_string);

        $out_string = preg_replace('/{title}/', $this->reporttitle,
            $out_string);

        return ($out_string);
    }

    public function setQuery(&$query)
    {
        $this->query = &$query;
        $this->columns = &$query->columns;
    }

    public function setColumns(&$columns)
    {
        $this->columns = &$columns;
    }

    public function start($engine = false)
    {
        $this->body_display = "show";
        if (ReporticoUtility::getRequestItem("hide_output_text")) {
            $this->body_display = false;
        }

        $this->graph_display = "show";
        if (ReporticoUtility::getRequestItem("hide_output_graph")) {
            $this->graph_display = false;
        }

        $this->page_line_count = 0;
        $this->line_count = 0;
        $this->page_count = 0;
        $this->debug("Base Start **");

        $this->reporttitle = $this->query->deriveAttribute("ReportTitle", "Set Report Title");
        if (isset($this->query->user_parameters["custom_title"])) {
            $this->reporttitle = $this->query->user_parameters["title"];
            $this->query->setAttribute("ReportTitle", $this->reporttitle);
        }
        $this->reportfilename = $this->reporttitle;
        $pos = 5;

        // Reorganise group trailers so they are as an array
        foreach ($this->query->groups as $group) {
            $group->organiseTrailersByDisplayColumn();
        }
    }

    public function finish()
    {
        $sessionClass = ReporticoSession();

        $this->last_line = true;
        $this->debug("Base finish");
        if ($sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            $this->afterGroupTrailers();
        }

        if ($this->page_count > 0) {
            $this->finishPage();
        }

    }

    public function beginPage()
    {
        $this->debug("Base New Page");
        $this->page_count++;
        $this->page_line_count = 0;

    }

    // For each line reset styles to default values
    public function setDefaultStyles()
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

    public function beforeFormatCriteriaSelection()
    {
    }

    public function formatCriteriaSelection($label, $value)
    {
        return;
    }

    public function formatCustomTrailer(&$trailer_col, &$value_col) // PDF

    {
        return;
    }

    public function customTrailerWrappers()
    {
        return;
    }

    public function endOfPageBlock()
    {
        return;
    }

    public function formatCriteriaSelectionSet()
    {
        $sessionClass = ReporticoSession();

        $is_criteria = false;
        foreach ($this->query->lookup_queries as $name => $crit) {
            $label = "";
            $value = "";

            if (isset($crit->criteria_summary) && $crit->criteria_summary) {
                $label = $crit->deriveAttribute("column_title", $crit->query_name);
                $value = $crit->criteria_summary;
            } else {
                if (ReporticoUtility::getRequestItem($name . "_FROMDATE_DAY", "")) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    $mth = ReporticoUtility::getRequestItem($name . "_FROMDATE_MONTH", "") + 1;
                    $value = ReporticoUtility::getRequestItem($name . "_FROMDATE_DAY", "") . "/" .
                    $mth . "/" .
                    ReporticoUtility::getRequestItem($name . "_FROMDATE_YEAR", "");
                    if (ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "")) {
                        $mth = ReporticoUtility::getRequestItem($name . "_TODATE_MONTH", "") + 1;
                        $value .= "-";
                        $value .= ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "") . "/" .
                        $mth . "/" .
                        ReporticoUtility::getRequestItem($name . "_TODATE_YEAR", "");
                    }
                } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "")) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    $value = ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "");
                    if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE", "")) {
                        $value .= "-";
                        $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE");
                    }

                } else if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "")) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    $value = ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "");
                    if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE", "")) {
                        $value .= "-";
                        $value .= ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE");
                    }

                } else if (ReporticoUtility::getRequestItem("EXPANDED_" . $name, "")) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    if ( is_array (ReporticoUtility::getRequestItem("EXPANDED_" . $name, "")))
                        $value .= implode(",", ReporticoUtility::getRequestItem("EXPANDED_" . $name, ""));
                    else
                        $value .= ReporticoUtility::getRequestItem("EXPANDED_" . $name, "");
                } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name, "")) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    if ( is_array(  ReporticoUtility::getRequestItem("MANUAL_" . $name, "") ))
                        $value .= implode( ",", ReporticoUtility::getRequestItem("MANUAL_" . $name, ""));
                    else
                        $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name, "");

                } else if (ReporticoUtility::getRequestItem($name)) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $label = ReporticoLang::translate($label);
                    if ( is_array(  ReporticoUtility::getRequestItem($name, "") ))
                        $value .= implode( ",", ReporticoUtility::getRequestItem( $name, ""));
                    else
                        $value .= ReporticoUtility::getRequestItem($name, "");

                }
            }
            if ($label || $value) {
                $is_criteria = true;
            }
        }

        if ($sessionClass::getReporticoSessionParam("target_show_criteria") && $is_criteria) {
            $this->beforeFormatCriteriaSelection();
            foreach ($this->query->lookup_queries as $name => $crit) {
                $label = "";
                $value = "";

                if ( $crit->hidden == "yes" )
                    continue;

                if (isset($crit->criteria_summary) && $crit->criteria_summary) {
                    $label = $crit->deriveAttribute("column_title", $crit->query_name);
                    $value = $crit->criteria_summary;
                } else {
                    if (ReporticoUtility::getRequestItem($name . "_FROMDATE_DAY", "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        $mth = ReporticoUtility::getRequestItem($name . "_FROMDATE_MONTH", "") + 1;
                        $value = ReporticoUtility::getRequestItem($name . "_FROMDATE_DAY", "") . "/" .
                        $mth . "/" .
                        ReporticoUtility::getRequestItem($name . "_FROMDATE_YEAR", "");
                        if (ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "")) {
                            $mth = ReporticoUtility::getRequestItem($name . "_TODATE_MONTH", "") + 1;
                            $value .= "-";
                            $value .= ReporticoUtility::getRequestItem($name . "_TODATE_DAY", "") . "/" .
                            $mth . "/" .
                            ReporticoUtility::getRequestItem($name . "_TODATE_YEAR", "");
                        }
                    } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        $value = ReporticoUtility::getRequestItem("MANUAL_" . $name . "_FROMDATE", "");
                        if (ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE", "")) {
                            $value .= "-";
                            $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name . "_TODATE");
                        }

                    } else if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        $value = ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_FROMDATE", "");
                        if (ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE", "")) {
                            $value .= "-";
                            $value .= ReporticoUtility::getRequestItem("HIDDEN_" . $name . "_TODATE");
                        }

                    } else if (ReporticoUtility::getRequestItem("EXPANDED_" . $name, "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        if ( is_array (ReporticoUtility::getRequestItem("EXPANDED_" . $name, "")))
                            $value .= implode(",", ReporticoUtility::getRequestItem("EXPANDED_" . $name, ""));
                        else
                            $value .= ReporticoUtility::getRequestItem("EXPANDED_" . $name, "");
                    } else if (ReporticoUtility::getRequestItem("MANUAL_" . $name, "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        if ( is_array(  ReporticoUtility::getRequestItem("MANUAL_" . $name, "") ))
                            $value .= implode( ",", ReporticoUtility::getRequestItem("MANUAL_" . $name, ""));
                        else {
                            if ( $crit->criteria_type == "DATE" || $crit->criteria_type == "DATERANGE" ) 
                                $value .= $crit->column_value_derived;
                            else
                                $value .= ReporticoUtility::getRequestItem("MANUAL_" . $name, "");
                        }
                    } else if (ReporticoUtility::getRequestItem($name, "")) {
                        $label = $crit->deriveAttribute("column_title", $crit->query_name);
                        $label = ReporticoLang::translate($label);
                        if ( is_array(  ReporticoUtility::getRequestItem($name, "") ))
                            $value .= implode( ",", ReporticoUtility::getRequestItem($name, ""));
                        else
                            $value .= ReporticoUtility::getRequestItem($name, "");
                    }
                }
                if ($label || $value) {
                    $this->formatCriteriaSelection($label, $value);
                }

            }
            $this->afterFormatCriteriaSelection();
        }
    }

    public function afterFormatCriteriaSelection()
    {
    }

    public function pageHeaders()
    {
        $this->formatPageHeaderStart();

        foreach ($this->query->pageHeaders as $ph) {

            // If one of the headers is {NOMORE} then ignore any subsequenct ones problably the default ones form the
            // reporticoDefaults file
            if ($ph->text == "{NOMORE}") {
                break;
            }

            if (
                ($ph->getAttribute("ShowInHTML") == "yes" && preg_match("/ReportHtml$/", get_class($this)))
                || ($ph->getAttribute("ShowInPDF") == "yes" && ( $this->query->target_format == "PDF" || $this->query->target_format == "HTML2PDF" ) )
                || ($ph->getAttribute("ShowInPDF") == "yes" && preg_match("/ReportHtml2pdf/", get_class($this)))
            ) {
                $this->formatPageHeader($ph);
            }
        }
        $this->formatPageHeaderEnd();
    }

    public function pageFooters()
    {
        $this->formatPageFooterStart();
        foreach ($this->query->pageFooters as $ph) {

            // If one of the headers is {NOMORE} then ignore any subsequenct ones problably the default ones form the
            // reporticoDefaults file
            if ($ph->text == "{NOMORE}") {
                break;
            }

            if (
                ($ph->getAttribute("ShowInHTML") == "yes" && preg_match("/ReportHtml/", get_class($this)))
                || ($ph->getAttribute("ShowInPDF") == "yes" && $this->query->target_format == "PDF")
            ) {
                $this->formatPageFooter($ph);
            }
        }
        $this->formatPageFooterEnd();
    }

    public function finishPage()
    {
        $this->debug("Base Finish Page");
    }

    public function newLine()
    {
        $this->debug(" Base New Page");
    }

    public function formatFormat($column_item, $format)
    {
        return;
    }

    public function formatPageHeader(&$header)
    {
        return;
    }

    public function formatPageFooter(&$header)
    {
        return;
    }

    public function formatPageHeaderStart()
    {
        return;
    }

    public function formatPageHeaderEnd()
    {
        return;
    }

    public function formatPageFooterStart()
    {
        return;
    }

    public function formatPageFooterEnd()
    {
        return;
    }

    public function formatReportDetailStart()
    {
        $this->detail_started = true;
        return;
    }

    public function formatReportDetailEnd()
    {
        $this->detail_started = false;
        return;
    }

    public function formatColumn(&$column_item)
    {
        $this->debug(" Base Format Column");
    }

    public function newColumnHeader()
    {
        $this->debug("Base New Page");
    }

    public function newColumn()
    {
        $this->debug("New Column");
    }

    public function showColumnHeader(&$column_item)
    {
        $this->debug("Show Column Header");

        if (!is_object($column_item)) {
            return (false);
        }

        $disp = $column_item->deriveAttribute(
            "column_display", "show");

        if ($disp == "hide") {
            return false;
        }

        return true;
    }

    public function publish()
    {
        $this->debug("Base Publish");
    }

    public function beginLine()
    {
        return;
    }

    public function endLine()
    {
        return;
    }

    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false)
    {
    }

    public function formatColumnTrailer_before_line()
    {
        return;
    }

    public function checkGraphicFit()
    {
        return true;
    }

    public function eachLine($val)
    {
        $sessionClass = ReporticoSession();

        if ($this->page_count == 0) {
            $this->beginPage();

            // Print Criteria Items at top of report
            $this->formatCriteriaSelectionSet();
            //$this->pageHeaders();
        }
        $this->debug("Base Each Line");

        if ($sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            $this->afterGroupTrailers();
        }

//echo "each line $this->inOverflow<BR>";
        $this->beforeGroupHeaders();

        $this->page_line_count++;
        $this->line_count++;

        // Add relevant values to any graphs
        foreach ($this->query->graphs as $k => $v) {
            $gr = &$this->query->graphs[$k];
            if (!$gr) {
                continue;
            }

            foreach ($gr->plot as $k1 => $v1) {
                $pl = &$gr->plot[$k1];
                $col = ReporticoUtility::getQueryColumn($pl["name"], $this->query->columns);
                $gr->addPlotValue($pl["name"], $k1, $col->column_value);
            }
            if ($gr->xlabel_column) {
                $col1 = ReporticoUtility::getQueryColumn($gr->xlabel_column, $this->query->columns);
                $gr->addXlabel($col1->column_value);
            }
        }

        $this->debug("Line: " . $this->page_line_count . "/" . $this->line_count);
    }

    public function afterGroupTrailers()
    {
        $sessionClass = ReporticoSession();

        $this->any_trailers = false;
        $this->any_custom_trailers = false;

        // Dont apply trailers in FORM style
        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        $trailer_first = true;
        $group_changed = false;
        if ($this->line_count <= 0) {
            // No group trailers as it's the first page
        } else {
            //Plot After Group Trailers
            if (count($this->query->groups) == 0) {
                return;
            }

            $rct = 0;

            // Work out which groups have triggered trailer by passing
            // through highest to lowest level .. group changes at level cause change at lower
            // also last line does too!!
            $uppergroupchanged = false;
            reset($this->query->groups);
            do {
                $group = current($this->query->groups);
                $group->change_triggered = false;
                if ($uppergroupchanged || $this->query->changed($group->group_name) || $this->last_line) {
                    $group->change_triggered = true;
                    $uppergroupchanged = true;
                }
            } while (next($this->query->groups));

            $graph_ct = 0;
            end($this->query->groups);
            do {
                $group = current($this->query->groups);
                if ($group->change_triggered) {
                    if ($rct == 1) {
                        $this->formatReportDetailEnd();
                    }

                    $rct++;
                    $group_changed = true;
                    $lev = 0;
                    $tolev = 0;

                    while ($lev < $group->max_level) {
                        if ($lev == 0) {
                            $this->applyFormat($group, "before_trailer");
                        }

                        $this->formatGroupTrailerStart($trailer_first);
                        $this->formatColumnTrailer_before_line();
                        $junk = 0;
                        $wc = count($this->columns);

                        // In PDF mode all trailer lines must be passed through twice
                        // to allow calculation of line height. Otherwise
                        // Only one pass through
                        $number_group_rows = 0;
                        for ($passno = 1; $passno <= 2; $passno++) {
                            if ($this->query->target_format == "PDF") {
                                if ($passno == 1) {
                                    $this->draw_mode = "CALCULATE";
                                }

                                if ($passno == 2) {
                                    $this->draw_mode = "DRAW";
                                    $this->unapplyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                    $this->checkPageOverflow();
                                    $this->applyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                }
                            } else {
                                if ($passno == 2) {
                                    break;
                                }

                            }
                            // Column Trailers
                            $linedrawn = false;
                            if ($this->draw_mode == "DRAW" && $number_group_rows == 0) {
                                $linedrawn = true;
                            } else {
                                $number_group_rows = 0;
                            }

                            foreach ($this->query->display_order_set["column"] as $w) {
                                if (!$this->showColumnHeader($w)) {
                                    continue;
                                }

                                if (array_key_exists($w->query_name, $group->trailers_by_column)) {
                                    $number_group_rows++;
                                    //if ( count($group->trailers_by_column[$w->query_name]) >= $lev + 1 && !$group->trailers_by_column[$w->query_name][$lev]["GroupTrailerCustom"] && $group->trailers_by_column[$w->query_name][$lev]["ShowInHTML"] == "yes")
                                    if (count($group->trailers_by_column[$w->query_name]) >= $lev + 1 && $group->trailers_by_column[$w->query_name][$lev]["ShowInHTML"] == "yes") {
                                        if (!$linedrawn) {
                                            $this->unapplyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $this->newReportPageLine("3");
                                            $this->applyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $linedrawn = true;
                                        }
                                        $this->formatColumnTrailer($w, $group->trailers_by_column[$w->query_name][$lev], $trailer_first);
                                    } else {
                                        if (!$linedrawn) {
                                            $this->unapplyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $this->newReportPageLine("3");
                                            $this->applyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                            $linedrawn = true;
                                        }
                                        $this->formatColumnTrailer($w, $junk, $trailer_first);
                                    }
                                    $this->any_trailers = true;

                                    if ($group->max_level > $tolev) {
                                        $tolev = $group->max_level;
                                    }
                                } else {
                                    if (!$linedrawn) {
                                        $this->unapplyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                        $this->newReportPageLine("3");
                                        $this->applyStyleTags("GROUPTRAILER", $this->query->output_group_trailer_styles);
                                        $linedrawn = true;
                                    }
                                    $this->formatColumnTrailer($w, $junk, $trailer_first);
                                }
                            } // foreach
                        }
                        if (!preg_match("/ReportHtml/", get_class($this))) {
                            $this->formatGroupTrailerEnd();
                        }


                        if ($trailer_first) {
                            $trailer_first = false;
                        }

                        $lev++;
                        $this->endLine();
                    } // while

                    // Generate custom trailers
                    if ($this->query->target_format == "PDF" || $this->query->target_format == "HTML2PDF") {
                        foreach ($group->trailers_by_column as $kk => $trailer) {
                            foreach ($trailer as $kk2 => $colgrp) {
                                if ($colgrp["ShowInPDF"] == "yes") {
                                    $this->formatCustomTrailer($w, $colgrp);
                                }

                            }
                        } // foreach
                    }
                    if ($this->query->target_format == "HTML") {
                        foreach ($group->trailers_by_column as $kk => $trailer) {
                            foreach ($trailer as $kk2 => $colgrp) {
                                if ($colgrp["ShowInHTML"] == "yes") {
                                    $this->formatCustomTrailer($w, $colgrp);
                                }

                            }
                        } // foreach
                    }

                    // Create Charts
                    if (!function_exists("imagecreatefromstring")) {
                        trigger_error("Function imagecreatefromstring does not exist - ensure PHP is installed with GD option", E_USER_NOTICE);
                    }

                    if (function_exists("imagecreatefromstring") &&
                        $this->graph_display &&
                        //ReporticoUtility::getCheckboxValue("target_show_graph"))
                        $sessionClass::getReporticoSessionParam("target_show_graph")) {
                        if ($graphs = &$this->query->getGraphByName($group->group_name)) {
                            foreach ($graphs as $graph) {
                                $graph->width_pdf_actual = ReporticoApp::getDefaultConfig("GraphWidthPDF", $graph->width_pdf);
                                $graph->height_pdf_actual = ReporticoApp::getDefaultConfig("GraphHeightPDF", $graph->height_pdf);
                                $graph->title_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->title, true);
                                $graph->xtitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->xtitle, true);
                                $graph->ytitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->ytitle, true);
                                if ($url_string = $graph->generateUrlParams($this->query->target_format)) {
                                    $this->plotGraph($graph, $graph_ct);
                                    $graph_ct++;
                                }
                            }
                        }
                    }


                }

            } while (prev($this->query->groups));

            if ($group_changed && preg_match("/ReportHtml/", get_class($this))) {
                $this->formatGroupTrailerEnd();
            }

            if ($group_changed && $this->query->target_format == "PDF") {
                $this->endOfPageBlock();
            }
        }
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
    }

    public function applyFormat($item, $format)
    {
        //echo "APPLY FORMAT $format<BR>";
        $formatval = $item->getFormat($format);
        $this->formatFormat($formatval, $format);
    }

    public function formatGroupTrailerStart($first = false)
    {
        return;
    }

    public function formatGroupCustomTrailerStart()
    {
        return;
    }

    public function formatGroupCustomTrailerEnd()
    {
        return;
    }

    public function formatGroupTrailerEnd($last_trailer = false)
    {
        return;
    }

    public function formatGroupHeaderStart()
    {

        return;
    }

    public function formatGroupHeaderEnd()
    {
        return;
    }

    public function beforeGroupHeaders()
    {
        $sessionClass = ReporticoSession();

        //if ( $this->inOverflow )
        //return;

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        // Work out which groups have triggered trailer by passing
        // through highest to lowest level .. group changes at level cause change at lower
        // also last line does too!!
        $uppergroupchanged = false;
        reset($this->query->groups);
        if ($this->query->groups) {
            do {
                $group = current($this->query->groups);
                $group->change_triggered = false;
                //echo "NAME $group->group_name <BR>";
                if ($uppergroupchanged || $this->query->changed($group->group_name) || $this->last_line) {
                    //echo "CHANGED $group->group_name <BR>";
                    $group->change_triggered = true;
                    $uppergroupchanged = true;
                }
            } while (next($this->query->groups));
        }

        $changect = 0;
        reset($this->query->groups);
        foreach ($this->query->groups as $name => $group) {
            //echo "TRIGGER $group->change_triggered <BR>";
            if (count($group->headers) > 0 && (($group->group_name == "REPORT_BODY" && $this->line_count == 0) || $group->change_triggered)) {
                if ($changect == 0 && $this->page_line_count > 0) {
                    $changect++;
                    $this->formatGroupHeaderStart($group->getFormat("before_header") == "newpage");
                    $this->applyFormat($group, "before_header");
                    //echo "1 BEFH <BR>";
                } else if ($changect == 0 || 1) {
                    $this->formatGroupHeaderStart($this->page_line_count > 0 && $group->getFormat("before_header") == "newpage");
                    //echo "2 BEFH <BR>";
                    $this->applyFormat($group, "before_header");
                }

                if ($sessionClass::getReporticoSessionParam("target_show_group_headers")) {
                    for ($i = 0; $i < count($group->headers); $i++) {
                        $col = &$group->headers[$i]["GroupHeaderColumn"];
                        $custom = $group->headers[$i]["GroupHeaderCustom"];
                        // Put off HTML Custom headers until later
                        if ($group->headers[$i]["ShowInHTML"] == "yes" && preg_match("/ReportHtml/", get_class($this)) && !$custom) {
                            $this->formatGroupHeader($col, $custom);
                        }

                        if ($group->headers[$i]["ShowInPDF"] == "yes" && preg_match("/ReportTCPDF/", get_class($this))) {
                            $this->formatGroupHeader($col, $custom);
                        }

                        if ($group->headers[$i]["ShowInPDF"] == "yes" && preg_match("/ReportFPDF/", get_class($this))) {
                            $this->formatGroupHeader($col, $custom);
                        }

                    }

                }

                if ($graphs = &$this->query->getGraphByName($group->group_name)) {
                    foreach ($graphs as $graph) {
                        $graph->clearData();
                    }
                }

                $this->formatGroupHeaderEnd();

                // For HTML custom headers draw them after the regular ones
                if ($this->query->target_format == "HTML2PDF") {
                    $this->formatGroupCustomHeaderStart();

                    for ($i = 0; $i < count($group->headers); $i++) {
                        $col = &$group->headers[$i]["GroupHeaderColumn"];
                        $custom = $group->headers[$i]["GroupHeaderCustom"];
                        if ( $custom )
                            if ($group->headers[$i]["ShowInPDF"] == "yes" && preg_match("/ReportHtml/", get_class($this))) {
                                $this->formatCustomHeader($col, $custom);
                            }
                    }

                    $this->formatGroupCustomHeaderEnd();
                }

                if ($this->query->target_format == "HTML") {
                    $this->formatGroupCustomHeaderStart();

                    for ($i = 0; $i < count($group->headers); $i++) {
                        $col = &$group->headers[$i]["GroupHeaderColumn"];
                        $custom = $group->headers[$i]["GroupHeaderCustom"];
                        if ( $custom )
                            if ($group->headers[$i]["ShowInHTML"] == "yes" && preg_match("/ReportHtml/", get_class($this))) {
                                $this->formatCustomHeader($col, $custom);
                            }
                    }

                    $this->formatGroupCustomHeaderEnd();
                }

                $this->applyFormat($group, "after_header");
            } else if (($group->group_name == "REPORT_BODY" && $this->line_count == 0) || $this->query->changed($group->group_name)) {
                $this->formatGroupHeaderStart($group->getFormat("before_header") == "newpage");
                if ($graphs = &$this->query->getGraphByName($group->group_name)) {
                    foreach ($graphs as $graph) {
                        $graph->clearData();
                    }
                }
            }
        }

        // Show column headers for HTML/CSV on group change, or on first line of report, or on new page
        if ((!$this->page_started && ($this->query->target_format == "HTML" || $this->query->target_format == "HTMLPRINT")) ||
            ($this->query->target_format != "CSV" && $changect > 0) ||
            $this->page_line_count == 0) {
            $this->formatReportDetailStart();
            if ($this->query->target_format == "PDF") {
                $this->column_header_required = true;
            } else {
                $this->formatHeaders();
            }

            $this->page_styles_started = true;
        }
    }

    public function formatGroupHeader(&$col, $custom)
    {
        return;
    }

    public function formatHeaders()
    {
        return;
    }

    public function newReportPageLine($txt = "")
    {
        return;
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

    public function &getContent() {
        return $this->text;
    }

}
