<?php

namespace Reportico\Engine;
/*
 Reportico - PHP Reporting Tool
 Copyright (C) 2010-2014 Peter Deed

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

 * File:        reportico_report_array.php
 *
 * Base class for all report output formats.
 * Defines base functionality for handling report 
 * page headers, footers, group headers, group trailers
 * data lines
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swoutput.php,v 1.33 2014/05/17 15:12:31 peter Exp $
 */

class DataSourceElastic
{
    public $EOF = false;
    public $pointer = 0;
    public $elasticusers = array();
    public $index = false;
    public $endpoint = false;
    public $user = false;
    public $password = false;
    public $parameters = [];

	function __construct ($parameters = false)
	{
		$this->page_width = 595;
		$this->page_height = 842;
		$this->column_spacing = "2%";

		if ( !$parameters || !isset($parameters["index"]) || !isset($parameters["endpoint"]) ) {
			die ("Elastic plugin requires following parameters :<br>index<br>endpoint<br><BR>Optional:<BR>user<BR>password");
		}
		$this->index = $parameters["index"];
		$this->endpoint = $parameters["endpoint"];
		$this->user = isset($parameters["user"]) ? $parameters["user"] : "";
		$this->password = isset($parameters["password"]) ? $parameters["password"] : "";
        $this->parameters = isset($parameters["parameters"]) ? $parameters["parameters"] : "";
        $this->parameters["index"] = $this->index;
        $this->parameters["endpoint"] = $this->endpoint;
	}

	function start ()
	{

		reportico_report::start();

		$results=array();

		$ct=0;
	}

	function finish ()
	{
		reportico_report::finish();

	}

	function format_column(& $column_item)
	{
		if ( !$this->show_column_header($column_item) )
				return;

		$k =& $column_item->column_value;
		$padstring = str_pad($k,20);
	}

	function each_line($val)
	{
		reportico_report::each_line($val);

		// Set the values for the fields in the record
		$record = array();

		foreach ( $this->query->display_order_set["column"] as $col )
	  	{
			$qn = get_query_column($col->query_name, $this->columns ) ;
			$this->results[$qn->query_name][] = $qn->column_value;
			$ct = count($this->results[$qn->query_name]);
       	}
		
	}

    function Connect()
    {
	    return true;
    }

    function &Execute($sql)
    {
        $this->pointer = 0;
        $this->EOF = false;
        
        $index = $this->index;
        $index = '{QUOTE}'.$this->index.'{QUOTE}';
        $sql = preg_replace("/wy\-1/", $index, $sql);
        $sql = preg_replace("/{index}/i", $index, $sql);

        $sql = preg_replace("/\\\n/", " ", $sql);
        $sql = preg_replace("/\\\"/", "'", $sql);
        $sql = preg_replace("/{QUOTE}/", "\"", $sql);
        $sql = array("query" => $sql);
        $sql = json_encode($sql);

	    $endpoint = $this->endpoint;
	    $endpoint = preg_replace("/{user}/i", $this->user, $this->endpoint);
	    $endpoint = preg_replace("/{password}/i", $this->password, $this->endpoint);
	    $endpoint = $endpoint."/_sql";
        //$sql = preg_replace("/\-\*/", "-%2A", $sql);
        $ch = curl_init($endpoint);

        $len = strlen($sql);


        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            //'SOAPAction: "http://schemas.elgin.gov.uk/sdep/webservice/RequestPull"',
            //'Host: services.roadworks.org',
            "Content-Length: $len",
            'Expect: 100-continue',
            'Connection: Keep-Alive'));

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sql);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $x = json_decode($result, true);

        $this->results = array();


        $columns = [];
        if ( isset($x["columns"] )) {
            foreach ($x["columns"] as $kcol => $column) {
                $columns[$kcol] = $column["name"];
            }
            $result = [];
            foreach ($x["rows"] as $krow => $row) {
                foreach($row as $kcol => $col) {
                    $result[$columns[$kcol]] = $col;
                }
                $this->results[] = $result;
            }
        } 
    
        return $this;
    }

    function FetchRow()
    {
        $this->pointer++;
        if ( $this->pointer > count($this->results) )
            return ( null);

        return $this->results[$this->pointer - 1];
        
    }

    function Close()
    {
        $this->results = [];
    }

    function ErrorNo()
    {
       	return "Error no";
    }

    function ErrorMsg()
    {
       	return "Error message";
    }

}
?>
