<?php

namespace Reportico;

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
