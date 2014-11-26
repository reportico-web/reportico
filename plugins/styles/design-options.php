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
    echo "load new field display";
    $this->field_display = array_merge ( $this->field_display,
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
					"PageFooterStyleWidth" => array ( "Title" => "ASSIGNWIDTH", "Validate" => "CSS1SIZE" ),
					"PageFooterBackgroundImage" => array ( "Title" => "ASSIGNPDFBACKGROUNDIMAGE" ),
                    )
                )
            );
        }
    )

?>
