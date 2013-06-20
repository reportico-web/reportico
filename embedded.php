<?php
	ob_start();
?>
<H1>EMBEDDED REPORT DEMONSTRATION</H1>
<H1>START OF WEB PAGE TEXT FOLLOWED BY REPORTICO OUTPUT</H1>
<?php
    date_default_timezone_set(@date_default_timezone_get());

	error_reporting(E_ALL);

	require_once('reportico.php');
	$a = new reportico();
	$a->allow_maintain = "FULL";
	$a->allow_debug = true;
	$a->embedded_report = true;
	$a->execute();
?>

<H1>END OF WEB PAGE TEXT FOLLOWING REPORTICO OUTPUT</H1>
<?php
// print out footer information
	ob_end_flush();
?>
