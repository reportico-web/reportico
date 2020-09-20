<?php
include (__DIR__."/header.php");

$description = "
The expression method allows you to add drillldown link to another report based on the column values in a report row.
<br><br>
For a drilldown, you need either an existing reportico script to call to generate the sub report or a report project with a report that can be called.
<br><br>
The expression() method must be provided with a column name to get started. This identifies the column in the report output that will contain the drilldown hyperlink. You can either specify a brand new column to add into the report or specify an existing column the should be converted to the hyperlink. In the set() method that you then call you specify the text for the drill down link. This can be just a straight of test such as \"Click Here\" or you use the {column} notation to have the drilldown link include a the column value at run time. For example you might have a country column showing a country, so setting this value to {country} will show the actual country in the report but converted to a hyperlink.
<br>
Next is to specify the report to drill down to.
To provide a report column that drills down into another report, you use the expression method 
Just specify an array of the dropdown menus and specify the indivual items ... TO BE CONTINUED ..";

$usage_method = "
<?php
          \Reportico\Engine\Builder::build()
              ...
              ...
              ->expression(\"link\")
                  ->set(\"drill label which can include {columnm value}\")
                  ->drilldownToUrl(\"{url_to_sub_report}\")->where(
                                   [\"criteria_item_in_sub_report\" => \"column_containing_value_to_filter_on\"]
                                   [\"another_criteria_item_in_sub_report\" => \"another_column_containing_value_to_filter_on\"]
                                   ...
                                   )
                  ->drilldownToReport(\"{url_to_sub_report}\")->where(
                                   [\"criteria_item_in_sub_report\" => \"column_containing_value_to_filter_on\"]
                                   [\"another_criteria_item_in_sub_report\" => \"another_column_containing_value_to_filter_on\"]
                                   ...
                                   )
                ...
                ...
                ->execute();
?>";



include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
