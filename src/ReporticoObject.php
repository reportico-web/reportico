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
            if ( !isset($this->usage["methods"][$method]["parameters"])){
                $this->usage["methods"][$method]["parameters"] = [];
            }

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
                            trigger_error("$level()->$method parameter $ct invalid value {$args[$ct-1]} - $k must be one of ".implode("|", array_keys($v["options"])).".<BR>".$this->builderMethodUsage($level, $method), E_USER_ERROR);
                            echo "$level()->$method parameter $ct value {$args[$ct-1]} - $k must be one of ".implode("|", array_keys($v["options"])).".<BR>".$this->builderMethodUsage($level, $method);
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function builderUsage($level) {


        $text = "";
        $nextline = "<BR>";
        $nextline = "\n";
        $space = "&nbsp;";
        $space = " ";
        if ( isset($this->usage["summary"] ))
            $text .= "<BR><div>".$this->usage["summary"]."</div><BR>";
        if ( isset($this->usage["description"] ))
            $text .= "<div>".$this->usage["description"]."</div><BR>";
        $text .= "{$nextline}{$nextline}Usage: {$nextline}";
        $mct = 0;
        $methodct = count($this->usage["methods"]);
        $syntax = "";
        $syntax = "\Reportico\Engine\Builder\build()\n";
        foreach ($this->usage["methods"] as $method => $properties){
            $ct = 0;
            if ( $mct )
                $syntax .= "){$nextline}";
            if ( $method == $level ) 
                $syntax .= "{$space}{$space}->$method(";
            else
                $syntax .= "{$space}{$space}{$space}{$space}->$method(";
            $mct++;

            if ( isset($properties["parameters"]) ) {
            $parameterct = count($properties["parameters"]);
            $pct = 0;
            foreach ($properties["parameters"] as $k => $v) {
                $pct ++;
                if ( !is_array($v) ) {
                    if ( $parameterct > 1 )
                        $syntax .= "{$nextline}{$space}{$space}{$space}{$space}{$space}{$space}{$space}{$space}";
                    $syntax .= "$k :";
                    if ( strlen($v) > 100 ) {
                        $ptr = 100;
                        $char = substr($v,$ptr,1);
                        while ( $ptr < strlen($v) && $char != " " && $char != "," ){
                            $ptr++;
                            $char = substr($v,$ptr,1);
                        }
                        $syntax .= substr($v, 0, $ptr);
                        $syntax .= "{$nextline}{$space}{$space}{$space}{$space}{$space}{$space}{$space}{$space}";
                        $syntax .= substr($v, $ptr);
                    }
                    else
                        $syntax .= "$v";
                    if ( $pct < $parameterct )
                        $syntax .= ",";
                } else {
                    if ( $parameterct > 1 )
                        $syntax .= "{$nextline}{$space}{$space}{$space}{$space}{$space}{$space}{$space}{$space}";
                    $syntax .= "$k";
                    $last = false;
                    if ( isset($v["options"])){
                        $syntax .=  " [";
                        foreach ( $v["options"] as $k1 => $v1 ) {
                            if ( $last ) $syntax .= "|";
                            $syntax .=  "$k1";
                            $last = $k1;
                        }
                        $syntax .=  "]";
                    } else {
                        foreach ( $v as $k1 => $v1 ) {
                            if ( isset($v1["options"])){
                                $syntax .=  " options ";
                                foreach ( $v1["options"] as $k2 => $v2 ) {
                                    if ( $last ) $syntax .= "|";
                                    $syntax .=  "$k2";
                                    $last = $k2;
                                }
                            } else {
                                $syntax .= " - $k1: $v1 {$nextline}";
                            }
                        }
                    }
                    $syntax .= "";
                }
                $ct++;
                $pct++;
            }
            }
        }
        if ( $mct )
            $syntax .= "){$nextline}";
            //$syntax .= "{$space}{$space}{$space}{$space}){$nextline}";


        $syntax = "\n<?php \n".$syntax."\n?>";
        $syntax = highlight_string($syntax, true);
        $syntax = preg_replace("/....\?php/", "", $syntax);
        $syntax = preg_replace("/\?.*>/", "", $syntax);
        $text .= $syntax;

        //$text .= ")<BR>";
        return $text;
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

    public function &getAttribute($attrib_name, $default = false)
    {
        $val = false;

        if (isset($this->attributes[$attrib_name]) ) {
            if ($this->attributes[$attrib_name] && $this->attributes[$attrib_name] != ".DEFAULT" ) {
                $val = $this->attributes[$attrib_name];
                return $val;
            } else {
                $val = ReporticoApp::getDefaultConfig($attrib_name, $this->attributes[$attrib_name]);
                if ( ( !$val || $val == ".DEFAULT" ) && $default ){
                    $val = $default;
                }
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
