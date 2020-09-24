<?php

     require_once(__DIR__ .'/../vendor/autoload.php');

     \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")

          // The sql for the main report makes reference to 4 criteria items specified further below.
          // The value between the {curly braces} links in the criteria item by name and is replaced at report time
          // by the users selected values. Where a criteria selections are generated from a lookup, the values come from the criteria
          // return() column.
          //
          // For criteria items that are ranges e.g. date or time ranges, specifiying {criteriarange} expands to a BETWEEN/AND clause and to
          // access the lower and upper range selections use {criteriarange,from} and {criteria,to}
          //
          // For multiselects, it is often appropriate to use the SQL IN () syntax so that IN ({criteria}) is replaced by 
          // IN ( \'selection1\', \'selection2\', ... )
          //
          // By default the values plugged into the SQL are surrounded by quotes and smetimes you need to plug into the SQL selections
          // which have no quotes, for example with the match string example below where you want to surround the selection with % signs to
          // perform and SQL LIKE operation. In this case use false as the final parameter for example {criteria,false} or with a range 
          // {daterange,from,false}
          ->sql       ("
			    SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, date(BirthDate) birth_date, Country, BirthDate
                FROM northwind_employees
                WHERE 1 = 1
                [ AND Country IN ( {country} ) ]
                [ AND DATE(BirthDate) BETWEEN {born,from} AND {born,to} ]
                [ AND ( LastName like '%{namematch,false}%' OR FirstName like '%{namematch,false}%' ) ]
                [ AND ( EmployeeID in ( {namemulti} ) ) ]
                ORDER BY Country, LastName
			    ")

          // This criteria generates a multiselect list box from an SQL statement
          // giving a list of countries. The "country" column is displayed in the box and 
          // also returned in the return() method which matches the {country} reference in the main SQL. 
          // The {country} specifier in the main query will be replaced by the selected countries
          ->criteria("country")
            ->title("Country")
            ->type("lookup")
            ->sql("SELECT DISTINCT Country country FROM northwind_employees")
            ->widget("multi")
            ->return("country")
            ->display("country", "country")
            ->match("country")

          // A date range criteria
          // This uses the default method to specify a default range. Specific dates can be set or
          // certain preefined values can be used. For example, FIRSTOFLASTMONTH-TODAY would show a
          // range form the first of the prior month to today. Other values are LASTOFLASTMONTH, FIRSTOFTHISMONTH.
          // YESTERDAY,TOMORROW,LASTOFTHISMONTH
          ->criteria("born")
            ->title("Born Between")
            ->type("daterange")
            ->default("1950-01-01-TODAY")

          // A simple text criteria where a match string can be entered, and in the main sql it is 
          // used without surrounding quotes to fetch all employees with first or last names matching the string
          ->criteria("namematch")
            ->title("Name Match String")
            ->type("textfield")
            ->tooltip("Report on all employees with names matching the entered string")

          // A searchable mutliselect criteria widget which uses the SQL CONCAT function to allow selection
          // from a list of full employee names. It returns the EmployeeID of the selected employees back to main
          // query SQL where the EmployeeID is used for the match.
          ->criteria("namemulti")
            ->title("Full Name Multi Select")
            ->type("lookup")
            ->sql("SELECT EmployeeID id, concat(FirstName, ' ', LastName) fullname FROM northwind_employees")
            ->widget("select2multiple")
            ->return("id")
            ->display("fullname", "fullname")
            ->match("CONCAT(FirstName, ' ', LastName)")
		  ->prepare();
?>
