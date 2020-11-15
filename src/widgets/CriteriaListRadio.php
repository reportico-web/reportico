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
use Reportico\Engine\ReporticoUtility;

class CriteriaListRadio extends CriteriaList
{
    public $value = false;
    public $expanded = false;

    public function __construct($engine, $criteria = false, $expanded = false)
    {
        $this->check_text = "checked";
        $this->criteria = $criteria;
        $this->expanded = $expanded;

        parent::__construct($engine, $criteria, $expanded);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];

        return
            [
                'name' => 'criteria-list-radio',
                'type' => 'criteria-selection',
                'title' => 'Checkbox List',
                'renderType' => 'RADIO',
                'sourceType' => 'LIST',
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
        return "";
    }

    public function renderWidgetItem($label, $value, $selected )
    {
        $selectedFlag = $selected ? "checked" : "";
        $name = $this->expanded ? "EXPANDED_" . $this->criteria->query_name : "DIRECT_". $this->criteria->query_name;
        return '<INPUT type="radio" name="'.$name.'" value="' . $value . '" ' . $selected . '>' . ReporticoLang::translate($label) . '<BR>';
    }


    public function renderWidgetEnd()
    {
        return "";
    }

}
// -----------------------------------------------------------------------------
