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
function reporticoDefaults($reportico)
{
    return;
    $reportico->createPageHeader("H1", 2, "{REPORT_TITLE}{STYLE border-width: 0px 0px 1px 0px; margin: 15px 0px 0px 0px; border-color: #000000; font-size: 18; border-style: solid;padding:0px 0px 0px 0px; width: 100%; background-color: inherit; color: #000; margin-left: 0%;margin-bottom: 20px;text-align:center}");
    $reportico->setPageHeaderAttribute("H1", "ShowInHTML", "yes");
    $reportico->setPageHeaderAttribute("H1", "ShowInPDF", "yes");
    $reportico->setPageHeaderAttribute("H1", "justify", "left");

    // Create Image on every page of PDF
    //$reportico->createPageHeader("H3", 1, "Time: date('Y-m-d H:i:s'){STYLE font-size: 8pt; text-align: right; font-style: italic;float:right;}");
    //$reportico->setPageHeaderAttribute("H3", "ShowInHTML", "yes");
    //$reportico->setPageHeaderAttribute("H3", "justify", "right");

    $reportico->createPageHeader("H4", 1, "<img src='http://127.0.0.1/newarc/images/reportico100.png' style='width: 100%'>{STYLE width: 100; height: 50; margin: 0 0 0 0; background-image:http://127.0.0.1/newarc/images/reportico100.png;}" );
    $reportico->createPageHeader("H4", 1, "<img src='http://127.0.0.1/newarc/images/reportico100.png' style='width: 100%'>{STYLE width: 100px; height: 50px; margin: 5px 0 0 0;}" );
    $reportico->setPageHeaderAttribute("H4", "ShowInHTML", "yes" );

    // Create Page No on bottom of PDF page
    $reportico->createPageFooter("F1", 2, "Page: {PAGE} of {PAGETOTAL} {STYLE border-width: 1 0 0 0; top: 0px; font-size: 6pt; margin: 2px 0px 0px 0px; font-style: italic; }");
    $reportico->setPageFooterAttribute("F1", "justify", "left");
    $reportico->setPageFooterAttribute("F1", "ShowInHTML", "yes");

    $reportico->createPageFooter("F2", 1, "Time: date('Y-m-d H:i:s'){STYLE font-size: 6pt; margin-top: 2px;text-align: right; width: 100%; font-style: italic;top:2px;}");
    $reportico->setPageFooterAttribute("F2", "ShowInHTML", "yes");
    $reportico->setPageFooterAttribute("F2", "justify", "right");

}
