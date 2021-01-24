<?php

namespace Reportico\Engine;

/**
 * Class XmlReader
 *
 * responsible for loading, parsing and organising
 * Reportico report XML definition files
 */
class XmlReader
{
    public $query;
    public $parser;
    public $records;
    public $record;
    public $value;
    public $current_field = '';
    public $xmltag_type;
    public $ends_record;
    public $queries = array();
    public $data = array();
    public $element_stack = array();
    public $query_stack = array();
    public $column_stack = array();
    public $action_stack = array();
    public $datasource_stack = array();
    public $current_element;
    public $current_datasource;
    public $current_query;
    public $current_action;
    public $current_column;
    public $current_object;
    public $element_count = 0;
    public $level_ct = 0;
    public $oldid = "";
    public $id = "";
    public $last_element = "";
    public $in_column_section = false;
    public $current_criteria_name = false;
    public $show_level = false;
    public $show_area = false;
    public $search_tag = false;
    public $search_response = false;
    public $element_counts = array();
    public $wizard_linked_to = false;
    public $partial_apply_drawn = false;

    public function __construct(&$query, $filename, $xmlstring = false, $search_tag = false, $debug = false, $no_authentication = false)
    {
        $this->query = &$query;

        $this->parser = xml_parser_create();
        $this->search_tag = $search_tag;
        $this->current_element = &$this->data;
        $fontlist = array(".DEFAULT", "Vera", "Font 1", "Font 2", "Font 3", "Trebuchet", "Arial", "Times", "Verdana", "Courier", "Book", "Comic", "Script");
        $this->field_display = array(
            "Expression" => array("Title" => "EXPRESSION", "EditMode" => "SAFE", "DocId" => "expression"),
            "Condition" => array("Title" => "CONDITION", "EditMode" => "SAFE", "DocId" => "condition"),
            "GroupHeaderColumn" => array("Title" => "GROUPHEADERCOLUMN", "Type" => "QUERYCOLUMNS"),
            "GroupTrailerDisplayColumn" => array("Title" => "GROUPTRAILERDISPLAYCOLUMN", "Type" => "QUERYCOLUMNS"),
            "GroupTrailerValueColumn" => array("Title" => "GROUPTRAILERVALUECOLUMN", "Type" => "QUERYCOLUMNS"),
            "ColumnType" => array("Type" => "HIDE"),
            "ColumnLength" => array("Type" => "HIDE"),
            "ColumnName" => array("Type" => "HIDE"),
            "QueryName" => array("Type" => "HIDE"),
            "Name" => array("Title" => "NAME", "Type" => "TEXTFIELD", "DocId" => "name"),
            "QueryTableName" => array("Type" => "HIDE", "HelpPage" => "criteria"),
            "QueryColumnName" => array("Title" => "QUERYCOLUMNNAME", "HelpPage" => "criteria", "DocId" => "main_query_column"),
            "TableName" => array("Type" => "HIDE"),
            "TableSql" => array("Type" => "SHOW"),
            "WhereSql" => array("Type" => "HIDE"),
            "GroupSql" => array("Type" => "SHOW"),
            "RowSelection" => array("Type" => "SHOW"),
            "ReportTitle" => array("Title" => "REPORTTITLE", "DocId" => "report_title"),
            "LinkFrom" => array("Title" => "LINKFROM", "Type" => "CRITERIA"),
            "LinkTo" => array("Title" => "LINKTO", "Type" => "CRITERIA"),
            "LinkClause" => array("Title" => "LINKCLAUSE"),
            "AssignName" => array("Title" => "ASSIGNNAME", "Type" => "QUERYCOLUMNS", "DocId" => "assign_to_existing_column"),
            "AssignNameNew" => array("Title" => "ASSIGNNAMENEW", "DocId" => "assign_to_newColumn"),
            "AssignImageUrl" => array("Title" => "ASSIGNIMAGEURL", "DocId" => "image_url"),
            "AssignHyperlinkLabel" => array("Title" => "ASSIGNHYPERLABEL", "DocId" => "hyperlink_label"),
            "AssignHyperlinkUrl" => array("Title" => "ASSIGNHYPERURL", "DocId" => "hyperlink_url"),

            "GroupHeaderStyleFgColor" => array("Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour"),
            "GroupHeaderStyleBgColor" => array("Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour"),
            "GroupHeaderStyleBorderStyle" => array("Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style"),
            "GroupHeaderStyleBorderSize" => array("Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness"),
            "GroupHeaderStyleBorderColor" => array("Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour"),
            "GroupHeaderStyleMargin" => array("Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "GroupHeaderStylePadding" => array("Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "GroupHeaderStyleFontName" => array("Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name"),
            "GroupHeaderStyleFontSize" => array("Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size"),
            "GroupHeaderStyleFontStyle" => array("Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style"),
            "GroupHeaderStyleWidth" => array("Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width"),
            "GroupHeaderStyleHeight" => array("Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height"),
            "GroupHeaderStylePosition" => array("Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page"),
            "GroupHeaderStyleBackgroundImage" => array("Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image"),

            "GroupTrailerStyleFgColor" => array("Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour"),
            "GroupTrailerStyleBgColor" => array("Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour"),
            "GroupTrailerStyleBorderStyle" => array("Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style"),
            "GroupTrailerStyleBorderSize" => array("Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness"),
            "GroupTrailerStyleBorderColor" => array("Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour"),
            "GroupTrailerStyleMargin" => array("Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "GroupTrailerStylePadding" => array("Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding"),
            "GroupTrailerStyleFontName" => array("Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name"),
            "GroupTrailerStyleFontSize" => array("Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size"),
            "GroupTrailerStyleFontStyle" => array("Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style"),
            "GroupTrailerStyleWidth" => array("Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width"),
            "GroupTrailerStyleHeight" => array("Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height"),
            "GroupTrailerStylePosition" => array("Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page"),
            "GroupTrailerStyleBackgroundImage" => array("Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image"),

            "PageHeaderStyleFgColor" => array("Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour"),
            "PageHeaderStyleBgColor" => array("Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour"),
            "PageHeaderStyleBorderStyle" => array("Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style"),
            "PageHeaderStyleBorderSize" => array("Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness"),
            "PageHeaderStyleBorderColor" => array("Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour"),
            "PageHeaderStyleMargin" => array("Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "PageHeaderStylePadding" => array("Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding"),
            "PageHeaderStyleFontName" => array("Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name"),
            "PageHeaderStyleFontSize" => array("Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size"),
            "PageHeaderStyleFontStyle" => array("Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style"),
            "PageHeaderStyleWidth" => array("Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width"),
            "PageHeaderStyleHeight" => array("Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height"),
            "PageHeaderStylePosition" => array("Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page"),
            "PageHeaderStyleBackgroundImage" => array("Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image"),

            "PageFooterStyleFgColor" => array("Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour"),
            "PageFooterStyleBgColor" => array("Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour"),
            "PageFooterStyleBorderStyle" => array("Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style"),
            "PageFooterStyleBorderSize" => array("Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness"),
            "PageFooterStyleBorderColor" => array("Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour"),
            "PageFooterStyleMargin" => array("Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "PageFooterStylePadding" => array("Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding"),
            "PageFooterStyleFontName" => array("Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name"),
            "PageFooterStyleFontSize" => array("Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size"),
            "PageFooterStyleFontStyle" => array("Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style"),
            "PageFooterStyleHeight" => array("Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "width"),
            "PageFooterStyleWidth" => array("Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "height"),
            "PageFooterStylePosition" => array("Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page"),
            "PageFooterBackgroundImage" => array("Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image"),

            "GroupHeaderCustom" => array("Title" => "GROUPHEADERCUSTOM", "Type" => "TEXTBOXNARROW", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "group_header_custom_text"),
            "GroupTrailerCustom" => array("Title" => "GROUPTRAILERCUSTOM", "Type" => "TEXTBOXNARROW", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "group_trailer_custom_text"),

            "AssignStyleLocType" => array("Title" => "ASSIGNSTYLELOCTYPE", "Type" => "STYLELOCTYPES", "XlateOptions" => true, "DocId" => "applyStyle_to"),
            "AssignStyleFgColor" => array("Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour"),
            "AssignStyleBgColor" => array("Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour"),
            "AssignStyleBorderStyle" => array("Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style"),
            "AssignStyleBorderSize" => array("Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness"),
            "AssignStyleBorderColor" => array("Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border colour"),
            "AssignStyleMargin" => array("Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin"),
            "AssignStylePadding" => array("Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding"),
            "AssignStyleFontName" => array("Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name"),
            "AssignStyleFontSize" => array("Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size", "DocId" => "font_size"),
            "AssignStyleFontStyle" => array("Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style", "DocId" => "font_style"),
            "AssignStyleWidth" => array("Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width"),
            "AssignAggType" => array("Title" => "ASSIGNAGGTYPE", "Type" => "AGGREGATETYPES", "XlateOptions" => true, "DocId" => "aggregate_type"),
            "AssignAggCol" => array("Title" => "ASSIGNAGGCOL", "Type" => "QUERYCOLUMNS", "DocId" => "aggregate_column"),
            "AssignAggGroup" => array("Title" => "ASSIGNAGGGROUP", "Type" => "QUERYCOLUMNSOPTIONAL", "DocId" => "grouped_by"),
            "AssignGraphicBlobCol" => array("Title" => "ASSIGNGRAPHICBLOBCOL", "DocId" => "column_containing_graphic"),
            "AssignGraphicBlobTab" => array("Title" => "ASSIGNGRAPHICBLOBTAB", "DocId" => "table_containing_graphic"),
            "AssignGraphicBlobMatch" => array("Title" => "ASSIGNGRAPHICBLOBMATCH", "DocId" => "column_to_match_report_graphic"),
            "AssignGraphicWidth" => array("Title" => "ASSIGNGRAPHICWIDTH", "DocId" => "report_graphic_width"),
            "AssignGraphicReportCol" => array("Title" => "ASSIGNGRAPHICREPORTCOL", "Type" => "QUERYCOLUMNS", "DocId" => "graphic_report_column"),
            "LinkToElement" => array("Title" => "LINKTOREPORT", "Type" => "REPORTLIST"),
            "DrilldownReport" => array("Title" => "DRILLDOWNREPORT", "Type" => "REPORTLIST", "DocId" => "drilldown_report"),
            "DrilldownColumn" => array("Title" => "DRILLDOWNCOLUMN", "Type" => "QUERYCOLUMNSOPTIONAL", "DocId" => "drilldown_criteria"),
            "GroupName" => array("Title" => "GROUPNAME", "Type" => "GROUPCOLUMNS", "DocId" => "group_on_column"),
            "GraphColumn" => array("Title" => "GRAPHCOLUMN", "Type" => "QUERYGROUPS", "DocId" => "group_column"),
            "GraphHeight" => array("Title" => "GRAPHHEIGHT", "DocId" => "graph_height"),
            "GraphWidth" => array("Title" => "GRAPHWIDTH", "DocId" => "graph_width"),
            "GraphColor" => array("Title" => "GRAPHCOLOR"),
            "GraphWidthPDF" => array("Title" => "GRAPHWIDTHPDF", "DocId" => "graph_height_pdf"),
            "GraphHeightPDF" => array("Title" => "GRAPHHEIGHTPDF", "DocId" => "graph_height_pdf"),
            "XTitle" => array("Title" => "XTITLE", "DocId" => "x_axis_title"),
            "YTitle" => array("Title" => "YTITLE", "DocId" => "y_axis_title"),
            "GridPosition" => array("Title" => "GRIDPOSITION"),
            "PlotStyle" => array("Title" => "PLOTSTYLE"),
            "LineColor" => array("Title" => "LINECOLOR", "DocId" => "line_color"),
            "DataType" => array("Title" => "DATATYPE", "Type" => "HIDE"),
            "Legend" => array("Title" => "LEGEND", "DocId" => "legend"),
            "FillColor" => array("Title" => "FILLCOLOR"),
            "XGridColor" => array("Title" => "XGRIDCOLOR"),
            "YGridColor" => array("Title" => "YGRIDCOLOR"),
            "TitleFontSize" => array("Title" => "TITLEFONTSIZE"),
            "XTickInterval" => array("Title" => "XTICKINTERVAL"),
            "YTickInterval" => array("Title" => "YTICKINTERVAL"),
            "XTickLabelInterval" => array("Title" => "XTICKLABELINTERVAL", "DocId" => "x_tick_label_interval"),
            "YTickLabelInterval" => array("Title" => "YTICKLABELINTERVAL"),
            "XTitleFontSize" => array("Title" => "XTITLEFONTSIZE"),
            "YTitleFontSize" => array("Title" => "YTITLEFONTSIZE"),
            "MarginColor" => array("Title" => "MARGINCOLOR", "DocId" => "margin_color"),
            "MarginLeft" => array("Title" => "MARGINLEFT", "DocId" => "margin_left"),
            "MarginRight" => array("Title" => "MARGINRIGHT", "DocId" => "margin_right"),
            "MarginTop" => array("Title" => "MARGINTOP", "DocId" => "margin_top"),
            "MarginBottom" => array("Title" => "MARGINBOTTOM", "DocId" => "margin_bottom"),
            "TitleColor" => array("Title" => "TITLECOLOR"),
            "XAxisColor" => array("Title" => "XAXISCOLOR"),
            "YAxisColor" => array("Title" => "YAXISCOLOR"),
            "XAxisFontColor" => array("Title" => "XAXISFONTCOLOR"),
            "YAxisFontColor" => array("Title" => "YAXISFONTCOLOR"),
            "XAxisFontSize" => array("Title" => "XAXISFONTSIZE"),
            "YAxisFontSize" => array("Title" => "YAXISFONTSIZE"),
            "XTitleColor" => array("Title" => "XTITLECOLOR"),
            "YTitleColor" => array("Title" => "YTITLECOLOR"),
            "PlotColumn" => array("Title" => "PLOTCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "column_to_plot", "DocId" => "column_to_plot"),
            "XLabelColumn" => array("Title" => "XLABELCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "column_for_x_labels"),
            //"YLabelColumn" => array ( "Title" => "YLABELCOLUMN", "Type" => "HIDE"),
            "ReturnColumn" => array("Title" => "RETURNCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "return_column"),
            "CriteriaDisplayGroup" => array("Title" => "CRITERIADISPLAYGROUP", "HelpPage" => "criteria", "DocId" => "display_group"),
            "CriteriaHidden" => array("Title" => "CRITERIAHIDDEN", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "yes", "no"), "HelpPage" => "criteria", "DocId" => "criteria_hidden"),
            "CriteriaRequired" => array("Title" => "CRITERIAREQUIRED", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "yes", "no"), "HelpPage" => "criteria", "DocId" => "criteria_required"),
            "CriteriaHelp" => array("Title" => "CRITERIAHELP", "Type" => "TEXTBOXSMALL", "DocId" => "criteria_help"),
            "MatchColumn" => array("Title" => "MATCHCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "match_column"),
            "MatchColumn" => array("Title" => "MATCHCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "match_column"),
            "DisplayColumn" => array("Title" => "DISPLAYCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "display_column"),
            "OverviewColumn" => array("Title" => "OVERVIEWCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "overview_column"),
            "content_type" => array("Title" => "CONTENTTYPE", "Type" => "HIDE", "XlateOptions" => true,
                "Values" => array("plain", "graphic")),
            "PreExecuteCode" => array(
                "Title" => "CUSTOMSOURCECODE",
                "Type" => "TEXTBOX", "DocId" => "custom_source_code",
                "EditMode" => "SAFE"),
            "ReportDescription" => array("Title" => "REPORTDESCRIPTION", "Type" => "TEXTBOX", "DocId" => "report_description"),
            "SQLText" => array("Title" => "SQLTEXT", "Type" => "TEXTBOX", "EditMode" => "SAFE", "DocId" => "pre-sql_text_entry"),
            "QuerySql" => array("Title" => "QUERYSQL", "Type" => "TEXTBOX", "DocSection" => "the_query_details_menu", "DocId" => "sql_query"),
            "SQLRaw" => array("Title" => "SQLRAW", "Type" => "HIDE"),
            "Password" => array("Type" => "PASSWORD"),
            "PageSize" => array("Title" => "PAGESIZE", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "B5", "A6", "A5", "A4", "A3", "A2", "A1",
                    "US-Letter", "US-Legal", "US-Ledger"), "DocId" => "page_size_pdf"),
            "AutoPaginate" => array("Title" => "AUTOPAGINATE", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "None", "HTML", "PDF", "HTML+PDF" ), "DocId" => "autopaginate"),
            "PdfZoomFactor" => array("Title" => "PDFZOOMFACTOR", "DocId" => "pdf_zoom_factor"),
            "HtmlZoomFactor" => array("Title" => "HTMLZOOMFACTOR", "Type" => "HIDE", "DocId" => "html_zoom_factor"),
            "PageTitleDisplay" => array("Title" => "PAGETITLEDISPLAY", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "Off", "TopOfFirstPage", "TopOfAllPages" ), "DocId" => "page_title_display"),
            "PageLayout" => array("Title" => "PAGELAYOUT", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "Table", "Form" ), "DocId" => "page_layout"),
            "ReportTemplate" => array("Title" => "REPORTTEMPLATE", "Type" => "TEMPLATELIST"),
            "PageWidthHTML" => array("Title" => "PAGEWIDTHHTML"),
            "PageOrientation" => array("Title" => "PAGEORIENTATION", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "Portrait", "Landscape"), "DocId" => "orientation_pdf"),
            "TopMargin" => array("Title" => "TOPMARGIN", "DocId" => "top_margin_pdf"),
            "BottomMargin" => array("Title" => "BOTTOMMARGIN", "DocId" => "bottom_margin_pdf"),
            "RightMargin" => array("Title" => "RIGHTMARGIN", "DocId" => "right_margin_pdf"),
            "LeftMargin" => array("Title" => "LEFTMARGIN", "DocId" => "left_margin_pdf"),
            "pdfFont" => array("Title" => "PDFFONT", "Type" => "FONTLIST", "DocId" => "font_pdf"),
            "OrderNumber" => array("Title" => "ORDERNUMBER", "DocId" => "order_number"),
            "ReportJustify" => array("Type" => "HIDE"),
            "BeforeGroupHeader" => array("Title" => "BEFOREGROUPHEADER", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("blankline", "solidline", "newpage"), "DocId" => "before_group_header"),
            "AfterGroupHeader" => array("Title" => "AFTERGROUPHEADER", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("blankline", "solidline", "newpage"), "DocId" => "after_group_header"),
            "BeforeGroupTrailer" => array("Title" => "BEFOREGROUPTRAILER", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("blankline", "solidline", "newpage"), "DocId" => "before_group_trailer"),
            "AfterGroupTrailer" => array("Title" => "AFTERGROUPTRAILER", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("blankline", "solidline", "newpage"), "DocId" => "after_group_trailer"),
            //"bodyDisplay" => array ( "Title" => "BODYDISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
            //"Values" => array("hide", "show") ),
            //"graphDisplay" => array ( "Title" => "GRAPHDISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
            //"Values" => array("hide", "show") ),
            "gridDisplay" => array("Title" => "GRIDDISPLAY", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "hide", "show"), "DocId" => "display_grid"),
            "gridSortable" => array("Title" => "GRIDSORTABLE", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "yes", "no"), "DocId" => "sortable_grid"),
            "gridSearchable" => array("Title" => "GRIDSEARCHABLE", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "yes", "no"), "DocId" => "searchable_grid_"),
            "gridPageable" => array("Title" => "GRIDPAGEABLE", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array(".DEFAULT", "yes", "no"), "DocId" => "display_grid"),
            "gridPageSize" => array("Title" => "GRIDPAGESIZE", "XlateOptions" => true, "DocId" => "grid_page_size"),
            "formBetweenRows" => array("Title" => "FORMBETWEENROWS", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("blankline", "solidline", "newpage"), "DocId" => "form_style_row_separator"),
            "GroupHeaderColumn" => array("Title" => "GROUPHEADERCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "group_header_column"),
            "GroupTrailerDisplayColumn" => array("Title" => "GROUPTRAILERDISPLAYCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "group_trailer_display_column"),
            "GroupTrailerValueColumn" => array("Title" => "GROUPTRAILERVALUECOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "group_trailer_value_column"),
            "LineNumber" => array("Title" => "LINENUMBER", "DocId" => "line_number"),
            "HeaderText" => array("Title" => "HEADERTEXT", "Type" => "TEXTBOXSMALL", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "header_text"),
            "FooterText" => array("Title" => "FOOTERTEXT", "Type" => "TEXTBOXSMALL", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "footer_text"),
            "ShowInPDF" => array("Title" => "SHOWINPDF", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("yes", "no"), "DocId" => "show_in_pdf"),
            "ShowInHTML" => array("Title" => "SHOWINHTML", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("yes", "no"), "DocId" => "show_in_html"),
            "ColumnStartPDF" => array("Title" => "COLUMNSTARTPDF", "DocId" => "column_start_pdf"),
            "ColumnWidthPDF" => array("Title" => "COLUMNWIDTHPDF", "DocId" => "column_width_pdf"),
            "ColumnWidthHTML" => array("Title" => "COLUMNWIDTHHTML", "DocId" => "column_width_html"),
            "column_title" => array("Title" => "COLUMN_TITLE"),
            "tooltip" => array("Type" => "HIDE", "Title" => "TOOLTIP"),
            "group_header_label" => array("Title" => "GROUP_HEADER_LABEL"),
            "group_trailer_label" => array("Title" => "GROUP_TRAILER_LABEL"),
            "group_header_label_xpos" => array("Title" => "GROUP_HEADER_LABEL_XPOS", "DocId" => "group_header_label_start_pdf"),
            "group_header_data_xpos" => array("Title" => "GROUP_HEADER_DATA_XPOS", "DocId" => "group_header_value_start_pdf"),
            "ReportJustify" => array("Type" => "HIDE"),
            "pdfFontSize" => array("Title" => "PDFFONTSIZE", "DocId" => "font_size_pdf"),
            "GridPosition" => array("Title" => "GRIDPOSITION", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "back", "front")),
            "XGridDisplay" => array("Title" => "XGRIDDISPLAY", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "none", "major", "all"), "DocId" => "x-grid_style"),
            "YGridDisplay" => array("Title" => "YGRIDDISPLAY", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "none", "major", "all"), "DocId" => "y-grid_style"),
            "PlotType" => array("Title" => "PLOTSTYLE", "Type" => "DROPDOWN",
                "Values" => array("BAR", "STACKEDBAR", "OVERLAYBAR", "LINE", "AREACHART", "SCATTER", "PIE", "PIE3D"), "DocId" => "plot_style"),
            "Title" => array("Title" => "TITLE", "DocId" => "title"),
            "CriteriaDefaults" => array("Title" => "CRITERIADEFAULTS", "HelpPage" => "criteria", "DocId" => "defaults"),
            "CriteriaList" => array("Title" => "CRITERIALIST", "HelpPage" => "criteria", "DocId" => "list_values"),
            "CriteriaType" => array("Title" => "CRITERIATYPE", "HelpPage" => "criteria", "Type" => "CRITERIARENDERS",
                //"Values" => array("TEXTFIELD", "LOOKUP", "DATE", "DATERANGE", "LIST", "SQLCOMMAND"),
                "XlateOptions" => true, "DocId" => "criteria_type"),
            "Use" => array("Title" => "USE", "HelpPage" => "criteria", "Type" => "DROPDOWN",
                "Values" => array("DATA-FILTER", "SHOW/HIDE", "SHOW/HIDE-and-GROUPBY")),
            "LinkToReport" => array("Type" => "TEXTFIELDREADONLY", "Title" => "LINKTOREPORT"),
            "LinkToReportItem" => array("Type" => "TEXTFIELDREADONLY", "Title" => "LINKTOREPORTITEM"),
            "CriteriaDisplay" => array("Title" => "CRITERIADISPLAY", "Type" => "CRITERIAWIDGETS", "HelpPage" => "criteria",
                "XlateOptions" => true,
                //"Values" => array("NOINPUT", "TEXTFIELD", "DROPDOWN", "MULTI", "SELECT2SINGLE", "SELECT2MULTIPLE", "CHECKBOX", "RADIO"), "DocId" => "criteria_display"),
                "DocId" => "criteria_display"),
            "ExpandDisplay" => array("Title" => "EXPANDDISPLAY", "Type" => "DROPDOWN", "HelpPage" => "criteria", "XlateOptions" => true,
                "Values" => array("NOINPUT", "TEXTFIELD", "DROPDOWN", "MULTI", "SELECT2SINGLE", "SELECT2MULTIPLE", "CHECKBOX", "RADIO"), "DocId" => "expand_display"),
            "DatabaseType" => array("Title" => "DATABASETYPE", "Type" => "DROPDOWN",
                "Values" => array("informix", "mysql", "sqlite-2", "sqlite-3", "none")),
            "justify" => array("Title" => "JUSTIFY", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("left", "center", "right"), "DocId" => "justification"),
            "column_display" => array("Title" => "COLUMN_DISPLAY", "Type" => "DROPDOWN", "XlateOptions" => true,
                "Values" => array("show", "hide"), "DocId" => "show_or_hide"),
            "TitleFont" => array("Title" => "TITLEFONT", "Type" => "DROPDOWN",
                "Values" => $fontlist),
            "TitleFontStyle" => array("Title" => "TITLEFONTSTYLE", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic")),
            "XTitleFont" => array("Title" => "XTITLEFONT", "Type" => "DROPDOWN",
                "Values" => $fontlist),
            "YTitleFont" => array("Title" => "YTITLEFONT", "Type" => "DROPDOWN",
                "Values" => $fontlist),
            "XAxisFont" => array("Title" => "XAXISFONT", "Type" => "DROPDOWN",
                "Values" => $fontlist),
            "YAxisFont" => array("Title" => "YAXISFONT", "Type" => "DROPDOWN",
                "Values" => $fontlist),
            "XAxisFontStyle" => array("Title" => "XAXISFONTSTYLE", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic")),
            "YAxisFontStyle" => array("Title" => "YAXISFONTSTYLE", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic")),
            "XTitleFontStyle" => array("Title" => "XTITLEFONTSTYLE", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic")),
            "YTitleFontStyle" => array("Title" => "YTITLEFONTSTYLE", "Type" => "DROPDOWN",
                "Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic")),
        );

        if ($this->query) {
            $this->query->applyPlugins("design-options", array("query" => &$this->query, "field_display" => &$this->field_display));
        }

        // If using pchart engine, then certain graph options are not available
        // and should therefore be hidden from design pane
        //$this->field_display["LineColor"]["Type"] = "HIDE";

        if (!ReporticoApp::isSetConfig("graph_engine") || ReporticoApp::getConfig('graph_engine') == "PCHART") {
            $this->field_display["GraphColor"]["Type"] = "HIDE";
            $this->field_display["XGridColor"]["Type"] = "HIDE";
            $this->field_display["YGridColor"]["Type"] = "HIDE";
            //$this->field_display["XGridDisplay"]["Type"] = "HIDE";
            //$this->field_display["YGridDisplay"]["Type"] = "HIDE";
            $this->field_display["GridPosition"]["Type"] = "HIDE";
            $this->field_display["FillColor"]["Type"] = "HIDE";
            $this->field_display["TitleColor"]["Type"] = "HIDE";
            $this->field_display["TitleFont"]["Type"] = "HIDE";
            $this->field_display["XTitleFont"]["Type"] = "HIDE";
            $this->field_display["XTitleColor"]["Type"] = "HIDE";
            $this->field_display["YTitleFont"]["Type"] = "HIDE";
            $this->field_display["YTitleColor"]["Type"] = "HIDE";
            $this->field_display["XTitleFontSize"]["Type"] = "HIDE";
            $this->field_display["XTitleFontStyle"]["Type"] = "HIDE";
            $this->field_display["YTitleFontSize"]["Type"] = "HIDE";
            $this->field_display["YTitleFontStyle"]["Type"] = "HIDE";
            $this->field_display["TitleFontSize"]["Type"] = "HIDE";
            $this->field_display["TitleFontStyle"]["Type"] = "HIDE";
            $this->field_display["XAxisColor"]["Type"] = "HIDE";
            $this->field_display["YAxisColor"]["Type"] = "HIDE";
            $this->field_display["XAxisFont"]["Type"] = "HIDE";
            $this->field_display["YAxisFont"]["Type"] = "HIDE";
            $this->field_display["XAxisFontColor"]["Type"] = "HIDE";
            $this->field_display["YAxisFontColor"]["Type"] = "HIDE";
            $this->field_display["XAxisFontSize"]["Type"] = "HIDE";
            $this->field_display["YAxisFontSize"]["Type"] = "HIDE";
            $this->field_display["XAxisFontStyle"]["Type"] = "HIDE";
            $this->field_display["YAxisFontStyle"]["Type"] = "HIDE";
            $this->field_display["XTitleFontStyle"]["Type"] = "HIDE";
            $this->field_display["YTitleFontStyle"]["Type"] = "HIDE";
            $this->field_display["XTickInterval"]["Type"] = "HIDE";
            $this->field_display["YTickInterval"]["Type"] = "HIDE";
            $this->field_display["YTickLabelInterval"]["Type"] = "HIDE";
        }

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'cdata');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);

        // 1 = single field, 2 = array field, 3 = record container
        $this->xmltag_type = array('Assignment' => 2,
            'QueryColumn' => 2,
            'OrderColumn' => 2,
            'CriteriaItem' => 2,
            'GroupHeader' => 2,
            'GroupTrailer' => 2,
            'PreSQL' => 2,
            'PageHeader' => 2,
            'Graph' => 2,
            'Plot' => 2,
            'PageFooter' => 2,
            'DisplayOrder' => 2,
            'Group' => 2,
            'CriteriaLink' => 2,
            'QueryColumns' => 3,
            'Format' => 3,
            'Datasource' => 3,
            'SourceConnection' => 3,
            'EntryForm' => 3,
            'CogQuery' => 3,
            'ReportQuery' => 3,
            'CogModule' => 3,
            'Report' => 3,
            'Query' => 3,
            'SQL' => 3,
            'Criteria' => 3,
            'Assignments' => 3,
            'GroupHeaders' => 3,
            'GroupTrailers' => 3,
            'OrderColumns' => 3,
            'PageHeaders' => 3,
            'PageFooters' => 3,
            'PreSQLS' => 3,
            'DisplayOrders' => 3,
            'Output' => 3,
            'Graphs' => 3,
            'Plots' => 3,
            'Groups' => 3,
            'CriteriaLinks' => 3,
            'XXXX' => 1);
        $this->ends_record = array('book' => true);

        $x = false;
        if ( $debug ) {
            echo "LOAD FILE $filename STRING ".strlen($xmlstring)."<BR>";
        }
        if ($xmlstring) {
            if ( $debug ) {
                echo "STRING " . strlen($xmlstring) . "<BR> " . htmlspecialchars($xmlstring);
            }
            $x = &$xmlstring;
        } else {
            if ($filename) {
                if (!preg_match("/\.xml$/", $filename)) {
                    $filename = $filename . ".xml";
                }
                if ( $debug ) {
                    echo "LOAD $filename<BR>";
                    echo "<PRE>";
                    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                    echo "</PRE>";
                    echo "XML $filename<BR>";
                }
                if ($this->query) {
                    $readfile = $this->query->projects_folder . "/" . ReporticoApp::getConfig("project") . "/" . $filename;
                    $adminfile = $this->query->admin_projects_folder . "/admin/" . $filename;
                } else {
                    $readfile = $filename;
                    $adminfile = false;
                }

                if ( !$no_authentication )
                if ($query) {
                    if (!Authenticator::allowed("admin")) {
                        if (ReporticoApp::getConfig("project") == "admin") {
                            $readfile = false;
                        }
                        $adminfile = false;
                    }
                }

                if ($readfile && !is_file($readfile)) {
                    ReporticoUtility::findFileToInclude($readfile, $readfile);
                }

                $use_admin_xml = false;
                if ($readfile && is_file($readfile)) {
                    $readfile = $readfile;
                } else {
                    if (!is_file($adminfile)) {
                        ReporticoUtility::findFileToInclude($adminfile, $readfile);
                        if (is_file($readfile)) {
                            $readfile = $readfile;
                        }

                    } else {
                        $use_admin_xml = true;
                        $readfile = $adminfile;
                    }
                }

                if ($readfile) {
                    //if ( $use_admin_xml )
                        //Authenticator::flag("admin-report-selected");
                    if ( !file_exists($readfile) ) {
                        ReporticoApp::backtrace();
                    }
                    $x = join("", file($readfile));
                } else {
                    trigger_error("Report Definition File  " . $this->query->reports_path . "/" . $filename . " Not Found", E_USER_ERROR);
                }

            }
        }

        if ($x) {
            xml_parse($this->parser, $x);
            xml_parser_free($this->parser);
        }

        //var_dump($this->data);
    }

    public function startElement($p, $element, $attributes)
    {
        //$element = strtolower($element);

        $this->gotdata = false;
        $this->value = "";

        if (!array_key_exists($element, $this->xmltag_type)) {
            $tp = 1;
        } else {
            $tp = $this->xmltag_type[$element];
        }

        switch ($tp) {
            case 1:
                $this->current_element[$element] = "";
                break;

            case 2:
                $ar = array();
                $this->current_element[] = &$ar;
                $this->element_count = array_push($this->element_stack,
                    count($this->current_element) - 1);
                $this->current_element = &$ar;
                break;

            case 3:
                $ar = array();
                $this->current_element[$element] = &$ar;
                $this->element_count = array_push($this->element_stack,
                    $element);
                $this->current_element = &$ar;
                break;

        }

    }

    public function endElement($p, $element)
    {
        //$element = strtolower($element);
        if (!array_key_exists($element, $this->xmltag_type)) {
            $tp = 1;
        } else {
            $tp = $this->xmltag_type[$element];
        }

        if ($tp == 1) {
            $this->current_element[$element] = $this->value;

            if ($element == $this->search_tag) {
                $this->search_response = $this->value;
            }
        } else {
            array_pop($this->element_stack);
            $this->element_count--;

            $ct = 0;
            $this->current_element = &$this->data;
            foreach ($this->element_stack as $v) {
                $this->current_element = &$this->current_element[$v];
            }
        }

    }

    public function cdata($p, $text)
    {
        $this->value .= $text;
        $this->gotdata = true;
    }

    public function &getArrayElement(&$in_arr, $element)
    {
        $retval = false;
        if (array_key_exists($element, $in_arr)) {
            return $in_arr[$element];
        } else {
            return $retval;
        }

    }

    public function countArrayElements(&$ar)
    {
        $ct = 0;
        foreach ($ar as $k => $el) {
            if (is_array($el)) {
                $ct = $ct + $this->countArrayElements($el);
            } else {
                $ct++;
            }
        }
        return $ct;
    }

    public function &analyseFormItem($tag)
    {
        $anal = array();
        $qr = false;
        $cl = false;
        $cr = false;
        $grph = false;
        $gr = false;
        $grn = false;
        $grno = false;
        $nm = false;
        $cn = false;
        $item = false;
        $actar = false;

        $anal["name"] = $tag;

        // To analyse the item, chop the item into 4 character fields
        // and then analyse each item
        $ptr = 0;
        $len = strlen($tag);
        //echo "analyse $tag<br>";
        //echo "Bit  = ";
        $last = false;
        while ($ptr < $len) {
            $bit = substr($tag, $ptr, 4);
            //echo $bit."/";
            $ptr += 4;

            if (is_numeric($bit) && (int) $bit == 0) {
                $bit = "ZERO";
            }

            switch ($bit) {
                case "main":
                    break;

                case "outp":
                    $item = &$this->query;
                    $qr = &$this->query;
                    break;

                case "quer":
                    $item = &$this->query;
                    $qr = &$this->query;
                    break;

                case "data":
                    $item = &$this->query->ds;
                    $action = $bit;
                    break;

                case "qury":
                    if (!$cr) {
                        $qr = &$this->query;
                    } else {
                        $qr = &$cr->lookup_query;
                    }

                    $item = &$qr;
                    break;

                case "conn":
                    $item = &$this->query->datasource;
                    $action = $bit;
                    break;

                case "crit":
                    $item = &$this->query;
                    $action = $bit;
                    break;

                case "gtrl":
                case "ghdr":
                case "grps":
                case "pgft":
                case "clnk":
                case "plot":
                case "grph":
                case "pghd":
                case "assg":
                case "sqlt":
                case "form":
                case "dord":
                case "psql":
                case "ords":
                case "qcol":
                    $action = $bit;
                    break;

                case "ZERO":
                    $bit = 0;

                default:
                    if (is_numeric($bit)) {
                        $bit = (int) $bit;
                        if ($last == "crit") {
                            $ct = 0;
                            foreach ($qr->lookup_queries as $k => $v) {
                                if ($ct == $bit) {
                                    $cr = &$qr->lookup_queries[$k];
                                    break;
                                }
                                $ct++;
                            }
                            $item = &$cr;
                        }
                        if ($last == "grph") {
                            $ct = 0;
                            foreach ($qr->graphs as $k => $v) {
                                if ($ct == $bit) {
                                    $grph = &$qr->graphs[$k];
                                    break;
                                }
                                $ct++;
                            }
                            $item = &$cl;
                        }
                        if ($last == "qcol") {
                            $ct = 0;
                            foreach ($qr->columns as $k => $v) {
                                if ($ct == $bit) {
                                    $cl = &$qr->columns[$k];
                                    $cn = $cl->query_name;
                                    //$cn = $k;
                                    break;
                                }
                                $ct++;
                            }
                            $item = &$cl;
                        }

                        if ($last == "grps") {
                            $ct = 0;
                            foreach ($qr->groups as $k => $v) {
                                if ($ct == $bit) {
                                    $gr = &$qr->groups[$k];
                                    $grn = $v->group_name;
                                    $grno = $k;
                                    break;
                                }
                                $ct++;
                            }
                            $item = &$gr;
                        }
                        if ($last == "clnk") {
                            $item = &$qr->criteria_links[$bit];
                        }

                        if ($last == "ords") {
                            //$item =& $qr->order_set["itemno"][$bit];
                        }
                        if ($last == "dord") {
                            $item = &$qr->display_order_set["itemno"][$bit];
                        }

                        if ($last == "assg") {
                            $item = &$qr->assignment[$bit];
                        }

                        if ($last == "pgft") {
                            $item = &$qr->pageFooters[$bit];
                        }

                        if ($last == "plot") {
                            //echo $qr->graphs;
                            //var_dump ($qr->graphs);
                            //echo $qr->graphs->plot;
                            //$item =& $qr->graphs->plot[$bit];
                        }
                        if ($last == "grph") {
                            $item = &$qr->graphs[$bit];
                        }

                        if ($last == "pghd") {
                            $item = &$qr->pageHeaders[$bit];
                        }

                        if ($last == "ghdr") {
                            $item = &$gr->headers[$bit];
                        }

                        if ($last == "gtrl") {
                            $item = &$gr->trailers[$bit];
                        }

                        $nm = (int) $bit;
                    }
            }

            $last = $bit;
        }

        $anal["graph"] = &$grph;
        $anal["quer"] = &$qr;
        $anal["crit"] = &$cr;
        $anal["column"] = &$cl;
        $anal["colname"] = &$cn;
        $anal["item"] = &$item;
        $anal["action"] = &$action;
        $anal["group"] = &$gr;
        $anal["groupname"] = &$grn;
        $anal["groupno"] = &$grno;
        $anal["number"] = $nm;
        $anal["array"] = &$actar;
        return $anal;

    }

    public function linkInReportFields($link_or_import, $match, $action)
    {
        $ret = false;

        // Fetch user select report to link to
        $match_key = "/^reportlink_(" . $match . ")/";
        $updates = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                $updates[$matches[1]] = stripslashes($v);
                $this->query->reportlink_report = $v;
            }
        }

        // Fetch user select report item ( criteria, assignment etc )  to link to
        $match_key = "/^reportlinkitem_(" . $match . ")/";
        $updates = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                $updates[$matches[1]] = stripslashes($v);
                if (strval($v) == "0") {
                    $v = "_ZERO";
                }

                $this->query->reportlink_report_item = $v;
            }
        }

        // Fetch user selection of whether to import or link
        $match_key = "/^linkorimport_(" . $match . ")/";
        $updates = array();

        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                $updates[$matches[1]] = stripslashes($v);
                $this->query->reportlink_or_import = $v;
                break;
            }
        }
        if ($action != "REPORTLINKITEM" && $action != "REPORTIMPORTITEM") {
            return;
        }

        if (!$this->query->reportlink_report_item) {
            return;
        }

        if ($this->query->reportlink_report_item == "_ZERO") {
            $this->query->reportlink_report_item = 0;
        }

        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "form":
                // Delete not applicable to "Format" option
                break;

            case "assg":
                $q = ReporticoUtility::loadExistingReport($this->query->reportlink_report, $this->query->projects_folder);
                foreach ($q->assignment as $k => $v) {
                    if (strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k) {
                        $found = false;
                        foreach ($this->query->columns as $querycol) {
                            if ($querycol->query_name == $v->query_name) {
                                $found = true;
                            }
                        }

                        if (!$found) {
                            $this->query->createQueryColumn($v->query_name, "", "", "", "",
                                '####.###', false);
                        }

                        $this->query->assignment[] = $v;
                    }
                }
                break;

            case "pghd":
                $q = ReporticoUtility::loadExistingReport($this->query->reportlink_report, $this->query->projects_folder);
                foreach ($q->pageHeaders as $k => $v) {
                    if (strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k) {
                        $this->query->pageHeaders[] = $v;
                    }

                }
                break;

            case "grph":
                $qr = &$anal["quer"];
                $qr->createGraph();
                break;

            case "plot":
                $qr = &$anal["graph"];
                $qr->createPlot("");
                break;

            case "pgft":
                $q = ReporticoUtility::loadExistingReport($this->query->reportlink_report, $this->query->projects_folder);
                foreach ($q->pageFooters as $k => $v) {
                    if (strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k) {
                        $this->query->pageFooters[] = $v;
                    }

                }
                break;

            case "clnk":
                $cr = &$anal["crit"];
                $this->query->setCriteriaLink(
                    $cr->query_name, $cr->query_name,
                    ReporticoLang::templateXlate("ENTERCLAUSE"));
                break;

            case "pgft":
                array_splice($this->query->pageFooters, $anal["number"], 1);
                break;

            case "grps":
                $qr = &$anal["quer"];
                $ak = array_keys($this->query->columns);
                $qr->createGroup($this->query->columns[0]->query_name);
                break;

            case "psql":
                $qr = &$anal["quer"];
                $qr->addPreSql("-- " . ReporticoLang::templateXlate("ENTERSQL"));
                break;

            case "crit":
                $q = ReporticoUtility::loadExistingReport($this->query->reportlink_report, $this->query->projects_folder);
                foreach ($q->lookup_queries as $k => $v) {
                    if ($this->query->reportlink_report_item == "ALLITEMS" ||
                        $this->query->reportlink_report_item == $v->query_name) {
                        $qu = new Reportico();
                        $qu->manager = $this->query->manager;

                        $this->query->setCriteriaLookup($v->query_name, $qu, "", "");
                        $lastitem = count($this->query->lookup_queries) - 1;
                        if ($this->query->reportlink_or_import == "import") {
                            $this->query->lookup_queries[$v->query_name] = $q->lookup_queries[$k];
                        } else {
                            $this->query->lookup_queries[$v->query_name]->link_to_report = $this->query->reportlink_report;
                            $this->query->lookup_queries[$v->query_name]->link_to_report_item = $v->query_name;
                        }
                    }

                }
                break;

            case "qcol":
                $qr = &$anal["quer"];
                $qr->createCriteriaColumn("NewColumn", "", "",
                    "char", 0, "###", false);
                break;

            case "ghdr":
                $updateitem = &$anal["item"];
                $gn = $anal["groupname"];
                if (reset($this->query->columns)) {
                    $cn = current($this->query->columns);
                    $this->query->createGroupHeader($gn, $cn->query_name, "");
                }
                break;

            case "gtrl":
                $updateitem = &$anal["item"];
                $gn = $anal["groupname"];
                if (reset($this->query->columns)) {
                    $cn = current($this->query->columns);
                    $this->query->createGroupTrailer
                    ($gn, $cn->query_name, $cn->query_name);
                }
                break;

            case "conn":
                // Delete not applicable to Connection action
                break;
        }

        return $ret;
    }

    public function addMaintainFields($match)
    {
        $ret = false;

        $match_key = "/^set_" . $match . "_(.*)/";
        $updates = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                $updates[$matches[1]] = stripslashes($v);
            }
        }
        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "form":
                // Delete not applicable to "Format" option
                break;

            case "assg":
                $qr = &$anal["quer"];
                $qr->addAssignment("Column", "Expression", "");
                break;

            case "pghd":
                $qr = &$anal["quer"];
                $qr->createPageHeader("Name", 1, "Header Text");
                break;

            case "grph":
                $qr = &$anal["quer"];
                $qr->createGraph();
                break;

            case "plot":
                $qr = &$anal["graph"];
                $qr->createPlot("");
                break;

            case "pgft":
                $qr = &$anal["quer"];
                $qr->createPageFooter("Name", 1, "Header Text");
                break;

            case "clnk":
                $cr = &$anal["crit"];
                $this->query->setCriteriaLink(
                    $cr->query_name, $cr->query_name,
                    ReporticoLang::templateXlate("ENTERCLAUSE"));
                break;

            case "pgft":
                array_splice($this->query->pageFooters, $anal["number"], 1);
                break;

            case "grps":
                $qr = &$anal["quer"];
                $ak = array_keys($this->query->columns);
                $qr->createGroup($this->query->columns[0]->query_name);
                break;

            case "psql":
                $qr = &$anal["quer"];
                $qr->addPreSql("-- " . ReporticoLang::templateXlate("ENTERSQL"));
                break;

            case "crit":
                $qr = &$anal["quer"];
                $qu = new Reportico();
                $qr->setCriteriaLookup(
                    "CriteriaName", $qu, "", "");
                break;

            case "qcol":
                $qr = &$anal["quer"];
                $qr->createCriteriaColumn("NewColumn", "", "",
                    "char", 0, "###", false);
                break;

            case "ghdr":
                $updateitem = &$anal["item"];
                $gn = $anal["groupname"];
                if (reset($this->query->columns)) {
                    $cn = current($this->query->columns);
                    $this->query->createGroupHeader($gn, $cn->query_name);
                }
                break;

            case "gtrl":
                $updateitem = &$anal["item"];
                $gn = $anal["groupname"];
                if (reset($this->query->columns)) {
                    $cn = current($this->query->columns);
                    $this->query->createGroupTrailer
                    ($gn, $cn->query_name, $cn->query_name);
                }
                break;

            case "conn":
                // Delete not applicable to Connection action
                break;
        }

        return $ret;
    }

    public function moveupMaintainFields($match)
    {
        $ret = false;
        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "form":
                // Delete not applicable to "Format" option
                break;

            case "assg":
                $updateitem = &$anal["item"];
                $qr = &$anal["quer"];
                $cut = array_splice($qr->assignment, $anal["number"], 1);
                array_splice($qr->assignment, $anal["number"] - 1, 0, $cut);
                break;

            case "dord":
                $updateitem = &$anal["item"];
                $cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
                $anal["quer"]->setColumnOrder($cl, $anal["number"], true);
                break;

            case "pgft":
                array_splice($this->query->pageFooters, $anal["number"], 1);
                break;

            case "grps":
                $cut = array_splice($this->query->groups, $anal["number"], 1);
                array_splice($this->query->groups, $anal["number"] - 1, 0, $cut);
                break;

            case "clnk":
                $qr = &$anal["crit"]->lookup_query;
                array_splice($qr->criteria_links, $anal["number"], 1);
                break;

            case "psql":
                $cut = array_splice($this->query->pre_sql, $anal["number"], 1);
                array_splice($this->query->pre_sql, $anal["number"] - 1, 0, $cut);
                break;

            case "crit":
                $cut = array_splice($this->query->lookup_queries, $anal["number"], 1);
                array_splice($this->query->lookup_queries, $anal["number"] - 1, 0, $cut);
                break;

            case "qcol":
                $anal["quer"]->removeColumn($anal["colname"]);
                break;

            case "ghdr":
                $cut = array_splice($this->query->groups[$anal["groupno"]]->headers,
                    $anal["number"], 1);
                array_splice($this->query->groups[$anal["groupno"]]->headers,
                    $anal["number"] - 1, 0, $cut);
                break;

            case "gtrl":
                $cut = array_splice($this->query->groups[$anal["groupno"]]->trailers,
                    $anal["number"], 1);
                array_splice($this->query->groups[$anal["groupno"]]->trailers,
                    $anal["number"] - 1, 0, $cut);
                break;

            case "ords":
                $qr = &$anal["quer"];
                array_splice($qr->order_set, $anal["number"], 1);
                break;

            case "grph":
                array_splice($this->query->graphs, $anal["number"], 1);
                break;

            case "plot":
                array_splice($anal["graph"]->plot, $anal["number"], 1);
                break;

            case "pghd":
                array_splice($this->query->pageHeaders, $anal["number"], 1);
                break;

            case "conn":
                // Delete not applicable to Connection action
                break;
        }

        return $ret;
    }

    public function movedownMaintainFields($match)
    {
        $ret = false;
        $match_key = "/^set_" . $match . "_(.*)/";

        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "form":
                // Delete not applicable to "Format" option
                break;

            case "assg":
                $updateitem = &$anal["item"];
                $qr = &$anal["quer"];
                $cut = array_splice($qr->assignment, $anal["number"], 1);
                array_splice($qr->assignment, $anal["number"] + 1, 0, $cut);
                break;

            case "dord":
                $updateitem = &$anal["item"];
                $cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
                $anal["quer"]->setColumnOrder($cl, $anal["number"] + 2, false);
                break;

            case "pgft":
                break;

            case "grps":
                $cut = array_splice($this->query->groups, $anal["number"], 1);
                array_splice($this->query->groups, $anal["number"] + 1, 0, $cut);
                break;

            case "clnk":
                $qr = &$anal["crit"]->lookup_query;
                array_splice($qr->criteria_links, $anal["number"], 1);
                break;

            case "psql":
                array_splice($this->query->pre_sql, $anal["number"], 1);
                break;

            case "crit":
                $cut = array_splice($this->query->lookup_queries, $anal["number"], 1);
                array_splice($this->query->lookup_queries, $anal["number"] + 1, 0, $cut);
                break;

            case "qcol":
                $anal["quer"]->removeColumn($anal["colname"]);
                break;

            case "ghdr":
                $cut = array_splice($this->query->groups[$anal["groupno"]]->headers,
                    $anal["number"], 1);
                array_splice($this->query->groups[$anal["groupno"]]->headers,
                    $anal["number"] + 1, 0, $cut);
                break;

            case "gtrl":
                $cut = array_splice($this->query->groups[$anal["groupno"]]->trailers,
                    $anal["number"], 1);
                array_splice($this->query->groups[$anal["groupno"]]->trailers,
                    $anal["number"] + 1, 0, $cut);
                break;

            case "ords":
                $qr = &$anal["quer"];
                array_splice($qr->order_set, $anal["number"], 1);
                break;

            case "grph":
                array_splice($this->query->graphs, $anal["number"], 1);
                break;

            case "plot":
                array_splice($anal["graph"]->plot, $anal["number"], 1);
                break;

            case "pghd":
                array_splice($this->query->pageHeaders, $anal["number"], 1);
                break;

            case "conn":
                // Delete not applicable to Connection action
                break;
        }

        return $ret;
    }

    public function deleteMaintainFields($match)
    {
        $ret = false;
        $match_key = "/^set_" . $match . "_(.*)/";
        $updates = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                $updates[$matches[1]] = stripslashes($v);
            }
        }

        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "form":
                // Delete not applicable to "Format" option
                break;

            case "assg":
                $updateitem = &$anal["item"];
                $qr = &$anal["quer"];
                array_splice($qr->assignment, $anal["number"], 1);
                break;

            case "pgft":
                array_splice($this->query->pageFooters, $anal["number"], 1);
                break;

            case "grps":
                array_splice($this->query->groups, $anal["number"], 1);
                break;

            case "clnk":
                $qr = &$anal["crit"]->lookup_query;
                array_splice($qr->criteria_links, $anal["number"], 1);
                break;

            case "psql":
                array_splice($this->query->pre_sql, $anal["number"], 1);
                break;

            case "crit":
                array_splice($this->query->lookup_queries, $anal["number"], 1);
                break;

            case "qcol":
                $anal["quer"]->removeColumn($anal["colname"]);
                break;

            case "ghdr":
                $anal["quer"]->deleteGroupHeaderByNumber
                ($anal["groupname"], $anal["number"]);
                break;

            case "gtrl":
                $updateitem = &$anal["item"];
                $anal["quer"]->deleteGroupTrailerByNumber
                ($anal["groupname"], $anal["number"]);
                break;

            case "ords":
                $qr = &$anal["quer"];
                array_splice($qr->order_set, $anal["number"], 1);
                break;

            case "grph":
                array_splice($this->query->graphs, $anal["number"], 1);
                break;

            case "plot":
                array_splice($anal["graph"]->plot, $anal["number"], 1);
                break;

            case "pghd":
                array_splice($this->query->pageHeaders, $anal["number"], 1);
                break;

            case "conn":
                // Delete not applicable to Connection action
                break;
        }

        return $ret;
    }

    public function changeArrayKeyname(&$in_array, $in_number, $in_key)
    {
        $nm = 0;
        foreach ($in_array as $k => $v) {
            if ($nm == $in_number) {
                $in_array[$in_key] = $v;
                array_splice($in_array, $nm, 1, $el);
                $in_array[$in_key] = $v;
                break;
            }
            $nm++;
        }
    }

    public function validateMaintainFields(&$updates)
    {
        $invalid = false;
        $current_key = false;
        $current_value = false;
        foreach ($updates as $k => $v) {
            $current_key = $k;
            $current_value = $v;
            if (isset($this->field_display[$k]) && isset($this->field_display[$k]["Validate"])) {
                if (!$v) {
                    continue;
                }

                // Must be a number
                if ($this->field_display[$k]["Validate"] == "NUMBER") {
                    if (!preg_match("/^[0-9]+$/", $v)) {
                        $invalid = true;
                        break;
                    }
                }

                // HTMLCOLOR must be in the form #hhhhhh or hhhh
                if ($this->field_display[$k]["Validate"] == "HTMLCOLOR") {
                    if (preg_match("/^[0-9a-fA-F]{6}$/", $v)) {
                        $updates[$k] = "#" . $v;
                    } else if (!preg_match("/^#[0-9a-fA-F]{6}$/", $v)) {
                        $invalid = true;
                        break;
                    }

                }

                // CSS1SIZE must be in the form xpx or x%
                if ($this->field_display[$k]["Validate"] == "CSSFONTSIZE") {
                    if (preg_match("/^[0-9]+$/", $v)) {
                        $updates[$k] = $v . "pt";
                    } else if (!preg_match("/^[0-9]+pt$/", $v)) {
                        $invalid = true;
                        break;
                    }
                }

                // CSS1SIZE must be in the form xpx or x%
                if ($this->field_display[$k]["Validate"] == "CSS1SIZE") {
                    if (!preg_match("/^[0-9]+px$/", $v) &&
                        !preg_match("/^[0-9]+cm$/", $v) &&
                        !preg_match("/^[0-9]+mm$/", $v) &&
                        !preg_match("/^[0-9]+em$/", $v) &&
                        !preg_match("/^[0-9]+%$/", $v) &&
                        !preg_match("/^[0-9]+$/", $v)) {
                        $invalid = true;
                        break;
                    }
                }

                // CSS4SIZE must be in the form of 4 items of xpx or x%
                if ($this->field_display[$k]["Validate"] == "CSS4SIZE") {
                    $arr = explode(" ", $v);
                    if (count($arr) < 1 && count($arr) > 4) {
                        $invalid = true;
                        break;
                    } else {
                        foreach ($arr as $k1 => $v1) {
                            if (!preg_match("/^[0-9]+px$/", $v1) &&
                                !preg_match("/^[0-9]+cm$/", $v1) &&
                                !preg_match("/^[0-9]+mm$/", $v1) &&
                                !preg_match("/^[0-9]+em$/", $v1) &&
                                !preg_match("/^[0-9]+%$/", $v1) &&
                                !preg_match("/^[0-9]+$/", $v1)) {
                                $invalid = true;
                                break;
                            }
                        }
                        if ($invalid) {
                            break;
                        }

                    }
                }
            }
        }

        if ($invalid) {
            $updates[$k] = false;
            trigger_error(ReporticoLang::templateXlate("INVALIDENTRY") . "'" . $current_value . "' " . ReporticoLang::templateXlate("FORFIELD") . " " . ReporticoLang::templateXlate($this->field_display[$current_key]["Title"]) . " - " . ReporticoLang::templateXlate($this->field_display[$current_key]["Validate"]), E_USER_NOTICE);

        }
    }

    public function updateMaintainFields($match)
    {
        $ret = false;
        $match_key = "/^set_" . $match . "_(.*)/";
        $updates = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match_key, $k, $matches)) {
                if ($k == "set_mainquerform_PreExecuteCode") {
                    $updates[$matches[1]] = $v;

                } else {
                    $updates[$matches[1]] = $v;

                }
            }
        }

        // Validate user entry
        $this->validateMaintainFields($updates);

        $anal = $this->analyseFormItem($match);

        // Based on results of analysis, decide what element we are updating ( column, query,
        // datasource etc )
        switch ($anal["action"]) {
            case "sqlt":
                $qr = &$anal["quer"];
                $maintain_sql = $updates["QuerySql"];
                $sql = $updates["QuerySql"];
                if (Authenticator::login()) {
                    $p = new SqlParser($sql);
                    if ($p->parse()) {
                        $p->importIntoQuery($qr);
                        if ($this->query->datasource->connect()) {
                            $p->testQuery($this->query, $sql);
                        }

                    }
                }
                $updateitem = &$anal["item"];
                if (isset($updates["SQLRaw"])) {
                    $updateitem->sql_raw = $qr->sql_raw;
                }

                break;

            case "form":
                $updateitem = &$anal["item"];
                foreach ($updates as $k => $v) {
                    $updateitem->setAttribute($k, $v);
                }
                break;

            case "assg":
                $styletxt = "";
                if ($updates["AssignStyleFgColor"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'color', '" . $updates["AssignStyleFgColor"] . "');";
                }

                if ($updates["AssignStyleBgColor"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'background-color', '" . $updates["AssignStyleBgColor"] . "');";
                }

                if (isset($updates["AssignStyleFontName"]) && $updates["AssignStyleFontName"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'font-family', '" . $updates["AssignStyleFontName"] . "');";
                }

                if ($updates["AssignStyleFontSize"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'font-size', '" . $updates["AssignStyleFontSize"] . "');";
                }

                if ($updates["AssignStyleWidth"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'width', '" . $updates["AssignStyleWidth"] . "');";
                }

                if ($updates["AssignStyleFontStyle"] && $updates["AssignStyleFontStyle"] != "NONE") {
                    $stylevalue = "none";
                    if ($updates["AssignStyleFontStyle"] == "BOLD" || $updates["AssignStyleFontStyle"] == "BOLDANDITALIC") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'font-weight', 'bold');";
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "ITALIC" || $updates["AssignStyleFontStyle"] == "BOLDANDITALIC") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'font-style', 'italic');";
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "NORMAL") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'font-style', 'normal');";
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "UNDERLINE") {
                        if ($updates["AssignStyleFontStyle"] == "UNDERLINE") {
                            if ($updates["AssignStyleFontStyle"]) {
                                $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'text-decoration', 'underline');";
                            }
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "OVERLINE") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'text-decoration', 'overline');";
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "BLINK") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'text-decoration', 'blink');";
                        }
                    }

                    if ($updates["AssignStyleFontStyle"] == "STRIKETHROUGH") {
                        if ($updates["AssignStyleFontStyle"]) {
                            $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'text-decoration', 'line-through');";
                        }
                    }

                }

                if (!$updates["AssignStyleBorderStyle"] || $updates["AssignStyleBorderStyle"] == "NOBORDER") {
                    if ($updates["AssignStyleBorderSize"] || $updates["AssignStyleBorderColor"]) {
                        trigger_error(ReporticoLang::templateXlate("SETBORDERSTYLE"), E_USER_ERROR);
                    }

                } else {
                    $stylevalue = "none";
                    if ($updates["AssignStyleBorderStyle"] == "SOLIDLINE") {
                        $stylevalue = "solid";
                    }

                    if ($updates["AssignStyleBorderStyle"] == "DASHED") {
                        $stylevalue = "dashed";
                    }

                    if ($updates["AssignStyleBorderStyle"] == "DOTTED") {
                        $stylevalue = "dotted";
                    }

                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'border-style', '" . $stylevalue . "');";
                    if ($updates["AssignStyleBorderSize"]) {
                        $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'border-width', '" . $updates["AssignStyleBorderSize"] . "');";
                    }

                    if ($updates["AssignStyleBorderColor"]) {
                        $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'border-color', '" . $updates["AssignStyleBorderColor"] . "');";
                    }

                }

                if ($updates["AssignStyleMargin"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'margin', '" . $updates["AssignStyleMargin"] . "');";
                    if ($updates["AssignStyleLocType"] == "PAGE" && !$updates["AssignStyleWidth"]) {
                        ReporticoApp::handleDebug(ReporticoLang::templateXlate("PAGEMARGINWITHWIDTH"), 0);
                    }
                }
                if ($updates["AssignStylePadding"]) {
                    $styletxt .= "applyStyle('" . $updates["AssignStyleLocType"] . "', 'padding', '" . $updates["AssignStylePadding"] . "');";
                }

                if ($styletxt) {
                    $updates["Expression"] = $styletxt;
                }

                if ($updates["AssignAggType"]) {
                    $aggtype = "";
                    $aggcol = $updates["AssignAggCol"];
                    $agggroup = $updates["AssignAggGroup"];
                    switch ($updates["AssignAggType"]) {
                    case "SUM":$aggtype = "sum";
                            break;
                    case "MIN":$aggtype = "min";
                            break;
                    case "SKIPLINE":$aggtype = "skipline";
                            $aggcol = "";
                            break;
                    case "MAX":$aggtype = "max";
                            break;
                    case "COUNT":$aggtype = "count";
                            $aggcol = "";
                            break;
                    case "AVERAGE":$aggtype = "avg";
                            break;
                    case "PREVIOUS":$aggtype = "old";
                            break;
                    case "SUM":$aggtype = "sum";
                            break;
                    }
                    if ($agggroup && $aggcol) {
                        $updates["Expression"] = $aggtype . "({" . $updates["AssignAggCol"] . "},{" . $updates["AssignAggGroup"] . "})";
                    } else if ($aggcol) {
                        $updates["Expression"] = $aggtype . "({" . $updates["AssignAggCol"] . "})";
                    } else if ($agggroup) {
                        $updates["Expression"] = $aggtype . "({" . $updates["AssignAggGroup"] . "})";
                    } else {
                        $updates["Expression"] = $aggtype . "()";
                    }

                }

                if ($updates["AssignGraphicBlobCol"] && $updates["AssignGraphicBlobTab"] && $updates["AssignGraphicBlobMatch"]) {
                    $updates["Expression"] =
                    "imagequery(\"SELECT " . $updates["AssignGraphicBlobCol"] .
                    " FROM " . $updates["AssignGraphicBlobTab"] .
                    " WHERE " . $updates["AssignGraphicBlobMatch"] . " ='\".{" .
                    $updates["AssignGraphicReportCol"] . "}.\"'\",\"" . $updates["AssignGraphicWidth"] . "\")";
                }

                if ($updates["AssignHyperlinkLabel"]) {
                    $hlabel = $updates["AssignHyperlinkLabel"];
                    $hurl = $updates["AssignHyperlinkUrl"];
                    if (!preg_match("/^{.*}$/", $hlabel)) {
                        $hlabel = "'$hlabel'";
                    }

                    $hurl = "'$hurl'";
                    $hurl = preg_replace("/{/", "'.{", $hurl);
                    $hurl = preg_replace("/}/", "}.'", $hurl);
                    $updates["Expression"] = "embed_hyperlink(" . $hlabel . ", " . $hurl . ", true, false);";
                }

                if ($updates["AssignImageUrl"]) {
                    $imgurl = $updates["AssignImageUrl"];
                    $imgurl = "'$imgurl'";
                    $imgurl = preg_replace("/{/", "'.{", $imgurl);
                    $imgurl = preg_replace("/}/", "}.'", $imgurl);
                    $updates["Expression"] = "embed_image(" . $imgurl . ");";
                }

                if ($updates["DrilldownReport"]) {
                    $this->query->drilldown_report = $updates["DrilldownReport"];
                    $q = new Reportico();
                    $q->projects_folder = $this->query->projects_folder;
                    $q->reports_path = $q->projects_folder . "/" . ReporticoApp::getConfig("project");
                    $reader = new XmlReader($q, $updates["DrilldownReport"], false);
                    $reader->xml2query();

                    $content = "Drill";
                    $url = $this->query->getActionUrl() . "?xmlin=" . $updates["DrilldownReport"] . "&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=" . ReporticoApp::getConfig("project");
                    $startbit = "'<a target=\"_blank\" href=\"" . $this->query->getActionUrl() . "?xmlin=" . $updates["DrilldownReport"] . "&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=" . ReporticoApp::getConfig("project");
                    $midbit = "";
                    foreach ($q->lookup_queries as $k => $v) {

                        $testdd = "DrilldownColumn_" . $v->query_name;

                        if (array_key_exists($testdd, $updates)) {
                            if ($updates[$testdd]) {
                                $midbit .= "&MANUAL_" . $v->query_name . "='.{" . $updates[$testdd] . "}.'";
                                if ($v->criteria_type == "DATERANGE") {
                                    $midbit = "&MANUAL_" . $v->query_name . "_FROMDATE='.{" . $updates[$testdd] . "}.'&" .
                                    "MANUAL_" . $v->query_name . "_TODATE='.{" . $updates[$testdd] . "}.'";
                                }

                            }
                        }
                    }
                    $url .= $midbit;
                    unset($q);
                    if ($midbit) {
                        $updates["Expression"] = "embed_hyperlink('" . $content . "', '" . $url . "', true, true)";
                    }

                }

                $updateitem = &$anal["item"];

                if ($assignname = ReporticoUtility::keyValueInArray($updates, "AssignNameNew")) {
                    $found = false;
                    foreach ($anal["quer"]->columns as $querycol) {
                        if ($querycol->query_name == $assignname) {
                            $found = true;
                        }
                    }

                    if (!$found) {
                        $anal["quer"]->createQueryColumn($assignname, "", "", "", "",
                            '####.###',
                            false);
                    }

                    $updates["AssignName"] = $assignname;
                }

                $updateitem->__construct(
                    $updates["AssignName"], $updates["Expression"], $updates["Condition"]);
                break;

            case "pgft":
                if (isset($updates["FooterText_shadow"]) && $updates["FooterText_shadow"] != $updates["FooterText"]) {
                    $updates["FooterText"] = $updates["FooterText"];
                } else {
                    $updates["FooterText"] = $this->applyUpdateStyles("PageFooter", $updates, $updates["FooterText"]);
                }

                $updateitem = &$anal["item"];
                $updateitem->__construct(
                    $updates["LineNumber"], $updates["FooterText"]);
                break;

            case "clnk":
                $qr = &$anal["crit"]->lookup_query;
                $this->query->setCriteriaLink(
                    $updates["LinkFrom"], $updates["LinkTo"],
                    $updates["LinkClause"], $anal["number"]);
                break;

            case "psql":
                $nm = $anal["number"];
                if (isset($updates["SQLText"])) {
                    $this->query->pre_sql[$nm] = $updates["SQLText"];
                } else {
                    $this->query->pre_sql[$nm] = "";
                }

                break;

            case "grps":
                $updateitem = &$anal["item"];
                $nm = $anal["number"];
                $this->query->groups[$anal["number"]]->group_name = $updates["GroupName"];
                $this->query->groups[$anal["number"]]->setAttribute("before_header", $updates["BeforeGroupHeader"]);
                $this->query->groups[$anal["number"]]->setAttribute("after_header", $updates["AfterGroupHeader"]);
                $this->query->groups[$anal["number"]]->setAttribute("before_trailer", $updates["BeforeGroupTrailer"]);
                $this->query->groups[$anal["number"]]->setAttribute("after_trailer", $updates["AfterGroupTrailer"]);
                $this->query->groups[$anal["number"]]->setFormat("before_header", $updates["BeforeGroupHeader"]);
                $this->query->groups[$anal["number"]]->setFormat("after_header", $updates["AfterGroupHeader"]);
                $this->query->groups[$anal["number"]]->setFormat("before_trailer", $updates["BeforeGroupTrailer"]);
                $this->query->groups[$anal["number"]]->setFormat("after_trailer", $updates["AfterGroupTrailer"]);
                break;

            case "crit":
                $updateitem = &$anal["item"];
                $qr = &$anal["quer"];

                $updateitem->setAttribute("column_title", $updates["Title"]);
                $nm = $anal["number"];
                $updateitem->query_name = $updates["Name"];

                if (array_key_exists("QueryTableName", $updates)) {
                    $updateitem->table_name = $updates["QueryTableName"];
                } else {
                    $updateitem->table_name = "";
                }

                $updateitem->column_name = $updates["QueryColumnName"];
                if (ReporticoApp::isSetConfig("dynamic_order_group")) {
                    $updateitem->_use = $updates["Use"];
                }

                $updateitem->criteria_type = $updates["CriteriaType"];
                $updateitem->criteria_list = $updates["CriteriaList"];
                $updateitem->criteria_display = $updates["CriteriaDisplay"];
                $updateitem->expand_display = $updates["ExpandDisplay"];
                $updateitem->required = $updates["CriteriaRequired"];
                //var_dump($updates);
                $updateitem->hidden = $updates["CriteriaHidden"];
                $updateitem->display_group = $updates["CriteriaDisplayGroup"];
                if (array_key_exists("ReturnColumn", $updates)) {
                    $updateitem->lookup_query->setLookupReturn($updates["ReturnColumn"]);
                    $updateitem->lookup_query->setLookupDisplay(
                        $updates["DisplayColumn"], $updates["OverviewColumn"]);
                    $updateitem->lookup_query->setLookupExpandMatch(
                        $updates["MatchColumn"]);
                }
                $updateitem->setCriteriaRequired($updates["CriteriaRequired"]);
                $updateitem->setCriteriaHidden($updates["CriteriaHidden"]);
                $updateitem->setCriteriaDisplayGroup($updates["CriteriaDisplayGroup"]);
                $updateitem->setCriteriaDefaults(
                    $updates["CriteriaDefaults"]);
                $updateitem->setCriteriaHelp(
                    $updates["CriteriaHelp"]);
                $updateitem->setCriteriaList(
                    $updates["CriteriaList"]);
                break;

            case "qcol":
                $cn = $anal["colname"];
                $anal["quer"]->removeColumn("NewColumn");
                $anal["quer"]->createQueryColumn($updates["Name"], "", "", "", "",
                    '####.###',
                    false
                );
                break;

            case "ords":
                break;

            case "dord":
                $cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
                $pn = $anal["number"] + 1;
                if ($pn > $updates["OrderNumber"]) {
                    $anal["quer"]->setColumnOrder($cl, $updates["OrderNumber"], true);
                } else {
                    $anal["quer"]->setColumnOrder($cl, $updates["OrderNumber"], false);
                }

                break;

            case "ghdr":
                //$updates["GroupHeaderCustom"] = $this->applyPdfStyles ( "GroupHeader", $updates, $updates["GroupHeaderCustom"] );
                if (isset($this->field_display["GroupHeaderCustom"])) {
                    if (isset($updates["GroupHeaderCustom_shadow"]) && $updates["GroupHeaderCustom_shadow"] != $updates["GroupHeaderCustom"]) {
                        $updates["GroupHeaderCustom"] = $updates["GroupHeaderCustom"];
                    } else {
                        $updates["GroupHeaderCustom"] = $this->applyUpdateStyles("GroupHeader", $updates, $updates["GroupHeaderCustom"]);
                    }

                } else {
                    $updates["GroupHeaderCustom"] = false;
                }

                $updateitem = &$anal["item"];
                $gr = &$anal["group"];
                $anal["quer"]->setGRoupHeaderByNumber
                ($anal["groupname"], $anal["number"], $updates["GroupHeaderColumn"], $updates["GroupHeaderCustom"], $updates["ShowInHTML"], $updates["ShowInPDF"]);
                break;

            case "gtrl":
                //$updates["GroupTrailerCustom"] = $this->applyPdfStyles ( "GroupTrailer", $updates, $updates["GroupTrailerCustom"] );
                if (isset($this->field_display["GroupTrailerCustom"])) {
                    if (isset($updates["GroupTrailerCustom_shadow"]) && $updates["GroupTrailerCustom_shadow"] != $updates["GroupTrailerCustom"]) {
                        $updates["GroupTrailerCustom"] = $updates["GroupTrailerCustom"];
                    } else {
                        $updates["GroupTrailerCustom"] = $this->applyUpdateStyles("GroupTrailer", $updates, $updates["GroupTrailerCustom"]);
                    }

                } else {
                    $updates["GroupTrailerCustom"] = false;
                }

                $updateitem = &$anal["item"];
                $gr = &$anal["group"];
                $anal["quer"]->setGroupTrailerByNumber
                ($anal["groupname"], $anal["number"],
                    $updates["GroupTrailerDisplayColumn"],
                    $updates["GroupTrailerValueColumn"],
                    $updates["GroupTrailerCustom"], $updates["ShowInHTML"], $updates["ShowInPDF"]
                );
                break;

            case "plot":
                $graph = &$anal["graph"];
                $pl = &$graph->plot[$anal["number"]];
                $pl["name"] = $updates["PlotColumn"];
                $pl["type"] = $updates["PlotType"];
                $pl["linecolor"] = $updates["LineColor"];
                if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig('graph_engine') != "PCHART") {
                    $pl["fillcolor"] = $updates["FillColor"];
                }
                $pl["legend"] = $updates["Legend"];
                break;

            case "grph":
                $qr = &$anal["quer"];
                $updateitem = &$anal["item"];
                $graph = &$qr->graphs[$anal["number"]];

                if (!array_key_exists("GraphColumn", $updates)) {
                    trigger_error(ReporticoLang::templateXlate("MUSTADDGROUP"), E_USER_ERROR);
                } else {
                    $graph->setGraphColumn($updates["GraphColumn"]);
                }

                if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig('graph_engine') != "PCHART") {
                    $graph->setGraphColor($updates["GraphColor"]);
                    $graph->setGrid(".DEFAULT",
                        $updates["XGridDisplay"], ".DEFAULT",
                        $updates["YGridDisplay"], ".DEFAULT"
                    );
                }

                $graph->setTitle($updates["Title"]);
                $graph->setXtitle($updates["XTitle"]);
                $graph->setXlabelColumn($updates["XLabelColumn"]);
                $graph->setYtitle($updates["YTitle"]);
                $graph->setWidth($updates["GraphWidth"]);
                $graph->setHeight($updates["GraphHeight"]);
                $graph->setWidthPdf($updates["GraphWidthPDF"]);
                $graph->setHeightPdf($updates["GraphHeightPDF"]);

                if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig('graph_engine') != "PCHART") {
                    $graph->setTitleFont($updates["TitleFont"], $updates["TitleFontStyle"],
                        $updates["TitleFontSize"], $updates["TitleColor"]);
                    $graph->setXtitleFont($updates["XTitleFont"], $updates["XTitleFontStyle"],
                        $updates["XTitleFontSize"], $updates["XTitleColor"]);
                    $graph->setYtitleFont($updates["YTitleFont"], $updates["YTitleFontStyle"],
                        $updates["YTitleFontSize"], $updates["YTitleColor"]);
                }

                if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig('graph_engine') != "PCHART") {
                    $graph->setXaxis($updates["XTickInterval"], $updates["XTickLabelInterval"], $updates["XAxisColor"]);
                } else {
                    $graph->setXaxis(".DEFAULT", $updates["XTickLabelInterval"], ".DEFAULT");
                }

                if (ReporticoApp::isSetConfig("graph_engine") && ReporticoApp::getConfig('graph_engine') != "PCHART") {
                    $graph->setYaxis($updates["YTickInterval"], $updates["YTickLabelInterval"], $updates["YAxisColor"]);
                    $graph->setXaxisFont($updates["XAxisFont"], $updates["XAxisFontStyle"],
                        $updates["XAxisFontSize"], $updates["XAxisFontColor"]);
                    $graph->setYaxisFont($updates["YAxisFont"], $updates["YAxisFontStyle"],
                        $updates["YAxisFontSize"], $updates["YAxisFontColor"]);
                }
                $graph->setMarginColor($updates["MarginColor"]);
                $graph->setMargins($updates["MarginLeft"], $updates["MarginRight"],
                    $updates["MarginTop"], $updates["MarginBottom"]);
                break;

            case "pghd":
                //$updates["HeaderTxt"] = $this->applyPdfStyles ( "PageHeader", $updates, $updates["HeaderText"] );
                if (isset($updates["HeaderText_shadow"]) && $updates["HeaderText_shadow"] != $updates["HeaderText"]) {
                    $updates["HeaderText"] = $updates["HeaderText"];
                } else {
                    $updates["HeaderText"] = $this->applyUpdateStyles("PageHeader", $updates, $updates["HeaderText"]);
                }

                $updateitem = &$anal["item"];
                $updateitem->__construct(
                    $updates["LineNumber"], $updates["HeaderText"]);
                break;

            case "data":
                $this->query->source_type = $updates["SourceType"];
                break;

            case "conn":
                $updateitem = &$anal["item"];
                $updateitem->setDetails($updates["DatabaseType"],
                    $updates["HostName"],
                    $updates["ServiceName"]);
                $updateitem->setDatabase($updates["DatabaseName"]);
                $updateitem->user_name = $updates["UserName"];
                $updateitem->disconnect();
                if (!$updateitem->connect()) {
                    $this->query->error_message = $updateitem->error_message;
                };
                break;
        }

        return $ret;
    }

    public function getMatchingRequestItem($match)
    {
        $ret = false;
        foreach ($_REQUEST as $k => $v) {
            if (preg_match($match, $k)) {
                return $k;
            }
        }
        return $ret;
    }

    public function getMatchingPostItem($match)
    {
        $ret = false;
        foreach ($_POST as $k => $v) {
            if (preg_match($match, $k)) {
                return $k;
            }
        }
        return $ret;
    }

    // Processes the HTML get/post paramters passed through on the maintain screen
    public function handleUserEntry()
    {
        $sessionClass = ReporticoSession();

        // First look for a parameter beginning "submit_". This will identify
        // What the user wanted to do.

        $hide_area = false;
        $show_area = false;
        $maintain_sql = false;
        $xmlsavefile = false;
        $xmldeletefile = false;
        if (($k = $this->getMatchingPostItem("/^submit_/"))) {
            // Strip off "_submit"
            preg_match("/^submit_(.*)/", $k, $match);

            // Now we should be left with a field element and an action
            // Lets strip the two
            $match1 = preg_split('/_/', $match[0]);
            $fld = $match1[1];
            $action = $match1[2];

            switch ($action) {
                case "ADD":
                    // We have chosen to set a block of data so pass through Request set and see which
                    // fields belong to this set and take appropriate action
                    $this->addMaintainFields($fld);
                    $show_area = $fld;
                    break;

                case "DELETE":
                    // We have chosen to set a block of data so pass through Request set and see which
                    // fields belong to this set and take appropriate action
                    $this->deleteMaintainFields($fld);
                    $show_area = $fld;
                    break;

                case "MOVEUP":
                    // We have chosen to set a block of data so pass through Request set and see which
                    // fields belong to this set and take appropriate action
                    $this->moveupMaintainFields($fld);
                    $show_area = $fld;
                    break;

                case "MOVEDOWN":
                    // We have chosen to set a block of data so pass through Request set and see which
                    // fields belong to this set and take appropriate action
                    $this->movedownMaintainFields($fld);
                    $show_area = $fld;
                    break;

                case "SET":
                    // We have chosen to set a block of data so pass through Request set and see which
                    // fields belong to this set and take appropriate action
                    $this->updateMaintainFields($fld);
                    $show_area = $fld;
                    break;

                case "REPORTLINK":
                case "REPORTLINKITEM":
                    // Link in an item from another report
                    $this->linkInReportFields("link", $fld, $action);
                    $show_area = $fld;
                    break;

                case "REPORTIMPORT":
                case "REPORTIMPORTITEM":
                    // Link in an item from another report
                    $this->linkInReportFields("import", $fld, $action);
                    $show_area = $fld;
                    break;

                case "SAVE":
                    $xmlsavefile = $this->query->xmloutfile;
                    if (!$xmlsavefile) {
                        trigger_error(ReporticoLang::templateXlate("UNABLE_TO_SAVE") . ReporticoLang::templateXlate("SPECIFYXML"), E_USER_ERROR);
                    }

                    break;

                case "PREPARESAVE":
                    $xmlsavefile = $this->query->xmloutfile;
                    $sessionClass::setReporticoSessionParam("execute_mode", "PREPARE");

                    if (!$xmlsavefile) {
                        header("HTTP/1.0 404 Not Found", true);
                        echo '<div class="reportico-error-box">' . ReporticoLang::templateXlate("UNABLE_TO_SAVE") . ReporticoLang::templateXlate("SPECIFYXML") . "</div>";
                        die;
                    }

                    break;

                case "DELETEREPORT":
                    $xmldeletefile = $this->query->xmloutfile;
                    break;

                case "HIDE":
                    $hide_area = $fld;
                    break;

                case "SHOW":
                    $show_area = $fld;
                    break;

                case "SQL":
                    $show_area = $fld;
                    if ($fld == "mainquerqury") {
                        // Main Query SQL Generation.
                        $sql = stripslashes($_REQUEST["mainquerqury_SQL"]);

                        $maintain_sql = $sql;
                        if (Authenticator::login()) {
                            $p = new SqlParser($sql);
                            if ($p->parse()) {
                                $p->importIntoQuery($qr);
                                if ($this->query->datasource->connect()) {
                                    $p->testQuery($this->query, $sql);
                                }

                            }
                        }
                    } else {
                        // It's a lookup
                        if (preg_match("/mainquercrit(.*)qury/", $fld, $match1)) {
                            $lookup = (int) $match1[1];
                            $lookup_char = $match1[1];

                            // Access the relevant crtieria item ..
                            $qc = false;
                            $ak = array_keys($this->query->lookup_queries);
                            if (array_key_exists($lookup, $ak)) {
                                $q = $this->query->lookup_queries[$ak[$lookup]]->lookup_query;
                            } else {
                                $q = new Reportico();
                            }

                            // Parse the entered SQL
                            $sqlparm = $fld . "_SQL";
                            $sql = $_REQUEST[$sqlparm];
                            $q->maintain_sql = $sql;
                            $q = new Reportico();
                            $p = new SqlParser($sql);
                            if ($p->parse()) {
                                if ($p->testQuery($this->query, $sql)) {
                                    $p->importIntoQuery($q);
                                    $this->query->setCriteriaLookup($ak[$lookup], $q, "WHAT", "NOW");
                                }
                            }
                        }
                    }

                    break;

            }
        }

        // Now work out what the maintainance screen should be showing by analysing
        // whether user pressed a SHOW button a HIDE button or keeps a maintenance item
        // show by presence of a shown value
        if (!$show_area) {
            // User has not pressed SHOW_ button - this would have been picked up in previous submit
            // So look for longest shown item - this will allow us to draw the maintenace screen with
            // the correct item maximised
            foreach ($_REQUEST as $k => $req) {
                if (preg_match("/^shown_(.*)/", $k, $match)) {
                    $containee = "/^" . $hide_area . "/";
                    $container = $match[1];
                    if (!preg_match($containee, $container)) {
                        if (strlen($match[1]) > strlen($show_area)) {
                            $show_area = $match[1];
                        }
                    }
                }
            }

        }

        if (!$show_area) {
            $show_area = "mainquer";
        }

        $xmlout = new XmlWriter($this->query);
        $xmlout->prepareXmlData();

        // If Save option has been used then write data to the named file and
        // use this file as the defalt input for future queries
        if ($xmlsavefile) {
            if ($this->query->allow_maintain != "SAFE" && $this->query->allow_maintain != "DEMO" && ReporticoApp::getConfig('allow_maintain')) {
                $xmlout->writeFile($xmlsavefile);
                $sessionClass::setReporticoSessionParam("xmlin", $xmlsavefile);
                $sessionClass::unsetReporticoSessionParam("xmlintext");
            } else {
                trigger_error(ReporticoLang::templateXlate("SAFENOSAVE"), E_USER_ERROR);
            }

        }

        // If Delete Report option has been used then remove the file
        // use this file as the defalt input for future queries
        if ($xmldeletefile) {
            if ($this->query->allow_maintain != "SAFE" && $this->query->allow_maintain != "DEMO" && ReporticoApp::getConfig('allow_maintain')) {
                $xmlout->removeFile($xmldeletefile);
                $sessionClass::setReporticoSessionParam("xmlin", false);
                $sessionClass::unsetReporticoSessionParam("xmlintext");
            } else {
                trigger_error(ReporticoLang::templateXlate("SAFENODEL"), E_USER_ERROR);
            }

        }

        $xml = $xmlout->getXmldata();
        $this->query->xmlintext = $xml;

        $this->query->xmlin = new XmlReader($this->query, false, $xml);
        $this->query->xmlin->show_area = $show_area;
        $this->query->maintain_sql = false;
    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_add_button($in_tag, $in_value = false)
    {
        $text = "";
        $text .= '<TD>';
        $text .= '<input class="' . $this->query->getBootstrapStyle('design_ok') . 'reportico-maintain-button reportico-submit" type="submit" name="submit_' . $in_tag . '_ADD" value="' . ReporticoLang::templateXlate("ADD") . '">';
        $text .= '</TD>';

        // Show Import/Link options
        // We allow import and linking to reports for criteria items
        // We import only for main query assignments
        $importtype = false;
        switch ($in_tag) {
            case "mainquercrit":$importtype = "LINKANDIMPORT";
                break;
            case "mainquerassg":$importtype = "IMPORT";
                break;
            case "mainqueroutppghd":$importtype = "IMPORT";
                break;
            case "mainqueroutppgft":$importtype = "IMPORT";
                break;
            default;
                $importtype = false;
        }

        if ($importtype) {
            $text .= $this->draw_report_link_panel($importtype, $in_tag, $in_value, $this->query->reportlink_report);
        }

        return $text;
    }

    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_movedown_button($in_tag, $in_value = false)
    {
        $text = "";
        //$text .= '<TD class="reportico-maintain-up-down-button-cell">';
        $text .= '<input class="reportico-maintain-move-down-button reportico-submit" type="submit" name="submit_' . $in_tag . '_MOVEDOWN" value="">';
        //$text .= '</TD>';
        return $text;
    }

    // Show drop down allowing user to select a report to link in to report being designed
    // and subsequent elements from that report
    public function &draw_report_link_panel($link_or_import, $tag, $label, $preselectedvalue = false)
    {
        $text = '<TD class="reportico-maintain-set-field" style="text-align: right; color: #eeeeee; background-color: #000000;" colspan="1">';
        $type = "TEXTFIELD";
        $translateoptions = false;

        $striptag = preg_replace("/ .*/", "", $tag);
        $showtag = preg_replace("/ /", "_", $tag);
        $subtitle = "";
        if (preg_match("/ /", $tag)) {
            $subtitle = preg_replace("/.* /", " ", $tag);
        }

        if (array_key_exists($striptag, $this->field_display)) {
            $arval = $this->field_display[$striptag];
            if (array_key_exists("Title", $arval)) {
                $title = $arval["Title"] . $subtitle;
            }

            if (array_key_exists("Type", $arval)) {
                $type = $arval["Type"];
            }

            if (array_key_exists("XlateOptions", $arval)) {
                $translateoptions = $arval["XlateOptions"];
            }

            if (array_key_exists("EditMode", $arval)) {
                $edit_mode = $arval["EditMode"];
            }

            if (array_key_exists("Values", $arval)) {
                $tagvals = $arval["Values"];
            }

        }

        $default = ReporticoApp::getDefaultConfig($striptag, ".");

        $helppage = "importlink";
        if ($helppage) {
            if ($this->query->url_path_to_assets) {
                $helpimg = $this->query->url_path_to_assets . "/images/help.png";
                $text .= '<a target="_blank" href="' . $this->helpPath($helppage, $striptag) . '">';
                $text .= '<img class="reportico-maintain-help-image" alt="tab" src="' . $helpimg . '">';
                $text .= '</a>&nbsp;';
            } else {
                $helpimg = ReporticoUtility::findBestUrlInIncludePath("images/help.png");
                $dr = ReporticoUtility::getReporticoUrlPath();
                $text .= '<a target="_blank" href="' . $this->helpPath($helppage, $striptag) . '">';
                $text .= '<img class="reportico-maintain-help-image" alt="tab" src="' . $dr . $helpimg . '">';
                $text .= '</a>&nbsp;';
            }
        }

        // Show options options to import or link
        $listarr = array();
        if ($link_or_import == "IMPORT" || $link_or_import == "LINKANDIMPORT") {
            $listarr["import"] = ReporticoLang::templateXlate("IMPORTREPORT");
        }

        if ($link_or_import == "LINK" || $link_or_import == "LINKANDIMPORT") {
            $listarr["linkto"] = ReporticoLang::templateXlate("MAKELINKTOREPORT");
        }

        $text .= $this->drawArrayDropdown("linkorimport_" . $this->id, $listarr, $this->query->reportlink_or_import, false, false, true);

        $text .= '</a>&nbsp;&nbsp;';

        // Draw report names we can link to
        $text .= $this->drawSelectFileList($this->query->reports_path, "/.*\.xml/", false, $preselectedvalue, true, false, "reportlink");
        $text .= '<input class="' . $this->query->getBootstrapStyle('design_ok') . 'reportico-maintain-button reportico-submit" style="margin-right: 20px" type="submit" name="submit_' . $this->id . '_REPORTLINK" value="' . ReporticoLang::templateXlate("OK") . '">';

        if ($this->query->reportlink_report) {
            // Draw report criteria items we can link to
            $q = ReporticoUtility::loadExistingReport($this->query->reportlink_report, $this->query->projects_folder);
            if (!$q) {
                trigger_error(ReporticoLang::templateXlate("NOOPENLINK") . $this->query->reportlink_report, E_USER_NOTICE);
            } else if (!$q->lookup_queries || count($q->lookup_queries) == 0) {
                trigger_error(ReporticoLang::templateXlate("NOCRITLINK") . $this->query->reportlink_report, E_USER_NOTICE);
            } else {
                if ($link_or_import == "LINK") {
                    $text .= ReporticoLang::templateXlate("MAKELINKTOREPORTITEM");
                } else {
                    $text .= ReporticoLang::templateXlate("IMPORTREPORT");
                }

                $text .= "&nbsp;";
                $listarr = array();
                $listarr["ALLITEMS"] = ReporticoLang::templateXlate("ALLITEMS");
                if ($tag == "mainquercrit") {
                    $lq = $q->lookup_queries;
                    foreach ($lq as $k => $v) {
                        $listarr[$v->query_name] = $v->query_name;
                    }

                } else if ($tag == "mainquerassg") {
                    $lq = $q->assignment;
                    foreach ($lq as $k => $v) {
                        if (strlen($v->expression) > 30) {
                            $listarr[$k] = $v->query_name . " = " . substr($v->expression, 0, 30) . "...";
                        } else {
                            $listarr[$k] = $v->query_name . " = " . $v->expression;
                        }

                    }
                } else if ($tag == "mainqueroutppghd") {
                    $lq = $q->pageHeaders;
                    foreach ($lq as $k => $v) {
                        if (strlen($v->text) > 30) {
                            $listarr[$k] = $k . " = " . substr($v->text, 0, 30) . "...";
                        } else {
                            $listarr[$k] = $k . " = " . $v->text;
                        }

                    }
                } else if ($tag == "mainqueroutppgft") {
                    $lq = $q->pageFooters;
                    foreach ($lq as $k => $v) {
                        if (strlen($v->text) > 30) {
                            $listarr[$k] = $k . " = " . substr($v->text, 0, 30) . "...";
                        } else {
                            $listarr[$k] = $k . " = " . $v->text;
                        }

                    }
                }

                $text .= $this->drawArrayDropdown("reportlinkitem_" . $this->id, $listarr, false, false, false, true);
                $text .= '<input class="' . $this->query->getBootstrapStyle('design_ok') . 'reportico-maintain-button reportico-submit" style="margin-right: 20px" type="submit" name="submit_' . $this->id . '_REPORTLINKITEM" value="' . ReporticoLang::templateXlate("OK") . '">';
            }
        }

        $text .= '</TD>';
        //$text .= '</TR>';

        return $text;
    }

    // Generate link to help for specific field
    public function helpPath($section, $field = false)
    {
        $fieldtag = $field;
        if (isset($this->field_display["$field"]) && isset($this->field_display["$field"]["DocId"])) {
            $fieldtag = $this->field_display["$field"]["DocId"];
        }

        $path = $this->query->url_doc_site . "/" . $this->query->doc_version . "/" . "doku.php?id=";

        if (isset($this->field_display["$field"]) && isset($this->field_display["$field"]["DocSection"])) {
            $path .= $this->field_display["$field"]["DocSection"];
        } else if ($section) {
            $path .= $this->getHelpLink($section);
        }

        if ($fieldtag) {
            $path .= "#$fieldtag";
        }

        return $path;
    }

    // Creates select list box from a directory file list
    public function drawSelectFileList($path, $filematch, $showtag, $preselectedvalue, $addblank, $translateoptions, $fieldtype = "set")
    {
        $keys = array();
        $keys[] = "";

        if ($showtag) {
            $showtag = "_" . $showtag;
        }

        if (is_dir($this->query->reports_path)) {
            $testpath = $this->query->reports_path;
        } else {
            $testpath = ReporticoUtility::findBestLocationInIncludePath($this->query->reports_path);
        }

        if (is_dir($testpath)) {
            if ($dh = opendir($testpath)) {
                while (($file = readdir($dh)) !== false) {
                    if (preg_match($filematch, $file)) {
                        $keys[] = $file;
                    }

                }
                closedir($dh);
            }
        } else {
            trigger_error(ReporticoLang::templateXlate("NOOPENDIR") . $this->query->reports_path, E_USER_NOTICE);
        }

        $text = $this->drawArrayDropdown($fieldtype . "_" . $this->id . $showtag, $keys, $preselectedvalue, true, false);
        return $text;
    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_moveup_button($in_tag, $in_value = false)
    {
        $text = "";
        //$text .= '<TD class="reportico-maintain-up-down-button-cell">';
        $text .= '<input class="reportico-maintain-move-up-button reportico-submit" type="submit" name="submit_' . $in_tag . '_MOVEUP" value="">';
        //$text .= '</TD>';
        return $text;
    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_delete_button($in_tag, $in_value = false)
    {
        $text = "";
        //$text .= '<TD class="reportico-maintain-up-down-button-cell">';
        $text .= '<input class="reportico-maintain-delete-button reportico-submit" type="submit" name="submit_' . $in_tag . '_DELETE" value="">';
        //$text .= '</TD>';
        return $text;
    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_select_box($in_tag, $in_array, $in_value = false)
    {
        $text = "";
        $text .= '<select class="' . $this->query->getBootstrapStyle('design_dropdown') . 'reportico-prepare-drop-select" name="execute_mode">';
        $text .= '<OPTION selected label="MAINTAIN" value="MAINTAIN">Maintain</OPTION>';
        $text .= '<OPTION label="PREPARE" value="PREPARE">Prepare</OPTION>';
        $text .= '</SELECT>';
        return $text;
    }

    // Draws a tab menu item within a horizontal tab menu
    public function &draw_show_hide_vtab_button($in_tag, $in_value = false,
        $in_moveup = false, $in_movedown = false, $in_delete = true) {
        $text = "";
        if (!$this->isShowing($in_tag)) {
            $text .= '<LI  class="reportico-maintain-verttab-menu-cell-unsel">';
            $text .= '<a class="" name="submit_' . $in_tag . "_SHOW" . '" >';
            $text .= '<input class="reportico-maintain-verttab-menu-but-unsel reportico-submit" type="submit" name="submit_' . $in_tag . "_SHOW" . '" value="' . $in_value . '">';
            if ($in_delete) {
                $text .= $this->draw_delete_button($in_tag);
            }

            if ($in_moveup) {
                $text .= $this->draw_moveup_button($in_tag);
            }

            if ($in_movedown) {
                $text .= $this->draw_movedown_button($in_tag);
            }

            $text .= '</a>';
            $text .= '</LI>';
        } else {
            $text .= '<LI  class="active reportico-maintain-verttab-menu-cell-sel">';
            $text .= '<a class="" name="submit_' . $in_tag . "_SHOW" . '" >';
            $text .= '<input class="reportico-maintain-verttab-menu-but-sel reportico-submit" type="submit" name="submit_' . $in_tag . "_SHOW" . '" value="' . $in_value . '">';
            if ($in_delete) {
                $text .= $this->draw_delete_button($in_tag);
            }

            if ($in_moveup) {
                $text .= $this->draw_moveup_button($in_tag);
            }

            if ($in_movedown) {
                $text .= $this->draw_movedown_button($in_tag);
            }

            $text .= '</a>';
            $text .= '</LI>';
        }
        return $text;

    }

    // Draws a tab menu item within a horizontal tab menu
    public function &draw_show_hide_tab_button($in_tag, $in_value = false)
    {

        $text = "";
        $in_value = ReporticoLang::templateXlate($in_value);

        // Only draw horizontal tab buttons if not mini maintain or they are relevant to tag
        if ($partialMaintain = ReporticoUtility::getRequestItem("partialMaintain", false)) {
            if (preg_match("/_ANY$/", $partialMaintain)) {
                $match1 = preg_replace("/_ANY/", "", $partialMaintain);
                $match2 = substr($in_tag, 0, strlen($match1));
                if ($match1 != $match2 || $match1 == $in_tag ) {
                    return $text;
                }

            } else {
                return $text;
            }

        }

        if (!$this->isShowing($in_tag)) {
            $text .= '<LI class="reportico-maintain-tab-menu-cell-unsel">';
            $text .= '<a class="reportico-maintain-tab-menu-bu1t-unsel reportico-submit" name="submit_' . $in_tag . "_SHOW" . '" >';
            $text .= '<input class="reportico-maintain-tab-menu-but-unsel reportico-submit" type="submit" name="submit_' . $in_tag . "_SHOW" . '" value="' . $in_value . '">';
            $text .= '</a>';
            $text .= '</LI>';
        } else {
            $text .= '<LI  class="active reportico-maintain-tab-menu-cell-sel">';
            $text .= '<a class="reportico-maintain-tab-menu-bu1t-unsel reportico-submit" name="submit_' . $in_tag . "_SHOW" . '" >';
            $text .= '<input class="reportico-maintain-tab-menu-but-sel reportico-submit" type="submit" name="submit_' . $in_tag . "_SHOW" . '" value="' . $in_value . '">';
            $text .= '</a>';
            $text .= '</LI>';
        }
        return $text;

    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function &draw_show_hide_button($in_tag, $in_value = false)
    {
        $text = "";

        if (!$this->isShowing($in_tag)) {
            $text .= '<TD>';
            //$text .= '<input class="reportico-maintain-tab-menu-but-unsel reportico-submit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
            $text .= '<input size="1" style="visibility:hidden" class"reportico-submit" type="submit" name="unshown_' . $in_tag . '" value="">';
            $text .= '</TD>';
        } else {
            $text .= '<TD>';
            //$text .= '<input class="reportico-maintain-tab-menu-but-sel" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
            $text .= '<input size="1" style="visibility:hidden" class"reportico-submit" type="submit" name="shown_' . $in_tag . '" value="">';
            $text .= '</TD>';
        }
        return $text;

    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    //
    // also we want to expand selected Query Column Types immediately into format so if
    // id ends in qcolXXXXXform then its true as well
    public function isShowing($in_tag)
    {
        $container = $this->show_area;
        $containee = $in_tag;
        $ret = false;
        $match = "/^" . $containee . "/";
        if (preg_match($match, $container)) {
            $ret = true;
        }

        $match = "/qcol....form$/";
        if (!$ret && preg_match($match, $containee)) {
            $ret = true;
        }

        $match = "/pghd....form$/";
        if (!$ret && preg_match($match, $containee)) {
            $ret = true;
        }

        $match = "/pgft....form$/";
        if (!$ret && preg_match($match, $containee)) {
            $ret = true;
        }

        $match = "/grph...._/";
        if (!$ret && preg_match($match, $containee)) {
            $ret = true;
        }

        return $ret;
    }

    // Works out whether a maintenance item should be shown on the screen based on the value
    // of the show_area parameter which was derived from the HTTP Request Data
    public function isShowingFull($in_tag)
    {
        $match = "/qcol....$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/qcol....form$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/pghd....form$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/pghd....$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/pgft....form$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/pgft....$/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        $match = "/grps...._/";
        if (preg_match($match, $in_tag)) {
            return true;
        }

        if ($in_tag . "detl" == $this->show_area) {
            return true;
        }

        if ($in_tag == $this->show_area) {
            return true;
        }

        return false;
    }

    public function &xml2html(&$ar, $from_key = false)
    {
        $text = "";

        $hold_last = false;

        $fct = 0;
        foreach ($ar as $k => $val) {
            $fct++;
            if (is_array($val)) {
                $oldid = $this->id;

                // To get over fact switch does not operatoe for a zero value force k to be
                // -1 if it is 0
                if (is_numeric($k) && (int) $k == 0) {
                    $k = "ZERO";
                }

                switch ($k) {
                    case "Report":
                    case "CogModule":
                        $this->id = "main";
                        $text .= '<TABLE class="reportico-maintain-main-box">';
                        if (ReporticoUtility::getRequestItem("partialMaintain", false)) {
                            break;
                        }

                        /*
                         * $text .= '<TR>';
                        $text .= '<TD colspan="2">';
                        $text .= '&nbsp;&nbsp;' . ReporticoLang::templateXlate('PROJECT') . ReporticoApp::getConfig("project") . '&nbsp;&nbsp;&nbsp;&nbsp;';
                        if ($this->query->xmloutfile == "configureproject") {
                            $text .= ReporticoLang::templateXlate('REPORT_FILE') . ' <input style="display: inline" type="text" name="xmlout" value="">';
                        } else {
                            $text .= ReporticoLang::templateXlate('REPORT_FILE') . ' <input type="text" style="display: inline" name="xmlout" value="' . $this->query->xmloutfile . '">';
                        }

                        $text .= '&nbsp;&nbsp;<input class="' . $this->query->getBootstrapStyle('button_admin') . 'reportico-ajax-link reportico-submit" type="submit" name="submit_xxx_SAVE" value="' . ReporticoLang::templateXlate("SAVE") . '">';
                        $text .= '&nbsp;&nbsp;<input class="' . $this->query->getBootstrapStyle('button_admin') . 'reportico-ajax-link reportico-submit" type="submit" name="submit_maintain_NEW" value="' . ReporticoLang::templateXlate("NEW_REPORT") . '">';
                        $text .= '<input class="' . $this->query->getBootstrapStyle('button_delete') . 'reportico-ajax-link reportico-submit" style="margin-left: 80px" type="submit" name="submit_xxx_DELETEREPORT" value="' . ReporticoLang::templateXlate("DELETE_REPORT") . '">';
                        $text .= '</TD>';
                        $text .= '</TR>';
                        //$text .= '<TR>';
                        */
                        break;

                    case "CogQuery":
                    case "ReportQuery":
                        $this->id .= "quer";
                        //$text .= '</TR>';
                        $text .= '</TABLE>';
                        $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';

                        // Force format Screen if none chosen
                        $match = "/quer$/";
                        if (preg_match($match, $this->show_area)) {
                            $this->show_area .= "form";
                        }

                        $text .= $this->draw_show_hide_tab_button($this->id . "form", "FORMAT");
                        $text .= $this->draw_show_hide_tab_button($this->id . "qury", "QUERY_DETAILS");
                        $text .= $this->draw_show_hide_tab_button($this->id . "assg", "ASSIGNMENTS");
                        $text .= $this->draw_show_hide_tab_button($this->id . "crit", "CRITERIA");
                        $text .= $this->draw_show_hide_tab_button($this->id . "outp", "OUTPUT");
                        $text .= '</UL>';
                        $text .= '<TABLE class="reportico-maintain-inner-box">';
                        break;

                    case "SQL":
                        $ct = count($val);
                        $this->id .= "sqlt";
                        break;

                    case "Format":
                        $ct = count($val);
                        $this->id .= "form";
                        if ($this->id != "mainquerform") {
                            $text .= "\n<!--FORM SHOW --><TR>";
                            $text .= $this->draw_show_hide_button($this->id, "Format");
                            $text .= "</TR>";
                        }
                        break;

                    case 'Groups':
                        $this->id .= "grps";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Groups");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "GROUP", "_key", true);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }

                        break;

                    case 'GroupTrailers':
                        $this->id .= "gtrl";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Group Trailers");
                            $text .= '</TR>';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "TRAILER", "_key", false);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'GroupHeaders':
                        $this->id .= "ghdr";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Group Headers");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "HEADER", "_key", true);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'PreSQLS':
                        $this->id .= "psql";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "PreSQLS");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "PRESQL", "_key", true);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'OrderColumns':
                        $this->id .= "ords";
                        break;

                    case 'DisplayOrders':
                        $this->id .= "dord";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "", "ColumnName", true, false);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'Plots':
                        $this->id .= "plot";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Plots");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "PLOT", "_key");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'Graphs':
                        $this->id .= "grph";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Graphs");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "GRAPH", "_key");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'PageHeaders':
                        $this->id .= "pghd";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Page Headers");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "PAGE_HEADER", "_key");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'PageFooters':
                        $this->id .= "pgft";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Page Footers");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "PAGE_FOOTER", "_key");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'QueryColumns':
                        $this->id .= "qcol";
                        if ($this->isShowing($this->id)) {
                            $text .= "\n<!--Debug Qcol-->";
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Query Columns");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "", "Name");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                            $text .= "\n<!--Debug Qcol-->";
                        }

                        break;

                    case 'Output':
                        $this->id .= "outp";
                        $ct = count($val);
                        if ($this->id != "mainqueroutp") {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_show_hide_button($this->id, "Output");
                            if ($this->isShowing($this->id)) {
                                $text .= '<TD colspan="3"><TABLE><TR>';
                            }
                        }
                        if ($this->isShowing($this->id)) {
                            // Force format Screen if none chosen
                            $match = "/outp$/";
                            if (preg_match($match, $this->show_area)) {
                                $this->show_area .= "pghd";
                            }

                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= '<TD style="width: 100%;">';
                            $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';
                            $text .= $this->draw_show_hide_tab_button($this->id . "pghd", "PAGE_HEADERS");
                            $text .= $this->draw_show_hide_tab_button($this->id . "pgft", "PAGE_FOOTERS");
                            $text .= $this->draw_show_hide_tab_button($this->id . "dord", "DISPLAY_ORDER");
                            $text .= $this->draw_show_hide_tab_button($this->id . "grps", "GROUPS");
                            $text .= $this->draw_show_hide_tab_button($this->id . "grph", "GRAPHS");
                            $text .= '</UL>';
                            $text .= '</TD>';
                            $text .= '</TR>';
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= '<TD style="width: 100%;">';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                        }
                        break;

                    case 'Datasource':
                        $this->id .= "data";
                        $ct = count($val);
                        if ($this->id != "mainquerdata") {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_show_hide_button($this->id, "Data Source");
                            if ($this->isShowing($this->id)) {
                                $text .= '<TD colspan="3"><TABLE><TR>';
                            }
                        }
                        break;

                    case 'SourceConnection':
                        $this->id .= "conn";
                        $ct = count($val);
                        $text .= '<TR class="reportico-maintain-row-block">';
                        $text .= '<TD colspan="3"><TABLE><TR>';
                        $text .= $this->draw_show_hide_button($this->id, "Connection");
                        $text .= '</TR>';
                        $text .= '<TR>';
                        break;

                    case 'EntryForm':
                        break;

                    case 'Query':
                        $this->id .= "qury";
                        $text .= '<!--Start Query-->';
                        if ($this->id == "mainquerqury" && $this->isShowing($this->id)) {
                            // Force format Screen if none chosen
                            $match = "/qury$/";
                            if (preg_match($match, $this->show_area)) {
                                $this->show_area .= "sqlt";
                            }

                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= '<TD style="width: 100%;">';
                            $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';
                            $text .= $this->draw_show_hide_tab_button($this->id . "sqlt", "SQL");
                            $text .= $this->draw_show_hide_tab_button($this->id . "qcol", "QUERY_COLUMNS");
                            //$text .= $this->draw_show_hide_tab_button ($this->id."ords", "ORDER_BY") ;
                            $text .= $this->draw_show_hide_tab_button($this->id . "psql", "PRESQLS");
                            $text .= '</UL>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                        }
                        $text .= '<!--End Query-->';
                        break;

                    case 'Criteria':
                        $this->id .= "crit";
                        if ($this->id != "mainquercrit") {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_show_hide_button($this->id, "Criteria");
                            $text .= '</TR>';
                        }

                        $text .= "\n<!--StartCrit" . $this->id . "-->";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Criteria");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "CRITERIAITEM", "Name", true);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }
                        break;

                    case 'CriteriaLinks':
                        $this->id .= "clnk";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "Link");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "LINKS", "_key");
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }

                        break;

                    case 'Assignments':
                        $this->id .= "assg";
                        if ($this->isShowing($this->id)) {
                            $text .= '<TR class="reportico-maintain-row-block">';
                            $text .= $this->draw_add_button($this->id, "ASSIGNMENT");
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                            $text .= '<TABLE class="reportico-maintain-inner-box">';
                            $text .= '<TR>';
                            $text .= $this->panel_key_to_html($this->id, $val, "ASSIGNMENT", "AssignName", true);
                            $text .= '<TD valign="top">';
                            $element_counts[$k] = count($val);
                            if (count($val) > 0) {
                                $text .= '<TABLE class="reportico-maintain-inner-right-box">';
                            }

                        }

                        break;

                    case 'CriteriaItem':
                        $this->id .= "item";
                        break;

                    case 'CriteriaLink':
                        $this->id .= "link";
                        break;

                    case "0":
                        break;

                    case "ZERO":
                        $k = 0;

                    default:
                        if (is_numeric($k)) {
                            $str = sprintf("%04d", $k);
                            $this->id .= $str;

                            $hold_last = true;
                            if ($from_key == "Assignments") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "Groups") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "GroupHeaders") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "GroupTrailers") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "PreSQLS") {
                            }
                            if ($from_key == "Plots") {
                            }
                            if ($from_key == "Graphs") {
                            }
                            if ($from_key == "PageHeaders") {
                            }
                            if ($from_key == "PageFooters") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "OrderColumns") {
                                $text .= '<TR class="reportico-maintain-row-block">';
                                $text .= $this->draw_show_hide_button($this->id, "Order Column " . $k);
                                $text .= $this->draw_delete_button($this->id);
                                $text .= '</TR>';
                            }
                            if ($from_key == "DisplayOrders") {
                            }
                            if ($from_key == "QueryColumns") {
                                $ct = count($val);
                                $ct++;
                            }
                            if ($from_key == "Criteria") {
                                $this->current_criteria_name = $val["Name"];
                            }
                        } else {
                            $text .= "*****Got bad $k<br>";
                        }

                        break;
                }
                if (!$hold_last) {
                    $this->last_element = $k;
                }

                if (count($val) > 0) {
                    // Only generate HTML if the suitable element needs to be shown
                    if ($this->isShowing($this->id)) {
                        $text .= $this->xml2html($val, $k);
                    }

                }

                $parent_id = $this->id;
                $this->id = $oldid;
                $this->level_ct--;

                if (is_numeric($k) && (int) $k == 0) {
                    $k = "ZERO";
                }

                switch ($k) {
                    case "Output":
                        if ($this->isShowing($parent_id)) {
                            $text .= "\n<!--End Output-->";
                            $text .= '</TABLE>';
                            $text .= '</TD>';
                            $text .= '</TR>';
                        }

                    case "Report":
                    case "CogModule":
                        if ($this->id) {
                            $text .= '<!--Report -->';
                            $text .= '</TABLE>';
                        }
                        break;

                    case "CogQuery":
                    case "ReportQuery":
                        break;

                    case "SQL":
                        break;

                    case "Format":
                        if ($this->isShowing($parent_id)) {
                            if ($this->id != "mainquer") {
                                $text .= "\n<!--End Format" . $this->id . " " . $parent_id . "-->";
                            }
                        }

                        break;

                    case 'PreSQLS':
                    case 'OrderColumns':
                        break;

                    case 'Datasource':
                        if ($this->id != "mainquer") {
                            $text .= "\n<!--End Data Source-->";
                            $text .= '</TR></TABLE>';
                        }
                        break;

                    case 'SourceConnection':
                        $text .= "\n<!--End Cource Connection-->";
                        $text .= '</TR></TABLE>';
                        break;

                    case 'EntryForm':
                        break;

                    case 'Query':
                        if ($parent_id == "mainquerqury" && $this->isShowing($parent_id)) {
                            $text .= "\n<!--End Query-->";
                            $text .= '</TABLE>';
                            $text .= '</TD>';
                            $text .= '</TR>';
                        }
                        break;

                    case 'Criteria':
                    case 'QueryColumns':
                    case 'GroupHeaders':
                    case 'GroupTrailers':
                    case 'Groups':
                    case 'DisplayOrders':
                    case 'Graphs':
                    case 'Plots':
                    case 'PreSQLS':
                    case 'PageFooters':
                    case 'PageHeaders':
                    case 'CriteriaLink':
                    case 'CriteriaLinks':
                        if ($this->isShowing($parent_id)) {
                            $text .= "\n<!--General-" . $parent_id . " $k-->";
                            $text .= '<TR>';
                            $text .= '<TD>&nbsp;</TD>';
                            $text .= '</TR>';
                            if ($element_counts[$k] > 0) {
                                $text .= '</TABLE>';
                            }
                            $text .= '</TD>';
                            $text .= '</TR>';
                        }
                        break;

                    case 'Assignments':
                        if ($this->isShowing($parent_id)) {
                            $text .= "\n<!--Assignment-->";
                            $text .= '<TR>';
                            $text .= '<TD>&nbsp;</TD>';
                            $text .= '</TR>';
                            $text .= '</TABLE>';
                        }
                        break;

                    case 'CriteriaItem':
                        break;

                    case "ZERO":
                        $k = 0;

                    default:
                        if (is_numeric($k)) {
                            $match = "/assg[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= $this->assignment_aggregates($parent_id);
                                }

                            }

                            $match = "/pghd[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= $this->drawStyleWizard($parent_id, "PageHeader", $val, $tagct);
                                }

                                //$text .= $this->query->applyPlugins("draw-section", array("parent" => &$this, "parent_id" => &$parent_id, "type" => "PageHeader", "value" => $val, "tagct" => &$tagct));
                            }

                            $match = "/ghdr[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= $this->drawStyleWizard($parent_id, "GroupHeader", $val, $tagct);
                                }

                            }

                            $match = "/gtrl[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= $this->drawStyleWizard($parent_id, "GroupTrailer", $val, $tagct);
                                }

                            }

                            $match = "/pgft[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= $this->drawStyleWizard($parent_id, "PageFooter", $val, $tagct);
                                }

                            }

                            $match = "/grph[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= "\n<!--End grph bit-->";
                                    $text .= "</TABLE>";
                                    $text .= "</TD>";
                                    $text .= "</TR>";
                                }
                            }
                            $match = "/grps[0-9][0-9][0-9][0-9]/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= "\n<!--End grop bit-->";
                                    $text .= "</TABLE>";
                                    $text .= "</TD>";
                                    $text .= "</TR>";
                                }
                            }
                            $match = "/crit[0-9][0-9][0-9][0-9]$/";
                            if (preg_match($match, $parent_id)) {
                                if ($this->isShowing($parent_id)) {
                                    $text .= "\n<!--end  crit bit " . $parent_id . "-->";
                                    $text .= "</TABLE>";
                                    $text .= "</TD>";
                                    $text .= "</TR>";
                                }
                            }
                        }
                        break;
                }
            } else {
                // Force Group Header Trailer menu after group entry fields
                $match = "/grph[0-9][0-9][0-9][0-9]/";
                $match1 = "/grph[0-9][0-9][0-9][0-9]$/";
                if (preg_match($match1, $this->id) && $fct == 1) {
                    $match = "/grph[0-9][0-9][0-9][0-9]$/";
                    if (preg_match($match, $this->show_area)) {
                        $this->show_area .= "detl";
                    }

                    $text .= "\n" . '<TR class="reportico-maintain-row-block">' . "\n";
                    $text .= '	<TD style="width: 100%;" colspan="4">' . "\n";
                    $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';
                    $text .= $this->draw_show_hide_tab_button($this->id . "detl", "DETAILS");
                    $text .= $this->draw_show_hide_tab_button($this->id . "plot", "PLOTS");
                    $text .= '		</UL>' . "\n";
                    $text .= '		<TABLE class="reportico-maintain-inner-box">' . "\n";
                }

                // Force Group Header Trailer menu after group entry fields
                $match = "/grps[0-9][0-9][0-9][0-9]/";
                $match1 = "/grps[0-9][0-9][0-9][0-9]$/";
                if (preg_match($match1, $this->id) && $fct == 1) {
                    $match = "/grps[0-9][0-9][0-9][0-9]$/";
                    if (preg_match($match, $this->show_area)) {
                        $this->show_area .= "detl";
                    }

                    $text .= "\n" . '<TR class="reportico-maintain-row-block">' . "\n";
                    $text .= '	<TD style="width: 100%;" colspan="4">' . "\n";
                    $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';
                    $text .= $this->draw_show_hide_tab_button($this->id . "detl", "DETAILS");
                    $text .= $this->draw_show_hide_tab_button($this->id . "ghdr", "HEADERS");
                    $text .= $this->draw_show_hide_tab_button($this->id . "gtrl", "TRAILERS");
                    $text .= '		</UL>' . "\n";
                    $text .= '		<TABLE class="reportico-maintain-inner-box">' . "\n";
                }

                // Force Criteria menu after group entry fields
                $match = "/crit[0-9][0-9][0-9][0-9]/";
                $match1 = "/crit[0-9][0-9][0-9][0-9]$/";
                if (preg_match($match1, $this->id) && $fct == 1) {
                    $match = "/crit[0-9][0-9][0-9][0-9]$/";
                    if (preg_match($match, $this->show_area)) {
                        $this->show_area .= "detl";
                    }

                    $text .= "\n<!--startcrit bit " . $this->id . "-->";
                    $text .= '<TR class="reportico-maintain-row-block">' . "\n";
                    $text .= '	<TD style="width: 100%;" colspan="4">' . "\n";
                    $text .= '<UL style="width:100%" class="' . $this->query->getBootstrapStyle('htabs') . 'reportico-maintain-main-box">';
                    $text .= $this->draw_show_hide_tab_button($this->id . "detl", "DETAILS");
                    if (!isset($ar["LinkToReport"]) || !$ar["LinkToReport"]) {
                        $text .= $this->draw_show_hide_tab_button($this->id . "qurysqlt", "SQL");
                        $text .= $this->draw_show_hide_tab_button($this->id . "quryqcol", "QUERY_COLUMNS");
                        $text .= $this->draw_show_hide_tab_button($this->id . "clnk", "LINKS");
                        $text .= $this->draw_show_hide_tab_button($this->id . "quryassg", "ASSIGNMENTS");
                    }

                    $text .= '		</UL>' . "\n";
                    $text .= '		<TABLE class="reportico-maintain-inner-box">' . "\n";
                }

                if ($this->isShowingFull($this->id)) {
                    if ($k == "QuerySql") {
                        if (!$this->current_criteria_name) {
                            $q = &$this->query;
                        } else {
                            $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                        }

                        $out = "";
                        if ($q->maintain_sql) {
                            $out = $q->maintain_sql;
                        } else {
                            $q->buildQuery(false, "", true);
                            $out = $q->query_statement;
                        }
                        $val = $out;
                    }

                    if (
                        $k != "TableSql"
                        && $k != "WhereSql"
                        && $k != "GroupSql"
                        && $k != "RowSelection"
                    ) {
                        if (isset($this->field_display[$k])) {
                            $text .= $this->display_maintain_field($k, $val, $fct);
                        }

                        //else
                        if (isset($this->field_display[$k]) && isset($this->field_display[$k]["HasChangeComparator"]) && $this->field_display[$k]["HasChangeComparator"]) {
                            $text .= $this->display_maintain_field($k, $val, $fct, true, false, false, false, true);
                        }
                    }

                }
            }
        }
        return $text;

    }

    public function drawStyleWizard(&$in_parent, $type, $val, &$tagct)
    {
        $parent = &$this;
        $text = "";
        $tagct = 1;
        $tmpid = $parent->id;
        $parent->id = $in_parent;
        $blocktype = "assignTypeStyle";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype .
        '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("OUTPUTSTYLESWIZARD") . '</b></TD></TR>';
        $val = false;

        // Extract existing styles into wizard elements
        $styles = array(
            "color" => false,
            "background-color" => false,
            "border-style" => false,
            "border-width" => false,
            "border-color" => false,
            "margin" => false,
            "position" => false,
            "padding" => false,
            "height" => false,
            "width" => false,
            "font-family" => false,
            "font-size" => false,
            "font-style" => false,
            "background-image" => false,
        );
        if ($parent->wizard_linked_to) {
            if (preg_match("/{STYLE[ ,]*([^}].*)}/", $parent->wizard_linked_to, $matches)) {
                if (isset($matches[1])) {
                    $stylearr = explode(";", $matches[1]);
                    foreach ($stylearr as $v) {
                        $element = explode(":", $v);
                        if ($element && isset($element[1])) {
                            $styles[$element[0]] = trim($element[1]);
                        }
                    }
                }
            }
        }
        if ($styles["border-style"]) {
            if ($styles["border-style"] == "noone") {
                $styles["border-style"] = "NONE";
            }

            if ($styles["border-style"] == "solid") {
                $styles["border-style"] = "SOLIDLINE";
            }

            if ($styles["border-style"] == "dotted") {
                $styles["border-style"] = "DOTTED";
            }

            if ($styles["border-style"] == "dashed") {
                $styles["border-style"] = "DASHED";
            }

        }

        if ($styles["font-style"]) {
            if ($styles["font-style"] == "noone") {
                $styles["border-style"] = "NONE";
            }
        }

        if ($styles["position"]) {
            if ($styles["position"] == "absolute") {
                $styles["position"] = "ABSOLUTE";
            }
        }

        $text .= $parent->display_maintain_field("${type}StyleFgColor", $styles["color"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBgColor", $styles["background-color"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderStyle", $styles["border-style"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderSize", $styles["border-width"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderColor", $styles["border-color"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleMargin", $styles["margin"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StylePadding", $styles["padding"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleHeight", $styles["height"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleWidth", $styles["width"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StylePosition", $styles["position"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontName", $styles["font-family"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontSize", $styles["font-size"], $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontStyle", $styles["font-style"], $tagct, true, false, $blocktype);
        $tagct++;

        if ($type == "PageHeader" || $type == "PageFooter" || $type == "GroupHeader" || $type == "GroupTrailer") {
            $text .= $parent->display_maintain_field("${type}StyleBackgroundImage", $styles["background-image"], $tagct, true, false, $blocktype);
            $tagct++;
        }

        $parent->id = $tmpid;

        return $text;
    }

    public function applyUpdateStyles($type, &$updates, $applyto)
    {
        $styletxt = "";
        if ($updates["${type}StyleFgColor"]) {
            $styletxt .= "color: " . $updates["${type}StyleFgColor"] . ";";
        }

        if ($updates["${type}StyleBgColor"]) {
            $styletxt .= "background-color:" . $updates["${type}StyleBgColor"] . ";";
        }

        if (isset($updates["${type}StyleFontName"]) && $updates["${type}StyleFontName"]) {
            $styletxt .= "font-family:" . $updates["${type}StyleFontName"] . ";";
        }

        if ($updates["${type}StyleFontSize"]) {
            $styletxt .= "font-size:" . $updates["${type}StyleFontSize"] . ";";
        }

        if ($updates["${type}StyleWidth"]) {
            $styletxt .= "width:" . $updates["${type}StyleWidth"] . ";";
        }

        if ($updates["${type}StyleHeight"]) {
            $styletxt .= "height:" . $updates["${type}StyleHeight"] . ";";
        }

        if ($updates["${type}StyleFontStyle"] && $updates["${type}StyleFontStyle"] != "NONE") {
            $stylevalue = "none";
            if ($updates["${type}StyleFontStyle"] == "BOLD" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-weight:bold;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "ITALIC" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-style:italic;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "NORMAL") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-style:normal;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "UNDERLINE") {
                if ($updates["${type}StyleFontStyle"] == "UNDERLINE") {
                    if ($updates["${type}StyleFontStyle"]) {
                        $styletxt .= "text-decoration:underline;";
                    }
                }
            }

            if ($updates["${type}StyleFontStyle"] == "OVERLINE") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:overline;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "BLINK") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:blink;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "STRIKETHROUGH") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:line-through;";
                }
            }

        }

        if ($updates["${type}StylePosition"]) {
            $stylevalue = "none";
            //if ( $updates["${type}StylePosition"] == "RELATIVE" || $updates["${type}StylePosition"] == "relative" )
            if ($updates["${type}StylePosition"] == "ABSOLUTE") {
                $styletxt .= "position: absolute;";
            }

        }

        if (!$updates["${type}StyleBorderStyle"] || $updates["${type}StyleBorderStyle"] == "NOBORDER") {
            if ($updates["${type}StyleBorderSize"] || $updates["${type}StyleBorderColor"]) {
                trigger_error(ReporticoLang::templateXlate("SETBORDERSTYLE"), E_USER_ERROR);
            }

        } else {
            $stylevalue = "none";
            if ($updates["${type}StyleBorderStyle"] == "SOLIDLINE") {
                $stylevalue = "solid";
            }

            if ($updates["${type}StyleBorderStyle"] == "DASHED") {
                $stylevalue = "dashed";
            }

            if ($updates["${type}StyleBorderStyle"] == "DOTTED") {
                $stylevalue = "dotted";
            }

            $styletxt .= "border-style:$stylevalue;";
            if ($updates["${type}StyleBorderSize"]) {
                $styletxt .= "border-width:" . $updates["${type}StyleBorderSize"] . ";";
            }

            if ($updates["${type}StyleBorderColor"]) {
                $styletxt .= "border-color:" . $updates["${type}StyleBorderColor"] . ";";
            }

        }

        if ($updates["${type}StylePadding"]) {
            $styletxt .= "padding:" . $updates["${type}StylePadding"] . ";";
        }

        if ($updates["${type}StyleBackgroundImage"]) {
            $styletxt .= "background-image:" . $updates["${type}StyleBackgroundImage"] . ";";
        }

        if ($updates["${type}StyleMargin"]) {
            $styletxt .= "margin:" . $updates["${type}StyleMargin"] . ";";
        }

        if ($styletxt) {
            $applyto = preg_replace("/{STYLE [^}]*}/", "", $applyto);
            $applyto .= "{STYLE $styletxt}";
        }
        return $applyto;
    }

    public function getHelpLink($tag)
    {
        $helppage = false;
        $stub = substr($tag, 0, 12);
        if ($stub == "mainquercrit") {
            $helppage = "the_criteria_menu";
        } else if ($stub == "mainquerassg") {
            $helppage = "the_assignments_menu";
        } else if ($stub == "mainquerqury") {
            $helppage = "the_query_details_menu";
        } else if ($stub == "mainqueroutp") {
            $helppage = "the_output_menu";
        } else if ($stub == "mainquerform") {
            $helppage = "the_design_format_menu";
        } else {
            $helppage = $stub;
        }

        return $helppage;
    }

    public function &display_maintain_field($tag, $val, &$tagct, $translate = true, $overridetitle = false, $toggleclass = false, $togglestate = false, $draw_shadow = false)
    {
        $text = "";
        $striptag = preg_replace("/ .*/", "", $tag);
        $showtag = preg_replace("/ /", "_", $tag);
        $partialMaintain = ReporticoUtility::getRequestItem("partialMaintain", false);
        if ($partialMaintain) {
            $x = $this->id . "_" . $showtag;
            if ($partialMaintain != $x && !preg_match("/$partialMaintain/", $x) && !preg_match("/_ANY/", $partialMaintain)) {
                return $text;
            }

        }

        $text .= "\n<!-- SETFIELD-->";
        $text .= '<TR';
        if ($toggleclass) {
            if ($togglestate) {
                $text .= " class=\"" . $toggleclass . "\" style=\"display: table-row\" ";
            } else {
                $text .= " class=\"" . $toggleclass . "\" style=\"display: none\" ";
            }

        }
        if ($draw_shadow) {
            $text .= " style=\"display: none\" ";
        }
        $text .= '>';
        $type = "TEXTFIELD";
        $translateoptions = false;
        $title = $tag;
        $edit_mode = "FULL";
        $tagvals = array();

        $subtitle = "";
        if (preg_match("/ /", $tag)) {
            $subtitle = preg_replace("/.* /", " ", $tag);
        }

        if (array_key_exists($striptag, $this->field_display)) {
            $arval = $this->field_display[$striptag];
            if (array_key_exists("Title", $arval)) {
                $title = $arval["Title"] . $subtitle;
            }

            if (array_key_exists("Type", $arval)) {
                $type = $arval["Type"];
            }

            if (array_key_exists("WizardLink", $arval)) {
                $this->wizard_linked_to = $val;
            }

            if (array_key_exists("XlateOptions", $arval)) {
                $translateoptions = $arval["XlateOptions"];
            }

            if (array_key_exists("EditMode", $arval)) {
                $edit_mode = $arval["EditMode"];
            }

            if (array_key_exists("Values", $arval)) {
                $tagvals = $arval["Values"];
            }

        }

        if ($overridetitle) {
            $title = $overridetitle;
        }

        $default = ReporticoApp::getDefaultConfig($striptag, ".");
        if ($type == "HIDE") {
            $tagct--;
            $test = "";
            return $text;
        }

        $helppage = $this->getHelpLink($this->id);

        $text .= '<TD class="reportico-maintain-set-field">';
        if ($helppage) {
            if ($this->query->url_path_to_assets) {
                $helpimg = $this->query->url_path_to_assets . "/images/help.png";
                $text .= '<a target="_blank" href="' . $this->helpPath($this->id, $striptag) . '">';
                $text .= '<img class="reportico-maintain-help-image" alt="tab" src="' . $helpimg . '">';
                $text .= '</a>&nbsp;';
            } else {
                $helpimg = ReporticoUtility::findBestUrlInIncludePath("images/help.png");
                $dr = ReporticoUtility::getReporticoUrlPath();
                $text .= '<a target="_blank" href="' . $this->helpPath($this->id, $striptag) . '">';
                $text .= '<img class="reportico-maintain-help-image" alt="tab" src="' . $dr . $helpimg . '">';
                $text .= '</a>&nbsp;';
            }
        }
        if ($translate) {
            $text .= ReporticoLang::templateXlate($title);
        } else {
            $text .= $title;
        }

        if ($edit_mode == "SAFE") {
            if (ReporticoApp::getConfig('safe_design_mode')) {
                $text .= "<br>" . ReporticoLang::templateXlate("SAFEOFF");
            } else {
                $text .= "";
            }
        }

        $text .= '</TD>';

        if ($draw_shadow) {
            $shadow = "_shadow";
        } else {
            $shadow = "";
        }

        // Display Field Entry
        $text .= '<TD class="reportico-maintain-set-field" colspan="1">';
        switch ($type) {
            case "PASSWORD":
                $text .= '<input class="' . $this->query->getBootstrapStyle('textfield') . '" type="password" size="40%" name="set_' . $this->id . "_" . $showtag . $shadow . '" value="' . htmlspecialchars($val) . '"><br>';
                break;

            case "TEXTFIELDREADONLY":
                $readonly = "readonly";
                $text .= '<input class="' . $this->query->getBootstrapStyle('textfield') . '" type="text" size="40%" ' . $readonly . ' name="set_' . $this->id . "_" . $showtag . $shadow . '" value="' . htmlspecialchars($val) . '">';
                break;

            case "TEXTFIELD":
            case "TEXTFIELDNOOK":
                $readonly = "";
                if ($edit_mode == "SAFE" && ($this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || ReporticoApp::getConfig('safe_design_mode'))) {
                    $readonly = "readonly";
                }

                $text .= '<input class="' . $this->query->getBootstrapStyle('textfield') . '" type="text" size="40%" ' . $readonly . ' name="set_' . $this->id . "_" . $showtag . $shadow . '" value="' . htmlspecialchars($val) . '">';
                break;

            case "TEXTBOX":
                $readonly = "";
                if ($edit_mode == "SAFE" && ($this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || ReporticoApp::getConfig('safe_design_mode'))) {
                    $readonly = "readonly";
                }

                $text .= '<textarea class="' . $this->query->getBootstrapStyle('textfield') . '" ' . $readonly . ' cols="70" rows="20" name="set_' . $this->id . "_" . $showtag . $shadow . '" >';
                $text .= htmlspecialchars($val);
                $text .= '</textarea>';
                break;

            case "TEXTBOXNARROW":
                $readonly = "";
                if ($edit_mode == "SAFE" && ($this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || ReporticoApp::getConfig('safe_design_mode'))) {
                    $readonly = "readonly";
                }

                $text .= '<textarea class="' . $this->query->getBootstrapStyle('textfield') . ' reportico-maintain-text-box-narrow" ' . $readonly . ' cols="70" rows="20" name="set_' . $this->id . "_" . $showtag . $shadow . '" >';
                $text .= htmlspecialchars($val);
                $text .= '</textarea>';
                break;

            case "TEXTBOXSMALL":
                $readonly = "";
                if ($edit_mode == "SAFE" && ($this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || ReporticoApp::getConfig('safe_design_mode'))) {
                    $readonly = "readonly";
                }

                $text .= '<textarea class="' . $this->query->getBootstrapStyle('textfield') . '" ' . $readonly . ' cols="70" rows="4" name="set_' . $this->id . "_" . $showtag . $shadow . '" >';
                $text .= htmlspecialchars($val);
                $text .= '</textarea>';
                break;

            case "DROPDOWN":
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $tagvals, $val, false, $translateoptions);
                break;

            case "CRITERIA":
                $keys = array_keys($this->query->lookup_queries);
                if (!is_array($keys)) {
                    $key = array();
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "GROUPCOLUMNS":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "REPORT_BODY";
                if (is_array($q->columns)) {
                    foreach ($q->columns as $col) {
                        $keys[] = $col->query_name;
                    }
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "CRITERIAWIDGETS":

                $keys = array();
                foreach ( $this->query->assetManager->availableAssets as $k => $widget){
                    $config = $widget->getConfig();

                    $order = isset($config["order"]) ? str_pad($config["order"],5, "0" ) : "99999";
                    if ( isset($config["renderType"]) && !in_array($config["renderType"], $keys)){
                        $keys[$order."-".$config["name"]] = $config["renderType"];
                    }
                }

                ksort($keys);

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "CRITERIARENDERS":

                $keys = array();
                foreach ( $this->query->assetManager->availableAssets as $k => $widget){
                    $config = $widget->getConfig();

                    $order = isset($config["order"]) ? str_pad($config["order"],5, "0" ) : "99999";
                    if ( isset($config["sourceType"]) && !in_array($config["sourceType"], $keys)){
                        $keys[$order."-".$config["name"]] = $config["sourceType"];
                    }
                }

                ksort($keys);

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;


            case "REPORTLIST":
                $keys = array();
                $keys[] = "";
                if (is_dir($this->query->reports_path)) {
                    $testpath = $this->query->reports_path;
                } else {
                    $testpath = ReporticoUtility::findBestLocationInIncludePath($this->query->reports_path);
                }

                if (is_dir($testpath)) {
                    if ($dh = opendir($testpath)) {
                        while (($file = readdir($dh)) !== false) {
                            if (preg_match("/.*\.xml/", $file)) {
                                $keys[] = $file;
                            }

                        }
                        closedir($dh);
                    }
                } else {
                    trigger_error(ReporticoLang::templateXlate("NOOPENDIR") . $this->query->reports_path, E_USER_NOTICE);
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);

                break;

            case "FONTLIST":
                $keys = array();
                $keys[] = "";

                if ($this->query->pdf_engine == "fpdf") {
                    //$fontdir = "src/fpdf/font";
                    $fontdir = "vendor/setasign/fpdf/font";
                } else if ($this->query->pdf_engine == "phantomjs"){
                    //$fontdir = "src/tcpdf/fonts";
                    $fontdir = false;
                } else if ($this->query->pdf_engine == "chromium"){
                    //$fontdir = "src/tcpdf/fonts";
                    $fontdir = false;
                } else {
                    $fontdir = "src/tcpdf/fonts";
                    $fontdir = __DIR__."/../vendor/tecnickcom/tcpdf/fonts";
                }

                if ( $fontdir )
                {
                if (is_dir($fontdir)) {
                    $testpath = $fontdir;
                } else {
                    $testpath = ReporticoUtility::findBestLocationInIncludePath($fontdir);
                }

                if (is_dir($testpath)) {
                    if ($dh = opendir($testpath)) {
                        while (($file = readdir($dh)) !== false) {
                            if (preg_match("/.*\.php/", $file)) {
                                $keys[] = preg_replace("/.php/", "", $file);
                            }
                        }
                        sort($keys);
                        closedir($dh);
                    }
                } else {
                    trigger_error(ReporticoLang::templateXlate("NOOPENDIR") . $fontdir, E_USER_NOTICE);
                }

                if (!in_array($val, $keys)) {
                    $keys[] = $val;
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                }

                break;

            case "STYLELOCTYPES":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "CELL";
                $keys[] = "ALLCELLS";
                $keys[] = "COLUMNHEADERS";
                $keys[] = "ROW";
                $keys[] = "PAGE";
                $keys[] = "BODY";
                $keys[] = "GROUPHEADERLABEL";
                $keys[] = "GROUPHEADERVALUE";
                $keys[] = "GROUPTRAILER";
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "FONTSTYLES":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "NONE";
                $keys[] = "BOLD";
                $keys[] = "ITALIC";
                $keys[] = "BOLDANDITALIC";
                $keys[] = "UNDERLINE";
                $keys[] = "NORMAL";
                $keys[] = "STRIKETHROUGH";
                $keys[] = "OVERLINE";
                $keys[] = "BLINK";
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "POSITIONS":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "";
                $keys[] = "RELATIVE";
                $keys[] = "ABSOLUTE";
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "BORDERSTYLES":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "NOBORDER";
                $keys[] = "NONE";
                $keys[] = "SOLIDLINE";
                $keys[] = "DOTTED";
                $keys[] = "DASHED";
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "AGGREGATETYPES":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "";
                $keys[] = "SUM";
                $keys[] = "AVERAGE";
                $keys[] = "MIN";
                $keys[] = "MAX";
                $keys[] = "PREVIOUS";
                $keys[] = "COUNT";
                $keys[] = "SKIPLINE";
                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "QUERYCOLUMNS":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                if ($q && is_array($q->columns)) {
                    foreach ($q->columns as $col) {
                        $keys[] = $col->query_name;
                    }
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "QUERYCOLUMNSOPTIONAL":
                if (!$this->current_criteria_name) {
                    $q = &$this->query;
                } else {
                    $q = &$this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
                }

                $keys = array();
                $keys[] = "";
                if (is_array($q->columns)) {
                    foreach ($q->columns as $col) {
                        $keys[] = $col->query_name;
                    }
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;

            case "QUERYGROUPS":
                $q = &$this->query;

                $keys = array();
                if (is_array($q->columns)) {
                    foreach ($q->groups as $col) {
                        $keys[] = $col->group_name;
                    }
                }

                $text .= $this->drawArrayDropdown("set_" . $this->id . "_" . $showtag . $shadow, $keys, $val, false, $translateoptions);
                break;
        }

        $text .= '<TD class="reportico-maintain-set-field" colspan="1">';
        if ($default) {
            if ( $translateoptions ) 
                $text .= '&nbsp;(' . ReporticoLang::templateXlate($default) . ')';
            else
                $text .= '&nbsp;(' . $default . ')';
        } else {
            $text .= '&nbsp;';
        }

        $text .= '</TD>';

        if ($partial = ReporticoUtility::getRequestItem("partialMaintain", false)) {
            $arr = explode("_", $partial);
            if (count($arr) > 1) {
                $partial = $arr[1];
            }
        }

        if ($tagct == 1 || ( ( ( preg_match("/$partial/", $tag) && !$this->partial_apply_drawn )  || $partial == $tag ) && $partial != "ANY")) {

            $this->partial_apply_drawn = true;

            $text .= "\n<!-- TAG 1-->";
            $text .= '<TD colspan="1">';
            if ($type != "TEXTFIELDNOOK") {
                $text .= '<input class="' . $this->query->getBootstrapStyle('design_ok') . 'reportico-maintain-button reportico-submit" type="submit" name="submit_' . $this->id . '_SET" value="' . ReporticoLang::templateXlate("OK") . '">';
            } else {
                $text .= "&nbsp;";
            }

            $text .= '</TD>';
        }
        $text .= '</TR>';

        return $text;
    }

    public function drawArrayDropdown($name, $ar, $val, $addblank, $translateoptions, $keysforid = false)
    {
        $text = "";

        if (count($ar) == 0) {
            $text .= '<input type="text" size="40%" name="' . $name . '" value="' . htmlspecialchars($val) . '"><br>';
            return;
        }

        $text .= '<SELECT class="' . $this->query->getBootstrapStyle('design_dropdown') . 'reportico-drop-select-regular" name="' . $name . '">';

        if ($addblank) {
            if (!$val) {
                $text .= '<OPTION selected label="" value=""></OPTION>';
            } else {
                $text .= '<OPTION label="" value=""></OPTION>';
            }
        }

        foreach ($ar as $k => $v) {
            $label = $v;
            if ($translateoptions) {
                $label = ReporticoLang::templateXlate($v);
            }

            $idval = $v;
            if ($keysforid) {
                $idval = $k;
            }

            if ($idval == $val) {
                $text .= '<OPTION selected label="' . $label . '" value="' . $idval . '">' . $label . '</OPTION>';
            } else {
                $text .= '<OPTION label="' . $label . '" value="' . $idval . '">' . $label . '</OPTION>';
            }

        }
        $text .= '</SELECT>';

        return $text;
    }

    public function applyPdfStyles($type, &$updates, $applyto)
    {
        $styletxt = "";
        if ($updates["${type}StyleFgColor"]) {
            $styletxt .= "color: " . $updates["${type}StyleFgColor"] . ";";
        }

        if ($updates["${type}StyleBgColor"]) {
            $styletxt .= "background-color:" . $updates["${type}StyleBgColor"] . ";";
        }

        if ($updates["${type}StyleFontName"]) {
            $styletxt .= "font-family:" . $updates["${type}StyleFontName"] . ";";
        }

        if ($updates["${type}StyleFontSize"]) {
            $styletxt .= "font-size:" . $updates["${type}StyleFontSize"] . ";";
        }

        if ($updates["${type}StyleWidth"]) {
            $styletxt .= "width:" . $updates["${type}StyleWidth"] . ";";
        }

        if ($updates["${type}StyleFontStyle"] && $updates["${type}StyleFontStyle"] != "NONE") {
            $stylevalue = "none";
            if ($updates["${type}StyleFontStyle"] == "BOLD" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-weight:bold;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "ITALIC" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-style:italic;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "NORMAL") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "font-style:normal;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "UNDERLINE") {
                if ($updates["${type}StyleFontStyle"] == "UNDERLINE") {
                    if ($updates["${type}StyleFontStyle"]) {
                        $styletxt .= "text-decoration:underline;";
                    }
                }
            }

            if ($updates["${type}StyleFontStyle"] == "OVERLINE") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:overline;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "BLINK") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:blink;";
                }
            }

            if ($updates["${type}StyleFontStyle"] == "STRIKETHROUGH") {
                if ($updates["${type}StyleFontStyle"]) {
                    $styletxt .= "text-decoration:line-through;";
                }
            }

        }

        if (!$updates["${type}StyleBorderStyle"] || $updates["${type}StyleBorderStyle"] == "NOBORDER") {
            if ($updates["${type}StyleBorderSize"] || $updates["${type}StyleBorderColor"]) {
                trigger_error(ReporticoLang::templateXlate("SETBORDERSTYLE"), E_USER_ERROR);
            }

        } else {
            $stylevalue = "none";
            if ($updates["${type}StyleBorderStyle"] == "SOLIDLINE") {
                $stylevalue = "solid";
            }

            if ($updates["${type}StyleBorderStyle"] == "DASHED") {
                $stylevalue = "dashed";
            }

            if ($updates["${type}StyleBorderStyle"] == "DOTTED") {
                $stylevalue = "dotted";
            }

            $styletxt .= "border-style:$stylevalue;";
            if ($updates["${type}StyleBorderSize"]) {
                $styletxt .= "border-width:" . $updates["${type}StyleBorderSize"] . ";";
            }

            if ($updates["${type}StyleBorderColor"]) {
                $styletxt .= "border-color:" . $updates["${type}StyleBorderColor"] . ";";
            }

        }

        if ($updates["${type}StylePadding"]) {
            $styletxt .= "padding:" . $updates["${type}StylePadding"] . ";";
        }

        if ($updates["${type}StyleBackgroundImage"]) {
            $styletxt .= "background-image:" . $updates["${type}StyleBackgroundImage"] . ";";
        }

        if ($updates["${type}StyleMargin"]) {
            $styletxt .= "margin:" . $updates["${type}StyleMargin"] . ";";
        }

        if ($styletxt) {
            $applyto = preg_replace("/{STYLE [^}]*}/", "", $applyto);
            $applyto .= "{STYLE $styletxt}";
        }
        return $applyto;
    }

/*
function & pdf_styles_wizard($in_parent, $type)
{
$text = "";

$tagct = 1;
$tmpid = $this->id;
$this->id = $in_parent;
$blocktype = "assignTypeStyle";
$text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.ReporticoLang::templateXlate("OUTPUTSTYLESWIZARD").'</b></TD></TR>';
$val = false;

// Extract existing styles into wizard elements
$styles = array(
"color" => false,
"background-color" => false,
"border-style" => false,
"border-width" => false,
"border-color" => false,
"margin" => false,
"padding" => false,
"width" => false,
"font-family" => false,
"font-size" => false,
"font-style" => false,
"background-image" => false,
);
if ( $this->wizard_linked_to )
{
if (preg_match("/{STYLE[ ,]*([^}].*)}/", $this->wizard_linked_to , $matches))
{
if ( isset($matches[1]))
{
$stylearr = explode(";",$matches[1]);
foreach ($stylearr as $v )
{
$element = explode(":",$v);
if ( $element && isset($element[1]))
{
$styles[$element[0]] = trim($element[1]);
}
}
}
}
}
if ( $styles["border-style"] )
{
if ( $styles["border-style"] == "noone" ) $styles["border-style"] = "NONE";
if ( $styles["border-style"] == "solid" ) $styles["border-style"] = "SOLIDLINE";
if ( $styles["border-style"] == "dotted" ) $styles["border-style"] = "DOTTED";
if ( $styles["border-style"] == "dashed" ) $styles["border-style"] = "DASHED";
}

if ( $styles["font-style"] )
{
if ( $styles["font-style"] == "noone" ) $styles["border-style"] = "NONE";
}

$text .= $this->display_maintain_field("${type}StyleFgColor", $styles["color"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleBgColor", $styles["background-color"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleBorderStyle", $styles["border-style"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleBorderSize", $styles["border-width"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleBorderColor", $styles["border-color"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleMargin", $styles["margin"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StylePadding", $styles["padding"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleWidth", $styles["width"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleFontName", $styles["font-family"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleFontSize", $styles["font-size"], $tagct, true, false, $blocktype); $tagct++;
$text .= $this->display_maintain_field("${type}StyleFontStyle", $styles["font-style"], $tagct, true, false, $blocktype); $tagct++;

if ( $type == "PageHeader" || $type == "PageFooter" || $type == "GroupHeader" || $type == "GroupTrailer" )
{
$text .= $this->display_maintain_field("${type}StyleBackgroundImage", $styles["background-image"], $tagct, true, false, $blocktype); $tagct++;
}

//$tagct = 1;
//$blocktype = "assignTypeDbg";
//$text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.ReporticoLang::templateXlate("DATABASEGRAPHICWIZARD").'</b></TD></TR>';
//$text .= $this->display_maintain_field("AssignGraphicBlobCol", false, $tagct, true, false, $blocktype); $tagct++;
//$text .= $this->display_maintain_field("AssignGraphicBlobTab", false, $tagct, true, false, $blocktype); $tagct++;
//$text .= $this->display_maintain_field("AssignGraphicBlobMatch", false, $tagct, true, false, $blocktype); $tagct++;
//$text .= $this->display_maintain_field("AssignGraphicWidth", false, $tagct, true, false, $blocktype); $tagct++;
//$text .= $this->display_maintain_field("AssignGraphicReportCol", false, $tagct, true, false, $blocktype); $tagct++;

$this->id = $tmpid;

return $text;
}
 */
    public function &assignment_aggregates($in_parent)
    {
        $text = "";

        $tagct = 1;
        $tmpid = $this->id;
        $this->id = $in_parent;
        $text .= '<TR><TD>&nbsp;</TD></TD>';
        $blocktype = "assignTypeImageUrl";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("OUTPUTIMAGE") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("AssignImageUrl", false, $tagct, true, false, $blocktype);
        $tagct++;

        $tagct = 1;
        $tmpid = $this->id;
        $this->id = $in_parent;
        $blocktype = "assignTypeHyper";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("OUTPUTHYPERLINK") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("AssignHyperlinkLabel", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignHyperlinkUrl", false, $tagct, true, false, $blocktype);
        $tagct++;

        $tagct = 1;
        $tmpid = $this->id;
        $this->id = $in_parent;
        $blocktype = "assignTypeStyle";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("OUTPUTSTYLESWIZARD") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("AssignStyleLocType", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleFgColor", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleBgColor", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleBorderStyle", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleBorderSize", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleBorderColor", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleMargin", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStylePadding", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleWidth", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleFontName", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleFontSize", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignStyleFontStyle", false, $tagct, true, false, $blocktype);
        $tagct++;

        $tagct = 1;
        $tmpid = $this->id;
        $this->id = $in_parent;
        $blocktype = "assignTypeAgg";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("AGGREGATESWIZARD") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("AssignAggType", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignAggCol", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignAggGroup", false, $tagct, true, false, $blocktype);
        $tagct++;

        $tagct = 1;
        $blocktype = "assignTypeDbg";
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">+</a><b>' . ReporticoLang::templateXlate("DATABASEGRAPHICWIZARD") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("AssignGraphicBlobCol", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignGraphicBlobTab", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignGraphicBlobMatch", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignGraphicWidth", false, $tagct, true, false, $blocktype);
        $tagct++;
        $text .= $this->display_maintain_field("AssignGraphicReportCol", false, $tagct, true, false, $blocktype);
        $tagct++;

        $tagct = 1;
        $blocktype = "assignTypeDrill";
        $text .= '<TR><TD>&nbsp;</TD></TD>';
        $text .= '<TR><TD class="reportico-maintain-set-field"><a class="reportico-toggle" id="' . $blocktype . '" href="javascript:toggleLine(\'' . $blocktype . '\')">-</a><b>' . ReporticoLang::templateXlate("DRILLDOWNWIZARD") . '</b></TD></TR>';
        $text .= $this->display_maintain_field("DrilldownReport", $this->query->drilldown_report, $tagct, true, false, $blocktype, true);

        $tagct++;
        if ($this->query->drilldown_report) {
            $q = new Reportico();
            $q->projects_folder = $this->query->projects_folder;
            $q->reports_path = $q->projects_folder . "/" . ReporticoApp::getConfig("project");
            $reader = new XmlReader($q, $this->query->drilldown_report, false);
            $reader->xml2query();
            foreach ($q->lookup_queries as $k => $v) {

                $text .= $this->display_maintain_field("DrilldownColumn" . " " . $v->query_name, false, $tagct, false, ReporticoLang::templateXlate("DRILLDOWNCOLUMN") . " " . $v->query_name, $blocktype, true);
            }
            unset($q);
        }

        $this->id = $tmpid;

        return $text;
    }

    /**
     * Functon: panel_key_to_html_row
     *
     * Generates a horizontal menu of buttons for a set of panel items
     * E.g. draws Format..Query Details..Assignments etc
     *
     */
    public function &panel_key_to_html_row($id, &$ar, $labtext, $labindex)
    {
        $text = "";
        $text .= '<TR>';
        foreach ($ar as $key => $val) {
            $text .= '<TD>';

            $padstring = $id . str_pad($key, 4, "0", STR_PAD_LEFT);
            if ($labindex == "_key") {
                $text .= $this->draw_show_hide_button($padstring, $labtext . " " . $key);
            } else {
                $text .= $this->draw_show_hide_button($padstring, $labtext . " " . $val[$labindex]);
            }

            $text .= $this->draw_delete_button($padstring);
            $text .= '</TD>';
        }
        $text .= '</TR>';

        return $text;
    }

    /**
     * Functon: panel_key_to_html
     *
     * Generates a vertical menu of buttons for a set of panel items
     * E.g. draws assignment and criteria buttons in left hand panel
     *
     */
    public function &panel_key_to_html($id, &$ar, $paneltype, $labindex,
        $draw_move_buttons = false, $draw_delete_button = true) {
        $text = "";
        $text .= '<TD valign="top" class="reportico-maintain-mid-section">';
        $text .= '<DIV class="side-nav-container affix-top">';
        $text .= '<UL class="' . $this->query->getBootstrapStyle('vtabs') . 'reportico-maintain-mid-sectionTable">';

        $defaulttext = ReporticoLang::templateXlate($paneltype);
        $ct = 0;
        foreach ($ar as $key => $val) {
            $drawup = false;
            $drawdown = false;
            if ($draw_move_buttons) {
                if ($ct > 0) {
                    $drawup = true;
                }

                if ($ct < count($ar) - 1) {
                    $drawdown = true;
                }

            }

            $padstring = $id . str_pad($key, 4, "0", STR_PAD_LEFT);
            // Choose button label to reflect a numeric or named element
            if ($labindex == "_key") {
                $labtext = $defaulttext . " " . $key;
            } else {
                $labtext = $defaulttext . " " . $val[$labindex];
            }

            // For assignments the button can be a column assignment or a style assignment .. choose the appropriate
            // label

            if ($paneltype == "ASSIGNMENT") {
                if (preg_match("/applyStyle *\([ \"']*CELL/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("CELLSTYLE") . " " . $val[$labindex];
                } else if (preg_match("/applyStyle *\([ \"']*ROW/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("ROWSTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*PAGE/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("PAGESTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*BODY/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("REPORTBODYSTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*ALLCELLS/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("ALLCELLSSTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*COLUMNHEADERS/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("COLUMNHEADERSTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*GROUPHEADERLABEL/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("GRPHEADERLABELSTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*GROUPHEADERVALUE/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("GRPHEADERVALUESTYLE");
                } else if (preg_match("/applyStyle *\([ \"']*GROUPTRAILER/", $val["Expression"])) {
                    $labtext = ReporticoLang::templateXlate("GROUPTRAILERSTYLE");
                }

            }

            $text .= $this->draw_show_hide_vtab_button($padstring, $labtext, $drawup, $drawdown, $draw_delete_button);
            $ct++;
        }
        //$text .= '<TR><TD>&nbsp;</TD></TR>';
        $text .= '</UL>';
        $text .= '</DIV>';
        $text .= '</TD>';

        return $text;
    }

    /**
     * Functon: xml2query
     *
     * Analyses XML report definition and builds Reportico report instance from it
     *
     */
    public function xml2query()
    {
        if (array_key_exists("CogModule", $this->data)) {
            $q = &$this->data["CogModule"];
        } else {
            $q = &$this->data["Report"];
        }

        $criteria_links = array();

        // Generate Output Information...
        $ds = false;
        if (!$q) {
            return;
        }

        foreach ($q as $cogquery) {
            // Set Query Attributes
            foreach ($cogquery["Format"] as $att => $val) {
                $this->query->setAttribute($att, $val);
            }

            // Set DataSource
            if (isset($cogquery["Datasource"])) {
                foreach ($cogquery["Datasource"] as $att => $val) {
                    //if ( $att == "SourceType" )
                    //$this->query->source_type = $val;

                    if ($att == "SourceConnection") {
                        // No longer relevant - connections are not supplied in xml files
                    }
                }
            }

            // Set Query Columns
            if (!($ef = &$this->getArrayElement($cogquery, "EntryForm"))) {
                $this->ErrorMsg = "No EntryForm tag within Format";
                return false;
            }

            if (!($qu = &$this->getArrayElement($ef, "Query"))) {
                $this->ErrorMsg = "No Query tag within EntryForm";
                return false;
            }

            $this->query->table_text = $this->getArrayElement($qu, "TableSql");
            $this->query->where_text = $this->getArrayElement($qu, "WhereSql");
            $this->query->group_text = $this->getArrayElement($qu, "GroupSql");
            $this->query->rowselection = $this->getArrayElement($qu, "RowSelection");
            $has_cols = true;

            if (($qc = &$this->getArrayElement($qu, "SQL"))) {
                $this->query->sql_raw = $this->getArrayElement($qc, "SQLRaw");
            }

            if (!($qc = &$this->getArrayElement($qu, "QueryColumns"))) {
                $this->ErrorMsg = "No QueryColumns tag within Query";
                $has_cols = false;
            }

            // Generate QueryColumn for each column found
            if ($has_cols) {
                foreach ($qc as $col) {
                    $in_query = true;
                    if (!$col["ColumnName"]) {
                        $in_query = false;
                    }

                    $this->query->createCriteriaColumn
                    (
                        $col["Name"],
                        $col["TableName"],
                        $col["ColumnName"],
                        $col["ColumnType"],
                        $col["ColumnLength"],
                        "###.##",
                        $in_query
                    );

                    // Set any Attributes
                    if (($fm = &$this->getArrayElement($col, "Format"))) {
                        foreach ($fm as $att => $val) {
                            $this->query->setColumnAttribute($col["Name"], $att, $val);
                        }
                    }
                }

                // Generate Order By List
                if (($oc = &$this->getArrayElement($qu, "OrderColumns"))) {
                    // Generate QueryColumn for each column found
                    foreach ($oc as $col) {
                        if (!$col["Name"]) {
                            return;
                        }

                        $this->query->createQueryColumn
                        (
                            $col["Name"],
                            $col["OrderType"]
                        );
                    }
                }

                // Generate Query Assignments
                if (($as = &$this->getArrayElement($ef, "Assignments"))) {
                    foreach ($as as $col) {
                        if (array_key_exists("AssignName", $col)) {
                            $this->query->addAssignment($col["AssignName"], $col["Expression"], $col["Condition"]);
                        } else {
                            $this->query->addAssignment($col["Name"], $col["Expression"], $col["Condition"]);
                        }

                    }
                }
            }

            // Generate Query Assignments
            if (($pq = &$this->getArrayElement($qu, "PreSQLS"))) {
                foreach ($pq as $col) {
                    $this->query->addPreSql($col["SQLText"]);
                }
            }

            // Generate Output Information...
            if (($op = &$this->getArrayElement($ef, "Output"))) {
                // Generate Page Headers
                if (($ph = $this->getArrayElement($op, "PageHeaders"))) {
                    foreach ($ph as $k => $phi) {
                        $this->query->createPageHeader($k, $phi["LineNumber"], $phi["HeaderText"]);
                        if (($fm = &$this->getArrayElement($phi, "Format"))) {
                            foreach ($fm as $att => $val) {
                                $this->query->setPageHeaderAttribute($k, $att, $val);
                            }
                        }

                    }
                }

                // Generate Page Footers
                if (($ph = $this->getArrayElement($op, "PageFooters"))) {
                    foreach ($ph as $k => $phi) {
                        $this->query->createPageFooter($k, $phi["LineNumber"], $phi["FooterText"]);
                        if (($fm = &$this->getArrayElement($phi, "Format"))) {
                            foreach ($fm as $att => $val) {
                                $this->query->setPageFooterAttribute($k, $att, $val);
                            }
                        }

                    }
                }

                // Generate Display Orders
                if ($has_cols && ($ph = $this->getArrayElement($op, "DisplayOrders"))) {
                    foreach ($ph as $k => $phi) {
                        $this->query->setColumnOrder($phi["ColumnName"], $phi["OrderNumber"]);
                    }
                }

                if ($has_cols && ($ph = $this->getArrayElement($op, "Groups"))) {
                    foreach ($ph as $k => $phi) {
                        if (array_key_exists("GroupName", $phi)) {
                            $gpname = $phi["GroupName"];
                        } else {
                            $gpname = $phi["Name"];
                        }

                        $grn = $this->query->createGroup($gpname);

                        if (array_key_exists("BeforeGroupHeader", $phi)) {
                            $grn->setAttribute("before_header", $phi["BeforeGroupHeader"]);
                            $grn->setAttribute("after_header", $phi["AfterGroupHeader"]);
                            $grn->setAttribute("before_trailer", $phi["BeforeGroupTrailer"]);
                            $grn->setAttribute("after_trailer", $phi["AfterGroupTrailer"]);
                            $grn->setFormat("before_header", $phi["BeforeGroupHeader"]);
                            $grn->setFormat("after_header", $phi["AfterGroupHeader"]);
                            $grn->setFormat("before_trailer", $phi["BeforeGroupTrailer"]);
                            $grn->setFormat("after_trailer", $phi["AfterGroupTrailer"]);
                        }

                        if (($gp = &$this->getArrayElement($phi, "GroupHeaders"))) {
                            foreach ($gp as $att => $val) {
                                if (!isset($val["GroupHeaderCustom"])) {
                                    $val["GroupHeaderCustom"] = false;
                                }

                                if (!isset($val["ShowInHTML"])) {
                                    $val["ShowInHTML"] = "yes";
                                }

                                if (!isset($val["ShowInPDF"])) {
                                    $val["ShowInPDF"] = "yes";
                                }

                                $this->query->createGroupHeader($gpname, $val["GroupHeaderColumn"], $val["GroupHeaderCustom"], $val["ShowInHTML"], $val["ShowInPDF"]);
                            }
                        }

                        if (($gp = &$this->getArrayElement($phi, "GroupTrailers"))) {
                            foreach ($gp as $att => $val) {
                                if (!isset($val["GroupTrailerCustom"])) {
                                    $val["GroupTrailerCustom"] = false;
                                }

                                if (!isset($val["ShowInHTML"])) {
                                    $val["ShowInHTML"] = "yes";
                                }

                                if (!isset($val["ShowInPDF"])) {
                                    $val["ShowInPDF"] = "yes";
                                }

                                $this->query->createGroupTrailer($gpname, $val["GroupTrailerDisplayColumn"],
                                    $val["GroupTrailerValueColumn"],
                                    $val["GroupTrailerCustom"], $val["ShowInHTML"], $val["ShowInPDF"]);
                            }
                        }

                    }
                }

                // Generate Graphs
                if ($has_cols && ($gph = $this->getArrayElement($op, "Graphs"))) {
                    foreach ($gph as $k => $gphi) {
                        $ka = array_keys($gphi);
                        $gph = &$this->query->createGraph();

                        $gph->setGraphColumn($gphi["GraphColumn"]);

                        $gph->setTitle($gphi["Title"]);
                        $gph->setXtitle($gphi["XTitle"]);
                        $gph->setXlabelColumn($gphi["XLabelColumn"]);
                        $gph->setYtitle($gphi["YTitle"]);
                        //$gph->setYlabelColumn($gphi["YLabelColumn"]);
                        //////HERE!!!
                        if (array_key_exists("GraphWidth", $gphi)) {
                            $gph->setWidth($gphi["GraphWidth"]);
                            $gph->setHeight($gphi["GraphHeight"]);
                            $gph->setWidthPdf($gphi["GraphWidthPDF"]);
                            $gph->setHeightPdf($gphi["GraphHeightPDF"]);
                        } else {
                            $gph->setWidth($gphi["Width"]);
                            $gph->setHeight($gphi["Height"]);
                        }

                        if (array_key_exists("GraphColor", $gphi)) {
                            $gph->setGraphColor($gphi["GraphColor"]);
                            $gph->setGrid($gphi["GridPosition"],
                                $gphi["XGridDisplay"], $gphi["XGridColor"],
                                $gphi["YGridDisplay"], $gphi["YGridColor"]
                            );
                            $gph->setTitleFont($gphi["TitleFont"], $gphi["TitleFontStyle"],
                                $gphi["TitleFontSize"], $gphi["TitleColor"]);
                            $gph->setXtitleFont($gphi["XTitleFont"], $gphi["XTitleFontStyle"],
                                $gphi["XTitleFontSize"], $gphi["XTitleColor"]);
                            $gph->setYtitleFont($gphi["YTitleFont"], $gphi["YTitleFontStyle"],
                                $gphi["YTitleFontSize"], $gphi["YTitleColor"]);
                            $gph->setXaxis($gphi["XTickInterval"], $gphi["XTickLabelInterval"], $gphi["XAxisColor"]);
                            $gph->setYaxis($gphi["YTickInterval"], $gphi["YTickLabelInterval"], $gphi["YAxisColor"]);
                            $gph->setXaxisFont($gphi["XAxisFont"], $gphi["XAxisFontStyle"],
                                $gphi["XAxisFontSize"], $gphi["XAxisFontColor"]);
                            $gph->setYaxisFont($gphi["YAxisFont"], $gphi["YAxisFontStyle"],
                                $gphi["YAxisFontSize"], $gphi["YAxisFontColor"]);
                            $gph->setMarginColor($gphi["MarginColor"]);
                            $gph->setMargins($gphi["MarginLeft"], $gphi["MarginRight"],
                                $gphi["MarginTop"], $gphi["MarginBottom"]);
                        }
                        foreach ($gphi["Plots"] as $pltk => $pltv) {
                            $pl = &$gph->createPlot($pltv["PlotColumn"]);
                            $pl["type"] = $pltv["PlotType"];
                            $pl["fillcolor"] = $pltv["FillColor"];
                            $pl["linecolor"] = $pltv["LineColor"];
                            $pl["legend"] = $pltv["Legend"];
                        }
                    }
                }

            } // Output

            // Check for Criteria Items ...

            if (($crt = &$this->getArrayElement($ef, "Criteria"))) {
                foreach ($crt as $ci) {
                    $critnm = $this->getArrayElement($ci, "Name");

                    $crittb = $this->getArrayElement($ci, "QueryTableName");
                    $critcl = $this->getArrayElement($ci, "QueryColumnName");
                    $linked_report = $this->getArrayElement($ci, "LinkToReport");
                    $linked_report_item = $this->getArrayElement($ci, "LinkToReportItem");

                    // If we are not designing a report then
                    // replace a linked criteria with the criteria
                    // item it links to from another report
                    if ($linked_report && $this->query->execute_mode != "MAINTAIN") {
                        $q = new Reportico();
                        $q->reports_path = $q->projects_folder . "/" . ReporticoApp::getConfig("project");
                        $reader = new XmlReader($q, $linked_report, false);
                        $reader->xml2query();

                        foreach ($q->lookup_queries as $k => $v) {

                            $found = false;
                            foreach ($this->query->columns as $querycol) {
                                if ($querycol->query_name == $v->query_name) {
                                    $found = true;
                                }
                            }

                            $qu = new Reportico();
                            if ($linked_report_item == $v->query_name) {
                                $this->query->lookup_queries[$v->query_name] = $v;
                            }

                        }
                        continue;
                    }

                    if ($crittb) {
                        $critcl = $crittb . "." . $critcl;
                        $crittb = "";
                    }
                    $crittp = $this->getArrayElement($ci, "CriteriaType");
                    $critlt = $this->getArrayElement($ci, "CriteriaList");
                    $crituse = $this->getArrayElement($ci, "Use");
                    $critds = $this->getArrayElement($ci, "CriteriaDisplay");
                    $critexp = $this->getArrayElement($ci, "ExpandDisplay");
                    $critmatch = $this->getArrayElement($ci, "MatchColumn");
                    $critdefault = $this->getArrayElement($ci, "CriteriaDefaults");
                    $crithelp = $this->getArrayElement($ci, "CriteriaHelp");
                    $crittitle = $this->getArrayElement($ci, "Title");
                    $crit_required = $this->getArrayElement($ci, "CriteriaRequired");
                    $crit_hidden = $this->getArrayElement($ci, "CriteriaHidden");
                    $crit_display_group = $this->getArrayElement($ci, "CriteriaDisplayGroup");
                    $crit_lookup_return = $this->getArrayElement($ci, "ReturnColumn");
                    $crit_lookup_display = $this->getArrayElement($ci, "DisplayColumn");
                    $crit_criteria_display = $this->getArrayElement($ci, "OverviewColumn");

                    if ($crittp == "ANYCHAR") {
                        $crittp = "TEXTFIELD";
                    }

                    if ($critds == "ANYCHAR") {
                        $critds = "TEXTFIELD";
                    }

                    if ($critexp == "ANYCHAR") {
                        $critexp = "TEXTFIELD";
                    }

                    // Generate criteria lookup info unless its a link to a criteria in a nother report
                    if (!$linked_report && !($ciq = &$this->getArrayElement($ci, "Query"))) {
                        continue;
                    }

                    $critquery = new Reportico();

                    // Generate Criteria Query Columns
                    if (!$linked_report && ($ciqc = &$this->getArrayElement($ciq, "QueryColumns"))) {
                        foreach ($ciqc as $ccol) {
                            $in_query = true;
                            if (!$ccol["ColumnName"]) {
                                $in_query = false;
                            }

                            if ( !$crit_lookup_display ) $crit_lookup_display = $ccol["Name"];
                            if ( !$crit_lookup_return ) $crit_lookup_return = $ccol["Name"];
                            $critquery->createCriteriaColumn
                            (
                                $ccol["Name"],
                                $ccol["TableName"],
                                $ccol["ColumnName"],
                                $ccol["ColumnType"],
                                $ccol["ColumnLength"],
                                "###.##",
                                $in_query
                            );
                        }
                    }
                    // Generate Order By List
                    if (!$linked_report && ($coc = &$this->getArrayElement($ciq, "OrderColumns"))) {
                        // Generate QueryColumn for each column found
                        foreach ($coc as $col) {
                            $critquery->createOrderColumn
                            (
                                $col["Name"],
                                $col["OrderType"]
                            );
                        }
                    }

                    if (!$linked_report && ($as = &$this->getArrayElement($ciq, "Assignments"))) {
                        foreach ($as as $ast) {
                            if (array_key_exists("AssignName", $ast)) {
                                $critquery->addAssignment($ast["AssignName"], $ast["Expression"], $ast["Condition"]);
                            } else {
                                $critquery->addAssignment($ast["Name"], $ast["Expression"], $ast["Condition"]);
                            }

                        }
                    }

                    // Generate Criteria Links  In Array for later use
                    if (!$linked_report && ($cl = &$this->getArrayElement($ci, "CriteriaLinks"))) {
                        foreach ($cl as $clitem) {
                            $criteria_links[] = array(
                                "LinkFrom" => $clitem["LinkFrom"],
                                "LinkTo" => $clitem["LinkTo"],
                                "LinkClause" => $clitem["LinkClause"],
                            );
                        }
                    }

                    // Set Query SQL Text
                    if (!$linked_report) {
                        $critquery->table_text = $this->getArrayElement($ciq, "TableSql");
                        $critquery->where_text = $this->getArrayElement($ciq, "WhereSql");
                        $critquery->group_text = $this->getArrayElement($ciq, "GroupSql");
                        $critquery->sql_raw = $this->getArrayElement($ciq, "SQLRaw");
                        $critquery->rowselection = $this->getArrayElement($ciq, "RowSelection");
                    }
                    $critquery->setLookupReturn($crit_lookup_return);
                    $critquery->setLookupDisplay($crit_lookup_display, $crit_criteria_display);
                    $critquery->setLookupExpandMatch($critmatch);
                    $this->query->setCriteriaLookup($critnm, $critquery, $crittb, $critcl);
                    $this->query->setCriteriaInput($critnm, $crittp, $critds, $critexp, $crituse);
                    $this->query->setCriteriaLinkReport($critnm, $linked_report, $linked_report_item);

                    $this->query->setCriteriaDefaults($critnm, $critdefault);
                    $this->query->setCriteriaList($critnm, $critlt);
                    //var_dump($crit_required);
                    $this->query->setCriteriaRequired($critnm, $crit_required);
                    $this->query->setCriteriaHidden($critnm, $crit_hidden);
                    $this->query->setCriteriaDisplayGroup($critnm, $crit_display_group);
                    //$this->query->setCriteriaHelp($critnm, $crithelp);
                    $this->query->setCriteriaAttribute($critnm, "column_title", $crittitle);

                    $this->query->setCriteriaHelp($critnm, $crithelp);

                } // End Criteria Item

                // Set up any Criteria Links
                foreach ($criteria_links as $cl) {
                    $this->query->setCriteriaLink($cl["LinkFrom"], $cl["LinkTo"], $cl["LinkClause"]);
                }
            }
        }

    }
}


