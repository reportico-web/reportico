<?php
//ob_start();
?>

<H1>START OF WEB PAGE TEXT BEFORE REPORTICO OUTPUT</H1>

<?php

// Include the Reportico library
require __DIR__.'/../vendor/autoload.php';

$q = new Reportico\Engine\Reportico();

// Paths to reportico for embedding assets and themes
// Set $reportico_url_home to the url path to your reportico installation
$reportico_home = __DIR__."/../";
$reportico_url_home = "{url_path_to_reportico}";
$q->reportico_ajax_script_url = $reportico_url_home."/run.php";
$q->url_path_to_assets = $reportico_url_home."/assets";
$q->url_path_to_templates = $reportico_url_home."/themes";

// Required PDF Engine set -- to phantomjs or tcpdf
$q->pdf_engine = "phantomjs";
$q->pdf_phantomjs_path = $reportico_home."/bin/phantomjs";

// Download pdf output in same window - see run.php for more options
$q->pdf_delivery_mode = "INLINE";


$q->setTheme('default');
$q->access_mode = "ONEREPORT";
$q->initial_execute_mode = "EXECUTE";
$q->initial_project = "tutorials";
$q->initial_report = "stock";
$q->initial_output_format = "PDF";

// Bootstrap options - specify true to preloaded if you already have bootstrap
$q->bootstrap_preloaded = false;
$q->bootstrap_styles = "3";

// IMPORTANT - initialises session for initial parameters above to take effect
$q->clear_reportico_session = true;

// Initialize session
Reportico\Engine\ReporticoSession::setUpReporticoSession($q->session_namespace);

// Generate Reportico Page
$q->execute();
?>

<H1>END OF WEB PAGE TEXT FOLLOWING REPORTICO OUTPUT</H1>
<?php
// print out footer information
//ob_end_flush();
?>
