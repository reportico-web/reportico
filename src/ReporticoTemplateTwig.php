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

    public function __construct($viewDir = false, $cacheDir = false, $theme = false, $disableThemeCaching = false) {

        $this->viewDir = $viewDir ? $viewDir : ReporticoUtility::findBestLocationInIncludePath("themes");


        $this->cacheDir = $cacheDir ? $cacheDir : __DIR__ . "/../themes/cache";

        if ( $theme ) 
            $this->viewDir .= DIRECTORY_SEPARATOR. $theme. DIRECTORY_SEPARATOR. "templates";

        // If theme caching disabled, then any recent theme modifications will be picked up
        if ( $disableThemeCaching )  {
            $this->cacheDir = false;
        }
        else
        {
            $rp = realpath($this->cacheDir);
            if ( !is_dir($this->cacheDir) ) {
                    echo "Error: Please make sure the cache folder '{$this->cacheDir}' exists<BR>";
                    die;
            }

            if ( !is_writeable($this->cacheDir) ) {
                    echo "Error: Please make sure the cache folder '{$rp}' has write permissions<BR>";
                    die;
            }
        }

        $loader = new \Twig\Loader\FilesystemLoader($this->viewDir);
        $this->twig = new \Twig\Environment($loader, array(
            'cache' => $this->cacheDir
            ));
    }

    public function __clone()
    {}


    /**
     * Get a template variable
     *
     * @param $keyord
     *
     * @return value
     */
    function get($keyword, $default = false) {
        return isset( $this->twig_vars[$keyword] ) ? $this->twig_vars[$keyword] : $default;
    }

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
        echo "load $template_file<BR>";
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
