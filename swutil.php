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
            $errtype = ReporticoLang::translate("Error");
            break;
        case E_NOTICE:
            $errtype = ReporticoLang::translate("Notice");
            break;
        case E_USER_ERROR:
            $errtype = ReporticoLang::translate("Error");
            break;
        case E_USER_WARNING:
            $errtype = ReporticoLang::translate("");
            break;
        case E_USER_NOTICE:
            $errtype = ReporticoLang::translate("");
            break;
        case E_WARNING:
            $errtype = ReporticoLang::translate("");
            break;

        default:
            $errtype = ReporticoLang::translate("Fatal Error");

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

    $url_path = $above . "/" . ReporticoSession::sessionRequestItem('reporticourl', dirname($newpath));

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

?>
