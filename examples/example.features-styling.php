<?php

      require_once(__DIR__ .'/../vendor/autoload.php');

      error_reporting(E_ALL);

      \Reportico\Engine\Builder::build()
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Product Stock")
          ->description     ("Produces a list of our employees")
          ->sql       ("
              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, ReorderLevel reorder_level, UnitsOnOrder on_order, companyname Company, country, categoryname category
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
          ->column("reorder_level")->justify("right")

          // Conditional styling - if the reorder level < in_stock then set in_stock cell to red 
          ->expression("in_stock")
              ->style("background-color: #dd5555")
              ->if("{in_stock} < {reorder_level}")

          // Conditional styling - if the reorder level < in_stock then set ALLCELLS in row to have light ref background
          ->expression()
              ->section("ALLCELLS")
              ->style("background-color: #ffcccc")
              ->if("{in_stock} < {reorder_level}")

          // Section styling - Set borders around the column headers and set the color blue
          ->expression()
              ->section("COLUMNHEADERS")
              ->style("border: 2px solid #888888; color:#0000ff" )

          // Section styling - Set dotted lines
          ->expression()
              ->section("ROW")
              ->style("border: 2px solid #888888; border-style: dotted" )

          // Section styling - Set the background color or the table to a lightish blue
          ->expression()
              ->section("PAGE")
              ->style("border: 2px solid #888888; background-color:#dff" )

          // Section styling - Set the color of each page to a different blue
          ->expression()
              ->section("BODY")
              ->style("background-color: #Aff;" )

          ->execute();
