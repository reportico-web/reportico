<?php
namespace Reportico\Engine;

/*


 * File:        DesignPanel.php
 *
 * This module provides functionality for reading and writing
 * xml reporting.
 * It also controls browser output through Twig templating class
 * for the different report modes MENU, PREPARE, DESIGN and
 * EXECUTE
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2018 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: swpanel.php,v 1.40 2014/05/17 15:12:32 peter Exp $
 */

/* $Id $ */

/**
 * Class DesignPanel
 *
 * Class for storing the hierarchy of content that will be
 * displayed through the browser when running Reportico
 *
 */
class DesignPanel
{
    public $panel_type;
    public $query = null;
    public $visible = false;
    public $pre_text = "";
    public $body_text = "";
    public $post_text = "";
    public $full_text = "";
    public $program = "";
    public $panels = array();
    public $template = false;
    public $reportlink_report = false;
    public $reportlink_report_item = false;

    public function __construct(&$in_query, $in_type)
    {
        $this->query = &$in_query;
        $this->panel_type = $in_type;
    }

    public function setTemplate(&$in_template)
    {
        $this->template = &$in_template;
    }

    public function setMenuItem($in_program, $in_text)
    {
        // Dont specify xml extensions in menu options
        $in_program = preg_replace("/\.xml\$/", "", $in_program);

        $this->program = $in_program;
        $this->text = $in_text;

        $cp = new DesignPanel($this->query, "MENUITEM");
        $cp->visible = true;
        $this->panels[] = &$cp;
        $cp->program = $in_program;
        $cp->text = $in_text;

    }

    public function setProjectItem($in_program, $in_text)
    {
        $this->program = $in_program;
        $this->text = $in_text;

        $cp = new DesignPanel($this->query, "PROJECTITEM");
        $cp->visible = true;
        $this->panels[] = &$cp;
        $cp->program = $in_program;
        $cp->text = $in_text;

    }

    public function setVisibility($in_visibility)
    {
        $this->visible = $in_visibility;
    }

}
