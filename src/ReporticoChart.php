<?php

namespace Reportico\Engine;

/**
 * Class ReporticoChart
 *
 * Identifies a report output chart and the associated
 * chart  header and footers.
 */
class ReporticoChart extends ReporticoObject
{
    public $renderer = "";
    public $currentPlot = false;

    public $usage = array(
        "summary" => "Generate charts inside your output",
        "description" => "The chart method() allows you to place a chart visualising the data for the report or for each group section. You are able to set the chart tiele, axes titles, legend title.",
        "methods" => array(
            "chart" => array(
                "description" => "Start a chart",
                "parameters" => array( "groupcolumn" => "If specified the group column after which to add a chart")
            ),
            "title" => array(
                "description" => "Chart Title",
                "parameters" => array( "title" => "Title Of Chart")
            ),
            "plot" => array(
                "description" => "Specify a column value to plot in Y-Axis",
                "parameters" => array( "column" => "Column to Plot")
            ),
            "plotType" => array( "description" => "How present the plot ( e.g. Bar Chart, Line Chart, Pie Chart )",
                "parameters" => array( "column" => [ "description" => "Type of plot",
                    "options" => [
                        "bar" => "Bar Chart",
                        "pie" => "Pie Chart",
                        "line" => "Line Chart",
                        "stackedbar" => "Stacked Bar"]
                ]
                )
            ),
            "legend" => array(
                "description" => "Text description of plot to show in the legend",
                "parameters" => array( "legend" => "Text label of the plot column to place in the legend box")
            ),
            "xlabels" => array(
                "parameters" => array( "column" => "Name of column containing the labels for the X axis")
            ),
            "xtitle" => array(
                "parameters" => array( "title" => "X Axis Title")
            ),
            "ytitle" => array(
                "parameters" => array( "title" => "Y Axis Title")
            ),
        )
    );

    public function __construct()
    {
        ReporticoObject::__construct();
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public static function __callStatic($method, $args)
    {
        switch ( $method ) {

            case "build":
                $builder = $args[0];
                if (isset($args[1]))  {
                    $chart = new \Reportico\Engine\ReporticoChart();
                    $chart->renderer = $builder->engine->createGraph();
                    $chart->renderer->setGraphColumn($args[1]);
                } else {
                    $builder->engine->createGroup("REPORT_BODY", $builder->engine);
                    $chart = new \Reportico\Engine\ReporticoChart();
                    $chart->renderer = $builder->engine->createGraph();
                    $chart->renderer->setGraphColumn("REPORT_BODY");
                }
                $chart->builder = $builder;
                $builder->stepInto("chart", $chart, "\Reportico\Engine\ReportChart");
                return $builder;
                break;

        }
    }

    /*
     * Magic method to set Reportico instance properties and call methods through
     * scaffolding calls
     */
    public function __call($method, $args)
    {
        $exitLevel = false;

        if (!$this->builderMethodValid("chart", $method, $args)) {
            echo "invalid $method";
            return false;
        }

        switch ( strtolower($method) ) {

            case "usage":
                echo $this->builderUsage("chart");
                break;

            case "title":
                $this->builder->value->renderer->setTitle($args[0]);
                break;

            case "plot":
                $this->builder->value->currentPlot =
                &$this->builder->value->renderer->createPlot($args[0]);
                break;

            case "plottype":
                $this->builder->value->currentPlot["type"] = strtoupper($args[0]);
                break;

            case "legend":
                $this->builder->value->currentPlot["legend"] = $args[0];
                break;

            case "xlabels":
                $this->builder->value->renderer->setXlabelColumn($args[0]);
                break;

            case "xtitle":
                $this->builder->value->renderer->setXTitle($args[0]);
                break;

            case "ytitle":
                $arg = $args[0];
                $this->builder->value->renderer->setYTitle($args[0]);
                break;

            case "end":
            default:
                $this->levelRef = false;
                $exitLevel = true;
                break;
        }

        if (!$exitLevel)
            return $this;

        return false;

    }


}
