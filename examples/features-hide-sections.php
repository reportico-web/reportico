<?php
include (__DIR__."/header.php");


$description = "The hideSections() method allows different elements of the report output to be hidden. 
<br><br>
This allows production of more summary reports to show, for example, just charts or group summary data.
";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->hideSections(\"detail\"|\"critera\"|\"columnheaders\"|\"groupheaders\"|\"grouptrailers\"|\"charts\")
          ...
          ->execute();
?>
";

$example_description = "
The following example shows the stock report that includes a charts and hides the detail section, meaning that the report only shows the group and chart output.
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
