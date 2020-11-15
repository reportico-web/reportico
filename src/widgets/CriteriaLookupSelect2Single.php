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
use \Reportico\Engine\ReporticoLang;
use Reportico\Engine\ReporticoUtility;

class CriteriaLookupSelect2Single extends CriteriaLookup
{
    public $value = false;
    public $expanded = false;

    public function __construct($engine, $criteria = false, $expanded = false)
    {
        $this->criteria = $criteria;
        $this->expanded = $expanded;

        parent::__construct($engine, $criteria, $expanded);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [
            "
CriteriaLookupSelect2Single = function() {
        
            var load = function (j) {

        // Already checked values for prepopulation
        preselected =[];

        jtag = j.replace(/ /g, '\\\\ ');

        reportico_jquery('#select2_dropdown_' + jtag + ',#select2_dropdown_expanded_' + jtag).find('option').each(function() {
            lab = reportico_jquery(this).prop('label');
            value = reportico_jquery(this).prop('value');
            checked = reportico_jquery(this).prop('selected');
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
          width: '100%',
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
    }
    return {
        load: function (string) {
            return load(string);
        }
    }
    }();

" ];
        return
            [
                'name' => 'criteria-lookup-select2-single',
                'type' => 'criteria-selection',
                'title' => 'Select2 Single Lookup',
                'renderType' => 'SELECT2SINGLE',
                'sourceType' => 'LOOKUP',
                'order' => 200,
                'files' => [
                    'css' => [
                        "{$this->engine->url_path_to_assets}/node_modules/select2/css/select2.min.css",
                    ],
                    'js' => [
                        "{$this->engine->url_path_to_assets}/node_modules/select2/js/select2.min.js",
                    ],
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

    public function getRenderConfig() {

        $init = [
            "\nCriteriaLookupSelect2Single.load('{$this->criteria->query_name}')\n"
            ];
        $runtime = [ ];

        return
            [
                'name' => 'criteria-lookup-select2-single-'.$this->criteria->query_name,
                'type' => 'criteria-selection',
                'title' => 'Select2 Single Lookup',
                'renderType' => 'SELECT2SINGLE',
                'sourceType' => 'LOOKUP',
                'order' => 200,
                'files' => [
                    'css' => [],
                    'js' => [],
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

    public function renderWidgetStart()
    {
        $res = &$this->criteria->list_values;
        $k = key($res);
        $multisize = 4;
        if ($res && count($res[$k]) > 4) {
            $multisize = count($res[$k]);
        }

        if (isset($res[$k])) {
            if (count($res[$k]) >= 10) {
                $multisize = 10;
            }
        }

        $tag = "select2_dropdown_" . $this->criteria->query_name;
        $name = $this->criteria->query_name;
        if ( $this->expanded) {
            $tag = "select2_dropdown_expanded_" . $this->criteria->query_name;
            $name = "EXPANDED_". $name;
        }

        $name = $this->expanded ? "EXPANDED_" . $this->criteria->query_name : "DIRECT_". $this->criteria->query_name;
        $text = '<SELECT id="'.$tag.'" class="' . $this->criteria->parent_reportico->getBootstrapStyle('design_dropdown') . 'reportico-prepare-drop-select2" name="' . $name .  '[]" size="' . $multisize . '" multiple >';
        $text .= '<OPTION></OPTION>';
        return $text;
    }

    public function renderWidgetItem($label, $value, $selected )
    {

        $criteriaName = preg_replace("/ /", " ", $this->criteria->query_name);

        $selectedFlag = $selected ? "selected" : "";
        $name = $this->expanded ? "EXPANDED_" . $criteriaName : $criteriaName;
        return '<OPTION label="' . $label . '" value="' . $value . '" ' . $selectedFlag . '>' . $label . '</OPTION>';
    }


    public function renderWidgetEnd()
    {
        $criteriaName = preg_replace("/ /", " ", $this->criteria->query_name);
        $text =  "</SELECT>";
        return $text;
    }

}
// -----------------------------------------------------------------------------
