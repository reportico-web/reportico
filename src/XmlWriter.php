<?php

namespace Reportico\Engine;

/**
 * Class XmlWriter
 *
 * Responsible for converting the current report back into XML format
 * and for saving that report XML back to disk.
 */
class XmlWriter
{
    public $panel_type;
    public $query = null;
    public $visible = true;
    public $text = "";
    public $program = "";
    public $xml_version = "1.0";
    public $xmldata;

    public function __construct(&$in_query)
    {
        $this->query = &$in_query;
    }

    public function setVisibility($in_visibility)
    {
        $this->visible = $in_visibility;
    }

    public function prepareXmlData()
    {
        $xmlval = new ReporticoXmlval("Report");

        $cq = &$xmlval->add_xmlval("ReportQuery");

        $at = &$cq->add_xmlval("Format");

        // Query Attributes
        foreach ($this->query->attributes as $k => $v) {
            $el = &$at->add_xmlval($k, $v);
        }

        //$el =& $cq->add_xmlval ( "Name", $this->query->name );

        // Export Data Connection Details
        $ds = &$cq->add_xmlval("Datasource");
        $el = &$ds->add_xmlval("SourceType", $this->query->source_type);

        $cn = &$ds->add_xmlval("SourceConnection");
        switch ($this->query->source_type) {
            case "database":
            case "informix":
            case "mysql":
            case "sqlite-2":
            case "sqlite-3":
                if ( false && $this->query->datasource ) {
                    $el = &$cn->add_xmlval("DatabaseType", $this->query->datasource->driver);
                    $el = &$cn->add_xmlval("DatabaseName", $this->query->datasource->database);
                    $el = &$cn->add_xmlval("HostName", $this->query->datasource->host_name);
                    $el = &$cn->add_xmlval("ServiceName", $this->query->datasource->service_name);
                    $el = &$cn->add_xmlval("UserName", $this->query->datasource->user_name);
                    $el = &$cn->add_xmlval("Password", $this->query->datasource->password);
                }
                break;

            default:
                $el = &$cn->add_xmlval("DatabaseType", $this->query->datasource->driver);
                $el = &$cn->add_xmlval("DatabaseName", $this->query->datasource->database);
                $el = &$cn->add_xmlval("HostName", $this->query->datasource->host_name);
                $el = &$cn->add_xmlval("ServiceName", $this->query->datasource->service_name);
                $el = &$cn->add_xmlval("UserName", $this->query->datasource->user_name);
                $el = &$cn->add_xmlval("Password", $this->query->datasource->password);
                break;

        }

        $this->xmldata = &$xmlval;

        // Export Main Entry Form Parameters
        $ef = &$cq->add_xmlval("EntryForm");

        // Export Main Query Parameters
        $qr = &$ef->add_xmlval("Query");
        $el = &$qr->add_xmlval("TableSql", $this->query->table_text);
        $el = &$qr->add_xmlval("WhereSql", $this->query->where_text);
        $el = &$qr->add_xmlval("GroupSql", $this->query->group_text);
        $el = &$qr->add_xmlval("RowSelection", $this->query->rowselection);
        $sq = &$qr->add_xmlval("SQL");
        $el = &$sq->add_xmlval("QuerySql", "");
        $el = &$sq->add_xmlval("SQLRaw", $this->query->sql_raw);

        $qcs = &$qr->add_xmlval("QueryColumns");
        foreach ($this->query->columns as $col) {
            $qc = &$qcs->add_xmlval("QueryColumn");
            $el = &$qc->add_xmlval("Name", $col->query_name);
            $el = &$qc->add_xmlval("TableName", $col->table_name);
            $el = &$qc->add_xmlval("ColumnName", $col->column_name);
            $el = &$qc->add_xmlval("ColumnType", $col->column_type);
            $el = &$qc->add_xmlval("ColumnLength", $col->column_length);

            // Column Attributes
            $at = &$qc->add_xmlval("Format");
            foreach ($col->attributes as $k => $v)
            //if ( $v )
            {
                $el = &$at->add_xmlval($k, $v);
            }

        }

        $qos = &$qr->add_xmlval("OrderColumns");
        foreach ($this->query->order_set as $col) {
            $qoc = &$qos->add_xmlval("OrderColumn");
            $el = &$qoc->add_xmlval("Name", $col->query_name);
            $el = &$qoc->add_xmlval("OrderType", $col->order_type);
        }

        $prcr = &$qr->add_xmlval("PreSQLS");
        foreach ($this->query->pre_sql as $prsq) {
            $sqtx = &$prcr->add_xmlval("PreSQL");
            $el = &$sqtx->add_xmlval("SQLText", $prsq);
        }

        // Output Assignments
        $as = &$ef->add_xmlval("Assignments");
        foreach ($this->query->assignment as $col) {
            $qcas = &$as->add_xmlval("Assignment");
            $el = &$qcas->add_xmlval("AssignName", $col->query_name);
            $el = &$qcas->add_xmlval("AssignNameNew", "");
            $el = &$qcas->add_xmlval("Expression", $col->raw_expression);
            $el = &$qcas->add_xmlval("Condition", $col->raw_criteria);
        }

        // Add Lookup Attributes As Separate Criteria Item
        $cr = &$ef->add_xmlval("Criteria");
        foreach ($this->query->lookup_queries as $lq) {
            // find which columns are for returning displaying etc
            $lookup_return_col = "";
            $lookup_display_col = "";
            $lookup_abbrev_col = "";

            foreach ($lq->lookup_query->columns as $cqc) {
                if ($cqc->lookup_return_flag) {
                    $lookup_return_col = $cqc->query_name;
                }
                if ($cqc->lookup_display_flag) {
                    $lookup_display_col = $cqc->query_name;
                }
                if ($cqc->lookup_abbrev_flag) {
                    $lookup_abbrev_col = $cqc->query_name;
                }
            }
            $ci = &$cr->add_xmlval("CriteriaItem");
            $el = &$ci->add_xmlval("Name", $lq->query_name);
            if ($lq->link_to_report) {
                $el = &$ci->add_xmlval("LinkToReport", $lq->link_to_report);
                $el = &$ci->add_xmlval("LinkToReportItem", $lq->link_to_report_item);
            } else {
                $el = &$ci->add_xmlval("Title", $lq->getAttribute("column_title"));
                $el = &$ci->add_xmlval("QueryTableName", $lq->table_name);
                $el = &$ci->add_xmlval("QueryColumnName", $lq->column_name);
                $el = &$ci->add_xmlval("CriteriaType", $lq->criteria_type);
                if (defined("SW_DYNAMIC_ORDER_GROUP")) {
                    $el = &$ci->add_xmlval("Use", $lq->_use);
                }

                $el = &$ci->add_xmlval("CriteriaHelp", $lq->criteria_help);
                $el = &$ci->add_xmlval("CriteriaDisplay", $lq->criteria_display);
                $el = &$ci->add_xmlval("ExpandDisplay", $lq->expand_display);
//echo "XML $lq->query_name $lq->criteria_display $lq->required $lq->criteria_list<BR>";
                $el = &$ci->add_xmlval("CriteriaRequired", $lq->required);
                $el = &$ci->add_xmlval("CriteriaHidden", $lq->hidden);
                $el = &$ci->add_xmlval("CriteriaDisplayGroup", $lq->display_group);
                $el = &$ci->add_xmlval("ReturnColumn", $lookup_return_col);
                $el = &$ci->add_xmlval("DisplayColumn", $lookup_display_col);
                $el = &$ci->add_xmlval("OverviewColumn", $lookup_abbrev_col);
                $el = &$ci->add_xmlval("MatchColumn", $lq->lookup_query->match_column);
                $el = &$ci->add_xmlval("CriteriaDefaults", $lq->defaults_raw);
                $el = &$ci->add_xmlval("CriteriaList", $lq->criteria_list);
                $q2 = &$ci->add_xmlval("Query");
                $el = &$q2->add_xmlval("TableSql", $lq->lookup_query->table_text);
                $el = &$q2->add_xmlval("WhereSql", $lq->lookup_query->where_text);
                $el = &$q2->add_xmlval("GroupSql", $lq->lookup_query->group_text);
                $el = &$q2->add_xmlval("RowSelection", $lq->lookup_query->group_text);
                $el = &$q2->add_xmlval("SQLRaw", $lq->lookup_query->sql_raw);
                $sq2 = &$q2->add_xmlval("SQL");
                $el = &$sq2->add_xmlval("QuerySql", "");

                $qcs2 = &$q2->add_xmlval("QueryColumns");
                foreach ($lq->lookup_query->columns as $lc) {

                    $qc2 = &$qcs2->add_xmlval("QueryColumn");
                    $el = &$qc2->add_xmlval("Name", $lc->query_name);
                    $el = &$qc2->add_xmlval("TableName", $lc->table_name);
                    $el = &$qc2->add_xmlval("ColumnName", $lc->column_name);
                    $el = &$qc2->add_xmlval("ColumnType", $lc->column_type);
                    $el = &$qc2->add_xmlval("ColumnLength", $lc->column_length);

                    // Column Attributes
                    $at = &$qc2->add_xmlval("Format");
                    foreach ($lc->attributes as $k => $v) {
                        if ($v) {
                            $el = &$at->add_xmlval($k, $v);
                        }
                    }

                }

                $qos2 = &$q2->add_xmlval("OrderColumns");
                foreach ($lq->lookup_query->order_set as $col) {
                    $qoc2 = &$qos2->add_xmlval("OrderColumn");
                    $el = &$qoc2->add_xmlval("Name", $col->query_name);
                    $el = &$qoc2->add_xmlval("OrderType", $col->order_type);
                }

                // Output Assignments
                $ascr = &$q2->add_xmlval("Assignments");
                foreach ($lq->lookup_query->assignment as $asg) {
                    $qc = &$ascr->add_xmlval("Assignment");
                    $el = &$qc->add_xmlval("AssignName", $asg->query_name);
                    $el = &$qc->add_xmlval("AssignNameNew", "");
                    $el = &$qc->add_xmlval("Expression", $asg->raw_expression);
                    $el = &$qc->add_xmlval("Condition", $asg->raw_criteria);
                }

                $clcr = &$ci->add_xmlval("CriteriaLinks");
                foreach ($lq->lookup_query->criteria_links as $ky => $lk) {
                    $clicr = &$clcr->add_xmlval("CriteriaLink");
                    $el = &$clicr->add_xmlval("LinkFrom", $lk["link_from"]);
                    $el = &$clicr->add_xmlval("LinkTo", $lk["tag"]);
                    $el = &$clicr->add_xmlval("LinkClause", $lk["clause"]);
                }
            }
        }

        // Output Report Output Details
        $op = &$ef->add_xmlval("Output");
        {
            $ph = &$op->add_xmlval("PageHeaders");
            foreach ($this->query->pageHeaders as $k => $val) {
                $phi = &$ph->add_xmlval("PageHeader");
                $el = &$phi->add_xmlval("LineNumber", $val->line);
                $el = &$phi->add_xmlval("HeaderText", $val->text);

                $phf = &$phi->add_xmlval("Format");
                foreach ($val->attributes as $k => $v) {
                    $el = &$phf->add_xmlval($k, $v);
                }

            }

            $pt = &$op->add_xmlval("PageFooters");
            foreach ($this->query->pageFooters as $val) {
                $pti = &$pt->add_xmlval("PageFooter");
                $el = &$pti->add_xmlval("LineNumber", $val->line);
                $el = &$pti->add_xmlval("FooterText", $val->text);

                $ptf = &$pti->add_xmlval("Format");
                foreach ($val->attributes as $k => $v) {
                    $el = &$ptf->add_xmlval($k, $v);
                }

            }

            $do = &$op->add_xmlval("DisplayOrders");
            $ct = 0;
            if (count($this->query->display_order_set) > 0) {
                foreach ($this->query->display_order_set["itemno"] as $val) {
                    $doi = &$do->add_xmlval("DisplayOrder");
                    $el = &$doi->add_xmlval("ColumnName", $this->query->display_order_set["column"][$ct]->query_name);
                    $el = &$doi->add_xmlval("OrderNumber", $this->query->display_order_set["itemno"][$ct]);
                    $ct++;
                }
            }

            $gp = &$op->add_xmlval("Groups");
            foreach ($this->query->groups as $k => $val) {
                $gpi = &$gp->add_xmlval("Group");
                $el = &$gpi->add_xmlval("GroupName", $val->group_name);
                $el = &$gpi->add_xmlval("BeforeGroupHeader", $val->getAttribute("before_header"));
                $el = &$gpi->add_xmlval("AfterGroupHeader", $val->getAttribute("after_header"));
                $el = &$gpi->add_xmlval("BeforeGroupTrailer", $val->getAttribute("before_trailer"));
                $el = &$gpi->add_xmlval("AfterGroupTrailer", $val->getAttribute("after_trailer"));

                $gph = &$gpi->add_xmlval("GroupHeaders");
                foreach ($val->headers as $k5 => $val2) {
                    $gphi = &$gph->add_xmlval("GroupHeader");
                    if (!isset($val2["GroupHeaderCustom"])) {
                        $val2["GroupHeaderCustom"] = false;
                    }

                    if ( !is_object($val2["GroupHeaderColumn"])  ) {
                        trigger_error("Unable to generate group header for unknown column ".$val->group_name, E_USER_ERROR) ;
                        $el = &$gphi->add_xmlval("GroupHeaderColumn", false );
                    }
                    else
                    {
                    $el = &$gphi->add_xmlval("GroupHeaderColumn", $val2["GroupHeaderColumn"]->query_name);
                    }
                    $el = &$gphi->add_xmlval("GroupHeaderCustom", $val2["GroupHeaderCustom"]);
                    $el = &$gphi->add_xmlval("ShowInHTML", $val2["ShowInHTML"]);
                    $el = &$gphi->add_xmlval("ShowInPDF", $val2["ShowInPDF"]);
                    
                }

                $gpt = &$gpi->add_xmlval("GroupTrailers");
                foreach ($val->trailers as $k2 => $val2) {
                    if (is_array($val2)) {
                        if (!isset($val2["GroupTrailerCustom"])) {
                            $val2["GroupTrailerCustom"] = false;
                        }

                        $gpti = &$gpt->add_xmlval("GroupTrailer");
                        $el = &$gpti->add_xmlval("GroupTrailerDisplayColumn", $val2["GroupTrailerDisplayColumn"]);
                        $el = &$gpti->add_xmlval("GroupTrailerValueColumn", $val2["GroupTrailerValueColumn"]->query_name);
                        $el = &$gpti->add_xmlval("GroupTrailerCustom", $val2["GroupTrailerCustom"]);
                        $el = &$gpti->add_xmlval("ShowInHTML", $val2["ShowInHTML"]);
                        $el = &$gpti->add_xmlval("ShowInPDF", $val2["ShowInPDF"]);
                    }
                }
            }

            $ggphs = &$op->add_xmlval("Graphs");
            foreach ($this->query->graphs as $k => $v) {
                $ggrp = &$ggphs->add_xmlval("Graph");
                $el = &$ggrp->add_xmlval("GraphColumn", $v->graph_column);

                $el = &$ggrp->add_xmlval("GraphColor", $v->graphcolor);
                $el = &$ggrp->add_xmlval("Title", $v->title);
                $el = &$ggrp->add_xmlval("GraphWidth", $v->width);
                $el = &$ggrp->add_xmlval("GraphHeight", $v->height);
                $el = &$ggrp->add_xmlval("GraphWidthPDF", $v->width_pdf);
                $el = &$ggrp->add_xmlval("GraphHeightPDF", $v->height_pdf);
                $el = &$ggrp->add_xmlval("XTitle", $v->xtitle);
                $el = &$ggrp->add_xmlval("YTitle", $v->ytitle);
                $el = &$ggrp->add_xmlval("GridPosition", $v->gridpos);
                $el = &$ggrp->add_xmlval("XGridDisplay", $v->xgriddisplay);
                $el = &$ggrp->add_xmlval("XGridColor", $v->xgridcolor);
                $el = &$ggrp->add_xmlval("YGridDisplay", $v->ygriddisplay);
                $el = &$ggrp->add_xmlval("YGridColor", $v->ygridcolor);
                $el = &$ggrp->add_xmlval("XLabelColumn", $v->xlabel_column);

                $el = &$ggrp->add_xmlval("TitleFont", $v->titlefont);
                $el = &$ggrp->add_xmlval("TitleFontStyle", $v->titlefontstyle);
                $el = &$ggrp->add_xmlval("TitleFontSize", $v->titlefontsize);
                $el = &$ggrp->add_xmlval("TitleColor", $v->titlecolor);

                $el = &$ggrp->add_xmlval("XTitleFont", $v->xtitlefont);
                $el = &$ggrp->add_xmlval("XTitleFontStyle", $v->xtitlefontstyle);
                $el = &$ggrp->add_xmlval("XTitleFontSize", $v->xtitlefontsize);
                $el = &$ggrp->add_xmlval("XTitleColor", $v->xtitlecolor);

                $el = &$ggrp->add_xmlval("YTitleFont", $v->ytitlefont);
                $el = &$ggrp->add_xmlval("YTitleFontStyle", $v->ytitlefontstyle);
                $el = &$ggrp->add_xmlval("YTitleFontSize", $v->ytitlefontsize);
                $el = &$ggrp->add_xmlval("YTitleColor", $v->ytitlecolor);

                $el = &$ggrp->add_xmlval("XAxisColor", $v->xaxiscolor);
                $el = &$ggrp->add_xmlval("XAxisFont", $v->xaxisfont);
                $el = &$ggrp->add_xmlval("XAxisFontStyle", $v->xaxisfontstyle);
                $el = &$ggrp->add_xmlval("XAxisFontSize", $v->xaxisfontsize);
                $el = &$ggrp->add_xmlval("XAxisFontColor", $v->xaxisfontcolor);

                $el = &$ggrp->add_xmlval("YAxisColor", $v->yaxiscolor);
                $el = &$ggrp->add_xmlval("YAxisFont", $v->yaxisfont);
                $el = &$ggrp->add_xmlval("YAxisFontStyle", $v->yaxisfontstyle);
                $el = &$ggrp->add_xmlval("YAxisFontSize", $v->yaxisfontsize);
                $el = &$ggrp->add_xmlval("YAxisFontColor", $v->yaxisfontcolor);

                $el = &$ggrp->add_xmlval("XTickInterval", $v->xtickinterval);
                $el = &$ggrp->add_xmlval("YTickInterval", $v->ytickinterval);
                $el = &$ggrp->add_xmlval("XTickLabelInterval", $v->xticklabelinterval);
                $el = &$ggrp->add_xmlval("YTickLabelInterval", $v->yticklabelinterval);

                $el = &$ggrp->add_xmlval("MarginColor", $v->margincolor);

                $el = &$ggrp->add_xmlval("MarginLeft", $v->marginleft);
                $el = &$ggrp->add_xmlval("MarginRight", $v->marginright);
                $el = &$ggrp->add_xmlval("MarginTop", $v->margintop);
                $el = &$ggrp->add_xmlval("MarginBottom", $v->marginbottom);

                $gplt = &$ggrp->add_xmlval("Plots");
                foreach ($v->plot as $k => $val2) {
                    $gpltd = &$gplt->add_xmlval("Plot");
                    $el = &$gpltd->add_xmlval("PlotColumn", $val2["name"]);
                    $el = &$gpltd->add_xmlval("PlotType", $val2["type"]);
                    $el = &$gpltd->add_xmlval("LineColor", $val2["linecolor"]);
                    $el = &$gpltd->add_xmlval("DataType", $val2["datatype"]);
                    $el = &$gpltd->add_xmlval("Legend", $val2["legend"]);
                    $el = &$gpltd->add_xmlval("FillColor", $val2["fillcolor"]);
                }
            }

        } // Output Section
    }

    public function getXmldata()
    {
        $text = '<?xml version="' . $this->xml_version . '"?>';
        $text .= $this->xmldata->unserialize();
        return $text;
    }

    public function write()
    {
        //header('Content-Type: text/xml');
        header('Content-Type: text/html');
        //echo '<?xml version="'.$this->xml_version.'"?s>';
        echo '<HTML><BODY><PRE>';
        //$this->xmldata->write();
        $xmltext = $this->xmldata->unserialize();
        echo htmlspecialchars($xmltext);
        echo '</PRE></BODY></HTML>';
    }

    public function writeReportFile($filename, &$writedata)
    {
        $fn = $this->query->reports_path . "/" . $filename;
        if (!($fd = fopen($fn, "w"))) {
            return false;
        }

        if (!fwrite($fd, $writedata)) {
            return false;
        }

        fclose($fd);

        return (true);

    }

    // Remove report XML from disk
    public function removeFile($filename)
    {

        if (!$filename) {
            trigger_error(ReporticoLang::templateXlate("UNABLE_TO_REMOVE") . ReporticoLang::templateXlate("SPECIFYXML"), E_USER_ERROR);
            return false;
        }

        if (!preg_match("/\.xml$/", $filename)) {
            $filename = $filename . ".xml";
        }

        $projdir = $this->query->projects_folder . "/" . ReporticoApp::getConfig("project");
        if (!is_file($projdir)) {
            ReporticoUtility::findFileToInclude($projdir, $projdir);
        }

        if ($projdir && is_dir($projdir)) {
            $fn = $projdir . "/" . $filename;
            if (!is_file($fn)) {
                trigger_error(ReporticoLang::templateXlate("UNABLE_TO_REMOVE") . " $filename  - " . ReporticoLang::templateXlate("NOFILE"), E_USER_ERROR);
            } else if (!is_writeable($fn)) {
                trigger_error(ReporticoLang::templateXlate("UNABLE_TO_REMOVE") . " $filename  - " . ReporticoLang::templateXlate("NOWRITE"), E_USER_ERROR);
            } else {
                if (!unlink($fn)) {
                    trigger_error(ReporticoLang::templateXlate("UNABLE_TO_REMOVE") . " $filename  - " . ReporticoLang::templateXlate("NOWRITE"), E_USER_ERROR);
                } else {
                    ReporticoApp::handleDebug(ReporticoLang::templateXlate("REPORTFILE") . " $filename " . ReporticoLang::templateXlate("DELETEOKACT"), 0);
                }

            }
        } else {
            trigger_error("Unable to open project area " . ReporticoApp::getConfig("project") . " to save file $filename " .
                $this->query->reports_path . "/" . $filename . " Not Found", E_USER_ERROR);
        }

    }

    // Save report XML to disk
    public function writeFile($filename)
    {

        if (!$filename) {
            trigger_error(ReporticoLang::templateXlate("UNABLE_TO_SAVE") . ReporticoLang::templateXlate("SPECIFYXML"), E_USER_ERROR);
            return false;
        }

        if (!preg_match("/\.xml$/", $filename)) {
            $filename = $filename . ".xml";
        }

        $projdir = $this->query->projects_folder . "/" . ReporticoApp::getConfig("project");
        if (!is_file($projdir)) {
            ReporticoUtility::findFileToInclude($projdir, $projdir);
        }

        if ($projdir && is_dir($projdir)) {
            $fn = $projdir . "/" . $filename;
            if (!($fd = @fopen($fn, "w"))) {

                trigger_error("Unable to open project file " . $fn . " for writing. Check folder and file permissions " , E_USER_ERROR);
                return false;
            }
        } else {


            trigger_error("Unable to open project area " . ReporticoApp::getConfig("project") . " to save file $filename " .
                $this->query->reports_path . "/" . $filename . " Not Found", E_USER_ERROR);
        }

        if (!fwrite($fd, '<?xml version="' . $this->xml_version . '"?>')) {
            return false;
        }

        $xmltext = $this->xmldata->unserialize();
        if (!fwrite($fd, $xmltext)) {
            return false;
        }

        fclose($fd);

    }

}
