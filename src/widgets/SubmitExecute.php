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
use \Reportico\Engine\ReporticoSession;

class SubmitExecute extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        $this->buttonType = $type;

        $this->buttonTypes = [
            "delete-report" => [
                "id" => "reportico-delete-report",
                "classes" => $engine->getBootstrapStyle('button_delete')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin-left: 50px",
                "label" => ReporticoLang::templateXlate("DELETE_REPORT"),
                "triggerTag" => "#reportico-delete-report",
                "type" => "text-button",
                "name" => "submit_XXX_DELETEREPORT",
            ],
            "run-report" => [
                "id" => "reportico-run-report",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this, '{URL}');",
                "styles" => "margin: 7px",
                "label" => ReporticoLang::templateXlate("RUN_REPORT"),
                "triggerTag" => "#reportico-run-report",
                "type" => "text-button",
                "name" => "submit_XXX_RUNREPORT",
                "url" => $engine->prepare_url
            ],
            "new-report" => [
                "id" => "reportico-new-report",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin-left: 50px",
                "label" => ReporticoLang::templateXlate("NEW_REPORT"),
                "triggerTag" => "#reportico-new-report",
                "type" => "text-button",
                "name" => "submit_maintain_NEW",
            ],
            "logout" => [
                "id" => "reportico-logout",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin: 7px",
                "label" => ReporticoLang::templateXlate("LOGOFF"),
                "triggerTag" => "#reportico-logout",
                "type" => "text-button",
                "name" => "logout",
            ],
            "login" => [
                "id" => "reportico-login",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("LOGIN"),
                "triggerTag" => "#reportico-login",
                "type" => "text-button",
                "name" => "login",
            ],
            "output-html-new-window" => [
                "id" => "reportico-printhtml-box",
                "classes" => $engine->getBootstrapStyle('toolbar_button')." reportico-printhtml-box reportico-run-report",
                "calls" => "executeReport(this, 'html-new-window');",
                "label" => ReporticoLang::templateXlate("PRINTABLE"),
                "styles" => "",
                "name" => "submitPrepare",
                "type" => "icon-button",
                "triggerTag" => "#reportico-printhtml-box"
            ],
            "output-html-inline" => [
                "id" => "reportico-html-box",
                "classes" => $engine->getBootstrapStyle('toolbar_button')." reportico-html-box reportico-run-report",
                "calls" => "executeReport(this, 'html-inline');",
                "label" => ReporticoLang::templateXlate("PRINT_HTML"),
                "styles" => "",
                "name" => "submitPrepare",
                "type" => "icon-button",
                "triggerTag" => "#reportico-html-box"
            ],
            "output-pdf" => [
                "id" => "reportico-pdf-box",
                "classes" => $engine->getBootstrapStyle('toolbar_button')." reportico-pdf-box reportico-run-report",
                "calls" => "executeReport(this, 'pdf');",
                "label" => ReporticoLang::templateXlate("PRINT_PDF"),
                "styles" => "",
                "type" => "icon-button",
                "name" => "submitPrepare",
                "triggerTag" => "#reportico-pdf-box"
            ],
            "output-csv" => [
                "id" => "reportico-csv-box",
                "classes" => $engine->getBootstrapStyle('toolbar_button')." reportico-csv-box reportico-run-report",
                "calls" => "executeReport(this, 'csv');",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("PRINT_CSV"),
                "type" => "icon-button",
                "name" => "submitPrepare",
                "triggerTag" => "#reportico-csv-box"
            ],
            "submit-go" => [
                "id" => "reportico-html-go",
                "classes" => $engine->getBootstrapStyle('button_go')." ",
                "calls" => "executeReport(this, 'html-inline');",
                "styles" => "float: right; margin: 4px",
                "label" => ReporticoLang::templateXlate("GO"),
                "type" => "text-button",
                "name" => "submitPrepare",
            ],
            "submit-reset" => [
                "id" => "reportico-reset",
                "classes" => $engine->getBootstrapStyle('button_reset')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "float: right; margin: 4px",
                "label" => ReporticoLang::templateXlate("RESET"),
                "triggerTag" => "#reportico-reset",
                "type" => "text-button",
                "name" => "clearform",
            ],
            "lookup-search" => [
                "id" => "reportico-lookup-search",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin: 4px",
                "label" => ReporticoLang::templateXlate("SEARCH"),
                "triggerTag" => "#reportico-lookup-search",
                "type" => "text-button",
                "name" => "EXPANDSEARCH_{LOOKUPITEM}",
            ],
            "lookup-clear" => [
                "id" => "reportico-lookup-clear",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin: 4px",
                "label" => ReporticoLang::templateXlate("CLEAR"),
                "triggerTag" => "#reportico-lookup-clear",
                "type" => "text-button",
                "name" => "EXPANDCLEAR_{LOOKUPITEM}",
            ],
            "lookup-select-all" => [
                "id" => "reportico-lookup-select-all",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin: 4px",
                "label" => ReporticoLang::templateXlate("SELECTALL"),
                "triggerTag" => "#reportico-lookup-select-all",
                "type" => "text-button",
                "name" => "EXPANDSELECTALL_{LOOKUPITEM}",
            ],
            "lookup-ok" => [
                "id" => "reportico-lookup-ok",
                "classes" => $engine->getBootstrapStyle('small_button')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "margin: 4px",
                "label" => ReporticoLang::templateXlate("OK"),
                "triggerTag" => "#reportico-lookup-ok",
                "type" => "text-button",
                "name" => "EXPANDOK_{LOOKUPITEM}",
            ],
            "set-admin-password-button" => [
                "id" => "reportico-set-admin-password",
                "classes" => $engine->getBootstrapStyle('button_admin')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("SET_ADMIN_PASSWORD"),
                "triggerTag" => "#reportico-set-admin-password",
                "type" => "text-button",
                "name" => "submit_admin_password",
            ],
            "admin-logout-button" => [
                "id" => "reportico-admin-logout",
                "classes" => "". $engine->getBootstrapStyle('button_primary')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("LOGOFF"),
                "triggerTag" => "#reportico-admin-logout",
                "type" => "text-button",
                "name" => "adminlogout",
            ],
            "admin-login-button" => [
                "id" => "reportico-admin-login",
                "classes" => "". $engine->getBootstrapStyle('button_primary')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("LOGIN"),
                "triggerTag" => "#reportico-admin-login",
                "type" => "text-button",
                "name" => "login",
            ],
            "admin-open-login-button" => [
                "id" => "reportico-admin-login",
                "classes" => "". $engine->getBootstrapStyle('button_primary')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("OPEN_LOGIN"),
                "triggerTag" => "#reportico-admin-login",
                "type" => "text-button",
                "name" => "login",
                "value" => "__OPENACCESS__",
            ],
            "admin-run-project" => [
                "id" => "reportico-admin-run-project",
                "classes" => "". $engine->getBootstrapStyle('button_go')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("GO"),
                "triggerTag" => "#reportico-admin-run-project",
                "type" => "text-button",
                "name" => "submit_menu_project",
            ],
            "admin-delete-project" => [
                "id" => "reportico-admin-delete-project",
                "classes" => "". $engine->getBootstrapStyle('button_delete')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("GO"),
                "triggerTag" => "#reportico-admin-delete-project",
                "type" => "text-button",
                "name" => "submit_delete_project",
            ],
            "admin-create-report" => [
                "id" => "reportico-admin-create-report",
                "classes" => "". $engine->getBootstrapStyle('button_admin')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("GO"),
                "triggerTag" => "#reportico-admin-create-report",
                "type" => "text-button",
                "name" => "submit_design_project",
            ],
            "admin-configure-project" => [
                "id" => "reportico-admin-configure-project",
                "classes" => "". $engine->getBootstrapStyle('button_admin')."",
                "calls" => "submitAjaxLink(this);",
                "styles" => "",
                "label" => ReporticoLang::templateXlate("GO"),
                "triggerTag" => "#reportico-admin-configure-project",
                "type" => "text-button",
                "name" => "submit_configure_project",
            ],
        ];

        parent::__construct($engine);

        if ( !$type )
            return;

    }

    public function getConfig() {

        if (isset($this->buttonTypes[$this->buttonType]["triggerTag"]) )
            $triggerTag = $this->buttonTypes[$this->buttonType]["triggerTag"];
        else
            $triggerTag = "#".$this->buttonTypes[$this->buttonType]["id"];
        $calls = $this->buttonTypes[$this->buttonType]["calls"];
        $url = false;
        if ( isset($this->buttonTypes[$this->buttonType]["url"]) ) {
            $sessionClass = \Reportico\Engine\ReporticoSession();
            $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
            $url = $this->buttonTypes[$this->buttonType]["url"];
            if ( $forward )
                $url .= "&$forward";
            $calls = preg_replace("/{URL}/", $url, $calls);
        }

        $init = [ ];

        $runtime = [];
        if ( $calls )
            $runtime = [
"
reportico_jquery(document).on('click', '$triggerTag', function() {
    $calls
    return false;
})
"
        ];

        $trigger = [

            ];

        return
            [
                'name' => $this->buttonType,
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
        $classes = $this->buttonTypes[$this->buttonType]["classes"];
        $id = $this->buttonTypes[$this->buttonType]["id"];
        $label = ReporticoLang::translate($this->buttonTypes[$this->buttonType]["label"]);
        $value = isset($this->buttonTypes[$this->buttonType]["value"]) ? $this->buttonTypes[$this->buttonType]["value"] : $label;
        $styles= ReporticoLang::translate($this->buttonTypes[$this->buttonType]["styles"]);
        $name= ReporticoLang::translate($this->buttonTypes[$this->buttonType]["name"]);



        if ( $this->buttonTypes[$this->buttonType]["type"] == "icon-button" )
            $text = "<input type='submit' id='$id' class='$classes' style='$styles' title='$label' name='$name' value=''>";
        else
            $text = "<input type='submit' id='$id' class='$classes' style='$styles' name='$name' value='$value'>";
        $this->manager->availableAssets[$this->buttonType] = $this;

        $sections = [];
        $sections["widget"] = $text;
        $sections["id"] = $id;
        $sections["classes"] = $classes;
        $sections["styles"] = $styles;
        $sections["title"] = $label;
        $sections["label"] = $label;
        $sections["name"] = $name;
        $sections["value"] = $value;
        $sections["type"] = $this->buttonTypes[$this->buttonType]["type"];

        return $sections;
    }
}
// -----------------------------------------------------------------------------
