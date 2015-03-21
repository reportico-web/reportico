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

 * File:        swgraph_pchart.php
 *
 * Contains functionality for generating inline report
 * graphs. The Reportico engine will either - depending
 * on the output format - generate URL string to create
 * the graph or generate the graph itself using these
 * classes.
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swgraph_pchart.php,v 1.17 2014/05/17 15:12:31 peter Exp $
 */


error_reporting(E_ALL);

if ( function_exists( "imagecreatefromstring" ) )
{
    include_once("pChart/pChart.class");
    include_once("pChart/pData.class");
    include_once("pChart/pCache.class");

    $fontpath = find_best_location_in_include_path( "pChart/fonts" );
    define ( "PCHARTFONTS_DIR", $fontpath."/" );
}
else
{
    echo "GD not installed ( imagecreatefromstring function does not exist )";
    die;
}
require_once('swutil.php');



/**
 * Class reportico_graph
 *
 * Storage and generation of report graphs. Holds
 * everything necessary such as titles, axis formatting, 
 * graph type, colours etc
 */
class reportico_graph
{
	var $calling_mode = "INTERNAL";
	var $graph_column = "";
	var $title = "Set Title";
	var $title_actual = "Set Title";
	var $xtitle = "Set Title";
	var $ytitle = "Set Title";
	var $xtitle_actual = "Set Title";
	var $ytitle_actual = "Set Title";
	var $graphcolor = ".DEFAULT";
	var $width = ".DEFAULT";
	var $height = ".DEFAULT";
	var $width_actual = 400;
	var $height_actual = 200;
	var $width_pdf_actual = 400;
	var $height_pdf_actual = 200;
	var $xtickinterval_actual = 1;
	var $xticklabelinterval_actual = 1;
	var $xaxiscolor_actual = "black";
	var $ytickinterval_actual = 1;
	var $yticklabelinterval_actual = 1;
	var $yaxiscolor_actual = "black";
	var $graphcolor_actual = "white";
	var $margincolor_actual;
	var $marginleft_actual;
	var $marginright_actual;
	var $margintop_actual;
	var $marginbottom_actual;
	var $gridpos_actual = ".DEFAULT";
	var $xgriddisplay_actual = ".DEFAULT";
	var $xgridcolor_actual = ".DEFAULT";
	var $ygriddisplay_actual = ".DEFAULT";
	var $ygridcolor_actual = ".DEFAULT";
	var $titlefont_actual = "";
	var $titlefontstyle_actual = "";
	var $titlefontsize_actual = ".DEFAULT";
	var $titlecolor_actual = ".DEFAULT";
	var $xtitlefont_actual = "";
	var $xtitlefontstyle_actual = "";
	var $xtitlefontsize_actual = ".DEFAULT";
	var $xtitlecolor_actual = ".DEFAULT";
	var $ytitlefont_actual = "";
	var $ytitlefontstyle_actual = "";
	var $ytitlefontsize_actual = ".DEFAULT";
	var $ytitlecolor_actual = ".DEFAULT";
	var $xaxisfont_actual = "";
	var $xaxisfontstyle_actual = "";
	var $xaxisfontsize_actual = ".DEFAULT";
	var $xaxisfontcolor_actual = ".DEFAULT";
	var $yaxisfont_actual = "";
	var $yaxisfontstyle_actual = "";
	var $yaxisfontsize_actual = ".DEFAULT";
	var $yaxisfontcolor_actual = ".DEFAULT";

	var $width_pdf = ".DEFAULT";
	var $height_pdf = ".DEFAULT";
	var $titlefont = ".DEFAULT";
	var $titlefontstyle = ".DEFAULT";
	var $titlefontsize = ".DEFAULT";

	var $gridpos = ".DEFAULT";
	var $xgriddisplay = ".DEFAULT";
	var $xgridcolor = ".DEFAULT";
	var $ygriddisplay = ".DEFAULT";
	var $ygridcolor = ".DEFAULT";

	var $yaxiscolor = ".DEFAULT";
	var $yaxisfont = ".DEFAULT";
	var $yaxisfontstyle = ".DEFAULT";
	var $yaxisfontsize = ".DEFAULT";
	var $yaxisfontcolor = ".DEFAULT";
	var $xaxiscolor = ".DEFAULT";
	var $xaxisfont = ".DEFAULT";
	var $xaxisfontstyle = ".DEFAULT";
	var $xaxisfontsize = ".DEFAULT";
	var $xaxisfontcolor = ".DEFAULT";
	var $xtitlefont = ".DEFAULT";
	var $xtitlefontstyle = ".DEFAULT";
	var $xtitlefontsize = ".DEFAULT";
	var $xtitlecolor = ".DEFAULT";
	var $xtickinterval = ".DEFAULT";
	var $xticklabelinterval = ".DEFAULT";
	var $ytitlefont = ".DEFAULT";
	var $ytitlefontstyle = ".DEFAULT";
	var $ytitlefontsize = ".DEFAULT";
	var $ytitlecolor = ".DEFAULT";
	var $ytickinterval = ".DEFAULT";
	var $yticklabelinterval = ".DEFAULT";
	var $titlecolor = ".DEFAULT";
	var $margincolor = ".DEFAULT";
	var $marginleft = ".DEFAULT";
	var $marginright = ".DEFAULT";
	var $margintop = ".DEFAULT";
	var $marginbottom = ".DEFAULT";
	var $xlabel_column = "";
	var $ylabel_column = "";
	var $xlabels = array();
	var $ylabels = array();
	var $plot = array();
	var $reportico = false;

	function __construct(&$reportico, $in_mode, $in_val = "")
	{
		$this->reportico = $reportico;
		$this->calling_mode = $in_mode;
		$this->graph_column = $in_val;
	}

	function &create_plot($in_query)
	{
        	$pl = array(
                "number" => count($this->plot),
                "name" => $in_query,
                "type" => "LINE",
                "fillcolor" => "",
                "linecolor" => "",
                "datatype" => "number",
                "legend" => "",
                "data" => array()
            );
        $this->plot[] =& $pl;

		return ( $pl );
	}

	function clear_data()
	{
		foreach ( $this->plot as $k => $v )
		{
			$this->plot[$k]["data"] = array();
		}
		$this->xlabels = array();
		$this->ylabels = array();
	}

	function add_xlabel($in_val)
	{
		$in_val = @preg_replace("/&/", "+", $in_val);
		$this->xlabels[] = $in_val;
	}

	function add_plot_value($in_query, $plot_no, $in_val)
	{
		$in_val = trim($in_val);
		if ( !$in_val )
			$in_val = 0;
		foreach ( $this->plot as $k => $v )
		{
			if ( $v["name"] == $in_query && $v["number"] == $plot_no  )
			{
				switch ( $v["datatype"] )
				{
					case "hhmmss":
						$this->plot[$k]["data"][] = hhmmss_to_seconds($in_val);
						break;

					default:
						$this->plot[$k]["data"][] = $in_val;
				}
			}
		}
	}

	function convert_special_chars($intext)
	{
		$outtext = preg_replace("/&/", "<AMPERSAND>", $intext);
		return $outtext;
	}

	function generate_url_params($target_format, $session_placeholder=false)
	{
		$this->apply_defaults();

		$result = "";
		$url = "";
		$url .= "title=".$this->convert_special_chars($this->title_actual);
		$url .= "&xtitle=".$this->convert_special_chars($this->xtitle_actual);
		$url .= "&ytitle=".$this->convert_special_chars($this->ytitle_actual);
        if ( $target_format == "PDF" )
        {
		    $url .= "&width=".$this->width_pdf_actual;
		    $url .= "&height=".$this->height_pdf_actual;
        }
        else
        {
		    $url .= "&width=".$this->width_actual;
		    $url .= "&height=".$this->height_actual;
        }
		$url .= "&graphcolor=".$this->graphcolor_actual;
		$url .= "&gridposition=".$this->gridpos_actual;
		$url .= "&xgriddisplay=".$this->xgriddisplay_actual;
		$url .= "&xgridcolor=".$this->xgridcolor_actual;
		$url .= "&ygriddisplay=".$this->ygriddisplay_actual;
		$url .= "&ygridcolor=".$this->ygridcolor_actual;
		$url .= "&titlefont=".$this->titlefont_actual;
		$url .= "&titlefontstyle=".$this->titlefontstyle_actual;
		$url .= "&titlefontsize=".$this->titlefontsize_actual;
		$url .= "&titlecolor=".$this->titlecolor_actual;
		$url .= "&xaxiscolor=".$this->xaxiscolor_actual;
		$url .= "&xaxisfont=".$this->xaxisfont_actual;
		$url .= "&xaxisfontstyle=".$this->xaxisfontstyle_actual;
		$url .= "&xaxisfontsize=".$this->xaxisfontsize_actual;
		$url .= "&xaxisfontcolor=".$this->xaxisfontcolor_actual;
		$url .= "&yaxiscolor=".$this->yaxiscolor_actual;
		$url .= "&yaxisfont=".$this->yaxisfont_actual;
		$url .= "&yaxisfontstyle=".$this->yaxisfontstyle_actual;
		$url .= "&yaxisfontsize=".$this->yaxisfontsize_actual;
		$url .= "&yaxisfontcolor=".$this->yaxisfontcolor_actual;
		$url .= "&xtitlefont=".$this->xtitlefont_actual;
		$url .= "&xtitlefontstyle=".$this->xtitlefontstyle_actual;
		$url .= "&xtitlefontsize=".$this->xtitlefontsize_actual;
		$url .= "&xtitlecolor=".$this->xtitlecolor_actual;
		$url .= "&xtickint=".$this->xtickinterval_actual;
		$url .= "&xticklabint=".$this->xticklabelinterval_actual;
		$url .= "&ytitlefont=".$this->ytitlefont_actual;
		$url .= "&ytitlefontstyle=".$this->ytitlefontstyle_actual;
		$url .= "&ytitlefontsize=".$this->ytitlefontsize_actual;
		$url .= "&ytitlecolor=".$this->ytitlecolor_actual;
		$url .= "&ytickint=".$this->ytickinterval_actual;
		$url .= "&yticklabint=".$this->yticklabelinterval_actual;
		$url .= "&margincolor=".$this->margincolor_actual;
		$url .= "&marginleft=".$this->marginleft_actual;
		$url .= "&marginright=".$this->marginright_actual;
		$url .= "&margintop=".$this->margintop_actual;
		$url .= "&marginbottom=".$this->marginbottom_actual;
		$url .= "&xlabels=".implode(",",$this->xlabels);


		foreach ( $this->plot as $k => $v )
		{
			$str = implode(",",$v["data"]);
			$url .= "&plotname$k=".$v["name"];
			$url .= "&plotdata$k=$str";
			$url .= "&plottype$k=".$v["type"];
			$url .= "&plotlinecolor$k=".$v["linecolor"];
			if ( $v["legend"] )
				$url .= "&plotlegend$k=".$v["legend"];
			if ( $v["fillcolor"] )
				$url .= "&plotfillcolor$k=".$v["fillcolor"];
		}
	
		if ( $session_placeholder)
		{
			$ses = "graph_".$session_placeholder;
            set_reportico_session_param($ses, $url);
			$url = "graphid=".$ses."&time=".time();
		}

        // Select the appropriate reporting engine
		$dyngraph = "dyngraph.php";
        if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE == "PCHART" )
		    $dyngraph = "dyngraph_pchart.php";

        $dr = get_reportico_url_path();
        $dyngraph = $dr."/".find_best_url_in_include_path( $dyngraph );

        if ( $this->reportico->framework_parent )
        {
            $dyngraph = "";
            if ( $this->reportico->reportico_ajax_mode == "2" )
                $dyngraph = preg_replace("/ajax/", "graph", $this->reportico->reportico_ajax_script_url);
        }
        $forward_url_params = session_request_item('forward_url_get_parameters_graph' );
	if ( !$forward_url_params )
        	$forward_url_params = session_request_item('forward_url_get_parameters', $this->reportico->forward_url_get_parameters);

        if ( $forward_url_params )
            $url .= "&".$forward_url_params;
        $url .= "&reportico_call_mode=graph_pchart";
        $url .= "&reportico_session_name=".reportico_session_name();
		$result = '<img class="swRepGraph" src=\''.$dyngraph.'?'.$url.'\'>';

		return $result;
	}

	function set_graph_color($in_col)
	{
		$this->graphcolor = $in_col;
	}
	function set_graph_column($in_val)
	{
		$this->graph_column = $in_val;
	}
	function set_title($in_title)
	{
		$this->title = $in_title;
	}
	function set_xtitle($in_xtitle)
	{
		$this->xtitle = $in_xtitle;
	}
	function set_ytitle($in_ytitle)
	{
		$this->ytitle = $in_ytitle;
	}
	function set_width_pdf($in_width)
	{
		$this->width_pdf = $in_width;
	}
	function set_height_pdf($in_height)
	{
		$this->height_pdf = $in_height;
	}
	function set_width($in_width)
	{
		$this->width = $in_width;
	}
	function set_height($in_height)
	{
		$this->height = $in_height;
	}
	function set_grid($in_pos,$in_xdisplay, $in_xcolor, $in_ydisplay, $in_ycolor)
	{
			$this->gridpos = $in_pos;
			$this->xgriddisplay = $in_xdisplay;
			$this->xgridcolor = $in_xcolor;
			$this->ygriddisplay = $in_ydisplay;
			$this->ygridcolor = $in_ycolor;
	}
	function set_xlabel_column($in_xlabel_column)
	{
		$this->xlabel_column = $in_xlabel_column;
	}
	function set_ylabel_column($in_ylabel_column)
	{
		$this->ylabel_column = $in_ylabel_column;
	}


	function set_title_font($in_font, $in_style, $in_size, $in_col)
	{
			$this->titlefont = $in_font;
			$this->titlefontstyle = $in_style;
			$this->titlefontsize = $in_size;
			$this->titlecolor = $in_col;
	}

	function set_xtitle_font($in_font, $in_style, $in_size, $in_col)
	{
			$this->xtitlefont = $in_font;
			$this->xtitlefontstyle = $in_style;
			$this->xtitlefontsize = $in_size;
			$this->xtitlecolor = $in_col;
	}

	function set_ytitle_font($in_font, $in_style, $in_size, $in_col)
	{
			$this->ytitlefont = $in_font;
			$this->ytitlefontstyle = $in_style;
			$this->ytitlefontsize = $in_size;
			$this->ytitlecolor = $in_col;
	}

	function set_xaxis_font($in_font, $in_style, $in_size, $in_col)
	{
			$this->xaxisfont = $in_font;
			$this->xaxisfontstyle = $in_style;
			$this->xaxisfontsize = $in_size;
			$this->xaxisfontcolor = $in_col;
	}

	function set_yaxis_font($in_font, $in_style, $in_size, $in_col)
	{
			$this->yaxisfont = $in_font;
			$this->yaxisfontstyle = $in_style;
			$this->yaxisfontsize = $in_size;
			$this->yaxisfontcolor = $in_col;
	}

	function set_xaxis($in_tic,$in_lab_tic,$in_col)
	{
			$this->xtickinterval = $in_tic;
			$this->xticklabelinterval = $in_lab_tic;
			$this->xaxiscolor = $in_col;
	}

	function set_yaxis($in_tic,$in_lab_tic,$in_col)
	{
			$this->ytickinterval = $in_tic;
			$this->yticklabelinterval = $in_lab_tic;
			$this->yaxiscolor = $in_col;

	}

	function set_margin_color($in_col)
	{
			$this->margincolor = $in_col;
	}

	function set_margins($in_lt, $in_rt, $in_tp, $in_bt)
	{
			$this->marginleft = $in_lt;
			$this->marginright = $in_rt;
			$this->margintop = $in_tp;
			$this->marginbottom = $in_bt;
	}


	function apply_defaults ()
	{
		$this->width_actual = check_for_default("GraphWidth", $this->width);
		$this->height_actual = check_for_default("GraphHeight", $this->height);
		$this->width_pdf_actual = check_for_default("GraphWidthPDF", $this->width_pdf);
		$this->height_pdf_actual = check_for_default("GraphHeightPDF", $this->height_pdf);
		$this->xaxiscolor_actual = check_for_default("XAxisColor", $this->xaxiscolor);
		$this->xticklabelinterval_actual = check_for_default("XTickLabelInterval", $this->xticklabelinterval);
		$this->xtickinterval_actual = check_for_default("XTickInterval", $this->xtickinterval);
		$this->yaxiscolor_actual = check_for_default("YAxisColor", $this->yaxiscolor);
		$this->yticklabelinterval_actual = check_for_default("YTickLabelInterval", $this->yticklabelinterval);
		$this->ytickinterval_actual = check_for_default("YTickInterval", $this->ytickinterval);
		$this->graphcolor_actual = check_for_default("GraphColor", $this->graphcolor);
		$this->marginbottom_actual = check_for_default("MarginBottom", $this->marginbottom);
		$this->margintop_actual = check_for_default("MarginTop", $this->margintop);
		$this->marginleft_actual = check_for_default("MarginLeft", $this->marginleft);
		$this->marginright_actual = check_for_default("MarginRight", $this->marginright);
		$this->margincolor_actual = check_for_default("MarginColor", $this->margincolor);
		$this->gridpos_actual = check_for_default("GridPosition", $this->gridpos);
		$this->xgriddisplay_actual = check_for_default("XGridDisplay", $this->xgriddisplay);
		$this->xgridcolor_actual = check_for_default("XGridColor", $this->xgridcolor);
		$this->ygriddisplay_actual = check_for_default("YGridDisplay", $this->ygriddisplay);
		$this->ygridcolor_actual = check_for_default("YGridColor", $this->ygridcolor);

		$this->titlefont_actual = check_for_default("TitleFont", $this->titlefont);
		$this->titlefontstyle_actual = check_for_default("TitleFontStyle", $this->titlefontstyle);
		$this->titlefontsize_actual = check_for_default("TitleFontSize", $this->titlefontsize);
		$this->titlecolor_actual = check_for_default("TitleColor", $this->titlecolor);
		$this->xtitlefont_actual = check_for_default("XTitleFont", $this->xtitlefont);
		$this->xtitlefontstyle_actual = check_for_default("XTitleFontStyle", $this->xtitlefontstyle);
		$this->xtitlefontsize_actual = check_for_default("XTitleFontSize", $this->xtitlefontsize);
		$this->xtitlecolor_actual = check_for_default("XTitleColor", $this->xtitlecolor);
		$this->ytitlefont_actual = check_for_default("YTitleFont", $this->ytitlefont);
		$this->ytitlefontstyle_actual = check_for_default("YTitleFontStyle", $this->ytitlefontstyle);
		$this->ytitlefontsize_actual = check_for_default("YTitleFontSize", $this->ytitlefontsize);
		$this->ytitlecolor_actual = check_for_default("YTitleColor", $this->ytitlecolor);
		$this->xaxisfont_actual = check_for_default("XAxisFont", $this->xaxisfont);
		$this->xaxisfontstyle_actual = check_for_default("XAxisFontStyle", $this->xaxisfontstyle);
		$this->xaxisfontsize_actual = check_for_default("XAxisFontSize", $this->xaxisfontsize);
		$this->xaxisfontcolor_actual = check_for_default("XAxisFontColor", $this->xaxisfontcolor);
		$this->xaxiscolor_actual = check_for_default("XAxisColor", $this->xaxiscolor);
		$this->yaxisfont_actual = check_for_default("YAxisFont", $this->yaxisfont);
		$this->yaxisfontstyle_actual = check_for_default("YAxisFontStyle", $this->yaxisfontstyle);
		$this->yaxisfontsize_actual = check_for_default("YAxisFontSize", $this->yaxisfontsize);
		$this->yaxisfontcolor_actual = check_for_default("YAxisFontColor", $this->yaxisfontcolor);
		$this->yaxiscolor_actual = check_for_default("YAxisColor", $this->yaxiscolor);

	}

	function apply_defaults_internal ()
	{
		$this->graphcolor = check_for_default("GraphColor", $this->graphcolor);
		$this->xaxiscolor = check_for_default("XAxisColor", $this->xaxiscolor);
		$this->xticklabelinterval = check_for_default("XTickLabelInterval", $this->xticklabelinterval);
		$this->xtickinterval = check_for_default("XTickInterval", $this->xtickinterval);
		$this->yaxiscolor = check_for_default("YAxisColor", $this->yaxiscolor);
		$this->yticklabelinterval = check_for_default("YTickLabelInterval", $this->yticklabelinterval);
		$this->ytickinterval = check_for_default("YTickInterval", $this->ytickinterval);
		$this->graphcolor = check_for_default("GraphColor", $this->graphcolor);
		$this->marginbottom = check_for_default("MarginBottom", $this->marginbottom);
		$this->margintop = check_for_default("MarginTop", $this->margintop);
		$this->marginleft = check_for_default("MarginLeft", $this->marginleft);
		$this->marginright = check_for_default("MarginRight", $this->marginright);
		$this->margincolor = check_for_default("MarginColor", $this->margincolor);
		$this->gridpos = check_for_default("GridPosition", $this->gridpos);
		$this->xgriddisplay = check_for_default("XGridDisplay", $this->xgriddisplay);
		$this->xgridcolor = check_for_default("XGridColor", $this->xgridcolor);
		$this->ygriddisplay = check_for_default("YGridDisplay", $this->ygriddisplay);
		$this->ygridcolor = check_for_default("YGridColor", $this->ygridcolor);

		$this->titlefont = check_for_default("TitleFont", $this->titlefont);
		$this->titlefontstyle = check_for_default("TitleFontStyle", $this->titlefontstyle);
		$this->titlefontsize = check_for_default("TitleFontSize", $this->titlefontsize);
		$this->titlecolor = check_for_default("TitleColor", $this->titlecolor);
		$this->xtitlefont = check_for_default("XTitleFont", $this->xtitlefont);
		$this->xtitlefontstyle = check_for_default("XTitleFontStyle", $this->xtitlefontstyle);
		$this->xtitlefontsize = check_for_default("XTitleFontSize", $this->xtitlefontsize);
		$this->xtitlecolor = check_for_default("XTitleColor", $this->xtitlecolor);
		$this->ytitlefont = check_for_default("YTitleFont", $this->ytitlefont);
		$this->ytitlefontstyle = check_for_default("YTitleFontStyle", $this->ytitlefontstyle);
		$this->ytitlefontsize = check_for_default("YTitleFontSize", $this->ytitlefontsize);
		$this->ytitlecolor = check_for_default("YTitleColor", $this->ytitlecolor);
		$this->xaxisfont = check_for_default("XAxisFont", $this->xaxisfont);
		$this->xaxisfontstyle = check_for_default("XAxisFontStyle", $this->xaxisfontstyle);
		$this->xaxisfontsize = check_for_default("XAxisFontSize", $this->xaxisfontsize);
		$this->xaxisfontcolor = check_for_default("XAxisFontColor", $this->xaxisfontcolor);
		$this->xaxiscolor = check_for_default("XAxisColor", $this->xaxiscolor);
		$this->yaxisfont = check_for_default("YAxisFont", $this->yaxisfont);
		$this->yaxisfontstyle = check_for_default("YAxisFontStyle", $this->yaxisfontstyle);
		$this->yaxisfontsize = check_for_default("YAxisFontSize", $this->yaxisfontsize);
		$this->yaxisfontcolor = check_for_default("YAxisFontColor", $this->yaxisfontcolor);
		$this->yaxiscolor = check_for_default("YAxisColor", $this->yaxiscolor);

	}

	function generate_graph_image ($outputfile)
	{
        // Create Graph Dataset and set axis attributes
        $graphData = new pData();
$graphData->setYAxisName($this->ytitle);
$graphData->AddPoint($this->xlabels,"xaxis");
//$graphData->SetSerieName("xaxis","xaxis");
$graphData->SetAbsciseLabelSerie("xaxis");
$graphData->setXAxisName($this->xtitle);


// Add each series of plot values to dataset, but Reportico will 
// duplicate series were the same data are displayed in different forms
// so only add each unique series once
$seriesadded = array();
foreach ( $this->plot as $k => $v )
{
	$series = $v["name"].$k;
	$graphData->AddPoint($v["data"],$series);
	$graphData->SetSerieName($v["legend"], $series);
    $graphData->AddSerie($series);
}


/*
switch ( $this->xgriddisplay )
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

switch ( $this->ygriddisplay )
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
$graph->xaxis->SetTickLabels($this->xlabels);
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

		$this->apply_defaults_internal();

//echo $this->width_pdf_actual.",".$this->height_pdf_actual."<BR>";
$graphImage = new pChart($this->width_pdf_actual,$this->height_pdf_actual); 

/* Turn of Antialiasing */
$graphImage->Antialias = TRUE;

// Add gradient fill from chosen background color to white
$startgradient=htmltorgb("#ffffff");
//$graphImage->drawGradientArea(0,0,$width,$height,DIRECTION_VERTICAL,array(
		//"StartR"=>$startgradient[0], "StartG"=>$startgradient[1], "StartB"=>$startgradient[2],
		//"EndR"=>$color[0], "EndG"=>$color[1], "EndB"=>$color[2],"Alpha"=>100));

 /* Add a border to the picture */
 //$graphImage->drawRectangle(0,0,$width - 1,$height - 1,200,200,200);

$graphImage->setFontProperties(PCHARTFONTS_DIR.$this->xaxisfont,$this->xaxisfontsize);

/* Define the chart area */
$graphImage->setGraphArea($this->marginleft_actual,$this->margintop_actual,$this->width_pdf_actual - $this->marginright_actual,$this->height_pdf_actual - $this->marginbottom_actual);

 $graphImage->drawFilledRoundedRectangle(3,3, $this->width_pdf_actual - 3,$this->height_pdf_actual - 3,5,240,240,240);
 $graphImage->drawRoundedRectangle(1,1,$this->width_pdf_actual - 1,$this->height_pdf_actual - 1,5,230,230,230);


// Before plotting a series ensure they are all not drawable.
/// Plot the chart data
$stackeddrawn = false;
$linedrawn = false;
$scatterdrawn = false;
$piedrawn = false;
$stackedexists = false;
$overlayexists = false;
$barexists = false;
foreach ( $this->plot as $k => $v )
{
	if ( $v["type"] == "OVERLAYBAR" ) 
		$overlayexists = true;
	if ( $v["type"] == "STACKEDBAR" ) 
		$stackedexists = true;
	if ( $v["type"] == "STACKEDBAR" || $v["type"] == "BAR") 
		$barexists = true;

    // Set plot colors
    if ( $v["linecolor"] )
        $graphImage->Palette[$k] = htmltorgb_pchart($v["linecolor"]);

	$url .= "&plotlinecolor$k=".$v["linecolor"];
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
	foreach ( $this->plot as $k => $v )
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
foreach ( $this->plot as $k => $v )
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

$graphImage->setFontProperties(PCHARTFONTS_DIR.$this->xtitlefont,$this->xtitlefontsize);

if ( $scalerequired )
{
    $graphImage->setGraphArea($this->marginleft_actual,$this->margintop_actual,$this->width_pdf_actual - $this->marginright,$this->height_pdf_actual - $this->marginbottom_actual);
    //$graphImage->drawGraphAreaGradient(240,240,240,-20);   

    // Automatic generation of x tick interval based on number of ticks
    if ( $this->xticklabelinterval_actual == "AUTO" )
    {
        $labct = count($this->plot[0]["data"]);
        $this->xticklabelinterval_actual = floor ($labct / 35 ) + 1 ;
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
            $this->xticklabelinterval_actual,       // skip labels
            FALSE    // Right scale
            );
    }
    $graphImage->drawGrid(2,TRUE,230,230,230,45);
}
else
{
    $this->marginright= 5;
    $this->marginbottom= 5;
    $this->marginleft= 5;
    $this->marginright_actual= 5;
    $this->marginbottom_actual= 5;
    $this->marginleft_actual= 5;
    //$this->margintop_actual = 5;
    $graphImage->setGraphArea($this->marginleft,$this->margintop_actual,$this->width_pdf_actual - $this->marginright,$this->height_pdf_actual - $this->marginbottom);
    $graphImage->drawGraphAreaGradient(240,240,240,-10);  

}

// If there's a Pie chart we want to draw different legends
$piechart = false;
foreach ( $this->plot as $k => $v )
{
    disableAllSeries($this->plot, $graphData);
	$series = $v["name"].$k;

	setSerieDrawable($this->plot, $graphData, $series,TRUE);

	switch ( $v["type"] ) {
	case "PIE":
        $piedrawn = true;
		$piechart = true;
        $graphImage->drawFilledCircle( ( $this->width_pdf_actual / 2 ) + 2,$this->margintop_actual + 2 + ( ( $this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) /  2),
                    ( $this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20 ) * 0.45  + 1,
                    200,200,200); 
        $graphImage->drawBasicPieGraph($graphData->GetData(),$graphData->GetDataDescription(),
                    $this->width_pdf_actual / 2,
                    $this->margintop_actual + ( ( $this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) /  2), 
                    ( $this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20 ) * 0.45  ,
                    PIE_PERCENTAGE_LABEL,
                    255,255,218);
		break;

	case "PIE3D":
        $piedrawn = true;
		$piechart = true;
        $graphImage->drawPieGraph($graphData->GetData(),$graphData->GetDataDescription(),
                    $this->width_pdf_actual / 2,
                    $this->margintop_actual + ( ( $this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) /  2), 
                    ( $this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20 ) * 0.50  ,
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
			foreach ( $this->plot as $k1 => $v1 )
			{
				if ( $v1["type"] == "BAR" || $v1["type"] == "STACKEDBAR" || $v1["type"] == "OVERLAYBAR" )
				{
					setSerieDrawable($this->plot, $graphData, $v1["name"].$k1,TRUE);
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
		foreach ( $this->plot as $k1 => $v1 )
		{
			if ( $v1["type"] == "SCATTER" )
			{
                if ( $ct == 0 ) $series1 = $v1["name"].$k1;
                if ( $ct == 1 ) $series2 = $v1["name"].$k1;
                $ct++;
				setSerieDrawable($this->plot, $graphData, $v1["name"].$k1,TRUE);
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
		foreach ( $this->plot as $k1 => $v1 )
		{
			if ( $v1["type"] == "LINE" )
			{
				setSerieDrawable($this->plot, $graphData, $v1["name"].$k1,TRUE);
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
	foreach ( $this->plot as $k1 => $v1 )
	{
		setSerieDrawable($this->plot, $graphData, $v1["name"].$k1,TRUE);
	}

    // Draw Legend if legend value has been set
    $drawlegend = false;
    foreach ( $this->plot as $k => $v )
    {
	    if ( isset($v["legend"]) && $v["legend"] )
        {
            // Temporarily Dont draw legend for Pie
	        //if ( $piechart )
 		        //$graphImage->drawPieLegend($this->width_pdf_actual - 180,30,$graphData->GetData(), $graphData->GetDataDescription(), 250, 250, 250); 
	        if ( !$piechart )
 		        $graphImage->drawLegend($this->width_pdf_actual - 120, 30,$graphData->GetDataDescription(), 254, 254, 254, 0, 0, 0 );

            break;
        }
    }



    $graphImage->setFontProperties(PCHARTFONTS_DIR.$this->xtitlefont,$this->titlefontsize);
    $graphImage->drawTitle(0,24,$this->title_actual,50,50,50,$this->width_pdf_actual);
 	//$graphImage->setShadow(TRUE,array("X"=>0,"Y"=>0,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 	//$graphImage->Render("example.png");
 	//$graphImage->Stroke();

 	$graphImage->render($outputfile);
	    return true;	
	}
}


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
