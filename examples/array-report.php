<?php 

include (__DIR__."/header.php");

$description = "
A report can be generated just from supplying an array of data ( which could have be returned from a function ).
<br><br>
Use the databasesource()->array() method to achieve this...
<br><br>
The report column labels are derived from the array index keys. The labels are automatically capitalised and any underscores are replaced by spaces.
";

$example_description = "In this is example an array is specified and passed to the report engine through the datasource array() method";

$code = 
            highlight_string('<?php
           

            $rows = [
                [ "id" => "1", "first_name" => "Nancy", "last_name" => "Davolio", "date_of_birth" => "1968-12-08", "country" => "USA" ],
                [ "id" => "2", "first_name" => "Andrew", "last_name" => "Fuller", "date_of_birth" => "1952-02-19", "country" => "USA" ],
                ....
            ];

            \Reportico\Engine\Builder::build()
            ->datasource()->array($rows)
            ->title("Employee List")
			->execute();
            ?>
            ', true);

$usage_method = "
<?php
    \Reportico\Engine\Builder::build()
    ...
    ...
    ->datasource()->array([ .. an array of data ..])
    ...
    ...
    ->execute();
?>;
";

include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
