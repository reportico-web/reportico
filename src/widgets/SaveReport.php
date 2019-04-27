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

class SaveReport extends Widget
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
        $runtime = [
"
reportico_jquery(document).on('click', '$triggerTag', function() {
    saveReport(this);
    return false;
})
"
        ];
        $trigger = [

            ];

        return
            [
                'name' => 'save-report',
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
        $file = ReporticoLang::templateXlate("REPORT_FILE");
        $save = ReporticoLang::templateXlate("SAVE");

        if ($this->engine->xmlinput == "deleteproject.xml" || $this->engine->xmlinput == "configureproject.xml" || $this->engine->xmlinput == "createtutorials.xml" || $this->engine->xmlinput == "createproject.xml" || $this->engine->xmlinput == "generate_tutorial.xml")
            $reportfile = "";
        else
            $reportfile = preg_replace("/\.xml/", "", $this->engine->xmloutfile);

        $buttonstyle = $this->engine->getBootstrapStyle('button_primary');

        $text = "$file<input type='text' name='xmlout' id='reportico-prepare-save-file' value='$reportfile'> <input type='submit' class='$buttonstyle reportico-prepare-save-button' type='submit' name='submit_xxx_SAVE' value='$save'>";

        return $text;
    }
}
// -----------------------------------------------------------------------------
