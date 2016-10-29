<?php
$menu_title = SW_PROJECT_TITLE;
$menu = array (
	array ( "language" => "en_gb", "report" => ".*\.xml", "title" => "<AUTO>" )
	);
?>
<?php
$menu_title = SW_PROJECT_TITLE;
$menu = array (
	array ( "report" => "<p>Welcome to the Reportico demonstration and tutorials.<br>In the drop down menus above you will find some example reports and tutorials.<br>
<Br>To try these reports and tutorials you will need to create the tutorial database tables <br>which are based upon a sample stock and orders database known as Northwind.<br><br>Click the <i>Generate Tutorial Tables</i> below to create the tables in an already existing database<br> (the tables created all begin with the prefix northwind_ so they wont conflict with any existing tables).<br><br>Once you have created the tables you are ready to<br>run through the exercises which are documented in the <a style=\"text-decoration: underline !important\"  href=\"http://www.reportico.org/documentation/4.5/doku.php?id=reporticotutorial\">Reportico online documentation here</a>", "title" => "TEXT" ),
	//array ( "report" => "stock.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut2_orders.xml", "title" => "<AUTO>" ),
	array ( "report" => "", "title" => "BLANKLINE" ),
	array ( "report" => "generate_tutorial.xml", "title" => "Generate The Tutorial Tables" ),
	array ( "report" => "", "title" => "BLANKLINE" ),
	//array ( "report" => "tut1_1_stock.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut1_2_stock.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut1_3_stock.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut1_4_stock.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut2_1_orders.xml", "title" => "<AUTO>" ),
	//array ( "report" => "tut2_2_orders.xml", "title" => "<AUTO>"),
	);

$admin_menu = $menu;


$dropdown_menu = array(
                    array ( 
                        "project" => "northwind",
                        "title" => "Inventory",
                        "items" => array (
                            array ( "reportfile" => "customer.xml" ),
                            array ( "reportfile" => "products.xml" ),
                            array ( "reportfile" => "stock.xml" ),
                            array ( "reportfile" => "suppliers.xml" ),
                            )
                        ),
                    array ( 
                        "project" => "northwind",
                        "title" => "Financial",
                        "items" => array (
                            array ( "reportfile" => "orders.xml" ),
                            array ( "reportfile" => "salestotals.xml" ),
                            )
                        ),
                    array ( 
                        "project" => "northwind",
                        "title" => "Tutorials",
                        "items" => array (
                            array ( "reportfile" => "generate_tutorial.xml") ,
                            array ( "reportfile" => "tut1_1_stock.xml") ,
                            array ( "reportfile" => "tut1_2_stock.xml") ,
                            array ( "reportfile" => "tut1_3_stock.xml") ,
                            array ( "reportfile" => "tut1_4_stock.xml") ,
                            array ( "reportfile" => "tut1_5_stock.xml") ,
                            array ( "reportfile" => "tut2_1_films.xml") ,
                            array ( "reportfile" => "tut3_1_films.xml") ,
                            array ( "reportfile" => "tut4_1_films.xml") 
                            )
                        ),
                );

?>
