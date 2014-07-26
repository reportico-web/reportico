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

 * File:        dyngraph_pchart3.php
 *
 * Generates a chart image from axes, data parameters
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: dyngraph_pchart3.php,v 1.8 2014/05/17 15:12:31 peter Exp $
 */

 /* pChart library inclusions */
 include("pChart3/class/pData.class.php");
 include("pChart3/class/pDraw.class.php");
 include("pChart3/class/pImage.class.php"); 
 include("pChart3/class/pPie.class.php"); 

ini_set("memory_limit","100M");
error_reporting(E_ALL);
date_default_timezone_set(@date_default_timezone_get());

set_include_path ( '.' );

$fontpath = find_best_location_in_include_path( "pChart3/fonts" );
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


session_start();
		
// Set up Font Mapping Arrays
/*
$fontfamilies = array (
	"Font 0" => FF_FONT0,
	"Font 1" => FF_FONT1,
	"Font 2" => FF_FONT2,
	"Font0" => FF_FONT0,
	"Font1" => FF_FONT1,
	"Font2" => FF_FONT2,
	"Arial" => FF_ARIAL,
	"Verdana" => FF_VERDANA,
	"Courier" => FF_COURIER,
	//"Book" => FF_BOOK,
	"Comic" => FF_COMIC,
	//"Script" => FF_HANDWRT,
	"Times" => FF_TIMES
);

$fontstyles = array (
	"Normal" =>  FS_NORMAL,
	"Bold" => FS_BOLD,
	"Italic" => FS_ITALIC,
	"Bold+Italic" => FS_BOLDITALIC
);
*/

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
		$_REQUEST[$b[0]] = $b[1];
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
	$plotlcolor = derive_request_item("plotlinecolor".$v, "black");
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
$graphData->setAxisName(0,$ytitle);
$graphData->addPoints($xlabels,"xaxis");
$graphData->setSerieDescription("xaxis","xaxis");
$graphData->setAbscissa("xaxis"); 
$graphData->setXAxisName("ooo");


// Add each series of plot values to dataset, but Reportico will 
// duplicate series were the same data are displayed in different forms
// so only add each unique series once
$seriesadded = array();
foreach ( $plot as $k => $v )
{
	$series = $v["name"].$k;
	$graphData->addPoints($v["data"],$series);
	$graphData->setSerieDescription($series, $v["legend"]);
}

/*
$graph->xgrid->SetColor($xgridcolor);
$graph->ygrid->SetColor($ygridcolor);
*/

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

$graphImage = new pImage($width,$height ,$graphData); 

/* Turn of Antialiasing */
$graphImage->Antialias = TRUE;

// Add gradient fill from chosen background color to white
$startgradient=htmltorgb("#ffffff");
$graphImage->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,array(
		"StartR"=>$startgradient[0], "StartG"=>$startgradient[1], "StartB"=>$startgradient[2],
		"EndR"=>$color[0], "EndG"=>$color[1], "EndB"=>$color[2],"Alpha"=>100));

 /* Add a border to the picture */
 $graphImage->drawRectangle(0,0,$width - 1,$height - 1,array("R"=>200,"G"=>200,"B"=>200));

 /* Set the title font and draw it */
$graphImage->setFontProperties(array("FontName"=>PCHARTFONTS_DIR.$titlefont,"FontSize"=>$titlefontsize));

$graphImage->drawText(20,30,$title,array("R"=>$titlecolor[0], "G"=>$titlecolor[1], "B"=>$titlecolor[2])); 

 /* Set the default font from the X title font */
$graphImage->setFontProperties(array("FontName"=>PCHARTFONTS_DIR.$xtitlefont,"FontSize"=>$xtitlefontsize));

/* Define the chart area */
$graphImage->setGraphArea($marginleft,$margintop,$width - $marginright,$height - $marginbottom);

//$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"LabelRotation"=>30);
//$graphImage->drawScale($scaleSettings);
//$settings = array("Surrounding"=>-30,"InnerSurrounding"=>30);
//$graphImage->drawBarChart($settings);
 	//$graphImage->autoOutput("pictures/example.drawBarChart.simple.png");
//return;

// Before plotting a series ensure they are all not drawable.
/// Plot the chart data
$stackeddrawn = false;
$stackedexists = false;
$barexists = false;
foreach ( $plot as $k => $v )
{
	if ( $v["type"] == "STACKEDBAR" ) 
		$stackedexists = true;
	if ( $v["type"] == "STACKEDBAR" || $v["type"] == "BAR") 
		$barexists = true;
}


/* Draw the scale */
$scaleSettings = array("GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"LabelRotation"=>30);

// For stacked charts fix up the Max and Min values;
if ( $stackedexists )
{
	$scaleMin = "Unknown";
	$scaleMax = 0;
	foreach ( $plot as $k => $v )
	{
		if ( $v["type"] == "BAR"  || $v["type"] == "STACKEDBAR" )
		{
			$series = $v["name"].$k;
			$min = $graphData->getMin($series);
			if ( $scaleMin == "Unknown" || $min < $scaleMin ) 
				$scaleMin = $min;
			$scaleMax  = $scaleMax + $graphData->getMax($series);
		}
	}
	if ( $scaleMin > 0 ) $scaleMin = 0;
	$range = $scaleMax - $scaleMin;
	// Make scales based on 5% of the range of values
	$scaleMax = round(( $range * 0.05 ) + $scaleMax);
	if ( $scaleMin < 0 )
		$scaleMin = $scaleMin - round(( $range * 0.05 )) ; 
	$AxisBoundaries = array(0=>array("Min"=>$scaleMin,"Max"=>$scaleMax));
	$scaleSettings["Mode"] = SCALE_MODE_MANUAL;
	$scaleSettings["ManualScale"] = $AxisBoundaries;
	
	
}
else if ( $barexists )
{
	$scaleMin = "Unknown";
	$scaleMax = 0;
	foreach ( $plot as $k => $v )
	{
		if ( $v["type"] == "BAR"  || $v["type"] == "STACKEDBAR" )
		{
			$series = $v["name"].$k;
			$min = $graphData->getMin($series);
			if ( $scaleMin == "Unknown" || $min < $scaleMin ) 
				$scaleMin = $min;
			$max = $graphData->getMax($series);
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
	$scaleSettings["Mode"] = SCALE_MODE_MANUAL;
	$scaleSettings["ManualScale"] = $AxisBoundaries;
	
	
}

$graphImage->drawScale($scaleSettings);


// If there's a Pie chart we want to draw different legends
$piechart = false;
foreach ( $plot as $k => $v )
{
	foreach ( $plot as $k1 => $v1 )
	{
		$series = $v1["name"].$k1;
		$graphData->setSerieDrawable($series,FALSE);
	}
	$series = $v["name"].$k;

	$graphData->setSerieDrawable($series,TRUE);
	switch ( $v["type"] ) {
	case "PIE":
		$piechart = true;
		$pie = new pPie($graphImage, $graphData);
 		//$pie->draw2DPie($width / 2,$height / 2,80,array("DrawLabels"=>TRUE,"LabelStacked"=>TRUE,"Border"=>TRUE));
 		$pie->draw2DPie($width / 2,$height / 2,80,
			array("WriteValues"=>PIE_VALUE_PERCENTAGE,"DataGapAngle"=>10,"DataGapRadius"=>6,"Border"=>TRUE,"BorderR"=>255,"BorderG"=>255,"BorderB"=>255));

		break;
	case "PIE3D":
		$piechart = true;
		$pie = new pPie($graphImage, $graphData);
 		$pie->draw3DPie($width / 2,$height / 2,80,array("SecondPass"=>FALSE));
		break;
	case "STACKEDBAR":
	case "BAR":
		if ( $stackeddrawn )
			break;

		if (  $barexists )
		{
			foreach ( $plot as $k1 => $v1 )
			{
				if ( $v1["type"] == "BAR" || $v1["type"] == "STACKEDBAR" )
				{
					$graphData->setSerieDrawable($v1["name"].$k1,TRUE);
				}
			}
		}
		$stackeddrawn = true;
 		$settings = array("Surrounding"=>-30,"InnerSurrounding"=>30);
		if ( $stackedexists )
 			$graphImage->drawStackedBarChart($settings);
		else
 			$graphImage->drawBarChart($settings);
	
		break;
	case "LINE":
	default;

		if ( count($v["data"]) == 1 )
			$v["data"][] = 0;

 		$graphImage->drawLineChart($settings);
		break;
	}
}
	$graphData->drawAll();

	if ( $piechart )
 		$pie->drawPieLegend($width - 100,30,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL)); 
	else
 		$graphImage->drawLegend($width - 180,22,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));
 	$graphImage->setShadow(TRUE,array("X"=>0,"Y"=>0,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 	$graphImage->autoOutput("pictures/example.drawBarChart.simple.png");

function htmltorgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return array(0,0,0);

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}
?>

