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
class ReporticoTemplate
{
    private function __construct()
    {}

    private function __clone()
    {}


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
}
