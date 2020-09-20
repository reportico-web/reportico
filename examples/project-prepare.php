<?php
include (__DIR__."/header.php");

$description = " <br><br> The prepare() method presents the report criteria entry and output selection screen. This presents defined criteria filters defined in the report and provides access to page settings and the report description.";
$example_description = "In the example below, we load the tutorials project and present the stock report in criteria entry mode";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->project(\"project\")
          ->load(\"reportfile\")
          ->prepare();
?>
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
