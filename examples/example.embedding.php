<HTML>
    <BODY style="background-color:#ddd">

            <DIV style="text-align: center;width:50%; margin-left:25%;background-color:#fff">
        <H3>Example Report Embedded in a Page </H3>
                <p>
                   <h3>Start of Page Content</h3>
<?php

    require_once(__DIR__ .'/../vendor/autoload.php');

    \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Employee List")
          ->description     ("Produces a list of our employees")
          ->sql       ("
                SELECT EmployeeID employee_id, LastName last_name, FirstName first_name, BirthDate birth_date, Country
                FROM northwind_employees
                ORDER BY Country, LastName
                ")
          ->execute();
?>
                <p>
                   <h3>End of Page Content</h3>
        </DIV>
    </BODY>
</HTML>
