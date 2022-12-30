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
            "
            reportico_jquery(document).on('click', '#submitDeleteTemplate,#submitSaveTemplate,#submitLoadTemplate', function(event)
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

    //if ( reportico_ajax_mode == 1 )
        //ajaxaction += '?r=reportico/ajax';
    //else
        //ajaxaction += '/reportico/ajax';

    ajaxaction +=  getCSRFURLParams();
    headers =  getCSRFHeaders();

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

    loadSpinner(false, true);
    reportico_jquery(expandpanel).addClass('loading');
    reportico_jquery.ajax({
        type: 'POST',
        url: ajaxaction,
        data: params,
        headers: headers,
        dataType: 'html',
        success: function(data, status)
        {
            loadSpinner(false, false);
            reportico_jquery(expandpanel).removeClass('loading');
            reportico_jquery('#saveTemplate').val(templatename) ;
            //if ( id == 'submitLoadTemplate' )
            {
                reportico_jquery(fillPoint).replaceWith(data);
                setupWidgets();
                setupTooltips();
                setupDropMenu();
                setupCheckboxes();
            }
        },
        error: function(xhr, desc, err) {
            loadSpinner(false, false);
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

                // If templates folder exists then store reports there
                if (is_dir($projpath."/templates") ){
                    $projpath = $projpath."/templates";
                }
                
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

                    // If templates folder exists then store reports there
                    if (is_dir($projpath."/templates") ){
                        $projpath = $projpath."/templates";
                    }
                
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
                    $extra = [];
                    foreach ($params as $k => $v) {
                        if ($k == "r") continue;
                        if (is_array($v)) {
                            $val = implode(",", $v);
                        } else
                            $val = $v;

                        
                        if ( preg_match("/_FROMDATE/", $k) && preg_match("/[0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]/", $val) ) {
                            $val = substr($val, 6, 4)."-".substr($val,3,2)."-".substr($val,0,2);
                        }
                        if ( preg_match("/_TODATE/", $k) && preg_match("/[0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]/", $val) ) {
                            $val = substr($val, 6, 4)."-".substr($val,3,2)."-".substr($val,0,2);
                        }
                        
                        //$paramstring .= "&$k=$val";
                        $field = $k;
                        if ( preg_match("/DIRECT_/", $k )) {
                            $k = preg_replace("/DIRECT_/", "MANUAL_", $k);
                        }
                        if ( preg_match("/MANUAL_/", $k ))  {
                            $_REQUEST["$k"] = $val;
                        }
                        else
                            $_REQUEST["MANUAL_$k"] = $val;
                        if ( preg_match("/DIRECT_/", $field )) {
                            unset($_REQUEST[$field]);
                        }

                    }

                    $this->engine->execute_mode = "PREPARE";
                    $this->engine->setRequestColumns();
                    //echo "AFT <PRE>"; var_dump($_REQUEST); echo "</PRE>";
                    $mode = "PREPARE";

                }

            if ($saveto) {
                \Reportico\Engine\ReporticoSession::setReporticoSessionParam("templatefile", "$saveto");
                $projpath = ReporticoApp::get("projpath");

                // If templates folder exists then store reports there
                if (is_dir($projpath."/templates") ){
                    $projpath = $projpath."/templates";
                }
                
                $templatefolder = $projpath . "/" . $user . "/" . $this->engine->xmloutfile;
                $templatefolder = preg_replace("/\.xml/", "", $templatefolder);
                if (!is_dir($templatefolder)) {
                    if (!mkdir($templatefolder, 0755, true)) {
                        header("HTTP/1.0 404 Not Found", true);
                        echo "<div class=\"reportico-error-box\">Cannot make template folder $templatefolder - check permissions</div>";
                        die;
                    }

                }


                $file = $templatefolder . "/" . $saveto;
                if (!file_put_contents($file, json_encode($_REQUEST))) {
                    header("HTTP/1.0 404 Not Found", true);
                    echo "<div class=\"reportico-error-box\">Cannot save template dile $saveto folder in $templatefolder - check permissions</div>";
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
        // If templates folder exists then store reports there
        if (is_dir($projpath."/templates") ){
            $projpath = $projpath."/templates";
        }
        $templatefolder = $projpath."/".$user."/".$this->engine->xmloutfile;
        $templatefolder = preg_replace("/\.xml/", "", $templatefolder);

        //if ( !is_dir($templatefolder)) {
            //mkdir($templatefolder,0755, true );
        //}

        $this->engine->template_files = [];

        if ( is_dir($templatefolder)) {

            if ( !is_dir($templatefolder)) {
                echo "Cannot create $templatefolder - check permissions";
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

        $sections["label"] = "Templates";
        $sections["file"] = $templateFile;
        $sections["save-template"] = "
        <div class='input-group' >
            <label class='form-control' aria-label='Text input with checkbox'>Templates:</label>
          <div class='input-group-prepend'>
                <input type='submit' class='btn btn-outline-secondary'  name='submitSaveTemplate' id='submitSaveTemplate' value='Save'>
            </div>
            <input type='text' id='saveTemplate' value='$templateFile' >
        </div> ";

        $sections["load-template"] = "
        <div class='input-group' >
          <div class='input-group-prepend'>
            <input type='submit' class='btn btn-outline-secondary'  name='submitLoadTemplate' id='submitLoadTemplate' value='Load'>
            </div>
            <span class='input-group-addon' style='padding: 0px 5px;'>
                <SELECT id='loadTemplate' class='form-control' style='padding: 0px; height: auto;width: auto; height: inherit' name='template_selection'>";


        $sections["load-options"] = "";
        foreach ( $this->identifyTemplates() as $template ) {
            $sections["load-template"] .= "<OPTION label='$template' value='$template'>$template</OPTION>";
            $sections["load-options"] .= "<OPTION label='$template' value='$template'>$template</OPTION>";
        }

        $sections["load-template"] .= "
                </SELECT>
            </span>
            <input type='submit' class='form-control btn btn-danger'  name='submitDeleteTemplate' id='submitDeleteTemplate' value='X'>
        <!--/div--><!-- /input-group -->
    </div>";

        return $sections;

    }
}
// -----------------------------------------------------------------------------
