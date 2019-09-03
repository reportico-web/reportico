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
use \Reportico\Engine\Authenticator;

class DebugLevel extends Widget
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
                'name' => 'debug-level',
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

        $text = "";
        if ( Authenticator::allowed("design") && $this->engine->execute_mode == "PREPARE" )
            if ($this->engine->allow_maintain == "DEMO" || ( $this->engine->xmlinput != "configureproject.xml" && $this->engine->xmlinput != "deleteproject.xml" )) {

                //$debugMode = ReporticoUtility::getRequestItem("debug_mode", "0", $this->engine->first_criteria_selection);
                $debugMode = ReporticoUtility::getRequestItem("debug_mode", "0");
                //echo $debugMode."!!";
                $selected0 = !$debugMode ? "selected" : "";
                $selected1 = $debugMode == "1" ? "selected" : "";
                $selected2 = $debugMode == "2" ? "selected" : "";
                $selected3 = $debugMode == "3" ? "selected" : "";

                $dropdownstyle = $this->engine->getBootstrapStyle('dropdown');
                $text = "
                <!--div style='margin: 6px 8px 0px 8px'-->
                <div>
                ".ReporticoLang::templateXlate("DEBUG_LEVEL")."
                <SELECT class='span2 $dropdownstyle' style='margin-bottom: 1px; display:inline; width: auto' name='debug_mode'>
                    <OPTION $selected0 label='None' value='0'>".ReporticoLang::templateXlate("DEBUG_NONE" )."</OPTION>
                    <OPTION $selected1 label='Low' value='1'>".ReporticoLang::templateXlate("DEBUG_LOW" )."</OPTION>
                    <OPTION $selected2 label='Medium' value='2'>".ReporticoLang::templateXlate("DEBUG_MEDIUM" )."</OPTION>
                    <OPTION $selected3 label='High' value='3'>".ReporticoLang::templateXlate("DEBUG_HIGH" )."</OPTION>
                </SELECT>
                </div>
                ";
            }

        return $text;
    }
}
// -----------------------------------------------------------------------------
