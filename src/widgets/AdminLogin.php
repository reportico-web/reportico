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

class AdminLogin extends Widget
{
    public $rawvalue = false;
    public $value = false;

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
                'name' => 'admin-login',
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

        $user = false;
        if (!ReporticoApp::getConfig("db_connect_from_config")) {
            $user = $this->query->datasource->user_name;
        }

        //Authenticator::show();

        $sections["current-user"] = ReporticoLang::templateXlate("LOGGED_ON_AS")." $user";

        if ( Authenticator::allowed('admin') )  {

            $sections["title"] = $this->engine->renderWidget("title", "Title");

            $title = $this->engine->renderWidget("title", "Title");

            $primarystyle = $this->engine->getBootstrapStyle('button_primary');
            $sections["logout-button"] = $this->engine->renderWidget("admin-logout-button", "SubmitExecute", "admin-logout-button");

        }

        if ( !Authenticator::allowed('admin') ) {
            $fieldstyle = $this->engine->getBootstrapStyle('textfield');
            $primarystyle = $this->engine->getBootstrapStyle('button_primary');
            if (ReporticoApp::getConfig('admin_password') == "__OPENACCESS__") {
                $sections["instructions"] = ReporticoLang::templateXlate("OPEN_ADMIN_INSTRUCTIONS");
                $sections["login-prompt"] = "<input class='form-control $fieldstyle' style='display: none' type='password' name='admin_password' value='__OPENACCESS__'>";
                $sections["login-submit"] = $this->engine->renderWidget("admin-open-login-button", "SubmitExecute", "admin-open-button");
                $sections["login-error"] = ReporticoLang::templateXlate("ADMIN_PASSWORD_ERROR");
            } else {
                $sections["instructions"] = ReporticoLang::templateXlate("ADMIN_INSTRUCTIONS");
                $sections["login-prompt"] = "<input class='form-control $fieldstyle' style='width: 200px; display: inline' type='password' name='admin_password' value=''>";
                $sections["login-submit"] = $this->engine->renderWidget("admin-login-button", "SubmitExecute", "admin-button");
                $sections["login-error"] = ReporticoLang::templateXlate("ADMIN_PASSWORD_ERROR");
            }
        }


        return $sections;
    }

}
// -----------------------------------------------------------------------------
