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
use \Reportico\Engine\ReporticoUtility;

class AdminPasswordSet extends Widget
{
    public $rawvalue = false;
    public $value = false;

    public function __construct($engine, $load = false )
    {
        parent::__construct($engine);
    }

    public function getConfig() {

        $init = [ ];
        $runtime = [];
        $trigger = [];

        return
            [
                'name' => 'admin-password-set',
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

        // New admin password
        $status_message = false;
        if (array_key_exists("submit_admin_password", $_REQUEST)) {
            $status_message = $this->saveAdminPassword($_REQUEST["new_admin_password"], $_REQUEST["new_admin_password2"], $_REQUEST["jump_to_language"]);
        }

        $text = " <div style='text-align:center;'>";
        if ( $status_message )
            $text .= "<div style='color: #ff0000;'>$status_message</div>";

        $text .= "<br><br>";
        $text .= ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_INFO");
        $text .= "<br>";
        //$text .= ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_NOT_SET");
        $text .= "<br>";
        $text .= ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_PROMPT");
        $text .= "<br>";
		$text .= "<input type='password' name='new_admin_password' value=''><br>";
		$text .= "<br>";
        $text .= ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_REENTER");
		$text .= "<br>";
        $text .= "<input type='password' name='new_admin_password2' value=''>";
		$text .= "<br><br>";
        $text .= $this->engine->renderWidget("language-selector", "LanguageSelector");
		$text .= "<br><br>";
        $text .= ($this->engine->renderWidget("set-admin-password-button", "SubmitExecute"))["widget"];

        $sections = [];
        $sections["widget"] = $text;
        $sections["set-admin-password-status-message"] = $status_message;
        $sections["set-admin-password-info"] = ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_INFO");
        $sections["set-admin-password-prompt"] = ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_PROMPT");
        $sections["set-admin-password-reenter"] = ReporticoLang::templateXlate("SET_ADMIN_PASSWORD_REENTER");
        $sections["set-language-selector"] = $this->engine->renderWidget("language-selector", "LanguageSelector");
        $sections["set-admin-password-button"] = $this->engine->renderWidget("set-admin-password-button", "SubmitExecute");


        return $sections;
    }

    /**
     * Function save_admin_password
     *
     * Writes new admin password to the admin project config.php. If the projects area is in a different location
     * than the admin area, then place the config.php in the projects area
     */
    public function saveAdminPassword($password1, $password2, $language)
    {
        $sessionClass =\Reportico\Engine\ReporticoSession();

        if ($language) {
            ReporticoApp::setConfig("language", $language);
        }

        if ($password1 != $password2) {
            return ReporticoLang::translate("The passwords are not identical please reenter");
        }

        if (strlen($password1) == 0) {
            return ReporticoLang::translate("The password may not be blank");
        }

        $source_parent = ReporticoUtility::findBestLocationInIncludePath($this->engine->admin_projects_folder);
        $source_dir = $source_parent . "/admin";
        $source_conf = $source_dir . "/config.php";
        $source_template = $source_dir . "/adminconfig.template";

        $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler", 0);
        if (!@file_exists($source_parent)) {
            $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
            return "Projects area $source_parent does not exist - cannot write project";
        }

        $target_parent = $source_parent;
        $target_dir = $source_dir;
        $target_conf = $source_conf;

        // If projects area different to source admin, create admin project in projects folder to store config.php
        if ($this->engine->admin_projects_folder != $this->engine->projects_folder) {
            $target_parent = $this->engine->admin_projects_folder;
            $target_dir = $target_parent . "/admin";
            $target_conf = $target_dir . "/config.php";
        }

        if (!@is_dir($target_dir)) {
            @mkdir($target_dir, 0755, true);
            if (!is_dir($target_dir)) {
                $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                return "Could not create admin config folder $target_conf - check permissions and continue";
            }
        }

        if (@file_exists($target_conf)) {
            if (!is_writeable($target_conf)) {
                $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                return "Admin config file $target_conf is not writeable - cannot write config file - change permissions to continue";
            }
        }

        if (!is_writeable($target_dir)) {
            $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
            return "Projects area $target_dir is not writeable - cannot write project password in config.php - change permissions to continue";
        }

        if (!@file_exists($source_conf)) {
            if (!@file_exists($source_template)) {
                $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");
                return "Projects config template file $source_template does not exist - please contact reportico.org";
            }
        }

        $old_error_handler = set_error_handler("Reportico\Engine\ReporticoApp::ErrorHandler");

        if (@file_exists($target_conf)) {
            $txt = file_get_contents($target_conf);
        } else {
            $txt = file_get_contents($source_template);
        }

        $proj_language = ReporticoUtility::findBestLocationInIncludePath("language");
        $lang_dir = $proj_language . "/" . $language;
        if (!is_dir($lang_dir)) {
            return "Language directory $language does not exist within the language folder";
        }

        $txt = preg_replace("/(define.*?SW_ADMIN_PASSWORD',).*\);/", "$1'$password1');", $txt);
        $txt = preg_replace("/(ReporticoApp::setConfig\(.admin_password.,).*/", "$1'$password1');", $txt);
        $txt = preg_replace("/(ReporticoApp::setConfig\(.language.,).*/", "$1'$language');", $txt);

        $sessionClass::unsetReporticoSessionParam('admin_password');
        $retval = file_put_contents($target_conf, $txt);

        // Password is saved so use it so user can login
        if (!ReporticoApp::isSetConfig('admin_password') || ReporticoApp::getConfig('admin_password') == "PROMPT"  ) {
            ReporticoApp::setConfig("admin_password", $password1);
        }

        \Reportico\Engine\Authenticator::unflag("show-set-admin-password");

        return;

    }
}
// -----------------------------------------------------------------------------
