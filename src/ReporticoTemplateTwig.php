<?php

 
namespace Reportico\Engine;

use Twig;
use Twig\Loader;

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
class ReporticoTemplateTwig
{
    private $twig = false;
    private $twig_vars = array();

    private $cacheDir = false;
    private $viewDir = false;

    public function __construct($viewDir = false, $cacheDir = false, $theme = false) {
        
        $this->viewDir = $viewDir ? $viewDir : ReporticoUtility::findBestLocationInIncludePath("views");
        $this->cacheDir = $cacheDir ? $cacheDir : __DIR__ . "/../views/cache";

        if ( $theme ) 
            $this->viewDir .= DIRECTORY_SEPARATOR. $theme;

        $loader = new \Twig_Loader_Filesystem($this->viewDir);
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => $this->cacheDir
            ));
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
        $this->twig_vars[$keyword] = $value;
    }

    /**
     * Assign a template variable
     * 
     * @param mixed $var Config variable name
     * 
     * @return void
     */
    function fetch($template_file) {
        $template = $this->twig->load($template_file);
        return $template->render($this->twig_vars);
    }

    /**
     * Render a template
     * 
     * @param mixed $var Config variable name
     * 
     * @return void
     */
    function display($template_file) {
        $template = $this->twig->load($template_file);
        echo $template->render($this->twig_vars);
    }
}
