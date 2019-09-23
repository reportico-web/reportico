<?php

namespace Reportico\Engine;


define('PIPE_VALUE', '__pipe-' . uniqid());

if ( !function_exists("reporticoPipe") ) {

// Instantiate Reportico
function reporticoPipe($value) {
        return new \Reportico\Engine\Builder($value);
}

}

$old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");


class Builder implements \ArrayAccess, \Iterator, \Serializable
{
    public $value;
    public $engine;
    public $buffer = [];
    private $position = 0;
    private $levelRef = false;
    public $level = "base";
    private $builderClass = false;
    private $objects = [];
    public $store = [];

    public function __construct($value)
    {
        $this->engine = $value;
        $this->stepInto("base", $value);
    }
    
    /*
     * Instantiate an instance of Reportico for building with
     */
    static public function build($session = "") {

        $engine = new Reportico();
        $engine->session($session);
        $engine->initialize_on_execute = false;
        $engine->report_from_builder = true;
        $engine->url_path_to_assets = "assets";
        $engine->url_path_to_templates = "themes";
        $engine->report_from_builder_first_call = true;
        return new \Reportico\Engine\Builder($engine);

    }

    /*
     * Fetch something from the store
     */
    public function retrieve($param) {

        return isset($this->store[$param]) ? $this->store[$param] : false;

    }

    /*
     * Add an object to the method chain stack
     */
    public function stepInto($level, $object, $builderClass = false) {
       //echo "<BR>Step into $level -> $builderClass<BR>";
       array_push($this->objects,["level" => $level, "builderClass" => $builderClass, "value" => $object ]);
       $this->level = $level;
       $this->value = $object;
       $this->builderClass = $builderClass;
    }

    /*
     * Remove an object from the method chain stack
     */
    public function stepOut() {
        //echo "<BR>Step out ".end($this->objects)["level"]." -> ".end($this->objects)["builderClass"]."<BR>";
        $this->value = array_pop($this->objects);
        $this->level = end($this->objects)["level"];
        $this->value = end($this->objects)["value"];
        $this->builderClass = end($this->objects)["builderClass"];
        $this->buffer = [];
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args) {

        $steppedOutOf = false;

        if ( is_object($this->value) ) {

        if ( $this->level == "base" && $method == "properties" ) {

            foreach ($args as $key => $arg) {
                if ( is_array($arg) ) {
                    foreach ( $arg as $key2 => $arg2 ) {
                        if (property_exists($this->value, $key2))
                            $this->value->$key2 = $arg2;
                        else {
                            trigger_error("Property '$key' doesn't exist", E_USER_ERROR);
                        }
                    }
                } else {
                    if (property_exists($this->value, $key))
                        $this->value->$key = $arg;
                    else
                        trigger_error("Property '$key' doesn't exist", E_USER_ERROR);
                }
            }

            return $this;

        }

        $methodObjectMap = [
            "expression" => "\Reportico\Engine\Assignment",
            "element" => "\Reportico\Engine\Assignment",
            "section" => "\Reportico\Engine\Assignment",
            "group" => "\Reportico\Engine\ReporticoGroup",
            "criteria" => "\Reportico\Engine\ReporticoCriteria",
            "chart" => "\Reportico\Engine\ReporticoChart",
            "page" => "\Reportico\Engine\ReporticoPage",
            "column" => "\Reportico\Engine\QueryColumn",
            "grid" => "\Reportico\Engine\ReporticoGrid",
            "datasource" => "\Reportico\Engine\ReporticoDataSource",
            "customCode" => "\Reportico\Engine\ReporticoCustomCode",
            "theme" => "\Reportico\Engine\ReporticoTheme",
            ];

        // We have passed a method relating to another class
        if (isset($methodObjectMap[$method])) {

            //echo "Level $this->level vd $method<BR>";
            if ( isset($methodObjectMap[$this->level])) {
               $steppedOutOf = end($this->objects)["level"];
                $this->stepOut();
            }
            $this->level = $method;
            $class = $methodObjectMap[$method];
            //echo "$class $this->level XXX<BR>";
            $class::build($this, ...$args);
            return $this;
        }

        if ( $this->builderClass ) {
            if ( $this->value->$method(...$args) ){
                return ($this);
            }
            else {
                $steppedOutOf = end($this->objects)["level"];
                $this->stepOut();
                $exitLevel = true;
            }
        }

        if ( $method == "page" ) {
            $this->value->setAttribute ("AutoPaginate", "PDF+HTML" );
        }

        if ( $method == "save" ) {

            $this->value->setProjectEnvironment($this->value->initial_project, $this->value->projects_folder, $this->value->admin_projects_folder);
            $this->value->xmlout = new XmlWriter($this->value);
            $this->value->xmlout->prepareXmlData();
            $this->value->xmlout->writeFile($args[0]);
            return $this;
        }

        if ( $method == "newSession" ) {
            $this->value->clear_reportico_session = 1;
            $arg = isset ($args[0]) ? $args[0] : "";
            $this->value->session ($arg);
            return $this;
        }

            if ( strtolower($method) == "page" ) {

                $this->value->setAttribute ("gridDisplay", "show" );
                $this->value->setAttribute ("AutoPaginate", "NONE" );

                $attributes = $args[0];

                foreach ( $attributes as $key => $val ) {
                    switch ( $key ) {

                        case 'sortable':
                            if ( $val )
                                $this->value->setAttribute ("gridSortable", "yes" );
                            else
                                $this->value->setAttribute ("gridSortable", "no" );
                            break;

                        case 'searchable':
                            if ( $val )
                                $this->value->setAttribute ("gridSearchable", "yes" );
                            else
                                $this->value->setAttribute ("gridSearchable", "no" );
                            break;

                        case 'paginated':
                            if ( $val )
                                $this->value->setAttribute ("gridPageable", "yes" );
                            else
                                $this->value->setAttribute ("gridPageable", "no" );
                            break;

                        case 'pageSize':
                            if ( $val )
                                $this->value->setAttribute ("gridPageSize", $val );
                            break;

                    }
                }
                return $this;
            }
        /*
        if ( $method == "column" ) {

                $colname = $args[0];
                $attributes = $args[1];

                if ( !$this->value->getColumn($colname) ) {
                    die ("Column $colname not found");
                }

                foreach ( $attributes as $key => $val ) {
                    switch ( $key ) {

                        case 'justify':
                            $this->value->getColumn($colname)->setAttribute("justify", $val);
                            break;

                        case 'label':
                            $this->value->getColumn($colname)->setAttribute("column_title", $val);
                            break;

                        case 'visible':
                            if ( !$val )
                                $this->value->getColumn($colname)->setAttribute("column_display", "hide");
                            break;
                    }
                }
                return $this;
        }
        */

            if ( isset($this->value->usage["attributes"][$method] )) {
                    $this->value->setAttribute ($this->value->usage["attributes"][$method], $args[0] );
                    return $this;
            }

            if ( $method == "menu" ) {
                    $this->value->initial_execute_mode = "MENU";
                    $method = "execute";
            }

            if ( $method == "to" ) {
                    $this->value->initial_output_format = $args[0];
                    return $this;
            }

            // Process sql
            if ( $method == "sql" ) {
                $ct = 0;
                foreach ($args as $key => $arg) {
                    $parser =  new SqlParser($arg);
                    if ($parser->parse()) {
                        $parser->importIntoQuery($this->value);
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

            // Call a method available in this builder instance
            if (method_exists($this, $method)) {

            }
            
            // Call a method available in the Reportico Instance
            if (method_exists($this->value, $method)) {
                foreach ($args as $key => $arg) {
                    if ($arg === PIPE_VALUE) {
                        $args[$key] = $this->value;
                    }
                }
                
                $this->value->$method(...$args);
                return $this;
            }

            echo "FAIL $method<BR>";
            if ($steppedOutOf)
                trigger_error("Method '$method' specified during construction of $steppedOutOf().<BR>This method is not known for $steppedOutOf() or as a code builder method.", E_USER_ERROR);
            else
                trigger_error("Reportico builder method '$method' doesn't exist", E_USER_ERROR);
            return $this;
        }

        if (function_exists($method)) {
            foreach ($args as $key => $arg) {
                if ($arg === PIPE_VALUE) {
                    $args[$key] = $this->value;
                }
            }
            
            $this->value = $method(...$args);
            return $this;
        }

        if ($steppedOutOf)
            trigger_error("Method '$method' specified during construction of $steppedOutOf().<BR>This method is not known for $steppedOutOf() or as a code builder method.", E_USER_ERROR);
        else
            trigger_error("Reportico builder method '$method' doesn't exist", E_USER_ERROR);
        return $this;
    }
    
    public function __get($name)
    {
        return $this->value;
    }
    
    public function __toString()
    {
        return "String";
        //return $this->value;
    }
    
    public function pipe($value)
    {
        if (is_callable($value) and $value instanceof \Closure) {
            $this->value = $value($this->value);
        }
        
        return $this;
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->value[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->value[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->value[$offset]) ? $this->value[$offset] : null;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function current() {
        return $this->value[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function valid() {
        return isset($this->value[$this->position]);
    }
    
    public function serialize() {
        return serialize($this->value);
    }
    
    public function unserialize($value) {
        $this->value = unserialize($value);
    }
}
