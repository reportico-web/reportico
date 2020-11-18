<?php
      require_once(__DIR__ .'/../vendor/autoload.php');

      \Reportico\Engine\Builder::build()
          ->properties(["url_path_to_assets" => "../assets"])
          ->properties(["url_path_to_templates" => "../themes"])
          ->datasource()->database("mysql:host=localhost; dbname=reportico")->user("peter")->password("pN0stalr!")
          ->title     ("Product Stock")
          ->description     ("Produces a list of our products")
          ->sql       ("
              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, UnitsOnOrder on_order, companyname Company, country, categoryname category
              FROM northwind_products 
              join northwind_suppliers on northwind_products.supplierid = northwind_suppliers.supplierid
              join northwind_categories on northwind_products.categoryid = northwind_categories.categoryid
              WHERE 1 = 1  
              ORDER BY categoryname
                ")
          ->group("category")
              ->header("category")
              ->throwPageBefore()
          ->column("in_stock")->justify("right")
          ->column("on_order")->justify("right")
          ->page()
            //->paginate()
            ->topmargin("1cm")
            ->orientation("portrait")
            ->leftmargin("1cm")
            ->rightmargin("1cm")
            //->pagetitledisplay("Off")
            ->header("{REPORT_TITLE}", "border-width: 0px 0px 1px 0px; margin: 0px 0px 0px 0px; border-color: #000000; font-size: 18; border-style: solid; padding:0px 0px 0px 0px; width: 95%; background-color: inherit; color: #fff; margin-left: 2%; margin-bottom: 70px; text-align:center")
            ->header( "", "width: 100; height: 5; margin: 5px 0 0 0; background-image:../assets/images/reportico100.png'"  )
            ->footer( 'Page: {PAGE} of {PAGETOTAL}', 'border-width: 1 0 0 0; top: 0px; font-size: 8pt; margin: 2px 0px 0px 0px; font-style: italic; margin-top: 30px;'  )
            ->footer( 'Time: date(\'Y-m-d H:i:s\')', 'font-size: 8pt; text-align: right; font-style: italic; width: 100%; margin-top: 30px;'  )
            ->pdfengine("tcpdf")
            ->pdfDownloadMethod("inline")
            ->to( "PDF" )
          ->execute();
?>
