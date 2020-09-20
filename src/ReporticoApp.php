<?php

 
namespace Reportico\Engine;

/**
 * Class used to store Global var
 * 
 * This class is based on the singloton architecture. 
 * Global variable can be set or get by Get and Set functions.
 * 
 * By default variables are stored in the general variables array.
 * The config variables are stored in the specific config array witch is 
 * a subdivision of the variable array
 * 
 * COnfig variable could be constant prefixed by SW_
 * 
 */
class ReporticoApp
{
    const DEFAULT_INDICATOR = '.';
    const DEBUG_NONE = 0;
    const DEBUG_LOW = 1;
    const DEBUG_MEDIUM = 2;
    const DEBUG_HIGH = 3;

    private static $_instance;

    private $variables;

    private $system_errors;
    private $system_debug;


    private function __construct()
    {}

    private function __clone()
    {}

    /**
     * Get the instance of the class
     *
     * @return SingletonClass
     */
    public static function getInstance()
    {
        if (true === is_null(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->variables = [];
            self::$_instance->system_errors = [];
            self::$_instance->system_debug = [];
            self::$_instance->variables["config"] = [];
        }

        return self::$_instance;
    }

    /**
     * Return the value of a config variable
     * 
     * If the config variable doesn't exist, a default value is returned
     * 
     * @param mixed $var Variable name
     * @param mixed $default Default value
     * 
     * @return mixed Variable value
     * 
     */
    private function getConfigVariable($var, $default = null)
    {

        if (preg_match("/^SW_/", $var)) {
            $var = strtolower(preg_replace("/SW_/", "", $var));
        }

        if (isset($this->variables["config"][$var])) {
            return $this->variables["config"][$var];
        } else {
            if (defined("SW_" . strtoupper($var))) {
                return constant("SW_" . strtoupper($var));
            }
        }

        return $default;

    }

    /**
     * Check if a config variable is defined
     * 
     * @param mixed $var Config variable name
     * 
     * @return bool
     */
    private function isSetConfigVariable($var)
    {
        if (preg_match("/^SW_/", $var)) {
            $var = strtolower(preg_replace("/SW_/", "", $var));
        }
        if (defined("SW_" . strtoupper($var))) {
            return true;
        }

        if (isset($this->variables["config"][$var])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set a config variable with a value
     * 
     * @param mixed $var Config variable name
     * @param mixed $value  Value of the config variable
     * 
     * @return void
     */
    private function setConfigVariable($var, $value)
    {
        $this->variables["config"][$var] = $value;
    }

    /**
     * Return the variable value
     * 
     * @param mixed $var variable name
     * 
     * @return mixed|null
     */
    private function getVariable($var)
    {
        if (isset($this->variables[$var])) {
            return $this->variables[$var];
        } else {
            return null;
        }
    }

    /**
     * Set a variable with a value
     * 
     * @param mixed $var Variable name
     * @param mixed $value  Value of the variable
     * 
     * @return void
     */
    private function setVariable($var, $value)
    {
        $this->variables[$var] = $value;
    }

    
    /**
     * Return the value of a variable
     * 
     * @param mixed $var Variable name
     * 
     * @return mixed|null
     */
    public static function get($var)
    {
        $instance = self::getInstance();
        return $instance->getVariable($var);
    }
    
    /**
     * Set a variable with a value
     * 
     * @param mixed $var Variable name
     * @param mixed $value  Value of the variable
     * 
     * @return void
     */
    public static function set($var, $value)
    {
        $instance = self::getInstance();
        return $instance->setVariable($var, $value);
    }

    /**
     * Return the value of a config variable
     * 
     * @param mixed $var Variable name
     * 
     * @return mixed|null
     */
    public static function getConfig($var, $default = null)
    {
        $instance = self::getInstance();
        return $instance->getConfigVariable($var, $default);
    }

    /**
     * Set a config variable with a value
     * 
     * @todo check if the variable name is not a constant. If it is, we could 
     * set the variable.
     * 
     * @param mixed $var Variable name
     * @param mixed $value  Value of the variable
     * 
     * @return void
     */
    public static function setConfig($var, $value)
    {
        $instance = self::getInstance();
        return $instance->setConfigVariable($var, $value);
    }
    
    /**
     * Check if a config variable is defined
     * 
     * @param mixed $var Variable name
     * 
     * @return bool
     */
    public static function isSetConfig($var)
    {
        $instance = self::getInstance();
        return $instance->isSetConfigVariable($var);
    }
    
    public static function &getSystemErrors()
    {
        $instance = self::getInstance();
        return $instance->system_errors;
    }

    public static function &getSystemDebug()
    {
        $instance = self::getInstance();
        return $instance->system_debug;
    }

    public static function show()
    {
        $instance = self::getInstance();
        //echo "<PRE>";
        //var_dump($instance->variables);
        //echo "</PRE>";
    }

// error handler function
    public function hasDefault($in_code)
    {
        if (substr($in_code, 0, 1) == ReporticoApp::DEFAULT_INDICATOR) {
            return true;
        }
        return false;
    }

    public static function getDefaultConfig($in_code, $in_default = "")
    {
        $out_val = $in_default;
        if (ReporticoApp::isSetConfig($in_code)) {
            $out_val = ReporticoApp::getConfig($in_code);
        } else if (ReporticoApp::isSetConfig("pdf_" . $in_code)) {
            $out_val = ReporticoApp::getConfig("pdf_" . $in_code);
        } else if (ReporticoApp::isSetConfig("chart_" . $in_code)) {
            $out_val = ReporticoApp::getConfig("chart_" . $in_code);
        } else if (ReporticoApp::isSetConfig("DEFAULT_" . $in_code)) {
            $out_val = ReporticoApp::getConfig("DEFAULT_" . $in_code);
        } else if (defined("SW_DEFAULT_" . $in_code)) {
            $out_val = constant("SW_DEFAULT_" . $in_code);
        }

        if ( $in_default && substr($in_default, 0 , 1)  != ReporticoApp::DEFAULT_INDICATOR)
        {
            $out_val = $in_default;
        }

        if ( $out_val == ReporticoApp::DEFAULT_INDICATOR )
            $out_val = "";

        return $out_val;
    }

// error handler function
    public static function checkForDefaultConfig($in_code, $in_val)
    {
        $out_val = $in_val;
        if (!$in_val) {
            $out_val = $in_val;
            if (ReporticoApp::isSetConfig($in_code)) {
                $out_val = ReporticoApp::getConfig($in_code);
            } else if (ReporticoApp::isSetConfig("pdf_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("pdf_" . $in_code);
            } else if (ReporticoApp::isSetConfig("chart_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("chart_" . $in_code);
            } else if (ReporticoApp::isSetConfig("DEFAULT_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("DEFAULT_" . $in_code);
            }

        } else
        if (substr($in_val, 0, 1) == ReporticoApp::DEFAULT_INDICATOR) {
            $out_val = substr($in_val, 1);
            if (ReporticoApp::isSetConfig($in_code)) {
                $out_val = ReporticoApp::getConfig($in_code);
            } else if (ReporticoApp::isSetConfig("pdf_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("pdf_" . $in_code);
            } else if (ReporticoApp::isSetConfig("chart_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("chart_" . $in_code);
            } else if (ReporticoApp::isSetConfig("DEFAULT_" . $in_code)) {
                $out_val = ReporticoApp::getConfig("DEFAULT_" . $in_code);
            }

        }
        return $out_val;
    }

    // Debug Message Handler
    static function handleDebug($dbgstr, $in_level)
    {
        if ( ReporticoApp::get("debug_mode") >= $in_level )
        {   
            $debug = &self::getSystemDebug();
            $debug[] = array (
            "dbgstr" => $dbgstr,
            "dbgarea" => ReporticoApp::get("code_area")
            );
        }
    }

    // User Error Handler
    static function handleError($errstr, $type = E_USER_ERROR)
    {
        self::set("errors", true);

        trigger_error($errstr, $type);
    }

    // exception handler function
    static function ExceptionHandler($exception)
    {
        self::ErrorHandler(E_USER_ERROR, $exception, false, false);
        //echo "<PRE>";
        //echo $exception->getMessage();
        //echo $exception->getTraceAsString();
        //echo "</PRE>";
    }

    // error handler function
    static function ErrorLogger($errno, $errstr, $errfile = false, $errline = false)
    {
        self::ErrorHandler($errno, $errstr, $errfile, $errline);
        return true;
    }

    // error handler function
    static function ErrorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
                $errtype = ReporticoLang::translate("Error");
                break;
            case E_NOTICE:
                $errtype = ReporticoLang::translate("Notice");
                break;
            case E_USER_ERROR:
                $errtype = ReporticoLang::translate("Error");
                break;
            case E_USER_WARNING:
                $errtype = ReporticoLang::translate("");
                break;
            case E_USER_NOTICE:
                $errtype = ReporticoLang::translate("");
                break;
            case E_WARNING:
                $errtype = ReporticoLang::translate("");
                break;

            default:
                $errtype = ReporticoLang::translate("Fatal Error");

        }
        
        
        // If request parameter errorsInModal is set then popup first user error
        // back to client and exit. This is to popup errors when probles in saving reports
        if ( ReporticoUtility::getRequestItem("errorsInModal") && $errno == E_USER_ERROR ) {


            header("HTTP/1.0 404 Not Found", true);
            echo $errstr;
            die;


        }
        // Avoid adding duplicate errors
        $errors = &self::getSystemErrors();
        $ct = 0;
        foreach ( self::getSystemErrors() as $k => $val) {
            if ($val["errstr"] == $errstr) {
                $errors[$k]["errct"] = $ct+1;
                return;
            }
        }

        $errors[] =
                array(
                "errno" => $errno,
                "errstr" => $errstr,
                "errfile" => $errfile,
                "errline" => $errline,
                "errtype" => $errtype,
                "errarea" => self::get("code_area"),
                "errsource" => self::get("code_source"),
                "errct" => 1,
                );
        //echo "<PRE>";
        //var_dump($errors);
        //echo "</PRE>";

        self::set("error_status", 1);

    }

    static function backtrace()
    {
        echo "<PRE>";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo "</PRE>";
    }


}
