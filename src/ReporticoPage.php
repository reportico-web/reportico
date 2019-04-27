<?php

namespace Reportico\Engine;

/**
 * Class ReporticoPage
 *
 * Identifies a report output criteria and the associated
 * criteria  header and footers.
 */
class ReporticoPage extends ReporticoObject
{
    public $usage = array(
        "description" => "Page Formatting",
        "methods" => array(
            "orientation" => array( "description" => "Landscape or Portrait?",
                "parameters" => array(
                    "type" => array( "description" => "Orientation Type",
                        "options" => array(
                            "Portrait" => "Portrait",
                            "Landscape" => "Landscape",
                        )
                    )
                )
            ),
            "pagetitledisplay" => array( "description" => "Landscape or Portrait?",
                "parameters" => array(
                    "type" => array( "description" => "Orientation Type",
                        "options" => array(
                            "Off" => "No Title",
                            "TopOfFirstPage" => "Top of the first page only",
                            "TopOfAllPages" => "Top of all pages",
                        )
                    )
                )
            ),
            "layout" => array( "description" => "Layout",
                "parameters" => array(
                    "type" => array( "description" => "Layout",
                        "options" => array(
                            "Table" => "Tabular Format",
                            "Form" => "One section per row",
                        )
                    )
                )
            ),
            "paginate" => array( "paginate" => "Whether to generate continuous output or with page throws",
                "parameters" => array(
                    "type" => array( "description" => "The type of criteria item",
                        "options" => array(
                            1 => "Pagination On",
                            0 => "Continuous Output"
                        )
                    )
                )
            ),
            "size" => array( "description" => "Type of criteria",
                        "parameters" => array(
                                    "type" => array( "description" => "The type of criteria item",
                                        "options" => array(
                                            "A5" => "A5",
                                            "A4" => "A4",
                                            "A3" => "A3",
                                            "US-Letter" => "US-Letter",
                                )
                            )
                        )
            ),
        ));

    public $query = false;

    public function __construct()
    {
        ReporticoObject::__construct();
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        switch ( $method ) {

            case "build":
                $builder = $args[0];
                $object = new \Reportico\Engine\ReporticoPage();
                $object->query = $builder->engine;
                $object->value = $builder->engine;

                $builder->stepInto("page", $object, "\Reportico\Engine\ReportPage");

                $object->builder = $builder;
                return $builder;
                break;

        }
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args)
    {
        $exitLevel = false;

        if (!$this->builderMethodValid("page", $method, $args)) {
            return false;
        }

        //echo "<BR>============ page $method <BR>";
        switch ( strtolower($method) ) {

            case "size":
                $this->builder->engine->setAttribute("PageSize", strtoupper($args[0]));
                break;

            case "orientation":
                $this->builder->engine->setAttribute("PageOrientation", $args[0]);
                break;

            case "leftmargin":
                $this->builder->engine->setAttribute("LeftMargin", $args[0]);
                break;

            case "rightmargin":
                $this->builder->engine->setAttribute("RightMargin", $args[0]);
                break;

            case "topmargin":
                $this->builder->engine->setAttribute("TopMargin", $args[0]);
                break;

            case "bottommargin":
                $this->builder->engine->setAttribute("BottomMargin", $args[0]);
                break;

            case "pagetitledisplay":
                $this->builder->engine->setAttribute("PageTitleDisplay", $args[0]);
                break;

            case "layout":
                $this->builder->engine->setAttribute("PageLayout", $args[0]);
                break;

            case "pdfzoomfactor":
                $this->builder->engine->setAttribute("PdfZoomFactor", $args[0]);
                break;

            case "paginate":
                $paginate = isset($args[0]) ? $args[0] : 1;
                if ( $paginate )
                    $this->builder->engine->setAttribute("AutoPaginate", "HTML+PDF");
                else
                    $this->builder->engine->setAttribute("AutoPaginate", "None");
                break;

            case "header":
                $header = $args[0];
                if (isset($args[1])) {
                    $styles = $args[1];
                    if (is_array($styles)) {
                        $text = "";
                        foreach ( $styles as $k => $v )
                            $text .= "$k: $v;";
                        $styles = $text;
                    }
                    $header = $header . "{STYLE $styles}";
                }

                $page = $this->builder->engine->createPageHeader(false, 5, $header);
                $page->setAttribute("ShowInHTML", "yes");
                $page->setAttribute("ShowInPDF", "yes");
                break;

            case "footer":
                $header = $args[0];
                if (isset($args[1])) {
                    $styles = $args[1];
                    if (is_array($styles)) {
                        $text = "";
                        foreach ( $styles as $k => $v )
                            $text .= "$k: $v;";
                        $styles = $text;
                    }
                    $header = $header . "{STYLE $styles}";
                }

                $page = $this->builder->engine->createPageFooter(false, 5, $header);
                $page->setAttribute("ShowInHTML", "yes");
                $page->setAttribute("ShowInPDF", "yes");
                break;

            case "hide":
            case "show":
                $firstTime = true;
                foreach ($args as $arg) {
                    $section = "initial_show_$arg";
                    if ( isset($this->builder->engine->$section) ){
                        if ( $firstTime && $method == "show" ) {
                            $this->builder->engine->initial_show_column_headers = "hide";
                            $this->builder->engine->initial_show_criteria = "hide";
                            $this->builder->engine->initial_show_detail = "hide";
                            $this->builder->engine->initial_show_group_headers = "hide";
                            $this->builder->engine->initial_show_group_trailers = "hide";
                            $this->builder->engine->initial_show_graph = "hide";
                            $firstTime = false;
                        }
                        echo "$section -> $method<BR>";
                        $this->builder->engine->$section = $method;
                    }
                }
                echo $this->builder->engine->initial_show_column_headers . "<BR>";
                echo $this->builder->engine->initial_show_criteria . "<BR>";
                echo $this->builder->engine->initial_show_detail . "<BR>";
                echo $this->builder->engine->initial_show_group_headers . "<BR>";
                echo $this->builder->engine->initial_show_group_trailers . "<BR>";
                echo $this->builder->engine->initial_show_graph . "<BR>";
                break;

            case "end":
            default:
                $this->levelRef = false;
                $exitLevel = true;
                break;
        }

        if (!$exitLevel)
            return $this;

        return false;

    }

    public function addHeader(&$in_value_column, $in_value_custom = false, $show_in_html, $show_in_pdf)
    {
        $header = array();
        $header["PageHeaderColumn"] = $in_value_column;
        $header["PageHeaderCustom"] = $in_value_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
        $this->headers[] = $header;

    }

    public function addTrailer($in_trailer_column, &$in_value_column, $in_custom, $show_in_html, $show_in_pdf)
    {
        $trailer = array();
        $trailer["PageTrailerDisplayColumn"] = $in_trailer_column;
        $trailer["PageTrailerValueColumn"] = $in_value_column;
        $trailer["PageTrailerCustom"] = $in_custom;
        $trailer["ShowInHTML"] = $show_in_html;
        $trailer["ShowInPDF"] = $show_in_pdf;
        $this->trailers[] = &$trailer;
        $level = count($this->trailers);
        if ($this->max_level < $level) {
            $this->max_level = $level;
        }

    }

    public function organiseTrailersByDisplayColumn()
    {
        foreach ($this->trailers as $trailer) {
            if (!isset($this->trailers_by_column[$trailer["PageTrailerDisplayColumn"]])) {
                $this->trailers_by_column[$trailer["PageTrailerDisplayColumn"]] = array();
            }

            $this->trailers_by_column[$trailer["PageTrailerDisplayColumn"]][] = $trailer;
        }
        // Calculate number of levels
        $this->max_level = 0;
        foreach ($this->trailers_by_column as $k => $trailergroup) {
            $level = count($trailergroup);
            if ($this->max_level < $level) {
                $this->max_level = $level;
            }

        }
    }

}
