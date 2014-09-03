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

 * File:        swsql.php
 *
 * Contains functionality for parsing SQL statements and
 * converting them to queries that can be used by the
 * Reportico engine
 *
 * @link http://www.reportico.org/
 * @copyright 2010-2014 Peter Deed
 * @author Peter Deed <info@reportico.org>
 * @package Reportico
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version $Id: swsql.php,v 1.17 2014/05/17 15:12:32 peter Exp $
 */


/**
 * Class reportico_sql_parser
 *
 * Parses SQL statements entered by user during
 * report design mode and imports them into
 * the Reportico engine
 */
class reportico_sql_parser
{

	var $sql;
	var $sql_raw;
	var $columns = array();
	var $tables = array();
	var $table_text;
	var $where = "";
	var $group = "";
	var $orders = array();
	var $status_message = "";
	var $unique = false;
    var $columnoffset = 0;      // Positon in sql where select columns exists
    var $whereoffset = 0;       // Positon in sql where WHERE clause exists or would exist
    var $haswhere = false;      // Does the sql have a where clause?

	function __construct( $in_sql )
	{
		$this->sql = $in_sql;
	}

	function import_into_query( &$in_query )
	{
        $in_query->sql_raw = $this->sql_raw;

		//When importing into query, we need to ensure that we remove
		// any columns already existing which do not appear in the
		// new query
		$delete_columns = array();
		foreach ( $in_query->columns as $k => $v )
		{
			if ( $v->in_select )
			{
				$delete_columns[$v->query_name] = true;
			}
		}

		foreach ( $this->columns as $col )
		{
			$qn = $col["name"];
			if ( $col["alias"] )
				$qn = $col["alias"];
		        
			$in_query->create_criteria_column(
					$qn, $col["table"], $col["name"], "char", 30, "####", true);

			if ( array_key_exists($qn, $delete_columns ) )
			{
				$delete_columns[$qn] = false;
			}
		}


		$ct = 0;
		$tabtext = "";
		foreach ( $this->tables as $col )
		{
				if ( $ct++ > 0 )
					$tabtext .= ",";

				switch ( $col["jointype"] )
				{
					case "outer":
						$tabtext .= "outer ";
						break;

					case "inner":
					case "default":
				}

				$tabtext .= $col["name"];
				if ( $col["alias"] )
					$tabtext .= " ".$col["alias"];
		}

		$in_query->table_text = $tabtext;
		$in_query->table_text = $this->table_text;
		$in_query->where_text = false;
		if ( substr($in_query->where_text, 0, 9) == "AND 1 = 1" ) 
		{
			$in_query->where_text = substr($in_query->where_text, 9);
		}

		if ( $this->group )
			$in_query->group_text = "GROUP BY ".$this->group;
		else
			$in_query->group_text = "";

		// Delete existing order columns
		$in_query->order_set = array();
		foreach ( $this->orders as $col )
		{
				if ( ($qc = get_query_column($col["name"], $in_query->columns)) )
					$in_query->create_order_column( $col["name"], $col["type"] );
		}

		// Now remove from the parent query any columns which were not in the
		// imported SQL
		foreach ( $delete_columns as $k => $v )
		{
			if ( $v )
			{
				$in_query->remove_column($k);
			}
		}

		// Now order the query columns in the reportico query to reflect the order specified in 
		// the select statement
		$pos = 0;
		$xx = false;
		foreach ( $this->columns as $col )
		{
			$pos2 = 0;
			$cut = false;
			foreach ( $in_query->columns as $k => $v )
			{
				if ( $v->query_name == $col["alias"] )
				{
					$cut = array_splice($in_query->columns, $pos2, 1 );
					break;
				}
				$pos2++;
				
			}

			if ( $cut )
			{
				array_splice($in_query->columns, $pos, 0,
								$cut );
			}

			$pos++;

		}

		$in_query->rowselection = "all";		
		if ( $this->unique )
			$in_query->rowselection = "unique";		

        
	}

	function  display()
	{
		echo "Columns<br>\n=======<br>\n";
		foreach ( $this->columns as $col )
		{
				echo $col["table"].".".$col["name"];
				echo " (".$col["alias"].")";
				echo " => ",$col["expression"];
				echo "<br>\n";
		}
		echo "<br>\nTables<br>\n======<br>\n";
		foreach ( $this->tables as $col )
		{
				echo $col["name"];
				echo " (".$col["alias"].")";
				echo " - ".$col["jointype"];
				echo "<br>\n";
		}

		echo "<br>\nWhere<br>\n=====<br>\n";
		echo $this->where;
		echo "<br>\n";

		echo "<br>\nOrder<br>\n=====<br>\n";
		foreach ( $this->orders as $col )
		{
				echo $col["name"]." ";
				echo $col["type"];
				echo "<br>\n";
		}

	}

    // **
    // Parses the report main SQL in order to extract the report columns to return
    // It performs this first trying to find the SELECT part of the query which contains the columns
    // Given that a single SQL statement may have many SELECT elements ( a WITH statement, th emain select,
    // embedded select in a where clause ) any of them could bring back the report columns. 
    // **
	function parse($warn_empty_aliases = true)
	{
		$err = false;

        $this->sql_raw = $this->sql;

        // First extract every element of of the sql which begins with SELECT ..... FROM
        $matches = array();
        preg_match_all ( "/.*SELECT\s*(.*)\sFROM\s.*/isU", $this->sql, $matches, PREG_OFFSET_CAPTURE );
       
		$sql =& $this->sql;

        // Find the main query SELECT column list which may be hidden among other many selects in 
        // a the users statement. The trick is to find the columns belonging to a select that
        // is not preceded by a "(" indicating a sub select.
        $col = "";
            
        $selpos = -1;
        $frompos = -1;
        $ptr = 0;
        $brackets = 0;
        $doublequotes = 0;
        while ( $ptr < strlen($sql) )
        {
            $bit = substr($sql, $ptr, 1);
            $bit4 = substr($sql, $ptr, 4);
            $bit6 = substr($sql, $ptr, 6);
            $bit7 = substr($sql, $ptr, 7);
            $inc = 1;

            if ( $bit == "\"" && $doublequotes == 0)
                $doublequotes++;
            else if ( $bit == "\"" )
                $doublequotes--;
            else if ( $bit == "(" )
                $brackets++;
            else if ( $bit == ")" )
                $brackets--;
            else if ( preg_match ("/SELECT\s/i", $bit7) )
            {
                if ( $brackets == 0 && $doublequotes == 0 )
                {
                    $selpos = $ptr + 7;
                    $inc = 7;
                }
            }
            else if ( preg_match("/\sFROM\s/i", $bit6 ) )
            {
                if ( $selpos > -1 && $brackets == 0 && $doublequotes == 0 )
                {
                    $frompos = $ptr;
                    $inc = 5;
                    break;
                }
            }
            $ptr += $inc;
        }

        $columnoffset = $frompos;

        // Find the main query SELECT column list which may be hidden among other many selects in 
        // a the users statement. The trick is to find the columns belonging to a select that
        // is not preceded by a "(" indicating a sub select.
        if ( $selpos == -1 || $frompos == -1 )
        {
			trigger_error("no SELECT clause specified. Query must contain a 'SELECT'", E_USER_ERROR);
        }
        else
        {
            $col = substr ( $sql, $selpos, $frompos - $selpos );
			if ( $col )
			{
				$this->parse_column_list($this->sql_raw, $col, $selpos ,$warn_empty_aliases );

                // Now find the location where the WHERE is or where it would be if there isnt one
                $wherematch = array();
                if ( preg_match ( "/.*(\[\s*WHERE\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->haswhere = false;
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*\s+WHERE(\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ))
                {
                    $this->haswhere = true;
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+GROUP BY\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+GROUP BY\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+GROUP BY\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+HAVING\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+ORDER BY\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+LIMIT\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else if ( preg_match ( "/.*(\s+PROCEDURE\s+.*)/siU", $sql, $wherematch, PREG_OFFSET_CAPTURE, $this->columnoffset ) )
                {
                    $this->whereoffset = $wherematch[1][1];
                }
                else
                {
                    $this->whereoffset = strlen ( $sql ) ;
                }
			}
		}

        $upd_match = "/^\s*UPDATE\s*(.*)/is";
        $del_match = "/^\s*DELETE\s*(.*)/is";

		if ( preg_match($upd_match, $sql, $cpt ) )
		{
			trigger_error("Update statements are not allowed in designer queries", E_USER_ERROR);
			$sel_type = "UPDATE";
            $this->sql_raw = "#". $this->sql_raw;
            $this->whereoffset = 0;
		}

		if ( preg_match($del_match, $sql, $cpt ) )
		{
			trigger_error("Delete statements are not allowed designer queries", E_USER_ERROR);
			$sel_type = "DELETE";
            $this->sql_raw = "#". $this->sql_raw;
            $this->whereoffset = 0;
		}

        return $this->whereoffset;

	}

	function tokenise_columns( $in_string )
	{

		$escaped = false;
		$level_stack = array();
		$in_dquote = false;
		$in_squote = false;
		$rbracket_level = 0;
		$sbracket_level = 0;
		$collist = array();
		$cur = false;

		for ( $ct = 0; $ct < strlen($in_string); $ct++ )
		{
			if ( $ct == 0 )
			{
				$collist[] = "";
				end($collist);
				$ky = key($collist);
				$cur =& $collist[$ky];
			}

			$ch = substr($in_string,$ct,1);
			$ok_to_add = true;
			
			switch ( $ch )
			{
				case ",":
					if ( !($in_dquote || $in_squote || $rbracket_level > 0 || $sbracket_level > 0) )
					{
						$collist[] = "";
						end($collist);
						$ky = key($collist);
						$cur =& $collist[$ky];
						$ok_to_add = false;
					}
					break;

				case "\"":
					if ( $in_dquote )
						$in_dquote = false;
					else
						if ( !$in_squote )
							$in_dquote = true;
					break;

				case "'":
					if ( $in_squote )
						$in_squote = false;
					else
						if ( !$in_dquote )
							$in_squote = true;
					break;

				case "(":
					if ( !$in_squote && !$in_dquote )
						$rbracket_level++;
					break;

				case ")":
					if ( !$in_squote && !$in_dquote )
						$rbracket_level--;
					break;
			
				case "[":
					if ( !$in_squote && !$in_dquote )
						$sbracket_level++;
					break;

				case "]":
					if ( !$in_squote && !$in_dquote )
						$sbracket_level--;
					break;
			}

			if ($ok_to_add )
				$cur .= $ch;

		}

		return $collist;
	} 

	// -----------------------------------------------------------------------------
	// Function : parse_column_list
	// ----------------------------
	// Analyses each column in a user SQL statement
    // 
    // If user has provided expressions in sql without aliases then an autogenerated
    // alias is added in
	// ----------------------------------------------------------------------------
	function parse_column_list( &$original_sql, $in_string, $offset_in_original_sql ,$warn_empty_aliases )
	{

        $tmpsql = $original_sql;
        $rolling_new_alias_offset = $offset_in_original_sql ;
		$collist = $this->tokenise_columns($in_string);
		foreach ( $collist as $k => $colitem )
		{
                $auto_gen_alias = false;
				if ( !$this->parse_column($k + 1, trim($colitem), $auto_gen_alias ,$warn_empty_aliases ) )
					return false;

                $rolling_new_alias_offset += strlen($colitem);
                if ( $auto_gen_alias )
                {
                    $tmpsql = substr($tmpsql, 0, $rolling_new_alias_offset).
                        " $auto_gen_alias". substr($tmpsql, $rolling_new_alias_offset);
                    $rolling_new_alias_offset += strlen($auto_gen_alias) + 1;
                }
                $rolling_new_alias_offset += 1;
		}
        
        $original_sql = $tmpsql;

		return true;
	} 

	// -----------------------------------------------------------------------------
	// Function : parse_column
	// -----------------------
	// Will take a column item from an SQL statement and parse it to identify
	// any alias, table identifier or expression
	// ----------------------------------------------------------------------------
	function parse_column( $in_colno, $in_string, &$auto_gen_alias ,$warn_empty_aliases )
	{
		$err = false;

		$colalias = "";
		$colname = "";
		$coltable = "";
		$colexp = "";

		// Check for an alias ( any final word which is preceded by any non
		// numeric or expression character

		// Split out the last two elements
		if ( preg_match("/(.+\))([^\s]*)\s*\$/s", $in_string, $out_match) )
		{
			if ( preg_match ( "/^[[:alpha:]]\w+$/s", $out_match[2] ) )
			{
				$colalias = $out_match[2];
				$colname = $out_match[1];
				$colexp = $colname;
			}
			else
			{
				if ( preg_match("/[^0-9A-Za-z_\r\n\t .]/", $in_string ) )
				{
					$colalias = "column".$in_colno;
                    $auto_gen_alias = $colalias;
                    if ( $warn_empty_aliases )
				        handle_debug("Expression <b>($in_string)</b> is unnamed and will be given the name <b>$colalias</b>. You might like to provide your own column alias for this expression.", 0);
				}
				$colname = $in_string;
				$colexp = $in_string;
			}
		}
		else
		if ( preg_match("/(.+)\s+(.*)\s*\$/s", $in_string, $out_match) )
		{
			if ( preg_match ( "/^[[:alpha:]]\w+$/s", $out_match[2] ) )
			{
				$colalias = $out_match[2];
				$colname = $out_match[1];
				$colexp = $colname;
			}
			else
			if ( preg_match ( "/^[a-zA-Z]$/s", $out_match[2] ) )
			{
				$colalias = $out_match[2];
				$colname = $out_match[1];
				$colexp = $colname;
			}
			else
			{
				if ( preg_match("/[^0-9A-Za-z_\r\n\t .]/", $in_string ) )
				{
					$colalias = "column".$in_colno;
                    $auto_gen_alias = $colalias;
                    if ( $warn_empty_aliases )
				        handle_debug("Expression <b>($in_string)</b> is unnamed and will be given the name <b>$colalias</b>. You might like to provide your own column alias for this expression.", 0);
				}
				$colname = $in_string;
				$colexp = $in_string;
			}
		}
		else
		{
			// Single column value only so assume no alias
			if ( preg_match("/[^0-9A-Za-z_\r\n\t .]/", $in_string ) )
			{
				$colalias = "column".$in_colno;
                $auto_gen_alias = $colalias;
                if ( $warn_empty_aliases )
				    handle_debug("Expression <b>($in_string)</b> is unnamed and will be given the name <b>$colalias</b>. You might like to provide your own column alias for this expression.", 0);
			}
			$colname = $in_string;
			$colexp = $in_string;
		}

		// Now with what's left of the column  try to ascertain a table name
		// and column part
		if ( preg_match("/^(\w+)\.(\w+)$/", $colname, $out_match) )
		{
			$coltable = $out_match[1];
			$colname = $out_match[2];
		}

		$this->columns[] = array(
				"name" =>  $colname,
				"table" =>  $coltable,
				"alias" =>  $colalias,
				"expression" =>  $colexp
				)
				;
		return true;

	}

	// -----------------------------------------------------------------------------
	// Function : test_query
    //
    // Checks syntax of report query by attempting to run user query. In order to   
    // avoid long execution times, the WHERE clause is modified to include a 1 = 0
    // test so that no rows are returned and therefore execution time is quick
	// -----------------------------------------------------------------------------
	function test_query($in_query, $sql)
	{
        
		$conn =& $in_query->datasource->ado_connection;

        if ( $this->haswhere )
        {
            // In order to test an SQL statement with a where clause add an "1 = 0 AND "
            $tmp = substr ( $sql, $this->whereoffset );
            $tmp = substr ( $sql, 0, $this->whereoffset );
            $tmp .= " 1 = 0 AND";
            $tmp .= substr( $sql, $this->whereoffset );
            $sql = $tmp;
        }
        else
        {
            // No where statement add a WHERE 1 = 0 
            $tmp = substr ( $sql, $this->whereoffset );
            $tmp = substr ( $sql, 0, $this->whereoffset );
            $tmp .= " WHERE 1 = 0 ";
            $tmp .= substr( $sql, $this->whereoffset );
            $sql = $tmp;
        }

		// Remove any meta_sql criteria links between "[" and "]"
		$sql = preg_replace("/WHERE 1 = 1/i", "WHERE 1 = 0", $sql);
		$sql = preg_replace("/\[.*\]/U", '',  $sql);

        // Replace External parameters specified by {USER_PARAM,xxxxx}
		if ( preg_match_all ( "/{USER_PARAM,([^}]*)}/", $sql, $matches ) )
        {
            foreach ( $matches[0] as $k => $v )
            {
                $param = $matches[1][$k];
                if ( isset($in_query->user_parameters[$param] ) )
                {
                    $sql = preg_replace("/{USER_PARAM,$param}/", $in_query->user_parameters[$param], $sql);
                }
                else
                {
		            trigger_error("User parameter $param, specified but not provided to reportico", E_USER_ERROR);
                }
            }
        }


        $errorCode = false;
        $errorMessage = false;
	    $recordSet = false;
        try
        {
		    $recordSet = $conn->Execute($sql) ;
        }
        catch( PDOException $Exception ) {
            $errorNumber = $Exception->getCode();
            $errorMessage = $Exception->getMessage();
            // PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
            // String.
        }


		// Begin Target Output
		if (!$recordSet) 
		{
            if ( $errorMessage )
			    handle_error( "Error in Connection:  ".$errorMessage. "<BR><BR>(Note that if the error warns of a missing temporary table that will be created at runtime, it is safe to ignore this message)");
            else
			    handle_error( "Error ( ".$conn->ErrorNo().") in Connection:  ".$conn->ErrorMsg(). "<BR><BR>(Note that if the error warns of a missing temporary table that will be created at runtime, it is safe to ignore this message)");
			return false;
		}
		else
			return true;
	}

}

