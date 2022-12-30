<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 e Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;

class Select2 extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public function __construct($engine)
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [  ];

        if ( $this->engine->reportico_ajax_preloaded ) {
            return
                [
                    'name' => 'select2',
                    'order' => 200,
                    'files' => [
                        'css' => [ ],
                        'js' => [ ],
                        'events' => [
                            'init' => $init,
                            'runtime' => $runtime
                        ]
                    ]
                ];
        }

        return
            [
                'name' => 'select2',
                'order' => 200,
                'files' => [
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

}
// -----------------------------------------------------------------------------
