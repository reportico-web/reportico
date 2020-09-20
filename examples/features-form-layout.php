<?php
include (__DIR__."/header.php");


$description = "The formLayout() method renders each column value on a separate line instead of as the default table format. 
";


$example_description = "In the example below we show the report data as a form and we force a page break on each row. In order to get the page break working, we use the group() method to force each row to be treated as a group section (by grouping on a unique id column ). and then use the throwPageBefore() method to trigger the page break.";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->formLayout()
          ...
          ->execute();
?>
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
