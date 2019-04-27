<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 * Widget for rendering Submit buttons in the front-end
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

use Reportico\Engine\ReporticoLang;
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;
use \Reportico\Engine\ReporticoUtility;

class DesignPage extends Widget
{
    public function __construct($engine, $load = false )
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $triggerTag = ".reportico-prepare-save-button";

        $init = [ ];
        $runtime = [];
        $trigger = [];

        return
            [
                'name' => 'design-page',
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

    public function render()
    {

        $sections = [];
        $sections["partial-section"] = ReporticoUtility::getRequestItem("partialMaintain", false);

        return $sections;
    }
}
// -----------------------------------------------------------------------------
