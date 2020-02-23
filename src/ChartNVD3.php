<?php
/*

 * File:        swgraph_nvd3reportico.php
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
 * @version $Id: swgraph_nvd3reportico.php,v 1.3 2014/05/17 15:12:31 peter Exp $
 */

namespace Reportico\Engine;

error_reporting(E_ALL);

if (function_exists("imagecreatefromstring")) {
    //include("pChart/class/pData.class.php");
    //include("pChart/class/pDraw.class.php");
    //include("pChart/class/pImage.class.php");
    //include("pChart/class/pPie.class.php");
    //include "pChart/pChart.class";
    //include "pChart/pData.class";
    //include "pChart/pCache.class";

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
class ChartNVD3
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
        $in_val = str_replace(",", "", $in_val);
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
        $this->applyDefaults($target_format);
        $result = "";
        $url = "";
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

        $sessionPlaceholder = $sessionPlaceholder . "_" . ReporticoApp::get("session_namespace");

        $container_width = "100%";
        //$container_height = "100%";
        if ( $target_format == "HTML2PDF" ) {
            $container_width = $this->width_actual . "px";
            //$container_height = $this->height_actual . "px";
        }
        $container_height = $this->height_actual . "px";

        $js = "";
        $js .= "<div class=\"reportico-chart-container\" style=\"width:$container_width;height:$container_height;\"> <span class=\"reportico-chart-title\">" . $this->convertSpecialChars($this->title_actual)."</span>";
        //$js .= "<div class=\"reportico-chart-container\" style=\"width: " . $this->width_actual . "px;height: " . $this->height_actual . "px\"> " . $this->convertSpecialChars($this->title_actual);
        //$js .= "<div id=\"reportico_chart$sessionPlaceholder\" class=\"reportico-chart-placeholder\"></div> </div>\n";

        //$js .= "<div class=\"reportico-chart-placeholder\" id=\"reportico_chart$sessionPlaceholder\" style=\"overflow-y: none; width: $container_width; height:$container_height;height:100%;\"></svg></div></div>";
        $js .= "<div class=\"reportico-chart-placeholder\" id=\"reportico_chart$sessionPlaceholder\" style=\"overflow-y: none; width: 100%; height:100%\"><svg style=\"width:100%;height:100%;\"></svg></div></div>";
        $js .= "<script>\n";
        $js .= "var placeholder = 'reportico_chart$sessionPlaceholder';\n";
        $plotct = 0;
        $labels = "";
        foreach ($this->plot as $k => $v) {
            if ($v["legend"]) {
                continue;
            }

            if ($plotct > 0) {
                $labels .= ",";
            }

            $labels .= "\"111" . $v["legend"] . "\"";
            $plotct++;
        }
        $js .= "var xlabels = [$labels];\n";
        $js .= "var reportico_datasets" . $sessionPlaceholder . " = [];\n";

        $plotct = 0;
        $chartType = "MULTICHART";

        // Work out what combinations o type we have
        $has_plot_types = array();
        $showLegend = false;
        $datasetct = 0;
        for ($k = 0; $k < count($this->plot); $k++) {
            $v = $this->plot[$k];
            //if ( $k == 0 ) $v["type"] = "SCATTER";
            //if ( $k == 1 ) $v["type"] = "SCATTER";
            //if ( $k == 2 ) $v["type"] = "SCATTER";

            $label = "";
            if ($v["legend"]) {
                $showLegend = true;
                $label = $v["legend"];
            }

            $js .= "values = [";

            $plotct1 = 0;
            foreach ($v["data"] as $k1 => $v1) {
                if ($plotct1 > 0) {
                    $js .= ",";
                }


                $xlabel = $this->xlabels[$k1];
                $key = $k1;
                if ($v["type"] == "SCATTER" && $k < count($this->plot) - 1) {
                    $yvalue = $this->plot[$k + 1]["data"][$k1];
                    $js .= "{index: $k1, series: 0, x: $v1, y: $yvalue, label: \"$xlabel\", value: $v1}";
                } else {
                    $yval = is_numeric($v1) ? $v1 : 0;
                    $js .= "{index: $k1, series: 0, x: $key, y: $yval, label: \"$xlabel\", value: $yval}";
                }

                $plotct1++;
            }
            $js .= "];\n";

            if ($v["type"] == "OVERLAYBAR") {
                $type = "bar";
            }

            if ($v["type"] == "STACKEDBAR") {
                $type = "bar";
            }

            if ($v["type"] == "BAR") {
                $type = "bar";
            }

            if ($v["type"] == "LINE") {
                $type = "line";
            }

            if ($v["type"] == "AREACHART") {
                $type = "area";
            }

            if ($v["type"] == "SCATTER") {
                $type = "scatter";
            }

            if ($v["type"] == "PIE") {
                $type = "pie";
            }

            if ($v["type"] == "PIE3D") {
                $type = "pie";
            }

            $has_plot_types[$v["type"]] = true;
            //$type = "bar";
            //echo $v["type"]." = $type <BR>";

            $js .= "reportico_datasets" . $sessionPlaceholder . "[$datasetct] = { type: \"$type\", yAxis: 1, key: \"$label\", originalKey: \"$label\", values: values};\n";
            //$js .= "reportico_datasets". $sessionPlaceholder."[$k][\"type\"] = \"line\";\n";
            $plotct++;

            $datasetct++;
            if ($v["type"] == "SCATTER") {
                $k++;
                continue;
            }
        }

        // NVD3 - we dont support overlay bar, just use regular bar
        if (isset($has_plot_types["OVERLAYBAR"])) {
            unset($has_plot_types["OVERLAYBAR"]);
            $has_plot_types["BAR"] = true;
        }

        // With any plot specified as stacked, we will stack them all as specified in stacked variable
        $stacked = false;
        if (isset($has_plot_types["STACKEDBAR"])) {
            $stacked = true;
            unset($has_plot_types["STACKEDBAR"]);
            $has_plot_types["BAR"] = true;
        }

        $chartType = "MULTICHART";
        // Decide what chart type to use. NVD3 cant mix stacked bars and lines in multichart, so if we have stacked bars only use multibar,
        // lines only use multiline otherwise use multichart. Unless of course we have PIE chart specified in which case use PIE.
        //
        if (isset($has_plot_types["PIE"]) || isset($has_plot_types["PIE3D"])) {
            $chartType = "PIE";
        } else
        if (isset($has_plot_types["SCATTER"])) {
            $chartType = "SCATTER";
        } else {
            // Chart contains only one type ( just lines, just areas, just bars so use relevant
            // but use multiBar chart if more than one
            if (count($has_plot_types) == 1) {
                foreach ($has_plot_types as $k => $v) {
                    if ($plotct > 1) {
                        $chartType = "MULTI" . $k;
                    } else {
                        $chartType = $k;
                    }

                }
            }
        }

        $js .= "
    function reporticoChart_$sessionPlaceholder()
    {
        var colorrange = d3reportico.scale.category10().range();
        ";

        if ( !$this->plot ) {
            return;
        }
        $labct = count($this->plot[0]["data"]);
        if ($this->xticklabelinterval_actual) {
            if ($this->xticklabelinterval_actual == "AUTO") {
                $this->xticklabelinterval_actual = floor($labct / 40) + 1;
            }
            $js .= "labelInterval = $this->xticklabelinterval_actual;";
        } else {
            $js .= "labelInterval = false;";
        }

        $js .= "rotateLabels = -30;";

        if ( $this->xticklabelinterval_actual > 0 )
            $labct = ($labct / $this->xticklabelinterval_actual) + 1;
        $labct = floor($labct);

        $js .= "labelCount = $labct;";

        if ($stacked) {
            $js .= "stacked = true;";
        } else {
            $js .= "stacked = false;";
        }

        if (count($this->plot) > 1) {
            $js .= "showControls = true;";
        } else {
            $js .= "showControls = false;";
        }

        if ($showLegend) {
            $js .= "showLegend = true;";
        } else {
            $js .= "showLegend = false;";
        }

        $plotct = 0;
        foreach ($this->plot as $k => $v) {
            if ($v["linecolor"]) {
                $js .= "colorrange[$k] = '" . $v["linecolor"] . "';\n";
            }

            $plotct++;
        }

        if ($chartType == "PIE") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.pieChart()
            //.margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .margin({top: 2, right: 0, bottom: 8, left: 0})
            .color(colorrange)
                .showLabels(!showLegend)
                .showLegend(showLegend)
                //.labelType(\"value\")
                .donut(false)
                .x(function (d)
                {
                    return d.label;
                })
                .y(function (d)
                {
                    return d.value;
                })
                ;


            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . "[0].values)
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            //d3reportico.selectAll(\"nv-legend\")
                //.style(\"display\", function (d, i) { //d is the data bound to the svg element
                    //return \"none\" ;
                //});
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else
        if ($chartType == "DISCRETEBAR") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.discreteBarChart()
            .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .color(colorrange)
            ;

            chart" . $sessionPlaceholder . ".xAxis
                .axisLabel('" . $this->xtitle . "')
                .rotateLabels (rotateLabels)
                .showMaxMin(false)
                //.staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
                .tickFormat(function (d, i, j)
                {
                    if ( reportico_datasets" . $sessionPlaceholder . "[0].values[d] )
                    {
                        return reportico_datasets" . $sessionPlaceholder . "[0].values[d].label;
                    }
                    else
                    {
                        return i;
                    }
                })
                ;


            chart" . $sessionPlaceholder . ".yAxis
                .axisLabel('" . $this->ytitle . "')
                .tickFormat(d3reportico.format(',.1f'));

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else
        if ($chartType == "SCATTER") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.scatterChart()
                .showDistX(false)    //showDist, when true, will display those little distribution lines on the axis.
                .showDistY(false)
                .transitionDuration(350)
                .color(d3reportico.scale.category10().range())
                .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
                .color(colorrange)
                ;


            //Configure how the tooltip looks.
            chart" . $sessionPlaceholder . ".tooltipContent(ReporticoLang::function(key) {
                return '<h3>' + key + '</h3>';
            });

            //Axis settings
            chart" . $sessionPlaceholder . ".xAxis.tickFormat(d3reportico.format('.02f'));
            chart" . $sessionPlaceholder . ".yAxis.tickFormat(d3reportico.format('.02f'));

            //We want to show shapes other than circles.
            chart" . $sessionPlaceholder . ".scatter.onlyCircles(false);

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else
        if ($chartType == "BAR" || $chartType == "MULTIBAR") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.multiBarChart()
            .reduceXTicks (labelInterval)
            .labelCount(labelCount)
            .stacked(stacked)
            .showControls(showControls)
            .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .color(colorrange)
            ;

            chart" . $sessionPlaceholder . ".xAxis
                .axisLabel('" . $this->xtitle . "')
                .rotateLabels (rotateLabels)
                .showMaxMin(false)
                //.staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
                .tickFormat(function (d, i, j)
                {
                    if ( reportico_datasets" . $sessionPlaceholder . "[0].values[d] )
                    {
                        return reportico_datasets" . $sessionPlaceholder . "[0].values[d].label;
                    }
                    else
                    {
                        return i;
                    }
                })
                ;


            chart" . $sessionPlaceholder . ".yAxis
                .axisLabel('" . $this->ytitle . "')
                .tickFormat(d3reportico.format(',.1f'));

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else
        if ($chartType == "AREACHART" || $chartType == "MULTIAREACHART") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.stackedAreaChart()
            .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .labelCount(labelCount)
            .color(colorrange)
            ;

            chart" . $sessionPlaceholder . ".xAxis
                .axisLabel('" . $this->xtitle . "')
                .rotateLabels (rotateLabels)
                .showMaxMin(false)
                //.staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
                .tickFormat(function (d, i, j)
                {
                    if ( reportico_datasets" . $sessionPlaceholder . "[0].values[d] )
                    {
                        return reportico_datasets" . $sessionPlaceholder . "[0].values[d].label;
                    }
                    else
                    {
                        return i;
                    }
                })
                ;


            chart" . $sessionPlaceholder . ".yAxis
                .axisLabel('" . $this->ytitle . "')
                .tickFormat(d3reportico.format(',.1f'));

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else
        if ($chartType == "LINE" || $chartType == "MULTILINE") {
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.lineChart()
            .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .tickInterval($this->xticklabelinterval_actual)
            .labelCount(labelCount)
            .color(colorrange)
            ;

            chart" . $sessionPlaceholder . ".xAxis
                .axisLabel('" . $this->xtitle . "')
                .rotateLabels (rotateLabels)
                .showMaxMin(false)
                //.staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
                .tickFormat(function (d, i, j)
                {
                    if ( reportico_datasets" . $sessionPlaceholder . "[0].values[d] )
                    {
                        return reportico_datasets" . $sessionPlaceholder . "[0].values[d].label;
                    }
                    else
                    {
                        return i;
                    }
                })
                ;


            chart" . $sessionPlaceholder . ".yAxis
                .axisLabel('" . $this->ytitle . "')
                .tickFormat(d3reportico.format(',.1f'));

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        } else {

            $chart_dimensions = "";
            if ( $target_format == "HTML2PDF" ) 
                $chart_dimensions = ".width({$this->width_actual}).height({$this->height_actual});";

            
            $js .= "
            var chart" . $sessionPlaceholder . " = nv.models.multiChart()
            .margin({top: " . $this->margintop_actual . ", right: " . $this->marginright_actual . ", bottom: " . $this->marginbottom_actual . ", left: " . $this->marginleft_actual . " + 10})
            .labelCount(labelCount)
            .color(colorrange)
            $chart_dimensions
            ;

            chart" . $sessionPlaceholder . ".xAxis
                .axisLabel('" . $this->xtitle . "')
                .rotateLabels (rotateLabels)
                .showMaxMin(false)
                //.staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
                .tickFormat(function (d, i, j)
                {
                    if ( reportico_datasets" . $sessionPlaceholder . "[0].values[d] )
                    {
                        return reportico_datasets" . $sessionPlaceholder . "[0].values[d].label;
                    }
                    else
                    {
                        return i;
                    }
                })
                ;


            chart" . $sessionPlaceholder . ".yAxis1
                .axisLabel('" . $this->ytitle . "')
                .tickFormat(d3reportico.format(',.1f'));

            d3reportico.select(\"#reportico_chart" . $sessionPlaceholder . " svg\")
            .datum(reportico_datasets" . $sessionPlaceholder . ")
            .transition().duration(0)
            .call(chart" . $sessionPlaceholder . ");

            d3reportico.selectAll(\"rect.nv-bar\")
                .style(\"fill-opacity\", function (d, i) { //d is the data bound to the svg element
                    return .5 ;
                })

            d3reportico.selectAll(\".tick line\")
                .style(\"opacity\", function (d, i) { //d is the data bound to the svg element
                    return .2 ;
                })


            nv.utils.windowResize(chart" . $sessionPlaceholder . ".update);
            ";

            if ($this->xgriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".x * * .tick line\").css(\"display\", \"none\"); ";
            }

            if ($this->ygriddisplay_actual == "none") {
                $js .= " reportico_jquery(\".y1 * * .tick line\").css(\"display\", \"none\"); ";
            }

        }

        $js .= "
        }
            nv.addGraph(reporticoChart_$sessionPlaceholder(this));

";
        $js .= "</script>\n";

        $result = $js;

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

    public function applyDefaults($target_format)
    {
        $this->width_actual = ReporticoApp::getDefaultConfig("GraphWidth", $this->width);
        $this->height_actual = ReporticoApp::getDefaultConfig("GraphHeight", $this->height);
        $this->width_pdf_actual = ReporticoApp::getDefaultConfig("GraphWidthPDF", $this->width_pdf);
        $this->height_pdf_actual = ReporticoApp::getDefaultConfig("GraphHeightPDF", $this->height_pdf);
        if ( $target_format == "HTML2PDF" ) {
            $this->width_actual = $this->width_pdf_actual;
            $this->height_actual = $this->height_pdf_actual;
        }

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
        return false;
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

function disableAllSeries($plot, &$graphData)
{
    foreach ($plot as $k => $v) {
        $series = $v["name"] . $k;
        $graphData->RemoveSerie($series);
    }
}

function setSerieDrawable($plot, $graphData, $inseries, $flag)
{
    foreach ($plot as $k => $v) {
        $series = $v["name"] . $k;
        if ($inseries == $series) {
            if ($flag) {
                $graphData->AddSerie($series);
                $graphData->SetSerieName($v["legend"], $series);
            } else {
                $graphData->RemoveSerie($series);
            }

        }
    }
}
