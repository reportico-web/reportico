<?php
      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Product Stock")
          ->description     ("Produces a list of our employees")
          ->sql       ("
              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, UnitsOnOrder on_order, companyname Company, country, categoryname category
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
              ->customTrailer("Total in stock for category {category} is {total_stock}", "border: solid 4px #22D; background-color: #222; color: #fff;  right: 0px; margin-left: auto;  width: 50%; padding: 10px;")
              ->header("category")
          ->column("in_stock")->justify("right")
          ->column("on_order")->justify("right")
          ->execute();
