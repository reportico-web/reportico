<?php
namespace Reportico;

/**
 * Class ReporticoObject
 *
 * Base class for other reportico classes.
 */
class ReporticoObject
{

    public $debug = false;
    public $formats = array();
    public $attributes = array();
    public $default_attr = array();

    public function __construct()
    {
        $this->default_attr = $this->attributes;
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
        if (isset($this->attributes[$attrib_name])) {
            if ($this->attributes[$attrib_name]) {
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
                    $parsed = "";
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

    public function &deriveAttribute($attrib_name, $default)
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
