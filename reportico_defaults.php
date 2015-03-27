<?php
    /*
     * Global reportico customization .. 
     * Set styles :-
     * Styles can be applied to the report body, details page, group headers, trailers, 
     * cell styles and are specified as a series of CSS like parameters.
     * Styles are applied to HTML and PDF formats
     * 
     * Add page headers, footers with styles to apply to every report page
     *
    */
    function reportico_defaults($reportico)
    {
            // Set up styles
            // Use 
            // $styles = array ( "styleproperty" => "value", .... );
            // $reportico->apply_styleset("REPORTSECTION", $styles, "columnname", WHENTOAPPLY );
            // Where REPORTSECTION is one of ALLCELLS ROW CELL PAGE BODY COLUMNHEADERS GROUPHEADER GROUPHEADERLABEL GROUPHEADERVALUE GROUPTRAILER
            // and WHENTOAPPLY can be PDF or HTML of leave unsepcified/false for both


             // REPORT BODY STYLES
            /*
            $styles = array(
                "background-color" => "#cccccc",
                "border-width" => "2px 2px 2px 2px",
                "border-style" => "solid",
                "border-color" => "#000000",
                "color" => "#003f00",
                "padding" => "0 20 0 20",
                "margin" => "0 0 0 5",
                "font-family" => "trebuchet",
                );
            $reportico->apply_styleset("BODY", $styles, false);
            */

            // PAGE DETAIL BOX STYLES
            /*
            $styles = array(
                "background-color" => "#ddeeee",
                "border-width" => "8px 8px 8px 8px",
                "border-style" => "solid",
                "border-color" => "#777777",
                "color" => "#003f00",
                "margin" => "0 5 0 5",
                );
            $reportico->apply_styleset("PAGE", $styles, false);
            */

            // DETAIL ROW BOX STYLES
            /*
            $styles = array(
                "background-color" => "#eeeeee",
                "margin" => "0 10 0 10",
                );
            $reportico->apply_styleset("ROW", $styles, false);
            */

            // GROUP HEADER VALUE STYLES
            /*
            $styles = array(
                "background-color" => "#000000",
                "color" => "#ffffff",
                "font-family" => "comic",
                "font-size" => "18px",
                "padding" => "0 10 0 10",
                "requires-before" => "8cm",
                "margin" => "0 10 0 0",
                );
            $reportico->apply_styleset("GROUPHEADERVALUE", $styles, false);
            */

            //GROUP HEADER LABEL STYLES
            /*
            $styles = array(
                "background-color" => "#000000",
                "color" => "#ffffff",
                "font-family" => "comic",
                "font-size" => "18px",
                "padding" => "0 10 0 10",
                "margin" => "0 0 0 0",
                "requires-before" => "8cm",
                );
            $reportico->apply_styleset("GROUPHEADERLABEL", $styles, false);
            */

            // ALL CELL STYLES
            /*
            $styles = array(
                "font-family" => "times",
                "border-width" => "1px 1px 1px 1px",
                "border-style" => "solid",
                "border-color" => "#888888",
                );
            $reportico->apply_styleset("ALLCELLS", $styles, false);
            */

            // Specific named cell styles
            /*
            $styles = array(
                "color" => "#880000",
                "font-weight" => "bold",
                "font-style" => "italic",
                );
            $reportico->apply_styleset("CELL", $styles, "id");
            */

            // Column header styles
            /*
            $styles = array(
                "font-weight" => "bold",
                "font-style" => "italic",
                "color" => "#bbbb00",
                );
            $reportico->apply_styleset("COLUMNHEADERS", $styles, false);
            */

            // Create Report Title Page Header on every page of PDF
            /*
            $reportico->create_page_header("H1", 1, "{REPORT_TITLE}{STYLE border-width: 1 1 1 1; border-color: #000000; font-size: 18; border-style: solid;padding:10px 14px 0px 6px; height:1cm;margin: 1cm 0cm 0cm 4cm; color: #000000;  width: 90%}" );
            $reportico->set_page_header_attribute("H1", "ShowInHTML", "no" );
            $reportico->set_page_header_attribute("H1", "ShowInPDF", "yes" );
            $reportico->set_page_header_attribute("H1", "Justify", "right" );
            */

            // Create Image on every page of PDF
            /*
            $reportico->create_page_header("H2", 1, "{STYLE width: 100; height: 50; margin: 0 0 0 0; background-color: #003333; background-image:images/reportico100.png;}" );
            $reportico->set_page_header_attribute("H2", "ShowInHTML", "no" );
            */

            // Create Image on every page of PDF
            /*
            $reportico->create_page_header("H3", 1, "Time: date('Y-m-d H:i:s'){STYLE font-size: 18; text-align: right}" );
            $reportico->set_page_header_attribute("H3", "ShowInHTML", "no" );
            $reportico->set_page_header_attribute("H3", "Justify", "right" );
            */

            // Create Page No on bottom of PDF page
            /*
            $reportico->create_page_footer("F1", 1, "Page: {PAGE}{STYLE margin: 2 0 0 0; }" );
            */
    }   
?>
