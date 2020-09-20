<?php
include (__DIR__."/header.php");

$description = "The dropdown feature allows a simple interface to join your reports under a single menu item.
Just specify an array of the top level dropdown menus and specify the indivual items within. Currently, as in the example below, the menu items must
point to reports al ready defined in a specified project.";

$usage_method = '<?php
          \Reportico\Engine\Builder::build()
              ...
              ...
              ->dropdownMenu(
                  [
                        [
                            "project" => "tutorials",
                            "title" => "First Dropmenu Title",
                            "items" => array (
                                array ( "reportfile" => "stock.xml" ),
                                array ( "reportfile" => "suppliers.xml" ),
                                array ( "reportfile" => "customer.xml" ),
                                )
                            ],
                        [
                            "project" => "tutorials",
                            "title" => "Financial",
                            "items" => array (
                                array ( "reportfile" => "orders.xml" ),
                                array ( "reportfile" => "salestotals.xml" ),
                                )
                            ],
                  ]
                )
                ...
                ...
                ->menu(); or ->prepare();
    ?>';


include (__DIR__."/content.php");
include (__DIR__."/trailer.php");
?>
