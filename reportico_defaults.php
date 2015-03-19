<?php
    /*
     * Global reportico customization .. PDF headers etc, global styles etc
    */
    function reportico_defaults($reportico)
    {
            $styles = array(
                "background-color" => "#cccfff",
                "border-width" => "10 10 10 10",
                "border-style" => "solid",
                "border-color" => "#000000",
                "padding" => "0 5 0 5",
                //"color" => "#003f00",
                "margin" => "0 5 0 5",
                "font-family" => "trebuchet",
                "font-size" => "16",
                );
            $reportico->apply_styleset("BODY", $styles, false, "PDF");

            $styles = array(
                "margin" => "5 5 5 5",
                "padding" => "20 5 5 5",
                "border-width" => "4 4 4 4",
                "border-style" => "solid",
                "border-color" => "#0000ff",
                "background-color" => "#ff0000",
                "color" => "#000000",
                "font-family" => "verdana",
                "font-size" => "10",
                );
            $reportico->apply_styleset("ALLCELLS", $styles);
            $styles = array(
                "padding" => "20 5 5 5",
                "border-width" => "4 4 4 4",
                "border-style" => "solid",
                "border-color" => "#aaaa00",
                "background-color" => "#aa00aa",
                "color" => "#000000",
                "font-family" => "verdana",
                "font-size" => "10",
                );
            $reportico->apply_styleset("ROW", $styles);

return;

            $reportico->create_page_header("H1", 1, "Northwind Sales Order{STYLE border-width: 1 1 1 1; border-color: #000000; border-style: solid;padding:10px 14px 0px 6px; height:1cm;margin: 1cm 0cm 0cm 0cm; color: #000000;  font-family:courier; width: 80%}" );
            $reportico->set_page_header_attribute("H1", "ShowInHTML", "yes" );
            $reportico->set_page_header_attribute("H1", "ShowInPDF", "yes" );

            $reportico->create_page_header("r1", 1, "Northwind 1Sales Order{STYLE border-width: 1 1 1 1; border-color: #000000; border-style: solid;padding:10px 14px 0px 6px; height:1cm;margin: 1cm 0cm 0cm 0cm; color: #000000;  font-family:courier; width: 80%; float:right}" );
            $reportico->set_page_header_attribute("r1", "ShowInHTML", "yes" );
            $reportico->set_page_header_attribute("r1", "ShowInPDF", "yes" );

            $reportico->create_page_header("H4", 1, "{STYLE width: 100; height: 150; background-color: #003333; background-image:images/northwind.jpg;}" );
            $reportico->set_page_header_attribute("H4", "ShowInHTML", "yes" );


            $reportico->create_page_header("H2", 1, "Northwind Sales Order{STYLE border-width: 1 1 1 1; border-color: #000000; border-style: solid;padding:10px 14px 0px 6px; height:1cm;margin: 1cm 0cm 0cm 4cm; color: #000000; background-color:#ffffff; font-family:courier;width: 50% }" );
            $reportico->set_page_header_attribute("H2", "ShowInHTML", "no" );
            $reportico->set_page_header_attribute("H2", "ShowInPDF", "no" );
            //$reportico->create_page_header("H2", 1, "Header2 skdfjsl dfjlskj dfksjdf sldfj lkj{STYLE width:200;height:1cm;background-color:#00aa22; margin: 4cm 0cm 0cm 1cm; }" );
            //$reportico->create_page_header("H3", 1, "Header3{STYLE width:200;height:1cm;margin: 2cm 0cm 0cm 8cm; background-color:#00aaee}" );
            //$reportico->set_page_header_attribute("H4", "Justify", "right" );
            //$reportico->create_page_header("H5", 1, "{full_address}{STYLE border-color: #000000; border-style: solid; border-width: 1 1 1 1; padding: 10px 0px 10px 10px; margin: 100 0 0 0; width: 30%; font-style:italic;background-color: #ffff00; text-align: left}" );
            //$reportico->create_page_header("H6", 1, "Order No. {id}{STYLE border-color: #000000; border-style: solid; border-width: 0 0 1 0; padding: 10px 0px 10px 10px; width: 50%; margin: 165 0 0 40%; text-align: left; text-align: right; font-size: 16}" );
            //$reportico->create_page_header("H6", 1, "{order_date}{STYLE font-family:times;font-weight:bold;font-style:italic;}" );

            //$reportico->create_page_header("H5", 1, "{full_address}{STYLE font-style:italic;background-color: #ffff00; text-align: left}" );

            $styles = array(
                "background-color" => "#cccfff",
                "border-width" => "10 10 10 10",
                "border-style" => "solid",
                "border-color" => "#000000",
                "padding" => "0 5 0 5",
                //"color" => "#003f00",
                "margin" => "0 5 0 5",
                "font-family" => "times",
                "font-size" => "12",
                );
            $reportico->apply_styleset("BODY", $styles, false, "PDF");

            $styles = array(
                //"background-color" => "#ffffff",
                "border-width" => "2 2 2 2",
                "border-style" => "solid",
                "border-color" => "#0000ff",
                "padding" => "0 10 0 10",
                "margin" => "0 0 0 0",
                //"color" => "#003f00",
                "font-size" => "12",
                );
            $reportico->apply_styleset("PAGE", $styles, false, "PDF");

            //$styles = array(
                //"background_color" => "#666666",
                //"color" => "#00ff00",
                //);
            //$reportico->apply_styleset("REPORTBODY", $styles);

            $styles = array(
                "border-width" => "1 1 1 1",
                "border-style" => "solid",
                "border-color" => "#0000ff",
                //"background-color" => "#ff0000",
                "color" => "#fff000",
                );
            $reportico->apply_styleset("ALLCELLS", $styles);

            $styles = array(
                //"background-color" => "#222288",
                "color" => "#ffffff",
                );
            $reportico->apply_styleset("CELL", $styles, "product");
    }   
?>
