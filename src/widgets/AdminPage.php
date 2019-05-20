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

class AdminPage extends Widget
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

        $triggerTag = ".reportico-prepare-save-button";

        $init = [ ];
        $runtime = [];
        $trigger = [];

        return
            [
                'name' => 'admin-page',
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


        $sections["admin-header"] = $this->engine->renderWidget("admin-header", "AdminHeader");

        if (ReporticoApp::getConfig("project") == "admin" && ReporticoApp::getConfig("admin_password") == "PROMPT") {
            // For first time ( no admin projects config.php we want to prompt for admin password
            $sections["admin-password-set"] = $this->engine->renderWidget("admin-password-set", "AdminPasswordSet");
        }

        if (ReporticoApp::getConfig("project") == "admin" && ReporticoApp::getConfig("admin_password") != "PROMPT") {
            // Just print normal admin menu
            $sections["admin-login"] = $this->engine->renderWidget("admin-login", "AdminLogin");
            $sections["admin-menu"] = $this->engine->renderWidget("admin-menu", "AdminMenu");
        }

        return $sections;
    }
}
// -----------------------------------------------------------------------------
