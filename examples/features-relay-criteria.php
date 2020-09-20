<?php
include (__DIR__."/header.php");


$description = "The relayCriteria() method behaves like the relay() method but allows passing of external values to the predefined Reportico criteria items.  For example, you may wish to provide a user criteria item provided from within your web application (which may be picked up from the \$_REQUEST array) and pass that through as a criteria filter to the Reportico query. The crtieria key passed through must equate to the name of a criteria item in the report.
<br><br>
You can pass either an array of parameters to the method or pass key and value pairs as strings in a number of relay calls.
<br><br>
To pass a set of multiple selection values to a criteria just pass the values delimited by a comma.
<br><br>
To pass a date range pass in the format startdate-enddate in <i>yyyy-mm-dd</i> format, for example, <i>yyyy-mm-dd-yyyy-mm-dd</i>.
";

$usage_method = "
<?php
      \Reportico\Engine\Builder::build()
          ...
          ->relay([array of parameters expressed as key value pairs])
          ->relay(key_string,value_string)
          ...
          ->execute();
?>
";

$example_description = "
The following example runs the stock report, passing in the USA and Japan as report filter options. The key of <b>country</b> relates to the criteria name defined in the criteria() method.
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
