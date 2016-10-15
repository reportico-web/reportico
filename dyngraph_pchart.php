<?php
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2014 Peter Deed

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

 * File:        dyngraph_pchart.php
 *
 * Generates a chart image from axes, data parameters
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: dyngraph_pchart.php,v 1.16 2014/05/17 15:12:31 peter Exp $
 */

 /* pChart library inclusions */
 //include("pChart/class/pData.class.php");
 //include("pChart/class/pDraw.class.php");
 //include("pChart/class/pImage.class.php"); 
 //include("pChart/class/pPie.class.php"); 
 include("pChart/pChart.class");
 include("pChart/pData.class");
 include("pChart/pCache.class"); 
 include_once("swutil.php"); 

ini_set("memory_limit","100M");
error_reporting(E_ALL);
date_default_timezone_set(@date_default_timezone_get());

set_include_path ( '.' );

$fontpath = find_best_location_in_include_path( "pChart/fonts" );
define ( "PCHARTFONTS_DIR", $fontpath."/" );
function & derive_request_item ( $attrib_name, $default )
{
	if ( !isset($_REQUEST[$attrib_name]))
		return $default;
	if ( $_REQUEST[$attrib_name] )
	{
		return $_REQUEST[$attrib_name];
	}
	else
	{
		return $default;
	}
}

/**
 * Function convert_special_chars
 *
 * Converts special strings encoded back to original text
 */
function convert_special_chars($intext)
{
    $outtext = preg_replace("/<AMPERSAND>/", "&", $intext);
    return $outtext;
}


set_up_reportico_session();		

global $g_session_namespace;
global $g_session_namespace_key;
if ( $g_session_namespace )
    $g_session_namespace_key = "reportico_".$g_session_namespace;
else
    $g_session_namespace_key = "reportico";

$plotdata = array();
$plot = array();

$graphid = derive_request_item("graphid", "");

if ( $graphid )
{
	$params=get_reportico_session_param($graphid);
	$a = explode('&', $params);
	$i = 0;
	while ($i < count($a)) 
	{
    	$b = preg_split('/=/', $a[$i]);
        if ( isset($b[1]) )
		    $_REQUEST[$b[0]] = $b[1];
        if ( isset($b[1]) )
		    $tx=$b[0]."=".$b[1];
    	$i++;
	}
	
}

$color = htmltorgb(derive_request_item("graphcolor", "white"));
$width = derive_request_item("width", 400);
$height = derive_request_item("height", 200);
$xgriddisplay = derive_request_item("xgriddisplay", "none");
$ygriddisplay = derive_request_item("ygriddisplay", "none");
$gridpos = derive_request_item("gridposition", "back");
$xgridcolor =  htmltorgb(derive_request_item("xgridcolor", "purple"));
$ygridcolor =  htmltorgb(derive_request_item("ygridcolor", "darkgreen"));
$title = convert_special_chars(derive_request_item("title", ""));
$xtitle = convert_special_chars(derive_request_item("xtitle", ""));
$ytitle = convert_special_chars(derive_request_item("ytitle", ""));

if ( $title == "Set Title" ) $title = "";
if ( $xtitle == "Set Title" ) $xtitle = "";
if ( $ytitle == "Set Title" ) $ytitle = "";

$titlefont = derive_request_item("titlefont", "Arial");
$titlefontstyle = derive_request_item("titlefontstyle", "Normal");
$titlefontsize = derive_request_item("titlefontsize", 12);
$xtitlefont = derive_request_item("xtitlefont", "Arial");
$xtitlefontstyle = derive_request_item("xtitlefontstyle", "Normal");
$xtitlefontsize = derive_request_item("xtitlefontsize", 12);
$xtitlecolor =  htmltorgb(derive_request_item("xtitlecolor", "black"));
$ytitlefont = derive_request_item("ytitlefont", "Arial");
$ytitlefontstyle = derive_request_item("ytitlefontstyle", "Normal");
$ytitlefontsize = derive_request_item("ytitlefontsize", 12);
$ytitlecolor =  htmltorgb(derive_request_item("ytitlecolor", "black"));
$xtickinterval = derive_request_item("xtickint", 4);
$ytickinterval = derive_request_item("ytickint", 4);
$titlecolor =  htmltorgb(derive_request_item("titlecolor", "black"));
$margincolor =  htmltorgb(derive_request_item("margincolor", "white"));
$marginleft = derive_request_item("marginleft", 50);
$marginright = derive_request_item("marginright", 50);
$margintop = derive_request_item("margintop", 20);
$marginbottom = derive_request_item("marginbottom", 60);
$xticklabint = derive_request_item("xticklabint", 2);
$xaxiscolor =  htmltorgb(derive_request_item("xaxiscolor", "red"));
$yaxiscolor =  htmltorgb(derive_request_item("yaxiscolor", "green"));
$xaxisfontcolor =  htmltorgb(derive_request_item("xaxisfontcolor", "purple"));
$yaxisfontcolor =  htmltorgb(derive_request_item("yaxisfontcolor", "gray"));
$xaxisfont = derive_request_item("xaxisfont", "Arial");
$xaxisfontstyle = derive_request_item("xaxisfontstyle", "Normal");
$xaxisfontsize = derive_request_item("xaxisfontsize", 12);
$yticklabint = derive_request_item("yticklabint", 2);
$yaxisfont = derive_request_item("yaxisfont", "Arial");
$yaxisfontstyle = derive_request_item("yaxisfontstyle", "Normal");
$yaxisfontsize = derive_request_item("yaxisfontsize", 12);
$val = derive_request_item("xlabels", "");
$xlabels = explode(",", $val);



$v = 0;
while ( true )
{
	$vval = "plotdata".$v;
	$plottype = derive_request_item("plottype".$v, "LINE");
	$plotlcolor = derive_request_item("plotlinecolor".$v, "");
	$plotfcolor = derive_request_item("plotfillcolor".$v, "");
	$plotlegend = derive_request_item("plotlegend".$v, "");
	$plotname = derive_request_item("plotname".$v, "");
	if ( array_key_exists("$vval", $_REQUEST ) )
	{
		$vals = explode(",", $_REQUEST["$vval"]);
		$plot[$v] = array(
				"name" => $plotname,
				"type" => $plottype,
				"fillcolor" => $plotfcolor,
				"linecolor" => $plotlcolor,
				"legend" => $plotlegend,
				"data" => $vals
			);
	}
	else
		break;

	$v++;
}

/*
// Create the correct type of graph. 
if ( $plot[0]["type"] == "PIE" || $plot[0]["type"] == "PIE3D" )
{
	$graph = new PieGraph($width,$height,"auto");	
	$graph->SetScale("textlin");

	$graph->img->SetMargin($marginleft,$marginright,$margintop,$marginbottom);
	$graph->SetMarginColor($margincolor);
	$graph->img->SetAntiAliasing(); 
	$graph->SetColor($color);
	$graph->SetShadow();
	$graph->xaxis->SetTitleMargin($marginbottom - 35); 
	$graph->yaxis->SetTitleMargin(50);
	$graph->yaxis->SetTitleMargin($marginleft - 15); 
	$graph->title->Set($title);
}
else
{
	$graph = new pu($width,$height,"auto");	
	$graph->SetScale("textlin");

	$graph->img->SetMargin($marginleft,$marginright,$margintop,$marginbottom);
	$graph->SetMarginColor($margincolor);
	$graph->img->SetAntiAliasing(); 
	$graph->SetColor($color);
	$graph->SetShadow();
	$graph->xaxis->SetTitleMargin($marginbottom - 35); 
	$graph->yaxis->SetTitleMargin(50);
	$graph->yaxis->SetTitleMargin($marginleft - 15); 
}
*/


// Create Graph Dataset and set axis attributes
$graphData = new pData();
$graphData->setYAxisName($ytitle);
$graphData->AddPoint($xlabels,"xaxis");
//$graphData->SetSerieName("xaxis","xaxis");
$graphData->SetAbsciseLabelSerie("xaxis"); 
$graphData->setXAxisName($xtitle);


// Add each series of plot values to dataset, but Reportico will 
// duplicate series were the same data are displayed in different forms
// so only add each unique series once
$seriesadded = array();
foreach ( $plot as $k => $v )
{
	$series = $v["name"].$k;
	$graphData->AddPoint($v["data"],$series);
	$graphData->SetSerieName($v["legend"], $series);
    $graphData->AddSerie($series);
}


/*
switch ( $xgriddisplay )
{
	case "all":
		$graph->xgrid->Show(true,true);
		break;
	case "major":
		$graph->xgrid->Show(true,false);
		break;
	case "minor":
		$graph->xgrid->Show(false,true);
		break;
	case "none":
	default:
		$graph->xgrid->Show(false,false);
		break;
}

switch ( $ygriddisplay )
{
	case "all":
		$graph->ygrid->Show(true,true);
		break;
	case "major":
		$graph->ygrid->Show(true,false);
		break;
	case "minor":
		$graph->ygrid->Show(false,true);
		break;
	case "none":
	default:
		$graph->ygrid->Show(false,false);
		break;
}
*/


/*
$graph->xaxis->SetFont($fontfamilies[$xaxisfont],$fontstyles[$xaxisfontstyle], $xaxisfontsize);
$graph->xaxis->SetColor($xaxiscolor,$xaxisfontcolor);
$graph->yaxis->SetFont($fontfamilies[$yaxisfont],$fontstyles[$yaxisfontstyle], $yaxisfontsize);
$graph->yaxis->SetColor($yaxiscolor,$yaxisfontcolor);
$graph->xaxis->title->SetFont($fontfamilies[$xtitlefont],$fontstyles[$xtitlefontstyle], $xtitlefontsize);
$graph->xaxis->title->SetColor($xtitlecolor);
$graph->yaxis->title->SetFont($fontfamilies[$ytitlefont],$fontstyles[$ytitlefontstyle], $ytitlefontsize);
$graph->yaxis->title->SetColor($ytitlecolor);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetLabelMargin(15); 
$graph->yaxis->SetLabelMargin(15); 
$graph->xaxis->SetTickLabels($xlabels);
$graph->xaxis->SetTextLabelInterval($xticklabint);
$graph->yaxis->SetTextLabelInterval($yticklabint);
$graph->xaxis->SetTextTickInterval($xtickinterval);
$graph->yaxis->SetTextTickInterval($ytickinterval);
*/

/*
if ( $gridpos == "front" )
	$graph->SetGridDepth(DEPTH_FRONT); 
*/

// Display the graph
/*?$graph->Stroke();*/

$graphImage = new pChart($width,$height); 


/* Turn of Antialiasing */
$graphImage->Antialias = TRUE;

// Add gradient fill from chosen background color to white
$startgradient=htmltorgb("#ffffff");
//$graphImage->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,array(
		//"StartR"=>$startgradient[0], "StartG"=>$startgradient[1], "StartB"=>$startgradient[2],
		//"EndR"=>$color[0], "EndG"=>$color[1], "EndB"=>$color[2],"Alpha"=>100));

 /* Add a border to the picture */
 //$graphImage->drawRectangle(0,0,$width - 1,$height - 1,200,200,200);

$graphImage->setFontProperties(PCHARTFONTS_DIR.$xaxisfont,$xaxisfontsize);

/* Define the chart area */
$graphImage->setGraphArea($marginleft,$margintop,$width - $marginright,$height - $marginbottom);

 $graphImage->drawFilledRoundedRectangle(3,3, $width - 3,$height - 3,5,240,240,240);
 $graphImage->drawRoundedRectangle(1,1,$width - 1,$height - 1,5,230,230,230);


// Before plotting a series ensure they are all not drawable.
/// Plot the chart data
$stackeddrawn = false;
$linedrawn = false;
$scatterdrawn = false;
$piedrawn = false;
$stackedexists = false;
$overlayexists = false;
$barexists = false;
foreach ( $plot as $k => $v )
{
	if ( $v["type"] == "OVERLAYBAR" ) 
		$overlayexists = true;
	if ( $v["type"] == "STACKEDBAR" ) 
		$stackedexists = true;
	if ( $v["type"] == "STACKEDBAR" || $v["type"] == "BAR") 
		$barexists = true;
    if ( $v["linecolor"] )
        $graphImage->Palette[$k] = htmltorgb_pchart($v["linecolor"]);
}

$scale_drawing_mode = SCALE_NORMAL;
$scale_drawing_mode = SCALE_START0;

// For stacked charts fix up the Max and Min values;
if ( $stackedexists )
{
    $scale_drawing_mode = SCALE_ADDALL;
	$scaleMin = "Unknown";
	$scaleMax = 0;
    $min = false;
    $max = false;
	foreach ( $plot as $k => $v )
	{
		if ( $v["type"] == "BAR"  || $v["type"] == "STACKEDBAR" )
		{
			$series = $v["name"].$k;
            minmaxValueOfSeries($v["data"], $min, $max );
			if ( $scaleMin == "Unknown" || $min < $scaleMin ) $scaleMin = $min;
			$scaleMax  = $scaleMax + $max;
		}
	}
	if ( $scaleMin > 0 ) $scaleMin = 0;
	$range = $scaleMax - $scaleMin;
	// Make scales based on 5% of the range of values
	$scaleMax = round(( $range * 0.05 ) + $scaleMax);
	if ( $scaleMin < 0 )
		$scaleMin = $scaleMin - round(( $range * 0.05 )) ; 
	$AxisBoundaries = array(0=>array("Min"=>$scaleMin,"Max"=>$scaleMax));
}
else if ( $barexists || $overlayexists )
{
	$scaleMin = "Unknown";
	$scaleMax = 0;
    $min = false;
    $max = false;
	foreach ( $plot as $k => $v )
	{
		if ( $v["type"] == "BAR"  || $v["type"] == "STACKEDBAR" )
		{
			$series = $v["name"].$k;
            minmaxValueOfSeries($v["data"], $min, $max );
			if ( $scaleMin == "Unknown" || $min < $scaleMin ) 
				$scaleMin = $min;
			if ( $scaleMax == "Unknown" || $max > $scaleMax ) 
				$scaleMax = $max;
		}
	}
	if ( $scaleMin > 0 ) $scaleMin = 0;
	$range = $scaleMax - $scaleMin;
	// Make scales based on 5% of the range of values
	$scaleMax = round(( $range * 0.05 ) + $scaleMax);
	if ( $scaleMin < 0 )
		$scaleMin = $scaleMin - round(( $range * 0.05 )) ; 
	$AxisBoundaries = array(0=>array("Min"=>$scaleMin,"Max"=>$scaleMax));
}

//echo "<PRE>";
//var_dump($graphData->GetDataDescription());
//die;
// Find out if a scale is required, will be except for pie charts
$scalerequired = false;
foreach ( $plot as $k => $v )
{
	switch ( $v["type"] ) {
	case "BAR":
	case "STACKEDBAR":
	case "OVERLAYBAR":
	case "LINE":
        $scalerequired = "NORMAL";
        break;

	case "SCATTER":
        $scalerequired = "SCATTER";
        break;
    }
}

$graphImage->setFontProperties(PCHARTFONTS_DIR.$xtitlefont,$xtitlefontsize);

if ( $scalerequired )
{
    $graphImage->setGraphArea($marginleft,$margintop,$width - $marginright,$height - $marginbottom);
    $graphImage->drawGraphAreaGradient(240,240,240,-20);   

    // Automatic generation of x tick interval based on number of ticks
    if ( $xticklabint == "AUTO" )
    {
        $labct = count($plot[0]["data"]);
        $xticklabint = floor ($labct / 50 ) + 1 ;
    }

    if ( $scalerequired == "NORMAL" )
    {
        $graphImage->drawScale($graphData->GetData(), 
            $graphData->GetDataDescription(), 
            $scale_drawing_mode, 
            0, 0, 0, // color
            TRUE,    // draw ticks?
            40,      // label angle
            FALSE,    // decimals ?
            TRUE,    // with margin
            $xticklabint,       // skip labels
            FALSE    // Right scale
            );
    }
    $graphImage->drawGrid(1,TRUE,230,230,230,45);
}
else
{
    $marginright= 5;
    $marginbottom= 5;
    $marginleft= 5;
    //$margintop = 5;
    $graphImage->setGraphArea($marginleft,$margintop,$width - $marginright,$height - $marginbottom);
    $graphImage->drawGraphAreaGradient(240,240,240,-10);   
}

// If there's a Pie chart we want to draw different legends
$piechart = false;
foreach ( $plot as $k => $v )
{
    disableAllSeries($plot, $graphData);
	$series = $v["name"].$k;

	setSerieDrawable($plot, $graphData, $series,TRUE);

	switch ( $v["type"] ) {
	case "PIE":
        $piedrawn = true;
		$piechart = true;
        $graphImage->drawFilledCircle( ( $width / 2 ) + 2,$margintop + 2 + ( ( $height - $margintop - $marginbottom) /  2),
                    ( $height - $marginbottom - $margintop - 20 ) * 0.45  + 1,
                    200,200,200); 
        $graphImage->drawBasicPieGraph($graphData->GetData(),$graphData->GetDataDescription(),
                    $width / 2,
                    $margintop + ( ( $height - $margintop - $marginbottom) /  2), 
                    ( $height - $marginbottom - $margintop - 20 ) * 0.45  ,
                    PIE_PERCENTAGE_LABEL,
                    255,255,218);
		break;

	case "PIE3D":
        $piedrawn = true;
		$piechart = true;
        $graphImage->drawPieGraph($graphData->GetData(),$graphData->GetDataDescription(),
                    $width / 2,
                    $margintop + ( ( $height - $margintop - $marginbottom) /  2), 
                    ( $height - $marginbottom - $margintop - 20 ) * 0.50  ,
                    PIE_PERCENTAGE_LABEL,
                    true, // enhance colors
                    60, // skew
                    20, // splice height
                    0, // splice distance
                    0 // decimals
                    );
		break;
	case "OVERLAYBAR":
	case "STACKEDBAR":
	case "BAR":
		if ( $stackeddrawn )
			break;

		if (  $barexists || $overlayexists )
		{
			foreach ( $plot as $k1 => $v1 )
			{
				if ( $v1["type"] == "BAR" || $v1["type"] == "STACKEDBAR" || $v1["type"] == "OVERLAYBAR" )
				{
					setSerieDrawable($plot, $graphData, $v1["name"].$k1,TRUE);
				}
			}
		}
		$stackeddrawn = true;
		if ( $stackedexists )
 			$graphImage->drawStackedBarGraph($graphData->GetData(), $graphData->GetDataDescription(), 90);
		else if ( $overlayexists )
 			$graphImage->drawOverlayBarGraph($graphData->GetData(), $graphData->GetDataDescription(), 90);
		else
 			$graphImage->drawBarGraph($graphData->GetData(), $graphData->GetDataDescription());
	
		break;
	case "SCATTER":
		if ( $scatterdrawn )
			break;

        $scatterdrawn = true;
        $series1 = false;
        $series2 = false;
        $graphImage->reportWarnings("GD");
        $ct = 0;
		foreach ( $plot as $k1 => $v1 )
		{
			if ( $v1["type"] == "SCATTER" )
			{
                if ( $ct == 0 ) $series1 = $v1["name"].$k1;
                if ( $ct == 1 ) $series2 = $v1["name"].$k1;
                $ct++;
				setSerieDrawable($plot, $graphData, $v1["name"].$k1,TRUE);
			}
		}
		if ( count($v["data"]) == 1 )
			$v["data"][] = 0;

        $graphImage->drawXYScale($graphData->GetData(), $graphData->GetDataDescription(), $series1, $series2,0,0,0); 
 		//$graphImage->drawXYGraph($graphData->GetData(), $graphData->GetDataDescription(), $series1, $series2);
 		$graphImage->drawXYPlotGraph($graphData->GetData(), $graphData->GetDataDescription(),  $series1, $series2);
        $graphImage->writeValues($graphData->GetData(),$graphData->GetDataDescription(),$series2); 
		break;
	case "LINE":
	default;
		if ( $linedrawn )
			break;

        $linedrawn = true;
		foreach ( $plot as $k1 => $v1 )
		{
			if ( $v1["type"] == "LINE" )
			{
				setSerieDrawable($plot, $graphData, $v1["name"].$k1,TRUE);
			}
		}
		if ( count($v["data"]) == 1 )
			$v["data"][] = 0;

 		$graphImage->LineWidth = 1;
 		$graphImage->drawLineGraph($graphData->GetData(), $graphData->GetDataDescription());
 		$graphImage->drawPlotGraph($graphData->GetData(), $graphData->GetDataDescription());
 		$graphImage->LineWidth = 1;
		break;
	}
}
	foreach ( $plot as $k1 => $v1 )
	{
		setSerieDrawable($plot, $graphData, $v1["name"].$k1,TRUE);
	}

    // Draw Legend if legend value has been set
    $drawlegend = false;
    foreach ( $plot as $k => $v )
    {
	    if ( isset($v["legend"]) && $v["legend"] )
        {
            // Temporarily Dont draw legend for Pie
	        //if ( $piechart )
 		        //$graphImage->drawPieLegend($width - 180,30,$graphData->GetData(), $graphData->GetDataDescription(), 250, 250, 250); 
	        if ( !$piechart )
 		        $graphImage->drawLegend($width - 180, 30,$graphData->GetDataDescription(), 254, 254, 254, 0, 0, 0 );
            break;
        }
    }

    $graphImage->setFontProperties(PCHARTFONTS_DIR.$xtitlefont,$titlefontsize);
    $graphImage->drawTitle(0,24,$title,50,50,50,$width);
 	//$graphImage->setShadow(TRUE,array("X"=>0,"Y"=>0,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 	//$graphImage->Render("example.png");
 	$graphImage->Stroke();

function minmaxValueOfSeries($data, &$min, &$max )
{
    $min = "undef";
    $max = "undef";

    foreach ( $data as $v )
    {
        if ( $min == "undef" || $v < $min )
            $min = $v;

        if ( $max == "undef" || $v > $max )
            $max = $v;
    }
}

function disableAllSeries($plot, &$graphData)
{
    foreach ( $plot as $k => $v )
    {
        $series = $v["name"].$k;
        $graphData->RemoveSerie($series);
    }
}

function setSerieDrawable($plot, $graphData, $inseries,$flag)
{
    foreach ( $plot as $k => $v )
    {
        $series = $v["name"].$k;
	    if ( $inseries == $series )
        {
            if ( $flag )
            {
	            $graphData->AddSerie($series);
	            $graphData->SetSerieName($v["legend"], $series);
            }
            else
	            $graphData->RemoveSerie($series);
        }
    }
}
?>
