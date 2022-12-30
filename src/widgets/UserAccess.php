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

use Reportico\Engine\Authenticator;
use Reportico\Engine\ReporticoLang;
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;

class UserAccess extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $engineCriteria = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $engineCriteria = false )
    {

        parent::__construct($engine);

    }

    public function getConfig() {

        $init = [ ];

        $criteria = "";
        foreach ( $this->engine->lookup_queries as $v ) {
            $criteria .= "    reportico_criteria_items.push('$v->query_name');\n";
        }
        $runtime = [];
        $trigger = [

            ];

        return
            [
                'name' => 'user-access',
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
        $lastval = false;
        $duptypect = 0;

        $sections["password-prompt"] = ReporticoLang::templateXlate("ENTER_PROJECT_PASSWORD" );
        $sections["login-box"] = "<input type='password' name='project_password' value=''>";
        $sections["login-button"] = "<input class='btn btn-sm btn-default reportico-prepare-submit' type='submit' name='login' value='".ReporticoLang::templateXlate("LOGIN" )."'>";
        //$sections["logout-button"] = "<input type='password' name='project_password' value=''>";
        $sections["password-error"] = "";
        if ( Authenticator::allowed("project_password_error"))
            $sections["password-error"] = "<span style='color: #ff0000;'>".ReporticoLang::templateXlate("PASSWORD_ERROR" )."</span>";

        return $sections;
    }
}
// -----------------------------------------------------------------------------
