\Reportico\Engine\Builder::build()
          ->datasource()
          ->database("mysql:host=localhost; dbname=reportico")
          ->user("peter")
          ->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")
          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country
                FROM northwind_employees
                WHERE 1 = 1
                ORDER BY Country, LastName
			    ")
		  ->expression("fullname")
                ->set("{first_name}.' '.{last_name}")
		  ->expression("age")
                ->set("date_diff(new DateTime(), new DateTime({birth_date}))->format('%y years %m months %d days')")
          ->column("first_name")->hide()
          ->column("last_name")->hide()
          ->column("fullname")->order(3)

		  ->execute();
