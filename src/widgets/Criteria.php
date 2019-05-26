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

class Criteria extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $engineCriteria = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $engineCriteria = false )
    {

        $this->engineCriteria = $engineCriteria;

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

    }

    public function getConfig() {

        $runtime = [
            "
reportico_criteria_items = [];
" ];
        $criteria = "";
        foreach ( $this->engine->lookup_queries as $v ) {
            $criteria .= "    reportico_criteria_items.push('$v->query_name');\n";
        }
        $init = [
"
//reportico_criteria_items = [];
$criteria

//reportico_jquery(document).on('click', '#reportico-lookup-button', function(event) {
//    executeExpand(this);
//})
"
        ];
        $trigger = [

            ];

        return
            [
                'name' => 'criteria',
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

        // Identify if we need to go into Expand/Lookup mode for this criteria
        $this->engine->expand_col = false;

        $lastdisplaygroup = "";
        if ( true)
        {
            $col = $this->engineCriteria;
            $name = preg_replace("/ /", "_", $col->query_name);
            $origname = $col->query_name;

            if ($col->criteria_type) {
                if (array_key_exists("EXPAND_" . $name, $_REQUEST)) {

                    $sections["lookup-selection"] = $this->engineCriteria->renderSelection(true);
                    $sections["lookup-criteria-name"] = $this->engineCriteria->query_name;
                }

                if (array_key_exists("EXPANDCLEAR_" . $name, $_REQUEST)) {
                    //$this->engine->expand_col = &$this->engine->lookup_queries[$col->query_name];
                    $sections["lookup-selection"] = $this->engineCriteria->renderSelection(true);
                    $sections["lookup-criteria-name"] = $this->engineCriteria->query_name;
                }

                if (array_key_exists("EXPANDSELECTALL_" . $name, $_REQUEST)) {
                    $sections["lookup-selection"] = $this->engineCriteria->renderSelection(true);
                    $sections["lookup-criteria-name"] = $this->engineCriteria->query_name;
                }

                if (array_key_exists("EXPANDSEARCH_" . $name, $_REQUEST)) {
                    //$this->engine->expand_col = &$this->engine->lookup_queries[$col->query_name];
                    $sections["lookup-selection"] = $this->engineCriteria->renderSelection(true);
                    $sections["lookup-criteria-name"] = $this->engineCriteria->query_name;
                }
            }
        }

        $sections["hidden"] = $this->engineCriteria->hidden == "yes";
        $sections["required"] = $this->engineCriteria->required == "yes";
        $sections["title"] = ReporticoLang::translate($this->engineCriteria->deriveAttribute("column_title"));
        $sections["tooltip"] = ReporticoLang::translate($this->engineCriteria->criteria_help);
        $sections["selection"] = $this->engineCriteria->renderSelection();

        $tabgroup = $this->engineCriteria->display_group;
        $openfilters = preg_replace("/ /", "_", $sessionClass::getReporticoSessionParam("openfilters"));
        $closedfilters = $sessionClass::getReporticoSessionParam("closedfilters");

        $sections["tab"] = $tabgroup;
        $sections["id"] = preg_replace("/ /", "_", $tabgroup);
        $sections["tabclass"] = "";
        $sections["tabhidden"] = true;
        //$sections["open"] = false;
        if ( $tabgroup ) {
            $sections["tabclass"] = "displayGroup{$sections["id"]}";
            $sections["hidden"] = true;
            if ($openfilters) {
                if (in_array(preg_replace("/ /", "_", $tabgroup), $openfilters)) {
                    $sections["hidden"] = false;
                    $sections["tabhidden"] = false;
                }
            }
        }

        if ($this->engineCriteria->expand_display && $this->engineCriteria->expand_display != "NOINPUT") {
            $name = $this->engineCriteria->query_name;
            $label = ReporticoLang::templateXlate("EXPAND");
            $sections["lookup"] = "<input class='reportico-prepare-crit-expand-button reportico-lookup-button' type='button' name='EXPAND_$name' value='$label'>";
        }
        return $sections;
    }
}
// -----------------------------------------------------------------------------
