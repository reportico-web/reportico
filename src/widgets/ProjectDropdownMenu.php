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

class ProjectDropdownMenu extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        parent::__construct($engine);

    }

    public function getConfig() {

        $init = [ ];
        $runtime = [];
        $trigger = [ ];

        return
            [
                'name' => 'project-dropdown-menu',
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

        $menu = '';

        //if ( Authenticator::allowed("project") && $this->engine->dropdown_menu && $this->engine->output_template_parameters["show_hide_dropdown_menu"] == "show" ) {
        if ( $this->engine->dropdown_menu && $this->engine->output_template_parameters["show_hide_dropdown_menu"] == "show" ) {

            $this->engine->generateDropdownMenu($this->engine->dropdown_menu);
            $brand = "<a href='#' class='navbar-brand'>".ReporticoApp::get('menu_title')." :</a>";
            $sections["brand"] = ReporticoApp::get('menu_title');
            $sections["menu-items"] = [];
            $menu = "<ul class='nav navbar-nav' style='margin-bottom: 0px'>";
            foreach ( $this->engine->dropdown_menu as $menuitem ) {
                $menuarr = [ "title" => $menuitem["title"], "items" => [] ];
                $menu .=  "<li class='dropdown'><a class='dropdown-toggle' data-toggle='dropdown' href='#'>{$menuitem["title"]}<span class='caret'></span></a>";
                $menu .=  "<ul class='dropdown-menu reportico-dropdown'>";
                if ( isset($menuitem["items"])) {
                    foreach ( $menuitem["items"] as $k => $subitem ) {
                        if (!isset($subitem["project"]))
                            $subitem["project"] = $menuitem["project"];

                        if (isset($subitem["reportname"])){
                            $menu .= "<li><a class='reportico-dropdown-item' href='$prepare_url&project={$subitem["project"]}&xmlin={$subitem["reportfile"]}'>{$subitem["reportname"]}</a></li>";
                        }
                        if (!isset($subitem["reportname"]))
                            continue;
                        $menuarr["items"][] = [
                            "url" => "$prepare_url&project={$subitem["project"]}&xmlin={$subitem["reportfile"]}",
                            "report-file" => "{$subitem["reportfile"]}",
                            "label" => "{$subitem["reportname"]}"
                        ];
                    }
                }
                $sections["menu-items"][] = $menuarr;
                $menu .= "</ul>";
                $menu .= "</li>";
            }
            $menu .= "</ul>";
            $sections["complete"] = $menu;
        }

        return $sections;

    }
}
// -----------------------------------------------------------------------------
