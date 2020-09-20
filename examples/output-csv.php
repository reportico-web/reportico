<?php
include (__DIR__."/header.php");

$description = "To create a CSV report on the fly use the method <b>to(\"CSV\")</b> as shown below.";

$usage_method = "
<?php
          \Reportico\Engine\Builder::build()
              ...
              ...
              ->to(\"CSV\")
              ->execute();
?>";


$example_description = "<a class=\"btn button btn-primary\" target=\"_BLANK\" href=\"{{ url('page/output-csv-standalone') }}\" >Click for a CSV report</a>.";
$example_description = "";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
