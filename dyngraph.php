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

 * File:        dyngraph.php
 *
 * Generates a chart image from axes, data parameters
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: dyngraph.php,v 1.14 2014/05/17 15:12:31 peter Exp $
 */


	ini_set("memory_limit","100M");
	error_reporting(E_ALL);
    date_default_timezone_set(@date_default_timezone_get());

	set_include_path ( '.' );

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


include ("swutil.php");
set_up_reportico_session();		

global $g_session_namespace;
global $g_session_namespace_key;
if ( $g_session_namespace )
    $g_session_namespace_key = "reportico_".$g_session_namespace;

		
include ("jpgraph/src/jpgraph.php");
include ("jpgraph/src/jpgraph_line.php");
include ("jpgraph/src/jpgraph_error.php");
include ("jpgraph/src/jpgraph_bar.php");
include ("jpgraph/src/jpgraph_pie.php");
include ("jpgraph/src/jpgraph_pie3d.php");

// Set up Font Mapping Arrays
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
	"Georgia" => FF_GEORGIA,
	"Trebuchet" => FF_TREBUCHE,
	//"Script" => FF_HANDWRT,
	"Times" => FF_TIMES,
	"Mukti_Narrow" => Mukti_Narrow,
    "advent_light" => advent_light,
    "Bedizen" => Bedizen,
    "calibri" => calibri,
    "Forgotte" => Forgotte,
    "GeosansLight" => GeosansLight,
    "MankSans" => MankSans,
    "pf_arma_five" => pf_arma_five,
    "Silkscreen" => Silkscreen,
    "verdana" => verdana,
    "Vera" => FF_VERA

);

$fontstyles = array (
	"Normal" =>  FS_NORMAL,
	"Bold" => FS_BOLD,
	"Italic" => FS_ITALIC,
	"Bold+Italic" => FS_BOLDITALIC
);

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

$color = derive_request_item("graphcolor", "white");
$width = derive_request_item("width", 400);
$height = derive_request_item("height", 200);
$xgriddisplay = derive_request_item("xgriddisplay", "none");
$ygriddisplay = derive_request_item("ygriddisplay", "none");
$gridpos = derive_request_item("gridposition", "back");
$xgridcolor = derive_request_item("xgridcolor", "purple");
$ygridcolor = derive_request_item("ygridcolor", "darkgreen");
$title = convert_special_chars(derive_request_item("title", ""));
$xtitle = convert_special_chars(derive_request_item("xtitle", ""));
$ytitle = convert_special_chars(derive_request_item("ytitle", ""));
$titlefont = derive_request_item("titlefont", "Arial");
$titlefontstyle = derive_request_item("titlefontstyle", "Normal");
$titlefontsize = derive_request_item("titlefontsize", 12);
$xtitlefont = derive_request_item("xtitlefont", "Arial");
$xtitlefontstyle = derive_request_item("xtitlefontstyle", "Normal");
$xtitlefontsize = derive_request_item("xtitlefontsize", 12);
$xtitlecolor = derive_request_item("xtitlecolor", "black");
$ytitlefont = derive_request_item("ytitlefont", "Arial");
$ytitlefontstyle = derive_request_item("ytitlefontstyle", "Normal");
$ytitlefontsize = derive_request_item("ytitlefontsize", 12);
$ytitlecolor = derive_request_item("ytitlecolor", "black");
$xtickinterval = derive_request_item("xtickint", 4);
$ytickinterval = derive_request_item("ytickint", 4);
$titlecolor = derive_request_item("titlecolor", "black");
$margincolor = derive_request_item("margincolor", "white");
$marginleft = derive_request_item("marginleft", 50);
$marginright = derive_request_item("marginright", 50);
$margintop = derive_request_item("margintop", 20);
$marginbottom = derive_request_item("marginbottom", 60);
$xticklabint = derive_request_item("xticklabint", 2);
$xaxiscolor = derive_request_item("xaxiscolor", "red");
$yaxiscolor = derive_request_item("yaxiscolor", "green");
$xaxisfontcolor = derive_request_item("xaxisfontcolor", "purple");
$yaxisfontcolor = derive_request_item("yaxisfontcolor", "gray");
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
	if ( array_key_exists("$vval", $_REQUEST ) )
	{
		$vals = explode(",", $_REQUEST["$vval"]);
		$plot[$v] = array(
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
	$graph = new Graph($width,$height,"auto");	
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

$lplot = array();
$lplotct = 0;

foreach ( $plot as $k => $v )
{
	switch ( $v["type"] )
	{
		case "PIE":
			$lplot[$lplotct]=new PiePlot($v["data"]);
			$lplot[$lplotct]->SetColor($v["linecolor"]);
			foreach ( $xlabels as $k => $v )
			{
				$xlabels[$k] = $v."\n %.1f%%";
			}
			$lplot[$lplotct]->SetLabels($xlabels,1.0);
			$graph->Add($lplot[$lplotct]);
			break;
		case "PIE3D":
			$lplot[$lplotct]=new PiePlot3D($v["data"]);
			$lplot[$lplotct]->SetColor($v["linecolor"]);
			foreach ( $xlabels as $k => $v )
			{
				$xlabels[$k] = $v."\n %.1f%%";
			}
			$lplot[$lplotct]->SetLabels($xlabels,1.0);
			$graph->Add($lplot[$lplotct]);
			break;
		case "STACKEDBAR":
		case "BAR":
			$lplot[$lplotct]=new BarPlot($v["data"]);
			$lplot[$lplotct]->SetColor($v["linecolor"]);
			$lplot[$lplotct]->SetWidth(0.8);
			//$lplot[$lplotct]->SetWeight(5);
			if ( $v["fillcolor"] )
				$lplot[$lplotct]->SetFillColor($v["fillcolor"]);
			if ( $v["legend"] )
				$lplot[$lplotct]->SetLegend($v["legend"]);
			$graph->Add($lplot[$lplotct]);
			break;
		case "LINE":
		default;
			if ( count($v["data"]) == 1 )
				$v["data"][] = 0;
			$lplot[$lplotct]=new LinePlot($v["data"]);
			$lplot[$lplotct]->SetColor($v["linecolor"]);
			$lplot[$lplotct]->SetWeight(5);
			if ( $v["fillcolor"] )
				$lplot[$lplotct]->SetFillColor($v["fillcolor"]);
			if ( $v["legend"] )
				$lplot[$lplotct]->SetLegend($v["legend"]);
			$graph->Add($lplot[$lplotct]);
			break;
	}
	$lplotct++;
}

$graph->title->Set($title);
$graph->xaxis->title->Set($xtitle);
$graph->yaxis->title->Set($ytitle);

$graph->xgrid->SetColor($xgridcolor);
$graph->ygrid->SetColor($ygridcolor);

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


$graph->title->SetFont($fontfamilies[$titlefont],$fontstyles[$titlefontstyle], $titlefontsize);
$graph->title->SetColor($titlecolor);
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
$graph->SetFrame(true,'darkblue',2); 
$graph->SetFrameBevel(2,true,'black'); 

if ( $gridpos == "front" )
	$graph->SetGridDepth(DEPTH_FRONT); 

// Display the graph
$graph->Stroke();
?>
