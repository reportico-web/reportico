<?php

namespace Reportico;
//Class to store global var 

class ReporticoApp
{
    private static $_instance; 
    
    private $variables;
    
    private function __construct(){}

    private function __clone (){}
    
    /**
     * Get the instance of the class
     *
     * @return SingletonClass
     */
    public static function getInstance()
    {
        if( true === is_null( self::$_instance ) )
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    private function getVariable($var){
        if(isset($this->variables[$var])){
            return $this->variables[$var];
        } else {
            return null;
        }
    }
    
    private function setVariable($var, $value){
        $this->variables[$var] = $value;
    }
    
    
    //
    public static function get($var){
        $instance = self::getInstance();
        return $instance->getVariable($var);
    }
    public static function set($var, $value){
        $instance = self::getInstance();
        return $instance->setVariable($var,$value);
    }
}