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
        "B5" => array("height" => 709, "width" => 501),
        "A6" => array("height" => 421, "width" => 297),
        "A5" => array("height" => 595, "width" => 421),
        //"A4" => array("height" => 75, "width" => 55),
        "A4" => array("height" => 29.7, "width" => 21),
        "A3" => array("height" => 1190, "width" => 842),
        "A2" => array("height" => 1684, "width" => 1190),
        "A1" => array("height" => 2380, "width" => 1684),
        "A0" => array("height" => 3368, "width" => 2380),
        "US-Letter" => array("height" => 792, "width" => 612),
        "US-Legal" => array("height" => 1008, "width" => 612),
        "US-Ledger" => array("height" => 792, "width" => 1224)
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

    public function start($engine = false)
    {
        //node "C:\xampp72\htdocs\newarc\vendor\spatie\browsershot\src../bin/browser.js"


        $sessionClass = ReporticoSession();

        // Build URL - dont include scheme and port if already provided
        $url = "{$engine->reportico_ajax_script_url}?execute_mode=EXECUTE&target_format=HTML2PDF&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $script_url = $engine->reportico_ajax_script_url;
        if ( !preg_match("/:\/\//", $url) ) {
            if ( substr($script_url, 0, 1) != "/" )
                $script_url = "/$script_url";

            $url = "${_SERVER["REQUEST_SCHEME"]}://${_SERVER["HTTP_HOST"]}{$script_url}?execute_mode=EXECUTE&target_format=HTML2PDF&reportico_session_name=" . $sessionClass::reporticoSessionName();
        }

        // Add in any extra forwarded URL parameters
        if ($engine->forward_url_get_parameters)
            $url .= "&".$engine->forward_url_get_parameters;

        // Generate Request Call
        //$request = $this->client->getMessageFactory()->createPdfRequest($url, 'GET', 4000);

        // Add any CSRF tokens for when Reportico is called inside a framework
        // And retain any cookies too
        $newHeaders = [];

        if ( $engine->csrfToken ) {

            // Its Laravel or October
            $oldHeaders = getallheaders();

            if ( isset($oldHeaders["Cookie"]) )
                $newHeaders["Cookie"]= $oldHeaders["Cookie"];
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
        //$request->setOutputFile($outputfile);
        $outputfile = "test.pdf";

        //Browsershot::html('<h1>Hello world!!</h1>')
        $x = Browsershot::url($url)
            //->save($outputfile);
            ->setExtraHttpHeaders($newHeaders)
            ->paperSize($this->pageWidth($engine->getAttribute("PageSize")), $this->pageHeight($engine->getAttribute("PageSize")), "cm")
            //->paperSize($this->pageWidth($engine->getAttribute("PageSize")), $this->pageHeight($engine->getAttribute("PageSize")))
            //->paperSize("A4")
            //->setOption('landscape', strtoupper($engine->getAttribute("PageOrientation")) == "LANDSCAPE")
            ->setOption('args', ['--disable-web-security'])
            ->emulateMedia('print')
            //->margins(0,0,0,0)
            //->showBackground()
            //->save("peter7.pdf")
            ->save("$outputfile")
            ;

            //echo $x;
//echo $url; die;


        // Set otheHttpHeadersr PhantomJS parameters
        //$request->setFormat(strtoupper($engine->getAttribute("PageSize")));
        //$request->setOrientation(strtolower($engine->getAttribute("PageOrientation")));
        //$request->setMargin(0);
        //$request->setDelay(2);


        // Get Response
        //$response = $this->client->getMessageFactory()->createResponse();

        // Since we are going to spawn web call to fetch HTML version of report for conversion to PDF,
        // we must close current sessions so they can be subsequently opened within the web call
        $sessionClass::closeReporticoSession();

        // Send the request
        //$this->client->send($request, $response);
        //if( $response->getStatus() !== 200) {
            //header("HTTP/1.0 {$response->getStatus()}", true);
            //echo "Failed to produce PDF file error {$response->getStatus()} - <BR>Content:<b><BR>{$response->getContent()}</b> - <BR>";
            //die;
        //}

        //header('content-type: application/pdf');
        //echo file_get_contents('/tmp/document.pdf');
        //die;
        //die;

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
            $buf = base64_encode(file_get_contents($outputfile));
            unlink($outputfile);
            //$buf = file_get_contents($outputfile);
            $len = strlen($buf);
            echo $buf;
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
