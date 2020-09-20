<?php
include (__DIR__."/header.php");

$description = "
<br><br>
The builder() method starts the report building process. Methods can subsequently be called on the returned object to set the connection to the datasource, provide the query and any further options such as criteria, grouping, expressions, styling etc.
<br><br>
Before writing queries using the Reportico as a framework library you need to ensure it is included and available for use in your code. When using Reportico as a standalone module you need to include the Reportico composer package with command :-
<br><br>
<pre>
require_once('<path to reportico folder>/vendor/autoload.php');
</pre>
<br>
Then wherever in your code you need to include a Reportico report menu, a report criteria selection screen or just report output, begin the query with
<br><br>
<pre>
\Reportico\Engine\Builder::build()
</pre>
<br>
Then you chain further methods on to include the database selection and create the report definition.
<br><br>
<pre>
\Reportico\Engine\Builder::build()
    ->datasource()->database(\"mysql:host=localhost; dbname=DATABASE NAME\")->user(\"USER\")->password(\"PASSWORD\")
    ->sql(\"SELECT column1 FROM table1\")
    ...
</pre>
<br>
Then you must finish the report with one of the following methods :-
<ul>
    <li>execute() - Runs the report and sends the output to the desired output format (defaults to HTML in the browser)</li>
    <li>prepare() - Presents you with a selection screen to choose report criteria and output format
    <li>menu() - Presents menu of reports you have already written under a project. Only works where you have used the designer to create a project
</ul>
<br>
If you do not specify any of these options no output will appear.
<br><br>
<pre>
\Reportico\Engine\Builder::build()
    ->datasource()->database(\"mysql:host=localhost; dbname=DATABASE NAME\")->user(\"USER\")->password(\"PASSWORD\")
    ->sql(\"SELECT column1 FROM table1\")
    ->execute();
</pre>
";


$example_description = false;
?>

<?php
include (__DIR__."/content.php");
?>

<?php
include (__DIR__."/trailer.php");
?>
