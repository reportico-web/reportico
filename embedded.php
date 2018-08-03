<?php
	ob_start();
?>
<H1>START OF WEB PAGE TEXT BEFORE REPORTICO OUTPUT</H1>

<?php
require_once('vendor/autoload.php');
$q = new Reportico\Engine\Reportico();
$q->pdf_delivery_mode = "DOWNLOAD_SAME_WINDOW";
$q->setTheme('default');
$q->access_mode = "ONEPROJECT";
$q->initial_execute_mode = "PREPARE";
$q->initial_project = "tutorials";
$q->initial_report = "stock";
$q->bootstrap_styles = "3";
$q->force_reportico_mini_maintains = false;
$q->bootstrap_preloaded = false;
$q->clear_reportico_session = true;
$q->reportico_ajax_mode = true;
$q->execute();
?>

<H1>END OF WEB PAGE TEXT FOLLOWING REPORTICO OUTPUT</H1>
<?php
// print out footer information
	ob_end_flush();
?>
