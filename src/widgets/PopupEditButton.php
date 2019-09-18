<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 * Widget for rendering Submit buttons in the front-end
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

use Reportico\Engine\ReporticoLang;
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;

class PopupEditButton extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public $buttonType = false;
    public $buttonTypes = array();

    public function __construct($engine, $load = false, $type = false )
    {

        $this->buttonType = $type;

        $this->buttonTypes = [
            "page-setup" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITPAGESETUP",
                "label" => "EDITPAGESETUP",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerform_Page|Margin|Zoom|Paginate",
                "tag" => "button",
                "editicon" => false
            ],
            "edit-title" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITTITLE",
                "label" => "",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerform_ReportTitle",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-sql" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITSQL",
                "label" => "EDITSQL",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerqurysqlt_QuerySql",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-columns" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITCOLUMNS",
                "label" => "EDITCOLUMNS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerquryqcol_ANY",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-assignments" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITASSIGNMENT",
                "label" => "EDITASSIGNMENT",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainquerassg_ANY",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-groups" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITGROUPS",
                "label" => "EDITGROUPS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainqueroutpgrps_ANY",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-charts" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITGRAPHS",
                "label" => "EDITGRAPHS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn btn-default reportico-edit-link",
                "name" => "mainqueroutpgrph_ANY",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-page-headers" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITPAGEHEADERS",
                "label" => "EDITPAGEHEADERS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainqueroutppghd0000form_ANY",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-page-footers" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITPAGEFOOTERS",
                "label" => "EDITPAGEFOOTERS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainqueroutppgft0000form_ANY",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-display-order" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITDISPLAYORDER",
                "label" => "EDITDISPLAYORDER",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainqueroutpdord0000form_ANY",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-pre-sqls" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITPRESQLS",
                "label" => "EDITPRESQLS",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainquerqurypsql_ANY",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-grid" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITGRID",
                "label" => "EDITGRID",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainquerform_grid",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-code" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITCUSTOMSOURCE",
                "label" => "EDITCUSTOMSOURCE",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainquerform_PreExecuteCode",
                "tag" => "link",
                "editicon" => true,
            ],
            "edit-criteria" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITCRITERIA",
                "label" => "EDITCRITERIA",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainquercrit_ANY",
                "tag" => "button",
                "editicon" => true,
            ],
            "edit-description" => [
                "id" => "submit_mainquerform_SHOW",
                "title" => "EDITDESCRIPTION",
                "label" => "EDITDESCRIPTION",
                "triggerTag" => ".reportico-edit-link",
                "calls" => "popupEditLink(this)",
                "classes" => "btn prepareMiniMaintain reportico-edit-link",
                "name" => "mainquerform_ReportDescription",
                "tag" => "button",
                "editicon" => true,
            ],
        ];

        parent::__construct($engine);

        if ( !$type )
            return;

    }

    public function getConfig() {

        if ( !$this->buttonType )
            return;
        $triggerTag = $this->buttonTypes[$this->buttonType]["triggerTag"];
        $triggerTag = "#reportico-popup-".$this->buttonType;
        $calls = $this->buttonTypes[$this->buttonType]["calls"];

        $init = [ ];
        $runtime = [
"
//alert('$triggerTag');
reportico_jquery(document).on('click', '$triggerTag', function() {
    $calls
    return false;
})
"
        ];
        $trigger = [

            ];

        $type = $this->buttonType ? $this->buttonType : "popup-edit-button";
        return
            [
                'name' => $type,
                'order' => 200,
                'files' => [
                    'css' => [ ],
                    'js' => [ ],
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

    public function render()
    {
        $sections = [];

        $classes = $this->buttonTypes[$this->buttonType]["classes"];
        if (!$this->engine->bootstrap_styles || $this->engine->force_reportico_mini_maintains)
            $classes = "btn btn-default prepareMiniMaintain reportico-edit-link";
        else
            $classes = "btn btn-default reportico-edit-link";

        $icon = "";
        if ( $this->buttonTypes[$this->buttonType]["editicon"] ) {
            $icon = "<span class='glyphicon glyphicon-pencil icon-pencil'></span>";
        }

        //$id = $this->buttonTypes[$this->buttonType]["id"];
        $id = "reportico-popup-".$this->buttonType;
        $label = ReporticoLang::templateXlate($this->buttonTypes[$this->buttonType]["label"]);
        $title =
            ReporticoLang::templateXlate("EDIT"). " ".
            ReporticoLang::templateXlate($this->buttonTypes[$this->buttonType]["title"]);
        $name = $this->buttonTypes[$this->buttonType]["name"];
        $this->manager->availableAssets[$this->buttonType] = $this;

        if ( $this->buttonTypes[$this->buttonType]["tag"] == "link" ) {
            if (!$this->engine->bootstrap_styles || $this->engine->force_reportico_mini_maintains)
                $classes = "btn prepareMiniMaintain reportico-edit-link";
            else
                $classes = "btn reportico-edit-link";
            $text = "<li><input type='submit' class='$classes' style='margin-right: 30px' title='$title' id='$id' value='$label' name='$name'></li>";
        } else {
            $text = "<button type='submit' class='$classes' title='$title' id='$id' name='$name'>$icon$label</button>";
        }

        $sections["id"] = $id;
        $sections["icon"] = $icon;
        $sections["label"] = $label;
        $sections["title"] = $title;
        $sections["name"] = $name;
        $sections["classes"] = $classes;
        $sections["widget"] = $text;
        return $sections;
    }
}
// -----------------------------------------------------------------------------
