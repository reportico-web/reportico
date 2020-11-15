<?php
namespace Reportico\Widgets;

/*

 * Core
 *
 e Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;
use \Reportico\Engine\ReporticoLang;

class CriteriaLookupDropdown extends CriteriaLookup
{
    public $value = false;
    public $expanded = false;

    public function __construct($engine, $criteria = false, $expanded = false)
    {
        $this->criteria = $criteria;
        $this->expanded = $expanded;

        parent::__construct($engine, $criteria, $expanded);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];

        return
            [
                'name' => 'criteria-lookup-dropdown',
                'type' => 'criteria-selection',
                'title' => 'Single Lookup Dropdown',
                'renderType' => 'DROPDOWN',
                'sourceType' => 'LOOKUP',
                'order' => 200,
                'files' => [
                    'css' => [],
                    'js' => [],
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

    public function renderWidgetStart()
    {
        $name = $this->expanded ? "EXPANDED_" . $this->criteria->query_name : "DIRECT_" . $this->criteria->query_name;
        $text = '<SELECT class="' . $this->criteria->lookup_query->getBootstrapStyle('design_dropdown') . 'reportico-drop-select-regular" name="' . $name . '">';
        return $text;
    }

    public function renderWidgetItem($label, $value, $selected )
    {
        $selectedFlag = $selected ? "selected" : "";
        $name = $this->expanded ? "EXPANDED_" . $this->criteria->query_name : $this->criteria->query_name;
        return '<OPTION label="' . $label . '" value="' . $value . '" ' . $selectedFlag . '>' . $label . '</OPTION>';
    }


    public function renderWidgetEnd()
    {
        return "</SELECT>";
    }

}
// -----------------------------------------------------------------------------
