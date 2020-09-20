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

class CriteriaToggle extends Widget
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
        $runtime = [
"
reportico_jquery(document).on('click', '.reportico-show-criteria', function(event) {
    showCriteriaBlock();
    return false;
})
reportico_jquery(document).on('click', '.reportico-hide-criteria', function(event) {
    hideCriteriaBlock();
    return false;
})
"
        ];
        $trigger = [

            ];

        return
            [
                'name' => 'criteria-toggle',
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

        $text = "
<div class='reportico-show-criteria' style='display:none'>
    <a href='#'><span class='glyphicon glyphicon-chevron-down icon-chevron-down' aria-hidden='true'></span></a>
</div>
<div class='reportico-hide-criteria' style='display:none'>
    <a href='#'><span class='glyphicon glyphicon-chevron-up icon-chevron-up' aria-hidden='true'></span></a>
</div>
<div id='reportico-report-output'>
</div>
    ";
        $sections["widget"] = $text;

        return $sections;
    }
}
// -----------------------------------------------------------------------------
