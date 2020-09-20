<h1><?php echo $title?></h1>
<?php echo $description; ?>
<br><br>

<?php 
    if ( $example_description !== false && $description !== false ) {
?>


<div class="rounded" style="border:solid 1px #cccccc; padding: 10px">
<ul class="nav nav-pills" role="tablist">
    <li class="nav-item active"><a id="Usage" class="nav-link show active" data-toggle="tab" href="#tab-content-usage">Usage</a></li>
    <li class="nav-item"><a id="View Code" class="nav-link " data-toggle="tab" href="#tab-example-code">View Code</a></li>
</ul>
<?php 
    }
?>

<div class="tab-content">
    <div id="tab-content-usage"  class="tab-pane fade-in active">
    <br>
<?php
                if ( $usage_method === false)  {
                } else 
                if ( !$usage_method ) 
                    \Reportico\Engine\Builder::build()->usage();
                else if ( $usage_method == "column" ) 
                      \Reportico\Engine\Builder::build()
                          ->datasource()->database("mysql:host=localhost; dbname=<DATABASENAME>")->user("<DATABASE USER>")->password("<DATABASE PASSWORD>")
                          ->title     ("Product Stock")
                          ->description     ("Produces a list of our employees")
                          ->sql       ("
                              SELECT  ProductID id, ProductName product, UnitsInStock in_stock, UnitsOnOrder on_order, 
                                  companyname Company, country, categoryname category
                              FROM northwind_products 
                              join northwind_suppliers on northwind_products.supplierid = northwind_suppliers.supplierid
                              join northwind_categories on northwind_products.categoryid = northwind_categories.categoryid
                              WHERE 1 = 1  
                              ORDER BY categoryname
                                ")
                       ->column("id")->usage();
                else
                    if ( strlen($usage_method) > 30 ) 
                        echo highlight_string($usage_method);
                    else {
                        \Reportico\Engine\Builder::build()->$usage_method("param")->usage();
                    }
?>
    </div>                  
                  
                  
    <div id="tab-example-code" class="tab-pane fade in" >
        <br><br>
        <div class="non-printable">
            <?php if ( isset($example_description) ) echo $example_description ?>
            <br><br>
            <a target="_blank" class="btn btn-success" href="<?php echo $example_url?>">Click here to run example</a>
        </div>
        <br><br>
<?php
     echo $code;
?>
    </div>
    </div>
</div>
