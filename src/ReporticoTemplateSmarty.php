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
class ReporticoTemplateSmarty
{
    private $smarty = false;

    public function __construct() {
        $this->smarty = new \SmartyBC();
        $this->smarty->template_dir = ReporticoUtility::findBestLocationInIncludePath("templates");
        $this->smarty->compile_dir = ReporticoUtility::findBestLocationInIncludePath("templates_c");
        $this->smarty->debugging = false;
    }

    public function __clone()
    {}


    /**
     * Assign a template variable
     * 
     * @param mixed $var Config variable name
     * 
     * @return void
     */
    function assign($keyword, $value) {
        $this->smarty->assign($keyword, $value);
    }

    /**
     * Assign a template variable
     * 
     * @param mixed $var Config variable name
     * 
     * @return void
     */
    function fetch($template) {
        return $this->smarty->fetch($template);
    }

    /**
     * Render a template
     * 
     * @param mixed $var Config variable name
     * 
     * @return void
     */
    function display($template) {
        $this->smarty->display($template);
    }
}
