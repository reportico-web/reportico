<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 * Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

use \Reportico\Engine\ReporticoApp;
use \Reportico\Engine\ReporticoLang;

class Core extends Widget
{
    // Define asset manager

    public function __construct($engine)
    {
        $this->options = [
            "csrfToken" =>  false,
            "ajaxHandler" =>  false,
        ];

        parent::__construct($engine);
        $this->manager = $engine->manager;
    }

    public function getConfig() {

        $csrfToken = $this->options["csrfToken"];
        $ajaxHandler = $this->options["ajaxHandler"];

        $sessionClass = \Reportico\Engine\ReporticoSession();

        /*
         * Calculate base url folder path to finder Reportico ajax runner ajax calls
         */
        $submitSelf = $this->engine->getActionUrl();
        $forward = $sessionClass::sessionRequestItem('forward_url_get_parameters', '');
        if ($forward)
            $submitSelf .= "?" . $forward;

        /*
         * Calculate Ajax mode .. whether standalone or within a framework, will be set to laravel, yii2 etc
         * if framework
         */
        $ajaxMode = $this->engine->reportico_ajax_mode;

        /*
         * Calculate Ajax endpoint, the full url to use when making ajax calls to Reportico
         */
        $ajaxEndpoint = $this->engine->reportico_ajax_script_url;

        /*
         * Calculate PDF Deliver mode
         */
        $pdfMode = $this->engine->pdf_delivery_mode;

        if ($this->engine->xmlinput == "configureproject.xml" || ReporticoApp::getConfig("project") == "admin")
            $reporttitle = $this->engine->deriveAttribute("ReportTitle", "Set Report Title");
        else
            $reporttitle = ReporticoLang::translate($this->engine->deriveAttribute("ReportTitle", "Set Report Title"));


        $themedir = $this->engine->url_path_to_templates."/".$this->engine->theme;

        $runtime = [
            "
// Core Javascript Variables
reportico_this_script = '$submitSelf';
reportico_ajax_script = '$submitSelf';
reportico_ajax_mode = '$ajaxMode';
reportico_ajax_runner = '$ajaxEndpoint';
reportico_pdf_delivery_mode = '$pdfMode';
reportico_report_title = '$reporttitle';
reportico_css_path = '$themedir/css/reportico.css';
" ];

        if ( $csrfToken ) {
            $runtime[0] .=
"
reportico_csrf_token = '$csrfToken';
ajax_event_handler = '$ajaxHandler';
"
                            ;
        }


        if ( $this->engine->reportico_ajax_preloaded )
            return
            [
                'name' => 'core',
                'order' => 1000,
                'files' => [
                    'css' => [
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/js/download.js",
                    ],
                    'events' => [
                        'runtime' => $runtime
                    ],
                ],
                'require' => [
                    'bootstrap3'
                ]
            ];

        return
            [
                'name' => 'core',
                'order' => 1000,
                'files' => [
                    'css' => [
                        //"{$this->engine->url_path_to_assets}/css/reportico-bundle.css",
                        "{$this->engine->url_path_to_templates}/{$this->engine->theme}/css/reportico.css"
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/js/download.js",
                        //"{$this->engine->url_path_to_assets}/js/reportico-bundle.js",
                        "{$this->engine->url_path_to_assets}/js/reportico.js"
                    ],
                    'events' => [
                        'runtime' => $runtime
                    ]],
                'require' => [
                    'bootstrap3'
                ]
            ];
    }
}
// -----------------------------------------------------------------------------
