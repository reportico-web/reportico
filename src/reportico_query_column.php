<?php

namespace Reportico;



/**
 * Class reportico_query_column
 *
 * Holds presentation and database retrieval information
 * about a data column that mus tbe retrieved from the database
 * or calculated during report execution.
 */
class reportico_query_column extends reportico_object
{
	var $query_name;
	var $table_name;
	var $column_name;
	var $column_type;
	var $column_length;
	var $column_mask;
	var $in_select;
	var $order_style;
	var $column_value;
	var $column_value2;
	var $old_column_value = "*&^%_+-=";
	var $column_value_count;
	var $column_value_sum;
	var $summary_columns;
	var $header_columns;
	var $assoc_column;
	var $reset_flag = false;
	var $criteria_type = "";
	var $criteria_list = "";
	var $required = false;
	var $hidden = false;
	var $match_column = "";
	var $lookup_query;

	var $lookup_return_flag;
	var $lookup_display_flag;
	var $lookup_abbrev_flag;
	var $datasource = false;

	var $minimum = false;
	var $maximum = false;
	var $lineno = 0;
	var $groupvals = array();
	var $average = 0;
	var $sum = 0;
	var $avgct = 0;
	var $avgsum = 0;
	var $median = false;
	var $value_list = array();
    var $output_cell_styles = false;
    var $output_hyperlinks = false;
    var $output_images = false;

	var $attributes = array (
		"column_display" => "show",
		"content_type" => "plain",
		"ColumnStartPDF" => "",
		"justify" => "left",
		"ColumnWidthPDF" => "",
		"ColumnWidthHTML" => "",
		"column_title" => "",
		"tooltip" => "",
		"group_header_label" => "0",
		"group_header_label_xpos" => "",
		"group_header_data_xpos" => "",
		"group_trailer_label" => "0"
		);

	var $values = array (
		"column_value" => "",
		"column_count" => 0,
		"column_sum" => 0
		);

	function set_datasource(&$datasource)
	{ 	
		$this->datasource =& $datasource;
	}


	function __construct
		(
			$query_name = "",
			$table_name = "table_name",
			$column_name = "column_name", 
			$column_type = "string",
			$column_length = 0,
			$column_mask = "MASK",
			$in_select = true
		)
		{
			reportico_object::__construct();

			$this->query_name = $query_name;
			$this->table_name = $table_name;
			$this->column_name = $column_name;
			$this->column_type = $column_type;
			$this->column_length = $column_length;
			$this->column_mask = $column_mask;
			$this->in_select = $in_select;

			if ( !($this->query_name) )
				$this->query_name = $this->column_name;
			
		}			

	// -----------------------------------------------------------------------------
	// Function : get_value_delimiter
	// -----------------------------------------------------------------------------
	function get_value_delimiter()
	{
		if ( strtoupper($this->column_type) == "CHAR" )
			return ("'");

		return("");
	}

}

