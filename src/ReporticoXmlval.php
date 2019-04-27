<?php

namespace Reportico\Engine;


/**
 * Class ReporticoXmlval
 *
 * Stores the definition of a single tag within an XML report definition
 */
class ReporticoXmlval
{
    public $name;
    public $value;
    public $attributes;
    public $ns;
    public $xmltext = "";
    public $elements = array();

    public function __construct($name, $value = false, $attributes = array())
    {
        $this->name = $name;
        $this->value = $value;
        $this->attributes = $attributes;
    }

    public function &add_xmlval($name, $value = false, $attributes = false)
    {
        $element = new ReporticoXmlval($name, htmlspecialchars($value), $attributes);
        $this->elements[] = &$element;
        return $element;
    }

    public function unserialize()
    {
        $this->xmltext = "<";
        $this->xmltext .= $this->name;

        if ($this->attributes) {
            $infor = true;
            foreach ($this->attributes as $k => $v) {
                if ($v) {
                    if ($infor) {
                        $this->xmltext .= " ";
                    } else {
                        $infor = true;
                    }

                    $this->xmltext .= $k . '="' . $v . '"';
                }

            }
        }

        $this->xmltext .= ">";

        if ($this->value || $this->value === "0") {
            $this->xmltext .= $this->value;
        } else {
            foreach ($this->elements as $el) {
                $this->xmltext .= $el->unserialize();
            }
        }

        $this->xmltext .= "</";
        $this->xmltext .= $this->name;
        $this->xmltext .= ">";

        return $this->xmltext;
    }

    public function write()
    {
        echo "<";
        echo $this->name;

        if ($this->attributes) {
            $infor = true;
            foreach ($this->attributes as $k => $v) {
                if ($v) {
                    if ($infor) {
                        echo " ";
                    } else {
                        $infor = true;
                    }

                    echo $k . '="' . $v . '"';
                }

            }
        }

        echo ">";

        if ($this->value) {
            echo $this->value;
        } else {
            foreach ($this->elements as $el) {
                $el->write();
            }
        }

        echo "</";
        echo $this->name;
        echo ">";
    }

}
