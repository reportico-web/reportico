<?php
namespace Reportico;

/*
Reportico - PHP Reporting Tool
Copyright (C) 2010-2011 Peter Deed

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

 * File:        swutil.php
 *
 * Contains utility functions required during Reportico operation
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2011 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swutil.php,v 1.38 2014/05/16 23:03:00 peter Exp $
 */
global $g_error_status;

// System Error Handling and Debug Tracking Variables
$g_system_errors = array();
$g_code_area = "";
$g_system_debug = array();
$g_debug_mode = false;
$g_error_status = false;

// Debug Levels
define('REPORTICO_DEBUG_NONE', 0);
define('REPORTICO_DEBUG_LOW', 1);
define('REPORTICO_DEBUG_MEDIUM', 2);
define('REPORTICO_DEBUG_HIGH', 3);
define('REPORTICO_DEFAULT_INDICATOR', '.');

// Ensure that sessions from different browser windows on same devide
// target separate SESSION_ID
function setUpReporticoSession()
{
    // Get current session
    $session_name = session_id();

    // Check for Posted Session Name and use that if specified
    if (isset($_REQUEST['reportico_session_name'])) {
        $session_name = $_REQUEST['reportico_session_name'];
        if (preg_match("/_/", $session_name)) {
            $ar = explode("_", $session_name);
            ReporticoApp::set("session_namespace", $ar[1]);
            if (ReporticoApp::get("session_namespace")) {
                ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
            }

            // Set session to join only if it is not NS meaning its called from framework and existing session
            // should be used
            if ($ar[0] != "NS") {
                $session_name = $ar[0];
            }

        }
    }

    // If the session_name starts with NS_ then it is a namespace for reportico
    // embedded in a framework or for multiple concurrent reportico instances
    // , so set the current namespace. All sessiion variables for this namespace
    // will be stored in a namspaces specific session array
    if (strlen($session_name) >= 3 && substr($session_name, 0, 3) == "NS_") {
        if (!$session_name || !isset($_SESSION)) {
            session_start();
        }

        ReporticoApp::set("session_namespace", substr($session_name, 3));

        // IF NS_NEW passed then autogenerate session namespace from current time
        if (ReporticoApp::get("session_namespace") == "NEW") {
            ReporticoApp::set("session_namespace", date("YmdHis"));
        }
        if (ReporticoApp::get("session_namespace")) {
            ReporticoApp::set("session_namespace_key", "reportico_" . ReporticoApp::get("session_namespace"));
        }

        if (isset($_REQUEST['clear_session']) && isset($_SESSION)) {
            initializeReporticoNamespace(reporticoNamespace());
        }
        return;
    }

    // If no current session start one, or if request to clear session (it really means clear namespace)
    // then clear this out
    if (!$session_name || isset($_REQUEST['clear_session'])) {
        // If no session current then create a new one
        if (!$session_name || !isset($_SESSION)) {
            session_start();
        }

        if (isset($_REQUEST['new_session']) && $_REQUEST['new_session']) {
            session_regenerate_id(false);
        }

        //unsetReporticoSessionParam("template");
        //session_regenerate_id(false);
        $session_name = session_id();

        if (isset($_REQUEST['clear_session'])) {
            initializeReporticoNamespace(reporticoNamespace());
        }
    } else {
        if (session_id() != $session_name) {
            session_id($session_name);
            session_start();
        }
        $session_name = session_id();
    }
}

/*
 * Cleanly shuts doen session
 */
function closeReporticoSession()
{
}

function convertYMDtoLocal($in_time, $from_format, $to_format)
{
    // Allow a time to be blank
    if (trim($in_time) == "") {
        return " ";
    }

    $from_format = getLocaleDateFormat($from_format);
    $to_format = getLocaleDateFormat($to_format);

    if (!class_exists("\DateTime", false) || !method_exists("\DateTime", "createFromFormat")) {
        //handleError("This version of PHP does not have the \DateTime class. Must be PHP >= 5.3 to use date criteria");
        //return false;
        $retval = reformatDate($from_format, $to_format, $in_time);
        return $retval;
    }
    try {
        $datetime = \DateTime::createFromFormat($from_format, $in_time);

        if (!$datetime) {
            handleError("Date value '$in_time' is expected in date format $from_format");
            return false;
        }
        $retval = $datetime->format($to_format);
    } catch (Exception $e) {
        handleError("Error in date formatting<BR>" . $e->getMessage());
        return "";
    }
    return $retval;
}

// Based on the users working language, returns the language code
// for loading the apprpriate data picket
function getDatepickerLanguage($in_format)
{
    $retval = "";
    switch ($in_format) {
        case "en-GB":$retval = "en-GB";
            break;
        case "ar-DZ":$retval = "ar-DZ";
            break;
        case "en-AU":$retval = "en-AU";
            break;
        case "en-NZ":$retval = "en-NZ";
            break;
        case "fr-CN":$retval = "fr-CH";
            break;
        case "pt-BR":$retval = "pt-BR";
            break;
        case "sr-SR":$retval = "sr-SR";
            break;
        case "zh-HK":$retval = "zh-HK";
            break;
        case "zh-TW":$retval = "zh-TW";
            break;
        case "zh-CN":$retval = "zh-CN";
            break;
        case "en_gb":$retval = "en-GB";
            break;
        case "ar_dz":$retval = "ar-DZ";
            break;
        case "en_au":$retval = "en-AU";
            break;
        case "en_nz":$retval = "en-NZ";
            break;
        case "fr_cn":$retval = "fr-CH";
            break;
        case "pt_br":$retval = "pt-BR";
            break;
        case "sr_sr":$retval = "sr-SR";
            break;
        case "zh_hk":$retval = "zh-HK";
            break;
        case "zh_tw":$retval = "zh-TW";
            break;
        case "zh_cn":$retval = "zh-CN";
            break;
        default:
            $retval = substr($in_format, 0, 2);
    }
    return $retval;
}

// Based on the users working date format, returns the appropriate format
// for the JUI date picker routine
function getDatepickerFormat($in_format)
{
    $retval = "";
    switch ($in_format) {
        case "d-m-Y":$retval = "dd-mm-yy";
            break;
        case "d/m/Y":$retval = "dd/mm/yy";
            break;
        case "m/d/Y":$retval = "mm/dd/yy";
            break;
        case "m-d-Y":$retval = "mm-dd-yy";
            break;
        case "Y/m/d":$retval = "yy/mm/dd";
            break;
        case "Y-m-d":$retval = "yy-mm-dd";
            break;

        default:
            $retval = "dd/mm/yy";
    }
    return $retval;
}

function parseDate($in_keyword, $in_time = false, $in_mask = "%d/%m/%Y")
{

    if (preg_match("/^{.*}$/", $in_keyword)) {
        $in_keyword = preg_replace("/{\"*([^\"]*)\"*}/", "$1", $in_keyword);
        $datetime = new \DateTime("$in_keyword");
        return $datetime->format($in_mask);
    }

    // To handle date ranges/date criteria that need to have the field set to blank
    if ($in_keyword == "BLANK") {
        return " ";
    }

    $in_mask = getLocaleDateFormat($in_mask);
    if (!$in_time) {
        $in_time = time();
    }
    $now = localtime($in_time, true);

    // Begin calculating the required data/time value
    switch ($in_keyword) {

        case "FIRSTOFLASTMONTH":
            $now["tm_mday"] = 1;
            $now["tm_mon"]--;
            if ($now["tm_mon"] < 0) {
                $now["tm_year"]--;
                $now["tm_mon"] = 11;
            }
            break;

        case "FIRSTOFYEAR":
            $now["tm_mday"] = 1;
            $now["tm_mon"] = 0;
            break;

        case "FIRSTOFLASTYEAR":
            $now["tm_mday"] = 1;
            $now["tm_mon"] = 0;
            $now["tm_year"]--;
            break;

        case "LASTOFYEAR":
            $now["tm_mday"] = 31;
            $now["tm_mon"] = 11;
            break;

        case "LASTOFLASTYEAR":
            $now["tm_mday"] = 31;
            $now["tm_mon"] = 11;
            $now["tm_year"]--;
            break;

        case "LASTOFLASTMONTH":
        case "FIRSTOFMONTH":
            $now["tm_mday"] = 1;
            break;

        case "LASTOFMONTH":
            $now["tm_mday"] = 1;
            $now["tm_mon"]++;
            if ($now["tm_mon"] == 12) {
                $now["tm_year"]++;
                $now["tm_mon"] = 0;
            }
            break;

        case "STARTOFWEEK":
        case "ENDOFWEEK":
        case "STARTOFLASTWEEK":
        case "ENDOFLASTWEEK":
        case "YESTERDAY":
        case "TOMORROW":
        case "TODAY":
            break;

        default:
            return $in_keyword;
    }

    if ($now["tm_year"] < 1000) {
        $now["tm_year"] += 1900;
    }

    // Convert the modified date time values back to to UNIX time
    $new_time = mktime($now["tm_hour"], $now["tm_min"],
        $now["tm_sec"], $now["tm_mon"] + 1,
        $now["tm_mday"], $now["tm_year"]);
    //$now["tm_isdst"] );

    // Apply any element transformations to get the reuqired UNIX date
    switch ($in_keyword) {
        case "YESTERDAY":
            $new_time -= 60 * 60 * 24;
            break;

        case "TOMORROW":
            $new_time += 60 * 60 * 24;
            break;

        case "LASTOFLASTMONTH":
        case "LASTOFMONTH":
            $new_time -= 60 * 60 * 24;
            break;

        case "STARTOFWEEK":
            ///$new_time = strtotime("last Saturday");
            $new_time = strtotime("this week");
            break;

        case "ENDOFWEEK":
            $new_time = strtotime("next week - 1 day");
            break;

        case "STARTOFLASTWEEK":
            ///$new_time = strtotime("last Saturday");
            $new_time = strtotime("last week");
            break;

        case "ENDOFLASTWEEK":
            $new_time = strtotime("this week - 1 day");
            break;

        case "FIRSTOFMONTH":
        default:
            break;

    }

    if (!class_exists("\DateTime", false)) {
        handleError("This version of PHP does not have the DateTime class. Must be PHP >= 5.3 to use date criteria");
        return false;
    }
    try {
        $datetime = new \DateTime("@$new_time");
    } catch (Exception $e) {
        handleError("Error in date formatting<BR>" . $e->getMessage());
        return "";
    }

    $ret = $datetime->format($in_mask);

    return ($ret);

}

// -----------------------------------------------------------------------------
// Function : convertDateRangeDefaultsToDates
// Takes a date default string and converts to an array of dates
// Handles the fact that dates may contain "-" characters and the date range specifier
// is indicated by a "-" too
// -----------------------------------------------------------------------------
function convertDateRangeDefaultsToDates($in_type, $in_string, &$range_start, &$range_end)
{
    $retval = true;
    if ($in_type == "DATE") {
        $elar = preg_split("/-/", $in_string);
        if (count($elar) == 1) {
            $range_start = $in_string;
        } else if (count($elar) == 3) // Specified in xx-xx-xx format
        {
            $range_start = $elar[0] . "-" . $elar[1] . "-" . $elar[2];
        } else {
            $retval = false;
            $range_start = "TODAY";
        }
    }

    if ($in_type == "DATERANGE") {
        $range_start = "TODAY";
        $range_end = "TODAY";
        $elar = preg_split("/-/", $in_string);

        if (count($elar) == 2) {
            $range_start = $elar[0];
            $range_end = $elar[1];
        } else if (count($elar) == 6) // Specified in xx-xx-xx format
        {
            $range_start = $elar[0] . "-" . $elar[1] . "-" . $elar[2];
            $range_end = $elar[3] . "-" . $elar[4] . "-" . $elar[5];
        } else if (count($elar) == 4 && is_numeric($elar[0]) && is_numeric($elar[1]) && is_numeric($elar[2]) && !is_numeric($elar[3])) // Specified in xx-xx-xx format
        {
            $range_start = $elar[0] . "-" . $elar[1] . "-" . $elar[2];
            $range_end = $elar[3];
        } else if (count($elar) == 4 && is_numeric($elar[1]) && is_numeric($elar[2]) && is_numeric($elar[3]) && !is_numeric($elar[0])) // Specified in xx-xx-xx format
        {
            $range_start = $elar[0];
            $range_end = $elar[1] . "-" . $elar[2] . "-" . $elar[3];
        } else {
            $retval = false;
            $range_start = "TODAY";
            $range_end = "TODAY";
        }
    }

    return $retval;
}

function getQueryColumnValue($name, &$arr)
{
    $ret = "NONE";
    foreach ($arr as $val) {
        if ($val->query_name == $name) {
            return $val->column_value;
        }
    }

    //foreach($arr as $val)
    //{
    //return $val->column_value;
    //}
    //return $name;
}

function getQueryColumn($name, &$arr)
{
    foreach ($arr as $k => $val) {
        if ($val->query_name == $name) {
            return $arr[$k];
        }

    }
    return false;
}

function getGroupColumn($name, &$arr)
{
    foreach ($arr as $k => $val) {
        if ($val->group_name == $name) {
            return $arr[$k];
        }

    }
    return false;
}

function &getDbImageString(
    $in_driver,
    $in_dbname,
    $in_hostname,
    $in_sql,
    $in_conn = false
) {

    $rs = false;
    if (!$in_conn) {
        $hostname = $in_hostname;
        $dbname = $in_dbname;
        $driver = $in_driver;

        $ado_connection = NewADOConnection($driver);
        $ado_connection->SetFetchMode(ADODB_FETCH_ASSOC);
        $ado_connection->PConnect($hostname, '', '', $dbname);

        $rs = $ado_connection->Execute($in_sql)
        or die("Query failed : " . $ado_connection->ErrorMsg());
    } else {
        $rs = $in_conn->Execute($in_sql)
        or die("Query failed : " . $in_conn->ErrorMsg());
    }

    $line = $rs->FetchRow();

    if ($line) {
        foreach ($line as $col) {
            $data = $col;
            break;
        }
    } else {
        $data = false;
    }

    return $data;
    $rs->Close();
}

function keyValueInArray($in_arr, $in_key)
{
    if (array_key_exists($in_key, $in_arr)) {
        $ret = $in_arr[$in_key];
    } else {
        $ret = false;
    }

    return ($ret);
}

function getRequestItem($in_val, $in_default = false, $in_default_condition = true)
{
    if (array_key_exists($in_val, $_REQUEST)) {
        $ret = $_REQUEST[$in_val];
    } else {
        $ret = false;
    }

    if ($in_default && $in_default_condition && !$ret) {
        $ret = $in_default;
    }

    return ($ret);
}

function sessionItem($in_item, $in_default = false)
{
    $ret = false;
    if (issetReporticoSessionParam($in_item)) {
        $ret = getReporticoSessionParam($in_item);
    }

    if (!$ret) {
        $ret = false;
    }

    if ($in_default && !$ret) {
        $ret = $in_default;
    }

    setReporticoSessionParam($in_item, $ret);

    return ($ret);
}

function sessionRequestItem($in_item, $in_default = false, $in_default_condition = true)
{
    $ret = false;
    if (issetReporticoSessionParam($in_item)) {
        $ret = getReporticoSessionParam($in_item);
    }

    if (array_key_exists($in_item, $_REQUEST)) {
        $ret = $_REQUEST[$in_item];
    }

    if (!$ret) {
        $ret = false;
    }

    if ($in_default && $in_default_condition && !$ret) {
        $ret = $in_default;
    }

    setReporticoSessionParam($in_item, $ret);

    return ($ret);
}

function getCheckboxValue($in_tag)
{
    if (array_key_exists($in_tag, $_REQUEST)) {
        return true;
    } else {
        return false;
    }

}

function hhmmssToSeconds($in_hhmmss)
{
    $ar = explode(":", $in_hhmmss);

    if (count($ar) != 3) {
        return (0);
    }

    if (preg_match("/ /", $in_hhmmss)) {
        return (0);
    }

    $secs = (int) $ar[0] * 3600;
    $secs += (int) $ar[1] * 60;
    $secs += (int) $ar[2];
    $first = substr($in_hhmmss, 0, 1);
    if ($first == "-") {
        $secs = -$secs;
    }

    return ($secs);
}

// Debug Message Handler
function handleDebug($dbgstr, $in_level)
{
    global $g_system_debug;
    global $g_code_area;
    global $g_debug_mode;

    //if ( $g_debug_mode )
    //{
    if ($g_debug_mode >= $in_level) {
        $g_system_debug[] = array(
            "dbgstr" => $dbgstr,
            "dbgarea" => $g_code_area,
        );
    }
    //}

}

// User Error Handler
function handleError($errstr, $type = E_USER_ERROR)
{
    global $g_system_debug;
    global $g_code_area;
    global $g_errors;

    $g_errors = true;

    trigger_error($errstr, $type);
}

// exception handler function
function ExceptionHandler($exception)
{
    echo "<PRE>";
    echo $exception->getMessage();
    echo $exception->getTraceAsString();
    echo "</PRE>";
}

// error handler function
function ErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $g_system_errors;
    global $g_error_status;
    global $g_code_area;
    global $g_code_source;

    switch ($errno) {
        case E_ERROR:
            $errtype = swTranslate("Error");
            break;
        case E_NOTICE:
            $errtype = swTranslate("Notice");
            break;
        case E_USER_ERROR:
            $errtype = swTranslate("Error");
            break;
        case E_USER_WARNING:
            $errtype = swTranslate("");
            break;
        case E_USER_NOTICE:
            $errtype = swTranslate("");
            break;
        case E_WARNING:
            $errtype = swTranslate("");
            break;

        default:
            $errtype = swTranslate("Fatal Error");

    }

    // Avoid adding duplicate errors
    if (!$g_system_errors) {
        $g_system_errors = array();
    }

    foreach ($g_system_errors as $k => $val) {
        if ($val["errstr"] == $errstr) {
            $g_system_errors[$k]["errct"]++;
            return;
        }
    }

    $g_system_errors[] = array(
        "errno" => $errno,
        "errstr" => $errstr,
        "errfile" => $errfile,
        "errline" => $errline,
        "errtype" => $errtype,
        "errarea" => $g_code_area,
        "errsource" => $g_code_source,
        "errct" => 1,
    );
    //echo "<PRE>";
    //var_dump($g_system_errors);
    //echo "</PRE>";

    $g_error_status = 1;

}

function backtrace()
{
    debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
}

// Look for a file in the include path, or the path of the current source file
function findFileToInclude($file_path, &$new_file_path, &$rel_to_include = "")
{
    // First look in path of current file
    static $_path_array = null;
    if (__FILE__) {
        $selfdir = dirname(__FILE__);
        $new_file_path = $selfdir . "/" . $file_path;
        $old_error_handler = set_error_handler("Reportico\ErrorHandler", 0);
        if (@file_exists($new_file_path) || is_dir($new_file_path)) {
            $new_file_path = $selfdir . "/" . $file_path;
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            return true;
        }
        $old_error_handler = set_error_handler("Reportico\ErrorHandler");
    }

    // else look in incude path
    if (!isset($_path_array)) {
        $_ini_include_path = get_include_path();

        if (defined("__DIR __")) {
            $selfdir = __DIR__;
        } else {
            $selfdir = dirname(__FILE__);
        }

        if (strstr($_ini_include_path, ';')) {
            $_ini_include_path = $selfdir . ";" . $_ini_include_path;
            $_path_array = explode(';', $_ini_include_path);
        } else {
            $_ini_include_path = $selfdir . ":" . $_ini_include_path;
            $_path_array = explode(':', $_ini_include_path);
        }
    }
    // Turn off Error handling for the following to avoid open_basedir errors
    $old_error_handler = set_error_handler("Reportico\ErrorHandler", 0);
    foreach ($_path_array as $_include_path) {
        if (@file_exists($_include_path . "/" . $file_path)) {
            $new_file_path = $_include_path . "/" . $file_path;
            $old_error_handler = set_error_handler("Reportico\ErrorHandler");
            return true;
        }
    }
    $old_error_handler = set_error_handler("Reportico\ErrorHandler");

    $new_file_path = $file_path;
    return false;
}

// Translate string into another language using the (ReporticoApp::get("translations")) array
function &swTranslate($in_string)
{
    $out_string = &$in_string;
    if ((ReporticoApp::get("translations"))) {
        if (array_key_exists(ReporticoApp::getConfig("language"), (ReporticoApp::get("translations")))) {
            $langset = &(ReporticoApp::get("translations"))[ReporticoApp::getConfig("language")];
            if (isset($langset[$in_string])) {
                $out_string = &$langset[$in_string];
            }

        }
    }

    return $out_string;
}

// Translate string into another language using the (ReporticoApp::get("translations")) array
function &swTranslateReportDesc($in_report)
{
    $in_report = preg_replace("/\.xml$/", "", $in_report);
    $out_string = false;
    if (ReporticoApp::get("report_desc")) {
        if (array_key_exists(ReporticoApp::getConfig("language"), ReporticoApp::get("report_desc"))) {
            $langset = &(ReporticoApp::get("report_desc"))[ReporticoApp::getConfig("language")];
            if (isset($langset[$in_report])) {
                $out_string = &$langset[$in_report];
            }

        }
    }

    return $out_string;
}

// Load the relevant localisation strings from the language folder
function loadModeLanguagePack($mode, $output_encoding = "utf-8", $replace = false)
{
    $langfile = findBestLocationInIncludePath("language");

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
            if (!(ReporticoApp::get("locale")) || !is_array((ReporticoApp::get("locale"))) || $replace) {
                ReporticoApp::set("locale", $locale_arr);
            } else {
                if (is_array((ReporticoApp::get("locale"))["template"]) && is_array($locale_arr) && is_array($locale_arr["template"])) {
                    $arr = array("template" => array_merge((ReporticoApp::get("locale"))["template"], $locale_arr["template"]));
                    ReporticoApp::set("locale", $arr);
                }
            }
        }
    }
}

// Load the users custom translations strings from the project
function loadProjectLanguagePack($project, $output_encoding = "utf-8")
{

    ReporticoApp::set("translations", array());

    // Include project specific language translations these could be
    // held in the file lang.php or lang_<language>.php
    $langfile = "projects/$project/lang_" . ReporticoApp::getConfig("language") . ".php";
    if (is_file($langfile)) {
        include $langfile;
    } else {
        findFileToInclude($langfile, $langfile);
        if (is_file($langfile)) {
            include $langfile;
        } else {
            $langfile = "projects/$project/lang.php";
            if (!is_file($langfile)) {
                findFileToInclude($langfile, $langfile);
            }

            if (is_file($langfile)) {
                include $langfile;
            }
        }
    }

    if (isset((ReporticoApp::get("translations"))[ReporticoApp::getConfig("language")]) && is_array((ReporticoApp::get("translations"))[ReporticoApp::getConfig("language")])) {
        // Convert UTF-8 mode to output character set if differen from native language pack
        if (strtolower($output_encoding) != "utf-8") {
            foreach ((ReporticoApp::get("translations"))[ReporticoApp::getConfig("language")] as $k => $v) {
                (ReporticoApp::get("translations"))["template"][$k] = iconv("utf-8", $output_encoding, $v);
            }
        }
    }
}

// Set local language strings in templates
function localiseTemplateStrings(&$in_smarty, $in_template = "")
{
    if (ReporticoApp::get("locale")) {
        foreach ((ReporticoApp::get("locale"))["template"] as $key => $string) {
            $in_smarty->assign($key, $string);
        }
    }

    // Now set the HTML META tag for identifying the HTML encoding character set
    $in_smarty->assign("OUTPUT_ENCODING", getOutputEncodingHtml());
}

// Fetched translation for a template string
function templateXlate($in_string)
{
    if (!$in_string) {
        return $in_string;
    }

    $out_string = "T_" . $in_string;
    if ((ReporticoApp::get("locale"))) {
        if (array_key_exists($out_string, (ReporticoApp::get("locale"))["template"])) {
            $out_string = (ReporticoApp::get("locale"))["template"][$out_string];
        }
        else
            $out_string = $in_string;
    }
    else
        $out_string = $in_string;
    return $out_string;
}

// Is path executable and writeable?
function swPathExecutable($in_path)
{
    $perms = fileperms($in_path);

    if (!is_dir($in_path)) {
        return false;
    }

    if (!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && is_executable($in_path)) {
        return false;
    }

    if (!is_writeable($in_path)) {
        return false;
    }

    return true;
}

// Search currentl directory and include for best absolute poistion
// of a file path
function findBestUrlInIncludePath($path)
{
    $newpath = $path;
    $reltoinclude;
    //if ( !is_file ( $newpath ) && !is_dir ( $newpath ) )
    //{
    $found = findFileToInclude($newpath, $newpath, $reltoinclude);
    $newpath = getRelativePath(str_replace("/", "\\", realpath($newpath)), dirname(__FILE__));
    if (!$found) {
        return false;
    }

    //}
    return $newpath;
}

function findBestLocationInIncludePath($path)
{
    $newpath = $path;
    $reltoinclude;
    if (substr($newpath, 0, 1) == "/" || substr($newpath, 0, 1) == "\\") {
        if (is_file($newpath) || is_dir($newpath)) {
            return $newpath;
        } else {
            return false;
        }

    }

    //if ( !is_file ( $newpath ) && !is_dir ( $newpath ) )
    //{
    $found = findFileToInclude($newpath, $newpath, $reltoinclude);
    //$newpath = getRelativePath(str_replace ("/", "\\", realpath($newpath)), dirname(realpath($_SERVER["SCRIPT_FILENAME"])));
    if (!$found) {
        return false;
    }

    //}
    return $newpath;
}

// Builds the base URL elements to HTML links produced in HTML
// created from :-
//    1 - the http://.....
//    2 - extra GET parameters
function buildForwardUrlGetParams($path, $forward_url_get_params, $remainder)
{
    $urlpath = findBestLocationInIncludePath($path);

    if ($forward_url_get_params || $remainder) {
        $urlpath .= "?";
    }

    if ($forward_url_get_params) {
        $urlpath .= $forward_url_get_params;
    }

    if ($remainder) {
        $urlpath .= "&";
    }

    if ($remainder) {
        $urlpath .= $remainder;
    }

    return $urlpath;
}

// For backward compatibility ensures that date formats anything expressed in
// formats sutiable for the date function ( e.g. Y-m-d ) are converted to
// locale formats ( e.g. %Y-%m-%d )
function getLocaleDateFormat($in_format)
{

    $out_format = $in_format;
    if ($in_format == "%d/%m/%Y") {
        $out_format = "d-m-Y";
    }

    if ($in_format == "%Y/%m/%d") {
        $out_format = "Y-m-d";
    }

    if ($in_format == "%m/%Y/%d") {
        $out_format = "m-Y-d";
    }

    if ($in_format == "%d-%m-%Y") {
        $out_format = "d-m-Y";
    }

    if ($in_format == "%Y-%m-%d") {
        $out_format = "Y-m-d";
    }

    if ($in_format == "%m-%Y-%d") {
        $out_format = "m-Y-d";
    }

    if (!$in_format) {
        $in_format = "d-m-Y";
    }

    return ($out_format);
}

// Calculates the URL path to reportico, ie the URL you
// would type into a browser address path to get to reportico
// This can be used to then find reportico images, links, runners etc
function getReporticoUrlPath()
{
    $newpath = "swutil.php";
    $found = findFileToInclude($newpath, $newpath, $reltoinclude);
    $newpath = getRelativePath(str_replace("/", "\\", realpath($newpath)), dirname(realpath($_SERVER["SCRIPT_FILENAME"])));
    $above = dirname($_SERVER["SCRIPT_NAME"]);
    if ($above == "/") {
        $above = "";
    }

    $url_path = $above . "/" . sessionRequestItem('reporticourl', dirname($newpath));

    // If reportico source files are installed in root directory or in some other
    // scenarios such as an invalid linkbaseurl parameter the dirname of the
    // the reportico files returns just a slash (backslash on windows) so
    // return a true path
    if ($url_path == "/" || $url_path == "\\") {
        $url_path = "/";
    } else {
        $url_path = $url_path . "/";
    }

    return $url_path;
}

// availableLanguages for each folder in language create an entry.
// Used to generate language selection box
function availableLanguages()
{
    $langs = array();
    $lang_dir = findBestLocationInIncludePath("language");
//echo $lang_dir; die;
    if (is_dir($lang_dir)) {
        // Place english at the start
        if ($dh = opendir($lang_dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == "en_gb" || $file == "en_us") {
                    if (is_dir($lang_dir . "/" . $file)) {
                        $langs[] = array("label" => templateXlate($file), "value" => $file, "active" => ($file == ReporticoApp::getConfig("language")));
                    }
                }

            }
            closedir($dh);
        }
        if ($dh = opendir($lang_dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != ".." && $file != "CVS" && $file != "packs" && $file != "en_us" && $file != "en_gb") {
                    if (is_dir($lang_dir . "/" . $file)) {
                        $langs[] = array("label" => templateXlate($file), "value" => $file, "active" => ($file == ReporticoApp::getConfig("language")));
                    }
                }

            }
            closedir($dh);
        }
    }

    // No languages found at all - default to en_gb
    if (count($langs) == 0) {
        $langs[] = array("label" => templateXlate("en_gb"), "value" => "en_gb");
    }
    return $langs;
}

// Takes project config db encoding and converts it to PHP representation for iconv
function dbCharsetToPhpCharset($in)
{
    $out = $in;
    switch ($in) {
        case "None":$out = false;
            break;
        case "UTF8":$out = "UTF-8";
            break;
        case "LATIN1":$out = "ISO-8859-1";
            break;
        case "LATIN2":$out = "ISO-8859-2";
            break;
        case "LATIN3":$out = "ISO-8859-3";
            break;
        case "LATIN4":$out = "ISO-8859-4";
            break;
        case "LATIN4":$out = "ISO-8859-4";
            break;
        case "LATIN5":$out = "ISO-8859-9";
            break;
        case "LATIN6":$out = "ISO-8859-10";
            break;
        case "LATIN7":$out = "ISO-8859-13";
            break;
        case "LATIN8":$out = "ISO-8859-14";
            break;
        case "LATIN9":$out = "ISO-8859-15";
            break;
        case "LATIN9":$out = "ISO-8859-16";
            break;
        case "LATIN9":$out = "ISO-8859-16";
            break;
        case "ISO-8859-1":
        case "ISO-8859-2":
        case "ISO-8859-3":
        case "ISO-8859-4":
        case "ISO-8859-5":
        case "ISO-8859-6":
        case "ISO-8859-7":
        case "ISO-8859-8":
        case "ISO-8859-8-I":
        case "ISO-8859-9":
        case "ISO-8859-10":
        case "ISO-8859-11":
        case "ISO-8859-12":
        case "ISO-8859-13":
        case "ISO-8859-14":
        case "ISO-8859-15":
        case "ISO-8859-16":$out = $in;
            break;
        case "GB18030":
        case "GB2312":
        case "GBK":
        case "BIG5":$out = strtolower($in);
            break;
        case "WIN1250":$out = "Windows-1250";
            break;
        case "WIN1251":$out = "Windows-1251";
            break;
        case "WIN1252":$out = "Windows-1252";
            break;
        case "WIN1253":$out = "Windows-1253";
            break;
        case "WIN1254":$out = "Windows-1254";
            break;
        case "WIN1255":$out = "Windows-1255";
            break;
        case "WIN1256":$out = "Windows-1256";
            break;
        case "WIN1257":$out = "Windows-1257";
            break;
        case "WIN1258":$out = "Windows-1258";
            break;
        case "TIS620":$out = "tis-620";
            break;
        case "SJIS":$out = "shift-jis";
            break;
        default:$out = false;
    }

    return $out;
}

// Takes project config output encoding and converts it to PHP representation for iconv
function outputCharsetToPhpCharset($in)
{
    $out = $in;
    switch ($in) {
        case "None":$out = false;
            break;
        case "UTF8":$out = "UTF-8";
            break;
        case "ISO-8859-1":
        case "ISO-8859-2":
        case "ISO-8859-3":
        case "ISO-8859-4":
        case "ISO-8859-5":
        case "ISO-8859-6":
        case "ISO-8859-7":
        case "ISO-8859-8":
        case "ISO-8859-8-I":
        case "ISO-8859-9":
        case "ISO-8859-10":
        case "ISO-8859-11":
        case "ISO-8859-12":
        case "ISO-8859-13":
        case "ISO-8859-14":
        case "ISO-8859-15":
        case "ISO-8859-16":$out = $in;
            break;
        case "GB18030":
        case "GB2312":
        case "GBK":
        case "Big5":
        case "BIG5":$out = strtolower($in);
            break;
        case "Windows-1250":$out = "Windows-1250";
            break;
        case "Windows-1251":$out = "Windows-1251";
            break;
        case "Windows-1252":$out = "Windows-1252";
            break;
        case "Windows-1253":$out = "Windows-1253";
            break;
        case "Windows-1254":$out = "Windows-1254";
            break;
        case "Windows-1255":$out = "Windows-1255";
            break;
        case "Windows-1256":$out = "Windows-1256";
            break;
        case "Windows-1257":$out = "Windows-1257";
            break;
        case "Windows-1258":$out = "Windows-1258";
            break;
        case "Shift_JIS":$out = "shift-jis";
            break;
        case "TIS-620":$out = "tis-620";
            break;
        default:$out = false;
    }

    return $out;
}

// Gets HTML META tag for setting HTML encoding based on project SW_OUTPUT_ENCODING tag
function getOutputEncodingHtml()
{

    $txt = '';
    $tmp1 = '<meta http-equiv="Content-Type" content="text/html; charset=';
    $tmp2 = '" />';
    switch (ReporticoApp::getConfig("output_encoding")) {
        case "None":$txt = '';
            break;
        case "UTF8":$txt = $tmp1 . "utf-8" . $tmp2;'<meta charset="utf-8">';
            break;
        default:$txt = $tmp1 . ReporticoApp::getConfig("output_encoding") . $tmp2;
            break;
    }

    return $txt;
}

// Converts absolute path or a file we want to access with the absolute path
// of the calling script so we can get the relative location
function getRelativePath($path, $compareTo)
{

    // On some windows machines the SCRIPT_FILENAME may be lower or upper case
    // compared to the target path which could be lower, upper or a mixture
    // so convert every thing lower for the comparison
    // Work out if it's Windows by looking for a back slash or a leading
    // driver specifier eg "C:"
    if (preg_match("/\\\/", $compareTo) || preg_match("/^[A-Za-z]:/", $compareTo)) {
        $compareTo = strtolower($compareTo);
        $path = strtolower($path);
    }

    // Convert Windows paths with "\" delimiters to forward delimiters
    $path = preg_replace("+\\\+", "/", $path);
    $compareTo = preg_replace("+\\\+", "/", $compareTo);

    // clean arguments by removing trailing and prefixing slashes
    if (substr($path, -1) == '/') {
        $path = substr($path, 0, -1);
    }

    if (substr($path, 0, 1) == '/') {
        $path = substr($path, 1);
    }

    if (substr($compareTo, -1) == '/') {
        $compareTo = substr($compareTo, 0, -1);
    }
    if (substr($compareTo, 0, 1) == '/') {
        $compareTo = substr($compareTo, 1);
    }

    // simple case: $compareTo is in $path
    if (strpos($path, $compareTo) === 0) {
        $offset = strlen($compareTo) + 1;
        return substr($path, $offset);
    }

    $relative = array();
    $pathParts = explode('/', $path);
    $compareToParts = explode('/', $compareTo);

    foreach ($compareToParts as $index => $part) {
        if (isset($pathParts[$index]) && $pathParts[$index] == $part) {
            continue;
        }

        $relative[] = '..';
    }

    foreach ($pathParts as $index => $part) {
        if (isset($compareToParts[$index]) && $compareToParts[$index] == $part) {
            continue;
        }

        $relative[] = $part;
    }

    return implode('/', $relative);
}

//
// Converts a string in HTML color format #rrggbb to an RGB array for use in pChart
function htmltorgbPchart($color)
{
    if (is_array($color)) {
        return $color;
    }

    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array($color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return array("R" => 0, "G" => 0, "B" => 0);
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array("R" => $r, "G" => $g, "B" => $b);
}

//
// Converts a string in HTML color format #rrggbb to an RGB array
function htmltorgb($color)
{
    if (is_array($color)) {
        return $color;
    }

    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array($color[0] . $color[1],
            $color[2] . $color[3],
            $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return array(0, 0, 0);
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array($r, $g, $b);
}

//
// Loads an existing report in to a reportico class instance
function &loadExistingReport($reportfile, $projects_folder = "projects")
{
    $q = new Reportico();
    $q->reports_path = $projects_folder . "/" . ReporticoApp::getconfig("project");
    $q->projects_folder = $projects_folder;

    $reader = new ReporticoXmlReader($q, $reportfile, false);
    $reader->xml2query();

    return $q;
}

/*
PHP pre 5.3 function for converting a date string from one format
to another
 */
function reformatDate($informat, $outformat, $date)
{

    // echo "Function start $informat, $outformat, $date <BR>";

    if (preg_match("=/=", $date)) {
        $arr = explode('/', $date);
    } else {
        $arr = explode('-', $date);
    }

    switch ($informat) {
        case "d-m-Y":
        case "d/m/Y":
            $inDay = $arr[0];
            $inMonth = $arr[1];
            $inYear = $arr[2];
            break;
        case "m-d-Y":
        case "m/d/Y":
            $inDay = $arr[1];
            $inMonth = $arr[0];
            $inYear = $arr[2];
            break;
        case "Y-m-d":
        case "Y/m/d":
            $inDay = $arr[2];
            $inMonth = $arr[1];
            $inYear = $arr[0];
            break;
    }

    switch ($outformat) {
        case "d-m-Y":
            $dt = $inDay . '-' . $inMonth . '-' . $inYear;
            break;
        case "m-d-Y":
            $dt = $inMonth . '-' . $inDay . '-' . $inYear;
            break;
        case "Y-m-d":
            $dt = $inYear . '-' . $inMonth . '-' . $inDay;
            break;
        case "d/m/Y":
            $dt = $inDay . '/' . $inMonth . '/' . $inYear;
            break;
        case "m/d/Y":
            $dt = $inMonth . '/' . $inDay . '/' . $inYear;
            break;
        case "Y/m/d":
            $dt = $inYear . '/' . $inMonth . '/' . $inDay;
            break;
    }
    return $dt;
}

// Converts a database column name into a label
// by converting any _ to spaces and upper casing the
// initial letter of each word
function columnNameToLabel($columnname)
{
    $retstring = str_replace("_", " ", $columnname);
    if (!function_exists("mb_strtolower")) {
        $retstring = ucwords(strtolower($retstring));
    } else {
        $retstring = ucwords(mb_strtolower($retstring, outputCharsetToPhpCharset(ReporticoApp::getConfig("output_encoding"))));
    }

    return $retstring;
}

/*
 ** Returns if a particular reeportico session parameter is set
 ** using current session namespace
 */
function issetReporticoSessionParam($param, $session_name = false)
{
    if (!$session_name) {
        return isset($_SESSION[ReporticoApp::get("session_namespace_key")][$param]);
    } else {
        return isset($_SESSION[$session_name][$param]);
    }

}

/*
 ** Sets a reportico session_param
 ** using current session namespace
 */
function setReporticoSessionParam($param, $value, $namespace = false, $array = false)
{
    if (!$namespace) {
        $_SESSION[ReporticoApp::get("session_namespace_key")][$param] = $value;
    } else {
        if (!$array) {
            $_SESSION[$namespace][$param] = $value;
        } else {
            $_SESSION[$namespace][$array][$param] = $value;
        }
    }
}

/*
 ** Gets the value of a reportico session_param
 ** using current session namespace
 */
function getReporticoSessionParam($param)
{
    if (isset($_SESSION[ReporticoApp::get("session_namespace_key")][$param])) {
        return $_SESSION[ReporticoApp::get("session_namespace_key")][$param];
    } else {
        return false;
    }

}

/*
 ** Does global reportico session exist
 */
function existsReporticoSession()
{
    if (isset($_SESSION[ReporticoApp::get("session_namespace_key")])) {
        return true;
    } else {
        return false;
    }

}

/*
 ** Clears a reportico session_param
 ** using current session namespace
 */
function unsetReporticoSessionParam($param)
{
    if (isset($_SESSION[ReporticoApp::get("session_namespace_key")][$param])) {
        unset($_SESSION[ReporticoApp::get("session_namespace_key")][$param]);
    }

}

/*
 **
 ** Register a session variable which will remain persistent throughout session
 */
function registerSessionParam($param, $value)
{
    if (!issetReporticoSessionParam($param)) {
        setReporticoSessionParam($param, $value);
    }

    return getReporticoSessionParam($param);
}

/*
 ** Returns the current session name.
 ** Session variables exist
 ** using current session namespace
 */
function reporticoSessionName()
{
    //if ( ReporticoApp::get("session_namespace") )
    if (getReporticoSessionParam("framework_parent")) {
        return "NS_" . ReporticoApp::get("session_namespace");
    } else {
        return session_id() . "_" . ReporticoApp::get("session_namespace");
    }

}

/*
 ** Returns the current namespace
 */
function reporticoNamespace()
{
    return ReporticoApp::get("session_namespace_key");
}

/*
 ** initializes a reportico namespace
 **
 */
function initializeReporticoNamespace($namespace)
{
    if (isset($_SESSION[$namespace])) {
        unset($_SESSION[$namespace]);
    }

    $_SESSION[$namespace] = array();
    $_SESSION[$namespace]["awaiting_initial_defaults"] = true;
    $_SESSION[$namespace]["firsttimeIn"] = true;
}
