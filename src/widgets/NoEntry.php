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

class NoEntry extends Widget
{
    public $value = false;
    public $expanded = false;

    public function __construct($engine)
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [ ];

        return
            [
                'name' => 'no-entry',
                'type' => 'criteria-selection',
                'title' => 'Text Field',
                'renderType' => 'NOINPUT',
                'sourceType' => 'TEXTFIELD',
                'order' => 1,
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

        if ( $this->criteria ) {
            $this->value = $this->criteria->column_value;
            if ( $this->expanded ) {
                $name = "EXPANDED_".$this->criteria->query_name;
                $length = $this->criteria->column_length;
                if ( !$length )
                    $length = 999;
            }
            else {
                $name = "MANUAL_".$this->criteria->query_name;
                $length = $this->criteria->column_length;
                if ( !$length )
                    $length = 999;
            }
        }

        $text = "<input  type='text' name='$name'";
        $text .= " class='$class'";
        $text .= " value='" . $this->value . "'>";

        return $text;
    }
}
// -----------------------------------------------------------------------------
