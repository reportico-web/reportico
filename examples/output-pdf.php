<?php
include (__DIR__."/header.php");

$description = "
To create a PDF report on the fly use the method <b>to(\"PDF\")</b> as shown below.
Reportico ships with two types of PDF generator, TCPDF and Chromium and depending on your environment you will eed to choose which is best for you.
<br><br>
The <b>tcpdf</b> option is the default and will work on all environments but produces an output which is slightly different from the output shown in the browser.
<br><br>
The <b>chromium</b> pdf engine uses the Chromium browser to render the output which looks exactly as it is in the browser. However in order to use this you will to ensure that Node and the Node package manager is installed and install the necessary puppeteer node package. This option may not be available if you are running Reportico on a hosted system provided by an ISP that does not allow running of custom applications. If your system does allow Node to run or you have control over your system then you can install the Chromium/Puppeteer pdf engine as follows :-
<br><br>
Ensure you have node and npm installed in your system.
<br><br>
In a command shell move to your application folder. If you are using Reportico within a web framework such as Laravel or including Reportico in your web pages this will be your web appplication root folder. Otherwise, if using Reportico in standalone mode its the top level of your Reportico directory.
<br><br>
Ensure you have a package.json file in the necessary structure defined by npm and add puppeteer as a dependency as follows.
<br><br>
<pre>
    \"dependencies\": {
        ...
        \"puppeteer\": \"^5.2.1\"
    },

</pre>
Then run <b>npm install</b> which will install the puppeteer pdf engine and download the necessary headless Chromium which it uses.
<br><br>
To set Chromium as the default pdf generator you you can set it as follows :-
<ul>
<li> For standalone Reportico.php edit the start.php and find and set the pdf_engine setting <pre>\$reportico->pdf_engine = \"chromium\";</pre>
<li> For Laravel change vendor/reportico/laravel-reportico/src/config.php and find and set <pre>'pdf_engine' => \"chromium\"</pre>
</ul>

<h3>Download Method</h3>
<br><br>
The pdfDownLoadMethod() allows the choice of whether to show the report inline in the browser or download to your local machine either from a new browser window or the current browser window. Pass either inline, newwindow or samewindow.
<br><br>
<p>
<a class=\"btn button btn-primary\" target=\"_BLANK\" href=\"output-pdf-chromium.php\">Click for a Chromium report</a>
<a class=\"btn button btn-primary\" target=\"_BLANK\" href=\"output-pdf-tcpdf.php\">Click for a TCPDF report</a>.
";

$usage_method = "
<?php
          \Reportico\Engine\Builder::build()
              ...
              ...
              ->to(\"PDF\")
              ->pdfEngine(\"tcpdf\" | \"chromium\" )
              ->pdfDownloadMethod(\"inline\" | \"samewindow\" | \"newwindow\" )
              ->execute();
?>";


include (__DIR__."/content.php");
include (__DIR__."/trailer.php");

?>
