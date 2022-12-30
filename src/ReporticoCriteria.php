<?php

namespace Reportico\Engine;

/**
 * Class ReporticoCriteria
 *
 * Identifies a report output criteria and the associated
 * criteria  header and footers.
 */
class ReporticoCriteria extends ReporticoObject
{
    public $usage = array(
        "summary" => "Adds criteria selection widgets to the report front end which are passed to the main report query to return just the results the user has selected",
        "description" => "Gives the ability to select from, for example,  checkboxes, dropdowns, date pickers and build them into the where clause of the query. The name passed to the criteria method is then referred to in the main sql using the {} notation.",
        "methods" => array(
            "criteria" => array(
                "description" => "criteria item",
                "parameters" => array( "name" => "Name of the criteria item to be referred to in query")
            ),
            "title" => array( "description" => "Title/Label of criteria item", "parameters" => array( "title" => "Label to display against criteria item") ),
            "type" => array( "description" => "Type of criteria",
                        "parameters" => array(
                                    "type" => array( "description" => "The type of criteria item",
                                        "options" => array(
                                            "textfield" => "Criteria manually typed into text box",
                                            "lookup" => "Selection comes from database table lookup",
                                            "date" => "Selection comes from date picker",
                                            "daterange" => "Selection comes from date range picker",
                                            "list" => "Selection based on list of value",
                                            "sqlcommand" => "Text box where user can enter free SQL query")
                                )
                            )
                        ),
            "widget" => array( "description" => "Type of browser control to present criteria options",
                "parameters" => array(
                    "type" => array( "description" => "The type of browser control",
                        "options" => array(
                            "noinput" => "Readonly just displays a value - for use with expand selection widget",
                            "textfield" => "Presents a textbox",
                            "dropdown" => "Single selection dropdown list",
                            "multi" => "Multi select dropdown list",
                            "select2single" => "Searchable single selection list box",
                            "select2multiple" => "Searchable multiple selection list box",
                            "checkbox" => "List of checkboxes",
                            "radio" => "List of radio buttons")
                    )
                )
            ),
            "expandWidget" => array( "description" => "Type of browser control to present expanded criteria options",
                "parameters" => array(
                    "type" => array( "description" => "The type of browser control",
                        "options" => array(
                            "noinput" => "Readonly just displays a value - for use with expand selection widget",
                            "textfield" => "Presents a textbox",
                            "dropdown" => "Single selection dropdown list",
                            "multi" => "Multi select dropdown list",
                            "select2single" => "Searchable single selection list box",
                            "select2multiple" => "Searchable multiple selection list box",
                            "checkbox" => "List of checkboxes",
                            "radio" => "List of radio buttons")
                    )
                )
            ),
            "default" => array( "description" => "Set initial values for the criteria. For example you might want to default dates to today or ", "parameters" => array( "default" => "Set initial values for the criteria. Use comma notation for multiselections and use FROM-TO notation for ranges") ),
            "sql" => array( "description" => "For lookup criteria items, the sql used to fetch the criteria selection items", "parameters" => array( "sql" => "The sql to return the relevant items") ),
            "tab" => array( "description" => "Places the criteria widget into a tab group named with the specifed tab label. In reports with many crtieria grouping criteria under tabs can unclutter the screen", "parameters" => array( "tab title" => "The tab name to group the crtieria under.") ),
            "tooltip" => array( "description" => "A help box describing the criteria item which appears when hovering over the widget", "parameters" => array( "tooltip" => "The criteria help text") ),
            "return" => array( "description" => "The column from the criteria sql query that is used to return to the main query for filtering on", "parameters" => array( "column" => "The name of the column to return to the main query") ),
            "display" => array( "description" => "The column from the criteria sql query that the user can select from in the chosen widget",
                "parameters" => array(
                    "widget display column" => "The name of the column to show for selection in the selection widget",
                    "summary column" => "The name of the column to use for displaying the selected item(s) in the criteria widget summary box"
                )
            ),
            "match" => array( "description" => "The column from the criteria sql query that the user can match on in the search box", "parameters" => array( "column" => "The name of the column to match on") ),
        )
    );

    public $query = false;
    public $type = false;
    public $criteriaItem = false;

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
                if (isset($args[1]))  {
                    $object = new \Reportico\Engine\ReporticoCriteria();

                    $object->query = new Reportico();
                    if ( !$builder->engine->datasource ) {
                        $builder->engine->setProjectEnvironment($builder->engine->initial_project, $builder->engine->projects_folder, $builder->engine->admin_projects_folder);
                        $builder->engine->datasource = new ReporticoDataSource($builder->engine->external_connection, $builder->engine->available_connections);
                        $builder->engine->datasource->connect();
                    }
                    $object->criteriaItem = $builder->engine->setCriteriaLookup($args[1], $object->query);
                    $object->criteriaItem->datasource = $builder->engine->datasource;

                    $builder->stepInto("criteria", $object, "\Reportico\Engine\ReportCriteria");
                } else {
                    trigger_error("criteria method requires 1 parameter<BR>".$this->builderMethodUsage($level, "criteria"), E_USER_ERROR);
                }
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

        if (!$this->builderMethodValid("criteria", $method, $args)) {
            return false;
        }

        // PPP echo "<BR>============ crit $method <BR>";
        switch ( strtolower($method) ) {

            case "usage":
                echo $this->builderUsage("criteria");
                break;

            case "title":
                $this->builder->value->criteriaItem->setAttribute("column_title", $args[0]);
                break;

            case "type":
                $this->type = $args[0];
                $this->builder->value->criteriaItem->setCriteriaType(strtoupper($args[0]));
                break;

            case "grouptab":
            case "tabname":
            case "tab":
                $this->builder->value->criteriaItem->setCriteriaDisplayGroup($args[0]);
                break;

            case "hidden":
                $value = isset($args[0]) ? $args[0] : true ;
                $value = $value ? "yes" : "no";
                $this->builder->value->criteriaItem->setCriteriaHidden($value);
                break;

            case "required":
                $value = isset($args[0]) ? $args[0] : true ;
                $value = $value ? "yes" : "no";
                $this->builder->value->criteriaItem->setCriteriaRequired($value);
                break;

            case "return":
                $this->builder->value->query->setLookupReturn($args[0]);
                break;

            case "display":
                $this->builder->value->query->setLookupDisplay($args[0],$args[1]);
                break;

            case "match":
                $this->builder->value->query->setLookupExpandMatch($args[0]);
                break;

            case "input":
                //$this->builder->value->criteriaItem->setLookupExpandMatch($args[0]);
                break;

            case "tooltip":
                $this->builder->value->criteriaItem->setCriteriaHelp($args[0]);
                break;

            case "defaults":
            case "default":
                $this->builder->value->criteriaItem->setCriteriaDefaults($args[0]);
                break;

            case "input":
            case "widget":
                $this->type = $args[0];
                $this->builder->value->criteriaItem->criteria_display = strtoupper($args[0]);
                break;

            case "expandwidget":
                $this->type = $args[0];
                $this->builder->value->criteriaItem->expand_display = strtoupper($args[0]);
                break;

            case "sql":
                if ( $method == "sql" ) {
                    $ct = 0;
                    foreach ($args as $key => $arg) {
                        $parser =  new SqlParser($arg);
                        if ($parser->parse()) {
                            $parser->importIntoQuery($this->query);

                            //if ($this->query->datasource->connect()) {
                            //$p->testQuery($this->query, $sql);
                            //}

                        }
                        $ct++;
                        break;
                    }
                    if ( $ct == 0 ) {
                        echo ("sql requires a parameter"); die;
                    }
                    return $this;
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
        $header["CriteriaHeaderColumn"] = $in_value_column;
        $header["CriteriaHeaderCustom"] = $in_value_custom;
        $header["ShowInHTML"] = $show_in_html;
        $header["ShowInPDF"] = $show_in_pdf;
        $this->headers[] = $header;

    }

    public function addTrailer($in_trailer_column, &$in_value_column, $in_custom, $show_in_html, $show_in_pdf)
    {
        $trailer = array();
        $trailer["CriteriaTrailerDisplayColumn"] = $in_trailer_column;
        $trailer["CriteriaTrailerValueColumn"] = $in_value_column;
        $trailer["CriteriaTrailerCustom"] = $in_custom;
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
            if (!isset($this->trailers_by_column[$trailer["CriteriaTrailerDisplayColumn"]])) {
                $this->trailers_by_column[$trailer["CriteriaTrailerDisplayColumn"]] = array();
            }

            $this->trailers_by_column[$trailer["CriteriaTrailerDisplayColumn"]][] = $trailer;
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
