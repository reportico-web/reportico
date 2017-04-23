<?php

namespace Reportico;

/**
 * Class reportico_group
 *
 * Identifies a report output group and the associated
 * group  header and footers.
 */
class reportico_group extends reportico_object
{
	var 	$group_name;
	var 	$query;
	var 	$group_column;
	var 	$headers = array();
	var 	$trailers = array();
	var 	$trailers_by_column = array();
	var 	$trailer_level_ct = 0;
	var 	$max_level = 0;
	var	$attributes = array(
			"before_header" => "blankline",
			"after_header" => "blankline",
			"before_trailer" => "blankline",
			"after_trailer" => "blankline"
				);

    var $change_triggered = false;

	function __construct($in_name, &$in_query)
	{
		reportico_object::__construct();

		$this->group_name = $in_name;
		$this->query =& $in_query;

		$this->formats = array(
			"before_header" => "blankline",
			"after_header" => "blankline",
			"before_trailer" => "blankline",
			"after_trailer" => "blankline"
				);
	}

	function add_header(&$in_value_column, $in_value_custom = false, $show_in_html, $show_in_pdf)
	{
		$header = array();
        $header["GroupHeaderColumn"] = $in_value_column;
        $header["GroupHeaderCustom"] = $in_value_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
		$this->headers[] = $header;
        
	}			

	function add_trailer($in_trailer_column, &$in_value_column, $in_custom, $show_in_html, $show_in_pdf)
	{
        $trailer = array();
        $trailer["GroupTrailerDisplayColumn"] = $in_trailer_column;
        $trailer["GroupTrailerValueColumn"] = $in_value_column;
        $trailer["GroupTrailerCustom"] = $in_custom;
        $trailer["ShowInHTML"] = $show_in_html;
        $trailer["ShowInPDF"] = $show_in_pdf;
		$this->trailers[] =& $trailer;
		$level = count($this->trailers);
        if ( $this->max_level < $level )
            $this->max_level = $level;
	}			

	function organise_trailers_by_display_column()
	{
        foreach ( $this->trailers as $trailer )
        {
            if ( !isset($this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]] ) )
                $this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]] = array();

            $this->trailers_by_column[$trailer["GroupTrailerDisplayColumn"]][] = $trailer;
        }
        // Calculate number of levels
        $this->max_level = 0;
        foreach ( $this->trailers_by_column as $k => $trailergroup )
        {
            $level = count($trailergroup);
            if ( $this->max_level < $level )
                $this->max_level = $level;
        }
	}			

}