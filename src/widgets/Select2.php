<?php

namespace Reportico\Widgets;

/*

 * Core
 *
 e Widget representing the Reportico instance
 * Serves up core Reportico css and js files
 *
 * @link http://www.reportico.co.uk/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @version $Id: reportico.php,v 1.68 2014/05/17 15:12:31 peter Exp $
 */
use \Reportico\Engine\ReporticoLocale;
use \Reportico\Engine\ReporticoApp;

class Select2 extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public function __construct($engine)
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $datepickerLanguage = \Reportico\Engine\ReporticoLocale::getDatepickerLanguage(ReporticoApp::getConfig("language"));
        $datepickerFormat = \Reportico\Engine\ReporticoLocale::getDatepickerFormat(ReporticoApp::getConfig("prep_dateformat"));

        $init = [
            "
    if (typeof reportico_criteria_items === 'undefined') 
        return; 

    for ( i in reportico_criteria_items )
    {
        j = reportico_criteria_items[i];

        // Already checked values for prepopulation
        preselected =[];
        
        jtag = j.replace(/ /g, '\\\\ ');

        reportico_jquery('#select2_dropdown_' + jtag + ',#select2_dropdown_expanded_' + jtag).find('option').each(function() {
            lab = reportico_jquery(this).prop('label');
            value = reportico_jquery(this).prop('value');
            checked = reportico_jquery(this).attr('checked');
            if ( checked )
            {
                preselected.push(value);
            }
        });

headers =  getCSRFHeaders();

if ( jQuery.type(reportico_ajax_script) === 'undefined' || !reportico_ajax_script )
{
var ajaxaction = reportico_jquery(forms).prop('action');
}
else
{
    ajaxaction = reportico_ajax_script;
}

ajaxextra = getYiiAjaxURL();
if ( ajaxextra != '' ) {
    ajaxaction += ajaxextra
            ajaxaction += '&' + 'reportico_criteria=' + j;
        }
else
    ajaxaction += '?' + 'reportico_criteria=' + j;

ajaxaction +=  getCSRFURLParams();
headers =  getCSRFHeaders();

reportico_jquery('#select2_dropdown_' + jtag + ',#select2_dropdown_expanded_' + jtag).select2({
          ajax: {
    url: ajaxaction,
            headers: headers,
            type: 'POST',
            error: function(data, status) {
        return {
            results: [{ id: 'error', text: 'Unable to autocomplete', disabled: true }]
                }
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
        forms = reportico_jquery('#reportico-container').find('.reportico-prepare-form');
        formparams = forms.serialize();
        formparams += '&reportico_ajax_called=1';
        formparams += '&execute_mode=CRITERIA';
        formparams += '&reportico_criteria_match=' + params.term;;
        return formparams;
        return {
            q: params.term, // search term
                formparams: formparams,
                page: params.page
              };
            },
            processResults: function (data, params) {
        // parse the results into the format expected by Select2
        // since we are using custom formatting functions we do not need to
        // alter the remote JSON data, except to indicate that infinite
        // scrolling can be used

        params.page = params.page || 1;

        return {
            results: data.items,
                pagination: {
                more: (params.page * 30) < data.total_count
                }
              };
            },
            cache: false,
            placeholder: 'hello',
            allowClear: true
          },
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 1
          //templateResult: select2FormatResult, // omitted for brevity, see the source of this page
          //templateSelection: select2FormatSelection // omitted for brevity, see the source of this page
        })
        reportico_jquery('#select2_dropdown_' + jtag).val(preselected).trigger('change');

        // If select2 exists in expand tab then hide the search box .. its not relevant
        reportico_jquery('#select2_dropdown_expanded_' + jtag).each(function() {
            reportico_jquery('#expandsearch').hide();
            reportico_jquery('#reporticoSearchExpand').hide();
        });
    };
            
" ];
        $runtime = [  ];

        if ( $this->engine->reportico_ajax_preloaded ) {
            return
                [
                    'name' => 'select2',
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

        return
            [
                'name' => 'select2',
                'order' => 200,
                'files' => [
                    'css' => [
                        'node_modules/select2/dist/css/select2.min.css',
                    ],
                    'js' => [
                        'node_modules/select2/dist/js/select2.min.js',
                    ],
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

}
// -----------------------------------------------------------------------------
