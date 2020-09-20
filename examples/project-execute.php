<?php
include (__DIR__."/header.php");

$description = " <br><br>To run an existing report stored by the designer just load the project, the report and erxecute it";
$example_description = "In the example, the tutorials projects is loaded, the stock report is loaded and executed";


$usage_method = "
<?php
\Reportico\Engine\Builder::build()
    ->project(\"project\")
    ->load(\"reportfile\")
    ->execute();
?>
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");

