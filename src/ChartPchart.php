<?php
/*

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
 * @version $Id: swgraph_pchart.php,v 1.17 2014/05/17 15:12:31 peter Exp $
 */
namespace Reportico\Engine;


use CpChart\Data;
use CpChart\Image;

error_reporting(E_ALL);

if (function_exists("imagecreatefromstring")) {
    //include_once "pChart/pChart.class";
    //include_once "pChart/pData.class";
    //include_once "pChart/pCache.class";

    $fontpath = ReporticoUtility::findBestLocationInIncludePath("pChart/fonts");
    define("PCHARTFONTS_DIR", $fontpath . "/");
} else {
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
class ChartPchart
{
    public $calling_mode = "INTERNAL";
    public $graph_column = "";
    public $title = "Set Title";
    public $title_actual = "Set Title";
    public $xtitle = "Set Title";
    public $ytitle = "Set Title";
    public $xtitle_actual = "Set Title";
    public $ytitle_actual = "Set Title";
    public $graphcolor = ".DEFAULT";
    public $width = ".DEFAULT";
    public $height = ".DEFAULT";
    public $width_actual = 400;
    public $height_actual = 200;
    public $width_pdf_actual = 400;
    public $height_pdf_actual = 200;
    public $xtickinterval_actual = 1;
    public $xticklabelinterval_actual = 1;
    public $xaxiscolor_actual = "black";
    public $ytickinterval_actual = 1;
    public $yticklabelinterval_actual = 1;
    public $yaxiscolor_actual = "black";
    public $graphcolor_actual = "white";
    public $margincolor_actual;
    public $marginleft_actual;
    public $marginright_actual;
    public $margintop_actual;
    public $marginbottom_actual;
    public $gridpos_actual = ".DEFAULT";
    public $xgriddisplay_actual = ".DEFAULT";
    public $xgridcolor_actual = ".DEFAULT";
    public $ygriddisplay_actual = ".DEFAULT";
    public $ygridcolor_actual = ".DEFAULT";
    public $titlefont_actual = "";
    public $titlefontstyle_actual = "";
    public $titlefontsize_actual = ".DEFAULT";
    public $titlecolor_actual = ".DEFAULT";
    public $xtitlefont_actual = "";
    public $xtitlefontstyle_actual = "";
    public $xtitlefontsize_actual = ".DEFAULT";
    public $xtitlecolor_actual = ".DEFAULT";
    public $ytitlefont_actual = "";
    public $ytitlefontstyle_actual = "";
    public $ytitlefontsize_actual = ".DEFAULT";
    public $ytitlecolor_actual = ".DEFAULT";
    public $xaxisfont_actual = "";
    public $xaxisfontstyle_actual = "";
    public $xaxisfontsize_actual = ".DEFAULT";
    public $xaxisfontcolor_actual = ".DEFAULT";
    public $yaxisfont_actual = "";
    public $yaxisfontstyle_actual = "";
    public $yaxisfontsize_actual = ".DEFAULT";
    public $yaxisfontcolor_actual = ".DEFAULT";

    public $width_pdf = ".DEFAULT";
    public $height_pdf = ".DEFAULT";
    public $titlefont = ".DEFAULT";
    public $titlefontstyle = ".DEFAULT";
    public $titlefontsize = ".DEFAULT";

    public $gridpos = ".DEFAULT";
    public $xgriddisplay = ".DEFAULT";
    public $xgridcolor = ".DEFAULT";
    public $ygriddisplay = ".DEFAULT";
    public $ygridcolor = ".DEFAULT";

    public $yaxiscolor = ".DEFAULT";
    public $yaxisfont = ".DEFAULT";
    public $yaxisfontstyle = ".DEFAULT";
    public $yaxisfontsize = ".DEFAULT";
    public $yaxisfontcolor = ".DEFAULT";
    public $xaxiscolor = ".DEFAULT";
    public $xaxisfont = ".DEFAULT";
    public $xaxisfontstyle = ".DEFAULT";
    public $xaxisfontsize = ".DEFAULT";
    public $xaxisfontcolor = ".DEFAULT";
    public $xtitlefont = ".DEFAULT";
    public $xtitlefontstyle = ".DEFAULT";
    public $xtitlefontsize = ".DEFAULT";
    public $xtitlecolor = ".DEFAULT";
    public $xtickinterval = ".DEFAULT";
    public $xticklabelinterval = ".DEFAULT";
    public $ytitlefont = ".DEFAULT";
    public $ytitlefontstyle = ".DEFAULT";
    public $ytitlefontsize = ".DEFAULT";
    public $ytitlecolor = ".DEFAULT";
    public $ytickinterval = ".DEFAULT";
    public $yticklabelinterval = ".DEFAULT";
    public $titlecolor = ".DEFAULT";
    public $margincolor = ".DEFAULT";
    public $marginleft = ".DEFAULT";
    public $marginright = ".DEFAULT";
    public $margintop = ".DEFAULT";
    public $marginbottom = ".DEFAULT";
    public $xlabel_column = "";
    public $ylabel_column = "";
    public $xlabels = array();
    public $ylabels = array();
    public $plot = array();
    public $reportico = false;

    public function __construct(&$reportico, $in_mode, $in_val = "")
    {
        $this->reportico = $reportico;
        $this->calling_mode = $in_mode;
        $this->graph_column = $in_val;
    }

    public function &createPlot($in_query)
    {
        $pl = array(
            "number" => count($this->plot),
            "name" => $in_query,
            "type" => "LINE",
            "fillcolor" => "",
            "linecolor" => "",
            "datatype" => "number",
            "legend" => "",
            "data" => array(),
        );
        $this->plot[] = &$pl;

        return ($pl);
    }

    public function clearData()
    {
        foreach ($this->plot as $k => $v) {
            $this->plot[$k]["data"] = array();
        }
        $this->xlabels = array();
        $this->ylabels = array();
    }

    public function addXlabel($in_val)
    {
        $in_val = @preg_replace("/&/", "+", $in_val);
        $this->xlabels[] = $in_val;
    }

    public function addPlotValue($in_query, $plot_no, $in_val)
    {
        $in_val = trim($in_val);
        if (!$in_val) {
            $in_val = 0;
        }

        foreach ($this->plot as $k => $v) {
            if ($v["name"] == $in_query && $v["number"] == $plot_no) {
                switch ($v["datatype"]) {
                    case "hhmmss":
                        $this->plot[$k]["data"][] = ReporticoUtility::hhmmssToSeconds($in_val);
                        break;

                    default:
                        $this->plot[$k]["data"][] = $in_val;
                }
            }
        }
    }

    public function convertSpecialChars($intext)
    {
        $outtext = preg_replace("/&/", "<AMPERSAND>", $intext);
        return $outtext;
    }

    public function generateUrlParams($target_format, $sessionPlaceholder = false)
    {
        $sessionClass = ReporticoSession();

        $this->applyDefaults();

        $result = "";
        $url = "";
        $url .= "title=" . $this->convertSpecialChars($this->title_actual);
        $url .= "&xtitle=" . $this->convertSpecialChars($this->xtitle_actual);
        $url .= "&ytitle=" . $this->convertSpecialChars($this->ytitle_actual);
        if ($target_format == "PDF") {
            $url .= "&width=" . $this->width_pdf_actual;
            $url .= "&height=" . $this->height_pdf_actual;
        } else {
            $url .= "&width=" . $this->width_actual;
            $url .= "&height=" . $this->height_actual;
        }
        $url .= "&graphcolor=" . $this->graphcolor_actual;
        $url .= "&gridposition=" . $this->gridpos_actual;
        $url .= "&xgriddisplay=" . $this->xgriddisplay_actual;
        $url .= "&xgridcolor=" . $this->xgridcolor_actual;
        $url .= "&ygriddisplay=" . $this->ygriddisplay_actual;
        $url .= "&ygridcolor=" . $this->ygridcolor_actual;
        $url .= "&titlefont=" . $this->titlefont_actual;
        $url .= "&titlefontstyle=" . $this->titlefontstyle_actual;
        $url .= "&titlefontsize=" . $this->titlefontsize_actual;
        $url .= "&titlecolor=" . $this->titlecolor_actual;
        $url .= "&xaxiscolor=" . $this->xaxiscolor_actual;
        $url .= "&xaxisfont=" . $this->xaxisfont_actual;
        $url .= "&xaxisfontstyle=" . $this->xaxisfontstyle_actual;
        $url .= "&xaxisfontsize=" . $this->xaxisfontsize_actual;
        $url .= "&xaxisfontcolor=" . $this->xaxisfontcolor_actual;
        $url .= "&yaxiscolor=" . $this->yaxiscolor_actual;
        $url .= "&yaxisfont=" . $this->yaxisfont_actual;
        $url .= "&yaxisfontstyle=" . $this->yaxisfontstyle_actual;
        $url .= "&yaxisfontsize=" . $this->yaxisfontsize_actual;
        $url .= "&yaxisfontcolor=" . $this->yaxisfontcolor_actual;
        $url .= "&xtitlefont=" . $this->xtitlefont_actual;
        $url .= "&xtitlefontstyle=" . $this->xtitlefontstyle_actual;
        $url .= "&xtitlefontsize=" . $this->xtitlefontsize_actual;
        $url .= "&xtitlecolor=" . $this->xtitlecolor_actual;
        $url .= "&xtickint=" . $this->xtickinterval_actual;
        $url .= "&xticklabint=" . $this->xticklabelinterval_actual;
        $url .= "&ytitlefont=" . $this->ytitlefont_actual;
        $url .= "&ytitlefontstyle=" . $this->ytitlefontstyle_actual;
        $url .= "&ytitlefontsize=" . $this->ytitlefontsize_actual;
        $url .= "&ytitlecolor=" . $this->ytitlecolor_actual;
        $url .= "&ytickint=" . $this->ytickinterval_actual;
        $url .= "&yticklabint=" . $this->yticklabelinterval_actual;
        $url .= "&margincolor=" . $this->margincolor_actual;
        $url .= "&marginleft=" . $this->marginleft_actual;
        $url .= "&marginright=" . $this->marginright_actual;
        $url .= "&margintop=" . $this->margintop_actual;
        $url .= "&marginbottom=" . $this->marginbottom_actual;
        $url .= "&xlabels=" . implode(",", $this->xlabels);

        foreach ($this->plot as $k => $v) {
            $str = implode(",", $v["data"]);
            $url .= "&plotname$k=" . $v["name"];
            $url .= "&plotdata$k=$str";
            $url .= "&plottype$k=" . $v["type"];
            $url .= "&plotlinecolor$k=" . $v["linecolor"];
            if ($v["legend"]) {
                $url .= "&plotlegend$k=" . $v["legend"];
            }

            if ($v["fillcolor"]) {
                $url .= "&plotfillcolor$k=" . $v["fillcolor"];
            }

        }

        if ($sessionPlaceholder) {
            $ses = "graph_" . $sessionPlaceholder;
            $sessionClass::setReporticoSessionParam($ses, $url);
            $url = "graphid=" . $ses . "&time=" . time();
        }

        // Select the appropriate reporting engine
        $dyngraph = "dyngraph.php";
        if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig("graph_engine") == "PCHART") {
            $dyngraph = "dyngraph_pchart.php";
        }

        $dr = ReporticoUtility::getReporticoUrlPath();
        $dyngraph = $dr . "/" . ReporticoUtility::findBestUrlInIncludePath($dyngraph);

        if ($this->reportico->framework_parent) {
            $dyngraph = "";
            if ($this->reportico->reportico_ajax_mode == "2") {
                $dyngraph = preg_replace("/ajax/", "graph", $this->reportico->reportico_ajax_script_url);
            }

        }
        $forward_url_params = $sessionClass::sessionRequestItem('forward_url_get_parameters_graph');
        if (!$forward_url_params) {
            $forward_url_params = $sessionClass::sessionRequestItem('forward_url_get_parameters', $this->reportico->forward_url_get_parameters);
        }

        if ($forward_url_params) {
            $url .= "&" . $forward_url_params;
        }

        $url .= "&reportico_call_mode=graph_pchart";
        $url .= "&reportico_session_name=" . $sessionClass::reporticoSessionName();
        $result = '<img class="reportico-output-graph" src=\'' . $dyngraph . '?' . $url . '\'>';

        return $result;
    }

    public function setGraphColor($in_col)
    {
        $this->graphcolor = $in_col;
    }
    public function setGraphColumn($in_val)
    {
        $this->graph_column = $in_val;
    }
    public function setTitle($in_title)
    {
        $this->title = $in_title;
    }
    public function setXtitle($in_xtitle)
    {
        $this->xtitle = $in_xtitle;
    }
    public function setYtitle($in_ytitle)
    {
        $this->ytitle = $in_ytitle;
    }
    public function setWidthPdf($in_width)
    {
        $this->width_pdf = $in_width;
    }
    public function setHeightPdf($in_height)
    {
        $this->height_pdf = $in_height;
    }
    public function setWidth($in_width)
    {
        $this->width = $in_width;
    }
    public function setHeight($in_height)
    {
        $this->height = $in_height;
    }
    public function setGrid($in_pos, $in_xdisplay, $in_xcolor, $in_ydisplay, $in_ycolor)
    {
        $this->gridpos = $in_pos;
        $this->xgriddisplay = $in_xdisplay;
        $this->xgridcolor = $in_xcolor;
        $this->ygriddisplay = $in_ydisplay;
        $this->ygridcolor = $in_ycolor;
    }
    public function setXlabelColumn($in_xlabel_column)
    {
        $this->xlabel_column = $in_xlabel_column;
    }
    public function setYlabelColumn($in_ylabel_column)
    {
        $this->ylabel_column = $in_ylabel_column;
    }

    public function setTitleFont($in_font, $in_style, $in_size, $in_col)
    {
        $this->titlefont = $in_font;
        $this->titlefontstyle = $in_style;
        $this->titlefontsize = $in_size;
        $this->titlecolor = $in_col;
    }

    public function setXtitleFont($in_font, $in_style, $in_size, $in_col)
    {
        $this->xtitlefont = $in_font;
        $this->xtitlefontstyle = $in_style;
        $this->xtitlefontsize = $in_size;
        $this->xtitlecolor = $in_col;
    }

    public function setYtitleFont($in_font, $in_style, $in_size, $in_col)
    {
        $this->ytitlefont = $in_font;
        $this->ytitlefontstyle = $in_style;
        $this->ytitlefontsize = $in_size;
        $this->ytitlecolor = $in_col;
    }

    public function setXaxisFont($in_font, $in_style, $in_size, $in_col)
    {
        $this->xaxisfont = $in_font;
        $this->xaxisfontstyle = $in_style;
        $this->xaxisfontsize = $in_size;
        $this->xaxisfontcolor = $in_col;
    }

    public function setYaxisFont($in_font, $in_style, $in_size, $in_col)
    {
        $this->yaxisfont = $in_font;
        $this->yaxisfontstyle = $in_style;
        $this->yaxisfontsize = $in_size;
        $this->yaxisfontcolor = $in_col;
    }

    public function setXaxis($in_tic, $in_lab_tic, $in_col)
    {
        $this->xtickinterval = $in_tic;
        $this->xticklabelinterval = $in_lab_tic;
        $this->xaxiscolor = $in_col;
    }

    public function setYaxis($in_tic, $in_lab_tic, $in_col)
    {
        $this->ytickinterval = $in_tic;
        $this->yticklabelinterval = $in_lab_tic;
        $this->yaxiscolor = $in_col;

    }

    public function setMarginColor($in_col)
    {
        $this->margincolor = $in_col;
    }

    public function setMargins($in_lt, $in_rt, $in_tp, $in_bt)
    {
        $this->marginleft = $in_lt;
        $this->marginright = $in_rt;
        $this->margintop = $in_tp;
        $this->marginbottom = $in_bt;
    }

    public function applyDefaults()
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

    public function applyDefaultsInternal()
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

    public function generateGraphImage($outputfile)
    {
        // Create Graph Dataset and set axis attributes
        $data = new Data();
        $data->addPoints($this->xlabels, "xaxis");
        $data->setSerieDescription("xaxis", $this->xtitle);
        $data->setAbscissa("xaxis");


        $data = new Data();

        // duplicate series were the same data are displayed in different forms
        // so only add each unique series once
        $seriesadded = array();
        foreach ($this->plot as $k => $v) {
            $series = $v["name"] . $k;
            $data->addPoints($v["data"], $series);
            $data->setSerieDescription($series, $v["legend"]);
        }

        //$data->addPoints([-7, -8, -15, -20, -18, -12, 8, -19, 9, 16, -20, 8, 10, -10, -14, -20, 8, -9, -19], "Probe 3");
        //$data->addPoints([19, 0, -8, 8, -8, 12, -19, -10, 5, 12, -20, -8, 10, -11, -12, 8, -17, -14, 0], "Probe 4");

        $data->setAxisName(0, $this->ytitle);
        $data->addPoints($this->xlabels, "xaxis");
        $data->setAbscissa("xaxis");

        $this->applyDefaultsInternal();

        // Create the chart image
        $image = new Image($this->width_pdf_actual, $this->height_pdf_actual, $data);

        // Set the font to Verdana (available fonts are ..
        // advent_light.ttf  Bedizen.ttf  calibri.ttf  Forgotte.ttf  GeosansLight.ttf  
        // MankSans.ttf  pf_arma_five.ttf  Silkscreen.ttf  verdana.ttf
        $image->setFontProperties(["FontName" => "verdana.ttf", "FontSize" => 8]);


        /// Plot the chart data
        $stackeddrawn = false;
        $linedrawn = false;
        $scatterdrawn = false;
        $piedrawn = false;
        $stackedexists = false;
        $overlayexists = false;
        $barexists = false;

        foreach ($this->plot as $k => $v) {

            if ($v["type"] == "OVERLAYBAR") {
                $overlayexists = true;
            }

            if ($v["type"] == "STACKEDBAR") {
                $stackedexists = true;
            }

            if ($v["type"] == "STACKEDBAR" || $v["type"] == "BAR") {
                $barexists = true;
            }

            // Set plot colors
            if ($v["linecolor"]) {
                $image->Palette[$k] = ReporticoUtility::htmltorgbPchart($v["linecolor"]);
            }

            $url .= "&plotlinecolor$k=" . $v["linecolor"];
        }

        $scale_drawing_mode = SCALE_MODE_ADDALL_START0;

        // For stacked charts fix up the Max and Min values;
        if ($stackedexists) {
            $scale_drawing_mode = SCALE_ADDALL;
            $scale_drawing_mode = SCALE_ADDALL_START0;
            $scaleMin = "Unknown";
            $scaleMax = 0;
            $min = false;
            $max = false;
            foreach ($plot as $k => $v) {
                if ($v["type"] == "BAR" || $v["type"] == "STACKEDBAR") {
                    $series = $v["name"] . $k;
                    minmaxValueOfSeries($v["data"], $min, $max);
                    if ($scaleMin == "Unknown" || $min < $scaleMin) {
                        $scaleMin = $min;
                    }

                    $scaleMax = $scaleMax + $max;
                }
            }
            if ($scaleMin > 0) {
                $scaleMin = 0;
            }

            $range = $scaleMax - $scaleMin;
            // Make scales based on 5% of the range of values
            $scaleMax = round(($range * 0.05) + $scaleMax);
            if ($scaleMin < 0) {
                $scaleMin = $scaleMin - round(($range * 0.05));
            }

            $AxisBoundaries = array(0 => array("Min" => $scaleMin, "Max" => $scaleMax));
        } else if ($barexists || $overlayexists) {
            $scaleMin = "Unknown";
            $scaleMax = 0;
            $min = false;
            $max = false;
            foreach ($this->plot as $k => $v) {
                if ($v["type"] == "BAR" || $v["type"] == "STACKEDBAR") {
                    $series = $v["name"] . $k;
                    minmaxValueOfSeries($v["data"], $min, $max);
                    if ($scaleMin == "Unknown" || $min < $scaleMin) {
                        $scaleMin = $min;
                    }

                    if ($scaleMax == "Unknown" || $max > $scaleMax) {
                        $scaleMax = $max;
                    }

                }
            }
            if ($scaleMin > 0) {
                $scaleMin = 0;
            }

            $range = $scaleMax - $scaleMin;
            // Make scales based on 5% of the range of values
            $scaleMax = round(($range * 0.05) + $scaleMax);
            if ($scaleMin < 0) {
                $scaleMin = $scaleMin - round(($range * 0.05));
            }

            $AxisBoundaries = array(0 => array("Min" => $scaleMin, "Max" => $scaleMax));
        }

        $scalerequired = false;
        foreach ($this->plot as $k => $v) {
            switch ($v["type"]) {
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

        $image->setFontProperties(PCHARTFONTS_DIR . $this->xtitlefont, $this->xtitlefontsize);

        if ($scalerequired) {
            $image->setGraphArea($this->marginleft_actual, $this->margintop_actual, $this->width_pdf_actual - $this->marginright, $this->height_pdf_actual - $this->marginbottom_actual);

            // Automatic generation of x tick interval based on number of ticks
            if ($this->xticklabelinterval_actual == "AUTO") {
                $labct = count($this->plot[0]["data"]);
                $this->xticklabelinterval_actual = floor($labct / 35) + 1;
            }

            if ($scalerequired == "NORMAL") {

                $settings = [
                    //"CycleBackground" => true,
                    "DrawSubTicks" => true,
                    "GridR" => 255,
                    "GridG" => 255,
                    "GridB" => 255,
                    //"GridAlpha" => 10,
                    "LabelRotation" => "40",
                    //"Mode" => $scale_drawing_mode
                    "Mode" => SCALE_MODE_ADDALL_START0
                ];

                $image->drawScale($settings);
            }
        } else {
            $this->marginright = 5;
            $this->marginbottom = 5;
            $this->marginleft = 5;
            $this->marginright_actual = 5;
            $this->marginbottom_actual = 5;
            $this->marginleft_actual = 5;
            $image->setGraphArea($this->marginleft, $this->margintop_actual, $this->width_pdf_actual - $this->marginright, $this->height_pdf_actual - $this->marginbottom);
            $image->drawGraphAreaGradient(240, 240, 240, -10);

        }

        // If there's a Pie chart we want to draw different legends
        $piechart = false;

        foreach ($this->plot as $k => $v) {

            // Pass through each plot, and hide all other plots from drawing
            // so we can plot each series individuallay
            disableAllSeries($this->plot, $data);
            setSerieDrawable($this->plot, $data, $v["name"] . $k, true);
            $series = $v["name"] . $k;

            switch ($v["type"]) {
                case "PIE":
                    $piedrawn = true;
                    $piechart = true;
                    $image->drawFilledCircle(($this->width_pdf_actual / 2) + 2, $this->margintop_actual + 2 + (($this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) / 2),
                        ($this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20) * 0.45 + 1,
                        200, 200, 200);
                    $image->drawBasicPieGraph($data->GetData(), $data->GetDataDescription(),
                        $this->width_pdf_actual / 2,
                        $this->margintop_actual + (($this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) / 2),
                        ($this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20) * 0.45,
                        PIE_PERCENTAGE_LABEL,
                        255, 255, 218);
                    break;

                case "PIE3D":
                    $piedrawn = true;
                    $piechart = true;
                    $image->drawPieGraph($data->GetData(), $data->GetDataDescription(),
                        $this->width_pdf_actual / 2,
                        $this->margintop_actual + (($this->height_pdf_actual - $this->margintop_actual - $this->marginbottom_actual) / 2),
                        ($this->height_pdf_actual - $this->marginbottom_actual - $this->margintop_actual - 20) * 0.50,
                        PIE_PERCENTAGE_LABEL,
                        true,     // enhance colors
                        60,     // skew
                        20,     // splice height
                        0,     // splice distance
                        0    // decimals
                    );
                    break;
                case "OVERLAYBAR":
                case "STACKEDBAR":
                case "BAR":
                    if ($stackeddrawn) {
                        break;
                    }

                    $stackeddrawn = true;
                    $settings = [
                        //"Rounded" => true,
                        "DisplayValues" => false,
                        "DisplayColor" => DISPLAY_AUTO,
                        "DisplaySize" => 6,
                        "BorderR" => 255,
                        "BorderG" => 255,
                        "BorderB" => 255
                        ];

                    if ($stackedexists) {
                        $image->drawStackedBarChart($settings);
                    } else if ($overlayexists) {
                        $image->drawOverlayBarChart($settings);
                    } else {
                        $image->drawBarChart($settings);
                    }

                    break;
                case "SCATTER":
                    if ($scatterdrawn) {
                        break;
                    }

                    $scatterdrawn = true;
                    $series1 = false;
                    $series2 = false;
                    $image->reportWarnings("GD");
                    $ct = 0;
                    foreach ($this->plot as $k1 => $v1) {
                        if ($v1["type"] == "SCATTER") {
                            if ($ct == 0) {
                                $series1 = $v1["name"] . $k1;
                            }

                            if ($ct == 1) {
                                $series2 = $v1["name"] . $k1;
                            }

                            $ct++;
                            setSerieDrawable($this->plot, $data, $v1["name"] . $k1, true);
                        }
                    }
                    if (count($v["data"]) == 1) {
                        $v["data"][] = 0;
                    }

                    $image->drawXYScale($data->GetData(), $data->GetDataDescription(), $series1, $series2, 0, 0, 0);
                    $image->drawXYPlotChart($data->GetData(), $data->GetDataDescription(), $series1, $series2);
                    $image->writeValues($data->GetData(), $data->GetDataDescription(), $series2);
                    break;
                case "LINE":
                default;
                    if ($linedrawn) {
                        break;
                    }

                    $linedrawn = true;
                    if (count($v["data"]) == 1) {
                        $v["data"][] = 0;
                    }

                    $image->LineWidth = 1;
                    $image->drawLineChart(["DisplayValues" => false, "DisplayColor" => DISPLAY_AUTO]);
                    $image->LineWidth = 1;
                    break;
            }
        }
        setSerieDrawable($this->plot, $data, "__ALL__", true);

        $image->setShadow(true, ["X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10]);

        // Draw Legend if legend value has been set
        $drawlegend = false;
        foreach ($this->plot as $k => $v) {
            if (isset($v["legend"]) && $v["legend"]) {
                if (!$piechart) {
                    $image->drawLegend($this->width_pdf_actual - 180, 30, ["Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL]);
                }

                break;
            }
        }

        $image->render($outputfile);
        return true;
    }
}

function minmaxValueOfSeries($data, &$min, &$max)
{
    $min = "undef";
    $max = "undef";

    foreach ($data as $v) {
        if ($min == "undef" || $v < $min) {
            $min = $v;
        }

        if ($max == "undef" || $v > $max) {
            $max = $v;
        }

    }
}

function disableAllSeries($plot, &$data)
{
    foreach ($plot as $k => $v) {
        $series = $v["name"] . $k;
        //$data->RemoveSerie($series);
        $data->setSerieDrawable($series, false);
    }
}

function setSerieDrawable($plot, $data, $inseries, $flag)
{
    foreach ($plot as $k => $v) {
        $series = $v["name"] . $k;
        if ($inseries == $series || $inseries == "__ALL__" ) {
            if ($flag) {
                //$data->AddSerie($series);
                //$data->addPoints($v["data"], $series);
                //$data->SetSerieName($v["legend"], $series);
                $data->setSerieDrawable($series, true);
            } else {
                //$data->RemoveSerie($series);
                $data->setSerieDrawable($series, false);
            }

        }
    }
}
