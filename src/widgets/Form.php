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
use Reportico\Engine\ReporticoUtility;

class Form extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $formType = false;
    public $formTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        $this->formTypes = [
            "basic" => [
                "id" => "reportico-form",
                "classes" => "reportico-form",
                "calls" => "executeReport(this, 'csv');",
                "styles" => "",

            ],
            "design" => [
                "id" => false,
                "classes" => "reportico-edit-link-form",
                "styles" => "",
            ],
        ];

        parent::__construct($engine);
        $this->formType = $type;

        if ( !$type )
            return;

    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];
        $trigger = [ ];

        return
            [
                'name' => 'form',
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

        $classes = $this->formTypes[$this->formType]["classes"];
        $id = $this->formTypes[$this->formType]["id"];
        if ( $id )
            $id = "$id='$id'";

        $sections["begin"] = "
<FORM class='$classes non-printable' $id  method='POST' action='$submit_self'>
<input type='hidden' name='reportico_session_name' value='$sessionId' />
        ";

        $sections["end"] = "</FORM>";

        return $sections;
    }
}
// -----------------------------------------------------------------------------
