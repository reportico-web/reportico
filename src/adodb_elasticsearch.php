<?php
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

class adodb_elasticsearch
{
    public $EOF = false;
    public $pointer = 0;
    public $elasticusers = array();

	function __construct ()
	{
		$this->page_width = 595;
		$this->page_height = 842;
		$this->column_spacing = "2%";
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

    function &Execute($sql)
    {
        global $g_elasticuser;
        global $g_elasticusers;
        //if ( !isset ( $g_elasticusers[$g_elasticuser]  ))
        //{
            //return false;
        //}
        $g_elasticuser = "nimbus";
        //var_dump($g_elasticusers);
        //var_dump($this->elasticusers);
        $this->pointer = 0;
        $this->EOF = false;
        //echo $sql; echo "!!<BR>";
        //$sql ="SELECT DepotCode, COUNT(*) AS count FROM wy-1 WHERE ScheduledDepartureTime between \"2016-01-01 00:00:00\" AND \"2016-11-08 23:59:59\" AND Operator = 'FY' GROUP BY DepotCode";
        //$sql = "SELECT Operator, COUNT(*) AS count FROM wy-1 WHERE ScheduledDepartureTime > '2016-04-01 00:00:00' GROUP BY Operator ";
        //$sql = "SELECT Operator, VehicleCode FROM wy-1 where LineRef = '120' LIMIT 2";
        //$sql = "select Operator, VehicleCode FROM wy-1 where LineRef = '120' LIMIT 2 ";
        //$sql = "SELECT Operator FROM wy-1 LIMIT 4";
        //echo $sql;
        $sql = preg_replace("/wy\-1/", ELASTICSEARCH_HISTORY_INDEX, $sql);
        //echo $sql;
        $len = strlen($sql);

        //echo 'http://'.$g_elasticuser.':'.$g_elasticusers[$g_elasticuser]["password"].'@10.16.1.247:9200/_sql';
        //$ch = curl_init('http://'.$g_elasticuser.':'.$g_elasticusers[$g_elasticuser]["password"].'@127.0.0.1:9200/_sql');
        $ch = curl_init('http://'.$g_elasticuser.':'.$g_elasticusers[$g_elasticuser]["password"].'@10.16.1.245:9200/_sql');
//echo 'http://'.$g_elasticuser.':'.$g_elasticusers[$g_elasticuser]["password"].'@10.16.1.245:9200/_sql';
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        //var_dump($result); die;
        curl_close($ch);

        $x = json_decode($result, true);
        //die;

        $this->results = array();

        if ( isset ( $x["hits"]["hits"] ) )
        foreach ( $x["hits"]["hits"] as $row )
        {
            $line = array();
            foreach ( $row["_source"] as $k => $v )
            {
                $line[$k] = $v;
            }
            $this->results[] = $line;
            
        }
        //var_dump($x["aggregations"]); die;
        if ( isset ( $x["aggregations"] ) )
        foreach ( $x["aggregations"] as $k => $row )
        {
            //echo "AGG<BR>";
            //var_dump($row);
            //die;
            $lastptr = false;
            $line = array();
            $ptr = &$row;
            $levels = array();
            $levelptrs = array();
            $leveltots = array();
            $levelkeys = array();
            $levelct = 0;

            $leveltots[$levelct] = count($ptr["buckets"]);
            $levelptrs[$levelct] = 0;
            $levelkeys[$levelct] = $k;
            $levels[$levelct] = $ptr;
            $ct = 0;
            //echo "<BR>";
            //echo "<PRE>"; var_dump($ptr); echo "</PRE>";
            while ( $ptr && isset ( $ptr["buckets"] )&& $ptr["buckets"] )
            {
                $k2 = $levelptrs[$levelct];
                $v2 = $ptr["buckets"][$levelptrs[$levelct]];
//if ( $ct++ > 455 ) { echo "OUTTTT<BR>";break;}
                //echo "<PRE>";var_dump($ptr["buckets"]); die;
                //foreach ( $ptr["buckets"] as $k2 => $v2 )
                {
                    $k = $levelkeys[$levelct];

//if ( $ct >  0 ) {echo "<PRE>";var_dump($ptr); echo "<PRE>";};
//if ( $ct ==  15 ) die;
                    if ( !isset($v2["key"]) ) break;
                    $line[$k] = $v2["key"];
//echo "<BR>====== $levelct: {$levelptrs[$levelct]}/{$leveltots[$levelct]} SET $k => {$v2["key"]}<BR>";
                            //echo "<BR><PRE>LEVEL $levelct <BR>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>"; 
                                //if ( $ct == 56 ) { var_dump($this->result); var_dump($ptr); die;}
//if ( $ct == 19 )
//{var_dump($ptr); }
                    $found = false;
                    foreach ( $v2 as $k3 => $v3 )
                    {
                        if ( isset ( $v3["buckets"] )  )
                        {
                        //var_dump($v2);
                            //echo "FOUND "; 
                            //echo "NEW LEVEL $ct lev $levelct<BR><PRE>$levelct<BR>";/*var_dump($ptr);*/  echo "</PRE>";
                            //echo "<BR><PRE> $levelct CT  $k3";var_dump($levelptrs);var_dump($leveltots); echo "</PRE>"; 
                            $k = $k3;
                            if ( $levelptrs[$levelct] < $leveltots[$levelct] )
                                $levelptrs[$levelct] ++;
                            $levelct++;
                            $levelptrs[$levelct] = -1;
                            $levels[$levelct] = $ptr["buckets"][$levelptrs[$levelct-1] - 1][$k3];
                            //var_dump($levelptrs); echo "<BR><BR>";
                            //var_dump($ptr["buckets"][$levelptrs[$levelct - 1] - 1]);
                            //echo "NEW LEVEL<BR><PRE>$levelct<BR>";var_dump($levels[$levelct]);  echo "</PRE>";
                            //echo "TRY $levelct ".count($ptr["buckets"])." VS ".count($v3["buckets"])."<BR>";
                            $leveltots[$levelct] = count($v3["buckets"]);
                            $levelkeys[$levelct] = $k3;
                            //echo "POINTING REALLY AT  ".$levelptrs[$levelct - 1]." + 1<BR>";
                            $ptr = &$ptr["buckets"][$levelptrs[$levelct - 1]][$k3];
                            $ptr = &$levels[$levelct];
                            //echo "$ct NEW LEVEL<BR><PRE>$levelct<BR>";var_dump($levels[$levelct]); 
                            $found = true;

                            //echo "<BR><PRE> $ct REALLY  $k3";var_dump($levels[$levelct]["buckets"]); var_dump($levelptrs);var_dump($leveltots); echo "</PRE>"; 
                        }
                        else
                        {
                            if ( isset($v3["value"] ))
                            {
                               $line[$k3] = $v3["value"];
                            }
                        }
                    }   
                    if ( !$found )
                    {
                        
                            //echo "<BR><PRE>PRE BREAK ";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>"; 
                        //echo "BREAK BACK FROM $levelct! ";
                                    //echo "<PRE>";var_dump($levels[1]); echo"</PRE>";
                        $this->results[] = $line;
                        //if ( $levelct > 0 )
                        //{
                            if ( $levelptrs[$levelct] < $leveltots[$levelct] )
                                $levelptrs[$levelct] ++;
                            //echo "<BR><PRE>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                            //var_dump($levels[$levelct]);
                            //var_dump($ptr[$levelptrs[$levelct]]);
                                //echo "<BR><PRE>BREAK FROM $levelct:<BR>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                            if ( $levelptrs[$levelct] >= $leveltots[$levelct] )
                            {
                                //echo $levelct; die;
                                $ptr = false;
                                //echo "<BR><PRE>REACHED END $levelct:<BR>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                                if ( $levelct >= 0 )
                                {
                                    //$ptr = &$levels[$levelct];
                                    //echo "point at $levelct {$levelptrs[$levelct]}<BR>";
                                    //$ptr = &$levels[$levelct - 1]["buckets"][$levelptrs[$levelct]][$k];
                                    $ptr = &$levels[$levelct - 1];
                                }
                                $levelct--;

                                //echo "<PRE>END LEVEL {$levelkeys[$levelct]} $levelct: ".count($ptr["buckets"][0])." ";var_dump($ptr["buckets"][0]);echo "</PRE>";

                                //echo "<BR><PRE>BREAK FROM $levelct:<BR>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                            }
                            else
                            {
                                if ( $levelct > 0 )
                                {

                                    //echo "<PRE>$ct move $levelct $k {$levelptrs[$levelct]} / {$leveltots[$levelct]}<BR></PRE>";
                                    //var_dump($levels[1]);echo "</PRE>";
                                    //var_dump($levels[$levelct-1]["buckets"][$levelptrs[$levelct-1]]);
                                    //echo "COUNT for $levelct = {$levelptrs[$levelct]} / {$leveltots[$levelct]}<BR>";
                                    //echo "<PRE>";var_dump($levels[0]["buckets"][$levelptrs[$levelct-1] - 1][$k]);echo "</PRE>";
                                    $ptr = &$levels[$levelct-1]["buckets"][$levelptrs[$levelct - 1] - 1][$k];
                                    //var_dump($ptr);
                                    //if ( $ct > 8 )
                                    //{
                                    //var_dump($ptr); die;
                                    //}

                                //echo "<PRE>MOVE LEVEL $ct {$levelkeys[$levelct]} $level_ct: ".count($ptr["buckets"])." ";var_dump($ptr["buckets"]);echo "</PRE>";
                                //echo "<BR><PRE>BREAK FROM $levelct:<BR>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                                }
                                else
                                {
                                    //echo "Break back from end<BR>";
                                }

                            }
                                    //if ( $ct == 5 )
                                    //{
                                    //echo "<PRE>";
                                    //var_dump($ptr);
                                    //var_dump($levels); 
                                    //echo "</PRE>";
                                    //}
                        //}

                    }
                    else
                    {
                        //echo "break $levelct";
                            if ( $levelptrs[$levelct] < $leveltots[$levelct] )
                                $levelptrs[$levelct] ++;
                            //$levelct++;
                            //echo " $ct <BR><PRE>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>"; 
                            //var_dump($ptr); 
                            //echo "<BR><PRE>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                        //echo "<PRE>";var_dump($ptr);
                            //echo "<BR><PRE>";var_dump($levelptrs); var_dump($leveltots); echo "</PRE>";
                        //var_dump($ptr);
                        //break;
                    }


                }
                //var_dump($ptr);
            }
        //unset($this->results[count($this->results) - 1]);
        //echo "done";
        //echo "<PRE>"; var_dump($this->results ); echo "</PRE>";
            
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
