<?

namespace Reportico;


/**
 * Class reportico_db_array
 *
 * Allows an array of data to appear like a database table by
 * implementing the necessary functions for connecting, disconnecting
 * and fetching. This means the Reportico engine will not care if data comes
 * from a database or an array
 */
class reportico_db_array
{
	var $array_set;
	var $EOF = false;
	var $ct = 0;
	var $numrows = 0;

	function __construct()
	{
	}

	function Connect(&$in_array)
	{
		$this->array_set =& $in_array;
		reset($this->array_set);
		$k = key($this->array_set);
		$this->numrows = count($this->array_set[$k]);
	}

	function FetchRow()
	{
		$rs = array();

		reset($this->array_set);
		while ( $d =& key($this->array_set) )
		{
			$rs[$d] = $this->array_set[$d][$this->ct];
			next($this->array_set);
		}
		$this->ct++;

		if ( $this->ct == $this->numrows )
		{
			$this->EOF = true;
		}

		return($rs);
	}

	function & ErrorMsg()
	{
		return "Array dummy Message";
	}

	function Close()
	{
		return ;
	}

	function & Execute($in_query)
	{
		return($this);
	}


}