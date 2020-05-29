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
        "description" => "Dynamic Grid Control",
        "methods" => array(
            "grid" => array(
                "description" => "Enable Dynamic Grid",
            ),
            "searchable" => array(
                "description" => "Make the grid searchable",
                "parameters" => array( "searchable" => "set to true if grid should be searchable")
            ),
            "sortable" => array(
                "description" => "Make the grid sortable",
                "parameters" => array( "sortable" => "set to true if grid should have sortable columns")
            ),
            "pageable" => array(
                "description" => "Enable paginated output in the grid ",
                "parameters" => array(
                    "pageable" => "set to true if grid should be paginated",
                    "page size" => "The number of rows to show in the paginated grid",
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

                $builder->stepInto("grid", $object, "\Reportico\Engine\ReportGrid");

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
                echo $this->builderUsage("grid");
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
