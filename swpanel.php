<?php
/*

 Reportico - PHP R2eporting Tool
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

 * File:        swpanel.php
 *
 * This module provides functionality for reading and writing
 * xml reporting. 
 * It also controls browser output through Smarty templating class
 * for the different report modes MENU, PREPARE, DESIGN and
 * EXECUTE
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swpanel.php,v 1.40 2014/05/17 15:12:32 peter Exp $
 */


/* $Id $ */

/**
 * Class reportico_panel
 *
 * Class for storing the hierarchy of content that will be
 * displayed through the browser when running Reportico
 * 
 */
class reportico_panel
{
	var $panel_type;
	var $query = NULL;
	var $visible = false;
	var $pre_text = "";
	var $body_text = "";
	var $post_text = "";
	var $full_text = "";
	var $program = "";
	var $panels = array();
	var $smarty = false;
	var $reportlink_report = false;
	var $reportlink_report_item = false;

	function __construct(&$in_query, $in_type)
	{
		$this->query = &$in_query;
		$this->panel_type = $in_type;
	}

	function set_smarty(&$in_smarty)
	{
		$this->smarty = &$in_smarty;
	}

	function set_menu_item($in_program, $in_text)
	{
        // Dont specify xml extensions in menu options
        $in_program = preg_replace("/\.xml\$/", "", $in_program);

		$this->program = $in_program;
		$this->text = $in_text;

		$cp = new reportico_panel($this->query, "MENUITEM");
		$cp->visible = true;
		$this->panels[] =& $cp;
		$cp->program = $in_program;
		$cp->text = $in_text;
	
	}

	function set_project_item($in_program, $in_text)
	{
		$this->program = $in_program;
		$this->text = $in_text;

		$cp = new reportico_panel($this->query, "PROJECTITEM");
		$cp->visible = true;
		$this->panels[] =& $cp;
		$cp->program = $in_program;
		$cp->text = $in_text;
	
	}

	function set_visibility($in_visibility)
	{
		$this->visible = $in_visibility;
	}

	function add_panel(&$in_panel)
	{
		$in_panel->set_smarty($this->smarty);
		$this->panels[] = &$in_panel;
	}

	function draw_smarty($send_to_browser = false)
	{
		$text = "";
		if ( !$this->visible ) 
			return;

		$this->pre_text = $this->pre_draw_smarty();

		// Now draw any panels owned by this panel
		foreach ( $this->panels as $k => $panel )
		{
			$panelref =& $this->panels[$k];
			$this->body_text .= $panelref->draw_smarty();
		}

		$this->post_text = $this->post_draw_smarty();
		$this->full_text = $this->pre_text.$this->body_text.$this->post_text;
		return  $this->full_text;
	}


	function pre_draw_smarty()
	{
		$text = "";
		switch($this->panel_type)
		{
			case "LOGIN":
                if ( defined ('SW_ADMIN_PASSWORD') && SW_ADMIN_PASSWORD == "__OPENACCESS__" )
				    $this->smarty->assign('SHOW_OPEN_LOGIN', true);
                else
				    $this->smarty->assign('SHOW_LOGIN', true);
				break;

			case "LOGOUT":
				if ( !SW_DB_CONNECT_FROM_CONFIG )
				{
					$this->smarty->assign('SHOW_LOGOUT', true);
				}
				break;

			case "MAINTAIN":
				$text .= $this->query->xmlin->xml2html($this->query->xmlin->data);
				break;
			
			case "BODY":
				$this->smarty->assign('EMBEDDED_REPORT',  $this->query->embedded_report);
				break;

			case "MAIN":
				break;

			case "TITLE":
				$reporttitle = sw_translate($this->query->derive_attribute("ReportTitle", "Set Report Title"));
    
                // For Admin options title should be translatable
                // Also for configureproject.xml
				global $g_project;
                if ( $this->query->xmlinput == "configureproject.xml" || $g_project == "admin" )
				    $this->smarty->assign('TITLE', template_xlate($reporttitle));
                else
				    $this->smarty->assign('TITLE', $reporttitle);
	
				$submit_self = $this->query->get_action_url();
				$forward = session_request_item('forward_url_get_parameters', '');
				if ( $forward )
					$submit_self .= "?".$forward;
				$this->smarty->assign('SCRIPT_SELF',  $submit_self);
				break;

			case "CRITERIA":
				$this->smarty->assign('SHOW_CRITERIA', true);
				break;

			case "CRITERIA_FORM":
				$dispcrit = array();
				$ct = 0;
				// Build Select Column List
				$this->query->expand_col = false;
                $lastdisplaygroup = "";
				foreach ( $this->query->lookup_queries as $k => $col )
				{
					if ( $col->criteria_type )
					{
						if ( array_key_exists("EXPAND_".$col->query_name, $_REQUEST) )
							$this->query->expand_col =& $this->query->lookup_queries[$col->query_name];
		
						if ( array_key_exists("EXPANDCLEAR_".$col->query_name, $_REQUEST) )
							$this->query->expand_col =& $this->query->lookup_queries[$col->query_name];

						if ( array_key_exists("EXPANDSELECTALL_".$col->query_name, $_REQUEST) )
							$this->query->expand_col =& $this->query->lookup_queries[$col->query_name];

						if ( array_key_exists("EXPANDSEARCH_".$col->query_name, $_REQUEST) )
							$this->query->expand_col =& $this->query->lookup_queries[$col->query_name];

						$crititle = "";
						if ( $tooltip = $col->derive_attribute("tooltip", false) )
						{
							$title = $col->derive_attribute("column_title", $col->query_name);
							$crittitle = '<a HREF="" onMouseOver="return overlib(\''.$tooltip.
										'\',STICKY,CAPTION,\''.$title.
										'\',DELAY,400);" onMouseOut="nd();" onclick="return false;">'.
										$title.'</A>';
						}
						else
							$crittitle = $col->derive_attribute("column_title", $col->query_name);

						$critsel = $col->format_form_column();
                        if ( $col->hidden == "yes" )
                            $crithidden = true;
                        else
                            $crithidden = false;
                        $critdisplaygroup = $col->display_group;
                        if ( $col->required == "yes" )
                            $critrequired = true;
                        else
                            $critrequired = false;
						$critexp = false;

						if ( $col->expand_display && $col->expand_display != "NOINPUT" )
							$critexp = true;

						$dispcrit[] = array (
									"name" => $col->query_name,
									"title" => sw_translate($crittitle),
									"entry" => $critsel,
									"entry" => $critsel,
									"hidden" => $crithidden,
									"last_display_group" => $lastdisplaygroup,
									"display_group" => $critdisplaygroup,
									"display_group_class" => preg_replace("/ /", "_", $critdisplaygroup),
									"required" => $critrequired,
									"expand" => $critexp,
                                    "tooltip" => sw_translate($col->criteria_help)
									);
                        $lastdisplaygroup = $critdisplaygroup;
					}
					$this->smarty->assign("CRITERIA_ITEMS", $dispcrit);
				}
				break;

			case "CRITERIA_EXPAND":
				// Expand Cell Table
				$this->smarty->assign("SHOW_EXPANDED", false);

				if ( $this->query->expand_col )
				{
					$this->smarty->assign("SHOW_EXPANDED", true);
					$this->smarty->assign("EXPANDED_ITEM", $this->query->expand_col->query_name);
					$this->smarty->assign("EXPANDED_SEARCH_VALUE", false);
					$title = $this->query->expand_col->derive_attribute("column_title", $this->query->expand_col->query_name);
					$this->smarty->assign("EXPANDED_TITLE", sw_translate($title));


					// Only use then expand value if Search was press
					$expval="";
					if ( $this->query->expand_col->submitted('MANUAL_'.$this->query->expand_col->query_name) )
					{
							$tmpval=$_REQUEST['MANUAL_'.$this->query->expand_col->query_name];
							if ( strlen($tmpval) > 1 && substr($tmpval, 0, 1) == "?" )
									$expval = substr($tmpval, 1);
							
					}
					if ( $this->query->expand_col->submitted('EXPANDSEARCH_'.$this->query->expand_col->query_name) )
						if ( array_key_exists("expand_value", $_REQUEST) )
						{
							$expval=$_REQUEST["expand_value"];
						}
					$this->smarty->assign("EXPANDED_SEARCH_VALUE", $expval);

					$text .= $this->query->expand_col->expand_template();
				}
				else
				{
					if ( !($desc = sw_translate_report_desc($this->query->xmloutfile)) )
						$desc = $this->query->derive_attribute("ReportDescription", false); 
					$this->smarty->debug = true;
					$this->smarty->assign("REPORT_DESCRIPTION", $desc);
				}
				break;

			case "USERINFO":
				$this->smarty->assign('DB_LOGGEDON', true);
				if ( !SW_DB_CONNECT_FROM_CONFIG )
				{
					$this->smarty->assign('DBUSER', $this->query->datasource->user_name);
				}
				else
					$this->smarty->assign('DBUSER', false);
				break;

			case "RUNMODE":
				if ( $this->query->execute_mode == "MAINTAIN" )
					$this->smarty->assign('SHOW_MODE_MAINTAIN_BOX', true);
				else
                {
                    // In demo mode for reporitco web site allow design
			        if ( $this->query->allow_maintain == "DEMO" )
					    $this->smarty->assign('SHOW_DESIGN_BUTTON', true);

                    // Dont allow design option when configuring project
                    if ( $this->query->xmlinput != "configureproject.xml" && $this->query->xmlinput != "deleteproject.xml" )
					    $this->smarty->assign('SHOW_DESIGN_BUTTON', true);
                    if ( $this->query->xmlinput == "deleteproject.xml" )
                    {
					    $this->smarty->assign('SHOW_ADMIN_BUTTON', true);
					    $this->smarty->assign('SHOW_PROJECT_MENU_BUTTON', false);       
                    }
                    else if ( $this->query->xmlinput == "configureproject.xml" )
                    {
					    $this->smarty->assign('SHOW_ADMIN_BUTTON', true);
                    }
                }

				$create_report_url = $this->query->create_report_url;
				$configure_project_url = $this->query->configure_project_url;
				$forward = session_request_item('forward_url_get_parameters', '');
				if ( $forward )
				{
					$configure_project_url .= "&".$forward;
					$create_report_url .= "&".$forward;
				}
				$this->smarty->assign('CONFIGURE_PROJECT_URL', $configure_project_url);
				$this->smarty->assign('CREATE_REPORT_URL', $create_report_url);

				break;

			case "MENUBUTTON":
				$prepare_url = $this->query->prepare_url;
				$menu_url = $this->query->menu_url;
				$forward = session_request_item('forward_url_get_parameters', '');
				if ( $forward )
                {
					$menu_url .= "&".$forward;
					$prepare_url .= "&".$forward;
                }
				$this->smarty->assign('MAIN_MENU_URL', $menu_url);
				$this->smarty->assign('RUN_REPORT_URL', $prepare_url);

				$admin_menu_url = $this->query->admin_menu_url;
				$forward = session_request_item('forward_url_get_parameters', '');
				if ( $forward )
					$admin_menu_url .= "&".$forward;
				$this->smarty->assign('ADMIN_MENU_URL', $admin_menu_url);
				break;

			case "MENU":
				break;

			case "PROJECTITEM":
				if ( $this->text != ".." && $this->text != "admin" )
				{
					$forward = session_request_item('forward_url_get_parameters', '');
					if ( $forward )
						$forward .= "&";

                    if ( preg_match("/\?/", $this->query->get_action_url()) )
                        $url_join_char = "&";
                    else
                        $url_join_char = "?";
						
					$this->query->projectitems[] = array (
						"label" => $this->text,
						"url" => $this->query->get_action_url().$url_join_char.$forward."execute_mode=MENU&project=".$this->program."&amp;reportico_session_name=".reportico_session_name()
							);
				}
				break;

			case "MENUITEM":
				$forward = session_request_item('forward_url_get_parameters', '');
				if ( $forward )
					$forward .= "&";
                if ( preg_match("/\?/", $this->query->get_action_url()) )
                    $url_join_char = "&";
                else
                    $url_join_char = "?";
						
                if ( $this->text == "TEXT" )
				    $this->query->menuitems[] = array (
						"label" => $this->text,
						"url" => $this->program
							);
                else
				    $this->query->menuitems[] = array (
						"label" => $this->text,
						"url" => $this->query->get_action_url().$url_join_char.$forward."execute_mode=PREPARE&xmlin=".$this->program."&amp;reportico_session_name=".reportico_session_name()
							);
				break;

			case "TOPMENU":
				$this->smarty->assign('SHOW_TOPMENU', true);
				break;

			case "DESTINATION":

				$this->smarty->assign('SHOW_OUTPUT', true);

				if ( defined("SW_ALLOW_OUTPUT" ) && !SW_ALLOW_OUTPUT )
					$this->smarty->assign('SHOW_OUTPUT', false);

				$op = session_request_item("target_format", "HTML");
				$output_types = array (
							"HTML" => "",
							"PDF" => "",
							"CSV" => "",
							"XML" => "",
							"JSON" => "",
							"GRID" => ""
							);
				$output_types[$op] = "checked";
				$noutput_types = array();
				foreach ( $output_types as $val )
					$noutput_types[] = $val;
				$this->smarty->assign('OUTPUT_TYPES', $noutput_types );

				$op = session_request_item("target_style", "TABLE");
				$output_styles = array (
							"TABLE" => "",
							"FORM" => ""
							);
				$output_styles[$op] = "checked";
				$noutput_styles = array();
				foreach ( $output_styles as $val )
					$noutput_styles[] = $val;
				$this->smarty->assign('OUTPUT_STYLES', $noutput_styles );

				$attach = get_request_item("target_attachment", "1", $this->query->first_criteria_selection );
				if ( $attach )
					$attach = "checked";
				$this->smarty->assign("OUTPUT_ATTACH", $attach );

				$this->smarty->assign("OUTPUT_SHOWGRAPH", get_reportico_session_param("target_show_graph") ? "checked" : "" );
				$this->smarty->assign("OUTPUT_SHOWCRITERIA", get_reportico_session_param("target_show_criteria") ? "checked" : "" );
				$this->smarty->assign("OUTPUT_SHOWDETAIL", get_reportico_session_param("target_show_detail") ? "checked" : "" );
				$this->smarty->assign("OUTPUT_SHOWGROUPHEADERS", get_reportico_session_param("target_show_group_headers") ? "checked" : "" );
				$this->smarty->assign("OUTPUT_SHOWGROUPTRAILERS", get_reportico_session_param("target_show_group_trailers") ? "checked" : "" );
				$this->smarty->assign("OUTPUT_SHOWCOLHEADERS", get_reportico_session_param("target_show_column_headers") ? "checked" : "" );

				if ( ( $this->query->allow_debug && SW_ALLOW_DEBUG ) )
				{
					$this->smarty->assign("OUTPUT_SHOW_DEBUG", true );
					$debug_mode = get_request_item("debug_mode", "0", $this->query->first_criteria_selection );
					$this->smarty->assign("DEBUG_NONE", "" );
					$this->smarty->assign("DEBUG_LOW", "" );
					$this->smarty->assign("DEBUG_MEDIUM", "" );
					$this->smarty->assign("DEBUG_HIGH", "" );
					switch ( $debug_mode )
					{
						case 1:
							$this->smarty->assign("DEBUG_LOW", "selected" );
							break;
						case 2:
							$this->smarty->assign("DEBUG_MEDIUM", "selected" );
							break;
						case 3:
							$this->smarty->assign("DEBUG_HIGH", "selected" );
							break;
						default:
							$this->smarty->assign("DEBUG_NONE", "selected" );
					}
						
					if ( $debug_mode )
						$debug_mode = "checked";
					$this->smarty->assign("OUTPUT_DEBUG", $debug_mode );
				}

				$checked="";

				$this->smarty->assign("OUTPUT_SHOW_SHOWGRAPH", false );
				if ( count($this->query->graphs) > 0 )
				{
					$checked="";
					if (  $this->query->get_attribute("graphDisplay") )
						$checked="checked";
					if (  !get_request_item("target_show_graph") && !$this->query->first_criteria_selection )
						$checked="";

					$this->smarty->assign("OUTPUT_SHOW_SHOWGRAPH", true );
					$this->smarty->assign("OUTPUT_SHOWDET", $checked );
				}
				break;

			case "STATUS":

				$msg = "";
				if ( $this->query->status_message )
					$this->smarty->assign('STATUSMSG', $this->query->status_message );
				global $g_system_debug;
				if ( !$g_system_debug )
					$g_system_debug = array();
				foreach ( $g_system_debug as $val )
				{

					$msg .= "<hr>".$val["dbgarea"]." - ".$val["dbgstr"]."\n";
				}

				if ( $msg )
				{
					$msg = "<BR><B>".template_xlate("INFORMATION")."</B>".$msg;
				}

				$this->smarty->assign('STATUSMSG', $msg );
				break;

			case "ERROR":
				$msg = "";

				global $g_system_errors;
				$lastval = false;
				$duptypect = 0;
				if ( !$g_system_errors )
					$g_system_errors = array();
                $ct = 0;
				foreach ( $g_system_errors as $val )
				{

					if ( $val["errno"] == E_USER_ERROR ||  $val["errno"] == E_USER_WARNING || $val["errno"] == E_USER_NOTICE )
					{
                        if ( $ct++ > 0 )
						    $msg .= "<HR>";
  						if ( $val["errarea"] ) $msg .= $val["errarea"]." - ";
						if ( $val["errtype"] ) $msg .= $val["errtype"].": ";
						$msg .= $val["errstr"];
						
						$msg .= $val["errsource"];
						$msg .= "\n";
					}
					else
					{
						// Dont keep repeating Assignment errors
                        if ( $ct++ > 0 )
						    $msg .= "<HR>";

						if ( $val["errarea"] ) $msg .= $val["errarea"]." - ";
						if ( $val["errtype"] ) $msg .= $val["errtype"].": ";
                        if ( isset($val["errstr"]) && $val["errstr"] ) 
                            $msg .= "{$val["errfile"]} Line {$val["errline"]} - ";
						$msg .= $val["errstr"];
						$duptypect = 0;
					}
					$lastval = $val;
				}
				if ( $duptypect > 0 )
					$msg .= "<BR>$duptypect more errors like this<BR>";

				$debugmsg = "";
				if ( $this->query->status_message )
					$this->smarty->assign('STATUSMSG', $this->query->status_message );
				global $g_system_debug;
				if ( !$g_system_debug )
					$g_system_debug = array();
				foreach ( $g_system_debug as $val )
				{

					$debugmsg .= "<hr>".$val["dbgarea"]." - ".$val["dbgstr"]."\n";
				}

				if ( $debugmsg )
				{
					$debugmsg = "<BR><B>".template_xlate("INFORMATION")."</B>".$debugmsg;
				}


                if ( false && $msg && $this->query->reportico_ajax_called )
                {
                    header("HTTP/1.0 500 Not Found", true);
                    $response_array = array();
                    $response_array["errno"] = 100;
                    $response_array["errmsg"] = "<div class=\"swError\">$msg</div>$debugmsg";
                    echo json_encode($response_array);
                    die;
                }
                else
                {
                    if ( $msg )
					    $msg = "</B><div class=\"swError\">$msg</div>$debugmsg";
                }

				$this->smarty->assign('ERRORMSG', $msg );
				set_reportico_session_param('latestRequest',"");
				break;
		}
		return $text;
	}

	function post_draw_smarty()
	{
		$text = "";
		switch($this->panel_type)
		{
			case "LOGIN":
			case "LOGOUT":
			case "USERINFO":
			case "DESTINATION":
				break;

			case "BODY":
				break;

			case "CRITERIA":
				break;
				
			case "CRITERIA_FORM":
				break;

			case "CRITERIA_EXPAND":
				break;

			case "MENU":
				$this->smarty->assign('MENU_ITEMS', $this->query->menuitems);
				break;

			case "ADMIN":
				$this->smarty->assign('DOCDIR', find_best_location_in_include_path( "doc" ));
				$this->smarty->assign('PROJECT_ITEMS', $this->query->projectitems);
				break;

			case "MENUBUTTON":
				break;

			case "MENUITEM":
				break;

			case "PROJECTITEM":
				break;

			case "TOPMENU":
				break;

			case "MAIN":
				break;
		}
		return $text;
	}

}

/**
 * Class reportico_xml_reader
 *
 * responsible for loading, parsing and organising
 * Reportico report XML definition files
 */
class reportico_xml_reader
{
	var $query;
	var $parser;
	var $records;
	var $record;
	var $value;
	var $current_field = '';
	var $xmltag_type;
	var $ends_record;
	var $queries = array();
	var $data = array();
	var $element_stack = array();
	var $query_stack = array();
	var $column_stack = array();
	var $action_stack = array();
	var $datasource_stack = array();
	var $current_element;
	var $current_datasource;
	var $current_query;
	var $current_action;
	var $current_column;
	var $current_object;
	var $element_count = 0;
	var $level_ct = 0;
	var $oldid = "";
	var $id = "";
	var $last_element = "";
	var $in_column_section = false;
	var $current_criteria_name = false;
	var $show_level = false;
	var	$show_area = false;
	var	$search_tag = false;
	var	$search_response = false;
	var	$element_counts = array();
    var $wizard_linked_to = false;

  	function __construct (&$query, $filename, $xmlstring = false, $search_tag = false ) 
	{
    	$this->query =& $query;

    	$this->parser = xml_parser_create();
        $this->search_tag = $search_tag;
        $this->current_element =& $this->data; 
		$fontlist = array(".DEFAULT", "Vera", "Font 1", "Font 2", "Font 3", "Trebuchet", "Arial", "Times", "Verdana", "Courier", "Book", "Comic", "Script" );
    	$this->field_display = array (
					"Expression" => array ( "Title" => "EXPRESSION", "EditMode" => "SAFE", "DocId" => "expression" ),
					"Condition" => array ( "Title" => "CONDITION", "EditMode" => "SAFE", "DocId" => "condition" ),
					"GroupHeaderColumn" => array ( "Title" => "GROUPHEADERCOLUMN", "Type" => "QUERYCOLUMNS"),
					"GroupTrailerDisplayColumn" => array ( "Title" => "GROUPTRAILERDISPLAYCOLUMN", "Type" => "QUERYCOLUMNS"),
					"GroupTrailerValueColumn" => array ( "Title" => "GROUPTRAILERVALUECOLUMN", "Type" => "QUERYCOLUMNS"),
					"ColumnType" => array ( "Type" => "HIDE"),
					"ColumnLength" => array ( "Type" => "HIDE"),
					"ColumnName" => array ( "Type" => "HIDE"),
					"QueryName" => array ( "Type" => "HIDE"),
					"Name" => array ( "Title" => "NAME", "Type" => "TEXTFIELD", "DocId" => "name"),
					"QueryTableName" => array ( "Type" => "HIDE", "HelpPage" => "criteria"),
					"QueryColumnName" => array ( "Title" => "QUERYCOLUMNNAME", "HelpPage" => "criteria", "DocId" => "main_query_column"),
					"TableName" => array ( "Type" => "HIDE"),
					"TableSql" => array ( "Type" => "SHOW"),
					"WhereSql" => array ( "Type" => "HIDE"),
					"GroupSql" => array ( "Type" => "SHOW"),
					"RowSelection" => array ( "Type" => "SHOW"),
					"ReportTitle" => array ( "Title" => "REPORTTITLE", "DocId" => "report_title"),
					"LinkFrom" => array ( "Title" => "LINKFROM", "Type" => "CRITERIA"),
					"LinkTo" => array ( "Title" => "LINKTO", "Type" => "CRITERIA"),
					"LinkClause" => array ( "Title" => "LINKCLAUSE" ),
					"AssignName" => array ( "Title" => "ASSIGNNAME", "Type" => "QUERYCOLUMNS", "DocId" => "assign_to_existing_column"),
					"AssignNameNew" => array ( "Title" => "ASSIGNNAMENEW", "DocId" => "assign_to_new_column"),
					"AssignImageUrl" => array ( "Title" => "ASSIGNIMAGEURL", "DocId" => "image_url" ),
					"AssignHyperlinkLabel" => array ( "Title" => "ASSIGNHYPERLABEL", "DocId" => "hyperlink_label" ),
					"AssignHyperlinkUrl" => array ( "Title" => "ASSIGNHYPERURL", "DocId" => "hyperlink_url" ),

					"GroupHeaderStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour" ),
					"GroupHeaderStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour" ),
					"GroupHeaderStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style" ),
					"GroupHeaderStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness" ),
					"GroupHeaderStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour" ),
					"GroupHeaderStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"GroupHeaderStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"GroupHeaderStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name" ),
					"GroupHeaderStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size" ),
					"GroupHeaderStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style" ),
					"GroupHeaderStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width" ),
					"GroupHeaderStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height" ),
					"GroupHeaderStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page" ),
					"GroupHeaderStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image" ),

					"GroupTrailerStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour" ),
					"GroupTrailerStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour" ),
					"GroupTrailerStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style" ),
					"GroupTrailerStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness" ),
					"GroupTrailerStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour" ),
					"GroupTrailerStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"GroupTrailerStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding" ),
					"GroupTrailerStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name" ),
					"GroupTrailerStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size" ),
					"GroupTrailerStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style" ),
					"GroupTrailerStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width" ),
					"GroupTrailerStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height" ),
					"GroupTrailerStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page" ),
					"GroupTrailerStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image" ),

					"PageHeaderStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour" ),
					"PageHeaderStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour" ),
					"PageHeaderStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style" ),
					"PageHeaderStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness" ),
					"PageHeaderStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour" ),
					"PageHeaderStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"PageHeaderStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding" ),
					"PageHeaderStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name" ),
					"PageHeaderStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size" ),
					"PageHeaderStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style" ),
					"PageHeaderStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width" ),
					"PageHeaderStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "height" ),
					"PageHeaderStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page" ),
					"PageHeaderStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image" ),

					"PageFooterStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour" ),
					"PageFooterStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour" ),
					"PageFooterStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style" ),
					"PageFooterStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness" ),
					"PageFooterStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border_colour" ),
					"PageFooterStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"PageFooterStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding" ),
					"PageFooterStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name" ),
					"PageFooterStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size" ),
					"PageFooterStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style" ),
					"PageFooterStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE", "DocId" => "width" ),
					"PageFooterStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "height" ),
					"PageFooterStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true, "DocId" => "relative_to_current_position_or_page" ),
					"PageFooterBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE", "DocId" => "background_image" ),

					"DetailStyle" => array ( "Title" => "DETAILSTYLE" ),

                    "GroupHeaderCustom" => array ( "Title" => "GROUPHEADERCUSTOM", "Type" => "TEXTBOXNARROW", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "group_header_custom_text" ),
                    "GroupTrailerCustom" => array ( "Title" => "GROUPTRAILERCUSTOM", "Type" => "TEXTBOXNARROW", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "group_trailer_custom_text" ),

					"AssignStyleLocType" => array ( "Title" => "ASSIGNSTYLELOCTYPE", "Type" => "STYLELOCTYPES", "XlateOptions" => true, "DocId" => "apply_style_to"),
					"AssignStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "text_colour" ),
					"AssignStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "background_colour" ),
					"AssignStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true, "DocId" => "border_style" ),
					"AssignStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE", "DocId" => "border_thickness" ),
					"AssignStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR", "DocId" => "border colour" ),
					"AssignStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE", "DocId" => "margin" ),
					"AssignStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE", "DocId" => "padding" ),
					"AssignStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST", "DocId" => "font_name" ),
					"AssignStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE", "DocId" => "font_size", "DocId" => "font_size" ),
					"AssignStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true, "DocId" => "font_style", "DocId" => "font_style" ),
					"AssignStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE", "DocId" => "width" ),
					"AssignAggType" => array ( "Title" => "ASSIGNAGGTYPE", "Type" => "AGGREGATETYPES", "XlateOptions" => true, "DocId" => "aggregate_type"),
					"AssignAggCol" => array ( "Title" => "ASSIGNAGGCOL", "Type" => "QUERYCOLUMNS", "DocId" => "aggregate_column"),
					"AssignAggGroup" => array ( "Title" => "ASSIGNAGGGROUP", "Type" => "QUERYCOLUMNSOPTIONAL", "DocId" => "grouped_by"),
					"AssignGraphicBlobCol" => array ( "Title" => "ASSIGNGRAPHICBLOBCOL", "DocId" => "column_containing_graphic"),
					"AssignGraphicBlobTab" => array ( "Title" => "ASSIGNGRAPHICBLOBTAB", "DocId" => "table_containing_graphic"),
					"AssignGraphicBlobMatch" => array ( "Title" => "ASSIGNGRAPHICBLOBMATCH", "DocId" => "column_to_match_report_graphic"),
					"AssignGraphicWidth" => array ( "Title" => "ASSIGNGRAPHICWIDTH", "DocId" => "report_graphic_width"),
					"AssignGraphicReportCol" => array ( "Title" => "ASSIGNGRAPHICREPORTCOL", "Type" => "QUERYCOLUMNS", "DocId" => "graphic_report_column"),
					"LinkToElement" => array ( "Title" => "LINKTOREPORT", "Type" => "REPORTLIST"),
					"DrilldownReport" => array ( "Title" => "DRILLDOWNREPORT", "Type" => "REPORTLIST", "DocId" => "drilldown_report"),
					"DrilldownColumn" => array ( "Title" => "DRILLDOWNCOLUMN", "Type" => "QUERYCOLUMNSOPTIONAL", "DocId" => "drilldown_criteria"),
					"GroupName" => array ( "Title" => "GROUPNAME", "Type" => "GROUPCOLUMNS", "DocId" => "group_on_column"),
					"GraphColumn" => array ( "Title" => "GRAPHCOLUMN", "Type" => "QUERYGROUPS", "DocId" => "group_column" ),
					"GraphHeight" => array ( "Title" => "GRAPHHEIGHT", "DocId" => "graph_height" ),
					"GraphWidth" => array ( "Title" => "GRAPHWIDTH", "DocId" => "graph_width" ),
					"GraphColor" => array ( "Title" => "GRAPHCOLOR" ),
					"GraphWidthPDF" => array ( "Title" => "GRAPHWIDTHPDF", "DocId" => "graph_height_pdf" ),
					"GraphHeightPDF" => array ( "Title" => "GRAPHHEIGHTPDF", "DocId" => "graph_height_pdf" ),
					"XTitle" => array ( "Title" => "XTITLE", "DocId" => "x_axis_title" ),
					"YTitle" => array ( "Title" => "YTITLE", "DocId" => "y_axis_title" ),
					"GridPosition" => array ( "Title" => "GRIDPOSITION" ),
					"PlotStyle" => array ( "Title" => "PLOTSTYLE" ),
					"LineColor" => array ( "Title" => "LINECOLOR", "DocId" => "line_color" ),
					"DataType" => array ( "Title" => "DATATYPE", "Type" => "HIDE" ),
					"Legend" => array ( "Title" => "LEGEND", "DocId" => "legend" ),
					"FillColor" => array ( "Title" => "FILLCOLOR" ),
					"XGridColor" => array ( "Title" => "XGRIDCOLOR" ),
					"YGridColor" => array ( "Title" => "YGRIDCOLOR" ),
					"TitleFontSize" => array ( "Title" => "TITLEFONTSIZE" ),
					"XTickInterval" => array ( "Title" => "XTICKINTERVAL" ),
					"YTickInterval" => array ( "Title" => "YTICKINTERVAL" ),
					"XTickLabelInterval" => array ( "Title" => "XTICKLABELINTERVAL", "DocId" => "x_tick_label_interval" ),
					"YTickLabelInterval" => array ( "Title" => "YTICKLABELINTERVAL" ),
					"XTitleFontSize" => array ( "Title" => "XTITLEFONTSIZE" ),
					"YTitleFontSize" => array ( "Title" => "YTITLEFONTSIZE" ),
					"MarginColor" => array ( "Title" => "MARGINCOLOR", "DocId" => "margin_color" ),
					"MarginLeft" => array ( "Title" => "MARGINLEFT", "DocId" => "margin_left" ),
					"MarginRight" => array ( "Title" => "MARGINRIGHT", "DocId" => "margin_right" ),
					"MarginTop" => array ( "Title" => "MARGINTOP", "DocId" => "margin_top" ),
					"MarginBottom" => array ( "Title" => "MARGINBOTTOM", "DocId" => "margin_bottom" ),
					"TitleColor" => array ( "Title" => "TITLECOLOR" ),
					"XAxisColor" => array ( "Title" => "XAXISCOLOR" ),
					"YAxisColor" => array ( "Title" => "YAXISCOLOR" ),
					"XAxisFontColor" => array ( "Title" => "XAXISFONTCOLOR" ),
					"YAxisFontColor" => array ( "Title" => "YAXISFONTCOLOR" ),
					"XAxisFontSize" => array ( "Title" => "XAXISFONTSIZE" ),
					"YAxisFontSize" => array ( "Title" => "YAXISFONTSIZE" ),
					"XTitleColor" => array ( "Title" => "XTITLECOLOR" ),
					"YTitleColor" => array ( "Title" => "YTITLECOLOR" ),
					"PlotColumn" => array ( "Title" => "PLOTCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "column_to_plot", "DocId" => "column_to_plot"),
					"XLabelColumn" => array ( "Title" => "XLABELCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "column_for_x_labels"),
					//"YLabelColumn" => array ( "Title" => "YLABELCOLUMN", "Type" => "HIDE"),
					"ReturnColumn" => array ( "Title" => "RETURNCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "return_column"),
					"CriteriaDisplayGroup" => array ( "Title" => "CRITERIADISPLAYGROUP", "HelpPage" => "criteria", "DocId" => "display_group"),
					"CriteriaHidden" => array ( "Title" => "CRITERIAHIDDEN", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "yes", "no"), "HelpPage" => "criteria", "DocId" => "criteria_hidden" ),
					"CriteriaRequired" => array ( "Title" => "CRITERIAREQUIRED", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "yes", "no"), "HelpPage" => "criteria", "DocId" => "criteria_required" ),
					"CriteriaHelp" => array ( "Title" => "CRITERIAHELP", "Type" => "TEXTBOXSMALL", "DocId" => "criteria_help" ),
					"MatchColumn" => array ( "Title" => "MATCHCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "match_column"),
					"MatchColumn" => array ( "Title" => "MATCHCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "match_column"),
					"DisplayColumn" => array ( "Title" => "DISPLAYCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "display_column"),
					"OverviewColumn" => array ( "Title" => "OVERVIEWCOLUMN", "HelpPage" => "criteria", "Type" => "QUERYCOLUMNS", "DocId" => "overview_column"),
					"content_type" => array ( "Title" => "CONTENTTYPE", "Type" => "HIDE",  "XlateOptions" => true,
								"Values" => array("plain", "graphic")),
					"PreExecuteCode" => array ( 
						"Title" => "CUSTOMSOURCECODE",
						"Type" => "TEXTBOX", "DocId" => "custom_source_code",
				       		"EditMode" => "SAFE"),
					"ReportDescription" => array ( "Title" => "REPORTDESCRIPTION", "Type" => "TEXTBOX", "DocId" => "report_description" ),
					"SQLText" => array ( "Title" => "SQLTEXT", "Type" => "TEXTBOX", "EditMode" => "SAFE", "DocId" => "pre-sql_text_entry" ),
					"QuerySql" => array ( "Title" => "QUERYSQL", "Type" => "TEXTBOX", "DocSection" => "the_query_details_menu", "DocId" => "sql_query" ),
					"SQLRaw" => array ( "Title" => "SQLRAW", "Type" => "HIDE" ),
					"Password" => array ( "Type" => "PASSWORD" ),
					"PageSize" => array ( "Title" => "PAGESIZE", "Type" => "DROPDOWN",  "XlateOptions" => true,
									"Values" => array(".DEFAULT","B5", "A6", "A5", "A4", "A3", "A2", "A1", 
											"US-Letter","US-Legal","US-Ledger"), "DocId" => "page_size_pdf" ),
					"ReportTemplate" => array ( "Title" => "REPORTTEMPLATE", "Type" => "TEMPLATELIST"),
					"PageWidthHTML" => array ( "Title" => "PAGEWIDTHHTML" ),
					"PageOrientation" => array ( "Title" => "PAGEORIENTATION", "Type" => "DROPDOWN", "XlateOptions" => true,
									"Values" => array(".DEFAULT","Portrait", "Landscape"), "DocId" => "orientation_pdf" ),
					"TopMargin" => array ( "Title" => "TOPMARGIN" , "DocId" => "top_margin_pdf" ),
					"BottomMargin" => array ( "Title" => "BOTTOMMARGIN" , "DocId" => "bottom_margin_pdf" ),
					"RightMargin" => array ( "Title" => "RIGHTMARGIN" , "DocId" => "right_margin_pdf" ),
					"LeftMargin" => array ( "Title" => "LEFTMARGIN" , "DocId" => "left_margin_pdf" ),
					"pdfFont" => array ( "Title" => "PDFFONT",  "Type" => "FONTLIST", "DocId" => "font_pdf" ),
					"OrderNumber" => array ( "Title" => "ORDERNUMBER", "DocId" => "order_number" ),
					"ReportJustify" => array ( "Type" => "HIDE" ),
					"BeforeGroupHeader" => array ( "Title" => "BEFOREGROUPHEADER", "Type" => "DROPDOWN", "XlateOptions" => true,
												"Values" => array("blankline", "solidline", "newpage"), "DocId" => "before_group_header" ),
					"AfterGroupHeader" => array ( "Title" => "AFTERGROUPHEADER", "Type" => "DROPDOWN", "XlateOptions" => true,
												"Values" => array("blankline", "solidline", "newpage"), "DocId" => "after_group_header" ),
					"BeforeGroupTrailer" => array ( "Title" => "BEFOREGROUPTRAILER", "Type" => "DROPDOWN", "XlateOptions" => true,
												"Values" => array("blankline", "solidline", "newpage"), "DocId" => "before_group_trailer" ),
					"AfterGroupTrailer" => array ( "Title" => "AFTERGROUPTRAILER", "Type" => "DROPDOWN", "XlateOptions" => true,
												"Values" => array("blankline", "solidline", "newpage"), "DocId" => "after_group_trailer" ),
					//"bodyDisplay" => array ( "Title" => "BODYDISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
												//"Values" => array("hide", "show") ),
					//"graphDisplay" => array ( "Title" => "GRAPHDISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
												//"Values" => array("hide", "show") ),
					"gridDisplay" => array ( "Title" => "GRIDDISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "hide", "show"), "DocId" => "display_grid" ),
					"gridSortable" => array ( "Title" => "GRIDSORTABLE", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "yes", "no"), "DocId" => "sortable_grid" ),
					"gridSearchable" => array ( "Title" => "GRIDSEARCHABLE", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "yes", "no"), "DocId" => "searchable_grid_" ),
					"gridPageable" => array ( "Title" => "GRIDPAGEABLE", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array(".DEFAULT", "yes", "no"), "DocId" => "display_grid" ),
					"gridPageSize" => array ( "Title" => "GRIDPAGESIZE", "XlateOptions" => true, "DocId" => "grid_page_size" ),
					"formBetweenRows" => array ( "Title" => "FORMBETWEENROWS", "Type" => "DROPDOWN", "XlateOptions" => true,
									"Values" => array("blankline", "solidline", "newpage"), "DocId" => "form_style_row_separator" ),
					"GroupHeaderColumn" => array ( "Title" => "GROUPHEADERCOLUMN", "Type" => "QUERYCOLUMNS", "DocId" => "group_header_column" ),
					"GroupTrailerDisplayColumn" => array ( "Title" => "GROUPTRAILERDISPLAYCOLUMN", "Type" => "QUERYCOLUMNS",  "DocId" => "group_trailer_display_column" ),
					"GroupTrailerValueColumn" => array ( "Title" => "GROUPTRAILERVALUECOLUMN", "Type" => "QUERYCOLUMNS",  "DocId" => "group_trailer_value_column" ),
					"LineNumber" => array ( "Title" => "LINENUMBER", "DocId" => "line_number" ),
					"HeaderText" => array ( "Title" => "HEADERTEXT", "Type" => "TEXTBOXSMALL", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "header_text" ),
					"FooterText" => array ( "Title" => "FOOTERTEXT", "Type" => "TEXTBOXSMALL", "WizardLink" => true, "HasChangeComparator" => true, "DocId" => "footer_text" ),
					"ShowInPDF" => array ( "Title" => "SHOWINPDF", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array("yes", "no"), "DocId" => "show_in_pdf" ),
					"ShowInHTML" => array ( "Title" => "SHOWINHTML", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array("yes", "no"), "DocId" => "show_in_html" ),
					"ColumnStartPDF" => array ( "Title" => "COLUMNSTARTPDF", "DocId" => "column_start_pdf"),
					"ColumnWidthPDF" => array ( "Title" => "COLUMNWIDTHPDF", "DocId" => "column_width_pdf"),
					"ColumnWidthHTML" => array ( "Title" => "COLUMNWIDTHHTML", "DocId" => "column_width_html"),
					"column_title" => array ( "Title" => "COLUMN_TITLE" ),
					"tooltip" => array ( "Type" => "HIDE", "Title" => "TOOLTIP" ),
					"group_header_label" => array ( "Title" => "GROUP_HEADER_LABEL" ),
					"group_trailer_label" => array ( "Title" => "GROUP_TRAILER_LABEL" ),
					"group_header_label_xpos" => array ( "Title" => "GROUP_HEADER_LABEL_XPOS", "DocId" => "group_header_label_start_pdf" ),
					"group_header_data_xpos" => array ( "Title" => "GROUP_HEADER_DATA_XPOS", "DocId" => "group_header_value_start_pdf" ),
					"ReportJustify" => array ( "Type" => "HIDE" ),
					"pdfFontSize" => array ( "Title" => "PDFFONTSIZE", "DocId" => "font_size_pdf" ),
					"GridPosition" => array ( "Title" => "GRIDPOSITION", "Type" => "DROPDOWN", 
												"Values" => array(".DEFAULT", "back", "front") ),
					"XGridDisplay" => array ( "Title" => "XGRIDDISPLAY", "Type" => "DROPDOWN", 
												"Values" => array(".DEFAULT", "none", "major", "all"), "DocId" => "x-grid_style" ),
					"YGridDisplay" => array ( "Title" => "YGRIDDISPLAY", "Type" => "DROPDOWN", 
												"Values" => array(".DEFAULT", "none", "major", "all"), "DocId" => "y-grid_style" ),
					"PlotType" => array ( "Title" => "PLOTSTYLE", "Type" => "DROPDOWN", 
												"Values" => array("BAR", "STACKEDBAR", "OVERLAYBAR", "LINE", "AREACHART", "SCATTER", "PIE", "PIE3D"), "DocId" => "plot_style" ),
					"Title" => array ( "Title" => "TITLE", "DocId" => "title" ), 
					"CriteriaDefaults" => array ( "Title" => "CRITERIADEFAULTS", "HelpPage" => "criteria", "DocId" => "defaults" ),
					"CriteriaList" => array ( "Title" => "CRITERIALIST", "HelpPage" => "criteria", "DocId" => "list_values" ),
					"CriteriaType" => array ( "Title" => "CRITERIATYPE", "HelpPage" => "criteria", "Type" => "DROPDOWN", 
					    "Values" => array("TEXTFIELD", "LOOKUP", "DATE", "DATERANGE", "LIST", "SQLCOMMAND" ), "XlateOptions" => true, "DocId" => "criteria_type" ),
					"Use" => array ( "Title" => "USE", "HelpPage" => "criteria", "Type" => "DROPDOWN", 
                                        "Values" => array("DATA-FILTER","SHOW/HIDE", "SHOW/HIDE-and-GROUPBY") ),
					"LinkToReport" => array ( "Type" => "TEXTFIELDREADONLY", "Title" => "LINKTOREPORT" ),
					"LinkToReportItem" => array ( "Type" => "TEXTFIELDREADONLY", "Title" => "LINKTOREPORTITEM" ),
					"CriteriaDisplay" => array ( "Title" => "CRITERIADISPLAY", "Type" => "DROPDOWN", "HelpPage" => "criteria", "XlateOptions" => true, 
												"Values" => array("NOINPUT", "TEXTFIELD", "DROPDOWN", "MULTI", "SELECT2SINGLE", "SELECT2MULTIPLE", "CHECKBOX", "RADIO", ), "DocId" => "criteria_display" ),
					"ExpandDisplay" => array ( "Title" => "EXPANDDISPLAY", "Type" => "DROPDOWN", "HelpPage" => "criteria", "XlateOptions" => true, 
												"Values" => array("NOINPUT", "TEXTFIELD", "DROPDOWN", "MULTI", "SELECT2SINGLE", "SELECT2MULTIPLE", "CHECKBOX", "RADIO", ), "DocId" => "expand_display" ),
					"DatabaseType" => array ( "Title" => "DATABASETYPE", "Type" => "DROPDOWN", 
												"Values" => array("informix", "mysql", "sqlite-2", "sqlite-3", "none" ) ),
					"justify" => array ( "Title" => "JUSTIFY", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array("left", "center", "right"), "DocId" => "justification" ),
					"column_display" => array ( "Title" => "COLUMN_DISPLAY", "Type" => "DROPDOWN",  "XlateOptions" => true,
												"Values" => array("show", "hide"), "DocId" => "show_or_hide" ),
					"TitleFont" => array ( "Title" => "TITLEFONT", "Type" => "DROPDOWN", 
								"Values" => $fontlist ),
					"TitleFontStyle" => array ( "Title" => "TITLEFONTSTYLE", "Type" => "DROPDOWN", 
								"Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic") ),
					"XTitleFont" => array ( "Title" => "XTITLEFONT", "Type" => "DROPDOWN", 
								"Values" => $fontlist ),
					"YTitleFont" => array ( "Title" => "YTITLEFONT", "Type" => "DROPDOWN", 
								"Values" => $fontlist ),
					"XAxisFont" => array ( "Title" => "XAXISFONT", "Type" => "DROPDOWN", 
								"Values" => $fontlist ),
					"YAxisFont" => array ( "Title" => "YAXISFONT", "Type" => "DROPDOWN", 
								"Values" => $fontlist ),
					"XAxisFontStyle" => array ( "Title" => "XAXISFONTSTYLE", "Type" => "DROPDOWN", 
								"Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic") ),
					"YAxisFontStyle" => array ( "Title" => "YAXISFONTSTYLE", "Type" => "DROPDOWN", 
								"Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic") ),
					"XTitleFontStyle" => array ( "Title" => "XTITLEFONTSTYLE", "Type" => "DROPDOWN", 
								"Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic") ),
					"YTitleFontStyle" => array ( "Title" => "YTITLEFONTSTYLE", "Type" => "DROPDOWN", 
								"Values" => array(".DEFAULT", "Normal", "Bold", "Italic", "Bold+Italic") )
					);
    
        if ( $this->query )
        {
            $this->query->apply_plugins("design-options", array("query" => &$this->query, "field_display" => &$this->field_display));
        }

        // If using pchart engine, then certain graph options are not available
        // and should therefore be hidden from design pane
		//$this->field_display["LineColor"]["Type"] = "HIDE";

        if ( !defined("SW_GRAPH_ENGINE") || SW_GRAPH_ENGINE == "PCHART" )
        {
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
    	xml_set_element_handler($this->parser, 'start_element', 'end_element');
    	xml_set_character_data_handler($this->parser, 'cdata');
    	xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING, false);

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
		if ( $xmlstring )
    		$x =& $xmlstring;
		else
		{
			if ( $filename )
			{
                global $g_project;
                if ( $this->query )
                {
                    $readfile = $this->query->projects_folder."/$g_project/".$filename;
                    $adminfile = $this->query->admin_projects_folder."/admin/".$filename;
                }
                else
                {
                    $readfile = $filename;
                    $adminfile = false;
                }

                if ( $query )
                if ( $query->login_type != "ADMIN" && $query->login_type != "DESIGN" )
                {
                    if ( $g_project == "admin" )
                        $readfile = false;
                    $adminfile = false;
                }

                if ( $readfile && !is_file($readfile) )
                {
                    find_file_to_include($readfile, $readfile);
                }

                if ( $readfile && is_file ( $readfile )  )
                {
                    $readfile = $readfile;
                }
				else 
				{
					if ( !is_file($adminfile) )
					{
						find_file_to_include($adminfile, $readfile);
						if ( is_file ( $readfile )  )
							$readfile = $readfile;
					}
					else
							$readfile = $adminfile;
				}
				if ( $readfile )
				{
					$x = join("", file($readfile));
				}
				else
					trigger_error ( "Report Definition File  ". $this->query->reports_path."/".$filename." Not Found", E_USER_ERROR );
			}
		}

		if ( $x )
		{
			xml_parse($this->parser, $x);
			xml_parser_free($this->parser);
		}

		//var_dump($this->data);
  	}

	function start_element ($p, $element, &$attributes) 
	{
		//$element = strtolower($element);

		$this->gotdata = false;
		$this->value = "";

		if ( !array_key_exists($element, $this->xmltag_type ) )
			$tp = 1;
		else
			$tp = $this->xmltag_type[$element];


		switch ( $tp )
		{
			case 1:
				$this->current_element[$element] = "";
				break;

			case 2:
				$ar = array();
				$this->current_element[] =& $ar;
				$this->element_count = array_push($this->element_stack, 
					count($this->current_element)-1);
				$this->current_element =& $ar; 
				break;

			case 3:
				$ar = array();
				$this->current_element[$element] =& $ar;
				$this->element_count = array_push($this->element_stack, 
					$element);
				$this->current_element =& $ar; 
				break;

		}

  	}

	function end_element ($p, $element) 
  	{
    		//$element = strtolower($element);
		if ( !array_key_exists($element, $this->xmltag_type ) )
			$tp = 1;
		else
			$tp = $this->xmltag_type[$element];


		if ( $tp == 1 )
		{
			$this->current_element[$element] = $this->value;

			if ( $element == $this->search_tag )
			{
				$this->search_response = $this->value;
			}
		}
		else
		{
			array_pop($this->element_stack);	
			$this->element_count--;

			$ct = 0;
			$this->current_element =& $this->data;
			foreach ( $this->element_stack as $v )
			{
				$this->current_element =& $this->current_element[$v];
			}
		}


 	}

	function cdata ($p, $text) 
  	{
     		$this->value .= $text;
		$this->gotdata = true;
  	}

	function & get_array_element ( &$in_arr, $element ) 
	{
		$retval = false;
		if ( array_key_exists($element, $in_arr ) )
		{
			return $in_arr[$element];
		}
		else
			return $retval;
	}

	function countArrayElements (&$ar) 
	{
			$ct = 0;
			foreach($ar as $k => $el)
			{
					if ( is_array($el) )
					{
						$ct = $ct + $this->countArrayElements($el);
					}
					else
					{
						$ct++;
					}
			}
			return $ct;
	}

	function &analyse_form_item ($tag) 
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
			while ( $ptr < $len )
			{
				$bit = substr($tag, $ptr, 4 );
				//echo $bit."/";
				$ptr += 4;

				if ( is_numeric($bit) && (int)$bit  == 0 )
					$bit = "ZERO";

				switch ( $bit )
				{
					case "main":
						break;

					case "outp":
						$item =& $this->query;
						$qr =& $this->query;
						break;

					case "quer":
						$item =& $this->query;
						$qr =& $this->query;
						break;

					case "data":
						$item =& $this->query->ds;
						$action = $bit;
						break;

					case "qury":
						if ( !$cr )
							$qr =& $this->query;
						else
							$qr =& $cr->lookup_query;
						$item =& $qr;
						break;

					case "conn":
						$item =& $this->query->datasource;
						$action = $bit;
						break;

					case "crit":
						$item =& $this->query;
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
						if ( is_numeric($bit) )
						{
							$bit = (int)$bit;
							if ( $last == "crit" )
							{
								$ct = 0;
								foreach ( $qr->lookup_queries as $k => $v )
								{
									if ( $ct == $bit ) 
									{
										$cr =& $qr->lookup_queries[$k];
										break;
									}
									$ct++;
								}
								$item =& $cr;
							}
							if ( $last == "grph" )
							{
								$ct = 0;
								foreach ( $qr->graphs as $k => $v )
								{
									if ( $ct == $bit ) 
									{
										$grph =& $qr->graphs[$k];
										break;
									}
									$ct++;
								}
								$item =& $cl;
							}
							if ( $last == "qcol" )
							{
								$ct = 0;
								foreach ( $qr->columns as $k => $v )
								{
									if ( $ct == $bit ) 
									{
										$cl =& $qr->columns[$k];
										$cn = $cl->query_name;
										//$cn = $k;
										break;
									}
									$ct++;
								}
								$item =& $cl;
							}
							
							if ( $last == "grps" )
							{
								$ct = 0;
								foreach ( $qr->groups as $k => $v )
								{
									if ( $ct == $bit ) 
									{
										$gr =& $qr->groups[$k];
										$grn = $v->group_name;
										$grno = $k;
										break;
									}
									$ct++;
								}
								$item =& $gr;
							}
							if ( $last == "clnk" )
								$item =& $qr->criteria_links[$bit];
							if ( $last == "ords" )
							{
								//$item =& $qr->order_set["itemno"][$bit];
							}
							if ( $last == "dord" )
								$item =& $qr->display_order_set["itemno"][$bit];
							if ( $last == "assg" )
								$item =& $qr->assignment[$bit];
							if ( $last == "pgft" )
								$item =& $qr->page_footers[$bit];
							if ( $last == "plot" )
							{
								//echo $qr->graphs;
								//var_dump ($qr->graphs);
								//echo $qr->graphs->plot;
								//$item =& $qr->graphs->plot[$bit];
							}
							if ( $last == "grph" )
								$item =& $qr->graphs[$bit];
							if ( $last == "pghd" )
								$item =& $qr->page_headers[$bit];
							if ( $last == "ghdr" )
								$item =& $gr->headers[$bit];
							if ( $last == "gtrl" )
								$item =& $gr->trailers[$bit];
							$nm = (int)$bit;
						}
				}

				$last = $bit;
			}

			$anal["graph"] =& $grph;
			$anal["quer"] =& $qr;
			$anal["crit"] =& $cr;
			$anal["column"] =& $cl;
			$anal["colname"] =& $cn;
			$anal["item"] =& $item;
			$anal["action"] =& $action;
			$anal["group"] =& $gr;
			$anal["groupname"] =& $grn;
			$anal["groupno"] =& $grno;
			$anal["number"] =$nm;
			$anal["array"] =& $actar;
			return $anal;
			
	}

	function link_in_report_fields ($link_or_import, $match, $action) 
	{
			$ret = false;

            // Fetch user select report to link to 
			$match_key = "/^reportlink_(".$match.")/";
			$updates = array();
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					$updates[$matches[1]] = stripslashes($v);
                    $this->query->reportlink_report = $v;
				}
			}

            // Fetch user select report item ( criteria, assignment etc )  to link to 
			$match_key = "/^reportlinkitem_(".$match.")/";
			$updates = array();
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					$updates[$matches[1]] = stripslashes($v);
                    if ( strval($v) == "0" ) 
                        $v = "_ZERO";
                    $this->query->reportlink_report_item = $v;
				}
			}

            // Fetch user selection of whether to import or link
			$match_key = "/^linkorimport_(".$match.")/";
			$updates = array();

			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					$updates[$matches[1]] = stripslashes($v);
                    $this->query->reportlink_or_import = $v;
                    break;
				}
			}
            if ( $action != "REPORTLINKITEM" && $action != "REPORTIMPORTITEM" ) 
                return;

            if ( !$this->query->reportlink_report_item )
                return;

            if ( $this->query->reportlink_report_item == "_ZERO" )
                $this->query->reportlink_report_item = 0;

			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "form":
					// Delete not applicable to "Format" option
					break;

				case "assg":
                    $q =  load_existing_report ( $this->query->reportlink_report, $this->query->projects_folder );
					foreach ( $q->assignment as $k => $v )
					{
                        if ( strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k )
                        {
						    $found = false;
						    foreach ( $this->query->columns as $querycol )
							    if (  $querycol->query_name == $v->query_name )
								    $found = true;

						    if ( !$found ) 
							    $this->query->create_query_column( $v->query_name, "", "", "", "",
										'####.###', false);
                            $this->query->assignment[] = $v;
                        }
					}
					break;

				case "pghd":
                    $q =  load_existing_report ( $this->query->reportlink_report, $this->query->projects_folder );
					foreach ( $q->page_headers as $k => $v )
					{
                        if ( strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k )
                            $this->query->page_headers[] = $v;
					}
					break;

				case "grph":
					$qr =& $anal["quer"];
					$qr->create_graph();
					break;

				case "plot":
					$qr =& $anal["graph"];
					$qr->create_plot("");
					break;

				case "pgft":
                    $q =  load_existing_report ( $this->query->reportlink_report, $this->query->projects_folder );
					foreach ( $q->page_footers as $k => $v )
					{
                        if ( strval($this->query->reportlink_report_item) == "ALLITEMS" || $this->query->reportlink_report_item == $k )
                            $this->query->page_footers[] = $v;
					}
					break;

				case "clnk":
					$cr =& $anal["crit"];
					$this->query->set_criteria_link ( 
						$cr->query_name, $cr->query_name, 
								template_xlate("ENTERCLAUSE") );
					break;

				case "pgft":
					array_splice($this->query->page_footers, $anal["number"], 1);
					break;

				case "grps":
					$qr =& $anal["quer"];
					$ak = array_keys( $this->query->columns);
					$qr->create_group ( $this->query->columns[0]->query_name );
					break;

				case "psql":
					$qr =& $anal["quer"];
					$qr->add_pre_sql ( "-- ". template_xlate("ENTERSQL") );
					break;

				case "crit":
                    $q =  load_existing_report ( $this->query->reportlink_report, $this->query->projects_folder );
					foreach ( $q->lookup_queries as $k => $v )
					{
                        if ( $this->query->reportlink_report_item == "ALLITEMS" ||
                                $this->query->reportlink_report_item == $v->query_name )
                        {
					        $qu = new reportico();

					        $this->query->set_criteria_lookup ( $v->query_name, $qu, "", "" );
                            $lastitem = count($this->query->lookup_queries) - 1;
                            if ( $this->query->reportlink_or_import == "import" )
                            {
                                $this->query->lookup_queries[$v->query_name] = $q->lookup_queries[$k];
                            }
                            else
                            {
                                $this->query->lookup_queries[$v->query_name]->link_to_report = $this->query->reportlink_report;
                                $this->query->lookup_queries[$v->query_name]->link_to_report_item = $v->query_name ;
                            }
                        }
                        
					}
					break;

				case "qcol":
					$qr =& $anal["quer"];
					$qr->create_criteria_column ( "NewColumn", "", "",
			   						"char", 0, "###", false	);
					break;

				case "ghdr":
					$updateitem =& $anal["item"];
					$gn = $anal["groupname"];
					if ( reset ( $this->query->columns ) )
					{
						$cn = current( $this->query->columns );
						$this->query->create_group_header( $gn, $cn->query_name, "" );
					}
					break;

				case "gtrl":
					$updateitem =& $anal["item"];
					$gn = $anal["groupname"];
					if ( reset ( $this->query->columns ) )
					{
						$cn = current( $this->query->columns );
						$this->query->create_group_trailer
								( $gn, $cn->query_name, $cn->query_name );
					}
					break;


				case "conn":
					// Delete not applicable to Connection action
					break;
			}

			return $ret;
	}

	function add_maintain_fields ($match) 
	{
			$ret = false;

			$match_key = "/^set_".$match."_(.*)/";
			$updates = array();
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					$updates[$matches[1]] = stripslashes($v);
				}
			}
			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "form":
					// Delete not applicable to "Format" option
					break;

				case "assg":
					$qr =& $anal["quer"];
					$qr->add_assignment ( "Column", "Expression", "" );
					break;

				case "pghd":
					$qr =& $anal["quer"];
					$qr->create_page_header("Name", 1, "Header Text" );
					break;

				case "grph":
					$qr =& $anal["quer"];
					$qr->create_graph();
					break;

				case "plot":
					$qr =& $anal["graph"];
					$qr->create_plot("");
					break;

				case "pgft":
					$qr =& $anal["quer"];
					$qr->create_page_footer("Name", 1, "Header Text" );
					break;

				case "clnk":
					$cr =& $anal["crit"];
					$this->query->set_criteria_link ( 
						$cr->query_name, $cr->query_name, 
								template_xlate("ENTERCLAUSE") );
					break;

				case "pgft":
					array_splice($this->query->page_footers, $anal["number"], 1);
					break;

				case "grps":
					$qr =& $anal["quer"];
					$ak = array_keys( $this->query->columns);
					$qr->create_group ( $this->query->columns[0]->query_name );
					break;

				case "psql":
					$qr =& $anal["quer"];
					$qr->add_pre_sql ( "-- ". template_xlate("ENTERSQL") );
					break;

				case "crit":
					$qr =& $anal["quer"];
					$qu = new reportico();
					$qr->set_criteria_lookup(
								"CriteriaName", $qu, "", "" );
					break;

				case "qcol":
					$qr =& $anal["quer"];
					$qr->create_criteria_column ( "NewColumn", "", "",
			   						"char", 0, "###", false	);
					break;

				case "ghdr":
					$updateitem =& $anal["item"];
					$gn = $anal["groupname"];
					if ( reset ( $this->query->columns ) )
					{
						$cn = current( $this->query->columns );
						$this->query->create_group_header( $gn, $cn->query_name );
					}
					break;

				case "gtrl":
					$updateitem =& $anal["item"];
					$gn = $anal["groupname"];
					if ( reset ( $this->query->columns ) )
					{
						$cn = current( $this->query->columns );
						$this->query->create_group_trailer
								( $gn, $cn->query_name, $cn->query_name );
					}
					break;


				case "conn":
					// Delete not applicable to Connection action
					break;
			}

			return $ret;
	}

	function moveup_maintain_fields ($match) 
	{
			$ret = false;
			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "form":
					// Delete not applicable to "Format" option
					break;

				case "assg":
					$updateitem =& $anal["item"];
					$qr =& $anal["quer"];
					$cut = array_splice($qr->assignment, $anal["number"], 1);
					array_splice($qr->assignment, $anal["number"] - 1, 0, $cut);
					break;

				case "dord":
					$updateitem =& $anal["item"];
					$cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
					$anal["quer"]->set_column_order ( $cl, $anal["number"], true );
					break;

				case "pgft":
					array_splice($this->query->page_footers, $anal["number"], 1);
					break;

				case "grps":
					$cut = array_splice($this->query->groups, $anal["number"], 1);
					array_splice($this->query->groups, $anal["number"] - 1, 0, $cut);
					break;

				case "clnk":
					$qr =& $anal["crit"]->lookup_query;
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
					$anal["quer"]->remove_column ( $anal["colname"] );
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
					$qr =& $anal["quer"];
					array_splice($qr->order_set, $anal["number"], 1);
					break;

				case "grph":
					array_splice($this->query->graphs, $anal["number"], 1);
					break;

				case "plot":
					array_splice($anal["graph"]->plot, $anal["number"], 1);
					break;

				case "pghd":
					array_splice($this->query->page_headers, $anal["number"], 1);
					break;

				case "conn":
					// Delete not applicable to Connection action
					break;
			}

			return $ret;
	}

	function movedown_maintain_fields ($match) 
	{
			$ret = false;
			$match_key = "/^set_".$match."_(.*)/";

			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "form":
					// Delete not applicable to "Format" option
					break;

				case "assg":
					$updateitem =& $anal["item"];
					$qr =& $anal["quer"];
					$cut = array_splice($qr->assignment, $anal["number"], 1);
					array_splice($qr->assignment, $anal["number"] + 1, 0, $cut);
					break;

				case "dord":
					$updateitem =& $anal["item"];
					$cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
					$anal["quer"]->set_column_order ( $cl, $anal["number"] + 2, false );
					break;

				case "pgft":
					break;

				case "grps":
					$cut = array_splice($this->query->groups, $anal["number"], 1);
					array_splice($this->query->groups, $anal["number"] + 1, 0, $cut);
					break;

				case "clnk":
					$qr =& $anal["crit"]->lookup_query;
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
					$anal["quer"]->remove_column ( $anal["colname"] );
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
					$qr =& $anal["quer"];
					array_splice($qr->order_set, $anal["number"], 1);
					break;

				case "grph":
					array_splice($this->query->graphs, $anal["number"], 1);
					break;

				case "plot":
					array_splice($anal["graph"]->plot, $anal["number"], 1);
					break;

				case "pghd":
					array_splice($this->query->page_headers, $anal["number"], 1);
					break;

				case "conn":
					// Delete not applicable to Connection action
					break;
			}

			return $ret;
	}

	function delete_maintain_fields ($match) 
	{
			$ret = false;
			$match_key = "/^set_".$match."_(.*)/";
			$updates = array();
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					$updates[$matches[1]] = stripslashes($v);
				}
			}

			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "form":
					// Delete not applicable to "Format" option
					break;

				case "assg":
					$updateitem =& $anal["item"];
					$qr =& $anal["quer"];
					array_splice($qr->assignment, $anal["number"], 1);
					break;

				case "pgft":
					array_splice($this->query->page_footers, $anal["number"], 1);
					break;

				case "grps":
					array_splice($this->query->groups, $anal["number"], 1);
					break;

				case "clnk":
					$qr =& $anal["crit"]->lookup_query;
					array_splice($qr->criteria_links, $anal["number"], 1);
					break;

				case "psql":
					array_splice($this->query->pre_sql, $anal["number"], 1);
					break;

				case "crit":
					array_splice($this->query->lookup_queries, $anal["number"], 1);
					break;

				case "qcol":
					$anal["quer"]->remove_column ( $anal["colname"] );
					break;

				case "ghdr":
					$anal["quer"]->delete_group_header_by_number 
							( $anal["groupname"], $anal["number"] );
					break;

				case "gtrl":
					$updateitem =& $anal["item"];
					$anal["quer"]->delete_group_trailer_by_number 
							( $anal["groupname"], $anal["number"] );
					break;

				case "ords":
					$qr =& $anal["quer"];
					array_splice($qr->order_set, $anal["number"], 1);
					break;

				case "grph":
					array_splice($this->query->graphs, $anal["number"], 1);
					break;

				case "plot":
					array_splice($anal["graph"]->plot, $anal["number"], 1);
					break;

				case "pghd":
					array_splice($this->query->page_headers, $anal["number"], 1);
					break;

				case "conn":
					// Delete not applicable to Connection action
					break;
			}

			return $ret;
	}

	function change_array_keyname(&$in_array, $in_number, $in_key)
	{
		$nm = 0;
		foreach ( $in_array as $k => $v )
		{
			if ( $nm == $in_number )
			{
				$in_array[$in_key] = $v;
				array_splice($in_array, $nm, 1, $el );
				$in_array[$in_key] = $v;
				break;
			}
			$nm++;
		}
	}

	function validate_maintain_fields (&$updates) 
	{
        $invalid = false;
        $current_key = false;
        $current_value = false;
        foreach ( $updates as $k => $v )
        {
            $current_key = $k;
            $current_value = $v;
            if ( isset ( $this->field_display[$k] ) && isset ( $this->field_display[$k]["Validate"] ) )
            {
                if ( !$v ) continue;

                // Must be a number
                if ( $this->field_display[$k]["Validate"] == "NUMBER" )
                {
                    if ( !preg_match ( "/^[0-9]+$/", $v ) )
                    {
                       $invalid = true;
                       break;
                    }
                }

                // HTMLCOLOR must be in the form #hhhhhh or hhhh
                if ( $this->field_display[$k]["Validate"] == "HTMLCOLOR" )
                {
                    if ( preg_match ( "/^[0-9a-fA-F]{6}$/", $v ) )
                        $updates[$k] = "#".$v;
                    else if ( !preg_match ( "/^#[0-9a-fA-F]{6}$/", $v ) )
                    {
                       $invalid = true;
                       break;
                    }
    
                }

                // CSS1SIZE must be in the form xpx or x%
                if ( $this->field_display[$k]["Validate"] == "CSSFONTSIZE" )
                {
                    if ( preg_match ( "/^[0-9]+$/", $v ) )
                        $updates[$k] = $v."pt";
                    else if ( !preg_match ( "/^[0-9]+pt$/", $v ) )
                    {
                       $invalid = true;
                       break;
                    }
                }

                // CSS1SIZE must be in the form xpx or x%
                if ( $this->field_display[$k]["Validate"] == "CSS1SIZE" )
                {
                    if ( !preg_match ( "/^[0-9]+px$/", $v ) &&
                         !preg_match ( "/^[0-9]+cm$/", $v ) &&
                         !preg_match ( "/^[0-9]+mm$/", $v ) &&
                         !preg_match ( "/^[0-9]+em$/", $v ) &&
                         !preg_match ( "/^[0-9]+%$/", $v ) &&
                         !preg_match ( "/^[0-9]+$/", $v ) )
                    {
                       $invalid = true;
                       break;
                    }
                }

                // CSS4SIZE must be in the form of 4 items of xpx or x%
                if ( $this->field_display[$k]["Validate"] == "CSS4SIZE" )
                {
                    $arr = explode(" " ,$v);
                    if ( count($arr) < 1 && count($arr) > 4 )
                    {
                        $invalid = true;
                        break;
                    }
                    else
                    {
                            foreach ( $arr as $k1 => $v1 )
                            {
                                if ( !preg_match ( "/^[0-9]+px$/", $v1 ) &&
                                    !preg_match ( "/^[0-9]+cm$/", $v1 ) &&
                                    !preg_match ( "/^[0-9]+mm$/", $v1 ) &&
                                    !preg_match ( "/^[0-9]+em$/", $v1 ) &&
                                    !preg_match ( "/^[0-9]+%$/", $v1 ) &&
                                    !preg_match ( "/^[0-9]+$/", $v1 ) )
                                {
                                    $invalid = true;
                                    break;
                                }
                            }
                            if ( $invalid ) break;
                    }
                }
            }
        }

        if ( $invalid )
        {
            $updates[$k] = false;
            trigger_error ( template_xlate("INVALIDENTRY")."'".$current_value."' ". template_xlate("FORFIELD")." ". template_xlate( $this->field_display[$current_key]["Title"])." - ".template_xlate( $this->field_display[$current_key]["Validate"] ), E_USER_NOTICE);

        }
    }

	function update_maintain_fields ($match) 
	{
			$ret = false;
			$match_key = "/^set_".$match."_(.*)/";
			$updates = array();
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match_key, $k, $matches ) )
				{
					if ( $k == "set_mainquerform_PreExecuteCode" )
                    {
                        if ( get_magic_quotes_gpc() )
                            $updates[$matches[1]] = stripslashes($v);
                        else
                            $updates[$matches[1]] = $v;
                    }
					else
                    {
                        if ( get_magic_quotes_gpc() )
                            $updates[$matches[1]] = stripslashes($v);
                        else
                            $updates[$matches[1]] = $v;
                    }
				}
			}

            // Validate user entry
            $this->validate_maintain_fields($updates);

			$anal = $this->analyse_form_item($match);

			// Based on results of analysis, decide what element we are updating ( column, query,
			// datasource etc ) 
			switch ( $anal["action"] )
			{
				case "sqlt":
					$qr =& $anal["quer"];
					$maintain_sql = $updates["QuerySql"];
					$sql = $updates["QuerySql"];
					if ( $qr->login_check(false) )
					{
						$p = new reportico_sql_parser($sql);
						if ( $p->parse() )
                        {
						    $p->import_into_query($qr);
						    if ( $this->query->datasource->connect() )
							    $p->test_query($this->query, $sql);
                        }
					}
					$updateitem =& $anal["item"];
                    if ( isset($updates["SQLRaw"]) )
					    $updateitem->sql_raw = $qr->sql_raw;
					break;
							
				case "form":
					$updateitem =& $anal["item"];
					foreach ( $updates as $k => $v )
					{
						$updateitem->set_attribute($k, $v);
					}
					break;

				case "assg":
                    $styletxt = "";
					if ( $updates["AssignStyleFgColor"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'color', '".$updates["AssignStyleFgColor"]."');";
					if ( $updates["AssignStyleBgColor"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'background-color', '".$updates["AssignStyleBgColor"]."');";
					if ( $updates["AssignStyleFontName"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'font-family', '".$updates["AssignStyleFontName"]."');";
					if ( $updates["AssignStyleFontSize"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'font-size', '".$updates["AssignStyleFontSize"]."');";
					if ( $updates["AssignStyleWidth"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'width', '".$updates["AssignStyleWidth"]."');";

                    if ( $updates["AssignStyleFontStyle"] && $updates["AssignStyleFontStyle"] != "NONE" )
                    {
                        $stylevalue = "none";
                        if ( $updates["AssignStyleFontStyle"] == "BOLD" || $updates["AssignStyleFontStyle"] == "BOLDANDITALIC" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'font-weight', 'bold');";
                        if ( $updates["AssignStyleFontStyle"] == "ITALIC" || $updates["AssignStyleFontStyle"] == "BOLDANDITALIC" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'font-style', 'italic');";
                        if ( $updates["AssignStyleFontStyle"] == "NORMAL" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'font-style', 'normal');";
                        if ( $updates["AssignStyleFontStyle"] == "UNDERLINE" ) 
                        if ( $updates["AssignStyleFontStyle"] == "UNDERLINE" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'text-decoration', 'underline');";
                        if ( $updates["AssignStyleFontStyle"] == "OVERLINE" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'text-decoration', 'overline');";
                        if ( $updates["AssignStyleFontStyle"] == "BLINK" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'text-decoration', 'blink');";
                        if ( $updates["AssignStyleFontStyle"] == "STRIKETHROUGH" ) 
					        if ( $updates["AssignStyleFontStyle"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'text-decoration', 'line-through');";
                    }

                    if ( !$updates["AssignStyleBorderStyle"] || $updates["AssignStyleBorderStyle"] == "NOBORDER" )
                    {
                        if ( $updates["AssignStyleBorderSize"] || $updates["AssignStyleBorderColor"] )
						    trigger_error ( template_xlate("SETBORDERSTYLE"), E_USER_ERROR );
                    }
                    else
                    {
                        $stylevalue = "none";
                        if ( $updates["AssignStyleBorderStyle"] == "SOLIDLINE" ) $stylevalue = "solid";
                        if ( $updates["AssignStyleBorderStyle"] == "DASHED" ) $stylevalue = "dashed";
                        if ( $updates["AssignStyleBorderStyle"] == "DOTTED" ) $stylevalue = "dotted";
					    $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'border-style', '".$stylevalue."');";
					    if ( $updates["AssignStyleBorderSize"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'border-width', '".$updates["AssignStyleBorderSize"]."');";
					    if ( $updates["AssignStyleBorderColor"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'border-color', '".$updates["AssignStyleBorderColor"]."');";
                    }

					if ( $updates["AssignStyleMargin"] ) 
                    {
                        $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'margin', '".$updates["AssignStyleMargin"]."');";
                        if ( $updates["AssignStyleLocType"] == "PAGE" && ! $updates["AssignStyleWidth"] )
                        {
			                handle_debug ( template_xlate("PAGEMARGINWITHWIDTH"), 0 );
                        }
                    }
					if ( $updates["AssignStylePadding"] ) $styletxt .=  "apply_style('".$updates["AssignStyleLocType"]."', 'padding', '".$updates["AssignStylePadding"]."');";
                    if ( $styletxt )
                        $updates["Expression"] = $styletxt;

					if ( $updates["AssignAggType"] )
					{
							$aggtype = "";
							$aggcol = $updates["AssignAggCol"];
							$agggroup = $updates["AssignAggGroup"];
							switch ( $updates["AssignAggType"] )
							{
									case "SUM": $aggtype = "sum"; break;
									case "MIN": $aggtype = "min"; break;
									case "SKIPLINE": $aggtype = "skipline"; $aggcol = ""; break;
									case "MAX": $aggtype = "max"; break;
									case "COUNT": $aggtype = "count"; $aggcol = ""; break;
									case "AVERAGE": $aggtype = "avg"; break;
									case "PREVIOUS": $aggtype = "old"; break;
									case "SUM": $aggtype = "sum"; break;
							}
							if ( $agggroup && $aggcol )
								$updates["Expression"] =  $aggtype."({".$updates["AssignAggCol"]."},{". $updates["AssignAggGroup"]."})";
							else if ( $aggcol )
								$updates["Expression"] =  $aggtype."({".$updates["AssignAggCol"]."})";
							else if ( $agggroup )
								$updates["Expression"] =  $aggtype."({".$updates["AssignAggGroup"]."})";
							else 
								$updates["Expression"] =  $aggtype."()";
					}

					if ( $updates["AssignGraphicBlobCol"] && $updates["AssignGraphicBlobTab"] && $updates["AssignGraphicBlobMatch"])
					{
						$updates["Expression"] = 
							"imagequery(\"SELECT ".$updates["AssignGraphicBlobCol"].
								" FROM ".$updates["AssignGraphicBlobTab"].
								" WHERE ".$updates["AssignGraphicBlobMatch"]." ='\".{".
                                $updates["AssignGraphicReportCol"]."}.\"'\",\"".$updates["AssignGraphicWidth"]."\")";
					}

					if ( $updates["AssignHyperlinkLabel"] ) 
                    {
                        $hlabel = $updates["AssignHyperlinkLabel"];
                        $hurl = $updates["AssignHyperlinkUrl"];
                        if ( !preg_match ( "/^{.*}$/", $hlabel ) )
                            $hlabel = "'$hlabel'";
                        $hurl = "'$hurl'";
                        $hurl = preg_replace("/{/", "'.{", $hurl);
                        $hurl = preg_replace("/}/", "}.'", $hurl);
                        $updates["Expression"] =  "embed_hyperlink(".$hlabel.", ".$hurl.", true, false);";
                    }

					if ( $updates["AssignImageUrl"] ) 
                    {
                        $imgurl = $updates["AssignImageUrl"];
                        $imgurl = "'$imgurl'";
                        $imgurl = preg_replace("/{/", "'.{", $imgurl);
                        $imgurl = preg_replace("/}/", "}.'", $imgurl);
                        $updates["Expression"] =  "embed_image(".$imgurl.");";
                    }

					if ( $updates["DrilldownReport"] )
					{
							$this->query->drilldown_report = $updates["DrilldownReport"];
							$q = new reportico();
                            $q->projects_folder = $this->query->projects_folder;
							global $g_project;
							$q->reports_path = $q->projects_folder."/".$g_project;
							$reader = new reportico_xml_reader($q, $updates["DrilldownReport"], false);
							$reader->xml2query();

                            $content = "Drill";
                            $url = $this->query->get_action_url()."?xmlin=".$updates["DrilldownReport"]."&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=".$g_project;
							$startbit= "'<a target=\"_blank\" href=\"".$this->query->get_action_url()."?xmlin=".$updates["DrilldownReport"]."&execute_mode=EXECUTE&target_format=HTML&target_show_body=1&project=".$g_project;
							$midbit = "";
							foreach ( $q->lookup_queries as $k => $v )
							{
									
									$testdd = "DrilldownColumn_".$v->query_name;

									if ( array_key_exists($testdd, $updates ) )
									{
										if ( $updates[$testdd] )
										{
												$midbit .= "&MANUAL_".$v->query_name."='.{".$updates[$testdd]."}.'";
												if ( $v->criteria_type == "DATERANGE" )
													$midbit = "&MANUAL_".$v->query_name."_FROMDATE='.{".$updates[$testdd]."}.'&".
													"MANUAL_".$v->query_name."_TODATE='.{".$updates[$testdd]."}.'";
										}
									}
							}
                            $url .= $midbit;
							unset($q);
							if ( $midbit )
								$updates["Expression"] = "embed_hyperlink('".$content."', '". $url. "', true, true)";
                            
					}

					$updateitem =& $anal["item"];

					if ( $assignname = key_value_in_array($updates, "AssignNameNew") )
					{
						$found = false;
						foreach ( $anal["quer"]->columns as $querycol )
						{
							if (  $querycol->query_name == $assignname )
							{	
								$found = true;
							}
						}

						if ( !$found ) 
						{
							$anal["quer"]->create_query_column( $assignname, "", "", "", "",
										'####.###',
										false);
						}
			
						$updates["AssignName"] = $assignname;
					}
	

					$updateitem->__construct(
					$updates["AssignName"], $updates["Expression"], $updates["Condition"]);
					break;

				case "pgft":
                    if ( isset($updates["FooterText_shadow"]) && $updates["FooterText_shadow"] != $updates["FooterText"] )
                        $updates["FooterText"] = $updates["FooterText"];
                    else
					    $updates["FooterText"] = $this->apply_update_styles("PageFooter", $updates, $updates["FooterText"]);
					$updateitem =& $anal["item"];
					$updateitem->__construct(
							$updates["LineNumber"], $updates["FooterText"]);
					break;

				case "clnk":
					$qr =& $anal["crit"]->lookup_query;
					$this->query->set_criteria_link(
								$updates["LinkFrom"], $updates["LinkTo"],
										$updates["LinkClause"], $anal["number"]);
					break;

				case "psql":
					$nm = $anal["number"];
					if  (isset ( $updates["SQLText"] ) )
						$this->query->pre_sql[$nm] = $updates["SQLText"];
					else
						$this->query->pre_sql[$nm] = "";
					break;

				case "grps":
					$updateitem =& $anal["item"];
					$nm = $anal["number"];
					$this->query->groups[$anal["number"]]->group_name =  $updates["GroupName"];
					$this->query->groups[$anal["number"]]->set_attribute("before_header",$updates["BeforeGroupHeader"]);
					$this->query->groups[$anal["number"]]->set_attribute("after_header",$updates["AfterGroupHeader"]);
					$this->query->groups[$anal["number"]]->set_attribute("before_trailer",$updates["BeforeGroupTrailer"]);
					$this->query->groups[$anal["number"]]->set_attribute("after_trailer",$updates["AfterGroupTrailer"]);
					$this->query->groups[$anal["number"]]->set_format("before_header",$updates["BeforeGroupHeader"]);
					$this->query->groups[$anal["number"]]->set_format("after_header",$updates["AfterGroupHeader"]);
					$this->query->groups[$anal["number"]]->set_format("before_trailer",$updates["BeforeGroupTrailer"]);
					$this->query->groups[$anal["number"]]->set_format("after_trailer",$updates["AfterGroupTrailer"]);
					break;

				case "crit":
					$updateitem =& $anal["item"];
					$qr =& $anal["quer"];

					$updateitem->set_attribute("column_title", $updates["Title"]);
					$nm = $anal["number"];
					$updateitem->query_name = $updates["Name"];

					if ( array_key_exists("QueryTableName", $updates) )
						$updateitem->table_name = $updates["QueryTableName"];
					else
						$updateitem->table_name = "";
					$updateitem->column_name = $updates["QueryColumnName"];
					if ( defined("SW_DYNAMIC_ORDER_GROUP" ) )
						$updateitem->_use = $updates["Use"];
					$updateitem->criteria_type = $updates["CriteriaType"];
					$updateitem->criteria_list = $updates["CriteriaList"];
					$updateitem->criteria_display = $updates["CriteriaDisplay"];
					$updateitem->expand_display = $updates["ExpandDisplay"];
					$updateitem->required = $updates["CriteriaRequired"];
                    //var_dump($updates);
					$updateitem->hidden = $updates["CriteriaHidden"];
					$updateitem->display_group = $updates["CriteriaDisplayGroup"];
					if ( array_key_exists("ReturnColumn", $updates) )
					{
						$updateitem->lookup_query->set_lookup_return($updates["ReturnColumn"]);
						$updateitem->lookup_query->set_lookup_display(
								$updates["DisplayColumn"], $updates["OverviewColumn"]);
						$updateitem->lookup_query->set_lookup_expand_match(
								$updates["MatchColumn"]);
					}
					$updateitem->set_criteria_required($updates["CriteriaRequired"]);
					$updateitem->set_criteria_hidden($updates["CriteriaHidden"]);
					$updateitem->set_criteria_display_group($updates["CriteriaDisplayGroup"]);
					$updateitem->set_criteria_defaults(
								$updates["CriteriaDefaults"]);
					$updateitem->set_criteria_help(
								$updates["CriteriaHelp"]);
					$updateitem->set_criteria_list(
								$updates["CriteriaList"]);
					break;

				case "qcol":
					$cn = $anal["colname"];
					$anal["quer"]->remove_column ( "NewColumn" );
					$anal["quer"]->create_query_column( $updates["Name"], "", "", "", "",
										'####.###',
										false
								);
					break;


				case "ords":
					break;

				case "dord":
					$cl = $anal["quer"]->display_order_set["column"][$anal["number"]]->query_name;
					$pn = $anal["number"] + 1;
					if ( $pn > $updates["OrderNumber"] )
						$anal["quer"]->set_column_order ( $cl, $updates["OrderNumber"], true );
					else
						$anal["quer"]->set_column_order ( $cl, $updates["OrderNumber"], false );

					break;

				case "ghdr":
					//$updates["GroupHeaderCustom"] = $this->apply_pdf_styles ( "GroupHeader", $updates, $updates["GroupHeaderCustom"] );
                    if ( isset ($this->field_display["GroupHeaderCustom"] ) )
                    {
                        if ( isset($updates["GroupHeaderCustom_shadow"]) && $updates["GroupHeaderCustom_shadow"] != $updates["GroupHeaderCustom"] )
                            $updates["GroupHeaderCustom"] = $updates["GroupHeaderCustom"];
                        else
					        $updates["GroupHeaderCustom"] = $this->apply_update_styles("GroupHeader", $updates, $updates["GroupHeaderCustom"]);
                    }
                    else
                        $updates["GroupHeaderCustom"] = false;
					$updateitem =& $anal["item"];
					$gr =& $anal["group"];
					$anal["quer"]->set_group_header_by_number 
							( $anal["groupname"], $anal["number"], $updates["GroupHeaderColumn"], $updates["GroupHeaderCustom"], $updates["ShowInHTML"],$updates["ShowInPDF"] );
					break;

				case "gtrl":
					//$updates["GroupTrailerCustom"] = $this->apply_pdf_styles ( "GroupTrailer", $updates, $updates["GroupTrailerCustom"] );
                    if ( isset ($this->field_display["GroupTrailerCustom"] ) )
                    {
                        if ( isset($updates["GroupTrailerCustom_shadow"]) && $updates["GroupTrailerCustom_shadow"] != $updates["GroupTrailerCustom"] )
                            $updates["GroupTrailerCustom"] = $updates["GroupTrailerCustom"];
                        else
					        $updates["GroupTrailerCustom"] = $this->apply_update_styles("GroupTrailer", $updates, $updates["GroupTrailerCustom"]);
                    }
                    else
                        $updates["GroupTrailerCustom"] = false;
					$updateitem =& $anal["item"];
					$gr =& $anal["group"];
					$anal["quer"]->set_group_trailer_by_number 
							( $anal["groupname"], $anal["number"], 
										$updates["GroupTrailerDisplayColumn"],
										$updates["GroupTrailerValueColumn"],
										$updates["GroupTrailerCustom"], $updates["ShowInHTML"],$updates["ShowInPDF"]
							   	);
					break;

				case "plot":
					$graph =& $anal["graph"];
					$pl =& $graph->plot[$anal["number"]];
					$pl["name"] = $updates["PlotColumn"];
					$pl["type"] = $updates["PlotType"];
				    $pl["linecolor"] = $updates["LineColor"];
                    if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE != "PCHART" )
                    {
					    $pl["fillcolor"] = $updates["FillColor"];
                    }
					$pl["legend"] = $updates["Legend"];
					break;

				case "grph":
					$qr =& $anal["quer"];
					$updateitem =& $anal["item"];
					$graph = &$qr->graphs[$anal["number"]];

					if ( !array_key_exists("GraphColumn", $updates ) )
					{
						trigger_error ( template_xlate("MUSTADDGROUP"), E_USER_ERROR );
					}
					else
						$graph->set_graph_column($updates["GraphColumn"]);

                    if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE != "PCHART" )
                    {
					    $graph->set_graph_color($updates["GraphColor"]);
					    $graph->set_grid(".DEFAULT",
							$updates["XGridDisplay"],".DEFAULT",
							$updates["YGridDisplay"],".DEFAULT"
							);
                    }
                    

					$graph->set_title($updates["Title"]);
					$graph->set_xtitle($updates["XTitle"]);
					$graph->set_xlabel_column($updates["XLabelColumn"]);
					$graph->set_ytitle($updates["YTitle"]);
					$graph->set_width($updates["GraphWidth"]);
					$graph->set_height($updates["GraphHeight"]);
					$graph->set_width_pdf($updates["GraphWidthPDF"]);
					$graph->set_height_pdf($updates["GraphHeightPDF"]);

                    if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE != "PCHART" )
                    {
					    $graph->set_title_font($updates["TitleFont"], $updates["TitleFontStyle"],
						    $updates["TitleFontSize"], $updates["TitleColor"]);
					    $graph->set_xtitle_font($updates["XTitleFont"], $updates["XTitleFontStyle"],
						    $updates["XTitleFontSize"], $updates["XTitleColor"]);
					    $graph->set_ytitle_font($updates["YTitleFont"], $updates["YTitleFontStyle"],
						    $updates["YTitleFontSize"], $updates["YTitleColor"]);
                    }

                    if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE != "PCHART" )
					    $graph->set_xaxis($updates["XTickInterval"],$updates["XTickLabelInterval"],$updates["XAxisColor"]);
                    else
					    $graph->set_xaxis(".DEFAULT",$updates["XTickLabelInterval"],".DEFAULT");

                    if ( defined("SW_GRAPH_ENGINE") && SW_GRAPH_ENGINE != "PCHART" )
                    {
					    $graph->set_yaxis($updates["YTickInterval"],$updates["YTickLabelInterval"],$updates["YAxisColor"]);
					    $graph->set_xaxis_font($updates["XAxisFont"], $updates["XAxisFontStyle"],
						    $updates["XAxisFontSize"], $updates["XAxisFontColor"]);
					    $graph->set_yaxis_font($updates["YAxisFont"], $updates["YAxisFontStyle"],
						    $updates["YAxisFontSize"], $updates["YAxisFontColor"]);
                    }
					$graph->set_margin_color($updates["MarginColor"]);
					$graph->set_margins($updates["MarginLeft"], $updates["MarginRight"],
						$updates["MarginTop"], $updates["MarginBottom"]);
					break;

				case "pghd":
					//$updates["HeaderText"] = $this->apply_pdf_styles ( "PageHeader", $updates, $updates["HeaderText"] );
                    if ( isset($updates["HeaderText_shadow"]) && $updates["HeaderText_shadow"] != $updates["HeaderText"] )
                        $updates["HeaderText"] = $updates["HeaderText"];
                    else
			            $updates["HeaderText"] = $this->apply_update_styles("PageHeader", $updates, $updates["HeaderText"]);
					$updateitem =& $anal["item"];
					$updateitem->__construct(
							$updates["LineNumber"], $updates["HeaderText"]);
					break;

				case "data":
					$this->query->source_type = $updates["SourceType"];
					break;

				case "conn":
					$updateitem =& $anal["item"];
					$updateitem->set_details($updates["DatabaseType"],
												$updates["HostName"],
												$updates["ServiceName"] );
					$updateitem->set_database($updates["DatabaseName"]);
					$updateitem->user_name = $updates["UserName"];
					$updateitem->disconnect();
					if ( !$updateitem->connect() )
					{
							$this->query->error_message = $updateitem->error_message;
					};
					break;
			}

			return $ret;
	}

	function get_matching_request_item ($match) 
	{
			$ret = false;
			foreach ( $_REQUEST as $k => $v )
			{
				if ( preg_match ( $match, $k ) )
				{
					return $k;
				}
			}
			return $ret;
	}

	function get_matching_post_item ($match) 
	{
			$ret = false;
			foreach ( $_POST as $k => $v )
			{
				if ( preg_match ( $match, $k ) )
				{
					return $k;
				}
			}
			return $ret;
	}

	// Processes the HTML get/post paramters passed through on the maintain screen
	function handle_user_entry () 
	{
		// First look for a parameter beginning "submit_". This will identify
		// What the user wanted to do. 

		$hide_area = false;
		$show_area = false;
		$maintain_sql = false;
		$xmlsavefile = false;
		$xmldeletefile = false;
		if ( ( $k = $this->get_matching_post_item("/^submit_/") ) )
		{
			// Strip off "_submit"
			preg_match("/^submit_(.*)/", $k, $match);

			// Now we should be left with a field element and an action
			// Lets strip the two
			$match1 = preg_split('/_/', $match[0]);
			$fld = $match1[1];
			$action = $match1[2];

			switch ( $action )
			{
				case "ADD":
					// We have chosen to set a block of data so pass through Request set and see which
					// fields belong to this set and take appropriate action
					$this->add_maintain_fields($fld);
					$show_area = $fld;
					break; 

				case "DELETE":
					// We have chosen to set a block of data so pass through Request set and see which
					// fields belong to this set and take appropriate action
					$this->delete_maintain_fields($fld);
					$show_area = $fld;
					break; 

				case "MOVEUP":
					// We have chosen to set a block of data so pass through Request set and see which
					// fields belong to this set and take appropriate action
					$this->moveup_maintain_fields($fld);
					$show_area = $fld;
					break; 

				case "MOVEDOWN":
					// We have chosen to set a block of data so pass through Request set and see which
					// fields belong to this set and take appropriate action
					$this->movedown_maintain_fields($fld);
					$show_area = $fld;
					break; 

				case "SET":
					// We have chosen to set a block of data so pass through Request set and see which
					// fields belong to this set and take appropriate action
					$this->update_maintain_fields($fld);
					$show_area = $fld;
					break; 

				case "REPORTLINK":
				case "REPORTLINKITEM":
					// Link in an item from another report
	                $this->link_in_report_fields ("link", $fld, $action) ;
					$show_area = $fld;
					break; 

				case "REPORTIMPORT":
				case "REPORTIMPORTITEM":
					// Link in an item from another report
	                $this->link_in_report_fields ("import", $fld, $action) ;
					$show_area = $fld;
					break; 

				case "SAVE":
					$xmlsavefile = $this->query->xmloutfile;
                    if ( !$xmlsavefile )
			            trigger_error ( template_xlate("UNABLE_TO_SAVE").template_xlate("SPECIFYXML"), E_USER_ERROR );
					break; 

				case "PREPARESAVE":
					$xmlsavefile = $this->query->xmloutfile;
				    set_reportico_session_param("execute_mode","PREPARE");
                    
                    if ( !$xmlsavefile )
                    {
                        header("HTTP/1.0 404 Not Found", true);
                            echo '<div class="swError">'.template_xlate("UNABLE_TO_SAVE").template_xlate("SPECIFYXML")."</div>";
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
					if ( $fld == "mainquerqury" )
					{
						// Main Query SQL Generation.
						$sql = stripslashes($_REQUEST["mainquerqury_SQL"]);

						$maintain_sql = $sql;
						if ( $this->query->login_check() )
						{
						    $p = new reportico_sql_parser($sql);
						    if ( $p->parse() )
                            {
						        $p->import_into_query($qr);
						        if ( $this->query->datasource->connect() )
							        $p->test_query($this->query, $sql);
                            }
						}
					}
					else
					{
						// It's a lookup 
						if ( preg_match("/mainquercrit(.*)qury/", $fld, $match1 ) )
						{
							$lookup = (int)$match1[1];
							$lookup_char = $match1[1];
							
							// Access the relevant crtieria item ..
							$qc = false;
							$ak = array_keys($this->query->lookup_queries);
							if ( array_key_exists ( $lookup, $ak ))
							{
									$q = $this->query->lookup_queries[$ak[$lookup]]->lookup_query;
							}
							else
							{
									$q = new reportico();
							}

							// Parse the entered SQL
							$sqlparm = $fld."_SQL";
							$sql = $_REQUEST[$sqlparm];
							$q->maintain_sql = $sql;
							$q = new reportico();
				            $p = new reportico_sql_parser($sql);
				            if ( $p->parse() )
                            {
						        if ( $p->test_query($this->query, $sql) )
						        {
							        $p->import_into_query($q);
							        $this->query->set_criteria_lookup($ak[$lookup], $q, "WHAT", "NOW");
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
		if ( !$show_area )
		{
			// User has not pressed SHOW_ button - this would have been picked up in previous submit
			// So look for longest shown item - this will allow us to draw the maintenace screen with
			// the correct item maximised
			foreach ( $_REQUEST as $k => $req )
			{
				if ( preg_match("/^shown_(.*)/", $k, $match ) )
				{
						$containee = "/^".$hide_area."/";
						$container = $match[1];
						if ( !preg_match ( $containee, $container ) )
						{
							if ( strlen ( $match[1] ) > strlen ( $show_area ) )
							{
								$show_area = $match[1];
							}
						}
				}
			}

		}

		if ( !$show_area )
			$show_area = "mainquer";

		$xmlout = new reportico_xml_writer($this->query);
		$xmlout->prepare_xml_data();

		// If Save option has been used then write data to the named file and
		// use this file as the defalt input for future queries
		if ( $xmlsavefile )
		{
			if ( $this->query->allow_maintain != "SAFE" && $this->query->allow_maintain != "DEMO" && SW_ALLOW_MAINTAIN )
			{
				$xmlout->write_file($xmlsavefile);
				set_reportico_session_param("xmlin",$xmlsavefile);
				unset_reportico_session_param("xmlintext");
			}
			else
				trigger_error ( template_xlate("SAFENOSAVE"), E_USER_ERROR );
		}

		// If Delete Report option has been used then remove the file
		// use this file as the defalt input for future queries
		if ( $xmldeletefile )
		{
			if ( $this->query->allow_maintain != "SAFE" && $this->query->allow_maintain != "DEMO" && SW_ALLOW_MAINTAIN )
			{
				$xmlout->remove_file($xmldeletefile);
				set_reportico_session_param("xmlin",false);
				unset_reportico_session_param("xmlintext");
			}
			else
				trigger_error ( template_xlate("SAFENODEL"), E_USER_ERROR );

		}

		$xml = $xmlout->get_xmldata();

		if ( $this->query->top_level_query )
		{
			$this->query->xmlintext = $xml;
		}

		$this->query->xmlin = new reportico_xml_reader($this->query, false, $xml);
		$this->query->xmlin->show_area = $show_area;
		$this->query->maintain_sql = false;
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_add_button ($in_tag, $in_value = false) 
	{
		$text = "";
		$text .= '<TD>';
		$text .= '<input class="'.$this->query->getBootstrapStyle('design_ok').'swMntButton reporticoSubmit" type="submit" name="submit_'.$in_tag.'_ADD" value="'.template_xlate("ADD").'">';
		$text .= '</TD>';

        // Show Import/Link options 
        // We allow import and linking to reports for criteria items
        // We import only for main query assignments
        $importtype = false;
        switch ( $in_tag )
        {
            case "mainquercrit" : $importtype = "LINKANDIMPORT"; break;
            case "mainquerassg" : $importtype = "IMPORT"; break;
            case "mainqueroutppghd" : $importtype = "IMPORT"; break;
            case "mainqueroutppgft" : $importtype = "IMPORT"; break;
            default; $importtype = false;
        }

        if ( $importtype )
	        $text .= $this->draw_report_link_panel ($importtype, $in_tag, $in_value, $this->query->reportlink_report) ;

		return $text;
	}


	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_movedown_button ($in_tag, $in_value = false) 
	{
		$text = "";
		//$text .= '<TD class="swMntUpDownButtonCell">';
		$text .= '<input class="swMntMoveDownButton reporticoSubmit" type="submit" name="submit_'.$in_tag.'_MOVEDOWN" value="">';
		//$text .= '</TD>';
		return $text;
	}

	// Show drop down allowing user to select a report to link in to report being designed
    // and subsequent elements from that report
	function & draw_report_link_panel ($link_or_import, $tag, $label, $preselectedvalue = false) 
	{
		$text = '<TD class="swMntSetField" style="text-align: right; color: #eeeeee; background-color: #000000;" colspan="1">';
		$type = "TEXTFIELD";
        $translateoptions = false;

		$striptag = preg_replace("/ .*/", "", $tag);
		$showtag = preg_replace("/ /", "_", $tag);
		$subtitle = "";
		if ( preg_match("/ /", $tag ) )
			$subtitle = preg_replace("/.* /", " ", $tag);

		if ( array_key_exists($striptag, $this->field_display ) )
		{
			$arval = $this->field_display[$striptag];
			if ( array_key_exists("Title", $arval ) )
				$title = $arval["Title"].$subtitle;

			if ( array_key_exists("Type", $arval ) )
				$type = $arval["Type"];

			if ( array_key_exists("XlateOptions", $arval ) )
				$translateoptions = $arval["XlateOptions"];

			if ( array_key_exists("EditMode", $arval ) )
				$edit_mode = $arval["EditMode"];

			if ( array_key_exists("Values", $arval ) )
				$tagvals = $arval["Values"];

		}

		$default = get_default($striptag, ".");

		$helppage = "importlink";
		if ( $helppage )
		{
            if ( $this->query->url_path_to_assets )
            {
			    $helpimg = $this->query->url_path_to_assets."/images/help.png";
			    $text .= '<a target="_blank" href="'.$this->help_path($helppage, $striptag).'">';
			    $text .= '<img class="swMntHelpImage" alt="tab" src="'.$helpimg.'">';
			    $text .= '</a>&nbsp;';
            }
            else
            {
			    $helpimg = find_best_url_in_include_path( "images/help.png" );
                $dr = get_reportico_url_path();
			    $text .= '<a target="_blank" href="'.$this->help_path($helppage, $striptag).'">';
			    $text .= '<img class="swMntHelpImage" alt="tab" src="'.$dr.$helpimg.'">';
			    $text .= '</a>&nbsp;';
            }
		}

        // Show options options to import or link
        $listarr = array();
        if ( $link_or_import == "IMPORT" || $link_or_import == "LINKANDIMPORT" )
            $listarr["import"] = template_xlate("IMPORTREPORT");
        if ( $link_or_import == "LINK" || $link_or_import == "LINKANDIMPORT" )
            $listarr["linkto"] = template_xlate("MAKELINKTOREPORT");
        $text .= $this->draw_array_dropdown("linkorimport_".$this->id, $listarr, $this->query->reportlink_or_import, false, false, true);

		$text .= '</a>&nbsp;&nbsp;';

        // Draw report names we can link to
        $text .= $this->draw_select_file_list ($this->query->reports_path, "/.*\.xml/", false, $preselectedvalue, true, false, "reportlink" );
        $text .= '<input class="'.$this->query->getBootstrapStyle('design_ok').'swMntButton reporticoSubmit" style="margin-right: 20px" type="submit" name="submit_'.$this->id.'_REPORTLINK" value="'.template_xlate("OK").'">';

        if ( $this->query->reportlink_report )
        {
            // Draw report criteria items we can link to
            $q =  load_existing_report ( $this->query->reportlink_report, $this->query->projects_folder );
            if ( !$q )
                trigger_error ( template_xlate("NOOPENLINK").$this->query->reportlink_report , E_USER_NOTICE);
            else if ( !$q->lookup_queries || count($q->lookup_queries) == 0 )
                trigger_error ( template_xlate("NOCRITLINK").$this->query->reportlink_report , E_USER_NOTICE);
            else
            {
                if ( $link_or_import == "LINK" )
	                $text .= template_xlate("MAKELINKTOREPORTITEM");
                else
                    $text .= template_xlate("IMPORTREPORT");
	            $text .= "&nbsp;";
                $listarr = array();
                $listarr["ALLITEMS"] = template_xlate("ALLITEMS");
                if ( $tag == "mainquercrit" )
                {
                    $lq = $q->lookup_queries;
		            foreach ( $lq as $k => $v )
                        $listarr[$v->query_name] = $v->query_name;
                }
                else if ( $tag == "mainquerassg" )
                {
                    $lq = $q->assignment;
		            foreach ( $lq as $k => $v )
                    {
                        if ( strlen ( $v->expression ) > 30 )
                            $listarr[$k] = $v->query_name . " = ". substr($v->expression, 0, 30) . "...";
                        else
                            $listarr[$k] = $v->query_name . " = ". $v->expression;
                    }
                }
                else if ( $tag == "mainqueroutppghd" )
                {
                    $lq = $q->page_headers;
		            foreach ( $lq as $k => $v )
                    {
                        if ( strlen ( $v->text ) > 30 )
                            $listarr[$k] = $k . " = ". substr($v->text, 0, 30) . "...";
                        else
                            $listarr[$k] = $k . " = ". $v->text;
                    }
                }
                else if ( $tag == "mainqueroutppgft" )
                {
                    $lq = $q->page_footers;
		            foreach ( $lq as $k => $v )
                    {
                        if ( strlen ( $v->text ) > 30 )
                            $listarr[$k] = $k . " = ". substr($v->text, 0, 30) . "...";
                        else
                            $listarr[$k] = $k . " = ". $v->text;
                    }
                }
    
                $text .= $this->draw_array_dropdown("reportlinkitem_".$this->id, $listarr, false, false, false, true);
                $text .= '<input class="'.$this->query->getBootstrapStyle('design_ok').'swMntButton reporticoSubmit" style="margin-right: 20px" type="submit" name="submit_'.$this->id.'_REPORTLINKITEM" value="'.template_xlate("OK").'">';
            }
        }

        $text .= '</TD>';
		//$text .= '</TR>';

		return $text;
	}

    // Generate link to help for specific field
    function help_path($section, $field = false)
    {
        $fieldtag = $field;
        if ( isset($this->field_display["$field"]) && isset($this->field_display["$field"]["DocId"] ))
            $fieldtag = $this->field_display["$field"]["DocId"] ;
        $path = $this->query->url_doc_site."/".$this->query->version."/"."doku.php?id=";

        if ( isset($this->field_display["$field"]) && isset($this->field_display["$field"]["DocSection"] ))
            $path .= $this->field_display["$field"]["DocSection"];
        else if ( $section )
            $path .= $this->get_help_link($section);


        if ( $fieldtag )
            $path .= "#$fieldtag";
        return $path;
    }

    // Creates select list box from a directory file list
	function draw_select_file_list ($path, $filematch, $showtag, $preselectedvalue, $addblank, $translateoptions, $fieldtype = "set" )
    {
        $keys = array();
        $keys[] = "";

        if ( $showtag )
            $showtag = "_".$showtag;

        if ( is_dir ( $this->query->reports_path ) )
            $testpath = $this->query->reports_path;
        else
            $testpath = find_best_location_in_include_path( $this->query->reports_path );

        if (is_dir($testpath)) 
        {
            if ($dh = opendir($testpath)) 
            {
                while (($file = readdir($dh)) !== false) 
                {
                    if ( preg_match ( $filematch, $file ) )
                            $keys[] = $file;
                }
                closedir($dh);
            }
        }
        else
            trigger_error ( template_xlate("NOOPENDIR").$this->query->reports_path , E_USER_NOTICE);

        $text = $this->draw_array_dropdown($fieldtype."_".$this->id.$showtag, $keys, $preselectedvalue, true, false);
        return $text;
    }


	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_moveup_button ($in_tag, $in_value = false) 
	{
		$text = "";
		//$text .= '<TD class="swMntUpDownButtonCell">';
		$text .= '<input class="swMntMoveUpButton reporticoSubmit" type="submit" name="submit_'.$in_tag.'_MOVEUP" value="">';
		//$text .= '</TD>';
		return $text;
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_delete_button ($in_tag, $in_value = false) 
	{
		$text = "";
		//$text .= '<TD class="swMntUpDownButtonCell">';
		$text .= '<input class="swMntDeleteButton reporticoSubmit" type="submit" name="submit_'.$in_tag.'_DELETE" value="">';
		//$text .= '</TD>';
		return $text;
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_select_box ($in_tag, $in_array, $in_value = false) 
	{
		$text = "";
		$text .= '<select class="'.$this->query->getBootstrapStyle('design_dropdown').'swPrpDropSelect" name="execute_mode">';
		$text .= '<OPTION selected label="MAINTAIN" value="MAINTAIN">Maintain</OPTION>';
		$text .= '<OPTION label="PREPARE" value="PREPARE">Prepare</OPTION>';
		$text .= '</SELECT>';
		return $text;
	}

	// Draws a tab menu item within a horizontal tab menu
	function & draw_show_hide_vtab_button ($in_tag, $in_value = false, 
			$in_moveup = false, $in_movedown = false, $in_delete = true) 
	{
		$text = "";
		if ( !$this->is_showing($in_tag ) )
		{
			$text .= '<LI  class="swMntVertTabMenuCellUnsel">';
			$text .= '<a class="" name="submit_'.$in_tag."_SHOW".'" >';
			$text .= '<input class="swMntVertTabMenuButUnsel reporticoSubmit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			if ( $in_delete )
				$text .= $this->draw_delete_button ($in_tag) ;
			if ( $in_moveup )
				$text .= $this->draw_moveup_button ($in_tag) ;
			if ( $in_movedown )
				$text .= $this->draw_movedown_button ($in_tag) ;
			$text .= '</a>';
			$text .= '</LI>';
		}
		else
		{
			$text .= '<LI  class="active swMntVertTabMenuCellSel">';
			$text .= '<a class="" name="submit_'.$in_tag."_SHOW".'" >';
			$text .= '<input class="swMntVertTabMenuButSel reporticoSubmit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			if ( $in_delete )
				$text .= $this->draw_delete_button ($in_tag) ;
			if ( $in_moveup )
				$text .= $this->draw_moveup_button ($in_tag) ;
			if ( $in_movedown )
				$text .= $this->draw_movedown_button ($in_tag) ;
			$text .= '</a>';
			$text .= '</LI>';
		}
		return $text;
		
	}

	// Draws a tab menu item within a horizontal tab menu
	function & draw_show_hide_tab_button ($in_tag, $in_value = false) 
	{

		$text = "";
        $in_value = template_xlate($in_value);

        // Only draw horizontal tab buttons if not mini maintain or they are relevant to tag
        if ( $partialMaintain = get_request_item("partialMaintain", false ) )
        {
            if ( preg_match("/_ANY$/", $partialMaintain)  )
            {
                $match1 = preg_replace("/_ANY/", "", $partialMaintain);
                $match2 = substr($in_tag, 0, strlen($match1));
                if ( $match1 != $match2 || $match1 == $in_tag)
                    return $text;
            }
            else
                return $text;
        }
    
		if ( !$this->is_showing($in_tag ) )
		{
			$text .= '<LI class="swMntTabMenuCellUnsel">';
			$text .= '<a class="swMntTabMenuBu1tUnsel reporticoSubmit" name="submit_'.$in_tag."_SHOW".'" >';
			$text .= '<input class="swMntTabMenuButUnsel reporticoSubmit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			$text .= '</a>';
			$text .= '</LI>';
		}
		else
		{
			$text .= '<LI  class="active swMntTabMenuCellSel">';
			$text .= '<a class="swMntTabMenuBu1tUnsel reporticoSubmit" name="submit_'.$in_tag."_SHOW".'" >';
			$text .= '<input class="swMntTabMenuButSel reporticoSubmit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			$text .= '</a>';
			$text .= '</LI>';
		}
		return $text;
		
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function & draw_show_hide_button ($in_tag, $in_value = false) 
	{
		$text = "";

		if ( !$this->is_showing($in_tag ) )
		{
			$text .= '<TD>';
			//$text .= '<input class="swMntTabMenuButUnsel reporticoSubmit" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			$text .= '<input size="1" style="visibility:hidden" class"reporticoSubmit" type="submit" name="unshown_'.$in_tag.'" value="">';
			$text .= '</TD>';
		}
		else
		{
			$text .= '<TD>';
			//$text .= '<input class="swMntTabMenuButSel" type="submit" name="submit_'.$in_tag."_SHOW".'" value="'.$in_value.'">';
			$text .= '<input size="1" style="visibility:hidden" class"reporticoSubmit" type="submit" name="shown_'.$in_tag.'" value="">';
			$text .= '</TD>';
		}
		return $text;
		
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	//
	// also we want to expand selected Query Column Types immediately into format so if
	// id ends in qcolXXXXXform then its true as well
	function is_showing ($in_tag) 
	{
		$container = $this->show_area;
		$containee = $in_tag;
		$ret = false;
		$match = "/^".$containee."/";
		if ( preg_match( $match, $container ) )
			$ret = true;

		$match = "/qcol....form$/";
		if ( !$ret && preg_match( $match, $containee ) )
			$ret = true;

		$match = "/pghd....form$/";
		if ( !$ret && preg_match( $match, $containee ) )
			$ret = true;

		$match = "/pgft....form$/";
		if ( !$ret && preg_match( $match, $containee ) )
			$ret = true;

		$match = "/grph...._/";
		if ( !$ret && preg_match( $match, $containee ) )
			$ret = true;

		return $ret;
	}

	// Works out whether a maintenance item should be shown on the screen based on the value
	// of the show_area parameter which was derived from the HTTP Request Data
	function is_showing_full ($in_tag) 
	{
		$match = "/qcol....$/";
		if ( preg_match( $match, $in_tag ) )
		{
				return true;
		}
		
		$match = "/qcol....form$/";
		if ( preg_match( $match, $in_tag ) )
				return true;

		$match = "/pghd....form$/";
		if ( preg_match( $match, $in_tag ) )
				return true;

		$match = "/pghd....$/";
		if ( preg_match( $match, $in_tag ) )
				return true;

		$match = "/pgft....form$/";
		if ( preg_match( $match, $in_tag ) )
				return true;

		$match = "/pgft....$/";
		if ( preg_match( $match, $in_tag ) )
				return true;

		$match = "/grps...._/";
		if ( preg_match( $match, $in_tag ) )
		{
			return true;
		}

		if ( $in_tag."detl" == $this->show_area )
			return true;

		if ( $in_tag == $this->show_area )
			return true;

		return false;
	}

	function & xml2html (&$ar, $from_key = false) 
	{
		$text = "";

		$hold_last = false;

		$fct = 0;
		foreach ( $ar as $k => $val )
		{
			$fct++;
			if ( is_array($val) )
			{
				$oldid = $this->id;

				// To get over fact switch does not operatoe for a zero value force k to be
				// -1 if it is 0 
				if ( is_numeric($k) && (int)$k  == 0 )
					$k = "ZERO";

				switch ( $k )
				{
					case "Report":
					case "CogModule":
						$this->id = "main";
						$text .= '<TABLE class="swMntMainBox">';
                        if ( get_request_item("partialMaintain", false ) )
		                    break;
						global $g_project;
		                		$text .= '<TR>';
						$text .= '<TD colspan="2">';
						$text .= '&nbsp;&nbsp;'.template_xlate('PROJECT').$g_project.'&nbsp;&nbsp;&nbsp;&nbsp;';
                        if ( $this->query->xmloutfile == "configureproject" )
						    $text .= template_xlate('REPORT_FILE').' <input style="display: inline" type="text" name="xmlout" value="">';
                        else
						    $text .= template_xlate('REPORT_FILE').' <input type="text" style="display: inline" name="xmlout" value="'.$this->query->xmloutfile.'">';
						$text .= '&nbsp;&nbsp;<input class="'.$this->query->getBootstrapStyle('button_admin').'swLinkMenu reporticoSubmit" type="submit" name="submit_xxx_SAVE" value="'.template_xlate("SAVE").'">';
						$text .= '&nbsp;&nbsp;<input class="'.$this->query->getBootstrapStyle('button_admin').'swLinkMenu reporticoSubmit" type="submit" name="submit_maintain_NEW" value="'.template_xlate("NEW_REPORT").'">';
						$text .= '<input class="'.$this->query->getBootstrapStyle('button_delete').'swLinkMenu reporticoSubmit" style="margin-left: 80px" type="submit" name="submit_xxx_DELETEREPORT" value="'.template_xlate("DELETE_REPORT").'">';
						$text .= '</TD>';
						$text .= '</TR>';
						//$text .= '<TR>';
						break;

					case "CogQuery":
					case "ReportQuery":
						$this->id .= "quer";
						//$text .= '</TR>';
						$text .= '</TABLE>';
						$text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
						
						// Force format Screen if none chosen
						$match = "/quer$/";
						if ( preg_match( $match, $this->show_area ) )
							$this->show_area .= "form";
						$text .= $this->draw_show_hide_tab_button ($this->id."form", "FORMAT") ;
						$text .= $this->draw_show_hide_tab_button ($this->id."qury", "QUERY_DETAILS") ;
						$text .= $this->draw_show_hide_tab_button ($this->id."assg", "ASSIGNMENTS") ;
						$text .= $this->draw_show_hide_tab_button ($this->id."crit", "CRITERIA") ;
						$text .= $this->draw_show_hide_tab_button ($this->id."outp", "OUTPUT") ;
						$text .= '</UL>';
						$text .= '<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
						break;

					case "SQL":
						$ct=count($val);
						$this->id .= "sqlt";
						break;

					case "Format":
						$ct=count($val);
						$this->id .= "form";
						if ( $this->id != "mainquerform" )
						{
							$text .= "\n<!--FORM SHOW --><TR>";
							$text .= $this->draw_show_hide_button ($this->id, "Format") ;
							$text .= "</TR>";
						}
						break;

					case 'Groups':
						$this->id .= "grps";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Groups") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "GROUP", "_key", true );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}

						break;

					case 'GroupTrailers':
						$this->id .= "gtrl";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Group Trailers") ;
							$text .= '</TR>';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "TRAILER", "_key", false );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'GroupHeaders':
						$this->id .= "ghdr";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Group Headers") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "HEADER", "_key", true );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
							if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'PreSQLS':
						$this->id .= "psql";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "PreSQLS") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "PRESQL", "_key", true );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;


					case 'OrderColumns':
						$this->id .= "ords";
						break;

					case 'DisplayOrders':
						$this->id .= "dord";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "", "ColumnName", true, false );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'Plots':
						$this->id .= "plot";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Plots") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "PLOT", "_key" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'Graphs':
						$this->id .= "grph";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Graphs") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "GRAPH", "_key" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'PageHeaders':
						$this->id .= "pghd";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Page Headers") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "PAGE_HEADER", "_key" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'PageFooters':
						$this->id .= "pgft";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Page Footers") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "PAGE_FOOTER", "_key" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'QueryColumns':
						$this->id .= "qcol";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= "\n<!--Debug Qcol-->";
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Query Columns") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "", "Name" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
							$text .= "\n<!--Debug Qcol-->";
						}

						break;

					case 'Output':
						$this->id .= "outp";
						$ct=count($val);
						if ( $this->id != "mainqueroutp" )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_show_hide_button ($this->id, "Output") ;
							if ( $this->is_showing ( $this->id ) )
							{
								$text .= '<TD colspan="3"><TABLE><TR>';
							}
						}
						if ( $this->is_showing ( $this->id ) )
						{
							// Force format Screen if none chosen
							$match = "/outp$/";
							if ( preg_match( $match, $this->show_area ) )
								$this->show_area .= "pghd";

							$text .= '<TR class="swMntRowBlock">';
							$text .= '<TD style="width: 100%;">';
						    $text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
							$text .= $this->draw_show_hide_tab_button ($this->id."pghd", "PAGE_HEADERS") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."pgft", "PAGE_FOOTERS") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."dord", "DISPLAY_ORDER") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."grps", "GROUPS") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."grph", "GRAPHS") ;
							$text .= '</UL>';
							$text .= '</TD>';
							$text .= '</TR>';
							$text .= '<TR class="swMntRowBlock">';
							$text .= '<TD style="width: 100%;">';
							$text .= '<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
						}
						break;

					case 'Datasource':
						$this->id .= "data";
						$ct=count($val);
						if ( $this->id != "mainquerdata" )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_show_hide_button ($this->id, "Data Source") ;
							if ( $this->is_showing ( $this->id ) )
							{
								$text .= '<TD colspan="3"><TABLE><TR>';
							}
						}
						break;

					case 'SourceConnection':
						$this->id .= "conn";
						$ct=count($val);
						$text .= '<TR class="swMntRowBlock">';
						$text .= '<TD colspan="3"><TABLE><TR>';
						$text .= $this->draw_show_hide_button ($this->id, "Connection") ;
						$text .= '</TR>';
						$text .= '<TR>';
						break;

					case 'EntryForm':
						break;

					case 'Query':
						$this->id .= "qury";
							$text .= '<!--Start Query-->';
						if (  $this->id == "mainquerqury" && $this->is_showing ( $this->id ) )
						{
							// Force format Screen if none chosen
							$match = "/qury$/";
							if ( preg_match( $match, $this->show_area ) )
								$this->show_area .= "sqlt";

							$text .= '<TR class="swMntRowBlock">';
							$text .= '<TD style="width: 100%;">';
						    $text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
							$text .= $this->draw_show_hide_tab_button ($this->id."sqlt", "SQL") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."qcol", "QUERY_COLUMNS") ;
							//$text .= $this->draw_show_hide_tab_button ($this->id."ords", "ORDER_BY") ;
							$text .= $this->draw_show_hide_tab_button ($this->id."psql", "PRESQLS") ;
							$text .= '</UL>';
							$text .= '<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
						}
							$text .= '<!--End Query-->';
						break;

					case 'Criteria':
						$this->id .= "crit";
						if ( $this->id != "mainquercrit" )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_show_hide_button ($this->id, "Criteria") ;
							$text .= '</TR>';
						}

							$text .= "\n<!--StartCrit".$this->id."-->";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Criteria") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "CRITERIAITEM", "Name", true );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}
						break;

					case 'CriteriaLinks':
						$this->id .= "clnk";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "Link") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "LINKS", "_key" );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
						}

						break;

					case 'Assignments':
						$this->id .= "assg";
						if ( $this->is_showing ( $this->id ) )
						{
							$text .= '<TR class="swMntRowBlock">';
							$text .= $this->draw_add_button ($this->id, "ASSIGNMENT") ;
							$text .= '</TR>';
							$text .= '</TABLE>';
							$text .='<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">';
							$text .= '<TR>';
							$text .= $this->panel_key_to_html($this->id, $val, "ASSIGNMENT", "AssignName", true );
							$text .= '<TD valign="top">';
							$element_counts[$k] = count($val);
                                                        if ( count($val) > 0 )
								$text .= '<TABLE class="swMntInnerRightBox">';
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
						if ( is_numeric ($k ) )
						{
							$str = sprintf( "%04d", $k);
							$this->id .= $str;

							$hold_last = true;
							if ( $from_key == "Assignments" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "Groups" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "GroupHeaders" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "GroupTrailers" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "PreSQLS" )
							{
							}
							if ( $from_key == "Plots" )
							{
							}
							if ( $from_key == "Graphs" )
							{
							}
							if ( $from_key == "PageHeaders" )
							{
							}
							if ( $from_key == "PageFooters" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "OrderColumns" )
							{
								$text .= '<TR class="swMntRowBlock">';
								$text .= $this->draw_show_hide_button ($this->id, "Order Column ".$k) ;
								$text .= $this->draw_delete_button ($this->id) ;
								$text .= '</TR>';
							}
							if ( $from_key == "DisplayOrders" )
							{
							}
							if ( $from_key == "QueryColumns" )
							{
								$ct=count($val);
								$ct++;
							}
							if ( $from_key == "Criteria" )
							{
								$this->current_criteria_name = $val["Name"];
							}
						}
						else
							$text .= "*****Got bad $k<br>";
						break;
				}

				if ( !$hold_last )
					$this->last_element = $k;

				if ( count($val) > 0 )
				{
					// Only generate HTML if the suitable element needs to be shown
					if ( $this->is_showing ( $this->id ) )
					{
						$text .= $this->xml2html($val, $k);
					}

				}

				$parent_id = $this->id;
				$this->id = $oldid;
				$this->level_ct--;

				if ( is_numeric($k) && (int)$k  == 0 )
					$k = "ZERO";

				switch ( $k )
				{
					case "Output":
						if ( $this->is_showing ( $parent_id ) )
						{
							$text .= "\n<!--End Output-->";
							$text .= '</TABLE>';
							$text .= '</TD>';
							$text .= '</TR>';
							break;
						}

					case "Report":
					case "CogModule":
						if ( $this->id )
						{
							$text .= '<!--Cog Mod-->';
							$text .= '</TABLE>';
						}
						break;

					case "CogQuery":
					case "ReportQuery":
						break;

					case "SQL":
						break;

					case "Format":
						if ( $this->is_showing ( $parent_id ) )
						if ( $this->id != "mainquer" )
						{
							$text .= "\n<!--End Format".$this->id." ".$parent_id."-->";
						}
						break;

					case 'PreSQLS':
					case 'OrderColumns':
						break;

					case 'Datasource':
						if ( $this->id != "mainquer" )
						{
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
						if ( $parent_id == "mainquerqury" && $this->is_showing ( $parent_id ) )
						{
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
						if ( $this->is_showing ( $parent_id ) )
						{
							$text .= "\n<!--General-".$parent_id." $k-->";
							$text .= '<TR>';
							$text .= '<TD>&nbsp;</TD>';
							$text .= '</TR>';
							if ( $element_counts[$k] > 0 )
							{
								$text .= '</TABLE>';
							}
							$text .= '</TD>';
							$text .= '</TR>';
						}
						break;

					case 'Assignments':
						if ( $this->is_showing ( $parent_id ) )
						{
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
						if ( is_numeric ($k ) )
						{
							$match = "/assg[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
									$text .= $this->assignment_aggregates( $parent_id);
							}

							$match = "/pghd[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
                                    $text .= $this->draw_style_wizard($parent_id, "PageHeader", $val, $tagct);
                                    //$text .= $this->query->apply_plugins("draw-section", array("parent" => &$this, "parent_id" => &$parent_id, "type" => "PageHeader", "value" => $val, "tagct" => &$tagct));
							}

							$match = "/ghdr[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
                                    $text .= $this->draw_style_wizard($parent_id, "GroupHeader", $val, $tagct);
							}

							$match = "/gtrl[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
                                    $text .= $this->draw_style_wizard($parent_id, "GroupTrailer", $val, $tagct);
							}

							$match = "/pgft[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
                                    $text .= $this->draw_style_wizard($parent_id, "PageFooter", $val, $tagct);
							}

							$match = "/grph[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
								{
										$text .= "\n<!--End grph bit-->";
										$text .= "</TABLE>";
										$text .= "</TD>";
										$text .= "</TR>";
								}
							}
							$match = "/grps[0-9][0-9][0-9][0-9]/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
								{
										$text .= "\n<!--End grop bit-->";
										$text .= "</TABLE>";
										$text .= "</TD>";
										$text .= "</TR>";
								}
							}
							$match = "/crit[0-9][0-9][0-9][0-9]$/";
							if ( preg_match( $match, $parent_id ) )
							{
								if ( $this->is_showing ( $parent_id ) )
								{
										$text .= "\n<!--end  crit bit ".$parent_id."-->";
										$text .= "</TABLE>";
										$text .= "</TD>";
										$text .= "</TR>";
								}
							}
						}
						break;
				}
			}
			else
			{
				// Force Group Header Trailer menu after group entry fields
				$match = "/grph[0-9][0-9][0-9][0-9]/";
				$match1 = "/grph[0-9][0-9][0-9][0-9]$/";
				if ( preg_match( $match1, $this->id ) && $fct == 1)
				{
					$match = "/grph[0-9][0-9][0-9][0-9]$/";
					if ( preg_match( $match, $this->show_area ))
						$this->show_area .= "detl";

					$text .= "\n".'<TR class="swMntRowBlock">'."\n";
					$text .= '	<TD style="width: 100%;" colspan="4">'."\n";
					$text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
					$text .= $this->draw_show_hide_tab_button ($this->id."detl", "DETAILS") ;
					$text .= $this->draw_show_hide_tab_button ($this->id."plot", "PLOTS") ;
					$text .= '		</UL>'."\n";
					$text .= '		<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">'."\n";
				}

				// Force Group Header Trailer menu after group entry fields
				$match = "/grps[0-9][0-9][0-9][0-9]/";
				$match1 = "/grps[0-9][0-9][0-9][0-9]$/";
				if ( preg_match( $match1, $this->id ) && $fct == 1)
				{
					$match = "/grps[0-9][0-9][0-9][0-9]$/";
					if ( preg_match( $match, $this->show_area ))
						$this->show_area .= "detl";

					$text .= "\n".'<TR class="swMntRowBlock">'."\n";
					$text .= '	<TD style="width: 100%;" colspan="4">'."\n";
					$text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
					$text .= $this->draw_show_hide_tab_button ($this->id."detl", "DETAILS") ;
					$text .= $this->draw_show_hide_tab_button ($this->id."ghdr", "HEADERS") ;
					$text .= $this->draw_show_hide_tab_button ($this->id."gtrl", "TRAILERS") ;
					$text .= '		</UL>'."\n";
					$text .= '		<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">'."\n";
				}

				// Force Criteria menu after group entry fields
				$match = "/crit[0-9][0-9][0-9][0-9]/";
				$match1 = "/crit[0-9][0-9][0-9][0-9]$/";
				if ( preg_match( $match1, $this->id ) && $fct == 1)
				{
					$match = "/crit[0-9][0-9][0-9][0-9]$/";
					if ( preg_match( $match, $this->show_area ))
						$this->show_area .= "detl";

					$text .= "\n<!--startcrit bit ".$this->id."-->";
					$text .= '<TR class="swMntRowBlock">'."\n";
					$text .= '	<TD style="width: 100%;" colspan="4">'."\n";
					$text .= '<UL style="width:100%" class="'.$this->query->getBootstrapStyle('htabs').'swMntMainBox">';
					$text .= $this->draw_show_hide_tab_button ($this->id."detl", "DETAILS") ;
                    if ( !isset ( $ar["LinkToReport"] ) || !$ar["LinkToReport"] )
                    {
					    $text .= $this->draw_show_hide_tab_button ($this->id."qurysqlt", "SQL") ;
					    $text .= $this->draw_show_hide_tab_button ($this->id."quryqcol", "QUERY_COLUMNS") ;
					    $text .= $this->draw_show_hide_tab_button ($this->id."clnk", "LINKS") ;
					    $text .= $this->draw_show_hide_tab_button ($this->id."quryassg", "ASSIGNMENTS") ;
                    }

					$text .= '		</UL>'."\n";
					$text .= '		<TABLE cellspacing="0" cellpadding="0" class="swMntInnerBox">'."\n";
				}
				
				if ( $this->is_showing_full ( $this->id ) )
				{
					if ( $k == "QuerySql" )
					{
						if ( !$this->current_criteria_name )
						{
							$q =& $this->query;
						}
						else
						{
							$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;
						}

						$out="";
						if ( $q->maintain_sql )
						{
							$out = $q->maintain_sql;
						}
						else
						{
							$q->build_query(false, "", true);
							$out =  $q->query_statement;
						}
						$val=$out;
					}

					if ( 
						$k != "TableSql"
						&& $k != "WhereSql"
						&& $k != "GroupSql"
						&& $k != "RowSelection"
						)
                    {
                        if ( isset($this->field_display[$k] ) )
						    $text .= $this->display_maintain_field($k, $val, $fct);
                        //else
                        if ( isset($this->field_display[$k]) && isset($this->field_display[$k]["HasChangeComparator"]) && $this->field_display[$k]["HasChangeComparator"] )
                        {
						    $text .= $this->display_maintain_field($k, $val, $fct, true, false, false, false, true);
                        }
                    }


				}
			}
		}
		return $text;

	}

    function draw_style_wizard(&$in_parent, $type, $val, &$tagct)
    {
        $parent = &$this;
        $text = "";
        $tagct = 1;
        $tmpid = $parent->id;
        $parent->id = $in_parent;
        $blocktype = "assignTypeStyle";
        $text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.
        '" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("OUTPUTSTYLESWIZARD").'</b></TD></TR>';
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
        if ( $parent->wizard_linked_to )
        {
            if (preg_match("/{STYLE[ ,]*([^}].*)}/", $parent->wizard_linked_to , $matches))
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
            if ( $styles["font-style"] == "noone" ) $styles["border-style"] = "NONE";

        if ( $styles["position"] )
            if ( $styles["position"] == "absolute" ) $styles["position"] = "ABSOLUTE";


        $text .= $parent->display_maintain_field("${type}StyleFgColor", $styles["color"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBgColor", $styles["background-color"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderStyle", $styles["border-style"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderSize", $styles["border-width"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleBorderColor", $styles["border-color"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleMargin", $styles["margin"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StylePadding", $styles["padding"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleHeight", $styles["height"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleWidth", $styles["width"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StylePosition", $styles["position"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontName", $styles["font-family"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontSize", $styles["font-size"], $tagct, true, false, $blocktype); $tagct++;
        $text .= $parent->display_maintain_field("${type}StyleFontStyle", $styles["font-style"], $tagct, true, false, $blocktype); $tagct++;

        if ( $type == "PageHeader" || $type == "PageFooter" || $type == "GroupHeader" || $type == "GroupTrailer" )
        {
            $text .= $parent->display_maintain_field("${type}StyleBackgroundImage", $styles["background-image"], $tagct, true, false, $blocktype); $tagct++;
        }

        $parent->id = $tmpid;
    
        return $text;
    }

    function apply_update_styles($type, &$updates, $applyto)
    {
        $styletxt = "";
        if ( $updates["${type}StyleFgColor"] ) $styletxt .=  "color: ".$updates["${type}StyleFgColor"].";";
        if ( $updates["${type}StyleBgColor"] ) $styletxt .=  "background-color:".$updates["${type}StyleBgColor"].";";
        if ( $updates["${type}StyleFontName"] ) $styletxt .=  "font-family:".$updates["${type}StyleFontName"].";";
        if ( $updates["${type}StyleFontSize"] ) $styletxt .=  "font-size:".$updates["${type}StyleFontSize"].";";
        if ( $updates["${type}StyleWidth"] ) $styletxt .=  "width:".$updates["${type}StyleWidth"].";";
        if ( $updates["${type}StyleHeight"] ) $styletxt .=  "height:".$updates["${type}StyleHeight"].";";

        if ( $updates["${type}StyleFontStyle"] && $updates["${type}StyleFontStyle"] != "NONE" )
        {
            $stylevalue = "none";
            if ( $updates["${type}StyleFontStyle"] == "BOLD" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-weight:bold;";
            if ( $updates["${type}StyleFontStyle"] == "ITALIC" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-style:italic;";
            if ( $updates["${type}StyleFontStyle"] == "NORMAL" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-style:normal;";
            if ( $updates["${type}StyleFontStyle"] == "UNDERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] == "UNDERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:underline;";
            if ( $updates["${type}StyleFontStyle"] == "OVERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:overline;";
            if ( $updates["${type}StyleFontStyle"] == "BLINK" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:blink;";
            if ( $updates["${type}StyleFontStyle"] == "STRIKETHROUGH" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:line-through;";
        }

        if ( $updates["${type}StylePosition"] )
        {
            $stylevalue = "none";
            //if ( $updates["${type}StylePosition"] == "RELATIVE" || $updates["${type}StylePosition"] == "relative" ) 
            if ( $updates["${type}StylePosition"] == "ABSOLUTE" )
                $styletxt .= "position: absolute;";
        }

        if ( !$updates["${type}StyleBorderStyle"] || $updates["${type}StyleBorderStyle"] == "NOBORDER" )
        {
            if ( $updates["${type}StyleBorderSize"] || $updates["${type}StyleBorderColor"] )
            trigger_error ( template_xlate("SETBORDERSTYLE"), E_USER_ERROR );
        }
        else
        {
            $stylevalue = "none";
            if ( $updates["${type}StyleBorderStyle"] == "SOLIDLINE" ) $stylevalue = "solid";
            if ( $updates["${type}StyleBorderStyle"] == "DASHED" ) $stylevalue = "dashed";
            if ( $updates["${type}StyleBorderStyle"] == "DOTTED" ) $stylevalue = "dotted";
            $styletxt .=  "border-style:$stylevalue;";
            if ( $updates["${type}StyleBorderSize"] ) $styletxt .=  "border-width:".$updates["${type}StyleBorderSize"].";";
            if ( $updates["${type}StyleBorderColor"] ) $styletxt .=  "border-color:".$updates["${type}StyleBorderColor"].";";
        }
        
        if ( $updates["${type}StylePadding"] ) 
        {
            $styletxt .=  "padding:".$updates["${type}StylePadding"].";";
        }

        if ( $updates["${type}StyleBackgroundImage"] ) 
        {
            $styletxt .=  "background-image:".$updates["${type}StyleBackgroundImage"].";";
        }

        if ( $updates["${type}StyleMargin"] ) 
        {
            $styletxt .=  "margin:".$updates["${type}StyleMargin"].";";
        }

        if ( $styletxt )
        {
            $applyto = preg_replace("/{STYLE [^}]*}/", "", $applyto);
            $applyto .= "{STYLE $styletxt}";
        }
        return $applyto;
    }


	function get_help_link($tag)
	{
		$helppage = false;
		$stub = substr($tag, 0, 12 );
		if ( $stub == "mainquercrit" )
			$helppage = "the_criteria_menu";
		else if ( $stub == "mainquerassg" )
			$helppage = "the_assignments_menu";
		else if ( $stub == "mainquerqury" )
			$helppage = "the_query_details_menu";
		else if ( $stub == "mainqueroutp" )
			$helppage = "the_output_menu";
		else if ( $stub == "mainquerform" )
			$helppage = "the_design_format_menu";
        else
            $helppage = $stub;
		return $helppage;
	}

	function & display_maintain_field($tag, $val, &$tagct, $translate = true, $overridetitle = false, $toggleclass = false, $togglestate = false, $draw_shadow = false)
	{
		$text = "";
		$striptag = preg_replace("/ .*/", "", $tag);
		$showtag = preg_replace("/ /", "_", $tag);
        $partialMaintain = get_request_item("partialMaintain", false );
        if ( $partialMaintain )
        {
            $x = $this->id . "_" . $showtag;
            if ( $partialMaintain != $x && !preg_match("/_ANY/", $partialMaintain ) )
                return $text;
        }

		$text .= "\n<!-- SETFIELD-->";
		$text .= '<TR';
        if ( $toggleclass ) 
        {
            if ( $togglestate )
                $text .= " class=\"".$toggleclass."\" style=\"display: table-row\" ";
            else
                $text .= " class=\"".$toggleclass."\" style=\"display: none\" ";
        }
        if ( $draw_shadow )
        {
            $text .= " style=\"display: none\" ";
        }
        $text .= '>';
		$type = "TEXTFIELD";
        $translateoptions = false;
		$title = $tag;
		$edit_mode = "FULL";
		$tagvals = array();

		$subtitle = "";
		if ( preg_match("/ /", $tag ) )
			$subtitle = preg_replace("/.* /", " ", $tag);

		if ( array_key_exists($striptag, $this->field_display ) )
		{
			$arval = $this->field_display[$striptag];
			if ( array_key_exists("Title", $arval ) )
				$title = $arval["Title"].$subtitle;

			if ( array_key_exists("Type", $arval ) )
				$type = $arval["Type"];

			if ( array_key_exists("WizardLink", $arval ) )
            {
                $this->wizard_linked_to = $val;
            }

			if ( array_key_exists("XlateOptions", $arval ) )
				$translateoptions = $arval["XlateOptions"];

			if ( array_key_exists("EditMode", $arval ) )
				$edit_mode = $arval["EditMode"];

			if ( array_key_exists("Values", $arval ) )
				$tagvals = $arval["Values"];

		}

        if ( $overridetitle )
            $title = $overridetitle;

		$default = get_default($striptag, ".");

		if ( $type == "HIDE" )
		{
			$tagct--;
			$test = "";
			return $text;
		}

		$helppage = $this->get_help_link($this->id);

		$text .= '<TD class="swMntSetField">';
		if ( $helppage )
		{
            if ( $this->query->url_path_to_assets )
            {
			    $helpimg = $this->query->url_path_to_assets."/images/help.png";
                $text .= '<a target="_blank" href="'.$this->help_path($this->id, $striptag).'">';
			    $text .= '<img class="swMntHelpImage" alt="tab" src="'.$helpimg.'">';
			    $text .= '</a>&nbsp;';
            }
            else
            {
			    $helpimg = find_best_url_in_include_path( "images/help.png" );
                $dr = get_reportico_url_path();
			    $text .= '<a target="_blank" href="'.$this->help_path($this->id, $striptag).'">';
			    $text .= '<img class="swMntHelpImage" alt="tab" src="'.$dr.$helpimg.'">';
			    $text .= '</a>&nbsp;';
            }
		}
        if ( $translate ) 
		    $text .= template_xlate($title);
        else
		    $text .= $title;
		if ( $edit_mode == "SAFE"  )
			if ( SW_SAFE_DESIGN_MODE ) 
				$text .= "<br>".template_xlate("SAFEOFF");
			else 
				$text .= "";
		$text .= '</TD>';

        if ( $draw_shadow )
            $shadow = "_shadow";
        else
            $shadow = "";

		// Display Field Entry
		$text .= '<TD class="swMntSetField" colspan="1">';
		switch ( $type )
		{
			case "PASSWORD":
				$text .= '<input class="'.$this->query->getBootstrapStyle('textfield').'" type="password" size="40%" name="set_'.$this->id."_".$showtag.$shadow.'" value="'.htmlspecialchars($val).'"><br>';
				break;

			case "TEXTFIELDREADONLY":
				$readonly = "readonly";
				$text .= '<input class="'.$this->query->getBootstrapStyle('textfield').'" type="text" size="40%" '.$readonly.' name="set_'.$this->id."_".$showtag.$shadow.'" value="'.htmlspecialchars($val).'">';
				break;

			case "TEXTFIELD":
			case "TEXTFIELDNOOK":
				$readonly = "";
				if ( $edit_mode == "SAFE" && ( $this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || SW_SAFE_DESIGN_MODE ) )
					$readonly = "readonly";
				$text .= '<input class="'.$this->query->getBootstrapStyle('textfield').'" type="text" size="40%" '.$readonly.' name="set_'.$this->id."_".$showtag.$shadow.'" value="'.htmlspecialchars($val).'">';
				break;

			case "TEXTBOX":
				$readonly = "";
				if ( $edit_mode == "SAFE" && ( $this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || SW_SAFE_DESIGN_MODE ) )
					$readonly = "readonly";
				$text .= '<textarea class="'.$this->query->getBootstrapStyle('textfield').'" '.$readonly.' cols="70" rows="20" name="set_'.$this->id."_".$showtag.$shadow.'" >';
				$text .= htmlspecialchars($val);
				$text .= '</textarea>';
				break;

			case "TEXTBOXNARROW":
				$readonly = "";
				if ( $edit_mode == "SAFE" && ( $this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || SW_SAFE_DESIGN_MODE ) )
					$readonly = "readonly";
				$text .= '<textarea class="'.$this->query->getBootstrapStyle('textfield').' swMntTextBoxNarrow" '.$readonly.' cols="70" rows="20" name="set_'.$this->id."_".$showtag.$shadow.'" >';
				$text .= htmlspecialchars($val);
				$text .= '</textarea>';
				break;

			case "TEXTBOXSMALL":
				$readonly = "";
				if ( $edit_mode == "SAFE" && ( $this->query->allow_maintain == "SAFE" || $this->query->allow_maintain == "DEMO" || SW_SAFE_DESIGN_MODE ) )
					$readonly = "readonly";
				$text .= '<textarea class="'.$this->query->getBootstrapStyle('textfield').'" '.$readonly.' cols="70" rows="4" name="set_'.$this->id."_".$showtag.$shadow.'" >';
				$text .= htmlspecialchars($val);
				$text .= '</textarea>';
				break;

			case "DROPDOWN":
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $tagvals, $val, false, $translateoptions);
				break;

			case "CRITERIA":
				$keys=array_keys($this->query->lookup_queries);
				if ( !is_array($keys) )
					$key = array();
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;

			case "GROUPCOLUMNS":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys = array();
				$keys[] = "REPORT_BODY";
				if ( is_array($q->columns) )
					foreach ( $q->columns as $col )
						$keys[] = $col->query_name;

				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "REPORTLIST":
				$keys = array();
				$keys[] = "";
                if ( is_dir ( $this->query->reports_path ) )
                    $testpath = $this->query->reports_path;
                else
				        $testpath = find_best_location_in_include_path( $this->query->reports_path );
				if (is_dir($testpath)) 
				{
    				if ($dh = opendir($testpath)) 
					{
        				while (($file = readdir($dh)) !== false) 
						{
							if ( preg_match ( "/.*\.xml/", $file ) )
								$keys[] = $file;
        				}
        				closedir($dh);
    				}
				}
				else
                    trigger_error ( template_xlate("NOOPENDIR").$this->query->reports_path , E_USER_NOTICE);

				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);

				break;
		
			case "FONTLIST":
				$keys = array();
				$keys[] = "";

                if ( $this->query->pdf_engine == "fpdf" )
                    $fontdir = "fpdf/font";
                else
                    $fontdir = "tcpdf/fonts";
                if ( is_dir ( $fontdir ) )
                    $testpath = $fontdir;
                else
				    $testpath = find_best_location_in_include_path( $fontdir );
				if (is_dir($testpath)) 
				{
    				if ($dh = opendir($testpath)) 
					{
        				while (($file = readdir($dh)) !== false) 
						{
							if ( preg_match ( "/.*\.php/", $file ) )
                            {
								$keys[] = preg_replace("/.php/", "", $file);
                            }
        				}
                        sort($keys);
        				closedir($dh);
    				}
				}
				else
                    trigger_error ( template_xlate("NOOPENDIR").$this->query->reports_path, E_USER_NOTICE );

                if( !in_array($val, $keys) )
                    $keys[] = $val;
                
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);

				break;
		
			case "STYLELOCTYPES":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "CELL";
				$keys[] = "ALLCELLS";
				$keys[] = "COLUMNHEADERS";
				$keys[] = "ROW";
				$keys[] = "PAGE";
				$keys[] = "BODY";
				$keys[] = "GROUPHEADERLABEL";
				$keys[] = "GROUPHEADERVALUE";
				$keys[] = "GROUPTRAILER";
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "FONTSTYLES":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "NONE";
				$keys[] = "BOLD";
				$keys[] = "ITALIC";
				$keys[] = "BOLDANDITALIC";
				$keys[] = "UNDERLINE";
				$keys[] = "NORMAL";
				$keys[] = "STRIKETHROUGH";
				$keys[] = "OVERLINE";
				$keys[] = "BLINK";
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "POSITIONS":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "";
				$keys[] = "RELATIVE";
				$keys[] = "ABSOLUTE";
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "BORDERSTYLES":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "NOBORDER";
				$keys[] = "NONE";
				$keys[] = "SOLIDLINE";
				$keys[] = "DOTTED";
				$keys[] = "DASHED";
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "AGGREGATETYPES":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "";
				$keys[] = "SUM";
				$keys[] = "AVERAGE";
				$keys[] = "MIN";
				$keys[] = "MAX";
				$keys[] = "PREVIOUS";
				$keys[] = "COUNT";
				$keys[] = "SKIPLINE";
				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "QUERYCOLUMNS":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				if ( $q && is_array($q->columns) )
					foreach ( $q->columns as $col )
						$keys[] = $col->query_name;

				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "QUERYCOLUMNSOPTIONAL":
				if ( !$this->current_criteria_name )
					$q =& $this->query;
				else
					$q =& $this->query->lookup_queries[$this->current_criteria_name]->lookup_query;

				$keys=array();
				$keys[] = "";
				if ( is_array($q->columns) )
					foreach ( $q->columns as $col )
						$keys[] = $col->query_name;

				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
				
			case "QUERYGROUPS":
				$q =& $this->query;

				$keys=array();
				if ( is_array($q->columns) )
					foreach ( $q->groups as $col )
						$keys[] = $col->group_name;

				$text .= $this->draw_array_dropdown("set_".$this->id."_".$showtag.$shadow, $keys, $val, false, $translateoptions);
				break;
		}

			$text .= '<TD class="swMntSetField" colspan="1">';
		if ( $default )
		{
			$text .= '&nbsp;('.$default.')';
		}
		else
			$text .= '&nbsp;';
		$text .= '</TD>';

        if ( $partial = get_request_item("partialMaintain", false ) )
        {
            $arr = explode("_" ,$partial);
            if ( count($arr) > 1 )
            {
                $partial = $arr[1];
            }
        }

		if ( $tagct == 1 || ( $partial == $tag && $partial != "ANY" ) )
		{
			$text .= "\n<!-- TAG 1-->";
			$text .= '<TD colspan="1">';
			if ( $type != "TEXTFIELDNOOK" )	
				$text .= '<input class="'.$this->query->getBootstrapStyle('design_ok').'swMntButton reporticoSubmit" type="submit" name="submit_'.$this->id.'_SET" value="'.template_xlate("OK").'">';
			else
				$text .= "&nbsp;";
			$text .= '</TD>';
		}
		$text .= '</TR>';

		return $text;
	}

	function draw_array_dropdown ($name, $ar, $val, $addblank, $translateoptions, $keysforid = false )
	{
		$text = "";


		if ( count($ar) == 0 )
		{
			$text .= '<input type="text" size="40%" name="'.$name.'" value="'.htmlspecialchars($val).'"><br>';
			return;
		}

		$text .= '<SELECT class="'.$this->query->getBootstrapStyle('design_dropdown').'swPrpDropSelectRegular" name="'.$name.'">';

		if ( $addblank )
			if ( !$val )
				$text .= '<OPTION selected label="" value=""></OPTION>';
			else
				$text .= '<OPTION label="" value=""></OPTION>';

		foreach ( $ar as $k => $v )
		{
            $label = $v;
            if ( $translateoptions )
                $label = template_xlate($v);
            $idval = $v;
            if ( $keysforid )
                $idval = $k;

			if ( $idval == $val )
				$text .= '<OPTION selected label="'.$label.'" value="'.$idval.'">'.$label.'</OPTION>';
			else
				$text .= '<OPTION label="'.$label.'" value="'.$idval.'">'.$label.'</OPTION>';
		}
		$text .= '</SELECT>';

		return $text;
	}

	function apply_pdf_styles($type, &$updates, $applyto)
	{
        $styletxt = "";
        if ( $updates["${type}StyleFgColor"] ) $styletxt .=  "color: ".$updates["${type}StyleFgColor"].";";
        if ( $updates["${type}StyleBgColor"] ) $styletxt .=  "background-color:".$updates["${type}StyleBgColor"].";";
        if ( $updates["${type}StyleFontName"] ) $styletxt .=  "font-family:".$updates["${type}StyleFontName"].";";
        if ( $updates["${type}StyleFontSize"] ) $styletxt .=  "font-size:".$updates["${type}StyleFontSize"].";";
        if ( $updates["${type}StyleWidth"] ) $styletxt .=  "width:".$updates["${type}StyleWidth"].";";

        if ( $updates["${type}StyleFontStyle"] && $updates["${type}StyleFontStyle"] != "NONE" )
        {
            $stylevalue = "none";
            if ( $updates["${type}StyleFontStyle"] == "BOLD" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-weight:bold;";
            if ( $updates["${type}StyleFontStyle"] == "ITALIC" || $updates["${type}StyleFontStyle"] == "BOLDANDITALIC" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-style:italic;";
            if ( $updates["${type}StyleFontStyle"] == "NORMAL" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "font-style:normal;";
            if ( $updates["${type}StyleFontStyle"] == "UNDERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] == "UNDERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:underline;";
            if ( $updates["${type}StyleFontStyle"] == "OVERLINE" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:overline;";
            if ( $updates["${type}StyleFontStyle"] == "BLINK" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:blink;";
            if ( $updates["${type}StyleFontStyle"] == "STRIKETHROUGH" ) 
            if ( $updates["${type}StyleFontStyle"] ) $styletxt .=  "text-decoration:line-through;";
        }

        if ( !$updates["${type}StyleBorderStyle"] || $updates["${type}StyleBorderStyle"] == "NOBORDER" )
        {
            if ( $updates["${type}StyleBorderSize"] || $updates["${type}StyleBorderColor"] )
            trigger_error ( template_xlate("SETBORDERSTYLE"), E_USER_ERROR );
        }
        else
        {
            $stylevalue = "none";
            if ( $updates["${type}StyleBorderStyle"] == "SOLIDLINE" ) $stylevalue = "solid";
            if ( $updates["${type}StyleBorderStyle"] == "DASHED" ) $stylevalue = "dashed";
            if ( $updates["${type}StyleBorderStyle"] == "DOTTED" ) $stylevalue = "dotted";
            $styletxt .=  "border-style:$stylevalue;";
            if ( $updates["${type}StyleBorderSize"] ) $styletxt .=  "border-width:".$updates["${type}StyleBorderSize"].";";
            if ( $updates["${type}StyleBorderColor"] ) $styletxt .=  "border-color:".$updates["${type}StyleBorderColor"].";";
        }
        
        if ( $updates["${type}StylePadding"] ) 
        {
            $styletxt .=  "padding:".$updates["${type}StylePadding"].";";
        }

        if ( $updates["${type}StyleBackgroundImage"] ) 
        {
            $styletxt .=  "background-image:".$updates["${type}StyleBackgroundImage"].";";
        }

        if ( $updates["${type}StyleMargin"] ) 
        {
            $styletxt .=  "margin:".$updates["${type}StyleMargin"].";";
        }

        if ( $styletxt )
        {
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
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("OUTPUTSTYLESWIZARD").'</b></TD></TR>';
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
		//$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("DATABASEGRAPHICWIZARD").'</b></TD></TR>';
		//$text .= $this->display_maintain_field("AssignGraphicBlobCol", false, $tagct, true, false, $blocktype); $tagct++;
		//$text .= $this->display_maintain_field("AssignGraphicBlobTab", false, $tagct, true, false, $blocktype); $tagct++;
		//$text .= $this->display_maintain_field("AssignGraphicBlobMatch", false, $tagct, true, false, $blocktype); $tagct++;
		//$text .= $this->display_maintain_field("AssignGraphicWidth", false, $tagct, true, false, $blocktype); $tagct++;
		//$text .= $this->display_maintain_field("AssignGraphicReportCol", false, $tagct, true, false, $blocktype); $tagct++;

		$this->id = $tmpid;

		return $text;
	}
*/
	function & assignment_aggregates($in_parent)
	{
		$text = "";

		$tagct = 1;
		$tmpid = $this->id;
		$this->id = $in_parent;
		$text .= '<TR><TD>&nbsp;</TD></TD>';
        $blocktype = "assignTypeImageUrl";
        $text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("OUTPUTIMAGE").'</b></TD></TR>';
		$text .= $this->display_maintain_field("AssignImageUrl", false, $tagct, true, false, $blocktype); $tagct++;
    
		$tagct = 1;
		$tmpid = $this->id;
		$this->id = $in_parent;
        $blocktype = "assignTypeHyper";
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("OUTPUTHYPERLINK").'</b></TD></TR>';
		$text .= $this->display_maintain_field("AssignHyperlinkLabel", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignHyperlinkUrl", false, $tagct, true, false, $blocktype); $tagct++;

		$tagct = 1;
		$tmpid = $this->id;
		$this->id = $in_parent;
        $blocktype = "assignTypeStyle";
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("OUTPUTSTYLESWIZARD").'</b></TD></TR>';
		$text .= $this->display_maintain_field("AssignStyleLocType", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleFgColor", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleBgColor", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleBorderStyle", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleBorderSize", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleBorderColor", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleMargin", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStylePadding", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleWidth", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleFontName", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleFontSize", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignStyleFontStyle", false, $tagct, true, false, $blocktype); $tagct++;

		$tagct = 1;
		$tmpid = $this->id;
		$this->id = $in_parent;
        $blocktype = "assignTypeAgg";
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("AGGREGATESWIZARD").'</b></TD></TR>';
		$text .= $this->display_maintain_field("AssignAggType", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignAggCol", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignAggGroup", false, $tagct, true, false, $blocktype); $tagct++;

		$tagct = 1;
        $blocktype = "assignTypeDbg";
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("DATABASEGRAPHICWIZARD").'</b></TD></TR>';
		$text .= $this->display_maintain_field("AssignGraphicBlobCol", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignGraphicBlobTab", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignGraphicBlobMatch", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignGraphicWidth", false, $tagct, true, false, $blocktype); $tagct++;
		$text .= $this->display_maintain_field("AssignGraphicReportCol", false, $tagct, true, false, $blocktype); $tagct++;

		$tagct = 1;
        $blocktype = "assignTypeDrill";
		$text .= '<TR><TD>&nbsp;</TD></TD>';
		$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">-</a><b>'.template_xlate("DRILLDOWNWIZARD").'</b></TD></TR>';
		$text .= $this->display_maintain_field("DrilldownReport", $this->query->drilldown_report, $tagct, true, false, $blocktype, true);

		$tagct++;
		if ( $this->query->drilldown_report )
		{
				$q = new reportico();
                $q->projects_folder = $this->query->projects_folder;
				global $g_project;
				$q->reports_path = $q->projects_folder."/".$g_project;
				$reader = new reportico_xml_reader($q, $this->query->drilldown_report, false);
				$reader->xml2query();
				foreach ( $q->lookup_queries as $k => $v )
				{

						$text .= $this->display_maintain_field("DrilldownColumn"." ".$v->query_name, false, $tagct, false, template_xlate("DRILLDOWNCOLUMN")." ".$v->query_name, $blocktype, true);
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
	function & panel_key_to_html_row($id, &$ar, $labtext, $labindex )
	{
		$text = "";
		$text .= '<TR>';
		foreach ( $ar as $key => $val )
		{
			$text .= '<TD>';

			$padstring = $id.str_pad($key, 4, "0", STR_PAD_LEFT);
			if ( $labindex == "_key" )
				$text .= $this->draw_show_hide_button ($padstring, $labtext." ".$key) ;
			else
				$text .= $this->draw_show_hide_button ($padstring, $labtext." ".$val[$labindex]) ;
			$text .= $this->draw_delete_button ($padstring) ;
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
	function & panel_key_to_html($id, &$ar, $paneltype, $labindex, 
			$draw_move_buttons = false, $draw_delete_button = true )
	{
		$text = "";
		$text .= '<TD valign="top" class="swMntMidSection">';
        $text .= '<DIV class="side-nav-container affix-top">';
		$text .= '<UL class="'.$this->query->getBootstrapStyle('vtabs').'swMntMidSectionTable">';

        $defaulttext = template_xlate($paneltype);
		$ct = 0;
		foreach ( $ar as $key => $val )
		{
			$drawup = false;
			$drawdown = false;
			if ( $draw_move_buttons )
			{
				if ( $ct > 0 )
					$drawup = true;
				if ( $ct < count($ar) - 1 )
					$drawdown = true;
			}


			$padstring = $id.str_pad($key, 4, "0", STR_PAD_LEFT);
            // Choose button label to reflect a numeric or named element
            if ( $labindex == "_key" )
                $labtext = $defaulttext." ".$key;
            else
                $labtext = $defaulttext." ".$val[$labindex];

            // For assignments the button can be a column assignment or a style assignment .. choose the appropriate
            // label

            if ( $paneltype == "ASSIGNMENT" )
            {
                if ( preg_match("/apply_style *\([ \"']*CELL/", $val["Expression"] ) )
                    $labtext = template_xlate("CELLSTYLE")." ".$val[$labindex];
                else if ( preg_match("/apply_style *\([ \"']*ROW/", $val["Expression"] ) )
                    $labtext = template_xlate("ROWSTYLE");
                else if ( preg_match("/apply_style *\([ \"']*PAGE/", $val["Expression"] ) )
                    $labtext = template_xlate("PAGESTYLE");
                else if ( preg_match("/apply_style *\([ \"']*BODY/", $val["Expression"] ) )
                    $labtext = template_xlate("REPORTBODYSTYLE");
                else if ( preg_match("/apply_style *\([ \"']*ALLCELLS/", $val["Expression"] ) )
                    $labtext = template_xlate("ALLCELLSSTYLE");
                else if ( preg_match("/apply_style *\([ \"']*COLUMNHEADERS/", $val["Expression"] ) )
                    $labtext = template_xlate("COLUMNHEADERSTYLE");
                else if ( preg_match("/apply_style *\([ \"']*GROUPHEADERLABEL/", $val["Expression"] ) )
                    $labtext = template_xlate("GRPHEADERLABELSTYLE");
                else if ( preg_match("/apply_style *\([ \"']*GROUPHEADERVALUE/", $val["Expression"] ) )
                    $labtext = template_xlate("GRPHEADERVALUESTYLE");
                else if ( preg_match("/apply_style *\([ \"']*GROUPTRAILER/", $val["Expression"] ) )
                    $labtext = template_xlate("GROUPTRAILERSTYLE");
            }
            
            
			$text .= $this->draw_show_hide_vtab_button ($padstring, $labtext, $drawup, $drawdown, $draw_delete_button) ;
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
	function xml2query () 
	{
		if ( array_key_exists("CogModule",$this->data ) )
			$q =& $this->data["CogModule"];
		else
			$q =& $this->data["Report"];

		$criteria_links = array();

		// Generate Output Information...
		$ds = false;
		if ( !$q )
			return;
		foreach ( $q as $cogquery )
		{
			// Set Query Attributes
			foreach ( $cogquery["Format"] as $att => $val )
			{
				$this->query->set_attribute($att, $val);
			}

			// Set DataSource
            if ( isset ( $cogquery["Datasource"] ) )
			foreach ( $cogquery["Datasource"] as $att => $val )
			{
				//if ( $att == "SourceType" )
					//$this->query->source_type = $val;

				if ( $att == "SourceConnection" )
				{
                    // No longer relevant - connections are not supplied in xml files
				}
			}

			// Set Query Columns
			if ( ! ($ef =& $this->get_array_element($cogquery,"EntryForm")) )
			{
				$this->ErrorMsg = "No EntryForm tag within Format";
				return false;
			}

			if ( ! ($qu =& $this->get_array_element($ef,"Query")) )
			{
				$this->ErrorMsg = "No Query tag within EntryForm";
				return false;
			}

			$this->query->table_text = $this->get_array_element($qu,"TableSql");
			$this->query->where_text = $this->get_array_element($qu,"WhereSql");
			$this->query->group_text = $this->get_array_element($qu,"GroupSql");
			$this->query->rowselection = $this->get_array_element($qu,"RowSelection");
			$has_cols = true;

			if ( ($qc =& $this->get_array_element($qu,"SQL")) )
			{
			    $this->query->sql_raw = $this->get_array_element($qc,"SQLRaw");
			}

			if ( ! ($qc =& $this->get_array_element($qu,"QueryColumns")) )
			{
				$this->ErrorMsg = "No QueryColumns tag within Query";
				$has_cols = false;
			}

			// Generate reportico_query_column for each column found
			if ( $has_cols )
			{
				foreach ( $qc as $col )
				{
					$in_query = true;
					if ( !$col["ColumnName"] )
						$in_query = false;

					$this->query->create_criteria_column 
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
					if ( ($fm =& $this->get_array_element($col,"Format")) )
					{
						foreach ( $fm as $att => $val )
						{
							$this->query->set_column_attribute($col["Name"], $att, $val );
						}
					}
				}


				// Generate Order By List
				if ( ($oc =& $this->get_array_element($qu,"OrderColumns")) )
				{
					// Generate reportico_query_column for each column found
					foreach ( $oc as $col )
					{
						if ( !$col["Name"] )
							return;
						$this->query->create_order_column 
						(
							$col["Name"],
							$col["OrderType"]
						);
					}
				}

				// Generate Query Assignments
				if ( ($as =& $this->get_array_element($ef,"Assignments")) )
				{
					foreach ( $as as $col )
					{
						if ( array_key_exists("AssignName", $col ) )
							$this->query->add_assignment ( $col["AssignName"], $col["Expression"], $col["Condition"]);
						else
							$this->query->add_assignment ( $col["Name"], $col["Expression"], $col["Condition"]);
					}
				}
			}


			// Generate Query Assignments
			if ( ($pq =& $this->get_array_element($qu,"PreSQLS")) )
			{
				foreach ( $pq as $col )
				{
					$this->query->add_pre_sql($col["SQLText"]);
				}
			}

			// Generate Output Information...
			if ( ($op =& $this->get_array_element($ef,"Output")) )
			{
				// Generate Page Headers
				if ( ($ph =  $this->get_array_element($op, "PageHeaders")) )
				{
					foreach ( $ph as $k => $phi )
					{
						$this->query->create_page_header($k, $phi["LineNumber"], $phi["HeaderText"] );
						if ( ($fm =& $this->get_array_element($phi,"Format")) )
							foreach ( $fm as $att => $val )
							{
								$this->query->set_page_header_attribute($k, $att, $val );
							}
					}
				}

				// Generate Page Footers
				if ( ($ph =  $this->get_array_element($op, "PageFooters")) )
				{
					foreach ( $ph as $k => $phi )
					{
						$this->query->create_page_footer($k, $phi["LineNumber"], $phi["FooterText"] );
						if ( ($fm =& $this->get_array_element($phi,"Format")) )
							foreach ( $fm as $att => $val )
							{
								$this->query->set_page_footer_attribute($k, $att, $val );
							}
					}
				}

				// Generate Display Orders
				if ( $has_cols && ($ph =  $this->get_array_element($op, "DisplayOrders")) )
				{
					foreach ( $ph as $k => $phi )
					{
						$this->query->set_column_order($phi["ColumnName"], $phi["OrderNumber"] );
					}
				}

				if ( $has_cols && ($ph =  $this->get_array_element($op, "Groups")) )
				{
					foreach ( $ph as $k => $phi )
					{
						if ( array_key_exists("GroupName", $phi ) )
							$gpname = $phi["GroupName"];
						else
							$gpname = $phi["Name"];

						$grn =$this->query->create_group( $gpname );

						if ( array_key_exists("BeforeGroupHeader", $phi ) )
						{
							$grn->set_attribute("before_header",$phi["BeforeGroupHeader"]);
							$grn->set_attribute("after_header",$phi["AfterGroupHeader"]);
							$grn->set_attribute("before_trailer",$phi["BeforeGroupTrailer"]);
							$grn->set_attribute("after_trailer",$phi["AfterGroupTrailer"]);
							$grn->set_format("before_header",$phi["BeforeGroupHeader"]);
							$grn->set_format("after_header",$phi["AfterGroupHeader"]);
							$grn->set_format("before_trailer",$phi["BeforeGroupTrailer"]);
							$grn->set_format("after_trailer",$phi["AfterGroupTrailer"]);
						}
		
						if ( ($gp =& $this->get_array_element($phi,"GroupHeaders")) )
							foreach ( $gp as $att => $val )
							{
                                    if ( !isset($val["GroupHeaderCustom"]) )
                                        $val["GroupHeaderCustom"] = false;
                                    if ( !isset($val["ShowInHTML"]) )
                                        $val["ShowInHTML"] = "yes";
                                    if ( !isset($val["ShowInPDF"]) )
                                        $val["ShowInPDF"] = "yes";
                                        
									$this->query->create_group_header($gpname, $val["GroupHeaderColumn"],$val["GroupHeaderCustom"],$val["ShowInHTML"],$val["ShowInPDF"]);
							}

						if ( ($gp =& $this->get_array_element($phi,"GroupTrailers")) )
							foreach ( $gp as $att => $val )
							{
                                    if ( !isset($val["GroupTrailerCustom"]) )
                                        $val["GroupTrailerCustom"] = false;
                                    if ( !isset($val["ShowInHTML"]) )
                                        $val["ShowInHTML"] = "yes";
                                    if ( !isset($val["ShowInPDF"]) )
                                        $val["ShowInPDF"] = "yes";
									$this->query->create_group_trailer($gpname, $val["GroupTrailerDisplayColumn"], 
																	$val["GroupTrailerValueColumn"],
																	$val["GroupTrailerCustom"],$val["ShowInHTML"],$val["ShowInPDF"]);
							}
					}
				}

				// Generate Graphs
				if ( $has_cols && ($gph =  $this->get_array_element($op, "Graphs")) )
				{
					foreach ( $gph as $k => $gphi )
					{
						$ka = array_keys($gphi);
						$gph =& $this->query->create_graph();

						$gph->set_graph_column($gphi["GraphColumn"]);

						$gph->set_title($gphi["Title"]);
						$gph->set_xtitle($gphi["XTitle"]);
						$gph->set_xlabel_column($gphi["XLabelColumn"]);
						$gph->set_ytitle($gphi["YTitle"]);
						//$gph->set_ylabel_column($gphi["YLabelColumn"]);
						//////HERE!!!
						if ( array_key_exists("GraphWidth", $gphi ) )
						{
							$gph->set_width($gphi["GraphWidth"]);
							$gph->set_height($gphi["GraphHeight"]);
							$gph->set_width_pdf($gphi["GraphWidthPDF"]);
							$gph->set_height_pdf($gphi["GraphHeightPDF"]);
						}
						else
						{
							$gph->set_width($gphi["Width"]);
							$gph->set_height($gphi["Height"]);
						}

						if ( array_key_exists("GraphColor", $gphi ) )
						{
							$gph->set_graph_color($gphi["GraphColor"]);
							$gph->set_grid($gphi["GridPosition"],
								$gphi["XGridDisplay"],$gphi["XGridColor"],
								$gphi["YGridDisplay"],$gphi["YGridColor"]
								);
							$gph->set_title_font($gphi["TitleFont"], $gphi["TitleFontStyle"],
								$gphi["TitleFontSize"], $gphi["TitleColor"]);
							$gph->set_xtitle_font($gphi["XTitleFont"], $gphi["XTitleFontStyle"],
								$gphi["XTitleFontSize"], $gphi["XTitleColor"]);
							$gph->set_ytitle_font($gphi["YTitleFont"], $gphi["YTitleFontStyle"],
								$gphi["YTitleFontSize"], $gphi["YTitleColor"]);
							$gph->set_xaxis($gphi["XTickInterval"],$gphi["XTickLabelInterval"],$gphi["XAxisColor"]);
							$gph->set_yaxis($gphi["YTickInterval"],$gphi["YTickLabelInterval"],$gphi["YAxisColor"]);
							$gph->set_xaxis_font($gphi["XAxisFont"], $gphi["XAxisFontStyle"],
								$gphi["XAxisFontSize"], $gphi["XAxisFontColor"]);
							$gph->set_yaxis_font($gphi["YAxisFont"], $gphi["YAxisFontStyle"],
								$gphi["YAxisFontSize"], $gphi["YAxisFontColor"]);
							$gph->set_margin_color($gphi["MarginColor"]);
							$gph->set_margins($gphi["MarginLeft"], $gphi["MarginRight"],
								$gphi["MarginTop"], $gphi["MarginBottom"]);
						}
						foreach ( $gphi["Plots"] as $pltk => $pltv )
						{
							$pl =& $gph->create_plot($pltv["PlotColumn"]);
                			$pl["type"] = $pltv["PlotType"];
                			$pl["fillcolor"] = $pltv["FillColor"];
                			$pl["linecolor"] = $pltv["LineColor"];
                			$pl["legend"] = $pltv["Legend"];
						}
					}
				}

			} // Output

			// Check for Criteria Items ...

			if ( ($crt =& $this->get_array_element($ef,"Criteria")) )
			{
			foreach ( $crt as $ci )
			{
				$critnm = $this->get_array_element($ci, "Name") ;

				$crittb = $this->get_array_element($ci, "QueryTableName") ;
				$critcl = $this->get_array_element($ci, "QueryColumnName") ;
				$linked_report = $this->get_array_element($ci, "LinkToReport") ;
				$linked_report_item = $this->get_array_element($ci, "LinkToReportItem") ;

                // If we are not designing a report then 
                // replace a linked criteria with the criteria
                // item it links to from another report
                if ( $linked_report && $this->query->execute_mode != "MAINTAIN" )
                {
					$q = new reportico();
					global $g_project;
					$q->reports_path = $q->projects_folder."/".$g_project;
					$reader = new reportico_xml_reader($q, $linked_report, false);
					$reader->xml2query();

					foreach ( $q->lookup_queries as $k => $v )
					{
                        
						$found = false;
						foreach ( $this->query->columns as $querycol )
							if (  $querycol->query_name == $v->query_name )
								$found = true;

					    $qu = new reportico();
                        if ( $linked_report_item == $v->query_name )
                            $this->query->lookup_queries[$v->query_name] = $v;
					}
                    continue;
                }
            
				if ( $crittb )
				{
					$critcl = $crittb.".".$critcl;
					$crittb = "";
				}
				$crittp = $this->get_array_element($ci, "CriteriaType") ;
				$critlt = $this->get_array_element($ci, "CriteriaList") ;
				$crituse = $this->get_array_element($ci, "Use") ;
				$critds = $this->get_array_element($ci, "CriteriaDisplay") ;
				$critexp = $this->get_array_element($ci, "ExpandDisplay") ;
				$critmatch = $this->get_array_element($ci, "MatchColumn") ;
				$critdefault = $this->get_array_element($ci, "CriteriaDefaults") ;
				$crithelp = $this->get_array_element($ci, "CriteriaHelp") ;
				$crittitle = $this->get_array_element($ci, "Title") ;
				$crit_required = $this->get_array_element($ci, "CriteriaRequired");
				$crit_hidden = $this->get_array_element($ci, "CriteriaHidden");
				$crit_display_group = $this->get_array_element($ci, "CriteriaDisplayGroup");
				$crit_lookup_return = $this->get_array_element($ci, "ReturnColumn");
				$crit_lookup_display = $this->get_array_element($ci, "DisplayColumn");
				$crit_criteria_display = $this->get_array_element($ci, "OverviewColumn");

				if ( $crittp == "ANYCHAR" ) $crittp = "TEXTFIELD";
				if ( $critds == "ANYCHAR" ) $critds = "TEXTFIELD";
				if ( $critexp == "ANYCHAR" ) $critexp = "TEXTFIELD";

				// Generate criteria lookup info unless its a link to a criteria in a nother report
				if ( !$linked_report && !($ciq =& $this->get_array_element($ci,"Query")) )
				{
					continue;
				}

				$critquery = new reportico();

				// Generate Criteria Query Columns
				if ( !$linked_report && ($ciqc =& $this->get_array_element($ciq,"QueryColumns")) )
				{
					foreach ( $ciqc as $ccol )
					{
						$in_query = true;
						if ( !$ccol["ColumnName"] )
							$in_query = false;

						$critquery->create_criteria_column 
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
				if ( !$linked_report && ($coc =& $this->get_array_element($ciq,"OrderColumns")) )
				{
					// Generate reportico_query_column for each column found
					foreach ( $coc as $col )
					{
						$critquery->create_order_column 
						(
							$col["Name"],
							$col["OrderType"]
						);
					}
				}
	
					
				if ( !$linked_report && ($as =& $this->get_array_element($ciq,"Assignments")) )
				{
					foreach ( $as as $ast )
					{
						if ( array_key_exists("AssignName", $ast ) )
							$critquery->add_assignment ( $ast["AssignName"], $ast["Expression"], $ast["Condition"]);
						else
							$critquery->add_assignment ( $ast["Name"], $ast["Expression"], $ast["Condition"]);
					}
				}

				// Generate Criteria Links  In Array for later use
				if ( !$linked_report && ($cl =& $this->get_array_element($ci,"CriteriaLinks")) )
				{
					foreach  ( $cl as $clitem )
					{
						$criteria_links[] = array ( 
									"LinkFrom" => $clitem["LinkFrom"],
									"LinkTo" => $clitem["LinkTo"],
									"LinkClause" => $clitem["LinkClause"]
									);
					}
				}

				// Set Query SQL Text
                if ( !$linked_report )
                {
				    $critquery->table_text = $this->get_array_element($ciq,"TableSql");
				    $critquery->where_text = $this->get_array_element($ciq,"WhereSql");
				    $critquery->group_text = $this->get_array_element($ciq,"GroupSql");
			        $critquery->sql_raw = $this->get_array_element($ciq,"SQLRaw");
				    $critquery->rowselection = $this->get_array_element($ciq,"RowSelection");
                }
				$critquery->set_lookup_return($crit_lookup_return);
				$critquery->set_lookup_display($crit_lookup_display, $crit_criteria_display);
				$critquery->set_lookup_expand_match($critmatch);
				$this->query->set_criteria_lookup($critnm, $critquery, $crittb, $critcl);
				$this->query->set_criteria_input($critnm, $crittp, $critds, $critexp, $crituse);
				$this->query->set_criteria_link_report($critnm, $linked_report, $linked_report_item);
//echo "SET $critnm $crit_required<BR>";
				$this->query->set_criteria_list($critnm, $critlt);
                //var_dump($crit_required);
				$this->query->set_criteria_required($critnm, $crit_required);
				$this->query->set_criteria_hidden($critnm, $crit_hidden);
				$this->query->set_criteria_display_group($critnm, $crit_display_group);
				//$this->query->set_criteria_help($critnm, $crithelp);
				$this->query->set_criteria_attribute($critnm, "column_title", $crittitle);
				
				$this->query->set_criteria_defaults($critnm, $critdefault);
				$this->query->set_criteria_help($critnm, $crithelp);
					
			} // End Criteria Item

			// Set up any Criteria Links
			foreach ( $criteria_links as $cl )
			{
					$this->query->set_criteria_link($cl["LinkFrom"], $cl["LinkTo"], $cl["LinkClause"]);
			}
		}
		}

	}
}


/**
 * Class reportico_xml_writer
 *
 * Responsible for converting the current report back into XML format
 * and for saving that report XML back to disk. 
 */
class reportico_xml_writer
{
	var $panel_type;
	var $query = NULL;
	var $visible = true;
	var $text = "";
	var $program = "";
	var $xml_version = "1.0";
	var $xmldata;

	function __construct(&$in_query)
	{
		$this->query = &$in_query;
	}

	function set_visibility($in_visibility)
	{
		$this->visible = $in_visibility;
	}

	function add_panel(&$in_panel)
	{
		$this->panels[] = &$in_panel;
	}


	function prepare_xml_data()
	{
		$xmlval = new reportico_xmlval ( "Report" );

		$cq =& $xmlval->add_xmlval ( "ReportQuery" );

		$at =& $cq->add_xmlval ( "Format" );

		// Query Attributes
		foreach  ( $this->query->attributes as $k => $v )
		{
				$el =& $at->add_xmlval ( $k, $v );
		}


		//$el =& $cq->add_xmlval ( "Name", $this->query->name );

		// Export Data Connection Details
		$ds =& $cq->add_xmlval ( "Datasource" );
		$el =& $ds->add_xmlval ( "SourceType", $this->query->source_type );

		$cn =& $ds->add_xmlval ( "SourceConnection" );
		switch ( $this->query->source_type )
		{
			case "database":
			case "informix":
			case "mysql":
			case "sqlite-2":
			case "sqlite-3":
				$el =& $cn->add_xmlval ( "DatabaseType",$this->query->datasource->driver );
				$el =& $cn->add_xmlval ( "DatabaseName",$this->query->datasource->database );
				$el =& $cn->add_xmlval ( "HostName",$this->query->datasource->host_name );
				$el =& $cn->add_xmlval ( "ServiceName",$this->query->datasource->service_name );
				$el =& $cn->add_xmlval ( "UserName",$this->query->datasource->user_name );
				$el =& $cn->add_xmlval ( "Password",$this->query->datasource->password );
				break;

			default:
				$el =& $cn->add_xmlval ( "DatabaseType",$this->query->datasource->driver );
				$el =& $cn->add_xmlval ( "DatabaseName",$this->query->datasource->database );
				$el =& $cn->add_xmlval ( "HostName",$this->query->datasource->host_name );
				$el =& $cn->add_xmlval ( "ServiceName",$this->query->datasource->service_name );
				$el =& $cn->add_xmlval ( "UserName",$this->query->datasource->user_name );
				$el =& $cn->add_xmlval ( "Password",$this->query->datasource->password );
				break;

		}

		$this->xmldata =& $xmlval;
		
		// Export Main Entry Form Parameters
		$ef =& $cq->add_xmlval ( "EntryForm" );

		// Export Main Query Parameters
		$qr =& $ef->add_xmlval ( "Query" );
		$el =& $qr->add_xmlval ( "TableSql", $this->query->table_text );
		$el =& $qr->add_xmlval ( "WhereSql", $this->query->where_text );
		$el =& $qr->add_xmlval ( "GroupSql", $this->query->group_text );
		$el =& $qr->add_xmlval ( "RowSelection", $this->query->rowselection );
		$sq =& $qr->add_xmlval ( "SQL" );
		$el =& $sq->add_xmlval ( "QuerySql", "" );
		$el =& $sq->add_xmlval ( "SQLRaw", $this->query->sql_raw);

		$qcs =& $qr->add_xmlval ( "QueryColumns" );
		foreach ( $this->query->columns as $col )
		{
			$qc =& $qcs->add_xmlval ( "QueryColumn" );
			$el =& $qc->add_xmlval ( "Name", $col->query_name );
			$el =& $qc->add_xmlval ( "TableName", $col->table_name );
			$el =& $qc->add_xmlval ( "ColumnName", $col->column_name );
			$el =& $qc->add_xmlval ( "ColumnType", $col->column_type );
			$el =& $qc->add_xmlval ( "ColumnLength", $col->column_length );

			// Column Attributes
			$at =& $qc->add_xmlval ( "Format" );
			foreach  ( $col->attributes as $k => $v )
				//if ( $v )
					$el =& $at->add_xmlval ( $k, $v );

		}

		$qos =& $qr->add_xmlval ( "OrderColumns" );
		foreach ( $this->query->order_set as $col )
		{
			$qoc =& $qos->add_xmlval ( "OrderColumn" );
			$el =& $qoc->add_xmlval ( "Name", $col->query_name );
			$el =& $qoc->add_xmlval ( "OrderType", $col->order_type );
		}

		$prcr =& $qr->add_xmlval ( "PreSQLS" );
		foreach ( $this->query->pre_sql as $prsq )
		{
			$sqtx =& $prcr->add_xmlval ( "PreSQL" );
			$el =& $sqtx->add_xmlval ( "SQLText", $prsq );
		}


		// Output Assignments
		$as =& $ef->add_xmlval ( "Assignments" );
		foreach ( $this->query->assignment as $col )
		{
			$qcas =& $as->add_xmlval ( "Assignment" );
			$el =& $qcas->add_xmlval ( "AssignName", $col->query_name );
			$el =& $qcas->add_xmlval ( "AssignNameNew", "" );
			$el =& $qcas->add_xmlval ( "Expression", $col->raw_expression );
			$el =& $qcas->add_xmlval ( "Condition", $col->raw_criteria );
		}


		// Add Lookup Attributes As Separate Criteria Item
		$cr =& $ef->add_xmlval ( "Criteria" );
		foreach ( $this->query->lookup_queries as $lq )
		{
			// find which columns are for returning displaying etc
			$lookup_return_col = "";
			$lookup_display_col = "";
			$lookup_abbrev_col = "";
				
			foreach ( $lq->lookup_query->columns as $cqc )
			{
				if ( $cqc->lookup_return_flag )
				{
					$lookup_return_col = $cqc->query_name;
				}
				if ( $cqc->lookup_display_flag )
				{
					$lookup_display_col = $cqc->query_name;
				}
				if ( $cqc->lookup_abbrev_flag )
				{
					$lookup_abbrev_col = $cqc->query_name;
				}
			}
			$ci =& $cr->add_xmlval ( "CriteriaItem" );
			$el =& $ci->add_xmlval ( "Name", $lq->query_name );
            if ( $lq->link_to_report )
            {
			    $el =& $ci->add_xmlval ( "LinkToReport", $lq->link_to_report );
			    $el =& $ci->add_xmlval ( "LinkToReportItem", $lq->link_to_report_item );
            }
            else
            {
			$el =& $ci->add_xmlval ( "Title", $lq->get_attribute("column_title") );
			$el =& $ci->add_xmlval ( "QueryTableName", $lq->table_name );
			$el =& $ci->add_xmlval ( "QueryColumnName", $lq->column_name );
			$el =& $ci->add_xmlval ( "CriteriaType", $lq->criteria_type );
			if ( defined("SW_DYNAMIC_ORDER_GROUP" ) )
				$el =& $ci->add_xmlval ( "Use", $lq->_use );
			$el =& $ci->add_xmlval ( "CriteriaHelp", $lq->criteria_help );
			$el =& $ci->add_xmlval ( "CriteriaDisplay", $lq->criteria_display );
			$el =& $ci->add_xmlval ( "ExpandDisplay", $lq->expand_display );
//echo "XML $lq->query_name $lq->criteria_display $lq->required $lq->criteria_list<BR>";
			$el =& $ci->add_xmlval ( "CriteriaRequired", $lq->required );
			$el =& $ci->add_xmlval ( "CriteriaHidden", $lq->hidden );
			$el =& $ci->add_xmlval ( "CriteriaDisplayGroup", $lq->display_group );
			$el =& $ci->add_xmlval ( "ReturnColumn", $lookup_return_col );
			$el =& $ci->add_xmlval ( "DisplayColumn", $lookup_display_col );
			$el =& $ci->add_xmlval ( "OverviewColumn", $lookup_abbrev_col );
			$el =& $ci->add_xmlval ( "MatchColumn", $lq->lookup_query->match_column );
			$el =& $ci->add_xmlval ( "CriteriaDefaults", $lq->defaults_raw );
			$el =& $ci->add_xmlval ( "CriteriaList", $lq->criteria_list );
			$q2 =& $ci->add_xmlval ( "Query" );
			$el =& $q2->add_xmlval ( "TableSql", $lq->lookup_query->table_text );
			$el =& $q2->add_xmlval ( "WhereSql", $lq->lookup_query->where_text );
			$el =& $q2->add_xmlval ( "GroupSql", $lq->lookup_query->group_text );
			$el =& $q2->add_xmlval ( "RowSelection", $lq->lookup_query->group_text );
		    $el =& $q2->add_xmlval ( "SQLRaw", $lq->lookup_query->sql_raw);
			$sq2 =& $q2->add_xmlval ( "SQL" );
			$el =& $sq2->add_xmlval ( "QuerySql", "" );
					
			$qcs2 =& $q2->add_xmlval ( "QueryColumns" );
			foreach ( $lq->lookup_query->columns as $lc )
			{

				$qc2 =& $qcs2->add_xmlval ( "QueryColumn" );
				$el =& $qc2->add_xmlval ( "Name", $lc->query_name );
				$el =& $qc2->add_xmlval ( "TableName", $lc->table_name );
				$el =& $qc2->add_xmlval ( "ColumnName", $lc->column_name );
				$el =& $qc2->add_xmlval ( "ColumnType", $lc->column_type );
				$el =& $qc2->add_xmlval ( "ColumnLength", $lc->column_length );

				// Column Attributes
				$at =& $qc2->add_xmlval ( "Format" );
				foreach  ( $lc->attributes as $k => $v )
					if ( $v )
						$el =& $at->add_xmlval ( $k, $v );
			}

			$qos2 =& $q2->add_xmlval ( "OrderColumns" );
			foreach ( $lq->lookup_query->order_set as $col )
			{
				$qoc2 =& $qos2->add_xmlval ( "OrderColumn" );
				$el =& $qoc2->add_xmlval ( "Name", $col->query_name );
				$el =& $qoc2->add_xmlval ( "OrderType", $col->order_type );
			}


			// Output Assignments
			$ascr =& $q2->add_xmlval ( "Assignments" );
			foreach ( $lq->lookup_query->assignment as $asg )
			{
				$qc =& $ascr->add_xmlval ( "Assignment" );
				$el =& $qc->add_xmlval ( "AssignName", $asg->query_name );
				$el =& $qc->add_xmlval ( "AssignNameNew", "" );
				$el =& $qc->add_xmlval ( "Expression", $asg->raw_expression );
				$el =& $qc->add_xmlval ( "Condition", $asg->raw_criteria );
			}


			$clcr =& $ci->add_xmlval ( "CriteriaLinks" );
			foreach ( $lq->lookup_query->criteria_links as $ky => $lk )
			{
				$clicr =& $clcr->add_xmlval ( "CriteriaLink" );
				$el =& $clicr->add_xmlval ( "LinkFrom", $lk["link_from"] );
				$el =& $clicr->add_xmlval ( "LinkTo", $lk["tag"] );
				$el =& $clicr->add_xmlval ( "LinkClause", $lk["clause"] );
			}
            }
		}

		// Output Report Output Details
		$op =& $ef->add_xmlval ( "Output" );
		{
			$ph =& $op->add_xmlval ( "PageHeaders" );
			foreach ( $this->query->page_headers as $k => $val )
			{
				$phi =& $ph->add_xmlval ( "PageHeader" );
				$el =& $phi->add_xmlval ( "LineNumber", $val->line );
				$el =& $phi->add_xmlval ( "HeaderText", $val->text );

				$phf =& $phi->add_xmlval ( "Format" );
				foreach  ( $val->attributes as $k => $v )
						$el =& $phf->add_xmlval ( $k, $v );
			}

			$pt =& $op->add_xmlval ( "PageFooters" );
			foreach ( $this->query->page_footers as $val )
			{
				$pti =& $pt->add_xmlval ( "PageFooter" );
				$el =& $pti->add_xmlval ( "LineNumber", $val->line );
				$el =& $pti->add_xmlval ( "FooterText", $val->text );

				$ptf =& $pti->add_xmlval ( "Format" );
				foreach  ( $val->attributes as $k => $v )
						$el =& $ptf->add_xmlval ( $k, $v );
			}

			$do =& $op->add_xmlval ( "DisplayOrders" );
			$ct = 0;
			if ( count($this->query->display_order_set) > 0 )
			foreach ( $this->query->display_order_set["itemno"] as $val )
			{
				$doi =& $do->add_xmlval ( "DisplayOrder" );
				$el =& $doi->add_xmlval ( "ColumnName", $this->query->display_order_set["column"][$ct]->query_name);
				$el =& $doi->add_xmlval ( "OrderNumber", $this->query->display_order_set["itemno"][$ct] );
				$ct++;
			}

			$gp =& $op->add_xmlval ( "Groups" );
			foreach ( $this->query->groups as $k => $val )
			{
				$gpi =& $gp->add_xmlval ( "Group" );
				$el =& $gpi->add_xmlval ( "GroupName", $val->group_name );
				$el =& $gpi->add_xmlval ( "BeforeGroupHeader", $val->get_attribute("before_header"));
				$el =& $gpi->add_xmlval ( "AfterGroupHeader", $val->get_attribute("after_header"));
				$el =& $gpi->add_xmlval ( "BeforeGroupTrailer", $val->get_attribute("before_trailer"));
				$el =& $gpi->add_xmlval ( "AfterGroupTrailer", $val->get_attribute("after_trailer"));

				$gph =& $gpi->add_xmlval ( "GroupHeaders" );
				foreach ( $val->headers as $k5 => $val2 )
				{
					$gphi =& $gph->add_xmlval ( "GroupHeader" );
                    if ( !isset($val2["GroupHeaderCustom"] ))
                        $val2["GroupHeaderCustom"] = false;
					$el =& $gphi->add_xmlval ( "GroupHeaderColumn", $val2["GroupHeaderColumn"]->query_name );
					$el =& $gphi->add_xmlval ( "GroupHeaderCustom", $val2["GroupHeaderCustom"]);
					$el =& $gphi->add_xmlval ( "ShowInHTML", $val2["ShowInHTML"]);
					$el =& $gphi->add_xmlval ( "ShowInPDF", $val2["ShowInPDF"]);
				}

				$gpt =& $gpi->add_xmlval ( "GroupTrailers" );
				foreach ( $val->trailers as $k2 => $val2 )
				{
					if ( is_array ( $val2) )
                    {
                        if ( !isset($val2["GroupTrailerCustom"] ))
                            $val2["GroupTrailerCustom"] = false;
					    $gpti =& $gpt->add_xmlval ( "GroupTrailer" );
					    $el =& $gpti->add_xmlval ( "GroupTrailerDisplayColumn", $val2["GroupTrailerDisplayColumn"] );
					    $el =& $gpti->add_xmlval ( "GroupTrailerValueColumn", $val2["GroupTrailerValueColumn"]->query_name );
					    $el =& $gpti->add_xmlval ( "GroupTrailerCustom", $val2["GroupTrailerCustom"]);
					    $el =& $gpti->add_xmlval ( "ShowInHTML", $val2["ShowInHTML"]);
					    $el =& $gpti->add_xmlval ( "ShowInPDF", $val2["ShowInPDF"]);
                    }
				}
			}

			$ggphs =& $op->add_xmlval ( "Graphs" );
			foreach ( $this->query->graphs as $k => $v )
			{
				$ggrp =& $ggphs->add_xmlval ( "Graph" );
				$el =& $ggrp->add_xmlval ( "GraphColumn", $v->graph_column );

				$el =& $ggrp->add_xmlval ( "GraphColor", $v->graphcolor );
				$el =& $ggrp->add_xmlval ( "Title", $v->title );
				$el =& $ggrp->add_xmlval ( "GraphWidth", $v->width );
				$el =& $ggrp->add_xmlval ( "GraphHeight", $v->height );
				$el =& $ggrp->add_xmlval ( "GraphWidthPDF", $v->width_pdf );
				$el =& $ggrp->add_xmlval ( "GraphHeightPDF", $v->height_pdf );
				$el =& $ggrp->add_xmlval ( "XTitle", $v->xtitle );
				$el =& $ggrp->add_xmlval ( "YTitle", $v->ytitle );
				$el =& $ggrp->add_xmlval ( "GridPosition", $v->gridpos );
				$el =& $ggrp->add_xmlval ( "XGridDisplay", $v->xgriddisplay );
				$el =& $ggrp->add_xmlval ( "XGridColor", $v->xgridcolor );
				$el =& $ggrp->add_xmlval ( "YGridDisplay", $v->ygriddisplay );
				$el =& $ggrp->add_xmlval ( "YGridColor", $v->ygridcolor );
				$el =& $ggrp->add_xmlval ( "XLabelColumn", $v->xlabel_column );

				$el =& $ggrp->add_xmlval ( "TitleFont", $v->titlefont );
				$el =& $ggrp->add_xmlval ( "TitleFontStyle", $v->titlefontstyle );
				$el =& $ggrp->add_xmlval ( "TitleFontSize", $v->titlefontsize );
				$el =& $ggrp->add_xmlval ( "TitleColor", $v->titlecolor );
				
				$el =& $ggrp->add_xmlval ( "XTitleFont", $v->xtitlefont );
				$el =& $ggrp->add_xmlval ( "XTitleFontStyle", $v->xtitlefontstyle );
				$el =& $ggrp->add_xmlval ( "XTitleFontSize", $v->xtitlefontsize );
				$el =& $ggrp->add_xmlval ( "XTitleColor", $v->xtitlecolor );
				
				$el =& $ggrp->add_xmlval ( "YTitleFont", $v->ytitlefont );
				$el =& $ggrp->add_xmlval ( "YTitleFontStyle", $v->ytitlefontstyle );
				$el =& $ggrp->add_xmlval ( "YTitleFontSize", $v->ytitlefontsize );
				$el =& $ggrp->add_xmlval ( "YTitleColor", $v->ytitlecolor );
				
				$el =& $ggrp->add_xmlval ( "XAxisColor", $v->xaxiscolor );
				$el =& $ggrp->add_xmlval ( "XAxisFont", $v->xaxisfont );
				$el =& $ggrp->add_xmlval ( "XAxisFontStyle", $v->xaxisfontstyle );
				$el =& $ggrp->add_xmlval ( "XAxisFontSize", $v->xaxisfontsize );
				$el =& $ggrp->add_xmlval ( "XAxisFontColor", $v->xaxisfontcolor );
				
				$el =& $ggrp->add_xmlval ( "YAxisColor", $v->yaxiscolor );
				$el =& $ggrp->add_xmlval ( "YAxisFont", $v->yaxisfont );
				$el =& $ggrp->add_xmlval ( "YAxisFontStyle", $v->yaxisfontstyle );
				$el =& $ggrp->add_xmlval ( "YAxisFontSize", $v->yaxisfontsize );
				$el =& $ggrp->add_xmlval ( "YAxisFontColor", $v->yaxisfontcolor );
				
				$el =& $ggrp->add_xmlval ( "XTickInterval", $v->xtickinterval );
				$el =& $ggrp->add_xmlval ( "YTickInterval", $v->ytickinterval );
				$el =& $ggrp->add_xmlval ( "XTickLabelInterval", $v->xticklabelinterval );
				$el =& $ggrp->add_xmlval ( "YTickLabelInterval", $v->yticklabelinterval );
			
				$el =& $ggrp->add_xmlval ( "MarginColor", $v->margincolor );
		
				$el =& $ggrp->add_xmlval ( "MarginLeft", $v->marginleft );
				$el =& $ggrp->add_xmlval ( "MarginRight", $v->marginright );
				$el =& $ggrp->add_xmlval ( "MarginTop", $v->margintop );
				$el =& $ggrp->add_xmlval ( "MarginBottom", $v->marginbottom );

				$gplt =& $ggrp->add_xmlval ( "Plots" );
				foreach ( $v->plot as $k => $val2 )
				{
					$gpltd =& $gplt->add_xmlval ( "Plot" );
					$el =& $gpltd->add_xmlval ( "PlotColumn", $val2["name"] );
					$el =& $gpltd->add_xmlval ( "PlotType", $val2["type"] );
					$el =& $gpltd->add_xmlval ( "LineColor", $val2["linecolor"] );
					$el =& $gpltd->add_xmlval ( "DataType", $val2["datatype"] );
					$el =& $gpltd->add_xmlval ( "Legend", $val2["legend"] );
					$el =& $gpltd->add_xmlval ( "FillColor",$val2["fillcolor"] );
				}
			}

		} // Output Section
	}

	function generate_web_service($in_report)
	{

		if ( !preg_match( "/(.*)\.xml/", $in_report, $matches ) )
		{
				trigger_error ( template_xlate("XMLCONFILE")." $in_report ".template_xlate("XMLFORM") , E_USER_NOTICE);
				return;
		}

		$stub = $matches[1];
		$wsdlfile = $matches[1].".wsdl";
		$srvphpfile = $matches[1]."_wsv.php";
		$cltphpfile = $matches[1]."_wcl.php";

		$this->prepare_web_service_file("wsdl.tpl", $wsdlfile, $stub);
		$this->prepare_web_service_file("soapclient.tpl", $cltphpfile, $stub);
		$this->prepare_web_service_file("soapserver.tpl", $srvphpfile, $stub);
		
	}
	
	function prepare_web_service_file($templatefile, $savefile = false, $instub)
	{
		global $g_project;
		$smarty = new smarty();
	 	$smarty->compile_dir = find_best_location_in_include_path( "templates_c" );

		$smarty->compile_dir = "/tmp";
		$smarty->assign('WS_SERVICE_NAMESPACE', SW_SOAP_NAMESPACE);
		$smarty->assign('WS_SERVICE_CODE', $instub);
		$smarty->assign('WS_SERVICE_NAME', $instub);
		$smarty->assign('PROJECT', $g_project);
		$smarty->assign('WS_SERVICE_BASEURL', SW_SOAP_SERVICEBASEURL);
		$smarty->assign('WS_REPORTNAME', $instub);
		$smarty->debug = true;

		$cols = array();
		$cols[] = array (
				"name" => "ReportName",
				"type" => "char",
				"length" => 0
			);
		foreach ( $this->query->columns as $col )
		{
			$cols[] = array (
				"name" => $col->query_name,
				"type" => $col->column_type,
				"length" => $col->column_length
				);
		}
		$smarty->assign("COLUMN_ITEMS", $cols);

		$crits = array();
		foreach ( $this->query->lookup_queries as $lq )
		{
			$crits[] = array (
				"name" => $lq->query_name
					);
		}
		$smarty->assign("CRITERIA_ITEMS", $crits);

		header('Content-Type: text/html');
		if ( $savefile )
		{
			$data = $smarty->fetch($templatefile, null, null, false );
			echo "<PRE>";
			echo "====================================================";
			echo "Writing $savefile from template $templatefile";
			echo "====================================================";
			echo htmlspecialchars($data);
			echo "</PRE>";
			$this->write_report_file($savefile, $data);
		}
		else
			$smarty->display($templatefile);
		
	}
	
	function prepare_wsdl_data($savefile = false)
	{
		$smarty = new smarty();
 		$smarty->compile_dir = find_best_location_in_include_path( "templates_c" );

		$smarty->assign('WS_SERVICE_NAMESPACE', SW_SOAP_NAMESPACE);
		$smarty->assign('WS_SERVICE_CODE', SW_SOAP_SERVICECODE);
		$smarty->assign('WS_SERVICE_NAME', SW_SOAP_SERVICENAME);
		$smarty->assign('WS_SERVICE_URL', SW_SOAP_SERVICEURL);
		$smarty->debugging = true;

		
		$cols = array();
		$cols[] = array (
				"name" => "ReportName",
				"type" => "char",
				"length" => 0
			);
		foreach ( $this->query->columns as $col )
		{
			$cols[] = array (
				"name" => $col->query_name,
				"type" => $col->column_type,
				"length" => $col->column_length
				);
		}
		$smarty->assign("COLUMN_ITEMS", $cols);

		$crits = array();
		foreach ( $this->query->lookup_queries as $lq )
		{
			$crits[] = array (
				"name" => $lq->query_name
					);
		}
		$smarty->assign("CRITERIA_ITEMS", $crits);

		header('Content-Type: text/xml');
		if ( $savefile )
		{
			$data = $smarty->fetch('wsdl.tpl', null, null, false );
			$this->write_report_file($savefile, $data);
		}
		else
			$smarty->display('wsdl.tpl');
		
	}
	
	function get_xmldata()
	{
		$text = '<?xml version="'.$this->xml_version.'"?>';
		$text .= $this->xmldata->unserialize();
		return $text;
	}

	function write()
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

	function write_report_file($filename, &$writedata)
	{
		global $g_project;
		$fn = $this->query->reports_path."/".$filename;
		if ( ! ($fd = fopen($fn, "w" )) )
		{
			return false;
		}

		if ( ! fwrite ($fd, $writedata ) )
		{
			return false;
		}

		fclose($fd);

		return(true);

	}

    // Remove report XML from disk
	function remove_file($filename)
	{

		global $g_project;

		
		if ( !$filename )
		{	
			trigger_error ( template_xlate("UNABLE_TO_REMOVE").template_xlate("SPECIFYXML"), E_USER_ERROR );
			return false;
		}

		if ( !preg_match("/\.xml$/", $filename) )
		{	
			$filename = $filename.".xml";
		}

		$projdir = $this->query->projects_folder."/".$g_project;
		if ( !is_file($projdir) )
			find_file_to_include($projdir, $projdir);

		if ( $projdir && is_dir($projdir))
		{
			$fn = $projdir."/".$filename;
            if ( !is_file ( $fn ) )
			    trigger_error ( template_xlate("UNABLE_TO_REMOVE")." $filename  - ".template_xlate("NOFILE"), E_USER_ERROR );
            else if ( !is_writeable ( $fn ) )
			    trigger_error ( template_xlate("UNABLE_TO_REMOVE")." $filename  - ".template_xlate("NOWRITE"), E_USER_ERROR );
            else
            {
                if ( !unlink ( $fn ) )
			        trigger_error ( template_xlate("UNABLE_TO_REMOVE")." $filename  - ".template_xlate("NOWRITE"), E_USER_ERROR );
                else
			        handle_debug ( template_xlate("REPORTFILE")." $filename ".template_xlate("DELETEOKACT"), 0 );
            }
		}
		else
			trigger_error ( "Unable to open project area $g_project to save file $filename ". 
				$this->query->reports_path."/".$filename." Not Found", E_USER_ERROR );
	}

    // Save report XML to disk
	function write_file($filename)
	{

		global $g_project;

		
		if ( !$filename )
		{	
			trigger_error ( template_xlate("UNABLE_TO_SAVE").template_xlate("SPECIFYXML"), E_USER_ERROR );
			return false;
		}

		if ( !preg_match("/\.xml$/", $filename) )
		{	
			$filename = $filename.".xml";
		}

		$projdir = $this->query->projects_folder."/".$g_project;
		if ( !is_file($projdir) )
			find_file_to_include($projdir, $projdir);

		if ( $projdir && is_dir($projdir))
		{
			$fn = $projdir."/".$filename;
			if ( ! ($fd = fopen($fn, "w" )) )
			{
				return false;
			}
		}
		else
			trigger_error ( "Unable to open project area $g_project to save file $filename ". 
				$this->query->reports_path."/".$filename." Not Found", E_USER_ERROR );
		

		if ( ! fwrite ($fd, '<?xml version="'.$this->xml_version.'"?>' ) )
		{
			return false;
		}

		$xmltext = $this->xmldata->unserialize();
		if ( ! fwrite ($fd, $xmltext) )
		{
			return false;
		}

		fclose($fd);

	}

}

/**
 * Class reportico_xmlval
 *
 * Stores the definition of a single tag within an XML report definition
 */
class reportico_xmlval
{
	var $name;
	var $value;
	var $attributes;
	var $ns;
	var $xmltext = "";
	var $elements = array();

	function __construct ( $name, $value = false, $attributes = array() )
	{
		$this->name = $name;
		$this->value = $value;
		$this->attributes = $attributes;
	}

	function &add_xmlval ( $name, $value = false, $attributes = false )
	{
		$element = new reportico_xmlval($name, htmlspecialchars($value), $attributes);
		$this->elements[] =& $element;
		return $element;
	}

	function unserialize ( )
	{
		$this->xmltext = "<";
		$this->xmltext .= $this->name;

		if ( $this->attributes )
		{
			$infor = true;
			foreach  ( $this->attributes as $k => $v )
			{
				if ( $v )
				{
					if ( $infor )
						$this->xmltext .= " ";
					else
						$infor = true;
					$this->xmltext .= $k.'="'.$v.'"';
				}
					
			}
		}

		$this->xmltext .= ">";

		if ( $this->value || $this->value === "0" )
		{
			$this->xmltext .= $this->value;
		}
		else
			foreach ( $this->elements as $el )
			{
				$this->xmltext .= $el->unserialize();
			}

		$this->xmltext .= "</";
		$this->xmltext .= $this->name;
		$this->xmltext .= ">";

		return $this->xmltext;
	}

	function write ( )
	{
		echo "<";
		echo $this->name;

		if ( $this->attributes )
		{
			$infor = true;
			foreach  ( $this->attributes as $k => $v )
			{
				if ( $v )
				{
					if ( $infor )
						echo " ";
					else
						$infor = true;
					echo $k.'="'.$v.'"';
				}
					
			}
		}

		echo ">";

		if ( $this->value )
		{
			echo $this->value;
		}
		else
			foreach ( $this->elements as $el )
				$el->write();

		echo "</";
		echo $this->name;
		echo ">";
	}

}
