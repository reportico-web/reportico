<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")
          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country
                FROM northwind_employees
                WHERE 1 = 1
                ORDER BY Country, LastName
			    ")

          ->accessLevel("ONEPROJECT")
		  ->dropdownMenu(
                    [
                    array ( 
                        "project" => "tutorials",
                        "title" => "Inventory",
                        "items" => array (
                            //array ( "reportfile" => "products.xml" ),
                            array ( "reportfile" => "stock.xml" ),
                            array ( "reportfile" => "suppliers.xml" ),
                            array ( "reportfile" => "customer.xml" ),
                            )
                        ),
                    array ( 
                        "project" => "tutorials",
                        "title" => "Financial",
                        "items" => array (
                            array ( "reportfile" => "orders.xml" ),
                            array ( "reportfile" => "salestotals.xml" ),
                            )
                        ),
                        ]
             )
		  ->prepare();
?>

