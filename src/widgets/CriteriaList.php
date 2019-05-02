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

class CriteriaList extends Widget
{
    public $value = false;
    public $expanded = false;

    public function __construct($engine, $criteria = false, $expanded = false)
    {
        $this->criteria = $criteria;
        $this->expanded = $expanded;

        parent::__construct($engine);
    }

    public static function createCriteriaList($engine, $criteria, $expanded) {

        if ($expanded) {
            $type = $criteria->expand_display;
        } else {
            $type = $criteria->criteria_display;
        }

        switch($type){
            case "NOINPUT": $class = "\Reportico\Widgets\CriteriaListTextField"; break;
            case "ANYCHAR": $class = "\Reportico\Widgets\CriteriaListTextField"; break;
            case "TEXTFIELD": $class = "\Reportico\Widgets\CriteriaListTextField"; break;
            case "MULTI": $class = "\Reportico\Widgets\CriteriaListMultiDropdown"; break;
            case "SELECT2MULTIPLE": $class = "\Reportico\Widgets\CriteriaListSelect2Multi"; break;
            case "SELECT2SINGLE": $class = "\Reportico\Widgets\CriteriaListSelect2Single"; break;
            case "CHECKBOX": $class = "\Reportico\Widgets\CriteriaListCheckbox"; break;
            case "RADIO": $class = "\Reportico\Widgets\CriteriaListRadio"; break;
            case "DROPDOWN": $class = "\Reportico\Widgets\CriteriaListDropdown"; break;
            default: $class = "\Reportico\Widgets\CriteriaListDropdown"; break;
        }

        return new $class($engine, $criteria, $expanded);

    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];

        return
            [
                'name' => 'criteria-list-checkbox',
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

    public function render()
    {
        $text = "";

        $this->value = "";
        $name = "unknown";

        $class = $this->criteria->parent_reportico->getBootstrapStyle('textfield');

        // START
        $sessionClass = \Reportico\Engine\ReporticoSession();

        $text = "";
        if ($this->expanded) {
            $tag_pref = "EXPANDED_";
            $type = $this->criteria->expand_display;
        } else {
            $tag_pref = "";
            $type = $this->criteria->criteria_display;
        }

        $value_string = "";

        $params = array();
        $manual_params = array();
        $hidden_params = array();
        $expanded_params = array();


        if (!$this->criteria->list_values) {
            trigger_error("'{$this->criteria->query_name}' is defined as a custom list criteria type without any list values defined", E_USER_ERROR);
        }

        if (array_key_exists("clearform", $_REQUEST)) {
                // If clearform is set, then reset selections
                $hidden_params = $this->criteria->defaults;
                $manual_params = $this->criteria->defaults;
        } else {
            if (!array_key_exists("EXPANDED_" . $this->criteria->query_name, $_REQUEST)) {
                if (array_key_exists($this->criteria->query_name, $_REQUEST)) {
                    $params = $_REQUEST[$this->criteria->query_name];
                    if (!is_array($params)) {
                        $params = explode(',', $_REQUEST[ $this->criteria->query_name]);
                        $params = array($params);
                    }
                }
            }

            $hidden_params = array();
            if (!array_key_exists("EXPANDED_" . $this->criteria->query_name, $_REQUEST)) {
                if (array_key_exists("HIDDEN_" . $this->criteria->query_name, $_REQUEST)) {
                    $hidden_params = $_REQUEST["HIDDEN_" . $this->criteria->query_name];
                    if (!is_array($hidden_params)) {
                        $hidden_params = array($hidden_params);
                    }

                }
            }

            $manual_params = array();
            if (!array_key_exists("EXPANDED_" . $this->criteria->query_name, $_REQUEST)) {
                if (array_key_exists("MANUAL_" . $this->criteria->query_name, $_REQUEST)) {
			if (is_array($_REQUEST["MANUAL_". $this->criteria->query_name])) {
                    		$manual_params = $_REQUEST["MANUAL_". $this->criteria->query_name];
			}
			else
                    		$manual_params = explode(',', $_REQUEST["MANUAL_" . $this->criteria->query_name]);
                    if ($manual_params) {
                        $hidden_params = $manual_params;
                    }

                }
            }

            // If this is first time into screen and we have defaults then
            // use these instead
            if (!$params && !$hidden_params && $sessionClass::getReporticoSessionParam("firstTimeIn")) {
                $hidden_params = $this->criteria->defaults;
                $manual_params = $this->criteria->defaults;
            }

            $expanded_params = array();
            if (array_key_exists("EXPANDED_" . $this->criteria->query_name, $_REQUEST)) {
                $expanded_params = $_REQUEST["EXPANDED_" . $this->criteria->query_name];
                if (!is_array($expanded_params)) {
                    $expanded_params = array($expanded_params);
                }

            }
        }

        switch ($type) {
            case "NOINPUT":
            case "ANYCHAR":
            case "TEXTFIELD":
                $text .= $this->renderWidgetStart();
                //$text .= '<SELECT style="display:none" name="' . "HIDDEN_" . $this->criteria->query_name . '[]" size="1" multiple>';
                //$text .= '<OPTION selected label="ALL" value="(ALL)">ALL</OPTION>';
                break;

            case "MULTI":
                $text .= $this->renderWidgetStart();
                break;

            case "SELECT2MULTIPLE":
            case "SELECT2SINGLE":
                $text .= $this->renderWidgetStart();
                /*
                $res = &$this->criteria->list_values;
                $k = key($res);
                $multisize = 4;
                if ($res && count($res[$k]) > 4) {
                    $multisize = count($res[$k]);
                }

                if (isset($res[$k])) {
                    if (count($res[$k]) >= 10) {
                        $multisize = 10;
                    }
                }

                if ($type == "SELECT2MULTIPLE") {
                    $text .= '<SELECT class="' . $this->criteria->lookup_query->getBootstrapStyle('design_dropdown') . 'reportico-prepare-drop-select2" name="' . $tag_pref . $this->criteria->query_name . '[]" size="' . $multisize . '" multiple>';
                } else {
                    $text .= '<SELECT class="' . $this->criteria->lookup_query->getBootstrapStyle('design_dropdown') . 'reportico-prepare-drop-select2" name="' . $tag_pref . $this->criteria->query_name . '[]" size="' . $multisize . '" >';
                }

                $text .= '<OPTION></OPTION>';
                */
                break;

            case "CHECKBOX":
            case "RADIO":
                $text .= $this->renderWidgetStart();
                break;

            default:
                //$text .= '<SELECT class="' . $this->criteria->lookup_query->getBootstrapStyle('design_dropdown') . 'reportico-drop-select-regular" name="' . $tag_pref . $this->criteria->query_name . '">';
                $text .= $this->renderWidgetStart();
                break;
        }

        $check_text = "";
        switch ($type) {
            case "MULTI":
            case "DROPDOWN":
            case "ANYCHAR":
            case "TEXTFIELD":
            case "NOINPUT":
                $check_text = "selected";
                break;

            default:
                $check_text = "checked";
                break;
        }

        $clearall = false;
        $isselected = false;

        // If clear has been pressed we dont want any list items selected
        if ($this->criteria->submitted('EXPANDCLEAR_' . $this->criteria->query_name)) {
            $isselected = false;
            $clearall = true;
            $check_text = "";
        }

        // If select all has been pressed we want all highlighted
        $selectall = false;
        if ($this->criteria->submitted('EXPANDSELECTALL_' . $this->criteria->query_name)) {
            $isselected = true;
            $selectall = true;
        }

        $res = &$this->criteria->list_values;
        if (!$res) {
            $res = array();
            $k = 0;
        } else {
            reset($res);
            $k = key($res);
            for ($i = 0; $i < count($res); $i++) {
                $line = &$res[$i];
                $lab = $res[$i]["label"];
                $ret = $res[$i]["value"];
                $checked = "";

                if (!$clearall && in_array($ret, $params)) {
                    $isselected = true;
                    $checked = $check_text;
                }

                if (!$clearall && in_array($ret, $hidden_params)) {
                    $isselected = true;
                    $checked = $check_text;
                }

                if (!$clearall && in_array($ret, $expanded_params)) {
                    $isselected = true;
                    $checked = $check_text;
                }

                if ($selectall) {
                    $isselected = true;
                    $checked = $check_text;
                }

                if ($checked != "") {
                    if (!$value_string) {
                        $value_string = $lab;
                    } else {
                        $value_string .= "," . $lab;
                    }
                }


                switch ($type) {
                    case "MULTI":
                        $text .= $this->renderWidgetItem($lab, $ret, $isselected);
                        break;

                    case "SELECT2MULTIPLE":
                    case "SELECT2SINGLE":
                        $text .= $this->renderWidgetItem($lab, $ret, $isselected);
                        //$text .= '<OPTION label="' . $lab . '" value="' . $ret . '" ' . $checked . '>' . $lab . '</OPTION>';
                        break;

                    case "RADIO":
                        $text .= $this->renderWidgetItem($lab, $ret, $isselected);
                        //$text .= '<INPUT type="radio" name="' . $tag_pref . $this->criteria->query_name . '" value="' . $ret . '" ' . $checked . '>' . ReporticoLang::translate($lab) . '<BR>';
                        break;

                    case "CHECKBOX":
                        //$text .= '<INPUT type="checkbox" name="' . $tag_pref . $this->criteria->query_name . '[]" value="' . $ret . '" ' . $checked . '>' . ReporticoLang::translate($lab) . '<BR>';
                        $text .= $this->renderWidgetItem($lab, $ret, $isselected);
                        break;

                    default:
                        //$text .= '<OPTION label="' . $lab . '" value="' . $ret . '" ' . $checked . '>' . $lab . '</OPTION>';
                        $text .= $this->renderWidgetItem($lab, $ret, $isselected);
                        break;
                }

            }
        }

        switch ($type) {
            case "SELECT2MULTIPLE":
            case "SELECT2SINGLE":
                //$text .= '</SELECT>';
                $text .= $this->renderWidgetEnd();
                break;

            case "MULTI":
                $text .= $this->renderWidgetEnd();
                break;

            case "CHECKBOX":
            case "RADIO":
                $text .= $this->renderWidgetEnd();
                break;

            default:
                //$text .= '</SELECT>';
                $text .= $this->renderWidgetEnd();
                break;
        }

        if (!$this->expanded) {

            if (array_key_exists("EXPAND_" . $this->criteria->query_name, $_REQUEST) ||
                array_key_exists("EXPANDCLEAR_" . $this->criteria->query_name, $_REQUEST) ||
                array_key_exists("EXPANDSELECTALL_" . $this->criteria->query_name, $_REQUEST) ||
                array_key_exists("EXPANDSEARCH_" . $this->criteria->query_name, $_REQUEST) ||
                $this->criteria->criteria_display == "NOINPUT")
                //if ( $this->criteria->criteria_display == "NOINPUT" )
            {
                $tag = $value_string;
                if (strlen($tag) > 40) {
                    $tag = substr($tag, 0, 40) . "...";
                }

                if (!$tag) {
                    $tag = "ANY";
                }

                $text .= '<br>' . $tag;
            } else if ($this->criteria->criteria_display == "ANYCHAR" || $this->criteria->criteria_display == "TEXTFIELD") {
                $tag = "";
                $tag .= '<br><input  class="' . $this->criteria->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" type="text" name="MANUAL_' . $this->criteria->query_name . '"';
                $tag .= ' size="50%"';
                $tag .= ' value="' . $value_string . '">';
                $tag .= '<br>';
                $text .= $tag;
            } else if ($this->criteria->criteria_display == "SQLCOMMAND") {
                $tag = "";
                $tag .= '<br><textarea  cols="70" rows="20" class="' . $this->criteria->lookup_query->getBootstrapStyle('textfield') . 'reportico-prepare-text-field" type="text" name="MANUAL_' . $this->criteria->query_name . '">';
                $tag .= $value_string;
                $tag .= "</textarea>";
                $text .= $tag;
            }
        }

        return $text;
    }
}
// -----------------------------------------------------------------------------
