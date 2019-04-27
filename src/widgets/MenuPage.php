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

class MenuPage extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false )
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [];
        $trigger = [];

        return
            [
                'name' => 'menu-page',
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
        $sections["admin-password-set"] = "";
        $sections["project-menu"] = $this->engine->renderWidget("project-menu", "ProjectMenu");

        return $sections;
    }
}
// -----------------------------------------------------------------------------
