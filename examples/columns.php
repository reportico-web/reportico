<?php 

include (__DIR__."/header.php");

$description = "
Each column in the report output can be configured in a number of ways using the builder. Use the column method passing the name of the column and apply one of the following options :-
<br><br>
<ul>
    <li>hide - hides the column from the report body.</li>
    <li>order - sets which column the data will appear in the report body table</li>
    <li>label - Alternative label to be placed in column headers and group sections</li>
    <li>columnwidth - How wide the column should be. Use css notation</li>
    <li>justify - Sets whether to left, right or centre justify the column value</li>
</ul>
";

$example_description = "In this is example an array is specified and passed to the report engine through the datasource array() method";


$usage_method = "column";
                  

$example_description = "
In this example, we play with some basic display options for each column. 
<p>
The justify method allows contents to be left, center or right justified. You can change the width of a column with the columnwidth() method and also use the order method to change the order of the columns in the output results.
";


include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>

