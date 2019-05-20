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
use \Reportico\Engine\XmlReader;

class AdminMenu extends Widget
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
                'name' => 'admin-menu',
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
        $dropdownstyle = $this->engine->getBootstrapStyle('dropdown');
        $gostyle = $this->engine->getBootstrapStyle('button_go');
        $adminstyle = $this->engine->getBootstrapStyle('button_admin');

        $sections = [];

        // Project List options
        $projectlist = "";
        $projects = $this->generateProjectList();
        foreach ( $projects as $projectitem)
            $projectlist .= "<OPTION label='{$projectitem["label"]}' value='{$projectitem["label"]}'>{$projectitem["label"]}</OPTION>";
        $sections["project-options"] = $projectlist;

        // Language selector
        $sections["languages"] = $this->engine->renderWidget("language-selector", "LanguageSelector");

        // Selection list of projects and Run button
        $sections["menu-item-run-project"] = "";
        $sections["select-run-project"] = "";

        $sections["admin-login-button"] = $this->engine->renderWidget("admin-login-button", "SubmitExecute", "admin-login-button");
        $sections["admin-logout-button"] = $this->engine->renderWidget("admin-logout-button", "SubmitExecute", "admin-logout-button");
        $sections["run-project-button"] = $this->engine->renderWidget("admin-run-project", "SubmitExecute", "admin-run-project");
        $sections["delete-project-button"] = $this->engine->renderWidget("admin-delete-project", "SubmitExecute", "admin-delete-project");
        $sections["create-report-button"] = $this->engine->renderWidget("admin-create-report", "SubmitExecute", "admin-create-report");
        $sections["configure-project-button"] = $this->engine->renderWidget("admin-configure-project", "SubmitExecute", "admin-configure-project");

        // Other admin links like Create Project and Configure Tutorials
        $sections["project-menu-items"] = [];
        if ( Authenticator::allowed("admin") ) {

            $menuitems = $this->generateMenuList();
            foreach ( $menuitems as $menuitem ){
                if ( $menuitem["label"] == 'BLANKLINE' )
                    $sections["project-menu-items"][] = [ "label" => "&nbsp;", "url" => false ];
                else if ( $menuitem["label"] == 'LINE' )
                    $sections["project-menu-items"][] = [ "label" => "<hr>", "url" => false ];
                else
                    $sections["project-menu-items"][] = [ "label" => $menuitem["label"], "url" => $menuitem["url"] ];
            }
        }

        // Link to online documentation
        $sections["documentation"] = "<a target='_blank' href='{$this->engine->url_site}documentation/{$this->engine->doc_version}'>".ReporticoLang::templateXlate("DOCUMENTATION")."</a>";

        return $sections;
    }

    // -----------------------------------------------------------------------------
    // Fetch the projects
    // -----------------------------------------------------------------------------
    public function generateMenuList ()
    {
        $sessionClass = \Reportico\Engine\ReporticoSession();

        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $forward .= "&";
        }

        if (preg_match("/\?/", $this->engine->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        $items = [];

        if (ReporticoApp::get("static_menu") && is_array(ReporticoApp::get("static_menu"))) {
            $ct = 0;
            foreach (ReporticoApp::get("static_menu") as $menuitem) {
                if ($menuitem["title"] == "<AUTO>") {
                    // Generate Menu from XML files
                    if (is_dir(ReporticoApp::get("projpath"))) {
                        if ($dh = opendir(ReporticoApp::get("projpath"))) {
                            while (($file = readdir($dh)) !== false) {
                                $mtch = "/" . $menuitem["report"] . "/";
                                if (preg_match($mtch, $file)) {
                                    $repxml = new XmlReader($this->engine, $file, false, "ReportTitle");
                                    $items[] = array(
                                        "label" => $repxml->search_response,
                                        "url" => $this->engine->getActionUrl() . $url_join_char . $forward . "execute_mode=PREPARE&xmlin=" . $file . "&reportico_session_name=" . $sessionClass::reporticoSessionName(),
                                    );
                                }
                            }
                        }
                        closedir($dh);
                    }
                } else {
                    $items[] = array(
                        "label" => ReporticoLang::templateXlate($menuitem["title"]),
                        "url" => $this->engine->getActionUrl() . $url_join_char . $forward . "execute_mode=PREPARE&xmlin=" . $menuitem["report"] . "&reportico_session_name=" . $sessionClass::reporticoSessionName(),
                    );
                }
                $ct++;
            }

            if ($ct == 0) {
                ReporticoApp::handleError("No Menu Items Available - Check Language - " . ReporticoApp::get("language"));
            }
        }

        return $items;
    }

    public function generateProjectList ()
    {
        $sessionClass = \Reportico\Engine\ReporticoSession();

        $projects = [];
        $projpath = $this->engine->projects_folder;
        if (!is_dir($projpath)) {
            ReporticoUtility::findFileToInclude($projpath, $projpath);
        }

        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward) {
            $forward .= "&";
        }

        if (preg_match("/\?/", $this->engine->getActionUrl())) {
            $url_join_char = "&";
        } else {
            $url_join_char = "?";
        }

        if (is_dir($projpath)) {
            $ct = 0;
            if ($dh = opendir($projpath)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == "." || $file == "admin")
                        continue;

                    if (is_dir($projpath . "/" . $file)) {
                        if (is_file($projpath . "/" . $file . "/config.php")) {
                            $projects[] = array(
                                "label" => $file,
                                "url" => $this->engine->getActionUrl() . $url_join_char . $forward . "execute_mode=MENU&project=" . $file . "&reportico_session_name=" . $sessionClass::reporticoSessionName(),
                            );
                        }
                    }

                }
                closedir($dh);
            }
        }

        return $projects;
    }


}
// -----------------------------------------------------------------------------
