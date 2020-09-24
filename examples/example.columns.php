<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Product Stock")
          ->description     ("Produces a list of our employees")
          ->sql       ("
              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, UnitsOnOrder on_order, companyname company, country, categoryname category
              FROM northwind_products 
              join northwind_suppliers on northwind_products.supplierid = northwind_suppliers.supplierid
              join northwind_categories on northwind_products.categoryid = northwind_categories.categoryid
              WHERE 1 = 1  
              ORDER BY categoryname
                ")
          ->expression("total_stock")->sum("in_stock","category")
          ->group("category")
              ->throwPageBefore()
              ->trailer("total_stock")->below("in_stock")->label("Total")
              ->header("category")
          ->column("in_stock")->justify("right")->label("Right justified")       // Right justify in stock column
          ->column("on_order")->justify("center")->label("Center justified")     // Center justify in on order column
          ->column("company")->order(2)->label("Reordered")                      // Make the company column the second column
          ->column("country")->label("Country Renamed")                          // Change the header label for the country column
          ->column("company")->columnwidth("5cm")->label("Company 5cm wide")     // Change the width of the company column to 100px ( its css so percentage works too )
          ->column("product")->columnwidth("500px")->label("Product 500px wide") // Change the width of the product column to 500px ( its css so percentage works too )
          ->execute();
?>
