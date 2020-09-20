<?php
include (__DIR__."/header.php");

$description = "
<br><br>
The group() method allows the report to be split into sections grouped by a specific column value.
Each section can have headers and trailers and throw a page before on group using the throwPageBefore() method.
<br><br>
For headers, the header() method will display the selected column at the top of each section instead of in the main report table and the customHeader() method allows you specify a complete block of text to show before each section, position and styled according to the passed css definition.
<br><br>
With trailers, you have column trailers and custom trailers. Column trailers allow a particular report column to be displayed at the end of each group underneath another column to provide, for example, a total or average at the end of a group. In order to show a group sum or other aggregate you need to create a new expression and provide the column to aggregate on (see the example code on this page). Then you can create a trailer and set the column underneath which to place the trailer. The customTrailer() option, like customHeader() allows you to display a text block after each group styled using a css block.
";


$example_description = "
In this example, we are showing the stock levels of each product grouped by product category. The expression() method is used to create a running count of the stock level grouped by category so that we can show group total under the in stock column
<br><br>
The customTrailer() method is used to create a right adjusted div with a dark background to combine the category and count into a summary box.The column values are included using the {columnname} notation.
The customHeader() does the same but places the block at the start of the group.
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
