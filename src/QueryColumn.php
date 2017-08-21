<?php

namespace Reportico;

/**
 * Class QueryColumn
 *
 * Holds presentation and database retrieval information
 * about a data column that mus tbe retrieved from the database
 * or calculated during report execution.
 */
class QueryColumn extends ReporticoObject
{
    public $query_name;
    public $table_name;
    public $column_name;
    public $column_type;
    public $column_length;
    public $column_mask;
    public $in_select;
    public $order_style;
    public $column_value;
    public $column_value2;
    public $old_column_value = "*&^%_+-=";
    public $column_value_count;
    public $column_value_sum;
    public $summary_columns;
    public $header_columns;
    public $assoc_column;
    public $reset_flag = false;
    public $criteria_type = "";
    public $criteria_list = "";
    public $required = false;
    public $hidden = false;
    public $match_column = "";
    public $lookup_query;

    public $lookup_return_flag;
    public $lookup_display_flag;
    public $lookup_abbrev_flag;
    public $datasource = false;

    public $minimum = false;
    public $maximum = false;
    public $lineno = 0;
    public $groupvals = array();
    public $average = 0;
    public $sum = 0;
    public $avgct = 0;
    public $avgsum = 0;
    public $median = false;
    public $value_list = array();
    public $output_cell_styles = false;
    public $output_hyperlinks = false;
    public $output_images = false;

    public $attributes = array(
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
        "group_trailer_label" => "0",
    );

    public $values = array(
        "column_value" => "",
        "column_count" => 0,
        "column_sum" => 0,
    );

    public function setDatasource(&$datasource)
    {
        $this->datasource = &$datasource;
    }

    public function __construct
    (
        $query_name = "",
        $table_name = "table_name",
        $column_name = "column_name",
        $column_type = "string",
        $column_length = 0,
        $column_mask = "MASK",
        $in_select = true
    ) {
        ReporticoObject::__construct();

        $this->query_name = $query_name;
        $this->table_name = $table_name;
        $this->column_name = $column_name;
        $this->column_type = $column_type;
        $this->column_length = $column_length;
        $this->column_mask = $column_mask;
        $this->in_select = $in_select;

        if (!($this->query_name)) {
            $this->query_name = $this->column_name;
        }

    }

    // -----------------------------------------------------------------------------
    // Function : getValueDelimiter
    // -----------------------------------------------------------------------------
    public function getValueDelimiter()
    {
        if (strtoupper($this->column_type) == "CHAR") {
            return ("'");
        }

        return ("");
    }

}
