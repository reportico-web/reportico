<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 * Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */

use Reportico\Engine\Reportico;
use Reportico\Engine\ReporticoUtility;

class Modal extends Widget
{
    public $rawvalue = false;

    public $enabled = false;

    public function __construct($engine, $load = true)
    {
        $this->options = [
            "bootstrapModal" => false,
        ];

        parent::__construct($engine, $load);
    }

    public function getConfig() {

        $bootstrapModal = "true";
        if (!$this->engine->bootstrap_styles || $this->engine->force_reportico_mini_maintains)
            $bootstrapModal = "false";

        return
            [
                'name' => 'modal',
                'group' => 'core',
                'order' => 200,
                'files' => [
                    'css' => [
                        "{$this->engine->url_path_to_assets}/node_modules/datatables/css/jquery.dataTables.min.css",
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/node_modules/datatables/js/jquery.dataTables.min.js",
                    ],
                    'events' => [
                        'runtime' =>
                            [
                                "
//alert('set runtime'); 
reportico_bootstrap_modal = $bootstrapModal;
"
                            ],
                        'init' =>
                            [
"
        if ( reportico_dynamic_grids )
        {
            reportico_jquery('.reportico-page').each(function(){
                reportico_jquery(this).dataTable(
                {
                'retrieve' : true,
                'searching' : reportico_dynamic_grids_searchable,
                'ordering' : reportico_dynamic_grids_sortable,
                'paging' : reportico_dynamic_grids_paging,
                'iDisplayLength': reportico_dynamic_grids_page_size
                }
                );
        });
        }
"
                    ]
                    ]
                ],
            ];
    }

    public function render()
    {
        $notice = \Reportico\Engine\ReporticoLang::templateXlate("NOTICE");

if (!$this->engine->bootstrap_styles || $this->engine->force_reportico_mini_maintains)

    $test =
'    
<div id="reporticoModal" tabindex="-1" class="reportico-modal">
    <div class="reportico-modal-dialog">
        <div class="reportico-modal-content">
            <div class="reportico-modal-header">
            <button type="button" class="reportico-modal-close">&times;</button>
            <h4 class="reportico-modal-title" id="reporticoModalLabel">$notice</h4>
            </div>
            <div class="reportico-modal-body" style="padding: 0px" id="reporticoModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="reportico-modal-footer">
                <!--button type="button" class="btn btn-default" data-dismiss="modal">Close</button-->
                <button type="button" class="reportico-edit-linkSubmit" >Close</button>
        </div>
    </div>
  </div>
</div>
<div id="reporticoNoticeModal" tabindex="-1" class="reportico-notice-modal">
    <div class="reportico-notice-modal-dialog">
        <div class="reportico-notice-modal-content">
            <div class="reportico-notice-modal-header">
            <button type="button" class="reportico-notice-modal-close">&times;</button>
            <h4 class="reportico-notice-modal-title" id="reporticoNoticeModalLabel">Set Parameter</h4>
            </div>
            <div class="reportico-notice-modal-body" id="reporticoNoticeModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="reportico-notice-modal-footer">
                <!--button type="button" class="btn btn-default" data-dismiss="modal">Close</button-->
                <button type="button" class="reportico-notice-modal-button" >Close</button>
        </div>
    </div>
  </div>
</div>
';
else
    if ($this->engine->bootstrap_styles == "joomla" ) {
$text = '
<style type="text/css">
    #reporticoModal .modal-dialog .modal-content
    {
        width:900px; margin-left:-150px;
    }
</style>
';
    }
else {
        $text =
'
<div class="modal fade" id="reporticoModal" tabindex="-1" role="dialog" aria-labelledby="reporticoModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close reportico-bootstrap-modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title reportico-modal-title" id="reporticoModalLabel">Set Parameter</h4>
            </div>
            <div class="modal-body" style="padding: 0px; overflow-y: auto" id="reporticoModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary reportico-edit-linkSubmit" >Close</button>
        </div>
    </div>
  </div>
</div>
<a id="a_reporticoNoticeModal" href="#reporticoNoticeModal" role="button" class="btn" data-target="#reporticoNoticeModal" data-toggle="modal" style="display:none">B2</a>
<div class="modal fade" id="reporticoNoticeModal" tabindex="-1" role="dialog" aria-labelledby="reporticoNoticeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close" aria-hidden="true">&times;</button>
            <h4 class="modal-title reportico-notice-modal-title" id="reporticoNoticeModalLabel">'.$notice.'</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; padding: 0px" id="reporticoNoticeModalBody">
                <h3>Modal Body</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>';

}


        return $text;

    }
}
// -----------------------------------------------------------------------------
