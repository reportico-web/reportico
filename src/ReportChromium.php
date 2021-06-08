<?php
/*

 * File:        Report_pdf.php
 *
 * Base class for all report output formats.
 * Defines base functionality for handling report
 * page headers, footers, group headers, group trailers
 * data lines
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */
namespace Reportico\Engine;

use Spatie\Browsershot\Browsershot;




class ReportChromium extends Report
{
    private $client = false;


    private $pageSizes = [ 
        "B5" => array("height" => 176, "width" => 125),
        "A6" => array("height" => 148, "width" => 105),
        "A5" => array("height" => 210, "width" => 148),
        "A4" => array("height" => 297, "width" => 210),
        "A3" => array("height" => 420, "width" => 297),
        "A2" => array("height" => 594, "width" => 420),
        "A1" => array("height" => 841, "width" => 594),
        "A0" => array("height" => 1189, "width" => 841),
        "US-Letter" => array("height" => 279, "width" => 216),
        "US-Legal" => array("height" => 356, "width" => 216),
        "US-Ledger" => array("height" => 432, "width" => 279)
        ];

    public function __construct() {
        $this->column_spacing = 0;
    }

    public function pageWidth($pageSize)
    {
        return isset($this->pageSizes[$pageSize]["width"]) ? $this->pageSizes[$pageSize]["width"] : 0; 
    }

    public function pageHeight($pageSize)
    {
        return isset($this->pageSizes[$pageSize]["height"]) ? $this->pageSizes[$pageSize]["height"] : 0; 
    }

    /**
     * @param bool $engine
     */
    public function start($engine = false)
    {
        $sessionClass = ReporticoSession();

        // Build URL - dont include scheme and port if already provided
        $url = "{$engine->reportico_ajax_script_url}?execute_mode=EXECUTE&reportico_headless=1&target_format=HTML2PDF&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $script_url = $engine->reportico_ajax_script_url;
        if ( !preg_match("/:\/\//", $url) ) {
            if ( substr($script_url, 0, 1) != "/" )
                $script_url = "/$script_url";

            $url = "${_SERVER["REQUEST_SCHEME"]}://${_SERVER["HTTP_HOST"]}{$script_url}?execute_mode=EXECUTE&reportico_headless=1&target_format=HTML2PDF&reportico_session_name=" . $sessionClass::reporticoSessionName();
        }

        // Add in any extra forwarded URL parameters
        if ($engine->forward_url_get_parameters)
            $url .= "&".$engine->forward_url_get_parameters;

        // Add any CSRF tokens for when Reportico is called inside a framework
        // And retain any cookies too
        $newHeaders = [];

        if ( $engine->csrfToken ) {

            // Its Laravel or October
            $oldHeaders = getallheaders();

            if ( isset($oldHeaders["Cookie"]) )
                $newHeaders["Cookie"]= $oldHeaders["Cookie"];
            if ( isset($oldHeaders["cookie"]) )
                $newHeaders["Cookie"]= $oldHeaders["cookie"];
            $newHeaders["X-CSRF-TOKEN"]= $engine->csrfToken;

            if ( isset($oldHeaders["OCTOBER-REQUEST-PARTIALS"]) )
                $newHeaders["OCTOBER-REQUEST-PARTIALS"]= $oldHeaders["OCTOBER-REQUEST-PARTIALS"];
            if ( isset($oldHeaders["X-OCTOBER-REQUEST-HANDLER"]) )
                $newHeaders["OCTOBER-REQUEST-HANDLER"]= $oldHeaders["X-OCTOBER-REQUEST-HANDLER"];
            //$request->setHeaders($newHeaders);
        }

        // Generate temporary name for pdf file to generate on disk. Since phantomjs must write to a file with pdf extension use tempnam, to create a file
        // without PDF extensiona and then delete this and use the name with etension for phantom generation
        $outputfile = tempnam($engine->pdf_phantomjs_temp_path, "pdf");

        unlink($outputfile);
        $outputfile .= ".pdf";
        $outputfile = preg_replace("/\\\/", "/", $outputfile);

        // Since we are going to spawn web call to fetch HTML version of report for conversion to PDF,
        // we must close current sessions so they can be subsequently opened within the web call
        $sessionClass::closeReporticoSession();

        try {
        $x = Browsershot::url($url)
            //->save($outputfile);
            ->setExtraHttpHeaders($newHeaders)
            ->ignoreHttpsErrors()
            ->paperSize($this->pageWidth($engine->getAttribute("PageSize")), $this->pageHeight($engine->getAttribute("PageSize")), "mm")
            ->setOption('args', ['--disable-web-security'])
            //->emulateMedia('print')
            ->noSandbox()
            ->landscape()
            ->showBackground()
            //->margins(0,0,0,0)
            ->setDelay(1000)
            ->save("$outputfile")
            ;
        } catch ( \Exception $ex ) {
            header("HTTP/1.0 500 Server Error", true);
            echo '<div class="reportico-error-box">Unable to generate PDF report. The pdf engine is configured within Reportico as \'chromium\' and it is likely that either of the node/npm or puppeteers packages are not installed. This functionality may not be available if Reportico is installed on a public hosting service as some hosting providers do not allow third party executables to be installed. 
            <p><p>
            If you are running Reportico on a system under your control or your hosting provider will allow node/npm and puppeteer to operate, then you will need to set up npm and install puppeteer. If you are using just standalone Reportico then this is a case of running \'npm install\' in the Reportico folder, this will download the necessary Chrome application for PDF generation. For using Reportico as a package embedded in your own application, for example a Laravel system, you will need to setup the package.json in your own root folder and run npm there.
            <p><p>
            Alternative if you change your pdf_engine configuration to "tcpdf" you can get a more basic pdf output without this setup.
            <p><p>
            '.$ex->getMessage().'
            </div>';
            die;
        }

        $this->reporttitle = $engine->deriveAttribute("ReportTitle", "Set Report Title");
        if (isset($engine->user_parameters["custom_title"])) {
            $reporttitle = $engine->user_parameters["title"];
            //$engine->setAttribute("ReportTitle", $reporttitle);
        }

        $attachfile = "reportico.pdf";
        $reporttitle = ReporticoLang::translate($engine->deriveAttribute("ReportTitle", "Unknown"));
        if ($reporttitle) {
            $attachfile = preg_replace("/ /", "_", $reporttitle . ".pdf");
        }

        // INLINE output is just returned to browser window it is invoked from
        // with hope that browser uses plugin
        if ($engine->pdf_delivery_mode == "INLINE") {
            header("Content-Type: application/pdf");
            echo file_get_contents($outputfile);
            unlink($outputfile);
            die;
        }

        // DOWNLOAD_SAME_WINDOW output is ajaxed back to current browser window and then downloaded
        else if ($engine->pdf_delivery_mode == "DOWNLOAD_SAME_WINDOW" /*&& $engine->reportico_ajax_called*/) {
            header('Content-Disposition: attachment;filename=' . $attachfile);
            header("Content-Type: application/pdf");

            // Dont encode to browser media format if called from a builder session
            // Without the front end being involved
            if ( $engine->report_from_builder_first_call ){
                echo file_get_contents($outputfile);
                unlink($outputfile);
            } else {
                // Dont encode if called from builder session
                $buf = base64_encode(file_get_contents($outputfile));
                unlink($outputfile);
                //$buf = file_get_contents($outputfile);
                $len = strlen($buf);
                echo $buf;
            }
            die;
        }
        // DOWNLOAD_NEW_WINDOW new browser window is opened to download file
        else {
            header('Content-Disposition: attachment;filename=' . $attachfile);
            header("Content-Type: application/pdf");
            echo file_get_contents($outputfile);
            unlink($outputfile);
            die;
        }
        die;
    }

}
