<?php

include (__DIR__."/header.php");

$description = "A report can be generated just from supplying the database connection details and an sql statement. The report column labels are derived from the column names, so if a column is for example customer_address, then the label will automatically be set to \"Customer Address\". Use an SQL alias to specify a better label. Also use an alias for SQL expressions like COUNT(*) or SUM(x) to get a sensible column label.
<br><br>
Note that you cannot yet use the SELECT * notation you have to specify the individual columns.
<br><br>";


$usage_method = "<?php
    \Reportico\Engine\Builder::build()
        ...
        ...
        ->datasource()->database(\"mysql:host=localhost; dbname=<DATABASENAME>\")->user(\"<DATABASE USER>\")->password(\"<DATABASE PASSWORD>\")
        ->sql       (\"
            SELECT column1, column2, column3 AS some_alias, expression * 2 as some_label
            FROM table
            ORDER BY column1
            \")
        ...
        ...
        ->execute();
    ?>
    ";


include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
