<?php

namespace Reportico\Engine;

/**
 * Class DataSourceArray
 *
 * Allows an array of data to appear like a database table by
 * implementing the necessary functions for connecting, disconnecting
 * and fetching. This means the Reportico engine will not care if data comes
 * from a database or an array
 */
class DataSourceArray
{
    public $array_set;
    public $EOF = false;
    public $ct = 0;
    public $numrows = 0;

    function __construct($source = false)
    {
        if ( $source )
            $this->Connect($source);
    }


    public function Connect(&$in_array)
    {
        $this->array_set =& $in_array;
        reset($this->array_set);
        $k = key($this->array_set);
        //$this->numrows = count($this->array_set[$k]);
        $this->numrows = count($this->array_set);
        $ct = 0;

    }

    function FetchRow()
    {
        $this->ct++;
        if ( $this->ct == $this->numrows )
        {
            $this->EOF = true;
            return($this->array_set[$this->ct - 1]);
        }
        return($this->array_set[$this->ct - 1]);
    }

    public function FetchRow1()
    {
        $rs = array();

        reset($this->array_set);
        while ($d = key($this->array_set)) {
            $rs[$d] = $this->array_set[$d][$this->ct];
            next($this->array_set);
        }
        echo "<BR>";
        var_dump($this->array_set); die;
        $this->ct++;

        if ($this->ct == $this->numrows) {
            $this->EOF = true;
        }

        return ($rs);
    }

    public function &ErrorMsg()
    {
        return "Array dummy Message";
    }

    public function ErrorNo()
    {
        return -1;
    }

    public function Close()
    {
        return;
    }

    public function &Execute($in_query)
    {
        return ($this);
    }

}
