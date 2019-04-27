<?php

namespace Reportico\Engine;

/**
 * Class ReporticoTheme
 *
 * Identifies a report output criteria and the associated
 * criteria  header and footers.
 */
class ReporticoTheme extends ReporticoObject
{
    public $usage = array(
        "description" => "Theme Loader",
        "methods" => array(
            //"disableCaching" => array( "disableCaching" => "true or false", "parameters" => array( "disableCaching" => "When disabled theme edits will be automatically shown") )
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
                $object = new \Reportico\Engine\ReporticoTheme();
                $object->query = $builder->engine;
                $object->value = $builder->engine;

                $builder->stepInto("theme", $object, "\Reportico\Engine\ReportTheme");

                $builder->engine->theme = $args[1];

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

        switch ( strtolower($method) ) {

            case 'disablecacheing':
            case 'disablecaching':
                $val = isset($args[0]) ? $args[0] : true;
                $this->builder->engine->disableThemeCaching = $val;
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
