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

class LanguageSelector extends Widget
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
        $languages = ReporticoLang::availableLanguages();
        if ( $languages ) {

            $classes = $this->engine->getBootstrapStyle('dropdown');
            $label = ReporticoLang::templateXlate("CHOOSE_LANGUAGE");
            //$text =  "<span style='text-align:right;width: 230px; display: inline-block'>$label</span>";
            $text =  "<span style='text-align:right; display: inline-block'>$label</span>";
            $text .= "<select class='$classes reportico-drop-select-regular' name='jump_to_language'>";

            foreach ( $languages as $language ) {
                if ( $language["active"] )
                    $text .= "<OPTION label='{$language["label"]}' selected value='{$language["value"]}'>{$language["label"]}</OPTION>";
                else
				    $text .= "<OPTION label='{$language["label"]}' value='{$language["value"]}'>{$language["label"]}</OPTION>";

            }
            $text .= "</select>";

        }

        return $text;
    }
}
// -----------------------------------------------------------------------------
