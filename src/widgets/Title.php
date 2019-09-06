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

use \Reportico\Engine\ReporticoSession;
use \Reportico\Engine\ReporticoUtility;
use Reportico\Engine\ReporticoLang;
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;
use \Reportico\Engine\Authenticator;

class Title extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false )
    {

        parent::__construct($engine);

        return;

    }

    public function getConfig() {

        $init = [ ];
        $runtime = [];
        $trigger = [ ];

        return
            [
                'name' => 'title',
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
        // For Admin options title should be translatable
        // Also for configureproject.xml
        $sections = [];

        if ( Authenticator::allowed("admin-report-selected")) {
            $title = $this->engine->deriveAttribute("ReportTitle", "");
        } else {
            $title = ReporticoLang::translate($this->engine->deriveAttribute("ReportTitle", "Set Report Title"));
        }

        // Show Title
        $text = "<h1 class='reportico-title-bar' >$title";

        // Show Title Edit if in Design Mode
        if ($this->engine->execute_mode == "PREPARE" && ( Authenticator::allowed("design") || $this->engine->access_mode == "DEMO")) {
            if ( Authenticator::allowed("admin-report-selected")) {
                $this->engine->widgets["popup-edit-title"] = new \Reportico\Widgets\PopupEditButton($this->engine, true, "edit-title");
                $text .= ($this->engine->widgets["popup-edit-title"]->render())["widget"];
            }
        }

        $text .= "</h1>";

        $sections["title"] = $title;
        $sections["widget"] = $text;

        return $sections;

    }
}
// -----------------------------------------------------------------------------
