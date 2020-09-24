<?php
      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Product Stock")
          ->description     ("Produces a list of our employees")
          ->sql       ("
              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, ReorderLevel reorder_level, companyname Company, country, categoryname category
              FROM northwind_products 
              join northwind_suppliers on northwind_products.supplierid = northwind_suppliers.supplierid
              join northwind_categories on northwind_products.categoryid = northwind_categories.categoryid
              WHERE 1 = 1  
              ORDER BY categoryname
                ")
          ->group("category")
              ->header("category")
              ->throwPageBefore()
          ->chart("category")
              ->title("Stock Levels")
              ->plot("in_stock")->plotType("bar")->legend("In stock")
              ->plot("reorder_level")->plotType("line")->legend("Reorder Level")
              ->xlabels("product")
              ->xtitle("Levels")
              ->ytitle("Products")
          ->execute();
?>
