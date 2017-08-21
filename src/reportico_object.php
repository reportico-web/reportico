<?php
namespace Reportico;

/**
 * Class reportico_object
 *
 * Base class for other reportico classes. 
 */
class reportico_object
{

	var $debug = false;
	var $formats = array();
	var $attributes = array();
	var $default_attr = array();

	function __construct()
	{
		$this->default_attr = $this->attributes;
	}

	function debug($val)
	{
		if ( $this->debug )
			printf("<br>(X".get_class($this)."): $val\n");
	}

	function error($in_text)
	{
		trigger_error($in_text, E_USER_ERROR);
	}


	function & get_attribute ( $attrib_name )
		{
            $val = false;
            if ( isset ( $this->attributes[$attrib_name] ) )
			    if ( $this->attributes[$attrib_name] )
			    {
				    $val = check_for_default($attrib_name, $this->attributes[$attrib_name]);
				    return $val;
			    }
			    else
			    {
				    $val = check_for_default($attrib_name, $this->attributes[$attrib_name]);
				    return $val;
			    }
            else
                return $val;
		}
		
	// Parses a Reportico value ( e.g. criteria default, criteria value )
	// and if it indicates some kind of metavalue surrounded by {} then
	// convert it
	// Current syntax :-
	// {constant,<VALUE>} - returns defined PHP constants
	function & derive_meta_value ( $to_parse )
	{
		global $g_project;

		$parsed  = $to_parse;
        if ( preg_match ( "/{constant,SW_PROJECT}/", $parsed ) )
        {
            $parsed = $g_project;
            return $parsed;
        }
		else
        if ( preg_match ( "/{constant,SW_DB_DRIVER}/", $parsed ) )
        {
            if ( defined("SW_DB_TYPE") && SW_DB_TYPE == "framework" )
                $parsed = "framework";
            else
            {
                $parsed = preg_replace('/{constant,([^}]*)}/',
                        	'\1',
                        	$parsed);
			    if ( defined ( $parsed ) )
				    $parsed = constant($parsed);
			    else
				    $parsed = "";
            }
			return $parsed;
        }
		else
        if ( 
            preg_match ( "/{constant,SW_DB_PASSWORD}/", $parsed )  ||
            preg_match ( "/{constant,SW_DB_USER}/", $parsed )  ||
            preg_match ( "/{constant,SW_DB_DATABASE}/", $parsed ) 
        )
        {
            if ( defined("SW_DB_TYPE") && SW_DB_TYPE == "framework" )
                $parsed = "";
            else
            {
                $parsed = preg_replace('/{constant,([^}]*)}/',
                        	'\1',
                        	$parsed);
			    if ( defined ( $parsed ) )
				    $parsed = constant($parsed);
			    else
				    $parsed = "";
            }
			return $parsed;
        }
		else
		if ( preg_match ( "/{constant,.*}/", $parsed ) )
		{
            $parsed = preg_replace('/{constant,([^}]*)}/',
                        	'\1',
                        	$parsed);
			if ( defined ( $parsed ) )
				$parsed = constant($parsed);
			else
				$parsed = "";
			return $parsed;
		}
		else
			return $parsed;
	}

	function & derive_attribute ( $attrib_name, $default )
		{
			if ( $this->attributes[$attrib_name] )
			{
				return $this->attributes[$attrib_name];
			}
			else
			{
				return $default;
			}
		}


	function set_format ( $format_type, $format_value )
	{
		if ( !array_key_exists($format_type, $this->formats) )
			handle_error("Format Type ".$format_type." Unknown.");

		$this->formats[$format_type] = $format_value;
	}

	function get_format ( $format_type )
	{
		if ( !array_key_exists($format_type, $this->formats) )
			return;

		return $this->formats[$format_type];
	}

	function set_attribute ( $attrib_name, $attrib_value )
	{
		if ( !array_key_exists($attrib_name, $this->attributes ) )
			return;

		if ( $attrib_value )
			$this->attributes[$attrib_name] = $attrib_value;
		else
			$this->attributes[$attrib_name] = $this->default_attr[$attrib_name];
	}

	function & get_value ( $value_name )
	{
		return $this->values[$value_name];
	}

	function set_value ( $value_name, $value_value )
	{
		$this->values[$value_name] = $value_value;
	}

	function submitted ( $value_name )
	{
		if ( array_key_exists($value_name, $_REQUEST) )
			return true;
		else
			return false;
	}
}