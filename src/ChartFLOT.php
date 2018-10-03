<?php
/*

 * File:        swgraph_flot.php
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
 * @version $Id: swgraph_flot.php,v 1.15 2013/09/26 19:23:06 peter Exp $
 */

namespace Reportico\Engine;

error_reporting(E_ALL);

if ( function_exists( "imagecreatefromstring" ) )
{
    //include("pChart/class/pData.class.php");
    //include("pChart/class/pDraw.class.php");
    //include("pChart/class/pImage.class.php");
    //include("pChart/class/pPie.class.php");
    include("pChart/pChart.class");
    include("pChart/pData.class");
    include("pChart/pCache.class");

    $fontpath = ReporticoUtility::findBestLocationInIncludePath( "pChart/fonts" );
    define ( "PCHARTFONTS_DIR", $fontpath."/" );
}
else
{
    echo "GD not installed ( imagecreatefromstring function does not exist )";
    die;
}


/**
 * Class reportico_graph
 *
 * Storage and generation of report graphs. Holds
 * everything necessary such as titles, axis formatting, 
 * graph type, colours etc
 */
class ChartFLOT
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
                "linecolor" => "black",
                "datatype" => "number",
                "legend" => "",
                "data" => array()
            );
        $this->plot[] =& $pl;

		return ( $pl );
	}

	function clearData()
	{
		foreach ( $this->plot as $k => $v )
		{
			$this->plot[$k]["data"] = array();
		}
		$this->xlabels = array();
		$this->ylabels = array();
	}

	function addXlabel($in_val)
	{
		$in_val = @preg_replace("/&/", "+", $in_val);
		$this->xlabels[] = $in_val;
	}

	function addPlotValue($in_query, $plot_no, $in_val)
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
						$this->plot[$k]["data"][] = ReporticoUtility::hhmmssToSeconds($in_val);
						break;

					default:
						$this->plot[$k]["data"][] = $in_val;
				}
			}
		}
	}

	function convertSpecialChars($intext)
	{
		$outtext = preg_replace("/&/", "<AMPERSAND>", $intext);
		return $outtext;
	}

	function generateUrlParams($target_format, $sessionPlaceholder=false)
	{
                $sessionClass = ReporticoSession();

		$this->applyDefaults();


		$result = "";
        $url = "";
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
	
		if ( $sessionPlaceholder)
		{
			$ses = "graph_".$sessionPlaceholder;
                        $sessionClass::setReporticoSessionParam($ses, $url);
			$url = "graphid=".$ses."&time=".time();
		}


        $js = "";
        $js .= "<div class=\"reportico-chart-container\"> ".$this->convertSpecialChars($this->title_actual);
        $js .= "<div id=\"reportico_chart$sessionPlaceholder\" class=\"reportico-chart-placeholder\"></div> </div>\n";
        $js .= "<script>\n";
        $js .= "var placeholder = '#reportico_chart$sessionPlaceholder';\n";
        $js .= "var data = [\n";
        $lct = 0;
        foreach ( $this->plot as $k => $v )
        {
            if ( $lct > 0 )
                $js .= ",";
            $js .= "{";
            if ( $v["legend"] )
            $js .= " label: "."\"".$v["legend"]."\", ";
            switch ( $v["type"] )
            {
                case 'BAR':
                    $js .= " bars: { show: true, fill: true }, ";
                    break;

                case 'LINE':
                default:
                    //$js .= " lines: { show: true, fill: false }, ";
                    $js .= " bars: { show: true, fill: true }, ";
                    break;
            }
            $js .= " data: [";
            $lct1 = 0;
            foreach ( $v["data"] as $v )
            {
                if ( $lct1 > 0 )
                    $js .= ",";
                $js .= "[$lct1, $v]";
                $lct1++;
            }
            $js .= "] }\n";
            $lct++;
        }
        $js .= "];\n";
        $js .= "var options = {
                        series: {
                            bars: {
                                show: true,
                                align: \"center\",
                                barWidth: 0.8,
                                fill: true,
                                }
                            },
                        margin: [ 100, 100 ],
                        xaxis: {
                            axisLabel: '".$this->xtitle_actual."',
                            axisLabelUseCanvas: true,
                            axisLabelFontSizePixels: 20,
                            axisLabelFontFamily: 'Arial'
                        },
                        yaxis: {
                            axisLabel: '".$this->ytitle_actual."',
                            axisLabelUseCanvas: true
                        },
                        legend: {
                            labelBoxBorderColor: '#000000',
                            margin: [ 50, 50 ]
                        }
                    };\n";

        $js .= "var plot = reportico_jquery.plot(placeholder, data, options);\n";
        $js .= "</script>\n";
    

		$result = $js;


		return $result;
	}

	function setGraphColor($in_col)
	{
		$this->graphcolor = $in_col;
	}
	function setGraphColumn($in_val)
	{
		$this->graph_column = $in_val;
	}
	function setTitle($in_title)
	{
		$this->title = $in_title;
	}
	function setXtitle($in_xtitle)
	{
		$this->xtitle = $in_xtitle;
	}
	function setYtitle($in_ytitle)
	{
		$this->ytitle = $in_ytitle;
	}
	function setWidthPdf($in_width)
	{
		$this->width_pdf = $in_width;
	}
	function setHeightPdf($in_height)
	{
		$this->height_pdf = $in_height;
	}
	function setWidth($in_width)
	{
		$this->width = $in_width;
	}
	function setHeight($in_height)
	{
		$this->height = $in_height;
	}
	function setGrid($in_pos,$in_xdisplay, $in_xcolor, $in_ydisplay, $in_ycolor)
	{
			$this->gridpos = $in_pos;
			$this->xgriddisplay = $in_xdisplay;
			$this->xgridcolor = $in_xcolor;
			$this->ygriddisplay = $in_ydisplay;
			$this->ygridcolor = $in_ycolor;
	}
	function setXlabelColumn($in_xlabel_column)
	{
		$this->xlabel_column = $in_xlabel_column;
	}
	function setYlabelColumn($in_ylabel_column)
	{
		$this->ylabel_column = $in_ylabel_column;
	}


	function setTitleFont($in_font, $in_style, $in_size, $in_col)
	{
			$this->titlefont = $in_font;
			$this->titlefontstyle = $in_style;
			$this->titlefontsize = $in_size;
			$this->titlecolor = $in_col;
	}

	function setXtitleFont($in_font, $in_style, $in_size, $in_col)
	{
			$this->xtitlefont = $in_font;
			$this->xtitlefontstyle = $in_style;
			$this->xtitlefontsize = $in_size;
			$this->xtitlecolor = $in_col;
	}

	function setYtitleFont($in_font, $in_style, $in_size, $in_col)
	{
			$this->ytitlefont = $in_font;
			$this->ytitlefontstyle = $in_style;
			$this->ytitlefontsize = $in_size;
			$this->ytitlecolor = $in_col;
	}

	function setXaxisFont($in_font, $in_style, $in_size, $in_col)
	{
			$this->xaxisfont = $in_font;
			$this->xaxisfontstyle = $in_style;
			$this->xaxisfontsize = $in_size;
			$this->xaxisfontcolor = $in_col;
	}

	function setYaxisFont($in_font, $in_style, $in_size, $in_col)
	{
			$this->yaxisfont = $in_font;
			$this->yaxisfontstyle = $in_style;
			$this->yaxisfontsize = $in_size;
			$this->yaxisfontcolor = $in_col;
	}

	function setXaxis($in_tic,$in_lab_tic,$in_col)
	{
			$this->xtickinterval = $in_tic;
			$this->xticklabelinterval = $in_lab_tic;
			$this->xaxiscolor = $in_col;
	}

	function setYaxis($in_tic,$in_lab_tic,$in_col)
	{
			$this->ytickinterval = $in_tic;
			$this->yticklabelinterval = $in_lab_tic;
			$this->yaxiscolor = $in_col;

	}

	function setMarginColor($in_col)
	{
			$this->margincolor = $in_col;
	}

	function setMargins($in_lt, $in_rt, $in_tp, $in_bt)
	{
			$this->marginleft = $in_lt;
			$this->marginright = $in_rt;
			$this->margintop = $in_tp;
			$this->marginbottom = $in_bt;
	}


	function applyDefaults ()
	{
		$this->width_actual = ReporticoApp::getDefaultConfig("GraphWidth", $this->width);
		$this->height_actual = ReporticoApp::getDefaultConfig("GraphHeight", $this->height);
		$this->width_pdf_actual = ReporticoApp::getDefaultConfig("GraphWidthPDF", $this->width_pdf);
		$this->height_pdf_actual = ReporticoApp::getDefaultConfig("GraphHeightPDF", $this->height_pdf);
		$this->xaxiscolor_actual = ReporticoApp::getDefaultConfig("XAxisColor", $this->xaxiscolor);
		$this->xticklabelinterval_actual = ReporticoApp::getDefaultConfig("XTickLabelInterval", $this->xticklabelinterval);
		$this->xtickinterval_actual = ReporticoApp::getDefaultConfig("XTickInterval", $this->xtickinterval);
		$this->yaxiscolor_actual = ReporticoApp::getDefaultConfig("YAxisColor", $this->yaxiscolor);
		$this->yticklabelinterval_actual = ReporticoApp::getDefaultConfig("YTickLabelInterval", $this->yticklabelinterval);
		$this->ytickinterval_actual = ReporticoApp::getDefaultConfig("YTickInterval", $this->ytickinterval);
		$this->graphcolor_actual = ReporticoApp::getDefaultConfig("GraphColor", $this->graphcolor);
		$this->marginbottom_actual = ReporticoApp::getDefaultConfig("MarginBottom", $this->marginbottom);
		$this->margintop_actual = ReporticoApp::getDefaultConfig("MarginTop", $this->margintop);
		$this->marginleft_actual = ReporticoApp::getDefaultConfig("MarginLeft", $this->marginleft);
		$this->marginright_actual = ReporticoApp::getDefaultConfig("MarginRight", $this->marginright);
		$this->margincolor_actual = ReporticoApp::getDefaultConfig("MarginColor", $this->margincolor);
		$this->gridpos_actual = ReporticoApp::getDefaultConfig("GridPosition", $this->gridpos);
		$this->xgriddisplay_actual = ReporticoApp::getDefaultConfig("XGridDisplay", $this->xgriddisplay);
		$this->xgridcolor_actual = ReporticoApp::getDefaultConfig("XGridColor", $this->xgridcolor);
		$this->ygriddisplay_actual = ReporticoApp::getDefaultConfig("YGridDisplay", $this->ygriddisplay);
		$this->ygridcolor_actual = ReporticoApp::getDefaultConfig("YGridColor", $this->ygridcolor);

		$this->titlefont_actual = ReporticoApp::getDefaultConfig("TitleFont", $this->titlefont);
		$this->titlefontstyle_actual = ReporticoApp::getDefaultConfig("TitleFontStyle", $this->titlefontstyle);
		$this->titlefontsize_actual = ReporticoApp::getDefaultConfig("TitleFontSize", $this->titlefontsize);
		$this->titlecolor_actual = ReporticoApp::getDefaultConfig("TitleColor", $this->titlecolor);
		$this->xtitlefont_actual = ReporticoApp::getDefaultConfig("XTitleFont", $this->xtitlefont);
		$this->xtitlefontstyle_actual = ReporticoApp::getDefaultConfig("XTitleFontStyle", $this->xtitlefontstyle);
		$this->xtitlefontsize_actual = ReporticoApp::getDefaultConfig("XTitleFontSize", $this->xtitlefontsize);
		$this->xtitlecolor_actual = ReporticoApp::getDefaultConfig("XTitleColor", $this->xtitlecolor);
		$this->ytitlefont_actual = ReporticoApp::getDefaultConfig("YTitleFont", $this->ytitlefont);
		$this->ytitlefontstyle_actual = ReporticoApp::getDefaultConfig("YTitleFontStyle", $this->ytitlefontstyle);
		$this->ytitlefontsize_actual = ReporticoApp::getDefaultConfig("YTitleFontSize", $this->ytitlefontsize);
		$this->ytitlecolor_actual = ReporticoApp::getDefaultConfig("YTitleColor", $this->ytitlecolor);
		$this->xaxisfont_actual = ReporticoApp::getDefaultConfig("XAxisFont", $this->xaxisfont);
		$this->xaxisfontstyle_actual = ReporticoApp::getDefaultConfig("XAxisFontStyle", $this->xaxisfontstyle);
		$this->xaxisfontsize_actual = ReporticoApp::getDefaultConfig("XAxisFontSize", $this->xaxisfontsize);
		$this->xaxisfontcolor_actual = ReporticoApp::getDefaultConfig("XAxisFontColor", $this->xaxisfontcolor);
		$this->xaxiscolor_actual = ReporticoApp::getDefaultConfig("XAxisColor", $this->xaxiscolor);
		$this->yaxisfont_actual = ReporticoApp::getDefaultConfig("YAxisFont", $this->yaxisfont);
		$this->yaxisfontstyle_actual = ReporticoApp::getDefaultConfig("YAxisFontStyle", $this->yaxisfontstyle);
		$this->yaxisfontsize_actual = ReporticoApp::getDefaultConfig("YAxisFontSize", $this->yaxisfontsize);
		$this->yaxisfontcolor_actual = ReporticoApp::getDefaultConfig("YAxisFontColor", $this->yaxisfontcolor);
		$this->yaxiscolor_actual = ReporticoApp::getDefaultConfig("YAxisColor", $this->yaxiscolor);

	}

	function applyDefaultsInternal ()
	{
		$this->graphcolor = ReporticoApp::getDefaultConfig("GraphColor", $this->graphcolor);
		$this->xaxiscolor = ReporticoApp::getDefaultConfig("XAxisColor", $this->xaxiscolor);
		$this->xticklabelinterval = ReporticoApp::getDefaultConfig("XTickLabelInterval", $this->xticklabelinterval);
		$this->xtickinterval = ReporticoApp::getDefaultConfig("XTickInterval", $this->xtickinterval);
		$this->yaxiscolor = ReporticoApp::getDefaultConfig("YAxisColor", $this->yaxiscolor);
		$this->yticklabelinterval = ReporticoApp::getDefaultConfig("YTickLabelInterval", $this->yticklabelinterval);
		$this->ytickinterval = ReporticoApp::getDefaultConfig("YTickInterval", $this->ytickinterval);
		$this->graphcolor = ReporticoApp::getDefaultConfig("GraphColor", $this->graphcolor);
		$this->marginbottom = ReporticoApp::getDefaultConfig("MarginBottom", $this->marginbottom);
		$this->margintop = ReporticoApp::getDefaultConfig("MarginTop", $this->margintop);
		$this->marginleft = ReporticoApp::getDefaultConfig("MarginLeft", $this->marginleft);
		$this->marginright = ReporticoApp::getDefaultConfig("MarginRight", $this->marginright);
		$this->margincolor = ReporticoApp::getDefaultConfig("MarginColor", $this->margincolor);
		$this->gridpos = ReporticoApp::getDefaultConfig("GridPosition", $this->gridpos);
		$this->xgriddisplay = ReporticoApp::getDefaultConfig("XGridDisplay", $this->xgriddisplay);
		$this->xgridcolor = ReporticoApp::getDefaultConfig("XGridColor", $this->xgridcolor);
		$this->ygriddisplay = ReporticoApp::getDefaultConfig("YGridDisplay", $this->ygriddisplay);
		$this->ygridcolor = ReporticoApp::getDefaultConfig("YGridColor", $this->ygridcolor);

		$this->titlefont = ReporticoApp::getDefaultConfig("TitleFont", $this->titlefont);
		$this->titlefontstyle = ReporticoApp::getDefaultConfig("TitleFontStyle", $this->titlefontstyle);
		$this->titlefontsize = ReporticoApp::getDefaultConfig("TitleFontSize", $this->titlefontsize);
		$this->titlecolor = ReporticoApp::getDefaultConfig("TitleColor", $this->titlecolor);
		$this->xtitlefont = ReporticoApp::getDefaultConfig("XTitleFont", $this->xtitlefont);
		$this->xtitlefontstyle = ReporticoApp::getDefaultConfig("XTitleFontStyle", $this->xtitlefontstyle);
		$this->xtitlefontsize = ReporticoApp::getDefaultConfig("XTitleFontSize", $this->xtitlefontsize);
		$this->xtitlecolor = ReporticoApp::getDefaultConfig("XTitleColor", $this->xtitlecolor);
		$this->ytitlefont = ReporticoApp::getDefaultConfig("YTitleFont", $this->ytitlefont);
		$this->ytitlefontstyle = ReporticoApp::getDefaultConfig("YTitleFontStyle", $this->ytitlefontstyle);
		$this->ytitlefontsize = ReporticoApp::getDefaultConfig("YTitleFontSize", $this->ytitlefontsize);
		$this->ytitlecolor = ReporticoApp::getDefaultConfig("YTitleColor", $this->ytitlecolor);
		$this->xaxisfont = ReporticoApp::getDefaultConfig("XAxisFont", $this->xaxisfont);
		$this->xaxisfontstyle = ReporticoApp::getDefaultConfig("XAxisFontStyle", $this->xaxisfontstyle);
		$this->xaxisfontsize = ReporticoApp::getDefaultConfig("XAxisFontSize", $this->xaxisfontsize);
		$this->xaxisfontcolor = ReporticoApp::getDefaultConfig("XAxisFontColor", $this->xaxisfontcolor);
		$this->xaxiscolor = ReporticoApp::getDefaultConfig("XAxisColor", $this->xaxiscolor);
		$this->yaxisfont = ReporticoApp::getDefaultConfig("YAxisFont", $this->yaxisfont);
		$this->yaxisfontstyle = ReporticoApp::getDefaultConfig("YAxisFontStyle", $this->yaxisfontstyle);
		$this->yaxisfontsize = ReporticoApp::getDefaultConfig("YAxisFontSize", $this->yaxisfontsize);
		$this->yaxisfontcolor = ReporticoApp::getDefaultConfig("YAxisFontColor", $this->yaxisfontcolor);
		$this->yaxiscolor = ReporticoApp::getDefaultConfig("YAxisColor", $this->yaxiscolor);

	}

	function generateGraphImage ($outputfile)
	{
        return false;
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
