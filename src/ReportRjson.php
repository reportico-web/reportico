<?php
/*

 * File:        ReportRjson.php
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

class ReportRjson extends Report
{
    public $graph_sessionPlaceholder = 0;

    public $jar = array(
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
        "pages" => array(
        ),
    );

    public function __construct()
    {
        return;
    }

    public function start($engine = false)
    {
        Report::start();

        $this->debug("RJSON Start **");
        $this->page_line_count = 0;
    }

    public function finish()
    {
        Report::finish();

        if ($this->line_count < 1) {
            $this->setupReportAttributes();
        }

        $str = json_encode($this->jar);
        $len = strlen($str);

        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: application/json');

        header("Content-Length: $len");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Disposition: attachment; filename=reportico.json');

        echo $str;
        die;

        $this->page_started = false;
    }

    public function beginPage()
    {
        Report::beginPage();
        $this->setupReportAttributes();
    }

    public function setupReportAttributes()
    {
        $sessionClass = ReporticoSession();

        $title = $this->query->deriveAttribute("ReportTitle", "Unknown");
        $this->jar["title"] .= ReporticoLang::translate($title);
        if ($this->query->output_template_parameters["show_hide_report_output_title"] == "hide") {
            $this->jar["attributes"]["hide_title"] = true;
        }

        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $forward .= "&";
        }

        // In printable rjson mode dont show back box
        if (!ReporticoUtility::getRequestItem("printable_rjson")) {
            // Show Go Back Button ( if user is not in "SINGLE REPORT RUN " )
            if (!$this->query->access_mode || ($this->query->access_mode != "REPORTOUTPUT")) {
                $this->jar["attributes"]["show_prepare_button"] .= $this->query->getActionUrl() . '?' . $forward . 'execute_mode=PREPARE&reportico_session_name=' . $sessionClass::reporticoSessionName();
            }
            if ($sessionName::getReporticoSessionParam("show_refresh_button")) {
                $this->jar["attributes"]["show_refresh_button"] .= $this->query->getActionUrl() . '?' . $forward . 'refreshReport=1&execute_mode=EXECUTE&reportico_session_name=' . $sessionClass::reporticoSessionName();
            }

        } else {
            $this->jar["attributes"]["show_print_button"] .= $this->query->getActionUrl() . '?' . $forward . 'printReport=1&execute_mode=EXECUTE&reportico_session_name=' . $sessionClass::reporticoSessionName();
        }

        $this->jar["attributes"]["column_header_styles"] = $this->query->output_header_styles;
        $this->jar["attributes"]["column_page_styles"] = $this->query->output_page_styles;
        $this->jar["attributes"]["page_style"] = $sessionClass::sessionRequestItem("target_style", "TABLE");
        $this->setupColumns();
    }

    public function setupColumns()
    {
        foreach ($this->query->groups as $name => $group) {
            if (count($group->headers) > 0) {
                foreach ($group->headers as $gphk => $col) {
                    $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);
                    $this->jar["groups"][$col->query_name] = array();
                    $this->jar["groups"][$col->query_name]["group_header"] = true;
                }
            }

        }

        //foreach ( $this->columns as $col )
        foreach ($this->query->display_order_set["column"] as $col) {
            if (!$this->showColumnHeader($col)) {
                continue;
            }

            // Create sensible column header label from column name
            $label = ReporticoUtility::columnNameToLabel($col->query_name);
            $label = $col->deriveAttribute("column_title", $label);
            $label = ReporticoLang::translate($label);

            $this->jar["columns"][$col->query_name] = array();
            $this->jar["columns"][$col->query_name]["label"] = $label;

            $colstyles = array();
            $cw = $col->deriveAttribute("ColumnWidthHTML", false);
            $just = $col->deriveAttribute("justify", false);
            if ($cw || $just) {
                $this->jar["columns"][$col->query_name]["styles"] = array();
                if ($cw) {
                    $this->jar["columns"][$col->query_name]["styles"]["width"] = $cw;
                }

                if ($just) {
                    $this->jar["columns"][$col->query_name]["styles"]["text-align"] = $just;
                }

            }

        }
    }

    public function formatColumn(&$column_item)
    {
        if ($this->body_display != "show") {
            return;
        }

        if (!$this->showColumnHeader($column_item)) {
            return;
        }

        $colstyles = array();
        $cw = $column_item->deriveAttribute("ColumnWidthHTML", false);
        $just = $column_item->deriveAttribute("justify", false);
        if ($cw) {
            $colstyles["width"] = $cw;
        }

        if ($just) {
            $colstyles["text-align"] = $just;
        }

        $style = $this->getStyleTags($colstyles, $column_item->output_cell_styles, $this->query->output_allcell_styles) . '>';

        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["style"] = $style;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["images"] = $column_item->output_images;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["attributes"][$column_item->query_name]["hyperlinks"] = $column_item->output_hyperlinks;
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["data"][$column_item->query_name] = $column_item->column_value;
    }

    public function getStyleTags($styleset, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";

        if ($grandparent_styleset && is_array($grandparent_styleset)) {
            foreach ($grandparent_styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v;";
            }
        }

        if ($parent_styleset && is_array($parent_styleset)) {
            foreach ($parent_styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v;";
            }
        }

        if ($styleset && is_array($styleset)) {
            foreach ($styleset as $k => $v) {
                if (!$outtext) {
                    $outtext = "style=\"";
                }

                $outtext .= "$k:$v;";
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
                //$this->jar[""] .= "<TR><TD><br></TD></TR>";
                break;

            case "solidline":
                //$this->jar[""] .= '<TR><TD colspan="10"><hr style="width:100%;" size="2"/></TD>';
                break;

            case "newpage":
                //$this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'reportico-page" '.$this->getStyleTags($this->query->output_page_styles).'>';
                $this->page_started = true;
                break;

            default:
                //$this->jar[""] .= "<TR><TD>Unknown Format $in_value</TD></TR>";
                break;

        }
    }

    public function formatHeaders()
    {
        return;
    }

    public function formatGroupHeaderStart($throw_page = false)
    {
        //$this->jar["pages"][$this->page_count]["groups"][] = array();
        $this->jar["pages"][$this->page_count]["groups"] = array();
        $this->jar["pages"][$this->page_count]["groups"]["headers"] = array();
        $this->jar["pages"][$this->page_count]["groups"]["trailers"] = array();
        return;
    }

    public function formatGroupHeader(&$col) // HTML

    {
        $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);

        // Create sensible group header label from column name
        $tempstring = ReporticoUtility::columnNameToLabel($col->query_name);
        $tempstring = $col->deriveAttribute("column_title", $tempstring);
        $tempstring = ReporticoLang::translate($col->deriveAttribute("column_title", $tempstring));

        if (!isset($this->jar["pages"][$this->page_count]["groups"]["headers"])) {
            $this->jar["pages"][$this->page_count]["groups"]["headers"] = array();
        }

        $hct = count($this->jar["pages"][$this->page_count]["groups"]["headers"]) - 1;
        $this->jar["pages"][$this->page_count]["groups"]["headers"][$hct] = array(
            "label" => $tempstring,
            "labelstyle" => $this->query->output_group_header_label_styles,
            "value" => $qn->column_value,
            "valuestyle" => $this->query->output_group_header_value_styles,
        );
    }

    public function formatGroupHeaderEnd()
    {
        return;
    }

    public function beginLine()
    {
        if ($this->body_display != "show") {
            return;
        }

        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["data"] = array();
        $this->jar["pages"][$this->page_count]["lines"][$this->page_line_count]["style"] = $this->query->output_row_styles;
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
        return;

        if ($graph_ct == 0) {
            if ($this->page_started) {
                $this->jar[""] .= '</TBODY></TABLE>';
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
        $this->jar[""] .= '<div class="reportico-chart">';
        if ($url_string) {
            $this->jar[""] .= $url_string;
        }

        $this->jar[""] .= '</div>';
    }
    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false) // HTML

    {
        $sessionClass = ReporticoSession();

        $just = $trailer_col->deriveAttribute("justify", false);
        if ($just && $just != "left") {
            $this->query->output_group_trailer_styles["text-align"] = $just;
        } else {
            $this->query->output_group_trailer_styles["text-align"] = "left";
        }

        if (!$sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        if ($value_col) {
            $group_label = $value_col->getAttribute("group_trailer_label");
            if (!$group_label) {
                $group_label = $value_col->getAttribute("column_title");
            }

            if (!$group_label) {
                $group_label = $value_col->query_name;
                $group_label = str_replace("_", " ", $group_label);
                $group_label = ucwords(strtolower($group_label));
            }
            $group_label = ReporticoLang::translate($group_label);
            $padstring = $value_col->old_column_value;
            if ($value_col->output_images) {
                $padstring = $this->formatImages($value_col->output_images);
            }

            if ($group_label == "BLANK") {
                $group_label == "";
            }

            if (!isset($this->jar["pages"][$this->page_count]["groups"]["trailers"])) {
                $this->jar["pages"][$this->page_count]["groups"]["trailers"] = array();
            }

            $hct = count($this->jar["pages"][$this->page_count]["groups"]["trailers"]) - 1;
            $this->jar["pages"][$this->page_count]["groups"]["trailers"][$trailer_col->query_name][] = array(
                "label" => $group_label,
                "labelstyle" => $this->query->output_group_trailer_styles,
                "value" => $value_col->column_value,
                "valuestyle" => $this->query->output_group_trailer_styles,
            );
        }
        //else
        //$this->jar[""] .= "&nbsp;";
    }

    public function formatGroupTrailerStart($first = false)
    {
        return;
    }

    public function formatGroupTrailerEnd()
    {
        return;
        $this->jar[""] .= "</TR>";
        if ($this->page_started) {
            $this->jar[""] .= "</TFOOT></TABLE>";
        }
        $this->page_started = false;
    }

    public function endLine()
    {
        return;
        if ($this->body_display != "show") {
            return;
        }

        $this->jar[""] .= "</TR>";
    }

    public function eachLine($val) // HTML

    {
        $sessionClass = ReporticoSession();

        Report::eachLine($val);

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            if (!$this->page_started) {
                $formpagethrow = $this->query->getAttribute("formBetweenRows");
                switch ($formpagethrow) {
                    case "newpage":
                        if ($this->page_line_count > 0) {
                            $formpagethrow = "reportico-pageFormLine reportico-new-page";
                        } else {
                            $formpagethrow = "reportico-pageFormLine";
                        }

                        break;
                    case "blankline":
                        $formpagethrow = "reportico-pageFormBlank";
                        break;
                    case "solidline":
                        $formpagethrow = "reportico-pageFormLine";
                        break;
                }

                // PPP $this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'reportico-page '.$formpagethrow.'" '.$this->getStyleTags($this->query->output_page_styles).'>';
                $this->page_started = true;
            }
            foreach ($this->query->groups as $val) {
                for ($i = 0; $i < count($val->headers); $i++) {
                    $col = &$val->headers[$i];
                    $this->formatGroupHeader($col);
                }
            }
            foreach ($this->query->display_order_set["column"] as $k => $w) {
                if ($w->attributes["column_display"] != "show") {
                    continue;
                }

                $this->formatGroupHeader($w);
            }
            $this->page_line_count++;
            $this->line_count++;
            //$this->jar[""] .= '</TABLE>';
            $this->page_started = false;
            return;
        }

        if ($this->page_line_count == 1) {
            //$this->jar[""] .="<tr class='reportico-prepare-crit-line'>";
            //foreach ( $this->columns as $col )
            //$this->formatColumnHeader($col);
            //$this->jar[""] .="</tr>";
        }

        //foreach ( $this->columns as $col )
        if ($this->body_display == "show" && $sessionClass::getReporticoSessionParam("target_show_detail")) {
            $this->beginLine();
            if (!$this->page_started) {
                //$this->jar[""] .= '<TABLE class="'.$this->query->getBootstrapStyle("page").'reportico-page" '.$this->getStyleTags($this->query->output_page_styles).'>';
                $this->page_started = true;
            }
            foreach ($this->query->display_order_set["column"] as $col) {
                $this->formatColumn($col);
            }

            $this->endLine();
        }
    }

    public function pageTemplate()
    {
        $this->debug("Page Template");
    }
    public function beforeFormatCriteriaSelection()
    {
        $this->jar["criteria"][] = array();
    }

    public function formatCriteriaSelection($label, $value)
    {
        $crtieriact = count($this->jar["criteria"]) - 1;
        $this->jar["criteriact"]["label"] = $label;
        $this->jar["criteriact"]["value"] = $value;
    }

    public function afterFormatCriteriaSelection()
    {
        //$this->jar[""] .= "</TABLE>";
    }

    public function finishPage()
    {
        //$this->debug("HTML Finish Page");
        //pdf_end_page($this->document);
    }

    public function publish()
    {
        Report::publish();
        $this->debug("Publish HTML");
    }

    public function formatPageHeader(&$header)
    {
        return;
        $just = strtolower($header->getAttribute("justify"));

        $this->jar[""] .= "<TR>";
        $this->jar[""] .= '<TD colspan="10" justify="' . $just . '">';
        $this->jar[""] .= ($header->text);
        $this->jar[""] .= "</TD>";
        $this->jar[""] .= "</TR>";

        return;
    }

    public function formatPageFooter(&$header)
    {
        return;
        $just = strtolower($header->getAttribute("justify"));

        $this->jar[""] .= "<TR>";
        $this->jar[""] .= '<TD colspan="10" justify="' . $just . '">';
        $this->jar[""] .= ($header->text);
        $this->jar[""] .= "</TD>";
        $this->jar[""] .= "</TR>";

        return;
    }

}
