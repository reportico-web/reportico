<?php
namespace Reportico\Engine;

/**
 * Class ReporticoObject
 *
 * Base class for other reportico classes.
 */
class ReporticoObject
{

    public $usage = array();
    public $debug = false;
    public $formats = array();
    public $attributes = array();
    public $default_attr = array();
    public $builder = false;

    public function __construct()
    {
        $this->default_attr = $this->attributes;
    }

    public function builderMethodValid($level, $method, $args) {

        if ( isset($this->usage["methods"]["$method"])) {
            //echo "<PRE>"; var_dump($this->usage["methods"][$method]); var_dump($args);echo "</PRE>";
            if ( count($args) != count($this->usage["methods"][$method]["parameters"]))  {
                trigger_error("$level()->$method requires ".count($this->usage["methods"][$method]["parameters"]). " parameters.<BR>".$this->builderMethodUsage($level, $method), E_USER_ERROR);
                return false;
            }

            if ( isset ($this->usage["methods"][$method]["parameters"] ) ) {
                $ct = 0;
                foreach ($this->usage["methods"][$method]["parameters"] as $k => $v ) {
                    $ct++;
                    if (isset($v["options"]))                    {
                        if (!isset($v["options"][$args[$ct-1]])) {
                            trigger_error("$level()->$method parameter $ct - $k must be one of ".implode("|", array_keys($v["options"])).".<BR>".$this->builderMethodUsage($level, $method), E_USER_ERROR);
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function builderMethodUsage($level, $method) {

        $text = "Usage: $level()->$method (<BR>";

        $ct = 0;
        foreach ($this->usage["methods"][$method]["parameters"] as $k => $v) {
            if ( $ct )
                $text .= ",";
            //var_dump($v);
            if ( !is_array($v) ) {
                $text .= "&nbsp;&nbsp;$k : $v<BR>";
            } else {
                $text .= "&nbsp;&nbsp;$k";
                $last = false;
                if ( isset($v["options"])){
                    $text .=  " options ";
                    foreach ( $v["options"] as $k1 => $v1 ) {
                        if ( $last ) $text .= "|";
                        $text .=  "$k1";
                        $last = $k1;
                    }
                }
                $text .= "<BR>";
            }
            $ct++;
        }

        $text .= ")<BR>";
        return $text;
    }

    public function debug($val)
    {
        if ($this->debug) {
            printf("<br>(X" . get_class($this) . "): $val\n");
        }

    }

    public function error($in_text)
    {
        trigger_error($in_text, E_USER_ERROR);
    }

    public function &getAttribute($attrib_name)
    {
        $val = false;

        if (isset($this->attributes[$attrib_name]) ) {
            if ($this->attributes[$attrib_name] && $this->attributes[$attrib_name] != ".DEFAULT" ) {
                $val = $this->attributes[$attrib_name];
                return $val;
            } else {
                $val = ReporticoApp::getDefaultConfig($attrib_name, $this->attributes[$attrib_name]);
                return $val;
            }
        } else {
            return $val;
        }

    }

    // Parses a Reportico value ( e.g. criteria default, criteria value )
    // and if it indicates some kind of metavalue surrounded by {} then
    // convert it
    // Current syntax :-
    // {constant,<VALUE>} - returns defined PHP constants
    public function &deriveMetaValue($to_parse)
    {
        $parsed = $to_parse;
        if (preg_match("/{constant,SW_PROJECT}/", $parsed)) {
            $parsed = ReporticoApp::getConfig("project");
            return $parsed;
        } else
        if (preg_match("/{constant,SW_DB_DRIVER}/", $parsed)) {
            if (defined("SW_DB_TYPE") && SW_DB_TYPE == "framework") {
                $parsed = "framework";
            } else {
                $parsed = preg_replace('/{constant,([^}]*)}/',
                    '\1',
                    $parsed);
                if (defined($parsed)) {
                    $parsed = constant($parsed);
                } else {
                    $parsed = ReporticoApp::getConfig("SW_DB_TYPE");
                    if ( $parsed && $parsed == "framework")
                        $parsed = ReporticoApp::getConfig("SW_DB_TYPE");
                    else
                        $parsed = ReporticoApp::getConfig("SW_DB_DRIVER");
                }

            }
            return $parsed;
        } else
        if (
            preg_match("/{constant,SW_DB_PASSWORD}/", $parsed) ||
            preg_match("/{constant,SW_DB_USER}/", $parsed) ||
            preg_match("/{constant,SW_DB_DATABASE}/", $parsed)
        ) {
            if (defined("SW_DB_TYPE") && SW_DB_TYPE == "framework") {
                $parsed = "";
            } else if ( ReporticoApp::getConfig("SW_DB_TYPE") == "framework") {
                    $parsed = "";
            } else {
                $parsed = preg_replace('/{constant,([^}]*)}/',
                    '\1',
                    $parsed);
                if (defined($parsed)) {
                    $parsed = constant($parsed);
                } else {
                    $parsed = ReporticoApp::getConfig($parsed);
                }

            }
            return $parsed;
        } else
        if (preg_match("/{constant,.*}/", $parsed)) {
            $parsed = preg_replace('/{constant,([^}]*)}/',
                '\1',
                $parsed);
            if (defined($parsed)) {
                $parsed = constant($parsed);
            } else {
                $parsed = ReporticoApp::getConfig($parsed);
            }

            return $parsed;
        } else {
            return $parsed;
        }

    }

    public function &deriveAttribute($attrib_name, $default = false)
    {
        if ($this->attributes[$attrib_name]) {
            return $this->attributes[$attrib_name];
        } else {
            return $default;
        }
    }

    public function setFormat($format_type, $format_value)
    {
        if (!array_key_exists($format_type, $this->formats)) {
            ReporticoApp::handleError("Format Type " . $format_type . " Unknown.");
        }

        $this->formats[$format_type] = $format_value;
    }

    public function getFormat($format_type)
    {
        if (!array_key_exists($format_type, $this->formats)) {
            return;
        }

        return $this->formats[$format_type];
    }

    public function setAttribute($attrib_name, $attrib_value)
    {
        if (!array_key_exists($attrib_name, $this->attributes)) {
            return;
        }

        if ($attrib_value) {
            $this->attributes[$attrib_name] = $attrib_value;
        } else {
            $this->attributes[$attrib_name] = $this->default_attr[$attrib_name];
        }

    }

    public function &get_value($value_name)
    {
        return $this->values[$value_name];
    }

    public function setValue($value_name, $value_value)
    {
        $this->values[$value_name] = $value_value;
    }

    public function submitted($value_name)
    {
        if (array_key_exists($value_name, $_REQUEST)) {
            return true;
        } else {
            return false;
        }

    }
}
