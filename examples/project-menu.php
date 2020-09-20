<?php
include (__DIR__."/header.php");

$description = " <br><br> The menu() method presents the report project menu and allows user access to all reports saved within the project folder. There must already have been a report created from the Reportico Administration menu and reports created in the designer.";
$example_description = "In the example below, we load the tutorials project and present the menu";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->project(\"project\")
          ->menu();
?>
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
