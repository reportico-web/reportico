<?php

namespace Reportico\Engine;

//Class to store global var

class ReporticoLocale
{

    static function convertYMDtoLocal($in_time, $from_format, $to_format)
    {
        // Allow a time to be blank
        if (trim($in_time) == "") {
            return " ";
        }

        $from_format = self::getLocaleDateFormat($from_format);
        $to_format = self::getLocaleDateFormat($to_format);

        if (!class_exists("\DateTime", false) || !method_exists("\DateTime", "createFromFormat")) {
            //ReporticoApp::handleError("This version of PHP does not have the \DateTime class. Must be PHP >= 5.3 to use date criteria");
            //return false;
            $retval = reformatDate($from_format, $to_format, $in_time);
            return $retval;
        }
        try {
            $datetime = \DateTime::createFromFormat($from_format, $in_time);

            if (!$datetime) {
                ReporticoApp::handleError("Date value '$in_time' is expected in date format $from_format");
                return false;
            }
            $retval = $datetime->format($to_format);
        } catch (Exception $e) {
            ReporticoApp::handleError("Error in date formatting<BR>" . $e->getMessage());
            return "";
        }
        return $retval;
    }
    // Based on the users working language, returns the language code
    // for loading the apprpriate data picket
    static function getDatepickerLanguage($in_format)
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
    static function getDatepickerFormat($in_format)
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

    static function parseDate($in_keyword, $in_time = false, $in_mask = "%d/%m/%Y")
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

        $in_mask = self::getLocaleDateFormat($in_mask);
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
            ReporticoApp::handleError("This version of PHP does not have the DateTime class. Must be PHP >= 5.3 to use date criteria");
            return false;
        }
        try {
            $datetime = new \DateTime("@$new_time");
        } catch (Exception $e) {
            ReporticoApp::handleError("Error in date formatting<BR>" . $e->getMessage());
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
    static function convertDateRangeDefaultsToDates($in_type, $in_string, &$range_start, &$range_end)
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
            foreach ( $elar as $k => $v )
                    $elar[$k] = trim($v);
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

    // For backward compatibility ensures that date formats anything expressed in
    // formats sutiable for the date function - e.g. Y-m-d are converted to
    // locale formats ( e.g. %Y-%m-%d )
    static function getLocaleDateFormat($in_format)
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


    // Takes project config db encoding and converts it to PHP representation for iconv
    static function dbCharsetToPhpCharset($in)
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
    static function outputCharsetToPhpCharset($in)
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
    static function getOutputEncodingHtml()
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

}
