<?php

namespace Reportico;

//Class to store global var

class ReporticoApp
{
    private static $_instance;

    private $variables;

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
            self::$_instance->variables["config"] = [];
        }

        return self::$_instance;
    }

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

    private function setConfigVariable($var, $value)
    {
        $this->variables["config"][$var] = $value;
    }

    private function getVariable($var)
    {
        if (isset($this->variables[$var])) {
            return $this->variables[$var];
        } else {
            return null;
        }
    }

    private function setVariable($var, $value)
    {
        $this->variables[$var] = $value;
    }

    //
    public static function get($var)
    {
        $instance = self::getInstance();
        return $instance->getVariable($var);
    }
    public static function set($var, $value)
    {
        $instance = self::getInstance();
        return $instance->setVariable($var, $value);
    }

    public static function getConfig($var, $default = null)
    {
        $instance = self::getInstance();
        return $instance->getConfigVariable($var, $default);
    }

    public static function setConfig($var, $value)
    {
        $instance = self::getInstance();
        return $instance->setConfigVariable($var, $value);
    }
    public static function isSetConfig($var)
    {
        $instance = self::getInstance();
        return $instance->isSetConfigVariable($var);
    }

    public static function show()
    {
        $instance = self::getInstance();
        echo "<PRE>";
        var_dump($instance->variables);
        echo "</PRE>";
    }



}
