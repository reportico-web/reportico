<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")
          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country
                FROM northwind_employees
                ORDER BY Country, LastName
			    ")
          ->group("employee_id")
            ->throwPageBefore()
		  ->page()->formLayout()->paginate()
		  ->execute();
?>
