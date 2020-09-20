<?php
include (__DIR__."/header.php");


$description = "The relay() method allows values external to Reportico to be passed in for use in the report. For example, you may wish to pass a string for use in the report query which may have been provided from your web application (which may be picked up from the \$_REQUEST array).
<br><br>
You can pass either an array of parameters to the method or pass key and value pairs as strings in a number of relay calls.
<br><br>
The key value passed must equate to the name of a criteria item created in the query. If you are passing multiple criteria filter values then the values can be separated by commas.
<br><br>
To use the parameters in the report to pass to a criteria element, use the notation 
<PRE>
{USER_PARAM,relay_parameter_key}
</PRE>
For example :-
<PRE>
SELECT x, y
FROM table
WHERE column = \"{USER_PARAM,my_parameter}\"
</PRE>
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
The following example runs the stock report, passing in the USA and Japan as report filter options. Note that we pass through the options with quotes in this example because the values are used within an SQL IN clause.
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
