<?php

namespace Reportico\Engine;

/*

 * AssetManager
 *
 * Collects js and css files together from
 * core widgets, theme widgets and custom ones
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

class AssetManager
{
    // Define asset manager
    public $manager = false;
    public $engine = false;
    public $framework = false;
    public $theme = false;
    public $availableAssets = [];

    public function __construct($engine)
    {
        $this->engine = $engine;
        $this->engine->manager = $this;
    }

    /*
     * Prepare asset manager
     */
    function initialise(){

        $this->manager = new \Reportico\Assetter\Assetter(new \Requtize\FreshFile\FreshFile(__DIR__."/../"));

        /*
         * Autoload assets from widgets folder
         */

        $files = glob(__DIR__."/widgets/*");

        foreach ( $files as $file) {

            //echo "$file<BR>";
            $base = substr(basename($file), 0, -4);
            $load = true;
            if ( $base == "DynamicGrid" ) $load = false;
            if ( $base == "SubmitExecute" ) continue;
            if ( $base == "Widget" ) {
                continue;
            }
            $file = "\\Reportico\\Widgets\\".substr(basename($file), 0, -4);

            $widget = new $file($this->engine, $load);
            if ( $widget->name )
                $this->availableAssets[$widget->name] = $widget;

        }
    }

    /*
     * Prepare asset manager
     */
    function initialiseTheme(){

        /*
         * Autoload assets from theme widgets folder
         */
        
        $files = glob("{$this->engine->theme_dir}/{$this->engine->theme}/widgets/*");
        foreach ( $files as $file) {

            $base = substr(basename($file), 0, -4);
            $load = true;
            if ( $base == "DynamicGrid" ) $load = false;
            if ( $base == "Widget" ) {
                continue;
            }

            $class = "\\Reportico\\Themes\\Widgets\\".substr(basename($file), 0, -4);
            include_once($file);

            if ( $base == "SubmitExecute" ) continue;
            $widget = new $class($this->engine, $load);
            $load = false;

            if ( $widget->name )
                $this->availableAssets[$widget->name] = $widget;

        }
    }

    function setOptions($name, $options){

        if ( !isset ($this->availableAssets[$name]) ){
            trigger_error("Unknown widget to set options: $name",E_USER_ERROR);
        }

        //echo $name;
        //foreach ( $this->availableAssets as $k => $v ) {
            //echo $k." <BR>";
        //}
        $this->availableAssets[$name]->setOptions($options);
    }

    function render($name){

        if ( !isset ($this->availableAssets[$name]) ){
            trigger_error("Unknown widget to set options: $name",E_USER_ERROR);
        }

        return $this->availableAssets[$name]->render();
    }

    function reload($group = false) {

        foreach ( $this->availableAssets as $k => $v ) {
            $v->config = $v->getConfig();
            if ( !$v->config ) {
                continue;
            }
            if ( !$v->added ) {
                $this->manager->appendToCollection($v->config);
            }

            $this->manager->load($k);
        }
    }

    /*
     * Return init files
     */
    function event($event){
        return $this->manager->event($event);
    }

    /*
     * Return js files
     */
    function js(){
        return $this->manager->js();
    }

    /*
     * Return css files
     */
    function css(){
        return $this->manager->css();
    }

}
// -----------------------------------------------------------------------------
