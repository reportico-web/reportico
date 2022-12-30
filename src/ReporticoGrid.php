<?php

namespace Reportico\Engine;

/**
 * Class ReporticoGrid
 *
 * Identifies a report output criteria and the associated
 * criteria  header and footers.
 */
class ReporticoGrid extends ReporticoObject
{
    public $usage = array(
        "summary" => "Generates the report as a searchable, sortable, paging table. ",
        "description" => "Where report output is split using the group option each table group is converted to its own dynamic table. The searchable, sortable and pageable parameters are on by default so do not need to be specified unless you are turning them off.",
        "methods" => array(
            "dynamicTable" => array(
                "description" => "Enable Dynamic Grid",
            ),
            "searchable" => array(
                "description" => "Make the table searchable",
                "parameters" => array( "searchable" => "set to true if table should be searchable")
            ),
            "sortable" => array(
                "description" => "Make the table sortable",
                "parameters" => array( "sortable" => "set to true if table should have sortable columns")
            ),
            "pageable" => array(
                "description" => "Enable paginated output in the table ",
                "parameters" => array(
                    "pageable" => "set to true if table should be paginated",
                    "page size" => "The number of rows to show in the paginated table",
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
                $object = new \Reportico\Engine\ReporticoGrid();
                $object->query = $builder->engine;
                $object->value = $builder->engine;

                $builder->stepInto("dynamicTable", $object, "\Reportico\Engine\ReportGrid");

                $builder->engine->setAttribute ("gridDisplay", "show" );
                //$builder->engine->setAttribute ("AutoPaginate", "NONE" );

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

            case "usage":
                $asString = isset ($args[0]) ? $args[0] : false;
                if ( $asString ) {
                    return $this->builderUsage("dynamicTable");
                }
                else
                    echo $this->builderUsage("dynamicTable");
                break;

            case 'sortable':
                $val = isset($args[0]) ? $args[0] : true;
                if ( $val )
                    $this->builder->engine->setAttribute ("gridSortable", "yes" );
                else
                    $this->builder->engine->setAttribute ("gridSortable", "no" );
                break;

            case 'searchable':
                $val = isset($args[0]) ? $args[0] : true;
                if ( $val )
                    $this->builder->engine->setAttribute ("gridSearchable", "yes" );
                else
                    $this->builder->engine->setAttribute ("gridSearchable", "no" );
                break;

            case 'pageable':
            case 'paginated':
            case 'paging':
                $val = isset($args[0]) ? $args[0] : true;
                if ( $val )
                    $this->builder->engine->setAttribute ("gridPageable", "yes" );
                else
                    $this->builder->engine->setAttribute ("gridPageable", "no" );
                $val = isset($args[1]) ? $args[1] : false;
                if ( $val )
                    $this->builder->engine->setAttribute ("gridPageSize", $val );
                break;

            case 'pagesize':
                $this->builder->engine->setAttribute ("gridPageable", "yes" );
                $val = isset($args[0]) ? $args[0] : true;
                if ( $val ) {
                    $this->builder->engine->setAttribute ("gridPageSize", $val );
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

}
