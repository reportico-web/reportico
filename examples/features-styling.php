<?php 

include (__DIR__."/header.php");

$description = "
Expressions allow you to call php syntax and call functions to create new columns and modify existing column values.
<br><br>
For example you may wish to make a format change to a column such as round a number or capitalise a string.
<br><br>
Or you may wish to generate a hyperlink based on the value of a column.
<br><br>
Or you may wish to make a value go red if it exceeds a certain value.
<br><br>
You use expressions if you want to create a running total or aggregation on an existing column in order to display at the bottom of the section.
";
$example_description = "
In this example, we have added two assignments.
<br><br>
The first concatenates the first and second name together into a new full name column, 
and then uses the column() method to hide the first and second name from the report.
<br><br>
The second uses the PHP in built date_diff function to calculate the age of the employee by taking the birth date away from the current date.
<br><br>
Note that in both cases we are deriving these expressions based on columns selected in the query and so they are fed into the expression()->set() method
using the {} notation. 
<br><br>
The expressions passed have to be in valid PHP and be careful to ensure that passed in columns are not in quotes.
So in the concatenation exaple we use <br>
<pre>
                ->set(&nbsp;\"{first_name}.' '.{last_name}\"&nbsp;)
</pre>
not <br>
<pre>
                ->set(\"&nbsp;'{first_name} {last_name}'&nbsp;\")
</pre>
";


include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>

