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
 * Class plugin 
 *
 * Class for storing the hierarchy of content that will be
 * displayed through the browser when running Reportico
 * 
 */
$this->plugins["styles"] = 
    array(
        "design-options"  => array(
                "function" => 
                function ($params)
                {
                    $params["field_display"] = array_merge ( $params["field_display"],
                    array (
					"GroupHeaderStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupHeaderStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupHeaderStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true ),
					"GroupHeaderStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE" ),
					"GroupHeaderStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupHeaderStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE" ),
					"GroupHeaderStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE" ),
					"GroupHeaderStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE" ),
					"GroupHeaderStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true ),
					"GroupHeaderStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE" ),
					"GroupHeaderStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE" ),
					"GroupHeaderStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true ),
					"GroupHeaderStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE" ),

					"GroupTrailerStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupTrailerStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupTrailerStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true ),
					"GroupTrailerStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE" ),
					"GroupTrailerStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR" ),
					"GroupTrailerStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE" ),
					"GroupTrailerStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE" ),
					"GroupTrailerStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE" ),
					"GroupTrailerStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true ),
					"GroupTrailerStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE" ),
					"GroupTrailerStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE" ),
					"GroupTrailerStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true ),
					"GroupTrailerStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE" ),

					"PageHeaderStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageHeaderStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageHeaderStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true ),
					"PageHeaderStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE" ),
					"PageHeaderStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageHeaderStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE" ),
					"PageHeaderStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE" ),
					"PageHeaderStyleFontName" => array ( "Title" => "ASSIGNFONTNAME", "Type" => "FONTLIST" ),
					"PageHeaderStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE" ),
					"PageHeaderStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true ),
					"PageHeaderStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE" ),
					"PageHeaderStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE" ),
					"PageHeaderStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true ),
					"PageHeaderStyleBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE" ),

					"PageFooterStyleFgColor" => array ( "Title" => "ASSIGNSTYLEFGCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageFooterStyleBgColor" => array ( "Title" => "ASSIGNSTYLEBGCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageFooterStyleBorderStyle" => array ( "Title" => "ASSIGNSTYLEBORDERSTYLE", "Type" => "BORDERSTYLES", "XlateOptions" => true ),
					"PageFooterStyleBorderSize" => array ( "Title" => "ASSIGNSTYLEBORDERSIZE", "Validate" => "CSS4SIZE" ),
					"PageFooterStyleBorderColor" => array ( "Title" => "ASSIGNSTYLEBORDERCOLOR", "Validate" => "HTMLCOLOR" ),
					"PageFooterStyleMargin" => array ( "Title" => "ASSIGNSTYLEMARGIN", "Validate" => "CSS4SIZE" ),
					"PageFooterStylePadding" => array ( "Title" => "ASSIGNSTYLEPADDING", "Validate" => "CSS4SIZE" ),
					"PageFooterStyleFontSize" => array ( "Title" => "ASSIGNFONTSIZE", "Validate" => "CSSFONTSIZE" ),
					"PageFooterStyleFontStyle" => array ( "Title" => "ASSIGNFONTSTYLE", "Type" => "FONTSTYLES", "XlateOptions" => true ),
					"PageFooterStyleHeight" => array ( "Title" => "ASSIGNHEIGHT", "Validate" => "CSS1SIZE" ),
					"PageFooterStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE" ),
					"PageFooterStylePosition" => array ( "Title" => "ASSIGNSTYLEPOSITION", "Type" => "POSITIONS", "XlateOptions" => true ),
					"PageFooterBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE" ),

					"DetailStyle" => array ( "Title" => "DETAILSTYLE" ),
                    )
                );

                return true;
            }
        ),
        "draw-section"  => array(
            "function" => 
                function (&$params)
                {
                    $parent = &$params["parent"];
                    $in_parent = $params["parent_id"];
                    $type = &$params["type"];
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
    
                    //$tagct = 1;
                    //$blocktype = "assignTypeDbg";
                    //$text .= '<TR><TD class="swMntSetField"><a class="swToggle" id="'.$blocktype.'" href="javascript:toggleLine(\''.$blocktype.'\')">+</a><b>'.template_xlate("DATABASEGRAPHICWIZARD").'</b></TD></TR>';
                    //$text .= $parent->display_maintain_field("AssignGraphicBlobCol", false, $tagct, true, false, $blocktype); $tagct++;
                    //$text .= $parent->display_maintain_field("AssignGraphicBlobTab", false, $tagct, true, false, $blocktype); $tagct++;
                    //$text .= $parent->display_maintain_field("AssignGraphicBlobMatch", false, $tagct, true, false, $blocktype); $tagct++;
                    //$text .= $parent->display_maintain_field("AssignGraphicWidth", false, $tagct, true, false, $blocktype); $tagct++;
                    //$text .= $parent->display_maintain_field("AssignGraphicReportCol", false, $tagct, true, false, $blocktype); $tagct++;
    
                    $parent->id = $tmpid;
    
                    return $text;
                }
        ),
        "apply-section"  => array(
            "function" => 
                function (&$params)
                {
                    $type = $params["type"];
                    $updates = &$params["updates"];
                    $applyto = &$params["applyto"];
            
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
        ),
    );

?>
