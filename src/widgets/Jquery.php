<?php

namespace Reportico\Widgets;

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

class Jquery extends Widget
{
    // Define asset manager

    public function __construct($engine)
    {
        parent::__construct($engine);
        $this->manager = $engine->manager;
    }

    public function getConfig() {

        if ( $this->engine->jquery_preloaded ) {
            return
            [
                'name' => 'jquery',
                'order' => 10,
                'files' => [ ],
            ];
        }

        return
            [
                'name' => 'jquery',
                'order' => 10,
                'files' => [
                    'js' => [
                        "{$this->engine->url_path_to_assets}/node_modules/jquery/js/jquery.js"
                    ]
                ],
            ];
    }
}
// -----------------------------------------------------------------------------
