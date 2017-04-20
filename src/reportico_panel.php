<?php
namespace Reportico;
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

                        
				        $openfilters = preg_replace("/ /", "_", get_reportico_session_param("openfilters"));
				        $closedfilters = get_reportico_session_param("closedfilters");
                        //echo "Look for $critdisplaygroup!!!!!!!!!!!!!!!!!! in $openfilters<BR>";
                        //var_dump($openfilters);
                        //var_dump($closedfilters);
                        //echo "!!!!!!!!!!!!!!!!!!1Filters<BR>";
                        $visible = false;
                        if ( $openfilters ) {
                            if ( in_array(preg_replace("/ /", "_", $critdisplaygroup),$openfilters) )
                            {
                                $visible = true;
                            }
                        }

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
									"visible" => $visible,
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

