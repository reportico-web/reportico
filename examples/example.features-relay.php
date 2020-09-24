<?php

     require_once(__DIR__ .'/../vendor/autoload.php');

     \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")

          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country, BirthDate
                FROM northwind_employees
                WHERE 1 = 1
                [ AND Country IN ( {USER_PARAM,country} ) ]
                ORDER BY Country, LastName
			    ")

          // Passes a hardcoded value to the report sql. Note the 'country' in the relay key must match the
          // USER_PARAM parameter above
          // the value passed could easily be a dynamic value 
          // passed from a form into the $_REQUEST php array.
          ->relay("country", "'UK','USA'")
		  ->execute();
?>
