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
use \Reportico\Engine\ReporticoSession;
use \Reportico\Engine\ReporticoUtility;
use Reportico\Engine\ReporticoLang;
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;

class NavigationMenu extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        $this->buttonType = $type;

        $this->buttonTypes = [
            "page-setup" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITPAGESETUP",
                "label" => "EDITPAGESETUP",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerform_Page|Margin|Zoom|Paginate",
            ],
        ];

        parent::__construct($engine);

        if ( !$type )
            return;

    }

    public function getConfig() {

        $init = [ ];
        $runtime = [];
        $trigger = [ ];

        return
            [
                'name' => 'navigation-menu',
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

    public function loadPartial($name)
    {
        $partialName = strtolower(get_class($this));
        $fileName = __DIR__."/$partialName/$name.htm";
        if (file_exists($fileName)) {
            return file_get_contents($fileName);
        }
        return false;
    }

    public function render()
    {
        $sessionClass = \Reportico\Engine\ReporticoSession();

        $prepare_url = $this->engine->prepare_url;
        $menu_url = $this->engine->menu_url;
        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $menu_url .= "&" . $forward;
            $prepare_url .= "&" . $forward;
        }

        $admin_menu_url = $this->engine->admin_menu_url;
        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $admin_menu_url .= "&" . $forward;
        }

        $create_report_url = $this->engine->create_report_url;
        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $create_report_url .= "&" . $forward;
        }

        $sections = [];

        if ( $this->engine->output_template_parameters["show_hide_navigation_menu"] == "hide" )
            $navstyle = 'display:none';

        if ( $this->engine->dropdown_menu && $this->engine->output_template_parameters["show_hide_dropdown_menu"] == "show" ) {
            Authenticator::flag("show-project-dropdown-menu");
            $sections["dropdown-menu"] = $this->engine->renderWidget("project-dropdown-menu", "ProjectDropdownMenu");
        }

        $sections ["debug-level"] = $this->engine->renderWidget("debug-level", "DebugLevel");

        $sections["admin_menu_url"] = $admin_menu_url;
        $sections["create_report_url"] = $create_report_url;
        $sections["prepare_url"] = $prepare_url;
        $sections["menu_url"] = $menu_url;
        $sections["mode"] = $this->engine->execute_mode;

        $sections["logout-button"] = $this->engine->renderWidget("logout", "SubmitExecute");
        $sections["login-button"] = $this->engine->renderWidget("login", "SubmitExecute");

        $useraccess = $this->engine->renderWidget("user-access", "UserAccess");

        return $sections;

    }
}
// -----------------------------------------------------------------------------
