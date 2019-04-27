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

class CriteriaForm extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        $this->formTypes = [
            "prepare" => [
                "id" => "reportico-csv-box",
                "classes" => $engine->getBootstrapStyle('toolbar_button')." reportico-csv-box reportico-run-report",
                "calls" => "executeReport(this, 'csv');",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("PRINT_CSV"),
                "triggerTag" => "#reportico-csv-box"
            ],
        ];

        parent::__construct($engine);

        if ( !$type )
            return;

    }

    public function getConfig() {

        $runtime = [
            "
reportico_criteria_items = [];
" ];
        $criteria = "";
        foreach ( $this->engine->lookup_queries as $v ) {
            //$query_name = preg_replace("/ /", "\\\\ xxx", $v->query_name);
            $criteria .= "    reportico_criteria_items.push('$v->query_name');\n";
        }
        $init = [
            "
$criteria
"
        ];
        $trigger = [

            ];

        return
            [
                'name' => 'criteria-form',
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

        $sessionClass =\Reportico\Engine\ReporticoSession();
        $sessionId = $sessionClass::reporticoSessionName();

        $submit_self = $this->engine->getActionUrl();
        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $submit_self .= "?" . $forward;
        }


        $sections["begin"] = "
<FORM class='reportico-prepare-form non-printable' id='reportico-form' method='POST' action='$submit_self'>
<input type='hidden' name='reportico_session_name' value='$sessionId' />
        ";

        $sections["end"] = "</FORM>";

        return $sections;
    }
}
// -----------------------------------------------------------------------------
