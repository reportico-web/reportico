<?php

namespace Reportico\Engine;

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
    public $engine = false;

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

    public $usage = array(
        "summary" => "Each column in the report output can be configured in a number of ways using the builder.
        Use the column method passing the name of the column and apply one of the following options :-",
        "description" => "",
        "methods" => array(
            //"properties" => array(
                //"description" => "Properties of the column",
                //"parameters" => array( "properties" => "Array of properties")
            //),
            "column" => array(
                "description" => "Name of column (based on name/alias from query results set)",
                "parameters" => array( "column" => "Name of column (based on name/alias from query results set)")
            ),
            "hide" => array(
                "description" => "Hide a Column from the body of the report",
                //"parameters" => array( "column" => "Column to hide")
            ),
            "order" => array(
                "description" => "Set the display order of the column in the report output",
                "parameters" => array( "order" => "The sequence of the column in the report output. Overrides the sequence from the query")
            ),
            "label" => array(
                "description" => "Override the default label for display in the colymn header. Overrides the column name from the query",
                "parameters" => array( "label" => "The label to show in the column header")
            ),
            "columnwidth" => array(
                "description" => "Force default column width in HTML output",
                "parameters" => array( "label" => "The width of the column in HTML output in CSS format eg 5px")
            ),
            //"columnwidthpdf" => array(
                //"description" => "Force default column width in PDF output",
                //"parameters" => array( "label" => "The width of the column in HTML output in CSS format eg 5px")
            //),
            //"groupheaderlabel" => array(
                //"description" => "Force the label to show when the column is displayed in the group header",
                //"parameters" => array( "label" => "The group header label")
            //),
            //"grouptrailerlabel" => array(
                //"description" => "Force the label to show when the column is displayed under a column as a total/average",
                //"parameters" => array( "label" => "The column trailer label")
            //),
            "justify" => array(
                "description" => "Display the column left,right or centered",
                "parameters" => array(
                    "type" => array( "description" => "Alignment/Justification",
                        "options" => array(
                            "left" => "Left justified",
                            "right" => "Right justified",
                            "center" => "Center justified",
                        )
                    )
                )
            ),
        )
    );


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

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        switch ( $method ) {

            case "build":
                $builder = $args[0];
                $colname = $args[1];

                if ( !($column = $builder->engine->getColumn($colname)) ) {
                    die ("Column $colname not found");
                }

                $column->engine = $builder->engine;
                $builder->stepInto("column", $column, "\Reportico\Engine\QueryColumn");
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

        switch ( $method ) {

            case "usage":
                echo $this->builderUsage("column");
                break;

            case "properties":

                foreach ( $args[0] as $key => $val ) {
                    switch ( strtolower($key) ) {

                        case 'grouptrailerlabel':
                            if ( !$val )
                                $val = "BLANK";
                            $this->setAttribute("group_trailer_label", $val);
                            break;

                        case 'groupheaderlabel':
                            if ( !$val )
                                $val = "BLANK";
                            $this->setAttribute("group_header_label", $val);
                            break;

                        case 'columnwidth':
                            $this->setAttribute("ColumnWidthHTML", $val);
                            break;

                        case 'columnwidthpdf':
                            $this->setAttribute("ColumnWidthPDF", $val);
                            break;

                        case 'justify':
                            $this->setAttribute("justify", $val);
                            break;

                        case 'title':
                        case 'label':
                            $this->setAttribute("column_title", $val);
                            break;

                        case 'visible':
                            if ( !$val )
                                $this->setAttribute("column_display", "hide");
                            break;
                    }
                }
                break;

            case "label": $this->setAttribute("column_title", $args[0], true); break;
            case "title": $this->setAttribute("column_title", $args[0], true); break;
            case "justify": $this->setAttribute("justify", $args[0], true); break;
            case "columnwidth": $this->setAttribute("ColumnWidthHTML", $args[0], true); break;
            case "columnwidthpdf": $this->setAttribute("ColumnWidthPDF", $args[0], true); break;
            case "groupheaderlabel": $this->setAttribute("group_header_label", $args[0], true); break;
            case "grouptrailerlabel": $this->setAttribute("group_trailer_label", $args[0], true); break;

            case "sequence":
            case "order":
                $this->engine->setColumnOrder($this->query_name, $args[0], true);
                break;

            case "hide":
                $this->setAttribute("column_display", "hide");
                break;

            case "end":
            default:
                $exitLevel = true;
                break;
        }

        if (!$exitLevel) {
            return $this;
        }

        return false;
    }

    // -----------------------------------------------------------------------------
    // Function : getValueDelimiter
    // -----------------------------------------------------------------------------
    public function getValueDelimiter()
    {
        if (strtoupper($this->column_type) == "CHAR") {
            return ("\"");
        }

        return ("");
    }

}
