<?php

namespace Reportico\Engine;

/**
 * Class ReporticoGroup
 *
 * Identifies a report output group and the associated
 * group  header and footers.
 */
class ReporticoGroup extends ReporticoObject
{
    public $group_name;
    public $query;
    public $group_column;
    public $headers = array();
    public $trailers = array();
    public $trailers_by_column = array();
    public $trailer_level_ct = 0;
    public $max_level = 0;
    public $attributes = array(
        "before_header" => "blankline",
        "after_header" => "blankline",
        "before_trailer" => "blankline",
        "after_trailer" => "blankline",
    );

    public $usage = array(
        "description" => "",
        "methods" => array(
            "on" => array(
                "description" => "Group By a Column",
                "parameters" => array( "column" => "column name to group on")
            )
        )
    );

    public $change_triggered = false;

    public function __construct($in_name, &$in_query)
    {
        ReporticoObject::__construct();

        $this->group_name = $in_name;
        $this->query = &$in_query;

        $this->formats = array(
            "before_header" => "blankline",
            "after_header" => "blankline",
            "before_trailer" => "blankline",
            "after_trailer" => "blankline",
        );
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
                if (isset($args[1]))  {
                    $group = new \Reportico\Engine\ReporticoGroup("temp", $builder->engine);
                    $builder->engine->createGroup($args[1], $builder->engine);
                    $group->levelRef = $args[1];
                } else {
                    $group = new \Reportico\Engine\ReporticoGroup("temp", $builder->engine);
                }
                $group->builder = $builder;
                $builder->stepInto("group", $group, "\Reportico\Engine\ReportGroup");
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

        if (!$this->builderMethodValid("group", $method, $args)) {
            return false;
        }

        switch ( $method ) {

            case "on":
                $this->levelRef = $args[0];
                $this->builder->engine->createGroup($this->levelRef, $this->builder->value);
                break;

            case "throwPageBefore":
                if (($grp = ReporticoUtility::getGroupColumn($this->levelRef, $this->builder->engine->groups))) {
                    $grp->setAttribute("before_header", "newpage");
                    $grp->setFormat("before_header", "newpage");
                }
                break;

            case "header":
                $headerColumn = $args[0];
                if ( $headerColumn ) {
                    //$x = new Reportico();
                    $this->builder->engine->createGroupHeader($this->levelRef, $headerColumn);
                    $this->builder->engine->getColumn($this->levelRef)->setAttribute("column_display", "hide");
                }
                break;

            case "customHeader":
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

                if ( $header ) {
                    //$x = new Reportico();
                    $this->builder->engine->createGroupHeader($this->levelRef, "", $header);
                }
                break;

            case "customTrailer":
                $trailer = $args[0];
                if (isset($args[1])) {
                    $styles = $args[1];
                    if (is_array($styles)) {
                        $text = "";
                        foreach ( $styles as $k => $v )
                            $text .= "$k: $v;";
                        $styles = $text;
                    }
                    $trailer = $trailer . "{STYLE $styles}";
                }

                if ( $trailer ) {
                    //$x = new Reportico();
                    $this->builder->engine->createGroupTrailer($this->levelRef, "", "", $trailer);
                }
                break;


            case "trailer":
            case "below":
                $this->builder->buffer[$method] = $args[0];
                if ( isset($this->builder->buffer["trailer"]) && isset($this->builder->buffer["below"]) ) {
                    $this->builder->engine->createGroupTrailer($this->levelRef,
                        $this->builder->buffer["below"],
                        $this->builder->buffer["trailer"],
                        false,
                        "yes",
                        "yes"
                    );
                    $this->builder->engine->getColumn($this->builder->buffer["trailer"])->setAttribute("column_display", "hide");
                    $this->builder->buffer = [];
                }
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
        $header["GroupHeaderColumn"] = $in_value_column;
        $header["GroupHeaderCustom"] = $in_value_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
        $this->headers[] = $header;

    }

    public function addTrailer($in_trailer_column, &$in_value_column, $in_custom, $show_in_html, $show_in_pdf)
    {
        $trailer = array();
        $trailer["GroupTrailerDisplayColumn"] = $in_trailer_column;
        $trailer["GroupTrailerValueColumn"] = $in_value_column;
        $trailer["GroupTrailerCustom"] = $in_custom;
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
            if (!isset($this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]])) {
                $this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]] = array();
            }

            $this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]][] = $trailer;
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
