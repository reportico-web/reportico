<?php
    /*
     * Global reportico customization .. PDF headers etc.
    */
    function reportico_customize($reportico)
    {
            $reportico->create_page_header("H1", 1, "Northwind Sales Order{STYLE border-width: 1 1 1 1; border-color: #000000; border-style: solid;padding:10px 4px 0px 6px; height:1cm;margin: 2cm 0cm 0cm 0cm; color: #000000; background-color:#ffffff}" );
            //$reportico->create_page_header("H2", 1, "Header2 skdfjsl dfjlskj dfksjdf sldfj lkj{STYLE width:200;height:1cm;background-color:#00aa22; margin: 4cm 0cm 0cm 1cm; }" );
            //$reportico->create_page_header("H3", 1, "Header3{STYLE width:200;height:1cm;margin: 2cm 0cm 0cm 8cm; background-color:#00aaee}" );
            $reportico->create_page_header("H4", 1, "{STYLE width: 100; height: 50; background-image:images/northwind.jpg;}" );
            //$reportico->set_page_header_attribute("H4", "justify", "right" );
            //$reportico->set_page_header_attribute("H4", "Justify", "right" );
            //$reportico->create_page_header("H5", 1, "{full_address}{STYLE border-color: #000000; border-style: solid; border-width: 1 1 1 1; padding: 10px 0px 10px 10px; margin: 100 0 0 0; width: 30%; font-style:italic;background-color: #ffff00; text-align: left}" );
            //$reportico->create_page_header("H6", 1, "Order No. {id}{STYLE border-color: #000000; border-style: solid; border-width: 0 0 1 0; padding: 10px 0px 10px 10px; width: 50%; margin: 165 0 0 40%; text-align: left; text-align: right; font-size: 16}" );
            //$reportico->create_page_header("H6", 1, "{order_date}{STYLE font-family:times;font-weight:bold;font-style:italic;}" );

            //$reportico->create_page_header("H5", 1, "{full_address}{STYLE font-style:italic;background-color: #ffff00; text-align: left}" );

            $styles = array(
                //"background-color" => "#ffffff",
                "border-width" => "5 5 5 5",
                "border-style" => "solid",
                "border-color" => "#000000",
                "padding" => "0 5 0 5",
                "color" => "#003f00",
                "margin" => "0 5 0 5",
                );
            $reportico->apply_styleset("BODY", $styles);

            $styles = array(
                //"background-color" => "#ffffff",
                "border-width" => "2 2 2 2",
                "border-style" => "solid",
                "border-color" => "#0000ff",
                "padding" => "10 0 0 10",
                "margin" => "0 15 0 15",
                "color" => "#003f00",
                );
            $reportico->apply_styleset("PAGE", $styles);

            //$styles = array(
                //"background_color" => "#666666",
                //"color" => "#00ff00",
                //);
            //$reportico->apply_styleset("REPORTBODY", $styles);

            $styles = array(
                "border-width" => "1 1 1 1",
                "border-style" => "solid",
                "border-color" => "#0000ff",
                "background-color" => "#ff0000",
                "color" => "#fff000",
                );
            $reportico->apply_styleset("ALLCELLS", $styles);

            $styles = array(
                "background-color" => "#222288",
                "color" => "#ffffff",
                );
            $reportico->apply_styleset("CELL", $styles, "product");
    }   
?>
