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

class StatusMessageBlock extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $engineCriteria = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $engineCriteria = false )
    {

        parent::__construct($engine);

    }

    public function getConfig() {

        $init = [ ];

        $criteria = "";
        foreach ( $this->engine->lookup_queries as $v ) {
            $criteria .= "    reportico_criteria_items.push('$v->query_name');\n";
        }
        $runtime = [];
        $trigger = [

            ];

        return
            [
                'name' => 'status-message-block',
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
        $lastval = false;
        $duptypect = 0;

        $ct = 0;
        $errors = &ReporticoApp::getSystemErrors();

        $msg = "";
        foreach ($errors as $val) {

            if ($val["errno"] == E_USER_ERROR || $val["errno"] == E_USER_WARNING || $val["errno"] == E_USER_NOTICE) {
                if ($ct++ > 0) {
                    $msg .= "<HR>";
                }

                if ($val["errarea"]) {
                    $msg .= $val["errarea"] . " - ";
                }

                if ($val["errtype"]) {
                    $msg .= $val["errtype"] . ": ";
                }

                $msg .= $val["errstr"];

                $msg .= $val["errsource"];
                $msg .= "\n";
            } else {
                // Dont keep repeating Assignment errors
                if ($ct++ > 0) {
                    $msg .= "<HR>";
                }

                if ($val["errarea"]) {
                    $msg .= $val["errarea"] . " - ";
                }

                if ($val["errtype"]) {
                    $msg .= $val["errtype"] . ": ";
                }

                if (isset($val["errstr"]) && $val["errstr"]) {
                    $msg .= "{$val["errfile"]} Line {$val["errline"]} - ";
                }

                $msg .= $val["errstr"];
                $duptypect = 0;
            }
            $lastval = $val;
        }
        if ($duptypect > 0) {
            $msg .= "<BR>$duptypect more errors like this<BR>";
        }

        // Debug Info
        $debug = ReporticoApp::getSystemDebug();
        $debugmsg = "";
        foreach ($debug as $val) {
            $debugmsg .= "<hr>" . $val["dbgarea"] . " - " . $val["dbgstr"] . "\n";
        }

        if ($debugmsg) {
            $debugmsg = "<BR><B>" . ReporticoLang::templateXlate("INFORMATION") . "</B>" . $debugmsg;
        }

        $sections = [];
        if ( $msg )
            $sections["error"] = $msg;
        if ( $debugmsg )
            $sections["debug"] = $debugmsg;
        if ( $this->engine->status_message )
             $sections["info"] = $this->engine->status_message;

        return $sections;
    }
}
// -----------------------------------------------------------------------------
