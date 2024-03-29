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

class CriteriaLookupRadio extends CriteriaLookup
{
    public $value = false;
    public $expanded = false;
    public $post_match_column = false;

    public function __construct($engine, $criteria = false, $expanded = false)
    {
        $this->criteria = $criteria;
        $this->expanded = $expanded;
        $this->check_text = "checked";
        $this->post_match_column = "return";

        parent::__construct($engine, $criteria, $expanded);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];

        return
            [
                'name' => 'criteria-list-radio',
                'type' => 'criteria-selection',
                'title' => 'Radio Buttons',
                'renderType' => 'RADIO',
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
        return "";
    }

    public function renderWidgetItem($label, $value, $selected )
    {
        $selectedFlag = $selected ? "checked" : "";
        $name = $this->expanded ? "EXPANDED_" . $this->criteria->query_name : "DIRECT_". $this->criteria->query_name;
        return '<INPUT type="radio" name="'.$name.'" value="' . $value . '" ' . $selectedFlag . '>' . ReporticoLang::translate($label) . '<BR>';
    }


    public function renderWidgetEnd()
    {
        return "";
    }

}
// -----------------------------------------------------------------------------
