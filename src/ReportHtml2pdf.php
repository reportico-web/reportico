<?php
/*

 * File:        ReportHtml2pdf.php
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

class ReportHtml2pdf extends Report
{
    public $abs_top_margin;
    public $abs_bottom_margin;
    public $abs_left_margin;
    public $abs_right_margin;
    public $header_count = 0;
    public $footer_count = 0;
    public $graph_sessionPlaceholder = 0;
    public $group_count = 0;
    public $currentTrailerRow = 0;
    public $currentGroup = false;

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
            "showColumnHeaders" => true,
            "show_criteria" => true,

            "column_header_styles" => false,
        ),
        "title" => "Set Report Title",
        "pageheaderstop" => array(),
        "pagefooters" => array(),
        "criteria" => array(),
        "groups" => array(),
        "columns" => array(),
        "buttons" => array(),
        "pages" => array(),
        "styles" => array(),
    );

    public function __construct()
    {
        return;
    }

    public function start($engine = false)
    {
        Report::start();

        $this->page_line_count = 0;
        $this->abs_top_margin = $this->absPagingHeight($this->getAttribute("TopMargin"));
        $this->abs_bottom_margin = $this->absPagingHeight($this->getAttribute("BottomMargin"));
        $this->abs_right_margin = $this->absPagingHeight($this->getAttribute("RightMargin"));
        $this->abs_left_margin = $this->absPagingHeight($this->getAttribute("LeftMargin"));
    }

    public function finish()
    {
        Report::finish();




        $this->setPageWidgets();

        if ($this->report_file) {
            $this->debug("Saved to $this->report_file");
        } else {
            $this->debug("No html file specified !!!");
            $buf = "";
            $len = strlen($buf) + 1;

            print($buf);
        }

        $this->footer_count++;
        $this->jar["footers"][] = $this->footer_count;
        $this->page_started = false;

        //echo "<PRE>"; var_dump($this->jar); echo "</PRE>";
        //die;
    }

    public function absPagingHeight($height_string)
    {
        $height = (int) $height_string;
        if ($this->page_height && strstr($height_string, "%")) {
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

    /**
     * @brief Sets column header
     *
     * @param $column_item
     */
    public function formatColumnHeader(&$column_item) //HTML
    {
        $sessionClass = ReporticoSession();


        if ($this->body_display != "show") {
            return;
        }

        if (!$sessionClass::getReporticoSessionParam("target_show_detail")) {
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

        $this->jar["pages"][$this->page_count]["headers"][] = [
            "styles" => $this->getStyleTags($colstyles, $this->query->output_header_styles),
            "content" => $padstring
            ];
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

        if ($column_item->output_images) {
            $padstring = $this->formatImages($column_item->output_images);
        }

        if ($column_item->output_hyperlinks) {
            $padstring = $this->formatHyperlinks($column_item->output_hyperlinks, $padstring);
        }

        $this->jar["pages"][$this->page_count]["rows"][$this->line_count]["data"][] = [
            "styles" => $this->getStyleTags($colstyles, $column_item->output_cell_styles, $this->query->output_allcell_styles),
            "content" => $padstring
            ];
        $rowcount = count( $this->jar["pages"][$this->page_count]["rows"]);
        //$this->currentGroup["rows"][]  = & $this->jar["pages"][$this->page_count]["rows"][$rowcount - 1]);

    }

    public function getStyleTags($styleset, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";

        if ($grandparent_styleset && is_array($grandparent_styleset)) {
            foreach ($grandparent_styleset as $k => $v) {

                $outtext .= "$k:$v !important;";
            }
        }

        if ($parent_styleset && is_array($parent_styleset)) {
            foreach ($parent_styleset as $k => $v) {

                $outtext .= "$k:$v !important;";
            }
        }

        if ($styleset && is_array($styleset)) {
            foreach ($styleset as $k => $v) {

                $outtext .= "$k:$v !important;";
            }
        }

        return $outtext;
    }

    public function formatFormat($in_value, $format)
    {
        if ( $in_value == "newpage" ) {
            $this->currentGroup[$format] = $in_value;
        }

        switch ($in_value) {
            case "blankline":
                break;

            case "solidline":
                break;

            case "newpage":
                $this->page_started = true;
                break;

            default:
                break;

        }
    }

    public function formatHeaders()
    {
        $sessionClass = ReporticoSession();

        if (!$this->page_started) {
            $this->page_started = true;
        }

        if ($sessionClass::sessionRequestItem("target_style", "TABLE") == "FORM") {
            return;
        }

        if ($this->body_display != "show") {
            return;
        }

        // Only set headers on first line
        if ( $this->line_count > 0)
            return;

        // Load the result array with column headers
        foreach ($this->query->display_order_set["column"] as $w) {
            $this->formatColumnHeader($w);
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

        $this->openGroup();

        if ($throw_page || $this->page_started) {
            $this->pageHeaders();
        } else {
        }

    }

    /**
     *  @brief Creates a group entry in the json array
     */
    public function openGroup() {

        $this->jar["groups"][] = array ( "parentGroup" => $this->currentGroup,
            "customheaders" => [],
            "pageheaders" => [],
            "headers" => [],
            "customtrailers" => [],
            "startrow" => $this->line_count,
            "endrow" => $this->line_count,
            "trailers" => [], "graphs" => [], "rows" => [] );
        $groupct = count($this->jar["groups"]);

        if ( $this->currentGroup )
            if ( !isset($this->jar["pages"][$this->page_count]["rows"][$this->line_count+1])) 
                $this->jar["pages"][$this->page_count]["rows"][$this->line_count+1] =
                    [ "data" => [],
                        "groupstarts" => [ &$this->jar["groups"][$groupct - 1] ],
                        "groupends" => false
                        ];
            else
                $this->jar["pages"][$this->page_count]["rows"][$this->line_count+1]["groupstarts"][]  = &$this->jar["groups"][$groupct - 1];
        $this->jar["pages"][$this->page_count]["rows"][$this->line_count+1]["openrowsection"] = true;

        $this->currentGroup = &$this->jar["groups"][$groupct - 1];

    }

    /**
     *  @brief Creates a group entry in the json array
     */
    public function closeGroup() {

        $x= $this->line_count;
        $this->currentGroup["endrow"] = $this->line_count - 1;
        $this->jar["pages"][$this->page_count]["rows"][$this->line_count]["closerowsection"] = true;
        if ( isset($this->currentGroup["parentGroup"]) && $this->currentGroup["parentGroup"] ) {
            $this->jar["pages"][$this->page_count]["rows"][$this->line_count]["groupends"][]  = $this->currentGroup;
            $this->currentGroup = &$this->currentGroup["parentGroup"];
        } else {
            unset($this->currentGroup);
            $this->currentGroup = false;
        }
    }


    public function formatGroupHeader(&$col, $custom) // HTML
    {
        if (!$col) {
            return;
        }

        if ( $custom)
        {
            $style = "";
            $attr = array();
            $this->extractStylesAndTextFromString ( $custom, $styles, $attr );

            $this->currentGroup["headers"][] = [ "styles" => $styles, "content" => $custom ];
            return;
        }


        $styles = $this->getStyleTags($this->query->output_group_header_label_styles);
        $qn = ReporticoUtility::getQueryColumn($col->query_name, $this->query->columns);


        $padstring = $qn->column_value;

        // Create sensible group header label from column name
        $tempstring = ReporticoUtility::columnNameToLabel($col->query_name);
        $tempstring = $col->deriveAttribute("column_title", $tempstring);
        $tempstring = ReporticoLang::translate($col->deriveAttribute("column_title", $tempstring));

        $this->currentGroup["headers"][] = [ "styles" => $styles,
            "label" => ReporticoLang::translate($col->deriveAttribute("group_header_label", $tempstring) ),
            "value" => $padstring
            ];
    }

    public function formatGroupHeaderEnd()
    {
        $this->page_started = false;
    }

    public function beginLine()
    {
        if ($this->body_display != "show") {
            return;
        }
    }

    public function plotGraph(&$graph, $graph_ct = false)
    {
        if ($graph_ct == 0) {
            $this->page_started = false;
        }
        $this->graph_sessionPlaceholder++;
        $graph->width_actual = ReporticoApp::getDefaultConfig("GraphWidth", $graph->width_pdf);
        $graph->height_actual = ReporticoApp::getDefaultConfig("GraphHeight", $graph->height_pdf);
        $graph->title_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->title, true);
        $graph->xtitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->xtitle, true);
        $graph->ytitle_actual = Assignment::reporticoMetaSqlCriteria($this->query, $graph->ytitle, true);
        $url_string = $graph->generateUrlParams("HTML2PDF", $this->graph_sessionPlaceholder);
        if ($url_string) {
            $this->jar["pages"][$this->page_count]["rows"][$this->line_count]["graphs"][] = [ "url" => $url_string ];
        }
    }

    public function formatColumnTrailer(&$trailer_col, &$value_col, $trailer_first = false) // HTML
    {
        $sessionClass = ReporticoSession();

        if (!$sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        $just = $trailer_col->deriveAttribute("justify", false);
        if ($just && $just != "left") {
            $this->query->output_group_trailer_styles["text-align"] = $just;
        } else {
            $this->query->output_group_trailer_styles["text-align"] = "left";
        }


        if ($value_col && !$value_col["GroupTrailerCustom"]) {
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
                $content= $padstring;
            } else {
                $content = $group_label . " " . $padstring;
            }

        } else {
            $content = "";
        }

        $this->currentGroup["trailers"][$this->currentTrailerRow][] =
                    ["styles" => $this->getStyleTags($this->query->output_group_trailer_styles),
                      "content" => $content];
    }

    /**
     * @brief
     * @param bool $first
     */
    public function formatGroupTrailerStart($first = false)
    {
        if ($first)
            $this->currentTrailerRow = -1;
        $this->currentTrailerRow++;

        $this->currentGroup["trailers"][$this->currentTrailerRow] = [];

    }

    public function formatGroupTrailerEnd($last_trailer = false)
    {
        $this->closeGroup();
        $this->page_started = false;
    }

    public function formatGroupCustomHeaderStart() 
    {
    }

    public function formatGroupCustomHeaderEnd() 
    {
    }


    public function formatCustomHeader(&$col, $custom) // HTML
    {
        $sessionClass = ReporticoSession();

        // If this is the first custom header break a little
        if (!$sessionClass::getReporticoSessionParam("target_show_group_headers")) {
            return;
        }

        if (!$custom) {
            return;
        }

        $style = "";
        $attr = array();
        $this->extractStylesAndTextFromString ( $custom, $styles, $attr );
        $this->currentGroup["customheaders"][] = [ "content" => $custom, "styles" => $styles, "attr" => $attr];
        $styles .= "position: absolute;";
        return;

    }


    public function formatGroupCustomTrailerStart() 
    {
    }

    public function formatGroupCustomTrailerEnd() 
    {
    }


    public function formatCustomTrailer(&$trailer_col, &$value_col) // PDF
    {
        $sessionClass = ReporticoSession();

        // If this is the first custom trailer break a little
        if (!$sessionClass::getReporticoSessionParam("target_show_group_trailers")) {
            return;
        }

        if (!$value_col["GroupTrailerCustom"]) {
            return;
        }

        $style = "";
        $attr = array();
        $custom = $value_col["GroupTrailerCustom"];
        $this->extractStylesAndTextFromString ( $custom, $styles, $attr );
        $styles .= "position: absolute";

        $this->currentGroup["customtrailers"][] = [
            "styles" => $styles,
            "content"=> $custom
        ];
        return;

    }

    public function endLine()
    {
        if ($this->body_display != "show") {
            return;
        }

    }

    public function eachLine($val) // HTML
    {
        $sessionClass = ReporticoSession();

        //if ($this->line_count > 3) return;
//echo $this->line_count;
        //echo "<PRE>"; var_dump($this->jar); echo "</PRE>";
        Report::eachLine($val);


        // Create an estry fro row
        if ( !isset($this->jar["pages"][$this->page_count]["rows"][$this->line_count]))
            $this->jar["pages"][$this->page_count]["rows"][$this->line_count] =
            [ "data" => [],
                "groupstarts" => [],
                "groupends" => []
            ];

        $this->jar["pages"][$this->page_count]["rows"][$this->line_count]["line"] = $this->line_count;

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
            $this->page_started = false;
            return;
        }

        if ($this->page_line_count == 1) {
            //foreach ( $this->columns as $col )
            //$this->formatColumnHeader($col);
        }

        //foreach ( $this->columns as $col )
        if ($this->body_display == "show" && $sessionClass::getReporticoSessionParam("target_show_detail")) {
            $this->beginLine();
            if (!$this->page_started) {
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

        // Setup Buttons, Title etc
        $this->setPageWidgets();

        // Page Headers
        Report::pageHeaders();

        $colstyles = false;
        $this->jar["styles"]["header"] = $this->getStyleTags($colstyles, $this->query->output_header_styles);
        $this->jar["styles"]["page"] = $this->getStyleTags($colstyles, $this->query->output_page_styles);
        $this->jar["styles"]["row"] = $this->getStyleTags($colstyles, $this->query->output_row_styles);
        $this->jar["styles"]["criteria"] = $this->getStyleTags($colstyles, $this->query->output_criteria_styles);
        $this->jar["styles"]["body"] = $this->getStyleTags($colstyles, $this->query->output_reportbody_styles);
        $this->jar["styles"]["group_header_label"] = $this->getStyleTags($colstyles, $this->query->output_group_header_label_styles);
        $this->jar["styles"]["group_header_value"] = $this->getStyleTags($colstyles, $this->query->output_group_header_value_styles);

        $this->jar["classes"]["page"] = $this->query->getBootstrapStyle("page");

        //var_dump($this->getStyleTags($colstyles, $this->query->output_criteria_styles)); die;
        // Create a dummy group for the wholte report
        $this->openGroup();

    }

    public function beforeFormatCriteriaSelection()
    {
    }

    public function formatCriteriaSelection($label, $value)
    {
        $this->jar["criteria"][] = 
            [ "label" => $label,
              "value" => $value ];

    }

    public function afterFormatCriteriaSelection()
    {
    }

    public function finishPage()
    {
        // Page Footers
        Report::pageFooters();

        $this->closeGroup();
    }

    public function publish()
    {
        Report::publish();
        $this->debug("Publish HTML");
    }

    public function formatPageHeaderStart()
    {
        return;
    }

    public function formatPageHeaderEnd()
    {
        return;
    }

    public function formatPageHeader(&$header)
    {
        $styles = "";
        $text = $header->text;

        $this->extractStylesAndTextFromString($text, $styles, $header->attributes, $parent_styleset = false, $grandparent_styleset = false);
        $just = strtolower($header->getAttribute("justify"));

        //var_dump($header->attributes);
        //echo "Styles = $styles <BR>";
        $img = false;
        $imgstyles = false;

        if ($styles) {
            $matches = array();
            if (preg_match("/background: url\('(.*)'\).*;/", $styles, $matches)) {
                if ( preg_match("/[ ;]height:/",$styles) || preg_match("/^height:/",$styles))
                    $imgstyles .= "height: 100%;";
                if ( preg_match("/[ ;]width:/",$styles) || preg_match("/^width:/",$styles))
                    $imgstyles .= "width:100%;";
                $styles = preg_replace("/background: url\('(.*)'\)[^;]*;/", "", $styles);
                if (count($matches) > 1) 
                    $img = $matches[1];
                }
            }

        if ( !$this->currentGroup ) {
            $this->jar["pageheaderstop"][] = [
                    "styles" => $styles,
                    "content" => "$text",
                    "image" => "$img",
                    "imagestyles" => "$imgstyles"
                ];
        } else
            $this->currentGroup["pageheaders"][] = [
                    "styles" => $styles,
                    "content" => "$text",
                    "image" => "$img",
                    "imagestyles" => "$imgstyles"
                ];

        return;
    }

    public function formatPageFooter(&$footer)
    {
        $styles = "";
        $text = $footer->text;

        $this->extractStylesAndTextFromString($text, $styles, $footer->attributes, $parent_styleset = false, $grandparent_styleset = false);
        $just = strtolower($footer->getAttribute("justify"));

        $img = false;
        $imgstyles = false;

        if ($styles) {
            $matches = array();
            if (preg_match("/background: url\('(.*)'\).*;/", $styles, $matches)) {
                if ( preg_match("/[ ;]height:/",$styles) || preg_match("/^height:/",$styles))
                    $imgstyles .= "height: 100%;";
                if ( preg_match("/[ ;]width:/",$styles) || preg_match("/^width:/",$styles))
                    $imgstyles .= "width:100%;";
                $styles = preg_replace("/background: url\('(.*)'\)[^;]*;/", "", $styles);
                if (count($matches) > 1) 
                    $img = $matches[1];
            }
        }


        $this->jar["pagefooters"][] = [
            "styles" => $styles,
            "content" => "$text",
            "image" => "$img",
            "imagestyles" => "$imgstyles"
        ];

        return;
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
        $sessionClass = ReporticoSession();

        $open = "";
        if ($hyperlinks["open_in_new"]) {
            $open = " target=\"_blank\"";
        }

        // Work out the drilldown url ...
        $url = $hyperlinks["url"];

        // Add any application specific url params
        if ($hyperlinks["is_drilldown"]) {
            if ($sessionClass::sessionRequestItem('forward_url_get_parameters', '')) {
                $url .= "&" . $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
            }

            // Add drilldown namespace normally specified in frameworks
            $url .= '&clear_session=1&reportico_session_name=NS_drilldown' . $sessionClass::reporticoNamespace();
        }

        if ($hyperlinks["label"] == "<SELF>") {
            $txt = '<a href="' . $url . '"' . $open . '>' . $padstring . '</a>';
        } else {
            $txt = '<a href="' . $url . '"' . $open . '>' . $hyperlinks["label"] . '</a>';
        }

        return $txt;
    }

    public static function extractStylesAndTextFromStringStandalone(&$text, &$styles, &$attributes, $parent_styleset = false, $grandparent_styleset = false)
    {
        $outtext = "";
        $style_arr = ReportHtml::fetchCellStylesStandalone($text);

        $widthset = false;

        if ($style_arr) {
            foreach ($style_arr as $k => $v) {
                if ($k == "width") {
                    $widthset = true;
                }

                if ($k == "background-color") {
                    $styles .= "$k:$v !important;";
                }
                else
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

        return "<div style='$styles'>$text</div>";
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

                if ($k == "background-color") {
                    $styles .= "$k:$v !important;";
                }
                else
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

    public static function fetchCellStylesStandalone(&$tx)
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

        //$tx = $this->reporticoStringToPhp($tx);
        //$tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
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

                        if ( isset($style[2] ))
                            $value .= ":".$style[2];

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
        $tx = Assignment::reporticoMetaSqlCriteria($this->query, $tx, false, true);
        $tx = preg_replace("/<\/*u>/", "", $tx);

        return $styles;
    }

    /*
    *  Draw refresh, back buttons at top of page and also deal with no rows found scenario
    */
    function setPageWidgets()
    {
        $sessionClass = ReporticoSession();

        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $forward .= "&";
        }

        if (preg_match("/\?/", $this->query->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        $title = $this->query->deriveAttribute("ReportTitle", "Unknown");

        //Dont show title in HTML to PDF mode
        $this->jar["title"] = "";

        // In printable html mode dont show back box
        if (!ReporticoUtility::getRequestItem("printable_html")) {

            // Show Go Back Button ( if user is not in "SINGLE REPORT RUN " )
            if (!$this->query->access_mode || ($this->query->access_mode != "REPORTOUTPUT")) {
                $this->jar["buttons"]["back"] = [ 
                    "href" => $this->query->getActionUrl() . $url_join_char . 
                        $forward . 'execute_mode=PREPARE&reportico_session_name=' . $sessionClass::reporticoSessionName(),
                    "class" => "reportico-back-button",
                    "title" => ReporticoLang::templateXlate("GO_BACK")
                ];
            }

            if ($sessionClass::getReporticoSessionParam("show_refresh_button")) {
                $this->jar["buttons"]["refresh"] = [ 
                "href" => $this->query->getActionUrl() . $url_join_char . 
                    $forward . 'refreshReport=1&execute_mode=EXECUTE&reportico_session_name=' . $sessionClass::reporticoSessionName(),
                    "class" => "reportico-refresh-button",
                    "title" => ReporticoLang::templateXlate("GO_REFRESH")
                ];
            }
                $this->jar["buttons"]["print"] = [ 
                        "href" => $this->query->getActionUrl() . $url_join_char . 
                                $forward . 'printReport=1&execute_mode=EXECUTE&reportico_session_name=' . $sessionClass::reporticoSessionName(),
                        "class" => "reportico-print-button",
                        "title" => ReporticoLang::templateXlate("GO_PRINT")
                    ];

        } else {
                $this->jar["buttons"]["print"] = [ 
                        "href" => $this->query->getActionUrl() . $url_join_char . 
                                $forward . 'printReport=1&execute_mode=EXECUTE&reportico_session_name=' . $sessionClass::reporticoSessionName(),
                        "class" => "reportico-print-button",
                        "title" => ReporticoLang::templateXlate("GO_PRINT")
                    ];

        }

        if ($this->line_count < 1) {
            $this->jar["noDataFound"] = ReporticoLang::templateXlate("NO_DATA_FOUND");
        }
    }

    public function &getContent() {
        return $this->jar;
    }

}
