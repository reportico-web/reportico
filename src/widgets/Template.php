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
use Reportico\Engine\ReporticoUtility;
use Reportico\Engine\ReporticoLang;

class Template extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public function __construct($engine)
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $init = [

];
        $runtime = [
            "reportico_jquery(document).on('click', '#submitDeleteTemplate,#submitSaveTemplate,#submitLoadTemplate', function(event)
{
    id = reportico_jquery(this).prop('id');
    var expandpanel = reportico_jquery(this).closest('#criteriaform').find('#swPrpExpandCell');
    var reportico_container = reportico_jquery(this).closest('#reportico-container');

    forms = reportico_jquery(this).closest('#reportico-container').find(\"#reportico-form\");
    if ( reportico_jquery.type(reportico_ajax_script) === 'undefined' )
    {

        var ajaxaction = reportico_jquery(forms).prop('action');
    }
    else
    {
        ajaxaction = reportico_ajax_script;
    }

    if ( reportico_ajax_mode == 1 )
        ajaxaction += '?r=reporticopdf/reportico/ajax';
    else
        ajaxaction += '/reporticopdf/reportico/ajax';

    params = forms.serialize();

    params += '&execute_mode=PREPARE';
    params += '&reportico_ajax_called=1';
    if ( id == 'submitSaveTemplate' )
    {
        params += '&templateAction=Save';
        params += '&saveTemplate=' + reportico_jquery('#saveTemplate').val();
        var templatename = reportico_jquery('#saveTemplate').val();
    }

    if ( id == 'submitDeleteTemplate' )
    {
        params += '&templateAction=Delete';
        params += '&loadTemplate=' + reportico_jquery('#loadTemplate').val();
        var templatename = reportico_jquery('#loadTemplate').val();
    }

    if ( id == 'submitLoadTemplate' )
    {
        params += '&templateAction=Load';
        params += '&loadTemplate=' + reportico_jquery('#loadTemplate').val();
        //params += '&partial_template=critbody';
        var templatename = reportico_jquery('#loadTemplate').val();
    }

    fillPoint = reportico_jquery(this).closest('#reportico-container');

    reportico_jquery(expandpanel).addClass('loading');
    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        dataType: 'html',
        success: function(data, status)
        {
            reportico_jquery(expandpanel).removeClass('loading');
            reportico_jquery('#saveTemplate').val(templatename) ;
            //if ( id == 'submitLoadTemplate' )
            {
                reportico_jquery(fillPoint).html(data);
                setupWidgets();
                setupTooltips();
                setupDropMenu();
                setupCheckboxes();
            }
        },
        error: function(xhr, desc, err) {
            reportico_jquery(expandpanel).removeClass('loading');
            reportico_jquery(reportico_container).removeClass('loading');
            try {
                // a try/catch is recommended as the error handler
                // could occur in many events and there might not be
                // a JSON response from the server
                var errstatus = reportico_jquery.parseJSON(xhr.responseText);
                var msg = errstatus.errmsg;
                //reportico_jquery(expandpanel).prop('innerHTML', msg);
                showNoticeModal(msg);
        
            } catch(e) {
                 showNoticeModal(xhr.responseText);
            }
        }
    });

    return false;
})
"
];

        return
            [
                'name' => 'template',
                'order' => 300,
                'files' => [
                    'events' => [
                        'init' => $init,
                        'runtime' => $runtime
                    ]
                ]
            ];
    }

    public function onSubmit()
    {
        $user = $this->engine->external_user;
        if ( !$user )
            $user = "public";

        if ( $this->engine->execute_mode == "PREPARE" ) {

            ReporticoLang::loadModeLanguagePack("languages", $this->engine->output_charset);

            //$this->engine->initialize_panels($mode);
            $this->engine->handleXmlQueryInput($this->engine->execute_mode, false);
            $this->engine->setRequestColumns();

            $saveto = ReporticoUtility::getRequestItem("saveTemplate", false);
            $loadFrom = ReporticoUtility::getRequestItem("loadTemplate", false);
            $templateAction = ReporticoUtility::getRequestItem("templateAction", false);

            if ($templateAction == "Save" && !$saveto) {
                header("HTTP/1.0 500 Not Found", true);
                echo "Please specify a template name to save to";
                die;
            }

            global $g_projpath;
            if ($loadFrom && $templateAction == "Delete") {
                \Reportico\Engine\ReporticoSession::setReporticoSessionParam("templatefile", "$loadFrom");
                $projpath = ReporticoApp::get("projpath");
                $templatefolder = $projpath . "/" . $user . "/" . $this->engine->xmloutfile;
                $templatefolder = preg_replace("/\.xml/", "", $templatefolder);
                if (!is_dir($templatefolder)) {
                    echo "{ Error: false, Message: \"Folder doesnt exist $templatefolder\" }";
                    die;
                }

                $file = $templatefolder . "/" . $loadFrom;
                if (!is_file($file) && !is_writeable($file)) {
                    echo "{ Error: false, Message: \"File not writeable $file\" }";
                    die;
                }

                unlink($file);

            }
            else
                if ($loadFrom) {
                    \Reportico\Engine\ReporticoSession::setReporticoSessionParam("templatefile", "$loadFrom");
                    $projpath = ReporticoApp::get("projpath");
                    $templatefolder = $projpath . "/" . $user . "/" . $this->engine->xmloutfile;
                    $templatefolder = preg_replace("/\.xml/", "", $templatefolder);
                    if (!is_dir($templatefolder)) {
                        echo "{ Error: false, Message: \"Folder doesnt exist $templatefolder\" }";
                        die;
                    }

                    $file = $templatefolder . "/" . $loadFrom;
                    if (!($json = file_get_contents($file))) {
                        echo "{ Error: false, Message: \"Can't open template file $loadFrom in $templatefolder\" }";
                        die;
                    }

                    $params = json_decode($json, true);
                    $paramstring = "";
                    foreach ($params as $k => $v) {
                        if ($k == "r") continue;
                        if (is_array($v)) {
                            $val = implode(",", $v);
                        } else
                            $val = $v;

                        $paramstring .= "&$k=$val";
                        $_REQUEST["$k"] = $val;
                        $_REQUEST["MANUAL_$k"] = $val;
                    }

                    $this->engine->execute_mode = "PREPARE";
                    $this->engine->setRequestColumns();
                    $mode = "PREPARE";

                }

            if ($saveto) {
                \Reportico\Engine\ReporticoSession::setReporticoSessionParam("templatefile", "$saveto");
                $projpath = ReporticoApp::get("projpath");
                $templatefolder = $projpath . "/" . $user . "/" . $this->engine->xmloutfile;
                $templatefolder = preg_replace("/\.xml/", "", $templatefolder);
                if (!is_dir($templatefolder)) {
                    if (!mkdir($templatefolder, 0755, true)) {
                        echo "{ Error: false, Message: \"Can't make template folder $templatefolder\" }";
                        die;
                    }

                }


                $file = $templatefolder . "/" . $saveto;
                if (!file_put_contents($file, json_encode($_REQUEST))) {
                    echo "{ Error: false, Message: \"Can't save template file $save to in $templatefolder\" }";
                    die;
                }
                //die;
            }
        }
    }

    private function identifyTemplates()
    {
        $user = $this->engine->external_user;
        if ( !$user )
            $user = "public";

        $projpath = ReporticoApp::get("projpath");
        $templatefolder = $projpath."/".$user."/".$this->engine->xmloutfile;
        $templatefolder = preg_replace("/\.xml/", "", $templatefolder);
        if ( !is_dir($templatefolder)) {
            mkdir($templatefolder,0755, true );
        }
        if ( !is_dir($templatefolder)) {
            echo "Cannot create $templatefolder";
            die;
        }
        $this->engine->template_files = array();


        if ( is_dir($templatefolder) )
        {
            if ($dh = opendir($templatefolder))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if ( substr($file, 0,1) == "." )
                        continue;
                    if ( is_file ( $templatefolder."/".$file ) )
                    {
                        $this->engine->template_files[] = $file;
                    }
                }
                closedir($dh);
            }
        }

        return $this->engine->template_files;
    }

    public function handlePost()
    {

    }

    public function render()
    {
        $templateFile = \Reportico\Engine\ReporticoSession::getReporticoSessionParam("templatefile", false);

        $sections = [];

        $sections["save-template"] = "
        <div style='max-width: auto; padding: 5px; float: right;vertical-align: bottom;text-align: right'>
        <label style='min-width: auto;border: none;margin-bottom: 0px; margin-left: 10px; width:100px; float: left' class='form-control' aria-label='Text input with checkbox'>Templates:</label>
        <div class='input-group' style='margin-bottom: 0px; margin-left: 10px; width:150px; float: left'>
            <input type='button' class='form-control'  name='submitSaveTemplate' id='submitSaveTemplate' value='Save'>
            <span class='input-group-addon' style='padding: 0px 5px;'>
                <input type='text' id='saveTemplate' value='$templateFile' >
            </span>
        </div><!-- /input-group -->";

        $sections["load-template"] = "
        <div class='input-group' style='margin-bottom: 0px; margin-left: 10px; width:150px; float: left'>
            <input type='submit' class='form-control'  name='submitLoadTemplate' id='submitLoadTemplate' value='Load'>
            <span class='input-group-addon' style='padding: 0px 5px;'>
                <SELECT id='loadTemplate' class='form-control' style='padding: 0px; height: auto;width: auto; height: inherit' name='template_selection'>";


        foreach ( $this->identifyTemplates() as $template ) {
            $sections["load-template"] .= "<OPTION label='$template' value='$template'>$template</OPTION>";
            
        }

        $sections["load-template"] .= "
                </SELECT>
            </span>
            <input type='submit' class='form-control btn btn-danger'  name='submitDeleteTemplate' id='submitDeleteTemplate' value='X'>
        </div><!-- /input-group -->
    </div>";

        return $sections;

    }
}
// -----------------------------------------------------------------------------
