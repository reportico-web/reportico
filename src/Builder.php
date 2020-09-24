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


class Builder extends ReporticoObject implements \ArrayAccess, \Iterator, \Serializable
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

        // Defaults for builder initiated access
        $this->engine->access_mode = "guest";

        $this->stepInto("base", $value);
    }

    public $usage = array(
        "description" => "Reportico core methods",
        "methods" => array(
            "project" => array(
                "description" => "Select an existing project to work in.",
                "parameters" => array(
                    "project" => array( "description" => "The name of the project to work in." ),
                )
            ),
            "load" => array(
                "description" => "Select an existing report file to work in. Requires the project to have been selected",
                "parameters" => array(
                    "file name" => array( "description" => "The name of the report to run" ),
                )
            ),
            "run" => array(
                "description" => "Run the report and generate output",
            ),
            "prepare" => array(
                "description" => "Shows the criteria selection and configuration screen for a report",
            ),
            "menu" => array(
                "description" => "Display a project menu where reports can be selected from (only useful when a project is specified)",
            ),
            "relay" => array(
                "description" => "Pass on parameter values onto Reportico, so they can be picked up in the report query",
            ),
            "relayCriteria" => array(
                "description" => "Pass on parameter values as filter values for existing Reportico criteria items",
            ),
            "pdfEngine" => array(
                "description" => "",
                "parameters" => array(
                    "engine" => array( 
                        "description" => "The name of the report to run",
                        "options" => array(
                            "chromium" => "Use the headleass chromium browser to generate attractive reports as they look in the browser",
                            "tcpdf" => "Default simple PDF generator"
                        )
                    )
                )
            ),
            "pdfDownloadMethod" => array(
                "description" => "",
                "parameters" => array(
                    "engine" => array( "description" => "How the browser provides the pdf output as a download or inline in the browser",
                        "options" => array(
                            "inline" => "Renders the PDF within a new browser tab",
                            "same_window" => "The default option. Downloads to the local system from the window the report was called from",
                            "new_window" => "Opens a new browser window to process the PDF and then download to local machine"
                        ))
                )
            ),
            "accessLevel" => array(
                "description" => "What level of access is granted to the user",
                "parameters" => array(
                    "type" => array( "description" => "What level of access is granted to the user",
                        "options" => array(
                            "admin" => "Full design mode, ability to edit reports",
                            "all-projects" => "Gives access to all the report projects and to allow execution of all reports in those projects",
                            "project" => "Gives access to all the reports in a single project - the project must be specified",
                            "report" => "Gives access to a single report in a one project - thre project and report name must be specified",
                            "report-output" => "Only runs the specified report without any access to the criteria entry screen",
                            "design-fiddle" => "Allows modifuying report parameters without the ability to save",
                            "EXECUTE" => "Run the report and show the output",
                        )
                    )
                )
            ),
            "hideSections" => array(
                "description" => "Turn off elements of the report output",
                "parameters" => array(
                    "type" => array( "description" => "The report sections to hide from the report output",
                        "options" => array(
                            "criteria" => "Hide the selected criteria summary section",
                            "detail" => "Hide the main report body so you are just left with page headers/trailers, group headers/trailers and charts",
                            "charts" => "Hide charts from the report",
                            "groupheaders" => "Hide the group header labels at the top of each report sections",
                            "grouptrailers" => "Hide the column trailers and group trailers",
                            "columnheaders" => "Hide the header labels at the top of the report body",
                        )
                    )
                )
            ),
            "render" => array(
                "description" => "Displays reportico to the user  specifying whether to show a project menu, report criteria screen or report output",
                "parameters" => array(
                            "type" => array( "description" => "The type of output to show",
                                "options" => array(
                                    "MENU" => "Show a report project menu",
                                    "PREPARE" => "Show a report screen",
                                    "EXECUTE" => "Run the report and show the output",
                                )
                        )
                    )
                ),
            "to" => array(
                "description" => "Report output format ( HTML, CSV or PDF )",
                "parameters" => array(
                    "type" => array( "description" => "The output to render the report in",
                        "options" => array(
                            "HTML" => "(the default))Render and HTML report",
                            "PDF" => "PDF output",
                            "CSV" => "CSV output",
                        )
                    )
                )
            ),
            "save" => array(
                "description" => "When using the framework builder you can save the resulting report definition as anamed report in the slected project",
                "parameters" => array(
                    "type" => array( "file name" => "The name of the xml file to save to (withut the xml extension)" )
                )
            ),
            "newSession" => array(
                "description" => "By default when refreshed in the browser, the existing user selections will be maintained, use the newSession method to restart the report from scratch",
            ),
        ));

    /*
     * Instantiate an instance of Reportico for building with
     */
    static public function build($session = "") {

        $builderClass = \Reportico\Engine\Reportico::ReporticoBuilder();
        return $builderClass::_build($session);

    }

    /*
     * Instantiate an instance of Reportico Builder in standalone mode
     */
    static function _build($session = "") {
        $engine = new Reportico();
        if (!$session)
            $engine->clear_reportico_session = 1;
        $engine->session($session);
        $engine->initialize_on_execute = false;
        $engine->report_from_builder = true;
        //$engine->url_path_to_assets = "assets";
        //$engine->url_path_to_templates = "themes";
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
            "dynamicTable" => "\Reportico\Engine\ReporticoGrid",
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
            if ( $method == "usage" ){
                return $this->value->$method(...$args);
            }
            else if ( $this->value->$method(...$args) ){
                return ($this);
            }
            else {
                $steppedOutOf = end($this->objects)["level"];
                $this->stepOut();
                $exitLevel = true;
            }
        }

        //if ( $method == "page" ) {
            //$this->value->setAttribute ("AutoPaginate", "PDF+HTML" );
        //}
        if ( $method == "fullusage" ) {
            echo $this->builderUsage("base");
            foreach ($methodObjectMap as $classmethod => $class){
                //echo $classmethod."<BR>";
                $classObject = new $class();
                $classObject->usage("$class");
            }
            die;
        }

        if ( $method == "usage" ) {

            $asString = isset ($args[0]) ? $args[0] : false;
            if ( $asString )
                return $this->builderUsage("reportico");
            else
                echo $this->builderUsage("reportico");
            
            return $this;
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

        // Relay user parameters
        if ( $method == "relay" ) {
            if ( is_array($args[0]) ){
                foreach($args[0] as $key => $value){
                    $this->value->user_parameters[$key] = $value;
                }
            }
            else
            {
               if ( isset($args[0]) && isset($args[1]) ){
                    $this->value->user_parameters[$args[0]] = $args[1];
               } else
                    trigger_error("relay: Must be array of key value pairs or two parameters - one for key and one for value", E_USER_ERROR);
            }
            return $this;
        }

        // Relay user parameters
        if ( $method == "relayCriteria" ) {
            if ( is_array($args[0]) ){
                foreach($args[0] as $key => $value){
                    $_REQUEST["MANUAL_$key"] = $value;
                }
            } else {
               if ( isset($args[0]) && isset($args[1]) ){
                    $_REQUEST["MANUAL_".$args[0]] = $args[1];
               } else
                    trigger_error("relay: Must be array of key value pairs or two parameters - one for key and one for value", E_USER_ERROR);
            }
            return $this;
        }


        if ( $method == "hideSection" || $method == "hideSections" ) {
            if ( !$args ) {
                trigger_error("hideSections: Section not provided - provide one of criteria, detail, graph", E_USER_ERROR);
                return $this;
            }
            foreach ( $args as $key => $section ) {
                switch ( strtolower($section) ) {

                    case "criteria":
                        $this->value->initial_show_criteria = "hide";
                        break;

                    case "detail":
                        $this->value->initial_show_detail = "hide";
                        break;

                    case "charts":
                        $this->value->initial_show_graph = "hide";
                        break;

                    case "groupheaders":
                        $this->value->initial_show_group_headers = "hide";
                        break;

                    case "grouptrailers":
                        $this->value->initial_show_group_trailers = "hide";
                        break;

                    case "columnheaders":
                        $this->value->initial_show_column_headers = "hide";
                        break;

                    default:
                        trigger_error("hideSections: Section '$section' doesn't exist valid options are criteria, detail, graph", E_USER_ERROR);

                }
            }
            return $this;
        }

        if ( strtolower($method) == "grid" || strtolower($method) == "dynamictable" ) {

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

            if ( isset($this->value->usage["attributes"][$method] )) {
                    $this->value->setAttribute ($this->value->usage["attributes"][$method], $args[0] );
                    return $this;
            }

            if ( $method == "dropdownMenu" || $method == "dropdown_menu") {
                $this->value->dropdown_menu = $args[0];
                return $this;
            }

            if ( $method == "project" ) {
                $this->value->initial_project = $args[0];
                return $this;
            }

            if ( $method == "password" ) {
                $this->value->initial_project_password = $args[0];
                return $this;
            }

            if ( strtolower($method) == "pdfengine" ) {
                $this->value->pdf_engine = $args[0];
                return $this;
            }

            if ( strtolower($method) == "pdfdownloadmethod" ) {
                $mode = $args[0];
                if ( strtoupper($mode) == "INLINE" ) {
                    $this->value->pdf_delivery_mode = "INLINE";
                } 
                else if ( preg_match("/SAME/", strtoupper($mode) ) ) {
                    $this->value->pdf_delivery_mode = "DOWNLOAD_SAME_WINDOW";
                }
                else if ( preg_match("/NEW/", strtoupper($mode) ) ) {
                    $this->value->pdf_delivery_mode = "DOWNLOAD_NEW_WINDOW";
                }
                return $this;
            }

            if ( $method == "load" ) {
                $this->value->initial_report = $args[0];
                //echo "HAVE A REPORT {$this->value->initial_report} <BR>";
                return $this;
            }

            if ( $method == "accessLevel" ) {
                $this->value->access_mode = $args[0];
                $this->value->initial_role = $args[0];
                return $this;
            }

            if ( $method == "prepare" ) {
                $this->value->initial_execute_mode = "PREPARE";
                $this->value->render("PREPARE");
                $method = "execute";
                return $this;
            }

            if ( $method == "run" || $method == "execute" ) {
                $this->value->initial_execute_mode = "EXECUTE";
                $this->value->access_mode = "REPORTOUTPUT";
                $this->value->render("EXECUTE");
                $method = "execute";
                return $this;
            }

            if ( $method == "menu" ) {
                $this->value->initial_execute_mode = "MENU";
                $this->value->render("MENU");
                $method = "execute";
                return $this;
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

            echo "<BR>Error in call to method '$method'<BR>";
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
