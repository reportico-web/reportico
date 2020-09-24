<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->project("tutorials")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")
          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country as country
                FROM northwind_employees
                WHERE 1 = 1
                ORDER BY Country, LastName
			    ")

          ->expression("country")
              ->set("{country}")
              ->drilldownToUrl("example.drilldown-sub-report.php")->where(["country" => "country"])
              //->drilldownToReport("tutorials", "stock")->where(["country" => "country"])
		  ->execute();
?>
