<?php

namespace Reportico\Widgets;

/*

 * ChartNVD3
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

class ChartNVD3 extends Widget
{
    // Define asset manager

    public function __construct($engine)
    {
        $this->options = [
        ];

        parent::__construct($engine);
        $this->manager = $engine->manager;
    }

    public function getConfig() {

        if ( $this->engine->reportico_ajax_preloaded ) {
            return
                [
                    'name' => 'chartnvd3',
                    'order' => 200,
                    'files' => [
                        'css' => [ ],
                        'js' => [ ],
                    ]
                ];
        }

        return
            [
                'name' => 'chartnvd3',
                'order' => 1000,
                'files' => [
                    'css' => [
                        "{$this->engine->url_path_to_assets}/js/nvd3/nv.d3.css",
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/js/nvd3/d3.min.js",
                        "{$this->engine->url_path_to_assets}/js/nvd3/nv.d3.js",
                    ],
                    ],
                'require' => [
                    'bootstrap3'
                ]
            ];
    }
}
// -----------------------------------------------------------------------------
