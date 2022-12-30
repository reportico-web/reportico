<?php

namespace Reportico\Engine;

//Class to store global var

class ReporticoLang
{
    // Translate string into another language using the (ReporticoApp::get("translations")) array
    static function &translate($in_string)
    {
        $out_string = &$in_string;

        $langage = ReporticoApp::getConfig("language");
        $translations = ReporticoApp::get("translations");

        if ($translations) {
            if (array_key_exists($langage, $translations)) {
                $langset = &$translations[$langage];
                if (isset($langset[$in_string])) {
                    $out_string = &$langset[$in_string];
                }

            }
        }
        

        return $out_string;
    }

    // Translate string into another language using the (ReporticoApp::get("translations")) array
    static function &translateReportDesc($in_report)
    {
        $in_report = preg_replace("/\.xml$/", "", $in_report);
        $out_string = false;
        
        $langage = ReporticoApp::getConfig("language");
        $report_desc = ReporticoApp::get("report_desc");
        
        
        if ($report_desc) {
            if (array_key_exists($langage, $report_desc)) {
                $langset = &$report_desc[$langage];
                if (isset($langset[$in_report])) {
                    $out_string = &$langset[$in_report];
                }
            }
        }

        return $out_string;
    }

    // Load the relevant localisation strings from the language folder
    static function loadModeLanguagePack($mode, $output_encoding = "utf-8", $replace = false)
    {
        $langfile = ReporticoUtility::findBestLocationInIncludePath("language");

        // Look for encoding specific language file
        if (ReporticoApp::isSetConfig("SW_OUTPUT_ENCODING") && ReporticoApp::getConfig("SW_OUTPUT_ENCODING") != "UTF8" && is_dir($langfile . "/" . ReporticoApp::getConfig("language") . "/" . ReporticoApp::getConfig("SW_OUTPUT_ENCODING") . "/" . $mode)) {
            $langfile = $langfile . "/" . ReporticoApp::getConfig("language") . "/" . ReporticoApp::isSetConfig("SW_OUTPUT_ENCODING") . "/" . $mode . ".php";
            require $langfile;
        } else {
            $langfile = $langfile . "/" . ReporticoApp::getConfig("language") . "/" . $mode . ".php";
            if (!is_file($langfile)) {
                trigger_error("Language pack for mode  $mode, language " . ReporticoApp::getConfig("language") . " not found", E_USER_ERROR);
            } else {
                require $langfile;
                // Convert UTF-8 mode to output character set if differen from native language pack
                if (strtolower($output_encoding) != "utf-8") {
                    foreach ($locale_arr["template"] as $k => $v) {
                        $locale_arr["template"][$k] = iconv("utf-8", $output_encoding, $v);
                    }
                }
                
                $local = ReporticoApp::get("locale");
                if (!($local) || !is_array(($local)) || $replace) {
                    ReporticoApp::set("locale", $locale_arr);
                } else {
                    if (is_array($local["template"]) && is_array($locale_arr) && is_array($locale_arr["template"])) {
                        $arr = array("template" => array_merge($local["template"], $locale_arr["template"]));
                        ReporticoApp::set("locale", $arr);
                    }
                }
            }
        }
    }

    // Load the users custom translations strings from the project
    static function loadProjectLanguagePack($project, $output_encoding = "utf-8")
    {

        ReporticoApp::set("translations", array());

        // Include project specific language translations these could be
        // held in the file lang.php or lang_<language>.php
        $langfile = "projects/$project/lang_" . ReporticoApp::getConfig("language") . ".php";
        if (is_file($langfile)) {
            include $langfile;
        } else {
            ReporticoUtility::findFileToInclude($langfile, $langfile);
            if (is_file($langfile)) {
                include $langfile;
            } else {
                $langfile = "projects/$project/lang.php";
                if (!is_file($langfile)) {
                    ReporticoUtility::findFileToInclude($langfile, $langfile);
                }

                if (is_file($langfile)) {
                    include $langfile;
                }
            }
        }

        $translation = ReporticoApp::get("translations");
        $langage = ReporticoApp::getConfig("language");
        if (isset($translation[$langage]) && is_array($translation[$langage])) {
            // Convert UTF-8 mode to output character set if differen from native language pack
            if (strtolower($output_encoding) != "utf-8") {
                foreach ($translation[$langage] as $k => $v) {
                    $translation["template"][$k] = iconv("utf-8", $output_encoding, $v);
                }
            }
        }
    }

    // Set local language strings in templates
    static function localiseTemplateStrings(&$in_template)
    {
        $local = ReporticoApp::get("locale");
        if ($local) {
            foreach ($local["template"] as $key => $string) {
                $in_template->assign($key, $string);
            }
        }

        // Now set the HTML META tag for identifying the HTML encoding character set
        $in_template->assign("OUTPUT_ENCODING", ReporticoLocale::getOutputEncodingHtml());
    }

    // Fetched translation for a template string
    static function templateXlate($in_string)
    {
        if (!$in_string) {
            return $in_string;
        }

        $out_string = "T_" . $in_string;
        $locale = ReporticoApp::get("locale");
        if ($locale) {
            if (array_key_exists($out_string, $locale["template"])) {
                $out_string = $locale["template"][$out_string];
            }
            else
                $out_string = $in_string;
        }
        else
            $out_string = $in_string;
        return $out_string;
    }


    // availableLanguages for each folder in language create an entry.
    // Used to generate language selection box
    static function availableLanguages()
    {
        $langs = array();
        $lang_dir = ReporticoUtility::findBestLocationInIncludePath("language");
    //echo $lang_dir; die;
        if (is_dir($lang_dir)) {
            // Place english at the start
            if ($dh = opendir($lang_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == "en_gb" || $file == "en_us") {
                        if (is_dir($lang_dir . "/" . $file)) {
                            $langs[] = array("label" => self::templateXlate($file), "value" => $file, "active" => ($file == ReporticoApp::getConfig("language")));
                        }
                    }

                }
                closedir($dh);
            }
            if ($dh = opendir($lang_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != ".." && $file != "CVS" && $file != "packs" && $file != "en_us" && $file != "en_gb") {
                        if (is_dir($lang_dir . "/" . $file)) {
                            $langs[] = array("label" => self::templateXlate($file), "value" => $file, "active" => ($file == ReporticoApp::getConfig("language")));
                        }
                    }

                }
                closedir($dh);
            }
        }

        // No languages found at all - default to en_gb
        if (count($langs) == 0) {
            $langs[] = array("label" => self::templateXlate("en_gb"), "value" => "en_gb");
        }
        return $langs;
    }

}
