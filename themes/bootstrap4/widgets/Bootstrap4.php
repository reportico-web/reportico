<?php

namespace Reportico\Themes\Widgets;

/*

 * Core
 *
 * Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

class Bootstrap4 extends \Reportico\Widgets\Widget
{
    // Define asset manager

    public function __construct($engine)
    {
        parent::__construct($engine);
        $this->manager = $engine->manager;
    }

    public function getConfig() {

        $this->engine->css_framework = "bootstrap4";

        if ( $this->engine->bootstrap_preloaded )
            return [];

        return
            [
                'name' => 'framework',
                'autoload' => false,
                'order' => 100,
                'files' => [
                    'css' => [
                        "{$this->engine->url_path_to_assets}/bootstrap4/bootstrap.css",
                        "{$this->engine->url_path_to_assets}/font-awesome/font-awesome.css"
                    ],
                    'js' => [
                        //"{$this->engine->url_path_to_assets}/bootstrap4/popper.js",
                        "{$this->engine->url_path_to_assets}/bootstrap4/bootstrap.js"
                    ],
                    'events' => [
                        'runtime' => [
"
reportico_css_framework = 'bootstrap4';
reportico_bootstrap_styles = '4';
"
                        ]
                    ]],
                'require' => [
                    'jquery',
                    'bootstrap'
                ]
            ];
    }

}
// -----------------------------------------------------------------------------
